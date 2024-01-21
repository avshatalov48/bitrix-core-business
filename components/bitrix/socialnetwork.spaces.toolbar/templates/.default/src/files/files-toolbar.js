import { Dom, Event, Tag, Type, Loc, Cache } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { Filter } from '../filter';
import { DocumentHandler, FilesAddSettings } from './files-add-settings';
import { FilesRouter } from './files-router';
import { FilesSettings } from './files-settings';
import { FilesDisk } from './files-disk';

type Params = {
	diskComponentId: string,
	networkDriveLink: string,
	pathToUserFilesVolume?: string,
	pathToFilesBizprocWorkflowAdmin?: string,
	pathToTrash: string,
	documentHandlers: Array<DocumentHandler>,
	permissions: { [key: string]: boolean },
	listAvailableFeatures: { [key: string]: boolean },
	featureRestrictionMap: { [key: string]: string },
	isTrashMode?: boolean,
	filterId: string,
	filterContainer: HTMLElement,
}

export class FilesToolbar
{
	#cache = new Cache.MemoryCache();

	#router: FilesRouter;
	#disk: FilesDisk;
	#addSettings: FilesAddSettings;
	#settings: FilesSettings;
	#filter: Filter;

	constructor(params: Params)
	{
		this.#setParams(params);

		this.#router = new FilesRouter({
			pathToTrash: this.#getParam('pathToTrash'),
			pathToUserFilesVolume: this.#getParam('pathToUserFilesVolume'),
		});

		this.#disk = new FilesDisk({
			diskComponentId: params.diskComponentId,
			networkDriveLink: this.#getParam('networkDriveLink'),
			pathToFilesBizprocWorkflowAdmin: this.#getParam('pathToFilesBizprocWorkflowAdmin'),
			listAvailableFeatures: this.#getParam('listAvailableFeatures'),
			featureRestrictionMap: this.#getParam('featureRestrictionMap'),
		});

		this.#filter = new Filter({
			filterId: params.filterId,
			filterContainer: params.filterContainer,
		});
	}

	renderAddBtnTo(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.FilesToolbar: HTMLElement for add btn not found');
		}

		Dom.append(this.#renderAddBtn(), container);
	}

	renderCleanBtnTo(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.FilesToolbar: HTMLElement for add btn not found');
		}

		Dom.append(this.#renderCleanBtn(), container);
	}

	renderSettingsBtnTo(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.FilesToolbar: HTMLElement for settings btn not found');
		}

		Dom.append(this.#renderSettingsBtn(), container);
	}

	#setParams(params: Params)
	{
		this.#cache.set('params', params);
	}

	#getParam(param: string): any
	{
		return this.#cache.get('params')[param];
	}

	#renderAddBtn(): HTMLElement
	{
		const node = Tag.render`
			<div class="ui-btn-split ui-btn-success ui-btn-round ui-btn-no-caps">
				<div data-id="spaces-files-add-main-btn" class="ui-btn-main">
					${Loc.getMessage('SN_SPACES_FILES_ADD_FILE')}
				</div>
				<div data-id="spaces-files-add-menu-btn" class="ui-btn-menu"></div>
			</div>
		`;

		Event.bind(node.querySelector('.ui-btn-menu'), 'click', this.#addMenuClick.bind(this));

		Event.bind(node.querySelector('.ui-btn-main'), 'mouseenter', (event) => {
			this.#disk.appendUploadInput(event.target);
		});
		Event.bind(node.querySelector('.ui-btn-main'), 'mouseleave', (event) => {
			this.#disk.hideUploadInput(event.target);
		});

		return node;
	}

	#renderCleanBtn(): HTMLElement
	{
		const node = Tag.render`
			<div class="ui-btn ui-btn-success ui-btn-round ui-btn-no-caps">
				${Loc.getMessage('SN_SPACES_FILES_CLEAN_BTN')}
			</div>
		`;

		Event.bind(node, 'click', this.#cleanClick.bind(this));

		return node;
	}

	#renderSettingsBtn(): HTMLElement
	{
		const node = Tag.render`
			<button
				data-id="spaces-files-settings-btn"
				class="ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-themes sn-spaces__toolbar-space_btn-more"
			>
				<div class="ui-icon-set --more"></div>
			</button>
		`;

		Event.bind(node, 'click', this.#settingsClick.bind(this));

		return node;
	}

	#addMenuClick(event)
	{
		if (!this.#addSettings)
		{
			this.#addSettings = new FilesAddSettings({
				bindElement: event.target,
				documentHandlers: this.#getParam('documentHandlers'),
			});
			this.#addSettings.subscribe('show', (baseEvent: BaseEvent) => {
				const { fileUploadContainer } = baseEvent.getData();
				this.#disk.appendUploadInput(fileUploadContainer);
			});
			this.#addSettings.subscribe('close', (baseEvent: BaseEvent) => {
				const { fileUploadContainer } = baseEvent.getData();
				this.#disk.hideUploadInput(fileUploadContainer);
			});
			this.#addSettings.subscribe('addFolder', () => {
				this.#disk.createFolder();
			});
			this.#addSettings.subscribe('addDoc', (baseEvent: BaseEvent) => {
				const { handlerCode } = baseEvent.getData();
				this.#disk.runCreatingDocFile(handlerCode);
			});
			this.#addSettings.subscribe('addTable', (baseEvent: BaseEvent) => {
				const { handlerCode } = baseEvent.getData();
				this.#disk.runCreatingTableFile(handlerCode);
			});
			this.#addSettings.subscribe('addPresentation', (baseEvent: BaseEvent) => {
				const { handlerCode } = baseEvent.getData();
				this.#disk.runCreatingPresentationFile(handlerCode);
			});
		}

		this.#addSettings.show();
	}

	#cleanClick()
	{
		this.#disk.cleanTrash();
	}

	#settingsClick(event)
	{
		if (!this.#settings)
		{
			this.#settings = new FilesSettings({
				bindElement: event.target,
				permissions: this.#getParam('permissions'),
				featureRestrictionMap: this.#getParam('featureRestrictionMap'),
				isTrashMode: this.#getParam('isTrashMode'),
			});
			this.#settings.subscribe('rights', () => {
				this.#disk.showRights();
			});
			this.#settings.subscribe('bizproc', () => {
				this.#disk.showBizproc();
			});
			this.#settings.subscribe('bizprocSettings', () => {
				this.#disk.showBizprocSettings();
			});
			this.#settings.subscribe('network', () => {
				this.#disk.showNetworkDriveConnect();
			});
			this.#settings.subscribe('clean', () => {
				this.#router.redirectToVolume();
			});
			this.#settings.subscribe('docSettings', () => {
				this.#disk.openWindowForSelectDocumentService();
			});
			this.#settings.subscribe('trash', () => {
				this.#router.redirectToTrash();
			});
		}

		this.#settings.show();
	}
}
