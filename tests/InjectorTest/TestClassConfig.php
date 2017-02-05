<?php
namespace InjectorTest;

class TestClassConfig
{
    public $param;
    public $secondParam;

    public function __construct($config = [])
    {
        if (!empty($config['param'])) {
            $this->param = $config['param'];
        }

        if (!empty($config['param2'])) {
            $this->secondParam = $config['param2'];
        }
    }

    public function getParam()
    {
        return $this->param;
    }

    public function getSecondParam()
    {
        return $this->secondParam;
    }
}