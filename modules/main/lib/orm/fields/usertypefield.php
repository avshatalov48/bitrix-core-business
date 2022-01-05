<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;
use Bitrix\Main\ArgumentException;

/**
 * UserType proxy fields. Works like expressions and allows to be set.
 *
 * @property ScalarField|UserTypeUtsMultipleField $valueField
 * @package    bitrix
 * @subpackage main
 */
class UserTypeField extends ExpressionField
{
	/** @var bool */
	protected $isMultiple = false;

	/**
	 * @param mixed $value
	 *
	 * @return mixed
	 * @throws ArgumentException
	 */
	public function cast($value)
	{
		if ($this->isMultiple)
		{
			if ($value !== false && $value !== null) // empty value for multiple field
			{
				//if (!\is_iterable($value)) PHP 7
				if (!is_array($value) && !($value instanceof \Traversable))
				{
					throw new ArgumentException(sprintf(
						'Expected iterable value for multiple field, but got `%s` instead', gettype($value)
					));
				}

				// array of values
				foreach ($value as &$_value)
				{
					$_value = $this->valueField->getUtmField()->cast($_value);
				}
			}

			return $value;
		}
		else
		{
			return parent::cast($value);
		}
	}

	/**
	 * @return mixed
	 */
	public function getTypeMask()
	{
		return FieldTypeMask::USERTYPE;
	}

	/**
	 * @param bool $isMultiple
	 *
	 * @return $this
	 */
	public function configureMultiple($isMultiple = true)
	{
		$this->isMultiple = (bool) $isMultiple;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function isMultiple()
	{
		return $this->isMultiple;
	}

	/**
	 * @param mixed $value
	 *
	 * @return string
	 * @throws \Bitrix\Main\SystemException
	 */
	public function convertValueToDb($value)
	{
		return $this->isMultiple
			? $this->getConnection()->getSqlHelper()->convertToDbString($value) // serialized values
			: parent::convertValueToDb($value);
	}

	public function getFetchDataModifiers()
	{
		$srcField = $this->getBuildFromChains()[0]->getLastElement()->getValue();

		return array_merge(parent::getFetchDataModifiers(), $srcField->getFetchDataModifiers());
	}

	public function getSaveDataModifiers()
	{
		$srcField = $this->getBuildFromChains()[0]->getLastElement()->getValue();

		return array_merge(parent::getSaveDataModifiers(), $srcField->getSaveDataModifiers());
	}
}
