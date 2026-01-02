<?php

namespace Bitrix\Socialnetwork\Integration\UI\EntitySelector;

class Config
{
	public static function getProjectAvatarTypes(): array
	{
		return [
			'default' => '/bitrix/js/socialnetwork/entity-selector/src/images/project.svg',
			'extranet' => '/bitrix/js/socialnetwork/entity-selector/src/images/extranet-project.svg',
			'collab' => '/bitrix/js/socialnetwork/entity-selector/src/images/collab-project.svg',
		];
	}
}
