<?
define("ADMIN_MODULE_NAME", "security");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule('security');
IncludeModuleLangFile(__FILE__);

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 **/

$canRead = $USER->CanDoOperation('security_iprule_settings_read');
$canWrite = $USER->CanDoOperation('security_iprule_settings_write');
if(!$canRead && !$canWrite)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$arAccessTypes = array(
	"M" => GetMessage("SEC_IP_LIST_RULE_TYPE_M"),
	"A" => GetMessage("SEC_IP_LIST_RULE_TYPE_A"),
);

$sTableID = "tbl_security_iprule_list";
$oSort = new CAdminSorting($sTableID, "SORT", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = Array(
	"find",
	"find_type",
	"find_rule_type",
	"find_active",
	"find_admin_section",
	"find_site_id",
	"find_name",
	"find_ip",
	"find_path",
);

$lAdmin->InitFilter($FilterArr);

$arFilter = Array(
	"=RULE_TYPE" => $find_rule_type,
	"=ACTIVE" => $find_active,
	"=ADMIN_SECTION" => $find_admin_section,
	"=SITE_ID" => $find_site_id,
	"%NAME" => ($find!="" && $find_type == "name"? $find: $find_name),
	"IP" =>  ($find!="" && $find_type == "ip"? $find: $find_ip),
	"PATH" =>  ($find!="" && $find_type == "path"? $find: $find_path),
);

foreach($arFilter as $key=>$value)
	if(!$value)
		unset($arFilter[$key]);

if($lAdmin->EditAction() && $canWrite)
{
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;
		$cData = new CSecurityIPRule;
		if(!$cData->Update($ID, $arFields))
			$lAdmin->AddGroupError(GetMessage("SEC_IP_LIST_UPDATE_ERROR")." ".$cData->LAST_ERROR, $ID);
	}
}

if(($arID = $lAdmin->GroupAction()) && $canWrite)
{
	if($_REQUEST['action_target']=='selected')
	{
		$cData = new CSecurityIPRule;
		$rsData = $cData->GetList(array('ID'), $arFilter, array());
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if($ID == '')
			continue;
		$ID = intval($ID);
		switch($_REQUEST['action'])
		{
		case "delete":
			if(!CSecurityIPRule::Delete($ID))
				$lAdmin->AddGroupError(GetMessage("SEC_IP_LIST_DELETE_ERROR"), $ID);
			break;
		}
	}
}

$arHeaders = array(
	array(
		"id" => "ID",
		"content" => GetMessage("SEC_IP_LIST_ID"),
		"align" => "right",
		"sort" => "ID",
//		"default" => true,
	),
	array(
		"id" => "RULE_TYPE",
		"content" => GetMessage("SEC_IP_LIST_RULE_TYPE"),
		"align" => "left",
		"sort" => "RULE_TYPE",
//		"default" => true,
	),
	array(
		"id" => "ACTIVE",
		"content" => GetMessage("SEC_IP_LIST_ACTIVE"),
		"align" => "center",
		"default" => true,
	),
	array(
		"id" => "ADMIN_SECTION",
		"content" => GetMessage("SEC_IP_LIST_ADMIN_SECTION"),
		"align" => "center",
//		"default" => true,
	),
	array(
		"id" => "SITE_ID",
		"content" => GetMessage("SEC_IP_LIST_SITE_ID"),
		"align" => "center",
//		"default" => true,
	),
	array(
		"id" => "SORT",
		"content" => GetMessage("SEC_IP_LIST_SORT"),
		"align" => "right",
		"sort" => "SORT",
		"default" => true,
	),
	array(
		"id" => "NAME",
		"content" => GetMessage("SEC_IP_LIST_NAME"),
		"align" => "left",
		"sort" => "NAME",
		"default" => true,
	),
	array(
		"id" => "ACTIVE_FROM",
		"content" => GetMessage("SEC_IP_LIST_ACTIVE_FROM"),
		"align" => "left",
		"sort" => "ACTIVE_FROM",
//		"default" => true,
	),
	array(
		"id" => "ACTIVE_TO",
		"content" => GetMessage("SEC_IP_LIST_ACTIVE_TO"),
		"align" => "left",
		"sort" => "ACTIVE_TO",
//		"default" => true,
	),
	array(
		"id" => "INCL_PATH",
		"content" => GetMessage("SEC_IP_LIST_INCL_PATH"),
		"align" => "left",
		"default" => true,
	),
	array(
		"id" => "EXCL_PATH",
		"content" => GetMessage("SEC_IP_LIST_EXCL_PATH"),
		"align" => "left",
		"default" => true,
	),
	array(
		"id" => "INCL_IP",
		"content" => GetMessage("SEC_IP_LIST_INCL_IP"),
		"align" => "left",
		"default" => true,
	),
	array(
		"id" => "EXCL_IP",
		"content" => GetMessage("SEC_IP_LIST_EXCL_IP"),
		"align" => "left",
		"default" => true,
	),
);

$lAdmin->AddHeaders($arHeaders);

$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();
if(!is_array($arSelectedFields) || (count($arSelectedFields) < 1))
	$arSelectedFields = array(
		"ID",
		"RULE_TYPE",
		"ACTIVE",
		"ADMIN_SECTION",
		"SITE_ID",
		"SORT",
		"NAME",
		"ACTIVE_FROM",
		"ACTIVE_TO",
	);

$arVisibleColumnsMap = array();
foreach($arSelectedFields as $value)
	$arVisibleColumnsMap[$value] = true;

if(array_key_exists("ACTIVE_FROM", $arVisibleColumnsMap))
	$arSelectedFields[] = "ACTIVE_FROM_TIMESTAMP";

if(array_key_exists("ACTIVE_TO", $arVisibleColumnsMap))
	$arSelectedFields[] = "ACTIVE_TO_TIMESTAMP";

$cData = new CSecurityIPRule;
$rsData = $cData->GetList($arSelectedFields, $arFilter, array($by => $order, "ID" => "DESC"));

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("SEC_IP_LIST_PAGER")));

$current_time = time();

while($arRes = $rsData->NavNext(true, "f_")):
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("ADMIN_SECTION", $f_ADMIN_SECTION=="Y"? GetMessage("MAIN_YES"): GetMessage("MAIN_NO"));
	$row->AddViewField("RULE_TYPE", $arAccessTypes[$f_RULE_TYPE]);
	if(
		$f_ACTIVE == "Y"
		&& ($f_ACTIVE_FROM == "" || intval($f_ACTIVE_FROM_TIMESTAMP) < $current_time)
		&& ($f_ACTIVE_TO == "" || intval($f_ACTIVE_TO_TIMESTAMP) > $current_time)
	)
	{
		$row->AddViewField("ACTIVE", '<div class="lamp-green"></div>');
	}
	else
	{
		$row->AddViewField("ACTIVE", '<div class="lamp-red"></div>');
	}

	if(array_key_exists("INCL_PATH", $arVisibleColumnsMap))
	{
		$arMasks = CSecurityIPRule::GetRuleInclMasks($f_ID);
		foreach($arMasks as $i => $mask)
			$arMasks[$i] = htmlspecialcharsex($mask);
		$row->AddViewField("INCL_PATH", implode("<br>", $arMasks));
	}

	if(array_key_exists("EXCL_PATH", $arVisibleColumnsMap))
	{
		$arMasks = CSecurityIPRule::GetRuleExclMasks($f_ID);
		foreach($arMasks as $i => $mask)
			$arMasks[$i] = htmlspecialcharsex($mask);
		$row->AddViewField("EXCL_PATH", implode("<br>", $arMasks));
	}

	if(array_key_exists("INCL_IP", $arVisibleColumnsMap))
	{
		$arIPs = CSecurityIPRule::GetRuleInclIPs($f_ID);
		foreach($arIPs as $i => $ip)
			$arIPs[$i] = htmlspecialcharsex($ip);
		$row->AddViewField("INCL_IP", implode("<br>", $arIPs));
	}

	if(array_key_exists("EXCL_IP", $arVisibleColumnsMap))
	{
		$arIPs = CSecurityIPRule::GetRuleExclIPs($f_ID);
		foreach($arIPs as $i => $ip)
			$arIPs[$i] = htmlspecialcharsex($ip);
		$row->AddViewField("EXCL_IP", implode("<br>", $arIPs));
	}

	if($canWrite)
	{
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("SORT", array("size"=>6));
		$row->AddEditField("SITE_ID", CLang::SelectBox("FIELDS[".$f_ID."][SITE_ID]", $f_SITE_ID, GetMessage("MAIN_ALL")));
		$row->AddInputField("NAME", array("size"=>20));
		$row->AddCalendarField("ACTIVE_FROM");
		$row->AddCalendarField("ACTIVE_TO");
		$row->AddCheckField("ADMIN_SECTION");
	}

	if($canWrite)
	{
		$arActions = array(
			array(
				"ICON" => "edit",
				"DEFAULT" => true,
				"TEXT" => GetMessage("SEC_IP_LIST_EDIT"),
				"ACTION" => $lAdmin->ActionRedirect('security_iprule_edit.php?lang='.LANGUAGE_ID.'&ID='.$f_ID)
			),
			array(
				"ICON" => "delete",
				"TEXT" => GetMessage("SEC_IP_LIST_DELETE"),
				"ACTION" => "if(confirm('".GetMessage("SEC_IP_LIST_DELETE_CONF")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
			),
		);
		$row->AddActions($arActions);
	}

endwhile;

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value"=>$rsData->SelectedRowsCount(),
		),
		array(
			"counter"=>true,
			"title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value"=>"0",
		),
	)
);

$aContext = array();
if($canWrite)
{
	$aContext[] = array(
		"TEXT" => GetMessage("MAIN_ADD"),
		"LINK" => "security_iprule_edit.php?lang=".LANG,
		"TITLE" => GetMessage("SEC_IP_LIST_ADD_TITLE"),
		"ICON" => "btn_new",
	);
}

$lAdmin->AddAdminContextMenu($aContext);

if($canWrite)
{
	$lAdmin->AddGroupActionTable(Array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
	));
}

$message = CSecurityIPRule::CheckAntiFile(true);
if($message)
{
	$lAdmin->BeginPrologContent();
	echo $message->Show();
	$lAdmin->EndPrologContent();
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SEC_IP_LIST_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"find_rule_type" => GetMessage("SEC_IP_LIST_RULE_TYPE"),
		"find_active" => GetMessage("SEC_IP_LIST_ACTIVE"),
		"find_admin_section" => GetMessage("SEC_IP_LIST_ADMIN_SECTION"),
		"find_site_id" => GetMessage("SEC_IP_LIST_SITE_ID"),
		"find_name" => GetMessage("SEC_IP_LIST_NAME"),
		"find_ip" => GetMessage("SEC_IP_LIST_IP"),
		"find_path" => GetMessage("SEC_IP_LIST_PATH"),
	)
);
?>

<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
	<td><b><?echo GetMessage("SEC_IP_LIST_FIND")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?echo GetMessage("SEC_IP_LIST_FIND")?>">
		<?
		$arr = array(
			"reference" => array(
				GetMessage("SEC_IP_LIST_NAME"),
				GetMessage("SEC_IP_LIST_PATH"),
				GetMessage("SEC_IP_LIST_IP"),
			),
			"reference_id" => array(
				"name",
				"path",
				"ip",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("SEC_IP_LIST_RULE_TYPE")?></td>
	<td>
		<select name="find_rule_type">
			<option value=""><?echo GetMessage("MAIN_ALL")?></option>
			<?foreach($arAccessTypes as $key => $value):?>
				<option value="<?echo $key?>" <?if($find_rule_type == $key) echo "selected"?>><?echo $value?></option>
			<?endforeach?>
		</select>
	</td>
</tr>
<?
$arYesNo = array(
	"reference"=>array(GetMessage("MAIN_YES"), GetMessage("MAIN_NO")),
	"reference_id"=>array("Y","N"),
);
?>
<tr>
	<td><?echo GetMessage("SEC_IP_LIST_ACTIVE")?></td>
	<td><?echo SelectBoxFromArray("find_active", $arYesNo, htmlspecialcharsbx($find_active), GetMessage("MAIN_ALL"));?></td>
</tr>
<tr>
	<td><?echo GetMessage("SEC_IP_LIST_ADMIN_SECTION")?></td>
	<td><?echo SelectBoxFromArray("find_admin_section", $arYesNo, htmlspecialcharsbx($find_admin_section), GetMessage("MAIN_ALL"));?></td>
</tr>
<tr>
	<td><?echo GetMessage("SEC_IP_LIST_SITE_ID")?></td>
	<td><?echo CLang::SelectBox("find_site_id", $find_admin_section, GetMessage("MAIN_ALL"));?></td>
</tr>
<tr>
	<td><?echo GetMessage("SEC_IP_LIST_NAME")?></td>
	<td><input type="text" name="find_name" size="47" value="<?echo htmlspecialcharsbx($find_name)?>"></td>
</tr>
<tr>
	<td><?echo GetMessage("SEC_IP_LIST_IP")?></td>
	<td><input type="text" name="find_ip" size="47" value="<?echo htmlspecialcharsbx($find_ip)?>"></td>
</tr>
<tr>
	<td><?echo GetMessage("SEC_IP_LIST_PATH")?></td>
	<td><input type="text" name="find_path" size="47" value="<?echo htmlspecialcharsbx($find_path)?>"></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"find_form"));
$oFilter->End();
?>
</form>

<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
