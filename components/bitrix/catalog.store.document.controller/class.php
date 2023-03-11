<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Catalog;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\v2\Contractor\Provider\Manager;

class CatalogStoreDocumentControllerComponent extends CBitrixComponent
{
	private const URL_TEMPLATE_DOCUMENT = 'document';
	private const URL_TEMPLATE_DOCUMENT_LIST = 'list';
	private const URL_TEMPLATE_CONTRACTORS_LIST = 'contractors';
	private const URL_TEMPLATE_CONTRACTORS_CONTACTS = 'contractors_contacts';
	private const URL_TEMPLATE_DOCUMENT_SHIPMENT = 'sales_order';
	private const URL_TEMPLATE_ERROR = 'error';

	private $isIframe = false;

	public function onPrepareComponentParams($params)
	{
		if (!is_array($params))
		{
			$params = [];
		}
		$params['SEF_URL_TEMPLATES'] = $params['SEF_URL_TEMPLATES'] ?? [];
		$params['VARIABLE_ALIASES'] = $params['VARIABLE_ALIASES'] ?? [];

		return $params;
	}

	public function executeComponent()
	{
		if (Loader::includeModule('catalog'))
		{
			if (!Catalog\Config\Feature::isInventoryManagementEnabled())
			{
				LocalRedirect('/shop/');
			}
		}
		$this->initConfig();

		if (\Bitrix\Main\Loader::includeModule('crm'))
		{
			/** installing demo data for crm used for PresetCrmStoreMenu creation*/
			\CAllCrmInvoice::installExternalEntities();
		}

		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_INVENTORY_MANAGEMENT_ACCESS))
		{
			$this->includeComponentTemplate('access_denied');
			return;
		}

		$this->checkRedirect();

		$templateUrls = self::getTemplateUrls();

		if ($this->arParams['SEF_MODE'] === 'Y')
		{
			[$template, $variables, $variableAliases] = $this->processSefMode($templateUrls);
		}
		$this->arResult['VARIABLES'] = $variables;

		$this->arResult['IS_CRM_CONTRACTORS_PROVIDER'] = Manager::isActiveProviderByModule('crm');
		$this->arResult['CONTRACTORS_MIGRATION_PROGRESS'] = Manager::getMigrationProgressHtml();

		$this->includeComponentTemplate($template);
	}

	public function isIframeMode(): bool
	{
		return $this->isIframe;
	}

	protected function initConfig(): void
	{
		$this->isIframe = $this->request->get('IFRAME') === 'Y' && $this->request->get('IFRAME_TYPE') === 'SIDE_SLIDER';
	}

	private static function getTemplateUrls(): array
	{
		return [
			self::URL_TEMPLATE_DOCUMENT_LIST => '#DOCUMENT_TYPE#/',
			self::URL_TEMPLATE_DOCUMENT => 'details/#DOCUMENT_ID#/',
			self::URL_TEMPLATE_CONTRACTORS_LIST => 'contractors/',
			self::URL_TEMPLATE_CONTRACTORS_CONTACTS => 'contractors_contacts/',
			self::URL_TEMPLATE_DOCUMENT_SHIPMENT => 'details/sales_order/#DOCUMENT_ID#/',
		];
	}

	private function processSefMode($templateUrls): array
	{
		$templateUrls = CComponentEngine::MakeComponentUrlTemplates($templateUrls, $this->arParams['SEF_URL_TEMPLATES']);

		foreach ($templateUrls as $name => $url)
		{
			$this->arResult['PATH_TO'][strtoupper($name)] = $this->arParams['SEF_FOLDER'].$url;
		}

		$variableAliases = CComponentEngine::MakeComponentVariableAliases([], $this->arParams['VARIABLE_ALIASES']);

		$variables = [];
		$template = CComponentEngine::ParseComponentPath($this->arParams['SEF_FOLDER'], $templateUrls, $variables);

		if (!is_string($template) || !isset($templateUrls[$template]))
		{
			$template = key($templateUrls);
		}

		CComponentEngine::InitComponentVariables($template, [], $variableAliases, $variables);

		return [$template, $variables, $variableAliases];
	}

	private function checkRedirect()
	{
		$requestUrl = $this->request->getRequestUri();

		$analyticsSource = $this->request->get('inventoryManagementSource');
		if ($this->isIframe)
		{
			$requestUrl = (new \Bitrix\Main\Web\Uri($requestUrl))->deleteParams(['IFRAME', 'IFRAME_TYPE', 'inventoryManagementSource'])->getUri();
		}

		$defaultUrl = $this->arParams['SEF_FOLDER'];
		if ($requestUrl === $defaultUrl)
		{
			\CBitrixComponent::includeComponentClass('bitrix:catalog.store.document.list');
			$redirectUrl = $defaultUrl . \CatalogStoreDocumentListComponent::ARRIVAL_MODE . '/';
			if ($this->isIframe)
			{
				$redirectUrl = (new \Bitrix\Main\Web\Uri($redirectUrl))
					->addParams([
						'IFRAME' => 'Y',
						'IFRAME_TYPE' => 'SIDE_SLIDER'
					]);
				if ($analyticsSource)
				{
					$redirectUrl->addParams([
						'inventoryManagementSource' => $analyticsSource,
					]);
				}
				$redirectUrl = $redirectUrl->getUri();
			}
			LocalRedirect($redirectUrl);
		}
		else if ($requestUrl === $defaultUrl.'inventory/')
		{
			$redirectUrl = (new \Bitrix\Main\Web\Uri($defaultUrl))
				->addParams([
					'inventoryManagementSource' => 'inventory',
				]);

			LocalRedirect($redirectUrl);
		}
	}
}
