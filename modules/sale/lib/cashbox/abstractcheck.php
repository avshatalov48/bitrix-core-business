<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Sale\Cashbox\Internals\CashboxCheckTable;
use Bitrix\Sale\Cashbox\Internals\Check2CashboxTable;
use Bitrix\Sale\Internals\CollectableEntity;
use Bitrix\Sale\Order;
use Bitrix\Catalog;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\Registry;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentCollection;

/**
 * Class AbstractCheck
 * @package Bitrix\Sale\Cashbox
 */
abstract class AbstractCheck
{
	public const PARAM_FISCAL_DOC_NUMBER = 'fiscal_doc_number';
	public const PARAM_FISCAL_DOC_ATTR = 'fiscal_doc_attribute';
	public const PARAM_FISCAL_RECEIPT_NUMBER = 'fiscal_receipt_number';
	public const PARAM_FN_NUMBER = 'fn_number';
	public const PARAM_SHIFT_NUMBER = 'shift_number';
	public const PARAM_REG_NUMBER_KKT = 'reg_number_kkt';
	public const PARAM_DOC_TIME = 'doc_time';
	public const PARAM_DOC_SUM = 'doc_sum';
	public const PARAM_CALCULATION_ATTR = 'calculation_attribute';

	public const CALCULATED_SIGN_INCOME = 'income';
	public const CALCULATED_SIGN_CONSUMPTION = 'consumption';

	public const SHIPMENT_TYPE_NONE = '';
	public const PAYMENT_TYPE_CASH = 'cash';
	public const PAYMENT_TYPE_ADVANCE = 'advance';
	public const PAYMENT_TYPE_CASHLESS = 'cashless';
	public const PAYMENT_TYPE_CREDIT = 'credit';

	public const SUPPORTED_ENTITY_TYPE_PAYMENT = 'payment';
	public const SUPPORTED_ENTITY_TYPE_SHIPMENT = 'shipment';
	public const SUPPORTED_ENTITY_TYPE_ALL = 'all';
	public const SUPPORTED_ENTITY_TYPE_NONE = 'none';

	protected const EVENT_ON_CHECK_PREPARE_DATA = 'OnSaleCheckPrepareData';

	/** @var array $fields */
	protected $fields = array();

	/** @var array $cashboxList */
	protected $cashboxList = array();

	/** @var CollectableEntity[] $entities */
	protected $entities = array();

	/**
	 * @return string
	 */
	abstract public static function getType();

	/**
	 * @return string
	 */
	abstract public static function getCalculatedSign();

	/**
	 * @return string
	 */
	abstract public static function getName();

	/**
	 * @return array
	 */
	abstract public function getDataForCheck();

	/**
	 * @return array
	 */
	abstract protected function extractDataInternal();

	/**
	 * @return string
	 */
	abstract static function getSupportedEntityType();

	/**
	 * @param string $handler
	 * @return null|Check
	 */
	public static function create($handler)
	{
		if (class_exists($handler))
		{
			return new $handler();
		}

		return null;
	}

	/**
	 * Check constructor.
	 */
	protected function __construct()
	{
		$this->fields['TYPE'] = static::getType();
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	public function getField($name)
	{
		return $this->fields[$name];
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function setField($name, $value)
	{
		$this->fields[$name] = $value;
	}

	/**
	 * @param $fields
	 */
	public function setFields($fields)
	{
		foreach ($fields as $name => $value)
		{
			$this->setField($name, $value);
		}
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		if (!$this->getField('LINK_PARAMS'))
		{
			return '';
		}

		$cashbox = Manager::getObjectById($this->getField('CASHBOX_ID'));
		if (!$cashbox)
		{
			return '';
		}

		$ofd = $cashbox->getOfd();
		if (!$ofd)
		{
			return '';
		}

		return $ofd->generateCheckLink($this->getField('LINK_PARAMS'));
	}

	/**
	 * @param array $cashboxList
	 */
	public function setAvailableCashbox(array $cashboxList)
	{
		$this->cashboxList = $cashboxList;
	}

	/**
	 * @param array $entities
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 */
	public function setEntities(array $entities)
	{
		$this->entities = $entities;

		$orderId = null;
		$entityRegistryType = null;

		foreach ($this->entities as $entity)
		{
			if ($entity instanceof Payment)
			{
				$this->fields['PAYMENT_ID'] = $entity->getId();
				$this->fields['SUM'] = $entity->getSum();
				$this->fields['CURRENCY'] = $entity->getField('CURRENCY');
			}

			// compatibility
			if ($entity instanceof Shipment)
			{
				$this->fields['SHIPMENT_ID'] = $entity->getId();
			}

			if ($entityRegistryType === null)
			{
				$entityRegistryType = $entity::getRegistryType();
			}
			elseif ($entityRegistryType !== $entity::getRegistryType())
			{
				throw new Main\ArgumentTypeException('entities');
			}

			/** @var PaymentCollection|ShipmentCollection $collection */
			$collection = $entity->getCollection();

			if ($orderId === null)
			{
				$orderId = $collection->getOrder()->getId();
			}
			elseif ($orderId != $collection->getOrder()->getId())
			{
				throw new Main\ArgumentTypeException('entities');
			}
		}

		$this->fields['ORDER_ID'] = $orderId;
		$this->fields['ENTITY_REGISTRY_TYPE'] = $entityRegistryType;
	}

	/**
	 * @return array|CollectableEntity[]
	 * @throws Main\SystemException
	 */
	public function getEntities()
	{
		if ($this->entities)
		{
			return $this->entities;
		}

		$registry = Registry::getInstance($this->fields['ENTITY_REGISTRY_TYPE']);

		if ($this->fields['ORDER_ID'] > 0)
		{
			$orderId = $this->fields['ORDER_ID'];
		}
		elseif ($this->fields['PAYMENT_ID'] > 0)
		{
			/** @var Payment $paymentClassName */
			$paymentClassName = $registry->getPaymentClassName();
			$dbRes = $paymentClassName::getList([
				'filter' => [
					'ID' => $this->fields['PAYMENT_ID']
				]
			]);
			$data = $dbRes->fetch();
			$orderId = $data['ORDER_ID'];
		}
		elseif ($this->fields['SHIPMENT_ID'] > 0)
		{
			/** @var Shipment $shipmentClassName */
			$shipmentClassName = $registry->getShipmentClassName();
			$dbRes = $shipmentClassName::getList([
				'filter' => [
					'ID' => $this->fields['SHIPMENT_ID']
				]
			]);
			$data = $dbRes->fetch();
			$orderId = $data['ORDER_ID'];
		}
		else
		{
			throw new Main\SystemException();
		}

		if ($orderId > 0)
		{
			$orderClassName = $registry->getOrderClassName();

			/** @var Order $order */
			$order = $orderClassName::load($orderId);
			if ($order)
			{
				if ($this->fields['PAYMENT_ID'] > 0)
				{
					$payment = $order->getPaymentCollection()->getItemById($this->fields['PAYMENT_ID']);
					if ($payment)
					{
						$this->entities[] = $payment;
					}
				}

				if ($this->fields['SHIPMENT_ID'] > 0)
				{
					$shipment = $order->getShipmentCollection()->getItemById($this->fields['SHIPMENT_ID']);
					if ($shipment)
					{
						$this->entities[] = $shipment;
					}
				}
			}
		}

		return $this->entities;
	}

	/**
	 * @return Main\ORM\Data\AddResult|Main\ORM\Data\UpdateResult
	 * @throws \Exception
	 */
	public function save()
	{
		if ((int)$this->fields['ID'] > 0)
		{
			return CashboxCheckTable::update($this->fields['ID'], $this->fields);
		}

		$this->fields['DATE_CREATE'] = new Main\Type\DateTime();

		$result = CashboxCheckTable::add($this->fields);
		if (!$result->isSuccess())
		{
			return $result;
		}

		$checkId = $result->getId();
		$this->fields['ID'] = $checkId;

		foreach ($this->cashboxList as $cashbox)
		{
			Check2CashboxTable::add([
				'CHECK_ID' => $checkId,
				'CASHBOX_ID' => $cashbox['ID']
			]);
		}

		return $result;
	}

	/**
	 * @param $cashboxId
	 */
	public function linkCashbox($cashboxId)
	{
		$this->fields['CASHBOX_ID'] = $cashboxId;
	}

	/**
	 * @param $settings
	 */
	public function init($settings)
	{
		$this->fields = $settings;
	}

	/**
	 * @return array|null
	 */
	protected function extractData()
	{
		$result = $this->extractDataInternal();

		$event = new Main\Event(
			'sale',
			self::EVENT_ON_CHECK_PREPARE_DATA,
			[$result, static::getType()]
		);
		$event->send();

		if ($event->getResults())
		{
			foreach ($event->getResults() as $eventResult)
			{
				if ($eventResult->getType() !== Main\EventResult::ERROR)
				{
					$result = $eventResult->getParameters();
				}
			}
		}

		return $result;
	}


	/**
	 * @param $vatRate
	 * @return int|mixed
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function getVatIdByVatRate($vatRate)
	{
		static $vatList = array();

		if (!$vatList)
		{
			if (Main\Loader::includeModule('catalog'))
			{
				$dbRes = Catalog\VatTable::getList(array('filter' => array('ACTIVE' => 'Y')));
				while ($data = $dbRes->fetch())
				{
					$vatList[(int)$data['RATE']] = (int)$data['ID'];
				}
			}
		}

		if (!isset($vatList[$vatRate]))
		{
			return 0;
		}

		return $vatList[$vatRate];
	}
}
