<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Hook\Page\B24button;
use Bitrix\Landing\LandingBlock;
use Bitrix\Landing\Site;
use Bitrix\Landing\Site\Type;
use Bitrix\Landing\Landing;

class OlWidgetBlock extends LandingBlock
{
	public function init(array $params = [])
	{
		$site = Site::getList([
			'select' => ['CODE'],
			'filter' => [
				'ID' => $params['site_id']
			]
		])->fetch();
		if (!$site)
		{
			return;
		}

		$this->params['SITE_TYPE'] = Type::getSiteSpecialType($site['CODE']);

		$hooks = Site::getHooks($params['site_id']);
		$hooksLanding = Landing::getHooks($params['landing_id']);

		if (
			!array_key_exists('B24BUTTON', $hooks)
			|| !array_key_exists('B24BUTTON', $hooksLanding)
			|| !array_key_exists('B24BUTTON_CODE', $hooks['B24BUTTON']->getPageFields())
			|| !array_key_exists('B24BUTTON_CODE', $hooksLanding['B24BUTTON']->getPageFields())
		)
		{
			return; // something wrong
		}

		$buttonUsePage = $hooksLanding['B24BUTTON']->getPageFields()['B24BUTTON_USE']->getValue();
		if ($buttonUsePage === 'Y')
		{
			$buttonCode = $hooksLanding['B24BUTTON']->getPageFields()['B24BUTTON_CODE']->getValue();
		}
		else
		{
			$buttonCode = $hooks['B24BUTTON']->getPageFields()['B24BUTTON_CODE']->getValue();
		}

		if ($buttonCode === 'N')
		{
			$this->params['BUTTON_ID'] = 'N';

			return;
		}

		if ($buttonId = B24button::getButtonIdByCode($buttonCode))
		{
			$this->params['BUTTON_ID'] = (int)$buttonId;
		}
	}
}