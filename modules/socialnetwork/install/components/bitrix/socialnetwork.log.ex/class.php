<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

if (!Loader::includeModule('socialnetwork'))
{
	ShowError(Loc::getMessage('SONET_LOG_LIST_SONET_MODULE_NOT_INSTALLED'));
	return false;
}

final class SocialnetworkLogList extends \Bitrix\Socialnetwork\Component\LogList
{
	protected function setTitle()
	{
		global $APPLICATION;

		if ($this->arParams['SET_TITLE'] == 'Y')
		{
			$APPLICATION->setTitle(Loc::getMessage('SONET_LOG_LIST_PAGE_TITLE'));
		}

		if ($this->arParams['SET_NAV_CHAIN'] != 'N')
		{
			$APPLICATION->addChainItem(Loc::getMessage('SONET_LOG_LIST_PAGE_TITLE'));
		}
	}

	public function executeComponent()
	{
		\CPageOption::setOptionString('main', 'nav_page_in_session', 'N');

		$this->arResult = $this->prepareData();

		if (!empty($this->getErrors()))
		{
			$this->printErrors();
			return;
		}

		$this->includeComponentTemplate();
	}
}