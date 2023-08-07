import { Type, Loc, ajax } from 'main.core';
import { MenuManager } from 'main.popup';
import { Messenger } from 'im.public';
import { Waiter } from './waiter.js';
import { SonetGroupMenu } from './sonetgroupmenu.js';
import { RecallJoinRequest } from './recalljoinrequest.js';

class Common
{
	static showGroupMenuPopup(params)
	{
		let bindElement = params.bindElement;
		if (Type.isStringFilled(bindElement))
		{
			bindElement = document.getElementById(bindElement);
		}

		const currentUserId = parseInt(Loc.getMessage('USER_ID'));
		const sonetGroupMenu = SonetGroupMenu.getInstance();

		if (bindElement.tagName === 'BUTTON')
		{
			bindElement.classList.add('ui-btn-active');
		}

		const menu = [];
		let itemTitle = '';

		if (currentUserId > 0)
		{
			menu.push({
				text: (!!sonetGroupMenu.favoritesValue ? Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_FAVORITES_REMOVE') : Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_FAVORITES_ADD')),
				title: (!!sonetGroupMenu.favoritesValue ? Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_FAVORITES_REMOVE') : Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_FAVORITES_ADD')),
				id: 'set-group-favorite',
				onclick: () => {

					const favoritesValue = sonetGroupMenu.favoritesValue;

					sonetGroupMenu.setItemTitle(!favoritesValue);
					sonetGroupMenu.favoritesValue = !favoritesValue;

					this.setFavoritesAjax({
						groupId: params.groupId,
						favoritesValue: favoritesValue,
						callback: {
							success: (data) => {

								BX.onCustomEvent(window, 'BX.Socialnetwork.WorkgroupFavorites:onSet', [{
									id: params.groupId,
									name: data.NAME,
									url: data.URL,
									extranet: (!Type.isUndefined(data.EXTRANET) ? data.EXTRANET : 'N'),
								}, !favoritesValue]);

								BX.onCustomEvent(window, 'BX.Socialnetwork.WorkgroupMenu:onSetFavorites', [{
									groupId: params.groupId,
									value: !favoritesValue,
								}]);

								window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', {
									code: 'afterSetFavorites',
									data: {
										groupId: data.ID,
										value: (data.RESULT === 'Y')
									}
								});
							},
							failure: () => {
								sonetGroupMenu.favoritesValue = favoritesValue;
								sonetGroupMenu.setItemTitle(favoritesValue);
							}
						}
					});
				}
			});

			if (params.perms.canInitiate)
			{
				itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_REQU');
				if (!!params.isScrumProject)
				{
					itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_REQU_SCRUM');
				}
				else if (!!params.isProject)
				{
					itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_REQU_PROJECT');
				}
				menu.push({
					text: itemTitle,
					title: itemTitle,
					href: params.urls.requestUser,
				});
			}

			if (params.perms.canModify)
			{
				itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_EDIT');
				if (!!params.isScrumProject)
				{
					itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_EDIT_SCRUM');
				}
				else if (!!params.isProject)
				{
					itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_EDIT_PROJECT');
				}
				menu.push({
					text: itemTitle,
					title: itemTitle,
					href: params.urls.edit,
				});

				if (!params.hideArchiveLinks)
				{
					const featuresItem = {
						text: Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_FEAT'),
						title: Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_FEAT'),
					};

					if (params.editFeaturesAllowed)
					{
						featuresItem.href = params.urls.features;
					}
					else
					{
						featuresItem.onclick = () => {
							B24.licenseInfoPopup.show(
								'sonetGroupFeatures',
								Loc.getMessage('SONET_EXT_COMMON_B24_SONET_GROUP_FEATURES_TITLE'),
								`<span>${Loc.getMessage('SONET_EXT_COMMON_B24_SONET_GROUP_FEATURES_TEXT')}</span>`,
								true
							);
						};
					}
					menu.push(featuresItem);
				}

				itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_DELETE');
				if (!!params.isScrumProject)
				{
					itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_DELETE_SCRUM');
				}
				else if (!!params.isProject)
				{
					itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_DELETE_PROJECT');
				}
				menu.push({
					text: itemTitle,
					title: itemTitle,
					href: params.urls.delete,
				});
			}

			menu.push({
				text: (params.perms.canModerate ? Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_MEMBERS_EDIT') : Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_MEMBERS_VIEW')),
				title: (params.perms.canModerate ? Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_MEMBERS_EDIT') : Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_MEMBERS_VIEW')),
				href: params.urls.members,
			});

			if (params.perms.canInitiate)
			{
				if (params.perms.canProcessRequestsIn)
				{
					menu.push({
						text: Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_REQ_IN'),
						title: Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_REQ_IN'),
						href: params.urls.requests,
					});
				}

				itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_REQ_OUT');
				if (!!params.isScrumProject)
				{
					itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_REQ_OUT_SCRUM');
				}
				else if (!!params.isProject)
				{
					itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_REQ_OUT_PROJECT');
				}
				menu.push({
					text: itemTitle,
					title: itemTitle,
					href: params.urls.requestsOut,
				});
			}

			if (params.perms.canModify)
			{
				itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_COPY');
				if (!!params.isScrumProject)
				{
					itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_COPY_SCRUM');
				}
				else if (!!params.isProject)
				{
					itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_COPY_PROJECT');
				}
				const copyGroupItem = {
					text: itemTitle,
					title: itemTitle,
				}
				if (params.copyFeatureAllowed)
				{
					copyGroupItem.href = params.urls.copy;
				}
				else
				{
					copyGroupItem.onclick = () => {
						if (!!params.isProject)
						{
							BX.UI.InfoHelper.show('limit_task_copy_project', {
								isLimit: true,
								limitAnalyticsLabels: {
									module: 'socialnetwork',
									source: 'projectCardActions'
								}
							});
						}
						else
						{
							BX.UI.InfoHelper.show('limit_task_copy_group', {
								isLimit: true,
								limitAnalyticsLabels: {
									module: 'socialnetwork',
									source: 'projectCardActions'
								}
							});
						}
					};
				}

				if (!params.isScrumProject) // todo remove after scrum copy will done
				{
					menu.push(copyGroupItem);
				}
			}

			if (
				(
					!Type.isStringFilled(params.userRole)
					|| (
						params.userRole === Loc.getMessage('USER_TO_GROUP_ROLE_REQUEST')
						&& params.initiatedByType === Loc.getMessage('USER_TO_GROUP_INITIATED_BY_GROUP')
					)
				)
				&& !params.hideArchiveLinks
			)
			{
				itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_JOIN');
				if (!!params.isScrumProject)
				{
					itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_JOIN_SCRUM');
				}
				else if (!!params.isProject)
				{
					itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_JOIN_PROJECT');
				}
				const userRequestItem = {
					text: itemTitle,
					title: itemTitle,
				};

				if (!!params.isOpened)
				{
					userRequestItem.onclick = () => {
						this.sendJoinRequest(params);
					};
				}
				else
				{
					userRequestItem.href = params.urls.userRequestGroup;
				}
				menu.push(userRequestItem);
			}

			if (
				Type.isStringFilled(params.userRole)
				&& params.userRole === Loc.getMessage('USER_TO_GROUP_ROLE_REQUEST')
				&& params.initiatedByType === Loc.getMessage('USER_TO_GROUP_INITIATED_BY_USER')
				&& parseInt(params.initiatedByUserId) === currentUserId
			)
			{
				itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_DELETE_REQUEST');

				menu.push({
					text: itemTitle,
					title: itemTitle,
					onclick: () => {
						this.cancelIncomingRequest(params);
					},
				});
			}

			if (
				(
					Type.isBoolean(params.perms.canLeave)
					&& params.perms.canLeave
				)
				||
				(
					!Type.isBoolean(params.perms.canLeave)
					&& params.userIsMember
					&& !params.userIsAutoMember
					&& params.userRole !== Loc.getMessage('USER_TO_GROUP_ROLE_OWNER')
				)
			)
			{
				itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_EXIT');
				if (!!params.isScrumProject)
				{
					itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_EXIT_SCRUM');
				}
				else if (!!params.isProject)
				{
					itemTitle = Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_EXIT_PROJECT');
				}
				menu.push({
					text: itemTitle,
					title: itemTitle,
					href: params.urls.userLeaveGroup
				});
			}

			if (params.canPickTheme)
			{
				menu.push({
					text: Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_THEME_DIALOG'),
					title: Loc.getMessage('SONET_EXT_COMMON_GROUP_MENU_THEME_DIALOG'),
					onclick: () => {
						BX.Intranet.Bitrix24.ThemePicker.Singleton.showDialog(false);
					},
				});
			}
		}

		const popup = MenuManager.create('group-profile-menu', bindElement, menu, {
			offsetTop: 5,
			offsetLeft : (bindElement.offsetWidth - 18),
			angle : true,
			events : {
				onPopupClose: () => {
					if (bindElement.tagName === 'BUTTON')
					{
						bindElement.classList.remove('ui-btn-active');
					}
				}
			}
		});

		const item = popup.getMenuItem('set-group-favorite');
		if (item)
		{
			sonetGroupMenu.menuItem = item.layout.text;
		}

		popup.popupWindow.show();
		sonetGroupMenu.menuPopup = popup;
	}

	static sendJoinRequest(params)
	{
		Waiter.getInstance().show();

		if (
			SonetGroupMenu.getInstance()
			&& SonetGroupMenu.getInstance().menuPopup
		)
		{
			SonetGroupMenu.getInstance().menuPopup.close();
		}

		ajax({
			url: params.urls.userRequestGroup,
			method: 'POST',
			dataType: 'json',
			data: {
				groupID: params.groupId,
				MESSAGE: '',
				ajax_request: 'Y',
				save: 'Y',
				sessid: Loc.getMessage('bitrix_sessid'),
			},
			onsuccess: (responseData) => {
				Waiter.getInstance().hide();
				if (
					Type.isStringFilled(responseData.MESSAGE)
					&& responseData.MESSAGE === 'SUCCESS'
					&& Type.isStringFilled(responseData.URL)
				)
				{
					BX.onCustomEvent(window.top, 'sonetGroupEvent', [ {
						code: 'afterJoinRequestSend',
						data: {
							groupId: params.groupId
						}
					} ]);
					top.location.href = responseData.URL;
				}
			},
			onfailure: () => {
				Waiter.getInstance().hide();
			},
		});
	}

	static cancelIncomingRequest(params)
	{
		Waiter.getInstance().show();

		if (
			SonetGroupMenu.getInstance()
			&& SonetGroupMenu.getInstance().menuPopup
		)
		{
			SonetGroupMenu.getInstance().menuPopup.close();
		}

		ajax.runAction('socialnetwork.api.usertogroup.cancelIncomingRequest', {
			data: {
				groupId: params.groupId,
				userId: parseInt(Loc.getMessage('USER_ID')),
			},
		}).then((response) => {
			Waiter.getInstance().hide();

			window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', {
				code: 'afterIncomingRequestCancel',
				data: {
					groupId: params.groupId,
				}
			});

			this.reload();
		}).catch((response) => {
			Waiter.getInstance().hide();
		});
	}

	static setFavoritesAjax(params)
	{
		ajax.runAction('socialnetwork.api.workgroup.setFavorites', {
			data: {
				params: {
					groupId: params.groupId,
					value: (params.favoritesValue === false ? 'Y' : 'N'),
					getAdditionalResultData: true,
				}
			},
			analyticsLabel: {
				b24statAction: (params.favoritesValue ? 'removeFavSonetGroup' : 'addFavSonetGroup')
			}
		}).then((response) => {
			params.callback.success(response.data);
		}).catch((response) => {
			params.callback.failure({
				ERROR: response.errors[0].message,
			});
		});
	}

	static reload()
	{
		if (top !== window) // current page in slider
		{
			if (!Type.isUndefined(top.BX.SidePanel))
			{
				top.BX.SidePanel.Instance.getSliderByWindow(window).showLoader();
			}
			window.location.reload();
		}
		else if (
			!Type.isUndefined(top.BX.SidePanel)
			&& top.BX.SidePanel.Instance.isOpen()
		) // there's an open slider
		{
			top.location.href = top.BX.SidePanel.Instance.getPageUrl();
		}
		else
		{
			top.location.reload();
		}
	}

	static reloadBlock(params)
	{
		if (
			!Type.isPlainObject(params)
			|| !Type.isStringFilled(params.blockId)
			|| !document.getElementById(params.blockId)
		)
		{
			return;
		}

		let url = '';

		if (
			!Type.isUndefined(top.BX.SidePanel)
			&& top.BX.SidePanel.Instance.isOpen()
		) // there's an open slider
		{
			url = top.BX.SidePanel.Instance.getPageUrl();
		}
		else
		{
			url = window.location.href;
		}

		ajax.promise({
			url: url,
			method: 'POST',
			dataType: 'json',
			data: {
				BLOCK_RELOAD: 'Y',
				BLOCK_ID: params.blockId
			}
		}).then((data) => {
			if (!Type.isUndefined(data.CONTENT))
			{
				document.getElementById(params.blockId).innerHTML = data.CONTENT;
				setTimeout(() => {
					ajax.processRequestData(data.CONTENT, {
						dataType: 'HTML'
					});
				}, 0);
			}
		});
	}

	static closeGroupCardMenu(node)
	{
		if (!node)
		{
			return;
		}

		const doc = node.ownerDocument;
		const win = doc.defaultView || doc.parentWindow;

		if (
			!win
			|| Type.isUndefined(win.BX.Socialnetwork.UIGroupMenu)
			|| !win.BX.Socialnetwork.UIGroupMenu.getInstance().menuPopup
		)
		{
			return;
		}

		win.BX.Socialnetwork.UIGroupMenu.getInstance().menuPopup.close();
	}

	static openMessenger(groupId: number): Promise
	{
		return ajax.runAction('socialnetwork.api.workgroup.getChatId', {
			data: {
				groupId: parseInt(groupId, 10),
			},
		}).then((response) => {
			if (response.data)
			{
				Messenger.openChat(`chat${parseInt(response.data, 10)}`);
			}
		}).catch(() => {});
	}

	static showError = RecallJoinRequest.showError;
	static hideError = RecallJoinRequest.hideError;
	static showButtonWait = RecallJoinRequest.showButtonWait;
	static hideButtonWait = RecallJoinRequest.hideButtonWait;
}

export {
	Common,
	RecallJoinRequest,
}

