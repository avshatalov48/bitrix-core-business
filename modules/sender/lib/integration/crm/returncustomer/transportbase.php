<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Crm\ReturnCustomer;

use Bitrix\Crm\Category\DealCategory;
use Bitrix\Crm\DealTable;
use Bitrix\Crm\EntityManageFacility;
use Bitrix\Crm\Integrity\ActualEntitySelector;
use Bitrix\Crm\Order\Basket;
use Bitrix\Crm\Order\BasketItem;
use Bitrix\Crm\Order\Company;
use Bitrix\Crm\Order\ContactCompanyEntity;
use Bitrix\Crm\Order\Order;
use Bitrix\Crm\Order\Payment;
use Bitrix\Crm\PhaseSemantics;
use Bitrix\Crm\Service\Container;
use Bitrix\Crm\Timeline;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sender\Internals\Queue;
use Bitrix\Sender\Message;
use Bitrix\Sender\Recipient;
use Bitrix\Sender\Transport;

/**
 * Class TransportBase
 * @package Bitrix\Sender\Integration\Crm\ReturnCustomer;
 */
class TransportBase implements Transport\iBase
{
	const CODE = self::CODE_UNDEFINED;
	const CODE_RC_LEAD = 'rc_lead';
	const CODE_RC_DEAL = 'rc_deal';

	/** @var Message\Configuration $configuration Configuration. */
	protected $configuration;

	/** @var  Queue|null Queue. */
	protected $responsibleQueue;

	/**
	 * Transport constructor.
	 */
	public function __construct()
	{
		$this->configuration = new Message\Configuration();
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'Rc';
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		return static::CODE;
	}

	/**
	 * Get supported recipient types.
	 *
	 * @return integer[]
	 */
	public function getSupportedRecipientTypes()
	{
		return [
			Recipient\Type::CRM_COMPANY_ID,
			Recipient\Type::CRM_CONTACT_ID,
			Recipient\Type::CRM_LEAD_ID,
		];
	}

	/**
	 * Get configuration.
	 *
	 * @return string
	 */
	public function loadConfiguration()
	{
		return $this->configuration;
	}

	public function saveConfiguration(Message\Configuration $configuration)
	{
		$this->configuration = $configuration;
	}

	/**
	 * Start.
	 *
	 * @return void
	 */
	public function start()
	{

	}

	/**
	 * Send.
	 *
	 * @param Message\Adapter $message Message.
	 * @return bool
	 */
	public function send(Message\Adapter $message)
	{
		$config = $message->getConfiguration();
		$authorId = $config->get('LETTER_CREATED_BY_ID');
		$text = $message->replaceFields($config->get('COMMENT'));
		$crmEntityId = $message->getRecipientCode();
		$crmEntityTypeId = Service::getTypeIdByRecipientType($message->getRecipientType());

		if (!$this->responsibleQueue || $this->responsibleQueue->getId() <> $message->getId())
		{
			$responsibleList = $config->get('ASSIGNED_BY');
			if ($responsibleList)
			{
				$responsibleList = explode(',', $responsibleList);
			}
			if (empty($responsibleList))
			{
				$responsibleList = [$authorId];
			}
			$this->responsibleQueue = (new Queue('sender/rc', $message->getId()))
				->enableUserCheck()
				->disableAutoSave()
				->setValues($responsibleList);

			if ($config->get('CHECK_WORK_TIME') === 'Y')
			{
				$this->responsibleQueue->enableWorkTimeCheck();
			}
		}

		$entityFields['TITLE'] = $message->replaceFields($config->get('TITLE'));
		$assignedId = $this->getAssignedWithCrmData((int)$crmEntityTypeId, (int)$crmEntityId);
		$isAssignedById = ($config->get('LINK_WITH_RESPONSIBLE') === 'Y') && $assignedId;
		$entityFields['ASSIGNED_BY_ID'] = $isAssignedById ? $assignedId : $this->responsibleQueue->next();

		$selector = (new ActualEntitySelector())
			->setEntity($crmEntityTypeId, $crmEntityId)
			->search();
		$facility = new EntityManageFacility($selector);
		$facility->enableAutoGenRc();

		if ($config->get('ALWAYS_ADD') === 'Y' || $config->get('FROM_PREVIOUS') === 'Y')
		{
			$facility->setRegisterMode(EntityManageFacility::REGISTER_MODE_ALWAYS_ADD);
		}

		switch ($message->getCode())
		{
			case MessageBase::CODE_RC_LEAD:
				if (empty($entityFields['SOURCE_ID']))
				{
					$entityFields['SOURCE_ID'] = 'RC_GENERATOR';
				}
				$facility->registerLead($entityFields);
				break;

			case MessageBase::CODE_RC_DEAL:
				if (empty($entityFields['SOURCE_ID']))
				{
					$entityFields['SOURCE_ID'] = 'RC_GENERATOR';
				}
				$entityFields['CATEGORY_ID'] = $this->detectDealCategoryId(
					$config->get('CATEGORY_ID'),
					$facility
				);


				if ($config->get('FROM_PREVIOUS') === 'Y')
				{
					$lastDeal = $this->getLastDeal($facility, $config->get('DEAL_DAYS_AGO'));
					$lastOrder = $this->getOrderData($lastDeal['ID']);
					if(!$lastDeal || !$lastOrder)
					{
						$this->responsibleQueue->previous();
						return false;
					}

					$entityFields += $lastDeal;
				}


				$registeredId = $facility->registerDeal($entityFields);

				if($registeredId && $config->get('FROM_PREVIOUS') === 'Y')
				{
					$this->copyDealProducts($lastDeal['ID'], $registeredId);
					$this->copyOrder($lastDeal['ID'], $registeredId);
				}
				break;

			default:
				return false;
		}

		if (!$facility->getRegisteredId())
		{
			$this->responsibleQueue->previous();
			return false;
		}

		if (!$text)
		{
			return true;
		}

		$commentId = Timeline\CommentEntry::create([
			'TYPE_ID' => Timeline\TimelineType::COMMENT,
			'AUTHOR_ID' => $authorId,
			'TEXT' => $text,
			'SETTINGS' => [],
			'BINDINGS' => [
				[
					'ENTITY_TYPE_ID' => $facility->getRegisteredTypeId(),
					'ENTITY_ID' => $facility->getRegisteredId(),
					'IS_FIXED' => true
				]
			],
			'ASSOCIATED_ENTITY_TYPE_ID' => $facility->getRegisteredTypeId(),
			'ASSOCIATED_ENTITY_ID' => $facility->getRegisteredId()
		]);

		return $commentId > 0;
	}

	/**
	 * End.
	 *
	 * @return void
	 */
	public function end()
	{
		if ($this->responsibleQueue)
		{
			$this->responsibleQueue->save();
		}
	}

	protected function detectDealCategoryId($categoryId, EntityManageFacility $facility)
	{
		if ($facility->canAddDeal() && !is_numeric($categoryId))
		{
			// retrieve category from last deal if it configured.
			$categoryId = $this->getLastDealCategoryId($facility);
		}

		$categoryId = (int) $categoryId;
		$categories = array_map(
			function ($category)
			{
				return (int) $category['ID'];
			},
			DealCategory::getAll(true)
		);

		if (!in_array($categoryId, $categories))
		{
			$categoryId = $categories[0];
		}

		return $categoryId;
	}

	protected function getLastDealCategoryId(EntityManageFacility $facility)
	{
		$categoryId = null;
		$categoryFilters = [];
		if ($facility->getSelector()->getCompanyId())
		{
			$categoryFilters[] = [
				'=COMPANY_ID' => $facility->getSelector()->getCompanyId()
			];
		}
		if ($facility->getSelector()->getContactId())
		{
			$categoryFilters[] = [
				'=CONTACT_ID' => $facility->getSelector()->getContactId()
			];
		}
		foreach ($categoryFilters as $categoryFilter)
		{
			$categoryFilter['=STAGE_SEMANTIC_ID'] = [
				PhaseSemantics::PROCESS,
				PhaseSemantics::SUCCESS
			];
			$dealRow = DealTable::getRow([
				'select' => ['CATEGORY_ID'],
				'filter' => $categoryFilter,
				'limit' => 1,
				'order' => ['DATE_CREATE' => 'DESC']
			]);

			if (!$dealRow)
			{
				break;
			}
			$categoryId = $dealRow['CATEGORY_ID'];
		}

		return $categoryId;
	}

	protected function getLastDeal(EntityManageFacility $facility, $days)
	{
		$categoryId = null;
		$dealFilters = [];
		if ($facility->getSelector()->getCompanyId())
		{
			$dealFilters[] = [
				'=COMPANY_ID' => $facility->getSelector()->getCompanyId()
			];
		}
		if ($facility->getSelector()->getContactId())
		{
			$dealFilters[] = [
				'=CONTACT_ID' => $facility->getSelector()->getContactId()
			];
		}
		foreach ($dealFilters as $dealFilter)
		{
			$dealFilter['=STAGE_SEMANTIC_ID'] = [
				PhaseSemantics::SUCCESS
			];
			$days = (int) $days;

			$dateCreate = (new \DateTime())->modify("-$days days");

			$beginningOfTheDay = DateTime::createFromPhp($dateCreate->setTime(0,0,0));
			$endOfTheDay = DateTime::createFromPhp($dateCreate->setTime(23,59,59));

			$dealFilter['<DATE_CREATE'] = $endOfTheDay;
			$dealFilter['>DATE_CREATE'] = $beginningOfTheDay;

			$dealFilter['=HAS_PRODUCTS'] = 1;

			$dealRow = DealTable::getRow([
				'select' => ['ID', 'CURRENCY_ID', 'RECEIVED_AMOUNT'],
				'filter' => $dealFilter,
				'limit' => 1,
				'order' => ['DATE_CREATE' => 'DESC']
			]);

			if (!$dealRow)
			{
				break;
			}

			return $dealRow;
		}

		return null;
	}

	protected function getOrderData($dealId)
	{
		$dbRes = \Bitrix\Crm\Order\EntityBinding::getList([
			'select' => ['PAYMENT_ID' => 'ORDER.PAYMENT.ID', 'PAYMENT_ORDER_ID' => 'ORDER.PAYMENT.ORDER_ID'],
			'filter' => [
				'OWNER_ID' => $dealId,
				'OWNER_TYPE_ID' => \CCrmOwnerType::Deal,
				'!ORDER.PAYMENT.PS_RECURRING_TOKEN' => '',
				'=ORDER.PAYMENT.PAID' => 'Y',
			],
			'order' => ['ORDER.PAYMENT.ID' => 'DESC']
		]);

		return $dbRes->fetch();
	}

	protected function copyDealProducts($fromDealId, $toDealId)
	{

		$productRows = \CCrmDeal::LoadProductRows($fromDealId);

		foreach($productRows as &$productRow)
		{
			$productRow['ID'] = 0;
		}

		\CCrmDeal::SaveProductRows($toDealId, $productRows, false);
	}

	protected function copyOrder($formDealId, $toDealId)
	{
		if ($data = $this->getOrderData($formDealId))
		{
			$registry = \Bitrix\Sale\Registry::getInstance( \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);
			$orderClassName = $registry->getOrderClassName();

			/** @var Order $order */
			$order = $orderClassName::load($data['PAYMENT_ORDER_ID']);
			if ($order)
			{
				/** @var Order $newOrder */
				$newOrder = $orderClassName::create($order->getSiteId(), $order->getUserId(), $order->getCurrency());

				/** @var Basket $basketClassName */
				$basketClassName = $registry->getBasketClassName();

				$newBasket = $basketClassName::create($order->getSiteId());
				/** @var BasketItem $item */
				foreach ($order->getBasket() as $item)
				{
					$basketItem = $newBasket->createItem($item->getField('MODULE'), $item->getProductId());
					$basketItem->setFields([
						'PRODUCT_PROVIDER_CLASS' => $item->getField('PRODUCT_PROVIDER_CLASS'),
						'QUANTITY' => $item->getQuantity(),
						'CURRENCY' => $item->getCurrency(),
						'BASE_PRICE' => $item->getBasePrice(),
						'PRICE' => $item->getPrice(),
						'CUSTOM_PRICE' => $item->isCustomPrice() ? 'Y' : 'N',

					]);
				}

				$newOrder->setBasket($newBasket);

				/** @var Payment $payment */
				$payment = $order->getPaymentCollection()->getItemById($data['PAYMENT_ID']);
				if ($payment)
				{
					$newPayment = $newOrder->getPaymentCollection()->createItem();
					$newPayment->setFields([
						'PAY_SYSTEM_ID' => $payment->getPaymentSystemId(),
						'PAY_SYSTEM_NAME' => $payment->getPaymentSystemName(),
						'PS_RECURRING_TOKEN' => $payment->getField('PS_RECURRING_TOKEN'),
						'SUM' => $payment->getSum(),
					]);
				}

				/** @var \Bitrix\Sale\Shipment $newShipment */
				$newShipment = $newOrder->getShipmentCollection()->createItem();
				foreach ($newBasket as $item)
				{
					$newShipmentItem = $newShipment->getShipmentItemCollection()->createItem($item);
					$newShipmentItem->setQuantity($item->getQuantity());
				}

				$binding = $newOrder->createEntityBinding();
				$binding->setField('OWNER_ID', $toDealId);
				$binding->setField('OWNER_TYPE_ID', \CCrmOwnerType::Deal);

				/** @var ContactCompanyEntity $item */
				foreach ($order->getContactCompanyCollection()->getCompanies() as $item)
				{
					/** @var Company $newCompany */
					$newCompany = $newOrder->getContactCompanyCollection()->createCompany();
					$newCompany->setField('ENTITY_ID', $item->getField('ENTITY_ID'));
					$newCompany->setField('IS_PRIMARY', $item->getField('IS_PRIMARY'));
				}

				/** @var ContactCompanyEntity $item */
				foreach ($order->getContactCompanyCollection()->getContacts() as $item)
				{
					/** @var Contact $newContact */
					$newContact = $newOrder->getContactCompanyCollection()->createContact();
					$newContact->setField('ENTITY_ID', $item->getField('ENTITY_ID'));
					$newContact->setField('IS_PRIMARY', $item->getField('IS_PRIMARY'));
				}

				$newOrder->refreshData();
				$newOrder->doFinalAction();

				$newOrder->save();
			}
		}
	}

	private function getAssignedWithCrmData(int $typeId, int $entityId): ?int
	{
		$factory = Container::getInstance()->getFactory($typeId);
		$entity = $factory->getItem($entityId);
		if ($entity)
		{
			$assignedId = (int)$entity->get('ASSIGNED_BY_ID');
			if ($assignedId)
			{
				return $assignedId;
			}
		}

		return null;
	}
}
