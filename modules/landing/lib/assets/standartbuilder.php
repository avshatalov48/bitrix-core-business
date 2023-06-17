<?php

namespace Bitrix\Landing\Assets;

use Bitrix\Main\Localization\Loc;

class StandartBuilder extends Builder
{
	/**
	 * Add assets output at the page
	 */
	public function setOutput(): void
	{
		if ($this->resources->isEmpty())
		{
			return;
		}

		$this->normalizeResources();
		$this->initResourcesAsJsExtension($this->normalizedResources);

		$this->setStrings();
	}

	protected function normalizeResources(): void
	{
		$this->normalizedResources = $this->resources->getNormalized();
		$this->normalizeLangResources();
	}

	protected function normalizeLangResources(): void
	{
		$langResources = $this->normalizedResources[Types::TYPE_LANG] ?? null;
		if (isset($langResources) && !empty($langResources))
		{
			// convert array to string (get first element)
			$this->normalizedResources[Types::TYPE_LANG] = $this->normalizedResources[Types::TYPE_LANG][0];

			// other files load by additional lang
			if ($additionalLang = self::loadAdditionalLangPhrases(array_slice($langResources, 1)))
			{
				$this->normalizedResources[Types::TYPE_LANG_ADDITIONAL] = $additionalLang;
			}
		}
	}

	protected static function loadAdditionalLangPhrases(array $langResources): array
	{
		$additionalLangPhrases = [];
		foreach ($langResources as $file)
		{
			foreach (Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'] . $file) as $key => $phrase)
			{
				$additionalLangPhrases[$key] = $phrase;
			}
		}

		return $additionalLangPhrases;
	}
}