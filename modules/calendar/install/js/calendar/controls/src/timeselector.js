import {Util} from 'calendar.util';
import {Type, Loc} from 'main.core';

export class TimeSelector {
	Z_INDEX = 4000;
	valueList = [];

	constructor(params)
	{
		this.DOM = {
			wrap: params.wrap,
			input: params.input
		};

		for (let hour = 0; hour < 24; hour++)
		{
			this.valueList.push({value: hour * 60, label: Util.formatTime(hour, 0)});
			this.valueList.push({value: hour * 60 + 30, label: Util.formatTime(hour, 30)});
		}

		this.onChangeCallback = Type.isFunction(params.onChangeCallback) ? params.onChangeCallback : null;
		this.selectContol = new BX.Calendar.Controls.SelectInput({
			input: this.DOM.input,
			zIndex: this.Z_INDEX,
			values: this.valueList,
			onChangeCallback: (data) => {
				if (this.onChangeCallback)
				{
					this.onChangeCallback(this.selectContol.getInputValue(), data.dataValue);
				}
			}
		});
	}

	highlightValue(date)
	{
		this.valueList.forEach(el => el.selected = false); // unselect previous time

		const minutes = date.getHours() * 60 + date.getMinutes();
		this.selectContol.setValue({value: minutes}); // this is needed for correct scroll

		let selectedValue = this.valueList.find(el => el.value === minutes);
		if (!selectedValue)
		{
			return;
		}

		selectedValue.selected = true;
		this.selectContol.setValueList(this.valueList);
	}

	updateDurationHints(fromTime, toTime, fromDate, toDate)
	{
		const parsedFromTime = Util.parseTime(fromTime);
		const parsedToTime = Util.parseTime(toTime);
		const parsedFromDate = Util.parseDate(fromDate);
		const parsedToDate = Util.parseDate(toDate);

		const fromMinutes = parsedFromTime.h * 60 + parsedFromTime.m;
		const toMinutes = parsedToTime.h * 60 + parsedToTime.m;
		const isSameDate = fromDate === toDate;
		const iterateFrom = isSameDate ? this.approximate(fromMinutes + 15, 15) : 0;
		const firstHour = this.approximate(fromMinutes + 60 + 15/2, 30);

		this.valueList = [];

		if (fromDate === toDate)
		{
			this.valueList.push(this.getValueElement(fromMinutes, fromMinutes, toMinutes, parsedFromDate, parsedToDate));
		}

		for (let minute = iterateFrom; minute <= 24 * 60; minute += (isSameDate && minute < firstHour ? 15 : 30))
		{
			this.valueList.push(this.getValueElement(fromMinutes, minute, toMinutes, parsedFromDate, parsedToDate));
		}

		this.selectContol.setValueList(this.valueList);
	}

	getValueElement(fromMinute, currentMinute, toMinute, fromDate, toDate)
	{
		const hour = Math.floor(currentMinute / 60);
		const min = currentMinute % 60;
		const time = Util.formatTime(hour, min);
		const durationHint = this.getStyledDurationHint(fromMinute, currentMinute, fromDate, toDate);
		const selected = currentMinute === toMinute;
		return {value: currentMinute, label: time, hint: durationHint, selected};
	}

	getStyledDurationHint(fromMinute, currentMinute, fromDate, toDate)
	{
		const durationHint = this.getDurationHint(fromMinute, currentMinute, fromDate, toDate);
		if (durationHint !== '')
		{
			return`<div class="menu-popup-item-hint">${durationHint}</div>`;
		}
		return '';
	}

	getDurationHint(fromMinutes, toMinutes, fromDate, toDate)
	{
		const from = new Date(fromDate.getTime() + fromMinutes * 60 * 1000);
		const to = new Date(toDate.getTime() + toMinutes * 60 * 1000);

		const diff = to.getTime() - from.getTime();
		const diffDays = this.approximateFloor(diff / (1000 * 60 * 60 * 24), 1);
		const diffHours = this.approximate(diff / (1000 * 60 * 60), 0.5);
		const diffMinutes = this.approximate(diff / (1000 * 60), 1);
		const diffMinutesApproximation = this.approximate(diffMinutes, 15);

		if (diffDays >= 1)
		{
			return '';
		}

		if (diffMinutes >= 60)
		{
			const approximationMark = diffMinutes !== diffMinutesApproximation ? '~' : '';
			return `${approximationMark}${this.formatDecimal(diffHours)} ${Loc.getMessage('EC_HOUR_SHORT')}`;
		}

		return `${this.formatDecimal(diffMinutes)} ${Loc.getMessage('EC_MINUTE_SHORT')}`;
	}

	formatDecimal(decimal)
	{
		return `${decimal}`.replace('.', ',');
	}

	approximateFloor(value, accuracy) {
		return Math.floor(value / accuracy) * accuracy;
	}

	approximate(value, accuracy) {
		return Math.round(value / accuracy) * accuracy;
	}

}