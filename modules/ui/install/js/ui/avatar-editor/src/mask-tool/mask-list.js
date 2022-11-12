import {Loc, Tag, Text, Cache, Dom} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {MaskItem} from './mask-item';
import {MaskType} from '../editor';
import Backend from "../backend";
import {Button, ButtonSize} from 'ui.buttons';
import MaskEditor from "./mask-editor";

export class MaskList extends EventEmitter
{
	static repoList: WeakMap<HTMLElement, MaskList> = new WeakMap();
	static paginationStates = {ready: 0, inprogress: 1, finished: 3};
	static regularPageSize = 9;
	static shortPageSize = 3;

	#container: Element;

	cache = new Cache.MemoryCache();

	#state: String = this.constructor.paginationStates['ready'];
	#pageSize: Number = 10;
	#pageNumber: Number = 1;

	constructor({initialPageSize, items})
	{
		super();
		this.setEventNamespace('Main.Avatar.Editor');

		this.#container = this.getContainer()
			.querySelector('[data-bx-role="avatar-mask-list-container"]');

		this.#pageSize = this.constructor.regularPageSize;
		this.loadItems(items);

		this.setReady();
	}

	static getTemplate(): String
	{
		return `<div>
				<div class="ui-avatar-editor--scope" data-bx-role="avatar-mask-list-container">
					<section class="ui-avatar-editor__mask-block-list-container" id="mask_group">
						<h3 class="ui-avatar-editor__mask-title" data-bx-role="group_title" data-bx-group-id="#GROUP_ID#">#GROUP_TITLE#</h3>
						<ul class="ui-avatar-editor__mask-block-mask-box" data-bx-role="group_body" data-bx-group-id="#GROUP_ID#">
							<li class="ui-avatar-editor__mask-block-mask-element" 
								id="mask_item"
								data-bx-role="mask_item"
								title="#MASK_TITLE# \n #MASK_SUBTITLE#"
								data-bx-id="#MASK_ID#">
								<div data-bx-role="mask-thumb" class="ui-avatar-editor__mask-block-mask-image" style="background-image: url('#MASK_SRC#'); "/></div>
								<div class="ui-avatar-editor__mask-block-mask-name">#MASK_TITLE#</div>
								<div class="ui-avatar-editor__mask-block-mask-subname">#MASK_SUBTITLE#</div>
								<div class="ui-avatar-editor__mask-block-mask-menu" data-bx-role="mask-item-menu-pointer"></div>
							</li>
						</ul>
					</section>
				</div>
				<nav class="ui-avatar-editor-pagination" data-bx-role="avatar-mask-list-pagination"></nav>
			</div>`
		;
	}

	static setByNode(node, object: MaskList)
	{
		return this.repoList.set(node, object);
	}

	static getByNode(node): ?MaskList
	{
		return this.repoList.get(node);
	}

	#getTemplateGroup(): String
	{
		return this.cache.remember('templateGroup', () => {
			const maskGroup = Tag.render`${this.constructor.getTemplate()}`.querySelector('#mask_group');
			const maskItem = maskGroup.querySelector('#mask_item');
			maskItem.parentNode.removeChild(maskItem);
			maskGroup.removeAttribute('id');
			return maskGroup.outerHTML.trim();
		});
	}

	#getTemplateItem(): String
	{
		return this.cache.remember('templateItem', () => {
			const maskItem = Tag.render`${this.constructor.getTemplate()}`.querySelector('#mask_item');
			maskItem.removeAttribute('id');
			return maskItem.outerHTML.trim();
		});
	}

	setPageSize(pageSize: number): MaskList
	{
		this.#pageSize = pageSize;
		return this;
	}

	getContainer(): HTMLElement
	{
		return this.cache.remember('container', () => {
			const res =  Tag.render`${this.constructor.getTemplate()}`;
			Dom.remove(res.querySelector('#mask_item'))
			Dom.remove(res.querySelector('#mask_group'))
			return res;
		});
	}

	isReady()
	{
		return (this.#state === this.constructor.paginationStates.ready);
	}

	setReady()
	{
		this.getMoreButton().setWaiting(false);
		this.#state = this.constructor.paginationStates.ready;
	}

	setBusy()
	{
		this.getMoreButton().setWaiting(true);
		this.#state = this.constructor.paginationStates.inprogress;
	}

	setFinished()
	{
		this.getMoreButton().setDisabled(true);
		this.#state = this.constructor.paginationStates.finished;
		Dom.remove(this.getContainer().querySelector('[data-bx-role="avatar-mask-list-pagination"]'));
	}

	getMoreButton(): Button
	{
		return this.cache.remember('moreButton', () => {
			const butt = new Button({
				text: Loc.getMessage('UI_AVATAR_EDITOR_MASK_LIST_PAGINATION'),
				baseClass: 'ui-btn ui-btn-light-border',
				size: ButtonSize.SMALL,
				noCaps: true,
				round: true,
				onclick: this.load.bind(this)
			});
			butt.renderTo(this.getContainer().querySelector('[data-bx-role="avatar-mask-list-pagination"]'));
			return butt;
		});
	}

	load(): void
	{
		if (!this.isReady())
		{
			return;
		}
		this.setBusy();

		Backend.getMaskList(
			this.constructor.name.replace('Mask', ''),
			{page: ++this.#pageNumber, size: this.#pageSize})
			.then(this.loadItems.bind(this))
			.catch(this.terminate.bind(this))
		;
	}

	loadItems(items)
	{
		this.renderItems(items);
		this.finish(items);
	}

	renderItems(data)
	{
		let maxCount = this.#pageSize;
		Object
			.values(data)
			.forEach(({id, title, items}) =>
			{
				if (maxCount <= 0)
				{
					return;
				}
				items = Object.values(items).slice(0, maxCount);
				maxCount -= items.length;

				id = id || '0';
				if (!this.#container.querySelector(`[data-bx-group-id="${id}"][data-bx-role="group_body"]`))
				{
					const groupText = this.#getTemplateGroup()
						.replace(/#GROUP_ID#/gi, Text.encode(id))
						.replace(/#GROUP_TITLE#/gi, Text.encode(title || ''))
					this.#container.appendChild(Tag.render`${groupText}`);
				}
				const badgeContainer = this.#container.querySelector(`[data-bx-group-id="${id}"][data-bx-role="group_body"]`);
				items.forEach((item: MaskType) => {
					const maskItem = new MaskItem(item, this.#getTemplateItem());
					badgeContainer.appendChild(maskItem.getContainer());
				});
			})
		;
	}

	renderItemsReverse(data)
	{
		Object
			.values(data)
			.forEach(({id, title, items}) =>
			{
				id = id || '0';
				if (!this.#container.querySelector(`[data-bx-group-id="${id}"][data-bx-role="group_body"]`))
				{
					const groupText = this.#getTemplateGroup()
						.replace(/#GROUP_ID#/gi, Text.encode(id))
						.replace(/#GROUP_TITLE#/gi, Text.encode(title || ''));
					Dom.prepend(Tag.render`${groupText}`, this.#container);
				}
				const badgeContainer = this.#container.querySelector(`[data-bx-group-id="${id}"][data-bx-role="group_body"]`);
				items.forEach((item: MaskType) => {
					const maskItem = new MaskItem(item, this.#getTemplateItem());
					Dom.prepend(maskItem.getContainer(), badgeContainer);
				});
			})
		;
	}

	finish(data)
	{
		let thisPageItemCount = 0;
		data.forEach(({items}) =>
		{
			thisPageItemCount += items.length;
		});
		if (thisPageItemCount >= this.#pageSize)
		{
			this.setReady();
		}
		else
		{
			this.setFinished();
		}
	}

	terminate(data)
	{
		let errors = [];
		if (data instanceof Error)
		{
			console.log('data: ', data);

			errors.push(data);
		}
		else if (data['errors'])
		{
			errors = data.errors;
		}
		else
		{
			errors.push({message: 'Some error'});
		}
		this.setFinished();
		errors.forEach(({code, message}) => {
			this.#container.appendChild(
				Tag.render`<pre>${Text.encode(message)}</pre>`
			)
		});
	}
}

export class MaskRecentlyUsedList extends MaskList{}

export class MaskSystemList extends MaskList {}

export class MaskUserList extends MaskList {
	constructor()
	{
		super(...arguments);
		MaskEditor.subscribe(
			'onSave',
			(event: BaseEvent) => {
				try {
					const {data: {id, data}} = event;
					if (id === null)
					{
						this.renderItemsReverse({'doesNotMatter': {items: [data]}})
					}
				}
				catch(e)
				{
					console.log(e.message);
				}
			})
		;
	}
}

export class MaskSharedList extends MaskList {}
