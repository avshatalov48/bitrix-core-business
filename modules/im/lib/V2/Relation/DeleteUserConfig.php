<?php

namespace Bitrix\Im\V2\Relation;

class DeleteUserConfig
{
	public function __construct(
		protected bool $withMessage = true,
		protected bool $skipRecent = false,
		protected bool $withNotification = true,
		protected bool $skipCheckReason = false,
		protected bool $withoutRead = false
	){}

	public function withNotification(): bool
	{
		return $this->withNotification;
	}

	public function skipCheckReason(): bool
	{
		return $this->skipCheckReason;
	}

	public function withMessage(): bool
	{
		return $this->withMessage;
	}

	public function skipRecent(): bool
	{
		return $this->skipRecent;
	}

	public function withoutRead(): bool
	{
		return $this->withoutRead;
	}
}
