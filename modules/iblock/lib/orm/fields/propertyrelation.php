<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2018 Bitrix
 */


namespace Bitrix\Iblock\ORM\Fields;


use Bitrix\Iblock\ORM\PropertyValue;
use Bitrix\Iblock\Property;
use Bitrix\Main\Cli\OrmAnnotateCommand;

trait PropertyRelation
{
	/** @var Property */
	protected $iblockElementProperty;

	/**
	 * @return Property
	 */
	public function getIblockElementProperty()
	{
		return $this->iblockElementProperty;
	}

	/**
	 * @param $property
	 *
	 * @return $this
	 */
	public function configureIblockElementProperty($property)
	{
		$this->iblockElementProperty = $property;

		return $this;
	}

	public function getSetterTypeHint()
	{
		return parent::getSetterTypeHint()
			.'|\\'.PropertyValue::class
			.'|'.$this->getRefEntity()->getField('VALUE')->getSetterTypeHint();
	}
}