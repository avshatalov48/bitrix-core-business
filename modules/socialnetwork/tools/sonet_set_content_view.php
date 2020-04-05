<?
define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);
define("PUBLIC_AJAX_MODE", true);

$site_id = (isset($_REQUEST["site"]) && is_string($_REQUEST["site"])) ? trim($_REQUEST["site"]): "";
$site_id = substr(preg_replace("/[^a-z0-9_]/i", "", $site_id), 0, 2);

define("SITE_ID", $site_id);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");

$xmlIdList = (
	isset($_REQUEST["viewXMLIdList"])
	&& is_array($_REQUEST["viewXMLIdList"])
		? $_REQUEST["viewXMLIdList"]
		: array()
);

$lng = (isset($_REQUEST["lang"]) && is_string($_REQUEST["lang"])) ? trim($_REQUEST["lang"]): "";
$lng = substr(preg_replace("/[^a-z0-9_]/i", "", $lng), 0, 2);

$action = (isset($_REQUEST["action"]) && is_string($_REQUEST["action"])) ? trim($_REQUEST["action"]): "";
$action = preg_replace("/[^a-z0-9_]/i", "", $action);

$contentId = (isset($_REQUEST["contentId"]) && is_string($_REQUEST["contentId"])) ? trim($_REQUEST["contentId"]) : "";
$page = (isset($_REQUEST["page"]) && intval($_REQUEST["page"]) > 0) ? intval($_REQUEST["page"]) : 1;

$pathToUserProfile = (isset($_REQUEST["pathToUserProfile"]) && is_string($_REQUEST["pathToUserProfile"])) ? trim($_REQUEST["pathToUserProfile"]) : "";

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Socialnetwork\Livefeed;
use Bitrix\Main\Loader;

if(Loader::includeModule("compression"))
{
	CCompress::Disable2048Spaces();
}

$result = array();
if(
	check_bitrix_sessid()
	&& Loader::includeModule("socialnetwork")
	&& in_array($action, array('set_content_view', 'get_view_list'))
)
{
	if (
		$action == 'set_content_view'
		&& !empty($xmlIdList)
	)
	{
		foreach($xmlIdList as $val)
		{
			$xmlId = $val['xmlId'];
			$save = (!isset($val['save']) || $val['save'] != 'N');

			$tmp = explode('-', $xmlId, 2);
			$entityType = trim($tmp[0]);
			$entityId = intval($tmp[1]);

			if (
				!empty($entityType)
				&& $entityId > 0
			)
			{
				$provider = Livefeed\Provider::init(array(
					'ENTITY_TYPE' => $entityType,
					'ENTITY_ID' => $entityId,
				));
				if ($provider)
				{
					$provider->setContentView(array(
						'save' => $save
					));
				}
			}
		}
	}
	elseif (
		$action == 'get_view_list'
		&& !empty($contentId)
	)
	{
		$userList = \Bitrix\Socialnetwork\Item\UserContentView::getUserList(array(
			'contentId' => $contentId,
			'page' => $page,
			'pathToUserProfile' => $pathToUserProfile
		));

		$result['items'] = $userList['items'];
		$result['itemsCount'] = count($result['items']);
		$result['hiddenCount'] = $userList['hiddenCount'];
	}

	$result["SUCCESS"] = "Y";
}

if (empty($_REQUEST['mobile_action']))
{
	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
}
echo CUtil::PhpToJSObject($result);
\CMain::finalActions();
die;
?>