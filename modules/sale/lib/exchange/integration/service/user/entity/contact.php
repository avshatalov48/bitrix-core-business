<?php
namespace Bitrix\Sale\Exchange\Integration\Service\User\Entity;

use Bitrix\Sale\Exchange\Integration\Service\User\EntityType;

class Contact extends Base
{
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

	public function getZip()
	{
		return $this->fields->get('ZIP');
	}

	public function setZip($value)
	{
		$this->fields->set('ZIP', $value);
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

	public function getCity()
	{
		return $this->fields->get('CITY');
	}

	public function setCity($value)
	{
		$this->fields->set('CITY', $value);
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

	static protected function resolveFields(array $list)
	{
		$result = [];
		foreach($list as $item)
		{
			$result['ID'] = $item['SALE_INTERNALS_ORDER_USER_ID'];

			if($item['SALE_INTERNALS_ORDER_PROPERTY_CODE'] == 'FIO')
			{
				$name = explode(' ', $item['SALE_INTERNALS_ORDER_PROPERTY_VALUE'])[0];
				$last = explode(' ', $item['SALE_INTERNALS_ORDER_PROPERTY_VALUE'])[1];

				$name = $name<>'' ? $name:$item['SALE_INTERNALS_ORDER_USER_NAME'];
				$last = $last<>'' ? $last:$item['SALE_INTERNALS_ORDER_USER_LAST_NAME'];

				$name = $name<>'' ? $name:'User №'.$item['SALE_INTERNALS_ORDER_USER_ID'];
				$last = $last<>'' ? $last:'User №'.$item['SALE_INTERNALS_ORDER_USER_ID'];

				$result['NAME'] = $name;
				$result['LAST_NAME'] = $last;
			}
			else
			{
				$result[$item['SALE_INTERNALS_ORDER_PROPERTY_CODE']] = $item['SALE_INTERNALS_ORDER_PROPERTY_VALUE'];
			}
		}
		return $result;
	}

	public function getType()
	{
		return EntityType::TYPE_I;
	}

	static public function createFromArray(array $fields)
	{
		return new static([
			'ID' => $fields['ID'],
			'NAME' => $fields['NAME'],
			'LAST_NAME' => $fields['LAST_NAME'],
			'EMAIL' => $fields['EMAIL'],
			'PHONE' => $fields['PHONE'],
			'ZIP' => $fields['ZIP'],
			'ADDRESS' => $fields['ADDRESS'],
		]);
	}
}