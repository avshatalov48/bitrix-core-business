import {Type, Dom, Loc} from "calendar.resourcebooking";
export class TimezoneSelector
{
	static timezoneList;

	constructor(params)
	{
		this.params = Type.isPlainObject(params) ? params : {};
		this.DOM = {
			outerWrap: this.params.outerWrap
		};
		Dom.addClass(this.DOM.outerWrap, 'fields enumeration field-item');
		this.create();
	}

	create()
	{
		this.DOM.select = this.DOM.outerWrap.appendChild(Dom.create('select'));
		this.DOM.select.options.add(
			new Option(
				Loc.getMessage('USER_TYPE_LOADING_TIMEZONE_LIST'),
				this.params.selectedValue || '',
				true,
				true)
		);

		this.getTimezoneList().then(
			function(timezoneList)
			{
				Dom.remove(this.DOM.select.options[0]);
				timezoneList.forEach(function(timezone)
				{
					let selected = this.params.selectedValue ? this.params.selectedValue === timezone.value : timezone.selected;

					this.DOM.select.options.add(
						new Option(
							timezone.label,
							timezone.value,
							selected,
							selected));
				}, this);
			}.bind(this)
		);
	}

	getTimezoneList(params)
	{
		params = params || {};

		return new Promise((resolve) => {
			if (!TimezoneSelector.timezoneList || params.clearCache)
			{
				BX.ajax.runAction('calendar.api.calendarajax.getTimezoneList')
					.then(function (response)
						{
							TimezoneSelector.timezoneList = [];
							for (let key in response.data)
							{
								if (response.data.hasOwnProperty(key))
								{
									TimezoneSelector.timezoneList.push({
										value: response.data[key].timezone_id,
										label: response.data[key].title,
										selected: response.data[key].default
									});
								}
							}
							resolve(TimezoneSelector.timezoneList);
						}.bind(this),
						function (response)
						{
							resolve(response);
						});
			}
			else
			{
				resolve(TimezoneSelector.timezoneList);
			}
		});
	}

	getValue()
	{
		return this.DOM.select.value;
	}
}







