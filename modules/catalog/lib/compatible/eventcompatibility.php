<?php
/** @global \CMain $APPLICATION */

namespace Bitrix\Catalog\Compatible;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Catalog;

Loc::loadMessages(__FILE__);

final class EventCompatibility
{
	/* Events old kernel, which will be called in a new kernel */

	/* class \CProduct */
	const ENTITY_PRODUCT = 'Product';
	const EVENT_ON_BEFORE_PRODUCT_ADD = 'OnBeforeProductAdd';
	const EVENT_ON_PRODUCT_ADD = 'OnProductAdd';
	const EVENT_ON_BEFORE_PRODUCT_UPDATE = 'OnBeforeProductUpdate';
	const EVENT_ON_PRODUCT_UPDATE = 'OnProductUpdate';

	/* class \CPrice */
	const ENTITY_PRICE = 'Price';
	const EVENT_ON_BEFORE_PRICE_ADD = 'OnBeforePriceAdd';
	const EVENT_ON_PRICE_ADD = 'OnPriceAdd';
	const EVENT_ON_BEFORE_PRICE_UPDATE = 'OnBeforePriceUpdate';
	const EVENT_ON_PRICE_UPDATE = 'OnPriceUpdate';
	const EVENT_ON_BEFORE_PRICE_DELETE = 'OnBeforePriceDelete';
	const EVENT_ON_PRICE_DELETE = 'OnPriceDelete';

	private static $allowEvents = 0;

	private static $handlerList = [];

	private static $whiteList = [];

	private static $entityClass = [
		self::ENTITY_PRODUCT => '\Bitrix\Catalog\Model\Product',
		self::ENTITY_PRICE => '\Bitrix\Catalog\Model\Price'
	];

	public static function execAgent()
	{
		self::registerEvents();
		return '';
	}

	public static function registerEvents()
	{
		$eventManager = Main\EventManager::getInstance();

		$eventManager->registerEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Product::'.Main\Entity\DataManager::EVENT_ON_BEFORE_ADD,
			'catalog', __CLASS__, 'handlerProductOnBeforeAdd'
		);
		$eventManager->registerEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Product::'.Main\Entity\DataManager::EVENT_ON_AFTER_ADD,
			'catalog', __CLASS__, 'handlerProductOnAfterAdd'
		);

		$eventManager->registerEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Product::'.Main\Entity\DataManager::EVENT_ON_BEFORE_UPDATE,
			'catalog', __CLASS__, 'handlerProductOnBeforeUpdate'
		);
		$eventManager->registerEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Product::'.Main\Entity\DataManager::EVENT_ON_AFTER_UPDATE,
			'catalog', __CLASS__, 'handlerProductOnAfterUpdate'
		);

		$eventManager->registerEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Price::'.Main\Entity\DataManager::EVENT_ON_BEFORE_ADD,
			'catalog', __CLASS__, 'handlerPriceOnBeforeAdd'
		);
		$eventManager->registerEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Price::'.Main\Entity\DataManager::EVENT_ON_AFTER_ADD,
			'catalog', __CLASS__, 'handlerPriceOnAfterAdd'
		);

		$eventManager->registerEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Price::'.Main\Entity\DataManager::EVENT_ON_BEFORE_UPDATE,
			'catalog', __CLASS__, 'handlerPriceOnBeforeUpdate'
		);
		$eventManager->registerEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Price::'.Main\Entity\DataManager::EVENT_ON_AFTER_UPDATE,
			'catalog', __CLASS__, 'handlerPriceOnAfterUpdate'
		);

		$eventManager->registerEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Price::'.Main\Entity\DataManager::EVENT_ON_BEFORE_DELETE,
			'catalog', __CLASS__, 'handlerPriceOnBeforeDelete'
		);
		$eventManager->registerEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Price::'.Main\Entity\DataManager::EVENT_ON_AFTER_DELETE,
			'catalog', __CLASS__, 'handlerPriceOnAfterDelete'
		);

		unset($eventManager);
	}

	public static function unRegisterEvents()
	{
		$eventManager = Main\EventManager::getInstance();

		$eventManager->unRegisterEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Product::'.Main\Entity\DataManager::EVENT_ON_BEFORE_ADD,
			'catalog', __CLASS__, 'handlerProductOnBeforeAdd'
		);
		$eventManager->unRegisterEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Product::'.Main\Entity\DataManager::EVENT_ON_AFTER_ADD,
			'catalog', __CLASS__, 'handlerProductOnAfterAdd'
		);

		$eventManager->unRegisterEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Product::'.Main\Entity\DataManager::EVENT_ON_BEFORE_UPDATE,
			'catalog', __CLASS__, 'handlerProductOnBeforeUpdate'
		);
		$eventManager->unRegisterEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Product::'.Main\Entity\DataManager::EVENT_ON_AFTER_UPDATE,
			'catalog', __CLASS__, 'handlerProductOnAfterUpdate'
		);

		$eventManager->unRegisterEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Price::'.Main\Entity\DataManager::EVENT_ON_BEFORE_ADD,
			'catalog', __CLASS__, 'handlerPriceOnBeforeAdd'
		);
		$eventManager->unRegisterEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Price::'.Main\Entity\DataManager::EVENT_ON_AFTER_ADD,
			'catalog', __CLASS__, 'handlerPriceOnAfterAdd'
		);

		$eventManager->unRegisterEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Price::'.Main\Entity\DataManager::EVENT_ON_BEFORE_UPDATE,
			'catalog', __CLASS__, 'handlerPriceOnBeforeUpdate'
		);
		$eventManager->unRegisterEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Price::'.Main\Entity\DataManager::EVENT_ON_AFTER_UPDATE,
			'catalog', __CLASS__, 'handlerPriceOnAfterUpdate'
		);

		$eventManager->unRegisterEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Price::'.Main\Entity\DataManager::EVENT_ON_BEFORE_DELETE,
			'catalog', __CLASS__, 'handlerPriceOnBeforeDelete'
		);
		$eventManager->unRegisterEventHandler(
			'catalog', 'Bitrix\Catalog\Model\Price::'.Main\Entity\DataManager::EVENT_ON_AFTER_DELETE,
			'catalog', __CLASS__, 'handlerPriceOnAfterDelete'
		);

		unset($eventManager);
	}

	public static function handlerProductOnBeforeAdd(Catalog\Model\Event $event)
	{
		return self::handlerOnBeforeAdd($event, self::ENTITY_PRODUCT, self::EVENT_ON_BEFORE_PRODUCT_ADD);
	}

	public static function handlerProductOnAfterAdd(Catalog\Model\Event $event)
	{
		$result = new Catalog\Model\EventResult();
		if (!self::allowedEvents())
			return $result;

		$success = $event->getParameter('success');
		if (!$success)
			return $result;

		self::disableEvents();

		$handlerList = array_merge(
			self::getHandlerList('catalog', self::EVENT_ON_PRODUCT_ADD),
			self::getHandlerList('sale', self::EVENT_ON_PRODUCT_ADD)       // compatibility with old strange code
		);
		if (!empty($handlerList))
		{
			$data = [
				$event->getParameter('id'),
				$event->getParameter('fields') + $event->getParameter('external_fields')
			];

			foreach ($handlerList as $handler)
			{
				ExecuteModuleEventEx($handler, $data);
			}
			unset($handler, $data);
		}
		unset($handlerList);

		self::enableEvents();

		return $result;
	}

	public static function handlerProductOnBeforeUpdate(Catalog\Model\Event $event)
	{
		return self::handlerOnBeforeUpdate($event, self::ENTITY_PRODUCT, self::EVENT_ON_BEFORE_PRODUCT_UPDATE);
	}

	public static function handlerProductOnAfterUpdate(Catalog\Model\Event $event)
	{
		return self::handlerOnAfterModify($event, self::EVENT_ON_PRODUCT_UPDATE);
	}

	public static function handlerPriceOnBeforeAdd(Catalog\Model\Event $event)
	{
		$result = new Catalog\Model\EventResult();
		if (!self::allowedEvents())
			return $result;

		self::disableEvents();

		$handlerList = self::getHandlerList('catalog', self::EVENT_ON_BEFORE_PRICE_ADD);

		if (!empty($handlerList))
		{
			$error = false;
			$fields = $event->getParameter('fields');
			$externalFields = $event->getParameter('external_fields');
			$oldFields = $fields;
			$oldExternalFields = $externalFields;
			$actions = $event->getParameter('actions');
			$fields['RECALL'] = (isset($actions['OLD_RECOUNT']) && $actions['OLD_RECOUNT'] === true);

			foreach ($handlerList as $handler)
			{
				if (ExecuteModuleEventEx($handler, [&$fields]) === false)
				{
					$error = true;
					break;
				}
			}
			unset($handler);

			if (isset($fields['RECALL']))
			{
				$result->modifyActions(['OLD_RECOUNT' => $fields['RECALL']]);
				unset($fields['RECALL']);
			}
			else
			{
				if (isset($actions['OLD_RECOUNT']))
					$result->unsetActions(['OLD_RECOUNT']);
			}

			self::fillResultData($result, self::ENTITY_PRICE, $oldFields, $oldExternalFields, $fields);
			unset($actions, $oldExternalFields, $oldFields, $externalFields, $fields);

			if ($error)
				self::setHandlerError($result, self::EVENT_ON_BEFORE_PRICE_ADD);
			unset($error);
		}

		self::enableEvents();

		return $result;
	}

	public static function handlerPriceOnAfterAdd(Catalog\Model\Event $event)
	{
		$result = new Catalog\Model\EventResult();
		if (!self::allowedEvents())
			return $result;

		$success = $event->getParameter('success');
		if (!$success)
			return $result;

		self::disableEvents();

		$handlerList = array_merge(
			self::getHandlerList('catalog', self::EVENT_ON_PRICE_ADD),
			self::getHandlerList('sale', self::EVENT_ON_PRICE_ADD)       // compatibility with old strange code
		);
		if (!empty($handlerList))
		{
			$fields = $event->getParameter('fields');
			$actions = $event->getParameter('actions');
			$fields['RECALL'] = (isset($actions['OLD_RECOUNT']) && $actions['OLD_RECOUNT'] === true);
			$data = [
				$event->getParameter('id'),
				$fields + $event->getParameter('external_fields')
			];
			unset($actions, $fields);

			foreach ($handlerList as $handler)
			{
				ExecuteModuleEventEx($handler, $data);
			}
			unset($handler, $data);
		}
		unset($handlerList);

		self::enableEvents();

		return $result;
	}

	public static function handlerPriceOnBeforeUpdate(Catalog\Model\Event $event)
	{
		$result = new Catalog\Model\EventResult();
		if (!self::allowedEvents())
			return $result;

		self::disableEvents();

		$handlerList = self::getHandlerList('catalog', self::EVENT_ON_BEFORE_PRICE_UPDATE);
		if (!empty($handlerList))
		{
			$error = false;
			$id = $event->getParameter('id');
			$fields = $event->getParameter('fields');
			$externalFields = $event->getParameter('external_fields');
			$oldFields = $fields;
			$oldExternalFields = $externalFields;
			$actions = $event->getParameter('actions');
			$fields['RECALL'] = (isset($actions['OLD_RECOUNT']) && $actions['OLD_RECOUNT'] === true);

			foreach ($handlerList as $handler)
			{
				if (ExecuteModuleEventEx($handler, [$id, &$fields]) === false)
				{
					$error = true;
					break;
				}
			}
			unset($handler);

			if (isset($fields['RECALL']))
			{
				$result->modifyActions(['OLD_RECOUNT' => $fields['RECALL']]);
				unset($fields['RECALL']);
			}
			else
			{
				if (isset($actions['OLD_RECOUNT']))
					$result->unsetActions(['OLD_RECOUNT']);
			}

			self::fillResultData($result, self::ENTITY_PRICE, $oldFields, $oldExternalFields, $fields);
			unset($actions, $oldExternalFields, $oldFields, $externalFields, $fields);

			if ($error)
				self::setHandlerError($result, self::EVENT_ON_BEFORE_PRICE_UPDATE);
			unset($error);
		}
		unset($handlerList);

		self::enableEvents();

		return $result;
	}

	public static function handlerPriceOnAfterUpdate(Catalog\Model\Event $event)
	{
		return self::handlerPriceOnAfterModify($event, self::EVENT_ON_PRICE_UPDATE);
	}

	public static function handlerPriceOnBeforeDelete(Catalog\Model\Event $event)
	{
		return self::handlerOnBeforeDelete($event, self::EVENT_ON_BEFORE_PRICE_DELETE);
	}

	public static function handlerPriceOnAfterDelete(Catalog\Model\Event $event)
	{
		return self::handlerOnAfterDelete($event, self::EVENT_ON_PRICE_DELETE);
	}

	/**
	 * Enable old kernel events.
	 *
	 * @return void
	 */
	private static function enableEvents()
	{
		self::$allowEvents++;
	}

	/**
	 * Disable old kernel events.
	 *
	 * @return void
	 */
	private static function disableEvents()
	{
		self::$allowEvents--;
	}

	/**
	 * Return is allow old kernel events.
	 *
	 * @return bool
	 */
	private static function allowedEvents()
	{
		return (self::$allowEvents >= 0);
	}

	private static function getHandlerList($module, $event)
	{
		$eventIndex = $module.':'.$event;
		if (!isset(self::$handlerList[$eventIndex]))
		{
			self::$handlerList[$eventIndex] = [];
			$eventManager = Main\EventManager::getInstance();
			$result = $eventManager->findEventHandlers($module, $event);
			if (!empty($result))
			{
				foreach (array_keys($result) as $index)
				{
					$result[$index]['FROM_MODULE_ID'] = 'catalog';
					$result[$index]['MESSAGE_ID'] = $event;
				}
				unset($index);
				self::$handlerList[$eventIndex] = $result;
			}
			unset($result, $eventManager);
		}
		return self::$handlerList[$eventIndex];
	}

	private static function handlerOnBeforeAdd(Catalog\Model\Event $event, $entity, $eventName)
	{
		$result = new Catalog\Model\EventResult();
		if (!self::allowedEvents())
			return $result;

		self::disableEvents();

		$handlerList = self::getHandlerList('catalog', $eventName);
		if (!empty($handlerList))
		{
			$error = false;
			$fields = $event->getParameter('fields');
			$externalFields = $event->getParameter('external_fields');
			$oldFields = $fields;
			$oldExternalFields = $externalFields;
			if (!empty($externalFields))
				$fields = $fields + $externalFields;

			foreach ($handlerList as $handler)
			{
				if (ExecuteModuleEventEx($handler, [&$fields]) === false)
				{
					$error = true;
					break;
				}
			}
			unset($handler);

			self::fillResultData($result, $entity, $oldFields, $oldExternalFields, $fields);
			unset($oldExternalFields, $oldFields, $externalFields, $fields);

			if ($error)
				self::setHandlerError($result, $eventName);
			unset($error);
		}

		self::enableEvents();

		return $result;
	}

	private static function handlerOnAfterModify(Catalog\Model\Event $event, $eventName)
	{
		$result = new Catalog\Model\EventResult();
		if (!self::allowedEvents())
			return $result;

		$success = $event->getParameter('success');
		if (!$success)
			return $result;

		self::disableEvents();

		$handlerList = self::getHandlerList('catalog', $eventName);
		if (!empty($handlerList))
		{
			$data = [
				$event->getParameter('id'),
				$event->getParameter('fields') + $event->getParameter('external_fields')
			];

			foreach ($handlerList as $handler)
			{
				ExecuteModuleEventEx($handler, $data);
			}
			unset($handler, $data);
		}
		unset($handlerList);

		self::enableEvents();

		return $result;
	}

	private static function handlerOnBeforeUpdate(Catalog\Model\Event $event, $entity, $eventName)
	{
		$result = new Catalog\Model\EventResult();
		if (!self::allowedEvents())
			return $result;

		self::disableEvents();

		$handlerList = self::getHandlerList('catalog', $eventName);
		if (!empty($handlerList))
		{
			$error = false;
			$id = $event->getParameter('id');
			$fields = $event->getParameter('fields');
			$externalFields = $event->getParameter('external_fields');
			$oldFields = $fields;
			$oldExternalFields = $externalFields;
			if (!empty($externalFields))
				$fields = $fields + $externalFields;

			foreach ($handlerList as $handler)
			{
				if (ExecuteModuleEventEx($handler, [$id, &$fields]) === false)
				{
					$error = true;
					break;
				}
			}
			unset($handler);

			self::fillResultData($result, $entity, $oldFields, $oldExternalFields, $fields);
			unset($oldExternalFields, $oldFields, $externalFields, $fields);

			if ($error)
				self::setHandlerError($result, $eventName);
			unset($error);
		}
		unset($handlerList);

		self::enableEvents();

		return $result;
	}

	private static function handlerOnBeforeDelete(Catalog\Model\Event $event, $eventName)
	{
		$result = new Catalog\Model\EventResult();
		if (!self::allowedEvents())
			return $result;

		self::disableEvents();

		$handlerList = self::getHandlerList('catalog', $eventName);
		if (!empty($handlerList))
		{
			$error = false;
			$id = $event->getParameter('id');

			foreach ($handlerList as $handler)
			{
				if (ExecuteModuleEventEx($handler, [$id]) === false)
				{
					$error = true;
					break;
				}
			}
			unset($handler);

			if ($error)
				self::setHandlerError($result, $eventName);
			unset($id, $error);
		}

		self::enableEvents();

		return $result;
	}

	private static function handlerOnAfterDelete(Catalog\Model\Event $event, $eventName)
	{
		$result = new Catalog\Model\EventResult();
		if (!self::allowedEvents())
			return $result;

		$success = $event->getParameter('success');
		if (!$success)
			return $result;

		self::disableEvents();

		$handlerList = self::getHandlerList('catalog', $eventName);
		if (!empty($handlerList))
		{
			$data = [
				$event->getParameter('id')
			];

			foreach ($handlerList as $handler)
			{
				ExecuteModuleEventEx($handler, $data);
			}
			unset($handler, $data);
		}
		unset($handlerList);

		self::enableEvents();

		return $result;
	}

	private static function handlerPriceOnAfterModify(Catalog\Model\Event $event, $eventName)
	{
		$result = new Catalog\Model\EventResult();
		if (!self::allowedEvents())
			return $result;

		$success = $event->getParameter('success');
		if (!$success)
			return $result;

		self::disableEvents();

		$handlerList = self::getHandlerList('catalog', $eventName);
		if (!empty($handlerList))
		{
			$fields = $event->getParameter('fields');
			$actions = $event->getParameter('actions');
			$fields['RECALL'] = (isset($actions['OLD_RECOUNT']) && $actions['OLD_RECOUNT'] === true);
			$data = [
				$event->getParameter('id'),
				$fields + $event->getParameter('external_fields')
			];
			unset($actions, $fields);

			foreach ($handlerList as $handler)
			{
				ExecuteModuleEventEx($handler, $data);
			}
			unset($handler, $data);
		}
		unset($handlerList);

		self::enableEvents();

		return $result;
	}

	private static function setHandlerError(Catalog\Model\EventResult $result, $eventName)
	{
		global $APPLICATION;

		$oldException = $APPLICATION->GetException();
		if (is_object($oldException))
		{
			$result->addError(new Main\Entity\EntityError(
				$oldException->GetString()
			));
		}
		else
		{
			$result->addError(new Main\Entity\EntityError(
				Loc::getMessage(
					'BX_CATALOG_EVENT_COMPATIBILITY_ERR_UNKNOWN',
					['#EVENT#' => $eventName]
				)
			));
		}
		unset($oldException);
	}

	private static function fillResultData(
		Catalog\Model\EventResult $result,
		$entity,
		array $fields,
		array $externalFields,
		array $handlerFields
	)
	{
		$unsetFields = [];
		$modifyFields = [];
		$unsetExternalFields = [];
		$modifyExternalFields = [];

		if (!isset(self::$entityClass[$entity]))
		{
			return;
		}
		if (!isset(self::$whiteList[$entity]))
		{
			/** @var Catalog\Model\Entity $className */
			$className = self::$entityClass[$entity];
			$list = $className::getTabletFieldNames(Catalog\Model\Entity::FIELDS_ALL);
			self::$whiteList[$entity] = (!empty($list)
				? array_fill_keys($list, true)
				: []
			);
		}
		if (empty(self::$whiteList[$entity]))
		{
			return;
		}

		$handlerExternalFields = array_diff_key($handlerFields, self::$whiteList[$entity]);
		if (!empty($handlerExternalFields))
			$handlerFields = array_intersect_key($handlerFields, self::$whiteList[$entity]);

		foreach ($fields as $key => $value)
		{
			if (!array_key_exists($key, $handlerFields))
			{
				$unsetFields[] = $key;
			}
			else
			{
				if (!is_array($value))
				{
					if ($value !== $handlerFields[$key])
						$modifyFields[$key] = $handlerFields[$key];
				}
				unset($handlerFields[$key]);
			}
		}
		if (!empty($handlerFields))
			$modifyFields = $modifyFields + $handlerFields;

		foreach ($externalFields as $key => $value)
		{
			if (!array_key_exists($key, $handlerExternalFields))
			{
				$unsetExternalFields[] = $key;
			}
			else
			{
				//TODO: add array check
				if (!is_array($value))
				{
					if ($value !== $handlerExternalFields[$key])
						$modifyExternalFields[$key] = $handlerExternalFields[$key];
				}
				unset($handlerExternalFields[$key]);
			}
		}
		if (!empty($handlerExternalFields))
			$modifyExternalFields = $modifyExternalFields + $handlerExternalFields;

		if (!empty($unsetFields))
			$result->unsetFields($unsetFields);
		if (!empty($modifyFields))
			$result->modifyFields($modifyFields);
		if (!empty($unsetExternalFields))
			$result->unsetExternalFields($unsetExternalFields);
		if (!empty($modifyExternalFields))
			$result->modifyExternalFields($modifyExternalFields);
		unset($modifyFields, $unsetFields);
	}
}