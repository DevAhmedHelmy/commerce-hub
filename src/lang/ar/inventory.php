<?php

declare(strict_types=1);

return [

    'type' => [
        'initial' => 'رصيد افتتاحي',
        'manual_add' => 'إضافة يدوية',
        'manual_remove' => 'خصم يدوي',
        'order' => 'طلب',
        'order_cancel_restore' => 'استرجاع بعد الإلغاء',
        'correction' => 'تصحيح',
    ],

    'stock' => 'المخزون',
    'in_stock' => 'متوفر',
    'out_of_stock' => 'غير متوفر',
    'insufficient' => 'الكمية المطلوبة غير متوفرة حالياً',
    'low_stock' => 'مخزون منخفض',

    'actions' => [
        'add' => 'إضافة مخزون',
        'remove' => 'خصم مخزون',
        'correct' => 'تصحيح المخزون',
        'history' => 'سجل المخزون',
    ],

    'fields' => [
        'quantity' => 'الكمية',
        'target' => 'الرصيد الصحيح',
        'reason' => 'السبب',
        'current' => 'الرصيد الحالي',
        'resulting' => 'الرصيد بعد التعديل',
    ],

    'errors' => [
        'negative' => 'لا يمكن أن يكون المخزون أقل من صفر.',
    ],

];
