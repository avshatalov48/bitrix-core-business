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

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/learning/prolog.php");
IncludeModuleLangFile(__FILE__);

$aContext = array();
$oAccess = CLearnAccess::GetInstance($USER->GetID());

$COURSE_ID = isset($_REQUEST['COURSE_ID']) ? intval($_REQUEST['COURSE_ID']) : 0;

$course = CCourse::GetByID($COURSE_ID);

$bBadCourse = true;
try
{
	if ($arCourse = $course->Fetch())
	{
		$linkedLessonId = CCourse::CourseGetLinkedLesson ($COURSE_ID);

		if ( ( $linkedLessonId !== false)
			&& $oAccess->IsLessonAccessible ($linkedLessonId, CLearnAccess::OP_LESSON_READ) )
		{
			$bBadCourse = false;
		}
		else
			$bBadCourse = true;
	}
	else
		$bBadCourse = true;
}
catch (Exception $e)
{
	$bBadCourse = true;
}


$isReadOnly = true;
try
{
	if ($oAccess->IsLessonAccessible ($linkedLessonId, CLearnAccess::OP_LESSON_WRITE))
		$isReadOnly = false;
}
catch (Exception $e)
{
	$isReadOnly = true;
}

if ($bBadCourse)
{
	$APPLICATION->SetTitle(GetMessage('LEARNING_TESTS'));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$aContext = array(
		array(
			"ICON"  => "btn_list",
			"TEXT"  => GetMessage("LEARNING_BACK_TO_ADMIN"),
			"LINK"  => "learn_unilesson_admin.php?lang=" . LANG
				. '&PARENT_LESSON_ID=-1'
				. GetFilterParams("filter_"),
			"TITLE" => GetMessage("LEARNING_BACK_TO_ADMIN")
		),
	);
	$context = new CAdminContextMenu($aContext);
	$context->Show();

	CAdminMessage::ShowMessage(GetMessage("LEARNING_BAD_COURSE"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$sTableID = "t_test_admin";
$oSort = new CAdminSorting($sTableID, "sort", "asc");// sort initializing
$lAdmin = new CAdminList($sTableID, $oSort);// list initializing


$arFilterFields = Array(
	"filter_name",
	"filter_active",
);

$lAdmin->InitFilter($arFilterFields);// filter initializing

$arFilter = Array(
	"ACTIVE" => $filter_active,
	"?NAME" => $filter_name,
	"COURSE_ID" => $COURSE_ID,
);


if ( ! $isReadOnly && $lAdmin->EditAction()) // save from the list
{
	foreach($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		$ob = new CTest;
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
if( ! $isReadOnly && ($arID = $lAdmin->GroupAction()) )
{
	if(isset($_REQUEST['action_target']) && $_REQUEST['action_target']=='selected')
	{
		$rsData = CTest::GetList(Array($by=>$order), $arFilter);
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
			$ch = new CTest;
			if(!$ch->Delete($ID))
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
			$ch = new CTest;
			$arFields = Array("ACTIVE"=>($action=="activate"?"Y":"N"));
			if(!$ch->Update($ID, $arFields))
				if($e = $APPLICATION->GetException())
					$lAdmin->AddGroupError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
			break;
		}
	}

	if(!empty($_REQUEST['return_url']) && check_bitrix_sessid())
	{
		LocalRedirect($_REQUEST['return_url']);
	}
}

// fetch data
$rsData = CTest::GetList(Array($by=>$order),$arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// navigation string setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LEARNING_TESTS")));


// list header
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"TIMESTAMP_X","content"=>GetMessage('LEARNING_COURSE_ADM_DATECH'), "sort"=>"timestamp_x", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage('LEARNING_NAME'),	"sort"=>"name", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage('LEARNING_COURSE_ADM_SORT'),"sort"=>"sort", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage('LEARNING_COURSE_ADM_ACT'),"sort"=>"active", "default"=>true),
	array("id"=>"TESTS_STATS", "content"=>GetMessage('LEARNING_TEST_ADM_STATS'), "default"=>true),
	));

// building list
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);
	$arStat = CTest::GetStats($f_ID);

	$row->AddCheckField("ACTIVE");
	$row->AddInputField("NAME",Array("size"=>"35"));
	$row->AddInputField("SORT", Array("size"=>"3"));

	$index = '-';

	if ($arStat["ALL_CNT"] > 0.1)
	{
		$index = 100 * ($arStat["CORRECT_CNT"] / $arStat["ALL_CNT"]);
		$index = round ($index, 1);
		$index = sprintf("%03.1f", $index) . '%';
	}

	$attemptUrl = "learn_attempt_admin.php?lang=".LANG."&filter_test_id=".$f_ID."&set_filter=Y&&filter_status=F";
	$row->AddViewField("TESTS_STATS",
		$index.
		' (<a href="'.$attemptUrl.'&filter_completed=Y">'.$arStat["CORRECT_CNT"].'</a> /
		<a href="'.$attemptUrl.'">'.$arStat["ALL_CNT"].'</a>)'
	);

	$arActions = array();

	$editActionText = GetMessage("MAIN_ADMIN_MENU_OPEN");
	if ( ! $isReadOnly )
		$editActionText = GetMessage("MAIN_ADMIN_MENU_EDIT");

	$editUrl = "learn_test_edit.php?lang=" . LANG
			. "&COURSE_ID=" . $COURSE_ID
			. '&PARENT_LESSON_ID=' . intval($_GET['PARENT_LESSON_ID'] ?? 0)
			. '&LESSON_PATH=' . htmlspecialcharsbx($_GET['LESSON_PATH'] ?? '')
			. "&ID=" . $f_ID
			. "&filter=Y&set_filter=Y";

	$row->AddViewField("NAME", "<a href=\"".$editUrl."\">".$f_NAME."</a>");

	$arActions[] = array(
		"ICON"    => "edit",
		"DEFAULT" => "Y",
		"TEXT"    => $editActionText,
		"ACTION"  => $lAdmin->ActionRedirect($editUrl)
	);

	if ( ! $isReadOnly )
	{
		$arActions[] = array("SEPARATOR"=>true);

		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
			"ACTION"=>"if(confirm('".GetMessageJS('LEARNING_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete",'COURSE_ID='.$COURSE_ID));
	}

	$row->AddActions($arActions);

}

// list footer
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if ( ! $isReadOnly )
{
	// group actions buttons
	$lAdmin->AddGroupActionTable(Array(
		"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
		)
	);

	$aContext[] =
		array(
			"ICON" => "btn_new",
			"TEXT"=>GetMessage("LEARNING_ADD"),
			"LINK"=>"learn_test_edit.php?lang=" . LANG
				. "&COURSE_ID=" . $COURSE_ID
				. '&PARENT_LESSON_ID=' . ($_GET['PARENT_LESSON_ID'] ?? 0)
				. '&LESSON_PATH=' . htmlspecialcharsbx($_GET['LESSON_PATH'] ?? '')
				. GetFilterParams("filter_"),
			"TITLE"=>GetMessage("LEARNING_ADD_ALT")
	);
}


$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();


$APPLICATION->SetTitle($arCourse["NAME"].": ".GetMessage('LEARNING_TESTS'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$filter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("LEARNING_F_ACTIVE2"),
	)
);
?>

<form method="get" action="learn_test_admin.php?lang=<?=LANG?>&COURSE_ID=<?=$COURSE_ID?>&PARENT_LESSON_ID=<?php
	echo htmlspecialcharsbx($_GET['PARENT_LESSON_ID'] ?? 0); ?>&LESSON_PATH=<?php echo htmlspecialcharsbx($_GET['LESSON_PATH'] ?? ''); ?>" name="find_form" onsubmit="return this.set_filter.onclick();">
<?$filter->Begin();?>
	<tr>
		<td align="right"><?echo GetMessage("LEARNING_NAME")?>:</td>
		<td align="left">
			<input type="text" name="filter_name" value="<?echo htmlspecialcharsex($filter_name)?>" size="47">&nbsp;<?=ShowFilterLogicHelp()?>
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


<?php
$filter->Buttons(
	array(
		"table_id" => $sTableID,
		"url"      => "learn_test_admin.php?lang=" . LANG
			. '&PARENT_LESSON_ID=' . ($_GET['PARENT_LESSON_ID'] ?? 0)
			. '&LESSON_PATH=' . htmlspecialcharsbx($_GET['LESSON_PATH'] ?? 0)
			. "&COURSE_ID=" . $COURSE_ID,
		"form"     => "find_form"
	)
);
$filter->End();
?>
</form>


<?$lAdmin->DisplayList();?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
