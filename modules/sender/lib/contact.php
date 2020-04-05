<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type as MainType;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Sender\Recipient;

Loc::loadMessages(__FILE__);

class ContactTable extends Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_contact';
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
			),
			'USER_ID' => array(
				'data_type' => 'integer',
			),
			/*
			// TODO: rename to EMAIL !!!!!!!
			'EMAIL11111111' => array(
				'column_name' => 'CODE',
				'data_type' => 'string',
				'validation' => array(__CLASS__, "validateEmail")
			),
			*/
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
	public static function validateEmail()
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
	 * @return bool|string
	 */
	public static function checkEmail($value)
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
	public static function onBeforeAdd(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();
		if(isset($data['fields']['EMAIL']))
		{
			$result->modifyFields(array('EMAIL' => Recipient\Normalizer::normalizeEmail($data['fields']['EMAIL'])));
		}

		if(isset($data['fields']['CODE']))
		{
			$typeId = isset($data['fields']['TYPE_ID']) ? $data['fields']['TYPE_ID'] : null;
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
	public static function onBeforeUpdate(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();
		if(isset($data['fields']['EMAIL']))
		{
			$result->modifyFields(array('EMAIL' => Recipient\Normalizer::normalizeEmail($data['fields']['EMAIL'])));
		}

		if(isset($data['fields']['CODE']))
		{
			$typeId = isset($data['fields']['TYPE_ID']) ? $data['fields']['TYPE_ID'] : null;
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
				$result->modifyFields(array(
					'CODE' => Recipient\Normalizer::normalize($data['fields']['CODE'], $typeId)
				));
			}
		}

		return $result;
	}

	/**
	 * On after delete.
	 *
	 * @param Entity\Event $event Event.
	 * @return Entity\EventResult
	 */
	public static function onAfterDelete(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();

		$primary = array('CONTACT_ID' => $data['primary']['ID']);
		ContactListTable::delete($primary);
		MailingSubscriptionTable::delete($primary);

		return $result;
	}


	/**
	 * Add if not exist.
	 *
	 * @param array $ar Data.
	 * @return bool|int
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function addIfNotExist($ar)
	{
		$id = false;
		$listId = false;

		if(array_key_exists('LIST_CODE', $ar) && array_key_exists('LIST_NAME', $ar))
		{
			$listId = ListTable::addIfNotExist($ar['LIST_CODE'], $ar['LIST_NAME']);
			unset($ar['LIST_CODE'], $ar['LIST_NAME']);
		}

		$ar['EMAIL'] = strtolower($ar['EMAIL']);
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
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function addFromConnector(Connector\Base $connector, $pageNumber = null, $timeout = 0)
	{
		$startTime = getmicrotime();
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

		while($timeout==0 || getmicrotime()-$startTime < $timeout)
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
}