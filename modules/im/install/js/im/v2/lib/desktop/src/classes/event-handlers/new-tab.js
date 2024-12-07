import { EventType } from 'im.v2.const';
import { DesktopApi } from 'im.v2.lib.desktop-api';

export class NewTabHandler
{
	static init(): NewTabHandler
	{
		return new NewTabHandler();
	}

	constructor()
	{
		this.#subscribeToNewTabEvent();
	}

	#subscribeToNewTabEvent()
	{
		DesktopApi.subscribe(EventType.desktop.onNewTabClick, this.#onNewTabClick.bind(this));
	}

	#onNewTabClick()
	{
		DesktopApi.createTab('/desktop/menu/');
	}
}
