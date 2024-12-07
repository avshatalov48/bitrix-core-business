import { Dom, Tag, Event, Text } from 'main.core';
import { MemoryCache } from 'main.core.cache';
import type { BaseCache } from 'main.core.cache';
import { BasePicker } from './base-picker';

import { createUtcDate } from './helpers/create-utc-date';
import { getDate } from './helpers/get-date';

import './css/year-picker.css';

export type YearPickerYear = {
	index: number,
	name: string,
	year: number,
	current: boolean,
	selected: boolean,
	focused: boolean,
	tabIndex: number,
};

export class YearPicker extends BasePicker
{
	#refs: BaseCache<HTMLElement> = new MemoryCache();

	getContainer(): HTMLElement
	{
		return this.#refs.remember('container', () => {
			return Tag.render`
				<div class="ui-year-picker">
					${this.getHeaderContainer(
						this.getPrevBtn(),
						this.getHeaderTitle(),
						this.getNextBtn(),
					)}
					${this.getContentContainer()}
				</div>
			`;
		});
	}

	getHeaderTitle(): HTMLElement
	{
		return this.#refs.remember('header-title', () => {
			return Tag.render`
				<div class="ui-year-picker-header-title"></div>
			`;
		});
	}

	getYears(): Array<YearPickerYear>[]
	{
		const { year: currentYear } = getDate(this.getDatePicker().getToday());
		const focusDate = this.getDatePicker().getFocusDate();
		const initialFocusYear = this.getDatePicker().getInitialFocusDate().getUTCFullYear();

		const years = [];
		let index = 0;
		let year = this.#getStartYear();
		for (let i = 0; i < 4; i++)
		{
			const quarter: YearPickerYear[] = [];
			for (let j = 0; j < 3; j++)
			{
				const focused = focusDate !== null && focusDate.getUTCFullYear() === year;
				quarter.push({
					index,
					year,
					name: year,
					current: currentYear === year,
					selected: this.getDatePicker().isDateSelected(createUtcDate(year), 'year'),
					focused,
					tabIndex: focused || year === initialFocusYear ? 0 : -1,
				});
				year++;
				index++;
			}

			years.push(quarter);
		}

		return years;
	}

	#getStartYear(): number
	{
		const { year: viewYear } = this.getDatePicker().getViewDateParts();
		const { year: currentYear } = getDate(this.getDatePicker().getToday());

		let year = currentYear - 4;
		year -= 12 * Math.ceil((year - viewYear) / 12);

		return year;
	}

	getFirstYear(): number
	{
		return this.#getStartYear();
	}

	getLastYear(): number
	{
		return this.#getStartYear() + 11;
	}

	renderTo(container: HTMLElement)
	{
		super.renderTo(container);

		Event.bind(this.getContentContainer(), 'click', this.#handleYearClick.bind(this));
	}

	render(): void
	{
		let focusButton = null;
		const isFocused = this.getDatePicker().isFocused();
		const years = this.getYears();
		years.forEach((quarter: YearPickerYear[], index) => {
			const quarterContainer: HTMLElement = this.#renderQuarter(index);
			quarter.forEach((year: YearPickerYear) => {
				const button = this.#renderYear(year, quarterContainer);
				if (year.focused)
				{
					focusButton = button;
				}
			});
		});

		if (focusButton !== null && isFocused)
		{
			focusButton.focus({ preventScroll: true });
		}

		const firstYear = years[0][0].name;
		const lastYear = years.at(-1).at(-1).name;
		this.getHeaderTitle().textContent = `${firstYear} — ${lastYear}`;
	}

	#renderQuarter(index: number): HTMLElement
	{
		return this.#refs.remember(`quarter-${index}`, () => {
			const container: HTMLElement = Tag.render`<div class="ui-year-picker-trio"></div>`;
			Dom.append(container, this.getContentContainer());

			return container;
		});
	}

	#renderYear(year: YearPickerYear, quarterContainer: HTMLElement): HTMLButtonElement
	{
		const button: HTMLElement = this.#refs.remember(`year-${year.index}`, () => {
			const yearButton: HTMLElement = Tag.render`
				<button
					type="button"
					class="ui-year-picker-year"
					data-year="${year}"
					data-tab-priority="true"
					onmouseenter="${this.#handleMouseEnter.bind(this)}"
					onmouseleave="${this.#handleMouseLeave.bind(this)}"
				>${Text.encode(year.name)}</button>
			`;

			Dom.append(yearButton, quarterContainer);

			return yearButton;
		});

		const currentYear: number = Number(button.dataset.year);
		if (currentYear !== year.year)
		{
			button.dataset.year = year.year;
			button.textContent = year.name;
		}

		if (year.current)
		{
			Dom.addClass(button, '--current');
		}
		else
		{
			Dom.removeClass(button, '--current');
		}

		if (year.selected)
		{
			Dom.addClass(button, '--selected');
		}
		else
		{
			Dom.removeClass(button, '--selected');
		}

		if (year.focused)
		{
			Dom.addClass(button, '--focused');
		}
		else
		{
			Dom.removeClass(button, '--focused');
		}

		button.tabIndex = year.tabIndex;

		return button;
	}

	#handleMouseEnter(event: MouseEvent): void
	{
		const dataset = event.target.dataset;
		const year = Text.toInteger(dataset.year);
		this.emit('onFocus', { year });
	}

	#handleMouseLeave(event: MouseEvent): void
	{
		this.emit('onBlur');
	}

	#handleYearClick(event: MouseEvent): void
	{
		if (!Dom.hasClass(event.target, 'ui-year-picker-year'))
		{
			return;
		}

		const year = Text.toInteger(event.target.dataset.year);
		this.emit('onSelect', { year });
	}
}
