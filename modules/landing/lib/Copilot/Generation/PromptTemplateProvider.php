<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation;

class PromptTemplateProvider
{
	protected array $previewTemplates = [
		"[{#prompt}][, for theme: {#siteTopic}][, {#style} style]",
	];
	protected array $baseTemplates = [
		"[{#prompt}][, for theme: {#siteTopic}][, color theme: {#color}][, {#style} style]",
	];
	protected array $bgTemplates = [
		"Create a minimalistic website background image[ in {#style} style][ using {#color} tones][, inspired by {#prompt} themes][ and {#siteTopic}]. The design should have soft textures or simple patterns that don’t overpower the content. Ensure the image remains uncluttered, with no objects in the center, leaving ample space for text and other elements.",
	];
	protected array $reserveTemplates = [
		"[theme: {#siteTopic}][, on background color: {#color}][, {#style} style]",
		"[theme: {#siteTopic}][, color theme: {#color}][, {#style} style]",
		"[create an image with the theme: {#siteTopic}][, using the color palette: {#color}][, in the {#style} style]",
	];

	public function getPreviewTemplate(): string
	{
		return $this->previewTemplates[0];
	}

	public function getBaseTemplate(): string
	{
		return $this->baseTemplates[0];
	}

	public function getBgTemplate(): string
	{
		return $this->bgTemplates[0];
	}

	public function getRandomReserveTemplate(): string
	{
		return $this->reserveTemplates[array_rand($this->reserveTemplates)];
	}
}