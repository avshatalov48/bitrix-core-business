<?php
namespace Bitrix\Sale\Exchange\Integration\Service\User\Entity;

use Bitrix\Sale\Exchange\Integration\Service\User\EntityType;

class Company extends Base
{
	//COMPANY
	//COMPANY_ADR
	//INN
	//KPP
	//CONTACT_PERSON
	//EMAIL
	//PHONE
	//FAX
	//ZIP
	//CITY
	//LOCATION
	//ADDRESS

	public function getCompany()
	{
		return $this->fields->get('COMPANY');
	}

	public function setCompany($value)
	{
		$this->fields->set('COMPANY', $value);
		return $this;
	}

	public function getCompanyAdr()
	{
		return $this->fields->get('COMPANY_ADR');
	}

	public function setCompanyAdr($value)
	{
		$this->fields->set('COMPANY_ADR', $value);
		return $this;
	}

	public function getInn()
	{
		return $this->fields->get('INN');
	}

	public function setInn($value)
	{
		$this->fields->set('INN', $value);
		return $this;
	}

	public function getKpp()
	{
		return $this->fields->get('KPP');
	}

	public function setKpp($value)
	{
		$this->fields->set('KPP', $value);
		return $this;
	}

	public function getContactPerson()
	{
		return $this->fields->get('CONTACT_PERSON');
	}

	public function setContactPerson($value)
	{
		$this->fields->set('CONTACT_PERSON', $value);
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

	public function getFax()
	{
		return $this->fields->get('FAX');
	}

	public function setFax($value)
	{
		$this->fields->set('FAX', $value);
		return $this;
	}

	public function getZip()
	{
		return $this->fields->get('ZIP');
	}

	public function setZip($value)
	{
		$this->fields->set('ZIP', $value);
		return $this;
	}

	public function getCity()
	{
		return $this->fields->get('CITY');
	}

	public function setCity($value)
	{
		$this->fields->set('CITY', $value);
		return $this;
	}

	public function getLocation()
	{
		return $this->fields->get('LOCATION');
	}

	public function setLocation($value)
	{
		$this->fields->set('LOCATION', $value);
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

	public function getType()
	{
		return EntityType::TYPE_E;
	}

	static protected function resolveFields(array $list)
	{
		$result = [];
		foreach($list as $item)
		{
			$result['ID'] = $item['SALE_INTERNALS_ORDER_USER_ID'];

			$result[$item['SALE_INTERNALS_ORDER_PROPERTY_CODE']] = $item['SALE_INTERNALS_ORDER_PROPERTY_VALUE'];
		}
		return $result;
	}

	static public function createFromArray(array $fields)
	{
		return new static([
			'ID' => $fields['ID'],
			'TITLE' => $fields['COMPANY'],
			'COMPANY_ADR' => $fields['COMPANY_ADR'],
			'EMAIL' => $fields['EMAIL'],
			'PHONE' => $fields['PHONE'],
			'ZIP' => $fields['ZIP'],
			'CITY' => $fields['CITY'],
			'ADDRESS' => $fields['ADDRESS'],
		]);
	}
}