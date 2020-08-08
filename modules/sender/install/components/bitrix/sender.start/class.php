<?

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Access\Map\AdsAction;
use Bitrix\Sender\Access\Map\MailingAction;
use Bitrix\Sender\Access\Map\RcAction;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Message;

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

class SenderStartComponent extends Bitrix\Sender\Internals\CommonSenderComponent
{
	/** @var ErrorCollection $errors Errors. */
	protected $errors;

	protected function checkRequiredParams()
	{
		if (!Bitrix\Main\Loader::includeModule('sender'))
		{
			$this->errors->setError(new Error('Module `sender` is not installed.'));
			return false;
		}

		return true;
	}

	protected function initParams()
	{
		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;
		$this->arParams['PATH_TO_ADS_ADD'] = isset($this->arParams['PATH_TO_ADS_ADD'])
			?
			$this->arParams['PATH_TO_ADS_ADD']
			:
			str_replace('letter', 'ads', $this->arParams['PATH_TO_LETTER_ADD']);
		$this->arParams['PATH_TO_RC_ADD'] = isset($this->arParams['PATH_TO_RC_ADD'])
			?
			$this->arParams['PATH_TO_RC_ADD']
			:
			str_replace('letter', 'rc', $this->arParams['PATH_TO_LETTER_ADD']);

		$this->arParams['PATH_TO_TOLOKA_ADD'] = $this->arParams['PATH_TO_TOLOKA_ADD']??
			str_replace('letter', 'toloka', $this->arParams['PATH_TO_LETTER_ADD']);
	}

	protected function getSenderMessageIcon(Message\Adapter $message)
	{
		$code = $message->getCode();
		$map = [
			Message\iBase::CODE_MAIL => 'ui-icon-service-campaign',
			Message\iBase::CODE_SMS => 'ui-icon-service-sms',
			Message\iBase::CODE_IM => 'ui-icon-service-messenger',
			Message\iBase::CODE_CALL => 'ui-icon-service-infocall',
			Message\iBase::CODE_AUDIO_CALL => 'ui-icon-service-audio-infocall',
			Message\iBase::CODE_WEB_HOOK => '',
			Integration\Seo\Ads\MessageBase::CODE_ADS_FB => 'ui-icon-service-fb',
			Integration\Seo\Ads\MessageBase::CODE_ADS_YA => 'ui-icon-service-ya-direct',
			Integration\Seo\Ads\MessageBase::CODE_ADS_GA => 'ui-icon-service-google-ads',
			Integration\Seo\Ads\MessageBase::CODE_ADS_VK => 'ui-icon-service-vk',
			Integration\Seo\Ads\MessageBase::CODE_ADS_LOOKALIKE_FB => 'ui-icon-service-fb',
			Integration\Seo\Ads\MessageBase::CODE_ADS_LOOKALIKE_VK => 'ui-icon-service-vk',
			Integration\Crm\ReturnCustomer\MessageBase::CODE_RC_DEAL => 'ui-icon-service-deal',
			Integration\Crm\ReturnCustomer\MessageBase::CODE_RC_LEAD => 'ui-icon-service-lead',
			Message\iBase::CODE_TOLOKA => 'ui-icon-service-ya-toloka',
		];

		return 'ui-icon ' . $map[$code];
	}

	protected function getSenderMessages(array $messages)
	{
		$pathToLetterAdd = $this->arParams['PATH_TO_LETTER_ADD'];
		$uri = new Uri($pathToLetterAdd);
		$uri->addParams(array('code' => '#code#'));
		$pathToLetterAdd = $uri->getLocator();

		$pathToAdsAdd = $this->arParams['PATH_TO_ADS_ADD'];
		$uri = new Uri($pathToAdsAdd);
		$uri->addParams(array('code' => '#code#'));
		$pathToAdsAdd = $uri->getLocator();

		$pathToRcAdd = $this->arParams['PATH_TO_RC_ADD'];
		$uri = new Uri($pathToRcAdd);
		$uri->addParams(array('code' => '#code#'));
		$pathToRcAdd = $uri->getLocator();

		$pathToTolokaAdd = $this->arParams['PATH_TO_TOLOKA_ADD'];
		$uri = new Uri($pathToTolokaAdd);
		$uri->addParams(array('code' => '#code#'));
		$pathToTolokaAdd = $uri->getLocator();

		$list = [];
		foreach ($messages as $message)
		{
			$message = new Message\Adapter($message);

			if ($message->isHidden())
			{
				continue;
			}

			if ($message->isAds())
			{
				$pathToAdd = $pathToAdsAdd;
			}
			elseif ($message->isReturnCustomer())
			{
				$pathToAdd = $pathToRcAdd;
			}
			elseif($message->isMailing())
			{
				$pathToAdd = $pathToLetterAdd;
			}
			else
			{
				$pathToAdd = $pathToTolokaAdd;
			}

			$list[] = array(
				'CODE' => $message->getCode(),
				'NAME' => $message->getName(),
				'IS_AVAILABLE' => $message->isAvailable(),
				'ICON_CLASS' => $this->getSenderMessageIcon($message),
				'URL' => str_replace(
					array('#code#', urlencode('#code#')),
					$message->getCode(),
					$pathToAdd
				)
			);
		}

		$featured = array(
			Message\iBase::CODE_MAIL,
			Message\iBase::CODE_SMS,
			Message\iBase::CODE_IM,
			Message\iBase::CODE_CALL,
			Message\iBase::CODE_AUDIO_CALL,
			Message\iBase::CODE_WEB_HOOK
		);

		$featuredList = array();
		$otherList = array();

		foreach ($list as $message)
		{
			$code = $message['CODE'];
			if (in_array($code, $featured))
			{
				$featuredList[$code] = $message;
			}
			else
			{
				$otherList[$code] = $message;
			}
		}

		$diffCount = count($featured) - count($featuredList);
		$otherCount = count($otherList);
		if ($diffCount > 0 && $otherCount > 0)
		{
			for ($i = 0; ($i < $diffCount && $i < $otherCount); $i++)
			{
				$message = array_shift($otherList);
				if (!$message)
				{
					break;
				}

				$featuredList[] = $message;
			}
		}

		return array(
			'LIST' => $list,
			'FEATURED_LIST' => $featuredList,
			'OTHER_LIST' => $otherList,
		);
	}

	private function filterMessages($messages, $map): array
	{
		$result = [];
		foreach ($messages as $message)
		{
			if(!$this->getAccessController()->check(
				$map[$message::CODE]
			))
			{
				continue;
			}
			$result[] = $message;
		}

		return $result;
	}

	protected function prepareResult()
	{
		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var CMain*/
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('SENDER_START_TITLE'));
		}

		$mailingMessages = $this->filterMessages(Message\Factory::getMailingMessages(), MailingAction::getMap());
		$adsMessages = $this->filterMessages(Message\Factory::getAdsMessages(), AdsAction::getMap());
		$rcMessages = $this->filterMessages(Message\Factory::getReturnCustomerMessages(), RcAction::getMap());
		$tolokaMessages = $this->filterMessages(Message\Factory::getTolokaMessages(), RcAction::getMap());

		$this->arResult['MESSAGES'] = array(
			'MAILING' =>  $this->getSenderMessages(
				$this->getAccessController()->check(ActionDictionary::ACTION_MAILING_VIEW)
				?
					$mailingMessages
				:
				[]
			),
			'ADS' =>  $this->getSenderMessages(
				$this->getAccessController()->check(ActionDictionary::ACTION_ADS_VIEW)
				?
					$adsMessages
				:
				[]
			),
			'RC' =>  $this->getSenderMessages(
				$this->getAccessController()->check(ActionDictionary::ACTION_RC_VIEW)
					?
					$rcMessages
					:
					[]
			),
			'TOLOKA' =>  $this->getSenderMessages(
				$this->getAccessController()->check(ActionDictionary::ACTION_RC_VIEW)
					?
					$tolokaMessages
					:
					[]
			),
		);

		foreach ($this->arResult['MESSAGES'] as $section => $data)
		{
			$data['TILES'] = array_map(
				function ($item)
				{
					return [
						'id' => $item['CODE'],
						'name' => $item['NAME'],
						'selected' => $item['IS_AVAILABLE'],
						'iconClass' => $item['ICON_CLASS'],
						'data' => [
							'url' => $item['URL']
						],
					];
				},
				$data['LIST']
			);

			$this->arResult['MESSAGES'][$section] = $data;
		}
		Integration\Bitrix24\Service::initLicensePopup();

		return true;
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		parent::executeComponent();
		parent::prepareResultAndTemplate();
	}

	public function getEditAction()
	{
		return ActionDictionary::ACTION_START_VIEW;
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_START_VIEW;
	}
}