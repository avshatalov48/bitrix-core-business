<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\CBitrixComponent::includeComponentClass('bitrix:landing.base.form');

class LandingDomainEditComponent extends LandingBaseFormComponent
{
	/**
	 * Class of current element.
	 * @var string
	 */
	protected $class = 'Domain';

	/**
	 * Local version of table map with available fields for change.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'ACTIVE', 'DOMAIN', 'PROTOCOL'
		);
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$init = $this->init();

		if ($init)
		{
			$this->checkParam('DOMAIN_ID', 0);
			$this->checkParam('PAGE_URL_DOMAINS', '');

			$this->id = $this->arParams['DOMAIN_ID'];
			$this->successSavePage = $this->arParams['PAGE_URL_DOMAINS'];

			$this->arResult['DOMAIN'] = $this->getRow();

			if (!$this->arResult['DOMAIN'])
			{
				$this->id = 0;
			}
		}

		parent::executeComponent();
	}
}