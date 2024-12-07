<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender;

use Bitrix\Main\Entity;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type as MainType;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Sender\Internals\Dto\UpdateContactDtoCollection;
use Bitrix\Sender\Internals\Factory\UpdateContactDtoFactory;
use Bitrix\Sender\Recipient;
use Bitrix\Sender\Service\ContactListUpdateService;
use Bitrix\Sender\Service\ContactUpdateService;

Loc::loadMessages(__FILE__);

/**
 * Class ContactTable
 *
 * @package Bitrix\Sender
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Contact_Query query()
 * @method static EO_Contact_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Contact_Result getById($id)
 * @method static EO_Contact_Result getList(array $parameters = array())
 * @method static EO_Contact_Entity getEntity()
 * @method static \Bitrix\Sender\EO_Contact createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\EO_Contact_Collection createCollection()
 * @method static \Bitrix\Sender\EO_Contact wakeUpObject($row)
 * @method static \Bitrix\Sender\EO_Contact_Collection wakeUpCollection($rows)
 */
class ContactTable extends Entity\DataManager
{
	const CONSENT_STATUS_WAIT = 'W';
	const CONSENT_STATUS_NEW = 'N';
	const CONSENT_STATUS_DENY = 'D';
	const CONSENT_STATUS_ACCEPT = 'A';
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_contact';
	}

	/**
	 * Get map.
	 *
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
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'default_value' => new MainType\DateTime(),
				'required' => true,
			),
			'DATE_UPDATE' => array(
				'data_type' => 'datetime',
				'default_value' => new MainType\DateTime(),
			),
			'TYPE_ID' => array(
				'data_type' => 'integer',
				'default_value' => Recipient\Type::EMAIL,
				'required' => true,
			),
			'CODE' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'NAME' => array(
				'data_type' => 'string',
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
			),
			'USER_ID' => array(
				'data_type' => 'integer',
			),
			'BLACKLISTED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
				'required' => true,
			),
			'IS_READ' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
				'required' => true,
			),
			'IS_CLICK' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
				'required' => true,
			),
			'IS_UNSUB' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
				'required' => true,
			),
			'IS_SEND_SUCCESS' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
				'required' => true,
			),
			'CONSENT_STATUS' => array(
				'data_type' => 'string',
				'default_value' => static::CONSENT_STATUS_NEW,
				'required' => true,
			),
			'CONSENT_REQUEST' => array(
				'data_type' => 'integer',
				'required' => true,
				'default_value' => 0
			),
			'IP' => array(
				'data_type' => 'string',
			),
			'AGENT' => array(
				'data_type' => 'integer',
			),
			'CONTACT_LIST' => array(
				'data_type' => 'Bitrix\Sender\ContactListTable',
				'reference' => array('=this.ID' => 'ref.CONTACT_ID'),
			),
			'MAILING_SUBSCRIPTION' => array(
				'data_type' => 'Bitrix\Sender\MailingSubscriptionTable',
				'reference' => array('=this.ID' => 'ref.CONTACT_ID', 'ref.IS_UNSUB' => new SqlExpression('?', 'N')),
			),
			'MAILING_UNSUBSCRIPTION' => array(
				'data_type' => 'Bitrix\Sender\MailingSubscriptionTable',
				'reference' => array('=this.ID' => 'ref.CONTACT_ID', 'ref.IS_UNSUB' =>  new SqlExpression('?', 'Y')),
			),
		);
	}


	/**
	 * Returns validators for EMAIL_FROM field.
	 *
	 * @return array
	 */
	public static function validateEmail(): array
	{
		return array(
			new Entity\Validator\Length(1, 255),
			array(__CLASS__, 'checkEmail'),
			new Entity\Validator\Unique
		);
	}

	/**
	 * Check email.
	 *
	 * @param string|null $value
	 * @return bool|string
	 */
	public static function checkEmail(?string $value)
	{
		if(empty($value) || check_email($value))
		{
			return true;
		}
		else
		{
			return Loc::getMessage('SENDER_ENTITY_CONTACT_VALID_EMAIL');
		}
	}

	/**
	 * Handler of before add event.
	 *
	 * @param Entity\Event $event Event object.
	 * @return Entity\EventResult
	 */
	public static function onBeforeAdd(Entity\Event $event): Entity\EventResult
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();
		if(isset($data['fields']['EMAIL']))
		{
			$result->modifyFields(array('EMAIL' => Recipient\Normalizer::normalizeEmail($data['fields']['EMAIL'])));
		}

		if(isset($data['fields']['CODE']))
		{
			$typeId = $data['fields']['TYPE_ID'] ?? null;
			$isValid = Recipient\Validator::validate($data['fields']['CODE'], $typeId);
			if (!$isValid)
			{
				$result->addError(new Entity\EntityError(Loc::getMessage('SENDER_ENTITY_CONTACT_VALID_CODE')));
			}
			else
			{
				$result->modifyFields(array(
					'CODE' => Recipient\Normalizer::normalize($data['fields']['CODE'], $typeId)
				));
			}
		}

		return $result;
	}

	/**
	 * Handler of before update event.
	 *
	 * @param Entity\Event $event Event object.
	 * @return Entity\EventResult
	 */
	public static function onBeforeUpdate(Entity\Event $event): Entity\EventResult
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();
		$modify = [];
		if(isset($data['fields']['EMAIL']))
		{
			$modify += array('EMAIL' => Recipient\Normalizer::normalizeEmail($data['fields']['EMAIL']));
			$modify += array('CONSENT_STATUS' => 'N');
		}

		if(isset($data['fields']['CODE']))
		{
			$modify += array( 'CONSENT_STATUS' => 'N' );
			$typeId = $data['fields']['TYPE_ID'] ?? null;
			if (!$typeId)
			{
				$row = static::getRowById($data['primary']['ID']);
				$typeId = $row['TYPE_ID'];
			}
			$isValid = Recipient\Validator::validate($data['fields']['CODE'], $typeId);
			if (!$isValid)
			{
				$result->addError(new Entity\EntityError(Loc::getMessage('SENDER_ENTITY_CONTACT_VALID_CODE')));
			}
			else
			{
				$modify += array('CODE' => Recipient\Normalizer::normalize($data['fields']['CODE'], $typeId));
			}
		}
		$result->modifyFields($modify);

		return $result;
	}

	/**
	 * On after delete.
	 *
	 * @param Entity\Event $event Event.
	 * @return Entity\EventResult
	 * @throws \Exception
	 */
	public static function onAfterDelete(Entity\Event $event): Entity\EventResult
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();

		$primary = array('CONTACT_ID' => $data['primary']['ID']);
		ContactListTable::deleteList($primary);
		MailingSubscriptionTable::deleteList($primary);

		return $result;
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

	/**
	 * @param mixed $primary
	 * @param string $contactStatus
	 * @throws \Exception
	 */
	public static function updateConsentStatus($primary, string $contactStatus)
	{
		ContactTable::update($primary,[
			'CONSENT_STATUS' => $contactStatus,
			'DATE_UPDATE' => new MainType\DateTime(),
			'CONSENT_REQUEST' => new SqlExpression("CONSENT_REQUEST+1"),
		]);
	}

	/**
	 * Add if not exist.
	 *
	 * @param array $ar Data.
	 * @return bool|int
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function addIfNotExist(array $ar)
	{
		$id = false;
		$listId = false;

		if(array_key_exists('LIST_CODE', $ar) && array_key_exists('LIST_NAME', $ar))
		{
			$listId = ListTable::addIfNotExist($ar['LIST_CODE'], $ar['LIST_NAME']);
			unset($ar['LIST_CODE'], $ar['LIST_NAME']);
		}

		$ar['EMAIL'] = mb_strtolower($ar['EMAIL']);
		$contactDb = ContactTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=CODE' => $ar['EMAIL'],
				'=TYPE_ID' => Recipient\Type::EMAIL
			)
		));
		if($contact = $contactDb->fetch())
		{
			$id = $contact['ID'];
		}
		else
		{
			$ar['TYPE_ID'] = Recipient\Type::EMAIL;
			$ar['CODE'] = $ar['EMAIL'];
			unset($ar['EMAIL']);

			$resultAdd = static::add($ar);
			if($resultAdd->isSuccess())
				$id = $resultAdd->getId();
		}

		if($listId && $id)
		{
			ContactListTable::addIfNotExist($id, $listId);
		}

		return $id;
	}

	/**
	 * Check connectors.
	 *
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function checkConnectors()
	{
		$connectorList = Connector\Manager::getConnectorList();
		foreach($connectorList as $connector)
		{
			if($connector->requireConfigure()) continue;
			static::addFromConnector($connector);
		}
	}

	/**
	 * Add from connector.
	 *
	 * @param Connector\Base $connector Connector instance.
	 * @param null|integer $pageNumber Page number.
	 * @param int $timeout Timeout.
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException|\Bitrix\Main\SystemException
	 */
	public static function addFromConnector(Connector\Base $connector, ?int $pageNumber = null, int $timeout = 0): array
	{
		$startTime = microtime(true);
		$withoutNav = empty($pageNumber);
		$result = false;
		$onlyOneLoop = false;
		$rowsInPage = 5;

		$countAll = 0;
		$countProcessed = 0;
		$countUpdated = 0;
		$countAdded = 0;
		$countError = 0;

		$dataDb = $connector->getResult();
		if($dataDb->resourceCDBResult)
		{
			$dataDb = $dataDb->resourceCDBResult;
		}
		elseif($dataDb->resource)
		{
			$dataDb = new \CDBResult($dataDb->resource);
		}
		else
		{
			$dataDb = new \CDBResult();
			$dataDb->initFromArray(array());
		}

		if(!is_subclass_of($dataDb, 'CDBResultMysql'))
		{
			$rowsInPage = 50;
			$onlyOneLoop = true;
		}

		while($timeout==0 || microtime(true)-$startTime < $timeout)
		{
			if(!$withoutNav)
			{
				$dataDb->navStart($rowsInPage, false, $pageNumber);
				$countAll = $dataDb->selectedRowsCount();
			}

			$listId = null;
			while ($row = $dataDb->fetch())
			{
				if($withoutNav)
				{
					$countAll++;
				}

				$countProcessed++;

				if(!$listId)
				{
					$listId = ListTable::addIfNotExist(
						$connector->getModuleId() . '_' . $connector->getCode(),
						Loc::getMessage('CONTACT_PULL_LIST_PREFIX').$connector->getName()
					);
				}

				$id = null;
				$contactDb = ContactTable::getList(array(
					'select' => array('ID'),
					'filter' => array('EMAIL' => $row['EMAIL'])
				));
				if($contactRow = $contactDb->fetch())
				{
					$id = $contactRow['ID'];
					$countUpdated++;
				}
				else
				{
					$resultAdd = static::add(array(
						'NAME' => $row['NAME'],
						'EMAIL' => $row['EMAIL'],
						'USER_ID' => $row['USER_ID']
					));
					if ($resultAdd->isSuccess())
					{
						$id = $resultAdd->getId();
						$countAdded++;
					} else
					{
						$countError++;
					}
				}

				if($id)
					ContactListTable::addIfNotExist($id, $listId);

			}


			if($withoutNav)
			{
				$result = false;
				break;
			}

			if ($dataDb->NavPageCount <= $pageNumber)
			{
				$result = false;
				break;
			}
			else
			{
				$pageNumber++;
				$result = $pageNumber;
			}

			if($onlyOneLoop)
			{
				break;
			}
		}

		if($withoutNav)
		{
			$countProgress = $countAll;
		}
		else
		{
			$countProgress = ($pageNumber-1) * $dataDb->NavPageSize;
			if (!$result || $countProgress > $countAll) $countProgress = $countAll;
		}

		return array(
			'STATUS' => $result,
			'COUNT_ALL' => $countAll,
			'COUNT_PROGRESS' => $countProgress,
			'COUNT_PROCESSED' => $countProcessed,
			'COUNT_NEW' => $countAdded,
			'COUNT_ERROR' => $countError,
		);
	}

	/**
	 * Upload contacts.
	 *
	 * @param array $list List of contacts.
	 * @param bool $isBlacklist Is blacklist.
	 * @param int|null $listId List ID.
	 * @return bool|int
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function upload(array $list, bool $isBlacklist = false, ?int $listId = null)
	{
		$updateCollection = new UpdateContactDtoCollection();
		$updateItemFactory = new UpdateContactDtoFactory($isBlacklist);
		foreach ($list as $item)
		{
			if (is_string($item))
			{
				$item = ['CODE' => $item];
			}

			if (empty($item['CODE']))
			{
				continue;
			}
			$code = trim((string)$item['CODE']);

			$updateItem = $updateItemFactory->make($code, $item['NAME'] ?? null);
			if ($updateItem)
			{
				$updateCollection->append($updateItem);
			}
		}

		// insert contacts
		if ($updateCollection->count() === 0)
		{
			return 0;
		}

		(new ContactUpdateService())->updateByCollection($updateCollection);

		if (!$listId)
		{
			return $updateCollection->count();
		}

		$row = ListTable::getRowById($listId);
		if (!$row)
		{
			return false;
		}

		// insert contacts & lists
		(new ContactListUpdateService())->updateByCollection($updateCollection, $listId);

		return ContactListTable::getCount(array('=LIST_ID' => $listId));
	}

	/**
	 * Get unique key index fields
	 *
	 * @return array|string[]
	 */
	public static function getConflictFields(): array
	{
		return [
			'TYPE_ID',
			'CODE',
		];
	}
}
