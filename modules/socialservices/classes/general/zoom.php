<?php

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Error;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use \Bitrix\Main\Web\HttpClient;
use \Bitrix\Main\Web\Json;
use \Bitrix\Main\Web\JWT;
use Bitrix\Socialservices\UserTable;
use Bitrix\Socialservices\ZoomMeetingTable;

IncludeModuleLangFile(__FILE__);

class CSocServZoom extends CSocServAuth
{
	public const ID = 'zoom';
	private const CONTROLLER_URL = 'https://www.bitrix24.com/controller';
	private const LOGIN_PREFIX = 'zoom_';
	public const EMPTY_TYPE = "EMPTY";

	protected $entityOAuth;

	public function __construct($userId = null)
	{
		$this->getEntityOAuth();

		parent::__construct($userId);
	}

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
			$backurl = $APPLICATION->GetCurPageParam('', ['logout', 'auth_service_error', 'auth_service_id', 'backurl']);
			$state = $this->getEntityOAuth()->GetRedirectURI() .
				urlencode('?state=' . JWT::urlsafeB64Encode('backurl=' . $backurl . '&check_key=' . $_SESSION['UNIQUE_KEY']));
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

		if (SITE_ID <> '')
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

					$userData = [
						'externalUserId' => $arUser['id'],
						'externalAccountId' => $arUser['account_id'],
						'socServLogin' => $arFields['LOGIN'],
					];
					$zc = new \Bitrix\SocialServices\Integration\Zoom\ZoomController();
					$zc->registerZoomUser($userData);
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
		$result = new Result();
		$conferenceData = null;
		if (!$this->getEntityOAuth()->GetAccessToken())
		{
			return $result->addError(new Error('Could not get oauth token'));
		}

		if (isset($params['ENTITY_TYPE_ID']))
		{
			$entityTypeId = $params['ENTITY_TYPE_ID'];
			unset($params['ENTITY_TYPE_ID']);
		}

		if (isset($params['ENTITY_ID']))
		{
			$entityId = $params['ENTITY_ID'];
			unset($params['ENTITY_ID']);
		}

		$requestConferenceResult = $this->getEntityOAuth()->requestConference($params);
		if(!$requestConferenceResult->isSuccess())
		{
			return $result->addErrors($requestConferenceResult->getErrors());
		}
		$conferenceData = $requestConferenceResult->getData();

		$userData = $this->getEntityOAuth()->getCurrentUser();
		if(!is_array($userData))
		{
			return $result->addError(new Error('Cannot get user data'));
		}
		$conferenceData['externalUserId'] = $userData['id'];
		$conferenceData['externalAccountId'] = $userData['account_id'];

		$conference['join_url'] = $this->attachPasswordToUrl($conferenceData['join_url'], $conferenceData['encrypted_password']);

		$startTimeStamp = \DateTime::createFromFormat(DATE_ATOM, $conferenceData['start_time'])->getTimestamp();
		$startDateTime = DateTime::createFromTimestamp($startTimeStamp);

		$params = [
			'ENTITY_TYPE_ID' => ($entityTypeId ?? self::EMPTY_TYPE),
			'ENTITY_ID' => ($entityId ?? 0),
			'CONFERENCE_EXTERNAL_ID' => $conferenceData['id'],
			'CONFERENCE_URL' => $conferenceData['join_url'],
			'CONFERENCE_PASSWORD' => $conferenceData['encrypted_password'],
			'CONFERENCE_CREATED' => (new DateTime()),
			'CONFERENCE_STARTED' => $startDateTime,
			'DURATION' => $conferenceData['duration'],
			'TITLE' => $conferenceData['topic'],
			'SHORT_LINK' => $this->getShortLink($conferenceData['id']),
		];

		$addResult = ZoomMeetingTable::add($params);
		if (!$addResult->isSuccess())
		{
			return $result->addErrors($addResult->getErrors());
		}
		$conferenceData['bitrix_internal_id'] = $addResult->getId();

		return $result->setData($conferenceData);
	}

	private function getShortLink(int $conferenceId): string
	{
		$host = UrlManager::getInstance()->getHostUrl();
		$controllerUrl = \Bitrix\Main\Engine\UrlManager::getInstance()->create(
			'crm.api.zoomUser.registerJoinMeeting',
			['conferenceId' => $conferenceId]
		)->getUri();

		return $host.\CBXShortUri::GetShortUri($controllerUrl);
	}

	/**
	 * Updates Zoom conference and saves the result in DB table.
	 *
	 * @param array $updateParams Params which uses for update (conference dates).
	 * @return Result
	 * @throws Exception
	 */
	public function updateConference(array $updateParams): Result
	{
		$result = new Result();
		$params = [];

		if (!$this->getEntityOAuth()->GetAccessToken())
		{
			return $result->addError(new Error('Could not get oauth token'));
		}

		$preparedData = $this->prepareDataToUpdate($updateParams);
		if (empty($preparedData))
		{
			return $result;
		}

		$externalConferenceId = $preparedData['id'];
		unset($preparedData['id']);

		$requestConferenceResult = $this->getEntityOAuth()->updateConference($externalConferenceId, $preparedData);
		if (!$requestConferenceResult->isSuccess())
		{
			return $result->addErrors($requestConferenceResult->getErrors());
		}

		if (isset($updateParams['start_time']))
		{
			$params['CONFERENCE_STARTED'] = DateTime::createFromUserTime($updateParams['start_time']);
		}

		if (isset($preparedData['duration']))
		{
			$params['DURATION'] = $preparedData['duration'];
		}

		if (!empty($params))
		{
			$addResult = ZoomMeetingTable::update($updateParams['meeting_id'], $params);
			if (!$addResult->isSuccess())
			{
				return $result->addErrors($addResult->getErrors());
			}
		}

		return $result;
	}

	/**
	 * Prepares start date and duration for update in Zoom, only if it is different from activity start date and duration.
	 *
	 * @param array $updateParams
	 * @return array|null
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function prepareDataToUpdate(array $updateParams): ?array
	{
		$preparedDataToUpdate = [];

		//get activity start and end timestamps
		$activityStartTimeStamp = DateTime::createFromUserTime($updateParams['start_time'])->getTimestamp();
		$activityEndTimeStamp = DateTime::createFromUserTime($updateParams['end_time'])->getTimestamp();

		$meetingData = ZoomMeetingTable::getRowById($updateParams['meeting_id']);
		if (!$meetingData)
		{
			return null;
		}

		//Prepare start_time only if the activity start_time does not match the conference start_time.
		$meetingStartTimeStamp = $meetingData['CONFERENCE_STARTED']->getTimestamp();
		if ($meetingStartTimeStamp !== $activityStartTimeStamp)
		{
			$preparedDataToUpdate['start_time'] = DateTime::createFromUserTime($updateParams['start_time'])
				->setTimeZone(new \DateTimeZone('UTC'))
				->format(DATE_ATOM);
		}

		//Prepare duration only if the activity duration does not match the conference duration.
		$currentActivityDuration = ($activityEndTimeStamp - $activityStartTimeStamp) / 60;
		if ($currentActivityDuration !== (int)$meetingData['DURATION'])
		{
			$preparedDataToUpdate['duration'] = $currentActivityDuration;
		}

		//Continue only if duration or start_time has been changed ($preparedDataToUpdate is not empty).
		if (!empty($preparedDataToUpdate))
		{
			$preparedDataToUpdate['id'] = $meetingData['CONFERENCE_EXTERNAL_ID'];
		}

		return $preparedDataToUpdate;
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

	private function attachPasswordToUrl(string $conferenceUrl, string $password): string
	{
		$url = new \Bitrix\Main\Web\Uri($conferenceUrl);
		$queryParams = $url->getQuery();
		$parsedParams = [];
		parse_str($queryParams, $parsedParams);
		if (!isset($parsedParams['pwd']))
		{
			$url->addParams(['pwd' => $password]);
			$conferenceUrl = $url->getUri();
		}

		return $conferenceUrl;
	}

	/**
	 * Notifies Zoom that we comply with the user's data policy after the user uninstalls Bitrix24 app.
	 *
	 * @deprecated by Zoom since August 7, 2021.
	 *
	 * @param array $payload
	 *
	 * @return Result
	 */
	public function sendComplianceRequest(array $payload): Result
	{
		return $this->getEntityOAuth()->sendComplianceNotify($payload);
	}
}

class CZoomInterface extends CSocServOAuthTransport
{
	const SERVICE_ID = "zoom";

	const AUTH_URL = 'https://zoom.us/oauth/authorize';
	const TOKEN_URL = 'https://zoom.us/oauth/token';
	const COMPLIANCE_URL = 'https://api.zoom.us/oauth/data/compliance';

	private const API_ENDPOINT = 'https://api.zoom.us/v2/';

	private const USER_INFO_URL = 'users/me';
	private const CREATE_MEETING_ENDPOINT = 'users/me/meetings';
	private const UPDATE_MEETING_ENDPOINT = 'meetings/';

	private const CACHE_TIME_CONNECT_INFO = "86400"; //One day
	public const CACHE_DIR_CONNECT_INFO = "/socialservices/zoom/";

	protected $userId = false;
	protected $responseData = array();
	protected $idToken;

	protected $scope = [
		'meeting:write', 'user:read:admin', 'meeting:read', 'recording:read'
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

		$endPoint = self::API_ENDPOINT . self::USER_INFO_URL;
		$requestResult = $this->sendRequest(HttpClient::HTTP_GET, $endPoint);

		if (!$requestResult->isSuccess())
		{
			return null;
		}

		return $requestResult->getData();
	}

	public function requestConference($params): Result
	{
		$result = new Result();

		$endPoint = self::API_ENDPOINT . self::CREATE_MEETING_ENDPOINT;
		$requestResult = $this->sendRequest(HttpClient::HTTP_POST, $endPoint, $params);

		if (!$requestResult->isSuccess())
		{
			return $result->addErrors($requestResult->getErrors());
		}
		$response = $requestResult->getData();

		if (isset($response['code']) && $response['code'] != 200)
		{
			// zoom api error
			return $result->addError(new Error($response['message'], $response['code']));
		}

		return $result->setData($response);
	}

	public function updateConference(int $conferenceId, array $params): Result
	{
		$endPoint = self::API_ENDPOINT . self::UPDATE_MEETING_ENDPOINT . $conferenceId;

		return $this->sendRequest(HttpClient::HTTP_PATCH, $endPoint, $params);
	}

	public function getConferenceById($confId): ?array
	{
		$newMeetingEndpoint = self::API_ENDPOINT. 'meetings/' . $confId;
		$conferenceDataResult = $this->sendRequest(HttpClient::HTTP_GET, $newMeetingEndpoint);

		if (!$conferenceDataResult->isSuccess())
		{
			return null;
		}

		$conferenceData = $conferenceDataResult->getData();
		if (!is_array($conferenceData) || $conferenceData['id'] <= 0)
		{
			return null;
		}

		return $conferenceData;
	}

	public function getConferenceFiles($confId): ?array
	{
		$endPoint = self::API_ENDPOINT . "/meetings/{$confId}/recordings";
		$requestResult = $this->sendRequest(HttpClient::HTTP_GET, $endPoint);
		if (!$requestResult->isSuccess())
		{
			return null;
		}

		return $requestResult->getData();
	}

	/**
	 * @deprecated by Zoom since August 7, 2021.
	 * @param array $params
	 *
	 * @return Result
	 * @throws ArgumentException
	 */
	public function sendComplianceNotify(array $params): Result
	{
		$requestParams = [
			'client_id' => $this->appID,
			'user_id' => $params['user_id'],
			'account_id' => $params['account_id'],
			'deauthorization_event_received' => $params,
			'compliance_completed' => true,
		];

		$result = new Result();
		$http = new HttpClient([
			'socketTimeout' => $this->httpTimeout,
			'streamTimeout' => $this->httpTimeout,
		]);

		$http->setAuthorization($this->appID, $this->appSecret);
		$http->setHeader('Content-type', 'application/json');
		$requestResult = $http->post(self::COMPLIANCE_URL, Json::encode($requestParams));

		try
		{
			$decodedData = Json::decode($requestResult);
			$result->setData($decodedData);
		}
		catch (ArgumentException $e)
		{
			return $result->addError(new Error('Could not decode service response'));
		}

		return $result;
	}

	private function sendRequest(string $method, string $endPoint, array $params = []): Result
	{
		$result = new Result();

		$http = new HttpClient(array(
			'socketTimeout' => $this->httpTimeout,
			'streamTimeout' => $this->httpTimeout,
		));
		$http->setHeader('Authorization', 'Bearer '.$this->access_token);

		switch ($method)
		{
			case HttpClient::HTTP_PATCH:
				$http->setHeader('Content-type', 'application/json');
				$http->query(HttpClient::HTTP_PATCH, $endPoint, Json::encode($params));
				if ($http->getStatus() != 204)
				{
					// zoom api error
					$requestResult = $http->getResult();
					$response = Json::decode($requestResult);
					return $result->addError(new Error($response['message'], $response['code']));
				}
				return $result;

			case HttpClient::HTTP_POST:
				$http->setHeader('Content-type', 'application/json');
				$requestResult = $http->post($endPoint, Json::encode($params));
				break;

			case HttpClient::HTTP_GET:
				$requestResult = $http->get($endPoint);
				break;

			default:
				return $result->addError(new Error('Unsupported request method'));
		}

		try
		{
			$decodedData = Json::decode($requestResult);
			$result->setData($decodedData);
		}
		catch (ArgumentException $e)
		{
			return $result->addError(new Error('Could not decode service response'));
		}

		return $result;
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
	public static function isConnected(int $userId): bool
	{
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cacheId = self::SERVICE_ID .'|'. $userId;
		$user = null;
		if ($cache->initCache(self::CACHE_TIME_CONNECT_INFO, $cacheId, self::CACHE_DIR_CONNECT_INFO))
		{
			$user = $cache->getVars()['user'];
		}
		elseif ($cache->startDataCache())
		{
			$user = UserTable::getRow([
					'filter' => [
						'=USER_ID' => $userId,
						'=EXTERNAL_AUTH_ID' => self::SERVICE_ID
					]
				]);

			$cache->endDataCache(['user' => $user]);
		}

		return $user !== null;
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
