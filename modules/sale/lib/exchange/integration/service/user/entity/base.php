<?php
namespace Bitrix\Sale\Exchange\Integration\Service\User\Entity;

use Bitrix\Sale\Exchange\Integration\Exception;
use Bitrix\Sale\Internals\BusinessValuePersonDomainTable;
use Bitrix\Sale\Internals\Fields;
use Bitrix\Sale\Order;
use Bitrix\Sale\Registry;

abstract class Base
{
	protected $fields;

	public function __construct(array $values = null)
	{
		$this->fields = new Fields($values);
	}

	public function getId()
	{
		return $this->fields->get('ID');
	}
	public function setId($value)
	{
		$this->fields->set('ID', $value);
		return $this;
	}

	public function getFieldsValues()
	{
		return $this->fields->getValues();
	}

	abstract public function getType();
	abstract static protected function resolveFields(array $list);
	abstract static public function createFromArray(array $fields);

	public function load(Order $order)
	{
		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		/** @var Order $orderClass */
		$orderClass = $registry->getOrderClassName();
		$list = $orderClass::getList([
			'select'=>[
				'ID',
				'PROPERTY.CODE',
				'PROPERTY.VALUE',
				'USER.ID',
				'USER.NAME',
				'USER.LAST_NAME',
				'USER.EMAIL',
				'USER.PERSONAL_PHONE',
				'PERSON_TYPE_ID'
			],
			'filter'=>['ID'=>$order->getId()]
		])->fetchAll();

		if(count($list)>0)
		{
			$fields = static::resolveFields($list);
			return static::createFromArray($fields);
		}
		else
		{
			throw new Exception\UserException('Client not loaded');
		}
	}

	static public function resolveNamePersonDomain($personTypeId)
	{
		static $list = null;

		if ($list == null)
		{
			$list = static::businessValuePersonDomainList();
		}

		if (isset($list[$personTypeId]))
		{
			return $list[$personTypeId];
		}
		else
		{
			throw new Exception\UserException("Person personTypeId: '".$personTypeId."' is not supported in current context");
		}
	}
	static protected function businessValuePersonDomainList()
	{
		$bzList = [];
		foreach (BusinessValuePersonDomainTable::getList()->fetchAll() as $bz)
		{
			$bzList[$bz['PERSON_TYPE_ID']] = $bz['DOMAIN'];
		}

		return $bzList;
	}
}