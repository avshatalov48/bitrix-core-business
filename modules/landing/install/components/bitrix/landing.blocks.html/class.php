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
		if (isset($this->arParams['~HTML_CODE']))
		{
			$this->arParams['~HTML_CODE'] = str_replace(
				'&lt;script',
				'&lt;script data-skip-moving="true"',
				$this->arParams['~HTML_CODE']
			);
		}
		else
		{
			$this->arParams['~HTML_CODE'] = '';
		}
		$this->IncludeComponentTemplate();
	}
}