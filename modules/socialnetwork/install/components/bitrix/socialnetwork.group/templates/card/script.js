;(function(){

if (typeof SonetGroupCardSlider != 'undefined')
{
	return;
}

SonetGroupCardSlider = function()
{
	this.instance = null;
	this.currentUserId = null;
	this.userRole = null;
	this.canInitiate = null;
	this.canModify = null;
	this.groupId = null;
	this.isProject = null;
	this.waitPopup = null;
	this.waitTimeout = null;
	this.notifyHintPopup = null;
	this.notifyHintTimeout = null;
	this.notifyHintTime = 3000;
	this.favoritesValue = null;
	this.styles = null;
	this.urls = null;
	this.containerNodeId = null;
	this.subscribeButtonNodeId = null;
	this.menuButtonNodeId = null;
	this.editFeaturesAllowed = true;
};

SonetGroupCardSlider.getInstance = function()
{
	if (SonetGroupCardSlider.instance == null)
	{
		SonetGroupCardSlider.instance = new SonetGroupCardSlider();
	}

	return SonetGroupCardSlider.instance;
};

SonetGroupCardSlider.prototype = {

	init: function(params)
	{
		if (
			typeof params == 'undefined'
			|| typeof params.groupId == 'undefined'
			|| parseInt(params.groupId) <= 0
		)
		{
			return;
		}

		this.currentUserId = parseInt(params.currentUserId);
		this.groupId = parseInt(params.groupId);
		this.isProject = !!params.isProject;
		this.isOpened = !!params.isOpened;
		this.favoritesValue = !!params.favoritesValue;
		this.canInitiate = !!params.canInitiate;
		this.canModify = !!params.canModify;
		this.userRole = params.userRole;
		this.userIsMember = !!params.userIsMember;
		this.userIsAutoMember = !!params.userIsAutoMember;
		this.containerNodeId = (BX.type.isNotEmptyString(params.containerNodeId) ? params.containerNodeId : null);
		this.subscribeButtonNodeId = (BX.type.isNotEmptyString(params.subscribeButtonNodeId) ? params.subscribeButtonNodeId : null);
		this.menuButtonNodeId = (BX.type.isNotEmptyString(params.menuButtonNodeId) ? params.menuButtonNodeId : null);
		this.editFeaturesAllowed = (typeof params.editFeaturesAllowed != 'undefined' ? !!params.editFeaturesAllowed : true);

		if (
			this.containerNodeId
			&& BX(this.containerNodeId)
			&& typeof params.styles != 'undefined'
		)
		{
			this.styles = params.styles;
			var i = null;

			if (
				typeof params.styles.tags != 'undefined'
				&& BX.type.isNotEmptyString(params.styles.tags.box)
				&& BX.type.isNotEmptyString(params.styles.tags.item)
			)
			{
				var tagBlockList = BX.findChildren(BX(this.containerNodeId), {
					className: params.styles.tags.box
				}, true);

				for (i = 0, length = tagBlockList.length; i < length; i++)
				{
					BX(tagBlockList[i]).addEventListener('click', BX.delegate(function(e) {
						var tagValue = BX.getEventTarget(e).getAttribute('bx-tag-value');
						if (BX.type.isNotEmptyString(tagValue))
						{
							this.clickTag(tagValue);
						}
						e.preventDefault();
					}, this), true);
				}
			}

			if (
				typeof params.styles.users != 'undefined'
				&& BX.type.isNotEmptyString(params.styles.users.box)
				&& BX.type.isNotEmptyString(params.styles.users.item)
			)
			{
				var userBlockList = BX.findChildren(BX(this.containerNodeId), {
					className: params.styles.users.box
				}, true);

				for (i = 0, length = userBlockList.length; i < length; i++)
				{
					BX(userBlockList[i]).addEventListener('click', BX.delegate(function(e) {
						var userId = BX.getEventTarget(e).getAttribute('bx-user-id');
						if (parseInt(userId) > 0)
						{
							this.clickUser(userId);
						}
						e.preventDefault();
					}, this), true);
				}
			}

			if (
				typeof params.styles.fav != 'undefined'
				&& BX.type.isNotEmptyString(params.styles.fav.switch)
			)
			{
				var favBlockList = BX.findChildren(BX(this.containerNodeId), {
					className: params.styles.fav.switch
				}, true);

				for (i = 0, length = favBlockList.length; i < length; i++)
				{
					BX(favBlockList[i]).addEventListener('click', BX.delegate(function(e) {
						this.setFavorites(e);
					}, this), true);
				}
			}
		}

		if (typeof params.urls != 'undefined')
		{
			this.urls = params.urls;
		}

		if (
			this.subscribeButtonNodeId
			&& BX(this.subscribeButtonNodeId)
		)
		{
			BX.bind(BX(this.subscribeButtonNodeId), 'click', BX.delegate(function(event) {
				this.setSubscribe();
				event.preventDefault();
			}, this));
		}

		if (
			this.menuButtonNodeId
			&& BX(this.menuButtonNodeId)
		)
		{
			var sonetGroupMenu = BX.SocialnetworkUICommon.SonetGroupMenu.getInstance();
			sonetGroupMenu.favoritesValue = this.favoritesValue;

			BX.bind(BX(this.menuButtonNodeId), 'click', BX.delegate(function(event) {

				BX.SocialnetworkUICommon.showGroupMenuPopup({
					bindElement: BX(this.menuButtonNodeId),
					groupId: this.groupId,
					userIsMember: this.userIsMember,
					userIsAutoMember: this.userIsAutoMember,
					userRole: this.userRole,
					editFeaturesAllowed: this.editFeaturesAllowed,
					isProject: this.isProject,
					isOpened: this.isOpened,
					perms: {
						canInitiate: this.canInitiate,
						canModify: this.canModify
					},
					urls: {
						requestUser: BX.message('SGCSPathToRequestUser'),
						edit: BX.message('SGCSPathToEdit'),
						delete: BX.message('SGCSPathToDelete'),
						features: BX.message('SGCSPathToFeatures'),
						members: BX.message('SGCSPathToMembers'),
						requests: BX.message('SGCSPathToRequests'),
						requestsOut: BX.message('SGCSPathToRequestsOut'),
						userRequestGroup: BX.message('SGCSPathToUserRequestGroup'),
						userLeaveGroup: BX.message('SGCSPathToUserLeaveGroup')
					}
				});

				event.preventDefault();
			}, this));
		}

		BX.addCustomEvent('SidePanel.Slider:onMessage', BX.delegate(function(event){
			if (event.getEventId() == 'sonetGroupEvent')
			{
				var eventData = event.getData();
				if (
					BX.type.isNotEmptyString(eventData.code)
					&& typeof eventData.data != 'undefined'
				)
				{
					if (
						eventData.code == 'afterEdit'
						&& typeof eventData.data.group != 'undefined'
						&& parseInt(eventData.data.group.ID) == this.groupId
					)
					{
						BX.SocialnetworkUICommon.reload();
					}
					else if (
						BX.util.in_array(eventData.code, [ 'afterDelete', 'afterLeave' ])
						&& typeof eventData.data.groupId != 'undefined'
						&& parseInt(eventData.data.groupId) == this.groupId
					)
					{
						if (window !== top.window) // frame
						{
							top.BX.SidePanel.Instance.getSliderByWindow(window).close();
						}
						top.location.href = this.urls.groupsList;
					}
				}
			}
		}, this));

		BX.addCustomEvent(window, "BX.Socialnetwork.WorkgroupMenu:onSetFavorites", BX.delegate(function(eventParams) {
			this.favoritesValue = eventParams.value;
			if (eventParams.groupId = this.groupId)
			{
				var targetNode = BX.findChild(BX(this.containerNodeId), {
					className: this.styles.fav.switch
				}, true);

				this.switchFavorites(targetNode, eventParams.value)
			}
		}, this));
	},

	setSubscribe: function()
	{
		var action = (!BX.hasClass(this.subscribeButtonNodeId, "ui-btn-active") ? "set" : "unset");
		this.switchSubscribe(this.subscribeButtonNodeId, (action == 'set'));

		BX.ajax({
			url: '/bitrix/components/bitrix/socialnetwork.group_menu/ajax.php',
			method: 'POST',
			dataType: 'json',
			data: {
				groupID: this.groupId,
				action: (action == 'set' ? 'set' : 'unset'),
				sessid: BX.bitrix_sessid()
			},
			onsuccess: BX.delegate(function(data) {
				if (
					typeof data.SUCCESS != 'undefined'
					&& data.SUCCESS == "Y"
				)
				{
					var eventData = {
						code: 'afterSetSubscribe',
						data: {
							groupId: this.groupId,
							value: (data.RESULT == 'Y')
						}
					};
					window.top.BX.onCustomEvent(window.top, 'sonetGroupEvent', [eventData]);
					window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', eventData);
				}
				else if (BX.type.isNotEmptyString(data.ERROR))
				{
					this.switchSubscribe(this.subscribeButtonNodeId, !(action == 'set'));
					this.processAJAXError(data.ERROR);
				}
			}, this),
			onerror: BX.delegate(function(data) {
				this.switchSubscribe(this.subscribeButtonNodeId, !(action == 'set'));
			}, this)
		});
	},

	setFavorites: function(event)
	{
		var _this = this;

		var currentValue = _this.favoritesValue;
		var newValue = !currentValue;
		var sonetGroupMenu = BX.SocialnetworkUICommon.SonetGroupMenu.getInstance();

		_this.favoritesValue = newValue;

		sonetGroupMenu.favoritesValue = newValue;
		sonetGroupMenu.setItemTitle(newValue);

		var targetNode = (
			BX.hasClass(BX.getEventTarget(event), 'socialnetwork-group-fav-switch') // star block
				? BX.getEventTarget(event)
				: null
		);

		if (!targetNode)
		{
			targetNode = BX.findChild(BX(this.containerNodeId), {
				className: this.styles.fav.switch
			}, true);
		}

		if (targetNode)
		{
			BX.delegate(function() {
				this.switchFavorites(targetNode, newValue)
			}, _this)();
		}

		BX.SocialnetworkUICommon.setFavoritesAjax({
			groupId: _this.groupId,
			favoritesValue: currentValue,
			callback: {
				success: function(data) {

					var eventData = {
						code: 'afterSetFavorites',
						data: {
							groupId: data.ID,
							value: (data.RESULT == 'Y')
						}
					};

					window.top.BX.onCustomEvent(window.top, 'sonetGroupEvent', [eventData]);
					window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', eventData);

					if (
						typeof data.NAME != 'undefined'
						&& typeof data.URL != 'undefined'
					)
					{
						BX.onCustomEvent(window, 'BX.Socialnetwork.WorkgroupFavorites:onSet', [{
							id: _this.groupId,
							name: data.NAME,
							url: data.URL,
							extranet: (typeof data.EXTRANET != 'undefined' ? data.EXTRANET : 'N')
						}, newValue]);
					}
				},
				failure: function(data) {

					_this.favoritesValue = currentValue;
					sonetGroupMenu.favoritesValue = currentValue;
					sonetGroupMenu.setItemTitle(currentValue);

					if (BX.type.isNotEmptyString(data.ERROR))
					{
						_this.processAJAXError(data.ERROR);
					}

					BX.delegate(function() {
						_this.switchFavorites(targetNode, currentValue)
					}, _this);
				}
			}
		});

		event.preventDefault();
	},

	switchFavorites: function(node, active)
	{
		if (
			BX(node)
			&& typeof this.styles.fav != 'undefined'
			&& BX.type.isNotEmptyString(this.styles.fav.activeSwitch)
		)
		{
			if (active)
			{
				BX.addClass(BX(node), this.styles.fav.activeSwitch);
			}
			else
			{
				BX.removeClass(BX(node), this.styles.fav.activeSwitch);
			}
		}
	},

	switchSubscribe: function(node, active)
	{
		if (BX(node))
		{
			if (!!active)
			{
				BX.addClass(BX(node), 'ui-btn-active');
				BX.removeClass(BX(node), 'ui-btn-icon-follow');
				BX.addClass(BX(node), 'ui-btn-icon-unfollow');
				BX(node).innerHTML = BX.message('SGCSSubscribeTitleY');

				this.showNotifyHint(BX(node), BX.message('SGCSSubscribeButtonHintOn'));
			}
			else
			{
				BX.removeClass(BX(node), 'ui-btn-active');
				BX.removeClass(BX(node), 'ui-btn-icon-unfollow');
				BX.addClass(BX(node), 'ui-btn-icon-follow');
				BX(node).innerHTML = BX.message('SGCSSubscribeTitleN');

				this.showNotifyHint(BX(node), BX.message('SGCSSubscribeButtonHintOff'));
			}
		}
	},

	processAJAXError: function(errorCode)
	{
		var _this = this;

		if (errorCode.indexOf("SESSION_ERROR", 0) === 0)
		{
			_this.showError(BX.message('SGMErrorSessionWrong'));
			return false;
		}
		else if (errorCode.indexOf("CURRENT_USER_NOT_AUTH", 0) === 0)
		{
			_this.showError(BX.message('SGMErrorCurrentUserNotAuthorized'));
			return false;
		}
		else if (errorCode.indexOf("SONET_MODULE_NOT_INSTALLED", 0) === 0)
		{
			_this.showError(BX.message('SGMErrorModuleNotInstalled'));
			return false;
		}
		else
		{
			_this.showError(errorCode);
			return false;
		}
	},

	showWait : function(timeout)
	{
		var _this = this;

		if (timeout !== 0)
		{
			return (_this.waitTimeout = setTimeout(function(){
				_this.showWait(0)
			}, 300));
		}

		if (!_this.waitPopup)
		{
			_this.waitPopup = new BX.PopupWindow('socialnetwork-group-wait', window, {
				autoHide: true,
				lightShadow: true,
				zIndex: 2,
				content: BX.create('DIV', {
					props: {
						className: 'socialnetwork-group-wait-cont'
					},
					children: [
						BX.create('DIV', {
							props: {
								className: 'socialnetwork-group-wait-icon'
							}
						}),
						BX.create('DIV', {
							props: {
								className: 'socialnetwork-group-wait-text'
							},
							html: BX.message('SGCSWaitTitle')
						})
					]
				})
			});
		}
		else
		{
			_this.waitPopup.setBindElement(window);
		}

		_this.waitPopup.show();
	},

	closeWait: function()
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
	},

	showNotifyHint: function(el, hint_text)
	{
		var _this = this;

		if (_this.notifyHintTimeout)
		{
			clearTimeout(_this.notifyHintTimeout);
			_this.notifyHintTimeout = null;
		}

		if (_this.notifyHintPopup == null)
		{
			_this.notifyHintPopup = new BX.PopupWindow('sgm_notify_hint', el, {
				autoHide: true,
				lightShadow: true,
				zIndex: 2,
				content: BX.create('DIV', {
					props: {
						className: 'sonet-sgm-notify-hint-content'
					},
					style: {
						display: 'none'
					},
					children: [
						BX.create('SPAN', {
							props: {
								id: 'sgm_notify_hint_text'
							},
							html: hint_text
						})
					]
				}),
				closeByEsc: true,
				closeIcon: false,
				offsetLeft: 21,
				offsetTop: 2
			});

			_this.notifyHintPopup.TEXT = BX('sgm_notify_hint_text');
			_this.notifyHintPopup.setBindElement(el);
		}
		else
		{
			_this.notifyHintPopup.TEXT.innerHTML = hint_text;
			_this.notifyHintPopup.setBindElement(el);
		}

		_this.notifyHintPopup.setAngle({});
		_this.notifyHintPopup.show();

		_this.notifyHintTimeout = setTimeout(function() {
			_this.notifyHintPopup.close();
		}, _this.notifyHintTime);
	},

	showError: function(errorText)
	{
		this.closeWait();

		var errorPopup = new BX.PopupWindow('sgm-error' + Math.random(), window, {
			autoHide: true,
			lightShadow: false,
			zIndex: 2,
			content: BX.create('DIV', {props: {'className': 'sonet-sgm-error-text-block'}, html: errorText}),
			closeByEsc: true,
			closeIcon: true
		});
		errorPopup.show();
	},

	clickTag: function(tagValue)
	{
		if (tagValue.length > 0)
		{
			top.location.href = BX.message('SGCSPathToGroupTag').replace('#tag#', tagValue);
		}
	},

	clickUser: function(userId)
	{
		if (parseInt(userId) > 0)
		{
			top.location.href = BX.message('SGCSPathToUserProfile').replace('#user_id#', userId);
		}
	}

};

})();