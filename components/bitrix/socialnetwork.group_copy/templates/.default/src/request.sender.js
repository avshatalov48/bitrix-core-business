import {ajax} from "main.core";

export class RequestSender
{
	constructor(options)
	{
		options = {...{
			signedParameters: "",
		}, ...options};

		this.signedParameters = options.signedParameters;
		this.isProject = false;
	}

	selectGroup(groupId)
	{
		return new Promise((resolve, reject) => {
			ajax.runComponentAction("bitrix:socialnetwork.group_copy", "getGroupData", {
				mode: "class",
				signedParameters: this.signedParameters,
				data: {
					groupId: parseInt(groupId)
				}
			}).then(resolve, reject);
		});
	}

	copyGroup(requestData)
	{
		return new Promise((resolve, reject) => {
			ajax.runComponentAction("bitrix:socialnetwork.group_copy", "copyGroup", {
				mode: "class",
				signedParameters: this.signedParameters,
				data: requestData,
				analyticsLabel: {
					project: (this.isProject ? "Y" : "N"),
				}
			}).then(resolve, reject);
		});
	}

	setProjectMarker(bool)
	{
		this.isProject = Boolean(bool);
	}
}