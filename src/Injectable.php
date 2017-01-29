<?php

namespace ArekX\MiniDI;

interface Injectable {
	public function __construct(Injector $injector, $config = []);
}