<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog;
use Bitrix\Crm;
use Bitrix\UI\Toolbar\Facade\Toolbar;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogProductControllerComponent extends CBitrixComponent
{
	private const TEMPLATE_CODE = 'SECTION';

	private const URL_TEMPLATE_GRID = 'product_grid';
	private const URL_TEMPLATE_GRID_SECTION = 'product_grid_section';
	private const URL_TEMPLATE_PRODUCT = 'product_details';
	private const URL_TEMPLATE_COPY_PRODUCT = 'product_copy_details';
	private const URL_TEMPLATE_VARIATION = 'variation_details';
	private const URL_TEMPLATE_CREATE_PROPERTY = 'property_creator';
	private const URL_TEMPLATE_MODIFY_PROPERTY = 'property_modify';
	private const URL_TEMPLATE_PRODUCT_STORE_AMOUNT = 'product_store_amount_details';
	private const URL_TEMPLATE_PRODUCT_STORE_AMOUNT_SLIDER = 'product_store_amount_details_slider';
	private const URL_TEMPLATE_SEO_CATALOG_SLIDER = 'seo_catalog';
	private const URL_TEMPLATE_SEO_SECTION_SLIDER = 'seo_section';
	private const URL_TEMPLATE_SEO_PRODUCT_SLIDER = 'seo_product';

	private const REQUEST_VARIABLE_PRODUCT_TYPE = 'productTypeId';

	public const SCOPE_SHOP = 'shop';
	public const SCOPE_CRM = 'crm';

	protected $crmIncluded;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->crmIncluded = Loader::includeModule('crm');
	}

	public function onPrepareComponentParams($params): array
	{
		$params['IFRAME'] = (bool)($params['IFRAME'] ?? $this->request->get('IFRAME') === 'Y');
		$params['SEF_URL_TEMPLATES'] = $params['SEF_URL_TEMPLATES'] ?? [];
		$params['VARIABLE_ALIASES'] = $params['VARIABLE_ALIASES'] ?? [];
		$params = $this->getPreparedParams($params);

		return parent::onPrepareComponentParams($params);
	}

	public static function getTemplateUrls(): array
	{
		return [
			self::URL_TEMPLATE_GRID => '#IBLOCK_ID#/',
			self::URL_TEMPLATE_GRID_SECTION => '#IBLOCK_ID#/section/#SECTION_ID#/',
			self::URL_TEMPLATE_PRODUCT => '#IBLOCK_ID#/product/#PRODUCT_ID#/',
			self::URL_TEMPLATE_COPY_PRODUCT => '#IBLOCK_ID#/product/0/copy/#COPY_PRODUCT_ID#/',
			self::URL_TEMPLATE_VARIATION => '#IBLOCK_ID#/product/#PRODUCT_ID#/variation/#VARIATION_ID#/',
			self::URL_TEMPLATE_CREATE_PROPERTY => '#IBLOCK_ID#/create_property/#PROPERTY_TYPE#/',
			self::URL_TEMPLATE_MODIFY_PROPERTY => '#IBLOCK_ID#/modify_property/#PROPERTY_ID#/',
			self::URL_TEMPLATE_PRODUCT_STORE_AMOUNT => '#IBLOCK_ID#/product/#PRODUCT_ID#/store_amount/?storeId=#STORE_ID#',
			self::URL_TEMPLATE_PRODUCT_STORE_AMOUNT_SLIDER => '#IBLOCK_ID#/product/#PRODUCT_ID#/variation/#VARIATION_ID#/store_amount_slider/',
			self::URL_TEMPLATE_SEO_SECTION_SLIDER => '#IBLOCK_ID#/seo/section/#SECTION_ID#/',
			self::URL_TEMPLATE_SEO_PRODUCT_SLIDER => '#IBLOCK_ID#/seo/product/#PRODUCT_ID#/',
			self::URL_TEMPLATE_SEO_CATALOG_SLIDER => '#IBLOCK_ID#/seo/',
		];
	}

	private function getNotIframeTemplates(): array
	{
		return [
			self::URL_TEMPLATE_GRID,
			self::URL_TEMPLATE_GRID_SECTION,
		];
	}

	public static function hasUrlTemplateId(string $templateId): bool
	{
		$templates = self::getTemplateUrls();

		return isset($templates[$templateId]);
	}

	protected function getPreparedParams(array $params): array
	{
		$allowedBuilderTypes = [
			Catalog\Url\ShopBuilder::TYPE_ID,
			Catalog\Url\InventoryBuilder::TYPE_ID,
		];
		$allowedScopeList = [
			self::SCOPE_SHOP,
		];
		if ($this->crmIncluded)
		{
			$allowedBuilderTypes[] = Crm\Product\Url\ProductBuilder::TYPE_ID;
			$allowedScopeList[] = self::SCOPE_CRM;
		}

		$params['BUILDER_CONTEXT'] = (string)($params['BUILDER_CONTEXT'] ?? '');
		if (!in_array($params['BUILDER_CONTEXT'], $allowedBuilderTypes, true))
		{
			$params['BUILDER_CONTEXT'] = Catalog\Url\ShopBuilder::TYPE_ID;
		}

		$params['SCOPE'] = (string)($params['SCOPE'] ?? '');
		if ($params['SCOPE'] === '')
		{
			$params['SCOPE'] = $this->getScopeByUrl();
		}

		if (!in_array($params['SCOPE'], $allowedScopeList))
		{
			$params['SCOPE'] = self::SCOPE_SHOP;
		}

		return $params;
	}

	protected function getScopeByUrl(): string
	{
		$result = '';

		$currentPath = $this->request->getRequestUri();
		if (strncmp($currentPath, '/shop/', 6) === 0)
		{
			$result = self::SCOPE_SHOP;
		}
		elseif ($this->crmIncluded)
		{
			if (strncmp($currentPath, '/crm/', 5) === 0)
			{
				$result = self::SCOPE_CRM;
			}
		}

		return $result;
	}

	protected function checkModules(): bool
	{
		if (!Loader::includeModule('catalog'))
		{
			$this->includeErrorComponent(Loc::getMessage('CATALOG_MODULE_IS_NOT_INSTALLED'));

			return false;
		}

		return true;
	}

	protected function isSkipCheckFeature(string $template): bool
	{
		return in_array($template, [
			'product_grid',
			'product_grid_section',
		], true);
	}

	protected function checkFeature(): bool
	{
		if (!$this->isCardAllowed())
		{
			$this->includeErrorComponent(Loc::getMessage('CATALOG_FEATURE_IS_DISABLED'));

			return false;
		}

		return true;
	}

	/**
	 * @param string $errorMessage
	 * @param string|null $description
	 * @return void
	 */
	protected function includeErrorComponent(string $errorMessage, string $description = null): void
	{
		Toolbar::deleteFavoriteStar();

		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			"bitrix:ui.info.error",
			"",
			[
				'TITLE' => $errorMessage,
				'DESCRIPTION' => $description,
			]
		);
	}

	protected function isCardAllowed(): bool
	{
		switch ($this->arParams['SCOPE'])
		{
			case self::SCOPE_SHOP:
				$result = Catalog\Config\State::isProductCardSliderEnabled();
				break;
			case self::SCOPE_CRM:
				$result = false;
				if ($this->crmIncluded)
				{
					$result = Crm\Settings\LayoutSettings::getCurrent()->isFullCatalogEnabled();
				}
				break;
			default:
				$result = false;
				break;
		}

		return $result;
	}

	protected function processSefMode($templateUrls): array
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

	protected function processRegularMode($templateUrls): array
	{
		$variableAliases = CComponentEngine::MakeComponentVariableAliases([], $this->arParams['VARIABLE_ALIASES']);

		$variables = [];
		CComponentEngine::InitComponentVariables(false, [], $variableAliases, $variables);

		$currentPage = $this->request->getRequestedPage();
		$templates = array_keys($templateUrls);

		foreach ($templates as $template)
		{
			$this->arResult['PATH_TO'][strtoupper($template)] = $currentPage.'?'.self::TEMPLATE_CODE.'='.$template;
		}

		$template = $this->request->get(self::TEMPLATE_CODE);

		if ($template === null || !in_array($template, $templates, true))
		{
			$template = key($templateUrls);
		}

		return [$template, $variables, $variableAliases];
	}

	protected function getAdditionalTemplateParameters(string $template): array
	{
		$result = [];

		if (
			$template === self::URL_TEMPLATE_PRODUCT
		)
		{
			$value = $this->request->get(self::REQUEST_VARIABLE_PRODUCT_TYPE);
			if (is_string($value) && $value !== '')
			{
				$result['PRODUCT_TYPE_ID'] = $value;
			}
		}

		return $result;
	}

	public function executeComponent()
	{
		if (!$this->checkModules())
		{
			return;
		}

		$templateUrls = $this->getTemplateUrls();

		if ($this->arParams['SEF_MODE'] === 'Y')
		{
			[$template, $variables, $variableAliases] = $this->processSefMode($templateUrls);
		}
		else
		{
			[$template, $variables, $variableAliases] = $this->processRegularMode($templateUrls);
		}

		if (!$this->isSkipCheckFeature($template) && !$this->checkFeature())
		{
			return;
		}

		$this->arResult['PATH_TO']['USER_PROFILE'] = '/company/personal/user/#user_id#/';

		$this->arResult = array_merge(
			[
				'VARIABLES' => $variables,
				'ALIASES' => $variableAliases,
			],
			$this->arResult
		);
		$this->arResult['ADDITIONAL_TEMPLATE_PARAMETERS'] = $this->getAdditionalTemplateParameters($template);
		$this->arResult['BUILDER_CONTEXT'] = $this->arParams['BUILDER_CONTEXT'];
		$this->arResult['SCOPE'] = $this->arParams['SCOPE'];

		if (
			$this->request->get('IFRAME') === 'Y'
			|| $this->request->get('mode') === 'dev'
			|| in_array($template, $this->getNotIframeTemplates())
		)
		{
			$this->includeComponentTemplate($template);
		}
		else
		{
			$urlManager = \Bitrix\Iblock\Url\AdminPage\BuilderManager::getInstance();
			$urlBuilder = $urlManager->getBuilder();
			$urlBuilder->setIblockId($variables['IBLOCK_ID'] ?? 0);
			$url = $urlBuilder->getElementListUrl(-1);

			$sliderPath = '';
			if ($template === self::URL_TEMPLATE_PRODUCT)
			{
				$sliderPath = str_replace(
					['#IBLOCK_ID#', '#PRODUCT_ID#'],
					[$variables['IBLOCK_ID'], $variables['PRODUCT_ID']],
					$this->arResult['PATH_TO']['PRODUCT_DETAILS']
				);
			}
			elseif ($template === self::URL_TEMPLATE_VARIATION)
			{
				$sliderPath = str_replace(
					['#IBLOCK_ID#', '#PRODUCT_ID#', '#VARIATION_ID#'],
					[$variables['IBLOCK_ID'], $variables['PRODUCT_ID'], $variables['VARIATION_ID']],
					$this->arResult['PATH_TO']['VARIATION_DETAILS']
				);
			}
			$uri = new \Bitrix\Main\Web\Uri($url);
			$sliderOption = $urlBuilder->getSliderPathOption($sliderPath);
			$uri->addParams(
				$sliderOption ?? []
			);
			\LocalRedirect($uri->getLocator());
		}
	}
}
