<?php

namespace Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Collector;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type\Date;
use Bitrix\Main\UserAccessTable;
use Bitrix\Socialnetwork\LogRightTable;

class SonetLogCollector
{
	private const RECOUNT_FROM = 'lf_cnt_recount_from';
	private int $userId;

	/**
	 * ex. ['SG1', 'SG2', ...]
	 * @var array
	 */
	private array $socialGroups = [];

	private array $accessCodes = [];

	private static array $instances = [];

	public static function getInstance(int $userId)
	{
		if (!array_key_exists($userId, self::$instances))
		{
			self::$instances[$userId] = new self($userId);
		}

		return self::$instances[$userId];
	}

	private function __construct(int $userId)
	{
		$this->userId = $userId;
	}

	public function fetch(int $limit, int $offset): array
	{
		$result = [];

		$query = LogRightTable::query()
			->setSelect([
				'LOG_ID',
			])
			->whereIn('GROUP_CODE', $this->getAccessCodes())
			->where('LOG_UPDATE', '>', $this->getDateStartFrom())
			->setLimit($limit)
			->setOffset($offset)
			->exec();

		foreach ($query->fetchAll() as $row)
		{
			$result[] = $row['LOG_ID'];
		}

		return $result;
	}

	public function fetchTotal(): int
	{
		$res = LogRightTable::getList([
			'select' => ['CNT'],
			'filter' => [
				'GROUP_CODE' => $this->getAccessCodes(),
				'>LOG_UPDATE' => $this->getDateStartFrom()
			],
			'runtime' => [
				new ExpressionField('CNT', 'COUNT(*)'),
			]
		])->fetch();

		return $res['CNT'] ?? 0;
	}


	private function getUserAccessCodes(): array
	{
		return [
			'G2',
			'AU',
			'U'.$this->userId,
		];
	}

	private function getUserAccessSocialGroups(): array
	{
		if (!empty($this->socialGroups))
		{
			return $this->socialGroups;
		}

		$query = UserAccessTable::query()
			->setDistinct()
			->setSelect([
				'ACCESS_CODE',
			])
			->where('USER_ID', '=', $this->userId)
			->where('PROVIDER_ID', '=', 'socnetgroup')
			->exec();

		foreach ($query->fetchAll() as $group)
		{
			$matches = [];
			preg_match('/SG([0-9]+)/m', $group['ACCESS_CODE'], $matches);

			if (isset($matches[0]))
			{
				$this->socialGroups[] = $matches[0];
			}
		}

		return $this->socialGroups;
	}

	private function getDateStartFrom(): Date
	{
		$recountFromOption = Option::get('socialnetwork', self::RECOUNT_FROM, 'null', '-');

		if ($recountFromOption !== 'null' && strtotime($recountFromOption))
		{
			return new Date($recountFromOption, 'Y-m-d H:i:s');
		}

		$format = 'Y-m-d H:i:s';
		$twoWeeks = date($format, strtotime('-14 days'));

		return new Date($twoWeeks, $format);
	}

	private function getAccessCodes(): array
	{
		if (!empty($this->accessCodes))
		{
			return $this->accessCodes;
		}

		$this->accessCodes = array_merge(
			$this->getUserAccessSocialGroups(),
			$this->getUserAccessCodes()
		);

		return $this->accessCodes;
	}
}