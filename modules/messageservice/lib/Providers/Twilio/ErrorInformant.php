<?php

namespace Bitrix\MessageService\Providers\Twilio;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

class ErrorInformant
{
	protected ?int $code;
	protected int $httpStatus;
	protected ?string $message;
	protected ?string $moreInfo;

	/**
	 * @param int|null $code
	 * @param string|null $message
	 * @param string|null $moreInfo
	 */
	public function __construct(?string $message, ?int $code, ?string $moreInfo, int $httpStatus)
	{
		$this->code = $code;
		$this->message = $message;
		$this->moreInfo = $moreInfo;
		$this->httpStatus = $httpStatus;
	}

	public function getError(): Error
	{
		return new Error($this->getErrorMessage(), $this->code ?? 0);
	}

	protected function getErrorMessage(): string
	{

		$str = Loc::getMessage('MESSAGESERVICE_PROVIDER_TWILIO_ERROR_INFORMANT_ERROR', [
			'#BR#' => '<br>',
		]);

		if (isset($this->moreInfo))
		{
			$str .= Loc::getMessage('MESSAGESERVICE_PROVIDER_TWILIO_ERROR_INFORMANT_ERROR_MORE', [
				'#LINKSTART#' => '<a href="' . $this->moreInfo . '" target="_blank">',
				'#INFO#' => $this->moreInfo,
				'#LINKEND#' => '</a>',
			]);

			return $str;
		}

		if (isset($this->message, $this->code))
		{
			$str .= Loc::getMessage('MESSAGESERVICE_PROVIDER_TWILIO_ERROR_INFORMANT_ERROR_CODE', [
				'#CODE#' => $this->code,
				'#MESSAGE#' => $this->message,
			]);

			return $str;
		}

		$str .= Loc::getMessage('MESSAGESERVICE_PROVIDER_TWILIO_ERROR_INFORMANT_ERROR_HTTP_STATUS', [
			'#STATUS#' => $this->httpStatus,
		]);

		return $str;
	}
}