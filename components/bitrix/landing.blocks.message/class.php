<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class LandingBlocksMessageComponent extends \CBitrixComponent
{
	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		if (!\Bitrix\Main\Loader::includeModule('landing'))
		{
			return;
		}

		$codes = [
			'HEADER', 'MESSAGE', 'BUTTON', 'LINK'
		];

		foreach ($codes as $code)
		{
			if (!isset($this->arParams[$code]))
			{
				$this->arParams[$code] = '';
			}
			if (!isset($this->arParams['~' . $code]))
			{
				$this->arParams['~' . $code] = '';
			}
		}

		$this->includeComponentTemplate();
	}
}