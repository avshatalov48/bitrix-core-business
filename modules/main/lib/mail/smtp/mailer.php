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
use Bitrix\Main\Mail\Sender;
use Bitrix\Main\Mail\SenderSendCounter;
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
	private const HEADER_FROM_REGEX = '/(?<=From:).*?(?=\\n)/iu';
	private const HEADER_CC_REGEX = '/^\s*cc:(?<emails>.+)/im';
	private const HEADER_BCC_REGEX = '/^\s*bcc:(?<emails>.+)/im';

	private $configuration;

	protected function getActualConfiguration(Context $context): array
	{
		$configuration = \Bitrix\Main\Config\Configuration::getValue('smtp');
		if ($context->getSmtp())
		{
			return [
				'host' => $context->getSmtp()->getHost(),
				'port' => $context->getSmtp()->getPort(),
				'encryption_type' => $context->getSmtp()->getProtocol() ?? 'smtp',
				'login' => $context->getSmtp()->getLogin(),
				'password' => $context->getSmtp()->getPassword(),
				'from' => $context->getSmtp()->getFrom(),
				'debug' => $configuration['debug'] ?? false,
				'force_from' => $configuration['force_from'] ?? false,
				'logFile' => $configuration['log_file'] ?? null,
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
		$this->configuration = $this->getActualConfiguration($context);

		$this->SMTPDebug = $this->configuration['debug'] ?? false;

		if ($this->SMTPDebug)
		{
			$configuration = $this->configuration;
			$this->Debugoutput = function ($logMessage) use ($configuration) {
				$logger = new FileLogger(
					$configuration['logFile'] ?? (($_SERVER['DOCUMENT_ROOT'] ?? __DIR__) . '/mailer.log')
				);
				$logger->info($logMessage);
			};
		}
		$this->isSMTP();

		$this->SMTPAuth = (bool)$this->configuration['password'];

		if (
			!$this->configuration['host']
			|| !$this->configuration['login']
		)
		{
			return false;
		}

		$this->From = $this->configuration['from'];
		$this->Host = $this->configuration['host'];
		$this->Username = $this->configuration['login'];
		$this->Password = $this->configuration['password'] ?? '';
		$this->Port = $this->configuration['port'] ?? 465;

		if (
			'smtps' === $this->configuration['encryption_type']
			|| ('smtp' !== $this->configuration['encryption_type'] && 465 === $this->port))
		{
			$this->SMTPSecure = $this->Port == 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
		}

		$this->Timeout = $this->configuration['connection_timeout'] ?? 30;

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
		string $sourceTo,
		string $subject,
		string $message,
		string $additional_headers,
		string $additional_parameters
	): bool
	{

		$addresses = Mailer::parseAddresses($sourceTo)??[];
		$eol = \Bitrix\Main\Mail\Mail::getMailEol();

		if ($subject && $additional_headers)
		{
			$this->Subject = $subject;
			$additional_headers .= $eol . 'Subject: ' . $subject;
		}

		preg_match(self::HEADER_FROM_REGEX, $additional_headers, $headerFrom);;
		if ($this->configuration['force_from'] && $headerFrom)
		{
			$additional_headers = preg_replace(
				self::HEADER_FROM_REGEX,
				$this->configuration['from'],
				$additional_headers
			);
		}

		if (!$headerFrom)
		{
			$additional_headers .= $eol . 'From: ' . $this->configuration['from'];
		}

		$this->clearAllRecipients();
		foreach ($addresses as $to)
		{
			if (!$to['address'])
			{
				continue;
			}
			$this->addAddress($to['address'], $to['name']);
		}

		$additional_headers .= $eol . 'To: ' . $sourceTo;

		$this->prepareBCCRecipients($additional_headers);
		$this->prepareCCRecipients($additional_headers);

		$this->setMIMEBody($message);
		$this->setMIMEHeader($additional_headers);

		$canSend = $this->checkLimit();
		if (!$canSend)
		{
			return false;
		}

		$sendResult = $this->postSend();

		if ($sendResult)
		{
			$this->increaseLimit();
		}

		return $sendResult;
	}

	private function prepareCCRecipients($additional_headers)
	{
		preg_match(self::HEADER_CC_REGEX, $additional_headers, $matches);

		if ($matches)
		{
			$recipients = explode(',', trim($matches['emails']));

			foreach ($recipients as $to)
			{
				$to = self::parseAddresses($to) ?? [];
				if (!$to)
				{
					continue;
				}
				$this->addCC($to[0]['address'], $to[0]['name']);
			}
		}
	}

	private function prepareBCCRecipients($additional_headers)
	{
		preg_match(self::HEADER_BCC_REGEX, $additional_headers, $matches);

		if ($matches)
		{
			$recipients = explode(',', trim($matches['emails']));

			foreach ($recipients as $to)
			{
				$to = self::parseAddresses($to) ?? [];
				if (!$to)
				{
					continue;
				}
				$this->addBCC($to[0]['address'], $to[0]['name']);
			}
		}
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

		if (\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			// Private addresses can't be used in the cloud
			$ip = \Bitrix\Main\Web\IpAddress::createByName($context->getSmtp()->getHost());
			if ($ip->isPrivate())
			{
				$errors->setError(new Error('SMTP server address is invalid'));
				return false;
			}
		}

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

	private function checkLimit(): bool
	{
		$from = self::parseAddresses($this->From)[0]['address'];
		$count = count($this->getAllRecipientAddresses());

		$emailCounter = new SenderSendCounter();
		$emailDailyLimit = Sender::getEmailLimit($from);
		if($emailDailyLimit
			&& ($emailCounter->get($from) + $count) > $emailDailyLimit)
		{
			//daily limit exceeded
			return false;
		}

		return true;
	}

	private function increaseLimit()
	{
		$from = self::parseAddresses($this->From)[0]['address'];
		$emailDailyLimit = Sender::getEmailLimit($from);

		if (!$emailDailyLimit)
		{
			return;
		}

		$emailCounter = new SenderSendCounter();
		$count = count($this->getAllRecipientAddresses());

		$emailCounter->increment($from, $count);
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