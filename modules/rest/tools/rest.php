<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Market\Subscription\Trial;
use Bitrix\Rest\Marketplace\Application;

define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

Loc::loadMessages(__FILE__);

$result = array();
$request = Bitrix\Main\Context::getCurrent()->getRequest();

if($request->isPost() && check_bitrix_sessid() && Loader::includeModule('rest'))
{
	$action = $request['action'];
	$admin = \CRestUtil::isAdmin();

	switch($action)
	{
		case 'install':
			$code = $request['code'];
			$version = $request['version'];
			$checkHash = $request['check_hash'];
			$installHash = $request['install_hash'];
			$from = $request['from'] ?? null;

			$result = Application::install($code, $version, $checkHash, $installHash, $from);
			if ($result['errorDescription'])
			{
				$result['error_description'] = $result['errorDescription'];
			}
		break;

		case 'uninstall':
			$code = $request['code'];
			$clean = $request['clean'] == 'true';
			$from = $request['from'] ?? null;

			$result = Application::uninstall($code, $clean, $from);
		break;

		case 'reinstall':
			$id = $request['id'];

			$result = Application::reinstall($id);
		break;

		case 'get_app_rigths':
			$appId = (int) $request['app_id'];

			$result = Application::getRights($appId);
		break;

		case 'set_app_rights':
			$appId = (int) $request['app_id'];
			$rights = $request->getPost('rights');

			$result = Application::setRights($appId, $rights);
		break;

		case 'activate_demo':
			if (
				Loader::includeModule('market')
				&& Trial::isAvailable()
				&& (
					!ModuleManager::isModuleInstalled('extranet')
					|| (Loader::includeModule('extranet') && \CExtranet::IsIntranetUser())
				)
			)
			{
				$result = Trial::activate();
			}
			else
			{
				$result = [
					'error' => Loc::getMessage('REST_MP_CONFIG_ACTIVATE_ERROR'),
				];
			}

			break;


		default:
			$result = array('error' => 'Unknown action');
	}
}

Header('Content-Type: application/json');
echo \Bitrix\Main\Web\Json::encode($result);

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_after.php");