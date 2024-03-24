<?php

namespace Bitrix\Calendar\ICal\Builder;

interface EventFactoryInterface
{
	public const REPLY = 'reply';
	public const CANCEL = 'cancel';
	public const REQUEST = 'reply';
	public static function create(array $event, string $type): static;
}