<?php

namespace Bitrix\Mail;

interface IMessageAccessStorage
{
	public function getCollectionForMessage(\Bitrix\Mail\Item\Message $message): \Bitrix\Mail\Collection\MessageAccess;
	public function getCollectionByMessageId(int $messageId, int $mailboxId): \Bitrix\Mail\Collection\MessageAccess;
}