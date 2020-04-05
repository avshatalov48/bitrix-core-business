import {BaseField} from "./base.field";
import {Dom, Tag, Text} from "main.core";

export class CustomField extends BaseField
{
	constructor(options)
	{
		super(options);

		options = {...{
			fieldContainerId: "",
		}, ...options};

		this.fieldContainerId = options.fieldContainerId;

		this.classes.set("control", "ui-ctl ui-ctl-textbox ui-ctl-wa");
		this.classes.set("innerControl", "social-group-copy-ui-ctl-element");
		this.classes.set("customContainer", "social-group-copy-custom-container");
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
		const customContainer = document.getElementById(this.fieldContainerId);
		Dom.removeClass(customContainer, this.classes.get("customContainer"));

		return Tag.render`
			<div class="${this.classes.get("control")}">
				<div id="${this.innerControlId}" class="${this.classes.get("innerControl")}">
					${customContainer}
				</div>
			</div>
		`;
	}
}