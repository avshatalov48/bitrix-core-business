<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Sender\Access\Role;

use Bitrix\Main\Access\Role\RoleDictionary;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Access\Permission\PermissionDictionary;
use Bitrix\Sender\Access\Permission\PermissionTable;

Loc::loadMessages(__FILE__);

class RoleUtil extends \Bitrix\Main\Access\Role\RoleUtil
{

	protected static function getRoleTableClass(): string
	{
		return RoleTable::class;
	}

	protected static function getRoleRelationTableClass(): string
	{
		return RoleRelationTable::class;
	}

	protected static function getPermissionTableClass(): string
	{
		return PermissionTable::class;
	}

	protected static function getRoleDictionaryClass(): ?string
	{
		return RoleDictionary::class;
	}

	/**
	 * pre installed roles list
	 * @return array
	 */
	public static function preparedRoleMap()
	{
		return [
			'ADMIN' => [
				PermissionDictionary::MAILING_VIEW,
				PermissionDictionary::MAILING_PAUSE_START_STOP,
				PermissionDictionary::MAILING_MESSENGER_EDIT,
				PermissionDictionary::MAILING_EMAIL_EDIT,
				PermissionDictionary::MAILING_CLIENT_VIEW,
				PermissionDictionary::MAILING_AUDIO_CALL_EDIT,
				PermissionDictionary::MAILING_INFO_CALL_EDIT,
				PermissionDictionary::MAILING_SMS_EDIT,
				PermissionDictionary::ADS_VIEW,
				PermissionDictionary::ADS_PAUSE_START_STOP,
				PermissionDictionary::ADS_YANDEX_EDIT,
				PermissionDictionary::ADS_FB_INSTAGRAM_EDIT,
				PermissionDictionary::ADS_MARKETING_INSTAGRAM_EDIT,
				PermissionDictionary::ADS_MARKETING_FB_EDIT,
				PermissionDictionary::ADS_GOOGLE_EDIT_MSGVER_1,
				PermissionDictionary::ADS_LOOK_ALIKE_FB_EDIT,
				PermissionDictionary::ADS_LOOK_ALIKE_VK_EDIT,
				PermissionDictionary::ADS_MASTER_YANDEX_EDIT,
				PermissionDictionary::ADS_VK_EDIT,
				PermissionDictionary::ADS_CLIENT_VIEW,
				PermissionDictionary::RC_EDIT,
				PermissionDictionary::RC_VIEW,
				PermissionDictionary::RC_PAUSE_START_STOP,
				PermissionDictionary::SEGMENT_CLIENT_OWN_CATEGORY,
				PermissionDictionary::SEGMENT_EDIT,
				PermissionDictionary::SEGMENT_VIEW,
				PermissionDictionary::SEGMENT_LEAD_EDIT,
				PermissionDictionary::SEGMENT_CLIENT_EDIT,
				PermissionDictionary::SEGMENT_CLIENT_PERSONAL_EDIT,
				PermissionDictionary::SEGMENT_CLIENT_VIEW,
				PermissionDictionary::SETTINGS_EDIT,
//				PermissionDictionary::SETTINGS_VIEW,
				PermissionDictionary::BLACKLIST_EDIT,
				PermissionDictionary::BLACKLIST_VIEW,
				PermissionDictionary::START_VIEW,
				PermissionDictionary::TEMPLATE_EDIT,
				PermissionDictionary::TEMPLATE_VIEW,
			],
			'CHIEF' => [
				PermissionDictionary::MAILING_VIEW,
				PermissionDictionary::MAILING_PAUSE_START_STOP,
				PermissionDictionary::MAILING_MESSENGER_EDIT,
				PermissionDictionary::MAILING_EMAIL_EDIT,
				PermissionDictionary::MAILING_CLIENT_VIEW,
				PermissionDictionary::MAILING_AUDIO_CALL_EDIT,
				PermissionDictionary::MAILING_INFO_CALL_EDIT,
				PermissionDictionary::MAILING_SMS_EDIT,
				PermissionDictionary::ADS_VIEW,
				PermissionDictionary::ADS_PAUSE_START_STOP,
				PermissionDictionary::ADS_YANDEX_EDIT,
				PermissionDictionary::ADS_FB_INSTAGRAM_EDIT,
				PermissionDictionary::ADS_MARKETING_INSTAGRAM_EDIT,
				PermissionDictionary::ADS_MARKETING_FB_EDIT,
				PermissionDictionary::ADS_GOOGLE_EDIT_MSGVER_1,
				PermissionDictionary::ADS_LOOK_ALIKE_FB_EDIT,
				PermissionDictionary::ADS_LOOK_ALIKE_VK_EDIT,
				PermissionDictionary::ADS_MASTER_YANDEX_EDIT,
				PermissionDictionary::ADS_VK_EDIT,
				PermissionDictionary::ADS_CLIENT_VIEW,
				PermissionDictionary::RC_EDIT,
				PermissionDictionary::RC_PAUSE_START_STOP,
				PermissionDictionary::RC_VIEW,
				PermissionDictionary::SEGMENT_CLIENT_OWN_CATEGORY,
				PermissionDictionary::SEGMENT_EDIT,
				PermissionDictionary::SEGMENT_VIEW,
				PermissionDictionary::SEGMENT_LEAD_EDIT,
				PermissionDictionary::SEGMENT_CLIENT_EDIT,
				PermissionDictionary::SEGMENT_CLIENT_PERSONAL_EDIT,
				PermissionDictionary::START_VIEW,
				PermissionDictionary::SEGMENT_CLIENT_VIEW
			],
			'MANAGER' => [
				PermissionDictionary::MAILING_VIEW,
				PermissionDictionary::MAILING_PAUSE_START_STOP,
				PermissionDictionary::MAILING_MESSENGER_EDIT,
				PermissionDictionary::MAILING_EMAIL_EDIT,
				PermissionDictionary::MAILING_AUDIO_CALL_EDIT,
				PermissionDictionary::MAILING_INFO_CALL_EDIT,
				PermissionDictionary::MAILING_SMS_EDIT,
				PermissionDictionary::ADS_VIEW,
				PermissionDictionary::ADS_PAUSE_START_STOP,
				PermissionDictionary::ADS_YANDEX_EDIT,
				PermissionDictionary::ADS_FB_INSTAGRAM_EDIT,
				PermissionDictionary::ADS_MARKETING_INSTAGRAM_EDIT,
				PermissionDictionary::ADS_MARKETING_FB_EDIT,
				PermissionDictionary::ADS_GOOGLE_EDIT_MSGVER_1,
				PermissionDictionary::ADS_LOOK_ALIKE_FB_EDIT,
				PermissionDictionary::ADS_LOOK_ALIKE_VK_EDIT,
				PermissionDictionary::ADS_MASTER_YANDEX_EDIT,
				PermissionDictionary::ADS_VK_EDIT,
				PermissionDictionary::RC_EDIT,
				PermissionDictionary::RC_PAUSE_START_STOP,
				PermissionDictionary::RC_VIEW,
				PermissionDictionary::SEGMENT_CLIENT_OWN_CATEGORY,
				PermissionDictionary::SEGMENT_EDIT,
				PermissionDictionary::SEGMENT_VIEW,
				PermissionDictionary::SEGMENT_LEAD_EDIT,
				PermissionDictionary::SEGMENT_CLIENT_EDIT,
				PermissionDictionary::START_VIEW,
				PermissionDictionary::SEGMENT_CLIENT_PERSONAL_EDIT
			]
		];
	}

	/**
	 * building sql insert list
	 * @param array $permissions permission list
	 * @param int $roleId role identification number
	 *
	 * @return array
	 */
	public static function buildInsertPermissionQuery(array $permissions, int $roleId): array
	{
		$query = [];

		foreach ($permissions as $permission)
		{
			$query[] = [
				'ROLE_ID' => $roleId,
				'PERMISSION_ID' => $permission,
				'VALUE' => PermissionDictionary::VALUE_YES,
			];
		}

		return $query;
	}

	/**
	 * insert data to permission table
	 * @param array $valuesData
	 *
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 */
	public static function insertPermissions(array $valuesData)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		foreach ($helper->prepareMergeMultiple('b_sender_permission', ['ROLE_ID', 'PERMISSION_ID'], $valuesData) as $sql)
		{
			$connection->query($sql);
		}
	}

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	public static function getLocalizedName(string $key)
	{
		return Loc::getMessage('SENDER_ROLE_' . $key);
	}
}
