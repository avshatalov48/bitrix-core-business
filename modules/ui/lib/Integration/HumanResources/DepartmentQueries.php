<?php

namespace Bitrix\UI\Integration\HumanResources;

use Bitrix\HumanResources;
use Bitrix\Main\Loader;

Loader::requireModule('humanresources');

class DepartmentQueries
{
	private HumanResources\Service\Container $hrServiceLocator;

	public static function getInstance(): self
	{
		return new self();
	}

	private function __construct()
	{
		$this->hrServiceLocator = HumanResources\Service\Container::instance();
	}

	public function getDepartmentByAccessCode(string $accessCode): ?HumanResources\Item\Node
	{
		return $this->hrServiceLocator::getNodeRepository()->getByAccessCode($accessCode);
	}
}
