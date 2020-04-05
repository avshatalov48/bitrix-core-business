<?php

namespace Bitrix\Sale\Exchange\Internals;


use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\Fields;

class Logger
{
	/** @var  Fields */
	protected $fields;

	const INTERVAL_DAY_OPTION = "SALE_EXCHANGE_DEBUG_INTERVAL_DAY";

	public function __construct()
	{
		$this->fields = new Fields();
	}

	/**
	 * @return static
	 */
	public static function getCurrent()
	{
		return new static();
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
	 * @return int
	 */
	static public function getInterval()
	{
		$interval = Option::get('sale', static::INTERVAL_DAY_OPTION, 1);
		return intval($interval)>0 ? $interval:1;
	}

	/**
	 * @return \Bitrix\Main\Entity\AddResult|null
	 */
	public function save()
	{
		$params['ENTITY_ID'] = $this->getField('ENTITY_ID');
		$params['ENTITY_TYPE_ID'] = $this->getField('ENTITY_TYPE_ID');
		$params['PARENT_ID'] = $this->getField('PARENT_ID');
		$params['OWNER_ENTITY_ID'] = $this->getField('OWNER_ENTITY_ID');
		$params['ENTITY_DATE_UPDATE'] = $this->getField('ENTITY_DATE_UPDATE');
		$params['XML_ID'] = $this->getField('XML_ID');
		$params['DESCRIPTION'] = $this->getField('DESCRIPTION');
		$params['MESSAGE'] = $this->getField('MESSAGE');
		$params['DIRECTION'] = $this->getField('DIRECTION');
		$params['MARKED'] = $this->getField('MARKED');
		$params['DATE_INSERT'] = new DateTime();

		return static::log($params);
	}

	/**
	 * @param array $params
	 * @return \Bitrix\Main\Entity\AddResult|null
	 */
	static public function log(array $params)
	{
		$result = ExchangeLogTable::add($params);
		return $result;
	}
}