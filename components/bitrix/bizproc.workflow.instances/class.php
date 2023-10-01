<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
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
		'WS_WORKFLOW_TEMPLATE_NAME' => 'TEMPLATE.NAME',
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
					'*' => GetMessage('BPWI_FILTER_DOCTYPE_ALL'),
					'is_locked' => GetMessage('BPWI_FILTER_PRESET_LOCKED'),
					'processes' => GetMessage('BPWI_MODULE_LISTS'),
					'crm' => GetMessage('BPWI_FILTER_DOCTYPE_CRM'),
					'disk' => GetMessage('BPWI_MODULE_DISK'),
					'lists' => GetMessage('BPWI_MODULE_IBLOCK'),
				],
			],
		];
		if (Loader::includeModule('ui'))
		{
			if ($this->isAdmin)
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

			$result[] = [
				'id' => 'WS_WORKFLOW_TEMPLATE_ID',
				'name' => Loc::getMessage('BPWI_WS_WORKFLOW_TEMPLATE_ID'),
				'type' => 'entity_selector',
				'default' => false,
				'params' => [
					'multiple' => 'N',
					'dialogOptions' => [
						'context' => 'bp-filter',
						'entities' => [
							[
								'id' => 'bizproc-template',
								'options' => [
									'showManual' => true,
								],
							],
							['id' => 'bizproc-script-template'],
							['id' => 'bizproc-automation-template'],
						],
						'multiple' => 'N',
						'dropdownMode' => true,
						'hideOnSelect' => true,
						'hideOnDeselect' => false,
						'clearSearchOnSelect' => true,
						'showAvatars' => false,
						'compactView' => true,
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
				'fields' => ['TYPE' => '*'],
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
			$this->gridOptions = new Bitrix\Main\Grid\Options(static::GRID_ID);
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

		$direction = mb_strtolower($orderRule[$orderKeys[0]]) === 'asc' ? 'asc' : 'desc';

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

		$this->killWorkflowsAction();

		$gridColumns = $this->getGridOptions()->getUsedColumns();
		$showDocumentName = !$gridColumns || in_array('WS_DOCUMENT_NAME', $gridColumns, true);

		$pageNavigation = $this->getPageNavigation();
		$filter = $this->getGridFilter();

		$instanceIds =
			WorkflowInstanceTable::query()
				->setSelect(['ID'])
				->setOrder($this->getSorting())
				->setLimit($pageNavigation->getLimit())
				->setOffset($pageNavigation->getOffset())
				->setFilter($filter)
				->exec()
				->fetchCollection()
				->getIdList()
		;
		$pageNavigation->setRecordCount(WorkflowInstanceTable::getCount($filter));

		$records = [];
		if ($instanceIds)
		{
			$iterator =
				WorkflowInstanceTable::query()
					->setSelect($this->getSelectFields($gridColumns))
					->whereIn('ID', $instanceIds)
					->exec()
			;

			$records = array_fill_keys($instanceIds, []);
			while ($row = $iterator->fetch())
			{
				$data = $this->prepareRowData($row, $showDocumentName);
				$actions = $this->prepareRowActions($data);
				$records[$data['ID']] = ['data' => $data, 'editable' => $this->isAdmin(), 'actions' => $actions];
			}
		}

		$this->arResult = [
			'HEADERS' => $this->getGridHeaders(),
			'FILTER' =>  $this->getFilter(),
			'SORT' => $this->getSorting(true),
			'NAV_OBJECT' => $pageNavigation,
			'RECORDS' => $records,
			'GRID_ID' => static::GRID_ID,
			'FILTER_ID' => static::GRID_ID . '_filter',
			'FILTER_PRESETS' => $this->getFilterPresets(),
			'EDITABLE' => $this->isAdmin(),
		];

		$this->includeComponentTemplate();
	}

	private function killWorkflowsAction(): void
	{
		$gridIds = $this->request->getPost('ID');
		$action = $this->request->getPost('action');
		$idFromAction = $this->request->getPost('id');

		$killIds = [];
		if (!empty($gridIds) && check_bitrix_sessid() && $this->isAdmin())
		{
			$killIds = (array)$gridIds;
		}
		elseif (!empty($action) && $action === 'deleteRow' && !empty($idFromAction) && check_bitrix_sessid())
		{
			$killIds[] = $idFromAction;
		}

		foreach ($killIds as $id)
		{
			CBPDocument::killWorkflow($id);
		}
	}

	private function getSelectFields(array $gridColumns): array
	{
		$selectFields = [
			'ID',
			'MODIFIED',
			'OWNER_ID',
			'OWNED_UNTIL',
			'WS_MODULE_ID' => $this->getFieldName('WS_MODULE_ID'),
			'WS_ENTITY' => $this->getFieldName('WS_ENTITY'),
			'WS_DOCUMENT_ID' => $this->getFieldName('WS_DOCUMENT_ID'),
		];

		$gridHeaders = $this->getGridHeaders();
		foreach ($gridHeaders as $header)
		{
			if (
				(!$gridColumns || in_array($header['id'], $gridColumns, true))
				&& !in_array($header['id'], $selectFields, true)
			)
			{
				if ($this->getFieldName($header['id']))
				{
					$selectFields[$header['id']] = $this->getFieldName($header['id']);
				}
				elseif ($header['id'] === 'IS_LOCKED' && !in_array('OWNED_UNTIL', $selectFields, true))
				{
					$selectFields['OWNED_UNTIL'] = $this->getFieldName('OWNED_UNTIL');
				}
			}
		}

		if (isset($selectFields['WS_STARTED_BY']))
		{
			$selectFields['WS_STARTED_USER_NAME'] = $this->getFieldName('WS_STARTED_USER_NAME');
			$selectFields['WS_STARTED_USER_LAST_NAME'] = $this->getFieldName('WS_STARTED_USER_LAST_NAME');
			$selectFields['WS_STARTED_USER_LOGIN'] = $this->getFieldName('WS_STARTED_USER_LOGIN');
		}

		if (isset($selectFields['WS_WORKFLOW_TEMPLATE_ID']))
		{
			$selectFields['WS_WORKFLOW_TEMPLATE_NAME'] = $this->getFieldName('WS_WORKFLOW_TEMPLATE_NAME');
		}

		return $selectFields;
	}

	private function getGridFilter(): array
	{
		$typeFilter = $this->request->get('type');
		$filterOptions = new \Bitrix\Main\UI\Filter\Options(static::GRID_ID . '_filter');
		$gridFilter = $filterOptions->getFilter();

		if ($typeFilter && empty($gridFilter['TYPE'])) //compatible
		{
			$gridFilter['TYPE'] = $typeFilter;
		}

		$filter = $this->prepareFilter($gridFilter);

		if (!$this->isAdmin())
		{
			$filter['=' . $this->getFieldName('WS_STARTED_BY')] = \Bitrix\Main\Engine\CurrentUser::get()->getId();
		}

		return $filter;
	}

	private function prepareRowData(array $data, bool $showDocumentName): array
	{
		if (isset($data['WS_WORKFLOW_TEMPLATE_ID']))
		{
			$data['WS_WORKFLOW_TEMPLATE_ID'] =
				isset($data['WS_WORKFLOW_TEMPLATE_NAME'])
					? $data['WS_WORKFLOW_TEMPLATE_NAME'] . ' [' . $data['WS_WORKFLOW_TEMPLATE_ID'] . ']'
					: null
			;
		}

		$data['IS_LOCKED'] = $data['OWNED_UNTIL'] && $data['OWNED_UNTIL']->getTimestamp() < $this->getLockedTime();

		if (!empty($data['WS_STARTED_BY']))
		{
			$data['WS_STARTED_BY'] =
				CUser::FormatName(
					$this->arParams["NAME_TEMPLATE"],
					[
						'LOGIN' => $data['WS_STARTED_USER_LOGIN'],
						'NAME' => $data['WS_STARTED_USER_NAME'],
						'LAST_NAME' => $data['WS_STARTED_USER_LAST_NAME'],
						],
					true
				)
				. ' ['
				. $data['WS_STARTED_BY']
				. ']'
			;
		}

		$data['DOCUMENT_URL'] = '';
		$data['WS_DOCUMENT_NAME'] = '';

		if (
			!empty($data['WS_MODULE_ID'])
			&& !empty($data['WS_ENTITY'])
			&& !empty($data['WS_DOCUMENT_ID'])
		)
		{
			$data['DOCUMENT_URL'] = CBPDocument::GetDocumentAdminPage([
				$data['WS_MODULE_ID'],
				$data['WS_ENTITY'],
				$data['WS_DOCUMENT_ID'],
			]);
			if ($showDocumentName)
			{
				$data['WS_DOCUMENT_NAME'] = CBPDocument::getDocumentName([
					$data['WS_MODULE_ID'],
					$data['WS_ENTITY'],
					$data['WS_DOCUMENT_ID'],
				]);

				if (!$data['WS_DOCUMENT_NAME'])
				{
					$data['WS_DOCUMENT_NAME'] = Loc::getMessage('BPWI_DOCUMENT_NAME');
				}
			}
		}

		return $data;
	}

	private function prepareRowActions(array $data): array
	{
		$actions = [];

		if ($data['DOCUMENT_URL'])
		{
			$actions[] = [
				'DEFAULT' => true,
				'TEXT' => Loc::getMessage('BPWI_OPEN_DOCUMENT'),
				'ONCLICK' => "window.open('" . $data['DOCUMENT_URL'] . "');",
			];
		}

		if ($this->isAdmin())
		{
			$actions[] = [
				'TEXT' => Loc::getMessage('BPWI_DELETE_LABEL'),
				'ONCLICK' => "BX.Bizproc.Component.WorkflowInstances.Instance.deleteItem('{$data['ID']}');",
			];
		}

		return $actions;
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
