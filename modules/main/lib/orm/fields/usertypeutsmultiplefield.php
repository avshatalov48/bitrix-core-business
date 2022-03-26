<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2021 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;

/**
 * @package    bitrix
 * @subpackage main
 */
class UserTypeUtsMultipleField extends TextField
{
	/** @var ScalarField */
	protected $utmField;

	/**
	 * @param Field $utmField
	 * @return $this
	 */
	public function configureUtmField(Field $utmField)
	{
		$this->utmField = $utmField;

		return $this;
	}

	/**
	 * @return ScalarField
	 */
	public function getUtmField()
	{
		return $this->utmField;
	}

	/**
	 * @inheritDoc
	 */
	public function getFetchDataModifiers()
	{
		$modifiers = parent::getFetchDataModifiers();

		if ($this->utmField->getFetchDataModifiers())
		{
			$modifiers[] = [$this, 'proxyFetchDataModification'];
		}

		return $modifiers;
	}

	/**
	 * @param $values
	 * @param $query
	 * @param $data
	 * @param $alias
	 * @return []
	 * @throws \Bitrix\Main\SystemException
	 */
	public function proxyFetchDataModification($values, $query, $data, $alias)
	{
		if ($values !== null)
		{
			foreach ($values as $k => $value)
			{
				foreach ($this->utmField->getFetchDataModifiers() as $modifier)
				{
					$values[$k] = call_user_func_array($modifier, array($values[$k], $query, $data, $alias));
				}
			}
		}

		return $values;
	}
}
