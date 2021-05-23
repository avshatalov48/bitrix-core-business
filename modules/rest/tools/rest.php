<?php
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Rest\Marketplace\Application;
use Bitrix\Rest\Engine\Access;

Loc::loadMessages(__FILE__);

$result = array();
$request = Bitrix\Main\Context::getCurrent()->getRequest();

if($request->isPost() && check_bitrix_sessid() && \Bitrix\Main\Loader::includeModule('rest'))
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
			if ($admin)
			{
				if (!\Bitrix\Rest\OAuthService::getEngine()->isRegistered())
				{
					try
					{
						\Bitrix\Rest\OAuthService::register();
						\Bitrix\Rest\OAuthService::getEngine()->getClient()->getApplicationList();
					}
					catch(\Bitrix\Main\SystemException $e)
					{
						$result = [
							'error' => Loc::getMessage('REST_MP_CONFIG_ACTIVATE_ERROR'),
							'error_description' => $e->getMessage(),
							'error_code' => $e->getCode()
						];
					}
				}
				else
				{
					try
					{
						\Bitrix\Rest\OAuthService::getEngine()->getClient()->getApplicationList();
					}
					catch(\Bitrix\Main\SystemException $e)
					{
						$result = [
							'error' => Loc::getMessage('REST_MP_CONFIG_ACTIVATE_ERROR'),
							'error_description' => $e->getMessage(),
							'error_code' => 4
						];
					}
				}

				if (\Bitrix\Rest\OAuthService::getEngine()->isRegistered())
				{
					$queryField = [];
					$loadedBitrix24 = \Bitrix\Main\Loader::includeModule('bitrix24');
					if ($loadedBitrix24)
					{
						$host = '';
						if (defined('BX24_HOST_NAME'))
						{
							$host = BX24_HOST_NAME;
						}
						else
						{
							$server = \Bitrix\Main\Context::getCurrent()->getServer();
							$host = $server->getHttpHost();
						}

						$queryField = [
							'DEMO' => 'subscription',
							'SITE' => $host
						];

						if (function_exists('bx_sign'))
						{
							$queryField['hash'] = bx_sign(md5(implode('|', $queryField)));
						}
					}
					else
					{
						include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/license_key.php';

						if (!empty($LICENSE_KEY))
						{
							$queryField = [
								'DEMO' => 'subscription',
								'SITE' => 'cp',
								'key' => md5($LICENSE_KEY),
								'hash' => md5('cp'. '|' . 'subscription' . '|' . md5($LICENSE_KEY))
							];
						}
					}

					if ($queryField)
					{
						$httpClient = new \Bitrix\Main\Web\HttpClient();
						if ($response = $httpClient->post('https://www.1c-bitrix.ru/buy_tmp/b24_coupon.php', $queryField))
						{
							if (mb_strpos($response, 'OK') === false)
							{
								$result = [
									'error' => Loc::getMessage('REST_MP_CONFIG_ACTIVATE_ERROR'),
									'error_code' => 2,
								];
							}
							else
							{
								$result = ['result' => true];
							}

							if (!$loadedBitrix24)
							{
								require_once( $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/update_client.php');
								$errorMessage = '';
								CUpdateClient::GetUpdatesList($errorMessage, LANG);
							}
						}
					}
				}
				elseif (!$result['error'])
				{
					$result = [
						'error' => Loc::getMessage('REST_MP_CONFIG_ACTIVATE_ERROR'),
						'error_code' => 1,
					];
				}
			}
			else
			{
				$result = ['error' => Loc::getMessage('REST_ACTIVATE_DEMO_ACCESS_DENIED')];
			}

			break;


		default:
			$result = array('error' => 'Unknown action');
	}
}

Header('Content-Type: application/json');
echo \Bitrix\Main\Web\Json::encode($result);

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_after.php");