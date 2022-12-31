<?

use \Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\JWK;
use Bitrix\Main\Web\JWT;

IncludeModuleLangFile(__FILE__);

class CSocServGoogleOAuth extends CSocServAuth
{
	const ID = "GoogleOAuth";
	const LOGIN_PREFIX = "G_";

	/** @var CGoogleOAuthInterface null  */
	protected $entityOAuth = null;

	/**
	 * @param string $code=false
	 * @return CGoogleOAuthInterface
	 */
	public function getEntityOAuth($code = false)
	{
		if(!$this->entityOAuth)
		{
			$this->entityOAuth = new CGoogleOAuthInterface();
		}

		if($code !== false)
		{
			$this->entityOAuth->setCode($code);
		}

		return $this->entityOAuth;
	}

	public function GetSettings()
	{
		return [
			["google_appid", GetMessage("socserv_google_client_id"), "", ["text", 40]],
			["google_appsecret", GetMessage("socserv_google_client_secret"), "", ["text", 40]],
			[
				'note' => getMessage(
					'socserv_google_note_2',
					[
						'#URL#' => $this->getEntityOAuth()->getRedirectUri(),
						'#MAIL_URL#' => \CHttp::urn2uri('/bitrix/tools/mail_oauth.php'),
					]
				),
			],
		];
	}

	public function CheckSettings()
	{
		return self::GetOption('google_appid') !== '' && self::GetOption('google_appsecret') !== '';
	}


	public function GetFormHtml($arParams)
	{
		$url = static::getUrl('opener', null, $arParams);

		$phrase = ($arParams["FOR_INTRANET"]) ? GetMessage("socserv_google_form_note_intranet") : GetMessage("socserv_google_form_note");

		if($arParams["FOR_INTRANET"])
		{
			return array("ON_CLICK" => 'onclick="BX.util.popup(\''.htmlspecialcharsbx(CUtil::JSEscape($url)).'\', 580, 400)"');
		}
		else
		{
			return '<a href="javascript:void(0)" onclick="BX.util.popup(\''.htmlspecialcharsbx(CUtil::JSEscape($url)).'\', 580, 400)" class="bx-ss-button google-button"></a><span class="bx-spacer"></span><span>'.$phrase.'</span>';
		}
	}

	public function GetOnClickJs($arParams)
	{
		$url = static::getUrl('opener', null, $arParams);
		return "BX.util.popup('".CUtil::JSEscape($url)."', 580, 400)";
	}

	public function getUrl($location = 'opener', $addScope = null, $arParams = array())
	{
		$this->entityOAuth = $this->getEntityOAuth();

		if($this->userId == null)
		{
			$this->entityOAuth->setRefreshToken("skip");
		}

		if($addScope !== null)
		{
			$this->entityOAuth->addScope($addScope);
		}
		if(IsModuleInstalled('bitrix24') && defined('BX24_HOST_NAME'))
		{
			$redirect_uri = static::getControllerUrl()."/redirect.php";
			$state = $this->getEntityOAuth()->getRedirectUri()."?check_key=".\CSocServAuthManager::getUniqueKey()."&state=";
			$backurl = $GLOBALS["APPLICATION"]->GetCurPageParam('', array("logout", "auth_service_error", "auth_service_id", "backurl"));
			$state .= urlencode('provider='.static::ID.
				"&state=".urlencode("backurl=".urlencode($backurl)
					.'&mode='.$location.(isset($arParams['BACKURL'])
						? '&redirect_url='.urlencode($arParams['BACKURL'])
						: '')
			));
		}
		else
		{
			$state = 'provider='.static::ID.'&site_id='.SITE_ID.'&backurl='.urlencode($GLOBALS["APPLICATION"]->GetCurPageParam('check_key='.\CSocServAuthManager::getUniqueKey(), array("logout", "auth_service_error", "auth_service_id", "backurl"))).'&mode='.$location.(isset($arParams['BACKURL']) ? '&redirect_url='.urlencode($arParams['BACKURL']) : '');
			$redirect_uri = $this->getEntityOAuth()->getRedirectUri();
		}

		return $this->entityOAuth->GetAuthUrl($redirect_uri, $state, $arParams['APIKEY']);
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

				if(empty($accessToken) || ((intval($arOauth["OATOKEN_EXPIRES"]) > 0) && (intval($arOauth["OATOKEN_EXPIRES"] < intval(time())))))
				{
					if(isset($arOauth['REFRESH_TOKEN']))
					{
						$this->getEntityOAuth()->getNewAccessToken($arOauth['REFRESH_TOKEN'], $userId, true);
					}

					if(($accessToken = $this->getEntityOAuth()->getToken()) === false)
					{
						return null;
					}
				}
			}
		}

		return $accessToken;
	}

	public function prepareUser($arGoogleUser, $short = false)
	{
		$first_name = "";
		$last_name = "";
		if(is_array($arGoogleUser['name']))
		{
			$first_name = $arGoogleUser['name']['givenName'];
			$last_name = $arGoogleUser['name']['familyName'];
		}
		elseif($arGoogleUser['name'] <> '')
		{
			$aName = explode(" ", $arGoogleUser['name']);
			if($arGoogleUser['given_name'] <> '')
				$first_name = $arGoogleUser['given_name'];
			else
				$first_name = $aName[0];

			if($arGoogleUser['family_name'] <> '')
				$last_name = $arGoogleUser['family_name'];
			elseif(isset($aName[1]))
				$last_name = $aName[1];
		}

		$id = $arGoogleUser['id'] ?? $arGoogleUser['sub'];
		$email = $arGoogleUser['email'];

		if($arGoogleUser['email'] <> '')
		{
			$dbRes = \Bitrix\Main\UserTable::getList(array(
				'filter' => array(
					'=EXTERNAL_AUTH_ID' => 'socservices',
					'=XML_ID' => $email,
				),
				'select' => array('ID'),
				'limit' => 1
			));
			if($dbRes->fetch())
			{
				$id = $email;
			}
		}

		$arFields = array(
			'EXTERNAL_AUTH_ID' => static::ID,
			'XML_ID' => $id,
			'LOGIN' => static::LOGIN_PREFIX.$id,
			'EMAIL' => $email,
			'NAME'=> $first_name,
			'LAST_NAME'=> $last_name,
			'OATOKEN' => $this->entityOAuth->getToken(),
			'OATOKEN_EXPIRES' => $this->entityOAuth->getAccessTokenExpires(),
			'REFRESH_TOKEN' => $this->entityOAuth->getRefreshToken(),
		);

		if($arGoogleUser['gender'] <> '')
		{
			if($arGoogleUser['gender'] == 'male')
			{
				$arFields["PERSONAL_GENDER"] = 'M';
			}
			elseif($arGoogleUser['gender'] == 'female')
			{
				$arFields["PERSONAL_GENDER"] = 'F';
			}
		}

		if(!$short && isset($arGoogleUser['picture']) && static::CheckPhotoURI($arGoogleUser['picture']))
		{
			$arGoogleUser['picture'] = preg_replace("/\?.*$/", '', $arGoogleUser['picture']);
			$arPic = false;
			if ($arGoogleUser['picture'])
			{
				$temp_path =  CFile::GetTempName('', sha1($arGoogleUser['picture']));

				$http = new HttpClient();
				$http->setPrivateIp(false);
				if($http->download($arGoogleUser['picture'], $temp_path))
				{
					$arPic = CFile::MakeFileArray($temp_path);
				}
			}

			if($arPic)
			{
				$arFields["PERSONAL_PHOTO"] = $arPic;
			}
		}

		$arFields["PERSONAL_WWW"] = isset($arGoogleUser['link'])
			? $arGoogleUser['link']
			: $arGoogleUser['url'];

		if(SITE_ID <> '')
		{
			$arFields["SITE_ID"] = SITE_ID;
		}

		return $arFields;
	}

	public function Authorize()
	{
		global $APPLICATION;
		$APPLICATION->RestartBuffer();

		$bSuccess = false;
		$bProcessState = false;

		$authError = SOCSERV_AUTHORISATION_ERROR;

		if(
			isset($_REQUEST["code"]) && $_REQUEST["code"] <> ''
			&& CSocServAuthManager::CheckUniqueKey()
		)
		{
			$this->getEntityOAuth()->setCode($_REQUEST["code"]);

			$bProcessState = true;

			if($this->getEntityOAuth()->GetAccessToken() !== false)
			{
				$arGoogleUser = $this->getEntityOAuth()->GetCurrentUser();

				if(is_array($arGoogleUser) && !isset($arGoogleUser["error"]))
				{
					$arFields = self::prepareUser($arGoogleUser);
					$authError = $this->AuthorizeUser($arFields);
				}
			}
		}

		if(!$bProcessState)
		{
			unset($_REQUEST["state"]);
		}

		$bSuccess = $authError === true;

		$aRemove = array("logout", "auth_service_error", "auth_service_id", "code", "error_reason", "error", "error_description", "check_key", "current_fieldset");

		if($bSuccess)
		{
			CSocServUtil::checkOAuthProxyParams();

			$url = ($APPLICATION->GetCurDir() == "/login/") ? "" : $APPLICATION->GetCurDir();
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

						$url = (!empty($arUrlQuery)) ? $urlPath . '?' . implode("&", $arUrlQuery) : $urlPath;
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
		<script type="text/javascript">
		'.$location.'
		</script>
		';

		echo $JSScript;

		CMain::FinalActions();
	}

	public function setUser($userId)
	{
		$this->getEntityOAuth()->setUser($userId);
	}

	public function getFriendsList($limit, &$next)
	{
		$res = array();

		if($this->getEntityOAuth()->GetAccessToken() !== false)
		{
			$res = $this->getEntityOAuth()->getCurrentUserFriends($limit, $next);

			foreach($res as $key => $contact)
			{
				$contact['uid'] = $contact['email'];

				$arName = $contact['name'];

				$contact['first_name'] = trim($arName['givenName']);
				$contact['last_name'] = trim($arName['familyName']);
				$contact['second_name'] = trim($arName['additionalName']);

				if(!$contact['first_name'] && !$contact['last_name'])
				{
					$contact['first_name'] = $contact['uid'];
				}

				$res[$key] = $contact;
			}
		}

		return $res;
	}
}

class CGoogleOAuthInterface extends CSocServOAuthTransport
{
	const SERVICE_ID = "GoogleOAuth";

	public const CERTS_URL = "https://www.googleapis.com/oauth2/v3/certs";
	public const JWT_ALG = ["RS256"];

	const AUTH_URL = "https://accounts.google.com/o/oauth2/auth";
	const TOKEN_URL = "https://accounts.google.com/o/oauth2/token";
	const CONTACTS_URL = "https://www.googleapis.com/oauth2/v1/userinfo";
	const FRIENDS_URL = "https://www.google.com/m8/feeds/contacts/default/full";
	const TOKENINFO_URL = "https://www.googleapis.com/oauth2/v2/tokeninfo";

	const REDIRECT_URI = "/bitrix/tools/oauth/google.php";

	protected $standardScope = array(
		'https://www.googleapis.com/auth/userinfo.email',
		'https://www.googleapis.com/auth/userinfo.profile',
	);

	protected $scope = array();

	protected $arResult = array();

	protected ?string $idTokenAuth = null;
	protected ?array $fetchedPublicKeys = null;

	public function __construct($appID = false, $appSecret = false, $code = false)
	{
		if($appID === false)
		{
			$appID = trim(CSocServGoogleOAuth::GetOption("google_appid"));
		}

		if($appSecret === false)
		{
			$appSecret = trim(CSocServGoogleOAuth::GetOption("google_appsecret"));
		}

		$this->scope = $this->standardScope;

		$this->checkSavedScope();

		parent::__construct($appID, $appSecret, $code);
	}

	protected function checkSavedScope()
	{
		$savedScope = \Bitrix\Main\Config\Option::get('socialservices', 'saved_scope_'.static::SERVICE_ID, '');
		if($savedScope)
		{
			$savedScope = unserialize($savedScope, ['allowed_classes' => false]);
			if(is_array($savedScope))
			{
				$this->scope = array_merge($this->scope, $savedScope);
			}
		}
	}

	protected function saveScope()
	{
		$scope = array_unique(array_diff($this->scope, $this->standardScope));
		\Bitrix\Main\Config\Option::set('socialservices', 'saved_scope_'.static::SERVICE_ID, serialize($scope));
	}

	public function addScope($scope)
	{
		parent::addScope($scope);

		$this->saveScope();

		return $this;
	}

	public function getScopeEncode()
	{
		return implode('+', array_map('urlencode', array_unique($this->getScope())));
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

	public function GetAuthUrl($redirect_uri, $state = '', $apiKey = '')
	{
		return static::AUTH_URL.
			"?client_id=".urlencode($this->appID).
			"&redirect_uri=".urlencode($redirect_uri).
			"&scope=".$this->getScopeEncode().
			"&response_type=code".
			"&access_type=offline".
			($this->refresh_token <> '' ? '' : '&approval_prompt=force').
			($state <> '' ? '&state='.urlencode($state) : '').
			($apiKey !== '' ? '&key=' . urlencode($apiKey) : '')
		;
	}

	public function setIdTokenAuth(string $tokenId): void
	{
		$this->idTokenAuth = $tokenId;
	}

	private function fetchPublicKeys(): ?array
	{
		if ($this->fetchedPublicKeys)
		{
			return $this->fetchedPublicKeys;
		}

		try
		{
			$publicKeys = $this->getDecodedJson(self::CERTS_URL);
			if (empty($publicKeys['keys']) || count($publicKeys['keys']) < 1)
			{
				return null;
			}

			$parsedPublicKeys = JWK::parseKeySet($publicKeys['keys']);
			foreach ($parsedPublicKeys as $keyId => $publicKey)
			{
				$details = openssl_pkey_get_details($publicKey);
				$this->fetchedPublicKeys[$keyId] = $details['key'];
			}

			return $this->fetchedPublicKeys;
		}
		catch (\Exception $e)
		{
		}

		return null;
	}

	private function decodeIdentityToken(string $identityToken): array
	{
		$publicKeys = $this->fetchPublicKeys();
		if ($publicKeys === null)
		{
			return [];
		}

		try
		{
			return (array)JWT::decode($identityToken, $publicKeys, self::JWT_ALG);
		}
		catch (UnexpectedValueException $exception)
		{
			return [];
		}
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
				if($this->checkAccessToken())
				{
					return true;
				}
				elseif(isset($tokens["REFRESH_TOKEN"]))
				{
					if($this->getNewAccessToken($tokens["REFRESH_TOKEN"], $this->userId, true))
					{
						return true;
					}
				}
			}

			$this->deleteStorageTokens();
		}

		if($this->code === false)
		{
			return false;
		}

		if($redirect_uri === false)
		{
			if(IsModuleInstalled('bitrix24') && defined('BX24_HOST_NAME'))
			{
				$redirect_uri = \CSocServGoogleOAuth::getControllerUrl()."/redirect.php";
			}
			else
			{
				$redirect_uri = $this->getRedirectUri();
			}
		}

		$authParams = [
			"client_id" => $this->appID,
			"code" => $this->code,
			"redirect_uri" => $redirect_uri,
			"grant_type" => "authorization_code",
			"client_secret" => $this->appSecret,
		];

		$this->arResult = $this->getDecodedJson(static::TOKEN_URL, $authParams);

		if(isset($this->arResult["access_token"]) && $this->arResult["access_token"] <> '')
		{
			if(isset($this->arResult["refresh_token"]) && $this->arResult["refresh_token"] <> '')
			{
				$this->refresh_token = $this->arResult["refresh_token"];
			}
			$this->access_token = $this->arResult["access_token"];
			$this->accessTokenExpires = $this->arResult["expires_in"] + time();

			$_SESSION["OAUTH_DATA"] = array(
				"OATOKEN" => $this->access_token,
				"OATOKEN_EXPIRES" => $this->accessTokenExpires,
				"REFRESH_TOKEN" => $this->refresh_token,
			);

			return true;
		}
		return false;
	}

	public function GetCurrentUser()
	{
		if ($this->idTokenAuth)
		{
			$identity = $this->decodeIdentityToken($this->idTokenAuth);

			return $identity ?: false;
		}

		if($this->access_token === false)
			return false;

		$result = $this->getDecodedJson(static::CONTACTS_URL.'?access_token='.urlencode($this->access_token));

		if ($result)
		{
			$result["access_token"] = $this->access_token;
			$result["refresh_token"] = $this->refresh_token;
			$result["expires_in"] = $this->accessTokenExpires;
		}

		return $result;
	}

	public function GetAppInfo()
	{
		if ($this->idTokenAuth)
		{
			$identity = $this->decodeIdentityToken($this->idTokenAuth);
			if (empty($identity['aud']))
			{
				return false;
			}

			return [
				'id' => $identity['aud'],
			];
		}
		if ($this->access_token === false)
		{
			return false;
		}

		$result = $this->getDecodedJson(static::TOKENINFO_URL.'?access_token='.urlencode($this->access_token));

		if ($result && $result["audience"])
		{
			$result["id"] = $result["audience"];
		}

		return $result;
	}

	public function GetCurrentUserFriends($limit, &$next)
	{
		if($this->access_token === false)
			return false;

		$http = new HttpClient();
		$http->setHeader('GData-Version', '3.0');
		$http->setHeader('Authorization', 'Bearer '.$this->access_token);

		$url = static::FRIENDS_URL.'?';

		$limit = (int)$limit;
		$next = (int)$next;

		if ($limit > 0)
		{
			$url .= '&max-results='.$limit;
		}

		if ($next > 0)
		{
			$url .= '&start-index='.$next;
		}

		$result = $http->get($url);

		if (!defined("BX_UTF"))
		{
			$result = \Bitrix\Main\Text\Encoding::convertEncoding($string, $charset_in, $charset_out)($result, "utf-8", LANG_CHARSET);
		}

		if((int)$http->getStatus() === 200)
		{
			$obXml = new \CDataXML();
			if($obXml->loadString($result))
			{
				$tree = $obXml->getTree();

				$total = $tree->elementsByName("totalResults");
				$total = (int)$total[0]->textContent();

				$limitNode = $tree->elementsByName("itemsPerPage");
				$next += (int)$limitNode[0]->textContent();

				if($next >= $total)
				{
					$next = '__finish__';
				}

				$arFriends = array();
				$arEntries = $tree->elementsByName('entry');
				foreach($arEntries as $entry)
				{
					$arEntry = array();
					$entryChildren = $entry->children();

					foreach ($entryChildren as $child)
					{
						$tag = $child->name();

						switch($tag)
						{
							case 'category':
							case 'updated':
							case 'edited';
								break;

							case 'name':
								$arEntry[$tag] = array();
								foreach($child->children() as $subChild)
								{
									$arEntry[$tag][$subChild->name()] = $subChild->textContent();
								}
							break;

							case 'email':

								if($child->getAttribute('primary') == 'true')
								{
									$arEntry[$tag] = $child->getAttribute('address');
								}

							break;
							default:

								$tagContent = $tag == 'link'
									? $child->getAttribute('href')
									: $child->textContent();

								if($child->getAttribute('rel'))
								{
									if(!isset($arEntry[$tag]))
									{
										$arEntry[$tag] = array();
									}

									$arEntry[$tag][preg_replace("/^[^#]*#/", "", $child->getAttribute('rel'))] = $tagContent;
								}
								elseif(isset($arEntry[$tag]))
								{
									if(!is_array($arEntry[$tag][0]) || !isset($arEntry[$tag][0]))
									{
										$arEntry[$tag] = array($arEntry[$tag], $tagContent);
									}
									else
									{
										$arEntry[$tag][] = $tagContent;
									}
								}
								else
								{
									$arEntry[$tag] = $tagContent;
								}
						}
					}

					if($arEntry['email'])
					{
						$arFriends[] = $arEntry;
					}
				}
				return $arFriends;
			}
		}

		return false;
	}

	public function getNewAccessToken($refreshToken = false, $userId = 0, $save = false)
	{
		if($this->appID == false || $this->appSecret == false)
		{
			return false;
		}

		if($refreshToken === false)
		{
			$refreshToken = $this->refresh_token;
		}

		$this->arResult = $this->getDecodedJson(static::TOKEN_URL, [
			"client_id" => $this->appID,
			"refresh_token"=>$refreshToken,
			"grant_type"=>"refresh_token",
			"client_secret" => $this->appSecret,
		]);

		if (isset($this->arResult["access_token"]) && $this->arResult["access_token"] <> '')
		{
			$this->access_token = $this->arResult["access_token"];
			$this->accessTokenExpires = $this->arResult["expires_in"] + time();
			if ($save && intval($userId) > 0)
			{
				$dbSocservUser = \Bitrix\Socialservices\UserTable::getList(array(
					'filter' => array(
						'=EXTERNAL_AUTH_ID' => static::SERVICE_ID,
						'=USER_ID' => $userId,
					),
					'select' => array("ID")
				));
				if($arOauth = $dbSocservUser->Fetch())
				{
					\Bitrix\Socialservices\UserTable::update($arOauth["ID"], array(
						"OATOKEN" => $this->access_token,
						"OATOKEN_EXPIRES" => $this->accessTokenExpires)
					);
				}
			}

			return true;
		}

		return false;
	}

	public function getRedirectUri()
	{
		return \CHTTP::URN2URI(static::REDIRECT_URI);
	}
}
