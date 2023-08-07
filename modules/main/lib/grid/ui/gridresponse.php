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
	 * @inheritDoc
	 *
	 * @return bool
	 */
	public function isSendable(): bool
	{
		return ! empty($this->messages);
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

		$response = new Json([
			'messages' => $this->messages,
		]);

		Application::getInstance()->end(200, $response);
	}
}
