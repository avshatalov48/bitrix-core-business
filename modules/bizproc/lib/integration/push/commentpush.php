<?php

namespace Bitrix\Bizproc\Integration\Push;

final class CommentPush extends BasePush
{
	protected static function getCommand(): string
	{
		return 'comment';
	}

	public static function pushCounter(mixed $workflowId, int $userId, Dto\UserCounter $counter): void
	{
		$command = static::getCommand();

		$push = new PushWorker();
		$push->sendLast(
			"{$command}-{$workflowId}-{$userId}",
			$command,
			[
				'workflowId' => $workflowId,
				'counter' => (array)$counter
			],
			[$userId],
		);
	}
}
