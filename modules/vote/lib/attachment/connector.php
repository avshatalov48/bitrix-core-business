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
	 * @param Attach $attachedObject Attach.
	 * @return Connector
	 * @throws ObjectNotFoundException
	 * @throws SystemException
	 */
	final public static function buildFromAttachedObject(Attach $attachedObject)
	{
		if(!Loader::includeModule($attachedObject->getModuleId()))
		{
			throw new SystemException("Module {$attachedObject->getModuleId()} is not included.");
		}
		$className = str_replace('\\\\', '\\', $attachedObject->getEntityType());
		/** @var \Bitrix\Vote\Attachment\Connector $connector */
		$connector = new $className($attachedObject->getEntityId());

		if(!$connector instanceof Connector)
		{
			throw new ObjectNotFoundException('Connector class should be instance of Connector.');
		}
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
	 * @return Application|\Bitrix\Main\HttpApplication|\CAllMain|\CMain
	 */
	protected function getApplication()
	{
		global $APPLICATION;
		return $APPLICATION;
	}

	/**
	 * @return array|bool|\CAllUser|\CUser
	 */
	protected function getUser()
	{
		global $USER;
		return $USER;
	}
}