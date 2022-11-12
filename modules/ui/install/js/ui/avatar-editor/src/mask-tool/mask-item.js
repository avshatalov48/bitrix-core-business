import {Dom, Tag, Text, Cache, Event, Loc} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events'
import {MenuManager, Popup} from 'main.popup';
import {MaskType} from '../editor';
import MaskEditor from './mask-editor';

export class MaskItem extends EventEmitter
{
	static #repo: WeakMap<HTMLElement, MaskItem> = new WeakMap()
	cache = new Cache.MemoryCache();
	data: MaskType;
	#template: String;

	constructor(data: MaskType, template: String)
	{
		super();
		this.setEventNamespace('Main.Avatar.Editor');
		this.data = data;
		this.#template = template;

		MaskEditor.subscribe(
			'onSave',
			(event: BaseEvent) => {
				try {
					const {data: {id, data}} = event;
					if (String(this.data.id) === String(id)) {
						this.update(data);
					}
				}
				catch(e)
				{
					console.log(e.message);
				}
			})
		;
	}

	getContainer(): HTMLElement
	{
		return this.cache.remember('container', () => {
			const itemText = this.#template
				.replace(/#MASK_ID#/gi, Text.encode(this.data.id))
				.replace(/#MASK_TITLE#/gi, Text.encode(this.data.title || ''))
				.replace(/#MASK_SUBTITLE#/gi, Text.encode(this.data.description || ''))
				.replace(/#MASK_SRC#/gi, Text.encode(this.data.src));

			const res = Tag.render`${itemText}`;
			Event.bind(res.querySelector('[data-bx-role="mask-item-menu-pointer"]'), 'click', this.onClickMenuPointer.bind(this));

			this.constructor.#repo.set(res, this);
			Event.bind(res, 'click', this.setActive.bind(this));
			return res;
		});
	}

	getData(): MaskType
	{
		return Object.assign({}, this.data);
	}

	getId(): string
	{
		return this.data.id;
	}

	update(data: MaskType)
	{
		this.data.title = data.title;
		this.data.src = data.src;
		this.data.description = data.description;
		this.data.accessCode = data.accessCode;
		this.data.editable = data.editable;
		const oldContainer = this.getContainer();
		this.cache.delete('container');
		const newContainer = this.getContainer();
		Dom.replace(oldContainer, newContainer);
	}

	setActive()
	{
		this.emit('onClickMask');
	}

	onClickMenuPointer(event)
	{
		event.preventDefault();
		event.stopPropagation();
		const thisPopupId = 'mask-item-menu-context-' + this.data.id;

		const thisPopup = (MenuManager.create(
			thisPopupId,
			event.target,
			[
				{
					href: this.data.src,
					dataset: {
						id: 'download'
					},
					text: Loc.getMessage('JS_AVATAR_EDITOR_DOWNLOAD_BUTTON'),
					onclick: (event, item) => {
						item.getMenuWindow().close();
					}
				},
				this.data.editable ?
				{
					text: Loc.getMessage('JS_AVATAR_EDITOR_EDIT_BUTTON'),
					onclick: (event, item) => {
						this.emit('onClickEditMask');
						item.getMenuWindow().close();
					}
				} : null,
				this.data.editable ?
				{
					text: Loc.getMessage('JS_AVATAR_EDITOR_DELETE_BUTTON'),
					onclick:(event, item) => {
						this.emit('onClickDeleteMask');
						item.getMenuWindow().close();
					}
				} : null,
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
				events: {
					onFirstShow: ({compatData: [popup: Popup]}) => {
						popup.getContentContainer().querySelector('[data-id="download"]').setAttribute('download', '');
					}
				}
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

	static getByNode(node): ?MaskItem
	{
		return this.#repo.get(node);
	}
}

