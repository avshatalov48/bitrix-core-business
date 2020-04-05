<?php
namespace Bitrix\Sale\Archive;

use Bitrix\Sale,
	Bitrix\Main;

class Order extends Sale\Order
{
	protected $discountResult = array();
	protected $dateArchived = null;
	protected $archiveVersion = Manager::SALE_ARCHIVE_VERSION;

	/**
	 * Restrict recalculation discounts
	 *
	 * @param array $data			Order data.
	 * @return Sale\Result
	 */
	public function applyDiscount(array $data)
	{
		return new Sale\Result();
	}

	/**
	 * @param array $fields
	 * @return Order
	 */
	protected static function createOrderObject(array $fields = array())
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ARCHIVE_ORDER);
		$orderClassName = $registry->getOrderClassName();

		return new $orderClassName($fields);
	}

	/**
	 * Set discount array for detail page.
	 * 
	 * @param array $resultData
	 */
	public function setDiscountData($resultData = array())
	{
		$this->discountResult = $resultData;
	}

	/**
	 * @return array
	 */
	public function getDiscountData()
	{
		return $this->discountResult;
	}

	/**
	 * @return Main\Type\DateTime
	 */
	public function getDateArchived()
	{
		return $this->dateArchived;
	}

	/**
	 * @param Main\Type\DateTime $date
	 * 
	 * @return void
	 */
	public function setDateArchived($date)
	{
		$this->dateArchived = $date;
	}

	/**
	 * @return int
	 */
	public function getVersion()
	{
		return $this->archiveVersion;
	}

	/**
	 * @param int $version		Version of archived entity.
	 *
	 * @return void
	 */
	public function setVersion($version)
	{
		$this->archiveVersion = (int)$version;
	}
}