<?php
namespace Bitrix\Main\UserField;

/**
 * Bitrix vars
 * @global \CUserTypeManager $USER_FIELD_MANAGER
 * @deprecated
 */

class DisplayView
	extends Display
	implements IDisplay
{
	public function display()
	{
		global $USER_FIELD_MANAGER;

		return $USER_FIELD_MANAGER->GetPublicView(
			$this->getField(),
			$this->getAdditionalParameters()
		);
	}
}