<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Operation;

use Bitrix\Socialnetwork\Control\GroupResult;
use Bitrix\Socialnetwork\Control\Mapper\Mapper;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;

abstract class AbstractOperation
{
	private Mapper $mapper;
	private GroupRegistry $registry;

	abstract public function run(): GroupResult;

	protected function getMapper(): Mapper
	{
		$this->mapper ??= new Mapper();

		return $this->mapper;
	}

	protected function getRegistry(): GroupRegistry
	{
		$this->registry ??= GroupRegistry::getInstance();

		return $this->registry;
	}
}