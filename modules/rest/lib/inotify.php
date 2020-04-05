<?php
namespace Bitrix\Rest;

interface INotify
{
	public function send($clientId, $userId, $token, $method, $message);
}