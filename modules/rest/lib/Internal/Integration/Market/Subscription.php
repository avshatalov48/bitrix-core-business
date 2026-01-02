<?php

declare(strict_types=1);

namespace Bitrix\Rest\Internal\Integration\Market;

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Result;
use Bitrix\Market\Subscription\Trial;

class Subscription
{
	private bool $isModuleIncluded;

	public function __construct()
	{
		$this->isModuleIncluded = Loader::includeModule('market');
	}

	public function activateDemo(): Result
	{
		$result = new Result();

		if (
			!$this->isModuleIncluded
			|| !$this->isDemoAvailable()
		)
		{
			return $result->addError(
				new Error('Subscription is not available')
			);
		}

		$res = Trial::activate();

		if (isset($res['error']))
		{
			$error = new Error(
				is_string($res['error']) ? $res['error'] : 'Unknown error',
				isset($res['error_code']) && (is_string($res['error_code']) || is_int($res['error_code']))
					? $res['error_code']
					: null,
			);

			return $result->addError($error);
		}

		return $result->setData($res);
	}

	private function isDemoAvailable(): bool
	{
		return Trial::isAvailable()
			&& (
				!ModuleManager::isModuleInstalled('extranet')
				|| (Loader::includeModule('extranet') && \CExtranet::IsIntranetUser())
			);
	}
}
