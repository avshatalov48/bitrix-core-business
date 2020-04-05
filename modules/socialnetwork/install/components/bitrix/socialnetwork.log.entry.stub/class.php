<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use \Bitrix\Main\Localization\Loc;

final class SocialnetworkLogEntryStub extends CBitrixComponent
{
	/**
	 * Execute component.
	 */
	public function executeComponent()
	{
		if (!isset($this->arParams['EVENT']) || !is_array($this->arParams['EVENT']))
		{
			$this->arParams['EVENT'] = [];
		}

		$this->arResult['MESSAGE'] = '';
		$this->arResult['FEATURE'] = '';
		$this->arResult['EVENT_ID'] = (!empty($this->arParams['EVENT']['EVENT_ID']) ? $this->arParams['EVENT']['EVENT_ID'] : '');

		if (!empty($this->arResult['EVENT_ID']))
		{
			switch($this->arResult['EVENT_ID'])
			{
				case 'timeman_entry':
					$this->arResult['MESSAGE'] = Loc::getMessage('SLEB_MESSAGE_TIMEMAN_ENTRY');
					$this->arResult['FEATURE'] = 'timeman';
					break;
				case 'report':
					$this->arResult['MESSAGE'] = Loc::getMessage('SLEB_MESSAGE_REPORT');
					$this->arResult['FEATURE'] = 'timeman';
					break;
				default:
			}
		}

		if (\Bitrix\Main\Loader::includeModule("bitrix24"))
		{
			$this->includeComponentTemplate();
		}
	}
}