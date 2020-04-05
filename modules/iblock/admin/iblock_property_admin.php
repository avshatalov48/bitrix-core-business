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

$arIBlock = CIBlock::GetArrayByID($_GET["IBLOCK_ID"]);
if(!is_array($arIBlock))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if(!CIBlockRights::UserHasRightTo($arIBlock["ID"], $arIBlock["ID"], "iblock_edit"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$simpleTypeList = array_fill_keys(Iblock\Helpers\Admin\Property::getBaseTypeList(false), true);

$sTableID = "tbl_iblock_property_admin_".$arIBlock["ID"];
$oSort = new CAdminSorting($sTableID, 'SORT', 'ASC');
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"find_name",
	"find_code",
	"find_active",
	"find_searchable",
	"find_filtrable",
	"find_is_required",
	"find_multiple",
	"find_xml_id",
	"find_property_type",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array(
	"=IBLOCK_ID" => $arIBlock["ID"],
	"?NAME" => $find_name,
	"?CODE" => $find_code,
	"=ACTIVE" => $find_active,
	"=SEARCHABLE" => $find_searchable,
	"=FILTRABLE" => $find_filtrable,
	"=XML_ID" => $find_xml_id,
	"=PROPERTY_TYPE" => $find_property_type,
	"=IS_REQUIRED" => $find_is_required,
	"=MULTIPLE" => $find_multiple,
);
foreach($arFilter as $key => $value)
	if(!strlen(trim($value)))
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
	foreach($FIELDS as $ID => $arFields)
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
	if($_REQUEST['action_target']=='selected')
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
		if(strlen($ID)<=0)
			continue;

		switch($_REQUEST['action'])
		{
		case "delete":
			if(!CIBlockProperty::Delete($ID))
				$lAdmin->AddGroupError(GetMessage("IBP_ADM_DELETE_ERROR"), $ID);
			break;
		case "activate":
		case "deactivate":
			$ibp = new CIBlockProperty();
			$arFields = array(
				"ACTIVE" => ($_REQUEST['action']=="activate"? "Y": "N"),
			);
			if(!$ibp->Update($ID, $arFields))
				$lAdmin->AddUpdateError(GetMessage("IBP_ADM_SAVE_ERROR", array("#ID#"=>$ID, "#ERROR_TEXT#"=>$ibp->LAST_ERROR)), $ID);
			break;
		}
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

$arPropType = Iblock\Helpers\Admin\Property::getBaseTypeList(true);
$arUserTypeList = CIBlockProperty::GetUserType();
Main\Type\Collection::sortByColumn($arUserTypeList, array('DESCRIPTION' => SORT_STRING));
foreach($arUserTypeList as $arUserType)
	$arPropType[$arUserType["PROPERTY_TYPE"].":".$arUserType["USER_TYPE"]] = $arUserType["DESCRIPTION"];

$lAdmin->AddHeaders($arHeader);

$selectFields = array_fill_keys($lAdmin->GetVisibleHeaderColumns(), true);
$selectFields['ID'] = true;
$selectFieldsMap = array_fill_keys(array_keys($arHeader), false);
$selectFieldsMap = array_merge($selectFieldsMap, $selectFields);

if (!isset($by))
	$by = 'SORT';
if (!isset($order))
	$order = 'ASC';

$propertyOrder = array();
if ($by == 'PROPERTY_TYPE')
	$propertyOrder = array('PROPERTY_TYPE' => $order, 'USER_TYPE' => $order);
else
	$propertyOrder = array(strtoupper($by) => strtoupper($order));
if (!isset($propertyOrder['ID']))
	$propertyOrder['ID'] = 'ASC';

$usePageNavigation = true;
$navyParams = array();
if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel')
{
	$usePageNavigation = false;
}
else
{
	$navyParams = CDBResult::GetNavParams(CAdminResult::GetNavSize($sTableID));
	if ($navyParams['SHOW_ALL'])
	{
		$usePageNavigation = false;
	}
	else
	{
		$navyParams['PAGEN'] = (int)$navyParams['PAGEN'];
		$navyParams['SIZEN'] = (int)$navyParams['SIZEN'];
	}
}
if ($selectFields['PROPERTY_TYPE'])
	$selectFields['USER_TYPE'] = true;
$selectFields = array_keys($selectFields);
$getListParams = array(
	'select' => $selectFields,
	'filter' => $arFilter,
	'order' => $propertyOrder
);
if ($usePageNavigation)
{
	$getListParams['limit'] = $navyParams['SIZEN'];
	$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
}
$totalPages = 0;
if ($usePageNavigation)
{
	$countQuery = new Main\Entity\Query(Iblock\PropertyTable::getEntity());
	$countQuery->addSelect(new Main\Entity\ExpressionField('CNT', 'COUNT(1)'));
	$countQuery->setFilter($getListParams['filter']);
	$totalCount = $countQuery->setLimit(null)->setOffset(null)->exec()->fetch();
	unset($countQuery);
	$totalCount = (int)$totalCount['CNT'];
	if ($totalCount > 0)
	{
		$totalPages = ceil($totalCount/$navyParams['SIZEN']);
		if ($navyParams['PAGEN'] > $totalPages)
			$navyParams['PAGEN'] = $totalPages;
		$getListParams['limit'] = $navyParams['SIZEN'];
		$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
	}
	else
	{
		$navyParams['PAGEN'] = 1;
		$getListParams['limit'] = $navyParams['SIZEN'];
		$getListParams['offset'] = 0;
	}
}

$propertyIterator = new CAdminResult(Iblock\PropertyTable::getList($getListParams), $sTableID);
if ($usePageNavigation)
{
	$propertyIterator->NavStart($getListParams['limit'], $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
	$propertyIterator->NavRecordCount = $totalCount;
	$propertyIterator->NavPageCount = $totalPages;
	$propertyIterator->NavPageNomer = $navyParams['PAGEN'];
}
else
{
	$propertyIterator->NavStart();
}

$lAdmin->NavText($propertyIterator->GetNavPrint(GetMessage("IBP_ADM_PAGER")));

while ($property = $propertyIterator->Fetch())
{
	$property['ID'] = (int)$property['ID'];
	$property['USER_TYPE'] = (string)$property['USER_TYPE'];
	if ($property['USER_TYPE'] != '')
		$property['PROPERTY_TYPE'] .= ':'.$property['USER_TYPE'];

	$urlEdit = 'iblock_edit_property.php?ID='.$property['ID'].'&lang='.LANGUAGE_ID."&IBLOCK_ID=".$arIBlock['ID'].($_REQUEST['admin']=="Y"? "&admin=Y": "&admin=N");

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

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$propertyIterator->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

$aContext = array(
	array(
		"ICON"=>"btn_new",
		"TEXT"=>GetMessage("IBP_ADM_TO_ADD"),
		"LINK"=>"iblock_edit_property.php?lang=".LANGUAGE_ID."&IBLOCK_ID=".urlencode($arIBlock["ID"])."&ID=n0".($_REQUEST["admin"]=="Y"? "&admin=Y": "&admin=N"),
		"TITLE"=>GetMessage("IBP_ADM_TO_ADD_TITLE")
	),
);
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->AddGroupActionTable(array(
	"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
	"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
	"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
));

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("IBP_ADM_TITLE", array("#IBLOCK_NAME#" => htmlspecialcharsex($arIBlock["NAME"]))));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form method="GET" action="iblock_admin.php?type=<?=urlencode($type)?>" name="find_form">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"find_code" => GetMessage("IBP_ADM_CODE"),
		"find_active" => GetMessage("IBP_ADM_ACTIVE"),
		"find_searchable" => GetMessage("IBP_ADM_SEARCHABLE"),
		"find_filtrable" => GetMessage("IBP_ADM_FILTRABLE"),
		"find_is_required" => GetMessage("IBP_ADM_IS_REQUIRED"),
		"find_multiple" => GetMessage("IBP_ADM_MULTIPLE"),
		"find_xml_id" => GetMessage("IBP_ADM_XML_ID"),
		"find_property_type" => GetMessage("IBP_ADM_PROPERTY_TYPE"),
	)
);

$oFilter->Begin();

	$arr = array(
		"reference" => array(GetMessage("IBLOCK_YES"), GetMessage("IBLOCK_NO")),
		"reference_id" => array("Y","N"),
	);
?>
	<tr>
		<td><b><?echo GetMessage("IBP_ADM_NAME")?>:</b></td>
		<td><input type="text" name="find_name" value="<?echo htmlspecialcharsbx($find_name)?>" size="40"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBP_ADM_CODE")?>:</td>
		<td><input type="text" name="find_code" value="<?echo htmlspecialcharsbx($find_code)?>" size="40"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBP_ADM_ACTIVE")?>:</td>
		<td><? echo SelectBoxFromArray("find_active", $arr, htmlspecialcharsex($find_active), GetMessage('IBLOCK_ALL')); ?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBP_ADM_SEARCHABLE")?>:</td>
		<td><? echo SelectBoxFromArray("find_searchable", $arr, htmlspecialcharsex($find_searchable), GetMessage('IBLOCK_ALL')); ?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBP_ADM_FILTRABLE")?>:</td>
		<td><? echo SelectBoxFromArray("find_filtrable", $arr, htmlspecialcharsex($find_filtrable), GetMessage('IBLOCK_ALL')); ?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBP_ADM_IS_REQUIRED")?>:</td>
		<td><? echo SelectBoxFromArray("find_is_required", $arr, htmlspecialcharsex($find_is_required), GetMessage('IBLOCK_ALL')); ?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBP_ADM_MULTIPLE")?>:</td>
		<td><? echo SelectBoxFromArray("find_multiple", $arr, htmlspecialcharsex($find_multiple), GetMessage('IBLOCK_ALL')); ?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBP_ADM_XML_ID")?>:</td>
		<td><input type="text" name="find_xml_id" value="<?echo htmlspecialcharsbx($find_xml_id)?>" size="40"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBP_ADM_PROPERTY_TYPE")?>:</td>
		<td><? echo SelectBoxFromArray("find_property_type", array(
			"reference_id" => array_keys($arPropType),
			"reference" => array_values($arPropType),
		), htmlspecialcharsex($find_filtrable), GetMessage('IBLOCK_ALL')); ?></td>
	</tr>
<?
$oFilter->Buttons(array(
	"table_id"=>$sTableID,
	"url"=>$APPLICATION->GetCurPage().'?IBLOCK_ID='.urlencode($arIBlock["ID"]),
	"form"=>"find_form",
));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");