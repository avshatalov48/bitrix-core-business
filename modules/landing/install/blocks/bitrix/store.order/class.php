<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Hook\Page\Settings;
use \Bitrix\Main\Localization\Loc;

class StoreOrderBlock extends \Bitrix\Landing\LandingBlock
{
	public function init(array $params = [])
	{
		$this->params = Settings::getDataForSite(
			$params['site_id']
		);
		$syspages = \Bitrix\Landing\Syspage::get(
			$params['site_id'],
			true
		);

		$this->params['NO_PERSONAL'] = !isset($syspages['personal']) ? 'Y' : 'N';
		$this->params['USER_CONSENT'] = ($this->params['AGREEMENT_ID'] > 0) ? 'Y' : 'N';
		$this->params['MESS_REGION_BLOCK_NAME'] = Loc::getMessage('LANDING_BLOCK_STORE_ORDER--REGION_NAME');
		$this->params['SITE_ID'] = $params['site_id'];
		$this->params['LANDING_ID'] = $params['landing_id'];

		if (isset($syspages['catalog']))
		{
			$this->params['EMPTY_PATH'] = '#system_catalog';
		}
		else
		{
			$this->params['EMPTY_PATH'] = '#system_mainpage';
		}

		Loc::loadMessages(
			\Bitrix\Main\Application::getDocumentRoot() .
			'/bitrix/blocks/bitrix/store.order/block.php'
		);
	}
}