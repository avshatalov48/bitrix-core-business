<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;

use Bitrix\Main\Web\Json;

/**
 * @package    bitrix
 * @subpackage main
 */
class ArrayField extends ScalarField
{
	/** @var string  json, serialize, custom */
	protected $serializationType;

	/** @var callable */
	protected $encodeFunction;

	/** @var callable */
	protected $decodeFunction;

	public function __construct($name, $parameters = [])
	{
		$this->configureSerializationJson();

		$this->addSaveDataModifier([$this, 'encode']);
		$this->addFetchDataModifier([$this, 'decode']);

		parent::__construct($name, $parameters);
	}

	/**
	 * Sets json serialization format
	 *
	 * @return $this
	 */
	public function configureSerializationJson()
	{
		$this->serializationType = 'json';
		$this->encodeFunction = [$this, 'encodeJson'];
		$this->decodeFunction = [$this, 'decodeJson'];

		return $this;
	}

	/**
	 * Sets php serialization format
	 *
	 * @return $this
	 */
	public function configureSerializationPhp()
	{
		$this->serializationType = 'php';
		$this->encodeFunction = [$this, 'encodePhp'];
		$this->decodeFunction = [$this, 'decodePhp'];

		return $this;
	}

	/**
	 * Custom encode handler
	 *
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function configureSerializeCallback($callback)
	{
		$this->encodeFunction = $callback;
		$this->serializationType = 'custom';

		return $this;
	}

	/**
	 * Custom decode handler
	 *
	 * @param $callback
	 *
	 * @return $this
	 */
	public function configureUnserializeCallback($callback)
	{
		$this->decodeFunction = $callback;
		$this->serializationType = 'custom';

		return $this;
	}

	/**
	 * @param array $value
	 *
	 * @return string
	 */
	public function encode($value)
	{
		$callback = $this->encodeFunction;
		return $callback($value);
	}

	/**
	 * @param string $value
	 *
	 * @return array
	 */
	public function decode($value)
	{
		$callback = $this->decodeFunction;
		return $callback($value);
	}

	/**
	 * @param $value
	 *
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function encodeJson($value)
	{
		return Json::encode($value);
	}

	/**
	 * @param $value
	 *
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function decodeJson($value)
	{
		return Json::decode($value);
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function encodePhp($value)
	{
		return serialize($value);
	}

	/**
	 * @param $value
	 *
	 * @return array
	 */
	public function decodePhp($value)
	{
		return unserialize($value);
	}

	/**
	 * @param mixed $value
	 *
	 * @return array
	 */
	public function cast($value)
	{
		return (array) $value;
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed|string
	 * @throws \Bitrix\Main\SystemException
	 */
	public function convertValueFromDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertFromDbString($value);
	}

	/**
	 * @param mixed $value
	 *
	 * @return string
	 * @throws \Bitrix\Main\SystemException
	 */
	public function convertValueToDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertToDbString($value);
	}
}
