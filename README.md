# MiniDI
Minimal PHP Dependency Injector

# Active Development

This library is currently in active development and not ready for use. Expect bugs.

# Usage

## Simple usage

```php
class TestClass1 {
	public $dependsOnTestClass2;
}

class TestClass2 {}

$injector = \ArekX\MiniDI\Injector::create([
	'testObject' => 'TestClass1',
	'dependsOnTestClass2' => 'TestClass2'
]);

$testObject = $injector->get('testObject');

echo $testObject->dependsOnTestClass2 instanceof TestClass2 ? 'Success!' : 'Fail'; // Outputs: Success!
```

## Recursive dependencies are also automatically resolved

```php
class TestClass1 {
	public $dependsOnTestClass2;
}

class TestClass2 {
	public $dependsOnTestClass3;
}

class TestClass3 {}

$injector = \ArekX\MiniDI\Injector::create([
	'testObject' => 'TestClass1',
	'dependsOnTestClass2' => 'TestClass2',
	'dependsOnTestClass3' => 'TestClass3',
]);

$testObject = $injector->get('testObject');

echo $testObject->dependsOnTestClass2->dependsOnTestClass3 instanceof TestClass3 ? 'Success!' : 'Fail'; // Outputs: Success!
```

## Shared objects

Objects can be shared easily across diffent objects by specifying `'shared' => true`.

```php
class TestClass1 {
	public $testClass2;
	public $sharedClass3;
}

class TestClass2 {
	public $sharedClass3;
}

class TestClass3 {}

$injector = \ArekX\MiniDI\Injector::create([
	'testObject' => 'TestClass1',
	'testClass2' => 'TestClass2',
	'sharedClass3' => ['class' => 'TestClass3', 'shared' => true],
]);

$testObject = $injector->get('testObject');

echo $testObject->sharedClass3 === $testObject->testClass2->sharedClass3 ? 'Same shared classes!' : 'Fail'; // Outputs: Same shared classes!
```

## Custom Config

You can specify which parameters will be injected yourself by setting `$injectables` property in your class.

```php
class TestClass1 implements \ArekX\MiniDI\Injectable {
	public $testClass2;
	public $mappedParam;
	public $notInjectedParameter;

	public function getInjectables() {

		return [
			'testClass2',
			'mappedParam' => 'sharedClass3' // This will take 'sharedClass3' from injector and put it into $mapppedParam of this class.
		];
	}
}

class TestClass2 {
	public $sharedClass3;
}

class TestClass3 {}

$injector = \ArekX\MiniDI\Injector::create([
	'testObject' => 'TestClass1',
	'testClass2' => 'TestClass2',
	'sharedClass3' => ['class' => 'TestClass3', 'shared' => true],
]);

$testObject = $injector->get('testObject');

echo $testObject->mappedParam === $testObject->testClass2->sharedClass3 ? 'Same shared classes!' : 'Fail'; // Outputs: Same shared classes!
```

You can also set specific custom configuration for configuring one injected object.

```php
class TestClass implements \ArekX\MiniDI\Injectable {
	public $class2;
	public $customParam;

	public function getInjectables() 
	{
		return ['class2']; // Only class2 parameter will be injected.
	}
}

class TestClass2 {}

$injector = \ArekX\MiniDI\Injector::create([
	'testObject' => [
		'class' => 'TestClass',
		'config' => [
			'customParam' => "Test String"
		]
	],
	'class2' => 'TestClass2'
]);

echo $injector->get('testObject')->customParam; // Outputs: Test String
```

# Installation

Install via composer

	composer require arekx/mini-di

# Testing

To run tests in injector you will need phpunit.phar in the directory of the project. 
Please run following command in cloned folder:

### Windows

In Command Prompt Run:

	test.bat

### Linux

In Terminal Run:

	./test.sh

# License

MIT License

Copyright (c) 2017 Aleksandar Panic

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.