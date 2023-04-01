<?
namespace Bitrix\Sale\Helpers\Order\Builder;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Helpers\Admin\OrderEdit;
use Bitrix\Sale;
use Bitrix\Sale\Shipment;

final class OrderBuilderNew implements IOrderBuilderDelegate
{
	protected $builder = null;

	public function __construct(OrderBuilder $builder)
	{
		$this->builder = $builder;
	}

	public function createOrder(array $data)
	{
		$siteId = '';

		if(isset($data['SITE_ID']))
		{
			$siteId = $data['SITE_ID'];
		}
		elseif($data['LID'])
		{
			$siteId = $data['LID'];
		}

		if($siteId == '')
		{
			$this->builder->getErrorsContainer()->addError(new Error(Loc::getMessage("SALE_HLP_OBN_SITEID_ABSENT")));
			throw new BuildingException();
		}

		$currentUserId = 0;
		$oldUserId = null;

		if (isset($data['USER_ID']))
		{
			$currentUserId = (int)$data['USER_ID'];
		}

		if (isset($data['OLD_USER_ID']))
		{
			$oldUserId = (int)$data['OLD_USER_ID'];
		}

		$currency = null;
		if (isset($data['CURRENCY']))
		{
			$currency = $data['CURRENCY'];
		}

		//If buyer changed - discount also can be changed
		OrderEdit::initCouponsData($currentUserId, $data['ID'], $oldUserId);
		$orderClassName = $this->builder->getRegistry()->getOrderClassName();
		$order = $orderClassName::create($siteId, $currentUserId, $currency);

		if(!$order)
		{
			$this->builder->getErrorsContainer()->addError(new Error(Loc::getMessage("SALE_HLP_OBE_ORDER_NOT_CREATED")));
			throw new BuildingException();
		}

		return $order;
	}

	public function buildBasket()
	{
		if(is_array($this->builder->getFormData('PRODUCT')))
		{
			$this->builder->getBasketBuilder()
				->initBasket()
				->preliminaryDataPreparation()
				->itemsDataPreparation()
				->basketCodeMap()
				->setItemsFields()
				->fillFUser()
				->finalActions();
		}
		else
		{
			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

			/** @var Sale\Basket $basketClass */
			$basketClass = $registry->getBasketClassName();

			if($basket = $basketClass::create($this->builder->getOrder()->getSiteId()))
			{
				$this->builder->getOrder()->setBasket($basket);
			}
			else
			{
				$this->builder->getErrorsContainer()->addError(new Error('Can\'t create basket'));
				throw new BuildingException();
			}
		}
	}

	public function setUser()
	{
		$this->builder->getOrder()->setFieldNoDemand(
			'USER_ID',
			$this->builder->getUserId()
		);

		$currentUserId = (int)$this->builder->getOrder()->getUserId();
		$oldFormDataUserId = (int)$this->builder->getFormData('OLD_USER_ID');

		$currentPersonTypeId = (int)$this->builder->getOrder()->getPersonTypeId();
		$oldPersonTypeId = (int)$this->builder->getFormData('OLD_PERSON_TYPE_ID');

		$reloadProfile = $oldFormDataUserId > 0 && $currentUserId !== $oldFormDataUserId;
		if (!$reloadProfile && $oldPersonTypeId > 0 && $oldPersonTypeId !== $currentPersonTypeId)
		{
			$reloadProfile = true;
		}

		if ($reloadProfile)
		{
			$resultLoading = \Bitrix\Sale\OrderUserProperties::loadProfiles($currentUserId, $currentPersonTypeId);
			if (!$resultLoading->isSuccess())
			{
				return;
			}
			$profiles = $resultLoading->getData();
			if (empty($profiles) || !is_array($profiles))
			{
				return;
			}
			$currentProfile = current($profiles[$currentPersonTypeId]);
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

	public function setShipmentPriceFields(Shipment $shipment, array $fields)
	{
		if ($fields['CUSTOM_PRICE_DELIVERY'] !== 'Y')
		{
			$fields['PRICE_DELIVERY'] = $shipment->calculateDelivery()->getPrice();
		}
		$fields['BASE_PRICE_DELIVERY'] = $fields['PRICE_DELIVERY'];

		$res = $shipment->setFields($fields);

		if(!$res->isSuccess())
		{
			$this->builder->getErrorsContainer()->addErrors($res->getErrors());
		}

		return $shipment;
	}
}
