<?

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

IncludeModuleLangFile(__FILE__);

class CSocServMailRu2 extends CSocServAuth
{
	const ID = "MailRu2";
	const CONTROLLER_URL = "https://www.bitrix24.ru/controller";

	protected $entityOAuth;

	public function GetSettings()
	{
		return array(
			array("mailru2_client_id", GetMessage("socserv_mailru2_id"), "", Array("text", 40)),
			array("mailru2_client_secret", GetMessage("socserv_mailru2_key"), "", Array("text", 40)),
			array(
				'note' => getMessage(
					'socserv_mailru2_sett_note_2',
					array(
						'#URL#' => $this->getEntityOAuth()->getRedirectUri(),
						'#MAIL_URL#' => \CHttp::urn2uri('/bitrix/tools/mail_oauth.php'),
					)
				),
			),
		);
	}

	/**
	 * @param string|bool $code = false
	 * @return CMailRu2Interface
	 */
	public function getEntityOAuth($code = false)
	{
		if (!$this->entityOAuth)
		{
			$this->entityOAuth = new CMailRu2Interface();
		}

		if ($code !== false)
		{
			$this->entityOAuth->setCode($code);
		}

		return $this->entityOAuth;
	}

	public function GetFormHtml($arParams)
	{
		$url = $this->getUrl($arParams);

		$phrase = ($arParams["FOR_INTRANET"])
			? GetMessage("socserv_mailru2_note_intranet")
			: GetMessage("socserv_mailru2_note");

		return $arParams["FOR_INTRANET"]
			? array("ON_CLICK" => 'onclick="BX.util.popup(\'' . htmlspecialcharsbx(CUtil::JSEscape($url)) . '\', 460, 420)"')
			: '<a href="javascript:void(0)" onclick="BX.util.popup(\'' . htmlspecialcharsbx(CUtil::JSEscape($url)) . '\', 460, 420)" class="bx-ss-button mailru-button"></a><span class="bx-spacer"></span><span>' . $phrase . '</span>';
	}

	public function GetOnClickJs($arParams)
	{
		$url = $this->getUrl($arParams);
		return "BX.util.popup('" . CUtil::JSEscape($url) . "', 460, 420)";
	}

	public function getUrl($arParams)
	{
		global $APPLICATION;

		CSocServAuthManager::SetUniqueKey();
		if (IsModuleInstalled('bitrix24') && defined('BX24_HOST_NAME'))
		{
			$redirect_uri = static::CONTROLLER_URL . "/redirect.php";
			$state = $this->getEntityOAuth()->GetRedirectURI() . "?check_key=" . $_SESSION["UNIQUE_KEY"] . "&state=";
			$backurl = $APPLICATION->GetCurPageParam('', array("logout", "auth_service_error", "auth_service_id", "backurl"));
			$state .= urlencode("state=" . urlencode("backurl=" . urlencode($backurl) . (isset($arParams['BACKURL']) ? '&redirect_url=' . urlencode($arParams['BACKURL']) : '')));
		}
		else
		{
			$state = 'site_id=' . SITE_ID . '&backurl=' . urlencode($APPLICATION->GetCurPageParam('check_key=' . $_SESSION["UNIQUE_KEY"], array("logout", "auth_service_error", "auth_service_id", "backurl"))) . (isset($arParams['BACKURL']) ? '&redirect_url=' . urlencode($arParams['BACKURL']) : '');
			$redirect_uri = $this->getEntityOAuth()->GetRedirectURI();
		}

		return $this->getEntityOAuth()->GetAuthUrl($redirect_uri, $state);
	}

	public function addScope($scope)
	{
		return $this->getEntityOAuth()->addScope($scope);
	}

	public function prepareUser($arUser, $short = false)
	{
		$entityOAuth = $this->getEntityOAuth();
		$arFields = array(
			'EXTERNAL_AUTH_ID' => self::ID,
			'XML_ID' => $arUser["email"],
			'LOGIN' => $arUser["email"],
			'EMAIL' => $arUser["email"],
			'NAME' => $arUser["first_name"],
			'LAST_NAME' => $arUser["last_name"],
			'OATOKEN' => $entityOAuth->getToken(),
			'OATOKEN_EXPIRES' => $entityOAuth->getAccessTokenExpires(),
		);

		if (!$short && isset($arUser['image']))
		{
			$picture_url = $arUser['image'];
			$temp_path = CFile::GetTempName('', 'picture.jpg');

			$ob = new HttpClient(array(
				"redirect" => true
			));
			$ob->download($picture_url, $temp_path);

			$arPic = CFile::MakeFileArray($temp_path);
			if ($arPic)
			{
				$arFields["PERSONAL_PHOTO"] = $arPic;
			}
		}

		if (isset($arUser['birthday']))
		{
			if ($date = MakeTimeStamp($arUser['birthday'], "MM/DD/YYYY"))
			{
				$arFields["PERSONAL_BIRTHDAY"] = ConvertTimeStamp($date);
			}
		}

		if (isset($arUser['gender']) && $arUser['gender'] != '')
		{
			if ($arUser['gender'] == 'm')
			{
				$arFields["PERSONAL_GENDER"] = 'M';
			}
			elseif ($arUser['gender'] == 'f')
			{
				$arFields["PERSONAL_GENDER"] = 'F';
			}
		}

		if (strlen(SITE_ID) > 0)
		{
			$arFields["SITE_ID"] = SITE_ID;
		}

		return $arFields;
	}

	public function Authorize()
	{
		global $APPLICATION;
		$APPLICATION->RestartBuffer();

		$authError = SOCSERV_AUTHORISATION_ERROR;

		if (
			isset($_REQUEST["code"]) && $_REQUEST["code"] <> ''
			&& CSocServAuthManager::CheckUniqueKey()
		)
		{
			if (IsModuleInstalled('bitrix24') && defined('BX24_HOST_NAME'))
			{
				$redirect_uri = static::CONTROLLER_URL . "/redirect.php";
			}
			else
			{
				$redirect_uri = $this->getEntityOAuth()->GetRedirectURI();
			}

			$entityOAuth = $this->getEntityOAuth($_REQUEST['code']);
			if ($entityOAuth->GetAccessToken($redirect_uri) !== false)
			{
				$arUser = $entityOAuth->GetCurrentUser();
				if (is_array($arUser) && isset($arUser["email"]))
				{
					$arFields = $this->prepareUser($arUser);
					$authError = $this->AuthorizeUser($arFields);
				}
			}
		}

		$bSuccess = $authError === true;

		$url = ($APPLICATION->GetCurDir() == "/login/") ? "" : $APPLICATION->GetCurDir();
		$aRemove = array("logout", "auth_service_error", "auth_service_id", "code", "error_reason", "error", "error_description", "check_key", "current_fieldset");

		if (isset($_REQUEST["state"]))
		{
			$arState = array();
			parse_str($_REQUEST["state"], $arState);

			if (isset($arState['backurl']) || isset($arState['redirect_url']))
			{
				$url = !empty($arState['redirect_url']) ? $arState['redirect_url'] : $arState['backurl'];
				if (substr($url, 0, 1) !== "#")
				{
					$parseUrl = parse_url($url);

					$urlPath = $parseUrl["path"];
					$arUrlQuery = explode('&', $parseUrl["query"]);

					foreach ($arUrlQuery as $key => $value)
					{
						foreach ($aRemove as $param)
						{
							if (strpos($value, $param . "=") === 0)
							{
								unset($arUrlQuery[$key]);
								break;
							}
						}
					}

					$url = (!empty($arUrlQuery)) ? $urlPath . '?' . implode("&", $arUrlQuery) : $urlPath;
				}
			}
		}

		if ($authError === SOCSERV_REGISTRATION_DENY)
		{
			$url = (preg_match("/\?/", $url)) ? $url . '&' : $url . '?';
			$url .= 'auth_service_id=' . self::ID . '&auth_service_error=' . $authError;
		}
		elseif ($bSuccess !== true)
		{
			$url = (isset($urlPath)) ? $urlPath . '?auth_service_id=' . self::ID . '&auth_service_error=' . $authError : $GLOBALS['APPLICATION']->GetCurPageParam(('auth_service_id=' . self::ID . '&auth_service_error=' . $authError), $aRemove);
		}

		if (CModule::IncludeModule("socialnetwork") && strpos($url, "current_fieldset=") === false)
		{
			$url .= ((strpos($url, "?") === false) ? '?' : '&') . "current_fieldset=SOCSERV";
		}
		?>
		<script type="text/javascript">
			if (window.opener)
				window.opener.location = '<?=CUtil::JSEscape($url)?>';
			window.close();
		</script>
		<?
		die();
	}

	public function setUser($userId)
	{
		$this->getEntityOAuth()->setUser($userId);
	}
}


class CMailRu2Interface extends CSocServOAuthTransport
{
	const SERVICE_ID = "MailRu2";

	const AUTH_URL = "https://oauth.mail.ru/login";
	const TOKEN_URL = "https://oauth.mail.ru/token";
	const USER_INFO_URL = "https://oauth.mail.ru/userinfo";

	protected $userId = false;
	protected $responseData = array();

	protected $scope = array(
		"userinfo",
	);

	public function __construct($appID = false, $appSecret = false, $code = false)
	{
		if ($appID === false)
		{
			$appID = trim(CSocServAuth::GetOption("mailru2_client_id"));
		}

		if ($appSecret === false)
		{
			$appSecret = trim(CSocServAuth::GetOption("mailru2_client_secret"));
		}

		parent::__construct($appID, $appSecret, $code);
	}

	public function GetRedirectURI()
	{
		return \CHTTP::URN2URI("/bitrix/tools/oauth/mailru2.php");
	}

	public function GetAuthUrl($redirect_uri, $state = '')
	{
		return self::AUTH_URL .
			"?client_id=" . $this->appID .
			"&redirect_uri=" . urlencode($redirect_uri) .
			"&scope=" . $this->getScopeEncode() .
			"&response_type=" . "code" .
			($state <> '' ? '&state=' . urlencode($state) : '');
	}

	public function getResult()
	{
		return $this->responseData;
	}

	public function GetAccessToken($redirect_uri)
	{
		$token = $this->getStorageTokens();
		if (is_array($token))
		{
			$this->access_token = $token["OATOKEN"];
			$this->accessTokenExpires = $token["OATOKEN_EXPIRES"];

			if (!$this->code)
			{
				if ($this->checkAccessToken())
				{
					return true;
				}
				else if (isset($token['REFRESH_TOKEN']))
				{
					if ($this->getNewAccessToken($token['REFRESH_TOKEN'], $this->userId, true))
					{
						return true;
					}
				}
			}

			$this->deleteStorageTokens();
		}

		if ($this->code === false)
		{
			return false;
		}

		$query = array(
			"code" => $this->code,
			"grant_type" => "authorization_code",
			"redirect_uri" => $redirect_uri,
		);

		$h = new \Bitrix\Main\Web\HttpClient(array(
			"socketTimeout" => $this->httpTimeout,
			"streamTimeout" => $this->httpTimeout,
		));
		$h->setAuthorization($this->appID, $this->appSecret);
		$h->setHeader('User-Agent', 'Bitrix'); // Mail.ru requires User-Agent to be set

		$result = $h->post(self::TOKEN_URL, $query);

		try
		{
			$arResult = \Bitrix\Main\Web\Json::decode($result);
		}
		catch (\Bitrix\Main\ArgumentException $e)
		{
			$arResult = array();
		}

		if ((isset($arResult["access_token"]) && $arResult["access_token"] <> ''))
		{
			$this->access_token = $arResult["access_token"];
			$this->accessTokenExpires = time() + $arResult["expires_in"];
			$this->refresh_token = $arResult['refresh_token'];

			$_SESSION["OAUTH_DATA"] = array(
				"OATOKEN" => $this->access_token,
				"OATOKEN_EXPIRES" => $this->accessTokenExpires,
				"REFRESH_TOKEN" => $this->refresh_token
			);
			return true;
		}

		return false;
	}

	public function getNewAccessToken($refreshToken = false, $userId = 0, $save = false)
	{
		if ($this->appID == false || $this->appSecret == false)
		{
			return false;
		}

		if ($refreshToken == false)
		{
			$refreshToken = $this->refresh_token;
		}

		$http = new HttpClient(array(
			'socketTimeout' => $this->httpTimeout,
			'streamTimeout' => $this->httpTimeout,
		));
		$http->setHeader('User-Agent', 'Bitrix');

		$result = $http->post(static::TOKEN_URL, array(
			'refresh_token' => $refreshToken,
			'client_id' => $this->appID,
			'client_secret' => $this->appSecret,
			'grant_type' => 'refresh_token',
		));

		try
		{
			$arResult = Json::decode($result);
		}
		catch (\Bitrix\Main\ArgumentException $e)
		{
			$arResult = array();
		}

		if (!empty($arResult['access_token']))
		{
			$this->access_token = $arResult['access_token'];
			$this->accessTokenExpires = $arResult['expires_in'] + time();
			if ($save && intval($userId) > 0)
			{
				$dbSocservUser = \Bitrix\Socialservices\UserTable::getList(array(
					'filter' => array(
						'=EXTERNAL_AUTH_ID' => static::SERVICE_ID,
						'=USER_ID' => $userId,
					),
					'select' => array('ID')
				));
				if ($arOauth = $dbSocservUser->fetch())
				{
					\Bitrix\Socialservices\UserTable::update($arOauth['ID'], array(
						'OATOKEN' => $this->access_token,
						'OATOKEN_EXPIRES' => $this->accessTokenExpires)
					);
				}
			}

			return true;
		}

		return false;
	}

	public function GetCurrentUser()
	{
		if ($this->access_token === false)
		{
			return false;
		}

		$http = new HttpClient();
		$http->setTimeout($this->httpTimeout);

		$result = $http->get(self::USER_INFO_URL . '?access_token=' . $this->access_token);

		try
		{
			return Json::decode($result);
		}
		catch (\Bitrix\Main\ArgumentException $e)
		{
			return false;
		}
	}

	public function GetAppInfo()
	{
		return false;
	}

	public function getScopeEncode()
	{
		return implode(' ', array_map('urlencode', array_unique($this->getScope())));
	}

}
