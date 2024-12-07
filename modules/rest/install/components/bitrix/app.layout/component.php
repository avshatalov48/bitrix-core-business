<?

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Rest\Engine\Access;
use Bitrix\Rest\PlacementTable;

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

if(isset($arParams["APP_VIEW"]) && $arParams["APP_VIEW"])
{
	$appInfo = \Bitrix\Rest\AppTable::getByClientId($arParams["APP_VIEW"]);
	if(
		$appInfo
		&& $appInfo['ACTIVE'] === \Bitrix\Rest\AppTable::ACTIVE
		&& $appInfo['INSTALLED'] === \Bitrix\Rest\AppTable::INSTALLED
	)
	{
		$appId =  $appInfo['ID'];
		$res = PlacementTable::getList(
			[
				'filter' => [
					'PLACEMENT' => \CRestUtil::PLACEMENT_APP_URI,
					'APP_ID' => $appInfo['ID']
				],
			]
		);
		if($placement = $res->fetch())
		{
			$arParams['ID'] = $placement['APP_ID'];
			$arParams['PLACEMENT'] = $placement['PLACEMENT'];
			$arParams['PLACEMENT_ID'] = $placement['ID'];
			if($params = $request->getQuery("params"))
			{
				$arParams['PLACEMENT_OPTIONS'] = $params;
				$arParams['~PLACEMENT_OPTIONS'] = $params;
			}

		}
	}
}

if ($arParams["IFRAME"] === true && ($componentParams = $this->request->getPost('PARAMS')) && isset($componentParams['params']))
{
	$arParams = array_merge($arParams, $componentParams['params']);
	if(isset($arParams['PLACEMENT_OPTIONS']) && !isset($arParams['~PLACEMENT_OPTIONS']))
	{
		$arParams['~PLACEMENT_OPTIONS'] = $arParams['PLACEMENT_OPTIONS'];
	}
	$arParams["LAZYLOAD"] = true;
}

$arParams['ID'] = isset($arParams['ID']) ? intval($arParams['ID']) : 0;
$appCode = '';
if($arParams['ID'] <= 0)
{
	$arDefaultUrlTemplates404 = array(
		"application" => "#id/",
	);

	$arDefaultVariableAliases404 = array();

	$arDefaultVariableAliases = array();

	$arComponentVariables = array("id");

	$SEF_FOLDER = "";
	$arUrlTemplates = array();

	if($arParams["SEF_MODE"] == "Y")
	{
		$arVariables = array();

		$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
		$arParams["VARIABLE_ALIASES"] = $arParams["VARIABLE_ALIASES"] ?? null;
		$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

		$componentPage = CComponentEngine::ParseComponentPath(
			$arParams["SEF_FOLDER"],
			$arUrlTemplates,
			$arVariables
		);

		if(!$componentPage)
		{
			$componentPage = 'application';
		}

		CComponentEngine::InitComponentVariables(
			$componentPage,
			$arComponentVariables,
			$arVariableAliases,
			$arVariables
		);

		$SEF_FOLDER = $arParams["SEF_FOLDER"];
	}
	else
	{
		$arVariables = array();

		$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
		CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

		$componentPage = "application";
	}

	if (
		isset($arVariables['placement_id'])
		&& (int)$arVariables['placement_id'] > 0
		&& (string)(int)$arVariables['placement_id'] === (string)$arVariables['placement_id']
	)
	{
		$res = PlacementTable::getById((int)$arVariables['placement_id']);
		if ($placement = $res->fetch())
		{
			$arParams['ID'] = $placement['APP_ID'];
			$arParams['CODE'] = $placement['APP_ID'];
			$arParams['PLACEMENT'] = $placement['PLACEMENT'];
			$arParams['PLACEMENT_ID'] = $placement['ID'];
		}
	}
	elseif ((string)(int)$arVariables['id'] === (string)$arVariables['id'])
	{
		$arParams['CODE'] = (int)$arVariables['id'];
	}
	else
	{
		$arParams['CODE'] = trim($arVariables['id']);
		$appCode = $arParams['CODE'];
	}
}
else
{
	$arParams['CODE'] = intval($arParams['ID']);
}

$arParams['POPUP'] = isset($arParams['POPUP']) ? $arParams['POPUP'] : false;
$arParams["IS_SLIDER"] =  isset($arParams['IS_SLIDER']) && $arParams['IS_SLIDER'] == 'Y';

if($arParams['CODE'] == '')
{
	$componentPage = 'error';
	$arResult['ERROR_MESSAGE'] = GetMessage('REST_AL_ERROR_APP_NOT_FOUND');
	$this->IncludeComponentTemplate($componentPage);
	return;
}

if(!\Bitrix\Main\Loader::includeModule("rest"))
{
	return;
}

$arParams['INITIALIZE'] = (isset($arParams['INITIALIZE']) && $arParams['INITIALIZE'] === 'N')  ? 'N' : 'Y';

$arParams['DETAIL_URL'] = isset($arParams['DETAIL_URL']) ? trim($arParams['DETAIL_URL']) : '/marketplace/detail/#code#/?from=app_layout';
$arParams['PLACEMENT'] = isset($arParams['PLACEMENT']) ? mb_strtoupper($arParams['PLACEMENT']) : PlacementTable::PLACEMENT_DEFAULT;
$arParams['PLACEMENT_OPTIONS'] = isset($arParams['PLACEMENT_OPTIONS']) ? $arParams['PLACEMENT_OPTIONS'] : '';
$arResult['SUBSCRIPTION_FINISH'] = \Bitrix\Rest\Marketplace\Client::getSubscriptionFinalDate();
if ($arParams['PLACEMENT'] === PlacementTable::PLACEMENT_DEFAULT && empty($arParams['PLACEMENT_OPTIONS']))
{
	$requestOptions = $_GET;
	if($arParams['POPUP'])
	{
		if (
			isset($_REQUEST['param'])
			&& is_array($_REQUEST['param'])
		)
		{
			$requestOptions = array_merge($requestOptions, $_REQUEST['param']);
		}
		$arParams['PARENT_SID'] = $_REQUEST['parentsid'] ?? null;
	}

	$deniedParam = \Bitrix\Main\HttpRequest::getSystemParameters();
	$deniedParam[] = '_r';

	$arParams['PLACEMENT_OPTIONS'] = array_diff_key($requestOptions, array_flip($deniedParam));
	$arParams['~PLACEMENT_OPTIONS'] = $arParams['PLACEMENT_OPTIONS'];
}

$arApp = \Bitrix\Rest\AppTable::getByClientId($arParams['CODE']);

if(
	is_array($arApp) && $arApp['ACTIVE'] == \Bitrix\Rest\AppTable::ACTIVE
	&& !empty($arApp['CLIENT_ID'])
)
{
	$arParams['ID'] = $arApp['ID'];

	$bHasAccess = \CRestUtil::checkAppAccess($arApp['ID']);

	$arResult['APP_NAME'] = $arApp['MENU_NAME'];
	if($arResult['APP_NAME'] == '')
	{
		$arResult['APP_NAME'] = $arApp['MENU_NAME_DEFAULT'];
	}
	if($arResult['APP_NAME'] == '')
	{
		$arResult['APP_NAME'] = $arApp['MENU_NAME_LICENSE'];
	}

	$placementHandlerInfo = array();
	if ($arParams['PLACEMENT'] !== PlacementTable::PLACEMENT_DEFAULT)
	{
		$filter = array();
		if(isset($arParams['PLACEMENT_ID']))
		{
			$filter['=ID'] = $arParams['PLACEMENT_ID'];
		}

		$filter['=APP_ID'] = $arApp['ID'];
		$filter['=PLACEMENT'] = $arParams['PLACEMENT'];
		$filter['=USER_ID'] = [
			PlacementTable::DEFAULT_USER_ID_VALUE,
			$USER->GetID(),
		];

		$res = PlacementTable::getList(
			[
				'filter' => $filter,
				'select' => [
					'ID',
					'PLACEMENT_HANDLER',
					'OPTIONS',
					'LANG_ALL',
				],
			]
		);
		if ($res->getSelectedRowsCount() > 0)
		{
			foreach ($res->fetchCollection() as $handler)
			{
				$placementHandlerInfo = [
					'ID' => $handler->getId(),
					'OPTIONS' => $handler->getOptions(),
					'PLACEMENT_HANDLER' => $handler->getPlacementHandler(),
					'LANG_ALL' => [],
				];
				if (!is_null($handler->getLangAll()))
				{
					foreach ($handler->getLangAll() as $lang)
					{
						$placementHandlerInfo['LANG_ALL'][$lang->getLanguageId()] = [
							'TITLE' => $lang->getTitle(),
							'DESCRIPTION' => $lang->getDescription(),
							'GROUP_NAME' => $lang->getGroupName(),
						];
					}
				}
				$placementHandlerInfo = \Bitrix\Rest\Lang::mergeFromLangAll($placementHandlerInfo);

				$arResult['PRESET_OPTIONS'] = $placementHandlerInfo['OPTIONS'];
				if (trim($placementHandlerInfo['TITLE']) !== '')
				{
					$arResult['APP_NAME'] = $placementHandlerInfo['TITLE'];
				}
			}
		}
		else
		{
			$componentPage = 'error';
			$arResult['ERROR_MESSAGE'] = GetMessage('REST_AL_ERROR_APP_PLACEMENT_NOT_INSTALLED');
			$this->IncludeComponentTemplate($componentPage);
			return;
		}

		if (!trim($arResult['APP_NAME']))
		{
			if (trim($arApp['APP_NAME']) !== '')
			{
				$arResult['APP_NAME'] = $arApp['APP_NAME'];
			}
			elseif ((int) $placementHandlerInfo['ID'] > 0)
			{
				$arResult['APP_NAME'] = PlacementTable::getDefaultTitle((int) $placementHandlerInfo['ID']);
			}
		}
	}
	elseif(isset($arParams['LAZYLOAD']) || $arResult['APP_NAME'] === '')
	{
		$arResult['APP_NAME'] = empty($arResult['APP_NAME']) ? $arApp['APP_NAME'] : $arResult['APP_NAME'];
	}

	if (
		(
			$bHasAccess
			|| $arParams['PLACEMENT'] === \Bitrix\Rest\Api\UserFieldType::PLACEMENT_UF_TYPE
		)
		&& $arResult['APP_NAME'] <> ''
	)
	{
		$arResult['ID'] = $arApp['ID'];
		$arResult['APP_ID'] = $arApp['CLIENT_ID'];
		$arResult['APP_VERSION'] = $arApp['VERSION'];
		$arResult['APP_INSTALLED'] = $arApp['INSTALLED'] == \Bitrix\Rest\AppTable::INSTALLED;
		$arResult['APP_CODE'] = $arApp['CODE'];

		// common application options set via setAppOption
		$arResult['APP_OPTIONS'] = COption::GetOptionString("rest", "options_".$arResult['APP_ID'], "");
		if($arResult['APP_OPTIONS'] <> '')
			$arResult['APP_OPTIONS'] = unserialize($arResult['APP_OPTIONS'], ['allowed_classes' => false]);
		else
			$arResult['APP_OPTIONS'] = array();

	// user application options set via setUserOption
		$arResult['USER_OPTIONS'] = CUserOptions::GetOption("app_options", "options_".$arResult['APP_ID'], array());

	// additional user application params set by app environment
		$arAppParams = CUserOptions::GetOption("app_options", "params_".$arResult['APP_ID']."_".$arResult['APP_VERSION'], array());

	// this is a first run for the application and the current user
		$arResult['FIRST_RUN'] = !array_key_exists('install', $arAppParams) || !$arAppParams['install'];

		$arResult['AUTH'] = null;
		if($bHasAccess)
		{
			$arResult['AUTH'] = \Bitrix\Rest\Application::getAuthProvider()->get(
				$arApp['CLIENT_ID'],
				$arApp['SCOPE'],
				array(),
				$USER->GetID()
			);
		}
		elseif($arParams['PLACEMENT'] === \Bitrix\Rest\Api\UserFieldType::PLACEMENT_UF_TYPE)
		{
			$arResult['AUTH'] = array();
		}

		if (!is_array($arResult['AUTH']) || !$arResult['AUTH']['access_token'])
		{
			if ($arResult['AUTH']['error'])
			{
				if (
					$arResult['AUTH']['error'] == 'ERROR_OAUTH'
					&& $arResult['AUTH']['error_description'] == 'Application not installed'
				)
				{
					$queryFields = array(
						'CLIENT_ID' => $arApp['CLIENT_ID'],
						'VERSION' => $arApp['VERSION'],
					);

					$installResult = \Bitrix\Rest\OAuthService::getEngine()
						->getClient()
						->installApplication($queryFields);

					if ($installResult['result'])
					{
						$arResult['AUTH'] = \Bitrix\Rest\Application::getAuthProvider()->get(
							$arApp['CLIENT_ID'],
							$arApp['SCOPE'],
							array(),
							$USER->GetID()
						);
					}
				}
				elseif (
					$arResult['AUTH']['error'] == 'ERROR_OAUTH'
					&& $arResult['AUTH']['error_description'] == 'Subscription has been ended'
				)
				{
					if (\Bitrix\Main\Loader::includeModule('ui'))
					{
						$code = Access::getHelperCode(
							Access::ACTION_OPEN,
							Access::ENTITY_TYPE_APP,
							$arResult['APP_ID']
						);
						if ($code !== '')
						{
							$arResult['HELPER_DATA']['TEMPLATE_URL'] = \Bitrix\UI\InfoHelper::getUrl();
							$arResult['HELPER_DATA']['URL'] = str_replace(
								'/code/',
								'/' . $code . '/',
								$arResult['HELPER_DATA']['TEMPLATE_URL']
							);
						}
					}
					$arResult['PAYMENT_TYPE'] = \Bitrix\Rest\AppTable::STATUS_SUBSCRIPTION;
					$componentPage = 'payment';
					$this->IncludeComponentTemplate($componentPage);
					return;
				}

				if ($arResult['AUTH']['error'])
				{
					if ($arResult['AUTH']['error'] !== "PAYMENT_REQUIRED")
					{
						$componentPage = 'error';
						$arResult['ERROR_MESSAGE'] = $arResult['AUTH']['error'].($arResult['AUTH']['error_description'] ? ': '.$arResult['AUTH']['error_description'] : '');
						$this->IncludeComponentTemplate($componentPage);
						return;
					}
					else
					{
						\Bitrix\Rest\AppTable::updateAppStatusInfo();
						$arApp = \Bitrix\Rest\AppTable::getByClientId($arParams['ID']);
					}
				}
			}
			else
			{
				$arResult['AUTH'] = \Bitrix\Rest\Application::getAuthProvider()->get(
					$arApp['CLIENT_ID'],
					$arApp['SCOPE'],
					array(),
					$USER->GetID()
				);

				if (!is_array($arResult['AUTH']) || !$arResult['AUTH']['access_token'])
				{
					if (isset($arResult['AUTH']['error']))
					{
						$arResult['ERROR_MESSAGE'] = $arResult['AUTH']['error']
							. ($arResult['AUTH']['error_description'] ? ': '
							. $arResult['AUTH']['error_description'] : '');
					}
					else
					{
						$arResult['ERROR_MESSAGE'] = GetMessage('REST_AL_ERROR_APP_GET_OAUTH_TOKEN');
					}

					$componentPage = 'error';
					$this->IncludeComponentTemplate($componentPage);

					return;
				}
			}
		}

		$dateFinish = $arApp['DATE_FINISH'] ? $arApp['DATE_FINISH']->getTimestamp() : '';
		$dateFinishAuth = $arResult['AUTH']['date_finish'] ?? null;
		$authError = $arResult['AUTH']['error'] ?? null;
		if(
			!$authError
			&& (
				$arResult['AUTH']['status'] !== $arApp['STATUS']
				|| $dateFinishAuth != $dateFinish
			)
		)
		{
			$arApp['STATUS'] = $arResult['AUTH']['status'];
			$arApp['DATE_FINISH'] = $arResult['AUTH']['date_finish'] ? \Bitrix\Main\Type\Date::createFromTimestamp($arResult['AUTH']['date_finish']) : '';

			\Bitrix\Rest\AppTable::setSkipRemoteUpdate(true);
			$result = \Bitrix\Rest\AppTable::update($arApp['ID'], array(
				'STATUS' => $arApp['STATUS'],
				'DATE_FINISH' => $arApp['DATE_FINISH'],
			));
			\Bitrix\Rest\AppTable::setSkipRemoteUpdate(false);
			if(
				$result->isSuccess()
				&& $arApp['STATUS'] === \Bitrix\Rest\AppTable::STATUS_PAID
			)
			{
				\Bitrix\Rest\AppTable::callAppPaymentEvent($arApp['ID']);
			}
		}

		$arResult['DETAIL_URL'] = str_replace("#code#", $arApp['CODE'], $arParams['DETAIL_URL']);

		$arResult['APP_STATUS'] = \Bitrix\Rest\AppTable::getAppStatusInfo($arApp, $arResult['DETAIL_URL']);

		$arResult['APP_NEED_REINSTALL'] = $arApp['STATUS'] == \Bitrix\Rest\AppTable::STATUS_PAID && !isset($arApp['SHARED_KEY']);

		$arResult['APP_SID'] = md5(uniqid(rand(), true));

		$arResult['IS_ADMIN'] = \CRestUtil::isAdmin() || \CRestUtil::canInstallApplication($arApp);
		$arResult['REST_PATH'] = \Bitrix\Main\Config\Option::get("rest", "server_path", "/rest");

		if(!is_array($arResult['AUTH']) || $authError)
		{
			$arResult['APP_STATUS']['PAYMENT_ALLOW'] = 'N';
		}

		// additional actions
		if($arResult['APP_STATUS']['PAYMENT_ALLOW'] == 'Y' && isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('access_refresh', 'set_option', 'set_installed')) && check_bitrix_sessid())
		{
			$APPLICATION->RestartBuffer();

			Header('Content-Type: application/json');

			switch($_REQUEST['action'])
			{
	// refresh auth
				case 'access_refresh':
					echo '{"access_token":"'.$arResult["AUTH"]['access_token'].'","refresh_token":"'.$arResult['AUTH']['refresh_token'].'","expires_in":"'.$arResult["AUTH"]['expires_in'].'"}';
				break;

	// set application option value
				case 'set_option':
					if($arResult['IS_ADMIN'])
					{
						if(is_array($_REQUEST['options']))
						{
							foreach($_REQUEST['options'] as $opt)
							{
								$arResult['APP_OPTIONS'][$opt['name']] = $opt['value'];
							}
						}

						\Bitrix\Main\Config\Option::set("rest", "options_".$arResult['APP_ID'], serialize($arResult['APP_OPTIONS']));

						echo \Bitrix\Main\Web\Json::encode($arResult['APP_OPTIONS']);
					}
					else
					{
						echo '{"error":"access_denied"}';
					}

				break;

	// set installation finish flag
				case 'set_installed':
					if($arResult['IS_ADMIN'])
					{
						\Bitrix\Rest\AppTable::setSkipRemoteUpdate(true);
						$updateResult = \Bitrix\Rest\AppTable::update(
							$arParams['ID'],
							array(
								'INSTALLED' => $_REQUEST['v'] == 'N'
									? \Bitrix\Rest\AppTable::NOT_INSTALLED
									: \Bitrix\Rest\AppTable::INSTALLED
							)
						);
						\Bitrix\Rest\AppTable::setSkipRemoteUpdate(false);

						\Bitrix\Rest\AppTable::install($arParams['ID']);

						\Bitrix\Rest\AppLogTable::log($arParams['ID'], \Bitrix\Rest\AppLogTable::ACTION_TYPE_INSTALL);

						echo '{"result":"'.($updateResult->isSuccess()  ? 'true' : 'false').'"}';
					}
					else
					{
						echo '{"result":"false"}';
					}
				break;
			}

			\CMain::FinalActions();
			die();
		}

	// get and parse application URL
		$arResult['APP_URL'] = $arApp['URL'];
		if(!$arResult['APP_INSTALLED'] && $arApp['URL_INSTALL'] <> '')
		{
			if($arResult['IS_ADMIN'])
			{
				$arResult['INSTALL'] = true;
				$arResult['APP_URL'] = $arApp['URL_INSTALL'];
			}
			else
			{
				$componentPage = 'error';
				$arResult['ERROR_MESSAGE'] = GetMessage('REST_AL_ERROR_APP_INSTALL_NOT_FINISH');
				$this->IncludeComponentTemplate($componentPage);
				return;
			}
		}
		elseif($arParams['PLACEMENT'] !== PlacementTable::PLACEMENT_DEFAULT)
		{
			$arResult['APP_URL'] = $placementHandlerInfo['PLACEMENT_HANDLER'];
		}
		elseif(
			(
				$arResult['APP_STATUS']['STATUS'] == \Bitrix\Rest\AppTable::STATUS_DEMO
				|| $arResult['APP_STATUS']['STATUS'] == \Bitrix\Rest\AppTable::STATUS_TRIAL
			)
			&& $arApp['URL_DEMO'] <> ''
		)
		{
			$arResult['APP_URL'] = $arApp['URL_DEMO'];
		}

		if($arResult['APP_URL'] == '')
		{
			return;
		}

		$arResult["APP_STATIC"] = \CRestUtil::isStatic($arResult['APP_URL']);

		$p = parse_url($arResult['APP_URL']);
		$arResult['APP_HOST'] = $p['host'];
		$arResult['APP_PORT'] = $p['port'] ?? null;
		$arResult['APP_PROTO'] = $p['scheme'];

		if($arResult['APP_PORT'])
		{
			$arResult['APP_HOST'] .= ':'.$arResult['APP_PORT'];
		}

		$arResult['CURRENT_HOST_SECURE'] = $request->isHttps();
		$arResult['CURRENT_HOST'] = $request->getHttpHost();

		$serverPort = \Bitrix\Main\Context::getCurrent()->getServer()->getServerPort();
		if($serverPort != 80 && $serverPort != 443)
		{
			$arResult['CURRENT_HOST'] .= ':'.$serverPort;
		}

		$arResult['MEMBER_ID'] = \CRestUtil::getMemberId();

		$updateVersion = intval(\Bitrix\Rest\Marketplace\Client::getAvailableUpdate($arApp["CODE"]));
		if(
			$updateVersion > $arApp['VERSION']
			&& !array_key_exists('skip_update_'.$updateVersion, $arAppParams)
		)
		{
			$arResult['UPDATE_VERSION'] = $updateVersion;
		}

		if($arParams['POPUP'])
		{
			$APPLICATION->RestartBuffer();
			$APPLICATION->ShowAjaxHead();
		}
		elseif(!isset($arParams['SET_TITLE']) || $arParams['SET_TITLE'] !== 'N')
		{
			$APPLICATION->SetTitle(htmlspecialcharsbx($arResult['APP_NAME']));
		}

		CJSCore::Init(array('applayout'));

		if($arResult['APP_STATUS']['PAYMENT_ALLOW'] === 'Y')
		{
			\Bitrix\Rest\UsageStatTable::logPlacement($arResult['APP_ID'], $arParams['PLACEMENT']);
			\Bitrix\Rest\UsageStatTable::finalize();
		}

		$componentPage = '';
		if (
			$arResult['APP_STATUS']['PAYMENT_EXPIRED'] === 'Y'
			&& (
				$arResult['APP_STATUS']['STATUS'] === \Bitrix\Rest\AppTable::STATUS_TRIAL
				|| $arResult['APP_STATUS']['STATUS'] === \Bitrix\Rest\AppTable::STATUS_PAID
			)
			&& \Bitrix\Rest\Marketplace\Client::isSubscriptionAvailable()
		)
		{
			$componentPage = 'payment';
		}
		elseif (
			!Access::isAvailable($arApp['CODE'])
			|| (Access::needCheckCount() && !Access::isAvailableCount(Access::ENTITY_TYPE_APP, $arApp['CODE']))
		)
		{
			$arResult['ERROR_MESSAGE'] = GetMessage('REST_AL_ERROR_APP_ACCESS_DENIED');
			$componentPage = 'error';
			if (\Bitrix\Main\Loader::includeModule('ui'))
			{
				$code = Access::getHelperCode(Access::ACTION_OPEN, Access::ENTITY_TYPE_APP, $arResult['APP_ID']);
				if ($code !== '')
				{
					$arResult['HELPER_DATA']['TEMPLATE_URL'] = \Bitrix\UI\InfoHelper::getUrl();
					$arResult['HELPER_DATA']['URL'] = str_replace(
						'/code/',
						'/' . $code . '/',
						$arResult['HELPER_DATA']['TEMPLATE_URL']
					);
				}
			}
		}
		elseif (
			$arResult['APP_STATUS']['PAYMENT_EXPIRED'] === 'Y'
			||
			(
				$arResult['APP_STATUS']['STATUS'] === \Bitrix\Rest\AppTable::STATUS_SUBSCRIPTION
				&& !\Bitrix\Rest\Marketplace\Client::isSubscriptionAvailable()
			)
		)
		{
			if (\Bitrix\Main\Loader::includeModule('ui'))
			{
				$code = Access::getHelperCode(
					Access::ACTION_OPEN,
					Access::ENTITY_TYPE_APP,
					$arResult['APP_ID']
				);
				if ($code !== '')
				{
					$arResult['HELPER_DATA']['TEMPLATE_URL'] = \Bitrix\UI\InfoHelper::getUrl();
					$arResult['HELPER_DATA']['URL'] = str_replace(
						'/code/',
						'/' . $code . '/',
						$arResult['HELPER_DATA']['TEMPLATE_URL']
					);
				}
			}
			$componentPage = 'payment';
		}

		$this->IncludeComponentTemplate($componentPage);

		if($arParams['POPUP'])
		{
			CMain::FinalActions();
			die();
		}

		return $arResult['APP_SID'];
	}
	else
	{
		if(!$bHasAccess)
		{
			$arResult['ERROR_MESSAGE'] = GetMessage('REST_AL_ERROR_APP_NOT_ACCESSIBLE');
		}
		else
		{
			$arResult['ERROR_MESSAGE'] = GetMessage('REST_AL_ERROR_APP_NOT_FOUND_MARKETPLACE');
		}
		$componentPage = 'error';
		$this->IncludeComponentTemplate($componentPage);
	}
}
elseif($appCode <> '')
{
	LocalRedirect(str_replace(
		'#code#',
		urlencode($appCode),
		$arParams['DETAIL_URL']
	));
}
else
{
	$componentPage = 'error';
	$arResult['ERROR_MESSAGE'] = GetMessage('REST_AL_ERROR_APP_NOT_FOUND_MARKETPLACE');
	$this->IncludeComponentTemplate($componentPage);
}

?>
