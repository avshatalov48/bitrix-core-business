<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Hook\Page\Settings;

class StoreCartBlock extends \Bitrix\Landing\LandingBlock
{
	public function init(array $params = [])
	{
		\CUtil::initJSCore(array('fx'));

		$this->params = Settings::getDataForSite(
			$params['site_id']
		);
		$syspages = \Bitrix\Landing\Syspage::get(
			$params['site_id']
		);

		if (isset($syspages['catalog']))
		{
			$this->params['EMPTY_PATH'] = '#system_catalog';
		}
		else
		{
			$this->params['EMPTY_PATH'] = '#system_mainpage';
		}

		$this->params['SITE_ID'] = $params['site_id'];
		$this->params['LANDING_ID'] = $params['landing_id'];
	}
}