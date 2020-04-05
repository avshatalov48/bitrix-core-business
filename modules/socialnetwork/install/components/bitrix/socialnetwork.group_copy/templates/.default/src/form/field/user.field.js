import {CustomField} from "./custom.field";
import {Type} from "main.core";

export class UserField extends CustomField
{
	constructor(options)
	{
		super(options);

		options = {...{
			selectorId: "",
			multiple: true
		}, ...options};

		this.selectorId = options.selectorId;
		this.multiple = options.multiple;

		this.value = (this.multiple ? {} : "");

		this.init();
	}

	init()
	{
		// eslint-ignore-next-line
		BX.addCustomEvent("BX.Main.User.SelectorController:select", this.onSelect.bind(this));
		// eslint-ignore-next-line
		BX.addCustomEvent("BX.Main.User.SelectorController:unSelect", this.onUnSelect.bind(this));
	}

	onSelect(info)
	{
		if (this.selectorId !== info.selectorId)
		{
			return;
		}
		if (Type.isUndefined(info.item) || Type.isUndefined(info.item.entityId))
		{
			return;
		}

		this.setValue(info.item.entityId);
	}

	onUnSelect(info)
	{
		if (this.selectorId !== info.selectorId)
		{
			return;
		}
		if (Type.isUndefined(info.item) || Type.isUndefined(info.item.entityId))
		{
			return;
		}

		this.deleteValue(info.item.entityId);
		this.validate();
	}

	setValue(value)
	{
		if (this.multiple)
		{
			this.value[value] = value;
		}
		else
		{
			this.value = value;
		}
	}

	deleteValue(value)
	{
		if (this.multiple)
		{
			delete this.value[value];
		}
		else
		{
			this.value = "";
		}
	}
}