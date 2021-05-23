<?

use Bitrix\Fileman;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Uri;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Integration\Yandex\Toloka\ApiRequest;
use Bitrix\Sender\Message;
use Bitrix\Sender\Security;
use Bitrix\Sender\Templates;
use Bitrix\Sender\UI;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderTolokaEditComponent extends \Bitrix\Sender\Internals\CommonSenderComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	/** @var Entity\Letter $letter Letter. */
	protected $letter;

	protected function checkRequiredParams()
	{
		return $this->errors->count() == 0;
	}

	protected function initParams()
	{
		$this->arParams['ID'] = $this->arParams['ID'] ? $this->arParams['ID'] : (int) $this->request->get('ID');

		$this->arParams['IS_OUTSIDE'] = isset($this->arParams['IS_OUTSIDE']) ? (bool) $this->arParams['IS_OUTSIDE'] : $this->request->get('isOutside') === 'Y';
		
		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;

		$this->arParams['CAN_VIEW'] = isset($this->arParams['CAN_VIEW'])
			?
			$this->arParams['CAN_VIEW']
			:
			Security\Access::current()->canViewLetters();

		$this->arParams['GOTO_URI_AFTER_SAVE'] = isset($this->arParams['GOTO_URI_AFTER_SAVE'])
			?
			$this->arParams['GOTO_URI_AFTER_SAVE']
			:
			$this->arParams['PATH_TO_TIME'];
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
				case Message\ConfigurationOption::TYPE_TEMPLATE_TYPE:
					$value = $this->letter->get('TEMPLATE_TYPE');
					$configuration->set('TEMPLATE_TYPE', $value);
					break;
				case Message\ConfigurationOption::TYPE_TEMPLATE_ID:
					$value = $this->letter->get('TEMPLATE_ID');
					$configuration->set('TEMPLATE_ID', $value);
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

	protected function preparePost()
	{
		// prepare letter
		$data = array(
			'TITLE' => $this->request->get('TITLE'),
			'IS_TRIGGER' => 'N',
			'TEMPLATE_TYPE' => $this->request->get('TEMPLATE_TYPE'),
			'TEMPLATE_ID' => $this->request->get('TEMPLATE_ID'),
			'UPDATED_BY' => Security\User::current()->getId(),
			'NOT_USE_SEGMENTS' => true
		);
		if (!$this->letter->getId())
		{
			$data['CAMPAIGN_ID'] = $this->arParams['CAMPAIGN_ID'] ?: Entity\Campaign::getDefaultId(SITE_ID);
			$data['CREATED_BY'] = Security\User::current()->getId();
		}
		$this->letter->mergeData($data);

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
			$uri->addParams(array('IFRAME' => 'Y'));
			if (!$this->arParams['GOTO_URI_AFTER_SAVE'])
			{
				$uri->addParams(array('IS_SAVED' => 'Y'));
			}
			if ($this->arParams['IS_OUTSIDE'])
			{
				$uri->addParams(array('isOutside' => 'Y'));
			}
			if (is_array($this->request->get('DISPATCH')))
			{
				$uri->addParams($this->request->get('DISPATCH'));
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
		$this->arResult['IS_REGISTERED'] = COption::GetOptionString('sender', ApiRequest::ACCESS_CODE);

		$this->letter = Entity\Letter::createInstanceById(
			$this->arParams['ID'],
			$this->arParams['MESSAGE_CODE_LIST']
		);

		$this->arResult['USE_TEMPLATES'] = Templates\Selector::create()
									->withMessageCode($this->arResult['MESSAGE_CODE'])
									->hasAny();
		if (!$this->letter)
		{
			Security\AccessChecker::addError($this->errors, Security\AccessChecker::ERR_CODE_NOT_FOUND);
			return false;
		}

		try
		{
			if (!$this->letter->getId())
			{
				$this->letter->set('MESSAGE_CODE', Integration\Yandex\Toloka\MessageToloka::CODE);
			}

			$message = $this->letter->getMessage();
			$this->arResult['MESSAGE_CODE'] = $message->getCode();
			$this->arResult['MESSAGE_ID'] = $message->getId();
			$this->arResult['MESSAGE_NAME'] = $message->getName();
			$this->arResult['MESSAGE'] = $message;
			$this->arResult['MESSAGE_CONFIG'] = $message->getConfiguration();
		}
		catch (SystemException $exception)
		{
			$this->errors->setError(new Error(Loc::getMessage('SENDER_COMP_LETTER_EDIT_ERROR_MSG_CODE', array('%type%' => $this->arParams['MESSAGE_CODE']))));
			return false;
		}

		// Process POST
		if ($this->request->isPost() && check_bitrix_sessid() && $this->arParams['CAN_EDIT'])
		{
			$this->preparePost();
		}

		// get row
		$this->arResult['ROW'] = $this->letter->getData();
		// get options list
		$configuration = $this->letter->getMessage()->getConfiguration();
		$this->arResult['LIST'] = array(
			Message\ConfigurationOption::GROUP_DEFAULT => Message\Configuration::convertToArray(
				$configuration->getOptionsByGroup(Message\ConfigurationOption::GROUP_DEFAULT)
			),
			Message\ConfigurationOption::GROUP_ADDITIONAL => Message\Configuration::convertToArray(
				$configuration->getOptionsByGroup(Message\ConfigurationOption::GROUP_ADDITIONAL)
			),
		);
		if($this->arResult['ROW']['TEMPLATE_ID'])
		{
			$this->arResult['ROW']['TEMPLATE'] =Templates\Selector::create()
						->withMessageCode(
							Integration\Yandex\Toloka\MessageToloka::CODE
						)
						->withTypeId($this->arResult['ROW']['TEMPLATE_TYPE'])
						->withId($this->arResult['ROW']['TEMPLATE_ID'])
						->get();
		}

		if ($this->arParams['SET_TITLE'])
		{
				$GLOBALS['APPLICATION']->SetTitle($this->getLocMessage('SENDER_COMP_TOLOKA_EDIT_TITLE_TEMPLATES'));
		}


		if (!$this->arResult['IS_REGISTERED'])
		{
				$GLOBALS['APPLICATION']->SetTitle($this->getLocMessage('SENDER_COMP_TOLOKA_CONNECT'));
		}

		$this->arResult['ACTION_URL'] = $this->getPath() . '/ajax.php';

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

		$this->arResult['USE_TEMPLATES'] = $this->accessController
				->check(ActionDictionary::ACTION_TEMPLATE_VIEW)
			&& Templates\Selector::create()
				->withMessageCode($this->arResult['MESSAGE_CODE'])
				->hasAny();

		$this->arResult['SHOW_TEMPLATE_SELECTOR'] =
			!$this->letter->getId() && !$this->request->isPost() && $this->arResult['USE_TEMPLATES'];

		return true;
	}

	public function executeComponent()
	{
		parent::executeComponent();
		parent::prepareResultAndTemplate();
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
		return \Bitrix\Sender\Access\ActionDictionary::ACTION_MAILING_EMAIL_EDIT;
	}

	public function getViewAction()
	{
		return \Bitrix\Sender\Access\ActionDictionary::ACTION_MAILING_VIEW;
	}
}