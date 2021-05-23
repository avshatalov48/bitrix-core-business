<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Manager;

\CBitrixComponent::includeComponentClass('bitrix:landing.site_edit');

class LandingDomainRenameComponent extends LandingSiteEditComponent
{
	/**
	 * Returns postfix for domain.
	 * @return string
	 */
	protected function getPostFix()
	{
		$zone = Manager::getZone();
		$postfix = '.bitrix24.site';

		if ($this->arParams['TYPE'] == 'STORE')
		{
			$postfix = ($zone == 'by')
				? '.bitrix24shop.by'
				: '.bitrix24.shop';
		}
		else if ($zone == 'by')
		{
			$postfix = '.bitrix24site.by';
		}
		else if ($zone == 'ua')
		{
			$postfix = '.bitrix24site.ua';
		}

		return $postfix;
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
			// params
			$this->checkParam('TYPE', '');
			$this->checkParam('FIELD_NAME', 'DOMAIN_ID');
			$this->checkParam('FIELD_ID', 'domain_id');
			$this->checkParam('DOMAIN_NAME', '');
			$this->checkParam('DOMAIN_ID', 0);
			$this->arResult['POSTFIX'] = $this->getPostFix();
			$puny = new \CBXPunycode;

			// template data
			$this->arResult['IP_FOR_DNS'] = $this->getIpForDNS();
			$this->arResult['DOMAINS'] = $this->getDomains();

			// domain name
			if ($this->arParams['DOMAIN_NAME'])
			{
				$this->arResult['DOMAIN_NAME'] = $this->arParams['DOMAIN_NAME'];
			}
			else
			{
				$this->arResult['DOMAIN_NAME'] = isset($this->arResult['DOMAINS'][$this->arParams['DOMAIN_ID']]['DOMAIN'])
					? $this->arResult['DOMAINS'][$this->arParams['DOMAIN_ID']]['DOMAIN']
					: '';
			}
			$this->arResult['DOMAIN_NAME_ORIGINAL'] = $this->arResult['DOMAIN_NAME'];
			$this->arResult['DOMAIN_NAME'] = $puny->decode($this->arResult['DOMAIN_NAME']);
		}

		parent::executeComponent();
	}
}