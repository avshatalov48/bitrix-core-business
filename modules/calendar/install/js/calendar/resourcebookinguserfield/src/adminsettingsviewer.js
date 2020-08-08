import {Event, Dom, Loc, Type} from "calendar.resourcebooking";
import {ServiceSelector} from "./controls/serviceselector"
import {UserSelectorFieldEditControl} from "./controls/userselectorfieldeditcontrol";
import {ModeSelector} from "./controls/modeselector";
import {ResourceSelectorFieldEditControl} from "./controls/resourceselectorfieldeditcontrol";

export class AdminSettingsViewer
{
	constructor(params = {})
	{
		this.params = Type.isPlainObject(params) ? params : {};
		this.fieldSettings = Type.isPlainObject(this.params.settings) ? this.params.settings : {};

		this.DOM = {
			outerWrap: document.getElementById(this.params.outerWrapId),
			form: document.forms[this.params.formName]
		};
	}

	showLayout()
	{
		if (!this.DOM.outerWrap || !this.DOM.form)
			return;

		Event.bind(this.DOM.form, 'submit', this.onSubmit.bind(this));

		Dom.addClass(this.DOM.outerWrap, 'calendar-resourcebook-content calendar-resourcebook-content-admin-settings');

		this.DOM.innerWrap = this.DOM.outerWrap
			.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-wrap"}}))
			.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-inner"}}));

		let
			resourceList = [],
			selectedResourceList = [];

		this.DOM.innerWrap.appendChild(
			Dom.create(
				"div",
				{
					props: { className: "calendar-resourcebook-content-block" },
					children:
						[
							Dom.create(
								"span",
								{
									props: {className: "calendar-resourcebook-content-block-title-text"},
									text: Loc.getMessage('USER_TYPE_RESOURCE_CHOOSE')
								}
							),
							new ModeSelector({
								useResources: this.fieldSettings.USE_RESOURCES === 'Y',
								useUsers: this.fieldSettings.USE_USERS === 'Y',
								showUsers: function(){
									this.resourceList.hide();
									this.userList.show();
								}.bind(this),
								showResources: function(){
									this.resourceList.show();
									this.userList.hide();
								}.bind(this),
								showResourcesAndUsers: function(){
									this.resourceList.show();
									this.userList.show();
								}.bind(this)
							}).getOuterWrap()
						]
				}
			)
		);

		this.DOM.optionWrap = this.DOM.innerWrap.appendChild(Dom.create(
			"div",
			{
				props: { className: "calendar-resourcebook-content-block" }
			}
		));

		// region Use Resources Option
		this.resourcesWrap = this.DOM.optionWrap.appendChild(Dom.create("div", {props: { className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add"}}));

		this.resourcesTitleWrap = this.resourcesWrap
			.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-title"}}))
			.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-title-text"}, text: Loc.getMessage('USER_TYPE_RESOURCE_RESOURCE_CONTROL_DEFAULT_NAME') + ':'}));

		this.resourcesListWrap = this.resourcesWrap.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-new-entries-wrap calendar-resourcebook-content-block-detail-inner"}}));

		this.resourcesListLowControls = this.resourcesWrap.appendChild(Dom.create("div", {props: {className: "calendar-resource-content-block-add-field"}}));

		if (this.fieldSettings.RESOURCES
			&& Type.isPlainObject(this.fieldSettings.RESOURCES['resource'])
			&& Type.isArray(this.fieldSettings.RESOURCES['resource'].SECTIONS))
		{
			this.fieldSettings.RESOURCES['resource'].SECTIONS.forEach(function(resource)
			{
				resourceList.push({
					id: resource.ID,
					title: resource.NAME,
					type: resource.CAL_TYPE
				});
			});
		}

		if (Type.isArray(this.fieldSettings.SELECTED_RESOURCES))
		{
			this.fieldSettings.SELECTED_RESOURCES.forEach(function(resource)
			{
				selectedResourceList.push({
					id: resource.id,
					type: resource.type
				});
			});
		}

		this.resourceList = new ResourceSelectorFieldEditControl({
			shown: this.fieldSettings.USE_RESOURCES === 'Y',
			editMode: true,
			outerWrap: this.resourcesWrap,
			listWrap: this.resourcesListWrap,
			controlsWrap: this.resourcesListLowControls,
			values: selectedResourceList,
			resourceList: resourceList,
			checkLimitCallback: this.checkResourceCountLimit.bind(this)
		});

		this.userSelectorWrap = this.DOM.optionWrap.appendChild(Dom.create("div", {props: { className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add"}}));

		this.usersTitleWrap = this.userSelectorWrap
			.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-title"}}))
			.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-title-text"}, text: Loc.getMessage('USER_TYPE_RESOURCE_USERS_CONTROL_DEFAULT_NAME') + ':'}));

		this.usersListWrap = this.userSelectorWrap.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-control custom-field-item"}}));

		let itemsSelected = [];
		if (Type.isArray(this.fieldSettings.SELECTED_USERS))
		{
			this.fieldSettings.SELECTED_USERS.forEach(function(user)
			{
				itemsSelected.push('U' + parseInt(user));
			});
		}

		this.userList = new UserSelectorFieldEditControl({
			shown: this.fieldSettings.USE_USERS === 'Y',
			outerWrap: this.userSelectorWrap,
			wrapNode: this.usersListWrap,
			socnetDestination: this.params.socnetDestination,
			itemsSelected: itemsSelected
		});

		// Region Data, Time and services
		this.DOM.optionWrap.appendChild(
			Dom.create("hr", { props: { className: "calendar-resbook-hr"}})
		);

		this.datetimeOptionsWrap = this.DOM.optionWrap.appendChild(Dom.create("div", {props: { className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add" }}));

		this.datetimeOptionsWrap.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-title"}})).appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-title-text"}, text: Loc.getMessage('USER_TYPE_RESOURCE_DATETIME_BLOCK_TITLE') + ':'}));

		this.datetimeOptionsInnerWrap = this.datetimeOptionsWrap.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-options"}}));
		// endregion

		//region Checkbox "Full day"
		this.DOM.fulldayCheckBox = Dom.create(
			"input",
			{
				props: { type: "checkbox", checked: this.fieldSettings.FULL_DAY === 'Y'}
			}
		);

		this.datetimeOptionsInnerWrap.appendChild(
			Dom.create(
				"label",
				{
					props: {className: 'calendar-resourcebook-content-block-option'},
					children:
						[
							this.DOM.fulldayCheckBox,
							Dom.create("span", { text: Loc.getMessage('USER_TYPE_RESOURCE_FULL_DAY') })
						]
				}
			)
		);
		//endregion

		//region Checkbox "Add services"
		this.DOM.useServicedayCheckBox = Dom.create(
			"input",
			{
				props: {
					type: "checkbox",
					checked: this.fieldSettings.USE_SERVICES === 'Y'
				},
				events: {
					'click' : function(){
						if (this.serviceList)
						{
							this.serviceList.show(this.DOM.useServicedayCheckBox.checked);
						}
					}.bind(this)
				}
			}
		);

		this.datetimeOptionsInnerWrap.appendChild(
			Dom.create(
				"label",
				{
					props: {className: 'calendar-resourcebook-content-block-option'},
					children:
						[
							this.DOM.useServicedayCheckBox,
							Dom.create("span", { text: Loc.getMessage('USER_TYPE_RESOURCE_ADD_SERVICES') })
						]
				}
			)
		);

		this.serviceList = new ServiceSelector({
			outerCont: this.datetimeOptionsInnerWrap,
			fieldSettings: this.fieldSettings,
			getFullDayValue: function(){return this.DOM.fulldayCheckBox.checked}.bind(this)
		});

		this.DOM.optionWrap.appendChild(
			Dom.create("hr", { props: { className: "calendar-resbook-hr"}})
		);

		this.DOM.overbookingCheckbox = Dom.create("input", {props: {type: "checkbox", checked: this.fieldSettings.ALLOW_OVERBOOKING === 'Y'}});

		this.DOM.optionWrap.appendChild(
			Dom.create(
				"label",
				{
					props: {className: 'calendar-resourcebook-content-block-option'},
					children:
						[
							this.DOM.overbookingCheckbox,
							Dom.create("span", { text: Loc.getMessage('USER_TYPE_RESOURCE_OVERBOOKING') })
						]
				}
			)
		);
		//endregion
	}

	onSubmit(e)
	{
		if (!this.DOM.inputsWrap)
		{
			this.DOM.inputsWrap = this.DOM.outerWrap.appendChild(Dom.create("DIV"));
		}
		else
		{
			Dom.clean(this.DOM.inputsWrap);
		}

		let inputName = this.params.htmlControl.NAME;
		this.DOM.inputsWrap.appendChild(Dom.create('INPUT', {
			attrs:{
				name: inputName + '[USE_USERS]',
				value: this.userList.isShown() ? 'Y' : 'N',
				type: 'hidden'
			}}));

		this.DOM.inputsWrap.appendChild(Dom.create('INPUT', {
			attrs:{
				name: inputName + '[USE_RESOURCES]',
				value: this.resourceList.isShown() ? 'Y' : 'N',
				type: 'hidden'
			}}));

		this.DOM.inputsWrap.appendChild(Dom.create('INPUT', {
			attrs:{
				name: inputName + '[USE_SERVICES]',
				value: this.DOM.useServicedayCheckBox.checked ? 'Y' : 'N',
				type: 'hidden'
			}}));

		this.DOM.inputsWrap.appendChild(Dom.create('INPUT', {
			attrs:{
				name: inputName + '[FULL_DAY]',
				value: this.DOM.fulldayCheckBox.checked ? 'Y' : 'N',
				type: 'hidden'
			}}));

		this.DOM.inputsWrap.appendChild(Dom.create('INPUT', {
			attrs:{
				name: inputName + '[ALLOW_OVERBOOKING]',
				value: this.DOM.overbookingCheckbox.checked ? 'Y' : 'N',
				type: 'hidden'
			}}));

		// Selected resources
		if (this.resourceList)
		{
			this.prepareFormDataInputs(this.DOM.inputsWrap, this.resourceList.getSelectedValues().concat(this.resourceList.getDeletedValues()), inputName + '[SELECTED_RESOURCES]');
		}

		// // Selected users
		if (this.userList)
		{
			let SELECTED_USERS = [];
			this.userList.getAttendeesCodesList().forEach(function(code)
			{
				if (code.substr(0, 1) === 'U')
				{
					SELECTED_USERS.push(parseInt(code.substr(1)));
				}
			}, this);

			this.prepareFormDataInputs(this.DOM.inputsWrap, SELECTED_USERS, inputName + '[SELECTED_USERS]');
		}

		if (this.DOM.useServicedayCheckBox.checked && this.serviceList)
		{
			this.prepareFormDataInputs(this.DOM.inputsWrap, this.serviceList.getValues(), inputName + '[SERVICE_LIST]');
		}
	}

	prepareFormDataInputs(wrap, data, inputName)
	{
		data.forEach(function(value, ind)
		{
			if (Type.isPlainObject(value))
			{
				let k;
				for (k in value)
				{
					if (value.hasOwnProperty(k))
					{
						wrap.appendChild(Dom.create('INPUT', {
							attrs:{
								name: inputName + '[' + ind + '][' + k + ']',
								value: value[k],
								type: 'hidden'
							}}));
					}
				}
			}
			else
			{
				wrap.appendChild(Dom.create('INPUT', {
					attrs:{
						name: inputName + '[' + ind + ']',
						value: value,
						type: 'hidden'
					}}));
			}
		}, this);
	}

	getTotalResourceCount()
	{
		let result = 0;

		if (this.fieldSettings)
		{
			if (Type.isPlainObject(this.fieldSettings.RESOURCES)
				&& Type.isPlainObject(this.fieldSettings.RESOURCES.resource)
				&& Type.isArray(this.fieldSettings.RESOURCES.resource.SECTIONS)
			)
			{
				result += this.fieldSettings.RESOURCES.resource.SECTIONS.length;
			}

			if (this.resourceList)
			{
				result -= this.resourceList.getDeletedValues().length;

				this.resourceList.getSelectedValues().forEach(function(value)
				{
					if (!value.id && value.title !== '')
					{
						result++;
					}
				}, this);
			}

			if (this.userList)
			{
				result += this.userList.getAttendeesCodesList().length;
			}
		}
		return result;
	}

	checkResourceCountLimitForNewEntries()
	{
		return this.RESOURCE_LIMIT <= 0 || this.getTotalResourceCount() < this.RESOURCE_LIMIT;
	}

	checkResourceCountLimit()
	{
		return this.RESOURCE_LIMIT <= 0 || this.getTotalResourceCount() <= this.RESOURCE_LIMIT;
	}
}