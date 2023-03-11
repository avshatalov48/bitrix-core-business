<?
namespace Bitrix\Sale\Helpers\Order\Builder;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Helpers\Admin\OrderEdit;
use Bitrix\Sale\Order;
use Bitrix\Sale\Registry;
use Bitrix\Sale\Shipment;

/**
 * Class OrderBuilderExist
 * @package Bitrix\Sale\Helpers\Order\Builder
 * @internal
 */
final class OrderBuilderExist implements IOrderBuilderDelegate
{
	/** @var OrderBuilder|null  */
	protected $builder = null;

	/**
	 * OrderBuilderExist constructor.
	 * @param OrderBuilder $builder
	 */
	public function __construct(OrderBuilder $builder)
	{
		$this->builder = $builder;
	}

	/**
	 * @param array $data Form data.
	 * @return Order
	 * @throws BuildingException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function createOrder(array $data)
	{
		$orderClassName = $this->builder->getRegistry()->getOrderClassName();
		$currentUserId = 0;
		$oldUserId = null;

		$dataUserId = (int)($data["USER_ID"] ?? 0);
		if ($dataUserId > 0)
		{
			$currentUserId = $dataUserId;
		}

		$dataOldUserId = (int)($data["OLD_USER_ID"] ?? 0);
		if ($dataOldUserId > 0)
		{
			$oldUserId = $dataOldUserId;
		}

		//If buyer changed - discount also can be changed
		OrderEdit::initCouponsData($currentUserId, $data["ID"], $oldUserId);
		$order = $orderClassName::load($data["ID"]);

		if(!$order)
		{
			$this->builder->getErrorsContainer()->addError(new Error(Loc::getMessage("SALE_HLP_OBE_ORDER_NOT_LOADED")));
			throw new BuildingException();
		}

		return $order;
	}

	public function setUser()
	{
		$currentUserId = (int)$this->builder->getOrder()->getUserId();

		$formDataUserId = (int)($this->builder->getFormData()['USER_ID'] ?? 0);
		$isChanged = ($formDataUserId > 0) && ($currentUserId !== $formDataUserId);
		if ($currentUserId && $isChanged)
		{
			$paymentCollection = $this->builder->getOrder()->getPaymentCollection();
			/** @var \Bitrix\Sale\Payment $payment */
			foreach ($paymentCollection as $payment)
			{
				if ($payment->isPaid())
				{
					$this->builder->getErrorsContainer()->addError(new Error(
						Loc::getMessage("SALE_HLP_OBE_CHANGE_USER_ERROR")
						, 'SALE_ORDEREDIT_ERROR_CHANGE_USER_WITH_PAID_PAYMENTS'));
				}
			}
		}

		if ($formDataUserId > 0)
		{
			$this->builder->getOrder()->setFieldNoDemand(
				"USER_ID",
				$this->builder->getUserId()
			);
		}

		if ($isChanged)
		{
			$personTypeId =  (int)$this->builder->getOrder()->getPersonTypeId();
			$resultLoading = \Bitrix\Sale\OrderUserProperties::loadProfiles($formDataUserId, $personTypeId);
			if (!$resultLoading->isSuccess())
			{
				return;
			}
			$profiles = $resultLoading->getData();
			if (!is_array($profiles[$personTypeId]))
			{
				return;
			}
			$currentProfile = current($profiles[$personTypeId]);
			if (empty($currentProfile))
			{
				return;
			}
			$values = $currentProfile['VALUES'];
			$propertyCollection = $this->builder->getOrder()->getPropertyCollection();
			$propertyCollection->setValuesFromPost(
				['PROPERTIES' => $values],[]
			);
		}
	}

	public function buildBasket()
	{
		if(is_array($this->builder->getFormData('PRODUCT')))
		{
			$this->builder->getBasketBuilder()
				->initBasket()
				->preliminaryDataPreparation()
				->removeDeletedItems() //edit only
				->itemsDataPreparation()
				->basketCodeMap()
				->setItemsFields()
				->fillFUser()
				->finalActions();
		}
	}

	public function setShipmentPriceFields(Shipment $shipment, array $fields)
	{
		if ($fields['CUSTOM_PRICE_DELIVERY'] !== 'Y' && $shipment->getId() <= 0)
		{
			$priceDelivery = $shipment->calculateDelivery()->getPrice();
		}
		else
		{
			$priceDelivery = $fields['PRICE_DELIVERY'];
		}

		if ($fields['CUSTOM_PRICE_DELIVERY'] === 'Y' || !isset($fields['BASE_PRICE_DELIVERY']))
		{
			$basePriceDelivery = $priceDelivery;
		}
		else
		{
			$basePriceDelivery = $fields['BASE_PRICE_DELIVERY'];
		}

		$fields['BASE_PRICE_DELIVERY'] = $basePriceDelivery;
		$fields['PRICE_DELIVERY'] = $priceDelivery;

		$res = $shipment->setFields($fields);

		if(!$res->isSuccess())
		{
			$this->builder->getErrorsContainer()->addErrors($res->getErrors());
		}

		return $shipment;
	}
}
