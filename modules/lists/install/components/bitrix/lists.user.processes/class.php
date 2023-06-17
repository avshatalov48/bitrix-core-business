<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ListsSelectElementComponent extends CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		$arParams['ERROR'] = [];
		if (!Loader::includeModule('lists'))
		{
			$arParams['ERROR'][] = Loc::getMessage('CC_BLL_MODULE_NOT_INSTALLED');
			return $arParams;
		}

		$arParams['IBLOCK_TYPE_ID'] = COption::GetOptionString("lists", "livefeed_iblock_type_id");

		if (!Loader::includeModule('bizproc') || !CLists::isBpFeatureEnabled($arParams["IBLOCK_TYPE_ID"]))
		{
			$arParams['ERROR'][] = Loc::getMessage('CC_BLL_BIZPROC_MODULE_NOT_INSTALLED');

			return $arParams;
		}

		global $USER;
		$arParams['LIST_PERM'] = CListPermissions::CheckAccess(
			$USER,
			COption::GetOptionString("lists", "livefeed_iblock_type_id"),
			false
		);
		if ($arParams['LIST_PERM'] < 0)
		{
			switch ($arParams['LIST_PERM'])
			{
				case CListPermissions::WRONG_IBLOCK_TYPE:
					$arParams['ERROR'][] = Loc::getMessage("CC_BLL_WRONG_IBLOCK_TYPE");
					break;
				case CListPermissions::WRONG_IBLOCK:
					$arParams['ERROR'][] = Loc::getMessage("CC_BLL_WRONG_IBLOCK");
					break;
				case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
					$arParams['ERROR'][] = Loc::getMessage("CC_BLL_LISTS_FOR_SONET_GROUP_DISABLED");
					break;
				default:
					$arParams['ERROR'][] = Loc::getMessage("CC_BLL_UNKNOWN_ERROR");
					break;
			}
		}
		elseif ($arParams['LIST_PERM'] <= CListPermissions::ACCESS_DENIED)
		{
			$arParams['ERROR'][] = Loc::getMessage("CC_BLL_ACCESS_DENIED");
		}

		return $arParams;
	}

	public function executeComponent()
	{
		if ($this->arParams['SET_TITLE'] == 'Y')
		{
			$this->getApplication()->setTitle(Loc::getMessage('CC_BLL_TITLE'));
		}

		if (!empty($this->arParams['ERROR']))
		{
			ShowError(array_shift($this->arParams['ERROR']));
			return;
		}

		$this->arResult['USER_ID'] = $this->arParams['USER_ID'];
		$this->arResult['GRID_ID'] = 'lists_processes';
		$this->arResult['FILTER_ID'] = 'lists_processes';

		$selectFields = ['ID', 'IBLOCK_TYPE_ID', 'IBLOCK_ID', 'NAME'];

		$gridOptions = new CGridOptions($this->arResult['GRID_ID']);
		$gridSort = $gridOptions->getSorting(['sort' => ['ID' => 'desc']]);

		$this->arResult['HEADERS'] = [
			[
				"id" => "ID",
				"name" => "ID",
				"default" => false,
				"sort" => "ID",
			],
			[
				'id' => 'DOCUMENT_NAME',
				'name' => Loc::getMessage('CC_BLL_DOCUMENT_NAME'),
				'default' => true,
				'sort' => 'DOCUMENT_NAME',
			],
			[
				'id' => 'COMMENTS',
				'name' => Loc::getMessage('CC_BLL_COMMENTS'),
				'default' => true,
				'sort' => '',
				'hideName' => true,
				'iconCls' => 'bp-comments-icon',
			],
			[
				'id' => 'WORKFLOW_PROGRESS',
				'name' => Loc::getMessage('CC_BLL_WORKFLOW_PROGRESS'),
				'default' => true,
				'sort' => '',
			],
			[
				'id' => 'WORKFLOW_STATE',
				'name' => Loc::getMessage('CC_BLL_WORKFLOW_STATE'),
				'default' => true,
				'sort' => '',
			],
		];

		$this->arResult['FILTER'] = [
			[
				"id" => "ID",
				"name" => 'ID',
				"type" => "number",
			],
			[
				"id" => "NAME",
				"name" => Loc::getMessage("BPATL_NAME"),
				"type" => "string",
				'default' => true,
			],
			[
				'id' => 'TIMESTAMP_X',
				'name' => Loc::getMessage('CC_BLL_MODIFIED'),
				'type' => 'date',
				'default' => true,
			],
			[
				'id' => 'DATE_CREATE',
				'name' => Loc::getMessage('CC_BLL_CREATED'),
				'type' => 'date',
				'default' => true,
			],
			[
				'id' => 'WORKFLOW_STATE',
				'name' => Loc::getMessage('CC_BLL_WORKFLOW_STATE'),
				'type' => 'list',
				'items' => [
					'A' => Loc::getMessage('CC_BLL_FILTER_STATUS_ALL'),
					'R' => Loc::getMessage('CC_BLL_FILTER_STATUS_RUNNING'),
					'C' => Loc::getMessage('CC_BLL_FILTER_STATUS_COMPLETE'),
				],
				'default' => true,
			],
		];

		$this->arResult['FILTER_PRESETS'] = [
			'01_running' => [
				'name' => Loc::getMessage('CC_BLL_FILTER_PRESET_RUNNING'),
				'default' => true,
				'fields' => [
					'WORKFLOW_STATE' => 'R',
				]
			],
			'02_completed' => [
				'name' => Loc::getMessage('CC_BLL_FILTER_PRESET_COMPLETED'),
				'fields' => [
					'WORKFLOW_STATE' => 'C',
				]
			],
		];

		$filterableFields = array_column($this->arResult['FILTER'], 'id');

		$filterOption = new Bitrix\Main\UI\Filter\Options($this->arResult['FILTER_ID']);
		$filterData = $filterOption->getFilter($this->arResult['FILTER']);

		$filter = $this->prepareElementFilter($filterData, $filterableFields);

		$this->arResult['SORT'] = $gridSort['sort'];

		$useComments = (bool)CModule::includeModule("forum");
		$workflows = [];
		$this->arResult['DATA'] = [];
		$this->arResult["COMMENTS_COUNT"] = [];

		$filter['CREATED_BY'] = $this->arParams['USER_ID'];
		$iblockTypeId = COption::GetOptionString("lists", "livefeed_iblock_type_id");
		$filter['=IBLOCK_TYPE'] = $iblockTypeId;
		$filter['CHECK_PERMISSIONS'] = ($this->arParams['LIST_PERM'] >= CListPermissions::CAN_READ ? "N" : "Y");
		$elementObject = CIBlockElement::getList(
			$gridSort['sort'],
			$filter,
			false,
			$gridOptions->getNavParams(),
			$selectFields
		);
		$path = rtrim(SITE_DIR, '/');
		while ($element = $elementObject->fetch())
		{
			$documentState = $this->getActualElementState(
				BizprocDocument::getDocumentComplexId($iblockTypeId, $element['ID'])
			);

			$this->arResult['DATA'][] = [
				'ID' => $element['ID'],
				'DOCUMENT_NAME' => $element['NAME'],
				'DOCUMENT_URL' => $path
					. COption::GetOptionString('lists', 'livefeed_url')
					. '?livefeed=y&list_id='
					. $element["IBLOCK_ID"]
					. '&element_id='
					. $element['ID']
				,
				'WORKFLOW_ID' => $documentState ? $documentState['ID'] : '',
				"WORKFLOW_STATE" => $documentState ? htmlspecialcharsbx($documentState["STATE_TITLE"]) : '',
			];
		}

		foreach ($this->arResult['DATA'] as $data)
		{
			if ($useComments && $data['WORKFLOW_ID'])
			{
				$workflows[] = 'WF_' . $data['WORKFLOW_ID'];
			}

			$actions = [];
			if ($data["DOCUMENT_URL"])
			{
				$actions[] = [
					'ICONCLASS' => '',
					'DEFAULT' => true,
					'TEXT' => Loc::getMessage('CC_BLL_C_DOCUMENT'),
					'ONCLICK' => 'window.open("' . $data["DOCUMENT_URL"] . '");',
				];
			}
			$this->arResult['RECORDS'][] = ['data' => $data, 'actions' => $actions];
		}

		$workflows = array_unique($workflows);
		if ($useComments && $workflows)
		{
			$iterator = CForumTopic::getList([], ["@XML_ID" => $workflows]);
			while ($row = $iterator->fetch())
			{
				$this->arResult["COMMENTS_COUNT"][$row['XML_ID']] = $row['POSTS'];
			}
		}

		$this->arResult['COUNTERS'] = ['all' => 0];

		$this->arResult["NAV_OBJECT"] = $elementObject;
		$componentObject = null;
		$this->arResult["GRID_ENABLE_NEXT_PAGE"] = ($elementObject->PAGEN < $elementObject->NavPageCount);
		$this->arResult["NAV_STRING"] = $elementObject->getPageNavStringEx(
			$componentObject, "", "grid", false, null, $gridOptions->getNavParams());
		$this->arResult["GRID_PAGE_SIZES"] = [
			["NAME" => "5", "VALUE" => "5"],
			["NAME" => "10", "VALUE" => "10"],
			["NAME" => "20", "VALUE" => "20"],
			["NAME" => "50", "VALUE" => "50"],
			["NAME" => "100", "VALUE" => "100"],
		];

		$this->includeComponentTemplate();
	}

	protected function getApplication()
	{
		global $APPLICATION;
		return $APPLICATION;
	}

	public function prepareElementFilter(array $filterData, array $filterableFields): array
	{
		$filter = [];
		foreach ($filterData as $key => $value)
		{
			if (empty($value))
			{
				continue;
			}

			$op = "";
			$filterKey = $key;

			if (mb_substr($key, -5) == "_from")
			{
				$filterKey = mb_substr($key, 0, -5);
				$op = (!empty($filterData[$filterKey . "_numsel"]) && $filterData[$filterKey . "_numsel"] === "more")
					? ">"
					: ">="
				;
			}
			elseif (mb_substr($key, -3) == "_to")
			{
				$filterKey = mb_substr($key, 0, -3);
				$op = (!empty($filterData[$filterKey . "_numsel"]) && $filterData[$filterKey . "_numsel"] == "less")
					? "<"
					: "<="
				;

				if (in_array($filterKey, ["TIMESTAMP_X", 'DATE_CREATE']))
				{
					global $DB;
					$dateFormat = $DB->dateFormatToPHP(Csite::getDateFormat());
					$dateParse = date_parse_from_format($dateFormat, $value);
					if (
						!mb_strlen($dateParse["hour"])
						&& !mb_strlen($dateParse["minute"])
						&& !mb_strlen($dateParse["second"])
					)
					{
						$timeFormat = $DB->dateFormatToPHP(CSite::getTimeFormat());
						$value .= " " . date($timeFormat, mktime(23, 59, 59, 0, 0, 0));
					}
				}
			}
			elseif ($key == "NAME")
			{
				$op = "?";
			}

			if ($key == "FIND")
			{
				$op = "?";
				$filter[$op . "SEARCHABLE_CONTENT"] = $value;
			}
			elseif ($key === 'WORKFLOW_STATE')
			{
				if ($value === 'R' || $value === 'C')
				{
					$not = $value === 'C' ? '!' : '';
					$filter[$not . '=ID'] = $this->getActiveWorkflowElementIds();
				}
			}
			elseif (in_array($filterKey, $filterableFields))
			{
				$filter[$op . $filterKey] = $value;
			}
		}
		return $filter;
	}

	private function getActiveWorkflowElementIds()
	{
		$userId = $this->getUser()->getId();
		$moduleId = 'lists';
		$entity = BizprocDocument::class;

		$rows = \Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable::getList([
			'filter' => [
				'=MODULE_ID' => $moduleId,
				'=ENTITY' => $entity,
				'=STARTED_BY' => $userId,
			],
			'select' => ['DOCUMENT_ID'],
		])->fetchAll();

		$ids = array_unique(array_column($rows, 'DOCUMENT_ID'));

		return $ids ?: [0];
	}

	private function getActualElementState(array $documentId): ?array
	{
		$state = \CBPDocument::getActiveStates($documentId, 1);
		if ($state)
		{
			return array_shift($state);
		}

		$ids = \CBPStateService::getIdsByDocument($documentId, 1);
		if ($ids)
		{
			return \CBPStateService::getWorkflowState(array_shift($ids));
		}

		return null;
	}

	protected function getUser()
	{
		global $USER;
		return $USER;
	}
}