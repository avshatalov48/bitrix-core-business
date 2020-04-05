<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Helpers\Order\Builder\SettingsContainer;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale;
use Bitrix\Sale\Result;

class Payment extends Controller
{
	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			Sale\Payment::class,
			'payment',
			function($className, $id) {

				$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

				/** @var Sale\Payment $paymentClass */
				$paymentClass = $registry->getPaymentClassName();

				$r = $paymentClass::getList([
					'select'=>['ORDER_ID'],
					'filter'=>['ID'=>$id]
				]);

				if($row = $r->fetch())
				{
					/** @var Sale\Order $orderClass */
					$orderClass = $registry->getOrderClassName();

					$order = $orderClass::load($row['ORDER_ID']);
					$payment = $order->getPaymentCollection()->getItemById($id);
					if ($payment)
					{
						return $payment;
					}
				}
				else
				{
					$this->addError(new Error('payment is not exists', 200640400001));
				}
				return null;
			}
		);
	}

	//region Actions
	public function getFieldsAction()
	{
		$entity = new \Bitrix\Sale\Rest\Entity\Payment();
		return ['PAYMENT'=>$entity->prepareFieldInfos(
			$entity->getFields()
		)];
	}

	public function modifyAction($fields)
	{
		$builder = $this->getBuilder();
		$builder->buildEntityPayments($fields);

		if($builder->getErrorsContainer()->getErrorCollection()->count()>0)
		{
			$this->addErrors($builder->getErrorsContainer()->getErrors());
			return null;
		}

		$order = $builder->getOrder();

		$r = $order->save();
		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}

		//TODO: return $payment->toArray();
		return ['PAYMENTS'=>$this->toArray($order)['ORDER']['PAYMENTS']];
	}

	public function addAction(array $fields)
	{
		$result = null;

		$data = [];
		$data['ORDER']['ID'] = $fields['ORDER_ID'];
		$data['ORDER']['PAYMENTS'] = [$fields];

		$builder = $this->getBuilder(
			new SettingsContainer([
				'deletePaymentIfNotExists' => false
			])
		);

		$builder->buildEntityPayments($data);

		if($builder->getErrorsContainer()->getErrorCollection()->count()>0)
		{
			$this->addErrors($builder->getErrorsContainer()->getErrors());
			return null;
		}

		$order=$builder->getOrder();
		$idx=0;
		$collection = $order->getPaymentCollection();
		/** @var \Bitrix\Sale\Payment $payment */
		foreach($collection as $payment)
		{
			if($payment->getId() <= 0)
			{
				$idx = $payment->getInternalIndex();
				break;
			}
		}

		$r = $order->save();
		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}

		/** @var \Bitrix\Sale\Payment $entity */
		$entity = $order->getPaymentCollection()->getItemByIndex($idx);
		return ['PAYMENT'=>$this->get($entity)];
	}

	public function updateAction(\Bitrix\Sale\Payment $payment, array $fields)
	{
		$data = [];

		$fields['ID'] = $payment->getId();
		$fields['ORDER_ID'] = $payment->getOrderId();

		$data['ORDER']['ID'] = $fields['ORDER_ID'];
		$data['ORDER']['PAYMENTS'] = [$fields];

		$builder = $this->getBuilder(
			new SettingsContainer([
				'deletePaymentIfNotExists' => false
			])
		);
		$builder->buildEntityPayments($data);

		if($builder->getErrorsContainer()->getErrorCollection()->count()>0)
		{
			$this->addErrors($builder->getErrorsContainer()->getErrors());
			return null;
		}

		$order = $builder->getOrder();

		$r = $order->save();
		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		if($r->hasWarnings())
		{
			$this->addErrors($r->getWarnings());
			return null;
		}

		/** @var \Bitrix\Sale\Payment $entity */
		$entity = $order->getPaymentCollection()->getItemById($payment->getId());
		return ['PAYMENT'=>$this->get($entity)];
	}

	public function deleteAction(\Bitrix\Sale\Payment $payment)
	{
		$r = $payment->delete();
		return $this->save($payment, $r);
	}

	public function getAction(\Bitrix\Sale\Payment $payment)
	{
		return ['PAYMENT'=>$this->get($payment)];
	}

	public function listAction($select=[], $filter=[], $order=[], PageNavigation $pageNavigation)
	{
		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['ID'=>'ASC']:$order;

		$runtime = [
			new \Bitrix\Main\Entity\ReferenceField(
				'PAY_SYSTEM',
				'\Bitrix\Sale\Internals\PaySystemActionTable',
				array('=this.PAY_SYSTEM_ID' => 'ref.ID')
			)
		];

		$payments = \Bitrix\Sale\Payment::getList(
			[
				'select'=>$select,
				'filter'=>$filter,
				'order'=>$order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit(),
				'runtime' => $runtime
			]
		)->fetchAll();

		return new Page('payments', $payments, function() use ($select, $filter, $runtime)
		{
			return count(
				\Bitrix\Sale\Payment::getList(['select'=>$select, 'filter'=>$filter, 'runtime'=>$runtime])->fetchAll()
			);
		});
	}

	public function getOrderIdAction(\Bitrix\Sale\Payment $payment)
	{
		return $payment->getOrderId();
	}

	public function getPaymentSystemIdAction(\Bitrix\Sale\Payment $payment)
	{
		return $payment->getPaymentSystemId();
	}

	public function getPaymentSystemNameAction(\Bitrix\Sale\Payment $payment)
	{
		return $payment->getPaymentSystemName();
	}

	public function getPersonTypeIdAction(\Bitrix\Sale\Payment $payment)
	{
		return $payment->getPersonTypeId();
	}

	public function getSumAction(\Bitrix\Sale\Payment $payment)
	{
		return $payment->getSum();
	}

	public function getSumPaidAction(\Bitrix\Sale\Payment $payment)
	{
		return $payment->getSumPaid();
	}

	public function isInnerAction(\Bitrix\Sale\Payment $payment)
	{
		return $payment->isInner()? 'Y':'N';
	}

	public function isMarkedAction(\Bitrix\Sale\Payment $payment)
	{
		return $payment->isMarked()? 'Y':'N';
	}

	public function isPaidAction(\Bitrix\Sale\Payment $payment)
	{
		return $payment->isPaid()? 'Y':'N';
	}

	public function isReturnAction(\Bitrix\Sale\Payment $payment)
	{
		return $payment->isReturn()? 'Y':'N';
	}

	public function setPaidAction(\Bitrix\Sale\Payment $payment, $value)
	{
		$r = $payment->setPaid($value);
		if($r->isSuccess())
		{
			$this->save($payment, $r);
		}

		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}

		return true;
	}
/*
	public function setAccountNumberAction(\Bitrix\Sale\Payment $payment, $id)
	{
		$r = $payment->setAccountNumber($id);
		return $this->save($payment, $r);
	}
*/
	public function setReturnAction(\Bitrix\Sale\Payment $payment, $value)
	{
		$r = $payment->setReturn($value);
		return $this->save($payment, $r);
	}
	//endregion

	private function save(\Bitrix\Sale\Payment $payment, Result $r)
	{
		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		else
		{
			/** @var PaymentCollection $collection */
			$collection = $payment->getCollection();
			$r = $collection->getOrder()->save();
			if(!$r->isSuccess())
			{
				$this->addErrors($r->getErrors());
				return null;
			}
		}
		return $r->isSuccess();
	}

	protected function get(\Bitrix\Sale\Payment $payment, array $fields=[])
	{
		$payments = $this->toArray($payment->getCollection()->getOrder(), $fields)['ORDER']['PAYMENTS'];
		foreach ($payments as $item)
		{
			if($item['ID']==$payment->getId())
			{
				return $item;
			}
		}
		return [];
	}

	static public function prepareFields($fields)
	{
		return isset($fields['PAYMENTS'])?['PAYMENT'=>$fields['PAYMENTS']]:[];
	}

	protected function checkPermissionEntity($name)
	{
		if($name == 'getorderid'
			|| $name == 'getpaymentsystemid'
			|| $name == 'getpaymentsystemname'
			|| $name == 'getpersontypeid'
			|| $name == 'getsum'
			|| $name == 'getsumpaid'
			|| $name == 'isinner'
			|| $name == 'ismarked'
			|| $name == 'isnarked'
			|| $name == 'ispaid'
			|| $name == 'isreturn'
		)
		{
			$r = $this->checkReadPermissionEntity();
		}
		elseif($name == 'setpaid'
			|| $name == 'setreturn')
		{
			$r = $this->checkModifyPermissionEntity();
		}
		else
		{
			$r = parent::checkPermissionEntity($name);
		}
		return $r;
	}
}