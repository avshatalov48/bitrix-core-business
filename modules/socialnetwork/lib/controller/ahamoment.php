<?php

namespace Bitrix\Socialnetwork\Controller;

class AhaMoment extends Base
{
	public function dontShowCollapseMenuAhaMomentAction(): void
	{
		\CUserOptions::SetOption('socialnetwork', 'dontShowCollapseMenuAhaMoment', 'Y');
	}
}