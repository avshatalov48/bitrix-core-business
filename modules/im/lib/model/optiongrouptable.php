<?php

namespace Bitrix\Im\Model;

use Bitrix\Main\ArgumentTypeException,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\DatetimeField,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\StringField,
	Bitrix\Main\ORM\Fields\Validators\LengthValidator,
	Bitrix\Main\SystemException;
use Bitrix\Main\Entity\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);


/**
 * Class OptionGroupTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NAME string(255) optional
 * <li> USER_ID int optional
 * <li> SORT int mandatory
 * <li> DATE_CREATE datetime mandatory
 * <li> CREATE_BY_ID int mandatory
 * <li> DATE_MODIFY datetime optional
 * <li> MODIFY_BY_ID int optional
 * </ul>
 *
 * @package Bitrix\Im
 **/

class OptionGroupTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_im_option_group';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 * @throws SystemException
	 */
	public static function getMap(): array
	{
		return [
			'ID' => (new IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
			])),
			'NAME' => (new StringField('NAME', [
				'validation' => [__CLASS__, 'validateName']
			])),
			'USER_ID' => (new IntegerField('USER_ID', [])),
			'SORT' => (new IntegerField('SORT', [
				'required' => true,
			])),
			'DATE_CREATE' => (new DatetimeField('DATE_CREATE', [
				'required' => true,
			])),
			'CREATE_BY_ID' => (new IntegerField('CREATE_BY_ID', [
				'required' => true,
			])),
			'DATE_MODIFY' => (new DatetimeField('DATE_MODIFY', [])),
			'MODIFY_BY_ID' => (new IntegerField('MODIFY_BY_ID', [])),
		];
	}

	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 * @throws ArgumentTypeException
	 */
	public static function validateName(): array
	{
		return [
			new LengthValidator(null, 255),
		];
	}

	public static function onBeforeAdd(Event $event): EventResult
	{
		$data = $event->getParameter('fields');
		$result = new EventResult();
		if (!isset($data['CREATE_DATE']) || !is_array($data['CREATE_DATE']))
		{
			$result->modifyFields(['DATE_CREATE' => new Datetime()]);
		}

		return $result;
	}

	public static function onBeforeUpdate(Event $event): EventResult
	{
		$data = $event->getParameter('fields');
		$result = new EventResult();
		if (!isset($data['MODIFY_DATE']) || !is_array($data['MODIFY_DATE']))
		{
			$result->modifyFields(['DATE_MODIFY' => new DateTime()]);
		}

		return $result;
	}
}