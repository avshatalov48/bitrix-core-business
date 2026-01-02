<?php

namespace Bitrix\Main\UI\AccessRights\Entity;

use Bitrix\Main\Localization\Loc;

class AccessTeamDeputy extends AccessTeamDirector
{
	public function getName(): string
	{
		return Loc::getMessage('MAIN_UI_SELECTOR_ACCESSRIGHT_TEAM_DEPUTY');
	}
}
