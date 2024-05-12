<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$questionId = intval($request->get("qid"));
if ($questionId <= 0 || !\Bitrix\Main\Loader::includeModule("vote"))
{
	return;
}

$diameter = intval($request->get("dm"));
$diameter = min(750, ($diameter > 0 ? $diameter : 150));

$res = CVoteAnswer::GetList($questionId,($by="s_counter"),($order="desc"), array("ACTIVE" => "Y"), array("nTopCount" => 1000));
$res->NavStart(1000);
$totalRecords = $res->SelectedRowsCount();
$arChart = array();
$color = "";
$sum = 0;
while ($arAnswer = $res->Fetch())
{
	$arChart[] = [
		"COLOR" => ($arAnswer["COLOR"] <> '' ? trim($arAnswer["COLOR"], "#") : ($color = GetNextRGB($color, $totalRecords))),
		"COUNTER" => $arAnswer["COUNTER"]
	];
	$sum += $arAnswer["COUNTER"];
}

// create an image
$ImageHandle = CreateImageHandle($diameter, $diameter);
imagefill($ImageHandle, 0, 0, imagecolorallocate($ImageHandle, 255,255,255));

// drawing pie chart
if ($sum > 0) 
{
	Circular_Diagram($ImageHandle, $arChart, "FFFFFF", $diameter, $diameter/2, $diameter/2);
}

// displaying of the resulting image
ShowImageHeader($ImageHandle);
?>