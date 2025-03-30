<?php

namespace Bitrix\Sale\Controller;

use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale;
use Bitrix\Sale\Helpers\Order\Builder\SettingsContainer;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PaySystem\PaymentAvailablesPaySystems;

/**
 * Payment controller
 *
 * @example BX.ajax.runAction("sale.payment.[action]", { data: {...} });
 */
class Payment extends Controller
{
	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			Sale\Payment::class,
			'payment',
			function($className, $id)
			{
				$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

				/** @var Sale\Payment $paymentClass */
				$paymentClass = $registry->getPaymentClassName();

				$iterator = $paymentClass::getList([
					'select' => [
						'ORDER_ID',
					],
					'filter' => [
						'ID' => $id,
					],
				]);

				$row = $iterator->fetch();
				if ($row)
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
		$entity = new Sale\Rest\Entity\Payment();

		return [
			'PAYMENT' => $entity->prepareFieldInfos($entity->getFields()),
		];
	}

	public function modifyAction($fields)
	{
		$builder = $this->getBuilder();
		$builder->buildEntityPayments($fields);

		if (!$builder->getErrorsContainer()->getErrorCollection()->isEmpty())
		{
			$this->addErrors($builder->getErrorsContainer()->getErrors());

			return null;
		}

		$order = $builder->getOrder();

		$result = $order->save();
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		//TODO: return $payment->toArray();
		return [
			'PAYMENTS' => $this->toArray($order)['ORDER']['PAYMENTS'],
		];
	}

	public function addAction(array $fields)
	{
		$data = [];
		$data['ORDER']['ID'] = $fields['ORDER_ID'];
		$data['ORDER']['PAYMENTS'] = [$fields];

		$builder = $this->getBuilder(
			new SettingsContainer([
				'deletePaymentIfNotExists' => false,
			])
		);

		$builder->buildEntityPayments($data);

		if (!$builder->getErrorsContainer()->getErrorCollection()->isEmpty())
		{
			$this->addErrors($builder->getErrorsContainer()->getErrors());

			return null;
		}

		$order = $builder->getOrder();
		$idx = 0;
		$collection = $order->getPaymentCollection();
		/** @var Sale\Payment $payment */
		foreach ($collection as $payment)
		{
			if ($payment->getId() <= 0)
			{
				$idx = $payment->getInternalIndex();
				break;
			}
		}

		$result = $order->save();
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		/** @var Sale\Payment $entity */
		$entity = $order->getPaymentCollection()->getItemByIndex($idx);

		return [
			'PAYMENT' => $this->get($entity),
		];
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
				'deletePaymentIfNotExists' => false,
			])
		);
		$builder->buildEntityPayments($data);

		if (!$builder->getErrorsContainer()->getErrorCollection()->isEmpty())
		{
			$this->addErrors($builder->getErrorsContainer()->getErrors());

			return null;
		}

		$order = $builder->getOrder();

		$result = $order->save();
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}
		if ($result->hasWarnings())
		{
			$this->addErrors($result->getWarnings());

			return null;
		}

		/** @var Sale\Payment $entity */
		$entity = $order->getPaymentCollection()->getItemById($payment->getId());

		return [
			'PAYMENT' => $this->get($entity),
		];
	}

	public function deleteAction(\Bitrix\Sale\Payment $payment)
	{
		$result = $payment->delete();

		return $this->save($payment, $result);
	}

	public function getAction(\Bitrix\Sale\Payment $payment)
	{
		return [
			'PAYMENT' => $this->get($payment),
		];
	}

	public function listAction(
		PageNavigation $pageNavigation,
		array $select = [],
		array $filter = [],
		array $order = [],
		bool $__calculateTotalCount = true
	): Page
	{
		$select = empty($select) ? ['*'] : $select;
		$order = empty($order) ? ['ID' => 'ASC'] : $order;

		$runtime = [
			new \Bitrix\Main\Entity\ReferenceField(
				'PAY_SYSTEM',
				'\Bitrix\Sale\Internals\PaySystemActionTable',
				['=this.PAY_SYSTEM_ID' => 'ref.ID']
			)
		];

		$iterator = Sale\Payment::getList([
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
			'offset' => $pageNavigation->getOffset(),
			'limit' => $pageNavigation->getLimit(),
			'runtime' => $runtime,
			'count_total' => $__calculateTotalCount,
		]);
		$payments = $iterator->fetchAll();
		$totalCount = $__calculateTotalCount ? $iterator->getCount() : 0;

		return new Page('payments', $payments, $totalCount);
	}

	public function getOrderIdAction(Sale\Payment $payment)
	{
		return $payment->getOrderId();
	}

	public function getPaymentSystemIdAction(Sale\Payment $payment)
	{
		return $payment->getPaymentSystemId();
	}

	public function getPaymentSystemNameAction(Sale\Payment $payment)
	{
		return $payment->getPaymentSystemName();
	}

	public function getPersonTypeIdAction(Sale\Payment $payment)
	{
		return $payment->getPersonTypeId();
	}

	public function getSumAction(Sale\Payment $payment)
	{
		return $payment->getSum();
	}

	public function getSumPaidAction(Sale\Payment $payment)
	{
		return $payment->getSumPaid();
	}

	public function isInnerAction(Sale\Payment $payment)
	{
		return $payment->isInner() ? 'Y' : 'N';
	}

	public function isMarkedAction(Sale\Payment $payment)
	{
		return $payment->isMarked() ? 'Y' : 'N';
	}

	public function isPaidAction(Sale\Payment $payment)
	{
		return $payment->isPaid() ? 'Y' : 'N';
	}

	public function isReturnAction(Sale\Payment $payment)
	{
		return $payment->isReturn() ? 'Y' : 'N';
	}

	public function setPaidAction(Sale\Payment $payment, $value)
	{
		$result = $payment->setPaid($value);
		if ($result->isSuccess())
		{
			$this->save($payment, $result);
		}

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

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
	public function setReturnAction(Sale\Payment $payment, $value)
	{
		$result = $payment->setReturn($value);

		return $this->save($payment, $result);
	}

	/**
	 * Remove bindings pay systems for payment
	 *
	 * Example:
	 * BX.ajax.runAction("sale.payment.clearavailablepaysystems", {data:{ id: 36 }});
	 *
	 * @param Sale\Payment $payment
	 * @return true|null
	 */
	public function clearAvailablePaySystemsAction(Sale\Payment $payment)
	{
		$result = PaymentAvailablesPaySystems::clearBindings($payment->getId());
		$errors = $result->getErrors();
		if (!empty($errors))
		{
			$this->addErrors($errors);

			return null;
		}

		return true;
	}

	/**
	 * Set available pay systems for payment
	 *
	 * Example of specifying payment systems:
	 * BX.ajax.runAction("sale.payment.setavailablepaysystems", {data:{ id: 36, paySystemIds: [1,2,7,8] }});
	 *
	 * @param Sale\Payment $payment
	 * @param array $paySystemIds
	 * @return true|null
	 */
	public function setAvailablePaySystemsAction(Sale\Payment $payment, array $paySystemIds)
	{
		$result = PaymentAvailablesPaySystems::setBindings($payment->getId(), $paySystemIds);
		$errors = $result->getErrors();
		if (!empty($errors))
		{
			$this->addErrors($errors);

			return null;
		}

		return true;
	}
	//endregion

	private function save(Sale\Payment $payment, Result $r)
	{
		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());

			return null;
		}
		else
		{
			/** @var PaymentCollection $collection */
			$collection = $payment->getCollection();
			$r = $collection->getOrder()->save();
			if (!$r->isSuccess())
			{
				$this->addErrors($r->getErrors());

				return null;
			}
		}

		return $r->isSuccess();
	}

	protected function get(Sale\Payment $payment, array $fields=[])
	{
		$payments = $this->toArray($payment->getCollection()->getOrder(), $fields)['ORDER']['PAYMENTS'];
		foreach ($payments as $item)
		{
			if ($item['ID'] == $payment->getId())
			{
				return $item;
			}
		}

		return [];
	}

	static public function prepareFields($fields)
	{
		return
			isset($fields['PAYMENTS'])
				? ['PAYMENT' => $fields['PAYMENTS']]
				: []
		;
	}

	protected function checkPermissionEntity($name)
	{
		if (
			$name === 'getorderid'
			|| $name === 'getpaymentsystemid'
			|| $name === 'getpaymentsystemname'
			|| $name === 'getpersontypeid'
			|| $name === 'getsum'
			|| $name === 'getsumpaid'
			|| $name === 'isinner'
			|| $name === 'ismarked'
			|| $name === 'isnarked'
			|| $name === 'ispaid'
			|| $name === 'isreturn'
		)
		{
			$result = $this->checkReadPermissionEntity();
		}
		elseif (
			$name === 'setpaid'
			|| $name === 'setavailablepaysystems'
			|| $name === 'clearavailablepaysystems'
			|| $name === 'setreturn'
		)
		{
			$result = $this->checkModifyPermissionEntity();
		}
		else
		{
			$result = parent::checkPermissionEntity($name);
		}

		return $result;
	}
}
