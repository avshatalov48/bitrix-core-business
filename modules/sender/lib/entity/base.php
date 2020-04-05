<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Entity;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\SystemException;
use Bitrix\Main\Entity\Base as MainEntityBase;
use Bitrix\Main\Entity\DataManager as MainDataManager;

use Bitrix\Sender\Security;
use Bitrix\Sender\Search;

Loc::loadMessages(__FILE__);

/**
 * Class Base
 * @package Bitrix\Sender\Entity
 */
abstract class Base
{
	const SEARCH_FIELD_NAME = 'SEARCH_CONTENT';

	/** @var ErrorCollection $errors */
	protected $errors;

	/** @var integer $id ID. */
	protected $id;

	/** @var array $data Data. */
	protected $data = array();

	/** @var Security\User|null $user User. */
	protected $user;

	/** @var Search\Builder|null $searchBuilder Search builder. */
	protected $searchBuilder;

	/**
	 * Create instance.
	 *
	 * @param integer|null $id ID.
	 * @return static
	 */
	public static function create($id = null)
	{
		return new static($id);
	}

	/**
	 * Base constructor.
	 *
	 * @param integer|null $id ID.
	 */
	public function __construct($id = null)
	{
		$this->errors = new ErrorCollection();
		$this->setData($this->getDefaultData());

		if ($id)
		{
			$this->load($id);
		}
	}

	/**
	 * Get default data.
	 *
	 * @return array
	 */
	protected function getDefaultData()
	{
		return array();
	}


	protected function filterDataByEntityFields(MainEntityBase $entity, array &$data)
	{
		foreach ($data as $key => $value)
		{
			if (!$entity->hasField($key))
			{
				unset($data[$key]);
			}
		}
	}


	protected function filterDataByChanging(array &$data, array $previousData)
	{
		foreach ($data as $key => $value)
		{
			if (!isset($previousData[$key]))
			{
				continue;
			}

			if ($previousData[$key] !== $data[$key])
			{
				continue;
			}

			unset($data[$key]);
		}

		return count($data) > 0;
	}

	/**
	 * Save data by data manager class name.
	 *
	 * @param MainEntityBase $entity Entity.
	 * @param array|integer|null $id ID.
	 * @param array $data Data.
	 * @param array|null $primary Primary.
	 * @return integer|null
	 * @throws SystemException
	 */
	protected function saveByEntity(MainEntityBase $entity, $id = null, array $data, $primary = null)
	{
		/** @var \Bitrix\Main\Entity\DataManager $className Class name. */
		$className = $entity->getDataClass();

		$primary = $primary ?: $id;

		if($id)
		{
			$resultDb = $className::update($primary, $data);
		}
		else
		{
			$resultDb = $className::add($data);
			$id = $resultDb->getId();
		}

		if(!$resultDb->isSuccess())
		{
			$this->errors->add($resultDb->getErrors());
		}

		return $id;
	}

	/**
	 * Remove by data manager class name.
	 *
	 * @param MainEntityBase $entity Entity.
	 * @param array|integer $primary Primary.
	 * @return bool
	 * @throws SystemException
	 */
	protected function removeByEntity(MainEntityBase $entity, $primary)
	{
		/** @var \Bitrix\Main\Entity\DataManager $className Class name. */
		$className = $entity->getDataClass();
		$result = $className::delete($primary);

		if(!$result->isSuccess())
		{
			$this->errors->add($result->getErrors());
		}

		return !$this->hasErrors();
	}

	/**
	 * Load data.
	 *
	 * @param integer $id ID.
	 * @return array|null
	 */
	abstract protected function loadData($id);

	/**
	 * Save data.
	 *
	 * @param integer|null $id ID.
	 * @param array $data Data.
	 * @return integer|null
	 */
	abstract protected function saveData($id = null, array $data);

	/**
	 * Copy data.
	 *
	 * @param integer $id ID.
	 * @param array $data Data.
	 * @return integer|null
	 */
	protected function copyData($id, array $data = array())
	{
		$loadedData = $this->loadData($id);
		if (!$loadedData)
		{
			return false;
		}
		unset($loadedData['ID']);
		$data = $data + $loadedData;

		foreach ($data['FIELDS'] as $index => $field)
		{
			if ($field['TYPE'] !== 'file')
			{
				continue;
			}

			if (empty($field['VALUE']))
			{
				continue;
			}

			$values = is_array($field['VALUE']) ? $field['VALUE'] : explode(',', $field['VALUE']);
			$field['VALUE'] = array();
			foreach ($values as $fileId)
			{
				$copiedFileId = \CFile::copyFile($fileId);
				if (!$copiedFileId)
				{
					continue;
				}

				$field['VALUE'][] = $copiedFileId;
			}
			$field['VALUE'] = implode(',', $field['VALUE']);
			$data['FIELDS'][$index] = $field;
		}

		return $this->saveData(null, $data);
	}

	/**
	 * Load by array.
	 *
	 * @param array $data Data.
	 * @return bool
	 */
	public function loadByArray(array $data)
	{
		$this->clearErrors();
		$this->setId((isset($data['ID']) && $data['ID']) ? $data['ID'] : null);
		$this->setData($this->getDefaultData())->mergeData($data);

		return !$this->hasErrors();
	}

	/**
	 * Load.
	 *
	 * @param integer $id ID.
	 * @return bool
	 */
	public function load($id)
	{
		$this->clearErrors();
		$this->setData($this->getDefaultData());

		if (!$id)
		{
			return false;
		}

		$data = $this->loadData($id);
		if (!is_array($data))
		{
			$data = array();
		}
		$this->mergeData($data);

		if (!$this->hasErrors())
		{
			$this->id = $id;
		}

		return !$this->hasErrors();
	}

	/**
	 * Save.
	 */
	public function save()
	{
		$this->clearErrors();

		$id = $this->saveData($this->getId(), $this->getData());
		if ($id)
		{
			$this->setId($id);
		}

		if (!$this->hasErrors())
		{
			$this->saveSearchIndex();
		}

		return !$this->hasErrors();
	}

	/**
	 * Get ID.
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set ID.
	 *
	 * @param integer $id ID.
	 * @return $this
	 */
	public function setId($id)
	{
		if ($id)
		{
			$this->id = $id;
		}

		return $this;
	}

	/**
	 * Set data value by key.
	 *
	 * @param string $key Key.
	 * @param mixed $value Value.
	 * @return $this
	 */
	public function set($key, $value)
	{
		$this->data[$key] = $value;
		return $this;
	}

	/**
	 * Unset data value by key.
	 *
	 * @param string $key Key.
	 * @return $this
	 */
	public function unsetByKey($key)
	{
		unset($this->data[$key]);
		return $this;
	}

	/**
	 * Get data value by key.
	 *
	 * @param string $key Key.
	 * @param mixed|null $defaultValue Default value.
	 * @return mixed
	 */
	public function get($key, $defaultValue = null)
	{
		return (isset($this->data[$key]) ? $this->data[$key] : $defaultValue);
	}

	/**
	 * Merge data.
	 *
	 * @param array $data Data.
	 * @return $this
	 */
	public function mergeData(array $data)
	{
		$this->setData($data + $this->getData());
		return $this;
	}

	/**
	 * Set data.
	 *
	 * @param array $data Data.
	 * @return $this
	 */
	public function setData(array $data)
	{
		$this->data = $data;
		return $this;
	}

	/**
	 * Get data.
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Return true if it have errors.
	 *
	 * @return bool
	 */
	public function hasErrors()
	{
		return !$this->errors->isEmpty();
	}

	/**
	 * Clear errors.
	 */
	public function clearErrors()
	{
		$this->errors->clear();
	}

	/**
	 * Add error.
	 *
	 * @param string $message Message text.
	 * @param string|null $code Code.
	 */
	public function addError($message, $code = null)
	{
		$this->errors->setError(new Error($message, $code));
	}

	/**
	 * Get error collection.
	 *
	 * @return ErrorCollection
	 */
	public function getErrorCollection()
	{
		return $this->errors;
	}

	/**
	 * Get errors.
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors->toArray();
	}

	/**
	 * Get error messages.
	 *
	 * @return array
	 */
	public function getErrorMessages()
	{
		$list = array();
		foreach ($this->errors as $error)
		{
			/** @var Error $error Error. */
			$list[] = $error->getMessage();
		}

		return $list;
	}

	/**
	 * Set user.
	 *
	 * @param Security\User|null $user User.
	 * @return $this;
	 */
	public function setUser(Security\User $user = null)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * Get user.
	 *
	 * @return Security\User;
	 */
	public function getUser()
	{
		if (!$this->user)
		{
			$this->user = Security\User::current();
		}

		return $this->user;
	}

	/**
	 * Get data class.
	 *
	 * @return null|MainDataManager;
	 */
	public static function getDataClass()
	{
		return null;
	}

	/**
	 * Get data class.
	 *
	 * @return null|Search\Builder
	 */
	public static function getSearchBuilder()
	{
		static $builder = null;
		if ($builder === null && static::getDataClass())
		{
			$dataClass = static::getDataClass();
			$builder = new Search\Builder(
				$dataClass::getEntity(),
				static::SEARCH_FIELD_NAME
			);
		}

		return $builder;
	}



	/**
	 * Prepare search content.
	 *
	 * @return $this
	 */
	protected function prepareSearchContent()
	{
		return $this;
	}

	/**
	 * Save search index.
	 *
	 * @return bool
	 */
	public function saveSearchIndex()
	{
		if (!$this->getId())
		{
			return false;
		}

		if (!$this->getSearchBuilder())
		{
			return false;
		}

		if (!$this->getSearchBuilder()->hasField())
		{
			return false;
		}

		$this->getSearchBuilder()->getContent()->clear();
		$this->prepareSearchContent();
		return $this->getSearchBuilder()->save($this->getId());
	}
}