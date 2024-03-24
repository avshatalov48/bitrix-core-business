import {FormFieldTunnerAbstract} from "../formfieldtunnerabstract";
import {FormFieldTunnerPopupAbstract} from "../formfieldtunnerpopupabstract";
import {FormFieldTunnerValuePopupAbstract} from "../formfieldtunnervaluepopupabstract";
import {ResourcebookingUserfield} from "../resourcebookinguserfield";
import {BookingUtil} from "calendar.resourcebooking";
import {Loc, Type, Dom, Tag, Text} from "main.core";
import {MenuItem} from "main.popup";
import { Dialog as EntitySelectorDialog } from 'ui.entity-selector';
import {EventEmitter} from 'main.core.events';


export class UserSelectorFieldTunner extends FormFieldTunnerAbstract {
	constructor()
	{
		super();
		this.label = Loc.getMessage('WEBF_RES_USERS');
		this.formLabel = Loc.getMessage('WEBF_RES_USERS_LABEL');
		this.displayed = true;
		this.selectedUsers = [];
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
		this.selectedUsers = Type.isArray(params.config.selected)
			? params.config.selected
			: params.config.selected.split('|');

		this.DOM.valueWrap = params.wrap;

		this.DOM.valueWrap.appendChild(
			Tag.render`
				<div class="calendar-resbook-webform-settings-popup-select-result">
					${this.DOM.usersValueLink = Tag.render`
						<span 
							class="calendar-resbook-webform-settings-popup-select-value"
							onclick="${this.showUserSelectorDialog.bind(this)}"
							>
								${this.getCurrentUsersValueText()}
						</span>
					`}
				</div>
			`
		);
	}

	getCurrentUsersValueText()
	{
		const count = this.selectedUsers.length;
		return count
			? (count + ' ' + ResourcebookingUserfield.getPluralMessage('WEBF_RES_USER', count))
			: Loc.getMessage('WEBF_RES_NO_VALUE');
	}

	showUserSelectorDialog()
	{
		if (!(this.userSelectorDialog instanceof EntitySelectorDialog))
		{
			this.userSelectorDialog = new EntitySelectorDialog({
				targetNode: this.DOM.usersValueLink,
				context: 'RESOURCEBOOKING',
				preselectedItems: this.selectedUsers.map((userId) => {return ['user', userId]}),
				enableSearch: true,
				zIndex: this.zIndex + 10,
				events: {
					'Item:onSelect': this.handleUserSelectorChanges.bind(this),
					'Item:onDeselect': this.handleUserSelectorChanges.bind(this),
				},
				entities: [
					{
						id: 'user',
						options: {
							inviteGuestLink: false,
							emailUsers: false,
							analyticsSource: 'calendar',
						}
					}
				]
			});
		}

		this.userSelectorDialog.show();
	}

	handleUserSelectorChanges()
	{
		this.selectedUsers = [];

		this.userSelectorDialog.getSelectedItems().forEach((item) => {
			if (item.entityId === "user")
			{
				this.selectedUsers.push(item.id);
			}
		});

		this.DOM.usersValueLink.innerHTML = this.getCurrentUsersValueText();

		EventEmitter.emit('ResourceBooking.settingsUserSelector:onChanged');
		setTimeout(() => {EventEmitter.emit('ResourceBooking.webformSettings:onChanged')}, 50);
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
			value: this.selectedUsers
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
		return [
			new MenuItem({
				text: Loc.getMessage('WEBF_RES_SELECT_DEFAULT_TITLE'),
				delimiter: true
			}),
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