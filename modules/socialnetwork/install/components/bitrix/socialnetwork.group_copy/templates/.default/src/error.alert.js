import {Tag, Text} from "main.core";

export class ErrorAlert
{
	constructor(options)
	{
		options = {...{
			id: Text.getRandom(),
			message: "Error!"
		}, ...options};

		this.id = options.id;
		this.message = options.message;

		this.classes = new Map([
			["container", "ui-alert ui-alert-danger"],
			["message", "ui-alert-message"],
		]);
	}

	render()
	{
		this.id = Text.encode(this.id);
		this.message = Text.encode(this.message);
		return Tag.render`
			<div id="${this.id}" class="${this.classes.get("container")}">
				<span class="${this.classes.get("message")}">${this.message}</span>
			</div>
		`;
	}
}