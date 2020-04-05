<?php
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

$result = array();
$request = Bitrix\Main\Context::getCurrent()->getRequest();

if($USER->IsAuthorized() && $request->isPost() && check_bitrix_sessid() && \Bitrix\Main\Loader::includeModule('webservice'))
{
	$action = $request["action"];

	switch($action)
	{
		case 'stssync_auth':

			$ap = \Bitrix\WebService\StsSync::getAuth($request['type']);
			if($ap)
			{
				$result = array('ap' => $ap);
			}

		break;
	}
}

Header('Content-Type: application/json;charset=utf-8');
echo \Bitrix\Main\Web\Json::encode($result);

CMain::FinalActions();
