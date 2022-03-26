<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Main\UserConsent\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

/**
 * Class UserConsentItemTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserConsentItem_Query query()
 * @method static EO_UserConsentItem_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserConsentItem_Result getById($id)
 * @method static EO_UserConsentItem_Result getList(array $parameters = [])
 * @method static EO_UserConsentItem_Entity getEntity()
 * @method static \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem_Collection createCollection()
 * @method static \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem wakeUpObject($row)
 * @method static \Bitrix\Main\UserConsent\Internals\EO_UserConsentItem_Collection wakeUpCollection($rows)
 */
class UserConsentItemTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_consent_user_consent_item';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'USER_CONSENT_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'VALUE' => [
				'data_type' => 'text',
				'required' => true,
			],
			(new Reference(
				'USER_CONSENT',
				ConsentTable::class,
				Join::on('this.USER_CONSENT_ID', 'ref.ID')
			))
		];
	}

	/**
	 * Set user consent items.
	 *
	 * @param integer $userConsentId User Consent Id.
	 * @param array $items Items.
	 * @throws \Exception
	 */
	public static function addItems(int $userConsentId, array $items): void
	{
		foreach ($items as $item)
		{
			static::add([
				'USER_CONSENT_ID' => $userConsentId,
				'VALUE' => $item['VALUE'],
			]);
		}
	}

	/**
	 * Get user consent items.
	 *
	 * @param integer $userConsentId User Consent Id.
	 * @return array
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getItems(int $userConsentId): array
	{
		$items = [];

		$queryObject = static::getList([
			'filter' => [
				'=USER_CONSENT_ID' => $userConsentId
			]
		]);
		while ($item = $queryObject->fetch())
		{
			$items[] = $item;
		}

		return $items;
	}

	/**
	 * Remove user consent items.
	 *
	 * @param int $userConsentId User Consent Id.
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function removeItems(int $userConsentId): void
	{
		$queryObject = static::getList([
			'select' => ['ID'],
			'filter' => [
				'=USER_CONSENT_ID' => $userConsentId
			]
		]);
		while ($item = $queryObject->fetch())
		{
			static::delete($item['ID']);
		}
	}
}
