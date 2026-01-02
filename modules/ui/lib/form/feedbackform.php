<?php

namespace Bitrix\UI\Form;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\License\UrlProvider;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

class FeedbackForm
{
	protected string $id;
	protected bool $isCloud;
	protected ?FeedbackFormParams $currentForm = null;
	protected array $presets = [];
	protected string $title;
	protected string $portalUri;

	public function __construct(string $id)
	{
		if ($id === '')
		{
			throw new ArgumentException(' Feedback form id can not be empty');
		}
		$this->id = $id;
		$this->isCloud = Loader::includeModule('bitrix24');
		$this->title = Loc::getMessage('UI_FEEDBACK_FORM_BUTTON');
		$this->portalUri = 'https://' . (new UrlProvider())->getFeedbackDomain();
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getCurrentForm(): ?array
	{
		return $this->currentForm?->toArray();
	}

	public function setPresets(array $presets = []): void
	{
		$this->presets = $presets;
	}

	public function getPresets(): array
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
		if (is_object($USER))
		{
			$name = $USER->GetFirstName();
			if (!$name)
			{
				$name = $USER->GetLogin();
			}
			$email = $USER->GetEmail();
		}
		$presets['c_name'] = $name;
		$presets['c_email'] = $email;

		$business = Option::get('bitrix24', 'CJM-business', null);
		if (isset($business))
		{
			$presets['business'] = $business;
		}

		return $presets;
	}

	public function setTitle(string $title): void
	{
		$this->title = $title;
	}

	public function getTitle(): ?string
	{
		return $this->title;
	}

	public function setPortalUri(string $portalUri): void
	{
		$this->portalUri = $portalUri;
	}

	public function getPortalUri(): string
	{
		return $this->portalUri;
	}

	public function getJsObjectParams(): array
	{
		return [
			'id' => $this->getId(),
			'form' => $this->getCurrentForm() ?? [],
			'presets' => $this->getPresets(),
			'title' => $this->getTitle(),
			'portal' => $this->getPortalUri(),
		];
	}

	public function setFormParams(array $params): void
	{
		$forms = [];

		foreach ($params as $item)
		{
			if (!isset($item['id'], $item['sec']))
			{
				continue;
			}

			$form = new FeedbackFormParams();
			$form->id = (int)$item['id'];
			$form->sec = (string)$item['sec'];
			$form->zones = $item['zones'] ?? [$this->getDefaultZone()];
			$form->lang = $item['lang'] ?? $this->getDefaultLang();

			$forms[] = $form;
		}

		$forms = $this->findFormByZone($forms);
		if (count($forms) === 0)
		{
			return;
		}

		$forms = $this->findByLang($forms);

		$this->currentForm = array_shift($forms);
	}

	/**
	 * @param FeedbackFormParams[] $forms
	 * @return array
	 */
	private function findFormByZone(array $forms): array
	{
		$zone = $this->getZone() ?? $this->getDefaultZone();
		$found = array_filter($forms, static function ($form) use ($zone)
		{
			return in_array($zone, $form->zones, true);
		});

		if (count($found) === 0)
		{
			$zoneDefault = $this->getDefaultZone();
			$found = array_filter($forms, static function ($form) use ($zoneDefault)
			{
				return in_array($zoneDefault, $form->zones, true);
			});
		}

		return $found;
	}

	/**
	 * @param FeedbackFormParams[] $forms
	 * @return array
	 */
	private function findByLang(array $forms): array
	{
		$lang = $this->getLang();
		$found = array_filter($forms, static function ($form) use ($lang)
		{
			return $lang === $form->lang;
		});

		if (count($found) === 0)
		{
			$langDefault = $this->getDefaultLang();
			$found = array_filter($forms, static function ($form) use ($langDefault)
			{
				return $langDefault === $form->lang;
			});
		}

		return count($found) > 0 ? $found : $forms;
	}

	/**
	 * @deprecated - can't use defaultForm param now
	 * @param array $formParams
	 * @return void
	 */
	public function setFormParamsDirectly(array $formParams): void
	{
		if (!isset($formParams['id'], $formParams['sec']))
		{
			return;
		}

		$form = new FeedbackFormParams();
		$form->id = (int)$formParams['id'];
		$form->sec = (string)$formParams['sec'];
		$form->zones = $formParams['zones'] ?? [$this->getDefaultZone()];
		$form->lang = $formParams['lang'] ?? $this->getDefaultLang();

		$this->currentForm = $form;
	}

	protected function getZone(): ?string
	{
		return Application::getInstance()->getLicense()->getRegion();
	}

	private function getDefaultZone(): string
	{
		return $this->isCis() ? 'ru' : 'en';
	}

	protected function getLang(): string
	{
		return LANGUAGE_ID;
	}

	private function getDefaultLang(): string
	{
		return $this->isCis() ? 'ru' : 'en';
	}

	private function isCis(): bool
	{
		$zone = $this->getZone() ?? '';

		return in_array($zone, ['ru', 'by', 'kz', 'uz']);
	}

	public static function getCisZones(): array
	{
		return ['ru'];
	}

	public static function getWestZones(): array
	{
		return ['en'];
	}
}