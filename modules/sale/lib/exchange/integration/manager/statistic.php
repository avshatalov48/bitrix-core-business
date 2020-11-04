<?php


namespace Bitrix\Sale\Exchange\Integration\Manager;

use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Exchange\Integration\App\IntegrationB24;
use Bitrix\Sale\Exchange\Integration\Connector\Manager;
use Bitrix\Sale\Exchange\Integration\Entity\StatusType;
use Bitrix\Sale\Exchange\Integration\EntityType;
use Bitrix\Sale\Exchange\Integration\Service\Command\Line\StatisticsProvider;
use Bitrix\Sale\Exchange\Integration\Service\Container\Collection;
use Bitrix\Sale\Exchange\Integration\Service\Container\Item;
use Bitrix\Sale\Exchange\Integration\Service\Scenarios\Statistics;
use Bitrix\Sale\Exchange\Integration\Service\Statistic\Entity\Order;
use Bitrix\Sale\Exchange\Logger\Exchange;
use Bitrix\Sale\Exchange\Logger\ProviderType;
use Bitrix\Sale\Exchange\ManagerExport;
use Bitrix\Sale\OrderTable;

class Statistic
{
	const STATISTIC_IMPORT_PACKAGE_LIMIT = 1000;
	const LOGGER_DAYS_INTERVAL = 1;

	protected $app;
	protected $collection;
	protected $exchange;

	public function __construct()
	{
		$this->app = new IntegrationB24();
		$this->collection = new Collection();
		$this->exchange = new Exchange(ProviderType::B24_INTEGRATION_NAME);
	}

	/**
	 * @return Collection
	 */
	public function getCollection(): Collection
	{
		return $this->collection;
	}

	public function isOn()
	{
		return (new Manager())->isOn();
	}

	public function modify()
	{
		if($this->isOn())
		{
			$provider = $this->getProvider();
			if(is_null($provider))
			{
				return false;
			}

			$list = $this->getListByParams($provider)
				->getCollection()
				->toArray();

			if(count($list)>0)
			{
				return (new Statistics())
					->modify($list)
					->isSuccess();
			}
		}

		return true;
	}

	protected function getStatisticsStartDate()
	{
		$startDate = new \Bitrix\Main\Type\DateTime();
		$startDate->add('-1 month');

		return $startDate;
	}

	protected function getProvider()
	{
		$res = StatisticsProvider::getList([
			'xmlId'=>$this->app
				->getCode()
		]);

		return (isset($res['error']) == false) ? $res:null;
	}

	protected function prepareParamsByProviderFields($provider)
	{
		$lastDateUpdate = null;
		if(isset($provider['statisticProviders'][0]['settings']))
		{
			$lastDateUpdate = $provider['statisticProviders'][0]['settings']['lastDateUpdate'];
		}

		return [
			'ID'=> (int)$provider['statisticProviders'][0]['id'],
			'LAST_DATE_UPDATE'=> $lastDateUpdate
		];
	}

	protected function getListByParams($provider)
	{
		$this->deleteOldEffectedRows();

		$startDate = $this->getStatisticsStartDate()
			->toString();

		\CTimeZone::Disable();

		$providerFields = $this->prepareParamsByProviderFields($provider);

		if(is_null($providerFields['LAST_DATE_UPDATE']))
		{
			$time = $startDate;
		}
		else
		{
			$lastDateUpdate = strtotime($providerFields['LAST_DATE_UPDATE']);
			$time = DateTime::createFromTimestamp($lastDateUpdate)
				->toString();
		}

		$filter['>=DATE_UPDATE'] = $time;
		$filter['=RUNNING'] = 'N';

		$logs = $this->exchange->getEffectedRows(
			$time,
			EntityType::ORDER,
			ManagerExport::EXCHANGE_DIRECTION_EXPORT);

		$r = OrderTable::getList([
			'order'=>['DATE_UPDATE'=>'ASC'],
			'filter'=>$filter,
			'limit' => static::STATISTIC_IMPORT_PACKAGE_LIMIT
		]);

		while($res = $r->fetch())
		{
			if($this->exchange->isEffected($res, $logs))
			{
				continue;
			}

			$res['AMOUNT'] = $res['PRICE'];
			$res['STATUS'] = $this->resolveStatusId($res);
			$res['ENTITY_ID'] = $res['ID'];
			$res['PROVIDER_ID'] = $providerFields['ID'];

			$this->collection->addItem(
				Item::create(
					Order::createFromArray($res))
					->setInternalIndex($res['ID'])
			);

			$this->addEffectedRows($res);
		}

		\CTimeZone::Enable();

		return $this;
	}

	protected function resolveStatusId($fields)
	{
		if($fields['PAYED'] == 'Y'  || $fields['DEDUCTED'] == 'Y')
		{
			return StatusType::SUCCESS_NAME;
		}
		elseif ($fields['CANCELED'] == 'Y')
		{
			return StatusType::FAULTY_NAME;
		}
		else
		{
			return StatusType::PROCESS_NAME;
		}
	}

	protected function deleteOldEffectedRows()
	{
		$this->exchange->deleteOldRecords(ManagerExport::EXCHANGE_DIRECTION_EXPORT, static::LOGGER_DAYS_INTERVAL);
	}

	protected function addEffectedRows(array $fields)
	{
		$this->exchange->add([
			'XML_ID' => $fields['XML_ID'],
			'ENTITY_ID' => $fields['ID'],
			'DIRECTION' => ManagerExport::EXCHANGE_DIRECTION_EXPORT,
			'ENTITY_TYPE_ID' => EntityType::ORDER,
			'ENTITY_DATE_UPDATE' => $fields['DATE_UPDATE']
		]);
	}

}