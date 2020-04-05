<?php
namespace Bitrix\Sale\Archive\Recovery;

use Bitrix\Main,
	Bitrix\Sale\Archive,
	Bitrix\Sale\Internals;

/**
 * @package Bitrix\Sale\Archive\Recovery
 */
class Restorer
{
	private $archiveId = null;
	private $version = null;
	private $dateArchived = null;
	/** @var Builder $builder */
	private $builder = null;
	private $firstVersion = 1;

	const EVENT_ON_ARCHIVE_ORDER_BEFORE_RESTORED = "OnSaleArchiveOrderBeforeRestored";

	protected function __construct($id)
	{
		$this->archiveId = (int)$id;
	}

	/**
	 * @param $id
	 *
	 * @return Restorer|null
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function load($id)
	{
		$id = (int)$id;
		if ($id <= 0)
		{
			return null;
		}

		$archivedOrder = Internals\OrderArchiveTable::getList(
			array(
				"select" => array("*", "ORDER_FULL" => "ORDER_PACKED.ORDER_DATA"),
				"filter" => array("=ID" => $id),
				"limit" => 1
			)
		);
		$orderFields = $archivedOrder->fetch();

		if (!$orderFields)
			return null;

		$restorer = new self($id);
		if (!$restorer->checkVersion($orderFields['VERSION']))
		{
			return null;
		}

		$restorer->version = (int)$orderFields['VERSION'];
		$restorer->dateArchived = $orderFields['DATE_ARCHIVED'];
		$builder = $restorer->loadBuilder();
		$builder->setPackedOrder($restorer->packField($orderFields['ORDER_FULL']));
		$orderFields['ID'] = $orderFields['ORDER_ID'];
		$archiveFields = ['ORDER_DATA', 'ORDER_FULL', 'ORDER_ID', 'VERSION', 'DATE_ARCHIVED'];
		foreach ($archiveFields as $fieldName)
		{
			unset($orderFields[$fieldName]);
		}

		$builder->setEntityFields('ORDER', $orderFields);

		$basketArchivedItems = Internals\BasketArchiveTable::getList(
			array(
				"select" => array("*", "BASKET_FULL" => "BASKET_PACKED.BASKET_DATA"),
				"filter" => array("ARCHIVE_ID" => $id)
			)
		);

		$basketItems = [];
		while ($item = $basketArchivedItems->fetch())
		{
			$builder->addPackedBasketItem(
				$item['ID'],
				$restorer->packField($item['BASKET_FULL'])
			);
			unset($item['BASKET_DATA'], $item['BASKET_FULL'], $item['ARCHIVE_ID']);
			$basketItems[$item['ID']] = $item;
		}
		$builder->setEntityFields('BASKET', $basketItems);
		$restorer->setBuilder($builder);
		return $restorer;
	}

	/**
	 * @param $version
	 *
	 * @return bool
	 */
	private function checkVersion($version)
	{
		return ($this->firstVersion <= (int)$version && (int)$version <= Archive\Manager::SALE_ARCHIVE_VERSION);
	}

	/**
	 * @return Builder|null
	 */
	private function loadBuilder()
	{
		switch ($this->version)
		{
			case 1:
			case 2:
				return $this->builder = new FirstSchemeBuilder();
		}

		return null;
	}

	/**
	 * @param Buildable $builder
	 */
	private function setBuilder(Buildable $builder)
	{
		$this->builder = $builder;
	}

	/**
	 * @param string $field
	 *
	 * @return PackedField
	 */
	public function packField($field = '')
	{
		if ($this->version === 1)
		{
			return new SerializedField($field);
		}
		else
		{
			return new JsonField($field);
		}
	}

	/**
	 * @return Builder
	 */
	public function getBuilder()
	{
		return $this->builder;
	}

	/**
	 * @return int
	 */
	public function getArchiveVersion()
	{
		return $this->version;
	}

	/**
	 * @return int
	 */
	public function getArchiveId()
	{
		return $this->archiveId;
	}

	/**
	 * @return Archive\Order
	 */
	public function restoreOrder()
	{
		$eventManager = Main\EventManager::getInstance();
		if ($eventsList = $eventManager->findEventHandlers('sale', self::EVENT_ON_ARCHIVE_ORDER_BEFORE_RESTORED))
		{
			$event = new Main\Event('sale', self::EVENT_ON_ARCHIVE_ORDER_BEFORE_RESTORED, array(
				'ENTITY' => $this
			));
			$event->send();
		}

		$archivedOrder = $this->builder->buildOrder();
		$archivedOrder->setVersion($this->version);
		$archivedOrder->setDateArchived($this->dateArchived);
		return $archivedOrder;
	}
}