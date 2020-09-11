<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Site;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\TemplateRef;
use \Bitrix\Landing\Domain\Register;
use \Bitrix\Landing\Site\Cookies;
use \Bitrix\Main\Localization\Loc;

\CBitrixComponent::includeComponentClass('bitrix:landing.base.form');

class LandingSiteEditComponent extends LandingBaseFormComponent
{
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
	 * Gets lang codes.
	 * @return array
	 */
	protected function getLangCodes()
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
		else
		{
			return [];
		}
	}

	/**
	 * Returns true, if this site without external domain.
	 * @return bool
	 */
	protected function isIntranet()
	{
		return
			isset($this->arResult['SITE']['DOMAIN_ID']['CURRENT']) &&
			(
				$this->arResult['SITE']['DOMAIN_ID']['CURRENT'] == '0' ||
				$this->arResult['SITE']['DOMAIN_ID']['CURRENT'] == ''
			);
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
			$this->checkParam('PAGE_URL_SITE_DOMAIN', '');
			$this->checkParam('PAGE_URL_SITE_COOKIES', '');
			$this->checkParam('TEMPLATE', '');

			\Bitrix\Landing\Site\Type::setScope(
				$this->arParams['TYPE']
			);

			$this->id = $this->arParams['SITE_ID'];
			$this->successSavePage = $this->arParams['PAGE_URL_SITES'];
			$this->template = $this->arParams['TEMPLATE'];

			$this->arResult['SITE'] = $this->getRow();
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
				$this->arResult['SITE']['TYPE']['CURRENT'] == 'SMN'
			)
			{
				Manager::forceB24disable(true);
			}

			if (Manager::isB24())
			{
				$this->arResult['IP_FOR_DNS'] = $this->getIpForDNS();
			}

			// set predefined for getting props from component
			\Bitrix\Landing\Node\Component::setPredefineForDynamicProps([
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
				$this->addError('ACCESS_DENIED', '', true);
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
				$this->arParams['SITE_ID'] = 0;
			}

			if ($this->id)
			{
				\Bitrix\Landing\Hook::setEditMode();
				$this->arResult['HOOKS'] = $this->getHooks();
				$this->arResult['TEMPLATES_REF'] = TemplateRef::getForSite($this->id);
			}
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
		}


		parent::executeComponent();
		Manager::forceB24disable(false);
	}
}