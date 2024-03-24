import { ajax, Loc } from 'main.core';
import { Messenger } from 'im.public';
import { Dialog } from 'ui.entity-selector';
import { UserSelector } from './user-selector/user-selector';

type Params = {
	entityType: 'group' | 'user',
	entityId: number,
	groupMembersList: any,
}

export class Chat
{
	#entityType: 'group' | 'user';
	#entityId: number;
	#groupMembersList: any;

	constructor(params: Params)
	{
		this.#entityType = params.entityType;
		this.#entityId = params.entityId;
		this.#groupMembersList = params.groupMembersList;
	}

	startVideoCall()
	{
		// eslint-disable-next-line promise/catch-or-return
		ajax.runAction('intranet.controlbutton.getVideoCallChat', {
			data: {
				entityType: this.#entityType === 'group' ? 'workgroup' : 'user',
				entityId: this.#entityId,
			},
			analyticsLabel: {
				entity: this.#entityType,
			},
		}).then((response) => {
			if (response.data)
			{
				Messenger.startVideoCall(`chat${response.data}`, true);
			}

			this.chatLockCounter = 0;
		}, (response) => {
			if (
				response.errors[0].code === 'lock_error'
				&& this.chatLockCounter < 4
			)
			{
				this.chatLockCounter++;
				this.startVideoCall();
			}
		});
	}

	openChat()
	{
		// eslint-disable-next-line promise/catch-or-return
		ajax.runAction('intranet.controlbutton.getChat', {
			data: {
				entityType: this.#entityType === 'group' ? 'workgroup' : 'user',
				entityId: this.#entityId,
			},
			analyticsLabel: {
				entity: this.#entityType,
			},
		}).then((response) => {
			if (response.data)
			{
				Messenger.openChat(`chat${parseInt(response.data, 10)}`);
			}

			this.chatLockCounter = 0;
		}, (response) => {
			if (response.errors[0].code === 'lock_error' && this.chatLockCounter < 4)
			{
				this.chatLockCounter++;
				this.openChat();
			}
		});
	}

	createChat(node)
	{
		this.getDialog(node).show();
	}

	getDialog(node): Dialog
	{
		if (!this.userSelector)
		{
			this.userSelector = new UserSelector({
				bindElement: node,
				createChat: true,
				title: Loc.getMessage('SN_SPACES_CREATE_CHAT'),
				onLoad: this.#updateMemberNodes.bind(this),
				groupId: this.#entityId,
			});
		}

		return this.userSelector.getDialog();
	}

	update(groupDataPromise)
	{
		// eslint-disable-next-line promise/catch-or-return
		groupDataPromise.then((response) => {
			this.#groupMembersList = response.groupMembersList;

			this.userSelector?.reload();
		});
	}

	#updateMemberNodes()
	{
		const membersIds = this.#groupMembersList
			.filter((user) => user.isMember)
			.map((item) => parseInt(item.id))
		;

		this.getDialog().getItems().forEach((item) => {
			const isHidden = !membersIds.includes(item.getId());
			item.setHidden(isHidden);

			if (isHidden && item.isSelected())
			{
				item.deselect();
			}
		});
	}
}
