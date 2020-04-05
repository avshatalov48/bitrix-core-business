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

$module_id = "learning";

$oArchiver = false;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/tar_gz.php");

$oAccess = CLearnAccess::GetInstance($USER->GetID());

$LEARNING_RIGHT = false;
if ($oAccess->IsBaseAccess(
	CLearnAccess::OP_LESSON_CREATE 
	| CLearnAccess::OP_LESSON_LINK_TO_PARENTS 
	| CLearnAccess::OP_LESSON_LINK_DESCENDANTS
	)
	||
	(
		$oAccess->IsBaseAccess (CLearnAccess::OP_LESSON_CREATE )
		&& $oAccess->IsBaseAccessForCR (CLearnAccess::OP_LESSON_LINK_TO_PARENTS | CLearnAccess::OP_LESSON_LINK_DESCENDANTS)
	)
)
{
	$LEARNING_RIGHT = true;
}

if ( ! $LEARNING_RIGHT )
{
	$APPLICATION->SetTitle(GetMessage('LEARNING_PAGE_TITLE'));
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


set_time_limit(0);
$STEP = intval($STEP);
$strError = "";
if ($STEP <= 0)
	$STEP = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST" && strlen($backButton) > 0)
	$STEP = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST" && $STEP == 2 && check_bitrix_sessid())
{

	if (!is_array($SITE_ID) || empty($SITE_ID))
		$strError .= GetMessage("LEARNING_BAD_SITE_ID")."<br>";


	if (strlen($URL_DATA_FILE) > 0 )
	{
		if ( ! CBXArchive::IsArchive($_SERVER["DOCUMENT_ROOT"] . $URL_DATA_FILE) )
			$strError .= GetMessage("LEARNING_NOT_TAR_GZ")."<br>";
	}
	else
		$strError .= GetMessage("LEARNING_DATA_FILE_NOT_FOUND");

	if (strlen($strError)<=0)
	{
		$oArchiver = CBXArchive::GetArchive($_SERVER["DOCUMENT_ROOT"] . $URL_DATA_FILE);
		if ($oArchiver === false)
			$strError .= GetMessage("LEARNING_NOT_TAR_GZ") . "<br>";
	}

	if ((strlen($strError)<=0) && ($oArchiver !== false))
	{
		$tmp_dir = BX_PERSONAL_ROOT."/tmp/learning/".uniqid(rand());
		CheckDirPath($_SERVER["DOCUMENT_ROOT"].$tmp_dir);

		if ($oArchiver->Unpack($_SERVER["DOCUMENT_ROOT"].$tmp_dir))
		{
			if (!isset($SCORM))
			{
				$package = new CCourseImport($tmp_dir, $SITE_ID);
				if (!strlen($package->LAST_ERROR))
				{
					if (!$package->ImportPackage())
						$strError .= $package->LAST_ERROR;
				}
				else
				{
					$strError .= $package->LAST_ERROR;
				}
			}
			else
			{
				$package = new CCourseSCORM($tmp_dir, $SITE_ID);
				if (!strlen($package->LAST_ERROR))
				{
					if (!$package->ImportPackage())
						$strError .= $package->LAST_ERROR;
				}
				else
				{
					$strError .= $package->LAST_ERROR;
				}
			}
		}
		else
		{
			$strError .= GetMessage("MAIN_T_EDIT_IMP_ERR");
			$arErrors = &$oArchiver->GetErrors();
			if (count($arErrors)>0)
			{
				$strError .= ":<br>";
				foreach ($arErrors as $value)
					$strError .= "[".$value[0]."] ".$value[1]."<br>";
			}
			else
				$strError .= ".<br>";
		}
		DeleteDirFilesEx($tmp_dir);
	}

	if (strlen($strError)>0)
		$STEP = 1;
}

$APPLICATION->SetTitle(GetMessage('LEARNING_PAGE_TITLE'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if (defined("LEARNING_ADMIN_ACCESS_DENIED"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"), false);
CAdminMessage::ShowMessage($strError);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("LEARNING_ADMIN_TAB1"), "TITLE"=>GetMessage("LEARNING_ADMIN_TAB1_EX")),
	array("DIV" => "edit2", "TAB" => GetMessage("LEARNING_ADMIN_TAB2"), "TITLE"=>GetMessage("LEARNING_ADMIN_TAB2_EX")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);
?>

<form method="post" enctype="multipart/form-data" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANG ?>" name="form1">
<input type="hidden" name="STEP" value="<?echo $STEP + 1;?>">
<?=bitrix_sessid_post()?>

<?
$tabControl->Begin();
$tabControl->BeginNextTab();
if ($STEP == 1):
?>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("LEARNING_DATA_FILE") ?>:</td>
		<td>
			<input type="text" name="URL_DATA_FILE" size="30">
			<input type="button" value="<?echo GetMessage("LEARNING_OPEN") ?>" OnClick="BtnClick()">
			<?
			CAdminFileDialog::ShowScript
			(
				Array(
					"event" => "BtnClick",
					"arResultDest" => array("FORM_NAME" => "form1", "FORM_ELEMENT_NAME" => "URL_DATA_FILE"),
					"arPath" => array("SITE" => SITE_ID, "PATH" =>"/upload"),
					"select" => 'F',// F - file only, D - folder only
					"operation" => 'O',
					"showUploadTab" => true,
					"showAddToMenuTab" => false,
					"fileFilter" => 'gz',
					"allowAllFiles" => true,
					"SaveConfig" => true,
				)
			);
			?>
		</td>
	</tr>

	<tr class="adm-detail-required-field">
		<td class="adm-detail-valign-top"><?echo GetMessage("LEARNING_SITE_ID")?>:</td>
		<td><?=CLang::SelectBoxMulti("SITE_ID", $SITE_ID);?></td>
	</tr>

	<tr>
		<td><?echo GetMessage("LEARNING_IF_SCORM")?>:</td>
		<td><input type="checkbox" name="SCORM"<?php echo (isset($SCORM) ? " checked" : "")?> /></td>
	</tr>
<?
endif;
$tabControl->EndTab();
$tabControl->BeginNextTab();
if ($STEP==2):
?>
	<tr>
		<td colspan="2"><b><?echo GetMessage("LEARNING_SUCCESS") ?></b></td>
	</tr>
<?
endif;
$tabControl->EndTab();
$tabControl->Buttons();
?>

<?if ($STEP > 1):?>
	<input type="submit" name="backButton" value="&lt;&lt; <?echo GetMessage("LEARNING_BACK") ?>">
<?else:?>
	<input type="submit" class="adm-btn-green" value="<?=GetMessage("LEARNING_NEXT_STEP_F")?> &gt;&gt;" name="submit_btn">
<?endif?>
<?$tabControl->End();?>
</form>

<script type="text/javascript">
<!--
<?if ($STEP == 1):?>
tabControl.SelectTab("edit1");
tabControl.DisableTab("edit2");
<?elseif ($STEP == 2):?>
tabControl.SelectTab("edit2");
tabControl.DisableTab("edit1");
<?endif;?>
//-->
</script>

<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>