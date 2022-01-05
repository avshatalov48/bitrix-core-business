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
use CIBlockElement;
use Bitrix\Seo\BusinessSuite\Service;
use Bitrix\Seo\Conversion\Facebook;

class FacebookConversion
{
	public static function onAddToCartHandler(int $id, array $productData): void
	{
		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		$action = $request->get('action');
		if ($action !== 'ADD2BASKET' && $action !== 'BUY')
		{
			return;
		}

		if (!self::checkModules())
		{
			return;
		}

		$service = self::getService();
		if (!$service)
		{
			return;
		}

		$facebookConversionParams = self::getFacebookConversionParams($productData['LID'], 'AddToCart');
		if (!$facebookConversionParams || $facebookConversionParams['ENABLED'] !== 'Y')
		{
			return;
		}

		$params = unserialize($facebookConversionParams['PARAMS'], ['allow_classes' => false]);
		if (!$params)
		{
			return;
		}

		if ($params['id'] !== 'Y')
		{
			return;
		}

		$customDataParams = [
			'content_type' => 'product',
		];

		if ($params['name'] === 'Y')
		{
			$customDataParams['content_name'] = $productData['NAME'];
		}
		if ($params['group'] === 'Y')
		{
			$customDataParams['content_category'] = self::getProductDeepestSection((int)$productData['PRODUCT_ID']);
		}
		if ($params['price'] === 'Y')
		{
			$customDataParams['value'] = $productData['PRICE'];
			$customDataParams['currency'] = $productData['CURRENCY'];
		}
		if ($params['quantity'] === 'Y')
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

		self::fireEvent(
			Facebook\Event::EVENT_ADD_TO_CART,
			$customDataParams,
			$service,
			$productData['LID'],
		);
	}

	public static function onOrderCreatedHandler(Order $order): void
	{
		$application = \Bitrix\Main\Application::getInstance();
		$session = $application ? $application->getSession() : null;
		$lid = $order->getField('LID');
		$isInitiateCheckoutSent =
			$session
				? $session->has('FACEBOOK_CONVERSION_INITIATE_CHECKOUT_SENT_' . $lid)
				: false
		;
		if (\Bitrix\Main\Context::getCurrent()->getRequest()->isAjaxRequest())
		{
			if (
				$isInitiateCheckoutSent
				&& \Bitrix\Main\Context::getCurrent()->getRequest()->get('action') === 'saveOrderAjax'
			)
			{
				$session->remove('FACEBOOK_CONVERSION_INITIATE_CHECKOUT_SENT_' . $lid);
			}

			return;
		}
		if ($isInitiateCheckoutSent)
		{
			return;
		}
		$session->set('FACEBOOK_CONVERSION_INITIATE_CHECKOUT_SENT_' . $lid, true);

		if (!self::checkModules())
		{
			return;
		}

		$service = self::getService();
		if (!$service)
		{
			return;
		}

		$facebookConversionParams = self::getFacebookConversionParams($lid, 'InitiateCheckout');
		if (!$facebookConversionParams || $facebookConversionParams['ENABLED'] !== 'Y')
		{
			return;
		}

		$params = unserialize($facebookConversionParams['PARAMS'], ['allow_classes' => false]);
		if (!$params)
		{
			return;
		}

		if ($params['ids'] !== 'Y')
		{
			return;
		}

		$customDataParams = [
			'content_type' => 'product',
		];

		if ($params['productsGroupAndQuantity'] === 'Y')
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

		if ($params['price'] === 'Y')
		{
			$customDataParams['value'] = $order->getPrice();
			$customDataParams['currency'] = $order->getCurrency();
		}
		if ($params['quantity'] === 'Y')
		{
			$totalQuantity = 0;
			foreach ($order->getBasket()->getBasketItems() as $basketItem)
			{
				$totalQuantity += $basketItem->getQuantity();
			}
			$customDataParams['num_items'] = (int)$totalQuantity;
		}

		self::fireEvent(
			Facebook\Event::EVENT_INITIATE_CHECKOUT,
			$customDataParams,
			$service,
			$lid
		);
	}

	public static function onOrderSavedHandler(\Bitrix\Main\Event $event): void
	{
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

		if (!self::checkModules())
		{
			return;
		}

		$service = self::getService();
		if (!$service)
		{
			return;
		}

		$lid = $order->getField('LID');
		$facebookConversionParams = self::getFacebookConversionParams($lid, Facebook\Event::EVENT_ADD_PAYMENT);
		if (!$facebookConversionParams || $facebookConversionParams['ENABLED'] !== 'Y')
		{
			return;
		}

		$params = unserialize($facebookConversionParams['PARAMS'], ['allow_classes' => false]);
		if (!$params)
		{
			return;
		}

		if ($params['ids'] !== 'Y')
		{
			return;
		}

		$customDataParams = [
			'content_type' => 'product',
		];

		$customDataParams['content_type'] = 'product';

		if ($params['productsGroupAndQuantity'] === 'Y')
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
		if ($params['price'] === 'Y')
		{
			$customDataParams['value'] = $order->getPrice();
			$customDataParams['currency'] = $order->getCurrency();
		}

		self::fireEvent(
			Facebook\Event::EVENT_ADD_PAYMENT,
			$customDataParams,
			$service,
			$lid
		);
	}

	public static function onFeedbackFormContactHandler(\Bitrix\Main\Event $event): void
	{
		$email = $event->getParameter('AUTHOR_EMAIL') ?? '';

		self::fireContactEvent('email', (string)$email);
	}

	public static function onContactHandler($contactBy): void
	{
		$type = null;
		$value = null;
		if (is_array($contactBy))
		{
			$type = $contactBy['type'] ?? '';
			$value = $contactBy['value'] ?? '';
		}
		else
		{
			$type = 'email';
			$value = $contactBy;
		}

		self::fireContactEvent((string)$type, (string)$value);
	}

	private static function fireContactEvent(string $type, string $value): void
	{
		if (!self::checkModules())
		{
			return;
		}

		$service = self::getService();
		if (!$service)
		{
			return;
		}

		$facebookConversionParams = self::getFacebookConversionParams(SITE_ID, Facebook\Event::EVENT_CONTACT);
		if (!$facebookConversionParams || $facebookConversionParams['ENABLED'] !== 'Y')
		{
			return;
		}

		$params = unserialize($facebookConversionParams['PARAMS'], ['allow_classes' => false]);
		if (!$params)
		{
			return;
		}

		if (!isset($params[$type]) || $params[$type] !== 'Y')
		{
			return;
		}

		$params = unserialize($facebookConversionParams['PARAMS'], ['allow_classes' => false]);
		if (!$params)
		{
			return;
		}

		$customDataParams['content_name'] = $value;

		self::fireEvent(Facebook\Event::EVENT_CONTACT, $customDataParams, $service, SITE_ID);
	}

	public static function onCustomizeProductHandler(int $offerId): void
	{
		if (!self::checkModules())
		{
			return;
		}

		$service = self::getService();
		if (!$service)
		{
			return;
		}

		$facebookConversionParams = self::getFacebookConversionParams(SITE_ID, Facebook\Event::EVENT_DONATE);
		if (!$facebookConversionParams || $facebookConversionParams['ENABLED'] !== 'Y')
		{
			return;
		}

		$params = unserialize($facebookConversionParams['PARAMS'], ['allow_classes' => false]);
		if (!$params)
		{
			return;
		}

		if ($params['id'] !== 'Y')
		{
			return;
		}

		$customDataParams = [
			'content_type' => 'product',
			'content_ids' => [$offerId],
		];

		if ($params['nameAndProperties'] === 'Y')
		{
			$skuPropertiesTextValue = self::getSkuNameAndPropertiesTextValue($offerId);
			if ($skuPropertiesTextValue)
			{
				$customDataParams['content_name'] = $skuPropertiesTextValue;
			}
		}

		self::fireEvent(Facebook\Event::EVENT_DONATE, $customDataParams, $service, SITE_ID);
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

	private static function fireEvent(string $eventName, array $customDataParams, Service $service, string $lid): void
	{
		$conversion = self::getConversionEntity($eventName, $customDataParams, $service, $lid);

		Application::getInstance()->addBackgroundJob(
			function() use ($conversion) {
				try
				{
					$conversion->fireEvents();
				}
				catch (\Throwable $throwable)
				{
				}
			}
		);
	}

	private static function getConversionEntity(
		string $eventName,
		array $customDataParams,
		Service $service,
		string $lid
	): Facebook\Conversion
	{
		$customData = new Facebook\CustomData($customDataParams);
		$userData = new Facebook\UserData([
			'client_ip_address' => $_SERVER['REMOTE_ADDR'],
			'client_user_agent' => $_SERVER['HTTP_USER_AGENT'],
		]);
		$event = new Facebook\Event([
			'event_name' => $eventName,
			'custom_data' => $customData,
			'user_data' => $userData,
			'event_source_url' => self::getSiteUrl($lid),
		]);
		$conversion = new Facebook\Conversion($service);
		$conversion->addEvent($event);

		return $conversion;
	}

	private static function getSiteUrl($lid): string
	{
		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		$protocol = $request->isHttps() ? 'https://' : 'http://';
		$url = $protocol . $request->getHttpHost();

		$site = \Bitrix\Main\SiteTable::getList([
			'select' => ['LID', 'DIR'],
			'filter' => [
				'LID' => $lid,
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

	private static function getFacebookConversionParams(string $lid, string $eventName):? array
	{
		$facebookConversionParams = FacebookConversionParamsTable::getList([
			'filter' => [
				'EVENT_NAME' => $eventName,
				'LID' => $lid,
				'ENABLED' => 'Y',
			],
		])->fetch();

		return $facebookConversionParams ?: null;
	}

	public static function isEventEnabled(string $eventName): bool
	{
		static $isEventEnabled;
		if (isset($isEventEnabled[$eventName]))
		{
			return $isEventEnabled[$eventName];
		}

		$facebookConversionParams = self::getFacebookConversionParams(SITE_ID, $eventName);
		$isEventEnabled[$eventName] =
			isset($facebookConversionParams['ENABLED']) && $facebookConversionParams['ENABLED'] === 'Y'
		;

		return $isEventEnabled[$eventName];
	}

	public static function OnSiteDeleteHandler(string $lid): void
	{
		$facebookConversionParamsResult = FacebookConversionParamsTable::getList([
			'filter' => [
				'LID' => $lid,
			],
		]);
		while ($facebookConversionParams = $facebookConversionParamsResult->fetch())
		{
			FacebookConversionParamsTable::delete($facebookConversionParams['ID']);
		}
	}
}