import {Tag, Dom, Type, Event} from "main.core";
import {CheckboxField} from "../field/checkbox.field";
import {ChildOption} from "./child.option";

export class Features extends ChildOption
{
	constructor(options)
	{
		super(options);

		this.promoId = "features";
		this.blockId = "features-block";

		this.classes.set("featuresList", "social-group-copy-features-list");
		this.classes.set("featureItem", "social-group-copy-feature-item social-group-copy-feature-item-tree");
	}

	onAppendToParent(optionsContainer)
	{
		super.onAppendToParent(optionsContainer);

		Event.bind(document.getElementById(this.promoId), "click", this.onClick.bind(this));
	}

	renderPromo()
	{
		return Tag.render`
			<span id="${this.promoId}" class="${this.classes.get("promoText")}">${this.fieldTitle}</span>
		`;
	}

	/**
	 * @returns {HTMLElement}
	 */
	getChildRender(data)
	{
		return Tag.render`
			<div class="${this.classes.get("featuresList")}">
				${this.getFeaturesRender(data)}
			</div>
		`;
	}

	selfClean()
	{
		if (Type.isDomNode(this.block))
		{
			Dom.remove(this.block);
		}
	}

	getFeaturesRender(features)
	{
		return Object.values(features).map((feature) => {
			const hasProperty = Object.prototype.hasOwnProperty;
			const childrenFields = Object.values(feature["Children"]).map((featureChild) => {
				const childField = new CheckboxField({
					fieldTitle: featureChild["Title"],
					fieldName: "features[" + feature["Name"] + "][" + featureChild["Name"] + "]",
					validators: [],
					parentNode: this.optionsContainer,
					checked: (hasProperty.call(featureChild, "Checked") ? featureChild["Checked"] : true)
				});
				this.fields.add(childField);
				return childField;
			});

			const childrenRender = childrenFields.map((childrenField) => {
				return Tag.render`${childrenField.render()}`;
			});

			const featureField = new CheckboxField({
				fieldTitle: feature["Title"],
				fieldName: "features[" + feature["Name"] + "][active]",
				validators: [],
				parentNode: this.optionsContainer,
				checked: (hasProperty.call(feature, "Checked") ? feature["Checked"] : true),
				onCustomChange: function(event) {
					this.forEach((childrenField) => {
						childrenField.setChecked(event.currentTarget.checked);
					});
				}.bind(childrenFields)
			});
			this.fields.add(featureField);

			return Tag.render`
				<div class="${this.classes.get("featureItem")}">
					${featureField.render()}
					${childrenRender}
				</div>
			`;
		});
	}
}