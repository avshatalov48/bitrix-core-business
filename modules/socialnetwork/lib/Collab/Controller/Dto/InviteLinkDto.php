<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Controller\Dto;

use Bitrix\Main\Validation\Rule\PositiveNumber;

class InviteLinkDto extends AbstractBaseDto
{
	public function __construct(
		#[PositiveNumber]
		public readonly ?int $collabId = null,
	)
	{
	}
}
