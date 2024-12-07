<?php

namespace Bitrix\Im\V2\Message\Send;

/**
 * Config for sending process.
 *
 * @method bool generateUrlPreview()
 * @method self enableUrlPreview()
 * @method self disableUrlPreview()
 *
 * @method bool skipUserCheck()
 * @method self enableSkipUserCheck()
 * @method self disableSkipUserCheck()
 *
 * @method bool sendPush()
 * @method self allowSendPush()
 * @method self disallowSendPush()
 *
 * @method bool sendPushImmediately()
 * @method self allowPushImmediately()
 * @method self disallowPushImmediately()
 *
 * @method bool addRecent()
 * @method self enableAddRecent()
 * @method self disableAddRecent()
 *
 * @method bool skipAuthorAddRecent()
 * @method self enableSkipAuthorAddRecent()
 * @method self disableSkipAuthorAddRecent()
 *
 * @method bool convertMode()
 * @method self enableConvertMode()
 * @method self disableConvertMode()
 *
 * @method bool skipCommandExecution()
 * @method self enableSkipCommandExecution()
 * @method self disableSkipCommandExecution()
 *
 * @method bool skipCounterIncrements()
 * @method self enableSkipCounterIncrements()
 * @method self disableSkipCounterIncrements()
 *
 * @method bool skipUrlIndex()
 * @method self enableSkipUrlIndex()
 * @method self disableSkipUrlIndex()
 *
 * @method bool keepConnectorSilence()
 * @method self enableKeepConnectorSilence()
 * @method self disableKeepConnectorSilence()
 *
 * @method bool skipConnectorSend()
 * @method self enableSkipConnectorSend()
 * @method self disableSkipConnectorSend()
 *
 * @method bool forceConnectorSend()
 * @method self enableForceConnectorSend()
 * @method self disableForceConnectorSend()
 *
 * @method bool skipOpenlineSession()
 * @method self enableSkipOpenlineSession()
 * @method self disableSkipOpenlineSession()
 *
 * @method int fakeRelation()
 * @method self setFakeRelation(int $value)
 */
class SendingConfig
{
	protected const TYPE_BOOL = 'BOOL';
	protected const TYPE_INT = 'INT';

	protected const MAP_LEGACY_TO_ACTUAL_FIELD = [
		'URL_PREVIEW' => [
			'actual' => 'generateUrlPreview',
			'type' => self::TYPE_BOOL,
		],
		'SKIP_USER_CHECK' => [
			'actual' => 'skipUserCheck',
			'type' => self::TYPE_BOOL,
		],
		'PUSH' => [
			'actual' => 'sendPush',
			'type' => self::TYPE_BOOL,
		],
		'PUSH_IMPORTANT' => [
			'actual' => 'sendPushImmediately',
			'type' => self::TYPE_BOOL,
		],
		'RECENT_ADD' => [
			'actual' => 'addRecent',
			'type' => self::TYPE_BOOL,
		],
		'RECENT_SKIP_AUTHOR' => [
			'actual' => 'skipAuthorAddRecent',
			'type' => self::TYPE_BOOL,
		],
		'CONVERT' => [
			'actual' => 'convertMode',
			'type' => self::TYPE_BOOL,
		],
		'SKIP_COMMAND' => [
			'actual' => 'skipCommandExecution',
			'type' => self::TYPE_BOOL,
		],
		'SKIP_COUNTER_INCREMENTS' => [
			'actual' => 'skipCounterIncrements',
			'type' => self::TYPE_BOOL,
		],
		'SILENT_CONNECTOR' => [
			'actual' => 'keepConnectorSilence',
			'type' => self::TYPE_BOOL,
		],
		'SKIP_CONNECTOR' => [
			'actual' => 'skipConnectorSend',
			'type' => self::TYPE_BOOL,
		],
		'IMPORTANT_CONNECTOR' => [
			'actual' => 'forceConnectorSend',
			'type' => self::TYPE_BOOL,
		],
		'NO_SESSION_OL' => [
			'actual' => 'skipOpenlineSession',
			'type' => self::TYPE_BOOL,
		],
		'FAKE_RELATION' => [
			'actual' => 'fakeRelation',
			'type' => self::TYPE_INT,
		],
		'SKIP_URL_INDEX' => [
			'actual' => 'skipUrlIndex',
			'type' => self::TYPE_BOOL,
		],
	];

	/** URL_PREVIEW - Generate URL preview attachment and insert date PUT/SEND command. */
	private bool $generateUrlPreview = true;

	/**
	 * SKIP_USER_CHECK - Skip chat relations check.
	 * Check if user has permission to write into open chat, open line or announce channel.
	 */
	private bool $skipUserCheck = false;

	/** PUSH - Allows sending pull. */
	private bool $sendPush = true;

	/** PUSH_IMPORTANT - Send push immediately. */
	private bool $sendPushImmediately = false;

	/**
	 * RECENT_ADD - Refresh of the recent list for chat relations.
	 * Do not flow up recent on hidden notification.
	 */
	private bool $addRecent = true;

	/** RECENT_SKIP_AUTHOR - Do not add author into recent list in case of self message chat. */
	private bool $skipAuthorAddRecent = false;

	/** CONVERT - Suppress events firing and pull sending on import operations. */
	private bool $convertMode = false;

	/** SKIP_COMMAND - Skip command execution @see \Bitrix\Im\Command::onCommandAdd */
	private bool $skipCommandExecution = false;

	private bool $skipFireEventBeforeMessageNotifySend = false;

	private bool $skipCounterIncrements = false;

	/**
	 * SILENT_CONNECTOR - Keep silent. Do not send message into OL connector to the client side.
	 * @see \Bitrix\ImOpenLines\Connector::onMessageSend
	 */
	private bool $keepConnectorSilence = false;

	/**
	 * SKIP_CONNECTOR - Do not send message into OL connector to the client side.
	 * @see \Bitrix\ImOpenLines\Connector::onMessageSend
	 */
	private bool $skipConnectorSend = false;

	/**
	 * IMPORTANT_CONNECTOR - Forward message into OL channel either mark as system.
	 * @see \Bitrix\ImOpenLines\Connector::onMessageSend
	 */
	private bool $forceConnectorSend = false;

	/**
	 * NO_SESSION_OL - Do not touch OL session.
	 * @see \Bitrix\ImOpenLines\Connector::onMessageSend
	 */
	private bool $skipOpenlineSession = false;

	private int $fakeRelation = 0;

	public function __construct(?array $args = null)
	{
		if (is_array($args))
		{
			$this->fillByLegacy($args);
		}
	}

	public static function create($sendingConfig): self
	{
		if ($sendingConfig instanceof self)
		{
			return $sendingConfig;
		}

		if (is_array($sendingConfig))
		{
			return (new static($sendingConfig));
		}

		return new static();
	}

	public function fillByLegacy(array $data): void
	{
		foreach ($data as $fieldName => $fieldValue)
		{
			if (!isset(self::MAP_LEGACY_TO_ACTUAL_FIELD[$fieldName]))
			{
				continue;
			}

			$fieldInfo = self::MAP_LEGACY_TO_ACTUAL_FIELD[$fieldName];
			$actualFieldName = $fieldInfo['actual'];
			$this->{$actualFieldName} = $this->prepareValue($fieldInfo['type'], $actualFieldName, $fieldValue);
		}
	}

	public function isSkipFireEventBeforeMessageNotifySend(): bool
	{
		return $this->skipFireEventBeforeMessageNotifySend;
	}

	public function skipFireEventBeforeMessageNotifySend(bool $flag = true): self
	{
		$this->skipFireEventBeforeMessageNotifySend = $flag;

		return $this;
	}

	//region Fields magic

	public function __call(string $name, array $arguments)
	{
		if (isset($this->{$name}))
		{
			return $this->{$name};
		}

		$parseResult = $this->parseFunction($name, $arguments);

		if (empty($parseResult))
		{
			return null;
		}

		[$fieldName, $fieldValue] = $parseResult;
		$this->{$fieldName} = $fieldValue;

		return $this;
	}

	protected function parseFunction(string $name, array $arguments): array
	{
		$mapPrefixToArgument = [
			'enable' => true,
			'allow' => true,
			'disable' => false,
			'disallow' => false,
			'set' => $arguments[0] ?? null,
		];

		foreach ($mapPrefixToArgument as $prefix => $argument)
		{
			if (str_starts_with($name, $prefix))
			{
				return [$this->getFieldNameByFunctionName($name, $prefix), $argument];
			}
		}

		return [];
	}

	protected function getFieldNameByFunctionName(string $name, string $prefix): string
	{
		return lcfirst(mb_substr($name, mb_strlen($prefix)));
	}

	public function toArray(array $options = []): array
	{
		$boolAsString = $options['BOOL_AS_STRING'] ?? true;

		$data = [];
		foreach (self::MAP_LEGACY_TO_ACTUAL_FIELD as $legacyFieldName => $fieldInfo)
		{
			$actualFieldName = $fieldInfo['actual'];
			if ($fieldInfo['type'] === self::TYPE_BOOL && $boolAsString)
			{
				$data[$legacyFieldName] = $this->{$actualFieldName} ? 'Y' : 'N';
			}
			else
			{
				$data[$legacyFieldName] = $this->{$actualFieldName};
			}
		}

		return $data;
	}

	protected function prepareValue(string $type, string $key, $value)
	{
		return match ($type)
		{
			self::TYPE_BOOL => $this->prepareFlag($value),
			self::TYPE_INT => (int)$value,
			default => null,
		};
	}

	protected function prepareFlag($value): bool
	{
		if (is_bool($value))
		{
			return $value;
		}

		return $value === 'Y';
	}
	//endregion
}