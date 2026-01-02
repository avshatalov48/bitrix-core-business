<?php

namespace Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Configurator;

use Bitrix\UI\AccessRights\V2\Options\RightSection;

interface RightSectionConfigurator
{
	public function configureRightSection(RightSection $rightSection): void;
}
