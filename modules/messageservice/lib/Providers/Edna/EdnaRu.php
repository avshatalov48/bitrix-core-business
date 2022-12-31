<?php

namespace Bitrix\MessageService\Providers\Edna;

use Bitrix\Main\Result;

interface EdnaRu
{
	public function getMessageTemplates(string $subject = ''): Result;
	public function getSentTemplateMessage(string $from, string $to): string;
	public function getChannelList(string $imType): Result;
	public function getCascadeList(): Result;
	public function setCallback(string $callbackUrl, array $callbackTypes, ?int $subjectId = null): Result;
	public function checkActiveChannelBySubjectIdList(array $subjectIdList, string $imType): bool;
	public function getActiveChannelList(string $imType): Result;
}