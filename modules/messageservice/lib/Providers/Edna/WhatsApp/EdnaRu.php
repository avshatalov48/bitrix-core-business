<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp;

use Bitrix\Main\Result;

interface EdnaRu
{
	public function testConnection(): Result;
	public function getLineId(): ?int;
	public function getMessageTemplates(string $subject = ''): Result;
	public function getSentTemplateMessage(string $from, string $to): string;
	public function getChannelList(): Result;
	public function getSubjectIdBySubject(string $subject): Result;
}