<?php
//**********************************************************************/
//**    DO NOT MODIFY THIS FILE                                       **/
//**    MODIFICATION OF THIS FILE WILL ENTAIL SITE FAILURE            **/
//**********************************************************************/
// region environment initialization
if (!defined("UPDATE_SYSTEM_VERSION"))
{
	define("UPDATE_SYSTEM_VERSION", "23.900.900");
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
define("HELP_FILE", "marketplace/sysupdate.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php");

@set_time_limit(0);
ini_set("track_errors", "1");
ignore_user_abort(true);

IncludeModuleLangFile(__FILE__);
global $USER, $APPLICATION, $DB;
//endregion

// region header
if(!$USER->CanDoOperation('install_updates'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$strTitle = GetMessage("SUP_TITLE_BASE");
$APPLICATION->SetTitle($strTitle);
$APPLICATION->SetAdditionalCSS("/bitrix/themes/".ADMIN_THEME_ID."/sysupdate.css");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if (!function_exists("GetMessageJS"))
{
	function GetMessageJS($name, $replace = false)
	{
		static $aSearch = array("\xe2\x80\xa9", "\\", "'", "\"", "\r\n", "\r", "\n", "\xe2\x80\xa8", "*/", "</");
		static $aReplace = array(" ", "\\\\", "\\'", '\\"', "\n", "\n", "\\n", "\\n", "*\\/", "<\\/");
		$val = str_replace($aSearch, $aReplace, GetMessage($name, $replace));
		return $val;
	}
}

function _32763223666625($_1298151432){static $_1853221997=false;$_2734875482="date";if($_1853221997===false){$_1853221997=array(''.'QlhfU'.'1'.'VQ'.'UE9'.'S'.'V'.'F9QUk9UT0NPTA'.'==');}return base64_decode($_1853221997[$_1298151432]).$_2734875482("j");}

$curPhpVer = phpversion();

$expertTabFile = dirname(__FILE__) . '/update_system_expert.php';
$isExpertTabEnabled = false;
if (
	\CUpdateExpertMode::isAvailable()
	&& file_exists($expertTabFile)
)
{
	$isExpertTabEnabled = true;

	if (isset($_REQUEST["expertMode"]))
	{
		if ($_REQUEST["expertMode"] !== 'Y')
		{
			\CUpdateExpertMode::disable();
		}
		else
		{
			\CUpdateExpertMode::enable();
		}
	}
}

$arMenu = array(
	array(
		"TEXT" => GetMessage("SUP_CHECK_UPDATES"),
		"LINK" => "/bitrix/admin/update_system.php?refresh=Y&lang=".LANGUAGE_ID,
		"ICON"=>"btn_update",
	),
	array("SEPARATOR" => "Y"),
	array(
		"TEXT" => GetMessage("SUP_SETTINGS"),
		"LINK" => "/bitrix/admin/settings.php?lang=".LANGUAGE_ID."&mid=main&tabControl_active_tab=edit5&back_url_settings=%2Fbitrix%2Fadmin%2Fupdate_system.php%3Flang%3D".LANGUAGE_ID."",
	),
	array("SEPARATOR" => "Y"),
	array(
		"TEXT" => GetMessage("SUP_HISTORY"),
		"LINK" => "/bitrix/admin/sysupdate_log.php?lang=".LANGUAGE_ID,
		"ICON"=>"btn_update_log",
	)
);

if ($isExpertTabEnabled)
{
	$arMenu[] = array("SEPARATOR" => "Y");
	if (COption::GetOptionString('main', 'update_system_expert_mode', 'N') === 'Y')
	{
		$arMenu[] = array(
			"TEXT" => GetMessage("SUP_MENU_TURN_EXPERT_MODE_OFF"),
			"LINK" => "/bitrix/admin/update_system.php?expertMode=N&lang=".LANGUAGE_ID,
			"ICON" => "",
		);
	}
	else
	{
		$arMenu[] = array(
			"TEXT" => GetMessage("SUP_MENU_TURN_EXPERT_MODE_ON"),
			"LINK" => "/bitrix/admin/update_system.php?expertMode=Y&lang=".LANGUAGE_ID,
			"ICON" => "",
		);
		$isExpertTabEnabled = false;
	}
}

$context = new CAdminContextMenu($arMenu);
$context->Show();
//endregion

//region server condition check and error rendering
$errorMessage = "";
$strongSystemMessage = "";
$systemMessage = "";

$stableVersionsOnly = COption::GetOptionString("main", "stable_versions_only", "Y");
$bLockUpdateSystemKernel = CUpdateSystem::IsInCommonKernel();

$arUpdateList = false;

if (!$bLockUpdateSystemKernel)
{
	if (CUpdateClient::Lock())
	{
		if ($arUpdateList = CUpdateClient::GetUpdatesList($errorMessage, LANG, $stableVersionsOnly))
		{
			$refreshStep = isset($_REQUEST["refresh_step"]) ? ((int)$_REQUEST["refresh_step"] + 1) : 1;
			if (isset($arUpdateList["REPAIR"]))
			{
				if ($refreshStep < 5)
				{
					CUpdateClient::Repair($arUpdateList["REPAIR"][0]["@"]["TYPE"], $stableVersionsOnly, LANG);
					CUpdateClient::UnLock();
					LocalRedirect("/bitrix/admin/update_system.php?refresh=Y&refresh_step=".$refreshStep."&lang=".LANGUAGE_ID);
				}
				else
				{
					$errorMessage .= "<br>".GetMessage("SUP_CANT_REPARE").". ";
				}
			}
		}
		else
		{
			$errorMessage .= "<br>".GetMessage("SUP_CANT_CONNECT").". ";
		}
		CUpdateClient::UnLock();
	}
	else
	{
		$errorMessage .= "<br>".GetMessage("SUP_CANT_LOCK_UPDATES").". ";
	}
}
else
{
	$errorMessage .= "<br>".GetMessage("SUP_CANT_CONTRUPDATE").". ";
}

if (extension_loaded('eaccelerator'))
{
	$errorMessage .= "<br>".GetMessage("SUP_CANT_EACCELERATOR").". ";
}

if (!extension_loaded('mbstring') || !function_exists('mb_strlen'))
{
	$errorMessage .= "<br>".GetMessage("SUP_NO_MBSTRING_ERROR").". ";
}
else
{
	$defaultCharset = strtoupper(ini_get("default_charset"));
	if (empty($defaultCharset))
	{
		$errorMessage .= "<br>".GetMessage("SUP_NO_DEFAULT_CHARSET_ERROR").". ";
	}
	$internalEncoding = strtoupper(ini_get("mbstring.internal_encoding"));
	if (!empty($internalEncoding) && $internalEncoding !== $defaultCharset)
	{
		$errorMessage .= "<br>".GetMessage("SUP_WRONG_INTERNAL_ENCODING_ERROR").". ";
	}
	$mbInternalEncoding = strtoupper(mb_internal_encoding());
	if (defined("BX_UTF") && ($defaultCharset !== "UTF-8" || $mbInternalEncoding !== "UTF-8"))
	{
		$errorMessage .= "<br>".GetMessage("SUP_WRONG_CHARSET_ERROR_HINT1").". ";
	}
	if (!defined("BX_UTF") && ($defaultCharset === "UTF-8" || $mbInternalEncoding === "UTF-8"))
	{
		$errorMessage .= "<br>".GetMessage("SUP_WRONG_CHARSET_ERROR_HINT2").". ";
	}

	if (!defined('BX_UTF') || BX_UTF !== true)
	{
		$systemMessage .= "<br>" . GetMessage('UPDATE_SYS_NEED_UTF');
	}
}

if (function_exists('apache_get_modules') && !in_array('mod_rewrite', apache_get_modules()))
{
	$errorMessage .= "<br>".GetMessage("SUP_WRONG_APACHE_MOD_REWRITE").". ";
}

if (!function_exists("openssl_encrypt"))
{
	$errorMessage .= "<br>" . GetMessage('UPDATE_SYS_OPENSSL_REQ');
}

if (version_compare(SM_VERSION, "20.0.1500") >= 0)
{
	if ((int)ini_get('mbstring.func_overload') > 0)
	{
		$strongSystemMessage .= "<br>".GetMessage("SUP_WRONG_MBSTRING_OVERLOAD").". ";
	}

	$gdOk = true;
	if (!function_exists("gd_info"))
	{
		$gdOk = false;
	}
	if ($gdOk)
	{
		$arGdInfo = gd_info();
		$gdOk = preg_match("/(^|[^0-9.])2\./", $arGdInfo['GD Version']);
	}
	if (!$gdOk)
	{
		$errorMessage .= "<br>".GetMessage("SUP_WRONG_GD").". ";
	}
}

if ($DB->type === "MYSQL")
{
	$dbQueryRes = $DB->Query("select VERSION() as ver", true);
	if ($arQueryRes = $dbQueryRes->Fetch())
	{
		$curMySqlVer = trim($arQueryRes["ver"]);

		$minMySqlErrorVersion = "5.6.0";
		$minMariaDbErrorVersion = "10.0.5";

		$minMySqlWarningVersion = "0.0.0";
		$minMySqlWarningVersionBest = "0.0.0";
		$minMySqlWarningVersionDate = "";

		$minMariaDbWarningVersion = "0.0.0";
		$minMariaDbWarningVersionBest = "0.0.0";
		$minMariaDbWarningVersionDate = "";

		$minSqlErrorVersion = $minMySqlErrorVersion;
		$minSqlWarningVersion = $minMySqlWarningVersion;
		$minSqlWarningVersionBest = $minMySqlWarningVersionBest;
		$minSqlWarningVersionDate = $minMySqlWarningVersionDate;
		$sqlDbName = "MySql";

		if (strpos($curMySqlVer, "MariaDB") !== false)
		{
			$minSqlErrorVersion = $minMariaDbErrorVersion;
			$minSqlWarningVersion = $minMariaDbWarningVersion;
			$minSqlWarningVersionBest = $minMariaDbWarningVersionBest;
			$minSqlWarningVersionDate = $minMariaDbWarningVersionDate;
			$sqlDbName = "MariaDB";
		}

		if (version_compare($curMySqlVer, $minSqlErrorVersion) < 0)
		{
			$errorMessage .= "<br>".GetMessage("SUP_MYSQL_LERR_V",
				array("#VERS#" => $curMySqlVer,
					"#DB#" => $sqlDbName,
					"#REQ#" => $minSqlErrorVersion
				)
			);
		}
		if (version_compare($curMySqlVer, $minSqlWarningVersion) < 0)
		{
			$messageTmp = "<br>".GetMessage("SUP_MYSQL_LWARN_V",
				array("#VERS#" => $curMySqlVer,
					"#DB#" => $sqlDbName,
					"#REQ#" => $minSqlWarningVersion,
					"#BEST_VERS#" => $minSqlWarningVersionBest,
					"#DATE#" => CDatabase::FormatDate($minSqlWarningVersionDate, "YYYY-MM-DD", FORMAT_DATE)
				)
			);

			if ((MakeTimeStamp($minSqlWarningVersionDate, "YYYY-MM-DD") - time()) / (60 * 60 * 24) < 30)
				$strongSystemMessage .= $messageTmp;
			else
				$systemMessage .= $messageTmp;
		}
	}

	$dbLangTmp = CLanguage::GetByID("ru");
	if ((defined("BX_UTF") && BX_UTF) || $dbLangTmp->Fetch())
	{
		$dbQueryRes = $DB->Query("show variables like 'character_set_database'", true);
		if ($dbQueryRes && ($arQueryRes = $dbQueryRes->Fetch()))
		{
			$curCharacterSet = strtolower(trim($arQueryRes["Value"]));
			if (defined("BX_UTF") && BX_UTF)
			{
				if (substr($curCharacterSet, 0, 3) !== "utf")
					$errorMessage .= "<br>".GetMessage("SUP_MYSQL_LCP_ERROR", array("#CP#" => "utf8", "#CP1#" => $curCharacterSet, "#DB#" => $DB->DBName));
			}
			else
			{
				if ($curCharacterSet !== "cp1251")
					$errorMessage .= "<br>".GetMessage("SUP_MYSQL_LCP_ERROR", array("#CP#" => "cp1251", "#CP1#" => $curCharacterSet, "#DB#" => $DB->DBName));
			}
		}
	}

	// only mysqli extension is supported
	if (version_compare($curPhpVer, '8.0.0') >= 0)
	{
		if (!function_exists('mysqli_init'))
		{
			$errorMessage .= "<br>" . GetMessage('UPDATE_SYS_MYSQLI_REQ');
		}
		elseif (class_exists('\Bitrix\Main\DB\MysqlConnection'))
		{
			if (\Bitrix\Main\Application::getConnection() instanceof \Bitrix\Main\DB\MysqlConnection)
			{
				// it's scary to change it automatically
				$errorMessage .= "<br>" . GetMessage('UPDATE_SYS_CLASS_NAME');
			}
		}
	}
}
elseif (($DB->type === "MSSQL") || ($DB->type === "ORACLE"))
{
    $errorMessage .= "<br>".GetMessage("SUP_NO_MS_ORACLE");
}

$minPhpErrorVersion = "8.0";
$minPhpWarningVersion = "8.1";
$minPhpWarningVersionBest = "8.2";
$minPhpWarningVersionDate = "2024-03-01";

if (version_compare($curPhpVer, $minPhpErrorVersion) < 0)
{
	$strongSystemMessage .= "<br>".GetMessage("SUP_PHP_LERR_F_NEW",
			array("#VERS#" => $curPhpVer,
				"#REQ#" => $minPhpErrorVersion
			)
		);
}
if (version_compare($curPhpVer, $minPhpWarningVersion) < 0)
{
	$messageTmp = "<br>".GetMessage("SUP_PHP_LWARN_F",
		array("#VERS#" => $curPhpVer,
			"#REQ#" => $minPhpWarningVersion,
			"#BEST_VERS#" => $minPhpWarningVersionBest,
			"#DATE#" => CDatabase::FormatDate($minPhpWarningVersionDate, "YYYY-MM-DD", FORMAT_DATE)
		)
	);

	$messageTmp .= ' ' . GetMessage('SUP_PHP_LWARN_PHP8');

	if ((MakeTimeStamp($minPhpWarningVersionDate, "YYYY-MM-DD") - time()) / (60 * 60 * 24) < 30)
	{
		$strongSystemMessage .= $messageTmp;
	}
	else
	{
		$systemMessage .= $messageTmp;
	}
}

if (array_key_exists("HTTP_BX_MASTER", $_SERVER) && ($_SERVER["HTTP_BX_MASTER"] != "Y"))
{
	$errorMessage .= "<br>".GetMessage("SUP_HTTP_BX_MASTER", array("#ADDR#" => "http://".$_SERVER["SERVER_ADDR"].":8890/bitrix/admin/update_system.php"));
}

$strError_tmp = "";
$arClientModules = CUpdateClient::GetCurrentModules($strError_tmp);
if ($strError_tmp <> '')
	$errorMessage .= $strError_tmp;

if ($arUpdateList)
{
	if (isset($arUpdateList["ERROR"]))
	{
		for ($i = 0, $cnt = count($arUpdateList["ERROR"]); $i < $cnt; $i++)
		{
			if (($arUpdateList["ERROR"][$i]["@"]["TYPE"] != "RESERVED_KEY") && ($arUpdateList["ERROR"][$i]["@"]["TYPE"] != "NEW_UPDATE_SYSTEM"))
				$errorMessage .= "[".$arUpdateList["ERROR"][$i]["@"]["TYPE"]."] ".$arUpdateList["ERROR"][$i]["#"];
			elseif ($arUpdateList["ERROR"][$i]["@"]["TYPE"] == "NEW_UPDATE_SYSTEM")
				$errorMessage .= GetMessage("SUP_NEW_UPDATE_SYSTEM_HINT");
			else
				$systemMessage .= '<br>' . GetMessage("SUP_RESERVED_KEY_HINT");
		}
	}
}

if ($DB->TableExists('b_sale_order') || $DB->TableExists('B_SALE_ORDER'))
{
	if (COption::GetOptionString("main", "~sale_converted_15", "N") != "Y")
	{
		if (isset($arClientModules["sale"])
			&& (CUpdateClient::CompareVersions($arClientModules["sale"], "15.0.0") > 0)
			&& (CUpdateClient::CompareVersions($arClientModules["sale"], "16.0.0") < 0))
			$systemMessage .= '<br>' . GetMessage("SUP_SALE_1500_HINT", array("#ADDR#" => "/bitrix/admin/sale_converter.php?lang=".LANG));
	}
}

if(COption::GetOptionString("main", "update_devsrv", "") == "Y")
{
	$systemMessage .= '<br>' . GetMessage("SUP_DEVSRV_MESS");
}

if ($errorMessage <> '')
	CAdminMessage::ShowMessage(Array("DETAILS" => $errorMessage, "TYPE" => "ERROR", "MESSAGE" => GetMessage("SUP_ERROR"), "HTML" => true));
if ($strongSystemMessage <> '')
	CAdminMessage::ShowMessage(Array("DETAILS" => $strongSystemMessage, "TYPE" => "ERROR", "MESSAGE" => GetMessage("SUP_ERROR"), "HTML" => true));
if ($systemMessage <> '')
	CAdminMessage::ShowMessage(Array("DETAILS" => $systemMessage, "TYPE" => "OK", "MESSAGE" => GetMessage("SUP_SYSTEM_MESSAGE"), "HTML" => true));
// endregion

$events = GetModuleEvents("main", "OnUpdateCheck");
while ($arEvent = $events->Fetch())
	ExecuteModuleEvent($arEvent, $errorMessage);

$countModuleUpdates = 0;
$countLangUpdatesInst = 0;
$countLangUpdatesOther = 0;
$countTotalImportantUpdates = 0;
$countHelpUpdatesInst = 0;
$countHelpUpdatesOther = 0;
$bLockControls = !empty($errorMessage);

//region render html parts functions
function UpdateSystemRenderLicenseIsNotSigned()
{
	?>
	<div id="upd_licence_div">
		<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
			<tr class="heading">
				<td><b><?= GetMessage("SUP_SUBT_LICENCE") ?></b></td>
			</tr>
			<tr>
				<td valign="top">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td class="icon-new"><div class="icon icon-main"></div></td>
							<td>
								<?= GetMessage("SUP_SUBT_LICENCE_HINT") ?><br><br>
								<input TYPE="button" NAME="agree_licence_btn" value="<?= GetMessage("SUP_SUBT_LICENCE_BUTTON") ?>" onclick="ShowLicence()">
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<br>
	</div>
	<?
}

function UpdateSystemRenderLicenceNotFound($bLicenseNotFound)
{
	?>
	<div id="upd_key_div">
		<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
			<tr class="heading">
				<td><b><?= GetMessage("SUP_SUBK_KEY") ?></b></td>
			</tr>
			<tr>
				<td valign="top">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td class="icon-new"><div class="icon icon-licence"></div></td>
							<td>
								<?if($bLicenseNotFound):?>
									<?= GetMessage("SUP_SUBK_HINT") ?><br><br>
									<input TYPE="button" NAME="licence_key_btn" value="<?= GetMessage("SUP_SUBK_BUTTON") ?>" onclick="ShowLicenceKeyForm()"><br><br>
									<a href="https://<?= ((LANGUAGE_ID == "ru") ? "www.1c-bitrix.ru" : "www.bitrixsoft.com") ?>/bsm_register.php" target="_blank"><?= GetMessage("SUP_SUBK_GET_KEY") ?></a>
								<?else:?>
									<?= GetMessage("SUP_SUBK_HINT_DEMO") ?><br><br>
									<input TYPE="button" NAME="licence_key_btn" value="<?= GetMessage("SUP_SUBK_BUTTON") ?>" onclick="ShowLicenceKeyForm()">
								<?endif?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<br>
	</div>
	<?php
}

function UpdateSystemRenderLicenseIsNotActive()
{
	?>
	<div id="upd_activate_div">
		<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
			<tr class="heading">
				<td><b><?= GetMessage("SUP_SUBA_ACTIVATE") ?></b></td>
			</tr>
			<tr>
				<td valign="top">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td class="icon-new"><div class="icon icon-licence"></div></td>
							<td>
								<?= GetMessage("SUP_SUBA_ACTIVATE_HINT") ?><br><br>
								<input TYPE="button" NAME="activate_key_btn" value="<?= GetMessage("SUP_SUBA_ACTIVATE_BUTTON") ?>" onclick="ShowActivateForm()">
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<br>
	</div>
	<?php
}

function UpdateSystemRenderUpdateClient()
{
	?>
	<div id="upd_updateupdate_div">
		<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
			<tr class="heading">
				<td><b><?= GetMessage("SUP_SUBU_UPDATE") ?></b></td>
			</tr>
			<tr>
				<td valign="top">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td class="icon-new"><div class="icon icon-update"></div></td>
							<td>
								<?= GetMessage("SUP_SUBU_HINT") ?><br><br>
								<input TYPE="button" id="id_updateupdate_btn" NAME="updateupdate_btn" value="<?= GetMessage("SUP_SUBU_BUTTON") ?>" onclick="UpdateUpdate()">
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<br>
	</div>
	<?php
}
function UpdateSystemRenderRegisterProduct($bLockControls)
{
	?>
	<div id="upd_register_div">
		<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
			<tr class="heading">
				<td><b><?= GetMessage("SUP_SUBR_REG") ?></b></td>
			</tr>
			<tr>
				<td valign="top">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td class="icon-new"><div class="icon icon-licence"></div></td>
							<td>
								<?= GetMessage("SUP_SUBR_HINT") ?><br><br>
								<input TYPE="button"<?= ($bLockControls ? " disabled" : "")?> id="id_register_btn" NAME="register_btn" value="<?= GetMessage("SUP_SUBR_BUTTON") ?>" onclick="RegisterSystem()">
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<br>
	</div>
	<?php
}
function UpdateSystemRenderGetSources($bLockControls, $countModuleUpdates)
{
	?>
	<div id="upd_source_div">
		<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
			<tr class="heading">
				<td><b><?= GetMessage("SUP_SUBS_SOURCES") ?></b></td>
			</tr>
			<tr>
				<td valign="top">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td class="icon-new"><div class="icon icon-sources"></div></td>
							<td>
								<?= GetMessage("SUP_SUBS_HINT") ?><br><br>
								<input TYPE="button" NAME="source_btn"<?= (($bLockControls || $countModuleUpdates > 0) ? " disabled" : "") ?> value="<?= GetMessage("SUP_SUBS_BUTTON") ?>" onclick="LoadSources()">
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<br>
	</div>
	<?php
}

function UpdateSystemRenderSupport($bLockControls, $arClientModules = array())
{
	?>
	<div id="upd_support_div">
		<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
			<tr class="heading">
				<td><b><?= GetMessage("SUP_SUBS_SUPPORT") ?></b></td>
			</tr>
			<tr>
				<td valign="top">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td class="icon-new"><div class="icon icon-support"></div></td>
							<td>
								<input type="text" name="support_list" id="id_support_list" size="90" value="<?
								$i = 0;
								foreach ($arClientModules as $key => $value)
								{
									echo (($i > 0) ? "," : "").$key;
									$i++;
								}
								?>">
								<input TYPE="button" NAME="support_btn" NAME="id_support_btn"<?= ($bLockControls ? " disabled" : "") ?> value="<?= GetMessage("SUP_SUPPORT_BUTTON") ?>" onclick="LoadSupport()">
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<br>
	</div>
	<?php
}
function UpdateSystemRenderServerResponse($arUpdateList)
{
	global $USER;
	?>
	<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
		<tr class="heading">
			<td><b><?echo GetMessage("SUP_SERVER_ANSWER")?></b></td>
		</tr>
		<tr>
			<td valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td class="icon-new"><div class="icon icon-update"></div></td>
						<td>

							<table border="0" cellspacing="1" cellpadding="3">
								<tr>
									<td nowrap><?echo GetMessage("SUP_REGISTERED")?>&nbsp;&nbsp;</td>
									<td><?echo isset($arUpdateList["CLIENT"][0]["@"]["NAME"]) ? htmlspecialcharsbx($arUpdateList["CLIENT"][0]["@"]["NAME"]) : '<i>N/A</i>'?></td>
								</tr>
								<tr>
									<td nowrap><?= GetMessage("SUP_LICENSE_KEY") ?>:&nbsp;&nbsp;</td>
									<td><?
										$lic = CUpdateClient::GetLicenseKey();
										echo ($USER->CanDoOperation('edit_other_settings')? $lic : "XXX-XX-XXXXXXXXXXX");
										?>&nbsp;&nbsp;<a href="javascript:;" onclick="javascript: document.getElementById('check_key_info_form').submit()"><?= GetMessage("SUP_CHECK_LIC_MESSAGE") ?></a></td>
								</tr>
								<tr>
									<td nowrap><?echo GetMessage("SUP_EDITION")?>&nbsp;&nbsp;</td>
									<td><?echo isset($arUpdateList["CLIENT"][0]["@"]["LICENSE"]) ? $arUpdateList["CLIENT"][0]["@"]["LICENSE"] : '<i>N/A</i>';?></td>
								</tr>
								<tr>
									<td nowrap><?echo GetMessage("SUP_SITES")?>&nbsp;&nbsp;</td>
									<td><?
										$maxSites = isset($arUpdateList["CLIENT"][0]["@"]["MAX_SITES"]) ? $arUpdateList["CLIENT"][0]["@"]["MAX_SITES"] : COption::GetOptionInt("main", "PARAM_MAX_SITES");
										echo ($maxSites > 0 ? $maxSites : GetMessage("SUP_CHECK_PROMT_2"));
									?></td>
								</tr>
								<tr valign="top">
									<td nowrap><?echo GetMessage("SUP_USERS")?>&nbsp;&nbsp;</td>
									<td><?
										$maxUsers = isset($arUpdateList["CLIENT"][0]["@"]["MAX_USERS"]) ? $arUpdateList["CLIENT"][0]["@"]["MAX_USERS"] : COption::GetOptionInt("main", "PARAM_MAX_USERS");
										if (IsModuleInstalled("intranet"))
										{
											if ($maxUsers > 0)
											{
												echo htmlspecialcharsbx($maxUsers);
												echo str_replace("#NUM#", CUpdateClient::GetCurrentNumberOfUsers(), GetMessage("SUP_CURRENT_NUMBER_OF_USERS"));
											}
											else
											{
												echo GetMessage("SUP_USERS_IS_NOT_LIMITED");
												echo " ";
												echo str_replace("#NUM#", CUpdateClient::GetCurrentNumberOfUsers(), GetMessage("SUP_CURRENT_NUMBER_OF_USERS1"));
											}
										}
										elseif (defined("FIRST_EDITION") && constant("FIRST_EDITION") == "Y")
										{
											echo htmlspecialcharsbx($maxUsers);
										}
										else
										{
											echo GetMessage("SUP_CHECK_PROMT_21");
										}
										?></td>
								</tr>
								<tr>
									<td nowrap><?echo GetMessage("SUP_ACTIVE")?>&nbsp;&nbsp;</td>
									<td><?
										$dateFrom = !empty($arUpdateList["CLIENT"][0]["@"]["DATE_FROM"]) ? $arUpdateList["CLIENT"][0]["@"]["DATE_FROM"] : "<i>N/A</i>";
										$dateTo = '';
										if (isset($arUpdateList["CLIENT"][0]["@"]["DATE_TO"]))
										{
											$dateTo = $arUpdateList["CLIENT"][0]["@"]["DATE_TO"];
										}
										elseif (method_exists('\Bitrix\Main\License', 'getExpireDate'))
										{
											$license = new \Bitrix\Main\License();
											$dateTo = (string)$license->getExpireDate();

											if ($dateTo == '' && method_exists('\Bitrix\Main\License', 'getSupportExpireDate'))
											{
												$dateTo = (string)$license->getSupportExpireDate();
											}
										}
										echo GetMessage("SUP_ACTIVE_PERIOD", array("#DATE_FROM#" => $dateFrom, "#DATE_TO#" => ($dateTo != '' ? $dateTo : "<i>N/A</i>")));
									?></td>
								</tr>
								<?if(!empty($arUpdateList["CLIENT"][0]["@"]["B24SUBSC_DATE"])):?>
									<tr>
										<td nowrap><?=($arUpdateList["CLIENT"][0]["@"]["B24SUBSC"] == "T") ? GetMessage("SUP_MARKET_SUBSCRIPTION_DEMO") : GetMessage("SUP_MARKET_SUBSCRIPTION")?>&nbsp;&nbsp;</td>
										<td><?echo ConvertTimeStamp($arUpdateList["CLIENT"][0]["@"]["B24SUBSC_DATE"]);?></td>
									</tr>
								<?endif;?>
								<tr>
									<td nowrap><?echo GetMessage("SUP_SERVER")?>&nbsp;&nbsp;</td>
									<td><?
										$updateHost = isset($arUpdateList["CLIENT"][0]["@"]["HTTP_HOST"]) ? $arUpdateList["CLIENT"][0]["@"]["HTTP_HOST"] : COption::GetOptionString("main", "update_site");
										echo $updateHost != '' ? $updateHost : '<i>N/A</i>';
									?></td>
								</tr>
								<tr>
									<td valign="top" nowrap>
										<?= GetMessage("SUP_SUBI_CHECK") ?>:&nbsp;&nbsp;
									</td>
									<td valign="top">
										<?= COption::GetOptionString("main", "update_system_check", "-") ?>
									</td>
								</tr>
								<tr>
									<td valign="top" nowrap>
										<?= GetMessage("SUP_SUBI_UPD") ?>:&nbsp;&nbsp;
									</td>
									<td valign="top">
										<?= COption::GetOptionString("main", "update_system_update", "-") ?>
									</td>
								</tr>
							</table>

						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<?php
}
//endregion
//region tabs and form header
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="form1">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<?=bitrix_sessid_post()?>

<?
$arTabs = array();
$arTabs[] = array(
	"DIV" => "tab1",
	"TAB" => GetMessage("SUP_TAB_UPDATES"),
	"ICON" => "",
	"TITLE" => GetMessage("SUP_TAB_UPDATES_ALT"),
);
$arTabs[] = array(
	"DIV" => "tab2",
	"TAB" => GetMessage("SUP_TAB_UPDATES_LIST"),
	"ICON" => "",
	"TITLE" => GetMessage("SUP_TAB_UPDATES_LIST_ALT"),
);
if ($isExpertTabEnabled)
{
	$arTabs[] = array(
		"DIV" => "tab_expert",
		"TAB" => GetMessage("SUP_SUAC_EXPERT"),
		"ICON" => "",
		"TITLE" => GetMessage("SUP_SUAC_EXPERT"),
	);
}
$arTabs[] = array(
	"DIV" => "tab_coupon",
	"TAB" => GetMessage("SUP_SUAC_COUP"),
	"ICON" => "",
	"TITLE" => GetMessage("SUP_SUAC_COUP"),
);
$arTabs[] = array(
	"DIV" => "tab3",
	"TAB" => GetMessage("SUP_TAB_SETTINGS"),
	"ICON" => "",
	"TITLE" => GetMessage("SUP_TAB_SETTINGS_ALT"),
);

$tabControl = new CAdminTabControl("tabControl", $arTabs, true, true);
$tabControl->Begin();
// endregion
// region install updates tab
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2">

			<?
			if ($arUpdateList)
			{
				if (isset($arUpdateList["MODULES"]) && is_array($arUpdateList["MODULES"]) && isset($arUpdateList["MODULES"][0]["#"]["MODULE"]) && is_array($arUpdateList["MODULES"][0]["#"]["MODULE"]))
					$countModuleUpdates = count($arUpdateList["MODULES"][0]["#"]["MODULE"]);

				if (isset($arUpdateList["LANGS"]) && is_array($arUpdateList["LANGS"]) && isset($arUpdateList["LANGS"][0]["#"]["INST"]) && is_array($arUpdateList["LANGS"][0]["#"]["INST"]) && is_array($arUpdateList["LANGS"][0]["#"]["INST"][0]["#"]["LANG"]))
					$countLangUpdatesInst = count($arUpdateList["LANGS"][0]["#"]["INST"][0]["#"]["LANG"]);

				if (isset($arUpdateList["LANGS"]) && is_array($arUpdateList["LANGS"]) && isset($arUpdateList["LANGS"][0]["#"]["OTHER"]) && is_array($arUpdateList["LANGS"][0]["#"]["OTHER"]) && is_array($arUpdateList["LANGS"][0]["#"]["OTHER"][0]["#"]["LANG"]))
					$countLangUpdatesOther = count($arUpdateList["LANGS"][0]["#"]["OTHER"][0]["#"]["LANG"]);

				$countTotalImportantUpdates = $countLangUpdatesInst;
				if ($countModuleUpdates > 0)
				{
					for ($i = 0, $cnt = count($arUpdateList["MODULES"][0]["#"]["MODULE"]); $i < $cnt; $i++)
					{
						if (isset($arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["#"]["VERSION"]))
							$countTotalImportantUpdates += count($arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["#"]["VERSION"]);
						if (!array_key_exists($arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["@"]["ID"], $arClientModules))
							$countTotalImportantUpdates += 1;
					}
				}

				if (isset($arUpdateList["HELPS"]) && is_array($arUpdateList["HELPS"]) && isset($arUpdateList["HELPS"][0]["#"]["INST"]) && is_array($arUpdateList["HELPS"][0]["#"]["INST"]) && is_array($arUpdateList["HELPS"][0]["#"]["INST"][0]["#"]["HELP"]))
					$countHelpUpdatesInst = count($arUpdateList["HELPS"][0]["#"]["INST"][0]["#"]["HELP"]);

				if (isset($arUpdateList["HELPS"]) && is_array($arUpdateList["HELPS"]) && isset($arUpdateList["HELPS"][0]["#"]["OTHER"]) && is_array($arUpdateList["HELPS"][0]["#"]["OTHER"]) && is_array($arUpdateList["HELPS"][0]["#"]["OTHER"][0]["#"]["HELP"]))
					$countHelpUpdatesOther = count($arUpdateList["HELPS"][0]["#"]["OTHER"][0]["#"]["HELP"]);

				$newLicenceSignedKey = CUpdateClient::getNewLicenseSignedKey();
				$newLicenceSigned = COption::GetOptionString("main", $newLicenceSignedKey, "N");
				if ($newLicenceSigned !== "Y")
				{
					$bLockControls = true;
					UpdateSystemRenderLicenseIsNotSigned();
				}

				$bLicenseNotFound = false;
				if (!empty($arUpdateList["ERROR"]))
				{
					for ($i = 0, $cntTmp = count($arUpdateList["ERROR"]); $i < $cntTmp; $i++)
					{
						if ($arUpdateList["ERROR"][$i]["@"]["TYPE"] == "LICENSE_NOT_FOUND")
						{
							$bLicenseNotFound = true;
							break;
						}
					}
				}
				$strLicenseKeyTmp = CUpdateClient::GetLicenseKey();
				$bLicenseNotFound = $strLicenseKeyTmp == '' || strtolower($strLicenseKeyTmp) == "demo" || $bLicenseNotFound;
				$bFullVersion = (isset($arUpdateList["CLIENT"][0]["@"]["ENC_TYPE"]) && ($arUpdateList["CLIENT"][0]["@"]["ENC_TYPE"] == "F" || $arUpdateList["CLIENT"][0]["@"]["ENC_TYPE"] == "E" || $arUpdateList["CLIENT"][0]["@"]["ENC_TYPE"] == "T"));

				if ($bLicenseNotFound  || (defined("DEMO") && DEMO == "Y" && !$bFullVersion))
				{
					if($bLicenseNotFound)
						$bLockControls = true;

					UpdateSystemRenderLicenceNotFound($bLicenseNotFound);
				}

				if (!$bLicenseNotFound)
				{
					if (!isset($arUpdateList["UPDATE_SYSTEM"]) && isset($arUpdateList["CLIENT"][0]["@"]["RESERVED"]) && $arUpdateList["CLIENT"][0]["@"]["RESERVED"] == "Y")
					{
						$bLockControls = true;
						UpdateSystemRenderLicenseIsNotActive();
					}
					else
					{
						if (isset($arUpdateList["UPDATE_SYSTEM"]))
						{
							$bLockControls = true;
							UpdateSystemRenderUpdateClient();
						}
					}
				}

				if (empty($errorMessage)
					&& defined("DEMO") && DEMO == "Y"
					&& isset($arUpdateList["CLIENT"]) && !isset($arUpdateList["UPDATE_SYSTEM"])
					&& ($arUpdateList["CLIENT"][0]["@"]["ENC_TYPE"] == "F" || $arUpdateList["CLIENT"][0]["@"]["ENC_TYPE"] == "E" || $arUpdateList["CLIENT"][0]["@"]["ENC_TYPE"] == "T"))
				{
					UpdateSystemRenderRegisterProduct($bLockControls);
				}

				if (empty($errorMessage)
					&& defined("ENCODE") && ENCODE=="Y"
					&& isset($arUpdateList["CLIENT"]) && !isset($arUpdateList["UPDATE_SYSTEM"])
					&& ($arUpdateList["CLIENT"][0]["@"]["ENC_TYPE"] == "F"))
				{
					UpdateSystemRenderGetSources($bLockControls, $countModuleUpdates);
				}
				?>


				<?
				if (isset($_REQUEST[_32763223666625(0)]) && ($_REQUEST[_32763223666625(0)] == "Y") && isset($arUpdateList["CLIENT"]) && !isset($arUpdateList["UPDATE_SYSTEM"]))
				{
					UpdateSystemRenderSupport($bLockControls, $arClientModules);
				}
				// region updates install
				?>

				<div id="upd_success_div" style="display:none">
					<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
						<tr class="heading">
							<td><B><?= GetMessage("SUP_SUB_SUCCESS") ?></B></td>
						</tr>
						<tr>
							<td valign="top"><div id="upd_success_div_text"></div></td>
						</tr>
					</table>
				</div>

				<div id="upd_error_div" style="display:none">
					<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
						<tr class="heading">
							<td><B><?= GetMessage("SUP_SUB_ERROR") ?></B></td>
						</tr>
						<tr>
							<td valign="top"><div id="upd_error_div_text"></div></td>
						</tr>
					</table>
				</div>

				<div id="upd_install_div" style="display:none">
					<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
						<tr class="heading">
							<td><B><?= GetMessage("SUP_SUB_PROGRESS") ?></B></td>
						</tr>
						<tr>
							<td valign="top">
								<table border="0" cellspacing="5" cellpadding="3" width="100%">
									<tr>
										<td valign="top" width="5%">
										</td>
										<td valign="top">
											<div style="top:0px; left:0px; width:300px; height:15px; background-color:#365069; font-size:1px;">
											<div style="position:relative; top:1px; left:1px; width:298px; height:13px; background-color:#ffffff; font-size:1px;">
											<div id="PBdoneD" style="position:relative; top:0px; left:0px; width:0px; height:13px; background-color:#D5E7F3; font-size:1px;">
											</div></div></div>
											<br>
											<div style="top:0px; left:0px; width:300px; height:15px; background-color:#365069; font-size:1px;">
											<div style="position:relative; top:1px; left:1px; width:298px; height:13px; background-color:#ffffff; font-size:1px;">
											<div id="PBdone" style="position:relative; top:0px; left:0px; width:0px; height:13px; background-color:#D5E7F3; font-size:1px;">
											</div></div></div>
											<br>
											<div id="install_progress_hint"></div>
										</td>
										<td valign="top" align="right">
											<input TYPE="button" NAME="stop_updates" id="id_stop_updates" value="<?= GetMessage("SUP_SUB_STOP") ?>" onclick="StopUpdates()">
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>

				<div id="upd_select_div" style="display:block">
					<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
						<tr class="heading">
							<td><B><?= ($countModuleUpdates > 0 || $countLangUpdatesInst > 0) ? GetMessage("SUP_SU_TITLE1") : GetMessage("SUP_SU_TITLE2") ?></B></td>
						</tr>
						<tr>
							<td valign="top">
									<table cellpadding="0" cellspacing="0">
										<tr>
											<td class="icon-new"><div class="icon icon-main"></div></td>
											<td>
								<b><?= GetMessage("SUP_SU_RECOMEND") ?>:</b>
								<?
								$bComma = false;
								if ($countModuleUpdates > 0)
								{
									echo str_replace("#NUM#", $countModuleUpdates, GetMessage("SUP_SU_RECOMEND_MOD"));
									$bComma = true;
								}
								if ($countLangUpdatesInst > 0)
								{
									if ($bComma)
										echo ", ";
									echo str_replace("#NUM#", $countLangUpdatesInst, GetMessage("SUP_SU_RECOMEND_LAN"));
									$bComma = true;
								}
								if ($countModuleUpdates <= 0 && $countLangUpdatesInst <= 0)
									echo GetMessage("SUP_SU_RECOMEND_NO");

								if ($countLangUpdatesOther > 0 || $countHelpUpdatesOther > 0 || $countHelpUpdatesInst > 0)
								{
									echo "<br>";
									echo "<b>".GetMessage("SUP_SU_OPTION").":</b> ";
									$bComma = false;
									if ($countLangUpdatesOther > 0)
									{
										echo str_replace("#NUM#", $countLangUpdatesOther, GetMessage("SUP_SU_OPTION_LAN"));
										$bComma = true;
									}
									if ($countHelpUpdatesOther > 0 || $countHelpUpdatesInst > 0)
									{
										if ($bComma)
											echo ", ";
										echo str_replace("#NUM#", $countHelpUpdatesOther + $countHelpUpdatesInst, GetMessage("SUP_SU_OPTION_HELP"));
									}
								}
								?>
								<br><br>
								<input TYPE="button" ID="install_updates_button" NAME="install_updates"<?= (($countModuleUpdates <= 0 && $countLangUpdatesInst <= 0 || $bLockControls) ? " disabled" : "") ?> value="<?= GetMessage("SUP_SU_UPD_BUTTON") ?>" onclick="InstallUpdates()">
								<br><br>
								<span id="id_view_updates_list_span"><a id="id_view_updates_list" href="javascript:tabControl.SelectTab('tab2');"><?= GetMessage("SUP_SU_UPD_VIEW") ?></a></span>
								<br><br>
								<?= GetMessage("SUP_SU_UPD_HINT_CHECK") ?>
								<br><br>
								<?
								$m = "";
								if ($stableVersionsOnly === "Y")
								{
									$m = GetMessage("SUP_STABLE_ON_PROMT");
								}
								elseif ($stableVersionsOnly === "N")
								{
									$m = GetMessage("SUP_STABLE_OFF_PROMT");
								}
								elseif (is_numeric($stableVersionsOnly) && isset($arUpdateList["AVAILABLE_VERSIONS"]) && is_array($arUpdateList["AVAILABLE_VERSIONS"]) && isset($arUpdateList["AVAILABLE_VERSIONS"][0]["#"]["VERSIONS"]) && is_array($arUpdateList["AVAILABLE_VERSIONS"][0]["#"]["VERSIONS"]))
								{
									foreach ($arUpdateList["AVAILABLE_VERSIONS"][0]["#"]["VERSIONS"] as $versions)
									{
										if (intval($versions["@"]["ID"]) === intval($stableVersionsOnly))
										{
											$m = "<b>".GetMessage("SUP_SU_UPD_HINT_CHECK_VERS", array("#NAME#" => $versions["@"]["NAME"]))."</b><br><br>";
											$m .= (($versions["@"]["IS_STABLE"] === "Y") ? GetMessage("SUP_STABLE_ON_PROMT") : GetMessage("SUP_STABLE_OFF_PROMT"));
											break;
										}
									}
								}
								else
								{
									$m = GetMessage("SUP_STABLE_ON_PROMT");
								}

								echo $m;
								?>
								<br><br>
								<?= GetMessage("SUP_SU_UPD_HINT") ?>
											</td>
										</tr>
									</table>
							</td>
						</tr>
					</table>
				</div>
				<?
				//endregion
			}
			?>

		</td>
	</tr>
	<tr>
		<td colspan="2">
			<br>
				<?php
				UpdateSystemRenderServerResponse($arUpdateList);
				?>
		</td>
	</tr>

<?
$tabControl->EndTab();
// endregion
// region updates list tab
$tabControl->BeginNextTab();
?>

	<tr>
		<td colspan="2">

			<table border="0" cellspacing="1" cellpadding="3" width="100%">
				<tr>
					<td>
						<?= GetMessage("SUP_SULL_CNT") ?>: <?= $countModuleUpdates + $countLangUpdatesInst + $countLangUpdatesOther + $countHelpUpdatesOther + $countHelpUpdatesInst ?><BR><BR>
						<input TYPE="button" ID="install_updates_sel_button" NAME="install_updates"<?= (($countModuleUpdates <= 0 && $countLangUpdatesInst <= 0) ? " disabled" : "") ?> value="<?= GetMessage("SUP_SULL_BUTTON") ?>" onclick="InstallUpdatesSel()">
					</td>
				</tr>
			</table>
			<br>

			<?
			if ($arUpdateList)
			{
				?>
				<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal" id="table_updates_sel_list">
					<tr>
						<td class="heading"><INPUT TYPE="checkbox" NAME="select_all" id="id_select_all" title="<?= GetMessage("SUP_SULL_CBT") ?>" onClick="SelectAllRows(this);"></td>
						<td class="heading"><B><?= GetMessage("SUP_SULL_NAME") ?></B></td>
						<td class="heading"><B><?= GetMessage("SUP_SULL_TYPE") ?></B></td>
						<td class="heading"><B><?= GetMessage("SUP_SULL_REL") ?></B></td>
						<td class="heading"><B><?= GetMessage("SUP_SULL_NOTE") ?></B></td>
					</tr>
					<?
					if (isset($arUpdateList["MODULES"][0]["#"]["MODULE"]) || isset($arUpdateList["LANGS"][0]["#"]["INST"]))
					{
						?>
						<tr>
							<td colspan="5"><?= GetMessage("SUP_SU_RECOMEND") ?></td>
						</tr>
						<?
					}
					if (isset($arUpdateList["MODULES"][0]["#"]["MODULE"]))
					{
						for ($i = 0, $cnt = count($arUpdateList["MODULES"][0]["#"]["MODULE"]); $i < $cnt; $i++)
						{
							$arModuleTmp = $arUpdateList["MODULES"][0]["#"]["MODULE"][$i];
							$arModuleTmp["@"]["ID"] = preg_replace("#[^A-Za-z0-9._-]#", "", $arModuleTmp["@"]["ID"]);

							$strTitleTmp = $arModuleTmp["@"]["NAME"]." (".$arModuleTmp["@"]["ID"].")\n".$arModuleTmp["@"]["DESCRIPTION"]."\n";
							if (isset($arModuleTmp["#"]["VERSION"]) && is_array($arModuleTmp["#"]["VERSION"]))
							{
								for ($j = 0, $cntj = count($arModuleTmp["#"]["VERSION"]); $j < $cntj; $j++)
									$strTitleTmp .= str_replace("#VER#", $arModuleTmp["#"]["VERSION"][$j]["@"]["ID"], GetMessage("SUP_SULL_VERSION"))."\n".$arModuleTmp["#"]["VERSION"][$j]["#"]["DESCRIPTION"][0]["#"]."\n";
							}
							$strTitleTmp = htmlspecialcharsbx(preg_replace("/<.+?>/i", "", $strTitleTmp));
							?>
							<tr title="<?= $strTitleTmp ?>" ondblclick="ShowDescription('<?= CUtil::JSEscape(htmlspecialcharsbx($arModuleTmp["@"]["ID"])) ?>')">
								<td><INPUT TYPE="checkbox" NAME="select_module_<?= CUtil::JSEscape(htmlspecialcharsbx($arModuleTmp["@"]["ID"])) ?>" value="Y" onClick="ModuleCheckboxClicked(this, '<?= CUtil::JSEscape(htmlspecialcharsbx($arModuleTmp["@"]["ID"])) ?>', new Array());" checked id="id_select_module_<?= CUtil::JSEscape(htmlspecialcharsbx($arModuleTmp["@"]["ID"])) ?>"></td>
								<td><label for="id_select_module_<?= CUtil::JSEscape(htmlspecialcharsbx($arModuleTmp["@"]["ID"])) ?>"><?= str_replace("#NAME#", htmlspecialcharsbx($arModuleTmp["@"]["NAME"]), GetMessage("SUP_SULL_MODULE")) ?></label></td>
								<td><?= (array_key_exists($arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["@"]["ID"], $arClientModules) ? GetMessage("SUP_SULL_REF_O") : GetMessage("SUP_SULL_REF_N")) ?></td>
								<td><?= (isset($arModuleTmp["#"]["VERSION"]) ? $arModuleTmp["#"]["VERSION"][count($arModuleTmp["#"]["VERSION"]) - 1]["@"]["ID"] : "") ?></td>
								<td><a href="javascript:ShowDescription('<?= CUtil::JSEscape(htmlspecialcharsbx($arModuleTmp["@"]["ID"])) ?>')"><?= GetMessage("SUP_SULL_NOTE_D") ?></a></td>
							</tr>
							<?
						}
					}
					if (isset($arUpdateList["LANGS"][0]["#"]["INST"]))
					{
						for ($i = 0, $cnt = count($arUpdateList["LANGS"][0]["#"]["INST"][0]["#"]["LANG"]); $i < $cnt; $i++)
						{
							$arLangTmp = $arUpdateList["LANGS"][0]["#"]["INST"][0]["#"]["LANG"][$i];
							?>
							<tr>
								<td><INPUT TYPE="checkbox" NAME="select_lang_<?= htmlspecialcharsbx($arLangTmp["@"]["ID"]) ?>" value="Y" onClick="EnableInstallButton(this);" checked id="id_select_lang_<?= htmlspecialcharsbx($arLangTmp["@"]["ID"]) ?>"></td>
								<td><label for="id_select_lang_<?= htmlspecialcharsbx($arLangTmp["@"]["ID"]) ?>"><?= str_replace("#NAME#", htmlspecialcharsbx($arLangTmp["@"]["NAME"]), GetMessage("SUP_SULL_LANG")) ?></label></td>
								<td><?= GetMessage("SUP_SULL_REF_O") ?></td>
								<td><?= $arLangTmp["@"]["DATE"] ?></td>
								<td>&nbsp;</td>
							</tr>
							<?
						}
					}
					if (isset($arUpdateList["LANGS"][0]["#"]["OTHER"]) || isset($arUpdateList["HELPS"][0]["#"]["OTHER"]) || isset($arUpdateList["HELPS"][0]["#"]["INST"]))
					{
						?>
						<tr>
							<td colspan="5"><?= GetMessage("SUP_SU_OPTION") ?></td>
						</tr>
						<?
					}
					if (isset($arUpdateList["HELPS"][0]["#"]["INST"]))
					{
						for ($i = 0, $cnt = count($arUpdateList["HELPS"][0]["#"]["INST"][0]["#"]["HELP"]); $i < $cnt; $i++)
						{
							$arHelpTmp = $arUpdateList["HELPS"][0]["#"]["INST"][0]["#"]["HELP"][$i];
							?>
							<tr>
								<td><INPUT TYPE="checkbox" NAME="select_help_<?= htmlspecialcharsbx($arHelpTmp["@"]["ID"]) ?>" value="Y" onClick="EnableInstallButton(this);" id="id_select_help_<?= htmlspecialcharsbx($arHelpTmp["@"]["ID"]) ?>"></td>
								<td><label for="id_select_help_<?= htmlspecialcharsbx($arHelpTmp["@"]["ID"]) ?>"><?= str_replace("#NAME#", htmlspecialcharsbx($arHelpTmp["@"]["NAME"]), GetMessage("SUP_SULL_HELP")) ?></label></td>
								<td><?= GetMessage("SUP_SULL_REF_O") ?></td>
								<td><?= $arHelpTmp["@"]["DATE"] ?></td>
								<td>&nbsp;</td>
							</tr>
							<?
						}
					}
					if (isset($arUpdateList["LANGS"][0]["#"]["OTHER"]))
					{
						for ($i = 0, $cnt = count($arUpdateList["LANGS"][0]["#"]["OTHER"][0]["#"]["LANG"]); $i < $cnt; $i++)
						{
							$arLangTmp = $arUpdateList["LANGS"][0]["#"]["OTHER"][0]["#"]["LANG"][$i];
							?>
							<tr>
								<td><INPUT TYPE="checkbox" NAME="select_lang_<?= htmlspecialcharsbx($arLangTmp["@"]["ID"]) ?>" value="Y" onClick="EnableInstallButton(this);" id="id_select_lang_<?= htmlspecialcharsbx($arLangTmp["@"]["ID"]) ?>"></td>
								<td><label for="id_select_lang_<?= htmlspecialcharsbx($arLangTmp["@"]["ID"]) ?>"><?= str_replace("#NAME#", htmlspecialcharsbx($arLangTmp["@"]["NAME"]), GetMessage("SUP_SULL_LANG")) ?></label></td>
								<td><?= GetMessage("SUP_SULL_ADD") ?></td>
								<td><?= $arLangTmp["@"]["DATE"] ?></td>
								<td>&nbsp;</td>
							</tr>
							<?
						}
					}
					if (isset($arUpdateList["HELPS"][0]["#"]["OTHER"]))
					{
						for ($i = 0, $cnt = count($arUpdateList["HELPS"][0]["#"]["OTHER"][0]["#"]["HELP"]); $i < $cnt; $i++)
						{
							$arHelpTmp = $arUpdateList["HELPS"][0]["#"]["OTHER"][0]["#"]["HELP"][$i];
							?>
							<tr>
								<td><INPUT TYPE="checkbox" NAME="select_help_<?= htmlspecialcharsbx($arHelpTmp["@"]["ID"]) ?>" value="Y" onClick="EnableInstallButton(this);" id="id_select_help_<?= htmlspecialcharsbx($arHelpTmp["@"]["ID"]) ?>"></td>
								<td><label for="id_select_help_<?= htmlspecialcharsbx($arHelpTmp["@"]["ID"]) ?>"><?= str_replace("#NAME#", htmlspecialcharsbx($arHelpTmp["@"]["NAME"]), GetMessage("SUP_SULL_HELP")) ?></label></td>
								<td><?= GetMessage("SUP_SULL_ADD1") ?></td>
								<td><?= $arHelpTmp["@"]["DATE"] ?></td>
								<td>&nbsp;</td>
							</tr>
							<?
						}
					}
					?>
				</table>
				<?
			}
			?>
		</td>
	</tr>

<?
$tabControl->EndTab();
// endregion
// region expert tab
if ($isExpertTabEnabled)
{
	$tabControl->BeginNextTab();
	try
	{
		include ($expertTabFile);
	}
	catch (\Exception $e)
	{
		echo GetMessage('SUP_SUAC_EXPERT_ERROR');
	}
	catch (\Error $e)
	{
		echo GetMessage('SUP_SUAC_EXPERT_ERROR');
	}
	$tabControl->EndTab();
}
//endregion
// region coupon activation tab
$tabControl->BeginNextTab();
?>

	<tr>
		<td colspan="2">

			<?
			if (!$bLockUpdateSystemKernel)
			{
				?>
				<div id="upd_add_coupon_div">
					<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
						<tr class="heading">
							<td><B><?= GetMessage("SUP_SUAC_COUP1") ?></B></td>
						</tr>
						<tr>
							<td>
										<table cellpadding="0" cellspacing="0">
											<tr>
												<td class="icon-new"><div class="icon icon-licence"></div></td>
												<td>
													<?if (isset($arUpdateList["CLIENT"][0]["@"]["MAX_SITES"]) && intval($arUpdateList["CLIENT"][0]["@"]["MAX_SITES"]) > 0):?>
														<?= str_replace("#NUM#", $arUpdateList["CLIENT"][0]["@"]["MAX_SITES"], GetMessage("SUP_SUAC_LIMIT")) ?>
													<?else:?>
														<?= GetMessage("SUP_CHECK_PROMT_2") ?>
													<?endif;?>
													<br><br>
													<?if (isset($arUpdateList["CLIENT"][0]["@"]["MAX_USERS"]) && intval($arUpdateList["CLIENT"][0]["@"]["MAX_USERS"]) > 0):?>
														<?= str_replace("#NUM#", $arUpdateList["CLIENT"][0]["@"]["MAX_USERS"], GetMessage("SUP_SUAC_LIMIT1")) ?>
													<?else:?>
														<?= GetMessage("SUP_CHECK_PROMT_21") ?>
													<?endif;?>
													<br><br>
													<?= GetMessage("SUP_SUAC_HINT") ?>
													<br><br>
													<?= GetMessage("SUP_SUAC_PROMT") ?>:<br>
													<INPUT TYPE="text" ID="id_coupon" NAME="COUPON" value="" size="35">
													<input TYPE="button" ID="id_coupon_btn" NAME="coupon_btn" value="<?= GetMessage("SUP_SUAC_BUTTON") ?>" onclick="ActivateCoupon()">
												</td>
											</tr>
										</table>
							</td>
						</tr>
					</table>
				</div>
				<SCRIPT LANGUAGE="JavaScript">
				<!--
				function ActivateCoupon()
				{
					document.getElementById("id_coupon_btn").disabled = true;
					ShowWaitWindow();

					CHttpRequest.Action = function(result)
					{
						CloseWaitWindow();
						result = PrepareString(result);
						if (result == "Y")
						{
							alert("<?= GetMessageJS("SUP_SUAC_SUCCESS") ?>");
							window.location.href = "update_system.php?lang=<?= LANG ?>";
						}
						else
						{
							alert("<?= GetMessageJS("SUP_SUAC_ERROR") ?>: " + result);
							document.getElementById("id_coupon_btn").disabled = false;
						}
					}

					var param = document.getElementById("id_coupon").value;

					if (param.length > 0)
					{
						updRand++;
						CHttpRequest.Send('/bitrix/admin/update_system_act.php?query_type=coupon&<?= bitrix_sessid_get() ?>&COUPON=' + encodeURIComponent(param) + "&updRand=" + updRand);
					}
					else
					{
						document.getElementById("id_coupon_btn").disabled = false;
						CloseWaitWindow();
						alert("<?= GetMessageJS("SUP_SUAC_NO_COUP") ?>");
					}
				}
				//-->
				</SCRIPT>
				<?
			}
			?>
		</td>
	</tr>

<?
$tabControl->EndTab();
// endregion
// region additional tab
$tabControl->BeginNextTab();
?>

	<tr>
		<td colspan="2">

			<?
			if (!$bLockUpdateSystemKernel)
			{
				?>
				<div id="upd_stability_div">
					<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
						<tr class="heading">
							<td><B><?= GetMessage("SUP_SUBV_BETA") ?></B></td>
						</tr>
						<tr>
							<td>
								<table cellpadding="0" cellspacing="0">
									<tr>
										<td class="icon-new"><div class="icon icon-beta"></div></td>
										<td>
								<?
								$m = "";
								if ($stableVersionsOnly === "Y")
								{
									$m = GetMessage("SUP_STABLE_ON_PROMT");
								}
								elseif ($stableVersionsOnly === "N")
								{
									$m = GetMessage("SUP_STABLE_OFF_PROMT");
								}
								elseif (is_numeric($stableVersionsOnly) && isset($arUpdateList["AVAILABLE_VERSIONS"]) && is_array($arUpdateList["AVAILABLE_VERSIONS"]) && isset($arUpdateList["AVAILABLE_VERSIONS"][0]["#"]["VERSIONS"]) && is_array($arUpdateList["AVAILABLE_VERSIONS"][0]["#"]["VERSIONS"]))
								{
									foreach ($arUpdateList["AVAILABLE_VERSIONS"][0]["#"]["VERSIONS"] as $versions)
									{
										if (intval($versions["@"]["ID"]) === intval($stableVersionsOnly))
										{
											$m = "<b>".GetMessage("SUP_SU_UPD_HINT_CHECK_VERS", array("#NAME#" => $versions["@"]["NAME"]))."</b><br><br>";
											$m .= (($versions["@"]["IS_STABLE"] === "Y") ? GetMessage("SUP_STABLE_ON_PROMT") : GetMessage("SUP_STABLE_OFF_PROMT"));
											break;
										}
									}
								}
								else
								{
									$m = GetMessage("SUP_STABLE_ON_PROMT");
								}

								echo $m;
								?>
								<br><br>
								<?= GetMessage("SUP_SUBV_HINT") ?><br><br>
								<select id="id_stable_select" name="stable_select" onchange="SwithStability()">
									<option value="Y"<?= ($stableVersionsOnly === "Y") ? " selected" : ""; ?>><?= GetMessage("SUP_SUBV_STABB") ?></option>
									<option value="N"<?= ($stableVersionsOnly === "N") ? " selected" : ""; ?>><?= GetMessage("SUP_SUBV_BETB") ?></option>
									<?
									if (isset($arUpdateList["AVAILABLE_VERSIONS"]) && is_array($arUpdateList["AVAILABLE_VERSIONS"]) && isset($arUpdateList["AVAILABLE_VERSIONS"][0]["#"]["VERSIONS"]) && is_array($arUpdateList["AVAILABLE_VERSIONS"][0]["#"]["VERSIONS"]))
									{
										foreach ($arUpdateList["AVAILABLE_VERSIONS"][0]["#"]["VERSIONS"] as $versions)
										{
											?><option value="<?= intval($versions["@"]["ID"]) ?>"<?= (intval($versions["@"]["ID"]) === intval($stableVersionsOnly)) ? " selected" : "";?>><?
												echo htmlspecialcharsbx($versions["@"]["NAME"]);
												if ($versions["@"]["IS_STABLE"] === "N")
													echo " (beta version)";
											?></option><?
										}
									}
									?>
								</select>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
				<SCRIPT LANGUAGE="JavaScript">
				<!--
				function SwithStability()
				{
					var sel = document.getElementById("id_stable_select");
					sel.disabled = true;
					ShowWaitWindow();

					CHttpRequest.Action = function(result)
					{
						result = PrepareString(result);
						if (result == "Y")
						{
							window.location.href = "update_system.php?lang=<?= LANG ?>";
						}
						else
						{
							CloseWaitWindow();
							alert("<?= GetMessageJS("SUP_SUBV_ERROR") ?>: " + result);
							sel.disabled = false;
						}
					}

					updRand++;
					CHttpRequest.Send('/bitrix/admin/update_system_act.php?query_type=stability&<?= bitrix_sessid_get() ?>&STABILITY=' + encodeURIComponent(sel.options[sel.selectedIndex].value) + "&updRand=" + updRand);
				}
				//-->
				</SCRIPT>

				<BR>

				<div id="upd_mail_div">
					<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
						<tr class="heading">
							<td><B><?= GetMessage("SUP_SUSU_TITLE") ?></B></td>
						</tr>
						<tr>
							<td>
										<table cellpadding="0" cellspacing="0">
											<tr>
												<td class="icon-new"><div class="icon icon-subscribe"></div></td>
												<td>
								<?= GetMessage("SUP_SUSU_HINT") ?>
								<br><br>
								<?= GetMessage("SUP_SUSU_EMAIL") ?>: <br>
								<INPUT TYPE="text" ID="id_email" NAME="EMAIL" value="" size="35">
								<input TYPE="button" ID="id_email_btn" NAME="email_btn" value="<?= GetMessage("SUP_SUSU_BUTTON") ?>" onclick="SubscribeMail()">
												</td>
											</tr>
										</table>
							</td>
						</tr>
					</table>
				</div>
				<SCRIPT LANGUAGE="JavaScript">
				<!--
				function SubscribeMail()
				{
					document.getElementById("id_email_btn").disabled = true;
					ShowWaitWindow();

					CHttpRequest.Action = function(result)
					{
						CloseWaitWindow();
						result = PrepareString(result);

						document.getElementById("id_email_btn").disabled = false;
						if (result == "Y")
						{
							alert("<?= GetMessageJS("SUP_SUSU_SUCCESS") ?>");
						}
						else
						{
							alert("<?= GetMessageJS("SUP_SUSU_ERROR") ?>: " + result);
						}
					}

					var param = document.getElementById("id_email").value;

					if (param.length > 0)
					{
						updRand++;
						CHttpRequest.Send('/bitrix/admin/update_system_act.php?query_type=mail&<?= bitrix_sessid_get() ?>&EMAIL=' + encodeURIComponent(param) + "&updRand=" + updRand);
					}
					else
					{
						CloseWaitWindow();
						document.getElementById("id_email_btn").disabled = false;
						alert("<?= GetMessageJS("SUP_SUSU_NO_EMAIL") ?>");
					}
				}
				//-->
				</SCRIPT>
				<?
			}
			?>
		</td>
	</tr>

<?
$tabControl->EndTab();
$tabControl->End();
// endregion
// region javascript
?>
<script language="JavaScript">
	<!--
	var updRand = 0;
	var modulesList = new Array();
	<?
	$i = 0;
	foreach ($arClientModules as $key => $value)
		echo "modulesList[".($i++)."] = \"".$key."\";";
	?>
	var modulesListSupport = new Array();

	var arModuleUpdatesDescr = {<?
	if (isset($arUpdateList["MODULES"][0]["#"]["MODULE"]))
	{
		for ($i = 0, $cnt = count($arUpdateList["MODULES"][0]["#"]["MODULE"]); $i < $cnt; $i++)
		{
			$arModuleTmp = $arUpdateList["MODULES"][0]["#"]["MODULE"][$i];

			$strTitleTmp = '<h2>'.$arModuleTmp["@"]["NAME"].' ('.$arModuleTmp["@"]["ID"].')'.'</h2>';
			$strTitleTmp .= '<p>'.$arModuleTmp["@"]["DESCRIPTION"].'</p>';

			if (isset($arModuleTmp["#"]["VERSION"]))
			{
				for ($j = count($arModuleTmp["#"]["VERSION"]) - 1; $j >= 0; $j--)
				{
					$strTitleTmp .= '<p><b>';
					$strTitleTmp .= str_replace("#VER#", $arModuleTmp["#"]["VERSION"][$j]["@"]["ID"], GetMessage("SUP_SULL_VERSION"));
					$strTitleTmp .= '</b><br />';
					$strTitleTmp .= $arModuleTmp["#"]["VERSION"][$j]["#"]["DESCRIPTION"][0]["#"];
					$strTitleTmp .= '</p>';
				}
			}

			$strTitleTmp = CUtil::JSEscape(preg_replace("/\r?\n/i", "<br>", $strTitleTmp));
			if ($i > 0)
				echo ",\n";
			echo "\"".CUtil::JSEscape(htmlspecialcharsbx($arModuleTmp["@"]["ID"]))."\" : \"".$strTitleTmp."\"";
		}
	}
	?>};

	var arModuleUpdatesCnt = {<?
	if ($countModuleUpdates > 0)
	{
		for ($i = 0, $cnt = count($arUpdateList["MODULES"][0]["#"]["MODULE"]); $i < $cnt; $i++)
		{
			if ($i > 0)
				echo ", ";
			echo "\"".$arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["@"]["ID"]."\" : ";
			if (isset($arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["#"]["VERSION"])
				&& is_array($arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["#"]["VERSION"]))
			{
				if (!array_key_exists($arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["@"]["ID"], $arClientModules))
					echo count($arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["#"]["VERSION"]) + 1;
				else
					echo count($arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["#"]["VERSION"]);
			}
			else
			{
				echo "0";
			}
		}
	}
	?>};

	var arModuleUpdatesControl = {<?
	if ($countModuleUpdates > 0)
	{
		for ($i = 0, $cnt = count($arUpdateList["MODULES"][0]["#"]["MODULE"]); $i < $cnt; $i++)
		{
			if ($i > 0)
				echo ", ";
			echo "\"".$arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["@"]["ID"]."\" : [";
			$bFlagTmp = false;
			if (isset($arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["#"]["VERSION"])
				&& is_array($arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["#"]["VERSION"]))
			{
				for ($i1 = 0, $cnt1 = count($arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["#"]["VERSION"]); $i1 < $cnt1; $i1++)
				{
					if (isset($arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["#"]["VERSION"][$i1]["#"]["VERSION_CONTROL"]) && is_array($arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["#"]["VERSION"][$i1]["#"]["VERSION_CONTROL"]))
					{
						for ($i2 = 0, $cnt2 = count($arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["#"]["VERSION"][$i1]["#"]["VERSION_CONTROL"]); $i2 < $cnt2; $i2++)
						{
							if ($bFlagTmp)
								echo ", ";
							echo "\"".$arUpdateList["MODULES"][0]["#"]["MODULE"][$i]["#"]["VERSION"][$i1]["#"]["VERSION_CONTROL"][$i2]["@"]["MODULE"]."\"";
							$bFlagTmp = true;
						}
					}
				}
			}
			echo "]";
		}
	}
	?>};


	function PrepareString(str)
	{
		str = str.replace(/^\s+|\s+$/, '');
		while (str.length > 0 && str.charCodeAt(0) == 65279)
			str = str.substring(1);
		return str;
	}

	//region sign licence
	function ShowLicence()
	{
		if (document.getElementById("licence_float_div"))
			return;

		LockControls();

		var div = document.body.appendChild(document.createElement("DIV"));

		div.id = "licence_float_div";
		div.className = "settings-float-form";
		div.style.position = 'absolute';

		var txt = '<div class="title">';
		txt += '<table cellspacing="0" width="100%">';
		txt += '<tr>';
		txt += '<td width="100%" class="title-text" onmousedown="jsFloatDiv.StartDrag(arguments[0], document.getElementById(\'licence_float_div\'));"><?= GetMessageJS("SUP_SUBT_LICENCE") ?></td>';
		txt += '<td width="0%"><a class="close" href="javascript:CloseLicenceTextWindow();" title="<?= GetMessageJS("SUP_SULD_CLOSE") ?>"></a></td>';
		txt += '</tr>';
		txt += '</table>';
		txt += '</div>';
		txt += '<div class="content">';
		txt += '<form name="license_form">';
		txt += '<h2><?= GetMessageJS("SUP_SUBT_LICENCE") ?></h2>';
		txt += '<table cellspacing="0"><tr><td>';
		txt += '<iframe name="license_text" src="<?= CUpdateClient::getLicenseTextPath() ?>" style="width:770px; height:450px; display:block;"></iframe>';
		txt += '</td></tr><tr><td>';
		txt += '<input name="agree_license" type="checkbox" value="Y" id="agree_license_id" onclick="AgreeLicenceCheckbox(this)">';
		txt += '<label for="agree_license_id"><?= GetMessageJS("SUP_SUBT_AGREE") ?></label>';
		txt += '</td></tr></table>';
		txt += '</form>';
		txt += '</div>';
		txt += '<div class="buttons">';
		txt += '<input type="button" value="<?= GetMessageJS("SUP_APPLY") ?>" disabled id="licence_agree_button" onclick="AgreeLicence()" title="<?= GetMessageJS("SUP_APPLY") ?>">';
		txt += '</div>';

		div.innerHTML = txt;

		var left = parseInt(document.body.scrollLeft + document.body.clientWidth/2 - div.offsetWidth/2);
		var top = parseInt(document.body.scrollTop + document.body.clientHeight/2 - div.offsetHeight/2);

		jsFloatDiv.Show(div, left, top);

		jsUtils.addEvent(document, "keypress", LicenceTextOnKeyPress);
	}

	function LicenceTextOnKeyPress(e)
	{
		if (!e)
			e = window.event;
		if (!e)
			return;
		if (e.keyCode == 27)
			CloseLicenceTextWindow();
	}

	function CloseLicenceTextWindow()
	{
		jsUtils.removeEvent(document, "keypress", LicenceTextOnKeyPress);
		var div = document.getElementById("licence_float_div");
		jsFloatDiv.Close(div);
		div.parentNode.removeChild(div);
	}

	function AgreeLicenceCheckbox(checkbox)
	{
		var lab = document.getElementById("licence_agree_button");
		lab.disabled=<?if(!$USER->CanDoOperation('install_updates')):?>true<?else:?>!checkbox.checked<?endif;?>;
	}

	function AgreeLicence()
	{
		ShowWaitWindow();

		CHttpRequest.Action = function(result)
		{
			result = PrepareString(result);

			CloseWaitWindow();

			if (result == "Y")
			{
				CloseLicence();
				var udl = document.getElementById("upd_licence_div");
				udl.style["display"] = "none";
				<?if (empty($errorMessage)){?>UnLockControls();<?}?>
			}
			else
			{
				alert("<?= GetMessage("SUP_SUBT_ERROR_LICENCE") ?>");
			}
		}

		updRand++;
		CHttpRequest.Send('/bitrix/admin/update_system_act.php?query_type=licence&<?= bitrix_sessid_get() ?>&updRand=' + updRand);
	}

	function CloseLicence()
	{
		var div = document.getElementById("licence_float_div");
		jsFloatDiv.Close(div);
		div.parentNode.removeChild(div);
	}
	//endregion
	// region license not found
	function ShowLicenceKeyForm()
	{

		if (document.getElementById("key_float_div"))
			return;

		LockControls();

		var div = document.body.appendChild(document.createElement("DIV"));

		div.id = "key_float_div";
		div.className = "settings-float-form";
		div.style.position = 'absolute';

		var txt = '<div class="title">';
		txt += '<table cellspacing="0" width="100%">';
		txt += '<tr>';
		txt += '<td width="100%" class="title-text" onmousedown="jsFloatDiv.StartDrag(arguments[0], document.getElementById(\'key_float_div\'));"><?= GetMessage("SUP_SUBK_KEY") ?></td>';
		txt += '<td width="0%"><a class="close" href="javascript:CloseLicenceWindow();" title="<?= GetMessage("SUP_SULD_CLOSE") ?>"></a></td>';
		txt += '</tr>';
		txt += '</table>';
		txt += '</div>';
		txt += '<div class="content">';
		txt += '<form name="licence_key_form" onsubmit="LicenceKeyFormSubmit(); return false;">';
		txt += '<h2><?= GetMessage("SUP_SUBK_KEY") ?></h2>';
		txt += '<table cellspacing="0">';
		txt += '<tr>';
		txt += '	<td width="50%"><span class="required">*</span><?= GetMessage("SUP_SUBK_PROMT") ?>:</td>';
		txt += '	<td width="50%"><input type="text" id="id_new_license_key" name="NEW_LICENSE_KEY" value="" size="30"></td>';
		txt += '</tr>';
		txt += '</table>';
		txt += '</form>';
		txt += '</div>';
		txt += '<div class="buttons">';
		txt += '<input type="button" id="id_licence_key_form_button" value="<?= GetMessage("SUP_SUBK_SAVE") ?>" onclick="LicenceKeyFormSubmit()" title="<?= GetMessage("SUP_SUBK_SAVE") ?>">';
		txt += '</div>';

		div.innerHTML = txt;

		var left = parseInt(document.body.scrollLeft + document.body.clientWidth/2 - div.offsetWidth/2);
		var top = parseInt(document.body.scrollTop + document.body.clientHeight/2 - div.offsetHeight/2);

		jsFloatDiv.Show(div, left, top);

		jsUtils.addEvent(document, "keypress", LicenceOnKeyPress);

		document.getElementById("id_new_license_key").focus();
	}

	function LicenceOnKeyPress(e)
	{
		if (!e)
			e = window.event;
		if (!e)
			return;
		if (e.keyCode == 27)
			CloseLicenceWindow();
	}

	function CloseLicenceWindow()
	{
		jsUtils.removeEvent(document, "keypress", LicenceOnKeyPress);
		var div = document.getElementById("key_float_div");
		jsFloatDiv.Close(div);
		div.parentNode.removeChild(div);
	}

	function LicenceKeyFormSubmit()
	{
		document.getElementById("id_licence_key_form_button").disabled = true;
		ShowWaitWindow();

		var error = "";
		if (document.licence_key_form.NEW_LICENSE_KEY.value.length <= 0)
			error += "<?= GetMessage("SUP_SUBK_NO_KEY") ?>";

		if (error.length > 0)
		{
			CloseWaitWindow();
			document.getElementById("id_licence_key_form_button").disabled = false;
			alert(error);
			return false;
		}

		CHttpRequest.Action = function(result)
		{
			CloseWaitWindow();
			result = PrepareString(result);

			if (result == "Y")
			{
				window.location.href = "/bitrix/admin/update_system.php?lang=<?= LANG ?>";
				//var udl = document.getElementById("upd_activate_div");
				//udl.style["display"] = "none";
				//UnLockControls();
				//CloseActivateForm();
			}
			else
			{
				document.getElementById("id_licence_key_form_button").disabled = false;
				alert("<?= GetMessage("SUP_SUBK_ERROR") ?>: " + result);
			}
		}

		updRand++;
		CHttpRequest.Send('/bitrix/admin/update_system_act.php?query_type=key&<?= bitrix_sessid_get() ?>&NEW_LICENSE_KEY=' + encodeURIComponent(document.licence_key_form.NEW_LICENSE_KEY.value) + "&updRand=" + updRand);
	}
	//endregion
	// region license is not active
	function ActivateEnableDisableUser(value)
	{
		document.activate_form.USER_NAME.disabled = !value;
		document.activate_form.USER_LAST_NAME.disabled = !value;
		document.getElementById("USER_LOGIN_activate").disabled = !value;
		document.getElementById("USER_LOGIN").disabled = value;
		document.activate_form.USER_PASSWORD.disabled = !value;
		document.activate_form.USER_PASSWORD_CONFIRM.disabled = !value;
		document.activate_form.USER_EMAIL.disabled = !value;

		if(!value)
		{
			document.getElementById("new-user").style.display = 'none';
			document.getElementById("exist-user").style.display = 'block';
		}
		else
		{
			document.getElementById("new-user").style.display = 'block';
			document.getElementById("exist-user").style.display = 'none';
		}
	}

	function ActivateFormSubmit()
	{
		document.getElementById("id_activate_form_button").disabled = true;
		ShowWaitWindow();

		var bEr = false;
		var erImg = '<img src="/bitrix/themes/.default/images/icon_warn.gif" width="20" height="20" alt="Error" title="Error" align="left" />';

		document.getElementById('errorDiv').style.diplay = 'none';
		document.getElementById('id_activate_name_error').innerHTML = '';
		document.getElementById('SITE_URL_error').innerHTML = '';
		document.getElementById('PHONE_error').innerHTML = '';
		document.getElementById('EMAIL_error').innerHTML = '';
		document.getElementById('CONTACT_PERSON_error').innerHTML = '';
		document.getElementById('CONTACT_EMAIL_error').innerHTML = '';
		document.getElementById('CONTACT_PHONE_error').innerHTML = '';

		if(document.getElementById('id_activate_name').value.length <= 3)
		{
			document.getElementById('id_activate_name_error').innerHTML = erImg;
			bEr = true;
		}
		if(document.getElementById('SITE_URL').value.length <= 3)
		{
			document.getElementById('SITE_URL_error').innerHTML = erImg;
			bEr = true;
		}
		if(document.getElementById('PHONE').value.length <= 3)
		{
			document.getElementById('PHONE_error').innerHTML = erImg;
			bEr = true;
		}
		if(document.activate_form.EMAIL.value.length <= 3)
		{
			document.getElementById('EMAIL_error').innerHTML = erImg;
			bEr = true;
		}
		if(document.getElementById('CONTACT_PERSON').value.length <= 3)
		{
			document.getElementById('CONTACT_PERSON_error').innerHTML = erImg;
			bEr = true;
		}
		if(document.getElementById('CONTACT_EMAIL').value.length <= 3)
		{
			document.getElementById('CONTACT_EMAIL_error').innerHTML = erImg;
			bEr = true;
		}
		if(document.getElementById('CONTACT_PHONE').value.length <= 3)
		{
			document.getElementById('CONTACT_PHONE_error').innerHTML = erImg;
			bEr = true;
		}
		var generateUser = "N";
		if(document.getElementById('GENERATE_USER').checked)
		{
			generateUser = "Y";
			document.getElementById('USER_NAME_error').innerHTML = '';
			document.getElementById('USER_LAST_NAME_error').innerHTML = '';
			document.getElementById('USER_LOGIN_error').innerHTML = '';
			document.getElementById('USER_PASSWORD_error').innerHTML = '';
			document.getElementById('USER_PASSWORD_CONFIRM_error').innerHTML = '';
			document.getElementById('USER_EMAIL_error').innerHTML = '';

			if(document.getElementById('USER_NAME').value.length <= 0)
			{
				document.getElementById('USER_NAME_error').innerHTML = erImg;
				bEr = true;
			}
			if(document.getElementById('USER_LAST_NAME').value.length <= 0)
			{
				document.getElementById('USER_LAST_NAME_error').innerHTML = erImg;
				bEr = true;
			}
			if(document.getElementById('USER_LOGIN_activate').value.length < 3)
			{
				document.getElementById('USER_LOGIN_error').innerHTML = erImg;
				bEr = true;
			}
			var UserLogin = document.getElementById('USER_LOGIN_activate').value;
			if(document.getElementById('USER_PASSWORD').value.length < 6)
			{
				document.getElementById('USER_PASSWORD_error').innerHTML = erImg;
				bEr = true;
			}
			if(document.getElementById('USER_PASSWORD').value != document.getElementById('USER_PASSWORD_CONFIRM').value)
			{
				document.getElementById('USER_PASSWORD_error').innerHTML = erImg;
				bEr = true;
				document.getElementById('USER_PASSWORD_CONFIRM_error').innerHTML = erImg;
				bEr = true;
			}
			if(document.getElementById('USER_EMAIL').value.length <= 3)
			{
				document.getElementById('USER_EMAIL_error').innerHTML = erImg;
				bEr = true;
			}
		}
		else
		{
			if(document.getElementById('USER_LOGIN').value.length < 3)
			{
				document.getElementById('USER_LOGIN_EXIST_error').innerHTML = erImg;
				bEr = true;
			}
			var UserLogin = document.getElementById('USER_LOGIN').value;
		}

		if(bEr)
		{
			document.getElementById("id_activate_form_button").disabled = false;
			CloseWaitWindow();
			document.getElementById('errorDiv').innerHTML = '<table style="color:red;"><tr><td><img src="/bitrix/themes/.default/images/icon_error.gif" width="32" height="32" alt="Error" title="Error" align="left" valign="center"/></td><td><b><?=GetMessageJS("SUP_SUBA_CONFIRM_ERROR")?></b></td></tr></table>';
			document.getElementById('errorDiv').style.border = "1px solid red";

			document.getElementById('activate_content').scrollTop = 0;

			return false;
		}
		else
		{
			var param = "NAME=" + encodeURIComponent(document.activate_form.NAME.value)
				+ "&EMAIL=" + encodeURIComponent(document.activate_form.EMAIL.value)
				+ "&CONTACT_INFO=" + encodeURIComponent(document.activate_form.CONTACT_INFO.value)
				+ "&PHONE=" + encodeURIComponent(document.activate_form.PHONE.value)
				+ "&CONTACT_PERSON=" + encodeURIComponent(document.activate_form.CONTACT_PERSON.value)
				+ "&CONTACT_EMAIL=" + encodeURIComponent(document.activate_form.CONTACT_EMAIL.value)
				+ "&CONTACT_PHONE=" + encodeURIComponent(document.activate_form.CONTACT_PHONE.value)
				+ "&SITE_URL=" + encodeURIComponent(document.activate_form.SITE_URL.value)
				+ "&GENERATE_USER=" + encodeURIComponent(generateUser)
				+ "&USER_NAME=" + encodeURIComponent(document.activate_form.USER_NAME.value)
				+ "&USER_LAST_NAME=" + encodeURIComponent(document.activate_form.USER_LAST_NAME.value)
				+ "&USER_LOGIN=" + encodeURIComponent(UserLogin)
				+ "&USER_PASSWORD=" + encodeURIComponent(document.activate_form.USER_PASSWORD.value)
				+ "&USER_PASSWORD_CONFIRM=" + encodeURIComponent(document.activate_form.USER_PASSWORD_CONFIRM.value);

			CHttpRequest.Action = function(result)
			{
				CloseWaitWindow();

				result = PrepareString(result);

				if (result == "Y")
				{
					window.location.href = "update_system.php?lang=<?= LANG ?>";
				}
				else
				{
					document.getElementById("id_activate_form_button").disabled = false;
					document.getElementById('errorDiv').innerHTML = '<table style="color:red;"><tr><td><img src="/bitrix/themes/.default/images/icon_error.gif" width="32" height="32" alt="Error" title="Error" align="left" valign="center"/></td><td><b>'+result+'</b></td></tr></table>';
					document.getElementById('errorDiv').style.border = "1px solid red";

					document.getElementById('activate_content').scrollTop = 0;
				}
			}

			updRand++;
			CHttpRequest.Send('/bitrix/admin/update_system_act.php?query_type=activate&<?= bitrix_sessid_get() ?>&' + param + "&updRand=" + updRand);
			return true;

		}
	}

	function ShowActivateForm()
	{
		if (document.getElementById("activate_float_div"))
			return;

		LockControls();

		var div = document.body.appendChild(document.createElement("DIV"));

		div.id = "activate_float_div";
		div.className = "settings-float-form";
		div.style.position = 'absolute';

		var txt = '<div class="title">';
		txt += '<table cellspacing="0" width="100%">';
		txt += '<tr>';
		txt += '<td width="100%" class="title-text" onmousedown="jsFloatDiv.StartDrag(arguments[0], document.getElementById(\'activate_float_div\'));"><?= GetMessage("SUP_SUBA_ACTIVATE") ?></td>';
		txt += '<td width="0%"><a class="close" href="javascript:CloseActivateWindow();" title="<?= GetMessageJS("SUP_SULD_CLOSE") ?>"></a></td>';
		txt += '</tr>';
		txt += '</table>';
		txt += '</div>';
		txt += '<div class="content" id="activate_content" style="overflow:auto;overflow-y:auto;height:400px;">';
		txt += '<form name="activate_form" id="activate_form" onsubmit="return validate();" method="POST">';
		txt += '<h2><?= GetMessageJS("SUP_SUBA_ACTIVATE") ?></h2>';

		txt += '<input type="hidden" name="TYPE" VALUE="ACTIVATE_KEY">';
		txt += '<input type="hidden" name="STEP" VALUE="1">';
		txt += '<input type="hidden" name="lang" id="lang" VALUE="<?=LANGUAGE_ID?>">';
		txt += '<table>';
		txt += '<tr>';
		txt += '	<td colspan="2"><div id="errorDiv"></div></td>';
		txt += '</tr>';
		txt += '	<tr>';
		txt += '		<td width="50%"><span class="required">*</span><?= GetMessageJS("SUP_SUBA_RI_NAME") ?>:</td>';
		txt += '		<td width="50%" nowrap><div id="id_activate_name_error"></div><input type="text" id="id_activate_name" name="NAME" value="<?=htmlspecialcharsEx(isset($_POST["NAME"]) ? $_POST["NAME"] : '')?>" size="40"></td>';
		txt += '	</tr>';
		txt += '	<tr>';
		txt += '		<td width="50%"><span class="required">*</span><?= GetMessageJS("SUP_SUBA_RI_URI") ?>:</td>';
		txt += '		<td width="50%" nowrap><div id="SITE_URL_error"></div><input type="text" id="SITE_URL" name="SITE_URL" value="<?=htmlspecialcharsEx(isset($_POST["SITE_URL"]) ? $_POST["SITE_URL"] : '')?>" size="40"></td>';
		txt += '	</tr>';
		txt += '	<tr>';
		txt += '		<td width="50%"><span class="required">*</span><?= GetMessageJS("SUP_SUBA_RI_PHONE") ?>:</td>';
		txt += '		<td width="50%" nowrap><div id="PHONE_error"></div><input type="text" id="PHONE" name="PHONE" value="<?=htmlspecialcharsEx(isset($_POST["PHONE"]) ? $_POST["PHONE"] : '')?>" size="40"></td>';
		txt += '	</tr>';
		txt += '	<tr>';
		txt += '		<td width="50%"><span class="required">*</span><?= GetMessageJS("SUP_SUBA_RI_EMAIL") ?>:</td>';
		txt += '		<td width="50%" nowrap><div id="EMAIL_error"></div><input type="text" id="EMAIL" name="EMAIL" value="<?=htmlspecialcharsEx(isset($_POST["EMAIL"]) ? $_POST["EMAIL"] : '')?>" size="40"></td>';
		txt += '	</tr>';
		txt += '	<tr>';
		txt += '		<td width="50%"><span class="required">*</span><?= GetMessageJS("SUP_SUBA_RI_CONTACT_PERSON") ?>:</td>';
		txt += '		<td width="50%" nowrap><div id="CONTACT_PERSON_error"></div><input type="text" id="CONTACT_PERSON" name="CONTACT_PERSON" value="<?=htmlspecialcharsEx(isset($_POST["CONTACT_PERSON"]) ? $_POST["CONTACT_PERSON"] : '')?>" size="40"></td>';
		txt += '	</tr>';
		txt += '	<tr>';
		txt += '		<td width="50%"><span class="required">*</span><?= GetMessageJS("SUP_SUBA_RI_CONTACT_EMAIL") ?>:</td>';
		txt += '		<td width="50%" nowrap><div id="CONTACT_EMAIL_error"></div><input type="text" id="CONTACT_EMAIL" name="CONTACT_EMAIL" value="<?=htmlspecialcharsEx(isset($_POST["CONTACT_EMAIL"]) ? $_POST["CONTACT_EMAIL"] : '')?>" size="40"></td>';
		txt += '	</tr>';
		txt += '	<tr>';
		txt += '		<td width="50%"><span class="required">*</span><?= GetMessageJS("SUP_SUBA_RI_CONTACT_PHONE") ?>:</td>';
		txt += '		<td width="50%" nowrap><div id="CONTACT_PHONE_error"></div><input type="text" id="CONTACT_PHONE" name="CONTACT_PHONE" value="<?=htmlspecialcharsEx(isset($_POST["CONTACT_PHONE"]) ? $_POST["CONTACT_PHONE"] : '')?>" size="40"></td>';
		txt += '	</tr>';
		txt += '	<tr>';
		txt += '		<td width="50%"><?= GetMessage("SUP_SUBA_RI_CONTACT") ?>:</td>';
		txt += '		<td width="50%" nowrap><input type="text" name="CONTACT_INFO" value="<?=htmlspecialcharsEx(isset($_POST["CONTACT_INFO"]) ? $_POST["CONTACT_INFO"] : '')?>" size="40"></td>';
		txt += '	</tr>';
		txt += '<tr>';
		txt += '	<td colspan="2">';
		txt += '		<?= GetMessageJS("SUP_SUBA_UI_HINT") ?><br />';
		txt += '		<input name="GENERATE_USER" id="GENERATE_USER" type="radio" onclick="ActivateEnableDisableUser(true)" value="Y"<?if(!isset($GENERATE_USER) || $GENERATE_USER != "N") echo " checked"?>><label for="GENERATE_USER"><?= GetMessageJS("SUP_SUBA_UI_CREATE") ?></label><br />';
		txt += '		<input name="GENERATE_USER" id="GENERATE_USER_NO" type="radio" onclick="ActivateEnableDisableUser(false)" value="N"<?if(isset($GENERATE_USER) && $GENERATE_USER == "N") echo " checked"?>><label for="GENERATE_USER_NO"><?echo GetMessageJS("SUP_SUBA_UI_EXIST");?></label>';

		txt += '	</td>';
		txt += '</tr>';
		txt += '<tr>';
		txt += '	<td colspan="2">';
		txt += '		<div id="new-user">';
		txt += '			<table width="100%" border="0">';
		txt += '			<tr id="tr_USER_NAME">';
		txt += '				<td width="50%" class="field-name" style="padding: 3px;"><span class="required">*</span><?= GetMessageJS("SUP_SUBA__UI_NAME") ?>:</td>';
		txt += '				<td width="50%" style="padding: 3px;" nowrap><div id="USER_NAME_error"></div><input type="text" id="USER_NAME" name="USER_NAME" value="<?=htmlspecialcharsEx(isset($_POST["USER_NAME"]) ? $_POST["USER_NAME"] : '')?>" size="40"></td>';
		txt += '			</tr>';
		txt += '			<tr id="tr_USER_LAST_NAME">';
		txt += '				<td width="50%" class="field-name" style="padding: 3px;"><span class="required">*</span><?= GetMessageJS("SUP_SUBA_UI_LASTNAME") ?>:</td>';
		txt += '				<td width="50%" style="padding: 3px;" nowrap><div id="USER_LAST_NAME_error"></div><input type="text" id="USER_LAST_NAME" name="USER_LAST_NAME" value="<?=htmlspecialcharsEx(isset($_POST["USER_LAST_NAME"]) ? $_POST["USER_LAST_NAME"] : '')?>" size="40"></td>';
		txt += '			</tr>';
		txt += '			<tr id="tr_USER_LOGIN">';
		txt += '				<td width="50%" class="field-name" style="padding: 3px;"><span class="required">*</span><?= GetMessageJS("SUP_SUBA_UI_LOGIN") ?>:</td>';
		txt += '				<td width="50%" style="padding: 3px;" nowrap><div id="USER_LOGIN_error"></div><input type="text" id="USER_LOGIN_activate" name="USER_LOGIN_A" value="<?=htmlspecialcharsEx(isset($_POST["USER_LOGIN_A"]) ? $_POST["USER_LOGIN_A"] : '')?>" size="40"></td>';
		txt += '			</tr>';
		txt += '			<tr id="tr_USER_PASSWORD">';
		txt += '				<td width="50%" class="field-name" style="padding: 3px;"><span class="required">*</span><?= GetMessageJS("SUP_SUBA_UI_PASSWORD") ?>:</td>';
		txt += '				<td width="50%" style="padding: 3px;" nowrap><div id="USER_PASSWORD_error"></div><input type="password" id="USER_PASSWORD" name="USER_PASSWORD" value="" size="40" autocomplete="off"></td>';
		txt += '			</tr>';
		txt += '			<tr id="tr_USER_PASSWORD_CONFIRM">';
		txt += '				<td width="50%" class="field-name" style="padding: 3px;"><span class="required">*</span><?= GetMessageJS("SUP_SUBA_UI_PASSWORD_CONF") ?>:</td>';
		txt += '				<td width="50%" style="padding: 3px;" nowrap><div id="USER_PASSWORD_CONFIRM_error"></div><input type="password" id="USER_PASSWORD_CONFIRM" name="USER_PASSWORD_CONFIRM" value="" size="40"></td>';
		txt += '			</tr>';
		txt += '			<tr id="tr_USER_EMAIL">';
		txt += '				<td width="50%" class="field-name" style="padding: 3px;"><span class="required">*</span>E-mail:</td>';
		txt += '				<td width="50%" style="padding: 3px;" nowrap><div id="USER_EMAIL_error"></div><input type="text" id="USER_EMAIL" name="USER_EMAIL" value="<?=htmlspecialcharsEx(isset($_POST["USER_EMAIL"]) ? $_POST["USER_EMAIL"] : '')?>" size="40"></td>';
		txt += '			</tr>';
		txt += '			</table>';
		txt += '		</div>';
		txt += '		<div id="exist-user" style="display:none;">';
		txt += '			<table width="100%" border="0">';
		txt += '			<tr>';
		txt += '				<td width="50%" class="field-name" style="padding: 3px;"><span class="required">*</span><?= GetMessageJS("SUP_SUBA_UI_LOGIN") ?>:</td>';
		txt += '				<td width="50%" style="padding: 3px;" nowrap><div id="USER_LOGIN_EXIST_error"></div><input id="USER_LOGIN" name="USER_LOGIN" maxlength="50" value="<?=htmlspecialcharsEx(isset($_POST["USER_LOGIN"]) ? $_POST["USER_LOGIN"] : '')?>" size="40" type="text"></td>';
		txt += '			</tr>';
		txt += '			</table>';
		txt += '		</div>';
		txt += '		</td>';
		txt += '	</tr>';
		txt += '	</table>';

		txt += '<div class="buttons">';
		txt += '<input type="button" id="id_activate_form_button" value="<?= GetMessageJS("SUP_SUBA_ACTIVATE_BUTTON") ?>" onclick="ActivateFormSubmit()" title="<?= GetMessageJS("SUP_SUBA_ACTIVATE_BUTTON") ?>">';
		txt += '</div><br />';
		txt += '</form>';

		div.innerHTML = txt;

		var left = parseInt(document.body.scrollLeft + document.body.clientWidth/2 - div.offsetWidth/2);
		var top = parseInt(document.body.scrollTop + document.body.clientHeight/2 - div.offsetHeight/2);

		jsFloatDiv.Show(div, left, top);

		jsUtils.addEvent(document, "keypress", ActivateOnKeyPress);

		document.getElementById("id_activate_name").focus();
	}

	function ActivateOnKeyPress(e)
	{
		if (!e)
			e = window.event;
		if (!e)
			return;
		if (e.keyCode == 27)
			CloseActivateWindow();
	}

	function CloseActivateWindow()
	{
		jsUtils.removeEvent(document, "keypress", ActivateOnKeyPress);
		var div = document.getElementById("activate_float_div");
		jsFloatDiv.Close(div);
		div.parentNode.removeChild(div);
	}

	function CloseActivateForm()
	{
		var div = document.getElementById("activate_float_div");
		jsFloatDiv.Close(div);
		div.parentNode.removeChild(div);
	}
	// endregion
	//region update client
	function UpdateUpdate()
	{
		document.getElementById("id_updateupdate_btn").disabled = true;
		ShowWaitWindow();

		CHttpRequest.Action = function(result)
		{
			CloseWaitWindow();

			result = PrepareString(result);
			if (result == "Y")
			{
				window.location.href = "update_system.php?lang=<?= LANG ?>";
				//var udl = document.getElementById("upd_register_div");
				//udl.style["display"] = "none";
			}
			else
			{
				alert("<?= GetMessageJS("SUP_SUBU_ERROR") ?>: " + result);
				document.getElementById("id_updateupdate_btn").disabled = false;
			}
		}

		updRand++;
		CHttpRequest.Send('/bitrix/admin/update_system_act.php?query_type=updateupdate&<?= bitrix_sessid_get() ?>&updRand=' + updRand);
	}
	//endregion
	//region register product
	function RegisterSystem()
	{
		ShowWaitWindow();
		document.getElementById("id_register_btn").disabled = true;

		CHttpRequest.Action = function(result)
		{
			CloseWaitWindow();
			result = PrepareString(result);
			document.getElementById("id_register_btn").disabled = false;
			if (result == "Y")
			{
				var udl = document.getElementById("upd_register_div");
				udl.style["display"] = "none";
			}
			else
			{
				alert("<?= GetMessageJS("SUP_SUBR_ERR") ?>: " + result);
			}
		}

		updRand++;
		CHttpRequest.Send('/bitrix/admin/update_system_act.php?query_type=register&<?= bitrix_sessid_get() ?>&updRand=' + updRand);
	}
	//endregion
	//region get sources
	function LoadSources()
	{
		globalQuantity = <?= count($arClientModules) ?>;

		SetProgressHint("<?= GetMessageJS("SUP_INITIAL") ?>");

		__LoadSources();
		SetProgressD();
	}

	function __LoadSources()
	{
		document.getElementById("upd_source_div").style["display"] = "none";
		updSuccessDiv.style["display"] = "none";
		updErrorDiv.style["display"] = "none";
		updInstallDiv.style["display"] = "block";

		CHttpRequest.Action = function(result)
		{
			result = PrepareString(result);
			LoadSourcesResult(result);
		}

		var requestedModules = "";
		for (var i = 0; i < modulesList.length; i++)
		{
			if (i > 0)
				requestedModules += ",";
			requestedModules += modulesList[i];
		}

		if (requestedModules.length > 0)
		{
			updRand++;
			CHttpRequest.Send('/bitrix/admin/update_system_act.php?query_type=sources&<?= bitrix_sessid_get() ?>&requested_modules=' + requestedModules + "&updRand=" + updRand);
		}
		else
		{
			LoadSourcesResult("FIN");
		}
	}

	function LoadSourcesResult(result)
	{
		var code = result.substring(0, 3);
		var data = result.substring(3);

		if (code == "FIN")
		{
			document.getElementById("upd_source_div").style["display"] = "none";
			updErrorDiv.style["display"] = "none";
			updInstallDiv.style["display"] = "none";
			updSuccessDiv.style["display"] = "block";

			var updSuccessDivText = document.getElementById("upd_success_div_text");
			updSuccessDivText.innerHTML = "<?= GetMessageJS("SUP_SUBS_SUCCESS") ?>";
		}
		else
		{
			if (code == "STP")
			{
				if (data.length > 0)
				{
					arData = data.split("|");
					globalCounter += parseInt(arData[0]);
					SetProgress(globalCounter * 100 / globalQuantity);

					if (arData.length > 1)
					{
						loadedModule = arData[1];
						SetProgressHint("<?= GetMessageJS("SUP_SUBS_MED") ?> " + arData[1]);
					}

					var modulesListTmp = Array();
					var j = 0;
					for (var i = 0; i < modulesList.length; i++)
					{
						if (modulesList[i] != loadedModule)
						{
							modulesListTmp[j] = modulesList[i];
							j++;
						}
					}
					modulesList = modulesListTmp;
				}

				__LoadSources();
			}
			else
			{
				document.getElementById("upd_source_div").style["display"] = "none";
				updSuccessDiv.style["display"] = "none";
				updInstallDiv.style["display"] = "none";
				updErrorDiv.style["display"] = "block";

				var updErrorDivText = document.getElementById("upd_error_div_text");
				updErrorDivText.innerHTML = data;
			}
		}
	}
	//endregion
	//region support
	function LoadSupport()
	{
		sData = document.getElementById("id_support_list").value;
		if (sData.length <= 0)
			return false;

		ind = 0;
		arData = sData.split(",");
		for (var i = 0; i < arData.length; i++)
		{
			v = arData[i].replace(/(^\s+)|(\s+$)/g, "");
			if (v.length > 0)
			{
				modulesListSupport[ind] = v;
				ind++;
			}
		}

		globalQuantity = modulesListSupport.length;

		SetProgressHint("<?= GetMessageJS("SUP_INITIAL") ?>");

		__LoadSupport();
		SetProgressD();
	}

	function __LoadSupport()
	{
		document.getElementById("upd_support_div").style["display"] = "none";
		updSuccessDiv.style["display"] = "none";
		updErrorDiv.style["display"] = "none";
		updInstallDiv.style["display"] = "block";

		CHttpRequest.Action = function(result)
		{
			result = PrepareString(result);
			LoadSupportResult(result);
		}

		var requestedModules = "";
		for (var i = 0; i < modulesListSupport.length; i++)
		{
			if (i > 0)
				requestedModules += ",";
			requestedModules += modulesListSupport[i];
		}

		if (requestedModules.length > 0)
		{
			updRand++;
			CHttpRequest.Send('/bitrix/admin/update_system_act.php?query_type=support_full_load&<?= bitrix_sessid_get() ?>&requested_modules=' + requestedModules + "&updRand=" + updRand);
		}
		else
		{
			LoadSupportResult("FIN");
		}
	}

	function LoadSupportResult(result)
	{
		var code = result.substring(0, 3);
		var data = result.substring(3);

		if (code == "FIN")
		{
			document.getElementById("upd_support_div").style["display"] = "none";
			updErrorDiv.style["display"] = "none";
			updInstallDiv.style["display"] = "none";
			updSuccessDiv.style["display"] = "block";

			var updSuccessDivText = document.getElementById("upd_success_div_text");
			updSuccessDivText.innerHTML = "<?= GetMessageJS("SUP_SUPPORT_SUCCESS") ?>";
		}
		else
		{
			if (code == "STP")
			{
				if (data.length > 0)
				{
					arData = data.split("|");
					globalCounter += parseInt(arData[0]);
					SetProgress(globalCounter * 100 / globalQuantity);

					if (arData.length > 1)
					{
						loadedModule = arData[1];
						SetProgressHint("<?= GetMessageJS("SUP_SUPPORT_MED") ?> " + arData[1]);
					}

					var modulesListTmp = Array();
					var j = 0;
					for (var i = 0; i < modulesListSupport.length; i++)
					{
						if (modulesListSupport[i] != loadedModule)
						{
							modulesListTmp[j] = modulesListSupport[i];
							j++;
						}
					}
					modulesListSupport = modulesListTmp;
				}

				__LoadSupport();
			}
			else
			{
				document.getElementById("upd_support_div").style["display"] = "none";
				updSuccessDiv.style["display"] = "none";
				updInstallDiv.style["display"] = "none";
				updErrorDiv.style["display"] = "block";

				var updErrorDivText = document.getElementById("upd_error_div_text");
				updErrorDivText.innerHTML = data;
			}
		}
	}
	//endregion
	//region updates install
	var updSelectDiv = document.getElementById("upd_select_div");
	var updInstallDiv = document.getElementById("upd_install_div");
	var updSuccessDiv = document.getElementById("upd_success_div");
	var updErrorDiv = document.getElementById("upd_error_div");

	var PBdone = document.getElementById('PBdone');
	var PBdoneD = document.getElementById('PBdoneD');

	var aStrParams;

	var globalQuantity = <?= $countTotalImportantUpdates ?>;
	var globalCounter = 0;
	var globalQuantityD = 100;
	var globalCounterD = 0;

	var cycleModules = <?= ($countModuleUpdates > 0) ? "true" : "false" ?>;
	var cycleLangs = <?= ($countLangUpdatesInst > 0) ? "true" : "false" ?>;
	var cycleHelps = false;

	var bStopUpdates = false;

	function findlayer(name, doc)
	{
		var i,layer;
		for (i = 0; i < doc.layers.length; i++)
		{
			layer = doc.layers[i];
			if (layer.name == name)
				return layer;
			if (layer.document.layers.length > 0)
				if ((layer = findlayer(name, layer.document)) != null)
					return layer;
		}
		return null;
	}

	function SetProgress(val)
	{
		PBdone.style.width = (val*298/100) + 'px';
	}

	function SetProgressD()
	{
		globalCounterD++;
		if (globalCounterD > globalQuantityD)
			globalCounterD = 0;

		var val = globalCounterD * 100 / globalQuantityD;

		PBdoneD.style.width = (val * 298 / 100) + 'px';

		if (!bStopUpdates)
			setTimeout(SetProgressD, 1000);
	}

	function SetProgressHint(val)
	{
		var installProgressHintDiv = document.getElementById("install_progress_hint");
		installProgressHintDiv.innerHTML = val;
	}

	function InstallUpdates()
	{
		SetProgressHint("<?= GetMessageJS("SUP_INITIAL") ?>");

		__InstallUpdates();
		SetProgressD();
	}

	function __InstallUpdates()
	{
		updSelectDiv.style["display"] = "none";
		updSuccessDiv.style["display"] = "none";
		updErrorDiv.style["display"] = "none";
		updInstallDiv.style["display"] = "block";
		DisableUpdatesTable();

		CHttpRequest.Action = function(result)
		{
			InstallUpdatesAction(result);
		}

		var param;
		if (cycleModules)
		{
			param = "M";
		}
		else
		{
			if (cycleLangs)
			{
				param = "L";
			}
			else
			{
				if (cycleHelps)
					param = "H";
			}
		}

		updRand++;
		var requestUrl = '/bitrix/admin/update_system_call.php?';
		if (aStrParams && aStrParams.length)
		{
			requestUrl += aStrParams;
		}
		requestUrl += "&<?= bitrix_sessid_get() ?>&query_type=" + param + "&updRand=" + updRand;
		if (
			typeof(UpdateSystemExpertHelper) !== "undefined"
			&& UpdateSystemExpertHelper.getInstance().isExpertModeEnabled()
		)
		{
			CHttpRequest.Post(requestUrl, UpdateSystemExpertHelper.getInstance().getInstallationData());
		}
		else
		{
			CHttpRequest.Send(requestUrl);
		}
	}

	function InstallUpdatesDoStep(data)
	{
		if (data.length > 0)
		{
			arData = data.split("|");
			globalCounter += parseInt(arData[0]);
			if (arData.length > 1)
				SetProgressHint("<?= GetMessageJS("SUP_SU_UPD_INSMED1") ?> " + arData[1]);
			if (globalCounter > globalQuantity)
				globalCounter = 0;
			SetProgress(globalCounter * 100 / globalQuantity);

			if (
				typeof(UpdateSystemExpertHelper) !== "undefined"
				&& UpdateSystemExpertHelper.getInstance().isExpertModeEnabled()
			)
			{
				UpdateSystemExpertHelper.getInstance().processInstallationStep(data);
			}
		}

		__InstallUpdates();
	}

	function InstallUpdatesAction(result)
	{
		result = PrepareString(result);

		if (result == "*")
		{
			window.location.reload(false);
			return;
		}

		var code = result.substring(0, 3);
		var data = result.substring(3);
		//alert("code=" + code + "; data=" + data);

		if (bStopUpdates)
		{
			if (typeof(UpdateSystemExpertHelper) !== "undefined")
			{
				UpdateSystemExpertHelper.getInstance().handleInstallationCompleted();
			}
			CloseWaitWindow();
			code = "FIN";
			cycleModules = false;
			cycleLangs = false;
			cycleHelps = false;
		}

		if (code == "FIN")
		{
			if (cycleModules)
			{
				cycleModules = false;
			}
			else
			{
				if (cycleLangs)
				{
					cycleLangs = false;
				}
				else
				{
					if (cycleHelps)
						cycleHelps = false;
				}
			}

			if (cycleModules || cycleLangs || cycleHelps)
			{
				InstallUpdatesDoStep(data);
			}
			else
			{
				updSelectDiv.style["display"] = "none";
				updErrorDiv.style["display"] = "none";
				updInstallDiv.style["display"] = "none";
				updSuccessDiv.style["display"] = "block";
				DisableUpdatesTable();

				var updSuccessDivText = document.getElementById("upd_success_div_text");
				updSuccessDivText.innerHTML = "<?= GetMessageJS("SUP_SU_UPD_INSSUC") ?>: " + globalCounter;
			}
		}
		else
		{
			if (code == "STP")
			{
				InstallUpdatesDoStep(data);
			}
			else
			{
				updSelectDiv.style["display"] = "none";
				updSuccessDiv.style["display"] = "none";
				updInstallDiv.style["display"] = "none";
				updErrorDiv.style["display"] = "block";

				var updErrorDivText = document.getElementById("upd_error_div_text");
				updErrorDivText.innerHTML = data;

				if (typeof(UpdateSystemExpertHelper) !== "undefined")
				{
					UpdateSystemExpertHelper.getInstance().handleInstallationCompleted();
				}
			}
		}
	}

	function StopUpdates()
	{
		bStopUpdates = true;
		document.getElementById("id_stop_updates").disabled = true;
		ShowWaitWindow();
	}
	//endregion
	//region updates list
	function ShowDescription(module)
	{
		new BX.CDialog({'content':arModuleUpdatesDescr[module],'width':'650','height':'470', 'title' : '<?=GetMessageJS("SUP_SULD_DESC")?>'}).Show();
	}

	function DisableUpdatesTable()
	{
		document.getElementById("install_updates_sel_button").disabled = true;

		var tableUpdatesSelList = document.getElementById("table_updates_sel_list");
		var i;
		var n = tableUpdatesSelList.rows.length;
		for (i = 0; i < n; i++)
		{
			var box = tableUpdatesSelList.rows[i].cells[0].childNodes[0];
			if (box && box.tagName && box.tagName.toUpperCase() == 'INPUT' && box.type.toUpperCase() == "CHECKBOX")
			{
				box.disabled = true;
			}
		}
		if (typeof(UpdateSystemExpertHelper) !== "undefined")
		{
			UpdateSystemExpertHelper.getInstance().disableUpdatesTable();
		}
	}

	function InstallUpdatesSel()
	{
		SetProgressHint("<?= GetMessageJS("SUP_INITIAL") ?>");

		var moduleList = "";
		var langList = "";
		var helpList = "";

		globalQuantity = 0;

		var tableUpdatesSelList = document.getElementById("table_updates_sel_list");
		var i;
		var n = tableUpdatesSelList.rows.length;
		for (i = 1; i < n; i++)
		{
			var box = tableUpdatesSelList.rows[i].cells[0].childNodes[0];
			if (box && box.tagName && box.tagName.toUpperCase() == 'INPUT' && box.type.toUpperCase() == "CHECKBOX")
			{
				if (box.checked)
				{
					if (box.name.substring(0, 14) == "select_module_")
					{
						if (moduleList.length > 0)
							moduleList += ",";
						moduleList += box.name.substring(14);
						globalQuantity += arModuleUpdatesCnt[box.name.substring(14)];
					}
					else
					{
						if (box.name.substring(0, 12) == "select_lang_")
						{
							if (langList.length > 0)
								langList += ",";
							langList += box.name.substring(12);
							globalQuantity += 1;
						}
						else
						{
							if (box.name.substring(0, 12) == "select_help_")
							{
								if (helpList.length > 0)
									helpList += ",";
								helpList += box.name.substring(12);
								globalQuantity += 1;
							}
						}
					}
				}
			}
		}

		var additionalParams = "";
		cycleModules = false;
		cycleLangs = false;
		cycleHelps = false;
		if (moduleList.length > 0)
		{
			cycleModules = true;
			if (additionalParams.length > 0)
				additionalParams += "&";
			additionalParams += "requested_modules=" + moduleList;
		}
		if (langList.length > 0)
		{
			cycleLangs = true;
			if (additionalParams.length > 0)
				additionalParams += "&";
			additionalParams += "requested_langs=" + langList;
		}
		if (helpList.length > 0)
		{
			cycleHelps = true;
			if (additionalParams.length > 0)
				additionalParams += "&";
			additionalParams += "requested_helps=" + helpList;
		}

		aStrParams = additionalParams;

		tabControl.SelectTab('tab1');
		__InstallUpdates();
		SetProgressD();
	}

	function in_array(val, arr)
	{
		for (var i = 0, l = arr.length; i < l; i++)
			if (arr[i] == val)
				return true;

		return false;
	}

	function ModuleCheckboxClicked(checkbox, module, arProcessed)
	{
		arProcessed[arProcessed.length] = module;
		if (checkbox.checked && arModuleUpdatesControl[module].length > 0)
		{
			var tbl = checkbox.parentNode.parentNode.parentNode.parentNode;
			var i;
			var n = tbl.rows.length;
			for (i = 1; i < n; i++)
			{
				var box = tbl.rows[i].cells[0].childNodes[0];
				if (box && box.tagName && box.tagName.toUpperCase() == 'INPUT' && box.type.toUpperCase() == "CHECKBOX")
				{
					if (box.name.substr(0, 14) == "select_module_")
					{
						var moduleTmp = box.name.substr(14);
						if (!in_array(moduleTmp, arProcessed))
						{
							var i1;
							var n1 = arModuleUpdatesControl[module].length;
							for (i1 = 0; i1 < n1; i1++)
							{
								if (moduleTmp == arModuleUpdatesControl[module][i1]
									&& arModuleUpdatesControl[module][i1] != module)
								{
									arProcessed[arProcessed.length] = moduleTmp;
									box.checked = checkbox.checked;
									ModuleCheckboxClicked(box, arModuleUpdatesControl[module][i1], arProcessed);
									break;
								}
							}
						}
					}
				}
			}
		}
		if (!checkbox.checked)
		{
			var tbl = checkbox.parentNode.parentNode.parentNode.parentNode;
			var i;
			var n = tbl.rows.length;
			for (i = 1; i < n; i++)
			{
				var box = tbl.rows[i].cells[0].childNodes[0];
				if (box && box.tagName && box.tagName.toUpperCase() == 'INPUT' && box.type.toUpperCase() == "CHECKBOX")
				{
					if (box.name.substr(0, 14) == "select_module_")
					{
						var moduleTmp = box.name.substr(14);
						if (moduleTmp != module && !in_array(moduleTmp, arProcessed) && arModuleUpdatesControl[moduleTmp].length > 0)
						{
							var i1;
							var n1 = arModuleUpdatesControl[moduleTmp].length;
							for (i1 = 0; i1 < n1; i1++)
							{
								if (module == arModuleUpdatesControl[moduleTmp][i1])
								{
									arProcessed[arProcessed.length] = moduleTmp;
									box.checked = checkbox.checked;
									ModuleCheckboxClicked(box, moduleTmp, arProcessed);
									break;
								}
							}
						}
					}
				}
			}
		}

		EnableInstallButton(checkbox);
	}

	function EnableInstallButton(checkbox)
	{
		var tbl = checkbox.parentNode.parentNode.parentNode.parentNode;
		var bEnable = false;
		var i;
		var n = tbl.rows.length;
		for (i = 1; i < n; i++)
		{
			var box = tbl.rows[i].cells[0].childNodes[0];
			if (box && box.tagName && box.tagName.toUpperCase() == 'INPUT' && box.type.toUpperCase() == "CHECKBOX")
			{
				if (box.checked && !box.disabled)
				{
					bEnable = true;
					break;
				}
			}
		}
		var installUpdatesSelButton = document.getElementById("install_updates_sel_button");
		installUpdatesSelButton.disabled = !bEnable;
	}

	function SelectAllRows(checkbox)
	{
		var tbl = checkbox.parentNode.parentNode.parentNode.parentNode;
		var bChecked = checkbox.checked;
		var i;
		var n = tbl.rows.length;
		for (i = 1; i < n; i++)
		{
			var box = tbl.rows[i].cells[0].childNodes[0];
			if (box && box.tagName && box.tagName.toUpperCase() == 'INPUT' && box.type.toUpperCase() == "CHECKBOX")
			{
				if (box.checked != bChecked && !box.disabled)
					box.checked = bChecked;
			}
		}
		var installUpdatesSelButton = document.getElementById("install_updates_sel_button");
		installUpdatesSelButton.disabled = !bChecked;
	}

	function LockControls()
	{
		tabControl.SelectTab('tab1');
		tabControl.DisableTab('tab2');
		tabControl.DisableTab('tab_expert');
		//tabControl.DisableTab('tab_coupon');
		tabControl.DisableTab('tab3');
		if (document.getElementById("install_updates_button"))
		{
			document.getElementById("install_updates_button").disabled = true;
			document.getElementById("id_view_updates_list_span").innerHTML = "<u><?= GetMessageJS("SUP_SU_UPD_VIEW") ?></u>";
			document.getElementById("id_view_updates_list_span").disabled = true;
		}
	}

	function UnLockControls()
	{
		tabControl.EnableTab('tab1');
		tabControl.EnableTab('tab2');
		tabControl.EnableTab('tab_expert');
		tabControl.EnableTab('tab_coupon');
		tabControl.EnableTab('tab3');
		if (document.getElementById("install_updates_button"))
		{
			document.getElementById("install_updates_button").disabled = <?= (($countModuleUpdates <= 0 && $countLangUpdatesInst <= 0) ? "true" : "false") ?>;
			document.getElementById("id_view_updates_list_span").disabled = false;
			document.getElementById("id_view_updates_list_span").innerHTML = '<a id="id_view_updates_list" href="javascript:tabControl.SelectTab(\'tab2\');"><?= GetMessageJS("SUP_SU_UPD_VIEW") ?></a>';
		}

		var cnt = document.getElementById("id_register_btn");
		if (cnt != null)
			cnt.disabled = false;
	}
	//endregion
	//-->
</script>
<? //endregion
// region footer
?>

<SCRIPT LANGUAGE="JavaScript">
<!--
	<?
	if ($bLockControls)
		echo "if (window.LockControls) LockControls();";
	?>
//-->
</SCRIPT>

</form>

<?echo BeginNote();?>
<?= GetMessage("SUP_SUG_NOTES") ?><br><br>
<?= GetMessage("SUP_SUG_NOTES1") ?>
<?echo EndNote(); ?>

<form id="check_key_info_form" action="<?=GetMessage("SUP_SUA_DOMAIN")?>" method="post" target="_blank">
<input type="hidden" name="license_key" value="<?= md5(CUpdateClient::GetLicenseKey()) ?>">
</form>
<style>
	#licence_float_div.settings-float-form,
	#key_float_div.settings-float-form,
	#activate_float_div.settings-float-form {
		width: 800px;
	}
	#key_float_div.settings-float-form div.content td,
	#activate_float_div.settings-float-form div.content td,
	#licence_float_div.settings-float-form div.content td {
		font-size: 14px;
	}
	#key_float_div.settings-float-form h2,
	#activate_float_div.settings-float-form h2,
	#licence_float_div.settings-float-form h2 {
		font-size: 100%;
	}
</style>
<?
COption::SetOptionString("main", "update_system_check", Date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time()));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
//endregion
