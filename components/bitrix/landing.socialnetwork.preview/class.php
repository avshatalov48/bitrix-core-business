<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Loader;
use \Bitrix\Landing\Landing\UrlPreview;

class LandingSocialnetworkPreviewComponent extends \CBitrixComponent
{
	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$this->arResult['PREVIEW'] = [];
		$landingId = isset($this->arParams['LANDING_ID'])
					? (int)$this->arParams['LANDING_ID']
					: 0;

		if ($landingId && Loader::includeModule('landing'))
		{
			\Bitrix\Landing\Hook::setEditMode(true);
			$this->arResult['PREVIEW'] = UrlPreview::getPreview($landingId);
		}

		if ($this->arResult['PREVIEW'])
		{
			$this->includeComponentTemplate();
		}
	}
}