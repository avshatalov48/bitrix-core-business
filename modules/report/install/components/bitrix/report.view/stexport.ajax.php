<?php

define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);

/** @var array $requiredModules */
if (!is_array($requiredModules))
{
	$requiredModules = array();
}

$params = isset($_REQUEST['PARAMS']) && is_array($_REQUEST['PARAMS']) ? $_REQUEST['PARAMS'] : array();
$siteId = (is_array($params) && isset($params['SITE_ID'])) ? substr(preg_replace('/[^a-z0-9_]/i', '', $params['SITE_ID']), 0, 2) : '';
if($siteId !== '')
{
	define('SITE_ID', $siteId);
}

$action = isset($_REQUEST['ACTION']) ? $_REQUEST['ACTION'] : '';

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Localization\Loc;

CUtil::JSPostUnescape();

if(!function_exists('__ReportStExportEndResponse'))
{
	function __ReportStExportEndResponse($result)
	{
		$GLOBALS['APPLICATION']->RestartBuffer();
		Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
		if(!empty($result))
		{
			echo CUtil::PhpToJSObject($result);
		}
		require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
		die();
	}
}
if(!function_exists('__ReportExportWriteDataToFile'))
{
	function __ReportExportWriteDataToFile($filePath, $data)
	{
		$file = fopen($filePath, 'ab');
		$fileSize = filesize($filePath);
		if(is_resource($file))
		{
			if($fileSize <= 0)
			{
				// add UTF-8 BOM marker
				if (defined('BX_UTF') && BX_UTF)
				{
					fwrite($file, chr(239).chr(187).chr(191));
				}
			}
			fwrite($file, $data);
			fclose($file);
			unset($file);
		}
	}
}

if (!is_string($siteId) || strlen($siteId) <= 0)
{
	__ReportStExportEndResponse(array('ERROR' => 'Site ID is not specified.'));
}


if (!Bitrix\Main\Loader::includeModule('report'))
{
	__ReportStExportEndResponse(array('ERROR' => 'Could not include report module.'));
}

/** @global CMain $APPLICATION */
global $APPLICATION;

if ($action === 'STEXPORT')
{
	\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

	$entityTypeName = isset($params['ENTITY_TYPE_NAME']) ? $params['ENTITY_TYPE_NAME'] : '';
	if($entityTypeName === '')
	{
		__ReportStExportEndResponse(array('ERROR' => 'Entity type is not specified.'));
	}

	if($entityTypeName !== 'REPORT')
	{
		__ReportStExportEndResponse(
			array('ERROR' => "The '{$entityTypeName}' type is not supported in current context.")
		);
	}

	$exportType = isset($params['EXPORT_TYPE']) ? $params['EXPORT_TYPE'] : '';
	if(!in_array($exportType, array('csv', 'excel'), true))
	{
		__ReportStExportEndResponse(
			array('ERROR' => "The export type '{$exportType}' is not supported in current context.")
		);
	}

	$stepTimeInterval = 2;    // sec
	$stepStartTime = time();
	$defaultBlockSize = 100;   // items per block

	$processToken = isset($params['PROCESS_TOKEN']) ? $params['PROCESS_TOKEN'] : '';
	if($processToken === '')
	{
		__ReportStExportEndResponse(array('ERROR' => 'Process token is not specified.'));
	}

	$cParams = is_array($params['COMPONENT_PARAMS']) ? $params['COMPONENT_PARAMS'] : array();

	$progressData = CUserOptions::GetOption('report', 'report_stexport', '');
	if (!is_array($progressData))
		$progressData = array();

	$lastToken = isset($progressData['PROCESS_TOKEN']) ? $progressData['PROCESS_TOKEN'] : '';
	$isNewToken = ($processToken !== $lastToken);
	$startTime = time();
	$initialOptions = array();
	if ($isNewToken)
	{
		$filePath = '';
		$processedItems = 0;
		$totalItems = 0;
		$blockSize = $defaultBlockSize;
		/*if (is_array($_REQUEST['INITIAL_OPTIONS'])
			&& isset($_REQUEST['INITIAL_OPTIONS']['INITIAL_OPTION'])
			&& $_REQUEST['INITIAL_OPTIONS']['INITIAL_OPTION'] === 'Y')
		{
			$initialOptions['INITIAL_OPTION'] = 'Y';
		}*/
	}
	else
	{
		$filePath = isset($progressData['FILE_PATH']) ? $progressData['FILE_PATH'] : 0;
		$processedItems = isset($progressData['PROCESSED_ITEMS']) ? (int)$progressData['PROCESSED_ITEMS'] : 0;
		$totalItems = isset($progressData['TOTAL_ITEMS']) ? (int)$progressData['TOTAL_ITEMS'] : 0;
		$blockSize = isset($progressData['BLOCK_SIZE']) ? (int)$progressData['BLOCK_SIZE'] : $defaultBlockSize;
		/*if (is_array($progressData['INITIAL_OPTIONS'])
			&& isset($progressData['INITIAL_OPTIONS']['INITIAL_OPTION'])
			&& $progressData['INITIAL_OPTIONS']['INITIAL_OPTION'] === 'Y')
		{
			$initialOptions['INITIAL_OPTION'] = 'Y';
		}*/
	}

	if (!is_string($filePath) || strlen($filePath) === 0 || !CheckDirPath($filePath))
	{
		if (!$isNewToken)
		{
			CUserOptions::DeleteOption('report', 'report_stexport');
			$processedItems = 0;
			$totalItems = 0;
			$blockSize = $defaultBlockSize;
		}

		if ($exportType === 'csv')
		{
			$fileExt = 'csv';
		}
		else
		{
			$fileExt = 'xls';
		}
		$fileName = "report.{$fileExt}";
		$tempDir = $_SESSION['REPORT_EXPORT_TEMP_DIR'] =
			CTempFile::GetDirectoryName(1, array('report', uniqid('report_export_')));
		CheckDirPath($tempDir);
		$filePath = "{$tempDir}{$fileName}";

		// Save progress
		$progressData = array(
			'FILE_PATH' => $filePath,
			'PROCESS_TOKEN' => $processToken,
			'INITIAL_OPTIONS' => $initialOptions,
			'BLOCK_SIZE' => $blockSize,
			'PROCESSED_ITEMS' => $processedItems,
			'TOTAL_ITEMS' => $totalItems
		);
		CUserOptions::SetOption('report', 'report_stexport', $progressData);
	}

	do
	{
		if ($processedItems < 0 || $totalItems < 0 || $totalItems < $processedItems
			|| ($processedItems > 0 && $totalItems === $processedItems))
		{
			__ReportStExportEndResponse(array('ERROR' => 'Progress data is incorrect.'));
		}

		$nextBlockNumber = (int)floor($processedItems / $blockSize) + 1;

		//region Component params
		$uriParams = array();
		$uriParamNameList = array(
			'F_DATE_TYPE', 'F_DATE_DAYS', 'F_DATE_TO', 'F_DATE_FROM', 'filter', 'set_filter', 'sort_id', 'sort_type',
			'USER_ID', 'GROUP_ID', 'PAGEN_1', 'EXCEL', 'select_my_tasks', 'select_depts_tasks', 'select_group_tasks'

		);
		if (is_array($cParams['URI_PARAMS']) && !empty($cParams['URI_PARAMS']))
		{
			foreach ($uriParamNameList as $paramName)
			{
				if ($paramName === 'PAGEN_1')
				{
					$uriParams[$paramName] = $nextBlockNumber;
				}
				else if ($paramName === 'EXCEL')
				{
					$uriParams[$paramName] = 'Y';
				}
				else if (array_key_exists($paramName, $cParams['URI_PARAMS']))
				{
					$uriParams[$paramName] = $cParams['URI_PARAMS'][$paramName];
				}
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
			if ($paramName === 'ROWS_PER_PAGE')
			{
				$componentParams[$paramName] = $blockSize;
			}
			else if (array_key_exists($paramName, $cParams))
			{
				$componentParams[$paramName] = $cParams[$paramName];
			}
		}
		$componentParams['URI_PARAMS'] = $uriParams;
		$componentParams['STEXPORT_OPTIONS'] = array(
			'STEXPORT_MODE' => 'Y',
			'STEXPORT_TYPE' => $exportType,
			'STEXPORT_TOTAL_ITEMS' => $totalItems,
			'STEXPORT_PAGE_SIZE' => $blockSize,
			'STEXPORT_PAGE_NUMBER' => $nextBlockNumber,
			'STEXPORT_INITIAL_OPTIONS' => $initialOptions
		);
		$componentParams['REQUIRED_MODULES'] = $requiredModules;

		unset($paramName, $componentParamNameList, $uriParamNameList, $uriParams, $requiredModules);
		//endregion Component params

		ob_start();
		$cResult = $APPLICATION->IncludeComponent('bitrix:report.view', '', $componentParams);
		$exportData = ob_get_contents();
		ob_end_clean();

		$processedItemsOnStep = 0;

		if (is_array($cResult))
		{
			if (isset($cResult['ERROR']))
			{
				__ReportStExportEndResponse(array('ERROR' => $cResult['ERROR']));
			}
			else
			{
				if (isset($cResult['PROCESSED_ITEMS']))
					$processedItemsOnStep = (int)$cResult['PROCESSED_ITEMS'];

				// Get total items quantity on 1st step.
				if ($nextBlockNumber === 1 && isset($cResult['TOTAL_ITEMS']))
					$totalItems = (int)$cResult['TOTAL_ITEMS'];
			}
		}
		
		if ($processedItemsOnStep === 0 && $nextBlockNumber === 1)
		{
			__ReportStExportEndResponse(array('ERROR' => Loc::getMessage('REPORT_VIEW_STEXPORT_NO_DATA')));
		}

		if($processedItemsOnStep > 0)
		{
			$processedItems += $processedItemsOnStep;

			__ReportExportWriteDataToFile($filePath, $exportData);
		}
		unset($exportData);

		// Save progress
		$progressData = array(
			'FILE_PATH' => $filePath,
			'PROCESS_TOKEN' => $processToken,
			'INITIAL_OPTIONS' => $initialOptions,
			'BLOCK_SIZE' => $blockSize,
			'PROCESSED_ITEMS' => $processedItems,
			'TOTAL_ITEMS' => $totalItems
		);
		CUserOptions::SetOption('report', 'report_stexport', $progressData);

		$stepTime = time() - $stepStartTime;
		$timeExceeded = ($stepTime < 0 || $stepTime >= $stepTimeInterval);

	} while (
		!$timeExceeded
		&& $processedItems < $totalItems
		&& $processedItemsOnStep > 0
		&& $processedItemsOnStep >= $blockSize
	);

	if($processedItems < $totalItems && $processedItemsOnStep > 0 && $processedItemsOnStep >= $blockSize)
	{
		__ReportStExportEndResponse(
			array(
				'STATUS' => 'PROGRESS',
				'PROCESSED_ITEMS' => $processedItems,
				'TOTAL_ITEMS' => $totalItems,
				'SUMMARY' => Loc::getMessage(
					'REPORT_VIEW_STEXPORT_PROGRESS_SUMMARY',
					array(
						'#PROCESSED_ITEMS#' => $processedItems,
						'#TOTAL_ITEMS#' => $totalItems
					)
				)
			)
		);
	}
	else
	{
		$fileUrl = SITE_DIR.'bitrix/components/bitrix/report.view/stexport.php?type='.$exportType;
		CUserOptions::DeleteOption('report', 'report_stexport');
		__ReportStExportEndResponse(
			array(
				'STATUS' => 'COMPLETED',
				'PROCESSED_ITEMS' => $processedItems,
				'TOTAL_ITEMS' => $totalItems,
				'SUMMARY_HTML' => '<div>'.
					htmlspecialcharsbx(Loc::getMessage('REPORT_VIEW_STEXPORT_COMPLETED_SUMMARY1')).'<br/>'.
					htmlspecialcharsbx(
						Loc::getMessage(
							'REPORT_VIEW_STEXPORT_COMPLETED_SUMMARY2',
							array('#PROCESSED_ITEMS#' => $processedItems)
						)
					).'<br/><br/></div><div><a href="'.htmlspecialcharsbx($fileUrl).'">'.
					htmlspecialcharsbx(Loc::getMessage('REPORT_VIEW_STEXPORT_DOWNLOAD')).'</a></div>'
			)
		);
	}
}
