<?php

use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\SenderSendCounter;
use Bitrix\Main\Type;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\ModuleManager;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Dispatch;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Internals\CommonSenderComponent;
use Bitrix\Sender\Internals\PrettyDate;
use Bitrix\Sender\Security;

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

class SenderLetterTimeComponent extends CommonSenderComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;
	protected $user_errors = [];

	/** @var Entity\Letter $letter */
	protected $letter;

	protected function checkRequiredParams()
	{
		if (!$this->arParams['ID'])
		{
			$this->errors->setError(new Error(Loc::getMessage('SENDER_LETTER_TIME_COMP_ERR_NO_LETTER')));
		}

		return $this->errors->isEmpty();
	}

	protected function initParams()
	{
		global $USER;

		if (empty($this->arParams['ID']))
		{
			$this->arParams['ID'] = (int) $this->request->get('ID');
		}

		$this->arParams['IS_OUTSIDE'] = isset($this->arParams['IS_OUTSIDE']) ? (bool) $this->arParams['IS_OUTSIDE'] : $this->request->get('isOutside') === 'Y';
		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;
		$this->arParams['IS_BX24_INSTALLED'] = Integration\Bitrix24\Service::isCloud();
		$this->arParams['IS_PHONE_CONFIRMED'] = \Bitrix\Sender\Integration\Bitrix24\Limitation\Verification::isPhoneConfirmed();

		$this->arParams['CAN_VIEW'] =
			isset($this->arParams['CAN_VIEW'])
				? $this->arParams['CAN_VIEW']
				: Security\Access::getInstance()->canViewLetters()
		;

		$transportCode = self::getTransportCode($this->arParams['ID'], $this->arParams['MESSAGE_CODE_LIST']);
		$this->arParams['IS_MAIL_TRANSPORT'] = $transportCode === \Bitrix\Sender\Transport\iBase::CODE_MAIL;
	}

	private static function getTransportCode($letterId, $transportCodesAlist): ?string
	{
		if ($letter = Entity\Letter::createInstanceById($letterId, $transportCodesAlist))
		{
			return $letter->getMessage()->getTransport()->getCode();
		}
		return null;
	}

	protected function preparePost()
	{
		$dateTime = null;
		$code = $this->request->get('LETTER_TIME');
		if (Type\DateTime::isCorrect($code))
		{
			/** @var Type\DateTime $dateTime Time */
			$dateTime = new Type\DateTime($code);
			$diff = \CTimeZone::GetOffset();
			$dateTime->add(($diff > 0 ? "-" : "") . "PT" . abs($diff) . "S");
			$code = 'time';
		}

		$userId = Security\User::current()->getId();
		if ($userId)
		{
			$this->letter->set('UPDATED_BY', $userId);
		}
		$method = $this->letter->getMethod();
		if ($method->canChange() && $this->accessController->check(ActionDictionary::ACTION_MAILING_PAUSE_START_STOP))
		{
			switch ($code)
			{
				case 'now':
					$method->now();
					break;

				case Dispatch\Method::SCHEDULE:
					$scheduleTime = Dispatch\MethodSchedule::parseTimesOfDay($this->request->get('TIMES_OF_DAY'));
					$scheduleMonths = Dispatch\MethodSchedule::parseMonthsOfYear($this->request->get('MONTHS_OF_YEAR'));
					$scheduleWeekDays = Dispatch\MethodSchedule::parseDaysOfWeek($this->request->get('DAYS_OF_WEEK'));
					$scheduleMonthDays = Dispatch\MethodSchedule::parseDaysOfMonth($this->request->get('DAYS_OF_MONTH'));
					if (empty($scheduleTime))
					{
						$this->errors->setError(new Error(Loc::getMessage('SENDER_LETTER_TIME_COMP_ERR_SCHEDULE_WRONG_TIME')));
					}
					if (empty($scheduleWeekDays))
					{
						$this->errors->setError(new Error(Loc::getMessage('SENDER_LETTER_TIME_COMP_ERR_SCHEDULE_WRONG_WEEK_DAYS')));
					}
					$method->set(
						(new Dispatch\MethodSchedule($this->letter))
							->setMonthsOfYear($scheduleMonths)
							->setDaysOfMonth($scheduleMonthDays)
							->setDaysOfWeek($scheduleWeekDays)
							->setTime($scheduleTime[0], $scheduleTime[1])
					);
					break;

				case Dispatch\Method::TIME:
					$method->time($dateTime);
					break;

				case Dispatch\Method::DEFERED:
				default:
					$method->defer();
					break;
			}

			if ($this->letter->hasErrors())
			{
				$this->errors->add($this->letter->getErrors());
				return;
			}

			$method->apply();

			if ($this->letter->hasErrors())
			{
				foreach ($this->letter->getErrorCollection() as $error)
				{
					/** @var \Bitrix\Main\Error $error Error. */
					if ($error->getCode())
						$this->user_errors[] = $error;
				}
				$this->errors->add($this->letter->getErrors());
				return;
			}
		}

		if ($this->errors->isEmpty())
		{
			$url = str_replace('#id#', $this->letter->getId(), $this->arParams['PATH_TO_TIME'] ?? '');
			$uri = new Uri($url);
			if (isset($this->arParams['IFRAME']) && $this->arParams['IFRAME'] == 'Y')
			{
				$uri->addParams(array('IFRAME' => 'Y'));
				$uri->addParams(array('IS_SAVED' => 'Y'));
			}
			if ($this->arParams['IS_OUTSIDE'])
			{
				$uri->addParams(array('isOutside' => 'Y'));
			}

			LocalRedirect($uri->getLocator());
		}
	}

	protected function prepareResult()
	{
		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var CAllMain*/
			$GLOBALS['APPLICATION']->SetTitle($this->getMessage('SENDER_LETTER_TIME_COMP_TITLE'));
		}

		if (!$this->arParams['CAN_VIEW'])
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->letter = Entity\Letter::createInstanceById(
			$this->arParams['ID'],
			$this->arParams['MESSAGE_CODE_LIST']
		);

		if (!$this->letter)
		{
			Security\AccessChecker::addError($this->errors, Security\AccessChecker::ERR_CODE_NOT_FOUND);
			return false;
		}

		$email = $this->letter->getMessage()->getConfiguration()->get('EMAIL_FROM', false);

		if ($email)
		{
			$this->arParams['DAY_LIMIT'] = Bitrix\Main\Mail\Sender::getEmailLimit($email);
		}

		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT'])
			?
			$this->arParams['CAN_EDIT']
			:
			Security\Access::getInstance()->canStopStartPause(get_class($this->letter));

		if (!$this->letter->getId())
		{
			$this->errors->setError(new Error(Loc::getMessage('SENDER_LETTER_TIME_COMP_ERR_NO_LETTER')));
			return false;
		}


		// Process POST
		if ($this->request->isPost() && check_bitrix_sessid() && $this->arParams['CAN_EDIT'])
		{
			$this->preparePost();
			$this->printErrors();
			$this->arResult['USER_ERRORS'] = $this->user_errors;
		}

		$this->arResult['SUBMIT_FORM_URL'] = Context::getCurrent()->getRequest()->getRequestUri();
		$this->arResult['ACTION_URL'] = $this->getPath() . '/ajax.php';
		$this->arResult['TITLE'] = $this->letter->get('TITLE');

		$method = $this->letter->getMethod();
		$code = $this->request->get('METHOD_CODE') ?: $method->getCode();
		switch ($code)
		{
			case Dispatch\Method::TIME:
				/** @var Dispatch\MethodTime $methodInstance */
				$methodInstance = $method->get();
				$code = $methodInstance->getDateTime();
				$this->arResult['DATE_SEND'] = $this->letter->get('DATE_SEND') ?: $this->letter->get('AUTO_SEND_TIME');
				$this->arResult['DATE_SEND'] = PrettyDate::formatDateTime($this->arResult['DATE_SEND']);
				break;

			case Dispatch\Method::SCHEDULE:
				$code = Dispatch\Method::SCHEDULE;
				$this->arResult['DATE_SEND'] = $this->letter->get('DATE_SEND') ?: $this->letter->get('AUTO_SEND_TIME');
				$this->arResult['DATE_SEND'] = PrettyDate::formatDateTime($this->arResult['DATE_SEND']);
				break;

			case Dispatch\Method::DEFERED:
			default:
				$code = Dispatch\Method::DEFERED;
				$this->arResult['DATE_SEND'] = '';
				break;
		}

		$this->arResult['LETTER_TIME'] = $code;
		$this->arResult['CAN_CHANGE'] = $method->canChange() && $this->arParams['CAN_EDIT'];
		foreach (['DAYS_OF_MONTH', 'DAYS_OF_WEEK', 'MONTHS_OF_YEAR', 'TIMES_OF_DAY'] as $key)
		{
			$this->arResult[$key] = $this->letter->get($key) ?: $this->request->get($key);
		}
		$this->arResult['TIME_LIST'] = Dispatch\MethodSchedule::getTimeList();
		$this->arResult['IS_SAVED'] = $this->request->get('IS_SAVED') == 'Y';
		$this->arResult['IS_SUPPORT_REITERATE'] = $this->letter->isSupportReiterate();
		$this->arResult['IS_SUPPORT_REITERATE_DAYS'] = !Integration\Bitrix24\Service::isPortal();


		$this->arResult['LIMITATION'] = array();
		$transport = $this->letter->getMessage()->getTransport();
		if ($transport->getCode() === $transport::CODE_MAIL)
		{
			if (Integration\Bitrix24\Service::isCloud())
			{
				$limiter = Integration\Bitrix24\Limitation\Limiter::getMonthly();
				$this->arResult['LIMITATION']['TEXT'] = $limiter->getText();
				$this->arResult['LIMITATION']['SETUP_URI'] = $limiter->getParameter('setupUri');
				$this->arResult['LIMITATION']['SETUP_CAPTION'] = $limiter->getParameter('setupCaption');
			}
		}

		return true;
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			if (!in_array($error, $this->user_errors))
			{
				ShowError($error);
			}
		}
	}

	public function executeComponent()
	{
		parent::executeComponent();
		parent::prepareResultAndTemplate();
	}

	public function getMessage($messageCode, $replace = [])
	{
		if (empty($this->arParams['~MESS'][$messageCode]))
		{
			return Loc::getMessage($messageCode, $replace);
		}

		return str_replace(
			array_keys($replace),
			array_values($replace),
			$this->arParams['~MESS'][$messageCode]
		);
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
