<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;
use Bitrix\Catalog;

\Bitrix\Main\Loader::includeModule('catalog');

class CatalogStoreDocumentControlPanelComponent extends \CBitrixComponent
{
	private $isIframe = false;
	private $analyticsSource = '';

	public function onPrepareComponentParams($arParams)
	{
		if (empty($arParams['PATH_TO']) || !is_array($arParams['PATH_TO']))
		{
			// TODO: replace this temporary hack
			$arParams['PATH_TO'] = [
				'LIST' => '/shop/documents/#DOCUMENT_TYPE#/',
				'STORES' => '/shop/documents/stores/',
				'CONTRACTORS' => '/shop/documents/contractors/',
			];
		}
		return parent::onPrepareComponentParams($arParams);
	}

	public function executeComponent()
	{
		$this->isIframe = $this->request->get('IFRAME') === 'Y' && $this->request->get('IFRAME_TYPE') === 'SIDE_SLIDER';
		$this->analyticsSource = $this->request->get('inventoryManagementSource') ?? '';
		$this->arResult['IS_IFRAME_MODE'] = $this->isIframe;
		$this->arResult['ITEMS'] = $this->getPanelButtons();
		$this->includeComponentTemplate();
	}

	private function getPanelButtons(): array
	{
		$requestUrl = $this->request->getRequestUri();
		$buttons = [];

		\CBitrixComponent::includeComponentClass('bitrix:catalog.store.document.list');

		$url = $this->getUrlToDocumentType(\CatalogStoreDocumentListComponent::ARRIVAL_MODE);
		$buttons[] = [
			'ID' => 'arrival_docs',
			'TEXT' => Loc::getMessage('STORE_DOCUMENTS_ARRIVAL_BUTTON_TITLE'),
			'URL' => $url,
			'SORT' => 10,
			'IS_ACTIVE' => $this->compareUrls($requestUrl, $url),
		];

		if (Main\Loader::includeModule('crm'))
		{
			$url = $this->getUrlToDocumentType('sales_order');
			$buttons[] = [
				'ID' => 'sales_order_docs',
				'TEXT' => Loc::getMessage('STORE_DOCUMENTS_SALES_ORDER_BUTTON_TITLE'),
				'URL' => $url,
				'SORT' => 20,
				'IS_ACTIVE' => $this->compareUrls($requestUrl, $url),
			];
		}

		$url = $this->getUrlToDocumentType(\CatalogStoreDocumentListComponent::MOVING_MODE);
		$buttons[] = [
			'ID' => 'moving_docs',
			'TEXT' => Loc::getMessage('STORE_DOCUMENTS_MOVING_BUTTON_TITLE'),
			'URL' => $url,
			'SORT' => 30,
			'IS_ACTIVE' => $this->compareUrls($requestUrl, $url),
		];

		$url = $this->getUrlToDocumentType(\CatalogStoreDocumentListComponent::DEDUCT_MODE);
		$buttons[] = [
			'ID' => 'deduct_docs',
			'TEXT' => Loc::getMessage('STORE_DOCUMENTS_DEDUCT_BUTTON_TITLE'),
			'URL' => $url,
			'SORT' => 40,
			'IS_ACTIVE' => $this->compareUrls($requestUrl, $url),
		];

		$urlBuilder = Iblock\Url\AdminPage\BuilderManager::getInstance()->getBuilder(
			Catalog\Url\InventoryBuilder::TYPE_ID
		);
		if (!empty($urlBuilder))
		{
			$catalogPath = $this->arParams['PATH_TO']['CATALOG'] ?? '';
			if ($catalogPath !== '')
			{
				$urlBuilder->setPrefix($catalogPath);
			}
			$rawUrl = $urlBuilder->getPrefix();
			$url = $this->getUrlWithParams($rawUrl);
			$buttons[] = [
				'ID' => 'store_goods',
				'TEXT' => Loc::getMessage('STORE_DOCUMENTS_GOODS_BUTTON_TITLE'),
				'URL' => $url,
				'SORT' => 50,
				'IS_ACTIVE' => strncmp(
					$rawUrl,
					$this->request->getRequestedPage(),
					strlen($rawUrl)
				) === 0,
			];
		}


		$sliderPath = \CComponentEngine::makeComponentPath('bitrix:catalog.warehouse.master.clear');
		$sliderPath = getLocalPath('components' . $sliderPath . '/slider.php');

		$masterSliderSettings = CUtil::PhpToJSObject([
			'data' => [
				'openGridOnDone' => false,
				'closeSliderOnMarketplace' => false,
			],
		]);

		if (Main\Loader::includeModule('report') && self::checkDocumentReadRights())
		{
			if (\Bitrix\Catalog\Component\UseStore::isUsed())
			{
				$url = '/report/analytics/?analyticBoardKey=catalog_warehouse_stock';
				$buttons[] = [
					'ID' => 'analytics',
					'TEXT' => Loc::getMessage('STORE_DOCUMENTS_ANALYTICS_BUTTON_TITLE'),
					'URL' => $url,
					'SORT' => 45,
					'IS_ACTIVE' => $this->compareUrls($requestUrl, $url),
				];
			}
			else
			{
				$buttons[] = [
					'ID' => 'analytics',
					'TEXT' => Loc::getMessage('STORE_DOCUMENTS_ANALYTICS_BUTTON_TITLE'),
					'SORT' => 45,
					'ON_CLICK' => 'new BX.Catalog.Store.Document.ControlPanel().storeMasterOpenSlider(\''.$sliderPath.'\', ' . $masterSliderSettings . ');',
				];
			}
		}

		$settingsUrls = [
			$this->getUrlWithParams($this->arParams['PATH_TO']['STORES']),
			$this->getUrlWithParams($this->arParams['PATH_TO']['CONTRACTORS']),
		];

		$settingsItems = [
			[
				'ID' => 'stores_settings',
				'PARENT_ID' => 'settings',
				'TEXT' => Loc::getMessage('STORE_DOCUMENTS_SETTINGS_STORES_TITLE'),
				'SORT' => 1000,
				'URL' => $this->getUrlWithParams($this->arParams['PATH_TO']['STORES']),
			],
			[
				'ID' => 'contractors_settings',
				'PARENT_ID' => 'settings',
				'TEXT' => Loc::getMessage('STORE_DOCUMENTS_SETTINGS_CONTRACTORS_TITLE'),
				'SORT' => 1010,
				'URL' => $this->getUrlWithParams($this->arParams['PATH_TO']['CONTRACTORS']),
			],
		];

		if (!\Bitrix\Catalog\Component\UseStore::isUsed())
		{
			$settingsItems[] = [
				'ID' => 'store_settings',
				'PARENT_ID' => 'settings',
				'TEXT' => Loc::getMessage('STORE_DOCUMENTS_SETTINGS_USE_STORE_Y_TITLE'),
				'SORT' => 1020,
				'ON_CLICK' => 'new BX.Catalog.Store.Document.ControlPanel().storeMasterOpenSlider(\''.$sliderPath.'\', ' . $masterSliderSettings . ');',
			];
		}

		if (!\CCrmSaleHelper::isWithOrdersMode())
		{
			$settingsItems[] = [
				'ID' => 'store_settings_control_and_settings',
				'PARENT_ID' => 'settings',
				'TEXT' => Loc::getMessage('STORE_DOCUMENTS_SETTINGS_STORE_CONTROL_AND_PRODUCTS'),
				'SORT' => 1030,
				'URL' => '/crm/configs/catalog/',
			];
		}

		$buttons[] = [
			'TEXT' => Loc::getMessage('STORE_DOCUMENTS_SETTINGS_BUTTON_TITLE'),
			'SORT' => 60,
			'IS_ACTIVE' => in_array($requestUrl, $settingsUrls),
			'ID' => 'settings',
			'PARENT_ID' => '',
			'ITEMS' => $settingsItems,
		];

		$url = $this->getUrlToDocumentType(\CatalogStoreDocumentListComponent::OTHER_MODE);
		$buttons[] = [
			'ID' => 'other_docs',
			'TEXT' => Loc::getMessage('STORE_DOCUMENTS_OTHER_BUTTON_TITLE'),
			'URL' => $url,
			'SORT' => 70,
			'IS_ACTIVE' => $url === $requestUrl,
			'IS_DISABLED' => true,
		];
		return $buttons;
	}

	private function getUrlToDocumentType($type): string
	{
		$pathToDocumentList = $this->arParams['PATH_TO']['LIST'] ?? '';
		if ($pathToDocumentList === '')
		{
			return $pathToDocumentList;
		}

		$baseUrl = str_replace('#DOCUMENT_TYPE#', $type, $pathToDocumentList);
		return $this->getUrlWithParams($baseUrl);
	}

	private function getUrlWithParams($baseUrl): string
	{
		$url = new \Bitrix\Main\Web\Uri($baseUrl);
		if (!Catalog\Component\UseStore::isUsed())
		{
			$url->addParams([
				Catalog\Component\UseStore::URL_PARAM_STORE_MASTER_HIDE => 'Y'
			]);
		}

		if ($this->analyticsSource !== '')
		{
			$url->addParams([
				'inventoryManagementSource' => $this->analyticsSource,
			]);
		}

		return $this->getUrlWithFrameParams($url);
	}

	private function getUrlWithFrameParams($baseUrl): string
	{
		$url = new \Bitrix\Main\Web\Uri($baseUrl);
		if ($this->isIframe)
		{
			$url->addParams([
				'IFRAME' => 'Y',
				'IFRAME_TYPE' => 'SIDE_SLIDER'
			]);
		}

		return $url->getUri();
	}

	private function compareUrls($requestUrlSource, $comparedUrlSource): bool
	{
		$requestUrl = new \Bitrix\Main\Web\Uri($requestUrlSource);
		$comparedUrl = new \Bitrix\Main\Web\Uri($comparedUrlSource);

		return $requestUrl->getPath() === $comparedUrl->getPath();
	}

	private static function checkDocumentReadRights(): bool
	{
		return \Bitrix\Main\Engine\CurrentUser::get()->canDoOperation('catalog_read');
	}
}
