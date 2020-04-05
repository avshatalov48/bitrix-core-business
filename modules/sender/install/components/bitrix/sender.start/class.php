<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;

use Bitrix\Sender\Message;
use Bitrix\Sender\Security;
use Bitrix\Sender\Integration;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderStartComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors Errors. */
	protected $errors;

	protected function checkRequiredParams()
	{
		if (!Loader::includeModule('sender'))
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

		$list = [];
		foreach ($messages as $message)
		{
			$message = new Message\Adapter($message);
			if ($message->isAds())
			{
				$pathToAdd = $pathToAdsAdd;
			}
			elseif ($message->isReturnCustomer())
			{
				$pathToAdd = $pathToRcAdd;
			}
			else
			{
				$pathToAdd = $pathToLetterAdd;
			}

			$list[] = array(
				'CODE' => $message->getCode(),
				'NAME' => $message->getName(),
				'IS_AVAILABLE' => $message->isAvailable(),
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

	protected function prepareResult()
	{
		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var CMain*/
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('SENDER_START_TITLE'));
		}

		if (!Security\Access::current()->canViewStart())
		{
			Security\AccessChecker::addError($this->errors);
			return false;
		}

		$this->arResult['MESSAGES'] = array(
			'MAILING' =>  $this->getSenderMessages(
				Security\Access::current()->canModifyLetters()
				?
				Message\Factory::getMailingMessages()
				:
				[]
			),
			'ADS' =>  $this->getSenderMessages(
				Security\Access::current()->canModifyAds()
				?
				Message\Factory::getAdsMessages()
				:
				[]
			),
			'RC' =>  $this->getSenderMessages(
				Security\Access::current()->canModifyRc()
				?
				Message\Factory::getReturnCustomerMessages()
				:
				[]
			),
		);

		Security\Agreement::requestFromCurrentUser();
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
		$this->errors = new ErrorCollection();
		$this->initParams();
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
			return;
		}

		if (!$this->prepareResult())
		{
			$this->printErrors();
			return;
		}

		$this->includeComponentTemplate();
	}
}