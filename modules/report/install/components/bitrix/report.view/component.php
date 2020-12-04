<?
/** @global CUser $USER */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

$arParams['REPORT_ID'] = isset($arParams['REPORT_ID']) ? (int)$arParams['REPORT_ID'] : 0;

$isStExportEnabled = (is_array($arParams['~STEXPORT_PARAMS']));
$isStExport = false;
$stExportOptions = array();
if (is_array($arParams['~STEXPORT_OPTIONS'])
	&& isset($arParams['~STEXPORT_OPTIONS']['STEXPORT_MODE'])
	&& $arParams['~STEXPORT_OPTIONS']['STEXPORT_MODE'] === 'Y')
{
	$isStExportEnabled = $isStExport = true;
	$stOptionsList = array(
		'STEXPORT_TYPE' => 'exel',
		'STEXPORT_TOTAL_ITEMS' => 0,
		'STEXPORT_PAGE_SIZE' => 100,
		'STEXPORT_PAGE_NUMBER' => 1
	);
	foreach ($stOptionsList as $optionName => $defValue)
	{
		if (array_key_exists($optionName, $arParams['~STEXPORT_OPTIONS']))
		{
			$stExportOptions[$optionName] = $arParams['~STEXPORT_OPTIONS'][$optionName];
		}
		else
		{
			$stExportOptions[$optionName] = $defValue;
		}
	}
	unset($stOptionsList, $optionName, $defValue);
}

if ($isStExportEnabled && $isStExport && is_array($arParams['~URI_PARAMS']))
{
	$uriParams = &$arParams['~URI_PARAMS'];
	$_GET = &$uriParams;
}
else
{
	$uriParams = &$_GET;
}

$requiredModules = array();
if (is_array($arParams['REQUIRED_MODULES']) && !empty($arParams['REQUIRED_MODULES']))
{
	$requiredModules = $arParams['REQUIRED_MODULES'];
}
if (!in_array('report', $requiredModules, true))
{
	$requiredModules[] = 'report';
}

foreach ($requiredModules as $requiredModule)
{
	if (!CModule::IncludeModule($requiredModule))
	{
		$errorMessage = GetMessage("F_NO_MODULE");
		if ($isStExport)
		{
			return array('ERROR' => $errorMessage);
		}
		else
		{
			ShowError($errorMessage);
			return 0;
		}
	}
}

if (!isset($arParams['REPORT_HELPER_CLASS'])
	|| mb_strlen($arParams['REPORT_HELPER_CLASS']) < 1
	|| !class_exists($arParams['REPORT_HELPER_CLASS'])
	|| !is_subclass_of($arParams['REPORT_HELPER_CLASS'], 'CReportHelper'))
{
	$errorMessage = GetMessage("REPORT_HELPER_NOT_DEFINED");
	if ($isStExport)
	{
		return array('ERROR' => $errorMessage);
	}
	else
	{
		ShowError($errorMessage);
		return 0;
	}
}

// Suppress the timezone, while report works in server time
CTimeZone::Disable();

use Bitrix\Main\Entity;

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
		array('id' => 'line', 'name' => GetMessage('REPORT_CHART_TYPE_LINE'), 'value_types' => array(
			/*'boolean', 'date', 'datetime', */
			'float', 'integer'/*, 'string', 'text', 'enum', 'file', 'disk_file', 'employee', 'crm', 'crm_status',
			'iblock_element', 'iblock_section', 'money'*/
		)),
		array('id' => 'bar', 'name' => GetMessage('REPORT_CHART_TYPE_BAR'), 'value_types' => array(
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

// get view params
if (!$isStExport)
{
	$strReportViewParams = CReport::getViewParams($arParams['REPORT_ID'], $this->GetTemplateName());
	if (isset($uriParams['set_filter']))
	{
		if (mb_substr($_SERVER['QUERY_STRING'], 0, 6) !== 'EXCEL=' || array_key_exists("publicSidePanel", $_REQUEST))
		{
			if ($_SERVER['QUERY_STRING'] !== $strReportViewParams)
			{
				CReport::setViewParams($arParams['REPORT_ID'], $this->GetTemplateName(), $_SERVER['QUERY_STRING']);
			}
		}
	}
	else
	{
		if (!empty($strReportViewParams))
		{
			if (!is_set($uriParams['sort_id']) && !array_key_exists("publicSidePanel", $_REQUEST))
			{
				$len = mb_strpos($arParams['PATH_TO_REPORT_VIEW'], '?');

				if ($len === false) $redirectUrl = $arParams['PATH_TO_REPORT_VIEW'];
				else $redirectUrl = mb_substr($arParams['PATH_TO_REPORT_VIEW'], 0, $len);
				$redirectUrl = CComponentEngine::makePathFromTemplate(
					$redirectUrl,
					array('report_id' => $arParams['REPORT_ID'])
				);
				$redirectUrl .= '?'.$strReportViewParams;
				LocalRedirect($redirectUrl);
			}
			else
			{
				CReport::clearViewParams($arParams['REPORT_ID']);
			}
		}
	}
}

$usedUFMap = array();

try
{
	// select report info/settings
	$report = array();
	$result = false;
	if (intval($arParams['REPORT_ID']) > 0)
	{
		$result = Bitrix\Report\ReportTable::getById($arParams['REPORT_ID']);
	}
	if (is_object($result))
	{
		$report = $result->fetch();
	}

	if (empty($report))
	{
		throw new BXUserException(sprintf(GetMessage('REPORT_NOT_FOUND'), $arParams['REPORT_ID']));
	}

	$userId = $USER->GetID();

	$rightsManager = new Bitrix\Report\RightsManager($userId);
	if(!$rightsManager->canRead($report['ID']))
		throw new BXUserException(GetMessage('REPORT_VIEW_PERMISSION_DENIED'));

	$arResult['AUTHOR'] = true;
	if($userId != $report['CREATED_BY'])
		$arResult['AUTHOR'] = false;

	$arResult['MARK_DEFAULT'] = 0;
	if (isset($report['MARK_DEFAULT']))
	{
		$arResult['MARK_DEFAULT'] = intval($report['MARK_DEFAULT']);
	}
	$arResult['SHOW_EDIT_BUTTON'] = is_bool($arParams['SHOW_EDIT_BUTTON']) ? $arParams['SHOW_EDIT_BUTTON'] : true;

	// action
	$settings = unserialize($report['SETTINGS'], ['allowed_classes' => false]);

	// prevent percent from percent
	$prcntSelect = [];
	foreach ($settings['select'] as $id => $elem)
	{
		if (isset($elem['prcnt']))
		{
			$prcntSelect[$id] = $elem;
		}
	}
	foreach ($prcntSelect as $id => $elem)
	{
		if (isset($prcntSelect[$elem['prcnt']]))
		{
			unset($settings['select'][$id]['prcnt']);
		}
	}
	unset($prcntSelect, $id, $elem);

	// <editor-fold defaultstate="collapsed" desc="parse period">
	$date_from = $date_to = null;
	$form_date = array('from' => null, 'to' => null, 'days' => null);

	// <editor-fold defaultstate="collapsed" desc="get value from POST or DB">
	if (!empty($uriParams['F_DATE_TYPE']) && in_array($uriParams['F_DATE_TYPE'], $periodTypes, true))
	{
		$period = array('type' => $uriParams['F_DATE_TYPE']);

		switch ($uriParams['F_DATE_TYPE'])
		{
			case 'days':
				$days = !empty($uriParams['F_DATE_DAYS']) ? (int) $uriParams['F_DATE_DAYS'] : 1;
				$period['value'] = $days ? $days : 1;
				break;

			case 'after':
				$date = !empty($uriParams['F_DATE_TO']) ?
					(string) $uriParams['F_DATE_TO'] : ConvertTimeStamp(false, 'SHORT');
				$date = MakeTimeStamp($date);
				$period['value'] = $date ? $date : time();
				break;

			case 'before':
				$date = !empty($uriParams['F_DATE_FROM']) ?
					(string) $uriParams['F_DATE_FROM'] : ConvertTimeStamp(false, 'SHORT');
				$date = MakeTimeStamp($date);
				$period['value'] = $date ? $date + (3600*24-1) : time() + (3600*24-1);
				break;

			case 'interval':
				$date_f = !empty($uriParams['F_DATE_FROM']) ?
					(string) $uriParams['F_DATE_FROM'] : ConvertTimeStamp(false, 'SHORT');
				$date_f = MakeTimeStamp($date_f);
				$date_t = !empty($uriParams['F_DATE_TO']) ?
					(string) $uriParams['F_DATE_TO'] : ConvertTimeStamp(false, 'SHORT');
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
		$period = $settings['period'];
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="parse period">
	switch ($period['type'])
	{
		case 'month':
			$date_from = strtotime(date("Y-m-01"));
			break;

		case 'month_ago':
			$curTime = time();
			$curTimeInfo = getdate($curTime);
			$date_from = strtotime(date("Y-m-01", strtotime('-'.$curTimeInfo['mday'].'day', $curTime)));
			$date_to = strtotime(date("Y-m-t", strtotime('-'.$curTimeInfo['mday'].'day', $curTime))) + (3600*24-1);
			unset($curTime, $curTimeInfo);
			break;

		case 'week':
			$date_from = strtotime("-".((date("w") == 0 ? 7 : date("w")) - 1)." day 00:00");
			break;

		case 'week_ago':
			$date_from = strtotime("-".((date("w") == 0 ? 7 : date("w")) + 6)." day 00:00");
			$date_to = strtotime("-".(date("w") == 0 ? 7 : date("w"))." day 23:59:59");
			break;

		case 'days':
			$date_from = strtotime(date("Y-m-d")." -".intval($period['value'])." day");
			$form_date['days'] = intval($period['value']);
			break;

		case 'after':
			$date_from = $period['value'];
			$form_date['to'] = ConvertTimeStamp($period['value'], 'SHORT');
			break;

		case 'before':
			$date_to = $period['value'];
			$form_date['from'] = ConvertTimeStamp($period['value'], 'SHORT');
			break;

		case 'interval':
			list($date_from, $date_to) = $period['value'];
			$form_date['from'] = ConvertTimeStamp($period['value'][0], 'SHORT');
			$form_date['to'] = ConvertTimeStamp($period['value'][1], 'SHORT');
			break;
	}

	$site_date_from = !is_null($date_from) ? ConvertTimeStamp($date_from, 'FULL') : null;
	$site_date_to = !is_null($date_to) ? ConvertTimeStamp($date_to, 'FULL') : null;

	// to_date for oracle
	// rewrite to CDatabase::CharToDateFunction
	global $DB;

	$db_date_from = !is_null($site_date_from) ? $DB->CharToDateFunction($site_date_from) : null;
	$db_date_to = !is_null($site_date_to) ? $DB->CharToDateFunction($site_date_to) : null;

	// user name format
	if (isset($arParams['USER_NAME_FORMAT'])
		&& is_string($arParams['USER_NAME_FORMAT'])
		&& !empty($arParams['USER_NAME_FORMAT']))
	{
		call_user_func(
			array($arParams['REPORT_HELPER_CLASS'], 'setUserNameFormat'),
			$arParams['USER_NAME_FORMAT']
		);
	}

	// period filter
	$filter = array('LOGIC' => 'AND');
	$period_filter = call_user_func(
		array($arParams['REPORT_HELPER_CLASS'], 'getPeriodFilter'),
		$site_date_from, $site_date_to
	);

	if (!empty($period_filter))
	{
		$filter[] = $period_filter;
	}

	// preiod option
	if (!is_null($date_from) && !is_null($date_to))
	{
		$sqlTimeInterval = "BETWEEN ".$db_date_from." AND ".$db_date_to;
	}
	else if (!is_null($date_from))
	{
		$sqlTimeInterval = ">= ".$db_date_from;
	}
	else if (!is_null($date_to))
	{
		$sqlTimeInterval = "<= ".$db_date_to;
	}
	else
	{
		$sqlTimeInterval = " IS NOT NULL";
	}
	// </editor-fold>

	// </editor-fold>

	$runtime = array();
	$select = array();
	$group = array();
	$order = array();
	$limit = array();

	$options = array(
		'SQL_TIME_INTERVAL' => $sqlTimeInterval
	);

	$excelView = isset($uriParams["EXCEL"]) && $uriParams["EXCEL"] == "Y";

	// <editor-fold defaultstate="collapsed" desc="parse entity">
	$entityName = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getEntityName'));
	$entityFields = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getColumnList'));
	$grcFields = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getGrcColumns'));

	// customize entity
	$entity = clone Entity\Base::getInstance($entityName);
	call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'setRuntimeFields'), $entity, $sqlTimeInterval);

	$chains = CReport::generateChains($entityFields, $entity, '');
	$fieldsTree = CReport::generateColumnTree($chains, $entity, $arParams['REPORT_HELPER_CLASS']);
	unset($chains);

	// custom columns types
	$customColumnTypes = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getCustomColumnTypes'));
	if (!is_array($customColumnTypes))
		$customColumnTypes = array();
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="parse select columns">

	// <editor-fold defaultstate="collapsed" desc="collect fields">
	$fList = array();
	$fChainList = array();
	$bGroupingMode = false;
	foreach ($settings['select'] as $elem)
	{
		if (!$bGroupingMode) if ($elem['grouping'] === true) $bGroupingMode = true;
		$fName = $elem['name'];

		if (array_key_exists($fName, $fList))
		{
			continue;
		}

		try
		{
			$chain = Entity\QueryChain::getChainByDefinition($entity, $fName);
		}
		catch (Exception $e)
		{
			if ($e->getCode() == 100)
			{
				throw new BXUserException(
					'<p style="color: red;">'.GetMessage('REPORT_UNKNOWN_FIELD_DEFINITION').'</p>'
				);
			}
			else
			{
				throw $e;
			}
		}
		$fList[$fName] = $chain->getLastElement()->getValue();
		if (is_array($fList[$fName])) $fList[$fName] = end($fList[$fName]);
		$fChainList[$fName] = $chain;
	}

	// customize select fields
	$customSelectFields = call_user_func_array(
		array($arParams['REPORT_HELPER_CLASS'], 'getCustomSelectFields'),
		array($settings['select'], $fList)
	);
	if (is_array($customSelectFields) && !empty($customSelectFields))
	{
		$customSelectKeys = array_keys($customSelectFields);
		$newSelect = array();
		foreach ($settings['select'] as $k => $elem)
		{
			if (in_array($k, $customSelectKeys, true))
			{
				$fName = $customSelectFields[$k]['name'];

				if (array_key_exists($fName, $fList))
					continue;

				try
				{
					$chain = Entity\QueryChain::getChainByDefinition($entity, $fName);
				}
				catch (Exception $e)
				{
					if ($e->getCode() == 100)
					{
						throw new BXUserException(
							'<p style="color: red;">'.GetMessage('REPORT_UNKNOWN_FIELD_DEFINITION').'</p>'
						);
					}
					else
					{
						throw $e;
					}
				}
				$fList[$fName] = $chain->getLastElement()->getValue();
				if (is_array($fList[$fName])) $fList[$fName] = end($fList[$fName]);
				$fChainList[$fName] = $chain;
				$newSelect[$k] = $customSelectFields[$k];
			}
			else
			{
				$newSelect[$k] = $elem;
			}
		}
		$settings['select'] = $newSelect;
		unset($customSelectKeys, $newSelect);
	}
	unset($customSelectFields);

	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="collect hrefs' fields">
	//$settings['select'][0]['href'] = array(
	//	'pattern' => '/company/personal/user/#RESPONSIBLE_ID#/tasks/task/view/#ID#/', //'/tasks/#ID#/',
	//	/*'elements' => array(  // not required
	//		'ID' => array(
	//			'name' => 'ID',
	//			'aggr' => null
	//		)
	//	)*/
	//);

	foreach ($settings['select'] as &$elem)
	{
		//if (in_array($elem['name'], $grcFields, true) && empty($elem['aggr']))
		if ($elem['aggr'] == 'GROUP_CONCAT')
		{
			continue;
		}

		CReport::appendHrefSelectElements($elem, $fList, $entity, $arParams['REPORT_HELPER_CLASS'], $select, $runtime);
	}
	unset($elem);
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="collect columns with aliases, build runtime fields">

	// if there is aggr of init entity or there is no init entity at all, then we think that 1:N need double aggregation
	$is_init_entity_aggregated = false;
	$is_init_entity_in_select = false;

	foreach ($settings['select'] as $num => $elem)
	{
		$chain = $fChainList[$elem['name']];
		if ($chain->getSize() == 2)
		{
			$is_init_entity_in_select = true;

			if (!empty($elem['aggr']))
			{
				$is_init_entity_aggregated = true;
				break;
			}
		}
	}

	if (!$is_init_entity_aggregated && !$is_init_entity_in_select)
	{
		$is_init_entity_aggregated = true;
	}


	// init variables
	$viewColumns = array();
	$viewColumnsByResultName = array();

	// blacklist of entity with aggr
	$aggr_bl = array();

	// grc stuff
	$grcSelectPrimaries = array();
	$grcInitPrimary = false;

	$need_concat_rows = false;
	$grcSettingsNum = array();

	$customTotals = [];

	foreach ($settings['select'] as $num => $elem)
	{
		/** @var Entity\Field $field */
		$chain = $fChainList[$elem['name']];
		$field = $fList[$elem['name']];
		$fType = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getFieldDataType'), $field);

		$is_grc = false;

		//if (in_array($elem['name'], $grcFields, true) && empty($elem['aggr']) && !strlen($elem['prcnt']))
		if ($elem['aggr'] == 'GROUP_CONCAT')
		{
			$is_grc = true;

			// collect grc_fields pointers
			$need_concat_rows = true;
			$grcSettingsNum[] = $num;
		}

		if (CReport::checkSelectViewElementCyclicDependency($settings['select'], $num))
		{
			throw new BXUserException(GetMessage('REPORT_COLUMNS_HAS_CYCLIC_DEPENDENCY'));
		}

		list($alias, $selElem, $totalInfo) = CReport::prepareSelectViewElement(
			$elem, $settings['select'], $is_init_entity_aggregated, $fList, $fChainList,
			$arParams['REPORT_HELPER_CLASS'], $entity
		);

		if (is_array($totalInfo))
		{
			$customTotals[$alias] = $totalInfo;
		}
		unset($totalInfo);

		if (is_array($selElem) && !empty($selElem['expression']))
		{
			// runtime expr
			$fType = $selElem['data_type'];
		}
		else
		{
			// normal field
			$alias = Entity\QueryChain::getAliasByDefinition($entity, $elem['name']);
		}

		if (!$is_grc)
		{
			// grc will be selected later
			if (is_array($selElem))
			{
				// runtime field
				$select[$alias] = $alias;
				$runtime[$alias] = $selElem;
			}
			else
			{
				$select[$alias] = $selElem;
			}
		}

		$arUF = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'detectUserField'), $field);

		if ($arUF['isUF'] && is_array($arUF['ufInfo'])
			&& isset($arUF['ufInfo']['ENTITY_ID']) && isset($arUF['ufInfo']['FIELD_NAME']))
		{
			$usedUFMap[$arUF['ufInfo']['ENTITY_ID']][$arUF['ufInfo']['FIELD_NAME']] = true;
		}

		// default sort
		if ($is_grc
			|| ((in_array($fType, array('file', 'disk_file', 'employee', 'crm', 'crm_status', 'iblock_element',
						'iblock_section', 'money'), true)
					|| ($arUF['isUF'] && $arUF['ufInfo']['MULTIPLE'] === 'Y'))
				&& empty($elem['aggr'])))
		{
			$defaultSort = '';
		}
		else if ($num == $settings['sort'] && array_key_exists('sort_type', $settings))
		{
			$defaultSort = $settings['sort_type'];
		}
		else if (($fType === 'string' || $fType === 'enum') && empty($elem['aggr']))
		{
			$defaultSort = 'ASC';
		}
		else
		{
			$defaultSort = 'DESC';
		}

		$viewColumns[$num] = array(
			'field' => $field,
			'fieldName' => $elem['name'],
			'resultName' => $alias,
			'humanTitle' => empty($elem['alias']) ? $alias : $elem['alias'],
			'defaultSort' => $defaultSort,
			'aggr' => empty($elem['aggr']) ? '' : $elem['aggr'],
			'prcnt' => $elem['prcnt'] <> ''? $elem['prcnt'] : '',
			'href' => empty($elem['href']) ? '' : $elem['href'],
			'grouping' => ($elem['grouping'] === true) ? true : false,
			'grouping_subtotal' => ($elem['grouping_subtotal'] === true) ? true : false,
			'isUF' => $arUF['isUF'],
			'ufInfo' => $arUF['ufInfo']
		);
		unset($arUF);

		$viewColumnsByResultName[$alias] = &$viewColumns[$num];

		// blacklist of entity with aggr
		//if (!in_array($elem['name'], $grcFields, true) && !empty($elem['aggr']))
		if ($elem['aggr'] != 'GROUP_CONCAT' && !empty($elem['aggr']))
		{
			$preDef = mb_substr($elem['name'], 0, mb_strrpos($elem['name'], '.'));
			$preDef = $preDef <> ''? $preDef.'.' : '';

			$aggr_bl[$preDef] = true;
		}
	}

	// collect entity primaries of fields without aggregation
	foreach ($settings['select'] as $num => $elem)
	{
		//if (!in_array($elem['name'], $grcFields, true) && empty($elem['aggr']))
		if (empty($elem['aggr']))
		{
			$primary = $viewColumns[$num]['field']->getEntity()->getPrimaryArray();

			$preDef = mb_substr($elem['name'], 0, mb_strrpos($elem['name'], '.'));
			$preDef = $preDef <> ''? $preDef.'.' : '';

			if (array_key_exists($preDef, $aggr_bl))
			{
				continue;
			}

			foreach ($primary as $pField)
			{
				$palias = Entity\QueryChain::getAliasByDefinition($entity, $preDef.$pField);
				$grcSelectPrimaries[$palias] = $preDef.$pField;
			}

			// remember if there is initEntity primary in data
			if ($viewColumns[$num]['field']->getEntity() === $entity)
			{
				$grcInitPrimary = true;
			}
		}
	}

	// normalize $grcSelectPrimaries
	if ($grcInitPrimary)
	{
		// it's enough only init primary
		$initPrimary = $entity->getPrimaryArray();

		foreach ($grcSelectPrimaries as $k => $v)
		{
			if (!in_array($v, $initPrimary, true))
			{
				unset($grcSelectPrimaries[$k]);
			}
		}
	}

	$select = array_merge($select, $grcSelectPrimaries);
	// </editor-fold>

	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="parse filter">

	// <editor-fold defaultstate="collapsed" desc="rewrite values by filter">
	foreach ($settings['filter'] as $fId => &$fInfo)
	{
		foreach ($fInfo as $k => &$fElem)
		{
			if (!empty($uriParams['filter'][$fId]) && array_key_exists($k, $uriParams['filter'][$fId]))
			{
				$fElem['value'] = $uriParams['filter'][$fId][$k];
			}
		}
	}
	unset($fInfo);
	unset($fElem);
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="add filter to fList and fChainList">
	foreach ($settings['filter'] as $fId => $fInfo)
	{
		foreach ($fInfo as $k => $fElem)
		{
			if (is_array($fElem) && $fElem['type'] == 'field')
			{
				if (preg_match('/__COLUMN__\d+/', $fElem['name']))
				{
					continue;
				}

				try
				{
					$chain = Entity\QueryChain::getChainByDefinition($entity, $fElem['name']);
				}
				catch (Exception $e)
				{
					if ($e->getCode() == 100)
					{
						throw new BXUserException(
							'<p style="color: red;">'.GetMessage('REPORT_UNKNOWN_FIELD_DEFINITION').'</p>'
						);
					}
					else
					{
						throw $e;
					}
				}
				$field = $chain->getLastElement()->getValue();
				if (is_array($field)) $field = end($field);
				$fList[$fElem['name']] = $field;
				$fChainList[$fElem['name']] = $chain;
			}
		}
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="collect changeables">
	$changeableFilters = [];
	$changeableFiltersEntities = [];
	$filterSettings = $settings['filter'];
	$contextStack = [];

	if (count($filterSettings) > 0 && is_array($filterSettings[0]))
	{
		$contextStack[] = [
			'filter' => $filterSettings[0],
			'id' => 0,
			'index' => 0
		];
	}
	while (count($contextStack) > 0)
	{
		$context = array_pop($contextStack);
		$contextSwitch = false;
		while (!$contextSwitch && isset($context['filter'][$context['index']]))
		{
			$index = $context['index']++;
			$fElem = $context['filter'][$index];
			if (is_array($fElem))
			{
				if ($fElem['type'] == 'field' && (int) $fElem['changeable'] > 0)
				{
					$match = [];
					$arUF = null;
					if (preg_match('/__COLUMN__(\d+)/', $fElem['name'], $match))
					{
						/** @var Entity\Field[] $view */
						$num = $match[1];
						$view = $viewColumns[$num];
						$data_type = call_user_func(
							array($arParams['REPORT_HELPER_CLASS'], 'getFieldDataType'), $view['field']
						);

						if ($view['prcnt'])
						{
							$data_type = 'float';
						}
						else if ($view['aggr'] == 'COUNT_DISTINCT')
						{
							$data_type = 'integer';
						}

						$field = null;
					}
					else
					{
						$field = $fList[$fElem['name']];
						$data_type = call_user_func(
							[
								$arParams['REPORT_HELPER_CLASS'],
								'getFieldDataType'
							],
							$field
						);
					}

					if ($field instanceof Entity\ReferenceField)
					{
						$tmpElem = $fElem;
						call_user_func_array(
							array($arParams['REPORT_HELPER_CLASS'], 'fillFilterReferenceColumn'),
							array(&$tmpElem, &$field)
						);
						$value = $tmpElem['value'];
						$changeableFiltersEntities[$field->getRefEntityName()] = true;
					}
					else
					{
						// detect UF
						$arUF = call_user_func_array(
							array($arParams['REPORT_HELPER_CLASS'], 'detectUserField'),
							array($field)
						);
						if ($arUF['isUF'] && is_array($arUF['ufInfo']) && isset($arUF['ufInfo']['USER_TYPE_ID']))
						{
							$tmpElem = $fElem;
							call_user_func_array(
								array($arParams['REPORT_HELPER_CLASS'], 'fillFilterUFColumn'),
								array(&$tmpElem, $field, $arUF['ufInfo'])
							);
							$value = $tmpElem['value'];
						}
						else
						{
							$value = $fElem['value'];
						}
					}

					// detect UF
					if ($arUF === null)
					{
						$arUF = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'detectUserField'), $field);
					}

					if ($arUF['isUF'] && is_array($arUF['ufInfo'])
						&& isset($arUF['ufInfo']['ENTITY_ID']) && isset($arUF['ufInfo']['FIELD_NAME']))
					{
						$usedUFMap[$arUF['ufInfo']['ENTITY_ID']][$arUF['ufInfo']['FIELD_NAME']] = true;
					}

					$changeableFilters[] = array(
						'name' => $fElem['name'],
						'title' => '', // will be added later
						'value' => $value,
						'compare' => $fElem['compare'],
						'filter' => $context['id'],
						'num' => $index,
						'formName' => 'filter['.$context['id'].']['.$index.']',
						'formId' => 'filter_'.$context['id'].'_'.$index,
						'field' => $field,
						'data_type' => $data_type,
						'isUF' => $arUF['isUF'],
						'ufId' => $arUF['isUF'] ? $arUF['ufInfo']['ENTITY_ID'] : '',
						'ufName' => $arUF['isUF'] ? $arUF['ufInfo']['FIELD_NAME'] : ''
					);

					unset($arUF);
				}
				elseif ($fElem['type'] == 'filter')
				{
					$index = $fElem['name'];
					if (is_array($filterSettings[$index])
						&& count($filterSettings[$index]) > 0
						&& is_array($filterSettings[$index][0]))
					{
						$contextStack[] = $context;
						$contextStack[] = [
							'filter' => $filterSettings[$index],
							'id' => $index,
							'index' => 0
						];
						$contextSwitch = true;
					}
				}
			}
		}
	}
	unset($filterSettings, $contextStack, $fElem);
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="rewrite references to primary">
	foreach ($settings['filter'] as $fId => &$fInfo)
	{
		foreach ($fInfo as $k => &$fElem)
		{
			if (is_array($fElem) && $fElem['type'] == 'field')
			{
				// delete empty filters
				if (is_array($fElem['value']))
				{
					foreach ($fElem['value'] as $l => $value)
					{
						if ($value === '' || !is_numeric($l)) unset($fElem['value'][$l]);
					}
					$l = count($fElem['value']);
					if ($l === 0) $fElem['value'] = '';
					else if ($l === 1) $fElem['value'] = $fElem['value'][0];
				}
				if ($fElem['value'] === '')
				{
					unset($fInfo[$k]);
					continue;
				}

				if (preg_match('/__COLUMN__(\d+)/', $fElem['name'], $match))
				{
					$num = $match[1];
					$field = $viewColumns[$num]['field'];
				}
				else
				{
					$field = $fList[$fElem['name']];
				}

				// rewrite
				if ($field instanceof Entity\ReferenceField)
				{
					// get primary
					$field = $field->GetRefEntity()->getField('ID');

					// get primary filter field name
					$primaryFilterField = call_user_func_array(
						array($arParams['REPORT_HELPER_CLASS'], 'getEntityFilterPrimaryFieldName'),
						array($fElem)
					);
					$fElem['name'] .= '.'.$primaryFilterField;
					unset($primaryFilterField);

					$fList[$fElem['name']] = $field;
					$fChainList[$fElem['name']] = Entity\QueryChain::getChainByDefinition($entity, $fElem['name']);
				}

				$dataType = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getFieldDataType'), $field);

				// rewrite date <=> {today, yesterday, tomorrow, etc}
				if ($dataType === 'datetime'
					&& !CheckDateTime($fElem['value'], CSite::GetDateFormat('SHORT'))
				)
				{
					$fElem['value'] = ConvertTimeStamp(strtotime($fElem['value']), 'SHORT');

					// ignore datetime filter with incorrect value
					if (!CheckDateTime($fElem['value'], CSite::GetDateFormat('SHORT')))
					{
						unset($fInfo[$k]);
						continue;
					}
				}

				// rewrite date=DAY to date BETWEEN DAY_START AND DAY_END
				if ($dataType === 'datetime')
				{
					if ($fElem['compare'] == 'EQUAL')
					{
						$dtValue = new Bitrix\Main\Type\DateTime($fElem['value']);
						$dtValue->setTime(23, 59, 59);
						$fElem['compare'] = 'BETWEEN';
						$fElem['value'] = [
							$fElem['value'] . ' 00:00:00',
							ConvertTimeStamp($dtValue->getTimestamp(), 'FULL')
						];
					}
					else if ($fElem['compare'] == 'NOT_EQUAL')
					{
						$dtValue = new Bitrix\Main\Type\DateTime($fElem['value']);
						$dtValue->setTime(23, 59, 59);
						$fElem['compare'] = 'NOT_BETWEEN';
						$fElem['value'] = [
							$fElem['value'] . ' 00:00:00',
							ConvertTimeStamp($dtValue->getTimestamp(), 'FULL')
						];
					}
					else if ($fElem['compare'] == 'LESS_OR_EQUAL')
					{
						$dtValue = new Bitrix\Main\Type\DateTime($fElem['value']);
						$dtValue->setTime(23, 59, 59);
						$fElem['value'] = ConvertTimeStamp($dtValue->getTimestamp(), 'FULL');
					}
					else if ($fElem['compare'] == 'GREATER_OR_EQUAL')
					{
						$fElem['value'] .= ' 00:00:00';
					}
					else if ($fElem['compare'] == 'LESS')
					{
						$fElem['value'] .= ' 00:00:00';
					}
					else if ($fElem['compare'] == 'GREATER')
					{
						$dtValue = new Bitrix\Main\Type\DateTime($fElem['value']);
						$dtValue->setTime(23, 59, 59);
						$fElem['value'] = ConvertTimeStamp($dtValue->getTimestamp(), 'FULL');
					}
				}
			}
		}
	}
	unset($fInfo);
	unset($fElem);
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="rewrite 1:N relations to EXISTS expression">
	call_user_func_array(
		array($arParams['REPORT_HELPER_CLASS'], 'beforeFilterBackReferenceRewrite'),
		array(&$settings['filter'], $viewColumns)
	);

	$f_filter_alias_count = 0;

	foreach ($settings['filter'] as $fId => &$fInfo)
	{
		foreach ($fInfo as $k => &$fElem)
		{
			if (is_array($fElem) && $fElem['type'] == 'field')
			{
				if (preg_match('/__COLUMN__\d+/', $fElem['name']))
				{
					continue;
				}

				$fField = $fList[$fElem['name']];
				$arUF = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'detectUserField'), $fField);

				if ($arUF['isUF'] && is_array($arUF['ufInfo'])
					&& isset($arUF['ufInfo']['ENTITY_ID']) && isset($arUF['ufInfo']['FIELD_NAME']))
				{
					$usedUFMap[$arUF['ufInfo']['ENTITY_ID']][$arUF['ufInfo']['FIELD_NAME']] = true;
				}

				if ($arUF['isUF'])
				{
					$fFieldDataType = call_user_func(
						array($arParams['REPORT_HELPER_CLASS'], 'getUserFieldDataType'), $arUF
					);
					if ($fFieldDataType === 'boolean')
					{
						if ($fElem['value'] === 'true')
							$fElem['value'] = 1;
						else
							$fElem['value'] = 0;
					}
				}

				$chain = $fChainList[$fElem['name']];

				if ($chain->hasBackReference())
				{
					$confirm = call_user_func_array(
						array($arParams['REPORT_HELPER_CLASS'], 'confirmFilterBackReferenceRewrite'),
						array(&$fElem, $chain)
					);

					if (!$confirm)
					{
						continue;
					}

					$_sub_init_table_alias = ToLower($entity->getCode());

					$subFilter = array();

					// add primary linking with main query
					foreach ($entity->GetPrimaryArray() as $_primary)
					{
						$subFilter['='.$_primary] =
							new CSQLWhereExpression('?#', $_sub_init_table_alias.'.'.$_primary);
					}

					// add value filter
					$filterCompare = CReport::$iBlockCompareVariations[$fElem['compare']];
					$filterName = $fElem['name'];
					$filterValue = $fElem['value'];
					$isNegativeCondition = ($filterCompare === '!' || $filterCompare === '!%');
					$subQueryAdv = null;

					// <editor-fold defaultstate="collapsed" desc="build subquery">
					$subQueryAdv = null;
					if ($isNegativeCondition)
					{
						$subFilterAdv = $subFilter;
						$subFilterAdv['!'.$filterName] = false;
						$subQueryAdv = new Entity\Query($entity);
						$subQueryAdv->setFilter($subFilterAdv);
						unset($subFilterAdv);
						$subQueryAdv->setLimit(1);
						$subQueryAdv->setTableAliasPostfix('_subex');
					}
					if ($filterCompare === '>%')
					{
						$filterCompare = '';
						$filterValue = $filterValue.'%';
					}
					$subFilter[$filterCompare.$filterName] = $filterValue;

					$subQuery = new Entity\Query($entity);
					$subQuery->setFilter($subFilter);
					$subQuery->setTableAliasPostfix('_sub');

					if ($isNegativeCondition)
					{
						$subSql = 'EXISTS('.$subQuery->getQuery().') OR NOT EXISTS('.$subQueryAdv->getQuery().')';
					}
					else
					{
						$subSql = 'EXISTS('.$subQuery->getQuery().')';
					}
					unset($subQueryAdv);

					$subSql = '(CASE WHEN '.$subSql.' THEN 1 ELSE 0 END)';
					// </editor-fold>

					// expression escaping as sprintf requires
					$subSql = str_replace('%', '%%', $subSql);

					$runtimeField = array(
						'data_type' => 'integer',
						'expression' => array($subSql)
					);

					$f_filter_alias = 'F_FILTER_ALIAS_'.(++$f_filter_alias_count);

					$runtime[$f_filter_alias] = $runtimeField;
					$fElem['name'] = $f_filter_alias;
					$fElem['compare'] = 'EQUAL';
					$fElem['value'] = 1;
				}
			}
		}
	}
	unset($fInfo, $fElem, $runtimeField);
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="rewrite __COLUMN__\d filters">
	foreach ($settings['filter'] as $fId => &$fInfo)
	{
		foreach ($fInfo as $k => &$fElem)
		{
			if (is_array($fElem) && $fElem['type'] == 'field')
			{
				if (preg_match('/__COLUMN__(\d+)/', $fElem['name'], $match))
				{
					$num = $match[1];
					$view = $viewColumns[$num];

					if (!empty($view['prcnt']) || !empty($view['aggr']))
					{
						$fElem['name'] = $view['resultName'];
					}
					else
					{
						$fElem['name'] = $view['fieldName'];
					}
				}
			}
		}
	}
	// </editor-fold>

	$iFilter = CReport::makeSingleFilter($settings['filter']);
	$filter[] = $iFilter;
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="parse sort">

	$sort_id = isset($settings['sort']) ? $settings['sort'] : -1;
	$sort_name = $sort_type = '';
	if (is_array($viewColumns[$sort_id])
		&& isset($viewColumns[$sort_id]['defaultSort'])
		&& $viewColumns[$sort_id]['defaultSort'] !== ''
		&& isset($viewColumns[$sort_id]['resultName']))
	{
		$sort_type = $viewColumns[$sort_id]['defaultSort'];
		$sort_name = $viewColumns[$sort_id]['resultName'];
	}

	// rewrite sort by POST
	if (array_key_exists('sort_id', $uriParams))
	{
		$sort_id = $uriParams['sort_id'];
		if (is_array($viewColumns[$sort_id])
			&& isset($viewColumns[$sort_id]['defaultSort'])
			&& $viewColumns[$sort_id]['defaultSort'] !== ''
			&& isset($viewColumns[$sort_id]['resultName']))
		{
			if (isset($uriParams['sort_type'])
				&& ($uriParams['sort_type'] === 'ASC'
					|| $uriParams['sort_type'] === 'DESC'))
			{
				$sort_type = $uriParams['sort_type'];
			}
			else
			{
				$sort_type = $viewColumns[$sort_id]['defaultSort'];
			}

			$sort_name = $viewColumns[$sort_id]['resultName'];
		}
	}

	if ($sort_name != '' && ($sort_type === 'ASC' || $sort_type === 'DESC'))
		$order = array($sort_name => $sort_type);
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="parse limit">
	$stExportPageSize = 0;
	$stExportPageNumber = 0;
	if ($isStExport)
	{
		$stExportPageSize = (int)$stExportOptions['STEXPORT_PAGE_SIZE'];
		$stExportPageNumber = (int)$stExportOptions['STEXPORT_PAGE_NUMBER'];
		$limit['nPageSize'] = $stExportPageSize + 1;
		$limit['iNumPage'] = $stExportPageNumber;
	}
	else if (!$bGroupingMode) // no limit in grouping mode
	{
		$limit['nPageSize'] = $arParams['ROWS_PER_PAGE'];

		if (!empty($settings['limit']))
		{
			$limit['nPageTop'] = $settings['limit'];
		}
		else if (!$excelView)
		{
			$limit['iNumPage'] = is_set($uriParams['PAGEN_1']) ? $uriParams['PAGEN_1'] : 1;
			$limit['bShowAll'] = true;
		}
	}
	// </editor-fold>

	unset($uriParams);

	// <editor-fold defaultstate="collapsed" desc="connect Lang">
	$fullHumanTitles = CReport::collectFullHumanTitles($fieldsTree);

	foreach ($viewColumns as $num => &$view)
	{
		if ($view['resultName'] == $view['humanTitle'])
		{
			$view['humanTitle'] = CReport::getFullColumnTitle($view, $viewColumns, $fullHumanTitles);
		}
	}
	unset($view);

	foreach ($changeableFilters as &$chFilter)
	{
		if (preg_match('/__COLUMN__(\d+)/', $chFilter['name'], $match))
		{
			$num = $match[1];
			$chFilter['title'] = $viewColumns[$num]['humanTitle'];
		}
		else
		{
			$chFilter['title'] = $fullHumanTitles[$chFilter['name']];
		}

	}
	unset($chFilter);
	// </editor-fold>

	// rewrite User SHORT_NAME
	CReport::rewriteUserShortName($select, $runtime, $arParams['USER_NAME_FORMAT'], $entity);

	// <editor-fold defaultstate="collapsed" desc="retrieve report rows">

	call_user_func_array(
		array($arParams['REPORT_HELPER_CLASS'], 'beforeViewDataQuery'),
		array(&$select, &$filter, &$group, &$order, &$limit, &$options, &$runtime)
	);

	$main_query = new Entity\Query($entity);
	$main_query->setSelect($select)
		->setFilter($filter)
		->setGroup($group)
		->setOrder($order)
	;

	foreach ($runtime as $k => $v)
	{
		$main_query->registerRuntimeField($k, $v);

		// add view column if needed
		if (isset($v['view_column']) && is_array($v['view_column']))
		{
			$runtimeColumnInfo = $v['view_column'];
			$newNum = max(array_keys($viewColumns)) + 1;
			$queryChains = $main_query->getChains();
			if (isset($queryChains[$k]))
			{
				$runtimeField = $queryChains[$k]->getLastElement()->getValue();
				if (is_array($runtimeField)) $runtimeField = end($runtimeField);
				$viewColumns[$newNum] = array(
					'field' => $runtimeField,
					'fieldName' => $k,
					'resultName' => $k,
					'humanTitle' => empty($runtimeColumnInfo['humanTitle']) ? '' : $runtimeColumnInfo['humanTitle'],
					'defaultSort' => '',
					'aggr' => '',
					'prcnt' => '',
					'href' => empty($runtimeColumnInfo['href']) ? '' : $runtimeColumnInfo['href'],
					'grouping' => false,
					'grouping_subtotal' => ($runtimeColumnInfo['grouping_subtotal'] === true) ? true : false,
					'runtime' => true
				);
				$viewColumnsByResultName[$k] = &$viewColumns[$newNum];
			}
		}
	}
	unset($runtimeField);

	if (isset($limit['nPageTop']))
		$main_query->setLimit($limit['nPageTop']);

	if ($isStExport && $stExportPageNumber > 1)
	{
		$main_query->setLimit($stExportPageSize + 1);
		$main_query->setOffset($stExportPageSize * ($stExportPageNumber - 1));
	}

	$result = $main_query->exec();
	$result = new CDBResult($result);
	if (!$bGroupingMode)
	{
		if ($isStExport && $stExportPageNumber === 1)
		{
			$result->NavStart($stExportPageSize + 1, true, 1);
		}
		else
		{
			if (isset($limit['nPageTop']))
				$result->NavStart($limit['nPageTop']);
			else
				$result->NavStart($limit['nPageSize']/*, true, $limit['iNumPage']*/);
		}
	}

	$data = array();
	$grcDataPrimaryValues = array();
	$grcDataPrimaryPointers = array();

	$qty = 0;
	$stExportEnableNextPage = false;
	while ($row = $result->Fetch())
	{
		if($isStExport && $stExportPageSize > 0 && ++$qty > $stExportPageSize)
		{
			$stExportEnableNextPage = true;
			break;
		}
		// rewrite UF values
		call_user_func_array(
			array($arParams['REPORT_HELPER_CLASS'], 'rewriteResultRowValues'),
			array(&$row, &$viewColumnsByResultName)
		);

		// attach URLs
		foreach ($row as $k => $v)
		{
			if (!array_key_exists($k, $viewColumnsByResultName))
			{
				continue;
			}

			$elem = $viewColumnsByResultName[$k];

			if (!empty($elem['href']))
			{
				$url = CReport::generateValueUrl($elem, $row, $entity);
				$row['__HREF_'.$k] = $url;
			}
		}

		// collect
		$data[] = $row;

		// grc stuff
		$grc_primary_string = '';

		foreach ($grcSelectPrimaries as $alias => $def)
		{
			// for grc filter
			$grcDataPrimaryValues['='.$def][] = $row[$alias];

			// for concat
			$grc_primary_string .= (string) $row[$alias] . '/';
		}

		// save original data indexes for grc values
		if (!isset($grcDataPrimaryPointers[$grc_primary_string]))
		{
			$grcDataPrimaryPointers[$grc_primary_string] = array();
		}

		$grcDataPrimaryPointers[$grc_primary_string][] = count($data)-1;
	}

	if ($isStExport)
	{
		if ($stExportPageNumber === 1)
		{
			$stExportOptions['STEXPORT_IS_FIRST_PAGE'] = 'Y';
			$stExportOptions['STEXPORT_TOTAL_ITEMS'] = (int)$result->SelectedRowsCount();
		}
		else
		{
			$stExportOptions['STEXPORT_IS_FIRST_PAGE'] = 'N';
		}
		$stExportOptions['STEXPORT_IS_LAST_PAGE'] = $stExportEnableNextPage ? 'N' : 'Y';
	}

	$grcDataPrimaryValues = array_map('array_unique', $grcDataPrimaryValues);
	// </editor-fold>

	if (!$isStExport && empty($settings['limit']))
	{
		$arResult["NAV_STRING"] = $result->GetPageNavString(
			'',
			(is_set($arParams['NAV_TEMPLATE'])) ? $arParams['NAV_TEMPLATE'] : 'arrows'
		);
		$arResult["NAV_PARAMS"] = $result->GetNavParams();
		$arResult["NAV_NUM"] = $result->NavNum;
	}

	// <editor-fold defaultstate="collapsed" desc="retrieve total counts">
	$total = array();
	$totalSelect = $select;
	$totalColumns = array();

	if (is_array($totalSelect) && !empty($totalSelect))
	{
		foreach ($viewColumns as $num => $view)
		{
			// total's fields are the same as percentable fields
			// they are also all numerics
			if (CReport::isColumnTotalCountable($view, $arParams['REPORT_HELPER_CLASS']))
			{
				// exclude from select all except those
				if (is_array($view) && isset($view['resultName']))
				{
					$totalColumns[$view['resultName']] = true;
				}
			}
		}
	}

	// save only totalCountable visible fields
	foreach ($totalSelect as $k => $v)
	{
		if (!array_key_exists($k, $totalColumns))
		{
			unset($totalSelect[$k]);
		}
	}

	// add SUM aggr
	$_totalSelect = $totalSelect;
	$totalSelect = [];
	$totalSelectAfter = [[], []];

	foreach ($_totalSelect as $k => $v)
	{
		$isCustomTotal = false;
		$isAverage = false;
		$isMinimum = false;
		$isMaximum = false;
		$isPrcntFromCol = false;
		if (is_array($customTotals[$k]))
		{
			if (is_array($customTotals[$k]['average']))
			{
				$isCustomTotal = $isAverage = true;
			}
			else if (is_array($customTotals[$k]['minimum']))
			{
				$isCustomTotal = $isMinimum = true;
			}
			else if (is_array($customTotals[$k]['maximum']))
			{
				$isCustomTotal = $isMaximum = true;
			}

			if (is_array($customTotals[$k]['prcntFromCol']))
			{
				$isCustomTotal = $isPrcntFromCol = true;
			}
		}

		if ($isCustomTotal)
		{
			if (!$isPrcntFromCol)
			{
				if ($isAverage)
				{
					$totalInfo = $customTotals[$k]['average'];
					$totalSelectAfter[0][] = new Entity\ExpressionField(
						'TOTAL_'.$k,
						'(SUM(%s) / SUM(%s))',
						[
							$totalInfo['sum']['alias'],
							$totalInfo['cnt']['alias']
						]
					);
				}
				else if ($isMinimum || $isMaximum)
				{
					if ($isMinimum)
					{
						$sqlFunctionName = 'MIN';
					}
					else if ($isMaximum)
					{
						$sqlFunctionName = 'MAX';
					}

					$totalSelect[] = new Entity\ExpressionField('TOTAL_'.$k, $sqlFunctionName.'(%s)', $k);

					unset($sqlFunctionName);
				}
			}
			else
			{
				$totalInfo = $customTotals[$k]['prcntFromCol'];
				if ($isAverage)
				{
					$averageInfo = $customTotals[$k]['average'];
					$totalSelectAfter[1][] = new Entity\ExpressionField(
						'TOTAL_'.$k,
						'(SUM(%s) / SUM(%s)) / (%s) * 100',
						[
							$averageInfo['sum']['alias'],
							$averageInfo['cnt']['alias'],
							'TOTAL_'.$totalInfo['remote']['alias']
						]
					);
				}
				else if ($isMinimum || $isMaximum)
				{
					if ($isMinimum)
					{
						$sqlFunctionName = 'MIN';
					}
					else if ($isMaximum)
					{
						$sqlFunctionName = 'MAX';
					}

					$totalSelectAfter[1][] = new Entity\ExpressionField(
						'TOTAL_'.$k,
						'('.$sqlFunctionName.'(%s)) / (%s) * 100',
						[
							$totalInfo['local']['alias'],
							'TOTAL_'.$totalInfo['remote']['alias']
						]
					);

					unset($sqlFunctionName);
				}
				else
				{
					$totalSelectAfter[1][] = new Entity\ExpressionField(
						'TOTAL_'.$k,
						'(SUM(%s)) / (%s) * 100',
						[
							$totalInfo['local']['alias'],
							'TOTAL_'.$totalInfo['remote']['alias']
						]
					);
				}
			}
		}
		else
		{
			$totalSelect[] = new Entity\ExpressionField('TOTAL_'.$k, 'SUM(%s)', $k);
		}
	}
	unset($isCustomTotal, $isAverage, $isPrcntFromCol, $totalInfo, $averageInfo);
	$totalSelect = array_merge($totalSelect, $totalSelectAfter[0], $totalSelectAfter[1]);
	unset($_totalSelect, $totalSelectAfter);

	if (!empty($totalSelect))
	{
		// source query
		$query_from = new Entity\Query($entity);

		$subSelect = $select;

		foreach ($runtime as $k => $v)
		{
			$isCustomTotal = false;
			$isAverage = false;
			$isPrcntFromCol = false;
			if (is_array($customTotals[$k]))
			{
				if (is_array($customTotals[$k]['average']))
				{
					$isCustomTotal = $isAverage = true;
				}
				if (is_array($customTotals[$k]['prcntFromCol']))
				{
					$isCustomTotal = $isPrcntFromCol = true;
				}
			}

			if ($isCustomTotal)
			{
				if ($isAverage)
				{
					$totalInfo = $customTotals[$k]['average'];
					$fieldAlias = $totalInfo['sum']['alias'];
					$query_from->registerRuntimeField($fieldAlias, $totalInfo['sum']['def']);
					$subSelect[$fieldAlias] = $fieldAlias;

					$fieldAlias = $totalInfo['cnt']['alias'];
					$query_from->registerRuntimeField($fieldAlias, $totalInfo['cnt']['def']);
					$subSelect[$fieldAlias] = $fieldAlias;
					unset($subSelect[$k]);
				}
				if ($isPrcntFromCol && !$isAverage)
				{
					$totalInfo = $customTotals[$k]['prcntFromCol'];
					$fieldAlias = $totalInfo['local']['alias'];
					$query_from->registerRuntimeField($fieldAlias, $totalInfo['local']['def']);
					$subSelect[$fieldAlias] = $fieldAlias;
					unset($subSelect[$k]);
				}
			}
			else
			{
				$query_from->registerRuntimeField($k, $v);
			}
		}
		unset($isCustomTotal, $isAverage, $isPrcntFromCol, $totalInfo);

		$query_from->setSelect($subSelect);
		$query_from->setFilter($filter);
		$query_from->setGroup($group);

		// total query
		$total_query = new Entity\Query($query_from);
		$total_query->setSelect($totalSelect);

		$result = $total_query->exec();
		$total = $result->fetch();
		$total = ($total === false) ? array() : $total;
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="group_concat fields">
	$grcData = array();

	// check necessity of concat rows
	if ($need_concat_rows && $grcDataPrimaryValues)
	{
		// filter - add primaries from data
		if ($grcInitPrimary)
		{
			// init primary enough
			$grcFilter = $grcDataPrimaryValues;
		}
		else
		{
			// merge with primaries
			$grcFilter = array_merge($filter, $grcDataPrimaryValues);
		}

		// select data for each grc field
		foreach ($grcSettingsNum as $num)
		{
			$elem = $settings['select'][$num];

			// prepare
			$grcSelect = $grcSelectPrimaries;

			CReport::appendHrefSelectElements(
				$elem, $fList, $entity, $arParams['REPORT_HELPER_CLASS'], $grcSelect, $runtime
			);

			if (!empty($elem['href']))
			{
				$viewColumns[$num]['href'] = $elem['href'];
			}

			list($alias, $selElem) = CReport::prepareSelectViewElement(
				$elem, $settings['select'], $is_init_entity_aggregated, $fList, $fChainList,
				$arParams['REPORT_HELPER_CLASS'], $entity
			);

			if (is_array($selElem) && !empty($selElem['expression']))
			{
				$runtime[$alias] = $selElem;
				$grcSelect[] = $alias;
			}
			else
			{
				// normal field
				$alias = Entity\QueryChain::getAliasByDefinition($entity, $elem['name']);
				$grcSelect[$alias] = $selElem;
			}

			CReport::rewriteUserShortName($grcSelect, $runtime, $arParams['USER_NAME_FORMAT'], $entity, true);

			// add primary of grc entity field
			$grcChain = Entity\QueryChain::getChainByDefinition($entity, $elem['name']);
			$grc_field = $grcChain->getLastElement()->getValue();
			if (is_array($grc_field)) $grc_field = end($grc_field);
			$grc_primary = end($grc_field->getEntity()->getPrimaryArray());
			$grc_marker = mb_substr($elem['name'], 0, mb_strrpos($elem['name'], '.')).'.' . $grc_primary;
			$grc_marker_alias = Entity\QueryChain::getAliasByDefinition($entity, $grc_marker);

			$grcSelect[$grc_marker_alias] = $grc_marker;

			// select
			$resultName = $viewColumns[$num]['resultName'];
			$grcData[$resultName] = array();

			$grc_query = new Entity\Query($entity);
			$grc_query->setSelect($grcSelect);
			$grc_query->setFilter($grcFilter);

			foreach ($runtime as $k => $v)
			{
				$grc_query->registerRuntimeField($k, $v);
			}

			$result = $grc_query->exec();

			while ($row = $result->fetch())
			{
				if (empty($row[$grc_marker_alias]))
				{
					continue;
				}

				$grcData[$resultName][] = $row;
			}

			// add empty values to data
			foreach ($data as $k => $v)
			{
				$data[$k][$alias] = null;
			}

			// add values to data
			foreach ($grcData[$resultName] as $grcIndex => &$row)
			{
				$grc_primary_string = '';

				foreach ($grcSelectPrimaries as $pResultName => $def)
				{
					$grc_primary_string .= (string) $row[$pResultName] . '/';
				}

				$dataIndexes = $grcDataPrimaryPointers[$grc_primary_string];

				foreach ($dataIndexes as $dataIndex)
				{
					if (!isset($data[$dataIndex][$alias]))
					{
						$data[$dataIndex][$alias] = array();
					}

					if (!empty($elem['href']) && mb_strlen($row[$alias]))
					{
						$url = CReport::generateValueUrl($elem, $row, $entity);
						$row['__HREF_'.$alias] = $url;
					}

					$data[$dataIndex][$alias][$grcIndex] = $row[$alias];
				}
			}
			unset($row);
		}
	} // end concat grc
	// </editor-fold>

	// collect UF values
	call_user_func_array(
		array($arParams['REPORT_HELPER_CLASS'], 'collectUFValues'),
		array(&$data, &$viewColumnsByResultName, $total)
	);

	$customChartTotal = $customChartData = array();
	// format results
	call_user_func_array(
		array($arParams['REPORT_HELPER_CLASS'], 'formatResults'),
		array(&$data, &$viewColumnsByResultName, $total, &$customChartData)
	);
	call_user_func_array(
		array($arParams['REPORT_HELPER_CLASS'], 'formatResultsTotal'),
		array(&$total, &$viewColumnsByResultName, &$customChartTotal)
	);
}
catch (Exception $e)
{
	if ($isStExport)
	{
		return array('ERROR' => $e->getMessage());
	}
	else
	{
		if ($e instanceof BXUserException)
		{
			$arResult['ERROR'] = $e->getMessage();
		}
		else
		{
			CTimeZone::Enable();
			throw $e;
		}
	}
}

CTimeZone::Enable();


// <editor-fold defaultstate="collapsed" desc="Params of step-by-step export">

if ($isStExport)
{
	$arResult['STEXPORT_OPTIONS'] = $stExportOptions;
}
else if ($isStExportEnabled && !$isStExport)
{
	$entityType = 'REPORT';
	$stExportId = 'STEXPORT_'.$entityType.'_MANAGER';
	$randomSequence = new Bitrix\Main\Type\RandomSequence($stExportId);
	$stExportManagerId = $stExportId.'_'.$randomSequence->randString();
	$uriParams = array();
	$uriParamNameList = array(
		'F_DATE_TYPE', 'F_DATE_DAYS', 'F_DATE_TO', 'F_DATE_FROM', 'filter', 'set_filter', 'sort_id', 'sort_type',
		'USER_ID', 'GROUP_ID', 'PAGEN_1', 'EXCEL', 'select_my_tasks', 'select_depts_tasks', 'select_group_tasks'

	);
	if (is_array($arParams['~URI_PARAMS']) && !empty($arParams['~URI_PARAMS']))
	{
		$uriParamsSource = &$arParams['~URI_PARAMS'];
	}
	else
	{
		$uriParamsSource = &$_GET;
	}
	foreach ($uriParamNameList as $paramName)
	{
		if (array_key_exists($paramName, $uriParamsSource))
		{
			$uriParams[$paramName] = $uriParamsSource[$paramName];
		}
	}
	$componentParams = array();
	$componentParamNameList = array(
		'REPORT_HELPER_CLASS', 'USE_CHART', 'REPORT_ID', 'PATH_TO_REPORT_LIST', 'PATH_TO_REPORT_CONSTRUCT',
		'PATH_TO_REPORT_VIEW', 'ROWS_PER_PAGE', 'NAV_TEMPLATE', 'USER_NAME_FORMAT', 'TITLE', 'OWNER_ID',
		'REPORT_CURRENCY_LABEL_TEXT', 'REPORT_WEIGHT_UNITS_LABEL_TEXT', 'F_SALE_SITE', 'F_SALE_PRODUCT'
	);
	foreach ($componentParamNameList as $paramName)
	{
		if (array_key_exists($paramName, $arParams))
		{
			$componentParams[$paramName] = $arParams[$paramName];
		}
	}
	$componentParams['URI_PARAMS'] = $uriParams;
	$arResult['STEXPORT_PARAMS'] = array(
		'siteId' => SITE_ID,
		'entityType' => $entityType,
		'stExportId' => $stExportId,
		'managerId' => $stExportManagerId,
		'sToken' => 's'.time(),
		'serviceUrl' => '/bitrix/components/bitrix/report.view/stexport.ajax.php',
		'componentParams' => $componentParams,
		'messages' => array(
			'stExportExcelDlgTitle' => GetMessage('REPORT_STEXPORT_TITLE'),
			'stExportExcelDlgSummary' => GetMessage('REPORT_STEXPORT_SUMMARY')
		)
	);
	if (isset($arParams['STEXPORT_PARAMS']['serviceUrl']))
	{
		$arResult['STEXPORT_PARAMS']['serviceUrl'] = $arParams['STEXPORT_PARAMS']['serviceUrl'];
	}
	unset(
		$entityType, $stExportId, $randomSequence, $stExportManagerId, $uriParams, $uriParamNameList,
		$uriParamsSource, $componentParams, $componentParamNameList, $paramName
	);
}
// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="template vars">
$arResult['entityName'] = $entityName;
$arResult['helperClassName'] = $arParams['REPORT_HELPER_CLASS'];
$arResult['fList'] = $fList;
$arResult['settings'] = $settings;
$arResult['sort_id'] = $sort_id;
$arResult['sort_type'] = $sort_type;
$arResult['report'] = $report;
$arResult['viewColumns'] = $viewColumns;
$arResult['data'] = $data;
$arResult['grcData'] = $grcData;
$arResult['changeableFilters'] = $changeableFilters;
$arResult['changeableFiltersEntities'] = $changeableFiltersEntities;
$arResult['chfilter_examples'] = array();
$arResult['total'] = $total;

$arResult['form_date'] = $form_date;
$arResult['period'] = $period;

$arResult['groupingMode'] = $bGroupingMode;

$arResult['customColumnTypes'] = $customColumnTypes;
$arResult['customChartData'] = $customChartData;
$arResult['customChartTotal'] = $customChartTotal;

$arResult['ufInfo'] = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getUFInfo'));

$arResult['allowHorizontalScroll'] = (
	!isset($arParams['ALLOW_HORIZONTAL_SCROLL'])
	|| $arParams['ALLOW_HORIZONTAL_SCROLL'] === 'Y'
	|| $arParams['ALLOW_HORIZONTAL_SCROLL'] === true
);
// </editor-fold>


if ($isStExport && $stExportOptions['STEXPORT_TYPE'] === 'excel')
{
	$this->IncludeComponentTemplate('excel');

	return array(
		'PROCESSED_ITEMS' => count($data),
		'TOTAL_ITEMS' => $stExportOptions['STEXPORT_TOTAL_ITEMS']
	);
}
else if ($excelView)
{
	$APPLICATION->RestartBuffer();

	Header("Content-Type: application/force-download");
	Header("Content-Type: application/octet-stream");
	Header("Content-Type: application/download");
	Header("Content-Disposition: attachment;filename=report.xls");
	Header("Content-Transfer-Encoding: binary");

	$this->IncludeComponentTemplate('excel');

	exit;
}
else
{
	$this->IncludeComponentTemplate();
}

