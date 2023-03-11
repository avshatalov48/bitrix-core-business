<?php

namespace Bitrix\Sale\TradingPlatform\Landing;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use Bitrix\Sale;

Loc::loadMessages(__FILE__);

/**
 * Class Landing
 * @package Bitrix\Sale\TradingPlatform\Landing
 */
class Landing
	extends Sale\TradingPlatform\Platform
	implements Sale\TradingPlatform\IRestriction
{
	public const TRADING_PLATFORM_CODE = 'landing';
	public const CODE_DELIMITER = '_';

	public const LANDING_STORE_CLOTHES = 'clothes';
	public const LANDING_STORE_INSTAGRAM = 'instagram';
	public const LANDING_STORE_CHATS = 'chats';
	public const LANDING_STORE_MINI_ONE_ELEMENT = 'mini-one-element';
	public const LANDING_STORE_MINI_CATALOG = 'mini-catalog';
	public const LANDING_STORE_STORE_V3 = 'store_v3';

	protected static $stores = [
		self::LANDING_STORE_CLOTHES,
		self::LANDING_STORE_INSTAGRAM,
		self::LANDING_STORE_CHATS,
		self::LANDING_STORE_MINI_ONE_ELEMENT,
		self::LANDING_STORE_MINI_CATALOG,
		self::LANDING_STORE_STORE_V3,
	];

	protected $site = [];

	/**
	 * @return bool|int
	 */
	public function install()
	{
		$data = $this->getInfo();

		$result = Sale\TradingPlatformTable::add([
			"CODE" => $this->getCode(),
			"ACTIVE" => "Y",
			"NAME" => Loc::getMessage('SALE_LANDING_NAME', ['#NAME#' => $data['TITLE']]),
			"DESCRIPTION" => '',
			"CLASS" => '\\'.static::class,
			"XML_ID" => static::generateXmlId(),
		]);

		if ($result->isSuccess())
		{
			$this->isInstalled = true;
			$this->id = $result->getId();
		}

		return $result->isSuccess();
	}

	/**
	 * @return string
	 */
	protected static function generateXmlId()
	{
		return uniqid('bx_');
	}

	/**
	 * @return int
	 */
	protected function getSiteId()
	{
		return (int)mb_substr($this->getCode(), mb_strrpos($this->getCode(), '_') + 1);
	}

	/**
	 * @return void
	 */
	public static function setShipmentTableOnAfterUpdateEvent()
	{
		return;
	}

	/**
	 * @return void
	 */
	protected static function unSetShipmentTableOnAfterUpdateEvent()
	{
		return;
	}

	/**
	 * @return void
	 */
	protected function setCatalogSectionsTabEvent()
	{
		return;
	}

	/**
	 * @return void
	 */
	protected function unSetCatalogSectionsTabEvent()
	{
		return;
	}

	/**
	 * @param Main\Event $event
	 */
	public static function onLandingSiteAdd(Main\Event $event)
	{
		$fields = $event->getParameter('fields');
		if ($fields['TYPE'] !== 'STORE')
		{
			return;
		}

		$primary = $event->getParameter('primary');
		$landing = Landing::getInstanceByCode(static::getCodeBySiteId($primary['ID']));
		if (!$landing->isInstalled())
		{
			$landing->install();
		}
	}

	public static function onLandingSiteUpdate(Main\Event $event)
	{
		$fields = $event->getParameter('fields');
		if (empty($fields['TYPE']) || $fields['TYPE'] !== 'STORE')
		{
			return;
		}

		$primary = $event->getParameter('primary');
		$landing = Landing::getInstanceByCode(static::getCodeBySiteId($primary['ID']));
		if ($landing->isInstalled())
		{
			Sale\TradingPlatformTable::update(
				$landing->getId(),
				[
					'NAME' => Loc::getMessage('SALE_LANDING_NAME', ['#NAME#' => $fields['TITLE']]),
				]
			);
		}
	}

	/**
	 * @param Main\Event $event
	 */
	public static function onLandingSiteDelete(Main\Event $event)
	{
		$primary = $event->getParameter('primary');

		$landing = Landing::getInstanceByCode(static::getCodeBySiteId($primary['ID']));
		if ($landing->isInstalled())
		{
			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

			/** @var Sale\TradeBindingCollection $tradeBindingCollection */
			$tradeBindingCollection = $registry->get(Sale\Registry::ENTITY_TRADE_BINDING_COLLECTION);

			$dbRes = $tradeBindingCollection::getList([
				'select' => ['ID'],
				'filter' => [
					'=TRADING_PLATFORM_ID' => $landing->getId()
				]
			]);

			if ($dbRes->fetch())
			{
				$landing->unsetActive();
			}
			else
			{
				$landing->uninstall();
			}
		}
	}

	/**
	 * @param Main\Event $event
	 */
	public static function onLandingBeforeSiteRecycle(Main\Event $event)
	{
		$id = $event->getParameter('id');
		$delete = $event->getParameter('delete');

		$res = \Bitrix\Landing\Site::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=ID' => $id,
				'CHECK_PERMISSIONS' => 'N',
				'=TYPE' => 'STORE'
			]
		]);

		if (!$res->fetch())
		{
			return;
		}

		$landing = Landing::getInstanceByCode(static::getCodeBySiteId($id));
		if (!$landing || !$landing->isInstalled())
		{
			return;
		}

		if ($delete)
		{
			$landing->unsetActive();
		}
		else
		{
			$landing->setActive();
		}
	}

	/**
	 * @param $id
	 * @return string
	 */
	public static function getCodeBySiteId($id)
	{
		return static::TRADING_PLATFORM_CODE.static::CODE_DELIMITER.$id;
	}

	/**
	 * @return array
	 */
	public function getInfo()
	{
		if (!Main\Loader::includeModule('landing'))
		{
			return [];
		}

		if ($this->site)
		{
			return $this->site;
		}

		/** @var Main\DB\Result $dbRes */
		$dbRes = \Bitrix\Landing\Site::getList([
			'filter' => [
				'=ID' => $this->getSiteId(),
				'CHECK_PERMISSIONS' => 'N',
				'=DELETED' => ['Y', 'N'],
			]
		]);

		if ($data = $dbRes->fetch())
		{
			$this->site = $data;
			$this->site['PUBLIC_URL'] = \Bitrix\Landing\Site::getPublicUrl($this->getSiteId());
		}

		return $this->site;
	}

	public function getAnalyticCode()
	{
		$data = $this->getInfo();
		if (!isset($data['XML_ID']) || !$data['XML_ID'])
		{
			return parent::getAnalyticCode();
		}

		foreach (static::$stores as $store)
		{
			if (mb_strpos($data['XML_ID'], $store) !== false)
			{
				return $store;
			}
		}

		return $data['XML_ID'];
	}

	/**
	 * @param $type
	 * @param Sale\Order $order
	 * @return string
	 * @throws Main\ArgumentException
	 */
	public function getExternalLink($type, Sale\Order $order)
	{
		if ($type === static::LINK_TYPE_PUBLIC_DETAIL_ORDER)
		{
			return $this->getLandingSysPageUrl(
				'personal',
				[
					'SECTION' => 'orders',
					'ID' => $order->getId()
				]
			);
		}

		if ($type === static::LINK_TYPE_PUBLIC_FEEDBACK)
		{
			return $this->getLandingSysPageUrl('feedback');
		}

		throw new Main\ArgumentException("Unsupported link type: {$type}");
	}

	/**
	 * @param string $type
	 * @param array $additional
	 * @return string
	 */
	private function getLandingSysPageUrl(string $type, array $additional = []): string
	{
		if (!Main\Loader::includeModule('landing'))
		{
			return '';
		}

		return \Bitrix\Landing\Syspage::getSpecialPage($this->getSiteId(), $type, $additional);
	}

	/**
	 * @return string
	 */
	public function getRealName()
	{
		return (string)($this->getInfo()['TITLE'] ?? '');
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	public function isOfType(string $type): bool
	{
		$info = $this->getInfo();
		if (!isset($info['XML_ID']))
		{
			return false;
		}

		return mb_strpos($info['XML_ID'], $type) !== false;
	}
}
