<?php

declare(strict_types=1);

namespace Bitrix\Main\Controller;

use Bitrix\Main\Engine;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Error;

/**
 * Class UserOption
 * @package Bitrix\Main\Controller
 *
 * This class represents a user option controller in a legacy way
 * @see modules/main/interface/user_options.php
 */
class UserOption extends Engine\JsonController
{
	public const ERROR_USER_NOT_AUTHORIZED = 'ERROR_USER_NOT_AUTHORIZED';

	protected function getDefaultPreFilters(): array
	{
		return array_merge(
			parent::getDefaultPreFilters(),
			[
				new ActionFilter\CloseSession(),
				new ActionFilter\HttpMethod(
					[ActionFilter\HttpMethod::METHOD_POST],
				),
			]
		);
	}

	public function saveOptionsAction(array $newValues): void
	{
		\CUserOptions::SetOptionsFromArray($newValues);
	}

	public function deleteOptionAction(string $category, string $name, bool $common = false): void
	{
		if (!($GLOBALS["USER"] instanceof \CUser))
		{
			$this->addError(new Error('User is not authorized', self::ERROR_USER_NOT_AUTHORIZED));

			return;
		}

		$currentUser = $GLOBALS["USER"];
		if ($common && !$currentUser->CanDoOperation('edit_other_settings'))
		{
			$common = false;
		}

		\CUserOptions::DeleteOption(
			$category,
			$name,
			$common,
			$currentUser->getId()
		);
	}
}
