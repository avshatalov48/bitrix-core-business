<?php
namespace Bitrix\Translate\Ui;

use Bitrix\Main;
use Bitrix\Main\Localization;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;


class Panel
{
	const DIALOG_ID = 'jsTranslateFilesWindow';

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

		if (!$USER instanceof \CUser || !$USER->isAuthorized())
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
			$checked = false;
			if (isset($_SESSION['SHOW_LANG_FILES']))
			{
				$cmd = $_SESSION['SHOW_LANG_FILES'] == 'Y' ? 'N' : 'Y';
				$checked = ($_SESSION['SHOW_LANG_FILES'] == 'Y');
			}

			$url = $APPLICATION->getCurPageParam('show_lang_files='.$cmd, array('show_lang_files'));
			$menu = array(
				array(
					'TEXT' => Loc::getMessage('TRANSLATE_SHOW_LANG_FILES_TEXT'),
					'TITLE' => Loc::getMessage('TRANSLATE_SHOW_LANG_FILES_TITLE'),
					'CHECKED' => $checked,
					'LINK' => $url,
					'DEFAULT' => false,
				),
			);
			if ($checked)
			{
				$menu[] = array(
					'TEXT' => Loc::getMessage('TRANSLATE_SHOW_LOADED_LANG_FILES_TEXT'),
					'TITLE' => Loc::getMessage('TRANSLATE_SHOW_LOADED_LANG_FILES_TITLE'),
					'ONCLICK' => self::DIALOG_ID.'.Show()',
					'DEFAULT' => false,
				);
			}

			$APPLICATION->addPanelButton(array(
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

	/**
	 * Shows included lang files.
	 * @return void
	 */
	public static function showLoadedFiles()
	{
		/**
		 * @global \CUser $USER
		 * @global \CMain $APPLICATION
		 */
		global $APPLICATION, $USER;

		if (!$USER instanceof \CUser || !$USER->isAuthorized())
		{
			return;
		}

		if (!Translate\Permission::canView($USER))
		{
			return;
		}

		if (($_SESSION["SHOW_LANG_FILES"] ?? '') !== 'Y')
		{
			return;
		}

		$request = Main\Context::getCurrent()->getRequest();

		// ajax
		if ($request->isAjaxRequest() || $request->get('AJAX_CALL') !== null)
		{
			return;
		}

		$searchString = $request->get('srchlngfil');

		\CJSCore::init('admin_interface');
		if (!defined("ADMIN_SECTION"))
		{
			$APPLICATION->setAdditionalCSS('/bitrix/themes/.default/pubstyles.css');
		}

		$popup = new \CJSPopupOnPage();
		$popup->jsPopup = self::DIALOG_ID;

		?>
		<script>
			var <?= self::DIALOG_ID ?> = new BX.CDebugDialog();
		</script>
		<?

		$popup->startDescription('bx-translate-search');
		$popup->startContent(array('buffer' => true));


		if(!defined('BX_PUBLIC_MODE'))
		{
			?>
			<p>
				<input type="text" size="50" class="typeinput" name="srchlngfil" value="<?= \htmlspecialcharsbx($searchString) ?>">
				<input type="submit" class="button" value="OK">
			</p>
			<?
		}

		?>
		<div id="BX_TRANSLATE_FILES">
			<style type="text/css">
				.bx-translate-files-table {width: 100%; border: none; border-spacing:0; }
				.bx-translate-files-table td {padding: 0 4px 4px 0; border:none; vertical-align:top; }
				.bx-component-debug.bx-debug-summary.bx-translate-debug-summary { left:unset; right: 10px; }
			</style>
			<table class="bx-translate-files-table">
				<?

				$includedLangFiles = Loc::getIncludedFiles();
				if (!empty($includedLangFiles))
				{
					$includedLangFiles = \array_values($includedLangFiles);
				}
				$lowPriorityLangFiles = array();
				$highPriorityLangFiles = array();
				foreach ($includedLangFiles as $langFile)
				{
					$langFile = Main\IO\Path::normalize($langFile);

					if (Localization\Translation::useTranslationRepository() && \in_array(\LANGUAGE_ID, Translate\Config::getTranslationRepositoryLanguages()))
					{
						if (\mb_strpos($langFile, Localization\Translation::getTranslationRepositoryPath()) === 0)
						{
							$langFile = \str_replace(
								Localization\Translation::getTranslationRepositoryPath().'/'.\LANGUAGE_ID.'/',
								'/bitrix/modules/',
								$langFile
							);
						}
					}
					if (Localization\Translation::getDeveloperRepositoryPath() !== null)
					{
						if (\mb_strpos($langFile, Localization\Translation::getDeveloperRepositoryPath()) === 0)
						{
							$langFile = \str_replace(
								Localization\Translation::getDeveloperRepositoryPath(). '/',
								'/bitrix/modules/',
								$langFile
							);
						}
					}
					if (\mb_strpos($langFile, Main\Application::getDocumentRoot()) === 0)
					{
						$langFile = \str_replace(
							Main\Application::getDocumentRoot(). '/',
							'/',
							$langFile
						);
					}
					if (empty($langFile))
					{
						continue;
					}

					if(
						(\mb_strpos($langFile, "/menu") !== false) ||
						(\mb_strpos($langFile, "/classes") !== false) ||
						(\mb_strpos($langFile, "tools.") !== false) ||
						(\mb_strpos($langFile, "/include.") !== false) ||
						(\mb_strpos($langFile, "menu_template.php") !== false) ||
						(\mb_strpos($langFile, ".menu.") !== false) ||
						(\mb_strpos($langFile, "/top_panel.php") !== false) ||
						(\mb_strpos($langFile, "prolog_main_admin.php") !== false) ||
						(\mb_strpos($_SERVER["REQUEST_URI"], "/iblock_") === false && \mb_strpos($langFile, "/modules/iblock/lang/")!==false)
					)
					{
						$lowPriorityLangFiles[] = $langFile;
					}
					else
					{
						$highPriorityLangFiles[] = $langFile;
					}
				}


				$lowPriorityLangFiles = \array_unique($lowPriorityLangFiles);
				$highPriorityLangFiles = \array_unique($highPriorityLangFiles);

				\asort($lowPriorityLangFiles);
				\reset($lowPriorityLangFiles);

				$highPriorityLangFiles = \array_reverse($highPriorityLangFiles, true);

				$includedLangFiles = \array_merge($highPriorityLangFiles, $lowPriorityLangFiles);

				if ($searchString !== null)
				{
					$lookForCode = \preg_match("/[a-z1-9_]+/i", $searchString);
				}

				foreach ($includedLangFiles as $langFile)
				{
					$stf = '';

					if ($searchString !== null)
					{
						$found = false;

						$filePath = Localization\Translation::convertLangPath($_SERVER["DOCUMENT_ROOT"]. $langFile, \LANGUAGE_ID);
						if (\file_exists($filePath))
						{
							$filePath = \str_replace('/lang/'.\LANGUAGE_ID.'/', '/', $langFile);
							$messages = Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"]. $filePath, \LANGUAGE_ID);

							$stf = "";
							foreach ($messages as $code => $phrase)
							{
								if (
									$lookForCode && \mb_strpos($code, $searchString) !== false ||
									\mb_strpos($phrase, $searchString) !== false
								)
								{
									$found = true;
									$highlight = "&highlight=". \preg_replace("/[^a-z1-9_]+/i", '', $code);
									$stf .= '<a href="/bitrix/admin/translate_edit.php?lang='.\LANGUAGE_ID.'&file='.$langFile. $highlight.'">'.
											\htmlspecialcharsbx($phrase).
											'</a><br> ';
								}
							}
						}
						if (!$found)
						{
							continue;
						}
					}
					?>
					<tr>
						<td><a href="/bitrix/admin/translate_edit.php?lang=<?= \LANGUAGE_ID ?>&file=<?= $langFile ?>"><?= $langFile ?></a></td>
						<td><?= $stf ?></td>
					</tr>
					<?

				}
				?>
			</table>
		</div>
		<?
		$popup->endContent();

		$popup->startButtons();
		$popup->showStandardButtons(array('close'));

		if ($searchString !== null)
		{
			?>
			<script>BX.ready(function(){ <?= self::DIALOG_ID ?>.Show(); });</script>
			<?
		}

		if (defined("ADMIN_SECTION"))
		{
			?>
			<style>
				div.bx-component-debug {border:1px solid red; font-size:11px; color:black; background-color:white; text-align:left; }
				div.bx-component-debug a, div.bx-component-debug a:visited {color:blue; text-decoration:none;}
				div.bx-component-debug a:hover {color:red; text-decoration:underline}
				div.bx-debug-summary {margin:5px; width:300px; padding:5px; position:fixed; bottom:10px; left:10px; z-index:1000; opacity: 0.4;}
				div.bx-debug-summary:hover {opacity: 1;}
			</style>
			<?
		}
		?>
		<div class="bx-component-debug bx-debug-summary bx-translate-debug-summary">
			<?= Loc::getMessage("TRANSLATE_COUNT_LOADED_LANG_FILES") ?>: <?= count($includedLangFiles) ?><br>
			<a title="<?= Loc::getMessage("TRANSLATE_SHOW_LOADED_LANG_FILES_TITLE") ?>" href="javascript:<?= self::DIALOG_ID ?>.Show();">
				<?= Loc::getMessage("TRANSLATE_SHOW_LOADED_LANG_FILES_TEXT") ?>
			</a><br>
		</div>
		<?
	}
}

