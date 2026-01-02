<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Controller\Dto\Converter;

use Bitrix\Main\Validation\Rule\PositiveNumber;
use Bitrix\Socialnetwork\Collab\Controller\Dto\AbstractBaseDto;

class ConvertToCollabDto extends AbstractBaseDto
{
	public function __construct(
		#[PositiveNumber]
		public readonly ?int $id = null,
	)
	{

	}
}
