<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arYesNo = Array(
	"Y" => GetMessage("SUP_DESC_YES"),
	"N" => GetMessage("SUP_DESC_NO"),
);

if(!CModule::IncludeModule("iblock"))
	return;

$arTypesEx = Array("-"=>" ");
$db_iblock_type = CIBlockType::GetList(Array("SORT"=>"ASC"));
while($arRes = $db_iblock_type->Fetch())
	if($arIBType = CIBlockType::GetByIDLang($arRes["ID"], LANG))
		$arTypesEx[$arRes["ID"]] = $arIBType["NAME"];

$arIBlocks=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"?$arCurrentValues["IBLOCK_TYPE"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocks[$arRes["ID"]] = $arRes["NAME"];

$arProperty_LNS = array();
$rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>(isset($arCurrentValues["IBLOCK_ID"])?$arCurrentValues["IBLOCK_ID"]:$arCurrentValues["ID"])));
while ($arr=$rsProp->Fetch())
{
	$arProperty[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	if (in_array($arr["PROPERTY_TYPE"], array("L", "N", "S")))
	{
		if ($arr['MULTIPLE']=='Y')
			$arProperty_M[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
		else
			$arProperty_S[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	}
}

global $USER_FIELD_MANAGER;
$SHOW_USER_FIELD = array( ""=>"" );
$arrUF = $USER_FIELD_MANAGER->GetUserFields( "SUPPORT", 0, LANGUAGE_ID );
foreach( $arrUF as $FIELD_ID => $arField )
{
	$SHOW_USER_FIELD[$FIELD_ID] = $arField["EDIT_FORM_LABEL"];
}


$arComponentParameters = array(
	"GROUPS" => array(
		"SECTIONS_TO_CATEGORIES" => array(
			"NAME" => GetMessage("WZ_GRP_SECTIONS_TO_CATEGORIES"),
		)
	),
	"PARAMETERS" => array(

		"VARIABLE_ALIASES" => Array(
			"ID" => Array("NAME" => GetMessage("SUP_TICKET_ID_DESC"))
		),

		"IBLOCK_TYPE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("WZ_IBTYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arTypesEx,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("WZ_IBLOCK"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => '={$_REQUEST["ID"]}',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
		"PROPERTY_FIELD_TYPE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("WZ_PROPERTY"),
			"TYPE" => "LIST",
			"VALUES" => $arProperty_S,
		),
		"PROPERTY_FIELD_VALUES" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("WZ_PROPERTY_VALUES"),
			"TYPE" => "LIST",
			"VALUES" => $arProperty_M,
		),
		"AJAX_MODE" => array(),
		"INCLUDE_IBLOCK_INTO_CHAIN" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("WZ_INCLUDE_INTO_CHAIN"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
/*		"SEF_MODE" => Array(
			"ticket_list" => Array(
				"NAME" => GetMessage("SUP_TICKET_LIST_DESC"),
				"DEFAULT" => "index.php",
				"VARIABLES" => array()
			),

			"ticket_edit" => Array(
				"NAME" => GetMessage("SUP_TICKET_EDIT_DESC"),
				"DEFAULT" => "#ID#.php",
				"VARIABLES" => array("ID")
			),
		),
*/
		"TICKETS_PER_PAGE" => Array(
			"NAME" => GetMessage("SUP_LIST_TICKETS_PER_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT" => "50"
		),

		"MESSAGES_PER_PAGE" => Array(
			"NAME" => GetMessage("SUP_EDIT_MESSAGES_PER_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT" => "20"
		),

		"MESSAGE_MAX_LENGTH" => Array(
			"NAME" => GetMessage("SUP_MESSAGE_MAX_LENGTH"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT" => "70"
		),
			
		"MESSAGE_SORT_ORDER" => Array(
			"NAME" => GetMessage("SUP_MESSAGE_SORT_ORDER"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"VALUES" =>Array(
				"asc"=>GetMessage("SUP_SORT_ASC"),
				"desc"=>GetMessage("SUP_SORT_DESC")
			),
		),
		
		"SET_PAGE_TITLE" => Array(
			"NAME"=>GetMessage("SUP_SET_PAGE_TITLE"), 
			"TYPE"=>"LIST", 
			"MULTIPLE"=>"N", 
			"DEFAULT"=>"Y", 
			"PARENT" => "ADDITIONAL_SETTINGS",
			"VALUES"=>$arYesNo, 
			"ADDITIONAL_VALUES"=>"N"
		),

		"TEMPLATE_TYPE" => Array(
			"NAME"=>GetMessage("WZ_TEMPLATE"),
			"TYPE"=>"LIST",
			"MULTIPLE"=>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"VALUES"=>Array(
				"standard"=>GetMessage("WZ_STANDARD"),
				".default"=>GetMessage("WZ_DEFAULT")
			)
		),
		
		"SHOW_RESULT" => Array(
			"NAME"=>GetMessage("WZ_SHOW_RESULT"), 
			"TYPE"=>"CHECKBOX", 
			"DEFAULT"=>"Y", 
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"SHOW_COUPON_FIELD" => Array(
			"NAME" => GetMessage("SUP_SHOW_COUPON_FIELD"),
			"TYPE" => "CHECKBOX",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT" => "N",
		),
		
		"SECTIONS_TO_CATEGORIES" => Array(
			"PARENT" => "SECTIONS_TO_CATEGORIES",
			"NAME" => GetMessage("SECTIONS_TO_CATEGORIES"),
			"TYPE" => "CHECKBOX",
			"REFRESH" => "Y"
		),
		
		"SET_SHOW_USER_FIELD" => Array(
			"NAME"=>GetMessage("SUP_SHOW_USER_FIELD"), 
			"TYPE"=>"LIST", 
			"MULTIPLE"=>"Y", 
			"PARENT" => "ADDITIONAL_SETTINGS", 
			"VALUES"=>$SHOW_USER_FIELD
		),

	)
);

if ($arCurrentValues['SECTIONS_TO_CATEGORIES']=='Y')
{
	if(!CModule::IncludeModule('support'))
		return;

	$arSections = array();
	$rs = CIBlockSection::GetList(Array("left_margin"=>"ASC","SORT"=>"ASC"),Array("IBLOCK_ID"=>$arCurrentValues['IBLOCK_ID']));
	while($f = $rs->GetNext())
	{
		$arSectionsDot[$f['ID']] = str_repeat(" . ",$f['DEPTH_LEVEL']-1).$f['NAME'];
		$arSections[$f['ID']] = $f['NAME'];
	}

	$arComponentParameters['PARAMETERS']['SELECTED_SECTIONS'] = array(
		"PARENT" => "SECTIONS_TO_CATEGORIES",
		"NAME" =>GetMessage('WZ_SELECT_SECTIONS'),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $arSectionsDot,
		"REFRESH" => "Y"
	);

	$arSelectedSections = $arCurrentValues['SELECTED_SECTIONS'];
	if (is_array($arSelectedSections) && count($arSelectedSections)>0)
	{
		$arCategories = array();
		$rs = CTicketDictionary::GetList($by,$order,array("TYPE"=>"C"),$is_filtered);
		while($f = $rs->GetNext())
			$arCategories[$f['ID']] = $f['NAME'];
	
		foreach($arSelectedSections as $k)
			if ($k)
				$arComponentParameters['PARAMETERS']['SECTION_'.$k] = array(
					"PARENT" => "SECTIONS_TO_CATEGORIES",
					"NAME"=>$arSections[$k],
					"TYPE" => "LIST",
					"VALUES" => $arCategories
				);	
	}
}

?>
