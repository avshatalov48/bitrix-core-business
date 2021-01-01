import {Type} from "../resourcebooking";
import {ViewControlAbstract} from "../viewcontrolabstract";
import {ViewDropDownSelect} from "../viewdropdownselect";

export class UserSelector extends ViewControlAbstract
{
	constructor(params)
	{
		super(params);

		this.name = 'UserSelector';
		this.data = params.data || {};
		this.userList = [];
		this.userIndex = {};

		this.values = [];
		this.defaultMode = 'auto';
		this.previewMode = params.previewMode === undefined;
		this.autoSelectDefaultValue = params.autoSelectDefaultValue;
		this.changeValueCallback = params.changeValueCallback;

		this.handleSettingsData(this.data, params.userIndex);
	}

	displayControl()
	{
		this.selectedValue = this.getSelectedUser();
		this.dropdownSelect = new ViewDropDownSelect({
			wrap: this.DOM.controlWrap,
			values: this.userList,
			selected: this.selectedValue,
			handleChangesCallback: this.handleChanges.bind(this)
		});
		this.dropdownSelect.build();
	}

	refresh(data, userIndex)
	{
		this.refreshLabel(data);
		this.data = data;
		this.handleSettingsData(this.data, userIndex);
		this.selectedValue = this.getSelectedUser();

		if (this.dropdownSelect)
		{
			this.dropdownSelect.setSettings({
				values: this.userList,
				selected: this.selectedValue
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

	handleSettingsData(data, userIndex)
	{
		if (Type.isPlainObject(userIndex))
		{
			for (let id in userIndex)
			{
				if (userIndex.hasOwnProperty(id))
				{
					this.userIndex[id] = userIndex[id];
				}
			}
		}

		this.defaultMode = this.data.defaultMode === 'none' ? 'none' : 'auto';
		let dataValue = [];
		this.userList = [];
		if (this.data.value)
		{
			let dataValueRaw = Type.isArray(this.data.value) ? this.data.value : this.data.value.split('|');
			dataValueRaw.forEach(function(id)
			{
				id = parseInt(id);
				if (id > 0)
				{
					dataValue.push(id);
					if (this.userIndex[id])
					{
						this.userList.push({
							id: id,
							title: this.userIndex[id].displayName
						});
					}
				}
			}, this);
		}
		this.values = dataValue;
	}

	getSelectedUser()
	{
		let selected = null;
		if (this.dropdownSelect)
		{
			selected = this.dropdownSelect.getSelectedValues();
			selected = (Type.isArray(selected) && selected.length) ? selected[0] : null;
		}

		if (!selected && this.previewMode
			&& this.data.defaultMode === 'auto'
			&& this.userList && this.userList[0])
		{
			selected = this.userList[0].id;
		}

		if (!selected && this.autoSelectDefaultValue)
		{
			selected = this.autoSelectDefaultValue;
		}

		return selected;
	}

	setSelectedUser(userId)
	{
		if (this.dropdownSelect)
		{
			this.dropdownSelect.setSelectedValues([userId]);
		}
		else
		{
			this.autoSelectDefaultValue = parseInt(userId);
		}
	}

	handleChanges(selectedValues)
	{
		if (!this.previewMode && Type.isFunction(this.changeValueCallback))
		{
			this.changeValueCallback(selectedValues[0] || null);
		}
	}
}