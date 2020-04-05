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
IncludeModuleLangFile(__DIR__."/common.php");

ClearVars();

$module_id = "learning";

$sTableID = "t_gradebook_admin";
$oSort = new CAdminSorting($sTableID, "ID", "desc");// sort initialization
$lAdmin = new CAdminList($sTableID, $oSort);// list initialization


$filter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"ID",
		GetMessage("LEARNING_ADMIN_TEST"),
		GetMessage("LEARNING_ADMIN_APPROVED"),
		GetMessage("LEARNING_ADMIN_RESULT"),
	)
);

$arFilterFields = Array(
	"filter_user",
	"filter_user_type",
	"filter_id",
	"filter_test_id",
	"filter_completed",
	"filter_result_from",
	"filter_result_to",
);

$lAdmin->InitFilter($arFilterFields);// filter initialization

/* was:
$arFilter = Array(
	"ID" => $filter_id,
	"TEST_ID" => $filter_test_id,
	"COMPLETED" => $filter_completed,
	"MIN_PERMISSION"=>"W",
);
*/
$arFilter = Array(
	"ID" => $filter_id,
	"TEST_ID" => $filter_test_id,
	"COMPLETED" => $filter_completed,
	'ACCESS_OPERATIONS' => CLearnAccess::OP_LESSON_READ | CLearnAccess::OP_LESSON_WRITE,
);

if(!empty($filter_result_from))
	$arFilter[">=RESULT"] = $filter_result_from;
if(!empty($filter_result_to))
	$arFilter["<=RESULT"] = $filter_result_to;

$filterTypeMap = array(
	"login" => "USER_LOGIN",
	"last_name" => "USER_LAST_NAME",
	"id" => "STUDENT_ID",
	"name" => "USER_NAME",
);

if(!empty($filter_user) && !empty($filter_user_type) && array_key_exists($filter_user_type, $filterTypeMap))
{
	$arFilter["=".$filterTypeMap[$filter_user_type]] = $filter_user;
}

if($lAdmin->EditAction()) //save from the list
{
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;

		$res = CGradeBook::GetByID($ID);
		if (!$ar = $res->Fetch())
			continue;

		$DB->StartTransaction();
		$ID = IntVal($ID);

		$ob = new CGradeBook;
		if(!$ob->Update($ID, $arFields))
		{
			if($e = $APPLICATION->GetException())
			{
				$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
				$DB->Rollback();
			}
		}
		else
		{
			CCertification::Certificate($ar["STUDENT_ID"], $ar["COURSE_ID"]);
		}
		$DB->Commit();
	}
}


// group and single actions processing
if($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CGradeBook::GetList(Array($by => $order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;

		$ID = intval($ID);
		switch($_REQUEST['action'])
		{
		case "delete":
			$oAccess = CLearnAccess::GetInstance($USER->GetID());
			if ( ! $oAccess->IsLessonAccessible (CGradeBook::LessonIdByGradeBookId ($ID), CLearnAccess::OP_LESSON_WRITE) )
				break;

			@set_time_limit(0);
			$DB->StartTransaction();
			if(!CGradeBook::Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage("LEARNING_DELETE_ERROR"), $ID);
			}
			$DB->Commit();
			break;
		case "completed":
		case "uncompleted":
			$ob = new CGradeBook;
			$arFields = Array("COMPLETED"=>($_REQUEST['action']=="completed"?"Y":"N"));
			if(!$ob->Update($ID, $arFields))
			{
				if($e = $APPLICATION->GetException())
					$lAdmin->AddGroupError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
			}
			break;
		}
	}
}


// fetch data
$rsData = CGradeBook::GetList(Array($by=>$order),$arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// navigation string setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LEARNING_ADMIN_RESULTS")));


// list header
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"TEST_NAME", "content"=>GetMessage('LEARNING_ADMIN_TEST'), "sort"=>"test_name", "default"=>true),
	array("id"=>"USER_NAME", "content"=>GetMessage('LEARNING_ADMIN_STUDENT'), "sort" =>"user_name", "default"=>true),
	array("id"=>"RESULT", "content"=>GetMessage('LEARNING_ADMIN_RESULT'),"sort"=>"result", "default"=>true),
	array("id"=>"MAX_RESULT", "content"=>GetMessage('LEARNING_ADMIN_MAX_RESULT'),"sort"=>"max_result", "default"=>true),
	array("id"=>"ATTEMPTS", "content"=>GetMessage('LEARNING_ADMIN_ATTEMPTS'), "default"=>true, "align" => "center"),
	array("id"=>"COMPLETED", "content"=>GetMessage('LEARNING_ADMIN_APPROVED'),"sort"=>"completed", "default"=>true),
	array("id"=>"EXTRA_ATTEMPTS", "content"=>GetMessage('LEARNING_ADMIN_EXTRA_ATTEMPTS'), "default"=>true),
	));

// building list
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddInputField("RESULT", Array("size"=>"3"));
	$row->AddInputField("MAX_RESULT", Array("size"=>"3"));
	$row->AddCheckField("COMPLETED");

	$row->AddViewField("USER_NAME", "[<a href=\"user_edit.php?lang=".LANG."&ID=".$f_USER_ID."\" title=\"".GetMessage("LEARNING_CHANGE_USER_PROFILE")."\">".$f_USER_ID."</a>] ".$f_USER_NAME);

	$row->AddViewField("ATTEMPTS", "<a href=\"learn_attempt_admin.php?lang=".LANG."&filter_student_id=".$f_STUDENT_ID."&filter_test_id=".$f_TEST_ID."&set_filter=Y\">".$f_ATTEMPTS."</a>".($f_ATTEMPT_LIMIT > 0 ? " / ".$f_ATTEMPT_LIMIT : ""));

	$row->AddViewField("TEST_NAME", "<a href=\"/bitrix/admin/learn_test_edit.php?lang=".LANGUAGE_ID."&COURSE_ID=".$f_COURSE_ID."&PARENT_LESSON_ID=".$f_LINKED_LESSON_ID."&LESSON_PATH=".$f_LINKED_LESSON_ID."&ID=".$f_TEST_ID."&filter=Y&set_filter=Y\">".$f_TEST_NAME."</a>");

	$row->AddInputField("EXTRA_ATTEMPTS", Array("size"=>"3"));

	$arActions = Array();

/*
	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT" => "Y",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_EDIT"),
		"ACTION"=>$lAdmin->ActionRedirect("learn_gradebook_edit.php?lang=".LANG."&ID=".$f_ID.GetFilterParams("filter_"))
	);

	$arActions[] = array("SEPARATOR"=>true);
*/

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
	"completed"=>GetMessage("MAIN_ADMIN_LIST_COMPLETED"),
	"uncompleted"=>GetMessage("MAIN_ADMIN_LIST_UNCOMPLETED"),
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
		<td>
			<input type="text" name="filter_user" value="<?=htmlspecialcharsbx($filter_user)?>" size="25">

			<?=SelectBoxFromArray(
				"filter_user_type",
				array(
					"reference" => array(
						GetMessage("LEARNING_FILTER_USER_LOGIN"),
						GetMessage("LEARNING_FILTER_USER_LAST_NAME"),
						"ID",
						GetMessage("LEARNING_FILTER_USER_NAME")
					),
					"reference_id" => array(
						"login",
						"last_name",
						"id",
						"name",
					)
				),
				$filter_user_type,
				"",
				""
			);?>

		</td>
	</tr>


	<tr>
		<td>ID:</td>
		<td><input type="text" name="filter_id" value="<?echo htmlspecialcharsbx($filter_id)?>" size="47"></td>
	</tr>

	<tr>
		<td><?=GetMessage("LEARNING_ADMIN_TEST")?>:</td>
		<td>
			<select name="filter_test_id">
				<option value=""><?echo GetMessage("LEARNING_ALL")?></option>
			<?
			$l = CTest::GetList(Array(), Array());
			while($l->ExtractFields("l_")):
				?><option value="<?echo $l_ID?>"<?if($filter_test_id==$l_ID)echo " selected"?>><?echo $l_NAME?></option><?
			endwhile;
			?>
			</select>
		</td>
	</tr>

	<tr>
		<td><?=GetMessage("LEARNING_ADMIN_APPROVED")?>:</td>
		<td>
			<?
			$arr = array("reference"=>array(GetMessage("LEARNING_YES"), GetMessage("LEARNING_NO")), "reference_id"=>array("Y","N"));
			echo SelectBoxFromArray("filter_completed", $arr, htmlspecialcharsex($filter_completed), GetMessage('LEARNING_ALL'));
			?>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("LEARNING_ADMIN_RESULT")?>:</td>
		<td nowrap>
			<input type="text" name="filter_result_from" size="10" value="<?echo htmlspecialcharsex($filter_result_from)?>">
			...
			<input type="text" name="filter_result_to" size="10" value="<?echo htmlspecialcharsex($filter_result_to)?>">
		</td>
	</tr>

<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));$filter->End();?>
</form>

<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>