<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\License;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\UpdateSystem\ActivationSystem;
use Bitrix\Main\UpdateSystem\Coupon;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UpdateSystem\PortalInfo;

class LicensePopupComponent extends CBitrixComponent implements Controllerable, Errorable
{
	protected ErrorCollection $errorCollection;

	private PortalInfo $portalInfo;
	private License $license;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->portalInfo = new PortalInfo();
		$this->license = Application::getInstance()->getLicense();
	}

	public function configureActions(): array
	{
		$prefilters = [
			new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
			new ActionFilter\Authentication(),
		];

		return [
			'activate' => [
				'prefilters' => $prefilters
			],
			'check' => [
				'prefilters' => $prefilters
			],
			'queryPartner' => [
				'prefilters' => $prefilters
			]

		];
	}

	public function onPrepareComponentParams($arParams)
	{
		$this->errorCollection = new ErrorCollection();
	}

	private function validatePartnerRequestData($phone, $email)
	{
		if (empty($phone) && empty($email))
		{
			$this->errorCollection[] = new Error(
				Loc::getMessage('MAIN_COUPON_ACTIVATION_EMAIL_OR_PHONE_ERROR'),
				0
			);
		}
	}

	public function queryPartnerAction(): bool
	{
		$request = Context::getCurrent()->getRequest();
		$name = htmlspecialcharsbx(trim($request->getPost('name') ?? ''));
		$phone = htmlspecialcharsbx(trim($request->getPost('phone')));
		$email = htmlspecialcharsbx(trim($request->getPost('email')));

		$this->validatePartnerRequestData($phone, $email);

		if ($this->errorCollection->count() > 0)
		{
			return false;
		}

		try
		{
			$activationSystem = new ActivationSystem();
			$activationSystem->sendInfoToPartner($name, $phone, $email);
		}
		catch (\Exception $exception)
		{
			$errorMessage = $this->replaceKernelErrorMessage($exception->getMessage());
			$this->errorCollection[] = new Error($errorMessage, 0);

			return false;
		}

		return true;
	}

	public function executeComponent()
	{
		$user = CurrentUser::get();

		$this->arResult['BUY_LINK'] = $this->license->getBuyLink();
		$this->arResult['PARTNER_ID'] = $this->license->getPartnerId();
		$this->arResult['NAME'] = $user->getFullName();
		$this->arResult['EMAIL'] = $user->GetEmail();
		$this->arResult['SUPPORT_LINK'] = $this->getSupportLink($this->license->getRegion());
		$this->arResult['DOC_LINK'] = $this->license->getDocumentationLink();

		$this->includeComponentTemplate();
	}

	/**
	 * @throws SystemException|HttpRequestException
	 */
	private function requestToUpdateServer(string $license): Result
	{
			$coupon = new Coupon($license);
			$activationSystem = new ActivationSystem();

			return $activationSystem->reincarnate($coupon);
	}

	public function checkAction(): array
	{
		try
		{
			$currentLicenseKey = Application::getInstance()->getLicense()->getKey();
			$result = $this->requestToUpdateServer($currentLicenseKey);
			if (!$result->isSuccess())
			{
				$this->errorCollection->add($result->getErrors());

				return [];
			}

			return $result->getData();
		}
		catch (\Exception $exception)
		{
			$errorMessage = $this->replaceKernelErrorMessage($exception->getMessage());
			$this->errorCollection[] = new Error($errorMessage, 0);

			return [];
		}
	}

	private function validateLicenseString(string $license)
	{
		if (empty($license))
		{
			$this->errorCollection[] = new Error(
				Loc::getMessage('MAIN_COUPON_ACTIVATION_LICENSE_KEY_IS_EMPTY_ERROR'),
				0
			);
		}
	}

	public function activateAction(): bool
	{
		$request = Context::getCurrent()->getRequest();
		$key = htmlspecialcharsbx(trim($request->getPost('key')));
		$this->validateLicenseString($key);

		if ($this->errorCollection->count() > 0)
		{
			return false;
		}

		try
		{
			$activationSystem = new ActivationSystem();
			if ($this->isHashKey($key))
			{
				$result = $activationSystem->activateByHash($key);
			}
			else
			{
				$result = $this->requestToUpdateServer($key);
			}

			if (!$result->isSuccess())
			{
				$this->errorCollection->add($result->getErrors());

				return false;
			}

			return $result->isSuccess();
		}
		catch (\Exception $exception)
		{
			$errorMessage = $this->replaceKernelErrorMessage($exception->getMessage());
			$this->errorCollection[] = new Error($errorMessage, 0);

			return false;
		}
	}

	public function getErrors(): array
	{
		return $this->errorCollection->toArray();
	}

	public function getErrorByCode($code): ?Error
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	private function getSupportLink(string $code): string
	{
		$links = [
			'ru' => 'https://www.1c-bitrix.ru/support/customers/classic_support.php',
			'en' => 'https://helpdesk.bitrix24.com/ticket.php',
			'de' => 'https://helpdesk.bitrix24.de/ticket.php',
			'fr' => 'https://helpdesk.bitrix24.fr/ticket.php',
			'it' => 'https://helpdesk.bitrix24.it/ticket.php',
			'es' => 'https://helpdesk.bitrix24.es/ticket.php',
		];

		return $links[$code] ?? $links['ru'];
	}

	private function replaceKernelErrorMessage($kernelMessage): string
	{
		if (str_contains($kernelMessage, 'Error verify openssl'))
		{
			return Loc::getMessage('MAIN_COUPON_ACTIVATION_VERIFY_SSL_ERROR');
		}
		else if (str_contains($kernelMessage, 'Not found license info'))
		{
			return Loc::getMessage('MAIN_COUPON_ACTIVATION_LICENSE_INFO_ERROR');
		}
		else if (str_contains($kernelMessage, 'Server response is not recognized'))
		{
			return Loc::getMessage('MAIN_COUPON_ACTIVATION_LICENSE_INFO_ERROR');
		}
		else if (str_contains($kernelMessage, 'File open fails'))
		{
			return Loc::getMessage('MAIN_COUPON_ACTIVATION_FILE_OPEN_ERROR');
		}
		else if (str_contains($kernelMessage, 'Folder is not writable'))
		{
			return Loc::getMessage('MAIN_COUPON_ACTIVATION_FOLDER_WRITABLE_ERROR');
		}
		else if (str_contains($kernelMessage, 'Unknown error'))
		{
			return Loc::getMessage('MAIN_COUPON_ACTIVATION_UNKNOWN_ERROR');
		}
		else
		{
			return $kernelMessage;
		}
	}

	private function isHashKey($key): bool
	{
		return !(base64_decode($key, true) === false);
	}
}