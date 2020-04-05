<?php


namespace Bitrix\Sale\Rest;


use Bitrix\Main\Error;
use Bitrix\Sale\Controller\Controller;
use Bitrix\Sale\Rest\Synchronization\LoggerDiag;
use Bitrix\Sale\Result;

class Internalizer extends ModificationFieldsBase
{
	public function __construct($name, $arguments, $controller, array $data = [], $scope = '')
	{
		$this->format = self::TO_WHITE_LIST | self::TO_SNAKE | self::CHECK_REQUIRED;

		parent::__construct($name, $arguments, $controller, $data, $scope);
	}

	/**
	 * @return Result
	 */
	public function process()
	{
		$r = new Result();
		/*
		 * Basket
		 * \Bitrix\Sale\Controller\Basket::modifyAction
		 * order%5Bid%5D=85&basket%5Bitems%5D%5B0%5D%5B0%5D%5Bquantity%5D=1&basket%5Bitems%5D%5B0%5D%5B0%5D%5BproductId%5D=319
		 * \Bitrix\Sale\Controller\Basket::addAction
		 * order%5Bid%5D=85&basket%5Bitems%5D%5B0%5D%5BproductId%5D=227&basket%5Bitems%5D%5B0%5D%5Bquantity%5D=2&basket%5Bitems%5D%5B1%5D%5BproductId%5D=69&basket%5Bitems%5D%5B1%5D%5Bquantity%5D=1
		 * \Bitrix\Sale\Controller\Basket::updateAction
		 * quantity=1&productId=227
		 *
		 * Order
		 * \Bitrix\Sale\Controller\Order::tryModifyAction
		 * \Bitrix\Sale\Controller\Order::modifyAction
		 * order%5BsiteId%5D=s1&order%5BuserId%5D=1&order%5BorderTopic%5D=&order%5BresponsibleId%5D=1&order%5BuserDescription%5D=&order%5BpersonTypeId%5D=1&properties%5B0%5D%5Bid%5D=1&properties%5B0%5D%5Bvalue%5D=%D2%E5%F1%F2&properties%5B1%5D%5Bid%5D=2&properties%5B1%5D%5Bvalue%5D=bx%40bx.bx&properties%5B2%5D%5Bid%5D=3&properties%5B2%5D%5Bvalue%5D=77777777777&properties%5B3%5D%5Bid%5D=4&properties%5B3%5D%5Bvalue%5D=101000&properties%5B4%5D%5Bid%5D=6&properties%5B4%5D%5Bvalue%5D=0000073738&properties%5B5%5D%5Bid%5D=7&properties%5B5%5D%5Bvalue%5D=test&properties%5B6%5D%5Bid%5D=20&properties%5B7%5D%5Bid%5D=21&properties%5B7%5D%5Bvalue%5D=code1&basketItems%5B0%5D%5Bid%5D=n1&basketItems%5B0%5D%5Bquantity%5D=1&basketItems%5B0%5D%5BproductId%5D=59&basketItems%5B1%5D%5Bid%5D=n2&basketItems%5B1%5D%5Bquantity%5D=2&basketItems%5B1%5D%5BproductId%5D=227&payment%5B0%5D%5Bsum%5D=100&payment%5B0%5D%5Bpaid%5D=N&payment%5B0%5D%5Bcomments%5D=%CA%EE%EC%EC%E5%ED%F2%E0%F0%E8%E9&payment%5B0%5D%5BpaySystemId%5D=3&payment%5B0%5D%5BresponsibleId%5D=1&payment%5B0%5D%5BpayVoucherNum%5D=000000001&payment%5B0%5D%5BpayVoucherDate%5D=&shipment%5B0%5D%5Bdeducted%5D=N&shipment%5B0%5D%5BdeliveryDocNum%5D=000000001&shipment%5B0%5D%5BtrackingNumber%5D=0000000002&shipment%5B0%5D%5Bcomments%5D=%CA%EE%EC%EC%E5%ED%F2%E0%F0%E8%E9&shipment%5B0%5D%5BstatusId%5D=DN&shipment%5B0%5D%5BresponsibleId%5D=1&shipment%5B0%5D%5BdeliveryDocDate%5D=&shipment%5B0%5D%5BdeliveryId%5D=1&shipment%5B0%5D%5BpriceDelivery%5D=500&shipment%5B0%5D%5BallowDelivery%5D=Y&shipment%5B0%5D%5BbasePriceDelivery%5D=500&shipment%5B0%5D%5BbasketItems%5D%5B0%5D%5Bid%5D=n1&shipment%5B0%5D%5BbasketItems%5D%5B0%5D%5Bquantity%5D=1&shipment%5B0%5D%5BbasketItems%5D%5B1%5D%5Bid%5D=n2&shipment%5B0%5D%5BbasketItems%5D%5B1%5D%5Bquantity%5D=1
		 * \Bitrix\Sale\Controller\Order::addAction
		 * \Bitrix\Sale\Controller\Order::tryAddAction
		 * order%5BsiteId%5D=s1&order%5BuserId%5D=1&order%5BorderTopic%5D=&order%5BresponsibleId%5D=1&order%5BuserDescription%5D=&order%5BpersonTypeId%5D=1
		 *
		 * Payment
		 * \Bitrix\Sale\Controller\Payment::addAction
		 * order%5Bid%5D=70&payment%5Bsum%5D=200&payment%5Bpaid%5D=N&payment%5Bcomments%5D=%CA%EE%EC%EC%E5%ED%F2%E0%F0%E8%E9&payment%5BpaySystemId%5D=3&payment%5BresponsibleId%5D=1&payment%5BpayVoucherNum%5D=000000001&payment%5BpayVoucherDate%5D=
		 * \Bitrix\Sale\Controller\Payment::modifyAction
		 * order%5Bid%5D=70&payment%5B0%5D%5Bid%5D=139&payment%5B0%5D%5Bsum%5D=500&payment%5B0%5D%5Bpaid%5D=N&payment%5B0%5D%5Bcomments%5D=%CA%EE%EC%EC%E5%ED%F2%E0%F0%E8%E92222222222222222222&payment%5B0%5D%5BpaySystemId%5D=3&payment%5B0%5D%5BresponsibleId%5D=1&payment%5B0%5D%5BpayVoucherNum%5D=000000001&payment%5B0%5D%5BpayVoucherDate%5D=&payment%5B1%5D%5Bsum%5D=200&payment%5B1%5D%5Bpaid%5D=N&payment%5B1%5D%5Bcomments%5D=%CA%EE%EC%EC%E5%ED%F2%E0%F0%E8%E9&payment%5B1%5D%5BpaySystemId%5D=3&payment%5B1%5D%5BresponsibleId%5D=1&payment%5B1%5D%5BpayVoucherNum%5D=000000001&payment%5B1%5D%5BpayVoucherDate%5D=
		 *
		 * PersonType
		 * \Bitrix\Sale\Controller\PersonType::modifyAction
		 * personType%5Bname%5D=%D4%E8%E7%E8%F7%E5%F1%EA%EE%E5+%EB%E8%F6%EE&personType%5Blid%5D=s1&busvalDomain%5BdomainType%5D=I
		 *
		 * Property
		 * \Bitrix\Sale\Controller\Property::updateAction
		 * id=34&personTypeId=1&propsGroupId=8&name=test+ENUM&code=test1&active=Y&util=Y&userProps=Y&isFiltered=Y&sort=100&description=%CE%EF%E8%F1%E0%ED%E8%E5+%F1%E2%EE%E9%F1%F2%E2%E0&type=ENUM&required=N&multiple=Y&defaultValue%5B0%5D=code1&defaultValue%5B1%5D=code2&isProfileName=N&isPayer=N&isEmail=N&isPhone=N&isZip=N&isAddress=N&isLocation=N&inputFieldLocation=0&isLocation4tax=N&multielement=N&size=&variants%5B0%5D%5Bid%5D=36&variants%5B0%5D%5Bvalue%5D=code1&variants%5B0%5D%5Bname%5D=value1&variants%5B0%5D%5Bsort%5D=100&variants%5B1%5D%5Bid%5D=47&variants%5B1%5D%5Bvalue%5D=code2&variants%5B1%5D%5Bname%5D=value2&variants%5B1%5D%5Bsort%5D=100&variants%5B2%5D%5Bid%5D=48&variants%5B2%5D%5Bvalue%5D=code3&variants%5B2%5D%5Bname%5D=value3&variants%5B2%5D%5Bsort%5D=100&variants%5B3%5D%5Bid%5D=49&variants%5B3%5D%5Bvalue%5D=code4&variants%5B3%5D%5Bname%5D=value4&variants%5B3%5D%5Bsort%5D=100&variants%5B4%5D%5Bid%5D=56&variants%5B4%5D%5Bvalue%5D=code5&variants%5B4%5D%5Bname%5D=value5&variants%5B4%5D%5Bsort%5D=100&relations%5Bp%5D%5B0%5D=3&relations%5Bp%5D%5B1%5D=4&relations%5Bp%5D%5B2%5D=5&relations%5Bd%5D%5B0%5D=1
		 * \Bitrix\Sale\Controller\Property::addAction
		 * personTypeId=1&propsGroupId=8&name=test+ENUM&code=test1&active=Y&util=Y&userProps=Y&isFiltered=Y&sort=100&description=&type=ENUM&required=N&multiple=N&defaultValue=&isProfileName=N&isPayer=N&isEmail=N&isPhone=N&isZip=N&isAddress=N&isLocation=N&inputFieldLocation=0&isLocation4tax=N&multielement=N&size=&variants%5B0%5D%5Bvalue%5D=code1&variants%5B0%5D%5Bname%5D=value1&variants%5B0%5D%5Bsort%5D=100&variants%5B1%5D%5Bvalue%5D=code2&variants%5B1%5D%5Bname%5D=value2&variants%5B1%5D%5Bsort%5D=100&variants%5B2%5D%5Bvalue%5D=code3&variants%5B2%5D%5Bname%5D=value3&variants%5B2%5D%5Bsort%5D=100&variants%5B3%5D%5Bvalue%5D=code4&variants%5B3%5D%5Bname%5D=value4&variants%5B3%5D%5Bsort%5D=100&variants%5B4%5D%5Bvalue%5D=code5&variants%5B4%5D%5Bname%5D=value5&variants%5B4%5D%5Bsort%5D=100&relations%5Bp%5D%5B0%5D=3&relations%5Bp%5D%5B1%5D=4&relations%5Bd%5D%5B0%5D=1
		 *
		 * PropertyValue
		 * \Bitrix\Sale\Controller\PropertyValue::modifyAction
		 * order%5Bid%5D=71&properties%5B0%5D%5BorderPropsId%5D=2&properties%5B0%5D%5Bvalue%5D=bx%40bx.bx2&properties%5B1%5D%5BorderPropsId%5D=1&properties%5B1%5D%5Bvalue%5D=%D2%E5%F1%F2&properties%5B2%5D%5BorderPropsId%5D=3&properties%5B2%5D%5Bvalue%5D=77777777777&properties%5B3%5D%5BorderPropsId%5D=4&properties%5B3%5D%5Bvalue%5D=101000&properties%5B4%5D%5BorderPropsId%5D=6&properties%5B4%5D%5Bvalue%5D=0000073738&properties%5B5%5D%5BorderPropsId%5D=7&properties%5B5%5D%5Bvalue%5D=test&properties%5B6%5D%5BorderPropsId%5D=20&properties%5B6%5D%5Bvalue%5D%5B0%5D=code1&properties%5B6%5D%5Bvalue%5D%5B1%5D=code2&properties%5B7%5D%5BorderPropsId%5D=21&properties%5B7%5D%5Bvalue%5D=code1
		 *
		 * Shipment
		 * \Bitrix\Sale\Controller\Shipment::modifyAction
		 * order%5Bid%5D=81&shipment%5B0%5D%5Bid%5D=182&shipment%5B0%5D%5Bdeducted%5D=N&shipment%5B0%5D%5BdeliveryDocNum%5D=000000001&shipment%5B0%5D%5BtrackingNumber%5D=0000000002&shipment%5B0%5D%5Bcomments%5D=%CA%EE%EC%EC%E5%ED%F2%E0%F0%E8%E9&shipment%5B0%5D%5BstatusId%5D=DN&shipment%5B0%5D%5BresponsibleId%5D=1&shipment%5B0%5D%5BdeliveryDocDate%5D=&shipment%5B0%5D%5BdeliveryId%5D=1&shipment%5B0%5D%5BcustomPriceDelivery%5D=N&shipment%5B0%5D%5BpriceDelivery%5D=500&shipment%5B0%5D%5BallowDelivery%5D=Y&shipment%5B0%5D%5BbasePriceDelivery%5D=500&shipment%5B0%5D%5BbasketItems%5D%5B0%5D%5Bid%5D=171&shipment%5B0%5D%5BbasketItems%5D%5B0%5D%5Bquantity%5D=1&shipment%5B0%5D%5BbasketItems%5D%5B0%5D%5BbarcodeInfo%5D%5B0%5D%5BstoreId%5D=2&shipment%5B0%5D%5BbasketItems%5D%5B0%5D%5BbarcodeInfo%5D%5B0%5D%5Bquantity%5D=1&shipment%5B0%5D%5BbasketItems%5D%5B0%5D%5BbarcodeInfo%5D%5B0%5D%5Bbarcode%5D%5B0%5D%5Bvalue%5D=%EA%EE%E42.7&shipment%5B0%5D%5BbasketItems%5D%5B0%5D%5BbarcodeInfo%5D%5B0%5D%5Bbarcode%5D%5B0%5D%5Bid%5D=24&shipment%5B0%5D%5BbasketItems%5D%5B1%5D%5Bid%5D=173&shipment%5B0%5D%5BbasketItems%5D%5B1%5D%5Bquantity%5D=1&shipment%5B0%5D%5BbasketItems%5D%5B1%5D%5BbarcodeInfo%5D%5B0%5D%5BstoreId%5D=2&shipment%5B0%5D%5BbasketItems%5D%5B1%5D%5BbarcodeInfo%5D%5B0%5D%5Bquantity%5D=1&shipment%5B0%5D%5BbasketItems%5D%5B1%5D%5BbarcodeInfo%5D%5B0%5D%5Bbarcode%5D%5B0%5D%5Bvalue%5D=1.1_%CF%E0%ED%F2%EE%EB%E5%F2%FB+%CA%EE%F1%F2%E8+%ED%E0+%CF%EB%FF%E6%E5
		 * \Bitrix\Sale\Controller\Shipment::addAction
		 * order%5Bid%5D=81&shipment%5Bdeducted%5D=N&shipment%5BdeliveryDocNum%5D=000000001&shipment%5BtrackingNumber%5D=0000000002&shipment%5Bcomments%5D=%CA%EE%EC%EC%E5%ED%F2%E0%F0%E8%E9&shipment%5BstatusId%5D=DN&shipment%5BresponsibleId%5D=1&shipment%5BdeliveryDocDate%5D=&shipment%5BdeliveryId%5D=1&shipment%5BcustomPriceDelivery%5D=N&shipment%5BpriceDelivery%5D=500&shipment%5BallowDelivery%5D=Y&shipment%5BbasePriceDelivery%5D=500&shipment%5BbasketItems%5D%5B0%5D%5Bid%5D=171&shipment%5BbasketItems%5D%5B0%5D%5Bquantity%5D=1&shipment%5BbasketItems%5D%5B0%5D%5BbarcodeInfo%5D%5B0%5D%5BstoreId%5D=2&shipment%5BbasketItems%5D%5B0%5D%5BbarcodeInfo%5D%5B0%5D%5Bquantity%5D=1&shipment%5BbasketItems%5D%5B0%5D%5BbarcodeInfo%5D%5B0%5D%5Bbarcode%5D%5B0%5D%5Bvalue%5D=%EA%EE%E42.7
		 * \Bitrix\Sale\Controller\Shipment::updateAction
		 * deducted=N&deliveryDocNum=000000001&trackingNumber=0000000002&comments=%CA%EE%EC%EC%E5%ED%F2%E0%F0%E8%E9&statusId=DN&responsibleId=1&deliveryDocDate=&deliveryId=1&customPriceDelivery=N&priceDelivery=500&allowDelivery=Y&basePriceDelivery=500&basketItems%5B0%5D%5Bid%5D=171&basketItems%5B0%5D%5Bquantity%5D=1&basketItems%5B0%5D%5BbarcodeInfo%5D%5B0%5D%5BstoreId%5D=2&basketItems%5B0%5D%5BbarcodeInfo%5D%5B0%5D%5Bquantity%5D=1&basketItems%5B0%5D%5BbarcodeInfo%5D%5B0%5D%5Bbarcode%5D%5B0%5D%5Bvalue%5D=%EA%EE%E42.7&basketItems%5B0%5D%5BbarcodeInfo%5D%5B0%5D%5Bbarcode%5D%5B0%5D%5Bid%5D=24
		 *
		 * Status
		 * \Bitrix\Sale\Controller\Status::modifyAction
		 * status%5Bid%5D=fw&status%5Btype%5D=O&status%5Bsort%5D=100&status%5Bnotify%5D=Y&status%5Bcolor%5D=%23FF5C5B&langs%5B0%5D%5Blid%5D=ru&langs%5B0%5D%5Bname%5D=%CF%F0%E8%ED%FF%F2%2C+%EE%E6%E8%E4%E0%E5%F2%F1%FF+%EE%EF%EB%E0%F2%E0&langs%5B0%5D%5Bdescription%5D=%C7%E0%EA%E0%E7+%EF%F0%E8%ED%FF%F2%2C+%ED%EE+%EF%EE%EA%E0+%ED%E5+%EE%E1%F0%E0%E1%E0%F2%FB%E2%E0%E5%F2%F1%FF+%28%ED%E0%EF%F0%E8%EC%E5%F0%2C+%E7%E0%EA%E0%E7+%F2%EE%EB%FC%EA%EE+%F7%F2%EE+%F1%EE%E7%E4%E0%ED+%E8%EB%E8+%EE%E6%E8%E4%E0%E5%F2%F1%FF+%EE%EF%EB%E0%F2%E0+%E7%E0%EA%E0%E7%E0%29&langs%5B1%5D%5Blid%5D=en&langs%5B1%5D%5Bname%5D=%CF%F0%E8%ED%FF%F2%2C+%EE%E6%E8%E4%E0%E5%F2%F1%FF+%EE%EF%EB%E0%F2%E0&langs%5B1%5D%5Bdescription%5D=%C7%E0%EA%E0%E7+%EF%F0%E8%ED%FF%F2%2C+%ED%EE+%EF%EE%EA%E0+%ED%E5+%EE%E1%F0%E0%E1%E0%F2%FB%E2%E0%E5%F2%F1%FF+%28%ED%E0%EF%F0%E8%EC%E5%F0%2C+%E7%E0%EA%E0%E7+%F2%EE%EB%FC%EA%EE+%F7%F2%EE+%F1%EE%E7%E4%E0%ED+%E8%EB%E8+%EE%E6%E8%E4%E0%E5%F2%F1%FF+%EE%EF%EB%E0%F2%E0+%E7%E0%EA%E0%E7%E0%29&groupTasks%5B0%5D%5BgroupId%5D=7&groupTasks%5B0%5D%5BtaskId%5D=75
		*/
		$arguments = $this->getArguments();

		if($this->format & self::TO_SNAKE)
		{
			$arguments = $this->convertToSnakeCase($arguments);
		}

		if($this->getScope() == Controller::SCOPE_REST)
		{
			if($this->format & self::CHECK_REQUIRED)
			{
				$check = $this->check($arguments);
				if(!$check->isSuccess())
				{
					$r->addErrors($check->getErrors());
				}
			}

			if($r->isSuccess())
			{
				if($this->format & self::TO_WHITE_LIST)
				{
					$arguments = $this->internalize($arguments);
				}
			}
		}

		return $r->setData(['data'=>$r->isSuccess()?$arguments:null]);
	}

	protected function convertToSnakeCase($arguments=[])
	{
		$name = $this->getName();
		/** @var Controller $controller */
		$controller = $this->getController();
		$entity = $this->getEntity($controller);

		LoggerDiag::addMessage('INTERNALIZER_RESOLVE_PARAMS_CAMEL2SNAKE_FIELDS_BEFORE', var_export([
			'name'=>$name,
			'fields'=>$arguments['fields']
		], true));

		if ($name == 'list')
		{
			if(isset($arguments['select']))
			{
				$fields = $arguments['select'];
				if(!empty($fields))
					$arguments['select'] = $entity->convertKeysToSnakeCaseSelect($fields);
			}

			if(isset($arguments['filter']))
			{
				$fields = $arguments['filter'];
				if(!empty($fields))
					$arguments['filter'] = $entity->convertKeysToSnakeCaseFilter($fields);
			}

			if(isset($arguments['order']))
			{
				$fields = $arguments['order'];
				if(!empty($fields))
					$arguments['order'] = $entity->convertKeysToSnakeCaseOrder($fields);
			}
		}
		elseif ($name == 'getfields'){}
		elseif ($name == 'get'){}
		elseif ($name == 'delete'){}
		elseif ($name == 'modify'
			|| $name == 'add'
			|| $name == 'update'
			|| $name == 'tryadd'
			|| $name == 'tryupdate'
			|| $name == 'trymodify')
		{
			if(isset($arguments['fields']))
			{
				$fields = $arguments['fields'];
				if(!empty($fields))
					$arguments['fields'] = $entity->convertKeysToSnakeCaseFields($fields);
			}
		}
		else
		{
			$arguments = $entity->convertKeysToSnakeCaseArguments($name, $arguments);
		}

		LoggerDiag::addMessage('INTERNALIZER_RESOLVE_PARAMS_CAMEL2SNAKE_FIELDS_AFTER', var_export([
			'name'=>$name,
			'fields'=>$arguments['fields']
		], true));

		return $arguments;
	}

	private function internalize($arguments)
	{
		$name = $this->getName();
		/** @var Controller $controller */
		$controller = $this->getController();
		$entity = $this->getEntity($controller);

		LoggerDiag::addMessage('INTERNALIZER_RESOLVE_PARAMS_PREPARE_FIELDS_BEFORE', var_export([
			'name'=>$name,
			'fields'=>$arguments['fields']
		], true));

		if($name == 'add')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $entity->internalizeFieldsAdd($fields);
		}
		elseif ($name == 'update')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $entity->internalizeFieldsUpdate($fields);
		}
		elseif ($name == 'list')
		{
			$fields = $entity->internalizeFieldsList([
				'select'=>$arguments['select'],
				'filter'=>$arguments['filter'],
				'order'=>$arguments['order'],
			]);

			$fields = $entity->rewriteFieldsList([
				'select'=>$fields['select'],
				'filter'=>$fields['filter'],
				'order'=>$fields['order'],
			]);

			$arguments['select'] = $fields['select'];
			$arguments['filter'] = $fields['filter'];
			$arguments['order'] = $fields['order'];
		}
		elseif ($name == 'getfields'){}
		elseif ($name == 'get'){}
		elseif ($name == 'delete'){}
		elseif ($name == 'modify')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $entity->internalizeFieldsModify($fields);
		}
		elseif ($name == 'tryadd')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $entity->internalizeFieldsTryAdd($fields);
		}
		elseif ($name == 'tryupdate')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $entity->internalizeFieldsUpdate($fields);
		}
		elseif ($name == 'trymodify')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $entity->internalizeFieldsTryModify($fields);
		}
		else
		{
			$arguments = $entity->internalizeArguments($name, $arguments);
		}

		LoggerDiag::addMessage('INTERNALIZER_RESOLVE_PARAMS_PREPARE_FIELDS_AFTER', var_export([
			'name'=>$name,
			'fields'=>$arguments['fields']
		], true));

		return $arguments;
	}

	protected function check($arguments)
	{
		$r = new Result();

		$name = $this->getName();
		/** @var Controller $controller */
		$controller = $this->getController();
		$entity = $this->getEntity($controller);

		if($name == 'add')
		{
			$r = $entity->checkFieldsAdd($arguments['fields']);
		}
		elseif ($name == 'update')
		{
			$r = $entity->checkFieldsUpdate($arguments['fields']);
		}
		elseif ($name == 'list'){}
		elseif ($name == 'getfields'){}
		elseif ($name == 'get'){}
		elseif ($name == 'delete'){}
		elseif ($name == 'modify')
		{
			$r = $entity->checkFieldsModify($arguments['fields']);
		}
		elseif ($name == 'tryadd')
		{
			$r = $entity->checkFieldsAdd($arguments['fields']);
		}
		elseif ($name == 'tryupdate')
		{
			$r = $entity->checkFieldsUpdate($arguments['fields']);
		}
		elseif ($name == 'trymodify')
		{
			$r = $entity->checkFieldsModify($arguments['fields']);
		}
		else
		{
			$r = $entity->checkArguments($name, $arguments);
		}

		return $r;
	}
}