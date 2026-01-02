<?php

namespace Bitrix\Main\UI\Viewer;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\FileTable;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

/**
 * Class FilePreviewTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_FilePreview_Query query()
 * @method static EO_FilePreview_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_FilePreview_Result getById($id)
 * @method static EO_FilePreview_Result getList(array $parameters = [])
 * @method static EO_FilePreview_Entity getEntity()
 * @method static \Bitrix\Main\UI\Viewer\EO_FilePreview createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\UI\Viewer\EO_FilePreview_Collection createCollection()
 * @method static \Bitrix\Main\UI\Viewer\EO_FilePreview wakeUpObject($row)
 * @method static \Bitrix\Main\UI\Viewer\EO_FilePreview_Collection wakeUpCollection($rows)
 */
final class FilePreviewTable extends DataManager
{
	/** @var array */
	protected static $alreadyDeleted = [];

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_file_preview';
	}

	/**
	 * Returns entity map definition.
	 * To get initialized fields
	 * @return array
	 * @throws \Bitrix\Main\SystemException
	 * @see \Bitrix\Main\ORM\Entity::getFields()
	 * @see \Bitrix\Main\ORM\Entity::getField()
	 */
	public static function getMap()
	{
		return [
			new Fields\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
			]),
			new Fields\IntegerField('FILE_ID', [
				'required' => true,
			]),
			new Fields\IntegerField('PREVIEW_ID'),
			new Fields\IntegerField('PREVIEW_IMAGE_ID'),
			new Fields\DatetimeField('CREATED_AT', [
				'default_value' => function () {
					return new DateTime();
				},
			]),
			new Fields\DatetimeField('TOUCHED_AT', [
				'default_value' => function () {
					return new DateTime();
				},
			]),
			new Fields\Relations\Reference('FILE',FileTable::class,
				['=this.FILE_ID' => 'ref.ID'],
				['join_type' => 'INNER']
			),
			new Fields\Relations\Reference('PREVIEW',FileTable::class,
				['=this.PREVIEW_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			new Fields\Relations\Reference('PREVIEW_IMAGE',FileTable::class,
				['=this.PREVIEW_IMAGE_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
		];
	}

	public static function deleteOld($dayToDeath = 22, $portion = 20)
	{
		$deathTime = new Date();
		$deathTime->add("-{$dayToDeath} day");

		$query = self::query();
		$filter = $query::filter()
			->logic('or')
				->whereNull('TOUCHED_AT')
				->where('TOUCHED_AT', '<', $deathTime)
		;

		$files = self::getList([
			'select' => ['ID', 'PREVIEW_IMAGE_ID', 'PREVIEW_ID'],
			'filter' => $filter,
			'limit' => $portion,
		]);

		foreach ($files as $file)
		{
			$keepImage = isset($file['PREVIEW_IMAGE_ID']);

			self::deleteContent($file, $keepImage);
			if (!$keepImage)
			{
				self::delete($file['ID']);
			}
		}
	}

	public static function deleteOldAgent($dayToDeath = 22, $portion = 20)
	{
		self::deleteOld($dayToDeath, $portion);

		return "\\Bitrix\\Main\\UI\\Viewer\\FilePreviewTable::deleteOldAgent({$dayToDeath}, {$portion});";
	}

	public static function onDelete(Event $event)
	{
		$id = $event->getParameter('primary')['ID'];
		if (isset(self::$alreadyDeleted[$id]))
		{
			return;
		}

		$file = self::getRowById($id);
		if (!$file)
		{
			return;
		}

		self::deleteContent($file);
	}

	protected static function deleteContent(array $file, $keepImage = false)
	{
		if (isset(self::$alreadyDeleted[$file['ID']]))
		{
			return;
		}

		self::$alreadyDeleted[$file['ID']] = true;

		\CFile::delete($file['PREVIEW_ID']);
		\CFile::delete($file['PREVIEW_IMAGE_ID']);
	}

	/**
	 * Event handler which listen to delete entries of b_file to clean preview.
	 * @param array $bfile
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function onFileDelete($bfile)
	{
		if (empty($bfile['ID']))
		{
			return;
		}

		$file = self::getRow([
			'filter' => [
				'=FILE_ID' => $bfile['ID'],
			]
		]);

		if (!$file)
		{
			return;
		}

		self::deleteContent($file);
		self::delete($file['ID']);
	}
}
