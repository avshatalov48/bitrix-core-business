import { Type, Event, Dom } from 'main.core';
import { addDate } from './helpers/add-date';
import { cloneDate } from './helpers/clone-date';
import { getDate } from './helpers/get-date';
import { getFocusableBoundaryElements } from './helpers/get-focusable-boundary-elements';
import { setTime } from './helpers/set-time';

import type { DatePicker } from './date-picker';
import type { TimePickerGrid } from './time-picker-grid';

const keyMap = {
	ArrowRight: {
		day: 1,
		month: 1,
		year: 1,
		hours: 1,
		minutes: 1,
	},
	ArrowLeft: {
		day: -1,
		month: -1,
		year: -1,
		hours: -1,
		minutes: -1,
	},
	ArrowUp: {
		day: -7,
		month: -3,
		year: -3,
		hours: -4,
		minutes: -2,
	},
	ArrowDown: {
		day: 7,
		month: 3,
		year: 3,
		hours: 4,
		minutes: 2,
	},
};

export class KeyboardNavigation
{
	#datePicker: DatePicker = null;
	#lastFocusElement: HTMLElement = null;

	constructor(datePicker)
	{
		this.#datePicker = datePicker;
	}

	init(): void
	{
		Event.bind(this.#datePicker.getContainer(), 'keydown', this.#handleKeyDown.bind(this));
		Event.bind(this.#datePicker.getContainer(), 'focusin', this.#handleFocusIn.bind(this));
		Event.bind(this.#datePicker.getContainer(), 'focusout', this.#handleFocusOut.bind(this));
	}

	#handleKeyDown(event: KeyboardEvent): void
	{
		const picker = this.#datePicker;

		if (
			event.key === 'Backspace'
			&& picker.getType() === 'date'
			&& ['year', 'month', 'time'].includes(picker.getCurrentView())
		)
		{
			event.preventDefault();
			this.resetLastFocusElement();
			picker.setCurrentView('day');

			return;
		}

		if (event.key === 'Tab' && !picker.isInline())
		{
			this.#handleFocusChange(event);

			return;
		}

		const view = picker.getCurrentView();
		if (view === 'time' && picker.getTimePickerStyle() === 'wheel')
		{
			return;
		}

		if (event.key === 'Space' || event.key === 'Enter' || event.key === ' ')
		{
			event.preventDefault();
			this.resetLastFocusElement();
			event.target.click();
		}
		else if (!Type.isUndefined(keyMap[event.key]))
		{
			event.preventDefault();
			this.resetLastFocusElement();
			const initialFocus = picker.getFocusDate() === null && this.#isRootContainerFocused();

			if (view === 'time')
			{
				const timePicker: TimePickerGrid = this.#datePicker.getPicker('time');
				let currentFocusDate = cloneDate(picker.getInitialFocusDate(timePicker.getMode()));
				let { hours, minutes } = getDate(currentFocusDate);

				if (initialFocus)
				{
					picker.setFocusDate(currentFocusDate);
					this.#adjustLastFocusElement();
				}
				else if (timePicker.getFocusColumn() === 'hours')
				{
					const increment = keyMap[event.key].hours;
					hours += increment;
					if (hours < 0)
					{
						hours += 24;
					}
					else if (hours > 23)
					{
						hours -= 24;
					}

					currentFocusDate = setTime(currentFocusDate, hours, null, null);
					picker.setFocusDate(currentFocusDate);
					this.#adjustLastFocusElement();
				}
				else if (timePicker.getFocusColumn() === 'minutes')
				{
					const increment = keyMap[event.key].minutes;
					minutes += timePicker.getCurrentMinuteStep() * increment;
					if (minutes < 0)
					{
						minutes += 60;
					}
					else if (minutes > 59)
					{
						minutes -= 60;
					}

					currentFocusDate = setTime(currentFocusDate, null, minutes, null);
					picker.setFocusDate(currentFocusDate);
					timePicker.adjustMinuteFocusPosition();
					this.#adjustLastFocusElement();
				}
			}
			else
			{
				const currentFocusDate = cloneDate(picker.getInitialFocusDate());
				if (initialFocus)
				{
					picker.setFocusDate(currentFocusDate);
				}
				else
				{
					const increment = keyMap[event.key][view];
					const focusDate = addDate(currentFocusDate, view, increment);
					picker.setFocusDate(focusDate);
				}

				this.#adjustLastFocusElement();
			}
		}
	}

	#isRootContainerFocused(): boolean
	{
		const rootContainer = this.#datePicker.getContainer();

		return rootContainer.ownerDocument.activeElement === rootContainer;
	}

	#handleFocusChange(event: KeyboardEvent): void
	{
		let prev: HTMLElement = null;
		let next: HTMLElement = null;
		const currentPickerContainer = this.#datePicker.getPicker().getContainer();
		if (this.#isRootContainerFocused())
		{
			[prev = null, next = null] = getFocusableBoundaryElements(
				currentPickerContainer,
				(element: HTMLElement) => element.dataset.tabPriority === 'true',
			);
		}

		if (prev === null && next === null)
		{
			[prev, next] = getFocusableBoundaryElements(currentPickerContainer);
		}

		if (event.shiftKey)
		{
			prev?.focus({ preventScroll: true, focusVisible: true });
			this.setLastFocusElement(prev);
		}
		else
		{
			next?.focus({ preventScroll: true, focusVisible: true });
			this.setLastFocusElement(next);
		}

		event.preventDefault();
	}

	setLastFocusElement(element: HTMLElement): void
	{
		this.resetLastFocusElement();

		this.#lastFocusElement = element;
		Dom.addClass(this.#lastFocusElement, '--focus-visible');
	}

	resetLastFocusElement(): void
	{
		Dom.removeClass(this.#lastFocusElement, '--focus-visible');
		this.#lastFocusElement = null;
	}

	#adjustLastFocusElement(): void
	{
		const rootContainer = this.#datePicker.getContainer();
		const activeElement = rootContainer.ownerDocument.activeElement;
		if (rootContainer.contains(activeElement))
		{
			this.setLastFocusElement(activeElement);
		}
	}

	#handleFocusIn(event: FocusEvent): void
	{
		this.resetLastFocusElement();
		// this.#lastFocusElement = event.target;
	}

	#handleFocusOut(event: FocusEvent): void
	{
		this.resetLastFocusElement();
		// this.#lastFocusElement = event.target;
	}
}
