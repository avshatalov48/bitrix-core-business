<?php
/**
 * This file is modified admin_lesson_edit.php (with added additional field for 'CODE' from admin_chapter_edit.php).
 * 
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

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

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/learning/prolog.php');

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/learning/options.php");
IncludeModuleLangFile(__FILE__);

$APPLICATION->AddHeadScript('/bitrix/js/learning/rights_edit.js');

$aContext = array();
$oAccess = CLearnAccess::GetInstance($USER->GetID());

ClearVars();

$message = null;
$bVarsFromForm = false;
$LESSON_ID = intval($LESSON_ID);

if (isset($g_learn_parentLessonId))
	unset($g_learn_parentLessonId);

if (isset($g_learn_currentLessonPath))
	unset($g_learn_currentLessonPath);

if (isset($g_learn_parentLessonPath))
	unset($g_learn_parentLessonPath);

if (isset($_POST['LESSON_PATH']) || isset($_GET['LESSON_PATH']))
{
	if (isset($_POST['LESSON_PATH']))
		$g_learn_currentLessonPath = $_POST['LESSON_PATH'];
	else
		$g_learn_currentLessonPath = $_GET['LESSON_PATH'];

	$oPath = new CLearnPath();
	$oPath->ImportUrlencoded ($g_learn_currentLessonPath);
	$arPath = $oPath->GetPathAsArray();
	if (count($arPath) >= 2)
	{
		// pop current lesson id
		array_pop ($arPath);

		// remember parent path
		$oParentPath = new CLearnPath();
		$oParentPath->SetPathFromArray($arPath);
		$g_learn_parentLessonPath = urldecode($oParentPath->ExportUrlencoded());

		// pop parent lesson id
		$g_learn_parentLessonId = array_pop ($arPath);

		// If lesson was edited and unlinked from parent $g_parentLessonId
		// we must cleanup LESSON_PATH. Also we must cleanup lesson path if
		// it is wrong due to other reasons.
//		$arEdgesToParents = CLearnLesson::ListImmediateParents($LESSON_ID);
//
//		$isLinkToParentFound = true;
//		foreach ($arEdgesToParents as $arEdgeToParent)
//		{
//			if ( (int) $arEdgeToParent['PARENT_LESSON'] === (int) $g_learn_parentLessonId )
//				$isLinkToParentFound = true;
//		}
//
//		if ( ! $isLinkToParentFound )
//		{
//			if (isset($_POST['LESSON_PATH']))
//				unset ($_POST['LESSON_PATH']);
//
//			if (isset($_GET['LESSON_PATH']))
//				unset ($_GET['LESSON_PATH']);
//
//			unset ($g_learn_parentLessonId, $g_learn_parentLessonPath, $g_learn_currentLessonPath);
//		}
	}
	unset ($oPath, $oParentPath, $arPath);
}

// This argument transmitted when pended new lesson creation
if (isset($_GET['PROPOSE_RETURN_LESSON_PATH']))
{
	if (isset($g_learn_parentLessonPath))
	{
		throw new LearnException (
			'EA_LOGIC: PROPOSE_RETURN_LESSON_PATH and '
			. 'LESSON_PATH are mutually exclusive arguments.', 
			LearnException::EXC_ERR_ALL_LOGIC);
	}

	$g_learn_parentLessonPath = $_GET['PROPOSE_RETURN_LESSON_PATH'];

	$oPath = new CLearnPath();
	$oPath->ImportUrlencoded ($g_learn_parentLessonPath);
	$g_learn_parentLessonId = $oPath->GetBottom();
	if ($g_learn_parentLessonId === false)
	{
		throw new LearnException (
			'EA_LOGIC: PROPOSE_RETURN_LESSON_PATH given, '
			. 'but there is no parent lesson in path', 
			LearnException::EXC_ERR_ALL_LOGIC);
	}
}

// Place to $topCourseLessonId lesson id of top course, if top lesson is course.
$topCourseLessonId = false;
if (isset($g_learn_parentLessonPath) && strlen($g_learn_parentLessonPath))
{
	try
	{
		$oPath = new CLearnPath();
		$oPath->ImportUrlencoded ($g_learn_parentLessonPath);
		$topLessonId = $oPath->GetTop();

		// Is lesson the course?
		if (CLearnLesson::GetLinkedCourse($topLessonId) !== false)
			$topCourseLessonId = $topLessonId;
	}
	catch (Exception $e)
	{
		$topCourseLessonId = false;
	}

	unset ($oPath, $topLessonId);
}


// This argument can be transmitted on POST submit of form when creating new lesson
if (isset($_GET['PARENT_LESSON_ID']))
	$_POST['PARENT_LESSON_ID'] = $_GET['PARENT_LESSON_ID'];

if (isset($_POST['PARENT_LESSON_ID']))
{
	$g_learn_parentLessonId = $_POST['PARENT_LESSON_ID'];
}

$createNewLesson = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && strlen($Update)>0 && check_bitrix_sessid())
{
	$arEdgeProperties = array();

	if ($LESSON_ID == 0)
		$createNewLesson = true;

	$was_errors = false;
	// Block 1: params, preview_text and detail_text
	if (array_key_exists('NAME', $_POST)
		&& array_key_exists('DETAIL_TEXT', $_POST)
	)
	{
		if (
			(($LESSON_ID == 0) && $oAccess->IsBaseAccess(CLearnAccess::OP_LESSON_CREATE))
			|| (($LESSON_ID != 0) && $oAccess->IsLessonAccessible ($LESSON_ID, CLearnAccess::OP_LESSON_WRITE))
		)
		{
			$arPREVIEW_PICTURE = $_FILES["PREVIEW_PICTURE"];
			$arPREVIEW_PICTURE["del"] = ${"PREVIEW_PICTURE_del"};
			$arPREVIEW_PICTURE["MODULE_ID"] = "learning";
			$arPREVIEW_PICTURE["description"] = ${"PREVIEW_PICTURE_descr"};

			$arDETAIL_PICTURE = $_FILES["DETAIL_PICTURE"];
			$arDETAIL_PICTURE["del"] = ${"DETAIL_PICTURE_del"};
			$arDETAIL_PICTURE["MODULE_ID"] = "learning";
			$arDETAIL_PICTURE["description"] = ${"DETAIL_PICTURE_descr"};

			// If we are in context of parent lesson => there is edge params
			if (isset($g_learn_parentLessonId))
			{
				$arEdgeProperties = array(
					'SORT' => $_POST['EDGE_SORT']
					);
			}

			$arFields = Array(
				"ACTIVE" => ( ($ACTIVE !== 'Y') ? 'N' : 'Y'),
				"NAME" => $NAME,
				"KEYWORDS" => $KEYWORDS,
				"CODE" => $CODE,

				"DETAIL_PICTURE" => $arDETAIL_PICTURE,
				"DETAIL_TEXT" => $DETAIL_TEXT,
				"DETAIL_TEXT_TYPE" => $DETAIL_TEXT_TYPE,

				"PREVIEW_PICTURE" => $arPREVIEW_PICTURE,
				"PREVIEW_TEXT" => $PREVIEW_TEXT,
				"PREVIEW_TEXT_TYPE" => $PREVIEW_TEXT_TYPE
			);

			if ($CONTENT_SOURCE == "file")
			{
				$arFields["DETAIL_TEXT_TYPE"] = "file";
				$arFields["LAUNCH"] = $LAUNCH;
			}

			$USER_FIELD_MANAGER->EditFormAddFields('LEARNING_LESSONS', $arFields);

			$res = true;
			try
			{
				if ($LESSON_ID > 0)
				{
					$res = CLearnLesson::Update($LESSON_ID, $arFields);

					// If we are in context of parent lesson => update edges properties
					if ($res && isset($g_learn_parentLessonId) && $g_learn_parentLessonId > 0)
					{
						CLearnLesson::RelationUpdate (
							$g_learn_parentLessonId, 
							$LESSON_ID, 
							$arEdgeProperties);
					}
				}
				else
				{
					// If we are in context of parent lesson => create linked lesson
					if (isset($g_learn_parentLessonId) && $g_learn_parentLessonId > 0)
					{
						$arNewEdgeProperties = array('SORT' => 500);
						if (isset($arEdgeProperties['SORT']))
							$arNewEdgeProperties['SORT'] = $arEdgeProperties['SORT'];

						$LESSON_ID = CLearnLesson::Add(
							$arFields, 
							false,		// is course?
							$g_learn_parentLessonId,
							$arNewEdgeProperties);
					}
					else
					{
						$LESSON_ID = CLearnLesson::Add($arFields);
					}

					$res = ($LESSON_ID > 0);
				}

				// PUBLISH_PROHIBITED available in context of most parent course only
				if ( ($LESSON_ID > 0) && ($topCourseLessonId !== false) )
				{
					$isProhibited = false;

					if ($_POST['PUBLISH_PROHIBITED'] === 'Y')
						$isProhibited = true;

					CLearnLesson::PublishProhibitionSetTo(
						$LESSON_ID, $topCourseLessonId, $isProhibited);
				}

			}
			catch (Exception $e)
			{
				$res = false;
			}

			if(!$res)
			{
				if($e = $APPLICATION->GetException())
					$message = new CAdminMessage(GetMessage("LEARNING_ERROR"), $e);
				else
					$message = new CAdminMessage(GetMessage("LEARNING_ERROR"));

				$bVarsFromForm = true;
				$was_errors = true;
			}
		}
		else
		{
			if($e = $APPLICATION->GetException())
				$message = new CAdminMessage(GetMessage("LEARNING_ERROR") . ': ' . GetMessage('LEARNING_ACCESS_D_FOR_EDIT_CONTENT'), $e);
			else
				$message = new CAdminMessage(GetMessage("LEARNING_ERROR") . ': ' . GetMessage('LEARNING_ACCESS_D_FOR_EDIT_CONTENT'));

			$bVarsFromForm = true;

			$res = false;
			$was_errors = true;
		}
	}

	// Block 2: relations (there is will be silently ignoring on insuficient permissions)
	// Process relations, data submitted from CLearnRelationHelper::RenderForm()
	if ( 
		( ! $createNewLesson ) 
		&& ($LESSON_ID > 0) 
		&& ( ! isset($_REQUEST['SKIP_RELATIONS_SAVING']) )
	)
	{
		$sort = false;	// default sort order will be used for new edges
		if (isset($_POST['EDGE_SORT']) && ($_POST['EDGE_SORT'] > 0))
			$sort = (int) $_POST['EDGE_SORT'];
		CLearnRelationHelper::ProccessPOST($oAccess, $LESSON_ID, $sort);
	}

	// Block 3: permissions
	if (
		array_key_exists('LESSON_RIGHTS_marker', $_POST) 
		&& ($LESSON_ID > 0)
		&& ( ! isset($_REQUEST['SKIP_RIGHTS_SAVING']) )
	)
	{
		$res = true;
		try
		{
			// Work with permissions
			if ($oAccess->IsLessonAccessible ($LESSON_ID, CLearnAccess::OP_LESSON_MANAGE_RIGHTS))
			{
				// Process permissions
				$arPostedRights = array();
				if (is_array($_POST['LESSON_RIGHTS']))
					$arPostedRights = $_POST['LESSON_RIGHTS'];

				$arAccessSymbols = array();
				$arTaskIds = array();
				foreach ($arPostedRights as $key => $arData)
				{
					if (isset($arData['GROUP_CODE']))
						$arAccessSymbols[] = $arData['GROUP_CODE'];
					elseif (isset($arData['TASK_ID']))
						$arTaskIds[] = $arData['TASK_ID'];
				}
				if (count($arAccessSymbols) !== count($arTaskIds))
					throw new LearnException('', LearnException::EXC_ERR_ALL_LOGIC | LearnException::EXC_ERR_ALL_GIVEUP);

				$arPermPairs = array();
				if (count($arAccessSymbols) > 0)
					$arPermPairs = array_combine($arAccessSymbols, $arTaskIds);

				if ($arPermPairs === false)
					$arPermPairs = array();

				// Save permissions
				$oAccess->SetLessonsPermissions (array($LESSON_ID => $arPermPairs));
			}
			else
			{
				if($e = $APPLICATION->GetException())
					$message = new CAdminMessage(GetMessage("LEARNING_ERROR") . ': ' . GetMessage('LEARNING_ACCESS_D_FOR_MANAGE_RIGHTS'), $e);
				else
					$message = new CAdminMessage(GetMessage("LEARNING_ERROR") . ': ' . GetMessage('LEARNING_ACCESS_D_FOR_MANAGE_RIGHTS'));

				$bVarsFromForm = true;

				$res = false;
				$was_errors = true;
			}
		}
		catch (Exception $e)
		{
			$res = false;
			$was_errors = true;
		}
	}

	if ( ! $was_errors )
	{

		$currentLessonPath = "";
		if (isset($g_learn_currentLessonPath))
		{
			$currentLessonPath = $g_learn_currentLessonPath.($createNewLesson && $LESSON_ID > 0 ? ".".$LESSON_ID : "");
		}

		if (strlen($apply) <= 0)
		{
			if (strlen($return_url) > 0)
			{
				LocalRedirect(
					str_replace(
						array("#LESSON_ID#", "#LESSON_PATH#"),
						array($LESSON_ID, $currentLessonPath),
						$return_url
					)
				);
			}
			else
			{
				$uriParentLessonPath = "";
				if (!$createNewLesson && isset($g_learn_parentLessonPath))
				{
					$uriParentLessonPath = '&LESSON_PATH=' . urlencode($g_learn_parentLessonPath);
				}
				elseif (isset($g_learn_currentLessonPath))
				{
					$uriParentLessonPath = '&LESSON_PATH=' . urlencode($g_learn_currentLessonPath);
				}

				LocalRedirect(
					"/bitrix/admin/learn_unilesson_admin.php?lang=".LANG.
					"&PARENT_LESSON_ID=".intval(isset($g_learn_parentLessonId) ? $g_learn_parentLessonId : 0).
					$uriParentLessonPath.
					GetFilterParams("filter_", false, array())
				);
			}
		}

		LocalRedirect("/bitrix/admin/learn_unilesson_edit.php?lang=" . LANG 
			. "&LESSON_ID=" . ($LESSON_ID + 0)
			. "&LESSON_PATH=". urlencode($currentLessonPath)
			. "&lessonTabControl_active_tab=" . urlencode($_REQUEST['lessonTabControl_active_tab'])
			. GetFilterParams("filter_", false));
	}
}

$bBadCourse = false;

$isBtnsDisabled = true;

if (($LESSON_ID > 0) && $oAccess->IsLessonAccessible ($LESSON_ID, CLearnAccess::OP_LESSON_WRITE))
{
	$isBtnsDisabled = false;
}
elseif ($LESSON_ID == 0)
{
	if (isset($g_learn_parentLessonId) && $g_learn_parentLessonId > 0)
	{

		if ($oAccess->IsBaseAccess(CLearnAccess::OP_LESSON_LINK_DESCENDANTS | CLearnAccess::OP_LESSON_LINK_TO_PARENTS)
		|| (
			$oAccess->IsLessonAccessible($g_learn_parentLessonId, CLearnAccess::OP_LESSON_LINK_DESCENDANTS)
			&& $oAccess->IsBaseAccessForCR(CLearnAccess::OP_LESSON_LINK_TO_PARENTS)
		))
		{
			$isBtnsDisabled = false;
		}
	}
	else
	{
		if ($oAccess->IsBaseAccess (CLearnAccess::OP_LESSON_CREATE))
		{
			$isBtnsDisabled = false;
		}
	}
}
elseif ( ($LESSON_ID > 0) && CLearnAccessMacroses::CanUserEditLessonRights (array('lesson_id' => $LESSON_ID)) )
{
	$isBtnsDisabled = false;
}
elseif ( ($LESSON_ID > 0) && CLearnAccessMacroses::CanUserPerformAtLeastOneRelationAction (array('lesson_id' => $LESSON_ID)) )
{
	$isBtnsDisabled = false;
}

if (($LESSON_ID > 0) && $oAccess->IsLessonAccessible ($LESSON_ID, CLearnAccess::OP_LESSON_READ))
	$APPLICATION->SetTitle(GetMessage("LEARNING_LESSONS").": ".GetMessage("LEARNING_EDIT_TITLE"));
elseif (($LESSON_ID == 0) && $oAccess->IsBaseAccess(CLearnAccess::OP_LESSON_CREATE))
	$APPLICATION->SetTitle(GetMessage('LEARNING_LESSONS').": ".GetMessage("LEARNING_NEW_TITLE"));
else
	$bBadCourse = true;

//Defaults
$str_ACTIVE      = "Y";
$str_DETAIL_TEXT_TYPE = $str_PREVIEW_TEXT_TYPE = "text";
$str_EDGE_SORT   = '500';
$str_PUBLISH_PROHIBITED = 'N';

// Are we in context of parent lesson?
$result = false;
if (isset($g_learn_parentLessonId))
{
	$str_PARENT_LESSON_ID = $g_learn_parentLessonId;

	if ($LESSON_ID > 0)
	{
		// Get lesson data as immediate child of parent lesson. 
		// It needs for getting edges data (SORT).
		$result = CLearnLesson::GetListOfImmediateChilds(
			$str_PARENT_LESSON_ID, 			// parent of current lesson
			array(), 						// sort order
			array('LESSON_ID' => $LESSON_ID));		// get data for current lesson only
	}
}
else
{
	if (isset($str_PARENT_LESSON_ID))
		unset ($str_PARENT_LESSON_ID);

	if ($LESSON_ID > 0)
		$result = CLearnLesson::GetByID($LESSON_ID);
}
// Now $str_PARENT_LESSON_ID exists only if in GET and/or POST request was PARENT_LESSON_ID

if (($topCourseLessonId !== false) && ($LESSON_ID > 0))
{
	if (CLearnLesson::IsPublishProhibited($LESSON_ID, $topCourseLessonId))
		$str_PUBLISH_PROHIBITED = 'Y';
}

if( ! ($result && $result->ExtractFields("str_")) )
	$LESSON_ID = 0;

if ($bVarsFromForm)
{
	$ACTIVE = ($ACTIVE != "Y"? "N":"Y");
	
	/**
	 * Resolving dependencies for new data structure
	 * was:
	 * $DB->InitTableVarsForEdit("b_learn_lesson", "", "str_");
	 */
	$arVarsOnForm = array (
		'TIMESTAMP_X', 'ACTIVE', 'CODE', 'NAME', 'KEYWORDS', 
		'PREVIEW_TEXT', 'PREVIEW_TEXT_TYPE',
		'DETAIL_TEXT', 'DETAIL_TEXT_TYPE',
		'LAUNCH');

	// Only in context of parent lesson
	if (isset($str_PARENT_LESSON_ID))
	{
		$arVarsOnForm[] = 'EDGE_SORT';
	}

	// Only in context of most top course
	if ($topCourseLessonId !== false)
		$arVarsOnForm[] = 'PUBLISH_PROHIBITED';

	foreach ($arVarsOnForm as $k => $varName)
	{
		if (!is_array(${$varName}))
			${'str_' . $varName} = htmlspecialcharsbx(${$varName});
		else
		{
			$tmp = array();
			foreach (${$varName} as $key => $value)
				$tmp[$key] = htmlspecialcharsbx($value);

			${'str_' . $varName} = $tmp;
			unset ($tmp);
		}
	}
}

$aTabs = array(
	array(
		"DIV" => "edit1",
		"ICON"=>"main_user_edit",
		"TAB" => GetMessage("LEARNING_EDIT_PARAM_SECTION"),
		"TITLE"=>GetMessage("LEARNING_EDIT_PARAM_SECTION")
	),

	array(
		"DIV" => "edit2",
		"ICON"=>"main_user_edit",
		"TAB" => GetMessage("LEARNING_ADMIN_TAB2"),
		"TITLE"=>GetMessage("LEARNING_ADMIN_TAB2_EX")
	),

	array(
		"DIV" => "edit3",
		"ICON"=>"main_user_edit",
		"TAB" => GetMessage("LEARNING_ADMIN_TAB3"),
		"TITLE"=>GetMessage("LEARNING_ADMIN_TAB3_EX")
	)
);

if (($LESSON_ID > 0) && CLearnAccessMacroses::CanUserViewLessonRelations (array('lesson_id' => $LESSON_ID)))
{
	$arOPathes = CLearnLesson::GetListOfParentPathes($LESSON_ID);

	$tabName = GetMessage("LEARNING_ADMIN_TAB4");

	$aTabs[] = array(
		"DIV" => "edit4",
		"ICON"=>"main_user_edit",
		"TAB" => $tabName,
		"TITLE"=>GetMessage("LEARNING_ADMIN_TAB4_EX")
	);
}

if (($LESSON_ID > 0) && CLearnAccessMacroses::CanUserViewLessonRights (array('lesson_id' => $LESSON_ID)))
{
	$aTabs[] = array(
		"DIV" => "edit5",
		"ICON"=>"main_user_edit",
		"TAB" => GetMessage("LEARNING_PERMISSIONS"),
		"TITLE"=>GetMessage("LEARNING_PERMISSIONS")
	);
}

$aTabs[] = $USER_FIELD_MANAGER->EditFormTab('LEARNING_LESSONS');

//$tabControl = new CAdminTabControl("lessonTabControl", $aTabs);
$tabControl = new CAdminForm("lessonTabControl", $aTabs);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($message)
	echo $message->Show();

if (!$bBadCourse):

// Back to lessons list available only if there is parentLessonPath exists
if (isset($g_learn_parentLessonPath))
{
	$aContext = array(
		array(
			"ICON"  => "btn_list",
			"TEXT"  => GetMessage("MAIN_ADMIN_MENU_LIST"),
			"LINK"  => "learn_unilesson_admin.php?lang=" . LANG . "&LESSON_PATH=" . urlencode($g_learn_parentLessonPath) . GetFilterParams("filter_"),
			"TITLE" => GetMessage("MAIN_ADMIN_MENU_LIST")
		),
	);
}
else
{
	// To all lessons list
	$aContext = array(
		array(
			'ICON'  => 'btn_list',
			'TEXT'  => GetMessage('LEARNING_ALL_LESSONS'),
			'LINK'  => 'learn_unilesson_admin.php?lang=' . LANG 
				. '&set_filter=Y'
				. '&PARENT_LESSON_ID=-2',	// magic number '-2' is means 'List lessons, without relation to parent'
			'TITLE' => GetMessage('LEARNING_ALL_LESSONS')
			)
		);
}

if ($LESSON_ID > 0)
{
	$aContext[] = array(
		"ICON"  => "btn_delete",
		"TEXT"  => GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"LINK"	=> "javascript:if(confirm('"
			. GetMessage("LEARNING_CONFIRM_DEL_MESSAGE")
			. "'))window.location='learn_unilesson_admin.php?lang=" . LANG 
			. "&action=delete&ID=" . $LESSON_ID 
			. ''
			. "&" . bitrix_sessid_get() 
			. urlencode(GetFilterParams("filter_", false)) . "';",
	);

	/*
	// If we are in context of parent lesson - we can unlink current lesson from parent
	if (isset($g_learn_parentLessonId))
	{
		$aContext[] = array(
			"ICON"  => "btn_delete",
			"TEXT"  => GetMessage("LEARNING_UNLINK_LESSON_FROM_PARENT"),
			"LINK"	=> "javascript:if(confirm('"
				. GetMessage("LEARNING_CONFIRM_UNLINK_LESSON_FROM_PARENT")
				. "'))window.location='learn_unilesson_admin.php?lang=" . LANG 
				. '&action=unlink'
				. '&ID=' . urlencode($g_learn_currentLessonPath)
				. "&" . bitrix_sessid_get() 
				. urlencode(GetFilterParams("filter_", false)) . "';",
		);
	}
	*/
}

$context = new CAdminContextMenu($aContext);
$context->Show();

if(COption::GetOptionString("learning", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman"))
{
	//TODO:This dirty hack will be replaced by special method like calendar do
	echo '<div style="display:none">';
	CFileMan::AddHTMLEditorFrame(
		"SOME_TEXT",
		"",
		"SOME_TEXT_TYPE",
		"text",
		array(
			'height' => 450,
			'width' => '100%'
		),
		"N",
		0,
		"",
		"",
		false
	);
	echo '</div>';
}
?>

<script type="text/javascript">
function toggleSource() {
	if (document.lessonTabControl_form.CONTENT_SOURCE[0].checked)
	{
		document.getElementById("source_field[0]").style.display = "";
		if (document.getElementById("source_field[1]"))
			document.getElementById("source_field[1]").style.display = "";
		document.getElementById("source_file").style.display = "none";
	}
	else
	{
		document.getElementById("source_field[0]").style.display = "none";
		if (document.getElementById("source_field[1]"))
			document.getElementById("source_field[1]").style.display = "none";
		document.getElementById("source_file").style.display = "";
	}
}
</script>
<?
CAdminFileDialog::ShowScript(Array
	(
		"event" => "OpenFileBrowserWindMedia",
		"arResultDest" => Array("FUNCTION_NAME" => "SetUrl"),
		"arPath" => Array("SITE" => $_GET["site"], "PATH" =>(strlen($str_FILENAME)>0 ? GetDirPath($str_FILENAME) : '')),
		"select" => 'F',// F - file only, D - folder only,
		"operation" => 'O',// O - open, S - save
		"showUploadTab" => true,
		"showAddToMenuTab" => false,
		"fileFilter" => 'wmv,flv,mp4,wma,mp3',//'' - don't shjow select, 'image' - only images; "ext1,ext2" - Only files with ext1 and ext2 extentions;
		"allowAllFiles" => true,
		"SaveConfig" => true
	)
);

?>
<?php
function CustomizeEditor()
{
	ob_start();
	?>
	<div class="bxed-dialog">
		<table class="bx-image-dialog-tbl">
			<tr>
				<td class="bx-par-title"><?echo GetMessage("LEARNING_PATH_TO_FILE")?>:</td>
				<td class="bx-par-val" colspan="3">
					<input type="text" size="30" id="mediaPath" />
					<input type="button" value="..." id="OpenFileBrowserWindMedia_button">
				</td>
			</tr>
			<tr>
				<td class="bx-par-title"><?echo GetMessage("LEARNING_WIDTH")?>:</td>
				<td width="80px"><input type="text" size="3" id="mediaWidth" /></td>
				<td><?echo GetMessage("LEARNING_HEIGHT")?>:</td>
				<td class="bx-par-val"><input type="text" size="3" id="mediaHeight" /></td>
			</tr>
		</table>
	</div>
<?php $dialogHTML = ob_get_clean()?>
<script type="text/javascript">
	var pEditor;
	var pElement;
	function SetUrl(filename, path, site)
	{
		if (path.substr(-1) == "/")
		{
			path = path.substr(0, path.length - 1);
		}
		var url = path+'/'+filename;
		BX("mediaPath").value = url;
		if(BX("mediaPath").onchange)
			BX("mediaPath").onchange();
	}
	function _mediaParser(_str, pMainObj)
	{
		// **** Parse WMV ****
		// b1, b3 - quotes
		// b2 - id of the div
		// b4 - javascript config
		var ReplaceWMV = function(str, b1, b2, b3, b4)
		{
			var
				id = b2,
				JSConfig, w, h, prPath;

			try {eval('JSConfig = ' + b4); } catch (e) { JSConfig = false; }
			if (!id || !JSConfig)
				return '';

			var w = (parseInt(JSConfig.width) || 50);
			var h = (parseInt(JSConfig.height) || 25);

			var arTagParams = {file: JSConfig.file};
			var bxTag =  pMainObj.GetBxTag(id);

			if (bxTag && bxTag && bxTag.tag == "media")
			{
				arTagParams.id = id;
			}
			return '<img  id="' + pMainObj.SetBxTag(false, {tag: 'media', params: arTagParams}) + '" src="/bitrix/images/1.gif" style="border: 1px solid rgb(182, 182, 184); background-color: rgb(226, 223, 218); background-image: url(/bitrix/images/learning/icons/media.gif); background-position: center center; background-repeat: no-repeat; width: '+w+'px; height: '+h+'px;" width="'+w+'" height="'+h+'" />';
		}
		_str = _str.replace(/<script.*?silverlight\.js.*?<\/script>\s*?<script.*?wmvplayer\.js.*?<\/script>\s*?<div.*?id\s*?=\s*?("|\')(.*?)\1.*?<\/div>\s*?<script.*?jeroenwijering\.Player\(document\.getElementById\(("|\')\2\3.*?wmvplayer\.xaml.*?({.*?})\).*?<\/script>/ig, ReplaceWMV);

		// **** Parse FLV ****
		var ReplaceFLV = function(str, attr)
		{
			attr = attr.replace(/[\r\n]+/ig, ' '); attr = attr.replace(/\s+/ig, ' '); attr = jsUtils.trim(attr);
			var
				arParams = {},
				arFlashvars = {},
				w, h, id, prPath;

			attr.replace(/([^\w]??)(\w+?)\s*=\s*("|\')([^\3]+?)\3/ig, function(s, b0, b1, b2, b3)
			{
				b1 = b1.toLowerCase();
				if (b1 == 'src' || b1 == 'type' || b1 == 'allowscriptaccess' || b1 == 'allowfullscreen' || b1 == 'pluginspage' || b1 == 'wmode')
					return '';
				arParams[b1] = b3; return b0;
			});
			id = arParams.id;

			if (!id || !arParams.flashvars)
				return str;

			arParams.flashvars.replace(/(\w+?)=((?:\s|\S)*?)&/ig, function(s, name, val) { arFlashvars[name] = val; return ''; });
			var w = (parseInt(arParams.width) || 50);
			var h = (parseInt(arParams.height) || 25);

			var arTagParams = {file: arFlashvars["file"]};
			var bxTag =  pMainObj.GetBxTag(id);

			if (bxTag && bxTag && bxTag.tag == "media")
			{
				arTagParams.id = id;
			}
			return '<img  id="' + pMainObj.SetBxTag(false, {tag: 'media', params: arTagParams}) + '" src="/bitrix/images/1.gif" style="border: 1px solid rgb(182, 182, 184); background-color: rgb(226, 223, 218); background-image: url(/bitrix/images/learning/icons/media.gif); background-position: center center; background-repeat: no-repeat; width: '+w+'px; height: '+h+'px;" width="'+w+'" height="'+h+'" />';
		}

		_str = _str.replace(/<object.*?>.*?<embed((?:\s|\S)*?player\/mediaplayer\/player\.swf(?:\s|\S)*?)(?:>\s*?<\/embed)?(?:\/?)?>.*?<\/object>/ig, ReplaceFLV);
		return _str;
	}
	arContentParsers.unshift(_mediaParser);

	function _mediaUnParser(_node, pMainObj)
	{
		bxTag = pMainObj.GetBxTag(_node.arAttributes["id"]);

		if (bxTag && bxTag.tag && bxTag.tag == "media")
		{
			var ext = bxTag.params.file.substr(bxTag.params.file.length - 3);
			var bWM = ext == "wmv" || ext == "wma";
			if (!bWM) // FL
			{
				var str = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" ';
				str += 'id="' + _node.arAttributes["id"] + '" ';
				str += 'width="' + _node.arAttributes["width"] + '" ';
				str += 'height="' + _node.arAttributes["height"] + '" ';
				str += '>';
				str += '<param name="movie" value="/bitrix/components/bitrix/player/mediaplayer/player">';

				var embed = '<embed src="/bitrix/components/bitrix/player/mediaplayer/player" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" pluginspage="http:/' + '/www.macromedia.com/go/getflashplayer" ';
				embed += 'id="' + _node.arAttributes["id"] + '" ';

				var arParams = {
					"menu": "true",
					"wmode": "transparent",
					"width": _node.arAttributes["width"],
					"height": _node.arAttributes["height"],
					"flashvars" : {
						"file" : bxTag.params.file,
						"logo.hide" : "true",
						"skin": "/bitrix/components/bitrix/player/mediaplayer/skins/bitrix.swf",
						"repeat" : "N",
						"bufferlength" : "10",
						"dock" : "true"
					}
				}

				for (i in arParams)
				{
					if (i == 'flashvars')
					{
						embed += 'flashvars="';
						str += '<param name="flashvars" value="';
						for (k in arParams[i])
						{
							embed += k + '=' + arParams[i][k] + '&';
							str += k + '=' + arParams[i][k] + '&';
						}
						embed = embed.substring(0, embed.length - 1) + '" ';
						str = str.substring(0, str.length - 1) + '">';
					}
					else
					{
						embed += i + '="' + arParams[i] + '" ';
						str += '<param name="' + i +'" value="' + arParams[i] +'">';
					}
				}
				embed += '/>';
				str += embed +'</object>';
			}
			else // WM
			{
				str = '<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/silverlight.js" /><\/script>' +
				'<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/wmvplayer.js"><\/script>' +
				'<div id="' + _node.arAttributes["id"] + '">WMV Player</div>' +
				'<script type="text/javascript">new jeroenwijering.Player(document.getElementById("' + _node.arAttributes["id"] + '"), "/bitrix/components/bitrix/player/wmvplayer/wmvplayer.xaml", {';

				var arParams = {
					"file" : bxTag.params.file,
					"bufferlength" : "10",
					"width": _node.arAttributes["width"],
					"height": _node.arAttributes["height"],
					"windowless": "true"
				}

				for (i in arParams)
					str += i + ': "' + arParams[i] + '", ';
				str = str.substring(0, str.length - 2);

				str += '});<\/script>';
			}
			return str;
		}

		return false;
	}
	oBXEditorUtils.addUnParser(_mediaUnParser);

	var pSaveButton = new BX.CWindowButton({
		'title': '<?echo GetMessage("LEARNING_SAVE")?>',
		'action': function() {
			var path = BX('mediaPath').value;
			var width = BX('mediaWidth').value;
			var height = BX('mediaHeight').value;

			this.parentWindow.Close();
			if (path.length > 0 && parseInt(width) > 0 && parseInt(height) > 0)
			{
				if (pElement && pElement.getAttribute && pElement.getAttribute("id"))
				{
					var bxTag =  pEditor.GetBxTag(pElement.getAttribute("id"))
					if (bxTag && bxTag.tag && bxTag.tag == "media")
					{
						bxTag.params.file = path;
						SAttr(pElement, "width", width);
						SAttr(pElement, "height", height);
						pElement.style.width = width + "px";
						pElement.style.height = height + "px";
					}
				}
				else
				{
					var arParams = {file: path};
					pEditor.insertHTML('<img id="' + pEditor.SetBxTag(false, {tag: 'media', params: arParams}) + '" src="/bitrix/images/1.gif" style="border: 1px solid rgb(182, 182, 184); background-color: rgb(226, 223, 218); background-image: url(/bitrix/images/learning/icons/media.gif); background-position: center center; background-repeat: no-repeat; width: '+width+'px; height: '+height+'px;" width="'+width+'" height="'+height+'" />');
				}
			}
			pElement = null;
		}
	});
	var pDialog = new BX.CDialog({
			title : '<?echo GetMessage("LEARNING_VIDEO_AUDIO")?>',
			content: '<?php echo CUtil::JSEscape(preg_replace("~>\s+<~", "><",  trim($dialogHTML)))?>',
			height: 180,
			width: 520,
			resizable: false,
			buttons: [pSaveButton, BX.CDialog.btnClose]
		});
	var pMediaButton = [
		'BXButton',
		{
			id : 'media',
			src : '/bitrix/images/learning/icons/media.gif',
			name : "<?echo GetMessage("LEARNING_VIDEO_AUDIO")?>",
			handler : function () {
				pDialog.Show();
				pEditor = this.pMainObj;
				BX("OpenFileBrowserWindMedia_button").onclick = OpenFileBrowserWindMedia;

				pElement = pEditor.GetSelectionObject();
				if (pElement && pElement.getAttribute && pElement.getAttribute("id"))
				{
					var bxTag =  pEditor.GetBxTag(pElement.getAttribute("id"))
					if (bxTag && bxTag.tag && bxTag.tag == "media")
					{
						BX('mediaPath').value = bxTag.params.file;
						BX('mediaWidth').value = pElement.getAttribute("width");
						BX('mediaHeight').value = pElement.getAttribute("height");
					}
				}
				else
				{
					BX('mediaPath').value = "";
					BX('mediaWidth').value = "400";
					BX('mediaHeight').value = "300";
				}
			}
		}
	];
	if (window.lightMode)
	{
		for(var i = 0, l = arGlobalToolbar.length; i < l ; i++)
		{
			var arButton = arGlobalToolbar[i];
			if (arButton[1] && arButton[1].id == "insert_flash" && arGlobalToolbar[i+1][1].id != "media") {
				arGlobalToolbar.splice(i + 1, 0, pMediaButton);
				break;
			}
		}
	}
	else
	{
		oBXEditorUtils.appendButton("insert_media", pMediaButton, "standart");
	}
</script>
<?php }?>
<?php AddEventHandler("fileman", "OnIncludeHTMLEditorScript", "CustomizeEditor"); ?>

<?php 

$tabControl->BeginEpilogContent();?>
	<?=bitrix_sessid_post()?>
	<?echo GetFilterHiddens("filter_");?>
	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="from" value="<?echo htmlspecialcharsbx($from)?>">
	<input type="hidden" name="return_url" value="<?echo htmlspecialcharsbx($return_url)?>">
	<input type="hidden" name="LESSON_ID" value="<?php echo $LESSON_ID; ?>">

	<?php
	// PARENT_LESSON_ID transmitted only when new lesson creating pended and 
	if (($LESSON_ID == 0) && isset($g_learn_parentLessonId))
	{
		?>
		<input type="hidden" name="PARENT_LESSON_ID" value="<?php echo ($g_learn_parentLessonId + 0); ?>">
		<?php
	}

	if (isset($g_learn_currentLessonPath))
	{
		?>
		<input type="hidden" name="LESSON_PATH" value="<?php echo htmlspecialcharsbx($g_learn_currentLessonPath); ?>">
		<?php
	}

$tabControl->EndEpilogContent();

if ($LESSON_ID > 0)
	$bContentReadOnly = ! CLearnAccessMacroses::CanUserEditLesson (array('lesson_id' => $LESSON_ID));
else
	$bContentReadOnly = ! $oAccess->IsBaseAccess(CLearnAccess::OP_LESSON_CREATE);

$tabControl->Begin();
$tabControl->BeginNextFormTab();
?>
<!-- ID -->
<?php $tabControl->BeginCustomField("LESSON_ID", "ID", false);?>
	<?if($LESSON_ID > 0):?>
		<tr>
			<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
			<td><?=$str_LESSON_ID?></td>
		</tr>
	<? endif; ?>
<?php $tabControl->EndCustomField("LESSON_ID");?>

<!-- Timestamp_X -->
<?php $tabControl->BeginCustomField("TIMESTAMP_X", GetMessage("LEARNING_LAST_UPDATE"), false);?>
	<?if($LESSON_ID > 0):?>
		<tr>
			<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
			<td><?=$str_TIMESTAMP_X?></td>
		</tr>
	<? endif; ?>
<?php $tabControl->EndCustomField("TIMESTAMP_X");?>

<!-- Created by -->
<?php $tabControl->BeginCustomField("CREATED_BY", GetMessage("LEARNING_AUTHOR"), false);?>
	<?if($LESSON_ID > 0):?>
		<tr>
			<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
			<td>
				<?php 
				echo '[<a href="user_edit.php?ID=' . ($str_CREATED_BY + 0) 
					. '&amp;lang=' . LANG . '">' 
					. $str_CREATED_BY . '</a>] ' 
					. $str_CREATED_USER_NAME;
				?>
			</td>
		</tr>
	<? endif; ?>
<?php $tabControl->EndCustomField("CREATED_BY");?>

<!-- Active -->
<?php $tabControl->BeginCustomField("ACTIVE", GetMessage("LEARNING_ACTIVE"), false);?>
	<tr>
		<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td><?php
			if ($bContentReadOnly)
			{
				if ($str_ACTIVE == 'Y')
					echo GetMessage('LEARNING_YES');
				else
					echo GetMessage('LEARNING_NO');
			}
			else
			{
				?><input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>><?php
			}
			?>
		</td>
	</tr>
<?php $tabControl->EndCustomField("ACTIVE", '<input type="hidden" id="ACTIVE" name="ACTIVE" value="' . $str_ACTIVE . '">');

$tabControl->BeginCustomField("NAME", GetMessage("LEARNING_NAME"), false);?>
	<tr class="adm-detail-required-field">
		<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td valign="top">
			<?php
			if ($bContentReadOnly)
				echo $str_NAME;
			else
			{
				?>
				<input type="text" name="NAME" size="50" maxlength="255" value="<?echo $str_NAME?>">
				<?php
			}
			?>
		</td>
	</tr>
	<?php 
$tabControl->EndCustomField("NAME", '<input type="hidden" id="NAME" name="NAME" value="' . $str_NAME . '">');

$tabControl->BeginCustomField('CODE', GetMessage('LEARNING_CODE'), false);?>
<!-- CODE -->
	<tr>
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<?php
			if ($bContentReadOnly)
				echo $str_CODE;
			else
			{
				?>
				<input type="text" name="CODE" size="20" maxlength="40" value="<?php echo $str_CODE; ?>">
				<?php
			}
			?>
		</td>
	</tr>
	<?php
$tabControl->EndCustomField('CODE', '<input type="hidden" id="CODE" name="CODE" value="' . $str_CODE . '">');

$tabControl->BeginCustomField("KEYWORDS", GetMessage("LEARNING_KEYWORDS"), false);?>
	<tr>
		<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td valign="top">
			<?php
			if ($bContentReadOnly)
				echo $str_KEYWORDS;
			else
			{
				?>
				<input type="text" name="KEYWORDS" size="50" maxlength="255" value="<?echo $str_KEYWORDS?>">
				<?php
			}
			?>
		</td>
	</tr>
	<?php 
$tabControl->EndCustomField("KEYWORDS", '<input type="hidden" id="KEYWORDS" name="KEYWORDS" value="' . $str_KEYWORDS . '">');


// If we are in context of parent lesson - show EDGE_SORT property
if (isset($g_learn_parentLessonId))
{
	$resParentLessonData = CLearnLesson::GetByID($g_learn_parentLessonId);
	$arParentLessonData  = $resParentLessonData->GetNext();
	if (is_array($arParentLessonData))
	{
		$tabControl->BeginCustomField("ZOMBIE_CONTEXT_PARENT_NAME", GetMessage("LEARNING_PARENT_CHAPTER_ID"), false);
		?>
			<tr>
				<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
				<td><?php echo $arParentLessonData['NAME']; ?></td>
			</tr>
		<?php
		$tabControl->EndCustomField("ZOMBIE_CONTEXT_PARENT_NAME");

		$tabControl->BeginCustomField('EDGE_SORT', GetMessage('LEARNING_SORT'), false);
		?>
		<!-- Sort -->
			<tr>
				<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
				<td>
					<?php
					if ($bContentReadOnly)
						echo $str_EDGE_SORT;
					else
					{
						?>
						<input type="text" name="EDGE_SORT" size="10" maxlength="10" value="<?php echo $str_EDGE_SORT; ?>">
						<?php
					}
					?>
				</td>
			</tr>
		<?php
		if ($bContentReadOnly)
			$tabControl->EndCustomField('EDGE_SORT');
		else
			$tabControl->EndCustomField('EDGE_SORT', '<input type="hidden" id="EDGE_SORT" name="EDGE_SORT" value="' . $str_EDGE_SORT . '">');
	}

	unset ($resParentLessonData, $arParentLessonData);
}

// PUBLISH_PROHIBITED - Only in context of most top course
if ($topCourseLessonId !== false)
{
	$resRootLessonData = CLearnLesson::GetByID($topCourseLessonId);
	$arRootLessonData  = $resRootLessonData->Fetch();
	if (is_array($arRootLessonData))
	{
		$tabControl->BeginCustomField(
			'PUBLISH_PROHIBITED', 
			GetMessage('LEARNING_COURSE_ADM_PUBLISH_PROHIBITED'), 
			false
		);
		?>
		<!-- PUBLISH_PROHIBITED -->
			<tr>
				<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
				<td>
					<label>
						<?php
						if ($bContentReadOnly)
							echo $str_PUBLISH_PROHIBITED;
						else
						{
							?>
							<input type="checkbox" name="PUBLISH_PROHIBITED" 
								value="Y" <?php if ($str_PUBLISH_PROHIBITED=="Y") echo "checked"; ?>>
							<?php
						}

						echo ' (' 
							. str_replace(
								'#COURSE_NAME#', 
								'&laquo;' . htmlspecialcharsbx($arRootLessonData['NAME']) . '&raquo;', 
								GetMessage('LEARNING_COURSE_ADM_PUBLISH_PROHIBITED_CONTEXT')
							)
							. ')';
						?>
					</label>
				</td>
			</tr>
		<?php
		if ($bContentReadOnly)
			$tabControl->EndCustomField('PUBLISH_PROHIBITED');
		else
			$tabControl->EndCustomField('PUBLISH_PROHIBITED', '<input type="hidden" id="PUBLISH_PROHIBITED" name="PUBLISH_PROHIBITED" value="' . $str_PUBLISH_PROHIBITED . '">');
	}

	unset ($resParentLessonData, $arRootLessonData);
}


$tabControl->BeginNextFormTab();
$tabControl->BeginCustomField("PREVIEW_TEXT", GetMessage("LEARNING_PREVIEW_TEXT"), false);?>
	<?if(COption::GetOptionString("learning", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td colspan="2" align="center">
			<?php
			if ($bContentReadOnly)
			{
				?>
				<div>
					<?php
					echo GetMessage("LEARNING_DESC_TYPE") . ': ';
					if ($str_PREVIEW_TEXT_TYPE != 'html')
						echo GetMessage("LEARNING_DESC_TYPE_TEXT");
					else
						echo GetMessage("LEARNING_DESC_TYPE_HTML");
					?>
				</div>

				<div id="learn_unilesson_edit_preview_text_div">
					<script type="text/javascript">
						var iframe = document.createElement('iframe');
						iframe.style.width = '100%';
						iframe.style.height = '200px';
						document.getElementById('learn_unilesson_edit_preview_text_div').appendChild(iframe);
						var idoc = iframe.contentWindow.document;
						idoc.write('<?php echo CUtil::JSEscape(htmlspecialcharsback($str_PREVIEW_TEXT)); ?>');
					</script>
				</div>
				<?php
			}
			else
			{
				CFileMan::AddHTMLEditorFrame(
					"PREVIEW_TEXT",
					$str_PREVIEW_TEXT,
					"PREVIEW_TEXT_TYPE",
					$str_PREVIEW_TEXT_TYPE,
					array(
						'width' => '100%',
						'height' => '600'
						),
					"N",
					0,
					"",
					"",
					false,
					true,
					false,
					array('toolbarConfig' => CFileman::GetEditorToolbarConfig("learning_".(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1 ? 'public' : 'admin')))
				);
			}
			?>
		</td>
	</tr>
	<?else:?>
	<tr>
		<td align="center"><?echo GetMessage("LEARNING_DESC_TYPE")?>:</td>
		<td>
			<input type="radio" <?php if ($bContentReadOnly) echo ' disabled="disabled" '; ?> name="PREVIEW_TEXT_TYPE" value="text"<?if($str_PREVIEW_TEXT_TYPE!="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_TEXT")?> / 
			<input type="radio" <?php if ($bContentReadOnly) echo ' disabled="disabled" '; ?> name="PREVIEW_TEXT_TYPE" value="html"<?if($str_PREVIEW_TEXT_TYPE=="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_HTML")?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php
			if ($bContentReadOnly)
			{
				?>
				<div id="learn_unilesson_edit_preview_text_div">
					<script type="text/javascript">
						var iframe = document.createElement('iframe');
						iframe.style.width = '100%';
						iframe.style.height = '200px';
						document.getElementById('learn_unilesson_edit_preview_text_div').appendChild(iframe);
						var idoc = iframe.contentWindow.document;
						idoc.write('<?php echo CUtil::JSEscape(htmlspecialcharsback($str_PREVIEW_TEXT)); ?>');
					</script>
				</div>
				<?php
			}
			else
			{
				?>
				<textarea style="width:100%; height:200px;" name="PREVIEW_TEXT" wrap="virtual"><?echo $str_PREVIEW_TEXT?></textarea>
				<?php
			}
			?>
		</td>
	</tr>
	<?endif?>
<?php $tabControl->EndCustomField("PREVIEW_TEXT",
	'<input type="hidden" id="PREVIEW_TEXT" name="PREVIEW_TEXT" value="' . $str_PREVIEW_TEXT . '">'.
	'<input type="hidden" id="PREVIEW_TEXT_TYPE" name="PREVIEW_TEXT_TYPE" value="' . $str_PREVIEW_TEXT_TYPE . '">'
);?>
<?php $tabControl->BeginCustomField("PREVIEW_PICTURE", GetMessage("LEARNING_PICTURE"), false);?>
	<tr>
		<?php
		if ( $bContentReadOnly && ! $str_PREVIEW_PICTURE)
		{
			?>
			<td colspan="2" align="center">
				<?php
				echo $tabControl->GetCustomLabelHTML() . ': ' . GetMessage('LEARNING_NO');
				?>
			</td>
			<?php
		}
		else
		{
			?>
			<td valign="top" style="width:50%;"><?echo $tabControl->GetCustomLabelHTML()?></td>
			<td>
				<?php
				if ( ! $bContentReadOnly )
					echo CFile::InputFile("PREVIEW_PICTURE", 20, $str_PREVIEW_PICTURE, false, 0, "IMAGE", "", 40) . '<br>';

				if ($str_PREVIEW_PICTURE)
				{
					echo CFile::ShowImage($str_PREVIEW_PICTURE, 200, 200, "border=0", "", true);
				}
				?>
			</td>
			<?php
		}
		?>
	</tr>
<?php $tabControl->EndCustomField("PREVIEW_PICTURE");?>

<?$tabControl->BeginNextFormTab();?>
<?php $tabControl->BeginCustomField("DESCRIPTION", GetMessage("LEARNING_DESCRIPTION"), false);?>
	<tr>
		<td valign="top" width="50%" align="right"><?echo GetMessage("LEARNING_CONTENT_SOURCE")?>:</td>
		<td valign="top" width="50%">
			<label><input onClick="toggleSource()" <?php if ($bContentReadOnly) echo ' disabled="disabled" '; ?> type="radio" name="CONTENT_SOURCE" value="field"<?php echo $str_DETAIL_TEXT_TYPE!="file" ? " checked" : ""?>>&nbsp;<?echo GetMessage("LEARNING_CONTENT_SOURCE_FIELD")?></label><br />
			<label><input onClick="toggleSource()" <?php if ($bContentReadOnly) echo ' disabled="disabled" '; ?> type="radio" name="CONTENT_SOURCE" value="file"<?php echo $str_DETAIL_TEXT_TYPE=="file" ? " checked" : ""?>>&nbsp;<?echo GetMessage("LEARNING_CONTENT_SOURCE_FILE")?></label>
		</td>
	</tr>
	<?if(COption::GetOptionString("learning", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):
	?>
	<tr id="source_field[0]">
		<td colspan="2" align="center">
			<?php
			if ($bContentReadOnly)
			{
				?>
				<div>
					<?php
					echo GetMessage("LEARNING_DESC_TYPE") . ': ';
					if ($str_DETAIL_TEXT_TYPE != 'html')
						echo GetMessage("LEARNING_DESC_TYPE_TEXT");
					else
						echo GetMessage("LEARNING_DESC_TYPE_HTML");
					?>
				</div>

				<div id="learn_unilesson_edit_detail_text_div">
					<script type="text/javascript">
						var iframe = document.createElement('iframe');
						iframe.style.width = '100%';
						iframe.style.height = '300px';
						document.getElementById('learn_unilesson_edit_detail_text_div').appendChild(iframe);
						var idoc = iframe.contentWindow.document;
						idoc.write('<?php echo CUtil::JSEscape(htmlspecialcharsback($str_DETAIL_TEXT)); ?>');
					</script>
				</div>
				<?php
			}
			else
			{
				CFileMan::AddHTMLEditorFrame(
					"DETAIL_TEXT",
					$str_DETAIL_TEXT,
					"DETAIL_TEXT_TYPE",
					$str_DETAIL_TEXT_TYPE,
					array(
						'width' => '100%',
						'height' => '600'
						),
					"N",
					0,
					"",
					"",
					false,
					true,
					false,
					array('toolbarConfig' => CFileman::GetEditorToolbarConfig("learning_".(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1 ? 'public' : 'admin')))
				);
			}
			?>
		</td>
	</tr>
	<?else:?>
	<tr id="source_field[0]">
		<td valign="top"><?echo GetMessage("LEARNING_DESC_TYPE")?></td>
		<td valign="top">
			<input type="radio" <?php if ($bContentReadOnly) echo ' disabled="disabled" '; ?> name="DETAIL_TEXT_TYPE" value="text"<?if($str_DETAIL_TEXT_TYPE!="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_TEXT")?> / 
			<input type="radio" <?php if ($bContentReadOnly) echo ' disabled="disabled" '; ?> name="DETAIL_TEXT_TYPE" value="html"<?if($str_DETAIL_TEXT_TYPE=="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_HTML")?>
		</td>
	</tr>
	<tr id="source_field[1]">
		<td valign="top" align="center" colspan="2">
			<?php
			if ($bContentReadOnly)
			{
				?>
				<div id="learn_unilesson_edit_detail_text_div">
					<script type="text/javascript">
						var iframe = document.createElement('iframe');
						iframe.style.width = '100%';
						iframe.style.height = '200px';
						document.getElementById('learn_unilesson_edit_detail_text_div').appendChild(iframe);
						var idoc = iframe.contentWindow.document;
						idoc.write('<?php echo CUtil::JSEscape(htmlspecialcharsback($str_DETAIL_TEXT)); ?>');
					</script>
				</div>
				<?php
			}
			else
			{
				?>
				<textarea style="width:100%; height:200px;" name="DETAIL_TEXT" wrap="virtual"><?echo $str_DETAIL_TEXT?></textarea>
				<?php
			}
			?>
		</td>
	</tr>
	<?endif;?>
	<tr id="source_file">
		<td valign="top" align="right"><?echo GetMessage("LEARNING_PATH_TO_FILE")?>:</td>
		<td valign="top">
			<?php
			if ($bContentReadOnly)
				echo $str_LAUNCH;
			else
			{
				?>
				<input type="text" name="LAUNCH" size="50" maxlength="255" value="<?echo $str_LAUNCH?>">
				<?php
			}
			?>
		</td>
	</tr>
	<script type="text/javascript">toggleSource()</script>
<?php $tabControl->EndCustomField("DESCRIPTION", 
	'<input type="hidden" id="DESCRIPTION" name="DESCRIPTION" value="' . $str_DESCRIPTION . '">'
	. '<input type="hidden" id="DETAIL_TEXT_TYPE" name="DETAIL_TEXT_TYPE" value="' . $str_DETAIL_TEXT_TYPE . '">'
	. '<input type="hidden" id="LAUNCH" name="LAUNCH" value="' . $str_LAUNCH . '">'
	);?>
<?php $tabControl->BeginCustomField("DETAIL_PICTURE", GetMessage("LEARNING_PICTURE"), false);?>
	<tr>
		<?php
		if ( $bContentReadOnly && ! $str_DETAIL_PICTURE)
		{
			?>
			<td colspan="2" align="center">
				<?php
				echo $tabControl->GetCustomLabelHTML() . ': ' . GetMessage('LEARNING_NO');
				?>
			</td>
			<?php
		}
		else
		{
			?>
			<td valign="top" style="width:50%;"><?echo $tabControl->GetCustomLabelHTML()?></td>
			<td>
				<?php
				if ( ! $bContentReadOnly )
					echo CFile::InputFile("DETAIL_PICTURE", 20, $str_DETAIL_PICTURE, false, 0, "IMAGE", "", 40) . '<br>';

				if ($str_DETAIL_PICTURE)
				{
					echo CFile::ShowImage($str_DETAIL_PICTURE, 200, 200, "border=0", "", true);
				}
				?>
			</td>
			<?php
		}
		?>
	</tr>
<?php $tabControl->EndCustomField("DETAIL_PICTURE");


// Tab: Relations
if (($LESSON_ID > 0) && CLearnAccessMacroses::CanUserViewLessonRelations (array('lesson_id' => $LESSON_ID)))
{
	$tabControl->BeginNextFormTab();
	$tabControl->BeginCustomField("_RELATIONS", '', false);
	echo '<tr><td>';
	CLearnRelationHelper::RenderForm($oAccess, $LESSON_ID, $arOPathes);
	echo '</td></tr>';
	$tabControl->EndCustomField("_RELATIONS", '<input type="hidden" id="SKIP_RELATIONS_SAVING" name="SKIP_RELATIONS_SAVING" value="Y">');
}
else
{
	/*
	$tabControl->BeginNextFormTab();
	$tabControl->BeginCustomField("_RELATIONS", '', false);

	if ($LESSON_ID > 0)
	{
		echo '<tr><td>'
			. GetMessage('LEARNING_ACCESS_D_FOR_EDIT_CONTENT')
			. '</td></tr>';
	}
	else
	{
		echo '<tr><td>'
			. GetMessage('LEARNING_EDIT_FORM_WILL_BE_AVAILABLE_AFTER_LESSON_CREATION')
			. '</td></tr>';
	}

	$tabControl->EndCustomField("_RELATIONS", '<input type="hidden" id="SKIP_RELATIONS_SAVING" name="SKIP_RELATIONS_SAVING" value="Y">');
	*/
}


if (($LESSON_ID > 0) && CLearnAccessMacroses::CanUserViewLessonRights (array('lesson_id' => $LESSON_ID)))
{
	$readOnly = true;
	if (CLearnAccessMacroses::CanUserEditLessonRights (array('lesson_id' => $LESSON_ID)))
		$readOnly = false;

	$tabControl->BeginNextFormTab();
	$tabControl->BeginCustomField("__GESGSTR", '', false);
	CLearnRenderRightsEdit::RenderLessonRightsTab ($USER->GetID(), 'LESSON_RIGHTS', $LESSON_ID, $readOnly);
	if ($readOnly)
		echo '<input type="hidden" id="SKIP_RIGHTS_SAVING" name="SKIP_RIGHTS_SAVING" value="Y">';
	$tabControl->EndCustomField("__GESGSTR");
}
else
{
	/*
	$tabControl->BeginNextFormTab();
	$tabControl->BeginCustomField("__GESGSTR", '', false);

	if ($LESSON_ID > 0)
	{
		echo '<tr><td>'
			. GetMessage('LEARNING_ACCESS_D_FOR_MANAGE_RIGHTS')
			. '</td></tr>';
	}
	else
	{
		echo '<tr><td>'
			. GetMessage('LEARNING_EDIT_FORM_WILL_BE_AVAILABLE_AFTER_LESSON_CREATION')
			. '</td></tr>';
	}

	$tabControl->EndCustomField("__GESGSTR", '<input type="hidden" id="SKIP_RIGHTS_SAVING" name="SKIP_RIGHTS_SAVING" value="Y">');
	*/
}

$uriParentLessonPath = "";
if ($LESSON_ID === 0 && isset($g_learn_currentLessonPath))
{
	$uriParentLessonPath = '&LESSON_PATH=' . urlencode($g_learn_currentLessonPath);
}
elseif (isset($g_learn_parentLessonPath))
{
	$uriParentLessonPath = '&LESSON_PATH=' . urlencode($g_learn_parentLessonPath);
}

if (isset($g_learn_currentLessonPath))
	$uriCurrentLessonPath = '&LESSON_PATH=' . urlencode($g_learn_currentLessonPath);
else
	$uriCurrentLessonPath = '';

$tabControl->BeginNextFormTab();
$tabControl->BeginCustomField("UFS", '', false);
$USER_FIELD_MANAGER->EditFormShowTab('LEARNING_LESSONS', $bVarsFromForm, $LESSON_ID); 
$tabControl->EndCustomField("UFS");

$tabControl->Buttons(
	array(
		'disabled' => $isBtnsDisabled,
		"back_url" => "/bitrix/admin/learn_unilesson_admin.php?lang=". LANG
			. $uriParentLessonPath
			. GetFilterParams("filter_", false)
		)
	);

$tabControl->arParams["FORM_ACTION"] = $APPLICATION->GetCurPage() . "?lang=" . LANG 
	. $uriCurrentLessonPath
	. GetFilterParams("filter_");

$tabControl->Show();
//$tabControl->End();
$tabControl->ShowWarnings($tabControl->GetName(), $message);

else: //if (!$bBadCourse)

$aContext = array(
	array(
		"ICON" => "btn_list",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_LIST"),
		"LINK"=>"learn_unilesson_admin.php?lang=" . LANG . '&PARENT_LESSON_ID=-1' . GetFilterParams("filter_"),
		"TITLE"=>GetMessage("LEARNING_BACK_TO_ADMIN")
	),
);

$context = new CAdminContextMenu($aContext);
$context->Show();


CAdminMessage::ShowMessage(GetMessage("LEARNING_BAD_COURSE"));
endif;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
