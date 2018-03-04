<?php
namespace Bosnadev\Repositories\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Bosnadev\Repositories\Console\Commands\Creators\CriteriaCreator;

class MakeCriteriaCommand extends Command {
    protected $name = 'make:criteria';
    protected $description = 'Create a new criteria class';

    protected $creator;
    protected $composer;

    public function __construct(CriteriaCreator $creator) {
        parent::__construct();

        $this->creator  = $creator;
        $this->composer = app()['composer'];
    }

    public function handle() {
        $arguments = $this->argument();
        $options   = $this->option();

        $this->writeCriteria($arguments, $options);
        $this->composer->dumpAutoloads();
    }
    public function writeCriteria($arguments, $options) {
        $criteria = $arguments['criteria'];
        $model = $options['model'];

        if ( $this->creator->create($criteria, $model) ) $this->info("Succesfully created the criteria class.");
    }
    protected function getArguments() {
        return [
            ['criteria', InputArgument::REQUIRED, 'The criteria name.']
        ];
    }
    protected function getOptions() {
        return [
            ['model', null, InputOption::VALUE_OPTIONAL, 'The model name.', null],
        ];
    }
}
