import {Type} from "main.core";

export class LockStatus
{
	constructor(options)
	{
		options = {...{
			widgetContainerId: ""
		}, ...options};

		this.widgetContainer = document.getElementById(options.widgetContainerId);

		if (Type.isDomNode(this.widgetContainer))
		{
			// eslint-ignore-next-line
			BX.UI.Hint.init(this.widgetContainer);
		}
	}
}