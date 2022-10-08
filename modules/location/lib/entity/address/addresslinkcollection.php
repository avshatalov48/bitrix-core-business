<?php

namespace Bitrix\Location\Entity\Address;

use Bitrix\Main\ArgumentTypeException;

/**
 * Class AddressLinkCollection
 * @package Bitrix\Location\Entity\Address
 * @internal
 */
final class AddressLinkCollection extends \Bitrix\Location\Entity\Generic\Collection
{
	/** @var IAddressLink[] */
	protected $items = [];

	/**
	 * @param IAddressLink $linkedEntity
	 * @return int
	 * @throws ArgumentTypeException
	 */
	public function addItem($linkedEntity): int
	{
		if(!($linkedEntity instanceof IAddressLink))
		{
			throw new ArgumentTypeException('linkedEntity must implement IAddressLink');
		}

		return parent::addItem($linkedEntity);
	}
}