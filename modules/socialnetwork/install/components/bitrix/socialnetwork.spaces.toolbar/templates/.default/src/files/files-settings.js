import { Cache, Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Menu } from 'main.popup';

type Params = {
	bindElement: HTMLElement,
	permissions: { [key: string]: boolean },
	featureRestrictionMap: { [key: string]: string },
	isTrashMode: boolean,
}

export class FilesSettings extends EventEmitter
{
	#cache = new Cache.MemoryCache();
	#menu: Menu;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.FilesSettings');

		this.#setParams(params);

		this.#menu = this.#createMenu(params.bindElement);
	}

	show(): void
	{
		this.#menu.toggle();
	}

	#setParams(params: Params)
	{
		this.#cache.set('params', params);
	}

	#getParam(param: string): any
	{
		return this.#cache.get('params')[param];
	}

	#createMenu(bindElement: HTMLElement): Menu
	{
		const menu = new Menu({
			id: 'spaces-files-settings',
			bindElement,
			closeByEsc: true,
		});

		const permissions = this.#getParam('permissions');

		if (permissions.canChangeRights === true)
		{
			menu.addMenuItem({
				dataset: { id: 'spaces-files-settings-rights' },
				text: Loc.getMessage('SN_SPACES_FILES_SETTINGS_RIGHTS'),
				onclick: () => {
					this.emit('rights');
				},
			});
		}

		if (permissions.canChangeBizproc === true)
		{
			menu.addMenuItem({
				dataset: { id: 'spaces-files-settings-bizproc' },
				text: Loc.getMessage('SN_SPACES_FILES_BIZPROC'),
				onclick: () => {
					this.emit('bizproc');
				},
			});
		}

		if (permissions.canChangeBizprocSettings === true)
		{
			menu.addMenuItem({
				dataset: { id: 'spaces-files-settings-config-bizproc' },
				text: Loc.getMessage('SN_SPACES_FILES_SETTINGS_BIZPROC'),
				onclick: () => {
					this.emit('bizprocSettings');
				},
			});
		}

		menu.addMenuItem({
			dataset: { id: 'spaces-files-settings-network' },
			text: Loc.getMessage('SN_SPACES_FILES_SETTINGS_NETWORK'),
			onclick: () => {
				this.emit('network');
			},
		});

		menu.addMenuItem({
			dataset: { id: 'spaces-files-settings-doc' },
			text: Loc.getMessage('SN_SPACES_FILES_SETTINGS_DOC'),
			onclick: () => {
				this.emit('docSettings');
			},
		});

		if (permissions.canCleanFiles === true)
		{
			menu.addMenuItem({
				dataset: { id: 'spaces-files-settings-clean' },
				text: Loc.getMessage('SN_SPACES_FILES_SETTINGS_CLEAN'),
				onclick: () => {
					this.emit('clean');
				},
			});
		}

		if (!this.#getParam('isTrashMode'))
		{
			menu.addMenuItem({
				dataset: { id: 'spaces-files-settings-trash' },
				text: Loc.getMessage('SN_SPACES_FILES_SETTINGS_TRASH'),
				onclick: () => {
					this.emit('trash');
				},
			});
		}

		return menu;
	}
}
