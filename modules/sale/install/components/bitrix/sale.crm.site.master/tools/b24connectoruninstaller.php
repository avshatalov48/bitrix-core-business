<?php
namespace Bitrix\Sale\CrmSiteMaster\Tools;

use Bitrix\Main,
	Bitrix\B24Connector;

/**
 * Class B24ConnectorUnInstaller
 * @package Bitrix\Sale\CrmSiteMaster\Tools
 */
class B24ConnectorUnInstaller
{
	const MODULE_NAME = "b24connector";
	/**
	 * @return bool
	 * @throws Main\LoaderException
	 */
	public function isModule()
	{
		return Main\Loader::includeModule(self::MODULE_NAME);
	}

	/**
	 * @return bool
	 */
	public function isSiteConnected()
	{
		return B24Connector\Connection::isExist();
	}

	/**
	 * @return Main\Result
	 */
	public function uninstallModule()
	{
		$result = new Main\Result();

		$module = \CModule::CreateModuleObject(self::MODULE_NAME);
		if (is_object($module))
		{
			$module->UnInstallDB();
			$module->UnInstallEvents();
			$module->UnInstallFiles();
		}

		/** @noinspection PhpVariableNamingConventionInspection */
		global $APPLICATION;
		if ($ex = $APPLICATION->GetException())
		{
			$result->addError(new Main\Error($ex->GetString()));
		}

		return $result;
	}
}