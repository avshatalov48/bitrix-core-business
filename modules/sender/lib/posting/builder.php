<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Posting;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

use Bitrix\Sender\Connector;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Entity;
use Bitrix\Sender\MailingSubscriptionTable;
use Bitrix\Sender\Recipient;
use Bitrix\Sender\Message;

use Bitrix\Sender\PostingTable;
use Bitrix\Sender\ContactTable;
use Bitrix\Sender\MailingGroupTable;
use Bitrix\Sender\PostingRecipientTable;
use Bitrix\Sender\Internals\Model;
use Bitrix\Sender\Internals\SqlBatch;

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
	 * Create instance.
	 *
	 * @return static
	 */
	public static function create()
	{
		return new static();
	}

	/**
	 * Shaper constructor.
	 *
	 * @param integer|null $postingId Posting ID.
	 * @param bool $checkDuplicates Check duplicates.
	 */
	public function __construct($postingId = null, $checkDuplicates = true)
	{
		if ($postingId)
		{
			$this->run($postingId, $checkDuplicates);
		}
	}

	/**
	 * Load.
	 *
	 * @param integer $postingId Posting ID.
	 * @param bool $checkDuplicates Check duplicates.
	 */
	public function run($postingId, $checkDuplicates = true)
	{
		$postingData = PostingTable::getList(array(
			'select' => array('*', 'MESSAGE_TYPE' => 'MAILING_CHAIN.MESSAGE_CODE'),
			'filter' => array('ID' => $postingId),
			'limit' => 1
		))->fetch();
		if(!$postingData)
		{
			return;
		}


		$this->postingData = $postingData;
		$this->checkDuplicates = $checkDuplicates;
		$this->postingId = $postingId;
		$this->groupCount = array();


		if(!$checkDuplicates)
		{
			if($this->postingData['STATUS'] === PostingTable::STATUS_NEW)
			{
				self::clean($postingId);
				$this->checkDuplicates = false;
			}
		}


		$message = Message\Adapter::create($this->postingData['MESSAGE_TYPE']);
		foreach ($message->getSupportedRecipientTypes() as $typeId)
		{
			if (!Recipient\Type::getCode($typeId))
			{
				continue;
			}

			$this->typeId = $typeId;
			$this->runForRecipientType();
		}


		Model\PostingTable::update(
			$postingId,
			array(
				'COUNT_SEND_ALL' => PostingRecipientTable::getCount(array('POSTING_ID' => $postingId))
			)
		);
	}

	protected function runForRecipientType()
	{
		// fetch all connectors for getting emails
		$groups = array();
		$groups = array_merge($groups, $this->getLetterConnectors($this->postingData['MAILING_CHAIN_ID']));
		$groups = array_merge($groups, $this->getSubscriptionConnectors($this->postingData['MAILING_ID']));

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
				$connector->setFieldValues($group['ENDPOINT']['FIELDS']);
			}

			$this->fill($connector, $group['INCLUDE'], $group['GROUP_ID']);
		}

		// update group counter of addresses
		foreach($this->groupCount as $groupId => $count)
		{
			Entity\Segment::updateAddressCounters(
				$groupId,
				array(
					new Connector\DataCounter(array(
						$this->typeId => $count
					))
				)
			);
		}
	}

	protected static function clean($postingId)
	{
		$primary = array('POSTING_ID' => $postingId);
		PostingRecipientTable::delete($primary);
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
			'CONNECTOR' => new Integration\Sender\Connectors\Subscriber
		);
		$groups[] = array(
			'INCLUDE' => false,
			'ENDPOINT' => array('FIELDS' => array('MAILING_ID' => $campaignId)),
			'GROUP_ID' => null,
			'CONNECTOR' => new Integration\Sender\Connectors\UnSubscribers
		);

		return $groups;
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
				'CONNECTOR' => null
			);
		}

		return $groups;
	}

	protected function setRecipientIdentificators(array &$dataList)
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
			"select c.ID, c.NAME, c.CODE, c.BLACKLISTED, s.IS_UNSUB " .
			"from $tableName c " .
			"left join $subsTableName s on " .
				"c.ID = s.CONTACT_ID " .
				"and s.MAILING_ID=" . (int) $this->postingData['MAILING_ID'] . " " .
			"where c.TYPE_ID = " . (int) $this->typeId . " and c.CODE in ($primariesString)"
		);
		while ($row = $recipientDb->fetch())
		{
			$existed[] = $row['CODE'];

			if ($row['BLACKLISTED'] === 'Y' || $row['IS_UNSUB'] === 'Y')
			{
				unset($dataList[$row['CODE']]);
				continue;
			}

			$dataList[$row['CODE']]['CONTACT_ID'] = $row['ID'];

			$name = isset($dataList[$row['CODE']]['NAME']) ? $dataList[$row['CODE']]['NAME'] : null;
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
		$batch = array();
		$sqlDateTimeFunction = Application::getConnection()->getSqlHelper()->getCurrentDateTimeFunction();
		$updateFieldsOnDuplicate = array(
			array('NAME' => 'DATE_UPDATE', 'VALUE' => $sqlDateTimeFunction),
		);
		foreach ($list as $code)
		{
			$batchItem = array(
				'TYPE_ID' => $this->typeId,
				'CODE' => $code,
				'DATE_INSERT' => array('VALUE' => $sqlDateTimeFunction),
				'DATE_UPDATE' => array('VALUE' => $sqlDateTimeFunction),
			);

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


		SqlBatch::insert($tableName, $batch, $updateFieldsOnDuplicate);


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

	protected function fill(Connector\Base $connector, $isInclude = false, $groupId = null)
	{
		$count = 0;

		$typeCode = $this->getTypeCode();
		$result = $connector->getResult();

		while (true)
		{
			$dataList = array();
			$maxPart = 500;

			while ($data = $result->fetch())
			{
				if (!isset($data[$typeCode]) || !$data[$typeCode])
				{
					continue;
				}

				$primary = Recipient\Normalizer::normalize($data[$typeCode], $this->typeId);
				if (strlen($primary) <= 0)
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

			$this->setRecipientIdentificators($dataList);
			if ($isInclude)
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

				$this->addPostingRecipients($dataList);
			}
			else
			{
				$this->removePostingRecipients($dataList);
			}
		}


		$this->incGroupCounters($groupId, $count);
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
			array('USER_ID', 'FIELDS')
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