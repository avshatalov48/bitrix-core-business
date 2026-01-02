<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\Chat;

interface ICopilotChatBot
{
	/**
	 * @param ChatBotMessageDto $message
	 * @return void
	 */
	public function sendErrorMessage(ChatBotMessageDto $message): void;

	/**
	 * Call when quota check is completed successfully
	 * @param ChatBotMessageDto $message
	 * @return void
	 */
	public function onRequestQuotaOk(ChatBotMessageDto $message): void;
}