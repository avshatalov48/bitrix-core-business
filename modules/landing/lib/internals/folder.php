<?php
namespace Bitrix\Landing\Internals;

use Bitrix\Landing\Manager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class FolderTable extends Entity\DataManager
{
	/**
	 * For save callbacks.
	 */
	const ACTION_TYPE_ADD = 'add';

	/**
	 * For save callbacks.
	 */
	const ACTION_TYPE_UPDATE = 'update';

	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_folder';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => 'ID'
			)),
			'PARENT_ID' => new Entity\IntegerField('PARENT_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_FOLDER_PARENT_ID')
			)),
			'SITE_ID' => new Entity\IntegerField('SITE_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_FOLDER_SITE_ID'),
				'required' => true
			)),
			'INDEX_ID' => new Entity\IntegerField('INDEX_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_FOLDER_INDEX_ID')
			)),
			'ACTIVE' => new Entity\StringField('ACTIVE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_FOLDER_ACTIVE'),
				'default_value' => 'N'
			)),
			'DELETED' => new Entity\StringField('DELETED', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_FOLDER_DELETED'),
				'default_value' => 'N'
			)),
			'TITLE' => new Entity\StringField('TITLE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_FOLDER_TITLE'),
				'required' => true
			)),
			'CODE' => new Entity\StringField('CODE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_FOLDER_CODE'),
				'required' => true
			)),
			'CREATED_BY_ID' => new Entity\IntegerField('CREATED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CREATED_BY_ID'),
				'required' => true
			)),
			'MODIFIED_BY_ID' => new Entity\IntegerField('MODIFIED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_MODIFIED_BY_ID'),
				'required' => true
			)),
			'DATE_CREATE' => new Entity\DatetimeField('DATE_CREATE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_CREATE'),
				'required' => true
			)),
			'DATE_MODIFY' => new Entity\DatetimeField('DATE_MODIFY', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_MODIFY'),
				'required' => true
			))
		);
	}

	/**
	 * Prepares change to save.
	 * @param Entity\Event $event Event instance.
	 * @param string $actionType Action type: add / update.
	 * @return Entity\EventResult
	 */
	protected static function prepareChange(Entity\Event $event, string $actionType): Entity\EventResult
	{
		$result = new Entity\EventResult();
		$primary = $event->getParameter('primary');
		$fields = $event->getParameter('fields');
		$modifyFields = [];

		// check that index landing is exists in folder's site
		/*if ($fields['INDEX_ID'] ?? 0)
		{
			if (!array_key_exists('SITE_ID', $fields))
			{
				$fields['SITE_ID'] = self::getList([
					'select' => [
						'SITE_ID'
					],
					'filter' => [
						'ID' => $primary['ID'] ?? 0
					]
				])->fetch()['SITE_ID'] ?? 0;
			}
			$res = LandingTable::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'SITE_ID' => $fields['SITE_ID'],
					'ID' => $fields['INDEX_ID'],
					'FOLDER_ID' => $primary['ID'] ?? 0
				]
			]);
			if (!$res->fetch())
			{
				$result->setErrors([
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_FOLDER_INDEX_OUT_OF_SITE'),
						'FOLDER_INDEX_OUT_OF_SITE'
					)
				]);
				return $result;
			}
		}*/

		// translit the code from title
		if (
			$actionType == self::ACTION_TYPE_ADD && array_key_exists('TITLE', $fields) &&
			(!array_key_exists('CODE', $fields) || trim($fields['CODE']) == '')
		)
		{
			$fields['CODE'] = \CUtil::translit(
				trim($fields['TITLE']),
				LANGUAGE_ID,
				[
					'replace_space' => '',
					'replace_other' => ''
				]
			);
			if (!$fields['CODE'])
			{
				$fields['CODE'] = Manager::getRandomString(12);
			}
			$modifyFields['CODE'] = $fields['CODE'];
		}

		// for update always we need the code
		if (isset($primary['ID']) && !array_key_exists('CODE', $fields))
		{
			$res = self::getList([
				'select' => [
					'CODE'
				],
				'filter' => [
					'ID' => $primary['ID']
				]
			]);
			if ($row = $res->fetch())
			{
				$fields['CODE'] = $row['CODE'];
			}
		}

		// check unique folder
		if (
			array_key_exists('CODE', $fields) &&
			array_key_exists('SITE_ID', $fields) &&
			\Bitrix\Landing\Landing::isCheckUniqueAddress()
		)
		{
			$filter = [
				'=CODE' => $fields['CODE'],
				'SITE_ID' => $fields['SITE_ID'],
				'PARENT_ID' => $fields['PARENT_ID'] ?? null
			];
			if (isset($primary['ID']))
			{
				$filter['!ID'] = $primary['ID'];
			}
			$res = self::getList([
				'select' => [
					'ID'
				],
				'filter' => $filter
			]);
			if ($res->fetch())
			{
				$result->setErrors([
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_FOLDER_IS_NOT_UNIQUE'),
						'FOLDER_IS_NOT_UNIQUE'
					)
				]);
				return $result;
			}
		}

		// check correct folder path
		if (array_key_exists('CODE', $fields))
		{
			if (mb_strpos($fields['CODE'], '/') !== false)
			{
				$result->setErrors([
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_FOLDER_SLASH_IS_NOT_ALLOWED'),
						'SLASH_IS_NOT_ALLOWED'
                   )
				]);
				return $result;
			}
		}

		$result->modifyFields($modifyFields);

		return $result;
	}

	/**
	 * Before add handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function onBeforeAdd(Entity\Event $event): Entity\EventResult
	{
		return self::prepareChange($event, self::ACTION_TYPE_ADD);
	}

	/**
	 * Before update handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function onBeforeUpdate(Entity\Event $event): Entity\EventResult
	{
		return self::prepareChange($event, self::ACTION_TYPE_UPDATE);
	}
}