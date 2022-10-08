<?php

namespace Bitrix\Seo\BusinessSuite\Configuration\Facebook;

use Bitrix\Main\Config;
use Bitrix\Main\Web\Json;
use Bitrix\Seo\BusinessSuite;
use Bitrix\Seo\BusinessSuite\Configuration\IConfig;
use Bitrix\Seo\BusinessSuite\Configuration\Facebook\Fields\Setup as SetupFields;

final class Setup implements IConfig
{
	// public constants
	const BUSINESS_ID = 'external_business_id';
	const TIMEZONE = 'timezone';
	const CURRENCY = 'currency';
	const CHANNEL = 'channel';
	const BUSINESS_TYPE = 'business_vertical';

	/**@var self $current current setup*/
	private static $current;

	/**@var array $value*/
	private $value = [];

	// non public constants
	private const MODULE_ID = 'seo';
	private const CONFIG_OPTION = '~facebook_business_setup';

	private const FIELDS_MAP = [
		self::CHANNEL => SetupFields\Channel::class,
		self::CURRENCY => SetupFields\Currency::class,
		self::TIMEZONE => SetupFields\Timezone::class,
		self::BUSINESS_ID  => SetupFields\BusinessId::class,
		self::BUSINESS_TYPE => SetupFields\BusinessType::class,
	];

	/** @var Fields\IField[] $fieldInstances */
	private static $fieldInstances = [];

	private static function getField(string $name) : Fields\IField
	{
		if($field = self::FIELDS_MAP[$name])
		{
			return static::$fieldInstances[$name] = static::$fieldInstances[$name] ?? new $field;
		}
		throw new BusinessSuite\Exception\UnknownFieldException("$name");
	}

	/**
	 * @return IConfig
	 */
	public static function create(): IConfig
	{
		return new self();
	}

	/**
	 * Setup constructor.
	 */
	public function __construct()
	{
		$this->value[self::BUSINESS_ID] = SetupFields\BusinessId::getDefaultValue();
	}

	/**
	 * TO facebook array
	 * @return array
	 */
	public function toArray(): array
	{
		return array_reduce(
			array_keys(self::FIELDS_MAP),
			(function (array $result,string $code) : array
			{
				$field = $this::getField($code);
				if($field::available())
				{
					if($value = $this->get($code))
					{
						$result[$code] = $value;
					}
					elseif($field::required())
					{
						throw new BusinessSuite\Exception\RequiredFieldNotFoundException("$code");
					}
				}
				return $result;

			})->bindTo($this,$this),
			array()
		);
	}

	public function set(string $name, $value): IConfig
	{
		if(self::getField($name)::available())
		{
			if(self::getField($name)::checkValue($value))
			{
				$this->value[$name] = $value;
				return $this;
			}
			throw new BusinessSuite\Exception\InvalidFieldValue("$name");
		}
		return $this;
	}

	public function get(string $name)
	{
		return $this->value[$name] ?? null;
	}

	public function delete() : void
	{
		Config\Option::delete(self::MODULE_ID,['name' => self::CONFIG_OPTION]);
	}

	public function save() : bool
	{
		try
		{
			Config\Option::set(self::MODULE_ID,self::CONFIG_OPTION,Json::encode($this->value));
		}
		catch (\Throwable $exception)
		{
			return false;
		}
		return true;
	}

	public function jsonSerialize() : array
	{
		return array_reduce(
			array_keys(self::FIELDS_MAP),
			function(array $result, string $code) : array
			{
				$field = static::getField($code);
				if($field::available())
				{
					$result[$code] = ['value' => $this->value[$code] ?? $field::getDefaultValue()];
					if($field instanceof Fields\IAvailableFieldList)
					{
						$result[$code]['set'] = $field::getAvailableValues();
					}
				}
				return $result;
			},
			array()
		);
	}

	/**
	 * @return IConfig|static|null
	 */
	public static function load(): ?IConfig
	{
		if(!self::$current)
		{
			if($data = Config\Option::get(self::MODULE_ID,self::CONFIG_OPTION,false))
			{
				[$data,self::$current] = [Json::decode($data),self::default()];
				foreach ($data as $key => $value)
				{
					self::$current->set($key,$value ?? self::getField($key)::getDefaultValue());
				}
			}
		}

		return self::$current;
	}

	/**
	 * build default setup instance
	 * @return IConfig
	 */
	public static function default(): IConfig
	{
		return array_reduce(
			array_keys(self::FIELDS_MAP),
			function(IConfig $instance,$code)
			{
				return $instance->set($code,self::getField($code)::getDefaultValue());
			},
			self::create()
		);
	}

	/**
	 * build setup instance from array
	 * @param array $array
	 *
	 * @return IConfig
	 */
	public static function loadFromArray(array $array) : IConfig
	{
		return array_reduce(
			array_keys(self::FIELDS_MAP),
			function(IConfig $instance,string $code) use ($array)
			{
				return ((array_key_exists($code,$array)) ? $instance->set($code,$array[$code]) : $instance);
			},
			self::create()
		);
	}
}