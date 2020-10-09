<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\ORM\Fields\Relations;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ORM\Fields\ITypeHintable;
use Bitrix\Main\SystemException;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\ORM\Query\Join;

/**
 * Performs relation mapping: back-reference and many-to-many relations.
 *
 * @package    bitrix
 * @subpackage main
 */
abstract class Relation extends Field implements ITypeHintable
{
	/** @var string Name of target entity */
	protected $refEntityName;

	/** @var Entity Target entity */
	protected $refEntity;

	/** @var string */
	protected $joinType = null;

	/** @var int */
	protected $cascadeSavePolicy;

	/** @var int */
	protected $cascadeDeletePolicy;

	/**
	 * @param int $cascadeSavePolicy
	 *
	 * @return Relation
	 */
	public function configureCascadeSavePolicy($cascadeSavePolicy)
	{
		$this->cascadeSavePolicy = $cascadeSavePolicy;

		return $this;
	}

	/**
	 * @param int $cascadeDeletePolicy
	 *
	 * @return Relation
	 */
	public function configureCascadeDeletePolicy($cascadeDeletePolicy)
	{
		$this->cascadeDeletePolicy = $cascadeDeletePolicy;

		return $this;
	}

	/**
	 * @return Entity
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws SystemException
	 */
	public function getRefEntity()
	{
		if ($this->refEntity === null)
		{
			// refEntityName could be an object or a data class
			if (class_exists($this->refEntityName) && is_subclass_of($this->refEntityName, EntityObject::class))
			{
				/** @var EntityObject $refObjectClass */
				$refObjectClass = $this->refEntityName;
				$this->refEntityName = $refObjectClass::$dataClass;
			}

			$this->refEntity = Entity::getInstance($this->refEntityName);
		}

		return $this->refEntity;
	}

	/**
	 * @return string
	 */
	public function getRefEntityName()
	{
		return $this->refEntityName;
	}

	/**
	 * @param $type
	 *
	 * @return $this
	 * @throws ArgumentException
	 */
	public function configureJoinType($type)
	{
		$type = mb_strtoupper($type);

		if (!in_array($type, Join::getTypes(), true))
		{
			throw new ArgumentException(sprintf(
				'Unknown join type `%s` in reference `%s` of `%s` entity',
				$type, $this->name, $this->entity->getDataClass()
			));
		}

		$this->joinType = $type;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getJoinType()
	{
		return $this->joinType;
	}

	/**
	 * @return int
	 */
	public function getCascadeSavePolicy()
	{
		return $this->cascadeSavePolicy;
	}

	/**
	 * @return int
	 */
	public function getCascadeDeletePolicy()
	{
		return $this->cascadeDeletePolicy;
	}

	/**
	 * @return EntityObject|string
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function getGetterTypeHint()
	{
		return $this->getRefEntity()->getObjectClass();
	}

	/**
	 * @return EntityObject|string
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function getSetterTypeHint()
	{
		return $this->getRefEntity()->getObjectClass();
	}
}
