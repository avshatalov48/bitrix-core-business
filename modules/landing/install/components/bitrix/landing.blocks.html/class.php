<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class LandingBlocksHtmlComponent extends \CBitrixComponent
{
	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$this->IncludeComponentTemplate();
	}
}