<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Authentication / onboarding copy (Arabic)
|--------------------------------------------------------------------------
| OTP outcome messages are keyed by neutral reason (auth.otp.<reason>) so logic
| never branches on translated text (R13/§39).
*/

return [

    'otp' => [
        'sent' => 'تم إرسال رمز التحقق إلى رقمك.',
        'resent' => 'تمت إعادة إرسال رمز التحقق.',
        'verified' => 'تم التحقق بنجاح.',
        'incorrect' => 'الرمز غير صحيح. حاول مرة أخرى.',
        'expired' => 'انتهت صلاحية الرمز. اطلب رمزًا جديدًا.',
        'locked' => 'محاولات كثيرة. يُرجى الانتظار قبل المحاولة مجددًا.',
        'cooldown' => 'يُرجى الانتظار :seconds ثانية قبل إعادة الإرسال.',
        'too_many_requests' => 'طلبات كثيرة. حاول بعد :seconds ثانية.',
    ],

    'onboarding' => [
        'complete' => 'اكتمل ملفك. يمكنك الآن الطلب.',
    ],

    'account_disabled' => 'تم إيقاف حسابك. يرجى التواصل مع الإدارة.',

    'fields' => [
        'phone' => 'رقم الجوال',
        'code' => 'رمز التحقق',
        'business_name' => 'اسم المنشأة',
        'contact_person_name' => 'اسم مسؤول الطلب',
        'whatsapp_phone' => 'رقم واتساب',
        'delivery_area' => 'منطقة التوصيل',
        'address_line' => 'العنوان',
        'building' => 'المبنى',
        'floor' => 'الطابق',
        'unit' => 'الوحدة',
        'landmark' => 'علامة مميزة',
        'delivery_notes' => 'ملاحظات التوصيل',
    ],

    'actions' => [
        'send_code' => 'إرسال الرمز',
        'verify' => 'تحقّق',
        'resend' => 'إعادة الإرسال',
        'continue' => 'متابعة',
    ],

    'screens' => [
        'login_title' => 'تسجيل الدخول',
        'login_subtitle' => 'أدخل رقم جوالك لاستلام رمز تحقق.',
        'verify_title' => 'أدخل رمز التحقق',
        'verify_subtitle' => 'أرسلنا رمزًا إلى :phone.',
        'profile_title' => 'أكمل ملف المنشأة',
        'address_title' => 'عنوان التوصيل',
    ],

];
