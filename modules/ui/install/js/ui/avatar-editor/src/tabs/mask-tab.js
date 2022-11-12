import {Dom, Tag, Loc, Type, Event} from 'main.core';
import DefaultTab from './default-tab';
import {MaskList, MaskRecentlyUsedList, MaskSystemList, MaskUserList, MaskSharedList} from '../mask-tool/mask-list';
import MaskEditor from '../mask-tool/mask-editor';
import {BaseEvent, EventEmitter} from "main.core.events";
import {MaskItem} from "../mask-tool/mask-item";
import {MaskType} from "../editor";
import Backend from "../backend";
import {MenuManager} from "main.popup";
import {MessageBox, MessageBoxButtons} from 'ui.dialogs.messagebox';
import {Loader} from "main.loader";

export default class MaskTab extends DefaultTab
{
	static maxCount = 5;
	static priority = 4;
	#ready: boolean = false;
	#callbacks: Array = [];

	constructor() {
		super();
		this.badges = null;
		this.activeId =  null;
		this.mask = this.mask.bind(this)
		this.subscribeOnce('onActive', this.initialize.bind(this));
	}

	getHeader(): ?String
	{
		return Loc.getMessage('JS_AVATAR_EDITOR_MASKS');
	}

	getBody(): String| Element
	{
		return this.cache.remember('body', () => {
			return Tag.render
			`<div class="ui-avatar-editor__mask-block-container">
				<div class="ui-avatar-editor__mask-block-content">
					<div data-bx-role="semantic-container" data-bx-id="recently-used" style="display: none;">
						<h3 class="ui-avatar-editor__mask-title">${Loc.getMessage('JS_AVATAR_EDITOR_RECENT_MASKS')}</h3>
						<div data-bx-role="list-container" data-bx-id="recently-used"></div>
					</div>
					<div data-bx-role="list-container" data-bx-id="system"></div>
					<div data-bx-role="semantic-container" data-bx-id="shared" style="display: none">
						<h3 class="ui-avatar-editor__mask-title">${Loc.getMessage('UI_AVATAR_EDITOR_MASK_LIST_SHARED')}</h3>
						<div data-bx-role="list-container" data-bx-id="shared">
							<a class="ui-btn ui-btn-lg ui-btn-link ui-btn-wait ui-btn-no-caps ui-btn-icon-add">...</a>
						</div>
					</div>
					<div data-bx-role="semantic-container" data-bx-id="my-own">
						<h3 class="ui-avatar-editor__mask-title" data-bx-id="rest-market-export-menu">
							${Loc.getMessage('UI_AVATAR_EDITOR_MASK_LIST_MY_OWN')}
							<div data-bx-id="rest-market-export-menu-pointer" class="ui-avatar-editor__menu-more"></div>
						</h3>
						<div data-bx-role="list-container" data-bx-id="my-own"></div>

						<a href="#" class="ui-avatar-editor__mask-create-box" data-bx-role="semantic-container" data-bx-id="rest-market" style="display: none;">
							<div class="ui-avatar-editor__mask-btn-load">
								<div class="ui-avatar-editor__mask-btn-load-icon"></div>
								${Loc.getMessage('JS_AVATAR_EDITOR_LOAD_FROM_MARKET')}
								<div class="ui-avatar-editor__mask-btn-load-cloud"></div>
							</div>
						</a>

						<div class="ui-avatar-editor__mask-create-box">
							<div class="ui-avatar-editor__mask-btn-add" data-bx-id="avatar-mask-list-own-create">${Loc.getMessage('UI_AVATAR_EDITOR_MASK_ADD_MY_OWN')}</div>
							<a href="/bitrix/js/ui/avatar-editor/dist/user_frame_template.zip" download class="ui-avatar-editor__mask-link">${Loc.getMessage('UI_AVATAR_EDITOR_MASK_DOWNLOAD_TEMPLATE1')}</a>
						</div>
					</div>
				</div>
			</div>`;
		});
	}

	initialize()
	{
		Backend
			.getMaskInitialInfo({size: MaskList.regularPageSize, recentlyUsedListSize: MaskList.shortPageSize})
			.then(this.initializeData.bind(this))
			.catch((error) => {
				console.log('errors: ', error);
			});
	}

	initializeData({recentlyUsedItems, systemItems, myOwnItems, sharedItems, restMarketInfo})
	{
		const body = this.getBody();
		if (Loc.getMessage('USER_ID') > 0)
		{
			Event.bind(
				body.querySelector('[data-bx-id="avatar-mask-list-own-create"]'),
				'click',
				this.onClickCreateMask.bind(this)
			);

			EventEmitter.subscribe(this.getEventNamespace() + ':' + 'onClickEditMask', this.onClickEditMask.bind(this));
			EventEmitter.subscribe(this.getEventNamespace() + ':' + 'onClickDeleteMask', this.onClickDeleteMask.bind(this));
		}
		if (restMarketInfo['available'] === 'Y')
		{
			const menuItem = body.querySelector('[data-bx-id="rest-market-export-menu"]');
			Dom.addClass(menuItem, '--menuable');
			Event.bind(
				menuItem.querySelector('[data-bx-id="rest-market-export-menu-pointer"]'),
				'click',
				(event) => {
					this.onClickOwnMaskMenu(event, restMarketInfo);
				})
			;
			const marketLink = body.querySelector('[data-bx-role="semantic-container"][data-bx-id="rest-market"]');
			marketLink.style.display = '';
			marketLink.href = restMarketInfo['marketUrl'];
		}

		[
			[MaskRecentlyUsedList, recentlyUsedItems, body.querySelector('[data-bx-role="list-container"][data-bx-id="recently-used"]')],
			[MaskSystemList, systemItems, body.querySelector('[data-bx-role="list-container"][data-bx-id="system"]')],
			[MaskUserList, myOwnItems, body.querySelector('[data-bx-role="list-container"][data-bx-id="my-own"]')],
			[MaskSharedList, sharedItems, body.querySelector('[data-bx-role="list-container"][data-bx-id="shared"]')],
		].forEach(([className, items, container]) => {
			items = items || [];
			if (items.length > 0)
			{
				const semanticContainer = container.closest('[data-bx-role="semantic-container"]');
				if (semanticContainer)
				{
					semanticContainer.style.display = '';
				}
			}

			container.innerHTML = '';
			/**
			 * @typedef {MaskList} list
			 */
			const list = new className({
				initialPageSize: this.constructor.initialPageSize,
				items: items
			});
			container.appendChild(list.getContainer());
			MaskList.setByNode(container, list);
		});
		EventEmitter.subscribe(
			this.getEventNamespace() + ':' + 'onClickMask',
			({target: maskItem}: BaseEvent) => {
				/**
				 * @typedef {MaskItem} maskItem
				 */
				if (this.getBody().contains(maskItem.getContainer()))
				{
					if (this.activeId === maskItem.getId())
					{
						this.unmask();
					}
					else
					{
						this.mask(maskItem.getData());
					}
				}
			})
		;

		this.#ready = true;
		this.fulfillReadyCallbacks();
	}

	onReady(callback: Function)
	{
		this.#callbacks.push(callback);
		if (this.#ready)
		{
			this.fulfillReadyCallbacks();
		}
	}

	#fulfillReadyCallbacksTimeout: ?Number

	fulfillReadyCallbacks()
	{
		if (this.#fulfillReadyCallbacksTimeout > 0)
		{
			return;
		}

		const callback = this.#callbacks.shift();
		if (callback)
		{
			if (this.#callbacks.length > 0)
			{
				this.#fulfillReadyCallbacksTimeout = setTimeout(() => {
					this.#fulfillReadyCallbacksTimeout = 0;
					this.fulfillReadyCallbacks();
				}, 10);
			}
			callback.call(this);
		}
	}

	unmask()
	{
		if (this.activeId !== null)
		{
			let foundAtLeastOneNode;
			this
				.getBody()
				.querySelectorAll(`[data-bx-role="mask_item"][data-bx-id="${this.activeId}"]`)
				.forEach((node) => {
					foundAtLeastOneNode = node;
					Dom.removeClass(node, '--active');
				})
			;
			if (foundAtLeastOneNode)
			{
				this.emit('onUnsetMask', this.activeId);
			}
		}
		this.activeId = null;
	}

	maskById(id)
	{
		this.onReady(() => {
			const maskItem = MaskItem.getByNode(
				this
					.getBody()
					.querySelector(`[data-bx-role="mask_item"][data-bx-id="${id}"]`)
			);
			if (maskItem instanceof MaskItem)
			{
				maskItem.setActive();
			}
		})
	}

	mask({id, src, thumb}: MaskType): void
	{
		if (this.activeId !== id && Type.isStringFilled(id))
		{
			this.unmask();
			let foundAtLeastOneNode;
			this
				.getBody()
				.querySelectorAll(`[data-bx-role="mask_item"][data-bx-id="${id}"]`)
				.forEach((node) => {
					foundAtLeastOneNode = node;
					Dom.addClass(node, ' --active');
				})
			;
			if (foundAtLeastOneNode)
			{
				this.activeId = id;
				this.emit('onSetMask', {
					id: id,
					src: src,
					thumb: thumb || src
				});
			}
		}
	}

	onClickCreateMask(event: BaseEvent)
	{
		event.stopImmediatePropagation();
		MaskEditor
			.getPromiseWithInstance()
			.then((maskEditor: MaskEditor) => {
				maskEditor.openNew();
			});
	}

	onClickEditMask(event: BaseEvent)
	{
		/* @var MaskItem maskItem */
		const maskItem = event.getTarget();
		if (this.getBody().contains(maskItem.getContainer()))
		{
			event.stopImmediatePropagation();
			MaskEditor
				.getPromiseWithInstance()
				.then((maskEditor: MaskEditor) => {
					maskEditor.openSaved(Object.assign({}, maskItem.getData()));
				});
		}
	}

	#deleteMaskVisually(maskItem: MaskItem)
	{
		/* @var MaskItem target */
		this
			.getBody()
			.querySelectorAll(`[data-bx-role="mask_item"][data-bx-id="${maskItem.getId()}"]`)
			.forEach((node) => {
				// node.style.display = 'none';
				Dom.remove(node);
			})
		;
		const listContainer = this.getBodyContainer().querySelector('[data-bx-role="list-container"][data-bx-id="recently-used"]');
		if (listContainer.childNodes.length <= 1)
		{
			const semanticContainer = listContainer.closest('[data-bx-role="semantic-container"]');
			if (semanticContainer)
			{
				semanticContainer.style.display = 'none';
			}
		}
		if (String(this.activeId) === String(maskItem.getId()))
		{
			this.unmask();
		}
	}

	onClickDeleteMask({target}: BaseEvent)
	{
		if (this.getBody().contains(target.getContainer()))
		{
			/* @var MaskItem target */
			this.#deleteMaskVisually(target);
			Backend
				.deleteMask(target.getId())
				.then(() => {
					this
						.getBody()
						.querySelectorAll(`[data-bx-role="mask_item"][data-bx-id="${target.getId()}"]`)
						.forEach((node) => {
							Dom.remove(node);
						})
					;
				})
				.catch(({errors}) => {
					BX.UI.Notification.Center.notify({
						content: [Loc.getMessage('JS_AVATAR_EDITOR_ERROR'), ...(errors.map(({message, code}) => {return message||code;}))].join(' ')
					});
				})
			;
		}
	}

	onClickOwnMaskMenu(event: BaseEvent, urls)
	{
		const thisPopupId = 'mask-item-menu-context-own-masks';
		const isFilled = !!this.getBody()
			.querySelector('[data-bx-role="list-container"][data-bx-id="my-own"]')
			.querySelector(`[data-bx-role="mask_item"]`);

		const thisPopup = (MenuManager.create(
			thisPopupId,
			event.target,
			[
				isFilled && Type.isStringFilled(urls.exportUrl) ? {
					href: urls.exportUrl,
					text: Loc.getMessage('JS_AVATAR_EDITOR_EXPORT_BUTTON'),
					onclick: (event, item) => {
						this.emit('onClickExport');
						item.getMenuWindow().close();
					}
				} : null,
				isFilled ? {
					text: Loc.getMessage('JS_AVATAR_EDITOR_CLEAN_BUTTON'),
					onclick: (event, item) => {
						item.getMenuWindow().close();
						(new MessageBox({
							message: Loc.getMessage('JS_AVATAR_EDITOR_CLEAN_NOTIFICATION'),
							title: Loc.getMessage('JS_AVATAR_EDITOR_CLEAN_NOTIFICATION_TITLE'),
							buttons: MessageBoxButtons.OK_CANCEL,
							okCaption: 'Ok',
							onOk: (messageBox) => {
								messageBox.close();
								this.cleanUp();
							},
						})).show();
					}
				} : null,
				{
					href: urls.importUrl,
					text: Loc.getMessage('JS_AVATAR_EDITOR_IMPORT_BUTTON'),
					onclick:(event, item) => {
						this.emit('onClickImport');
						item.getMenuWindow().close();
					}
				},
			],
			{
				closeByEsc: true,
				autoHide: true,
				offsetTop: 0,
				offsetLeft: 15,
				angle: true,
				cacheable: false,
				targetContainer: event.target.closest('.ui-avatar-editor__mask-block-container'),
				className: 'popup-window-content-frame-item-menu',
			}
		));
		thisPopup.show();
		EventEmitter.subscribeOnce(
			thisPopup.getPopupWindow().getEventNamespace() + ':onBeforeShow',
			() => {
				thisPopup.close();
			})
		;
		return false;
	}

	cleanUp() //delete all my masks
	{
		const container = this.getBody()
			.querySelector('[data-bx-role="list-container"][data-bx-id="my-own"]');

		const loader = new Loader({
			target: container,
			color: 'rgba(82, 92, 105, 0.9)'
		});
		loader.show();
		Backend
			.cleanUp()
			.then(() => {
				MaskList.getByNode(container).setFinished();
				container
					.querySelectorAll(`[data-bx-role="mask_item"]`)
					.forEach((node) => {
						this.#deleteMaskVisually(MaskItem.getByNode(node));
					})
				;
				loader.hide();
			});
	}

	//TODO delete this string and its using after testing
	static isAvailable(): boolean
	{
		return Loc.getMessage('UI_AVATAR_MASK_IS_AVAILABLE') === true;
	}

	static get code()
	{
		return 'mask';
	}
}
