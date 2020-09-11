<?php

namespace Bitrix\Landing\Update\Block;

use Bitrix\Landing\Internals\BlockTable;

class Buttons
{
	protected const BTN_CLASS_MATCHER = '/class=(["\'][^"\']*\s|["\'])btn(\s[^"\']*["\']|["\'])/i';
	protected const OLD_BUTTON_MATCHER = '/class=["\'][^"\']*u-btn-\w+[^"\']*["\']/i';
	protected const BUTTON_COLOR_MATCHER = '/(?<=[\s\'"])u-btn-(\w+)([\s\'"])/i';
	protected const BUTTON_COLOR_OUTLINE_MATCHER = '/(?<=[\s\'"])u-btn-outline-(\w+)([\s\'"])/i';
	protected const BUTTON_COLOR_THEME_MATCHER = '/(?<=[\s\'"])u-(theme-bitrix-btn-v)(\d{1,})([\s\'"])/i';
	protected const BUTTON_COLOR_THEME_OUTLINE_MATCHER = '/(?<=[\s\'"])u-(theme-bitrix-btn-outline-v)(\d{1,})([\s\'"])/i';
	protected const BUTTON_BORDER_MATCHER = '/(?<=[\s\'"])g-brd-[\d]{1,2}([\s\'"])/i';
	protected const PADDING_MATCHER = '/(?<=[\s\'"])(g-p[abtlrxy]-\d{1,3}(--\w\w)?)([\s\'"])/i';

	/**
	 * @var integer
	 */
	protected $blockId;
	/**
	 * @var string
	 */
	protected $content;

	public function __construct($blockId, $content)
	{
		$this->blockId = $blockId;
		$this->content = $content;
	}

	public function update(): void
	{
		preg_match_all(self::BTN_CLASS_MATCHER, $this->content, $contentMatches);
		$newContentMatches = [];
		foreach ($contentMatches[0] as $contentMatchesItem)
		{
			$contentMatchesItem = $this->changeColor($contentMatchesItem);
			$contentMatchesItem = $this->changeBorder($contentMatchesItem);
			$contentMatchesItem = $this->changeRound($contentMatchesItem);
			$contentMatchesItem = $this->changePadding($contentMatchesItem);
			array_push($newContentMatches, $contentMatchesItem);
		}

		$this->content = str_replace($contentMatches[0], $newContentMatches, $this->content);
		$this->save();
	}

	protected function save(): void
	{
		BlockTable::update(
			$this->blockId,
			[
				'CONTENT' => $this->content
			]
		);
	}

	protected function changeColor($content)
	{
		$content = preg_replace(self::BUTTON_COLOR_MATCHER, 'g-btn-$1 g-btn-type-solid$2', $content);
		$content = preg_replace(self::BUTTON_COLOR_OUTLINE_MATCHER, 'g-btn-$1 g-btn-type-outline$2', $content);
		$content = preg_replace(self::BUTTON_COLOR_THEME_MATCHER, 'g-$1$2 g-btn-type-solid$3', $content);
		$content = preg_replace(self::BUTTON_COLOR_THEME_OUTLINE_MATCHER, 'g-$1$2 g-btn-type-outline$3', $content);
		return $content;
	}

	protected function changeBorder($content)
	{
		$content = preg_replace(self::BUTTON_BORDER_MATCHER, '$1', $content);
		return $content;
	}

	protected function changeRound($content)
	{
		$content = str_replace('g-rounded-50x', 'g-rounded-50', $content);
		return $content;
	}

	protected function changePadding($content)
	{
		$content = preg_replace(self::PADDING_MATCHER, '$3', $content);
		return $content;
	}

	public static function isOldButton($content): bool
	{
		return (bool) preg_match(self::OLD_BUTTON_MATCHER, $content);
	}

	public static function updateLanding(int $lid): void
	{
		$res = BlockTable::getList(
			[
				'select' => [
					'ID', 'CONTENT'
				],
				'filter' => [
					'LID' => $lid,
				],
			]
		);
		while ($row = $res->fetch())
		{
			if(self::isOldButton($row['CONTENT']))
			{
				$buttonBlock = new self($row['ID'], $row['CONTENT']);
				$buttonBlock->update();
			}
		}
	}

}