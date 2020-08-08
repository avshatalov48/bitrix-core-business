<?php
namespace Bitrix\Forum;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Type\DateTime;

/**
 * Class UserTopicTable
 *
 * @package Bitrix\Forum
 */
class UserTopicTable extends DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string|null
	 */
	public static function getTableName(): string
	{
		return 'b_forum_user_topic';
	}

	/**
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'autocomplete' => true,
			],
			'TOPIC_ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'required' => true,
			],
			'USER_ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'required' => true,
			],
			'FORUM_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'LAST_VISIT' => [
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => static function() {
					return new DateTime();
				},
			],
		];
	}
}