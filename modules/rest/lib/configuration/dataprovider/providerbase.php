<?php

namespace Bitrix\Rest\Configuration\DataProvider;

use Bitrix\Main\Web\Json;
use Bitrix\Rest\Configuration\Helper;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;

/**
 * Class ProviderBase
 * @package Bitrix\Rest\Configuration\DataProvider
 */
abstract class ProviderBase
{
	public const ERROR_DECODE_DATA = 'ERROR_DECODE_DATA';
	public const ERROR_DATA_NOT_FOUND = 'ERROR_DATA_NOT_FOUND';
	private $errorList = [];
	private $contextUser;
	private $context;

	/**
	 * ProviderBase constructor.
	 *
	 * @param array $setting
	 */
	public function __construct(array $setting)
	{
		if (!empty($setting['CONTEXT']))
		{
			$this->context = $setting['CONTEXT'];
		}

		if (!empty($setting['CONTEXT_USER']))
		{
			$this->contextUser = $setting['CONTEXT_USER'];
		}
	}

	/**
	 * Returns action context
	 *
	 * @return mixed|string
	 */
	protected function getContext()
	{
		return $this->context ?? Helper::getInstance()->getContextAction();
	}

	/**
	 * Return users action context
	 *
	 * @return mixed|string
	 */
	protected function getUserContext()
	{
		return $this->contextUser ?? Helper::getInstance()->getContextUser('');
	}

	/**
	 * Adds content as file to configuration folder.
	 *
	 * @param $code string name of file
	 * @param $content string|array saving configuration data
	 * @param $type mixed type of configuration data
	 *
	 * @return bool
	 */
	abstract public function addContent(string $code, $content, $type = false): bool;

	/**
	 * Adds files to configurations files folder.
	 * @param $files array files list
	 *
	 * @return array
	 */
	abstract public function addFiles(array $files): array;

	/**
	 * Returns content from file
	 *
	 * @param string $path
	 * @param int $step
	 *
	 * @return array|null
	 */
	abstract public function get(string $path, int $step): ?array;

	/**
	 * Returns structured content for working
	 * @param $path
	 *
	 * @return array
	 */
	public function getContent(string $path, int $step): array
	{
		$result = [
			'DATA' => null,
			'~DATA' => null,
			'FILE_NAME' => null,
			'SANITIZE' => false,
			'COUNT' => 0,
		];

		try
		{
			$content = $this->get($path, $step);
			if (!is_null($content['DATA']))
			{
				$result['~DATA'] = Json::decode($content['DATA']);
				if (!empty($result['~DATA']))
				{
					$result['DATA'] = Helper::getInstance()->sanitize($result['~DATA'], $result['SANITIZE']);
				}
			}
			else
			{
				$result['ERROR_CODE'] = self::ERROR_DATA_NOT_FOUND;
			}

			if ($content['FILE_NAME'])
			{
				$result['FILE_NAME'] = preg_replace(
					'/(' . Helper::CONFIGURATION_FILE_EXTENSION . ')$/i',
					'',
					$content['FILE_NAME']
				);
			}

			if (!empty($content['COUNT']) && (int)$content['COUNT'] > 0)
			{
				$result['COUNT'] = (int)$content['COUNT'];
			}
		}
		catch (ArgumentException $exception)
		{
			$result['ERROR_CODE'] = self::ERROR_DECODE_DATA;
		}
		catch (SystemException $exception)
		{
			$result['ERROR_CODE'] = self::ERROR_DATA_NOT_FOUND;
		}

		return $result;
	}

	/**
	 * Makes string from content data.
	 *
	 * @param $content
	 * @return false|string
	 */
	protected function packageContent($content): ?string
	{
		$result = null;
		if (is_array($content))
		{
			try
			{
				$result = Json::encode($content);
			}
			catch (ArgumentException $e)
			{
				$result = null;
				$this->addError('ERROR_JSON_ENCODE', '');

			}
		}
		elseif (is_string($content))
		{
			$result = $content;
		}

		return $result;
	}

	/**
	 * Adds error.
	 *
	 * @param $code
	 * @param $message
	 *
	 * @return bool
	 */
	protected function addError($code, $message): bool
	{
		$this->errorList[$code] = $message;

		return true;
	}

	/**
	 * Returns error list.
	 *
	 * @return array
	 */
	public function listError(): array
	{
		return $this->errorList;
	}

	/**
	 * Resets errors.
	 *
	 * @return bool
	 */
	public function resetErrors(): bool
	{
		$this->errorList = [];

		return true;
	}
}
