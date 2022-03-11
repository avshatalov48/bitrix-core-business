<?

use Bitrix\Fileman;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Address;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Uri;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Access\Map\AdsAction;
use Bitrix\Sender\Access\Map\MailingAction;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Internals\PostFiles;
use Bitrix\Sender\Internals\SqlBatch;
use Bitrix\Sender\Message;
use Bitrix\Sender\Security;
use Bitrix\Sender\Templates;
use Bitrix\Sender\UI;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	ShowError('Module `sender` not installed');
	die();
}

Loc::loadMessages(__FILE__);

class SenderLetterEditComponent extends Bitrix\Sender\Internals\CommonSenderComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	/** @var Entity\Letter $letter Letter. */
	protected $letter;

	protected $contentValue = null;

	protected function checkRequiredParams()
	{
		return $this->errors->count() == 0;
	}

	protected function initParams()
	{
		$this->arParams['ID'] = isset($this->arParams['ID']) ? (int) $this->arParams['ID'] : 0;
		$this->arParams['ID'] = $this->arParams['ID'] ? $this->arParams['ID'] : (int) $this->request->get('ID');
		$this->arParams['IS_OUTSIDE'] = isset($this->arParams['IS_OUTSIDE']) ? (bool) $this->arParams['IS_OUTSIDE'] : $this->request->get('isOutside') === 'Y';

		$this->arParams['IFRAME'] = isset($this->arParams['IFRAME'])
			? ($this->arParams['IFRAME'] === true ? 'Y' : false)
			: false;

		if (empty($this->arParams['CAMPAIGN_ID']))
		{
			$this->arParams['CAMPAIGN_ID'] = (int) $this->request->get('CAMPAIGN_ID');
		}
		if (!isset($this->arParams['MESSAGE_CODE']))
		{
			$this->arParams['MESSAGE_CODE'] = $this->request->get('code');
		}
		if (!isset($this->arParams['MESSAGE_CODE_LIST']))
		{
			if ($this->arParams['MESSAGE_CODE'])
			{
				$this->arParams['MESSAGE_CODE_LIST'] = [$this->arParams['MESSAGE_CODE']];
			}
			else
			{
				$this->arParams['MESSAGE_CODE_LIST'] = Message\Factory::getMailingMessageCodes();
			}
		}
		if (!$this->arParams['MESSAGE_CODE'])
		{
			$this->arParams['MESSAGE_CODE'] = current($this->arParams['MESSAGE_CODE_LIST']);
		}

		if (!isset($this->arParams['IFRAME']) || !$this->arParams['IFRAME'])
		{
			$this->arParams['IFRAME'] = $this->request->get('IFRAME');
		}

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;
		$this->arParams['SHOW_SEGMENT_COUNTERS'] = isset($this->arParams['SHOW_SEGMENT_COUNTERS']) ? $this->arParams['SHOW_SEGMENT_COUNTERS'] : true;
		$this->arParams['CHECK_ON_STATIC'] = $this->arParams['CHECK_ON_STATIC'] ?? false;

		$map = MailingAction::getMap();
		$map = isset($map[$this->arParams['MESSAGE_CODE']]) ? $map : AdsAction::getMap();

		$this->arParams['CAN_EDIT'] = $this->arParams['CAN_EDIT']??
			$this->getAccessController()->check(
				$map[$this->arParams['MESSAGE_CODE']]
			);

		$this->arParams['CAN_VIEW'] = $this->arParams['CAN_VIEW']??
									$this->getAccessController()->check(
										ActionDictionary::ACTION_MAILING_VIEW
										);

		$this->arParams['IS_TRIGGER'] = isset($this->arParams['IS_TRIGGER']) ? (bool) $this->arParams['IS_TRIGGER'] : false;
		$this->arParams['SHOW_SEGMENTS'] = isset($this->arParams['SHOW_SEGMENTS']) ? (bool) $this->arParams['SHOW_SEGMENTS'] : true;
		$this->arParams['GOTO_URI_AFTER_SAVE'] = isset($this->arParams['GOTO_URI_AFTER_SAVE'])
			?
			$this->arParams['GOTO_URI_AFTER_SAVE']
			:
			$this->arParams['PATH_TO_TIME'];
		$this->arParams['SHOW_CAMPAIGNS'] = isset($this->arParams['SHOW_CAMPAIGNS'])
			?
			$this->arParams['SHOW_CAMPAIGNS']
			:
			Integration\Bitrix24\Service::isCampaignsAvailable();
	}

	protected function preparePostMessage()
	{
		$message = $this->letter->getMessage();
		$configuration = $message->getConfiguration();

		foreach ($configuration->getOptions() as $option)
		{
			$key = 'CONFIGURATION_' . $option->getCode();
			$value = $this->request->get($key);
			switch ($option->getType())
			{
				case Message\ConfigurationOption::TYPE_TITLE:
					$value = $this->request->get('TITLE');
					$configuration->set('TITLE', $value);
					break;
				case Message\ConfigurationOption::TYPE_TEMPLATE_TYPE:
					$value = $this->letter->get('TEMPLATE_TYPE');
					$configuration->set('TEMPLATE_TYPE', $value);
					break;
				case Message\ConfigurationOption::TYPE_TEMPLATE_ID:
					$value = $this->letter->get('TEMPLATE_ID');
					$configuration->set('TEMPLATE_ID', $value);
					break;
				case Message\ConfigurationOption::TYPE_FILE:
					$value = $option->getValue();
					if (!is_array($value))
					{
						$value = array();
					}
					$value = PostFiles::getFromContext($key, $value);
					break;
				case Message\ConfigurationOption::TYPE_MAIL_EDITOR:
					$value = Security\Sanitizer::fixReplacedStyles($value);
					$value = Security\Sanitizer::sanitizeHtml($value, $option->getValue());
					$this->contentValue = $value;
					break;
				case Message\ConfigurationOption::TYPE_USER_LIST:
					$value = array_filter(
						is_array($value) ? $value : [],
						function ($item)
						{
							return (is_numeric($item) && $item > 0);
						}
					);
					$value = implode(',', $value);
					break;
				case Message\ConfigurationOption::TYPE_AUDIO:
					$value = $message->getAudioValue($option->getCode(), $value);
					break;
				case Message\ConfigurationOption::TYPE_EMAIL:
					if (\Bitrix\Sender\Integration\Sender\AllowedSender::isAllowed($value))
					{
						$address = new Address();
						$address->set($value);
						$value = $address->get();
					}
					else
					{
						$value = "";
					}
					break;
			}
			$option->setValue($value);
		}

		$result = $configuration->checkOptions();
		if ($result->isSuccess())
		{
			$result = $message->saveConfiguration($configuration);
		}
		if ($result->isSuccess())
		{
			$this->letter->set('MESSAGE_ID', $configuration->getId());
		}

		$this->errors->add($result->getErrors());
	}

	protected function preparePostSegments($include = true)
	{
		$segments = $this->request->get('SEGMENT');
		if (!is_array($segments))
		{
			return array();
		}

		$key = $include ? 'INCLUDE' : 'EXCLUDE';
		if (!isset($segments[$key]) || !is_array($segments[$key]))
		{
			return array();
		}
		$segments = $segments[$key];

		$result = array();
		foreach ($segments as $segmentId)
		{
			$result[] = (int) $segmentId;
		}

		return $result;
	}

	protected function canSaveAsTemplate()
	{
		return $this->letter->getMessage()->getCode() === Message\iBase::CODE_MAIL;
	}

	protected function preparePostSaveAsTemplate()
	{
		if ($this->request->get('save_as_template') !== 'Y')
		{
			return;
		}

		if (!$this->canSaveAsTemplate())
		{
			return;
		}


		$templateType = $this->letter->get('TEMPLATE_TYPE');
		$templateId = $this->letter->get('TEMPLATE_ID');
		$message = $this->request->get('CONFIGURATION_MESSAGE');
		$name = $this->request->get('CONFIGURATION_SUBJECT') ?: $this->letter->get('TITLE');

		if (!$message)
		{
			return;
		}

		if ($templateType && $templateType)
		{
			$template = Templates\Selector::create()
				->withMessageCode($this->letter->getMessage()->getCode())
				->withTypeId($templateType)
				->withId($templateId)
				->get();

			if ($template && $template['FIELDS']['MESSAGE']['VALUE'])
			{
				$templateHtml = $template['FIELDS']['MESSAGE']['VALUE'];
				Loader::includeModule('fileman');
				if (Fileman\Block\Editor::isContentSupported($templateHtml))
				{
					$message = Fileman\Block\Content\Engine::fillHtmlTemplate($templateHtml, $message);
					if (!$message)
					{
						return;
					}
				}
			}
		}

		$addResult = \Bitrix\Sender\TemplateTable::add(array('NAME' => $name, 'CONTENT' => $message));
		if($addResult->isSuccess())
		{
			$templateType = Templates\Type::getCode(Templates\Type::USER);
			$templateId = $addResult->getId();
			$this->letter->set('TEMPLATE_TYPE', $templateType);
			$this->letter->set('TEMPLATE_ID', $templateId);
			$this->letter->getMessage()->getConfiguration()->set('TEMPLATE_TYPE', $templateType);
			$this->letter->getMessage()->getConfiguration()->set('TEMPLATE_ID', $templateId);
		}
	}

	protected function preparePost()
	{
		// prepare letter
		$data = array(
			'TITLE' => $this->request->get('TITLE'),
			'SEGMENTS_INCLUDE' => $this->preparePostSegments(true),
			'SEGMENTS_EXCLUDE' => $this->preparePostSegments(false),
			'TEMPLATE_TYPE' => $this->request->get('TEMPLATE_TYPE'),
			'TEMPLATE_ID' => $this->request->get('TEMPLATE_ID'),
			'IS_TRIGGER' => $this->arParams['IS_TRIGGER'] ? 'Y' : 'N',
			'UPDATED_BY' => Security\User::current()->getId()
		);

		if (!$this->letter->getId())
		{
			$data['CAMPAIGN_ID'] = $this->arParams['CAMPAIGN_ID'] ?: Entity\Campaign::getDefaultId(SITE_ID);
			$data['CREATED_BY'] = Security\User::current()->getId();
		}
		$this->letter->mergeData($data);

		// copy template
		if ($this->errors->isEmpty())
		{
			$this->preparePostSaveAsTemplate();
		}

		// add message
		if ($this->errors->isEmpty())
		{
			$this->preparePostMessage();
		}

		// save letter
		if ($this->errors->isEmpty())
		{
			$this->letter->save();
		}
		if ($this->letter->hasErrors())
		{
			$this->errors->add($this->letter->getErrors());
			return;
		}

		// redirect
		if ($this->errors->isEmpty())
		{
			if(in_array(
				$this->arResult['MESSAGE_CODE'],
				[Message\iMarketing::CODE_FACEBOOK, Message\iMarketing::CODE_INSTAGRAM]
			))
			{
				$this->letter->send();
			}

			if ($this->request->get('apply'))
			{
				if ($this->arParams['ID'])
				{
					$url = $this->request->getRequestUri();
				}
				else
				{
					$url = str_replace('#id#', $this->letter->getId(), $this->arParams['PATH_TO_EDIT']);
				}
			}
			else
			{
				$url = $this->arParams['GOTO_URI_AFTER_SAVE'] ?: $this->arParams['PATH_TO_EDIT'];
				$url = str_replace('#id#', $this->letter->getId(), $url);
			}
			$uri = new Uri($url);
			if ($this->arParams['IFRAME'] == 'Y')
			{
				$uri->addParams(array('IFRAME' => 'Y'));
				if (!$this->arParams['GOTO_URI_AFTER_SAVE'])
				{
					$uri->addParams(array('IS_SAVED' => 'Y'));
				}
				if ($this->arParams['IS_OUTSIDE'])
				{
					$uri->addParams(array('isOutside' => 'Y'));
				}
			}
			if (is_array($this->request->get('DISPATCH')))
			{
				$uri->addParams($this->request->get('DISPATCH'));
			}

			if ($this->contentValue)
			{
				\Bitrix\Sender\FileTable::syncFiles($this->letter->getId(), 0, $this->contentValue);
			}

			LocalRedirect($uri->getLocator());
		}
	}

	protected function prepareResult()
	{
		if (!$this->arParams['CAN_VIEW'])
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->arResult['SUBMIT_FORM_URL'] = Context::getCurrent()->getRequest()->getRequestUri();

		$this->letter = Entity\Letter::createInstanceById(
			$this->arParams['ID'],
			$this->arParams['MESSAGE_CODE_LIST']
		);

		if (!$this->letter)
		{
			Security\AccessChecker::addError($this->errors, Security\AccessChecker::ERR_CODE_NOT_FOUND);
			return false;
		}
		$appliedConsents = json_decode(\COption::GetOptionString("sender", "sender_approve_consent_created"), true);
		if (!$appliedConsents[Context::getCurrent()->getLanguage()])
		{
			\CAgent::AddAgent(
				'\\Bitrix\\Sender\\Preset\\Consent\\ConsentInstaller::run(\''.Context::getCurrent()->getLanguage().'\');',
				"sender",
				"N",
				60,
				"",
				"Y",
				\ConvertTimeStamp(time()+\CTimeZone::GetOffset()+450, "FULL"));

			$appliedConsents[Context::getCurrent()->getLanguage()] = Context::getCurrent()->getLanguage();
			\COption::SetOptionString("sender", "sender_approve_consent_created", json_encode($appliedConsents));
		}

		try
		{
			if (!$this->letter->getId())
			{
				$this->letter->set('MESSAGE_CODE', $this->arParams['MESSAGE_CODE']);
			}

			$message = $this->letter->getMessage();
			$this->arResult['MESSAGE_CODE'] = $message->getCode();
			$this->arResult['MESSAGE_ID'] = $message->getId();
			$this->arResult['MESSAGE_NAME'] = $message->getName();
			$this->arResult['MESSAGE'] = $message;
			$defaultCategory = null;

			if($message != null && method_exists($message, "getConfiguration"))
			{
				foreach ($message->getConfiguration()->getOptions() as $option)
				{
					if($option->getCode() === 'CATEGORY_ID')
					{
						$items = $option->getItems();
						$newItems = [];

						foreach ($items as $item)
						{
							$newItems[$item['code']] = $item['value'];
						}

						$items = (new \Bitrix\Sender\Access\Service\RoleDealCategoryService())
							->getFilteredDealCategories($this->userId, $newItems);
						$convertedItems = [];

						foreach($items as $key => $item)
						{
							$convertedItems[] = ['code' => $key, 'value' => $item];
						}

						$option->setItems($convertedItems);
					}
				}
			}
		}
		catch (SystemException $exception)
		{
			$this->errors->setError(new Error(Loc::getMessage('SENDER_COMP_LETTER_EDIT_ERROR_MSG_CODE', array('%type%' => $this->arParams['MESSAGE_CODE']))));
			return false;
		}

		$isNewAds = in_array(
			$this->arResult['MESSAGE_CODE'],
			[Message\iMarketing::CODE_FACEBOOK, Message\iMarketing::CODE_INSTAGRAM]
		);
		// get row
		$this->arResult['ROW'] = $this->letter->getData();
		if ($this->arResult['ROW']['IS_TRIGGER'] === 'Y'
			|| $isNewAds)
		{
			$this->arParams['SHOW_SEGMENTS'] = false;
			$this->arParams['SHOW_CAMPAIGNS'] = false;
			$this->arParams['GOTO_URI_AFTER_SAVE'] = $isNewAds ? false : null;
		}

		// Process POST
		if ($this->request->isPost() && check_bitrix_sessid() && $this->arParams['CAN_EDIT'])
		{
			$this->preparePost();
		}
		else if (!$this->letter->getId())
		{
			$this->prepareDefaultSegments();
		}

		// get campaign
		$this->arResult['CAMPAIGN_ID'] = $this->arParams['CAMPAIGN_ID'] ?: $this->letter->getCampaignId() ?: Entity\Campaign::getDefaultId(SITE_ID);

		// get campaigns
		$this->arResult['CAMPAIGNS'] = array();
		$campaigns = Entity\Campaign::getList(array(
			'select' => array('ID', 'NAME', 'SITE_ID', 'SITE_NAME' => 'SITE.NAME'),
			'order' => array('ID' => 'DESC')
		));
		foreach ($campaigns as $campaign)
		{
			$campaign['SELECTED'] = $campaign['ID'] == $this->arResult['CAMPAIGN_ID'];
			$siteName = Loc::getMessage('SENDER_COMP_LETTER_EDIT_SITE') . " `{$campaign['SITE_NAME']}`";
			$this->arResult['CAMPAIGNS'][$siteName][] = $campaign;
		}


		// get options list
		$configuration = $this->letter->getMessage()->getConfiguration();
		$templateType = $configuration->get('TEMPLATE_TYPE');
		$templateId = $configuration->get('TEMPLATE_ID');
		if ($templateType)
		{
			$configuration->getOption('TEMPLATE_TYPE')->setValue($templateType);
			$this->arResult['ROW']['TEMPLATE_TYPE'] = $templateType;
		}
		if ($templateId)
		{
			$configuration->getOption('TEMPLATE_ID')->setValue($templateId);
			$this->arResult['ROW']['TEMPLATE_ID'] = $templateId;
		}

		$this->arResult['LIST'] = array(
			Message\ConfigurationOption::GROUP_DEFAULT => Message\Configuration::convertToArray(
				$configuration->getOptionsByGroup(Message\ConfigurationOption::GROUP_DEFAULT)
			),
			Message\ConfigurationOption::GROUP_ADDITIONAL => Message\Configuration::convertToArray(
				$configuration->getOptionsByGroup(Message\ConfigurationOption::GROUP_ADDITIONAL)
			),
		);

		$this->arResult['USE_TEMPLATES'] = $this->accessController
				->check(ActionDictionary::ACTION_TEMPLATE_VIEW)
			&& Templates\Selector::create()
			->withMessageCode($this->arResult['MESSAGE_CODE'])
			->hasAny();

		$this->arResult['SHOW_BUTTONS'] = true;

		if(in_array($this->letter->getMessage()->getCode(),[
			Integration\Seo\Ads\MessageMarketingFb::CODE,
			Integration\Seo\Ads\MessageMarketingInstagram::CODE
		]))
		{
			$this->arResult['SHOW_BUTTONS'] = false;
		}

		$this->arResult['SHOW_TEMPLATE_SELECTOR'] =
			!$this->letter->getId() && !$this->request->isPost() && $this->arResult['USE_TEMPLATES'];

		$this->arResult['CAN_CHANGE_TEMPLATE'] = $this->letter->canChangeTemplate();


		$this->arResult['SEGMENTS'] = array(
			'INCLUDE' => $this->arResult['ROW']['SEGMENTS_INCLUDE'] + $this->letter->get('SEGMENTS_INCLUDE'),
			'EXCLUDE' => $this->arResult['ROW']['SEGMENTS_EXCLUDE'],
			'RECIPIENT_COUNT' => $this->letter->getId() ?
				$this->letter->getCounter()->getAll()
				:
				null,
			'IS_RECIPIENT_COUNT_EXACT' => $this->letter->getId() > 0,
			'DURATION_FORMATTED' => !$this->letter->getState()->isFinished() ?
				$this->letter->getDuration()->getFormattedInterval()
				:
				null,
			'READONLY' => !$this->arParams['CAN_EDIT'] || !$this->letter->canChangeSegments()
		);

		$this->arResult['CAN_SAVE_AS_TEMPLATE'] = $this->canSaveAsTemplate();

		if ($this->arParams['SET_TITLE'])
		{
			if ($this->arParams['IFRAME'] && $this->arResult['SHOW_TEMPLATE_SELECTOR'])
			{
				$GLOBALS['APPLICATION']->SetTitle($this->getLocMessage('SENDER_COMP_LETTER_EDIT_TITLE_TEMPLATES'));
			}
			else
			{
				$GLOBALS['APPLICATION']->SetTitle($this->letter->getId() ?
					$this->getLocMessage('SENDER_COMP_LETTER_EDIT_TITLE_EDIT')
					:
					$this->getLocMessage('SENDER_COMP_LETTER_EDIT_TITLE_ADD')
				);
			}
		}

		$this->arResult['LETTER_TILE'] = UI\TileView::create()->getTile(
			$this->arResult['ROW']['ID'],
			$this->arResult['ROW']['TITLE'],
			[
				'title' => $this->arResult['ROW']['TITLE'],
				'userId' => $this->arResult['ROW']['USER_ID'],
				'userName' => $this->arResult['ROW']['USER_NAME'] . ' ' . $this->arResult['ROW']['USER_LAST_NAME'],
				'dateInsert' => (string) $this->arResult['ROW']['DATE_INSERT'],
				'timeShift' => (int) $this->arResult['ROW']['TIME_SHIFT'],
			]
		);
		$this->arResult['IS_SAVED'] = $this->request->get('IS_SAVED') == 'Y';
		$this->arResult['IS_AVAILABLE']  = $this->letter->getMessage()->isAvailable();

		return true;
	}

	protected function prepareDefaultSegments()
	{
		$segments = $this->request->get('SEGMENTS_INCLUDE');
		$segments = (!empty($segments) && is_array($segments))
			?
			array_filter(
				$segments,
				function ($segmentId)
				{
					return $segmentId && is_numeric($segmentId);
				}
			)
			:
			Entity\Segment::getDefaultIds();

		$this->letter->set('SEGMENTS_INCLUDE', $segments);
	}

	protected function initTemplates()
	{
		$configuration = $this->letter->getMessage()->getConfiguration();

		// get template input names and values
		$this->arResult['TEMPLATE_TYPE'] = null;
		$this->arResult['TEMPLATE_ID'] = null;
		$option = $configuration->getOptionByType(Message\ConfigurationOption::TYPE_TEMPLATE_TYPE);
		if ($option)
		{
			$this->arResult['TEMPLATE_TYPE_INPUT_NAME'] = $option->getCode();
			$this->arResult['TEMPLATE_TYPE'] = $option->getValue();
			$option = $configuration->getOptionByType(Message\ConfigurationOption::TYPE_TEMPLATE_ID);
			if ($option)
			{
				$this->arResult['TEMPLATE_ID_INPUT_NAME'] = $option->getCode();
				$this->arResult['TEMPLATE_ID'] = $option->getValue();
			}
		}

		$template = Templates\Selector::create()
			->withMessageCode($this->arResult['MESSAGE_CODE'])
			->withTypeId($this->arResult['TEMPLATE_TYPE'])
			->withId($this->arResult['TEMPLATE_ID'])
			->get();
		if ($template)
		{
			$this->arResult['TEMPLATE_NAME'] = $template['NAME'];
		}
		else
		{
			$this->arResult['TEMPLATE_TYPE'] = null;
			$this->arResult['TEMPLATE_ID'] = null;
		}
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			/** @var Error $error */
			ShowError($error);
			$code = explode('feature:', $error->getCode());
			if (!empty($code[1]))
			{
				?><script>BX.UI.InfoHelper.show('<?=CUtil::JSescape($code[1])?>');</script><?php
			}
		}
	}

	public function executeComponent()
	{
		parent::executeComponent();
		$templateName = $this->request->get('showTime') == 'y' ? 'time' : '';
		parent::prepareResultAndTemplate($templateName);
	}

	public function getLocMessage($messageCode, $replace = [])
	{
		if (!empty($this->arParams['MESS'][$messageCode]))
		{
			$message = $this->arParams['MESS'][$messageCode];
		}
		else
		{
			$message = Loc::getMessage($messageCode, $replace);
		}

		return str_replace(array_keys($replace), array_values($replace), $message);
	}

	public function getEditAction()
	{
		return $this->getViewAction();
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_MAILING_VIEW;
	}
}