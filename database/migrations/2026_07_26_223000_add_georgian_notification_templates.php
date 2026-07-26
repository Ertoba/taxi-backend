<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function translations(): array
    {
        return [
            1 => ['რეგისტრაცია წარმატებულია', 'რეგისტრაცია წარმატებით დასრულდა. კეთილი იყოს თქვენი მობრძანება {{website_name}}-ში! 🎉'],
            2 => ['თქვენი ერთჯერადი კოდია {{OTP}}', 'ერთჯერადი კოდი: {{OTP}}. თუ კოდი არ მოგითხოვიათ, უგულებელყავით შეტყობინება.'],
            3 => ['პაროლის აღდგენის უსაფრთხოების კოდი', 'პაროლის აღდგენის ერთჯერადი კოდია {{OTP}}. კოდი მოქმედებს 10 წუთის განმავლობაში.'],
            4 => ['თანხის გატანის მოთხოვნა მიღებულია', '{{first_name}}, {{payout_amount}}-ის გატანის მოთხოვნა მიღებულია და მუშავდება. შედეგს შეტყობინებით გაცნობებთ.'],
            6 => ['გადახდა წარმატებით დამუშავდა', 'გადახდა გაგზავნილია. გთხოვთ, გადაამოწმოთ საფულე ან გადახდის მეთოდი.'],
            7 => ['საფულის ტრანზაქცია', 'თქვენს საფულეზე შესრულდა {{transaction_type}} ოპერაცია: {{currency_code}} {{payout_amount}}.'],
            8 => ['ახალი შეტყობინება {{sender}}-ისგან', '{{sender}}-მა გამოგიგზავნათ ახალი შეტყობინება: „{{message}}...“'],
            12 => ['მძღოლის შეფასება მიღებულია', '{{vendor_name}}-მა შეგაფასათ. მგზავრობის ნომერია {{bookingid}}.'],
            13 => ['შეფასება გაგზავნილია', 'თქვენი შეფასება გაიგზავნა. მგზავრობის ნომერია {{bookingid}}.'],
            14 => ['მგზავრობა დასრულებულია #{{bookingid}}', 'მგზავრობა #{{bookingid}} დასრულებულია. მადლობა, რომ სარგებლობთ {{website_name}}-ით.'],
            18 => ['მძღოლმა მგზავრობა დაადასტურა #{{bookingid}}', 'მძღოლმა მგზავრობა #{{bookingid}} დაადასტურა. აყვანის დრო: {{check_in}}.'],
            22 => ['მგზავრობა გაუქმებულია #{{bookingid}}', 'მგზავრობა #{{bookingid}} გაუქმებულია. საჭიროების შემთხვევაში თანხის დაბრუნება დამუშავდება.'],
            26 => ['მგზავრობა უარყოფილია #{{bookingid}}', 'მგზავრობა #{{bookingid}} უარყოფილია. საჭიროების შემთხვევაში თანხის დაბრუნება დამუშავდება.'],
            34 => ['მძღოლის განაცხადი მიღებულია', 'მძღოლის განაცხადი წარმატებით გაიგზავნა. მადლობა! 🎉'],
            35 => ['მძღოლის განაცხადი დამტკიცებულია', 'გილოცავთ! თქვენი მძღოლის განაცხადი დამტკიცებულია. 🎉'],
            36 => ['ელფოსტის შეცვლის კოდი {{OTP}}', 'ელფოსტის შეცვლის ერთჯერადი კოდია {{OTP}}. თუ კოდი არ მოგითხოვიათ, უგულებელყავით შეტყობინება.'],
            37 => ['ერთჯერადი კოდი ხელახლა გაიგზავნა', 'თქვენი ერთჯერადი კოდია {{OTP}}. კოდი მოქმედებს 10 წუთის განმავლობაში.'],
            38 => ['მობილურის ნომრის შეცვლის კოდი {{OTP}}', 'მობილურის ნომრის შეცვლის ერთჯერადი კოდია {{OTP}}. თუ კოდი არ მოგითხოვიათ, უგულებელყავით შეტყობინება.'],
            39 => ['ავტომობილი გამოქვეყნებულია', 'გილოცავთ! ავტომობილი „{{title}}“ გამოქვეყნებულია {{website_name}}-ში. 🎉'],
            40 => ['ავტომობილი აღარ არის გამოქვეყნებული', 'ავტომობილი „{{title}}“ მოხსნილია გამოქვეყნებიდან.'],
            41 => ['მხარდაჭერის მოთხოვნა მიღებულია', 'მხარდაჭერის მოთხოვნა მიღებულია. მოთხოვნის ნომერია {{ticket_id}}.'],
            42 => ['მხარდაჭერის მოთხოვნაზე ახალი პასუხია', 'მხარდაჭერის მოთხოვნაზე მიღებულია ახალი პასუხი. მოთხოვნის ნომერია {{ticket_id}}.'],
            43 => ['გზავნილი ჩაბარებულია', 'გზავნილი „{{item_name}}“ წარმატებით ჩაბარდა მიმღებს.'],
            44 => ['გზავნილი მიღებულია', 'გზავნილი „{{item_name}}“ მიმღებმა წარმატებით მიიღო.'],
            45 => ['გზავნილი დაბრუნებულია', 'გზავნილი „{{item_name}}“ წარმატებით დაბრუნდა.'],
        ];
    }

    public function up(): void
    {
        $languageId = DB::table('languages')
            ->where('short_name', 'ka')
            ->value('id');
        if (! $languageId) {
            $languageId = DB::table('languages')->insertGetId([
                'name' => 'Georgian',
                'short_name' => 'ka',
                'language_status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($this->translations() as $id => [$subject, $push]) {
            $base = DB::table('email_sms_notification')->where('id', $id)->first();
            if (! $base || DB::table('email_sms_notification')
                ->where('temp_name', $base->temp_name)
                ->where('lang', 'ka')
                ->exists()) {
                continue;
            }

            $attributes = (array) $base;
            unset($attributes['id']);
            $attributes['lang'] = 'ka';
            $attributes['lang_id'] = $languageId;
            $attributes['subject'] = $subject;
            $attributes['push_notification'] = $push;
            $attributes['sms'] = $push;
            $attributes['vendorsubject'] = $subject;
            $attributes['vendorpush_notification'] = $push;
            $attributes['vendorsms'] = $push;
            $attributes['created_at'] = now();
            $attributes['updated_at'] = now();
            DB::table('email_sms_notification')->insert($attributes);
        }
    }

    public function down(): void
    {
        foreach ($this->translations() as [$subject]) {
            DB::table('email_sms_notification')
                ->where('lang', 'ka')
                ->where('subject', $subject)
                ->delete();
        }
    }
};
