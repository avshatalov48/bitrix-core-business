<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage iblock
 */
namespace Bitrix\Iblock\PropertyIndex;

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock;

Loc::loadMessages(__FILE__);

class Manager
{
	private static $catalog = null;

	private static $deferredIndexing = -1;

	private static $elementQueue = array();

	private static $indexerInstances = array();

	/**
	 * For offers iblock identifier returns it's products iblock.
	 * Otherwise $iblockId returned.
	 *
	 * @param integer $iblockId Information block identifier.
	 *
	 * @return integer
	 */
	public static function resolveIblock($iblockId)
	{
		if (self::$catalog === null)
		{
			self::$catalog = Loader::includeModule("catalog");
		}

		if (self::$catalog)
		{
			$catalog = \CCatalogSKU::getInfoByOfferIBlock($iblockId);
			if (!empty($catalog) && is_array($catalog))
			{
				return $catalog["PRODUCT_IBLOCK_ID"];
			}
		}

		return $iblockId;
	}

	/**
	 * If elementId is an offer, then it's product identifier returned
	 * Otherwise $elementId returned.
	 *
	 * @param integer $iblockId Information block identifier.
	 * @param integer $elementId Element identifier.
	 *
	 * @return integer
	 */
	public static function resolveElement($iblockId, $elementId)
	{
		if (self::$catalog === null)
		{
			self::$catalog = Loader::includeModule("catalog");
		}

		if (self::$catalog)
		{
			$catalog = \CCatalogSKU::getProductInfo($elementId, $iblockId);
			if (!empty($catalog) && is_array($catalog))
			{
				return $catalog["ID"];
			}
		}

		return $elementId;
	}

	/**
	 * Drops all related to index database structures.
	 *
	 * @param integer $iblockId Information block identifier.
	 *
	 * @return void
	 */
	public static function dropIfExists($iblockId)
	{
		$storage = new Storage($iblockId);
		if ($storage->isExists())
			$storage->drop();

		$dictionary = new Dictionary($iblockId);
		if ($dictionary->isExists())
			$dictionary->drop();
	}

	/**
	 * Creates and initializes new indexer instance.
	 *
	 * @param integer $iblockId Information block identifier.
	 *
	 * @return Iblock\PropertyIndex\Indexer
	 */
	public static function createIndexer($iblockId)
	{
		$iblockId = (int)$iblockId;
		$productIblock = self::resolveIblock($iblockId);
		if (!isset(self::$indexerInstances[$productIblock]))
		{
			$indexer = new Indexer($productIblock);
			$indexer->init();
			self::$indexerInstances[$productIblock] = $indexer;
			unset($indexer);
		}
		return self::$indexerInstances[$productIblock];
	}

	/**
	 * Marks iblock as one who needs index rebuild.
	 *
	 * @param integer $iblockId Information block identifier.
	 *
	 * @return void
	 */
	public static function markAsInvalid($iblockId)
	{
		Iblock\IblockTable::update($iblockId, array(
			"PROPERTY_INDEX" => "I",
		));

		$productIblock = self::resolveIblock($iblockId);
		if ($iblockId != $productIblock)
		{
			Iblock\IblockTable::update($productIblock, array(
				"PROPERTY_INDEX" => "I",
			));
		}

		self::checkAdminNotification(true);
	}

	/**
	 * Marks iblock as one who needs index rebuild when it is needed.
	 *
	 * @param integer $iblockId Information block identifier.
	 * @param array $propertyOld Previos property fields.
	 * @param array $propertyNew New property fields.
	 *
	 * @return void
	 */
	public static function onPropertyUpdate($iblockId, $propertyOld, $propertyNew)
	{
		if (array_key_exists("USER_TYPE", $propertyNew))
		{
			$storageOld = Iblock\PropertyIndex\Indexer::getPropertyStorageType($propertyOld);
			$storageNew = Iblock\PropertyIndex\Indexer::getPropertyStorageType($propertyNew);
			if ($storageOld !== $storageNew)
			{
				self::markAsInvalid($iblockId);
			}
		}
	}

	/**
	 * Adds admin users notification about index rebuild.
	 *
	 * @param boolean $force Whenever skip iblock check.
	 *
	 * @return void
	 */
	public static function checkAdminNotification($force = false)
	{
		if ($force)
		{
			$add = true;
		}
		else
		{
			$iblockList = Iblock\IblockTable::getList(array(
				'select' => array('ID'),
				'filter' => array('=PROPERTY_INDEX' => 'I'),
			));
			$add = ($iblockList->fetch()? true: false);
		}

		if ($add)
		{
			$notifyList = \CAdminNotify::getList(array(), array(
				"TAG" => "iblock_property_reindex",
			));
			if (!$notifyList->fetch())
			{
				\CAdminNotify::add(array(
					"MESSAGE" => Loc::getMessage("IBLOCK_NOTIFY_PROPERTY_REINDEX", array(
						"#LINK#" => "/bitrix/admin/iblock_reindex.php?lang=".\Bitrix\Main\Application::getInstance()->getContext()->getLanguage(),
					)),
					"TAG" => "iblock_property_reindex",
					"MODULE_ID" => "iblock",
					"ENABLE_CLOSE" => "Y",
					"PUBLIC_SECTION" => "N",
				));
			}
		}
		else
		{
			\CAdminNotify::deleteByTag("iblock_property_reindex");
		}
	}
	/**
	 * Deletes index and mark iblock as having none.
	 *
	 * @param integer $iblockId Information block identifier.
	 *
	 * @return void
	 */
	public static function deleteIndex($iblockId)
	{
		self::dropIfExists($iblockId);
		Iblock\IblockTable::update($iblockId, array(
			"PROPERTY_INDEX" => "N",
		));
	}

	/**
	 * Deletes all related to element information if index exists.
	 *
	 * @param integer $iblockId Information block identifier.
	 * @param integer $elementId Identifier of the element.
	 *
	 * @return void
	 */
	public static function deleteElementIndex($iblockId, $elementId)
	{
		$elementId = (int)$elementId;
		$productIblock = self::resolveIblock($iblockId);
		$indexer = self::createIndexer($productIblock);

		if ($indexer->isExists())
		{
			if ($iblockId != $productIblock)
			{
				self::updateElementIndex($iblockId, $elementId);
			}
			else
			{
				$indexer->deleteElement($elementId);
			}
		}
	}

	/**
	 * Updates all related to element information if index exists.
	 *
	 * @param integer $iblockId Information block identifier.
	 * @param integer $elementId Identifier of the element.
	 *
	 * @return void
	 */
	public static function updateElementIndex($iblockId, $elementId)
	{
		$elementId = (int)$elementId;
		$productIblock = self::resolveIblock($iblockId);
		if ($iblockId != $productIblock)
			$elementId = self::resolveElement($iblockId, $elementId);
		if (self::usedDeferredIndexing())
		{
			self::pushToQueue($productIblock, $elementId);
		}
		else
		{
			$indexer = self::createIndexer($productIblock);
			if ($indexer->isExists())
			{
				self::elementIndexing($indexer, $elementId);
			}
			unset($indexer);
		}
	}

	/**
	 * Enable deferred indexing.
	 *
	 * @return void
	 */
	public static function enableDeferredIndexing()
	{
		self::$deferredIndexing++;
	}

	/**
	 * Disable deferred indexing.
	 *
	 * @return void
	 */
	public static function disableDeferredIndexing()
	{
		self::$deferredIndexing--;
	}

	/**
	 * Return true if allowed deferred indexing.
	 *
	 * @return bool
	 */
	public static function usedDeferredIndexing()
	{
		return (self::$deferredIndexing >= 0);
	}

	/**
	 * Update iblock index if defered data exists.
	 *
	 * @param int $iblockId		Iblock.
	 * @return void
	 */
	public static function runDeferredIndexing($iblockId)
	{
		$iblockId = (int)$iblockId;
		$productIblock = self::resolveIblock($iblockId);
		if (empty(self::$elementQueue[$productIblock]))
			return;
		$indexer = self::createIndexer($productIblock);
		if ($indexer->isExists())
		{
			sort(self::$elementQueue[$productIblock]);

			foreach (self::$elementQueue[$productIblock] as $elementId)
				self::elementIndexing($indexer, $elementId);
			unset($elementId);
		}
		unset($indexer);
		unset(self::$elementQueue[$productIblock]);
	}

	private static function pushToQueue($iblockId, $elementId)
	{
		if (!isset(self::$elementQueue[$iblockId]))
			self::$elementQueue[$iblockId] = [];
		self::$elementQueue[$iblockId][$elementId] = $elementId;
	}

	private static function elementIndexing(Iblock\PropertyIndex\Indexer $indexer, $elementId)
	{
		$indexer->deleteElement($elementId);
		$connection = \Bitrix\Main\Application::getConnection();
		$elementCheck = $connection->query("
				SELECT BE.ID
				FROM b_iblock_element BE
				WHERE BE.ACTIVE = 'Y' AND BE.ID = ".$elementId.
				\CIBlockElement::wf_getSqlLimit("BE.", "N")
		);
		if ($elementCheck->fetch())
		{
			$indexer->indexElement($elementId);
		}
		unset($elementCheck, $connection);
	}
}
