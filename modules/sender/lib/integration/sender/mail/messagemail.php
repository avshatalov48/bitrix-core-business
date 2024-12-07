<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Sender\Mail;

use Bitrix\Fileman\Block;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\DOM\Document;
use Bitrix\Main\Web\DOM\StyleInliner;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Integration\Crm\Connectors\Helper;
use Bitrix\Sender\Message;
use Bitrix\Sender\Posting;
use Bitrix\Sender\PostingRecipientTable;
use Bitrix\Sender\Templates;
use Bitrix\Sender\Transport;
use Bitrix\Sender\Transport\TimeLimiter;

Loc::loadMessages(__FILE__);

/**
 * Class MessageMail
 * @package Bitrix\Sender\Integration\Sender\Mail
 */
class MessageMail implements Message\iBase, Message\iMailable
{
	const CODE = self::CODE_MAIL;

	/** @var Message\Configuration $configuration Configuration. */
	protected $configuration;

	/** @var integer $configurationId Configuration ID. */
	protected $configurationId;

	protected $closureRefCountFix;

	/**
	 * MessageMail constructor.
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
		return Loc::getMessage('SENDER_INTEGRATION_MAIL_MESSAGE_NAME');
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
	 * Get supported transports.
	 *
	 * @return array
	 */
	public function getSupportedTransports()
	{
		return array(Transport\Adapter::CODE_MAIL);
	}

	/**
	 * Set configuration options
	 * @return void
	 */
	protected function setConfigurationOptions()
	{
		if ($this->configuration->hasOptions())
		{
			return;
		}

		$this->configuration->setArrayOptions(array(
			array(
				'type' => 'string',
				'code' => 'SUBJECT',
				'name' => Loc::getMessage('SENDER_INTEGRATION_MAIL_MESSAGE_CONFIG_SUBJECT'),
				'required' => true,
				'value' => '',
				'show_in_list' => true,
				'hint' => array(
					'menu' => array_map(
						function ($item)
						{
							return array(
								'id' => '#' . ($item['CODE'] ?? '') . '#',
								'text' => $item['NAME'] ?? '',
								'title' => $item['DESC'] ?? '',
								'items' => isset($item['ITEMS']) ? array_map(
									function ($item)
									{
										return array(
											'id' => '#' . ($item['CODE'] ?? ''). '#',
											'text' => $item['NAME'] ?? '',
											'title' => $item['DESC'] ?? ''
										);
									}, $item['ITEMS']
								) : []
							);
						},
						array_merge(
							Helper::getPersonalizeFieldsFromConnectors(),
							PostingRecipientTable::getPersonalizeList()
						)
					),
				),
			),
			array(
				'type' => 'email',
				'code' => 'EMAIL_FROM',
				'name' => Loc::getMessage('SENDER_INTEGRATION_MAIL_MESSAGE_CONFIG_EMAIL_FROM'),
				'required' => true,
				'value' => '',
				'show_in_list' => true,
				'readonly_view' => function($value)
				{
					return (new Mail\Address())->set($value)->get();
				},
				//'group' => Message\ConfigurationOption::GROUP_ADDITIONAL,
			),
			array(
				'type' => 'mail-editor',
				'code' => 'MESSAGE',
				'name' => Loc::getMessage('SENDER_INTEGRATION_MAIL_MESSAGE_CONFIG_MESSAGE'),
				'required' => true,
				'templated' => true,
				'value' => '',
				'items' => array(),
			),
			array(
				'type' => 'list',
				'code' => 'PRIORITY',
				'name' => Loc::getMessage('SENDER_INTEGRATION_MAIL_MESSAGE_CONFIG_PRIORITY'),
				'required' => false,
				'group' => Message\ConfigurationOption::GROUP_ADDITIONAL,
				'value' => '',
				'show_in_list' => true,
				'items' => array(
					array('code' => '', 'value' => '(' . Loc::getMessage('SENDER_INTEGRATION_MAIL_MESSAGE_NO') . ')'),
					array('code' => '1 (Highest)', 'value' => Loc::getMessage('SENDER_INTEGRATION_MAIL_MESSAGE_CONFIG_PRIORITY_HIGHEST')),
					array('code' => '3 (Normal)', 'value'  => Loc::getMessage('SENDER_INTEGRATION_MAIL_MESSAGE_CONFIG_PRIORITY_NORMAL')),
					array('code' => '5 (Lowest)', 'value'  => Loc::getMessage('SENDER_INTEGRATION_MAIL_MESSAGE_CONFIG_PRIORITY_LOWEST')),
				),
				'hint' => Loc::getMessage('SENDER_INTEGRATION_MAIL_MESSAGE_CONFIG_PRIORITY_HINT'),
			),
			array(
				'type' => 'string',
				'code' => 'LINK_PARAMS',
				'name' => Loc::getMessage('SENDER_INTEGRATION_MAIL_MESSAGE_CONFIG_LINK_PARAMS'),
				'required' => false,
				'group' => Message\ConfigurationOption::GROUP_ADDITIONAL,
				'value' => '',
				'show_in_list' => true,
				'items' => array(),
			),
			array(
				'type' => 'file',
				'code' => 'ATTACHMENT',
				'name' => Loc::getMessage('SENDER_INTEGRATION_MAIL_MESSAGE_CONFIG_ATTACHMENT'),
				'required' => false,
				'multiple' => true,
				'group' => Message\ConfigurationOption::GROUP_ADDITIONAL,
				'value' => '',
				'items' => array(),
			), [
				'type' => Message\ConfigurationOption::TYPE_CHECKBOX,
				'code' => 'TRACK_MAIL',
				'name' => Loc::getMessage('SENDER_INTEGRATION_MAIL_MESSAGE_CONFIG_TRACK_MAIL'),
				'group' => Message\ConfigurationOption::GROUP_ADDITIONAL,
				'show_in_list' => false,
				'required' => false,
				'value' => Option::get('sender', 'track_mails')
			],
			[
				'type' => Message\ConfigurationOption::TYPE_CONSENT,
				'code' => 'APPROVE_CONFIRMATION',
				'name' => Loc::getMessage('SENDER_INTEGRATION_MAIL_MESSAGE_CONFIG_APPROVE_CONFIRMATION'),
				'hint' => Loc::getMessage('SENDER_INTEGRATION_MAIL_MESSAGE_CONFIG_APPROVE_CONFIRMATION_HINT'),
				'group' => Message\ConfigurationOption::GROUP_ADDITIONAL,
				'show_in_list' => false,
				'required' => false,
				'value' => Option::get('sender', 'mail_consent'),
			], [
				'type' => Message\ConfigurationOption::TYPE_CONSENT_CONTENT,
				'code' => 'APPROVE_CONFIRMATION_CONSENT',
				'group' => Message\ConfigurationOption::GROUP_ADDITIONAL,
				'show_in_list' => false,
				'required' => false,
				'show_preview' => true
			],
		));

		TimeLimiter::prepareMessageConfiguration($this->configuration);

		$list = array(
			array(
				'type' => 'template-type',
				'code' => 'TEMPLATE_TYPE',
				'name' => 'Template type',
				'value' => '',
			),
			array(
				'type' => 'template-id',
				'code' => 'TEMPLATE_ID',
				'name' => 'Template id',
				'value' => '',
			),
		);

		foreach ($list as $optionData)
		{
			$optionData = $optionData + array(
				'type' => 'string',
				'name' => '',
				'internal' => true,
			);
			$this->configuration->addOption(new Message\ConfigurationOption($optionData));
		}
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
		$this->setConfigurationOptions();
		Entity\Message::create()
			->setCode($this->getCode())
			->loadConfiguration($id, $this->configuration);


		// do not remove: increment ref count to closure for rewriting.
		$instance = $this;
		$this->closureRefCountFix = function () use (&$instance)
		{
			return $instance->getMailBody();
		};

		$trackMail = $this->configuration->getOption('TRACK_MAIL')->getValue();
		if (is_null($trackMail))
		{
			$this->configuration->getOption('TRACK_MAIL')->setValue(Option::get('sender', 'track_mails'));
		}

		$optionLinkParams = $this->configuration->getOption('LINK_PARAMS');
		if ($optionLinkParams)
		{
			$optionLinkParams->setView(
				function () use ($id, $optionLinkParams)
				{
					ob_start();
					$GLOBALS['APPLICATION']->IncludeComponent(
						'bitrix:sender.mail.link.editor', '',
						array(
							"INPUT_NAME" => "%INPUT_NAME%",
							"VALUE" => $optionLinkParams->getValue(),
							"USE_DEFAULT" => (
								!$optionLinkParams->getValue()
								&&
								!$id
								&&
								!Application::getInstance()->getContext()->getRequest()->isPost()
							),
							"PLACEHOLDERS" => array(
								array(
									"code" => "campaign",
									"inputName" => "%INPUT_NAME_SUBJECT%"
								)
							),
							"DEFAULT_VALUE" => Option::get(
								'sender',
								'mail_utm',
								'utm_source=newsletter&utm_medium=mail&utm_campaign=%campaign%'
							)
						)
					);
					return ob_get_clean();
				}
			);
		}

		$optionFrom = $this->configuration->getOption('EMAIL_FROM');
		if ($optionFrom)
		{
			$optionFrom->setView(
				function () use ($optionFrom)
				{
					ob_start();
					$GLOBALS['APPLICATION']->IncludeComponent(
						'bitrix:sender.mail.sender', '',
						array(
							"INPUT_NAME" => "%INPUT_NAME%",
							"VALUE" => $optionFrom->getValue()
						)
					);
					return ob_get_clean();
				}
			);
		}

		$this->configuration->set('BODY', $this->closureRefCountFix);

		$mailHeaders = array('Precedence' => 'bulk');
		$mailHeaders = self::fillHeadersByOptionHeaders($mailHeaders);
		$this->configuration->set('HEADERS', $mailHeaders);
		TimeLimiter::prepareMessageConfigurationView($this->configuration);

		return $this->configuration;
	}

	/**
	 * Save configuration.
	 *
	 * @param Message\Configuration $configuration Configuration.
	 * @return Result|null
	 */
	public function saveConfiguration(Message\Configuration $configuration)
	{
		$this->configuration = $configuration;

		try
		{
			$mailBody = $this->getMailBody();
		}
		catch (SystemException $exception)
		{
			$result = new Result();
			$result->addError(new Error($exception->getMessage()));

			return $result;
		}

		$sizeInBytes = mb_strlen($mailBody);
		$sizeInKilobytes = $sizeInBytes / 1024;
		$limitInKilobytes = 2.4 * 1024;
		$saveAsTemplate = $this->configuration->get('save_as_template') === 'Y';

		if ($saveAsTemplate && $sizeInKilobytes > $limitInKilobytes)
		{
			$result = new Result();

			$result->addError(new Error(Loc::getMessage('SENDER_INTEGRATION_MAIL_BODY_LIMIT')));
			return $result;
		}

		if (Integration\Bitrix24\Service::isCloud())
		{
			if ($mailBody && mb_strpos($mailBody, '#UNSUBSCRIBE_LINK#') === false)
			{
				$result = new Result();
				$result->addError(new Error(Loc::getMessage('SENDER_INTEGRATION_MAIL_MESSAGE_ERR_NO_UNSUB_LINK')));
			}

			if (
				$mailBody
				&& $configuration->getOption('APPROVE_CONFIRMATION')->getValue() === 'Y'
				&& !$configuration->getOption('APPROVE_CONFIRMATION_CONSENT')->getValue()
			)
			{
				$result = new Result();
				$result->addError(new Error(Loc::getMessage('SENDER_INTEGRATION_MAIL_MESSAGE_ERR_NO_APPROVE_CONFIRMATION_CONSENT')));

				return $result;
			}
		}
		parse_str(
			$this->configuration->getOption('LINK_PARAMS')->getValue(),
			$utmTags
		);

		$utm = [];
		foreach ($utmTags as $utmTag => $value)
		{
			$utm[] = [
				'CODE' => $utmTag,
				'VALUE' => $value
			];
		}

		//TODO: compare with allowed email list
		$emailFrom = $this->configuration->getOption('EMAIL_FROM')->getValue();
		$emailFrom = (new Mail\Address($emailFrom))->get();
		$this->configuration->getOption('EMAIL_FROM')->setValue($emailFrom);

		$trackMail = $this->configuration->getOption('TRACK_MAIL')->getValue();

		if (!$trackMail)
		{
			$this->configuration->getOption('TRACK_MAIL')->setValue('N');
		}
		return Entity\Message::create()
			->setCode($this->getCode())
			->setUtm($utm)
			->saveConfiguration($this->configuration);
	}

	/**
	 * Copy configuration.
	 *
	 * @param integer|string|null $id ID.
	 * @return Result|null
	 */
	public function copyConfiguration($id)
	{
		return Entity\Message::create()
			->setCode($this->getCode())
			->copyConfiguration($id);
	}

	/**
	 * Remove php code from html
	 * @param string $html Input html.
	 * @return string
	 */
	protected function removePhp($html)
	{
		static $isCloud = null;
		if ($isCloud === null)
		{
			$isCloud = Integration\Bitrix24\Service::isCloud();
			Loader::includeModule('fileman');
		}

		if ($isCloud)
		{
			return Block\EditorMail::removePhpFromHtml($html);
		}

		return $html;
	}

	/**
	 * Get main body.
	 *
	 * @return string
	 */
	public function getMailBody()
	{
		Loader::includeModule('fileman');

		$msg = $this->configuration->getOption('MESSAGE')->getValue();
		$template = $this->getTemplate();
		if (!$template)
		{
			return $this->removePhp($msg);
		}
		if (!isset($template['FIELDS']) || !$template['FIELDS']['MESSAGE']['ON_DEMAND'])
		{
			return $this->removePhp($msg);
		}

		$templateHtml = null;
		if (isset($template['FIELDS']) && isset($template['FIELDS']['MESSAGE']))
		{
			$templateHtml = $template['FIELDS']['MESSAGE']['VALUE'];
		}
		if (!$templateHtml && isset($template['HTML']))
		{
			$templateHtml = $template['HTML'];
		}
		if (!$templateHtml)
		{
			return $this->removePhp($msg);
		}

		$document = new Document;
		$document->loadHTML($templateHtml);

		try
		{
			if(!Block\Content\Engine::create($document)->setContent($msg)->fill())
			{
				return '';
			}
		}
		catch (SystemException $exception)
		{
			throw new Posting\StopException();
		}

		StyleInliner::inlineDocument($document);
		$msg = $document->saveHTML();
		unset($document);

		$msg = $this->removePhp($msg);
		$msgTmp = Block\Editor::replaceCharset($msg, '#SENDER_MAIL_CHARSET#', true);
		if ($msgTmp)
		{
			$msg = $msgTmp;
		}

		if (Option::get('sender', 'use_inliner_for_each_template_mail', 'N') != 'Y')
		{
			$this->configuration->set('BODY', $msg);
		}

		return $msg;
	}

	/**
	 * Get template
	 * @return array|null
	 */
	protected function getTemplate()
	{
		if (!$this->configuration->get('TEMPLATE_TYPE') || !$this->configuration->get('TEMPLATE_ID'))
		{
			return null;
		}

		return Templates\Selector::create()
			->withMessageCode(static::CODE)
			->withTypeId($this->configuration->get('TEMPLATE_TYPE'))
			->withId($this->configuration->get('TEMPLATE_ID'))
			->get();
	}

	/**
	 * Add option headers to headers
	 * @param array $headers Headers.
	 * @return array
	 */
	protected static function fillHeadersByOptionHeaders(array $headers = array())
	{
		static $headerList = null;
		if ($headerList === null)
		{
			$headerList = array();
			// add headers from module options
			$optionHeaders = Option::get('sender', 'mail_headers', '');
			$optionHeaders = !empty($optionHeaders) ? unserialize($optionHeaders, ['allowed_classes' => false]) : array();
			foreach ($optionHeaders as $optionHeader)
			{
				$optionHeader = trim($optionHeader);
				if (!$optionHeader)
				{
					continue;
				}

				$optionHeaderParts = explode(':', $optionHeader);
				$optionHeaderName = isset($optionHeaderParts[0]) ? $optionHeaderParts[0] : '';
				$optionHeaderName = trim($optionHeaderName);
				$optionHeaderValue = isset($optionHeaderParts[1]) ? $optionHeaderParts[1] : '';
				$optionHeaderValue = trim($optionHeaderValue);
				if (!$optionHeaderName || !$optionHeaderValue)
				{
					continue;
				}

				$headerList[$optionHeaderName] = $optionHeaderValue;
			}
		}

		foreach ($headerList as $optionHeaderName => $optionHeaderValue)
		{
			$headers[$optionHeaderName] = $optionHeaderValue;
		}

		return $headers;
	}
}