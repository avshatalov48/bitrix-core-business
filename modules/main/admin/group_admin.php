<?
/*
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "users/group_admin.php");
if (!$USER->CanDoOperation('view_groups'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/admin/group_admin.php");
$err_mess = "File: ".__FILE__."<br>Line: ";

// идентификатор таблицы
$sTableID = "tbl_user_group";

// инициализация сортировки
$oSort = new CAdminSorting($sTableID, "c_sort", "asc");
// инициализация списка
$lAdmin = new CAdminList($sTableID, $oSort);


// инициализация параметров списка - фильтры
$arFilterFields = Array(
	"find",
	"find_type",
	"find_id",
	"find_timestamp_1",
	"find_timestamp_2",
	"find_active",
	"find_name",
	"find_description",
	"find_users_1",
	"find_users_2"
	);

$lAdmin->InitFilter($arFilterFields);

function CheckFilter() // проверка введенных полей
{
	global $strError, $find_timestamp_1, $find_timestamp_2;
	$str = "";

	if (strlen(trim($find_timestamp_1))>0 || strlen(trim($find_timestamp_2))>0)
	{
		$date_1_ok = false;
		$date1_stm = MkDateTime(FmtDate($find_timestamp_1,"D.M.Y"),"d.m.Y");
		$date2_stm = MkDateTime(FmtDate($find_timestamp_2,"D.M.Y")." 23:59","d.m.Y H:i");
		if (!$date1_stm && strlen(trim($find_timestamp_1))>0)
			$str.= GetMessage("MAIN_WRONG_DATE_FROM")."<br>";
		else $date_1_ok = true;
		if (!$date2_stm && strlen(trim($find_timestamp_2))>0)
			$str.= GetMessage("MAIN_WRONG_DATE_TILL")."<br>";
		elseif ($date_1_ok && $date2_stm <= $date1_stm && strlen($date2_stm)>0)
			$str.= GetMessage("MAIN_FROM_TILL_DATE")."<br>";
	}
	$strError .= $str;
	if(strlen($str)>0)
	{
		global $lAdmin;
		$lAdmin->AddFilterError($str);
		return false;
	}

	return true;

}

$arFilter = Array();
if(CheckFilter($arFilterFields))
{
	$arFilter = Array(
		"ID"			=> ($find!='' && $find_type == "id"? $find: $find_id),
		"TIMESTAMP_1"	=> $find_timestamp_1,
		"TIMESTAMP_2"	=> $find_timestamp_2,
		"ACTIVE"		=> $find_active,
		"NAME"		=> ($find!='' && $find_type == "name"? $find: $find_name),
		"DESCRIPTION"	=> $find_description,
		"USERS_1"		=> $find_users_1,
		"USERS_2"		=> $find_users_2
		);
}

// обработка редактирования (права доступа!)
if($lAdmin->EditAction() && $USER->CanDoOperation('edit_groups'))
{
	foreach($FIELDS as $ID=>$arFields)
	{
		$DB->StartTransaction();
		$ID = IntVal($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		$ob = new CGroup();
		if(!$ob->Update($ID, $arFields))
		{
			$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".$ob->LAST_ERROR, $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}

// обработка действий групповых и одиночных
if(($arID = $lAdmin->GroupAction()) && $USER->CanDoOperation('edit_groups'))
{
	if($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$rsData = CGroup::GetList($by, $order, Array());
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;

		switch($_REQUEST['action'])
		{
		case "delete":
			if($ID>2)
			{
				@set_time_limit(0);
				$DB->StartTransaction();
				$group = new CGroup();
				if(!$group -> Delete($ID))
				{
					$DB->Rollback();
					$lAdmin->AddGroupError(GetMessage("DELETE_ERROR"), $ID);
				}
				$DB->Commit();
			}
			else
				$lAdmin->AddGroupError(GetMessage("MAIN_ERROR_GROUP").$ID.GetMessage("MAIN_ERROR_GROUP_DELETE"));
			break;
		case "activate":
		case "deactivate":
			if($ID>2)
			{
				$ob = new CGroup();
				$arFields["ACTIVE"] = ($_REQUEST['action']=="activate"?"Y":"N");
				if(!$ob->Update($ID, $arFields))
					$lAdmin->AddGroupError(GetMessage("MAIN_EDIT_ERROR").":".$ob->LAST_ERROR, $ID);
			}
			else
				$lAdmin->AddGroupError(GetMessage("MAIN_ERROR_GROUP").$ID.GetMessage("MAIN_ERROR_GROUP_EDIT"));
			break;
		}
	}
}

// заголовок списка
$lAdmin->AddHeaders(array(
	array("id"=>"ID",				"content"=>"ID", 	"sort"=>"id", "default"=>true, "align"=>"right"),
	array("id"=>"TIMESTAMP_X",		"content"=>GetMessage('TIMESTAMP'), "sort"=>"timestamp_x", "default"=>true),
	array("id"=>"ACTIVE", 			"content"=>GetMessage('ACTIVE'),	"sort"=>"active", "default"=>true),
	array("id"=>"C_SORT", 			"content"=>GetMessage("MAIN_C_SORT"),  "sort"=>"c_sort", "default"=>true, "align"=>"right"),
	array("id"=>"NAME",				"content"=>GetMessage("NAME"), "sort"=>"name",	"default"=>true),
	array("id"=>"DESCRIPTION", 		"content"=>GetMessage("MAIN_DESCRIPTION"),  "sort"=>"description", "default"=>false),
	array("id"=>"USERS", 			"content"=>GetMessage('MAIN_USERS'),  "sort"=>"users", "align"=>"right"),
));

$showUserCount = in_array("USERS", $lAdmin->GetVisibleHeaderColumns());

// инициализация списка - выборка данных
$rsData = CGroup::GetList($by, $order, $arFilter, ($showUserCount? "Y" : "N"));
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// установке параметров списка
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("PAGES")));

// построение списка
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes, "group_edit.php?lang=".LANGUAGE_ID."&ID=".$f_ID, GetMessage("MAIN_EDIT_TITLE"));
	$row->AddViewField("ID", "<a href='group_edit.php?lang=".LANGUAGE_ID."&ID=".$f_ID."' title='".GetMessage("MAIN_EDIT_TITLE")."'>".$f_ID."</a>");


	if ($USER->CanDoOperation('edit_groups'))
	{
		if($f_ID <= 2)
			$row->AddCheckField("ACTIVE", false);
		else
			$row->AddCheckField("ACTIVE");

		$row->AddInputField("C_SORT");
		$row->AddInputField("NAME");
		$row->AddInputField("DESCRIPTION");
	}
	else
	{
		$row->AddCheckField("ACTIVE", false);
		$row->AddViewField("C_SORT", $f_C_SORT);
		$row->AddViewField("NAME", $f_NAME);
		$row->AddViewField("DESCRIPTION", $f_DESCRIPTION);
	}

	if ($f_ID!=2)
		$row->AddViewField("USERS", "<a href='user_admin.php?lang=".LANGUAGE_ID."&GROUPS_ID[]=".$f_ID."&apply_filter=Y' title='".GetMessage("USERS_OF_GROUP")."'>".$f_USERS."</a>");

	$arActions = Array();

	if(IntVal($f_ID)>2 && $USER->CanDoOperation('edit_groups'))
	{
		$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("MAIN_ADMIN_MENU_EDIT"), "ACTION"=>$lAdmin->ActionRedirect("group_edit.php?ID=".$f_ID));
		$arActions[] = array("ICON"=>"copy", "TEXT"=>GetMessage("MAIN_ADMIN_MENU_COPY"), "ACTION"=>$lAdmin->ActionRedirect("group_edit.php?COPY_ID=".$f_ID));
		$arActions[] = array("SEPARATOR"=>true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"), "ACTION"=>"if(confirm('".GetMessage('CONFIRM_DEL_GROUP')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}
	else
	{
		$arActions[] = array("ICON" => "view", "TEXT" => GetMessage("VIEW"), "ACTION" => $lAdmin->ActionRedirect("group_edit.php?ID=".$f_ID));
	}

	$row->AddActions($arActions);
}

$aContext = array();
if ($USER->CanDoOperation('edit_groups'))
{
	// показ формы с кнопками добавления, ...
	$lAdmin->AddGroupActionTable(Array(
		"delete"=>true,
		"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE")
		));

	$aContext[] = array(
			"TEXT"	=> GetMessage("ADD_GROUP"),
			"LINK"	=> "group_edit.php?lang=".LANGUAGE_ID,
			"TITLE"	=> GetMessage("ADD_GROUP_TITLE"),
			"ICON"	=> "btn_new"
		);
}
$lAdmin->AddAdminContextMenu($aContext);

// проверка на вывод только списка (в случае списка, скрипт дальше выполняться не будет)
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("TITLE"));

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage('MAIN_F_ID'),
		GetMessage('MAIN_F_TIMESTAMP'),
		GetMessage('MAIN_F_ACTIVE'),
		GetMessage('F_NAME'),
		GetMessage('MAIN_F_DESCRIPTION'),
		GetMessage('MAIN_F_USERS'),
	)
);

$oFilter->Begin();
?>
<tr>
	<td><b><?=GetMessage("MAIN_FLT_SEARCH")?></b></td>
	<td nowrap>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("MAIN_FLT_SEARCH_TITLE")?>">
		<select name="find_type">
			<option value="name"<?if($find_type=="name") echo " selected"?>><?=GetMessage('F_NAME')?></option>
			<option value="id"<?if($find_type=="id") echo " selected"?>><?=GetMessage('MAIN_F_ID')?></option>
		</select>
	</td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("MAIN_F_ID")?>:</td>
	<td nowrap><input type="text" name="find_id" value="<?echo htmlspecialcharsbx($find_id)?>" size="47"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td width="0%" nowrap><?echo GetMessage("MAIN_F_TIMESTAMP").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_timestamp_1", htmlspecialcharsbx($find_timestamp_1), "find_timestamp_2", htmlspecialcharsbx($find_timestamp_2), "find_form","Y")?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("MAIN_F_ACTIVE")?>:</td>
	<td nowrap><?
		$arr = array("reference"=>array(GetMessage("MAIN_YES"), GetMessage("MAIN_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_active", $arr, htmlspecialcharsbx($find_active), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("F_NAME")?>:</td>
	<td nowrap><input type="text" name="find_name" value="<?echo htmlspecialcharsbx($find_name)?>" size="47"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("MAIN_F_DESCRIPTION")?>:</td>
	<td nowrap><input type="text" name="find_description" value="<?echo htmlspecialcharsbx($find_description)?>" size="47"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_F_USERS")?>:</td>
	<td><input type="text" name="find_users_1" size="10" value="<?echo htmlspecialcharsbx($find_users_1)?>" placeholder="<?echo GetMessage("group_admin_flt_from")?>">&nbsp;<input type="text" name="find_users_2" size="10" value="<?echo htmlspecialcharsbx($find_users_2)?>" placeholder="<?echo GetMessage("group_admin_flt_to")?>"></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"find_form"));
$oFilter->End();
?>
</form>
<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>