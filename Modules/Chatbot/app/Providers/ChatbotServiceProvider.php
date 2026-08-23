<?php

namespace Modules\Chatbot\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Chatbot\Models\ChatbotSession;
use Modules\Chatbot\Policies\ChatbotSessionPolicy;
use Nwidart\Modules\Support\ModuleServiceProvider;

class ChatbotServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Chatbot';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'chatbot';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        Gate::policy(ChatbotSession::class, ChatbotSessionPolicy::class);
    }

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
