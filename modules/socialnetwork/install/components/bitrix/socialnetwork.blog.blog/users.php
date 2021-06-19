<?
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define('BX_SECURITY_SESSION_READONLY', true);
define("DisableEventsCheck", true);

$lng = (isset($_REQUEST["lang"]) && is_string($_REQUEST["lang"])) ? trim($_REQUEST["lang"]): "";
$lng = mb_substr(preg_replace("/[^a-z0-9_]/i", "", $lng), 0, 2);

/************** CACHE **********************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__, $lng);

$APPLICATION->RestartBuffer();
$arList = array(
	"post_id" => $_REQUEST["post_id"],
	"items" => array(),
	"StatusPage" => "done",
	"RecordCount" => 0);

$arResult["nPageSize"] = 20;
$_REQUEST["post_id"] = intval($_REQUEST["post_id"]);
$_REQUEST["name"] = trim($_REQUEST["name"]);
$_REQUEST["value"] = trim($_REQUEST["value"]);

$_REQUEST["PATH_TO_USER"] = ((!empty($_REQUEST["PATH_TO_UER"]) ? $_REQUEST["PATH_TO_UER"] : (!empty($_REQUEST["PATH_TO_USER"]) ? $_REQUEST["PATH_TO_USER"] : '/company/personal/user/#USER_ID#/')));
$_REQUEST["NAME_TEMPLATE"] = (!empty($_REQUEST["NAME_TEMPLATE"]) ? $_REQUEST["NAME_TEMPLATE"] : CSite::GetNameFormat(false));

if ($_REQUEST["post_id"] > 0 && !empty($_REQUEST["name"]) && !empty($_REQUEST["value"]) && check_bitrix_sessid())
{
	$arParams["CACHE_TIME"] = ($_REQUEST["iNumPage"] >= 2 ? 0 : 600);
	global $CACHE_MANAGER;
	$cache = new CPHPCache();
	$cache_id = "blog_post_param_".serialize(array(
		$arResult["nPageSize"],
		$_REQUEST["post_id"],
		$_REQUEST["name"],
		$_REQUEST["iNumPage"],
		$_REQUEST["value"],
		$_REQUEST["NAME_TEMPLATE"],
		$_REQUEST["PATH_TO_USER"]));

	$cache_path = $CACHE_MANAGER->GetCompCachePath(CComponentEngine::MakeComponentPath("socialnetwork.blog.blog"))."/".$_REQUEST["post_id"];

	$arList = (($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path)) ?
		$cache->GetVars() : array());
	if ((!is_array($arList) || empty($arList)) && CModule::IncludeModule("blog"))
	{
		$bMailInstalled = IsModuleInstalled('mail');
		$bExtranetInstalled = IsModuleInstalled('extranet');
		$arUserId = array();

		$db_res = CBlogUserOptions::GetList(
			array(
				"RANK" => "DESC",
				"OWNER_ID" => $GLOBALS["USER"]->GetID()),
			array(
				"POST_ID" => $_REQUEST["post_id"],
				"NAME" => $_REQUEST["name"],
				"VALUE" => $_REQUEST["value"],
				"USER_ACTIVE" => "Y"
			),
			array(
				"iNumPage" => ($_REQUEST["iNumPage"] > 0 ? $_REQUEST["iNumPage"] : 0),
				"bDescPageNumbering" => false,
				"nPageSize" => $arResult["nPageSize"],
				"bShowAll" => false,
				"SELECT" => array("USER_ID", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "USER_PERSONAL_PHOTO")
			)
		);
		$arList = is_array($arList) ? $arList : array();
		$arList["items"] = array();
		if ($db_res && ($res = $db_res->Fetch()))
		{
			$arList["StatusPage"] = (
				(
					$db_res->NavPageNomer >= $db_res->NavPageCount
					|| $arResult["nPageSize"] > $db_res->NavRecordCount
				)
					? "done"
					: "continue"
			);
			$arList["RecordCount"] = $db_res->NavRecordCount;
			if ($_REQUEST["iNumPage"] <= $db_res->NavPageCount)
			{
				do {
					$arUser = array(
						"ID" =>  $res["USER_ID"],
						"PHOTO" => "",
						"PHOTO_SRC" => "",
						"FULL_NAME" => CUser::FormatName(
							$_REQUEST["NAME_TEMPLATE"],
							array(
								"NAME" => $res["USER_NAME"],
								"LAST_NAME" => $res["USER_LAST_NAME"],
								"SECOND_NAME" => $res["USER_SECOND_NAME"],
								"LOGIN" => $res["USER_LOGIN"]
							)
						),
						"URL" => CUtil::JSEscape(
							CComponentEngine::MakePathFromTemplate(
								$_REQUEST["PATH_TO_USER"],
								array(
									"UID" => $res["USER_ID"],
									"user_id" => $res["USER_ID"],
									"USER_ID" => $res["USER_ID"]
								)
							)
						),
						"TYPE" => ""
					);
					if (array_key_exists("USER_PERSONAL_PHOTO", $res))
					{
						$arFileTmp = CFile::ResizeImageGet(
							$res["USER_PERSONAL_PHOTO"],
							array("width" => 21, "height" => 21),
							BX_RESIZE_IMAGE_EXACT,
							false
						);
						$arUser["PHOTO_SRC"] = $arFileTmp["src"];
						$arUser["PHOTO"] = CFile::ShowImage($arFileTmp["src"], 21, 21, "border=0");
					}

					$arList["items"][$arUser['ID']] = $arUser;
					$arUserId[] = $arUser["ID"];
				} while ($res = $db_res->Fetch());

				if (
					!empty($arUserId)
					&& ($bMailInstalled || $bExtranetInstalled)
				)
				{
					$arSelect = array();
					if ($bMailInstalled)
					{
						$arSelect["FIELDS"] = array("ID", "EXTERNAL_AUTH_ID");
					}
					if ($bExtranetInstalled)
					{
						$arSelect["SELECT"] = array("UF_DEPARTMENT");
					}

					$res = CUser::GetList(
						"ID",
						"ASC",
						array("ID" => implode("|", $arUserId)),
						$arSelect
					);
					while($ar = $res->Fetch())
					{
						if (
							$bMailInstalled
							&& $ar["EXTERNAL_AUTH_ID"] == "email"
						)
						{
							$arList["items"][$ar['ID']]["TYPE"] = "mail";
						}
						elseif (
							$bExtranetInstalled
							&& (
								empty($ar["UF_DEPARTMENT"])
								|| intval($ar["UF_DEPARTMENT"][0]) <= 0
							)
						)
						{
							$arList["items"][$ar['ID']]["TYPE"] = "extranet";
						}
					}
				}
			}
		}
		if (
			$arParams["CACHE_TIME"] > 0
			&& !empty($arList["items"])
		)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$CACHE_MANAGER->StartTagCache($cache_path);
			$CACHE_MANAGER->RegisterTag($_REQUEST["name"].$_REQUEST["post_id"]);
			$CACHE_MANAGER->EndTagCache();
			$cache->EndDataCache($arList);
		}
	}

	if (
		CModule::IncludeModule("socialnetwork")
		&& !CSocNetUser::IsCurrentUserModuleAdmin()
		&& CModule::IncludeModule("extranet")
	)
	{
		$arMyGroupsUserID = CExtranet::GetMyGroupsUsersSimple(CExtranet::GetExtranetSiteID());
		$arIntranetUsers = CExtranet::GetIntranetUsers();
		$bIntranetUser = CExtranet::IsIntranetUser();

		$arHidden = array(
			"ID" =>  0,
			"PHOTO" => "",
			"PHOTO_SRC" => "",
			"FULL_NAME" => GetMessage("BLOG_BLOG_USER_HIDDEN"),
			"URL" => "",
			"TYPE" => ""
		);

		foreach ($arList["items"] as $key => $arUser)
		{
			if (
				is_array($arMyGroupsUserID)
				&& is_array($arIntranetUsers)
				&& (
					(
						$bIntranetUser
						&& !in_array($arUser["ID"], $arIntranetUsers)
						&& !in_array($arUser["ID"], $arMyGroupsUserID)
					)
					|| 
					(
						!$bIntranetUser
						&& !in_array($arUser["ID"], $arMyGroupsUserID)
					)
				)
			)
			{
				$arList["items"][$key] = $arHidden;
			}
		}
	}
}

$APPLICATION->RestartBuffer();
Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
echo CUtil::PhpToJsObject($arList);
die();
?>