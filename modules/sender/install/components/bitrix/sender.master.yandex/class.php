<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class SenderMasterYandexComponent extends CBitrixComponent
{
	protected function initParams(): void
	{
		$this->arParams['SITE_PUBLIC_URLS'] ??= $this->getSitePublicUrls();
		$this->arParams['PARTNER_ID'] ??= (new \Bitrix\Sender\Integration\Yandex\Master\Config())->getPartnerId();
	}

	public function executeComponent()
	{
		$this->initParams();
		$this->prepareResult();

		$this->includeComponentTemplate();
	}

	protected function getSitePublicUrls(): array
	{
		return \Bitrix\Sender\Integration\Landing\Site::getLandingAndStorePublicUrls();
	}

	private function prepareResult(): void
	{
		$this->arResult['IN_SIDE_SLIDER'] = $this->isComponentInSideSlider();
	}

	private function isComponentInSideSlider(): bool
	{
		return $this->request->get('IFRAME') === 'Y' && $this->request->get('IFRAME_TYPE') === 'SIDE_SLIDER';
	}
}