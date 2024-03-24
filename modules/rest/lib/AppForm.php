<?php

namespace Bitrix\Rest;

use Bitrix\Main\Loader;

class AppForm
{
	public function __construct(
		private string $config,
		private MessageTransportInterface $transport
	)
	{}

	public function sendShowMessage(): bool
	{
		return $this->transport->send('showForm', ['config' => $this->config]);
	}
}