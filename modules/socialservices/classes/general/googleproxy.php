<?php

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Security\Cipher;
use Bitrix\Main\Service\MicroService\Client;
use Bitrix\Main\SystemException;
use Bitrix\SocialServices\UserTable;
use Bitrix\Main\Web\HttpClient;


class CSocServGoogleProxyOAuth extends CSocServGoogleOAuth
{
	public const PROXY_CONST = 'BITRIX';
	/**
	 * @var \Bitrix\Main\EO_User
	 */
	private $user;

	/**
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function isProxyAuth(): bool
	{
		return !\Bitrix\Main\Loader::includeModule('bitrix24')
			&& (\Bitrix\Main\Config\Option::get('socialservices', 'google_sync_proxy', 'N') === 'Y');
	}

	public function Authorize()
	{
		global $APPLICATION;
		$APPLICATION->RestartBuffer();

		$bSuccess = false;
		$addParams = false;

		$authError = SOCSERV_AUTHORISATION_ERROR;

		$state = $this->parseState($_REQUEST['state']);

		if(!empty($_REQUEST["code"]) && $this->checkUserToken($state['user_token']))
		{
			$this->getEntityOAuth()->setCode($_REQUEST["code"]);

			unset($_REQUEST["state"]);

			if($this->getEntityOAuth()->GetAccessToken() !== false)
			{
				$arGoogleUser = $this->getEntityOAuth()->GetCurrentUser();

				if(is_array($arGoogleUser) && !isset($arGoogleUser["error"]))
				{
					$arFields = $this->prepareUser($arGoogleUser);
					$arFields['USER_ID'] = $this->user->getId();
					$authError = $this->AuthorizeUser($arFields);
				}
			}
		}

		$aRemove = ["logout", "auth_service_error", "auth_service_id", "code", "error_reason", "error", "error_description", "check_key", "current_fieldset"];
		$mode = null;

		if($this->user && ($authError === true))
		{
			$bSuccess = true;
			CSocServUtil::checkOAuthProxyParams();

			$url = ($APPLICATION->GetCurDir() === "/login/") ? "" : $APPLICATION->GetCurDir();
			$mode = 'opener';
			$addParams = true;
			if(isset($state) && is_array($state))
			{
				if(isset($state['backurl']) || isset($state['redirect_url']))
				{
					$url = !empty($state['redirect_url']) ? $state['redirect_url'] : $state['backurl'];
					if(!str_starts_with($url, "#"))
					{
						$parseUrl = parse_url($url);

						$urlPath = $parseUrl["path"];
						$arUrlQuery = explode('&', $parseUrl["query"]);

						foreach($arUrlQuery as $key => $value)
						{
							foreach($aRemove as $param)
							{
								if(str_starts_with($value, $param . "="))
								{
									unset($arUrlQuery[$key]);
									break;
								}
							}
						}

						$url = (!empty($arUrlQuery)) ? $urlPath . '?' . implode("&", $arUrlQuery) : $urlPath;
					}
					else
					{
						$addParams = false;
					}
				}

				if(isset($state['mode']))
				{
					$mode = $state['mode'];
				}
			}
		}

		if($authError === SOCSERV_REGISTRATION_DENY)
		{
			$url = (preg_match("/\?/", $url)) ? $url.'&' : $url.'?';
			$url .= 'auth_service_id='.static::ID.'&auth_service_error='.SOCSERV_REGISTRATION_DENY;
		}
		elseif($bSuccess !== true)
		{
			$url = (isset($urlPath)) ? $urlPath.'?auth_service_id='.static::ID.'&auth_service_error='.$authError : $APPLICATION->GetCurPageParam(('auth_service_id='.static::ID.'&auth_service_error='.$authError), $aRemove);
		}

		if($addParams && CModule::IncludeModule("socialnetwork") && !str_contains($url, "current_fieldset="))
		{
			$url = (preg_match("/\?/", $url)) ? $url."&current_fieldset=SOCSERV" : $url."?current_fieldset=SOCSERV";
		}

		$url = CUtil::JSEscape($url);

		if ($bSuccess && $mode === self::MOBILE_MODE)
		{
			$this->onAfterMobileAuth();
		}
		else
		{
			$this->onAfterWebAuth($addParams, $mode, $url);
		}

		CMain::FinalActions();
	}

	public function getUrl($location = 'opener', $addScope = null, $arParams = array())
	{
		if(IsModuleInstalled('bitrix24') && defined('BX24_HOST_NAME'))
		{
			return '';
		}

		$this->entityOAuth = $this->getEntityOAuth();

		if($this->userId === null)
		{
			$this->entityOAuth->setRefreshToken("skip");
		}

		if($addScope !== null)
		{
			$this->entityOAuth->addScope($addScope);
		}

		$state = 'provider='.static::ID
			. '&site_id=' . SITE_ID
			. '&backurl=' . urlencode(
				$GLOBALS["APPLICATION"]
					->GetCurPageParam(
						'check_key=' . \CSocServAuthManager::getUniqueKey(),
						["logout", "auth_service_error", "auth_service_id", "backurl", 'serviceName', 'hitHash']
					)
			)
			. '&mode=' . $location
				. (isset($arParams['BACKURL'])
					? '&redirect_url=' . urlencode($arParams['BACKURL'])
					: '')
			. '&user_token=' . urlencode($this->generateUserToken())
			. '&hostUrl=' . urlencode(\Bitrix\Main\Engine\UrlManager::getInstance()->getHostUrl())
		;

		$redirect_uri = $this->getEntityOAuth()->getRedirectUri();

		return $this->entityOAuth->GetAuthUrl($redirect_uri, $state, $arParams['APIKEY']);
	}

	/**
	 * @param string $code=false
	 * @return CGoogleOAuthInterface
	 */
	public function getEntityOAuth($code = false)
	{
		if(!($this->entityOAuth instanceof CGoogleProxyOAuthInterface))
		{
			$this->entityOAuth = new CGoogleProxyOAuthInterface();
		}

		if($code !== false)
		{
			$this->entityOAuth->setCode($code);
		}

		return $this->entityOAuth;
	}

	/**
	 * @return string
	 * @throws SystemException
	 * @throws \Bitrix\Main\Security\SecurityException
	 */
	private function generateUserToken(): string
	{
		$configuration = Configuration::getInstance();
		$cipherKey = $configuration->get('crypto')['crypto_key'] ?? null;
		if (!$cipherKey)
		{
			throw new SystemException('There is no crypto[crypto_key] in .settings.php. Generate it.');
		}

		$cipher = new Cipher();

		return base64_encode($cipher->encrypt(time() . '_'. $this->userId .'_' . self::PROXY_CONST, $cipherKey));
	}

	/**
	 * @param string|null $userToken
	 * @return bool
	 * @throws SystemException
	 * @throws \Bitrix\Main\Security\SecurityException
	 */
	private function checkUserToken(string $userToken = null): bool
	{
		if (!$userToken)
		{
			return false;
		}

		$configuration = Configuration::getInstance();
		$cipherKey = $configuration->get('crypto')['crypto_key'] ?? null;
		if (!$cipherKey)
		{
			throw new SystemException('There is no crypto[crypto_key] in .settings.php. Generate it.');
		}

		$cipher = new Cipher();
		$data = explode('_', $cipher->decrypt(base64_decode($userToken), $cipherKey));
		if (
			empty($data[1])
			|| (($data[0] + 3600) < time())
			|| $data[2] !== self::PROXY_CONST
		)
		{
			return false;
		}

		$user = \Bitrix\Main\UserTable::query()
			->where('ID', (int)$data[1])
			->setSelect(['*'])
			->exec()
			->fetchObject()
		;

		if (!$user)
		{
			return false;
		}

		$this->user = $user;
		$this->userId = $data[1];

		return true;
	}

	private function parseState(string $requestState = null): ?array
	{
		if (!$requestState)
		{
			return null;
		}

		$state = [];
		parse_str($requestState, $state);

		if (!$state)
		{
			return null;
		}

		return $state;
	}

	public function AuthorizeUser($socservUserFields, $bSave = false)
	{
		if(!isset($socservUserFields['XML_ID']) || $socservUserFields['XML_ID'] == '')
		{
			return false;
		}

		if(!isset($socservUserFields['EXTERNAL_AUTH_ID']) || $socservUserFields['EXTERNAL_AUTH_ID'] == '')
		{
			return false;
		}

		$dbSocUser = UserTable::getList([
			'filter' => [
				'=XML_ID'=>$socservUserFields['XML_ID'],
				'=EXTERNAL_AUTH_ID'=>$socservUserFields['EXTERNAL_AUTH_ID']
			],
			'select' => ["ID", "USER_ID", "ACTIVE" => "USER.ACTIVE", "PERSONAL_PHOTO"],
		]);
		$socservUser = $dbSocUser->fetch();

		if(!empty($socservUserFields['USER_ID']))
		{
			if(!$socservUser)
			{
				$result = UserTable::add(UserTable::filterFields($socservUserFields));
			}
			else
			{
				$result = UserTable::update($socservUser['ID'], UserTable::filterFields($socservUserFields));
			}
		}
		else
		{
			return false;
		}

		return $result->isSuccess();
	}
}

class CGoogleProxyOAuthInterface extends CGoogleOAuthInterface
{
	public const TOKEN_URL = "https://calendar-proxy-ru-01.bitrix24.com";

	public function __construct($appID = false, $appSecret = false, $code = false)
	{
		parent::__construct($this->getAppId(), null, $code);
	}

	public function GetAccessToken($redirect_uri = false)
	{
		$tokens = $this->getStorageTokens();

		if(is_array($tokens))
		{
			$this->access_token = $tokens["OATOKEN"];
			$this->accessTokenExpires = $tokens["OATOKEN_EXPIRES"];

			if(!$this->code)
			{
				if ($this->checkAccessToken())
				{
					return true;
				}

				if(
					isset($tokens["REFRESH_TOKEN"])
					&& $this->getNewAccessToken(
						$tokens["REFRESH_TOKEN"],
						$this->userId,
						true
					)
				)
				{
					return true;
				}
			}

			$this->deleteStorageTokens();
		}

		if($this->code === false)
		{
			return false;
		}

		$http = new HttpClient([
			"socketTimeout" => $this->httpTimeout
		]);

		$params = array_merge(
			$this->getLicenseParams(),
			[
				"client_id" => $this->appID,
				"code" => $this->code,
				"redirect_uri" => $this->getRedirectUri(),
				"grant_type" => "authorization_code",
			]
		);

		try
		{
			$result = \Bitrix\Main\Web\Json::decode($http->post(static::TOKEN_URL, $params));
			if (isset($result['APP_ID'], $result['API_KEY']))
			{
				$params['client_id'] = $result['APP_ID'];
				$this->appID = $result['APP_ID'];
				CSocServGoogleOAuth::SetOption("google_proxy_appid", trim($result['APP_ID']));
				CSocServGoogleOAuth::SetOption("google_proxy_api_key", trim($result['API_KEY']));

				$result = \Bitrix\Main\Web\Json::decode($http->post(static::TOKEN_URL, $params));
			}

			$this->arResult = $result;
		}
		catch(\Bitrix\Main\ArgumentException $e)
		{
			$this->arResult = [];
		}

		if(isset($this->arResult["access_token"]) && $this->arResult["access_token"] <> '')
		{
			if(isset($this->arResult["refresh_token"]) && $this->arResult["refresh_token"] <> '')
			{
				$this->refresh_token = $this->arResult["refresh_token"];
			}
			$this->access_token = $this->arResult["access_token"];
			$this->accessTokenExpires = $this->arResult["expires_in"] + time();

			$_SESSION["OAUTH_DATA"] = [
				"OATOKEN" => $this->access_token,
				"OATOKEN_EXPIRES" => $this->accessTokenExpires,
				"REFRESH_TOKEN" => $this->refresh_token,
			];

			return true;
		}

		return false;
	}

	public function getNewAccessToken($refreshToken = false, $userId = 0, $save = false)
	{
		if($this->appID === false)
		{
			return false;
		}

		if($refreshToken === false)
		{
			$refreshToken = $this->refresh_token;
		}


		$params = array_merge(
			$this->getLicenseParams(),
			[
				"client_id" => $this->appID,
				"refresh_token"=>$refreshToken,
				"grant_type"=>"refresh_token",
			]
		);

		$http = new HttpClient(
			array("socketTimeout" => $this->httpTimeout)
		);

		$result = $http->post(static::TOKEN_URL, $params);

		try
		{
			$this->arResult = \Bitrix\Main\Web\Json::decode($result);
		}
		catch(\Bitrix\Main\ArgumentException $e)
		{
			$this->arResult = [];
		}

		if (isset($this->arResult["access_token"]) && $this->arResult["access_token"] <> '')
		{
			$this->access_token = $this->arResult["access_token"];
			$this->accessTokenExpires = $this->arResult["expires_in"] + time();
			if ($save && intval($userId) > 0)
			{
				$dbSocservUser = \Bitrix\Socialservices\UserTable::getList([
					'filter' => [
						'=EXTERNAL_AUTH_ID' => static::SERVICE_ID,
						'=USER_ID' => $userId,
					],
					'select' => ["ID"]
				]);
				if($arOauth = $dbSocservUser->Fetch())
				{
					\Bitrix\Socialservices\UserTable::update($arOauth["ID"], [
							"OATOKEN" => $this->access_token,
							"OATOKEN_EXPIRES" => $this->accessTokenExpires
						]
					);
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * @return string
	 */
	public function getAppId(): string
	{
		if ($appId = trim(CSocServGoogleOAuth::GetOption("google_proxy_appid")))
		{
			return $appId;
		}

		$http = new HttpClient(["socketTimeout" => $this->httpTimeout]);

		$result = $http->post(static::TOKEN_URL, $this->getLicenseParams());

		try
		{
			$proxyData = \Bitrix\Main\Web\Json::decode($result);
			CSocServGoogleOAuth::SetOption("google_proxy_appid", trim($proxyData['APP_ID']));
			CSocServGoogleOAuth::SetOption("google_proxy_api_key", trim($proxyData['API_KEY']));

			return $proxyData['APP_ID'];
		}
		catch(\Bitrix\Main\ArgumentException $e)
		{
		}

		return '';
	}

	/**
	 * @return string
	 */
	public function getRedirectUri(): string
	{
		return static::TOKEN_URL;
	}

	/**
	 * @return array
	 */
	public function getLicenseParams(): array
	{
		$params["BX_TYPE"] = Client::getPortalType();
		$params["BX_LICENCE"] = Client::getLicenseCode();
		$params["SERVER_NAME"] = Client::getServerName();
		$params["license_key"] = Client::signRequest($params);

		return $params;
	}
}
