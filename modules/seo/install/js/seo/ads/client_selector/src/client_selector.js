import { Dom, Event, Loc, Tag, Type } from "main.core";
import { Loader } from "main.loader";
import "ui.design-tokens";
import "./client_selector.css";

export class ClientSelector
{
	constructor(container, params)
	{
		this.container = container;
		this.canAddItems = !!params.canAddItems;
		this.canUnSelectItem = !!params.canUnSelectItem;
		this.onNewItemCallback = params.events && Type.isFunction(params.events.onNewItem) ? params.events.onNewItem : null;
		this.onSelectItemCallback = params.events && Type.isFunction(params.events.onSelectItem) ? params.events.onSelectItem : null;
		this.onUnSelectItemCallback = params.events && Type.isFunction(params.events.onUnSelectItem) ? params.events.onUnSelectItem : null;
		this.onRemoveItemCallback = params.events && Type.isFunction(params.events.onRemoveItem) ? params.events.onRemoveItem : null;
		this.init();
		this.setSelected(params.selected);
		this.setItems(params.items ? params.items : {});
		this.enabled = true;
		this.loader = new Loader({size: 20});
	}

	setSelected(item)
	{
		this.selected = item;
		this.closeMenu();
		this.updateClientHtml();
	}
	setItems(items)
	{
		this.closeMenu();
		this.items = items;
	}

	init()
	{
		Dom.append(this.getHtml(), this.container);
		this.updateClientHtml();
		Event.bind(this.container, 'click', this.onContainerClick.bind(this));
	}

	enable()
	{
		this.enabled = true;
		let selector = this.getSelectorNode();
		selector ? selector.classList.remove('seo-ads-client-selector-loading') : false;
		this.loader.hide();
	}

	disable()
	{
		this.enabled = false;
		let selector = this.getSelectorNode();
		selector ? selector.classList.add('seo-ads-client-selector-loading') : false;
		this.loader.hide();
		if (selector)
		{
			selector.classList.add('seo-ads-client-selector-loading');
			let loader = selector.getElementsByClassName('seo-ads-client-selector-loader')[0];
			this.loader.show(loader);
		}
	}

	getHtml()
	{
		return Tag.render`
		<div class="seo-ads-client">
			<div class="seo-ads-client-selector">
				<div class="seo-ads-client-selector-avatar" data-role="user-avatar"></div>
				<div class="seo-ads-client-selector-user">
					<a target="_top" data-role="user-name user-link" class="seo-ads-client-selector-user-link" title=""></a>
				</div>
				<span class="seo-ads-client-selector-arrow"></span>
				<span class="seo-ads-client-selector-loader"></span>
			</div>
			<div class="seo-ads-client-note">
			${Loc.getMessage('SEO_ADS_CLIENT_NOTE')}
			</div>
		</div>
		`;
	}

	getMenuItemHtml(item)
	{
		const name = BX.util.htmlspecialchars(item.NAME);
		const html =  Tag.render`<div>
			${item.PICTURE ? 
				Tag.render`<div class="seo-ads-client-menu-avatar" style="background-image: url('${item.PICTURE}');"></div>` :
				Tag.render`<div class="seo-ads-client-menu-avatar"></div>`}
			<span class="seo-ads-client-menu-popup-user">${name}</span>
			<span class="seo-ads-client-menu-popup-shutoff" data-role="client-remove" data-client-id="${item.CLIENT_ID}">${Loc.getMessage('SEO_ADS_CLIENT_DISCONNECT')}</span>
		</div>`;
		return html.innerHTML;
	}

	getRemoveConfirmPopupHtml(item)
	{
		const name = BX.util.htmlspecialchars(item.NAME);
		return Tag.render`<div class="seo-ads-client-popup">
			<div class="seo-ads-client-popup-text">
			${Loc.getMessage('SEO_ADS_CLIENT_REMOVE').replace('#NAME#', name)}
			</div>
		</div>`;
	}

	updateClientHtml()
	{
		let userAvatar = '';
		let userName = '';
		let userLink = '';
		let empty = false;
		if (this.selected)
		{
			userAvatar = this.selected.hasOwnProperty('PICTURE') ? this.selected.PICTURE : '';
			userName = this.selected.hasOwnProperty('NAME') ? this.selected.NAME : Loc.getMessage('SEO_ADS_CLIENT_SELECTOR_UNTITLED');
			userLink = this.selected.hasOwnProperty('LINK') ? this.selected.LINK : '';
		}
		else
		{
			userName = Loc.getMessage('SEO_ADS_CLIENT_SELECTOR_EMPTY')
			empty = true;
		}
		let selector = this.getSelectorNode();

		if (empty)
		{
			selector ? selector.classList.add('seo-ads-client-selector-empty') : false;
		}
		else
		{
			selector ? selector.classList.remove('seo-ads-client-selector-empty') : false;
		}
		let avatarNode = this.container.querySelector('[data-role="user-avatar"]');
		let nameNode = this.container.querySelector('[data-role*="user-name"]');
		let linkNode = this.container.querySelector('[data-role*="user-link"]');

		if (userAvatar)
			avatarNode.style.backgroundImage = "url('"+userAvatar+"')";
		else
			avatarNode.style.removeProperty('background-image');

		nameNode.textContent = userName;

		if (userLink)
			linkNode.setAttribute('href', userLink);
		else
			linkNode.removeAttribute('href');
	}

	onSelectItem(item)
	{
		this.setSelected(item);
		if (Type.isFunction(this.onSelectItemCallback))
			this.onSelectItemCallback(item);
	}

	onUnSelectItem()
	{
		this.setSelected(null);
		if (Type.isFunction(this.onUnSelectItemCallback))
		{
			this.onUnSelectItemCallback();
		}
	}

	onRemoveItem(item)
	{
		if (Type.isFunction(this.onRemoveItemCallback))
			this.onRemoveItemCallback(item);
	}

	onContainerClick()
	{
		if (!this.enabled)
		{
			return;
		}

		let menuItems = [];

		for (let item of this.items)
		{
			menuItems.push({
				html: this.getMenuItemHtml(item),
				className : "seo-ads-client-menu menu-popup-no-icon",
				onclick: this.onSelectItem.bind(this, item)
			});
		}

		if (this.canUnSelectItem)
		{
			menuItems.push(
				{delimiter: true},
				{
					text: Loc.getMessage('SEO_ADS_CLIENT_NO_ACCOUNT'),
					onclick: this.onUnSelectItem.bind(this)
				}
			);
		}
		if (this.canAddItems)
		{
			menuItems.push(
				{delimiter: true},
				{
					text: Loc.getMessage('SEO_ADS_CLIENT_ADD'),
					onclick: () => {
						this.closeMenu();
						if (Type.isFunction(this.onNewItemCallback))
							this.onNewItemCallback();
					}
				});
		}
		let selector = this.getSelectorNode();

		BX.PopupMenu.show(
			"clientsMenuDropdown",
			this.container,
			menuItems,
			{
				offsetTop: 0,
				offsetLeft: 42,
				angle: true,
				events: {
					onPopupClose: () => {
						selector ? selector.classList.remove('seo-ads-client-selector-active') : false;
						BX.PopupMenu.destroy('clientsMenuDropdown');
					}
				}
			}
		);
		selector ? selector.classList.add('seo-ads-client-selector-active') : false;

		let removeClientLinks = BX.PopupMenu.currentItem.popupWindow.getContentContainer().querySelectorAll('[data-role="client-remove"]');
		for (let removeClientLink of removeClientLinks) {
			Event.bind(removeClientLink, "click", (event) => {
				event.stopPropagation();
				let clientId = BX.data(event.target, "client-id");
				this.closeMenu();
				for (let item of this.items) {
					if (item.CLIENT_ID == clientId) {
						this.confirmRemoveItem(item);
					}
				}
			});
		}
	}

	confirmRemoveItem(item)
	{
		let confirmPopup = new BX.PopupWindow({
			content: this.getRemoveConfirmPopupHtml(item),
			autoHide: true,
			cacheable: false,
			closeIcon: true,
			closeByEsc: true,
			buttons: [
				new BX.UI.Button({
					text : Loc.getMessage('SEO_ADS_CLIENT_DISCONNECT'),
					color: BX.UI.Button.Color.DANGER,
					onclick: (event) => {
						confirmPopup.close();
						this.onRemoveItem(item);
					}
				}),
				new BX.UI.Button({
					text : Loc.getMessage('SEO_ADS_CLIENT_BTN_CANCEL'),
					color: BX.UI.Button.Color.LINK,
					onclick: () => {
						confirmPopup.close();
					}
				})
			]
		});
		confirmPopup.show();
	}

	closeMenu()
	{
		if (BX.PopupMenu.currentItem) {
			BX.PopupMenu.currentItem.close();
		}
	}

	destroy()
	{
		if (BX.PopupMenu.currentItem) {
			BX.PopupMenu.currentItem.close();
		}
		this.container.innerHTML = '';
	}

	getSelectorNode()
	{
		let selector = this.container.getElementsByClassName('seo-ads-client-selector');
		if (selector)
			selector = selector[0];

		return selector;
	}
}