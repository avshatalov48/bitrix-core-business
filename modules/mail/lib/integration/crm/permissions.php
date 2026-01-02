<?php

namespace Bitrix\Mail\Integration\Crm;

use Bitrix\Main\Loader;
use Bitrix\Crm\Service\Container;

class Permissions
{
	private static ?Permissions $instance = null;
	private bool $isCrmInstalled;

	public static function getInstance(): self
	{
		if (!isset(self::$instance))
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function hasAccessToCrm(): bool
	{
		return $this->isCrmInstalled && Container::getInstance()->getUserPermissions()->entityType()->canReadSomeItemsInCrm();
	}

	private function __construct()
	{
		$this->isCrmInstalled = Loader::includeModule('crm');
	}
}
