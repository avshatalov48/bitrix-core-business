<?php

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Sale\Internals\FacebookConversion;
use Bitrix\Sale\Internals\FacebookConversionParamsTable;
use Bitrix\Main\Loader;
use Bitrix\Sale\ShopSitesController;
use Bitrix\Seo\BusinessSuite\Service;
use Bitrix\Seo\Conversion\Facebook;
use Bitrix\Sale\Internals\Analytics;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class SaleFacebookConversion extends CBitrixComponent implements Controllerable, Errorable
{
	use ErrorableImplementation;

	private $eventName;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new ErrorCollection();
	}

	public function executeComponent()
	{
		$this->setEventName($this->arParams['eventName']);
		if ($this->checkComponent())
		{
			global $APPLICATION;
			$APPLICATION->SetTitle(GetMessage('FACEBOOK_CONVERSION_TITLE'));
			$this->fillResult();
			$this->includeComponentTemplate();
		}

		$this->showErrors();
	}

	private function checkComponent(): bool
	{
		return $this->checkEventName() && $this->checkModules() && $this->checkPermissions();
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
		if (!in_array($this->getEventName(), $this->getAllowedEventNames(), true))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(GetMessage('FACEBOOK_CONVERSION_ERROR_EVENT_NAME'));

			return false;
		}

		return true;
	}

	private function getAllowedEventNames(): array
	{
		return [
			Facebook\Event::EVENT_ADD_TO_CART,
			Facebook\Event::EVENT_INITIATE_CHECKOUT,
			Facebook\Event::EVENT_ADD_PAYMENT,
			Facebook\Event::EVENT_DONATE,
			Facebook\Event::EVENT_CONTACT,
		];
	}

	private function checkModules(): bool
	{
		if (!Loader::includeModule('sale'))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(GetMessage('FACEBOOK_CONVERSION_ERROR_MODULE_SALE'));

			return false;
		}

		if (!Loader::includeModule('seo'))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(GetMessage('FACEBOOK_CONVERSION_ERROR_MODULE_SEO'));

			return false;
		}

		return true;
	}

	private function checkPermissions(): bool
	{
		if (!CurrentUser::get()->isAdmin())
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(GetMessage('FACEBOOK_CONVERSION_ERROR_ACCESS_DENIED'));

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

	private function setEventName(string $eventName): void
	{
		$this->eventName = $eventName;
	}

	private function getEventName(): ?string
	{
		return $this->eventName;
	}

	private function getFacebookBusinessParams(): array
	{
		/** @var Service $service */
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

		$shops = ShopSitesController::getShops();
		$facebookConversionParamsData = $this->getFacebookConversionParamsDataForCurrentEvent();
		foreach ($shops as $shop)
		{
			$currentParamsData = $facebookConversionParamsData[$shop['LID']] ?? null;

			if ($currentParamsData)
			{
				$params = unserialize($currentParamsData['PARAMS'], ['allowed_classes' => false]);
			}
			else
			{
				$params = $this->getDefaultConversionParams();
			}

			$conversionShops[$shop['LID']] = [
				'name' => $shop['NAME'],
				'enabled' => $currentParamsData['ENABLED'] ?? 'N',
				'params' => $params,
			];
		}

		return $conversionShops;
	}

	private function getFacebookConversionParamsDataForCurrentEvent(): array
	{
		$facebookConversionParamsData = [];

		$iterator = FacebookConversionParamsTable::getList([
			'select' => ['LID', 'ENABLED', 'PARAMS'],
			'filter' => [
				'=EVENT_NAME' => $this->getEventName(),
			],
		]);

		while ($currentParams = $iterator->fetch())
		{
			$facebookConversionParamsData[$currentParams['LID']] = [
				'ENABLED' => $currentParams['ENABLED'],
				'PARAMS' => $currentParams['PARAMS'],
			];
		}

		return $facebookConversionParamsData;
	}

	private function getTitle(): string
	{
		$eventName = $this->getEventName();
		switch ($eventName)
		{
			case Facebook\Event::EVENT_ADD_TO_CART:
				return GetMessage('FACEBOOK_CONVERSION_EVENT_TITLE_ADD_TO_CART');
			case Facebook\Event::EVENT_INITIATE_CHECKOUT:
				return GetMessage('FACEBOOK_CONVERSION_EVENT_TITLE_INITIATE_CHECKOUT');
			case Facebook\Event::EVENT_ADD_PAYMENT:
				return GetMessage('FACEBOOK_CONVERSION_EVENT_TITLE_ADD_PAYMENT_INFO');
			case Facebook\Event::EVENT_DONATE:
				return GetMessage('FACEBOOK_CONVERSION_EVENT_TITLE_CUSTOMIZE_PRODUCT');
			case Facebook\Event::EVENT_CONTACT:
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
			case Facebook\Event::EVENT_ADD_TO_CART:
				return [
					'id' => 'Y',
					'name' => 'N',
					'group' => 'N',
					'price' => 'N',
					'quantity' => 'N',
				];
			case Facebook\Event::EVENT_INITIATE_CHECKOUT:
				return [
					'ids' => 'Y',
					'productsGroupAndQuantity' => 'N',
					'price' => 'N',
					'quantity' => 'N',
				];
			case Facebook\Event::EVENT_ADD_PAYMENT:
				return [
					'ids' => 'Y',
					'productsGroupAndQuantity' => 'N',
					'price' => 'N',
				];
			case Facebook\Event::EVENT_DONATE:
				return [
					'id' => 'Y',
					'nameAndProperties' => 'N',
				];
			case Facebook\Event::EVENT_CONTACT:
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
			case Facebook\Event::EVENT_ADD_TO_CART:
				return [
					FacebookConversion::ID => GetMessage('FACEBOOK_CONVERSION_LABEL_SEND_ID'),
					FacebookConversion::NAME => GetMessage('FACEBOOK_CONVERSION_LABEL_ADD_TO_CART_SEND_NAME'),
					FacebookConversion::GROUP => GetMessage('FACEBOOK_CONVERSION_LABEL_ADD_TO_CART_SEND_GROUP'),
					FacebookConversion::PRICE => GetMessage('FACEBOOK_CONVERSION_LABEL_ADD_TO_CART_SEND_PRICE'),
					FacebookConversion::QUANTITY => GetMessage('FACEBOOK_CONVERSION_LABEL_ADD_TO_CART_SEND_QUANTITY'),
				];
			case Facebook\Event::EVENT_INITIATE_CHECKOUT:
				return [
					FacebookConversion::IDS => GetMessage('FACEBOOK_CONVERSION_LABEL_INITIATE_CHECKOUT_SEND_ID'),
					FacebookConversion::PRODUCTS_GROUP_AND_QUANTITY => GetMessage('FACEBOOK_CONVERSION_LABEL_INITIATE_CHECKOUT_SEND_PRODUCTS_GROUP_AND_QUANTITY'),
					FacebookConversion::PRICE => GetMessage('FACEBOOK_CONVERSION_LABEL_INITIATE_CHECKOUT_SEND_ORDER_PRICE'),
					FacebookConversion::QUANTITY => GetMessage('FACEBOOK_CONVERSION_LABEL_INITIATE_CHECKOUT_SEND_ORDER_PRODUCTS_TOTAL_QUANTITY'),
				];
			case Facebook\Event::EVENT_ADD_PAYMENT:
				return [
					FacebookConversion::IDS => GetMessage('FACEBOOK_CONVERSION_LABEL_INITIATE_CHECKOUT_SEND_ID'),
					FacebookConversion::PRODUCTS_GROUP_AND_QUANTITY => GetMessage('FACEBOOK_CONVERSION_LABEL_INITIATE_CHECKOUT_SEND_PRODUCTS_GROUP_AND_QUANTITY'),
					FacebookConversion::PRICE => GetMessage('FACEBOOK_CONVERSION_LABEL_INITIATE_CHECKOUT_SEND_ORDER_PRICE'),
				];
			case Facebook\Event::EVENT_DONATE:
				return [
					FacebookConversion::ID => GetMessage('FACEBOOK_CONVERSION_LABEL_SEND_ID'),
					FacebookConversion::NAME_AND_PROPERTIES => GetMessage('FACEBOOK_CONVERSION_LABEL_CUSTOMIZE_PRODUCT_SEND_NAME_AND_PROPERTIES'),
				];
			case Facebook\Event::EVENT_CONTACT:
				return [
					FacebookConversion::SOCIAL_NETWORK => GetMessage('FACEBOOK_CONVERSION_LABEL_CONTACT_SEND_SOCIAL_NETWORK'),
					FacebookConversion::EMAIL => GetMessage('FACEBOOK_CONVERSION_LABEL_CONTACT_SEND_EMAIL'),
				];
			default:
				return [];
		}
	}

	public function changeParamStateAction(string $eventName, string $shopId, string $paramName, string $state): void
	{
		$this->setEventName($eventName);
		if ($paramName === 'id' || $paramName === 'ids' || !$this->checkComponent())
		{
			return;
		}

		$facebookConversionParams = FacebookConversionParamsTable::getList([
			'select' => ['ID', 'PARAMS'],
			'filter' => [
				'=EVENT_NAME' => $eventName,
				'=LID' => $shopId,
			],
		])->fetch();

		if ($facebookConversionParams)
		{
			$params = unserialize($facebookConversionParams['PARAMS'], ['allowed_classes' => false]);
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
		$this->setEventName($eventName);
		if (!$this->checkComponent())
		{
			return;
		}

		$facebookConversionParams = FacebookConversionParamsTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=EVENT_NAME' => $eventName,
				'=LID' => $shopId,
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
		else
		{
			FacebookConversionParamsTable::add([
				'EVENT_NAME' => $this->getEventName(),
				'LID' => $shopId,
				'ENABLED' => $enabled,
				'PARAMS' => serialize($this->getDefaultConversionParams()),
			]);
		}

		$name =
			$enabled
				? Analytics\Events\Event::FACEBOOK_CONVERSION_SHOP_EVENT_ENABLED
				: Analytics\Events\Event::FACEBOOK_CONVERSION_SHOP_EVENT_DISABLED
		;
		$analyticsEvent = new Analytics\Events\Event(
			$name,
			[
				'EVENT_NAME' => $this->getEventName(),
			]
		);
		$provider = new Analytics\Events\Provider($analyticsEvent);
		(new Analytics\Storage($provider))->save();

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
			'=EVENT_NAME' => $eventName,
			'=ENABLED' => 'Y'
		]);

		return $count > 0;
	}

	private function registerEventHandler(string $eventName): void
	{
		switch ($eventName)
		{
			case Facebook\Event::EVENT_ADD_TO_CART:
				$this->registerAddToCartEventHandler();
				break;
			case Facebook\Event::EVENT_INITIATE_CHECKOUT:
				$this->registerInitiateCheckoutEventHandler();
				break;
			case Facebook\Event::EVENT_ADD_PAYMENT:
				$this->registerAddPaymentInfoEventHandler();
				break;
			case Facebook\Event::EVENT_CONTACT:
				$this->registerContactEventHandler();
				break;
		}
	}

	private function unregisterEventHandler(string $eventName): void
	{
		switch ($eventName)
		{
			case Facebook\Event::EVENT_ADD_TO_CART:
				$this->unregisterAddToCartEventHandler();
				break;
			case Facebook\Event::EVENT_INITIATE_CHECKOUT:
				$this->unregisterInitiateCheckoutEventHandler();
				break;
			case Facebook\Event::EVENT_ADD_PAYMENT:
				$this->unregisterAddPaymentInfoEventHandler();
				break;
			case Facebook\Event::EVENT_CONTACT:
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
