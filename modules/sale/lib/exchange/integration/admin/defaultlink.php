<?php
namespace Bitrix\Sale\Exchange\Integration\Admin;


class DefaultLink extends LinkBase
{
	protected $isLangSetted = false;
	protected $isFilterParamsSetted = false;

	public function getType()
	{
		return ModeType::DEFAULT_TYPE;
	}

	public function setLang($lang)
	{
		$this->isLangSetted = true;
		if($lang !== false)
			$this->query->set('lang', $lang);
		return $this;
	}

	public function setFilterParams($query)
	{
		$this->isFilterParamsSetted = true;
		if($query !== false)
			$this->setQuery($query);
		return $this;
	}

	public function fill()
	{
		//if($this->query->isChanged('lang') == false)
		if($this->isLangSetted == false)
		{
			$this->query->set('lang', LANGUAGE_ID);
		}

		if($this->isFilterParamsSetted == false)
		{
			$this->setQuery(GetFilterParams("filter_", false));
		}
		return $this;
	}
}