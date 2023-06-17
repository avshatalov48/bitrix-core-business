<?
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);

use Bitrix\Main\UserField\{TypeBase, Dispatcher, Display};
use Bitrix\Main\Web\PostDecodeFilter;
use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Main\UserField\Types\BaseType;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if(isset($_REQUEST['mode']) && $_REQUEST['mode'] === BaseType::MODE_VIEW)
{
	define('BX_SECURITY_SESSION_READONLY', true);
}

if(isset($_REQUEST['tpl']) && isset($_REQUEST['tpls']) && !defined('SITE_TEMPLATE_ID'))
{
	define('SITE_TEMPLATE_ID', $_REQUEST['tpl']);
}

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$request->addFilter(new PostDecodeFilter());

if(check_bitrix_sessid())
{
	$fields = $request['FIELDS'];
	if(!is_array($fields))
	{
		$fields = array();
	}

	$userFieldDispatcher = Dispatcher::instance();

	if(
		isset($_REQUEST['tpl'])
		&& isset($_REQUEST['tpls'])
		&& !$userFieldDispatcher->getSignatureManager()->validateSignature(SITE_TEMPLATE_ID, $request['tpls'])
	)
	{
		die();
	}

	if(isset($request['lang']))
	{
		$userFieldDispatcher->setLanguage($request['lang']);
	}

	foreach($fields as $fieldInfo)
	{
		if(isset($request['action']))
		{
			switch($request['action'])
			{
				case 'add':
					$userFieldDispatcher->createField($fieldInfo);

				break;

				case 'update':
					$userFieldDispatcher->editField($fieldInfo);

				break;

				case 'delete':
					$userFieldDispatcher->deleteField($fieldInfo);

				break;

				case 'validate':
					$userFieldDispatcher->validateField($fieldInfo);

				break;
			}
		}
		else
		{
			$userFieldDispatcher->addField($fieldInfo);
		}
	}

	$mode = $request['mode'];

	$mediaType = !empty($request['MEDIA_TYPE']) ? $request['MEDIA_TYPE'] : BaseUfComponent::MEDIA_TYPE_DEFAULT;

	$view = new Display($mode, $mediaType);

	if(isset($request['FORM']))
	{
		$view->setAdditionalParameter('form_name', $request['FORM'], true);
	}

	if(isset($request['CONTEXT']))
	{
		$view->setAdditionalParameter('CONTEXT', $request['CONTEXT'], true);
	}

	if(isset($request['MEDIA_TYPE']))
	{
		$view->setAdditionalParameter('mediaType', $request['MEDIA_TYPE'], true);
	}

	$userFieldDispatcher->setView($view);

	$result = $userFieldDispatcher->getResult();

	Header('Content-Type: application/json');
	echo \Bitrix\Main\Web\Json::encode($result);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");