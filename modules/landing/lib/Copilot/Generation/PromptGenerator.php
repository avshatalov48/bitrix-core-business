<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation;

use Bitrix\Landing\Copilot\Data\Node\Node;
use Bitrix\Landing\Copilot\Data\Site;

class PromptGenerator
{
	private PromptTemplateProvider $templateProvider;
	private ?Node $node;
	private ?string $template;
	private string $style;
	private string $color;
	private string $siteTopic;

	public function __construct(PromptTemplateProvider $templateProvider, Site $site, ?Node $node = null)
	{
		$this->templateProvider = $templateProvider;
		$this->node = $node;
		$this->style = $site->getImageStyle();
		$this->color = $site->getColors()->themeName;
		$this->siteTopic = $site->getSiteTopic();
	}

	/**
	 * The main method for composing a prompt
	 *
	 * @param array $prompts
	 *
	 * @return array updated prompts
	 */
	public function getUpdatedPromptTexts(array $prompts): array
	{
		$this->initTemplate();

		$updatedPrompts = [];
		foreach ($prompts as $prompt)
		{
			$updatedPrompts[] = str_replace(
				['{#prompt}', '{#color}', '{#style}', '{#siteTopic}'],
				[$prompt, $this->color, $this->style, $this->siteTopic],
				$this->template
			);
		}

		return $updatedPrompts;
	}

	/**
	 * Get random reserve prompt
	 * Get random reserve prompt
	 *
	 * @return string reserve prompt
	 */
	public function getRandomReservePromptText(): string
	{
		$this->template = $this->templateProvider->getRandomReserveTemplate();
		$this->prepareTemplate();

		return str_replace(
			['{#color}', '{#style}', '{#siteTopic}'],
			[$this->color, $this->style, $this->siteTopic],
			$this->template,
		);
	}

	/**
	 * Template selection method
	 */
	protected function initTemplate(): void
	{
		if ($this->node === null)
		{
			$this->template = $this->templateProvider->getPreviewTemplate();
		}
		elseif (method_exists($this->node, 'isEditInStyle') && $this->node->isEditInStyle())
		{
			$this->template = $this->templateProvider->getBgTemplate();
		}
		else
		{
			$this->template = $this->templateProvider->getBaseTemplate();
		}

		$this->prepareTemplate();
	}

	private function prepareTemplate(): void
	{
		$placeholders = [
			'#color' => $this->color,
			'#style' => $this->style,
			'#siteTopic' => $this->siteTopic,
		];

		foreach ($placeholders as $key => $value)
		{
			if ($value === '')
			{
				$pattern = sprintf('/\[[^]]*{%s}[^]]*]/', $key);
				$this->template = preg_replace($pattern, '', $this->template);
			}
		}

		$this->template = str_replace(['[', ']'], '', $this->template);
	}
}
