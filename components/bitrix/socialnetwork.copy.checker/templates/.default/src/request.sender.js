import {Event, ajax} from "main.core";

export class RequestSender extends Event.EventEmitter
{
	constructor(options)
	{
		super(options);

		options = {...{
			signedParameters: "",
		}, ...options};

		this.signedParameters = options.signedParameters;
	}

	deleteErrorOption(requestData)
	{
		return new Promise((resolve, reject) => {
			ajax.runComponentAction("bitrix:socialnetwork.copy.checker", "deleteErrorOption", {
				mode: "class",
				signedParameters: this.signedParameters,
				data: requestData
			}).then(resolve, reject);
		});
	}
}