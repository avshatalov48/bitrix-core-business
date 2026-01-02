<?php

namespace Bitrix\Im\V2\Notification\Group;

use Bitrix\Im\V2\Notification\Group\Condition\Conditions;

class GroupItem
{
	public function __construct(
		public readonly string $tag,
		public readonly string $title,
		public readonly Conditions $conditions,
		public readonly bool $isEditable = false,
	) {}

	public function toRestFormat(): array
	{
		return [
			'tag' => $this->tag,
			'title' => $this->title,
			'conditions' => $this->conditions->toRestFormat(),
			'isEditable' => $this->isEditable,
		];
	}
}