<?php
namespace Bosnadev\Repositories\Criteria;

use Bosnadev\Repositories\Contracts\RepositoryInterface as Repository;

abstract class Criteria {
    public abstract function apply($model, Repository $repository);
}