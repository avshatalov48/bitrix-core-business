<?php

namespace Bitrix\Main\UserField\Access\Permission;

use Bitrix\Main\Access\Permission\AccessPermissionTable;
use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\UserAccessTable;
use Bitrix\Main\UserFieldTable;

/**
 * Class UserFieldPermissionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserFieldPermission_Query query()
 * @method static EO_UserFieldPermission_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserFieldPermission_Result getById($id)
 * @method static EO_UserFieldPermission_Result getList(array $parameters = [])
 * @method static EO_UserFieldPermission_Entity getEntity()
 * @method static \Bitrix\Main\UserField\Access\Permission\UserFieldPermission createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\UserField\Access\Permission\EO_UserFieldPermission_Collection createCollection()
 * @method static \Bitrix\Main\UserField\Access\Permission\UserFieldPermission wakeUpObject($row)
 * @method static \Bitrix\Main\UserField\Access\Permission\EO_UserFieldPermission_Collection wakeUpCollection($rows)
 */
class UserFieldPermissionTable extends AccessPermissionTable
{
	private const PERMISSION_ALLOWED = 1;

	public static function getTableName()
	{
		return 'b_user_field_permission';
	}

	public static function getObjectClass()
	{
		return UserFieldPermission::class;
	}

	public static function getMap()
	{
		return [
			new Entity\IntegerField('ID', [
				'autocomplete' => true,
				'primary' => true
			]),
			new Entity\IntegerField('ENTITY_TYPE_ID', [
				'required' => true
			]),
			new Entity\IntegerField('USER_FIELD_ID', [
				'required' => true
			]),
			new Entity\StringField('ACCESS_CODE', [
				'required' => true
			]),
			new Entity\StringField('PERMISSION_ID', [
				'required' => true
			]),
			new Entity\IntegerField('VALUE', [
				'required' => true
			]),
			(new Reference(
				'USER_FIELD',
				UserFieldTable::class,
				Join::on('this.USER_FIELD_ID', 'ref.ID')
			)),
			new Reference(
				'USER_ACCESS',
				UserAccessTable::class,
				Join::on('this.ACCESS_CODE', 'ref.ACCESS_CODE')
			),
		];
	}

	/**
	 * @param $primary
	 * @param array $data
	 */
	protected static function updateChildPermission($primary, array $data)
	{
		$data = self::loadUpdateRow($primary, $data);
		if ((int)$data['VALUE'] === PermissionDictionary::VALUE_YES) {
			return;
		}
		$sql = "
			UPDATE `" . static::getTableName() . "` 
			SET VALUE = " . PermissionDictionary::VALUE_NO . "
			WHERE 
				USER_FIELD_ID = " . $data['USER_FIELD_ID'] . "
				AND ACCESS_CODE = " . $data['ACCESS_CODE'] . "
				AND PERMISSION_ID LIKE '" . $data['PERMISSION_ID'] . ".%' 
		";
		static::getEntity()->getConnection()->query($sql);
	}

	/**
	 * @param array $data
	 * @return bool
	 */
	public static function validateRow(array $data): bool
	{
		$parentPermissions = PermissionDictionary::getParentsPath($data['PERMISSION_ID']);
		if (!$parentPermissions)
		{
			return true;
		}

		$res = self::getRow([
			'select' => ['VALUE'],
			'filter' => [
				'=USER_FIELD_ID' => (int)$data['USER_FIELD_ID'],
				'=ACCESS_CODE' => (int)$data['ACCESS_CODE'],
				'%=PERMISSION_ID' => $parentPermissions,
				'=VALUE' => PermissionDictionary::VALUE_NO
			]
		]);

		if (is_array($res) && count($res))
		{
			return false;
		}

		return true;
	}

	/**
	 * @param int $entityTypeID
	 * @return array
	 */
	public static function getUserFieldsAccessCodes(int $entityTypeID): array
	{
		$query = new Entity\Query(self::getEntity());
		$query->addSelect('USER_FIELD.FIELD_NAME', 'FIELD_NAME');
		$query->addSelect('ACCESS_CODE');
		$query->addSelect('USER_ACCESS.USER_ID', 'USER_ID');
		$query->addSelect('USER_FIELD_ID');
		$query->addFilter('=ENTITY_TYPE_ID', $entityTypeID);
		$query->addFilter('=VALUE', self::PERMISSION_ALLOWED);
		$query->whereNotNull('FIELD_NAME');

		$dbResult = $query->exec();

		return $dbResult->fetchAll();
	}

	/**
	 * @param array|bool $accessCodes
	 * @param string $fieldName
	 * @param int $entityTypeId
	 * @param string $permissionId
	 * @param string|null $entityTypeName
	 */
	public static function saveEntityConfiguration(
		$accessCodes,
		string $fieldName,
		int $entityTypeId,
		string $permissionId,
		?string $entityTypeName = null
	): void
	{
		if ($userField = self::getUserFieldId($fieldName, $entityTypeName))
		{
			self::removeEntityConfiguration($userField['ID'], $entityTypeId);
			if (is_array($accessCodes))
			{
				foreach ($accessCodes as $accessCode)
				{
					/**
					 * @var $permission UserFieldPermission
					 */
					$permission = self::createObject([
						'ENTITY_TYPE_ID' => $entityTypeId,
						'USER_FIELD_ID' => $userField['ID'],
						'ACCESS_CODE' => $accessCode['ID'],
						'PERMISSION_ID' => $permissionId,
						'VALUE' => self::PERMISSION_ALLOWED
					]);
					$permission->save();
				}
			}
		}
	}

	/**
	 * @param int $userFieldId
	 * @param int $entityTypeId
	 */
	private static function removeEntityConfiguration(int $userFieldId, int $entityTypeId): void
	{
		self::deleteList([
			'=ENTITY_TYPE_ID' => $entityTypeId,
			'=USER_FIELD_ID' => $userFieldId
		]);
	}

	/**
	 * @param string $fieldName
	 * @param string $entityId
	 * @return array|null
	 */
	private static function getUserFieldId(string $fieldName, ?string $entityId = null): ?array
	{
		$filter = ['=FIELD_NAME' => $fieldName];
		if ($entityId)
		{
			$filter['=ENTITY_ID'] = $entityId;
		}

		return UserFieldTable::getRow([
			'select' => ['ID'],
			'filter' => $filter
		]);
	}
}
