<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog;
use Bitrix\Crm;
use Bitrix\Iblock;

class CatalogCatalogControllerComponent extends CBitrixComponent implements Main\Errorable
{
	private const PAGE_INDEX = 'index';
	private const PAGE_LIST = 'list';
	private const PAGE_SECTION_LIST = 'section_list';
	private const PAGE_SECTION_DETAIL = 'section_detail';
	private const PAGE_PRODUCT_DETAIL = 'product_detail';
	private const PAGE_ERROR = 'error';

	/** @var  Main\ErrorCollection */
	protected $errorCollection;

	/** @var int */
	protected $iblockId;
	/** @var array */
	protected $iblock;
	/** @var string */
	protected $iblockListMode;
	/** @var bool */
	protected $iblockListMixed;

	/** @var string */
	protected $pageId;

	/** @var Crm\Product\Url\ProductBuilder */
	protected $urlBuilder;

	private $isIframe = false;

	protected $config = [];

	/**
	 * Base constructor.
	 * @param \CBitrixComponent|null $component		Component object if exists.
	 */
	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new Main\ErrorCollection();
	}

	/**
	 * @param $arParams
	 * @return array
	 */
	public function onPrepareComponentParams($arParams): array
	{
		if (!is_array($arParams))
		{
			$arParams = [];
		}

		$arParams['IBLOCK_ID'] = (int)($arParams['IBLOCK_ID'] ?? 0);
		$arParams['BUILDER_CONTEXT'] = (string)($arParams['BUILDER_CONTEXT'] ?? '');

		$arParams['SEF_MODE'] = 'Y';
		$arParams['SEF_FOLDER'] = (string)($arParams['SEF_FOLDER'] ?? '/shop/documents-catalog/');
		$arParams['SEF_URL_TEMPLATES'] = $arParams['SEF_URL_TEMPLATES'] ?? [];
		$arParams['VARIABLE_ALIASES'] = $arParams['VARIABLE_ALIASES'] ?? [];

		if (empty($arParams['PATH_TO']) || !is_array($arParams['PATH_TO']))
		{
			$arParams['PATH_TO'] = [];
		}



		return parent::onPrepareComponentParams($arParams);
	}

	/**
	 * @return void
	 */
	public function onIncludeComponentLang(): void
	{
		$this->includeComponentLang('class.php');
	}

	/**
	 * @param string $code
	 * @return Main\Error|null
	 */
	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	/**
	 * @return Main\Error[]
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * @return bool
	 */
	protected function isExistErrors(): bool
	{
		return !$this->errorCollection->isEmpty();
	}

	/**
	 * @return void
	 */
	protected function showErrors(): void
	{
		foreach ($this->getErrors() as $error)
		{
			\ShowError($error);
		}
		unset($error);
	}

	/**
	 * @param string $message
	 * @return void
	 */
	protected function addErrorMessage(string $message): void
	{
		$this->errorCollection->setError(new Main\Error($message));
	}

	public function isUiCatalog(): bool
	{
		return (isset($this->config['UI_CATALOG']) && $this->config['UI_CATALOG']);
	}

	public function executeComponent()
	{
		$this->checkModules();
		if ($this->isExistErrors())
		{
			$this->showErrors();
			return;
		}
		$this->checkAccess();
		if ($this->isExistErrors())
		{
			$this->includeComponentTemplate(self::PAGE_ERROR);

			return;
		}
		$this->initConfig();
		if ($this->isExistErrors())
		{
			$this->showErrors();
			return;
		}
		$this->initUrlBuilder();
		if ($this->isExistErrors())
		{
			$this->showErrors();
			return;
		}
		$this->parseComponentVariables();
		if ($this->isExistErrors())
		{
			$this->showErrors();
			return;
		}
		$this->initUiScope();
		$this->arResult['PAGE_DESCRIPTION'] = $this->getPageDescription();
		$this->includeComponentTemplate($this->pageId);
	}

	protected function checkModules(): void
	{
		if (!Loader::includeModule('catalog'))
		{
			$this->addErrorMessage(Loc::getMessage('CATALOG_CATALOG_CONTROLLER_ERR_CATALOG_MODULE_ABSENT'));
		}
		if (!Loader::includeModule('iblock'))
		{
			$this->addErrorMessage(Loc::getMessage('CATALOG_CATALOG_CONTROLLER_ERR_IBLOCK_MODULE_ABSENT'));
		}
	}

	protected function checkAccess(): void
	{
		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ))
		{
			$this->addErrorMessage(Loc::getMessage('CATALOG_CATALOG_CONTROLLER_ERR_ACCESS_DENIED'));
		}
	}

	protected function initConfig(): void
	{
		$iblockId = $this->arParams['IBLOCK_ID'];
		if ($iblockId <= 0)
		{
			$iblockId = null;
			if (Loader::includeModule('crm'))
			{
				$iblockId = Crm\Product\Catalog::getDefaultId();
			}
		}
		if ($iblockId === null)
		{
			$this->addErrorMessage(Loc::getMessage('CATALOG_CATALOG_CONTROLLER_ERR_CATALOG_PRODUCT_ABSENT'));
			return;
		}
		$iblock = \CIBlock::GetArrayByID($iblockId);
		if (empty($iblock) || !is_array($iblock))
		{
			$this->addErrorMessage(Loc::getMessage('CATALOG_CATALOG_CONTROLLER_ERR_CATALOG_PRODUCT_ABSENT'));
			return;
		}
		$this->iblockId = $iblockId;
		$this->iblock = $iblock;
		$this->isIframe = $this->request->get('IFRAME') === 'Y' && $this->request->get('IFRAME_TYPE') === 'SIDE_SLIDER';
		$this->config['UI_CATALOG'] = Catalog\Config\State::isProductCardSliderEnabled();
	}

	protected function initUrlBuilder(): void
	{
		$builderId = $this->arParams['BUILDER_CONTEXT'];
		if ($builderId === '')
		{
			$builderId = Catalog\Url\ShopBuilder::TYPE_ID;
		}

		$builderManager = Iblock\Url\AdminPage\BuilderManager::getInstance();
		$this->urlBuilder = $builderManager->getBuilder($builderId);
		if ($this->urlBuilder === null)
		{
			$this->addErrorMessage(Loc::getMessage('CATALOG_CATALOG_CONTROLLER_ERR_URL_BUILDER_ABSENT'));
			return;
		}
		$this->urlBuilder->setIblockId($this->iblockId);
		$this->urlBuilder->setUrlParams([]);
		$this->iblockListMixed = $this->urlBuilder->isIblockListMixed();
		$this->iblockListMode =
			$this->iblockListMixed
				? Iblock\IblockTable::LIST_MODE_COMBINED
				: Iblock\IblockTable::LIST_MODE_SEPARATE
		;
	}

	protected function parseComponentVariables(): void
	{
		CBitrixComponent::includeComponentClass('bitrix:catalog.productcard.controller');

		$templateUrls = $this->getTemplateUrls();
		[$template, $variables, $variableAliases] = $this->processSefMode($templateUrls);

		if ($this->isUiCatalog())
		{
			if (\CatalogProductControllerComponent::hasUrlTemplateId($template))
			{
				$template = self::PAGE_PRODUCT_DETAIL;
			}
		}

		$this->arResult = array_merge(
			[
				'VARIABLES' => $variables,
				'ALIASES' => $variableAliases,
			],
			$this->arResult
		);

		$this->pageId = $template;
		if (empty($this->pageId))
		{
			$this->addErrorMessage(Loc::getMessage('CATALOG_CATALOG_CONTROLLER_ERR_PAGE_UNKNOWN'));
		}
		if ($this->pageId === self::PAGE_INDEX)
		{
			if (
				!$this->request->isPost()
				&& !$this->request->isAjaxRequest()
				&& (
					$this->request->getQuery('find_section_section') === null
					|| $this->request->getQuery('SECTION_ID') === null
					|| $this->request->getQuery('apply_filter') === null
				)
			)
			{
				$pageUrl = $this->request->getRequestUri();
				$currentUri = new Main\Web\Uri($pageUrl);
				LocalRedirect($currentUri->addParams([
					'find_section_section' => 0,
					'SECTION_ID' => 0,
					'apply_filter' => 'Y'
				])->getUri());
			}
		}
	}

	protected function getTemplateUrls(): array
	{
		if ($this->iblockListMixed)
		{
			$result = [
				self::PAGE_INDEX => '',
				self::PAGE_LIST => 'list/#SECTION_ID#/',
				self::PAGE_SECTION_DETAIL => 'section/#SECTION_ID#/',
			];
		}
		else
		{
			$result = [
				self::PAGE_INDEX => '',
				self::PAGE_LIST => 'list/#SECTION_ID#/',
				self::PAGE_SECTION_LIST => 'section_list/#SECTION_ID#/',
				self::PAGE_SECTION_DETAIL => 'section/#SECTION_ID#/',
			];
		}

		if ($this->isUiCatalog())
		{
			return $result + \CatalogProductControllerComponent::getTemplateUrls();
		}
		else
		{
			$result[self::PAGE_PRODUCT_DETAIL] = 'product/#ELEMENT_ID#/';

			return $result;
		}
	}

	protected function processSefMode(array $templateUrls): array
	{
		$templateUrls = \CComponentEngine::MakeComponentUrlTemplates($templateUrls, $this->arParams['SEF_URL_TEMPLATES']);
		foreach ($templateUrls as $name => $url)
		{
			$this->arResult['PATH_TO'][strtoupper($name)] = $this->arParams['SEF_FOLDER'].$url;
		}

		$variableAliases = \CComponentEngine::MakeComponentVariableAliases([], $this->arParams['VARIABLE_ALIASES']);

		$variables = [];
		$template = \CComponentEngine::ParseComponentPath($this->arParams['SEF_FOLDER'], $templateUrls, $variables);

		if (!is_string($template) || !isset($templateUrls[$template]))
		{
			$template = key($templateUrls);
		}

		\CComponentEngine::InitComponentVariables($template, [], $variableAliases, $variables);

		return [$template, $variables, $variableAliases];
	}

	protected function getPageDescription(): ?array
	{
		$result = null;

		$pageUrl = $this->request->getRequestUri();
		$currentUri = new Main\Web\Uri($pageUrl);
		$queryString = $currentUri->getQuery();

		switch ($this->pageId)
		{
			case self::PAGE_INDEX:
				$result = [
					'PAGE_ID' => 'catalog_catalog_products',
					'PAGE_PATH' => '/bitrix/modules/iblock/admin/'.($this->iblockListMixed
						? 'iblock_list_admin.php'
						: 'iblock_element_admin.php'
					),
					'PAGE_PARAMS' => $this->urlBuilder->getBaseParams(),
					'SEF_FOLDER' => '/', // hack for template files
					'INTERNAL_PAGE' => 'N',
					'IS_SIDE_PANEL' => 'N',
					'CACHE_TYPE' => 'N',
					'PAGE_CONSTANTS' => [
						'URL_BUILDER_TYPE' => $this->urlBuilder->getId(),
						'SELF_FOLDER_URL' => '/shop/settings/'
					]
				];
				break;
			case self::PAGE_LIST:
				$result = [
					'PAGE_ID' => ($this->iblockListMixed ? 'catalog_catalog_item_list' : 'catalog_catalog_product_list'),
					'PAGE_PATH' => '/bitrix/modules/iblock/admin/'.($this->iblockListMixed
						? 'iblock_list_admin.php'
						: 'iblock_element_admin.php'
					),
					'PAGE_PARAMS' => $this->urlBuilder->getBaseParams(),
					'SEF_FOLDER' => '/', // hack for template files
					'INTERNAL_PAGE' => 'N',
					'IS_SIDE_PANEL' => 'N',
					'CACHE_TYPE' => 'N',
					'PAGE_CONSTANTS' => [
						'URL_BUILDER_TYPE' => $this->urlBuilder->getId(),
						'SELF_FOLDER_URL' => '/shop/settings/'
					]
				];
				break;
			case self::PAGE_SECTION_LIST:
				$result = [
					'PAGE_ID' => 'catalog_catalog_section_list',
					'PAGE_PATH' => '/bitrix/modules/iblock/admin/iblock_section_admin.php',
					'PAGE_PARAMS' => $this->urlBuilder->getBaseParams(),
					'SEF_FOLDER' => '/', // hack for template files
					'INTERNAL_PAGE' => 'N',
					'IS_SIDE_PANEL' => 'N',
					'CACHE_TYPE' => 'N',
					'PAGE_CONSTANTS' => [
						'URL_BUILDER_TYPE' => $this->urlBuilder->getId(),
						'SELF_FOLDER_URL' => '/shop/settings/'
					]
				];
				break;
			case self::PAGE_SECTION_DETAIL:
				$result = [
					'PAGE_ID' => 'catalog_catalog_section_detail',
					'PAGE_PATH' => '',
					'PAGE_PARAMS' => $queryString,
					'SEF_FOLDER' => '/', // hack for template files
					'INTERNAL_PAGE' => 'Y',
					'CACHE_TYPE' => 'N',
					'PAGE_CONSTANTS' => [
						'URL_BUILDER_TYPE' => $this->urlBuilder->getId(),
						'SELF_FOLDER_URL' => '/shop/settings/'
					]
				];
				break;
			case self::PAGE_PRODUCT_DETAIL:
				//$result = [];
				if ($this->isUiCatalog())
				{
					$result = [];
				}
				else
				{
					$result = [
						'PAGE_ID' => 'catalog_catalog_product_detail',
						'PAGE_PATH' => '',
						'PAGE_PARAMS' => $queryString,
						'SEF_FOLDER' => '/', // hack for template files
						'INTERNAL_PAGE' => 'Y',
						'CACHE_TYPE' => 'N',
						'PAGE_CONSTANTS' => [
							'URL_BUILDER_TYPE' => $this->urlBuilder->getId(),
							'SELF_FOLDER_URL' => '/shop/settings/'
						]
					];
				}
				break;
		}

		return $result;
	}

	/**
	 * @return void
	 */
	protected function initUiScope(): void
	{
		global $APPLICATION;

		Main\UI\Extension::load($this->getUiExtensions());

		foreach ($this->getUiStyles() as $styleList)
		{
			$APPLICATION->SetAdditionalCSS($styleList);
		}

		$scripts = $this->getUiScripts();
		if (!empty($scripts))
		{
			$asset = Main\Page\Asset::getInstance();
			foreach ($scripts as $row)
			{
				$asset->addJs($row);
			}
			unset($row, $asset);
		}
		unset($scripts);
	}

	/**
	 * @return array
	 */
	protected function getUiExtensions(): array
	{
		return [
			'admin_interface',
			'sidepanel'
		];
	}

	/**
	 * @return array
	 */
	protected function getUiStyles(): array
	{
		return [];
	}

	/**
	 * @return array
	 */
	protected function getUiScripts(): array
	{
		return [];
	}

	public function showCatalogControlPanel(): void
	{
		/** global \CMain $APPLICATION */
		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			'bitrix:catalog.store.document.control_panel',
			'',
			[
				'PATH_TO' => $this->arParams['PATH_TO'],
			],
			$this
		);
	}

	public function isIframeMode(): bool
	{
		return $this->isIframe;
	}
}
