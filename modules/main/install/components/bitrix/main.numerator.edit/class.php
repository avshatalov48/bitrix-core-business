<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true){die();}

use Bitrix\Main\Numerator\Numerator;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class MainNumeratorEdit
 */
class MainNumeratorEdit extends CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
	/** @inheritdoc */
	public function executeComponent()
	{
		$this->arResult['numeratorType'] = isset($this->arParams["~NUMERATOR_TYPE"]) ? $this->arParams["~NUMERATOR_TYPE"] : 'DEFAULT';
		$numeratorSettingsFields = Numerator::getSettingsFields($this->arResult['numeratorType']);
		$this->arResult['numeratorSettingsFields'] = $numeratorSettingsFields['settingsFields'];
		$this->arResult['numeratorTemplateWords'] = $numeratorSettingsFields['settingsWords'];
		if ($this->arParams['IS_SLIDER'] || $this->request->get('IFRAME'))
		{
			$this->arResult['IS_SLIDER'] = true;
		}

		$this->arResult['IS_EDIT'] = false;
		if (isset($this->arParams["~NUMERATOR_ID"]) && $this->arParams["~NUMERATOR_ID"])
		{
			$this->arResult['IS_EDIT'] = true;
		}

		$this->fillNumeratorConfigValues();
		$this->setHideSettings();
		$this->includeComponentTemplate();
	}

	private function setHideSettings()
	{
		$this->arResult['HIDE_NUMERATOR_NAME'] = false;
		$this->arResult['HIDE_IS_DIRECT_NUMERATION'] = false;
		if (isset($this->arParams["~HIDE_NUMERATOR_NAME"]) && $this->arParams["~HIDE_NUMERATOR_NAME"])
		{
			$this->arResult['HIDE_NUMERATOR_NAME'] = true;
		}
		if (isset($this->arParams["~HIDE_IS_DIRECT_NUMERATION"]) && $this->arParams["~HIDE_IS_DIRECT_NUMERATION"])
		{
			$this->arResult['HIDE_IS_DIRECT_NUMERATION'] = true;
		}
	}

	/**
	 * @param $settings
	 * @return string
	 */
	public function getDefaultValueFromSettings($settings)
	{
		if (isset($settings['default']))
		{
			if (is_array($settings['default']))
			{
				return Loc::getMessage($settings['default']['name'], $settings['default']['replacement']);
			}
			return $settings['default'];
		}
		return '';
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function fillNumeratorConfigValues()
	{
		$numeratorId = (isset($this->arParams["~NUMERATOR_ID"]) && $this->arParams["~NUMERATOR_ID"])
			? intval($this->arParams["~NUMERATOR_ID"]) : null;
		$numerator = Numerator::load($numeratorId);
		$numeratorConfiguration = $numerator ? $numerator->getConfig() : [];
		foreach ($this->arResult['numeratorSettingsFields'] as $generatorType => $generatorSettingsField)
		{
			foreach ($generatorSettingsField as $key => $generatorSettings)
			{
				if (isset($numeratorConfiguration[$generatorType][$generatorSettings['settingName']]))
				{
					$this->arResult['numeratorSettingsFields'][$generatorType][$key]['value'] = $numeratorConfiguration[$generatorType][$generatorSettings['settingName']];
				}
				else
				{
					$this->arResult['numeratorSettingsFields'][$generatorType][$key]['value'] = '';
				}
			}
		}
		if ($numeratorId)
		{
			$this->arResult['numeratorSettingsFields'][Numerator::getType()][] = ["settingName" => "id", "type" => 'hidden', 'value' => $numeratorId];
		}
		$this->arResult['numeratorSettingsFields'][Numerator::getType()][] = ["settingName" => "type", "type" => 'hidden', 'value' => $this->arResult['numeratorType']];
		$this->arResult['numeratorSettingsFields'][Numerator::getType()][] = ["settingName" => "template", "type" => 'hidden', 'value' => '',];
	}

	/**
	 * @return array
	 */
	public function configureActions()
	{
		return [];
	}

	/**
	 * @return static
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	public function saveAction()
	{
		if (!check_bitrix_sessid())
		{
			return AjaxJson::createError(new ErrorCollection([new Error('Error')]));
		}
		$id = $_POST[Numerator::getType()]['id'];
		if ($id)
		{
			$result = Numerator::update($id, $_POST);
		}
		else
		{
			$numerator = Numerator::create();
			$result = $numerator->setConfig($_POST);
			if ($result->isSuccess())
			{
				$result = $numerator->save();
				$id = $result->getId();
			}
		}

		if (!$result->isSuccess())
		{
			$errors = new ErrorCollection();
			foreach ($result->getErrorCollection() as $index => $error)
			{
				$errors->add([new Error($error->getMessage())]);
			}
			return AjaxJson::createError($errors);
		}
		return AjaxJson::createSuccess(['id' => $id]);
	}
}