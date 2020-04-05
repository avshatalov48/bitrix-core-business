import {Type} from "main.core";
import {CustomField} from "./custom.field";

export class GroupField extends CustomField
{
	constructor(options)
	{
		super(options);

		options = {...{
			requestSender: null,
			features: null
		}, ...options};

		this.requestSender = options.requestSender;
		this.features = options.features;

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
		if (Type.isUndefined(info.item) || Type.isUndefined(info.item.entityId))
		{
			return;
		}

		const groupId = info.item.entityId;

		super.setValue("SG"+groupId);

		if (this.validate())
		{
			this.requestSender
				.selectGroup(groupId)
				.then((response) => {
					this.features.selfClean();
					this.features.append(response.data.features);
				});
		}
	}

	onUnSelect()
	{
		super.setValue("");

		this.features.selfClean();

		this.validate();
	}
}