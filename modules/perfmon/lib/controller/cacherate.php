<?php

namespace Bitrix\Perfmon\Controller;

use \Bitrix\Main\Error;
use \Bitrix\Main\IO\Path;
use Bitrix\Perfmon\Model\CacheHitrateTable;

class Cacherate extends \Bitrix\Main\Engine\Controller
{
	public function recountStatAction(int $offset = 0, int $processed = 0): ?array
	{
		if ($this->getCurrentUser()->isAdmin())
		{
			$connection = \Bitrix\Main\Application::getConnection();

			if ($offset === 0)
			{
				$totalCount = (int) $connection->query('SELECT COUNT(1) AS CNT FROM b_perf_cache')->fetch()['CNT'];
				\Bitrix\Main\Config\Option::set('perfmon', 'cacheTotalItems', $totalCount);
				\Bitrix\Main\Config\Option::set('perfmon', 'cacheRateTotal', 0);

				$connection->truncateTable(CacheHitrateTable::getTableName());
			}
			else
			{
				$totalCount = (int) \Bitrix\Main\Config\Option::get('perfmon', 'cacheTotalItems', 0);
			}

			if ($totalCount === 0)
			{
				return [
					'TOTAL_ITEMS' => 0,
					'PROCESSED_ITEMS' => 0,
					'STATUS' => 'COMPLETED',
					'SUMMARY' => 'NOTHING',
				];
			}

			$lastId = 0;
			$cacheRate = [];
			$hitrateRes = $connection->query('
				SELECT
					cache.ID,
					cache.BASE_DIR,
					cache.INIT_DIR,
					cache.FILE_NAME,
					cache.MODULE_NAME,
					cache.OP_MODE,
					cache.CACHE_SIZE
				FROM b_perf_cache cache
				WHERE
					cache.ID > ' . $offset . '
				ORDER BY cache.ID ASC
				LIMIT 500
			');

			$count = 0;
			while ($cache = $hitrateRes->fetch())
			{
				$lastId = (int) $cache['ID'];
				$count++;
				if ($cache['FILE_NAME'] === '')
				{
					continue;
				}
				$hash = md5(Path::normalize($cache['BASE_DIR'] . $cache['INIT_DIR'] . $cache['FILE_NAME']));
				if (isset($cacheRate[$hash]))
				{
					if ($cache['OP_MODE'] === 'R')
					{
						$cacheRate[$hash]['READ_COUNT'] += 1;
					}
					elseif ($cache['OP_MODE'] === 'W')
					{
						$cacheRate[$hash]['WRITE_COUNT'] += 1;
					}
					elseif ($cache['OP_MODE'] === 'C')
					{
						$cacheRate[$hash]['CLEAN_COUNT'] += 1;
					}

					$cacheRate[$hash]['CACHE_SIZE'] = (int) $cacheRate['CACHE_SIZE'] > $cache['CACHE_SIZE'] ? $cacheRate['CACHE_SIZE'] : $cache['CACHE_SIZE'];
				}
				else
				{
					$cacheRate[$hash] = [
						'HASH' => $hash,
						'MODULE_ID' => $cache['MODULE_NAME'],
						'BASE_DIR' => $cache['BASE_DIR'],
						'INIT_DIR' => $cache['INIT_DIR'],
						'FILE_NAME' => $cache['FILE_NAME'],
						'READ_COUNT' => $cache['OP_MODE'] === 'R' ? 1 : 0,
						'WRITE_COUNT' => $cache['OP_MODE'] === 'W' ? 1 : 0,
						'CLEAN_COUNT' => $cache['OP_MODE'] === 'C' ? 1 : 0,
						'CACHE_SIZE' => (int) $cache['CACHE_SIZE'],
					];
				}
			}

			if (!empty($cacheRate))
			{
				$toUpdateRate = [];
				$existedRates = CacheHitrateTable::query()
					->setSelect(['ID', 'HASH', 'READ_COUNT', 'WRITE_COUNT', 'CLEAN_COUNT', 'CACHE_SIZE'])
					->whereIn('HASH', array_keys($cacheRate))
					->exec()
					->fetchCollection()
				;

				foreach ($existedRates as $eRate)
				{
					$hash = $eRate->getHash();
					$eRate->set('READ_COUNT', (int) $eRate->get('READ_COUNT') + $cacheRate[$hash]['READ_COUNT']);
					$eRate->set('WRITE_COUNT', (int) $eRate->get('WRITE_COUNT') + $cacheRate[$hash]['WRITE_COUNT']);
					$eRate->set('CLEAN_COUNT', (int) $eRate->get('CLEAN_COUNT') + $cacheRate[$hash]['CLEAN_COUNT']);
					if ($cacheRate[$hash]['CACHE_SIZE'] > $eRate->get('CACHE_SIZE'))
					{
						$eRate->set('CACHE_SIZE', $cacheRate[$hash]['CACHE_SIZE']);
					}
					$toUpdateRate[$hash] = true;
				}

				$toAddRate = array_diff_key($cacheRate, $toUpdateRate);
				unset($cacheRate);

				foreach ($toAddRate as $rate)
				{
					$newRate = new \Bitrix\Perfmon\Model\EO_CacheHitrate;
					foreach ($rate as $fieldName => $fieldValue)
					{
						$newRate->set($fieldName, $fieldValue);
					}
					$existedRates->add($newRate);
				}

				$saveResult = $existedRates->save(true);

				if (!$saveResult->isSuccess())
				{
					$this->addError(new Error('Couldn\'t add/update rows'));

					return null;
				}

				return [
					'TOTAL_ITEMS' => $totalCount,
					'PROCESSED_ITEMS' => $processed + $count,
					'STATUS' => 'PROGRESS',
					'SUMMARY' => '',
					'LAST_ID' => $lastId,
				];
			}

			return [
				'TOTAL_ITEMS' => $totalCount,
				'PROCESSED_ITEMS' => $totalCount,
				'STATUS' => 'COMPLETED',
				'SUMMARY' => '',
			];
		}

		$this->addError(new Error('Who are you?'));

		return null;
	}

	public function countRateAction(int $offset = 0, int $processed = 0): array|null
	{
		if ($this->getCurrentUser()->isAdmin())
		{
			if ($offset === 0)
			{
				$totalCount = (int) CacheHitrateTable::getCount(['>READ_COUNT' => 0]);
				\Bitrix\Main\Config\Option::set('perfmon', 'rateTotalItems', $totalCount);
			}
			else
			{
				$totalCount = (int) \Bitrix\Main\Config\Option::get('perfmon', 'rateTotalItems', 0);
			}

			if ($totalCount === 0)
			{
				return [
					'TOTAL_ITEMS' => 0,
					'PROCESSED_ITEMS' => 0,
					'STATUS' => 'COMPLETED',
					'SUMMARY' => 'NOTHING',
				];
			}

			$lastId = $offset;
			$count = 0;
			$rateData = CacheHitrateTable::query()
				->setSelect(['ID', 'READ_COUNT', 'WRITE_COUNT', 'CLEAN_COUNT'])
				->where('ID', '>', $offset)
				->where('READ_COUNT', '>', 0)
				->setOrder(['ID' => 'ASC'])
				->setLimit(200)
				->fetchCollection()
			;

			if (!$rateData->isEmpty())
			{
				foreach ($rateData as $rate)
				{
					$lastId = $rate->getId();
					$count++;

					$rate->setRate(round($rate->get('READ_COUNT') / ($rate->get('READ_COUNT') + $rate->get('WRITE_COUNT') + $rate->get('CLEAN_COUNT')) * 100), 1);
				}

				$rateData->save(true);

				return [
					'TOTAL_ITEMS' => $totalCount,
					'PROCESSED_ITEMS' => $processed + $count,
					'STATUS' => 'PROGRESS',
					'SUMMARY' => '',
					'LAST_ID' => $lastId,
				];
			}

			return [
				'TOTAL_ITEMS' => $totalCount,
				'PROCESSED_ITEMS' => $totalCount,
				'STATUS' => 'COMPLETED',
				'SUMMARY' => '',
			];
		}

		$this->addError(new Error('Who are you?'));

		return null;
	}

	public function countTotalRateAction()
	{
		if ($this->getCurrentUser()->isAdmin())
		{
			$totalRate = CacheHitrateTable::query()
				->addSelect(\Bitrix\Main\Entity\Query::expr()->avg('RATE'), 'TOTALRATE')
				->fetch()
			;
			if ($totalRate)
			{
				$totalRate = round($totalRate['TOTALRATE'], 1);
			}
			else
			{
				$totalRate = 0;
			}

			\Bitrix\Main\Config\Option::set('perfmon', 'cacheRateTotal', $totalRate);
			\Bitrix\Main\Config\Option::set('perfmon', 'cacheRateDate', (new \DateTime())->getTimestamp());

			return [
				'TOTAL_ITEMS' => 1,
				'PROCESSED_ITEMS' => 1,
				'STATUS' => 'COMPLETED',
				'SUMMARY' => '',
				'TOTAL_RATE' => $totalRate,
			];
		}
	}
}
