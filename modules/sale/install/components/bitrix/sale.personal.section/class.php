<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class PersonalOrderSection extends CBitrixComponent
{
	public function onPrepareComponentParams($params)
	{
		if (!isset($params['MAIN_CHAIN_NAME']))
		{
			$params['MAIN_CHAIN_NAME'] = Loc::getMessage("SPS_CHAIN_MAIN");
		}
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

		if ($this->arParams["SEF_MODE"] == "Y")
		{
			$templatesUrls = CComponentEngine::makeComponentUrlTemplates($defaultUrlTemplates404, $this->arParams["SEF_URL_TEMPLATES"]);

			foreach ($templatesUrls as $url => $value)
			{
				$this->arResult["PATH_TO_".ToUpper($url)] = $this->arParams["SEF_FOLDER"].$value;
			}

			$this->arResult["PATH_TO_ORDER_COPY"] = $this->arResult["PATH_TO_ORDERS"]."?COPY_ORDER=Y&ID=#ID#";

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

			$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();

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
					$this->arResult["PATH_TO_".ToUpper($sectionName)] = $currentPage."?SECTION=".$sectionName;
				}
			}
		}

		if ($componentPage == "index" && $this->getTemplateName() !== "")
			$componentPage = "template";

		$this->includeComponentTemplate($componentPage);
	}
}