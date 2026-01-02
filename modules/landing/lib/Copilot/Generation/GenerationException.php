<?php

declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation;

use Bitrix\Main\SystemException;
use Bitrix\Landing\Copilot\Generation\Type\GenerationErrors;

/**
 * Exception class for errors occurring during AI generation processes.
 * Stores an error code, optional additional message, and parameters for detailed context.
 */
class GenerationException extends SystemException
{
	/**
	 * @var array Parameters for the error message template.
	 */
	private array $params;

	/**
	 * @var GenerationErrors Error code enum for this exception.
	 */
	private GenerationErrors $errorCode;

	/**
	 * GenerationException constructor.
	 *
	 * @param GenerationErrors $errorCode Error code enum describing the type of generation error.
	 * @param string $message Optional additional message for more context.
	 * @param array $params Optional parameters for the error message template.
	 */
	public function __construct(GenerationErrors $errorCode, string $message = '', array $params = [])
	{
		$this->params = $params;
		$this->errorCode = $errorCode;
		$errorMessage = $this->buildErrorMessage($errorCode, $message);

		parent::__construct($errorMessage, $errorCode->value);
	}

	/**
	 * Returns the exception code as a GenerationErrors enum instance.
	 *
	 * @return GenerationErrors The error code as an enum value.
	 */
	public function getCodeObject(): GenerationErrors
	{
		return GenerationErrors::tryFrom($this->getCode()) ?? GenerationErrors::default;
	}

	/**
	 * Returns the parameters associated with this exception.
	 *
	 * @return array Parameters for the error message template.
	 */
	public function getParams(): array
	{
		return $this->params;
	}

	/**
	 * Returns the error code enum for this exception.
	 *
	 * @return GenerationErrors The error code.
	 */
	public function getErrorCode(): GenerationErrors
	{
		return $this->errorCode;
	}

	/**
	 * Builds the final error message based on the code and an optional additional message.
	 *
	 * @param GenerationErrors $code Error code enum.
	 * @param string $additionalMessage Optional additional message for more context.
	 *
	 * @return string The complete error message.
	 */
	protected function buildErrorMessage(GenerationErrors $code, string $additionalMessage): string
	{
		$message = self::getErrorMessage($code);

		if ($additionalMessage !== '')
		{
			return $message . ': ' . $additionalMessage . '.';
		}

		return $message . '.';
	}

	/**
	 * Returns a default error message for a given error code.
	 *
	 * @param GenerationErrors $code Error code enum.
	 *
	 * @return string The default error message for the code.
	 */
	private static function getErrorMessage(GenerationErrors $code): string
	{
		$messages = [
			GenerationErrors::default->value => 'Generation error',
			GenerationErrors::notExistResponse->value => 'Response is not exist',
			GenerationErrors::restrictedRequest->value => 'Request is not allowed',
			GenerationErrors::dataValidation->value => 'Data validation error',
			GenerationErrors::notFullyResponse->value => 'Response is not complete',
			GenerationErrors::notCorrectResponse->value => 'Response is not correct',
			GenerationErrors::requestQuotaExceeded->value => 'Not enough requests. Limit type',
			GenerationErrors::notSendRequest->value => 'Request is not send',
			GenerationErrors::errorInRequest->value => 'Error in request',
		];

		return $messages[$code->value] ?? $messages[GenerationErrors::default->value];
	}
}
