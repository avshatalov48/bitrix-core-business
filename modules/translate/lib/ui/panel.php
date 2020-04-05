<?php
namespace Bitrix\Translate\Ui;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;


class Panel
{
	/**
	 * main::OnPanelCreate event handler.
	 * @return void
	 */
	public static function onPanelCreate()
	{
		/**
		 * @global \CUser $USER
		 * @global \CMain $APPLICATION
		 */
		global $APPLICATION, $USER;

		if (!$USER instanceof \CUser || !$USER->IsAuthorized())
		{
			return;
		}

		if (!Translate\Permission::canView($USER))
		{
			return;
		}

		if (Translate\Config::getOption(Translate\Config::OPTION_BUTTON_LANG_FILES) === 'Y')
		{
			Loc::loadLanguageFile(__FILE__);

			$cmd = 'Y';
			$checked = 'N';
			if (isset($_SESSION['SHOW_LANG_FILES']))
			{
				$cmd = $_SESSION['SHOW_LANG_FILES'] == 'Y' ? 'N' : 'Y';
				$checked = $_SESSION['SHOW_LANG_FILES'] == 'Y' ? 'Y' : 'N';
			}

			$url = $APPLICATION->GetCurPageParam('show_lang_files='.$cmd, array('show_lang_files'));
			$menu = array(
				array(
					'TEXT' => Loc::getMessage('TRANSLATE_SHOW_LANG_FILES_TEXT'),
					'TITLE' => Loc::getMessage('TRANSLATE_SHOW_LANG_FILES_TITLE'),
					'CHECKED' => ($checked == 'Y'),
					'LINK' => $url,
					'DEFAULT' => false,
				));

			$APPLICATION->AddPanelButton(array(
				'HREF' => '',
				'ID' => 'translate',
				'ICON' => 'bx-panel-translate-icon',
				'ALT' => Loc::getMessage('TRANSLATE_ICON_ALT'),
				'TEXT' => Loc::getMessage('TRANSLATE_ICON_TEXT'),
				'MAIN_SORT' => '1000',
				'SORT' => 50,
				'MODE' => array('configure'),
				'MENU' => $menu,
				'HINT' => array(
					'TITLE' => Loc::getMessage('TRANSLATE_ICON_TEXT'),
					'TEXT' => Loc::getMessage('TRANSLATE_ICON_HINT'),
				),
			));
		}
	}
}
