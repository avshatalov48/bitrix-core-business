<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Numerator\Numerator;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Numerator\Generator\SequentNumberGenerator;

Loc::loadMessages(__FILE__);

/**
 * Class MainNumeratorEdit
 */
class MainNumeratorEdit extends CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
	/** @inheritdoc */
	public function executeComponent()
	{
		$this->arResult['numeratorType'] = $this->arParams["NUMERATOR_TYPE"] ?? 'DEFAULT';
		$this->arResult['isEmbedMode'] = $this->arParams["IS_EMBED_FORM"] ?? false;
		$numeratorSettingsFields = Numerator::getSettingsFields($this->arResult['numeratorType']);
		$this->arResult['numeratorSettingsFields'] = $numeratorSettingsFields['settingsFields'];
		$this->arResult['numeratorTemplateWords'] = $numeratorSettingsFields['settingsWords'];

		if ($this->request->get('IFRAME'))
		{
			$this->arResult['IS_SLIDER'] = true;
		}

		if (isset($this->arParams['IS_SLIDER']) && $this->arParams['IS_SLIDER'] === false)
		{
			$this->arResult['IS_SLIDER'] = false;
		}

		$this->arResult['IS_EDIT'] = false;
		if (isset($this->arParams["~NUMERATOR_ID"]) && $this->arParams["~NUMERATOR_ID"])
		{
			$this->arResult['IS_EDIT'] = true;
		}
		$this->arResult['IS_SHOW_CHANGE_NUMBER'] = true;
		if (!is_null($this->arParams['IS_SHOW_CHANGE_NUMBER']))
		{
			$this->arResult['IS_SHOW_CHANGE_NUMBER'] = (bool)$this->arParams["IS_SHOW_CHANGE_NUMBER"];
		}

		$this->setHideSettings();
		$this->fillNumeratorConfigValues();
		$this->includeComponentTemplate();
	}

	private function setHideSettings()
	{
		$this->arResult['IS_HIDE_NUMERATOR_NAME'] = false;
		$this->arResult['IS_HIDE_PAGE_TITLE'] = false;
		$this->arResult['IS_HIDE_IS_DIRECT_NUMERATION'] = false;
		if (isset($this->arParams["IS_HIDE_NUMERATOR_NAME"]) && $this->arParams["IS_HIDE_NUMERATOR_NAME"])
		{
			$this->arResult['IS_HIDE_NUMERATOR_NAME'] = true;
		}
		if (isset($this->arParams["IS_HIDE_IS_DIRECT_NUMERATION"]) && $this->arParams["IS_HIDE_IS_DIRECT_NUMERATION"])
		{
			$this->arResult['IS_HIDE_IS_DIRECT_NUMERATION'] = true;
		}
		if (isset($this->arParams["IS_HIDE_PAGE_TITLE"]) && $this->arParams["IS_HIDE_PAGE_TITLE"])
		{
			$this->arResult['IS_HIDE_PAGE_TITLE'] = true;
		}
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function fillNumeratorConfigValues()
	{
		$numeratorId = (isset($this->arParams["NUMERATOR_ID"]) && $this->arParams["NUMERATOR_ID"])
			? intval($this->arParams["NUMERATOR_ID"]) : null;
		$numerator = Numerator::load($numeratorId);
		$numeratorConfiguration = $numerator ? $numerator->getConfig() : [];
		$this->indexArray($this->arResult['numeratorSettingsFields'], 'settingName');
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
					if (isset($generatorSettings['default']))
					{
						if (is_array($generatorSettings['default']))
						{
							$this->arResult['numeratorSettingsFields'][$generatorType][$key]['value'] = Loc::getMessage($generatorSettings['default']['name'], $generatorSettings['default']['replacement']);
						}
						else
						{
							$this->arResult['numeratorSettingsFields'][$generatorType][$key]['value'] = $generatorSettings['default'];
						}
						unset($this->arResult['numeratorSettingsFields'][$generatorType][$key]['default']);
					}
				}
			}
		}
		if ($numeratorId)
		{
			$this->addSequenceSettings($numerator, $numeratorId);
			$this->arResult['numeratorSettingsFields'][Numerator::getType()][] = ["settingName" => "id", "type" => 'hidden', 'value' => $numeratorId];
		}
		$this->arResult['numeratorSettingsFields'][Numerator::getType()][] = ['settingName' => 'type', 'type' => 'hidden', 'value' => $this->arResult['numeratorType']];
		$this->arResult['numeratorSettingsFields'][Numerator::getType()][] = ['settingName' => 'template', 'type' => 'hidden', 'value' => '',];
		if (isset($this->arResult['numeratorSettingsFields'][SequentNumberGenerator::getType()]))
		{
			foreach ($this->arResult['numeratorSettingsFields'][SequentNumberGenerator::getType()]['timezone']['values'] as $index => $_value)
			{
				$this->arResult['numeratorSettingsFields'][SequentNumberGenerator::getType()]['timezone']['values'][$index]['title'] = $_value['settingName'];
			}
			$this->arResult['numeratorSettingsFields'][SequentNumberGenerator::getType()]['timezoneToggle'] = [
				'type'        => 'linkToggle',
				'settingName' => 'timezoneToggle',
				'value'       => '',
				'title'       => Loc::getMessage('NUMERATOR_EDIT_TIMEZONE_LINK'),
			];
			if ($this->arResult['IS_HIDE_IS_DIRECT_NUMERATION'])
			{
				$this->arResult['numeratorSettingsFields'][SequentNumberGenerator::getType()]['isDirectNumeration']['type'] = 'hidden';
			}
		}
		if ($this->arResult['IS_HIDE_NUMERATOR_NAME'])
		{
			$this->arResult['numeratorSettingsFields'][Numerator::getType()]['name']['type'] = 'hidden';
		}
		$this->sortArray($this->arResult['numeratorSettingsFields']);
	}

	/**
	 * @param $array
	 */
	private function sortArray(&$array)
	{
		if (isset($array[Numerator::getType()]))
		{
			$sortedKeys = [
				'name',
				'template',
			];
			uksort($array[Numerator::getType()], function ($a, $b) use ($sortedKeys) {
				foreach ($sortedKeys as $value)
				{
					if ($a == $value)
					{
						return 0;
						break;
					}
					if ($b == $value)
					{
						return 1;
						break;
					}
				}
			});
		}
		if (isset($array[SequentNumberGenerator::getType()]))
		{
			$sortedKeys = [
				'start',
				'step',
				'length',
				'padString',
				'periodicBy',
				'timezoneToggle',
				'timezone',
				'isDirectNumeration',
			];
			uksort($array[SequentNumberGenerator::getType()], function ($a, $b) use ($sortedKeys) {
				foreach ($sortedKeys as $value)
				{
					if ($a == $value)
					{
						return 0;
						break;
					}
					if ($b == $value)
					{
						return 1;
						break;
					}
				}
			});
		}
	}

	/**
	 * @param $array
	 * @param $keyBy
	 */
	private function indexArray(&$array, $keyBy)
	{
		foreach ($array as $key => $numeratorSettingsField)
		{
			$indexedFields = [];
			for ($index = count($numeratorSettingsField) - 1; $index >= 0; $index--)
			{
				$fieldSettings = $numeratorSettingsField[$index];
				if (isset($fieldSettings[$keyBy]))
				{
					$indexedFields[$fieldSettings[$keyBy]] = $fieldSettings;
					unset($array[$key][$index]);
				}
				else
				{
					$indexedFields[$index] = $fieldSettings;
				}
			}
			$array[$key] = $indexedFields;
		}
	}

	/**
	 * @param Numerator $numerator
	 * @param $numeratorId
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function addSequenceSettings($numerator, $numeratorId)
	{
		if (in_array(SequentNumberGenerator::getType(), array_keys($this->arResult['numeratorSettingsFields'])))
		{
			if ($this->arResult['IS_EDIT'] && $this->arResult['IS_SHOW_CHANGE_NUMBER'])
			{
				$sequences = \Bitrix\Main\Numerator\Model\NumeratorSequenceTable::getList(
					[
						'filter' => ['=NUMERATOR_ID' => $numeratorId],
						'select' => ['NUMERATOR_ID', 'TEXT_KEY'],
					])
					->fetchAll();
				if ($sequences)
				{
					if (count($sequences) == 1)
					{
						$this->arResult['isMultipleSequences'] = false;
						$this->arResult['numeratorSettingsFields'][SequentNumberGenerator::getType()]['currentNumberForSequence'] = [
							'type'        => 'plain',
							'settingName' => 'currentNumberForSequence',
							'value'       => $numerator->previewNextSequentialNumber($sequences[0]['TEXT_KEY']),
							'toggleTitle' => Loc::getMessage('NUMERATOR_EDIT_TITLE_BITRIX_MAIN_SEQUENTNUMBERGENERATOR_SET_NUMBER_TOGGLE'),
						];
						$this->arResult['numeratorSettingsFields'][SequentNumberGenerator::getType()]['nextNumberForSequence'] = [
							'type'        => 'string',
							'settingName' => 'nextNumberForSequence',
							'title'       => Loc::getMessage('NUMERATOR_EDIT_TITLE_BITRIX_MAIN_SEQUENTNUMBERGENERATOR_NEW_NEXT_NUMBER'),
						];
					}
					else
					{
						$this->arResult['isMultipleSequences'] = true;
						$this->arResult['numeratorSettingsFields'][SequentNumberGenerator::getType()]['currentNumberForSequence'] = [
							'type'        => 'custom',
							'settingName' => 'currentNumberForSequence',
							'toggleTitle' => Loc::getMessage('NUMERATOR_EDIT_TITLE_BITRIX_MAIN_SEQUENTNUMBERGENERATOR_SET_NUMBER_TOGGLE_S'),
						];
					}
				}
			}
		}

	}

	/**
	 * @return array
	 */
	public function configureActions()
	{
		return [];
	}
}