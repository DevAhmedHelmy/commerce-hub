<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Shared UI copy (Arabic)
|--------------------------------------------------------------------------
|
| Reusable, non-feature-specific presentation strings. No user-facing string
| is hard-coded in logic or Blade — components receive translated copy keyed
| here. Feature phases add their own message files/keys as needed.
|
*/

return [

    'app_name' => 'مستلزمات المطاعم',

    'states' => [
        'loading' => 'جارٍ التحميل…',
        'empty_title' => 'لا يوجد شيء هنا بعد',
        'empty_body' => 'لم يتم العثور على عناصر.',
        'error_title' => 'حدث خطأ ما',
        'error_body' => 'تعذّر تحميل المحتوى. يُرجى المحاولة مرة أخرى.',
    ],

    'actions' => [
        'retry' => 'إعادة المحاولة',
        'back' => 'رجوع',
        'save' => 'حفظ',
        'cancel' => 'إلغاء',
        'confirm' => 'تأكيد',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'search' => 'بحث',
    ],

    'offline' => [
        'title' => 'أنت غير متصل بالإنترنت',
        'body' => 'تحقّق من اتصالك ثم حاول مرة أخرى.',
    ],

];
