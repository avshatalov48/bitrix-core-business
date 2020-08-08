<?php
/**
 * @global CUser $USER
 */
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_FILE_PERMISSIONS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$status = "SESSION_EXPIRED";

if(check_bitrix_sessid())
{
	//still the same session
	$status = "OK";
}
elseif($USER->IsAuthorized())
{
	//the user got a new session, but has authorized by the stored cookie
	//change the sessid so the user can post a form successfully
	$request = \Bitrix\Main\Context::getCurrent()->getRequest();

	if($request->getCookie("UIDH") <> '')
	{
		$sessid = \Bitrix\Main\UI\SessionExpander::getSignedValue($request["k"]);
		if($sessid !== false)
		{
			bitrix_sessid_set($sessid);
			$status = "SESSION_CHANGED";
		}
	}
}

echo $status;
\Bitrix\Main\Application::getInstance()->end();
