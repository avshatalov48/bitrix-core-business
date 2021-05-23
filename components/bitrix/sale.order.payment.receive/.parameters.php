<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("sale"))
	return;

$arPaySys = array("0" => GetMessage("SOPR_CHOOSE_PC"));
$dbPaySystem = \Bitrix\Sale\PaySystem\Manager::getList(array(
	'filter' => array('ACTIVE'=>'Y', 'HAVE_RESULT_RECEIVE'=>'Y'),
	'order' => array('SORT'=>'ASC', 'PSA_NAME'=>'ASC')
));

while ($paySystem = $dbPaySystem->fetch())
	$arPaySys[$paySystem["ID"]] = $paySystem["NAME"];

if (!isset($arCurrentValues["PAY_SYSTEM_ID_NEW"]))
{
	$newId = \CSalePaySystem::getNewIdsFromOld($arCurrentValues["PAY_SYSTEM_ID"], $arCurrentValues['PERSON_TYPE_ID']);
	$currentValue = current($newId);
}
else
{
	$currentValue = $arCurrentValues["PAY_SYSTEM_ID_NEW"];
}

$arComponentParameters = array(
	"PARAMETERS" => array(
		"PAY_SYSTEM_ID_NEW" => array(
			"NAME" => GetMessage("SOPR_PC"),
			"TYPE" => "LIST",
			"MULTIPLE"=>"N",
			"VALUES" => $arPaySys,
			"COLS"=>25,
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "BASE",
			"DEFAULT" => $currentValue
		)
	)
);
?>