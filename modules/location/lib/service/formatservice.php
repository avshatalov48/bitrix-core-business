<?php

namespace Bitrix\Location\Service;

use Bitrix\Location\Common\RepositoryTrait;
use \Bitrix\Location\Entity;
use Bitrix\Location\Exception\RuntimeException;
use Bitrix\Location\Common\BaseService;
use Bitrix\Location\Infrastructure\Service\Config\Container;
use Bitrix\Location\Repository\FormatRepository;

/**
 * Class FormatService
 *
 * Service to work with addresses formats
 *
 * @package Bitrix\Location\Service
 */
final class FormatService extends BaseService
{
	use RepositoryTrait;

	/** @var FormatService */
	protected static $instance;

	/** @var string  */
	private $defaultFormatCode;

	/** @var FormatRepository */
	protected $repository;

	/**
	 * Find format by format code
	 *
	 * @param string $formatCode
	 * @param string $languageId
	 * @return Entity\Format|null|bool
	 */
	public function findByCode(string $formatCode, string $languageId)
	{
		$result = false;

		try
		{
			$result = $this->repository->findByCode($formatCode, $languageId);
		}
		catch (RuntimeException $exception)
		{
			$this->processException($exception);
		}

		return $result;
	}

	/**
	 * Find all existing formats
	 *
	 * @param string $languageId
	 * @return array|bool
	 */
	public function findAll(string $languageId)
	{
		$result = false;

		try
		{
			$result = $this->repository->findAll($languageId);
		}
		catch (RuntimeException $exception)
		{
			$this->processException($exception);
		}

		return $result;
	}

	/**
	 * Find default (current) format
	 *
	 * @param string $languageId
	 * @return Entity\Format|bool|null
	 */
	public function findDefault(string $languageId)
	{
		$result = false;

		try
		{
			$result = $this->repository
				->findByCode($this->defaultFormatCode, $languageId);

		}
		catch (RuntimeException $exception)
		{
			$this->processException($exception);
		}

		return $result;
	}

	/**
	 * Returns default (current) format code
	 *
	 * @return string
	 */
	public function getDefaultFormatCode(): string
	{
		return $this->defaultFormatCode;
	}

	/**
	 * @param string $defaultFormatCode
	 * @return FormatService
	 */
	public function setDefaultFormatCode($defaultFormatCode)
	{
		$this->defaultFormatCode = $defaultFormatCode;
		return $this;
	}

	/**
	 * FormatService constructor.
	 * @param Container $config
	 */
	protected function __construct(Container $config)
	{
		parent::__construct($config);
		$this->defaultFormatCode = $config->get('defaultFormatCode');
		$this->setRepository($config->get('repository'));
	}
}
