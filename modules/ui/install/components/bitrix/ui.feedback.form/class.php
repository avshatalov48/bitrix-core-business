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
		if (empty($this->arParams['ID']))
		{
			return;
		}

		if (empty($this->arParams['FORMS']) || !is_array($this->arParams['FORMS']))
		{
			return;
		}

		$this->arParams['VIEW_TARGET'] = array_key_exists('VIEW_TARGET', $this->arParams) ? $this->arParams['VIEW_TARGET'] : 'pagetitle';
		$this->arParams['TITLE'] = isset($this->arParams['TITLE']) ? $this->arParams['TITLE'] : null;
		$this->arParams['PORTAL_URI'] = isset($this->arParams['PORTAL_URI']) ? $this->arParams['PORTAL_URI'] : null;
		$isCloud = Loader::includeModule('bitrix24');
		$this->arResult['FORM'] = $this->selectFeedbackForm($isCloud);
		if (!$this->arResult['FORM'])
		{
			return;
		}

		$this->arResult['PRESETS'] = $this->getFormPresets($isCloud);

		$this->includeComponentTemplate();
	}

	protected function getFormPresets($isCloud)
	{
		$presets = [];
		if (!empty($this->arParams['PRESETS']) && is_array($this->arParams['PRESETS']))
		{
			$presets = $this->arParams['PRESETS'];
		}

		$presets['b24_plan'] = $isCloud ? \CBitrix24::getLicenseType() : '';

		global $USER;
		$presets['c_name'] = is_object($USER) ? $USER->GetFirstName() : '';

		return $presets;
	}

	protected function selectFeedbackForm($isCloud)
	{
		$forms = $this->arParams['FORMS'];
		if ($isCloud)
		{
			$zone = \CBitrix24::getPortalZone();
			$defaultForm = null;
			foreach ($forms as $form)
			{
				if (!isset($form['zones']) || !is_array($form['zones']))
				{
					continue;
				}

				if (in_array($zone, $form['zones']))
				{
					return $form;
				}

				if (in_array('en', $form['zones']))
				{
					$defaultForm = $form;
				}
			}

			return $defaultForm;
		}
		else
		{
			$lang = LANGUAGE_ID;
			$defaultForm = null;
			foreach ($forms as $form)
			{
				if (!isset($form['lang']))
				{
					continue;
				}

				if ($lang === $form['lang'])
				{
					return $form;
				}

				if ($form['lang'] === 'en')
				{
					$defaultForm = $form;
				}
			}

			return $defaultForm;
		}
	}
}