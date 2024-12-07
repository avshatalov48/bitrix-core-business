<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class FinderDestTable
 * Is used to store and retrieve last used destinations in the destinations selector dialog
 * @package Bitrix\Main
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_FinderDest_Query query()
 * @method static EO_FinderDest_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_FinderDest_Result getById($id)
 * @method static EO_FinderDest_Result getList(array $parameters = [])
 * @method static EO_FinderDest_Entity getEntity()
 * @method static \Bitrix\Main\EO_FinderDest createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_FinderDest_Collection createCollection()
 * @method static \Bitrix\Main\EO_FinderDest wakeUpObject($row)
 * @method static \Bitrix\Main\EO_FinderDest_Collection wakeUpCollection($rows)
 */
class FinderDestTable extends Main\UI\EntitySelector\EntityUsageTable
{
	/*public static function getTableName()
	{
		return 'b_finder_dest';
	}*/

	/*public static function getMap()
	{
		global $USER;

		return array(
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			new Entity\ReferenceField(
				'USER',
				'Bitrix\Main\UserTable',
				array('=this.USER_ID' => 'ref.ID')
			),
			'CODE' => array(
				'data_type' => 'string',
				'primary' => true
			),
			'CODE_USER_ID' => array(
				'data_type' => 'integer'
			),
			'CODE_TYPE' => array(
				'data_type' => 'string'
			),
			new Entity\ReferenceField(
				'CODE_USER',
				'Bitrix\Main\UserTable',
				array('=this.CODE_USER_ID' => 'ref.ID')
			),
			new Entity\ReferenceField(
				'CODE_USER_CURRENT',
				'Bitrix\Main\UserTable',
				array(
					'=this.CODE_USER_ID' => 'ref.ID',
					'=this.USER_ID' => new SqlExpression('?i', $USER->GetId())
				)
			),
			'CONTEXT' => array(
				'data_type' => 'string',
				'primary' => true
			),
			'LAST_USE_DATE' => array(
				'data_type' => 'datetime'
			)
		);
	}*/

	/**
	 * Adds or updates data about using destinations by a user
	 *
	 * @param array $data data to store,
	 * keys:
	 *    USER_ID - user who selected a destination,
	 *    CODE - code or array of codes of destinations,
	 *    CONTEXT - the place where a destination is selected
	 *
	 * @return void
	 */
	public static function merge(array $data)
	{
		global $USER;

		$userId = (
			isset($data['USER_ID']) && intval($data['USER_ID']) > 0
				? intval($data['USER_ID'])
				: (is_object($GLOBALS['USER']) ? $USER->getId() : 0)
		);

		if ($userId <= 0 || empty($data['CODE']) || empty($data['CONTEXT']) || !is_string($data['CONTEXT']))
		{
			return;
		}

		if (is_array($data['CODE']))
		{
			$dataModified = $data;

			foreach ($data['CODE'] as $code)
			{
				$dataModified['CODE'] = $code;
				FinderDestTable::merge($dataModified);
			}

			return;
		}

		if (!is_string($data['CODE']))
		{
			return;
		}

		foreach (Main\UI\EntitySelector\Converter::getCompatEntities() as $entityId => $entity)
		{
			if (preg_match('/'.$entity['pattern'].'/i', $data['CODE'], $matches))
			{
				$itemId = $matches['itemId'];
				$prefix = $matches['prefix'];

				if (isset($entity['itemId']) && is_callable($entity['itemId']))
				{
					$itemId = $entity['itemId']($prefix, $itemId);
				}

				parent::merge([
					'USER_ID' => $userId,
					'CONTEXT' => mb_strtoupper($data['CONTEXT']),
					'ENTITY_ID' => $entityId,
					'ITEM_ID' => $itemId,
					'PREFIX' => mb_strtoupper($prefix)
			  	]);

				$cache = new \CPHPCache;
				$cache->cleanDir('/sonet/log_dest_sort/'.intval($userId / 100));
				$cache->cleanDir(\Bitrix\Main\UI\Selector\Entities::getCacheDir([
					'userId' => $userId,
				]));

				return;
			}
		}
	}

	/**
	 * Converts access rights into destination codes
	 *
	 * @param array $rights access right codes to convert
	 * @param array $excludeCodes access right codes to not process
	 *
	 * @return array destination codes
	 */
	public static function convertRights($rights, $excludeCodes = [])
	{
		$result = [];

		if (is_array($rights))
		{
			foreach ($rights as $right)
			{
				if (
					!in_array($right, $excludeCodes)
					&& (
						preg_match('/^SG(\d+)$/i', $right, $matches)
						|| preg_match('/^U(\d+)$/i', $right, $matches)
						|| preg_match('/^DR(\d+)$/i', $right, $matches)
						|| preg_match('/^CRMCONTACT(\d+)$/i', $right, $matches)
						|| preg_match('/^CRMCOMPANY(\d+)$/i', $right, $matches)
						|| preg_match('/^CRMLEAD(\d+)$/i', $right, $matches)
						|| preg_match('/^CRMDEAL(\d+)$/i', $right, $matches)
					)
				)
				{
					$result[] = mb_strtoupper($right);
				}
			}

			$result = array_unique($result);
		}

		return $result;
	}

	/**
	 * @deprecated isn't used in the kernel already
	 * Handler for onAfterAjaxActionCreateFolderWithSharing, onAfterAjaxActionAppendSharing and onAfterAjaxActionChangeSharingAndRights events of disk module
	 * Converts sharings into destination codes and stores them
	 *
	 * @param array $sharings
	 *
	 * @return void
	 */
	public static function onAfterDiskAjaxAction($sharings)
	{
		if (is_array($sharings))
		{
			$destinationCodes = [];
			foreach ($sharings as $key => $sharing)
			{
				$destinationCodes[] = $sharing->getToEntity();
			}

			if (!empty($destinationCodes))
			{
				$destinationCodes = array_unique($destinationCodes);
				\Bitrix\Main\FinderDestTable::merge([
					"CONTEXT" => "DISK_SHARE",
					"CODE" => \Bitrix\Main\FinderDestTable::convertRights($destinationCodes)
				]);
			}
		}
	}

	/**
	 * Used once to fill b_finder_dest table
	 *
	 * @return void
	 */
	public static function migrateData()
	{
		$res = \CUserOptions::getList(
			[],
			[
				"CATEGORY" => "socialnetwork",
				"NAME" => "log_destination"
			]
		);

		while ($option = $res->fetch())
		{
			if (!empty($option["VALUE"]))
			{
				$optionValue = unserialize($option["VALUE"], ['allowed_classes' => false]);

				if (is_array($optionValue))
				{
					foreach ($optionValue as $key => $val)
					{
						if (in_array(
							$key,
							["users", "sonetgroups", "department", "companies", "contacts", "leads", "deals"]
						))
						{
							$codes = \CUtil::jsObjectToPhp($val);
							if (is_array($codes))
							{
								\Bitrix\Main\FinderDestTable::merge(
									[
										"USER_ID" => $option["USER_ID"],
										"CONTEXT" => "blog_post",
										"CODE" => array_keys($codes)
									]
								);
							}
						}
					}
				}
			}
		}

		$res = \CUserOptions::getList(
			[],
			[
				"CATEGORY" => "crm",
				"NAME" => "log_destination"
			]
		);

		while ($option = $res->fetch())
		{
			if (!empty($option["VALUE"]))
			{
				$optionValue = unserialize($option["VALUE"], ['allowed_classes' => false]);

				if (is_array($optionValue))
				{
					foreach ($optionValue as $key => $val)
					{
						$codes = explode(',', $val);
						if (is_array($codes))
						{
							\Bitrix\Main\FinderDestTable::merge(
								[
									"USER_ID" => $option["USER_ID"],
									"CONTEXT" => "crm_post",
									"CODE" => $codes
								]
							);
						}
					}
				}
			}
		}
	}

	/**
	 * Returns array of email user IDs fetched from users (email and not email) destination codes
	 *
	 * @param mixed $code user destination code or array of them
	 *
	 * @return array
	 */
	public static function getMailUserId($code)
	{
		$userId = [];
		$result = [];

		if (!is_array($code))
		{
			$code = [$code];
		}

		foreach ($code as $val)
		{
			if (preg_match('/^U(\d+)$/', $val, $matches))
			{
				$userId[] = $matches[1];
			}
		}

		if (!empty($userId))
		{
			$res = \Bitrix\Main\UserTable::getList(
				[
					'order' => [],
					'filter' => [
						"ID" => $userId,
						"=EXTERNAL_AUTH_ID" => 'email'
					],
					'select' => ["ID"]
				]
			);

			while ($user = $res->fetch())
			{
				$result[] = $user["ID"];
			}
		}

		return $result;
	}

}
