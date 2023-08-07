<?php

namespace Bitrix\Im\V2\Controller;

class Beta extends BaseController
{
	const BETA_NOT_AVAILABLE = 'BETA_NOT_AVAILABLE';
	/**
	 * @restMethod im.v2.Beta.enable
	 */
	public function enableAction(): ?array
	{
		if (!\Bitrix\Im\Settings::isBetaAvailable())
		{
			$this->addError(new \Bitrix\Main\Error(
				"Beta is not available for this Bitrix24.",
				self::BETA_NOT_AVAILABLE,
			));

			return null;
		}

		$result = \Bitrix\Im\Settings::setBetaActive(true);

		return [
			'result' => $result
		];
	}

	/**
	 * @restMethod im.v2.Beta.disable
	 */
	public function disableAction(): ?array
	{
		if (!\Bitrix\Im\Settings::isBetaAvailable())
		{
			$this->addError(new \Bitrix\Main\Error(
				"Beta is not available for this Bitrix24.",
				self::BETA_NOT_AVAILABLE,
			));

			return null;
		}

		$result = \Bitrix\Im\Settings::setBetaActive(false);

		return [
			'result' => $result
		];
	}
}