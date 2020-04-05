<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\TemplateRef;
use \Bitrix\Landing\Landing;
use \Bitrix\Main\Localization\Loc;

\CBitrixComponent::includeComponentClass('bitrix:landing.base.form');

class LandingEditComponent extends LandingBaseFormComponent
{
	/**
	 * Detect or not og image from landing content.
	 */
	const DETECT_OG_IMAGE = false;

	/**
	 * Min width/height for og:image.
	 */
	const OG_IMAGE_MIN_SIZE = 200;

	/**
	 * Max width/height for og:image.
	 */
	const OG_IMAGE_MAX_SIZE = 1500;

	/**
	 * Class of current element.
	 * @var string
	 */
	protected $class = 'Landing';

	/**
	 * Local version of table map with available fields for change.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'CODE', 'TITLE', 'TPL_ID', 'SITE_ID', 'SITEMAP'
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
	 * Detect image in landing content.
	 * @param int $lid Landing id.
	 * @param int $limit Limit for pictures.
	 * @return array
	 */
	protected function detectLandingImage($lid, $limit = 0)
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

			$docRoot = \Bitrix\Landing\Manager::getDocRoot();

			if (preg_match_all('/(<img.*?src="([^"]+)"[^>]+>)/is', $content, $matches))
			{
				$cnt = 0;
				foreach ($matches[2] as $i => $pictureUrl)
				{
					$picture = parse_url($pictureUrl);
					if (
						isset($picture['path']) &&
						in_array(substr($picture['path'], -4), array('.png', '.jpg'))
					)
					{
						if (substr($pictureUrl, 0, 1) == '/')
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
	protected function getMeta()
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
						$meta[strtolower($code)] = $fields[$code]->getValue();
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
						$meta['og:' . strtolower($code)] = $fields[$code]->getValue();
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
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$init = $this->init();

		if ($init)
		{
			$this->checkParam('SITE_ID', 0);
			$this->checkParam('LANDING_ID', 0);
			$this->checkParam('PAGE_URL_LANDINGS', '');
			$this->checkParam('PAGE_URL_LANDING_VIEW', '');

			$this->id = $this->arParams['LANDING_ID'];
			$this->successSavePage = $this->arParams['PAGE_URL_LANDINGS'];

			$this->arResult['LANDING_INST'] = null;
			$this->arResult['TEMPLATES'] = $this->getTemplates();
			$this->arResult['LANDING'] = $this->getRow();
			$this->arResult['LANDINGS'] = $this->arParams['SITE_ID'] > 0
										? $this->getLandings(array(
												'filter' => array(
													'SITE_ID' => $this->arParams['SITE_ID']
												)
											))
										: array();

			if (!$this->arResult['LANDING'])
			{
				$this->id = 0;
			}

			$this->arResult['HOOKS'] = $this->getHooks();
			$this->arResult['HOOKS_SITE'] = $this->getHooks('Site', $this->arParams['SITE_ID']);
			$this->arResult['TEMPLATES_REF'] = TemplateRef::getForLanding($this->id);
			$this->arResult['META'] = $this->getMeta();
			$this->arResult['SITES'] = $this->getSites();
			$this->arResult['DOMAINS'] = $this->getDomains();

			if ($this->id > 0)
			{
				$this->arResult['LANDING_INST'] = Landing::createInstance($this->id);
			}
		}

		// callback for update landing
		$tplRef = $this->request('TPL_REF');
		\Bitrix\Landing\Landing::callback('OnAfterUpdate',
			function(\Bitrix\Main\Event $event) use ($tplRef)
			{
				$primary = $event->getParameter('primary');
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
}