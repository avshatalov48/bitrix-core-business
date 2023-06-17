<?php

namespace Bitrix\Sale\TradingPlatform;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Entity\EventResult;
use Bitrix\Main\Entity\Result;
use Bitrix\Main\SystemException;
use Bitrix\Sale;
use Bitrix\Sale\TradingPlatformTable;
use Bitrix\Main\EventManager;

/**
 * Class Platform
 * Base class for trading platforms.
 * @package Bitrix\Sale\TradingPlatform
 */
abstract class Platform
{
	public const LINK_TYPE_PUBLIC_DETAIL_ORDER = 'PUBLIC_DETAIL_ORDER';
	public const LINK_TYPE_PUBLIC_FEEDBACK = 'PUBLIC_FEEDBACK';

	protected $logger;
	protected $logLevel = Logger::LOG_LEVEL_ERROR;
	
	protected $code;
	protected $isActive = false;
	protected $settings = array();
	
	protected $isInstalled = false;
	protected $isNeedCatalogSectionsTab = false;
	
	protected $id;
	protected $fields = [];
	
	protected static $instances = array();
	
	const TRADING_PLATFORM_CODE = "";
	
	/**
	 * Constructor
	 * @param $code
	 */
	protected function __construct($code)
	{
		$this->code = $code;
		
		$dbRes = TradingPlatformTable::getList([
			'filter' => [
				'=CODE' => $this->code,
			],
		]);
		
		if ($platform = $dbRes->fetch())
		{
			$this->isActive = $platform["ACTIVE"] == "Y";
			$this->isNeedCatalogSectionsTab = $platform["CATALOG_SECTION_TAB_CLASS_NAME"] <> '';
			
			if (is_array($platform["SETTINGS"]))
			{
				$this->settings = $platform["SETTINGS"];
			}
			
			$this->isInstalled = true;
			$this->id = $platform["ID"];
			$this->fields = $platform;
		}
		
		$this->logger = new Logger($this->logLevel);
	}
	
	protected function __clone() {}
	
	/**
	 * @param $code
	 * @return \Bitrix\Sale\TradingPlatform\Platform
	 * @throws ArgumentNullException
	 */
	public static function getInstanceByCode($code)
	{
		if ($code === '')
		{
			throw new ArgumentNullException("code");
		}

		if (!isset(self::$instances[$code]))
		{
			self::$instances[$code] = new static($code);
		}
		
		return self::$instances[$code];
	}
	
	/**
	 * @return mixed Id of the current trading platform.
	 */
	public function getId()
	{
		return $this->id;
	}

	public function getIdIfInstalled(): ?int
	{
		if (!$this->isInstalled())
		{
			return null;
		}

		return $this->getId();
	}
	
	/**
	 * @param int $level The level of event.
	 * @param string $type Type of event.
	 * @param string $itemId Item idenifyer.
	 * @param string $description Description of event.
	 * @return bool Success or not.
	 */
	public function addLogRecord($level, $type, $itemId, $description)
	{
		return $this->logger->addRecord($level, $type, $itemId, $description);
	}

	public function getField($fieldName)
	{
		if(!isset($this->fields[$fieldName]))
		{
			return '';
		}

		return $this->fields[$fieldName];
	}

	public function getRealName()
	{
		return $this->getField('NAME');
	}

	/**
	 * @return bool
	 */
	public function isActive()
	{
		return $this->isActive;
	}
	
	/**
	 * Sets the platform active.
	 * @return bool
	 */
	public function setActive()
	{
		if ($this->isActive())
		{
			return true;
		}
		
		$this->isActive = true;
		
		if ($this->isNeedCatalogSectionsTab && !$this->isSomebodyUseCatalogSectionsTab())
			$this->setCatalogSectionsTabEvent();
		
		// if we are the first, let's switch on the event to notify about the track numbers changings
		if (!$this->isActiveItemsExist())
			$this->setShipmentTableOnAfterUpdateEvent();
		
		$res = TradingPlatformTable::update($this->id, array("ACTIVE" => "Y"));
		
		return $res->isSuccess();
	}
	
	/**
	 * Sets  the platform inactive.
	 * @return bool
	 */
	public function unsetActive()
	{
		$this->isActive = false;
		
		if ($this->isNeedCatalogSectionsTab && !$this->isSomebodyUseCatalogSectionsTab())
			$this->unSetCatalogSectionsTabEvent();
		
		$res = TradingPlatformTable::update($this->id, array("ACTIVE" => "N"));
		
		//If we are last let's switch off unused event about track numbers changing
		if (!$this->isActiveItemsExist())
		{
			$this->unSetShipmentTableOnAfterUpdateEvent();
		}
		
		return $res->isSuccess();
	}
	
	protected static function isActiveItemsExist()
	{
		$dbRes = TradingPlatformTable::getList([
			'filter' => [
				'ACTIVE' => 'Y',
			],
			'select' => ['ID'],
		]);
		
		return (bool)$dbRes->fetch();
	}
	
	public static function setShipmentTableOnAfterUpdateEvent()
	{
		$eventManager = EventManager::getInstance();
		$eventManager->registerEventHandler(
			'sale',
			'ShipmentOnAfterUpdate',
			'sale',
			'\Bitrix\Sale\TradingPlatform\Helper',
			'onAfterUpdateShipment'
		);
	}
	
	protected static function unSetShipmentTableOnAfterUpdateEvent()
	{
		$eventManager = EventManager::getInstance();
		$eventManager->unRegisterEventHandler(
			'sale',
			'ShipmentOnAfterUpdate',
			'sale',
			'\Bitrix\Sale\TradingPlatform\Helper',
			'onAfterUpdateShipment'
		);
	}
	
	/**
	 * Shows is another platforms using the iblock section edit page, "trading platforms" tab.
	 * @return bool
	 */
	protected function isSomebodyUseCatalogSectionsTab()
	{
		$result = false;
		
		$res = TradingPlatformTable::getList(array(
			'select' => array("ID", "CATALOG_SECTION_TAB_CLASS_NAME"),
			'filter' => array(
				'!=CODE' => $this->code,
				'=ACTIVE' => 'Y',
			),
		));
		
		while ($arRes = $res->fetch())
		{
			if ($arRes["CATALOG_SECTIONS_TAB_CLASS_NAME"] <> '')
			{
				$result = true;
				break;
			}
		}
		
		return $result;
	}
	
	protected function setCatalogSectionsTabEvent()
	{
		$eventManager = EventManager::getInstance();
		$eventManager->registerEventHandlerCompatible("main", "OnAdminIBlockSectionEdit", "sale", "\\Bitrix\\Sale\\TradingPlatform\\CatalogSectionTab", "OnInit");
	}
	
	protected function unSetCatalogSectionsTabEvent()
	{
		$eventManager = EventManager::getInstance();
		$eventManager->unRegisterEventHandler("main", "OnAdminIBlockSectionEdit", "sale", "\\Bitrix\\Sale\\TradingPlatform\\CatalogSectionTab", "OnInit");
	}
	
	/**
	 * @return array Platform settings.
	 */
	public function getSettings()
	{
		return $this->settings;
	}
	
	/**
	 * @param array $settings Platform settings.
	 * @return bool Is success?.
	 */
	public function saveSettings(array $settings)
	{
		$this->settings = $settings;
		$result = TradingPlatformTable::update($this->id, array("SETTINGS" => $settings));
		
		return $result->isSuccess() && $result->getAffectedRowsCount();
	}
	
	
	public function resetSettings($siteId)
	{
		$settings = $this->getSettings();
		if (isset($settings[$siteId]) && is_array($settings[$siteId]))
		{
			unset($settings[$siteId]);
		}
		
		if (empty($settings))
			$this->unsetActive();
		
		return $this->saveSettings($settings);
	}
	
	/**
	 * @return bool Is platfom installed?.
	 */
	public function isInstalled()
	{
		return $this->isInstalled;
	}
	
	/**
	 * Installs platform
	 * @return int Platform Id.
	 */
	public function install()
	{
		$res = TradingPlatformTable::add(array(
			"CODE" => self::TRADING_PLATFORM_CODE,
			"ACTIVE" => "N",
		));
		
		self::$instances[$this->getCode()] = new static($this->getCode());
		
		return $res->getId();
	}
	
	/**
	 * @return bool Is deletion successful?.
	 */
	public function uninstall()
	{
		if ($this->isInstalled())
		{
			$this->unsetActive();
			$res = TradingPlatformTable::delete($this->getId());
		}
		else
		{
			$res = new Result();
		}
		
		unset(self::$instances[$this->getCode()]);
		$this->isInstalled = false;
		
		return $res->isSuccess();
	}
	
	/**
	 * @return string Platform code.
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @return string Platform code.
	 */
	public function getAnalyticCode()
	{
		return static::TRADING_PLATFORM_CODE;
	}
	
	public static function onAfterUpdateShipment(\Bitrix\Main\Event $event, array $additional)
	{
		return new EventResult();
	}

	/**
	 * @return array
	 */
	public function getInfo()
	{
		return [];
	}

	/**
	 * @param string $storeType
	 * @return bool
	 */
	public function isOfType(string $type): bool
	{
		return false;
	}

	/**
	 * @param $type
	 * @param Sale\Order $order
	 * @return string
	 */
	public function getExternalLink($type, Sale\Order $order)
	{
		return '';
	}
}

