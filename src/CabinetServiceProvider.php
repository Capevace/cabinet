<?php

namespace Cabinet;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CabinetServiceProvider extends PackageServiceProvider
{
    public function register()
    {
        parent::register();

        $this->app->singleton(Cabinet::class, function () {
            return new Cabinet;
        });

        $this->app->singleton('cabinet', Cabinet::class);
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('cabinet')
            ->hasConfigFile()
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
