<?php

declare(strict_types=1);

namespace SimpleLicense\Vendor\Resources;

/**
 * License Resource
 * Represents a license entity
 */
class License
{
    public function __construct(
        public readonly string $licenseKey,
        public readonly string $status,
        public readonly ?string $customerEmail = null,
        public readonly ?string $tierCode = null,
        public readonly ?string $domain = null,
        public readonly ?int $activationLimit = null,
        public readonly ?int $activationCount = null,
        public readonly ?string $expiresAt = null,
        public readonly ?array $features = null,
        public readonly ?int $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null
    ) {
    }

    /**
     * Create License from API response data
     *
     * @param array<string, mixed> $data API response data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            licenseKey: $data['license_key'] ?? $data['licenseKey'] ?? '',
            status: $data['status'] ?? '',
            customerEmail: $data['customer_email'] ?? $data['customerEmail'] ?? null,
            tierCode: $data['tier_code'] ?? $data['tierCode'] ?? null,
            domain: $data['domain'] ?? null,
            activationLimit: isset($data['activation_limit']) ? (int) $data['activation_limit'] : null,
            activationCount: isset($data['activation_count']) ? (int) $data['activation_count'] : null,
            expiresAt: $data['expires_at'] ?? $data['expiresAt'] ?? null,
            features: $data['features'] ?? null,
            id: isset($data['id']) ? (int) $data['id'] : null,
            createdAt: $data['created_at'] ?? $data['createdAt'] ?? null,
            updatedAt: $data['updated_at'] ?? $data['updatedAt'] ?? null
        );
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'license_key' => $this->licenseKey,
            'status' => $this->status,
            'customer_email' => $this->customerEmail,
            'tier_code' => $this->tierCode,
            'domain' => $this->domain,
            'activation_limit' => $this->activationLimit,
            'activation_count' => $this->activationCount,
            'expires_at' => $this->expiresAt,
            'features' => $this->features,
            'id' => $this->id,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}

