<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Sale;

class PersonalOrder extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->setFrameMode(false);

		$defaultUrlTemplates404 = array(
			"list" => "index.php",
			"detail" => "detail/#ID#",
			"cancel" => "cancel/#ID#",
		);

		$componentVariables = array("CANCEL", "COPY_ORDER", "ID");
		$variables = array();

		$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();

		if ($this->arParams["SEF_MODE"] == "Y")
		{
			$templatesUrls = CComponentEngine::makeComponentUrlTemplates($defaultUrlTemplates404, $this->arParams["SEF_URL_TEMPLATES"]);

			foreach ($templatesUrls as $url => $value)
			{
				$this->arResult["PATH_TO_".ToUpper($url)] = $this->arParams["SEF_FOLDER"].$value;				
			}

			
			$variableAliases = CComponentEngine::makeComponentVariableAliases(array(), $this->arParams["VARIABLE_ALIASES"]);

			$componentPage = CComponentEngine::parseComponentPath(
				$this->arParams["SEF_FOLDER"],
				$templatesUrls,
				$variables
			);

			CComponentEngine::initComponentVariables($componentPage, $componentVariables, $variableAliases, $variables);

			if ($request["CANCEL"]=="Y")
			{
				$componentPage = "cancel";
			}

			if (empty($componentPage))
			{
				$componentPage = 'list';
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

			if ($request["CANCEL"]=="Y")
			{
				$componentPage = "cancel";
			}
			elseif (!empty($variables["ID"]) && $request["COPY_ORDER"]!="Y")
			{
				$componentPage = "detail";
			}
			else
			{
				$componentPage = "list";
			}

			$this->arResult = array(
				"VARIABLES" => $variables,
				"ALIASES" => $variableAliases
			);
		}

		if ($componentPage == "index" && $this->getTemplateName() !== "")
			$componentPage = "template";

		if ($componentPage == "detail")
		{
			$id = urldecode(urldecode($variables["ID"]));
			$registry = Sale\Registry::getInstance(Sale\Order::getRegistryType());
			$orderClassName = $registry->getOrderClassName();

			$order = $orderClassName::loadByAccountNumber($id);
			if (!$order)
			{
				$order = $orderClassName::load((int)$id);
			}

			/** @var Sale\Order $order */
			if ($order)
			{
				if (
					(is_array($this->arParams["HISTORIC_STATUSES"]) && in_array($order->getField('STATUS_ID'), $this->arParams["HISTORIC_STATUSES"]))
					|| $order->isCanceled()
				)
				{
					$delimeter = (strpos($this->arResult["PATH_TO_LIST"], '?' ) !== false) ? '&' : '?';
					$this->arResult["PATH_TO_LIST"] .=  $delimeter . "filter_history=Y";
					if ($order->isCanceled())
					{
						$this->arResult["PATH_TO_LIST"] .=  "&show_canceled=Y";
					}
				}
			}
		}

		$this->includeComponentTemplate($componentPage);
	}
}