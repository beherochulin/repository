<?php
namespace Bosnadev\Repositories\Console\Commands\Creators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Doctrine\Common\Inflector\Inflector;

class RepositoryCreator {
    protected $files;
    protected $repository;
    protected $model;

    public function __construct(Filesystem $files) {
        $this->files = $files;
    }

    public function getRepository() {
        return $this->repository;
    }
    public function setRepository($repository) {
        $this->repository = $repository;
    }
    public function getModel() {
        return $this->model;
    }
    public function setModel($model) {
        $this->model = $model;
    }

    // ## 创建
    public function create($repository, $model) {
        $this->setRepository($repository);
        $this->setModel($model);

        $this->createDirectory();
        return $this->createClass();
    }
    protected function createDirectory() { // 创建目录
        $directory = $this->getDirectory(); // 获取目录
        if ( !$this->files->isDirectory($directory) ) $this->files->makeDirectory($directory, 0755, true);
    }
    // ## 创建文件
    // # 内容替换
    protected function getRepositoryName() { // 获取仓库名称 加后缀
        $repository_name = $this->getRepository();
        if ( !strpos($repository_name, 'Repository') !== false ) $repository_name .= 'Repository';
        return $repository_name;
    }
    protected function stripRepositoryName() {
        $repository = strtolower($this->getRepository());
        $stripped   = str_replace("repository", "", $repository);
        $result = ucfirst($stripped);
        return $result;
    }
    protected function getModelName() { // 模型名称
        $model = $this->getModel();

        if ( isset($model) && !empty($model) ) {
            $model_name = $model;
        } else {
            $model_name = Inflector::singularize($this->stripRepositoryName());
        }

        return $model_name;
    }
    protected function getPopulateData() {
        $repository_namespace = Config::get('repositories.repository_namespace');
        $repository_class = $this->getRepositoryName();
        $model_path = Config::get('repositories.model_namespace');
        $model_name = $this->getModelName();

        $populate_data = [
            'repository_namespace' => $repository_namespace,
            'repository_class' => $repository_class,
            'model_path' => $model_path,
            'model_name' => $model_name
        ];
        return $populate_data;
    }
    // # 内容
    protected function getStubPath() { // 获取存根目录
        $stub_path = __DIR__ . '/../../../../../../resources/stubs/';
        return $stub_path;
    }
    protected function getStub() { // 获取存根内容
        $stub = $this->files->get($this->getStubPath() . "repository.stub");
        return $stub;
    }
    protected function populateStub() { // 存根内容替换
        $stub = $this->getStub();

        $populate_data = $this->getPopulateData();
        foreach ( $populate_data as $key => $value ) {
            $stub = str_replace($key, $value, $stub);
        }

        return $stub;
    }
    // # 目录
    protected function getDirectory() { // 获取目标目录
        $directory = Config::get('repositories.repository_path');
        return $directory;
    }
    protected function getPath() { // 获取目标路径
        $path = $this->getDirectory() . DIRECTORY_SEPARATOR . $this->getRepositoryName() . '.php';
        return $path;
    }
    // # 文件
    protected function createClass() {
        $result = $this->files->put($this->getPath(), $this->populateStub());
        return $result;
    }
}
