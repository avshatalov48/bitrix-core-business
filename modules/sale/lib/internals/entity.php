<?php
namespace Bitrix\Sale\Internals;

use Bitrix\Main;
use Bitrix\Sale\Result;
use Bitrix\Sale\ResultError;

Main\Localization\Loc::loadMessages(__FILE__);

abstract class Entity
{
	/** @var Fields */
	protected $fields;

	protected $eventName = null;

	protected function __construct(array $fields = array())
	{
		foreach ($fields as $name => $value)
		{
			$fields[$name] = $this->normalizeValue($name, $value);
		}

		$this->fields = new Fields($fields);
	}

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getRegistryType()
	{
		throw new Main\NotImplementedException('The method '.__METHOD__.' is not overridden in '.static::class);
	}

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getRegistryEntity()
	{
		throw new Main\NotImplementedException('The method '.__METHOD__.' is not overridden in '.static::class);
	}

	/**
	 * @return array
	 *
	 * @throws Main\NotImplementedException
	 */
	public static function getAvailableFields()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @return array
	 */
	public static function getCustomizableFields() : array
	{
		return [];
	}

	/**
	 * @return array|null
	 * @throws Main\NotImplementedException
	 */
	public static function getAvailableFieldsMap()
	{
		static $fieldsMap = [];

		if (!isset($fieldsMap[static::class]))
		{
			$fieldsMap[static::class] = array_fill_keys(static::getAvailableFields(), true);
		}

		return $fieldsMap[static::class];
	}

	/**
	 * @return array
	 *
	 * @throws Main\NotImplementedException
	 */
	public static function getAllFields()
	{
		static $mapFields = [];

		if (!isset($mapFields[static::class]))
		{
			$mapFields[static::class] = [];

			$fields = static::getFieldsDescription();
			foreach ($fields as $field)
			{
				$mapFields[static::class][$field['CODE']] = $field['CODE'];
			}
		}

		return $mapFields[static::class];
	}

	/**
	 * @return array
	 * @throws Main\NotImplementedException
	 */
	public static function getFieldsDescription()
	{
		$result = [];

		$map = static::getFieldsMap();
		foreach ($map as $key => $value)
		{
			if (is_array($value) && !isset($value['expression']))
			{
				$result[$key] = [
					'CODE' => $key,
					'TYPE' => $value['data_type']
				];
			}
			elseif ($value instanceof Main\Entity\ScalarField)
			{
				$result[$value->getName()] = [
					'CODE' => $value->getName(),
					'TYPE' => $value->getDataType(),
				];
			}
		}

		return $result;
	}

	/**
	 * @throws Main\NotImplementedException
	 * @return array
	 */
	protected static function getFieldsMap()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @return array
	 *
	 * @throws Main\NotImplementedException
	 */
	protected static function getMeaningfulFields()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param $name
	 * @return string|null
	 */
	public function getField($name)
	{
		return $this->fields->get($name);
	}

	protected function normalizeValue($name, $value)
	{
		return $value;
	}

	/**
	 * @param $name
	 * @param $value
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws \Exception
	 */
	public function setField($name, $value)
	{
		$result = new Result();

		$value = $this->normalizeValue($name, $value);

		if ($this->eventName === null)
		{
			$this->eventName = static::getEntityEventName();
		}

		if ($this->eventName)
		{
			$eventManager = Main\EventManager::getInstance();
			if ($eventsList = $eventManager->findEventHandlers('sale', 'OnBefore'.$this->eventName.'SetField'))
			{
				/** @var Main\Entity\Event $event */
				$event = new Main\Event('sale', 'OnBefore'.$this->eventName.'SetField', array(
					'ENTITY' => $this,
					'NAME' => $name,
					'VALUE' => $value,
				));
				$event->send();

				if ($event->getResults())
				{
					/** @var Main\EventResult $eventResult */
					foreach($event->getResults() as $eventResult)
					{
						if($eventResult->getType() == Main\EventResult::SUCCESS)
						{
							if ($eventResultData = $eventResult->getParameters())
							{
								if (isset($eventResultData['VALUE']) && $eventResultData['VALUE'] != $value)
								{
									$value = $eventResultData['VALUE'];
								}
							}
						}
						elseif($eventResult->getType() == Main\EventResult::ERROR)
						{

							$errorMsg = new ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_ON_BEFORE_'.mb_strtoupper($this->eventName).'_SET_FIELD_ERROR'), 'SALE_EVENT_ON_BEFORE_'.mb_strtoupper($this->eventName).'_SET_FIELD_ERROR');

							if ($eventResultData = $eventResult->getParameters())
							{
								if (isset($eventResultData) && $eventResultData instanceof ResultError)
								{
									/** @var ResultError $errorMsg */
									$errorMsg = $eventResultData;
								}
							}

							$result->addError($errorMsg);

						}
					}

					if (!$result->isSuccess())
					{
						return $result;
					}
				}
			}
		}

		$availableFields = static::getAvailableFieldsMap();
		if (!isset($availableFields[$name]))
		{
			throw new Main\ArgumentOutOfRangeException("name=$name");
		}

		$oldValue = $this->fields->get($name);
		if ($oldValue != $value || ($oldValue === null && $value !== null))
		{
			$r = $this->checkValueBeforeSet($name, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			if ($this->eventName)
			{
				if ($eventsList = $eventManager->findEventHandlers('sale', 'On'.$this->eventName.'SetField'))
				{
					$event = new Main\Event('sale', 'On'.$this->eventName.'SetField', array(
						'ENTITY' => $this,
						'NAME' => $name,
						'VALUE' => $value,
						'OLD_VALUE' => $oldValue,
					));
					$event->send();

					if ($event->getResults())
					{
						/** @var Main\EventResult $evenResult */
						foreach($event->getResults() as $eventResult)
						{
							if($eventResult->getType() == Main\EventResult::SUCCESS)
							{
								if ($eventResultData = $eventResult->getParameters())
								{
									if (isset($eventResultData['VALUE']) && $eventResultData['VALUE'] != $value)
									{
										$value = $eventResultData['VALUE'];
									}
								}
							}
						}
					}
				}
			}

			$isStartField = $this->isStartField(in_array($name, static::getMeaningfulFields()));

			$this->fields->set($name, $value);
			try
			{
				$result = $this->onFieldModify($name, $oldValue, $value);

				if ($result->isSuccess() && $this->eventName)
				{
					$event = new Main\Event('sale', 'OnAfter'.$this->eventName.'SetField', array(
						'ENTITY' => $this,
						'NAME' => $name,
						'VALUE' => $value,
						'OLD_VALUE' => $oldValue,
					));
					$event->send();
				}

				if ($result->isSuccess())
				{
					static::addChangesToHistory($name, $oldValue, $value);
				}
			}
			catch (\Exception $e)
			{
				$this->fields->set($name, $oldValue);
				throw $e;
			}

			if (!$result->isSuccess())
			{
				$this->fields->set($name, $oldValue);
			}
			else
			{
				if ($isStartField)
				{
					$hasMeaningfulFields = $this->hasMeaningfulField();

					/** @var Result $r */
					$r = $this->doFinalAction($hasMeaningfulFields);
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}
					else
					{
						if (($data = $r->getData())
							&& !empty($data) && is_array($data))
						{
							$result->setData($result->getData() + $data);
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param $name
	 * @param $value
	 * @return Result
	 */
	protected function checkValueBeforeSet($name, $value)
	{
		return new Result();
	}

	/**
	 * @param bool $isMeaningfulField
	 * @return bool
	 */
	abstract public function isStartField($isMeaningfulField = false);

	/**
	 *
	 */
	abstract public function clearStartField();

	/**
	 * @return bool
	 */
	abstract public function hasMeaningfulField();

	/**
	 * @param bool $hasMeaningfulField
	 * @return Result
	 */
	abstract public function doFinalAction($hasMeaningfulField = false);

	/**
	 * @internal
	 * @param bool|false $value
	 */
	abstract public function setMathActionOnly($value = false);

	/**
	 * @return bool
	 */
	abstract public function isMathActionOnly();

	/**
	 * @internal
	 *
	 * @param $name
	 * @param $value
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function setFieldNoDemand($name, $value)
	{
		$allFields = static::getAllFields();
		if (!isset($allFields[$name]))
		{
			throw new Main\ArgumentOutOfRangeException($name);
		}

		$value = $this->normalizeValue($name, $value);

		$oldValue = $this->fields->get($name);

		if ($oldValue != $value || ($oldValue === null && $value !== null))
		{
			$this->fields->set($name, $value);
			static::addChangesToHistory($name, $oldValue, $value);
		}
	}

	/**
	 *
	 * @param array $values
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws \Exception
	 */
	public function setFields(array $values)
	{
		$resultData = array();
		$result = new Result();
		$oldValues = null;

		foreach ($values as $key => $value)
		{
			$oldValues[$key] = $this->fields->get($key);
		}

		if ($this->eventName === null)
		{
			$this->eventName = static::getEntityEventName();
		}

		if ($this->eventName)
		{
			$eventManager = Main\EventManager::getInstance();
			if ($eventsList = $eventManager->findEventHandlers('sale', 'OnBefore'.$this->eventName.'SetFields'))
			{
				$event = new Main\Event('sale', 'OnBefore'.$this->eventName.'SetFields', array(
					'ENTITY' => $this,
					'VALUES' => $values,
					'OLD_VALUES' => $oldValues
				));
				$event->send();

				if ($event->getResults())
				{
					/** @var Main\EventResult $eventResult */
					foreach($event->getResults() as $eventResult)
					{
						if($eventResult->getType() == Main\EventResult::SUCCESS)
						{
							if ($eventResultData = $eventResult->getParameters())
							{
								if (isset($eventResultData['VALUES']))
								{
									$values = $eventResultData['VALUES'];
								}
							}
						}
						elseif($eventResult->getType() == Main\EventResult::ERROR)
						{
							$errorMsg = new ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_ON_BEFORE_'.mb_strtoupper($this->eventName).'_SET_FIELDS_ERROR'), 'SALE_EVENT_ON_BEFORE_'.mb_strtoupper($this->eventName).'_SET_FIELDS_ERROR');

							if ($eventResultData = $eventResult->getParameters())
							{
								if (isset($eventResultData) && $eventResultData instanceof ResultError)
								{
									/** @var ResultError $errorMsg */
									$errorMsg = $eventResultData;
								}
							}

							$result->addError($errorMsg);
						}
					}
				}
			}
		}

		if (!$result->isSuccess())
		{
			return $result;
		}

		$values = $this->onBeforeSetFields($values);

		$isStartField = $this->isStartField();

		foreach ($values as $key => $value)
		{
			$r = $this->setField($key, $value);
			if (!$r->isSuccess())
			{
				$data = $r->getData();
				if (!empty($data) && is_array($data))
				{
					$resultData = array_merge($resultData, $data);
				}
				$result->addErrors($r->getErrors());
			}
			elseif ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());
			}
		}

		if (!empty($resultData))
		{
			$result->setData($resultData);
		}

		if ($isStartField)
		{
			$hasMeaningfulFields = $this->hasMeaningfulField();

			/** @var Result $r */
			$r = $this->doFinalAction($hasMeaningfulFields);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

			if (($data = $r->getData())
				&& !empty($data) && is_array($data))
			{
				$result->setData(array_merge($result->getData(), $data));
			}
		}

		return $result;
	}

	/**
	 * @param array $values
	 * @return array
	 */
	protected function onBeforeSetFields(array $values)
	{
		return $values;
	}

	/**
	 * @internal
	 *
	 * @param array $values
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function setFieldsNoDemand(array $values)
	{
		foreach ($values as $key => $value)
		{
			$this->setFieldNoDemand($key, $value);
		}
	}

	/**
	 * @internal
	 *
	 * @param $name
	 * @param $value
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function initField($name, $value)
	{
		$allFields = static::getAllFields();
		if (!isset($allFields[$name]))
		{
			throw new Main\ArgumentOutOfRangeException($name);
		}

		$this->fields->init($name, $value);
	}

	/**
	 * @internal
	 *
	 * @param array $values
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function initFields(array $values)
	{
		foreach ($values as $key => $value)
		{
			$this->initField($key, $value);
		}
	}

	/**
	 * @return array
	 */
	public function getFieldValues()
	{
		return $this->fields->getValues();
	}

	/**
	 * @internal
	 * @return Fields
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * @param string $name
	 * @param mixed $oldValue
	 * @param mixed $value
	 * @return Result
	 */
	protected function onFieldModify($name, $oldValue, $value)
	{
		return new Result();
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return (int)$this->getField("ID");
	}

	/**
	 * @param string $name
	 * @param null|string $oldValue
	 * @param null|string $value
	 */
	protected function addChangesToHistory($name, $oldValue = null, $value = null)
	{
		return;
	}

	/**
	 * @internal
	 *
	 * @throws Main\NotImplementedException
	 * @return mixed
	 */
	public static function getEntityEventName()
	{
		throw new Main\NotImplementedException(static::class . ':' . __FUNCTION__ . ' is not implemented');
	}

	/**
	 * @return string
	 */
	public static function getClassName()
	{
		return get_called_class();
	}

	/**
	 * @return bool
	 */
	public function isChanged()
	{
		return (bool)$this->fields->getChangedValues();
	}

	/**
	 * @param string $name
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function markFieldCustom(string $name)
	{
		$fields = static::getCustomizableFields();
		if (!isset($fields[$name]))
		{
			throw new Main\ArgumentOutOfRangeException(
				Main\Localization\Loc::getMessage(
					'SALE_INTERNALS_ENTITY_FIELD_IS_NOT_CUSTOMIZABLE',
					['#FIELD#' => $name]
				)
			);
		}

		$this->fields->markCustom($name);
	}

	/**
	 * @param string $name
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function unmarkFieldCustom(string $name)
	{
		$fields = static::getCustomizableFields();
		if (!isset($fields[$name]))
		{
			throw new Main\ArgumentOutOfRangeException(
				Main\Localization\Loc::getMessage(
					'SALE_INTERNALS_ENTITY_FIELD_IS_NOT_CUSTOMIZABLE',
					['#FIELD#' => $name]
				)
			);
		}

		$this->fields->unmarkCustom($name);
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function isMarkedFieldCustom(string $name) : bool
	{
		return $this->fields->isMarkedCustom($name);
	}

	/**
	 * @return Result
	 */
	public function verify()
	{
		return new Result();
	}

	/**
	 * @internal
	 */
	public function clearChanged()
	{
		$this->fields->clearChanged();
	}

	public function toArray() : array
	{
		return $this->getFieldValues();
	}
}
