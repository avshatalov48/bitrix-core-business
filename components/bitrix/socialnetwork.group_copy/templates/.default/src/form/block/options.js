import {Tag} from "main.core";

export class Options
{
	constructor(options)
	{
		options = {...{
			switcher: null
		}, ...options};

		this.switcher = options.switcher;

		this.options = [];

		this.block = null;
		this.list = null;

		this.ids = new Map([
			["blockId", "social-group-copy-options-block"],
			["listId", "social-group-copy-options-list-block"],
		]);
		this.classes = new Map([
			["block", "social-group-copy-options-block"],
			["switcher", "social-group-copy-options-title-block"],
			["optionsList", "social-group-copy-options-list hide fade-out"],
		]);
	}

	onAppendToParent()
	{
		this.block = document.getElementById(this.ids.get("blockId"));
		this.list = document.getElementById(this.ids.get("listId"));

		this.options.forEach((option) => {
			return option.onAppendToParent(this.list);
		});

		this.switcher.onAppendToParent(this.list);
	}

	addOption(option)
	{
		this.options.push(option);
		this.switcher.addOption(option);
	}

	render()
	{
		return Tag.render`
			<div id="${this.ids.get("blockId")}" class="${this.classes.get("block")}">
				${this.switcher.render()}
				<div id="${this.ids.get("listId")}" class="${this.classes.get("optionsList")}"></div>
			</div>
		`;
	}

	getValues()
	{
		let optionsValues = {};
		this.options.forEach((option) => {
			optionsValues = {...optionsValues, ...option.getValues()};
		});
		return optionsValues;
	}
}