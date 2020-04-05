<?php

namespace Bitrix\Seo;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

/**
 * Class AdvEntity
 *
 * Interface for Yandex.Direct and Google AdWords local data mirrors. Contains common fields defeinitions.
 *
 * Implemented fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ENGINE_ID int mandatory
 * <li> XML_ID string(255) mandatory
 * <li> LAST_UPDATE datetime optional
 * <li> SETTINGS string optional
 * </ul>
 *
 * @package Bitrix\Seo
 **/
class AdvEntity extends Entity\DataManager
{
	const ACTIVE = 'Y';
	const INACTIVE = 'N';
	
	protected static $skipRemoteUpdate = false;
	
	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('ADV_CAMPAIGN_ENTITY_ID_FIELD'),
			)),
			new IntegerField('ENGINE_ID', array(
				'required' => true,
				'title' => Loc::getMessage('ADV_CAMPAIGN_ENTITY_ENGINE_ID_FIELD'),
			)),
			new BooleanField('ACTIVE', array(
				'values' => array(static::INACTIVE, static::ACTIVE),
			)),
			new StringField('OWNER_ID', array(
				'required' => true,
				'title' => Loc::getMessage('ADV_CAMPAIGN_ENTITY_OWNER_ID_FIELD'),
			)),
			new StringField('OWNER_NAME', array(
				'required' => true,
				'title' => Loc::getMessage('ADV_CAMPAIGN_ENTITY_OWNER_NAME_FIELD'),
			)),
			new StringField('XML_ID', array(
				'required' => true,
				'title' => Loc::getMessage('ADV_CAMPAIGN_ENTITY_XML_ID_FIELD'),
			)),
			new StringField('NAME', array(
				'title' => Loc::getMessage('ADV_CAMPAIGN_ENTITY_NAME_FIELD'),
			)),
			new DatetimeField('LAST_UPDATE', array(
				'title' => Loc::getMessage('ADV_CAMPAIGN_ENTITY_LAST_UPDATE_FIELD'),
			)),
			new ArrayField('SETTINGS', array(
				'title' => Loc::getMessage('ADV_CAMPAIGN_ENTITY_SETTINGS_FIELD'),
			)),
			new Reference("ENGINE", SearchEngineTable::class, Join::on("this.ENGINE_ID", "ref.ID"), [
				"join_type" => "left",
			]),
		);
	}
	
	public static function setSkipRemoteUpdate($value)
	{
		static::$skipRemoteUpdate = $value;
	}
	
	public static function onBeforeAdd(Entity\Event $event)
	{
		$result = new Entity\EventResult();
		$result->modifyFields([
			'LAST_UPDATE' => new DateTime(),
			'ACTIVE' => static::ACTIVE,
		]);
		
		return $result;
	}
	
	public static function onBeforeUpdate(Entity\Event $event)
	{
		$result = new Entity\EventResult();
		$result->modifyFields(['LAST_UPDATE' => new DateTime()]);
		
		return $result;
	}
	
}
