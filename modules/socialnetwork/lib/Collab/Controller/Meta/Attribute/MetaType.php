<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Controller\Meta\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MetaType
{
	public function __construct(
		public readonly PropertyMetaType $type
	)
	{

	}
}