<?php
namespace InjectorTest;

require __DIR__ .'/vendor/autoload.php';

class TestCase extends \PHPUnit\Framework\TestCase
{
   public static function setUpBeforeClass()
    {
		$testFiles = glob(__DIR__ . '/tests/' . static::class . '/*.php');

		foreach ($testFiles as $testFile) {
			require_once $testFile;
		}
    }
}