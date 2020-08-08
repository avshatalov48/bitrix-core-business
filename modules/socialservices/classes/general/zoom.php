<?php

use Bitrix\Main\ArgumentException;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use \Bitrix\Main\Web\HttpClient;
use \Bitrix\Main\Web\Json;
use \Bitrix\Main\Web\JWT;
use Bitrix\Socialservices\UserTable;

IncludeModuleLangFile(__FILE__);

class CSocServZoom extends CSocServAuth
{
	public const ID = 'zoom';
	private const CONTROLLER_URL = 'https://www.bitrix24.ru/controller';
	private const LOGIN_PREFIX = 'zoom_';

	protected $entityOAuth;

	public function GetSettings()
	{
		return [
			['zoom_client_id', Loc::getMessage('SOCSERV_ZOOM_CLIENT_ID'), '', ['text', 40]],
			['zoom_client_secret', Loc::getMessage('SOCSERV_ZOOM_CLIENT_SECRET'), '', ['text', 40]],
			[
				'note' => Loc::getMessage(
						'SOCSERV_ZOOM_SETT_NOTE_2',
						['#URL#'=>\CHTTP::URN2URI('/bitrix/tools/oauth/zoom.php')]
				)
			],
		];
	}

	/**
	 * @param string|bool $code = false
	 * @return CZoomInterface
	 */
	public function getEntityOAuth($code = false): CZoomInterface
	{
		if (!$this->entityOAuth)
		{
			$this->entityOAuth = new CZoomInterface();
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

		$phrase = ($arParams['FOR_INTRANET'])
			? GetMessage('SOCSERV_ZOOM_NOTE_INTRANET')
			: GetMessage('SOCSERV_ZOOM_NOTE');

		return $arParams['FOR_INTRANET']
			? array('ON_CLICK' => 'onclick="BX.util.popup(\'' . htmlspecialcharsbx(CUtil::JSEscape($url)) . '\', 700, 700)"')
			: '<a href="javascript:void(0)" onclick="BX.util.popup(\'' . htmlspecialcharsbx(CUtil::JSEscape($url)) . '\', 700, 700)" class="bx-ss-button zoom-button"></a><span class="bx-spacer"></span><span>' . $phrase . '</span>';
	}

	public function GetOnClickJs($arParams): string
	{
		$url = $this->getUrl($arParams);
		return "BX.util.popup('" . CUtil::JSEscape($url) . "', 460, 420)";
	}

	public function getUrl($arParams): string
	{
		global $APPLICATION;

		CSocServAuthManager::SetUniqueKey();
		if (defined('BX24_HOST_NAME') && IsModuleInstalled('bitrix24'))
		{
			$redirect_uri = static::CONTROLLER_URL . '/redirect.php';
			$state = $this->getEntityOAuth()->GetRedirectURI() . '?check_key=' . $_SESSION['UNIQUE_KEY'] . '&state=';
			$backurl = $APPLICATION->GetCurPageParam('', ['logout', 'auth_service_error', 'auth_service_id', 'backurl']);
			$state .= urlencode('state=' . urlencode('backurl=' . urlencode($backurl) . (isset($arParams['BACKURL']) ? '&redirect_url=' . urlencode($arParams['BACKURL']) : '')));
		}
		else
		{
			$state = 'site_id=' . SITE_ID . '&backurl=' .
				urlencode($APPLICATION->GetCurPageParam('check_key=' . $_SESSION['UNIQUE_KEY'], ['logout', 'auth_service_error', 'auth_service_id', 'backurl'])) .
				(isset($arParams['BACKURL']) ? '&redirect_url=' . urlencode($arParams['BACKURL']) : '');

			$redirect_uri = $this->getEntityOAuth()->GetRedirectURI();
		}

		return $this->getEntityOAuth()->GetAuthUrl($redirect_uri, $state);
	}

	public function addScope($scope): CZoomInterface
	{
		return $this->getEntityOAuth()->addScope($scope);
	}

	public function prepareUser($arUser, $short = false): array
	{
		$entityOAuth = $this->getEntityOAuth();
		$arFields = array(
			'EXTERNAL_AUTH_ID' => self::ID,
			'XML_ID' => $arUser["email"],
			'LOGIN' => self::LOGIN_PREFIX.$arUser["email"],
			'EMAIL' => $arUser["email"],
			'NAME' => $arUser["first_name"],
			'LAST_NAME' => $arUser["last_name"],
			'OATOKEN' => $entityOAuth->getToken(),
			'OATOKEN_EXPIRES' => $entityOAuth->getAccessTokenExpires(),
		);

		if (!$short && isset($arUser['pic_url']))
		{
			$picture_url = $arUser['pic_url'];
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

		if (strlen(SITE_ID) > 0)
		{
			$arFields["SITE_ID"] = SITE_ID;
		}

		return $arFields;
	}

	public static function CheckUniqueKey($bUnset = true): bool
	{
		$arState = array();

		if (isset($_REQUEST['state']))
		{
			parse_str(urldecode(JWT::urlsafeB64Decode($_REQUEST['state'])), $arState);

			if (isset($arState['backurl']))
			{
				InitURLParam($arState['backurl']);
			}
		}

		if (!isset($_REQUEST['check_key']) && isset($_REQUEST['backurl']))
		{
			InitURLParam($_REQUEST['backurl']);
		}

		$checkKey = '';
		if (isset($_REQUEST['check_key']))
		{
			$checkKey = $_REQUEST['check_key'];
		}
		elseif (isset($arState['check_key']))
		{
			$checkKey = $arState['check_key'];
		}

		if ($_SESSION['UNIQUE_KEY'] !== '' && $checkKey !== '' && ($checkKey === $_SESSION['UNIQUE_KEY']))
		{
			if ($bUnset)
			{
				unset($_SESSION['UNIQUE_KEY']);
			}

			return true;
		}
		return false;
	}

	public function Authorize(): void
	{
		global $APPLICATION;
		$APPLICATION->RestartBuffer();

		$authError = SOCSERV_AUTHORISATION_ERROR;

		if (
			isset($_REQUEST['code']) && $_REQUEST['code'] <> ''
			&& self::CheckUniqueKey()
		)
		{
			if (defined('BX24_HOST_NAME') && IsModuleInstalled('bitrix24'))
			{
				$redirect_uri = static::CONTROLLER_URL . '/redirect.php';
			}
			else
			{
				$redirect_uri = $this->getEntityOAuth()->GetRedirectURI();
			}

			$entityOAuth = $this->getEntityOAuth($_REQUEST['code']);
			if ($entityOAuth->GetAccessToken($redirect_uri) !== false)
			{
				$arUser = $entityOAuth->getCurrentUser();
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

			$decodedState = urldecode(JWT::urlsafeB64Decode($_REQUEST["state"]));
			parse_str($decodedState, $arState);

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
				window.opener.location.reload();
			window.close();
		</script>
		<?php
		die();
	}

	public function setUser($userId)
	{
		$this->getEntityOAuth()->setUser($userId);
	}

	public function getStorageToken()
	{
		$accessToken = null;
		$userId = (int)$this->userId;
		if ($userId > 0)
		{
			$dbSocservUser = \Bitrix\Socialservices\UserTable::getList([
				'filter' => ['=USER_ID' => $userId, '=EXTERNAL_AUTH_ID' => static::ID],
				'select' => ['OATOKEN', 'REFRESH_TOKEN', 'OATOKEN_EXPIRES']
			]);
			if ($arOauth = $dbSocservUser->fetch())
			{
				$accessToken = $arOauth['OATOKEN'];

				if (empty($accessToken) || (((int)$arOauth['OATOKEN_EXPIRES'] > 0) && ((int)($arOauth['OATOKEN_EXPIRES'] < (int)time()))))
				{
					if (isset($arOauth['REFRESH_TOKEN']))
					{
						$this->getEntityOAuth()->getNewAccessToken($arOauth['REFRESH_TOKEN'], $userId, true);
					}

					if (($accessToken = $this->getEntityOAuth()->getToken()) === false)
					{
						return null;
					}
				}
			}
		}

		return $accessToken;
	}

	public function createConference($params)
	{
		$conference = null;
		if ($this->getEntityOAuth()->GetAccessToken())
		{
			$conference = $this->getEntityOAuth()->requestConference($params);
		}

		return $conference;
	}

	public function getConferenceById(int $confId): ?array
	{
		$conference = null;
		if ($this->getEntityOAuth()->GetAccessToken())
		{
			$conference = $this->getEntityOAuth()->getConferenceById($confId);
		}

		return $conference;
	}
}

class CZoomInterface extends CSocServOAuthTransport
{
	const SERVICE_ID = "zoom";

	const AUTH_URL = 'https://zoom.us/oauth/authorize';
	const TOKEN_URL = 'https://zoom.us/oauth/token';

	private const API_ENDPOINT = 'https://api.zoom.us/v2/';

	private const USER_INFO_URL = 'users/me';
	private const CREATE_MEETING_ENDPOINT = 'users/me/meetings';

	protected $userId = false;
	protected $responseData = array();
	protected $idToken;

	protected $scope = [
		'meeting:write', 'user:read:admin'
	];

	public function __construct($appID = false, $appSecret = false, $code = false)
	{
		if ($appID === false)
		{
			$appID = trim(CSocServAuth::GetOption("zoom_client_id"));
		}

		if ($appSecret === false)
		{
			$appSecret = trim(CSocServAuth::GetOption("zoom_client_secret"));
		}

		parent::__construct($appID, $appSecret, $code);
	}

	public function GetRedirectURI(): string
	{
		return \CHTTP::URN2URI('/bitrix/tools/oauth/zoom.php');
	}

	public function GetAuthUrl($redirect_uri, $state = ''): string
	{
		return self::AUTH_URL .
			'?client_id=' . $this->appID .
			'&redirect_uri=' . urlencode($redirect_uri) .
			'&response_type=' . 'code' .
			'&scope=' . $this->getScopeEncode() .
			'&response_mode=' . 'form_post' .
			($state <> '' ? '&state=' . JWT::urlsafeB64Encode($state) : '');
	}

	public function getResult()
	{
		return $this->responseData;
	}

	public function GetAccessToken($redirect_uri = ''): bool
	{
		$token = $this->getStorageTokens();
		if (is_array($token))
		{
			$this->access_token = $token['OATOKEN'];
			$this->accessTokenExpires = $token['OATOKEN_EXPIRES'];

			if (!$this->code)
			{
				if ($this->checkAccessToken())
				{
					return true;
				}

				if (isset($token['REFRESH_TOKEN']) && $this->getNewAccessToken($token['REFRESH_TOKEN'], $this->userId, true))
				{
					return true;
				}
			}

			$this->deleteStorageTokens();
		}

		if ($this->code === false)
		{
			return false;
		}

		$query = [
			'code' => $this->code,
			'grant_type' => 'authorization_code',
			'redirect_uri' => $redirect_uri,
		];

		$httpClient = new HttpClient([
			'socketTimeout' => $this->httpTimeout,
			'streamTimeout' => $this->httpTimeout,
		]);
		$httpClient->setAuthorization($this->appID, $this->appSecret);

		$result = $httpClient->post(self::TOKEN_URL, $query);
		try
		{
			$result = \Bitrix\Main\Web\Json::decode($result);
		}
		catch (\Bitrix\Main\ArgumentException $e)
		{
			$result = [];
		}

		if ((isset($result['access_token']) && $result['access_token'] <> ''))
		{
			$this->access_token = $result['access_token'];
			$this->accessTokenExpires = time() + $result['expires_in'];
			$this->refresh_token = $result['refresh_token'];

			$_SESSION["OAUTH_DATA"] = [
				"OATOKEN" => $this->access_token,
				"OATOKEN_EXPIRES" => $this->accessTokenExpires,
				"REFRESH_TOKEN" => $this->refresh_token,
			];
			return true;
		}

		return false;
	}

	public function getNewAccessToken($refreshToken = false, $userId = 0, $save = false): bool
	{
		if (!$this->appID || !$this->appSecret)
		{
			return false;
		}

		if (!$refreshToken)
		{
			$refreshToken = $this->refresh_token;
		}

		$httpClient = new HttpClient(array(
			'socketTimeout' => $this->httpTimeout,
			'streamTimeout' => $this->httpTimeout,
		));
		$httpClient->setAuthorization($this->appID, $this->appSecret);
		$queryPrams = http_build_query([
			'refresh_token' => $refreshToken,
			'grant_type' => 'refresh_token',
		]);
		$url = static::TOKEN_URL.'?'.$queryPrams;
		$result = $httpClient->post($url);

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
			$this->refresh_token = $arResult['refresh_token'];

			if ($save && (int)$userId > 0)
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
							'REFRESH_TOKEN' => $this->refresh_token,
							'OATOKEN_EXPIRES' => $this->accessTokenExpires,
						)
					);
				}
			}

			return true;
		}

		return false;
	}

	public function getCurrentUser()
	{
		if ($this->access_token === false)
		{
			return false;
		}

		$http = new HttpClient();
		$http->setTimeout($this->httpTimeout);
		$http->setHeader('Authorization', 'Bearer ' . $this->access_token);

		$result = $http->get(self::API_ENDPOINT . self::USER_INFO_URL);

		$http->getStatus();

		try
		{
			return Json::decode($result);
		}
		catch (\Bitrix\Main\ArgumentException $e)
		{
			return false;
		}
	}

	public function requestConference($params)
	{
		$http = new HttpClient();

		$http->setHeader('Authorization', 'Bearer '.$this->access_token);
		$http->setHeader('Content-type', 'application/json');
		$requestResult = $http->post(self::API_ENDPOINT . self::CREATE_MEETING_ENDPOINT, json_encode($params));

		try
		{
			$conference = Json::decode($requestResult);
		}
		catch (ArgumentException $e)
		{
			return null;
		}

		return $conference;
	}

	public function getConferenceById($confId)
	{
		$conferenceData = null;
		$newMeetingEndpoint = self::API_ENDPOINT. 'meetings/' . $confId;

		$http = new HttpClient();
		$http->setHeader('Authorization', 'Bearer '.$this->access_token);
		$result = $http->get($newMeetingEndpoint);

		try
		{
			$conferenceData = Json::decode($result);
		}
		catch (ArgumentException $e)
		{
			return null;
		}

		if ($conferenceData['id'] > 0)
		{
			return $conferenceData;
		}

		return null;
	}

	/**
	 * Checks if zoom is connected to user profile.
	 *
	 * @param $userId
	 * @return bool
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function isConnected($userId): bool
	{
		$result = UserTable::getList([
			'filter' => [
				'=USER_ID' => $userId,
				'=EXTERNAL_AUTH_ID' => self::SERVICE_ID
			]
		]);

		if ($user = $result->fetch())
		{
			return true;
		}

		return false;
	}

	public function GetAppInfo(): bool
	{
		return false;
	}

	public function getScopeEncode(): string
	{
		return implode(' ', array_map('urlencode', array_unique($this->getScope())));
	}
}
