<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait GeneratesTokens
{
    /**
     * Boot the trait for the model.
     * Laravel automatically calls "boot{TraitName}" when model boots.
     */
    protected static function bootGeneratesTokens(): void
    {
        static::creating(function ($model) {
            $tokenColumn = $model->getTokenColumnName();
            $keyColumn   = $model->getKeyIdColumnName();

            // 1. UUID Token
            // bootGeneratesTokens mein yeh check add karo
            if (empty($model->{$tokenColumn})) {
                // ✅ Sirf tab generate karo jab column empty string nahi hai
                if (!empty($tokenColumn)) {
                    $model->{$tokenColumn} = (string) Str::uuid();
                }
            }

            // 2. Numeric Key ID — DB unique constraint par rely karo
            if (empty($model->{$keyColumn})) {
                $digits = max(1, min(
                    method_exists($model, 'getKeyIdDigits') ? $model->getKeyIdDigits() : 8,
                    18
                ));
                
                $min = (int) pow(10, $digits - 1);
                $max = (int) pow(10, $digits) - 1;

                $model->{$keyColumn} = (string) random_int($min, $max);
                // DB column par UNIQUE INDEX hona ZAROORI hai
                // Race condition handle hogi retry middleware ya DB exception se
            }
        });
    }

    /**
     * Override these in your model to use custom column names.
     *
     * Example (User model):
     *   public function getTokenColumnName(): string { return 'user_token_keyid'; }
     *   public function getKeyIdColumnName(): string { return 'user_key_id'; }
     */
    public function getTokenColumnName(): string
    {
        return 'token';
    }

    public function getKeyIdColumnName(): string
    {
        return 'key_id';
    }
}