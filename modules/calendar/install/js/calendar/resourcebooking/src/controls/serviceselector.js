import {Type, BookingUtil} from "../resourcebooking";
import {ViewControlAbstract} from "../viewcontrolabstract";
import {ViewDropDownSelect} from "../viewdropdownselect";

export class ServiceSelector extends ViewControlAbstract
{
	constructor(params)
	{
		super(params);
		this.name = 'ServiceSelector';
		this.data = params.data;
		this.serviceList = [];

		this.allServiceList = [];
		if (Type.isArray(params.serviceList))
		{
			params.serviceList.forEach((service) => {
				if (Type.isString(name))
				{
					service.name = service.name.trim();
				}
				this.allServiceList.push(service);
			})
		}
		this.values = [];
		this.changeValueCallback = Type.isFunction(params.changeValueCallback) ? params.changeValueCallback : null;
		if (params.selectedValue)
		{
			this.setSelectedService(params.selectedValue);
		}
		this.handleSettingsData(this.data);
	}

	displayControl()
	{
		this.dropdownSelect = new ViewDropDownSelect({
			wrap: this.DOM.controlWrap,
			values: this.serviceList,
			selected: this.getSelectedService(),
			handleChangesCallback: function (selectedValues)
			{
				if (selectedValues && selectedValues[0])
				{
					this.setSelectedService(selectedValues[0]);
					if (this.changeValueCallback)
					{
						this.changeValueCallback();
					}
				}
			}.bind(this)
		});
		this.dropdownSelect.build();
	}

	refresh(data)
	{
		this.refreshLabel(data);
		this.data = data;

		this.handleSettingsData(this.data);

		if (this.dropdownSelect)
		{
			this.dropdownSelect.setSettings({
				values: this.serviceList,
				selected: this.getSelectedService()
			});
		}

		if (this.setDataConfig())
		{
			if (this.isDisplayed())
			{
				this.show({animation: true});
			}
			else
			{
				this.hide({animation: true});
			}
		}
	}

	handleSettingsData()
	{
		this.serviceIndex = {};
		if (Type.isArray(this.allServiceList))
		{
			this.allServiceList.forEach(function(service)
			{
				if (Type.isPlainObject(service)
					&& Type.isString(service.name)
					&& service.name.trim() !== '')
				{
					this.serviceIndex[this.prepareServiceId(service.name)] = service;
				}
			}, this);
		}

		this.serviceList = [];
		if (this.data.value)
		{
			let dataValueRaw = Type.isArray(this.data.value) ? this.data.value : this.data.value.split('|');
			dataValueRaw.forEach(function(id)
			{
				let service = this.serviceIndex[this.prepareServiceId(id)];
				if (Type.isPlainObject(service)
					&& Type.isString(service.name)
					&& service.name.trim() !== '')
				{
					this.serviceList.push({
						id: this.prepareServiceId(service.name),
						title: service.name + ' - ' + BookingUtil.getDurationLabel(service.duration)
					});
				}
			}, this);
		}
	}

	setSelectedService(serviceName)
	{
		this.selectedService = serviceName;
	}

	getSelectedService(getMeta)
	{
		return getMeta !== true ? (this.selectedService || null) : (this.serviceIndex[this.prepareServiceId(this.selectedService)] || null);
	}

	prepareServiceId(str)
	{
		return BookingUtil.translit(str);
	}
}
