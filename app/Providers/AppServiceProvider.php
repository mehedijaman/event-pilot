<?php

namespace App\Providers;

use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->configureSettings();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureSettings(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        try {
            $settings = Setting::first();
        } catch (\Throwable) {
            return;
        }

        if (! $settings) {
            return;
        }

        if ($settings->site_name) {
            config(['app.name' => $settings->site_name]);
        }

        if ($settings->smtp_host) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $settings->smtp_host,
                'mail.mailers.smtp.port' => $settings->smtp_port,
                'mail.mailers.smtp.username' => $settings->smtp_username,
                'mail.mailers.smtp.password' => $settings->smtp_password,
                'mail.mailers.smtp.encryption' => $settings->smtp_encryption,
                'mail.from.address' => $settings->smtp_from_address ?? config('mail.from.address'),
                'mail.from.name' => $settings->smtp_from_name ?? config('mail.from.name'),
            ]);
        }
    }
}
