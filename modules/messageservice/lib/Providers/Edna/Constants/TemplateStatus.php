<?php

namespace Bitrix\MessageService\Providers\Edna\Constants;

/** @see https://docs.edna.ru/kb/message-matchers-get-by-request/ */
final class TemplateStatus
{
	public const APPROVED = 'APPROVED';
	public const REJECTED = 'REJECTED';
	public const PENDING = 'PENDING';
	public const NOT_SENT = 'NOT_SENT';
	public const ARCHIVED =	'ARCHIVED';
	public const PAUSED = 'PAUSED';
	public const DISABLED = 'DISABLED';
}