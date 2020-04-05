<?php
namespace Bitrix\Main\UserField;

/**
 * Bitrix vars
 * @global \CUserTypeManager $USER_FIELD_MANAGER
 */

class DisplayEdit
	extends Display
	implements IDisplay
{
	public function display()
	{
		global $USER_FIELD_MANAGER;

		return $USER_FIELD_MANAGER->GetPublicEdit(
			$this->getField(),
			$this->getAdditionalParameters()
		);
	}
}