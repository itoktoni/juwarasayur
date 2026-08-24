<?php

namespace Modules\Faq\Providers;

use App\Policies\FaqPolicy;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Faq\Models\Faq;
use Nwidart\Modules\Support\ModuleServiceProvider;

class FaqServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Faq';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'faq';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        Gate::policy(Faq::class, FaqPolicy::class);
    }

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
