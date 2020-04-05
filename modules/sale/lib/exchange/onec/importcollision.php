<?php
namespace Bitrix\Sale\Exchange\OneC;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Sale\Exchange\Entity\EntityImport;
use Bitrix\Sale\Exchange\Entity\OrderImport;
use Bitrix\Sale\Exchange\Entity\PaymentImport;
use Bitrix\Sale\Exchange\Entity\ShipmentImport;
use Bitrix\Sale\Exchange\EntityCollisionType;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Exchange\ICollision;
use Bitrix\Sale\Exchange\ImportBase;
use Bitrix\Sale\Exchange\ProfileImport;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Result;
use Bitrix\Sale\Shipment;

class ImportCollision implements ICollision
{
    protected $entityTypeId = EntityType::UNDEFINED;
    protected $typeId = null;
    protected $entity = null;
    protected $message = null;

    /**
     * @param $entityTypeId
     * @param $typeId
     * @param Entity $entity
     * @param null $message
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     */
    public function addItem($entityTypeId, $typeId, Entity $entity, $message=null)
    {
        if(!is_int($entityTypeId))
        {
            $entityTypeId = (int)$entityTypeId;
        }
        if(!EntityType::IsDefined($entityTypeId))
        {
            throw new ArgumentOutOfRangeException('Is not defined', EntityType::FIRST, EntityType::LAST);
        }

        if(!is_int($typeId))
        {
            $typeId = (int)$typeId;
        }

        if(!EntityCollisionType::isDefined($typeId))
        {
            throw new ArgumentOutOfRangeException('Is not defined', EntityCollisionType::First, EntityCollisionType::Last);
        }

        $this->setEntity($entity);

        $this->entityTypeId = $entityTypeId;
        $this->typeId = $typeId;
        $this->message = $message;
    }

    /**
     * @param $entityTypeId
     * @return self
     * @throws ArgumentOutOfRangeException
     */
    public static function getCurrent($entityTypeId)
    {
        if(!EntityType::IsDefined($entityTypeId))
        {
            throw new ArgumentOutOfRangeException('Is not defined', EntityType::FIRST, EntityType::LAST);
        }

        $criterion =  new static();

        return $criterion;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return int
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        return EntityCollisionType::resolveName($this->typeId);
    }

    /**
     * @param Entity $entity
     */
    public function setEntity(Entity $entity)
    {
        $this->entity = $entity;
    }

	/**
	 * @param EntityImport $item
	 * @return Result
	 * @throws ArgumentException
	 */
	public function setCollision(EntityImport $item)
	{
		/** @var Order|Payment|Shipment $entity */
		$entity = $item->getEntity();
		if(!empty($entity))
		{
			$types = $this->getCollision($item);
			if(count($types)>0)
			{
				foreach ($types as $typeId)
				{
					$item->setCollisions($typeId, $entity);
				}
			}
		}
		return new Result();
	}

	/**
	 * @param EntityImport $item
	 * @return array
	 */
	public function getCollision(EntityImport $item)
	{
		return array();
	}

	/**
	 * Resolve import collisions
	 * @param EntityImport $item
	 * @return Result
	 */
	public function resolve(ImportBase $item)
	{
		return new Result();
	}
}
class CollisionOrder extends ImportCollision
{

	/**
	 * @param OrderImport $item
	 * @return Result
	 * @throws ArgumentException
	 */
	public function resolve(ImportBase $item)
    {
		if(!($item instanceof OrderImport))
			throw new ArgumentException("Item must be instanceof OrderImport");

		$this->setCollision($item);

		return new Result();
    }

	/**
	 * @param EntityImport $item
	 * @return array
	 */
	public function getCollision(EntityImport $item)
	{
		$result = array();

		/** @var ImportSettings $settings */
		$settings = $item->getSettings();

		/** @var Order $order */
		$order = $item->getEntity();
		if(!empty($order))
		{
			$collisionTypes = $settings->getCollisionResolve($item->getOwnerTypeId());

			if(is_array($collisionTypes))
			{
				foreach ($collisionTypes as $collisionType)
				{
					switch($collisionType)
					{
						case EntityCollisionType::OrderFinalStatusName:
							if($order->getField('STATUS_ID') == $settings->finalStatusIdFor($item->getOwnerTypeId()))
								$result[] = EntityCollisionType::resolveID($collisionType);
							break;
						case EntityCollisionType::OrderIsPayedName:
							if($order->isPaid())
								$result[] = EntityCollisionType::resolveID($collisionType);
							break;
						case EntityCollisionType::OrderIsShippedName:
							if($order->isShipped())
								$result[] = EntityCollisionType::resolveID($collisionType);
							break;
					}
				}
			}
		}

		return $result;
	}
}

class CollisionPayment extends ImportCollision
{
    /**
     * Resolve import collisions
     * @param PaymentImport $item
     * @return Result
     */
	public function resolve(ImportBase $item)
	{
		if(!($item instanceof PaymentImport))
			throw new ArgumentException("Item must be instanceof PaymentImport");

		$this->setCollision($item);

		return new Result();
	}

	/**
	 * @param EntityImport $item
	 * @return array
	 */
	public function getCollision(EntityImport $item)
	{
		$result = array();

		/** @var Payment $payment */
		$payment = $item->getEntity();
		if(!empty($payment))
		{
			if($payment->isPaid())
				$result[] = EntityCollisionType::PaymentIsPayed;
		}

		return $result;
	}
}

class CollisionShipment extends ImportCollision
{
    /**
     * Resolve import collisions
     * @param ShipmentImport $item
     * @return Result
     */
	public function resolve(ImportBase $item)
	{
		if(!($item instanceof ShipmentImport))
			throw new ArgumentException("Item must be instanceof ShipmentImport");

		$this->setCollision($item);

		return new Result();
	}

	/**
	 * @param EntityImport $item
	 * @return array
	 */
	public function getCollision(EntityImport $item)
	{
		$result = array();

		/** @var Shipment $shipment */
		$shipment = $item->getEntity();
		if(!empty($shipment))
		{
			if($shipment->isShipped())
				$result[] = EntityCollisionType::ShipmentIsShipped;
		}

		return $result;
	}
}

class CollisionProfile extends ImportCollision
{
    /**
     * Resolve import collisions
     * @param ProfileImport $item
     * @return Result
	 * @deprecated
     */
    public function resolve(ImportBase $item)
    {
		if(!($item instanceof ProfileImport))
			throw new ArgumentException("Item must be instanceof ProfileImport");

        return new Result();
    }
}