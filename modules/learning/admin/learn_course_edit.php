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

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/learning/options.php");
IncludeModuleLangFile(__FILE__);

$APPLICATION->AddHeadScript('/bitrix/js/learning/rights_edit.js');

$oAccess = CLearnAccess::GetInstance($USER->GetID());

ClearVars();

$strWarning="";
$message = null;
$linkedLessonId = null;

$bVarsFromForm = false;
$COURSE_ID = (is_set($_REQUEST["COURSE_ID"]) ? intval($COURSE_ID) : 0);

$bDenyAutosave = false;
if ($COURSE_ID !== 0)
{
	$linkedLessonId = CCourse::CourseGetLinkedLesson ($COURSE_ID);
	if ($oAccess->IsLessonAccessible ($linkedLessonId, CLearnAccess::OP_LESSON_READ | CLearnAccess::OP_LESSON_WRITE))
	{
		$Perm = 'X';
	}
	elseif ($oAccess->IsLessonAccessible ($linkedLessonId, CLearnAccess::OP_LESSON_READ))
	{
		$bDenyAutosave = true;
		$Perm = 'G';
	}
	else
	{
		$bDenyAutosave = true;
		$Perm = 'D';
	}
}
else
{
	if ($oAccess->IsBaseAccess(CLearnAccess::OP_LESSON_CREATE))
		$Perm = 'X';
	else
	{
		$APPLICATION->SetTitle(GetMessage('LEARNING_ACCESS_D'));
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

		CAdminMessage::ShowMessage(GetMessage("LEARNING_ACCESS_D"));

		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
}


$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("LEARNING_ADMIN_TAB1"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("LEARNING_ADMIN_TAB1_EX")),

	array("DIV" => "edit2", "TAB" => GetMessage("LEARNING_ADMIN_TAB3"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("LEARNING_ADMIN_TAB3_EX")),

	array("DIV" => "edit3", "TAB" => GetMessage("LEARNING_ADMIN_TAB4"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("LEARNING_ADMIN_TAB4_EX")),
);

if ( ($COURSE_ID > 0) && ($linkedLessonId = CCourse::CourseGetLinkedLesson ($COURSE_ID)) )
{
	$arOPathes = CLearnLesson::GetListOfParentPathes($linkedLessonId);

	$arOPathes_cnt = count($arOPathes);

	$tabName = GetMessage("LEARNING_ADMIN_TAB5");
	if ($arOPathes_cnt > 1)
		$tabName .= ' (' . $arOPathes_cnt . ')';

	$aTabs[] = array(
		"DIV" => "edit4",
		"ICON"=>"main_user_edit",
		"TAB" => $tabName,
		"TITLE"=>GetMessage("LEARNING_ADMIN_TAB5_EX")
	);
}


$aTabs[] = array("DIV" => "edit5", "ICON" => "main_user_edit", "TAB" => GetMessage("LEARNING_PERMISSIONS"), "TITLE" => GetMessage("LEARNING_PERMISSIONS"));
$aTabs[] = $USER_FIELD_MANAGER->EditFormTab('LEARNING_LESSONS');

$tabControl = new CAdminForm("courseTabControl", $aTabs, true, $bDenyAutosave);


if ( ($_SERVER["REQUEST_METHOD"] == "POST") && ($Perm >= "X") && ($_POST["Update"] <> '') && check_bitrix_sessid() )
{
	$course = new CCourse;

	$arPREVIEW_PICTURE = $_FILES["PREVIEW_PICTURE"];
	$arPREVIEW_PICTURE["del"] = $PREVIEW_PICTURE_del;
	$arPREVIEW_PICTURE["MODULE_ID"] = "learning";
	$arPREVIEW_PICTURE["description"] = $PREVIEW_PICTURE_descr;

	$arFields = Array(
		"ACTIVE" => $ACTIVE,
		"NAME" => $NAME,
		"CODE" => $CODE,
		"SITE_ID" => $SITE_ID, //Sites
		"GROUP_ID" => $GROUP, //Permission
		"SORT" => $SORT,
		"DETAIL_TEXT" => $DETAIL_TEXT,
		"DETAIL_TEXT_TYPE" => $DETAIL_TEXT_TYPE,

		"PREVIEW_PICTURE" => $arPREVIEW_PICTURE,
		"PREVIEW_TEXT" => $PREVIEW_TEXT,
		"PREVIEW_TEXT_TYPE" => $PREVIEW_TEXT_TYPE,

		"ACTIVE_FROM" => $ACTIVE_FROM,
		"ACTIVE_TO" => $ACTIVE_TO,

		"RATING" => $RATING,
		"RATING_TYPE" => $RATING_TYPE,
	);

	$USER_FIELD_MANAGER->EditFormAddFields('LEARNING_LESSONS', $arFields);

	$res = false;
	if($COURSE_ID>0)
	{
		$linkedLessonId = CCourse::CourseGetLinkedLesson ($COURSE_ID);
		if ($linkedLessonId !== false)
		{
			if ($oAccess->IsLessonAccessible ($linkedLessonId, CLearnAccess::OP_LESSON_WRITE))
			{
				$res = $course->Update($COURSE_ID, $arFields);
			}

			// Process relations, data submitted from CLearnRelationHelper::RenderForm()
			CLearnRelationHelper::ProccessPOST($oAccess, $linkedLessonId);
		}
	}
	else
	{
		if ($oAccess->IsBaseAccess(CLearnAccess::OP_LESSON_CREATE))
		{
			$COURSE_ID = $course->Add($arFields);
			$res = ($COURSE_ID>0);
		}
	}

	try
	{
		// Work with permissions
		if (isset($_POST['LESSON_RIGHTS_marker'])
			&& ($COURSE_ID > 0)
		)
		{
			$linkedLessonId = CCourse::CourseGetLinkedLesson($COURSE_ID);
			if ( ($linkedLessonId !== false)
				&& $oAccess->IsLessonAccessible ($linkedLessonId, CLearnAccess::OP_LESSON_MANAGE_RIGHTS)
			)
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

				if (
					is_array($arAccessSymbols)
					&& is_array($arTaskIds)
					&& (($cntAccessSymbols = count($arAccessSymbols)) > 0)
					&& ($cntAccessSymbols === count($arTaskIds))
				)
				{
					$arPermPairs = array_combine($arAccessSymbols, $arTaskIds);
				}

				// Save permissions
				$oAccess->SetLessonsPermissions (array($linkedLessonId => $arPermPairs));
			}
		}
	}
	catch (Exception $e)
	{
		$res = false;
	}

	if(!$res)
	{
		//$strWarning .= $course->LAST_ERROR."<br>";
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("LEARNING_ERROR"), $e);
		$bVarsFromForm = true;
	}
	else
	{
		if (isset($_GET['PROPOSE_RETURN_LESSON_PATH']))
			$returnPath = '&LESSON_PATH=' . htmlspecialcharsbx($_GET['PROPOSE_RETURN_LESSON_PATH']);
		else
			$returnPath = '&PARENT_LESSON_ID=-1';

		if(!$bVarsFromForm)
		{
			if($apply == '')
			{
				if($return_url <> '')
				{
					if(mb_strpos($return_url, "#COURSE_ID#") !== false)
					{
						$return_url = str_replace("#COURSE_ID#", $COURSE_ID, $return_url);
					}
					LocalRedirect($return_url);
				}
				else
				{
					LocalRedirect("/bitrix/admin/learn_unilesson_admin.php?lang=" . LANG
						. $returnPath
						. GetFilterParams("filter_", false));
				}
			}

			LocalRedirect("/bitrix/admin/learn_course_edit.php?lang=".LANG
				. $returnPath
				. "&COURSE_ID=".$COURSE_ID
				."&".$tabControl->ActiveTabParam()
				.GetFilterParams("filter_", false));
		}
	}

	unset ($course);
}

if($COURSE_ID>0)
	$APPLICATION->SetTitle(str_replace("#ID#", $COURSE_ID, GetMessage("LEARNING_EDIT_TITLE2")));
else
	$APPLICATION->SetTitle(GetMessage("LEARNING_EDIT_TITLE1"));

//Defaults
$str_ACTIVE="Y";
$str_SORT="500";
$str_DETAIL_TEXT_TYPE = $str_PREVIEW_TEXT_TYPE = "text";

$res = false;
if ($COURSE_ID > 0)
{
	$course = new CCourse;
	$linkedLessonId = CCourse::CourseGetLinkedLesson ($COURSE_ID);
	if ($oAccess->IsLessonAccessible ($linkedLessonId, CLearnAccess::OP_LESSON_READ))
	{
		$res = $course->GetByID($COURSE_ID);
	}
	else
	{
		$APPLICATION->SetTitle(GetMessage('LEARNING_ACCESS_D'));
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
}

if ( ($res === false) || ( ! $res->ExtractFields("str_") ) )
{
	$COURSE_ID = 0;
}
else
{
	$str_SITE_ID = Array();
	$db_SITE_ID = CCourse::GetSite($COURSE_ID);
	while($ar_SITE_ID = $db_SITE_ID->Fetch())
		$str_SITE_ID[] = $ar_SITE_ID["LID"];
}

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

if($bVarsFromForm)
{
	$str_SITE_ID = $SITE_ID;
	$ACTIVE = ($ACTIVE != "Y"? "N":"Y");
	/**
	 * Resolving dependencies for new data structure
	 * was:
	 * $DB->InitTableVarsForEdit("b_learn_course", "", "str_");
	 */
	$arVarsOnForm = array ('COURSE_ID', 'TIMESTAMP_X', 'ACTIVE_FROM', 'ACTIVE_TO',
		'ACTIVE', 'CODE', 'NAME', 'SORT', 'RATING', 'RATING_TYPE',
		'PREVIEW_TEXT', 'PREVIEW_TEXT_TYPE', 'PREVIEW_PICTURE',
		'DETAIL_TEXT', 'DETAIL_TEXT_TYPE', 'DETAIL_PICTURE',
		'DESCRIPTION', 'SCORM', 'LAUNCH');

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

if (isset($_GET['PROPOSE_RETURN_LESSON_PATH']))
{
	$returnPath = '&LESSON_PATH=' . htmlspecialcharsbx($_GET['PROPOSE_RETURN_LESSON_PATH']);
	$returnPathUrlencoded = '&LESSON_PATH=' . urlencode(urlencode($_GET['PROPOSE_RETURN_LESSON_PATH']));
}
else
{
	$returnPath = '';
	$returnPathUrlencoded = '';
}

$aContext = array(
	array(
		"ICON" => "btn_list",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_LIST"),
		"LINK"=>"learn_unilesson_admin.php?lang=" . LANG."&PARENT_LESSON_ID=-1"
			. $returnPath
			. GetFilterParams("filter_"),
		"TITLE"=>GetMessage("LEARNING_BACK_TO_ADMIN")
	),
);

if ($Perm >= "X" && $COURSE_ID > 0)
{
	$aContext[] = 	array(
		"ICON" => "btn_new",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_CREATE"),
		"LINK"=>"learn_course_edit.php?lang=".LANG.GetFilterParams("filter_"),
		"TITLE"=>GetMessage("LEARNING_ADD")
	);


	$linkedLessonId = CCourse::CourseGetLinkedLesson($COURSE_ID);
	if ($linkedLessonId !== false)
	{
		$aContext[] = array(
			"ICON" => "btn_delete",
			"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
			"LINK"	=> "javascript:if(confirm('".GetMessage("LEARNING_CONFIRM_DEL_MESSAGE")
				."'))window.location='learn_unilesson_admin.php?action=delete&ID=". $linkedLessonId
				."&lang=".LANG
				. $returnPathUrlencoded
				."&".bitrix_sessid_get().urlencode(GetFilterParams("filter_", false))."';",
		);
	}

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

if($message)
	echo $message->Show();

if($Perm>="G"):?>

<?php $tabControl->BeginEpilogContent();?>
	<?=bitrix_sessid_post()?>
	<?echo GetFilterHiddens("filter_");?>
	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="COURSE_ID" value="<?echo $COURSE_ID?>">
	<?php if($return_url <> ''):?><input type="hidden" name="return_url" value="<?=htmlspecialcharsbx($return_url)?>"><?endif?>
<?php
$tabControl->EndEpilogContent();
$tabControl->Begin();
$tabControl->BeginNextFormTab();
?>
<!-- COURSE_ID -->
<?php
$tabControl->BeginCustomField('ID', 'ID', false);
if($linkedLessonId>0)
{
	?>
	<tr>
		<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td><?=$linkedLessonId?></td>
	</tr>
	<?php
}
$tabControl->EndCustomField('ID');
?>
<!-- COURSE_ID -->
<?php $tabControl->BeginCustomField("COURSE_ID", "COURSE_ID", false);?>
	<?if($COURSE_ID>0):?>
		<tr>
			<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
			<td><?=$COURSE_ID?></td>
		</tr>
	<? endif; ?>
<?php $tabControl->EndCustomField("COURSE_ID");?>
<?php $tabControl->BeginCustomField("CREATED_USER_NAME", GetMessage("LEARNING_AUTHOR"), false);?>
<!-- CREATED_USER_NAME -->
	<tr>
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<?php echo $str_CREATED_USER_NAME; ?>
		</td>
	</tr>
<?php $tabControl->EndCustomField("CREATED_USER_NAME");?>
<!-- Timestamp_X -->
<?php $tabControl->BeginCustomField("TIMESTAMP_X", GetMessage("LEARNING_LAST_UPDATE"), false);?>
	<?if($COURSE_ID>0):?>
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
<?php $tabControl->BeginCustomField("ACTIVE_PERIOD", GetMessage("LEARNING_ACTIVE_PERIOD"), false);?>
<!-- Active period-->
	<tr>
		<td><?echo $tabControl->GetCustomLabelHTML()?>):</td>
		<td>
			<?echo CalendarPeriod("ACTIVE_FROM", $str_ACTIVE_FROM, "ACTIVE_TO", $str_ACTIVE_TO, "courseTabControl", "N", "", "", "19")?>
		</td>
	</tr>
<?php $tabControl->EndCustomField("ACTIVE_PERIOD");?>
<?php $tabControl->BeginCustomField("CODE", GetMessage("LEARNING_CODE"), false);?>
<!-- CODE -->
	<tr>
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<input type="text" name="CODE" size="20" maxlength="40" value="<?=$str_CODE?>">
		</td>
	</tr>
<?php $tabControl->EndCustomField("CODE");?>
<?php $tabControl->BeginCustomField("SITE_ID", GetMessage("LEARNING_SITE_ID"), false);?>
<!-- Site -->
	<tr class="adm-detail-required-field">
		<td valign="top"><?echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td><?=CLang::SelectBoxMulti("SITE_ID", $str_SITE_ID);?></td>
	</tr>
<?php $tabControl->EndCustomField("SITE_ID");?>
<?php $tabControl->BeginCustomField("NAME", GetMessage("LEARNING_NAME"), false);?>
<!-- Name -->
	<tr class="adm-detail-required-field">
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td>
			<input type="text" name="NAME" size="40" maxlength="255" value="<?echo $str_NAME?>">
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
<?php $tabControl->BeginCustomField("RATING", GetMessage("LEARNING_RATING"), false);

	$arRating['reference_id'] = Array("", "Y", "N");
	$arRating['reference'] = Array(GetMessage("LEARNING_RATING_CONFIG"), GetMessage("MAIN_YES"), GetMessage("MAIN_NO"));
?>
	<tr>
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td><?=SelectBoxFromArray("RATING", $arRating, $str_RATING, "", "");?></td>
	</tr>
<?php $tabControl->EndCustomField("RATING");?>
<?php $tabControl->BeginCustomField("RATING_TYPE", GetMessage("LEARNING_RATING_TYPE"), false);
	$arRatingType['reference_id'] = Array("", "like", "like_graphic", "standart_text", "standart");
	$arRatingType['reference'] = Array(GetMessage("LEARNING_RATING_TYPE_CONFIG"), GetMessage("LEARNING_RATING_TYPE_LIKE"), GetMessage("LEARNING_RATING_TYPE_LIKE_GRAPHIC"), GetMessage("LEARNING_RATING_TYPE_STANDART_TEXT"), GetMessage("LEARNING_RATING_TYPE_STANDART"));
?>
	<tr>
		<td><? echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td><?=SelectBoxFromArray("RATING_TYPE", $arRatingType, $str_RATING_TYPE, "", "");?></td>
	</tr>
<?php $tabControl->EndCustomField("RATING_TYPE");?>
<?$tabControl->BeginNextFormTab();?>
<!-- 	<tr class="heading">
		<td colspan="2"><?echo GetMessage("LEARNING_ELEMENT_PREVIEW")?></td>
	</tr> -->

<?php $tabControl->BeginCustomField("PREVIEW_TEXT", GetMessage("LEARNING_PREVIEW_TEXT"), false);?>
	<?if(COption::GetOptionString("learning", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame(
				"PREVIEW_TEXT",
				$str_PREVIEW_TEXT,
				"PREVIEW_TEXT_TYPE",
				$str_PREVIEW_TEXT_TYPE,
				array('width' => '100%', 'height' => '450'),
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
				<input type="radio" name="PREVIEW_TEXT_TYPE" value="text"<?if($str_PREVIEW_TEXT_TYPE!="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_TEXT")?> / <input type="radio" name="PREVIEW_TEXT_TYPE" value="html"<?if($str_PREVIEW_TEXT_TYPE=="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_HTML")?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<textarea style="width:100%; height:200px;" name="PREVIEW_TEXT" wrap="virtual"><?echo $str_PREVIEW_TEXT?></textarea>
		</td>
	</tr>
	<?endif?>
<?php $tabControl->EndCustomField("PREVIEW_TEXT");?>
<?php $tabControl->BeginCustomField("PREVIEW_PICTURE", GetMessage("LEARNING_PICTURE"), false);?>
	<tr>
		<td valign="top"><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td>
			<?echo CFile::InputFile("PREVIEW_PICTURE", 20, $str_PREVIEW_PICTURE, false, 0, "IMAGE", "", 40);?><br>
			<?
				if($str_PREVIEW_PICTURE)
				{
					echo CFile::ShowImage($str_PREVIEW_PICTURE, 200, 200, "border=0", "", true);
				}
			?>
		</td>
	</tr>
<?php $tabControl->EndCustomField("PREVIEW_PICTURE");?>
<?$tabControl->BeginNextFormTab();?>
<!-- DETAIL_TEXT -->
	<!-- <tr class="heading">
		<td colspan="2"><?echo GetMessage("LEARNING_ELEMENT_DETAIL")?></td>
	</tr> -->
<?php $tabControl->BeginCustomField("DETAIL_TEXT", GetMessage("LEARNING_DESCRIPTION"), false);?>
	<?if(COption::GetOptionString("learning", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame(
				"DETAIL_TEXT",
				$str_DETAIL_TEXT,
				"DETAIL_TEXT_TYPE",
				$str_DETAIL_TEXT_TYPE,
				array('width' => '100%', 'height' => '450'),
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
			<input type="radio" name="DETAIL_TEXT_TYPE" value="text"<?if($str_DETAIL_TEXT_TYPE!="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_TEXT")?>
			<input type="radio" name="DETAIL_TEXT_TYPE" value="html"<?if($str_DETAIL_TEXT_TYPE=="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_HTML")?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<textarea style="width:100%; height:250px;" name="DETAIL_TEXT" wrap="off"><?echo $str_DETAIL_TEXT?></textarea>
		</td>
	</tr>
	<?endif?>
<?php $tabControl->EndCustomField("DETAIL_TEXT");


// Tab: Relations
if ($COURSE_ID > 0)
{
	$linkedLessonId = CCourse::CourseGetLinkedLesson ($COURSE_ID);
	$tabControl->BeginNextFormTab();
	$tabControl->BeginCustomField("_RELATIONS", '', false);
	echo '<tr><td>';
	CLearnRelationHelper::RenderForm($oAccess, $linkedLessonId, $arOPathes);
	echo '</td></tr>';
	$tabControl->EndCustomField("_RELATIONS");
}


$tabControl->BeginNextFormTab();
$tabControl->BeginCustomField("__GESGSTR", '', false);
if ($COURSE_ID > 0)
{
	$linkedLessonId = CCourse::CourseGetLinkedLesson ($COURSE_ID);
	if ( ($linkedLessonId !== false)
		&& $oAccess->IsLessonAccessible ($linkedLessonId, CLearnAccess::OP_LESSON_MANAGE_RIGHTS)
	)
	{
		$readOnly = false;
	}
	else
	{
		$readOnly = true;
	}

	CLearnRenderRightsEdit::RenderLessonRightsTab ($USER->GetID(), 'LESSON_RIGHTS', $linkedLessonId, $readOnly);
}
else
{
	if ($oAccess->IsBaseAccess(CLearnAccess::OP_LESSON_MANAGE_RIGHTS))
		$readOnly = false;
	else
		$readOnly = true;

	CLearnRenderRightsEdit::RenderLessonRightsTab ($USER->GetID(), 'LESSON_RIGHTS', 0, $readOnly);
}
$tabControl->EndCustomField("__GESGSTR");

if ($Perm < 'X')
	$isBtnsDisabled = true;
else
	$isBtnsDisabled = false;

$tabControl->BeginNextFormTab();
$tabControl->BeginCustomField("UFS", '', false);
if ($linkedLessonId)
	$USER_FIELD_MANAGER->EditFormShowTab('LEARNING_LESSONS', $bVarsFromForm, $linkedLessonId);
else
	$USER_FIELD_MANAGER->EditFormShowTab('LEARNING_LESSONS', $bVarsFromForm, 0);
$tabControl->EndCustomField("UFS");

$tabControl->Buttons(
	array(
		'disabled' => $isBtnsDisabled,
		"back_url" => "/bitrix/admin/learn_unilesson_admin.php?lang=".LANG."&PARENT_LESSON_ID=-1"
			. $uriParentLessonPath
			. GetFilterParams("filter_", false)
		)
	);



$tabControl->arParams["FORM_ACTION"] = $APPLICATION->GetCurPage()."?lang=".LANG."&COURSE_ID=".$COURSE_ID;
$tabControl->Show();
$tabControl->ShowWarnings($tabControl->GetName(), $message);

else:?>
<?CAdminMessage::ShowMessage(GetMessage("LEARNING_BAD_COURSE"));?>
<?endif?>
<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
