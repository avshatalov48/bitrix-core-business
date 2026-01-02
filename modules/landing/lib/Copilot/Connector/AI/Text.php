<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\AI;

use Bitrix\Main;
use Bitrix\Landing;
use Bitrix\AI;
use Bitrix\AI\Payload;
use Bitrix\AI\Tuning;

class Text extends BaseConnector
{
	protected const PROMPT_CODE = 'zero_prompt';
	protected const CATEGORY = 'text';
	protected const CONTEXT_ID = 'landing_site_copilot_text';

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

		$promptCode = $prompt->getCode();
		$payload = new Payload\Prompt($promptCode);
		$payload->setPromptCategory(static::PROMPT_CATEGORY);
		$payload->setMarkers($prompt->getMarkers());

		$this->engine->setPayload($payload);
		$this->engine->setParameters($this->getParams());
		$this->engine->setUserParameters($this->getUserParams());
		$this->engine->setHistoryState(true);

		$this->engine
			->onSuccess(function (AI\Result $aiResult, ?string $hash = null) use (&$result){
				if ($hash)
				{
					$result->setData(['hash' => $hash]);
				}
				else
				{
					$result->setData(['result' => $aiResult->getPrettifiedData()]);
				}
			})
			->onError(function (Main\Error $error) use (&$result)
			{
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

		return $manager->getItem(Landing\Connector\Ai::TUNING_CODE_SITE_TEXT_PROVIDER)?->getValue();
	}

	private function getParams(): array
	{
		return [
			'max_tokens' => self::MAX_TOKENS,
		];
	}

	private function getUserParams(): array
	{
		return [
			'promptCategory' => '',
			'temperature' => $this->temperature,
		];
	}
}