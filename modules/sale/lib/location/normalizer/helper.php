<?php

namespace Bitrix\Sale\Location\Normalizer;

use Bitrix\Sale\Location\Name;

/**
 * Class Helper
 * @package Bitrix\Sale\Location\Normalizer
 * Different service staff.
 */
final class Helper
{
	/**
	 * Fill locations NAME_NORM FIELD
	 * @param int $startId Location name record ID. Start position.
	 * @param int $timeout Processing timeout.
	 * @return int Last processed ID.
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function fillNormNameColumn($startId = 0, $timeout = 0, $limit = 100)
	{
		/** @var INormalizer[] $normalizers */
		$startTime = microtime(false);
		$normalizers = [];
		$glParams = [
			'filter' => [
				'>=ID' => $startId,
				'=NAME_NORM' => false
			]
		];

		if($limit > 0)
		{
			$glParams['limit'] = $limit;
		}

		$res = Name\LocationTable::getList($glParams);
		$lastId = $startId;

		while($row = $res->fetch())
		{
			if(!isset($normalizers[$row['LANGUAGE_ID']]))
			{
				$normalizers[$row['LANGUAGE_ID']] = \Bitrix\Sale\Location\Normalizer\Builder::build($row['LANGUAGE_ID']);
			}

			Name\LocationTable::update(
				$row['ID'],
				[
					'NAME_NORM' => $normalizers[$row['LANGUAGE_ID']]->normalize($row['NAME'])
				]
			);

			$lastId = $row['ID'];

			if($timeout && $startTime + $timeout >= microtime(false))
			{
				break;
			}
		}

		return $lastId;
	}
}