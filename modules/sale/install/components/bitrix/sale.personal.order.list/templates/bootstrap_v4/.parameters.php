<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (CModule::IncludeModule('sale'))
{
	$dbStat = CSaleStatus::GetList(array('sort' => 'asc'), array('LID' => LANGUAGE_ID), false, false, array('ID', 'NAME'));
	$statList = array();
	while ($item = $dbStat->Fetch())
		$statList[$item['ID']] = $item['NAME'];

	$statList['PSEUDO_CANCELLED'] = 1;	

	$availColors = array(
		'green' => GetMessage("SPOL_STATUS_COLOR_GREEN"),
		'yellow' => GetMessage("SPOL_STATUS_COLOR_YELLOW"),
		'red' => GetMessage("SPOL_STATUS_COLOR_RED"),
		'gray' => GetMessage("SPOL_STATUS_COLOR_GRAY"),
	);

	$colorDefaults = array(
		'N' => 'green', // new
		'P' => 'yellow', // payed
		'F' => 'gray', // finished
		'PSEUDO_CANCELLED' => 'red' // cancelled
	);

	foreach ($statList as $id => $name)
		$arTemplateParameters["STATUS_COLOR_".$id] = array(
			"NAME" => $id == 'PSEUDO_CANCELLED' ? GetMessage("SPOL_PSEUDO_CANCELLED_COLOR") : GetMessage("SPOL_STATUS_COLOR").' "'.$name.'"',
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => $availColors,
			"DEFAULT" => empty($colorDefaults[$id]) ? 'gray' : $colorDefaults[$id],
		);
}
?>