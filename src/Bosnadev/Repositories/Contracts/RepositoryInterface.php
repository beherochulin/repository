<?php
namespace Bosnadev\Repositories\Contracts;

interface RepositoryInterface {
    public function create(array $data);
    public function saveModel(array $data);
    public function update(array $data, $id);
    public function delete($id);
    public function all($columns = array('*'));
    public function paginate($perPage = 1, $columns = array('*'));
    public function find($id, $columns = array('*'));
    public function findBy($field, $value, $columns = array('*'));
    public function findAllBy($field, $value, $columns = array('*'));
    public function findWhere($where, $columns = array('*'));
}
