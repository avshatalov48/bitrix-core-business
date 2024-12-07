<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\ORM\Fields\Relations;

use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\FieldTypeMask;

/**
 * Performs back-reference relation
 *
 * @package    bitrix
 * @subpackage main
 */
class OneToMany extends Relation
{
	/** @var string */
	protected $referenceName;

	/** @var int */
	protected $cascadeSavePolicy = CascadePolicy::FOLLOW;

	protected $cascadeDeletePolicy = CascadePolicy::SET_NULL; // follow | no_action | set_null

	public function __construct($name, $referenceEntity, $referenceName)
	{
		$this->referenceName = $referenceName;

		if ($referenceEntity instanceof Entity)
		{
			$this->refEntity = $referenceEntity;
			$this->refEntityName = $referenceEntity->getFullName();
		}
		else
		{
			// this one could be without leading backslash and/or with Table-postfix
			$this->refEntityName = Entity::normalizeName($referenceEntity);
		}

		parent::__construct($name);
	}

	public function getTypeMask()
	{
		return FieldTypeMask::ONE_TO_MANY;
	}

	public function getRefField()
	{
		return $this->getRefEntity()->getField($this->referenceName);
	}

	/**
	 * @return string
	 */
	public function getReferenceName()
	{
		return $this->referenceName;
	}
}
