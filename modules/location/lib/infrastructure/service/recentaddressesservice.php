<?php

namespace Bitrix\Location\Infrastructure\Service;

use Bitrix\Location\Common\BaseService;
use Bitrix\Location\Entity\Address;
use Bitrix\Location\Infrastructure\Service\Config\Container;
use Bitrix\Location\Model\RecentAddressTable;
use Bitrix\Main\Application;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Type\DateTime;

class RecentAddressesService extends BaseService
{
	/** @var RecentAddressesService */
	protected static $instance;

	private const MAX_CNT = 20;

	private int $currentUserId;

	protected function __construct(Container $config)
	{
		parent::__construct($config);

		$this->currentUserId = (int)CurrentUser::get()?->getId();
	}

	public function add(Address $address): void
	{
		$normalizedAddress = $this->getNormalizedAddress($address);

		$recentAddressList = RecentAddressTable::query()
			->setSelect(['ID', 'ADDRESS'])
			->where('USER_ID', $this->currentUserId)
			->setOrder(['USED_AT' => 'DESC'])
			->setLimit(self::MAX_CNT)
			->fetchAll()
		;

		$isExisting = false;
		foreach ($recentAddressList as $recentAddressListItem)
		{
			$recentAddress = null;
			try
			{
				$recentAddress = Address::fromJson($recentAddressListItem['ADDRESS']);
			}
			catch (\Exception $e) {}

			if ($recentAddress === null)
			{
				continue;
			}

			if ($this->areAddressesEqual($normalizedAddress, $recentAddress))
			{
				RecentAddressTable::update(
					(int)$recentAddressListItem['ID'],
					[
						'ADDRESS' => $normalizedAddress->toJson(),
						'USED_AT' => new DateTime(),
					]
				);
				$isExisting = true;

				break;
			}
		}

		if (!$isExisting)
		{
			RecentAddressTable::add([
				'USER_ID' => $this->currentUserId,
				'ADDRESS' => $normalizedAddress->toJson(),
			]);
		}
	}

	public function get(int $limit = self::MAX_CNT): array
	{
		$result = [];

		$recentAddressList = RecentAddressTable::query()
			->setSelect(['ADDRESS'])
			->where('USER_ID', $this->currentUserId)
			->setLimit(min($limit, self::MAX_CNT))
			->setOrder(['USED_AT' => 'DESC'])
			->fetchAll()
		;
		foreach ($recentAddressList as $recentAddressListItem)
		{
			$recentAddressJson = $recentAddressListItem['ADDRESS'];

			$address = Address::fromJson($recentAddressJson);
			if (!$address)
			{
				continue;
			}

			$result[] = $address;
		}

		return $result;
	}

	public static function cleanUp(): string
	{
		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$connection->query('
			DELETE FROM b_location_recent_address
			WHERE
				USED_AT < ' . $helper->addDaysToDateTime(-30) . '
		');

		return '\\Bitrix\\Location\\Infrastructure\\Service\\RecentAddressesService::cleanUp();';
	}

	private function areAddressesEqual(Address $address1, Address $address2): bool
	{
		return $this->getAddressFields($address1) === $this->getAddressFields($address2);
	}

	private function getAddressFields(Address $address): array
	{
		$result = [];

		/** @var Address\Field $field */
		foreach ($address->getFieldCollection() as $field)
		{
			$result[$field->getType()] = $field->getValue();
		}

		ksort($result);

		return $result;
	}

	private function getNormalizedAddress(Address $address): Address
	{
		$result = new Address(
			$address->getLanguageId()
		);
		$fieldCollection = new Address\FieldCollection();

		$result->setLatitude($address->getLatitude());
		$result->setLongitude($address->getLongitude());

		/** @var Address\Field $field */
		foreach ($address->getFieldCollection() as $field)
		{
			$fieldCollection->addItem(
				new Address\Field($field->getType(), $field->getValue())
			);
		}

		$result->setFieldCollection($fieldCollection);

		return $result;
	}
}
