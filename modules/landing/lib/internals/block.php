<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class BlockTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Block_Query query()
 * @method static EO_Block_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Block_Result getById($id)
 * @method static EO_Block_Result getList(array $parameters = array())
 * @method static EO_Block_Entity getEntity()
 * @method static \Bitrix\Landing\Internals\EO_Block createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Internals\EO_Block_Collection createCollection()
 * @method static \Bitrix\Landing\Internals\EO_Block wakeUpObject($row)
 * @method static \Bitrix\Landing\Internals\EO_Block_Collection wakeUpCollection($rows)
 */
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
			'TPL_CODE' => new Entity\StringField('TPL_CODE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_TPL_CODE'),
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
			'DESIGNED' => new Entity\StringField('DESIGNED', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DESIGNED'),
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
			'FAVORITE_META' => (new \Bitrix\Main\ORM\Fields\ArrayField('FAVORITE_META', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_FAVORITE_META')
			)))->configureSerializationPhp(),
			'HISTORY_STEP_DESIGNER' => new Entity\IntegerField('HISTORY_STEP_DESIGNER', array(
				'title' => 'History step for design block',
				'default_value' => 0,
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
	 * Prepare change to save.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	protected static function prepareChange(Entity\Event $event): Entity\EventResult
	{
		$result = new Entity\EventResult();
		$primary = $event->getParameter('primary');
		$fields = $event->getParameter('fields');
		$modifyFields = [];

		// calculate filter hash
		if (($primary['ID'] ?? null) && array_key_exists('SOURCE_PARAMS', $fields))
		{
			\Bitrix\Landing\Source\FilterEntity::setFilter(
				$primary['ID'],
				$fields['SOURCE_PARAMS']
			);
			$modifyFields['SOURCE_PARAMS'] = $fields['SOURCE_PARAMS'];
		}

		// work with content
		if (array_key_exists('CONTENT', $fields))
		{
			$replaced = false;
			$oldContent = null;

			if ($primary['ID'] ?? null)
			{
				$res = self::getList([
					'select' => [
						'CONTENT'
					],
					'filter' => [
						'ID' => $primary['ID']
					]
				]);
				$oldContent = $res->fetch()['CONTENT'] ?? null;
			}

			$fields['CONTENT'] = \Bitrix\Landing\Connector\Disk::sanitizeContent($fields['CONTENT'], $oldContent, $replaced);
			if ($replaced)
			{
				$modifyFields['CONTENT'] = $fields['CONTENT'];
			}
		}

		if ($modifyFields)
		{
			$result->modifyFields($modifyFields);
		}

		return $result;
	}

	/**
	 * Before add handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function onBeforeAdd(Entity\Event $event): Entity\EventResult
	{
		return self::prepareChange($event);
	}

	/**
	 * Before update handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function onBeforeUpdate(Entity\Event $event): Entity\EventResult
	{
		return self::prepareChange($event);
	}

	/**
	 * After delete handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function onAfterDelete(Entity\Event $event): Entity\EventResult
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
