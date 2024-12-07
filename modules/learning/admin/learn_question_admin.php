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

$lessonPath = '';

if (isset($_POST['LESSON_PATH']))
	$lessonPath = $_POST['LESSON_PATH'];
elseif (isset($_GET['LESSON_PATH']))
	$lessonPath = $_GET['LESSON_PATH'];

$oPath = new CLearnPath();
$oPath->ImportUrlencoded($lessonPath);
$LESSON_ID = $oPath->GetBottom();
if ($LESSON_ID === false)
{
	CAdminMessage::ShowMessage(GetMessage('LEARNING_BAD_LESSON'));
	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
	exit();
}
$uriLessonPath = $oPath->ExportUrlencoded();
unset ($lessonPath);

if (isset($from) && $from <> '')
	$str_from = "&from=".htmlspecialcharsbx($from);
else
	$str_from = "";

$oAccess = CLearnAccess::GetInstance($USER->GetID());
$bAccessLessonModify = $oAccess->IsLessonAccessible ($LESSON_ID, CLearnAccess::OP_LESSON_WRITE);

$lesson = CLearnLesson::GetList(Array(), Array('LESSON_ID' => $LESSON_ID));
$arLesson = $lesson->Fetch();

$oTree = CLearnLesson::GetTree($LESSON_ID, array('EDGE_SORT' => 'asc'), array(), false);

$arSubLessons = $oTree->GetTreeAsList();
$arSubLessonsIDs = array();
foreach ($arSubLessons as $arSubLesson)
	$arSubLessonsIDs[] = (int) $arSubLesson['LESSON_ID'];

$arSubLessonsIDs[] = (int) $LESSON_ID;

if ( ! $bAccessLessonModify )
{
	$APPLICATION->SetTitle(GetMessage('LEARNING_QUESTION'));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$aContext = array(
		array(
			"ICON"  => "btn_list",
			"TEXT"  => GetMessage("LEARNING_BACK_TO_ADMIN"),
			"LINK"  => "learn_unilesson_admin.php?lang=" . LANG
						. GetFilterParams("filter_")
						. '&LESSON_PATH=' . $uriLessonPath,
			"TITLE" => GetMessage("LEARNING_BACK_TO_ADMIN")
		),
	);
	$context = new CAdminContextMenu($aContext);
	$context->Show();

	CAdminMessage::ShowMessage(GetMessage("LEARNING_BAD_LESSON"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$sTableID = "t_question_admin";
$oSort = new CAdminSorting($sTableID, "timestamp_x", "desc");// sort initializing
$lAdmin = new CAdminList($sTableID, $oSort);// list initializing


$arFilterFields = Array(
	"filter_title",
	"filter_self",
	"filter_active",
	"filter_required",
);

$lAdmin->InitFilter($arFilterFields);// filter initializing

$arFilter = Array(
	'LESSON_ID' => $arSubLessonsIDs,
	"SELF" => $filter_self,
	"ACTIVE" => $filter_active,
	"CORRECT_REQUIRED" => $filter_required,
	"?NAME" => $filter_title,
);

if ($lAdmin->EditAction()) // save from the list
{
	foreach($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		// http://jabber.bx/view.php?id=39495
		if (isset($arFields['FILE_ID']))
			unset($arFields['FILE_ID']);

		$DB->StartTransaction();
		$ob = new CLQuestion;
		if(!$ob->Update($ID, $arFields))
		{
			if($e = $APPLICATION->GetException())
			{
				$e = $APPLICATION->GetException();
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
	if(isset($_REQUEST['action_target']) && $_REQUEST['action_target']=='selected')
	{
		$rsData = CLQuestion::GetList(Array($by=>$order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if($ID == '')
			continue;
		$ID = intval($ID);

		$action = $_REQUEST['action'] ?? '';

		switch($action)
		{
		case "delete":
			@set_time_limit(0);
			$DB->StartTransaction();
			$cl = new CLQuestion;
			if(!$cl->Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage("LEARNING_DELETE_ERROR"), $ID);
			}
			else
			{
				$DB->Commit();
			}
			break;
		case "self":
		case "deself":
			// We shouldn't do this for text lessons
			$rs = CLQuestion::GetByID($ID);
			$arQuestionData = $rs->Fetch();
			if ($arQuestionData)
			{
				if ($arQuestionData['QUESTION_TYPE'] !== 'T')
				{
					$cl = new CLQuestion;
					$arFields = Array("SELF"=>($action=="self"?"Y":"N"));
					if(!$cl->Update($ID, $arFields))
						if($e = $APPLICATION->GetException())
							$lAdmin->AddGroupError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
				}
				else
					$lAdmin->AddGroupError(GetMessage('LEARNING_QUESTION_OF_TEXT_TYPE_IGNORED'), $ID);
			}
			break;

		case "activate":
		case "deactivate":
			$cl = new CLQuestion;
			$arFields = Array("ACTIVE"=>($action=="activate"?"Y":"N"));
			if(!$cl->Update($ID, $arFields))
				if($e = $APPLICATION->GetException())
					$lAdmin->AddGroupError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
			break;

		case "required":
		case "derequired":
			$cl = new CLQuestion;
			$arFields = Array("CORRECT_REQUIRED"=>($action=="required"?"Y":"N"));
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

$rsData = CLQuestion::GetList(array($by=>$order), $arFilter, true, $arNavParams);
$rsData = new CAdminResult($rsData, $sTableID);

// navigation string setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LEARNING_QUESTION")));

// list header
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage('LEARNING_COURSE_ADM_ACT'),"sort"=>"active", "default"=>true),
	array("id"=>"TIMESTAMP_X","content"=>GetMessage('LEARNING_COURSE_ADM_DATECH'), "sort"=>"timestamp_x", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage('LEARNING_NAME'),	"sort"=>"name", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage('LEARNING_COURSE_ADM_SORT'),"sort"=>"sort", "default"=>true),
	array("id"=>"SELF", "content"=>GetMessage('LEARNING_QUESTION_ADM_SELF'),"sort"=>"self", "default"=>true),
	array("id"=>"CORRECT_REQUIRED", "content"=>GetMessage('LEARNING_QUESTION_ADM_REQUIRED'),"sort"=>"correct_required", "default"=>true),
	array("id"=>"QUESTION_TYPE", "content"=>GetMessage('LEARNING_QUESTION_ADM_TYPE'),"sort"=>"type", "default"=>true),
	array("id"=>"POINT", "content"=>GetMessage('LEARNING_QUESTION_ADM_POINT'),"sort"=>"point", "default"=>true),
	array("id"=>"ANSWERS_STATS", "content"=>GetMessage('LEARNING_QUESTION_ADM_STATS'), "default"=>true),
));

$arQuestions = array();
$arQuestionsIds = array();
while($arRes = $rsData->Fetch())
{
	$arQuestionsIds[] = (int) $arRes['ID'];
	$arQuestions[] = $arRes;
}

$arMultiStats = CLAnswer::getMultiStats($arQuestionsIds);

// building list
foreach ($arQuestions as $arRes)
{
	extract($arRes, EXTR_PREFIX_ALL , 'f');

	$row =& $lAdmin->AddRow($f_ID, $arRes);
	$arStat = $arMultiStats[$f_ID] ?? [];

	$row->AddCheckField("SELF");
	$row->AddCheckField("ACTIVE");
	$row->AddCheckField("CORRECT_REQUIRED");
	$row->AddInputField("NAME",Array("size"=>"35"));
	$row->AddInputField("SORT", Array("size"=>"3"));
	$row->AddInputField("POINT", Array("size"=>"3"));

	$row->AddViewField("QUESTION_TYPE",
		'<div title="'.GetMessage("LEARNING_QUESTION_TYPE_".$f_QUESTION_TYPE)
		.'" class="learning-question-'.mb_strtolower($f_QUESTION_TYPE) . '"></div>');

	$index = '-';

	if (isset($arStat["ALL_CNT"]) && $arStat["ALL_CNT"] > 0.1)
	{
		$index = 100 * ($arStat["CORRECT_CNT"] / $arStat["ALL_CNT"]);
		$index = round ($index, 1);
		$index = sprintf("%03.1f", $index) . '%';
	}

	$row->AddViewField("ANSWERS_STATS",
		$index
		. ' (<a href="learn_test_result_admin.php?lang=' . LANG
		. '&set_filter=Y&filter_correct=Y&filter_answered=Y">' . ($arStat["CORRECT_CNT"] ?? 0)
		. '</a> / <a href="learn_test_result_admin.php?lang=' . LANG . '">'
		. ($arStat["ALL_CNT"] ?? 0) . '</a>)');

	$arActions = Array();

	$editUrl = "learn_question_edit.php?lang=".LANG.'&LESSON_PATH='.$uriLessonPath
				."&ID=".$f_ID.GetFilterParams("filter_", false).$str_from;

	$row->AddViewField("NAME", '<a href="'.htmlspecialcharsbx($editUrl).'">'.htmlspecialcharsbx($f_NAME).'</a>');

	$arActions[] = array(
		"ICON"    => "edit",
		"DEFAULT" => "Y",
		"TEXT"    => GetMessage("MAIN_ADMIN_MENU_EDIT"),
		"ACTION"  => $lAdmin->ActionRedirect($editUrl)
	);

	/*
	$arActions[] = array(
		"ICON"=>"copy",
		"TEXT"=>GetMessage("MAIN_ADMIN_ADD_COPY"),
		"ACTION"=>$lAdmin->ActionRedirect("learn_course_edit.php?COPY_ID=".$f_ID));
	*/

	$arActions[] = array("SEPARATOR"=>true);

	$arActions[] = array(
		"ICON"   => "delete",
		"TEXT"   => GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"ACTION" => "if(confirm('" . GetMessageJS('LEARNING_CONFIRM_DEL_MESSAGE') . "')) "
			. $lAdmin->ActionDoGroup($f_ID, "delete", 'LESSON_PATH=' . $uriLessonPath));

	$row->AddActions($arActions);
}

// list footer
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

// group actions buttons
$lAdmin->AddGroupActionTable(Array(
	"self"=>GetMessage("LEARNING_ACTION_SELF"),
	"deself"=>GetMessage("LEARNING_ACTION_DESELF"),
	"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
	"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	"required"=>GetMessage("MAIN_ADMIN_LIST_REQUIRED"),
	"derequired"=>GetMessage("MAIN_ADMIN_LIST_NOT_REQUIRED"),
	"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
	)
);

$arContextPopup = Array(
	Array(
		"TEXT" => GetMessage('LEARNING_SINGLE_CHOICE'),
		//"ICON" => "learning-question-s",
		"LINK" => "learn_question_edit.php?lang="
			. LANG
			. '&LESSON_PATH=' . $uriLessonPath
			. "&QUESTION_TYPE=S" . GetFilterParams("filter_", false)
			. $str_from
		//"window.location='learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."QUESTION_TYPE=S".GetFilterParams("filter_", false)."'",

	),
	Array(
		"TEXT" => GetMessage('LEARNING_MULTIPLE_CHOICE'),
		//"ICON" => "learning-question-m",
		"LINK" =>
		"learn_question_edit.php?lang="
			. LANG
			. '&LESSON_PATH=' . $uriLessonPath
			. "&QUESTION_TYPE=M"
			. GetFilterParams("filter_", false)
			. $str_from

		//"window.location='learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."QUESTION_TYPE=M".GetFilterParams("filter_", false)."'",
	),
	Array(
		"TEXT" => GetMessage('LEARNING_SORTING'),
		//"ICON" => "learning-question-s",
		"LINK" => "learn_question_edit.php?lang=" . LANG
			. '&LESSON_PATH=' . $uriLessonPath
			. "&QUESTION_TYPE=R"
			. GetFilterParams("filter_", false)
			. $str_from
		//"window.location='learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."QUESTION_TYPE=S".GetFilterParams("filter_", false)."'",

	),
	Array(
		"TEXT" => GetMessage('LEARNING_TEXT_ANSWER'),
		//"ICON" => "learning-question-m",
		"LINK" =>
		"learn_question_edit.php?lang=" . LANG
			. '&LESSON_PATH=' . $uriLessonPath
			. "&QUESTION_TYPE=T"
			. GetFilterParams("filter_", false)
			. $str_from

		//"window.location='learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."QUESTION_TYPE=M".GetFilterParams("filter_", false)."'",
	),
	);


$aContext = array(
	array(
		"ICON" => "btn_new",
		"TEXT"=>GetMessage("LEARNING_ADD"),
		"TITLE"=>GetMessage("LEARNING_ADD_ALT"),
		"MENU" => $arContextPopup
	),
);


$lAdmin->AddAdminContextMenu($aContext);



// list mode check (if AJAX then terminate the script)
$lAdmin->CheckListMode();

$APPLICATION->SetTitle($arLesson['NAME'] . ': ' . GetMessage('LEARNING_QUESTION'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$filter = new CAdminFilter(
	$sTableID . "_filter",
	array(
		GetMessage("LEARNING_NAME"),
		GetMessage("LEARNING_F_SELF"),
		GetMessage("LEARNING_F_ACTIVE2"),
		GetMessage("LEARNING_F_CORRECT_REQUIRED"),
	)
);

?>
<form method="GET" action="<?echo $APPLICATION->GetCurPage()?>" name="find_form" onsubmit="return this.set_filter.onclick();">
	<input type="hidden" name="LESSON_PATH" value="<?php echo htmlspecialcharsbx(urldecode($uriLessonPath)); ?>">
<?$filter->Begin();?>

	<tr>
		<td><b><?echo GetMessage("LEARNING_NAME")?>:</b></td>
		<td>
			<input type="text" name="filter_title" size="50" value="<?echo htmlspecialcharsex($filter_title)?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?>
		</td>
	</tr>


	<tr>
		<td><?echo GetMessage("LEARNING_F_SELF")?>:</td>
		<td>
			<select name="filter_self">
				<option value=""><?=htmlspecialcharsex(GetMessage('LEARNING_ALL2'))?></option>
				<option value="Y"<?if($filter_self=="Y")echo " selected"?>><?=htmlspecialcharsex(GetMessage("LEARNING_YES"))?></option>
				<option value="N"<?if($filter_self=="N")echo " selected"?>><?=htmlspecialcharsex(GetMessage("LEARNING_NO"))?></option>
			</select>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("LEARNING_F_ACTIVE")?>:</td>
		<td>
			<select name="filter_active">
				<option value=""><?=htmlspecialcharsex(GetMessage('LEARNING_ALL'))?></option>
				<option value="Y"<?if($filter_active=="Y")echo " selected"?>><?=htmlspecialcharsex(GetMessage("LEARNING_YES"))?></option>
				<option value="N"<?if($filter_active=="N")echo " selected"?>><?=htmlspecialcharsex(GetMessage("LEARNING_NO"))?></option>
			</select>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("LEARNING_F_CORRECT_REQUIRED")?>:</td>
		<td>
			<select name="filter_required">
				<option value=""><?=htmlspecialcharsex(GetMessage('LEARNING_ALL'))?></option>
				<option value="Y"<?if($filter_required=="Y")echo " selected"?>><?=htmlspecialcharsex(GetMessage("LEARNING_YES"))?></option>
				<option value="N"<?if($filter_required=="N")echo " selected"?>><?=htmlspecialcharsex(GetMessage("LEARNING_NO"))?></option>
			</select>
		</td>
	</tr>

<?
$filter->Buttons(array(
	"table_id" => $sTableID,
	"url"      => "learn_question_admin.php?lang=" . LANG . "&LESSON_PATH=" . $uriLessonPath,
	"form"     => "find_form"));
$filter->End();
?>
</form>

<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
