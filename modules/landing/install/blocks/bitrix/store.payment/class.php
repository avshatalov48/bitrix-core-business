<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

use Bitrix\Landing\Block;

class StorePaymentBlock extends \Bitrix\Landing\LandingBlock
{
	/**
	 * Method, which executes just before block.
	 *
	 * @param Block $block Block instance.
	 * @return bool
	 */
	public function beforeView(Block $block): bool
	{
		if ($this->getEditMode())
		{
			return false;
		}

		return true;
	}
}
