<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Posting;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sender\Connector;
use Bitrix\Sender\Consent\Consent;
use Bitrix\Sender\ContactTable;
use Bitrix\Sender\Entity;
use Bitrix\Sender\GroupTable;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Internals\Model;
use Bitrix\Sender\Internals\SqlBatch;
use Bitrix\Sender\MailingGroupTable;
use Bitrix\Sender\MailingSubscriptionTable;
use Bitrix\Sender\Message;
use Bitrix\Sender\PostingRecipientTable;
use Bitrix\Sender\PostingTable;
use Bitrix\Sender\Recipient;
use Bitrix\Sender\Runtime\RecipientBuilderJob;
use Bitrix\Sender\Service\GroupQueueService;
use Bitrix\Sender\Service\GroupQueueServiceInterface;

Loc::loadMessages(__FILE__);

/**
 * Class Builder
 * @package Bitrix\Sender\Posting
 */
class Builder
{
	/** @var bool $checkDuplicates Check duplicates. */
	protected $checkDuplicates = true;

	/** @var array $groupCount Group count. */
	protected $groupCount = array();

	/** @var integer $postingId Posting ID. */
	protected $postingId;

	/** @var array $postingData Posting data. */
	protected $postingData;

	/** @var integer $typeId Type ID. */
	protected $typeId;

	/**
	 * @var bool $result
	 */
	private $result;

	/**
	 * @var GroupQueueServiceInterface $groupQueueService
	 */
	private $groupQueueService;

	/**
	 * @var Message\Configuration
	 */
	private $messageConfiguration;

	/**
	 * Create instance.
	 *
	 * @return static
	 */
	public static function create()
	{
		return new static();
	}

	/**
	 * @return bool
	 */
	public function isResult(): bool
	{
		return $this->result;
	}

	/**
	 * @param bool $result
	 *
	 * @return Builder
	 */
	public function setResult(bool $result): Builder
	{
		$this->result = $result;

		return $this;
	}


	/**
	 * Shaper constructor.
	 *
	 * @param integer|null $postingId Posting ID.
	 * @param bool $checkDuplicates Check duplicates.
	 */
	public function __construct($postingId = null, $checkDuplicates = true)
	{
		$this->groupQueueService = new GroupQueueService();
		if ($postingId)
		{
			$this->setResult($this->run($postingId, $checkDuplicates));
		}
	}

	/**
	 * Load.
	 *
	 * @param integer $postingId Posting ID.
	 * @param bool $checkDuplicates Check duplicates.
	 *
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function run($postingId, $checkDuplicates = true): bool
	{
		$postingData = PostingTable::getList(array(
			'select' => array(
				'*',
				'MESSAGE_TYPE' => 'MAILING_CHAIN.MESSAGE_CODE',
				'WAITING_RECIPIENT' => 'MAILING_CHAIN.WAITING_RECIPIENT',
				'MAILING_STATUS' => 'MAILING_CHAIN.STATUS',
				'MESSAGE_ID' => 'MAILING_CHAIN.MESSAGE_ID'
			),
			'filter' => array('ID' => $postingId),
			'limit' => 1
		))->fetch();

		if(!$postingData)
		{
			return true;
		}

		if ($postingData['MAILING_STATUS'] === Model\LetterTable::STATUS_END)
		{
			Model\LetterTable::update($postingData['MAILING_CHAIN_ID'], [
				'WAITING_RECIPIENT' => 'N'
			]);

			return true;
		}

		$entityProcessed = $this->groupQueueService->isEntityProcessed(
			Model\GroupQueueTable::TYPE['POSTING'],
			$postingId
		);

		if (
			$postingData['MAILING_STATUS'] === Model\LetterTable::STATUS_SEND
			&& $postingData['WAITING_RECIPIENT'] === 'N'
			&& !$entityProcessed
		)
		{
			return true;
		}

		$this->postingData = $postingData;
		$this->checkDuplicates = $checkDuplicates;
		$this->postingId = $postingId;
		$this->groupCount = array();

		try
		{
			$this->messageConfiguration =
				Message\Adapter::getInstance($postingData['MESSAGE_TYPE'])
					->loadConfiguration($postingData['MESSAGE_ID'])
			;
		}
		catch (ArgumentException $e)
		{
			return true;
		}

		if(!$checkDuplicates)
		{
			if($this->postingData['STATUS'] === PostingTable::STATUS_NEW)
			{
				self::clean($postingId);
				$this->checkDuplicates = false;
			}
		}

		$messageFields = Model\MessageFieldTable::getList(
			['filter' => ['=MESSAGE_ID' => $postingData['MESSAGE_ID']]]
		)->fetchAll();

		$personalizeFields = [];
		foreach ($messageFields as $messageField)
		{
			if (!in_array(
				$messageField['CODE'],
				[
					'MESSAGE_PERSONALIZE',
					'SUBJECT_PERSONALIZE',
					'TITLE_PERSONALIZE'
				]
			))
			{
				continue;
			}

			$personalizeFields[$messageField['CODE']] =
				json_decode($messageField['VALUE'], true)[1];
		}

		try
		{
			if ($postingData['WAITING_RECIPIENT'] !== 'Y')
			{
				Model\LetterTable::update($postingData['MAILING_CHAIN_ID'], [
					'WAITING_RECIPIENT' => 'Y'
				]);
			}

			$groups = $this->prepareGroups();
			$message = Message\Adapter::create($this->postingData['MESSAGE_TYPE']);
			foreach ($message->getSupportedRecipientTypes() as $typeId)
			{
				if (!Recipient\Type::getCode($typeId))
				{
					continue;
				}

				$this->typeId = $typeId;
				$this->runForRecipientType($personalizeFields, $groups);
			}
		} catch (NotCompletedException $e)
		{
			return false;
		}

		Model\PostingTable::update(
			$postingId,
			array(
				'COUNT_SEND_ALL' => PostingRecipientTable::getCount(array('POSTING_ID' => $postingId))
			)
		);

		$usedGroups = [];
		foreach ($groups as $group)
		{
			if ($group['GROUP_ID'] && !isset($usedGroups[$group['GROUP_ID']]))
			{
				RecipientBuilderJob::removeAgentFromDB($this->postingId);

				$this->groupQueueService->releaseGroup(
					Model\GroupQueueTable::TYPE['POSTING'],
					$this->postingId,
					$group['GROUP_ID']
				);
				$usedGroups[$group['GROUP_ID']] = $group['GROUP_ID'];
			}
		}

		$this->postingData['WAITING_RECIPIENT'] = 'N';
		Model\LetterTable::update($this->postingData['MAILING_CHAIN_ID'], [
			'WAITING_RECIPIENT' => $this->postingData['WAITING_RECIPIENT']
		]);

		return true;
	}

	protected function prepareGroups()
	{
		$groups = [];
		$groups = array_merge($groups, $this->getLetterConnectors($this->postingData['MAILING_CHAIN_ID']));
		$groups = array_merge($groups, $this->getSubscriptionConnectors($this->postingData['MAILING_ID']));

		foreach ($groups as $group)
		{
			if ($group['GROUP_ID'] && !GroupTable::getById($group['GROUP_ID'])->fetch())
			{
				continue;
			}

			$rebuild = $this->needsRebuildGroup($group);
			if ($group['GROUP_ID'])
			{
				$this->groupQueueService
					->addToDB(Model\GroupQueueTable::TYPE['POSTING'], $this->postingId, $group['GROUP_ID']);
			}

			if ($rebuild)
			{
				SegmentDataBuilder::actualize($group['GROUP_ID'], true);
				$this->stopRecipientListBuilding();
			}

			if ($group['STATUS'] !== GroupTable::STATUS_READY_TO_USE)
			{
				SegmentDataBuilder::checkIsSegmentPrepared($group['GROUP_ID']);
				$this->stopRecipientListBuilding();
			}
		}

		// fetch all connectors for getting emails
		array_walk($groups,
			function(&$group)
			{
				$group['INCLUDE'] = (bool)$group['INCLUDE'];
			}
		);

		// sort groups by include value
		usort(
			$groups,
			function ($a, $b)
			{
				if ($a['INCLUDE'] == $b['INCLUDE'])
				{
					return 0;
				}

				return ($a['INCLUDE'] > $b['INCLUDE']) ? -1 : 1;
			}
		);

		return $groups;
	}

	private function needsRebuildGroup($group)
	{
		$isNewOrDone = in_array($group['STATUS'], [GroupTable::STATUS_NEW, GroupTable::STATUS_DONE]);
		$isReadyAndReleased =
			isset($group['GROUP_ID'])
			&& $group['STATUS'] === GroupTable::STATUS_READY_TO_USE
			&& $this->groupQueueService->isReleased($group['GROUP_ID']);

		return $isNewOrDone || $isReadyAndReleased;
	}

	protected function runForRecipientType($usedPersonalizeFields = [], $groups = [])
	{
		// import recipients
		foreach($groups as $group)
		{
			if (is_array($group['ENDPOINT']) && !(isset($group['CONNECTOR']) && $group['CONNECTOR'] instanceof Connector\Base))
			{
				$group['CONNECTOR'] = Connector\Manager::getConnector($group['ENDPOINT']);
			}

			if(empty($group['CONNECTOR']))
			{
				continue;
			}

			$connector = $group['CONNECTOR'];
			$connector->setDataTypeId($this->typeId);
			if (is_array($group['ENDPOINT']['FIELDS']))
			{
				$connector->setCheckAccessRights(false);
				$connector->setFieldValues($group['ENDPOINT']['FIELDS']);
			}

			$this->fill(
				$connector,
				$group,
				$usedPersonalizeFields
			);
		}
	}

	protected function stopRecipientListBuilding()
	{
		RecipientBuilderJob::removeAgentFromDB($this->postingData['ID']);
		RecipientBuilderJob::addEventAgent($this->postingData['ID']);

		Model\LetterTable::update($this->postingData['MAILING_CHAIN_ID'], [
			'WAITING_RECIPIENT' => $this->postingData['MAILING_STATUS'] !== Model\LetterTable::STATUS_END ?  'Y' : 'N'
		]);

		throw new NotCompletedException();
	}
	protected static function clean($postingId)
	{
		$primary = array('POSTING_ID' => $postingId);
		PostingRecipientTable::deleteList($primary);
		Model\PostingTable::update(
			$postingId,
			array(
				'COUNT_SEND_ALL' => 0,
				'COUNT_SEND_NONE' => 0,
				'COUNT_SEND_ERROR' => 0,
				'COUNT_SEND_SUCCESS' => 0,
			)
		);
	}

	protected function getTypeCode()
	{
		return Recipient\Type::getCode($this->typeId);
	}

	protected function getSubscriptionConnectors($campaignId)
	{
		$groups = array();
		$groups[] = array(
			'INCLUDE' => true,
			'ENDPOINT' => array('FIELDS' => array('MAILING_ID' => $campaignId)),
			'GROUP_ID' => null,
			'STATUS' => GroupTable::STATUS_READY_TO_USE,
			'FILTER_ID' => null,
			'CONNECTOR' => new Integration\Sender\Connectors\Subscriber
		);
		$groups[] = array(
			'INCLUDE' => false,
			'ENDPOINT' => array('FIELDS' => array('MAILING_ID' => $campaignId)),
			'GROUP_ID' => null,
			'STATUS' => GroupTable::STATUS_READY_TO_USE,
			'FILTER_ID' => null,
			'CONNECTOR' => new Integration\Sender\Connectors\UnSubscribers
		);

		return $groups;
	}

	/**
	 * @param array $data
	 * @param string|null $typeCode
	 * @return bool
	 */
	public function isCorrectData(array $data, ?string $typeCode): bool
	{
		return (!isset($data[$typeCode]) || !$data[$typeCode])
			&& !(
				isset($data['FIELDS'])
				&& (
					(int)$data['FIELDS']['CRM_ENTITY_TYPE_ID'] === \CCrmOwnerType::Lead
					|| ($data['FIELDS']['CRM_ENTITY_TYPE'] === \CCrmOwnerType::LeadName)
				)
				&& !empty($data['FIELDS']['CRM_ENTITY_ID'])
				&& ((int)$this->typeId === \Bitrix\Sender\Recipient\Type::CRM_LEAD_ID)
			);
	}

	protected function getCampaignGroups($campaignId)
	{
		$groups = array();
		$groupConnectorDb = MailingGroupTable::getList(array(
			'select' => array(
				'INCLUDE',
				'CONNECTOR_ENDPOINT' => 'GROUP.GROUP_CONNECTOR.ENDPOINT',
				'GROUP_ID'
			),
			'filter' => array(
				'=MAILING_ID' => $campaignId,
			),
			'order' => array('INCLUDE' => 'DESC', 'GROUP_ID' => 'ASC')
		));
		while($group = $groupConnectorDb->fetch())
		{
			$groups[] = array(
				'INCLUDE' => $group['INCLUDE'],
				'ENDPOINT' => $group['CONNECTOR_ENDPOINT'],
				'GROUP_ID' => $group['GROUP_ID'],
				'CONNECTOR' => null
			);
		}

		return $groups;
	}

	protected function getLetterConnectors($letterId)
	{
		$groups = array();
		$groupConnectors = Model\LetterSegmentTable::getList(array(
			'select' => array(
				'INCLUDE',
				'STATUS' => 'SEGMENT.STATUS',
				'FILTER_ID' => 'SEGMENT.GROUP_CONNECTOR.FILTER_ID',
				'CONNECTOR_ENDPOINT' => 'SEGMENT.GROUP_CONNECTOR.ENDPOINT',
				'SEGMENT_ID'
			),
			'filter' => array(
				'=LETTER_ID' => $letterId,
			),
			'order' => array('INCLUDE' => 'DESC', 'LETTER_ID' => 'ASC')
		));
		while($group = $groupConnectors->fetch())
		{
			$groups[] = array(
				'INCLUDE' => $group['INCLUDE'],
				'ENDPOINT' => $group['CONNECTOR_ENDPOINT'],
				'GROUP_ID' => $group['SEGMENT_ID'],
				'FILTER_ID' => $group['FILTER_ID'],
				'STATUS' => $group['STATUS'],
				'CONNECTOR' => null
			);
		}

		return $groups;
	}

	private function isExcluded(bool $include, $row): bool
	{
		return 	$include
			&& (
				$row['BLACKLISTED'] === 'Y' ||
				$row['IS_UNSUB'] === 'Y' ||
				$row['IS_MAILING_UNSUB'] === 'Y' ||
				(
					$this->messageConfiguration->get('APPROVE_CONFIRMATION', 'N') === 'Y' &&
					Consent::isUnsub(
						$row['CONSENT_STATUS'],
						$row['CONSENT_REQUEST'],
						$this->postingData['MESSAGE_TYPE']
					)
				)
			);
	}

	protected function setRecipientIdentificators(array &$dataList, bool $include = true)
	{
		if (count($dataList) === 0)
		{
			return;
		}

		$codes = array_keys($dataList);
		$tableName = ContactTable::getTableName();
		$subsTableName = MailingSubscriptionTable::getTableName();

		$existed = [];
		$contactCodeFilter = [];

		$connection = Application::getConnection();
		$primariesString = SqlBatch::getInString($codes);

		$recipientDb = $connection->query(
			"select 
			c.ID, 
			c.NAME, 
			c.CODE, 
			c.BLACKLISTED, 
			c.CONSENT_STATUS, 
			c.CONSENT_REQUEST, 
			c.IS_UNSUB, 
			s.IS_UNSUB as IS_MAILING_UNSUB " .
			"from $tableName c " .
			"left join $subsTableName s on " .
				"c.ID = s.CONTACT_ID " .
				"and s.MAILING_ID=" . (int) $this->postingData['MAILING_ID'] . " " .
			"where c.TYPE_ID = " . (int) $this->typeId . " and c.CODE in ($primariesString)"
		);
		while ($row = $recipientDb->fetch())
		{
			$existed[] = $row['CODE'];
			$dataList[$row['CODE']]['CONTACT_ID'] = $row['ID'];
			$dataList[$row['CODE']]['EXCLUDED'] = $this->isExcluded($include, $row);

			$name = $dataList[$row['CODE']]['NAME'] ?? null;
			if ($name && $name !== $row['NAME'])
			{
				$contactCodeFilter[] = $row['CODE'];
			}
		}

		// update existed contact names
		$this->updateContacts($dataList, $contactCodeFilter);

		// exit if no new contacts
		if (count($existed) === count($codes))
		{
			return;
		}

		// add new contacts
		$list = array_diff($codes, $existed);
		$batch = [];
		$insertDate = new DateTime();
		$updateFieldsOnDuplicate = ['DATE_UPDATE'];
		foreach ($list as $code)
		{
			$batchItem = [
				'TYPE_ID' => $this->typeId,
				'CODE' => $code,
				'DATE_INSERT' => $insertDate,
				'DATE_UPDATE' => $insertDate,
			];

			$key = 'NAME';
			if (isset($dataList[$key]) && $dataList[$key])
			{
				$batchItem[$key] = $dataList[$key];
				if (!in_array($key, $updateFieldsOnDuplicate))
				{
					$updateFieldsOnDuplicate[] = $key;
				}
			}

			$batch[] = $batchItem;
		}


		SqlBatch::insert($tableName, $batch, $updateFieldsOnDuplicate, ContactTable::getConflictFields());


		$recipientDb = $connection->query(
			"select ID, CODE " .
			"from $tableName " .
			"where TYPE_ID = " . (int) $this->typeId . " and CODE in ($primariesString)"
		);
		while ($row = $recipientDb->fetch())
		{
			$dataList[$row['CODE']]['CONTACT_ID'] = $row['ID'];
		}
	}

	protected function checkUsedFields($entityType, $ids, $usedPersonalizeFields, &$dataList)
	{
		$usedFields = [];
		foreach ($usedPersonalizeFields as $personalizeField)
		{
			foreach ($personalizeField as $usedField)
			{
				$usedFieldExploded = explode('.', $usedField);
				if (
					$entityType == $usedFieldExploded[0] &&
					isset
					(
						$usedFieldExploded[1]
					))
				{
					unset($usedFieldExploded[0]);
					$usedFields[$usedField] = implode('.', $usedFieldExploded);
				}
			}
		}
		$fields = Integration\Crm\Connectors\Helper::getData(
			$entityType, $ids, $usedFields
		);

		foreach ($fields as &$entity)
		{
			foreach ($entity as $key => $field)
			{
				$entity[$entityType.'.'.$key] = $field;
				unset($entity[$key]);
			}
		}

		foreach($dataList as &$data)
		{
			if(
				isset($fields[(int)$data['FIELDS']['CRM_ENTITY_ID']])
				&& $data['FIELDS']['CRM_ENTITY_TYPE'] === $entityType
			)
			{
				$data['FIELDS'] = array_merge(
					$data['FIELDS'],
					$fields[$data['FIELDS']['CRM_ENTITY_ID']]
				);
			}
		}

		return $usedFields;
	}

	protected function fill(Connector\Base $connector, $group, $usedPersonalizeFields = [])
	{
		$count = 0;

		$typeCode = $this->getTypeCode();

		$isIncrementally = $connector instanceof Connector\IncrementallyConnector && $group['FILTER_ID'];
		if ($isIncrementally)
		{
			$segmentBuilder = new SegmentDataBuilder($group['GROUP_ID'], $group['FILTER_ID'], $group['ENDPOINT']);

			if (!$segmentBuilder->isBuildingCompleted())
			{
				throw new NotCompletedException();
			}
		}

		$result = $isIncrementally
			? $segmentBuilder->getPreparedData()
			: $connector->getResult();

		while (true)
		{
			$dataList = array();
			$maxPart = 500;

			while ($data = $result->fetch())
			{
				if ($this->isCorrectData($data, $typeCode))
				{
					continue;
				}

				if (!isset($data[$typeCode]) && ((int)$this->typeId === Recipient\Type::CRM_LEAD_ID))
				{
					$data[$typeCode] = $data['FIELDS']['CRM_ENTITY_ID'];
				}

				$primary = Recipient\Normalizer::normalize($data[$typeCode], $this->typeId);
				if ($primary == '')
				{
					continue;
				}
				$dataList[$primary] = $data;

				$count++;

				$maxPart--;
				if ($maxPart == 0)
				{
					break;
				}
			}

			if (count($dataList) === 0)
			{
				break;
			}
			$this->setRecipientIdentificators($dataList, $group['INCLUDE']);

			if ($group['INCLUDE'])
			{
				// add address if not exists
				if ($this->checkDuplicates)
				{
					$primariesString = SqlBatch::getInString(array_keys($dataList));
					$connection = Application::getConnection();
					$rowDb = $connection->query(
						"select r.CODE " .
						"from b_sender_posting_recipient pr, b_sender_contact r " .
						"where pr.CONTACT_ID = r.ID " .
						"and pr.POSTING_ID = " . (int) $this->postingId . " " .
						"and r.TYPE_ID = " . (int) $this->typeId . " " .
						"and r.CODE in ($primariesString)"
					);
					while ($row = $rowDb->fetch())
					{
						unset($dataList[$row['CODE']]);
					}
				}

				if (empty($dataList))
				{
					continue;
				}

				if(
					count($usedPersonalizeFields) > 0
				)
				{
					$preparedFields = [];

					foreach($dataList as $data)
					{
						if(!isset($data['FIELDS']))
						{
							continue;
						}

						$field = $data['FIELDS'];
						if(!isset($preparedFields[$field['CRM_ENTITY_TYPE']]))
						{
							$preparedFields[$field['CRM_ENTITY_TYPE']] = [];
						}
						$preparedFields[$field['CRM_ENTITY_TYPE']][] = $field['CRM_ENTITY_ID'];
					}

					foreach ($preparedFields as $entityType => $ids)
					{
						$this->checkUsedFields(
							$entityType, $ids, $usedPersonalizeFields, $dataList
						);
					}
				}

				$this->addPostingRecipients($dataList);
			}
			else
			{
				$this->removePostingRecipients($dataList);
			}
		}

		if (!$group['GROUP_ID'])
		{
			return;
		}

		$this->incGroupCounters($group['GROUP_ID'], $count);
	}

	protected function removePostingRecipients(array &$list)
	{
		$primaries = array();
		foreach($list as $code => $data)
		{
			if (!isset($data['CONTACT_ID']) || !$data['CONTACT_ID'])
			{
				continue;
			}
			$primaries[] = (int) $data['CONTACT_ID'];
		}

		if (count($primaries) === 0)
		{
			return;
		}

		$connection = Application::getConnection();
		$primariesString = implode(',', $primaries);
		$connection->query(
			"delete " .
			"from b_sender_posting_recipient " .
			"where POSTING_ID = " . (int) $this->postingId . " " .
			"and CONTACT_ID in (" . $primariesString . ")"
		);
	}

	protected function updateContacts(array &$list, array $codeFilter)
	{
		$fields = [];
		foreach ($codeFilter as $code)
		{
			if (!isset($list[$code]))
			{
				continue;
			}

			$item = $list[$code];
			$fields[] = ['ID' => $item['CONTACT_ID'], 'NAME' => $item['NAME']];
		}

		SqlBatch::update(ContactTable::getTableName(), $fields);
	}

	protected function addPostingRecipients(array &$list)
	{
		$dataList = array();
		foreach($list as $code => $data)
		{
			if (isset($data['EXCLUDED']) && $data['EXCLUDED'])
			{
				continue;
			}

			$recipientInsert = array(
				'CONTACT_ID' => (int) $data['CONTACT_ID'],
				'STATUS' => PostingRecipientTable::SEND_RESULT_NONE,
				'POSTING_ID' => (int) $this->postingId,
				'USER_ID' => null,
				'FIELDS' => null
			);

			if (array_key_exists('USER_ID', $data) && intval($data['USER_ID']) > 0)
			{
				$recipientInsert['USER_ID'] = intval($data['USER_ID']);
			}

			if (array_key_exists('FIELDS', $data) && count($data['FIELDS']) > 0)
			{
				$recipientInsert['FIELDS'] =  serialize($data['FIELDS']);
			}

			$dataList[] = $recipientInsert;
		}

		if(count($dataList) == 0)
		{
			return;
		}

		SqlBatch::insert(
			PostingRecipientTable::getTableName(),
			$dataList,
			['USER_ID', 'FIELDS'],
			PostingRecipientTable::getConflictFields(),
		);
	}

	protected function incGroupCounters($groupId = null, $count = 0)
	{
		if (!$groupId)
		{
			return;
		}

		if (array_key_exists($groupId, $this->groupCount))
		{
			$this->groupCount[$groupId] += $count;
		}
		else
		{
			$this->groupCount[$groupId] = $count;
		}

	}
}
