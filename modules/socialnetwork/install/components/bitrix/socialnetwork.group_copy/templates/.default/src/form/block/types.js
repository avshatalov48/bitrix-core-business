import {Tag, Loc, Event} from "main.core";
import {ChildOption} from "./child.option";
import {CheckboxField} from "../field/checkbox.field";

export class Types extends ChildOption
{
	constructor(options)
	{
		super(options);

		options = {...{
			isProject: false,
			isExtranetGroup: false,
			isExtranetInstalled: false,
			isIntranetInstalled: false,
			isLandingInstalled: false,
		}, ...options};

		this.isProject = options.isProject;
		this.isExtranetGroup = options.isExtranetGroup;
		this.isExtranetInstalled = options.isExtranetInstalled;
		this.isIntranetInstalled = options.isIntranetInstalled;
		this.isLandingInstalled = options.isLandingInstalled;

		this.promoId = "types";
		this.blockId = "types-block";
		this.titleId = "types-option-title-id";

		this.classes.set("typesList", "social-group-copy-types-list");
		this.classes.set("typeItem", "social-group-copy-feature-item");

		this.checkboxEventName = "BX.Socialnetwork.CheckboxField";
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
				${Loc.getMessage("SGCG_OPTIONS_TYPE_TITLE")}
			</span>
		`;
	}

	getChildRender(data)
	{
		return Tag.render`
			<div class="${this.classes.get("typesList")}">
				${this.getTypesRender(data)}
			</div>
		`;
	}

	getTypesRender(data)
	{
		const result = [];

		const visibleField = this.createVisibleField(data);
		result.push(Tag.render`${visibleField.render()}`);

		const openField = this.createOpenedField(data);
		result.push(Tag.render`${openField.render()}`);

		const closeField = this.createCloseField(data);
		result.push(Tag.render`${closeField.render()}`);

		if (this.isExtranetInstalled)
		{
			const extranetField = this.createExtranetField(data);
			result.push(Tag.render`${extranetField.render()}`);
		}

		if (this.isIntranetInstalled)
		{
			const projectField = this.createProjectField(data);
			result.push(Tag.render`${projectField.render()}`);
		}

		if (this.isLandingInstalled)
		{
			const landingField = this.createLandingField(data);
			result.push(Tag.render`${landingField.render()}`);
		}

		return Tag.render`${result}`;
	}

	createVisibleField(data)
	{
		const visibleField = new CheckboxField({
			fieldTitle: (this.isProject ? Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_VISIBLE") :
				Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_VISIBLE")),
			fieldName: "visible",
			validators: [],
			checked: (data["VISIBLE"] === "Y" && !this.isExtranetGroup),
			disabled: this.isExtranetGroup
		});

		this.fields.add(visibleField);

		this.subscribeToField(this.checkboxEventName + ":project:onChange", (baseEvent) => {
			visibleField.changeTitle(baseEvent.data.checked ?
				Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_VISIBLE") : Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_VISIBLE"))
		});

		this.subscribeToField(this.checkboxEventName + ":extranet_group:onChange", (baseEvent) => {
			visibleField.changeDisabled(baseEvent.data.checked);
		});

		return visibleField;
	}

	createOpenedField(data)
	{
		const openField = new CheckboxField({
			fieldTitle: (this.isProject ? Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_OPEN") :
				Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_OPEN")),
			fieldName: "opened",
			validators: [],
			checked: (data["OPENED"] === "Y" && !this.isExtranetGroup),
			disabled: (data["VISIBLE"] !== "Y" || this.isExtranetGroup)
		});

		this.fields.add(openField);

		this.subscribeToField(this.checkboxEventName + ":project:onChange", (baseEvent) => {
			openField.changeTitle(baseEvent.data.checked ?
				Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_OPEN") : Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_OPEN"))
		});

		this.subscribeToField(this.checkboxEventName + ":visible:onChange", (baseEvent) => {
			openField.changeDisabled(!baseEvent.data.checked);
		});

		this.subscribeToField(this.checkboxEventName + ":extranet_group:onChange", (baseEvent) => {
			openField.changeDisabled(baseEvent.data.checked);
		});

		return openField;
	}

	createCloseField(data)
	{
		const closeField = new CheckboxField({
			fieldTitle: (this.isProject ? Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_CLOSED") :
				Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_CLOSED")),
			fieldName: "closed",
			validators: [],
			checked: (data["CLOSED"] === "Y"),
			disabled: this.isExtranetGroup
		});

		this.fields.add(closeField);

		this.subscribeToField(this.checkboxEventName + ":project:onChange", (baseEvent) => {
			closeField.changeTitle(baseEvent.data.checked ?
				Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_CLOSED") : Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_CLOSED"));
		});

		this.subscribeToField(this.checkboxEventName + ":extranet_group:onChange", (baseEvent) => {
			closeField.changeDisabled(baseEvent.data.checked);
		});

		return closeField;
	}

	createExtranetField(data)
	{
		const extranetField = new CheckboxField({
			fieldTitle: (this.isProject ? Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_EXTRANET") :
				Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_EXTRANET")),
			fieldName: "extranet_group",
			validators: [],
			checked: this.isExtranetGroup
		});

		this.fields.add(extranetField);

		this.subscribeToField(this.checkboxEventName + ":project:onChange", (baseEvent) => {
			extranetField.changeTitle(baseEvent.data.checked ?
				Loc.getMessage("SGCG_OPTIONS_PROJECT_TYPE_EXTRANET") : Loc.getMessage("SGCG_OPTIONS_GROUP_TYPE_EXTRANET"))
		});

		return extranetField;
	}

	createProjectField(data)
	{
		const projectField = new CheckboxField({
			fieldTitle: Loc.getMessage("SGCG_OPTIONS_TYPE_PROJECT"),
			fieldName: "project",
			validators: [],
			checked: (data["PROJECT"] === "Y")
		});
		this.fields.add(projectField);
		return projectField;
	}

	createLandingField(data)
	{
		const landingField = new CheckboxField({
			fieldTitle: Loc.getMessage("SGCG_OPTIONS_TYPE_LANDING_MSGVER_2"),
			fieldName: "landing",
			validators: [],
			checked: (data["LANDING"] === "Y")
		});
		this.fields.add(landingField);
		return landingField;
	}

	subscribeToField(eventName, callback)
	{
		Event.EventEmitter.subscribe(eventName, callback);
	}
}