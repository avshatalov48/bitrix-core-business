<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Bizproc\WorkflowInstanceTable;
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
	protected static $fields = array(
		'ID' => 'ID',
		'MODIFIED' => 'MODIFIED',
		'OWNER_ID' => 'OWNER_ID',
		'OWNED_UNTIL' => 'OWNED_UNTIL',
		'WS_MODULE_ID' => 'STATE.MODULE_ID',
		'WS_ENTITY' => 'STATE.ENTITY',
		'WS_DOCUMENT_ID' => 'STATE.DOCUMENT_ID',
		'WS_STARTED' => 'STATE.STARTED',
		'WS_STARTED_BY' => 'STATE.STARTED_BY',
		'WS_WORKFLOW_TEMPLATE_ID' => 'STATE.WORKFLOW_TEMPLATE_ID',
		'WS_STARTED_USER_NAME' => 'STATE.STARTED_USER.NAME',
		'WS_STARTED_USER_LAST_NAME' => 'STATE.STARTED_USER.LAST_NAME',
		'WS_STARTED_USER_LOGIN' => 'STATE.STARTED_USER.LOGIN',
	);
	protected static $moduleNames = array();

	protected function isAdmin()
	{
		global $USER;
		if ($this->isAdmin === null)
		{
			$this->isAdmin = $USER->IsAdmin() || (Loader::includeModule('bitrix24') && \CBitrix24::IsPortalAdmin($USER->GetID()));
		}
		return $this->isAdmin;
	}

	public function onPrepareComponentParams($params)
	{
		if (empty($params['NAME_TEMPLATE']))
			$params['NAME_TEMPLATE'] = COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID);

		if (!empty($_REQUEST['WS_STARTED_BY']) && !empty($_REQUEST['clear_filter']))
			unset($_REQUEST['WS_STARTED_BY']);

		return $params;
	}

	protected function getFieldName($name)
	{
		return $name && isset(static::$fields[$name]) ? static::$fields[$name] : null;
	}
	
	protected function getGridHeaders()
	{
		return array(
			array('id' => 'ID', 'name' => 'ID', 'default' => false, 'sort' => 'ID'),
			array('id' => 'WS_MODULE_ID', 'name' => Loc::getMessage('BPWI_WS_MODULE_ID'), 'default' => true, 'sort' => 'WS_MODULE_ID'),
			array('id' => 'WS_DOCUMENT_NAME', 'name' => Loc::getMessage('BPWI_DOCUMENT_NAME'), 'default' => true, 'sort' => ''),
			array('id' => 'MODIFIED', 'name' => Loc::getMessage('BPWI_MODIFIED'), 'default' => true, 'sort' => 'MODIFIED'),
			array('id' => 'IS_LOCKED', 'name' => Loc::getMessage('BPWI_IS_LOCKED'), 'default' => true, 'sort' => ''),
			array('id' => 'OWNED_UNTIL', 'name' => Loc::getMessage('BPWI_OWNED_UNTIL'), 'default' => false, 'sort' => 'OWNED_UNTIL'),
			array('id' => 'WS_STARTED', 'name' => Loc::getMessage('BPWI_WS_STARTED'), 'default' => true, 'sort' => 'WS_STARTED'),
			array('id' => 'WS_STARTED_BY', 'name' => Loc::getMessage('BPWI_WS_STARTED_BY'), 'default' => true, 'sort' => 'WS_STARTED_BY'),
			array('id' => 'WS_WORKFLOW_TEMPLATE_ID', 'name' => Loc::getMessage('BPWI_WS_WORKFLOW_TEMPLATE_ID'), 'default' => true, 'sort' => 'WS_WORKFLOW_TEMPLATE_ID', 'ormField' => 'STATE.WORKFLOW_TEMPLATE_ID'),
		);
	}
	
	protected function getFilter()
	{
		$result = array(
			array('id' => 'MODIFIED', 'name' => Loc::getMessage('BPWI_MODIFIED'), 'type' => 'date', 'default' => true),
			array('id' => 'WS_STARTED', 'name' => Loc::getMessage('BPWI_WS_STARTED'), 'type' => 'date', 'default' => false),
		);
		if ($this->isAdmin() && Loader::includeModule('intranet'))
			$result[] = array('id' => 'WS_STARTED_BY', 'name' => Loc::getMessage('BPWI_WS_STARTED_BY'), 'type' => 'user', 'default' => true);
		return $result;
	}
	
	protected function getFilterPresets()
	{
		return array();
	}
	
	protected function getDocumentTypes()
	{
		return array(
			'*' => array('NAME' => Loc::getMessage('BPWI_FILTER_DOCTYPE_ALL')),
			'is_locked' => array('NAME' => Loc::getMessage('BPWI_FILTER_PRESET_LOCKED'), 'CNT' => $this->getLockedCount()),
			'processes' => array('NAME' => Loc::getMessage('BPWI_MODULE_LISTS'), 'MODULE_ID' => 'lists', 'ENTITY' => 'BizprocDocument'),
			'crm' => array('NAME' => Loc::getMessage('BPWI_FILTER_DOCTYPE_CRM'), 'MODULE_ID' => 'crm'),
			'disk' => array('NAME' => Loc::getMessage('BPWI_MODULE_DISK'), 'MODULE_ID' => 'disk'),
			'lists' => array('NAME' => Loc::getMessage('BPWI_MODULE_IBLOCK'), 'MODULE_ID' => 'lists', 'ENTITY' => 'Bitrix\Lists\BizprocDocumentLists')
		);
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

	protected function mergeFilters(array $filter, array $gridFilter)
	{
		foreach ($gridFilter as $key => $value)
		{
			if ($value === '' || $value === null)
				continue;

			if (substr($key, -5) == '_from')
			{
				$op = '>=';
				$newKey = substr($key, 0, -5);
			}
			elseif (substr($key, -3) == '_to')
			{
				$op = '<=';
				$newKey = substr($key, 0, -3);

				if (in_array($newKey, array('MODIFIED', 'WS_STARTED')))
				{
					if (!preg_match('/\\d\\d:\\d\\d:\\d\\d\$/', $value))
						$value .= ' 23:59:59';
				}
			}
			else
			{
				$op = '';
				$newKey = $key;
			}

			$fieldKey = $this->getFieldName($newKey);
			if (!$fieldKey)
				continue;

			if ($fieldKey == 'WS_STARTED_BY' && !$this->isAdmin())
				continue;

			$filter[$op.$fieldKey] = $value;
		}

		return $filter;
	}

	protected function getSorting($useAliases = false)
	{
		$gridSort = $this->getGridOptions()->getSorting(array('sort' => array('MODIFIED' => 'desc')));
		$orderRule = $gridSort['sort'];
		$orderKeys  = array_keys($orderRule);
		$fieldName = $this->getFieldName($orderKeys[0]);
		if ($fieldName === null)
			$fieldName = 'MODIFIED';
		elseif ($useAliases)
			$fieldName = $orderKeys[0];

		$direction = strtoupper($orderRule[$orderKeys[0]]);
		if ($direction !== 'DESC')
			$direction = 'ASC';

		return array($fieldName => $direction);
	}

	protected function getPaginationInfo()
	{
		$pageSize = $this->getGridOptions()->getNavParams();
		$pageSize = $pageSize['nPageSize'];
		$currentPage = isset($_REQUEST['pageNumber'])? max(1, (int)$_REQUEST['pageNumber']) : 1;
		$offset = ($currentPage - 1)*$pageSize;
		return array($currentPage, $pageSize, $offset);
	}

	protected function getLockedTime()
	{
		if ($this->lockedTime === null)
			$this->lockedTime = time() - WorkflowInstanceTable::LOCKED_TIME_INTERVAL;
		return $this->lockedTime;
	}

	protected function getLockedCount()
	{
		global $DB;

		$filter = array(
			'<OWNED_UNTIL' => date($DB->DateFormatToPHP(FORMAT_DATETIME), $this->getLockedTime())
		);
		if (!$this->isAdmin())
		{
			global $USER;
			$filter['='.$this->getFieldName('WS_STARTED_BY')] = $USER->getId();
		}

		$iterator = WorkflowInstanceTable::getList(
			array(
				'select' => array(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(\'x\')')),
				'filter' => $filter,
			)
		);
		$row = $iterator->fetch();
		return $row['CNT'];
	}

	public function executeComponent()
	{
		if (!Loader::includeModule('bizproc'))
			return false;

		if ($this->arParams['SET_TITLE'])
		{
			$this->setPageTitle(Loc::getMessage('BPWI_PAGE_TITLE'));
		}

		if (!empty($_POST['ID']) && check_bitrix_sessid() && $this->isAdmin())
		{
			foreach((array)$_POST['ID'] as $id)
			{
				CBPDocument::killWorkflow($id);
			}
		}

		$selectFields = array('ID', 'MODIFIED', 'OWNER_ID', 'OWNED_UNTIL',
			'WS_MODULE_ID' => $this->getFieldName('WS_MODULE_ID'),
			'WS_ENTITY' => $this->getFieldName('WS_ENTITY'),
			'WS_DOCUMENT_ID' => $this->getFieldName('WS_DOCUMENT_ID')
		);
		$gridColumns = $this->getGridOptions()->getVisibleColumns();

		$this->arResult['HEADERS'] = $this->getGridHeaders();

		$showDocumentName = false;
		foreach ($this->arResult['HEADERS'] as $h)
		{
			if ((count($gridColumns) <= 0 || in_array($h['id'], $gridColumns)) && !in_array($h['id'], $selectFields))
			{
				if ($this->getFieldName($h['id']))
					$selectFields[$h['id']] = $this->getFieldName($h['id']);
				elseif ($h['id'] == 'IS_LOCKED' && !in_array('OWNED_UNTIL', $selectFields))
					$selectFields['OWNED_UNTIL'] = $this->getFieldName('OWNED_UNTIL');
				elseif ($h['id'] == 'WS_DOCUMENT_NAME')
					$showDocumentName = true;
			}
		}

		if (isset($selectFields['WS_STARTED_BY']))
		{
			$selectFields['WS_STARTED_USER_NAME'] =  $this->getFieldName('WS_STARTED_USER_NAME');
			$selectFields['WS_STARTED_USER_LAST_NAME'] =  $this->getFieldName('WS_STARTED_USER_LAST_NAME');
			$selectFields['WS_STARTED_USER_LOGIN'] =  $this->getFieldName('WS_STARTED_USER_LOGIN');
		}

		$filter = array();
		$templatesFilter = array();

		$this->arResult['DOCUMENT_TYPES'] = $this->getDocumentTypes();
		if (!empty($_REQUEST['type']) && isset($this->arResult['DOCUMENT_TYPES'][$_REQUEST['type']]))
		{
			$this->arResult['DOCUMENT_TYPES'][$_REQUEST['type']]['ACTIVE'] = true;
			if (!empty($this->arResult['DOCUMENT_TYPES'][$_REQUEST['type']]['MODULE_ID']))
			{
				$filter = array('='.$this->getFieldName('WS_MODULE_ID') => $this->arResult['DOCUMENT_TYPES'][$_REQUEST['type']]['MODULE_ID']);
				$templatesFilter = array('MODULE_ID' => $this->arResult['DOCUMENT_TYPES'][$_REQUEST['type']]['MODULE_ID']);
				if (!empty($this->arResult['DOCUMENT_TYPES'][$_REQUEST['type']]['ENTITY']))
				{
					$filter['='.$this->getFieldName('WS_ENTITY')] = $this->arResult['DOCUMENT_TYPES'][$_REQUEST['type']]['ENTITY'];
					$templatesFilter['ENTITY'] = $this->arResult['DOCUMENT_TYPES'][$_REQUEST['type']]['ENTITY'];
				}
			}
			elseif ($_REQUEST['type'] == 'is_locked')
			{
				global $DB;
				$filter['<OWNED_UNTIL'] = date($DB->DateFormatToPHP(FORMAT_DATETIME), $this->getLockedTime());
			}
		}
		else
			$this->arResult['DOCUMENT_TYPES']['*']['ACTIVE'] = true;

		$templatesList = array('' => Loc::getMessage('BPWI_WORKFLOW_ID_ANY'));
		$dbResTmp = \CBPWorkflowTemplateLoader::GetList(
			array('NAME' => 'ASC'),
			$templatesFilter,
			false,
			false,
			array('ID', 'NAME')
		);
		while ($arResTmp = $dbResTmp->GetNext())
			$templatesList[$arResTmp['ID']] = $arResTmp['NAME'];

		$this->arResult['FILTER'] = $this->getFilter();
		$this->arResult['FILTER'][] = array('id' => 'WS_WORKFLOW_TEMPLATE_ID', 'name' => Loc::getMessage('BPWI_WS_WORKFLOW_TEMPLATE_ID'), 'type' => 'list', 'items' => $templatesList);

		$gridFilter = $this->getGridOptions()->getFilter($this->arResult['FILTER']);
		$filter = $this->mergeFilters($filter, $gridFilter);
		if (!$this->isAdmin())
		{
			global $USER;
			$filter['='.$this->getFieldName('WS_STARTED_BY')] = $USER->getId();
		}

		list ($currentPage, $pageSize, $offset) = $this->getPaginationInfo();

		$this->arResult['SORT'] = $this->getSorting(true);
		$this->arResult['CURRENT_PAGE'] = $currentPage;
		$this->arResult['SHOW_NEXT_PAGE'] = false;
		$this->arResult['RECORDS'] = array();

		$iterator = WorkflowInstanceTable::getList(array(
			'order' => $this->getSorting(),
			'select' => $selectFields,
			'filter' => $filter,
			'limit' => $pageSize + 1,
			'offset' => $offset,
		));

		$rowsCount = 0;
		while ($row = $iterator->fetch())
		{
			$rowsCount++;
			if($rowsCount > $pageSize)
			{
				$this->arResult['SHOW_NEXT_PAGE'] = true;
				break;
			}

			$row['WS_WORKFLOW_TEMPLATE_ID'] = $row['WS_WORKFLOW_TEMPLATE_ID'] ? $templatesList[$row['WS_WORKFLOW_TEMPLATE_ID']] : null;
			$row['IS_LOCKED'] = $row['OWNED_UNTIL'] && $row['OWNED_UNTIL']->getTimestamp() < $this->getLockedTime();

			if (!empty($row['WS_STARTED_BY']))
			{
				$row['WS_STARTED_BY'] = CUser::FormatName(
						$this->arParams["NAME_TEMPLATE"],
						array(
							'LOGIN' => $row['WS_STARTED_USER_LOGIN'],
							'NAME' => $row['WS_STARTED_USER_NAME'],
							'LAST_NAME' => $row['WS_STARTED_USER_LAST_NAME'],
						),
						true)." [".$row['WS_STARTED_BY']."]";
			}
			$row['DOCUMENT_URL'] = $row['WS_DOCUMENT_NAME'] = '';
			if (!empty($row['WS_MODULE_ID']))
			{
				$row['DOCUMENT_URL'] = CBPDocument::GetDocumentAdminPage(array(
					$row['WS_MODULE_ID'],
					$row['WS_ENTITY'],
					$row['WS_DOCUMENT_ID']
				));
				if ($showDocumentName)
				{
					$row['WS_DOCUMENT_NAME'] = CBPDocument::getDocumentName(array(
						$row['WS_MODULE_ID'],
						$row['WS_ENTITY'],
						$row['WS_DOCUMENT_ID']
					));

					if (!$row['WS_DOCUMENT_NAME'])
						$row['WS_DOCUMENT_NAME'] = Loc::getMessage('BPWI_DOCUMENT_NAME');

				}
			}

			$rowActions = array();
			if ($row['DOCUMENT_URL'])
			{
				$rowActions[] = array(
					"ICONCLASS" => "edit",
					"DEFAULT" => true,
					"TEXT" => Loc::getMessage("BPWI_OPEN_DOCUMENT"),
					"ONCLICK" => "window.open('".$row["DOCUMENT_URL"]."');"
				);
			}

			if ($this->isAdmin())
				$rowActions[] = array(
					"ICONCLASS"=>"delete",
					"TEXT"=>Loc::getMessage("BPWI_DELETE_LABEL"),
					"ONCLICK" => "bxGrid_".static::GRID_ID.".DeleteItem('".$row['ID']."', '".Loc::getMessage("BPWI_DELETE_CONFIRM")."')"
				);

			$this->arResult['RECORDS'][] = array('data' => $row, 'editable' => $this->isAdmin(), 'actions' => $rowActions);
		}

		$this->arResult['ROWS_COUNT'] = sizeof($this->arResult['RECORDS']);
		$this->arResult['GRID_ID'] = static::GRID_ID;
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
			$message = Loc::getMessage('BPWI_MODULE_'.strtoupper($moduleId));
			static::$moduleNames[$moduleId] = $message? $message : $moduleId;
		}
		return static::$moduleNames[$moduleId];
	}
}