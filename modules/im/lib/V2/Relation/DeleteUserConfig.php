<?php

namespace Bitrix\Im\V2\Relation;

final class DeleteUserConfig
{
	public readonly bool $withMessage;
	public readonly bool $skipRecent;
	public readonly bool $withNotification;
	public readonly bool $skipCheckReason;
	public readonly bool $withoutRead;

	public function __construct(
		bool $withMessage = true,
		bool $skipRecent = false,
		bool $withNotification = true,
		bool $skipCheckReason = false,
		bool $withoutRead = false
	)
	{
		$this->withMessage = $withMessage;
		$this->skipRecent = $skipRecent;
		$this->withNotification = $withNotification;
		$this->skipCheckReason = $skipCheckReason;
		$this->withoutRead = $withoutRead;
	}
}
