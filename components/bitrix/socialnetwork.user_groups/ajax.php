<?
define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);

$siteId = (isset($_POST["site"]) && is_string($_POST["site"])) ? trim($_POST["site"]) : "";
$siteId = mb_substr(preg_replace("/[^a-z0-9_]/i", "", $siteId), 0, 2);
$groupId = (isset($_POST["groupId"]) ? intval($_POST["groupId"]) : 0);
$action = (isset($_POST["action"]) ? $_POST["action"] : false);

define("SITE_ID", $siteId);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Loader;
/** @global CMain $APPLICATION */
/** @global CUser $USER */
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

$res = CSite::getByID($siteId);
if ($site = $res->fetch())
{
	define("LANGUAGE_ID", $site["LANGUAGE_ID"]);
}
else
{
	define("LANGUAGE_ID", "en");
}

if (!Loader::includeModule("socialnetwork"))
{
	echo CUtil::phpToJsObject(Array('ERROR' => 'SONET_MODULE_NOT_INSTALLED'));
	die();
}

if (!$USER->isAuthorized())
{
	echo CUtil::phpToJsObject(Array('ERROR' => 'CURRENT_USER_NOT_AUTH'));
	die();
}

if ($groupId <= 0)
{
	echo CUtil::phpToJsObject(Array('ERROR' => 'GROUP_ID_NOT_DEFINED'));
	die();
}
else
{
	$group = CSocNetGroup::GetByID($groupId);
	if (!$group)
	{
		echo CUtil::phpToJsObject(Array('ERROR' => 'GROUP_ID_NOT_DEFINED'));
		die();
	}
}

if (check_bitrix_sessid())
{
	$currentUserPerms = \Bitrix\Socialnetwork\Helper\Workgroup::getPermissions([
		'groupId' => $groupId,
	]);

	if (!$currentUserPerms["UserCanViewGroup"])
	{
		echo CUtil::phpToJsObject(Array('ERROR' => 'CANNOT_VIEW_GROUP'));
		die();
	}

	if (
		$action == 'REQUEST'
		&& !empty($currentUserPerms["UserRole"])
	)
	{
		echo CUtil::phpToJsObject(Array('ERROR' => 'ALREADY_HAS_ROLE'));
		die();
	}

	if ($action == 'REQUEST')
	{
		$url = CComponentEngine::MakePathFromTemplate(COption::GetOptionString("socialnetwork", "workgroups_page", "/workgroups/", SITE_ID)."group/#group_id#/requests/", array("group_id" => $groupId));
		$res = CSocNetUserToGroup::sendRequestToBeMember($USER->getId(), $group["ID"], "", $url, false);
		if (
			!$res
			&& ($e = $APPLICATION->getException())
		)
		{
			echo CUtil::phpToJsObject(Array('ERROR' => $e->GetString()));
			die();
		}
	}
	elseif ($action == 'FAVORITES')
	{
		$value = (isset($_POST["params"]["value"]) && $_POST["params"]["value"] ? $_POST["params"]["value"] : 'Y');

		$res = false;
		try
		{
			$res = \Bitrix\Socialnetwork\Item\WorkgroupFavorites::set(array(
				'GROUP_ID' => $groupId,
				'USER_ID' => $USER->getId(),
				'VALUE' => $value
			));
		}
		catch (Exception $e)
		{
			echo CUtil::phpToJsObject(Array('ERROR' => $e->getMessage()));
			die();
		}
	}

	$result = array('SUCCESS' => 'Y');

	if ($action == 'REQUEST')
	{
		$res = \Bitrix\Socialnetwork\UserToGroupTable::getList(array(
			'filter' => array(
				'GROUP_ID' => $group["ID"],
				'USER_ID' => $USER->getId()
			),
			'select' => array('ROLE')
		));

		if ($relation = $res->fetch())
		{
			$result['ROLE'] = $relation['ROLE'];
		}
	}
	elseif ($action == 'FAVORITES')
	{
		$groupItem = \Bitrix\Socialnetwork\Item\Workgroup::getById($group["ID"]);
		$groupFields = $groupItem->getFields();
		$groupUrlData = $groupItem->getGroupUrlData(array(
			'USER_ID' => $USER->getId()
		));

		$groupSiteList = array();
		$resSite = \Bitrix\Socialnetwork\WorkgroupSiteTable::getList(array(
			'filter' => array(
				'=GROUP_ID' => $group["ID"]
			),
			'select' => array('SITE_ID')
		));
		while ($groupSite = $resSite->fetch())
		{
			$groupSiteList[] = $groupSite['SITE_ID'];
		}

		$result["NAME"] = $groupFields["NAME"];
		$result["URL"] = $groupUrlData["URL"];
		$result["EXTRANET"] = (
			\Bitrix\Main\Loader::includeModule('extranet')
			&& CExtranet::isIntranetUser()
			&& in_array(CExtranet::getExtranetSiteID(), $groupSiteList)
				? 'Y'
				: 'N'
		);
	}

	echo CUtil::PhpToJsObject($result);
}
else
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'SESSION_ERROR'));
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>