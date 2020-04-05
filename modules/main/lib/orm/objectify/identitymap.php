<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\ORM\Objectify;

use Bitrix\Main\ORM\Entity;

/**
 * Object registry, stores objects in collections.
 *
 * @package    bitrix
 * @subpackage main
 */
class IdentityMap
{
	/** @var Collection[] */
	protected $collections;

	/**
	 * @param $class
	 * @param $primary
	 *
	 * @return EntityObject|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function get($class, $primary)
	{
		$collection = $this->getCollectionByClass($class);
		return $collection->getByPrimary($primary);
	}

	/**
	 * @param EntityObject $object
	 *
	 * @return Collection
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function put($object)
	{
		$collection = $this->getCollectionByClass(get_class($object));
		$collection[] = $object;

		return $collection;
	}

	/**
	 * @param $class
	 *
	 * @return Collection
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getCollectionByClass($class)
	{
		if (empty($this->collections[$class]))
		{
			$normalizedClass = Entity::normalizeName($class);

			if (!empty($this->collections[$normalizedClass]))
			{
				$this->collections[$class] = $this->collections[$normalizedClass];
			}
			else
			{
				if (Entity::has($normalizedClass))
				{
					$entity = Entity::get($normalizedClass);
				}
				else
				{
					/** @var $normalizedClass EntityObject custom object class */
					$entity = Entity::getInstance($normalizedClass::$dataClass);
				}

				$collection = $entity->createCollection();

				$this->collections[$class] = $collection;
				$this->collections[$normalizedClass] = $collection;
			}
		}

		return $this->collections[$class];
	}
}
