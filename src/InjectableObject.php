<?php

namespace ArekX\MiniDI;

abstract class InjectableObject implements Injectable
{
	public function getInjectables()
	{
		return null;
	}
}