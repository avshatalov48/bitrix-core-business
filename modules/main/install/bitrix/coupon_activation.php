<?
define("UPDATE_SYSTEM_VERSION", "9.0.2");
error_reporting(E_ALL & ~E_NOTICE);

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lib/loader.php");
$application = \Bitrix\Main\HttpApplication::getInstance();
$application->initializeBasicKernel();

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/php_interface/dbconn.php");
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/classes/".$DBType."/database.php");
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/tools.php");

if ($_REQUEST['lang'] == 'ru')
	define("LANGUAGE_ID", 'ru');
else
	define("LANGUAGE_ID", 'en');

$MESS = array();
if (LANGUAGE_ID == 'ru')
{
	$MESS['TITLE'] = 'Восстановление';
	$MESS['LOGIN_PROMT'] = 'Логин администратора';
	$MESS['PASSWORD_PROMT'] = 'Пароль администратора';
	$MESS['COUPON_PROMT'] = 'Лицензионный ключ или купон';
	$MESS['BUTTON_SUBMIT'] = 'Восстановить';
	$MESS['BUTTON_RESET'] = 'Отменить';
	$MESS['ERROR_EMPTY_CONTENT'] = 'Сервер не отвечает';
	$MESS['ERROR_INVALID_CONTENT'] = 'Ответ сервера не распознан';
	$MESS['ERROR_NOT_ADMIN'] = 'Вы не являетесь администратором';
	$MESS['ERROR_INVALID_COUPON'] = 'Лицензионный ключ / купон не корректен';
	$MESS['ERROR_EMPTY_COUPON'] = 'Лицензионный ключ / купон не указан';
	$MESS['SUCCESS_RECOVER'] = "Работоспособность сайта восстановлена";
	$MESS['ERROR_NOT_WRITABLE'] = "Ядро продукта не доступно на запись";
	$MESS['ERROR_NOT_FOPEN'] = "Не удалось открыть файл на запись";
}
else
{
	$MESS['TITLE'] = 'Restore';
	$MESS['LOGIN_PROMT'] = 'Administrator\'s Login';
	$MESS['PASSWORD_PROMT'] = 'Administrator\'s Password';
	$MESS['COUPON_PROMT'] = 'License Key or Coupon';
	$MESS['BUTTON_SUBMIT'] = 'Restore';
	$MESS['BUTTON_RESET'] = 'Cancel';
	$MESS['ERROR_EMPTY_CONTENT'] = 'Server does not respond.';
	$MESS['ERROR_INVALID_CONTENT'] = 'Server response is not recognized';
	$MESS['ERROR_NOT_ADMIN'] = 'You are not an administrator';
	$MESS['ERROR_INVALID_COUPON'] = 'License Key / Coupon is incorrect';
	$MESS['ERROR_EMPTY_COUPON'] = 'License Key / Coupon is not specified';
	$MESS['SUCCESS_RECOVER'] = "Site restore completed";
	$MESS['ERROR_NOT_WRITABLE'] = "Folder is not writable";
	$MESS['ERROR_NOT_FOPEN'] = "File open fails";
}

$DB = new CDatabase;
$DB->debug = $DBDebug;
$DB->Connect($DBHost, $DBName, $DBLogin, $DBPassword);

$errorMessage = "";
$successMessage = "";

/**************************************************************************************************************************/
/*************************   FUNCTIONS   **********************************************************************************/
/**************************************************************************************************************************/
if (!function_exists("file_get_contents"))
{
	function file_get_contents($filename)
	{
		$fd = fopen("$filename", "rb");
		$content = fread($fd, filesize($filename));
		fclose($fd);
		return $content;
	}
}

function UpdateGetOption($name, $default = "")
{
	global $DB;

	$value = "";
	$dbOption = $DB->Query("SELECT VALUE FROM b_option WHERE MODULE_ID='main' AND NAME='".$DB->ForSql($name)."'", true);
	if ($arOption = $dbOption->Fetch())
		$value = $arOption['VALUE'];
	if ($value == '')
		$value = $default;

	return $value;
}

function UpdateSetOption($name, $value)
{
	global $DB, $DBType;

	$fn = $_SERVER['DOCUMENT_ROOT']."/bitrix/managed_cache/".mb_strtoupper($DBType)."/e5/".md5("b_option").".php";
	@chmod($fn, BX_FILE_PERMISSIONS);
	@unlink($fn);

	$dbResult = $DB->Query("SELECT 'x' FROM b_option WHERE MODULE_ID='main' AND NAME='".$DB->ForSql($name)."'");
	if ($dbResult->Fetch())
	{
		$DB->Query("UPDATE b_option SET VALUE='".$DB->ForSql($value, 2000)."' WHERE MODULE_ID='main' AND NAME='".$DB->ForSql($name)."'");
	}
	else
	{
		$DB->Query(
			"INSERT INTO b_option(SITE_ID, MODULE_ID, NAME, VALUE) ".
			"VALUES(NULL, 'main', '".$DB->ForSql($name, 50)."', '".$DB->ForSql($value, 2000)."') "
		);
	}
}

function UpdateGetHTTPPage($requestDataAdd, &$errorMessage)
{
	global $DB;

	$serverIP = UpdateGetOption("update_site", "www.bitrixsoft.com");
	$serverPort = 80;

	$proxyAddr = UpdateGetOption("update_site_proxy_addr", "");
	$proxyPort = 0;
	$proxyUserName = "";
	$proxyPassword = "";
	if ($proxyAddr <> '')
	{
		$proxyPort = intval(UpdateGetOption("update_site_proxy_port", ""));
		$proxyUserName = UpdateGetOption("update_site_proxy_user", "");
		$proxyPassword = UpdateGetOption("update_site_proxy_pass", "");
	}

	$bUseProxy = ($proxyAddr <> '' && $proxyPort > 0);

	if ($bUseProxy)
	{
		$requestIP = $proxyAddr;
		$requestPort = $proxyPort;
	}
	else
	{
		$requestIP = $serverIP;
		$requestPort = $serverPort;
	}

	$FP = fsockopen($requestIP, $requestPort, $errno, $errstr, 120);

	if ($FP)
	{
		$LICENSE_KEY = "demo";
		if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/license_key.php"))
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/license_key.php");

		$dbv = $DB->GetVersion();
		
		$usrCnt = 0;
		$q = "SELECT COUNT(U.ID) as C FROM b_user U WHERE U.ACTIVE = 'Y' AND U.LAST_LOGIN IS NOT NULL AND EXISTS(SELECT 'x' FROM b_utm_user UF, b_user_field F WHERE F.ENTITY_ID = 'USER' AND F.FIELD_NAME = 'UF_DEPARTMENT' AND UF.FIELD_ID = F.ID AND UF.VALUE_ID = U.ID AND UF.VALUE_INT IS NOT NULL AND UF.VALUE_INT <> 0)";
		$dbRes = $DB->Query($q, true);
		if ($dbRes && ($arRes = $dbRes->Fetch()))
			$usrCnt = $arRes["C"];

		$requestData = 
			"&LICENSE_KEY=".urlencode(md5($LICENSE_KEY)).
			"&lang=".urlencode(LANGUAGE_ID).
			"&utf=".urlencode(defined('BX_UTF') ? "Y" : "N").
			"&stable=".urlencode(UpdateGetOption("stable_versions_only", "Y")).
			"&CANGZIP=".urlencode(function_exists("gzcompress") ? "Y" : "N").
			"&SUPD_STS=".urlencode("RA").
			"&SUPD_SRS=".urlencode("RU").
			"&SUPD_CMP=".urlencode("N").
			"&SUPD_DBS=".urlencode($DB->type).
			"&XE=".urlencode(($DB->XE) ? "Y" : "N").
			"&SUPD_URS=".urlencode($usrCnt).
			"&CLIENT_SITE=".urlencode($_SERVER["SERVER_NAME"]).
			"&spd=".urlencode(UpdateGetOption("crc_code", "")).
			"&dbv=".urlencode($dbv != false ? $dbv : "").
			"&SUPD_VER=".urlencode(UPDATE_SYSTEM_VERSION);

		if ($requestDataAdd <> '')
			$requestData .= "&".$requestDataAdd;

		$requestString = "";

		if ($bUseProxy)
		{
			$requestString .= "POST http://".$serverIP."/bitrix/updates/us_updater_actions.php HTTP/1.0\r\n";
			if ($proxyUserName <> '')
				$requestString .= "Proxy-Authorization: Basic ".base64_encode($proxyUserName.":".$proxyPassword)."\r\n";
		}
		else
			$requestString .= "POST /bitrix/updates/us_updater_actions.php HTTP/1.0\r\n";


		$requestString .= "User-Agent: BitrixSMUpdater\r\n";
		$requestString .= "Accept: */*\r\n";
		$requestString .= "Host: ".$serverIP."\r\n";
		$requestString .= "Accept-Language: en\r\n";
		$requestString .= "Content-type: application/x-www-form-urlencoded\r\n";
		$requestString .= "Content-length: ".mb_strlen($requestData)."\r\n\r\n";
		$requestString .= "$requestData";
		$requestString .= "\r\n";

		fputs($FP, $requestString);

		while (!feof($FP))
		{
			$line = fgets($FP, 4096);
			if ($line == "\r\n")
				break;
		}

		$content = "";
		while ($line = fread($FP, 4096))
			$content .= $line;

		fclose($FP);
	}
	else
	{
		$content = "";
		$errorMessage .= "[".$errno."] ".$errstr.". ";
	}

	return $content;
}

function UpdateHtmlSpecialCharsBack($str)
{
	if ($str <> '')
	{
		$str = str_replace("&lt;", "<", $str);
		$str = str_replace("&gt;", ">", $str);
		$str = str_replace("&quot;", "\"", $str);
		$str = str_replace("&amp;", "&", $str);
	}
	return $str;
}

function UpdateParseServerData($content, &$errorMessage)
{
	global $MESS;

	$arContent = array();

	if (mb_substr($content, 0, mb_strlen("<DATA>")) != "<DATA>" && function_exists("gzcompress"))
		$content = @gzuncompress($content);

	if (mb_substr($content, 0, mb_strlen("<DATA>")) != "<DATA>")
		return false;

	if (preg_match_all('#<ERROR[^>]*>(.+?)</ERROR>#is', $content, $arMatches))
	{
		for ($i = 0, $cnt = count($arMatches[1]); $i < $cnt; $i++)
			$errorMessage .= UpdateHtmlSpecialCharsBack($arMatches[1][$i]).". ";

		return false;
	}

	if (preg_match('#<RENT\s+([^>]*)/>#i', $content, $arMatches))
	{
		if (preg_match_all("/(\\S+?)\\s*=\\s*[\"](.*?)[\"]/s", $arMatches[1], $arMatches1))
		{
			for ($i = 0, $cnt = count($arMatches1[1]); $i < $cnt; $i++)
				$arContent[$arMatches1[1][$i]] .= $arMatches1[2][$i];
		}
	}

	if (isset($arContent["V1"]) && isset($arContent["V2"]))
		return $arContent;

	return false;
}

function UpdateActivateCoupon($coupon, &$errorMessage)
{
	global $MESS;

	$postDataString = "coupon=".urlencode($coupon)."&query_type=".urlencode("reincarnate");
	$content = UpdateGetHTTPPage($postDataString, $errorMessage);
	if ($content == '')
	{
		$errorMessage .= $MESS['ERROR_EMPTY_CONTENT'].". ";
		return false;
	}

	$arContent = UpdateParseServerData($content, $errorMessage);
	if (!is_array($arContent) || count($arContent) <= 0)
	{
		if ($errorMessage == '')
			$errorMessage .= $MESS['ERROR_INVALID_CONTENT'].". ";
		return false;
	}

	UpdateSetOption('~SAAS_MODE', "Y");

	UpdateSetOption('admin_passwordh', $arContent["V1"]);

	if (is_writable($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin"))
	{
		if ($fp = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/define.php", 'w'))
		{
			fwrite($fp, "<"."?Define(\"TEMPORARY_CACHE\", \"".$arContent["V2"]."\");?".">");
			fclose($fp);
		}
		else
		{
			$errorMessage .= $MESS['ERROR_NOT_FOPEN'].". ";
		}
	}
	else
	{
		$errorMessage .= $MESS['ERROR_NOT_WRITABLE'].". ";
	}

	if (isset($arContent["DATE_TO_SOURCE"]))
		UpdateSetOption("~support_finish_date", $arContent["DATE_TO_SOURCE"]);
	if (isset($arContent["MAX_SITES"]))
		UpdateSetOption("PARAM_MAX_SITES", intval($arContent["MAX_SITES"]));
	if (isset($arContent["MAX_USERS"]))
		UpdateSetOption("PARAM_MAX_USERS", intval($arContent["MAX_USERS"]));
    if (isset($arContent["MAX_USERS_STRING"]))
        UpdateSetOption("~PARAM_MAX_USERS", $arContent["MAX_USERS_STRING"]);
	if (isset($arContent["DATE_TO_SOURCE_STRING"]))
		UpdateSetOption("~PARAM_FINISH_DATE", $arContent["DATE_TO_SOURCE_STRING"]);
	if (isset($arContent["ISLC"]))
	{
		if (is_writable($_SERVER['DOCUMENT_ROOT']."/bitrix"))
		{
			if ($fp = fopen($_SERVER['DOCUMENT_ROOT']."/bitrix/license_key.php", "wb"))
			{
				fputs($fp, '<'.'?$LICENSE_KEY = "'.EscapePHPString($coupon).'";?'.'>');
				fclose($fp);
			}
			else
			{
				$errorMessage .= $MESS['ERROR_NOT_FOPEN'].". ";
			}
		}
		else
		{
			$errorMessage .= $MESS['ERROR_NOT_WRITABLE'].". ";
		}
	}

	return true;
}

function UpdateIsAdmin($login, $password)
{
	global $DB;

	if (!is_string($login) || $login == '' || !is_string($password) || $password == '')
		return false;

	$dbUser = $DB->Query(
		"SELECT U.ID, U.PASSWORD, U.LOGIN_ATTEMPTS ".
		"FROM b_user U ".
		"	INNER JOIN b_user_group UG ON (UG.USER_ID = U.ID) ".
		"WHERE U.LOGIN = '".$DB->ForSql($login)."' ".
		"	AND (U.EXTERNAL_AUTH_ID IS NULL OR U.EXTERNAL_AUTH_ID = '') ".
		"	AND U.ACTIVE = 'Y' ".
		"	AND UG.GROUP_ID = 1 ".
		"	AND ((UG.DATE_ACTIVE_FROM IS NULL) OR (UG.DATE_ACTIVE_FROM <= ".$DB->CurrentTimeFunction().")) ".
		"	AND ((UG.DATE_ACTIVE_TO IS NULL) OR (UG.DATE_ACTIVE_TO >= ".$DB->CurrentTimeFunction().")) "
	);
	if ($arUser = $dbUser->Fetch())
	{
		if(intval($arUser["LOGIN_ATTEMPTS"]) <= 5)
		{
			if (mb_strlen($arUser["PASSWORD"]) > 32)
			{
				$salt = mb_substr($arUser["PASSWORD"], 0, mb_strlen($arUser["PASSWORD"]) - 32);
				$db_password = mb_substr($arUser["PASSWORD"], -32);
			}
			else
			{
				$salt = "";
				$db_password = $arUser["PASSWORD"];
			}

			$user_password =  md5($salt.$password);

			if($db_password === $user_password)
			{
				return true;
			}
		}
		$DB->Query("UPDATE b_user SET LOGIN_ATTEMPTS = LOGIN_ATTEMPTS+1, TIMESTAMP_X = TIMESTAMP_X WHERE ID = ".intval($arUser["ID"]));
	}

	return false;
}

/**************************************************************************************************************************/
/**************************************************************************************************************************/
/**************************************************************************************************************************/

header("Content-Type: text/html; charset=windows-1251");

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	if (is_string($_POST["autoActivateCoupon"]) && $_POST["autoActivateCoupon"] <> '')
	{
		$autoActivateCoupon = $_POST["autoActivateCoupon"];
		if (preg_match("#^[A-Z0-9]{3}-[A-Z0-9]{10}-[A-Z0-9]{10}$#i", $autoActivateCoupon))
		{
			if (UpdateActivateCoupon($autoActivateCoupon, $errorMessage))
				echo "success";
			else
				echo $errorMessage;
		}
		else
		{
			echo "error";
		}
		die();
	}

	if (is_string($_POST["reincarnate"]) && $_POST["reincarnate"] <> '')
	{
		if (!is_string($_POST["coupon"]) || $_POST["coupon"] == '')
		{
			$errorMessage .= $MESS['ERROR_EMPTY_COUPON'].". ";
		}
		elseif (!preg_match("#^[A-Z0-9]{3}-[A-Z]{2}-?[A-Z0-9]{12,18}$#i", $_POST["coupon"]) && !preg_match("#^[A-Z0-9]{3}-[A-Z0-9]{10}-[A-Z0-9]{10}$#i", $_POST["coupon"]))
		{
			$errorMessage .= $MESS['ERROR_INVALID_COUPON'].". ";
		}
		elseif (!UpdateIsAdmin($_POST["login"], $_POST["password"]))
		{
			$errorMessage .= $MESS['ERROR_NOT_ADMIN'].". ";
		}
		else
		{
			if (UpdateActivateCoupon($_POST["coupon"], $errorMessage))
				$successMessage .= $MESS['SUCCESS_RECOVER'].". ";
		}
	}
}

?>
<html>
	<head>
		<title><?= $MESS['TITLE'] ?></title>
		<link rel="stylesheet" type="text/css" href="/bitrix/themes/.default/sysupdate.css">
		<link rel="stylesheet" type="text/css" href="/bitrix/themes/.default/adminstyles.css">
	</head>
	<body>
	<?
	if ($errorMessage <> '')
	{
		?><br>
		<table width="600" align="center" cellspacing="1" cellpadding="10" bgcolor="red"><tr><td bgcolor="white">
		<font style="color:red"><b><?= $errorMessage ?></b></font>
		</td></tr></table>
		<br>
		<?
	}

	if ($successMessage <> '')
	{
		?><br>
		<table width="600" align="center" cellspacing="1" cellpadding="10" bgcolor="green"><tr><td bgcolor="white">
		<font style="color:green"><b><?= $successMessage ?></b></font>
		</td></tr></table>
		<br>
		<?
	}

	?>
	<form method="POST" action="/bitrix/coupon_activation.php">
	<input type="hidden" name="lang" value="<?= htmlspecialcharsbx(LANGUAGE_ID) ?>" />

	<table width="100%" cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td width="20%">
			</td>
			<td width="60%">
				<br />
				<div class="edit-form">
				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="edit-form">
					<tr class="top">
						<td class="left"><div class="empty"></div></td>
						<td><div class="empty"></div></td>
						<td class="right"><div class="empty"></div></td>
					</tr>
					<tr>
						<td class="left"><div class="empty"></div></td>
						<td class="content">
							<table cellspacing="0" class="edit-tabs" width="100%">
								<tr>
									<td class="tab-indent"><div class="empty"></div></td>
									<td class="tab-container-selected">
										<table cellspacing="0">
											<tr>
												<td class="tab-left-selected" id="tab_left_edit1"><div class="empty"></div></td>
												<td class="tab-selected" id="tab_edit1"><?= $MESS['TITLE'] ?></td>
												<td class="tab-right-selected" id="tab_right_edit1"><div class="empty"></div></td>
											</tr>
										</table>
									</td>
									<td width="100%" class="tab-indent"><div class="empty"></div></td>
								</tr>
							</table>
							<table cellspacing="0" class="edit-tab">
								<tr>
									<td>
										<div id="edit1" class="edit-tab-inner"><div style="height: 100%;">
										<table cellpadding="0" cellspacing="0" border="0" class="edit-table">
											<tr>
												<td width="40%" align="right"><?= $MESS['LOGIN_PROMT'] ?>:</td>
												<td><input type="text" name="login" value="<?= htmlspecialcharsbx(strval($_POST["login"])) ?>" size="40"></td>
											</tr>
											<tr>
												<td width="40%" align="right"><?= $MESS['PASSWORD_PROMT'] ?>:</td>
												<td><input type="password" name="password" value="" size="40"></td>
											</tr>
											<tr>
												<td width="40%" align="right"><?= $MESS['COUPON_PROMT'] ?>:</td>
												<td><input type="text" name="coupon" value="<?= htmlspecialcharsbx(strval($_POST["coupon"])) ?>" size="40"></td>
											</tr>
										</table>
										</div></div>
									</td>
								</tr>
							</table>

							<div class="buttons">
								<input type="submit" class="button" name="reincarnate" value="<?= $MESS['BUTTON_SUBMIT'] ?>">
								<input type="reset" class="button" name="reset" value="<?= $MESS['BUTTON_RESET'] ?>">
							</div>
						</td>
						<td class="left"><div class="empty"></div></td>
					</tr>
					<tr class="bottom">
						<td class="left"><div class="empty"></div></td>
						<td><div class="empty"></div></td>
						<td class="right"><div class="empty"></div></td>
					</tr>
				</table>
				</div>
			</td>
			<td width="20%">
			</td>
		</tr>
	</table>

	</form>

	</body>
</html>