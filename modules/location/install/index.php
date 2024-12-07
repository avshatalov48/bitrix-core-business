<?php

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

if(class_exists("location")) return;

Class location extends CModule
{
	var $MODULE_ID = "location";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $errors = [];

	public function __construct()
	{
		$arModuleVersion = [];

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = Loc::getMessage('LOCATION_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('LOCATION_MODULE_DESCRIPTION');
	}

	public function DoInstall()
	{
		global $APPLICATION;

		$this->InstallFiles();
		$this->InstallDB();
		$this->InstallEvents();

		$GLOBALS["errors"] = $this->errors;
		$APPLICATION->IncludeAdminFile(Loc::getMessage("LOCATION_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/location/install/step1.php");
	}

	public function setDefaultFormatCode()
	{
		if(!\Bitrix\Main\Loader::includeModule('location'))
		{
			return;
		}

		$event = new Event("location", "onInitialFormatCodeSet");
		$event->send();
		$results = $event->getResults();
		$formatCode = Bitrix\Location\Infrastructure\FormatCode::getDefault();

		if (is_array($results) && !empty($results))
		{
			foreach ($results as $result)
			{
				if ($result->getType() !== EventResult::SUCCESS)
					continue;

				$params = $result->getParameters();

				if(isset($params["formatCode"]))
				{
					$formatCode = $params["formatCode"];
					break;
				}
			}
		}

		Bitrix\Location\Infrastructure\FormatCode::setCurrent($formatCode);
	}

	public function installSources()
	{
		global $DB;

		$DB->query("
				INSERT INTO b_location_source (
					CODE,
					NAME,
					CONFIG
				) VALUES (
					 'GOOGLE',
					 'Google',
				     '" . $DB->forSql(serialize(
						[
							[
								'code' => 'API_KEY_FRONTEND',
								'type' => 'string',
								'sort' => 10,
								'value' => '',
							],
							[
								'code' => 'API_KEY_BACKEND',
								'type' => 'string',
								'sort' => 20,
								'value' => '',
							],
							[
								'code' => 'SHOW_PHOTOS_ON_MAP',
								'type' => 'bool',
								'sort' => 30,
								'value' => true,
							],
							[
								'code' => 'USE_GEOCODING_SERVICE',
								'type' => 'bool',
								'sort' => 40,
								'value' => true,
							],
						]
					)) . "'
				 );
		", true);

		$DB->query("
			INSERT INTO b_location_source (
				CODE,
				NAME,
				CONFIG
			)
			VALUES
			(
				'OSM',
				'OpenStreetMap',
				'" . $DB->ForSQL(serialize(
				[
					[
						'code' => 'SERVICE_URL',
						'type' => 'string',
						'sort' => 10,
						'value' => '',
						'is_visible' => true,
					],
					[
						'code' => 'TOKEN',
						'type' => 'string',
						'sort' => 20,
						'value' => null,
						'is_visible' => false,
					],
				]
			)) . "'
			)
		", true);
	}

	public function installAreas()
	{
		\CTimeZone::Disable();

		/**
		 * @see \Bitrix\Location\Infrastructure\DataInstaller::installAreasAgent()
		 */
		CAgent::AddAgent(
			"\\Bitrix\\Location\\Infrastructure\\DataInstaller::installAreasAgent();",
			"location",
			"N",
			2,
			'',
			'Y',
			\ConvertTimeStamp(time() + \CTimeZone::GetOffset() + 2, 'FULL')
		);

		\CTimeZone::Enable();
	}

	public function installConfigurer()
	{
		\CTimeZone::Disable();

		/**
		 * @see \Bitrix\Location\Source\Osm\Configurer::configure()
		 */
		CAgent::AddAgent(
			"\\Bitrix\\Location\\Source\\Osm\\Configurer::configure();",
			'location',
			'N',
			2,
			'',
			'Y',
			\ConvertTimeStamp(time() + \CTimeZone::GetOffset() + 2, 'FULL')
		);

		\CTimeZone::Enable();
	}

	public function installRecentAddressesCleaner()
	{
		\CTimeZone::Disable();

		/**
		 * @see \Bitrix\Location\Infrastructure\Service\RecentAddressesService::cleanUp()
		 */
		CAgent::AddAgent(
			"\\Bitrix\\Location\\Infrastructure\\Service\\RecentAddressesService::cleanUp();",
			'location',
			'N',
			86400,
			'',
			'Y',
			\ConvertTimeStamp(time() + \CTimeZone::GetOffset() + 3600, 'FULL')
		);

		\CTimeZone::Enable();
	}

	public function DoUninstall()
	{
		global $APPLICATION, $step;
		$step = intval($step);

		if($step < 2)
		{
			$APPLICATION->IncludeAdminFile(Loc::getMessage("LOCATION_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/location/install/unstep1.php");
		}
		elseif($step == 2)
		{
			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"] ?? null,
			));

			$this->UnInstallFiles();
			$this->UnInstallEvents();
			\CAgent::RemoveModuleAgents('location');

			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(Loc::getMessage("LOCATION_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/location/install/unstep2.php");
		}

		return true;
	}

	public function InstallDB()
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		if (!$DB->TableExists('b_location'))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/location/install/db/' . $connection->getType() . '/install.sql');
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}

		/*
		 * Reason: registerModule() could already be used in updaters
		 * bitrix24 20.5.100, bitrix24 20.5.200, location 20.5.1
		*/
		if(!\Bitrix\Main\ModuleManager::isModuleInstalled($this->MODULE_ID))
		{
			\Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
		}

		$this->installSources();
		$this->installAreas();
		$this->installConfigurer();
		$this->installRecentAddressesCleaner();
		$this->setDefaultFormatCode();

		return true;
	}

	public function UnInstallDB($arParams = Array())
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		if (array_key_exists('savedata', $arParams) && $arParams['savedata'] !== 'Y')
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/location/install/db/".$connection->getType()."/uninstall.sql");
		}

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode('', $this->errors));
			return false;
		}

		\Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

		return true;
	}

	public function InstallFiles($arParams = array())
	{
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/location/install/js', $_SERVER['DOCUMENT_ROOT'].'/bitrix/js', true, true);

		return true;
	}

	public function UnInstallFiles()
	{
		DeleteDirFilesEx('/bitrix/js/location/');

		return true;
	}

	public function InstallEvents()
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler("ui", "onUIFormInitialize", "location", "\\Bitrix\\Location\\Infrastructure\\EventHandler", "onUIFormInitialize");

		return true;
	}

	public function UnInstallEvents()
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler("ui", "onUIFormInitialize", "location", "\\Bitrix\\Location\\Infrastructure\\EventHandler", "onUIFormInitialize");

		return true;
	}
}
