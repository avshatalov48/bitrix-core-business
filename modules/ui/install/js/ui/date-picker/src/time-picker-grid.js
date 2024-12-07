import { Dom, Tag, Type, Loc, Runtime, Text } from 'main.core';
import { MemoryCache, type BaseCache } from 'main.core.cache';
import { TimePickerBase, type TimePickerHour, type TimePickerMinute } from './time-picker-base';

import './css/time-picker-grid.css';

export class TimePickerGrid extends TimePickerBase
{
	#refs: BaseCache<HTMLElement> = new MemoryCache();
	#firstRender: boolean = true;

	getContainer(): HTMLElement
	{
		return this.#refs.remember('container', () => {
			return Tag.render`
				<div class="ui-time-picker-grid${this.getDatePicker().isAmPmMode() ? ' --am-pm' : ''}">
					${
						this.getDatePicker().getType() === 'time'
							? null
							: this.getHeaderContainer(this.getPrevBtn(), this.getHeaderTitle())
					}
					<div class="ui-time-picker-grid-content">
						<div class="ui-time-picker-grid-column">
							<div class="ui-time-picker-grid-column-title">${Loc.getMessage('UI_DATE_PICKER_HOURS')}</div>
							<div class="ui-time-picker-grid-column-content">
								${this.getHoursContainer()}
							</div>
						</div>
						<div class="ui-time-picker-grid-column-separator"></div>
						<div class="ui-time-picker-grid-column">
							<div class="ui-time-picker-grid-column-title">${Loc.getMessage('UI_DATE_PICKER_MINUTES')}</div>
							<div class="ui-time-picker-grid-column-content">
								${this.getMinutesContainer()}
							</div>
						</div>
					</div>
				</div>
			`;
		});
	}

	getHeaderTitle(): HTMLElement
	{
		return this.#refs.remember('header-title', () => {
			return Tag.render`
				<div class="ui-time-picker-grid-header-title" onclick="${this.#handleTitleClick.bind(this)}"></div>
			`;
		});
	}

	getHoursContainer(): HTMLElement
	{
		return this.#refs.remember('hours', () => {
			return Tag.render`
				<div 
					class="ui-time-picker-grid-column-items --hours" 
					onclick="${this.#handleItemClick.bind(this)}"
				></div>
			`;
		});
	}

	getMinutesContainer(): HTMLElement
	{
		return this.#refs.remember('minutes', () => {
			return Tag.render`
				<div 
					class="ui-time-picker-grid-column-items --minutes" 
					onclick="${this.#handleItemClick.bind(this)}"
					onscroll="${Runtime.debounce(this.#adjustScrollShadows, 100, this)}"
				></div>
			`;
		});
	}

	onHide()
	{
		super.onHide();
		this.#firstRender = true;
	}

	render()
	{
		super.render();

		let focusedHourBtn: HTMLButtonElement = null;
		this.getHours().forEach((hour: TimePickerHour) => {
			const button: HTMLButtonElement = this.#renderHour(hour, this.getHoursContainer());
			if (hour.focused)
			{
				focusedHourBtn = button;
			}
		});

		let selectedMinute: HTMLElement = null;
		let focusedMinute: HTMLButtonElement = null;
		this.getMinutes().forEach((minute: TimePickerMinute) => {
			const button: HTMLButtonElement = this.#renderMinute(minute, this.getMinutesContainer());
			if (minute.selected)
			{
				selectedMinute = button;
			}

			if (minute.focused)
			{
				focusedMinute = button;
			}
		});

		if (this.#firstRender)
		{
			Dom.style(this.getMinutesContainer(), 'height', `${this.getHoursContainer().offsetHeight}px`);

			if (selectedMinute !== null)
			{
				this.#adjustScrollPosition(selectedMinute, false);
			}

			this.#adjustScrollShadows();

			this.#firstRender = false;
		}

		if (this.getDatePicker().isFocused())
		{
			if (this.getFocusColumn() === 'hours' && focusedHourBtn !== null)
			{
				focusedHourBtn.focus({ preventScroll: true });
			}
			else if (this.getFocusColumn() === 'minutes' && focusedMinute !== null)
			{
				focusedMinute.focus({ preventScroll: true });
			}
		}
	}

	#renderHour(hour: TimePickerHour, container: HTMLElement): HTMLButtonElement
	{
		const button: HTMLButtonElement = this.#refs.remember(`hour-${hour.value}`, () => {
			const hourContainer: HTMLButtonElement = Tag.render`
				<button
					type="button"
					class="ui-time-picker-grid-item" 
					data-index="${hour.index}" 
					data-hour="${hour.value}"
					data-tab-priority="true"
					onmouseenter="${this.#handleMouseEnter.bind(this)}"
					onmouseleave="${this.#handleMouseLeave.bind(this)}"
					onfocus="${this.#handleFocus.bind(this)}"
				><span class="ui-time-picker-grid-item-inner">${hour.name}</span></button>
			`;

			if (this.getDatePicker().isAmPmMode())
			{
				if (hour.value === 0)
				{
					hourContainer.dataset.meridiem = 'AM';
					Dom.addClass(hourContainer, '--has-meridiem');
				}
				else if (hour.value === 12)
				{
					hourContainer.dataset.meridiem = 'PM';
					Dom.addClass(hourContainer, '--has-meridiem');
				}
			}

			Dom.append(hourContainer, container);

			return hourContainer;
		});

		if (hour.selected)
		{
			Dom.addClass(button, '--selected');
		}
		else
		{
			Dom.removeClass(button, '--selected');
		}

		if (hour.focused)
		{
			Dom.addClass(button, '--focused');
		}
		else
		{
			Dom.removeClass(button, '--focused');
		}

		button.tabIndex = hour.tabIndex;

		return button;
	}

	#renderMinute(minute: TimePickerMinute, container: HTMLElement): HTMLButtonElement
	{
		const button: HTMLButtonElement = this.#refs.remember(`minute-${minute.value}`, () => {
			const minuteContainer = Tag.render`
				<button
					type="button"
					class="ui-time-picker-grid-item"
					data-index="${minute.index}" 
					data-minute="${minute.value}"
					onmouseenter="${this.#handleMouseEnter.bind(this)}"
					onmouseleave="${this.#handleMouseLeave.bind(this)}"
					onfocus="${this.#handleFocus.bind(this)}"
				><span class="ui-time-picker-grid-item-inner">${minute.name}</span></button>
			`;

			Dom.append(minuteContainer, container);

			return minuteContainer;
		});

		if (minute.selected)
		{
			Dom.addClass(button, '--selected');
		}
		else
		{
			Dom.removeClass(button, '--selected');
		}

		if (minute.hidden)
		{
			button.dataset.index = '';
			Dom.addClass(button, '--hidden');
		}
		else
		{
			button.dataset.index = minute.index;
			Dom.removeClass(button, '--hidden');
		}

		if (minute.focused)
		{
			Dom.addClass(button, '--focused');
		}
		else
		{
			Dom.removeClass(button, '--focused');
		}

		button.tabIndex = minute.tabIndex;

		return button;
	}

	#adjustScrollPosition(selectedMinute: HTMLElement, smooth: boolean = true): void
	{
		const shadowHeight = 20;
		const scrollTop = this.getMinutesContainer().scrollTop;
		const viewportTop = scrollTop + shadowHeight;

		const offsetTop = selectedMinute.offsetTop;
		const offsetBottom = offsetTop + selectedMinute.offsetHeight;
		const viewportHeight = this.getMinutesContainer().offsetHeight;
		const viewportBottom = scrollTop + viewportHeight - shadowHeight;

		const isVisible = (
			(offsetTop >= viewportTop && offsetTop <= viewportBottom)
			&& (offsetBottom <= viewportBottom && offsetBottom >= viewportTop)
		);

		if (!isVisible)
		{
			this.getMinutesContainer().scrollTo({
				top: selectedMinute.offsetTop - viewportHeight / 2,
				behavior: smooth ? 'smooth' : 'instant',
			});
		}
	}

	adjustMinuteFocusPosition(): void
	{
		const item = this.getContainer().ownerDocument.activeElement;
		if (!item.closest('.ui-time-picker-grid-item'))
		{
			return;
		}

		this.#adjustScrollPosition(item);
	}

	#adjustScrollShadows(): void
	{
		const scrollTop = this.getMinutesContainer().scrollTop;
		const scrollHeight = this.getMinutesContainer().scrollHeight;
		const offsetHeight = this.getMinutesContainer().offsetHeight;
		const columnContainer = this.getMinutesContainer().parentNode.parentNode;
		if (scrollTop > 0)
		{
			Dom.addClass(columnContainer, '--top-shadow');
		}
		else
		{
			Dom.removeClass(columnContainer, '--top-shadow');
		}

		if (scrollTop === scrollHeight - offsetHeight)
		{
			Dom.removeClass(columnContainer, '--bottom-shadow');
		}
		else
		{
			Dom.addClass(columnContainer, '--bottom-shadow');
		}
	}

	#handleItemClick(event: MouseEvent): void
	{
		const item = event.target;
		if (!item.closest('.ui-time-picker-grid-item'))
		{
			return;
		}

		if (Type.isStringFilled(item.dataset.hour))
		{
			this.setFocusColumn('hours');
			const hour = Number(item.dataset.hour);
			this.emit('onSelect', { hour });
		}
		else if (Type.isStringFilled(item.dataset.minute))
		{
			this.setFocusColumn('minutes');
			this.#adjustScrollPosition(item);

			const minute = Number(item.dataset.minute);
			this.emit('onSelect', { minute });
		}
	}

	#handleMouseEnter(event: MouseEvent): void
	{
		const { hour, minute } = event.target.dataset;
		if (Type.isStringFilled(hour))
		{
			this.setFocusColumn('hours');
			this.emit('onFocus', { hour: Text.toInteger(hour) });
		}
		else if (Type.isStringFilled(minute))
		{
			this.setFocusColumn('minutes');
			this.emit('onFocus', { minute: Text.toInteger(minute) });
		}
	}

	#handleMouseLeave(event: MouseEvent): void
	{
		this.emit('onBlur');
	}

	#handleFocus(event: MouseEvent): void
	{
		const { hour, minute } = event.target.dataset;

		const currentColumn = this.getFocusColumn();
		if (Type.isStringFilled(hour))
		{
			this.setFocusColumn('hours');
		}
		else if (Type.isStringFilled(minute))
		{
			this.setFocusColumn('minutes');
		}

		if (currentColumn !== this.getFocusColumn())
		{
			this.render();
		}
	}

	#handleTitleClick(event: MouseEvent): void
	{
		this.emit('onTitleClick');
	}
}
