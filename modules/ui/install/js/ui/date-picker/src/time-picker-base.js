import { Type } from 'main.core';
import { BasePicker } from './base-picker';

export type TimePickerHour = {
	index: number,
	name: string,
	value: number,
	selected: boolean,
	focused: boolean,
	tabIndex: number,
};

export type TimePickerMinute = {
	index: number,
	name: string,
	value: number,
	selected: boolean,
	hidden: boolean,
	focused: boolean,
	tabIndex: number,
};

export type TimePickerMeridiem = {
	index: number,
	name: string,
	value: number,
	selected: boolean,
};

export class TimePickerBase extends BasePicker
{
	#mode: 'datetime' | 'range-start' | 'range-end' = 'datetime';
	#currentMinuteStep: number = Infinity;
	#focusColumn: 'hours' | 'minutes' = 'hours';

	getTimeDate(): Date
	{
		if (this.#mode === 'range-start')
		{
			return this.getDatePicker().getRangeStart();
		}

		if (this.#mode === 'range-end')
		{
			return this.getDatePicker().getRangeEnd();
		}

		return this.getDatePicker().getSelectedDate();
	}

	setMode(mode: string): void
	{
		this.#mode = mode;
	}

	getMode(): 'datetime' | 'range-start' | 'range-end'
	{
		return this.#mode;
	}

	getFocusColumn(): 'hours' | 'minutes'
	{
		return this.#focusColumn;
	}

	setFocusColumn(column: 'hours' | 'minutes'): void
	{
		if (Type.isStringFilled(column) && ['hours', 'minutes'].includes(column))
		{
			this.#focusColumn = column;
		}
	}

	getHours(): TimePickerHour[]
	{
		const selectedDate = this.getTimeDate();
		const selectedHour = selectedDate === null ? -1 : selectedDate.getUTCHours();
		const isAmPmMode = this.getDatePicker().isAmPmMode();
		const focusDate = this.getDatePicker().getFocusDate();
		const focusHour = focusDate === null ? selectedHour : focusDate.getUTCHours();
		const initialFocusHour = this.getDatePicker().getInitialFocusDate(this.getMode()).getUTCHours();

		const hours: TimePickerHour[] = [];
		for (let hour = 0, index = 0; hour < 24; hour++, index++)
		{
			let hourToDisplay = hour;
			if (isAmPmMode)
			{
				hourToDisplay %= 12;
				hourToDisplay = hourToDisplay === 0 ? 12 : hourToDisplay;
			}

			hours.push({
				index,
				name: isAmPmMode ? hourToDisplay : String(hourToDisplay).padStart(2, '0'),
				value: hour,
				selected: selectedHour === hour,
				focused: focusHour === hour && this.getFocusColumn() === 'hours',
				tabIndex: focusHour === hour || initialFocusHour === hour ? 0 : -1,
			});
		}

		return hours;
	}

	getMinutes(): TimePickerMinute[]
	{
		const selectedDate = this.getTimeDate();
		const selectedMinute = selectedDate === null ? -1 : selectedDate.getUTCMinutes();
		const step = Math.min(this.getDatePicker().getMinuteStepByDate(selectedDate), this.#currentMinuteStep);
		const focusDate = this.getDatePicker().getFocusDate();
		const focusMinute = focusDate === null ? selectedMinute : focusDate.getUTCMinutes();
		const initialFocusMinute = this.getDatePicker().getInitialFocusDate(this.getMode()).getUTCMinutes();

		this.#currentMinuteStep = step;

		const minutes: TimePickerMinute[] = [];
		for (let minute = 0, index = 0; minute < 60; minute++)
		{
			const hidden = minute % step !== 0;
			minutes.push({
				index,
				name: String(minute).padStart(2, '0'),
				value: minute,
				selected: selectedMinute === minute,
				hidden,
				focused: !hidden && focusMinute === minute && this.getFocusColumn() === 'minutes',
				tabIndex: !hidden && (focusMinute === minute || initialFocusMinute === minute) ? 0 : -1,
			});

			if (!hidden)
			{
				index++;
			}
		}

		return minutes;
	}

	getMeridiems(): TimePickerMeridiem[]
	{
		const selectedDate = this.getTimeDate();
		const selectedHour = selectedDate === null ? -1 : selectedDate.getUTCHours();
		const isPm = selectedHour >= 12;

		return [
			{ index: 0, name: 'AM', value: 'am', selected: !isPm },
			{ index: 1, name: 'PM', value: 'pm', selected: isPm },
		];
	}

	getCurrentMinuteStep(): number
	{
		return this.#currentMinuteStep;
	}

	onHide()
	{
		this.setFocusColumn('hours');
	}

	render()
	{
		const picker = this.getDatePicker();
		const timeDate = this.getTimeDate();
		if (timeDate === null)
		{
			this.getHeaderTitle().textContent = '';
		}
		else
		{
			this.getHeaderTitle().textContent = (
				picker.getType() === 'time'
					? picker.formatTime(timeDate)
					: picker.formatDate(timeDate)
			);
		}
	}
}
