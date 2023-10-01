<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Lists\Field;

class ListsElementAttachedCrmComponent extends CBitrixComponent
{
	protected $singleMode = false;

	protected $entityIdWithPrefix;
	protected $gridAction;

	protected $listPropertyIdWithoutPrefix = array();
	protected $listElementData = array();
	protected $listElementEditPermission = array();

	protected $iblockId = 0;
	protected $listIblockId = array();
	protected $listIblockName = array();
	protected $listIblockType = [];
	protected $listIblockElementId = array();
	protected $listIblockElementTemplateUrl = array();
	protected $listIblockSocnetGroupId = array();
	protected $listFields = array();
	protected $listFieldsValue = array();
	protected $listObject = array();
	protected $listIblockPermission = array();
	protected $listIblockBpTemplates = [];

	protected $properties = array();
	protected $selectedFields = array();

	protected $prefixGridId = 'lists_attached_crm_';
	protected $listGridId = array();
	protected $listGridOptions = array();
	protected $navigationGrid = array();
	protected $headerGrids = array();

	protected $rowGrids = array();
	protected $groupActionsGrids = array();

	protected $entityData = array();

	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));
		Loc::loadMessages(__FILE__);
	}

	protected function checkModules()
	{
		if(!Loader::includeModule('lists'))
		{
			throw new SystemException(Loc::getMessage('LEAC_MODULE_NOT_INSTALLED', array('MODULE_ID' => 'lists')));
		}
		if(!Loader::includeModule('crm'))
		{
			throw new SystemException(Loc::getMessage('LEAC_MODULE_NOT_INSTALLED', array('MODULE_ID' => 'crm')));
		}
	}

	public function onPrepareComponentParams($params)
	{
		if(!empty($_REQUEST['gridId']))
		{
			$params['GRID_ID'] = $_REQUEST['gridId'];
			if(!empty($_REQUEST['action_button_'.$_REQUEST['gridId']]))
				$params['ACTION'] = $_REQUEST['action_button_'.$_REQUEST['gridId']];
			if(!empty($_REQUEST['ID']))
				$params['ID_FOR_DELETE'] = (is_array($_REQUEST['ID'])) ? $_REQUEST['ID'] : array($_REQUEST['ID']);
		}
		if(!empty($_REQUEST['entityId']))
			$params['ENTITY_ID'] = $_REQUEST['entityId'];
		if(!empty($_REQUEST['entityType']))
			$params['ENTITY_TYPE'] = $_REQUEST['entityType'];

		return $params;
	}

	public function executeComponent()
	{
		try
		{
			$this->checkModules();
			$this->fillParams();

			$this->checkGridAction();

			$this->getElementIdByEntityId();

			$this->createListElementIdByPermission();

			$this->setGridId();
			$this->getGridOptions();

			$this->setSelectedFields();
			$this->getListElement();

			$this->setGridGroupActions();

			$this->getEntityData();

			$this->formatResult();
			$this->includeComponentTemplate();
		}
		catch (SystemException $exception)
		{
			ShowError($exception->getMessage());
		}
	}

	protected function fillParams()
	{
		if(empty($this->arParams['ENTITY_ID']) || empty($this->arParams['ENTITY_TYPE']))
		{
			throw new SystemException(Loc::getMessage('LEAC_ERROR_REQUIRED_PARAMETER'));
		}
		$this->entityIdWithPrefix = \CCrmOwnerTypeAbbr::resolveByTypeID(
				$this->arParams['ENTITY_TYPE']).'_'.$this->arParams['ENTITY_ID'];

		$this->arParams['ENTITY_TYPE_NAME'] = \CCrmOwnerTypeAbbr::resolveName(\CCrmOwnerTypeAbbr::resolveByTypeID(
			$this->arParams['ENTITY_TYPE']));

		if(!empty($this->arParams['IBLOCK_ID']))
		{
			$this->iblockId = intval($this->arParams['IBLOCK_ID']);
			$this->singleMode = true;
		}

		if(!empty($this->arParams['GRID_ID']))
		{
			$this->iblockId = intval(str_replace($this->prefixGridId, '', $this->arParams['GRID_ID']));

			if(!empty($this->arParams['ACTION']))
				$this->gridAction = $this->arParams['ACTION'];
		}

		$this->arParams['RAND_STRING'] = $this->randString();
		$this->arParams['JS_OBJECT'] = sprintf(
			'ListsElementAttachedCrm_%d%s',
			(int)$this->iblockId,
			$this->arParams['RAND_STRING']
		);

		if(!empty($this->arParams['LIST_ELEMENT_DATA']))
			$this->listElementData = $this->arParams['LIST_ELEMENT_DATA'];
	}

	protected function checkGridAction()
	{
		if(!empty($this->gridAction) && method_exists($this, 'performGridAction'.$this->gridAction))
		{
			$actionMethod = 'performGridAction'.$this->gridAction;
			$this->$actionMethod();
		}
	}

	protected function performGridActionDelete()
	{
		if(empty($this->arParams['ID_FOR_DELETE'])
			|| !$this->checkDeletePermission($this->iblockId,$this->arParams['ID_FOR_DELETE']))
			return;

		$newPropertyValues = array();
		$propertyValues = $this->getPropertyValues(
			$this->iblockId, array('ID' => $this->arParams['ID_FOR_DELETE'], 'SHOW_NEW' => 'Y'), array());
		foreach($propertyValues as $propertyData)
		{
			if(!isset($propertyData['IBLOCK_ELEMENT_ID']))
				continue;

			$elementId = $propertyData['IBLOCK_ELEMENT_ID'];
			foreach($propertyData as $propertyId => $propertyValue)
			{
				if($propertyId == 'IBLOCK_ELEMENT_ID')
					continue;
				if(is_array($propertyValue))
				{
					$keyForDelete = array_search($this->entityIdWithPrefix, $propertyValue);
					if($keyForDelete !== false)
					{
						unset($propertyValue[$keyForDelete]);
						$newPropertyValues[$elementId][$propertyId] = $propertyValue;
					}
					$keyForDelete = array_search($this->arParams['ENTITY_ID'], $propertyValue);
					if($keyForDelete !== false)
					{
						unset($propertyValue[$keyForDelete]);
						$newPropertyValues[$elementId][$propertyId] = $propertyValue;
					}
				}
				else
				{
					if($propertyValue == $this->entityIdWithPrefix)
					{
						$newPropertyValues[$elementId][$propertyId] = false;
					}
					elseif($propertyValue == $this->arParams['ENTITY_ID'])
					{
						$newPropertyValues[$elementId][$propertyId] = false;
					}
				}
			}
		}

		foreach($newPropertyValues as $elementId => $listPropertyData)
		{
			foreach($listPropertyData as $propertyId => $propertyValues)
			{
				$propertyObject = Bitrix\Iblock\PropertyTable::getList(array(
					'select' => array('USER_TYPE'),
					'filter' => array('IBLOCK_ID'=> $this->iblockId, 'ID' => $propertyId)
				));
				if($property = $propertyObject->fetch())
				{
					if($property['USER_TYPE'] == Bitrix\Crm\Integration\IBlockElementProperty::USER_TYPE)
						CIBlockElement::setPropertyValues($elementId, $this->iblockId, $propertyValues, $propertyId);
				}
			}
		}
	}

	protected function checkDeletePermission($iblockId, array $listElementId)
	{
		$iblockObject = Bitrix\Iblock\IblockTable::getList(array(
			'select' => array('IBLOCK_TYPE_ID', 'SOCNET_GROUP_ID'),
			'filter' => array('=ACTIVE' => 'Y', '=ID' => $iblockId)
		));
		if(!$iblock = $iblockObject->fetch())
			return false;

		global $USER;
		foreach($listElementId as $elementId)
		{
			$listsPerm = CListPermissions::checkAccess(
				$USER,
				$iblock['IBLOCK_TYPE_ID'],
				$iblockId,
				$iblock['SOCNET_GROUP_ID']
			);
			if($listsPerm < 0)
				return false;

			$isSocnetGroupClosed = false;
			if(intval($iblock['SOCNET_GROUP_ID']) > 0 && Loader::includeModule('socialnetwork'))
			{
				$sonetGroup = CSocNetGroup::getByID($iblock['SOCNET_GROUP_ID']);
				if (is_array($sonetGroup) && $sonetGroup['CLOSED'] == 'Y'
					&& !CSocNetUser::isCurrentUserModuleAdmin()
					&& ($sonetGroup['OWNER_ID'] != $USER->getID()
						|| COption::getOptionString('socialnetwork', 'work_with_closed_groups', 'N') != 'Y'))
				{
					$isSocnetGroupClosed = true;
				}
			}
			if(!$isSocnetGroupClosed && ($listsPerm >= CListPermissions::CAN_WRITE ||
				CIBlockElementRights::userHasRightTo($iblockId, $elementId, 'element_delete')))
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		return true;
	}

	protected function getElementIdByEntityId()
	{
		$filter = array(
			'=ACTIVE' => 'Y',
			'=USER_TYPE' => Bitrix\Crm\Integration\IBlockElementProperty::USER_TYPE,
		);
		if($this->iblockId)
			$filter['=IBLOCK_ID'] = $this->iblockId;

		$listProperty = array();
		$propertyObject = Bitrix\Iblock\PropertyTable::getList(array(
			'select' => array('ID', 'IBLOCK_ID', 'USER_TYPE_SETTINGS'),
			'filter' => $filter
		));
		while($property = $propertyObject->fetch())
		{
			$property['USER_TYPE_SETTINGS'] = unserialize($property['USER_TYPE_SETTINGS'], ['allowed_classes' => false]);
			if(is_array($property['USER_TYPE_SETTINGS']))
			{
				if(array_key_exists('VISIBLE', $property['USER_TYPE_SETTINGS']))
					unset($property['USER_TYPE_SETTINGS']['VISIBLE']);
				$tmpArray = array_filter($property['USER_TYPE_SETTINGS'], function($mark){
					return $mark == "Y";
				});
				if(count($tmpArray) == 1)
					$this->listPropertyIdWithoutPrefix[] = $property['ID'];
				if ($property['USER_TYPE_SETTINGS'][$this->arParams['ENTITY_TYPE_NAME']] == 'Y')
				{
					if (!isset($listProperty[$property['IBLOCK_ID']]) || !is_array($listProperty[$property['IBLOCK_ID']]))
					{
						$listProperty[$property['IBLOCK_ID']] = [];
					}
					$listProperty[$property['IBLOCK_ID']][] = $property['ID'];
				}
			}
		}

		foreach($listProperty as $iblockId => $listPropertyId)
		{
			$iblockObject = Bitrix\Iblock\IblockTable::getList(array(
				'select' => array('NAME', 'IBLOCK_TYPE_ID', 'SOCNET_GROUP_ID'),
				'filter' => array('=ACTIVE' => 'Y', '=ID' => $iblockId)
			));
			if(!$iblock = $iblockObject->fetch())
				continue;

			$this->listIblockId[] = $iblockId;
			$this->listIblockSocnetGroupId[$iblockId] = $iblock['SOCNET_GROUP_ID'];
			$this->listIblockName[$iblockId] = $iblock['NAME'];
			$this->listIblockType[$iblockId] = $iblock['IBLOCK_TYPE_ID'];
			$this->listElementData[$iblock['IBLOCK_TYPE_ID']][$iblockId] = array();

			$elementFilter = [
				'IBLOCK_ID' => $iblockId,
				'=ACTIVE' => 'Y',
			];

			$propertyFilter = [];
			foreach ($listPropertyId as $item)
			{
				$propertyFilter['=PROPERTY_' . $item] = (
					in_array($item, $this->listPropertyIdWithoutPrefix)
						? $this->arParams['ENTITY_ID']
						: $this->entityIdWithPrefix
					);
			}

			if (count($propertyFilter) > 1)
			{
				$propertyFilter['LOGIC'] = 'OR';
				$elementFilter[] = $propertyFilter;
			}
			else
			{
				$elementFilter = array_merge($elementFilter, $propertyFilter);
			}

			$queryObject = CIBlockElement::getList(
				[],
				$elementFilter,
				false,
				$this->listGridOptions[$iblockId]['navParams'] ?? null,
				['ID'],
			);

			while ($row = $queryObject->fetch())
			{
				$this->listElementData[$iblock['IBLOCK_TYPE_ID']][$iblockId][] = (int)$row['ID'];
			}
		}
	}

	protected function createListElementIdByPermission()
	{
		if(empty($this->listElementData))
			return;

		global $USER;

		foreach($this->listElementData as $iblockTypeId => $iblock)
		{
			foreach($iblock as $iblockId => $listElementId)
			{
				$listsPerm = CListPermissions::checkAccess(
					$USER,
					$iblockTypeId,
					$iblockId,
					$this->listIblockSocnetGroupId[$iblockId]
				);
				if($listsPerm < 0)
					continue;

				$isSocnetGroupClosed = false;
				if(intval($this->listIblockSocnetGroupId[$iblockId]) > 0 && Loader::includeModule('socialnetwork'))
				{
					$sonetGroup = CSocNetGroup::getByID($this->listIblockSocnetGroupId[$iblockId]);
					if (is_array($sonetGroup) && $sonetGroup['CLOSED'] == 'Y'
						&& !CSocNetUser::isCurrentUserModuleAdmin()
						&& ($sonetGroup['OWNER_ID'] != $USER->getID()
							|| COption::getOptionString('socialnetwork', 'work_with_closed_groups', 'N') != 'Y'))
					{
						$isSocnetGroupClosed = true;
					}
				}

				$this->listIblockPermission[$iblockId]['EDIT'] = !$isSocnetGroupClosed &&
					($listsPerm >= CListPermissions::IS_ADMIN ||
						CIBlockRights::userHasRightTo($iblockId, $iblockId, 'iblock_edit'));

				$this->listIblockPermission[$iblockId]['ADD_ELEMENT'] = !$isSocnetGroupClosed &&
					($listsPerm > CListPermissions::CAN_READ ||
						CIBlockSectionRights::userHasRightTo($iblockId, 0, 'section_element_bind'));

				$this->listIblockPermission[$iblockId]['READ'] = !$isSocnetGroupClosed &&
					($listsPerm > CListPermissions::CAN_READ ||
						CIBlockSectionRights::userHasRightTo($iblockId, 0, 'element_read'));

				foreach($listElementId as $elementKey => $elementId)
				{
					if($listsPerm < CListPermissions::CAN_READ
						&& !CIBlockElementRights::userHasRightTo($iblockId, $elementId, 'element_read'))
						continue;

					$this->listIblockElementId[$iblockId][] = $elementId;

					if(!$isSocnetGroupClosed && ($listsPerm >= CListPermissions::CAN_WRITE ||
						CIBlockElementRights::userHasRightTo($iblockId, $elementId, 'element_edit')))
					{
						$this->listElementEditPermission[$elementId] = CListPermissions::CAN_WRITE;
					}
				}
			}
		}
	}

	protected function setGridId()
	{
		if(empty($this->listIblockId))
			return;

		foreach($this->listIblockId as $iblockId)
			$this->listGridId[$iblockId] = $this->prefixGridId.$iblockId;
	}

	protected function getGridOptions()
	{
		if(empty($this->listGridId))
			return;

		foreach($this->listGridId as $iblockId => $gridId)
		{
			$gridOptions = new Bitrix\Main\Grid\Options($gridId);
			$this->listGridOptions[$iblockId]['gridOptionsObject'] = $gridOptions;
			$this->listGridOptions[$iblockId]['visibleColumns'] = $gridOptions->getVisibleColumns();
			$this->listGridOptions[$iblockId]['sorting'] = $gridOptions->getSorting(array(
				'sort' => array('NAME' => 'ASC')));
			$this->listGridOptions[$iblockId]['navParams'] = $gridOptions->getNavParams();
		}
	}

	protected function setSelectedFields()
	{
		if(empty($this->listIblockId))
			return;

		foreach($this->listIblockId as $iblockId)
		{
			$this->listObject[$iblockId] = new CList($iblockId);
			$this->listFields[$iblockId] = $this->listObject[$iblockId]->getFields();

			$this->listIblockElementTemplateUrl[$iblockId] = $this->listObject[$iblockId]->getUrlByIblockId($iblockId);

			$this->headerGrids[$iblockId][] = array(
				'id' => 'ID',
				'name' => 'ID',
				'default' => false,
				'sort' => 'ID'
			);

			$visibleColumns = $this->listGridOptions[$iblockId]['visibleColumns'];
			$this->selectedFields[$iblockId] = array('ID', 'IBLOCK_ID');
			foreach($this->listFields[$iblockId] as $fieldId => $field)
			{
				if(empty($visibleColumns) || in_array($fieldId, $visibleColumns))
				{
					if(mb_substr($fieldId, 0, 9) == 'PROPERTY_')
						$this->properties[$iblockId][] = $fieldId;
					else
						$this->selectedFields[$iblockId][] = $fieldId;

					if($fieldId == 'CREATED_BY')
						$this->selectedFields[$iblockId][] = 'CREATED_USER_NAME';

					if($fieldId == 'MODIFIED_BY')
						$this->selectedFields[$iblockId][] = 'USER_NAME';
				}

				$this->setHeaderGrid($iblockId, $fieldId, $field);
			}
		}
	}

	protected function getListElement()
	{
		if(empty($this->listIblockElementId))
			return;

		foreach($this->listIblockElementId as $iblockId => $listElementId)
		{
			$queryObject = CIBlockElement::getList(
				$this->listGridOptions[$iblockId]['sorting']['sort'],
				array('=ACTIVE' => 'Y', '=ID' => $listElementId),
				false,
				$this->listGridOptions[$iblockId]['navParams'],
				$this->selectedFields[$iblockId]
			);
			while($elementObject = $queryObject->getNextElement())
			{
				$element = $elementObject->getFields();
				if(!is_array($element))
					continue;

				if (!isset($this->listFieldsValue[$element['ID']]) || !is_array($this->listFieldsValue[$element['ID']]))
				{
					$this->listFieldsValue[$element['ID']] = [];
				}

				foreach($element as $fieldId => $fieldValue)
				{
					$this->listFieldsValue[$element['ID']][$fieldId] = $fieldValue;
				}

				if(!empty($this->properties[$iblockId]))
				{
					$propertyValues = $this->getPropertyValues($iblockId,
						array('ID' => $element['ID'], 'SHOW_NEW' => 'Y'));
					foreach(current($propertyValues) as $propertyId => $propertyValue)
					{
						if($propertyId == 'IBLOCK_ELEMENT_ID')
							continue;
						$this->listFieldsValue[$element['ID']]['PROPERTY_'.$propertyId] = $propertyValue;
					}
				}

				$this->setRowGrid($iblockId, $element['ID']);
			}
			$this->setNavigationGrid($iblockId, $queryObject);
		}
	}

	protected function setRowGrid($iblockId, $elementId)
	{
		if(empty($this->listFields))
			return;

		$columns = array(
			'ID' => intval($elementId)
		);

		$downloadFileUrl = '/bitrix/components/bitrix/lists.element.attached.crm/lazyload.ajax.php?&site='.SITE_ID.'&'.
			bitrix_sessid_get().'&list_id=#list_id#&element_id=#element_id#&field_id=#field_id#&file_id=#file_id#';

		foreach($this->listFields[$iblockId] as $fieldId => $field)
		{
			$valueKey = (mb_substr($fieldId, 0, 9) == "PROPERTY_") ? $fieldId : "~".$fieldId;
			$field["ELEMENT_ID"] = $elementId;
			$field["FIELD_ID"] = $fieldId;
			$field['VALUE'] = $this->listFieldsValue[$elementId][$valueKey];
			$field["DOWNLOAD_FILE_URL"] = $downloadFileUrl;
			$columns[$fieldId] = Field::renderField($field);
		}

		$this->rowGrids[$iblockId][] = array(
			'id' => $elementId,
			'columns' => $columns,
			'actions' => $this->createRowActions($iblockId, $elementId),
		);
	}

	protected function setGridGroupActions()
	{
		$snippet = new Bitrix\Main\Grid\Panel\Snippet();
		foreach($this->listIblockId as $iblockId)
		{
			if(!$this->listIblockPermission[$iblockId]['EDIT'])
				continue;

			$this->groupActionsGrids[$iblockId] = array(
				'GROUPS' => array(
					array(
						'ITEMS' => array(
							$snippet->getRemoveButton(),
						)
					)
				)
			);
		}
	}

	protected function getEntityData()
	{
		$entityId = $this->arParams['ENTITY_ID'];
		$this->entityData = \CCrmEntitySelectorHelper::prepareEntityInfo($this->arParams['ENTITY_TYPE_NAME'], $entityId, array(
			'ENTITY_EDITOR_FORMAT' => true,
			'REQUIRE_REQUISITE_DATA' => false,
			'REQUIRE_MULTIFIELDS' => false,
			'NAME_TEMPLATE' => \Bitrix\Crm\Format\PersonNameFormatter::getFormat()
		));
		$this->entityData['defaultValue'] = $this->entityIdWithPrefix;
	}

	protected function formatResult()
	{
		$this->arResult['SINGLE_MODE'] = $this->singleMode;
		$this->arResult['IBLOCK_ID'] = $this->iblockId;
		$this->arResult['LIST_ELEMENT_TEMPLATE_URL'] = $this->listIblockElementTemplateUrl;
		$this->arResult['IBLOCK_PERMISSION'] = $this->listIblockPermission;

		$this->arResult['BUTTON_NAME_ELEMENT_ADD'] = CIBlock::getArrayByID($this->iblockId, 'ELEMENT_ADD');

		$this->arResult['RAND_STRING'] = $this->arParams['RAND_STRING'];
		$this->arResult['JS_OBJECT'] = $this->arParams['JS_OBJECT'];

		$this->arResult['ENTITY_ID'] = $this->arParams['ENTITY_ID'];
		$this->arResult['ENTITY_TYPE'] = $this->arParams['ENTITY_TYPE'];

		$this->arResult['LIST_IBLOCK_NAME'] = $this->listIblockName;

		$this->arResult['GRID_PREFIX_ID'] = $this->prefixGridId;
		$this->arResult['GRID_ID'] = $this->listGridId;
		$this->arResult['GRID_NAVIGATION'] = $this->navigationGrid;
		$this->arResult['GRID_HEADERS'] = $this->headerGrids;
		$this->arResult['GRID_ROWS'] = $this->rowGrids;
		$this->arResult['GRID_GROUP_ACTIONS'] = $this->groupActionsGrids;

		if(!empty($this->listFields[$this->iblockId]))
		{
			$this->arResult['FIELDS_FOR_SET_VALUE'] = array();
			foreach($this->listFields[$this->iblockId] as $fieldId => $fieldData)
			{
				if($fieldData['TYPE'] == 'S:ECrm' &&
					$fieldData['USER_TYPE_SETTINGS'][$this->arParams['ENTITY_TYPE_NAME']] == "Y")
				{
					$this->arResult['FIELDS_FOR_SET_VALUE'][$fieldId] = $this->entityData;
				}
			}
		}
	}

	protected function setHeaderGrid($iblockId, $fieldId, $field)
	{
		$this->headerGrids[$iblockId][] = array(
			'id' => $fieldId,
			'name' => $field['NAME'],
			'default' => true,
			'sort' => ($field['MULTIPLE'] == 'Y') ? '' : $fieldId,
		);
	}

	protected function setNavigationGrid($iblockId, CIBlockResult $navObject)
	{
		$this->navigationGrid[$iblockId]['TOTAL_ROWS_COUNT'] = $navObject->NavRecordCount;
		$this->navigationGrid[$iblockId]['ENABLE_NEXT_PAGE'] = ($navObject->PAGEN < $navObject->NavPageCount);
		$this->navigationGrid[$iblockId]['PAGE_SIZES'] = array(
			array('NAME' => '5', 'VALUE' => '5'),
			array('NAME' => '10', 'VALUE' => '10'),
			array('NAME' => '20', 'VALUE' => '20'),
			array('NAME' => '50', 'VALUE' => '50'),
			array('NAME' => '100', 'VALUE' => '100'),
			array('NAME' => '200', 'VALUE' => '200'),
			array('NAME' => '500', 'VALUE' => '500')
		);
		$dummy = null;
		$this->navigationGrid[$iblockId]['NAV_STRING'] = $navObject->getPageNavStringEx(
			$dummy, '', 'grid', true, null, $this->listGridOptions[$iblockId]['navParams']);
	}

	protected function createRowActions($iblockId, $elementId)
	{
		$actions = [];

		$canEdit = (array_key_exists($elementId, $this->listElementEditPermission)
			&& $this->listElementEditPermission[$elementId] == CListPermissions::CAN_WRITE);

		$actions[] = [
			'text' => $canEdit ? Loc::getMessage('LEAC_GRID_ACTION_ELEMENT_EDIT') :
				Loc::getMessage('LEAC_GRID_ACTION_ELEMENT_SHOW'),
			'title' => Loc::getMessage('LEAC_GRID_ACTION_ELEMENT_SHOW_TITLE'),
			'onclick' => 'BX.Lists["'.$this->arParams['JS_OBJECT'].'"].editElement("'.$elementId.'");'
		];
		if ($canEdit)
		{
			$bpTemplates = $this->getIblockBpTemplates($iblockId);
			if ($bpTemplates)
			{
				$documentType = BizProcDocument::generateDocumentComplexType($this->listIblockType[$iblockId], $iblockId);
				$bpActions = [];

				foreach ($bpTemplates as $template)
				{
					$params = \Bitrix\Main\Web\Json::encode(array(
						'moduleId' => $documentType[0],
						'entity' => $documentType[1],
						'documentType' => $documentType[2],
						'documentId' => $elementId,
						'templateId' => $template['id'],
						'templateName' => $template['name'],
						'hasParameters' => $template['hasParameters']
					));
					$bpActions[] = [
						'TEXT' => $template['name'],
						'ONCLICK' => 'BX.Bizproc.Starter.singleStart('
							. $params
							. ', function(){BX.Main.gridManager.reload(\''
							. CUtil::JSEscape($this->listGridId[$iblockId])
							. '\');});',
					];
				}

				$actions[] = array(
					"TEXT" => Loc::getMessage("LEAC_GRID_ACTION_ELEMENT_START_BP"),
					"MENU" => $bpActions,
				);
			}


			$actions[] = [
				'text' => Loc::getMessage('LEAC_GRID_ACTION_ELEMENT_UNBIND_DEL'),
				'title' => Loc::getMessage('LEAC_GRID_ACTION_ELEMENT_UNBIND_TITLE_DEL'),
				'onclick' => 'BX.Lists["'.$this->arParams['JS_OBJECT'].'"].unBind("'.
					$this->listGridId[$iblockId].'", "'.$elementId.'");'
			];
		}

		return $actions;
	}

	protected function getPropertyValues($iblockId, $elementFilter = array(), $propertyFilter = array())
	{
		$values = array();

		$propertyValuesObject = \CIblockElement::getPropertyValues($iblockId, $elementFilter, false, $propertyFilter);
		while($propertyValues = $propertyValuesObject->fetch())
			$values[] = $propertyValues;

		return $values;
	}

	protected function getIblockBpTemplates($iblockId): array
	{
		if (!isset($this->listIblockBpTemplates[$iblockId]))
		{
			$this->listIblockBpTemplates[$iblockId] = [];

			if (
				CLists::isBpFeatureEnabled($this->listIblockType[$iblockId])
				&& CModule::IncludeModule('bizproc')
			)
			{
				$user = Main\Engine\CurrentUser::get();
				$this->listIblockBpTemplates[$iblockId] = CBPDocument::getTemplatesForStart(
					$user->getId(),
					BizProcDocument::generateDocumentComplexType($this->listIblockType[$iblockId], $iblockId),
				);
			}
		}

		return $this->listIblockBpTemplates[$iblockId];
	}
}