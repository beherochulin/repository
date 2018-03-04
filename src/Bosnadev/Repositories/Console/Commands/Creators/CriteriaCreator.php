<?php
namespace Bosnadev\Repositories\Console\Commands\Creators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Doctrine\Common\Inflector\Inflector;

class CriteriaCreator {
    protected $files;
    protected $criteria;
    protected $model;

    public function __construct(Filesystem $files) {
        $this->files = $files;
    }

    public function getCriteria() { // 获取标准
        return $this->criteria;
    }
    public function setCriteria($criteria) { // 设置标准
        $this->criteria = $criteria;
    }
    public function getModel() { // 获取模型
        return $this->model;
    }
    public function setModel($model) { // 设置模型
        $this->model = $model;
    }

    // ## 创建
    public function create($criteria, $model) { // 主方法
        $this->setCriteria($criteria);
        $this->setModel($model);

        $this->createDirectory();
        return $this->createClass();
    }
    // ## 创建目录
    public function createDirectory() { // 创建目录
        $directory = $this->getDirectory(); // 获取目录
        if ( !$this->files->isDirectory($directory) ) $this->files->makeDirectory($directory, 0755, true);
    }
    // ## 创建文件
    // # 内容替换
    protected function pluralizeModel() { // 复数化模型
        $pluralized = Inflector::pluralize($this->getModel());
        $model_name = ucfirst($pluralized);
        return $model_name;
    }
    protected function getPopulateData() { // 路径信息
        $criteria_namespace = Config::get('repositories.criteria_namespace'); // 命名空间
        $model = $this->pluralizeModel(); // 复数化模型
        if ( isset($model) && !empty($model) ) $criteria_namespace .= '\\' . $model; // 命名空间
        $criteria = $this->getCriteria(); // 获取标准
        $criteria_class = $criteria; // 类名

        $populate_data = [
            'criteria_namespace' => $criteria_namespace,
            'criteria_class'     => $criteria_class
        ];
        return $populate_data;
    }
    // # 内容
    protected function getStubPath() { // 获取存根目录
        $path = __DIR__ . '/../../../../../../resources/stubs/';
        return $path;
    }
    protected function getStub() { // 获取存根内容
        $stub = $this->files->get($this->getStubPath() . "criteria.stub");
        return $stub;
    }
    protected function populateStub() { // 存根内容替换
        $stub = $this->getStub(); // 目录

        $populate_data = $this->getPopulateData(); // 路径信息
        foreach ( $populate_data as $search => $replace ) {
            $stub = str_replace($search, $replace, $stub);
        }

        return $stub;
    }
    // # 路径
    public function getDirectory() { // 获取目标目录
        $directory = Config::get('repositories.criteria_path');
        $model = $this->getModel();
        if ( isset($model) && !empty($model) ) $directory .= DIRECTORY_SEPARATOR . $this->pluralizeModel();
        return $directory;
    }
    protected function getPath() { // 获取目标路径
        $path = $this->getDirectory() . DIRECTORY_SEPARATOR . $this->getCriteria() . '.php';
        return $path;
    }
    // # 文件
    protected function createClass() { // 创建类
        $result = $this->files->put($this->getPath(), $this->populateStub());
        return $result;
    }
}
