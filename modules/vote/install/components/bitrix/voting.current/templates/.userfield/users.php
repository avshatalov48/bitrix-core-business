<?
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);
/************** CACHE **********************************************/
$arResult["nPageSize"] = 10;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$arVoteList = array(
	"ID" => $_REQUEST["ID"],
	"answer_id" => $_REQUEST["answer_id"],
	"request_id" => $_REQUEST["request_id"],
	"items" => array(),
	"StatusPage" => "done");
$_REQUEST["ID"] = is_array($_REQUEST["ID"]) ? $_REQUEST["ID"] : !empty($_REQUEST["ID"]) ? explode(",", $_REQUEST["ID"]) : array();
$_REQUEST["URL_TEMPLATE"] = (!empty($_REQUEST["URL_TEMPLATE"]) ? $_REQUEST["URL_TEMPLATE"] : '/company/personal/user/#USER_ID#/');
$_REQUEST["NAME_TEMPLATE"] = (!empty($_REQUEST["NAME_TEMPLATE"]) ? $_REQUEST["NAME_TEMPLATE"] : CSite::GetNameFormat(false));

if ((!empty($_REQUEST["ID"]) || !empty($_REQUEST["answer_id"])) && check_bitrix_sessid())
{
	$arParams["CACHE_TIME"] = 600;
	global $CACHE_MANAGER;
	$cache = new CPHPCache();
	$cache_id = "vote_user_list_".serialize(array(
		$arResult["nPageSize"],
		$_REQUEST["ID"],
		$_REQUEST["answer_id"],
		$_REQUEST["iNumPage"],
		$_REQUEST["NAME_TEMPLATE"],
		$_REQUEST["URL_TEMPLATE"]));
	$cache_path = $CACHE_MANAGER->GetCompCachePath(CComponentEngine::MakeComponentPath("voting.current"));
	$arVoteList = (($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path)) ?
		$cache->GetVars() : array());
	if (!is_array($arVoteList) || empty($arVoteList))
	{
		$arEventsInfo = array();
		$arVoteList = array(
			"ID" => $_REQUEST["ID"],
			"answer_id" => $_REQUEST["answer_id"],
			"request_id" => $_REQUEST["request_id"],
			"items" => array(),
			"StatusPage" => "done");

		if (empty($_REQUEST["ID"]) && CModule::IncludeModule("vote"))
		{
			$db_res = CVoteEvent::GetUserAnswerStat(array(),
				array("ANSWER_ID" => $_REQUEST["answer_id"], "VALID" => "Y", "bGetVoters" => "Y", "bGetMemoStat" => "N"),
				array(
					"nPageSize" => $arResult["nPageSize"],
					"bShowAll" => false,
					"iNumPage" => ($_REQUEST["iNumPage"] > 0 ? $_REQUEST["iNumPage"] : false)
				)
			);
			if ($db_res && ($res = $db_res->Fetch()))
			{
				$arEventsInfo = $res;
				$arVoteList["StatusPage"] = (($db_res->NavPageNomer >= $db_res->NavPageCount ||
					$arResult["nPageSize"] > $db_res->NavRecordCount) ? "done" : "continue");
				if ($_REQUEST["iNumPage"] <= $db_res->NavPageCount)
				{
					$_REQUEST["ID"] = array();
					do {
						$_REQUEST["ID"][] = $res["AUTH_USER_ID"];
					} while ($res = $db_res->Fetch());
				}
				else
				{
					$arVoteList["StatusPage"] = "done";
				}
			}
		}

		if (!empty($_REQUEST["ID"]))
		{
			$arUsers = array();

			$arSelect = array(
				"FIELDS" => array("ID", "NAME", "LAST_NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO")
			);
			if (IsModuleInstalled('mail'))
			{
				$arSelect["FIELDS"][] = "EXTERNAL_AUTH_ID";
				$bMailInstalled = true;
			}
			if (IsModuleInstalled('extranet'))
			{
				$arSelect["SELECT"] = array("UF_DEPARTMENT");
				$bExtranetInstalled = true;
			}
			$db_res = CUser::GetList(
				($by = "ID"),
				($order = "ASC"),
				array("ID" => implode("|", $_REQUEST["ID"])),
				$arSelect
			);

			while ($res = $db_res->Fetch())
			{
				$data = array(
					"ID" => $res["ID"]
				);
				if (array_key_exists("PERSONAL_PHOTO", $res))
				{
					if (!empty($res["PERSONAL_PHOTO"]))
					{
						$arFileTmp = CFile::ResizeImageGet(
							$res["PERSONAL_PHOTO"],
							array("width" => 21, "height" => 21),
							BX_RESIZE_IMAGE_EXACT,
							false
						);
						$data["PHOTO"] = CFile::ShowImage($arFileTmp["src"], 21, 21, "border=0");
						$data["PHOTO_SRC"] = $arFileTmp["src"];
					}
					else
					{
						$data["PHOTO"] = $data["PHOTO_SRC"] = '';
					}
				}
				$data["FULL_NAME"] = CUser::FormatName($_REQUEST["NAME_TEMPLATE"], $res);
				$data["URL"] = CUtil::JSEscape(CComponentEngine::MakePathFromTemplate($_REQUEST["URL_TEMPLATE"], array("UID" => $res["ID"], "user_id" => $res["ID"], "USER_ID" => $res["ID"])));
				if (
					$bMailInstalled
					&& $res["EXTERNAL_AUTH_ID"] == "email"
				)
				{
					$data["TYPE"] = "mail";
				}
				elseif (
					$bExtranetInstalled
					&& (
						empty($res["UF_DEPARTMENT"])
						|| intval($res["UF_DEPARTMENT"][0]) <= 0
					)
				)
				{
					$data["TYPE"] = "extranet";
				}

				$arUsers[$res["ID"]] = $data;
			}
			$arVoteList["items"] = array();
			foreach($_REQUEST["ID"] as $id)
				$arVoteList["items"][] = $arUsers[$id];
			if ($arParams["CACHE_TIME"] > 0)
			{
				$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
				if (!!$arEventsInfo)
				{
					$db_res = CVoteQuestion::GetByID($arEventsInfo["QUESTION_ID"]);
					if ($db_res && ($res = $db_res->Fetch()))
					{
						CVoteCacheManager::SetTag($cache_path, "V", $res["VOTE_ID"]);
					}
				}
				$cache->EndDataCache($arVoteList);
			}
		}
	}
}

$APPLICATION->RestartBuffer();
Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
echo CUtil::PhpToJsObject($arVoteList);
die();
?>