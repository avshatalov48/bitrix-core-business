import { Event, Loc } from 'main.core';
import { InlineSelector } from './inline-selector';

export class TimeSelector extends InlineSelector
{
	#clockInstance;

	destroy()
	{
		if (this.#clockInstance)
		{
			this.#clockInstance.closeWnd();
		}
	}

	renderTo(targetInput: Element)
	{
		this.targetInput = targetInput; //this.targetInput = Runtime.clone(targetInput);

		const datetime = new Date();
		datetime.setHours(0, 0, 0, 0);
		datetime.setTime(datetime.getTime() + this.#getCurrentTime() * 1000);

		this.targetInput.value = this.constructor.#formatTime(datetime);

		Event.bind(targetInput, 'click', this.showClock.bind(this));
	}

	showClock(): void
	{
		if (!this.#clockInstance)
		{
			this.#clockInstance = new BX.CClockSelector({
				start_time: this.#getCurrentTime(),
				node: this.targetInput,
				callback: this.#onTimeSelect.bind(this),
			});
		}

		this.#clockInstance.Show();
	}

	#onTimeSelect(time): void
	{
		this.targetInput.value = time;
		BX.fireEvent(this.targetInput, 'change');
		this.#clockInstance.closeWnd();
	}

	#getCurrentTime(): number
	{
		return this.#convertTimeToSeconds(this.targetInput.value);
	}

	#convertTimeToSeconds(time: string): number
	{
		const timeParts = time.split(/[\s:]+/).map(part => parseInt(part));

		let [hours, minutes] = timeParts;
		if (timeParts.length === 3)
		{
			const period = timeParts[2];

			if (period === 'pm' && hours < 12)
			{
				hours += 12;
			}
			else if (period === 'am' && hours === 12)
			{
				hours = 0;
			}
		}

		return hours * 3600 + minutes * 60;
	}

	static #formatTime(datetime: Date): string
	{
		const getFormat = (formatId) => (
			BX.date.convertBitrixFormat(Loc.getMessage(formatId)).replace(/:?\s*s/, '')
		);

		const dateFormat = getFormat('FORMAT_DATE');
		const timeFormat = getFormat('FORMAT_DATETIME').replace(dateFormat, '').trim();

		return BX.date.format(timeFormat, datetime);
	}
}