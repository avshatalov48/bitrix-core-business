<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender;

use Bitrix\Main\Access\Entity\DataManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type as MainType;

Loc::loadMessages(__FILE__);

class TimeLineQueueTable extends DataManager
{
	const STATUS_NEW = 'N';

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_timeline_queue';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID'      => [
				'data_type'    => 'integer',
				'primary'      => true,
				'autocomplete' => true,
			],
			'POSTING_ID'      => [
				'data_type'    => 'integer',
				'autocomplete' => true,
			],
			'ENTITY_ID'      => [
				'data_type' => 'integer',
				'required'  => true,
			],
			'RECIPIENT_ID'      => [
				'data_type' => 'integer',
				'required'  => true,
			],
			'CONTACT_TYPE_ID' => [
				'data_type' => 'integer',
				'required'  => true,
			],
			'CONTACT_CODE'    => [
				'data_type' => 'string',
				'required'  => true
			],
			'FIELDS'          => [
				'data_type' => 'string',
				'required'  => true,
			],
			'DATE_INSERT'     => [
				'data_type'     => 'datetime',
				'required'      => true,
				'default_value' => new MainType\DateTime(),
			],
			'STATUS'          => [
				'data_type'     => 'string',
				'required'      => true,
				'default_value' => static::STATUS_NEW,
			],
		];
	}
}