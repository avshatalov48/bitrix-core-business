<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Manager;

Loc::loadMessages(__FILE__);

class LandingTable extends Entity\DataManager
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
	 * Stored fields for save separate.
	 * @var array
	 */
	protected static $additionalFields = array();

	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing';
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
			'CODE' => new Entity\StringField('CODE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_CODE')
			)),
			'RULE' => new Entity\StringField('RULE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_RULE')
			)),
			'ACTIVE' => new Entity\StringField('ACTIVE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_ACTIVE'),
				'default_value' => 'Y'
			)),
			'PUBLIC' => new Entity\StringField('PUBLIC', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_PUBLIC'),
				'default_value' => 'Y'
			)),
			'TITLE' => new Entity\StringField('TITLE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_TITLE'),
				'required' => true
			)),
			'XML_ID' => new Entity\StringField('XML_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_XML_ID')
			)),
			'DESCRIPTION' => new Entity\StringField('DESCRIPTION', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DESCRIPTION')
			)),
			'TPL_ID' => new Entity\IntegerField('TPL_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_TPL_ID')
			)),
			'SITE_ID' => new Entity\IntegerField('SITE_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SITE_ID'),
				'required' => true
			)),
			'SITE' => new Entity\ReferenceField(
				'SITE',
				'Bitrix\Landing\Internals\SiteTable',
				array('=this.SITE_ID' => 'ref.ID')
			),
			'SITEMAP' => new Entity\StringField('SITEMAP', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_LANDING_SITEMAP'),
				'default_value' => 'N'
			)),
			'FOLDER' => new Entity\StringField('FOLDER', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_FOLDER'),
				'default_value' => 'N'
			)),
			'FOLDER_ID' => new Entity\IntegerField('FOLDER_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_FOLDER_ID')
			)),
			'CREATED_BY_ID' => new Entity\IntegerField('CREATED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CREATED_BY_ID'),
				'required' => true
			)),
			'CREATED_BY' => new Entity\ReferenceField(
				'CREATED_BY',
				'Bitrix\Main\UserTable',
				array('=this.CREATED_BY_ID' => 'ref.ID')
			),
			'MODIFIED_BY_ID' => new Entity\IntegerField('MODIFIED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_MODIFIED_BY_ID'),
				'required' => true
			)),
			'MODIFIED_BY' => new Entity\ReferenceField(
				'MODIFIED_BY',
				'Bitrix\Main\UserTable',
				array('=this.MODIFIED_BY_ID' => 'ref.ID')
			),
			'DATE_CREATE' => new Entity\DatetimeField('DATE_CREATE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_CREATE'),
				'required' => true
			)),
			'DATE_MODIFY' => new Entity\DatetimeField('DATE_MODIFY', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_MODIFY'),
				'required' => true
			)),
			'DATE_PUBLIC' => new Entity\DatetimeField('DATE_PUBLIC', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_PUBLIC')
			))
		);
	}

	/**
	 * Set additional access filter.
	 * @param mixed $params ORM params.
	 * @return mixed
	 */
	public static function setAccessFilter($params)
	{
		/*if (
			!isset($params['filter']) ||
			!is_array($params['filter'])
		)
		{
			$params['filter'] = array();
		}
		$params['filter'][] = array(
			'ID' => array(251, 260)
		);*/

		return $params;
	}

	/**
	 * Prepare change to save.
	 * @param Entity\Event $event Event instance.
	 * @param string $actionType Action type: add / update.
	 * @return Entity\EventResult
	 */
	protected static function prepareChange(Entity\Event $event, $actionType)
	{
		$result = new Entity\EventResult();
		$primary = $event->getParameter('primary');
		$fields = $event->getParameter('fields');

		// additional fields save after
		if (array_key_exists('ADDITIONAL_FIELDS', $fields))
		{
			self::$additionalFields = $fields['ADDITIONAL_FIELDS'];
			$result->unsetFields(array('ADDITIONAL_FIELDS'));
		}
		else
		{
			self::$additionalFields = array();
		}

		// slash in CODE is not allowed
		if (
			array_key_exists('CODE', $fields) &&
			strpos($fields['CODE'], '/') !== false
		)
		{
			$result->setErrors(array(
				new Entity\EntityError(
					Loc::getMessage('LANDING_TABLE_ERROR_SLASH_IS_NOT_ALLOWED'),
					'SLASH_IS_NOT_ALLOWED'
				)
			));
			return $result;
		}
		// CODE can't be empty
		elseif (
			array_key_exists('CODE', $fields) &&
			$fields['CODE'] == ''
		)
		{
			$result->setErrors(array(
				new Entity\EntityError(
					Loc::getMessage('LANDING_TABLE_ERROR_CANT_BE_EMPTY'),
					'CANT_BE_EMPTY'
				)
			));
			return $result;
		}
		elseif (
			$actionType == self::ACTION_TYPE_ADD && array_key_exists('TITLE', $fields) &&
			(!array_key_exists('CODE', $fields) || trim($fields['CODE']) == '')
		)
		{
			$fields['CODE'] = \CUtil::translit(
				trim($fields['TITLE']),
				LANGUAGE_ID,
				array(
					'replace_space' => '',
					'replace_other' => ''
				));
			$result->modifyFields(array(
				'CODE' => $fields['CODE']
			));
		}

		// check rights for landing site
		if (array_key_exists('SITE_ID', $fields))
		{
			// for check rights call upper level
			$res = \Bitrix\Landing\Site::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'ID' => $fields['SITE_ID']
				)
			));
			if (!$res->fetch())
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_SITE_NOT_FOUND'),
						'SITE_NOT_FOUND'
					)
				));
			}
		}

		// check CODE unique in site group
		if (array_key_exists('CODE', $fields))
		{
			// get site id if no exists
			if (!array_key_exists('SITE_ID', $fields) && $primary)
			{
				$site = self::getById($primary['ID'])->fetch();
				$fields['SITE_ID'] = $site['SITE_ID'];
			}

			$i = 0;
			do
			{
				$newCode = $fields['CODE'] . ($i++ > 0 ? $i : '');
				$res = self::getList(array(
					'select' => array(
						'ID'
					),
					'filter' => array(
						'!ID' => $primary ? $primary['ID'] : 0,
						'SITE_ID' => $fields['SITE_ID'],
						'=CODE' => $newCode
					)
				));
			} while ($res->fetch());

			$fields['CODE'] = $newCode;
			$result->modifyFields(array(
				'CODE' => $fields['CODE']
			));
		}

		// all code blocks below promptly return result !

		// check if folder_id is folder
		if (
			array_key_exists('FOLDER_ID', $fields) &&
			$fields['FOLDER_ID'] > 0
		)
		{
			$res = self::getList(array(
				'select' => array(
					'FOLDER'
				),
				'filter' => array(
					'ID' => $fields['FOLDER_ID'],
					'=FOLDER' => 'Y'
				)
			));
			if (!$res->fetch())
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_ISNOT_FOLDER'),
						'ISNOT_FOLDER'
					)
				));
				return $result;
			}
		}

		// subfolder disabled
		if (
			array_key_exists('FOLDER', $fields) &&
			$fields['FOLDER'] == 'Y'
		)
		{
			if (!array_key_exists('FOLDER_ID', $fields))
			{
				$fields['FOLDER_ID'] = 0;
				if ($primary)
				{
					$row = self::getList(array(
						'select' => array(
							'FOLDER_ID'
						),
						'filter' => array(
							'ID' => $primary['ID']
						)
					))->fetch();
					$fields['FOLDER_ID'] = $row['FOLDER_ID'];
				}
			}
			if ($fields['FOLDER_ID'] > 0)
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_SUBFOLDER_DISABLED'),
						'SUBFOLDER_DISABLED'
					)
				));
				return $result;
			}
		}

		return $result;
	}

	/**
	 * Before add handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnBeforeAdd(Entity\Event $event)
	{
		// check page limit
		if (!Manager::checkFeature(Manager::FEATURE_CREATE_PAGE))
		{
			$result = new Entity\EventResult();
			$result->unsetFields(array('ADDITIONAL_FIELDS'));
			$result->setErrors(array(
				new Entity\EntityError(
					Loc::getMessage('LANDING_TABLE_ERROR_PAGE_LIMIT_REACHED'),
					'ERROR_PAGE_LIMIT_REACHED'
				)
			));
			return $result;
		}

		return self::prepareChange($event, self::ACTION_TYPE_ADD);
	}

	/**
	 * Before update handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnBeforeUpdate(Entity\Event $event)
	{
		return self::prepareChange($event, self::ACTION_TYPE_UPDATE);
	}

	/**
	 * Before delete handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnBeforeDelete(Entity\Event $event)
	{
		$result = new Entity\EventResult();
		$primary = $event->getParameter('primary');
		if ($primary)
		{
			$res = self::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'FOLDER_ID' => $primary['ID']
				),
				'limit' => 1
			));
			if ($res->fetch())
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_PAGE_FOLDER_NOT_EMPTY'),
						'ERROR_PAGE_FOLDER_NOT_EMPTY'
					)
				));
			}
		}
		return $result;
	}

	/**
	 * Save additional fields after add / update.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	protected static function saveAdditionalFields(Entity\Event $event)
	{
		$result = new Entity\EventResult();

		if (!empty(self::$additionalFields))
		{
			$primary = $event->getParameter('primary');
			Landing::saveAdditionalFields(
				$primary['ID'],
				self::$additionalFields
			);
		}

		return $result;
	}

	/**
	 * After add handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnAfterAdd(Entity\Event $event)
	{
		$primary = $event->getParameter('primary');
		$fields = $event->getParameter('fields');

		// set current landing as index, if this is single page
		if ($primary && isset($fields['SITE_ID']))
		{
			$res = self::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'SITE_ID' => $fields['SITE_ID']
				),
				'limit' => 2
			));
			if (count($res->fetchAll()) == 1)
			{
				SiteTable::update($fields['SITE_ID'], array(
					'LANDING_ID_INDEX' => $primary['ID']
				));
			}
		}

		return self::saveAdditionalFields($event);
	}

	/**
	 * After update handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnAfterUpdate(Entity\Event $event)
	{
		// for B24 we must update domain
		$fields = $event->getParameter('fields');
		if (
			array_key_exists('SITE_ID', $fields) &&
			Manager::isB24()
		)
		{
			\Bitrix\Landing\Site::update(
				$fields['SITE_ID'],
				array()
			);
		}
		return self::saveAdditionalFields($event);
	}

	/**
	 * After delete handler.
	 * @param Entity\Event $event Event instance.
	 * @return void
	 */
	public static function OnAfterDelete(Entity\Event $event)
	{
		$primary = $event->getParameter('primary');

		if ($primary)
		{
			\Bitrix\Landing\Syspage::deleteForLanding($primary['ID']);
			\Bitrix\Landing\Hook::deleteForLanding($primary['ID']);
			\Bitrix\Landing\TemplateRef::deleteArea($primary['ID']);
			\Bitrix\Landing\TemplateRef::setForLanding($primary['ID'], array());
		}
	}
}