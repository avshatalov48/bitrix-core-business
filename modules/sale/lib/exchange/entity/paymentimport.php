<?php
namespace Bitrix\Sale\Exchange\Entity;

use Bitrix\Sale;
use Bitrix\Sale\Internals;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Exchange;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Main;
use Bitrix\Main\Error;

/**
 * Class PaymentImport
 * @package Bitrix\Sale\Exchange\Entity
 * @internal
 */
class PaymentImport extends EntityImport
{
    /**
     * @param Internals\Entity $entity
     * @throws Main\ArgumentException
     */
    public function setEntity(Internals\Entity $entity)
    {
        if(!($entity instanceof Payment))
            throw new Main\ArgumentException("Entity must be instanceof Payment");

        $this->entity = $entity;
    }

    /**
     * @param array $fields
     * @return Sale\Result
     */
    protected function checkFields(array $fields)
    {
        $result = new Sale\Result();

        if(intval($fields['ORDER_ID'])<=0 && !$this->isLoadedParentEntity())
        {
            $result->addError(new Error('ORDER_ID is not defined',''));
        }

        return $result;
    }

	/**
	 * @return Main\Entity\AddResult|Main\Entity\UpdateResult|Sale\Result|mixed
	 */
	public function save()
    {
        /** @var Order $parentEntity */
        $parentEntity = $this->getParentEntity();
        return $parentEntity->save();
    }

	/**
	 * @param array $params
	 * @return Sale\Result
	 */
	public function add(array $params)
    {
    	$result = new Sale\Result();

        if(!$this->isLoadedParentEntity())
        {
			$result->addError(new Error(GetMessage('SALE_EXCHANGE_ENTITY_PAYMENT_ORDER_IS_NOT_LOADED_ERROR'),'ENTITY_PAYMENT_ORDER_IS_NOT_LOADED_ERROR'));
			return $result;
        }

		$fields = $params['TRAITS'];

        if(($paySystem = Sale\PaySystem\Manager::getObjectById($fields['PAY_SYSTEM_ID'])) == null)
		{
			$result->addError(new Error(GetMessage('SALE_EXCHANGE_ENTITY_PAYMENT_PAYMENT_SYSTEM_IS_NOT_AVAILABLE_ERROR'),'PAYMENT_SYSTEM_IS_NOT_AVAILABLE_ERROR'));
		}
		else
		{
			$parentEntity = $this->getParentEntity();
			$paymentCollection = $parentEntity->getPaymentCollection();
			$payment = $paymentCollection->createItem($paySystem);
			$result = $payment->setFields($fields);

			if($result->isSuccess())
			{
				$this->setEntity($payment);
			}
		}

        return $result;
    }

    /**
     * @param array $params
     * @return Sale\Result
     */
    public function update(array $params)
    {
    	/** @var Sale\Payment $payment*/
        $payment = $this->getEntity();

        $criterion = $this->getCurrentCriterion($this->getEntity());

        $fields = $params['TRAITS'];
        if(!$criterion->equals($fields))
        {
            unset(
                $fields['SUM'],
                $fields['COMMENTS'],
                $fields['PAY_VOUCHER_DATE'],
                $fields['PAY_VOUCHER_NUM']
            );
        }
        $result = $payment->setFields($fields);

        return $result;
    }

    /**
     * @param array|null $params
     * @return Sale\Result
     */
    public function delete(array $params = null)
    {
        /** @var Payment $entity */
        $entity = $this->getEntity();
        $result = $entity->delete();
        if($result->isSuccess())
        {
            //$this->setCollisions(Exchange\EntityCollisionType::OrderPaymentDeleted, $this->getParentEntity());
        }
        else
        {
            $this->setCollisions(Exchange\EntityCollisionType::OrderPaymentDeletedError, $this->getParentEntity(), implode(',', $result->getErrorMessages()));
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getExternalFieldName()
    {
        return 'EXTERNAL_PAYMENT';
    }

	/**
	 * @param array $fields
	 * @return Sale\Result
	 * @throws Main\ArgumentException
	 */
	public function load(array $fields)
    {
        $r = $this->checkFields($fields);
        if(!$r->isSuccess())
        {
            throw new Main\ArgumentException('ORDER_ID is not defined');
        }

		if(!$this->isLoadedParentEntity() && !empty($fields['ORDER_ID']))
		{
			$this->setParentEntity(
				$this->loadParentEntity(['ID'=>$fields['ORDER_ID']])
			);
		}

        if($this->isLoadedParentEntity())
        {
            /** @var Order $parentEntity */
            $parentEntity = $this->getParentEntity();

            if(!empty($fields['ID']))
            {
                $payment = $parentEntity->getPaymentCollection()->getItemById($fields['ID']);
            }

            /** @var Payment $payment*/
            if(!empty($payment))
            {
                $this->setEntity($payment);
            }
            else
            {
                $this->setExternal();
            }
        }
		return new Sale\Result();
    }

    /**
     * @param array $fields
     */
    public function refreshData(array $fields)
    {
        /** @var Sale\Payment $entity */
        $entity = $this->getEntity();
        if(!empty($entity) && $entity->isPaid())
        {
            if($fields['PAID'] == 'N')
                $entity->setField('PAID', 'N');
        }
    }

    /**
     * @param Internals\Entity $payment
     * @return int
     * @throws Main\ArgumentException
     */
	static public function resolveEntityTypeId(Internals\Entity $payment)
    {
        if(!($payment instanceof Payment))
            throw new Main\ArgumentException("Entity must be instanceof Payment");

        $paySystem = $payment->getPaySystem();
        $type = $paySystem->getField('IS_CASH');

        return static::resolveEntityTypeIdByCodeType($type);
    }

	/**
	 * @param string $type
	 * @return int
	 */
	static public function resolveEntityTypeIdByCodeType($type)
	{
		switch($type)
		{
			case 'Y':
				$resolveType = EntityType::PAYMENT_CASH;
				break;
			case 'N':
				$resolveType = EntityType::PAYMENT_CASH_LESS;
				break;
			case 'A':
				$resolveType = EntityType::PAYMENT_CARD_TRANSACTION;
				break;
			default;
				$resolveType = EntityType::UNDEFINED;
		}
		return $resolveType;
	}

	public function initFields()
	{
		$this->setFields(
			array(
				'TRAITS'=>$this->getFieldsTraits(),
			)
		);
	}

	/**
	 * @param Sale\IBusinessValueProvider $entity
	 * @return Sale\Order
	 */
	static protected function getBusinessValueOrderProvider(\Bitrix\Sale\IBusinessValueProvider $entity)
	{
		if(!($entity instanceof Payment))
			throw new Main\ArgumentException("entity must be instanceof Payment");

		/** @var Sale\PaymentCollection $collection */
		$collection = $entity->getCollection();

		return $collection->getOrder();
	}
}

class PaymentCashImport extends PaymentImport
{
    public function __construct($parentEntityContext = null)
    {
        parent::__construct($parentEntityContext);
    }

    public function getOwnerTypeId()
    {
        return EntityType::PAYMENT_CASH;
    }
}

class PaymentCashLessImport extends PaymentImport
{
    public function __construct($parentEntityContext = null)
    {
        parent::__construct($parentEntityContext);
    }

    public function getOwnerTypeId()
    {
        return EntityType::PAYMENT_CASH_LESS;
    }
}

class PaymentCardImport extends PaymentImport
{
    public function __construct($parentEntityContext = null)
    {
        parent::__construct($parentEntityContext);
    }

    public function getOwnerTypeId()
    {
        return EntityType::PAYMENT_CARD_TRANSACTION;
    }
}