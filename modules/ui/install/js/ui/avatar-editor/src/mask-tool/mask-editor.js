import {Tag, Text, Cache, Event, Loc} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Layout} from 'ui.sidepanel.layout';
import {Editor} from '../editor'
import Backend from "../backend";
import type {MaskType} from "../editor";
import UploadTab from '../tabs/upload-tab';
import 'ui.notification';
import {TagSelector, Dialog} from "ui.entity-selector";
import {Options} from '../options';

export default class MaskEditor extends EventEmitter
{
	static #instance: this;
	cache = new Cache.MemoryCache();
	#data: ?MaskType;
	#id: Number;
	#changesCount: Number = 0;

	constructor()
	{
		super();
		this.setEventNamespace([Options.eventNamespace, 'mask:editor'].join(':'));

		this.#id = [this.getEventNamespace(), (new Date()).getTime()].join(':');
	}

	getContentContainer(): HTMLElement
	{
		return this.cache.remember('content', () => {
			const res = Tag.render
				`<div class="ui-avatar-editor--scope">
						<ol class="ui-avatar-editor-list">
							<li class="ui-avatar-editor-list-item">
								<span class="ui-avatar-editor-list-item-num">1</span>
								${Loc
									.getMessage('UI_AVATAR_EDITOR_MASK_CREATOR_CONTENT_1_POINT')
									.replace(/#SIZE/gi, Loc.getMessage('UI_AVATAR_MASK_MAX_SIZE'))
								}
								<div class="ui-avatar-editor-list-link-box">
									<a href="${Loc.getMessage('UI_AVATAR_MASK_PATH_ARTICLE')}" class="ui-avatar-editor-list-link">${Loc.getMessage('JS_AVATAR_EDITOR_HOW_TO')}</a>
									<a href="/bitrix/js/ui/avatar-editor/dist/user_frame_template.zip" download class="ui-avatar-editor-list-link">${Loc.getMessage('UI_AVATAR_EDITOR_MASK_DOWNLOAD_TEMPLATE2')}</a>
								</div>
							</li>
							<li class="ui-avatar-editor-list-item">
								<span class="ui-avatar-editor-list-item-num">2</span>
								${Loc.getMessage('UI_AVATAR_EDITOR_MASK_CREATOR_CONTENT_2_POINT')}
								<div class="ui-avatar-editor-mask-file" data-bx-role="mask-file"></div>
							</li>
							<li class="ui-avatar-editor-list-item">${Loc.getMessage('UI_AVATAR_EDITOR_MASK_CREATOR_CONTENT_3_POINT')}
								<span class="ui-avatar-editor-list-item-num">3</span>
								<div class="ui-form">
									<div class="ui-form-row">
										<div class="ui-form-label">
											<div class="ui-ctl-label-text">${Loc.getMessage('JS_AVATAR_EDITOR_TITLE')}</div>
										</div>
										<div class="ui-form-content">
											<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
												<input data-bx-role="title" type="text" class="ui-ctl-element" placeholder="${Loc.getMessage('JS_AVATAR_EDITOR_PLACEHOLDER')}">
											</div>
										</div>
									</div>
									<div class="ui-form-row">
										<div class="ui-form-label">
											<div class="ui-ctl-label-text">${Loc.getMessage('JS_AVATAR_EDITOR_ACCESS')}</div>
										</div>
										<div class="ui-form-content">
											<div class="ui-ctl ui-ctl-textbox ui-ctl-w100" data-bx-role="access-container"></div>
										</div>
									</div>
								</div>
							</li>
						</ol>
					</div>`;
			res
				.querySelector('[data-bx-role="mask-file"]')
				.appendChild(this.#getEditor().getContainer());
			this.#getEditor().getCanvasZooming().setDefaultValue(0.5).reset();
			return res;
		});
	}

	#getEditor(): Editor
	{
		return this.cache.remember('editor', () => {
			const res = new Editor({
				enableCamera: false,
				enableUpload: true,
				uploadTabOptions: {fileAccept: 'image/png'},
				enableMask: false,
			});
			res.subscribe('onChange', ({data}) => {
				this.#changesCount++;
			});
			return res;
		});
	}

	#initAccessSelector(): TagSelector
	{
		return this.cache.remember('TagSelector', () => {
			const handler = ({target}) => {
				if (target instanceof Dialog)
				{
					this.#data.accessCode = target.getSelectedItems()
						.map((item) => {
							return [item.entityId, item.id];
						})
					;
				}
			};

			const selector = new top.BX.UI.EntitySelector.TagSelector({
				id: this.constructor.name,
				dialogOptions: {
					id: this.constructor.name,
					context: null,
					preselectedItems: this.#data.accessCode,
					events: {
						'Item:onSelect': handler,
						'Item:onDeselect': handler
					},
					entities: [
						{
							id: 'meta-user',
							options: {
								'all-users': {
									title: 'All users',
									allowView: true
								}
							}
						},
						{
							id: 'user',
							options: {
								emailUsers: false,
								inviteGuestLink: false,
								myEmailUsers: false
							}
						},
						{
							id: 'department',
							options: {
								selectMode: 'usersAndDepartments',
								allowFlatDepartments: false,
							}
						}
					]
				}
			});
			selector.renderTo(
				this.getContentContainer()
					.querySelector('[data-bx-role="access-container"]')
			);
			return selector;
		})
	}

	isModified(): boolean
	{
		return this.#changesCount > 0;
	}

	openNew()
	{
		this
			.#showSlider()
			.then(() => {
				this.#changesCount = 0;
				this.#data = {
					id: null,
					title: '',
					src: null,
					accessCode: [['meta-user', 'all-users']]
				};
				this.#getEditor().reset();
				this.getContentContainer()
					.querySelector('[data-bx-role="title"]').value = '';
				this.#initAccessSelector();
			});
	}

	openSaved(data: MaskType)
	{
		this.#data = {
			id: data.id,
			title: data.title,
			src: data.src,
			accessCode: data.accessCode || null
		};

		this.getContentContainer().querySelector('[data-bx-role="title"]').value = Text.encode(data.title);
		this
			.#showSlider()
			.then(() => {
				this.#getEditor()
					.loadSrc(data.src)
					.then(() => {
						this.#changesCount = 0;
						if (!data.accessCode)
						{
							Backend.getMaskAccessCode(data.id)
								.then(({data: {accessCode}}) => {
									this.emit('maskAccessCodeHasGot', accessCode);
									this.#data.accessCode = accessCode
									this.#initAccessSelector();
								})
							;
						}
						else
						{
							this.#data.accessCode = Array.from(data.accessCode);
							this.#initAccessSelector();
						}
					})
				;
			})
		;
	}

	checkOpened(): Promise
	{
		return new Promise((resolve, reject) => {
			let isSuccess = true;
			if (this.#getEditor().isEmpty())
			{
				this.#getEditor()
					.getTab(UploadTab.code)
					.showError({message: Loc.getMessage('JS_AVATAR_EDITOR_ERROR_IMAGE_IS_NOT_CHOSEN')});
				isSuccess = false;
			}
			const title = this.getContentContainer()
				.querySelector('[data-bx-role="title"]').value.trim();
			if (title.length <= 0)
			{
				this.getContentContainer()
					.querySelector('[data-bx-role="title"]').style.border = '3px solid red';
				isSuccess = false;
			}
			if (isSuccess)
			{
				return resolve();
			}
			return reject();
		});
	}

	saveOpened(): Promise
	{
		return new Promise((resolve, reject) => {
			const cb = ({blob}) => {
				Backend
					.saveMask({
						id: this.#data.id,
						title: this.getContentContainer()
							.querySelector('[data-bx-role="title"]').value,
						accessCode: this.#data.accessCode
					}, blob)
					.then(resolve)
					.catch(reject)
				;
			}
			if (!this.isModified())
			{
				return cb({blob: null});
			}
			return this.#getEditor()
				.packBlob()
				.then(cb);
		})
	}

	destroy()
	{
		this.#getEditor().reset();
		this.getContentContainer()
			.querySelector('[data-bx-role="title"]').value = '';
		this.#data = null;
		this.cache.storage.clear();
	}

	#showSlider(): Promise
	{
		return new Promise((resolve, reject) => {
				BX.SidePanel.Instance.open(this.#id, {
					width: 800,
					cacheable: false,
					allowChangeHistory: false,
					events: {
						onCloseByEsc: (event) => {
							event.denyAction();
						},
						onOpen: () => {
							setTimeout(() => {
								this.emit('onOpen', {});
							}, 0);
							resolve();
						},
						onCloseComplete: this.destroy.bind(this),
					},
					contentCallback: (slider) => {
						return Layout.createContent({
							extensions: [],
							title: Loc.getMessage('UI_AVATAR_EDITOR_MASK_CREATOR_TITLE'),
							content: () => {
								const res = this.getContentContainer();
								setTimeout(() => {
									this.#getEditor().getCanvasZooming().setDefaultValue(0.5).reset();
								}, 0);
								return res;
							},
							buttons: ({CancelButton, SaveButton}) => {
								return [
									new SaveButton({
										onclick: (button) => {
											button.setWaiting(true);
											this
												.checkOpened()
												.then(this.saveOpened.bind(this))
												.then(({data}) => {
													this.emit('onSave', {id: this.#data.id, data: data});
													button.setWaiting(false);
													slider.close();
												})
												.catch((error) => {
													if (error)
													{
														BX.UI.Notification.Center.notify({
															content: ['Error is here', ...arguments].join('-')
														});
													}
													button.setWaiting(false);
												});
										}
									}),
									new CancelButton({
										onclick: () => {
											slider.close();
										}
									})
								];
							}
						});
					},
					label: {
						text: Loc.getMessage('UI_AVATAR_EDITOR_MASK_CREATOR_LABEL'),
					}
				});
			}
		)
	}

	/**
	 * Emits specified event with specified event object
	 * @param {string} eventName
	 * @param {BaseEvent | any} event
	 * @return {this}
	 */
	emit(eventName: string, event?: BaseEvent): this
	{
		BX.SidePanel.Instance.postMessageAll(this.#id, eventName, event);
		return  this;
	}

	static #subscribedToASliderEvents = false

	static subscribe(eventName: string, listener: (event: BaseEvent) => void): void
	{
		EventEmitter.subscribe(([Options.eventNamespace, 'mask:editor', eventName].join(':')), listener);
		if (this.#subscribedToASliderEvents)
		{
			return;
		}
		this.#subscribedToASliderEvents = true;
		EventEmitter.subscribe('SidePanel.Slider:onMessage', ({data: [BXSidePanelMessageEvent]}) => {
			if (BXSidePanelMessageEvent.getSender().getUrl().indexOf([Options.eventNamespace, 'mask:editor'].join(':')) === 0)
			{
				EventEmitter.emit(
					[Options.eventNamespace, 'mask:editor', BXSidePanelMessageEvent.getEventId()].join(':'),
					BXSidePanelMessageEvent.getData()
				);
			}
		});
	}

	static getInstance(): ?this
	{
		if (this.#instance)
		{
			return this.#instance;
		}

		if (window === window.top)
		{
			if (!this.#instance)
			{
				this.#instance = new this();
			}
			return this.#instance;
		}
		return null;
	}

	static getPromiseWithInstance(): Promise
	{
		if (this.#instance || this.getInstance())
		{
			return new Promise((resolve) => {
				resolve(this.#instance);
			});
		}

		return new Promise((resolve) => {
			top
				.BX
				.Runtime
				.loadExtension(['ui.avatar-editor'])
				.then(() => {
					this.#instance = top.BX.UI.AvatarEditor.MaskEditor.getInstance();
					resolve(this.#instance);
				});
		});
	}
}
