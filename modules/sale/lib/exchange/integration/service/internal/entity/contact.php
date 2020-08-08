<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Internal\Entity;


use Bitrix\Sale\Exchange\Integration\CRM;

class Contact extends Base
{
	function getType()
	{
		return CRM\EntityType::CONTACT;
	}

	public function getName()
	{
		return $this->fields->get('NAME');
	}

	public function setName($value)
	{
		$this->fields->set('NAME', $value);
		return $this;
	}

	public function getLastName()
	{
		return $this->fields->get('LAST_NAME');
	}

	public function setLastName($value)
	{
		$this->fields->set('LAST_NAME', $value);
		return $this;
	}

	public function getEmail()
	{
		return $this->fields->get('EMAIL');
	}

	public function setEmail($value)
	{
		$this->fields->set('EMAIL', $value);
		return $this;
	}

	public function getPhone()
	{
		return $this->fields->get('PHONE');
	}

	public function setPhone($value)
	{
		$this->fields->set('PHONE', $value);
		return $this;
	}

	public function getAddressPostalCode()
	{
		return $this->fields->get('ADDRESS_POSTAL_CODE');
	}

	public function setAddressPostalCode($value)
	{
		$this->fields->set('ADDRESS_POSTAL_CODE', $value);
		return $this;
	}

	public function getAddressCity()
	{
		return $this->fields->get('ADDRESS_CITY');
	}

	public function setAddressCity($value)
	{
		$this->fields->set('ADDRESS_CITY', $value);
		return $this;
	}

	public function getAddress()
	{
		return $this->fields->get('ADDRESS');
	}

	public function setAddress($value)
	{
		$this->fields->set('ADDRESS', $value);
		return $this;
	}
}