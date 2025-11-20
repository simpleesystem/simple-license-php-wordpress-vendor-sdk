<?php

declare(strict_types=1);

namespace SimpleLicense\Vendor;

use SimpleLicense\Vendor\Constants;
use SimpleLicense\Vendor\Exceptions\ApiException;
use SimpleLicense\Vendor\Resources\License;

/**
 * WooCommerce Integration Helper
 * Provides convenience methods for WooCommerce order fulfillment
 */
class WooCommerceHelper
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create license from WooCommerce order
     *
     * @param int|\WC_Order $order Order ID or WC_Order object
     * @param array<string, mixed> $licenseData License data (customer_email, product_slug, tier_code, etc.)
     * @return License Created license
     * @throws ApiException
     */
    public function createLicenseFromOrder(int|\WC_Order $order, array $licenseData): License
    {
        $orderObject = is_int($order) ? wc_get_order($order) : $order;
        if (!$orderObject) {
            throw new ApiException('Order not found', Constants::ERROR_CODE_VALIDATION_ERROR);
        }

        // Ensure customer email is set
        if (!isset($licenseData['customer_email'])) {
            $licenseData['customer_email'] = $orderObject->get_billing_email();
        }

        // Create license via API
        $response = $this->client->createLicense($licenseData);
        $license = License::fromArray($response);

        // Store license metadata in order
        $this->storeLicenseInOrder($orderObject, $license);

        return $license;
    }

    /**
     * Revoke license for order (on refund/cancellation)
     *
     * @param int|\WC_Order $order Order ID or WC_Order object
     * @return void
     * @throws ApiException
     */
    public function revokeLicenseForOrder(int|\WC_Order $order): void
    {
        $orderObject = is_int($order) ? wc_get_order($order) : $order;
        if (!$orderObject) {
            return;
        }

        $licenseKey = $orderObject->get_meta(Constants::WOOCOMMERCE_ORDER_META_LICENSE_KEY);
        if (empty($licenseKey)) {
            return;
        }

        $licenseId = $orderObject->get_meta(Constants::WOOCOMMERCE_ORDER_META_LICENSE_ID);
        $idToUse = !empty($licenseId) ? (string) $licenseId : $licenseKey;

        $this->client->revokeLicense($idToUse);

        // Update order meta
        $orderObject->update_meta_data(Constants::WOOCOMMERCE_ORDER_META_LICENSE_STATUS, Constants::LICENSE_STATUS_REVOKED);
        $orderObject->save();
    }

    /**
     * Get license for order
     *
     * @param int|\WC_Order $order Order ID or WC_Order object
     * @return License|null License or null if not found
     * @throws ApiException
     */
    public function getLicenseForOrder(int|\WC_Order $order): ?License
    {
        $orderObject = is_int($order) ? wc_get_order($order) : $order;
        if (!$orderObject) {
            return null;
        }

        $licenseKey = $orderObject->get_meta(Constants::WOOCOMMERCE_ORDER_META_LICENSE_KEY);
        if (empty($licenseKey)) {
            return null;
        }

        $licenseId = $orderObject->get_meta(Constants::WOOCOMMERCE_ORDER_META_LICENSE_ID);
        $idToUse = !empty($licenseId) ? (string) $licenseId : $licenseKey;

        try {
            $response = $this->client->getLicense($idToUse);
            return License::fromArray($response);
        } catch (ApiException $e) {
            return null;
        }
    }

    /**
     * Register WooCommerce order status hooks
     *
     * @param callable|null $productMapper Optional callback to map WooCommerce product to SLS product/tier
     * @return void
     */
    public function registerOrderHooks(?callable $productMapper = null): void
    {
        add_action('woocommerce_order_status_completed', function (int $orderId) use ($productMapper) {
            $this->handleOrderCompleted($orderId, $productMapper);
        });

        add_action('woocommerce_order_status_refunded', function (int $orderId) {
            $this->handleOrderRefunded($orderId);
        });

        add_action('woocommerce_order_status_cancelled', function (int $orderId) {
            $this->handleOrderCancelled($orderId);
        });
    }

    /**
     * Handle order completed status
     *
     * @param int $orderId Order ID
     * @param callable|null $productMapper Product mapper callback
     * @return void
     */
    private function handleOrderCompleted(int $orderId, ?callable $productMapper): void
    {
        $order = wc_get_order($orderId);
        if (!$order) {
            return;
        }

        // Check if license already exists
        $existingLicense = $this->getLicenseForOrder($order);
        if ($existingLicense !== null) {
            return;
        }

        // Map products to license data
        $licenseData = $this->mapOrderToLicenseData($order, $productMapper);
        if (empty($licenseData)) {
            return;
        }

        try {
            $this->createLicenseFromOrder($order, $licenseData);
        } catch (ApiException $e) {
            error_log(sprintf('Failed to create license for order %d: %s', $orderId, $e->getMessage()));
        }
    }

    /**
     * Handle order refunded status
     *
     * @param int $orderId Order ID
     * @return void
     */
    private function handleOrderRefunded(int $orderId): void
    {
        try {
            $this->revokeLicenseForOrder($orderId);
        } catch (ApiException $e) {
            error_log(sprintf('Failed to revoke license for order %d: %s', $orderId, $e->getMessage()));
        }
    }

    /**
     * Handle order cancelled status
     *
     * @param int $orderId Order ID
     * @return void
     */
    private function handleOrderCancelled(int $orderId): void
    {
        try {
            $this->revokeLicenseForOrder($orderId);
        } catch (ApiException $e) {
            error_log(sprintf('Failed to revoke license for order %d: %s', $orderId, $e->getMessage()));
        }
    }

    /**
     * Map WooCommerce order to license data
     *
     * @param \WC_Order $order Order object
     * @param callable|null $productMapper Product mapper callback
     * @return array<string, mixed> License data
     */
    private function mapOrderToLicenseData(\WC_Order $order, ?callable $productMapper): array
    {
        $licenseData = [
            'customer_email' => $order->get_billing_email(),
        ];

        if ($productMapper !== null) {
            $items = $order->get_items();
            foreach ($items as $item) {
                $product = $item->get_product();
                if (!$product) {
                    continue;
                }

                $mapped = $productMapper($product, $item, $order);
                if (!empty($mapped)) {
                    $licenseData = array_merge($licenseData, $mapped);
                    break; // Use first matching product
                }
            }
        }

        return $licenseData;
    }

    /**
     * Store license metadata in order
     *
     * @param \WC_Order $order Order object
     * @param License $license License object
     * @return void
     */
    private function storeLicenseInOrder(\WC_Order $order, License $license): void
    {
        $order->update_meta_data(Constants::WOOCOMMERCE_ORDER_META_LICENSE_KEY, $license->licenseKey);
        $order->update_meta_data(Constants::WOOCOMMERCE_ORDER_META_LICENSE_STATUS, $license->status);
        if ($license->id !== null) {
            $order->update_meta_data(Constants::WOOCOMMERCE_ORDER_META_LICENSE_ID, $license->id);
        }
        $order->save();
    }
}

