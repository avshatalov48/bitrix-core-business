import {Dom, Event, Text, Type} from "main.core";
import {ErrorAlert} from "../../error.alert";

export class BaseField extends Event.EventEmitter
{
	constructor(options)
	{
		super(options);

		options = {...{
			fieldTitle: "title",
			fieldName: "name",
			validators: [],
			onCustomChange: null,
			parentNode: null,
			value: "",
			focus: false,
			visible: true
		}, ...options};

		this.fieldTitle = options.fieldTitle;
		this.fieldName = options.fieldName;
		this.validators = options.validators;
		this.onCustomChange = options.onCustomChange;
		this.parentNode = options.parentNode;

		this.titleControl = null;
		this.titleId = Text.encode(this.fieldName) + "-" + "title";
		this.innerControl = null;
		this.innerControlId = this.fieldName + "-" + Text.getRandom();
		this.value = (options.value ? options.value : "");
		this.focus = options.focus;
		this.visible = options.visible;

		this.container = null;

		this.ids = new Map([
			["container", "social-group-copy-field-" + this.fieldName],
		]);

		this.errorContainer = new Set();

		this.classes = new Map([
			["container", "social-group-copy-fields-item"],
			["leftColumn", "social-group-copy-fields-item-column-left"],
			["rightColumn", "social-group-copy-fields-item-column-right"],
			["fieldTitle", "social-group-copy-fields-item-name"],
			["errorMark", "ui-ctl-danger"],
			["hide", "hide"]
		]);

		if (Type.isDomNode(this.parentNode))
		{
			this.observerParent = new MutationObserver(this.onAppendToParent.bind(this));
			this.observerParent.observe(this.parentNode, {
				childList: true
			});
		}
	}

	setClass(id, name)
	{
		this.classes.set(id, name);
	}

	addClass(id, name)
	{
		if (this.classes.has(id))
		{
			this.classes.set(id, this.classes.get(id) + " " + name);
		}
	}

	getType()
	{
		return this.constructor.name();
	}

	getControl()
	{
		return this.innerControl;
	}

	onAppendToParent(mutations)
	{
		this.titleControl = document.getElementById(this.titleId);
		this.innerControl = document.getElementById(this.innerControlId);
		this.container = document.getElementById(this.ids.get("container"));

		if (Type.isFunction(this.onCustomChange))
		{
			Event.bind(this.innerControl, "change", this.onCustomChange);
		}

		if (this.focus)
		{
			this.innerControl.focus();
		}
	}

	validate()
	{
		this.errorContainer.clear();

		if (!this.visible)
		{
			return true;
		}

		const parentNode = (this.parentNode ? this.parentNode : this.innerControl.parentNode);

		this.validators.forEach((validatorClass) => {
			const validator = new validatorClass();

			const errorId = "social-group-" + this.fieldName + "-" + validatorClass.getType();
			const errorDom = document.getElementById(errorId);
			if (Type.isDomNode(errorDom))
			{
				errorDom.remove();
			}

			if (validator.validate(this.value))
			{
				Dom.removeClass(parentNode, this.classes.get("errorMark"));
			}
			else
			{
				Dom.addClass(parentNode, this.classes.get("errorMark"));

				const error = new ErrorAlert({
					id: errorId,
					message: validator.getErrorMessage()
				});
				parentNode.before(error.render());

				this.errorContainer.add(error);
			}
		});

		return this.errorContainer.size === 0;
	}

	getErrorContainer()
	{
		return this.errorContainer;
	}

	setValue(value)
	{
		this.value = value;
	}

	getValue()
	{
		if (!this.visible)
		{
			return "";
		}
		return this.value;
	}

	getName()
	{
		return this.fieldName;
	}

	changeTitle(title)
	{
		this.fieldTitle = title;
		this.titleControl.innerHTML = Text.encode(this.fieldTitle);
	}

	toggleVisible(bool)
	{
		this.visible = bool;

		if (bool)
		{
			Dom.removeClass(this.container, this.classes.get("hide"));
		}
		else
		{
			Dom.addClass(this.container, this.classes.get("hide"));
		}
	}
}