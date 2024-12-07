<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;

class PersonalOrderSection extends CBitrixComponent
{
	public function onPrepareComponentParams($params)
	{
		$params['DISABLE_SOCSERV_AUTH'] = $params['DISABLE_SOCSERV_AUTH'] ?? 'N';
		$params['DISABLE_SOCSERV_AUTH'] = $params['DISABLE_SOCSERV_AUTH'] === 'Y' ? 'Y' : 'N';

		$params['SEF_MODE'] ??= 'Y';
		$params['VARIABLE_ALIASES'] ??= [];
		if (!is_array($params['VARIABLE_ALIASES']))
		{
			$params['VARIABLE_ALIASES'] = [];
		}

		$params['PATH_TO_LIST'] = trim((string)($params['PATH_TO_LIST'] ?? ''));

		$params['SHOW_ACCOUNT_PAGE'] = (string)($params['SHOW_ACCOUNT_PAGE'] ?? 'Y');
		$params['SHOW_ORDER_PAGE'] = (string)($params['SHOW_ORDER_PAGE'] ?? 'Y');
		$params['SHOW_PRIVATE_PAGE'] = (string)($params['SHOW_PRIVATE_PAGE'] ?? 'Y');
		$params['SHOW_PROFILE_PAGE'] = (string)($params['SHOW_PROFILE_PAGE'] ?? 'Y');
		$params['SHOW_SUBSCRIBE_PAGE'] = (string)($params['SHOW_SUBSCRIBE_PAGE'] ?? 'Y');
		$params['SHOW_CONTACT_PAGE'] = (string)($params['SHOW_CONTACT_PAGE'] ?? 'Y');
		$params['SHOW_BASKET_PAGE'] = (string)($params['SHOW_BASKET_PAGE'] ?? 'Y');

		$params['USE_PRIVATE_PAGE_TO_AUTH'] = (string)($params['USE_PRIVATE_PAGE_TO_AUTH'] ?? 'N');

		$params['PATH_TO_PAYMENT'] = (string)($params['PATH_TO_PAYMENT'] ?? '/personal/order/payment/');
		$params['PATH_TO_CONTACT'] = (string)($params['PATH_TO_CONTACT'] ?? '/about/contacts/');
		$params['PATH_TO_BASKET'] = (string)($params['PATH_TO_BASKET'] ?? '/personal/cart/');
		$params['PATH_TO_CATALOG'] = (string)($params['PATH_TO_CATALOG'] ?? '/catalog/');

		$params['MAIN_CHAIN_NAME'] = (string)($params['MAIN_CHAIN_NAME'] ?? Loc::getMessage('SPS_CHAIN_MAIN'));

		$params['SET_TITLE'] = (string)($params['SET_TITLE'] ?? 'Y');
		$params['CACHE_TIME'] = (int)($params['CACHE_TIME'] ?? 3600);
		$params['CACHE_GROUPS'] = (string)($params['CACHE_GROUPS'] ?? 'Y');

		$params['CUSTOM_PAGES'] ??= '[]';
		if (!is_string($params['CUSTOM_PAGES']) || trim($params['CUSTOM_PAGES']) === '')
		{
			$params['CUSTOM_PAGES'] = '[]';
		}

		$params['SHOW_ACCOUNT_COMPONENT'] = (string)($params['SHOW_ACCOUNT_COMPONENT'] ?? 'Y');
		$params['SHOW_ACCOUNT_PAY_COMPONENT'] = (string)($params['SHOW_ACCOUNT_PAY_COMPONENT'] ?? 'Y');
		$params['ACCOUNT_PAYMENT_SELL_CURRENCY'] = (string)($params['ACCOUNT_PAYMENT_SELL_CURRENCY'] ?? '');
		$params['ACCOUNT_PAYMENT_PERSON_TYPE'] = (string)($params['ACCOUNT_PAYMENT_PERSON_TYPE'] ?? '');
		$params['ACCOUNT_PAYMENT_ELIMINATED_PAY_SYSTEMS'] ??= [];
		if (!is_array($params['ACCOUNT_PAYMENT_ELIMINATED_PAY_SYSTEMS']))
		{
			$params['ACCOUNT_PAYMENT_ELIMINATED_PAY_SYSTEMS'] = [];
		}
		$params['ACCOUNT_PAYMENT_SELL_SHOW_FIXED_VALUES'] = (string)($params['ACCOUNT_PAYMENT_SELL_SHOW_FIXED_VALUES'] ?? 'Y');

		$params['ACCOUNT_PAYMENT_SELL_TOTAL'] ??= [];
		if (empty($params['ACCOUNT_PAYMENT_SELL_TOTAL']) || !is_array($params['ACCOUNT_PAYMENT_SELL_TOTAL']))
		{
			$params['ACCOUNT_PAYMENT_SELL_TOTAL'] = [
				100,
				200,
				500,
				1000,
				5000,
			];
		}
		$params['ACCOUNT_PAYMENT_SELL_USER_INPUT'] = (string)($params['ACCOUNT_PAYMENT_SELL_USER_INPUT'] ?? 'Y');
		$params['ACCOUNT_PAYMENT_SELL_SHOW_RESULT_SUM'] = (string)($params['ACCOUNT_PAYMENT_SELL_SHOW_RESULT_SUM'] ?? 'N');

		$params['SAVE_IN_SESSION'] = (string)($params['SAVE_IN_SESSION'] ?? 'Y');
		$params['ACTIVE_DATE_FORMAT'] = (string)($params['ACTIVE_DATE_FORMAT'] ?? '');
		$params['CUSTOM_SELECT_PROPS'] ??= [];
		if (!is_array($params['CUSTOM_SELECT_PROPS']))
		{
			$params['CUSTOM_SELECT_PROPS'] = [];
		}
		$params['CUSTOM_SELECT_PROPS'] = array_filter($params['CUSTOM_SELECT_PROPS']);

		$params['ORDER_HIDE_USER_INFO'] ??= [];
		if (!is_array($params['ORDER_HIDE_USER_INFO']))
		{
			$params['ORDER_HIDE_USER_INFO'] = [];
		}

		$params['ORDER_HISTORIC_STATUSES'] ??= [];
		if (!is_array($params['ORDER_HISTORIC_STATUSES']))
		{
			$params['ORDER_HISTORIC_STATUSES'] = [];
		}

		$params['ORDER_RESTRICT_CHANGE_PAYSYSTEM'] ??= [];
		if (!is_array($params['ORDER_RESTRICT_CHANGE_PAYSYSTEM']))
		{
			$params['ORDER_RESTRICT_CHANGE_PAYSYSTEM'] = [];
		}

		$params['ORDER_DEFAULT_SORT'] = (string)($params['ORDER_DEFAULT_SORT'] ?? 'STATUS');
		$params['ORDER_REFRESH_PRICES'] = (string)($params['ORDER_REFRESH_PRICES'] ?? 'N');
		$params['ORDER_DISALLOW_CANCEL'] = (string)($params['ORDER_DISALLOW_CANCEL'] ?? 'N');

		$params['ALLOW_INNER'] = (string)($params['ALLOW_INNER'] ?? 'N');
		$params['ONLY_INNER_FULL'] = (string)($params['ONLY_INNER_FULL'] ?? 'N');

		$params['NAV_TEMPLATE'] = (string)($params['NAV_TEMPLATE'] ?? '');
		$params['ORDERS_PER_PAGE'] = (int)($params['ORDERS_PER_PAGE'] ?? 20);
		if ($params['ORDERS_PER_PAGE'] <= 0)
		{
			$params['ORDERS_PER_PAGE'] = 20;
		}

		$params['SEND_INFO_PRIVATE'] = (string)($params['SEND_INFO_PRIVATE'] ?? 'N');
		$params['CHECK_RIGHTS_PRIVATE'] = (string)($params['CHECK_RIGHTS_PRIVATE'] ?? 'N');
		$params['USE_AJAX_LOCATIONS_PROFILE'] = (string)($params['USE_AJAX_LOCATIONS_PROFILE'] ?? 'N');
		$params['COMPATIBLE_LOCATION_MODE_PROFILE'] = (string)($params['COMPATIBLE_LOCATION_MODE_PROFILE'] ?? 'N');
		$params['PROFILES_PER_PAGE'] = (int)($params['PROFILES_PER_PAGE'] ?? 20);
		if ($params['PROFILES_PER_PAGE'] <= 0)
		{
			$params['PROFILES_PER_PAGE'] = 20;
		}

		$params['CONTEXT_SITE_ID'] ??= null;

		$params['SUBSCRIBE_DETAIL_URL'] = (string)($params['SUBSCRIBE_DETAIL_URL'] ?? ''); // strange parameter for templates

		return $params;
	}

	public function executeComponent()
	{
		$sectionsList = array();

		$this->setFrameMode(false);

		$defaultUrlTemplates404 = array(
			"orders" => "orders/",
			"account" => "account/",
			"profile" => "profiles/",
			"profile_detail" => "profiles/#ID#",
			"private" => "private/",
			"subscribe" => "subscribe/",
			"index" => "index.php",
			"order_detail" => "orders/#ID#",
			"order_detail_old" => "order/detail/#ID#/",
			"order_cancel" => "orders/order_cancel.php?ID=#ID#",
			"password_change" => "password/change/",
			"password_restore" => "password/restore/",
			"login" => "login/",
		);

		if (!CBXFeatures::IsFeatureEnabled('SaleAccounts'))
		{
			$this->arParams['SHOW_ACCOUNT_PAGE'] = 'N';
			unset($this->arParams["SEF_URL_TEMPLATES"]['account']);
		}
		else
		{
			$defaultUrlTemplates404["account"] = "account/";
			$sectionsList[] = "account";
		}

		if (!\CComponentUtil::isComponent("/bitrix/components/bitrix/catalog.product.subscribe.list"))
		{
			$this->arParams['SHOW_SUBSCRIBE_PAGE'] = 'N';
			unset($this->arParams["SEF_URL_TEMPLATES"]['subscribe']);
		}
		else
		{
			$defaultUrlTemplates404["subscribe"] = "subscribe/";
			$sectionsList[] = "subscribe";
		}

		$componentVariables = array("CANCEL", "COPY_ORDER", "ID");
		$variables = array();

		$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();

		if ($this->arParams["SEF_MODE"] == "Y")
		{
			$templatesUrls = CComponentEngine::makeComponentUrlTemplates($defaultUrlTemplates404, $this->arParams["SEF_URL_TEMPLATES"]);

			foreach ($templatesUrls as $url => $value)
			{
				$this->arResult["PATH_TO_" . mb_strtoupper($url)] = $this->arParams["SEF_FOLDER"].$value;
			}
			$urlSign = str_contains($this->arParams['PATH_TO_LIST'], '?') ? '&' : '?';
			$this->arResult['PATH_TO_ORDER_COPY'] = $this->arResult['PATH_TO_ORDERS'] . $urlSign . 'COPY_ORDER=Y&ID=#ID#';
			$this->arResult['PATH_TO_PROFILE_DELETE'] = $this->arResult['PATH_TO_PROFILE'] . '?del_id=#ID#';

			$variableAliases = CComponentEngine::makeComponentVariableAliases(array(), $this->arParams["VARIABLE_ALIASES"]);

			$componentPage = CComponentEngine::parseComponentPath(
				$this->arParams["SEF_FOLDER"],
				$templatesUrls,
				$variables
			);

			if ($componentPage === "order_detail_old")
			{
				$componentPage = "order_detail";
			}

			CComponentEngine::initComponentVariables($componentPage, $componentVariables, $variableAliases, $variables);

			if (empty($componentPage))
			{
				$componentPage = 'index';
			}

			$this->arResult = array_merge(
				Array(
					"SEF_FOLDER" => $this->arParams["SEF_FOLDER"],
					"URL_TEMPLATES" => $templatesUrls,
					"VARIABLES" => $variables,
					"ALIASES" => $variableAliases,
				),
				$this->arResult
			);
		}
		else
		{
			$variableAliases = CComponentEngine::makeComponentVariableAliases(array(), $this->arParams["VARIABLE_ALIASES"]);
			CComponentEngine::initComponentVariables(false, $componentVariables, $variableAliases, $variables);

			$componentPage = $request->get('SECTION');

			if ($componentPage === "orders"
				&& $request->get('ID')
				&& !$request->get('COPY_ORDER'))
			{
				if ($request->get('CANCEL') === "Y")
				{
					$componentPage = "order_cancel";
				}
				else
				{
					$componentPage = "order_detail";
				}
			}

			if ($componentPage === "profile" && $request->get('ID'))
			{
				$componentPage = "profile_detail";
			}

			if (empty($componentPage))
			{
				if ($request->get('ID') && $request->get('COPY_ORDER') === 'Y')
				{
					$componentPage = "orders";
				}
				else
				{
					$componentPage = "index";
				}
			}

			$currentPage = $request->getRequestedPage();

			$this->arResult = array(
				"VARIABLES" => $variables,
				"ALIASES" => $variableAliases,
				"SEF_FOLDER" => $currentPage,
				""
			);

			$sectionsList = array_merge($sectionsList, array("orders", "profile", "private"));

			if ($this->arParams['USE_PRIVATE_PAGE_TO_AUTH'] === 'Y')
			{
				$sectionsList = array_merge($sectionsList, ['password_change', 'password_restore', 'login']);
			}

			foreach ($sectionsList as $sectionName)
			{
				if ($sectionName === "orders")
				{
					$this->arResult["PATH_TO_ORDERS"] = $currentPage."?SECTION=orders";
					$this->arResult["PATH_TO_ORDER_DETAIL"] = $this->arResult["PATH_TO_ORDERS"]."&ID=#ID#";
					$this->arResult["PATH_TO_ORDER_CANCEL"] = $this->arResult["PATH_TO_ORDERS"]."&ID=#ID#";
					$this->arResult["PATH_TO_ORDER_COPY"] = $currentPage."?COPY_ORDER=Y&ID=#ID#&SECTION=orders";
				}
				elseif ($sectionName === "profile")
				{
					$this->arResult["PATH_TO_PROFILE"] = $currentPage."?SECTION=".$sectionName;
					$this->arResult["PATH_TO_PROFILE_DETAIL"] = $this->arResult["PATH_TO_PROFILE"]."&ID=#ID#";
					$this->arResult["PATH_TO_PROFILE_DELETE"] = $this->arResult["PATH_TO_PROFILE"]."&del_id=#ID#";
				}
				else
				{
					$this->arResult["PATH_TO_" . mb_strtoupper($sectionName)] = $currentPage."?SECTION=".$sectionName;
				}
			}
		}

		if ($componentPage == "index" && $this->getTemplateName() !== "")
			$componentPage = "template";

		if ($componentPage == "order_detail")
		{
			Loader::includeModule('sale');

			$order = null;

			$id = urldecode(urldecode($variables["ID"]));

			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
			/** @var Sale\OrderBase $orderClassName */
			$orderClassName = $registry->getOrderClassName();

			if (trim($id) !== '')
			{
				$order = $orderClassName::loadByAccountNumber($id);
			}

			if (!$order)
			{
				$id = (int)$id;
				if ($id > 0)
				{
					$order = $orderClassName::load($id);
				}
			}

			/** @var Sale\Order $order */
			if ($order)
			{
				if (
					(is_array($this->arParams["ORDER_HISTORIC_STATUSES"]) && in_array($order->getField('STATUS_ID'), $this->arParams["ORDER_HISTORIC_STATUSES"]))
					|| $order->isCanceled()
				)
				{
					$this->arResult["PATH_TO_ORDERS"] = \CHTTP::urlAddParams(
						CComponentEngine::makePathFromTemplate($this->arResult["PATH_TO_ORDERS"]),
						['filter_history' => 'Y']
					);
					if ($order->isCanceled())
					{
						$this->arResult["PATH_TO_ORDERS"] = \CHTTP::urlAddParams(
							CComponentEngine::makePathFromTemplate($this->arResult["PATH_TO_ORDERS"]),
							['show_canceled' => 'Y']
						);
					}
				}
			}
		}
		elseif ($componentPage === "password_restore")
		{
			$this->arResult['SHOW_FORGOT_PASSWORD_FORM'] = 'Y';
			$componentPage = "private";
		}
		elseif ($componentPage === "password_change" )
		{
			$this->arResult['SHOW_CHANGE_PASSWORD_FORM'] = 'Y';
			$componentPage = "private";
		}
		elseif ($componentPage === "login" )
		{
			$this->arResult['SHOW_LOGIN_FORM'] = 'Y';
			$componentPage = "private";
		}

		if ($componentPage == 'private')
		{
			$this->arResult['SHOW_LOGIN_FORM'] ??= 'N';
			$this->arResult['SHOW_FORGOT_PASSWORD_FORM'] ??= 'N';
			$this->arResult['SHOW_CHANGE_PASSWORD_FORM'] ??= 'N';
			$this->arParams['AJAX_MODE_PRIVATE'] ??= 'N';
			$this->arParams['EDITABLE_EXTERNAL_AUTH_ID'] ??= 'N';
		}

		if ($this->arParams['USE_PRIVATE_PAGE_TO_AUTH'] === 'Y')
		{
			$this->arResult["AUTH_SUCCESS_URL"] = $this->arResult["PATH_TO_LOGIN"];
			$backUrl = $this->request->get('backurl');
			if (!empty($backUrl) && mb_strpos($backUrl, "/") === 0)
			{
				$this->arResult["AUTH_SUCCESS_URL"] = $backUrl;
			}

			$this->arResult["PATH_TO_AUTH_PAGE"] = \CHTTP::urlAddParams(
				$this->arResult["PATH_TO_PRIVATE"],
				['backurl' => urlencode($request->getRequestUri())],
				true
			);
		}

		$this->arResult['VARIABLES']['ID'] ??= 0;

		$this->includeComponentTemplate($componentPage);
	}
}
