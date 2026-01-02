<?php

use Bitrix\Landing\Manager;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

CBitrixComponent::includeComponentClass('bitrix:landing.blocks.mp_widget.base');

class LandingBlocksMainpageWidgetEntWest extends LandingBlocksMainpageWidgetBase
{
	private const WIDGET_CSS_VAR_PROPERTIES = [];

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$this->initializeParams();
		$this->getData();
		parent::executeComponent();
	}

	protected function initializeParams(): void
	{
		foreach (self::WIDGET_CSS_VAR_PROPERTIES as $property => $cssVar)
		{
			$this->addCssVarProperty($property, $cssVar);
		}
	}

	protected function getData(): void
	{
		$this->arResult['ZONE']  = Manager::getZone();
		$this->arResult['REGION']  = Application::getInstance()->getLicense()->getRegion();

		if (Loader::includeModule('bitrix24'))
		{
			$licenseType = \CBitrix24::getLicenseType();

			$this->arResult['IS_TARIFF_FREE'] = $licenseType === 'project';
			$this->arResult['IS_TRIAL_ACTIVE'] = $licenseType === 'demo';
			$this->arResult['IS_TRIALABLE'] = (new Bitrix\Bitrix24\License\DemoLicense)->isAvailable();
		}
	}
}
