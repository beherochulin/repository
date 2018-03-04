<?php
namespace Bosnadev\Repositories\Eloquent;

use Bosnadev\Repositories\Contracts\RepositoryInterface;
use Bosnadev\Repositories\Contracts\CriteriaInterface;

use Bosnadev\Repositories\Exceptions\RepositoryException;
use Bosnadev\Repositories\Criteria\Criteria;

use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class Repository implements RepositoryInterface, CriteriaInterface {
    private $app;
    protected $criteria;
    protected $model;
    protected $newModel;
    protected $skipCriteria = false;
    protected $preventCriteriaOverwriting = true;

    public function __construct(App $app, Collection $collection) {
        $this->app = $app;
        $this->criteria = $collection;

        $this->resetScope();
        $this->makeModel();
    }
    public abstract function model();

    public function all($columns = array('*')) { // 所有记录
        $this->applyCriteria();
        return $this->model->get($columns);
    }
    public function paginate($perPage = 25, $columns = array('*')) { // 分页记录
        $this->applyCriteria();
        return $this->model->paginate($perPage, $columns);
    }
    public function lists($value, $key = null) { // 列表所有
        $this->applyCriteria();
        $lists = $this->model->lists($value, $key);
        if ( is_array($lists) ) return $lists;
        return $lists->all();
    }
    public function create(array $data) { // 创建
        return $this->model->create($data);
    }
    public function update(array $data, $id, $attribute = "id") { // 更新
        return $this->model->where($attribute, '=', $id)->update($data);
    }
    public function saveModel(array $data) {
        foreach ( $data as $k => $v ) {
            $this->model->$k = $v;
        }
        return $this->model->save();
    }
    public function updateRich(array $data, $id) {
        if ( !($model = $this->model->find($id)) ) return false;
        return $model->fill($data)->save();
    }
    public function delete($id) { // 删除
        return $this->model->destroy($id);
    }
    public function find($id, $columns = array('*')) { // id 查找
        $this->applyCriteria();
        return $this->model->find($id, $columns);
    }
    public function findBy($attribute, $value, $columns = array('*')) { // 字段查找
        $this->applyCriteria();
        return $this->model->where($attribute, '=', $value)->first($columns);
    }
    public function findAllBy($attribute, $value, $columns = array('*')) { // 字段查找所有
        $this->applyCriteria();
        return $this->model->where($attribute, '=', $value)->get($columns);
    }
    public function findWhere($where, $columns = ['*'], $or = false) { // 条件查找
        $this->applyCriteria();

        $model = $this->model;

        foreach ( $where as $field => $value ) {
            if ( $value instanceof \Closure ) {
                $model = (!$or)
                    ? $model->where($value)
                    : $model->orWhere($value);
            } elseif ( is_array($value) ) {
                if ( count($value) === 3 ) {
                    list($field, $operator, $search) = $value;
                    $model = (!$or)
                        ? $model->where($field, $operator, $search)
                        : $model->orWhere($field, $operator, $search);
                } elseif ( count($value) === 2 ) {
                    list($field, $search) = $value;
                    $model = (!$or)
                        ? $model->where($field, '=', $search)
                        : $model->orWhere($field, '=', $search);
                }
            } else {
                $model = (!$or)
                    ? $model->where($field, '=', $value)
                    : $model->orWhere($field, '=', $value);
            }
        }

        return $model->get($columns);
    }

    public function with(array $relations) {
        $this->model = $this->model->with($relations);
        return $this;
    }
    public function makeModel() {
        return $this->setModel($this->model());
    }
    public function setModel($eloquentModel) { // 设置模型
        $this->newModel = $this->app->make($eloquentModel);
        if ( !$this->newModel instanceof Model ) throw new RepositoryException("Class {$this->newModel} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        return $this->model = $this->newModel;
    }

    public function resetScope() {
        $this->skipCriteria(false);
        return $this;
    }
    public function skipCriteria($status = true) {
        $this->skipCriteria = $status;
        return $this;
    }
    public function getCriteria() {
        return $this->criteria;
    }
    public function getByCriteria(Criteria $criteria) {
        $this->model = $criteria->apply($this->model, $this);
        return $this;
    }
    public function pushCriteria(Criteria $criteria) {
        if ( $this->preventCriteriaOverwriting ) {
            $key = $this->criteria->search(function ($item) use ($criteria) { // Find existing criteria
                return (is_object($item) && (get_class($item) == get_class($criteria)));
            });

            if ( is_int($key) ) $this->criteria->offsetUnset($key); // Remove old criteria
        }

        $this->criteria->push($criteria);
        return $this;
    }
    public function applyCriteria() {
        if ( $this->skipCriteria === true ) return $this;

        foreach ( $this->getCriteria() as $criteria ) {
            if ( $criteria instanceof Criteria ) $this->model = $criteria->apply($this->model, $this);
        }

        return $this;
    }
}
