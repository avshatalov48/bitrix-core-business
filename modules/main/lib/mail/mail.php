<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\Mail;

use Bitrix\Main\Config as Config;
use Bitrix\Main\IO\File;
use Bitrix\Main\Application;
use Bitrix\Main\Web\Uri;

class Mail
{
	protected $settingServerMsSmtp;
	protected $settingMailFillToEmail;
	protected $settingMailConvertMailHeader;
	protected $settingMailAddMessageId;
	protected $settingConvertNewLineUnixToWindows;
	protected $settingMailAdditionalParameters;
	protected $settingMaxFileSize;
	protected $settingAttachImages;
	protected $settingServerName;
	protected $settingMailEncodeBase64;

	protected $eol;
	protected $attachment;
	protected $generateTextVersion;
	protected $charset;
	protected $contentType;
	protected $messageId;
	protected $filesReplacedFromBody;
	protected $trackLinkProtocol;
	protected $trackReadLink;
	protected $trackClickLink;
	protected $trackClickUrlParams;
	protected $bitrixDirectory;

	protected $contentTransferEncoding = '8bit';
	protected $to;
	protected $subject;
	protected $headers = [];
	protected $body;
	protected $additionalParameters;
	/** @var  Context */
	protected $context;
	/** @var  Multipart */
	protected $multipart;
	/** @var  array */
	protected $blacklistedEmails = [];
	/** @var  array */
	protected $blacklistCheckedEmails = [];
	/** @var  bool */
	protected $useBlacklist = true;
	/** @var array  */
	protected static $emailHeaders = ['to', 'cc', 'bcc'];

	/**
	 * Mail constructor.
	 *
	 * @param array $mailParams Mail parameters.
	 */
	public function __construct(array $mailParams)
	{
		if(array_key_exists('LINK_PROTOCOL', $mailParams) && strlen($mailParams['LINK_PROTOCOL']) > 0)
		{
			$this->trackLinkProtocol = $mailParams['LINK_PROTOCOL'];
		}

		if(array_key_exists('TRACK_READ', $mailParams) && !empty($mailParams['TRACK_READ']))
		{
			$this->trackReadLink = Tracking::getLinkRead(
				$mailParams['TRACK_READ']['MODULE_ID'],
				$mailParams['TRACK_READ']['FIELDS'],
				isset($mailParams['TRACK_READ']['URL_PAGE']) ? $mailParams['TRACK_READ']['URL_PAGE'] : null
			);
		}
		if(array_key_exists('TRACK_CLICK', $mailParams) && !empty($mailParams['TRACK_CLICK']))
		{
			$this->trackClickLink = Tracking::getLinkClick(
				$mailParams['TRACK_CLICK']['MODULE_ID'],
				$mailParams['TRACK_CLICK']['FIELDS'],
				isset($mailParams['TRACK_CLICK']['URL_PAGE']) ? $mailParams['TRACK_CLICK']['URL_PAGE'] : null
			);
			if(!empty($mailParams['TRACK_CLICK']['URL_PARAMS']))
			{
				$this->trackClickUrlParams = $mailParams['TRACK_CLICK']['URL_PARAMS'];
			}
		}

		if(array_key_exists('LINK_DOMAIN', $mailParams) && strlen($mailParams['LINK_DOMAIN']) > 0)
		{
			$this->settingServerName = $mailParams['LINK_DOMAIN'];
		}

		$this->charset = $mailParams['CHARSET'];
		$this->contentType = $mailParams['CONTENT_TYPE'];
		$this->messageId = $mailParams['MESSAGE_ID'];
		$this->eol = $this->getMailEol();

		$this->attachment = (isset($mailParams['ATTACHMENT']) ? $mailParams['ATTACHMENT'] : array());
		if (isset($mailParams['USE_BLACKLIST']))
		{
			$this->useBlacklist = (bool) $mailParams['USE_BLACKLIST'];
		}

		$this->initSettings();

		if (isset($mailParams['GENERATE_TEXT_VERSION']))
		{
			$this->generateTextVersion = (bool) $mailParams['GENERATE_TEXT_VERSION'];
		}
		$this->multipart = (new Multipart())->setContentType(Multipart::MIXED)->setEol($this->eol);

		$this->setTo($mailParams['TO']);
		$this->setSubject($mailParams['SUBJECT']);
		$this->setBody($mailParams['BODY']);
		$this->setHeaders($mailParams['HEADER']);
		$this->setAdditionalParameters();

		if(array_key_exists('CONTEXT', $mailParams) && is_object($mailParams['CONTEXT']))
		{
			$this->context = $mailParams['CONTEXT'];
		}
	}

	/**
	 * Create instance.
	 *
	 * @param array $mailParams Mail parameters.
	 * @return static
	 */
	public static function createInstance(array $mailParams)
	{
		return new static($mailParams);
	}

	/**
	 * Send email.
	 *
	 * @param array $mailParams Mail parameters.
	 * @return bool
	 */
	public static function send($mailParams)
	{
		$result = false;

		$event = new \Bitrix\Main\Event("main", "OnBeforeMailSend", array($mailParams));
		$event->send();
		foreach ($event->getResults() as $eventResult)
		{
			if($eventResult->getType() == \Bitrix\Main\EventResult::ERROR)
				return false;

			$mailParams = array_merge($mailParams, $eventResult->getParameters());
		}

		if(defined("ONLY_EMAIL") && $mailParams['TO'] != ONLY_EMAIL)
		{
			$result = true;
		}
		else
		{
			$mail = static::createInstance($mailParams);
			if ($mail->canSend())
			{
				$mailResult = bxmail(
					$mail->getTo(),
					$mail->getSubject(),
					$mail->getBody(),
					$mail->getHeaders(),
					$mail->getAdditionalParameters(),
					$mail->getContext()
				);

				if($mailResult)
				{
					$result = true;
				}
			}
		}

		return $result;
	}

	/**
	 * Return true if mail can be sent.
	 *
	 * @return bool
	 */
	public function canSend()
	{
		$pseudoHeaders = ['To' => $this->to];
		$this->filterHeaderEmails($pseudoHeaders);

		return !$this->useBlacklist || !empty($pseudoHeaders);
	}

	/**
	 * Init settings.
	 *
	 * @return void
	 */
	public function initSettings()
	{
		if(defined("BX_MS_SMTP") && BX_MS_SMTP===true)
		{
			$this->settingServerMsSmtp = true;
		}

		if(Config\Option::get("main", "fill_to_mail", "N")=="Y")
		{
			$this->settingMailFillToEmail = true;
		}
		if(Config\Option::get("main", "convert_mail_header", "Y")=="Y")
		{
			$this->settingMailConvertMailHeader = true;
		}
		if(Config\Option::get("main", "send_mid", "N")=="Y")
		{
			$this->settingMailAddMessageId = true;
		}
		if(Config\Option::get("main", "CONVERT_UNIX_NEWLINE_2_WINDOWS", "N")=="Y")
		{
			$this->settingConvertNewLineUnixToWindows = true;
		}
		if(Config\Option::get("main", "attach_images", "N")=="Y")
		{
			$this->settingAttachImages = true;
		}
		if(Config\Option::get("main", "mail_encode_base64", "N") == "Y")
		{
			$this->settingMailEncodeBase64 = true;
		}

		if(!isset($this->settingServerName) || strlen($this->settingServerName) <= 0)
		{
			$this->settingServerName = Config\Option::get("main", "server_name", "");
		}

		if (!$this->trackLinkProtocol)
		{
			$this->trackLinkProtocol = Config\Option::get("main", "mail_link_protocol") ?: "http";
		}

		$this->generateTextVersion = Config\Option::get("main", "mail_gen_text_version", "Y") === 'Y';

		$this->settingMaxFileSize = intval(Config\Option::get("main", "max_file_size"));

		$this->settingMailAdditionalParameters = Config\Option::get("main", "mail_additional_parameters", "");

		$this->bitrixDirectory = Application::getInstance()->getPersonalRoot();
	}

	/**
	 * Set additional parameters.
	 *
	 * @param string $additionalParameters Additional parameters.
	 * @return void
	 */
	public function setAdditionalParameters($additionalParameters = '')
	{
		$this->additionalParameters = ($additionalParameters ? $additionalParameters : $this->settingMailAdditionalParameters);
	}


	/**
	 * Set body.
	 *
	 * @param string $bodyPart Html or text of body.
	 * @return void
	 */
	public function setBody($bodyPart)
	{
		$charset = $this->charset;
		$messageId = $this->messageId;

		$htmlPart = null;
		$plainPart = new Part();
		$plainPart->addHeader('Content-Type', 'text/plain; charset=' . $charset);

		if($this->contentType == "html")
		{
			$bodyPart = $this->replaceImages($bodyPart);
			$bodyPart = $this->replaceHrefs($bodyPart);
			$bodyPart = $this->trackRead($bodyPart);
			$bodyPart = $this->addMessageIdToBody($bodyPart, true, $messageId);

			$htmlPart = new Part();
			$htmlPart->addHeader('Content-Type', 'text/html; charset=' . $charset);
			$htmlPart->setBody($bodyPart);
			$plainPart->setBody($this->convertBodyHtmlToText($bodyPart));
		}
		else
		{
			$bodyPart = $this->addMessageIdToBody($bodyPart, false, $messageId);
			$plainPart->setBody($bodyPart);
		}

		$cteName = 'Content-Transfer-Encoding';
		$cteValue = $this->settingMailEncodeBase64 ? 'base64' : $this->contentTransferEncoding;
		$this->multipart->addHeader($cteName, $cteValue);
		$plainPart->addHeader($cteName, $cteValue);
		if ($htmlPart)
		{
			$htmlPart->addHeader($cteName, $cteValue);
		}


		if ($htmlPart)
		{
			if ($this->generateTextVersion)
			{
				$alternative = (new Multipart())->setContentType(Multipart::ALTERNATIVE)->setEol($this->eol);
				$alternative->addPart($plainPart);
				$alternative->addPart($htmlPart);
				$this->multipart->addPart($alternative);
			}
			else
			{
				$this->multipart->addPart($htmlPart);
			}
		}
		else
		{
			$this->multipart->addPart($plainPart);
		}

		$this->setAttachment();

		$body = $this->multipart->toStringBody();
		$body = str_replace("\r\n", "\n", $body);
		if($this->settingConvertNewLineUnixToWindows)
		{
			$body = str_replace("\n", "\r\n", $body);
		}
		$this->body = $body;
	}

	/**
	 * Return true if mail has attachment.
	 *
	 * @return bool
	 */
	public function hasAttachment()
	{
		return !empty($this->attachment) || !empty($this->filesReplacedFromBody);
	}

	/**
	 * Set attachment.
	 *
	 * @return void
	 */
	public function setAttachment()
	{
		$files = $this->attachment;
		if(is_array($this->filesReplacedFromBody))
		{
			$files = array_merge($files, array_values($this->filesReplacedFromBody));
		}

		if(count($files)>0)
		{
			foreach($files as $attachment)
			{
				try
				{
					$fileContent = File::getFileContents($attachment["PATH"]);
				}
				catch (\Exception $exception)
				{
					$fileContent = '';
				}

				$name = $this->encodeSubject($attachment["NAME"], $this->charset);
				$part = (new Part())
					->addHeader('Content-Type', $attachment['CONTENT_TYPE'] . "; name=\"$name\"")
					->addHeader('Content-Disposition', "attachment; filename=\"$name\"")
					->addHeader('Content-Transfer-Encoding', 'base64')
					->addHeader('Content-ID', "<{$attachment['ID']}>")
					->setBody($fileContent);
				$this->multipart->addPart($part);
			}
		}
	}

	/**
	 * Set headers.
	 *
	 * @param array $headers Headers.
	 * @return $this
	 */
	public function setHeaders(array $headers)
	{
		$this->headers = $headers;
		return $this;
	}

	/**
	 * Set subject.
	 *
	 * @param string $subject Subject.
	 * @return $this
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;
		return $this;
	}

	/**
	 * Set to.
	 *
	 * @param string $to To.
	 * @return $this
	 */
	public function setTo($to)
	{
		$this->to = $to ? trim($to) : null;
		return $this;
	}

	/**
	 * Get body.
	 *
	 * @return string
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Get headers.
	 *
	 * @return string
	 */
	public function getHeaders()
	{
		$headers = $this->headers;

		foreach($headers as $k=>$v)
		{
			$headers[$k] = trim($v, "\r\n");
			if($headers[$k] == '')
			{
				unset($headers[$k]);
			}
		}

		$this->filterHeaderEmails($headers);

		if($headers["Reply-To"] == '' && $headers["From"] <> '')
		{
			$headers["Reply-To"] = preg_replace("/(.*)\\<(.*)\\>/i", '$2', $headers["From"]);
		}

		if($headers["X-Priority"] == '')
		{
			$headers["X-Priority"] = '3 (Normal)';
		}

		if($headers["Date"] == '')
		{
			$headers["Date"] = date("r");
		}

		if(empty($headers["MIME-Version"]))
		{
			$headers["MIME-Version"] = '1.0';
		}

		if($this->settingMailConvertMailHeader)
		{
			foreach($headers as $k => $v)
			{
				if ($k == 'From' || $k == 'CC' || $k == 'Reply-To')
				{
					$headers[$k] = $this->encodeHeaderFrom($v, $this->charset);
				}
				else
				{
					$headers[$k] = $this->encodeMimeString($v, $this->charset);
				}
			}
		}

		if($this->settingServerMsSmtp)
		{
			if($headers["From"] != '')
			{
				$headers["From"] = preg_replace("/(.*)\\<(.*)\\>/i", '$2', $headers["From"]);
			}

			if($headers["To"] != '')
			{
				$headers["To"] = preg_replace("/(.*)\\<(.*)\\>/i", '$2', $headers["To"]);
			}

			if($headers["Reply-To"] != '')
			{
				$headers["Reply-To"] = preg_replace("/(.*)\\<(.*)\\>/i", '$2', $headers["Reply-To"]);
			}
		}

		if($this->settingMailFillToEmail)
		{
			$headers["To"] = $this->getTo();
		}

		if($this->messageId != '')
		{
			$headers['X-MID'] = $this->messageId;
		}


		$headerString = "";
		foreach($headers as $k=>$v)
		{
			$headerString .= $k . ': ' . $v . $this->eol;
		}
		// Content-Transfer-Encoding & Content-Type add from Multipart
		$headerString .= rtrim($this->multipart->toStringHeaders());

		return $headerString;
	}

	/**
	 * Get message ID.
	 *
	 * @return string
	 */
	public function getMessageId()
	{
		return $this->messageId;
	}

	/**
	 * Get subject.
	 *
	 * @return string
	 */
	public function getSubject()
	{
		if($this->settingMailConvertMailHeader)
		{
			return $this->encodeSubject($this->subject, $this->charset);
		}

		return $this->subject;
	}

	/**
	 * Get to.
	 *
	 * @return string
	 */
	public function getTo()
	{
		$resultTo = $this->to;

		if($this->settingMailConvertMailHeader)
		{
			$resultTo = $this->encodeHeaderFrom($resultTo, $this->charset);
		}

		if($this->settingServerMsSmtp)
		{
			$resultTo = preg_replace("/(.*)\\<(.*)\\>/i", '$2', $resultTo);
		}

		return $resultTo;
	}

	/**
	 * Get additional parameters.
	 *
	 * @return mixed
	 */
	public function getAdditionalParameters()
	{
		return $this->additionalParameters;
	}

	/**
	 * Get context instance.
	 *
	 * @return Context|null
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * Dump email data.
	 *
	 * @return string
	 */
	public function dump()
	{
		$result = '';
		$delimeter = str_repeat('-',5);

		$result .= $delimeter."TO".$delimeter."\n".$this->getTo()."\n\n";
		$result .= $delimeter."SUBJECT".$delimeter."\n".$this->getSubject()."\n\n";
		$result .= $delimeter."HEADERS".$delimeter."\n".$this->getHeaders()."\n\n";
		$result .= $delimeter."BODY".$delimeter."\n".$this->getBody()."\n\n";
		$result .= $delimeter."ADDITIONAL PARAMETERS".$delimeter."\n".$this->getAdditionalParameters()."\n\n";

		return $result;
	}


	/**
	 * Return true if input string is in 8bit charset.
	 *
	 * @param string $inputString Input string.
	 * @return bool
	 */
	public static function is8Bit($inputString)
	{
		return preg_match("/[\\x80-\\xFF]/", $inputString) > 0;
	}

	/**
	 * Encode mime string.
	 *
	 * @param string $text Text string.
	 * @param string $charset Charset.
	 * @return string
	 */
	public static function encodeMimeString($text, $charset)
	{
		if(!static::is8Bit($text))
			return $text;

		//$maxl = IntVal((76 - strlen($charset) + 7)*0.4);
		$res = "";
		$maxl = 40;
		$eol = static::getMailEol();
		$len = strlen($text);
		for($i=0; $i<$len; $i=$i+$maxl)
		{
			if($i>0)
				$res .= $eol."\t";
			$res .= "=?".$charset."?B?".base64_encode(substr($text, $i, $maxl))."?=";
		}
		return $res;
	}

	/**
	 * Encode subject.
	 *
	 * @param string $text Text string.
	 * @param string $charset Charset.
	 * @return string
	 */
	public static function encodeSubject($text, $charset)
	{
		return "=?".$charset."?B?".base64_encode($text)."?=";
	}

	/**
	 * Encode header From.
	 *
	 * @param string $text Text string.
	 * @param string $charset Charset.
	 * @return string
	 */
	public static function encodeHeaderFrom($text, $charset)
	{
		$i = strlen($text);
		while($i > 0)
		{
			if(ord(substr($text, $i-1, 1))>>7)
				break;
			$i--;
		}
		if($i==0)
			return $text;
		else
			return "=?".$charset."?B?".base64_encode(substr($text, 0, $i))."?=".substr($text, $i);
	}

	/**
	 * Get symbol of mail End-Of-Line.
	 *
	 * @return string
	 */
	public static function getMailEol()
	{
		static $eol = false;
		if($eol !== false)
			return $eol;

		if(strtoupper(substr(PHP_OS,0,3)) == 'WIN')
			$eol="\r\n";
		elseif(strtoupper(substr(PHP_OS,0,3)) <> 'MAC')
			$eol="\n"; 	 //unix
		else
			$eol="\r";

		return $eol;
	}


	/**
	 * @param $matches
	 * @return string
	 * @throws \Bitrix\Main\IO\FileNotFoundException
	 */
	protected function getReplacedImageCid($matches)
	{
		$src = $matches[3];

		if($src == "")
		{
			return $matches[0];
		}

		if(array_key_exists($src, $this->filesReplacedFromBody))
		{
			$uid = $this->filesReplacedFromBody[$src]["ID"];
			return $matches[1].$matches[2]."cid:".$uid.$matches[4].$matches[5];
		}

		$uri = new Uri($src);
		$filePath = Application::getDocumentRoot() . $uri->getPath();
		$io = \CBXVirtualIo::GetInstance();
		$filePath = $io->GetPhysicalName($filePath);
		if(!File::isFileExists($filePath))
		{
			return $matches[0];
		}

		foreach($this->attachment as $attach)
		{
			if($filePath == $attach['PATH'])
			{
				return $matches[1].$matches[2]."cid:".$attach['ID'].$matches[4].$matches[5];
			}
		}

		if ($this->settingMaxFileSize > 0)
		{
			$fileIoObject = new File($filePath);
			if ($fileIoObject->getSize() > $this->settingMaxFileSize)
			{
				return $matches[0];
			}
		}


		$imageSize = \CFile::GetImageSize($filePath, true);
		if (!is_array($imageSize))
		{
			return $matches[0];
		}

		if (function_exists("image_type_to_mime_type"))
		{
			$contentType = image_type_to_mime_type($imageSize[2]);
		}
		else
		{
			$contentType = $this->imageTypeToMimeType($imageSize[2]);
		}

		$uid = uniqid(md5($src));

		$this->filesReplacedFromBody[$src] = array(
			"SRC" => $src,
			"PATH" => $filePath,
			"CONTENT_TYPE" => $contentType,
			"NAME" => bx_basename($src),
			"ID" => $uid,
		);

		return $matches[1].$matches[2]."cid:".$uid.$matches[4].$matches[5];
	}

	/**
	 * @param $matches
	 * @return string
	 */
	protected function getReplacedImageSrc($matches)
	{
		$src = $matches[3];
		if($src == "")
		{
			return $matches[0];
		}

		$srcTrimmed = trim($src);
		if(substr($srcTrimmed,0, 2) == "//")
		{
			$src = $this->trackLinkProtocol . ":" . $srcTrimmed;
		}
		else if(substr($srcTrimmed,0, 1) == "/")
		{
			$srcModified = false;
			if(count($this->attachment)>0)
			{
				$io = \CBXVirtualIo::GetInstance();
				$filePath = $io->GetPhysicalName(Application::getDocumentRoot().$srcTrimmed);
				foreach($this->attachment as $attach)
				{
					if($filePath == $attach['PATH'])
					{
						$src = "cid:".$attach['ID'];
						$srcModified = true;
						break;
					}
				}
			}

			if(!$srcModified)
			{
				$src = $this->trackLinkProtocol . "://".$this->settingServerName . $srcTrimmed;
			}
		}

		$add = '';
		if (stripos($matches[0], '<img') === 0 && !preg_match("/<img[^>]*?\\s+alt\\s*=[^>]+>/is", $matches[0]))
		{
			$add = ' alt="" ';
		}

		return $matches[1] . $matches[2] . $src . $matches[4] . $add . $matches[5];
	}

	/**
	 * Replace images.
	 * All src of images in html will be added by protocol and domain.
	 *
	 * @param string $text Html text.
	 * @return string
	 */
	public function replaceImages($text)
	{
		$replaceImageFunction = 'getReplacedImageSrc';
		if($this->settingAttachImages)
			$replaceImageFunction = 'getReplacedImageCid';

		$this->filesReplacedFromBody = array();
		$textReplaced = preg_replace_callback(
			"/(<img\\s[^>]*?(?<=\\s)src\\s*=\\s*)([\"']?)(.*?)(\\2)(\\s.+?>|\\s*>)/is",
			array($this, $replaceImageFunction),
			$text
		);
		if($textReplaced !== null) $text = $textReplaced;

		$textReplaced = preg_replace_callback(
			"/(background|background-image\\s*:\\s*url\\s*\\()([\"']?)(.*?)(\\2)(\\s*\\)(.*?);)/is",
			array($this, $replaceImageFunction),
			$text
		);
		if($textReplaced !== null) $text = $textReplaced;

		$textReplaced = preg_replace_callback(
			"/(<td\\s[^>]*?(?<=\\s)background\\s*=\\s*)([\"']?)(.*?)(\\2)(\\s.+?>|\\s*>)/is",
			array($this, $replaceImageFunction),
			$text
		);
		if($textReplaced !== null) $text = $textReplaced;

		$textReplaced = preg_replace_callback(
			"/(<table\\s[^>]*?(?<=\\s)background\\s*=\\s*)([\"']?)(.*?)(\\2)(\\s.+?>|\\s*>)/is",
			array($this, $replaceImageFunction),
			$text
		);
		if($textReplaced !== null) $text = $textReplaced;

		return $text;
	}

	/**
	 * @param $html
	 * @return string
	 */
	private function trackRead($html)
	{
		if(!$this->trackReadLink)
		{
			return $html;
		}

		$url = $this->trackReadLink;
		if (substr($url, 0, 4) !== 'http')
		{
			$url = $this->trackLinkProtocol . "://" . $this->settingServerName . $url;
		}

		$html .= '<img src="' . $url . '" border="0" height="1" width="1" alt="" />';

		return $html;
	}

	/**
	 * Replace href attribute in links.
	 * All href of links in html will be added by protocol and domain.
	 *
	 * @param string $text Text.
	 * @return mixed
	 */
	public function replaceHrefs($text)
	{
		if($this->settingServerName != '')
		{
			$pcre_pattern = "/(<a\\s[^>]*?(?<=\\s)href\\s*=\\s*)([\"'])(\\/.*?|http:\\/\\/.*?|https:\\/\\/.*?)(\\2)(\\s.+?>|\\s*>)/is";
			$text = preg_replace_callback(
				$pcre_pattern,
				array($this, 'trackClick'),
				$text
			);
		}

		return $text;
	}

	/**
	 * Track click.
	 * All href of links in html will be wrapped by tracking url for click-detecting.
	 *
	 * @param array $matches Result of preg_match call.
	 * @return string
	 */
	public function trackClick($matches)
	{
		$href = $matches[3];
		if ($href == "")
		{
			return $matches[0];
		}

		if(substr($href, 0, 2) == '//')
		{
			$href = $this->trackLinkProtocol . ':' . $href;
		}

		if(substr($href, 0, 1) == '/')
		{
			$href = $this->trackLinkProtocol . '://' . $this->settingServerName . $href;
		}

		if($this->trackClickLink)
		{
			if($this->trackClickUrlParams)
			{
				$hrefAddParam = '';
				foreach($this->trackClickUrlParams as $k => $v)
					$hrefAddParam .= '&'.htmlspecialcharsbx($k).'='.htmlspecialcharsbx($v);

				$parsedHref = explode("#", $href);
				$parsedHref[0] .= (strpos($parsedHref[0], '?') === false ? '?' : '&') . substr($hrefAddParam, 1);
				$href = implode("#", $parsedHref);
			}

			$href = $this->trackClickLink . '&url=' . urlencode($href) . '&sign=' . urlencode(Tracking::getSign($href));
			if (!preg_match('/^http:\/\/|https:\/\//', $this->trackClickLink))
			{
				$href = $this->trackLinkProtocol . '://' . $this->settingServerName . $href;
			}
		}

		return $matches[1].$matches[2].$href.$matches[4].$matches[5];
	}

	/**
	 * @param $type
	 * @return string
	 */
	protected function imageTypeToMimeType($type)
	{
		$aTypes = array(
			1 => "image/gif",
			2 => "image/jpeg",
			3 => "image/png",
			4 => "application/x-shockwave-flash",
			5 => "image/psd",
			6 => "image/bmp",
			7 => "image/tiff",
			8 => "image/tiff",
			9 => "application/octet-stream",
			10 => "image/jp2",
			11 => "application/octet-stream",
			12 => "application/octet-stream",
			13 => "application/x-shockwave-flash",
			14 => "image/iff",
			15 => "image/vnd.wap.wbmp",
			16 => "image/xbm",
		);
		if(!empty($aTypes[$type]))
			return $aTypes[$type];
		else
			return "application/octet-stream";
	}

	protected function addMessageIdToBody($body, $isHtml, $messageId)
	{
		if($this->settingMailAddMessageId && !empty($messageId))
		{
			$body .= $isHtml ? "<br><br>" : "\n\n";
			$body .= "MID #" . $messageId . "\r\n";
		}

		return $body;
	}

	protected function convertBodyHtmlToText($body)
	{
		// get <body> inner html if exists
		$innerBody = trim(preg_replace('/(.*?<body[^>]*>)(.*?)(<\/body>.*)/is', '$2', $body));
		$body = $innerBody ?: $body;

		// modify links to text version
		$body = preg_replace_callback(
			"/<a\\s[^>]*?href=['|\\\"](.*?)['|\\\"][^>]*?>([^>]*?)<\\/a>/i",
			function ($matches)
			{
				$href = $matches[1];
				$text = trim($matches[2]);
				if (!$href)
				{
					return $matches[0];
				}

				return ($text ? "$text:" : '') ."\n$href\n";
			},
			$body
		);

		// change <br> to new line
		$body = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $body);

		// remove tags
		$body = strip_tags($body);

		// format text to the left side
		$lines = [];
		foreach (explode("\n", trim($body)) as $line)
		{
			$lines[] = trim($line);
		}

		// remove redundant new lines
		$body = preg_replace("/[\\n]{2,}/", "\n\n", implode("\n", $lines));

		// remove redundant spaces
		$body = preg_replace("/[ \\t]{2,}/", "  ", $body);

		// decode html-entities
		return html_entity_decode($body);
	}

	/**
	 * Filter header emails by blacklist.
	 *
	 * @param array &$headers Headers.
	 * return void
	 */
	protected function filterHeaderEmails(array &$headers)
	{
		if (!$this->useBlacklist || !Internal\BlacklistTable::hasBlacklistedEmails())
		{
			return;
		}

		$list = [];
		$allEmails = [strtolower($this->to)];

		// get all emails for query Blacklist, prepare emails as Address instances
		foreach ($headers as $name => $value)
		{
			// exclude non target headers
			if (!in_array(strtolower($name), static::$emailHeaders))
			{
				continue;
			}

			$list[$name] = [];
			$emails = explode(',', $value);
			foreach ($emails as $email)
			{
				$email = trim($email);
				if (!$email)
				{
					continue;
				}

				$address = new Address($email);
				$email = $address->getEmail();
				if ($email)
				{
					$list[$name][] = $address;
					$allEmails[] = $address->getEmail();
				}
			}
		}

		// get blacklisted emails from all emails
		$allEmails = array_diff($allEmails, $this->blacklistCheckedEmails);
		if (!empty($allEmails))
		{
			$blacklisted = Internal\BlacklistTable::getList([
				'select' => ['CODE'],
				'filter' => ['=CODE' => $allEmails]
			])->fetchAll();
			$blacklisted = array_column($blacklisted, 'CODE');

			$this->blacklistedEmails = array_unique(array_merge($this->blacklistedEmails, $blacklisted));
			$this->blacklistCheckedEmails = array_merge($this->blacklistCheckedEmails, $allEmails);
		}

		if (empty($this->blacklistedEmails))
		{
			return;
		}

		// remove blacklisted emails, remove empty headers
		$blacklisted = $this->blacklistedEmails;
		foreach ($headers as $name => $value)
		{
			// exclude non target headers
			if (!in_array(strtolower($name), static::$emailHeaders))
			{
				continue;
			}
			// filter Address instances by blacklist
			$emails = array_filter(
				$list[$name],
				function (Address $address) use ($blacklisted)
				{
					$email = $address->getEmail();
					return $email && !in_array($email, $blacklisted);
				}
			);
			// get emails from Address instances
			$emails = array_map(
				function (Address $address)
				{
					return $address->getName() ? $address->get() : $address->getEmail();
				},
				$emails
			);
			// get header emails as string
			$emails = implode(', ', $emails);
			// remove empty or update headers
			if (!$emails)
			{
				unset($headers[$name]);
			}
			else
			{
				$headers[$name] = $emails;
			}
		}
	}
}