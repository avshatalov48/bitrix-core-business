<?php
declare(strict_types=1);

namespace Bitrix\Landing\Metrika;

use Bitrix\Main\Analytics\AnalyticsEvent;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;

class Metrika
{
	private const ERROR_PARAM = 'errorType';

	private array $data = [];
	private AnalyticsEvent $event;

	public function __construct(Categories $category, Events $event, ?Tools $tool = null)
	{
		$toolValue = isset($tool) ? $tool->value : Tools::landing->value;

		$this->event = new AnalyticsEvent($event->value, $toolValue, $category->value);
		$this->event->setStatus(Statuses::Success->value);

		$this->data['tool'] = $toolValue;
		$this->data['status'] = Statuses::Success->value;
		$this->data['category'] = $category->value;
		$this->data['event'] = $event->value;
	}

	public function setType(?Types $type): self
	{
		if ($type)
		{
			$this->event->setType($type->value);
			$this->data['type'] = $type->value;
		}

		return $this;
	}

	public function setStatus(?Statuses $status): self
	{
		if (isset($status))
		{
			$this->event->setStatus($status->value);
			$this->data['status'] = $status->value;
		}

		return $this;
	}

	public function setSection(?Sections $section): self
	{
		if ($section)
		{
			$this->event->setSection($section->value);
			$this->data['c_section'] = $section->value;
		}

		return $this;
	}

	public function setSubSection(?string $subSection): self
	{
		if (isset($subSection))
		{
			$this->event->setSubSection($subSection);
			$this->data['c_sub_section'] = $subSection;
		}

		return $this;
	}

	public function setElement(?string $element): self
	{
		if (isset($element))
		{
			$this->event->setElement($element);
			$this->data['c_element'] = $element;
		}

		return $this;
	}

	public function setParam(int $position, string $param, string $value): self
	{
		if ($position <= 0 || $position > 5)
		{
			return $this;
		}

		$param = str_replace(['_', ' '], '-', $param);
		$value = str_replace(['_', ' '], '-', $value);
		$this->data['p' . $position] = [$param, $value];
		$this->event->{'setP' . $position}("{$param}_{$value}");

		return $this;
	}

	/**
	 * @param string $error
	 * @param Statuses|null $status - custom error status. If note pass - use default Error
	 * @return $this
	 */
	public function setError(string $error, ?Statuses $status = null): self
	{
		return
			$this
				->setStatus($status ?? Statuses::Error)
				->setParam(5, self::ERROR_PARAM, $error)
		;
	}

	public function send(): void
	{
		$this->event->send();
	}

	public function getSendingScript(bool $addTag = false): string
	{
		$data = Json::encode($this->data);

		$script = <<<script
			if (typeof BX.Landing.Metrika !== 'undefined')
			{
				const metrika = new BX.Landing.Metrika();
				metrika.sendData($data);
			}
		script;

		return $addTag
			? "<script>$script</script>"
			: $script;
	}

	/**
	 * Add analytic get params to URL
	 * @param string $url
	 * @return string
	 */
	public function parametrizeUri(string $url): string
	{
		$uri = new Uri($url);
		$add = [];
		foreach ($this->data as $param => $value)
		{
			if ($value !== null)
			{
				$add["st[{$param}]"] = $value;
			}
		}
		$uri->addParams($add);

		return $uri->getUri();
	}
}
