<?php

class SettingsController
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function shipping($params)
    {
        $freeThreshold = 5000;
        $shippingCost = 100;

        $stmt = $this->pdo->prepare("SELECT `key`, `value` FROM site_settings WHERE `key` IN ('free_shipping_threshold', 'shipping_cost')");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            if ($row['key'] === 'free_shipping_threshold') {
                $freeThreshold = (int)$row['value'];
            }
            if ($row['key'] === 'shipping_cost') {
                $shippingCost = (int)$row['value'];
            }
        }

        json_success([
            'free_shipping_threshold' => $freeThreshold,
            'shipping_cost' => $shippingCost,
        ]);
    }
}
