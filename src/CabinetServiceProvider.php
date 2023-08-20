<?php

namespace Cabinet;

use Cabinet\Services\SourceService;
use Filament\Support\Facades\FilamentView;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CabinetServiceProvider extends PackageServiceProvider
{
    public function register()
    {
        parent::register();

        $this->app->singleton(SourceService::class, function () {
            return new SourceService;
        });

        $this->app->singleton(Cabinet::class, function () {
            return new Cabinet;
        });

        $this->app->singleton('cabinet', Cabinet::class);
    }

    public function boot()
    {
        parent::boot();

        FilamentView::registerRenderHook(
            'panels::body.end',
            fn () => view('cabinet-filament::global-finder')
        );
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('cabinet')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations(
                'create_cabinet_directories_table',
                'create_cabinet_basic_files_table',
                'create_cabinet_file_refs_table',
            )
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    ->publishMigrations();
//                    ->copyAndRegisterServiceProviderInApp()
//                    ->askToStarRepoOnGitHub('capevace/cabinet');
            });;
    }
}
