import { Reflection } from 'main.core';
import { EventEmitter } from 'main.core.events';

const namespace = Reflection.namespace('BX.Mail.AddressBook');
const gridId = 'MAIL_ADDRESSBOOK_LIST';

import { Avatar } from 'mail.avatar';

BX.ready(function()
{
	const addContactButton = document.getElementById('mail-address-book-add-button');

	addContactButton.onclick = () => {
		top.BX.Runtime.loadExtension('mail.dialogeditcontact').then(
			() => top.BX.Mail.AddressBook.DialogEditContact.openCreateDialog(),
		);
	};

	namespace.openEditDialog = function(attributes) {
		top.BX.Runtime.loadExtension('mail.dialogeditcontact').then(
			() => top.BX.Mail.AddressBook.DialogEditContact.openEditDialog(attributes),
		);
	};

	namespace.openRemoveDialog = function(configContact) {
		top.BX.Runtime.loadExtension('mail.dialogeditcontact').then(
			() => {
				top.BX.Mail.AddressBook.DialogEditContact.openRemoveDialog(configContact).then(
					() => reloadGrid(gridId),
				);
			},
		);
	};

	function reloadGrid(gridID)
	{
		const gridObject = BX.Main.gridManager.getById(gridID);

		if (gridObject.hasOwnProperty('instance'))
		{
			gridObject.instance.reloadTable('POST');
		}
	}

	Avatar.replaceTagsWithAvatars({
		className: 'mail-ui-avatar',
	});

	EventEmitter.subscribe('SidePanel.Slider:onMessage', (event) => {
		const [messageEvent] = event.getCompatData();

		if(messageEvent.getEventId() === 'dialogEditContact::reloadList')
		{
			reloadGrid(gridId);
		}
	});

	EventEmitter.subscribe('Grid::updated', (event) => {
		const [messageEvent] = event.getCompatData();

		if (messageEvent.containerId === 'MAIL_ADDRESSBOOK_LIST')
		{
			Avatar.replaceTagsWithAvatars({
				className: 'mail-ui-avatar',
			});
		}
	});
});