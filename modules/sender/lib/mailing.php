<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type as MainType;

use Bitrix\Sender\Internals\Model;

Loc::loadMessages(__FILE__);

class MailingTable extends Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_mailing';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SENDER_ENTITY_MAILING_FIELD_TITLE_NAME')
			),
			'DESCRIPTION' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('SENDER_ENTITY_MAILING_FIELD_TITLE_DESCRIPTION'),
				'validation' => array(__CLASS__, 'validateDescription'),
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new MainType\DateTime(),
			),
			'ACTIVE' => array(
				'data_type' => 'string',
				'default_value' => 'Y'
			),
			'TRACK_CLICK' => array(
				'data_type' => 'string',
				'default_value' => 'N',
			),
			'IS_PUBLIC' => array(
				'data_type' => 'string',
				'default_value' => 'Y',
			),
			'IS_TRIGGER' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => 'N',
			),
			'SORT' => array(
				'data_type' => 'integer',
				'required' => true,
				'default_value' => 100,
				'title' => Loc::getMessage('SENDER_ENTITY_MAILING_FIELD_TITLE_SORT')
			),
			'SITE_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => SITE_ID
			),
			'TRIGGER_FIELDS' => array(
				'data_type' => 'text',
				'serialized' => true
			),
			'EMAIL_FROM' => array(
				'data_type' => 'string',
				'required' => false,
				'title' => Loc::getMessage('SENDER_ENTITY_MAILING_FIELD_TITLE_EMAIL_FROM'),
				'validation' => array('Bitrix\Sender\MailingChainTable', 'validateEmailForm'),
			),
			'CHAIN' => array(
				'data_type' => 'Bitrix\Sender\MailingChainTable',
				'reference' => array('=this.ID' => 'ref.MAILING_ID'),
			),
			'POSTING' => array(
				'data_type' => 'Bitrix\Sender\PostingTable',
				'reference' => array('=this.ID' => 'ref.MAILING_ID'),
			),
			'MAILING_GROUP' => array(
				'data_type' => 'Bitrix\Sender\MailingGroupTable',
				'reference' => array('=this.ID' => 'ref.MAILING_ID'),
			),
			'MAILING_SUBSCRIPTION' => array(
				'data_type' => 'Bitrix\Sender\MailingSubscriptionTable',
				'reference' => array('=this.ID' => 'ref.MAILING_ID'),
			),
			'SUBSCRIBER' => array(
				'data_type' => 'Bitrix\Sender\MailingSubscriptionTable',
				'reference' => array('=this.ID' => 'ref.MAILING_ID', 'ref.IS_UNSUB' => new SqlExpression('?', 'N')),
			),
			'SITE' => array(
				'data_type' => 'Bitrix\Main\SiteTable',
				'reference' => array('=this.SITE_ID' => 'ref.LID'),
			),
		);
	}

	/**
	 * Returns validators for DESCRIPTION field.
	 *
	 * @return array
	 */
	public static function validateDescription()
	{
		return array(
			new Entity\Validator\Length(null, 2000),
		);
	}


	/**
	 * @param Entity\Event $event
	 * @return Entity\EventResult
	 */
	public static function onAfterUpdate(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();

		if(array_key_exists('ACTIVE', $data['fields']))
		{
			if ($data['fields']['ACTIVE'] === 'Y')
			{
				$chain = (new \Bitrix\Sender\Entity\Chain())->load($data['primary']['ID']);
				foreach ($chain->getList() as $letter)
				{
					if (!$letter->getState()->wasStartedSending())
					{
						$letter->wait();
					}
				}
			}

			Runtime\Job::actualizeByCampaignId($data['primary']['ID']);
		}

		if (array_key_exists('ACTIVE', $data['fields']) || array_key_exists('TRIGGER_FIELDS', $data['fields']))
		{
			static::updateChainTrigger($data['primary']['ID']);
		}

		return $result;
	}

	/**
	 * @param Entity\Event $event
	 * @return Entity\EventResult
	 */
	public static function onAfterDelete(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();

		$primary = array('MAILING_ID' => $data['primary']['ID']);
		MailingGroupTable::deleteList($primary);
		MailingChainTable::deleteList($primary);
		MailingSubscriptionTable::deleteList($primary);
		PostingTable::deleteList($primary);

		return $result;
	}

	/*
	 *
	 * @return \Bitrix\Main\DB\Result
	 * */
	public static function getPresetMailingList(array $params = null)
	{
		$resultList = array();
		$event = new \Bitrix\Main\Event('sender', 'OnPresetMailingList');
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getModuleId() === 'sale')
			{
				continue;
			}

			if ($eventResult->getType() == \Bitrix\Main\EventResult::ERROR)
			{
				continue;
			}

			$eventResultParameters = $eventResult->getParameters();

			if (!empty($eventResultParameters))
			{
				if(!empty($params['CODE']))
				{
					$eventResultParametersTmp = array();
					foreach($eventResultParameters as $preset)
					{
						if($params['CODE'] == $preset['CODE'])
						{
							$eventResultParametersTmp[] = $preset;
							break;
						}
					}

					$eventResultParameters = $eventResultParametersTmp;
				}

				$resultList = array_merge($resultList, $eventResultParameters);
			}
		}

		$resultListTmp = Integration\EventHandler::onSenderTriggerCampaignPreset();
		foreach($resultList as $result)
		{
			if(empty($result['TRIGGER']['START']['ENDPOINT']['CODE']))
				continue;

			$trigger = Trigger\Manager::getOnce($result['TRIGGER']['START']['ENDPOINT']);
			if(!$trigger)
				continue;

			$result['TRIGGER']['START']['ENDPOINT']['NAME'] = $trigger->getName();
			if(!empty($result['TRIGGER']['START']['ENDPOINT']['CODE']))
			{
				$trigger = Trigger\Manager::getOnce($result['TRIGGER']['END']['ENDPOINT']);
				if(!$trigger)
					$result['TRIGGER']['END']['ENDPOINT']['NAME'] = $trigger->getName();
			}


			$resultListTmp[] = $result;
		}

		return $resultListTmp;
	}

	public static function checkFieldsChain(Entity\Result $result, $primary, array $fields)
	{
		$id = $primary;
		$errorList = array();
		$errorCurrentNumber = 0;

		foreach($fields as $item)
		{
			$errorCurrentNumber++;

			$chainFields = array(
				'MAILING_ID' => ($id ? $id : 1),
				'ID' => $item['ID'],
				'REITERATE' => 'Y',
				'IS_TRIGGER' => 'Y',
				'EMAIL_FROM' => $item['EMAIL_FROM'],
				'SUBJECT' => $item['SUBJECT'],
				'MESSAGE' => $item['MESSAGE'],
				'TEMPLATE_TYPE' => $item['TEMPLATE_TYPE'],
				'TEMPLATE_ID' => $item['TEMPLATE_ID'],
				'TIME_SHIFT' => intval($item['TIME_SHIFT']),
			);

			$chainId = 0;
			if(!empty($item['ID']))
				$chainId = $item['ID'];

			if($chainId > 0)
			{
				$chain = MailingChainTable::getRowById(array('ID' => $chainId));
				if($chain && $chain['STATUS'] != MailingChainTable::STATUS_WAIT)
				{
					$chainFields['STATUS'] = $chain['STATUS'];
				}
			}

			if(empty($chainFields['STATUS']))
				$chainFields['STATUS'] = MailingChainTable::STATUS_WAIT;

			$chainFields['ID'] = $chainId;

			$resultItem = new Entity\Result;
			MailingChainTable::checkFields($resultItem, null, $chainFields);
			if($resultItem->isSuccess())
			{

			}
			else
			{
				$errorList[$errorCurrentNumber] = $resultItem->getErrors();
			}
		}

		$delimiter = '';
		foreach($errorList as $number => $errors)
		{
			/* @var \Bitrix\Main\Entity\FieldError[] $errors*/
			foreach($errors as $error)
			{
				$result->addError(new Entity\FieldError(
						$error->getField(),
						$delimiter . Loc::getMessage('SENDER_ENTITY_MAILING_CHAIN_ITEM_NUMBER') . $number . ': ' . $error->getMessage(),
						$error->getCode()
					)
				);

				$delimiter = '';
			}

			$delimiter = "\n";
		}


		return $result;
	}

	public static function updateChain($id, array $fields)
	{
		$result = new Entity\Result;

		static::checkFieldsChain($result, $id, $fields);
		if(!$result->isSuccess(true))
			return $result;

		$parentChainId = null;
		$existChildIdList = array();
		foreach($fields as $chainFields)
		{
			$chainId = $chainFields['ID'];
			unset($chainFields['ID']);

			$chainFields['MAILING_ID'] = $id;
			$chainFields['IS_TRIGGER'] = 'Y';
			$chainFields['REITERATE'] = 'Y';
			$chainFields['PARENT_ID'] = $parentChainId;

			// default status
			if($chainId > 0)
			{
				$chain = MailingChainTable::getRowById(array('ID' => $chainId));
				if($chain && $chain['STATUS'] != MailingChainTable::STATUS_WAIT)
				{
					$chainFields['STATUS'] = $chain['STATUS'];
					unset($chainFields['CREATED_BY']);
				}
			}
			if(empty($chainFields['STATUS']))
				$chainFields['STATUS'] = MailingChainTable::STATUS_WAIT;


			// add or update
			if($chainId > 0)
			{
				$existChildIdList[] = $chainId;

				$chainUpdateDb = Model\LetterTable::update($chainId, $chainFields);
				if($chainUpdateDb->isSuccess())
				{

				}
				else
				{
					$result->addErrors($chainUpdateDb->getErrors());
				}
			}
			else
			{
				$chainAddDb = MailingChainTable::add($chainFields);
				if($chainAddDb->isSuccess())
				{
					$chainId = $chainAddDb->getId();
					$existChildIdList[] = $chainId;
				}
				else
				{
					$result->addErrors($chainAddDb->getErrors());
				}
			}

			if(!empty($errorList)) break;

			$parentChainId = null;
			if($chainId !== null)
				$parentChainId = $chainId;
		}

		$deleteChainDb = MailingChainTable::getList(array(
			'select' => array('ID'),
			'filter' => array('MAILING_ID' => $id, '!ID' => $existChildIdList),
		));
		while($deleteChain = $deleteChainDb->fetch())
		{
			Model\LetterTable::delete($deleteChain['ID']);
		}

		static::updateChainTrigger($id);

		return $result;
	}

	public static function getChain($id)
	{
		$result = array();
		$parentId = null;

		do
		{
			$chainDb = MailingChainTable::getList(array(
				'select' => array(
					'ID', 'SUBJECT', 'EMAIL_FROM', 'MESSAGE', 'TIME_SHIFT', 'PARENT_ID',
					'DATE_INSERT', 'PRIORITY', 'LINK_PARAMS', 'TEMPLATE_TYPE', 'TEMPLATE_ID',
					'CREATED_BY', 'CREATED_BY_NAME' => 'CREATED_BY_USER.NAME', 'CREATED_BY_LAST_NAME' => 'CREATED_BY_USER.LAST_NAME'
				),
				'filter' => array('=MAILING_ID' => $id, '=PARENT_ID' => $parentId),
			));

			$parentId = null;
			while($chain = $chainDb->fetch())
			{
				//unset($chain['MESSAGE']);
				$result[] = $chain;
				$parentId = $chain['ID'];
			}


		}while($parentId !== null);


		return $result;
	}

	public static function updateChainTrigger($id)
	{
		// get first item of chain
		$chainDb = MailingChainTable::getList(array(
			'select' => array('ID', 'TRIGGER_FIELDS' => 'MAILING.TRIGGER_FIELDS'),
			'filter' => array('=MAILING_ID' => $id, '=IS_TRIGGER' => 'Y', '=PARENT_ID' => null),
		));

		$chain = $chainDb->fetch();
		if(!$chain) return;
		$chainId = $chain['ID'];

		// get trigger settings from mailing
		$triggerFields = $chain['TRIGGER_FIELDS'];
		if(!is_array($triggerFields))
			$triggerFields = array();

		// init TriggerSettings objects
		$settingsList = array();
		foreach($triggerFields as $key => $point)
		{
			if(empty($point['CODE'])) continue;

			$point['IS_EVENT_OCCUR'] = true;
			$point['IS_PREVENT_EMAIL'] = false;
			$point['SEND_INTERVAL_UNIT'] = 'M';
			$point['IS_CLOSED_TRIGGER'] = ($point['IS_CLOSED_TRIGGER'] == 'Y' ? true : false);

			switch($key)
			{
				case 'END':
					$point['IS_TYPE_START'] = false;
					break;

				case 'START':
				default:
					$point['IS_TYPE_START'] = true;
			}

			$settingsList[] = new Trigger\Settings($point);
		}


		// prepare fields for save
		$mailingTriggerList = array();
		foreach($settingsList as $settings)
		{
			/* @var \Bitrix\Sender\Trigger\Settings $settings */
			$trigger = Trigger\Manager::getOnce($settings->getEndpoint());
			if($trigger)
			{
				$triggerFindId = $trigger->getFullEventType() . "/" .((int) $settings->isTypeStart());
				$mailingTriggerList[$triggerFindId] = array(
					'IS_TYPE_START' => $settings->isTypeStart(),
					'NAME' => $trigger->getName(),
					'EVENT' => $trigger->getFullEventType(),
					'ENDPOINT' => $settings->getArray(),
				);
			}
		}


		// add new, update exists, delete old rows
		$triggerDb = MailingTriggerTable::getList(array(
			'select' => array('EVENT', 'MAILING_CHAIN_ID', 'IS_TYPE_START'),
			'filter' => array('=MAILING_CHAIN_ID' => $chainId)
		));
		while($trigger = $triggerDb->fetch())
		{
			$triggerFindId = $trigger['EVENT'] . "/" . ((int) $trigger['IS_TYPE_START']);
			if(!isset($mailingTriggerList[$triggerFindId]))
			{
				MailingTriggerTable::delete($trigger);
			}
			else
			{
				MailingTriggerTable::update($trigger, $mailingTriggerList[$triggerFindId]);
				unset($mailingTriggerList[$triggerFindId]);
			}
		}

		foreach($mailingTriggerList as $triggerFindId => $settings)
		{
			/** @var array $settings */
			$settings['MAILING_CHAIN_ID'] = $chainId;
			MailingTriggerTable::add($settings);
		}

		Trigger\Manager::actualizeHandlerForChild();
	}

	public static function setWasRunForOldData($id, $state)
	{
		$state = (bool) $state == true ? 'Y' : 'N';
		$mailing = static::getRowById($id);
		if(!$mailing)
		{
			return;
		}

		$triggerFields = $mailing['TRIGGER_FIELDS'];
		if(!is_array($triggerFields))
		{
			return;
		}

		if(!isset($triggerFields['START']))
		{
			return;
		}

		$triggerFields['START']['WAS_RUN_FOR_OLD_DATA'] = $state;
		$updateDb = static::update($id, array('TRIGGER_FIELDS' => $triggerFields));
		if($updateDb->isSuccess())
		{
			static::updateChainTrigger($id);
		}
	}

	public static function getPersonalizeList($id)
	{
		$result = array();

		// fetch all connectors for getting emails
		$groupConnectorDb = MailingGroupTable::getList(array(
			'select' => array(
				'CONNECTOR_ENDPOINT' => 'GROUP.GROUP_CONNECTOR.ENDPOINT',
				'GROUP_ID'
			),
			'filter' => array(
				'MAILING_ID' => $id,
				'INCLUDE' => true,
			),
			'order' => array('GROUP_ID' => 'ASC')
		));
		while($groupConnector = $groupConnectorDb->fetch())
		{
			$connector = null;
			if(is_array($groupConnector['CONNECTOR_ENDPOINT']))
			{
				$connector = Connector\Manager::getConnector($groupConnector['CONNECTOR_ENDPOINT']);
			}

			if(!$connector)
			{
				continue;
			}

			$result = array_merge($result, $connector->getPersonalizeList());
		}

		return $result;
	}

	public static function getChainPersonalizeList($id)
	{
		$result = array();

		$mailingDb = MailingTable::getList(array(
			'select' => array('ID', 'TRIGGER_FIELDS'),
			'filter' => array(
				//'=ACTIVE' => 'Y',
				'=IS_TRIGGER' => 'Y',
				'=ID' => $id
			),
		));
		if(!$mailing = $mailingDb->fetch())
			return $result;

		$triggerFields = $mailing['TRIGGER_FIELDS'];
		if(!is_array($triggerFields))
			$triggerFields = array();

		$settingsList = array();
		foreach($triggerFields as $key => $point)
		{
			if(empty($point['CODE'])) continue;

			$point['IS_EVENT_OCCUR'] = true;
			$point['IS_PREVENT_EMAIL'] = false;
			$point['SEND_INTERVAL_UNIT'] = 'M';

			switch($key)
			{
				case 'END':
					$point['IS_TYPE_START'] = false;
					break;

				case 'START':
				default:
					$point['IS_TYPE_START'] = true;
			}

			$settingsList[] = new Trigger\Settings($point);
		}

		foreach($settingsList as $settings)
		{
			/* @var \Bitrix\Sender\Trigger\Settings $settings */
			if(!$settings->isTypeStart())
				continue;

			$trigger = Trigger\Manager::getOnce($settings->getEndpoint());
			if($trigger)
			{
				$result = array_merge($result, $trigger->getPersonalizeList());
			}
		}

		return $result;
	}

	public static function getMailingSiteId($mailingId)
	{
		static $cache;
		if (!$cache || !($cache[$mailingId] ?? false))
		{
			$mailing = self::getById($mailingId)->fetch();
			$cache[$mailingId] = $mailing['SITE_ID'];
		}

		return $cache[$mailingId];
	}

	/**
	 * @param array $filter
	 * @return \Bitrix\Main\DB\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function deleteList(array $filter): \Bitrix\Main\DB\Result
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();

		\CTimeZone::disable();
		$sql = sprintf(
			'DELETE FROM %s WHERE %s',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			Query::buildFilterSql($entity, $filter)
		);
		$res = $connection->query($sql);
		\CTimeZone::enable();

		return $res;
	}
}


class MailingGroupTable extends Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_mailing_group';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'MAILING_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'INCLUDE' => array(
				'data_type' => 'boolean',
				'values' => array(false, true),
				'required' => true,
			),
			'MAILING' => array(
				'data_type' => 'Bitrix\Sender\MailingTable',
				'reference' => array('=this.MAILING_ID' => 'ref.ID'),
			),
			'GROUP' => array(
				'data_type' => 'Bitrix\Sender\GroupTable',
				'reference' => array('=this.GROUP_ID' => 'ref.ID'),
			),
		);
	}

	/**
	 * @param array $filter
	 * @return \Bitrix\Main\DB\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function deleteList(array $filter): \Bitrix\Main\DB\Result
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();

		\CTimeZone::disable();
		$sql = sprintf(
			'DELETE FROM %s WHERE %s',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			Query::buildFilterSql($entity, $filter)
		);
		$res = $connection->query($sql);
		\CTimeZone::enable();

		return $res;
	}
}

/**
 * Class MailingSubscriptionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MailingSubscription_Query query()
 * @method static EO_MailingSubscription_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MailingSubscription_Result getById($id)
 * @method static EO_MailingSubscription_Result getList(array $parameters = array())
 * @method static EO_MailingSubscription_Entity getEntity()
 * @method static \Bitrix\Sender\EO_MailingSubscription createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\EO_MailingSubscription_Collection createCollection()
 * @method static \Bitrix\Sender\EO_MailingSubscription wakeUpObject($row)
 * @method static \Bitrix\Sender\EO_MailingSubscription_Collection wakeUpCollection($rows)
 */
class MailingSubscriptionTable extends Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_mailing_subscription';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'MAILING_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'CONTACT_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'default_value' => new MainType\DateTime(),
			),
			'IS_UNSUB' => array(
				'data_type' => 'string',
			),
			'MAILING' => array(
				'data_type' => 'Bitrix\Sender\MailingTable',
				'reference' => array('=this.MAILING_ID' => 'ref.ID'),
			),
			'CONTACT' => array(
				'data_type' => 'Bitrix\Sender\ContactTable',
				'reference' => array('=this.CONTACT_ID' => 'ref.ID'),
			),
		);
	}

	/**
	 * Get subscription list
	 *
	 * @param array $parameters
	 * @return \Bitrix\Main\DB\Result
	 */
	public static function getSubscriptionList(array $parameters = array())
	{
		$parameters['filter'] = array('=IS_UNSUB' => 'N') + (!isset($parameters['filter']) ? array() : $parameters['filter']);
		return parent::getList($parameters);
	}

	/**
	 * Get un subscription list
	 *
	 * @param array $parameters
	 * @return \Bitrix\Main\DB\Result
	 */
	public static function getUnSubscriptionList(array $parameters = array())
	{
		$parameters['filter'] = array('=IS_UNSUB' => 'Y') + (!isset($parameters['filter']) ? array() : $parameters['filter']);
		return parent::getList($parameters);
	}


	/**
	 * Ad subscription row
	 *
	 * @param array $parameters
	 * @return bool
	 */
	public static function addSubscription(array $parameters = array())
	{
		$primary = array('MAILING_ID' => $parameters['MAILING_ID'], 'CONTACT_ID' => $parameters['CONTACT_ID']);
		$fields = array('IS_UNSUB' => 'N');
		$row = static::getRowById($primary);
		if($row)
		{
			$result = static::update($primary, array('IS_UNSUB' => 'N'));
		}
		else
		{
			$result = static::add($fields + $parameters);
		}

		return $result->isSuccess();
	}

	/**
	 * Ad subscription row.
	 *
	 * @param array $parameters
	 * @return bool
	 */
	public static function addUnSubscription(array $parameters = array())
	{
		$primary = array('MAILING_ID' => $parameters['MAILING_ID'], 'CONTACT_ID' => $parameters['CONTACT_ID']);
		$fields = array('IS_UNSUB' => 'Y');
		$row = static::getRowById($primary);
		if($row)
		{
			$result = static::update($primary, $fields);
		}
		else
		{
			$result = static::add($fields + $parameters);
		}

		return $result->isSuccess();
	}


	/**
	 * @param array $filter
	 * @return \Bitrix\Main\DB\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function deleteList(array $filter): \Bitrix\Main\DB\Result
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();

		\CTimeZone::disable();
		$sql = sprintf(
			'DELETE FROM %s WHERE %s',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			Query::buildFilterSql($entity, $filter)
		);
		$res = $connection->query($sql);
		\CTimeZone::enable();

		return $res;
	}
}