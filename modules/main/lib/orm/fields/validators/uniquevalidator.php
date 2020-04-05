<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2013 Bitrix
 */

namespace Bitrix\Main\ORM\Fields\Validators;

use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class UniqueValidator extends Validator
{
	/**
	 * @var string
	 */
	protected $errorPhraseCode = 'MAIN_ENTITY_VALIDATOR_UNIQUE';

	public function validate($value, $primary, array $row, ORM\Fields\Field $field)
	{
		$entity = $field->getEntity();
		$primaryNames = $entity->getPrimaryArray();

		$query = new Query($entity);
		$query->setSelect($primaryNames);
		$query->setFilter(array('='.$field->getName() => $value));
		$query->setLimit(2);
		$result = $query->exec();

		while ($existing = $result->fetch())
		{
			// check primary
			foreach ($existing as $k => $v)
			{
				if (!isset($primary[$k]) || $primary[$k] != $existing[$k])
				{
					return $this->getErrorMessage($value, $field);
				}
			}
		}

		return true;
	}
}
