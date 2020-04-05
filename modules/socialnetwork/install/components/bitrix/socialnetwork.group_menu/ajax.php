<?
define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);
define("BX_PUBLIC_TOOLS", true);

$lng = (isset($_REQUEST["lang"]) && is_string($_REQUEST["lang"])) ? trim($_REQUEST["lang"]): "";
$lng = substr(preg_replace("/[^a-z0-9_]/i", "", $lng), 0, 2);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__, $lng);
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

if(CModule::IncludeModule("compression"))
{
	CCompress::Disable2048Spaces();
}

if (!CModule::IncludeModule("socialnetwork"))
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'SONET_MODULE_NOT_INSTALLED'));
	die();
}

if (!$USER->IsAuthorized())
{
	echo CUtil::PhpToJsObject(Array("ERROR" => "CURRENT_USER_NOT_AUTH"));
	die();
}

$groupID = intval($_POST["groupID"]);
if ($groupID <= 0)
{
	echo CUtil::PhpToJsObject(Array("ERROR" => "EMPTY_GROUP_ID"));
	die();
}

$action = $_POST["action"];

if (check_bitrix_sessid())
{
	if (in_array($action, array("set", "unset", "fav_set", "fav_unset")))
	{
		if (in_array($action, array("set", "unset")))
		{
			$userRole = CSocNetUserToGroup::GetUserRole($USER->GetID(), $groupID);
			if (!in_array($userRole, array(SONET_ROLES_OWNER, SONET_ROLES_MODERATOR, SONET_ROLES_USER)))
			{
				echo CUtil::PhpToJsObject(Array("ERROR" => "INCORRECT_USER_ROLE"));
				die();
			}

			if (CSocNetSubscription::set($USER->GetID(), "SG".$groupID, ($action == "set" ? "Y" : "N")))
			{
				$rsSubscription = CSocNetSubscription::getList(
					array(),
					array(
						"USER_ID" => $USER->GetID(),
						"CODE" => "SG".$groupID
					)
				);
				if ($arSubscription = $rsSubscription->fetch())
				{
					echo CUtil::PhpToJsObject(Array(
						"SUCCESS" => "Y",
						"RESULT" => "Y"
					));
				}
				else
				{
					echo CUtil::PhpToJsObject(Array(
						"SUCCESS" => "Y",
						"RESULT" => "N"
					));
				}
			}
		}
		elseif(in_array($action, array("fav_set", "fav_unset")))
		{
			$res = false;
			try
			{
				$res = \Bitrix\Socialnetwork\Item\WorkgroupFavorites::set(array(
					'GROUP_ID' => $groupID,
					'USER_ID' => $USER->getId(),
					'VALUE' => ($action == 'fav_set' ? 'Y' : 'N')
				));
			}
			catch (Exception $e)
			{
				echo CUtil::PhpToJsObject(Array("ERROR" => $e->getMessage()));
			}

			if ($res)
			{
				$groupItem = \Bitrix\Socialnetwork\Item\Workgroup::getById($groupID);
				$groupFields = $groupItem->getFields();
				$groupUrlData = $groupItem->getGroupUrlData(array(
					'USER_ID' => $USER->getId()
				));

				$groupSiteList = array();
				$resSite = \Bitrix\Socialnetwork\WorkgroupSiteTable::getList(array(
					'filter' => array(
						'=GROUP_ID' => $groupID
					),
					'select' => array('SITE_ID')
				));
				while ($groupSite = $resSite->fetch())
				{
					$groupSiteList[] = $groupSite['SITE_ID'];
				}

				$result = Array(
					"SUCCESS" => "Y",
					"ID" => $groupID,
					"NAME" => $groupFields["NAME"],
					"URL" => $groupUrlData["URL"],
					"EXTRANET" => (
						\Bitrix\Main\Loader::includeModule('extranet')
						&& CExtranet::isIntranetUser()
						&& in_array(CExtranet::getExtranetSiteID(), $groupSiteList)
							? 'Y'
							: 'N'
					)
				);

				if ($action == 'fav_set')
				{
					$result["RESULT"] = "Y";
				}
				else
				{
					$result["RESULT"] = "N";
				}

				echo CUtil::PhpToJsObject($result);
			}
			else
			{
				echo CUtil::PhpToJsObject(Array("ERROR" => Loc::getMessage('SONET_GMA_ACTION_FAILED')));
			}
		}
	}
	else
	{
		echo CUtil::PhpToJsObject(Array("ERROR" => "UNKNOWN_ACTION"));
	}
}
else
{
	echo CUtil::PhpToJsObject(Array("ERROR" => "SESSION_ERROR"));
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>