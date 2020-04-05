import {Tag, Text} from "main.core";
import {BaseField} from "./base.field";

export class CheckboxField extends BaseField
{
	constructor(options)
	{
		super(options);

		options = {...{
			checked: true,
			disabled: false
		}, ...options};

		this.checked = options.checked;
		this.disabled = options.disabled;

		this.classes.set("control", "ui-ctl ui-ctl-checkbox ui-ctl-wa ui-ctl-xs social-group-copy-checkbox");
		this.classes.set("innerControl", "ui-ctl-element");
		this.classes.set("title", "ui-ctl-label-text");

		// todo tmp delete after main 20.0.200
		this.eventNamespace = "BX.Socialnetwork.CheckboxField:";
		if (typeof this.setEventNamespace === "function")
		{
			this.eventNamespace = "";
			this.setEventNamespace("BX.Socialnetwork.CheckboxField");
		}
	}

	setChecked(checked)
	{
		this.innerControl.checked = checked;
	}

	/**
	 * @returns {HTMLElement}
	 */
	render()
	{
		const onChange = this.onChange.bind(this);

		this.fieldTitle = Text.encode(this.fieldTitle);
		this.fieldName = Text.encode(this.fieldName);
		return Tag.render`
			<label class="${this.classes.get("control")}">
				<input id="${this.innerControlId}" ${this.disabled ? "disabled" : ""} ${this.checked ? "checked" : ""} 
					type="checkbox" name="${this.fieldName}" 
					onchange="${onChange}" class="${this.classes.get("innerControl")}">
				<div id="${this.titleId}" class="${this.classes.get("title")}">${this.fieldTitle}</div>
			</label>
		`;
	}

	onChange()
	{
		this.validate();

		this.emit(this.eventNamespace+this.fieldName+":onChange", {checked: this.innerControl.checked});
	}

	isDisabled()
	{
		return Boolean(this.innerControl.disabled);
	}

	/**
	 * @param {Boolean} disabled
	 */
	changeDisabled(disabled)
	{
		if (disabled)
		{
			this.innerControl.checked = false;
			this.setValue("");
		}
		this.innerControl.disabled = disabled;
	}

	validate()
	{
		this.setValue(this.innerControl.checked ? "Y": "");

		return super.validate();
	}

	getValue()
	{
		return (this.innerControl.checked ? "Y": "");
	}
}