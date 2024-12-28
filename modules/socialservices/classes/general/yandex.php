<?
IncludeModuleLangFile(__FILE__);

class CSocServYandexAuth extends CSocServAuth
{
	const ID = "YandexOAuth";
	const CONTROLLER_URL = "https://www.bitrix24.ru/controller";
	const LOGIN_PREFIX = "YA_";

	/** @var CYandexOAuthInterface null  */
	protected $entityOAuth = null;

	/**
	 * @param string $code=false
	 * @return CYandexOAuthInterface
	 */
	public function getEntityOAuth($code = false)
	{
		if(!$this->entityOAuth)
		{
			$this->entityOAuth = new CYandexOAuthInterface();
		}

		if($code !== false)
		{
			$this->entityOAuth->setCode($code);
		}

		return $this->entityOAuth;
	}

	public function GetSettings()
	{
		return array(
			array("yandex_appid", GetMessage("socserv_yandex_client_id"), "", array("text", 40)),
			array("yandex_appsecret", GetMessage("socserv_yandex_client_secret"), "", array("text", 40)),
			array(
				'note' => getMessage(
					'socserv_yandex_note_2',
					array(
						'#URL#' => \CYandexOAuthInterface::getRedirectUri(),
						'#MAIL_URL#' => \CHttp::urn2uri('/bitrix/tools/mail_oauth.php'),
					)
				),
			),
		);
	}

	public function getUrl($location = 'opener', $addScope = null, $arParams = array())
	{
		global $APPLICATION;

		$this->entityOAuth = $this->getEntityOAuth();
		if(IsModuleInstalled('bitrix24') && defined('BX24_HOST_NAME'))
		{
			$redirect_uri = static::CONTROLLER_URL."/redirect.php";
			$state = CYandexOAuthInterface::GetRedirectURI()."?check_key=".\CSocServAuthManager::getUniqueKey()."&state=";
			$backurl = $APPLICATION->GetCurPageParam('', array("logout", "auth_service_error", "auth_service_id", "backurl"));
			$state .= urlencode("state=".urlencode("backurl=".urlencode($backurl).'&mode='.$location.(isset($arParams['BACKURL']) ? '&redirect_url='.urlencode($arParams['BACKURL']) : '')));
		}
		else
		{
			$state = 'site_id='.SITE_ID.'&backurl='.urlencode($APPLICATION->GetCurPageParam('check_key='.\CSocServAuthManager::getUniqueKey(), array("logout", "auth_service_error", "auth_service_id", "backurl"))).'&mode='.$location.(isset($arParams['BACKURL']) ? '&redirect_url='.urlencode($arParams['BACKURL']) : '');
			$redirect_uri = CYandexOAuthInterface::GetRedirectURI();
		}

		return $this->entityOAuth->GetAuthUrl($redirect_uri, $state);
	}

	public function GetFormHtml($arParams)
	{
		$url = static::getUrl('opener', null, $arParams);

		$phrase = ($arParams["FOR_INTRANET"]) ? GetMessage("socserv_yandex_form_note_intranet") : GetMessage("socserv_yandex_form_note");

		if($arParams["FOR_INTRANET"])
		{
			return array("ON_CLICK" => 'onclick="BX.util.popup(\''.htmlspecialcharsbx(CUtil::JSEscape($url)).'\', 680, 600)"');
		}
		else
		{
			return '<a href="javascript:void(0)" onclick="BX.util.popup(\''.htmlspecialcharsbx(CUtil::JSEscape($url)).'\', 680, 600)" class="bx-ss-button yandex-button"></a><span class="bx-spacer"></span><span>'.$phrase.'</span>';
		}
	}

	public function GetOnClickJs($arParams)
	{
		$url = static::getUrl('opener', null, $arParams);
		return "BX.util.popup('".CUtil::JSEscape($url)."', 680, 600)";
	}

	public function getStorageToken()
	{
		$accessToken = null;
		$userId = intval($this->userId);
		if($userId > 0)
		{
			$dbSocservUser = \Bitrix\Socialservices\UserTable::getList([
				'filter' => ['=USER_ID' => $userId, "=EXTERNAL_AUTH_ID" => static::ID],
				'select' => ["OATOKEN", "REFRESH_TOKEN", "OATOKEN_EXPIRES"]
			]);
			if($arOauth = $dbSocservUser->fetch())
			{
				$accessToken = $arOauth["OATOKEN"];
			}
		}

		return $accessToken;
	}

	public function prepareUser($yandexUser, $short = false)
	{
		$id = $yandexUser['id'];

		$userFields = array(
			'EXTERNAL_AUTH_ID' => static::ID,
			'XML_ID' => $id,
			'LOGIN' => static::LOGIN_PREFIX.$id,
			'NAME'=> $yandexUser['first_name'],
			'LAST_NAME'=> $yandexUser['last_name'],
			'OATOKEN' => $this->entityOAuth->getToken(),
			'OATOKEN_EXPIRES' => $this->entityOAuth->getAccessTokenExpires(),
		);

		if($userFields["NAME"] == '')
		{
			$userFields["NAME"] = $yandexUser["login"];
		}

		if(isset($yandexUser["emails"]) && is_array($yandexUser["emails"]) && count($yandexUser["emails"]) > 0)
		{
			$userFields["EMAIL"] = $yandexUser['emails'][0];
		}

		if(!$short && !empty($yandexUser['default_avatar_id']))
		{
			$picture_url = "https://avatars.yandex.net/get-yapic/".$yandexUser["default_avatar_id"]."/islands-200";

			$temp_path = CFile::GetTempName('', 'picture.jpg');

			$ob = new \Bitrix\Main\Web\HttpClient(array(
				"redirect" => true
			));
			$ob->download($picture_url, $temp_path);

			$arPic = CFile::MakeFileArray($temp_path);
			if($arPic)
			{
				$userFields["PERSONAL_PHOTO"] = $arPic;
			}
		}

		if(SITE_ID <> '')
		{
			$userFields["SITE_ID"] = SITE_ID;
		}

		return $userFields;
	}

	public function Authorize()
	{
		global $APPLICATION;
		$APPLICATION->RestartBuffer();

		$bSuccess = false;
		$bProcessState = false;
		$authError = SOCSERV_AUTHORISATION_ERROR;

		if(
			isset($_REQUEST["code"]) && $_REQUEST["code"] <> '' && CSocServAuthManager::CheckUniqueKey()
		)
		{
			$bProcessState = true;
			$this->entityOAuth = $this->getEntityOAuth($_REQUEST['code']);

			if(IsModuleInstalled('bitrix24') && defined('BX24_HOST_NAME'))
			{
				$redirect_uri = static::CONTROLLER_URL."/redirect.php";
			}
			else
			{
				$redirect_uri = $this->getEntityOAuth()->GetRedirectURI();
			}

			if($this->entityOAuth->GetAccessToken($redirect_uri) !== false)
			{
				$boxUser = $this->entityOAuth->GetCurrentUser();

				if(is_array($boxUser))
				{
					$arFields = self::prepareUser($boxUser);
					$authError = $this->AuthorizeUser($arFields);
					$bSuccess = $authError === true;
				}
			}
		}

		$url = ($APPLICATION->GetCurDir() == "/login/") ? "" : $APPLICATION->GetCurDir();
		$aRemove = array("logout", "auth_service_error", "auth_service_id", "code", "error_reason", "error", "error_description", "check_key", "current_fieldset");

		if(!$bProcessState)
		{
			unset($_REQUEST["state"]);
		}

		$mode = 'opener';
		$addParams = true;
		if(isset($_REQUEST["state"]))
		{
			$arState = array();
			parse_str($_REQUEST["state"], $arState);

			if(isset($arState['backurl']) || isset($arState['redirect_url']))
			{
				$url = !empty($arState['redirect_url']) ? $arState['redirect_url'] : $arState['backurl'];
				if(mb_substr($url, 0, 1) !== "#")
				{
					$parseUrl = parse_url($url);

					$urlPath = $parseUrl["path"];
					$arUrlQuery = explode('&', $parseUrl["query"]);

					foreach($arUrlQuery as $key => $value)
					{
						foreach($aRemove as $param)
						{
							if(mb_strpos($value, $param."=") === 0)
							{
								unset($arUrlQuery[$key]);
								break;
							}
						}
					}

					$url = (!empty($arUrlQuery)) ? $urlPath.'?'.implode("&", $arUrlQuery) : $urlPath;
				}
				else
				{
					$addParams = false;
				}
			}

			if(isset($arState['mode']))
			{
				$mode = $arState['mode'];
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

		if($addParams && CModule::IncludeModule("socialnetwork") && mb_strpos($url, "current_fieldset=") === false)
		{
			$url = (preg_match("/\?/", $url)) ? $url."&current_fieldset=SOCSERV" : $url."?current_fieldset=SOCSERV";
		}

		$url = CUtil::JSEscape($url);

		if($addParams)
		{
			$location = ($mode == "opener") ? 'if(window.opener) window.opener.location = \''.$url.'\'; window.close();' : ' window.location = \''.$url.'\';';
		}
		else
		{
			//fix for chrome
			$location = ($mode == "opener") ? 'if(window.opener) window.opener.location = window.opener.location.href + \''.$url.'\'; window.close();' : ' window.location = window.location.href + \''.$url.'\';';
		}

		$JSScript = '
		<script>
		'.$location.'
		</script>
		';

		echo $JSScript;

		CMain::FinalActions();
	}
}

class CYandexOAuthInterface extends CSocServOAuthTransport
{
	const SERVICE_ID = "YandexOAuth";

	const AUTH_URL = "https://oauth.yandex.ru/authorize";
	const TOKEN_URL = "https://oauth.yandex.ru/token";

	const USERINFO_URL = "https://login.yandex.ru/info";

	const MAX_DEVICE_ID_LENGTH = 50;

	protected $arResult = array();

	public function __construct($appID = false, $appSecret = false, $code = false)
	{
		if($appID === false)
		{
			$appID = trim(CSocServYandexAuth::GetOption("yandex_appid"));
		}

		if($appSecret === false)
		{
			$appSecret = trim(CSocServYandexAuth::GetOption("yandex_appsecret"));
		}

		parent::__construct($appID, $appSecret, $code);
	}

	public static function GetRedirectURI()
	{
		return \CHTTP::URN2URI("/bitrix/tools/oauth/yandex.php");
	}

	public function getResult()
	{
		return $this->arResult;
	}

	public function getError()
	{
		return is_array($this->arResult) && isset($this->arResult['error'])
			? $this->arResult['error']
			: '';
	}

	/**
	 * @param string $redirect_uri
	 * @param string $state
	 * @return string
	 */
	public function GetAuthUrl($redirect_uri = '', $state = '')
	{
		$deviceId = $this->getDeviceId($state);

		return self::AUTH_URL
			."?response_type=code"
			."&client_id=".urlencode($this->appID)
			.(!empty($deviceId) ? "&device_id=".$deviceId : '')
			."&display=popup"
			."&redirect_uri=".urlencode($redirect_uri)
			.'&force_confirm=yes'
			.(!empty($state) ? "&state=".urlencode($state) : '');
	}

	/**
	 * @param string $state
	 * @return string
	 */
	public function getDeviceId($state)
	{
		$deviceId = '';
		if (!empty($state) && isset($_SESSION[$state]))
		{
			list(, $deviceId) = $_SESSION[$state];
			if ($deviceId)
			{
				$deviceId = mb_substr($deviceId, 0, self::MAX_DEVICE_ID_LENGTH);
			}
		}

		return $deviceId;
	}

	public function GetAccessToken()
	{
		if(($tokens = $this->getStorageTokens()) && is_array($tokens))
		{
			$this->access_token = $tokens["OATOKEN"];

			if(!$this->code)
			{
				if($this->checkAccessToken())
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

		$h = new \Bitrix\Main\Web\HttpClient(array("socketTimeout" => $this->httpTimeout));
		$h->setAuthorization($this->appID, $this->appSecret);

		$result = $h->post(self::TOKEN_URL, array(
			"grant_type"=>"authorization_code",
			"code"=>$this->code,
			"client_id" => $this->appID,
		));

		$this->arResult = \Bitrix\Main\Web\Json::decode($result);

		if(isset($this->arResult["access_token"]) && $this->arResult["access_token"] <> '')
		{
			// yandex doesn't send refresh tokens but I leave it here in case they will
			if(isset($this->arResult["refresh_token"]) && $this->arResult["refresh_token"] <> '')
			{
				$this->refresh_token = $this->arResult["refresh_token"];
			}
			$this->access_token = $this->arResult["access_token"];
			$this->accessTokenExpires = $this->arResult["expires_in"] + time();

			$_SESSION["OAUTH_DATA"] = array(
				"OATOKEN" => $this->access_token,
			);

			return true;
		}
		return false;
	}

	public function GetCurrentUser()
	{
		if($this->access_token === false)
			return false;

		$h = new \Bitrix\Main\Web\HttpClient();
		$result = $h->get(self::USERINFO_URL.'?format=json&oauth_token='.urlencode($this->access_token));

		try
		{
			$result = \Bitrix\Main\Web\Json::decode($result);
		}
		catch(\Bitrix\Main\SystemException $e)
		{
			$result = false;
		}

		if(is_array($result))
		{
			$result["access_token"] = $this->access_token;
			$result["refresh_token"] = $this->refresh_token;
			$result["expires_in"] = $this->accessTokenExpires;
		}
		return $result;
	}

	public function GetAppInfo()
	{
		if ($this->access_token === false)
			return false;

		$h = new \Bitrix\Main\Web\HttpClient();
		$h->setTimeout($this->httpTimeout);

		$result = $h->get(self::USERINFO_URL.'?format=json&oauth_token='.urlencode($this->access_token));

		try
		{
			$result = \Bitrix\Main\Web\Json::decode($result);
			$result = array_key_exists("client_id", $result)
							? array("id" => $result["client_id"])
							: array();
		} catch (\Bitrix\Main\ArgumentException $e)
		{
			$result = array();
		}

		return $result;
	}
}
