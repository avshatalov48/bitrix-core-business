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

		if (\Bitrix\Main\Loader::includeModule('catalog'))
		{
			$iblockInfo = \CCatalogSku::GetInfoByIBlock($this->params['IBLOCK_ID']);
			if (!empty($iblockInfo) && $iblockInfo['IBLOCK_ID'] !== $iblockInfo['PRODUCT_IBLOCK_ID'])
			{
				$this->params['SKU_IBLOCK_ID'] = $iblockInfo['IBLOCK_ID'];
			}
		}

		if (isset($syspages['catalog']))
		{
			$this->params['EMPTY_PATH'] = '#landing' . $syspages['catalog']['LANDING_ID'];
		}
		else
		{
			$this->params['EMPTY_PATH'] = '#system_mainpage';
		}

		$this->params['SITE_ID'] = $params['site_id'];
		$this->params['LANDING_ID'] = $params['landing_id'];
	}
}