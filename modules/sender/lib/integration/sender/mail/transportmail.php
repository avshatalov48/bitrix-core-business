<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Sender\Mail;

use Bitrix\Main;
use Bitrix\Main\Config\Option;
use Bitrix\Main\IO\File;
use Bitrix\Main\Result;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail;

use Bitrix\Sender\Message;
use Bitrix\Sender\Transport;
use Bitrix\Sender\Recipient;

use Bitrix\Sender\Integration;

Loc::loadMessages(__FILE__);

/**
 * Class TransportMail
 * @package Bitrix\Sender\Integration\Sender\Mail
 */
class TransportMail implements Transport\iBase, Transport\iDuration, Transport\iLimitation
{
	const CODE = self::CODE_MAIL;

	/** @var Message\Configuration $configuration Configuration. */
	protected $configuration;

	/** @var Mail\Context $mailContext Mail context. */
	protected $mailContext;

	/** @var Mail\Address $mailAddress Mail address. */
	protected $mailAddress;

	/**
	 * TransportMail constructor.
	 */
	public function __construct()
	{
		$this->configuration = new Message\Configuration();
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return Loc::getMessage('SENDER_INTEGRATION_MAIL_TRANSPORT_NAME');
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		return self::CODE;
	}

	/**
	 * Get supported recipient types.
	 *
	 * @return integer[]
	 */
	public function getSupportedRecipientTypes()
	{
		return array(Recipient\Type::EMAIL);
	}

	/**
	 * Load configuration.
	 *
	 * @param string|null $id ID.
	 *
	 * @return Message\Configuration
	 */
	public function loadConfiguration($id = null)
	{
		return $this->configuration;
	}

	/**
	 * Save configuration.
	 *
	 * @param Message\Configuration $configuration Configuration.
	 *
	 * @return Result|null
	 */
	public function saveConfiguration(Message\Configuration $configuration)
	{
		return null;
	}

	/**
	 * Start.
	 *
	 * @return void
	 */
	public function start()
	{

	}

	/**
	 * Send.
	 *
	 * @param Message\Adapter $message Message.
	 *
	 * @return bool
	 */
	public function send(Message\Adapter $message)
	{
		$headers = $message->getConfiguration()->get('HEADERS');
		$headers = is_array($headers) ? $headers : array();
		$fields = $message->getFields();

		$unsubLink = $message->getUnsubTracker()->getLink();
		if (!isset($fields['UNSUBSCRIBE_LINK']))
		{
			$fields['UNSUBSCRIBE_LINK'] = $unsubLink;
		}
		if ($unsubLink)
		{
			if (!preg_match('/^http:|https:/', $unsubLink))
			{
				$unsubLink = $this->getSenderLinkProtocol() . '://' . $message->getSiteServerName() . $unsubLink;
			}
			$headers['List-Unsubscribe'] = '<'.$unsubLink.'>';
		}

		$fields['SENDER_MAIL_CHARSET'] = $message->getCharset();

		if (Integration\Bitrix24\Service::isCloud())
		{
			$headers['X-Bitrix-Mail-Count'] = $message->getTransport()->getSendCount() ?: 1;
			$recipientData = $message->getRecipientData();
			if ($recipientData['CONTACT_IS_SEND_SUCCESS'] !== 'Y')
			{
				$headers['X-Bitrix-Mail-Unverified'] = 1;
			}
		}

		$linkParameters = $message->getConfiguration()->get('LINK_PARAMS');
		if($linkParameters)
		{
			$parametersTmp = [];
			parse_str($linkParameters, $parametersTmp);
			if(is_array($parametersTmp))
			{
				$clickUriParameters = $message->getClickTracker()->getUriParameters();
				$message->getClickTracker()->setUriParameters(
					array_merge($clickUriParameters, $parametersTmp)
				);
			}
		}

		$mailAttachment = array();
		$messageAttachment = $message->getConfiguration()->get('ATTACHMENT');
		$messageAttachment = is_array($messageAttachment) ? $messageAttachment : array();
		foreach ($messageAttachment as $key => $file)
		{
			if (is_numeric($file) && $file > 0)
			{
				continue;
			}

			if (is_array($file) && File::isFileExists($file['tmp_name']))
			{
				$mailAttachment[] = array(
					'PATH' => $file['tmp_name'],
					'ID' => md5($file['tmp_name']),
					'CONTENT_TYPE' => File::getFileContents($file['tmp_name']),
					'NAME' => ($file['name'] ?: 'some_file'),
				);
			}

			unset($messageAttachment[$key]);
		}

		//set callback entity Id
		if (Integration\Bitrix24\Service::isCloud())
		{
			if ($message->getRecipientId())
			{
				$this->getMailContext()->getCallback()
					->setEntityType('rcpt')
					->setEntityId($message->getRecipientId());
			}
			else
			{
				$this->getMailContext()->getCallback()
					->setEntityType('test')
					->setEntityId(time() . '.' . rand(100, 1000));
			}
		}

		$mailMessageParams = array(
			'EVENT' => null,
			'FIELDS' => $fields,
			'MESSAGE' => array(
				'BODY_TYPE' => 'html',
				'EMAIL_FROM' => $this->getCleanMailAddress($message->getConfiguration()->get('EMAIL_FROM')),
				'EMAIL_TO' => '#EMAIL_TO#',
				'PRIORITY' => $message->getConfiguration()->get('PRIORITY'),
				'SUBJECT' => $message->getConfiguration()->get('SUBJECT'),
				'MESSAGE' => $message->getConfiguration()->get('BODY'),
				'MESSAGE_PHP' => $message->getConfiguration()->get('BODY_PHP'),
				'FILE' => $messageAttachment
			),
			'SITE' => $message->getSiteId(),
			'CHARSET' => $message->getCharset(),
		);
		$mailMessage = Mail\EventMessageCompiler::createInstance($mailMessageParams);
		$mailMessage->compile();

		if (is_array($mailMessage->getMailAttachment()))
		{
			$mailAttachment = array_merge($mailAttachment, $mailMessage->getMailAttachment());
		}

		$mailParams = array(
			'TO' => $mailMessage->getMailTo(),
			'SUBJECT' => $mailMessage->getMailSubject(),
			'BODY' => $mailMessage->getMailBody(),
			'HEADER' => $mailMessage->getMailHeaders() + $headers,
			'CHARSET' => $mailMessage->getMailCharset(),
			'CONTENT_TYPE' => $mailMessage->getMailContentType(),
			'MESSAGE_ID' => '',
			'ATTACHMENT' => $mailAttachment,
			'LINK_PROTOCOL' => $this->getSenderLinkProtocol(),
			'LINK_DOMAIN' => $message->getSiteServerName(),
			'TRACK_READ' => $this->canTrackMails() ? $message->getReadTracker()->getArray() : null,
			'TRACK_CLICK' => $this->canTrackMails() ? $message->getClickTracker()->getArray() : null,
			'CONTEXT' => $this->getMailContext(),
		);
		$linkDomain = $message->getReadTracker()->getLinkDomain();
		if ($linkDomain)
		{
			$mailParams['LINK_DOMAIN'] = $linkDomain;
		}

		// event on sending email
		$eventMailParams = $mailParams;
		$eventMailParams['MAILING_CHAIN_ID'] = $message->getConfiguration()->get('LETTER_ID');
		$event = new Main\Event('sender', 'OnPostingSendRecipientEmail', [$eventMailParams]);
		$event->send();
		foreach ($event->getResults() as $eventResult)
		{
			if($eventResult->getType() == Main\EventResult::ERROR)
			{
				return false;
			}

			if(is_array($eventResult->getParameters()))
			{
				$eventMailParams = array_merge($eventMailParams, $eventResult->getParameters());
			}
		}
		unset($eventMailParams['MAILING_CHAIN_ID']);
		$mailParams = $eventMailParams;

		return Mail\Mail::send($mailParams);
	}

	/**
	 * End.
	 *
	 * @return void
	 */
	public function end()
	{

	}

	/**
	 * Get send duration in seconds.
	 *
	 * @param Message\Adapter|null $message Message.
	 *
	 * @return float
	 */
	public function getDuration(Message\Adapter $message = null)
	{
		return 0.01;
	}

	/**
	 * Get limiters.
	 *
	 * @param Message\iBase $message Message.
	 * @return Transport\iLimiter[]
	 */
	public function getLimiters(Message\iBase $message = null)
	{
		return Integration\Bitrix24\Limitation\Limiter::getList();
	}

	protected function getSenderLinkProtocol()
	{
		$protocol = Option::get('sender', 'link_protocol', null);
		$protocol = $protocol ?: (Integration\Bitrix24\Service::isCloud() ? 'https' : 'http');
		return $protocol;
	}

	protected function canTrackMails()
	{
		return Option::get('sender', 'track_mails') === 'Y';
	}

	protected function getMailContext()
	{
		if (!$this->mailContext)
		{
			$this->mailContext = new Mail\Context();
			$this->mailContext->setCategory(Mail\Context::CAT_EXTERNAL);
			$this->mailContext->setPriority(Mail\Context::PRIORITY_LOW);
			if (Integration\Bitrix24\Service::isCloud())
			{
				$this->mailContext->setCallback(
					(new Mail\Callback\Config())->setModuleId('sender')
				);
			}
		}

		return $this->mailContext;
	}

	/**
	 * Get clean address.
	 *
	 * @param string $address Address.
	 * @return string|null
	 */
	protected function getCleanMailAddress($address)
	{
		if (!$this->mailAddress)
		{
			$this->mailAddress = new Mail\Address();
		}

		return $this->mailAddress->set($address)->get();
	}
}