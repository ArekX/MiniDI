<?php

namespace ArekX\MiniDI;

trait InjectableTrait
{
	protected $reflection = null;
	protected $injectables = null;

	public function __construct(Injector $injector, $config = [])
	{
		$this->reflection = new \ReflectionClass(static::class);

		if ($this->injectables === null) {
			$publicProperties = $this->reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
			
			$this->injectables = [];

			foreach ($publicProperties as $property) {
				$this->injectables[] = $property->getName();
			}
		}

		$this->inject($injector);
		$this->configure($config);
	}

	public function inject(Injector $injector)
	{
		$thisClass = static::class;

		foreach ($this->injectables as $property => $injectorProperty) {
			if (is_numeric($property)) {
				$property = $injectorProperty;
			}

			$methodName = 'set' . ucfirst($property);

			if ($this->reflection->hasMethod($methodName)) {
				$this->{$methodName}($injector->get($injectorProperty));
			} elseif ($this->reflection->hasProperty($property)) {
				$this->{$property} = $injector->get($injectorProperty);
			} else {
				throw new InjectablePropertyException($property);
			}
		}
	}

	public function configure($config = [])
	{
       if (empty($config)) {
           return $this;
       }

       foreach ($config as $key => $value) {
           if (!$this->reflection->hasProperty($key) || !$this->reflection->getProperty($key)->isPublic()) {
               throw new InvalidConfigurationException($reflection, $key);
           }

           $this->{$key} = $value;
       }

       return $this;
	}
}