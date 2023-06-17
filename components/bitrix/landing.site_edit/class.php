<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Hook;
use Bitrix\Landing\Hook\Page\Theme;
use Bitrix\Landing\Node\Component;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Rights;
use Bitrix\Landing\Site\Type;
use \Bitrix\Landing\TemplateRef;
use \Bitrix\Landing\Domain\Register;
use \Bitrix\Landing\Site\Cookies;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Restriction;

CBitrixComponent::includeComponentClass('bitrix:landing.base.form');

class LandingSiteEditComponent extends LandingBaseFormComponent
{
	/**
	 * Default site color (lightblue bitrix color)
	 */
	public const DEFAULT_SITE_COLOR = '#2fc6f6';

	/**
	 * Default color picker color
	 */
	public const COLOR_PICKER_COLOR = '#f25a8f';

	/**
	 * Default color for color picker bg color
	 */
	public const COLOR_PICKER_DEFAULT_BG_COLOR = '#ffffff';

	/**
	 * Default color for color pickers: text color, title color
	 */
	public const COLOR_PICKER_DEFAULT_COLOR_TEXT = '#000000';

	/**
	 * Default color picker color in RGB format
	 */
	public const COLOR_PICKER_COLOR_RGB = 'rgb(52, 188, 242)';

	/**
	 * Class of current element.
	 * @var string
	 */
	protected $class = 'Site';

	/**
	 * Local version of table map with available fields for change.
	 * @return array
	 */
	protected function getMap(): array
	{
		return array(
			'CODE', 'TITLE', 'TYPE', 'TPL_ID', 'DOMAIN_ID', 'LANG',
			'LANDING_ID_INDEX', 'LANDING_ID_404', 'LANDING_ID_503'
		);
	}

	/**
	 * Allowed or not additional fields for this form.
	 * @return boolean
	 */
	protected function additionalFieldsAllowed(): bool
	{
		return true;
	}

	/**
	 * Gets lang codes.
	 * @return array
	 */
	protected function getLangCodes(): array
	{
		if (
			!Manager::isB24() ||
			!defined('SITE_TEMPLATE_PATH')
		)
		{
			return [];
		}

		$file = Manager::getDocRoot();
		$file .= SITE_TEMPLATE_PATH;
		$file .= '/languages.php';

		if (file_exists($file))
		{
			include $file;
		}

		if (
			isset($b24Languages) &&
			is_array($b24Languages)
		)
		{
			$langs = [];
			foreach ($b24Languages as $code => $lang)
			{
				if (isset($lang['NAME']))
				{
					$langs[$code] = $lang['NAME'];
				}
			}
			return $langs;
		}

		return [];
	}

	/**
	 * Returns true, if this site without external domain.
	 * @return bool
	 */
	protected function isIntranet(): bool
	{
		return
			isset($this->arResult['SITE']['DOMAIN_ID']['CURRENT']) &&
			(
				$this->arResult['SITE']['DOMAIN_ID']['CURRENT'] === '0' ||
				$this->arResult['SITE']['DOMAIN_ID']['CURRENT'] === ''
			);
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$init = $this->init();

		if ($init)
		{
			$this->checkParam('SITE_ID', 0);
			$this->checkParam('TYPE', '');
			$this->checkParam('PAGE_URL_SITES', '');
			$this->checkParam('PAGE_URL_LANDING_VIEW', '');
			$this->checkParam('PAGE_URL_SITE_DOMAIN', '');
			$this->checkParam('PAGE_URL_SITE_COOKIES', '');
			$this->checkParam('TEMPLATE', '');

			Type::setScope(
				$this->arParams['TYPE']
			);

			$this->id = $this->arParams['SITE_ID'];
			$this->successSavePage = $this->arParams['PAGE_URL_SITES'];
			$this->template = $this->arParams['TEMPLATE'];

			$this->arResult['SITE'] = $site = $this->getRow();
			$this->arResult['LANG_CODES'] = $this->getLangCodes();
			$this->arResult['TEMPLATES'] = $this->getTemplates();
			$this->arResult['IS_INTRANET'] = $this->isIntranet();
			$this->arResult['SHOW_RIGHTS'] = Rights::isAdmin() && Rights::isExtendedMode();
			$this->arResult['SETTINGS'] = [];
			$this->arResult['REGISTER'] = Register::getInstance();
			$this->arResult['SITE_INCLUDES_SCRIPT'] = Cookies::isSiteIncludesScript($this->id);
			$this->arResult['COOKIES_AGREEMENT'] = Cookies::getMainAgreement();

			if (
				!defined('LANDING_DISABLE_B24_MODE') &&
				$this->arResult['SITE']['TYPE']['CURRENT'] === 'SMN'
			)
			{
				Manager::forceB24disable(true);
			}

			if (Manager::isB24())
			{
				$this->arResult['IP_FOR_DNS'] = $this->getIpForDNS();
			}

			// set predefined for getting props from component
			Component::setPredefineForDynamicProps([
				'IBLOCK_ID' => Option::get('crm', 'default_product_catalog_id'),
				'USE_ENHANCED_ECOMMERCE' => 'Y',
				'SHOW_DISCOUNT_PERCENT' => 'Y',
				'LABEL_PROP' => [
					'NEWPRODUCT',
					'SALELEADER',
					'SPECIALOFFER'
				],
				'CONVERT_CURRENCY' => 'Y'
			]);

			// if access denied, or not found
			if (
				$this->id &&
				!Rights::hasAccessForSite(
					$this->id,
					Rights::ACCESS_TYPES['sett']
				)
			)
			{
				$this->id = 0;
				$this->arParams['SITE_ID'] = 0;
				$this->addError('LANDING_ERROR_SETTINGS_ACCESS_DENIED_MSGVER_1', '', true);
			}

			if (!$this->id)
			{
				parent::executeComponent();
				return;
			}

			// rights
			if ($this->arResult['SHOW_RIGHTS'])
			{
				$this->arResult['ACCESS_TASKS'] = $this->getAccessTasks();
				$this->arResult['CURRENT_RIGHTS'] = [];
				if ($this->id)
				{
					$this->arResult['CURRENT_RIGHTS'] = Rights::getDataForSite(
						$this->id,
						$this->getRightsValue(true)
					);
				}
			}

			// etc
			$this->arResult['DOMAINS'] = $this->getDomains();
			$this->arResult['LANDINGS'] = $this->arParams['SITE_ID'] > 0
										? $this->getLandings(array(
												'filter' => array(
													'SITE_ID' => $this->arParams['SITE_ID']
												)
											))
										: array();
			// check landings as areas
			$areas = TemplateRef::landingIsArea(
				array_keys($this->arResult['LANDINGS'])
			);
			foreach ($this->arResult['LANDINGS'] as &$landingItem)
			{
				$landingItem['IS_AREA'] = $areas[$landingItem['ID']] === true;
			}
			unset($landingItem);

			if (!$this->arResult['SITE'])
			{
				$this->id = 0;
				$this->arParams['SITE_ID'] = 0;
			}

			if ($this->id)
			{
				Hook::setEditMode();
				$this->arResult['HOOKS'] = $this->getHooks();
				$this->arResult['TEMPLATES_REF'] = TemplateRef::getForSite($this->id);
			}

			$this->arResult['COLORS'] = Theme::getColorCodes();
			$this->arResult['PREPARE_COLORS'] = self::prepareColors($this->arResult['COLORS']);
			$themeHookFields = $this->arResult['HOOKS']['THEME']->getPageFields();
			if ($themeHookFields['THEME_CODE'])
			{
				$this->arResult['LANDING_VALUE_CODE'] = $themeHookFields['THEME_CODE']->getValue();
			}
			if ($themeHookFields['THEME_COLOR'])
			{
				$this->arResult['LANDING_VALUE_COLOR'] = $themeHookFields['THEME_COLOR']->getValue();
			}
			if (isset($this->arResult['LANDING_VALUE_CODE']) && !isset($this->arResult['LANDING_VALUE_COLOR']))
			{
				$themeHookFields['THEME_USE']->setValue('Y');
			}
			$this->arResult['CURRENT_COLORS']['value'] = htmlspecialcharsbx(trim($themeHookFields['THEME_COLOR']->getValue()));
			if (!$this->arResult['CURRENT_COLORS']['value'])
			{
				$this->arResult['CURRENT_COLORS']['theme'] = htmlspecialcharsbx(trim($themeHookFields['THEME_CODE']->getValue()));
			}
			$this->arResult['CURRENT_COLORS']  = self::getCurrentColors($this->arResult['CURRENT_COLORS']);
			$this->arResult['CURRENT_THEME'] = self::getCurrentTheme($this->arResult['HOOKS'], $this->arResult['COLORS']);
			$this->arResult['SLIDER_CODE'] = Restriction\Hook::getRestrictionCodeByHookCode('THEME');
			$this->arResult['ALLOWED_HOOK'] = Restriction\Manager::isAllowed($this->arResult['SLIDER_CODE']);
			if (!$this->arResult['ALLOWED_HOOK'] && !(in_array($this->arResult['CURRENT_THEME'], $this->arResult['PREPARE_COLORS']['allColors'], true)))
			{
				$this->arResult['LAST_CUSTOM_COLOR'] = $this->arResult['CURRENT_THEME'];
				$this->arResult['CURRENT_THEME'] = self::DEFAULT_SITE_COLOR;
			}
			$this->arResult['CURRENT_THEME'] = self::checkCurrentTheme($this->arResult['CURRENT_THEME']);
		}

		// callback for update site
		$tplRef = $this->request('TPL_REF', true);
		Site::callback('OnAfterUpdate',
			function(Event $event) use ($tplRef, $site)
			{
				$primary = $event->getParameter('primary');

				if ($tplRef !== false)
				{
					$areaCount = 0;
					$tplId = $this->arResult['SITE']['TPL_ID']['CURRENT'];
					$templates = $this->arResult['TEMPLATES'];
					if (isset($templates[$tplId]))
					{
						$areaCount = $templates[$tplId]['AREA_COUNT'];
					}
					// set template refs
					$data = array();
					if ($primary && $primary['ID'])
					{
						foreach (explode(',', $tplRef) as $ref)
						{
							if (mb_strpos($ref, ':') !== false)
							{
								[$a, $lid] = explode(':', $ref);
								$data[$a] = $lid;
							}
						}
						// create empty areas if need
						for ($i = 1; $i <= $areaCount; $i++)
						{
							if (!isset($data[$i]) || !$data[$i])
							{
								$res = Landing::add(array(
									'SITE_ID' => $primary['ID'],
									'TITLE' =>  Loc::getMessage('LANDING_CMP_AREA') . ' #' . $i
								));
								if ($res->isSuccess())
								{
									$data[$i] = $res->getId();
								}
							}
						}
					}
					TemplateRef::setForSite(
						$primary['ID'],
						$data
					);
				}
				// rights
				if (Rights::isAdmin() && Rights::isExtendedMode())
				{
					Rights::setOperationsForSite(
						$primary['ID'],
						$this->getRightsValue()
					);
				}
			}
		);

		parent::executeComponent();
		Manager::forceB24disable(false);
	}

	/**
	 * Get correct hex value.
	 * @param array $params some params.
	 *
	 * @return string
	 */
	public static function getPrepareColor(array $params): string
	{
		$colors = Theme::getColorCodes();
		$value = $params['value'];
		if ($params['theme'] ?? null)
		{
			$value = $colors[$params['theme']]['color'];
		}
		if (!empty($params['value']) && $params['value'][0] !== '#')
		{
			$value = '#'.$params['value'];
		}
		return $value;
	}

	/**
	 * Check allowed hook by hook code
	 * @param string $hookCode
	 * @return bool
	 */
	public static function isHookAllowed(string $hookCode): bool
	{
		$restrictionCode = Restriction\Hook::getRestrictionCodeByHookCode($hookCode);
		return Restriction\Manager::isAllowed($restrictionCode);
	}

	/**
	 * Get colors array
	 * @return array
	 */
	public static function getColors(): array
	{
		$colors = Theme::getColorCodes();
		$colors["allColors"] = Theme::getAllColorCodes();
		$colors["startColors"] = Theme::getStartColorCodes();
		return $colors;
	}

	/**
	 * Get current color or last custom color with default color
	 * @param array $params
	 * @return array
	 */
	public static function getCurrentColors(array $params): array
	{
		$allowed = self::isHookAllowed('THEME');
		$currentColor = self::getPrepareColor($params);
		$colors = self::getColors();
		$result['currentColor'] = $currentColor;
		if (!$allowed && !(in_array($currentColor, $colors["allColors"], true)))
		{
			$result['lastColor'] = $currentColor;
			$result['currentColor'] = self::DEFAULT_SITE_COLOR;
		}
		return $result;
	}

	/**
	 * Getting a set of colors: all, start, other
	 * @param array $colors
	 * @return array
	 */
	public static function prepareColors(array $colors): array
	{
		$prepareColors = [];
		foreach ($colors as $colorItem)
		{
			if (isset($colorItem['color']))
			{
				$prepareColors['allColors'][] = $colorItem['color'];
			}
			if (isset($colorItem['base']) && $colorItem['base'] === true && ($colorItem['baseInSettings'] ?? null) !== false)
			{
				$prepareColors['startColors'][] = $colorItem['color'];
			}
		}

		return $prepareColors;
	}

	/**
	 * Getting a current theme
	 * @param array $hooks
	 * @param array $colors
	 * @return string
	 */
	public static function getCurrentTheme(array $hooks, array $colors): string
	{
		$themeHookFields = $hooks['THEME']->getPageFields();
		$themeCurr = htmlspecialcharsbx(trim($themeHookFields['THEME_COLOR']->getValue()));
		if (!$themeCurr)
		{
			$theme = htmlspecialcharsbx(trim($themeHookFields['THEME_CODE']->getValue()));
			$themeCurr = $colors[$theme]['color'];
		}
		if ($themeCurr[0] !== '#')
		{
			$themeCurr = '#' . $themeCurr;
		}

		return $themeCurr;
	}

	/**
	 * Check for length for current theme
	 * @param string $color
	 * @return string
	 */
	public static function checkCurrentTheme(string $color): string
	{
		if (strlen($color) !== 7)
		{
			$color = self::COLOR_PICKER_COLOR;
		}

		return $color;
	}
}
