<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class Step1 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('WSL_STEP1_TITLE'));
		$this->SetNextStep("step2");
		$this->SetStepID("step1");
		$this->SetCancelStep("cancel");
	}

	function OnPostForm()
	{
		$wizard = &$this->GetWizard();
		$install_type = $wizard->GetVar("install_type");
		$wizard->SetCurrentStep($install_type);
	}

	function ShowStep()
	{
		$this->content = GetMessage('WSL_STEP1_CONTENT');
	}
}

class Step2 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('WSL_STEP2_TITLE'));
		$this->SetNextStep("step2_params");
		$this->SetPrevStep("step1");
		$this->SetStepID("step2");
		$this->SetCancelStep("cancel");
	}

	function ShowStep()
	{
		$this->content = '';

		CUtil::InitJSCore();

		$this->content .= <<<EOT
<script type="text/javascript">
function checkZIP()
{
	var obCSVFileRus = BX('loc_ussr');
	var obCSVFileNone = BX('none');
	var obZIPFile = BX('load_zip');
	var obOwnFile = BX('ffile');

	if (obCSVFileRus && obCSVFileNone && obZIPFile && obOwnFile)
	{
		if (obCSVFileRus.checked || obCSVFileNone.checked || obOwnFile.checked)
			obZIPFile.disabled = false;
		else
		{
			obZIPFile.disabled = true;
			obZIPFile.checked = false;
		}

		if(obOwnFile.checked)
			BX.show(BX('fileupload'));
		else
			BX.hide(BX('fileupload'));
	}
}

</script>
EOT;
		$this->content .= "<b>".GetMessage('WSL_STEP2_GFILE_TITLE')."</b><p>";

		$this->content .= $this->ShowRadioField("locations_csv", "loc_ussr.csv", array("onchange" => "checkZIP()", "id" => "loc_ussr", "checked" => "checked"))
			." <label for=\"loc_ussr\">".GetMessage('WSL_STEP2_GFILE_USSR')."</label><br />";

		$this->content .= $this->ShowRadioField("locations_csv", "loc_ua.csv", array("onchange" => "checkZIP()", "id" => "loc_ua"))
			." <label for=\"loc_ua\">".GetMessage('WSL_STEP2_GFILE_UA')."</label><br />";
		$this->content .= $this->ShowRadioField("locations_csv", "loc_kz.csv", array("onchange" => "checkZIP()", "id" => "loc_kz"))
			." <label for=\"loc_kz\">".GetMessage('WSL_STEP2_GFILE_KZ')."</label><br />";
		$this->content .= $this->ShowRadioField("locations_csv", "loc_usa.csv", array("onchange" => "checkZIP()", "id" => "loc_usa"))
			." <label for=\"loc_usa\">".GetMessage('WSL_STEP2_GFILE_USA')."</label><br />";
		$this->content .= $this->ShowRadioField("locations_csv", "loc_cntr.csv", array("onchange" => "checkZIP()", "id" => "loc_cntr"))
			." <label for=\"loc_cntr\">".GetMessage('WSL_STEP2_GFILE_CNTR')."</label><br />";
		$this->content .= $this->ShowRadioField("locations_csv", "locations.csv", array("onchange" => "checkZIP()", "id" => "ffile"))
			." <label for=\"ffile\">".GetMessage('WSL_STEP2_GFILE_FILE')."</label><br />"
			."<span style=\"display:none;\" id=\"fileupload\">"."<input type=\"file\" name=\"FILE_IMPORT_UPLOAD\" value=\"\"><br />"."</span>";
		$this->content .= $this->ShowRadioField("locations_csv", "", array("onchange" => "checkZIP()", "id" => "none"))
			." <label for=\"none\">".GetMessage('WSL_STEP2_GFILE_NONE')."</label>";

		$this->content .= "</p><p>";

		$this->content .= $this->ShowCheckboxField("load_zip", 'Y', array("id" => "load_zip"))
			." <label for=\"load_zip\">".GetMessage('WSL_STEP2_GFILE_ZIP')."</label>";

		$this->content .= "</p><p><b>".GetMessage('WSL_STEP2_GSYNC_TITLE')."</b></p><p>";

		$this->content .= $this->ShowRadioField("sync", 'Y', array("id" => "sync_Y", "checked" => "checked"))
			." <label for=\"sync_Y\">".GetMessage('WSL_STEP2_GSYNC_Y')."</label><br />";
		$this->content .= $this->ShowRadioField("sync", 'N', array("id" => "sync_N"))
			." <label for=\"sync_N\">".GetMessage('WSL_STEP2_GSYNC_N')."</label><br />";

		$this->content .= "</p>";

		$this->content .= '<small>'.GetMessage('WSL_STEP2_GSYNC_HINT').'</small>';
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();

		if ($wizard->IsNextButtonClick() || $wizard->IsFinishButtonClick())
		{
			$locations_csv = $wizard->GetVar('locations_csv');
			$load_zip = $wizard->GetVar('load_zip');

			if ($locations_csv == '' && $load_zip != 'Y')
				$this->SetError(GetMessage('WSL_STEP2_GFILE_ERROR'), 'locations_csv');

			if($locations_csv == "locations.csv")
			{
				if (!is_uploaded_file($_FILES["FILE_IMPORT_UPLOAD"]["tmp_name"])
					|| !file_exists($_FILES["FILE_IMPORT_UPLOAD"]["tmp_name"]))
					$this->SetError(GetMessage("NO_LOC_FILE"), 'locations_csv');
				else
				{
					$fp = fopen($_FILES["FILE_IMPORT_UPLOAD"]["tmp_name"], 'r');
					$contents = fread($fp, filesize($_FILES["FILE_IMPORT_UPLOAD"]["tmp_name"]));
					fclose($fp);

					$contents = $GLOBALS["APPLICATION"]->ConvertCharset($contents, 'windows-1251', LANG_CHARSET);

					$sTmpFilePath = CTempFile::GetDirectoryName(12, 'sale');
					CheckDirPath($sTmpFilePath);

					$fp = fopen($sTmpFilePath."locations.csv", 'w+');
					fwrite($fp, $contents);
					fclose($fp);
				}
			}
		}
	}
}

class Step5 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('WSL_STEP5_TITLE'));
		$this->SetNextStep("step3");
		$this->SetPrevStep("step2");
		$this->SetStepID("step2_params");
		$this->SetCancelStep("cancel");
	}

	function ShowStep()
	{
		$wizard = &$this->GetWizard();
		$wizard->SetDefaultVars(
			Array(
				"step_length" => "20",
			)
		);

		$this->content = '';
		$this->content .= '<p>'.GetMessage('WSL_STEP5_STEP_LENGTH_TITLE').": ".$this->ShowInputField("text", "step_length", Array("size" => "20")).'</p>';
		$this->content .= '<p><small>'.GetMessage('WSL_STEP5_STEP_LENGTH_HINT').'</small></p>';
	}

	function OnPostForm()
	{
		$wizard = &$this->GetWizard();

		if ($wizard->IsNextButtonClick())
		{
			$step_length = intval($wizard->GetVar("step_length"));

			if ($step_length <= 0)
				$this->SetError(GetMessage('WSL_STEP5_STEP_LENGTH_ERROR'), "step_length");
		}
	}
}

class Step3 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('WSL_STEP3_TITLE'));
		$this->SetNextStep("step4");
		$this->SetPrevStep("step2_params");
		$this->SetStepID("step3");
		$this->SetCancelStep("cancel");
	}

	function ShowStep()
	{
		$wizard =& $this->GetWizard();
		$filename = $wizard->GetVar('locations_csv');
		$bLoadZIP = $wizard->GetVar('load_zip');

		$path = $wizard->package->path;

		$this->content .= '<div style="padding: 17px;">';
		$this->content .= '<div id="output"></div>';
		$this->content .= '<div id="wait_message" style="display: none;"></div>';
		$this->content .= '<div id="error_message" style="display: none;"><br /><button onclick="RunAgain(); return false">'.GetMessage('WSL_STEP3_ERROR_TRY').'</button></div>';
		$this->content .= '</div>';
		$this->content .= '<script type="text/javascript" src="/bitrix/js/main/cphttprequest.js"></script>';
		$this->content .= '<script language="JavaScript" src="'.$path.'/js/import.js"></script>';
		$this->content .= '<script language="JavaScript">

var nextButtonID = "'.$wizard->GetNextButtonID().'";
var formID = "'.$wizard->GetFormName().'";
var ajaxMessages = {wait:\''.GetMessage('WSL_STEP3_LOADING').' <img src="'.$path.'/images/loading.gif">\'};
var obImageCache = new Image();
obImageCache.src = \''.$path.'/images/loading.gif\';
var filename = "'.CUtil::JSEscape($filename).'";
var load_zip = "'.($bLoadZIP == 'Y' ? 'Y' : 'N').'";
var path = "'.CUtil::JSEscape($path).'";
var sessid = "'.bitrix_sessid().'";

if (window.addEventListener)
{
	window.addEventListener("load", DisableButton, false);
	window.addEventListener("load", Run, false);
}
else if (window.attachEvent)
{
	window.attachEvent("onload", DisableButton);
	window.attachEvent("onload", Run);
}
</script>';
	}
}

class Step4 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('WSL_STEP4_TITLE'));
		$this->SetNextStep("final");
		$this->SetStepID("step4");
		//$this->SetFinishStep("final");
	}

	function ShowStep()
	{
		unset($_SESSION["ZIP_POS"]);
		unset($_SESSION["LOC_POS"]);

		$wizard =& $this->GetWizard();
		$filename = $wizard->GetVar('locations_csv');
		$bLoadZIP = $wizard->GetVar('load_zip');
		$bSync = $wizard->GetVar('sync');
		$step_length = intval($wizard->GetVar('step_length'));
		$path = $wizard->package->path;

		$this->content = '';
		$this->content .= '<div style="padding: 10px 20px 10px;">';
		$this->content .= '<div id="progress" style="height: 20px; width: 500px;"></div>';
		$this->content .= '<div id="wait_message" style="display: none;"></div>';
		$this->content .= '<div id="output"><br /></div>';
		$this->content .= '</div>';
		$this->content .= '<script type="text/javascript" src="/bitrix/js/main/cphttprequest.js"></script>';
		$this->content .= '<script type="text/javascript" src="'.$path.'/js/import.js"></script>';
		$this->content .= '<script type="text/javascript">

var nextButtonID = "'.$wizard->GetNextButtonID().'";
var formID = "'.$wizard->GetFormName().'";
var ajaxMessages = {wait:\''.GetMessage('WSL_STEP4_LOADING').'\'};
var filename = "'.CUtil::JSEscape($filename).'";
var load_zip = "'.($bLoadZIP == 'Y' ? 'Y' : 'N').'";
var sync = "'.($bSync == 'Y' ? 'Y' : 'N').'";
var path = "'.CUtil::JSEscape($path).'";
var step_length = "'.$step_length.'";
var sessid = "'.bitrix_sessid().'";

if (window.addEventListener)
{
	window.addEventListener("load", Import, false);
	window.addEventListener("load", DisableButton, false);
}
else if (window.attachEvent)
{
	window.attachEvent("onload", Import);
	window.attachEvent("onload", DisableButton);
}
</script>';
	}

	function OnPostForm()
	{
		$wizard = &$this->GetWizard();

		if ($wizard->IsNextButtonClick())
		{
			$path = dirname(__FILE__);
			$path = mb_strtolower(str_replace("\\", '/', $path));

			$filename = $wizard->GetVar('locations_csv');
			$bLoadZIP = $wizard->GetVar('load_zip');

			if (file_exists($path.'/upload/'.$filename))
			{
				@unlink($path.'/upload/'.$filename);
			}

			if ($bLoadZIP == "Y" && file_exists($path.'/upload/zip_ussr.csv'))
			{
				@unlink($path.'/upload/zip_ussr.csv');
			}
		}
	}
}

class FinalStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('WSL_FINALSTEP_TITLE'));
		$this->SetStepID("final");
		$this->SetCancelStep("final");
		$this->SetCancelCaption(GetMessage('WSL_FINALSTEP_BUTTONTITLE'));
	}

	function ShowStep()
	{
		$this->content = GetMessage('WSL_FINALSTEP_CONTENT');
	}
}

class CancelStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('WSL_CANCELSTEP_TITLE'));
		$this->SetStepID("cancel");
		$this->SetCancelStep("cancel");
		$this->SetCancelCaption(GetMessage('WSL_CANCELSTEP_BUTTONTITLE'));
	}

	function ShowStep()
	{
		$this->content = GetMessage('WSL_CANCELSTEP_CONTENT');
	}
}
?>