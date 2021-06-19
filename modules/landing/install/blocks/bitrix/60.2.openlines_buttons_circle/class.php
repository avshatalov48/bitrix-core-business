<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Hook\Page\B24button;
use Bitrix\Landing\LandingBlock;
use Bitrix\Landing\Site;

class WidgetBlock extends LandingBlock
{
	public function init(array $params = [])
	{
		$hooks = Site::getHooks($params['site_id']);
		if (
			!array_key_exists('B24BUTTON', $hooks)
			|| !array_key_exists('B24BUTTON_CODE', $hooks['B24BUTTON']->getPageFields())
		)
		{
			return; // something wrong
		}

		$buttonCode = $hooks['B24BUTTON']->getPageFields()['B24BUTTON_CODE']->getValue();
		if ($buttonCode === 'N')
		{
			$this->params['BUTTON_ID'] = 'N';

			return;
		}

		if ($buttonId = B24button::getButtonIdByCode($buttonCode))
		{
			$this->params['BUTTON_ID'] = $buttonId;
		}
	}
}