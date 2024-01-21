import { DateTimeFormat } from 'main.date';

export class DateFormatter
{
	#date: Date;
	#currentDate: Date;
	static formatDate(timestamp: number): string
	{
		return new DateFormatter(new Date(timestamp)).formatDate();
	}

	constructor(date: Date)
	{
		this.#date = date;
		this.#currentDate = new Date();
	}

	formatDate(): string
	{
		let format = '';

		if (this.#isToday())
		{
			format = DateTimeFormat.getFormat('SHORT_TIME_FORMAT');
		}
		else if (this.#isCurrentWeek())
		{
			format = 'D';
		}
		else if (this.#isCurrentYear())
		{
			format = DateTimeFormat.getFormat('DAY_SHORT_MONTH_FORMAT');
		}
		else
		{
			format = DateTimeFormat.getFormat('MEDIUM_DATE_FORMAT');
		}

		return DateTimeFormat.format(format, this.#date.getTime() / 1000);
	}

	#isToday(): boolean
	{
		return this.#date.toLocaleDateString() === this.#currentDate.toLocaleDateString();
	}

	#isCurrentWeek(): boolean
	{
		const currentWeekNumber = parseInt(DateTimeFormat.format('W', this.#currentDate), 10);
		const weekNumber = parseInt(DateTimeFormat.format('W', this.#date), 10);

		return this.#isCurrentYear() && weekNumber === currentWeekNumber;
	}

	#isCurrentYear(): boolean
	{
		return this.#date.getFullYear() === this.#currentDate.getFullYear();
	}
}
