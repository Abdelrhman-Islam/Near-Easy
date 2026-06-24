<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. URL إعادة تعيين الباسورد (موجود عندك أصلاً)
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        // 2. URL تفعيل الحساب (الـ Verification للـ API)
        VerifyEmail::createUrlUsing(function (object $notifiable) {
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            
            // بنكريت الرابط الآمن من لاراڤيل
            $secureUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            // بنأخذ الـ query string (الـ id والـ hash والـ expires) ونبعتها للفرونت
            $queryString = parse_url($secureUrl, PHP_URL_QUERY);

            return "{$frontendUrl}/verify-email?{$queryString}";
        });
    }
}