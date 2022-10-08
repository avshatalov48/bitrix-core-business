<?php
namespace Bitrix\Location\Entity\Address;

use Bitrix\Main\ArgumentNullException;

/**
 * The default implementation of IAddressLink
 *
 * Class AddressLink
 * @package Bitrix\Location\Entity\Address
 */
final class AddressLink implements IAddressLink
{
	/** @var string */
	private $entityId;

	/** @var string  */
	private $entityType;

	/**
	 * AddressLink constructor.
	 * @param string $entityId
	 * @param string $entityType
	 * @throws ArgumentNullException
	 */
	public function __construct(string $entityId, string $entityType)
	{
		if($entityId === '')
		{
			throw new ArgumentNullException('entityId');
		}

		if($entityType === '')
		{
			throw new ArgumentNullException('entityType');
		}

		$this->entityId = $entityId;
		$this->entityType = $entityType;
	}

	/**
	 * @inheritDoc
	 */
	public function getAddressLinkEntityId(): string
	{
		return $this->entityId;
	}

	/**
	 * @inheritDoc
	 */
	public function getAddressLinkEntityType(): string
	{
		return $this->entityType;
	}
}