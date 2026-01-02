<?php declare(strict_types=1);

namespace Bitrix\UI\Counter;

class Counter
{
	public function __construct(
		public readonly bool $useAirDesign = false,
		public readonly CounterStyle $style = CounterStyle::FILLED_ALERT,
		public readonly int $value = 0,
		public readonly int $maxValue = 99,
		public readonly CounterColor $color = CounterColor::PRIMARY,
		public readonly CounterColor $secondaryColor = CounterColor::PRIMARY,
		public readonly bool $border = false,
		public readonly CounterSize $size = CounterSize::MEDIUM,
		public readonly bool $isDouble = false,
		public readonly bool $usePercentSymbol = false,
		public readonly string $id = '',
		public readonly bool $hideIfZero = false,
	) {
	}

	public function render(): string
	{
		$classes = ['ui-counter', 'ui-counter__scope'];

		if ($this->useAirDesign)
		{
			$classes[] = '--air';
		}

		if ($this->border)
		{
			$classes[] = 'ui-counter-border';
		}

		if ($this->value < 10 && !$this->usePercentSymbol)
		{
			$classes[] = '--one-digit';
		}

		if ($this->hideIfZero)
		{
			$classes[] = '--hide-zero';
		}

		$classes[] = $this->color->value;
		$classes[] = $this->size->value;
		$classes[] = $this->style->value;

		$classString = implode(' ', $classes);
		$id = $this->id ?? '';
		$html = sprintf(
			'<div id="%s" class="%s" data-value="%s">',
			htmlspecialcharsbx($id),
			htmlspecialcharsbx($classString),
			htmlspecialcharsbx($this->value)
		);
		$html .= $this->getInnerHtml();

		if ($this->isDouble)
		{
			$html .= sprintf(
				'<div class="ui-counter-secondary %s"></div>',
				htmlspecialcharsbx($this->secondaryColor->value),
			);
		}

		$html .= '</div>';

		return $html;
	}

	private function getInnerHtml(): string
	{
		if ($this->useAirDesign)
		{
			$symbol = '';
			$displayValue = $this->value;

			if ($this->usePercentSymbol)
			{
				$symbol = '%';
			}
			elseif ($this->value > $this->maxValue)
			{
				$displayValue = $this->maxValue;
				$symbol = '+';
			}

			return sprintf(
				'<div class="ui-counter-inner"><span class="ui-counter__value">%s</span><span class="ui-counter__symbol">%s</span></div>',
				htmlspecialcharsbx($displayValue),
				htmlspecialcharsbx($symbol),
			);
		}

		$percentSymbol = $this->usePercentSymbol ? '%' : '';

		return sprintf(
			'<div class="ui-counter-inner">%s%s</div>',
			htmlspecialcharsbx((string)$this->getValue()),
			htmlspecialcharsbx($percentSymbol),
		);
	}

	private function getValue(): string|int
	{
		if ($this->usePercentSymbol)
		{
			return $this->value;
		}

		if ($this->value <= $this->maxValue)
		{
			return $this->value;
		}

		return $this->maxValue . '+';
	}
}
