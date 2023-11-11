<?php

namespace Bitrix\Iblock\Grid\Entity;

use Bitrix\Main\Loader;
use Bitrix\Iblock\Url\AdminPage\BaseBuilder;

class ElementSettings extends IblockSettings
{
	private bool $isUseBusinessProcesses;
	private bool $isUseWorkflow;
	private ?BaseBuilder $urlBuilder = null;
	private bool $isSkuSelectorEnable = false;
	private bool $isNewCardEnabled = false;
	private ?array $selectedProductOfferIds = null;

	protected function init(): void
	{
		parent::init();

		$this->isUseBusinessProcesses =
			$this->iblockFields['BIZPROC'] === 'Y'
			&& Loader::includeModule('bizproc')
		;

		$this->isUseWorkflow =
			$this->iblockFields['WORKFLOW'] === 'Y'
			&& Loader::includeModule('workflow')
		;
	}

	public function isUseBusinessProcesses(): bool
	{
		return $this->isUseBusinessProcesses;
	}

	public function isUseWorkflow(): bool
	{
		return $this->isUseWorkflow;
	}

	public function getUrlBuilder(): ?BaseBuilder
	{
		return $this->urlBuilder;
	}

	public function setUrlBuilder(BaseBuilder $urlBuilder): self
	{
		$this->urlBuilder = $urlBuilder;

		return $this;
	}

	public function isSkuSelectorEnabled(): bool
	{
		return $this->isSkuSelectorEnable;
	}

	public function setSkuSelectorEnable(bool $value): self
	{
		$this->isSkuSelectorEnable = $value;

		return $this;
	}

	public function getSelectedProductOfferIds(): ?array
	{
		return $this->selectedProductOfferIds;
	}

	public function setSelectedProductOfferIds(array $value): self
	{
		$this->selectedProductOfferIds = $value;

		return $this;
	}

	public function setNewCardEnabled(bool $value): self
	{
		$this->isNewCardEnabled = $value;

		return $this;
	}

	public function isNewCardEnabled(): bool
	{
		return $this->isNewCardEnabled;
	}
}
