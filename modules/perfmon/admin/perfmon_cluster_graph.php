<?php
define('BX_SESSION_ID_CHANGE', false);
define('STOP_STATISTICS', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
/** @var CMain $APPLICATION */
/** @var CDatabase $DB */
/** @var CUser $USER */

if (!CModule::IncludeModule('perfmon'))
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$RIGHT = CMain::GetGroupRight('perfmon');
if ($RIGHT < 'R')
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

/** @var \Bitrix\Main\HTTPRequest $request */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/img.php';

$width = intval($request->get('width'));
$max_width = COption::GetOptionInt('perfmon', 'max_graph_width');
if ($width <= 0 || $width > $max_width)
{
	$width = $max_width;
}

$height = intval($request->get('height'));
$max_height = COption::GetOptionInt('perfmon', 'max_graph_height');
if ($height <= 0 || $height > $max_height)
{
	$height = $max_height;
}

// Image init
$ImageHandle = CreateImageHandle($width, $height);

$arrX = []; // X axis points
$arrY = []; // Y axis points
$arExec = [];
$arResp = [];
$arPages = [];

/******************************************************
		Get graph data
*******************************************************/
$i = 1;
$rsData = CPerfCluster::GetList(['ID' => 'ASC']);
while ($ar = $rsData->Fetch())
{
	$arrX[] = $i;
	$i++;

	if ($_REQUEST['find_data_type'] == 'PAGE_EXEC_TIME')
	{
		$arExec[] = $ar['PAGE_EXEC_TIME'];
		$arrY[] = $ar['PAGE_EXEC_TIME'];

		$arResp[] = $ar['PAGE_RESP_TIME'];
		$arrY[] = $ar['PAGE_RESP_TIME'];
	}
	else
	{
		$arPages[] = $ar['PAGES_PER_SECOND'];
		$arrY[] = $ar['PAGES_PER_SECOND'];
	}
}

if (count($arrX) > 1)
{
	$arrayX = GetArrayY($arrX, $MinX, $MaxX, 10, 'N', true); // X axis grid points
	$arrayY = GetArrayY($arrY, $MinY, $MaxY, 10, 'Y', $_REQUEST['find_data_type'] == 'PAGE_EXEC_TIME' ? false : true); // Y axis grid points
	DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHandle);
	if ($_REQUEST['find_data_type'] == 'PAGE_EXEC_TIME')
	{
		Graf($arrX, $arExec, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, 'ff0000');
		Graf($arrX, $arResp, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, '0000ff');
	}
	else
	{
		Graf($arrX, $arPages, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, 'ff0000');
	}
}
else
{
	DrawCoordinatGrid([1, 2], [0, 0], $width, $height, $ImageHandle);
}

/******************************************************
		send image
*******************************************************/

ShowImageHeader($ImageHandle);
