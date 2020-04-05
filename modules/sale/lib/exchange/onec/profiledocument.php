<?php
namespace Bitrix\Sale\Exchange\OneC;

use Bitrix\Sale\Exchange;

/**
 * Class ProfileDocument
 * @package Bitrix\Sale\Exchange\OneC
 * @deprecated
 * For backward compatibility
 */
class ProfileDocument extends UserProfileDocument
{
    private static $FIELD_INFOS = null;

	/**
	 * @return int
	 */
	public function getOwnerEntityTypeId()
	{
		return Exchange\EntityType::PROFILE;
	}
}