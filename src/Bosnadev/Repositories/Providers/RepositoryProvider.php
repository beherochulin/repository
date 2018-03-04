<?php
namespace Bosnadev\Repositories\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Composer;
use Illuminate\Filesystem\Filesystem;
use Bosnadev\Repositories\Console\Commands\MakeCriteriaCommand;
use Bosnadev\Repositories\Console\Commands\MakeRepositoryCommand;
use Bosnadev\Repositories\Console\Commands\Creators\CriteriaCreator;
use Bosnadev\Repositories\Console\Commands\Creators\RepositoryCreator;

class RepositoryProvider extends ServiceProvider {
    protected $defer = true;

    public function boot() { // 拷贝配置
        $config_path = __DIR__ . '/../../../../config/repositories.php';

        $this->publishes(
            [$config_path => config_path('repositories.php')],
            'repositories'
        );
    }
    public function provides() { // 提供器
        return [
            'command.repository.make',
            'command.criteria.make'
        ];
    }
    public function register() { // 注册器
        $this->registerBindings();
        // # 注册命令
        // $this->registerMakeRepositoryCommand();
        // $this->registerMakeCriteriaCommand();
        // $this->commands(['command.repository.make', 'command.criteria.make']);
        $this->commands('Bosnadev\Repositories\Console\Commands\MakeRepositoryCommand');
        $this->commands('Bosnadev\Repositories\Console\Commands\MakeCriteriaCommand');
        // # 合并配置
        $config_path = __DIR__ . '/../../../../config/repositories.php';

        $this->mergeConfigFrom(
            $config_path,
            'repositories'
        );
    }
    protected function registerBindings() { // 注册绑定
        $this->app->instance('FileSystem', new Filesystem());
        $this->app->bind('Composer', function ($app) {
            return new Composer($app['FileSystem']);
        });
        $this->app->singleton('RepositoryCreator', function ($app) {
            return new RepositoryCreator($app['FileSystem']);
        });
        $this->app->singleton('CriteriaCreator', function ($app) {
            return new CriteriaCreator($app['FileSystem']);
        });
    }
    // make:repository
    // protected function registerMakeRepositoryCommand() {
        // $this->app['command.repository.make'] = $this->app->share(
        //     function($app) {
        //         return new MakeRepositoryCommand($app['RepositoryCreator'], $app['Composer']);
        //     }
        // );
    // }
    // make:criteria
    // protected function registerMakeCriteriaCommand() {
        // $this->app['command.criteria.make'] = $this->app->share(
        //     function($app) {
        //         return new MakeCriteriaCommand($app['CriteriaCreator'], $app['Composer']);
        //     }
        // );
    // }
}
