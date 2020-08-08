import {FormFieldTunnerAbstract} from "../formfieldtunnerabstract";
import {FormFieldTunnerPopupAbstract} from "../formfieldtunnerpopupabstract";
import {FormFieldTunnerValuePopupAbstract} from "../formfieldtunnervaluepopupabstract";
import {ResourcebookingUserfield} from "../resourcebookinguserfield";
import {BookingUtil} from "calendar.resourcebooking";
import {Loc, Type, Dom} from "main.core";
import {WebformUserSelectorFieldEditControl} from "./userselectorfieldeditcontrol";

export class UserSelectorFieldTunner extends FormFieldTunnerAbstract {
	constructor()
	{
		super();
		this.label = Loc.getMessage('WEBF_RES_USERS');
		this.formLabel = Loc.getMessage('WEBF_RES_USERS_LABEL');
		this.displayed = true;
	}

	updateConfig(params)
	{
		super.updateConfig(params);
		this.defaultMode = params.defaultMode;
	}

	buildStatePopup(params)
	{
		params.isDisplayed = this.isDisplayed.bind(this);
		params.defaultMode = params.defaultMode || this.defaultMode;
		this.statePopup = new UsersStatePopup(params);
	}

	buildValuePopup(params)
	{
		this.valuePopup = new UsersValuePopup(params);
	}

	displayInForm()
	{
		super.displayInForm();
		this.statePopup.handleControlChanges();
		this.statePopup.setEnabled();
	}

	hideInForm()
	{
		super.hideInForm();
		this.statePopup.handleControlChanges();
		this.statePopup.setDisabled();
	}

	getValue()
	{
		return {
			show: this.isDisplayed() ? 'Y' : 'N',
			label: this.getFormLabel(),
			defaultMode: this.statePopup.getDefaultMode(),
			value: this.valuePopup.getSelectedValues()
		};
	}
}

class UsersStatePopup extends FormFieldTunnerPopupAbstract
{
	constructor(params)
	{
		super(params);
		this.name = 'usersStatePopup';
		this.inputName = 'user-select-mode';
		this.id = 'users-state-' + Math.round(Math.random() * 1000);
		this.defaultMode = params.defaultMode === 'none' ? 'none' : 'auto';
		this.isDisplayed = Type.isFunction(params.isDisplayed) ? params.isDisplayed : function(){return false};
		this.build();
	}

	build()
	{
		super.build();
		this.handleControlChanges();
	}

	getMenuItems()
	{
		let submenuClass = 'main-buttons-submenu-separator main-buttons-submenu-item main-buttons-hidden-label';

		return [
			{
				text: '<span>' + Loc.getMessage('WEBF_RES_SELECT_DEFAULT_TITLE') + '</span>',
				className: submenuClass
			},
			{
				id: 'users-state-list',
				text: Loc.getMessage('WEBF_RES_SELECT_DEFAULT_EMPTY'),
				dataset: {
					type: 'radio',
					value: 'none',
					inputName: this.inputName,
					checked: this.defaultMode === 'none'
				},
				onclick: this.menuItemClick.bind(this)
			},
			{
				id: 'users-state-auto',
				text: Loc.getMessage('WEBF_RES_SELECT_DEFAULT_FREE_USER'),
				dataset: {
					type: 'radio',
					value: 'auto',
					inputName: this.inputName,
					checked: this.defaultMode === 'auto'
				},
				onclick: this.menuItemClick.bind(this)
			}
		];
	}

	menuItemClick(e, menuItem)
	{
		var target = e.target || e.srcElement;
		if (Type.isDomNode(target) && target.nodeName.toLowerCase() === 'input'
			&& menuItem.dataset && menuItem.dataset.inputName === this.inputName
		)
		{
			this.defaultMode = menuItem.dataset.value;
		}
		this.handleControlChanges();
		setTimeout(this.closePopup.bind(this), 50);
	}

	getCurrentModeState()
	{
		return this.isDisplayed()
			?
			(Loc.getMessage('WEBF_RES_SELECT_USER_FROM_LIST_SHORT') +
				(this.defaultMode === 'none'
						? ''
						: (',<br>' + Loc.getMessage('WEBF_RES_AUTO_SELECT_USER_SHORT'))
				))
			:
			Loc.getMessage('WEBF_RES_SELECT_USER_FROM_LIST_AUTO');
	}

	handleControlChanges()
	{
		super.handleControlChanges();
		this.DOM.currentStateLink.innerHTML = this.getCurrentModeState();
		BX.onCustomEvent(this, "ResourceBooking.userSettingsField:onControlChanged", []);
	}

	getDefaultMode()
	{
		return this.defaultMode;
	}
}

class UsersValuePopup extends FormFieldTunnerValuePopupAbstract
{
	constructor(params)
	{
		super(params);
		this.name = 'usersValuePopup';

		this.values = [];
		this.selectedValues = [];
		this.selectedCodes = [];
		let
			selectedItems, selectedIndex = {},
			selectAll = params.config.selected === null;

		selectedItems = Type.isArray(params.config.selected) ? params.config.selected : params.config.selected.split('|');
		if (Type.isArray(selectedItems))
		{
			for(let i = 0; i < selectedItems.length; i++)
			{
				selectedIndex[selectedItems[i]] = true;
				this.selectedValues.push(selectedItems[i]);
				this.selectedCodes.push('U' + selectedItems[i]);
			}
		}

		if (Type.isArray(params.config.users) && selectAll)
		{
			params.config.users.forEach(function(userId)
			{
				if (!selectedIndex[userId])
				{
					this.selectedValues.push(userId);
					this.selectedCodes.push('U' + userId);
				}
			}, this);
		}

		this.config = {};
		this.build();
	}

	getPopupContent()
	{
		super.getPopupContent();
		new Promise((resolve) => {

			if (!this.config.socnetDestination)
			{
				this.showPopupLoader();
				BX.ajax.runAction('calendar.api.resourcebookingajax.getuserselectordata', {
					data: {
						selectedUserList: this.selectedValues
					}
				}).then(function (response)
					{
						this.hidePopupLoader();
						this.config.socnetDestination = response.data;
						resolve();
					}.bind(this),
					function (response) {
						resolve(response);
					});
			}
			else
			{
				resolve();
			}
		}).then(this.buildUserSelector.bind(this));

		return this.DOM.innerWrap;
	}

	showPopupLoader ()
	{
		if (this.DOM.innerWrap)
		{
			this.hidePopupLoader();
			this.DOM.popupLoader = this.DOM.innerWrap.appendChild(Dom.create("div", {props: {className: 'calendar-resourcebook-popup-loader-wrap'}}));
			this.DOM.popupLoader.appendChild(BookingUtil.getLoader(38));
		}
	}

	getPopupWidth()
	{
		return 680;
	}

	buildUserSelector()
	{
		this.DOM.userCurrentvalueWrap = this.DOM.innerWrap.appendChild(Dom.create("div", {
			props: {
				className: 'calendar-resourcebook-content-block-control custom-field-item'
			}
		}));
		this.DOM.userSelectorWrap = this.DOM.innerWrap.appendChild(Dom.create("div", {
			props: {
				className: 'calendar-resourcebook-pseudo-popup-wrap'
			}
		}));

		this.userSelector = new WebformUserSelectorFieldEditControl({
			wrapNode: this.DOM.userCurrentvalueWrap,
			socnetDestination: this.config.socnetDestination,
			itemsSelected: this.selectedCodes,
			addMessage: Loc.getMessage('USER_TYPE_RESOURCE_SELECT_USER'),
			externalWrap: this.DOM.userSelectorWrap
		});

		this.userSelectorId = this.userSelector.getId();

		BX.addCustomEvent('OnResourceBookDestinationAddNewItem', this.triggerUserSelectorUpdate.bind(this));
		BX.addCustomEvent('OnResourceBookDestinationUnselect', this.triggerUserSelectorUpdate.bind(this));
	}

	getSelectedValues()
	{
		return this.selectedValues;
	}

	triggerUserSelectorUpdate(item, selectroId, delayExecution)
	{
		if (selectroId === this.userSelectorId)
		{
			if (this.selectorUpdateTimeout)
			{
				this.selectorUpdateTimeout = clearTimeout(this.selectorUpdateTimeout);
			}

			if (delayExecution !== false)
			{
				this.selectorUpdateTimeout = setTimeout(function(){
					this.triggerUserSelectorUpdate(item, selectroId, false);
				}.bind(this), 300);
				return;
			}

			this.selectedValues = [];
			this.selectedCodes = this.userSelector.getAttendeesCodesList();

			this.selectedCodes.forEach(function(code)
			{
				if (code.substr(0, 1) === 'U')
				{
					this.selectedValues.push(parseInt(code.substr(1)));
				}
			}, this);

			this.handleControlChanges();
		}
	}

	getCurrentValueState()
	{
		const count = this.selectedValues.length;
		return count ? (count + ' ' + ResourcebookingUserfield.getPluralMessage('WEBF_RES_USER', count)) : Loc.getMessage('WEBF_RES_NO_VALUE');
	}

	handleControlChanges()
	{
		BX.onCustomEvent('ResourceBooking.settingsUserSelector:onChanged');
		super.handleControlChanges();
		Dom.adjust(this.DOM.valueLink, {text: this.getCurrentValueState()});
	}
}