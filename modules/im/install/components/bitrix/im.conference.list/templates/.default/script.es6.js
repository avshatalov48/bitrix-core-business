import {Reflection, Event, Loc, Type, Tag, Dom, ajax as Ajax} from 'main.core';
import {EventEmitter} from 'main.core.events'
import {Menu} from 'main.popup';
import {MessageBox} from 'ui.dialogs.messagebox';
import {Clipboard} from "im.lib.clipboard";

const namespace = Reflection.namespace('BX.Messenger.PhpComponent');

class ConferenceList
{
	constructor(params)
	{
		this.pathToAdd = params.pathToAdd;
		this.pathToEdit = params.pathToEdit;
		this.pathToList = params.pathToList;
		this.sliderWidth = params.sliderWidth || 800;
		this.gridId = params.gridId;

		this.gridManager = Reflection.getClass('top.BX.Main.gridManager');

		this.init();
	}

	init()
	{
		this.bindEvents();
	}

	bindEvents()
	{
		EventEmitter.subscribe('Grid::updated', () => {
			this.bindGridEvents();
		});

		this.bindCreateButtonEvents();
		this.bindGridEvents();
	}

	bindCreateButtonEvents()
	{
		const emptyListCreateButton = document.querySelector('.im-conference-list-empty-button');
		if (emptyListCreateButton)
		{
			Event.bind(emptyListCreateButton, 'click', () => {
				this.openCreateSlider();
			});
		}

		const panelCreateButton = document.querySelector('.im-conference-list-panel-button-create');
		Event.bind(panelCreateButton, 'click', () => {
			this.openCreateSlider();
		});
	}

	bindGridEvents()
	{
		//grid rows
		this.rows = document.querySelectorAll('.main-grid-row');
		this.rows.forEach((row) => {
			const conferenceId = row.getAttribute('data-conference-id');
			const chatId = row.getAttribute('data-chat-id');
			const publicLink = row.getAttribute('data-public-link');
			const conferenceIsFinished = !!row.getAttribute('data-conference-finished');

			//more button
			const moreButton = row.querySelector('.im-conference-list-controls-button-more');
			Event.bind(moreButton, 'click', (event) => {
				event.preventDefault();
				this.openContextMenu({
					buttonNode: moreButton, conferenceId, chatId
				});
			});

			//copy link button
			const copyButton = row.querySelector('.im-conference-list-controls-button-copy');
			Event.bind(copyButton, 'click', (event) => {
				event.preventDefault();
				this.copyLink(publicLink);
			});

			//chat name link
			const chatNameLink = row.querySelector('.im-conference-list-chat-name-link');
			Event.bind(chatNameLink, 'click', (event) => {
				event.preventDefault();
				this.openEditSlider(conferenceId);
			});
		});
	}

	openCreateSlider()
	{
		this.openSlider(this.pathToAdd);
	}

	openEditSlider(conferenceId)
	{
		const pathToEdit = this.pathToEdit.replace('#id#', conferenceId);
		this.openSlider(pathToEdit);
	}

	openSlider(path)
	{
		this.closeContextMenu();

		if (Reflection.getClass('BX.SidePanel'))
		{
			BX.SidePanel.Instance.open(path, {width: this.sliderWidth, cacheable: false});
		}
	}

	copyLink(link)
	{
		Clipboard.copy(link);

		if (Reflection.getClass('BX.UI.Notification.Center'))
		{
			BX.UI.Notification.Center.notify({
				content: Loc.getMessage('CONFERENCE_LIST_NOTIFICATION_LINK_COPIED')
			})
		}
	}

	openContextMenu({buttonNode, conferenceId, chatId})
	{
		Ajax.runComponentAction('bitrix:im.conference.list', "getAllowedOperations", {
				mode: 'ajax',
				data: { conferenceId }
			})
			.then(({data: {delete: canDelete, edit: canEdit}}) => {
				if (Type.isDomNode(buttonNode))
				{
					const menuItems = [
						{
							text: Loc.getMessage('CONFERENCE_LIST_CONTEXT_MENU_CHAT'),
							onclick: () => {
								this.openChat(chatId)
							}
						}
					];

					if (canEdit)
					{
						menuItems.push({
							text: Loc.getMessage('CONFERENCE_LIST_CONTEXT_MENU_EDIT'),
							onclick: () => {
								this.openEditSlider(conferenceId);
							}
						});
					}

					if (canDelete)
					{
						menuItems.push({
							text: Loc.getMessage('CONFERENCE_LIST_CONTEXT_MENU_DELETE'),
							className: 'im-conference-list-context-menu-item-delete menu-popup-no-icon',
							onclick: () => {
								this.deleteAction(conferenceId);
							}
						});
					}

					this.menu = new Menu({
						bindElement: buttonNode,
						items: menuItems,
						events: {
							onPopupClose: function()
							{
								this.destroy();
							}
						}
					});

					this.menu.show();
				}
			})
			.catch((response) => {
				console.error(response);
			});
	}

	closeContextMenu()
	{
		if (this.menu)
		{
			this.menu.close();
		}
	}

	openChat(chatId)
	{
		this.closeContextMenu();

		if (Reflection.getClass('BXIM.openMessenger'))
		{
			BXIM.openMessenger('chat' + chatId);
		}
	}

	deleteAction(conferenceId)
	{
		this.closeContextMenu();

		Ajax.runComponentAction('bitrix:im.conference.list', "deleteConference", {
				mode: 'ajax',
				data: { conferenceId }
			})
			.then((response) => {
				this.onSuccessfulDelete(response);
			})
			.catch((response) => {
				this.onFailedDelete(response);
			});
	}

	onSuccessfulDelete(response)
	{
		if (response.data['LAST_ROW'] === true)
		{
			top.window.location = this.pathToList;

			return true;
		}

		if (this.gridManager)
		{
			this.gridManager.reload(this.gridId);
		}
	}

	onFailedDelete(response)
	{
		MessageBox.alert(response["errors"][0].message);
	}
}

namespace.ConferenceList = ConferenceList;