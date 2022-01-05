<?php

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Sale\Internals\FacebookConversionParamsTable;
use Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class SaleFacebookConversion extends CBitrixComponent implements Controllerable, Errorable
{
	use ErrorableImplementation;

	public function executeComponent()
	{
		if ($this->checkEventName() && $this->checkModules() && $this->checkPermissions())
		{
			global $APPLICATION;
			$APPLICATION->SetTitle(GetMessage('FACEBOOK_CONVERSION_TITLE'));
			$this->fillResult();
			$this->includeComponentTemplate();
		}

		$this->showErrors();
	}

	private function showErrors(): void
	{
		foreach ($this->getErrors() as $error)
		{
			ShowError($error);
		}
	}

	private function checkEventName(): bool
	{
		if (!$this->getEventName())
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Event name not found.');

			return false;
		}

		return true;
	}

	private function checkModules(): bool
	{
		if (!Loader::includeModule('sale'))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Module "sale" is not installed.');

			return false;
		}

		if (!Loader::includeModule('seo'))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Module "seo" is not installed.');

			return false;
		}

		return true;
	}

	private function checkPermissions(): bool
	{
		if (!CurrentUser::get()->isAdmin())
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Access Denied.', 'permissions');

			return false;
		}

		return true;
	}

	private function fillResult(): void
	{
		$this->arResult['eventName'] = $this->getEventName();
		$this->arResult['facebookBusinessParams'] = $this->getFacebookBusinessParams();
		$this->arResult['shops'] = $this->getShopsConversionData();
		$this->arResult['conversionDataLabelsText'] = $this->getConversionDataLabelsText();
		$this->arResult['title'] = $this->getTitle();
	}

	private function getEventName(): ?string
	{
		return $this->arParams['eventName'] ?? null;
	}

	private function getFacebookBusinessParams(): array
	{
		if ($service = ServiceLocator::getInstance()->get('seo.business.service'))
		{
			$installed = $service::getAuthAdapter($service::FACEBOOK_TYPE)->hasAuth();
			$profile = $installed ? $service->getAccount($service::FACEBOOK_TYPE)->getProfile() : null;
			$profileParams = null;
			if ($profile)
			{
				$profileParams = [
					'name' => $profile->getName(),
					'id' => $profile->getId(),
					'link' => $profile->getLink(),
					'picture' => $profile->getPicture(),
					'type' => $profile->getType(),
				];
			}

			return [
				'available' => true,
				'auth' => $installed,
				'profile' => $profileParams
			];
		}

		return [
			'available' => false,
			'auth' => false,
			'profile' => null
		];
	}

	private function getShopsConversionData(): array
	{
		$conversionShops = [];
		$shops = $this->getShops();
		foreach ($shops as $shop)
		{
			$facebookConversionParams = FacebookConversionParamsTable::getList([
				'filter' => [
					'EVENT_NAME' => $this->getEventName(),
					'LID' => $shop['LID'],
				],
			])->fetch();

			if ($facebookConversionParams)
			{
				$params = unserialize($facebookConversionParams['PARAMS'], ['allowedClasses' => false]);
			}
			else
			{
				$params = $this->getDefaultConversionParams();
				FacebookConversionParamsTable::add([
					'EVENT_NAME' => $this->getEventName(),
					'LID' => $shop['LID'],
					'ENABLED' => 'N',
					'PARAMS' => serialize($params),
				]);
			}

			$conversionShops[$shop['LID']] = [
				'name' => $shop['NAME'],
				'enabled' => $facebookConversionParams['ENABLED'] ?? 'N',
				'params' => $params,
			];
		}

		return $conversionShops;
	}

	private function getShops(): array
	{
		$shops = [];
		$siteIterator = \Bitrix\Main\SiteTable::getList([
			'select' => ['LID', 'NAME', 'SORT'],
			'order' => ['SORT' => 'ASC'],
		]);
		while ($site = $siteIterator->fetch())
		{
			$saleSite = \Bitrix\Main\Config\Option::get('sale', 'SHOP_SITE_'.$site['LID']);
			if ($site['LID'] === $saleSite)
			{
				$shops[] = $site;
			}
		}

		return $shops;
	}

	private function getTitle(): string
	{
		$eventName = $this->getEventName();
		switch ($eventName)
		{
			case 'AddToCart':
				return GetMessage('FACEBOOK_CONVERSION_EVENT_TITLE_ADD_TO_CART');
			case 'InitiateCheckout':
				return GetMessage('FACEBOOK_CONVERSION_EVENT_TITLE_INITIATE_CHECKOUT');
			case 'AddPaymentInfo':
				return GetMessage('FACEBOOK_CONVERSION_EVENT_TITLE_ADD_PAYMENT_INFO');
			case 'CustomizeProduct':
				return GetMessage('FACEBOOK_CONVERSION_EVENT_TITLE_CUSTOMIZE_PRODUCT');
			case 'Contact':
				return GetMessage('FACEBOOK_CONVERSION_EVENT_TITLE_CONTACT');
			default:
				return '';
		}
	}

	private function getDefaultConversionParams(): array
	{
		$eventName = $this->getEventName();
		switch ($eventName)
		{
			case 'AddToCart':
				return [
					'id' => 'Y',
					'name' => 'N',
					'group' => 'N',
					'price' => 'N',
					'quantity' => 'N',
				];
			case 'InitiateCheckout':
				return [
					'ids' => 'Y',
					'productsGroupAndQuantity' => 'N',
					'price' => 'N',
					'quantity' => 'N',
				];
			case 'AddPaymentInfo':
				return [
					'ids' => 'Y',
					'productsGroupAndQuantity' => 'N',
					'price' => 'N',
				];
			case 'CustomizeProduct':
				return [
					'id' => 'Y',
					'nameAndProperties' => 'N',
				];
			case 'Contact':
				return [
					'socialNetwork' => 'N',
					'email' => 'N',
				];
			default:
				return [];
		}
	}

	private function getConversionDataLabelsText(): array
	{
		$eventName = $this->getEventName();
		switch ($eventName)
		{
			case 'AddToCart':
				return [
					'id' => GetMessage('FACEBOOK_CONVERSION_LABEL_SEND_ID'),
					'name' => GetMessage('FACEBOOK_CONVERSION_LABEL_ADD_TO_CART_SEND_NAME'),
					'group' => GetMessage('FACEBOOK_CONVERSION_LABEL_ADD_TO_CART_SEND_GROUP'),
					'price' => GetMessage('FACEBOOK_CONVERSION_LABEL_ADD_TO_CART_SEND_PRICE'),
					'quantity' => GetMessage('FACEBOOK_CONVERSION_LABEL_ADD_TO_CART_SEND_QUANTITY'),
				];
			case 'InitiateCheckout':
				return [
					'ids' => GetMessage('FACEBOOK_CONVERSION_LABEL_INITIATE_CHECKOUT_SEND_ID'),
					'productsGroupAndQuantity' => GetMessage('FACEBOOK_CONVERSION_LABEL_INITIATE_CHECKOUT_SEND_PRODUCTS_GROUP_AND_QUANTITY'),
					'price' => GetMessage('FACEBOOK_CONVERSION_LABEL_INITIATE_CHECKOUT_SEND_ORDER_PRICE'),
					'quantity' => GetMessage('FACEBOOK_CONVERSION_LABEL_INITIATE_CHECKOUT_SEND_ORDER_PRODUCTS_TOTAL_QUANTITY'),
				];
			case 'AddPaymentInfo':
				return [
					'ids' => GetMessage('FACEBOOK_CONVERSION_LABEL_INITIATE_CHECKOUT_SEND_ID'),
					'productsGroupAndQuantity' => GetMessage('FACEBOOK_CONVERSION_LABEL_INITIATE_CHECKOUT_SEND_PRODUCTS_GROUP_AND_QUANTITY'),
					'price' => GetMessage('FACEBOOK_CONVERSION_LABEL_INITIATE_CHECKOUT_SEND_ORDER_PRICE'),
				];
			case 'CustomizeProduct':
				return [
					'id' => GetMessage('FACEBOOK_CONVERSION_LABEL_SEND_ID'),
					'nameAndProperties' => GetMessage('FACEBOOK_CONVERSION_LABEL_CUSTOMIZE_PRODUCT_SEND_NAME_AND_PROPERTIES'),
				];
			case 'Contact':
				return [
					'socialNetwork' => GetMessage('FACEBOOK_CONVERSION_LABEL_CONTACT_SEND_SOCIAL_NETWORK'),
					'email' => GetMessage('FACEBOOK_CONVERSION_LABEL_CONTACT_SEND_EMAIL'),
				];
			default:
				return [];
		}
	}

	public function changeParamStateAction(string $eventName, string $shopId, string $paramName, string $state): void
	{
		if ($paramName === 'id' || $paramName === 'ids' || !$this->checkModules() || !$this->checkPermissions())
		{
			return;
		}

		$facebookConversionParams = FacebookConversionParamsTable::getList([
			'filter' => [
				'EVENT_NAME' => $eventName,
				'LID' => $shopId,
			],
		])->fetch();

		if ($facebookConversionParams)
		{
			$params = unserialize($facebookConversionParams['PARAMS'], ['allow_classes' => false]);
			$params[$paramName] = $state;
			$serializedParams = serialize($params);
			FacebookConversionParamsTable::update(
				$facebookConversionParams['ID'],
				[
					'PARAMS' => $serializedParams,
				]
			);
		}
	}

	public function changeShopEnabledStateAction(string $eventName, string $shopId, string $enabled): void
	{
		if (!$this->checkModules() || !$this->checkPermissions())
		{
			return;
		}

		$facebookConversionParams = FacebookConversionParamsTable::getList([
			'filter' => [
				'EVENT_NAME' => $eventName,
				'LID' => $shopId,
			],
		])->fetch();

		if ($facebookConversionParams)
		{
			FacebookConversionParamsTable::update(
				$facebookConversionParams['ID'],
				[
					'ENABLED' => $enabled,
				]
			);
		}

		if ($this->isFacebookConversionEventEnabled($eventName))
		{
			$this->registerEventHandler($eventName);
		}
		else
		{
			$this->unregisterEventHandler($eventName);
		}
	}

	private function isFacebookConversionEventEnabled(string $eventName): bool
	{
		$count = FacebookConversionParamsTable::getCount([
			'EVENT_NAME' => $eventName,
			'ENABLED' => 'Y'
		]);

		return $count > 0;
	}

	private function registerEventHandler(string $eventName): void
	{
		switch ($eventName)
		{
			case 'AddToCart':
				$this->registerAddToCartEventHandler();
				break;
			case 'InitiateCheckout':
				$this->registerInitiateCheckoutEventHandler();
				break;
			case 'AddPaymentInfo':
				$this->registerAddPaymentInfoEventHandler();
				break;
			case 'Contact':
				$this->registerContactEventHandler();
				break;
		}
	}

	private function unregisterEventHandler(string $eventName): void
	{
		switch ($eventName)
		{
			case 'AddToCart':
				$this->unregisterAddToCartEventHandler();
				break;
			case 'InitiateCheckout':
				$this->unregisterInitiateCheckoutEventHandler();
				break;
			case 'AddPaymentInfo':
				$this->unregisterAddPaymentInfoEventHandler();
				break;
			case 'Contact':
				$this->unregisterContactEventHandler();
				break;
		}
	}

	private function registerAddToCartEventHandler(): void
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler(
			'sale',
			'OnBasketAdd',
			'sale',
			'\Bitrix\Sale\Internals\FacebookConversion',
			'onAddToCartHandler'
		);
	}

	private function unregisterAddToCartEventHandler(): void
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unregisterEventHandler(
			'sale',
			'OnBasketAdd',
			'sale',
			'\Bitrix\Sale\Internals\FacebookConversion',
			'onAddToCartHandler'
		);
	}

	private function registerInitiateCheckoutEventHandler(): void
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler(
			'sale',
			'OnSaleComponentOrderCreated',
			'sale',
			'\Bitrix\Sale\Internals\FacebookConversion',
			'onOrderCreatedHandler'
		);
	}

	private function unregisterInitiateCheckoutEventHandler(): void
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unregisterEventHandler(
			'sale',
			'OnSaleComponentOrderCreated',
			'sale',
			'\Bitrix\Sale\Internals\FacebookConversion',
			'onOrderCreatedHandler'
		);
	}

	private function registerAddPaymentInfoEventHandler(): void
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler(
			'sale',
			'OnSaleOrderSaved',
			'sale',
			'\Bitrix\Sale\Internals\FacebookConversion',
			'onOrderSavedHandler'
		);
	}

	private function unregisterAddPaymentInfoEventHandler(): void
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unregisterEventHandler(
			'sale',
			'OnSaleOrderSaved',
			'sale',
			'\Bitrix\Sale\Internals\FacebookConversion',
			'onOrderSavedHandler'
		);
	}

	private function registerContactEventHandler(): void
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler(
			'main',
			'onFeedbackFormSubmit',
			'sale',
			'\Bitrix\Sale\Internals\FacebookConversion',
			'onFeedbackFormContactHandler'
		);
	}

	private function unregisterContactEventHandler(): void
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unregisterEventHandler(
			'main',
			'onFeedbackFormSubmit',
			'sale',
			'\Bitrix\Sale\Internals\FacebookConversion',
			'onFeedbackFormContactHandler'
		);
	}

	public function logoutAction(): void
	{
		if (!$this->checkModules() || !$this->checkPermissions())
		{
			return;
		}

		($service = ServiceLocator::getInstance()->get('seo.business.service'))
			::getAuthAdapter($service::FACEBOOK_TYPE)->removeAuth();
	}

	public function configureActions(): array
	{
		return [];
	}
}
