import {Tag, Text} from "main.core";
import {BaseField} from "./base.field";

export class SelectField extends BaseField
{
	constructor(options)
	{
		super(options);

		options = {...{
			list: {},
		}, ...options};

		this.list = options.list;

		this.onChangeCallback = this.onChange.bind(this);

		this.classes.set("control", "ui-ctl ui-ctl-after-icon ui-ctl-dropdown");
		this.classes.set("controlAngle", "ui-ctl-after ui-ctl-icon-angle");
		this.classes.set("innerControl", "ui-ctl-element");
	}

	/**
	 * @returns {HTMLElement}
	 */
	render()
	{
		this.fieldTitle = Text.encode(this.fieldTitle);
		this.fieldName = Text.encode(this.fieldName);
		this.value = Text.encode(this.value);

		return Tag.render`
			<div class="${this.classes.get("container")}">
				<div class="${this.classes.get("leftColumn")}">
					<div id="${this.titleId}" class="${this.classes.get("fieldTitle")}">${this.fieldTitle}</div>
				</div>
				<div class="${this.classes.get("rightColumn")}">
					${this.renderRightColumn()}
				</div>
			</div>
		`;
	}

	/**
	 * @returns {HTMLElement}
	 */
	renderRightColumn()
	{
		const options = this.getOptions();
		return Tag.render`
			<div class="${this.classes.get("control")}">
				<div class="${this.classes.get("controlAngle")}"></div>
				<select id="${this.innerControlId}" name="${this.fieldName}" class="${
					this.classes.get("innerControl")}" onchange="${this.onChangeCallback}">
					${options.join("")}
				</select>
			</div>
		`;
	}

	getOptions()
	{
		return Object.entries(this.list).map(([id, value]) => {
			return `<option ${(id === this.value ? "selected" : "")} value="${Text.encode(id)}">${
				Text.encode(value)}</option>`;
		});
	}

	changeOptions(data)
	{
		this.list = data;
		const options = this.getOptions();
		this.innerControl.innerHTML = options.join("");
	}

	onChange()
	{
		this.validate();
	}

	validate()
	{
		super.setValue(this.innerControl.value);

		return super.validate();
	}
}