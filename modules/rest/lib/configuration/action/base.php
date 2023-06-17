<?php

namespace Bitrix\Rest\Configuration\Action;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\IO\File;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\Configuration\Notification;
use Bitrix\Rest\Configuration\Structure;
use Bitrix\Rest\Configuration\Helper;
use Bitrix\Rest\Configuration\Setting;
use CRestUtil;
use CTimeZone;
use CFile;
use CAgent;

abstract class Base
{
	public const CONTEXT_PREFIX = 'configuration_action';
	public const ACTION = 'base';
	public const ERROR_PROCESS_NOT_FOUND = 'PROCESS_NOT_FOUND';
	public const ERROR_PROCESS_DID_NOT_CREATE = 'PROCESS_DID_NOT_CREATE';

	private const SETTING_ACTION_STATUS_CODE = 'ACTION_STATUS_CODE';
	public const STATUS_START = 'S';
	public const STATUS_PROCESS = 'P';
	public const STATUS_FINISH = 'F';
	public const STATUS_ERROR = 'E';
	public const STATUS_UNKNOWN = 'U';
	public const STATUSES = [
		self::STATUS_START,
		self::STATUS_PROCESS,
		self::STATUS_FINISH,
		self::STATUS_ERROR,
		self::STATUS_UNKNOWN,
	];


	public const PROPERTY_STRUCTURE = 'STRUCTURE';
	public const PROPERTY_FILES = 'FILES';
	public const PROPERTY_MANIFEST = 'MANIFEST';

	protected const MODULE_ID = 'rest';
	protected $processId = 0;
	protected $setting = null;
	protected $notification = null;
	private $context = '';
	private $contextEntity = '';

	abstract protected function run(): bool;
	abstract protected function checkRegister($data): array;

	/**
	 * Base constructor.
	 *
	 * @param int $processId
	 */
	public function __construct($processId = 0)
	{
		$this->setProcessId((int) $processId);
		$this->init();
	}

	protected function init()
	{
		$app = $this->getSetting()->get(Setting::SETTING_APP_INFO);
		$this->setContextEntity($app['ID'] ?? 0);
	}

	protected function reset()
	{
		$this->setting = null;
		$this->notification = null;
	}

	/**
	 * Sets process id
	 * @param int $processId
	 *
	 * @return bool
	 */
	public function setProcessId(int $processId): bool
	{
		if ($processId !== $this->processId)
		{
			$this->processId = $processId;
			$this->reset();
			$this->init();

			return true;
		}

		return false;
	}

	/**
	 * Returns current working user context
	 *
	 * @return string
	 */
	public function getContext(): string
	{
		return !empty($this->context) ? $this->context : static::CONTEXT_PREFIX . static::ACTION . $this->processId;
	}

	/**
	 * Set custom working user context
	 * @param string $context
	 *
	 * @return bool
	 */
	public function setContext($context = ''): bool
	{
		if ($context !== $this->context)
		{
			$this->context = (string) $context;
			$this->reset();
			$this->init();

			return true;
		}

		return false;
	}

	/**
	 * Returns working context
	 * @return string
	 */
	public function getContextEntity(): string
	{
		return !empty($this->contextEntity) ? $this->contextEntity : static::CONTEXT_PREFIX . static::ACTION . $this->processId;
	}

	/**
	 * Sets working context
	 * @param int $appId
	 *
	 * @return bool
	 */
	public function setContextEntity($appId = 0): bool
	{
		$id = $appId > 0 ? (int) $appId : 0;
		$this->contextEntity = Helper::getInstance()->getContextAction($id);

		return true;
	}

	/**
	 * Returns instance of Setting with current context
	 *
	 * @return Setting
	 */
	public function getSetting(): Setting
	{
		if (!$this->setting)
		{
			$this->setting = new Setting($this->getContext());
		}

		return $this->setting;
	}

	/**
	 * @return array|mixed
	 */
	protected function getStructureData()
	{
		$result = [];

		$data = $this->getSetting()->get(Structure::CODE_CUSTOM_FILE . static::ACTION);
		if ($data['ID'] > 0)
		{
			$path = CFile::GetPath($data['ID']);
			if ($path)
			{
				if (mb_strpos($path, 'https://') === false)
				{
					$path = $_SERVER['DOCUMENT_ROOT'] . $path;
					$fileContent = File::getFileContents($path);
				}
				else
				{
					$httpClient = new HttpClient();
					$httpClient->get($path);
					$fileContent = $httpClient->getResult();
				}

				try
				{
					$result = Json::decode($fileContent);
				}
				catch (ArgumentException $e)
				{
				}
			}
		}

		return $result;
	}

	/**
	 * Registers process
	 *
	 * @param array $data
	 * @param array $additionalOptions
	 * @param int $userId
	 * @param string $appCode
	 * @param bool $byAgent
	 *
	 * @return array
	 * @throws ArgumentException
	 */
	public function register(array $data, array $additionalOptions = [], int $userId = 0, string $appCode = '', bool $byAgent = true): array
	{
		$result = static::checkRegister($data);
		if (!empty($result['error']))
		{
			return $result;
		}

		$data = $this->prepareData($data);
		$file = CRestUtil::saveFile(base64_encode(Json::encode($data)));
		$file['MODULE_ID'] = static::MODULE_ID;
		$processId = CFile::SaveFile(
			$file,
			'configuration/' . static::ACTION
		);

		if ($processId > 0)
		{
			$this->setProcessId($processId);
			$isSave = $this->getSetting()->set(
				Structure::CODE_CUSTOM_FILE . static::ACTION,
				[
					'ID' => $processId,
				]
			);
			if ($isSave)
			{
				$this->getSetting()->set(
					Setting::SETTING_ACTION_ADDITIONAL_OPTION,
					$additionalOptions
				);

				$this->getSetting()->set(
					Setting::SETTING_USER_ID,
					$userId
				);

				if (!empty($appCode))
				{
					$app = AppTable::getByClientId($appCode);
					if (is_array($app))
					{
						$this->getSetting()->set(
							Setting::SETTING_APP_INFO,
							$app
						);
					}
				}

				$this->setStatus(self::STATUS_START);
				$result['processId'] = $processId;
				if ($byAgent)
				{
					CAgent::AddAgent(
						static::class . '::runAgent(' . $processId . ');',
						static::MODULE_ID,
						'N',
						60,
						'',
						'Y',
						ConvertTimeStamp(time() + CTimeZone::GetOffset() + 60, 'FULL')
					);
				}
			}
			else
			{
				$this->setStatus(self::STATUS_ERROR);
			}
		}

		if (!$result['processId'])
		{
			$result['error'] = static::ERROR_PROCESS_DID_NOT_CREATE;
		}

		return $result;
	}

	/**
	 * Prepares data before saving.
	 * Sorted list of files important for some cases.
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	protected function prepareData($data)
	{
		if (isset($data[self::PROPERTY_STRUCTURE]) && is_array($data[self::PROPERTY_STRUCTURE]))
		{
			foreach ($data[self::PROPERTY_STRUCTURE] as $type => $item)
			{
				if ($type === Helper::STRUCTURE_FILES_NAME)
				{
					continue;
				}
				if (is_array($data[self::PROPERTY_STRUCTURE][$type]))
				{
					$list = [];
					foreach ($data[self::PROPERTY_STRUCTURE][$type] as $value)
					{
						if (is_string($value))
						{
							$path = array_filter(explode('/', $value));
							$list[end($path)] =  $value;
						}
					}
					ksort($list);
					$data[self::PROPERTY_STRUCTURE][$type] = array_values($list);
				}
			}
		}

		return $data;
	}

	/**
	 * Unregister process
	 * @return string[]
	 */
	public function unregister(): array
	{
		$result = [
			'error' => static::ERROR_PROCESS_NOT_FOUND,
			'error_description' => 'Process doesn\'t found.',
		];
		if ($this->processId > 0)
		{
			$data = $this->getSetting()->get(
				Structure::CODE_CUSTOM_FILE . static::ACTION
			);

			if ($data)
			{
				$result = [
					'success' => 'Y',
				];
				$this->getSetting()->delete(
					Structure::CODE_CUSTOM_FILE . static::ACTION
				);
				$res = CAgent::getList(
					[],
					[
						'MODULE_ID' => static::MODULE_ID,
						'NAME' => static::class . '::runAgent(' . $data['ID'] . ');',
					]
				);
				while ($agent = $res->fetch())
				{
					CAgent::Delete($agent['ID']);
				}
			}
		}

		return $result;
	}

	/**
	 * Returns instance to work with notification
	 *
	 * @return Notification
	 */
	public function getNotificationInstance(): Notification
	{
		if (!$this->notification)
		{
			$this->notification = new Notification($this->getSetting());
		}

		return $this->notification;
	}

	/**
	 * @param string $status
	 *
	 * @return bool
	 */
	protected function setStatus(string $status)
	{
		if (!in_array($status, self::STATUSES))
		{
			return false;
		}

		return $this->getSetting()->set(self::SETTING_ACTION_STATUS_CODE, $status);
	}

	/**
	 * Returns actions status
	 *
	 * @return array|mixed|string
	 */
	public function getStatus()
	{
		return $this->getSetting()->get(self::SETTING_ACTION_STATUS_CODE) ?? self::STATUS_UNKNOWN;
	}

	/**
	 * Returns information about current action
	 * @return array
	 */
	public function get(): array
	{
		$result = [
			'status' => $this->getStatus(),
		];
		$notification = [
			'notice' => $this->getNotificationInstance()->list(
				[
					'type' => Notification::TYPE_NOTICE,
				]
			),
			'errors' => $this->getNotificationInstance()->list(
				[
					'type' => Notification::TYPE_ERROR,
				]
			),
			'exception' => $this->getNotificationInstance()->list(
				[
					'type' => Notification::TYPE_EXCEPTION,
				]
			),
		];

		return array_merge($result, array_filter($notification));
	}

	/**
	 * @param int $processId
	 *
	 * @return string
	 */
	public static function runAgent(int $processId): string
	{
		$action = new static($processId);
		$result = $action->run();

		if (!$result)
		{
			$action->unregister();
		}

		return $result ? static::class . '::runAgent(' . $processId . ');' : '';
	}

}
