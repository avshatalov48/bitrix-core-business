import {Type, Dom, BookingUtil, SelectInput} from "../resourcebooking";
import {ViewControlAbstract} from "../viewcontrolabstract";

export class DurationSelector extends ViewControlAbstract
{
	constructor(params)
	{
		super(params);
		this.name = 'DurationSelector';
		this.data = params.data;
		this.durationList = BookingUtil.getDurationList(params.fullDay);
		this.changeValueCallback = params.changeValueCallback;
		this.defaultValue = params.defaultValue || this.data.defaultValue;
		this.handleSettingsData(params.data);
	}

	handleSettingsData()
	{
		this.durationItems = [];
		if (Type.isArray(this.durationList))
		{
			this.durationList.forEach(function(item)
			{
				this.durationItems.push({
					id: item.value,
					title: item.label
				});
			}, this);
		}
	}

	displayControl()
	{
		this.DOM.durationInput = this.DOM.controlWrap.appendChild(Dom.create('INPUT', {
			attrs: {
				value: this.data.defaultValue || null,
				type: 'text'
			},
			props: {className: 'calendar-resbook-webform-block-input calendar-resbook-webform-block-input-dropdown'}
		}));

		this.durationControl = new SelectInput({
			input: this.DOM.durationInput,
			values: this.durationList,
			value: this.data.defaultValue || null,
			editable: this.data.manualInput === 'Y',
			defaultValue: this.defaultValue,
			setFirstIfNotFound: true,
			onChangeCallback: this.changeValueCallback
		});
	}

	refresh(data)
	{
		this.refreshLabel(data);
		this.data = data;
		this.handleSettingsData(this.data);

		if (this.setDataConfig())
		{
			if (this.isDisplayed())
			{
				this.show({animation: true});
				if (this.durationControl)
				{
					this.durationControl.setValue(this.data.defaultValue || null);
				}
			}
			else
			{
				this.hide({animation: true});
			}
		}
	}

	getSelectedValue()
	{
		let duration = null;
		if (this.durationControl)
		{
			duration = BookingUtil.parseDuration(this.durationControl.getValue());
		}
		else
		{
			duration = parseInt(this.defaultValue);
		}
		return duration;
	}
}