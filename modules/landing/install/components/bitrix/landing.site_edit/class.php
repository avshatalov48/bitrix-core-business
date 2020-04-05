<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Site;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\TemplateRef;
use \Bitrix\Main\Localization\Loc;

\CBitrixComponent::includeComponentClass('bitrix:landing.base.form');

class LandingSiteEditComponent extends LandingBaseFormComponent
{
	/**
	 * B24 service for detect IP for current zone.
	 */
	const B24_SERVICE_DETECT_IP = 'https://ip.bitrix24.site/getipforzone/?bx24_zone=';

	/**
	 * Default IP for DNS.
	 */
	const B24_DEFAULT_DNS_IP = '52.59.124.117';

	/**
	 * Class of current element.
	 * @var string
	 */
	protected $class = 'Site';

	/**
	 * Local version of table map with available fields for change.
	 * @return array
	 */
	protected function getMap()
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
	protected function additionalFieldsAllowed()
	{
		return true;
	}

	/**
	 * Get IP for DNS record for custom domain.
	 * @return string
	 */
	protected function getIpForDNS()
	{
		$ip = '';
		$http = new \Bitrix\Main\Web\HttpClient;
		$ip = $http->get($this::B24_SERVICE_DETECT_IP . Manager::getZone());
		$ip = \CUtil::jsObjectToPhp($ip);

		if (isset($ip['IP']))
		{
			return $ip['IP'];
		}

		return $this::B24_DEFAULT_DNS_IP;
	}

	/**
	 * Gets lang codes.
	 * @return array
	 */
	protected function getLangCodes()
	{
		$file = \Bitrix\Landing\Manager::getDocRoot();
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
		else
		{
			return [];
		}
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$init = $this->init();

		if ($init)
		{
			$this->checkParam('SITE_ID', 0);
			$this->checkParam('TYPE', '');
			$this->checkParam('PAGE_URL_SITES', '');
			$this->checkParam('PAGE_URL_LANDING_VIEW', '');
			$this->checkParam('TEMPLATE', '');

			$this->id = $this->arParams['SITE_ID'];
			$this->successSavePage = $this->arParams['PAGE_URL_SITES'];
			$this->template = $this->arParams['TEMPLATE'];

			$this->arResult['LANG_CODES'] = $this->getLangCodes();
			$this->arResult['IP_FOR_DNS'] = $this->getIpForDNS();
			$this->arResult['TEMPLATES'] = $this->getTemplates();

			$this->arResult['SETTINGS'] = \Bitrix\Landing\Hook\Page\Settings::getDataForSite(
				$this->id
			);
			$this->arResult['DOMAINS'] = $this->getDomains();
			$this->arResult['SITE'] = $this->getRow();
			$this->arResult['LANDINGS'] = $this->arParams['SITE_ID'] > 0
										? $this->getLandings(array(
												'filter' => array(
													'SITE_ID' => $this->arParams['SITE_ID']
												)
											))
										: array();
			// check landings as areas
			$areas = \Bitrix\Landing\TemplateRef::landingIsArea(
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
			}

			$this->arResult['HOOKS'] = $this->getHooks();
			$this->arResult['TEMPLATES_REF'] = TemplateRef::getForSite($this->id);
			$this->arResult['CUSTOM_DOMAIN'] = Manager::checkFeature(
				Manager::FEATURE_CUSTOM_DOMAIN
			);
		}

		// callback for update site
		$tplRef = $this->request('TPL_REF', true);
		if ($tplRef !== false)
		{
			Site::callback('OnAfterUpdate',
				function(\Bitrix\Main\Event $event) use ($tplRef)
				{
					$primary = $event->getParameter('primary');
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
							if (strpos($ref, ':') !== false)
							{
								list($a, $lid) = explode(':', $ref);
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
			);
		}


		parent::executeComponent();
	}
}