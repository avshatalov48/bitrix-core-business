<?php

namespace Bitrix\Pull\DTO;

use Bitrix\Pull\Common;

class Message implements \JsonSerializable
{
	public array $userList = [];
	public array $channelList = [];
	public ?array $body;
	public ?string $type;
	public array $userParams; // map: userId => {user specific params}
	public array $dictionary; // map: key => value
	public int $expiry;

	public static function fromEvent(array $arrayFields): Message
	{
		$instance = new static();
		$instance->userList = $arrayFields['users'] ?? [];
		$instance->channelList = $arrayFields['channels'] ?? [];

		$body = $arrayFields['event'];
		if (is_array($body['user_params']) && !empty($body['user_params']))
		{
			$instance->userParams = $arrayFields['event']['user_params'];
		}
		if (is_array($body['dictionary']) && !empty($body['dictionary']))
		{
			$instance->dictionary = $arrayFields['event']['dictionary'];
		}
		$instance->expiry = is_int($body['expiry']) && $body['expiry'] > 0 ? $body['expiry'] : 86400;
		// for statistics
		$messageType = "{$body['module_id']}_{$body['command']}";
		$messageType = preg_replace("/[^\w]/", "", $messageType);
		$instance->type = $messageType;

		unset($body['user_params']);
		unset($body['dictionary']);
		unset($body['expiry']);

		$instance->body = $body;

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
		if (isset($this->type))
		{
			$result['type'] = $this->type;
		}

		return $result;
	}
}