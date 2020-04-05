<?
define("BX_SESSION_ID_CHANGE", false);
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */

if (!CModule::IncludeModule('perfmon'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$RIGHT = $APPLICATION->GetGroupRight("perfmon");
if ($RIGHT < "R")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

$width = intval($_GET["width"]);
$max_width = COption::GetOptionInt("perfmon", "max_graph_width");
if ($width <= 0 || $width > $max_width)
	$width = $max_width;

$height = intval($_GET["height"]);
$max_height = COption::GetOptionInt("perfmon", "max_graph_height");
if ($height <= 0 || $height > $max_height)
	$height = $max_height;

// Image init
$ImageHandle = CreateImageHandle($width, $height);

$arrX    = array(); // X axis points
$arrY    = array(); // Y axis points
$arExec  = array();
$arResp  = array();
$arPages = array();

/******************************************************
		Get graph data
*******************************************************/
$i = 1;
$rsData = CPerfCluster::GetList(array("ID" => "ASC"));
while ($ar = $rsData->Fetch())
{
	$arrX[] = $i;
	$i++;

	if ($_REQUEST["find_data_type"] == "PAGE_EXEC_TIME")
	{
		$arExec[] = $ar["PAGE_EXEC_TIME"];
		$arrY[] = $ar["PAGE_EXEC_TIME"];

		$arResp[] = $ar["PAGE_RESP_TIME"];
		$arrY[] = $ar["PAGE_RESP_TIME"];
	}
	else
	{
		$arPages[] = $ar["PAGES_PER_SECOND"];
		$arrY[] = $ar["PAGES_PER_SECOND"];
	}
}

if (count($arrX) > 1)
{
	$arrayX = GetArrayY($arrX, $MinX, $MaxX, 10, 'N', true);  // X axis grid points
	$arrayY = GetArrayY($arrY, $MinY, $MaxY, 10, 'Y', $_REQUEST["find_data_type"] == "PAGE_EXEC_TIME"? false: true); // Y axis grid points
	DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHandle);
	if ($_REQUEST["find_data_type"] == "PAGE_EXEC_TIME")
	{
		Graf($arrX, $arExec, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, "ff0000");
		Graf($arrX, $arResp, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, "0000ff");
	}
	else
	{
		Graf($arrX, $arPages, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, "ff0000");
	}
}
else
{
	DrawCoordinatGrid(array(1, 2), array(0, 0), $width, $height, $ImageHandle);
}

/******************************************************
		send image
*******************************************************/

ShowImageHeader($ImageHandle);
