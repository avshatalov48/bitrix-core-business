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

		$sessid = bitrix_sessid();

		$signer = new Sign\Signer();
		$signedSessId = $signer->sign($sessid, static::getSalt());

		\CJSCore::Init(['ajax', 'ls']);

		$jsCode = '<script>'."\n";

		$showMess = ($USER->IsAuthorized() && Config\Option::get("main", "session_show_message", "Y") <> "N");
		if($showMess)
		{
			$policy = $USER->GetSecurityPolicy();
			$message = \CUtil::JSEscape(Loc::getMessage("MAIN_SESS_MESS", array("#TIMEOUT#" => (int)$policy["SESSION_TIMEOUT"])));
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
