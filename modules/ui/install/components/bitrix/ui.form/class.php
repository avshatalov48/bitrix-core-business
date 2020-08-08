<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main;
use Bitrix\UI;

Main\Loader::includeModule('ui');

/**
 * Class UIFormComponent
 */
class UIFormComponent extends \CBitrixComponent
{
	private const COLUMN_TYPE = 'column';
	private const SECTION_TYPE = 'section';
	private const INCLUDED_AREA_TYPE = 'included_area';

	/** @var int */
	protected $userID = 0;
	/** @var string */
	protected $entityTypeName = '';
	/** @var int */
	protected $entityID = 0;
	/** @var string */
	protected $guid = '';
	/** @var string */
	protected $configID = '';
	/** @var string */
	protected $optionID = '';

	/**
	 * Execute component.
	 */
	public function executeComponent()
	{
		$this->initialize();
		$this->emitOnUIFormInitializeEvent();
		$this->includeComponentTemplate();
	}

	protected function emitOnUIFormInitializeEvent()
	{
		$event = new Main\Event('ui', 'onUIFormInitialize', ['TEMPLATE' => $this->getTemplateName()]);
		$event->send();
	}

	protected function initialize()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$this->guid = $this->arResult['GUID'] = isset($this->arParams['GUID']) ? $this->arParams['GUID'] : 'form_editor';
		$this->configID = $this->arResult['CONFIG_ID'] = isset($this->arParams['CONFIG_ID']) ? $this->arParams['CONFIG_ID'] : $this->guid;

		$this->arResult['READ_ONLY'] = isset($this->arParams['~READ_ONLY'])
			&& $this->arParams['~READ_ONLY'];

		$this->arResult['INITIAL_MODE'] = isset($this->arParams['~INITIAL_MODE'])
			? $this->arParams['~INITIAL_MODE'] : '';

		$this->arResult['ENABLE_MODE_TOGGLE'] = !isset($this->arParams['~ENABLE_MODE_TOGGLE'])
			|| $this->arParams['~ENABLE_MODE_TOGGLE'];

		$this->arResult['ENABLE_CONFIG_CONTROL'] = !isset($this->arParams['~ENABLE_CONFIG_CONTROL'])
			|| $this->arParams['~ENABLE_CONFIG_CONTROL'];

		$this->arResult['ENABLE_VISIBILITY_POLICY'] = !isset($this->arParams['~ENABLE_VISIBILITY_POLICY'])
			|| $this->arParams['~ENABLE_VISIBILITY_POLICY'];

		$this->arResult['ENABLE_TOOL_PANEL'] = !isset($this->arParams['~ENABLE_TOOL_PANEL'])
			|| $this->arParams['~ENABLE_TOOL_PANEL'];

		$this->arResult['ENABLE_BOTTOM_PANEL'] = !isset($this->arParams['~ENABLE_BOTTOM_PANEL'])
			|| $this->arParams['~ENABLE_BOTTOM_PANEL'];

		$this->arResult['ENABLE_FIELDS_CONTEXT_MENU'] = !isset($this->arParams['~ENABLE_FIELDS_CONTEXT_MENU'])
			|| $this->arParams['~ENABLE_FIELDS_CONTEXT_MENU'];

		$this->arResult['IS_EMBEDDED'] = isset($this->arParams['~IS_EMBEDDED'])
			&& $this->arParams['~IS_EMBEDDED'];

		$this->entityTypeName = isset($this->arParams['ENTITY_TYPE_NAME'])
			? $this->arParams['ENTITY_TYPE_NAME'] : '';
		$this->entityID = isset($this->arParams['ENTITY_ID'])
			? (int)$this->arParams['ENTITY_ID'] : 0;

		$this->arResult['ENTITY_TYPE_NAME'] = $this->entityTypeName;
		$this->arResult['ENTITY_ID'] = $this->entityID;

		$this->arResult['IS_IDENTIFIABLE_ENTITY'] = !isset($this->arParams['~IS_IDENTIFIABLE_ENTITY'])
			|| $this->arParams['~IS_IDENTIFIABLE_ENTITY'];

		$this->arResult['ENTITY_DATA'] = isset($this->arParams['~ENTITY_DATA']) && is_array($this->arParams['~ENTITY_DATA'])
			? $this->arParams['~ENTITY_DATA'] : array();

		$this->arResult['ENTITY_FIELDS'] = isset($this->arParams['~ENTITY_FIELDS']) && is_array($this->arParams['~ENTITY_FIELDS'])
			? $this->arParams['~ENTITY_FIELDS'] : array();

		$this->arResult['ENTITY_VALIDATORS'] = isset($this->arParams['~ENTITY_VALIDATORS']) && is_array($this->arParams['~ENTITY_VALIDATORS'])
			? $this->arParams['~ENTITY_VALIDATORS'] : array();

		$this->arResult['DETAIL_MANAGER_ID'] = isset($this->arParams['~DETAIL_MANAGER_ID']) ? $this->arParams['~DETAIL_MANAGER_ID'] : '';

		$configuration = new UI\Form\EntityEditorConfiguration();

		//Trying get scope from params
		$configScope = isset($this->arParams['~ENTITY_CONFIG_SCOPE'])
			? $this->arParams['~ENTITY_CONFIG_SCOPE'] : UI\Form\EntityEditorConfigScope::UNDEFINED;
		if(!UI\Form\EntityEditorConfigScope::isDefined($configScope))
		{
			//Trying resolve scope from configuration
			$configScope = $configuration->getScope($this->configID);
		}

		$config = null;
		if(!(isset($this->arParams['~FORCE_DEFAULT_CONFIG']) && $this->arParams['~FORCE_DEFAULT_CONFIG']))
		{
			if(UI\Form\EntityEditorConfigScope::isDefined($configScope))
			{
				$config = $configuration->get($this->configID, $configScope);
			}
			else
			{
				//Try to resolve current scope by stored configuration
				$config = $configuration->get($this->configID, UI\Form\EntityEditorConfigScope::PERSONAL);
				if(is_array($config) && !empty($config))
				{
					$configScope = UI\Form\EntityEditorConfigScope::PERSONAL;
				}
				else
				{
					$config = $configuration->get($this->configID, UI\Form\EntityEditorConfigScope::COMMON);
					$configScope = UI\Form\EntityEditorConfigScope::COMMON;
				}
			}
		}
		elseif($configScope === UI\Form\EntityEditorConfigScope::UNDEFINED)
		{
			$configScope = is_array($configuration->get($this->configID, UI\Form\EntityEditorConfigScope::PERSONAL))
				? UI\Form\EntityEditorConfigScope::PERSONAL
				: UI\Form\EntityEditorConfigScope::COMMON;
		}

		$defaultConfig = array();
		if(!is_array($config) || empty($config))
		{
			$config = isset($this->arParams['~ENTITY_CONFIG']) && is_array($this->arParams['~ENTITY_CONFIG'])
				? $this->arParams['~ENTITY_CONFIG'] : array();
		}
		elseif(isset($this->arParams['~ENTITY_CONFIG']) && is_array($this->arParams['~ENTITY_CONFIG']))
		{
			foreach($this->arParams['~ENTITY_CONFIG'] as $section)
			{
				$defaultConfig[$section['name']] = $section;
			}
		}

		if (!empty($defaultConfig) && $this->arParams["~FORCE_DEFAULT_SECTION_NAME"])
		{
			foreach ($config as $key => $section)
			{
				if (isset($defaultConfig[$section["name"]]))
				{
					$config[$key]["title"] = $defaultConfig[$section["name"]]["title"];
				}
			}
		}

		$this->arResult['ENTITY_CONTROLLERS'] = isset($this->arParams['~ENTITY_CONTROLLERS']) && is_array($this->arParams['~ENTITY_CONTROLLERS'])
			? $this->arParams['~ENTITY_CONTROLLERS'] : array();

		$availableFields = array();

		$requiredFields = array();
		$hasEmptyRequiredFields = false;
		$htmlFieldNames = array();
		foreach($this->arResult['ENTITY_FIELDS'] as $field)
		{
			$name = isset($field['name']) ? $field['name'] : '';
			if($name === '')
			{
				continue;
			}

			$fieldType = $field['type'] ?? '';
			if($fieldType === 'html')
			{
				$htmlFieldNames[] = $name;
			}

			$availableFields[$name] = $field;
			if(isset($field['required']) && $field['required'] === true)
			{
				$requiredFields[$name] = $field;
				if($hasEmptyRequiredFields)
				{
					continue;
				}

				//HACK: Skip if user field of type Boolean. Absence of value is treated as equivalent to FALSE.
				if($fieldType === 'userField')
				{
					$fieldInfo = isset($field['data']) && isset($field['data']['fieldInfo'])
						? $field['data']['fieldInfo'] : array();

					if(isset($fieldInfo['USER_TYPE_ID']) && $fieldInfo['USER_TYPE_ID'] === 'boolean')
					{
						continue;
					}
				}

				if(isset($this->arResult['ENTITY_DATA'][$name])
					&& is_array($this->arResult['ENTITY_DATA'][$name])
					&& isset($this->arResult['ENTITY_DATA'][$name]['IS_EMPTY'])
					&& $this->arResult['ENTITY_DATA'][$name]['IS_EMPTY']
				)
				{
					$hasEmptyRequiredFields = true;
				}
			}
		}

		$config = $this->initializeConfigWithColumns($config);
		$scheme = [];

		$primaryColumnIndex = 0;
		$primarySectionIndex = 0;

		$serviceColumnIndex = 0;
		$serviceSectionIndex = -1;

		foreach ($config as $j => $column)
		{
			$columnScheme = [];

			foreach ($column['elements'] as $i => $section)
			{
				$type = $section['type'] ?? '';

				if ($type !== self::SECTION_TYPE && $type !== self::INCLUDED_AREA_TYPE)
				{
					continue;
				}

				$sectionName = $section['name'] ?? '';

				if ($sectionName === 'main')
				{
					$primaryColumnIndex = $j;
					$primarySectionIndex = $i;
				}
				elseif ($sectionName === 'required')
				{
					$serviceColumnIndex = $j;
					$serviceSectionIndex = $i;
				}

				if (!empty($defaultConfig[$sectionName]['data']))
				{
					$section['data'] = $defaultConfig[$sectionName]['data'];
				}

				$elements = isset($section['elements']) && is_array($section['elements'])
					? $section['elements'] : [];

				$schemeElements = [];

				foreach ($elements as $element)
				{
					$name = $element['name'] ?? '';

					if ($name === '')
					{
						continue;
					}

					$schemeElement = $availableFields[$name];
					$fieldType = $schemeElement['type'] ?? '';

					//User fields in common scope must have original names.
					$title = '';
					if (isset($element['title'])
						&& !($fieldType === 'userField' && $configScope === UI\Form\EntityEditorConfigScope::COMMON)
					)
					{
						$title = $element['title'];
					}

					if ($title !== '')
					{
						if (isset($schemeElement['title']))
						{
							$schemeElement['originalTitle'] = $schemeElement['title'];
						}

						$schemeElement['title'] = $title;
					}

					if (isset($element['optionFlags']))
					{
						$schemeElement['optionFlags'] = (int)$element['optionFlags'];
					}

					$schemeElements[] = $schemeElement;
					unset($availableFields[$name]);

					if (isset($requiredFields[$name]))
					{
						unset($requiredFields[$name]);
					}
				}

				$columnScheme[] = array_merge($section, ['elements' => $schemeElements]);
			}

			$scheme[] = array_merge($column, ['elements' => $columnScheme]);
		}

		//Add section 'Required Fields'
		if(!$this->arResult['READ_ONLY'])
		{
			//Force Edit mode if empty required fields are found.
			if($hasEmptyRequiredFields)
			{
				$this->arResult['INITIAL_MODE'] = 'edit';
			}

			// todo check required fields
			if(!empty($requiredFields))
			{
				$schemeElements = array();
				if($serviceSectionIndex >= 0)
				{
					$section = $config[$serviceColumnIndex][$serviceSectionIndex];
					if(
						isset($scheme[$serviceColumnIndex][$serviceSectionIndex]['elements'])
						&& is_array($scheme[$serviceColumnIndex][$serviceSectionIndex]['elements'])
					)
					{
						$schemeElements = $scheme[$serviceColumnIndex][$serviceSectionIndex]['elements'];
					}
				}
				else
				{
					$section = array(
						'name' => 'required',
						'title' => Main\Localization\Loc::getMessage('UI_FORM_REQUIRED_FIELD_SECTION'),
						'type' => self::SECTION_TYPE,
						'elements' => array()
					);

					$serviceColumnIndex = $primaryColumnIndex;
					$serviceSectionIndex = $primarySectionIndex + 1;

					array_splice(
						$config[$serviceColumnIndex],
						$serviceSectionIndex,
						0,
						array($section)
					);

					array_splice(
						$scheme[$serviceColumnIndex],
						$serviceSectionIndex,
						0,
						array(array_merge($section, array('elements' => array())))
					);
				}

				foreach($requiredFields as $fieldName => $fieldInfo)
				{
					$section['elements'][] = array('name' => $fieldName);
					$schemeElements[] = $fieldInfo;
				}

				$scheme[$serviceColumnIndex][$serviceSectionIndex]['elements'] = $schemeElements;
			}
		}

		$this->arResult['ENABLE_CONFIG_SCOPE_TOGGLE'] = !isset($this->arParams['~ENABLE_CONFIG_SCOPE_TOGGLE'])
			|| $this->arParams['~ENABLE_CONFIG_SCOPE_TOGGLE'];
		$this->arResult['ENTITY_CONFIG_SCOPE'] = $configScope;
		$this->arResult['ENTITY_CONFIG'] = $config;
		$this->arResult['ENTITY_SCHEME'] = $scheme;

		$this->arResult['ENTITY_AVAILABLE_FIELDS'] = array_values($availableFields);
		$this->arResult['ENTITY_HTML_FIELD_NAMES'] = $htmlFieldNames;

		$this->arResult['ENABLE_AJAX_FORM'] = !isset($this->arParams['~ENABLE_AJAX_FORM'])
			|| $this->arParams['~ENABLE_AJAX_FORM'];

		$this->arResult['ENABLE_REQUIRED_USER_FIELD_CHECK'] = !isset($this->arParams['~ENABLE_REQUIRED_USER_FIELD_CHECK'])
			|| $this->arParams['~ENABLE_REQUIRED_USER_FIELD_CHECK'];

		$this->arResult['ENABLE_USER_FIELD_CREATION'] = isset($this->arParams['~ENABLE_USER_FIELD_CREATION'])
			&& $this->arParams['~ENABLE_USER_FIELD_CREATION'];
		$this->arResult['ENABLE_USER_FIELD_MANDATORY_CONTROL'] = !isset($this->arParams['~ENABLE_USER_FIELD_MANDATORY_CONTROL'])
			|| $this->arParams['~ENABLE_USER_FIELD_MANDATORY_CONTROL'];
		$this->arResult['USER_FIELD_ENTITY_ID'] = isset($this->arParams['~USER_FIELD_ENTITY_ID'])
			? $this->arParams['~USER_FIELD_ENTITY_ID'] : '';
		$this->arResult['USER_FIELD_PREFIX'] = isset($this->arParams['~USER_FIELD_PREFIX'])
			? $this->arParams['~USER_FIELD_PREFIX'] : '';
		$this->arResult['USER_FIELD_CREATE_PAGE_URL'] = isset($this->arParams['~USER_FIELD_CREATE_PAGE_URL'])
			? $this->arParams['~USER_FIELD_CREATE_PAGE_URL'] : '';
		$this->arResult['USER_FIELD_CREATE_SIGNATURE'] = isset($this->arParams['~USER_FIELD_CREATE_SIGNATURE'])
			? $this->arParams['~USER_FIELD_CREATE_SIGNATURE'] : '';

		if(isset($this->arParams['~ENABLE_CONFIGURATION_UPDATE']))
		{
			$this->arResult['CAN_UPDATE_PERSONAL_CONFIGURATION'] = $this->arParams['~ENABLE_CONFIGURATION_UPDATE'];
			$this->arResult['CAN_UPDATE_COMMON_CONFIGURATION'] = $this->arParams['~ENABLE_CONFIGURATION_UPDATE'];
		}
		else
		{
			$this->arResult['CAN_UPDATE_PERSONAL_CONFIGURATION'] = !isset($this->arParams['~ENABLE_PERSONAL_CONFIGURATION_UPDATE'])
				|| $this->arParams['~ENABLE_PERSONAL_CONFIGURATION_UPDATE'];

			$this->arResult['CAN_UPDATE_COMMON_CONFIGURATION'] = isset($this->arParams['~ENABLE_COMMON_CONFIGURATION_UPDATE'])
				&& $this->arParams['~ENABLE_COMMON_CONFIGURATION_UPDATE'];
		}

		$this->arResult['ENABLE_SETTINGS_FOR_ALL'] = isset($this->arParams['~ENABLE_SETTINGS_FOR_ALL'])
			&& $this->arParams['~ENABLE_SETTINGS_FOR_ALL'];

		$this->arResult['ENABLE_SECTION_EDIT'] = isset($this->arParams['~ENABLE_SECTION_EDIT'])
			&& $this->arParams['~ENABLE_SECTION_EDIT'];

		$this->arResult['ENABLE_SECTION_CREATION'] = isset($this->arParams['~ENABLE_SECTION_CREATION'])
			&& $this->arParams['~ENABLE_SECTION_CREATION'];

		$this->arResult['ENABLE_SECTION_DRAG_DROP'] = !isset($this->arParams['~ENABLE_SECTION_DRAG_DROP'])
			|| $this->arParams['~ENABLE_SECTION_DRAG_DROP'];

		$this->arResult['ENABLE_FIELD_DRAG_DROP'] = !isset($this->arParams['~ENABLE_FIELD_DRAG_DROP'])
			|| $this->arParams['~ENABLE_FIELD_DRAG_DROP'];

		$this->arResult['SERVICE_URL'] = isset($this->arParams['~SERVICE_URL'])
			? $this->arParams['~SERVICE_URL'] : '';

		$this->arResult['EXTERNAL_CONTEXT_ID'] = isset($this->arParams['~EXTERNAL_CONTEXT_ID']) ? $this->arParams['~EXTERNAL_CONTEXT_ID'] : '';
		$this->arResult['CONTEXT_ID'] = isset($this->arParams['~CONTEXT_ID']) ? $this->arParams['~CONTEXT_ID'] : '';

		$this->arResult['CONTEXT'] = isset($this->arParams['~CONTEXT']) && is_array($this->arParams['~CONTEXT'])
			? $this->arParams['~CONTEXT'] : array();
		$this->arResult['CONTEXT']['EDITOR_CONFIG_ID'] = $this->configID;

		$this->arResult['COMPONENT_AJAX_DATA'] = isset($this->arParams['~COMPONENT_AJAX_DATA']) && is_array($this->arParams['~COMPONENT_AJAX_DATA'])
			? $this->arParams['~COMPONENT_AJAX_DATA'] : array();

		//region Languages
		$this->arResult['LANGUAGES'] = array();
		$dbResultLangs = \CLanguage::GetList($by = '', $order = '');
		while($lang = $dbResultLangs->Fetch())
		{
			$this->arResult['LANGUAGES'][] = array('LID' => $lang['LID'], 'NAME' => $lang['NAME']);
		}
		//endregion

		//??
		$this->optionID = $this->arResult['OPTION_ID'] = mb_strtolower($this->configID).'_opts';
		$this->arResult['ENTITY_CONFIG_OPTIONS'] = \CUserOptions::GetOption(
			'ui.entity.editor',
			$this->optionID,
			array()
		);

		$this->arResult['EDITOR_OPTIONS'] = array('show_always' => 'Y');
	}

	private function initializeConfigWithColumns(array $config): array
	{
		$columns = [];
		$elementsWithoutColumn = [];

		foreach ($config as $element)
		{
			$type = $element['type'] ?? '';

			if ($type === self::COLUMN_TYPE)
			{
				if (!isset($element['elements']) || !is_array($element['elements']))
				{
					$element['elements'] = [];
				}

				$columns[] = $element;
			}
			else
			{
				$elementsWithoutColumn[] = $element;
			}
		}

		if (empty($columns))
		{
			$columns = [
				[
					'name' => 'main',
					'title' => '',
					'type' => self::COLUMN_TYPE,
					'elements' => [],
				],
			];
		}

		if (!empty($elementsWithoutColumn))
		{
			$columns[0]['elements'] = array_merge($columns[0]['elements'], $elementsWithoutColumn);
		}

		return $columns;
	}
}