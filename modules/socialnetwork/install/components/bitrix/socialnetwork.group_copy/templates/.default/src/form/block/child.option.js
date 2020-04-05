import {Tag, Dom, Type, Text} from "main.core";
import {Switcher} from "./switcher";

export class ChildOption
{
	constructor(options)
	{
		options = {...{
			fieldTitle: "title",
			data: {},
			switcher: null,
			value: ""
		}, ...options};

		this.fieldTitle = Text.encode(options.fieldTitle);
		this.data = options.data;
		this.value = (options.value ? options.value : "");
		this.switcher = options.switcher;

		this.titleControl = null;
		this.titleId = "child-option-title-id";

		this.optionsContainer = null;
		this.blockId = "base";
		this.block = null;

		this.fields = new Set();

		this.classes = new Map([
			["optionItem", "social-group-copy-options-item social-group-copy-form-field-list-block"],
			["leftColumn", "social-group-copy-options-item-column-left"],
			["fieldTitle", "social-group-copy-options-item-name"],
			["rightColumn", "social-group-copy-options-item-column-right"],
			["promoText", "social-group-copy-switcher-promo-text"],
			["itemHighlight", "item-highlight"],
		]);
	}

	onAppendToParent(optionsContainer)
	{
		this.optionsContainer = optionsContainer;

		if (Type.isPlainObject(this.data))
		{
			this.append(this.data)
		}

		this.fields.forEach((field) => {
			return field.onAppendToParent();
		});

		this.block = document.getElementById(this.blockId);
		this.titleControl = document.getElementById(this.titleId);
	}

	append(data)
	{
		this.optionsContainer.appendChild(
			Tag.render`
				<div id="${this.blockId}" class="${this.classes.get("optionItem")}">
					<div class="${this.classes.get("leftColumn")}">
						<div id="${this.titleId}" class="${this.classes.get("fieldTitle")}">${this.fieldTitle}</div>
					</div>
					<div class="${this.classes.get("rightColumn")}">
						${this.getChildRender(data)}
					</div>
				</div>
			`
		);
	}

	/**
	 * @returns {HTMLElement}
	 */
	getChildRender(data)
	{
		return Tag.render``;
	}

	getValues()
	{
		const fieldsValues = {};
		this.fields.forEach((field) => {
			fieldsValues[field.getName()] = field.getValue();
		});

		return fieldsValues;
	}

	onClick(event)
	{
		this.switcher.switchOptions();

		if (this.switcher.isOpened())
		{
			setTimeout(() => {
				Dom.addClass(this.block, this.classes.get("itemHighlight"));
				const position = Dom.getPosition(this.block);
				window.scrollBy({
					top: position.top,
					left: position.left,
					behavior: "smooth"
				});
				// todo hack for slider
				//this.block.scrollIntoView({behavior: "smooth", block: "start"});
				setTimeout(() => {
					Dom.removeClass(this.block, this.classes.get("itemHighlight"));
				}, 3000);
			}, 1000);
		}
	}

	changeTitle(title)
	{
		this.fieldTitle = title;
		this.titleControl.innerHTML = Text.encode(this.fieldTitle);
	}
}