import { ajax, Event, Loc, Tag, Text, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Selector, UserSelector } from 'ui.form-elements.view';

import { Params } from './access-rights';

import 'ui.forms';
import 'ui.hint';

type FormData = {
	id: number,
	ownerId: number,
	moderators: Array<number>,
	permissions: { [string]: { [string]: string } },
	options: { [string]: { [string]: string } },
	permissionsLabels: { [string]: string },
	rightsPermissionsLabels: { [string]: string },
	optionsLabels: { [string]: string },
};

export type Data = {
	id: number,
	ownerId: number,
	moderators: Array<number>,
	permissions: { [string]: { [string]: string } },
}

export class Form extends EventEmitter
{
	#params: Params;

	#layout: {
		ownerField: UserSelector,
		moderatorsField: UserSelector,
		showHistory: Selector,
		whoCanInvite: Selector,
		manageMessages: Selector,
		tasksViewUsersField: Selector,
		tasksSortTasksField: Selector,
		tasksCreateTasksField: Selector,
		tasksEditTasksField: Selector,
		tasksDeleteTasksField: Selector,
	};

	#hintManager = null;
	#sidePanel: BX.SidePanel.Manager;

	sidePanelId: string;

	constructor(params: Params)
	{
		super(params);

		this.#params = params;

		this.setEventNamespace('BX.Socialnetwork.Collab.Form');

		this.#layout = {};

		this.sidePanelId = 'sn-collab-access-rights';
	}

	open(): void {}

	prepareFormData(data: Object): FormData
	{
		return {
			id: Number(data.id),
			ownerId: Number(data.ownerId),
			moderators: Type.isArray(data.moderatorMembers) ? data.moderatorMembers : [],
			permissions: Type.isPlainObject(data.permissions) ? data.permissions : {},
			options: Type.isPlainObject(data.options) ? data.options : {},
			permissionsLabels: Type.isPlainObject(data.permissionsLabels) ? data.permissionsLabels : {},
			rightsPermissionsLabels: Type.isPlainObject(data.rightsPermissionsLabels) ? data.rightsPermissionsLabels : {},
			optionsLabels: Type.isPlainObject(data.optionsLabels) ? data.optionsLabels : {},
		};
	}

	render(formData: FormData): HTMLElement
	{
		const uiStyles = 'ui-sidepanel-layout-content ui-sidepanel-layout-content-margin';

		this.#prepareFields(formData);

		const { content, form } = Tag.render`
			<div ref="content" class="sn-collab__access-right-side-panel ui-sidepanel-layout">
				<div class="ui-sidepanel-layout-header">
					<div class="ui-sidepanel-layout-title">
						${Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS')}
					</div>
				</div>
				<form ref="form" class="${uiStyles} sn-collab__access-right-form">
					<div class="sn-collab__access-right-form-box">
						<div class="sn-collab__access-right-form-box-label">
							${Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_MANAGEMENT_LABEL')}
						</div>
						${this.#layout.ownerField.render()}
						${this.#layout.moderatorsField.render()}
						${this.#layout.showHistory.render()}
						${this.#layout.whoCanInvite.render()}
						${this.#layout.manageMessages.render()}
					</div>
					<div class="sn-collab__access-right-form-box --selectors">
						<div class="sn-collab__access-right-form-box-label">
							${Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_TASKS_LABEL')}
						</div>
						${this.#layout.tasksViewUsersField.render()}
						${this.#layout.tasksSortTasksField.render()}
						${this.#layout.tasksCreateTasksField.render()}
						${this.#layout.tasksEditTasksField.render()}
						${this.#layout.tasksDeleteTasksField.render()}
					</div>
				</form>
				<div class="ui-sidepanel-layout-footer-anchor"></div>
				<div class="ui-sidepanel-layout-footer">
				<div class="ui-sidepanel-layout-buttons">
					${this.#renderButtons(formData)}
				</div>
			</div>
		`;

		Event.bind(form, 'change', () => {
			this.#checkFormData();
		});

		return content;
	}

	#renderButtons(formData: FormData): HTMLElement
	{
		const { buttons, save, cancel } = Tag.render`
			<div ref="buttons">
				<button ref="save" class="ui-btn ui-btn-success">
					${Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_BUTTON_SAVE')}
				</button>
				<button ref="cancel" class="ui-btn ui-btn-link">
					${Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_BUTTON_CANCEL')}
				</button>
			</div>
		`;

		Event.bind(save, 'click', () => {
			if (this.#checkFormData())
			{
				if (this.#params.enableServerSave === true)
				{
					ajax.runAction(
						'socialnetwork.collab.AccessRights.saveRights',
						{
							data: this.#collectFormData(formData),
						},
					)
						.then(() => {
							this.#sidePanel.close();
						})
						.catch((error) => {
							console.error(error);
						})
					;
				}
				else
				{
					this.emit('save', this.#collectFormData(formData));

					this.#sidePanel.close();
				}
			}
		});

		Event.bind(cancel, 'click', () => {
			this.emit('cancel');

			this.#sidePanel.close();
		});

		return buttons;
	}

	onLoad(event)
	{
		this.#hintManager = BX.UI.Hint.createInstance({
			id: this.sidePanelId,
			popupParameters: {
				targetContainer: window.top.document.body,
			},
		});
		this.#hintManager.init(event.slider.getContainer());

		this.#sidePanel = event.slider;
	}

	onClose()
	{
		this.#hintManager.hide();
	}

	#checkFormData(): boolean
	{
		const ownerIds = this.#layout.ownerField?.getSelector().getTags().map((tag) => tag.id);
		if (ownerIds.length === 0)
		{
			this.#layout.ownerField.setErrors([Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_ERROR_REQUIRED_OWNER')]);

			return false;
		}
		this.#layout.ownerField.cleanError();

		return true;
	}

	#collectFormData(formData: FormData): Data
	{
		let ownerId = formData.ownerId;
		if (this.#layout.ownerField?.getSelector().getDialog().isLoaded())
		{
			ownerId = this.#layout.ownerField?.getSelector().getTags().map((tag) => tag.id);
		}

		let moderators = formData.moderators;
		if (this.#layout.moderatorsField?.getSelector().getDialog().isLoaded())
		{
			moderators = this.#layout.moderatorsField?.getSelector().getTags().map((tag) => tag.id);
		}

		return {
			id: formData.id,
			ownerId: ownerId[0],
			moderators,
			options: {
				showHistory: this.#layout.showHistory.getValue(),
				manageMessages: this.#layout.manageMessages.getValue(),
				whoCanInvite: this.#layout.whoCanInvite.getValue(),
			},
			permissions: {
				tasks: {
					view_all: this.#layout.tasksViewUsersField.getValue(),
					sort: this.#layout.tasksSortTasksField.getValue(),
					create_tasks: this.#layout.tasksCreateTasksField.getValue(),
					edit_tasks: this.#layout.tasksEditTasksField.getValue(),
					delete_tasks: this.#layout.tasksDeleteTasksField.getValue(),
				},
			},
		};
	}

	#prepareFields(formData: FormData): void
	{
		this.#prepareBaseFields(formData);
		this.#prepareTasksFields(formData);
	}

	#prepareBaseFields(formData: FormData): void
	{
		this.#layout.ownerField = this.#getOwnerField(formData.ownerId);
		this.#layout.moderatorsField = this.#getModeratorsField(formData.moderators);

		this.#layout.showHistory = this.#getShowHistoryField(
			formData.optionsLabels,
			formData.options?.showHistory,
		);

		this.#layout.whoCanInvite = this.#getWhoCanInviteField(
			formData.permissionsLabels,
			formData.options?.whoCanInvite,
		);

		this.#layout.manageMessages = this.#getManageMessagesField(
			formData.permissionsLabels,
			formData.options?.manageMessages,
		);
	}

	#prepareTasksFields(formData: FormData): void
	{
		const tasks = Type.isPlainObject(formData.permissions?.tasks) ? formData.permissions.tasks : {};

		this.#layout.tasksViewUsersField = this.#getTasksViewUsersField(
			formData.rightsPermissionsLabels,
			tasks?.view_all,
		);
		this.#layout.tasksSortTasksField = this.#getTasksSortTasksField(
			formData.rightsPermissionsLabels,
			tasks?.sort,
		);
		this.#layout.tasksCreateTasksField = this.#getTasksCreateTasksField(
			formData.rightsPermissionsLabels,
			tasks?.create_tasks,
		);
		this.#layout.tasksEditTasksField = this.#getTasksEditTasksField(
			formData.rightsPermissionsLabels,
			tasks?.edit_tasks,
		);
		this.#layout.tasksDeleteTasksField = this.#getTasksDeleteTasksField(
			formData.rightsPermissionsLabels,
			tasks?.delete_tasks,
		);
	}

	#getOwnerField(ownerId: number): UserSelector
	{
		const label = this.#getFieldLabel(
			'OwnerHint',
			Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_OWNER_LABEL'),
			Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_OWNER_LABEL_HINT'),
		);

		return new UserSelector({
			id: 'sn-collab-form-field-owner',
			label,
			enableAll: false,
			enableDepartments: false,
			multiple: false,
			values: [['user', ownerId]],
		});
	}

	#getModeratorsField(moderators: Array<number>): UserSelector
	{
		const label = this.#getFieldLabel(
			'ModeratorsHint',
			Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_MODERATORS_LABEL'),
			Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_MODERATORS_LABEL_HINT'),
		);

		return new UserSelector({
			id: 'sn-collab-form-field-moderators',
			label,
			enableAll: false,
			enableDepartments: false,
			multiple: true,
			values: moderators.map((moderatorId) => ['user', moderatorId]),
		});
	}

	#getShowHistoryField(options: { [string]: string }, selectedValue: ?string): Selector
	{
		const label = this.#getFieldLabel(
			'ShowHistoryHint',
			Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_SHOW_HISTORY_LABEL'),
		);

		return this.#getSelector('showHistory', label, options, selectedValue);
	}

	#getWhoCanInviteField(options: { [string]: string }, selectedValue: ?string): Selector
	{
		const label = this.#getFieldLabel(
			'InitiateHint',
			Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_INITIATE_LABEL'),
			Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_INITIATE_LABEL_HINT'),
		);

		return this.#getSelector('whoCanInvite', label, options, selectedValue);
	}

	#getManageMessagesField(options: { [string]: string }, selectedValue: ?string): Selector
	{
		const label = this.#getFieldLabel(
			'ChatHint',
			Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_CHAT_LABEL'),
			Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_CHAT_LABEL_HINT'),
		);

		return this.#getSelector('manageMessages', label, options, selectedValue);
	}

	#getTasksViewUsersField(options: { [string]: string }, selectedValue: ?string): Selector
	{
		const label = this.#getFieldLabel(
			'TasksViewUsersHint',
			Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_TVU_LABEL'),
		);

		return this.#getSelector('initiate', label, options, selectedValue);
	}

	#getTasksSortTasksField(options: { [string]: string }, selectedValue: ?string): Selector
	{
		const label = this.#getFieldLabel(
			'TasksSortTasksHint',
			Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_TST_LABEL'),
		);

		return this.#getSelector('initiate', label, options, selectedValue);
	}

	#getTasksCreateTasksField(options: { [string]: string }, selectedValue: ?string): Selector
	{
		const label = this.#getFieldLabel(
			'TasksCreateTasksHint',
			Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_TCT_LABEL'),
		);

		return this.#getSelector('initiate', label, options, selectedValue);
	}

	#getTasksEditTasksField(options: { [string]: string }, selectedValue: ?string): Selector
	{
		const label = this.#getFieldLabel(
			'TasksEditTasksHint',
			Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_TET_LABEL'),
		);

		return this.#getSelector('initiate', label, options, selectedValue);
	}

	#getTasksDeleteTasksField(options: { [string]: string }, selectedValue: ?string): Selector
	{
		const label = this.#getFieldLabel(
			'TasksDeleteTasksHint',
			Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_TDT_LABEL'),
		);

		return this.#getSelector('initiate', label, options, selectedValue);
	}

	#getFieldLabel(id: string, label: string, hint: ?string = null): string
	{
		if (hint === null)
		{
			return `
				<div class="tasks-flow__create-title-with-hint">
					${label}
				</div>
			`;
		}

		return `
			<div class="tasks-flow__create-title-with-hint">
				${label}
				<span
					data-id="${id}"
					class="ui-hint"
					data-hint="${hint}" 
					data-hint-no-icon
				><span class="ui-hint-icon"></span></span>
			</div>
		`;
	}

	#getSelector(
		id: string,
		label: HTMLElement,
		options: { [string]: string },
		selectedValue: string = 'K',
	): Selector
	{
		const items = [];

		Object.entries(options).forEach(([value, name]) => {
			items.push({
				value,
				name,
				selected: value === selectedValue,
			});
		});

		return new Selector({
			id: `sn-collab-form-field-${id}`,
			label,
			items,
			current: selectedValue,
		});
	}
}
