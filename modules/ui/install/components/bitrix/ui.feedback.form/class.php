<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;

/**
 * Class UiFeedbackForm
 */
class UiFeedbackForm extends CBitrixComponent
{

	public function executeComponent()
	{
		if (!Loader::includeModule('ui'))
		{
			return;
		}
		if (empty($this->arParams['ID']))
		{
			return;
		}
		$this->arParams['ID'] = strval($this->arParams['ID']);

		if (empty($this->arParams['FORMS']) || !is_array($this->arParams['FORMS']))
		{
			return;
		}

		$feedbackForm = new \Bitrix\UI\Form\FeedbackForm($this->arParams['ID']);
		$feedbackForm->setFormParams($this->arParams['FORMS']);
		$feedbackForm->setPresets(is_array($this->arParams['PRESETS']) ? $this->arParams['PRESETS'] : []);
		if (isset($this->arParams['TITLE']))
		{
			$feedbackForm->setTitle($this->arParams['TITLE']);
		}
		if (isset($this->arParams['PORTAL_URI']))
		{
			$feedbackForm->setPortalUri($this->arParams['PORTAL_URI']);
		}

		$this->arResult['FORM'] = $feedbackForm->getFormParams();
		if (!$this->arResult['FORM'])
		{
			return;
		}
		$this->arResult['PRESETS'] = $feedbackForm->getPresets();
		$this->arResult['TITLE'] = $feedbackForm->getTitle();
		$this->arResult['PORTAL_URI'] = $feedbackForm->getPortalUri();
		$this->arResult['JS_OBJECT_PARAMS'] = $feedbackForm->getJsObjectParams();

		$this->arParams['VIEW_TARGET'] = array_key_exists('VIEW_TARGET', $this->arParams) ? $this->arParams['VIEW_TARGET'] : 'pagetitle';

		$this->includeComponentTemplate();
	}
}