<?php

namespace Bitrix\UI\FileUploader;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;

/**
 * Class TempFileTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_TempFile_Query query()
 * @method static EO_TempFile_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_TempFile_Result getById($id)
 * @method static EO_TempFile_Result getList(array $parameters = [])
 * @method static EO_TempFile_Entity getEntity()
 * @method static \Bitrix\UI\FileUploader\TempFile createObject($setDefaultValues = true)
 * @method static \Bitrix\UI\FileUploader\EO_TempFile_Collection createCollection()
 * @method static \Bitrix\UI\FileUploader\TempFile wakeUpObject($row)
 * @method static \Bitrix\UI\FileUploader\EO_TempFile_Collection wakeUpCollection($rows)
 */
class TempFileTable extends Data\DataManager
{
	public static function getTableName()
	{
		return 'b_ui_file_uploader_temp_file';
	}

	public static function getObjectClass()
	{
		return TempFile::class;
	}

	public static function getMap()
	{
		return [
			(new Fields\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
			,

			(new Fields\StringField("GUID"))
				->configureUnique(true)
				->configureNullable(false)
				->configureDefaultValue(static function () {
					return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
						mt_rand(0, 0xffff), mt_rand(0, 0xffff),
						mt_rand(0, 0xffff),
						mt_rand(0, 0x0fff) | 0x4000,
						mt_rand(0, 0x3fff) | 0x8000,
						mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
					);
				})
				->configureSize(36)
			,

			new Fields\IntegerField('FILE_ID'),

			(new Fields\StringField('FILENAME'))
				->configureRequired()
				->configureSize(255)
			,

			(new Fields\IntegerField('SIZE'))
				->configureRequired()
				->configureSize(8)
			,

			(new Fields\StringField('PATH'))
				->configureRequired()
				->configureSize(255)
			,

			(new Fields\StringField('MIMETYPE'))
				->configureRequired()
				->configureSize(255)
			,

			(new Fields\IntegerField('RECEIVED_SIZE'))
				->configureSize(8)
			,
			new Fields\IntegerField('WIDTH'),
			new Fields\IntegerField('HEIGHT'),

			new Fields\IntegerField('BUCKET_ID'),
			(new Fields\StringField('MODULE_ID'))
				->configureRequired()
				->configureSize(50)
			,

			(new Fields\StringField('CONTROLLER'))
				->configureRequired()
				->configureSize(255)
			,

			(new Fields\BooleanField('CLOUD'))
				->configureValues(0, 1)
				->configureDefaultValue(0)
			,

			(new Fields\BooleanField('UPLOADED'))
				->configureValues(0, 1)
				->configureDefaultValue(0)
			,

			(new Fields\BooleanField('DELETED'))
				->configureValues(0, 1)
				->configureDefaultValue(0)
			,

			(new Fields\IntegerField('CREATED_BY'))
				->configureRequired()
				->configureDefaultValue(static function () {
					global $USER;
					if (is_object($USER) && method_exists($USER, 'getId'))
					{
						return (int)$USER->getId();
					}

					return 0;
				})
			,

			(new Fields\DatetimeField('CREATED_AT'))
				->configureDefaultValue(static function () {
					return new DateTime();
				})
			,

			(new Reference(
				'FILE',
				\Bitrix\Main\FileTable::class,
				Join::on('this.FILE_ID', 'ref.ID'),
				['join_type' => Join::TYPE_INNER]
			)),
		];
	}

	public static function onDelete(Event $event)
	{
		$tempFile = $event->getParameter('object');
		if (!$tempFile)
		{
			$id = $event->getParameter('primary')['ID'];
			$tempFile = self::getById($id)->fetchObject();
		}

		if ($tempFile)
		{
			$tempFile->fill();

			$deleteBFile = $tempFile->customData->get('deleteBFile') !== false;
			$tempFile->deleteContent($deleteBFile);
		}
	}
}
