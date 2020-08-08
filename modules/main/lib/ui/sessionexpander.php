<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */

namespace Bitrix\Main\UI;

use Bitrix\Main\Security\Sign;
use Bitrix\Main\Config;
use Bitrix\Main\Localization\Loc;

class SessionExpander
{
	/**
	 * Initializes scripts on the page.
	 */
	public static function init()
	{
		global $USER;

		$policy = $USER->GetSecurityPolicy();

		$phpSessTimeout = ini_get("session.gc_maxlifetime");
		if($policy["SESSION_TIMEOUT"] > 0)
		{
			$sessTimeout = min($policy["SESSION_TIMEOUT"]*60, $phpSessTimeout);
		}
		else
		{
			$sessTimeout = $phpSessTimeout;
		}

		$sessid = bitrix_sessid();

		$signer = new Sign\Signer();
		$signedSessId = $signer->sign($sessid, static::getSalt());

		\CUtil::InitJSCore(['ajax', 'ls']);

		$jsCode = '<script type="text/javascript">'."\n";

		$showMess = ($USER->IsAuthorized() && Config\Option::get("main", "session_show_message", "Y") <> "N");
		if($showMess)
		{
			$message = \CUtil::JSEscape(Loc::getMessage("MAIN_SESS_MESS", array("#TIMEOUT#" => round($sessTimeout / 60))));
			$jsCode .= 'BX.message({"SessExpired": \''.$message.'\'});'."\n";
		}

		$jsCode .= 'bxSession.Expand(\''.$signedSessId.'\');'."\n".'</script>';

		$asset = \Bitrix\Main\Page\Asset::getInstance();
		$asset->addJs('/bitrix/js/main/session.js');
		$asset->addString($jsCode);
	}

	/**
	 * Returns the value of the signed string or false on an error.
	 * @param $signedParam
	 * @return bool|string
	 */
	public static function getSignedValue($signedParam)
	{
		try
		{
			$signer = new Sign\Signer();
			$string = $signer->unsign($signedParam, static::getSalt());
			return $string;
		}
		catch(\Bitrix\Main\SystemException $exception)
		{
			return false;
		}
	}

	protected static function getSalt()
	{
		global $USER;

		$context = \Bitrix\Main\Context::getCurrent();

		return md5(
			$context->getRequest()->getCookie("UIDH").
			"|".$USER->GetID().
			"|".$context->getServer()->getRemoteAddr()
		);
	}
}
