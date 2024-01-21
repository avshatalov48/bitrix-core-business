import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { UserSelector } from './user-selector/user-selector';

export class Invite extends EventEmitter
{
	#node: HTMLElement;
	#invitedUsers: number[];
	#cannotInvite: number[];

	constructor(options)
	{
		super(options);

		this.setEventNamespace('SocialNetwork.Spaces.Invite');

		this.#node = options.node;
		this.#invitedUsers = this.#prepareInvited(options.groupMembersList);
		this.#cannotInvite = this.#prepareCannotInvite(options.groupMembersList);
	}

	show(): void
	{
		this.getDialog().show();
	}

	close(): void
	{
		this.getDialog().hide();
	}

	#onClose()
	{
		const users = [...this.dialog.getSelectedItems()].map((item) => item.id);
		if (!this.#arraysAreEqual(users, this.#invitedUsers))
		{
			this.#invitedUsers = users;
			this.emit('usersSelected', this.#invitedUsers);
		}
		this.emit('onClose');
	}

	#arraysAreEqual(arr1, arr2)
	{
		return [...arr1].sort().toString() === [...arr2].sort().toString();
	}

	isShown(): boolean
	{
		this.getDialogPopup().isShown();
	}

	getDialogPopup()
	{
		return this.getDialog().getPopup();
	}

	getDialog()
	{
		if (!this.dialog)
		{
			const userSelector = new UserSelector({
				bindElement: this.#node,
				title: Loc.getMessage('SN_SPACES_MENU_SPACE_INVITE_MEMBERS'),
				preselectedItems: this.#prepareItems(),
				onClose: this.#onClose.bind(this),
				onLoad: this.#updateCannotInviteNodes.bind(this),
			});

			this.dialog = userSelector.getDialog();

			this.dialog.getPopup().setAngle({
				position: 'top',
				offset: this.#node.offsetWidth + parseInt(getComputedStyle(this.#node).marginLeft),
			});
		}

		return this.dialog;
	}

	update(groupDataPromise)
	{
		groupDataPromise.then((response) => {
			this.#invitedUsers = this.#prepareInvited(response.groupMembersList);
			this.#cannotInvite = this.#prepareCannotInvite(response.groupMembersList);

			this.#updateCannotInviteNodes();
		});
	}

	#updateCannotInviteNodes()
	{
		this.dialog?.getItems().forEach((item) => {
			const isNotInInvited = !this.#invitedUsers.includes(item.getId());
			const isHidden = this.#cannotInvite.includes(item.getId());
			item.setHidden(isHidden);

			if ((isHidden || isNotInInvited) && item.isSelected())
			{
				item.deselect();
			}
		});
	}

	#prepareInvited(users): number[]
	{
		return users
			.filter((user) => user.invited)
			.map((user) => parseInt(user.id));
	}

	#prepareCannotInvite(users): number[]
	{
		return users
			.filter((user) => !user.invited)
			.map((user) => parseInt(user.id));
	}

	#prepareItems()
	{
		return this.#invitedUsers.map((userId) => ['user', parseInt(userId)]);
	}
}