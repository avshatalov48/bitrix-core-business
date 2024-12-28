<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Result;
use Bitrix\SocialNetwork\Collab\Access\CollabAccessController;
use Bitrix\SocialNetwork\Collab\Access\CollabDictionary;
use Bitrix\SocialNetwork\Collab\Access\Model\CollabModel;
use Bitrix\Socialnetwork\Collab\Integration\Extranet\Extranet;

class Requirement
{
	public const REQUIRED_MODULES = [
		'intranet',
		'extranet',
		'im',
		'tasks',
		'calendar',
		'disk',
		'humanresources',
	];

	public static function checkWithAccess(int $userId): Result
	{
		$result = static::check();

		if (!$result->isSuccess())
		{
			return $result;
		}

		$accessController = CollabAccessController::getInstance($userId);
		$accessController->check(CollabDictionary::CREATE, new CollabModel());

		$result->addErrors($accessController->getErrors());

		return $result;
	}

	public static function check(): Result
	{
		$result = static::checkRequiredModules();

		if (!$result->isSuccess())
		{
			return $result;
		}

		return static::checkExtranetConfigured();
	}

	public static function checkRequiredModules(): Result
	{
		$result = new Result();

		$uninstalledModules = [];
		foreach (static::REQUIRED_MODULES as $module)
		{
			if (!ModuleManager::isModuleInstalled($module))
			{
				$uninstalledModules[] = $module;
			}
		}

		if (empty($uninstalledModules))
		{
			return $result;
		}

		$uninstalledModules = implode(', ', $uninstalledModules);

		$error = new Error(Loc::getMessage('SOCIALNETWORK_COLLAB_REQUIREMENT_UNINSTALLED_MODULES', [
			'#MODULES#' => $uninstalledModules,
		]));

		$result->addError($error);

		return $result;
	}

	public static function checkExtranetConfigured(): Result
	{
		$result = new Result();

		$extranetSiteId = Extranet::getSiteId();
		if (!empty($extranetSiteId))
		{
			return $result;
		}

		$error = new Error(Loc::getMessage('SOCIALNETWORK_COLLAB_REQUIREMENT_NOT_CONFIGURED_EXTRANET_SITE'));

		$result->addError($error);

		return $result;
	}
}