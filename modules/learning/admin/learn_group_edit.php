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

$APPLICATION->AddHeadScript('/bitrix/js/learning/learning_edit.js');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/admin_tools_user_selector.php");

ClearVars();

$ID = intval($ID);
$bCopy = false;
$bBadResult = false;
$message = null;
$arMembers = array();

if ($ID != 0)
{
	$r = CLearningGroup::GetList(array($by => $order), array('ID' => $ID));

	if(!$r->ExtractFields("str_"))
		$bBadResult = true;
	else
	{
		$rc = CLearningGroupMember::getList(
			array(),	// arOrder
			array('LEARNING_GROUP_ID' => $ID),	// arFilter
			array('USER_ID')	// arSelect
		);

		while ($arMember = $rc->fetch())
			$arMembers[] = $arMember['USER_ID'];

		$arMembers = array_unique($arMembers);
	}
}

if($bBadResult)
{
	$APPLICATION->SetTitle(GetMessage("LEARNING_ADMIN_TITLE"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$aContext = array(
		array(
			"ICON" => "btn_list",
			"TEXT"=>GetMessage("LEARNING_BACK_TO_LEARNING_GROUPS"),
			"LINK"=>"learn_group_admin.php?lang=" . LANG,
			"TITLE"=>GetMessage("LEARNING_BACK_TO_LEARNING_GROUPS")
		),
	);
	$context = new CAdminContextMenu($aContext);
	$context->Show();

	CAdminMessage::ShowMessage(GetMessage("LEARNING_BAD_LEARNING_GROUP_ID_EX"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$aTabs = array(
	array(
		"DIV" => "edit1", 
		"TAB" => GetMessage("LEARNING_ADMIN_TAB1"), 
		"ICON"=>"main_user_edit", 
		"TITLE"=>GetMessage("LEARNING_ADMIN_TAB1_EX")
	)
);

$aTabs[] = $USER_FIELD_MANAGER->EditFormTab('LEARNING_LGROUPS');
$tabControl = new CAdminForm("learningGroupResultTabControl", $aTabs);

if ($_SERVER["REQUEST_METHOD"] == "POST" && strlen($Update)>0 && check_bitrix_sessid())
{
	if ($ACTIVE !== 'Y')
		$ACTIVE = 'N';

	$COURSE_LESSON_ID = (int) $COURSE_LESSON_ID;
	$str_COURSE_LESSON_ID = (int) $COURSE_LESSON_ID;

	$arFields = array(
		"TITLE"            => $TITLE,
		"ACTIVE"           => $ACTIVE,
		"CODE"             => $CODE,
		"SORT"             => $SORT,
		"ACTIVE_FROM"      => $ACTIVE_FROM,
		"ACTIVE_TO"        => $ACTIVE_TO,
		"COURSE_LESSON_ID" => $COURSE_LESSON_ID
	);

	// Process lessons' delays
	if ($ID && isset($PERIOD_L) && is_array($PERIOD_L))
	{
		$arDelays = array();
		foreach ($PERIOD_L as $lessonId => $delay)
			$arDelays[(int)$lessonId] = (int) $delay;

		CLearningGroupLesson::setDelays($ID, $arDelays);
	}

	// Process members
	$arNewMembers = array();
	$arAddedMembers = array();
	$arRemovedMembers = array();
	if ($USER->CanDoOperation('view_subordinate_users') || $USER->CanDoOperation('view_all_users'))
	{

		if (
			isset($_POST['PROP'], $_POST['SELECTPROP'])
			&& is_array($_POST['PROP']) && is_array($_POST['SELECTPROP'])
			&& isset($_POST['PROP'][1], $_POST['SELECTPROP'][1])
		)
		{
			foreach ($_POST['SELECTPROP'][1] as $key => $data)
			{
				if ($data['VALUE'] === 'none')
					continue;

				$value = (int) $_POST['PROP'][1][$key]['VALUE'];

				if ($value < 1)
					continue;

				$arNewMembers[] = $value;
			}
		}

		$arNewMembers = array_unique($arNewMembers);

		$arAddedMembers   = array_diff($arNewMembers, $arMembers);
		$arRemovedMembers = array_diff($arMembers, $arNewMembers);
	}

	if ($USER_FIELD_MANAGER->getRights('LEARNING_LGROUPS') >= 'W')
		$USER_FIELD_MANAGER->EditFormAddFields('LEARNING_LGROUPS', $arFields);

	$res = false;
	$oAccess = CLearnAccess::GetInstance($USER->GetID());
	$isAccessible = $oAccess->IsBaseAccess(CLearnAccess::OP_LESSON_MANAGE_RIGHTS);

	$DB->StartTransaction();

	if ($isAccessible)
	{
		$tr = new CLearningGroup;
		if ($ID == 0)
		{
			$res = $tr->add($arFields);

			if ($res > 0)
				$ID = (int) $res;
		}
		else
			$res = $tr->update($ID, $arFields);

		if ($res)
		{
			foreach($arAddedMembers as $memberId)
			{
				CLearningGroupMember::add(array(
					'USER_ID'           => $memberId,
					'LEARNING_GROUP_ID' => $ID
				));
			}

			foreach($arRemovedMembers as $memberId)
				CLearningGroupMember::delete($memberId, $ID);
		}
	}

	if(!$res)
	{
		$DB->Rollback();
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("LEARNING_ERROR"), $e);
		elseif (!$isAccessible)
			$message = new CAdminMessage(GetMessage("LEARNING_ERROR") . ': ' . GetMessage('LEARNING_ACCESS_D_FOR_EDIT_CONTENT'));

		$bVarsFromForm = true;
	}
	else
	{
		$DB->Commit();

		if(strlen($apply)<=0)
		{
			if(strlen($return_url)>0)
				LocalRedirect($return_url);
			else
				LocalRedirect("/bitrix/admin/learn_group_admin.php?lang=".LANG.GetFilterParams("filter_", false));
		}

		LocalRedirect("/bitrix/admin/learn_group_edit.php?lang=".LANG."&ID=".$ID.GetFilterParams("filter_", false));
	}
}

if($bVarsFromForm)
{
	$DB->InitTableVarsForEdit("b_learn_groups", "", "str_");
}

$adminChain->AddItem(array(
	"TEXT" => GetMessage("LEARNING_GROUPS_LIST"),
	"LINK"=>"learn_group_admin.php?lang=". LANG.GetFilterParams("filter_", false)
));

if ($ID == 0)
	$APPLICATION->SetTitle(GetMessage("LEARNING_NEW_TITLE"));
else
	$APPLICATION->SetTitle(GetMessage("LEARNING_EDIT_TITLE") . ' #' . $str_ID . ' ("' . htmlspecialcharsback($str_TITLE) . '")');

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aContext = array(
		array(
			"ICON" => "btn_list",
			"TEXT"=>GetMessage("MAIN_ADMIN_MENU_LIST"),
			"LINK"=>"learn_group_admin.php?lang=" . LANG
				. GetFilterParams("filter_"),
			"TITLE"=>GetMessage("LEARNING_BACK_TO_ADMIN")
		),
	);
$context = new CAdminContextMenu($aContext);
$context->Show();
?>

<?
if ($message)
	echo $message->Show();

if (!isset($str_SORT))
	$str_SORT = 500;

?>

<?php $tabControl->BeginEpilogContent();?>
	<?=bitrix_sessid_post()?>
	<?echo GetFilterHiddens("filter_");?>
	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="from" value="<?echo htmlspecialcharsbx($from)?>">
	<input type="hidden" name="return_url" value="<?echo htmlspecialcharsbx($return_url)?>">
	<input type="hidden" name="ID" value="<?echo $ID?>">
<?php $tabControl->EndEpilogContent();?>
<?$tabControl->Begin();?>
<?$tabControl->BeginNextFormTab();?>
<?php $tabControl->BeginCustomField("TITLE", GetMessage("LEARNING_ADMIN_TITLE"), $required = true);?>
<tr class="adm-detail-required-field">
	<td width="40%"><?php echo $tabControl->GetCustomLabelHTML()?>:</td>
	<td>
		<input type="text" name="TITLE" size="20" maxlength="255" value="<? echo $str_TITLE; ?>">
	</td>
</tr>
<?php $tabControl->EndCustomField("TITLE");?>
<?php $tabControl->BeginCustomField("CODE", GetMessage("LEARNING_ADMIN_CODE"), false);?>
<tr>
	<td width="40%"><?php echo $tabControl->GetCustomLabelHTML()?>:</td>
	<td>
		<input type="text" name="CODE" size="20" maxlength="50" value="<?echo $str_CODE;?>">
	</td>
</tr>
<?php $tabControl->EndCustomField("CODE");?>
<?php $tabControl->BeginCustomField("COURSE_LESSON_ID", GetMessage("LEARNING_ADMIN_ATTACHED_COURSE"), $required = true);?>
	<tr class="adm-detail-required-field">
		<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
		<td><?php
			if ($str_COURSE_LESSON_ID)
			{
				$rsLesson = CLearnLesson::GetByID($str_COURSE_LESSON_ID);
				$arLesson = $rsLesson->Fetch();

				$curDir = $APPLICATION->GetCurDir();
				if (substr($curDir, -1) !== '/')
					$curDir .= '/';
			}
			?>
			<script>
			function module_learning_js_admin_function_change_attached_lesson(lesson_id, name)
			{
				BX('attached_lesson_id').value = lesson_id;
				BX('attached_lesson_name').textContent = name;
			}
			</script>
			<div style="padding:0px;">
				<span id="attached_lesson_name"><?php
					if ($arLesson)
						echo htmlspecialcharsbx($arLesson['NAME']);
				?></span><?php
				if ($ID == 0)
				{
					?>
					(<a href="javascript:void(0);" class="bx-action-href"
						onclick="window.open('/bitrix/admin/learn_unilesson_admin.php?lang=<?php echo LANGUAGE_ID;
							?>&amp;search_retpoint=module_learning_js_admin_function_change_attached_lesson&amp;search_mode_type=attach_question_to_lesson', 
							'module_learning_js_admin_window_select_lessons_for_attach', 
							'scrollbars=yes,resizable=yes,width=960,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 960)/2-5));" 
						><?php echo GetMessage('LEARNING_ADMIN_CHANGE_ATTACHED_COURSE'); ?></a>)
					<?php
				}
				?>
			</div>
			<input id="attached_lesson_id" type="hidden" name="COURSE_LESSON_ID" value="<?echo $str_COURSE_LESSON_ID; ?>">
		</td>
	</tr>
<?php $tabControl->EndCustomField("COURSE_LESSON_ID");?>
<?php $tabControl->BeginCustomField("ACTIVE_PERIOD", GetMessage("LEARNING_ACTIVE_PERIOD"), false);?>
<!-- Active period-->
<tr>
	<td><?php echo $tabControl->GetCustomLabelHTML()?>:</td>
	<td>
		<?echo CalendarPeriod("ACTIVE_FROM", $str_ACTIVE_FROM, "ACTIVE_TO", $str_ACTIVE_TO, "learningGroupResultTabControl", "N", "", "", "19")?>
	</td>
</tr>
<?php $tabControl->EndCustomField("ACTIVE_PERIOD");?>
<?php $tabControl->BeginCustomField("ACTIVE", GetMessage("LEARNING_ADMIN_ACTIVE"), false);?>
<tr>
	<td><?php echo $tabControl->GetCustomLabelHTML()?>:</td>
	<td>
		<input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>>
	</td>
</tr>
<?php $tabControl->EndCustomField("ACTIVE");?>
<?php $tabControl->BeginCustomField("SORT", GetMessage("LEARNING_ADMIN_SORT"), false);?>
<tr>
	<td><?php echo $tabControl->GetCustomLabelHTML()?>:</td>
	<td>
		<input type="text" name="SORT" size="4" maxlength="10" value="<?echo htmlspecialcharsbx($str_SORT);?>">
	</td>
</tr>
<?php
$tabControl->EndCustomField("SORT");

$tabControl->AddSection("LEARNING_ACTIVATION_SCHEDULE", GetMessage('LEARNING_ACTIVATION_SCHEDULE'));
$tabControl->BeginCustomField("PROPERTY_2", GetMessage('LEARNING_ACTIVATION_SCHEDULE_TITLE'), false);

$html = '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb'.md5($name).'">';

$arLessons = $arDelays = array();

if ($ID && $str_COURSE_LESSON_ID)
{
	$rs = CLearnLesson::GetListOfImmediateChilds($str_COURSE_LESSON_ID, array('SORT' => 'ASC'));

	while ($ar = $rs->getNext())
		$arLessons[$ar['LESSON_ID']] = $ar['NAME'];

	$arDelays = CLearningGroupLesson::getDelays($ID, array_keys($arLessons));

	foreach ($arLessons as $lessonId => $lessonName)
	{
		$period = $arDelays[$lessonId];

		$html .= '<tr><td>';
		$html .= $lessonName;
		$html .= '</td><td>';
		$html .= '<input type="text" name="PERIOD_L[' . $lessonId . ']" size="4" maxlength="5" value="' . htmlspecialcharsbx($period) . '">';
		$html .= '</td></tr>';
	}
}
else
	$html .= GetMessage('LEARNING_AVAILABLE_AFTER_ELEMENT_CREATION');

$html .= '</table>';
?>
<tr id="tr_PROPERTY_2">
	<td class="adm-detail-valign-top" width="40%"><?echo $tabControl->GetCustomLabelHTML();?>:</td>
	<td width="60%"><?php
		echo $html;
	?></td>
</tr>
<?
$tabControl->EndCustomField("PROPERTY_2", $hidden);

$tabControl->AddSection("LEARNING_ELEMENT_USERS", GetMessage('LEARNING_GROUP_MEMBERSHIP'));


$prop_fields = array(
	'ID'                 =>  1,
	'NAME'               => GetMessage('LEARNING_GROUP_MEMBERS_LIST'),
	'ACTIVE'             => 'Y',
	'PROPERTY_TYPE'      => 'S',
	'LIST_TYPE'          => 'L',
	'MULTIPLE'           => 'Y',
	'MULTIPLE_CNT'       =>  5,
	'IS_REQUIRED'        => 'N',
	'USER_TYPE'          => 'UserID',
	'USER_TYPE_SETTINGS' =>  null,
	'VALUE'              => $bVarsFromForm ? $arNewMembers : $arMembers,
	'~VALUE'             => array()
);

if ($bVarsFromForm)
{
	for ($i = 0; $i < 5; $i ++)
		$prop_fields['VALUE'][] = '';
}

foreach($prop_fields['VALUE'] as $id => $value)
	$prop_fields['~VALUE'][$id] = array('VALUE' => $value, 'DESCRIPTION' => '');

$tabControl->BeginCustomField("PROPERTY_1".$prop_fields["ID"], $prop_fields["NAME"], $prop_fields["IS_REQUIRED"]==="Y");
?>
<tr id="tr_PROPERTY_<?echo $prop_fields["ID"];?>"<?if ($prop_fields["PROPERTY_TYPE"]=="F"):?> class="adm-detail-file-row"<?endif?>>
	<td class="adm-detail-valign-top" width="40%"><?if($prop_fields["HINT"]!=""):
		?><span id="hint_<?echo $prop_fields["ID"];?>"></span><script>BX.hint_replace(BX('hint_<?echo $prop_fields["ID"];?>'), '<?echo CUtil::JSEscape($prop_fields["HINT"])?>');</script>&nbsp;<?
	endif;?><?echo $tabControl->GetCustomLabelHTML();?>:</td>
	<td width="60%"><?php
		if(!($USER->CanDoOperation('view_subordinate_users') || $USER->CanDoOperation('view_all_users')))
			echo GetMessage('LEARNING_ACCESS_DENIED_TO_USERS');
		else
			echo _ShowUserPropertyField('PROP['.$prop_fields["ID"].']', $prop_fields, $prop_fields["VALUE"], false, false, 50000, $tabControl->GetFormName(), $bCopy)
	?></td>
</tr>
<?
	$hidden = "";
	if(!is_array($prop_fields["~VALUE"]))
		$values = Array();
	else
		$values = $prop_fields["~VALUE"];
	$start = 1;
	foreach($values as $key=>$val)
	{
		if($bCopy)
		{
			$key = "n".$start;
			$start++;
		}

		if(is_array($val) && array_key_exists("VALUE",$val))
		{
			$hidden .= _ShowHiddenValue('PROP['.$prop_fields["ID"].']['.$key.'][VALUE]', $val["VALUE"]);
			$hidden .= _ShowHiddenValue('PROP['.$prop_fields["ID"].']['.$key.'][DESCRIPTION]', $val["DESCRIPTION"]);
		}
		else
		{
			$hidden .= _ShowHiddenValue('PROP['.$prop_fields["ID"].']['.$key.'][VALUE]', $val);
			$hidden .= _ShowHiddenValue('PROP['.$prop_fields["ID"].']['.$key.'][DESCRIPTION]', "");
		}
	}
$tabControl->EndCustomField("PROPERTY_1".$prop_fields["ID"], $hidden);


$tabControl->BeginNextFormTab();
?><div><?php
$tabControl->BeginCustomField("UFS", '', false);

	if ($USER_FIELD_MANAGER->getRights('LEARNING_LGROUPS') < 'W')
	{
		?>
		<p style="font-weight:bold;">
			<?php echo GetMessage('LEARNING_ACCESS_DENIED_TO_UF_MANAGE'); ?>
		</p>
		<?php
	}

	$USER_FIELD_MANAGER->EditFormShowTab('LEARNING_LGROUPS', $bVarsFromForm, $ID); 

$tabControl->EndCustomField("UFS");
?></div><?php

$tabControl->Buttons(Array("back_url" =>"learn_group_admin.php?lang=". LANG.GetFilterParams("filter_", false)));
$tabControl->arParams["FORM_ACTION"] = $APPLICATION->GetCurPage()."?lang=".LANG.GetFilterParams("filter_");
$tabControl->Show();
$tabControl->ShowWarnings($tabControl->GetName(), $message);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
