<?
/** @global CUser $USER */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arParams['REPORT_ID'] = isset($arParams['REPORT_ID']) ? (int)$arParams['REPORT_ID'] : 0;

$requiredModules = array('report');

foreach ($requiredModules as $requiredModule)
{
	if (!CModule::IncludeModule($requiredModule))
	{
		ShowError(GetMessage("F_NO_MODULE"));
		return 0;
	}
}

if (!isset($arParams['REPORT_HELPER_CLASS'])
	|| mb_strlen($arParams['REPORT_HELPER_CLASS']) < 1
	|| !class_exists($arParams['REPORT_HELPER_CLASS'])
	|| !is_subclass_of($arParams['REPORT_HELPER_CLASS'], 'CReportHelper'))
{
	ShowError(GetMessage("REPORT_HELPER_NOT_DEFINED"));
	return 0;
}

use Bitrix\Main\Entity;

// <editor-fold defaultstate="collapsed" desc="calc variations">
$calcVariations =
$arResult['calcVariations'] = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getCalcVariations'));
// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="filter compare variations">
$compareVariations =
$arResult['compareVariations'] = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getCompareVariations'));
// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="period types">
$periodTypes =
$arResult['periodTypes'] = array(
	'month',
	'month_ago',
	'week',
	'week_ago',
	'days',
	'after',
	'before',
	'interval',
	'all'
);
// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="chart types">
if ($arParams['USE_CHART'])
{
	$arResult['chartTypes'] = array(
		array('id' => 'line', 'name' => GetMessage('REPORT_CHART_TYPE_LINE1'), 'value_types' => array(
			/*'boolean', 'date', 'datetime', */
			'float', 'integer'/*, 'string', 'text', 'enum', 'file', 'disk_file', 'employee', 'crm', 'crm_status',
			'iblock_element', 'iblock_section', 'money'*/
		)),
		array('id' => 'bar', 'name' => GetMessage('REPORT_CHART_TYPE_BAR1'), 'value_types' => array(
			/*'boolean', 'date', 'datetime', */
			'float', 'integer'/*, 'string', 'text', 'enum', 'file', 'disk_file', 'employee', 'crm', 'crm_status',
			'iblock_element', 'iblock_section', 'money'*/
		)),
		array('id' => 'pie', 'name' => GetMessage('REPORT_CHART_TYPE_PIE'), 'value_types' => array(
			/*'boolean', 'date', 'datetime', */
			'float', 'integer'/*, 'string', 'text', 'enum', 'file', 'disk_file', 'employee', 'crm', 'crm_status',
			'iblock_element', 'iblock_section', 'money'*/
		))
	);
}
// </editor-fold>

$fieldList = array();

try
{
	$userId = $USER->GetID();

	// <editor-fold defaultstate="collapsed" desc="common initiazlize">
	$ownerId = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getOwnerId'));
	$entityName = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getEntityName'));
	$entityFields = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getColumnList'));
	// customize entity
	$initEntity = clone Entity\Base::getInstance($entityName);
	call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'setRuntimeFields'), $initEntity, '');

	$chains = CReport::generateChains($entityFields, $initEntity, '');

	$arResult['fieldsTree'] = CReport::generateColumnTree($chains, $initEntity, $arParams['REPORT_HELPER_CLASS']);

	$fieldList = CReport::getUniqueFieldsByTree($arResult['fieldsTree']);

	$bGroupingMode = false;

	// custom columns types
	$customColumnTypes = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getCustomColumnTypes'));
	if (!is_array($customColumnTypes))
		$customColumnTypes = array();
	$arResult['customColumnTypes'] = $customColumnTypes;
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="validation">
	if ($arParams['ACTION'] == 'edit' || $arParams['ACTION'] == 'copy' || $arParams['ACTION'] == 'delete')
	{
		$result = Bitrix\Report\ReportTable::getById($arParams['REPORT_ID']);
		$report = $result->fetch();

		if (empty($report))
		{
			throw new BXUserException(sprintf(GetMessage('REPORT_NOT_FOUND'), $arParams['REPORT_ID']));
		}

		$rightsManager = new Bitrix\Report\RightsManager($userId);
		if(!$rightsManager->canRead($report['ID']))
			throw new BXUserException(GetMessage('REPORT_VIEW_PERMISSION_DENIED'));

		if ($arParams['ACTION'] === 'edit')
		{
			if(!$rightsManager->canEdit($report['ID']) || isset($report['MARK_DEFAULT']) &&
				intval($report['MARK_DEFAULT']) > 0)
				throw new BXUserException(GetMessage('REPORT_DEFAULT_CAN_NOT_BE_EDITED'));
		}

		if($arParams['ACTION'] === 'delete' && !$rightsManager->canDelete($report['ID']))
		{
			throw new BXUserException(GetMessage('REPORT_DEFAULT_CAN_NOT_BE_DELETED'));
		}

		$arResult['report'] = $report;
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="sharing initiazlize">
	if($arParams['ACTION'] == 'edit' || $arParams['ACTION'] == 'create' || $arParams['ACTION'] == 'copy')
	{
		$selected = array();
		$reportId = intval($arParams['REPORT_ID']);
		$entitySharing = Bitrix\Report\Sharing::getEntityOfSharing($reportId);
		$entityList = array();
		foreach($entitySharing as $entity)
		{
			list($type, $id) = Bitrix\Report\Sharing::parseEntityValue($entity['ENTITY']);
			$typeData = Bitrix\Report\Sharing::getTypeData($type, $id);
			$entityList[] = array(
				'entityId' => $entity['ENTITY'],
				'name' => $typeData['name'],
				'right' => $entity['RIGHTS'],
				'avatar' => $typeData['avatar'],
				'type' => $type,
			);
		}
		if($arParams['ACTION'] == 'copy')
			$entityList = array();
		foreach($entityList as $entity)
			$selected[] = $entity['entityId'];
		$destination = Bitrix\Report\Sharing::getSocNetDestination($userId, $selected);
		$arResult['SHARING_DATA'] = array(
			'members' => $entityList,
			'maxAccess' => Bitrix\Report\RightsManager::ACCESS_READ,
			'destination' => array(
				'items' => array(
					'users' => $destination['USERS'],
					'groups' => array(),
					'sonetgroups' => $destination['SONETGROUPS'],
					'department' => $destination['DEPARTMENT'],
					'departmentRelation' => $destination['DEPARTMENT_RELATION'],
				),
				'itemsLast' => array(
					'users' => $destination['LAST']['USERS'],
					'groups' => array(),
					'sonetgroups' => $destination['LAST']['SONETGROUPS'],
					'department' => $destination['LAST']['DEPARTMENT'],
				),
				'itemsSelected' => $destination['SELECTED']
			)
		);
	}
	// </editor-fold>

	if($isPost && isset($_POST['EXPORT_REPORT']))
	{
		if (!check_bitrix_sessid())
		{
			throw new BXFormException(GetMessage('REPORT_CSRF'));
		}
		$reportId = intval($_POST['EXPORT_REPORT']);
		$rightsmanager = new Bitrix\Report\RightsManager($USER->GetID());
		if(!$rightsmanager->canRead($reportId))
		{
			$_SESSION['REPORT_LIST_ERROR'] = GetMessage('REPORT_ERROR_ACCESS_DENIED');
			LocalRedirect($arParams['PATH_TO_REPORT_LIST']);
		}

		$queryObject = Bitrix\Report\ReportTable::getById($reportId);
		if($report = $queryObject->fetch())
		{
			unset($report['ID']);
			unset($report['CREATED_BY']);
			unset($report['CREATED_DATE']);
			unset($report['MARK_DEFAULT']);
			$arResult['REPORT'] = $report;
		}

		$APPLICATION->RestartBuffer();

		Header('Content-Type: text/csv');
		Header('Content-Disposition: attachment;filename=report'.$reportId.'.csv');

		$this->IncludeComponentTemplate('csv');
		die();
	}

	if (!empty($_POST) && (!empty($_POST['report_select_columns']) || $arParams['ACTION'] == 'delete_confirmed'))
	{
		// <editor-fold defaultstate="collapsed" desc="POST action">

		$formErr = '';

		// check csrf
		if (!check_bitrix_sessid('csrf_token'))
		{
			throw new BXFormException(GetMessage('REPORT_CSRF'));
		}

		// delete
		if (empty($formErr) && $arParams['ACTION'] == 'delete_confirmed')
		{
			CReport::Delete($arParams['REPORT_ID']);

			$url = CComponentEngine::MakePathFromTemplate(
				$arParams["PATH_TO_REPORT_LIST"], array()
			);

			LocalRedirect($url);
			exit;
		}

		// <editor-fold defaultstate="collapsed" desc="prepare title">
		$title = !empty($_POST['report_title']) ? trim((string) $_POST['report_title']) : '';
		if (empty($title))
		{
			$formErr = GetMessage('REPORT_TITLE_NOT_SELECTED');
		}

		$description = !empty($_POST['report_description']) ? trim((string) $_POST['report_description']) : '';
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="preapre period">
		$period = [];
		if (!empty($_POST['F_DATE_TYPE']) && in_array($_POST['F_DATE_TYPE'], $periodTypes, true))
		{
			$period = array('type' => $_POST['F_DATE_TYPE']);

			switch ($_POST['F_DATE_TYPE'])
			{
				case 'days':
					$days = !empty($_POST['F_DATE_DAYS']) ? (int) $_POST['F_DATE_DAYS'] : 1;
					$period['value'] = $days ? $days : 1;
					break;

				case 'after':
					$date = !empty($_POST['F_DATE_TO']) ? (string) $_POST['F_DATE_TO'] : ConvertTimeStamp(false, 'SHORT');
					$date = MakeTimeStamp($date);
					$period['value'] = $date ? $date : time();
					break;

				case 'before':
					$date = !empty($_POST['F_DATE_FROM']) ? (string) $_POST['F_DATE_FROM'] : ConvertTimeStamp(false, 'SHORT');
					$date = MakeTimeStamp($date);
					$period['value'] = $date ? $date + (3600*24-1) : time() + (3600*24-1);
					break;

				case 'interval':
					$date_f = !empty($_POST['F_DATE_FROM']) ? (string) $_POST['F_DATE_FROM'] : ConvertTimeStamp(false, 'SHORT');
					$date_f = MakeTimeStamp($date_f);
					$date_t = !empty($_POST['F_DATE_TO']) ? (string) $_POST['F_DATE_TO'] : ConvertTimeStamp(false, 'SHORT');
					$date_t = MakeTimeStamp($date_t);
					if ($date_f || $date_t)
					{
						$period['value'][0] = $date_f ? $date_f : time();
						$period['value'][1] = $date_t ? $date_t + (3600*24-1) : time() + (3600*24-1);
					}
					break;

				default:
					$period['value'] = null;
			}
		}
		else
		{
			$period = array('type' => 'month', 'value' => null);
		}
		if (isset($_POST['period_hidden']))
		{
			$period['hidden'] = ($_POST['period_hidden'] === 'Y' ? 'Y' : 'N');
		}
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="prepare select fields">
		$select = array();

		foreach ($_POST['report_select_columns'] as $k => $v)
		{
			if (!is_numeric($k))
			{
				// probably it's example of column (%s)
				continue;
			}

			$row = array();

			// valid field definition
			if (array_key_exists($v['name'], $fieldList))
			{
				/** @var Entity\Field $field */
				$field = $fieldList[$v['name']];

				// save definition
				$row['name'] = $v['name'];

				// save alias
				if (!empty($v['alias']))
				{
					$row['alias'] = $v['alias'];
				}

				// save aggregation
				if (!empty($v['calc']))
				{
					if (array_key_exists($v['name'], $calcVariations))
					{
						$calcVars = $calcVariations[$v['name']];
					}
					else
					{
						$calcVars = $calcVariations[call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getFieldDataType'), $field)];
					}

					if (in_array($v['calc'], $calcVars, true))
					{
						$row['aggr'] = $v['calc'];
					}
				}

				// save prcnt
				if($v['prcnt'] <> '')
				{
					if($v['prcnt'] == 'self_column' || array_key_exists($v['prcnt'], $_POST['report_select_columns']))
					{
						$row['prcnt'] = $v['prcnt'];
					}
				}

				// save grouping
				if (isset($v['grouping']))
				{
					$bGroupingMode = true;
					$row['grouping'] = true;
				}
				if (isset($v['grouping_subtotal'])) $row['grouping_subtotal'] = true;

				$select[$k] = $row;
			}
		}

		if (empty($select))
		{
			$formErr = GetMessage('REPORT_NO_COLUMN_SELECTED');
		}
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="prepare sorting">
		$sortFieldKey = (int) $_POST['reports_sort_select'];

		$sortType = 'ASC';
		if (array_key_exists('reports_sort_type_select', $_POST) && $_POST['reports_sort_type_select'] == 'DESC')
		{
			$sortType = 'DESC';
		}
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="prepare filters">
		$filter = array();

		// step 1. validation and normalize format, remove failed filters and fields
		foreach ($_POST['filters'] as $fId => $filterInfo)
		{
			// validate logic
			if (
				!array_key_exists('logic', $filterInfo)
				|| ($filterInfo['logic'] !== 'AND' && $filterInfo['logic'] !== 'OR')
			)
			{
				continue;
			}

			$iFilterItems = array();

			foreach ($filterInfo as $key => $subFilter)
			{
				// collect fields and subfilters
				if ($key === 'logic')
				{
					continue;
				}

				if ($subFilter['type'] == 'field')
				{
					// validate field and calc
					if (
						array_key_exists($subFilter['name'], $fieldList)
						&& CReport::isValidFilterCompareVariation(
								$subFilter['name'],
								call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getFieldDataType'), $fieldList[$subFilter['name']]),
								$subFilter['compare'],
								$compareVariations
							)
					)
					{
						$iFilterItems[] = $subFilter;
					}
				}
				else if ($subFilter['type'] == 'filter')
				{
					// hold link to another filter
					$iFilterItems[] = $subFilter;
				}
			}

			if (!empty($iFilterItems))
			{
				$iFilterItems['LOGIC'] = $filterInfo['logic'];
				$filter[$fId] = $iFilterItems;
			}
		}

		// step 2. replace (link) sub-filters if it still exists
		$filter = array_reverse($filter, true);  // start from end

		foreach ($filter as $fId => &$filterInfo)
		{
			foreach ($filterInfo as $key => $subFilter)
			{
				if (is_array($subFilter) && $subFilter['type'] == 'filter')
				{
					$sfId = $subFilter['name'];

					if (array_key_exists($sfId, $filter))
					{
						//$filterInfo[$key] = &$filter[$sfId];
					}
					else
					{
						unset($filterInfo[$key]);
					}
				}
			}

			// remove empty filters
			if (count($filterInfo) == 1 && array_key_exists('LOGIC', $filterInfo))
			{
				unset($filter[$fId]);
			}
		}

		$filter = array_reverse($filter, true);  // restore order

		$iFilter = $filter;
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="prepare limit">
		$limit = !empty($_POST['report_filter_limit']) && is_numeric($_POST['report_filter_limit'])
			? $_POST['report_filter_limit']
			: null;
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="prepare red negative values">
		$redNegativeValues = ($_POST['report_red_neg_vals'] === 'on') ? true : false;
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="prepare helper specific settings">
		// use columns selection of price types
		if ($_POST['helper_spec_ucspt'] === 'on')
		{
			$helperSpecSettings = array('ucspt' => true);
		}
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="prepare chart settings">
		if ($arParams['USE_CHART'])
		{
			if (!empty($_REQUEST['display_chart']))
			{
				$chart = array(
					'display' => true
				);
				$chartTypeIds = array();
				foreach ($arResult['chartTypes'] as $chartTypeInfo) $chartTypeIds[] = $chartTypeInfo['id'];
				if (isset($_REQUEST['chart_type']) && in_array($_REQUEST['chart_type'], $chartTypeIds))
				{
					$chart['type'] = $_REQUEST['chart_type'];
					if (isset($_REQUEST['chart_x']) && isset($_REQUEST['chart_y']) && is_array($_REQUEST['chart_y']))
					{
						$chart['x_column'] = intval($_REQUEST['chart_x']);
						foreach ($_REQUEST['chart_y'] as $k => $v)
						{
							if (is_numeric($k)) $chart['y_columns'][intval($k)] =  intval($v);
						}
					}
				}
			}
		}
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="prepare mobile settings">
		$mobile = null;
		if ($_POST['report_mobile_enabled'] === 'on')
		{
			$mobile = array('enabled' => true);
		}
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="prepare sharing data">
		$sharingData = array();
		if(!empty($_POST['sharing_entity']) && is_array($_POST['sharing_entity']))
		{
			foreach($_POST['sharing_entity'] as $entityId => $entity)
			{
				$sharingData[$entityId]['right'] = $entity;
			}
		}
		// </editor-fold>

		// combine
		$reportSettings = array(
			'title'  => $title,
			'description' => $description,
			'owner'  => $ownerId,
			'entity' => $entityName,
			'period' => $period,
			'select' => $select,
			'filter' => $iFilter,
			'sort'   => $sortFieldKey,
			'sort_type' => $sortType,
			'limit'  => $limit,
			'red_neg_vals' => $redNegativeValues,
			'grouping_mode' => $bGroupingMode
		);
		if (isset($helperSpecSettings)) $reportSettings['helper_spec'] = $helperSpecSettings;
		if ($arParams['USE_CHART']) $reportSettings['chart'] = $chart;
		if (is_array($mobile) && count($mobile) > 0) $reportSettings['mobile'] = $mobile;

		if (!empty($formErr))
		{
			throw new BXFormException($formErr);
		}

		// save
		$ID = false;
		if ($arParams['ACTION'] == 'create' || $arParams['ACTION'] == 'copy')
		{
			$ID = CReport::Add($reportSettings);
		}
		else if ($arParams['ACTION'] == 'edit')
		{
			$ID = $arParams['REPORT_ID'];
			CReport::Update($ID, $reportSettings);
		}

		if($ID)
		{
			$sharing = new Bitrix\Report\Sharing($ID);
			$sharing->changeSharing($sharingData);
		}

		$url = CComponentEngine::MakePathFromTemplate(
			$arParams["PATH_TO_REPORT_VIEW"],
			array('report_id' => $ID)
		);

		LocalRedirect($url);
		exit;
		// </editor-fold>
	}
	else
	{
		// <editor-fold defaultstate="collapsed" desc="initialize default values">
		if ($arParams['ACTION'] == 'edit' || $arParams['ACTION'] == 'copy')
		{
			$settings = unserialize($arResult['report']['SETTINGS'], ['allowed_classes' => false]);

			call_user_func_array(
				array($arParams['REPORT_HELPER_CLASS'], 'fillFilterUFColumns'),
				array(&$settings['filter'], &$fieldList)
			);
			call_user_func_array(
				array($arParams['REPORT_HELPER_CLASS'], 'fillFilterReferenceColumns'),
				array(&$settings['filter'], &$fieldList)
			);

			$arResult['preSettings'] = $settings;

			// change title
			if ($arParams['ACTION'] == 'copy')
			{
				$arResult['report']['TITLE'] .= ' ('.GetMessage('REPORT_TITLE_COPY').')';
			}
		}
		else
		{
			// default preset for new report
			$arResult['preSettings'] = array(
				'select' => call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getDefaultColumns'))
			);
		}
		// </editor-fold>

		$arResult['ufInfo'] = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getUFInfo'));
	}
}
catch (Exception $e)
{
	//throw $e;

	if ($e instanceof BXFormException)
	{
		$arResult['FORM_ERROR'] = $e->getMessage();

		// refill form
		$arResult['report']['TITLE'] = $reportSettings['title'];
		unset($reportSettings['title']);

		call_user_func_array(
			array($arParams['REPORT_HELPER_CLASS'], 'fillFilterReferenceColumns'),
			array(&$reportSettings['filter'], &$fieldList)
		);

		$arResult['preSettings'] = $reportSettings;
	}
	else
	{
		$arResult['ERROR'] = $e->getMessage();
	}
	/*else if ($e instanceof BXUserException)
	{
		$arResult['ERROR'] = $e->getMessage();
	}
	else
	{
		$arResult['ERROR'] = GetMessage('REPORT_UNKNOWN_ERROR');
	}*/
}

$arResult['randomString'] = $this->randString();

$this->IncludeComponentTemplate();


// <editor-fold defaultstate="collapsed" desc="tree structure description">
// format fields parameters to chains and build fields tree
//array(
//	array($originalDefinition, $elem, $subTree),
//	array(str, scalar, null)
//	array(str, entity, array(
//		array(str, scalar, null)
//		array(str, scalar, null)
//	))
//)
// </editor-fold>
