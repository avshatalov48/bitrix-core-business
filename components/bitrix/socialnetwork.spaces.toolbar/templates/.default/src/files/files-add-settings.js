import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Menu } from 'main.popup';

type Params = {
	bindElement: HTMLElement,
	documentHandlers: Array<DocumentHandler>
}

export type DocumentHandler = {
	code: string,
	name: string,
}

export class FilesAddSettings extends EventEmitter
{
	#menu: Menu;
	#documentHandlers: Array<DocumentHandler>;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.FilesAddSettings');

		this.#documentHandlers = params.documentHandlers;

		this.#menu = this.#createMenu(params.bindElement);
	}

	show(): void
	{
		this.#menu.toggle();
	}

	#createMenu(bindElement: HTMLElement): Menu
	{
		const fileUploadItemId = 'spaces-files-add-file-item';

		const menu = new Menu({
			id: 'spaces-files-add-settings',
			bindElement,
			closeByEsc: true,
			events: {
				onShow: (event) => {
					this.emit('show', {
						fileUploadContainer: menu.getMenuItem(fileUploadItemId).getContainer(),
					});
				},
				onClose: () => {
					this.emit('close', {
						fileUploadContainer: menu.getMenuItem(fileUploadItemId).getContainer(),
					});
				},
			},
		});

		menu.addMenuItem({
			dataset: { id: 'spaces-files-add-settings-file' },
			id: fileUploadItemId,
			text: Loc.getMessage('SN_SPACES_FILES_ADD_SETTINGS_FILE'),
		});

		menu.addMenuItem({
			dataset: { id: 'spaces-files-add-settings-folder' },
			text: Loc.getMessage('SN_SPACES_FILES_ADD_SETTINGS_FOLDER'),
			onclick: () => {
				this.emit('addFolder');
			},
		});

		this.#documentHandlers.forEach((handler: DocumentHandler) => {
			menu.addMenuItem({
				dataset: { id: `spaces-files-add-settings-type-${handler.code}` },
				text: handler.name,
				items: [
					{
						text: Loc.getMessage('SN_SPACES_FILES_ADD_SETTINGS_DOC'),
						onclick: () => {
							menu.close();
							this.emit('addDoc', { handlerCode: handler.code });
						},
					},
					{
						text: Loc.getMessage('SN_SPACES_FILES_ADD_SETTINGS_TABLE'),
						onclick: () => {
							menu.close();
							this.emit('addTable', { handlerCode: handler.code });
						},
					},
					{
						text: Loc.getMessage('SN_SPACES_FILES_ADD_SETTINGS_PRESENTATION'),
						onclick: () => {
							menu.close();
							this.emit('addPresentation', { handlerCode: handler.code });
						},
					},
				],
			});
		});

		return menu;
	}
}
