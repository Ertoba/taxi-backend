<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payout_method')->updateOrInsert(
            ['name' => 'keepz split receiver'],
            [
                'status' => 1,
                'module' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $settings = [
            'keepz_split_status' => 'Inactive',
            'keepz_split_fallback_to_main_receiver' => '0',
            'test_keepz_split_platform_iban' => '',
            'test_keepz_split_platform_mapping_confirmed' => '0',
            'live_keepz_split_platform_iban' => '',
            'live_keepz_split_platform_mapping_confirmed' => '0',
        ];

        foreach ($settings as $key => $value) {
            DB::table('general_settings')->updateOrInsert(
                ['meta_key' => $key],
                [
                    'meta_value' => $value,
                    'module' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('general_settings')->whereIn('meta_key', [
            'keepz_split_status',
            'keepz_split_fallback_to_main_receiver',
            'test_keepz_split_platform_iban',
            'test_keepz_split_platform_mapping_confirmed',
            'live_keepz_split_platform_iban',
            'live_keepz_split_platform_mapping_confirmed',
        ])->delete();

        DB::table('payout_method')
            ->where('name', 'keepz split receiver')
            ->update(['status' => 0, 'updated_at' => now()]);
    }
};
