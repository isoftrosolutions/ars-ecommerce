<?php

class CouponController
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function validate($params)
    {
        $data = get_json_input();
        validate_required($data, ['code']);
        ValidationErrors::throwIfInvalid();

        $code = strtoupper(trim($data['code']));
        $subtotal = (float)($data['subtotal'] ?? 0);

        $stmt = $this->pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
        $stmt->execute([$code]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$coupon) {
            json_success([
                'valid' => false,
                'message' => 'Invalid or expired coupon code',
            ]);
        }

        $now = date('Y-m-d H:i:s');
        if ($coupon['valid_from'] && $now < $coupon['valid_from']) {
            json_success([
                'valid' => false,
                'message' => 'This coupon is not yet valid',
            ]);
        }
        if ($coupon['valid_until'] && $now > $coupon['valid_until']) {
            json_success([
                'valid' => false,
                'message' => 'This coupon has expired',
            ]);
        }

        if ($subtotal < (float)$coupon['min_order']) {
            json_success([
                'valid' => false,
                'message' => 'Minimum order amount of ' . $coupon['min_order'] . ' required',
            ]);
        }

        $discount = 0;
        $discountType = $coupon['discount_type'];

        if ($discountType === 'percentage') {
            $discount = $subtotal * ((float)$coupon['discount_value'] / 100);
            if ((float)$coupon['max_discount'] > 0 && $discount > (float)$coupon['max_discount']) {
                $discount = (float)$coupon['max_discount'];
            }
        } else {
            $discount = (float)$coupon['discount_value'];
            if ($discount > $subtotal) {
                $discount = $subtotal;
            }
        }

        json_success([
            'valid' => true,
            'discount' => round($discount, 2),
            'discount_type' => $discountType,
            'message' => 'Coupon applied successfully',
        ]);
    }
}
