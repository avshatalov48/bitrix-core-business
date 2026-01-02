<?php

declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\AI;

use Bitrix\AI\Cloud;
use Bitrix\AI\Context;
use Bitrix\AI\Facade\Bitrix24;

use Bitrix\Landing\Copilot\Connector\AI\Type\ErrorCode;
use Bitrix\Landing\Copilot\Connector\AI\Type\HelpdeskCode;
use Bitrix\Landing\Copilot\Connector\AI\Type\LimitType;
use Bitrix\Landing\Copilot\Connector\AI\Type\MessageCode;
use Bitrix\Landing\Copilot\Connector\AI\Type\PromoLimitCode;
use Bitrix\Landing\Copilot\Connector\AI\Type\SliderCode;
use Bitrix\AI\Limiter\Enums\TypeLimit;
use Bitrix\AI\Limiter\LimitControlService;
use Bitrix\AI\Limiter\LimitControlBoxService;
use Bitrix\AI\Limiter\Usage;
use Bitrix\AI\Limiter\Enums\ErrorLimit;
use Bitrix\Baas\Baas;
use Bitrix\Landing\Copilot\Generation\GenerationException;
use Bitrix\Landing\Copilot\Generation\Type\GenerationErrors;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\UI\Util;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class RequestLimiter
 *
 * Handles checking and messaging for request quotas to AI services (CoPilot) within the Landing module.
 * Supports both cloud (Bitrix24 module present) and box (on-premise) environments.
 * Determines if request limits are exceeded and returns localized error messages or null if within limits.
 */
class RequestLimiter
{

	/**
	 * Stores the result of the most recent limit check, including type, message, and exceeded flag.
	 */
	private LimitCheckResult $checkResult;

	/**
	 * Constructor initializes checkResult with a default value (no limit exceeded).
	 */
	public function __construct()
	{
		$this->checkResult = $this->createLimitResult(LimitType::None, false);
	}

	/**
	 * Returns the result object containing information about the current limit check.
	 *
	 * @return LimitCheckResult Limit result data transfer object with type, message, and exceeded flag.
	 */
	public function getCheckResult(): LimitCheckResult
	{
		return $this->checkResult;
	}

	/**
	 * Returns the localized message describing the current limit status.
	 *
	 * @return string|null Localized message for the user about the limit status.
	 */
	public function getCheckResultMessage(): ?string
	{
		return $this->checkResult->getMessage();
	}

	/**
	 * Checks if the given error corresponds to a request limit exceeded situation.
	 *
	 * @param Error $error Error object to check.
	 *
	 * @return bool True if the limit is exceeded, false otherwise.
	 */
	public function checkError(Error $error): bool
	{
		if (Loader::includeModule('bitrix24'))
		{
			$this->checkResult = $this->checkCloudErrorLimit($error);
		}
		else
		{
			$this->checkResult = $this->checkBoxErrorLimit($error);
		}

		return $this->checkResult->isExceeded();
	}

	/**
	 * Checks if the request count exceeds quota limits.
	 *
	 * @param int $requestCount Number of requests to check.
	 *
	 * @return bool True if the limit is exceeded, false otherwise.
	 */
	public function checkQuota(int $requestCount): bool
	{
		if ($requestCount <= 0)
		{
			$this->checkResult = $this->createLimitResult(LimitType::None, false);

			return $this->checkResult->isExceeded();
		}

		if (Loader::includeModule('bitrix24'))
		{
			$this->checkResult =  $this->checkCloudQuotaLimit($requestCount);
		}
		else
		{
			$this->checkResult = $this->checkBoxQuotaLimit($requestCount);
		}

		return $this->checkResult->isExceeded();
	}


	/**
	 * Handles cloud-specific AI error codes for quota limits.
	 *
	 * @param Error $error Error object to check.
	 *
	 * @return LimitCheckResult Result object containing a localized message, limit type, and a flag indicating if the limit is exceeded.
	 */
	private function checkCloudErrorLimit(Error $error): LimitCheckResult
	{
		$code = $error->getCode();

		//right 4 in board
		if ($code === ErrorCode::BaasRateLimit->value)
		{
			return $this->createLimitResult(
				LimitType::Rate,
				true,
				self::buildLimitMessage(MessageCode::BaasRateLimit)
			);
		}

		if ($code === ErrorCode::LimitBaasCloud->value)
		{
			//right 3 in board
			if (Bitrix24::isMarketAvailable())
			{
				return $this->createLimitResult(
					LimitType::Market,
					true,
					self::buildLimitMessage(MessageCode::Market, SliderCode::Market)
				);
			}

			//right 2 in board
			return $this->createLimitResult(
				LimitType::Baas,
				true,
				self::buildLimitMessage(MessageCode::Baas, SliderCode::BoostCopilot)
			);
		}

		if (str_starts_with($code, ErrorCode::LimitCloud->value))
		{
			//right 1 in board
			if (Loader::includeModule('baas') && Baas::getInstance()->isAvailable())
			{
				return $this->createLimitResult(
					LimitType::Promo,
					true,
					self::buildLimitMessage(MessageCode::Promo, SliderCode::BoostCopilot)
				);
			}

			//left 1 in board
			if ($code === ErrorCode::Daily->value)
			{
				return $this->createLimitResult(
					LimitType::Daily,
					true,
					self::buildLimitMessage(MessageCode::Daily, SliderCode::Daily)
				);
			}

			//left 2 in board
			if ($code === ErrorCode::Monthly->value)
			{
				return $this->createLimitResult(
					LimitType::Monthly,
					true,
					self::buildLimitMessage(MessageCode::Monthly, SliderCode::Monthly)
				);
			}
		}

		return $this->createLimitResult(LimitType::None, false);
	}

	/**
	 * Handles box AI error codes for quota limits.
	 *
	 * @param Error $error Error object to check.
	 *
	 * @return LimitCheckResult Result object containing a localized message, limit type, and a flag indicating if the limit is exceeded.
	 */
	private function checkBoxErrorLimit(Error $error): LimitCheckResult
	{
		$errorCode = $error->getCode();

		if ($errorCode === ErrorCode::RateLimit->value)
		{
			//top in board
			return $this->createLimitResult(
				LimitType::Rate,
				true,
				self::buildLimitMessage(MessageCode::Rate, null, HelpdeskCode::Rate)
			);
		}

		$customData = $error->getCustomData();

		if (
			isset($customData['showSliderWithMsg'])
			&& $errorCode === ErrorCode::LimitBaasCloud->value
		)
		{
			//right 2 in board
			if ($customData['showSliderWithMsg'] === true)
			{
				return $this->createLimitResult(
					LimitType::Baas,
					true,
					self::buildLimitMessage(MessageCode::Baas, SliderCode::BoostCopilotBox)
				);
			}

			//left 1 in board
			return $this->createLimitResult(
				LimitType::Monthly,
				true,
				self::buildLimitMessage(MessageCode::Monthly, SliderCode::RequestBox)
			);
		}

		//right 1 in board
		return $this->createLimitResult(
			LimitType::Promo,
			true,
			self::buildLimitMessage(MessageCode::Promo, SliderCode::BoostCopilotBox)
		);
	}

	/**
	 * Cloud-side reservation of request quota.
	 *
	 * @param int $requestCount Number of requests to reserve.
	 *
	 * @return LimitCheckResult Result object containing a localized message, limit type, and a flag indicating if the limit is exceeded.
	 */
	private function checkCloudQuotaLimit(int $requestCount): LimitCheckResult
	{
		$reservedRequest = (new LimitControlService())->reserveRequest(
			new Usage(Context::getFake()),
			$requestCount
		);

		if ($reservedRequest->isSuccess())
		{
			return $this->createLimitResult(LimitType::None, false);
		}

		$errorLimit = $reservedRequest->getErrorLimit();
		//right 4 in board
		if ($errorLimit === ErrorLimit::BAAS_RATE_LIMIT)
		{
			return $this->createLimitResult(
				LimitType::Rate,
				true,
				self::buildLimitMessage(MessageCode::BaasRateLimit)
			);
		}

		$typeLimit = $reservedRequest->getTypeLimit();
		if ($errorLimit === ErrorLimit::BAAS_LIMIT && Bitrix24::isMarketAvailable())
		{
			//right 3 in board
			return $this->createLimitResult(
				LimitType::Market,
				true,
				self::buildLimitMessage(MessageCode::Market, SliderCode::Market)
			);
		}

		if ($typeLimit === TypeLimit::BAAS)
		{
			//right 2 in board
			return $this->createLimitResult(
				LimitType::Baas,
				true,
				self::buildLimitMessage(MessageCode::Baas, SliderCode::BoostCopilot)
			);
		}

		//right 1 in board
		if (Loader::includeModule('baas') && Baas::getInstance()->isAvailable())
		{
			return $this->createLimitResult(
				LimitType::Promo,
				true,
				self::buildLimitMessage(MessageCode::Promo, SliderCode::BoostCopilot)
			);
		}

		$promoLimitCode = $reservedRequest->getPromoLimitCode();
		//left 1 in board
		if ($promoLimitCode === PromoLimitCode::Daily->value)
		{
			return $this->createLimitResult(
				LimitType::Daily,
				true,
				self::buildLimitMessage(MessageCode::Daily, SliderCode::Daily)
			);
		}

		//left 2 in board
		if ($promoLimitCode === PromoLimitCode::Monthly->value)
		{
			return $this->createLimitResult(
				LimitType::Monthly,
				true,
				self::buildLimitMessage(MessageCode::Monthly, SliderCode::Monthly)
			);
		}

		return $this->createLimitResult(LimitType::None, false);
	}

	/**
	 * On-premise (box) reservation of request quota.
	 *
	 * @param int $requestCount Number of requests to reserve.
	 *
	 * @return LimitCheckResult Result object containing a localized message, limit type, and a flag indicating if the limit is exceeded.
	 *
	 * @throws GenerationException When request reservation fails due to argument or object not found exceptions.
	 */
	private function checkBoxQuotaLimit(int $requestCount): LimitCheckResult
	{
		$cloudConfig = new Cloud\Configuration();
		$registrationDto = $cloudConfig->getCloudRegistrationData();
		if (!$registrationDto)
		{
			//top in board
			return $this->createLimitResult(
				LimitType::Unregistered,
				true,
				self::buildLimitMessage(MessageCode::CloudRegistration)
			);
		}

		try
		{
			$reservedBoxRequest = (new LimitControlBoxService())->isAllowedQuery($requestCount);
		}
		catch (ArgumentException|ObjectNotFoundException|NotFoundExceptionInterface)
		{
			throw new GenerationException(GenerationErrors::notSendRequest);
		}

		if (!$reservedBoxRequest)
		{
			throw new GenerationException(GenerationErrors::notSendRequest);
		}

		if ($reservedBoxRequest->isSuccess())
		{
			return $this->createLimitResult(LimitType::None, false);
		}

		$limitError = $reservedBoxRequest->getErrorByLimit();

		if ($limitError === ErrorLimit::RATE_LIMIT)
		{
			//top in board
			return $this->createLimitResult(
				LimitType::Rate,
				true,
				self::buildLimitMessage(MessageCode::Rate, null, HelpdeskCode::Rate)
			);
		}

		if ($limitError === ErrorLimit::BAAS_LIMIT)
		{
			if (Loader::includeModule('baas') && Baas::getInstance()->isAvailable())
			{
				//right 1 in board
				return $this->createLimitResult(
					LimitType::Promo,
					true,
					self::buildLimitMessage(MessageCode::Promo, SliderCode::BoostCopilotBox)
				);
			}

			//right 3 in board
			return $this->createLimitResult(
				LimitType::Promo,
				true,
				self::buildLimitMessage(MessageCode::Promo, SliderCode::Box)
			);
		}

		$typeLimit = $reservedBoxRequest->getTypeLimit();
		if ($typeLimit === TypeLimit::BAAS)
		{
			//right 2 in board
			return $this->createLimitResult(
				LimitType::Baas,
				true,
				self::buildLimitMessage(MessageCode::Baas, SliderCode::BoostCopilotBox)
			);
		}

		if ($limitError === ErrorLimit::PROMO_LIMIT)
		{
			//left 1 in board
			return $this->createLimitResult(
				LimitType::Monthly,
				true,
				self::buildLimitMessage(MessageCode::Monthly, SliderCode::RequestBox)
			);
		}

		return $this->createLimitResult(LimitType::None, false);
	}

	/**
	 * Creates a LimitCheckResult object.
	 *
	 * @param LimitType $limitType Type of the limit that was exceeded, or null if not applicable.
	 * @param bool $isExceeded True if the limit is exceeded, false otherwise.
	 * @param string|null $message Localized message for the user, or null if no limit is exceeded.
	 *
	 * @return LimitCheckResult
	 */
	private function createLimitResult(LimitType $limitType, bool $isExceeded, ?string $message = null): LimitCheckResult
	{
		if ($message === null)
		{
			return new LimitCheckResult($limitType, $isExceeded);
		}

		return new LimitCheckResult($limitType, $isExceeded, $message);
	}

	/**
	 * Returns the final localized message with substituted links for limits.
	 *
	 * @param MessageCode $phraseCode Localization phrase code.
	 * @param SliderCode|null $featurePromoterCode Optional feature promoter code for link substitution.
	 * @param HelpdeskCode|null $helpdeskCode Optional helpdesk code for help link substitution.
	 *
	 * @return string Localized message with links.
	 */
	private static function buildLimitMessage(
		MessageCode  $phraseCode,
		?SliderCode $featurePromoterCode = null,
		?HelpdeskCode $helpdeskCode = null,
	): string
	{
		if ($featurePromoterCode !== null)
		{
			return Loc::getMessage($phraseCode->value, [
				'#LINK#' => "[url=/?FEATURE_PROMOTER=$featurePromoterCode->value]",
				'#/LINK#' => '[/url]',
			]);
		}

		if ($helpdeskCode !== null)
		{
			$helpUrl = Util::getArticleUrlByCode($helpdeskCode->value);

			return Loc::getMessage($phraseCode->value, [
				'#HELP#' => "[url=$helpUrl]",
				'#/HELP#' => '[/url]',
			]);
		}

		return Loc::getMessage($phraseCode->value);
	}
}