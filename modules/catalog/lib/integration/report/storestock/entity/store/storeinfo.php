<?php

namespace Bitrix\Catalog\Integration\Report\StoreStock\Entity\Store;


use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Localization\Loc;

abstract class StoreInfo
{
	private static array $storeNameList = [];

	private int $storeId;

	public function __construct(int $storeId)
	{
		$this->storeId = $storeId;
	}

	public function getStoreId(): int
	{
		return $this->storeId;
	}

	/**
	 * Return name of store
	 * <br>if name was already loaded by <b>loadStoreName</b> method, it returns it
	 * <br>otherwise it will load name throw <b>loadStoreName</b> and returns it
	 * @return string
	 */
	public function getStoreName(): string
	{
		if (!isset(self::$storeNameList[$this->storeId]))
		{
			self::loadStoreName($this->storeId);
		}

		return self::$storeNameList[$this->storeId];
	}

	/**
	 * Load stores names with <b>$storeIds</b> id
	 * @param int ...$storeIds
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function loadStoreName(int ...$storeIds): void
	{
		$storeNamesData = StoreTable::getList([
			'select' => ['ID', 'TITLE'],
			'filter' => ['=ID' => $storeIds],
		])->fetchAll();

		foreach ($storeIds as $storeId)
		{
			if (!isset(self::$storeNameList[$storeId]))
			{
				self::setStoreName($storeId, Loc::getMessage('STORE_INFO_DEFAULT_STORE_NAME'));
			}
		}

		foreach ($storeNamesData as $storeNameData)
		{
			if (!empty($storeNameData['TITLE']))
			{
				self::setStoreName($storeNameData['ID'], $storeNameData['TITLE']);
			}
		}
	}

	public static function setStoreName(int $storeId, string $storeName): void
	{
		self::$storeNameList[$storeId] = $storeName;
	}

	/**
	 * Return sum of store based on products price
	 * @return float
	 */
	abstract public function getCalculatedSumPrice(): float;
}