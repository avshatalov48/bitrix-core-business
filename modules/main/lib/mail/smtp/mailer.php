<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Mail\Smtp;

use Bitrix\Main\Error;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Mail\Context;
use Bitrix\Main\Localization\Loc;
use PHPMailer\PHPMailer\PHPMailer;
use Bitrix\Main\Diag\FileLogger;

class Mailer extends PHPMailer
{
	/**
	 * @var Mailer[] $instances
	 */
	private static $instances = [];
	public const KEEP_ALIVE_ALWAYS = 'keep_alive_always';
	public const KEEP_ALIVE_NONE = 'keep_alive_none';
	public const KEEP_ALIVE_OPTIONAL = 'keep_alive_optional';

	protected function getActualConfiguration(Context $context): array
	{
		$configuration = \Bitrix\Main\Config\Configuration::getValue('smtp');
		if ($context->getSmtp())
		{
			return [
				'host' => $context->getSmtp()->getHost(),
				'port' => $context->getSmtp()->getPort(),
				'encryption_type' => $context->getSmtp()->getProtocol() ,
				'login' => $context->getSmtp()->getLogin(),
				'password' => $context->getSmtp()->getPassword(),
				'from' => $context->getSmtp()->getFrom(),
				'debug' => $configuration['debug'] ?? false,
				'logFile' => $configuration['log_file'] ?? (($_SERVER['DOCUMENT_ROOT'] ?? __DIR__) . '/mailer.log'),
			];
		}

		return $configuration ?? [];
	}


	/**
	 * Prepare PHPMailer configuration from bitrix/.settings.php
	 * @param array|null $configuration
	 * @return bool
	 */
	public function prepareConfiguration(Context $context): bool
	{
		$preparedConfiguration = $this->getActualConfiguration($context);

		$this->SMTPDebug = $preparedConfiguration['debug'] ?? false;

		if ($this->SMTPDebug)
		{
			$this->Debugoutput = function ($logMessage) use ($preparedConfiguration) {
				$logger = new FileLogger(
					$preparedConfiguration['logFile']
				);
				$logger->info($logMessage);
			};
		}
		$this->isSMTP();

		$this->SMTPAuth = (bool)$preparedConfiguration['password'];

		if (
			!$preparedConfiguration['host']
			|| !$preparedConfiguration['login']
		)
		{
			return false;
		}

		$this->From = $preparedConfiguration['from'] ?? $this->From;
		$this->Host = $preparedConfiguration['host'];
		$this->Username = $preparedConfiguration['login'];
		$this->Password = $preparedConfiguration['password'] ?? '';
		$this->Port = $preparedConfiguration['port'] ?? 465;
		$this->SMTPSecure = $this->Port == 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

		$this->Timeout = $preparedConfiguration['connection_timeout'] ?? 30;

		return true;
	}

	/**
	 * Set prepared MIME body data
	 * @param $body
	 */
	public function setMIMEBody($body)
	{
		$this->MIMEBody = $body;
	}

	/**
	 * Set prepared MIME header
	 * @param $headers
	 */
	public function setMIMEHeader($headers)
	{
		$this->MIMEHeader = $headers;
	}


	/**
	 * Send mail via smtp connection
	 * @param string $to
	 * @param string $subject
	 * @param string $message
	 * @param string $additional_headers
	 * @param string $additional_parameters
	 * @return bool
	 * @throws \PHPMailer\PHPMailer\Exception
	 */
	public function sendMailBySmtp(
		string $to,
		string $subject,
		string $message,
		string $additional_headers,
		string $additional_parameters
	): bool
	{

		$to = Mailer::parseAddresses($to)[0]??[];

		if (!$to['address'])
		{
			return false;
		}

		if ($subject && $additional_headers)
		{
			$eol = \Bitrix\Main\Mail\Mail::getMailEol();
			$this->Subject = $subject;
			$additional_headers .= $eol . 'Subject: ' . $subject;
		}

		$this->clearAddresses();
		$this->addAddress($to['address'], $to['name']);
		$this->setMIMEBody($message);
		$this->setMIMEHeader($additional_headers);

		return $this->postSend();
	}

	/**
	 * Returns instance of current class
	 * @param Context $context
	 * @return Mailer|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \PHPMailer\PHPMailer\Exception
	 */
	public static function getInstance(Context $context): ?Mailer
	{
		$key = hash('sha256', serialize($context));
		if (!static::$instances[$key])
		{
			$mail = new Mailer();
			if (!$mail->prepareConfiguration($context))
			{
				return null;
			}

			if ($context->getSmtp())
			{
				$mail->setFrom($context->getSmtp()->getFrom());
			}

			switch ($context->getKeepAlive())
			{
				default:
				case self::KEEP_ALIVE_NONE:
					$mail->SMTPKeepAlive = false;
					break;
				case self::KEEP_ALIVE_ALWAYS:
					$mail->SMTPKeepAlive = true;
					HttpApplication::getInstance()->addBackgroundJob(
						function () use ($mail)
						{
							$mail->smtpClose();
						});
					break;
				case self::KEEP_ALIVE_OPTIONAL:
					$mail->SMTPKeepAlive = true;
					break;
			}

			static::$instances[$key] = $mail;
		}

		return static::$instances[$key];
	}

	/**
	 * Returns true if Login, Password and Server parameters is right
	 * Returns false if any errors in connection was detected
	 * @param Context $context
	 * @param \Bitrix\Main\ErrorCollection $errors
	 * @return bool
	 * @throws \PHPMailer\PHPMailer\Exception
	 */
	public static function checkConnect(Context $context, \Bitrix\Main\ErrorCollection $errors): bool
	{
		$mail = new Mailer();

		if (!$mail->prepareConfiguration($context))
		{
			return false;
		}

		if ($mail->smtpConnect())
		{
			$mail->smtpClose();
			return true;
		}

		$errors->setError(new Error(Loc::getMessage('main_mail_smtp_connection_failed')));
		return false;
	}

	/**
	 * Closes all instances connections
	 */
	public static function closeConnections(): void
	{
		foreach (static::$instances as $instance)
		{
			$instance->smtpClose();
		}
	}

}