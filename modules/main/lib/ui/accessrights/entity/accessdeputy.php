<?php


namespace Bitrix\Main\UI\AccessRights\Entity;


use Bitrix\Main\Localization\Loc;

class AccessDeputy extends AccessDirector
{
	public function getName(): string
	{
		return Loc::getMessage('MAIN_UI_SELECTOR_ACCESSRIGHT_DEPUTY');
	}
}