<?php

namespace Bitrix\Sale\Rest;

use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Rest\RestException;
use Bitrix\Sale\EventActions;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Rest\Synchronization\LoggerDiag;
use Bitrix\Sale\Rest\Synchronization\Manager;
use Bitrix\Sale\Rest\Synchronization\Synchronizer;
use Bitrix\Rest\Sqs;

class RestManager
{

//RegisterModuleDependences("rest", "onRestGetModule", "sale", "\\Bitrix\\Sale\\Rest\\RestManager", "onRestGetModule");
//RegisterModuleDependences("rest", "OnRestServiceBuildDescription", "sale", "\\Bitrix\\Sale\\Rest\\RestManager", "onRestServiceBuildDescription");
///rest/event.bind.json?auth=423f8e5b0000cdb90000cdb8000000010000030eb629c718430b3c900e901aa414b84c&auth_type=0&event=OnSaleOrderSaved&handler=http://evgenik.office.bitrix.ru/handler/
///rest/event.bind.json?auth=423f8e5b0000cdb90000cdb8000000010000030eb629c718430b3c900e901aa414b84c&auth_type=0&event=OnSaleBeforeOrderDelete&handler=http://evgenik.office.bitrix.ru/handler/

//	public static function onRestGetModule()
//	{
//		return ['MODULE_ID' => 'sale'];
//	}

	public static function onRestServiceBuildDescription()
	{
		Loader::includeModule('sale');

		return [
			'sale' => [
				\CRestUtil::EVENTS => [
					'OnSaleOrderSaved' => [
						'sale',
						'OnSaleOrderSaved',
						[
							RestManager::class,
							'processEvent',
						],
						[
							'category' => Sqs::CATEGORY_CRM,
						],
					],
					'OnSaleBeforeOrderDelete' => [
						'sale',
						'OnSaleBeforeOrderDelete',
						[
							RestManager::class,
							'processEvent',
						],
						[
							'category' => Sqs::CATEGORY_CRM,
						],
					],
					'OnPropertyValueEntitySaved' => [
						'sale',
						'OnSalePropertyValueEntitySaved',
						[
							RestManager::class,
							'processEvent',
						],
						[
							'category' => Sqs::CATEGORY_CRM,
						],
					],
					'OnPaymentEntitySaved' => [
						'sale',
						'OnSalePaymentEntitySaved',
						[
							RestManager::class,
							'processEvent',
						],
						[
							'category' => Sqs::CATEGORY_CRM,
						],
					],
					'OnShipmentEntitySaved' => [
						'sale',
						'OnSaleShipmentEntitySaved',
						[
							RestManager::class,
							'processEvent',
						],
						[
							'category' => Sqs::CATEGORY_CRM,
						],
					],
					'OnOrderEntitySaved' => [
						'sale',
						'OnSaleOrderEntitySaved',
						[
							RestManager::class,
							'processEvent',
						],
						[
							'category' => Sqs::CATEGORY_CRM,
						],
					],
					'OnPropertyValueDeleted' => [
						'sale',
						'OnSalePropertyValueDeleted',
						[
							RestManager::class,
							'processEvent',
						],
						[
							'category' => Sqs::CATEGORY_CRM,
						],
					],
					'OnPaymentDeleted' => [
						'sale',
						'OnSalePaymentDeleted',
						[
							RestManager::class,
							'processEvent',
						],
						[
							'category' => Sqs::CATEGORY_CRM,
						],
					],
					'OnShipmentDeleted' => [
						'sale',
						'OnSaleShipmentDeleted',
						[
							RestManager::class,
							'processEvent',
						],
						[
							'category' => Sqs::CATEGORY_CRM,
						],
					],
					'OnOrderDeleted' => [
						'sale',
						'OnSaleOrderDeleted',
						[
							RestManager::class,
							'processEvent',
						],
						[
							'category' => Sqs::CATEGORY_CRM,
						],
					],
				]
			]
		];
	}

	public static function processEvent(array $params, array $handlerFields)
	{
		/** @var Event $event */
		$event = $params[0];
		$eventParameters = $event->getParameters();
		/** @var Entity|null $entity */
		$entity = $eventParameters['ENTITY'] ?? null;
		$eventName = $handlerFields['EVENT_NAME'];
		$eventHandler = $handlerFields['EVENT_HANDLER'];

		$instance = Manager::getInstance();
		$action = $instance->getAction();

		LoggerDiag::addMessage(
			'processEvent',
			var_export(
				[
					'processEvent [process-01]' => [
						'eventName' => $eventName,
						'action' => $action,
					],
				],
				true
			)
		);

		switch(mb_strtolower($eventName))
		{
			case 'onsaleordersaved':
				if (in_array($action, [Manager::ACTION_IMPORT, Manager::ACTION_DELETED], true))
				{
					throw new RestException("Event stopped");
				}
				elseif ($instance->isExecutedHandler($eventHandler))
				{
					throw new RestException("Event stopped");
				}

				if ($entity->getId() <= 0)
				{
					throw new RestException("Could not find entity ID in fields of event \"{$eventName}\"");
				}

				$parameters = [
					'FIELDS' => [
						'ID' => $entity->getId(),
						'XML_ID' => $entity->getField('XML_ID'),
						'ACTION' => Synchronizer::MODE_SAVE,
					]
				];

				LoggerDiag::addMessage(
					mb_strtolower($eventName),
					var_export(
						[
							'processEvent [process-02]' => [
								'parameters' => $parameters,
							],
						],
						true
					)
				);

				$instance->pushHandlerExecuted($eventHandler);

				return $parameters;

			case 'onsalebeforeorderdelete':
				if (in_array($action, [Manager::ACTION_IMPORT, Manager::ACTION_DELETED], true))
				{
					throw new RestException("Event stopped");
				}

				$instance->setAction(Manager::ACTION_DELETED);

				$parameters = [
					'FIELDS' => [
						'ID' => $entity->getId(),
						'XML_ID' => $entity->getField('XML_ID'),
						'ACTION' => Synchronizer::MODE_DELETE,
					]
				];

				LoggerDiag::addMessage(
					mb_strtolower($eventName),
					var_export(
						[
							'processEvent [process-03]' => [
								'parameters' => $parameters,
							],
						],
						true
					)
				);

				return $parameters;

			case 'onpropertyvalueentitysaved':
			case 'onpaymententitysaved':
			case 'onshipmententitysaved':
			case 'onorderentitysaved':
			case 'onpropertyvaluedeleted':
			case 'onpaymentdeleted':
			case 'onshipmentdeleted':
			case 'onorderdeleted':
				$entityId = 0;
				if ($entity !== null)
				{
					$entityId = $entity->getId();
				}
				elseif (isset($event->getParameters()['VALUES']))
				{
					$entityId = $event->getParameters()['VALUES']['ID'];
				}

				$parameters = ['FIELDS' => ['ID' => $entityId]];

				LoggerDiag::addMessage(
					mb_strtolower($eventName),
					var_export(
						[
							'processEvent [process-04]' => [
								'parameters' => $parameters,
							],
						],
						true
					)
				);

				return $parameters;

			default:
				throw new RestException("The Event \"{$eventName}\" is not supported in current context");
		}
	}
}
