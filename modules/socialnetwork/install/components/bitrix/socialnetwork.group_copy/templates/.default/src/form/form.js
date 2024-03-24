import {Event, Tag, Text, Type, Loc} from "main.core";
import {ErrorAlert} from "../error.alert";
import {Button} from "ui.buttons";
import {TextField} from "./field/text.field";
import {RequireValidator} from "./validator/require.validator";
import {TextAreaField} from "./field/textarea.field";

export class Form
{
	constructor(options)
	{
		options = {...{
			requestSender: null,
			groupData: {},
			copyButtonId: "",
			cancelButtonId: ""
		}, ...options};

		this.requestSender = options.requestSender;

		this.groupData = options.groupData;
		this.isProject = (this.groupData.PROJECT === "Y");

		this.copyButtonId = options.copyButtonId;
		this.cancelButtonId = options.cancelButtonId;

		this.fields = [];
		this.blocks = [];

		this.errorContainer = new Map();
		this.errorDomContainer = null;

		this.ids = new Map([
			["errorDomContainer", "social-group-copy-error"],
		]);
		this.classes = new Map([
			["form", "social-group-copy-form"],
			["base", "social-group-copy-base"],
			["nameContainer", "social-group-copy-name"],
			["name", "social-group-copy-name-title"],
			["descriptionContainer", "social-group-copy-description"],
			["descriptionArea", "social-group-copy-description-area"],
			["description", "social-group-copy-description"],
			["descriptionSeparator", "social-group-copy-separator-line"],
			["errorDomContainer", "social-group-copy-error"],
			["fields", "social-group-copy-fields"],
			["blocks", "social-group-copy-blocks"]
		]);

		this.checkboxEventName = "BX.Socialnetwork.CheckboxField";
	}

	renderTo(formContainer)
	{
		if (!Type.isDomNode(formContainer))
		{
			throw new Error("Form: HTMLElement for form not found");
		}

		formContainer.appendChild(this.render());

		this.errorDomContainer = document.getElementById(this.ids.get("errorDomContainer"));

		this.emitFormAppend(formContainer);

		this.bindButtons();
	}

	onCopy()
	{
		if (this.validate())
		{
			const uiCopyButton = new Button({
				buttonContainer: this.copyButton
			});

			if (uiCopyButton.isWaiting())
			{
				return;
			}

			uiCopyButton.setWaiting(true);

			this.requestSender.copyGroup(this.getRequestData())
				.then((response) => {
					this.handleResponse(response);
				}).catch((response) => {
				uiCopyButton.setWaiting(false);
				this.handleResponse(response);
			});
		}
	}

	onClose()
	{
		this.constructor.closeSlider();
	}

	static closeSlider()
	{
		// eslint-ignore-next-line
		window.top.BX.onCustomEvent("BX.Bitrix24.PageSlider:close", [false]);
	}

	bindButtons()
	{
		this.copyButton = document.getElementById(this.copyButtonId);
		this.cancelButton = document.getElementById(this.cancelButtonId);
		if (!Type.isDomNode(this.copyButton) || !Type.isDomNode(this.cancelButton))
		{
			throw new Error("Form: buttons are not found");
		}

		Event.bind(this.copyButton, "click", this.onCopy.bind(this));
		Event.bind(this.cancelButton, "click", this.onClose.bind(this));
	}

	emitFormAppend(formContainer)
	{
		this.fields.forEach((field) => {
			field.onAppendToParent(formContainer);
		});
		this.blocks.forEach((block) => {
			block.onAppendToParent(formContainer);
		});
	}

	addField(field)
	{
		this.fields.push(field);
	}

	getFields()
	{
		return this.fields;
	}

	addBlock(block)
	{
		this.blocks.push(block);
	}

	/**
	 * @returns {HTMLElement}
	 */
	render()
	{
		const nameField = new TextField({
			fieldName: "name",
			validators: [RequireValidator],
			placeHolder: (this.isProject ? Loc.getMessage("SGCG_PROJECT_NAME_FIELD") :
				Loc.getMessage("SGCG_GROUP_NAME_FIELD")),
			focus: true
		});
		nameField.setClass("control", this.classes.get("name"));
		Event.EventEmitter.subscribe(this.checkboxEventName + ":project:onChange", (baseEvent) => {
			nameField.changePlaceHolder(baseEvent.data.checked ?
				Loc.getMessage("SGCG_PROJECT_NAME_FIELD") : Loc.getMessage("SGCG_GROUP_NAME_FIELD"))
		});

		const descriptionField = new TextAreaField({
			fieldName: "description",
			value: this.groupData.DESCRIPTION,
			placeHolder: (this.isProject ? Loc.getMessage("SGCG_PROJECT_DESCRIPTION_FIELD") :
				Loc.getMessage("SGCG_GROUP_DESCRIPTION_FIELD"))
		});
		descriptionField.setClass("control", this.classes.get("descriptionArea"));
		descriptionField.setClass("innerControl", this.classes.get("description"));

		const fields = this.fields.map((field) => {
			return field.render();
		});
		const blocks = this.blocks.map((block) => {
			return block.render();
		});

		this.addField(nameField);
		this.addField(descriptionField);

		return Tag.render`
			<form class="${this.classes.get("form")}" novalidate>
				<div id="${this.ids.get("errorDomContainer")}" class="${this.classes.get("errorDomContainer")}"></div>
				<div class="${this.classes.get("base")}">
					<div class="${this.classes.get("nameContainer")}">
						${nameField.renderRightColumn()}
					</div>
					<div class="${this.classes.get("descriptionContainer")}">
						${descriptionField.renderRightColumn()}
					</div>
				</div>
				<div class="${this.classes.get("fields")}">
					${fields}
				</div>
				<div class="${this.classes.get("blocks")}">
					${blocks}
				</div>
			</form>
		`;
	}

	validate()
	{
		this.fields.forEach((field) => {
			if (field.validate())
			{
				this.errorContainer.delete(field);
			}
			else
			{
				this.errorContainer.set(field, field.getErrorContainer());
			}
		});

		return this.errorContainer.size === 0;
	}

	getRequestData()
	{
		const fieldsValues = {
			id: this.groupData.ID,
		};

		this.fields.forEach((field) => {
			fieldsValues[field.getName()] = field.getValue();
		});

		let blocksValues = {};
		this.blocks.forEach((block) => {
			blocksValues = { ...blocksValues, ...block.getValues() };
		});

		const formData = new FormData();

		for (const [name, value] of Object.entries(Object.assign(fieldsValues, blocksValues)))
		{
			if (value instanceof Blob)
			{
				formData.append(name, value, value.name);
			}
			else
			{
				formData.append(name, Type.isObjectLike(value) ? JSON.stringify(value) : value);
			}
		}

		return formData;
	}

	handleResponse(response)
	{
		if (response.errors.length)
		{
			this.displayResponseError(new ErrorAlert({
				message: response.errors.shift().message
			}));
		}
		else
		{
			const urlToCopiedGroup = response.data;
			if (urlToCopiedGroup.length)
			{
				top.window.location.href = urlToCopiedGroup;
			}
			else
			{
				this.displayResponseError(new ErrorAlert({
					message: "Unknown error"
				}));
			}
		}
	}

	displayResponseError(errorAlert)
	{
		while (this.errorDomContainer.hasChildNodes())
		{
			this.errorDomContainer.removeChild(this.errorDomContainer.firstChild);
		}

		this.errorDomContainer.appendChild(errorAlert.render());
	}
}