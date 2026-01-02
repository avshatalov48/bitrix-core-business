<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\AI;

use Bitrix\Main;
use Bitrix\Landing;
use Bitrix\AI;
use Bitrix\AI\Payload;
use Bitrix\AI\Tuning;

class Image extends BaseConnector
{
	protected const CATEGORY = 'image';
	protected const CONTEXT_ID = 'landing_site_copilot_image';

	public function request(Prompt $prompt): Main\Result
	{
		$result = new Main\Result();

		if (!$this->canRequest())
		{
			$result->addError(
				new Main\Error(
					'Ai is not available now', 'AI_UNAVAILABLE'
				)
			);

			return $result;
		}

		$this->setTemperature($prompt->getTemperature());

		$payload = new Payload\StyledPicture($prompt->getCode());
		$payload->setMarkers($prompt->getMarkers());

		$this->engine->setPayload($payload);
		$this->engine->setHistoryState(true);

		$this->engine
			->onSuccess(function (AI\Result $aiResult, ?string $hash = null) use (&$result) {
				if ($hash)
				{
					$result->setData(['hash' => $hash]);
				}
				else
				{
					$result->setData(['result' => $aiResult->getPrettifiedData()]);
				}
			})
			->onError(function (Main\Error $error) use (&$result) {
				$result->addError(new Main\Error(
					$error->getMessage(), $error->getCode()
				));
			})
			->completionsInQueue()
		;

		return $result;
	}

	protected function getEngineCode(): ?string
	{
		$manager = new Tuning\Manager();

		return $manager->getItem(Landing\Connector\Ai::TUNING_CODE_SITE_IMAGE_PROVIDER)?->getValue();
	}
}