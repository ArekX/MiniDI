<?php
namespace InjectorTest;

class TestClassAfterInit
{
    /**
     * @var TestClass1Param
     */
    public $testClass1;
    public $initParam;

    public function init()
    {
        $this->initParam = $this->testClass1->injectParam;
    }
}