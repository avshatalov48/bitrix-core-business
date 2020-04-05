<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

//http://jabber.bx/view.php?id=25340
$arComponentVariables = array('message_id','group_id','wiki_name');

if ($arParams['SEF_MODE'] != 'Y')
	return;

if(!CModule::IncludeModule('forum'))
	return;

$dbMessage = CForumMessage::GetList(array(), array('ID' => intval($arResult['VARIABLES']['message_id'])));

if (!$dbMessage || !($arMessage = $dbMessage->Fetch()))
	return;

$elementID = intval($arMessage['PARAM2']);

if ($elementID <= 0)
	return;

$dbElement = CIBlockElement::GetList(array(), array('ID' => $elementID),false,false,array("IBLOCK_SECTION_ID","IBLOCK_ID","NAME") );

if (!$dbElement || !($arElement = $dbElement->Fetch()) || !($arElement['IBLOCK_ID'] == COption::GetOptionString('wiki', 'socnet_iblock_id')))
	return;

$ibSectionID = $arElement['IBLOCK_SECTION_ID'];

do
{
	$dbIBSection = CIBlockSection::GetList(array(),array('ID' => $ibSectionID, 'CHECK_PERMISSIONS' => 'N'),false,false,array('IBLOCK_SECTION_ID','SOCNET_GROUP_ID'));

	if (!$dbIBSection || !($arIBSection = $dbIBSection->Fetch()))
		break;

	$ibSectionID = $arIBSection['IBLOCK_SECTION_ID'];

} while (!$arIBSection['SOCNET_GROUP_ID'] && $arIBSection['IBLOCK_SECTION_ID']);


$redirectPath = CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_WIKI_POST_CATEGORY'],
															array(
																"wiki_name" => rawurlencode($arElement['NAME']),
																"group_id"	=> $arIBSection['SOCNET_GROUP_ID']
																)
															);

$redirectPath .= "?MID=".$arResult['VARIABLES']['message_id']."#message".$arResult['VARIABLES']['message_id'];

LocalRedirect($redirectPath);

?>
