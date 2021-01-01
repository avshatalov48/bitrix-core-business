<?php
namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO\Assembler;

use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\Filter;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\Pool;

class PoolAssembler implements Assembler
{
	/**
	 * @param HttpRequest $request
	 *
	 * @return Pool
	 */
	public static function toDTO(HttpRequest $request)
	{
		$pool = new Pool();
		$id = (int)$request->get('id');
		$filters = $request->get('filter');

		if($id)
		{
			$pool->setId($id);
		}

		if (!empty($filters))
		{
			foreach ($filters as $key => $filter)
			{
				if (is_array($filter))
				{
					foreach ($filter as $filterValue)
					{
						$poolFilter = new Filter();
						$pool->addFilter(
							$poolFilter->setValue($filterValue)
								->setKey(strtolower($key))
						);
					}
				}
			}
		}

		$willExpire = \DateTime::createFromFormat('d.m.Y H:i:s',
			$request->get('will_expire'));

		if(!$willExpire)
		{
			$willExpire = new \DateTime();
			$willExpire->setTime(23,59,59);
		}

		$pool->setMayContainAdultContent(json_decode($request->get('may_contain_adult_content')));
		$pool->setPrivateName($request->get('private_name'));
		$pool->setPublicDescription($request->get('public_description'));
		$pool->setProjectId($request->get('project_id'));
		$pool->setRewardPerAssignment($request->get('reward_per_assignment'));
		$pool->setWillExpire(
			$willExpire->format('Y-m-d\TH:i:s')
		);

		$pool->setDefaults(PoolDefaultsAssembler::toDTO($request));

		return $pool;
	}
}