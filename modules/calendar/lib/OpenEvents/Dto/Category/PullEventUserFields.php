<?php

namespace Bitrix\Calendar\OpenEvents\Dto\Category;

use Bitrix\Main\Type\Contract\Arrayable;

final class PullEventUserFields implements Arrayable
{
	public function __construct(
		public readonly bool $isAttendee
	)
	{
	}

	public function toArray(): array
	{
		return [
			'isAttendee' => $this->isAttendee,
		];
	}
}
