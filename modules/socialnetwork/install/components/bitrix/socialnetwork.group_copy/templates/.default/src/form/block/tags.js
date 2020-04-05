import {Tag, Dom, Type, Text, Loc, Event} from "main.core";
import {ChildOption} from "./child.option";
import {TagField} from "../field/tag.field";

export class Tags extends ChildOption
{
	constructor(options)
	{
		super(options);

		options = {...{
			tagsFieldContainerId: ""
		}, ...options};

		this.promoId = "tags";
		this.blockId = "tags-block";

		this.tagsFieldContainerId = options.tagsFieldContainerId;
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

	getChildRender(data)
	{
		const tagField = new TagField({
			selectorId: "tags-list",
			fieldTitle: this.fieldTitle,
			fieldName: "keywords",
			fieldContainerId: this.tagsFieldContainerId
		});

		this.fields.add(tagField);

		return tagField.renderRightColumn();
	}
}