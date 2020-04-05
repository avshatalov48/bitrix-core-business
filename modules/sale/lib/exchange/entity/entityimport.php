<?php
namespace Bitrix\Sale\Exchange\Entity;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Exchange;
use Bitrix\Sale\EntityMarker;

/**
 * Class EntityImport
 * @package Bitrix\Sale\Exchange\Entity
 * @internal
 */
abstract class EntityImport extends Exchange\ImportBase
{
    public $collisions = array();

    protected $parentEntity = null;
    /** @var Sale\Internals\Entity $entity*/
    protected $entity = null;
    protected $external = null;
    protected $marked = false;

    public function __construct($parentEntityContext = null)
    {
        $this->fields = new Sale\Internals\Fields();

        if(!empty($parentEntityContext))
        {
            $this->setParentEntity($parentEntityContext);
        }
    }

    /**
     * @return int
     */
    public function getOwnerTypeId()
    {
        return Exchange\EntityType::UNDEFINED;
    }

    /**
     * @internal
     * @param Order $parentEntity
     */
    public function setParentEntity(Order $parentEntity)
    {
        $this->parentEntity = $parentEntity;
    }

    /**
     * @return null|Order
     */
    public function getParentEntity()
    {
        return $this->parentEntity;
    }

    /**
     * @return bool
     */
    public function isLoadedParentEntity()
    {
        $order = $this->getParentEntity();
        return $order instanceof Order;
    }

    /**
     * @param Sale\Internals\Entity $entity
     * @throws Main\NotImplementedException
     */
    abstract public function setEntity(Sale\Internals\Entity $entity);

    /**
     * @return Sale\Internals\Entity $entity|null
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return string
     */
    protected function isExternal()
    {
        return $this->external;
    }

    /**
     * @param bool|true $external
     */
    public function setExternal($external = true)
    {
        $this->external = $external;
    }

    /**
     * @param $tipeId
     * @param Sale\Internals\Entity $entity
     * @param null $message
     * @internal
     */
    public function setCollisions($tipeId, Sale\Internals\Entity $entity, $message=null)
    {
    	if(Exchange\EntityCollisionType::getErrorGroup($tipeId) == Exchange\EntityCollisionType::GROUP_E_ERROR)
		{
			$this->collisionErrors = true;
		}
		elseif(Exchange\EntityCollisionType::getErrorGroup($tipeId) == Exchange\EntityCollisionType::GROUP_E_WARNING)
		{
			$this->collisionWarnings = true;
		}

    	$collision = $this->getCurrentCollision($this->getOwnerTypeId());
        $collision->addItem($this->getOwnerTypeId(), $tipeId, $entity, $message);

        $this->collisions[] = $collision;
    }

    /**
     * @return Exchange\ICollisionOrder[]|Exchange\ICollisionShipment[]|Exchange\ICollisionPayment[]|Exchange\ICollisionProfile[]
     */
    public function getCollisions()
    {
        return $this->collisions;
    }

    /**
     * @return int
     */
    public function hasCollisions()
    {
        return (count($this->collisions));
    }

    /**
     * @param $collisions
     */
    public function markedEntityCollisions($collisions)
    {
        /** @var Shipment|Payment $entity */
        $entity = $this->getEntity();

        /** @var Order $parentEntity */
        $parentEntity = $this->getParentEntity();

        /** @var Exchange\ICollision $collision*/
        foreach($collisions as $collision)
        {
            $result = new Sale\Result();
            $result->addWarning(new Sale\ResultError(Exchange\EntityCollisionType::getDescription($collision->getTypeId()).($collision->getMessage() != null ? " ".$collision->getMessage():'' ), $collision->getTypeName()));

            $entity->setField('MARKED', 'Y');
            $this->marked = true;

            $collisionEntity = $collision->getEntity();
            if(!empty($collisionEntity))
            {
                EntityMarker::addMarker($parentEntity, $collisionEntity, $result);
            }
            else
            {
                EntityMarker::addMarker($parentEntity, $entity, $result);
            }
        }
    }

	/**
	 * @return bool
	 */
	public function isMarked()
	{
		return $this->marked;
	}

    /**
     * @return null|string
     */
    public function getId()
    {
        return $this->getEntityId();
    }

    /**
     * @return bool
     * @throws Main\ArgumentTypeException
     * @throws Main\NotSupportedException
     */
	public function isImportable()
    {
        return $this->settings->isImportableFor($this->getOwnerTypeId());
    }

    /**
     * @param array $params
     * @return Sale\Internals\Entity|Sale\Result
     * @internal param $fields
     */
    public function import(array $params)
    {
        $result = parent::import($params);
		if($result->isSuccess())
		{
			/** @var Sale\Internals\Entity $entity*/
			if(($entity = $this->getEntity()))
				$this->marked($entity, $params['TRAITS']);
		}
        return $result;
    }

    /**
	 * @return Main\Entity\AddResult|Main\Entity\UpdateResult|Sale\Result|mixed
     */
    abstract public function save();

    /**
     * @param Sale\Internals\Entity $entity
     * @param array $fields
     * @throws Main\ArgumentOutOfRangeException
     * @throws \Exception
     */
    function marked(Sale\Internals\Entity $entity, array $fields)
    {
        if($this->isExternal())
            $entity->setField($this->getExternalFieldName(), 'Y');
        else
            $entity->setField('UPDATED_1C', 'Y');

        if(!$this->hasCollisions())
        {
            $entity->setField('VERSION_1C', $fields['VERSION_1C']);
        }

        $entity->setField('ID_1C', $fields['ID_1C']);

        if(!($entity instanceof Order))
        {
            /** @var Order $parentEntity */
            $parentEntity = $this->getParentEntity();

            $parentEntity->setField('UPDATED_1C','Y');
        }
    }

    /**
     * @return null|string
     */
    public function getEntityId()
    {
        $entity = $this->getEntity();
        if(!empty($entity))
        {
            /** @var Sale\Internals\Entity $entity*/
            return $entity->getId();
        }

        return null;
    }

    /**
     * @param $id
     * @return bool
     */
    protected function checkEntity($id)
    {
        return is_int($id) && $id > 0;
    }

    /**
     * @return string
     */
    abstract protected function getExternalFieldName();

    /**
     * @param Sale\Internals\Entity $entity
     * @return int
     */
    public static function resolveEntityTypeId(Sale\Internals\Entity $entity)
    {
        return Exchange\EntityType::UNDEFINED;
    }

    /**
     * @return string
     */
    public static function getFieldExternalId()
    {
        return 'ID_1C';
    }
}