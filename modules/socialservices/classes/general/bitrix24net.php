<?

use Bitrix\Bitrix24\Integration\Network\Broadcast;
use Bitrix\Bitrix24\License;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Socialservices\UserTable;

Loc::loadMessages(__FILE__);

if(!defined('B24NETWORK_NODE'))
{
	$defaultValue = \Bitrix\Main\Config\Option::get('socialservices', 'network_url', '');

	if($defaultValue <> '')
	{
		define('B24NETWORK_NODE', $defaultValue);
	}
	elseif(defined('B24NETWORK_URL'))
	{
		define('B24NETWORK_NODE', B24NETWORK_URL);
	}
	else
	{
		define('B24NETWORK_NODE', 'https://www.bitrix24.net');
	}
}

class CSocServBitrix24NetLogger
{
	/**
	 * Log info.
	 *
	 * @param string $message
	 * @param array $additionalParams
	 *
	 * @return void
	 */
	public static function log(string $message, array $additionalParams = []): void
	{
		if (!empty($additionalParams))
		{
			$postfix = ' |';
			foreach ($additionalParams as $key => $value)
			{
				if (is_array($value))
				{
					if (empty($value))
					{
						$value = '';
					}
					else
					{
						$value = Json::encode($value);
					}
				}
				else
				{
					$value = (string)$value;
				}

				$postfix .= " {$key}[{$value}];";
			}

			$message .= $postfix;
		}

		AddMessage2Log("SocServBitrix24Net: {$message}", 'socialservices');
	}
}

class CSocServBitrix24Net extends CSocServAuth
{
	const ID = "Bitrix24Net";
	const NETWORK_URL = B24NETWORK_NODE;

	protected $entityOAuth = null;

	public function GetSettings()
	{
		return array(
			array("bitrix24net_domain", Loc::getMessage("socserv_b24net_domain"), "", array("statictext")),
			array("bitrix24net_id", Loc::getMessage("socserv_b24net_id"), "", array("text", 40)),
			array("bitrix24net_secret", Loc::getMessage("socserv_b24net_secret"), "", array("text", 40)),
			array("note"=>GetMessage("socserv_b24net_sett_note"))
		);
	}

	public function CheckSettings()
	{
		return self::GetOption('bitrix24net_id') !== '' && self::GetOption('bitrix24net_secret') !== '';
	}


	public function getFormHtml($arParams)
	{
		$url = $this->getUrl("popup");

		$phrase = ($arParams["FOR_INTRANET"]) ? Loc::getMessage("socserv_b24net_note_intranet") : Loc::getMessage("socserv_b24net_note");

		return $arParams["FOR_INTRANET"]
			? array("ON_CLICK" => 'onclick="BX.util.popup(\''.htmlspecialcharsbx(CUtil::JSEscape($url)).'\', 800, 600)"')
			: '<a href="javascript:void(0)" onclick="BX.util.popup(\''.htmlspecialcharsbx(CUtil::JSEscape($url)).'\', 800, 600)" class="bx-ss-button bitrix24net-button bitrix24net-button-'.LANGUAGE_ID.'"></a><span class="bx-spacer"></span><span>'.$phrase.'</span>';
	}

	public function GetOnClickJs()
	{
		$url = $this->getUrl("popup");
		return "BX.util.popup('".CUtil::JSEscape($url)."', 800, 600)";
	}

	public function getEntityOAuth($code = false)
	{
		if(!$this->entityOAuth)
		{
			$this->entityOAuth = new CBitrix24NetOAuthInterface();
		}

		if($code !== false)
		{
			$this->entityOAuth->setCode($code);
		}

		return $this->entityOAuth;
	}

	public function getUrl($mode = "page")
	{
		$redirect_uri = CSocServUtil::GetCurUrl('auth_service_id='.self::ID);

		$state =
			(defined("ADMIN_SECTION") && ADMIN_SECTION == true ? 'admin=1' : 'site_id='.SITE_ID)
			.'&backurl='.urlencode($GLOBALS["APPLICATION"]->GetCurPageParam(
				'check_key='.CSocServAuthManager::GetUniqueKey(),
				array_merge(array(
					"auth_service_error", "auth_service_id", "check_key", "error_message"
				), \Bitrix\Main\HttpRequest::getSystemParameters())
			))
			.'&mode='.$mode;

		return $this->getEntityOAuth()->GetAuthUrl($redirect_uri, $state, $mode);
	}

	public function getInviteUrl($userId, $checkword)
	{
		return $this->getEntityOAuth()->GetInviteUrl($userId, $checkword);
	}

	public function addScope($scope)
	{
		return $this->getEntityOAuth()->addScope($scope);
	}

	public function Authorize($skipCheck = false)
	{
		global $APPLICATION;
		$APPLICATION->RestartBuffer();

		$bProcessState = false;
		$authError = SOCSERV_AUTHORISATION_ERROR;
		$errorMessage = '';

		if (
			$skipCheck
			|| (
				(isset($_REQUEST["code"]) && $_REQUEST["code"] <> '')
				&& CSocServAuthManager::CheckUniqueKey()
			)
		)
		{
			$redirect_uri = \CHTTP::URN2URI('/bitrix/tools/oauth/bitrix24net.php');
			$bProcessState = true;
			$bAdmin = false;

			if (isset($_REQUEST["state"]))
			{
				parse_str($_REQUEST["state"], $arState);
				$bAdmin = isset($arState['admin']);
			}
			if ($bAdmin)
			{
				$this->checkRestrictions = false;
				$this->addScope("admin");
			}

			if (!$skipCheck)
			{
				$this->getEntityOAuth()->setCode($_REQUEST["code"]);
			}

			if (isset($_REQUEST['saml']) && is_string($_REQUEST['saml']))
			{
				$this->getEntityOAuth()->setSamlEncodedValue($_REQUEST['saml']);
			}

			if ($this->getEntityOAuth()->GetAccessToken($redirect_uri) !== false)
			{
				$arB24NetUser = $this->getEntityOAuth()->GetCurrentUser();
				if ($arB24NetUser)
				{
					$authError = true;

					$arFields = array(
						'EXTERNAL_AUTH_ID' => self::ID,
						'XML_ID' => $arB24NetUser["ID"],
						'LOGIN' => isset($arB24NetUser['LOGIN']) ? $arB24NetUser['LOGIN'] : "B24_".$arB24NetUser["ID"],
						'NAME' => $arB24NetUser["NAME"],
						'LAST_NAME' => $arB24NetUser["LAST_NAME"],
						'EMAIL' => $arB24NetUser["EMAIL"],
						'PERSONAL_WWW' => $arB24NetUser["PROFILE"],
						'OATOKEN' => $this->getEntityOAuth()->getToken(),
						'REFRESH_TOKEN' => $this->getEntityOAuth()->getRefreshToken(),
						'OATOKEN_EXPIRES' => $this->getEntityOAuth()->getAccessTokenExpires(),
					);

					foreach(GetModuleEvents("socialservices", "OnBeforeNetworkUserAuthorize", true) as $arEvent)
					{
						if (ExecuteModuleEventEx($arEvent, array(&$arFields, $arB24NetUser, $this)) === false)
						{
							$authError = SOCSERV_AUTHORISATION_ERROR;
							$errorMessage = $APPLICATION->GetException();

							break;
						}
					}

					if ($authError === true)
					{
						if (SITE_ID <> '')
						{
							$arFields["SITE_ID"] = SITE_ID;
						}

						$bSaveNetworkAuth = COption::GetOptionString("main", "allow_external_auth_stored_hash", "N") == "Y";
						$authError = $this->AuthorizeUser($arFields, $bSaveNetworkAuth);
					}
				}

				if ($authError !== true && !IsModuleInstalled('bitrix24'))
				{
					$this->getEntityOAuth()->RevokeAuth();
				}
				elseif ($bAdmin)
				{
					global $CACHE_MANAGER, $USER;
					$CACHE_MANAGER->Clean("sso_portal_list_".$USER->GetID());
				}
			}
			else
			{
				CSocServBitrix24NetLogger::log('Authorize - cannot load data', [
					'skipCheck' => $skipCheck,
					'has_saml' => isset($_REQUEST['saml']),
					'has_code' => isset($_REQUEST['code']),
				]);
			}
		}
		else
		{
			CSocServBitrix24NetLogger::log('Authorize - bad request', [
				'skipCheck' => $skipCheck,
				'has_code' => isset($_REQUEST['code']),
			]);
		}

		$bSuccess = $authError === true;

		if ($bSuccess)
		{
			CSocServAuthManager::SetAuthorizedServiceId(self::ID);
		}

		// hack to update option used for visualization in module options
		if ($bSuccess && !self::GetOption("bitrix24net_domain"))
		{
			$request = \Bitrix\Main\Context::getCurrent()->getRequest();
			self::SetOption("bitrix24net_domain", ($request->isHttps() ? "https://" : "http://").$request->getHttpHost());
		}

		$aRemove = array_merge(array("auth_service_error", "auth_service_id", "code", "error_reason", "error", "error_description", "check_key", "current_fieldset", "checkword"), \Bitrix\Main\HttpRequest::getSystemParameters());

		$url = ($APPLICATION->GetCurDir() == "/login/") ? "" : $APPLICATION->GetCurDir();

		$mode = 'page';

		if (!$bProcessState)
		{
			unset($_REQUEST["state"]);
		}

		if (isset($_REQUEST["state"]))
		{
			$arState = array();
			parse_str($_REQUEST["state"], $arState);

			if (isset($arState['backurl']) || isset($arState['redirect_url']))
			{
				$parseUrl = parse_url(isset($arState['redirect_url']) ? $arState['redirect_url'] : $arState['backurl']);

				$urlPath = $parseUrl["path"];
				$arUrlQuery = explode('&', $parseUrl["query"]);

				foreach($arUrlQuery as $key => $value)
				{
					foreach($aRemove as $param)
					{
						if (mb_strpos($value, $param."=") === 0)
						{
							unset($arUrlQuery[$key]);
							break;
						}
					}
				}

				$url = (!empty($arUrlQuery)) ? $urlPath.'?'.implode("&", $arUrlQuery) : $urlPath;
			}

			if (isset($arState['mode']))
			{
				$mode = $arState['mode'];
			}
		}

		if ($url == '' || preg_match("'^(http://|https://|ftp://|//)'i", $url))
		{
			$url = \CHTTP::URN2URI('/');
		}

		$url = CUtil::JSEscape($url);

		if ($bSuccess)
		{
			unset($_SESSION['B24_NETWORK_REDIRECT_TRY']);
		}
		else
		{
			if (IsModuleInstalled('bitrix24'))
			{
				if (isset($_SESSION['B24_NETWORK_REDIRECT_TRY']))
				{
					unset($_SESSION['B24_NETWORK_REDIRECT_TRY']);
					$url = self::getUrl();
					$url .= (mb_strpos($url, '?') >= 0 ? '&' : '?').'skip_redirect=1&error_message='.urlencode($errorMessage);
				}else
				{
					$_SESSION['B24_NETWORK_REDIRECT_TRY'] = true;
					$url = '/';
				}
			}
			else
			{
				if ($authError === SOCSERV_REGISTRATION_DENY)
				{
					$url = (preg_match("/\?/", $url)) ? $url.'&' : $url.'?';
					$url .= 'auth_service_id='.self::ID.'&auth_service_error='.$authError;
				}
				elseif ($bSuccess !== true)
				{
					$url = (isset($urlPath)) ? $urlPath.'?auth_service_id='.self::ID.'&auth_service_error='.$authError : $GLOBALS['APPLICATION']->GetCurPageParam(('auth_service_id='.self::ID.'&auth_service_error='.$authError), $aRemove);
				}
				if ($errorMessage <> '')
				{
					$url .= '&error_message='.urlencode($errorMessage);
				}
			}
		}

		if (CModule::IncludeModule("socialnetwork") && mb_strpos($url, "current_fieldset=") === false)
		{
			$url .= ((mb_strpos($url, "?") === false) ? '?' : '&')."current_fieldset=SOCSERV";
		}

		if ($url === $APPLICATION->GetCurPageParam())
		{
			$url = "/";
		}

		$location = ($mode == "popup")
			? 'if(window.opener) window.opener.location = \''.$url.'\'; window.close();'
			: 'window.location = \''.$url.'\';';
?>
<script type="text/javascript">
<?=$location?>
</script>
<?

		CMain::FinalActions();
	}

	public static function registerSite($domain)
	{
		if (defined("LICENSE_KEY") && LICENSE_KEY !== "DEMO")
		{
			$query = new HttpClient();
			$result = $query->get(static::NETWORK_URL.'/client.php?action=register&redirect_uri='.urlencode($domain.'/bitrix/tools/oauth/bitrix24net.php').'&key='.urlencode(LICENSE_KEY));

			$arResult = null;
			if ($result)
			{
				try
				{
					$arResult = Json::decode($result);
				}
				catch(\Bitrix\Main\ArgumentException $e)
				{

				}
			}

			if (is_array($arResult))
			{
				return $arResult;
			}
			else
			{
				return array("error" => "Unknown response", "error_details" => $result);
			}
		}
		else
		{
			return array("error" => "License check failed");
		}
	}
}

class CBitrix24NetOAuthInterface
{
	const NET_URL = B24NETWORK_NODE;

	const INVITE_URL = "/invite/";
	const PASSPORT_URL = "/id/";
	const AUTH_URL = "/oauth/authorize/";
	const TOKEN_URL = "/oauth/token/";

	protected $appID;
	protected $appSecret;
	protected $code = false;
	protected $access_token = false;
	protected $accessTokenExpires = 0;
	protected $lastAuth = null;
	protected $refresh_token = '';
	protected string $samlEncodedValue;
	protected $scope = array(
		'auth',
	);
	protected ?int $samlStatus;

	protected $arResult = array();
	protected $networkNode;

	public function __construct($appID = false, $appSecret = false, $code = false)
	{
		if($appID === false)
		{
			$appID = trim(CSocServBitrix24Net::GetOption("bitrix24net_id"));
		}

		if($appSecret === false)
		{
			$appSecret = trim(CSocServBitrix24Net::GetOption("bitrix24net_secret"));
		}

		list($prefix, $suffix) = explode(".", $appID, 2);

		if($prefix === 'site')
		{
			$this->addScope("client");
		}
		elseif($prefix == 'b24')
		{
			$this->addScope('profile');
		}

		$this->httpTimeout = SOCSERV_DEFAULT_HTTP_TIMEOUT;

		$this->appID = $appID;
		$this->appSecret = $appSecret;
		$this->code = $code;

		$this->networkNode = self::NET_URL;
	}

	public function getAppID()
	{
		return $this->appID;
	}

	public function getAppSecret()
	{
		return $this->appSecret;
	}

	public function getAccessTokenExpires()
	{
		return $this->accessTokenExpires;
	}

	public function setAccessTokenExpires($accessTokenExpires)
	{
		$this->accessTokenExpires = $accessTokenExpires;
	}

	public function getToken()
	{
		return $this->access_token;
	}

	public function setToken($access_token)
	{
		$this->access_token = $access_token;
	}

	public function getRefreshToken()
	{
		return $this->refresh_token;
	}

	public function setRefreshToken($refresh_token)
	{
		$this->refresh_token = $refresh_token;
	}

	public function setCode($code)
	{
		$this->code = $code;
	}

	public function setScope($scope)
	{
		$this->scope = $scope;
	}

	public function getScope()
	{
		return $this->scope;
	}

	public function addScope($scope)
	{
		if(is_array($scope))
			$this->scope = array_merge($this->scope, $scope);
		else
			$this->scope[] = $scope;
		return $this;
	}

	public function getScopeEncode()
	{
		return implode(',', array_map('urlencode', array_unique($this->getScope())));
	}

	public function getSamlEncodedValue(): string
	{
		return $this->samlEncodedValue;
	}

	public function setSamlEncodedValue(string $samlEncodedValue): void
	{
		$this->samlEncodedValue = $samlEncodedValue;
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

	public function GetAuthUrl($redirect_uri, $state = '', $mode = 'popup')
	{
		return $this->networkNode . self::AUTH_URL.
			"?user_lang=".LANGUAGE_ID.
			"&client_id=".urlencode($this->appID).
			"&redirect_uri=".urlencode($redirect_uri).
			"&scope=".$this->getScopeEncode().
			"&response_type=code".
			"&mode=".$mode.
			//($this->refresh_token <> '' ? '' : '&approval_prompt=force').
			($state <> '' ? '&state='.urlencode($state) : '');
	}

	public function getInviteUrl($userId, $checkword)
	{
		return $this->networkNode . self::INVITE_URL.
			"?user_lang=".LANGUAGE_ID.
			"&client_id=".urlencode($this->appID).
			"&profile_id=".$userId.
			"&checkword=".$checkword;
	}

	public function getLastAuth()
	{
		return $this->lastAuth;
	}

	public function GetAccessToken($redirect_uri = '')
	{
		if ($this->code === false)
		{
			$token = $this->getStorageTokens();

			// getStorageTokens returns null for unauthorized user
			if (is_array($token))
			{
				$this->access_token = $token["OATOKEN"];
				$this->accessTokenExpires = $token["OATOKEN_EXPIRES"];
			}

			if ($this->access_token && $this->checkAccessToken())
			{
				return true;
			}
			elseif (isset($token["REFRESH_TOKEN"]))
			{
				if ($this->getNewAccessToken($token["REFRESH_TOKEN"], $token["USER_ID"], true))
				{
					return true;
				}
			}

			return false;
		}

		$http = new HttpClient([
			'socketTimeout' => $this->httpTimeout,
			'streamTimeout' => $this->httpTimeout,
		]);

		$result = $http->get($this->networkNode . self::TOKEN_URL . '?' . http_build_query([
			'code' => $this->code,
			'client_id' => $this->appID,
			'client_secret' => $this->appSecret,
			'redirect_uri' => $redirect_uri,
			'scope' => implode(',',$this->getScope()),
			'grant_type' => 'authorization_code',
		]));

		try
		{
			if (empty($result))
			{
				throw new \Bitrix\Main\ArgumentException('Empty result');
			}

			$arResult = Json::decode($result);
		}
		catch(\Bitrix\Main\ArgumentException $e)
		{
			CSocServBitrix24NetLogger::log("GetAccessToken", [
				'status' => $http->getStatus(),
				'error' => $http->getError(),
				'response' => $result,
			]);

			$arResult = [];
		}

		if (isset($arResult["access_token"]) && $arResult["access_token"] <> '')
		{
			if (isset($arResult["refresh_token"]) && $arResult["refresh_token"] <> '')
			{
				$this->refresh_token = $arResult["refresh_token"];
			}

			$this->access_token = $arResult["access_token"];
			$this->accessTokenExpires = time() + $arResult["expires_in"];

			$this->lastAuth = $arResult;

			return true;
		}

		return false;
	}

	public function getNewAccessToken($refreshToken = false, $userId = 0, $save = false, $scope = array())
	{
		if ($this->appID == false || $this->appSecret == false)
		{
			return false;
		}

		if ($refreshToken == false)
		{
			$refreshToken = $this->refresh_token;
		}

		if ($scope != null)
		{
			$this->addScope($scope);
		}

		$http = new HttpClient(array(
			'socketTimeout' => $this->httpTimeout,
			'streamTimeout' => $this->httpTimeout,
		));

		$result = $http->get($this->networkNode . self::TOKEN_URL . '?' . http_build_query([
			'client_id' => $this->appID,
			'client_secret' => $this->appSecret,
			'refresh_token' => $refreshToken,
			'scope' => implode(',',$this->getScope()),
			'grant_type' => 'refresh_token',
		]));

		try
		{
			if (empty($result))
			{
				throw new \Bitrix\Main\ArgumentException('Empty result');
			}

			$arResult = Json::decode($result);
		}
		catch(\Bitrix\Main\ArgumentException $e)
		{
			CSocServBitrix24NetLogger::log("GetNewAccessToken", [
				'status' => $http->getStatus(),
				'error' => $http->getError(),
				'response' => $result,
			]);

			$arResult = [];
		}

		if (isset($arResult["access_token"]) && $arResult["access_token"] <> '')
		{
			$this->access_token = $arResult["access_token"];
			$this->accessTokenExpires = time() + $arResult["expires_in"];
			$this->refresh_token = $arResult["refresh_token"];

			if ($save && intval($userId) > 0)
			{
				$dbSocservUser = UserTable::getList([
					'filter' => [
						"=USER_ID" => intval($userId),
						"=EXTERNAL_AUTH_ID" => CSocServBitrix24Net::ID
					],
					'select' => ['ID']
				]);

				$arOauth = $dbSocservUser->fetch();
				if ($arOauth)
				{
					UserTable::update(
						$arOauth["ID"], array(
							"OATOKEN" => $this->access_token,
							"OATOKEN_EXPIRES" => $this->accessTokenExpires,
							"REFRESH_TOKEN" => $this->refresh_token,
						)
					);
				}
			}

			return true;
		}

		return false;
	}

	public function GetCurrentUser()
	{
		if ($this->access_token)
		{
			$ob = new CBitrix24NetTransport($this->access_token);
			$res = $ob->getProfile();

			if ($res && !isset($res['error']))
			{
				return $res['result'];
			}
		}

		return false;
	}

	public function RevokeAuth()
	{
		if ($this->access_token)
		{
			$ob = new CBitrix24NetTransport($this->access_token);
			$ob->call('profile.revoke');
		}
	}

	public function UpdateCurrentUser($arFields)
	{
		if ($this->access_token)
		{
			$ob = new CBitrix24NetTransport($this->access_token);
			$res = $ob->updateProfile($arFields);

			if (!isset($res['error']))
			{
				return $res['result'];
			}
		}

		return false;
	}

	private function getStorageTokens()
	{
		global $USER;

		$accessToken = '';
		if (is_object($USER) && $USER->IsAuthorized())
		{
			$dbSocservUser = UserTable::getList([
				'filter' => [
					'=USER_ID' => $USER->GetID(),
					'=EXTERNAL_AUTH_ID' => CSocServBitrix24Net::ID
				],
				'select' => ["USER_ID", "OATOKEN", "OATOKEN_EXPIRES", "REFRESH_TOKEN"]
			]);

			$accessToken = $dbSocservUser->fetch();
		}
		return $accessToken;
	}

	public function checkAccessToken()
	{
		return (($this->accessTokenExpires - 30) < time()) ? false : true;
	}

	public function getNetworkNode(): string
	{
		return $this->networkNode;
	}

	public function setNetworkNode(string $hostWithScheme): void
	{
		$this->networkNode = $hostWithScheme;
	}

	public function getSamlStatus(): ?int
	{
		return $this->samlStatus;
	}
}

/**
 * Sends a request on behalf of the user!
 */
class CBitrix24NetTransport
{
	const SERVICE_URL = "/rest/";

	const METHOD_METHODS = 'methods';
	const METHOD_BATCH = 'batch';
	const METHOD_PROFILE = 'profile';
	const METHOD_PROFILE_ADD = 'profile.add';
	const METHOD_PROFILE_ADD_CHECK = 'profile.add.check';
	const METHOD_PROFILE_UPDATE = 'profile.update';
	const METHOD_PROFILE_DELETE = 'profile.delete';
	const METHOD_PROFILE_CONTACTS = 'profile.contacts';
	const METHOD_PROFILE_RESTORE_PASSWORD = 'profile.password.restore';
	const METHOD_PROFILE_PUSH_QRCODE_AUTH_TOKEN = 'profile.pushqrcodeauthtoken';

	const RESTORE_PASSWORD_METHOD_EMAIL = 'EMAIL';
	const RESTORE_PASSWORD_METHOD_PHONE = 'PHONE';

	const REPONSE_KEY_BROADCAST = "broadcast";

	protected $access_token = '';
	protected $httpTimeout = SOCSERV_DEFAULT_HTTP_TIMEOUT;
	protected $networkNode;

	public static function init($networkNode = null)
	{
		$ob = new CBitrix24NetOAuthInterface();
		if($networkNode)
		{
			$ob->setNetworkNode($networkNode);
		}
		if($ob->GetAccessToken() !== false)
		{
			$token = $ob->getToken();
			$transport = new self($token);
			$transport->setNetworkNode($ob->getNetworkNode());

			return $transport;
		}

		return false;
	}

	public function __construct($access_token)
	{
		$this->access_token = $access_token;
		$this->networkNode = CBitrix24NetOAuthInterface::NET_URL;
	}

	public function getNetworkNode(): string
	{
		return $this->networkNode;
	}

	public function setNetworkNode(string $hostWithScheme): void
	{
		$this->networkNode = $hostWithScheme;
	}

	protected function prepareResponse($result)
	{
		if (empty($result))
		{
			throw new \Bitrix\Main\ArgumentException('Empty result');
		}

		$result = Json::decode($result);

		if(is_array($result) && isset($result["result"]) && is_array($result["result"]) && array_key_exists(static::REPONSE_KEY_BROADCAST, $result["result"]))
		{
			try
			{
				if (Loader::includeModule('bitrix24') && class_exists(Broadcast::class))
				{
					Broadcast::processBroadcastData($result["result"][static::REPONSE_KEY_BROADCAST]);
				}
			}
			catch(Exception $e)
			{
				CSocServBitrix24NetLogger::log('prepareResponse', [
					'error' => $e->getMessage(),
					'file' => "{$e->getFile()}:{$e->getLine()}",
				]);
			}
			unset($result["result"][static::REPONSE_KEY_BROADCAST]);
		}

		return $result;
	}

	protected function prepareRequest(array $request, $lang = null)
	{
		if (Loader::includeModule('bitrix24'))
		{
			$license = License::getCurrent();
			$request['license'] = $license->getCode();
			$request['license_partner'] = $license->getPartnerId();
			if (class_exists(Broadcast::class))
			{
				$request["broadcast_last_check"] = Broadcast::getLastBroadcastCheck();
			}
		}

		$request["user_lang"] = $lang ?? LANGUAGE_ID;
		$request["auth"] = $this->access_token;

		return $this->convertRequest($request);
	}

	protected function convertRequest(array $request)
	{
		global $APPLICATION;

		return $APPLICATION->ConvertCharsetArray($request, LANG_CHARSET, 'utf-8');
	}

	public function call($methodName, $additionalParams = null, $lang = null)
	{
		if(!is_array($additionalParams))
		{
			$additionalParams = [];
		}

		$request = $this->prepareRequest($additionalParams, $lang);

		$http = new HttpClient([
			'socketTimeout' => $this->httpTimeout,
			'streamTimeout' => $this->httpTimeout,
		]);
		if ($lang)
		{
			$http->setCookies(['USER_LANG' => $lang]);
		}
		$result = $http->post(
			$this->networkNode . self::SERVICE_URL . $methodName,
			$request
		);

		try
		{
			$res = $this->prepareResponse($result);
		}
		catch(\Bitrix\Main\ArgumentException $e)
		{
			$res = false;
		}

		if(!$res)
		{
			CSocServBitrix24NetLogger::log("CBitrix24NetTransport:call", [
				'method' => $methodName,
				'status' => $http->getStatus(),
				'error' => $http->getError(),
				'response' => $result,
			]);
		}

		return $res;
	}

	public function batch($actions)
	{
		$arBatch = array();

		if (is_array($actions))
		{
			foreach ($actions as $query_key => $arCmd)
			{
				list($cmd, $arParams) = array_values($arCmd);
				$arBatch['cmd'][$query_key] = $cmd.(is_array($arParams) ? '?'.http_build_query($arParams) : '');
			}
		}

		return $this->call(self::METHOD_BATCH, $arBatch);
	}

	public function getMethods()
	{
		return $this->call(self::METHOD_METHODS);
	}

	public function getProfile()
	{
		return $this->call(self::METHOD_PROFILE);
	}

	public function addProfile($arFields)
	{
		return $this->call(self::METHOD_PROFILE_ADD, $arFields);
	}

	public function checkProfile($arFields)
	{
		return $this->call(self::METHOD_PROFILE_ADD_CHECK, $arFields);
	}

	public function updateProfile($arFields)
	{
		return $this->call(self::METHOD_PROFILE_UPDATE, $arFields);
	}

	public function deleteProfile($ID)
	{
		return $this->call(self::METHOD_PROFILE_DELETE, array("ID" => $ID));
	}

	public function getProfileContacts($userId)
	{
		return $this->call(self::METHOD_PROFILE_CONTACTS, array("USER_ID" => $userId));
	}

	/**
	 * Restore user profile password
	 * @param int $userId User id whom password should be restored.
	 * @param string $restoreMethod Restore method (via email or via phone).
	 * @return mixed
	 */
	public function restoreProfilePassword($userId, $restoreMethod)
	{
		return $this->call(self::METHOD_PROFILE_RESTORE_PASSWORD, array("USER_ID" => $userId, 'RESTORE_METHOD' => $restoreMethod, 'LANGUAGE_ID' => LANGUAGE_ID));
	}

	/**
	 * Push qr code auth token
	 * @param array $params
	 * @return mixed
	 */
	public function pushQrCodeAuthToken(array $params)
	{
		return $this->call(self::METHOD_PROFILE_PUSH_QRCODE_AUTH_TOKEN, $params, LANGUAGE_ID);
	}
}

/**
 * Sends a request on behalf of the portal!
 *
 * Required client's `id` and `secret`, access token is not used.
 */
class CBitrix24NetPortalTransport extends CBitrix24NetTransport
{
	protected $clientId = null;
	protected $clientSecret = null;

	public static function init($networkNode = null)
	{
		$result = parent::init($networkNode);

		if (!$result)
		{
			$interface = new CBitrix24NetOAuthInterface();
			if ($networkNode)
			{
				$interface->setNetworkNode($networkNode);
			}
			if ($interface->getAppID())
			{
				$result = new self($interface->getAppID(), $interface->getAppSecret());
				$result->setNetworkNode($interface->getNetworkNode());
			}
		}

		return $result;
	}

	public function __construct($clientId, $clientSecret)
	{
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;

		return parent::__construct('');
	}

	protected function prepareRequest(array $request, $lang = null)
	{
		$request = parent::prepareRequest($request, $lang);

		$request["client_id"] = $this->clientId;
		$request["client_secret"] = $this->clientSecret;
		unset($request['auth']);

		return $request;
	}

}
