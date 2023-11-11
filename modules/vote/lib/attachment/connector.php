<?php

namespace Bitrix\Vote\Attachment;

use Bitrix\Main\Application;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

abstract class Connector
{
	protected $entityId;

	public function __construct($entityId)
	{
		$this->entityId = $entityId;
	}

	/**
	 * @param \Bitrix\Vote\Attach $attachedObject Attach.
	 * @return Connector
	 * @throws ObjectNotFoundException
	 * @throws SystemException
	 */
	final public static function buildFromAttachedObject(\Bitrix\Vote\Attach $attachedObject)
	{
		if(!Loader::includeModule($attachedObject->getModuleId()))
		{
			throw new SystemException("Module {$attachedObject->getModuleId()} is not included.");
		}
		$className = str_replace('\\\\', '\\', $attachedObject->getEntityType());
		/** @var \Bitrix\Vote\Attachment\Connector $connector */
		if (!is_a($className, Connector::class, true))
		{
			throw new ObjectNotFoundException('Connector class should be instance of Bitrix\Vote\Attachment\Connector.');
		}

		$connector = new $className($attachedObject->getEntityId());

		if($connector instanceof Storable)
		{
			$connector->setStorage($attachedObject->getStorage());
		}

		return $connector;
	}

	/**
	 * @return string
	 */
	public static function className()
	{
		return get_called_class();
	}

	/**
	 * @todo finis this method
	 * @return array
	 */
	public function getDataToShow()
	{
		return array();
	}

	/**
	 * @param integer $userId User ID.
	 * @return bool
	 */
	public function canRead($userId)
	{
		return false;
	}

	/**
	 * @param integer $userId User ID.
	 * @return bool
	 */
	public function canEdit($userId)
	{
		return false;
	}

	/**
	 * @param array $data Data array.
	 * @return array
	 */
	public function checkFields(&$data)
	{
		return $data;
	}

	/**
	 * @return Application|\Bitrix\Main\HttpApplication|\CMain
	 */
	protected function getApplication()
	{
		global $APPLICATION;
		return $APPLICATION;
	}

	/**
	 * @return array|bool|\CUser
	 */
	protected function getUser()
	{
		global $USER;
		return $USER;
	}
}