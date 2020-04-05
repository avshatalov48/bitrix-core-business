<?php
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if ($STAT_RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");
if (isset($find_diagram_type))
{
	if ($find_diagram_type != "EXIT_COUNTER" && $find_diagram_type != "ENTER_COUNTER")
		$find_diagram_type = "COUNTER";
}
else
	$find_diagram_type = "COUNTER";

if (is_array($find_adv) && count($find_adv) > 0)
	$str = implode(" | ", $find_adv);

$arFilter = array(
	"DATE1" => $find_date1,
	"DATE2" => $find_date2,
	"SHOW" => $find_show,
	"SECTION" => $find_section,
	"SITE_ID" => $find_site_id,
	"PAGE_404" => $find_page_404,
	"ADV" => $str,
	"ADV_DATA_TYPE" => $find_adv_data_type,
	"SECTION_EXACT_MATCH" => $find_section_exact_match,
);
$rsPages = CPage::GetList($find_diagram_type, $by, $order, $arFilter, $is_filtered);
$arrPages = array();
$max_counter = 0;
$sum_counter = 0;
while ($arPage = $rsPages->Fetch())
{
	$arrPages[] = $arPage;
	$sum_counter += $arPage["COUNTER"];
	if (intval($arPage["COUNTER"]) > $max_counter)
		$max_counter = intval($arPage["COUNTER"]);
}
$total = count($arrPages);
if ($total > 10)
	$total = 11;

$i = 1;
$top_sum = 0;
foreach ($arrPages as $key => $arVal)
{
	if ($i == 11)
		break;

	$top_sum += $arVal["COUNTER"];

	$color = GetNextRGB($color, $total);
	$arChart[] = array(
		"COUNTER" => $arVal["COUNTER"],
		"COLOR" => $color,
	);
	$i++;
}
if ($total == 11)
{
	$arChart[] = array(
		"COUNTER" => ($sum_counter - $top_sum),
		"COLOR" => GetNextRGB($color, $total),
	);
}
$diameter = COption::GetOptionString("statistic", "DIAGRAM_DIAMETER");
// create an image canvas
$ImageHandle = CreateImageHandle($diameter, $diameter);
// draw pie chart
Circular_Diagram($ImageHandle, $arChart, "FFFFFF", $diameter, $diameter / 2, $diameter / 2, true);
// send to client
ShowImageHeader($ImageHandle);
