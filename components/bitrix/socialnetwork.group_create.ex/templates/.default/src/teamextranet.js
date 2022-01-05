import {Loc} from 'main.core';
import {PopupManager, MenuManager} from 'main.popup';

export class TeamExtranetManager
{
	constructor()
	{
		this.menuId = 'sonet_group_create_popup_action_popup';

		const emailsTextareaNode = document.getElementById('EMAILS');
		if (emailsTextareaNode)
		{
			emailsTextareaNode.addEventListener('blur', (e) => {
				if (this.value === '')
				{
					e.currentTarget.classList.remove('invite-dialog-inv-form-textarea-active');
					e.currentTarget.value = e.currentTarget.value.replace(new RegExp(/^$/), Loc.getMessage('SONET_GCE_T_EMAILS_DESCR'));
				}
			});

			emailsTextareaNode.addEventListener('focus', (e) => {
				e.currentTarget.classList.add('invite-dialog-inv-form-textarea-active');
				e.currentTarget.value = e.currentTarget.value.replace(Loc.getMessage('SONET_GCE_T_EMAILS_DESCR'), '');
			});
		}

		this.actionLinkAdd = document.getElementById('sonet_group_create_popup_action_title_add');
		this.actionLinkInvite = document.getElementById('sonet_group_create_popup_action_title_invite');

		if (this.actionLinkAdd)
		{
			this.actionLinkAdd.addEventListener('click', () => {
				this.onActionSelect('add')
			});
		}

		if (this.actionLinkInvite)
		{
			this.actionLinkInvite.addEventListener('click', () => {
				this.onActionSelect('invite')
			});
		}
		this.inviteBlock1 = document.getElementById('sonet_group_create_popup_action_block_invite');
		this.inviteBlock2 = document.getElementById('sonet_group_create_popup_action_block_invite_2');
		this.addBlock = document.getElementById('sonet_group_create_popup_action_block_add');
	}

	onActionSelect(action)
	{
		if (action !== 'add')
		{
			action = 'invite';
		}

		this.lastAction = action;

		if (action === 'invite')
		{
			this.inviteBlock1.style.display = 'block';
			this.inviteBlock2.style.display = 'block';
			this.addBlock.style.display = 'none';
			this.actionLinkInvite.classList.add('--active');
			this.actionLinkAdd.classList.remove('--active');
		}
		else
		{
			this.inviteBlock1.style.display = 'none';
			this.inviteBlock2.style.display = 'none';
			this.addBlock.style.display = 'block';
			this.actionLinkInvite.classList.remove('--active');
			this.actionLinkAdd.classList.add('--active');
		}

		MenuManager.destroy(this.menuId);
	}
}
