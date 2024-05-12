<?php

namespace Bitrix\UI\FeaturePromoter;

class PopupConfigurationValidator
{

	public function __construct(private array $configuration)
	{
	}

	public function isValidConfiguration(): bool
	{
		if (!is_array($this->configuration['header']) || !is_array($this->configuration['items']))
		{
			return false;
		}

		return $this->isValidHeaderConfiguration($this->configuration['header'])
			&& $this->isValidItemsConfiguration($this->configuration['items']);
	}

	private function isValidHeaderConfiguration(array $headerConfiguration): bool
	{
		if (isset($headerConfiguration['iconClass']) && !is_string($headerConfiguration['iconClass']))
		{
			return false;
		}

		return is_array($headerConfiguration['top'])
			&& $this->isValidHeaderTopConfiguration($headerConfiguration['top'])
			&& is_array($headerConfiguration['info'])
			&& $this->isValidHeaderInfoConfiguration($headerConfiguration['info']);
	}

	private function isValidHeaderTopConfiguration(array $headerTopConfiguration): bool
	{
		if (isset($headerTopConfiguration['subtitle']) && !is_string($headerTopConfiguration['subtitle']))
		{
			return false;
		}

		return is_string($headerTopConfiguration['title']);
	}

	private function isValidHeaderInfoConfiguration(array $headerInfoConfiguration): bool
	{
		if (
			(isset($headerTopConfiguration['subtitle']) && !is_string($headerTopConfiguration['subtitle']))
			|| (isset($headerTopConfiguration['subtitleDescription'])
				&& !is_string($headerTopConfiguration['subtitleDescription']))
			|| (isset($headerTopConfiguration['moreLabel']) && !is_string($headerTopConfiguration['moreLabel']))
			|| (isset($headerTopConfiguration['moreUrl']) && !is_string($headerTopConfiguration['moreUrl']))
			|| (isset($headerTopConfiguration['roundContent'])
				&& (is_string($headerTopConfiguration['roundContent'])
					|| is_array($headerTopConfiguration['roundContent']))
				&& !$this->isValidRoundContentConfiguration($headerTopConfiguration['roundContent']))
		)
		{
			return false;
		}

		return is_string($headerInfoConfiguration['title']);
	}

	private function isValidRoundContentConfiguration(array|string $roundContentConfiguration): bool
	{
		if (is_array($roundContentConfiguration))
		{
			return is_string($roundContentConfiguration['posterUrl'])
				&& is_array($roundContentConfiguration['videos'])
				&& $this->isValidVideosConfiguration($roundContentConfiguration['videos']);
		}

		return true;
	}

	private function isValidVideosConfiguration(array $videosConfiguration): bool
	{
		foreach ($videosConfiguration as $videoConfiguration)
		{
			if (!is_string($videoConfiguration['url']) && !is_string($videoConfiguration['type']))
			{
				return false;
			}
		}

		return true;
	}

	private function isValidItemsConfiguration(array $itemsConfiguration): bool
	{
		foreach ($itemsConfiguration as $itemConfiguration)
		{
			if (!is_array($itemConfiguration) || !$this->isValidItemConfiguration($itemConfiguration))
			{
				return false;
			}
		}

		return true;
	}

	private function isValidItemConfiguration(array $itemConfiguration): bool
	{
		if (
			(isset($itemConfiguration['description']) && (!is_array($itemConfiguration['description'])
				|| !$this->isValidTextConfiguration($itemConfiguration['description'])))
			|| (isset($itemConfiguration['more']) && (!is_array($itemConfiguration['more'])
				|| !$this->isValidMoreItemConfiguration($itemConfiguration['more'])))
			|| (isset($itemConfiguration['icon']) && (!is_array($itemConfiguration['icon'])
				|| !$this->isValidItemIconConfiguration($itemConfiguration['icon'])))
			|| (isset($itemConfiguration['button']) && (!is_array($itemConfiguration['button'])
				|| !$this->isValidItemButtonConfiguration($itemConfiguration['button'])))
		)
		{
			return false;
		}

		return is_array($itemConfiguration['title']) && $this->isValidTextConfiguration($itemConfiguration['title']);
	}

	private function isValidTextConfiguration(array $textConfiguration): bool
	{
		if (isset($textConfiguration['color']) && !is_string($textConfiguration['color']))
		{
			return false;
		}

		return is_string($textConfiguration['text']);
	}

	private function isValidMoreItemConfiguration(array $itemMoreConfiguration): bool
	{
		return is_array($itemMoreConfiguration['text'])
			&& $this->isValidTextConfiguration($itemMoreConfiguration['text']);
	}

	private function isValidItemIconConfiguration(array $itemIconConfiguration): bool
	{
		if (isset($itemIconConfiguration['color']) && !is_string($itemIconConfiguration['color']))
		{
			return false;
		}

		return is_string($itemIconConfiguration['name']);
	}

	private function isValidItemButtonConfiguration(array $itemButtonConfiguration): bool
	{
		if (
			(isset($itemButtonConfiguration['description'])
				&& !is_array($itemButtonConfiguration['description'])
				&& !$this->isValidTextConfiguration($itemButtonConfiguration['description']))
			|| (isset($itemButtonConfiguration['backgroundColor'])
				&& !is_string($itemButtonConfiguration['backgroundColor']))
			|| (isset($itemButtonConfiguration['onclick']) && !is_string($itemButtonConfiguration['onclick']))
			|| (isset($itemButtonConfiguration['url']) && !is_string($itemButtonConfiguration['url']))
		)
		{
			return false;
		}

		return is_string($itemButtonConfiguration['text']);
	}
}