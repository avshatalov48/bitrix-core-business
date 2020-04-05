<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingDomainsComponent extends LandingBaseComponent
{
	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$init = $this->init();

		if ($init)
		{
			$this->checkParam('PAGE_URL_DOMAIN_EDIT', '');
			$this->arResult['DOMAINS'] = $this->getDomains();
		}

		parent::executeComponent();
	}
}