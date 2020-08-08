<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class BlockTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_block';
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
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_PARENT_ID')
			)),
			'LID' => new Entity\IntegerField('LID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LID'),
				'required' => true
			)),
			'LANDING' => new Entity\ReferenceField(
				'LANDING',
				'\Bitrix\Landing\Internals\LandingTable',
				array('=this.LID' => 'ref.ID')
			),
			'CODE' => new Entity\StringField('CODE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CODE'),
				'required' => true
			)),
			'XML_ID' => new Entity\StringField('XML_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_XML_ID')
			)),
			'INITIATOR_APP_CODE' => new Entity\StringField('INITIATOR_APP_CODE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_INITIATOR_APP_CODE'),
				'default_value' => ''
			)),
			'ANCHOR' => new Entity\StringField('ANCHOR', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ANCHOR')
			)),
			'MANIFEST_DB' => new Entity\ReferenceField(
				'MANIFEST_DB',
				'\Bitrix\Landing\Internals\ManifestTable',
				array('=this.CODE' => 'ref.CODE')
			),
			'SORT' => new Entity\IntegerField('SORT', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SORT')
			)),
			'ACTIVE' => new Entity\StringField('ACTIVE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ACTIVE'),
				'default_value' => 'Y'
			)),
			'PUBLIC' => new Entity\StringField('PUBLIC', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_PUBLIC'),
				'default_value' => 'Y'
			)),
			'DELETED' => new Entity\StringField('DELETED', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DELETED'),
				'default_value' => 'N'
			)),
			'ACCESS' => new Entity\StringField('ACCESS', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ACCESS'),
				'default_value' => 'X'
			)),
			'SOURCE_PARAMS' => (new \Bitrix\Main\ORM\Fields\ArrayField('SOURCE_PARAMS', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SOURCE_PARAMS')
			)))->configureSerializationPhp(),
			'CONTENT' => new Entity\StringField('CONTENT', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CONTENT'),
				'required' => true,
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
			)),
			'SEARCH_CONTENT' => new Entity\StringField('SEARCH_CONTENT', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SEARCH_CONTENT')
			)),
			'ASSETS' => (new \Bitrix\Main\ORM\Fields\ArrayField('ASSETS', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SOURCE_PARAMS')
			)))->configureSerializationPhp(),
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
	 * Prepare change to save.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	protected static function prepareChange(Entity\Event $event)
	{
		$result = new Entity\EventResult();
		$primary = $event->getParameter('primary');
		$fields = $event->getParameter('fields');

		// calculate filter hash
		if (array_key_exists('SOURCE_PARAMS', $fields))
		{
			\Bitrix\Landing\Source\FilterEntity::setFilter(
				$primary['ID'],
				$fields['SOURCE_PARAMS']
			);
			$result->modifyFields([
				'SOURCE_PARAMS' => $fields['SOURCE_PARAMS']
			]);
		}

		return $result;
	}

	/**
	 * Before update handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function onBeforeUpdate(Entity\Event $event)
	{
		return self::prepareChange($event);
	}

	/**
	 * After delete handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function onAfterDelete(Entity\Event $event)
	{
		$result = new Entity\EventResult();
		$primary = $event->getParameter('primary');

		if ($primary)
		{
			\Bitrix\Landing\File::deleteFromBlock($primary['ID']);
			\Bitrix\Landing\Source\FilterEntity::removeBlock($primary['ID']);
			\Bitrix\Landing\Chat\Binding::unbindingBlock($primary['ID']);
		}

		return $result;
	}
}