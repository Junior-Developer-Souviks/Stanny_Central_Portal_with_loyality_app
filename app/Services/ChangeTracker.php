<?php
namespace App\Services;
use App\Models\Changelog;

class ChangeTracker
{
    protected static array $changes = [];
    protected static ?int $orderId = null;

    /**
     * Set the current order context.
     */
    public static function setOrderId(int $orderId): void
    {
        self::$orderId = $orderId;
    }

    /**
     * Get the currently active order ID.
     */
    public static function getOrderId(): ?int
    {
        return self::$orderId;
    }

    /**
     * Clear order ID and collected changes.
     */
    public static function clear(): void
    {
        self::$changes = [];
        self::$orderId = null;
    }

    /**
     * Add a change record.
     */
    public static function add(string $type, array $data): void
    {
        // Attach the order_id if not already present in data
        if (!isset($data['order_id']) && self::$orderId !== null) {
            $data['order_id'] = self::$orderId;
        }
        self::$changes[$type][] = $data;
    }

    /**
     * Get all collected changes.
     */
    public static function getAll(): array
    {
        return self::$changes;
    }
}
?>
