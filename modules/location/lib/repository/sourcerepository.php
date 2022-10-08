<?php

namespace Bitrix\Location\Repository;

use Bitrix\Location\Entity\Source;
use Bitrix\Location\Model\SourceTable;

/**
 * Class SourceRepository
 * @package Bitrix\Location\Repository
 * @internal
 */
final class SourceRepository
{
	/** @var Source\OrmConverter */
	private $ormConverter;

	/**
	 * SourceRepository constructor.
	 * @param Source\OrmConverter $ormConverter
	 */
	public function __construct(Source\OrmConverter $ormConverter)
	{
		$this->ormConverter = $ormConverter;
	}

	/**
	 * @return Source[]
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function findAll(): array
	{
		$result = [];

		$queryResult = SourceTable::getList();

		while ($ormSource = $queryResult->fetchObject())
		{
			$result[] = $this->ormConverter->convertFromOrm($ormSource);
		}

		return $result;
	}

	/**
	 * @param string $code
	 * @return Source|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function findByCode(string $code): ?Source
	{
		$result = null;

		$ormSource = SourceTable::getList(
			[
				'filter' => [
					'=CODE' => $code
				],
				'limit' => 1,
			]
		)->fetchObject();

		if (!$ormSource)
		{
			return null;
		}

		return $this->ormConverter->convertFromOrm($ormSource);
	}

	/**
	 * @param Source $source
	 * @return \Bitrix\Main\ORM\Data\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function save(Source $source)
	{
		return $this->ormConverter->convertToOrm($source)->save();
	}
}
