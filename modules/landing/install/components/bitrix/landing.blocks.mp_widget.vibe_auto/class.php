<?php

use Bitrix\Main\Loader;
use Bitrix\UI\Form\FormProvider;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

CBitrixComponent::includeComponentClass('bitrix:landing.blocks.mp_widget.base');

class LandingBlocksMainpageVibeAuto extends LandingBlocksMainpageWidgetBase
{
	private const WIDGET_CSS_VAR_PROPERTIES = [];
	private const MODULE_ID = 'landing';

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$this->initializeParams();
		$this->prepareResult();
		parent::executeComponent();
	}

	protected function initializeParams(): void
	{
		foreach (self::WIDGET_CSS_VAR_PROPERTIES as $property => $cssVar)
		{
			$this->addCssVarProperty($property, $cssVar);
		}
	}

	protected function prepareResult(): void
	{
		$this->arResult['FEEDBACK_FORM'] = $this->getFeedbackFormData();
		$this->arResult['TYPE'] = $this->arParams['TYPE'] ?? '1';
	}

	private function getFeedbackFormData(): ?array
	{
		if (!Loader::includeModule('ui'))
		{
			$portalUri = (new Bitrix\UI\Form\UrlProvider)->getPartnerPortalUrl();
			$forms =  (new Bitrix\UI\Form\FormProvider)->getPartnerFormList();
		}

		return [
			'id' => 'landing-feedback-mainpage',
			'forms' => (new FormProvider())->getPartnerFormList(),
			'presets' => [
				'source' => self::MODULE_ID,
			],
			'portal' => (new Bitrix\UI\Form\UrlProvider)->getPartnerPortalUrl(),
		];
	}
}
