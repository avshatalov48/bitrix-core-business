<?php
namespace Bitrix\Catalog;

use Bitrix\Main,
	Bitrix\Main\Application,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock,
	Bitrix\Catalog;

Loc::loadMessages(__FILE__);

class CatalogViewedProductTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_viewed_product';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Main\Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('VIEWED_PRODUCT_ENTITY_ID_FIELD')
			)),
			'FUSER_ID' => new Main\Entity\IntegerField('FUSER_ID', array(
				'title' => Loc::getMessage('VIEWED_PRODUCT_ENTITY_FUSER_ID_FIELD')
			)),
			'DATE_VISIT' => new Main\Entity\DatetimeField('DATE_VISIT', array(
				'default_value' => new Main\Type\DateTime(),
				'title' => Loc::getMessage('VIEWED_PRODUCT_ENTITY_DATE_VISIT_FIELD')
			)),
			'PRODUCT_ID' => new Main\Entity\IntegerField('PRODUCT_ID', array(
				'title' => Loc::getMessage('VIEWED_PRODUCT_ENTITY_PRODUCT_ID_FIELD')
			)),
			'ELEMENT_ID' => new Main\Entity\IntegerField('ELEMENT_ID', array(
				'title' => Loc::getMessage('VIEWED_PRODUCT_ENTITY_ELEMENT_ID_FIELD')
			)),
			'SITE_ID' => new Main\Entity\StringField('SITE_ID', array(
				'validation' => array(__CLASS__, 'validateSiteId'),
				'title' => Loc::getMessage('VIEWED_PRODUCT_ENTITY_SITE_ID_FIELD')
			)),
			'VIEW_COUNT' => new Main\Entity\IntegerField('VIEW_COUNT', array(
				'title' => Loc::getMessage('VIEWED_PRODUCT_ENTITY_VIEW_COUNT_FIELD')
			)),
			'RECOMMENDATION' => new Main\Entity\StringField('RECOMMENDATION', array(
				'validation' => array(__CLASS__, 'validateRecommendation'),
				'title' => Loc::getMessage('VIEWED_PRODUCT_ENTITY_RECOMMENDATION_FIELD')
			)),
			'ELEMENT' => new Main\Entity\ReferenceField(
				'ELEMENT',
				'\Bitrix\Iblock\Element',
				array('=this.PRODUCT_ID' => 'ref.ID'),
				array('join_type' => 'INNER')
			),
			'PRODUCT' => new Main\Entity\ReferenceField(
				'PRODUCT',
				'\Bitrix\Sale\Internals\Product',
				array('=this.PRODUCT_ID' => 'ref.ID'),
				array('join_type' => 'INNER')
			),
			'PARENT_ELEMENT' => new Main\Entity\ReferenceField(
				'PARENT_ELEMENT',
				'\Bitrix\Iblock\Element',
				array('=this.ELEMENT_ID' => 'ref.ID'),
				array('join_type' => 'INNER')
			),
			'FUSER' => new Main\Entity\ReferenceField(
				'FUSER',
				'\Bitrix\Sale\Internals\Fuser',
				array('=this.FUSER_ID' => 'ref.ID'),
				array('join_type' => 'LEFT')
			)
		);
	}

	/**
	 * Returns validators for SITE_ID field.
	 *
	 * @return array
	 */
	public static function validateSiteId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 2),
		);
	}

	/**
	 * Returns validators for RECOMMENDATION field.
	 *
	 * @return array
	 */
	public static function validateRecommendation()
	{
		return array(
			new Main\Entity\Validator\Length(null, 40),
		);
	}

	/**
	 * Common function, used to update/insert any product.
	 *
	 * @param int $productId			Id of product.
	 * @param int $fuserId				User basket id.
	 * @param string|mixed $siteId		Site id.
	 * @param int $elementId			Parent id.
	 * @param string $recommendationId	Bigdata recommendation id.
	 *
	 * @return int
	 */
	public static function refresh($productId, $fuserId, $siteId = SITE_ID, $elementId = 0, $recommendationId = '')
	{
		$rowId = -1;
		$productId = (int)$productId;
		$fuserId = (int)$fuserId;
		$siteId = (string)$siteId;
		$elementId = (int)$elementId;
		$recommendationId = (string)$recommendationId;
		if ($productId <= 0 || $fuserId <= 0 || $siteId == '')
			return $rowId;

		if (!Catalog\Product\Basket::isNotCrawler())
			return $rowId;

		$filter = array('=FUSER_ID' => $fuserId, '=SITE_ID' => $siteId);

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$sqlSiteId = $helper->forSql($siteId);

		if ($elementId > 0)
		{
			$filter["=ELEMENT_ID"] = $elementId;

			// Delete parent product id (for capability)
			if ($elementId != $productId)
				$connection->query(
					"delete from b_catalog_viewed_product where PRODUCT_ID = ".$elementId." and FUSER_ID = ".$fuserId." and SITE_ID = '".$sqlSiteId."'"
				);
		}
		else
		{
			/** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection */
			$productInfo = \CCatalogSku::getProductInfo($productId);
			// Real SKU ID
			if (!empty($productInfo))
			{
				$elementId = $productInfo['ID'];
				$siblings = array();
				// Delete parent product id (for capability)
				$connection->query("delete from b_catalog_viewed_product
									where PRODUCT_ID = ".$productInfo['ID']." and FUSER_ID = ".$fuserId." and SITE_ID = '" .$sqlSiteId. "'"
				);

				$skus = \CIBlockElement::getList(
					array(),
					array('IBLOCK_ID' => $productInfo['OFFER_IBLOCK_ID'], '=PROPERTY_'.$productInfo['SKU_PROPERTY_ID'] => $productInfo['ID']),
					false,
					false,
					array('ID', 'IBLOCK_ID')
				);
				/** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection */
				while ($oneSku = $skus->fetch())
					$siblings[] = $oneSku['ID'];
				unset($oneSku, $skus);

				$filter['@PRODUCT_ID'] = $siblings;
			}
			else
			{
				$elementId = $productId;
				$filter['=PRODUCT_ID'] = $productId;
			}
		}

		// recommendation
		if (!empty($elementId))
		{
			global $APPLICATION;

			$recommendationCookie = $APPLICATION->get_cookie(Main\Analytics\Catalog::getCookieLogName());
			if (!empty($recommendationCookie))
			{
				$recommendations = Main\Analytics\Catalog::decodeProductLog($recommendationCookie);
				if (is_array($recommendations) && isset($recommendations[$elementId]))
					$recommendationId = $recommendations[$elementId][0];
			}
		}

		$iterator = static::getList(array(
			'select' => array('ID', 'FUSER_ID', 'DATE_VISIT', 'PRODUCT_ID', 'SITE_ID', 'VIEW_COUNT'),
			'filter' => $filter
		));

		if ($row = $iterator->fetch())
		{
			$update = array(
				"PRODUCT_ID" => $productId,
				"DATE_VISIT" => new Main\Type\DateTime,
				'VIEW_COUNT' => $row['VIEW_COUNT'] + 1,
				"ELEMENT_ID" => $elementId
			);
			if (!empty($recommendationId))
				$update["RECOMMENDATION"] = $recommendationId;

			$result = static::update($row['ID'], $update);
		}
		else
		{
			$result = static::add(array(
				"FUSER_ID" => $fuserId,
				"DATE_VISIT" => new Main\Type\DateTime(),
				"PRODUCT_ID" => $productId,
				"ELEMENT_ID" => $elementId,
				"SITE_ID" => $siteId,
				"VIEW_COUNT" => 1,
				"RECOMMENDATION" => $recommendationId
			));
		}

		if ($result->isSuccess(true))
		{
			$rowId = $result->getId();
			self::truncateUserViewedProducts($fuserId, $siteId);
		}

		return $rowId;
	}

	/**
	 * Returns ids map: SKU_PRODUCT_ID => PRODUCT_ID.
	 *
	 * @param array $originalIds			Input products ids.
	 * @return array
	 */
	public static function getProductsMap(array $originalIds = array())
	{
		if (empty($originalIds) && !is_array($originalIds))
			return array();

		$result = array();
		$productList = \CCatalogSku::getProductList($originalIds);
		if ($productList === false)
			$productList = array();
		foreach ($originalIds as $oneId)
			$result[$oneId] = (isset($productList[$oneId]) ? $productList[$oneId]['ID'] : $oneId);
		unset($oneId, $productList);
		return $result;
	}

	/**
	 * Returns product map: array('PRODUCT_ID' => 'ELEMENT_ID').
	 *
	 * @param int $iblockId					Iblock Id.
	 * @param int $sectionId				Section Id.
	 * @param int $fuserId					Sale user Id.
	 * @param int $excludeProductId				Exclude item Id.
	 * @param int $limit					Max count.
	 * @param int $depth					Depth level.
	 * @param string|null $siteId			Site identifier.
	 * @return array
	 */
	public static function getProductSkuMap($iblockId, $sectionId, $fuserId, $excludeProductId, $limit, $depth = 0, $siteId = null)
	{
		$map = array();

		$iblockId = (int)$iblockId;
		$sectionId = (int)$sectionId;
		$fuserId = (int)$fuserId;
		$excludeProductId = (int)$excludeProductId;
		$limit = (int)$limit;
		$depth = (int)$depth;
		if ($iblockId <= 0 || $depth < 0 || $fuserId <= 0)
			return $map;

		if (empty($siteId))
		{
			$context = Application::getInstance()->getContext();
			$siteId = $context->getSite();
		}
		if (empty($siteId))
			return $map;

		$subSections = array();
		if ($depth > 0)
		{
			$parentSectionId = Product\Viewed::getParentSection($sectionId, $depth);
			if ($parentSectionId !== null)
				$subSections[$parentSectionId] = $parentSectionId;
			unset($parentSectionId);
		}

		if (empty($subSections) && $sectionId <= 0)
		{
			$getListParams = array(
				'select' => array('PRODUCT_ID', 'ELEMENT_ID', 'DATE_VISIT'),
				'filter' => array(
					'=FUSER_ID' => $fuserId,
					'=SITE_ID' => $siteId,
					'=PARENT_ELEMENT.IBLOCK_ID' => $iblockId,
					'=PARENT_ELEMENT.WF_STATUS_ID' => 1,
					'=PARENT_ELEMENT.WF_PARENT_ELEMENT_ID' => null
				),
				'order' => array('DATE_VISIT' => 'DESC')
			);
			if ($excludeProductId > 0)
				$getListParams['filter']['!=PARENT_ELEMENT.ID'] = $excludeProductId;
			if ($limit > 0)
				$getListParams['limit'] = $limit;
			$iterator = static::getList($getListParams);
			unset($getListParams);
		}
		else
		{
			if (empty($subSections))
				$subSections[$sectionId] = $sectionId;

			$sectionQuery = new Main\Entity\Query(Iblock\SectionTable::getEntity());
			$sectionQuery->setTableAliasPostfix('_parent');
			$sectionQuery->setSelect(array('ID', 'LEFT_MARGIN', 'RIGHT_MARGIN'));
			$sectionQuery->setFilter(array('@ID' => $subSections));

			$subSectionQuery = new Main\Entity\Query(Iblock\SectionTable::getEntity());
			$subSectionQuery->setTableAliasPostfix('_sub');
			$subSectionQuery->setSelect(array('ID'));
			$subSectionQuery->setFilter(array('=IBLOCK_ID' => $iblockId));
			$subSectionQuery->registerRuntimeField(
				'',
				new Main\Entity\ReferenceField(
					'BS',
					Main\Entity\Base::getInstanceByQuery($sectionQuery),
					array('>=this.LEFT_MARGIN' => 'ref.LEFT_MARGIN', '<=this.RIGHT_MARGIN' => 'ref.RIGHT_MARGIN'),
					array('join_type' => 'INNER')
				)
			);

			$sectionElementQuery = new Main\Entity\Query(Iblock\SectionElementTable::getEntity());
			$sectionElementQuery->setSelect(array('IBLOCK_ELEMENT_ID'));
			$sectionElementQuery->setGroup(array('IBLOCK_ELEMENT_ID'));
			$filter = array('=ADDITIONAL_PROPERTY_ID' => null);
			if ($excludeProductId > 0)
				$filter['!=IBLOCK_ELEMENT_ID'] = $excludeProductId;
			$sectionElementQuery->setFilter($filter);
			unset($filter);
			$sectionElementQuery->registerRuntimeField(
				'',
				new Main\Entity\ReferenceField(
					'BSUB',
					Main\Entity\Base::getInstanceByQuery($subSectionQuery),
					array('=this.IBLOCK_SECTION_ID' => 'ref.ID'),
					array('join_type' => 'INNER')
				)
			);

			$elementQuery = new Main\Entity\Query(Iblock\ElementTable::getEntity());
			$elementQuery->setSelect(array('ID'));
			$filter = array('=IBLOCK_ID' => $iblockId, '=WF_STATUS_ID' => 1, '=WF_PARENT_ELEMENT_ID' => null);
			if ($excludeProductId > 0)
				$filter['!=ID'] = $excludeProductId;
			$elementQuery->setFilter($filter);
			unset($filter);
			$elementQuery->registerRuntimeField(
				'',
				new Main\Entity\ReferenceField(
					'BSE',
					Main\Entity\Base::getInstanceByQuery($sectionElementQuery),
					array('=this.ID' => 'ref.IBLOCK_ELEMENT_ID'),
					array('join_type' => 'INNER')
				)
			);

			$query = static::query();
			$query->setSelect(array('PRODUCT_ID', 'ELEMENT_ID', 'DATE_VISIT'));
			$query->setFilter(array('=FUSER_ID' => $fuserId, '=SITE_ID' => $siteId));
			$query->setOrder(array('DATE_VISIT' => 'DESC'));

			$query->registerRuntimeField(
				'',
				new Main\Entity\ReferenceField(
					'BE',
					Main\Entity\Base::getInstanceByQuery($elementQuery),
					array('=this.ELEMENT_ID' => 'ref.ID'),
					array('join_type' => 'INNER')
				)
			);
			if ($limit > 0)
				$query->setLimit($limit);

			$iterator = $query->exec();

			unset($query, $elementQuery, $sectionElementQuery, $subSectionQuery, $sectionQuery);
		}

		while ($row = $iterator->fetch())
			$map[$row['PRODUCT_ID']] = $row['ELEMENT_ID'];
		unset($row, $iterator);
		unset($subSections);

		return $map;
	}

	/**
	 * Clear old records.
	 *
	 * @param int $liveTime			Live time (in days).
	 * @return void
	 */
	public static function clear($liveTime = 10)
	{
		$liveTime = (int)$liveTime;
		if ($liveTime <= 0)
			return;

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();
		$liveTo = $helper->addSecondsToDateTime($liveTime * 86400, $helper->quote('DATE_VISIT'));

		$connection->query(
			'delete from '.$helper->quote(self::getTableName()).
			' where '.$liveTo.' < '.$helper->getCurrentDateTimeFunction()
		);
		unset($liveTo, $helper, $connection);
	}

	/**
	 * For agent.
	 *
	 * @return string
	 */
	public static function clearAgent()
	{
		self::clear((int)Option::get('catalog', 'viewed_time'));
		return '\Bitrix\Catalog\CatalogViewedProductTable::clearAgent();';
	}

	private static function truncateUserViewedProducts($fuserId, $siteId)
	{
		$fuserId = (int)$fuserId;
		$siteId = (string)$siteId;

		if ($fuserId <= 0 || $siteId == '')
			return;

		$maxCount = (int)Main\Config\Option::get('catalog', 'viewed_count');
		if ($maxCount <= 0)
			return;

		$iterator = self::getList(array(
			'select' => array('DATE_VISIT', 'FUSER_ID', 'SITE_ID'),
			'filter' => array('=FUSER_ID' => $fuserId, '=SITE_ID' => $siteId),
			'order' => array('FUSER_ID' => 'ASC', 'SITE_ID' => 'ASC', 'DATE_VISIT' => 'DESC'),
			'limit' => 1,
			'offset' => $maxCount
		));
		$row = $iterator->fetch();
		unset($iterator);
		if (!empty($row) && $row['DATE_VISIT'] instanceof Main\Type\DateTime)
		{
			$connection = Application::getConnection();
			$helper = $connection->getSqlHelper();

			$query = 'delete from '.$helper->quote(self::getTableName()).
				' where '.$helper->quote('FUSER_ID').' = '.$fuserId.
				' and '.$helper->quote('SITE_ID').' = \''.$helper->forSql($siteId).'\''.
				' and '.$helper->quote('DATE_VISIT').' <= '.$helper->convertToDbDateTime($row['DATE_VISIT']);
			$connection->query($query);
			unset($query, $helper, $connection);
		}
		unset($row);
	}
}