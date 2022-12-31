<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

if (!CModule::IncludeModule("socialservices"))
	return;

if (!$GLOBALS["USER"]->IsAuthorized())
	return;

if ($_SESSION["LAST_ERROR"])
{
	ShowError($_SESSION["LAST_ERROR"]);
	$_SESSION["LAST_ERROR"] = false;
}
$oAuthManager = new CSocServAuthManager();
if(isset($arParams['BACKURL']))
{
	$arResult['BACKURL'] = trim($arParams['BACKURL']);
}

$arResult["FOR_INTRANET"] = true;

$arServices = $oAuthManager->GetActiveAuthServices($arResult);
$arResult["AUTH_SERVICES"] = $arServices;
$arResult["DB_SOCSERV_USER"] = [];
//***************************************
//Checking the input parameters.
//***************************************
if(
	(
		(
			isset($_REQUEST["code"])
			&& $_REQUEST["code"] <> ''
		)
		||
		(
			isset($_REQUEST["auth_service_id"])
			&& $_REQUEST["auth_service_id"] <> ''
			&& isset($arResult["AUTH_SERVICES"][$_REQUEST["auth_service_id"]])
		)
	)
	&&
	(
		check_bitrix_sessid()
		|| CSocServAuthManager::CheckUniqueKey(false)
	)
)
{
	$arResult["CURRENT_SERVICE"] = $_REQUEST["auth_service_id"];
	if(isset($_REQUEST["auth_service_error"]) && $_REQUEST["auth_service_error"] <> '')
	{
		$arResult['ERROR_MESSAGE'] = $oAuthManager->GetError($arResult["CURRENT_SERVICE"], $_REQUEST["auth_service_error"]);
	}
	elseif(!$oAuthManager->Authorize($_REQUEST["auth_service_id"]))
	{
		$ex = $GLOBALS["APPLICATION"]->GetException();
		if ($ex)
			$arResult['ERROR_MESSAGE'] = $ex->GetString();
	}
}

$userID = $GLOBALS["USER"]->GetID();
if(isset($arParams['USER_ID']) && intval($arParams['USER_ID']) > 0)
	$userID = intval($arParams['USER_ID']);
$arResult["AUTH_SERVICES_ICONS"] = $arServices;
$userName = '';
$arResult["ALLOW_DELETE_ID"] = array();
$arResult["SEND_MY_ACTIVITY"] = '';
$arResult["PostToShow"]["SPERM"] = array();
$twitNum = 100;

$dbSocservUser = \Bitrix\Socialservices\UserTable::getList([
	'filter' => [
		'=USER_ID' => $userID,
		"!EXTERNAL_AUTH_ID" => 'Bitrix24OAuth'
	]
]);

//***************************************
//Obtain data on the related user account.
//***************************************
while($arUser = $dbSocservUser->fetch())
{
	if($arUser["EXTERNAL_AUTH_ID"] == 'Twitter')
		$arResult["PostToShow"]["SPERM"] = unserialize($arUser["PERMISSIONS"], ['allowed_classes' => false]);
	if($arUser["NAME"] != '' && $arUser["LAST_NAME"] != '')
		$userName = $arUser["NAME"]." ".$arUser["LAST_NAME"];
	elseif ($arUser["NAME"] != '')
		$userName = $arUser["NAME"];
	elseif ($arUser["LAST_NAME"] != '')
		$userName = $arUser["LAST_NAME"];
	elseif ($arUser["LOGIN"] != '')
		$userName = $arUser["LOGIN"];
	if($arUser["SEND_ACTIVITY"] == 'Y')
		$arResult["SEND_MY_ACTIVITY"] = 'Y';

	$result = array();
	preg_match("/\/([a-zA-Z0-9._]{1,})\//", $arUser["EXTERNAL_AUTH_ID"], $result);
	if (isset($result[1]))
	{
		switch($result[1])
		{
			case 'openid.mail.ru' : $arUser["EXTERNAL_AUTH_ID"] = 'MailRuOpenID';
			break;
			case 'www.livejournal.com' : $arUser["EXTERNAL_AUTH_ID"] = 'Livejournal';
			break;
			case 'openid.yandex.ru' : $arUser["EXTERNAL_AUTH_ID"] = 'YandexOpenID';
			break;
			case 'www.liveinternet.ru' : $arUser["EXTERNAL_AUTH_ID"] = 'Liveinternet';
			break;
			case 'www.blogger.com' : $arUser["EXTERNAL_AUTH_ID"] = 'Blogger';
			break;
			default : $arUser["EXTERNAL_AUTH_ID"] = $result[1];
		}

	}

	if(!array_key_exists($arUser["EXTERNAL_AUTH_ID"], $arServices))
	{
		continue;
	}

	foreach($arResult["AUTH_SERVICES"] as $key => $value)
	{
		if($key == $arUser["EXTERNAL_AUTH_ID"])
		{
			unset($arResult["AUTH_SERVICES"][$key]);
		}
	}

	$arService = array(
		"ID" => $arUser["ID"],
		"LOGIN" => htmlspecialcharsbx($arUser["LOGIN"]),
		"NAME" => htmlspecialcharsbx($arUser["NAME"]),
		"LAST_NAME" => htmlspecialcharsbx($arUser["LAST_NAME"]),
		"EXTERNAL_AUTH_ID" => htmlspecialcharsbx($arUser["EXTERNAL_AUTH_ID"]),
		"VIEW_NAME" => htmlspecialcharsbx($userName),
		"PERSONAL_LINK" => htmlspecialcharsbx($arUser["PERSONAL_WWW"]),
		"PERSONAL_PHOTO" => intval($arUser["PERSONAL_PHOTO"]),
		"PERMISSIONS" => unserialize($arUser["PERMISSIONS"], ['allowed_classes' => false]),
	);

	if($arUser["CAN_DELETE"] != 'N' && $arParams["ALLOW_DELETE"] != 'N')
	{
		$arResult["ALLOW_DELETE_ID"][] = $arUser["ID"];
		$arService['DELETE_LINK'] = $APPLICATION->GetCurPageParam("action=delete&user_id=".$arUser["ID"]."&".bitrix_sessid_get(), array("action", "user_id"));
	}

	$arResult["DB_SOCSERV_USER"][] = $arService;
}

if(is_array($arResult["DB_SOCSERV_USER"]))
	foreach($arResult["DB_SOCSERV_USER"] as $key => $value)
	{
		if($value["EXTERNAL_AUTH_ID"] == 'Twitter')
		{
			$arResult["DB_SOCSERV_USER"][$twitNum] = $arResult["DB_SOCSERV_USER"][$key];
			unset($arResult["DB_SOCSERV_USER"][$key]);
			$twitNum++;
		}
	}

$arParamsToDelete = array(
	//"auth_service_id",
	"openid_assoc_handle",
	"openid_identity",
	"openid_sreg_email",
	"openid_sreg_fullname",
	"openid_sreg_gender",
	"openid_mode",
	"openid_op_endpoint",
	"openid_response_nonce",
	"openid_return_to",
	"openid_signed",
	"openid_sig",
	"current_fieldset",
);
$add = (CModule::IncludeModule("socialnetwork") && $_REQUEST["auth_service_id"] <> '' && $componentTemplate == 'twitpost') ? "current_fieldset=SOCSERV" : "";
if ($_SERVER["REQUEST_METHOD"] == "GET" && $_REQUEST["action"] == "delete" && isset($_REQUEST["user_id"]) && intval($_REQUEST["user_id"] > 0) && check_bitrix_sessid())
{
	$userId = intval($_REQUEST["user_id"]);
	if(in_array($userId, $arResult["ALLOW_DELETE_ID"]))
	{
		$result = \Bitrix\Socialservices\UserTable::delete($userId);
		if (!$result->isSuccess())
		{
			$_SESSION["LAST_ERROR"] = GetMessage("DELETE_ERROR");
		}
	}

	$backurl = '';
	if($arResult['BACKURL'] != '')
	{
		$backurl = $arResult['BACKURL'];
		if($componentTemplate == 'twitpost')
		{
			$backurl .= (mb_strpos($arResult['BACKURL'], '?') > 0 ? "&" : "?")."current_fieldset=SOCSERV";
		}
	}
	else
	{
		$backurl = $APPLICATION->GetCurPageParam(($componentTemplate == 'twitpost') ? "current_fieldset=SOCSERV" : "", array("sessid", "user_id", "action"));
	}

	LocalRedirect($backurl);
}

if($componentTemplate == 'twitpost')
	$arResult["TWIT_HASH"] = htmlspecialcharsbx(COption::GetOptionString("socialservices", "twitter_search_hash", "#b24"));

$arResult['CURRENTURL'] = $APPLICATION->GetCurPageParam($add, $arParamsToDelete);

if(CModule::IncludeModule("socialnetwork"))
{
	CJSCore::Init(array('socnetlogdest'));
	// socialnetwork

	if (method_exists('CSocNetLogDestination','GetDestinationSort'))
	{
		$arResult["PostToShow"]["FEED_DESTINATION"] = array(
			'DEST_SORT' => CSocNetLogDestination::GetDestinationSort(array(
				"DEST_CONTEXT" => "SOCSERVICES"
			)),
			'LAST' => array()
		);
		CSocNetLogDestination::fillLastDestination($arResult["PostToShow"]["FEED_DESTINATION"]['DEST_SORT'], $arResult["PostToShow"]["FEED_DESTINATION"]['LAST']);
	}
	else
	{
		$arResult["PostToShow"]["FEED_DESTINATION"] = array(
			'LAST' => array(
				'SONETGROUPS' => CSocNetLogDestination::GetLastSocnetGroup(),
				'DEPARTMENT' => CSocNetLogDestination::GetLastDepartment(),
				'USERS' => CSocNetLogDestination::GetLastUser()
			)
		);
	}

	$arResult["PostToShow"]["FEED_DESTINATION"]['SONETGROUPS'] = CSocNetLogDestination::GetSocnetGroup(Array('features' => array("blog", array("premoderate_post", "moderate_post", "write_post", "full_post"))));

	$arDestUser = Array();
	$arResult["PostToShow"]["FEED_DESTINATION"]['SELECTED'] = Array();

	if(!empty($arResult["PostToShow"]["SPERM"]))
	{
		$arResult["PostToShow"]["FEED_DESTINATION"]['SELECTED'] = Array();
		if (empty($arResult["PostToShow"]["SPERM"]))
		{
			if (CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser())
			{
				if(!empty($arResult["PostToShow"]["FEED_DESTINATION"]['LAST']['SONETGROUPS']))
				{
					foreach ($arResult["PostToShow"]["FEED_DESTINATION"]['LAST']['SONETGROUPS'] as $val)
					{
						$arResult["PostToShow"]["FEED_DESTINATION"]['SELECTED'][$val] = "sonetgroups";
					}
				}
				else
				{
					foreach ($arResult["PostToShow"]["FEED_DESTINATION"]['SONETGROUPS'] as $k => $val)
					{
						$arResult["PostToShow"]["FEED_DESTINATION"]['SELECTED'][$k] = "sonetgroups";
					}
				}

			}
			else
				$arResult["PostToShow"]["FEED_DESTINATION"]['SELECTED']['UA'] = 'groups';
		}
		else
		{
			foreach ($arResult["PostToShow"]["SPERM"] as $type => $ar)
			{
				if(is_array($ar))
				{
					foreach ($ar as $value => $ar2)
					{
						if ($type == 'UA')
							$arResult["PostToShow"]["FEED_DESTINATION"]['SELECTED']['UA'] = 'groups';
						elseif ($type == 'U')
						{
							$arResult["PostToShow"]["FEED_DESTINATION"]['SELECTED'][$ar2] = 'users';
							$arDestUser[] = $value;
						}
						elseif ($type == 'SG')
							$arResult["PostToShow"]["FEED_DESTINATION"]['SELECTED'][$ar2] = 'sonetgroups';
						elseif ($type == 'DR' || $type == 'D')
							$arResult["PostToShow"]["FEED_DESTINATION"]['SELECTED'][$ar2] = 'department';
					}
				}
			}
		}
	}
	else
	{
		$arResult["PostToShow"]["FEED_DESTINATION"]['SELECTED']['UA'] = 'groups';
	}

	// intranet structure
	$arStructure = CSocNetLogDestination::GetStucture();
	$arResult["PostToShow"]["FEED_DESTINATION"]['DEPARTMENT'] = $arStructure['department'];
	$arResult["PostToShow"]["FEED_DESTINATION"]['DEPARTMENT_RELATION'] = $arStructure['department_relation'];

	if (CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser())
	{
		$arResult["PostToShow"]["FEED_DESTINATION"]['EXTRANET_USER'] = 'Y';
		$arResult["PostToShow"]["FEED_DESTINATION"]['USERS'] = CSocNetLogDestination::GetExtranetUser();
	}
	else
	{
		if (!empty($arResult["PostToShow"]["FEED_DESTINATION"]['LAST']['USERS']))
		{
			foreach ($arResult["PostToShow"]["FEED_DESTINATION"]['LAST']['USERS'] as $value)
			{
				$arDestUser[] = str_replace('U', '', $value);
			}
		}

		$arResult["PostToShow"]["FEED_DESTINATION"]['EXTRANET_USER'] = 'N';
		$arResult["PostToShow"]["FEED_DESTINATION"]['USERS'] = CSocNetLogDestination::GetUsers(Array('id' => $arDestUser));
	}

}

$this->IncludeComponentTemplate();
?>