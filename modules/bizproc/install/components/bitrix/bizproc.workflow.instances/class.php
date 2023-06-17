<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable;
use Bitrix\Main\Localization\Loc;

if (!Loader::includeModule('bizproc'))
{
	return;
}

Loc::loadMessages(__FILE__);

class BizprocWorkflowInstances extends \CBitrixComponent
{
	const GRID_ID = 'bizproc_instances';

	protected $lockedTime;
	protected $isAdmin;
	protected $gridOptions;
	protected static $fields = [
		'ID' => 'ID',
		'MODIFIED' => 'MODIFIED',
		'OWNER_ID' => 'OWNER_ID',
		'OWNED_UNTIL' => 'OWNED_UNTIL',
		'WS_MODULE_ID' => 'MODULE_ID',
		'WS_ENTITY' => 'ENTITY',
		'WS_DOCUMENT_ID' => 'DOCUMENT_ID',
		'WS_STARTED' => 'STARTED',
		'WS_STARTED_BY' => 'STARTED_BY',
		'WS_WORKFLOW_TEMPLATE_ID' => 'WORKFLOW_TEMPLATE_ID',
		'WS_STARTED_USER_NAME' => 'STARTED_USER.NAME',
		'WS_STARTED_USER_LAST_NAME' => 'STARTED_USER.LAST_NAME',
		'WS_STARTED_USER_LOGIN' => 'STARTED_USER.LOGIN',
	];
	protected static $moduleNames = [];

	protected function isAdmin()
	{
		if ($this->isAdmin === null)
		{
			$this->isAdmin = (new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser))->isAdmin();
		}

		return $this->isAdmin;
	}

	public function onPrepareComponentParams($params)
	{
		if (empty($params['NAME_TEMPLATE']))
		{
			$params['NAME_TEMPLATE'] = COption::GetOptionString(
				"bizproc",
				"name_template",
				CSite::GetNameFormat(false),
				SITE_ID
			);
		}

		if (!empty($_REQUEST['WS_STARTED_BY']) && !empty($_REQUEST['clear_filter']))
		{
			unset($_REQUEST['WS_STARTED_BY']);
		}

		return $params;
	}

	protected function getFieldName($name)
	{
		return $name && isset(static::$fields[$name]) ? static::$fields[$name] : null;
	}

	protected function getGridHeaders()
	{
		return [
			[
				'id' => 'ID',
				'name' => 'ID',
				'default' => false,
				'sort' => 'ID',
			],
			[
				'id' => 'WS_MODULE_ID',
				'name' => Loc::getMessage('BPWI_WS_MODULE_ID'),
				'default' => true,
				'sort' => 'WS_MODULE_ID',
			],
			[
				'id' => 'WS_DOCUMENT_NAME',
				'name' => Loc::getMessage('BPWI_DOCUMENT_NAME'),
				'default' => true,
				'sort' => '',
			],
			[
				'id' => 'MODIFIED',
				'name' => Loc::getMessage('BPWI_MODIFIED'),
				'default' => true,
				'sort' => 'MODIFIED',
			],
			[
				'id' => 'IS_LOCKED',
				'name' => Loc::getMessage('BPWI_IS_LOCKED'),
				'default' => true,
				'sort' => '',
			],
			[
				'id' => 'OWNED_UNTIL',
				'name' => Loc::getMessage('BPWI_OWNED_UNTIL'),
				'default' => false,
				'sort' => 'OWNED_UNTIL',
			],
			[
				'id' => 'WS_STARTED',
				'name' => Loc::getMessage('BPWI_WS_STARTED'),
				'default' => true,
				'sort' => 'WS_STARTED',
			],
			[
				'id' => 'WS_STARTED_BY',
				'name' => Loc::getMessage('BPWI_WS_STARTED_BY'),
				'default' => true,
				'sort' => 'WS_STARTED_BY',
			],
			[
				'id' => 'WS_WORKFLOW_TEMPLATE_ID',
				'name' => Loc::getMessage('BPWI_WS_WORKFLOW_TEMPLATE_ID'),
				'default' => true,
				'sort' => 'WS_WORKFLOW_TEMPLATE_ID',
			],
		];
	}

	protected function getFilter()
	{
		$result = [
			[
				'id' => 'MODIFIED',
				'name' => Loc::getMessage('BPWI_MODIFIED'),
				'type' => 'date',
				'default' => true,
			],
			[
				'id' => 'WS_STARTED',
				'name' => Loc::getMessage('BPWI_WS_STARTED'),
				'type' => 'date',
				'default' => false,
			],
			[
				'id' => 'TYPE',
				'name' => Loc::getMessage('BPWI_FILTER_TYPE'),
				'type' => 'list',
				'default' => true,
				'items' => [
					'' => GetMessage('BPWI_FILTER_DOCTYPE_ALL'),
					'is_locked' => GetMessage('BPWI_FILTER_PRESET_LOCKED'),
					'processes' => GetMessage('BPWI_MODULE_LISTS'),
					'crm' => GetMessage('BPWI_FILTER_DOCTYPE_CRM'),
					'disk' => GetMessage('BPWI_MODULE_DISK'),
					'lists' => GetMessage('BPWI_MODULE_IBLOCK'),
				],
			],
		];
		if ($this->isAdmin() && Loader::includeModule('ui'))
		{
			$result[] = [
				'id' => 'WS_STARTED_BY',
				'name' => Loc::getMessage('BPWI_WS_STARTED_BY'),
				'type' => 'entity_selector',
				'default' => true,
				'params' => [
					'multiple' => 'N',
					'dialogOptions' => [
						'context' => 'filter',
						'entities' => [
							[
								'id' => 'user',
								'options' => [
									'intranetUsersOnly' => true,
									'inviteEmployeeLink' => false,
								],
							],
						],
					],
				],
			];
		}

		return $result;
	}

	protected function getFilterPresets()
	{
		return [
			'filter_all' => [
				'name' => GetMessage('BPWI_FILTER_PRESET_ALL'),
				'fields' => ['TYPE' => ''],
				'default' => true,
			],
			'filter_is_locked' => [
				'name' => GetMessage('BPWI_FILTER_PRESET_LOCKED'),
				'fields' => ['TYPE' => 'is_locked'],
			],
		];
	}

	protected function getDocumentTypes()
	{
		return [
			'*' => ['NAME' => Loc::getMessage('BPWI_FILTER_DOCTYPE_ALL')],
			'is_locked' => [
				'NAME' => Loc::getMessage('BPWI_FILTER_PRESET_LOCKED'),
			],
			'processes' => [
				'NAME' => Loc::getMessage('BPWI_MODULE_LISTS'),
				'MODULE_ID' => 'lists',
				'ENTITY' => 'BizprocDocument',
			],
			'crm' => [
				'NAME' => Loc::getMessage('BPWI_FILTER_DOCTYPE_CRM'),
				'MODULE_ID' => 'crm',
			],
			'disk' => [
				'NAME' => Loc::getMessage('BPWI_MODULE_DISK'),
				'MODULE_ID' => 'disk',
			],
			'lists' => [
				'NAME' => Loc::getMessage('BPWI_MODULE_IBLOCK'),
				'MODULE_ID' => 'lists',
				'ENTITY' => 'Bitrix\Lists\BizprocDocumentLists',
			],
		];
	}

	protected function setPageTitle($title)
	{
		global $APPLICATION;
		$APPLICATION->SetTitle($title);
	}

	protected function getGridOptions()
	{
		if ($this->gridOptions === null)
		{
			$this->gridOptions = new CGridOptions(static::GRID_ID);
		}
		return $this->gridOptions;
	}

	private function prepareFilter(array $gridFilter)
	{
		$filter = [];
		foreach ($gridFilter as $key => $value)
		{
			if ($value === '' || $value === null)
			{
				continue;
			}

			if (mb_substr($key, -5) == '_from')
			{
				$op = '>=';
				$newKey = mb_substr($key, 0, -5);
			}
			elseif (mb_substr($key, -3) == '_to')
			{
				$op = '<=';
				$newKey = mb_substr($key, 0, -3);

				if (in_array($newKey, ['MODIFIED', 'WS_STARTED']))
				{
					if (!preg_match('/\\d\\d:\\d\\d:\\d\\d\$/', $value))
					{
						$value .= ' 23:59:59';
					}
				}
			}
			else
			{
				$op = '';
				$newKey = $key;
			}

			if ($newKey === 'TYPE')
			{
				$types = $this->getDocumentTypes();

				if (!empty($types[$value]['MODULE_ID']))
				{
					$filter['=' . $this->getFieldName('WS_MODULE_ID')] = $types[$value]['MODULE_ID'];
					if (!empty($types[$value]['ENTITY']))
					{
						$filter['=' . $this->getFieldName('WS_ENTITY')] = $types[$value]['ENTITY'];
					}
				}
				elseif ($value === 'is_locked')
				{
					global $DB;
					$filter['<OWNED_UNTIL'] = date($DB->DateFormatToPHP(FORMAT_DATETIME), $this->getLockedTime());
				}

				continue;
			}

			$fieldKey = $this->getFieldName($newKey);
			if (!$fieldKey)
			{
				continue;
			}

			if ($fieldKey == 'WS_STARTED_BY' && !$this->isAdmin())
			{
				continue;
			}

			$filter[$op . $fieldKey] = $value;
		}

		return $filter;
	}

	protected function getSorting($useAliases = false)
	{
		$gridSort = $this->getGridOptions()->getSorting(['sort' => ['MODIFIED' => 'desc']]);
		$orderRule = $gridSort['sort'];
		$orderKeys = array_keys($orderRule);
		$fieldName = $this->getFieldName($orderKeys[0]);
		if ($fieldName === null)
		{
			$fieldName = 'MODIFIED';
		}
		elseif ($useAliases)
		{
			$fieldName = $orderKeys[0];
		}

		$direction = mb_strtoupper($orderRule[$orderKeys[0]]);
		if ($direction !== 'DESC')
		{
			$direction = 'ASC';
		}

		return [$fieldName => $direction];
	}

	protected function getPageNavigation(): \Bitrix\Main\UI\PageNavigation
	{
		$gridOptions = new Bitrix\Main\Grid\Options(static::GRID_ID);
		$navParams = $gridOptions->GetNavParams();

		$pageNavigation= new Bitrix\Main\UI\PageNavigation(static::GRID_ID);
		$pageNavigation->setPageSize($navParams['nPageSize'])->initFromUri();

		return $pageNavigation;
	}

	protected function getLockedTime()
	{
		if ($this->lockedTime === null)
		{
			$this->lockedTime = time() - WorkflowInstanceTable::LOCKED_TIME_INTERVAL;
		}

		return $this->lockedTime;
	}

	public function executeComponent()
	{
		if (!Loader::includeModule('bizproc'))
		{
			return false;
		}

		if ($this->arParams['SET_TITLE'])
		{
			$this->setPageTitle(Loc::getMessage('BPWI_PAGE_TITLE'));
		}

		$killIds = [];


		if (!empty($_POST['ID']) && check_bitrix_sessid() && $this->isAdmin())
		{
			$killIds = (array)$_POST['ID'];
		}
		elseif (!empty($_POST['action']) && $_POST['action'] === 'deleteRow' && !empty($_POST['id']))
		{
			$killIds[] = $_POST['id'];
		}

		foreach ($killIds as $id)
		{
			CBPDocument::killWorkflow($id);
		}

		$selectFields = [
			'ID',
			'MODIFIED',
			'OWNER_ID',
			'OWNED_UNTIL',
			'WS_MODULE_ID' => $this->getFieldName('WS_MODULE_ID'),
			'WS_ENTITY' => $this->getFieldName('WS_ENTITY'),
			'WS_DOCUMENT_ID' => $this->getFieldName('WS_DOCUMENT_ID'),
		];
		$gridColumns = $this->getGridOptions()->getVisibleColumns();

		$this->arResult['HEADERS'] = $this->getGridHeaders();

		$showDocumentName = false;
		foreach ($this->arResult['HEADERS'] as $h)
		{
			if ((count($gridColumns) <= 0 || in_array($h['id'], $gridColumns)) && !in_array($h['id'], $selectFields))
			{
				if ($this->getFieldName($h['id']))
				{
					$selectFields[$h['id']] = $this->getFieldName($h['id']);
				}
				elseif ($h['id'] == 'IS_LOCKED' && !in_array('OWNED_UNTIL', $selectFields))
				{
					$selectFields['OWNED_UNTIL'] = $this->getFieldName('OWNED_UNTIL');
				}
				elseif ($h['id'] == 'WS_DOCUMENT_NAME')
				{
					$showDocumentName = true;
				}
			}
		}

		if (isset($selectFields['WS_STARTED_BY']))
		{
			$selectFields['WS_STARTED_USER_NAME'] = $this->getFieldName('WS_STARTED_USER_NAME');
			$selectFields['WS_STARTED_USER_LAST_NAME'] = $this->getFieldName('WS_STARTED_USER_LAST_NAME');
			$selectFields['WS_STARTED_USER_LOGIN'] = $this->getFieldName('WS_STARTED_USER_LOGIN');
		}

		$typeFilter = $_REQUEST['type'] ?? null;
		$this->arResult['FILTER'] = $this->getFilter();

		$filterOptions = new \Bitrix\Main\UI\Filter\Options(static::GRID_ID . '_filter');
		$gridFilter = $filterOptions->getFilter();

		if ($typeFilter && empty($gridFilter['TYPE'])) //compatible
		{
			$gridFilter['TYPE'] = $typeFilter;
		}

		$filter = $this->prepareFilter($gridFilter);
		if (!$this->isAdmin())
		{
			global $USER;
			$filter['=' . $this->getFieldName('WS_STARTED_BY')] = $USER->getId();
		}

		$templatesFilter = [];
		if (isset($filter['=MODULE_ID']))
		{
			$templatesFilter['MODULE_ID'] = $filter['=MODULE_ID'];
			if (isset($filter['=ENTITY']))
			{
				$templatesFilter['ENTITY'] = $filter['=ENTITY'];
			}
		}

		$templatesList = ['' => Loc::getMessage('BPWI_WORKFLOW_ID_ANY')];
		$dbResTmp = \CBPWorkflowTemplateLoader::GetList(
			['NAME' => 'ASC'],
			$templatesFilter,
			false,
			false,
			['ID', 'NAME']
		);
		while ($arResTmp = $dbResTmp->GetNext())
		{
			$templatesList[$arResTmp['ID']] = $arResTmp['NAME'];
		}
		$this->arResult['FILTER'][] = [
			'id' => 'WS_WORKFLOW_TEMPLATE_ID',
			'name' => Loc::getMessage('BPWI_WS_WORKFLOW_TEMPLATE_ID'),
			'type' => 'list',
			'items' => $templatesList,
		];

		$pageNavigation = $this->getPageNavigation();

		$this->arResult['SORT'] = $this->getSorting(true);
		$this->arResult['NAV_OBJECT'] = $pageNavigation;
		$this->arResult['RECORDS'] = [];

		$iterator = WorkflowInstanceTable::getList([
			'order' => $this->getSorting(),
			'select' => $selectFields,
			'filter' => $filter,
			'limit' => $pageNavigation->getLimit(),
			'offset' => $pageNavigation->getOffset(),
		]);

		$pageNavigation->setRecordCount(WorkflowInstanceTable::getCount($filter));

		while ($row = $iterator->fetch())
		{
			$row['WS_WORKFLOW_TEMPLATE_ID'] =
				$row['WS_WORKFLOW_TEMPLATE_ID'] && isset($templatesList[$row['WS_WORKFLOW_TEMPLATE_ID']])
				? $templatesList[$row['WS_WORKFLOW_TEMPLATE_ID']]
				: null
			;
			$row['IS_LOCKED'] = $row['OWNED_UNTIL'] && $row['OWNED_UNTIL']->getTimestamp() < $this->getLockedTime();

			if (!empty($row['WS_STARTED_BY']))
			{
				$row['WS_STARTED_BY'] = CUser::FormatName(
						$this->arParams["NAME_TEMPLATE"],
						[
							'LOGIN' => $row['WS_STARTED_USER_LOGIN'],
							'NAME' => $row['WS_STARTED_USER_NAME'],
							'LAST_NAME' => $row['WS_STARTED_USER_LAST_NAME'],
						],
						true) . " [" . $row['WS_STARTED_BY'] . "]";
			}
			$row['DOCUMENT_URL'] = $row['WS_DOCUMENT_NAME'] = '';
			if (
				!empty($row['WS_MODULE_ID'])
				&& !empty($row['WS_ENTITY'])
				&& !empty($row['WS_DOCUMENT_ID'])
			)
			{
				$row['DOCUMENT_URL'] = CBPDocument::GetDocumentAdminPage([
					$row['WS_MODULE_ID'],
					$row['WS_ENTITY'],
					$row['WS_DOCUMENT_ID'],
				]);
				if ($showDocumentName)
				{
					$row['WS_DOCUMENT_NAME'] = CBPDocument::getDocumentName([
						$row['WS_MODULE_ID'],
						$row['WS_ENTITY'],
						$row['WS_DOCUMENT_ID'],
					]);

					if (!$row['WS_DOCUMENT_NAME'])
					{
						$row['WS_DOCUMENT_NAME'] = Loc::getMessage('BPWI_DOCUMENT_NAME');
					}
				}
			}

			$rowActions = [];
			if ($row['DOCUMENT_URL'])
			{
				$rowActions[] = [
					"DEFAULT" => true,
					"TEXT" => Loc::getMessage("BPWI_OPEN_DOCUMENT"),
					"ONCLICK" => "window.open('" . $row["DOCUMENT_URL"] . "');",
				];
			}

			if ($this->isAdmin())
			{
				$rowActions[] = [
					"TEXT" => Loc::getMessage("BPWI_DELETE_LABEL"),
					"ONCLICK" => "BX.Bizproc.Component.WorkflowInstances.Instance.deleteItem('{$row['ID']}');",
				];
			}

			$this->arResult['RECORDS'][] = ['data' => $row, 'editable' => $this->isAdmin(), 'actions' => $rowActions];
		}

		$this->arResult['GRID_ID'] = static::GRID_ID;
		$this->arResult['FILTER_ID'] = static::GRID_ID . '_filter';
		$this->arResult['FILTER_PRESETS'] = $this->getFilterPresets();
		$this->arResult['EDITABLE'] = $this->isAdmin();
		$this->includeComponentTemplate();
	}

	public static function getModuleName($moduleId, $entity = null)
	{
		if ($moduleId == 'lists' && $entity == 'Bitrix\Lists\BizprocDocumentLists')
		{
			$moduleId = 'iblock';
		}

		if (!isset(static::$moduleNames[$moduleId]))
		{
			$message = Loc::getMessage('BPWI_MODULE_' . mb_strtoupper($moduleId));
			static::$moduleNames[$moduleId] = $message ?: $moduleId;
		}

		return static::$moduleNames[$moduleId];
	}
}
