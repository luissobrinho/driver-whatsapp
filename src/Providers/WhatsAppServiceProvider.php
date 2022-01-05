<?php

namespace BotMan\Drivers\WhatsApp\Providers;

use Illuminate\Support\ServiceProvider;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\WhatsApp\WhatsAppDriver;
use BotMan\Drivers\WhatsApp\WhatsAppFileDriver;
use BotMan\Drivers\WhatsApp\WhatsAppAudioDriver;
use BotMan\Drivers\WhatsApp\WhatsAppPhotoDriver;
use BotMan\Drivers\WhatsApp\WhatsAppVideoDriver;
use BotMan\Studio\Providers\StudioServiceProvider;
use BotMan\Drivers\WhatsApp\WhatsAppLocationDriver;
use BotMan\Drivers\WhatsApp\WhatsAppContactDriver;
use BotMan\Drivers\WhatsApp\Console\Commands\WhatsAppRegisterCommand;

class WhatsAppServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if (! $this->isRunningInBotManStudio()) {
            $this->loadDrivers();

            $this->publishes([
                __DIR__.'/../../stubs/whatsapp.php' => config_path('botman/whatsapp.php'),
            ]);

            $this->mergeConfigFrom(__DIR__.'/../../stubs/whatsapp.php', 'botman.whatsapp');

            $this->commands([
                WhatsAppRegisterCommand::class,
            ]);
        }
    }

    /**
     * Load BotMan drivers.
     */
    protected function loadDrivers()
    {
        DriverManager::loadDriver(WhatsAppDriver::class);
        DriverManager::loadDriver(WhatsAppAudioDriver::class);
        DriverManager::loadDriver(WhatsAppFileDriver::class);
        DriverManager::loadDriver(WhatsAppLocationDriver::class);
        DriverManager::loadDriver(WhatsAppContactDriver::class);
        DriverManager::loadDriver(WhatsAppPhotoDriver::class);
        DriverManager::loadDriver(WhatsAppVideoDriver::class);
    }

    /**
     * @return bool
     */
    protected function isRunningInBotManStudio()
    {
        return class_exists(StudioServiceProvider::class);
    }
}
