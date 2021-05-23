import {CustomField} from "./custom.field";
import {Dom, Event, Type} from "main.core";

export class ImageField extends CustomField
{
	constructor(options)
	{
		super(options);

		this.classes.set("innerControl", "social-group-copy-link-upload social-group-copy-link-upload-set");
		this.classes.set("uploadSet", "social-group-copy-link-upload-set");

		this.init();
	}

	init()
	{
		// eslint-ignore-next-line
		const uploaderInstance = BX.UploaderManager.getById(this.fieldName);
		if (uploaderInstance)
		{
			// eslint-ignore-next-line
			BX.addCustomEvent(uploaderInstance, "onQueueIsChanged", this.onQueueIsChanged.bind(this));
		}
	}

	onAppendToParent()
	{
		super.onAppendToParent();

		const currentValue = this.getCurrentValue();
		super.setValue(currentValue);
		if (!currentValue)
		{
			Dom.removeClass(this.innerControl, this.classes.get("uploadSet"));
		}
	}

	onQueueIsChanged(uploaderInstance, action, fileId, file)
	{
		// eslint-ignore-next-line
		BX.addCustomEvent(file, "onUploadDone", this.onUploadDone.bind(this));

		switch (action)
		{
			case "add":
				Dom.addClass(this.innerControl, this.classes.get("uploadSet"));
				break;
			case "delete":
				Dom.removeClass(this.innerControl, this.classes.get("uploadSet"));
				super.setValue("");
				break;
		}
	}

	onUploadDone(status, file, agent, pIndex)
	{
		super.setValue(this.getCurrentValue());
	}

	getCurrentValue()
	{
		const fieldInput = document.getElementsByName(this.fieldName);
		return fieldInput.length > 0 ? fieldInput[0].value : "";
	}
}