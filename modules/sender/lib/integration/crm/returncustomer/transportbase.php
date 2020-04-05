<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Crm\ReturnCustomer;

use Bitrix\Sender\Internals\Queue;
use Bitrix\Sender\Message;
use Bitrix\Sender\Transport;
use Bitrix\Sender\Recipient;

use Bitrix\Crm\Timeline;
use Bitrix\Crm\EntityManageFacility;
use Bitrix\Crm\Integrity\ActualEntitySelector;
use Bitrix\Crm\Category\DealCategory;
use Bitrix\Crm\PhaseSemantics;
use Bitrix\Crm\DealTable;

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
		return [Recipient\Type::CRM_COMPANY_ID, Recipient\Type::CRM_CONTACT_ID];
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

		$entityFields = [
			'TITLE' => $message->replaceFields($config->get('TITLE')),
			'ASSIGNED_BY_ID' => $this->responsibleQueue->next(),
		];

		$selector = (new ActualEntitySelector())
			->setEntity($crmEntityTypeId, $crmEntityId)
			->search();
		$facility = new EntityManageFacility($selector);
		$facility->enableAutoGenRc();
		if ($config->get('ALWAYS_ADD') === 'Y')
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
				$entityFields['CATEGORY_ID'] = $this->detectDealCategoryId(
					$config->get('CATEGORY_ID'),
					$facility
				);
				$facility->registerDeal($entityFields);
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
}