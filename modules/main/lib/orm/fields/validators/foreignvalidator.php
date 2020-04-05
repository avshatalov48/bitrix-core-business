<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2014 Bitrix
 */

namespace Bitrix\Main\ORM\Fields\Validators;

use Bitrix\Main\ORM;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Checks if field value exists in referenced entity
 *
 * example: new Foreign(GroupTable::getEntity()->getField('ID'));
 *
 * @package Bitrix\Main\ORM\Validator
 */
class ForeignValidator extends Validator
{
	protected $errorPhraseCode = 'MAIN_ENTITY_VALIDATOR_FOREIGN';
	protected $reference = null;
	protected $filter = null;
	const NOT_EXISTS = 'BX_FOREIGN_NOT_EXISTS';

	/**
	 * @param ORM\Fields\Field $reference
	 * @param array            $filter
	 */
	public function __construct(ORM\Fields\Field $reference, array $filter = array())
	{
		$this->reference = $reference;
		$this->filter = $filter;
		parent::__construct();
	}

	public function validate($value, $primary, array $row, ORM\Fields\Field $field)
	{
		$query = new ORM\Query\Query($this->reference->getEntity());
		$query->setFilter(array('='.$this->reference->getName() => $value) + $this->filter);
		$query->setLimit(1);
		$result = $query->exec();

		if($result->fetch())
		{
			return true;
		}
		return 	new ORM\Fields\FieldError($field, $this->getErrorMessage($value, $field), self::NOT_EXISTS);
	}
}
