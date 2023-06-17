<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Hook;
use Bitrix\Landing\Hook\Page\Theme;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Folder;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\TemplateRef;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Restriction;

\CBitrixComponent::includeComponentClass('bitrix:landing.base.form');

class LandingEditComponent extends LandingBaseFormComponent
{
	/**
	 * Detect or not og image from landing content.
	 */
	public const DETECT_OG_IMAGE = false;

	/**
	 * Min width/height for og:image.
	 */
	public const OG_IMAGE_MIN_SIZE = 200;

	/**
	 * Max width/height for og:image.
	 */
	public const OG_IMAGE_MAX_SIZE = 1500;

	/**
	 * Default site color (lightblue bitrix color)
	 */
	public const DEFAULT_SITE_COLOR = '#2fc6f6';

	/**
	 * Default color picker color
	 */
	public const COLOR_PICKER_COLOR = '#f25a8f';

	/**
	 * Default color for colorpicker bg color
	 */
	public const COLOR_PICKER_DEFAULT_BG_COLOR = '#ffffff';

	/**
	 * Default color for colorpickers: text color, title color
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
	protected $class = 'Landing';

	/**
	 * Local version of table map with available fields for change.
	 * @return array
	 */
	protected function getMap(): array
	{
		return array(
			'CODE', 'TITLE', 'TPL_ID', 'SITE_ID', 'SITEMAP'
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
	 * Detect image in landing content.
	 * @param int $lid Landing id.
	 * @param int $limit Limit for pictures.
	 * @return array
	 */
	protected function detectLandingImage($lid, $limit = 0): array
	{
		$images = array();

		$landing = \Bitrix\Landing\Landing::createInstance($lid);
		if ($landing->exist())
		{
			ob_start();
			foreach ($landing->getBlocks() as $block)
			{
				$block->view();
			}
			$content = ob_get_contents();
			ob_end_clean();

			$docRoot = Manager::getDocRoot();

			if (preg_match_all('/(<img.*?src="([^"]+)"[^>]+>)/is', $content, $matches))
			{
				$cnt = 0;
				foreach ($matches[2] as $i => $pictureUrl)
				{
					$picture = parse_url($pictureUrl);
					if (
						isset($picture['path']) &&
						in_array(mb_substr($picture['path'], -4), array('.png', '.jpg'))
					)
					{
						if (mb_substr($pictureUrl, 0, 1) == '/')
						{
							if (file_exists($docRoot . $pictureUrl))
							{
								$images[$i] = getimagesize($docRoot . $pictureUrl);
								$images[$i]['src'] = $pictureUrl;
								if (
									$images[$i]['0'] < $this::OG_IMAGE_MIN_SIZE ||
									$images[$i]['1'] < $this::OG_IMAGE_MIN_SIZE ||
									$images[$i]['0'] > $this::OG_IMAGE_MAX_SIZE ||
									$images[$i]['1'] > $this::OG_IMAGE_MAX_SIZE
								)
								{
									unset($images[$i]);
								}
								else
								{
									$cnt++;
									if ($limit && $cnt >= $limit)
									{
										break;
									}
								}
							}
						}
					}
				}
			}
		}

		return $images;
	}

	/**
	 * Get meta tags for current landing.
	 * @return array
	 */
	protected function getMeta(): array
	{
		$meta = array(
			'title' => '',
			'description' => '',
			'og:title' => '',
			'og:description' => '',
			'og:image' => ''
		);

		if ($this->id)
		{
			if (!isset($this->arResult['LANDING']))
			{
				$this->arResult['LANDING'] = $this->getRow();
			}
			$landing = $this->arResult['LANDING'];

			if (!isset($this->arResult['HOOKS']))
			{
				$this->arResult['HOOKS'] = $this->getHooks();
			}
			$hooks = $this->arResult['HOOKS'];

			// get title and description from hook Metamain
			if (isset($hooks['METAMAIN']))
			{
				$fields = $hooks['METAMAIN']->getFields();
				foreach (array('TITLE', 'DESCRIPTION') as $code)
				{
					if (isset($fields[$code]))
					{
						$meta[mb_strtolower($code)] = $fields[$code]->getValue();
					}
				}
			}

			// get og tags from hook Metaog
			if (isset($hooks['METAOG']))
			{
				$fields = $hooks['METAOG']->getFields();
				foreach (array('TITLE', 'DESCRIPTION', 'IMAGE') as $code)
				{
					if (isset($fields[$code]))
					{
						$meta['og:'.mb_strtolower($code)] = $fields[$code]->getValue();
					}
				}
			}

			// if some fields are not detected
			if (!$meta['title'])
			{
				$meta['title'] = $landing['TITLE']['~CURRENT'];
			}
			if (!$meta['og:title'])
			{
				$meta['og:title'] = $meta['title'];
			}
			if (!$meta['og:description'])
			{
				$meta['og:description'] = $meta['description'];
			}
			if (!$meta['og:image'] && self::DETECT_OG_IMAGE)
			{
				$meta['og:image'] = $this->detectLandingImage($this->id, 1);
			}
			if ($meta['og:image'] && !is_array($meta['og:image']))
			{
				$meta['og:image'] = array($meta['og:image']);
			}
		}

		return $meta;
	}

	/**
	 * Returns true, if this site without external domain.
	 * @return bool
	 */
	protected function isIntranet(): bool
	{
		return
			isset($this->arResult['SITES'][$this->arParams['SITE_ID']]) &&
			isset($this->arResult['SITES'][$this->arParams['SITE_ID']]['DOMAIN_ID']) &&
			$this->arResult['SITES'][$this->arParams['SITE_ID']]['DOMAIN_ID'] == '0';
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
			$this->checkParam('LANDING_ID', 0);
			$this->checkParam('TYPE', '');
			$this->checkParam('PAGE_URL_LANDINGS', '');
			$this->checkParam('PAGE_URL_LANDING_VIEW', '');
			$this->checkParam('PAGE_URL_SITE_EDIT', '');
			$this->checkParam('PAGE_URL_FOLDER_EDIT', '');

			\Bitrix\Landing\Site\Type::setScope(
				$this->arParams['TYPE']
			);

			$this->id = $this->arParams['LANDING_ID'];
			$this->successSavePage = $this->arParams['PAGE_URL_LANDINGS'];

			$this->arResult['TEMPLATES'] = $this->getTemplates();
			$this->arResult['LANDING'] = $landing = $this->getRow();
			$this->arResult['SPECIAL_TYPE'] = $this->getSpecialTypeSiteByLanding(
				\Bitrix\Landing\Landing::createInstance($this->id, ['skip_blocks' => true])
			);
			$this->arResult['LANDINGS'] = $this->arParams['SITE_ID'] > 0
				? $this->getLandings(array(
						'filter' => array(
							'SITE_ID' => $this->arParams['SITE_ID']
						)
					))
				: array();

			// if access denied, or not found
			if (!$this->arResult['LANDING'])
			{
				$this->id = 0;
			}
			if (
				$this->id &&
				!Rights::hasAccessForSite(
					$this->arParams['SITE_ID'],
					Rights::ACCESS_TYPES['sett']
				)
			)
			{
				$this->id = 0;
				$this->arParams['LANDING_ID'] = 0;
				$this->addError('ACCESS_DENIED', '', true);
			}

			if (!$this->id)
			{
				parent::executeComponent();
				return;
			}

			// if current page in folder
			$this->arResult['FOLDER'] = [];// backward compatibility
			$this->arResult['LAST_FOLDER'] = [];
			$this->arResult['FOLDER_PATH'] = '';
			if ($landing['FOLDER_ID']['CURRENT'])
			{
				$this->arResult['FOLDER_PATH'] = Folder::getFullPath(
					$landing['FOLDER_ID']['CURRENT'],
					$landing['SITE_ID']['CURRENT'],
					$this->arResult['LAST_FOLDER']
				);
			}

			$this->arResult['SITES'] = $sites = $this->getSites();

			// types mismatch
			$availableType = [$this->arParams['TYPE']];
			if ($this->arParams['TYPE'] == 'STORE')
			{
				$availableType[] = 'SMN';
			}
			if (
				!isset($sites[$this->arParams['SITE_ID']]) ||
				!in_array($sites[$this->arParams['SITE_ID']]['TYPE'], $availableType)
			)
			{
				\localRedirect($this->getRealFile());
			}

			$this->arResult['IS_INTRANET'] = $this->isIntranet();
			\Bitrix\Landing\Hook::setEditMode();
			$this->arResult['HOOKS'] = $this->getHooks();
			$this->arResult['HOOKS_SITE'] = $this->getHooks('Site', $this->arParams['SITE_ID']);
			$this->arResult['TEMPLATES_REF'] = TemplateRef::getForLanding($this->id);
			$this->arResult['META'] = $this->getMeta();
			$this->arResult['DOMAINS'] = $this->getDomains();

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

		// callback for update landing
		$tplRef = $this->request('TPL_REF');
		Landing::callback('OnAfterUpdate',
			function(\Bitrix\Main\Event $event) use ($tplRef, $landing)
			{
				static $updated = false;

				if ($updated)
				{
					return;
				}

				$primary = $event->getParameter('primary');
				$updated = true;
				$areaCount = 0;
				$tplId = $this->arResult['LANDING']['TPL_ID']['CURRENT'];
				$siteId = $this->arResult['LANDING']['SITE_ID']['CURRENT'];
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
								'SITE_ID' => $siteId,
								'TITLE' =>  Loc::getMessage('LANDING_CMP_AREA') . ' #' . $i
							));
							if ($res->isSuccess())
							{
								$data[$i] = $res->getId();
							}
						}
					}
				}
				TemplateRef::setForLanding(
					$primary['ID'],
					$data
				);
			}
		);

		parent::executeComponent();
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
			$value = $colors[$params['theme']]['color'] ?? '';
		}
		if ($value && $params['value'][0] !== '#')
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
		$themeCurr = \htmlspecialcharsbx(trim($themeHookFields['THEME_COLOR']->getValue()));
		if (!$themeCurr)
		{
			$theme = \htmlspecialcharsbx(trim($themeHookFields['THEME_CODE']->getValue()));
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