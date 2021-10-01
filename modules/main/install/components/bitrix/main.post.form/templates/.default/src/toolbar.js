import {Type, Tag} from 'main.core';
import {Popup, PopupManager} from 'main.popup';
import {EventEmitter} from 'main.core.events';
import 'main.polyfill.intersectionobserver';

let intersectionObserver;
function observeIntersection(entity, callback)
{
	if (!intersectionObserver)
	{
		intersectionObserver = new IntersectionObserver(function(entries) {
			entries.forEach((entry) => {
				if (entry.isIntersecting)
				{
					intersectionObserver.unobserve(entry.target);
					const observedCallback = entry.target.observedCallback;
					delete entry.target.observedCallback;
					setTimeout(observedCallback);
				}
			});
		}, {
			threshold: 0
		});
	}
	entity.observedCallback = callback;

	intersectionObserver.observe(entity);
}

type Button = {
	ID: ?string,
	BODY: Element|string,
}
let justCounter = 0;
export default class Toolbar {
	constructor(eventObject, container) {
		this.container = container.querySelector('[data-bx-role="toolbar"]');

		this.adjustMorePosition = this.adjustMorePosition.bind(this);
		this.moreItem = container.querySelector('[data-bx-role="toolbar-item-more"]');
		this.moreItem.addEventListener('click', this.showSubmenu.bind(this));
		observeIntersection(this.container, this.adjustMorePosition);
		window.addEventListener('resize', this.adjustMorePosition);
	}

	insertAfter(button: Button, buttonId: ?String)
	{
		if (!Type.isElementNode(button['BODY']) && !Type.isStringFilled(button['BODY']))
		{
			return;
		}

		const item = Tag.render`<div class="main-post-form-toolbar-button" data-bx-role="toolbar-item"></div>`;

		if (Type.isElementNode(button['BODY']))
		{
			item.appendChild(button['BODY']);
		}
		else
		{
			item.innerHTML = button['BODY'];
		}

		if (button['ID'])
		{
			item.setAttribute('data-id', button['ID']);
		}

		if (buttonId !== null)
		{
			let found = false;
			let itemBefore = null;
			Array.from(
				this.container
					.querySelectorAll('[data-bx-role="toolbar-item"]')
			)
			.forEach((toolbarItem) => {
				if (found === true && itemBefore === null)
				{
					itemBefore = toolbarItem;
				}
				else if (found === false
					&& toolbarItem && toolbarItem.dataset
					&& toolbarItem.dataset.id === buttonId)
				{
					found = true;
				}
			});
			if (itemBefore)
			{
				itemBefore.parentNode.insertBefore(item, itemBefore);
			}
		}
		if (!item.parentNode)
		{
			this.container.appendChild(item);
		}
		this.adjustMorePosition();
	}

	getItems(): Array
	{
		return Array.from(this.container.querySelectorAll('[data-bx-role="toolbar-item"]'));
	}

	getVisibleItems(): Array
	{
		const visibleItems = [];
		Array.from(
			this.container
				.querySelectorAll('[data-bx-role="toolbar-item"]')
		)
		.forEach((item) => {
			if (item.offsetTop > this.container.clientHeight / 2)
			{
				visibleItems.push(item);
			}
		});

		return visibleItems;
	}

	getHiddenItems(): Array
	{
		const hiddenItems = [];
		Array.from(
			this.container
				.querySelectorAll('[data-bx-role="toolbar-item"]')
		)
		.forEach((item) => {
			if (item.offsetTop > 0)
			{
				hiddenItems.push(item);
			}
		});

		return hiddenItems;
	}

	adjustMorePosition()
	{
		const visibleItemsLength = this.getVisibleItems().length;

		if (visibleItemsLength <= 0 || visibleItemsLength >= this.getItems().length)
		{
			this.moreItem.style.display = 'none';
		}
		else
		{
			this.moreItem.style.display = '';
		}
	}

	getPopup(): Popup
	{
		if (!this.popup)
		{
			this.popup = PopupManager.create({
				id: 'main_post_form_toolbar_' + (justCounter++),
				className: 'main-post-form-toolbar-popup',
				cacheable: false,
				content: this.getPopupContainer(),
				closeByEsc: true,
				autoHide: true,
				angle: true,
				bindElement: this.moreItem,
				offsetTop: -5,
				offsetLeft: 5,
				events: {
					onClose: () => {
						Array.from(
							this.getPopupContainer()
								.querySelectorAll('[data-bx-role="toolbar-item"]')
						)
						.forEach((item) => {
							this.container.appendChild(item);
						});
						delete this.popup;
					}
				},
			});
		}
		return this.popup;
	}

	getPopupContainer(): Element
	{
		if (!this.popupContainer)
		{
			this.popupContainer = document.createElement('DIV');
		}
		return this.popupContainer;
	}

	showSubmenu()
	{
		const hiddenItems = this.getHiddenItems();
		if (hiddenItems.length <= 0)
		{
			return;
		}
		hiddenItems.forEach((item) => {
			this.getPopupContainer().appendChild(item);
		});
		this.getPopup().show();
	}
}
