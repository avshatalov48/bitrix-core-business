<?php


namespace Bitrix\Sale\Rest\Synchronization;


use Bitrix\Main\Context;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Error;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\EntityMarker;
use Bitrix\Sale\Order;
use Bitrix\Sale\OrderHistory;
use Bitrix\Sale\OrderTable;
use Bitrix\Sale\Registry;
use Bitrix\Sale\Rest\Externalizer;
use Bitrix\Sale\Rest\Internalizer;
use Bitrix\Sale\Result;

/**
 * Class Synchronizer
 * @package Bitrix\Sale\Rest\Synchronization
 * @intrnal
 */
final class Synchronizer
{
	protected $request;

	const SYNCHRONIZER_MARKER_ERROR = 'SYNCHRONIZER_ERROR';
	const MODE_SAVE = 'save';
	const MODE_DELETE = 'delete';

	public function __construct()
	{
		$this->request = Context::getCurrent()->getRequest();
	}

	public function incomingReplication($id='', $xmlId='', $action='', $accessToken='')
	{
		$result = new Result();
		$instance = Manager::getInstance();

		if($instance->isActive() == false)
			return $result;

		//region debug
		if($id === '')
			$id = $this->request->getPost('data')['FIELDS']['ID'];
		if($xmlId === '')
			$xmlId = $this->request->getPost('data')['FIELDS']['XML_ID'];
		if($action === '')
			$action = $this->request->getPost('data')['FIELDS']['ACTION'];
		if($accessToken === '')
			$accessToken = $this->request->getPost('auth')['access_token'];
		//endregion

		LoggerDiag::addMessage('SYNCHRONIZER_INCOMING_REPLICATION_INCOMING_REQUEST', var_export(['id'=>$id, 'xmlId'=>$xmlId,' action'=>$action, 'accessToken'=>$accessToken], true));
		LoggerDiag::addMessage('SYNCHRONIZER_INCOMING_REPLICATION_FLAGS', var_export(['action'=>$instance->getAction()], true));

		$r = $instance->getClient()->checkAccessToken($accessToken);
		if($r->isSuccess())
		{
			LoggerDiag::addMessage('SYNCHRONIZER_INCOMING_REPLICATION_CHECK_ACCESS_TOKEN_SUCCESS', var_export(['access_token'=>$accessToken], true));

			$instance->setAccessToken($accessToken);

			if($action == self::MODE_DELETE)
			{
				//TODO: обработать ситуацию при которой пришел запрос на удаленеи заказа из внешней сиистемы, но заказ не был удален по причине ошибки

				LoggerDiag::addMessage('SYNCHRONIZER_INCOMING_REPLICATION_FILTER_BY_XML_ID', var_export(['xmlId'=>$xmlId], true));

				$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
				/** @var Order $orderClass */
				$orderClass = $registry->getOrderClassName();

				/** @var Order[] $orders */
				$orders = $orderClass::loadByFilter(['filter'=>['XML_ID'=>$xmlId]]);
				if(count($orders)>0)
				{
					LoggerDiag::addMessage('SYNCHRONIZER_INCOMING_REPLICATION_FILTER_BY_XML_ID_SUCCESS', var_export($orders, true));

					$controllerOrder = new \Bitrix\Sale\Controller\Order();
					LoggerDiag::addMessage('SYNCHRONIZER_INCOMING_REPLICATION_DELETED_BY_INTERNAL_ID', var_export($orders[0], true));

					/** @var Result $r */
					$controllerOrder->importDeleteAction($orders[0]);
					if(count($controllerOrder->getErrors())>0)
					{
						$r->addErrors($controllerOrder->getErrors());
						LoggerDiag::addMessage('SYNCHRONIZER_INCOMING_REPLICATION_DELETED_BY_INTERNAL_ID_ERROR');
					}
					else
						LoggerDiag::addMessage('SYNCHRONIZER_INCOMING_REPLICATION_DELETED_BY_INTERNAL_ID_SUCCESS');
				}
				else
				{
					$r->addError(new Error('Order not found', 'ORDER_NOT_FOUND'));
					LoggerDiag::addMessage('SYNCHRONIZER_INCOMING_REPLICATION_FILTER_BY_XML_ID_ERROR');
				}
			}
			elseif($action == self::MODE_SAVE)
			{
				LoggerDiag::addMessage('SYNCHRONIZER_INCOMING_REPLICATION_REQUEST_EXTERNAL_ENTITY_BY_ID', var_export(['id'=>$id], true));

				$r = $instance->getClient()->call(
					'sale.order.get',
					[
						'auth'=>$accessToken,
						'id'=>$id
					]
				);
				if($r->isSuccess())
				{
					LoggerDiag::addMessage('SYNCHRONIZER_INCOMING_REPLICATION_REQUEST_EXTERNAL_ENTITY_BY_ID_SUCCESS', var_export(['sale.order.get'=>$r->getData()['DATA']['result']], true));

					$r = $this->import($r->getData()['DATA']['result']);
					if($r->isSuccess())
					{
						$result->setData(['DATA'=>$r->getData()['DATA']]);

						$orderId = isset($r->getData()['DATA']['ORDER']['ID'])?$r->getData()['DATA']['ORDER']['ID']:0;
						$siteId = isset($r->getData()['DATA']['ORDER']['LID'])?$r->getData()['DATA']['ORDER']['LID']:'';

						self::addMarkedTimelineExternalSystem($id,
							[
								'direction'=>'incoming',
								'type'=>'success',
								'orderId'=>$orderId,
								'siteId'=>$siteId
							]
						);

						self::addActionOrderHistory(
							[
								'orderId'=>$orderId,
								'typeName'=>'ORDER_SYNCHRONIZATION_IMPORT',
								'fields'=>['EXTERNAL_ORDER_ID'=>$id]
							]
						);
					}
					else
					{
						self::addMarkedTimelineExternalSystem($id,
							[
								'direction'=>'incoming',
								'type'=>'failed',
								'errorMessage'=>$r->getErrorMessages()[0]
							]
						);
					}
				}
				else
					LoggerDiag::addMessage('SYNCHRONIZER_INCOMING_REPLICATION_REQUEST_EXTERNAL_ENTITY_BY_ID_ERROR');
			}
			else
			{
				$r = new Result();
				$r->addError(new Error('Action udefined'));
				LoggerDiag::addMessage('SYNCHRONIZER_INCOMING_REPLICATION_UNKNOWN_COMMAND', var_export(['action'=>$action], true));
			}
		}
		else
			LoggerDiag::addMessage('SYNCHRONIZER_INCOMING_REPLICATION_CHECK_ACCESS_TOKEN_ERROR');

		if(!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			LoggerDiag::addMessage('SYNCHRONIZER_INCOMING_REPLICATION_RESULT_ERROR', var_export([
				'parameters'=>[
					'action'=>$action,
					'id'=>$id,
					'xmlId'=>$xmlId,
					'auth'=>$accessToken,
					'errors'=>$result->getErrorMessages()
				]], true));

			//return new EventResult( EventResult::ERROR, ResultError::create(current($orderController->getErrors())), 'sale');
		}
		else
		{
			LoggerDiag::addMessage('SYNCHRONIZER_INCOMING_REPLICATION_RESULT_SUCCESS', var_export([
				'id'=>$id,
				'xmlId'=>$xmlId,
				'action'=>$action,
				'result'=>$result->getData()['DATA']
			], true));
		}

		//return new EventResult( EventResult::SUCCESS, null, 'sale');
		return $result;
	}

	public function onSaleOrderSaved(Order $order)
	{
		$instance = Manager::getInstance();

		//TODO: блокировка исходящих rest-вызовов
		if($instance->getAction() == Manager::ACTION_DELETED)
			return new EventResult( EventResult::SUCCESS, null, 'sale');

		if($instance->getAction() == Manager::ACTION_IMPORT)
			return new EventResult( EventResult::SUCCESS, null, 'sale');

		self::outcomingReplication($order, self::MODE_SAVE);

		return true;
	}

	public function onSaleBeforeOrderDelete(Order $order)
	{
		//TODO: huck для блокировки исходящего события. Блокируется действием - deleted т.к. запрос исходящий
		// и удаление происходит на текущем хосте. Можно переделять на установку updated_1c=Y и проверку его сосотояния,
		// но на данный момент механизм не рабочий
		$instance = Manager::getInstance();
		$instance->setAction(Manager::ACTION_DELETED);

		self::outcomingReplication($order, self::MODE_DELETE);
		return true;
	}

	public static function outcomingReplication(Order $order, $mode)
	{
		$result = new Result();

		$instance = Manager::getInstance();

		if($instance->isActive() == false)
			return $result;

		LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_OUTCOMING_REQUEST', var_export(['id'=>$order->getId(), 'mode'=>$mode], true));
		LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_FLAGS', var_export(['action'=>$instance->getAction()], true));

		$synchronizer = new self();
		$r = $synchronizer->refreshToken();
		if($r->isSuccess())
		{
			LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_REFRESH_ACCESS_TOKEN_SUCCESS', var_export(['auth'=>$instance->getAccessToken()], true));

			if($mode == self::MODE_DELETE)
			{
				$xmlId = $order->getField('XML_ID')<>'' ? $order->getField('XML_ID'):'';

				LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_REQUEST_EXTERNAL_ENTITY_BY_XML_ID', var_export(['xmlId'=>$xmlId], true));

				$r = $instance->getClient()->call(
					'sale.order.list',
					[
						'auth'=>$instance->getAccessToken(),
						'select'=>[],
						'filter'=>[
							'xmlId'=>$xmlId
						]
					]
				);

				if($r->isSuccess())
				{
					LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_REQUEST_EXTERNAL_ENTITY_BY_XML_ID_SUCCESS', var_export(['sale.order.list'=>$r->getData()['DATA']], true));

					if(count($r->getData()['DATA']['result']['orders'])>0)
					{
						$fields = $r->getData()['DATA']['result']['orders'][0];
						if($fields['id']>0)
						{
							LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_REQUEST_DELETED_EXTERNAL_ENTITY_BY_ID', var_export(['id'=>$fields['id']], true));
							$r = $instance->getClient()->call(
								'sale.order.importdelete',
								[
									'auth'=>$instance->getAccessToken(),
									'id'=>$fields['id']
								]
							);
							if($r->isSuccess())
							{
								LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_REQUEST_DELETED_EXTERNAL_ENTITY_BY_ID_SUCCESS', var_export(['sale.order.importdelete.result'=>$r->getData()['DATA']], true));

								if($r->getData()['DATA']['result']<>1)
								{
									LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_REQUEST_RESULT_DELETED_EXTERNAL_ENTITY_BY_ID_ERROR');
									$result->addError(new Error('Error delete - '.$fields['id'], 'ERROR_DELETED_EXTERNAL_ORDER'));
								}
								else
									LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_REQUEST_RESULT_DELETED_EXTERNAL_ENTITY_BY_ID_SUCCESS');
							}
							else
								LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_REQUEST_DELETED_EXTERNAL_ENTITY_BY_ID_ERROR');
						}
						else
							LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_REQUEST_PROCESS_GET_ID');
					}
					else
						LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_REQUEST_LIST_EXTERNAL_ENTITIES_BY_XML_ID_ERROR');
				}
				else
					LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_REQUEST_ERROR');
			}
			elseif($mode == self::MODE_SAVE)
			{
				$controllerOrder = new \Bitrix\Sale\Controller\Order();

				$r = $synchronizer->requesPrepareData(
					$controllerOrder->getAction($order)
				);
				if($r->isSuccess())
				{
					LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_GET_INTERNAL_FIELDS', var_export(['fields'=>$r->getData()['DATA']], true));
					$client = $instance->getClient();
					$r = $client->call(
						'sale.order.import',
						[
							'auth'=>$instance->getAccessToken(),
							'fields'=>$r->getData()['DATA']
						]
					);

					if($r->isSuccess())
					{
						// TODO: необходимо для БУС фиксировать изменения по заказу, включая отправку во внешнии сиситемы в исории заказа
						$externalOrderId = $r->getData()['DATA']['result']['order']['id'];
						if(intval($externalOrderId)>0)
						{
							self::addMarkedTimelineExternalSystem($externalOrderId,
								[
									'direction'=>'outcoming',
									'type'=>'success',
									'orderId'=>$order->getId(),
									'siteId'=>$order->getSiteId()
								]
							);
						}

						LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_GET_INTERNAL_FIELDS_SUCCESS', var_export(['sale.order.import.result'=>$r->getData()['DATA']], true));
					}
					else
						LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_GET_INTERNAL_FIELDS_ERROR');
				}
				else
					LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_REQUEST_PREPARE_DATA_FIELDS_ERROR');
			}
			else
			{
				$r = new Result();
				$r->addError(new Error('Mode udefined'));
				LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_UNKNOWN_COMMAND', var_export(['mode'=>$mode], true));
			}

			if(!$r->isSuccess())
				$result->addErrors($r->getErrors());
		}
		else
		{
			LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_REFRESH_ACCESS_TOKEN_ERROR', var_export($r->getErrorMessages(), true));
			$result->addError(new Error('refresh token error'));
		}

		if($result->isSuccess())
		{
			self::addActionOrderHistory(
				[
					'order'=>$order,
					'orderId'=>$order->getId(),
					'typeName'=>'ORDER_SYNCHRONIZATION_EXPORT'
				]
			);

			EntityMarker::deleteByFilter([
				'CODE'=>self::SYNCHRONIZER_MARKER_ERROR,
				'ORDER_ID'=>$order->getId(),
				'ENTITY_ID'=>$order->getId()
			]);

			OrderTable::update($order->getId(), ['MARKED'=>'N']);

			LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_OUTCOMING_REQUEST_SUCCESS', var_export([
				'orderId'=>$order->getId(),
				'mode'=>$mode,
				'result'=>$r->getData()['DATA']
			], true));
		}
		else
		{
			self::addActionOrderHistory(
				[
					'order'=>$order,
					'orderId'=>$order->getId(),
					'typeName'=>'ORDER_SYNCHRONIZATION_EXPORT_ERROR',
					'fields'=>[
						'ERROR' => $result->getErrorMessages()
					]
				]
			);

			if($instance->isMarked())
			{
				$result->addWarning(new Error(Loc::getMessage('SYNCH_OUTCOMING_REPLICATION_OUTCOMING_ORDER_ERROR', ['#ERROR_MESSAGE#'=>$result->getErrorMessages()[0]]), self::SYNCHRONIZER_MARKER_ERROR));
				EntityMarker::addMarker($order, $order, $result);
				$r = EntityMarker::saveMarkers();

				if($r->isSuccess())
					OrderTable::update($order->getId(), ['MARKED'=>'Y']);
			}

			LoggerDiag::addMessage('SYNCHRONIZER_OUTCOMING_REPLICATION_OUTCOMING_REQUEST_ERROR', var_export($result->getErrorMessages(), true));
		}
		return $result;
	}

	public function refreshToken()
	{
		$instance = Manager::getInstance();
		/** @var Client $client */
		$client = $instance->getClient();
		$r = $client->refreshToken($instance->getRefreshToken());
		if($r->isSuccess())
		{
			$data = $r->getData()['DATA'];

			$instance->setAccessToken($data['access_token']);
			$instance->setRefreshToken($data['refresh_token']);
		}

		return $r;
	}

	public function requesPrepareData(array $fields)
	{
		$result = new Result();
		$manager = new Manager();

		// ключ tradeBindings не выгружаем т.к. при его выгрузке и пустом массиве все связки в внешней сиситеме будут удалены.
		// наиболее частый случий соответствие между внешней сиситемой и внутренней выставляться не будет.
		//if(count($fields['ORDER']['TRADE_BINDINGS'])>0)
		//{
		//	foreach($fields['ORDER']['TRADE_BINDINGS'] as $k=>$item)
		//	{
		//		$fields['ORDER']['TRADE_BINDINGS'][$k]['FIELDS'] = array_intersect_key($item['FIELDS'], $restTradeBinding->getFieldsForShow());
		//	}
		//}

		// для решения зазада - 'внешние заказы в Б24 должны иметь свой источник'
		// переписываем источник заказа из настройки синхронизции на источник внешней системы
		// метод используется только для БУС.
		if($manager->getTradePlatformsXmlId($fields['ORDER']['LID'])<>'')
		{
			$fields['ORDER']['TRADE_BINDINGS'][] = [
				'XML_ID'=>$fields['ORDER']['ID'],
				'EXTERNAL_ORDER_ID'=>$fields['ORDER']['ID'],
				'TRADING_PLATFORM_XML_ID'=>$manager->getTradePlatformsXmlId($fields['ORDER']['LID'])
			];
		}

		$externalizer = new Externalizer('import', [], new \Bitrix\Sale\Controller\Order(), $fields, Controller::SCOPE_REST);
		$fields = $externalizer->process()->getData()['data'];

		//$fields = $coverter->process($fields);

		$respons = new \Bitrix\Main\Engine\Response\Json($fields);
		$fields = \Bitrix\Main\Web\Json::decode($respons->getContent());

		if(is_array($fields) && count($fields)>0)
		{
			$result->setData(['DATA'=>$fields]);
		}
		else
		{
			$result->addError(new Error('Reques prepare data empty respons'));
		}

		return $result;
	}

	protected function import(array $fields)
	{
		$r = new Result();
		$errors=[];

		$internalizer = new Internalizer('import', ['fields'=>$fields], new \Bitrix\Sale\Controller\Order(), [], Controller::SCOPE_REST);
		$process = $internalizer->process();

		if($process->isSuccess())
		{
			$fields = $process->getData()['data']['fields'];

			LoggerDiag::addMessage('SYNCHRONIZER_IMPORT_FIELDS', var_export($fields, true));

			$orderController = new \Bitrix\Sale\Controller\Order();

			try
			{
				$result = $orderController->importAction($fields);
			}
			catch (\Bitrix\Main\SystemException $e)
			{
				$errors[] = new Error('SYNCH_OUTCOMING_REPLICATION_IMPORT_INTERNAL_ERROR');
				LoggerDiag::addMessage('SYNCHRONIZER_IMPORT_FIELDS_EXCEPTION', var_export($e->getTraceAsString(), true));
			}

			if(count($orderController->getErrors())>0)
			{
				$errors = $orderController->getErrors();
			}
		}
		else
		{
			$errors = $r->getErrors();
		}

		if(count($errors)>0)
		{
			$r->addErrors($errors);
			LoggerDiag::addMessage('SYNCHRONIZER_IMPORT_FIELDS_ERROR', var_export($errors, true));
		}
		else
		{
			$r->setData(['DATA'=>$result]);
			LoggerDiag::addMessage('SYNCHRONIZER_IMPORT_FIELDS_SUCCESS', var_export($result, true));
		}

		return $r;
	}

	protected static function addMarkedTimelineExternalSystem($externalOrderId, array $params)
	{
		$typeId = 0;
		$message = '';
		$siteName = '';

		$sites = self::getSites();
		$instance = Manager::getInstance();

		if($params['type'] == 'success')
			$typeId = 2;
		elseif ($params['type']== 'failed')
			$typeId = 5;

		if(isset($params['siteId']) && $params['siteId']<>'')
			$siteName = isset($sites[$params['siteId']])?$sites[$params['siteId']]['NAME']:'';
		if($siteName == '')
			$siteName =  $sites[SITE_ID]['NAME'];

		if($params['direction'] == 'incoming')
		{
			if($params['type'] == 'success')
			{
				$message = Loc::getMessage('SYNCH_OUTCOMING_REPLICATION_EXTERNAL_SYSTEM_MESS_EXPORT_ORDER_SUCCESS', [
					'#EXTERNAL_SYSTEM#'=>($siteName<>''?' ('.$siteName.')':''),
					'#ORDER_ID#'=>($params['orderId']>0?$params['orderId']:'')
				]);
			}
			else
			{
				$message = Loc::getMessage('SYNCH_OUTCOMING_REPLICATION_EXTERNAL_SYSTEM_MESS_EXPORT_ORDER_ERROR', [
					'#EXTERNAL_SYSTEM#'=>($siteName<>''?' ('.$siteName.')':''),
					'#ERROR_MESSAGE#'=>($params['errorMessage']<>''?$params['errorMessage']:'')
				]);
			}
		}
		if($params['direction'] == 'outcoming')
		{
			$message = Loc::getMessage('SYNCH_OUTCOMING_REPLICATION_EXTERNAL_SYSTEM_MESS_IMPORT_EXTERNAL_ORDER', [
				'#EXTERNAL_SYSTEM#'=>($siteName<>''?' ('.$siteName.')':''),
				'#ORDER_ID#'=>($params['orderId']>0?$params['orderId']:'')
			]);
		}

		$instance->getClient()->call(
			'sale.synchronizer.addTimelineAfterOrderModify',
			[
				'auth'=>$instance->getAccessToken(),
				'orderId'=>$externalOrderId,
				'params'=>[
					'type'=>$typeId,
					'message'=>$message
				]
			]
		);
	}

	/**
	 * @param array $params
	 * запись в историю осуществляется только для БУС. Для Б24 изменения по заказу пишутся в тайм лайн
	 */
	private static function addActionOrderHistory(array $params)
	{
		$params['order'] = (isset($params['order']) && ($params['order'] instanceof Order)) ? $params['order']:null;
		$params['fields'] = isset($params['fields']) ? $params['fields']:[];

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		/** @var OrderHistory $orderHistory */
		$orderHistory = $registry->getOrderHistoryClassName();

		$orderHistory::addAction(
			'ORDER',
			$params['orderId'],
			$params['typeName'],
			$params['orderId'],
			$params['order'],
			$params['fields']
		);
		$orderHistory::collectEntityFields('ORDER', $params['orderId']);
	}

	protected static function getSites()
	{
		$result=[];
		$r = \CSite::GetList();
		while ($row = $r->fetch())
			$result[$row['ID']]=$row;

		return $result;
	}
}