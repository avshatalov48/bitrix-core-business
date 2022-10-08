<?php
namespace Bitrix\Location\Infrastructure\Service\LoggerService;

interface ILogger
{
	public function log(int $level, string $message, int $eventType = 0, array $context = []);
}