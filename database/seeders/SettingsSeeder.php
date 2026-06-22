<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [

            // ── General ───────────────────────────────────────
            [
                'key'         => 'site_title',
                'value'       => 'My Application',
                'label'       => 'Site Title',
                'description' => 'Your website or app name shown in browser tab',
                'type'        => 'text',
                'group'       => 'general',
                'sort_order'  => 1,
            ],
            [
                'key'         => 'site_description',
                'value'       => 'Welcome to our platform',
                'label'       => 'Site Description',
                'description' => 'Short description of your website',
                'type'        => 'textarea',
                'group'       => 'general',
                'sort_order'  => 2,
            ],
            [
                'key'         => 'site_favicon',
                'value'       => null,
                'label'       => 'Favicon',
                'description' => 'Small icon shown in browser tab (32x32 recommended)',
                'type'        => 'file',
                'group'       => 'general',
                'sort_order'  => 3,
            ],
            [
                'key'         => 'footer_text',
                'value'       => '© 2025 My Application. All rights reserved.',
                'label'       => 'Footer Text',
                'description' => 'Text shown at the bottom of every page',
                'type'        => 'text',
                'group'       => 'general',
                'sort_order'  => 4,
            ],
            [
                'key'         => 'allow_user_refund_requests',
                'value'       => '0',
                'label'       => 'Allow Users to Request Refunds',
                'description' => 'If enabled, customers can request refunds for their completed payments directly from the portal. If disabled, refunds can only be initiated by admin/staff.',
                'type'        => 'boolean',
                'group'       => 'payments',
                'is_deletable'=> false,
                'sort_order'  => 1,
            ],

            // ── Appearance ────────────────────────────────────
            [
                'key'         => 'site_logo',
                'value'       => null,
                'label'       => 'Site Logo',
                'description' => 'Your brand logo (PNG with transparent background recommended)',
                'type'        => 'file',
                'group'       => 'appearance',
                'sort_order'  => 1,
            ],
            [
                'key'         => 'primary_color',
                'value'       => '#4f46e5',
                'label'       => 'Primary Color',
                'description' => 'Main brand color used across the platform',
                'type'        => 'text',
                'group'       => 'appearance',
                'sort_order'  => 2,
            ],

            // ── Currency ──────────────────────────────────────
            [
                'key'         => 'default_currency',
                'value'       => 'USD',
                'label'       => 'Default Currency',
                'description' => 'Currency used for all payments and transactions',
                'type'        => 'select',
                'options'     => ['USD' => 'USD - US Dollar', 'INR' => 'INR - Indian Rupee', 'EUR' => 'EUR - Euro', 'GBP' => 'GBP - British Pound', 'AED' => 'AED - UAE Dirham', 'PKR' => 'PKR - Pakistani Rupee'],
                'group'       => 'currency',
                'sort_order'  => 1,
            ],
            [
                'key'         => 'currency_symbol',
                'value'       => '$',
                'label'       => 'Currency Symbol',
                'description' => 'Symbol shown before price (e.g. $, ₹, €)',
                'type'        => 'text',
                'group'       => 'currency',
                'sort_order'  => 2,
            ],
            [
                'key'   => 'refund_subscription_policy',
                'value' => 'no_change',
                'label' => 'Subscription Policy on Partial Refund',
                'description' => 'Determines what happens to an active subscription when a partial refund is processed: no_change (access continues as-is), proportional (remaining access days reduced proportionally), or threshold (subscription cancelled if refund percentage exceeds the threshold).',
                'type'  => 'select',
                'options' => [
                    'no_change'    => 'No Change (goodwill refund)',
                    'proportional' => 'Reduce Access Proportionally',
                    'threshold'    => 'Cancel if Refund Exceeds Threshold',
                ],
                'group' => 'payment',
                'sort_order' => 9,
            ],
            [
                'key'   => 'refund_cancellation_threshold',
                'value' => '50',
                'label' => 'Refund Cancellation Threshold (%)',
                'description' => 'Used only when policy is "threshold". If the refunded percentage of a payment meets or exceeds this value, the subscription is cancelled.',
                'type'  => 'number',
                'group' => 'payment',
                'sort_order' => 10,
            ],

            // ── Payment Gateways ──────────────────────────────
            [
                'key'         => 'active_payment_gateway',
                'value'       => 'stripe',
                'label'       => 'Active Payment Gateway',
                'description' => 'Select which payment gateway to use for transactions',
                'type'        => 'select',
                'options'     => ['stripe' => 'Stripe', 'razorpay' => 'Razorpay', 'cashfree' => 'Cashfree', 'manual' => 'Manual / Cash'],
                'group'       => 'payment',
                'sort_order'  => 1,
            ],

            // Stripe
            [
                'key'         => 'stripe_public_key',
                'value'       => null,
                'label'       => 'Stripe Publishable Key',
                'description' => 'Your Stripe public key (starts with pk_)',
                'type'        => 'text',
                'group'       => 'payment',
                'sort_order'  => 2,
            ],
            [
                'key'         => 'stripe_secret_key',
                'value'       => null,
                'label'       => 'Stripe Secret Key',
                'description' => 'Your Stripe secret key (starts with sk_) — Keep this private!',
                'type'        => 'text',
                'group'       => 'payment',
                'sort_order'  => 3,
            ],
            [
                'key'         => 'stripe_webhook_secret',
                'value'       => null,
                'label'       => 'Stripe Webhook Secret',
                'description' => 'Webhook secret for verifying Stripe events',
                'type'        => 'text',
                'group'       => 'payment',
                'sort_order'  => 4,
            ],

            // Razorpay
            [
                'key'         => 'razorpay_key_id',
                'value'       => null,
                'label'       => 'Razorpay Key ID',
                'description' => 'Your Razorpay Key ID',
                'type'        => 'text',
                'group'       => 'payment',
                'sort_order'  => 5,
            ],
            [
                'key'         => 'razorpay_key_secret',
                'value'       => null,
                'label'       => 'Razorpay Key Secret',
                'description' => 'Your Razorpay Secret Key — Keep this private!',
                'type'        => 'text',
                'group'       => 'payment',
                'sort_order'  => 6,
            ],

            // Cashfree
            [
                'key'         => 'cashfree_app_id',
                'value'       => null,
                'label'       => 'Cashfree App ID',
                'description' => 'Your Cashfree App ID',
                'type'        => 'text',
                'group'       => 'payment',
                'sort_order'  => 7,
            ],
            [
                'key'         => 'cashfree_secret_key',
                'value'       => null,
                'label'       => 'Cashfree Secret Key',
                'description' => 'Your Cashfree Secret Key — Keep this private!',
                'type'        => 'text',
                'group'       => 'payment',
                'sort_order'  => 8,
            ],

            // ── Contact ───────────────────────────────────────
            [
                'key'         => 'contact_email',
                'value'       => null,
                'label'       => 'Support Email',
                'description' => 'Email address shown to users for support',
                'type'        => 'text',
                'group'       => 'contact',
                'sort_order'  => 1,
            ],
            [
                'key'         => 'contact_phone',
                'value'       => null,
                'label'       => 'Support Phone',
                'description' => 'Phone number shown to users for support',
                'type'        => 'text',
                'group'       => 'contact',
                'sort_order'  => 2,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'is_deletable' => false,
                    'options'      => isset($setting['options'])
                        ? json_encode($setting['options'])
                        : null,
                ])
            );
        }

        $this->command->info('✅ Settings seeded successfully!');
    }
}