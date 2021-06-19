import {Util} from 'calendar.util';
import {Type} from 'main.core';

export class TimeSelector {
	Z_INDEX = 4000;
	MIN_WIDTH = 102;
	static valueList = null;

	constructor(params)
	{
		this.DOM = {
			wrap: params.wrap,
			input: params.input
		};

		this.onChangeCallback = Type.isFunction(params.onChangeCallback) ? params.onChangeCallback : null;
		this.create();
	}

	create()
	{
		this.selectContol = new BX.Calendar.Controls.SelectInput({
			input: this.DOM.input,
			zIndex: this.Z_INDEX,
			values: TimeSelector.getValueList(),
			minWidth: this.MIN_WIDTH,
			onChangeCallback: () => {
				if (this.onChangeCallback)
				{
					this.onChangeCallback(this.selectContol.getInputValue());
				}
			}
		});
	}

	static adaptTimeValue(timeValue)
	{
		timeValue = parseInt(timeValue.h * 60) + parseInt(timeValue.m);
		let
			timeList = TimeSelector.getValueList(),
			diff = 24 * 60,
			ind = false,
			i;

		for (i = 0; i < timeList.length; i++)
		{
			if (Math.abs(timeList[i].value - timeValue) < diff)
			{
				diff = Math.abs(timeList[i].value - timeValue);
				ind = i;
				if (diff <= 15)
				{
					break;
				}
			}
		}

		return timeList[ind || 0];
	}

	static getValueList()
	{
		if (!TimeSelector.valueList)
		{
			TimeSelector.valueList = [];
			let i;
			for (i = 0; i < 24; i++)
			{
				TimeSelector.valueList.push({value: i * 60, label: Util.formatTime(i, 0)});
				TimeSelector.valueList.push({value: i * 60 + 30, label: Util.formatTime(i, 30)});
			}
		}
		return TimeSelector.valueList;
	}

	setValue(value)
	{
		let time;
		if (Type.isDate(value))
		{
			time = {
				h: value.getHours(),
				m: value.getMinutes()
			};
		}
		else
		{
			time = Util.parseTime(value);
		}

		const adaptedValue = TimeSelector.adaptTimeValue(time);

		this.selectContol.setValue({value: adaptedValue.value});

		let hour = Math.floor(adaptedValue.value / 60);
		let min = (adaptedValue.value) - hour * 60;

		this.DOM.input.value = Util.formatTime(hour, min);
	}
}