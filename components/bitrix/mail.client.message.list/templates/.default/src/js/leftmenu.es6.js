import { EventEmitter } from "main.core.events";
import { DirectoryMenu } from 'mail.directorymenu';

export class LeftMenu
{
	constructor(config={
		dirsWithUnseenMailCounters: {},
		mailboxId:'',
		filterId: '',
		systemDirs :
		{
			spam: 'Spam',
			trash: 'Trash',
			outcome: 'Outcome',
			drafts: 'Drafts',
			inbox: 'Inbox',
		}
	})
	{
		const leftDirectoryMenuWrapper = document.querySelector('.mail-left-menu-wrapper');

		this.directoryMenu = new DirectoryMenu({
			dirsWithUnseenMailCounters: config['dirsWithUnseenMailCounters'],
			filterId: config['filterId'],
			systemDirs: config['systemDirs'],
		});

		leftDirectoryMenuWrapper.append(this.directoryMenu.getNode());
	}
}