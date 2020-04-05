<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ListsCatalogProcessesComponent extends CBitrixComponent
{
	/** @var  array */
	protected $errors = array();
	/** @var  string or int */
	protected $listPerm;
	/** @var  string */
	protected $iblockTypeId;

	public function onPrepareComponentParams($arParams)
	{
		if (Loader::includeModule('lists'))
		{
			$this->iblockTypeId = $arParams['IBLOCK_TYPE_ID'];
			$this->checkPermission();
		}
		else
		{
			$this->errors[] = Loc::getMessage('CC_LCP_MODULE_NOT_INSTALLED');
		}
		return $arParams;
	}

	public function executeComponent()
	{
		global $APPLICATION;
		$APPLICATION->setTitle(Loc::getMessage('CC_LCP_TITLE'));

		if(!empty($this->errors))
		{
			ShowError(array_shift($this->errors));
			return;
		}

		$this->loadDataProcesses();
		if(!empty($this->errors))
		{
			ShowError(array_shift($this->errors));
			return;
		}

		$this->checkingInstallation();

		$this->arResult['RAND_STRING'] = $this->randString();
		$this->arResult['LISTS_URL'] = $this->arParams['LISTS_URL'];

		$this->includeComponentTemplate();
	}

	protected function loadDataProcesses()
	{
		$this->arResult['SYSTEM_PROCESSES'] = array();
		$this->arResult['USER_PROCESSES'] = array();
		try
		{
			$defaultLang = "en";
			if(IsModuleInstalled("bitrix24"))
			{
				$gr = COption::GetOptionString("main", "~controller_group_name", "");
				if($gr != "")
					$defaultLang = substr($gr, 0, 2);
				if($defaultLang == "ua")
					$defaultLang = "ru";
			}
			else
			{
				$defaultSiteId = CSite::GetDefSite();
				$siteObject = CSite::GetByID($defaultSiteId);
				$site = $siteObject->fetch();
				$defaultLang = $site ? $site['LANGUAGE_ID'] : "en";
				if($defaultLang == "ua")
					$defaultLang = "ru";
			}
			\Bitrix\Lists\Importer::loadDataProcesses($defaultLang, true, $this->arResult['SYSTEM_PROCESSES']);
			\Bitrix\Lists\Importer::loadDataProcesses($defaultLang, false, $this->arResult['USER_PROCESSES']);

			$this->checkForIblock($this->arResult['SYSTEM_PROCESSES']);
			$this->checkForIblock($this->arResult['USER_PROCESSES']);
		}
		catch (Exception $e)
		{
			$this->errors[] =  $e->getMessage();
		}

		if(empty($this->arResult['SYSTEM_PROCESSES']) && empty($this->arResult['USER_PROCESSES']))
		{
			$this->errors[] = Loc::getMessage('CC_LCP_NOT_PROCESSES');
		}
	}

	protected function checkForIblock(&$listIblock)
	{
		if(!empty($listIblock))
		{
			$filter = array();
			foreach($listIblock as $iblockData)
			{
				$filter['CODE'][] = $iblockData['CODE'];
				$filter['IBLOCK_TYPE_ID'][] = $iblockData['IBLOCK_TYPE_ID'];
				$filter['NAME'][] = $iblockData['NAME'];
			}

			$iblockObject = CIBlock::getList(
				array(),
				array(
					'IBLOCK_TYPE_ID' => $filter['IBLOCK_TYPE_ID'],
					'CODE' => $filter['CODE'],
					'CHECK_PERMISSIONS' => 'N',
					'SITE_ID' => SITE_ID
				)
			);

			while($iblock = $iblockObject->fetch())
			{
				if(array_key_exists($iblock['CODE'], $listIblock))
				{
					$listIblock[$iblock['CODE']]['NAME'] .= Loc::getMessage('CC_LCP_PROCESS_INSTALLED');
					$listIblock[$iblock['CODE']]['PICK_OUT'] = true;
				}
			}
		}
	}

	protected function checkingInstallation()
	{
		$this->arResult['SYSTEM_PROCESSES_INSTALL'] = true;
		$this->arResult['USER_PROCESSES_INSTALL'] = true;
		if(!empty($this->arResult['SYSTEM_PROCESSES']))
		{
			foreach($this->arResult['SYSTEM_PROCESSES'] as $systemProcesses)
			{
				if(!array_key_exists('PICK_OUT', $systemProcesses))
				{
					$this->arResult['SYSTEM_PROCESSES_INSTALL'] = false;
					break;
				}
			}
		}

		if(!empty($this->arResult['USER_PROCESSES']))
		{
			foreach($this->arResult['USER_PROCESSES'] as $systemProcesses)
			{
				if(!array_key_exists('PICK_OUT', $systemProcesses))
				{
					$this->arResult['USER_PROCESSES_INSTALL'] = false;
					break;
				}
			}
		}
		$this->arResult['ALL_PROCESSES_INSTALL'] = (
			$this->arResult['SYSTEM_PROCESSES_INSTALL'] && $this->arResult['USER_PROCESSES_INSTALL']
		) ? true : false;
	}

	protected function checkPermission()
	{
		global $USER;
		$this->listPerm = CListPermissions::checkAccess($USER, $this->iblockTypeId);
		if($this->listPerm < 0)
		{
			switch($this->listPerm)
			{
				case CListPermissions::WRONG_IBLOCK_TYPE:
					$this->errors[] = Loc::getMessage('CC_LCP_WRONG_IBLOCK_TYPE');
					break;
				case CListPermissions::WRONG_IBLOCK:
					$this->errors[] = Loc::getMessage('CC_LCP_WRONG_IBLOCK');
					break;
				case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
					$this->errors[] = Loc::getMessage('CC_LCP_SONET_GROUP_DISABLED');
					break;
				default:
					$this->errors[] = Loc::getMessage('CC_LCP_UNKNOWN_ERROR');
					break;
			}
		}
		elseif($this->listPerm < CListPermissions::IS_ADMIN)
		{
			$this->errors[] = Loc::getMessage('CC_LCP_ACCESS_DENIED');
		}
	}
}