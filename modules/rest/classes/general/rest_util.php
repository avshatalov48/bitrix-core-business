<?php
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Security;

IncludeModuleLangFile(__FILE__);

class CRestUtil
{
	const GLOBAL_SCOPE = '_global';
	const EVENTS = '_events';
	const PLACEMENTS = '_placements';

	const HANDLER_SESSION_TTL = 7;

	const BATCH_MAX_LENGTH = 50;

	const METHOD_DOWNLOAD = "download";
	const METHOD_UPLOAD = "upload";

	const TOKEN_DELIMITER = "|";

	const BITRIX_1C_APP_CODE = 'bitrix.1c';

	const PLACEMENT_APP_URI = 'REST_APP_URI';

	public static function sendHeaders()
	{
		Header('Access-Control-Allow-Origin: *');
		Header('Access-Control-Allow-Headers: origin, content-type, accept');
		Header('X-Content-Type-Options: nosniff');
	}

	public static function getStandardParams()
	{
		return array(
			"PARAMETERS" => array(
				"VARIABLE_ALIASES" => Array(
					"method" => Array("NAME" => GetMessage('REST_PARAM_METHOD_NAME')),
				),
				"SEF_MODE" => Array(
					"path" => array(
						"NAME" => GetMessage('REST_PARAM_PATH'),
						"DEFAULT" => "#method#",
						"VARIABLES" => array("method" => "method"),
					),
				),
			)
		);
	}

	public static function getRequestData()
	{
		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		$server = \Bitrix\Main\Context::getCurrent()->getServer();

		$query = $request->toArray();

		if($request->isPost() && $request->getPostList()->isEmpty())
		{
			$rawPostData = trim($request->getInput());

			if(isset($server['HTTP_CONTENT_TYPE']))
			{
				$requestContentType = $server['HTTP_CONTENT_TYPE'];
			}
			else
			{
				$requestContentType = $server['CONTENT_TYPE'];
			}

			$requestContentType = trim(preg_replace('/;.*$/', '', $requestContentType));

			$postData = array();

			switch($requestContentType)
			{
				case 'application/json':

					try
					{
						$postData = \Bitrix\Main\Web\Json::decode($rawPostData);
					}
					catch(\Bitrix\Main\ArgumentException $e)
					{
						$postData = array();
					}

					break;

				default:

					if($rawPostData <> '')
					{
						parse_str($rawPostData, $postData);
					}

					break;
			}

			if (!is_array($postData))
			{
				$postData = [];
			}

			$query = array_replace($query, $postData);
		}

		// TODO: process errorMessage and output correct error message on encoding mismatch
		$query = \Bitrix\Main\Text\Encoding::convertEncoding($query, 'UTF-8', LANG_CHARSET);

		return $query;
	}

	/**
	 * @param int|null $userId - if not set - check system user
	 * @return bool
	 */
	public static function isAdmin(?int $userId = null): bool
	{
		global $USER;

		if ($userId > 0)
		{
			if (ModuleManager::isModuleInstalled('bitrix24'))
			{
				return $USER->CanDoOperation('bitrix24_config', $userId);
			}

			return in_array(1, \CUser::GetUserGroup($userId));
		}

		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			return $USER->CanDoOperation('bitrix24_config');
		}

		return $USER->IsAdmin();
	}

	/**
	 * @param $appInfo
	 * @param int|null $userId - if not set - check system user
	 * @return bool|mixed
	 */
	public static function canInstallApplication($appInfo = null, ?int $userId = null)
	{
		global $USER;

		if (
			!empty($appInfo['HOLD_INSTALL_BY_TRIAL'])
			&& $appInfo['HOLD_INSTALL_BY_TRIAL'] === 'Y'
			&& \Bitrix\Rest\Marketplace\Client::isSubscriptionDemo()
		)
		{
			return false;
		}

		if (static::isAdmin($userId))
		{
			return true;
		}

		if (
			is_array($appInfo)
			&& $appInfo['TYPE'] === \Bitrix\Rest\AppTable::TYPE_CONFIGURATION
			&& !empty($appInfo['MANIFEST']['CODE'])
		)
		{
			$access = \Bitrix\Rest\Configuration\Manifest::checkAccess(
				\Bitrix\Rest\Configuration\Manifest::ACCESS_TYPE_IMPORT,
				$appInfo['MANIFEST']['CODE']
			);

			return $access['result'];
		}

		$hasAccess = $USER->CanAccess(static::getInstallAccessList());
		if ($hasAccess && is_array($appInfo))
		{
			return static::appCanBeInstalledByUser($appInfo);
		}

		return $hasAccess;
	}

	public static function appCanBeInstalledByUser(array $appInfo)
	{
		return $appInfo['USER_INSTALL'] === 'Y';
	}

	public static function getInstallAccessList()
	{
		$accessList = \Bitrix\Main\Config\Option::get('rest', 'install_access_list', '');

		return $accessList === '' ? array() : explode(",", $accessList);
	}

	public static function setInstallAccessList($accessList)
	{
		if(is_array($accessList))
		{
			$value = implode(',', $accessList);
		}
		else
		{
			$value = '';
		}

		\Bitrix\Main\Config\Option::set('rest', 'install_access_list', $value);
	}

	public static function notifyInstall($appInfo)
	{
		global $USER;

		if (Loader::includeModule('im'))
		{
			$userName = \CUser::FormatName("#NAME# #LAST_NAME#", array(
				"NAME" => $USER->GetFirstName(),
				"LAST_NAME" => $USER->GetLastName(),
				"SECOND_NAME" => $USER->GetSecondName(),
				"LOGIN" => $USER->GetLogin()
			));

			$adminList = \CRestUtil::getAdministratorIdList();
			foreach($adminList as $id)
			{
				$messageFields = array(
					"TO_USER_ID" => $id,
					"FROM_USER_ID" => $USER->GetID(),
					"NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
					"NOTIFY_MODULE" => "rest",
					"NOTIFY_TAG" => "REST|APP_INSTALL_NOTIFY|".$USER->GetID()."|TO|".$id,
					"NOTIFY_SUB_TAG" => "REST|APP_INSTALL_NOTIFY",
					"NOTIFY_MESSAGE" => GetMessage(
						"REST_APP_INSTALL_NOTIFY_TEXT",
						array(
							"#USER_NAME#" => $userName,
							"#APP_NAME#" => $appInfo['APP_NAME'],
							"#APP_CODE#" => $appInfo['CODE'],
							"#APP_LINK#" => \Bitrix\Rest\Marketplace\Url::getApplicationDetailUrl(urlencode($appInfo['CODE'])),
						)),
				);
				\CIMNotify::Add($messageFields);
			}
		}
	}

	public static function signLicenseRequest(array $request, $licenseKey)
	{
		if(Loader::includeModule('bitrix24') && defined('BX24_HOST_NAME'))
		{
			$request['BX_TYPE'] = 'B24';
			$request['BX_LICENCE'] = BX24_HOST_NAME;
			$request['BX_HASH'] = \CBitrix24::RequestSign(md5(implode("|", $request)));
		}
		else
		{
			$request['BX_TYPE'] = ModuleManager::isModuleInstalled('intranet') ? 'CP' : 'BSM';
			$request['BX_LICENCE'] = md5("BITRIX".$licenseKey."LICENCE");
			$request['BX_HASH'] = md5(md5(implode("|", $request)).md5($licenseKey));
		}

		return $request;
	}

	public static function ConvertDate($dt)
	{
		return $dt ? date('c', MakeTimeStamp($dt, FORMAT_DATE) + date("Z")) : '';
	}


	public static function ConvertDateTime($dt)
	{
		return $dt ? date('c', MakeTimeStamp($dt) - CTimeZone::GetOffset()) : '';
	}


	/**
	 * @param string $iso8601 date in ISO-8601 format (for example: '2013-05-14T12:00:50+04:00')
	 * @return string date in Bitrix format, or FALSE (bool) on error
	 */
	public static function unConvertDate($iso8601)
	{
		if(is_array($iso8601))
		{
			foreach($iso8601 as $key => $value)
			{
				$iso8601[$key] = self::unConvertDateTime($value);
			}

			return $iso8601;
		}
		else
		{
			$date = false;
			$timestamp = strtotime($iso8601);

			if ($timestamp !== false)
				$date = ConvertTimeStamp($timestamp, 'SHORT');

			return ($date);
		}
	}

	/**
	 * @param string $iso8601 Datetime in ISO-8601 format (for example: '2013-05-14T12:00:50+04:00').
	 * @param bool $enableOffset Add user timezone offset.
	 * If $enableOffset == false, time in server timezone will be returned.
	 * If $enableOffset == true, time in user timezone will be returned.
	 * @return string datetime in Bitrix format, or FALSE (bool) on error
	 */
	public static function unConvertDateTime($iso8601, $enableOffset = false)
	{
		if(is_array($iso8601))
		{
			foreach($iso8601 as $key => $value)
			{
				$iso8601[$key] = self::unConvertDateTime($value, $enableOffset);
			}

			return $iso8601;
		}
		else
		{
			$date = false;
			$timestamp = strtotime($iso8601);

			if ($timestamp !== false)
			{
				if($enableOffset)
				{
					$timestamp += CTimeZone::GetOffset();
				}
				$date = ConvertTimeStamp($timestamp, 'FULL');
			}

			return ($date);
		}
	}

	public static function getMemberId()
	{
		if(CModule::IncludeModule('bitrix24'))
		{
			return \CBitrix24::getMemberId();
		}
		else
		{
			return \Bitrix\Rest\OAuthService::getMemberId();
		}
	}

	public static function isStatic($url)
	{
		return preg_match("/^http[s]{0,1}:\/\/[^\/]*?(\.apps-bitrix24\.com|\.bitrix24-cdn\.com|cdn\.bitrix24\.|app\.bitrix24\.com|upload-.*?\.s3\.amazonaws\.com\/app_local\/)/i", $url);
	}

	public static function GetFile($fileId , $resizeParam = false)
	{
		$fileSrc = array();
		$bMult = false;

		if(is_array($fileId))
		{
			$fileId = implode(',', $fileId);
			$bMult = true;
		}

		if($fileId <> '')
		{
			$files = \CFile::GetList(array(), array('@ID' => $fileId));
			while($file = $files->Fetch())
			{
				if($resizeParam !== false)
				{
					$resizeResult = \CFile::ResizeImageGet($file["ID"], $resizeParam, BX_RESIZE_IMAGE_PROPORTIONAL_ALT, false, false, false);
					$fileSrc[$file['ID']] = \CHTTP::URN2URI($resizeResult['src']);
				}
				else
				{
					$fileSrc[$file['ID']] = \CHTTP::URN2URI(\CFile::GetFileSrc($file));
				}
			}
		}

		return $bMult ? $fileSrc : $fileSrc[$fileId];
	}

	protected static function processBatchElement($query, $arResult, $keysCache = '')
	{
		$regexp = "/\\$(".$keysCache.")([^\s]*)/i";

		if(preg_match_all($regexp, $query, $arMatches, PREG_SET_ORDER))
		{
			foreach($arMatches as $arMatch)
			{
				$path = $arMatch[2];
				if(preg_match_all("/\\[([^\\]]+)\\]/", $path, $arPath))
				{
					$r = $arResult[$arMatch[1]];

					while(count($arPath[1]) > 0)
					{
						$key = array_shift($arPath[1]);
						if(isset($r[$key]))
						{
							$r = $r[$key];
						}
						else
						{
							break;
						}
					}
					if($arMatch[0] === $query)
					{
						$query = $r;
					}
					else
					{
						if (is_numeric($r))
						{
							$r = (string)$r;
						}

						if (!is_string($r))
						{
							continue;
						}
						$query = str_replace($arMatch[0], $r, $query);
					}
				}
			}
		}

		return $query;
	}

	protected static function processBatchStructure($queryParams, $arResult, $keysCache = null)
	{
		$resultQueryParams = array();

		if(is_array($queryParams))
		{
			foreach($queryParams as $key => $param)
			{
				if($keysCache === null)
				{
					$keysCache = implode('|', array_keys($arResult));
				}

				$newKey = self::processBatchElement($key, $arResult);
				if(is_array($param))
				{
					$resultQueryParams[$newKey] = self::processBatchStructure($param, $arResult, $keysCache);
				}
				else
				{
					$resultQueryParams[$newKey] = self::processBatchElement($param, $arResult, $keysCache);
				}
			}
		}

		return $resultQueryParams;
	}

	public static function ParseBatchQuery($query, $arResult)
	{
		$resultQueryParams = array();

		if($query)
		{
			$queryParams = array();
			parse_str($query, $queryParams);

			$queryParams = \Bitrix\Main\Text\Encoding::convertEncoding($queryParams, 'utf-8', LANG_CHARSET);

			$resultQueryParams = self::processBatchStructure($queryParams, $arResult);
		}

		return $resultQueryParams;
	}

	/** @deprecated */
	public static function getAuthForEvent($appId, $userId, array $additionalData = array())
	{
		return \Bitrix\Rest\Event\Sender::getAuth($appId, $userId, $additionalData, \Bitrix\Rest\Event\Sender::getDefaultEventParams());
	}

	/**
	 * @deprecated
	 *
	 * use \Bitrix\Rest\OAuth\Auth::get
	 */
	public static function getAuth($appId, $appSecret, $scope, $additionalParams, $user_id = 0)
	{
		global $USER;

		if(CModule::IncludeModule('oauth'))
		{
			if(is_array($scope))
			{
				$scope = implode(',', $scope);
			}

			$oauth = new \Bitrix\OAuth\Client\Application();
			$authParams = $oauth->getAuthorizeParamsInternal($appId, COAuthConstants::AUTH_RESPONSE_TYPE_AUTH_CODE, '', '', $scope, array(), $user_id > 0 ? $user_id : $USER->GetID());

			if(is_array($authParams) && isset($authParams[COAuthConstants::AUTH_RESPONSE_TYPE_AUTH_CODE]))
			{
				$res = $oauth->grantAccessTokenInternal($appId, COAuthConstants::GRANT_TYPE_AUTH_CODE, '', $authParams[COAuthConstants::AUTH_RESPONSE_TYPE_AUTH_CODE], $scope, $appSecret, '', $additionalParams, $user_id > 0 ? $user_id : $USER->GetID());

				return $res;
			}
		}

		return false;
	}

	public static function checkAuth($query, $scope, &$res)
	{
		// compatibility fix: other modules use checkAuth instead of /rest/download
		if(!is_array($query))
		{
			$query = array('auth' => $query);
		}

		foreach(GetModuleEvents('rest', 'OnRestCheckAuth', true) as $eventHandler)
		{
			$eventResult = ExecuteModuleEventEx($eventHandler, array($query, $scope, &$res));
			if($eventResult !== null)
			{
				return $eventResult;
			}
		}

		$res = array(
			"error" => "NO_AUTH_FOUND",
			"error_description" => "Wrong authorization data",
		);

		return false;
	}

	public static function makeAuth($res, $application_id = null)
	{
		global $USER;

		if($res['user_id'] > 0)
		{
			$dbRes = CUser::GetByID($res['user_id']);
			$userInfo = $dbRes->fetch();

			if($userInfo && $userInfo['ACTIVE'] === 'Y' && $USER->Authorize($res['user_id'], false, false, $application_id))
			{
				setSessionExpired(true);
				return true;
			}
		}
		elseif($res['user_id'] === 0)
		{
			setSessionExpired(true);
			return true;
		}

		return false;
	}

	public static function checkAppAccess($appId, $appInfo = null)
	{
		global $USER;

		$hasAccess = \CRestUtil::isAdmin();
		if(!$hasAccess)
		{
			if($appInfo === null)
			{
				$appInfo = \Bitrix\Rest\AppTable::getByClientId($appId);
			}

			if($appInfo)
			{
				if(!empty($appInfo["ACCESS"]))
				{
					$rights = explode(",", $appInfo["ACCESS"]);
					$hasAccess = $USER->CanAccess($rights);
				}
				else
				{
					$hasAccess = true;
				}
			}
		}

		return $hasAccess;
	}

	public static function updateAppStatus(array $tokenInfo)
	{
		if(array_key_exists('status', $tokenInfo) && array_key_exists('client_id', $tokenInfo))
		{
			$appInfo = \Bitrix\Rest\AppTable::getByClientId($tokenInfo['client_id']);
			if($appInfo)
			{
				$dateFinish = $appInfo['DATE_FINISH'] ? $appInfo['DATE_FINISH']->getTimestamp() : '';

				if($tokenInfo['status'] !== $appInfo['STATUS'] || $tokenInfo['date_finish'] != $dateFinish)
				{
					\Bitrix\Rest\AppTable::update($appInfo['ID'], array(
						'STATUS' => $tokenInfo['status'],
						'DATE_FINISH' => $tokenInfo['date_finish'] ? \Bitrix\Main\Type\DateTime::createFromTimestamp($tokenInfo['date_finish']) : '',
					));
				}
			}
		}
	}

	public static function saveFile($fileContent, $fileName = "")
	{
		if(is_array($fileContent))
		{
			list($fileName, $fileContent) = array_values($fileContent);
		}

		if($fileContent <> '' && $fileContent !== 'false') // let it be >0
		{
			$fileContent = base64_decode($fileContent);
			if($fileContent !== false && $fileContent <> '')
			{
				if($fileName == '')
				{
					$fileName = Security\Random::getString(32);
				}
				else
				{
					$fileName = \Bitrix\Main\Text\Encoding::convertEncoding($fileName, LANG_CHARSET, 'utf-8');
				}

				$fileName = CTempFile::GetFileName($fileName);

				if(CheckDirPath($fileName))
				{
					file_put_contents($fileName, $fileContent);
					return CFile::MakeFileArray($fileName);
				}
			}
			else
			{
				return null; // wrong file content
			}
		}

		return false;
	}

	public static function CleanApp($appId, $bClean)
	{
		$arFields = array(
			'APP_ID' => $appId,
			'CLEAN' => $bClean
		);

		foreach (GetModuleEvents("rest", "OnRestAppDelete", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($arFields));
		}

		\Bitrix\Rest\EventTable::deleteByApp($appId);
		\Bitrix\Rest\PlacementTable::deleteByApp($appId);

		if($bClean)
		{
			$dbRes = \Bitrix\Rest\AppTable::getById($appId);
			$arApp = $dbRes->fetch();
			if($arApp)
			{
				// delete app settings
				COption::RemoveOption("rest", "options_".$arApp['CLIENT_ID']);
				CUserOptions::DeleteOption("app_options", "params_".$arApp['CLIENT_ID']."_".$arApp['VERSION']);
				// delete app user settings
				CUserOptions::DeleteOption("app_options", "options_".$arApp['CLIENT_ID'], array());

				// clean app iblocks
				CBitrixRestEntity::Clean($arApp['CLIENT_ID']);
			}
		}
	}

	/**
	 * Simple app installation without checks.
	 *
	 * @param string $appCode Application code.
	 *
	 * @return boolean
	 */
	public static function InstallApp($code)
	{
		$result = false;

		if(!\Bitrix\Rest\OAuthService::getEngine()->isRegistered())
		{
			try
			{
				\Bitrix\Rest\OAuthService::register();
				\Bitrix\Rest\OAuthService::getEngine()->getClient()->getApplicationList();
			}
			catch(\Bitrix\Main\SystemException $e)
			{
				$result = array('error' => $e->getCode(), 'error_description' => $e->getMessage());
			}
		}

		if(\Bitrix\Rest\OAuthService::getEngine()->isRegistered())
		{
			$appDetailInfo = \Bitrix\Rest\Marketplace\Client::getInstall($code);

			if($appDetailInfo)
			{
				$appDetailInfo = $appDetailInfo['ITEMS'];
			}

			if($appDetailInfo)
			{
				$queryFields = array(
					'CLIENT_ID' => $appDetailInfo['APP_CODE'],
					'VERSION' => $appDetailInfo['VER'],
					'BY_SUBSCRIPTION' => $appDetailInfo['BY_SUBSCRIPTION'] === 'Y' ? 'Y' : 'N',
				);

				$installResult = \Bitrix\Rest\OAuthService::getEngine()
					->getClient()
					->installApplication($queryFields);

				if($installResult['result'])
				{
					$appFields = array(
						'CLIENT_ID' => $installResult['result']['client_id'],
						'CODE' => $appDetailInfo['CODE'],
						'ACTIVE' => \Bitrix\Rest\AppTable::ACTIVE,
						'INSTALLED' => !empty($appDetailInfo['INSTALL_URL'])
							? \Bitrix\Rest\AppTable::NOT_INSTALLED
							: \Bitrix\Rest\AppTable::INSTALLED,
						'URL' => $appDetailInfo['URL'],
						'URL_DEMO' => $appDetailInfo['DEMO_URL'],
						'URL_INSTALL' => $appDetailInfo['INSTALL_URL'],
						'VERSION' => $installResult['result']['version'],
						'SCOPE' => implode(',', $installResult['result']['scope']),
						'STATUS' => $installResult['result']['status'],
						'SHARED_KEY' => $appDetailInfo['SHARED_KEY'],
						'CLIENT_SECRET' => '',
						'APP_NAME' => $appDetailInfo['NAME'],
						'MOBILE' => $appDetailInfo['BXMOBILE'] == 'Y' ? \Bitrix\Rest\AppTable::ACTIVE : \Bitrix\Rest\AppTable::INACTIVE,
					);

					if(
						$appFields['STATUS'] === \Bitrix\Rest\AppTable::STATUS_TRIAL
						|| $appFields['STATUS'] === \Bitrix\Rest\AppTable::STATUS_PAID
					)
					{
						$appFields['DATE_FINISH'] = \Bitrix\Main\Type\DateTime::createFromTimestamp($installResult['result']['date_finish']);
					}
					else
					{
						$appFields['DATE_FINISH'] = '';
					}

					$existingApp = \Bitrix\Rest\AppTable::getByClientId($appFields['CLIENT_ID']);

					if($existingApp)
					{
						$addResult = \Bitrix\Rest\AppTable::update($existingApp['ID'], $appFields);
						\Bitrix\Rest\AppLangTable::deleteByApp($existingApp['ID']);
					}
					else
					{
						$addResult = \Bitrix\Rest\AppTable::add($appFields);
					}

					if($addResult->isSuccess())
					{
						$appId = $addResult->getId();
						if(is_array($appDetailInfo['MENU_TITLE']))
						{
							foreach($appDetailInfo['MENU_TITLE'] as $lang => $langName)
							{
								\Bitrix\Rest\AppLangTable::add(array(
									'APP_ID' => $appId,
									'LANGUAGE_ID' => $lang,
									'MENU_NAME' => $langName
								));
							}
						}

						if($appDetailInfo["OPEN_API"] === "Y" && !empty($appFields["URL_INSTALL"]))
						{
							// checkCallback is already called inside checkFields
							$result = \Bitrix\Rest\EventTable::add(array(
								"APP_ID" => $appId,
								"EVENT_NAME" => "ONAPPINSTALL",
								"EVENT_HANDLER" => $appFields["URL_INSTALL"],
							));
							if($result->isSuccess())
							{
								\Bitrix\Rest\Event\Sender::bind('rest', 'OnRestAppInstall');
							}
						}

						\Bitrix\Rest\AppTable::install($appId);

						$result = true;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @deprecated
	 *
	 * use \Bitrix\Rest\AppTable::update
	 */
	public static function UpdateApp($appId, $oldVersion)
	{
		$arApp = CBitrix24App::GetByID($appId);

		$arFields = array(
			'APP_ID' => $appId,
			'VERSION' => $arApp['VERSION'],
			'PREVIOUS_VERSION' => $oldVersion,
		);

		foreach (GetModuleEvents("rest", "OnRestAppUpdate", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($arFields));
		}

		\Bitrix\Rest\EventTable::deleteAppInstaller($appId);

		CUserOptions::DeleteOption("app_options", "params_".$arApp['APP_ID']."_".$arApp['VERSION']);
	}

	/**
	 * @deprecated
	 */
	public static function getScopeList(array $description = null)
	{
		return \Bitrix\Rest\Engine\ScopeManager::getInstance()->listScope();
	}

	public static function getEventList(array $description = null)
	{
		if($description == null)
		{
			$provider = new \CRestProvider();
			$description = $provider->getDescription();
		}

		$eventList = array();
		foreach($description as $scope => $scopeMethods)
		{
			if(
				array_key_exists(\CRestUtil::EVENTS, $scopeMethods)
				&& is_array($scopeMethods[\CRestUtil::EVENTS])
			)
			{
				$eventList[$scope] = array_keys($scopeMethods[\CRestUtil::EVENTS]);
			}
		}

		return $eventList;
	}

	public static function getApplicationToken(array $application)
	{
		if(!empty($application['APPLICATION_TOKEN']))
		{
			return $application['APPLICATION_TOKEN'];
		}
		else
		{
			$secret = array_key_exists("APP_SECRET_ID", $application) ? $application["APP_SECRET_ID"] : $application["CLIENT_SECRET"];
			return md5(\CRestUtil::getMemberId()."|".$application["ID"]."|".$secret."|".$application["SHARED_KEY"]);
		}
	}

	/**
	 * Generates link to file download
	 *
	 * @param array|string $query Params, which will be transferred to download handler
	 * @param CRestServer $server REST Server object
	 *
	 * @return string Absolute file download URL.
	 *
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getDownloadUrl($query, \CRestServer $server)
	{
		return static::getSpecialUrl(static::METHOD_DOWNLOAD, $query, $server);
	}

	public static function getLanguage()
	{
		$languageId = '';

		$siteIterator = \Bitrix\Main\SiteTable::getList(array(
			'select' => array('LANGUAGE_ID'),
			'filter' => array('=DEF' => 'Y', '=ACTIVE' => 'Y')
		));
		if($site = $siteIterator->fetch())
		{
			$languageId = (string)$site['LANGUAGE_ID'];
		}

		if($languageId == '')
		{
			if(\Bitrix\Main\Loader::includeModule('bitrix24'))
			{
				$languageId = \CBitrix24::getLicensePrefix();
			}
			else
			{
				$languageId = LANGUAGE_ID;
			}
		}

		if($languageId == '')
		{
			$languageId = 'en';
		}

		return $languageId;
	}

	/**
	 * Generates link to file upload
	 *
	 * @param array|string $query Params, which will be transferred to upload handler
	 * @param CRestServer $server REST Server object
	 *
	 * @return string Absolute file download URL.
	 *
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getUploadUrl($query, \CRestServer $server)
	{
		return static::getSpecialUrl(static::METHOD_UPLOAD, $query, $server);
	}

	public static function getSpecialUrl($method, $query, \CRestServer $server)
	{
		if(is_array($query))
		{
			$query = http_build_query($query);
		}

		$query = base64_encode($query."&_=".RandString(32));

		$scope = $server->getScope();
		if($scope === static::GLOBAL_SCOPE)
		{
			$scope = '';
		}

		$signature = $server->getTokenCheckSignature(ToLower($method), $query);

		$token = $scope
			.static::TOKEN_DELIMITER.$query
			.static::TOKEN_DELIMITER.$signature;


		$authData = $server->getAuthData();

		if($authData['password_id'])
		{
			$auth = $server->getAuth();

			return static::getWebhookEndpoint(
				$auth['ap'],
				$auth['aplogin'],
				$method
			)."?".http_build_query(array(
				'token' => $token,
			));
		}
		else
		{
			$urlParam = array_merge(
				$server->getAuth(),
				array(
					'token' => $token,
				)
			);

			return static::getEndpoint().$method.".".$server->getTransport()."?".http_build_query($urlParam);
		}
	}

	public static function getWebhookEndpoint($ap, $userId, $method = '')
	{
		return static::getEndpoint().urlencode($userId).'/'.urlencode($ap).'/'.($method === '' ? '' : urlencode($method).'/');
	}

	public static function getEndpoint()
	{
		return \CHTTP::URN2URI(\Bitrix\Main\Config\Option::get('rest', 'rest_server_path', '/rest').'/');
	}

	public static function getAdministratorIdList()
	{
		$adminList = array();

		$dbAdminList = \CGroup::GetGroupUserEx(1);
		while($admin = $dbAdminList->fetch())
		{
			$adminList[] = $admin["USER_ID"];
		}

		return $adminList;
	}

	public static function getApplicationPage($id, $type = 'ID', $appInfo = null)
	{
		if($appInfo === null)
		{
			$appInfo = \Bitrix\Rest\AppTable::getByClientId($id);
		}

		if($type !== 'ID' && $type !== 'CODE' && $type !== 'CLIENT_ID')
		{
			$type = 'ID';
		}

		if(
			empty($appInfo['MENU_NAME'])
			&& empty($appInfo['MENU_NAME_DEFAULT'])
			&& empty($appInfo['MENU_NAME_LICENSE'])
			|| $appInfo['ACTIVE'] === \Bitrix\Rest\AppTable::INACTIVE
			|| (isset($appInfo['TYPE']) && $appInfo['TYPE'] === \Bitrix\Rest\AppTable::TYPE_CONFIGURATION)
		)
		{
			$url = \Bitrix\Rest\Marketplace\Url::getApplicationDetailUrl(urlencode($appInfo['CODE']));
		}
		elseif($appInfo['CODE'] === static::BITRIX_1C_APP_CODE)
		{
			$url = SITE_DIR.'onec/';
		}
		else
		{
			$url = \Bitrix\Rest\Marketplace\Url::getApplicationUrl(urlencode($appInfo[$type]));
		}
		return $url;
	}

	public static function isSlider()
	{
		return (isset($_REQUEST['IFRAME']) && $_REQUEST['IFRAME'] === 'Y' && $_REQUEST['IFRAME_TYPE'] == 'SIDE_SLIDER');
	}

}
