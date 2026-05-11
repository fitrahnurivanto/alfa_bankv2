<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "\n🔧 Seeding Settings for Alfa Bank...\n\n";

        $settings = [
            // Company Information
            ['key' => 'company_email', 'value' => 'mkt.alfayk@gmail.com'],
            ['key' => 'company_phone', 'value' => '6289671481943'],
            ['key' => 'company_website', 'value' => 'https://alfabank.com'],
            
            // Logo placeholders (will be uploaded later via Settings UI)
            ['key' => 'company_logo', 'value' => null],
            ['key' => 'company_favicon', 'value' => null],
            
            // System Settings
            ['key' => 'system_timezone', 'value' => 'Asia/Jakarta'],
            ['key' => 'system_currency', 'value' => 'IDR'],
            ['key' => 'system_language', 'value' => 'id'],
            
            // Features (Training/Pelatihan only - no Agency features)
            ['key' => 'feature_training', 'value' => 'true'],
            ['key' => 'feature_notifications', 'value' => 'true'],
            ['key' => 'feature_reports', 'value' => 'true'],
            
            // Tax & Financial
            ['key' => 'tax_rate', 'value' => '11'], // PPN 11%
            ['key' => 'tax_type', 'value' => 'PPN'],
            
            // Contact & Social Media
            ['key' => 'contact_whatsapp', 'value' => '6289671481943'],
            ['key' => 'contact_telegram', 'value' => null],
            ['key' => 'social_facebook', 'value' => null],
            ['key' => 'social_instagram', 'value' => null],
            ['key' => 'social_twitter', 'value' => null],
            ['key' => 'social_linkedin', 'value' => null],
            ['key' => 'social_youtube', 'value' => null],
            
            // Email Settings
            ['key' => 'mail_from_name', 'value' => 'ALFA BANK'],
            ['key' => 'mail_from_address', 'value' => 'mkt.alfayk@gmail.com'],
            
            // Invoice Settings
            ['key' => 'invoice_prefix', 'value' => 'INV/AB'],
            ['key' => 'invoice_next_number', 'value' => '1'],
            ['key' => 'invoice_footer', 'value' => 'Terima kasih telah mempercayai ALFA BANK untuk kebutuhan pelatihan Anda.'],
            
            // Class/Training Settings
            ['key' => 'class_min_participants', 'value' => '5'],
            ['key' => 'class_max_participants', 'value' => '30'],
            ['key' => 'class_booking_deadline_days', 'value' => '7'],
            
            // Payment Settings
            ['key' => 'payment_methods', 'value' => 'Bank Transfer,Cash,E-Wallet'],
            ['key' => 'bank_account_name', 'value' => 'ALFA BANK'],
            ['key' => 'bank_account_number', 'value' => '1234567890'],
            ['key' => 'bank_name', 'value' => 'Bank Mandiri'],
            
            // Notification Settings
            ['key' => 'notification_email', 'value' => 'true'],
            ['key' => 'notification_database', 'value' => 'true'],
            ['key' => 'notification_sms', 'value' => 'false'],
            
            // Theme & Branding
            ['key' => 'theme_primary_color', 'value' => '#3B82F6'], // Clean blue
            ['key' => 'theme_secondary_color', 'value' => '#6B7280'], // Gray
            ['key' => 'theme_style', 'value' => 'clean'], // clean, modern, classic
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }

        echo "✅ Settings created (" . count($settings) . " items)\n";
        echo "   Company: ALFA BANK\n";
        echo "   Email: mkt.alfayk@gmail.com\n";
        echo "   WhatsApp: +6289671481943\n\n";
        
        echo "==================================================\n";
        echo "  ALFA BANK SETTINGS SEEDED SUCCESSFULLY!\n";
        echo "==================================================\n";
        echo "  Theme: Clean White\n";
        echo "  Currency: IDR (11% PPN)\n";
        echo "  Timezone: Asia/Jakarta\n";
        echo "==================================================\n\n";
    }
}
