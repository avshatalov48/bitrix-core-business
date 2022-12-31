import {Dom, Loc, Type, Cache, Tag, Event} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import DefaultTab from './tabs/default-tab';
import CameraTab from './tabs/camera-tab';
import MaskTab from './tabs/mask-tab';
import UploadTab from './tabs/upload-tab';
import CanvasTab from './tabs/canvas-tab';
import CanvasMaster from './canvas-tool/canvas-master';
import CanvasPreview from './canvas-tool/canvas-preview';
import CanvasZooming from "./canvas-tool/canvas-zooming";
import CanvasMask from "./canvas-tool/canvas-mask";
import CanvasLoader from "./canvas-tool/canvas-loader";
import {CanvasDefault} from "./canvas-tool/canvas-default";

export type MaskType = {
	id: String,
	title: String,
	src: String,
	description: ?String,
	accessCode: ?String,
	editable: boolean
};

export type FileType = {
	src: String,
	maskId: ?String
};
const hiddenCanvas = Symbol('hiddenCanvas');
export class Editor extends EventEmitter
{
	static justANumber = 0;
	static repo = new Map();
	#id = 0;
	#activeTabId: String;
	#previousActiveTabId: String;
	#tabs = new Map();
	cache = new Cache.MemoryCache();
	#canvasMaster: CanvasMaster;
	#canvasPreview: CanvasPreview;
	#canvasZooming: CanvasZooming;
	#canvasMask: ?CanvasMask;

	constructor(options: {
		enableCamera: boolean,
		enableUpload: boolean,
		uploadTabOptions: ?Object,
		enableMask: boolean,
	})
	{
		super();
		this.setEventNamespace('Main.Avatar.Editor');
		this.#id = Editor.justANumber++;
		options = Type.isPlainObject(options) ? options : {};
		const tabsWithThePictureInside = [
			[CanvasTab, true],
			[MaskTab, options.enableMask, null]
		];
		tabsWithThePictureInside.forEach(([tabClass, enabled, initialOptions]) =>
		{
			if (enabled === true && tabClass.isAvailable() !== false)
			{
				const tab = new tabClass(initialOptions);
				this.#tabs.set(tabClass.code, tab);

				tab.subscribe('onSetMask', ({data}) => {
					this.getContainer().setAttribute('data-badge-is-set', 'Y');
					this.#canvasMask.mask(data);
				});
				tab.subscribe('onUnsetMask', () => {
					this.getContainer().removeAttribute('data-badge-is-set');
					this.#canvasMask.unmask();
				});
			}
		});

		const tabsWithConnectionsToThePicture = [
			[UploadTab, options.enableUpload, options.uploadTabOptions],
			[CameraTab, options.enableCamera, null]
		];
		tabsWithConnectionsToThePicture.forEach(([tabClass, enabled, initialOptions]) =>
		{
			if (enabled !== false && tabClass.isAvailable() !== false)
			{
				const tab = new tabClass(initialOptions);
				this.#tabs.set(tabClass.code, tab);
				tab.setParentTab(this.#tabs.get(CanvasTab.code));
				tab.subscribe('onClickBack', () => {
					this.setActiveTab(CanvasTab.code);
				});
				tab.subscribe('onSetFile', ({data}) => {
					if (data instanceof Blob)
					{
						this.#canvasMaster.load(data);
					}
					else
					{
						this.#canvasMaster.set(data);
					}
					this.setActiveTab(CanvasTab.code);
				});
				if (tab instanceof CameraTab)
				{
					this.subscribe('onOpen', () => {
						if (this.#activeTabId === CameraTab.code)
						{
							tab.activate.call(tab);
						}
					});
					this.subscribe('onClose', () => {
						if (this.#activeTabId === CameraTab.code)
						{
							tab.inactivate.call(tab);
						}
					});
				}
			}
		});

		let theFutureActiveTab = this.#activeTabId;
		this.#tabs.forEach((tab, tabId) => {
			if (!theFutureActiveTab || this.#tabs.get(theFutureActiveTab).getPriority() < tab.getPriority())
			{
				theFutureActiveTab = tabId;
			}
		});
		this.#setActiveTabByDefault(theFutureActiveTab);

		EventEmitter.subscribe(
			this.getEventNamespace() + ':' + 'onEditMask',
			(baseEvent: BaseEvent) => {
				//TODO describe that true mask has changed and this view is not actual.
			})
		;
		EventEmitter.subscribe(
			this.getEventNamespace() + ':' + 'onDeleteMask',
			(baseEvent: BaseEvent) => {
				//TODO describe that true mask has changed and this view is not actual.
			})
		;
		this.init();
	}

	init()
	{
		if (!this.getContainer().querySelector('canvas[data-bx-canvas="canvas"]'))
		{
			return setTimeout(this.init.bind(this), 1);
		}
		const tabsWithConnectionsToThePicture = [UploadTab, CameraTab];
		tabsWithConnectionsToThePicture.forEach((tabClass) => {
			this.getContainer().setAttribute('data-bx-' + tabClass.code + '-tab-available', this.#tabs.has(tabClass.code) ? 'Y' : 'N');
		});
		this.#canvasMaster = new CanvasMaster(this.getContainer().querySelector('canvas[data-bx-canvas="canvas"]'));
		this.#canvasPreview = new CanvasPreview(this.getContainer().querySelector('canvas[data-bx-canvas="preview"]'));
		this.#canvasZooming = new CanvasZooming({
			knob: this.getContainer().querySelector('[data-bx-role="zoom-knob"]'),
			scale: this.getContainer().querySelector('[data-bx-role="zoom-scale"]'),
			plus: this.getContainer().querySelector('[data-bx-role="zoom-plus-button"]'),
			minus: this.getContainer().querySelector('[data-bx-role="zoom-minus-button"]'),
		});
		this.#canvasMask = this.#tabs.has(MaskTab.code) ? new CanvasMask(
			this.getContainer().querySelector('[data-bx-role="canvas-mask"]')
		) : false;
		this.getContainer()
			.querySelector('[data-bx-role="unset-canvas-mask"]')
			.addEventListener('click', () => {
				this.getContainer().removeAttribute('data-badge-is-set');
				this.#canvasMask.unmask();
				this.#tabs.get(MaskTab.code).unmask();
			})
		;
		this.#canvasMaster.subscribe('onLoad', (event) => {
			this.getContainer().setAttribute('data-bx-canvas-load-status', 'loading');
			this.emit('onChange');
		});
		this.#canvasMaster.subscribe('onReset', (event 	) => {
			this.getContainer().setAttribute('data-bx-canvas-load-status', 'isnotset');
			this.#canvasZooming.reset();
			this.#canvasPreview.reset();
			this.emit('onChange');
		});
		this.getContainer().setAttribute('data-bx-canvas-load-status', 'isnotset');

		this.#canvasMaster.subscribe('onSetImage', ({data: {canvas}}) => {
			this.getContainer().setAttribute('data-bx-canvas-load-status', 'set');
			this.#canvasZooming.reset();
			this.#canvasPreview.set(canvas);
			this.emit('onSet');
			this.emit('onChange');
		});
		this.#canvasMaster.subscribe('onMove', (event) => {
			this.#canvasPreview.onMove(event);
			this.emit('onChange');
		});
		this.#canvasMaster.subscribe('onScale', (event) => {
			this.#canvasPreview.onScale(event);
			this.emit('onChange');
		});
		this.#canvasZooming.subscribe('onChange', ({data}) => {
			this.#canvasMaster.scale(data);
		});
		this.#canvasMaster.subscribe('onError', ({data}) => {
			this.getContainer().setAttribute('data-bx-canvas-load-status', 'errored');
			this.#canvasZooming.reset();
			this.#canvasPreview.reset();
			this.getContainer()
				.querySelector('[data-bx-role="tab-canvas-error"]').innerHTML = data;
		});
		this.emit('onReady');
	}

	ready(callback)
	{
		if (this.#canvasMaster)
		{
			callback.call();
		}
		else
		{
			this.subscribe('onReady', callback);
		}
		return this;
	}

	getId()
	{
		return this.#id;
	}

	getContainer()
	{
		return this.cache.remember('container', () => {
			const res = Tag.render`
				<div class="ui-avatar-editor__tab-wrapper ui-avatar-editor--scope">
					<input type="hidden" data-bx-active-tab="doesNotMatterForNowItIsAFile">
					<div class="ui-avatar-editor__tab-button-container" data-bx-role="headers" style="display:none;"></div>
					<div class="ui-avatar-editor__tab-container">
						<div data-bx-role="bodies"></div>
						<div class="ui-avatar-editor__tab-avatar-block">
							<div class="ui-avatar-editor__tab-avatar-inner">
								<div class="ui-avatar-editor__arrow-icon-container">
									<span class="ui-avatar-editor__arrow-icon"></span>
								</div>
								<div class="ui-avatar-editor__tab-avatar-image-container">
									<div data-bx-role="unset-canvas-mask" class="ui-avatar-editor__tab-avatar-image-not-allowed"></div>
									<span class="ui-avatar-editor__tab-avatar-image-item" data-bx-role="canvas-button">
										<div data-editor-role="preview-holder">
											<canvas data-bx-canvas="preview" height="165" width="165"></canvas>
										</div>
										<div class="ui-avatar-editor__tab-avatar-mask" data-bx-role="canvas-mask"></div>
									</span>
								</div>
								<div class="ui-avatar-editor__tab-avatar-desc-container">
									<span class="ui-avatar-editor__tab-avatar-desc-item"></span>
								</div>
							</div>
						</div>
					</div>
				</div>`;

			const headers = res.querySelector('[data-bx-role="headers"]');
			const bodies = res.querySelector('[data-bx-role="bodies"]');
			Array.from(this.#tabs.entries())
				.forEach(
					([itemId, itemTab: DefaultTab]) => {
						Event.bind(
							itemTab.getHeaderContainer(),
							'click',
							() => {
								this.setActiveTab(itemId);
							}
						);
						Dom.append(itemTab.getHeaderContainer(), headers);
						Dom.append(itemTab.getBodyContainer(), bodies);
					}
				);
			if (headers.querySelectorAll('[data-bx-state="visible"]').length > 1)
			{
				headers.style.display = "block";
			}

			[
				[
					UploadTab.code,
					res.querySelector('[data-bx-role="button-add-picture"][data-bx-id="upload-file"]'),
					() => { this.#selectFile(); }
				],
				[
					CameraTab.code,
					res.querySelector('[data-bx-role="button-add-picture"][data-bx-id="snap-picture"]'),
					() => { this.#snapAPicture(); }
				]
			].forEach(([tabName, buttonNode, callback]) => {
				if (this.#tabs.has(tabName))
				{
					Event.bind(buttonNode, 'click', callback);
				}
				else
				{
					Dom.remove(buttonNode);
				}
			});
			return res;
		});
	}

	#setActiveTabByDefault(activeTab: ?String)
	{
		if (this.cache.get('activeTabChangesCounter') > 0)
		{
			return;
		}
		this.setActiveTab(activeTab);
		this.cache.delete('activeTabChangesCounter');
	}

	setActiveTab(activeTab: ?String, isIt: boolean = false): ?DefaultTab
	{
		if (!this.#tabs.has(activeTab))
		{
			return null;
		}

		const activeTabChangesCounter = this.cache.get('activeTabChangesCounter') || 0;
		if (this.#activeTabId !== activeTab)
		{
			if (this.#activeTabId === null)
			{
				this.#activeTabId = activeTab;
			}
			else if (this.#tabs.has(this.#activeTabId))
			{
				this.#tabs.get(this.#activeTabId).inactivate();
			}

			if (this.#activeTabId === UploadTab.code || this.#activeTabId === CameraTab.code)
			{
				this.#previousActiveTabId = this.#activeTabId;
			}

			this.#activeTabId = activeTab;
			this.#tabs.get(this.#activeTabId).activate();
		}
		this.cache.set('activeTabChangesCounter', activeTabChangesCounter + 1);
		return this.#tabs.get(this.#activeTabId);
	}

	getTab(tabName: String): ?DefaultTab
	{
		return this.#tabs.get(tabName);
	}

	#setPreviousActiveTab()
	{
		this.setActiveTab(this.#previousActiveTabId);
	}
	
	loadJSON(data: FileType): Promise
	{
		return this.loadData(JSON.parse(data));
	}

	loadData(data: FileType): Promise
	{
		return new Promise((resolve, reject) => {
			if (Type.isPlainObject(data) && data['src'])
			{
				this.#canvasMaster
					.load(data['src'])
					.then(() => {
						if (data['maskId'] && this.#tabs.has(MaskTab.code))
						{
							this.#tabs.get(MaskTab.code).maskById(data['maskId']);
							this.#setActiveTabByDefault(MaskTab.code);
						}
						else
						{
							this.#setActiveTabByDefault(CanvasTab.code);
						}
						resolve(data['src'], this);
					})
					.catch(reject);
			}
			else
			{
				this.#setActiveTabByDefault(UploadTab.code);
				resolve(null, this);
			}
		});
	}

	loadSrc(src)
	{
		return new Promise((resolve, reject) => {
			this.#canvasMaster
				.load(src)
				.then(() => {
					this.setActiveTab(this.#tabs.has(MaskTab.code) ? MaskTab.code : CanvasTab.code);
					resolve(src, this);
				})
				.catch(() => {
					this.#setPreviousActiveTab();
					reject(src, this);
				})
			;
		});
	}

	reset(): this
	{
		this.#canvasMaster.reset();
		this.#setPreviousActiveTab();
		return this;
	}

	#selectFile()
	{
		// this.#canvasMaster.reset();
		this.setActiveTab(UploadTab.code);
		return this;
	}

	#snapAPicture()
	{
		// this.#canvasMaster.reset();
		this.setActiveTab(CameraTab.code);
		return this;
	}

	packBlobAndMask(): Promise
	{
		return new Promise((resolve, reject) => {
			this.#canvasMaster.getBlob()
				.then(({blob}) =>{
					const loader = CanvasLoader.getInstance();
					loader[hiddenCanvas] = loader[hiddenCanvas] ?? document.createElement('canvas');
					const canvas = loader[hiddenCanvas];
					canvas.width = blob.width;
					canvas.height = blob.height;

					canvas
						.getContext('2d')
						.drawImage(loader.getCanvas(), 0, 0);

					if (!this.#canvasMask)
					{
						return resolve({blob, canvas});
					}

					this
						.#canvasMask
						.applyAndPack(canvas)
						.then((maskedBlob: Blob, maskId: Number) => {
							resolve({blob, maskedBlob, maskId, canvas});
						})
						.catch(() => {
							resolve({blob, canvas});
						})
				})
				.catch((error) => {
					return reject(error);
				});
		});
	}

	packBlob(): Promise
	{
		return new Promise((resolve, reject) => {
			this.#canvasMaster.getBlob()
				.then(resolve)
				.catch(reject);
		});
	}

	isEmpty(): boolean
	{
		return this.#canvasMaster.isEmpty();
	}

	isModified(): boolean
	{
		return this.#canvasMaster.imageFrame.changed;
	}

	getCanvasEditor(): CanvasDefault
	{
		return this.#canvasMaster;
	}

	getCanvasZooming(): CanvasZooming
	{
		return this.#canvasZooming;
	}

	destroy()
	{

	}

	static createInstance(id, options): this
	{
		if (this.repo.has(id))
		{
			this.repo.get(id).destroy();
		}

		const editor = new this(options);
		if (document.querySelector('#' + id))
		{
			editor.ready(() => {
					editor.loadJSON(
						document.querySelector('#' + id)
							.getAttribute('data-bx-ui-avatar-editor-info')
					);
				})
			;
		}

		if (Type.isStringFilled(id))
		{
			this.repo.set(id, editor);
		}

		return editor;
	}

	static getInstanceById(id): ?Editor
	{
		if (this.repo.has(id))
		{
			return this.repo.get(id);
		}
		return null;
	}

	static getOrCreateInstanceById(id, options): this
	{
		return this.getInstanceById(id) || this.createInstance(...arguments)
	}
}
