import {Type, Loc, Tag, Dom} from 'main.core';
import {Popup} from 'main.popup';
import {Button} from 'ui.buttons';
import {MenuManager} from 'main.popup';

import {FeedInstance, PinnedPanelInstance} from './feed';
import {TaskCreator} from './taskcreator';

class Post
{
	static showBackgroundWarning({
		urlToEdit,
		menuPopupWindow
	})
	{
		const content = Tag.render`<div>${Loc.getMessage('SONET_EXT_LIVEFEED_POST_BACKGROUND_EDIT_WARNING_DESCRIPTION')}</div>`;

		const dialog = new Popup('backgroundWarning', null, {
			autoHide: true,
			closeByEsc: true,
			offsetLeft: 0,
			offsetTop: 0,
			draggable: true,
			bindOnResize: false,
			titleBar: Loc.getMessage('SONET_EXT_LIVEFEED_POST_BACKGROUND_EDIT_WARNING_TITLE'),
			closeIcon: true,
			className: 'sonet-livefeed-popup-warning',
			content: content,
			events: {},
			cacheable: false,
			buttons: [
				new Button({
					text: Loc.getMessage('SONET_EXT_LIVEFEED_POST_BACKGROUND_EDIT_WARNING_BUTTON_SUBMIT'),
					className: 'ui-btn ui-btn-primary',
					events: {
						click: () => {
							window.location = urlToEdit;
							dialog.close();
							if (menuPopupWindow)
							{
								menuPopupWindow.close();
							}
						}
					}
				}),
				new Button({
					text: Loc.getMessage('SONET_EXT_LIVEFEED_POST_BACKGROUND_EDIT_WARNING_BUTTON_CANCEL'),
					className: 'ui-btn ui-btn-light',
					events : {click : () => {
						dialog.close();
						if (menuPopupWindow)
						{
							menuPopupWindow.close();
						}
					}}
				})
			]
		});

		dialog.show();

		return false;
	}

	static showMenu(params)
	{
		if (!Type.isPlainObject(params))
		{
			params = {};
		}

		const menuElement = params.menuElement;
		const ind = params.ind;
		const menuId = this.getMenuId(ind);

		MenuManager.destroy(menuId);

		let log_id = (!Type.isUndefined(params.log_id) ? parseInt(params.log_id) : 0);

		if (log_id <= 0)
		{
			log_id = parseInt(menuElement.getAttribute('data-log-entry-log-id'));
		}
		if (log_id <= 0)
		{
			return false;
		}

		let bFavorites = params.bFavorites;
		if (Type.isUndefined(bFavorites))
		{
			bFavorites = (menuElement.getAttribute('data-log-entry-favorites') === 'Y');
		}

		let arMenuItemsAdditional = params.arMenuItemsAdditional;
		if (Type.isUndefined(arMenuItemsAdditional))
		{
			arMenuItemsAdditional = menuElement.getAttribute('data-bx-items');
			try
			{
				arMenuItemsAdditional = JSON.parse(arMenuItemsAdditional);
				if (!Type.isPlainObject(arMenuItemsAdditional))
				{
					arMenuItemsAdditional = {};
				}
			}
			catch (e)
			{
				arMenuItemsAdditional = {};
			}
		}

		const bindElement = params.bindElement;

		let itemPinned = null;
		const pinnedPostNode = bindElement.closest('[data-livefeed-post-pinned]');
		if (pinnedPostNode)
		{
			const pinnedState = (pinnedPostNode.getAttribute('data-livefeed-post-pinned') === 'Y');

			itemPinned = {
				text : (pinnedState ? Loc.getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_PINNED_Y') : Loc.getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_PINNED_N')),
				className: 'menu-popup-no-icon',
				onclick: (e) => {
					PinnedPanelInstance.changePinned({
						logId: log_id,
						newState: (pinnedState ? 'N' : 'Y'),
						event: e,
						node: bindElement,
					});

					MenuManager.getMenuById(this.getMenuId(ind)).popupWindow.close();
					e.preventDefault();
				}
			};
		}

		const itemFavorites = (
			Loc.getMessage('sonetLbUseFavorites') !== 'N'
				? {
					text: (bFavorites ? Loc.getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_Y') : Loc.getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_N')),
					className: 'menu-popup-no-icon',
					onclick : (e) => {
						__logChangeFavorites(
							log_id,
							`log_entry_favorites_${log_id}`,
							(bFavorites ? 'N' : 'Y'),
							true,
							e
						);
						e.preventDefault();
						e.stopPropagation();
					}
				}
				: null
		);

		let arItems = [
			itemPinned,
			itemFavorites,
			(
				Type.isStringFilled(menuElement.getAttribute('data-log-entry-url'))
					? {
						html: `<span id="${menuId}-href-text">${Loc.getMessage('sonetLMenuHref')}</span>`,
						className: 'menu-popup-no-icon feed-entry-popup-menu feed-entry-popup-menu-href',
						href: menuElement.getAttribute('data-log-entry-url'),
					}
					: null
			),
			(
				Type.isStringFilled(menuElement.getAttribute('data-log-entry-url'))
					? {
						html: `<span id="${menuId}-link-text">${Loc.getMessage('sonetLMenuLink')}</span>` +
							`<span id="${menuId}-link-icon-animate" class="post-menu-link-icon-wrap">` +
							`<span class="post-menu-link-icon" id="${menuId}-link-icon-done" style="display: none;">` +

							'</span>' +
							'</span>',
						className : 'menu-popup-no-icon feed-entry-popup-menu feed-entry-popup-menu-link',
						onclick: (e) => {

							const menuItemText = document.getElementById(`${menuId}-link-text`);
							const menuItemIconDone = document.getElementById(`${menuId}-link-icon-done`);

							if (BX.clipboard.isCopySupported())
							{
								if (menuItemText && menuItemText.getAttribute('data-block-click') === 'Y')
								{
									return;
								}

								BX.clipboard.copy(menuElement.getAttribute('data-log-entry-url'));

								if (
									menuItemText
									&& menuItemIconDone
								)
								{
									menuItemIconDone.style.display = 'inline-block';
									document.getElementById(`${menuId}-link-icon-animate`).classList.remove('post-menu-link-icon-animate');

									Dom.adjust(document.getElementById(`${menuId}-link-text`), {
										attrs: {
											'data-block-click': 'Y',
										},
									});

									setTimeout(() => {
										document.getElementById(`${menuId}-link-icon-animate`).classList.add('post-menu-link-icon-animate');
									}, 1);

									setTimeout(() => {
										Dom.adjust(document.getElementById(`${menuId}-link-text`), {
											attrs: {
												'data-block-click': 'N',
											},
										});
									}, 500);
								}

								return;
							}

							const it = e.currentTarget;
							const height = parseInt(!!it.getAttribute('bx-height') ? it.getAttribute('bx-height') : it.offsetHeight);

							if (it.getAttribute('bx-status') !== 'shown')
							{
								it.setAttribute('bx-status', 'shown');

								const node = document.getElementById(`${menuId}-link-text`);

								if (!document.getElementById(`${menuId}-link`) && !!node)
								{
									const pos = BX.pos(node);
									const pos2 = BX.pos(node.parentNode);
									const pos3 = BX.pos(node.closest('.menu-popup-item'));

									pos.height = pos2.height - 1;

									Dom.adjust(it, {
										attrs : {
											'bx-height': it.offsetHeight,
										},
										style : {
											overflow: 'hidden',
											display: 'block'
										},
										children : [
											Dom.create('BR'),
											Dom.create('DIV', {
												attrs : {
													id: `${menuId}-link`,
												},
												children : [
													Dom.create('SPAN', {attrs: {className: 'menu-popup-item-left'}}),
													Dom.create('SPAN', {attrs: {className: 'menu-popup-item-icon'}}),
													Dom.create('SPAN', {
														attrs: {className: 'menu-popup-item-text'},
														children : [
															Dom.create('INPUT', {
																attrs : {
																	id: `${menuId}-link-input`,
																	type: 'text',
																	value: menuElement.getAttribute('data-log-entry-url'),
																},
																style : {
																	height: `${pos.height}px`,
																	width: `${(pos3.width - 21)}px`,
																},
																events: {
																	click: (e) => {
																		e.currentTarget.select();
																		e.stopPropagation();
																		e.preventDefault();
																	},
																}
															})
														]
													})
												]
											}),
											Dom.create('SPAN', {attrs: {className: 'menu-popup-item-right'}}),
										]
									});

									Event.bind(document.getElementById(`${menuId}-link-input`), 'click', (e) => {
										e.currentTarget.select();
										e.preventDefault();
										e.stopPropagation()
									});
								}
								(new BX.fx({
									time: 0.2,
									step: 0.05,
									type: 'linear',
									start: height,
									finish: height * 2,
									callback: function (height) {
										this.style.height = `${height}px`;
									}.bind(it),
								})).start();
								BX.fx.show(document.getElementById(`${menuId}-link`), 0.2);
								document.getElementById(`${menuId}-link-input`).select();
							}
							else
							{
								it.setAttribute('bx-status', 'hidden');
								(new BX.fx({
									time: 0.2,
									step: 0.05,
									type: 'linear',
									start: it.offsetHeight,
									finish: height,
									callback: function(height) {
										this.style.height = `${height}px`;
									}.bind(it),
								})).start();
								BX.fx.hide(document.getElementById(`${menuId}-link`), 0.2);
							}
						}
					}
					: null
			),
			(
				Loc.getMessage('sonetLCanDelete') === 'Y'
					? {
						text: Loc.getMessage('sonetLMenuDelete'),
						className: 'menu-popup-no-icon',
						onclick: (e) => {
							if (confirm(Loc.getMessage('sonetLMenuDeleteConfirm')))
							{
								FeedInstance.delete({
									logId: log_id,
									nodeId: `log-entry-${log_id}`,
									ind: ind,
								});
							}

							e.stopPropagation();
							e.preventDefault();
						}
					} : null
			),
			(
				menuElement.getAttribute('data-log-entry-createtask') === 'Y'
					? {
						text: Loc.getMessage('sonetLMenuCreateTask'),
						className: 'menu-popup-no-icon',
						onclick: (e) => {
							TaskCreator.create({
								entryEntityType: menuElement.getAttribute('data-log-entry-entity-type'),
								entityType: menuElement.getAttribute('data-log-entry-entity-type'),
								entityId: menuElement.getAttribute('data-log-entry-entity-id'),
								logId: parseInt(menuElement.getAttribute('data-log-entry-log-id')),
							});

							MenuManager.getMenuById(this.getMenuId(ind)).popupWindow.close();
							return e.preventDefault();
						}
					}
					: null
			),
			(
				menuElement.getAttribute('data-log-entry-createtask') === 'Y'
				&& menuElement.getAttribute('data-log-entry-entity-type') === 'TASK'
					? {
						text: Loc.getMessage('sonetLMenuCreateSubTask'),
						className: 'menu-popup-no-icon',
						onclick: (e) => {
							TaskCreator.create({
								entryEntityType: menuElement.getAttribute('data-log-entry-entity-type'),
								entityType: menuElement.getAttribute('data-log-entry-entity-type'),
								entityId: menuElement.getAttribute('data-log-entry-entity-id'),
								logId: parseInt(menuElement.getAttribute('data-log-entry-log-id')),
								parentTaskId: parseInt(menuElement.getAttribute('data-log-entry-entity-id')),
							});

							MenuManager.getMenuById(this.getMenuId(ind)).popupWindow.close();
							return e.preventDefault();
						}
					}
					: null
			),
		];

		if (
			!!arMenuItemsAdditional
			&& Type.isArray(arMenuItemsAdditional)
		)
		{
			arMenuItemsAdditional.forEach((item) => {
				if (Type.isUndefined(item.className))
				{
					item.className = 'menu-popup-no-icon';
				}
			});

			arItems = arItems.concat(arMenuItemsAdditional);
		}

		const arParams = {
			offsetLeft: -14,
			offsetTop: 4,
			lightShadow: false,
			angle: {
				position: 'top',
				offset : 50,
			},
			events: {
				onPopupShow: (ob) => {
					if (document.getElementById(`log_entry_favorites_${log_id}`))
					{
						let favoritesMenuItem = null;

						const menuItems = ob.contentContainer.querySelectorAll('.menu-popup-item-text');
						menuItems.forEach((menuItem) => {
							if (
								menuItem.innerHTML === Loc.getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_Y')
								|| menuItem.innerHTML === Loc.getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_N')
							)
							{
								favoritesMenuItem = menuItem;
							}
						});

						if (Type.isDomNode(favoritesMenuItem))
						{
							favoritesMenuItem.innerHTML = (
								document.getElementById(`log_entry_favorites_${log_id}`).classList.contains('feed-post-important-switch-active')
									? Loc.getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_Y')
									: Loc.getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_N')
							);
						}
					}

					if (document.getElementById(`${menuId}-link`))
					{
						const linkMenuItem = ob.popupContainer.querySelector('.feed-entry-popup-menu-link');
						if (linkMenuItem)
						{
							const height = parseInt(!!linkMenuItem.getAttribute('bx-height') ? linkMenuItem.getAttribute('bx-height') : 0);
							if (height > 0)
							{
								document.getElementById(`${menuId}-link`).style.display = 'none';
								linkMenuItem.setAttribute('bx-status', 'hidden');
								linkMenuItem.style.height = `${height}px`;
							}
						}
					}
				}
			}
		};

		MenuManager.show(this.getMenuId(ind), bindElement, arItems, arParams);
	}

	static getMenuId(ind)
	{
		return `post-menu-${ind}`;
	}

}

export {
	Post
};