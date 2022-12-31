<?php

namespace Bitrix\Pull\DTO;

use Bitrix\Pull\Common;

class Message implements \JsonSerializable
{
	public array $userList = [];
	public array $channelList = [];
	public array $body;
	public array $userParams; // map: userId => {user specific params}
	public array $dictionary; // map: key => value
	public int $expiry;

	public static function fromEvent(array $arrayFields): Message
	{
		$instance = new static();
		$instance->userList = $arrayFields['users'] ?? [];
		$instance->channelList = $arrayFields['channels'] ?? [];
		$instance->body = $arrayFields['event'];
		if (isset($instance->body['user_params']))
		{
			$instance->userParams = $arrayFields['event']['user_params'];
			unset($instance->body['user_params']);
		}
		if (isset($instance->body['dictionary']))
		{
			$instance->dictionary = $arrayFields['event']['dictionary'];
			unset($instance->body['dictionary']);
		}

		$instance->expiry = $arrayFields['event']['expiry'] ?? 86400;

		return $instance;
	}

	public function jsonSerialize(): array
	{
		$result = [];
		if (!empty($this->channelList))
		{
			$result['channelList'] = $this->channelList;
		}
		if (!empty($this->userList))
		{
			$result['userList'] = $this->userList;
		}
		if (isset($this->body))
		{
			$result['body'] = $this->body;
			Common::recursiveConvertDateToString($result['body']);
		}
		if (isset($this->userParams))
		{
			$result['user_params'] = $this->userParams;
		}
		if (isset($this->dictionary))
		{
			$result['dictionary'] = $this->dictionary;
		}
		if (isset($this->expiry))
		{
			$result['expiry'] = $this->expiry;
		}

		return $result;
	}
}