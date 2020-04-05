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
		if (
			isset($this->arParams['MESSAGE']) &&
			trim($this->arParams['MESSAGE'])
		)
		{
			$this->IncludeComponentTemplate();
		}
	}
}