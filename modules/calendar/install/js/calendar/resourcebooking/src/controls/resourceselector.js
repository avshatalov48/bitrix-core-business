import {Type} from "../resourcebooking";
import {ViewControlAbstract} from "../viewcontrolabstract";
import {ViewDropDownSelect} from "../viewdropdownselect";

export class ResourceSelector extends ViewControlAbstract
{
	constructor(params)
	{
		super(params);
		this.name = 'ResourceSelector';
		this.data = params.data;
		this.allResourceList = params.resourceList;
		this.autoSelectDefaultValue = params.autoSelectDefaultValue;
		this.changeValueCallback = params.changeValueCallback;
		this.handleSettingsData(params.data);
	}

	handleSettingsData(data)
	{
		if (!Type.isArray(data.value))
		{
			let dataValue = [];
			if (data.value)
			{
				data.value.split('|').forEach(function(id)
				{
					if (parseInt(id) > 0)
					{
						dataValue.push(parseInt(id))
					}
				});
			}
			this.data.value = dataValue;
		}

		this.resourceList = [];
		if (Type.isArray(this.allResourceList) && Type.isArray(this.data.value))
		{
			this.allResourceList.forEach(function(item)
			{
				if (this.data.value.includes(parseInt(item.id)))
				{
					this.resourceList.push(item);
				}
			}, this);
		}

		this.setSelectedValues(this.getSelectedValues());
	}

	displayControl()
	{
		this.dropdownSelect = new ViewDropDownSelect({
			wrap: this.DOM.controlWrap,
			values: this.resourceList,
			selected: this.selectedValues,
			multiple: this.data.multiple === 'Y',
			handleChangesCallback: this.changeValueCallback
		});
		this.dropdownSelect.build();
	}

	refresh(data)
	{
		this.refreshLabel(data);
		this.data = data;
		this.handleSettingsData(this.data);
		this.setSelectedValues(this.getSelectedValues());

		if (this.dropdownSelect)
		{
			this.dropdownSelect.setSettings({
				values: this.resourceList,
				selected: this.selectedValues,
				multiple: this.data.multiple === 'Y'
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

	getSelectedValues()
	{
		let selected = null;

		if (this.dropdownSelect)
		{
			selected = this.dropdownSelect.getSelectedValues();
		}

		if (!selected && this.autoSelectDefaultValue)
		{
			selected = [this.autoSelectDefaultValue];
		}

		if (!selected && this.data.defaultMode === 'auto')
		{
			if (this.resourceList && this.resourceList[0])
			{
				selected = [this.resourceList[0].id];
			}
		}

		return selected;
	}

	setSelectedValues(selectedValues)
	{
		this.selectedValues = selectedValues;
	}

	setSelectedResource(id)
	{
		if (this.dropdownSelect)
		{
			this.dropdownSelect.setSelectedValues([id]);
		}
		else
		{
			this.autoSelectDefaultValue = parseInt(id);
			this.selectedValues = [id];
		}
	}
}