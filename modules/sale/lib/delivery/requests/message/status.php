<?php

namespace Bitrix\Sale\Delivery\Requests\Message;

use Bitrix\Main\SystemException;

/**
 * Class Status
 * @package Bitrix\Sale\Delivery\Requests\Message
 * @internal
 */
final class Status
{
	private const SEMANTIC_SUCCESS = 'success';
	private const SEMANTIC_ERROR = 'error';
	private const SEMANTIC_PROCESS = 'process';

	/** @var string */
	private $message;

	/** @var string */
	private $semantic;

	/**
	 * Status constructor.
	 * @param string $message
	 * @param string $semantic
	 * @throws SystemException
	 */
	public function __construct(string $message, string $semantic)
	{
		$this->message = $message;
		if (!in_array($semantic, [self::SEMANTIC_SUCCESS, self::SEMANTIC_PROCESS, self::SEMANTIC_ERROR]))
		{
			throw new SystemException(sprintf('Unexpected semantic: %s', $semantic));
		}

		$this->semantic = $semantic;
	}

	/**
	 * @return string
	 */
	public function getMessage(): string
	{
		return $this->message;
	}

	/**
	 * @return string
	 */
	public function getSemantic(): string
	{
		return $this->semantic;
	}

	/**
	 * @return string
	 */
	public static function getSuccessSemantic(): string
	{
		return self::SEMANTIC_SUCCESS;
	}

	/**
	 * @return string
	 */
	public static function getErrorSemantic(): string
	{
		return self::SEMANTIC_ERROR;
	}

	/**
	 * @return string
	 */
	public static function getProcessSemantic(): string
	{
		return self::SEMANTIC_PROCESS;
	}

	/**
	 * @return string[]
	 */
	public static function getAvailableSemantics(): array
	{
		return [
			self::getSuccessSemantic(),
			self::getErrorSemantic(),
			self::getProcessSemantic(),
		];
	}
}
