<?php
/**
 * @global int $ID - Edited user id
 * @global string $strError - Save error
 * @global \CUser $USER
 * @global CMain $APPLICATION
 */

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Socialservices\UserTable;

$ID = intval($ID);
$socialservices_res = true;

if(
	$ID > 0
	&& isset($_REQUEST["SS_REMOVE_NETWORK"])
	&& $_REQUEST["SS_REMOVE_NETWORK"] == "Y"
	&& Option::get("socialservices", "bitrix24net_id", "") != ""
	&& Loader::includeModule('socialservices')
	&& check_bitrix_sessid()
)
{
	$dbRes = UserTable::getList(array(
		'filter' => array(
			'=USER_ID' => $ID,
			'=EXTERNAL_AUTH_ID' => CSocServBitrix24Net::ID
		),
		'select' => array('ID')
	));

	$profileInfo = $dbRes->fetch();
	if($profileInfo)
	{
		$deleteResult = UserTable::delete($profileInfo["ID"]);
		$socialservices_res = $deleteResult->isSuccess();

		if($socialservices_res)
		{
			\Bitrix\Socialservices\Network::clearAdminPopupSession($ID);
		}
	}
}
