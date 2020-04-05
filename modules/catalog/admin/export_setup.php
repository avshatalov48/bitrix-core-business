<?
/** @global CDatabase $DB */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
use Bitrix\Main\Loader;
define('NO_AGENT_CHECK', true);

$executeExport = (isset($_REQUEST['ACTION']) && is_string($_REQUEST['ACTION']) && $_REQUEST['ACTION'] == 'EXPORT');
$existActionFile = (isset($_REQUEST['ACT_FILE']) && is_string($_REQUEST['ACT_FILE']) && trim($_REQUEST['ACT_FILE']) !== '');
$existExportSession = false;
if (isset($_REQUEST['CUR_EXPORT_SESS_ID']) && is_string($_REQUEST['CUR_EXPORT_SESS_ID']))
{
	$exportSessionId = trim($_REQUEST['CUR_EXPORT_SESS_ID']);
	$existExportSession = ($exportSessionId !== '' && preg_match('/^CE\d+$/', $exportSessionId));
	unset($exportSessionId);
}
$listPosition = 0;
if (isset($_REQUEST['CUR_ELEMENT_ID']) && is_string($_REQUEST['CUR_ELEMENT_ID']))
	$listPosition = (int)$_REQUEST['CUR_ELEMENT_ID'];

if ($executeExport && $existActionFile && $existExportSession && $listPosition > 0)
{
	define('NO_KEEP_STATISTIC', true);
	define('STOP_STATISTICS', true);
}
unset($listPosition, $existExportSession, $existActionFile, $executeExport);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_export_edit') || $USER->CanDoOperation('catalog_export_exec')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
Loader::includeModule('catalog');
$bCanEdit = $USER->CanDoOperation('catalog_export_edit');
$bCanExec = $USER->CanDoOperation('catalog_export_exec');

IncludeModuleLangFile(__FILE__);

if ($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError($ex->GetString());
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$publicMode = $adminPage->publicMode;

set_time_limit(0);
$strErrorMessage = "";
$strOKMessage = "";

global $arCatalogAvailProdFields;
$arCatalogAvailProdFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_ELEMENT);
global $arCatalogAvailPriceFields;
$arCatalogAvailPriceFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_CATALOG);
global $arCatalogAvailValueFields;
$arCatalogAvailValueFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_PRICE);
global $arCatalogAvailQuantityFields;
$arCatalogAvailQuantityFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_PRICE_EXT);
global $arCatalogAvailGroupFields;
$arCatalogAvailGroupFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_SECTION);

global $defCatalogAvailProdFields;
$defCatalogAvailProdFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_ELEMENT);
global $defCatalogAvailPriceFields;
$defCatalogAvailPriceFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_CATALOG);
global $defCatalogAvailValueFields;
$defCatalogAvailValueFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_PRICE);
global $defCatalogAvailQuantityFields;
$defCatalogAvailQuantityFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_PRICE_EXT);
global $defCatalogAvailGroupFields;
$defCatalogAvailGroupFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_SECTION);
global $defCatalogAvailCurrencies;
$defCatalogAvailCurrencies = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_CURRENCY);

/////////////////////////////////////////////////////////////////////
function GetReportsList($strPath2Export)
{
	$arReports = array();

	CheckDirPath($_SERVER["DOCUMENT_ROOT"].$strPath2Export);
	if ($handle = opendir($_SERVER["DOCUMENT_ROOT"].$strPath2Export))
	{
		while (($file = readdir($handle)) !== false)
		{
			if ($file == "." || $file == "..") continue;
			if (is_file($_SERVER["DOCUMENT_ROOT"].$strPath2Export.$file) && substr($file, strlen($file)-8)=="_run.php")
			{
				$export_name = substr($file, 0, strlen($file)-8);

				$rep_title = $export_name;
				$file_handle = fopen($_SERVER["DOCUMENT_ROOT"].$strPath2Export.$file, "rb");
				$file_contents = fread($file_handle, 1500);
				fclose($file_handle);

				$arMatches = array();
				if (preg_match("#<title[\s]*>([^<]*)</title[\s]*>#i", $file_contents, $arMatches))
				{
					$arMatches[1] = Trim($arMatches[1]);
					if (strlen($arMatches[1])>0) $rep_title = $arMatches[1];
				}

				$arReports[$export_name] = array(
					"PATH" => $strPath2Export,
					"FILE_RUN" => $strPath2Export.$file,
					"TITLE" => $rep_title
					);
				if (file_exists($_SERVER["DOCUMENT_ROOT"].$strPath2Export.$export_name."_setup.php"))
				{
					$arReports[$export_name]["FILE_SETUP"] = $strPath2Export.$export_name."_setup.php";
				}
			}
		}
	}
	closedir($handle);

	return $arReports;
}

$arReportsList = GetReportsList(CATALOG_PATH2EXPORTS);

/////////////////////////////////////////////////////////////////////
// In the step by step wizard
//	$FINITE = true the last step
//	$SETUP_FIELDS_LIST  list of fields that are stored in the profile of exports, separated by commas
//	$STEP  current step of the wizard
//	$SETUP_PROFILE_NAME  name for the new profile must set in the wizard, if $ACTION=="EXPORT_SETUP"
//	$SETUP_FILE_NAME  If set the path to export the file name is shown after the upload. Can be relative or absolute
//	$strExportErrorMessage  string of errors after the export
/////////////////////////////////////////////////////////////////////

if (($bCanEdit || $bCanExec) && check_bitrix_sessid())
{
	$strActFileName = trim(strval($_REQUEST["ACT_FILE"]));
	if (strlen($_REQUEST["ACTION"])>0 && strlen($strActFileName)<=0)
	{
		$strErrorMessage .= GetMessage("CES_ERROR_NO_FILE")."\n";
	}
	elseif (strlen($_REQUEST["ACTION"])<=0 && strlen($strActFileName)>0)
	{
		$strErrorMessage .= GetMessage("CES_ERROR_NO_ACTION")."\n";
	}
	elseif (preg_match(BX_CATALOG_FILENAME_REG, $strActFileName))
	{
		$strErrorMessage .= GetMessage("CES_ERROR_BAD_FILENAME2")."\n";
	}

	if (strlen($strErrorMessage)<=0 && strlen($_REQUEST["ACTION"])>0)
	{
		if (!file_exists($_SERVER["DOCUMENT_ROOT"].$arReportsList[$strActFileName]["FILE_RUN"])
			|| !is_file($_SERVER["DOCUMENT_ROOT"].$arReportsList[$strActFileName]["FILE_RUN"])
			)
			$strErrorMessage .= GetMessage("CES_ERROR_FILE_NOT_EXIST")." (".$arReportsList[$strActFileName]["FILE_RUN"].").\n";

		if (strlen($strErrorMessage)<=0)
		{
			$PROFILE_ID = intval($_REQUEST["PROFILE_ID"]);

			//////////////////////////////////////////////
			// EXPORT
			//////////////////////////////////////////////
			if ($_REQUEST["ACTION"]=="EXPORT" && $bCanExec)
			{
				// if profile is default, than
				//		If there is a setup, then setup and run with the parameters in setup
				//		else run
				// Otherwise, the initialization parameters and run

				$CUR_EXPORT_SESS_ID = '';
				if (isset($_REQUEST['CUR_EXPORT_SESS_ID']) && is_string($_REQUEST['CUR_EXPORT_SESS_ID']))
				{
					$CUR_EXPORT_SESS_ID = trim($_REQUEST['CUR_EXPORT_SESS_ID']);
				}
				if ($CUR_EXPORT_SESS_ID !== '' && !preg_match('/^CE\d+$/', $CUR_EXPORT_SESS_ID))
				{
					$CUR_EXPORT_SESS_ID = '';
				}

				$CUR_ELEMENT_ID = 0;
				if (isset($_REQUEST['CUR_ELEMENT_ID']) && is_string($_REQUEST['CUR_ELEMENT_ID']))
				{
					$CUR_ELEMENT_ID = (int)$_REQUEST['CUR_ELEMENT_ID'];
				}
				if ($CUR_ELEMENT_ID < 0)
				{
					$CUR_ELEMENT_ID = 0;
				}

				if ($CUR_ELEMENT_ID > 0 && '' != $CUR_EXPORT_SESS_ID && isset($_SESSION[$CUR_EXPORT_SESS_ID]) && is_array($_SESSION[$CUR_EXPORT_SESS_ID]))
				{
					$firstStep = false;

					$arSetupVars = array();
					$intSetupVarsCount = 0;
					if (isset($_SESSION[$CUR_EXPORT_SESS_ID]["SETUP_VARS"]))
					{
						parse_str($_SESSION[$CUR_EXPORT_SESS_ID]["SETUP_VARS"], $arSetupVars);
						if (!empty($arSetupVars) && is_array($arSetupVars))
						{
							$intSetupVarsCount = extract($arSetupVars, EXTR_SKIP);
						}
					}

					$arInternalVars = array();
					$intInternalVarsCount = 0;
					if (isset($_SESSION[$CUR_EXPORT_SESS_ID]["INTERNAL_VARS"]))
					{
						$arInternalVars = $_SESSION[$CUR_EXPORT_SESS_ID]["INTERNAL_VARS"];
						if (!empty($arInternalVars) && is_array($arInternalVars))
						{
							$intInternalVarsCount = extract($arInternalVars, EXTR_SKIP);
						}
					}
				}
				else
				{
					$firstStep = true;

					$bDefaultProfile = true;
					$boolNeedEdit = false;
					if ($PROFILE_ID > 0)
					{
						$ar_profile = CCatalogExport::GetByID($PROFILE_ID);
						if ($ar_profile)
						{
							if ($ar_profile["DEFAULT_PROFILE"] != "Y")
								$bDefaultProfile = false;
							if ('Y' == $ar_profile["NEED_EDIT"])
								$boolNeedEdit = true;
						}
						else
						{
							$PROFILE_ID = 0;
						}
					}

					// if profile absent, search default profile
					if ($PROFILE_ID <= 0)
					{
						$db_profile = CCatalogExport::GetList(
							array(),
							array("DEFAULT_PROFILE" => "Y", "FILE_NAME" => $strActFileName)
						);
						if ($ar_profile = $db_profile->Fetch())
						{
							$PROFILE_ID = (int)$ar_profile['ID'];
							if ($ar_profile['NEED_EDIT'] == 'Y')
								$boolNeedEdit = true;
						}
					}

					if ($bDefaultProfile || $boolNeedEdit)
					{
						if (strlen($arReportsList[$strActFileName]["FILE_SETUP"]) > 0)
						{
							$STEP = intval($_REQUEST["STEP"]);
							if (isset($_POST['backButton']) && !empty($_POST['backButton'])) $STEP-=2;
							if ($STEP <= 0) $STEP = 1;
							$FINITE = false;

							ob_start();
							$APPLICATION->SetTitle($arReportsList[$strActFileName]["TITLE"]);
							include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

							// compatibility hack!
							$CATALOG_RIGHT = 'W';
							include($_SERVER["DOCUMENT_ROOT"].$arReportsList[$strActFileName]["FILE_SETUP"]);

							if ($FINITE!==true)
							{
								ob_end_flush();
								include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
								die();
							}
							ob_end_clean();
						}
					}
					else
					{
						$arSetupVars = array();
						$intSetupVarsCount = 0;

						parse_str($ar_profile["SETUP_VARS"], $arSetupVars);
						if (!empty($arSetupVars) && is_array($arSetupVars))
						{
							$intSetupVarsCount = extract($arSetupVars, EXTR_SKIP);
						}
					}

					if ('' != $CUR_EXPORT_SESS_ID && isset($_SESSION[$CUR_EXPORT_SESS_ID]))
					{
						unset($_SESSION[$CUR_EXPORT_SESS_ID]);
						unset($CUR_EXPORT_SESS_ID);
					}
				}

				$strExportErrorMessage = "";

				$finalExport = true;

				CCatalogDiscountSave::Disable();
				include($_SERVER["DOCUMENT_ROOT"].$arReportsList[$strActFileName]["FILE_RUN"]);
				CCatalogDiscountSave::Enable();

				if (!$finalExport)
				{
					if (empty($CUR_EXPORT_SESS_ID))
						$CUR_EXPORT_SESS_ID = "CE".time();

					$arInternalVars = array();
					$arInternalVarsList = array();
					if (isset($INTERNAL_VARS_LIST) && '' != $INTERNAL_VARS_LIST)
					{
						$arInternalVarsList = explode(",", $INTERNAL_VARS_LIST);
					}
					if (!empty($arInternalVarsList) && is_array($arInternalVarsList))
					{
						foreach ($arInternalVarsList as $strInternalVarName)
						{
							$strInternalVarName = trim($strInternalVarName);
							if (!empty($strInternalVarName) && isset($GLOBALS[$strInternalVarName]))
							{
								$arInternalVars[$strInternalVarName] = $GLOBALS[$strInternalVarName];
							}
						}
						unset($strInternalVarName);
					}

					$setupVars = "";
					$arSetupVars = array();
					$arSetupVarsList = array();
					if (isset($SETUP_VARS_LIST) && '' != $SETUP_VARS_LIST)
					{
						$arSetupVarsList = explode(",", $SETUP_VARS_LIST);
					}
					if (!empty($arSetupVarsList) && is_array($arSetupVarsList))
					{
						foreach ($arSetupVarsList as $strSetupVarName)
						{
							$strSetupVarName = trim($strSetupVarName);
							if (!empty($strSetupVarName) && isset($GLOBALS[$strSetupVarName]))
							{
								$arSetupVars[$strSetupVarName] = $GLOBALS[$strSetupVarName];
							}
						}
						unset($strSetupVarName);
						if (!empty($arSetupVars))
							$setupVars = http_build_query($arSetupVars);
					}

					$_SESSION[$CUR_EXPORT_SESS_ID]["CUR_ELEMENT_ID"] = $CUR_ELEMENT_ID;
					$_SESSION[$CUR_EXPORT_SESS_ID]["INTERNAL_VARS"] = $arInternalVars;
					$_SESSION[$CUR_EXPORT_SESS_ID]["SETUP_VARS"] = $setupVars;
					$_SESSION[$CUR_EXPORT_SESS_ID]["ERROR_MESSAGE"] .= $strExportErrorMessage;
					//$_SESSION[$CUR_EXPORT_SESS_ID]["OK_MESSAGE"] .= $strImportOKMessage;

					$urlParams = "CUR_ELEMENT_ID=".$CUR_ELEMENT_ID."&CUR_EXPORT_SESS_ID=".urlencode($CUR_EXPORT_SESS_ID)."&ACT_FILE=".urlencode($strActFileName)."&ACTION=EXPORT&PROFILE_ID=".$PROFILE_ID;
					$fullUrl = $APPLICATION->GetCurPage().'?lang='.LANGUAGE_ID.'&'.$urlParams.'&'.bitrix_sessid_get();
					?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title><?= GetMessage("CES_STEP_TITLE") ?></title>
</head>
<body>
<?echo GetMessage("CES_AUTO_REFRESH");?><br>
<a href="<?=$fullUrl; ?>"><?echo GetMessage("CES_AUTO_REFRESH_STEP");?></a><br>
<script type="text/javascript">
function DoNext()
{
	window.location="<?=$fullUrl; ?>";
}
setTimeout('DoNext()', 2000);
</script>
</body>
</html><?

					include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
					die();
				}
				else
				{
					if (isset($CUR_EXPORT_SESS_ID) && strlen($CUR_EXPORT_SESS_ID) > 0 && is_set($_SESSION, $CUR_EXPORT_SESS_ID))
					{
						$strExportErrorMessage = $_SESSION[$CUR_EXPORT_SESS_ID]["ERROR_MESSAGE"].$strExportErrorMessage;
						//$strImportOKMessage = $_SESSION[$CUR_EXPORT_SESS_ID]["OK_MESSAGE"].$strImportOKMessage;

						unset($_SESSION[$CUR_EXPORT_SESS_ID]);
						unset($CUR_EXPORT_SESS_ID);
					}
				}

				if (isset($CUR_EXPORT_SESS_ID) && strlen($CUR_EXPORT_SESS_ID) > 0 && is_set($_SESSION, $CUR_EXPORT_SESS_ID))
				{
					$strExportErrorMessage = $_SESSION[$CUR_EXPORT_SESS_ID]["ERROR_MESSAGE"].$strExportErrorMessage;
					//$strImportOKMessage = $_SESSION[$CUR_EXPORT_SESS_ID]["OK_MESSAGE"].$strImportOKMessage;
				}


				if (strlen($strExportErrorMessage) > 0)
					$strErrorMessage .= $strExportErrorMessage;

				if ($PROFILE_ID > 0)
				{
					CCatalogExport::Update(
						$PROFILE_ID,
						array(
							"=LAST_USE" => $DB->GetNowFunction(),
							'NEED_EDIT' => 'N',
						)
					);
				}
				else
				{
					$PROFILE_ID = CCatalogExport::Add(
						array(
							"=LAST_USE" => $DB->GetNowFunction(),
							"FILE_NAME" => $strActFileName,
							"NAME" => $arReportsList[$strActFileName]["TITLE"],
							"DEFAULT_PROFILE" => "Y",
							"IN_MENU" => "N",
							"IN_AGENT" => "N",
							"IN_CRON" => "N",
							'NEED_EDIT' => 'N',
							"SETUP_VARS" => false
						)
					);
				}

				if (strlen($strErrorMessage) <= 0)
				{
					$strSetupFileName = '';
					$strRedirect = '/bitrix/admin/cat_export_setup.php?lang='.urlencode(LANGUAGE_ID).'&success_export=Y';
					if (isset($SETUP_FILE_NAME) && !empty($SETUP_FILE_NAME))
					{
						if (preg_match(BX_CATALOG_FILENAME_REG,$SETUP_FILE_NAME))
						{
							$strErrorMessage .= GetMessage('CES_ERROR_BAD_EXPORT_FILENAME').'<br>';
						}
						else
						{
							$strSetupFileName = Rel2Abs('/',$SETUP_FILE_NAME);
							if (false !== $strSetupFileName)
							{
								if (substr($strSetupFileName, 0, strlen($_SERVER["DOCUMENT_ROOT"]))==$_SERVER["DOCUMENT_ROOT"])
								{
									$strSetupFileName = substr($strSetupFileName, strlen($_SERVER["DOCUMENT_ROOT"]));
								}
								if (file_exists($_SERVER['DOCUMENT_ROOT'].$strSetupFileName) && is_file($_SERVER['DOCUMENT_ROOT'].$strSetupFileName))
								{
									if ($APPLICATION->GetFileAccessPermission($strSetupFileName) >= "R")
									{
										if (!isset($_SESSION['BX_EXP_TMP_ID']) || !is_array($_SESSION['BX_EXP_TMP_ID']))
											$_SESSION['BX_EXP_TMP_ID'] = array();
										do
										{
											$strTempID = md5(str_replace('.','',uniqid('bxexp',true)));
										} while (in_array($strTempID, $_SESSION['BX_EXP_TMP_ID']));
										$_SESSION['BX_EXP_TMP_ID'][] = $strTempID;
										$_SESSION[$strTempID] = $strSetupFileName;
										$strRedirect .= '&export_id='.urlencode($strTempID);
									}
								}
							}
						}
					}
					if (strlen($strErrorMessage) <= 0)
					{
						$adminSidePanelHelper->reloadPage($strRedirect, "save");
						LocalRedirect($strRedirect);
					}
				}
			}
			//////////////////////////////////////////////
			// MENU
			//////////////////////////////////////////////
			elseif ($_REQUEST["ACTION"]=="MENU" && $bCanEdit)
			{
				if ($PROFILE_ID>0)
				{
					$ar_profile = CCatalogExport::GetByID($PROFILE_ID);
					if (!$ar_profile)
						$PROFILE_ID = 0;
				}

				// if profile absent, search default profile
				if ($PROFILE_ID <= 0)
				{
					$db_profile = CCatalogExport::GetList(array(), array("DEFAULT_PROFILE"=>"Y", "FILE_NAME"=>$strActFileName));
					if ($ar_profile = $db_profile->Fetch())
						$PROFILE_ID = (int)$ar_profile['ID'];
				}

				if ($PROFILE_ID > 0)
				{
					CCatalogExport::Update(
						$PROFILE_ID,
						array("IN_MENU" => ($ar_profile["IN_MENU"]=="Y" ? "N" : "Y"))
					);
				}
				else
				{
					$PROFILE_ID = CCatalogExport::Add(array(
						"LAST_USE"		=> false,
						"FILE_NAME"		=> $strActFileName,
						"NAME"			=> $arReportsList[$strActFileName]["TITLE"],
						"DEFAULT_PROFILE" => "Y",
						"IN_MENU"		=> "Y",
						"IN_AGENT"		=> "N",
						"IN_CRON"		=> "N",
						"NEED_EDIT"		=> "N",
						"SETUP_VARS"	=> false
						));
				}

				if (strlen($strErrorMessage)<=0)
				{
					$redirectUrl = "/bitrix/admin/cat_export_setup.php?lang=".urlencode(LANGUAGE_ID)."&success_export=Y";
					$adminSidePanelHelper->reloadPage($redirectUrl, "save");
					LocalRedirect($redirectUrl);
				}
			}
			//////////////////////////////////////////////
			// AGENT
			//////////////////////////////////////////////
			elseif ($_REQUEST["ACTION"]=="AGENT" && $USER->CanDoOperation('edit_php'))
			{
				$bDefaultProfile = true;
				$boolNeedEdit = false;
				if ($PROFILE_ID>0)
				{
					$ar_profile = CCatalogExport::GetByID($PROFILE_ID);
					if ($ar_profile)
					{
						if ($ar_profile["DEFAULT_PROFILE"] != "Y")
							$bDefaultProfile = false;
						if ($ar_profile['NEED_EDIT'] == 'Y')
							$boolNeedEdit = true;
					}
					else
					{
						$PROFILE_ID = 0;
					}
				}

				// if profile absent, search default profile
				if ($PROFILE_ID<=0)
				{
					$db_profile = CCatalogExport::GetList(array(), array("DEFAULT_PROFILE"=>"Y", "FILE_NAME"=>$strActFileName));
					if ($ar_profile = $db_profile->Fetch())
					{
						$PROFILE_ID = (int)$ar_profile['ID'];
						if ($ar_profile['NEED_EDIT'] == 'Y')
							$boolNeedEdit = true;
					}
				}

				if (($bDefaultProfile && strlen($arReportsList[$strActFileName]["FILE_SETUP"])>0) || $boolNeedEdit)
				{
					$strErrorMessage .= GetMessage("CES_ERROR_NOT_AGENT")."\n";
				}

				if (strlen($strErrorMessage)<=0)
				{
					$agent_period = intval($_REQUEST["agent_period"]);
					if ($agent_period<=0) $agent_period = 24;

					if ($PROFILE_ID>0)
					{
						if ($ar_profile["IN_AGENT"]=="Y")
							CAgent::RemoveAgent("CCatalogExport::PreGenerateExport(".$PROFILE_ID.");", "catalog");
						else
							CAgent::AddAgent("CCatalogExport::PreGenerateExport(".$PROFILE_ID.");", "catalog", "N", $agent_period*60*60, "", "Y");

						CCatalogExport::Update($PROFILE_ID, array(
							"IN_AGENT" => ($ar_profile["IN_AGENT"]=="Y" ? "N" : "Y")
							));
					}
					else
					{
						$PROFILE_ID = CCatalogExport::Add(array(
							"LAST_USE"		=> false,
							"FILE_NAME"		=> $strActFileName,
							"NAME"			=> $arReportsList[$strActFileName]["TITLE"],
							"DEFAULT_PROFILE" => "Y",
							"IN_MENU"		=> "N",
							"IN_AGENT"		=> "Y",
							"IN_CRON"		=> "N",
							"NEED_EDIT"		=> "N",
							"SETUP_VARS"	=> false
							));
						if ((int)$PROFILE_ID > 0)
						{
							CAgent::AddAgent("CCatalogExport::PreGenerateExport(".$PROFILE_ID.");", "catalog", "N", $agent_period*60*60, "", "Y");
						}
						else
						{
							$strErrorMessage .= GetMessage("CES_ERROR_ADD_PROFILE")."\n";
						}
					}
				}

				if (strlen($strErrorMessage)<=0)
				{
					$redirectUrl = "/bitrix/admin/cat_export_setup.php?lang=".urlencode(LANGUAGE_ID)."&success_export=Y";
					$adminSidePanelHelper->reloadPage($redirectUrl, "save");
					LocalRedirect($redirectUrl);
				}
			}
			//////////////////////////////////////////////
			// CRON
			//////////////////////////////////////////////
			elseif ($_REQUEST["ACTION"]=="CRON" && $USER->CanDoOperation('edit_php'))
			{
				$bDefaultProfile = true;
				$boolNeedEdit = false;
				if ($PROFILE_ID>0)
				{
					$ar_profile = CCatalogExport::GetByID($PROFILE_ID);
					if ($ar_profile)
					{
						if ($ar_profile["DEFAULT_PROFILE"] != "Y")
							$bDefaultProfile = false;
						if ($ar_profile['NEED_EDIT'] == 'Y')
							$boolNeedEdit = true;
					}
					else
					{
						$PROFILE_ID = 0;
					}
				}

				// if profile absent, search default profile
				if ($PROFILE_ID<=0)
				{
					$db_profile = CCatalogExport::GetList(array(), array("DEFAULT_PROFILE"=>"Y", "FILE_NAME"=>$strActFileName));
					if ($ar_profile = $db_profile->Fetch())
					{
						$PROFILE_ID = (int)$ar_profile['ID'];
						if ($ar_profile['NEED_EDIT'] == 'Y')
							$boolNeedEdit = true;
					}
				}

				if (($bDefaultProfile && strlen($arReportsList[$strActFileName]["FILE_SETUP"])>0) || $boolNeedEdit)
				{
					$strErrorMessage .= GetMessage("CES_ERROR_NOT_CRON")."\n";
				}

				if (strlen($strErrorMessage)<=0)
				{
					$agent_period = intval($_REQUEST["agent_period"]);
					$agent_hour = trim($_REQUEST["agent_hour"]);
					$agent_minute = trim($_REQUEST["agent_minute"]);

					if ($agent_period<=0 && (strlen($agent_hour)<=0 || strlen($agent_minute)<=0))
					{
						$agent_period = 24;
						$agent_hour = "";
						$agent_minute = "";
					}
					elseif ($agent_period>0 && strlen($agent_hour)>0 && strlen($agent_minute)>0)
					{
						$agent_period = 0;
					}

					$agent_php_path = trim($_REQUEST["agent_php_path"]);
					if (strlen($agent_php_path)<=0) $agent_php_path = "/usr/local/php/bin/php";

					if (!file_exists($_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS."cron_frame.php"))
					{
						CheckDirPath($_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS);
						$tmp_file_size = filesize($_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS_DEF."cron_frame.php");
						$fp = fopen($_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS_DEF."cron_frame.php", "rb");
						$tmp_data = fread($fp, $tmp_file_size);
						fclose($fp);

						$tmp_data = str_replace("#DOCUMENT_ROOT#", $_SERVER["DOCUMENT_ROOT"], $tmp_data);
						$tmp_data = str_replace("#PHP_PATH#", $agent_php_path, $tmp_data);

						$fp = fopen($_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS."cron_frame.php", "ab");
						fwrite($fp, $tmp_data);
						fclose($fp);
					}

					$cfg_data = "";
					if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg"))
					{
						$cfg_file_size = filesize($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg");
						$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg", "rb");
						$cfg_data = fread($fp, $cfg_file_size);
						fclose($fp);
					}

					CheckDirPath($_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS."logs/");
					if ($PROFILE_ID>0)
					{
						if ($ar_profile["IN_CRON"]=="Y")
						{
							// remove
							$cfg_data = preg_replace("#^.*?".preg_quote(CATALOG_PATH2EXPORTS)."cron_frame.php +".$PROFILE_ID." *>.*?$#im", "", $cfg_data);
						}
						else
						{
							if ($agent_period>0)
							{
								//$strTime = "* */".$agent_period." * * * ";
								$strTime = "0 */".$agent_period." * * * ";
							}
							else
							{
								$strTime = intval($agent_minute)." ".intval($agent_hour)." * * * ";
							}

							// add
							if (strlen($cfg_data)>0) $cfg_data .= "\n";
							$cfg_data .= $strTime.$agent_php_path." -f ".$_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS."cron_frame.php ".$PROFILE_ID." >".$_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS."logs/".$PROFILE_ID.".txt\n";
						}

						CCatalogExport::Update($PROFILE_ID, array(
							"IN_CRON" => ($ar_profile["IN_CRON"]=="Y" ? "N" : "Y")
							));
					}
					else
					{
						$PROFILE_ID = CCatalogExport::Add(array(
							"LAST_USE"		=> false,
							"FILE_NAME"		=> $strActFileName,
							"NAME"			=> $arReportsList[$strActFileName]["TITLE"],
							"DEFAULT_PROFILE" => "Y",
							"IN_MENU"		=> "N",
							"IN_AGENT"		=> "N",
							"IN_CRON"		=> "Y",
							"NEED_EDIT"		=> 'N',
							"SETUP_VARS"	=> false
							));
						if ((int)$PROFILE_ID > 0)
						{
							// add
							if ($agent_period>0)
							{
								//$strTime = "* */".$agent_period." * * * ";
								$strTime = "0 */".$agent_period." * * * ";
							}
							else
							{
								$strTime = intval($agent_minute)." ".intval($agent_hour)." * * * ";
							}

							if (strlen($cfg_data)>0) $cfg_data .= "\n";
							$cfg_data .= $strTime.$agent_php_path." -f ".$_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS."cron_frame.php ".$PROFILE_ID." >".$_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS."logs/".$PROFILE_ID.".txt\n";
						}
						else
						{
							$strErrorMessage .= GetMessage("CES_ERROR_ADD_PROFILE")."\n";
						}
					}
					if (strlen($strErrorMessage)<=0)
					{
						CheckDirPath($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/");
						$cfg_data = preg_replace("#[\r\n]{2,}#im", "\n", $cfg_data);
						$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg", "wb");
						fwrite($fp, $cfg_data);
						fclose($fp);

						if ($_REQUEST["auto_cron_tasks"]=="Y")
						{
							$arRetval = array();
							@exec("crontab ".$_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg", $arRetval, $return_var);
							if (intval($return_var)!=0)
							{
								$strErrorMessage .= GetMessage("CES_ERROR_ADD2CRON")." \n";
								if (is_array($arRetval) && !empty($arRetval))
								{
									$strErrorMessage .= implode("\n", $arRetval)."\n";
								}
								else
								{
									$strErrorMessage .= GetMessage("CES_ERROR_UNKNOWN")."\n";
								}
							}
						}
					}
				}

				if (strlen($strErrorMessage)<=0)
				{
					$redirectUrl = "/bitrix/admin/cat_export_setup.php?lang=".urlencode(LANGUAGE_ID)."&success_export=Y";
					$adminSidePanelHelper->reloadPage($redirectUrl, "save");
					LocalRedirect($redirectUrl);
				}
			}
			//////////////////////////////////////////////
			// DEL_PROFILE
			//////////////////////////////////////////////
			elseif ($_REQUEST["ACTION"]=="DEL_PROFILE" && $bCanEdit)
			{
				$ar_profile = CCatalogExport::GetByID($PROFILE_ID);
				if (!$ar_profile)
					$strErrorMessage .= GetMessage("CES_ERROR_NO_PROFILE1").$PROFILE_ID." ".GetMessage("CES_ERROR_NO_PROFILE2")."\n";

				if (strlen($strErrorMessage)<=0)
				{
					if ($ar_profile["IN_AGENT"]=="Y")
					{
						CAgent::RemoveAgent("CCatalogExport::PreGenerateExport(".$PROFILE_ID.");", "catalog");
					}
					if ($ar_profile["IN_CRON"]=="Y")
					{
						$cfg_data = "";
						if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg"))
						{
							$cfg_file_size = filesize($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg");
							$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg", "rb");
							$cfg_data = fread($fp, $cfg_file_size);
							fclose($fp);

							$cfg_data = preg_replace("#^.*?".preg_quote(CATALOG_PATH2EXPORTS)."cron_frame.php +".$PROFILE_ID." *>.*?$#im", "", $cfg_data);

							$cfg_data = preg_replace("#[\r\n]{2,}#im", "\n", $cfg_data);
							$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg", "wb");
							fwrite($fp, $cfg_data);
							fclose($fp);

							$arRetval = array();
							@exec("crontab ".$_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg", $arRetval, $return_var);
							if (intval($return_var)!=0)
							{
								$strErrorMessage .= GetMessage("CES_ERROR_ADD2CRON")." \n";
								if (is_array($arRetval) && !empty($arRetval))
								{
									$strErrorMessage .= implode("\n", $arRetval)."\n";
								}
								else
								{
									$strErrorMessage .= GetMessage("CES_ERROR_UNKNOWN")."\n";
								}
							}
						}
					}
					CCatalogExport::Delete($PROFILE_ID);
				}
			}
			//////////////////////////////////////////////
			// EXPORT_SETUP
			//////////////////////////////////////////////
			elseif ($_REQUEST["ACTION"]=="EXPORT_SETUP" && $bCanEdit)
			{
				if (strlen($arReportsList[$strActFileName]["FILE_SETUP"])>0)
				{
					$STEP = intval($_REQUEST["STEP"]);
					if (isset($_POST['backButton']) && !empty($_POST['backButton'])) $STEP-=2;
					if ($STEP<=0) $STEP = 1;
					$FINITE = false;

					ob_start();
					$APPLICATION->SetTitle($arReportsList[$strActFileName]["TITLE"]);
					// compatibility hack!
					$CATALOG_RIGHT = 'W';
					include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
					include($_SERVER["DOCUMENT_ROOT"].$arReportsList[$strActFileName]["FILE_SETUP"]);

					if ($FINITE!==true)
					{
						ob_end_flush();
						include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
						die();
					}
					ob_end_clean();

					// Save profile
					if (strlen($SETUP_FIELDS_LIST)<=0) $SETUP_FIELDS_LIST = $_REQUEST["SETUP_FIELDS_LIST"];
					$arProfileFields = explode(",", $SETUP_FIELDS_LIST);
					$strSETUP_VARS = "";
					for ($i = 0, $intCount = count($arProfileFields); $i < $intCount; $i++)
					{
						$arProfileFields[$i] = Trim($arProfileFields[$i]);

						$vValue = ${$arProfileFields[$i]};
						if (!is_array($vValue) && strlen($vValue)<=0) $vValue = $_REQUEST[$arProfileFields[$i]];

						if (is_array($vValue))
						{
							foreach ($vValue as $key1 => $value1)
							{
								if (strlen($strSETUP_VARS)>0) $strSETUP_VARS .= "&";
								$strSETUP_VARS .= $arProfileFields[$i]."[".(is_numeric($key1)?"":"\"").$key1.(is_numeric($key1)?"":"\"")."]=".urlencode($value1);
							}
						}
						else
						{
							if (strlen($strSETUP_VARS)>0) $strSETUP_VARS .= "&";
							$strSETUP_VARS .= $arProfileFields[$i]."=".urlencode($vValue);
						}
					}

					if (strlen($SETUP_PROFILE_NAME)<=0) $SETUP_PROFILE_NAME = $_REQUEST["SETUP_PROFILE_NAME"];
					if (strlen($SETUP_PROFILE_NAME)<=0) $SETUP_PROFILE_NAME = $arReportsList[$strActFileName]["TITLE"];

					$PROFILE_ID = CCatalogExport::Add(array(
						"LAST_USE"		=> false,
						"FILE_NAME"		=> $strActFileName,
						"NAME"			=> $SETUP_PROFILE_NAME,
						"DEFAULT_PROFILE" => "N",
						"IN_MENU"		=> "N",
						"IN_AGENT"		=> "N",
						"IN_CRON"		=> "N",
						"NEED_EDIT"		=> "N",
						"SETUP_VARS"	=> $strSETUP_VARS
						));

					if ((int)$PROFILE_ID <= 0)
					{
						$strErrorMessage .= GetMessage("CES_ERROR_SAVE_PROFILE")."\n";
					}
				}
				else
				{
					$strErrorMessage .= GetMessage("CES_ERROR_NO_SETUP_FILE")."\n";
				}
				if (strlen($strErrorMessage)<=0)
				{
					$redirectUrl = "/bitrix/admin/cat_export_setup.php?lang=".urlencode(LANGUAGE_ID)."&success_export=Y";
					$adminSidePanelHelper->reloadPage($redirectUrl, "save");
					LocalRedirect($redirectUrl);
				}
			}
			//////////////////////////////////////////////
			// EXPORT_EDIT & EXPORT_COPY
			//////////////////////////////////////////////
			elseif (($_REQUEST["ACTION"]=="EXPORT_EDIT" || $_REQUEST['ACTION'] == 'EXPORT_COPY') && $bCanEdit)
			{
				$boolFlag = true;
				if (!isset($_REQUEST['PROFILE_ID']))
				{
					$strErrorMessage .= GetMessage('CES_EDIT_PROFILE_ERR_ID_ABSENT')."\n";
					$boolFlag = false;
				}
				else
				{
					$PROFILE_ID = intval($_REQUEST['PROFILE_ID']);
					if ($PROFILE_ID <= 0)
					{
						$strErrorMessage .= GetMessage('CES_EDIT_PROFILE_ERR_ID_ABSENT')."\n";
						$boolFlag = false;
					}
				}
				if ($boolFlag)
				{
					$arProfile = CCatalogExport::GetByID($PROFILE_ID);
					if ($arProfile)
					{
						if ($arProfile["DEFAULT_PROFILE"] == "Y")
						{
							$strErrorMessage .= ($_REQUEST["ACTION"]=="EXPORT_EDIT" ? GetMessage('CES_EDIT_PROFILE_ERR_DEFAULT') : GetMessage('CES_COPY_PROFILE_ERR_DEFAULT'))."\n";
							$boolFlag = false;
						}
					}
					else
					{
						$strErrorMessage .= ($_REQUEST["ACTION"]=="EXPORT_EDIT" ? GetMessage('CES_EDIT_PROFILE_ERR_DEFAULT') : GetMessage('CES_COPY_PROFILE_ERR_DEFAULT'))."\n";
						$boolFlag = false;
					}
				}

				if ($boolFlag)
				{
					if (strlen($arReportsList[$arProfile['FILE_NAME']]["FILE_SETUP"])>0)
					{
						$STEP = intval($_REQUEST["STEP"]);
						if (isset($_POST['backButton']) && !empty($_POST['backButton'])) $STEP-=2;
						if ($STEP<=0) $STEP = 1;
						$FINITE = false;

						$arOldSetupVars = array();
						if ($arProfile['SETUP_VARS'])
							parse_str($arProfile['SETUP_VARS'],$arOldSetupVars);
						$arOldSetupVars['SETUP_PROFILE_NAME'] = $arProfile['NAME'];
						$_REQUEST['OLD_SETUP_VARS'] = $arOldSetupVars;

						ob_start();
						$APPLICATION->SetTitle($arReportsList[$strActFileName]["TITLE"]);

						// compatibility hack!
						$CATALOG_RIGHT = 'W';
						include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

						include($_SERVER["DOCUMENT_ROOT"].$arReportsList[$strActFileName]["FILE_SETUP"]);

						if ($FINITE!==true)
						{
							ob_end_flush();
							include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
							die();
						}
						ob_end_clean();

						// Save profile
						if (strlen($SETUP_FIELDS_LIST)<=0) $SETUP_FIELDS_LIST = $_REQUEST["SETUP_FIELDS_LIST"];
						$arProfileFields = explode(",", $SETUP_FIELDS_LIST);
						$strSETUP_VARS = "";
						for ($i = 0, $intCount = count($arProfileFields); $i < $intCount; $i++)
						{
							$arProfileFields[$i] = Trim($arProfileFields[$i]);

							$vValue = ${$arProfileFields[$i]};
							if (!is_array($vValue) && strlen($vValue)<=0) $vValue = $_REQUEST[$arProfileFields[$i]];

							if (is_array($vValue))
							{
								foreach ($vValue as $key1 => $value1)
								{
									if (strlen($strSETUP_VARS)>0) $strSETUP_VARS .= "&";
									$strSETUP_VARS .= $arProfileFields[$i]."[".(is_numeric($key1)?"":"\"").$key1.(is_numeric($key1)?"":"\"")."]=".urlencode($value1);
								}
							}
							else
							{
								if (strlen($strSETUP_VARS)>0) $strSETUP_VARS .= "&";
								$strSETUP_VARS .= $arProfileFields[$i]."=".urlencode($vValue);
							}
						}

						if (strlen($SETUP_PROFILE_NAME)<=0) $SETUP_PROFILE_NAME = $_REQUEST["SETUP_PROFILE_NAME"];
						if (strlen($SETUP_PROFILE_NAME)<=0) $SETUP_PROFILE_NAME = $arReportsList[$strActFileName]["TITLE"];

						if ($_REQUEST["ACTION"]=="EXPORT_EDIT")
						{
							$NEW_PROFILE_ID = CCatalogExport::Update($PROFILE_ID,array(
								"NAME"			=> $SETUP_PROFILE_NAME,
								"SETUP_VARS"	=> $strSETUP_VARS,
								"NEED_EDIT"		=> "N",
							));
							if ($NEW_PROFILE_ID != $PROFILE_ID)
							{
								$strErrorMessage .= GetMessage("CES_ERROR_PROFILE_UPDATE")."\n";
							}
						}
						elseif ($_REQUEST["ACTION"]=="EXPORT_COPY")
						{
							$NEW_PROFILE_ID = CCatalogExport::Add(array(
								"LAST_USE"		=> false,
								"FILE_NAME"		=> $strActFileName,
								"NAME"			=> $SETUP_PROFILE_NAME,
								"DEFAULT_PROFILE" => "N",
								"IN_MENU"		=> "N",
								"IN_AGENT"		=> "N",
								"IN_CRON"		=> "N",
								"NEED_EDIT"		=> "N",
								"SETUP_VARS"	=> $strSETUP_VARS
							));
							if ((int)$PROFILE_ID <= 0)
							{
								$strErrorMessage .= GetMessage("CES_ERROR_COPY_PROFILE")."\n";
							}
						}
					}
					else
					{
						$strErrorMessage .= GetMessage("CES_ERROR_NO_SETUP_FILE")."\n";
					}
				}
				if (strlen($strErrorMessage)<=0)
				{
					$redirectUrl = "/bitrix/admin/cat_export_setup.php?lang=".urlencode(LANGUAGE_ID)."&success_export=Y";
					$adminSidePanelHelper->reloadPage($redirectUrl, "save");
					LocalRedirect($redirectUrl);
				}
			}
		}
	}
}
/////////////////////////////////////////////////////////////////////

// Set NEW_OS in GET string to test other operational systems!!!
$bWindowsHosting = false;
if (isset($_GET["NEW_OS"]))
{
	if (strlen(trim($_GET["NEW_OS"]))<=0)
		unset($_SESSION["TMP_MY_NEW_OS"]);
	else
		$_SESSION["TMP_MY_NEW_OS"] = $_GET["NEW_OS"];
}
$strCurrentOS = PHP_OS;
if (isset($_SESSION["TMP_MY_NEW_OS"]) && strlen($_SESSION["TMP_MY_NEW_OS"])>0)
	$strCurrentOS = $_SESSION["TMP_MY_NEW_OS"];
if (strtoupper(substr($strCurrentOS, 0, 3)) === "WIN")
{
	$bWindowsHosting = true;
}

$sTableID = "export_setup";

$lAdmin = new CAdminUiList($sTableID);

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "align" => "right", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("export_setup_name"), "default"=>true),
	array("id"=>"FILE", "content"=>GetMessage("export_setup_file"), "default"=>true),
	array("id"=>"PROFILE", "content"=>GetMessage("CES_PROFILE"), "default"=>true),
	array("id"=>"IN_MENU", "content"=>GetMessage("CES_IN_MENU"), "default"=>true),
	array("id"=>"IN_AGENT", "content"=>GetMessage("CES_IN_AGENT"), "default"=>true),
	array("id"=>"IN_CRON", "content"=>GetMessage("CES_IN_CRON"), "default"=>true),
	array("id"=>"USED", "content"=>GetMessage("CES_USED"), "default"=>true),
	array('id' => 'CREATED_BY', 'content' => GetMessage('CES_CREATED_BY'), 'default' => false),
	array('id' => 'DATE_CREATE', 'content' => GetMessage('CES_DATE_CREATE'), 'default' => false),
	array('id' => 'MODIFIED_BY', 'content' => GetMessage('CES_MODIFIED_BY'), 'default' => true),
	array('id' => 'TIMESTAMP_X', 'content' => GetMessage('CES_TIMESTAMP_X'), 'default' => true),
));

$arUserList = array();
$strNameFormat = CSite::GetNameFormat(true);

$arContextMenu = array();
$cronErrors = array();

foreach ($arReportsList as $strReportFile => $arReportParams)
{
	if ($bCanEdit && !empty($arReportParams["FILE_SETUP"]))
	{
		$arContextMenu[] = array(
			"TEXT" => htmlspecialcharsbx($arReportParams["TITLE"]),
			"TITLE" => GetMessage("export_setup_script").' "'.$strReportFile.'"',
			"LINK"=>"/bitrix/admin/cat_export_setup.php?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&ACTION=EXPORT_SETUP"."&".bitrix_sessid_get()
		);
	}

	$boolExist = false;
	$rsProfiles = CCatalogExport::GetList(
		array("LAST_USE" => "DESC", "NAME" => "ASC"),
		array("FILE_NAME" => $strReportFile, 'DEFAULT_PROFILE' => 'Y')
	);

	while ($arProfile = $rsProfiles->Fetch())
	{
		if ($arProfile['IN_AGENT'] == 'Y' && $arProfile['IN_CRON'] == 'Y')
			$cronErrors[] = '['.$arProfile['ID'].'] '.$arReportParams['TITLE'];

		$arProfile['USED'] = $arProfile['LAST_USE_FORMAT'];
		$boolExist = true;
		$boolNeedEdit = (isset($arProfile['NEED_EDIT']) && 'Y' == $arProfile['NEED_EDIT']);

		$row = &$lAdmin->AddRow($arProfile['ID'], $arProfile);

		$row->AddViewField('ID', $arProfile['ID']);
		$row->AddViewField("NAME", htmlspecialcharsbx($arReportParams["TITLE"]));
		$row->AddViewField("FILE", $strReportFile);

		$strProfileLink = '';
		if ($bCanEdit)
		{
			if ($boolNeedEdit)
			{
				$url = "/bitrix/admin/cat_export_setup.php?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&ACTION=EXPORT_EDIT&PROFILE_ID=".$arProfile["ID"]."&".bitrix_sessid_get();
				$strProfileLink = '<a href="'.CHTTP::URN2URI($url).'" title="'.GetMessage("CES_EDIT_PROPFILE_DESCR").'"><i>'.GetMessage("CES_DEFAULT").'</i></a><br /><i>('.GetMessage('CES_NEED_EDIT').')</i>';
			}
			else
			{
				$url = ('Y' == $arProfile["IN_MENU"] ? '/bitrix/admin/cat_exec_exp.php' : "/bitrix/admin/cat_export_setup.php").'?lang='.LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&ACTION=EXPORT&PROFILE_ID=".$arProfile["ID"]."&".bitrix_sessid_get();
				$strProfileLink = '<a href="'.CHTTP::URN2URI($url).'" title="'.GetMessage("export_setup_begin").'"><i>'.GetMessage("CES_DEFAULT").'</i></a>';
			}
		}
		else
		{
			$strProfileLink = '<i>'.GetMessage("CES_DEFAULT").'</i>';
		}
		$row->AddViewField('PROFILE', $strProfileLink);

		$row->AddCheckField("IN_MENU", false);
		$row->AddCheckField("IN_AGENT", false);
		$row->AddCheckField("IN_CRON", false);

		$row->AddCalendarField("USED", false);

		$strModifiedBy = '';
		$arProfile['MODIFIED_BY'] = (int)$arProfile['MODIFIED_BY'];
		if (0 < $arProfile['MODIFIED_BY'])
		{
			if (!isset($arUserList[$arProfile['MODIFIED_BY']]))
			{
				$byUser = 'ID';
				$byOrder = 'ASC';
				$rsUsers = CUser::GetList(
					$byUser,
					$byOrder,
					array('ID_EQUAL_EXACT' => $arProfile['MODIFIED_BY']),
					array('FIELDS' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'))
				);
				if ($arOneUser = $rsUsers->Fetch())
				{
					$arOneUser['ID'] = (int)$arOneUser['ID'];
					if ($publicMode)
					{
						$arUserList[$arOneUser['ID']] = CUser::FormatName($strNameFormat, $arOneUser);
					}
					else
					{
						$arUserList[$arOneUser['ID']] = '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$arProfile['MODIFIED_BY'].'">'.CUser::FormatName($strNameFormat, $arOneUser).'</a>';
					}
				}
			}
			if (isset($arUserList[$arProfile['MODIFIED_BY']]))
				$strModifiedBy = $arUserList[$arProfile['MODIFIED_BY']];
		}

		$row->AddViewField("CREATED_BY", '');
		$row->AddViewField("DATE_CREATE", '');
		$row->AddViewField("MODIFIED_BY", $strModifiedBy);
		$row->AddCalendarField("TIMESTAMP_X", false);

		$arActions = array();

		if ($bCanExec)
		{
			$arActions[] = array(
				"DEFAULT"=>true,
				"TEXT"=>GetMessage("CES_RUN_EXPORT"),
				"TITLE"=>GetMessage("CES_RUN_EXPORT_DESCR"),
				"ACTION"=>$lAdmin->ActionRedirect("/bitrix/admin/cat_export_setup.php?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&".bitrix_sessid_get()."&ACTION=EXPORT&PROFILE_ID=".$arProfile['ID']),
			);
			$arActions[] = array(
				"TEXT" => GetMessage('CES_ADD_PROFILE'),
				"TITLE" => GetMessage('CES_ADD_PROFILE_DESCR'),
				"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/cat_export_setup.php?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&ACTION=EXPORT_SETUP"."&".bitrix_sessid_get()),
			);
		}
		if ($bCanEdit || $bCanExec)
			$arActions[] = array("SEPARATOR"=>true);

		if ($bCanEdit && !$publicMode)
		{
			if ('Y' == $arProfile["IN_MENU"])
			{
				$arActions[] = array(
					"TEXT" => GetMessage("CES_TO_LEFT_MENU_DEL"),
					"TITLE" => GetMessage("CES_TO_LEFT_MENU_DESCR_DEL"),
					"ACTION" => $lAdmin->ActionRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&".bitrix_sessid_get()."&ACTION=MENU&PROFILE_ID=".$arProfile['ID']),
				);
			}
			else
			{
				$arActions[] = array(
					"TEXT" => GetMessage("CES_TO_LEFT_MENU"),
					"TITLE" => GetMessage("CES_TO_LEFT_MENU_DESCR"),
					"ACTION" => $lAdmin->ActionRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&".bitrix_sessid_get()."&ACTION=MENU&PROFILE_ID=".$arProfile['ID']),
				);
			}
		}

		if (empty($arReportParams["FILE_SETUP"]) && 'Y' != $arProfile["NEED_EDIT"] && $USER->CanDoOperation('edit_php'))
		{
			if ('Y' == $arProfile["IN_AGENT"])
			{
				$arActions[] = array(
					"TEXT" => GetMessage("CES_TO_AGENT_DEL"),
					"TITLE" => GetMessage("CES_TO_AGENT_DESCR_DEL"),
					"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/cat_export_setup.php?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($ReportFile)."&".bitrix_sessid_get()."&ACTION=AGENT&PROFILE_ID=".$ar_prof_res["ID"]),
				);
			}
			else
			{
				$arActions[] = array(
					"TEXT" => GetMessage("CES_TO_AGENT"),
					"TITLE" => GetMessage("CES_TO_AGENT_DESCR"),
					"ACTION" => "ShowAgentForm('".$APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ACT_FILE=".$strReportFile."&".bitrix_sessid_get()."&ACTION=AGENT&PROFILE_ID=".$arProfile["ID"]."');",
				);
			}

			if ('Y' == $arProfile['IN_CRON'])
			{
				$arActions[] = array(
					"DISABLED" => $bWindowsHosting,
					"TEXT" => GetMessage("CES_TO_CRON_DEL"),
					"TITLE" => GetMessage("CES_TO_CRON_DESCR_DEL"),
					"ACTION" => ($bWindowsHosting ? '' : "ShowCronForm('".$APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ACT_FILE=".$strReportFile."&".bitrix_sessid_get()."&ACTION=CRON&PROFILE_ID=".$arProfile["ID"]."', false);"),
				);
			}
			else
			{
				$arActions[] = array(
					"DISABLED" => $bWindowsHosting,
					"TEXT" => GetMessage("CES_TO_CRON"),
					"TITLE" => GetMessage("CES_TO_CRON_DESCR"),
					"ACTION" => ($bWindowsHosting ? '' : "ShowCronForm('".$APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ACT_FILE=".$strReportFile."&".bitrix_sessid_get()."&ACTION=CRON&PROFILE_ID=".$arProfile["ID"]."', true);"),
				);
			}
		}

		$row->AddActions($arActions);
	}

	if (!$boolExist)
	{
		$row = &$lAdmin->AddRow(0, null);

		$row->AddViewField('ID', ' ');
		$row->AddViewField("NAME", htmlspecialcharsbx($arReportParams["TITLE"]));
		$row->AddViewField("FILE", $strReportFile);

		$strProfileLink = '<i>'.GetMessage("CES_DEFAULT").'</i>';
		if ($bCanEdit)
		{
			$url = "/bitrix/admin/cat_export_setup.php?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&".bitrix_sessid_get()."&ACTION=EXPORT&PROFILE_ID=0";
			$strProfileLink = '<a href="'.CHTTP::URN2URI($url).'" title="'.GetMessage("export_setup_begin").'"><i>'.GetMessage("CES_DEFAULT").'</i></a>';
		}
		$row->AddViewField('PROFILE', $strProfileLink);

		$row->AddViewField("IN_MENU", GetMessage("CES_NO"));
		$row->AddViewField("IN_AGENT", GetMessage("CES_NO"));
		$row->AddViewField("IN_CRON", GetMessage("CES_NO"));

		$row->AddViewField("USED", '');

		$row->AddViewField("CREATED_BY", '');
		$row->AddViewField("DATE_CREATE", '');
		$row->AddViewField("MODIFIED_BY", '');
		$row->AddViewField("TIMESTAMP_X", '');

		$arActions = array();

		if ($bCanExec)
		{
			$arActions[] = array(
				"DEFAULT" => true,
				"TEXT" => GetMessage("CES_RUN_EXPORT"),
				"TITLE" => GetMessage("CES_RUN_EXPORT_DESCR"),
				"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/cat_export_setup.php?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&".bitrix_sessid_get()."&ACTION=EXPORT&PROFILE_ID=0"),
			);
			$arActions[] = array(
				"TEXT" => GetMessage('CES_ADD_PROFILE'),
				"TITLE" => GetMessage('CES_ADD_PROFILE_DESCR'),
				"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/cat_export_setup.php?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&ACTION=EXPORT_SETUP"."&".bitrix_sessid_get()),
			);
		}

		if ($bCanEdit && !$publicMode)
		{
			$arActions[] = array(
				"TEXT" => GetMessage("CES_TO_LEFT_MENU"),
				"TITLE" => GetMessage("CES_TO_LEFT_MENU_DESCR"),
				"ACTION" => $lAdmin->ActionRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&".bitrix_sessid_get()."&ACTION=MENU&PROFILE_ID=0"),
			);
		}

		if (empty($arReportParams["FILE_SETUP"]) && $USER->CanDoOperation('edit_php'))
		{
			$arActions[] = array(
				"TEXT" => GetMessage("CES_TO_AGENT"),
				"TITLE" => GetMessage("CES_TO_AGENT_DESCR"),
				"ACTION" => "ShowAgentForm('".$APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ACT_FILE=".$strReportFile."&".bitrix_sessid_get()."&ACTION=AGENT&PROFILE_ID=0');",
			);
			$arActions[] = array(
				"DISABLED" => $bWindowsHosting,
				"TEXT" => GetMessage("CES_TO_CRON"),
				"TITLE" => GetMessage("CES_TO_CRON_DESCR"),
				"ACTION" => ($bWindowsHosting ? '' : "ShowCronForm('".$APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ACT_FILE=".$strReportFile."&".bitrix_sessid_get()."&ACTION=CRON&PROFILE_ID=0', true);"),
			);
		}

		$row->AddActions($arActions);
	}

	$rsProfiles = CCatalogExport::GetList(
		array("LAST_USE" => "DESC", "NAME" => "ASC"),
		array("FILE_NAME" => $strReportFile, '!DEFAULT_PROFILE' => 'Y')
	);

	while ($arProfile = $rsProfiles->Fetch())
	{
		if ($arProfile['IN_AGENT'] == 'Y' && $arProfile['IN_CRON'] == 'Y')
			$cronErrors[] = '['.$arProfile['ID'].'] '.$arReportParams['TITLE'];

		$arProfile['USED'] = $arProfile['LAST_USE_FORMAT'];
		$boolNeedEdit = (isset($arProfile['NEED_EDIT']) && 'Y' == $arProfile['NEED_EDIT']);

		$row = &$lAdmin->AddRow($arProfile['ID'], $arProfile);

		$row->AddViewField('ID', $arProfile['ID']);
		$row->AddViewField("NAME", htmlspecialcharsbx($arReportParams["TITLE"]));
		$row->AddViewField("FILE", $strReportFile);

		$strProfileLink = '';
		if ($bCanExec)
		{
			if ($boolNeedEdit)
			{
				$url = "/bitrix/admin/cat_export_setup.php?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&ACTION=EXPORT_EDIT&PROFILE_ID=".$arProfile["ID"]."&".bitrix_sessid_get();
				$strProfileLink = '<a href="'.CHTTP::URN2URI($url).'" title="'.GetMessage("CES_EDIT_PROPFILE_DESCR").'">'.htmlspecialcharsbx($arProfile["NAME"]).'</a>'.
					'<br /><i>('.GetMessage('CES_NEED_EDIT').')</i>';
			}
			else
			{
				$url = ('Y' == $arProfile["IN_MENU"] ? "/bitrix/admin/cat_exec_exp.php" : "/bitrix/admin/cat_export_setup.php")."?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&ACTION=EXPORT&PROFILE_ID=".$arProfile["ID"]."&".bitrix_sessid_get();
				$strProfileLink = '<a href="'.CHTTP::URN2URI($url).'" title="'.GetMessage("export_setup_begin").'">'.htmlspecialcharsbx($arProfile["NAME"]).'</a>';
			}
		}
		else
		{
			$strProfileLink = htmlspecialcharsbx($arProfile["NAME"]);
			if ($boolNeedEdit)
			{
				$strProfileLink .= '<br><i>('.GetMessage('CES_NEED_EDIT').')</i>';
			}
		}
		$row->AddViewField('PROFILE', $strProfileLink);

		$row->AddCheckField("IN_MENU", false);
		$row->AddCheckField("IN_AGENT", false);
		$row->AddCheckField("IN_CRON", false);
		$row->AddCalendarField("USED", false);

		$strCreatedBy = '';
		$strModifiedBy = '';
		$arProfile['CREATED_BY'] = (int)$arProfile['CREATED_BY'];
		if (0 < $arProfile['CREATED_BY'])
		{
			if (!isset($arUserList[$arProfile['CREATED_BY']]))
			{
				$byUser = 'ID';
				$byOrder = 'ASC';
				$rsUsers = CUser::GetList(
					$byUser,
					$byOrder,
					array('ID_EQUAL_EXACT' => $arProfile['CREATED_BY']),
					array('FIELDS' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'))
				);
				if ($arOneUser = $rsUsers->Fetch())
				{
					$arOneUser['ID'] = (int)$arOneUser['ID'];
					if ($publicMode)
					{
						$arUserList[$arOneUser['ID']] = CUser::FormatName($strNameFormat, $arOneUser);
					}
					else
					{
						$arUserList[$arOneUser['ID']] = '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$arProfile['MODIFIED_BY'].'">'.CUser::FormatName($strNameFormat, $arOneUser).'</a>';
					}
				}
			}
			if (isset($arUserList[$arProfile['CREATED_BY']]))
				$strCreatedBy = $arUserList[$arProfile['CREATED_BY']];
		}
		$arProfile['MODIFIED_BY'] = (int)$arProfile['MODIFIED_BY'];
		if (0 < $arProfile['MODIFIED_BY'])
		{
			if (!isset($arUserList[$arProfile['MODIFIED_BY']]))
			{
				$byUser = 'ID';
				$byOrder = 'ASC';
				$rsUsers = CUser::GetList(
					$byUser,
					$byOrder,
					array('ID_EQUAL_EXACT' => $arProfile['MODIFIED_BY']),
					array('FIELDS' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'))
				);
				if ($arOneUser = $rsUsers->Fetch())
				{
					$arOneUser['ID'] = (int)$arOneUser['ID'];
					if ($publicMode)
					{
						$arUserList[$arOneUser['ID']] = CUser::FormatName($strNameFormat, $arOneUser);
					}
					else
					{
						$arUserList[$arOneUser['ID']] = '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$arProfile['MODIFIED_BY'].'">'.CUser::FormatName($strNameFormat, $arOneUser).'</a>';
					}
				}
			}
			if (isset($arUserList[$arProfile['MODIFIED_BY']]))
				$strModifiedBy = $arUserList[$arProfile['MODIFIED_BY']];
		}

		$row->AddViewField("CREATED_BY", $strCreatedBy);
		$row->AddCalendarField("DATE_CREATE", false);
		$row->AddViewField("MODIFIED_BY", $strModifiedBy);
		$row->AddCalendarField("TIMESTAMP_X", false);

		$arActions = array();
		if ($bCanExec)
			$arActions[] = array(
				"DEFAULT" => false,
				"TEXT" => GetMessage("CES_RUN_EXPORT"),
				"TITLE" => GetMessage("CES_RUN_EXPORT_DESCR"),
				"ACTION" => $lAdmin->ActionRedirect(('Y' == $arProfile["IN_MENU"] ? "/bitrix/admin/cat_exec_exp.php" : "/bitrix/admin/cat_export_setup.php")."?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&".bitrix_sessid_get()."&ACTION=EXPORT&PROFILE_ID=".$arProfile["ID"]),
			);

		if ($bCanEdit)
		{
			$arVars = explode('&', preg_replace("/[\n\r]+/i", "", $arProfile["SETUP_VARS"]));
			foreach ($arVars as &$value)
			{
				$value = htmlspecialcharsbx(urldecode($value));
			}
			if (isset($value))
				unset($value);
			$arActions[] = array(
				"TEXT" => GetMessage("CES_SHOW_VARS_LIST"),
				"TITLE" => GetMessage("CES_SHOW_VARS_LIST_DESCR"),
				"ACTION" => "ShowVarsForm('".CUtil::JSEscape(implode('<br />', $arVars))."')",
			);
			$arActions[] = array(
				"DEFAULT" => true,
				"TEXT" => GetMessage("CES_EDIT_PROFILE"),
				"TITLE" => GetMessage("CES_EDIT_PROPFILE_DESCR"),
				"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/cat_export_setup.php?lang=".urlencode(LANGUAGE_ID)."&ACT_FILE=".urlencode($strReportFile)."&ACTION=EXPORT_EDIT&PROFILE_ID=".$arProfile['ID']."&".bitrix_sessid_get()),
			);
			$arActions[] = array(
				"TEXT" => GetMessage("CES_COPY_PROFILE"),
				"TITLE" => GetMessage("CES_COPY_PROPFILE_DESCR"),
				"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/cat_export_setup.php?lang=".urlencode(LANGUAGE_ID)."&ACT_FILE=".urlencode($strReportFile)."&ACTION=EXPORT_COPY&PROFILE_ID=".$arProfile['ID']."&".bitrix_sessid_get()),
			);
		}

		if ($bCanEdit && !$publicMode)
		{
			if ('Y' == $arProfile["IN_MENU"])
			{
				$arActions[] = array(
					"TEXT" => GetMessage("CES_TO_LEFT_MENU_DEL"),
					"TITLE" => GetMessage("CES_TO_LEFT_MENU_DESCR_DEL"),
					"ACTION" => $lAdmin->ActionRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&".bitrix_sessid_get()."&ACTION=MENU&PROFILE_ID=".$arProfile["ID"]),
				);
			}
			else
			{
				$arActions[] = array(
					"TEXT" => GetMessage("CES_TO_LEFT_MENU"),
					"TITLE" => GetMessage("CES_TO_LEFT_MENU_DESCR"),
					"ACTION" => $lAdmin->ActionRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&".bitrix_sessid_get()."&ACTION=MENU&PROFILE_ID=".$arProfile["ID"]),
				);
			}
		}

		if ($USER->CanDoOperation('edit_php') && 'Y' != $arProfile["NEED_EDIT"])
		{
			if ('Y' == $arProfile["IN_AGENT"])
			{
				$arActions[] = array(
					"TEXT" => GetMessage("CES_TO_AGENT_DEL"),
					"TITLE" => GetMessage("CES_TO_AGENT_DESCR_DEL"),
					"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/cat_export_setup.php?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&".bitrix_sessid_get()."&ACTION=AGENT&PROFILE_ID=".$arProfile["ID"]),
				);
			}
			else
			{
				$arActions[] = array(
					"TEXT" => GetMessage("CES_TO_AGENT"),
					"TITLE" => GetMessage("CES_TO_AGENT_DESCR"),
					"ACTION" => "ShowAgentForm('".$APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ACT_FILE=".$strReportFile."&".bitrix_sessid_get()."&ACTION=AGENT&PROFILE_ID=".$arProfile["ID"]."');",
				);
			}

			if ('Y' == $arProfile["IN_CRON"])
			{
				$arActions[] = array(
					"DISABLED" => $bWindowsHosting,
					"TEXT" => GetMessage("CES_TO_CRON_DEL"),
					"TITLE" => GetMessage("CES_TO_CRON_DESCR_DEL"),
					"ACTION" => ($bWindowsHosting ? '' : "ShowCronForm('".$APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ACT_FILE=".$strReportFile."&".bitrix_sessid_get()."&ACTION=CRON&PROFILE_ID=".$arProfile["ID"]."', false);"),
				);
			}
			else
			{
				$arActions[] = array(
					"DISABLED" => $bWindowsHosting,
					"TEXT" => GetMessage("CES_TO_CRON"),
					"TITLE" => GetMessage("CES_TO_CRON_DESCR"),
					"ACTION" => ($bWindowsHosting ? '' : "ShowCronForm('".$APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ACT_FILE=".$strReportFile."&".bitrix_sessid_get()."&ACTION=CRON&PROFILE_ID=".$arProfile["ID"]."', true);"),
				);
			}
		}

		if($bCanEdit)
		{
			$arActions[] = array(
				"TEXT" => GetMessage("CES_DELETE_PROFILE"),
				"TITLE" => GetMessage("CES_DELETE_PROFILE_DESCR"),
				"ACTION" => "if(confirm('".GetMessage("CES_DELETE_PROFILE_CONF")."')) window.location='".$APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&ACT_FILE=".urlencode($strReportFile)."&".bitrix_sessid_get()."&ACTION=DEL_PROFILE&PROFILE_ID=".$arProfile["ID"]."';",
			);
		}
		$row->AddActions($arActions);
	}
}

$arContext = array();
if (!empty($arContextMenu))
{
	$arContext[] = array(
		"TEXT" => GetMessage("CES_ADD_PROFILE"),
		"TITLE" => GetMessage("CES_ADD_PROFILE_DESCR"),
		"ICON" => "btn_new",
		"DISABLE" => true,
		"MENU" => $arContextMenu,
	);
}
$lAdmin->AddAdminContextMenu($arContext, false);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("TITLE_EXPORT_PAGE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<div id="form_shadow" style="display:none;" class="float-form-shadow">&nbsp;</div>
<div id="agent_form" style="display:none;" class="float-form">
<form name="agentform" id="agentform" action="" method="post">
	<table class="edit-table">
		<tbody>
	<tr>
		<td style="white-space: nowrap; font-size: 12px;"><? echo GetMessage("CES_RUN_INTERVAL"); ?></td>
		<td><input type="text" name="agent_period" value="" size="10"></td>
	</tr>
		</tbody>
		<tfoot>
	<tr>
		<td colspan="2" style="text-align: center;">
			<input type="submit" value="<? echo GetMessage("CES_SET"); ?>">&nbsp;&nbsp;<input type="button" value="<? echo GetMessage("CES_CLOSE"); ?>" onclick="HideAgentForm();">
		</td>
	</tr>
		</tfoot>
	</table>
</form>
</div>

<div id="cron_form_add" style="display:none;" class="float-form">
<form name="cronform_add" id="cronform_add" action="" method="post">
	<table class="edit-table">
	<tr>
		<td style="font-size: 12px;"><? echo GetMessage("CES_RUN_INTERVAL"); ?></td>
		<td><input type="text" name="agent_period" value="" size="10"></td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: center; font-weight: bold; font-size: 12px;"><? echo GetMessage("CES_OR"); ?></td>
	</tr>
	<tr>
		<td style="font-size: 12px;"><? echo GetMessage("CES_RUN_TIME"); ?></td>
		<td style="white-space: nowrap;"><input type="text" name="agent_hour" value="" size="2"> : <input type="text" name="agent_minute" value="" size="2"></td>
	</tr>
	<tr>
		<td style="font-size: 12px;"><? echo GetMessage("CES_PHP_PATH"); ?></td>
		<td><input type="text" name="agent_php_path" value="/usr/local/php/bin/php" size="25"></td>
	</tr>
	<tr>
		<td style="font-size: 12px;"><? echo GetMessage("CES_AUTO_CRON"); ?></td>
		<td><input type="hidden" name="auto_cron_tasks" value="N"><input type="checkbox" name="auto_cron_tasks" value="Y"></td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: center;">
			<input type="submit" value="<? echo GetMessage("CES_SET"); ?>">&nbsp;&nbsp;<input type="button" value="<? echo GetMessage("CES_CLOSE"); ?>" onclick="HideCronForm(true);">
		</td>
	</tr>
	</table>
</form>
</div>

<div id="cron_form_del" style="display:none;" class="float-form">
<form name="cronform_del" id="cronform_del" action="" method="post">
	<table class="edit-table">
	<tr>
		<td style="font-size: 12px;"><? echo GetMessage("CES_AUTO_CRON_DEL"); ?></td>
		<td><input type="hidden" name="auto_cron_tasks" value="N"><input type="checkbox" name="auto_cron_tasks" value="Y"></td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: center;">
			<input type="submit" value="<? echo GetMessage("CES_DELETE"); ?>">&nbsp;&nbsp;<input type="button" value="<? echo GetMessage("CES_CLOSE"); ?>" onclick="HideCronForm(false);">
		</td>
	</tr>
	</table>
</form>
</div>

<div id="vars_div" style="display:none;" class="float-form">
<div id="vars_div_cont" class="data" style="font-size: 12px;">
</div>
<div style="text-align: center;">
	<input type="button" value="<? echo GetMessage("CES_CLOSE"); ?>" onclick="HideVarsForm();">
</div>
</div>
<?
if (strlen($strErrorMessage) > 0)
	CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("CES_ERRORS"), "DETAILS"=>$strErrorMessage));

if ($_GET["success_export"]=="Y")
{
	CAdminMessage::ShowNote(GetMessage("CES_SUCCESS"));

	if (isset($_GET['export_id']) && !empty($_GET['export_id']))
	{
		if (isset($_SESSION['BX_EXP_TMP_ID']) && is_array($_SESSION['BX_EXP_TMP_ID']))
		{
			$strTempID = substr(strval($_GET['export_id']),0,32);
			$strKey = array_search($strTempID,$_SESSION['BX_EXP_TMP_ID']);
			if (false !== $strKey && isset($_SESSION[$_SESSION['BX_EXP_TMP_ID'][$strKey]]))
			{
				if (0 == preg_match(BX_CATALOG_FILENAME_REG,$SETUP_FILE_NAME))
				{
					$strSetupFileName = Rel2Abs('/',$_SESSION[$_SESSION['BX_EXP_TMP_ID'][$strKey]]);
					if (false !== $strSetupFileName)
					{
						if (substr($strSetupFileName, 0, strlen($_SERVER["DOCUMENT_ROOT"]))==$_SERVER["DOCUMENT_ROOT"])
						{
							$strSetupFileName = substr($strSetupFileName, strlen($_SERVER["DOCUMENT_ROOT"]));
						}

						if (file_exists($_SERVER['DOCUMENT_ROOT'].$strSetupFileName) && is_file($_SERVER['DOCUMENT_ROOT'].$strSetupFileName))
						{
							if ($APPLICATION->GetFileAccessPermission($strSetupFileName) >= "R")
							{
								echo "<p>".GetMessage("CES_EXPORT_FILE")." <a href=\"".htmlspecialcharsbx($strSetupFileName)."\">".htmlspecialcharsbx($strSetupFileName)."</a></p>";
							}
						}
					}
				}
				unset($_SESSION[$_SESSION['BX_EXP_TMP_ID'][$strKey]]);
				unset($_SESSION['BX_EXP_TMP_ID'][$strKey]);
			}
		}
	}
}

if (!empty($cronErrors))
{
	$cronMessage = new CAdminMessage(array(
		'MESSAGE' => GetMessage('CES_CRON_AGENT_ERRORS'),
		'DETAILS' => implode('<br>', $cronErrors),
		'TYPE' => 'OK',
		'HTML' => true
	));
	echo $cronMessage->Show();
	unset($cronMessage);
}

$lAdmin->DisplayList();

echo BeginNote();
	echo GetMessage("export_setup_cat")?> <?echo CATALOG_PATH2EXPORTS?><br><br>
	<?echo GetMessage("CES_NOTES1");?><br><br>
	<?if ($bWindowsHosting):?>
		<b><?echo GetMessage("CES_NOTES2");?></b>
	<?else:?>
		<?echo GetMessage("CES_NOTES3");?>
		<b><?echo $_SERVER["DOCUMENT_ROOT"];?>/bitrix/crontab/crontab.cfg</b>
		<?echo GetMessage("CES_NOTES4");?><br>
		<?echo GetMessage("CES_NOTES5");?><br>
		<b>crontab <?echo $_SERVER["DOCUMENT_ROOT"];?>/bitrix/crontab/crontab.cfg</b><br>
		<?echo GetMessage("CES_NOTES6");?><br>
		<b>crontab -l</b><br>
		<?echo GetMessage("CES_NOTES7");?><br>
		<b>crontab -r</b><br><br>
		<?
		$arRetval = array();
		@exec("crontab -l", $arRetval);
		if (is_array($arRetval) && !empty($arRetval))
		{
			echo GetMessage("CES_NOTES8");?><br>
			<textarea name="crontasks" cols="70" rows="5" readonly>
			<?
			echo htmlspecialcharsbx(implode("\n", $arRetval))."\n";
			?>
			</textarea><br>
			<?
		}
		echo GetMessage("CES_NOTES10");?><br><br>
		<?=GetMessage("CES_NOTES11_EXT", array('#FILE#' => '/bitrix/php_interface/include/catalog_export/cron_frame.php'));?><br>
		<?=GetMessage("CES_NOTES12_EXT");?><br>
		<?=GetMessage('CES_NOTES13_EXT', array('#FOLDER#' => '/bitrix/modules/catalog/load/'));
	endif;

echo EndNote();

?>
<script type="text/javascript">
function ShowDiv(div, shadow)
{
	var obDiv = BX(div);
	var obShadow = BX(shadow);
	if (!!obDiv && !!obShadow)
	{
		var obCoord = BX.GetWindowSize();
		BX.style(obDiv, 'display', 'block');
		BX.style(obShadow, 'display', 'block');

		var l = parseInt(obCoord.scrollLeft + obCoord.innerWidth/2 - obDiv.offsetWidth/2);
		var t = parseInt(obCoord.scrollTop + obCoord.innerHeight/2 - obDiv.offsetHeight/2);

		BX.adjust(obDiv, {style: {left: l + "px", top: t + "px"}});
		BX.adjust(obShadow, {style: {left: (l+4) + "px", top: (t+4) + "px", width: obDiv.offsetWidth + 'px', height: obDiv.offsetHeight + 'px'}});
	}
}

function HideDiv(div, shadow)
{
	var obDiv = BX(div);
	var obShadow = BX(shadow);
	if (!!obDiv && !!obShadow)
	{
		BX.style(obDiv, 'display', 'none');
		BX.style(obShadow, 'display', 'none');
	}
}

function SetForm(form, strAction)
{
	var obForm = BX(form);
	if (!!obForm)
	{
		obForm.action = strAction;
		var obTbl = BX.findChild(obForm, {tag: 'table', className: 'edit-table'}, false, false);
		if (!!obTbl)
		{
			var n = obTbl.tBodies[0].rows.length;
			for (var i=0; i<n; i++)
			{
				if (obTbl.tBodies[0].rows[i].cells.length > 1)
				{
					BX.addClass(obTbl.rows[i].cells[0], 'adm-detail-content-cell-l');
					BX.addClass(obTbl.rows[i].cells[1], 'adm-detail-content-cell-r');
				}
			}
		}
		BX.adminFormTools.modifyFormElements(obTbl);
		return true;
	}
	return false;
}

function ShowAgentForm(strAction)
{
	if (SetForm('agentform', strAction))
	{
		ShowDiv('agent_form', 'form_shadow');
	}
}

function HideAgentForm()
{
	HideDiv('agent_form', 'form_shadow');
}

function ShowCronForm(strAction, boolAdd)
{
	if (boolAdd)
	{
		if (SetForm('cronform_add', strAction))
		{
			ShowDiv('cron_form_add', 'form_shadow');
		}
	}
	else
	{
		if (SetForm('cronform_del', strAction))
		{
			ShowDiv('cron_form_del', 'form_shadow');
		}
	}
}

function HideCronForm(boolAdd)
{
	if (boolAdd)
	{
		HideDiv('cron_form_add', 'form_shadow');
	}
	else
	{
		HideDiv('cron_form_del', 'form_shadow');
	}
}

function ShowVarsForm(strData)
{
	var obDivCont = BX('vars_div_cont');
	if (!!obDivCont)
	{
		BX.adjust(obDivCont, { html: (!!strData.length && 0 < strData.length ? strData : ' ')});
		ShowDiv('vars_div', 'form_shadow');
	}
}

function HideVarsForm()
{
	HideDiv('vars_div', 'form_shadow');
}
</script>
<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");