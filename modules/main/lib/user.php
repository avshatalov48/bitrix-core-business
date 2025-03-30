<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

namespace Bitrix\Main;

use Bitrix\HumanResources\Compatibility\Utils\DepartmentBackwardAccessCode;
use Bitrix\HumanResources\Service\Container;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Search\MapBuilder;

Loc::loadMessages(__FILE__);

/**
 * Class UserTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_User_Query query()
 * @method static EO_User_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_User_Result getById($id)
 * @method static EO_User_Result getList(array $parameters = [])
 * @method static EO_User_Entity getEntity()
 * @method static \Bitrix\Main\EO_User createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_User_Collection createCollection()
 * @method static \Bitrix\Main\EO_User wakeUpObject($row)
 * @method static \Bitrix\Main\EO_User_Collection wakeUpCollection($rows)
 */
class UserTable extends DataManager
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

		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),

			new StringField('LOGIN'),

			(new StringField('PASSWORD'))
				->configurePrivate(),

			new StringField('EMAIL'),

			(new BooleanField('ACTIVE'))
				->configureValues('N', 'Y'),

			(new BooleanField('BLOCKED'))
				->configureValues('N', 'Y'),

			new DatetimeField('DATE_REGISTER'),

			(new ExpressionField(
				'DATE_REG_SHORT',
				$helper->getDatetimeToDateFunction('%s'),
				'DATE_REGISTER')
			)->configureValueType(DatetimeField::class),

			new DatetimeField('LAST_LOGIN'),

			(new ExpressionField(
				'LAST_LOGIN_SHORT',
				$helper->getDatetimeToDateFunction('%s'),
				'LAST_LOGIN')
			)->configureValueType(DatetimeField::class),

			new DatetimeField('LAST_ACTIVITY_DATE'),

			new DatetimeField('TIMESTAMP_X'),

			new StringField('NAME'),
			new StringField('SECOND_NAME'),
			new StringField('LAST_NAME'),
			new StringField('TITLE'),
			new StringField('EXTERNAL_AUTH_ID'),
			new StringField('XML_ID'),
			new StringField('BX_USER_ID'),
			new StringField('CONFIRM_CODE'),
			new StringField('LID'),
			(new StringField('LANGUAGE_ID'))
				->addValidator(new ORM\Fields\Validators\RegExpValidator('/^[a-z0-9]{2}$/')),
			new StringField('TIME_ZONE'),
			new IntegerField('TIME_ZONE_OFFSET'),
			new StringField('PERSONAL_PROFESSION'),
			new StringField('PERSONAL_PHONE'),
			new StringField('PERSONAL_MOBILE'),
			new StringField('PERSONAL_WWW'),
			new StringField('PERSONAL_ICQ'),
			new StringField('PERSONAL_FAX'),
			new StringField('PERSONAL_PAGER'),
			new TextField('PERSONAL_STREET'),
			new StringField('PERSONAL_MAILBOX'),
			new StringField('PERSONAL_CITY'),
			new StringField('PERSONAL_STATE'),
			new StringField('PERSONAL_ZIP'),
			new StringField('PERSONAL_COUNTRY'),
			new DateField('PERSONAL_BIRTHDAY'),
			new StringField('PERSONAL_GENDER'),
			new IntegerField('PERSONAL_PHOTO'),
			new TextField('PERSONAL_NOTES'),
			new StringField('WORK_COMPANY'),
			new StringField('WORK_DEPARTMENT'),
			new StringField('WORK_PHONE'),
			new StringField('WORK_POSITION'),
			new StringField('WORK_WWW'),
			new StringField('WORK_FAX'),
			new StringField('WORK_PAGER'),
			new TextField('WORK_STREET'),
			new StringField('WORK_MAILBOX'),
			new StringField('WORK_CITY'),
			new StringField('WORK_STATE'),
			new StringField('WORK_ZIP'),
			new StringField('WORK_COUNTRY'),
			new TextField('WORK_PROFILE'),
			new IntegerField('WORK_LOGO'),
			new TextField('WORK_NOTES'),
			new TextField('ADMIN_NOTES'),

			new ExpressionField(
				'SHORT_NAME',
				$helper->getConcatFunction(
					"%s",
					"' '",
					"UPPER(" . $helper->getSubstrFunction("%s", 1, 1) . ")", "'.'"
				),
				['LAST_NAME', 'NAME']
			),

			(new ExpressionField(
				'IS_ONLINE',
				'CASE WHEN %s > '
					. $helper->addSecondsToDateTime('(-' . self::getSecondsForLimitOnline() . ')')
					. ' THEN \'Y\' ELSE \'N\' END',
				'LAST_ACTIVITY_DATE',
				['values' => ['N', 'Y']]
			))->configureValueType(BooleanField::class),

			(new ExpressionField(
				'IS_REAL_USER',
				'CASE WHEN %s IN (\''
					. join('\', \'', static::getExternalUserTypes())
					. '\') THEN \'N\' ELSE \'Y\' END',
				'EXTERNAL_AUTH_ID',
				['values' => ['N', 'Y']]
			))->configureValueType(BooleanField::class),

			(new Reference(
				'INDEX',
				UserIndexTable::class,
				Join::on('this.ID', 'ref.USER_ID')
			))->configureJoinType(Join::TYPE_INNER),

			(new Reference(
				'COUNTER',
				UserCounterTable::class,
				Join::on('this.ID', 'ref.USER_ID')->where('ref.CODE', 'tasks_effective')
			)),
			(new Reference(
				'PHONE_AUTH',
				UserPhoneAuthTable::class,
				Join::on('this.ID', 'ref.USER_ID')
			)),
			(new OneToMany('GROUPS', UserGroupTable::class, 'USER'))
				->configureJoinType(Join::TYPE_INNER),

			(new Reference(
				'ACTIVE_LANGUAGE',
				\Bitrix\Main\Localization\LanguageTable::class,
				Join::on('this.LANGUAGE_ID', 'ref.LID')->where('ref.ACTIVE', 'Y')
			)),

			(new ExpressionField(
				'NOTIFICATION_LANGUAGE_ID',
				'CASE WHEN (%s IS NOT NULL AND %s = %s) THEN %s ELSE %s END',
				[
					'LANGUAGE_ID', 'LANGUAGE_ID', 'ACTIVE_LANGUAGE.LID', 'LANGUAGE_ID',	function () {
						return new SqlExpression("'" . (SiteTable::getDefaultLanguageId() ?? LANGUAGE_ID) . "'");
					},
				],
			))->configureValueType(StringField::class),
		];
	}

	public static function getSecondsForLimitOnline()
	{
		$seconds = intval(ini_get("session.gc_maxlifetime"));

		if ($seconds == 0)
		{
			$seconds = 1440;
		}
		elseif ($seconds < 120)
		{
			$seconds = 120;
		}

		return $seconds;
	}

	/**
	 * @param Type\Date|null $lastLoginDate
	 * @return int
	 * @deprecated
	 */
	public static function getActiveUsersCount(Type\Date $lastLoginDate = null)
	{
		return Application::getInstance()->getLicense()->getActiveUsersCount($lastLoginDate);
	}

	public static function getUserGroupIds($userId): array
	{
		$groups = [];

		// anonymous groups
		$result = GroupTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=ANONYMOUS' => 'Y',
				'=ACTIVE' => 'Y',
			],
			'cache' => ['ttl' => 86400],
		]);

		while ($row = $result->fetch())
		{
			$groups[] = (int)$row['ID'];
		}

		$groups[] = 2;

		if ($userId > 0)
		{
			$nowTimeExpression = new SqlExpression(
				static::getEntity()->getConnection()->getSqlHelper()->getCurrentDateTimeFunction()
			);

			$result = GroupTable::getList([
				'select' => ['ID'],
				'filter' => [
					'=UserGroup:GROUP.USER_ID' => $userId,
					'=ACTIVE' => 'Y',
					[
						'LOGIC' => 'OR',
						'=UserGroup:GROUP.DATE_ACTIVE_FROM' => null,
						'<=UserGroup:GROUP.DATE_ACTIVE_FROM' => $nowTimeExpression,
					],
					[
						'LOGIC' => 'OR',
						'=UserGroup:GROUP.DATE_ACTIVE_TO' => null,
						'>=UserGroup:GROUP.DATE_ACTIVE_TO' => $nowTimeExpression,
					],
				],
			]);

			while ($row = $result->fetch())
			{
				$groups[] = (int)$row['ID'];
			}
		}

		$groups = array_unique($groups, SORT_NUMERIC);
		sort($groups);

		return $groups;
	}

	public static function getExternalUserTypes()
	{
		static $types = [
			'bot',
			'email',
			'__controller',
			'replica',
			'imconnector',
			'sale',
			'saleanonymous',
			'shop',
			'call',
			'document_editor',
			'calendar_sharing',
		];

		return $types;
	}

	/**
	 * Returns an array with fields used in full-text index.
	 *
	 * @return string[]
	 */
	public static function getIndexedFields(): array
	{
		static $fields = [
			'ID',
			'NAME',
			'SECOND_NAME',
			'LAST_NAME',
			'WORK_POSITION',
			'PERSONAL_PROFESSION',
			'PERSONAL_WWW',
			'LOGIN',
			'EMAIL',
			'PERSONAL_MOBILE',
			'PERSONAL_PHONE',
			'PERSONAL_CITY',
			'PERSONAL_STREET',
			'PERSONAL_STATE',
			'PERSONAL_COUNTRY',
			'PERSONAL_ZIP',
			'PERSONAL_MAILBOX',
			'WORK_CITY',
			'WORK_STREET',
			'WORK_STATE',
			'WORK_ZIP',
			'WORK_COUNTRY',
			'WORK_MAILBOX',
			'WORK_PHONE',
			'WORK_COMPANY',
		];

		if (ModuleManager::isModuleInstalled('intranet'))
		{
			return array_merge($fields, ['UF_DEPARTMENT']);
		}
		return $fields;
	}

	/**
	 * Returns true if there are fields to be indexed in the set.
	 *
	 * @param array $fields
	 * @return bool
	 */
	public static function shouldReindex(array $fields): bool
	{
		if (isset($fields['ID']))
		{
			unset($fields['ID']);
		}
		return !empty(array_intersect(
			static::getIndexedFields(),
			array_keys($fields)
		));
	}

	public static function indexRecord($id)
	{
		$id = intval($id);
		if ($id == 0)
		{
			return false;
		}

		$record = parent::getList([
			'select' => static::getIndexedFields(),
			'filter' => ['=ID' => $id],
		])->fetch();

		if (!is_array($record))
		{
			return false;
		}

		$record['UF_DEPARTMENT_NAMES'] = [];
		if (
			Loader::includeModule('humanresources')
			&& isset($record['UF_DEPARTMENT'])
			&& is_array($record['UF_DEPARTMENT'])
		)
		{
			$departments = Container::getNodeRepository()->findAllByAccessCodes(
				array_map(
					static fn($departmentId) => DepartmentBackwardAccessCode::makeById((int)$departmentId),
					$record['UF_DEPARTMENT'],
				),
			);

			foreach ($departments as $department)
			{
				$record['UF_DEPARTMENT_NAMES'][] = $department->name;
			}
		}

		$departmentName = $record['UF_DEPARTMENT_NAMES'][0] ?? '';
		$searchDepartmentContent = implode(' ', $record['UF_DEPARTMENT_NAMES']);

		UserIndexTable::merge([
			'USER_ID' => $id,
			'NAME' => (string)$record['NAME'],
			'SECOND_NAME' => (string)$record['SECOND_NAME'],
			'LAST_NAME' => (string)$record['LAST_NAME'],
			'WORK_POSITION' => (string)$record['WORK_POSITION'],
			'UF_DEPARTMENT_NAME' => (string)$departmentName,
			'SEARCH_USER_CONTENT' => self::generateSearchUserContent($record),
			'SEARCH_ADMIN_CONTENT' => self::generateSearchAdminContent($record),
			'SEARCH_DEPARTMENT_CONTENT' => MapBuilder::create()->addText($searchDepartmentContent)->build(),
		]);

		return true;
	}

	public static function deleteIndexRecord($id)
	{
		UserIndexTable::delete($id);
	}

	private static function generateSearchUserContent(array $fields)
	{
		$text = implode(' ', [
			$fields['NAME'],
			$fields['LAST_NAME'],
			$fields['WORK_POSITION'],
		]);

		$charsToReplace = ['(', ')', '[', ']', '{', '}', '<', '>', '-', '#', '"', '\''];

		$clearedText = str_replace($charsToReplace, ' ', $text);
		$clearedText = preg_replace('/\s\s+/', ' ', $clearedText);

		$result = MapBuilder::create()
			->addInteger($fields['ID'])
			->addText($clearedText)
			->build()
		;

		return $result;
	}

	private static function generateSearchAdminContent(array $fields)
	{
		$personalCountry = (
			isset($fields['PERSONAL_COUNTRY'])
			&& intval($fields['PERSONAL_COUNTRY'])
				? UserUtils::getCountryValue([
					'VALUE' => intval($fields['PERSONAL_COUNTRY']),
				])
				: ''
		);
		$workCountry = (
			isset($fields['WORK_COUNTRY'])
			&& intval($fields['WORK_COUNTRY'])
				? UserUtils::getCountryValue([
					'VALUE' => intval($fields['WORK_COUNTRY']),
				])
				: ''
		);
		$department = (
			isset($fields['UF_DEPARTMENT_NAMES'])
			&& is_array($fields['UF_DEPARTMENT_NAMES'])
				? implode(' ', $fields['UF_DEPARTMENT_NAMES'])
				: ''
		);

		$ufContent = UserUtils::getUFContent($fields['ID']);
		$tagsContent = UserUtils::getTagsContent($fields['ID']);

		$result = MapBuilder::create()
			->addInteger($fields['ID'])
			->addText($fields['NAME'])
			->addText($fields['SECOND_NAME'])
			->addText($fields['LAST_NAME'])
			->addEmail($fields['EMAIL'])
			->addText($fields['WORK_POSITION'])
			->addText($fields['PERSONAL_PROFESSION'])
			->addText($fields['PERSONAL_WWW'])
			->addText($fields['LOGIN'])
			->addPhone($fields['PERSONAL_MOBILE'])
			->addPhone($fields['PERSONAL_PHONE'])
			->addText($fields['PERSONAL_CITY'])
			->addText($fields['PERSONAL_STREET'])
			->addText($fields['PERSONAL_STATE'])
			->addText($fields['PERSONAL_ZIP'])
			->addText($fields['PERSONAL_MAILBOX'])
			->addText($fields['WORK_CITY'])
			->addText($fields['WORK_STREET'])
			->addText($fields['WORK_STATE'])
			->addText($fields['WORK_ZIP'])
			->addText($fields['WORK_MAILBOX'])
			->addPhone($fields['WORK_PHONE'])
			->addText($fields['WORK_COMPANY'])
			->addText($personalCountry)
			->addText($workCountry)
			->addText($department)
			->addText($ufContent)
			->addText($tagsContent)
			->build()
		;

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

	public static function onAfterAdd(ORM\Event $event)
	{
		$id = $event->getParameter("id");
		static::indexRecord($id);
		return new ORM\EventResult();
	}

	public static function onAfterUpdate(ORM\Event $event)
	{
		$primary = $event->getParameter("id");
		$id = $primary["ID"];
		static::indexRecord($id);
		return new ORM\EventResult();
	}

	public static function onAfterDelete(ORM\Event $event)
	{
		$primary = $event->getParameter("id");
		$id = $primary["ID"];
		static::deleteIndexRecord($id);
		return new ORM\EventResult();
	}

	public static function postInitialize(ORM\Entity $entity)
	{
		// add uts inner reference

		if ($entity->hasField('UTS_OBJECT'))
		{
			/** @var Reference $leftUtsRef */
			$leftUtsRef = $entity->getField('UTS_OBJECT');

			$entity->addField((
				new Reference(
					'UTS_OBJECT_INNER', $leftUtsRef->getRefEntity(), $leftUtsRef->getReference()
				))
				->configureJoinType('inner')
			);
		}
	}
}
