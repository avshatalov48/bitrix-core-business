<?php
use \Bitrix\Landing\Template;
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
	public $installPubDirs = array(
		'sites' => 'sites'
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
			$this->uninstallDB(array('savedata' => isset($_GET['savedata']) && $_GET['savedata'] == 'Y'));
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
		$errors = $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/landing/install/db/' . strtolower($DB->type) . '/install.sql');
		if ($errors !== false)
		{
			$APPLICATION->throwException(implode('', $errors));
			return false;
		}

		// module
		registerModule($this->MODULE_ID);

		// events
		$eventManager = Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('rest', 'OnRestServiceBuildDescription', $this->MODULE_ID, '\Bitrix\Landing\Publicaction', 'restBase');
		$eventManager->registerEventHandler('rest', 'OnRestAppDelete', $this->MODULE_ID, '\Bitrix\Landing\Publicaction', 'restApplicationDelete');

		// templates
		if (\Bitrix\Main\Loader::includeModule($this->MODULE_ID))
		{
			$this->installTemplates();
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

		// uninstall tpl in site
		if (!$install)
		{
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
			\Bitrix\Main\SiteTemplateTable::add(
				$tplFields + array(
					'SITE_ID' => 's1',//@todo
					'SORT' => 0,
					'CONDITION' => 'preg_match(\'#/sites/site/[\d]+/view/[\d]+/#\', ' .
								   '$GLOBALS[\'APPLICATION\']->GetCurPage(0))'
				)
			);
		}

		// work with cloud repo always
		$repoAddr = 'https://repo.bitrix24.site/rest/1/w1uqy3swvyp50bso/';
		Option::set('landing', 'block_vendor_bitrix', $repoAddr);
		Option::set('landing', 'disabled_namespaces', 'bitrix');

		if ($clearCache)
		{
			$GLOBALS['CACHE_MANAGER']->cleanAll();
			$GLOBALS['CACHE_MANAGER']->cleanDir('b_site_template');
		}
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
								<div class="g-width-25x--lg g-max-width-100x g-overflow-hidden landing-sidebar">#AREA_1#</div> 
								<div class="g-width-75x--lg g-max-width-100x landing-main">#CONTENT#</div> 
							</div>',
				'area_count' => 1
			),
			'sidebar_right' => array(
				'content' => '<div class="landing-layout-flex">
								<div class="g-width-25x--lg landing-flex-order-1 g-max-width-100x landing-sidebar">#AREA_1#</div> 
								<div class="g-width-75x--lg landing-flex-order-0 g-max-width-100x landing-main">#CONTENT#</div> 
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
								<div class="landing-layout-flex">
									<div class="g-width-25x--lg g-max-width-100x landing-flex-order-1 landing-sidebar">#AREA_2#</div>
									<div class="g-width-75x--lg g-max-width-100x landing-flex-order-0 landing-main">#CONTENT#</div> 
								</div> 
							<div class="landing-footer">#AREA_3#</div>',
				'area_count' => 3
			),
			'without_right' => array(
				'content' => '<div class="landing-header">#AREA_1#</div>
								<div class="landing-layout-flex">
									<div class="g-width-25x--lg g-max-width-100x landing-sidebar">#AREA_2#</div>
									<div class="g-width-75x--lg g-max-width-100x landing-main">#CONTENT#</div>
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

		if ($this->isB24())
		{
			foreach ($this->installPubDirs as $dir => $subdir)
			{
				if (!file_exists($this->docRoot . '/' . $dir))
				{
					copyDirFiles(
						$this->docRoot . '/bitrix/modules/landing/install/' . $dir,
						$this->docRoot . '/' . $dir,
						true, true
					);
				}
			}
			\Bitrix\Main\UrlRewriter::add(
				's1',//@todo
				array(
					'ID' => 'bitrix:landing.start',
					'PATH' => '/sites/index.php',
					'CONDITION' => '#^/sites/#'
				)
			);
		}

		$GLOBALS['CACHE_MANAGER']->clearByTag('landing_blocks');
		$GLOBALS['CACHE_MANAGER']->clearByTag('landing_demo');

		return true;
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
				$this->docRoot . '/bitrix/modules/landing/install/db/' . strtolower($DB->type) . '/uninstall.sql'
			);
		}
		if ($errors !== false)
		{
			$APPLICATION->throwException(implode('', $errors));
			return false;
		}

		// unregister events
		$eventManager = Bitrix\Main\EventManager::getInstance();
		$eventManager->unregisterEventHandler('rest', 'OnRestServiceBuildDescription', $this->MODULE_ID, '\Bitrix\Landing\Publicaction', 'restBase');
		$eventManager->unregisterEventHandler('rest', 'OnRestAppDelete', $this->MODULE_ID, '\Bitrix\Landing\Publicaction', 'restApplicationDelete');

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
}