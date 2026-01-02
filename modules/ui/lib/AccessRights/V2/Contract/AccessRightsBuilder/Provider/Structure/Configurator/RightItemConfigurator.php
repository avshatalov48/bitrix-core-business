<?php

namespace Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Configurator;

use Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem;

interface RightItemConfigurator
{
	public function configureRightItem(RightItem $rightItem): void;
}
