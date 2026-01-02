<?php

namespace Bitrix\Iblock;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\DeleteResult;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\ORM\EntityError;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\TaskOperationTable;

/**
 * Class RightTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> IBLOCK_ID int mandatory
 * <li> GROUP_CODE string(50) mandatory
 * <li> ENTITY_TYPE string(32) mandatory
 * <li> ENTITY_ID int mandatory
 * <li> DO_INHERIT string(1) mandatory
 * <li> TASK_ID int mandatory
 * <li> OP_SREAD string(1) mandatory
 * <li> OP_EREAD string(1) mandatory
 * <li> XML_ID string(32) optional
 * <li> IBLOCK_ID reference to {@link IblockTable}
 * <li> TASK_ID reference to {@link TaskOperationTable}
 * </ul>
 *
 * @package Bitrix\Iblock
 **/

class IblockRightTable extends DataManager
{
	public static function getTableName(): string
	{
		return 'b_iblock_right';
	}

	public static function getMap(): array
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('IBLOCK_RIGHTS_ENTITY_ID_FIELD'),
				]
			),
			new IntegerField(
				'IBLOCK_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('IBLOCK_RIGHTS_ENTITY_IBLOCK_ID_FIELD'),
				]
			),
			new StringField(
				'GROUP_CODE',
				[
					'required' => true,
					'validation' => function()
					{
						return [
							new LengthValidator(null, 50),
						];
					},
					'title' => Loc::getMessage('IBLOCK_RIGHTS_ENTITY_GROUP_CODE_FIELD'),
				]
			),
			new StringField(
				'ENTITY_TYPE',
				[
					'required' => true,
					'validation' => function()
					{
						return [
							new LengthValidator(null, 32),
						];
					},
					'title' => Loc::getMessage('IBLOCK_RIGHTS_ENTITY_ENTITY_TYPE_FIELD'),
				]
			),
			new IntegerField(
				'ENTITY_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('IBLOCK_RIGHTS_ENTITY_ENTITY_ID_FIELD'),
				]
			),
			new BooleanField(
				'DO_INHERIT',
				[
					'required' => true,
					'data_type' => 'boolean',
					'values' => ['N', 'Y'],
					'title' => Loc::getMessage('IBLOCK_RIGHTS_ENTITY_DO_INHERIT_FIELD'),
				]
			),
			new IntegerField(
				'TASK_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('IBLOCK_RIGHTS_ENTITY_TASK_ID_FIELD'),
				]
			),
			new BooleanField(
				'OP_SREAD',
				[
					'data_type' => 'boolean',
					'values' => ['N', 'Y'],
					'title' => Loc::getMessage('IBLOCK_RIGHTS_ENTITY_OP_SREAD_FIELD'),
				]
			),
			new BooleanField(
				'OP_EREAD',
				[
					'data_type' => 'boolean',
					'values' => ['N', 'Y'],
					'title' => Loc::getMessage('IBLOCK_RIGHTS_ENTITY_OP_EREAD_FIELD'),
				]
			),
			new StringField(
				'XML_ID',
				[
					'validation' => function()
					{
						return [
							new LengthValidator(null, 32),
						];
					},
					'title' => Loc::getMessage('IBLOCK_RIGHTS_ENTITY_XML_ID_FIELD'),
				]
			),
			new Reference(
				'IBLOCK',
				IblockTable::class,
				['=this.IBLOCK_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			new Reference(
				'TASK',
				TaskOperationTable::class,
				['=this.TASK_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
		];
	}

	public static function add(array $data): AddResult
	{
		$result = new AddResult();
		$result->addError(new EntityError(
			Loc::getMessage('IBLOCK_RIGHTS_ENTITY_ADD_BLOCK')
		));

		return $result;
	}

	public static function update($primary, array $data): UpdateResult
	{
		$result = new UpdateResult();
		$result->addError(new EntityError(
			Loc::getMessage('IBLOCK_RIGHTS_ENTITY_UPDATE_BLOCK')
		));

		return $result;
	}

	public static function delete($primary): DeleteResult
	{
		$result = new DeleteResult();
		$result->addError(new EntityError(
			Loc::getMessage('IBLOCK_RIGHTS_ENTITY_DELETE_BLOCK')
		));

		return $result;
	}
}
