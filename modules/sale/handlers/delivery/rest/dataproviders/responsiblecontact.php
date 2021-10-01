<?php

namespace Sale\Handlers\Delivery\Rest\DataProviders;

use Bitrix\Sale;

/**
 * Class ResponsibleContact
 * @package Sale\Handlers\Delivery\Rest\DataProviders
 * @internal
 */
final class ResponsibleContact
{
	/**
	 * @param Sale\Shipment $shipment
	 * @return array|null
	 */
	public static function getData(Sale\Shipment $shipment): ?array
	{
		$responsibleUserId = $shipment->getField('RESPONSIBLE_ID')
			? (int)$shipment->getField('RESPONSIBLE_ID')
			: (int)$shipment->getField('EMP_RESPONSIBLE_ID');

		if (!$responsibleUserId)
		{
			return null;
		}

		$responsibleUser = \CUser::GetList('id', 'asc', ['ID' => $responsibleUserId])->fetch();
		if (!$responsibleUser)
		{
			return null;
		}

		return [
			'NAME' => trim(
				sprintf(
					'%s %s',
					$responsibleUser['NAME'],
					$responsibleUser['LAST_NAME']
				)
			),
			'PHONES' => self::getUserPhones($responsibleUser),
		];
	}

	/**
	 * @param array $user
	 * @return array
	 */
	private static function getUserPhones(array $user): array
	{
		$result = [];

		if (isset($user['WORK_PHONE']) && !empty($user['WORK_PHONE']))
		{
			$result[] = [
				'TYPE' => 'WORK',
				'VALUE' => $user['WORK_PHONE'],
			];
		}

		if (isset($user['PERSONAL_MOBILE']) && !empty($user['PERSONAL_MOBILE']))
		{
			$result[] = [
				'TYPE' => 'MOBILE',
				'VALUE' => $user['PERSONAL_MOBILE'],
			];
		}

		if (isset($user['PERSONAL_PHONE']) && !empty($user['PERSONAL_PHONE']))
		{
			$result[] = [
				'TYPE' => 'HOME',
				'VALUE' => $user['PERSONAL_PHONE'],
			];
		}

		if (isset($user['WORK_FAX']) && !empty($user['WORK_FAX']))
		{
			$result[] = [
				'TYPE' => 'FAX',
				'VALUE' => $user['WORK_FAX'],
			];
		}

		if (isset($user['PERSONAL_FAX']) && !empty($user['PERSONAL_FAX']))
		{
			$result[] = [
				'TYPE' => 'FAX',
				'VALUE' => $user['PERSONAL_FAX'],
			];
		}

		if (isset($user['WORK_PAGER']) && !empty($user['WORK_PAGER']))
		{
			$result[] = [
				'TYPE' => 'PAGER',
				'VALUE' => $user['WORK_PAGER'],
			];
		}

		if (isset($user['PERSONAL_PAGER']) && !empty($user['PERSONAL_PAGER']))
		{
			$result[] = [
				'TYPE' => 'PAGER',
				'VALUE' => $user['PERSONAL_PAGER'],
			];
		}

		return $result;
	}
}
