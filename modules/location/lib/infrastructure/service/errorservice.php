<?php

namespace Bitrix\Location\Infrastructure\Service;

use Bitrix\Location\Common\BaseService;
use Bitrix\Location\Exception\ErrorCodes;
use Bitrix\Location\Infrastructure\Service\Config\Container;
use Bitrix\Location\Infrastructure\Service\LoggerService\LogLevel;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

/**
 * Class ErrorService
 * @package Bitrix\Location\Infrastructure\Service
 */
final class ErrorService extends BaseService
{
	/** @var ErrorService */
	protected static $instance;

	/** @var ErrorCollection  */
	protected $errorCollection;

	/** @var bool */
	protected $logErrors;

	/** @var bool */
	protected $throwExceptionOnError;

	/**
	 * @param Error $error
	 * @throws SystemException
	 */
	public function addError(Error $error): void
	{
		$this->errorCollection[] = $error;

		if($this->logErrors)
		{
			$this->logError($error);
		}

		if($this->throwExceptionOnError)
		{
			throw new SystemException(
				Loc::getMessage('LOCATION_ISTRUCTURE_ERRORSERVICE_ERROR'),
				ErrorCodes::ERRORSERVICE_ERROR_WAS_HAPPENED
			);
		}
	}

	/**
	 * @param Error $error
	 */
	protected function logError(Error $error): void
	{
		LoggerService::getInstance()->log(LogLevel::ERROR, $message = $error->getMessage(), $error->getCode());
	}

	/**
	 * @return ErrorCollection
	 */
	public function getErrors(): ErrorCollection
	{
		return $this->errorCollection;
	}

	public function clearErrors(): void
	{
		$this->errorCollection->clear();
	}

	/**
	 * @param bool $logErrors
	 */
	public function setLogErrors(bool $logErrors): void
	{
		$this->logErrors = $logErrors;
	}

	/**
	 * @param bool $throwExceptionOnError
	 */
	public function setThrowExceptionOnError(bool $throwExceptionOnError): void
	{
		$this->throwExceptionOnError = $throwExceptionOnError;
	}

	/**
	 * @return bool
	 */
	public function hasErrors(): bool
	{
		return $this->errorCollection->count() > 0;
	}

	/**
	 * ErrorService constructor.
	 * @param Container $config
	 */
	protected function __construct(Container $config)
	{
		parent::__construct($config);
		$this->logErrors = $config->get('logErrors');
		$this->throwExceptionOnError = $config->get('throwExceptionOnError');
		$this->errorCollection = new ErrorCollection();
	}
}