<?php

use ArekX\MiniDI\Injector;

use ArekX\MiniDI\Exception\CircularDependencyException;

class InjectorExceptionTest extends \InjectorTest\TestCase
{
    public function testCircularDependencyException()
    {
        $this->expectException(CircularDependencyException::class);

        Injector::create([
            'testObject' => '\InjectorExceptionTest\TestClassCircularException',
            'circularParam' => '\InjectorExceptionTest\TestClassCircularException'
        ])->get('testObject');
    }
}