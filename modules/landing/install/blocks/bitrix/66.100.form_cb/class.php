<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Block;
use Bitrix\Landing\LandingBlock;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Subtype;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialservices\ApClient;

class CallbackFormBlock extends LandingBlock
{
	protected const CALLBACK_MARKER_PREFIX = '#crmFormCallback';

	/**
	 * Before every view checked:
	 * If form ID already replaced - do nothing.
	 * If first time - replace by first CB form
	 * If not form - show alert
	 *
	 * @param Block $block Block instance.
	 * @return void
	 */
	public function beforeView(Block $block)
	{
		$content = $block->getContent();
		if(strpos($content, self::CALLBACK_MARKER_PREFIX) !== false)
		{
			if(($forms = Subtype\Form::getCallbackForms()))
			{
				$block->saveContent(str_replace(
					self::CALLBACK_MARKER_PREFIX,
					Subtype\Form::POPUP_MARKER_PREFIX . array_shift($forms)['ID'],
					$content
				));
				$block->save();
			}
			else
			{
 				if(Loader::includeModule('crm'))
				{
					$desc = Loc::getMessage('LNDNGBLCK_CALLBACK_ERR_DESC', [
						'#LINK1#' => '/telephony/',
						'#LINK2#' => '/crm/webform/',
					]);
				}
				elseif (Manager::isB24Connector())
				{
					//todo check
					$portalUrl = ApClient::init() ? ApClient::init()->getConnection()['ENDPOINT'] : '';
					$desc = Loc::getMessage('LNDNGBLCK_CALLBACK_ERR_DESC_CONNECTOR', [
						'#LINK1#' => $portalUrl . '/telephony/',
						'#LINK2#' => $portalUrl . '/crm/webform/',
					]);
				}
				else
				{
					$desc = Loc::getMessage('LNDNGBLCK_CALLBACK_ERR_DESC_NO_CONNECTOR', [
						'#LINK1#' => '/bitrix/admin/module_admin.php',
					]);
				}
				$block->setRuntimeRequiredUserAction([
					'header' => Loc::getMessage('LNDNGBLCK_CALLBACK_ERR_TITLE'),
					'description' => $desc,
					'text' => Loc::getMessage('LNDNGBLCK_CALLBACK_SETTINGS'),
				]);

			}
		}
	}
}