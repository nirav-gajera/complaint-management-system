<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $settings = [
            [
                'group' => 'notifications',
                'key' => 'complaint_status_email',
                'label' => 'Complaint Status Email Notifications',
                'type' => 'boolean',
                'value' => true,
            ],
            [
                'group' => 'notifications',
                'key' => 'complaint_status_sms',
                'label' => 'Complaint Status SMS Notifications',
                'type' => 'boolean',
                'value' => false,
            ],
            [
                'group' => 'email',
                'key' => 'complaint_status_template',
                'label' => 'Complaint Status Email Template',
                'type' => 'textarea',
                'value' => ['body' => 'Your complaint status has been updated.'],
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::query()->updateOrCreate(
                ['key' => $setting['key']],
                $setting,
            );
        }
    }
}
