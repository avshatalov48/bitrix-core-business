<?php


namespace Bitrix\Sale\Exchange\Integration\Service\Command\Batch;


use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Sale\Exchange\Integration\EntityType;
use Bitrix\Sale\Exchange\Integration\Service\Command\IProxy;
use Bitrix\Sale\Exchange\Integration\Service\Container\Collection;
use Bitrix\Sale\Exchange\Integration\Service\Container\Item;
use Bitrix\Sale\Exchange\Integration\Service\Statistic\Entity\Order;

class Statistics
	implements IProxy
{

	protected $collection;

	/**
	 * @return Collection
	 */
	public function getCollection(): Collection
	{
		return $this->collection;
	}

	public function __construct()
	{
		$this->collection = new Collection();
	}
	public function init($params)
	{
		foreach($params as $index=>$item)
		{
			$this->collection->addItem(
				Item::create(
					(new Order())
						->setEntityId($index)
						->setEntityTypeId(EntityType::ORDER)
						->setDateUpdate($item['dateUpdate'])
						->setCurrency($item['currency'])
						->setProviderId($item['providerId'])
						->setStatus($item['status'])
						->setXmlId($item['xmlId'])
						->setAmount($item['amount']))
					->setInternalIndex($index)
			);
		}

		return $this;
	}

	static public function getProxy()
	{
		return new \Bitrix\Sale\Exchange\Integration\Rest\RemoteProxies\Sale\Statistics();
	}

	/**
	 * @return Result
	 */
	public function modify()
	{
		/** @var Item $item */

		foreach ($this->getCollection() as $item)
		{
			$providerId = $item->getEntity()->getProviderId();
			break;
		}

		return $this
			->modifyFromParams([
				'provider'=>['id'=>$providerId],
				'statistics'=>$this
					->getCollection()
					->toArray()
			]);
	}

	static protected function proxyModify(array $list)
	{
		$proxy = static::getProxy();
		$r = $proxy->modify($list);
		if($r->isSuccess())
		{
			$result = $r->getData()['DATA']['result'];
		}
		else
		{
			$result['error'] = $r->getErrorMessages();
		}

		return $result;
	}

	/**
	 * @param array $params
	 * @return Result
	 */
	protected function modifyFromParams(array $params)
	{
		$r = new Result();

		$res = static::proxyModify($params);

		if (!empty($res['error']))
		{
			foreach ($res['error'] as $error)
			{
				$r->addError(new Error($error));
			}
		}

		return $r;
	}

}