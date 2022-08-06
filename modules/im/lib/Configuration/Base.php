<?php

namespace Bitrix\Im\Configuration;

abstract class Base
{
	/**
	 * @var string It needs to be redefined in the child class.
	 * It is used in the template as a prefix to get records in the database in like-requests
	 */
	protected const ENTITY = 'ba';

	/** @var string  It is used to separate semantic parts in the template*/
	protected const SEPARATOR = '|';

	protected const CHUNK_LENGTH = 1000;

	/**
	 * Get default entity settings
	 * @return array
	 */
	abstract public static function getDefaultSettings(): array;

	/**
	 * Get the current user settings from database
	 *
	 * @param int $userId
	 *
	 * @return array
	 */
	abstract public static function getUserSettings(int $userId): array;

	/**
	 * Get group settings from database
	 *
	 * @param int $groupId
	 *
	 * @return array
	 */
	abstract public static function getGroupSettings(int $groupId): array;

	/**
	 * Set the group settings to the database
	 *
	 * @param int $groupId
	 * @param array $settings
	 */
	abstract public static function setSettings(int $groupId, array $settings): void;

	/**
	 * Update the group settings in the database
	 *
	 * @param int $groupId
	 * @param array $settings
	 */
	abstract public static function updateGroupSettings(int $groupId, array $settings): void;

	/**
	 * You need to encode the settings into templates to add to the database
	 *
	 * @param array $settings
	 *
	 * @see
	 * @return array
	 */
	abstract protected static function encodeSettings(array $settings): array;

	/**
	 * You need to decode the templates of settings received from the database into the original format
	 *
	 * @param array $rowSettings
	 *
	 * @return array
	 */
	abstract public static function decodeSettings(array $rowSettings): array;

}