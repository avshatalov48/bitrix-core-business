<?php
namespace Bitrix\UI\Form;

use Bitrix\Main;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

class FeedbackForm
{
	protected $id;
	protected $isCloud;
	protected $formParams = [];
	protected $presets = [];
	protected $title;
	protected $portalUri;

	public function __construct(string $id)
	{
		if ($id == '')
		{
			throw new ArgumentException(' Feedback form id can not be empty');
		}
		$this->id = $id;
		$this->isCloud = Loader::includeModule('bitrix24');
		$this->title = Loc::getMessage('UI_FEEDBACK_FORM_BUTTON');
		$this->portalUri = 'https://product-feedback.bitrix24.com';
	}

	public function getId()
	{
		return $this->id;
	}

	public function getFormParams()
	{
		return $this->formParams;
	}

	public function setFormParamsDirectly($form)
	{
		$form['lang'] = $form['lang']??LANGUAGE_ID;
		$this->formParams = $form;
	}

	public function getPresets()
	{
		$presets = $this->presets;
		$presets['b24_plan'] = $this->isCloud ? \CBitrix24::getLicenseType() : '';
		$presets['b24_plan_date_to'] = (
			$this->isCloud
				? ConvertTimeStamp(Option::get('main', '~controller_group_till', time()))
				: ''
		);
		$presets['b24_partner_id'] = (
			($this->isCloud && method_exists('CBitrix24', 'getPartnerId'))
				? \CBitrix24::getPartnerId()
				: ''
		);

		$presets['hosturl'] = Main\Engine\UrlManager::getInstance()->getHostUrl();
		$presets['hostname'] = parse_url($presets['hosturl'], PHP_URL_HOST);

		global $USER;
		$name = '';
		$email = '';
		if(is_object($USER))
		{
			$name = $USER->GetFirstName();
			if(!$name)
			{
				$name = $USER->GetLogin();
			}
			$email = $USER->GetEmail();
		}
		$presets['c_name'] = $name;
		$presets['c_email'] = $email;

		return $presets;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getPortalUri()
	{
		return $this->portalUri;
	}

	public function getJsObjectParams()
	{
		return [
			'id' => $this->getId(),
			'form' => $this->getFormParams(),
			'presets' => $this->getPresets(),
			'title' => $this->getTitle(),
			'portal' => $this->getPortalUri()
		];
	}

	public function setFormParams(array $forms)
	{
		if ($this->isCloud && isset($forms['prefixes']))
		{
			$zone = \CBitrix24::getLicensePrefix();
			$defaultForm = null;
			foreach ($forms as $form)
			{
				if (!isset($form['licenseZones']) || !isset($form['zones']) || !is_array($forms['licenseZones']))
				{
					continue;
				}

				if (in_array($zone, $forms['licenseZones']))
				{
					$form['lang'] = $form['lang']??LANGUAGE_ID;
					$this->formParams = $form;
					return;
				}

				if (in_array('en', $forms['licenseZones']))
				{
					$form['lang'] = $form['lang']??LANGUAGE_ID;
					$defaultForm = $form;
				}
			}

			$this->formParams = $defaultForm;
		}
		else if ($this->isCloud)
		{
			$zone = \CBitrix24::getPortalZone();
			$defaultForm = null;
			foreach ($forms as $form)
			{
				if (!isset($form['zones']) || !is_array($form['zones']))
				{
					continue;
				}

				if (in_array($zone, $form['zones']))
				{
					$form['lang'] = $form['lang']??LANGUAGE_ID;
					$this->formParams = $form;
					return;
				}

				if (in_array('en', $form['zones']))
				{
					$form['lang'] = $form['lang']??LANGUAGE_ID;
					$defaultForm = $form;
				}
			}

			$this->formParams = $defaultForm;
		}
		else
		{
			$lang = LANGUAGE_ID;
			$defaultForm = null;
			foreach ($forms as $form)
			{
				if (!isset($form['lang']))
				{
					continue;
				}

				if ($lang === $form['lang'])
				{
					$this->formParams =  $form;
					return;
				}

				if ($form['lang'] === 'en')
				{
					$defaultForm = $form;
				}
			}

			$this->formParams =  $defaultForm;
		}
	}

	public function setPresets(array $presets = [])
	{
		$this->presets = $presets;
	}

	public function setTitle(string $title)
	{
		$this->title = $title;
	}

	public function setPortalUri(string $portalUri)
	{
		$this->portalUri = $portalUri;
	}
}