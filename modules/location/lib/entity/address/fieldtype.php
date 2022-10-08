<?php

namespace Bitrix\Location\Entity\Address;

use Bitrix\Location\Entity\Location;

/**
 * Address Fields types
 *
 * Class FieldType
 * @package Bitrix\Location\Entity\Address
 */
final class FieldType extends Location\Type
{
	public const POSTAL_CODE = 50;

	public const ADDRESS_LINE_2 = 600;
	public const RECIPIENT_COMPANY = 700;
	public const RECIPIENT = 710;
	public const PO_BOX = 800;
}