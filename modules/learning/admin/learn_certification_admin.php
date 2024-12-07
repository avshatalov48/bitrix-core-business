<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if (!CModule::IncludeModule('learning'))
{
	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php'); // second system's prolog

	if (IsModuleInstalled('learning') && defined('LEARNING_FAILED_TO_LOAD_REASON'))
		echo LEARNING_FAILED_TO_LOAD_REASON;
	else
		CAdminMessage::ShowMessage(GetMessage('LEARNING_MODULE_NOT_FOUND'));

	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');	// system's epilog
	exit();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/prolog.php");
IncludeModuleLangFile(__FILE__);

ClearVars();

$module_id = "learning";

$sTableID = "t_certification_admin";
$oSort = new CAdminSorting($sTableID, "ID", "desc");// sort initialization
$lAdmin = new CAdminList($sTableID, $oSort);// list initialization


$filter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"ID",
		GetMessage("LEARNING_ADMIN_COURSE_ID"),
		GetMessage("LEARNING_F_ACTIVE2"),
		GetMessage("LEARNING_ADMIN_SUMMARY"),
		GetMessage("LEARNING_ADMIN_FROM_ONLINE"),
	)
);

$arFilterFields = Array(
	"filter_user",
	"filter_id",
	"filter_course_id",
	"filter_active",
	"filter_summary_from",
	"filter_summary_to",
	"filter_from_online",
);

$lAdmin->InitFilter($arFilterFields);// filter initialization

/* was:
$arFilter = Array(
	"ID" => $filter_id,
	"COURSE_ID" => $filter_course_id,
	"ACTIVE" => $filter_active,
	"FROM_ONLINE" => $filter_from_online,
	"MIN_PERMISSION"=>"W",
);
*/

$arFilter = Array(
	"ID" => $filter_id,
	"COURSE_ID" => $filter_course_id,
	"ACTIVE" => $filter_active,
	"FROM_ONLINE" => $filter_from_online,
	"ACCESS_OPERATIONS" => CLearnAccess::OP_LESSON_READ | CLearnAccess::OP_LESSON_WRITE
);

if(!empty($filter_summary_from))
	$arFilter[">=SUMMARY"] = $filter_summary_from;
if(!empty($filter_summary_to))
	$arFilter["<=SUMMARY"] = $filter_summary_to;

if(!empty($filter_user))
	$arFilter["USER"] = $filter_user;


if($lAdmin->EditAction()) //save from the list
{
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;

		$res = CCertification::GetByID($ID);
		if (!$res->Fetch())
			continue;

		$DB->StartTransaction();
		$ID = intval($ID);

		$ob = new CCertification;
		if(!$ob->Update($ID, $arFields))
		{
			if($e = $APPLICATION->GetException())
			{
				$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
			}
			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}

// group and single actions processing
if($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CCertification::GetList(Array($by => $order), $arFilter);
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
			$oAccess = CLearnAccess::GetInstance($USER->GetID());
			if ( ! $oAccess->IsLessonAccessible (CCertification::LessonIdByCertId ($ID), CLearnAccess::OP_LESSON_WRITE) )
				break;

			@set_time_limit(0);
			$DB->StartTransaction();
			if(!CCertification::Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage("LEARNING_DELETE_ERROR"), $ID);
			}
			else
			{
				$DB->Commit();
			}
			break;
		case "activate":
		case "deactivate":
			$cl = new CCertification;
			$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
			if(!$cl->Update($ID, $arFields))
				if($e = $APPLICATION->GetException())
					$lAdmin->AddGroupError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
			break;
		}
	}
}


// fetch data
if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "excel")
	$arNavParams = array();
else
	$arNavParams = array('nPageSize' => CAdminResult::GetNavSize($sTableID));

$rsData = CCertification::GetList(array($by => $order), $arFilter, $arNavParams);
$rsData = new CAdminResult($rsData, $sTableID);

// navigation string setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LEARNING_ADMIN_RESULTS")));


$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"TIMESTAMP_X","content"=>GetMessage('LEARNING_COURSE_ADM_DATECH'), "sort"=>"timestamp_x", "default"=>true),
	array("id"=>"COURSE_NAME", "content"=>GetMessage('LEARNING_ADMIN_COURSE_ID'),  "default"=>true),
	array("id"=>"USER_NAME", "content"=>GetMessage('LEARNING_ADMIN_STUDENT'), "sort" =>"user_name", "default"=>true),
	array("id"=>"SUMMARY", "content"=>GetMessage('LEARNING_ADMIN_SUMMARY'),"sort"=>"summary", "default"=>true),
	array("id"=>"MAX_SUMMARY", "content"=>GetMessage('LEARNING_ADMIN_MAX_SUMMARY'),"sort"=>"max_summary", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage('LEARNING_COURSE_ADM_ACT'), "sort"=>"active", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage('LEARNING_COURSE_ADM_SORT'),"sort"=>"sort", "default"=>true),
	array("id"=>"FROM_ONLINE", "content"=>GetMessage('LEARNING_ADMIN_ONLINE'),"sort"=>"from_online", "default"=>true),
));


// building list
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddCheckField("ACTIVE");
	$row->AddCheckField("PUBLIC");
	$row->AddCheckField("FROM_ONLINE");
	$row->AddInputField("SUMMARY",Array("size"=>"3"));
	$row->AddInputField("MAX_SUMMARY",Array("size"=>"3"));
	$row->AddInputField("SORT", Array("size"=>"3"));

	$row->AddViewField("USER_NAME", "[<a href=\"user_edit.php?lang=".LANG."&ID=".$f_USER_ID."\" title=\"".GetMessage("LEARNING_CHANGE_USER_PROFILE")."\">".$f_USER_ID."</a>] ".$f_USER_NAME);


	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"delete",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"ACTION"=>"if(confirm('".GetMessageJS('LEARNING_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete",''));

	$row->AddActions($arActions);
}

// list footer
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

$lAdmin->AddGroupActionTable(Array(
	"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
	"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
));

$lAdmin->AddAdminContextMenu(Array());
$lAdmin->CheckListMode();


$APPLICATION->SetTitle(GetMessage("LEARNING_ADMIN_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

if (defined("LEARNING_ADMIN_ACCESS_DENIED"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"), false);
?>

<form name="form1" method="GET" action="<?echo $APPLICATION->GetCurPage()?>" onsubmit="return this.set_filter.onclick();">
<?$filter->Begin();?>

	<tr>
		<td><b><?=GetMessage("LEARNING_ADMIN_STUDENT")?>:</b></td>
		<td><input type="text" name="filter_user" value="<?echo htmlspecialcharsbx($filter_user)?>" size="47"></td>
	</tr>


	<tr>
		<td>ID:</td>
		<td><input type="text" name="filter_id" value="<?echo htmlspecialcharsbx($filter_id)?>" size="47"></td>
	</tr>

	<tr>
		<td><?=GetMessage("LEARNING_ADMIN_COURSE_ID")?>:</td>
		<td>
			<select name="filter_course_id">
				<option value=""><?echo GetMessage("LEARNING_ALL")?></option>
			<?
			$l = CCourse::GetList(Array(), Array());
			while($l->ExtractFields("l_")):
				?><option value="<?echo $l_ID?>"<?if($filter_course_id==$l_ID)echo " selected"?>><?echo $l_NAME?></option><?
			endwhile;
			?>
			</select>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("LEARNING_F_ACTIVE")?>:</td>
		<td>
			<?
			$arr = array("reference"=>array(GetMessage("LEARNING_YES"), GetMessage("LEARNING_NO")), "reference_id"=>array("Y","N"));
			echo SelectBoxFromArray("filter_active", $arr, htmlspecialcharsex($filter_active), GetMessage('LEARNING_ALL'));
			?>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("LEARNING_ADMIN_SUMMARY")?>:</td>
		<td nowrap>
			<input type="text" name="filter_summary_from" size="10" value="<?echo htmlspecialcharsex($filter_summary_from)?>">
			...
			<input type="text" name="filter_summary_to" size="10" value="<?echo htmlspecialcharsex($filter_summary_to)?>">
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("LEARNING_ADMIN_FROM_ONLINE")?>:</td>
		<td>
			<?
			$arr = array("reference"=>array(GetMessage("LEARNING_YES"), GetMessage("LEARNING_NO")), "reference_id"=>array("Y","N"));
			echo SelectBoxFromArray("filter_from_online", $arr, htmlspecialcharsex($filter_from_online), GetMessage('LEARNING_ALL'));
			?>
		</td>
	</tr>

<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));$filter->End();?>
</form>

<?$lAdmin->DisplayList();?>


<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>