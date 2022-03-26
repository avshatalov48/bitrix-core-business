<?php
use \Bitrix\Landing\Template;
use \Bitrix\Landing\Landing as LandingCore;
use \Bitrix\Landing\Internals\BlockTable;
use \Bitrix\Landing\Internals\LandingTable;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Domain;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

if (class_exists('Landing'))
{
	return;
}

class Landing extends \CModule
{
	public $MODULE_ID = 'landing';
	public $MODULE_GROUP_RIGHTS = 'Y';
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;

	public $docRoot = '';
	public $eventsData = [
		'crm' => [
			'onAfterCrmCompanyAdd' => ['\Bitrix\Landing\Connector\Crm', 'onAfterCompanyChange'],
			'onAfterCrmCompanyUpdate' => ['\Bitrix\Landing\Connector\Crm', 'onAfterCompanyChange']
		],
		'iblock' => [
			'onAfterIBlockSectionDelete' => ['\Bitrix\Landing\Connector\Iblock', 'onAfterIBlockSectionDelete']
		],
		'intranet' => [
			'onBuildBindingMenu' => ['\Bitrix\Landing\Connector\Intranet', 'onBuildBindingMenu']
		],
		'landing' => [
			'onBuildSourceList' => ['\Bitrix\Landing\Connector\Landing', 'onSourceBuildHandler']
		],
		'main' => [
			'onBeforeSiteDelete' => ['\Bitrix\Landing\Site', 'onBeforeMainSiteDelete'],
			'onSiteDelete' => ['\Bitrix\Landing\Site', 'onMainSiteDelete'],
			'onUserConsentProviderList' => ['\Bitrix\Landing\Site\Cookies', 'onUserConsentProviderList']
		],
		'mobile' => [
			'onMobileMenuStructureBuilt' => ['\Bitrix\Landing\Connector\Mobile', 'onMobileMenuStructureBuilt']
		],
		'rest' => [
			'onRestServiceBuildDescription' => ['\Bitrix\Landing\Publicaction', 'restBase'],
			'onBeforeApplicationUninstall' => ['\Bitrix\Landing\Publicaction', 'beforeRestApplicationDelete'],
			'onRestAppDelete' => ['\Bitrix\Landing\Publicaction', 'restApplicationDelete'],
			// sites transfer
			'onRestApplicationConfigurationGetManifest' => ['\Bitrix\Landing\Transfer\AppConfiguration', 'getManifestList'],
			'onRestApplicationConfigurationExport' => ['\Bitrix\Landing\Transfer\AppConfiguration', 'onEventExportController'],
			'onRestApplicationConfigurationGetManifestSetting' => ['\Bitrix\Landing\Transfer\AppConfiguration', 'onInitManifest'],
			'onRestApplicationConfigurationEntity' => ['\Bitrix\Landing\Transfer\AppConfiguration', 'getEntityList'],
			'onRestApplicationConfigurationImport' => ['\Bitrix\Landing\Transfer\AppConfiguration', 'onEventImportController'],
			'onRestApplicationConfigurationFinish' => ['\Bitrix\Landing\Transfer\AppConfiguration', 'onFinish']
		],
		'seo' => [
			'onExtensionInstall' => ['\Bitrix\Landing\Hook\Page\PixelFb', 'changeBusinessPixel'],
		],
		'socialnetwork' => [
			'onFillSocNetFeaturesList' => ['\Bitrix\Landing\Connector\SocialNetwork', 'onFillSocNetFeaturesList'],
			'onFillSocNetMenu' => ['\Bitrix\Landing\Connector\SocialNetwork', 'onFillSocNetMenu'],
			'onSocNetGroupDelete' => ['\Bitrix\Landing\Connector\SocialNetwork', 'onSocNetGroupDelete']
		],
	];
	public $installDirs = array(
		'admin' => 'admin',
		'js' => 'landing',
		'images' => 'landing',
		'tools' => 'landing',
		'blocks' => 'bitrix',
		'components' => 'bitrix',
		'templates' => 'landing24'
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$arModuleVersion = array();

		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$server = $context->getServer();
		$this->docRoot = $server->getDocumentRoot();

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->MODULE_NAME = Loc::getMessage('LANDING_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('LANDING_MODULE_DESCRIPTION');
	}

	/**
	 * Call all install methods.
	 * @returm void
	 */
	public function doInstall()
	{
		global $DB, $APPLICATION;

		$this->installFiles();
		$this->installDB();

		$GLOBALS['APPLICATION']->includeAdminFile(
			Loc::getMessage('LANDING_INSTALL_TITLE'),
			$this->docRoot . '/bitrix/modules/landing/install/step1.php'
		);
	}

	/**
	 * Call all uninstall methods, include several steps.
	 * @returm void
	 */
	public function doUninstall()
	{
		global $APPLICATION;

		$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
		if ($step < 2)
		{
			$APPLICATION->includeAdminFile(
				Loc::getMessage('LANDING_UNINSTALL_TITLE'),
				$this->docRoot . '/bitrix/modules/landing/install/unstep1.php'
			);
		}
		elseif ($step == 2)
		{
			$params = [];
			if (isset($_GET['savedata']))
			{
				$params['savedata'] = $_GET['savedata'] == 'Y';
			}
			$this->uninstallDB($params);
			$this->uninstallFiles();
			$APPLICATION->includeAdminFile(
				Loc::getMessage('LANDING_UNINSTALL_TITLE'),
				$this->docRoot . '/bitrix/modules/landing/install/unstep2.php'
			);
		}
	}

	/**
	 * Install DB, events, etc.
	 * @return boolean
	 */
	public function installDB()
	{
		global $DB, $APPLICATION;

		// db
		$errors = $DB->runSQLBatch(
			$this->docRoot.'/bitrix/modules/landing/install/db/mysql/install.sql'
		);
		if ($errors !== false)
		{
			$APPLICATION->throwException(implode('', $errors));
			return false;
		}

		// module
		registerModule($this->MODULE_ID);

		// full text
		$errors = $DB->runSQLBatch(
			$this->docRoot.'/bitrix/modules/landing/install/db/mysql/install_ft.sql'
		);
		if ($errors === false)
		{
			if (\Bitrix\Main\Loader::includeModule('landing'))
			{
				BlockTable::getEntity()->enableFullTextIndex('SEARCH_CONTENT');
				LandingTable::getEntity()->enableFullTextIndex('SEARCH_CONTENT');
			}
		}

		// install event handlers
		$eventManager = Bitrix\Main\EventManager::getInstance();
		foreach ($this->eventsData as $module => $events)
		{
			foreach ($events as $eventCode => $callback)
			{
				$eventManager->registerEventHandler(
					$module,
					$eventCode,
					$this->MODULE_ID,
					$callback[0],
					$callback[1]
				);
			}
		}

		// agents
		\CAgent::addAgent(
			'Bitrix\Landing\Agent::clearRecycle();',
			$this->MODULE_ID,
			'N',
			7200
		);
		\CAgent::addAgent(
			'Bitrix\Landing\Agent::clearFiles();',
			$this->MODULE_ID,
			'N',
			3600
		);
		\CAgent::addAgent(
			'Bitrix\Landing\Agent::sendRestStatistic();',
			$this->MODULE_ID
		);
		\CAgent::addAgent(
			'Bitrix\Landing\Agent::clearTempFiles();',
			$this->MODULE_ID
		);

		// rights
		$this->InstallTasks();

		// templates
		if (\Bitrix\Main\Loader::includeModule($this->MODULE_ID))
		{
			$this->installTemplates();
			$this->setOptions();
		}
		$this->setSiteTemplates();

		// route handlers
		$this->setRouteHandlers();

		return true;
	}

	/**
	 * Is B24 portal?
	 * @return bool
	 */
	private function isB24()
	{
		if (
			defined('LANDING_DISABLE_B24_MODE') &&
			LANDING_DISABLE_B24_MODE === true
		)
		{
			return false;
		}
		else
		{
			return ModuleManager::isModuleInstalled('bitrix24') ||
				   ModuleManager::isModuleInstalled('intranet');
		}
	}


	/**
	 * Install or uninstall main site templates.
	 * @param boolean $install Call type.
	 * @return void
	 */
	public function setSiteTemplates($install = true)
	{
		$clearCache = false;
		$tplFields = array(
			'TEMPLATE' => Option::get('landing', 'site_template_id')
		);

		// uninstall tpl in site (always, and before the install)
		$resCheck = \Bitrix\Main\SiteTemplateTable::getList(array(
			'filter' => $tplFields
		));
		while ($rowCheck = $resCheck->fetch())
		{
			$clearCache = true;
			\Bitrix\Main\SiteTemplateTable::delete(
				$rowCheck['ID']
			);
		}

		// set template for every site
		if ($install)
		{
			$res = \Bitrix\Main\SiteTable::getList(array(
				'select' => array(
					'LID', 'DIR'
				),
				'order' => array(
					'SORT' => 'ASC'
				)
			));
			while ($row = $res->fetch())
			{
				// only for b24
				if (ModuleManager::isModuleInstalled('bitrix24'))
				{
					$clearCache = true;
					\Bitrix\Main\SiteTemplateTable::add(
						$tplFields + array(
							'SITE_ID' => $row['LID'],
							'SORT' => 0,
							'CONDITION' => 'CSite::InDir(\'' . rtrim($row['DIR'], '/') . '/pub/site/\')'
						)
					);
				}
				// more short address for smn
				if (!$this->isB24())
				{
					if (!Option::get('landing', 'pub_path_' . $row['LID']))
					{
						Option::set('landing', 'pub_path_' . $row['LID'], '/lp/');
					}
				}
			}
		}

		// only for B24 smn
		if (
			!ModuleManager::isModuleInstalled('bitrix24') &&
			ModuleManager::isModuleInstalled('intranet')
		)
		{
			$clearCache = true;
			\Bitrix\Main\SiteTemplateTable::add(
				$tplFields + array(
					'SITE_ID' => 's1',//@todo
					'SORT' => 500,
					'CONDITION' => 'preg_match(\'#/sites/site/[\d]+/view/[\d]+/#\', ' .
								   '$GLOBALS[\'APPLICATION\']->GetCurPage(0))'
				)
			);
		}

		if ($clearCache)
		{
			$GLOBALS['CACHE_MANAGER']->clean('b_site_template');
		}
	}

	/**
	 * Set router handlers for post preview.
	 * @return void
	 */
	private function setRouteHandlers()
	{
		\Bitrix\Main\UrlPreview\Router::setRouteHandler(
			'/knowledge/#knowledgeCode#/',
			'landing',
			'\Bitrix\Landing\Landing\UrlPreview',
			[
				'knowledgeCode' => '$knowledgeCode',
				'scope' => 'knowledge',
				'allowSlashes' => 'N'
			]
		);
		\Bitrix\Main\UrlPreview\Router::setRouteHandler(
			'/knowledge/group/#knowledgeCode#/',
			'landing',
			'\Bitrix\Landing\Landing\UrlPreview',
			[
				'knowledgeCode' => '$knowledgeCode',
				'scope' => 'group',
				'allowSlashes' => 'N'
			]
		);
	}

	/**
	 * Settings required options.
	 * @return void
	 */
	public function setOptions()
	{
	}

	/**
	 * Install templates of landing.
	 * @return boolean
	 */
	public function installTemplates()
	{
		$installTtpl = array(
			'empty' => array(
				'content' => '#CONTENT#',
				'area_count' => 0
			),
			'sidebar_left' => array(
				'content' => '<div class="landing-layout-flex">
								<div class="landing-sidebar g-max-width-100x g-overflow-hidden">#AREA_1#</div>
								<div class="landing-main g-max-width-100x">#CONTENT#</div>
							</div>',
				'area_count' => 1
			),
			'sidebar_right' => array(
				'content' => '<div class="landing-layout-flex sidebar-right">
								<div class="landing-sidebar g-max-width-100x">#AREA_1#</div>
								<div class="landing-main g-max-width-100x">#CONTENT#</div>
							</div>',
				'area_count' => 1
			),
			'header_only' => array(
				'content' => '<div class="landing-header">#AREA_1#</div> 
								<div class="landing-main">#CONTENT#</div>',
				'area_count' => 1
			),
			'header_footer' => array(
				'content' => '<div class="landing-header">#AREA_1#</div> 
								<div class="landing-main">#CONTENT#</div> 
							<div class="landing-footer">#AREA_2#</div>',
				'area_count' => 2
			),
			'without_left' => array(
				'content' => '<div class="landing-header">#AREA_1#</div>
								<div class="landing-layout-flex without-left">
									<div class="landing-sidebar g-max-width-100x">#AREA_2#</div>
									<div class="landing-main g-max-width-100x">#CONTENT#</div>
								</div>
							<div class="landing-footer">#AREA_3#</div>',
				'area_count' => 3
			),
			'without_right' => array(
				'content' => '<div class="landing-header">#AREA_1#</div>
								<div class="landing-layout-flex">
									<div class="landing-sidebar g-max-width-100x">#AREA_2#</div>
									<div class="landing-main g-max-width-100x">#CONTENT#</div>
								</div>
							<div class="landing-footer">#AREA_3#</div>',
				'area_count' => 3
			)
		);
		// first check exist
		$res = Template::getList(array(
			'filter' => array(
				'XML_ID' => array_keys($installTtpl)
			)
		));
		while ($row = $res->fetch())
		{
			$installTtpl[$row['XML_ID']]['id'] = $row['ID'];
		}
		// then add / update
		$i = 0;
		foreach ($installTtpl as $code => $tpl)
		{
			$i += 100;
			$fields = array(
				'XML_ID' => $code,
				'ACTIVE' => 'Y',
				'SORT' => $i,
				'TITLE' => '#'.mb_strtoupper($code) . '#',
				'CONTENT' => $tpl['content'],
				'AREA_COUNT' => $tpl['area_count']
			);
			if (isset($tpl['id']))
			{
				Template::update($tpl['id'], $fields);
			}
			else
			{
				Template::add($fields);
			}
		}

		return true;
	}

	/**
	 * Install files.
	 * @return boolean
	 */
	public function installFiles()
	{
		foreach ($this->installDirs as $dir => $subdir)
		{
			copyDirFiles(
				$this->docRoot . '/bitrix/modules/landing/install/' . $dir,
				$this->docRoot . '/bitrix/' . $dir,
				true, true
			);
		}

		$GLOBALS['CACHE_MANAGER']->clearByTag('landing_blocks');
		$GLOBALS['CACHE_MANAGER']->clearByTag('landing_demo');

		return true;
	}

	/**
	 * Remove all pages and sites first.
	 * @return void
	 */
	public function removeData()
	{
		if (\Bitrix\Main\Loader::includeModule($this->MODULE_ID))
		{
			// first delete landings
			$res = LandingCore::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'=DELETED' => ['Y', 'N'],
					'=SITE.DELETED' => ['Y', 'N'],
					'CHECK_PERMISSIONS' => 'N'
				]
			]);
			while ($row = $res->fetch())
			{
				$resDel = LandingCore::delete($row['ID'], true);
				$resDel->isSuccess();// for trigger
			}

			// then delete sites
			$res = Site::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'=DELETED' => ['Y', 'N'],
					'CHECK_PERMISSIONS' => 'N'
				],
			]);
			while ($row = $res->fetch())
			{
				$resDel = Site::delete($row['ID']);
				$resDel->isSuccess();// for trigger
			}
		}
	}

	/**
	 * Uninstall DB, events, etc.
	 * @param array $arParams Some params.
	 * @return boolean
	 */
	public function uninstallDB($arParams = array())
	{
		global $APPLICATION, $DB;

		$errors = false;

		// delete DB
		if (isset($arParams['savedata']) && !$arParams['savedata'])
		{
			$errors = $DB->runSQLBatch(
				$this->docRoot.'/bitrix/modules/landing/install/db/mysql/uninstall.sql'
			);
		}
		if ($errors !== false)
		{
			$APPLICATION->throwException(implode('', $errors));
			return false;
		}

		// agents and rights
		\CAgent::removeModuleAgents($this->MODULE_ID);
		$this->UnInstallTasks();

		// uninstall event handlers
		$eventManager = Bitrix\Main\EventManager::getInstance();
		foreach ($this->eventsData as $module => $events)
		{
			foreach ($events as $eventCode => $callback)
			{
				$eventManager->unregisterEventHandler(
					$module,
					$eventCode,
					$this->MODULE_ID,
					$callback[0],
					$callback[1]
				);
			}
		}

		// module
		unregisterModule($this->MODULE_ID);

		// templates
		$this->setSiteTemplates(false);

		// delete files finaly
		if (isset($arParams['savedata']) && !$arParams['savedata'])
		{
			$res = \Bitrix\Main\FileTable::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'=MODULE_ID' => $this->MODULE_ID
				),
				'order' => array(
					'ID' => 'DESC'
				)
			));
			while ($row = $res->fetch())
			{
				\CFile::Delete($row['ID']);
			}
		}

		return true;
	}

	/**
	 * Uninstall files.
	 * @return boolean
	 */
	public function uninstallFiles()
	{
		foreach ($this->installDirs as $dir => $subdir)
		{
			if ($dir != 'components')
			{
				deleteDirFilesEx('/bitrix/' . $dir . '/' . $subdir);
			}
		}

		$GLOBALS['CACHE_MANAGER']->clearByTag('landing_blocks');
		$GLOBALS['CACHE_MANAGER']->clearByTag('landing_demo');

		return true;
	}

	/**
	 * Get module rights.
	 * @return array
	 */
	public function getModuleRightList()
	{
		return array(
			'reference_id' => array('D', 'W'),
			'reference' => array(
				'[D] ' . Loc::getMessage('LANDING_RIGHT_D'),
				'[W] ' . Loc::getMessage('LANDING_RIGHT_W')
			)
		);
	}

	/**
	 * Get access tasks for module.
	 * @return array
	 */
	public function getModuleTasks()
	{
		return array(
			'landing_right_denied' => array(
				'LETTER' => 'D',
				'BINDING' => 'module',
				'OPERATIONS' => array()
			),
			'landing_right_read' => array(
				'LETTER' => 'R',
				'BINDING' => 'module',
				'OPERATIONS' => array(
					'landing_read'
				)
			),
			'landing_right_edit' => array(
				'LETTER' => 'U',
				'BINDING' => 'module',
				'OPERATIONS' => array(
					'landing_edit'
				)
			),
			'landing_right_sett' => array(
				'LETTER' => 'V',
				'BINDING' => 'module',
				'OPERATIONS' => array(
					'landing_sett'
				)
			),
			'landing_right_public' => array(
				'LETTER' => 'W',
				'BINDING' => 'module',
				'OPERATIONS' => array(
					'landing_public'
				)
			),
			'landing_right_delete' => array(
				'LETTER' => 'X',
				'BINDING' => 'module',
				'OPERATIONS' => array(
					'landing_delete'
				)
			)
		);
	}

	/**
	 * Method for migrate from cloud version.
	 * @return void
	 */
	public function migrateToBox()
	{
		// delete some cloud options
		$keyForDelete = [
			'shops_limit_count',
			'site_limit_count',
			'shops_limit_count_publication',
			'site_limit_count_publication',
			'pages_limit_count_publication',
			'permissions_available',
			'google_images_key'
		];
		foreach ($keyForDelete as $key)
		{
			Option::delete(
				'landing',
				['name' => $key]
			);
		}

		if (\Bitrix\Main\Loader::includeModule('landing'))
		{
			// clear all providers in domains
			$res = Domain::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'!=PROVIDER' => null
				]
			]);
			while ($row = $res->fetch())
			{
				Domain::update($row['ID'], [
					'PROVIDER' => null
				]);
			}
		}

		unset($keyForDelete, $key);
	}
}
