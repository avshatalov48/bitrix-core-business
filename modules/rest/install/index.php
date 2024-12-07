<?
IncludeModuleLangFile(__FILE__);

if(class_exists("rest")) return;
class rest extends CModule
{
	var $MODULE_ID = "rest";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "N";

	private $errors = false;

	public function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("REST_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("REST_MODULE_DESCRIPTION");
	}

	function InstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		if (!$DB->TableExists('b_rest_app'))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/rest/install/db/' . $connection->getType() . '/install.sql');
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		RegisterModule("rest");

		COption::SetOptionString("rest", "server_path", "/rest");

		$eventManager = \Bitrix\Main\EventManager::getInstance();

		$eventManager->registerEventHandler("main", "OnBeforeProlog", "rest", "CRestEventHandlers", "OnBeforeProlog", 49);

		$eventManager->registerEventHandler('rest', 'OnRestServiceBuildDescription', 'rest', 'CBitrixRestEntity', 'OnRestServiceBuildDescription');
		$eventManager->registerEventHandler('rest', 'OnRestServiceBuildDescription', 'rest', '\Bitrix\Rest\Api\User', 'onRestServiceBuildDescription');
		$eventManager->registerEventHandler('rest', 'OnRestServiceBuildDescription', 'rest', '\Bitrix\Rest\Api\Placement', 'onRestServiceBuildDescription');
		$eventManager->registerEventHandler('rest', 'OnRestServiceBuildDescription', 'rest', '\Bitrix\Rest\Api\Event', 'onRestServiceBuildDescription');
		$eventManager->registerEventHandler('rest', 'OnRestServiceBuildDescription', 'rest', '\Bitrix\Rest\Api\UserFieldType', 'onRestServiceBuildDescription');
		$eventManager->registerEventHandler("rest","onFindMethodDescription", "rest","\\Bitrix\\Rest\\Engine\\RestManager","onFindMethodDescription");
		$eventManager->registerEventHandler("main", "OnApplicationsBuildList", "main", '\Bitrix\Rest\APAuth\Application', "onApplicationsBuildList", 100, "modules/rest/lib/apauth/application.php");
		$eventManager->registerEventHandler("im", "OnAfterConfirmNotify", "rest", "\\Bitrix\\Rest\\NotifyIm", "receive");
		$eventManager->registerEventHandler("rest", "\\Bitrix\\Rest\\APAuth\\Password::OnDelete", "rest", "\\Bitrix\\Rest\\APAuth\\PermissionTable", "onPasswordDelete");
		$eventManager->registerEventHandler("perfmon", "OnGetTableSchema", "rest", "rest", "OnGetTableSchema");
		$eventManager->registerEventHandler('rest', 'OnRestApplicationConfigurationImport', 'rest', '\Bitrix\Rest\Configuration\AppConfiguration', 'onEventImportController');
		$eventManager->registerEventHandler('rest', 'OnRestApplicationConfigurationExport', 'rest', '\Bitrix\Rest\Configuration\AppConfiguration', 'onEventExportController');
		$eventManager->registerEventHandler('rest', 'OnRestApplicationConfigurationClear', 'rest', '\Bitrix\Rest\Configuration\AppConfiguration', 'onEventClearController');
		$eventManager->registerEventHandler('rest', 'OnRestApplicationConfigurationEntity', 'rest', '\Bitrix\Rest\Configuration\AppConfiguration', 'getEntityList');
		$eventManager->registerEventHandler('rest', 'OnRestApplicationConfigurationGetManifest', 'rest', '\Bitrix\Rest\Configuration\AppConfiguration', 'getManifestList');
		$eventManager->registerEventHandler('main', 'OnAfterSetOption_~mp24_paid_date', 'rest', '\Bitrix\Rest\Marketplace\Client', 'onChangeSubscriptionDate');
		if(CModule::IncludeModule('iblock'))
		{
			COption::SetOptionString("rest", "entity_iblock_type", "rest_entity");

			$arFields = array(
				'ID' => 'rest_entity',
				'SECTIONS' => 'Y',
				'IN_RSS' => 'N',
				'SORT' => 1000,
				'LANG' => array(
					LANGUAGE_ID => array(
						'NAME' => GetMessage('REST_IBLOCK_NAME_2'),
						'SECTION_NAME' => GetMessage('REST_IBLOCK_SECTION_NAME'),
						'ELEMENT_NAME' => GetMessage('REST_IBLOCK_ELEMENT_NAME'),
					)
				)
			);

			$dbRes = CIBlockType::GetByID($arFields['ID']);
			if(!$dbRes->Fetch())
			{
				$obBlocktype = new CIBlockType;
				$obBlocktype->Add($arFields);
			}
		}

		if(!\Bitrix\Main\ModuleManager::isModuleInstalled("oauth"))
		{
			$eventManager->registerEventHandler("rest", "onRestCheckAuth", "rest", "\\Bitrix\\Rest\\OAuth\\Auth", "onRestCheckAuth");
		}

		$eventManager->registerEventHandler("rest", "onRestCheckAuth", "rest", "\\Bitrix\\Rest\\APAuth\\Auth", "onRestCheckAuth");
		$eventManager->registerEventHandler("rest", "onRestCheckAuth", "rest", "\\Bitrix\\Rest\\SessionAuth\\Auth", "onRestCheckAuth");
		$eventManager->registerEventHandler(
			'main',
			'OnAfterRegisterModule',
			'rest',
			'\Bitrix\Rest\Engine\ScopeManager',
			'onChangeRegisterModule'
		);
		$eventManager->registerEventHandler(
			'main',
			'OnAfterUnRegisterModule',
			'rest',
			'\Bitrix\Rest\Engine\ScopeManager',
			'onChangeRegisterModule'
		);
		$eventManager->registerEventHandler(
			'main',
			'OnAfterRegisterModule',
			'rest',
			'\Bitrix\Rest\Marketplace\TagTable',
			'onAfterRegisterModule'
		);
		$eventManager->registerEventHandler(
			'main',
			'OnAfterUnRegisterModule',
			'rest',
			'\Bitrix\Rest\Marketplace\TagTable',
			'onAfterUnRegisterModule'
		);

		\CAgent::AddAgent("Bitrix\\Rest\\Marketplace\\Client::getNumUpdates();", "rest", "N", 86400);
		\CAgent::AddAgent("Bitrix\\Rest\\EventOfflineTable::cleanProcessAgent();", "rest", "N", 86400);
		\CAgent::AddAgent("Bitrix\\Rest\\LogTable::cleanUpAgent();", "rest", "N", 86400);
		\CAgent::AddAgent('\Bitrix\Rest\Configuration\Helper::sendStatisticAgent();', "rest", "N",86400);
		\CAgent::AddAgent('\\Bitrix\\Rest\\UsageStatTable::sendAgent();', "rest", "N", 3600);
		\CAgent::AddAgent('\\Bitrix\\Rest\\UsageStatTable::cleanUpAgent();', "rest", "N", 3600);
		\CAgent::AddAgent('\Bitrix\Rest\Marketplace\Notification::checkAgent();', "rest", "N", 86400);
		\CAgent::AddAgent('\Bitrix\Rest\Marketplace\Immune::load();', "rest", "N", 86400);
		\CAgent::AddAgent('\Bitrix\Rest\Configuration\Structure::clearContentAgent();', 'rest', 'N', 86400);
		\CAgent::AddAgent('\Bitrix\Rest\Helper::recoveryAgents();','rest','N',604800);

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rest/install/db/".$connection->getType()."/uninstall.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		$eventManager = \Bitrix\Main\EventManager::getInstance();

		$eventManager->unRegisterEventHandler("main", "OnBeforeProlog", "rest", "CRestEventHandlers", "OnBeforeProlog");

		$eventManager->unRegisterEventHandler('rest', 'OnRestServiceBuildDescription', 'rest', 'CBitrixRestEntity', 'OnRestServiceBuildDescription');
		$eventManager->unRegisterEventHandler('rest', 'OnRestServiceBuildDescription', 'rest', '\Bitrix\Rest\Api\User', 'onRestServiceBuildDescription');
		$eventManager->unRegisterEventHandler('rest', 'OnRestServiceBuildDescription', 'rest', '\Bitrix\Rest\Api\Placement', 'onRestServiceBuildDescription');
		$eventManager->unRegisterEventHandler('rest', 'OnRestServiceBuildDescription', 'rest', '\Bitrix\Rest\Api\Event', 'onRestServiceBuildDescription');
		$eventManager->unRegisterEventHandler('rest', 'OnRestServiceBuildDescription', 'rest', '\Bitrix\Rest\Api\UserFieldType', 'onRestServiceBuildDescription');
		$eventManager->unRegisterEventHandler("rest","onFindMethodDescription", "rest","\\Bitrix\\Rest\\Engine\\RestManager","onFindMethodDescription");
		$eventManager->unRegisterEventHandler("rest", "onRestCheckAuth", "rest", "\\Bitrix\\Rest\\OAuth\\Auth", "onRestCheckAuth");
		$eventManager->unRegisterEventHandler("rest", "onRestCheckAuth", "rest", "\\Bitrix\\Rest\\APAuth\\Auth", "onRestCheckAuth");
		$eventManager->unRegisterEventHandler("rest", "onRestCheckAuth", "rest", "\\Bitrix\\Rest\\SessionAuth\\Auth", "onRestCheckAuth");
		$eventManager->unRegisterEventHandler("main", "OnApplicationsBuildList", "main", '\Bitrix\Rest\APAuth\Application', "onApplicationsBuildList", "modules/rest/lib/apauth/application.php");
		$eventManager->unRegisterEventHandler("im", "OnAfterConfirmNotify", "rest", "\\Bitrix\\Rest\\NotifyIm", "receive");
		$eventManager->unRegisterEventHandler("rest", "\\Bitrix\\Rest\\APAuth\\Password::OnDelete", "rest", "\\Bitrix\\Rest\\APAuth\\PermissionTable", "onPasswordDelete");
		$eventManager->unRegisterEventHandler("rest", "OnRestServiceBuildDescription", "rest", "\\Bitrix\\Rest\\Engine\\RestManager", "OnRestServiceBuildDescription");
		$eventManager->unRegisterEventHandler("perfmon", "OnGetTableSchema", "rest", "rest", "OnGetTableSchema");
		$eventManager->unRegisterEventHandler('rest', 'OnRestApplicationConfigurationImport', 'rest', '\Bitrix\Rest\Configuration\AppConfiguration', 'onEventImportController');
		$eventManager->unRegisterEventHandler('rest', 'OnRestApplicationConfigurationExport', 'rest', '\Bitrix\Rest\Configuration\AppConfiguration', 'onEventExportController');
		$eventManager->unRegisterEventHandler('rest', 'OnRestApplicationConfigurationClear', 'rest', '\Bitrix\Rest\Configuration\AppConfiguration', 'onEventClearController');
		$eventManager->unRegisterEventHandler('rest', 'OnRestApplicationConfigurationEntity', 'rest', '\Bitrix\Rest\Configuration\AppConfiguration', 'getEntityList');
		$eventManager->unRegisterEventHandler('rest', 'OnRestApplicationConfigurationGetManifest', 'rest', '\Bitrix\Rest\Configuration\AppConfiguration', 'getManifestList');
		$eventManager->unRegisterEventHandler('main', 'OnAfterSetOption_~mp24_paid_date', 'rest', '\Bitrix\Rest\Marketplace\Client', 'onChangeSubscriptionDate');
		$eventManager->unRegisterEventHandler(
			'main',
			'OnAfterRegisterModule',
			'rest',
			'\Bitrix\Rest\Engine\ScopeManager',
			'onChangeRegisterModule'
		);
		$eventManager->unRegisterEventHandler(
			'main',
			'OnAfterUnRegisterModule',
			'rest',
			'\Bitrix\Rest\Engine\ScopeManager',
			'onChangeRegisterModule'
		);
		$eventManager->unRegisterEventHandler(
			'main',
			'OnAfterRegisterModule',
			'rest',
			'\Bitrix\Rest\Marketplace\TagTable',
			'onAfterRegisterModule'
		);
		$eventManager->unRegisterEventHandler(
			'main',
			'OnAfterUnRegisterModule',
			'rest',
			'\Bitrix\Rest\Marketplace\TagTable',
			'onAfterUnRegisterModule'
		);

		CAgent::RemoveModuleAgents("rest");

		UnRegisterModule("rest");

		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles($arParams = array())
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rest/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rest/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rest/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rest/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rest/install/services", $_SERVER["DOCUMENT_ROOT"]."/bitrix/services", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rest/install/images",  $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/rest", true, true);

		// delete old urlrewrite rule
		CUrlRewriter::Delete(array(
			'CONDITION' => '#^/rest/#',
			'PATH' => '/rest/index.php'
		));

		$siteId = \CSite::GetDefSite();

		\Bitrix\Main\UrlRewriter::add($siteId, array(
			"CONDITION" => "#^/rest/#",
			"RULE" => "",
			"PATH" => "/bitrix/services/rest/index.php",
		));

		if(\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rest/install/public", $_SERVER["DOCUMENT_ROOT"]."/", true, true);

			\Bitrix\Main\UrlRewriter::add($siteId, array(
				"CONDITION" => "#^/marketplace/#",
				"RULE" => "",
				"ID" => "bitrix:rest.marketplace",
				"PATH" => "/marketplace/index.php",
			));

			\Bitrix\Main\UrlRewriter::add($siteId, array(
				"CONDITION" => "#^/marketplace/app/#",
				"RULE" => "",
				"ID" => "bitrix:app.layout",
				"PATH" => "/marketplace/app/index.php",
			));

			\Bitrix\Main\UrlRewriter::add($siteId, array(
				"CONDITION" => "#^/marketplace/hook/#",
				"RULE" => "",
				"ID" => "bitrix:rest.hook",
				"PATH" => "/marketplace/hook/index.php",
			));

			\Bitrix\Main\UrlRewriter::add($siteId, array(
				"CONDITION" => "#^/marketplace/configuration/#",
				"RULE" => "",
				"ID" => "bitrix:rest.configuration",
				"PATH" => "/marketplace/configuration/index.php",
			));
		}

		return true;
	}

	function UnInstallFiles()
	{
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $USER, $step, $DB;
		$step = intval($step);

		if(!$USER->IsAdmin())
			return;

		if(!check_bitrix_sessid())
		{
			$step = 1;
		}

		if($step < 2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("REST_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rest/install/step1.php");
		}
		elseif($step == 2)
		{
			$this->InstallDB(array());
			$this->InstallFiles(array());

			$GLOBALS["errors"] = $this->errors;

			$APPLICATION->IncludeAdminFile(GetMessage("REST_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rest/install/step2.php");
		}
	}

	function DoUninstall()
	{
		global $APPLICATION, $USER, $step;
		if($USER->IsAdmin())
		{
			$step = intval($step);
			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("REST_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rest/install/unstep1.php");
			}
			elseif($step == 2)
			{
				$this->UnInstallDB(array(
					"savedata" => $_REQUEST["savedata"],
				));
				$this->UnInstallFiles();

				$GLOBALS["errors"] = $this->errors;

				$APPLICATION->IncludeAdminFile(GetMessage("REST_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rest/install/unstep2.php");
			}
		}
	}

	public static function OnGetTableSchema()
	{
		return array(
			"rest" => array(
				"b_rest_app" => array(
					"ID" => array(
						"b_rest_event" => "APP_ID",
						"b_rest_app_lang" => "APP_ID",
						"b_rest_placement" => "APP_ID",
						"b_rest_event_offline" => "APP_ID",
						"b_rest_stat" => "APP_ID",
						"b_rest_stat_app" => "APP_ID",
						"b_rest_app_log" => "APP_ID",
					),
					"CODE" => array(
						"b_rest_stat_app" => "APP_CODE",
					)
				),
				"b_rest_event" => array(
					"ID" => array(
						"b_rest_log" => "EVENT_ID",
					),
				),
				"b_rest_ap" => array(
					"ID" => array(
						"b_rest_ap_permission" => "PASSWORD_ID",
						"b_rest_log" => "PASSWORD_ID",
					)
				),
				"b_rest_stat_method" => array(
					"ID" => array(
						"b_rest_stat" => "METHOD_ID",
					)
				),
			),
			"main" => array(
				"b_user" => array(
					"ID" => array(
						"b_rest_event" => "USER_ID",
						"b_rest_ap" => "USER_ID",
						"b_rest_app_log" => "USER_ID",
					)
				),
			),
		);
	}
}
?>
