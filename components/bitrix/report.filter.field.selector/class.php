<?php

use Bitrix\Crm\Service\Container;
use Bitrix\Crm\UserField\Types\ElementType;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Selector\Entities;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

Loc::loadMessages(__FILE__);

class CReportComponent extends \CBitrixComponent
{
	protected $selectorItems;
	protected $moduleIncluded;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->selectorItems = array();
		$this->moduleIncluded = array();
	}

	public function executeComponent()
	{
		if (is_array($this->arParams['ufInfo']))
		{
			if ($this->prepareResult())
				$this->IncludeComponentTemplate();
		}
	}

	public function ensureModuleIncluded($moduleName)
	{
		$moduleName = trim(strval($moduleName));
		if (empty($moduleName))
			return false;

		if (isset($this->moduleIncluded[$moduleName]))
			return $this->moduleIncluded[$moduleName];

		$this->moduleIncluded[$moduleName] = Bitrix\Main\Loader::includeModule($moduleName);

		return $this->moduleIncluded[$moduleName];
	}

	protected function prepareResult()
	{
		foreach ($this->arParams['ufInfo'] as $arUfInfo)
		{
			foreach ($arUfInfo as $ufInfo)
			{
				if (is_array($ufInfo['USER_TYPE'])
					&& is_callable(array($ufInfo['USER_TYPE']['CLASS_NAME'], 'getlist')))
				{
					$enum = array();
					if ($ufInfo['USER_TYPE_ID'] !== 'enumeration')    // lazy load for enumerations
					{
						$rsEnum = call_user_func_array(
							array($ufInfo['USER_TYPE']['CLASS_NAME'], 'getlist'),
							array($ufInfo)
						);
						if (is_object($rsEnum))
						{
							while($arEnum = $rsEnum->GetNext())
							{
								$enum[$arEnum['ID']] = $arEnum['VALUE'];
							}
						}
						unset($rsEnum, $arEnum);
					}
					$ufInfo['USER_TYPE']['FIELDS'] = $enum;
				}

				switch ($ufInfo['USER_TYPE_ID'])
				{
					case 'enumeration':
						$selectorItem = $this->prepareEnumerationSelectorItem($ufInfo);
						if ($selectorItem)
						{
							$this->selectorItems[] = $selectorItem;
						}
						break;
					case 'crm':
						if($this->ensureModuleIncluded('crm'))
						{
							$selectorItem = $this->prepareCrmSelectorItem($ufInfo);
							if ($selectorItem)
								$this->selectorItems[] = $selectorItem;
						}
						break;
					case 'crm_status':
						if($this->ensureModuleIncluded('crm'))
						{
							$selectorItem = $this->prepareCrmStatusSelectorItem($ufInfo);
							if ($selectorItem)
								$this->selectorItems[] = $selectorItem;
						}
						break;
					case 'iblock_element':
						if($this->ensureModuleIncluded('iblock'))
						{
							$selectorItem = $this->prepareIblockElementSelectorItem($ufInfo);
							if ($selectorItem)
								$this->selectorItems[] = $selectorItem;
						}
						break;
					case 'iblock_section':
						if($this->ensureModuleIncluded('iblock'))
						{
							$selectorItem = $this->prepareIblockSectionSelectorItem($ufInfo);
							if ($selectorItem)
								$this->selectorItems[] = $selectorItem;
						}
						break;
					case 'money':
						if($this->ensureModuleIncluded('currency'))
						{
							$selectorItem = $this->prepareMoneySelectorItem($ufInfo);
							if ($selectorItem)
							{
								$this->selectorItems[] = $selectorItem;
							}
						}
						break;
				}
			}
		}

		$this->arResult['SELECTOR_ITEMS'] = $this->selectorItems;

		return !empty($this->arResult['SELECTOR_ITEMS']);
	}

	private function prepareBaseListSelectorItem($ufInfo)
	{
		$selectorItem = array();

		$selectorItem['USER_TYPE_ID'] = $ufInfo['USER_TYPE_ID'];
		$selectorItem['ENTITY_ID'] = $ufInfo['ENTITY_ID'];
		$selectorItem['FIELD_NAME'] = $ufInfo['FIELD_NAME'];

		$isMultiple = (isset($ufInfo['MULTIPLE']) && $ufInfo['MULTIPLE'] === 'Y');
		$selectorItem['LIST_HEIGHT'] =
			isset($ufInfo['SETTINGS']['LIST_HEIGHT']) ? intval($ufInfo['SETTINGS']['LIST_HEIGHT']) : 5;
		if (!$isMultiple && $selectorItem['LIST_HEIGHT'] < 3)
			$selectorItem['LIST_HEIGHT'] = 5;
		else if ($selectorItem['LIST_HEIGHT'] <= 0)
			$selectorItem['LIST_HEIGHT'] = 1;
		$selectorItem['ITEMS'] = array();
		$enum = is_array($ufInfo['USER_TYPE']['FIELDS']) ? $ufInfo['USER_TYPE']['FIELDS'] : array();
		$selectorItem['ITEMS'][] = array('id' => '', 'title' => GetMessage('REPORT_IGNORE_FILTER_VALUE'));
		foreach ($enum as $k => $v)
			$selectorItem['ITEMS'][] = array('id' => $k, 'title' => $v);

		$result = $selectorItem;

		return $result;
	}

	protected function prepareCrmSelectorItem($ufInfo)
	{
		if(!Bitrix\Main\Loader::includeModule('crm'))
		{
			return false;
		}

		$entityTypes = [];
		$entityTypeTitles = [];
		$selectorEntityTypeMap = [];
		$selectorEntityTypeAbbr = [];
		$permittedEntityTypeCount = 0;
		if (is_array($ufInfo['SETTINGS']))
		{
			$selectorEntityTypes = ElementType::getSelectorEntityTypes();
			$userPermissions = Container::getInstance()->getUserPermissions(CCrmPerms::GetCurrentUserID());
			foreach (ElementType::getPossibleEntityTypes() as $entityTypeName => $entityTypeTitle)
			{
				if (isset($ufInfo['SETTINGS'][$entityTypeName]) && $ufInfo['SETTINGS'][$entityTypeName] === 'Y')
				{
					$entityTypeId = CCrmOwnerType::ResolveID($entityTypeName);
					if ($entityTypeId !== CCrmOwnerType::Undefined && $userPermissions->canReadType($entityTypeId))
					{
						$permittedEntityTypeCount++;
						$entityTypeNameLower = mb_strtolower($entityTypeName);
						$entityTypes[] = $entityTypeNameLower;
						$entityTypeTitles[$entityTypeNameLower] = $entityTypeTitle;
						if (CCrmOwnerType::isPossibleDynamicTypeId($entityTypeId))
						{
							$selectorEntityTypeName =
								mb_strtolower(CCrmOwnerType::CommonDynamicName) . '_' . $entityTypeId
							;
						}
						else
						{
							$selectorEntityTypeName = $selectorEntityTypes[$entityTypeName];
						}
						$selectorEntityTypeMap[$selectorEntityTypeName] = $entityTypeNameLower;
						$selectorEntityTypeAbbr[$selectorEntityTypeName] =
							CCrmOwnerTypeAbbr::ResolveByTypeName($entityTypeName)
						;
					}
				}
			}
			unset($entityTypeName, $isEnabled, $entityTypeId, $selectorEntityTypeName);

			$selectorParams = ElementType::getDestSelectorParametersForFilter(
				$ufInfo['SETTINGS'],
				isset($ufInfo['SETTINGS']['MULTIPLE']) && $ufInfo['SETTINGS']['MULTIPLE'] === 'Y'
			);
			$selectorEntityTypeOptions = ElementType::getDestSelectorOptions($selectorParams);
			$selectedItems = [];
			$data = Entities::getData($selectorParams, $selectorEntityTypeOptions, $selectedItems);
		}

		$elements = [];
		$useIdPrefix = ($permittedEntityTypeCount > 1);

		if (is_array($data['ENTITIES']))
		{
			foreach ($data['ENTITIES'] as $entities)
			{
				if (is_array($entities['ITEMS']))
				{
					foreach ($entities['ITEMS'] as $item)
					{
						$id =
							$useIdPrefix
							? $selectorEntityTypeAbbr[$item['entityType']] . '_' . $item['entityId']
							: $item['entityId']
						;
						$elements[] = [
							'id' =>$id,
							'type' => $selectorEntityTypeMap[$item['entityType']],
							'title' => htmlspecialcharsback($item['name']) ?? '',
							'desc' => htmlspecialcharsback($item['desc']) ?? '',
							'url' => $item['url'] ?? '',
							'selected' => 'N',
							'image' => $item['avatar'] ?? '',
						];
					}
				}
			}
			unset($id);
		}

		return [
			'USER_TYPE_ID' => $ufInfo['USER_TYPE_ID'],
			'ENTITY_ID' => $ufInfo['ENTITY_ID'],
			'FIELD_NAME' => $ufInfo['FIELD_NAME'],
			'PREFIX' => $useIdPrefix ? 'Y' : 'N',
			'MULTIPLE' => 'Y',
			'ENTITY_TYPE' => $entityTypes,
			'ENTITY_TYPE_ABBR' => array_values($selectorEntityTypeAbbr),
			'ELEMENT' => $elements,
			'MESSAGES' => $entityTypeTitles,
		];
	}

	protected function prepareEnumerationSelectorItem($ufInfo)
	{
		return $this->prepareBaseListSelectorItem($ufInfo);
	}

	protected function prepareCrmStatusSelectorItem($ufInfo)
	{
		return $this->prepareBaseListSelectorItem($ufInfo);
	}

	protected function prepareIblockElementSelectorItem($ufInfo)
	{
		return $this->prepareBaseListSelectorItem($ufInfo);
	}

	protected function prepareIblockSectionSelectorItem($ufInfo)
	{
		return $this->prepareBaseListSelectorItem($ufInfo);
	}

	protected function prepareMoneySelectorItem($ufInfo)
	{
		$result = false;

		if (!Bitrix\Main\Loader::includeModule('currency'))
		{
			return $result;
		}

		$currencyListSrc = Bitrix\Currency\Helpers\Editor::getListCurrency();
		if (!is_array($currencyListSrc))
		{
			$currencyListSrc = [];
		}
		$currencyList = [['id' => '', 'title' => Loc::getMessage('REPORT_IGNORE_FILTER_VALUE')]];

		$defaultCurrency = '';
		$defaultCurrencyIndex = 0;
		$index = 0;
		foreach($currencyListSrc as $currency => $currencyInfo)
		{
			$value = ['id' => $currency, 'title' => $currencyInfo['NAME']];
			$currencyList[] = $value;

			if($defaultCurrency === '' || $currencyInfo['BASE'] === 'Y')
			{
				$defaultCurrency = $value["id"];
				$defaultCurrencyIndex = $index;
			}

			$index++;
		}
		unset($currencyListSrc, $index, $currency, $currencyInfo, $value);

		$result = [];

		$result['USER_TYPE_ID'] = $ufInfo['USER_TYPE_ID'];
		$result['ENTITY_ID'] = $ufInfo['ENTITY_ID'];
		$result['FIELD_NAME'] = $ufInfo['FIELD_NAME'];

		$result['CURRENCY_LIST'] = $currencyList;
		$result['DEFAULT_CURRENCY_VALUE'] = $defaultCurrency;
		$result['DEFAULT_CURRENCY_INDEX'] = $defaultCurrencyIndex;
		$result['DEFAULT_NUMBER_VALUE'] = '';

		return $result;
	}
}
