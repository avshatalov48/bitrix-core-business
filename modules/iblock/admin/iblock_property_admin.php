<?
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
use Bitrix\Main\Loader,
	Bitrix\Main,
	Bitrix\Iblock;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule('iblock');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

$arIBlock = CIBlock::GetArrayByID($_GET["IBLOCK_ID"]);
if(!is_array($arIBlock))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if(!CIBlockRights::UserHasRightTo($arIBlock["ID"], $arIBlock["ID"], "iblock_edit"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$simpleTypeList = array_fill_keys(Iblock\Helpers\Admin\Property::getBaseTypeList(false), true);

$sTableID = "tbl_iblock_property_admin_".$arIBlock["ID"];
$oSort = new CAdminUiSorting($sTableID, 'SORT', 'ASC');
$lAdmin = new CAdminUiList($sTableID, $oSort);

$arPropType = Iblock\Helpers\Admin\Property::getBaseTypeList(true);
$arUserTypeList = CIBlockProperty::GetUserType();
Main\Type\Collection::sortByColumn($arUserTypeList, array('DESCRIPTION' => SORT_STRING));
foreach($arUserTypeList as $arUserType)
	$arPropType[$arUserType["PROPERTY_TYPE"].":".$arUserType["USER_TYPE"]] = $arUserType["DESCRIPTION"];

$filterFields = array(
	array(
		"id" => "NAME",
		"name" => GetMessage("IBP_ADM_NAME"),
		"filterable" => "",
		"quickSearch" => "?",
		"default" => true
	),
	array(
		"id" => "CODE",
		"name" => GetMessage("IBP_ADM_CODE"),
		"filterable" => "?"
	),
	array(
		"id" => "ACTIVE",
		"name" => GetMessage("IBP_ADM_ACTIVE"),
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("IBLOCK_YES"),
			"N" => GetMessage("IBLOCK_NO")
		),
		"filterable" => "="
	),
	array(
		"id" => "SEARCHABLE",
		"name" => GetMessage("IBP_ADM_SEARCHABLE"),
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("IBLOCK_YES"),
			"N" => GetMessage("IBLOCK_NO")
		),
		"filterable" => "="
	),
	array(
		"id" => "FILTRABLE",
		"name" => GetMessage("IBP_ADM_FILTRABLE"),
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("IBLOCK_YES"),
			"N" => GetMessage("IBLOCK_NO")
		),
		"filterable" => "="
	),
	array(
		"id" => "IS_REQUIRED",
		"name" => GetMessage("IBP_ADM_IS_REQUIRED"),
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("IBLOCK_YES"),
			"N" => GetMessage("IBLOCK_NO")
		),
		"filterable" => "="
	),
	array(
		"id" => "MULTIPLE",
		"name" => GetMessage("IBP_ADM_MULTIPLE"),
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("IBLOCK_YES"),
			"N" => GetMessage("IBLOCK_NO")
		),
		"filterable" => "="
	),
	array(
		"id" => "XML_ID",
		"name" => GetMessage("IBP_ADM_XML_ID"),
		"filterable" => "="
	),
	array(
		"id" => "PROPERTY_TYPE",
		"name" => GetMessage("IBP_ADM_PROPERTY_TYPE"),
		"type" => "list",
		"items" => $arPropType,
		"filterable" => "="
	),
);

$arFilter = array("=IBLOCK_ID" => $arIBlock["ID"]);

$lAdmin->AddFilter($filterFields, $arFilter);

foreach($arFilter as $key => $value)
	if(trim($value) == '')
		unset($arFilter[$key]);
if (isset($arFilter['=PROPERTY_TYPE']))
{
	if (!isset($simpleTypeList[$arFilter['=PROPERTY_TYPE']]))
		list($arFilter['=PROPERTY_TYPE'], $arFilter['=USER_TYPE']) = explode(':', $arFilter['=PROPERTY_TYPE'], 2);
	else
		$arFilter['=USER_TYPE'] = null;
}

if($lAdmin->EditAction())
{
	foreach($_REQUEST['FIELDS'] as $ID => $arFields)
	{
		$DB->StartTransaction();
		$ID = (int)$ID;

		if(!$lAdmin->IsUpdated($ID))
			continue;

		if (isset($arFields['PROPERTY_TYPE']))
		{
			$arFields["USER_TYPE"] = false;
			if (!isset($simpleTypeList[$arFields['PROPERTY_TYPE']]))
				list($arFields["PROPERTY_TYPE"], $arFields["USER_TYPE"]) = explode(':', $arFields["PROPERTY_TYPE"], 2);
		}

		$ibp = new CIBlockProperty;
		if(!$ibp->Update($ID, $arFields))
		{
			$lAdmin->AddUpdateError(GetMessage("IBP_ADM_SAVE_ERROR", array("#ID#"=>$ID, "#ERROR_TEXT#"=>$ibp->LAST_ERROR)), $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}

if($arID = $lAdmin->GroupAction())
{
	if ($lAdmin->IsGroupActionToAll())
	{
		$propertyIterator = Iblock\PropertyTable::getList(array(
			'select' => array('ID'),
			'filter' => $arFilter
		));
		while ($property = $propertyIterator->fetch())
			$arID[] = $property['ID'];
		unset($property, $propertyIterator);
	}

	foreach($arID as $ID)
	{
		if($ID == '')
			continue;

		switch($_REQUEST['action'])
		{
		case "delete":
			if(!CIBlockProperty::Delete($ID))
			{
				$exception = $APPLICATION->getException();
				if ($exception)
				{
					$lAdmin->AddGroupError($exception->GetString(), $ID);
				}
				else
				{
					$lAdmin->AddGroupError(GetMessage("IBP_ADM_DELETE_ERROR"), $ID);
				}
			}
			break;
		case "activate":
		case "deactivate":
			$ibp = new CIBlockProperty();
			$arFields = array(
				"ACTIVE" => ($_REQUEST['action']=="activate"? "Y": "N"),
			);
			if(!$ibp->Update($ID, $arFields))
				$lAdmin->AddGroupError(GetMessage("IBP_ADM_SAVE_ERROR", array("#ID#"=>$ID, "#ERROR_TEXT#"=>$ibp->LAST_ERROR)), $ID);
			break;
		}
	}

	if ($lAdmin->hasGroupErrors())
	{
		$adminSidePanelHelper->sendJsonErrorResponse($lAdmin->getGroupErrors());
	}
	else
	{
		$adminSidePanelHelper->sendSuccessResponse();
	}
}

$arHeader = array(
	array(
		"id"=>"ID",
		"content"=>GetMessage("IBP_ADM_ID"),
		"sort"=>"ID",
		"align"=>"right",
		"default"=>true,
	),
	array(
		"id"=>"NAME",
		"content"=>GetMessage("IBP_ADM_NAME"),
		"sort"=>"NAME",
		"default"=>true,
	),
	array(
		"id"=>"CODE",
		"content"=>GetMessage("IBP_ADM_CODE"),
		"sort" => "CODE",
		"default"=>true,
	),
	array(
		"id"=>"PROPERTY_TYPE",
		"content"=>GetMessage("IBP_ADM_PROPERTY_TYPE"),
		"sort" => "PROPERTY_TYPE",
		"default"=>true,
	),
	array(
		"id"=>"SORT",
		"content"=>GetMessage("IBP_ADM_SORT"),
		"sort"=>"SORT",
		"align"=>"right",
		"default"=>true,
	),
	array(
		"id"=>"ACTIVE",
		"content"=>GetMessage("IBP_ADM_ACTIVE"),
		"sort"=>"ACTIVE",
		"align"=>"center",
		"default"=>true,
	),
	array(
		"id"=>"IS_REQUIRED",
		"content"=>GetMessage("IBP_ADM_IS_REQUIRED"),
		"sort" => "IS_REQUIRED",
		"align"=>"center",
		"default"=>true,
	),
	array(
		"id"=>"MULTIPLE",
		"content"=>GetMessage("IBP_ADM_MULTIPLE"),
		"sort" => "MULTIPLE",
		"align"=>"center",
		"default"=>true,
	),
	array(
		"id"=>"SEARCHABLE",
		"content"=>GetMessage("IBP_ADM_SEARCHABLE"),
		"sort"=>"SEARCHABLE",
		"align"=>"center",
		"default"=>true,
	),
	array(
		"id"=>"FILTRABLE",
		"content"=>GetMessage("IBP_ADM_FILTRABLE"),
		"sort"=>"FILTRABLE",
		"align"=>"center",
	),
	array(
		"id"=>"XML_ID",
		"content"=>GetMessage("IBP_ADM_XML_ID"),
		"sort" => "XML_ID"
	),
	array(
		"id"=>"WITH_DESCRIPTION",
		"content"=>GetMessage("IBP_ADM_WITH_DESCRIPTION"),
		"sort" => "WITH_DESCRIPTION",
		"align"=>"center",
	),
	array(
		"id"=>"HINT",
		"content"=>GetMessage("IBP_ADM_HINT"),
	),
);

$lAdmin->AddHeaders($arHeader);

$selectFields = array_fill_keys($lAdmin->GetVisibleHeaderColumns(), true);
$selectFields['ID'] = true;
$selectFieldsMap = array_fill_keys(array_keys($arHeader), false);
$selectFieldsMap = array_merge($selectFieldsMap, $selectFields);

global $by, $order;
if (!isset($by))
	$by = 'SORT';
if (!isset($order))
	$order = 'ASC';

$propertyOrder = array();
if ($by == 'PROPERTY_TYPE')
	$propertyOrder = array('PROPERTY_TYPE' => $order, 'USER_TYPE' => $order);
else
	$propertyOrder = array(mb_strtoupper($by) => mb_strtoupper($order));
if (!isset($propertyOrder['ID']))
	$propertyOrder['ID'] = 'ASC';

if ($selectFields['PROPERTY_TYPE'])
	$selectFields['USER_TYPE'] = true;
$selectFields = array_keys($selectFields);
$getListParams = array(
	'select' => $selectFields,
	'filter' => $arFilter,
	'order' => $propertyOrder
);

$propertyIterator = new CAdminUiResult(Iblock\PropertyTable::getList($getListParams), $sTableID);
$propertyIterator->NavStart();

$lAdmin->SetNavigationParams($propertyIterator, array("BASE_LINK" => $selfFolderUrl."iblock_property_admin.php"));

while ($property = $propertyIterator->Fetch())
{
	$property['ID'] = (int)$property['ID'];
	$property['USER_TYPE'] = (string)$property['USER_TYPE'];
	if ($property['USER_TYPE'] != '')
		$property['PROPERTY_TYPE'] .= ':'.$property['USER_TYPE'];

	$urlEdit = $selfFolderUrl.'iblock_edit_property.php?ID='.$property['ID'].'&lang='.LANGUAGE_ID."&IBLOCK_ID=".
		$arIBlock['ID'].($_REQUEST['admin']=="Y"? "&admin=Y": "&admin=N");
	$urlEdit = $adminSidePanelHelper->editUrlToPublicPage($urlEdit);

	$row = &$lAdmin->AddRow($property['ID'], $property, $urlEdit);
	$row->AddViewField('ID', $property['ID']);
	if ($selectFieldsMap['NAME'])
	{
		$row->AddInputField('NAME', array('size' => 50, 'maxlength' => 255));
		$row->AddViewField('NAME', '<a href="'.$urlEdit.'">'.htmlspecialcharsex($property['NAME']).'</a>');
	}
	if ($selectFieldsMap['CODE'])
		$row->AddInputField('CODE', array('size' => 20, 'maxlength' => 50));
	if ($selectFieldsMap['SORT'])
		$row->AddInputField('SORT', array('size' => 5));
	if ($selectFieldsMap['ACTIVE'])
		$row->AddCheckField('ACTIVE');
	if ($selectFieldsMap['MULTIPLE'])
		$row->AddCheckField('MULTIPLE');
	if ($selectFieldsMap['XML_ID'])
		$row->AddInputField('XML_ID');
	if ($selectFieldsMap['WITH_DESCRIPTION'])
		$row->AddCheckField('WITH_DESCRIPTION');
	if ($selectFieldsMap['SEARCHABLE'])
		$row->AddCheckField('SEARCHABLE');
	if ($selectFieldsMap['FILTRABLE'])
		$row->AddCheckField('FILTRABLE');
	if ($selectFieldsMap['FILTRABLE'])
		$row->AddCheckField('FILTRABLE');
	if ($selectFieldsMap['IS_REQUIRED'])
		$row->AddCheckField('IS_REQUIRED');
	if ($selectFieldsMap['HINT'])
		$row->AddInputField('HINT');
	if ($selectFieldsMap['PROPERTY_TYPE'])
		$row->AddSelectField('PROPERTY_TYPE', $arPropType);

	$arActions = array(
		array(
			'ICON' => 'edit',
			'TEXT' => GetMessage('MAIN_ADMIN_MENU_EDIT'),
			'DEFAULT' => true,
			'ACTION' => $lAdmin->ActionRedirect($urlEdit),
		),
		array(
			'ICON' => 'delete',
			'TEXT' => GetMessage('MAIN_ADMIN_MENU_DELETE'),
			'ACTION' => "if(confirm('".GetMessageJS("IBP_ADM_CONFIRM_DEL_MESSAGE")."')) ".$lAdmin->ActionDoGroup($property['ID'], "delete", "&IBLOCK_ID=".$arIBlock['ID']."&lang=".LANGUAGE_ID),
		),
	);
	$row->AddActions($arActions);

	unset($row, $urlEdit);
}
unset($property);

$addUrl = $selfFolderUrl."iblock_edit_property.php?lang=".LANGUAGE_ID."&IBLOCK_ID=".urlencode($arIBlock["ID"]).
	"&ID=n0".($_REQUEST["admin"]=="Y"? "&admin=Y": "&admin=N");
$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
$aContext = array(
	array(
		"ICON"=>"btn_new",
		"TEXT"=>GetMessage("IBP_ADM_TO_ADD"),
		"LINK"=>$addUrl,
		"TITLE"=>GetMessage("IBP_ADM_TO_ADD_TITLE")
	),
);
$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."iblock_property_admin.php"));
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->AddGroupActionTable(array(
	"edit" => GetMessage("MAIN_ADMIN_LIST_EDIT"),
	"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
	"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
	"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	"for_all" => true
));

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("IBP_ADM_TITLE", array("#IBLOCK_NAME#" => htmlspecialcharsex($arIBlock["NAME"]))));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");