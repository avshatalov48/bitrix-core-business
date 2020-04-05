<?php
use \Bitrix\Landing\Template;
use \Bitrix\Landing\Landing as LandingCore;
use \Bitrix\Landing\Internals\BlockTable;
use \Bitrix\Landing\Internals\LandingTable;
use \Bitrix\Landing\Site;
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

		$path = str_replace('\\', '/', __FILE__);
		$path = substr($path, 0, strlen($path) - strlen('/index.php'));
		include($path . '/version.php');

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
			$this->docRoot . '/bitrix/modules/landing/install/db/' .
			strtolower($DB->type) . '/install.sql'
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
			$this->docRoot . '/bitrix/modules/landing/install/db/' .
			strtolower($DB->type) . '/install_ft.sql'
		);
		if ($errors === false)
		{
			if (\Bitrix\Main\Loader::includeModule('landing'))
			{
				BlockTable::getEntity()->enableFullTextIndex('SEARCH_CONTENT');
				LandingTable::getEntity()->enableFullTextIndex('SEARCH_CONTENT');
			}
		}

		// events
		$eventManager = Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler(
			'rest',
			'OnRestServiceBuildDescription',
			$this->MODULE_ID,
			'\Bitrix\Landing\Publicaction',
			'restBase'
		);
		$eventManager->registerEventHandler(
			'rest',
			'onBeforeApplicationUninstall',
			$this->MODULE_ID,
			'\Bitrix\Landing\Publicaction',
			'beforeRestApplicationDelete'
		);
		$eventManager->registerEventHandler(
			'rest',
			'OnRestAppDelete',
			$this->MODULE_ID,
			'\Bitrix\Landing\Publicaction',
			'restApplicationDelete'
		);
		$eventManager->registerEventHandler(
			'main',
			'OnBeforeSiteDelete',
			$this->MODULE_ID,
			'\Bitrix\Landing\Site',
			'onBeforeMainSiteDelete'
		);
		$eventManager->registerEventHandler(
			'main',
			'OnSiteDelete',
			$this->MODULE_ID,
			'\Bitrix\Landing\Site',
			'onMainSiteDelete'
		);
		$eventManager->registerEventHandler(
			'bitrix24',
			'OnDomainChange',
			$this->MODULE_ID,
			'\Bitrix\Landing\Update\Block\NodeAttributes',
			'updateFormDomain'
		);
		$eventManager->registerEventHandler(
			'socialservices',
			'\Bitrix\Socialservices\ApTable::OnAfterAdd',
			$this->MODULE_ID,
			'\Bitrix\Landing\Update\Block\NodeAttributes',
			'updateFormDomainByConnector'
		);
		$eventManager->registerEventHandler(
			$this->MODULE_ID,
			'OnBuildSourceList',
			$this->MODULE_ID,
			'\Bitrix\Landing\Connector\Landing',
			'onSourceBuildHandler'
		);
		$eventManager->registerEventHandler(
			'mobile',
			'onMobileMenuStructureBuilt',
			$this->MODULE_ID,
			'\Bitrix\Landing\Connector\Mobile',
			'onMobileMenuStructureBuilt'
		);
		$eventManager->registerEventHandler(
			'intranet',
			'onBuildBindingMenu',
			$this->MODULE_ID,
			'\Bitrix\Landing\Connector\Intranet',
			'onBuildBindingMenu'
		);
		$eventManager->registerEventHandler(
			'socialnetwork',
			'onFillSocNetFeaturesList',
			$this->MODULE_ID,
			'\Bitrix\Landing\Connector\SocialNetwork',
			'onFillSocNetFeaturesList'
		);
		$eventManager->registerEventHandler(
			'socialnetwork',
			'onFillSocNetMenu',
			$this->MODULE_ID,
			'\Bitrix\Landing\Connector\SocialNetwork',
			'onFillSocNetMenu'
		);
		$eventManager->registerEventHandler(
			'socialnetwork',
			'onSocNetGroupDelete',
			$this->MODULE_ID,
			'\Bitrix\Landing\Connector\SocialNetwork',
			'onSocNetGroupDelete'
		);

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

		// rights
		$this->InstallTasks();

		// templates
		if (\Bitrix\Main\Loader::includeModule($this->MODULE_ID))
		{
			$this->installTemplates();
			$this->setOptions();
		}
		$this->setSiteTemplates();

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
			'/knowledge/#siteCode#/',
			'landing',
			'\Bitrix\Landing\Landing\UrlPreview',
			[
				'siteCode' => '$siteCode',
				'scope' => 'knowledge'
			]
		);
		\Bitrix\Main\UrlPreview\Router::setRouteHandler(
			'/knowledge/#siteCode#/#pageCode#/',
			'landing',
			'\Bitrix\Landing\Landing\UrlPreview',
			[
				'siteCode' => '$siteCode',
				'pageCode' => '$pageCode',
				'scope' => 'knowledge'
			]
		);
		\Bitrix\Main\UrlPreview\Router::setRouteHandler(
			'/knowledge/#siteCode#/#folderCode#/#pageCode#/',
			'landing',
			'\Bitrix\Landing\Landing\UrlPreview',
			[
				'siteCode' => '$siteCode',
				'folderCode' => '$folderCode',
				'pageCode' => '$pageCode',
				'scope' => 'knowledge'
			]
		);
		\Bitrix\Main\UrlPreview\Router::setRouteHandler(
			'/knowledge/#siteCode#/#folderCode#/#pageCode#/#additionalCode#/',
			'landing',
			'\Bitrix\Landing\Landing\UrlPreview',
			[
				'siteCode' => '$siteCode',
				'folderCode' => '$folderCode',
				'pageCode' => '$pageCode',
				'additionalCode' => '$additionalCode',
				'scope' => 'group'
			]
		);
	}

	/**
	 * Settings required options.
	 * @return void
	 */
	public function setOptions()
	{
		Option::set('landing', 'disabled_namespaces', 'bitrix');
		\Bitrix\Landing\Manager::getRestPath();
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
				'TITLE' => '#' . strtoupper($code) . '#',
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
				$this->docRoot . '/bitrix/modules/landing/install/db/' .
				strtolower($DB->type) . '/uninstall.sql'
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

		// unregister events
		$eventManager = Bitrix\Main\EventManager::getInstance();
		$eventManager->unregisterEventHandler(
			'rest',
			'OnRestServiceBuildDescription',
			$this->MODULE_ID,
			'\Bitrix\Landing\Publicaction',
			'restBase'
		);
		$eventManager->unregisterEventHandler(
			'rest',
			'onBeforeApplicationUninstall',
			$this->MODULE_ID,
			'\Bitrix\Landing\Publicaction',
			'beforeRestApplicationDelete'
		);
		$eventManager->unregisterEventHandler(
			'rest',
			'OnRestAppDelete',
			$this->MODULE_ID,
			'\Bitrix\Landing\Publicaction',
			'restApplicationDelete'
		);
		$eventManager->unregisterEventHandler(
			'main',
			'OnBeforeSiteDelete',
			$this->MODULE_ID,
			'\Bitrix\Landing\Site',
			'onBeforeMainSiteDelete'
		);
		$eventManager->unregisterEventHandler(
			'main',
			'OnSiteDelete',
			$this->MODULE_ID,
			'\Bitrix\Landing\Site',
			'onMainSiteDelete'
		);
		$eventManager->unregisterEventHandler(
			'bitrix24',
			'OnDomainChange',
			$this->MODULE_ID,
			'\Bitrix\Landing\Update\Block\NodeAttributes',
			'updateFormDomain'
		);
		$eventManager->unregisterEventHandler(
			'socialservices',
			'\Bitrix\Socialservices\ApTable::OnAfterAdd',
			$this->MODULE_ID,
			'\Bitrix\Landing\Update\Block\NodeAttributes',
			'updateFormDomainByConnector'
		);
		$eventManager->unregisterEventHandler(
			$this->MODULE_ID,
			'OnBuildSourceList',
			$this->MODULE_ID,
			'\Bitrix\Landing\Connector\Landing',
			'onSourceBuildHandler'
		);
		$eventManager->unregisterEventHandler(
			'mobile',
			'onMobileMenuStructureBuilt',
			$this->MODULE_ID,
			'\Bitrix\Landing\Connector\Mobile',
			'onMobileMenuStructureBuilt'
		);
		$eventManager->unregisterEventHandler(
			'intranet',
			'onBuildBindingMenu',
			$this->MODULE_ID,
			'\Bitrix\Landing\Connector\Intranet',
			'onBuildBindingMenu'
		);
		$eventManager->unregisterEventHandler(
			'socialnetwork',
			'onFillSocNetFeaturesList',
			$this->MODULE_ID,
			'\Bitrix\Landing\Connector\SocialNetwork',
			'onFillSocNetFeaturesList'
		);
		$eventManager->unregisterEventHandler(
			'socialnetwork',
			'onFillSocNetMenu',
			$this->MODULE_ID,
			'\Bitrix\Landing\Connector\SocialNetwork',
			'onFillSocNetMenu'
		);
		$eventManager->unregisterEventHandler(
			'socialnetwork',
			'onSocNetGroupDelete',
			$this->MODULE_ID,
			'\Bitrix\Landing\Connector\SocialNetwork',
			'onSocNetGroupDelete'
		);

		// module
		unregisterModule($this->MODULE_ID);

		// templates
		$this->setSiteTemplates(false);

		// route handlers
		$this->setRouteHandlers();

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

		unset($keyForDelete, $key);
	}
}
