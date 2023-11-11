<?php

namespace Bitrix\Mail;

use Bitrix\Main;
use Bitrix\Main\Text\BinaryString;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Smtp
{
	const ERR_CONNECT = 101;
	const ERR_REJECTED = 102;
	const ERR_COMMUNICATE = 103;
	const ERR_EMPTY_RESPONSE = 104;

	const ERR_STARTTLS = 201;
	const ERR_COMMAND_REJECTED = 202;
	const ERR_CAPABILITY = 203;
	const ERR_AUTH = 204;
	const ERR_AUTH_MECH = 205;

	protected $stream, $errors;
	protected $sessCapability;

	protected $options = array();

	/**
	 * Is password value OAuth meta build
	 *
	 * @var bool
	 */
	protected bool $isOauth = false;

	/**
	 * Smtp client constructor.
	 *
	 * @param string $host Host.
	 * @param string $port Port.
	 * @param string $tls Tls.
	 * @param string $strict Strict.
	 * @param string $login Login.
	 * @param string $password Password.
	 * @param string|null $encoding.  If null - current site encoding.
	 */
	public function __construct($host, $port, $tls, $strict, $login, $password, $encoding = null)
	{
		$this->reset();

		$this->options = array(
			'host'    => $host,
			'port'    => $port,
			'tls'     => $tls,
			'socket'  => sprintf('%s://%s:%s', ($tls ? 'ssl' : 'tcp'), $host, $port),
			'timeout' => \COption::getOptionInt('mail', 'connect_timeout', B_MAIL_TIMEOUT),
			'context' => stream_context_create(array(
				'ssl' => array(
					'verify_peer' => (bool) $strict,
					'verify_peer_name' => (bool) $strict,
					'crypto_method' => STREAM_CRYPTO_METHOD_ANY_CLIENT,
				)
			)),
			'login'    => $login,
			'password' => $password,
			'encoding' => $encoding ?: LANG_CHARSET,
		);
	}

	/**
	 * Disconnects from the submission server.
	 *
	 * @return void
	 */
	public function __destruct()
	{
		$this->disconnect();
	}

	/**
	 * Disconnects from the submission server.
	 *
	 * @return void
	 */
	protected function disconnect()
	{
		if (!is_null($this->stream))
		{
			@fclose($this->stream);
		}

		unset($this->stream);
	}

	protected function reset()
	{
		$this->disconnect();

		$this->errors = new Main\ErrorCollection();
	}

	/**
	 * Connect to the submission server.
	 *
	 * @param array $error Will be filled with connection errors.
	 * @return bool True if the connection was successful, false - otherwise.
	 */
	public function connect(&$error)
	{
		$error = null;

		if ($this->stream)
		{
			return true;
		}

		$resource = @stream_socket_client(
			$this->options['socket'], $errno, $errstr, $this->options['timeout'],
			STREAM_CLIENT_CONNECT, $this->options['context']
		);

		if ($resource === false)
		{
			$error = $this->errorMessage(Smtp::ERR_CONNECT, $errno ?: null);
			return false;
		}

		$this->stream = $resource;

		if ($this->options['timeout'] > 0)
		{
			stream_set_timeout($this->stream, $this->options['timeout']);
		}

		$prompt = $this->readResponse();

		if (false === $prompt)
		{
			$error = $this->errorMessage(array(Smtp::ERR_CONNECT, Smtp::ERR_COMMUNICATE));
		}
		else if (!preg_match('/^ 220 ( \r\n | \x20 ) /x', end($prompt)))
		{
			$error = $this->errorMessage(array(Smtp::ERR_CONNECT, Smtp::ERR_REJECTED), trim(end($prompt)));
		}

		if ($error)
		{
			return false;
		}

		if (!$this->capability($error))
		{
			return false;
		}

		if (!$this->options['tls'] && preg_grep('/^ STARTTLS $/ix', $this->sessCapability))
		{
			if (!$this->starttls($error))
			{
				return false;
			}
		}

		return true;
	}

	protected function starttls(&$error)
	{
		$error = null;

		if (!$this->stream)
		{
			$error = $this->errorMessage(Smtp::ERR_STARTTLS);
			return false;
		}

		$response = $this->executeCommand('STARTTLS', $error);

		if ($error)
		{
			$error = $error == Smtp::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Smtp::ERR_STARTTLS, $error), $response ? trim(end($response)) : null);

			return false;
		}

		if (stream_socket_enable_crypto($this->stream, true, STREAM_CRYPTO_METHOD_ANY_CLIENT))
		{
			if (!$this->capability($error))
			{
				return false;
			}
		}
		else
		{
			$this->reset();

			$error = $this->errorMessage(Smtp::ERR_STARTTLS);
			return false;
		}

		return true;
	}

	protected function capability(&$error)
	{
		$error = null;

		if (!$this->stream)
		{
			$error = $this->errorMessage(Smtp::ERR_CAPABILITY);
			return false;
		}

		$response = $this->executeCommand(
			sprintf(
				'EHLO %s',
				Main\Context::getCurrent()->getRequest()->getHttpHost() ?: 'localhost'
			),
			$error
		);

		if ($error || !is_array($response))
		{
			$error = $error == Smtp::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Smtp::ERR_CAPABILITY, $error), $response ? trim(end($response)) : null);

			return false;
		}

		$this->sessCapability = array_map(
			function ($line)
			{
				return trim(mb_substr($line, 4));
			},
			$response
		);

		return true;
	}

	/**
	 * Authenticate to the submission server.
	 *
	 * @param array $error Will be filled with authentication errors.
	 * @return bool True if the authentication was successful, false - otherwise.
	 */
	public function authenticate(&$error)
	{
		$error = null;

		if (!$this->connect($error))
		{
			return false;
		}

		$mech = false;

		if ($capabilities = preg_grep('/^ AUTH \x20 /ix', $this->sessCapability))
		{
			if ($this->isOauth)
			{
				$mech = 'oauth';
			}
			else if (preg_grep('/ \x20 PLAIN ( \x20 | $ ) /ix', $capabilities))
			{
				$mech = 'plain';
			}
			else if (preg_grep('/ \x20 LOGIN ( \x20 | $ ) /ix', $capabilities))
			{
				$mech = 'login';
			}
		}

		if (!$mech)
		{
			$error = $this->errorMessage(array(Smtp::ERR_AUTH, Smtp::ERR_AUTH_MECH));
			return false;
		}

		if ($mech === 'oauth')
		{
			$token = Helper\OAuth::getTokenByMeta($this->options['password']);
			if (empty($token))
			{
				$error = $this->errorMessage(array(Smtp::ERR_AUTH, Smtp::ERR_AUTH_MECH));
				return false;
			}
			$formatted = sprintf("user=%s\x01auth=Bearer %s\x01\x01", $this->options['login'], $token);
			$response = $this->executeCommand(sprintf("AUTH XOAUTH2\x00%s", base64_encode($formatted)), $error);
		}
		else if ($mech === 'plain')
		{
			$response = $this->executeCommand(
				sprintf(
					"AUTH PLAIN\x00%s",
					base64_encode(sprintf(
						"\x00%s\x00%s",
						Encoding::convertEncoding($this->options['login'], $this->options['encoding'], 'UTF-8'),
						Encoding::convertEncoding($this->options['password'], $this->options['encoding'], 'UTF-8')
					))
				),
				$error
			);
		}
		else
		{
			$response = $this->executeCommand(sprintf(
				"AUTH LOGIN\x00%s\x00%s",
				base64_encode($this->options['login']),
				base64_encode($this->options['password'])
			), $error);
		}

		if ($error)
		{
			$error = $error == Smtp::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Smtp::ERR_AUTH, $error), $response ? trim(end($response)) : null);

			return false;
		}

		return true;
	}

	protected function executeCommand($command, &$error)
	{
		$error = null;
		$response = false;

		$chunks = explode("\x00", $command);

		$k = count($chunks);
		foreach ($chunks as $chunk)
		{
			$k--;

			$response = (array) $this->exchange($chunk, $error);

			if ($k > 0 && mb_strpos(end($response), '3') !== 0)
			{
				break;
			}
		}

		return $response;
	}

	protected function exchange($data, &$error)
	{
		$error = null;

		if ($this->sendData(sprintf("%s\r\n", $data)) === false)
		{
			$error = Smtp::ERR_COMMUNICATE;
			return false;
		}

		$response = $this->readResponse();

		if ($response === false)
		{
			$error = Smtp::ERR_COMMUNICATE;
			return false;
		}

		if (!preg_match('/^ [23] \d{2}  /ix', end($response)))
		{
			$error = Smtp::ERR_COMMAND_REJECTED;
		}

		return $response;
	}

	protected function sendData($data)
	{
		$fails = 0;
		while (BinaryString::getLength($data) > 0 && !feof($this->stream))
		{
			$bytes = @fputs($this->stream, $data);

			if (false == $bytes)
			{
				if (false === $bytes || ++$fails >= 3)
				{
					break;
				}

				continue;
			}

			$fails = 0;

			$data = BinaryString::getSubstring($data, $bytes);
		}

		if (BinaryString::getLength($data) > 0)
		{
			$this->reset();
			return false;
		}

		return true;
	}

	protected function readLine()
	{
		$line = '';

		while (!feof($this->stream))
		{
			$buffer = @fgets($this->stream, 4096);
			if ($buffer === false)
			{
				break;
			}

			$meta = ($this->options['timeout'] > 0 ? stream_get_meta_data($this->stream) : array('timed_out' => false));

			$line .= $buffer;

			if (preg_match('/\r\n$/', $buffer, $matches) || $meta['timed_out'])
			{
				break;
			}
		}

		if (!preg_match('/\r\n$/', $line, $matches))
		{
			$this->reset();

			return false;
		}

		return $line;
	}

	/**
	 * Reads and returns server response.
	 *
	 * @return array|false
	 */
	protected function readResponse()
	{
		$response = array();

		do
		{
			$line = $this->readLine();
			if ($line === false)
			{
				return false;
			}

			$response[] = $line;
		}
		while (!preg_match('/^ \d{3} ( \r\n | \x20 ) /x', $line));

		return $response;
	}

	protected function errorMessage($errors, $details = null)
	{
		$errors  = array_filter((array) $errors);
		$details = array_filter((array) $details);

		foreach ($errors as $i => $error)
		{
			$errors[$i] = static::decodeError($error);
			$this->errors->setError(new Main\Error((string) $errors[$i], $error > 0 ? $error : 0));
		}

		$error = join(': ', $errors);
		if ($details)
		{
			$error .= sprintf(' (SMTP: %s)', join(': ', $details));

			$this->errors->setError(new Main\Error('SMTP', -1));
			foreach ($details as $item)
			{
				$this->errors->setError(new Main\Error((string) $item, -1));
			}
		}

		return $error;
	}

	/**
	 * Returns all Smtp client errors.
	 *
	 * @return Main\ErrorCollection object.
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Returns error message by code.
	 *
	 * @param int $code Error code.
	 * @return string
	 */
	public static function decodeError($code)
	{
		switch ($code)
		{
			case self::ERR_CONNECT:
				return Loc::getMessage('MAIL_SMTP_ERR_CONNECT');
			case self::ERR_REJECTED:
				return Loc::getMessage('MAIL_SMTP_ERR_REJECTED');
			case self::ERR_COMMUNICATE:
				return Loc::getMessage('MAIL_SMTP_ERR_COMMUNICATE');
			case self::ERR_EMPTY_RESPONSE:
				return Loc::getMessage('MAIL_SMTP_ERR_EMPTY_RESPONSE');

			case self::ERR_STARTTLS:
				return Loc::getMessage('MAIL_SMTP_ERR_STARTTLS');
			case self::ERR_COMMAND_REJECTED:
				return Loc::getMessage('MAIL_SMTP_ERR_COMMAND_REJECTED');
			case self::ERR_CAPABILITY:
				return Loc::getMessage('MAIL_SMTP_ERR_CAPABILITY');
			case self::ERR_AUTH:
				return Loc::getMessage('MAIL_SMTP_ERR_AUTH');
			case self::ERR_AUTH_MECH:
				return Loc::getMessage('MAIL_SMTP_ERR_AUTH_MECH');

			default:
				return Loc::getMessage('MAIL_SMTP_ERR_DEFAULT');
		}
	}

	/**
	 * Set flag is need to connect with OAuth
	 *
	 * @param bool $value
	 *
	 * @return $this
	 */
	public function setIsOauth(bool $value): self
	{
		$this->isOauth = $value;
		return $this;
	}

}
