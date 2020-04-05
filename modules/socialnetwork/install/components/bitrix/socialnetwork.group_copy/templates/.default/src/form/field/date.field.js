import {Event, Tag, Text, Type} from "main.core";
import {BaseField} from "./base.field";
import {DateValidator} from "../validator/date.validator";

export class DateField extends BaseField
{
	constructor(options)
	{
		super(options);

		this.ids.set("container", "social-group-copy-date-field-" + this.fieldName);

		this.classes.set("control", "ui-ctl ui-ctl-after-icon ui-ctl-datetime");
		this.classes.set("icon", "ui-ctl-after ui-ctl-icon-calendar");
		this.classes.set("innerControl", "ui-ctl-element");

		this.validators.push(DateValidator);
	}

	onAppendToParent()
	{
		super.onAppendToParent();

		Event.bind(this.innerControl, "click", this.showCalendar.bind(this));
	}

	setParentNode(node)
	{
		if (Type.isDomNode(node))
		{
			this.parentNode = node;
		}
	}

	/**
	 * @returns {HTMLElement}
	 */
	render()
	{
		this.fieldTitle = Text.encode(this.fieldTitle);

		return Tag.render`
			<div id="${Text.encode(this.ids.get("container"))}" class="${this.classes.get("container")}">
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
		this.fieldName = Text.encode(this.fieldName);
		this.value = Text.encode(this.value);
		const onChange = this.onChange.bind(this);

		return Tag.render`
			<div class="${this.classes.get("control")}">
				<div class="${this.classes.get("icon")}"></div>
				<input id="${this.innerControlId}" type="text" autocomplete="off" value="${this.value}" name="
					${this.fieldName}" class="${this.classes.get("innerControl")}" onchange="${onChange}">
			</div>
		`;
	}

	showCalendar()
	{
		/* eslint-disable */
		BX.calendar({
			node: this.innerControl,
			field: this.innerControl,
			bTime: false,
			bSetFocus: false,
			bHideTime: false
		});
		/* eslint-enable */
	}

	onChange()
	{
		this.setValue(this.innerControl.value);
		this.validate();
	}
}