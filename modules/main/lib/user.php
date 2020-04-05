<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Main;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Search\MapBuilder;

Loc::loadMessages(__FILE__);

class UserTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_user';
	}

	public static function getUfId()
	{
		return 'USER';
	}

	public static function getMap()
	{
		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'LOGIN' => array(
				'data_type' => 'string'
			),
			'PASSWORD' => array(
				'data_type' => 'string'
			),
			'EMAIL' => array(
				'data_type' => 'string'
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
			'DATE_REGISTER' => array(
				'data_type' => 'datetime'
			),
			'DATE_REG_SHORT' => array(
				'data_type' => 'datetime',
				'expression' => array(
					$helper->getDatetimeToDateFunction('%s'), 'DATE_REGISTER'
				)
			),
			'LAST_LOGIN' => array(
				'data_type' => 'datetime'
			),
			'LAST_LOGIN_SHORT' => array(
				'data_type' => 'datetime',
				'expression' => array(
					$helper->getDatetimeToDateFunction('%s'), 'LAST_LOGIN'
				)
			),
			'LAST_ACTIVITY_DATE' => array(
				'data_type' => 'datetime'
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime'
			),
			'NAME' => array(
				'data_type' => 'string'
			),
			'SECOND_NAME' => array(
				'data_type' => 'string'
			),
			'LAST_NAME' => array(
				'data_type' => 'string'
			),
			'TITLE' => array(
				'data_type' => 'string'
			),
			'EXTERNAL_AUTH_ID' => array(
				'data_type' => 'string'
			),
			'XML_ID' => array(
				'data_type' => 'string'
			),
			'BX_USER_ID' => array(
				'data_type' => 'string'
			),
			'CONFIRM_CODE' => array(
				'data_type' => 'string'
			),
			'LID' => array(
				'data_type' => 'string'
			),
			'LANGUAGE_ID' => array(
				'data_type' => 'string'
			),
			'TIME_ZONE_OFFSET' => array(
				'data_type' => 'integer'
			),
			'PERSONAL_PROFESSION' => array(
				'data_type' => 'string'
			),
			'PERSONAL_PHONE' => array(
				'data_type' => 'string'
			),
			'PERSONAL_MOBILE' => array(
				'data_type' => 'string'
			),
			'PERSONAL_WWW' => array(
				'data_type' => 'string'
			),
			'PERSONAL_ICQ' => array(
				'data_type' => 'string'
			),
			'PERSONAL_FAX' => array(
				'data_type' => 'string'
			),
			'PERSONAL_PAGER' => array(
				'data_type' => 'string'
			),
			'PERSONAL_STREET' => array(
				'data_type' => 'text'
			),
			'PERSONAL_MAILBOX' => array(
				'data_type' => 'string'
			),
			'PERSONAL_CITY' => array(
				'data_type' => 'string'
			),
			'PERSONAL_STATE' => array(
				'data_type' => 'string'
			),
			'PERSONAL_ZIP' => array(
				'data_type' => 'string'
			),
			'PERSONAL_COUNTRY' => array(
				'data_type' => 'string'
			),
			'PERSONAL_BIRTHDAY' => array(
				'data_type' => 'date'
			),
			'PERSONAL_GENDER' => array(
				'data_type' => 'string'
			),
			'PERSONAL_PHOTO' => array(
				'data_type' => 'integer'
			),
			'PERSONAL_NOTES' => array(
				'data_type' => 'text'
			),
			'WORK_COMPANY' => array(
				'data_type' => 'string'
			),
			'WORK_DEPARTMENT' => array(
				'data_type' => 'string'
			),
			'WORK_PHONE' => array(
				'data_type' => 'string'
			),
			'WORK_POSITION' => array(
				'data_type' => 'string'
			),
			'WORK_WWW' => array(
				'data_type' => 'string'
			),
			'WORK_FAX' => array(
				'data_type' => 'string'
			),
			'WORK_PAGER' => array(
				'data_type' => 'string'
			),
			'WORK_STREET' => array(
				'data_type' => 'text'
			),
			'WORK_MAILBOX' => array(
				'data_type' => 'string'
			),
			'WORK_CITY' => array(
				'data_type' => 'string'
			),
			'WORK_STATE' => array(
				'data_type' => 'string'
			),
			'WORK_ZIP' => array(
				'data_type' => 'string'
			),
			'WORK_COUNTRY' => array(
				'data_type' => 'string'
			),
			'WORK_PROFILE' => array(
				'data_type' => 'text'
			),
			'WORK_LOGO' => array(
				'data_type' => 'integer'
			),
			'WORK_NOTES' => array(
				'data_type' => 'text'
			),
			'ADMIN_NOTES' => array(
				'data_type' => 'text'
			),
			'SHORT_NAME' => array(
				'data_type' => 'string',
				'expression' => array(
					$helper->getConcatFunction("%s","' '", "UPPER(".$helper->getSubstrFunction("%s", 1, 1).")", "'.'"),
					'LAST_NAME', 'NAME'
				)
			),
			'IS_ONLINE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'expression' => array(
					'CASE WHEN %s > '.$helper->addSecondsToDateTime('(-'.self::getSecondsForLimitOnline().')').' THEN \'Y\' ELSE \'N\' END',
					'LAST_ACTIVITY_DATE',
				)
			),
			'IS_REAL_USER' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'expression' => array(
					'CASE WHEN %s IN ("'.join('", "', static::getExternalUserTypes()).'") THEN \'N\' ELSE \'Y\' END',
					'EXTERNAL_AUTH_ID',
				)
			),
			'INDEX' => array(
				'data_type' => 'Bitrix\Main\UserIndex',
				'reference' => array('=this.ID' => 'ref.USER_ID'),
				'join_type' => 'INNER',
			),
			(new Entity\ReferenceField(
				'COUNTER',
				\Bitrix\Main\UserCounterTable::class,
				Entity\Query\Join::on('this.ID', 'ref.USER_ID')->where('ref.CODE', 'tasks_effective')
			)),
			(new Reference(
				'PHONE_AUTH',
				UserPhoneAuthTable::class,
				Join::on('this.ID', 'ref.USER_ID')
			))
		);
	}

	public static function getSecondsForLimitOnline()
	{
		$seconds = intval(ini_get("session.gc_maxlifetime"));

		if ($seconds == 0)
		{
			$seconds = 1440;
		}
		else if ($seconds < 120)
		{
			$seconds = 120;
		}

		return intval($seconds);
	}

	public static function getActiveUsersCount()
	{
		if (ModuleManager::isModuleInstalled("intranet"))
		{
			$sql = "SELECT COUNT(U.ID) ".
				"FROM b_user U ".
				"WHERE U.ACTIVE = 'Y' ".
				"   AND U.LAST_LOGIN IS NOT NULL ".
				"   AND EXISTS(".
				"       SELECT 'x' ".
				"       FROM b_utm_user UF, b_user_field F ".
				"       WHERE F.ENTITY_ID = 'USER' ".
				"           AND F.FIELD_NAME = 'UF_DEPARTMENT' ".
				"           AND UF.FIELD_ID = F.ID ".
				"           AND UF.VALUE_ID = U.ID ".
				"           AND UF.VALUE_INT IS NOT NULL ".
				"           AND UF.VALUE_INT <> 0".
				"   )";
		}
		else
		{
			$sql = "SELECT COUNT(ID) ".
				"FROM b_user ".
				"WHERE ACTIVE = 'Y' ".
				"   AND LAST_LOGIN IS NOT NULL";
		}

		$connection = Application::getConnection();
		return $connection->queryScalar($sql);
	}

	public static function getUserGroupIds($userId)
	{
		$groups = array();

		// anonymous groups
		$result = GroupTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=ANONYMOUS' => 'Y',
				'=ACTIVE' => 'Y'
			)
		));

		while ($row = $result->fetch())
		{
			$groups[] = $row['ID'];
		}

		if(!in_array(2, $groups))
			$groups[] = 2;

		if($userId > 0)
		{
			// private groups
			$nowTimeExpression = new SqlExpression(
				static::getEntity()->getConnection()->getSqlHelper()->getCurrentDateTimeFunction()
			);

			$result = GroupTable::getList(array(
				'select' => array('ID'),
				'filter' => array(
					'=UserGroup:GROUP.USER_ID' => $userId,
					'=ACTIVE' => 'Y',
					array(
						'LOGIC' => 'OR',
						'=UserGroup:GROUP.DATE_ACTIVE_FROM' => null,
						'<=UserGroup:GROUP.DATE_ACTIVE_FROM' => $nowTimeExpression,
					),
					array(
						'LOGIC' => 'OR',
						'=UserGroup:GROUP.DATE_ACTIVE_TO' => null,
						'>=UserGroup:GROUP.DATE_ACTIVE_TO' => $nowTimeExpression,
					),
					array(
						'LOGIC' => 'OR',
						'!=ANONYMOUS' => 'Y',
						'=ANONYMOUS' => null
					)
				)
			));

			while ($row = $result->fetch())
			{
				$groups[] = $row['ID'];
			}
		}

		sort($groups);

		return $groups;
	}

	public static function getExternalUserTypes()
	{
		static $types = array("bot", "email", "controller", "replica", "imconnector", "sale", "saleanonymous");
		return $types;
	}

	public static function indexRecord($id)
	{
		$id = intval($id);
		if($id == 0)
		{
			return false;
		}

		$intranetInstalled = ModuleManager::isModuleInstalled('intranet');

		$select = array('ID', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'WORK_POSITION', 'LOGIN', 'EMAIL');
		if ($intranetInstalled)
		{
			$select[] = 'UF_DEPARTMENT';
		}

		$record = parent::getList(array(
			'select' => $select,
			'filter' => array('=ID' => $id)
		))->fetch();

		if(!is_array($record))
		{
			return false;
		}

		$record['UF_DEPARTMENT_NAMES'] = array();
		if ($intranetInstalled)
		{
			$departmentNames = UserUtils::getDepartmentNames($record['UF_DEPARTMENT']);
			foreach ($departmentNames as $departmentName)
			{
				$record['UF_DEPARTMENT_NAMES'][] = $departmentName['NAME'];
			}
		}

		$departmentName = isset($record['UF_DEPARTMENT_NAMES'][0])? $record['UF_DEPARTMENT_NAMES'][0]: '';
		$searchDepartmentContent = implode(' ', $record['UF_DEPARTMENT_NAMES']);

		UserIndexTable::merge(array(
			'USER_ID' => $id,
			'NAME' => (string)$record['NAME'],
			'SECOND_NAME' => (string)$record['SECOND_NAME'],
			'LAST_NAME' => (string)$record['LAST_NAME'],
			'WORK_POSITION' => (string)$record['WORK_POSITION'],
			'UF_DEPARTMENT_NAME' => (string)$departmentName,
			'SEARCH_USER_CONTENT' => self::generateSearchUserContent($record),
			'SEARCH_ADMIN_CONTENT' => self::generateSearchAdminContent($record),
			'SEARCH_DEPARTMENT_CONTENT' => MapBuilder::create()->addText($searchDepartmentContent)->build(),
		));

		return true;
	}

	public static function deleteIndexRecord($id)
	{
		UserIndexTable::delete($id);
	}

	private static function generateSearchUserContent(array $fields)
	{
		$result = MapBuilder::create()
			->addInteger($fields['ID'])
			->addText($fields['NAME'])
			->addText($fields['SECOND_NAME'])
			->addText($fields['LAST_NAME'])
			->addText($fields['WORK_POSITION'])
			->addText(implode(' ', $fields['UF_DEPARTMENT_NAMES']))
			->build();

		return $result;
	}

	private static function generateSearchAdminContent(array $fields)
	{
		$result = MapBuilder::create()
			->addInteger($fields['ID'])
			->addText($fields['NAME'])
			->addText($fields['SECOND_NAME'])
			->addText($fields['LAST_NAME'])
			->addEmail($fields['EMAIL'])
			->addText($fields['WORK_POSITION'])
			->addText($fields['LOGIN'])
			->build();

		return $result;
	}

	public static function add(array $data)
	{
		throw new NotImplementedException("Use CUser class.");
	}

	public static function update($primary, array $data)
	{
		throw new NotImplementedException("Use CUser class.");
	}

	public static function delete($primary)
	{
		throw new NotImplementedException("Use CUser class.");
	}

	public static function onAfterAdd(Entity\Event $event)
	{
		$id = $event->getParameter("id");
		static::indexRecord($id);
		return new Entity\EventResult();
	}

	public static function onAfterUpdate(Entity\Event $event)
	{
		$primary = $event->getParameter("id");
		$id = $primary["ID"];
		static::indexRecord($id);
		return new Entity\EventResult();
	}

	public static function onAfterDelete(Entity\Event $event)
	{
		$primary = $event->getParameter("id");
		$id = $primary["ID"];
		static::deleteIndexRecord($id);
		return new Entity\EventResult();
	}
}
