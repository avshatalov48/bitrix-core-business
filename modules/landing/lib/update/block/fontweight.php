<?php

namespace Bitrix\Landing\Update\Block;

use Bitrix\Landing\Internals\BlockTable;

class FontWeight
{
	// todo: check this!
	protected const FONT_BOLD_MATCHER = '/font-weight-bold/i';

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
		// todo: what about partners? !!!!!!!!!!!!!
		$this->changeWeightClass();

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

	protected function changeWeightClass()
	{
		$this->content = preg_replace(self::FONT_BOLD_MATCHER, 'g-font-weight-700', $this->content);
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
			$block = new self($row['ID'], $row['CONTENT']);
			$block->update();
		}
	}

}