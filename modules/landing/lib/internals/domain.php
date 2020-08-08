<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class DomainTable extends Entity\DataManager
{
	/**
	 * Code of https protocol.
	 */
	const PROTOCOL_HTTPS = 'https';

	/**
	 * Code of http protocol.
	 */
	const PROTOCOL_HTTP = 'http';

	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_domain';
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
			'ACTIVE' => new Entity\StringField('ACTIVE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ACTIVE'),
				'default_value' => 'Y'
			)),
			'DOMAIN' => new Entity\StringField('DOMAIN', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DOMAIN'),
				'required' => true
			)),
			'XML_ID' => new Entity\StringField('XML_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_XML_ID')
			)),
			'PROTOCOL' => new Entity\StringField('PROTOCOL', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_PROTOCOL'),
				'required' => true,
				'default_value' => self::PROTOCOL_HTTPS
			)),
			'PROVIDER' => new Entity\StringField('PROVIDER', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_PROVIDER')
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
			))
		);
	}

	/**
	 * Get available protocol list.
	 * @return array
	 */
	public static function getProtocolList()
	{
		return array(
			self::PROTOCOL_HTTPS => 'https',
			self::PROTOCOL_HTTP => 'http'
		);
	}

	/**
	 * Valid or not protocol.
	 * @param string $protocol Protocol.
	 * @return boolean
	 */
	protected static function isValidProtocol($protocol)
	{
		$list = self::getProtocolList();
		return isset($list[$protocol]);
	}

	/**
	 * Prepare change to save.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	protected static function prepareChange(Entity\Event $event)
	{
		$result = new Entity\EventResult();
		$fields = $event->getParameter('fields');
		$primary = $event->getParameter('primary');
		$update = array();

		// prepare CODE - base part of URL
		if (array_key_exists('DOMAIN', $fields))
		{
			$url = parse_url($fields['DOMAIN']);
			if (isset($url['host']))
			{
				$fields['DOMAIN'] = $url['host'];
			}
			else
			{
				$fields['DOMAIN'] = trim($fields['DOMAIN']);
			}
			$res = self::getList(array(
				'select' => array(
					'*'
				),
				'filter' => array(
					'!ID' => $primary ? $primary['ID'] : 0,
					'=DOMAIN' => $fields['DOMAIN']
				)
			));
			if ($res->fetch())
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_DOMAIN_IS_NOT_UNIQUE'),
						'DOMAIN_IS_NOT_UNIQUE'
					)
				));
				return $result;
			}
			$update['DOMAIN'] = $fields['DOMAIN'];
		}

		// force set protocol
		$fields['PROTOCOL'] = Manager::isHttps()
							? self::PROTOCOL_HTTPS
							: self::PROTOCOL_HTTP;
		$update['PROTOCOL'] = $fields['PROTOCOL'];

		// modify fields
		if (!empty($update))
		{
			$result->modifyFields($update);
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
		return self::prepareChange($event);
	}

	/**
	 * Before update handler.
	 * @param Entity\Event $event Event instance.
	 * @return Entity\EventResult
	 */
	public static function OnBeforeUpdate(Entity\Event $event)
	{
		return self::prepareChange($event);
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
		// check if domain is not empty
		if ($primary)
		{
			$res = SiteTable::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'DOMAIN_ID' => $primary['ID'],
					'CHECK_PERMISSIONS' => 'N'
				)
			));
			if ($res->fetch())
			{
				$result->setErrors(array(
					new Entity\EntityError(
						Loc::getMessage('LANDING_TABLE_ERROR_DOMAIN_IS_NOT_EMPTY'),
						'DOMAIN_IS_NOT_EMPTY'
					)
				));
				return $result;
			}
		}
		return $result;
	}
}