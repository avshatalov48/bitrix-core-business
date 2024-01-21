import { Dom, Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Menu } from 'main.popup';

type Params = {
	bindElement: HTMLElement,
}

export class VideoCall extends EventEmitter
{
	#menu: Menu;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.VideoCall');

		this.#menu = this.#createMenu(params.bindElement);
	}

	show(): void
	{
		this.#menu.toggle();
	}

	#createMenu(bindElement: HTMLElement): Menu
	{
		const menu = new Menu({
			id: 'spaces-video-call-menu',
			bindElement,
			closeByEsc: true,
			angle: {
				position: 'top',
				offset: 31,
			},
		});

		menu.addMenuItem({
			dataset: { id: 'spaces-menu-video-call-hd' },
			text: Loc.getMessage('SN_SPACES_MENU_VIDEO_CALL_HD'),
			className: 'sn-spaces-menu-video-call-hd-icon',
			onclick: () => {
				this.emit('hd');
				menu.close();
			},
		});

		menu.addMenuItem({
			delimiter: true,
		});

		menu.addMenuItem({
			dataset: { id: 'spaces-menu-video-call-chat' },
			text: Loc.getMessage('SN_SPACES_MENU_VIDEO_CALL_CHAT'),
			className: 'sn-spaces-menu-video-call-chat-icon',
			onclick: () => {
				this.emit('chat');
				menu.close();
			},
		});

		menu.addMenuItem({
			dataset: { id: 'spaces-menu-create-chat' },
			text: Loc.getMessage('SN_SPACES_MENU_CREATE_CHAT'),
			className: 'sn-spaces-menu-create-chat-icon',
			onclick: () => {
				this.emit('createChat');
				menu.close();
			},
		});

		return menu;
	}
}
