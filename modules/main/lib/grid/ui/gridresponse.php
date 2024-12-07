<?php

namespace Bitrix\Main\Grid\UI;

use Bitrix\Main\Application;
use Bitrix\Main\Engine\Response\Json;
use CMain;

/**
 * Grid's response for `bitrix:main.ui.grid` component.
 */
class GridResponse implements \Bitrix\Main\Grid\GridResponse
{
	/**
	 * @var array[]
	 */
	private array $messages = [];
	private mixed $payload = null;

	/**
	 * Add message to response.
	 *
	 * @param string $message
	 * @param string $type constant of `MessageType` class e.g. MessageType::ERROR
	 *
	 * @see \Bitrix\Main\Grid\MessageType
	 *
	 * @return void
	 */
	public function addMessage(string $message, string $type): void
	{
		$this->messages[] = [
			'TYPE' => $type,
			'TEXT' => $message,
		];
	}

	/**
	 * Add payload to response.
	 *
	 * @param mixed $payload
	 *
	 * @return void
	 */
	public function setPayload(mixed $payload): void
	{
		$this->payload = $payload;
	}

	/**
	 * @inheritDoc
	 *
	 * @return bool
	 */
	public function isSendable(): bool
	{
		return !(
			empty($this->messages) && empty($this->payload)
		);
	}

	/**
	 * Send response with messages.
	 *
	 * @return never
	 */
	public function send(): void
	{
		global $APPLICATION;

		/**
		 * @var CMain $APPLICATION
		 */

		$APPLICATION->RestartBuffer();

		// TODO: fix JS code for correct work without messages
		$responseData = [
			'messages' => $this->messages,
		];
		if (!empty($this->payload))
		{
			$responseData['payload'] = $this->payload;
		}

		$response = new Json($responseData);

		Application::getInstance()->end(200, $response);
	}
}
