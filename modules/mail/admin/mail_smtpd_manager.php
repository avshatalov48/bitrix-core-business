<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002 - 2010 Bitrix           #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!check_bitrix_sessid())
	die();

if(!CModule::IncludeModule('mail'))
	die();

$MOD_RIGHT = $APPLICATION->GetGroupRight("mail");
if($MOD_RIGHT < "R")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$res = '';
switch($_REQUEST['action'])
{
	case 'start':
		$bWindowsHosting = false;
		$strCurrentOS = PHP_OS;
		if (mb_strtoupper(mb_substr($strCurrentOS, 0, 3)) === "WIN")
		   $bWindowsHosting = true;

		$phpPath = COption::GetOptionString("mail", "php_path", $bWindowsHosting ? "../apache/php.exe -c ../apache/php.ini" : "authbind php -c /etc/php.ini");
		$serverPath = $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/mail/smtpd.php";

		chdir($_SERVER["DOCUMENT_ROOT"]);

		$startErrorMessage = "";
/*
		$p = $phpPath." ".($bWindowsHosting ? $serverPath : $_SERVER['DOCUMENT_ROOT'].ltrim($serverPath, '.'))." test_mode";
		if (!$bWindowsHosting)
			$p .= " 2>&1";
		else
			$p = str_replace("/", "\\", $p);
		exec($p, $execOutput, $execReturnVar);
		$s = strtolower(implode("\n", $execOutput));

		if ($execReturnVar == 0)
		{
			if (strlen($s) <= 0)
				$startErrorMessage .= "Unknown error";
			elseif (strpos($s, "server started") === false || strpos($s, "error") !== false)
				$startErrorMessage .= $s;
		}
		else
		{
			$startErrorMessage .= "[".$execReturnVar."] ".$s;
		}
*/
		if ($startErrorMessage == '')
		{
			if ($bWindowsHosting)
			{
				pclose(popen("start ".$phpPath." \"".$serverPath."\"", "r"));
			}
			else
			{
				$cmd = 'nohup '.$phpPath.' '.ltrim($serverPath, '.').' > /dev/null &';
				exec($cmd, $op);
			}
		}

		if ($startErrorMessage == '')
			$res = "success";
		else
			$res = $startErrorMessage;
		break;
	case 'stop':
		$CACHE_MANAGER->Read(3600000, $cache_id = "smtpd_stop");
		$CACHE_MANAGER->Set($cache_id, true);
		break;
	case 'stats':
		$res = false;
		if($CACHE_MANAGER->Read(3600000, $cache_id = "smtpd_stats"))
		{
			$res = $CACHE_MANAGER->Get($cache_id);
			$res["uptime"] = time() - $res["started"];
		}
		break;
}

echo CUtil::PhpToJSObject($res, false);
?>
<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_after.php");
?>