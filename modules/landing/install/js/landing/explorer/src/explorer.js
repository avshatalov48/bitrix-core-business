import { Backend } from 'landing.backend';
import { Loc } from 'landing.loc';
import { Dom } from 'main.core';
import { Popup } from 'main.popup';
import { MessageBox } from 'ui.dialogs.messagebox';

import { ExplorerUI, DataType, FolderType } from './ui';

import './explorer.css';

type ExplorerOptions = {
	type: string,
	siteId: number,
	folderId: ?number,
	startBreadCrumbs: ?Array<FolderType>
};

type ErrorType = {
	error: string,
	error_description: string
};

export class Explorer
{
	/** @var {Popup} */
	popupWindow = null;
	type: string;
	currentSiteId: number;
	currentFolderId: number;
	startBreadCrumbs: ?Array<FolderType>;

	constructor(options: ExplorerOptions)
	{
		this.type = options.type;
		this.currentSiteId = options.siteId;
		this.currentFolderId = options.folderId;
		if (options.startBreadCrumbs)
		{
			this.startBreadCrumbs = options.startBreadCrumbs;
		}
		this.popupWindow = this.getPopupWindow();
	}

	getPopupWindow()
	{
		if (this.popupWindow === null)
		{
			this.popupWindow = new Popup({
				bindElement: null,
				className: 'ui-message-box landing-explorer--copy-page',
				content: null,
				titleBar: '&nbsp;',
				overlay: { opacity: 30 },
				closeIcon: false,
				contentBackground: 'transparent',
				padding: 0
			});
		}

		return this.popupWindow;
	}

	open()
	{
		this.popupWindow.setContent(
			ExplorerUI.getLoader()
		);
		this.popupWindow.show();
	}

	errorAlert(errors: Array<ErrorType>)
	{
		MessageBox.alert(
			errors[0].error_description,
			Loc.getMessage('LANDING_EXT_EXPLORER_ALERT_TITLE'),
			(messageBox, button) => {
				button.setWaiting(false);
				messageBox.close();
				this.popupWindow.close();
			}
		);
	}

	setTitle(type: string, title: string)
	{
		this.popupWindow.setTitleBar(
			Loc.getMessage('LANDING_EXT_EXPLORER_TITLE_' + type.toUpperCase())
				.replace('#title#', title)
		);
	}

	setButtons(entityId: number, type: 'copy' | 'move' | 'moveFolder')
	{
		const typeUpper = type.toUpperCase();
		let action = null;
		let data = null;

		this.popupWindow.setButtons([
			ExplorerUI.getActionButton(
				(type === 'moveFolder')
				? Loc.getMessage('LANDING_EXT_EXPLORER_BUTTON_MOVE')
				: Loc.getMessage('LANDING_EXT_EXPLORER_BUTTON_' + typeUpper),
				() => {
					switch (type)
					{
						case 'copy':
							action = 'Landing::copy';
							data = {
								lid: entityId,
								toSiteId: this.currentSiteId,
								toFolderId: this.currentFolderId,
								skipSystem: true
							};
							break;
						case 'move':
							action = 'Landing::move';
							data = {
								lid: entityId,
								toSiteId: this.currentSiteId,
								toFolderId: this.currentFolderId
							};
							break;
						case 'moveFolder':
							action = 'Site::moveFolder';
							data = {
								folderId: entityId,
								toSiteId: this.currentSiteId,
								toFolderId: this.currentFolderId
							};
							break;
					}
					Backend.getInstance()
						.action(
							action,
							data,
							{
								site_id: this.currentSiteId,
								type: this.type
							},
						)
						.then(() => {
							this.popupWindow.setContent(
								ExplorerUI.getLoader()
							);
						})
						.then(() => {
							setTimeout(() => {
								window.location.reload();
							}, 500);
						})
						.catch(reason => {
							this.errorAlert(reason.result);
							//return Promise.reject(reason);
						})
				}
			),
			ExplorerUI.getCancelButton(() => {
				this.popupWindow.close();
			})
		]);
	}

	#loadBreadCrumbs(pos: number)
	{
		if (this.startBreadCrumbs[pos])
		{
			this.#loadFolders(
				this.currentSiteId,
				this.startBreadCrumbs[pos].PARENT_ID,
				() => {
					if (this.startBreadCrumbs[pos + 1])
					{
						this.#loadBreadCrumbs(pos + 1);
					}
					else
					{
						this.#clickFolder(this.startBreadCrumbs[pos].ID);
					}
				}
			);
		}
	}

	#loadSites()
	{
		Backend.getInstance()
			.action(
				'Site::getList',
				{
					params: {
						filter: {
							'=TYPE': this.type,
							'=SPECIAL': 'N'
						},
						order: {
							DATE_MODIFY: 'desc'
						}
					}
				},
				{
					type: this.type
				}
			)
			.then(result => {
				this.popupWindow.setContent(
					ExplorerUI.getSiteList(result, this.#clickSite.bind(this), this.type)
				);
				this.popupWindow.adjustPosition();
				this.#scrollToSite(this.currentSiteId);
				if (this.startBreadCrumbs.length > 0)
				{
					this.#selectSite(this.currentSiteId);
					this.#loadBreadCrumbs(0);
				}
				else
				{
					this.#clickSite(this.currentSiteId);
				}
			});
	}

	#loadFolders(siteId: number, parentId: ?number, onLoad: ?() => {})
	{
		Backend.getInstance()
			.action(
				'Site::getFolders',
				{
					siteId,
					filter: {
						PARENT_ID: parentId ? parentId : 0
					}
				},
				{
					site_id: siteId,
					type: this.type
				}
			)
			.then((result: Array<DataType>) => {
				if (result.length <= 0)
				{
					return;
				}
				const selectedItem = (parentId > 0)
					? this.#selectFolder(parentId)
					: this.#selectSite(siteId);
				result.reverse().map((item: DataType) => {
					const folderExist = document.querySelector('.landing-site-selector-item[data-explorer-folderId="' + item.ID + '"]');
					if (!folderExist)
					{
						const depth = parseInt(Dom.attr(selectedItem, 'data-explorer-depth')) + 1;
						Dom.insertAfter(
							ExplorerUI.getFolderItem(item, depth, this.#clickFolder.bind(this)),
							selectedItem
						);
					}
				});
				if (onLoad)
				{
					onLoad();
				}
			});
	}

	#clickSite(siteId: number)
	{
		this.currentFolderId = 0;
		this.#selectSite(siteId);
		this.#loadFolders(siteId);
	}

	#clickFolder(folderId: number)
	{
		this.#selectFolder(folderId);
		this.#loadFolders(this.currentSiteId, folderId);
	}

	#selectSite(siteId: number): HTMLElement
	{
		this.currentSiteId = siteId;
		return this.#selectItem(siteId, 'siteId')
	}

	#selectFolder(folderId: number): HTMLElement
	{
		this.currentFolderId = folderId;
		return this.#selectItem(folderId, 'folderId')
	}

	#selectItem(itemId: number, dataType: string): HTMLElement
	{
		const currentSelect = document.querySelector('.landing-site-selector-item-selected');
		const newSelect = document.querySelector('.landing-site-selector-item[data-explorer-' + dataType + '="' + itemId + '"]');
		if (currentSelect)
		{
			Dom.removeClass(currentSelect, 'landing-site-selector-item-selected');
		}
		if (newSelect)
		{
			Dom.addClass(newSelect, 'landing-site-selector-item-selected');
		}

		return newSelect;
	}

	#scrollToSite(siteId: number)
	{
		const siteNode = document.querySelector('[data-explorer-siteId="' + siteId + '"]');
		if (siteNode)
		{
			// const posY = siteNode.getBoundingClientRect().y;
			// document.querySelector('.landing-site-selector-list').scrollTo(0, posY);
			siteNode.scrollIntoView(
				{
					behavior: 'smooth',
					block: 'nearest',
					inline: 'start'
				}
			);
		}
	}

	copy(landing: DataType)
	{
		this.setTitle('copy', landing.TITLE);
		this.setButtons(landing.ID, 'copy');
		this.open();
		this.#loadSites();
	}

	move(landing: DataType)
	{
		this.setTitle('move', landing.TITLE);
		this.setButtons(landing.ID, 'move');
		this.open();
		this.#loadSites();
	}

	moveFolder(folder: FolderType)
	{
		this.setTitle('move', folder.TITLE);
		this.setButtons(folder.ID, 'moveFolder');
		this.open();
		this.#loadSites();
	}
}
