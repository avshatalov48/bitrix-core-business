import { EventEmitter } from "main.core.events";
import { DirectoryMenu } from 'mail.directorymenu';

export class LeftMenu
{
	constructor(config={
		dirsWithUnseenMailCounters: {},
		mailboxId:'',
		filterId: '',
	})
	{
		const leftDirectoryMenuWrapper = document.querySelector('.mail-left-menu-wrapper');

		this.directoryMenu = new DirectoryMenu({
			dirsWithUnseenMailCounters: config['dirsWithUnseenMailCounters'],
			filterId: config['filterId'],
		});

		EventEmitter.subscribe('BX.Mail.Sync:newLettersArrived', () => {

			BX.ajax.runComponentAction('bitrix:mail.client.message.list', 'getDirsWithUnseenMailCounters', {
				mode: 'class',
				data: {
					mailboxId: config['mailboxId'],
				},
			}).then(response => {
				const data = response.data || {};
				BX.Mail.Home.Counters.setCounters(data);
			});
		});

		leftDirectoryMenuWrapper.append(this.directoryMenu.getNode());
	}
}