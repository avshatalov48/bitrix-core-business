import {CustomField} from "./custom.field";
import {Tag} from "main.core";
import {DateField} from "./date.field";
import {RequireValidator} from "../validator/require.validator";

export class DateRangeField extends CustomField
{
	constructor(options)
	{
		super(options);

		this.startPoint = null;
		this.endPoint = null;

		this.ids.set("container", "social-group-copy-date-range-field-" + this.fieldName);
		this.ids.set("baseContainer", "social-group-copy-date-range-base-" + this.fieldName);

		this.classes.set("baseContainer", "social-group-copy-field-container " +
			"social-group-copy-field-container-datetime social-group-copy-field-datetime");
		this.classes.set("delimiter", "social-group-copy-field-block social-group-copy-field-block-between");
	}

	onAppendToParent()
	{
		super.onAppendToParent();

		this.baseContainer = document.getElementById(this.ids.get("baseContainer"));

		this.startPoint.setParentNode(this.baseContainer);
		this.startPoint.onAppendToParent();
		this.endPoint.onAppendToParent();

		this.toggleVisible(this.visible);
	}

	/**
	 * @returns {HTMLElement}
	 */
	renderRightColumn()
	{
		this.startPoint = new DateField({
			fieldName: "range_start_point",
			validators: [RequireValidator]
		});
		this.endPoint = new DateField({
			fieldName: "range_end_point"
		});

		return Tag.render`
			<div>
				<div id="${this.ids.get("baseContainer")}" class="${this.classes.get("baseContainer")}">
					${this.startPoint.renderRightColumn()}
					<div class="${this.classes.get("delimiter")}"></div>
					${this.endPoint.renderRightColumn()}
				</div>
			</div>
		`;
	}

	validate()
	{
		this.errorContainer.clear();

		if (!this.visible)
		{
			return true;
		}
		return this.startPoint && this.startPoint.validate();
	}

	getValue()
	{
		return {
			start_point: this.startPoint.getValue(),
			end_point: this.endPoint.getValue()
		};
	}
}