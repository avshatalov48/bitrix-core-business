<?php

namespace Bitrix\Mail;

interface IMessageStorage
{
	public function getMessage(int $id): \Bitrix\Mail\Item\Message;
}