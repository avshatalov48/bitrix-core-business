<?php

namespace Bitrix\Rest;

class AppForm
{
	public function __construct(
		private string $config
	)
	{}

	public function sendShowMessage(MessageTransportInterface $transport): bool
	{
		return $transport->send('showForm', ['config' => $this->config]);
	}
}