import {Tag, Dom, Type, Text, Loc, Event} from "main.core";
import {ChildOption} from "./child.option";
import {SelectField} from "../field/select.field";

export class Rights extends ChildOption
{
	constructor(options)
	{
		super(options);

		this.promoId = "rights";
		this.blockId = "rights-block";
		this.titleId = "rights-option-title-id";

		this.selectField = null;
	}

	onAppendToParent(optionsContainer)
	{
		super.onAppendToParent(optionsContainer);

		Event.bind(document.getElementById(this.promoId), "click", this.onClick.bind(this));
	}

	renderPromo()
	{
		return Tag.render`
			<span id="${this.promoId}" class="${this.classes.get("promoText")}">
				${Loc.getMessage("SGCG_OPTIONS_PERMS_TITLE")}
			</span>
		`;
	}

	getChildRender(data)
	{
		this.selectField = new SelectField({
			fieldTitle: this.fieldTitle,
			fieldName: "initiate_perms",
			value: this.value,
			list: data
		});

		this.fields.add(this.selectField);

		return this.selectField.renderRightColumn();
	}

	changeSelectOptions(data)
	{
		this.selectField.changeOptions(data);
	}
}