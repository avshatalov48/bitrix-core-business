<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Integration\Report\Dashboard\DashboardManager;
use Bitrix\Catalog\Integration\Report\Dashboard\StoreStockDashboard;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;
use Bitrix\Catalog;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\v2\Contractor;

Main\Loader::includeModule('catalog');

class CatalogStoreDocumentControlPanelComponent extends \CBitrixComponent
{
	public const PATH_TO = [
		'LIST' => '/shop/documents/#DOCUMENT_TYPE#/',
		'STORES' => '/shop/documents-stores/',
		'CATALOG' => '/shop/documents-catalog/',
		'CONTRACTORS' => '/shop/documents/contractors/',
		'CONTRACTORS_CONTACTS' => '/shop/documents/contractors_contacts/',
	];

	private $isIframe = false;
	private $analyticsSource = '';
	private AccessController $accessController;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->accessController = AccessController::getCurrent();
	}

	public function onPrepareComponentParams($arParams)
	{
		if (empty($arParams['PATH_TO']) || !is_array($arParams['PATH_TO']))
		{
			$arParams['PATH_TO'] = [];
		}

		$arParams['PATH_TO'] += self::PATH_TO;

		return parent::onPrepareComponentParams($arParams);
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

		if ($this->isIframe)
		{
			$url->addParams([
				'IFRAME' => 'Y',
				'IFRAME_TYPE' => 'SIDE_SLIDER'
			]);
		}

		return $url->getUri();
	}

	private function isActiveUrl(string $comparedUrlSource): bool
	{
		$requestUrl = new \Bitrix\Main\Web\Uri($this->request->getRequestUri());
		$comparedUrl = new \Bitrix\Main\Web\Uri($comparedUrlSource);

		return $requestUrl->getPath() === $comparedUrl->getPath();
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
		if (!$this->accessController->check(ActionDictionary::ACTION_INVENTORY_MANAGEMENT_ACCESS))
		{
			return [];
		}

		$buttons = $this->getPanelButtonsStoreDocuments();
		$buttons[] = $this->getPanelButtonProducts();

		if (
			$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)
			|| $this->accessController->check(ActionDictionary::ACTION_CATALOG_VIEW)
		)
		{
			$buttons[] = $this->getPanelButtonAnalytics();
		}

		array_push($buttons, ... $this->getPanelButtonsSettings());
		array_push($buttons, ... $this->getPanelButtonsOther());

		$buttons = array_filter($buttons, static fn($item) => !is_null($item));

		return $buttons;
	}

	private function getPanelButtonsStoreDocuments(): array
	{
		$buttons = [];

		\CBitrixComponent::includeComponentClass('bitrix:catalog.store.document.list');

		$documents = [
			[
				'ID' => 'arrival_docs',
				'TEXT' => Loc::getMessage('STORE_DOCUMENTS_ARRIVAL_BUTTON_TITLE'),
				'URL_TYPE' => \CatalogStoreDocumentListComponent::ARRIVAL_MODE,
				'SORT' => 10,
			],
			// this was `sales_order_docs`
			[
				'ID' => 'moving_docs',
				'TEXT' => Loc::getMessage('STORE_DOCUMENTS_MOVING_BUTTON_TITLE'),
				'URL_TYPE' => \CatalogStoreDocumentListComponent::MOVING_MODE,
				'SORT' => 30,
			],
			[
				'ID' => 'deduct_docs',
				'TEXT' => Loc::getMessage('STORE_DOCUMENTS_DEDUCT_BUTTON_TITLE'),
				'URL_TYPE' => \CatalogStoreDocumentListComponent::DEDUCT_MODE,
				'SORT' => 40,
			],
		];

		if (Main\Loader::includeModule('crm'))
		{
			array_splice($documents, 1, 0, [
				[
					'ID' => 'sales_order_docs',
					'TEXT' => Loc::getMessage('STORE_DOCUMENTS_SALES_ORDER_BUTTON_TITLE'),
					'URL_TYPE' => 'sales_order',
					'SORT' => 20,
				],
			]);
		}

		foreach ($documents as $item)
		{
			$item['URL'] = $this->getUrlToDocumentType($item['URL_TYPE']);
			$item['IS_ACTIVE'] = $this->isActiveUrl($item['URL']);
			unset(
				$item['URL_TYPE']
			);

			$buttons[] = $item;
		}

		if (Contractor\Provider\Manager::isActiveProviderByModule('crm'))
		{
			$clientsMenuItem = $this->getCrmClientsMenuItem();
			if ($clientsMenuItem)
			{
				$buttons[] = $clientsMenuItem;
			}
		}

		return $buttons;
	}

	private function getPanelButtonProducts(): ?array
	{
		$result = null;

		$urlBuilder = Iblock\Url\AdminPage\BuilderManager::getInstance()->getBuilder(
			Catalog\Url\InventoryBuilder::TYPE_ID
		);
		if (isset($urlBuilder))
		{
			$catalogPath = $this->arParams['PATH_TO']['CATALOG'] ?? '';
			if ($catalogPath !== '')
			{
				$urlBuilder->setPrefix($catalogPath);
			}
			$rawUrl = $urlBuilder->getPrefix();
			$url = $this->getUrlWithParams($rawUrl);

			$result = [
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

		return $result;
	}

	private function getPanelButtonAnalytics(): ?array
	{
		$sliderPath = \CComponentEngine::makeComponentPath('bitrix:catalog.warehouse.master.clear');
		$sliderPath = getLocalPath('components' . $sliderPath . '/slider.php');

		$masterSliderSettings = CUtil::PhpToJSObject([
			'data' => [
				'openGridOnDone' => false,
				'closeSliderOnMarketplace' => false,
			],
		]);

		if (Main\Loader::includeModule('report'))
		{
			if (\Bitrix\Catalog\Component\UseStore::isUsed())
			{
				$allowedDashboards = DashboardManager::getManager()->getAllowedDashboards();
				if (!$allowedDashboards)
				{
					return null;
				}

				$linkKey = '';
				foreach ($allowedDashboards as $board)
				{
					$linkKey = $board->getBoardKey();
					if ($linkKey === StoreStockDashboard::BOARD_KEY)
					{
						break;
					}
				}

				$url = '/report/analytics/?analyticBoardKey=' . $linkKey;
				return [
					'ID' => 'analytics',
					'TEXT' => Loc::getMessage('STORE_DOCUMENTS_ANALYTICS_BUTTON_TITLE'),
					'URL' => $url,
					'SORT' => 45,
					'IS_ACTIVE' => $this->isActiveUrl($url),
				];
			}
			else
			{
				return [
					'ID' => 'analytics',
					'TEXT' => Loc::getMessage('STORE_DOCUMENTS_ANALYTICS_BUTTON_TITLE'),
					'SORT' => 45,
					'ON_CLICK' => 'new BX.Catalog.Store.Document.ControlPanel().storeMasterOpenSlider(\''.$sliderPath.'\', ' . $masterSliderSettings . ');',
				];
			}
		}

		return null;
	}

	private function getPanelButtonsSettings(): array
	{
		$settingsButton = null;
		$accessRightsButton = null;

		if (
			Main\Loader::includeModule('crm')
			&& !\CCrmSaleHelper::isWithOrdersMode()
		)
		{
			Main\UI\Extension::load(['crm.config.catalog']);

			$settingsButton = [
				'TEXT' => Loc::getMessage('STORE_DOCUMENTS_SETTINGS_BUTTON_TITLE'),
				'SORT' => 60,
				'ID' => 'settings',
				'PARENT_ID' => '',
				'ON_CLICK' => 'BX.Crm.Config.Catalog.Slider.open(\'' . CUtil::JSEscape($this->analyticsSource) . '\');',
			];
		}

		Main\UI\Extension::load('sidepanel');

		$accessRightsButton = [
			'ID' => 'access_rights',
			'PARENT_ID' => '',
			'TEXT' => Loc::getMessage('STORE_DOCUMENTS_ACCESS_RIGHT_BUTTON_TITLE'),
			'SORT' => 65,
		];

		if (Catalog\Config\Feature::isAccessControllerCheckingEnabled())
		{
			$accessRightsButton['ON_CLICK'] = "BX.SidePanel.Instance.open('" . \CUtil::JSEscape('/shop/settings/permissions/') . "')";
		}
		else
		{
			$helpLink = Catalog\Config\Feature::getAccessControllerHelpLink();
			if (!empty($helpLink))
			{
				$accessRightsButton['IS_LOCKED'] = true;
				$accessRightsButton['ON_CLICK'] = $helpLink['LINK'];
			}
			else
			{
				$accessRightsButton = null;
			}
		}

		$result = [];

		if ($settingsButton && $accessRightsButton)
		{
			$rootSettingsButton = $settingsButton;
			$rootSettingsButton['ID'] = 'settings_root';

			$settingsButton['TEXT'] = Loc::getMessage('STORE_DOCUMENTS_SETTINGS_CHILD_BUTTON_TITLE');
			$settingsButton['PARENT_ID'] = 'settings_root';

			$accessRightsButton['PARENT_ID'] = 'settings_root';

			$rootSettingsButton['ITEMS'] = [
				$settingsButton,
				$accessRightsButton,
			];
			$result[] = $rootSettingsButton;
		}
		else
		{
			$result = array_filter([
				$settingsButton,
				$accessRightsButton,
			]);
		}

		return $result;
	}

	private function getPanelButtonsOther(): array
	{
		$buttons = [];

		$url = $this->getUrlWithParams($this->arParams['PATH_TO']['STORES']);
		$buttons[] = [
			'ID' => 'stores_settings',
			'TEXT' => Loc::getMessage('STORE_DOCUMENTS_SETTINGS_STORES_TITLE'),
			'URL' => $url,
			'SORT' => 80,
			'IS_ACTIVE' => $this->isActiveUrl($url),
			'IS_DISABLED' => true,
		];

		if (!Contractor\Provider\Manager::isActiveProviderByModule('crm'))
		{
			$url = $this->getUrlWithParams($this->arParams['PATH_TO']['CONTRACTORS']);
			$buttons[] = [
				'ID' => 'contractors_settings',
				'TEXT' => Loc::getMessage('STORE_DOCUMENTS_SETTINGS_CONTRACTORS_TITLE'),
				'URL' => $url,
				'SORT' => 90,
				'IS_ACTIVE' => $this->isActiveUrl($url),
				'IS_DISABLED' => true,
			];
		}

		if (
			Main\Loader::includeModule('rest')
			&& $this->accessController->check(ActionDictionary::ACTION_CATALOG_IMPORT_EXECUTION)
		)
		{
			$url = '/marketplace/?tag[0]=migrator&tag[1]=inventory';
			$buttons[] = [
				'ID' => 'transfer_data',
				'TEXT' => Loc::getMessage('STORE_DOCUMENTS_TRANSFER_DATA_TITLE'),
				'URL' => $url,
				'SORT' => 100,
				'IS_ACTIVE' => $this->isActiveUrl($url),
				'IS_DISABLED' => true,
			];
		}

		return $buttons;
	}

	/**
	 * @return array|null
	 */
	private function getCrmClientsMenuItem(): ?array
	{
		$crmControlPanelResult = $GLOBALS['APPLICATION']->includeComponent(
			'bitrix:crm.control_panel',
			'',
			[
				'GET_RESULT' => 'Y',
			]
		);

		if (
			!isset($crmControlPanelResult['ITEMS'])
			|| !is_array($crmControlPanelResult['ITEMS'])
		)
		{
			return null;
		}

		$clientsItem = array_filter(
			$crmControlPanelResult['ITEMS'],
			static function ($item)
			{
				return $item['ID'] === CrmControlPanel::MENU_ID_CRM_CLIENT;
			}
		);
		if (!$clientsItem)
		{
			return null;
		}
		$clientsItem = current($clientsItem);

		$clientsItem['ITEMS'] = array_filter(
			$clientsItem['ITEMS'],
			static function ($item)
			{
				return in_array(
					$item['ID'],
					[
						CrmControlPanel::MENU_ID_CRM_CONTACT,
						CrmControlPanel::MENU_ID_CRM_COMPANY,
						CrmControlPanel::MENU_ID_CRM_STORE_CONTRACTORS,
						CrmControlPanel::MENU_ID_CRM_CONTACT_CENTER,
					],
					true
				);
			}
		);
		$clientsItem['ITEMS'] = array_values($clientsItem['ITEMS']);

		$clientsItem['ITEMS'] = array_map(
			function ($item)
			{
				$isCrmItem = in_array(
					$item['ID'],
					[
						CrmControlPanel::MENU_ID_CRM_CONTACT,
						CrmControlPanel::MENU_ID_CRM_COMPANY,
					],
					true
				);
				$isCatalogItem = $item['ID'] === CrmControlPanel::MENU_ID_CRM_STORE_CONTRACTORS;

				if ($isCrmItem)
				{
					$item['ON_CLICK'] = 'event.preventDefault();BX.SidePanel.Instance.open("' . CUtil::JSescape($item['URL']) . '", {cacheable: false});';
				}
				elseif ($isCatalogItem)
				{
					$item['ITEMS'] = array_map(
						function($item)
						{
							unset($item['ON_CLICK']);

							$item['IS_ACTIVE'] = $this->isActiveUrl(
								$item['URL']
							);
							$item['URL'] = $this->getUrlWithParams($item['URL']);

							return $item;
						},
						$item['ITEMS']
					);
				}

				return $item;
			},
			$clientsItem['ITEMS']
		);

		return $clientsItem;
	}
}
