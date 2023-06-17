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
 * @method self enableUserCheck()
 * @method self disableUserCheck()
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
 */
class SendingConfig
{
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

	public function __construct($args = null)
	{
		if (is_array($args))
		{
			$this->fill($args);
		}
	}

	//region Fields magic

	public function __call(string $name, array $arguments)
	{
		$fields = $this->fieldMirror();
		if (isset($fields['flags'][$name]))
		{
			return (bool)$this->{$name};
		}

		foreach ($fields['flags'] as $field => [$key, $switchOn, $switchOff])
		{
			if ($name == $switchOn)
			{
				$this->{$field} = true;
				return $this;
			}
			if ($name == $switchOff)
			{
				$this->{$field} = false;
				return $this;
			}
		}

		return null;
	}

	public function toArray(): array
	{
		$fields = $this->fieldMirror();
		$data = [];
		foreach ($fields['flags'] as $field => [$key,,])
		{
			$data[$key] = $this->{$field};
		}

		return $data;
	}

	public function fill(array $data): void
	{
		$fields = $this->fieldMirror();
		foreach ($data as $field => $value)
		{
			if (isset($fields['flags'][$field]))
			{
				if (is_bool($value))
				{
					$this->{$field} = $value;
				}
				else
				{
					$this->{$field} = ($value === 'Y');
				}
			}
		}
		foreach ($fields['flags'] as $field => [$key,,])
		{
			if (isset($data[$key]))
			{
				if (is_bool($data[$key]))
				{
					$this->{$field} = $data[$key];
				}
				else
				{
					$this->{$field} = ($data[$key] === 'Y');
				}
			}
		}
	}

	private function fieldMirror(): array
	{
		return [
			'flags' => [
				/** @see SendingConfig::$generateUrlPreview */
				'generateUrlPreview' => [
					'URL_PREVIEW',
					'enableUrlPreview',
					'disableUrlPreview',
				],
				/** @see SendingConfig::$skipUserCheck */
				'skipUserCheck' => [
					'SKIP_USER_CHECK',
					'enableUserCheck',
					'disableUserCheck',
				],
				/** @see SendingConfig::$sendPush */
				'sendPush' => [
					'PUSH',
					'allowSendPush',
					'disallowSendPush',
				],
				/** @see SendingConfig::$sendPushImmediately */
				'sendPushImmediately' => [
					'PUSH_IMPORTANT',
					'allowPushImmediately',
					'disallowPushImmediately',
				],
				/** @see SendingConfig::$addRecent */
				'addRecent' => [
					'RECENT_ADD',
					'enableAddRecent',
					'disableAddRecent',
				],
				/** @see SendingConfig::$skipAuthorAddRecent */
				'skipAuthorAddRecent' => [
					'RECENT_SKIP_AUTHOR',
					'enableSkipAuthorAddRecent',
					'disableSkipAuthorAddRecent',
				],
				/** @see SendingConfig::$convertMode */
				'convertMode' => [
					'CONVERT',
					'enableConvertMode',
					'disableConvertMode',
				],
				/** @see SendingConfig::$skipCommandExecution */
				'skipCommandExecution' => [
					'SKIP_COMMAND',
					'enableSkipCommandExecution',
					'disableSkipCommandExecution',
				],
				/** @see SendingConfig::$keepConnectorSilence */
				'keepConnectorSilence' => [
					'SILENT_CONNECTOR',
					'enableKeepConnectorSilence',
					'disableKeepConnectorSilence',
				],
				/** @see SendingConfig::$skipConnectorSend */
				'skipConnectorSend' => [
					'SKIP_CONNECTOR',
					'enableSkipConnectorSend',
					'disableSkipConnectorSend',
				],
				/** @see SendingConfig::$forceConnectorSend */
				'forceConnectorSend' => [
					'IMPORTANT_CONNECTOR',
					'enableForceConnectorSend',
					'disableForceConnectorSend',
				],
				/** @see SendingConfig::$skipOpenlineSession */
				'skipOpenlineSession' => [
					'NO_SESSION_OL',
					'enableSkipOpenlineSession',
					'disableSkipOpenlineSession',
				],
			]
		];
	}
	//endregion
}