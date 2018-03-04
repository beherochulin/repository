<?php
namespace Bosnadev\Repositories\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Bosnadev\Repositories\Console\Commands\Creators\RepositoryCreator;

class MakeRepositoryCommand extends Command {
    protected $name = 'make:repository';
    protected $description = 'Create a new repository class';

    protected $creator;
    protected $composer;

    public function __construct(RepositoryCreator $creator) {
        parent::__construct();

        $this->creator  = $creator;
        $this->composer = app()['composer'];
    }

    public function handle() {
        $arguments = $this->argument();
        $options   = $this->option();

        $this->writeRepository($arguments, $options);
        $this->composer->dumpAutoloads();
    }

    protected function writeRepository($arguments, $options) {
        $repository = $arguments['repository'];
        $model = $options['model'];

        if ( $this->creator->create($repository, $model) ) $this->info("Successfully created the repository class");
    }

    protected function getArguments() {
        return [
            ['repository', InputArgument::REQUIRED, 'The repository name.']
        ];
    }
    protected function getOptions() {
        return [
            ['model', null, InputOption::VALUE_OPTIONAL, 'The model name.', null],
        ];
    }
}
