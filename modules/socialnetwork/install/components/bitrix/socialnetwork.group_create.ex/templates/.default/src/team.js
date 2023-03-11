import {Loc, Type, Dom} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {TagSelector} from 'ui.entity-selector';

import {WorkgroupForm} from './index';
import {Util} from './util';

export class TeamManager
{
	static instance = null;

	static contextList = {
		owner: 'GROUP_INVITE_OWNER',
		scrumMaster: 'GROUP_INVITE_SCRUM_MASTER',
		moderators: 'GROUP_INVITE_MODERATORS',
		users: 'GROUP_INVITE',
	};

	static getInstance()
	{
		return TeamManager.instance;
	}

	constructor(params)
	{
		this.groupId = parseInt(params.groupId, 10);

		this.ownerSelector = null;
		this.scrumMasterSelector = null;
		this.moderatorsSelector = null;
		this.usersSelector = null;

		this.ownerOptions = params.ownerOptions || {};
		this.scrumMasterOptions = params.scrumMasterOptions || {};
		this.moderatorsOptions = params.moderatorsOptions || {};
		this.usersOptions = params.usersOptions || {};

		this.ownerContainerNode = document.getElementById('GROUP_OWNER_selector');
		this.scrumMasterContainerNode = document.getElementById('SCRUM_MASTER_selector');
		this.moderatorsContainerNode = document.getElementById('GROUP_MODERATORS_selector');
		this.usersContainerNode = document.getElementById('GROUP_USERS_selector');

		this.isCurrentUserAdmin = (Type.isBoolean(params.isCurrentUserAdmin) ? params.isCurrentUserAdmin : false);
		this.extranetInstalled = (Type.isBoolean(params.extranetInstalled) ? params.extranetInstalled : false);
		this.allowExtranet = (Type.isBoolean(params.allowExtranet) ? params.allowExtranet : false);

		TeamManager.instance = this;

		this.buildOwnerSelector();
		this.buildScrumMasterSelector();
		this.buildModeratorsSelector();
		this.buildUsersSelector();

		this.bindEvents();
	}

	buildOwnerSelector()
	{
		if (!Type.isDomNode(this.ownerContainerNode))
		{
			return;
		}

		Dom.clean(this.ownerContainerNode);

		const selectorOptions = this.ownerOptions;
		this.ownerSelector = new TagSelector({

			id: selectorOptions.selectorId || 'group_create_owner',
			dialogOptions: {
				id: selectorOptions.selectorId || 'group_create_owner',
				context: TeamManager.contextList.owner,
				preselectedItems: selectorOptions.value,
				events: {
					onLoad: this.onLoad.bind(this),
					'Item:onSelect': TeamManager.onOwnerSelect,
					'Item:onDeselect': TeamManager.onOwnerSelect,
				},
				entities: [
					{
						id: 'user',
						options: {
							intranetUsersOnly: !this.allowExtranet,
							inviteEmployeeLink: true,
							inviteExtranetLink: true,
							groupId: this.groupId,
							checkWorkgroupWhenInvite: true,
						},
					},
					{
						id: 'department',
						options: {
							selectMode: 'usersOnly',
						},
					},
				],
			},
			multiple: false,
			addButtonCaption: Loc.getMessage('SONET_GCE_T_ADD_OWNER'),
		});

		this.ownerSelector.renderTo(this.ownerContainerNode);
	}

	buildScrumMasterSelector()
	{
		if (!Type.isDomNode(this.scrumMasterContainerNode))
		{
			return;
		}

		Dom.clean(this.scrumMasterContainerNode);

		const selectorOptions = this.scrumMasterOptions;

		this.scrumMasterSelector = new TagSelector({

			id: selectorOptions.selectorId || 'group_create_scrum_master',
			dialogOptions: {
				id: selectorOptions.selectorId || 'group_create_scrum_master',
				context: TeamManager.contextList.scrumMaster,
				preselectedItems: selectorOptions.value,
				events: {
					onLoad: this.onLoad.bind(this),
					'Item:onSelect': TeamManager.onScrumMasterSelect,
					'Item:onDeselect': TeamManager.onScrumMasterSelect,
				},
				entities: [
					{
						id: 'user',
						options: {
							intranetUsersOnly: !this.allowExtranet,
							inviteEmployeeLink: true,
						},
					},
					{
						id: 'department',
						options: {
							selectMode: 'usersOnly',
						},
					},
				],
			},
			multiple: false,
			addButtonCaption: Loc.getMessage('SONET_GCE_T_CHANGE_SCRUM_MASTER'),
			addButtonCaptionMore: Loc.getMessage('SONET_GCE_T_CHANGE_SCRUM_MASTER_MORE'),
		});

		this.scrumMasterSelector.renderTo(this.scrumMasterContainerNode);
	}

	buildModeratorsSelector()
	{
		if (!Type.isDomNode(this.moderatorsContainerNode))
		{
			return;
		}

		Dom.clean(this.moderatorsContainerNode);

		const selectorOptions = this.moderatorsOptions;

		this.moderatorsSelector = new TagSelector({
			id: selectorOptions.selectorId || 'group_create_moderators',
			dialogOptions: {
				id: selectorOptions.selectorId || 'group_create_moderators',
				context: TeamManager.contextList.moderators,
				preselectedItems: selectorOptions.value,
				events: {
					onLoad: this.onLoad.bind(this),
					'Item:onSelect': TeamManager.onModeratorsSelect,
					'Item:onDeselect': TeamManager.onModeratorsSelect,
				},
				entities: [
					{
						id: 'user',
						options: {
							intranetUsersOnly: !this.allowExtranet,
							inviteEmployeeLink: true,
							groupId: this.groupId,
							checkWorkgroupWhenInvite: true,
						},
					},
					{
						id: 'department',
						options: {
							selectMode: 'usersOnly',
						},
					},
				],
			},
			multiple: true,
			addButtonCaption: Loc.getMessage('SONET_GCE_T_ADD_USER'),
			addButtonCaptionMore: Loc.getMessage('SONET_GCE_T_ADD_USER_MORE'),
		});

		this.moderatorsSelector.renderTo(this.moderatorsContainerNode);
	}

	buildUsersSelector()
	{
		if (!Type.isDomNode(this.usersContainerNode))
		{
			return;
		}

		Dom.clean(this.usersContainerNode);

		const selectorOptions = this.usersOptions;

		this.usersSelector = new TagSelector({
			id: selectorOptions.selectorId || 'group_create_users',
			dialogOptions: {
				id: selectorOptions.selectorId || 'group_create_users',
				context: TeamManager.contextList.users,
				preselectedItems: selectorOptions.value,
				events: {
					onLoad: this.onLoad.bind(this),
					'Item:onSelect': TeamManager.onUsersSelect,
					'Item:onDeselect': TeamManager.onUsersSelect,
				},
				entities: [
					{
						id: 'user',
						options: {
							inviteEmployeeLink: true,
							'!userId': (this.isCurrentUserAdmin ? [ parseInt(Loc.getMessage('USER_ID')) ] : []),
							intranetUsersOnly: !this.allowExtranet,
							groupId: this.groupId,
							checkWorkgroupWhenInvite: true,
						}
					},
					{
						id: 'department',
						options: {
							selectMode: (selectorOptions.enableSelectDepartment ? 'usersAndDepartments' : 'usersOnly'),
						},
					},
				],
			},
			multiple: true,
			addButtonCaption: Loc.getMessage('SONET_GCE_T_ADD_USER'),
			addButtonCaptionMore: Loc.getMessage('SONET_GCE_T_ADD_USER_MORE'),
		});

		this.usersSelector.renderTo(this.usersContainerNode);
	}

	bindEvents()
	{
		WorkgroupForm.getInstance().subscribe('onSwitchExtranet', this.onSwitchExtranet.bind(this));
		EventEmitter.emit('BX.Socialnetwork.WorkgroupFormTeamManager::onEventsBinded');
	}

	onSwitchExtranet(event)
	{
		const data = event.getData();
		if (!Type.isBoolean(data.isChecked))
		{
			return;
		}

		this.allowExtranet = this.extranetInstalled && data.isChecked;

		if (
			this.ownerSelector
			&& [ 'DONE', 'UNSENT' ].includes(this.ownerSelector.getDialog().loadState)
		)
		{
			this.recalcSelectorByExtranetSwitched({
				selector: this.ownerSelector,
				isChecked: data.isChecked,
				options: this.ownerOptions,
			});
			this.buildOwnerSelector();
		}

		if (
			this.scrumMasterSelector
			&& [ 'DONE', 'UNSENT' ].includes(this.scrumMasterSelector.getDialog().loadState)
		)
		{
			this.recalcSelectorByExtranetSwitched({
				selector: this.scrumMasterSelector,
				isChecked: data.isChecked,
				options: this.scrumMasterOptions,
			});

			this.buildScrumMasterSelector();
		}

		if (
			this.moderatorsSelector
			&& [ 'DONE', 'UNSENT' ].includes(this.moderatorsSelector.getDialog().loadState)
		)
		{
			this.recalcSelectorByExtranetSwitched({
				selector: this.moderatorsSelector,
				isChecked: data.isChecked,
				options: this.moderatorsOptions,
			});

			this.buildModeratorsSelector();
		}

		if (
			this.usersSelector
			&& [ 'DONE', 'UNSENT' ].includes(this.usersSelector.getDialog().loadState)
		)
		{
			this.recalcSelectorByExtranetSwitched({
				selector: this.usersSelector,
				isChecked: data.isChecked,
				options: this.usersOptions,
			});

			this.buildUsersSelector();
		}
	}

	recalcSelectorByExtranetSwitched(params)
	{
		const selector = params.selector;
		const isChecked = params.isChecked;

		const context = selector.getDialog().getContext();
		let selectedItems = selector.getDialog().getSelectedItems();

		if (
			this.extranetInstalled && !isChecked
			&& Type.isArray(selectedItems)
		)
		{
			selectedItems = selectedItems.filter((item) => {
				return !(item.getEntityId() === 'user' && item.getEntityType() === 'extranet');
			});

			switch (context)
			{
				case TeamManager.contextList.owner:
					Util.recalcInputValue({
						selectedItems: selectedItems,
						inputNodeName: 'OWNER_CODE',
						inputContainerNodeId: 'OWNER_CODE_container',
						multiple: false,
					});
					break;
				case TeamManager.contextList.scrumMaster:
					Util.recalcInputValue({
						selectedItems: selectedItems,
						inputNodeName: 'SCRUM_MASTER_CODE',
						inputContainerNodeId: 'SCRUM_MASTER_CODE_container',
						multiple: false,
					});
					break;
				case TeamManager.contextList.moderators:
					Util.recalcInputValue({
						selectedItems: selectedItems,
						inputNodeName: 'MODERATOR_CODES',
						inputContainerNodeId: 'MODERATOR_CODES_container',
						multiple: true,
					});
					break;
				case TeamManager.contextList.users:
					Util.recalcInputValue({
						selectedItems: selectedItems,
						inputNodeName: 'USER_CODES',
						inputContainerNodeId: 'USER_CODES_container',
						multiple: true,
					});
					break;
				default:
			}
		}

		params.options.value = selectedItems.map((item) => {
			return [ item.getEntityId(), item.getId() ];
		});
	}

	onLoad(event: BaseEvent)
	{
		switch (event.getTarget().context)
		{
			case TeamManager.contextList.owner:
				this.recalcSelectorByExtranetSwitched({
					selector: this.ownerSelector,
					isChecked: this.allowExtranet,
					options: this.ownerOptions,
				});
				break;
			case TeamManager.contextList.scrumMaster:
				this.recalcSelectorByExtranetSwitched({
					selector: this.scrumMasterSelector,
					isChecked: this.allowExtranet,
					options: this.scrumMasterOptions,
				});
				break;
			case TeamManager.contextList.moderators:
				this.recalcSelectorByExtranetSwitched({
					selector: this.moderatorsSelector,
					isChecked: this.allowExtranet,
					options: this.moderatorsOptions,
				});

				if (WorkgroupForm.getInstance().initialFocus === 'addModerator')
				{
					this.moderatorsSelector.getAddButtonLink().click();
				}

				break;
			case TeamManager.contextList.users:
				this.recalcSelectorByExtranetSwitched({
					selector: this.usersSelector,
					isChecked: this.allowExtranet,
					options: this.usersOptions,
				});
				break;
			default:
		}
	}

	static onOwnerSelect(event: BaseEvent)
	{
		Util.recalcInputValue({
			selectedItems: event.getTarget().getSelectedItems(),
			inputNodeName: 'OWNER_CODE',
			inputContainerNodeId: 'OWNER_CODE_container',
			multiple: false,
		});
	}

	static onScrumMasterSelect(event: BaseEvent)
	{
		Util.recalcInputValue({
			selectedItems: event.getTarget().getSelectedItems(),
			inputNodeName: 'SCRUM_MASTER_CODE',
			inputContainerNodeId: 'SCRUM_MASTER_CODE_container',
			multiple: false,
		});
	}

	static onModeratorsSelect(event: BaseEvent)
	{
		Util.recalcInputValue({
			selectedItems: event.getTarget().getSelectedItems(),
			inputNodeName: 'MODERATOR_CODES',
			inputContainerNodeId: 'MODERATOR_CODES_container',
			multiple: true,
		});
	}

	static onUsersSelect(event: BaseEvent)
	{
		Util.recalcInputValue({
			selectedItems: event.getTarget().getSelectedItems(),
			inputNodeName: 'USER_CODES',
			inputContainerNodeId: 'USER_CODES_container',
			multiple: true,
		});

		const hintNode = document.getElementById('GROUP_ADD_DEPT_HINT_block');
		if (hintNode)
		{
			TeamManager.showDepartmentHint({
				selectedItems: event.getTarget().getSelectedItems(),
				hintNode: hintNode,
			});
		}
	}

	static showDepartmentHint(params)
	{
		const selectedItems = params.selectedItems || {};
		const hintNode = params.hintNode || null;
		if (!Type.isDomNode(hintNode))
		{
			return;
		}

		if (!Type.isArray(selectedItems))
		{
			hintNode.classList.remove('visible');
			return;
		}

		const departmentFound = !Type.isUndefined(selectedItems.find((item) => {
			return (item.entityId === 'department');
		}));

		if (departmentFound)
		{
			hintNode.classList.add('visible');
		}
		else
		{
			hintNode.classList.remove('visible');
		}
	}
}
