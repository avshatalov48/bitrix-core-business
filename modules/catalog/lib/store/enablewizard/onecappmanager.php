<?php

namespace Bitrix\Catalog\Store\EnableWizard;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Rest\AppTable;
use Bitrix\Crm\Integration\Market\Router;

class OnecAppManager
{
	private const APP_CODE = 'bitrix.1ctotal';
	private const INSTALL_TYPE = '1c_store_management';
	private const MIN_APP_VERSION = 5;

	public static function isAppInstalled(): bool
	{
		$app = self::getAppInfo();
		if (!$app)
		{
			return false;
		}

		$minVersion = (int)Option::get('catalog', 'bitrix_1ctotal_app_min_version', self::MIN_APP_VERSION);

		return $app['INSTALLED'] === 'Y' && (int)$app['VERSION'] >= $minVersion;
	}

	public static function getStatusUrl(): array
	{
		if (self::isAppInstalled())
		{
			$app = self::getAppInfo();

			return [
				'type' => 'app',
				'value' => isset($app['ID']) ? (int)$app['ID'] : 0,
			];
		}

		return [
			'type' => 'install',
			'value' => self::getInstallUrl(),
		];
	}

	public static function getInstallUrl(): string
	{
		if (!Loader::includeModule('crm'))
		{
			return '';
		}

		return Application::getInstance()->getRouter()->url(
			Router::getApplicationPath(self::APP_CODE),
			[
				'install' => 'Y',
				'install_type' => self::INSTALL_TYPE,
			]
		);
	}

	public static function onRestAppInstall($params): void
	{
		$localAppServer = Option::get('catalog', 'bitrix_1ctotal_app_local_server');
		if (!$localAppServer)
		{
			return;
		}

		if (!is_array($params) || !isset($params['APP_ID']))
		{
			return;
		}

		$appId = (int)$params['APP_ID'];
		if (!$appId)
		{
			return;
		}

		if (!Loader::includeModule('rest'))
		{
			return;
		}

		$appInfo = AppTable::getById($appId)->fetch();
		if (!$appInfo || $appInfo['CODE'] !== self::APP_CODE)
		{
			return;
		}

		AppTable::update(
			$appId,
			[
				'INSTALLED' => AppTable::INSTALLED,
				'URL' => $localAppServer . '/app/1ctotal/v4/index.php',
				'URL_INSTALL' => $localAppServer . '/app/1ctotal/v4/install.php',
			]
		);
	}

	private static function getAppInfo(): ?array
	{
		if (!Loader::includeModule('rest'))
		{
			return null;
		}

		$app = AppTable::getByClientId(self::APP_CODE);
		if (!is_array($app))
		{
			return null;
		}

		return $app;
	}
}
