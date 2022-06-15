<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Catalog\Component\SkuTree;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Main\Application;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\Internals\Analytics;
use Bitrix\Seo\BusinessSuite\Service;
use Bitrix\Seo\Conversion\Facebook;

class FacebookConversion
{
	public const ID = 'id';
	public const IDS = 'ids';
	public const NAME = 'name';
	public const GROUP = 'group';
	public const PRICE = 'price';
	public const QUANTITY = 'quantity';
	public const PRODUCTS_GROUP_AND_QUANTITY = 'productsGroupAndQuantity';
	public const NAME_AND_PROPERTIES = 'nameAndProperties';
	public const SOCIAL_NETWORK = 'socialNetwork';
	public const EMAIL = 'email';

	public static function onAddToCartHandler(int $id, array $productData): void
	{
		if (!self::isAllowedRegion())
		{
			return;
		}

		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		$action = $request->get('action');
		if ($action !== 'ADD2BASKET' && $action !== 'BUY')
		{
			return;
		}

		if (!self::checkClass())
		{
			return;
		}

		$params = self::getFacebookConversionParams(Facebook\Event::EVENT_ADD_TO_CART);
		if (!$params)
		{
			return;
		}

		if ($params[self::ID] !== 'Y')
		{
			return;
		}

		$customDataParams = [
			'content_type' => 'product',
		];

		if ($params[self::NAME] === 'Y')
		{
			$customDataParams['content_name'] = $productData['NAME'];
		}
		if ($params[self::GROUP] === 'Y')
		{
			$customDataParams['content_category'] = self::getProductDeepestSection((int)$productData['PRODUCT_ID']);
		}
		if ($params[self::PRICE] === 'Y')
		{
			$customDataParams['value'] = $productData['PRICE'];
			$customDataParams['currency'] = $productData['CURRENCY'];
		}
		if ($params[self::QUANTITY] === 'Y')
		{
			$customDataParams['contents'] = [
				[
					'product_id' => $productData['PRODUCT_ID'],
					'quantity' => $productData['QUANTITY'],
				]
			];
		}
		else
		{
			$customDataParams['content_ids'] = [$productData['PRODUCT_ID']];
		}

		self::fireEvent(Facebook\Event::EVENT_ADD_TO_CART, $customDataParams);
	}

	public static function onOrderCreatedHandler(Order $order): void
	{
		if (!self::isAllowedRegion())
		{
			return;
		}

		$application = \Bitrix\Main\Application::getInstance();
		$session = $application ? $application->getSession() : null;
		$isInitiateCheckoutSent =
			$session
				? $session->has('FACEBOOK_CONVERSION_INITIATE_CHECKOUT_SENT_' . SITE_ID)
				: false
		;
		if (\Bitrix\Main\Context::getCurrent()->getRequest()->isAjaxRequest())
		{
			if (
				$isInitiateCheckoutSent
				&& \Bitrix\Main\Context::getCurrent()->getRequest()->get('action') === 'saveOrderAjax'
			)
			{
				$session->remove('FACEBOOK_CONVERSION_INITIATE_CHECKOUT_SENT_' . SITE_ID);
			}

			return;
		}
		if ($isInitiateCheckoutSent)
		{
			return;
		}
		$session->set('FACEBOOK_CONVERSION_INITIATE_CHECKOUT_SENT_' . SITE_ID, true);

		if (!self::checkClass())
		{
			return;
		}

		$params = self::getFacebookConversionParams(Facebook\Event::EVENT_INITIATE_CHECKOUT);
		if (!$params)
		{
			return;
		}

		if ($params[self::IDS] !== 'Y')
		{
			return;
		}

		$customDataParams = self::getCustomDataParamsForOrderEvent($order, $params);

		if ($params[self::QUANTITY] === 'Y')
		{
			$totalQuantity = 0;
			/** @var BasketItem $basketItem */
			foreach ($order->getBasket()->getBasketItems() as $basketItem)
			{
				$totalQuantity += $basketItem->getQuantity();
			}
			$customDataParams['num_items'] = (int)$totalQuantity;
		}

		self::fireEvent(Facebook\Event::EVENT_INITIATE_CHECKOUT, $customDataParams);
	}

	public static function onOrderSavedHandler(\Bitrix\Main\Event $event): void
	{
		if (!self::isAllowedRegion())
		{
			return;
		}

		if (\Bitrix\Main\Context::getCurrent()->getRequest()->get('action') !== 'saveOrderAjax')
		{
			return;
		}

		/** @var Order $order */
		$order = $event->getParameter('ENTITY');
		if (!$order)
		{
			return;
		}

		if (!self::checkClass())
		{
			return;
		}

		$params = self::getFacebookConversionParams(Facebook\Event::EVENT_ADD_PAYMENT);
		if (!$params)
		{
			return;
		}

		if ($params[self::IDS] !== 'Y')
		{
			return;
		}

		$customDataParams = self::getCustomDataParamsForOrderEvent($order, $params);

		self::fireEvent(Facebook\Event::EVENT_ADD_PAYMENT, $customDataParams);
	}

	public static function getCustomDataParamsForOrderEvent(Order $order, array $params): array
	{
		$customDataParams = [
			'content_type' => 'product',
		];

		if ($params[self::PRODUCTS_GROUP_AND_QUANTITY] === 'Y')
		{
			$products = [];
			foreach ($order->getBasket()->getBasketItems() as $basketItem)
			{
				/** @var BasketItem $basketItem */
				$products[] = [
					'product_id' => $basketItem->getProductId(),
					'category' => self::getProductDeepestSection((int)$basketItem->getProductId()),
					'quantity' => $basketItem->getQuantity(),
				];
			}
			$customDataParams['contents'] = $products;
		}
		else
		{
			$productIds = [];
			foreach ($order->getBasket()->getBasketItems() as $basketItem)
			{
				/** @var BasketItem $basketItem */
				$productIds[] = $basketItem->getProductId();
			}
			$customDataParams['content_ids'] = $productIds;
		}

		if ($params[self::PRICE] === 'Y')
		{
			$customDataParams['value'] = $order->getPrice();
			$customDataParams['currency'] = $order->getCurrency();
		}

		return $customDataParams;
	}

	public static function onFeedbackFormContactHandler(\Bitrix\Main\Event $event): void
	{
		if (!self::isAllowedRegion())
		{
			return;
		}

		$email = $event->getParameter('AUTHOR_EMAIL') ?? '';
		self::fireContactEvent(self::EMAIL, (string)$email);
	}

	public static function onContactHandler($contactBy): void
	{
		if (!self::isAllowedRegion())
		{
			return;
		}

		$type = null;
		$value = null;
		if (is_array($contactBy))
		{
			$type = $contactBy['type'] ?? '';
			$value = $contactBy['value'] ?? '';
		}
		else
		{
			$type = self::EMAIL;
			$value = $contactBy;
		}

		self::fireContactEvent((string)$type, (string)$value);
	}

	private static function fireContactEvent(string $type, string $value): void
	{
		if (!self::checkClass())
		{
			return;
		}

		$params = self::getFacebookConversionParams(Facebook\Event::EVENT_CONTACT);
		if (!$params)
		{
			return;
		}

		if (!isset($params[$type]) || $params[$type] !== 'Y')
		{
			return;
		}

		$customDataParams['content_name'] = $value;

		self::fireEvent(Facebook\Event::EVENT_CONTACT, $customDataParams);
	}

	public static function onCustomizeProductHandler(int $offerId): void
	{
		if (!self::isAllowedRegion())
		{
			return;
		}

		if (!self::checkClass())
		{
			return;
		}

		$params = self::getFacebookConversionParams(Facebook\Event::EVENT_DONATE);
		if (!$params)
		{
			return;
		}

		if ($params[self::ID] !== 'Y')
		{
			return;
		}

		$customDataParams = [
			'content_type' => 'product',
			'content_ids' => [$offerId],
		];

		if ($params[self::NAME_AND_PROPERTIES] === 'Y')
		{
			$skuPropertiesTextValue = self::getSkuNameAndPropertiesTextValue($offerId);
			if ($skuPropertiesTextValue)
			{
				$customDataParams['content_name'] = $skuPropertiesTextValue;
			}
		}

		self::fireEvent(Facebook\Event::EVENT_DONATE, $customDataParams);
	}

	private static function getSkuNameAndPropertiesTextValue(int $offerId): ?string
	{
		/** @var BaseSku $skuEntity */
		$skuEntity = ServiceContainer::getRepositoryFacade()->loadVariation($offerId);
		if (!$skuEntity)
		{
			return null;
		}

		$skuTree = self::getProductSkuTree($skuEntity);
		if (!$skuTree)
		{
			return null;
		}

		$selectedValues = $skuTree['SELECTED_VALUES'];
		$offersProp = $skuTree['OFFERS_PROP'];

		$skuProperties = [];
		foreach ($offersProp as $property)
		{
			$selectedValueId = $selectedValues[$property['ID']];
			if ($selectedValueId === 0)
			{
				continue;
			}

			$filteredValues = array_filter(
				$property['VALUES'],
				static function($valuesElement) use($selectedValueId) {
					return $valuesElement['ID'] === $selectedValueId;
				}
			);
			$skuProperties[] = $filteredValues[array_key_first($filteredValues)]['NAME'];
		}

		return $skuEntity->getName() . ' ' . implode(', ', $skuProperties);
	}

	private static function getProductSkuTree(BaseSku $skuEntity): ?array
	{
		/** @var SkuTree $skuTreeComponent */
		$skuTreeComponent = ServiceContainer::make('sku.tree', ['iblockId' => $skuEntity->getIblockId()]);
		if (!$skuTreeComponent)
		{
			return null;
		}

		/** @var BaseProduct $productEntity */
		$productEntity = $skuEntity->getParent();
		if (!$productEntity)
		{
			return null;
		}
		$productId = $productEntity->getId();

		$skuIds = array_column($productEntity->getSkuCollection()->toArray(), 'ID');
		$productsSkuTree = $skuTreeComponent->loadWithSelectedOffers(
			[$productId => $skuIds]
		);

		return $productsSkuTree[$productId][$skuEntity->getId()];
	}

	private static function fireEvent(string $eventName, array $customDataParams): void
	{
		$conversion = self::getConversionEntity($eventName, $customDataParams);

		Application::getInstance()->addBackgroundJob(
			function() use ($conversion, $eventName) {
				try
				{
					$isEventSent = $conversion->fireEvents();
					if ($isEventSent)
					{
						$analyticsEvent = new Analytics\Events\Event(
							Analytics\Events\Event::FACEBOOK_CONVERSION_EVENT_FIRED,
							[
								'EVENT_NAME' => $eventName,
							]
						);

						$provider = new Analytics\Events\Provider($analyticsEvent);
						(new Analytics\Storage($provider))->save();
					}
				}
				catch (\Throwable $throwable)
				{
				}
			}
		);
	}

	private static function getConversionEntity(
		string $eventName,
		array $customDataParams
	): ?Facebook\Conversion
	{
		$service = self::getService();
		if (!$service)
		{
			return null;
		}

		$customData = new Facebook\CustomData($customDataParams);
		$userData = new Facebook\UserData([
			'client_ip_address' => $_SERVER['REMOTE_ADDR'],
			'client_user_agent' => $_SERVER['HTTP_USER_AGENT'],
		]);
		$event = new Facebook\Event([
			'event_name' => $eventName,
			'custom_data' => $customData,
			'user_data' => $userData,
			'event_source_url' => self::getSiteUrl(),
		]);
		$conversion = new Facebook\Conversion($service);
		$conversion->addEvent($event);

		return $conversion;
	}

	private static function getSiteUrl(): string
	{
		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		$protocol = $request->isHttps() ? 'https://' : 'http://';
		$url = $protocol . $request->getHttpHost();

		$site = \Bitrix\Main\SiteTable::getList([
			'select' => ['LID', 'DIR'],
			'filter' => [
				'LID' => SITE_ID,
			],
			'limit' => 1,
		])->fetch();
		if (!$site)
		{
			return $url;
		}

		return $url . $site['DIR'];
	}

	private static function getProductDeepestSection(int $productId):? string
	{
		/** @var \Bitrix\Catalog\v2\Sku\Sku $variation */
		$variation = ServiceContainer::getRepositoryFacade()->loadVariation($productId);
		if (!$variation)
		{
			return null;
		}

		/** @var \Bitrix\Catalog\v2\Product\Product $product */
		$product = $variation->getParent();
		if (!$product)
		{
			return null;
		}

		$sectionIds = $product->getSectionCollection()->getValues();
		if (!empty($sectionIds))
		{
			$sectionData = \CIBlockSection::GetList(
				['DEPTH_LEVEL' => 'DESC'],
				['ID' => $sectionIds],
				false,
				['ID', 'NAME']
			)->Fetch();
			if ($sectionData)
			{
				return $sectionData['NAME'];
			}
		}

		return null;
	}

	private static function checkClass(): bool
	{
		return self::checkModules() && self::getService();
	}

	private static function checkModules(): bool
	{
		return
			Loader::includeModule('seo')
			&& Loader::includeModule('socialservices')
			&& Loader::includeModule('sale')
		;
	}

	private static function getService(): ?Service
	{
		return ServiceLocator::getInstance()->get('seo.business.service') ?: null;
	}

	private static function getFacebookConversionParamsData(string $eventName):? array
	{
		$facebookConversionParamsData = FacebookConversionParamsTable::getList([
			'filter' => [
				'=EVENT_NAME' => $eventName,
				'=LID' => SITE_ID,
				'=ENABLED' => 'Y',
			],
		])->fetch();

		return $facebookConversionParamsData ?: null;
	}

	private static function getFacebookConversionParams(string $eventName): ?array
	{
		$facebookConversionParamsData = self::getFacebookConversionParamsData($eventName);
		if (!$facebookConversionParamsData || $facebookConversionParamsData['ENABLED'] !== 'Y')
		{
			return null;
		}

		$params = unserialize($facebookConversionParamsData['PARAMS'], ['allow_classes' => false]);
		if (!$params)
		{
			return null;
		}

		return $params;
	}

	private static function isAllowedRegion(): bool
	{
		$region = \Bitrix\Main\Application::getInstance()->getLicense()->getRegion();

		return $region !== null && $region !== 'ru';
	}

	public static function isEventEnabled(string $eventName): bool
	{
		if (!self::isAllowedRegion())
		{
			return false;
		}

		static $isEventEnabled;
		if (isset($isEventEnabled[$eventName]))
		{
			return $isEventEnabled[$eventName];
		}

		$facebookConversionParams = self::getFacebookConversionParamsData($eventName);
		$isEventEnabled[$eventName] =
			isset($facebookConversionParams['ENABLED']) && $facebookConversionParams['ENABLED'] === 'Y'
		;

		return $isEventEnabled[$eventName];
	}

	public static function OnSiteDeleteHandler(string $lid): void
	{
		$facebookConversionParamsResult = FacebookConversionParamsTable::getList([
			'filter' => [
				'=LID' => $lid,
			],
		]);
		while ($facebookConversionParams = $facebookConversionParamsResult->fetch())
		{
			FacebookConversionParamsTable::delete($facebookConversionParams['ID']);
		}
	}
}