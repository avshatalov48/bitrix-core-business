<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ListsSelectElementComponent extends CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		$arParams['ERROR'] = array();
		if (!Loader::includeModule('lists'))
		{
			$arParams['ERROR'][] = Loc::getMessage('CC_BLL_MODULE_NOT_INSTALLED');
		}
		return $arParams;
	}

	public function executeComponent()
	{
		if(!empty($this->arParams['ERROR']))
		{
			ShowError(array_shift($this->arParams['ERROR']));
			return;
		}

		$this->arResult['SOCNET_GROUP_ID'] = $this->arParams['SOCNET_GROUP_ID'];
		$this->arResult['RAND_STRING'] = $this->randString();
		$this->arResult['DESTINATION'] = $this->arParams['DESTINATION'];
		$path = rtrim(SITE_DIR, '/');
		$this->arResult['LISTS_URL'] = $path.COption::GetOptionString('lists', 'livefeed_url');
		$this->arResult['IBLOCK_ID'] = intval($this->arParams['IBLOCK_ID']);

		if($this->arResult['IBLOCK_ID'])
		{
			$this->getIblockData();
		}

		$this->includeComponentTemplate();
	}

	protected function getIblockData()
	{
		$this->arResult['LIST_DATA'] = array();
		$lists = CIBlock::getList(
			array("SORT" => "ASC","NAME" => "ASC"),
			array('ACTIVE' => 'Y','ID' => $this->arResult['IBLOCK_ID'])
		);
		while($list = $lists->fetch())
		{
			if(CLists::getLiveFeed($list['ID']))
			{
				$this->arResult['LIST_DATA']['ID'] = $list['ID'];
				$this->arResult['LIST_DATA']['NAME'] = $list['NAME'];
				$this->arResult['LIST_DATA']['DESCRIPTION'] = $list['DESCRIPTION'];
				$this->arResult['LIST_DATA']['CODE'] = $list['CODE'];
				if($list['PICTURE'] > 0)
				{
					$imageFile = CFile::GetFileArray($list['PICTURE']);
					if($imageFile !== false)
					{
						$this->arResult['LIST_DATA']['PICTURE'] = '<img src="'.$imageFile["SRC"].'" width="36" height="30" border="0" />';
						$this->arResult['LIST_DATA']['PICTURE_SMALL'] = '<img src="'.$imageFile["SRC"].'" width="19" height="16" border="0" />';
					}
				}
				else
				{
					$this->arResult['LIST_DATA']['PICTURE'] = "<img src=\"/bitrix/images/lists/default.png\" width=\"36\" height=\"30\" border=\"0\" />";
					$this->arResult['LIST_DATA']['PICTURE_SMALL'] = "<img src=\"/bitrix/images/lists/default.png\" width=\"19\" height=\"16\" border=\"0\" />";
				}
			}
		}
	}

	protected function getApplication()
	{
		global $APPLICATION;
		return $APPLICATION;
	}

	protected function getUser()
	{
		global $USER;
		return $USER;
	}
}