(function() {

var BX = window.BX;

if (!!BX.SocialnetworkUICommon)
{
	return;
}

BX.SocialnetworkUICommon = {

	showRecallJoinRequestPopup: function(params)
	{
		if (
			parseInt(params.RELATION_ID) <= 0
			|| !BX.type.isNotEmptyString(params.URL_REJECT_OUTGOING_REQUEST)
		)
		{
			return;
		}

		var isProject = (typeof params.PROJECT != 'undefined' ? !!params.PROJECT : false);

		var successPopup = new BX.PopupWindow('bx-group-join-successfull-request-popup', window, {
			width: 400,
			autoHide: true,
			lightShadow: false,
			zIndex: 1000,
			overlay: true,
			content: BX.create('DIV', {children: [
					BX.create('DIV', {
						text: BX.message('SONET_EXT_COMMON_RECALL_JOIN_POPUP_TITLE'),
						props: {
							className: 'sonet-group-join-successfull-request-popup-title'
						}
					}),
					BX.create('DIV', {
						text: BX.message(isProject ? 'SONET_EXT_COMMON_RECALL_JOIN_POPUP_TEXT_PROJECT' : 'SONET_EXT_COMMON_RECALL_JOIN_POPUP_TEXT'),
						props: {
							className: 'sonet-group-join-successfull-request-popup-text'
						}
					}),
					BX.create('DIV', {
						props: {
							className: 'sonet-ui-btn-cont sonet-ui-btn-cont-center'
						},
						children: [
							BX.create('DIV', {
								children: [
									BX.create('BUTTON', {
										props: {
											className: 'ui-btn ui-btn-md ui-btn-danger'
										},
										events: {
											click: BX.delegate(function(event) {

												var _currentTarget = event.currentTarget;
												this.hideError(BX('bx-group-delete-request-error'));
												this.showButtonWait(_currentTarget);

												BX.ajax({
													url: params.URL_REJECT_OUTGOING_REQUEST,
													method: 'POST',
													dataType: 'json',
													data: {
														action: 'reject',
														max_count: 1,
														checked_0: 'Y',
														type_0: 'INVITE_GROUP',
														id_0: params.RELATION_ID,
														type: 'out',
														ajax_request: 'Y',
														sessid: BX.bitrix_sessid()
													},
													onsuccess: BX.delegate(function (deleteResponseData) {
														this.hideButtonWait(_currentTarget);

														if (
															typeof deleteResponseData.MESSAGE != 'undefined'
															&& deleteResponseData.MESSAGE == 'SUCCESS'
														)
														{
															successPopup.destroy();
															if (BX.type.isNotEmptyString(params.URL_GROUPS_LIST))
															{
																top.location.href = params.URL_GROUPS_LIST;
															}
														}
														else if (
															typeof deleteResponseData.MESSAGE != 'undefined'
															&& deleteResponseData.MESSAGE == 'ERROR'
															&& typeof deleteResponseData.ERROR_MESSAGE != 'undefined'
															&& deleteResponseData.ERROR_MESSAGE.length > 0
														)
														{
															this.showError(deleteResponseData.ERROR_MESSAGE, BX('bx-group-delete-request-error'));
														}
													}, this),
													onfailure: BX.delegate(function (deleteResponseData) {
														this.showError(BX.message('SONET_EXT_COMMON_AJAX_ERROR'), BX('bx-group-delete-request-error'));
														this.hideButtonWait(_currentTarget);
													}, this)
												});

											}, this)
										},
										text: BX.message(isProject ? 'SONET_EXT_COMMON_RECALL_JOIN_POPUP_BUTTON_PROJECT' : 'SONET_EXT_COMMON_RECALL_JOIN_POPUP_BUTTON')
									})
								]
							})
						]
					})
				]}),
			closeByEsc: true,
			closeIcon: true
		});
		successPopup.show();
	},

	showGroupMenuPopup: function(params)
	{
		var
			bindElement = params.bindElement,
			currentUserId = parseInt(BX.message('USER_ID')),
			sonetGroupMenu = BX.SocialnetworkUICommon.SonetGroupMenu.getInstance();

		if (BX(bindElement).tagName == 'BUTTON')
		{
			BX.addClass(bindElement, "ui-btn-active");
		}

		var menu = [];

		if (currentUserId > 0)
		{
			menu.push({
				text : BX.message(!!sonetGroupMenu.favoritesValue ? "SONET_EXT_COMMON_GROUP_MENU_FAVORITES_REMOVE" : "SONET_EXT_COMMON_GROUP_MENU_FAVORITES_ADD"),
				title : BX.message(!!sonetGroupMenu.favoritesValue ? "SONET_EXT_COMMON_GROUP_MENU_FAVORITES_REMOVE" : "SONET_EXT_COMMON_GROUP_MENU_FAVORITES_ADD"),
				id: "set-group-favorite",
				onclick : BX.delegate(function(event) {

					var favoritesValue = sonetGroupMenu.favoritesValue;

					sonetGroupMenu.setItemTitle(!favoritesValue);
					sonetGroupMenu.favoritesValue = !favoritesValue;

					BX.onCustomEvent(window, 'BX.Socialnetwork.WorkgroupMenu:onSetFavorites', [{
						groupId: params.groupId,
						value: !favoritesValue
					}]);

					this.setFavoritesAjax({
						groupId: params.groupId,
						favoritesValue: favoritesValue,
						callback: {
							success: function(data)
							{
								BX.onCustomEvent(window, 'BX.Socialnetwork.WorkgroupFavorites:onSet', [{
									id: params.groupId,
									name: data.NAME,
									url: data.URL,
									extranet: (typeof data.EXTRANET != 'undefined' ? data.EXTRANET : 'N')
								}, !favoritesValue]);
							},
							failure: function(data)
							{
								sonetGroupMenu.favoritesValue = favoritesValue;
								sonetGroupMenu.setItemTitle(favoritesValue);

								BX.onCustomEvent(window, 'BX.Socialnetwork.WorkgroupMenu:onSetFavorites', [{
									groupId: params.groupId,
									value: favoritesValue
								}]);
							}
						}
					});
				}, this)
			});

			if (params.perms.canInitiate)
			{
				menu.push({
					text: BX.message(!!params.isProject ? 'SONET_EXT_COMMON_GROUP_MENU_REQU_PROJECT' : 'SONET_EXT_COMMON_GROUP_MENU_REQU'),
					title: BX.message(!!params.isProject ? 'SONET_EXT_COMMON_GROUP_MENU_REQU_PROJECT' : 'SONET_EXT_COMMON_GROUP_MENU_REQU'),
					href: params.urls.requestUser
				});
			}

			if (params.perms.canModify)
			{
				menu.push({
					text: BX.message(!!params.isProject ? 'SONET_EXT_COMMON_GROUP_MENU_EDIT_PROJECT' : 'SONET_EXT_COMMON_GROUP_MENU_EDIT'),
					title: BX.message(!!params.isProject ? 'SONET_EXT_COMMON_GROUP_MENU_EDIT_PROJECT' : 'SONET_EXT_COMMON_GROUP_MENU_EDIT'),
					href: params.urls.edit
				});

				if (!params.hideArchiveLinks)
				{
					var featuresItem = {
						text: BX.message('SONET_EXT_COMMON_GROUP_MENU_FEAT'),
						title : BX.message('SONET_EXT_COMMON_GROUP_MENU_FEAT')
					};

					if (params.editFeaturesAllowed)
					{
						featuresItem.href = params.urls.features;
					}
					else
					{
						featuresItem.onclick = function() {
							B24.licenseInfoPopup.show('sonetGroupFeatures', BX.message('SONET_EXT_COMMON_B24_SONET_GROUP_FEATURES_TITLE'), '<span>' + BX.message('SONET_EXT_COMMON_B24_SONET_GROUP_FEATURES_TEXT') + '</span>', true);
						};
					}
					menu.push(featuresItem);
				}

				menu.push({
					text: BX.message(!!params.isProject ? 'SONET_EXT_COMMON_GROUP_MENU_DELETE_PROJECT' : 'SONET_EXT_COMMON_GROUP_MENU_DELETE'),
					title: BX.message(!!params.isProject ? 'SONET_EXT_COMMON_GROUP_MENU_DELETE_PROJECT' : 'SONET_EXT_COMMON_GROUP_MENU_DELETE'),
					href: params.urls.delete
				});
			}

			menu.push({
				text: BX.message(params.perms.canModerate ? 'SONET_EXT_COMMON_GROUP_MENU_MEMBERS_EDIT' : 'SONET_EXT_COMMON_GROUP_MENU_MEMBERS_VIEW'),
				title: BX.message(params.perms.canModerate ? 'SONET_EXT_COMMON_GROUP_MENU_MEMBERS_EDIT' : 'SONET_EXT_COMMON_GROUP_MENU_MEMBERS_VIEW'),
				href : params.urls.members
			});

			if (params.perms.canInitiate)
			{
				menu.push({
					text: BX.message('SONET_EXT_COMMON_GROUP_MENU_REQ_IN'),
					title: BX.message('SONET_EXT_COMMON_GROUP_MENU_REQ_IN'),
					href: params.urls.requests
				});
				menu.push({
					text: BX.message(!!params.isProject ? 'SONET_EXT_COMMON_GROUP_MENU_REQ_OUT_PROJECT' : 'SONET_EXT_COMMON_GROUP_MENU_REQ_OUT'),
					title: BX.message(!!params.isProject ? 'SONET_EXT_COMMON_GROUP_MENU_REQ_OUT_PROJECT' : 'SONET_EXT_COMMON_GROUP_MENU_REQ_OUT'),
					href: params.urls.requestsOut
				});
			}

			if (
				(
					!BX.type.isNotEmptyString(params.userRole)
					|| (
						params.userRole == BX.message('USER_TO_GROUP_ROLE_REQUEST')
						&& params.initiatedByType == BX.message('USER_TO_GROUP_INITIATED_BY_GROUP')
					)
				)
				&& !params.hideArchiveLinks
			)
			{
				var userRequestItem = {
					text: BX.message(!!params.isProject ? 'SONET_EXT_COMMON_GROUP_MENU_JOIN_PROJECT' : 'SONET_EXT_COMMON_GROUP_MENU_JOIN'),
					title: BX.message(!!params.isProject ? 'SONET_EXT_COMMON_GROUP_MENU_JOIN_PROJECT' : 'SONET_EXT_COMMON_GROUP_MENU_JOIN')
				};

				if (!!params.isOpened)
				{
					userRequestItem.onclick = BX.delegate(function() {

						BX.SocialnetworkUICommon.Waiter.getInstance().show();
						BX.SocialnetworkUICommon.SonetGroupMenu.getInstance().menuPopup.close();

						BX.ajax({
							url: params.urls.userRequestGroup,
							method: 'POST',
							dataType: 'json',
							data: {
								groupID: params.groupId,
								MESSAGE: '',
								ajax_request: 'Y',
								save: 'Y',
								sessid: BX.bitrix_sessid()
							},
							onsuccess: BX.delegate(function(responseData) {
								BX.SocialnetworkUICommon.Waiter.getInstance().hide();
								if (
									typeof responseData.MESSAGE != 'undefined'
									&& responseData.MESSAGE == 'SUCCESS'
									&& typeof responseData.URL != 'undefined'
								)
								{
									BX.onCustomEvent(window.top, 'sonetGroupEvent', [ {
										code: 'afterJoinRequestSend',
										data: {
											groupId: this.groupId
										}
									} ]);
									top.location.href = responseData.URL;
								}
							}, this),
							onfailure: BX.delegate(function() {
								BX.SocialnetworkUICommon.Waiter.getInstance().hide();
							}, this)
						});
					}, this);
				}
				else
				{
					userRequestItem.href = params.urls.userRequestGroup;
				}
				menu.push(userRequestItem);
			}

			if (
				params.userIsMember
				&& !params.userIsAutoMember
				&& params.userRole != BX.message('USER_TO_GROUP_ROLE_OWNER')
			)
			{
				menu.push({
					text: BX.message(!!params.isProject ? 'SONET_EXT_COMMON_GROUP_MENU_EXIT_PROJECT' : 'SONET_EXT_COMMON_GROUP_MENU_EXIT'),
					title: BX.message(!!params.isProject ? 'SONET_EXT_COMMON_GROUP_MENU_EXIT_PROJECT' : 'SONET_EXT_COMMON_GROUP_MENU_EXIT'),
					href: params.urls.userLeaveGroup
				});
			}
		}

		var popup = BX.PopupMenu.create("group-profile-menu", bindElement, menu, {
			offsetTop: 5,
			offsetLeft : (bindElement.offsetWidth - 18),
			angle : true,
			events : {
				onPopupClose : function() {
					if (BX(bindElement).tagName == 'BUTTON')
					{
						BX.removeClass(bindElement, "ui-btn-active");
					}
				}
			}
		});

		var item = popup.getMenuItem("set-group-favorite");
		if (item)
		{
			sonetGroupMenu.menuItem = item.layout.text;
		}

		popup.popupWindow.show();
		sonetGroupMenu.menuPopup = popup;
	},

	setFavoritesAjax: function(params)
	{
		BX.ajax({
			url: '/bitrix/components/bitrix/socialnetwork.group_menu/ajax.php',
			method: 'POST',
			dataType: 'json',
			data: {
				groupID: params.groupId,
				action: (params.favoritesValue ? 'fav_unset' : 'fav_set'),
				sessid: BX.bitrix_sessid(),
				lang: BX.message('LANGUAGE_ID')
			},
			onsuccess: function(data) {
				if (
					typeof data.SUCCESS != 'undefined'
					&& data.SUCCESS == "Y"
				)
				{
					params.callback.success(data);
				}
				else
				{
					params.callback.failure(data);
				}
			},
			onfailure: function(data) {
				params.callback.failure(data);
			}
		});
	},

	showButtonWait: function(buttonNode)
	{
		buttonNode = BX(buttonNode);
		if (buttonNode)
		{
			BX.addClass(buttonNode, 'ui-btn-clock');
			BX.addClass(buttonNode, 'ui-btn-disabled');
			buttonNode.disabled = true;
			buttonNode.style.cursor = 'auto';
		}
	},

	hideButtonWait: function(buttonNode)
	{
		buttonNode = BX(buttonNode);
		if (buttonNode)
		{
			BX.removeClass(buttonNode, 'ui-btn-clock');
			BX.removeClass(buttonNode, 'ui-btn-disabled');
			buttonNode.disabled = false;
			buttonNode.style.cursor = 'cursor';
		}
	},

	showError: function(errorText, errorNode)
	{
		if (BX(errorNode))
		{
			BX(errorNode).innerHTML = errorText;
			BX.removeClass(BX(errorNode), 'sonet-ui-form-error-block-invisible');
		}
	},

	hideError: function(errorNode)
	{
		if (BX(errorNode))
		{
			BX.addClass(BX(errorNode), 'sonet-ui-form-error-block-invisible');
		}
	},

	reload: function()
	{
		if (top !== window) // current page in slider
		{
			if (typeof top.BX.SidePanel != 'undefined')
			{
				top.BX.SidePanel.Instance.getSliderByWindow(window).showLoader();
			}
			window.location.reload();
		}
		else if (
			typeof top.BX.SidePanel != 'undefined'
			&& top.BX.SidePanel.Instance.isOpen()
		) // there's an open slider
		{
			top.location.href = top.BX.SidePanel.Instance.getPageUrl();
		}
		else
		{
			top.location.reload();
		}
	},

	reloadBlock: function(params)
	{
		if (
			typeof params == 'undefined'
			|| !BX.type.isNotEmptyString(params.blockId)
			|| !BX(params.blockId)
		)
		{
			return;
		}

		var url = '';

		if (
			typeof top.BX.SidePanel != 'undefined'
			&& top.BX.SidePanel.Instance.isOpen()
		) // there's an open slider
		{
			url = top.BX.SidePanel.Instance.getPageUrl();
		}
		else
		{
			url = window.location.href;
		}

		BX.ajax.promise({
			url: url,
			method: 'POST',
			dataType: 'json',
			data: {
				BLOCK_RELOAD: 'Y',
				BLOCK_ID: params.blockId
			}
		}).then(BX.delegate(function(data) {
			if (
				typeof data != 'undefined'
				&& typeof data.CONTENT != 'undefined'
			)
			{
				BX(params.blockId).innerHTML = data.CONTENT;
				setTimeout(function() {
					BX.ajax.processRequestData(data.CONTENT, {
						dataType: 'HTML'
					});
				}, 0);
			}
		}, this));
	},

	closeGroupCardMenu: function(node)
	{
		if (!node)
		{
			return;
		}

		var doc = node.ownerDocument;
		var win = doc.defaultView || doc.parentWindow;

		if (
			!win
			|| typeof win.BX.SocialnetworkUICommon.SonetGroupMenu == 'undefined'
			|| !win.BX.SocialnetworkUICommon.SonetGroupMenu.getInstance().menuPopup
		)
		{
			return;
		}

		win.BX.SocialnetworkUICommon.SonetGroupMenu.getInstance().menuPopup.close();
	}
};

BX.SocialnetworkUICommon.Waiter = function()
{
	this.instance = null;
	this.waitTimeout = null;
	this.waitPopup = null;
};

BX.SocialnetworkUICommon.Waiter.getInstance = function()
{
	if (BX.SocialnetworkUICommon.Waiter.instance == null)
	{
		BX.SocialnetworkUICommon.Waiter.instance = new BX.SocialnetworkUICommon.Waiter();
	}

	return BX.SocialnetworkUICommon.Waiter.instance;
};

BX.SocialnetworkUICommon.Waiter.prototype = {

	show: function(timeout)
	{
		if (timeout !== 0)
		{
			return (this.waitTimeout = setTimeout(BX.proxy(function(){
				this.show(0)
			}, this), 50));
		}

		if (!this.waitPopup)
		{
			this.waitPopup = new BX.PopupWindow('sonet_common_wait_popup', window, {
				autoHide: true,
				lightShadow: true,
				zIndex: 2,
				content: BX.create('DIV', {
					props: {
						className: 'sonet-wait-cont'
					},
					children: [
						BX.create('DIV', {
							props: {
								className: 'sonet-wait-icon'
							}
						}),
						BX.create('DIV', {
							props: {
								className: 'sonet-wait-text'
							},
							html: BX.message('SONET_EXT_COMMON_WAIT')
						})
					]
				})
			});
		}
		else
		{
			this.waitPopup.setBindElement(window);
		}

		this.waitPopup.show();
	},

	hide: function()
	{
		if (this.waitTimeout)
		{
			clearTimeout(this.waitTimeout);
				this.waitTimeout = null;
		}

		if (this.waitPopup)
		{
			this.waitPopup.close();
		}
	}
};


BX.SocialnetworkUICommon.SonetGroupMenu = function()
{
	this.favoritesValue = null;
	this.instance = null;
	this.menuPopup = null;
	this.menuItem = null;
};

BX.SocialnetworkUICommon.SonetGroupMenu.getInstance = function()
{
	if (BX.SocialnetworkUICommon.SonetGroupMenu.instance == null)
	{
		BX.SocialnetworkUICommon.SonetGroupMenu.instance = new BX.SocialnetworkUICommon.SonetGroupMenu();

		BX.addCustomEvent("SidePanel.Slider:onClose", function(event) {
			if (BX.SocialnetworkUICommon.SonetGroupMenu.instance.menuPopup)
			{
				BX.SocialnetworkUICommon.SonetGroupMenu.instance.menuPopup.close();
			}
		});
	}

	return BX.SocialnetworkUICommon.SonetGroupMenu.instance;
};

BX.SocialnetworkUICommon.SonetGroupMenu.prototype = {
	setItemTitle: function(value)
	{
		if (this.menuItem)
		{
			BX(this.menuItem).innerHTML = BX.message(!!value ? 'SONET_EXT_COMMON_GROUP_MENU_FAVORITES_REMOVE' : 'SONET_EXT_COMMON_GROUP_MENU_FAVORITES_ADD');
		}
	}
};

})();
