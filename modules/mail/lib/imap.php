<?php

namespace Bitrix\Mail;

use Bitrix\Main;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Emoji;

Loc::loadMessages(__FILE__);

class Imap
{
	const LOG_LEVEL_WRITE = 1;
	const LOG_LEVEL_READ = 2;

	const ERR_CONNECT          = 101;
	const ERR_REJECTED         = 102;
	const ERR_COMMUNICATE      = 103;
	const ERR_EMPTY_RESPONSE   = 104;
	const ERR_BAD_SERVER       = 105;

	const ERR_STARTTLS         = 201;
	const ERR_COMMAND_REJECTED = 202;
	const ERR_CAPABILITY       = 203;
	const ERR_AUTH             = 204;
	const ERR_AUTH_MECH        = 205;
	const ERR_AUTH_OAUTH       = 206;
	const ERR_LIST             = 207;
	const ERR_SELECT           = 208;
	const ERR_SEARCH           = 209;
	const ERR_FETCH            = 210;
	const ERR_APPEND           = 211;
	const ERR_STORE            = 212;

	protected $stream, $errors;
	protected $sessState, $sessCapability, $sessCounter, $sessUntagged, $sessMailbox;
	protected $logLevel = 0, $logPath;

	protected $options = array();

	protected static $atomRegex    = '[^\x00-\x20\x22\x25\x28-\x2a\x5c\x5d\x7b\x7f-\xff]+';
	protected static $qcharRegex   = '[^\x00\x0a\x0d\x22\x5c\x80-\xff]|\x5c[\x5c\x22]';
	protected static $qcharExtRegex = '[^\x00\x0a\x0d\x22\x5c]|\x5c[\x5c\x22]'; // #119098
	protected static $astringRegex = '[^\x00-\x20\x22\x25\x28-\x2a\x5c\x7b\x7f-\xff]+';

	public function __construct($host, $port, $tls, $strict, $login, $password, $encoding = null)
	{
		$this->reset();

		$strict = (bool) $strict;

		$this->options = array(
			'host'    => $host,
			'port'    => $port,
			'tls'     => $tls,
			'socket'  => sprintf('%s://%s:%s', ($tls ? 'ssl' : 'tcp'), $host, $port),
			'timeout' => \COption::getOptionInt('mail', 'connect_timeout', B_MAIL_TIMEOUT),
			'context' => stream_context_create(array(
				'ssl' => array(
					'verify_peer' => $strict,
					'verify_peer_name' => $strict,
					'crypto_method' => STREAM_CRYPTO_METHOD_ANY_CLIENT,
				)
			)),
			'login'    => $login,
			'password' => $password,
			'encoding' => $encoding ?: LANG_CHARSET,
		);

		$logParams = Main\Config\Configuration::getValue('imap');
		if(isset($logParams["log_level"]) && $logParams["log_level"] > 0)
		{
			$this->logLevel = $logParams["log_level"];
			if(isset($logParams["log_path"]) && $logParams["log_path"] <> '')
			{
				$this->logPath = $logParams["log_path"];
			}
		}
	}

	public function __destruct()
	{
		$this->disconnect();
	}

	protected function disconnect()
	{
		if (!is_null($this->stream))
		{
			@fclose($this->stream);
			unset($this->stream);
		}
	}

	protected function reset()
	{
		$this->disconnect();

		unset($this->sessState);
		unset($this->sessCapability);
		$this->sessCounter = 0;
		$this->sessUntagged = array();
		$this->sessMailbox = array(
			'name'        => null,
			'exists'      => null,
			'uidvalidity' => null,
			'permanentflags' => null,
		);
		$this->errors = new Main\ErrorCollection();
	}

	public function getState()
	{
		return $this->sessState;
	}

	public function connect(&$error)
	{
		$error = null;

		if (!empty($this->sessState))
			return true;

		$resource = @stream_socket_client(
			$this->options['socket'], $errno, $errstr, $this->options['timeout'],
			STREAM_CLIENT_CONNECT, $this->options['context']
		);

		if ($resource === false)
		{
			$error = $this->errorMessage(Imap::ERR_CONNECT, $errno ?: null);
			return false;
		}

		$this->stream = $resource;

		if ($this->options['timeout'] > 0)
			stream_set_timeout($this->stream, $this->options['timeout']);

		$prompt = $this->readLine();

		if ($prompt !== false && preg_match('/^\* (OK|PREAUTH)/i', $prompt, $matches))
		{
			if ($matches[1] == 'OK')
				$this->sessState = 'no_auth';
			elseif ($matches[1] == 'PREAUTH')
				$this->sessState = 'auth';
		}
		else
		{
			if ($prompt === false)
				$error = Imap::ERR_EMPTY_RESPONSE;
			elseif (preg_match('/^\* BYE/i', $prompt))
				$error = Imap::ERR_REJECTED;
			else
				$error = Imap::ERR_BAD_SERVER;

			$error = $this->errorMessage(array(Imap::ERR_CONNECT, $error));

			return false;
		}

		if (!$this->capability($error))
			return false;

		if (!$this->options['tls'] && preg_match('/ \x20 STARTTLS ( \x20 | \r\n ) /ix', $this->sessCapability))
		{
			if (!$this->starttls($error))
				return false;
		}

		return true;
	}

	protected function starttls(&$error)
	{
		$error = null;

		if (!$this->sessState)
		{
			$error = $this->errorMessage(Imap::ERR_STARTTLS);
			return false;
		}

		$response = $this->executeCommand('STARTTLS', $error);

		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Imap::ERR_STARTTLS, $error), $response);

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

			$error = $this->errorMessage(Imap::ERR_STARTTLS);
			return false;
		}

		return true;
	}

	protected function capability(&$error)
	{
		$error = null;

		if (!$this->sessState)
		{
			$error = $this->errorMessage(Imap::ERR_CAPABILITY);
			return false;
		}

		$response = $this->executeCommand('CAPABILITY', $error);

		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Imap::ERR_CAPABILITY, $error), $response);

			return false;
		}

		$regex = '/^ \* \x20 CAPABILITY /ix';
		foreach ($this->getUntagged($regex, true) as $item)
			$this->sessCapability = $item[0];

		return true;
	}

	public function authenticate(&$error)
	{
		$error = null;

		if (!$this->connect($error))
			return false;
		if (in_array($this->sessState, array('auth', 'select')))
			return true;

		$mech = false;
		$token = null;

		if (preg_match('/ \x20 AUTH=XOAUTH2 ( \x20 | \r\n ) /ix', $this->sessCapability))
		{
			$token = Helper\OAuth::getTokenByMeta($this->options['password']);

			if (!empty($token))
			{
				$mech = 'oauth';
			}
			else if (false === $token)
			{
				$error = $this->errorMessage(array(Imap::ERR_AUTH, Imap::ERR_AUTH_OAUTH));
				return false;
			}
		}

		if ($mech == false)
		{
			if (preg_match('/ \x20 AUTH=PLAIN ( \x20 | \r\n ) /ix', $this->sessCapability))
				$mech = 'plain';
			elseif (!preg_match('/ \x20 LOGINDISABLED ( \x20 | \r\n ) /ix', $this->sessCapability))
				$mech = 'login';
		}

		if (!$mech)
		{
			$error = $this->errorMessage(array(Imap::ERR_AUTH, Imap::ERR_AUTH_MECH));
			return false;
		}

		if ($mech == 'oauth')
		{
			$response = $this->executeCommand('AUTHENTICATE XOAUTH2', $error);

			if (mb_strpos($response, '+') !== 0)
			{
				$error = $error == Imap::ERR_COMMAND_REJECTED ? Imap::ERR_AUTH_MECH : $error;
				$error = $this->errorMessage(array(Imap::ERR_AUTH, $error), $response);

				return false;
			}

			$response = $this->exchange(base64_encode(sprintf(
				"user=%s\x01auth=Bearer %s\x01\x01", $this->options['login'], $token
			)), $error);

			if (mb_strpos($response, '+') === 0)
				$response = $this->exchange("\r\n", $error);
		}
		elseif ($mech == 'plain')
		{
			$response = $this->executeCommand('AUTHENTICATE PLAIN', $error);

			if (mb_strpos($response, '+') !== 0)
			{
				$error = $error == Imap::ERR_COMMAND_REJECTED ? Imap::ERR_AUTH_MECH : $error;
				$error = $this->errorMessage(array(Imap::ERR_AUTH, $error), $response);

				return false;
			}

			$response = $this->exchange(base64_encode(sprintf(
				"\x00%s\x00%s",
				Encoding::convertEncoding($this->options['login'], $this->options['encoding'], 'UTF-8'),
				Encoding::convertEncoding($this->options['password'], $this->options['encoding'], 'UTF-8')
			)), $error);
		}
		else // if ($mech == 'login')
		{
			$response = $this->executeCommand(sprintf(
				'LOGIN %s %s',
				static::prepareString($this->options['login']),
				static::prepareString($this->options['password'])
			), $error);
		}

		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Imap::ERR_AUTH, $error), $response);

			return false;
		}

		$this->sessState = 'auth';

		if (!$this->capability($error))
			return false;

		return true;
	}

	public function select($mailbox, &$error)
	{
		$error = null;

		if (!$this->authenticate($error))
			return false;
		if ($this->sessState == 'select' && $mailbox == $this->sessMailbox['name'])
			return $this->sessMailbox;

		$response = $this->executeCommand(sprintf(
			'SELECT "%s"', static::escapeQuoted($this->encodeUtf7Imap($mailbox))
		), $error);

		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Imap::ERR_SELECT, $error), $response);

			return false;
		}

		$this->sessState = 'select';
		$this->sessMailbox = array(
			'name'        => $mailbox,
			'exists'      => null,
			'uidvalidity' => null,
		);

		$regex = '/^ \* \x20 ( \d+ ) \x20 EXISTS /ix';
		foreach ($this->getUntagged($regex, true) as $item)
			$this->sessMailbox['exists'] = $item[1][1];

		$regex = '/^ \* \x20 OK \x20 \[ UIDVALIDITY \x20 ( \d+ ) \] /ix';
		foreach ($this->getUntagged($regex, true) as $item)
			$this->sessMailbox['uidvalidity'] = $item[1][1];

		$regex = sprintf(
			'/^ \* \x20 OK \x20 \[ PERMANENTFLAGS \x20 \( ( ( \x5c? %1$s | \x5c \* ) ( \x20 (?2) )* )? \) \] /ix',
			self::$atomRegex
		);
		foreach ($this->getUntagged($regex, true) as $item)
		{
			$this->sessMailbox['permanentflags'] = explode("\x20", $item[1][1]);
		}

		if (!$this->capability($error))
			return false;

		return $this->sessMailbox;
	}

	public function examine($mailbox, &$error)
	{
		$error = null;

		if (!$this->authenticate($error))
			return false;

		$response = $this->executeCommand(sprintf(
			'EXAMINE "%s"', static::escapeQuoted($this->encodeUtf7Imap($mailbox))
		), $error);

		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Imap::ERR_SELECT, $error), $response);

			return false;
		}

		$result = array();

		$regex = '/^ \* \x20 ( \d+ ) \x20 EXISTS /ix';
		foreach ($this->getUntagged($regex, true) as $item)
			$result['exists'] = $item[1][1];

		$regex = '/^ \* \x20 OK \x20 \[ UIDVALIDITY \x20 ( \d+ ) \] /ix';
		foreach ($this->getUntagged($regex, true) as $item)
			$result['uidvalidity'] = $item[1][1];

		$regex = sprintf(
			'/^ \* \x20 OK \x20 \[ PERMANENTFLAGS \x20 \( ( ( \x5c? %1$s | \x5c \* ) ( \x20 (?2) )* )? \) \] /ix',
			self::$atomRegex
		);
		foreach ($this->getUntagged($regex, true) as $item)
		{
			$result['permanentflags'] = explode("\x20", $item[1][1]);
		}

		return $result;
	}

	/**
	 * Connects to server and authenticate client
	 *
	 * @param string &$error Error message.
	 * @return boolean
	 */
	public function singin(&$error)
	{
		$error = null;

		return $this->authenticate($error);
	}

	/**
	 * @param $uid
	 * @param $mailbox
	 * @param $range
	 * @param $select
	 * @param $error
	 * @param string $outputFormat 'smart' or 'list'
	 * @return array|false|mixed
	 */
	public function fetch($uid, $mailbox, $range, $select, &$error, $outputFormat = 'smart')
	{
		$error = null;

		if (!preg_match('/(([1-9]\d*|\*)(:(?2))?)(,(?1))*/', $range))
		{
			return false;
		}

		if (empty($select))
		{
			$select = '(FLAGS)';
		}
		else if (is_array($select))
		{
			$select = sprintf('(%s)', join(' ', $select));
		}

		if (!$this->select($mailbox, $error))
		{
			return false;
		}

		$list = array();

		if ($this->sessMailbox['exists'] > 0)
		{
			$fetchUntaggedRegex = '/^ \* \x20 ( \d+ ) \x20 FETCH \x20 \( ( .+ ) \) \r\n $/isx';
			$this->getUntagged($fetchUntaggedRegex, true);

			$response = $this->executeCommand(
				sprintf('%sFETCH %s %s', $uid ? 'UID ' : '', $range, $select),
				$error
			);

			if ($error)
			{
				$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
				$error = $this->errorMessage(array(Imap::ERR_FETCH, $error), $response);

				return false;
			}

			$shiftName = function (&$item)
			{
				$result = false;

				// #120949
				$regex = sprintf(
					'/^
						(
							[a-z0-9]+ (?: \. [a-z0-9]+ )*
							(?: \[ (?: [a-z0-9]+ (?: \. [a-z0-9]* )* (?: \x20 %s )* )? \] )?
							(?: < \d+ > )?
						)
						\x20
					/ix',
					self::$astringRegex
				);

				if (preg_match($regex, $item, $matches))
				{
					$result = $matches[1];

					$item = substr($item, strlen($matches[0]));
				}

				return $result;
			};

			$shiftValue = function (&$item) use (&$shiftValue)
			{
				$result = false;

				$tail = ' (?= [\x20)] | $ ) \x20? ';

				if (substr($item, 0, 1) === '(')
				{
					$item = substr($item, 1);

					$result = array();

					while (strlen($item) > 0 && substr($item, 0, 1) !== ')')
					{
						$subresult = $shiftValue($item);

						if (false !== $subresult)
						{
							$result[] = $subresult;
						}
						else
						{
							return false;
						}
					}

					if (preg_match('/^ \) (?= [\x20()] | $ ) \x20? /ix', $item, $matches))
					{
						$item = substr($item, strlen($matches[0]));
					}
					else
					{
						return false;
					}
				}
				else if (preg_match('/^ { ( \d+ ) } \r\n /ix', $item, $matches))
				{
					$item = substr($item, strlen($matches[0]));

					if (strlen($item) >= $matches[1])
					{
						$result = substr($item, 0, $matches[1]);

						$item = substr($item, $matches[1]);

						if (preg_match(sprintf('/^ %s /ix', $tail), $item, $matches))
						{
							$item = substr($item, strlen($matches[0]));
						}
						else
						{
							return false;
						}
					}
				}
				else if (preg_match(sprintf('/^ NIL %s /ix', $tail), $item, $matches))
				{
					$result = null;

					$item = substr($item, strlen($matches[0]));
				}
				else if (preg_match(sprintf('/^ " ( (?: %s )* ) " %s /ix', self::$qcharExtRegex, $tail), $item, $matches))
				{
					$result = self::unescapeQuoted($matches[1]);

					$item = substr($item, strlen($matches[0]));
				}
				else if (preg_match(sprintf('/^ ( \x5c? %s ) %s /ix', self::$astringRegex, $tail), $item, $matches))
				{
					$result = $matches[1];

					$item = substr($item, strlen($matches[0]));
				}
				else if (preg_match(sprintf('/^ %s /ix', $tail), $item, $matches))
				{
					$result = '';

					$item = substr($item, strlen($matches[0]));
				}

				return $result;
			};

			$bodystructure = function (&$value) use (&$bodystructure)
			{
				if (!is_array($value) || !is_array($value[0]))
				{
					return $value;
				}

				$value[0] = $bodystructure($value[0]);
				$value[0] = array($value[0]);

				while (array_key_exists(1, $value) && is_array($value[1]))
				{
					$value[0][] = $bodystructure($value[1]);

					array_splice($value, 1, 1);
				}

				while (count($value[0]) == 1 && count($value[0][0]) == 1)
				{
					$value[0] = $value[0][0];
				}

				return $value;
			};

			foreach ($this->getUntagged($fetchUntaggedRegex, true) as $item)
			{
				$data = array(
					'id' => $item[1][1],
				);

				while (strlen($item[1][2]) > 0)
				{
					if (($name = $shiftName($item[1][2])) !== false)
					{
						if (($value = $shiftValue($item[1][2])) !== false)
						{
							if (in_array(mb_strtoupper($name), array('BODY', 'BODYSTRUCTURE')))
							{
								$value = $bodystructure($value);
							}

							$data[$name] = $value;

							continue;
						}
					}

					break;
				}

				$list[$data['id']] = $data;
			}
		}

		ksort($list);

		// todo remove the different format of the data output
		if ($outputFormat === 'smart' && !preg_match('/[:,]/', $range))
		{
			$list = reset($list);
		}

		return $list;
	}

	public function getUIDsForSpecificDay($dirPath, $internalDate)
	{
		$error = [];

		if (!$this->select($dirPath, $error))
		{
			return false;
		}

		//since some mail services (example mail.ru ) do not support the 'on' search criterion
		$command = 'UID SEARCH SINCE '.date("j-M-Y", strtotime($internalDate)).' BEFORE '.date('j-M-Y', strtotime($internalDate.' +1 day'));

		$response = $this->executeCommand($command, $error);

		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Imap::ERR_SEARCH, $error), $response);

			return false;
		}

		$UIDs = [];
		$regex = '/^ \* \x20 SEARCH \x20 ( .+ ) \r\n $ /ix';
		foreach ($this->getUntagged($regex, true) as $item)
		{
			preg_match_all('/\d+/', $item[1][1],$UIDs);
		}

		if(count($UIDs) === 0 )
		{
			return [];
		}

		return $UIDs[0];
	}

	/**
	 * Returns unseen messages count
	 *
	 * @param string $dirPath dir path.
	 * @param string &$error Error message.
	 * @param null, string $startInternalDate start internal date for count.
	 *
	 * @return int|false
	 */
	public function getUnseen($dirPath, &$error, $startInternalDate = null)
	{
		$error = null;

		if (!$this->select($dirPath, $error))
		{
			return false;
		}

		$unseen = 0;

		if (!($this->sessMailbox['exists'] > 0))
		{
			return $unseen;
		}

		$command = 'SEARCH UNSEEN';

		if(!is_null($startInternalDate))
		{
			$command .= (' SINCE '.$startInternalDate->format('j-M-Y'));
		}
		$response = $this->executeCommand($command, $error);

		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Imap::ERR_SEARCH, $error), $response);

			return false;
		}

		$regex = '/^ \* \x20 SEARCH \x20 ( .+ ) \r\n $ /ix';
		foreach ($this->getUntagged($regex, true) as $item)
		{
			$unseen = preg_match_all('/\d+/', $item[1][1]);
		}

		return $unseen;
	}

	public function getNew($mailbox, $uidMin, $uidMax, &$error)
	{
		$error = null;

		if (!($uidMin <= $uidMax))
		{
			return false;
		}

		if (!$this->select($mailbox, $error))
		{
			return false;
		}

		$new = 0;

		if (!($this->sessMailbox['exists'] > 0))
		{
			return $new;
		}

		if ($uidMax < 1)
		{
			return $this->sessMailbox['exists'];
		}

		$range = $this->getUidRange($mailbox, $error);

		if (empty($range))
		{
			return false;
		}

		[$min, $max] = $range;

		$searches = array();

		if ($uidMin > 1 && $uidMin > $min)
		{
			$searches[] = sprintf('%u:%u', $min, $uidMin - 1);
		}

		if ($uidMax > 0 && $uidMax < $max)
		{
			$searches[] = sprintf('%u:%u', $uidMax + 1, $max);
		}

		if (!empty($searches))
		{
			$response = $this->executeCommand(sprintf('SEARCH UID %s', join(',', $searches)), $error);

			if ($error)
			{
				$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
				$error = $this->errorMessage(array(Imap::ERR_SEARCH, $error), $response);

				return false;
			}

			$regex = '/^ \* \x20 SEARCH \x20 ( .+ ) \r\n $ /ix';
			foreach ($this->getUntagged($regex, true) as $item)
			{
				$new = preg_match_all('/\d+/', $item[1][1]);
			}
		}

		return $new;
	}

	public function getUidRange($mailbox, &$error)
	{
		$error = null;

		if (!$this->select($mailbox, $error))
		{
			return false;
		}

		if (!($this->sessMailbox['exists'] > 0))
		{
			return false;
		}

		$range = $this->fetch(false, $mailbox, sprintf('1,%u', $this->sessMailbox['exists']), '(UID)', $error);

		if (empty($range) || empty($range[1]))
		{
			return false;
		}

		return array(
			$range[1]['UID'],
			end($range)['UID'],
		);
	}

	public function listex($reference, $pattern, &$error)
	{
		$error = null;

		if (!$this->authenticate($error))
		{
			return false;
		}

		$response = $this->executeCommand(sprintf(
			'LIST "%s" "%s"',
			static::escapeQuoted($this->encodeUtf7Imap($reference)),
			static::escapeQuoted($this->encodeUtf7Imap($pattern))
		), $error);

		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Imap::ERR_LIST, $error), $response);

			return false;
		}

		$list = array();

		$regex = sprintf(
			'/^ \* \x20 LIST \x20
				\( (?<flags> ( \x5c? %1$s ( \x20 \x5c? %1$s )* )? ) \) \x20
				(?<delim> NIL | " ( %2$s ) " ) \x20
				(?<name> \{ \d+ \} | " ( %2$s )* " | %3$s ) \r\n
				(?<ext> .* )
			/ix',
			self::$atomRegex, self::$qcharRegex, self::$astringRegex
		);
		foreach ($this->getUntagged($regex, true) as $item)
		{
			[$item, $matches] = $item;

			$sflags = $matches['flags'];
			$sdelim = $matches['delim'];
			$sname  = $matches['name'];

			if (preg_match('/^ " ( .+ ) " $/ix', $sdelim, $quoted))
			{
				$sdelim = static::unescapeQuoted($quoted[1]);
			}

			if (preg_match('/^ \{ ( \d+ ) \} $/ix', $sname, $literal))
			{
				$sname = substr($matches['ext'], 0, $literal[1]);
			}
			else if (preg_match('/^ " ( .* ) " $/ix', $sname, $quoted))
			{
				$sname = static::unescapeQuoted($quoted[1]);
			}

			$sname = $this->decodeUtf7Imap($sname);

			// #79498
			if (mb_strtoupper($sdelim) != 'NIL')
				$sname = rtrim($sname, $sdelim);

			$list[] = array(
				'name'  => $sname,
				'delim' => mb_strtoupper($sdelim) == 'NIL' ? 'NIL' : $sdelim,
				'flags' => preg_split('/\s+/i', $sflags, -1, PREG_SPLIT_NO_EMPTY),
			);
		}

		return $list;
	}

	/**
	 * Returns mailboxes list
	 *
	 * @param string $pattern Mailbox name pattern.
	 * @param string &$error Error message.
	 * @return array|false
	 */
	public function listMailboxes($pattern, &$error, $flat = false)
	{
		$error = null;

		$listGetter = function ($parent = null, $level = 0) use (&$listGetter, &$delimiter, &$error)
		{
			$pattern = $parent ? sprintf('%s%s%%', $parent, $delimiter) : '%';

			$list = $this->listex('', $pattern, $error);

			if (false === $list)
			{
				return false;
			}

			foreach ($list as $i => $item)
			{
				$item['title'] = $item['name'];
				$item['level'] = $level;

				if ($parent)
				{
					$regex = sprintf(
						'/^%s%s(.)/',
						preg_quote($parent, '/'),
						preg_quote($delimiter, '/')
					);

					if (!preg_match($regex, $item['name']))
					{
						unset($list[$i]);
						continue;
					}

					$item['title'] = preg_replace($regex, '\1', $item['name']);
				}

				if ($item['name'] == $parent)
				{
					continue;
				}

				if ($item['delim'] === null)
				{
					continue;
				}

				$delimiter = $item['delim'];

				if (!preg_grep('/^ \x5c ( Noinferiors | HasNoChildren ) $/ix', $item['flags']))
				{
					$children = $listGetter($item['name'], $level + 1);

					if ($children === false)
					{
						return false;
					}

					if (!empty($children))
					{
						$item['children'] = $children;
					}
				}

				$list[$i] = $item;
			}

			return array_values($list);
		};

		$list = $listGetter();

		if (false === $list)
		{
			return false;
		}

		$regex = sprintf(
			'/^%s$/i',
			preg_replace(
				array('/ ( \x5c \* )+ /x', '/ ( \% )+ /x'),
				array('.*', $delimiter ? sprintf('[^\x%s]*', bin2hex($delimiter)) : '.*'),
				preg_quote($pattern, '/')
			)
		);

		$listFilter = function ($list) use (&$listFilter, $regex)
		{
			foreach ($list as $i => $item)
			{
				if (!preg_match($regex, $item['name']))
				{
					if (empty($item['children']))
					{
						unset($list[$i]);
						continue;
					}
				}

				if (!empty($item['children']))
				{
					$item['children'] = $listFilter($item['children']);

					if (empty($item['children']))
					{
						unset($item['children']);
					}
				}

				$list[$i] = $item;
			}

			$list = array_values($list);

			for ($i = 0; $i < count($list); $i++)
			{
				$item = $list[$i];

				if (!preg_match($regex, $item['name']))
				{
					$children = empty($item['children']) ? array() : $item['children'];

					array_splice($list, $i, 1, $children);
					$i += count($children) - 1;
				}
			}

			return $list;
		};

		$list = $listFilter($list);

		$listHandler = function ($list, $path = array()) use (&$listHandler, $regex, $flat)
		{
			for ($i = 0; $i < count($list); $i++)
			{
				$item = $list[$i];

				$item['path'] = array_merge($path, array($item['title']));

				if (!empty($item['children']))
				{
					$item['children'] = $listHandler($item['children'], $item['path']);
				}

				$list[$i] = $item;

				if ($flat && !empty($item['children']))
				{
					unset($list[$i]['children']);

					array_splice($list, $i + 1, 0, $item['children']);
					$i += count($item['children']);
				}
			}

			return array_values($list);
		};

		$list = $listHandler($list);

		return $list;
	}

	public function listMessages($mailbox, &$uidtoken, &$error)
	{
		$error = null;

		$params = array(
			'offset' => 0,
			'limit'  => -1,
		);

		if (is_array($mailbox))
		{
			$params  = array_merge($params, $mailbox);
			$mailbox = $mailbox['mailbox'];
		}

		if (!($params['offset'] > 0))
		{
			$params['offset'] = 0;
		}

		if (!($params['limit'] > 0))
		{
			$params['limit'] = -1;
		}

		if (!$this->select($mailbox, $error))
		{
			return false;
		}

		if (!($this->sessMailbox['exists'] > 0) || $params['offset'] + 1 > $this->sessMailbox['exists'])
		{
			return array();
		}

		if ($params['limit'] > 0 && $params['offset'] + $params['limit'] > $this->sessMailbox['exists'])
		{
			$params['limit'] = $this->sessMailbox['exists'] - $params['offset'];
		}

		$uidtoken = $this->sessMailbox['uidvalidity'];

		$list = $this->fetch(
			false,
			$mailbox,
			sprintf(
				'%u:%s',
				$params['offset'] + 1,
				$params['limit'] > 0 ? (int) ($params['offset']+$params['limit']) : '*'
			),
			array_merge(
				!is_null($uidtoken) ? array('UID') : array(),
				array('INTERNALDATE', 'RFC822.SIZE', 'FLAGS')
			),
			$error
		);

		foreach ($list as $id => $data)
		{
			$list[$id] = array(
				'id'    => $id,
				'uid'   => array_key_exists('UID', $data) ? $data['UID'] : null,
				'date'  => array_key_exists('INTERNALDATE', $data) ? $data['INTERNALDATE'] : null,
				'size'  => array_key_exists('RFC822.SIZE', $data) ? $data['RFC822.SIZE'] : null,
				'flags' => array_key_exists('FLAGS', $data) ? $data['FLAGS'] : array(),
			);
		}

		$list = array_filter(
			$list,
			function ($item)
			{
				return isset($item['date'], $item['size'], $item['flags']);
			}
		);

		return $list;
	}

	/**
	 * Adds message
	 *
	 * @param string $mailbox Mailbox name.
	 * @param string $data Message.
	 * @param string &$error Error message.
	 * @return string|false
	 */
	public function addMessage($mailbox, $data, &$error)
	{
		$error = null;

		return $this->append($mailbox, array('\Seen'), new \DateTime, $data, $error);
	}

	public function searchByHeader($uid, $mailbox, array $header, &$error)
	{
		$error = null;

		if (!$this->select($mailbox, $error))
		{
			return false;
		}

		if (empty($header))
		{
			return false;
		}

		$result = array();

		$response = $this->executeCommand(
			$ccc = sprintf(
				'%sSEARCH %s', $uid ? 'UID ' : '',
				join(
					' ',
					array_map(
						function ($name, $value)
						{
							return sprintf(
								'HEADER %s %s',
								static::prepareString($name),
								static::prepareString($value)
							);
						},
						array_keys($header),
						array_values($header)
					)
				)
			),
			$error
		);

		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Imap::ERR_SEARCH, $error), $response);

			return false;
		}

		$regex = '/^ \* \x20 SEARCH \x20 ( .+ ) \r\n $ /ix';
		foreach ($this->getUntagged($regex, true) as $item)
		{
			$result = preg_match_all('/\d+/', $item[1][1]);
		}

		return $result;
	}

	public function append($mailbox, array $flags, \DateTime $internaldate, $data, &$error)
	{
		$error = null;

		if (!$this->authenticate($error))
		{
			return false;
		}

		foreach ($flags as $k => $item)
		{
			if (!preg_match(sprintf('/ ^ \x5c? %s $ /ix', self::$atomRegex), $item))
			{
				unset($flags[$k]);
			}
		}

		$response = $this->executeCommand(sprintf(
			'APPEND "%s" (%s) "%26s" %s',
			static::escapeQuoted($this->encodeUtf7Imap($mailbox)),
			join(' ', $flags),
			$internaldate->format('j-M-Y H:i:s O'),
			static::prepareString($data)
		), $error);

		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Imap::ERR_APPEND, $error), $response);

			return false;
		}

		$regex = sprintf('/^ OK \x20 \[ APPENDUID \x20 ( \d+ ) \x20 ( \d+ ) \] /ix', $this->getTag());
		if (preg_match($regex, $response, $matches))
		{
			return sprintf('%u:%u', $matches[1], $matches[2]);
		}

		return true;
	}

	public function moveMails($ids, $folderFrom, $folderTo)
	{
		$folderTo = Emoji::decode($folderTo);
		$error = null;
		$result = new Main\Result();
		if (!$this->authenticate($error))
		{
			return $result->addError(new Main\Error(''));
		}

		if (preg_match('/ \x20 MOVE ( \x20 | \r\n ) /ix', $this->sessCapability))
		{
			$result = $this->move($ids, $folderFrom, $folderTo);
		}
		else
		{
			$result = $this->copyMailToFolder($ids, $folderFrom, $folderTo);
			if ($result->isSuccess())
			{
				$result = $this->delete($ids, $folderFrom);
			}
		}
		return $result;
	}

	public function move($ids, $folderFrom, $folderTo)
	{
		$error = null;
		$result = new Main\Result();
		if (!$this->authenticate($error))
		{
			return $result->addError(new Main\Error(''));
		}
		if (!$this->select($folderFrom, $error))
		{
			return $result->addError(new Main\Error(''));
		}
		$response = $this->executeCommand(sprintf('UID MOVE %s "%s"', $this->prepareIdsParam($ids), static::escapeQuoted($this->encodeUtf7Imap($folderTo))), $error);
		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage([Imap::ERR_STORE, $error], $response);
			return $result->addError(new Main\Error($error));
		}

		return $result;
	}

	public function copyMailToFolder($ids, $mailboxName, $folder)
	{
		$error = null;
		$result = new Main\Result();
		if (!$this->authenticate($error))
		{
			return $result->addError(new Main\Error(''));
		}
		if (!$this->select($mailboxName, $error))
		{
			return $result->addError(new Main\Error(''));
		}
		$response = $this->executeCommand(sprintf('UID COPY %s "%s"', $this->prepareIdsParam($ids), static::escapeQuoted($this->encodeUtf7Imap($folder))), $error);
		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Imap::ERR_STORE, $error), $response);
			return $result->addError(new Main\Error($error));
		}

		return $result;
	}

	public function unseen($ids, $folder)
	{
		$error = null;
		$result = new Main\Result();
		if (!$this->authenticate($error))
		{
			return $result->addError(new Main\Error(''));
		}
		if (!$this->select($folder, $error))
		{
			return $result->addError(new Main\Error(''));
		}

		$response = $this->store($ids, ['\Seen'], $error, true, true);
		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage([Imap::ERR_STORE, $error], $response);
			return $result->addError(new Main\Error($error));
		}
		return $result;
	}

	public function seen($ids, $folder)
	{
		$error = null;
		$result = new Main\Result();
		if (!$this->authenticate($error))
		{
			return $result->addError(new Main\Error(''));
		}
		if (!$this->select($folder, $error))
		{
			return $result->addError(new Main\Error(''));
		}

		$response = $this->store($ids, ['\Seen'], $error);
		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage([Imap::ERR_STORE, $error], $response);
			return $result->addError(new Main\Error($error));
		}
		return $result;
	}

	/**
	 * @param $id
	 * @param $mailboxName
	 * @return Main\Result
	 */
	public function delete($id, $mailboxName)
	{
		$error = null;
		$result = new Main\Result();
		if (!$this->authenticate($error))
		{
			return $result->addError(new Main\Error(''));
		}
		if (!$this->select($mailboxName, $error))
		{
			return $result->addError(new Main\Error(''));
		}
		$response = $this->store($id, ['\Deleted'], $error);
		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Imap::ERR_STORE, $error), $response);
			return $result->addError(new Main\Error($error));
		}

		$response = $this->expunge($error);
		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Imap::ERR_STORE, $error), $response);
			return $result->addError(new Main\Error($error));
		}
		return $result;
	}

	/**
	 * @param int|array $ids
	 * @param $error
	 * @return bool|null|string|string[]
	 */
	private function expunge(&$error)
	{
		return $this->executeCommand('EXPUNGE', $error);
	}

	/**
	 * @param int|array $ids
	 * @param $flags
	 * @param $error
	 * @param bool $isUid
	 * @param bool $isRemoveFlags
	 * @return bool|null|string|string[]
	 */
	private function store($ids, $flags, &$error, $isUid = true, $isRemoveFlags = false)
	{
		$command = sprintf('STORE %s ', $this->prepareIdsParam($ids));
		$command .= $isRemoveFlags ? '-' : '+';
		$command = $command . sprintf('FLAGS (%s)', join(' ', $flags));

		if ($isUid)
		{
			$command = 'UID ' . $command;
		}

		return $this->executeCommand($command, $error);
	}

	public function updateMessageFlags($mailbox, $id, $flags, &$error)
	{
		$error = null;
		$response = '';

		if (!$this->select($mailbox, $error))
		{
			return false;
		}

		$addFlags = array();
		$delFlags = array();
		foreach ($flags as $name => $value)
		{
			if (preg_match(sprintf('/ ^ \x5c? %s $ /ix', self::$atomRegex), $name))
			{
				if ($value)
				{
					$addFlags[] = $name;
				}
				else
				{
					$delFlags[] = $name;
				}
			}
		}

		if ($addFlags)
		{
			$response = $this->store($id, $addFlags, $error, false);
		}

		if (!$error && $delFlags)
		{
			$response = $this->store($id, $delFlags, $error, false, true);
		}

		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Imap::ERR_STORE, $error), $response);

			return false;
		}

		$this->getUntagged(sprintf('/^ \* \x20 %u \x20 FETCH \x20 \( .+ \) \r\n $/isx', $id), true);

		return true;
	}

	/**
	 * Returns message
	 *
	 * @param string $mailbox Mailbox name.
	 * @param int $id Message ID.
	 * @param string $section Message section.
	 * @param string &$error Error message.
	 * @return string|false
	 */
	public function getMessage($mailbox, $id, $section, &$error)
	{
		$error = null;

		$section = mb_strtoupper($section);

		if (!in_array(mb_strtoupper($section), array('HEADER', 'TEXT')))
		{
			$section = '';
		}

		if (!$this->select($mailbox, $error))
		{
			return false;
		}

		$response = $this->fetch(false, $mailbox, (int) $id, sprintf('BODY.PEEK[%s]', $section), $error);

		return $response[sprintf('BODY[%s]', $section)];
	}

	public function isExistsDir($mailbox, &$error)
	{
		$error = null;

		$dirs = $this->listex('', $mailbox, $error);

		if (is_array($dirs) && empty($dirs))
		{
			return false;
		}

		return true;
	}

	public function ensureEmpty($mailbox, &$error)
	{
		$error = null;

		if (!$this->select($mailbox, $error))
		{
			return false;
		}

		if ($this->sessMailbox['exists'] > 0)
		{
			return false;
		}

		$searchUntaggedRegex = '/^ \* \x20 SEARCH \x20 ( .+ ) \r\n $ /ix';
		$this->getUntagged($searchUntaggedRegex, true);

		$response = $this->executeCommand('UID SEARCH 1', $error);

		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Imap::ERR_SEARCH, $error), $response);

			return false;
		}

		$matches = 0;
		foreach ($this->getUntagged($searchUntaggedRegex, true) as $item)
		{
			$matches = preg_match_all('/\d+/', $item[1][1]);
		}

		if ($matches > 0)
		{
			addMessage2Log(
				sprintf(
					'IMAP: invalid mailbox (search>exists) (%s:%s:%s:%u)',
					$this->options['host'], $this->options['login'], $mailbox, $this->sessMailbox['uidvalidity']
				),
				'mail', 0, false
			);

			return false;
		}

		$fetchUntaggedRegex = '/^ \* \x20 ( \d+ ) \x20 FETCH \x20 \( ( .+ ) \) \r\n $/isx';
		$this->getUntagged($fetchUntaggedRegex, true);

		$response = $this->executeCommand('FETCH 1 (UID)', $error);

		if ($error)
		{
			$error = $error == Imap::ERR_COMMAND_REJECTED ? null : $error;
			$error = $this->errorMessage(array(Imap::ERR_FETCH, $error), $response);

			return false;
		}

		$matches = 0;
		foreach ($this->getUntagged($fetchUntaggedRegex, true) as $item)
		{
			$matches = $item[1][1];
		}

		if ($matches > 0)
		{
			addMessage2Log(
				sprintf(
					'IMAP: invalid mailbox (fetch>exists) (%s:%s:%s:%u)',
					$this->options['host'], $this->options['login'], $mailbox, $this->sessMailbox['uidvalidity']
				),
				'mail', 0, false
			);

			return false;
		}

		return true;
	}

	protected function getUntagged($regex, $unset = false)
	{
		$result = array();

		$length = count($this->sessUntagged);
		for ($i = 0; $i < $length; $i++)
		{
			if (!preg_match($regex, $this->sessUntagged[$i], $matches))
				continue;

			unset($matches[0]);
			$result[] = array($this->sessUntagged[$i], $matches);

			if ($unset)
				unset($this->sessUntagged[$i]);
		}

		if ($unset && !empty($result))
			$this->sessUntagged = array_values($this->sessUntagged);

		return $result;
	}

	protected function getTag($next = false)
	{
		if ($next)
			$this->sessCounter++;

		return sprintf('A%03u', $this->sessCounter);
	}

	protected function executeCommand($command, &$error)
	{
		$error = null;
		$response = false;

		$chunks = explode("\x00", sprintf('%s %s', $this->getTag(true), $command));

		$k = count($chunks);
		foreach ($chunks as $chunk)
		{
			$k--;

			$response = $this->exchange($chunk, $error);

			if ($k > 0 && mb_strpos($response, '+') !== 0)
				break;
		}

		return $response;
	}

	protected function exchange($data, &$error)
	{
		$error = null;

		if ($this->sendData(sprintf("%s\r\n", $data)) === false)
		{
			$error = Imap::ERR_COMMUNICATE;
			return false;
		}

		$response = $this->readResponse();

		if ($response === false)
		{
			$error = Imap::ERR_EMPTY_RESPONSE;
			return false;
		}

		$response = trim($response);

		if (!preg_match(sprintf('/^ %s \x20 OK /ix', $this->getTag()), $response))
		{
			if (preg_match(sprintf('/^ %s \x20 ( NO | BAD ) /ix', $this->getTag()), $response))
				$error = Imap::ERR_COMMAND_REJECTED;
			else
				$error = Imap::ERR_BAD_SERVER;
		}

		return preg_replace(sprintf('/^ %s \x20 /ix', $this->getTag()), '', $response);
	}

	protected function sendData($data)
	{
		$logData = null;
		if(($this->logLevel & self::LOG_LEVEL_WRITE))
		{
			$logData = $data;
		}

		$fails = 0;
		while (strlen($data) > 0 && !feof($this->stream))
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

			$data = substr($data, $bytes);
		}

		if (strlen($data) > 0)
		{
			$this->reset();
			return false;
		}

		if($logData !== null)
		{
			$this->writeToLog($logData);
		}

		return true;
	}

	protected function readBytes($bytes)
	{
		$data = '';

		while ($bytes > 0 && !feof($this->stream))
		{
			$buffer = @fread($this->stream, $bytes);
			if ($buffer === false)
				break;

			$meta = $this->options['timeout'] > 0
				? stream_get_meta_data($this->stream)
				: array('timed_out' => false);

			$data  .= $buffer;
			$bytes -= strlen($buffer);

			if ($meta['timed_out'])
				break;
		}

		if ($bytes > 0)
		{
			$this->reset();
			return false;
		}

		return $data;
	}

	protected function readLine()
	{
		$line = '';

		while (!feof($this->stream))
		{
			$buffer = @fgets($this->stream, 4096);
			if ($buffer === false)
				break;

			$meta = $this->options['timeout'] > 0
				? stream_get_meta_data($this->stream)
				: array('timed_out' => false);

			$line .= $buffer;

			$eolRegex = '/ (?<literal> \{ (?<bytes> \d+ ) \} )? \r\n $ /x';
			if (preg_match($eolRegex, $line, $matches))
			{
				if (empty($matches['literal']))
					break;

				if ($meta['timed_out'])
					return false;

				$data = $this->readBytes($matches['bytes']);
				if ($data === false)
					return false;

				$line .= $data;
			}

			if ($meta['timed_out'])
				break;
		}

		if (!preg_match('/\r\n$/', $line, $matches))
		{
			$this->reset();
			return false;
		}

		if(($this->logLevel & self::LOG_LEVEL_READ))
		{
			$this->writeToLog($line);
		}

		return $line;
	}

	protected function readResponse()
	{
		do
		{
			$line = $this->readLine();
			if ($line === false)
				return false;

			if (mb_strpos($line, '*') === 0)
				$this->sessUntagged[] = $line;
		}
		while (mb_strpos($line, '*') === 0);

		if ('select' == $this->sessState)
		{
			$regex = '/^ \* \x20 ( \d+ ) \x20 EXISTS /ix';
			foreach ($this->getUntagged($regex) as $item)
			{
				$this->sessMailbox['exists'] = $item[1][1];
			}
		}

		return $line;
	}

	protected static function prepareString($data)
	{
		if (preg_match('/^[^\x00\x0a\x0d\x80-\xff]*$/', $data))
			return sprintf('"%s"', static::escapeQuoted($data));
		else
			return sprintf("{%u}\x00%s", strlen($data), $data);
	}

	protected static function escapeQuoted($data)
	{
		return str_replace(array('\\', '"'), array('\\\\', '\\"'), $data);
	}

	protected static function unescapeQuoted($data)
	{
		return str_replace(array('\\\\', '\\"'), array('\\', '"'), $data);
	}

	protected function encodeUtf7Imap($data)
	{
		if (!$data)
			return $data;

		$result = Encoding::convertEncoding($data, $this->options['encoding'], 'UTF7-IMAP');

		if ($result === false)
		{
			$result = $data;

			$result = Encoding::convertEncoding($result, $this->options['encoding'], 'UTF-8');
			$result = str_replace('&', '&-', $result);

			$result = preg_replace_callback('/[\x00-\x1f\x7f-\xff]+/', function($matches)
			{
				$result = $matches[0];

				$result = Encoding::convertEncoding($result, 'UTF-8', 'UTF-16BE');
				$result = base64_encode($result);
				$result = str_replace('/', ',', $result);
				$result = str_replace('=', '', $result);
				$result = '&' . $result . '-';

				return $result;
			}, $result);
		}

		return $result;
	}

	protected function decodeUtf7Imap($data)
	{
		if (!$data)
			return $data;

		$result = Encoding::convertEncoding($data, 'UTF7-IMAP', $this->options['encoding']);

		if ($result === false)
		{
			$result = $data;

			$result = preg_replace_callback('/&([\x2b\x2c\x30-\x39\x41-\x5a\x61-\x7a]+)-/', function($matches)
			{
				$result = $matches[1];

				$result = str_replace(',', '/', $result);
				$result = base64_decode($result);
				$result = Encoding::convertEncoding($result, 'UTF-16BE', 'UTF-8');

				return $result;
			}, $result);

			$result = str_replace('&-', '&', $result);
			$result = Encoding::convertEncoding($result, 'UTF-8', $this->options['encoding']);
		}

		return $result;
	}

	private function prepareIdsParam($idsData)
	{
		if (is_array($idsData))
		{
			return implode(',', array_map('intval', $idsData));
		}
		else
		{
			return intval($idsData);
		}
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
			$error .= sprintf(' (IMAP: %s)', join(': ', $details));

			$this->errors->setError(new Main\Error('IMAP', -1));
			foreach ($details as $item)
			{
				$this->errors->setError(new Main\Error((string) $item, -1));
			}
		}

		return $error;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Returns error message
	 *
	 * @param int $code Error code.
	 * @return string
	 */
	public static function decodeError($code)
	{
		switch ($code)
		{
			case self::ERR_CONNECT:
				return Loc::getMessage('MAIL_IMAP_ERR_CONNECT');
			case self::ERR_REJECTED:
				return Loc::getMessage('MAIL_IMAP_ERR_REJECTED');
			case self::ERR_COMMUNICATE:
				return Loc::getMessage('MAIL_IMAP_ERR_COMMUNICATE');
			case self::ERR_EMPTY_RESPONSE:
				return Loc::getMessage('MAIL_IMAP_ERR_EMPTY_RESPONSE');
			case self::ERR_BAD_SERVER:
				return Loc::getMessage('MAIL_IMAP_ERR_BAD_SERVER');
			case self::ERR_STARTTLS:
				return Loc::getMessage('MAIL_IMAP_ERR_STARTTLS');
			case self::ERR_COMMAND_REJECTED:
				return Loc::getMessage('MAIL_IMAP_ERR_COMMAND_REJECTED');
			case self::ERR_CAPABILITY:
				return Loc::getMessage('MAIL_IMAP_ERR_CAPABILITY');
			case self::ERR_AUTH:
			case self::ERR_SELECT:
				return Loc::getMessage('MAIL_IMAP_ERR_AUTH');
			case self::ERR_AUTH_MECH:
				return Loc::getMessage('MAIL_IMAP_ERR_AUTH_MECH');
			case self::ERR_AUTH_OAUTH:
				return Loc::getMessage('MAIL_IMAP_ERR_AUTH_OAUTH');
			case self::ERR_LIST:
				return Loc::getMessage('MAIL_IMAP_ERR_LIST');
			case self::ERR_SEARCH:
				return Loc::getMessage('MAIL_IMAP_ERR_SEARCH');
			case self::ERR_FETCH:
				return Loc::getMessage('MAIL_IMAP_ERR_FETCH');
			case self::ERR_APPEND:
				return Loc::getMessage('MAIL_IMAP_ERR_APPEND');
			case self::ERR_STORE:
				return Loc::getMessage('MAIL_IMAP_ERR_STORE');

			default:
				return Loc::getMessage('MAIL_IMAP_ERR_DEFAULT');
		}
	}

	protected function writeToLog($data)
	{
		if($this->logPath <> '')
		{
			$fileName = $this->options["host"].".".$this->options["login"].".log";
			file_put_contents($this->logPath."/".$fileName, $data, FILE_APPEND);
		}
	}
}
