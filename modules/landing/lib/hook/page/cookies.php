<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Help;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Field;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;

class Cookies extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Work modes.
	 */
	const MODE_A = 'A';
	const MODE_I = 'I';

	/**
	 * Cookies server is enabled.
	 * @var bool
	 */
	public static $enabled = false;

	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap(): array
	{
		$helpUrl = Help::getHelpUrl('COOKIES_EDIT');

		return [
			'USE' => new Field\Checkbox('USE', [
				'title' => Loc::getMessage('LANDING_HOOK_COOKIES_USE')
			]),
			'AGREEMENT_ID' => new Field\Checkbox('AGREEMENT_ID', [
				'title' => Loc::getMessage('LANDING_HOOK_COOKIES_AGREEMENT_ID')
			]),
			'COLOR_BG' => new Field\Text('COLOR_BG', [
				'title' => Loc::getMessage('LANDING_HOOK_COOKIES_COLOR_BG'),
				'default' => '#03c1fe'
			]),
			'COLOR_TEXT' => new Field\Text('COLOR_TEXT', [
				'title' => Loc::getMessage('LANDING_HOOK_COOKIES_COLOR_TEXT'),
				'default' => '#fff'
			]),
			'POSITION' => new Field\Select('POSITION', [
				'title' => Loc::getMessage('LANDING_HOOK_COOKIES_POSITION'),
				'default' => 'bottom_left',
				'options' => [
					'bottom_left' => Loc::getMessage('LANDING_HOOK_COOKIES_POSITION_BL'),
					'bottom_right' => Loc::getMessage('LANDING_HOOK_COOKIES_POSITION_BR'),
				]
			]),
			'MODE' => new Field\Select('MODE', [
				'title' => Loc::getMessage('LANDING_HOOK_COOKIES_MODE'),
				'default' => 'A',
				'options' => [
					self::MODE_A => Loc::getMessage('LANDING_HOOK_COOKIES_MODE_A'),
					self::MODE_I => Loc::getMessage('LANDING_HOOK_COOKIES_MODE_I'),
				],
				'help' => $helpUrl
					?   '<a href="' . $helpUrl . '" target="_blank">' .
							Loc::getMessage('LANDING_HOOK_COOKIES_MODE_HELP') .
						'</a>'
					: ''
			])
		];
	}

	/**
	 * Hook title.
	 * @return string
	 */
	public function getTitle(): string
	{
		return Loc::getMessage('LANDING_HOOK_COOKIES_TITLE');
	}

	/**
	 * Exec or not hook in edit mode.
	 * @return boolean
	 */
	public function enabledInEditMode(): bool
	{
		return false;
	}

	/**
	 * Get sort of block (execute order).
	 * @return int
	 */
	public function getSort()
	{
		return 50;
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled(): bool
	{
		// this hook are enabled always
		return !$this->isPage();
	}

	/**
	 * Returns true if current mode is information only.
	 * @return bool
	 */
	public function isInformationMode(): bool
	{
		$mode = $this->fields['MODE']->getValue();
		return $mode == self::MODE_I || (!$mode && Manager::availableOnlyForZone('ru'));
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec(): void
	{
		if ($this->execCustom())
		{
			return;
		}

		if ($this->fields['USE']->getValue() == 'Y')
		{
			$infoMode = $this->isInformationMode();

			if (!$infoMode)
			{
				self::$enabled = true;
				Manager::clearPageView('Noscript');
			}

			ob_start();
			Manager::getApplication()->includeComponent(
				'bitrix:landing.cookies',
				'',
				[
					'USE' => $this->fields['USE']->getValue(),
					'POSITION' => $this->fields['POSITION']->getValue(),
					'COLOR_BG' => $this->fields['COLOR_BG']->getValue(),
					'COLOR_TEXT' => $this->fields['COLOR_TEXT']->getValue(),
					'AGREEMENT_ID' => $this->fields['AGREEMENT_ID']->getValue(),
					'INFORMATION' =>  $infoMode ? 'Y' : 'N'
				],
				false
			);
			$hookContent = ob_get_contents();
			ob_end_clean();

			echo $hookContent;
		}
	}

	/**
	 * Set cookies js code on the page. Immediately if service off and after load otherwise.
	 * @param string $cookieCode Cookie unique code.
	 * @param string $functionBody JS function body.
	 * @return void
	 */
	public static function addCookieScript(string $cookieCode, string $functionBody): void
	{
		if (self::$enabled)
		{
			Manager::setPageView('AfterHeadOpen',
                '<script data-skip-moving="true">
					window["bxCookies"] = window["bxCookies"] || {};
					window["bxCookies"]["' . $cookieCode . '"] = false;
					window.addEventListener("load", function() {
						BX.addCustomEvent(
							"BX.Landing.Cookies:onAccept", 
							function(acceptedCookies)
							{
								if (
									!window["bxCookies"]["' . $cookieCode . '"] && 
									BX.util.in_array("' . $cookieCode . '", acceptedCookies)
								)
								{
									window["bxCookies"]["' . $cookieCode . '"] = true;
									' . $functionBody . '
								}
							}
						);
					});
				</script>'
			);
		}
		else
		{
			Manager::setPageView('AfterHeadOpen',
				'<script data-skip-moving="true">
					' . $functionBody . '
				</script>'
			);
		}
	}

	/**
	 * Returns agreement id by site id.
	 * @param int $siteId Site id.
	 * @return int|null
	 */
	public static function getAgreementIdBySiteId(int $siteId): ?int
	{
		Rights::setOff();
		$fields = Site::getAdditionalFields($siteId);
		Rights::setOn();

		$mode = $fields['COOKIES_MODE']->getValue();
		$informationMode = $mode == self::MODE_I || (!$mode && Manager::availableOnlyForZone('ru'));

		if ($informationMode)
		{
			return null;
		}

		return isset($fields['COOKIES_AGREEMENT_ID'])
			? $fields['COOKIES_AGREEMENT_ID']->getValue()
			: null;
	}
}