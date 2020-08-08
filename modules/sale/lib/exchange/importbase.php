<?php
namespace Bitrix\Sale\Exchange;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\Exchange;

abstract class ImportBase
{
    const ITEM_ITEM = 'ITEM';
    const ITEM_SERVICE = 'SERVICE';

	protected $collisionErrors = false;
	protected $collisionWarnings = false;
	protected $logging = false;

    /** @var Sale\Internals\Fields */
    protected $fields;
    /** @var ISettingsImport */
    protected $settings = null;

    /** @var Exchange\ICriterion */
    protected $loadCriterion = null;
    /** @var Exchange\Internals\LoggerDiag  */
    protected $loadLogger = null;
    /** @var ICollision  */
    protected $loadCollision = null;

    /** @var  Exchange\Internals\LoggerDiag $logger */
    protected $logger = array();

	/**
	 * @return string
	 */
	static public function getFieldExternalId()
	{
		throw new Main\NotImplementedException('The method is not implemented.');
	}

	/**
	 * @return Sale\Internals\Entity $entity|ImportBase|null
	 */
    abstract public function getEntity();

    /**
     * @return int
     */
    abstract public function getOwnerTypeId();

    /**
     * Adds row to entity table
     * @param array $params
     * @return Sale\Result
     */
    abstract public function add(array $params);

    /**
     * Updates row in entity table
     * @param array $params
     * @return Sale\Result
     */
    abstract public function update(array $params);

    /**
     * Deletes row in entity table by primary key
     * @param array|null $params
     * @return Sale\Result
     */
    abstract public function delete(array $params = null);

    /**
     * @param array $fields
     * @return Sale\Result
     */
    abstract protected function checkFields(array $fields);

    /**
     * @param array $fields
	 * @return Sale\Result
     */
    abstract public function load(array $fields);

    /**
     * @param array $params
     * @return Sale\Result
     */
    public function import(array $params)
    {
        $result = new Sale\Result();
        if($this->getId()>0)
        {
            $result = $this->update($params);
        }
        elseif($this->isImportable())
        {
            $result = $this->add($params);
        }

		$this->initLogger();

        return $result;
    }

    /**
     * @return int|null
     */
    abstract public function getId();

	/**
	 * @return null|string
	 */
	public function getExternalId()
	{
		$entity = $this->getEntity();
		if(!empty($entity))
		{
			return $entity->getField(static::getFieldExternalId());
		}

		return null;
	}

    /**
     * @return bool
     */
    abstract public function isImportable();

    /**
     * @param array $values
     * @internal param array $fields
     */
    public function setFields(array $values)
    {
        foreach ($values as $key=>$value)
        {
            $this->setField($key, $value);
        }
    }

    /**
     * @param $name
     * @param $value
     */
    public function setField($name, $value)
    {
        $this->fields->set($name, $value);
    }

    /**
     * @param $name
     * @return null|string
     */
    public function getField($name)
    {
        return $this->fields->get($name);
    }

    /**
     * @return array
     */
    public function getFieldValues()
    {
        return $this->fields->getValues();
    }

    /**
     * @param array $fields
     */
    abstract public function refreshData(array $fields);

	/**
	 * @return array
	 */
	protected function getFieldsTraits()
	{
		$entity = $this->getEntity();
		return $entity->getFieldValues();
	}

	abstract public function initFields();

	/**
	 * @param $fields
	 */
	public function initFieldsFromArray($fields)
	{
		$this->setFields($fields);
	}

    /**
     * @param ISettings $settings
     */
    public function loadSettings(ISettings $settings)
    {
        $this->settings = $settings;
    }

	/**
	 * @param ICriterion $criterion
	 */
	public function loadCriterion(ICriterion $criterion)
	{
		$this->loadCriterion = $criterion;
	}

    /**
     * @return ICriterion
     * @internal param $typeId
     * @internal param $entity
     * @internal
     */
    public function getLoadedCriterion()
    {
        return $this->loadCriterion;
    }

    /**
     * @param $entity
     * @return ICriterion
     */
    public function getCurrentCriterion($entity)
    {
        /** @var ICriterion $criterion */
        $criterions = $this->getLoadedCriterion();
        return $criterions::getCurrent($this->getOwnerTypeId(), $entity);
    }

	/**
	 * @param ICollision $collision
	 */
	public function loadCollision(ICollision $collision)
	{
		$this->loadCollision = $collision;
	}

    /**
     * @internal
     * @return ICollision
     * @throws Main\ArgumentOutOfRangeException
     */
    public function getLoadedCollision()
    {
        return $this->loadCollision;
    }

    /**
     * @return ISettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param $typeId
     * @return ICollision
     */
    public function getCurrentCollision($typeId)
    {
        /** @var Exchange\OneC\ImportCollision $collision */
        $collision = $this->getLoadedCollision();
        return $collision::getCurrent($typeId);
    }

	public function loadLogger(Exchange\Internals\Logger $logger)
	{
		$this->loadLogger = $logger;
	}

	public function getLoadedLogger()
	{
		return $this->loadLogger;
	}

	public function getCurrentLogger()
	{
		$logger = $this->getLoadedLogger();
		return $logger::getCurrent();
	}

	public function initLogger()
	{
		$this->logging = true;

		$logger = $this->getCurrentLogger();
		$this->logger = $logger;
	}

	/**
	 * @return Internals\LoggerDiag
	 */
	public function getLogger()
	{
		return $this->logger;
	}

	/**
	 * @return bool
	 */
	public function hasCollisionErrors()
	{
		return $this->collisionErrors;
	}

	/**
	 * @return bool
	 */
	public function hasCollisionWarnings()
	{
		return $this->collisionWarnings;
	}

	public function hasLogging()
	{
		return $this->logging;
	}

	/**
	 * @return array|null
	 * @deprecated
	 */
	static private function getSaleExport()
	{
		static $exportProfiles = null;

		if($exportProfiles === null)
		{
			$exportProfiles = \CSaleExport::getSaleExport();
		}
		return $exportProfiles;

	}

	/**
	 * @param Sale\Order $order
	 * @return array
	 * @deprecated
	 */
	static public function getBusinessValue(\Bitrix\Sale\IBusinessValueProvider $entity)
	{
		$order = static::getBusinessValueOrderProvider($entity);

		$orderFields = $order->getFieldValues();
		$paymentList = array();
		$shipmentList = array();

		if($paymentCollection = $order->getPaymentCollection())
		{
			/** @var Sale\Payment $payment */
			foreach ($paymentCollection as $payment)
			{
				$paymentList[$payment->getId()] = $payment->getPaymentSystemName();
			}
		}
		if($shipmentCollection = $order->getShipmentCollection())
		{
			/** @var Sale\Shipment $shipment */
			foreach ($shipmentCollection as $shipment)
			{
				$shipmentList[$shipment->getId()] = $shipment->getDeliveryName();
			}
		}

		$arProp = \CSaleExport::prepareSaleProperty(
			$orderFields,
			false,
			false,
			$paymentList,
			$shipmentList,
			$locationStreetPropertyValue,
			$order
		);

		$exportProfiles = static::getSaleExport();
		$exportProfile = (array_key_exists($order->getPersonTypeId(), $exportProfiles) ? $exportProfiles[$order->getPersonTypeId()]: array());

		$properties = \CSaleExport::prepareSalePropertyRekv(
			$entity,
			$exportProfile,
			$arProp,
			$locationStreetPropertyValue
		);
		$properties['REKV'] = static::modifyRekv($properties['REKV'], $exportProfile);

		return $properties;
	}

	/**
	 * @param $rekv
	 * @param array $exportProfile
	 * @return array
	 */
	static private function modifyRekv($rekv, array $exportProfile)
	{
		$result = array();
		foreach ($rekv as $k=>$v)
		{
			if(isset($exportProfile[$k]) && $exportProfile[$k]['NAME'] <> '' && $v <> '')
			{
				$result[$exportProfile[$k]['NAME']] = $v;
			}
		}
		return $result;
	}

	/**
	 * @param Sale\IBusinessValueProvider $entity
	 * @return Sale\Order
	 */
	static protected function getBusinessValueOrderProvider(\Bitrix\Sale\IBusinessValueProvider $entity)
	{
		throw new Main\NotImplementedException('The method is not implemented.');
	}
}