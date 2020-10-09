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

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/tar_gz.php");

set_time_limit(0);
$STEP = intval($STEP);
if ($STEP <= 0)
	$STEP = 1;
if ($REQUEST_METHOD == "POST" && $backButton <> '')
	$STEP = $STEP - 2;
if ($REQUEST_METHOD == "POST" && $backButton2 <> '')
	$STEP = 1;

$COURSE_ID = intval($COURSE_ID);
$strError = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && $STEP > 1 && check_bitrix_sessid())
{
	if ($STEP > 1)
	{
		// was: $res = CCourse::GetList(Array("sort" => "asc"),Array("ID" => $COURSE_ID,"MIN_PERMISSION" => "W"));
		// TODO: think about better way of rights check (for every exported lesson, I think).
		$res = CCourse::GetList(Array("sort" => "asc"),Array("ID" => $COURSE_ID,'ACCESS_OPERATIONS' => CLearnAccess::OP_LESSON_READ | CLearnAccess::OP_LESSON_WRITE));
		
		if (!$arCourse = $res->GetNext())
			$strError .= GetMessage("LEARNING_BAD_COURSE")."<br>";

		if ($strError <> '')
			$STEP = 1;
	}

	if ($STEP > 2)
	{
		// Check filename
		$pattern = '#^[0-9a-zA-Z_.-/]+$#';
		$antiPattern = '#[^0-9a-zA-Z_.-/]#';
		if (preg_match($pattern, $DATA_FILE_NAME) !== 1)
		{
			$DATA_FILE_NAME = preg_replace($antiPattern, '', $DATA_FILE_NAME);
			$strError .= GetMessage('LEARNING_BAD_FILENAME');
			$STEP = 2;
		}
	}

	if ($STEP > 2)
	{
		$tmp_dir = BX_PERSONAL_ROOT."/tmp/learning/".uniqid(rand());
		CheckDirPath($_SERVER["DOCUMENT_ROOT"].$tmp_dir);

		$exportFolder = '/upload/learning_export/';
		if ( ! file_exists($_SERVER["DOCUMENT_ROOT"] . $exportFolder) )
			mkdir ($_SERVER["DOCUMENT_ROOT"] . $exportFolder);

		$DATA_FILE_NAME = $exportFolder . BX_basename($DATA_FILE_NAME);

		if ($DATA_FILE_NAME == '')
		{
			$strError .= GetMessage("LEARNING_NO_DATA_FILE")."<br>";
		}
		else
		{
			$bUseCompression = true;
			if(!extension_loaded('zlib') || !function_exists("gzcompress"))
				$bUseCompression = false;

			if (mb_substr($DATA_FILE_NAME, -6) != "tar.gz")
				$DATA_FILE_NAME .= ".tar.gz";

			if (is_file($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME))
				@unlink($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME);

			if ($arCourse["SCORM"] == "Y")
			{
				$dir = "/".(COption::GetOptionString("main", "upload_dir", "upload"))."/learning/scorm/".$COURSE_ID."/";

				$arc = new CArchiver($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME, $bUseCompression);
				$res = $arc->Add("\"".$_SERVER["DOCUMENT_ROOT"].$dir."\"", false, $_SERVER["DOCUMENT_ROOT"].$dir);

				if (!$res)
				{
					$arErrors = &$arc->GetErrors();
					foreach ($arErrors as $value)
						$strError .= "[".$value[0]."] ".$value[1]."<br>";
				}
			}
			else
			{
				$package = new CCoursePackage($COURSE_ID);

				if ($package->LAST_ERROR == '')
				{
					$success = $package->CreatePackage($tmp_dir);

					if ($success)
					{
						$arc = new CArchiver($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME, $bUseCompression);
						$res = $arc->Add("\"".$_SERVER['DOCUMENT_ROOT'].$tmp_dir."\"", false, $_SERVER['DOCUMENT_ROOT'].$tmp_dir);

						if (!$res)
						{
							$arErrors = &$arc->GetErrors();
							foreach ($arErrors as $value)
								$strError .= "[".$value[0]."] ".$value[1]."<br>";
						}

						DeleteDirFilesEx($tmp_dir);
					}
					else
					{
						$strError .= $package->LAST_ERROR;
					}
				}
				else
				{
					$strError .= $package->LAST_ERROR;
				}
			}
		}

		if ($strError <> '')
			$STEP = 2;
	}
}

$APPLICATION->SetTitle(GetMessage("LEARNING_PAGE_TITLE")." ".$STEP);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if (defined("LEARNING_ADMIN_ACCESS_DENIED"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"), false);
CAdminMessage::ShowMessage($strError);
?>


<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANG ?>" ENCTYPE="multipart/form-data">

<input type="hidden" name="STEP" value="<?echo $STEP + 1;?>">
<?=bitrix_sessid_post()?>
<?
if ($STEP > 1)
{
	?><input type="hidden" name="COURSE_ID" value="<?echo $COURSE_ID ?>"><?
}
?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("LEARNING_ADMIN_TAB1"), "TITLE" => GetMessage("LEARNING_ADMIN_TAB1_EX")),
		array("DIV" => "edit2", "TAB" => GetMessage("LEARNING_ADMIN_TAB2"),  "TITLE" => GetMessage("LEARNING_ADMIN_TAB2_EX")),
		array("DIV" => "edit3", "TAB" => GetMessage("LEARNING_ADMIN_TAB3"), "TITLE" => GetMessage("LEARNING_ADMIN_TAB3_EX"))
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();

if ($STEP < 2)
{
	?>
	<tr>
		<td><?echo GetMessage("LEARNING_COURSES") ?>:</td>
		<td>
			<select name="COURSE_ID" style="width:300px;">
				<?
				// was: $course = CCourse::GetList(array("SORT" => "ASC"), array("MIN_PERMISSION" => "W"));
				// TODO: think about better way of rights check (for every exported lesson, I think).
				$course = CCourse::GetList(array("SORT" => "ASC"), array('ACCESS_OPERATIONS' => CLearnAccess::OP_LESSON_READ | CLearnAccess::OP_LESSON_WRITE));
				while ($course->ExtractFields("f_"))
				{
					?><option value="<?echo $f_ID ?>" <?if (intval($f_ID)==$COURSE_ID) echo "selected";?>><?echo $f_NAME ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<?
}

$tabControl->EndTab();
?>

<?
$tabControl->BeginNextTab();

if ($STEP == 2)
{
	?>

	<tr class="heading">
		<td colspan="2"><?echo GetMessage("LEARNING_DATA_FILE_NAME") ?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("LEARNING_DATA_FILE_NAME1") ?>:<br>&nbsp;</td>
		<td valign="top">
			<input type="text" name="DATA_FILE_NAME" size="40" value="<?echo ($DATA_FILE_NAME <> '')?htmlspecialcharsbx($DATA_FILE_NAME):"package".$COURSE_ID.".tar.gz"?>"><br>
			<small><?echo GetMessage("LEARNING_DATA_FILE_NAME1_DESC") ?></small>
		</td>
	</tr>
	<?
}

$tabControl->EndTab();
?>

<?
$tabControl->BeginNextTab();

if ($STEP > 2)
{

	?>
	<tr>
		<td colspan="2"><b><?echo GetMessage("LEARNING_SUCCESS") ?></b></td>
	</tr>
	<tr>
		<td colspan="2">
			<?php
			$tmp = '/' . $DATA_FILE_NAME;
			$arAllowedPrefixes = array('http://', 'ftp://', 'https://', '/');
			foreach ($arAllowedPrefixes as $prefix)
			{
				if (mb_substr($DATA_FILE_NAME, 0, mb_strlen($prefix)) === $prefix)
				{
					$tmp = $DATA_FILE_NAME;
					break;
				}
			}

			echo str_replace("%DATA_URL%", "<a href=\"".htmlspecialcharsbx($tmp)."\" target=\"_blank\">".htmlspecialcharsbx($DATA_FILE_NAME)."</a>", GetMessage("LEARNING_SU_ALL1"));
			?>
		</td>
	</tr>
	<?
}
$tabControl->EndTab();
?>

<?
$tabControl->Buttons();
?>

<?if ($STEP < 3):?>
	<?if ($STEP > 1):?>
		<input type="submit" name="backButton" value="&lt;&lt; <?echo GetMessage("LEARNING_BACK") ?>">
	<?endif?>
	<input type="submit" class="adm-btn-green" value="<?echo ($STEP==2)?GetMessage("LEARNING_NEXT_STEP_F"):GetMessage("LEARNING_NEXT_STEP") ?> &gt;&gt;" name="submit_btn">
<?else:?>
	<input type="submit" name="backButton2" value="&lt;&lt; <?echo GetMessage("LEARNING_2_1_STEP") ?>">
<?endif;?>

<?
$tabControl->End();
?>

</form>

<script language="JavaScript">
<!--
<?if ($STEP < 2):?>
tabControl.SelectTab("edit1");
tabControl.DisableTab("edit2");
tabControl.DisableTab("edit3");
<?elseif ($STEP == 2):?>
tabControl.SelectTab("edit2");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit3");
<?elseif ($STEP > 2):?>
tabControl.SelectTab("edit3");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit2");
<?endif;?>
//-->
</script>

<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>