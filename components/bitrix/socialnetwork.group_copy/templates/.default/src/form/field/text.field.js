import {Tag, Text} from "main.core";
import {BaseField} from "./base.field";

export class TextField extends BaseField
{
	constructor(options)
	{
		super(options);

		options = {...{
			placeHolder: "",
		}, ...options};

		this.placeHolder = options.placeHolder;

		this.classes.set("control", "ui-ctl ui-ctl-textbox ui-ctl-w100");
		this.classes.set("innerControl", "ui-ctl-element");
	}
	
	/**
	 * @returns {HTMLElement}
	 */
	render()
	{
		return Tag.render`
			<div class="${this.classes.get("container")}">
				<div class="${this.classes.get("leftColumn")}">
					<div class="${this.classes.get("fieldTitle")}">${this.fieldTitle}</div>
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
		const onChange = this.onChange.bind(this);

		this.fieldTitle = Text.encode(this.fieldTitle);
		this.fieldName = Text.encode(this.fieldName);
		this.value = Text.encode(this.value);
		this.placeHolder = Text.encode(this.placeHolder);

		return Tag.render`
			<div class="${this.classes.get("control")}">
				<input id="${this.innerControlId}" type="text" value="${this.value}" 
					name="${this.fieldName}" class="${this.classes.get("innerControl")}" 
					onchange="${onChange}" placeholder="${this.placeHolder}">
			</div>
		`;
	}

	changePlaceHolder(text)
	{
		this.innerControl.placeholder = Text.encode(text);
	}

	onChange()
	{
		this.setValue(this.innerControl.value);
		this.validate();
	}
}