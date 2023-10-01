<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if(!\Bitrix\Main\Loader::includeModule('advertising'))
	return;

Loc::loadMessages(__FILE__);

class AdvertisingBannerView extends \CBitrixComponent
{
	public function onPrepareComponentParams($params)
	{
		if (is_array($params['FILES']) && $params['CASUAL_PROPERTIES']['TYPE'] == 'template')
		{
			foreach ($params['FILES'] as $name => $id)
			{
				if ($id !== 'null')
					$params['FILES'][$name] = CFile::GetFileArray($id);
			}
		}
		elseif (isset($params['CASUAL_PROPERTIES']['IMG']))
		{
				$params['FILES']['CASUAL_IMG'] = CFile::GetFileArray(intval($params['CASUAL_PROPERTIES']['IMG']));
		}
		else
			$params['FILES'] = array();

		return $params;
	}

	public function executeComponent()
	{
		$this->includeComponentTemplate();
	}
}