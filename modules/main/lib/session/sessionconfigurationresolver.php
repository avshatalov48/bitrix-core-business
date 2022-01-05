<?php

namespace Bitrix\Main\Session;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\Session\Handlers\AbstractSessionHandler;
use Bitrix\Main\Session\Handlers\StrictSessionHandler;

final class SessionConfigurationResolver
{
	private const MODE_DEFAULT   = 'default';
	private const MODE_SEPARATED = 'separated';

	public const TYPE_FILE       = 'file';
	public const TYPE_DATABASE   = 'database';
	public const TYPE_REDIS      = 'redis';
	public const TYPE_MEMCACHE   = 'memcache';
	public const TYPE_ARRAY      = 'array';
	public const TYPE_NULL       = 'null';
	public const TYPE_CUSTOM_INI = 'save_handler.php.ini';

	/** @var Configuration */
	private $configuration;
	/** @var Session */
	private $session;
	/** @var KernelSession */
	private $kernelSession;

	public function __construct(Configuration $configuration)
	{
		$this->configuration = $configuration;
	}

	public function resolve(): void
	{
		$sessionConfig = $this->getSessionConfig();
		$generalOptions = $sessionConfig['handlers']['general'];
		$generalHandler = $this->buildSessionHandlerByOptions($generalOptions['type'], $generalOptions);

		if ($sessionConfig['mode'] === self::MODE_DEFAULT)
		{
			$this->session = new Session($generalHandler);
			$this->kernelSession = new KernelSessionProxy($this->session);
		}
		elseif ($sessionConfig['mode'] === self::MODE_SEPARATED)
		{
			if (empty($sessionConfig['handlers']['kernel']))
			{
				throw new NotSupportedException('There is no handler for kernel session');
			}
			if (empty($sessionConfig['handlers']['general']))
			{
				throw new NotSupportedException('There is no handler for general session');
			}
			if ($sessionConfig['handlers']['kernel'] !== 'encrypted_cookies')
			{
				throw new NotSupportedException('Invalid settings. Kernel session works only with "encrypted_cookies"');
			}

			$this->session = new Session($generalHandler);
			$this->kernelSession = new KernelSession($sessionConfig['lifetime'] ?? 0);
			Legacy\LazySessionStart::register();
			$this->session->enableLazyStart();
			if (!empty($sessionConfig['debug']))
			{
				$this->session->enableDebug();

				$debugger = $this->session->getDebugger();
				$debugger->setMode($sessionConfig['debug']);
				$debugger->storeConfig($sessionConfig);
			}
		}

		if (isset($sessionConfig['ignoreSessionStartErrors']) && $sessionConfig['ignoreSessionStartErrors'] === true)
		{
			$this->session->enableIgnoringSessionStartErrors();
		}
	}

	public function getSessionConfig(): array
	{
		if (defined("BX_SECURITY_SESSION_VIRTUAL") && BX_SECURITY_SESSION_VIRTUAL === true)
		{
			return [
				'mode' => self::MODE_DEFAULT,
				'handlers' => [
					'general' => ['type' => self::TYPE_ARRAY]
				]
			];
		}

		$sessionConfig = $this->configuration::getValue("session") ?: [];
		$saveHandlerFromIni = ini_get('session.save_handler');
		if ((!$sessionConfig || $sessionConfig === ['mode' => self::MODE_DEFAULT]) && $saveHandlerFromIni !== 'files')
		{
			//when some specific save_handler was installed and we can't change behavior.
			return [
				'mode' => self::MODE_DEFAULT,
				'handlers' => [
					'general' => ['type' => self::TYPE_CUSTOM_INI]
				]
			];
		}

		$sessionConfig['mode'] = $sessionConfig['mode'] ?? self::MODE_DEFAULT;
		$sessionConfig['handlers']['general'] = $this->normalizeSessionOptions($sessionConfig['handlers']['general'] ?? ['type' => self::TYPE_FILE]);

		if (defined("BX_FORCE_DISABLE_SEPARATED_SESSION_MODE") && BX_FORCE_DISABLE_SEPARATED_SESSION_MODE === true)
		{
			$sessionConfig['mode'] = self::MODE_DEFAULT;
		}

		return $sessionConfig;
	}

	private function buildSessionHandlerByOptions(string $type, array $options = []): ?AbstractSessionHandler
	{
		switch ($type)
		{
			case self::TYPE_FILE:
				return new StrictSessionHandler(new Handlers\NativeFileSessionHandler($options));
			case self::TYPE_DATABASE:
				return new Handlers\DatabaseSessionHandler($options);
			case self::TYPE_REDIS:
				return new Handlers\RedisSessionHandler($options);
			case self::TYPE_MEMCACHE:
				return new Handlers\MemcacheSessionHandler($options);
			case self::TYPE_ARRAY:
			case self::TYPE_NULL:
				return new Handlers\NullSessionHandler();
			case self::TYPE_CUSTOM_INI:
				return null;
		}

		throw new NotSupportedException("Unknown session handler {{$type}}");
	}

	private function normalizeSessionOptions($options): array
	{
		if (!is_array($options) && is_string($options))
		{
			$options = [
				'type' => $options,
			];
		}
		if (is_array($options))
		{
			if (defined('BX_SECURITY_SESSION_READONLY') && BX_SECURITY_SESSION_READONLY)
			{
				$options['readOnly'] = true;
			}

			return $options;
		}

		throw new ArgumentException('Session handler has to have options as array or string.');
	}

	public function getSession(): SessionInterface
	{
		return $this->session;
	}

	public function getKernelSession(): SessionInterface
	{
		return $this->kernelSession;
	}

	public function getConfiguration(): Configuration
	{
		return $this->configuration;
	}
}