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

ClearVars();

$message = null;
$bVarsFromForm = false;

$ID = isset($_REQUEST['ID']) ? intval($_REQUEST['ID']) : 0;
$COURSE_ID = isset($_REQUEST['COURSE_ID']) ? intval($_REQUEST['COURSE_ID']) : 0;

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage('LEARNING_TEST'), "ICON"=>"main_user_edit", "TITLE"=>GetMessage('LEARNING_TEST_TITLE')),
	array("DIV" => "edit2", "TAB" => GetMessage('LEARNING_DESC'), "ICON"=>"main_user_edit", "TITLE"=>GetMessage('LEARNING_DESC_TITLE')),
	array("DIV" => "edit3", "TAB" => GetMessage('LEARNING_MARKS'), "ICON"=>"main_user_edit", "TITLE"=>GetMessage('LEARNING_MARKS_TITLE')),
);
$tabControl = new CAdminForm("testTabControl", $aTabs);


$isReadAccess         = false;
$isCreateOrEditAccess = false;
$isBtnsDisabled       = true;

$oAccess = CLearnAccess::GetInstance($USER->GetID());
$linkedLessonId = CCourse::CourseGetLinkedLesson ($COURSE_ID);

if ($linkedLessonId !== false)
{
	if ($oAccess->IsLessonAccessible ($linkedLessonId, CLearnAccess::OP_LESSON_READ))
		$isReadAccess = true;

	if ($oAccess->IsLessonAccessible ($linkedLessonId, CLearnAccess::OP_LESSON_WRITE))
	{
		$isReadAccess         = true;
		$isCreateOrEditAccess = true;
		$isBtnsDisabled       = false;
	}
}

if ($isReadAccess === false)
{
	$APPLICATION->SetTitle(GetMessage('LEARNING_TESTS'));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$aContext = array(
		array(
			"ICON" => "btn_list",
			"TEXT"=>GetMessage("LEARNING_BACK_TO_ADMIN"),
			"LINK"=>"learn_unilesson_admin.php?lang=" . LANG . '&PARENT_LESSON_ID=-1' . GetFilterParams("filter_"),
			"TITLE"=>GetMessage("LEARNING_BACK_TO_ADMIN")
		),
	);
	$context = new CAdminContextMenu($aContext);
	$context->Show();

	CAdminMessage::ShowMessage(GetMessage("LEARNING_BAD_COURSE"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}


$arNewIDs = array();
$nextNum = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["Update"] <> '' && check_bitrix_sessid() && $isCreateOrEditAccess)
{
	$test = new CTest;

	foreach($_POST as $key=>$val)
	{
		if (mb_substr($key, 0, 7) == "N_MARK_")
		{
			$arNewIDs[] = intval(mb_substr($key, 7));
		}
	}
	if (count($arNewIDs) > 0)
		$nextNum = max($arNewIDs);

	$QUESTIONS_FROM = $_REQUEST['QUESTIONS_FROM'] ?? '';
	$QUESTIONS_AMOUNT = isset($_REQUEST["QUESTIONS_AMOUNT_".$QUESTIONS_FROM]) && intval($_REQUEST["QUESTIONS_AMOUNT_".$QUESTIONS_FROM]) > 0 ? intval($_REQUEST["QUESTIONS_AMOUNT_".$QUESTIONS_FROM]) : 0;
	$QUESTIONS_FROM_ID = isset($_REQUEST["QUESTIONS_FROM_ID_".$QUESTIONS_FROM]) && intval($_REQUEST["QUESTIONS_FROM_ID_".$QUESTIONS_FROM]) > 0 ? intval($_REQUEST["QUESTIONS_FROM_ID_".$QUESTIONS_FROM]) : 0;

	if (isset($_REQUEST['CURRENT_INDICATION']) && $_REQUEST['CURRENT_INDICATION'] === "Y")
	{
		$CURRENT_INDICATION =
			(isset($_REQUEST['CURRENT_INDICATION_PERCENT']) && $_REQUEST['CURRENT_INDICATION_PERCENT'] === "Y" ? 1 : 0) +
			(isset($_REQUEST['CURRENT_INDICATION_MARK']) && $_REQUEST['CURRENT_INDICATION_MARK'] === "Y" ? 2 : 0);
	}
	else
	{
		$CURRENT_INDICATION = 0;
	}

	if (isset($_REQUEST['FINAL_INDICATION']) && $_REQUEST['FINAL_INDICATION'] === "Y")
	{
		$FINAL_INDICATION =
			(isset($_REQUEST['FINAL_INDICATION_CORRECT_COUNT']) && $_REQUEST['FINAL_INDICATION_CORRECT_COUNT'] == "Y" ? 1 : 0) +
			(isset($_REQUEST['FINAL_INDICATION_SCORE']) && $_REQUEST['FINAL_INDICATION_SCORE'] == "Y" ? 2 : 0) +
			(isset($_REQUEST['FINAL_INDICATION_MARK']) && $_REQUEST['FINAL_INDICATION_MARK'] == "Y" ? 4 : 0) +
			(isset($_REQUEST['FINAL_INDICATION_MESSAGE']) && $_REQUEST['FINAL_INDICATION_MESSAGE'] == "Y" ? 8 : 0);
	}
	else
	{
		$FINAL_INDICATION = 0;
	}

	$MIN_TIME_BETWEEN_ATTEMPTS =
		(int)($_REQUEST['MIN_TIME_BETWEEN_ATTEMPTS_D'] ?? 0) * 60 * 24
		+ (int)($_REQUEST['MIN_TIME_BETWEEN_ATTEMPTS_H'] ?? 0) * 60
		+ (int)($_REQUEST['MIN_TIME_BETWEEN_ATTEMPTS_M'] ?? 0)
	;

	$NEXT_QUESTION_ON_ERROR = (
		isset($_REQUEST['SHOW_ERRORS']) && $_REQUEST['SHOW_ERRORS'] == "Y"
		&& isset($_REQUEST['NEXT_QUESTION_ON_ERROR']) && $_REQUEST['NEXT_QUESTION_ON_ERROR'] == "N"
		&& isset($_REQUEST['PASSAGE_TYPE']) && $_REQUEST['PASSAGE_TYPE'] == "2"
	) ? "N" : "Y";

	$arFields = Array(
		"ACTIVE" => $_REQUEST['ACTIVE'] ?? null,
		"COURSE_ID" => $_REQUEST['COURSE_ID'] ?? null,
		"NAME" => $_REQUEST['NAME'] ?? null,
		"CODE" => $_REQUEST['CODE'] ?? null,
		"SORT" => $_REQUEST['SORT'] ?? null,
		"DESCRIPTION" => $_REQUEST['DESCRIPTION'] ?? null,
		"DESCRIPTION_TYPE" => $_REQUEST['DESCRIPTION_TYPE'] ?? null,

		"TIME_LIMIT" => $_REQUEST['TIME_LIMIT'] ?? null,
		"ATTEMPT_LIMIT" => $_REQUEST['ATTEMPT_LIMIT'] ?? null,
		"COMPLETED_SCORE" => $_REQUEST['COMPLETED_SCORE'] ?? null,

		"QUESTIONS_FROM" => $QUESTIONS_FROM,
		"QUESTIONS_AMOUNT" => $QUESTIONS_AMOUNT,
		"QUESTIONS_FROM_ID" => $QUESTIONS_FROM_ID,

		"RANDOM_QUESTIONS" => $_REQUEST['RANDOM_QUESTIONS'] ?? null,
		"RANDOM_ANSWERS" => $_REQUEST['RANDOM_ANSWERS'] ?? null,

		"APPROVED" => $_REQUEST['APPROVED'] ?? null,
		"INCLUDE_SELF_TEST" => $_REQUEST['INCLUDE_SELF_TEST'] ?? null,

		"PASSAGE_TYPE" => $_REQUEST['PASSAGE_TYPE'] ?? null,

		"PREVIOUS_TEST_ID" => $_REQUEST['PREVIOUS_TEST_ID'] ?? null,
		"PREVIOUS_TEST_SCORE" => $_REQUEST['PREVIOUS_TEST_SCORE'] ?? null,

		"INCORRECT_CONTROL" => $_REQUEST['INCORRECT_CONTROL'] ?? null,

		"CURRENT_INDICATION" => $CURRENT_INDICATION,
		"FINAL_INDICATION" => $FINAL_INDICATION,

		"SHOW_ERRORS" => $_REQUEST['SHOW_ERRORS'] ?? null,
		"NEXT_QUESTION_ON_ERROR" => $NEXT_QUESTION_ON_ERROR,

		"MIN_TIME_BETWEEN_ATTEMPTS" => $MIN_TIME_BETWEEN_ATTEMPTS,
	);

	if ($arFields["COMPLETED_SCORE"] == '')
	{
		unset($arFields["COMPLETED_SCORE"]);
		$arFields["APPROVED"] = "N";
	}

	if (intval($arFields["PREVIOUS_TEST_ID"]) <= 0)
	{
		$arFields["PREVIOUS_TEST_ID"] = false;
	}
	if ($arFields["PREVIOUS_TEST_SCORE"] == '')
	{
		$arFields["PREVIOUS_TEST_SCORE"] = 0;
	}

	$DB->StartTransaction();

	if($ID>0)
	{
		$actionType = "update";
		$res = $test->Update($ID, $arFields);
	}
	else
	{
		$actionType = "add";
		$ID = $test->Add($arFields);
		$res = ($ID>0);
	}

	if(!$res)
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("LEARNING_ERROR"), $e);
		$bVarsFromForm = true;
	}
	else
	{
		//Marks
		$marks = CLTestMark::GetList(Array(),Array("TEST_ID" => $ID));

		$arMarks = $arScores = array();

		while ($m = $marks->GetNext())
		{
			//delete?
			if (isset($_REQUEST["MARK_".$m["ID"]."_DEL"]) && $_REQUEST["MARK_".$m["ID"]."_DEL"] === "Y")
			{
					if(!CLTestMark::Delete($m["ID"]))
					{
						$message = new CAdminMessage(Array("MESSAGE" => GetMessage("LEARNING_DELETE_ERROR").$m["ID"]));
						$bVarsFromForm = true;
					}
			}
			else if(isset($_REQUEST["SCORE_".$m["ID"]]) && in_array($_REQUEST["SCORE_".$m["ID"]], $arScores))
			{
				$message = new CAdminMessage(Array("MESSAGE" =>  str_replace("##SCORE##", $_REQUEST["SCORE_".$m["ID"]], GetMessage("LEARNING_SCORE_EXISTS_ERROR"))));
				$bVarsFromForm = true;
			}
			elseif(isset($_REQUEST["MARK_".$m["ID"]]) && in_array($_REQUEST["MARK_".$m["ID"]], $arMarks))
			{
				$message = new CAdminMessage(Array("MESSAGE" => str_replace("##MARK##", $_REQUEST["MARK_".$m["ID"]], GetMessage("LEARNING_MARK_EXISTS_ERROR"))));
				$bVarsFromForm = true;
			}
			else
			{
				$arMarks[] = $_REQUEST["MARK_".$m["ID"]];
				$arScores[] = $_REQUEST["SCORE_".$m["ID"]];

				$arFields = Array(
					"TEST_ID" => $ID,
					"SCORE" => $_REQUEST["SCORE_".$m["ID"]],
					"MARK" => $_REQUEST["MARK_".$m["ID"]],
					"DESCRIPTION" => $_REQUEST["DESCRIPTION_".$m["ID"]],
				);

				$mrk = new CLTestMark;
				$res = $mrk->Update($m["ID"], $arFields);
				if (!$res)
				{
					$message = new CAdminMessage(Array("MESSAGE" => GetMessage("LEARNING_SAVE_ERROR").$m["ID"]));
					$bVarsFromForm = true;
				}
			}
		}

		//add new
		foreach ($arNewIDs as $i)
		{
			if (empty($_REQUEST["N_MARK_".$i]) && empty($_REQUEST["N_SCORE_".$i]))
			{
				continue;
			}

			if (isset($_REQUEST["N_SCORE_".$i]) && in_array($_REQUEST["N_SCORE_".$i], $arScores))
			{
				$message = new CAdminMessage(Array("MESSAGE" => str_replace("##SCORE##", $_REQUEST["N_SCORE_".$i], GetMessage("LEARNING_SCORE_EXISTS_ERROR"))));
				$bVarsFromForm = true;
			}
			elseif(isset($_REQUEST["N_MARK_".$i]) && in_array($_REQUEST["N_MARK_".$i], $arMarks))
			{
				$message = new CAdminMessage(Array("MESSAGE" => str_replace("##MARK##", $_REQUEST["N_MARK_".$i], GetMessage("LEARNING_MARK_EXISTS_ERROR"))));
				$bVarsFromForm = true;
			}
			else
			{
				$arMarks[] = $_REQUEST["N_MARK_".$i];
				$arScores[] = $_REQUEST["N_SCORE_".$i];
				$arFields = Array(
					"SCORE" => $_REQUEST["N_SCORE_".$i],
					"MARK" => $_REQUEST["N_MARK_".$i],
					"DESCRIPTION" => $_REQUEST["N_DESCRIPTION_".$i],
					"TEST_ID" => $ID,
				);

				$mark = new CLTestMark;
				$MarkID = $mark->Add($arFields);
				if (intval($MarkID)<=0)
				{
					if ($e = $APPLICATION->GetException())
						$message = new CAdminMessage(GetMessage("LEARNING_ERROR"), $e);
					$bVarsFromForm = true;
				}
			}
		}

		if (sizeof($arScores) && !in_array(100, $arScores))
		{
			$message = new CAdminMessage(Array("MESSAGE" => GetMessage("LEARNING_MAX_MARK_ERROR")));
			$bVarsFromForm = true;
		}
	}

	//Redirect
	if (!$bVarsFromForm)
	{
		$DB->Commit();

		if(empty($apply))
		{
			if(isset($from) && $from == "learn_admin")
			{
				LocalRedirect("/bitrix/admin/learn_unilesson_admin.php?lang=".LANG
					. '&PARENT_LESSON_ID=' . ($_GET['PARENT_LESSON_ID'] ?? 0)
					. '&LESSON_PATH=' . urlencode($_GET['LESSON_PATH'] ?? '')
					."&".GetFilterParams("filter_", false));
			}
			elseif (!empty($return_url))
			{
				if(mb_strpos($return_url, "#TEST_ID#") !== false)
				{
					$return_url = str_replace("#TEST_ID#", $ID, $return_url);
				}
				LocalRedirect($return_url);
			}
			else
			{
				LocalRedirect("/bitrix/admin/learn_test_admin.php?lang=".LANG
					. "&COURSE_ID=" . $COURSE_ID
					. '&PARENT_LESSON_ID=' . ($_GET['PARENT_LESSON_ID'] ?? 0)
					. '&LESSON_PATH=' . urlencode($_GET['LESSON_PATH'] ?? '')
					.GetFilterParams("filter_", false));
			}
		}
		LocalRedirect("/bitrix/admin/learn_test_edit.php?lang=" . LANG
			. "&COURSE_ID=" . $COURSE_ID
			. '&PARENT_LESSON_ID=' . ($_GET['PARENT_LESSON_ID'] ?? 0)
			. '&LESSON_PATH=' . urlencode($_GET['LESSON_PATH'] ?? '')
			. "&ID=" . $ID
			."&tabControl_active_tab=".urlencode(($tabControl_active_tab ?? '')).GetFilterParams("filter_", false));

	}
	else
	{
		if ($actionType == "add")
		{
			$ID = 0;
		}
		$DB->Rollback();
	}
}

if($ID>0)
	$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("LEARNING_EDIT_TITLE2")));
else
	$APPLICATION->SetTitle(GetMessage("LEARNING_EDIT_TITLE1"));

//Defaults
$str_NAME = '';
$str_ACTIVE = "Y";
$str_SORT = "500";
$str_APPROVED = "N";
$str_DESCRIPTION = "";
$str_COMPLETED_SCORE = "95";
$str_INCLUDE_SELF_TEST = "N";
$str_RANDOM_QUESTIONS = "Y";
$str_RANDOM_ANSWERS="Y";
$str_QUESTIONS_FROM="A";
$str_QUESTIONS_AMOUNT = "0";
$str_TIME_LIMIT = "0";
$str_ATTEMPT_LIMIT = "0";
$str_DESCRIPTION_TYPE = "text";
$str_SKIP_QUESTION = "N";
$str_FINAL_RESPONSE = "Y";
$str_PASSAGE_TYPE = "0";
$str_PREVIOUS_TEST_ID = "0";
$str_PREVIOUS_TEST_SCORE = "95";
$str_INCORRECT_CONTROL = "N";
$str_CURRENT_INDICATION_PERCENT = "N";
$str_CURRENT_INDICATION_MARK = "N";
$str_CURRENT_INDICATION = "N";
$str_FINAL_INDICATION_CORRECT_COUNT = "N";
$str_FINAL_INDICATION_SCORE = "N";
$str_FINAL_INDICATION_MARK = "N";
$str_FINAL_INDICATION_MESSAGE = "N";
$str_FINAL_INDICATION = "N";
$str_SHOW_ERRORS = "N";
$str_NEXT_QUESTION_ON_ERROR = "Y";
$str_MIN_TIME_BETWEEN_ATTEMPTS = 0;
$str_MIN_TIME_BETWEEN_ATTEMPTS_D = 0;
$str_MIN_TIME_BETWEEN_ATTEMPTS_H = 0;
$str_MIN_TIME_BETWEEN_ATTEMPTS_M = 0;

$test = new CTest;
$res = $test->GetByID($ID);
if(!$res->ExtractFields("str_"))
{
	$ID = 0;
}
else
{
	if ($str_CURRENT_INDICATION > 0)
	{
		$str_CURRENT_INDICATION_PERCENT = ($str_CURRENT_INDICATION & 1) ? "Y" : "N";
		$str_CURRENT_INDICATION_MARK = ($str_CURRENT_INDICATION & 2) >> 1 ? "Y" : "N";
		$str_CURRENT_INDICATION = "Y";
	}

	if ($str_FINAL_INDICATION > 0)
	{
		$str_FINAL_INDICATION_CORRECT_COUNT = ($str_FINAL_INDICATION & 1) ? "Y" : "N";
		$str_FINAL_INDICATION_SCORE = ($str_FINAL_INDICATION & 2) >> 1 ? "Y" : "N";
		$str_FINAL_INDICATION_MARK = ($str_FINAL_INDICATION & 4) >> 2 ? "Y" : "N";
		$str_FINAL_INDICATION_MESSAGE = ($str_FINAL_INDICATION & 8) >> 3 ? "Y" : "N";
		$str_FINAL_INDICATION = "Y";
	}

	$str_MIN_TIME_BETWEEN_ATTEMPTS_D = floor($str_MIN_TIME_BETWEEN_ATTEMPTS / (60 * 24));
	$str_MIN_TIME_BETWEEN_ATTEMPTS_H = floor(($str_MIN_TIME_BETWEEN_ATTEMPTS - $str_MIN_TIME_BETWEEN_ATTEMPTS_D * 60 * 24) / 60);
	$str_MIN_TIME_BETWEEN_ATTEMPTS_M = $str_MIN_TIME_BETWEEN_ATTEMPTS - $str_MIN_TIME_BETWEEN_ATTEMPTS_D * 60 * 24 - $str_MIN_TIME_BETWEEN_ATTEMPTS_H * 60;
}

if($bVarsFromForm)
{
	$ACTIVE = (!isset($_REQUEST['ACTIVE']) || $_REQUEST['ACTIVE'] != "Y" ? "N" : "Y");
	$APPROVED = (!isset($_REQUEST['APPROVED']) || $_REQUEST['APPROVED'] != "Y" ? "N" : "Y");
	$RANDOM_QUESTIONS = (!isset($_REQUEST['RANDOM_QUESTIONS']) || $_REQUEST['RANDOM_QUESTIONS'] != "Y" ? "N" : "Y");
	$RANDOM_ANSWERS = (!isset($_REQUEST['RANDOM_ANSWERS']) || $_REQUEST['RANDOM_ANSWERS'] != "Y" ? "N" : "Y");
	$INCORRECT_CONTROL = (!isset($_REQUEST['INCORRECT_CONTROL']) || $_REQUEST['INCORRECT_CONTROL'] != "Y" ? "N" : "Y");
	$CURRENT_INDICATION = (!isset($_REQUEST['CURRENT_INDICATION']) || $_REQUEST['CURRENT_INDICATION'] == 0 ? "N" : "Y");
	$FINAL_INDICATION = (!isset($_REQUEST['FINAL_INDICATION']) || $_REQUEST['FINAL_INDICATION'] == 0 ? "N" : "Y");

	$SHOW_ERRORS = !isset($_REQUEST['SHOW_ERRORS']) || $_REQUEST['SHOW_ERRORS'] != "Y" ? "N" : "Y";
	$NEXT_QUESTION_ON_ERROR = !isset($_REQUEST['NEXT_QUESTION_ON_ERROR']) || $_REQUEST['NEXT_QUESTION_ON_ERROR'] != "Y" ? "N" : "Y";
	$DB->InitTableVarsForEdit("b_learn_test", "", "str_");

	$str_CURRENT_INDICATION_PERCENT = (!isset($_REQUEST['CURRENT_INDICATION_PERCENT']) || $_REQUEST['CURRENT_INDICATION_PERCENT'] != "Y"? "N":"Y");
	$str_CURRENT_INDICATION_MARK = (!isset($_REQUEST['CURRENT_INDICATION_MARK']) || $_REQUEST['CURRENT_INDICATION_MARK'] != "Y"? "N":"Y");
	$str_FINAL_INDICATION_CORRECT_COUNT = (!isset($_REQUEST['FINAL_INDICATION_CORRECT_COUNT']) || $_REQUEST['FINAL_INDICATION_CORRECT_COUNT'] != "Y"? "N":"Y");
	$str_FINAL_INDICATION_SCORE = (!isset($_REQUEST['FINAL_INDICATION_SCORE']) || $_REQUEST['FINAL_INDICATION_SCORE'] != "Y"? "N":"Y");
	$str_FINAL_INDICATION_MARK = (!isset($_REQUEST['FINAL_INDICATION_MARK']) || $_REQUEST['FINAL_INDICATION_MARK'] != "Y"? "N":"Y");
	$str_FINAL_INDICATION_MESSAGE = (!isset($_REQUEST['FINAL_INDICATION_MESSAGE']) || $_REQUEST['FINAL_INDICATION_MESSAGE'] != "Y"? "N":"Y");

	$str_MIN_TIME_BETWEEN_ATTEMPTS_D = intval($_REQUEST['MIN_TIME_BETWEEN_ATTEMPTS_D'] ?? 0);
	$str_MIN_TIME_BETWEEN_ATTEMPTS_H = intval($_REQUEST['MIN_TIME_BETWEEN_ATTEMPTS_H'] ?? 0);
	$str_MIN_TIME_BETWEEN_ATTEMPTS_M = intval($_REQUEST['MIN_TIME_BETWEEN_ATTEMPTS_M'] ?? 0);
}

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

if ($message)
	echo $message->Show();

$aContext = array(
	array(
		"ICON"  => "btn_list",
		"TEXT"  => GetMessage("MAIN_ADMIN_MENU_LIST"),
		"LINK"  => "learn_test_admin.php?lang=" . LANG
			. '&PARENT_LESSON_ID=' . ($_GET['PARENT_LESSON_ID'] ?? 0)
			. '&LESSON_PATH=' . htmlspecialcharsbx($_GET['LESSON_PATH'] ?? '')
			. "&COURSE_ID=" . $COURSE_ID
			. GetFilterParams("filter_"),
		"TITLE" => GetMessage("MAIN_ADMIN_MENU_LIST")
	),
);


if ($ID > 0)
{
	$aContext[] = 	array(
		"ICON" => "btn_new",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_CREATE"),
		"LINK"=>"learn_test_edit.php?lang=" . LANG
			. "&COURSE_ID=" . $COURSE_ID
			. '&PARENT_LESSON_ID=' . ($_GET['PARENT_LESSON_ID'] ?? 0)
			. '&LESSON_PATH=' . htmlspecialcharsbx($_GET['LESSON_PATH'] ?? '')
			. GetFilterParams("filter_"),

		"TITLE"=>GetMessage("LEARNING_ADD")
	);

	$returnUrl = "/bitrix/admin/learn_test_admin.php?lang=" . LANG
		. "&amp;COURSE_ID=" . $COURSE_ID
		. '&amp;PARENT_LESSON_ID=' . ($_GET['PARENT_LESSON_ID'] ?? 0)
		. '&amp;LESSON_PATH=' . urlencode($_GET['LESSON_PATH'] ?? '')
		. GetFilterParams("filter_", false);


	$aContext[] = 	array(
		"ICON" => "btn_delete",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"LINK"	=> "javascript:if(confirm('".GetMessage("LEARNING_CONFIRM_DEL_MESSAGE")."'))window.location='learn_test_admin.php?lang=".LANG
			. "&COURSE_ID=" . $COURSE_ID . "&action=delete&ID=" . $ID . "&" . bitrix_sessid_get() . urlencode(GetFilterParams("filter_", false))
			. '&return_url=' . urlencode(urlencode($returnUrl)) . "';",
	);

}

$context = new CAdminContextMenu($aContext);
$context->Show();
?>

<?php $tabControl->BeginEpilogContent();?>
	<?=bitrix_sessid_post()?>
	<?echo GetFilterHiddens("find_");?>
	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="ID" value="<?echo $ID?>">
	<input type="hidden" name="COURSE_ID" value="<?echo $COURSE_ID?>">
	<input type="hidden" name="from" value="<?echo htmlspecialcharsbx($from ?? '')?>">
	<?if(!empty($return_url)):?><input type="hidden" name="return_url" value="<?=htmlspecialcharsbx($return_url)?>"><?endif?>
<?php $tabControl->EndEpilogContent();?>
<?$tabControl->Begin();?>
<?$tabControl->BeginNextFormTab();?>
<!-- ID -->
<?php $tabControl->BeginCustomField("ID", "ID", false);?>
	<?if($ID>0):?>
		<tr>
			<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
			<td><?=$str_ID?></td>
		</tr>
	<? endif; ?>
<?php $tabControl->EndCustomField("ID");?>
<!-- Timestamp_X -->
<?php $tabControl->BeginCustomField("TIMESTAMP_X", GetMessage("LEARNING_LAST_UPDATE"), false);?>
	<?if($ID>0):?>
		<tr>
			<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
			<td><?=$str_TIMESTAMP_X?></td>
		</tr>
	<? endif; ?>
<?php $tabControl->EndCustomField("TIMESTAMP_X");?>
<?php $tabControl->BeginCustomField("ACTIVE", GetMessage("LEARNING_ACTIVE"), false);?>
<!-- Active -->
	<tr>
		<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td><input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>></td>
	</tr>
<?php $tabControl->EndCustomField("ACTIVE");?>
<?php $tabControl->BeginCustomField("NAME", GetMessage("LEARNING_NAME"), false);?>
	<tr class="adm-detail-required-field">
		<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td valign="top">
			<input type="text" name="NAME" size="50" maxlength="255" value="<?echo $str_NAME?>">
		</td>
	</tr>
<?php $tabControl->EndCustomField("NAME");?>
<?php $tabControl->BeginCustomField("SORT", GetMessage("LEARNING_SORT"), false);?>
<!-- Sort -->
	<tr>
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<input type="text" name="SORT" size="10" maxlength="10" value="<?echo $str_SORT?>">
		</td>
	</tr>
<?php $tabControl->EndCustomField("SORT");?>
<?php $tabControl->BeginCustomField("QUESTIONS_FROM", GetMessage("LEARNING_QUESTIONS_FROM"), false);?>
	<tr>
		<td valign="top"><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
		<table>
			<tr>
				<td colspan="2"><input type="radio" name="QUESTIONS_FROM" id="QUESTIONS_FROM_A" value="A"<?if($str_QUESTIONS_FROM=="A" && intval($str_QUESTIONS_AMOUNT)==0)echo " checked"?>  onClick="OnChangeAnswer('');"><label for="QUESTIONS_FROM_A"><? echo GetMessage("LEARNING_QUESTIONS_FROM_ALL")?></label></td>
			</tr>

			<?php
			$linkedLessonId = CCourse::CourseGetLinkedLesson ($COURSE_ID);
			if ($linkedLessonId === false)
				throw new Exception();

			$oTree = CLearnLesson::GetTree ($linkedLessonId);

			$arSubLessons = $oTree->GetTreeAsList();



			// because of some troubles with backward compatibility, some clients can have QUESTIONS_FROM === 'H'
			if($str_QUESTIONS_FROM=="H")
			{
				?>
				<input style="display:none;" type="radio" name="QUESTIONS_FROM" value="H" checked="checked">
				<input type="hidden" name="QUESTIONS_FROM_ID_H" value="<?php echo $str_QUESTIONS_FROM_ID; ?>">
				<?php
			}


			?>
			<tr>
				<td colspan="2">
					<input type="radio" name="QUESTIONS_FROM" id="QUESTIONS_FROM_R" value="R"
						<?php
						if ( ($str_QUESTIONS_FROM=="R") && (intval($str_QUESTIONS_AMOUNT)==0) )
							echo " checked ";
						?>  onclick="OnChangeAnswer('R');"><label for="QUESTIONS_FROM_R"><?php echo GetMessage("LEARNING_QUESTIONS_FROM_ALL_LESSON_WITH_SUBLESSONS"); ?></label>
					<select name="QUESTIONS_FROM_ID_R">
						<?php
						foreach ($arSubLessons as $key => $arSubLesson)
						{
							if ( ($str_QUESTIONS_FROM=="R") && ($str_QUESTIONS_FROM_ID == $arSubLesson['LESSON_ID']) )
								$htmlSelected = ' selected="selected" ';
							else
								$htmlSelected = ' ';

							?>
							<option value="<?php echo $arSubLesson['LESSON_ID']; ?>"
								<?php echo $htmlSelected; ?>><?php
								echo str_repeat('&nbsp;.&nbsp;', $arSubLesson['#DEPTH_IN_TREE'])
									. htmlspecialcharsbx($arSubLesson['NAME']);
							?></option>
							<?php
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="radio" name="QUESTIONS_FROM" id="QUESTIONS_FROM_S" value="S"
						<?php
						if ( ($str_QUESTIONS_FROM=="S") && (intval($str_QUESTIONS_AMOUNT)==0) )
							echo " checked ";
						?>  onclick="OnChangeAnswer('S');"><label for="QUESTIONS_FROM_S"><?php echo GetMessage("LEARNING_QUESTIONS_FROM_ALL_LESSON"); ?></label>
					<select name="QUESTIONS_FROM_ID_S">
						<?php
						foreach ($arSubLessons as $key => $arSubLesson)
						{
							if ( ($str_QUESTIONS_FROM=="S") && ($str_QUESTIONS_FROM_ID == $arSubLesson['LESSON_ID']) )
								$htmlSelected = ' selected="selected" ';
							else
								$htmlSelected = ' ';

							?>
							<option value="<?php echo $arSubLesson['LESSON_ID']; ?>"
								<?php echo $htmlSelected; ?>><?php
								echo str_repeat('&nbsp;.&nbsp;', $arSubLesson['#DEPTH_IN_TREE'])
									. htmlspecialcharsbx($arSubLesson['NAME']);
							?></option>
							<?php
						}
						?>
					</select>
				</td>
			</tr>

			<tr>
				<td colspan="2"><input type="radio" name="QUESTIONS_FROM" id="QUESTIONS_FROM_A2" value="A"<?if($str_QUESTIONS_FROM=="A" && intval($str_QUESTIONS_AMOUNT)!=0)echo " checked"?> onclick="OnChangeAnswer('A');"><label for="QUESTIONS_FROM_A2"><input type="text" name="QUESTIONS_AMOUNT_A" onclick="return false;" size="2" value="<?echo ($str_QUESTIONS_FROM=="A" && $str_QUESTIONS_AMOUNT!=0? $str_QUESTIONS_AMOUNT : "")?>">&nbsp;<? echo GetMessage("LEARNING_QUESTIONS_FROM_COURSE")?></label></td>
			</tr>

			<tr>
				<td><input type="radio" name="QUESTIONS_FROM" id="QUESTIONS_FROM_C" value="C"<?if($str_QUESTIONS_FROM=="C")echo " checked"?> onclick="OnChangeAnswer('C');"><label for="QUESTIONS_FROM_C"><input type="text" name="QUESTIONS_AMOUNT_C" onclick="return false;" size="2" value="<?echo ($str_QUESTIONS_FROM=="C" ? $str_QUESTIONS_AMOUNT : "")?>">&nbsp;<? echo GetMessage("LEARNING_QUESTIONS_FROM_CHAPTERS")?></label></td>
			</tr>

			<tr>
				<td><input type="radio" name="QUESTIONS_FROM" id="QUESTIONS_FROM_L" value="L"<?if($str_QUESTIONS_FROM=="L")echo " checked"?> onclick="OnChangeAnswer('L');"><label for="QUESTIONS_FROM_L"><input type="text" name="QUESTIONS_AMOUNT_L" onclick="return false;" size="2" value="<?echo ($str_QUESTIONS_FROM=="L" ? $str_QUESTIONS_AMOUNT : "")?>">&nbsp;<? echo GetMessage("LEARNING_QUESTIONS_FROM_LESSONS")?></label></td>
			</tr>
		</table>
		<script>
			<?
			if ($str_QUESTIONS_AMOUNT == '0' && $str_QUESTIONS_FROM != "S" && $str_QUESTIONS_FROM != "H" && $str_QUESTIONS_FROM != "R")
				$str = "";
			else
				$str = $str_QUESTIONS_FROM;
			?>

			var QUESTIONS_FROM = '<?=$str?>';


			function OnChangeAnswer(QUESTIONS_FROM)
			{
				var arFrom = new Array('A','L','C');

				for (var i=0; i<arFrom.length; i++)
				{
					if (arFrom[i] != QUESTIONS_FROM)
						document.forms['testTabControl_form'].elements['QUESTIONS_AMOUNT_'+arFrom[i]].disabled = true;
					else
						document.forms['testTabControl_form'].elements['QUESTIONS_AMOUNT_'+arFrom[i]].disabled = false;
				}

				var arFromID = new Array('S','H', 'R');

				for (var i=0; i<arFromID.length; i++)
				{
					if (document.forms['testTabControl_form'].elements['QUESTIONS_FROM_ID_'+arFromID[i]])
					{
						if (arFromID[i] != QUESTIONS_FROM)
							document.forms['testTabControl_form'].elements['QUESTIONS_FROM_ID_'+arFromID[i]].disabled = true;
						else
							document.forms['testTabControl_form'].elements['QUESTIONS_FROM_ID_'+arFromID[i]].disabled = false;
					}
				}

			}

			OnChangeAnswer(QUESTIONS_FROM);
		</script>
		</td>
	</tr>
<?php $tabControl->EndCustomField("QUESTIONS_FROM");?>
<?php $tabControl->BeginCustomField("INCLUDE_SELF_TEST", GetMessage("LEARNING_INCLUDE_SELF_TEST"), false);?>
	<tr>
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<input type="checkbox" name="INCLUDE_SELF_TEST" value="Y"<?if($str_INCLUDE_SELF_TEST=="Y")echo " checked"?>>
		</td>
	</tr>
<?php $tabControl->EndCustomField("INCLUDE_SELF_TEST");?>
<?php $tabControl->BeginCustomField("RANDOM_QUESTIONS", GetMessage("LEARNING_RANDOM_QUESTIONS"), false);?>
	<tr>
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<input type="checkbox" name="RANDOM_QUESTIONS" value="Y"<?if($str_RANDOM_QUESTIONS=="Y")echo " checked"?>>
		</td>
	</tr>
<?php $tabControl->EndCustomField("RANDOM_QUESTIONS");?>
<?php $tabControl->BeginCustomField("RANDOM_ANSWERS", GetMessage("LEARNING_RANDOM_ANSWERS"), false);?>
	<tr>
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<input type="checkbox" name="RANDOM_ANSWERS" value="Y"<?if($str_RANDOM_ANSWERS=="Y")echo " checked"?>>
		</td>
	</tr>
<?php $tabControl->EndCustomField("RANDOM_ANSWERS");?>
<?php $tabControl->BeginCustomField("ATTEMPT_LIMIT", GetMessage("LEARNING_ATTEMPT_LIMIT"), false);?>
	<tr>
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<input type="text" name="ATTEMPT_LIMIT" value="<?echo $str_ATTEMPT_LIMIT?>" size="3"> <? echo GetMessage("LEARNING_ATTEMPT_LIMIT_HINT")?>
		</td>
	</tr>
<?php $tabControl->EndCustomField("ATTEMPT_LIMIT");?>
<?php $tabControl->BeginCustomField("MIN_TIME_BETWEEN_ATTEMPTS", GetMessage("LEARNING_MIN_TIME_BETWEEN_ATTEMPTS"), false);?>
	<tr>
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<input type="text" name="MIN_TIME_BETWEEN_ATTEMPTS_D" value="<?echo $str_MIN_TIME_BETWEEN_ATTEMPTS_D?>" size="3"> <? echo GetMessage("LEARNING_MIN_TIME_BETWEEN_ATTEMPTS_D")?> <input type="text" name="MIN_TIME_BETWEEN_ATTEMPTS_H" value="<?echo $str_MIN_TIME_BETWEEN_ATTEMPTS_H?>" size="3"> <? echo GetMessage("LEARNING_MIN_TIME_BETWEEN_ATTEMPTS_H")?> <input type="text" name="MIN_TIME_BETWEEN_ATTEMPTS_M" value="<?echo $str_MIN_TIME_BETWEEN_ATTEMPTS_M?>" size="3"> <? echo GetMessage("LEARNING_MIN_TIME_BETWEEN_ATTEMPTS_M")?>
		</td>
	</tr>
<?php $tabControl->EndCustomField("MIN_TIME_BETWEEN_ATTEMPTS");?>
<?php $tabControl->BeginCustomField("TIME_LIMIT", GetMessage("LEARNING_TIME_LIMIT"), false);?>
	<tr>
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<input type="text" name="TIME_LIMIT" value="<?echo $str_TIME_LIMIT?>" size="3"> <? echo GetMessage("LEARNING_TIME_LIMIT_HINT")?>
		</td>
	</tr>
<?php $tabControl->EndCustomField("TIME_LIMIT");?>
<?php $tabControl->BeginCustomField("APPROVED", GetMessage("LEARNING_APPROVED"), false);?>
	<tr>
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<input type="checkbox" name="APPROVED" value="Y"<?if($str_APPROVED=="Y")echo " checked"?> onclick="OnChangeApproved(this.checked);">
		</td>
	</tr>
<?php $tabControl->EndCustomField("APPROVED");?>
<?php $tabControl->BeginCustomField("COMPLETED_SCORE", GetMessage("LEARNING_COMPLETED_SCORE"), false);?>
	<tr>
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<input type="text" name="COMPLETED_SCORE" size="3" maxlength="3" value="<?echo $str_COMPLETED_SCORE?>">
			<? echo GetMessage("LEARNING_COMPLETED_SCORE2")?>
		</td>
	</tr>
	<script>
		function OnChangeApproved(val)
		{
			document.forms['testTabControl_form'].elements['COMPLETED_SCORE'].disabled = !val;
		}
		OnChangeApproved(<?=($str_APPROVED=="Y"?"true":"false")?>);
	</script>
<?php $tabControl->EndCustomField("COMPLETED_SCORE");?>
<?php $tabControl->BeginCustomField("PASSAGE_TYPE", GetMessage("LEARNING_PASSAGE_TYPE"), false);?>
	<tr>
		<td valign="top"><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<table>
			<tr>
				<td valign="top"><input type="radio" name="PASSAGE_TYPE" id="PASSAGE_TYPE_0" value="0"<?if($str_PASSAGE_TYPE=="0")echo " checked"?> onclick="toggleNextQ();"></td>
				<td><label for="PASSAGE_TYPE_0"><?=GetMessage("LEARNING_PASSAGE_TYPE_0")?></label></td>
			</tr>
			<tr>
				<td valign="top"><input type="radio" name="PASSAGE_TYPE" id="PASSAGE_TYPE_1" value="1"<?if($str_PASSAGE_TYPE=="1")echo " checked"?> onclick="toggleNextQ();"></td>
				<td><label for="PASSAGE_TYPE_1"><?=GetMessage("LEARNING_PASSAGE_TYPE_1")?></label></td>
			</tr>
			<tr>
				<td valign="top"><input type="radio" name="PASSAGE_TYPE" id="PASSAGE_TYPE_2" value="2"<?if($str_PASSAGE_TYPE=="2")echo " checked"?> onclick="toggleNextQ();"></td>
				<td><label for="PASSAGE_TYPE_2"><?=GetMessage("LEARNING_PASSAGE_TYPE_2")?></label></td>
			</tr>
			</table>
		</td>
	</tr>
<?php $tabControl->EndCustomField("PASSAGE_TYPE");?>
<?php $tabControl->BeginCustomField("PREVIOUS_TEST", GetMessage("LEARNING_PREVIOUS_TEST_ID"), false);

	$PREVIOUS_TEST_COURSE_ID = null;
	$t = CTest::GetList(array(), array("ACTIVE" => "Y", 'ID' => $str_PREVIOUS_TEST_ID));
	if ($arData = $t->Fetch())
		$PREVIOUS_TEST_COURSE_ID = $arData['COURSE_ID'];

	?>
	<tr>
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td valign="top">
		<script>
			function filterTests()
			{
				var course = BX("PREVIOUS_TEST_COURSE_ID");
				var test = BX("PREVIOUS_TEST_ID");
				var iSelectIndex = 0;
				var needSelectedIndex = 0;

				while(test.options.length > 1)
				{
					test.options.remove(1);
				}
				for(var i = 0, count = sourceList.options.length; i < count; i++)
				{
					if(sourceList.options[i].getAttribute("data-course") == course.options[course.selectedIndex].value)
					{
						iSelectIndex = iSelectIndex + 1;
						var tmp = sourceList.options[i].cloneNode(true);

						var newElem = document.createElement("option");
						newElem.text = sourceList.options[i].textContent;
						newElem.value = sourceList.options[i].value;

						if (sourceList.options[i].index == sourceList.selectedIndex)
							needSelectedIndex = iSelectIndex;

						test.options.add(newElem);
					}
				}

				if (needSelectedIndex !== false)
					test.selectedIndex = needSelectedIndex;
			}
		</script>
		<select name="PREVIOUS_TEST_COURSE_ID" id="PREVIOUS_TEST_COURSE_ID" onchange="filterTests()">
			<?
			// was: $course = CCourse::GetList(array("SORT" => "ASC"), array("MIN_PERMISSION" => "W"));
			$course = CCourse::GetList(array("SORT" => "ASC"), array("ACCESS_OPERATIONS" => CLearnAccess::OP_LESSON_READ));
			while ($course->ExtractFields("f_"))
			{
				?><option value="<?echo $f_ID ?>" <?if (intval($f_ID)==$PREVIOUS_TEST_COURSE_ID || (!isset($PREVIOUS_TEST_COURSE_ID) && intval($f_ID)==$COURSE_ID)) echo "selected";?>><?echo $f_NAME ?></option><?
			}
			?>
		</select>
		<?$t = CTest::GetList(array(), array("ACTIVE" => "Y"));?>
		<select name="PREVIOUS_TEST_ID" id="PREVIOUS_TEST_ID" onchange="OnChangePreviousTest();">
			<option value="0">&lt;<? echo GetMessage("LEARNING_TEST_NO_DEPENDS")?>&gt;</option>
			<?
				while($t->ExtractFields("t_")):
					if (!isset($ID) || $ID != $t_ID):
			?>
					<option data-course="<?php echo intval($t_COURSE_ID)?>" value="<?echo $t_ID?>"<?if($str_PREVIOUS_TEST_ID == $t_ID)echo " selected"?>><?echo $t_NAME?></option>
			<?
					endif;
				endwhile;
			?>
		</select>
		<script>
			var sourceList = BX("PREVIOUS_TEST_ID").cloneNode(true);
			sourceList.selectedIndex = BX("PREVIOUS_TEST_ID").selectedIndex;
			filterTests();
		</script>
		<? echo GetMessage("LEARNING_PREVIOUS_TEST_SCORE")?>
		<input type="text" name="PREVIOUS_TEST_SCORE" size="3" maxlength="3" value="<?echo $str_PREVIOUS_TEST_SCORE?>">
		<? echo GetMessage("LEARNING_PREVIOUS_TEST_SCORE2")?>
		</td>
	</tr>
	<script>
		function OnChangePreviousTest()
		{
			document.forms['testTabControl_form'].elements['PREVIOUS_TEST_SCORE'].disabled = !document.forms['testTabControl_form'].elements['PREVIOUS_TEST_ID'].selectedIndex;
		}
		OnChangePreviousTest();
	</script>
<?php $tabControl->EndCustomField("PREVIOUS_TEST");?>
<?php $tabControl->BeginCustomField("INCORRECT_CONTROL", GetMessage("LEARNING_INCORRECT_CONTROL"), false);?>
	<tr>
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<input type="checkbox" name="INCORRECT_CONTROL" value="Y"<?if($str_INCORRECT_CONTROL=="Y")echo " checked"?>>
		</td>
	</tr>
<?php $tabControl->EndCustomField("INCORRECT_CONTROL");?>
<?php $tabControl->BeginCustomField("CURRENT_INDICATION", GetMessage("LEARNING_CURRENT_INDICATION"), false);?>
	<tr>
		<td valign="top"><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<input type="checkbox" name="CURRENT_INDICATION" value="Y"<?if($str_CURRENT_INDICATION == "Y")echo " checked"?> onClick="toggleIndication(this.checked, 1);" id="indication_cb_1">
			<div id="indication_1">
				<label><input type="checkbox" name="CURRENT_INDICATION_PERCENT" value="Y"<?if($str_CURRENT_INDICATION_PERCENT == "Y")echo " checked"?>><? echo GetMessage("LEARNING_CURRENT_INDICATION_PERCENT")?></label><br />
				<label><input type="checkbox" name="CURRENT_INDICATION_MARK" value="Y"<?if($str_CURRENT_INDICATION_MARK =="Y")echo " checked"?>><? echo GetMessage("LEARNING_CURRENT_INDICATION_MARK")?></label>
			</div>
		</td>
	</tr>
<?php $tabControl->EndCustomField("CURRENT_INDICATION");?>
<?php $tabControl->BeginCustomField("FINAL_INDICATION", GetMessage("LEARNING_FINAL_INDICATION"), false);?>
	<tr>
		<td valign="top"><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<input type="checkbox" name="FINAL_INDICATION" value="Y"<?if($str_FINAL_INDICATION == "Y")echo " checked"?> onClick="toggleIndication(this.checked, 2);" id="indication_cb_2">
			<div id="indication_2">
				<label><input type="checkbox" name="FINAL_INDICATION_CORRECT_COUNT" value="Y"<?if($str_FINAL_INDICATION_CORRECT_COUNT == "Y")echo " checked"?>><? echo GetMessage("LEARNING_FINAL_INDICATION_CORRECT_COUNT")?></label><br />
				<label><input type="checkbox" name="FINAL_INDICATION_SCORE" value="Y"<?if($str_FINAL_INDICATION_SCORE == "Y")echo " checked"?>><? echo GetMessage("LEARNING_FINAL_INDICATION_SCORE")?></label><br />
				<label><input type="checkbox" name="FINAL_INDICATION_MARK" value="Y"<?if($str_FINAL_INDICATION_MARK == "Y")echo " checked"?>><? echo GetMessage("LEARNING_FINAL_INDICATION_MARK")?></label><br />
				<label><input type="checkbox" name="FINAL_INDICATION_MESSAGE" value="Y"<?if($str_FINAL_INDICATION_MESSAGE == "Y")echo " checked"?>><? echo GetMessage("LEARNING_FINAL_INDICATION_MESSAGE")?></label>
			</div>
		</td>
	</tr>
	<script>
		function toggleIndication(visible, num)
		{
			if (visible)
				document.getElementById("indication_" + num).style.display = "block";
			else
				document.getElementById("indication_" + num).style.display = "none";
		}

		toggleIndication(document.getElementById("indication_cb_1").checked, 1);
		toggleIndication(document.getElementById("indication_cb_2").checked, 2);
	</script>
<?php $tabControl->EndCustomField("FINAL_INDICATION");?>
<?php $tabControl->BeginCustomField("SHOW_ERRORS", GetMessage("LEARNING_SHOW_ERRORS"), false);?>
	<tr>
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<input type="checkbox" name="SHOW_ERRORS" value="Y"<?if($str_SHOW_ERRORS=="Y")echo " checked"?> onClick="toggleNextQ();" id="show_errors">
		</td>
	</tr>
<?php $tabControl->EndCustomField("SHOW_ERRORS");?>
<?php $tabControl->BeginCustomField("NEXT_QUESTION_ON_ERROR", GetMessage("LEARNING_ON_ERROR"), false);?>
	<tr id="next_q_on_error">
		<td valign="top"><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<input type="radio" name="NEXT_QUESTION_ON_ERROR" value="Y"<?if($str_NEXT_QUESTION_ON_ERROR!="N")echo " checked"?>>&nbsp;<? echo GetMessage("LEARNING_NEXT_QUESTION_ON_ERROR")?><br />
			<input type="radio" name="NEXT_QUESTION_ON_ERROR" value="N"<?if($str_NEXT_QUESTION_ON_ERROR=="N")echo " checked"?>>&nbsp;<? echo GetMessage("LEARNING_PREV_QUESTION_ON_ERROR")?>
		</td>
	</tr>
	<script>
		function toggleNextQ()
		{
			if (document.getElementById("show_errors").checked && document.getElementsByName("PASSAGE_TYPE")[2].checked)
			{
				document.getElementById("next_q_on_error").style.display = "";
			}
			else
			{
				document.getElementById("next_q_on_error").style.display = "none";
			}
		}

		toggleNextQ();
	</script>
<?php $tabControl->EndCustomField("NEXT_QUESTION_ON_ERROR");?>

<?$tabControl->BeginNextFormTab();?>
<?php $tabControl->BeginCustomField("DESCRIPTION", GetMessage("LEARNING_DESCRIPTION"), false);?>
	<?if(COption::GetOptionString("learning", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame(
				"DESCRIPTION",
				$str_DESCRIPTION,
				"DESCRIPTION_TYPE",
				$str_DESCRIPTION_TYPE,
				array(
					'height' => 450,
					'width' => '100%'
				),
				"N",
				0,
				"",
				"",
				false,
				true,
				false,
				array('toolbarConfig' => CFileman::GetEditorToolbarConfig("learning_".(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1 ? 'public' : 'admin')))
			);?>
		</td>
	</tr>
	<?else:?>
	<tr>
		<td align="center"><?echo GetMessage("LEARNING_DESC_TYPE")?>:</td>
		<td>
			<input type="radio" name="DESCRIPTION_TYPE" value="text"<?if($str_DESCRIPTION_TYPE!="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_TEXT")?>
			<input type="radio" name="DESCRIPTION_TYPE" value="html"<?if($str_DESCRIPTION_TYPE=="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_HTML")?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<textarea style="width:100%; height:250px;" name="DESCRIPTION" wrap="off"><?echo $str_DESCRIPTION?></textarea>
		</td>
	</tr>
	<?endif?>
<?php $tabControl->EndCustomField("DESCRIPTION");?>

<?$tabControl->BeginNextFormTab();?>
<?php $tabControl->BeginCustomField("MARKS", GetMessage("LEARNING_MARKS"), false);?>
	<tr>
		<td colspan="2">
			<table cellpadding="0" cellspacing="0" width="100%" class="internal" id="marks-table">
				<tbody id="marks-table-tbody">
				<tr class="heading">
					<td align="center" width="10%">ID</td>
					<td align="center" width="15%"><?echo GetMessage("LEARNING_TEST_MARK_SCORE")?></td>
					<td align="center" width="20%"><?echo GetMessage("LEARNING_TEST_MARK")?></td>
					<td align="center" width="50%"><?echo GetMessage("LEARNING_TEST_MARK_MESSAGE")?></td>
					<td align="center" width="10%"><?echo GetMessage("LEARNING_TEST_MARK_DELETE")?></td>
				</tr>
				<?php
					if ($ID)
					{
						$marks = CLTestMark::GetList(Array("score" => "DESC"),Array("TEST_ID" => $ID));
						while($arMarkData = $marks->Fetch())
						{
							$s_ID          = (integer) $arMarkData['ID'];
							$s_SCORE       = (int) $arMarkData['SCORE'];
							$s_MARK        = htmlspecialcharsbx($arMarkData['MARK']);
							$s_DESCRIPTION = htmlspecialcharsbx($arMarkData['DESCRIPTION']);
							?>
							<tr>
								<td align="center"><?php echo $s_ID?></td>
								<td align="center">
									<div style="white-space:nowrap;"><?php
										echo GetMessage("LEARNING_TEST_SCORE_TILL");
									?> <input type="text" size="4" name="SCORE_<?php echo $s_ID?>" value="<?php
										echo isset(${"SCORE_".$s_ID}) ? ((int) ${"SCORE_".$s_ID}) : $s_SCORE;
									?>"> %</div>
								</td>
								<td align="center">
									<input type="text" size="20"  name="MARK_<?php echo $s_ID?>" value="<?php
										echo isset(${"MARK_".$s_ID}) ? htmlspecialcharsbx(${"MARK_".$s_ID}) : $s_MARK;
									?>">
								</td>
								<td align="center">
									<input type="text" size="60"  name="DESCRIPTION_<?php echo $s_ID?>" value="<?php
										echo isset(${"DESCRIPTION_".$s_ID}) ? htmlspecialcharsbx(${"DESCRIPTION_".$s_ID}) : $s_DESCRIPTION;
									?>">
								</td>
								<td align="center"><input type="checkbox" name="MARK_<?php echo $s_ID?>_DEL" value="Y"></td>
							</tr><?php
						}
					}

					foreach($arNewIDs as $i):?>
					<tr>
						<td align="center">&nbsp;</td>
						<td align="center">
							<div style="white-space:nowrap;"><?echo GetMessage("LEARNING_TEST_SCORE_TILL")?> <input type="text" size="4" name="N_SCORE_<?php echo $i?>" value="<?php echo isset(${"N_SCORE_".$i}) ? intval(${"N_SCORE_".$i}) : ""?>"> %</div>
						</td>
						<td align="center">
							<input type="text" size="20"  name="N_MARK_<?php echo $i?>" value="<?php echo isset(${"N_MARK_".$i}) ? htmlspecialcharsbx(${"N_MARK_".$i}) : ""?>">
						</td>
						<td align="center">
							<input type="text" size="60"  name="N_DESCRIPTION_<?php echo $i?>" value="<?php echo isset(${"N_DESCRIPTION_".$i}) ? htmlspecialcharsbx(${"N_DESCRIPTION_".$i}) : ""?>">
						</td>
						<td align="center"><a href="javascript:void(0);" onclick="BX.remove(this.parentNode.parentNode)"><img src="/bitrix/themes/.default/images/actions/delete_button.gif" border="0" width="20" height="20"/></a><input type="hidden" name="ANSWER_HIDDEN_ID[]" value="<?php echo (int) $i; ?>"></td>
					</tr>
				<?php endforeach?>
				</tbody>
			</table>
			<script>
				var nextNum = <?php echo $nextNum?>;
				function addMark() {
					var row = BX.create("tr", {
						children: [
							BX.create('td', {
								html : '&nbsp;'
							}),
							BX.create('td', {
								html : '<div style="white-space:nowrap;"><?echo GetMessage("LEARNING_TEST_SCORE_TILL")?> <input type="text" size="4" name="N_SCORE_' + nextNum + '" value=""> %</div>',
								props : {align: 'center'}
							}),
							BX.create('td', {
								html : '<input type="text" size="20"  name="N_MARK_' + nextNum + '" value="">',
								props : {align: 'center'}
							}),
							BX.create('td', {
								html : '<input type="text" size="60"  name="N_DESCRIPTION_' + nextNum + '" value="">',
								props : {align: 'center'}
							}),
							BX.create('td', {
								html : '<a href="javascript:void(0);" onclick="BX.remove(this.parentNode.parentNode)"><img src="/bitrix/themes/.default/images/actions/delete_button.gif" border="0" width="20" height="20"/></a><input type="hidden" name="ANSWER_HIDDEN_ID[]" value="n' + nextNum + '">',
								props : {align: 'center'}
							})
						]
					});

					nextNum++;
					BX("marks-table-tbody").appendChild(row);
				}
				<?php
				if ($ID == 0)
				{
					?>
					addMark();
					<?php
				}
				?>
			</script>
			<br />
			<a href="javascript:void(0)" class="adm-btn" onclick="addMark();"><?php echo GetMessage("LEARNING_ADD_MARK")?></a>
		</td>
	</tr>
<?php $tabControl->EndCustomField("MARKS");?>

<?
$tabControl->Buttons(
	array(
		'disabled' => $isBtnsDisabled,
		"back_url" =>"learn_test_admin.php?lang=". LANG
		. '&PARENT_LESSON_ID=' . ($_GET['PARENT_LESSON_ID'] ?? 0)
		. '&LESSON_PATH=' . htmlspecialcharsbx($_GET['LESSON_PATH'] ?? '')
		. "&COURSE_ID=" . $COURSE_ID
		. GetFilterParams("find_", false)));

$tabControl->arParams["FORM_ACTION"] = $APPLICATION->GetCurPage() . "?lang=" . LANG
	. "&COURSE_ID=" . $COURSE_ID
	. '&PARENT_LESSON_ID=' . ($_GET['PARENT_LESSON_ID'] ?? 0)
	. '&LESSON_PATH=' . htmlspecialcharsbx($_GET['LESSON_PATH'] ?? '')
	. "&ID=" . $ID;
$tabControl->Show();

$tabControl->ShowWarnings($tabControl->GetName(), $message);

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
