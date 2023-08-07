import { Type, Reflection, Event, Loc } from 'main.core';
import { Avatar } from 'mail.avatar';
import { EventEmitter } from 'main.core.events';
import { MessageGrid } from 'mail.messagegrid';
import { ProgressBar } from './src/js/progressbar.es6';
import { Counters } from './src/js/counters.es6';
import { LeftMenu } from './src/js/leftmenu.es6';
import { List } from './src/js/list.es6';
import { Button } from 'ui.buttons';

const namespaceMailHome = Reflection.namespace('BX.Mail.Home');

EventEmitter.subscribe('SidePanel.Slider:onMessage', (event) => {
	const [messageEvent] = event.getCompatData();
	if(messageEvent.getEventId() === 'mail-mailbox-config-success')
	{
		BXMailMailbox.sync(namespaceMailHome.ProgressBar, namespaceMailHome.Grid.getId(), false,true);
	}
});

let sliderPage;
let progressBar;
let errorBox;
let syncButtonWrapper;

let selectedIdsForRecovery = {};

Event.ready(() => {

	syncButtonWrapper = document.querySelector('[data-role="mail-msg-sync-button-wrapper"]');

	const syncButton = new Button({
		className:"ui-btn ui-btn-themes ui-btn-light-border mail-msg-sync-button",
		icon: Button.Icon.BUSINESS,
		props: {
			title: Loc.getMessage("MAIL_MESSAGE_SYNC_BTN_HINT")
		},
		onclick: function() {
			BXMailMailbox.sync(namespaceMailHome.ProgressBar, Loc.getMessage("MAIL_MESSAGE_GRID_ID"),false,true);
		},
	});

	syncButtonWrapper.append(
		syncButton.getContainer()
	)

	EventEmitter.subscribe('BX.Main.Grid:onBeforeReload', (event) => {
		const [grid] = event.getCompatData();
		if(grid !== {} && grid !== undefined && Loc.getMessage("MAIL_MESSAGE_GRID_ID") === grid.getId())
		{
			selectedIdsForRecovery = grid.getRows().getSelectedIds();
		}
	});

	EventEmitter.subscribe('Grid::updated', (event) => {
		const [grid] = event.getCompatData();

		if(grid !== {} && grid !== undefined && Loc.getMessage("MAIL_MESSAGE_GRID_ID") === grid.getId())
		{
			let rowsWereSelected = false;
			namespaceMailHome.Grid.getRows().map(function (row)
			{
				if(Type.isFunction(selectedIdsForRecovery.indexOf) && selectedIdsForRecovery.indexOf(row.getId()) !== -1)
				{
					if(row.isShown())
					{
						row.select();
						rowsWereSelected = true;
					}
				}
			})
			selectedIdsForRecovery = {}

			if(rowsWereSelected)
			{
				setTimeout(
					function ()
					{
						EventEmitter.emit(window,'Grid::thereSelectedRows');
					},
					0
				);
			}
		}
	});

	Avatar.replaceTagsWithAvatars({
		className: 'mail-ui-avatar',
	});

	sliderPage = document.getElementsByClassName("ui-slider-page")[0];
	progressBar = document.querySelector('[data-role="mail-progress-bar"]');
	sliderPage.insertBefore(progressBar,sliderPage.firstChild);
	errorBox = document.querySelector('[data-role="error-box"]');

	namespaceMailHome.ProgressBar = new ProgressBar(progressBar);
	namespaceMailHome.unreadMessageMailboxesMarker = document.querySelector('[data-role="unreadMessageMailboxesMarker"]');;

	namespaceMailHome.ProgressBar.setSyncButton(syncButton);
	namespaceMailHome.ProgressBar.setErrorBoxNode(document.querySelector('[data-role="error-box"]'))
	namespaceMailHome.ProgressBar.setErrorTextNode(document.querySelector('[data-role="error-box-text"]'));
	namespaceMailHome.ProgressBar.setErrorHintNode(document.querySelector('[data-role="error-box-hint"]'));
	namespaceMailHome.ProgressBar.setErrorTitleNode(document.querySelector('[data-role="error-box-title"]'));
});

BX.ready(function() {
	namespaceMailHome.Counters = new Counters('dirs', Loc.getMessage("DEFAULT_DIR"));
	namespaceMailHome.mailboxCounters = new Counters('mailboxCounters');
	namespaceMailHome.Grid = new MessageGrid(Loc.getMessage("MAILBOX_IS_SYNC_AVAILABILITY"));
});
namespaceMailHome.LeftMenu = LeftMenu;

const namespaceClientMessage = Reflection.namespace('BX.Mail.Client.Message');
namespaceClientMessage.List = List;
