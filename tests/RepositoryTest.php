<?php
namespace Bosnadev\Tests\Repositories;

use \PHPUnit_Framework_TestCase as TestCase;
use \Mockery as m;

class RepositoryTest extends TestCase {
    protected $mock;
    protected $repository;

    public function setUp() {
        $this->mock = m::mock('Illuminate\Database\Eloquent\Model');
    }

    public function testRepository() {
        $this->assertTrue(true);
    }
}
