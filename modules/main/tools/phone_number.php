<?
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("NO_AGENT_CHECK", true);

if(isset($_REQUEST['tpl']) && isset($_REQUEST['tpls']))
{
	define('SITE_TEMPLATE_ID', $_REQUEST['tpl']);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

/**
 * Bitrix vars
 *
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CUserTypeManager $USER_FIELD_MANAGER
 */

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter());

if(check_bitrix_sessid())
{
	$action = $request['ACTION'];

	switch($action)
	{
		case 'getCountries':
			$result = GetCountries();
			break;
		default:
			$result = array(
				'ERROR' => 'Unknown action'
			);
			break;
	}

	Header('Content-Type: application/json');
	echo \Bitrix\Main\Web\Json::encode($result);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");