<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

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

	if(strval(intval($arVariables['id'])) == $arVariables['id'])
	{
		$arParams['CODE'] = intval($arVariables['id']);
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

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$arParams['POPUP'] = isset($arParams['POPUP']) ? $arParams['POPUP'] : false;

if(strlen($arParams['CODE']) <= 0)
{
	ShowError(GetMessage('REST_AL_ERROR_APP_NOT_FOUND'));
	return;
}

if(!\Bitrix\Main\Loader::includeModule("rest"))
{
	return;
}

$arParams['INITIALIZE'] = $arParams['INITIALIZE']  == 'N' ? 'N' : 'Y';

$arParams['DETAIL_URL'] = isset($arParams['DETAIL_URL']) ? trim($arParams['DETAIL_URL']) : '/marketplace/detail/#code#/';
$arParams['PLACEMENT'] = isset($arParams['PLACEMENT']) ? ToUpper($arParams['PLACEMENT']) : \Bitrix\Rest\PlacementTable::PLACEMENT_DEFAULT;
$arParams['PLACEMENT_OPTIONS'] = isset($arParams['PLACEMENT_OPTIONS']) ? $arParams['PLACEMENT_OPTIONS'] : '';

if($arParams['PLACEMENT'] === \Bitrix\Rest\PlacementTable::PLACEMENT_DEFAULT && empty($arParams['PLACEMENT_OPTIONS']))
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
		$arParams['PARENT_SID'] = $_REQUEST['parentsid'];
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
	if(strlen($arResult['APP_NAME']) <= 0)
	{
		$arResult['APP_NAME'] = $arApp['MENU_NAME_DEFAULT'];
	}
	if(strlen($arResult['APP_NAME']) <= 0)
	{
		$arResult['APP_NAME'] = $arApp['MENU_NAME_LICENSE'];
	}

	$placementHandlerInfo = array();
	if($arParams['PLACEMENT'] !== \Bitrix\Rest\PlacementTable::PLACEMENT_DEFAULT)
	{
		$filter = array();
		if(isset($arParams['PLACEMENT_ID']))
		{
			$filter['=ID'] = $arParams['PLACEMENT_ID'];
		}

		$filter['=APP_ID'] = $arApp['ID'];
		$filter['=PLACEMENT'] = $arParams['PLACEMENT'];

		$dbRes = \Bitrix\Rest\PlacementTable::getList(array(
			'filter' => $filter,
			'select' => array('PLACEMENT_HANDLER')
		));
		$placementHandlerInfo = $dbRes->fetch();
		if(!$placementHandlerInfo)
		{
			ShowError(GetMessage('REST_AL_ERROR_APP_PLACEMENT_NOT_INSTALLED'));
			return;
		}

		if(strlen($placementHandlerInfo['TITLE']) > 0)
		{
			$arResult['APP_NAME'] = $placementHandlerInfo['TITLE'];
		}
		elseif(strlen($arResult['APP_NAME']) <= 0)
		{
			$arResult['APP_NAME'] = $arApp['APP_NAME'];
		}
	}
	elseif(isset($arParams['LAZYLOAD']) && strlen($arResult['APP_NAME']) <= 0)
	{
		$arResult['APP_NAME'] = $arApp['APP_NAME'];
	}

	if (
		(
			$bHasAccess
			|| $arParams['PLACEMENT'] === \Bitrix\Rest\Api\UserFieldType::PLACEMENT_UF_TYPE
		)
		&& strlen($arResult['APP_NAME']) > 0)
	{
		$arResult['ID'] = $arApp['ID'];
		$arResult['APP_ID'] = $arApp['CLIENT_ID'];
		$arResult['APP_VERSION'] = $arApp['VERSION'];
		$arResult['APP_INSTALLED'] = $arApp['INSTALLED'] == \Bitrix\Rest\AppTable::INSTALLED;
		$arResult['APP_CODE'] = $arApp['CODE'];

		// common application options set via setAppOption
		$arResult['APP_OPTIONS'] = COption::GetOptionString("rest", "options_".$arResult['APP_ID'], "");
		if(strlen($arResult['APP_OPTIONS']) > 0)
			$arResult['APP_OPTIONS'] = unserialize($arResult['APP_OPTIONS']);
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

		if(!is_array($arResult['AUTH']) || !$arResult['AUTH']['access_token'])
		{
			if($arResult['AUTH']['error'])
			{
				if(
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

					if($installResult['result'])
					{
						$arResult['AUTH'] = \Bitrix\Rest\Application::getAuthProvider()->get(
							$arApp['CLIENT_ID'],
							$arApp['SCOPE'],
							array(),
							$USER->GetID()
						);
					}
				}

				if($arResult['AUTH']['error'])
				{
					if($arResult['AUTH']['error'] !== "PAYMENT_REQUIRED")
					{
						ShowError($arResult['AUTH']['error'].($arResult['AUTH']['error_description'] ? ': '.$arResult['AUTH']['error_description'] : ''));
						return;
					}
					else
					{
						\Bitrix\Rest\AppTable::updateAppStatusInfo();
						$arApp = \Bitrix\Rest\AppTable::getByClientId($arParams['ID']);
					}
				}
			}
		}

		$dateFinish = $arApp['DATE_FINISH'] ? $arApp['DATE_FINISH']->getTimestamp() : '';
		if(
			!$arResult['AUTH']['error']
			&& (
				$arResult['AUTH']['status'] !== $arApp['STATUS']
				|| $arResult['AUTH']['date_finish'] != $dateFinish
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

		if(!is_array($arResult['AUTH']) || $arResult['AUTH']['error'])
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
								$arResult['APP_OPTIONS'][$opt['name']] = \Bitrix\Main\Text\Encoding::convertEncoding($opt['value'], 'utf-8', LANG_CHARSET);
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
		if(!$arResult['APP_INSTALLED'] && strlen($arApp['URL_INSTALL']) > 0)
		{
			if($arResult['IS_ADMIN'])
			{
				$arResult['INSTALL'] = true;
				$arResult['APP_URL'] = $arApp['URL_INSTALL'];
			}
			else
			{
				ShowError(GetMessage('REST_AL_ERROR_APP_NOT_INSTALLED'));
				return;
			}
		}
		elseif($arParams['PLACEMENT'] !== \Bitrix\Rest\PlacementTable::PLACEMENT_DEFAULT)
		{
			$arResult['APP_URL'] = $placementHandlerInfo['PLACEMENT_HANDLER'];
		}
		elseif(
			(
				$arResult['APP_STATUS']['STATUS'] == \Bitrix\Rest\AppTable::STATUS_DEMO
				|| $arResult['APP_STATUS']['STATUS'] == \Bitrix\Rest\AppTable::STATUS_TRIAL
			)
			&& strlen($arApp['URL_DEMO']) > 0
		)
		{
			$arResult['APP_URL'] = $arApp['URL_DEMO'];
		}

		if(strlen($arResult['APP_URL']) <= 0)
		{
			return;
		}

		$arResult["APP_STATIC"] = \CRestUtil::isStatic($arResult['APP_URL']);

		$p = parse_url($arResult['APP_URL']);
		$arResult['APP_HOST'] = $p['host'];
		$arResult['APP_PROTO'] = $p['scheme'];

		if($p['port'])
		{
			$arResult['APP_HOST'] .= ':'.$p['port'];
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
		elseif($arParams['SET_TITLE'] !== 'N')
		{
			$APPLICATION->SetTitle($arResult['APP_NAME']);
		}

		CJSCore::Init(array('applayout'));

		if($arResult['APP_STATUS']['PAYMENT_ALLOW'] === 'Y')
		{
			\Bitrix\Rest\StatTable::logPlacement($arResult['APP_ID'], $arParams['PLACEMENT']);
			\Bitrix\Rest\StatTable::finalize();
		}

		$this->IncludeComponentTemplate();

		if($arParams['POPUP'])
		{
			CMain::FinalActions();
			die();
		}

		return $arResult['APP_SID'];
	}
	else
	{
		ShowError(GetMessage('REST_AL_ERROR_APP_NOT_FOUND'));
	}
}
elseif(strlen($appCode) > 0)
{
	LocalRedirect(str_replace(
		'#code#',
		urlencode($appCode),
		$arParams['DETAIL_URL']
	));
}
else
{
	ShowError(GetMessage('REST_AL_ERROR_APP_NOT_FOUND'));
}

?>