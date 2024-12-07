import { Dom, Loc, Runtime, Tag } from 'main.core';
import { type BaseCache, MemoryCache } from 'main.core.cache';
import {
	TimePickerBase,
	type TimePickerHour,
	type TimePickerMeridiem,
	type TimePickerMinute,
} from './time-picker-base';

import './css/time-picker.css';

export class TimePickerWheel extends TimePickerBase
{
	#refs: BaseCache<HTMLElement> = new MemoryCache();
	#focusSelectorId: string = null;
	#selectorScrollHandler = Runtime.debounce(this.#handleSelectorScroll, 200, this);

	getContainer(): HTMLElement
	{
		return this.#refs.remember('container', () => {
			return Tag.render`
				<div class="ui-time-picker">
					${
						this.getDatePicker().getType() === 'time'
							? null
							: this.getHeaderContainer(this.getPrevBtn(), this.getHeaderTitle())
					}
					<div class="ui-time-picker-content">
						${this.getTimeHighlighter()}
						<div 
							class="ui-time-picker-selector"
							data-selector-id="hour" 
							onmouseenter="${this.#handleSelectorMouseEnter.bind(this)}"
						>
							<div class="ui-time-picker-selector-title">${Loc.getMessage('UI_DATE_PICKER_HOURS')}</div>
							<div class="ui-time-picker-viewport">
								<div class="ui-time-picker-scroll-container" 
									tabindex="0" 
									onscroll="${this.#selectorScrollHandler}"
									onfocus="${this.#handleFocus.bind(this)}"
								>
									${this.getHoursContainer()}
								</div>
							</div>
						</div>
						<div class="ui-time-picker-time-separator"></div>
						<div 
							class="ui-time-picker-selector"
							data-selector-id="minute" 
							onmouseenter="${this.#handleSelectorMouseEnter.bind(this)}"
						>
							<div class="ui-time-picker-selector-title">${Loc.getMessage('UI_DATE_PICKER_MINUTES')}</div>
							<div class="ui-time-picker-viewport">
								<div class="ui-time-picker-scroll-container" 
									tabindex="0" 
									onscroll="${this.#selectorScrollHandler}"
									onfocus="${this.#handleFocus.bind(this)}"
								>
									${this.getMinutesContainer()}
								</div>
							</div>
						</div>
						${
							this.getDatePicker().isAmPmMode()
								? Tag.render`
									<div 
										class="ui-time-picker-selector" 
										onmouseenter="${this.#handleSelectorMouseEnter.bind(this)}"
										data-selector-id="meridiem"
									>
										<div class="ui-time-picker-selector-title">AM/PM</div>
										<div class="ui-time-picker-viewport">
											<div class="ui-time-picker-scroll-container" 
												tabindex="0" 
												onscroll="${this.#selectorScrollHandler}"
												onfocus="${this.#handleFocus.bind(this)}"
											>
												${this.getMeridiemsContainer()}
											</div>
										</div>
									</div>
								`
								: null
						}
					</div>
				</div>
			`;
		});
	}

	getHeaderTitle(): HTMLElement
	{
		return this.#refs.remember('header-title', () => {
			return Tag.render`
				<div class="ui-time-picker-header-title" onclick="${this.#handleTitleClick.bind(this)}"></div>
			`;
		});
	}

	getHoursContainer(): HTMLElement
	{
		return this.#refs.remember('hours', () => {
			return Tag.render`
				<div 
					class="ui-time-picker-list-container" 
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
					class="ui-time-picker-list-container" 
					onclick="${this.#handleItemClick.bind(this)}"
				></div>
			`;
		});
	}

	getMeridiemsContainer(): HTMLElement
	{
		return this.#refs.remember('meridiems', () => {
			return Tag.render`
				<div 
					class="ui-time-picker-list-container" 
					onclick="${this.#handleItemClick.bind(this)}"
				></div>
			`;
		});
	}

	getTimeHighlighter(): HTMLElement
	{
		return this.#refs.remember('time-highlighter', () => {
			return Tag.render`<div class="ui-time-picker-time-highlighter"></div>`;
		});
	}

	onShow()
	{
		super.onShow();
		this.focusSelector('hour', !this.getDatePicker().isInline());
	}

	renderTo(container: HTMLElement)
	{
		super.renderTo(container);

		this.#adjustScrollHeight(this.getHoursContainer());
		this.#adjustScrollHeight(this.getMinutesContainer());
		if (this.getDatePicker().isAmPmMode())
		{
			this.#adjustScrollHeight(this.getMeridiemsContainer());
		}
	}

	render(): void
	{
		super.render();

		let selectedHourIndex = 0;
		this.getHours().forEach((hour: TimePickerHour) => {
			if (hour.selected)
			{
				selectedHourIndex = hour.index;
			}

			this.#renderHour(hour);
		});

		this.#adjustScrollPosition(this.getHoursContainer(), selectedHourIndex, false);

		let selectedMinuteIndex = 0;
		this.getMinutes().forEach((minute: TimePickerMinute) => {
			if (minute.selected)
			{
				selectedMinuteIndex = minute.index;
			}

			this.#renderMinute(minute);
		});

		this.#adjustScrollPosition(this.getMinutesContainer(), selectedMinuteIndex, false);

		const picker = this.getDatePicker();
		if (picker.isAmPmMode())
		{
			let selectedMeridiemIndex = 0;
			this.getMeridiems().forEach((meridiem: TimePickerMeridiem) => {
				if (meridiem.selected)
				{
					selectedMeridiemIndex = meridiem.index;
				}

				this.#renderMeridiem(meridiem);
			});

			this.#adjustScrollPosition(this.getMeridiemsContainer(), selectedMeridiemIndex, false);
		}
	}

	getItemHeight(): number
	{
		return 30;
	}

	focusSelector(id: string, changePageFocus: boolean = true)
	{
		if (this.#focusSelectorId === id)
		{
			return;
		}

		if (this.#focusSelectorId !== null)
		{
			const currentSelector = (
				this.getContainer().querySelector(`[data-selector-id="${this.#focusSelectorId}"]`)
			);

			Dom.removeClass(currentSelector, '--focused');
		}

		this.#focusSelectorId = id;

		const newSelector: HTMLElement = this.getContainer().querySelector(`[data-selector-id="${id}"]`);
		const scrollContainer: HTMLElement = newSelector.querySelector('[tabindex]:not([tabindex="-1"])');

		Dom.addClass(newSelector, '--focused');

		if (changePageFocus)
		{
			scrollContainer.focus({ preventScroll: true });
		}
	}

	#renderHour(hour: TimePickerHour): void
	{
		const div = this.#refs.remember(`hour-${hour.value}`, () => {
			const hourContainer = Tag.render`
				<div 
					class="ui-time-picker-list-item" 
					data-index="${hour.index}" 
					data-value="${hour.value}"
				>${hour.name}</div>
			`;

			Dom.append(hourContainer, this.getHoursContainer());

			return hourContainer;
		});

		if (hour.selected)
		{
			Dom.addClass(div, '--selected');
		}
		else
		{
			Dom.removeClass(div, '--selected');
		}
	}

	#renderMinute(minute: TimePickerMinute): void
	{
		const div = this.#refs.remember(`minute-${minute.value}`, () => {
			const minuteContainer = Tag.render`
				<div 
					class="ui-time-picker-list-item"
					data-index="${minute.index}" 
					data-value="${minute.value}"
				>${minute.name}</div>
			`;

			Dom.append(minuteContainer, this.getMinutesContainer());

			return minuteContainer;
		});

		if (minute.selected)
		{
			Dom.addClass(div, '--selected');
		}
		else
		{
			Dom.removeClass(div, '--selected');
		}

		if (minute.hidden)
		{
			div.dataset.index = '';
			Dom.addClass(div, '--hidden');
		}
		else
		{
			div.dataset.index = minute.index;
			Dom.removeClass(div, '--hidden');
		}
	}

	#renderMeridiem(meridiem: TimePickerMeridiem): void
	{
		const div = this.#refs.remember(`meridiem-${meridiem.value}`, () => {
			const meridiemContainer = Tag.render`
				<div 
					class="ui-time-picker-list-item"
					data-index="${meridiem.index}" 
					data-value="${meridiem.value}"
				>${meridiem.name}</div>
			`;

			Dom.append(meridiemContainer, this.getMeridiemsContainer());

			return meridiemContainer;
		});

		if (meridiem.selected)
		{
			Dom.addClass(div, '--selected');
		}
		else
		{
			Dom.removeClass(div, '--selected');
		}
	}

	#adjustScrollHeight(listContainer: HTMLElement): void
	{
		const viewport: HTMLElement = listContainer.parentNode.parentNode;
		const offset = viewport.offsetHeight / 2 - this.getItemHeight() / 2;

		Dom.style(listContainer, {
			marginTop: `${offset}px`,
			marginBottom: `${offset}px`,
		});
	}

	#adjustScrollPosition(listContainer: HTMLElement, index: number, smooth: boolean = true): boolean
	{
		const scrollContainer: HTMLElement = listContainer.parentNode;
		const scrollTop = this.getItemHeight() * index;
		if (scrollContainer.scrollTop !== scrollTop)
		{
			scrollContainer.scrollTo({
				top: scrollTop,
				behavior: smooth ? 'smooth' : 'instant',
			});

			return true;
		}

		return false;
	}

	#handleItemClick(event: MouseEvent): void
	{
		const item = event.target;
		if (!item.closest('.ui-time-picker-list-item'))
		{
			return;
		}

		const listContainer: HTMLElement = item.parentNode;
		const index = Number(item.dataset.index);

		const scrollChanged = this.#adjustScrollPosition(listContainer, index);
		if (!scrollChanged)
		{
			this.#selectTime(listContainer.parentNode);
		}
	}

	#handleTitleClick(event: MouseEvent): void
	{
		this.emit('onTitleClick');
	}

	#handleSelectorMouseEnter(event: MouseEvent): void
	{
		this.focusSelector(event.target.dataset.selectorId);
	}

	#handleFocus(event: FocusEvent)
	{
		this.focusSelector(event.target.parentNode.parentNode.dataset.selectorId);
	}

	#handleSelectorScroll(event: MouseEvent): void
	{
		const scrollContainer = event.target;
		const scrollTop = scrollContainer.scrollTop;
		const atSnappingPoint = scrollTop % this.getItemHeight() === 0;
		if (atSnappingPoint)
		{
			this.#selectTime(scrollContainer);
		}
	}

	#selectTime(scrollContainer: HTMLElement): void
	{
		const scrollTop = scrollContainer.scrollTop;
		const index = scrollTop / this.getItemHeight();
		const selector: HTMLElement = scrollContainer.parentNode.parentNode;
		const selectorId = selector.dataset.selectorId;
		const item: HTMLElement = selector.querySelector(`[data-index="${index}"]`);
		const selectedDate = this.getTimeDate();
		const currentHour = selectedDate === null ? -1 : selectedDate.getUTCHours();
		const currentMinute = selectedDate === null ? -1 : selectedDate.getUTCMinutes();

		switch (selectorId)
		{
			case 'hour':
			{
				const hour = Number(item.dataset.value);
				if (currentHour !== hour)
				{
					this.emit('onSelect', { hour });
				}

				break;
			}

			case 'minute':
			{
				const minute = Number(item.dataset.value);
				if (currentMinute !== minute)
				{
					this.emit('onSelect', { minute });
				}

				break;
			}

			case 'meridiem':
			{
				const meridiem = item.dataset.value;
				if (meridiem === 'am' && currentHour >= 12)
				{
					const hour = currentHour - 12;
					this.emit('onSelect', { hour });
				}
				else if (meridiem === 'pm' && currentHour >= 0 && currentHour < 12)
				{
					const hour = currentHour + 12;
					this.emit('onSelect', { hour });
				}

				break;
			}
			default:
				break;
		}
	}
}
