import { Dom, Tag, Text, Event } from 'main.core';
import { DateTimeFormat } from 'main.date';
import { MemoryCache, type BaseCache } from 'main.core.cache';
import { BasePicker } from './base-picker';

import { createUtcDate } from './helpers/create-utc-date';
import { getDate } from './helpers/get-date';
import { isDatesEqual } from './helpers/is-dates-equal';

export type MonthPickerQuarter = Array<MonthPickerMonth>;
export type MonthPickerMonth = {
	name: string,
	date: Date,
	year: number,
	month: number,
	current: boolean,
	selected: boolean,
	focused: boolean,
	tabIndex: number,
};

import './css/month-picker.css';

export class MonthPicker extends BasePicker
{
	#refs: BaseCache<HTMLElement> = new MemoryCache();

	getContainer(): HTMLElement
	{
		return this.#refs.remember('container', () => {
			return Tag.render`
				<div class="ui-month-picker">
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
				<button type="button" class="ui-month-picker-header-title" onclick="${this.#handleTitleClick.bind(this)}"></button>
			`;
		});
	}

	getMonths(): MonthPickerQuarter[]
	{
		const { year } = getDate(this.getDatePicker().getViewDate());
		const today = this.getDatePicker().getToday();
		const focusDate = this.getDatePicker().getFocusDate();
		const initialFocusDate = this.getDatePicker().getInitialFocusDate();
		// const formatter = new Intl.DateTimeFormat(
		// 	this.getDatePicker().getLocale(),
		// 	{ month: 'short', timeZone: 'UTC' },
		// );

		const months: MonthPickerQuarter[] = [];
		let currentMonthIndex = 0;
		for (let quarterIndex = 0; quarterIndex < 4; quarterIndex++)
		{
			const quarter: MonthPickerQuarter = [];
			for (let monthIndex = 0; monthIndex < 3; monthIndex++)
			{
				const date = createUtcDate(year, currentMonthIndex);
				const focused = isDatesEqual(date, focusDate, 'month');
				const month: MonthPickerMonth = {
					name: DateTimeFormat.format('f', date, null, true),
					// name: formatter.format(date),
					date,
					year,
					month: currentMonthIndex,
					current: isDatesEqual(date, today, 'month'),
					selected: this.getDatePicker().isDateSelected(date, 'month'),
					focused,
					tabIndex: focused || isDatesEqual(date, initialFocusDate, 'month') ? 0 : -1,
				};

				quarter.push(month);
				currentMonthIndex++;
			}

			months.push(quarter);
		}

		return months;
	}

	renderTo(container: HTMLElement)
	{
		super.renderTo(container);

		Event.bind(this.getContentContainer(), 'click', this.#handleMonthClick.bind(this));
	}

	render(): void
	{
		const isFocused = this.getDatePicker().isFocused();
		let focusButton: HTMLButtonElement = null;
		this.getMonths().forEach((quarter: MonthPickerQuarter, index) => {
			const quarterContainer: HTMLElement = this.#renderQuarter(index);
			quarter.forEach((month: MonthPickerMonth) => {
				const button = this.#renderMonth(month, quarterContainer);
				if (month.focused)
				{
					focusButton = button;
				}
			});
		});

		if (focusButton !== null && isFocused)
		{
			focusButton.focus({ preventScroll: true });
		}

		const { year: currentYear } = getDate(this.getDatePicker().getViewDate());
		this.getHeaderTitle().textContent = currentYear;
	}

	#renderQuarter(index: number): HTMLElement
	{
		return this.#refs.remember(`quarter-${index}`, () => {
			const container: HTMLElement = Tag.render`<div class="ui-month-picker-quarter"></div>`;
			Dom.append(container, this.getContentContainer());

			return container;
		});
	}

	#renderMonth(month, quarterContainer: HTMLElement): HTMLButtonElement
	{
		const button: HTMLElement = this.#refs.remember(`month-${month.month}`, () => {
			const monthButton: HTMLElement = Tag.render`
				<button
					type="button"
					class="ui-month-picker-month"
					data-year="${month.year}"
					data-month="${month.month}"
					data-tab-priority="true"
					onmouseenter="${this.#handleMouseEnter.bind(this)}"
					onmouseleave="${this.#handleMouseLeave.bind(this)}"
				>${Text.encode(month.name)}</button>
			`;

			Dom.append(monthButton, quarterContainer);

			return monthButton;
		});

		const currentYear: number = Number(button.dataset.year);
		if (currentYear !== month.year)
		{
			button.dataset.year = month.year;
		}

		if (month.current)
		{
			Dom.addClass(button, '--current');
		}
		else
		{
			Dom.removeClass(button, '--current');
		}

		if (month.selected)
		{
			Dom.addClass(button, '--selected');
		}
		else
		{
			Dom.removeClass(button, '--selected');
		}

		if (month.focused)
		{
			Dom.addClass(button, '--focused');
		}
		else
		{
			Dom.removeClass(button, '--focused');
		}

		button.tabIndex = month.tabIndex;

		return button;
	}

	#handleMouseEnter(event: MouseEvent): void
	{
		const dataset = event.target.dataset;
		const year = Text.toInteger(dataset.year);
		const month = Text.toInteger(dataset.month);
		this.emit('onFocus', { year, month });
	}

	#handleMouseLeave(event: MouseEvent): void
	{
		this.emit('onBlur');
	}

	#handleMonthClick(event: MouseEvent): void
	{
		if (!Dom.hasClass(event.target, 'ui-month-picker-month'))
		{
			return;
		}

		const year = Text.toInteger(event.target.dataset.year);
		const month = Text.toInteger(event.target.dataset.month);
		this.emit('onSelect', { year, month });
	}

	#handleTitleClick(event: MouseEvent): void
	{
		this.emit('onTitleClick');
	}
}
