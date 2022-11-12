import {EventEmitter} from "main.core.events";
import {Dom, Event, Text, Tag, Loc} from 'main.core';
import Base from "./base";

import {PopupWindowManager} from "main.popup";
import ColumnItemOptions from "../columnitem";

export default class Role extends Base
{
	static TYPE = 'role';

	constructor(options: ColumnItemOptions)
	{
		super(options);

		this.column = options.column;
	}

	bindEvents(): void
	{
		Event.bind(window, 'click', (event: Event) => {
			if (
				event.target === this.getRole()
				|| event.target.closest('.ui-access-rights-role')
			)
			{
				return;
			}

			this.updateRole();
			this.offRoleEditMode();
		});

		EventEmitter.subscribe(this.grid, 'onBeforeSave', () => {
			this.updateRole();
			this.offRoleEditMode();
		});
	}

	getRole(): HTMLElement
	{
		if (this.role)
		{
			return this.role;
		}

		EventEmitter.subscribe('BX.UI.AccessRights:preservation', this.updateRole.bind(this));
		EventEmitter.subscribe('BX.UI.AccessRights:preservation', this.offRoleEditMode.bind(this));

		this.roleInput = Tag.render`
				<input
					type='text'
					class='ui-access-rights-role-input'
					value='${Text.encode(this.text)}'
					placeholder='${Loc.getMessage('JS_UI_ACCESSRIGHTS_ROLE_NAME')}'
				/>
			`;

		Event.bind(this.roleInput, 'keydown', (event: Event) => {
			if (event.keyCode === 13)
			{
				this.updateRole();
				this.offRoleEditMode();
			}
		});

		Event.bind(this.roleInput, 'input', () => {
			this.grid.getButtonPanel().show();
		});

		this.roleValue = Tag.render`<div class='ui-access-rights-role-value'>${Text.encode(this.text)}</div>`;

		const editControl = Tag.render`<div class='ui-access-rights-role-edit'></div>`;
		Event.bind(editControl, 'click', this.onRoleEditMode.bind(this));

		const removeControl = Tag.render`<div class='ui-access-rights-role-remove'></div>`;
		Event.bind(removeControl, 'click', this.showPopupConfirm.bind(this));

		const roleControlWrapper = Tag.render`
				<div class='ui-access-rights-role-controls'>
					${editControl}
					${removeControl}
				</div>
			`;

		this.role = Tag.render`
				<div class='ui-access-rights-role'>
					${this.roleInput}
					${this.roleValue}
					${roleControlWrapper}
				</div>
			`;

		return this.role;
	}

	render(): HTMLElement
	{
		return this.getRole();
	}

	onRoleEditMode()
	{
		Dom.addClass(this.getRole(), 'ui-access-rights-role-edit-mode');
		this.roleInput.focus();
	}

	showPopupConfirm()
	{
		if (!this.popupConfirm)
		{
			/**@ToDO check role*/
			this.popupConfirm = PopupWindowManager.create(
				null,
				this.getRole(),
				{
					width: 250,
					overlay: true,
					contentPadding: 10,
					content: Loc.getMessage('JS_UI_ACCESSRIGHTS_POPUP_REMOVE_THIS_ROLE'),
					animation: 'fading-slide'
				}
			);

			this.popupConfirm.setButtons([
				new BX.UI.Button({
					text: Loc.getMessage('JS_UI_ACCESSRIGHTS_POPUP_REMOVE'),
					className: 'ui-btn ui-btn-sm ui-btn-primary',
					events: {
						click: () => {
							this.popupConfirm.close();
							EventEmitter.emit('BX.UI.AccessRights.ColumnItem:removeRole', this);
						}
					}
				}),
				new BX.UI.Button({
					text: Loc.getMessage('JS_UI_ACCESSRIGHTS_POPUP_CANCEL'),
					className: 'ui-btn ui-btn-sm ui-btn-link',
					events: {
						click: () => {
							this.popupConfirm.close();
						}
					}
				})
			]);
		}

		this.popupConfirm.show();
	}

	updateRole(): void
	{
		if (this.roleValue.innerHTML === this.roleInput.value || this.roleInput.value === '')
		{
			return;
		}

		this.text = this.roleInput.value;
		this.userGroup = this.column.getUserGroup();

		this.roleValue.innerText = this.roleInput.value;
		EventEmitter.emit('BX.UI.AccessRights.ColumnItem:updateRole', this);
	}

	offRoleEditMode(): void
	{
		Dom.removeClass(this.getRole(), 'ui-access-rights-role-edit-mode')
	}
}
