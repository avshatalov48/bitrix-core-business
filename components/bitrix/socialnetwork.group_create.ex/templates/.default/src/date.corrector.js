import {Event} from 'main.core';

type Params = {
	culture: CultureData
}

type CultureData = {
	shortDateFormat: string
}

export class DateCorrector
{
	#dateStartInput;
	#dateEndInput;
	#culture: CultureData;

	constructor(params: Params)
	{
		this.#dateStartInput = this.#getDateInput('PROJECT_DATE_START');
		this.#dateEndInput = this.#getDateInput('PROJECT_DATE_FINISH');

		this.#culture = params.culture;

		this.#bindHandlers();
	}

	#getDateInput(name: string): ?HTMLElement
	{
		if (document.getElementsByName(name)[0])
		{
			return document.getElementsByName(name)[0];
		}

		return null;
	}

	#bindHandlers()
	{
		if (this.#dateStartInput)
		{
			Event.bind(this.#dateStartInput, 'change', this.#adjustDates.bind(this, true));
		}

		if (this.#dateEndInput)
		{
			Event.bind(this.#dateEndInput, 'change', this.#adjustDates.bind(this, false));
		}
	}

	#adjustDates(startChanged: boolean)
	{
		const start = this.#getTimeStamp(this.#dateStartInput.value);
		const end = this.#getTimeStamp(this.#dateEndInput.value);

		const startDate = start ? new Date(start * 1000) : null;
		const endDate = end ? new Date(end * 1000) : null;

		if (startDate && endDate)
		{
			if (startDate >= endDate)
			{
				const defaultOffset = 86400 * 1000;

				if (startChanged)
				{
					const newEndDate = new Date(startDate.getTime() + defaultOffset);

					this.#dateEndInput.value = this.#getFormatDate(newEndDate.getTime() / 1000);
				}
				else
				{
					const newStartDate = new Date(endDate.getTime() - defaultOffset);

					this.#dateStartInput.value = this.#getFormatDate(newStartDate.getTime() / 1000);
				}
			}
		}
	}

	#getTimeStamp(date: string): ?number
	{
		if (date.toString().length > 0)
		{
			// eslint-disable-next-line bitrix-rules/no-bx
			const parsedValue = BX.parseDate(date, true);
			if (parsedValue === null)
			{
				return null;
			}

			return this.#convertToSeconds(parsedValue.getTime());
		}

		return null;
	}

	#convertToSeconds(value: number): number
	{
		return Math.floor((parseInt(value) / 1000));
	}

	#getFormatDate(timeStamp: number): string
	{
		const date = new Date(timeStamp * 1000);

		return BX.date.format(this.#culture.shortDateFormat, date)
	}
}