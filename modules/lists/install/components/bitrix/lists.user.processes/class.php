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
	private array $canEditElements = [];
	private array $listIblockBpTemplates = [];

	public function onPrepareComponentParams($arParams)
	{
		$arParams['ERROR'] = [];
		if (!Loader::includeModule('lists'))
		{
			$arParams['ERROR'][] = Loc::getMessage('CC_BLL_MODULE_NOT_INSTALLED');

			return $arParams;
		}

		$arParams['IBLOCK_TYPE_ID'] = \Bitrix\Main\Config\Option::get('lists', 'livefeed_iblock_type_id');

		if (!Loader::includeModule('bizproc') || !CLists::isBpFeatureEnabled($arParams['IBLOCK_TYPE_ID']))
		{
			$arParams['ERROR'][] = Loc::getMessage('CC_BLL_BIZPROC_MODULE_NOT_INSTALLED');

			return $arParams;
		}

		global $USER;
		$accessService =
			(new \Bitrix\Lists\Api\Service\AccessService(
				(int)$USER->GetID(),
				new \Bitrix\Lists\Service\Param([
					'IBLOCK_TYPE_ID' => (string)$arParams['IBLOCK_TYPE_ID'],
					'IBLOCK_ID' => false,
					'SOCNET_GROUP_ID' => 0,
				])
			))
		;
		$checkPermissionResult = $accessService->checkPermissions();
		$arParams['LIST_PERM'] = $checkPermissionResult->getPermission();

		if (!$checkPermissionResult->isSuccess())
		{
			// todo: localization Bitrix\Lists\Security\Right
			$arParams['ERROR'][] = Loc::getMessage('CC_BLL_UNKNOWN_ERROR'); // $checkPermissionResult->getErrorMessages()[0]
		}
		elseif ($accessService->isAccessDeniedPermission($arParams['LIST_PERM']))
		{
			$arParams['ERROR'][] = Loc::getMessage('CC_BLL_ACCESS_DENIED');
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
		$this->arResult['JS_OBJECT'] = 'ListsProcessesClass_' . $this->randString();

		$this->arResult['HEADERS'] = $this->getGridHeaders();
		$this->arResult['FILTER'] = $this->getGridFilterHeaders();
		$this->arResult['FILTER_PRESETS'] = $this->getGridPresets();

		$this->processGridAction();

		$selectFields = ['ID', 'IBLOCK_TYPE_ID', 'IBLOCK_ID', 'NAME'];

		$gridOptions = new CGridOptions($this->arResult['GRID_ID']);
		$gridSort = $gridOptions->getSorting(['sort' => ['ID' => 'desc']]);
		$this->arResult['SORT'] = $gridSort['sort'];

		$filterableFields = array_column($this->arResult['FILTER'], 'id');
		$filterOption = new Bitrix\Main\UI\Filter\Options($this->arResult['FILTER_ID']);
		$filterData = $filterOption->getFilter($this->arResult['FILTER']);
		$filter = $this->prepareElementFilter($filterData, $filterableFields);
		$filter['CREATED_BY'] = $this->arParams['USER_ID'];
		$iblockTypeId = COption::GetOptionString('lists', 'livefeed_iblock_type_id');
		$filter['=IBLOCK_TYPE'] = $iblockTypeId;
		$filter['CHECK_PERMISSIONS'] = ($this->arParams['LIST_PERM'] >= CListPermissions::CAN_READ ? 'N' : 'Y');

		$useComments = (bool)CModule::includeModule('forum');
		$workflows = [];
		$this->arResult['DATA'] = [];
		$this->arResult['COMMENTS_COUNT'] = [];

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
				'IBLOCK_ID' => $element['IBLOCK_ID'],
				'DOCUMENT_NAME' => $element['NAME'],
				'DOCUMENT_URL' => $path
					. COption::GetOptionString('lists', 'livefeed_url')
					. '?livefeed=y&list_id='
					. $element['IBLOCK_ID']
					. '&element_id='
					. $element['ID']
				,
				'WORKFLOW_ID' => $documentState ? $documentState['ID'] : '',
				'WORKFLOW_STATE' => $documentState ? htmlspecialcharsbx($documentState['STATE_TITLE']) : '',
			];
		}

		foreach ($this->arResult['DATA'] as $data)
		{
			if ($useComments && $data['WORKFLOW_ID'])
			{
				$workflows[] = 'WF_' . $data['WORKFLOW_ID'];
			}

			$this->arResult['RECORDS'][] = [
				'data' => $data,
				'actions' => $this->createRowActions($data)
			];
		}

		$workflows = array_unique($workflows);
		if ($useComments && $workflows)
		{
			$iterator = CForumTopic::getList([], ['@XML_ID' => $workflows]);
			while ($row = $iterator->fetch())
			{
				$this->arResult['COMMENTS_COUNT'][$row['XML_ID']] = $row['POSTS'];
			}
		}

		$this->arResult['COUNTERS'] = ['all' => 0];

		$this->arResult['NAV_OBJECT'] = $elementObject;
		$componentObject = null;
		$this->arResult['GRID_ENABLE_NEXT_PAGE'] = ($elementObject->PAGEN < $elementObject->NavPageCount);
		$this->arResult['NAV_STRING'] = $elementObject->getPageNavStringEx(
			$componentObject,
			'',
			'grid',
			false,
			null,
			$gridOptions->getNavParams()
		);
		$this->arResult['GRID_PAGE_SIZES'] = [
			['NAME' => '5', 'VALUE' => '5'],
			['NAME' => '10', 'VALUE' => '10'],
			['NAME' => '20', 'VALUE' => '20'],
			['NAME' => '50', 'VALUE' => '50'],
			['NAME' => '100', 'VALUE' => '100'],
		];

		if ($this->canEditAllRows())
		{
			$snippet = new \Bitrix\Main\Grid\Panel\Snippet();
			$this->arResult['GRID_ACTION_PANEL'] = [
				'GROUPS' => [
					[
						'ITEMS' => [
							$snippet->getRemoveButton(),
						],
					],
				],
			];
		}

		$this->includeComponentTemplate();
	}

	private function getGridHeaders(): array
	{
		return [
			[
				'id' => 'ID',
				'name' => 'ID',
				'default' => false,
				'sort' => 'ID',
			],
			[
				'id' => 'DOCUMENT_NAME',
				'name' => Loc::getMessage('CC_BLL_DOCUMENT_NAME_MSGVER_1'),
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
	}

	private function getGridFilterHeaders(): array
	{
		return [
			[
				'id' => 'ID',
				'name' => 'ID',
				'type' => 'number',
			],
			[
				'id' => 'NAME',
				'name' => Loc::getMessage('BPATL_NAME'),
				'type' => 'string',
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
					'R' => Loc::getMessage('CC_BLL_FILTER_STATUS_RUNNING_1'),
					'C' => Loc::getMessage('CC_BLL_FILTER_STATUS_COMPLETE_1'),
				],
				'default' => true,
			],
		];
	}

	private function getGridPresets(): array
	{
		return [
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
	}

	private function createRowActions($element)
	{
		$actions = [];

		if ($element['DOCUMENT_URL'])
		{
			$actions[] = [
				'ICONCLASS' => '',
				'DEFAULT' => true,
				'TEXT' => Loc::getMessage('CC_BLL_C_DOCUMENT_MSGVER_1'),
				'ONCLICK' => 'window.open("' . $element['DOCUMENT_URL'] . '");',
			];
		}

		if ($this->canEditElement($element['IBLOCK_ID'], $element['ID']))
		{
			$bpTemplates = $this->getIblockBpTemplates($element['IBLOCK_ID']);
			if ($bpTemplates)
			{
				$documentType = BizProcDocument::generateDocumentComplexType(
					$this->arParams['IBLOCK_TYPE_ID'],
					$element['IBLOCK_ID']
				);
				$bpActions = [];

				foreach ($bpTemplates as $template)
				{
					$params = \Bitrix\Main\Web\Json::encode(array(
						'moduleId' => $documentType[0],
						'entity' => $documentType[1],
						'documentType' => $documentType[2],
						'documentId' => $element['ID'],
						'templateId' => $template['id'],
						'templateName' => $template['name'],
						'hasParameters' => $template['hasParameters']
					));
					$bpActions[] = [
						'TEXT' => $template['name'],
						'ONCLICK' => 'BX.Bizproc.Starter.singleStart('
							. $params
							. ', function(){BX.Main.gridManager.reload(\''
							. CUtil::JSEscape($this->arResult['GRID_ID'])
							. '\');});',
					];
				}

				$actions[] = array(
					'TEXT' => Loc::getMessage('CC_BLL_ELEMENT_ACTION_MENU_START_BP'),
					'MENU' => $bpActions,
				);
			}

			$actions[] = [
				'TEXT' => Loc::getMessage('CC_BLL_ELEMENT_ACTION_MENU_DELETE'),
				'ONCLICK' => "javascript:BX.Lists['" . $this->arResult['JS_OBJECT'] . "'].deleteElement('" .
					$this->arResult['GRID_ID'] . "', '" . $element['ID'] . "')",
			];
		}

		return $actions;
	}

	private function canEditElement($iblockId, $elementId): bool
	{
		if (!isset($this->canEditElements[$elementId]))
		{
			$listsPerm = CListPermissions::checkAccess(
				$this->getUser(),
				$this->arParams['IBLOCK_TYPE_ID'],
				$iblockId,
			);

			$this->canEditElements[$elementId] = (
				$listsPerm >= CListPermissions::CAN_WRITE
				|| CIBlockElementRights::userHasRightTo($iblockId, $elementId, 'element_edit')
			);
		}

		return $this->canEditElements[$elementId];
	}

	private function canEditAllRows(): bool
	{
		return !in_array(false, $this->canEditElements, true);
	}

	private function getIblockBpTemplates($iblockId): array
	{
		if (!isset($this->listIblockBpTemplates[$iblockId]))
		{
			$this->listIblockBpTemplates[$iblockId] = CBPDocument::getTemplatesForStart(
				$this->getUser()->getId(),
				BizProcDocument::generateDocumentComplexType($this->arParams['IBLOCK_TYPE_ID'], $iblockId),
			);
		}

		return $this->listIblockBpTemplates[$iblockId];
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

			$op = '';
			$filterKey = $key;

			if (mb_substr($key, -5) == '_from')
			{
				$filterKey = mb_substr($key, 0, -5);
				$op = (!empty($filterData[$filterKey . '_numsel']) && $filterData[$filterKey . '_numsel'] === 'more')
					? '>'
					: '>='
				;
			}
			elseif (mb_substr($key, -3) == '_to')
			{
				$filterKey = mb_substr($key, 0, -3);
				$op = (!empty($filterData[$filterKey . '_numsel']) && $filterData[$filterKey . '_numsel'] == 'less')
					? '<'
					: '<='
				;

				if (in_array($filterKey, ['TIMESTAMP_X', 'DATE_CREATE']))
				{
					global $DB;
					$dateFormat = $DB->dateFormatToPHP(Csite::getDateFormat());
					$dateParse = date_parse_from_format($dateFormat, $value);
					if (
						!mb_strlen($dateParse['hour'])
						&& !mb_strlen($dateParse['minute'])
						&& !mb_strlen($dateParse['second'])
					)
					{
						$timeFormat = $DB->dateFormatToPHP(CSite::getTimeFormat());
						$value .= ' ' . date($timeFormat, mktime(23, 59, 59, 0, 0, 0));
					}
				}
			}
			elseif ($key == 'NAME')
			{
				$op = '?';
			}

			if ($key == 'FIND')
			{
				$op = '?';
				$filter[$op . 'SEARCHABLE_CONTENT'] = $value;
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

	private function processGridAction()
	{
		if ($this->arParams['LIST_PERM'] < CListPermissions::CAN_WRITE)
		{
			return;
		}

		$actionKey = 'action_button_' . $this->arResult['GRID_ID'];
		if (
			check_bitrix_sessid()
			&& $this->request->getPost($actionKey) === 'delete'
		)
		{
			$filter = [];
			$filter['CREATED_BY'] = $this->arParams['USER_ID'];
			$filter['=IBLOCK_TYPE'] = $this->arParams['IBLOCK_TYPE_ID'];

			$postId = $this->request->getPost('ID');
			$filter['=ID'] = (is_array($postId) ? $postId : []);

			if (!empty($filter['=ID']))
			{
				$filter['SHOW_NEW'] = 'Y';
				$obElement = new CIBlockElement;

				$rsElements = CIBlockElement::getList([], $filter, false, false, ['ID']);
				while ($arElement = $rsElements->Fetch())
				{
					$obElement->delete($arElement['ID']);
				}
			}
		}
	}
}
