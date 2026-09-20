<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Domain identifier labels (Arabic)
|--------------------------------------------------------------------------
|
| Presentation labels keyed by LANGUAGE-NEUTRAL domain identifiers. Business
| logic stores/branches only on the neutral value (e.g. `out_for_delivery`);
| these strings are resolved at the presentation boundary via the enum
| labelKey(). Adding English later is a matter of adding lang/en/domain.php —
| no schema change, no commerce-logic change (R13/R14).
|
*/

return [

    'order_status' => [
        'new' => 'جديد',
        'confirmed' => 'مؤكد',
        'preparing' => 'قيد التحضير',
        'out_for_delivery' => 'خارج للتوصيل',
        'delivered' => 'تم التوصيل',
        'cancelled' => 'ملغي',
    ],

    'availability' => [
        'available' => 'متوفر',
        'out_of_stock' => 'غير متوفر',
        'inactive' => 'غير نشط',
    ],

    'discount_type' => [
        'fixed' => 'خصم ثابت',
        'percentage' => 'نسبة مئوية',
        'free_delivery' => 'توصيل مجاني',
    ],

    'payment_method' => [
        'cod' => 'الدفع عند الاستلام',
    ],

    'applied_price_source' => [
        'normal' => 'السعر العادي',
        'tier' => 'سعر الجملة',
        'offer' => 'عرض',
    ],

    'unit_code' => [
        'bag' => 'كيس',
        'carton' => 'كرتونة',
        'pack' => 'عبوة',
        'bottle' => 'زجاجة',
        'box' => 'صندوق',
        'piece' => 'قطعة',
    ],

];
