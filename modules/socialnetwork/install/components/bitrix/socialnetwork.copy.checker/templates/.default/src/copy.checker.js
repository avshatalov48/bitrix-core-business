import {Event} from "main.core";
import {RequestSender} from "./request.sender";

export class CopyChecker
{
	constructor(options)
	{
		options = {...{
			signedParameters: "",
			moduleId: "",
			errorOptionName: "",
			errorAlertContainerId: "",
			errorAlertCloseButtonId: ""
		}, ...options};

		this.signedParameters = options.signedParameters;
		this.moduleId = options.moduleId;
		this.errorOptionName = options.errorOptionName;

		this.errorAlertContainerId = options.errorAlertContainerId;
		this.errorAlertCloseButtonId = options.errorAlertCloseButtonId;

		this.init();
	}

	init()
	{
		this.requestSender = new RequestSender({
			signedParameters: this.signedParameters,
		});

		this.errorAlertContainer = document.getElementById(this.errorAlertContainerId);
		this.errorAlertCloseButton = document.getElementById(this.errorAlertCloseButtonId);
		if (this.errorAlertCloseButton)
		{
			Event.bind(this.errorAlertCloseButton, "click", this.onCloseAlert.bind(this));
		}
	}

	onCloseAlert()
	{
		this.requestSender.deleteErrorOption({
			"moduleId": this.moduleId,
			"errorOptionName": this.errorOptionName
		})
		.then((response) => {
			this.errorAlertContainer.remove();
		}).catch((response) => {});
	}
}