<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Statistic\Entity;

use Bitrix\Sale\Exchange\Integration\Service\Container\Entity;
use Bitrix\Sale\Exchange\Integration\EntityType;

class Order extends Entity
{
	public function getEntityTypeId()
	{
		return $this->fields->get('entityTypeId');
	}

	public function setEntityTypeId($value)
	{
		$this->fields->set('entityTypeId', $value);
		return $this;
	}

	public function getEntityId()
	{
		return $this->fields->get('entityId');
	}

	public function setEntityId($value)
	{
		$this->fields->set('entityId', $value);
		return $this;
	}

	public function getDateUpdate()
	{
		return $this->fields->get('dateUpdate');
	}

	public function setDateUpdate($value)
	{
		$this->fields->set('dateUpdate', $value);
		return $this;
	}

	public function getProviderId()
	{
		return $this->fields->get('providerId');
	}

	public function setProviderId($value)
	{
		$this->fields->set('providerId', $value);
		return $this;
	}

	public function getCurrency()
	{
		return $this->fields->get('currency');
	}

	public function setCurrency($value)
	{
		$this->fields->set('currency', $value);
		return $this;
	}

	public function getStatus()
	{
		return $this->fields->get('status');
	}

	public function setStatus($value)
	{
		$this->fields->set('status', $value);
		return $this;
	}

	public function getXmlId()
	{
		return $this->fields->get('xmlId');
	}

	public function setXmlId($value)
	{
		$this->fields->set('xmlId', $value);
		return $this;
	}

	public function getAmount()
	{
		return $this->fields->get('amount');
	}

	public function setAmount($value)
	{
		$this->fields->set('amount', $value);
		return $this;
	}

	static public function createFromArray(array $fields)
	{
		return new static([
			'entityTypeId' => EntityType::ORDER,
			'entityId' => $fields['ENTITY_ID'],
			'dateUpdate' => $fields['DATE_UPDATE']->format('c'),
			'providerId' => $fields['PROVIDER_ID'],
			'currency' => $fields['CURRENCY'],
			'status' => $fields['STATUS'],
			'xmlId' => $fields['XML_ID'],
			'amount' => $fields['AMOUNT'],
		]);
	}

	public function getType()
	{
		return EntityType::ORDER;
	}
}
