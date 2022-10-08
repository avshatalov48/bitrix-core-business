<?php

namespace Bitrix\Location\Source;

use Bitrix\Location\Entity\Source\Config;

interface IConfigFactory
{
	public function createConfig(): Config;
}