<?php
namespace InjectorMappingTest;

class TestClassOneParamSetter
{
    public $differentParam;
    public $notDependentParam = "I AM NOT SET";

    public function setDependentParam($value)
    {
        $this->differentParam = $value;
    }

    public function getInjectables()
    {
        return ['dependentParam'];
    }
}