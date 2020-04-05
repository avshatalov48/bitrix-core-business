var ECUserControll = function(params)
{
	this.oEC = params.oEC;
	var _this = this;
	this.count = 0;
	this.countAgr = 0;
	this.countDec = 0;

	this.bEditMode = params.view !== true;
	this.pAttendeesCont = params.AttendeesCont;
	this.pAttendeesList = params.AttendeesList;
	this.pParamsCont = params.AdditionalParams;
	this.pSummary = params.SummaryCont;

	this.pCount = this.pSummary.appendChild(BX.create("A", {props: {className: 'bxc-count', href:"javascript:void(0)"}}));
	this.pCountArg = this.pSummary.appendChild(BX.create("A", {props: {className: 'bxc-count-agr', href:"javascript:void(0)"}}));
	this.pCountDec = this.pSummary.appendChild(BX.create("A", {props: {className: 'bxc-count-dec', href:"javascript:void(0)"}}));

	this.pCount.onclick = function(){_this.ListMode('all');};
	this.pCountArg.onclick = function(){_this.ListMode('agree');};
	this.pCountDec.onclick = function(){_this.ListMode('decline');};

	this._getFromDate = (params.fromDateGetter && typeof params.fromDateGetter == 'function') ? params.fromDateGetter : function(){return false;};
	this._getToDate = (params.toDateGetter && typeof params.toDateGetter == 'function') ? params.toDateGetter : function(){return false;};
	this._getEventId = (params.eventIdGetter && typeof params.eventIdGetter == 'function') ? params.eventIdGetter : function(){return false;};

	this.ListMode('all');
	this.Attendees = {};

	// Only if we need to add or delete users
	if (this.bEditMode)
	{
		this.pLinkCont = params.AddLinkCont;
		var
			pIcon = this.pLinkCont.appendChild(BX.create("I")),
			pTitle = this.pLinkCont.appendChild(BX.create("SPAN", {text: EC_MESS.AddAttendees}));
		pIcon.onclick = pTitle.onclick = BX.proxy(this.OpenSelectUser, this);

		var arMenuItems = [{text : EC_MESS.AddGuestsDef, onclick: BX.proxy(this.OpenSelectUser, this)}];
		if (!this.oEC.bExtranet && this.oEC.type == 'group')
			arMenuItems.push({text : EC_MESS.AddGroupMemb, title: EC_MESS.AddGroupMembTitle, onclick: BX.proxy(this.oEC.AddGroupMembers, this.oEC)});

		if (arMenuItems.length > 1)
		{
			pMore = this.pLinkCont.appendChild(BX.create("A", {props: {href: 'javascript: void(0);', className: 'bxec-add-more'}}));
			pMore.onclick = function()
			{
				BX.PopupMenu.show('bxec_add_guest_menu', _this.pLinkCont, arMenuItems, {events: {onPopupClose: function() {BX.removeClass(pMore, "bxec-add-more-over");}}});
				BX.addClass(pMore, "bxec-add-more-over");
			};
		}

		BX.addCustomEvent(window, "onUserSelectorOnChange", BX.proxy(this.UserOnChange, this));
	}
};

ECUserControll.prototype = {
SetValues: function(Attendees)
{
	var i, l = Attendees.length, User;

	// Clear list
	BX.cleanNode(this.pAttendeesList);
	this.Attendees = {};
	this.count = 0;
	this.countAgr = 0;
	this.countDec = 0;

	for(i = 0; i < l; i++)
	{
		User = Attendees[i];
		User.key = User.id || User.email;
		if (User && User.key && !this.Attendees[User.key])
			this.DisplayAttendee(User);
	}

	if (this.bEditMode)
	{
		this.DisableUserOnChange(true, true);
		O_BXCalUserSelect.setSelected(Attendees);
	}

	this.UpdateCount();
},

GetValues: function()
{
	return this.Attendees;
},

SetEmpty: function(bEmpty)
{
	if (this.bEmpty === bEmpty)
		return;

	BX.onCustomEvent(this, 'SetEmpty', [bEmpty]);

	if (bEmpty)
	{
		BX.addClass(this.pAttendeesCont, 'bxc-att-empty');
		if (this.pParamsCont)
			this.pParamsCont.style.display = 'none';
	}
	else
	{
		BX.removeClass(this.pAttendeesCont, 'bxc-att-empty');
		if (this.pParamsCont)
			this.pParamsCont.style.display = '';
	}
	this.bEmpty = bEmpty;
},

UpdateCount: function()
{
	this.pCount.innerHTML = EC_MESS.AttSumm + ' - ' + (parseInt(this.count) || 0);

	if (this.countAgr > 0)
	{
		this.pCountArg.innerHTML = EC_MESS.AttAgr + ' - ' + parseInt(this.countAgr);
		this.pCountArg.style.display = "";
	}
	else
	{
		this.pCountArg.style.display = "none";
	}

	if (this.countDec > 0)
	{
		this.pCountDec.innerHTML = EC_MESS.AttDec + ' - ' + parseInt(this.countDec);
		this.pCountDec.style.display = "";
	}
	else
	{
		this.pCountDec.style.display = "none";
	}

	this.SetEmpty(this.count == 0);
},

OpenSelectUser : function(e)
{
	if (BX.PopupMenu && BX.PopupMenu.currentItem)
		BX.PopupMenu.currentItem.popupWindow.close();

	if(!e) e = window.event;
	if (!this.SelectUserPopup)
	{
		var _this = this;
		this.SelectUserPopup = BX.PopupWindowManager.create("bxc-user-popup", this.pLinkCont, {
			offsetTop : 1,
			autoHide : true,
			closeByEsc : true,
			content : BX("BXCalUserSelect_selector_content"),
			className: 'bxc-popup-user-select',
			buttons: [
				new BX.PopupWindowButton({
					text: EC_MESS.Add,
					events: {click : function()
					{
						_this.SelectUserPopup.close();
						for (var id in _this.selectedUsers)
						{
							if (_this.selectedUsers.hasOwnProperty(id))
							{
								id = parseInt(id);
								if (!isNaN(id) && id > 0)
								{
									if (!_this.Attendees[id] && _this.selectedUsers[id]) // Add new user
									{
										_this.selectedUsers[id].key = id;
										_this.DisplayAttendee(_this.selectedUsers[id]);
									}
									else if (_this.Attendees[id] && !_this.selectedUsers[id]) // Del user from our list
									{
										_this.RemoveAttendee(id, false);
									}
								}
							}
						}

						BX.onCustomEvent(_this, 'UserOnChange');
						_this.UpdateCount();
					}}
				}),
				new BX.PopupWindowButtonLink({
					text: EC_MESS.Close,
					className: "popup-window-button-link-cancel",
					events: {click : function(){_this.SelectUserPopup.close();}}
				})
			]
		});
	}

	// Clean
	if (this.bEditMode)
	{
		this.selectedUsers = {};
		var Attendees = [], k;
		for (k in this.Attendees)
		{
			if (this.Attendees[k] && this.Attendees[k].type != 'ext')
				Attendees.push(this.Attendees[k].User);
		}
		O_BXCalUserSelect.setSelected(Attendees);
	}

	this.SelectUserPopup.show();
	BX.PreventDefault(e);
},

AddByEmail : function(e)
{
	var _this = this;
	if (BX.PopupMenu && BX.PopupMenu.currentItem)
		BX.PopupMenu.currentItem.popupWindow.close();

	if(!e) e = window.event;
	if (!this.EmailPopup)
	{
		var pDiv = BX.create("DIV", {props:{className: 'bxc-email-cont'}, html: '<label class="bxc-email-label">' + EC_MESS.UserEmail + ':</label>'});
		this.pEmailValue = pDiv.appendChild(BX.create('INPUT', {props: {className: 'bxc-email-input'}}));

		this.EmailPopup = BX.PopupWindowManager.create("bxc-user-popup-email", this.pLinkCont, {
			offsetTop : 1,
			autoHide : true,
			content : pDiv,
			className: 'bxc-popup-user-select-email',
			closeIcon: { right : "12px", top : "5px"},
			closeByEsc : true,
			buttons: [
			new BX.PopupWindowButton({
				text: EC_MESS.Add,
				className: "popup-window-button-accept",
				events: {click : function(){
					var email = BX.util.trim(_this.pEmailValue.value);
					if (email != "" && !_this.Attendees[email])
					{
						var User = {name: email, key: email, type: 'ext', status: 'Y'};
						_this.DisplayAttendee(User);
						_this.UpdateCount();
					}
					_this.EmailPopup.close();
				}}
			}),
			new BX.PopupWindowButtonLink({
				text: EC_MESS.Close,
				className: "popup-window-button-link-cancel",
				events: {click : function(){_this.EmailPopup.close();}}
			})
		]
		});
	}

	this.EmailPopup.show();
	setTimeout(function(){BX.focus(_this.pEmailValue);}, 50);
	BX.PreventDefault(e);
},

DisableUserOnChange: function(bDisable, bTime)
{
	this.bDisableUserOnChange = bDisable === true;
	if (bTime)
		setTimeout(BX.proxy(this.DisableUserOnChange, this), 300);
},

UserOnChange: function(arUsers)
{
	if (this.bDisableUserOnChange)
		return;

	this.selectedUsers = arUsers;
},

DisplayAttendee: function(User, bUpdate)
{
	this.count++;
	if (User.status == 'Y')
		this.countAgr++;
	else if (User.status == 'N')
		this.countDec++;
	else
		User.status = 'Q';

	if (bUpdate && User.id && this.Attendees[User.id])
	{
		// ?
	}
	else
	{
		var
			_this = this,
			pBusyInfo = false,
			status = User.status.toLowerCase(),
			pRow = this.pAttendeesList.appendChild(BX.create("SPAN", {props:{className: 'bxc-attendee-row bxc-att-row-' + status}})),
			pStatus = pRow.appendChild(BX.create("I", {props: {className: 'bxc-stat-' + status, title: EC_MESS['GuestStatus_' + status] + (User.desc ? ' - ' + User.desc : '')}}));

		if (User.type == 'ext')
			pName = pRow.appendChild(BX.create("span", {props:{className: "bxc-name"}, text: (User.name || User.email)}));
		else
			pName = pRow.appendChild(BX.create("A", {props:{href: this.oEC.GetUserHref(User.id), className: "bxc-name"}, text: User.name}));


		if (this.bEditMode && User.type != 'ext')
			pBusyInfo = pRow.appendChild(BX.create("SPAN", {props:{className: "bxc-busy"}}));
		pRow.appendChild(BX.create("SPAN", {props: {className: "bxc-comma"}, html: ','}));

		if (this.bEditMode)
		{
			pRow.appendChild(BX.create("A", {props: {id: 'bxc-att-key-' + User.key, href: 'javascript:void(0)', title: EC_MESS.Delete, className: 'bxc-del-att'}})).onclick = function(e){_this.RemoveAttendee(this.id.substr('bxc-att-key-'.length)); return BX.PreventDefault(e || window.event)};
		}

		this.Attendees[User.key] = {
			User : User,
			pRow: pRow,
			pBusyCont: pBusyInfo
		};
	}
},

RemoveAttendee: function(key, bAffectToControl)
{
	bAffectToControl = bAffectToControl !== false;

	if (this.Attendees[key])
	{
		this.Attendees[key].pRow.parentNode.removeChild(this.Attendees[key].pRow);

		if (this.Attendees[key].User.status == 'Y')
			this.countAgr--;
		if (this.Attendees[key].User.status == 'N')
			this.countDec--;
		this.count--;

		this.Attendees[key] = null;
		delete this.Attendees[key];

		if (this.bEditMode)
		{
			var Attendees = [];
			for (k in this.Attendees)
			{
				if (this.Attendees[k] && this.Attendees[k].type != 'ext')
					Attendees.push(this.Attendees[k].User);
			}

			this.DisableUserOnChange(true, true);

			if (bAffectToControl)
				O_BXCalUserSelect.setSelected(Attendees);
		}
	}

	this.UpdateCount();
},

ListMode: function(mode)
{
	if (this.mode == mode)
		return;

	if (this.mode) // In start
	{
		BX.removeClass(this.pAttendeesList, 'bxc-users-mode-' + this.mode);
		BX.removeClass(this.pSummary, 'bxc-users-mode-' + this.mode);
	}

	this.mode = mode;
	BX.addClass(this.pAttendeesList, 'bxc-users-mode-' + this.mode);
	BX.addClass(this.pSummary, 'bxc-users-mode-' + this.mode);
}
};

var ECSyncPannel = function(oEC)
{
	this.oEC = oEC;

	if (!this.oEC.arConfig.syncInfo)
		return;

	this.Display();

	if (!window.jsOutlookUtils)
		return BX.loadScript('/bitrix/js/calendar/outlook.js');
};

ECSyncPannel.prototype =
{
	Display: function()
	{
		var _this = this, i;
		this.syncList = [
			{
				id: 'google',
				label: EC_MESS.syncGoogle,
				className: 'bxec-sect-access-for-google',
				active: !!this.oEC.arConfig.syncInfo.google.active,
				connected: !!this.oEC.arConfig.syncInfo.google.connected,
				syncDate: this.oEC.arConfig.syncInfo.google.syncDate,
				connectHandler: function(e)
				{
					BX.util.popup(_this.oEC.arConfig.googleCalDavStatus.authLink, 500, 600);
					return BX.PreventDefault(e || window.event);
				},
				disconnectHandler:function(e)
				{
					_this.DisconnectGoogle(e);
					return BX.PreventDefault(e || window.event);
				}
			},
			{
				id: 'macosx',
				className: 'bxec-sect-access-for-macosx',
				label: EC_MESS.syncMac,
				active: !!this.oEC.arConfig.syncInfo.macosx.active,
				connected: !!this.oEC.arConfig.syncInfo.macosx.connected,
				syncDate: this.oEC.arConfig.syncInfo.macosx.syncDate,
				connectHandler: function(e)
				{
					_this.ConnectMacOSX();
					return BX.PreventDefault(e || window.event);
				},
				disconnectHandler:function(e)
				{
					_this.DisconnectMacOSX();
					return BX.PreventDefault(e || window.event);
				}
			},
			{
				id: 'iphone',
				className: 'bxec-sect-access-for-iphone',
				label: EC_MESS.syncIphone,
				active: !!this.oEC.arConfig.syncInfo.iphone.active,
				connected: !!this.oEC.arConfig.syncInfo.iphone.connected,
				syncDate: this.oEC.arConfig.syncInfo.iphone.syncDate,
				connectHandler: function(e)
				{
					_this.ConnectIphone();
					return BX.PreventDefault(e || window.event);
				},
				disconnectHandler:function(e)
				{
					_this.DisconnectIphone();
					return BX.PreventDefault(e || window.event);
				}
			},
			{
				id: 'android',
				className: 'bxec-sect-access-for-android',
				label: EC_MESS.syncAndroid,
				active: !!this.oEC.arConfig.syncInfo.android.active,
				connected: !!this.oEC.arConfig.syncInfo.android.connected,
				syncDate: this.oEC.arConfig.syncInfo.android.syncDate,
				connectHandler: function(e)
				{
					_this.ConnectAndroid();
					return BX.PreventDefault(e || window.event);
				},
				disconnectHandler:function(e)
				{
					_this.DisconnectAndroid();
					return BX.PreventDefault(e || window.event);
				}
			},
			{
				id: 'outlook',
				className: 'bxec-sect-access-for-outlook',
				label: EC_MESS.syncOutlook,
				active: !!this.oEC.arConfig.syncInfo.outlook.active && !BX.browser.IsMac(),
				connected: !!this.oEC.arConfig.syncInfo.outlook.connected,
				syncDate: this.oEC.arConfig.syncInfo.outlook.syncDate,
				connectHandler: function(e)
				{
					_this.ConnectOutlook();
					return BX.PreventDefault(e || window.event);
				},
				disconnectHandler:function(e)
				{
					_this.DisconnectOutlook();
					return BX.PreventDefault(e || window.event);
				}
			},
			{
				id: 'office365',
				className: 'bxec-sect-access-for-office365',
				label: EC_MESS.syncOffice365,
				active: !!this.oEC.arConfig.syncInfo.office365.active,
				connected: !!this.oEC.arConfig.syncInfo.office365.connected,
				syncDate: this.oEC.arConfig.syncInfo.office365.syncDate
			},
			{
				id: 'exchange',
				className: 'bxec-sect-access-for-exchange',
				label: EC_MESS.syncExchange,
				active: !!this.oEC.arConfig.syncInfo.exchange.active,
				connected: !!this.oEC.arConfig.syncInfo.exchange.connected,
				syncDate: this.oEC.arConfig.syncInfo.exchange.syncDate,
				connectHandler:function(e)
				{
					_this.ConnectExchange();
					return BX.PreventDefault(e || window.event);
				},
				disconnectHandler:function(e)
				{
					_this.DisconnectExchange();
					return BX.PreventDefault(e || window.event);
				}
			}
		];

		this.pWrap = BX(this.oEC.id + '-sync-inner-wrap');
		BX.cleanNode(this.pWrap);
		this.brightMode = true;
		var connectedLength = 0;
		var disconnectedLength = 0;

		for (i = 0; i < this.syncList.length; i++)
		{
			if (this.syncList[i].active)
			{
				if (this.syncList[i].connected)
				{
					this.brightMode = false;
					connectedLength++;
				}
				else
				{
					disconnectedLength++;
				}
			}
		}

		if (this.brightMode)
		{
			// All list
			for (i = 0; i < this.syncList.length; i++)
			{
				this.BuildSyncItem(this.syncList[i]);
			}
		}
		else
		{
			for (i = 0; i < this.syncList.length; i++)
			{
				if (this.syncList[i].connected)
					this.BuildSyncItem(this.syncList[i]);
			}

			this.showMoreLink = this.pWrap.appendChild(BX.create("DIV", {props: {className: 'bxec-sect-access-view-all'}})).appendChild(BX.create("A", {props: {href: ''}, text: EC_MESS.connectMore.replace(/#COUNT#/ig, disconnectedLength)}));
			BX.bind(this.showMoreLink, 'click', function(e)
			{
				_this.showMoreLink.style.display = 'none';
				_this.hiddenWrap.style.height = (disconnectedLength * 41) + 'px';

				if (!_this.hideMoreLink)
				{
					_this.hideMoreLink = _this.pWrap.appendChild(BX.create("DIV", {props: {className: 'bxec-sect-access-view-all'}})).appendChild(BX.create("A", {props: {href: ''}, text: EC_MESS.showLess}));
					BX.bind(_this.hideMoreLink, 'click', function(e)
					{
						_this.hideMoreLink.style.display = 'none';
						_this.showMoreLink.style.display = '';
						_this.hiddenWrap.style.height = 0;
						return BX.PreventDefault(e || window.event);
					});
				}
				else
				{
					_this.hideMoreLink.style.display = '';
				}

				return BX.PreventDefault(e || window.event);
			});
			this.hiddenWrap = this.pWrap.appendChild(BX.create("DIV", {props: {className: 'bxec-sect-access-hidden-wrap'}}));

			for (i = 0; i < this.syncList.length; i++)
			{
				if (!this.syncList[i].connected)
					this.BuildSyncItem(this.syncList[i], this.hiddenWrap);
			}
		}
	},

	SyncSectionWithOutlook: function(id)
	{
		var oSect = this.oEC.oSections[id];
		if(oSect && oSect.OUTLOOK_JS && oSect.OUTLOOK_JS.length > 0)
			try{eval(oSect.OUTLOOK_JS);}catch(e){};
	},

	DisconnectGoogle: function(e)
	{
		if (confirm(EC_MESS.googleDisconnectConfirm))
		{
			var i, con = null, _this = this;
			for (i = 0; i < this.oEC.arConnections.length; i++)
			{
				con = this.oEC.arConnections[i];
				if (con.account_type == "caldav_google_oauth")
				{
					break;
				}
			}

			if (con && con.id)
			{
				this.oEC.Request({
					postData: this.oEC.GetReqData('disconnect_google', {
						connectionId: con.id
					}), handler: function ()
					{
						window.location = window.location;
					}
				});
			}
		}
		return BX.PreventDefault(e || window.event);
	},

	ConnectOutlook: function()
	{
		var
			activeSectionsCount = 0,
			_this = this,
			i, sect, lastSect, pItem;

		for (i = 0; i < this.oEC.arSections.length; i++)
		{
			sect = this.oEC.arSections[i];
			if (sect.ACTIVE !== 'N' && this.oEC.IsCurrentViewSect(sect) && sect.OUTLOOK_JS)
			{
				activeSectionsCount++;
				lastSect = sect;
			}
		}

		// Only one section
		if (activeSectionsCount == 1 && lastSect)
		{
			this.SyncSectionWithOutlook(lastSect.ID);
		}
		else
		{
			// Show popup
			var sync = this.GetSyncItem('outlook');
			if (!this.outlookPopup)
			{
				sync.popupCont = BX.create('DIV', {props: {className: 'bxec-sync-popup-wrap'}});

				for (i = 0; i < this.oEC.arSections.length; i++)
				{
					sect = this.oEC.arSections[i];
					if (this.oEC.IsCurrentViewSect(sect) && sect.OUTLOOK_JS)
					{
						pItem = sync.popupCont.appendChild(BX.create("DIV", {
							props: {
								id: 'ecpp_' + sect.ID,
								className: 'bxec-sync-popup-item' + (sect.bDark ? ' bxec-dark' : '')
							},
							style: {
								backgroundColor: sect.COLOR
							},
							text: sect.NAME
						}));
						pItem.onclick = function ()
						{
							_this.SyncSectionWithOutlook(this.id.substr('ecpp_'.length));
						}
					}
				}

				this.outlookPopup = BX.PopupWindowManager.create(this.oEC.id + "-outlook-sync-popup", sync.pConnectLink, {
					autoHide: true,
					closeByEsc: true,
					offsetTop: -1,
					offsetLeft: 1,
					lightShadow: true,
					content: sync.popupCont
				});
			}

			this.outlookPopup.show(true);

			BX.addCustomEvent(this.outlookPopup, 'onPopupClose', function()
			{
				if(_this.outlookPopup && _this.outlookPopup.destroy)
				{
					_this.outlookPopup.destroy();
					_this.outlookPopup = null;
				}
			});
		}
	},

	ConnectIphone: function()
	{
		this.oEC.ShowMobileSyncDialog('iphone');
	},

	ConnectMacOSX: function()
	{
		this.oEC.ShowMobileSyncDialog('macosx');
	},

	ConnectAndroid: function()
	{
		this.oEC.ShowMobileSyncDialog('android');
	},

	DisconnectIphone: function()
	{
		//1. Send request to clear sync information
		this.oEC.Request({postData: this.oEC.GetReqData('clear_sync_info', {sync_type: 'iphone'})});

		//2. Show popup with info how to disconnect it
		var sync = this.GetSyncItem('iphone');
		if (sync && sync.pDisconnectLink)
		{
			var _this = this;
			this.ShowInfoPopup(sync.pDisconnectLink, EC_MESS.disconnectIphone, function ()
			{
				_this.oEC.arConfig.syncInfo.iphone.connected = false;
				_this.oEC.arConfig.syncInfo.iphone.syncDate = false;
				_this.Display();
			});
		}
	},

	DisconnectMacOSX: function()
	{
		//1. Send request to clear sync information
		this.oEC.Request({postData: this.oEC.GetReqData('clear_sync_info', {sync_type: 'mac'})});

		//2. Show popup with info how to disconnect it
		var sync = this.GetSyncItem('macosx');
		if (sync && sync.pDisconnectLink)
		{
			var _this = this;
			this.ShowInfoPopup(sync.pDisconnectLink, EC_MESS.disconnectMac, function ()
			{
				_this.oEC.arConfig.syncInfo.macosx.connected = false;
				_this.oEC.arConfig.syncInfo.macosx.syncDate = false;
				_this.Display();
			});
		}
	},

	DisconnectAndroid: function()
	{
		//1. Send request to clear sync information
		this.oEC.Request({postData: this.oEC.GetReqData('clear_sync_info', {sync_type: 'android'})});

		//2. Show popup with info how to disconnect it
		var sync = this.GetSyncItem('android');
		if (sync && sync.pDisconnectLink)
		{
			var _this = this;
			this.ShowInfoPopup(sync.pDisconnectLink, EC_MESS.disconnectAndroid, function ()
			{
				_this.oEC.arConfig.syncInfo.android.connected = false;
				_this.oEC.arConfig.syncInfo.android.syncDate = false;
				_this.Display();
			});
		}
	},
	DisconnectOutlook: function()
	{
		//1. Send request to clear sync information
		this.oEC.Request({postData: this.oEC.GetReqData('clear_sync_info', {sync_type: 'outlook'})});

		//2. Show popup with info how to disconnect it
		var sync = this.GetSyncItem('outlook');
		if (sync && sync.pDisconnectLink)
		{
			var _this = this;
			this.ShowInfoPopup(sync.pDisconnectLink, EC_MESS.disconnectOutlook, function ()
			{
				_this.oEC.arConfig.syncInfo.outlook.connected = false;
				_this.oEC.arConfig.syncInfo.outlook.syncDate = false;
				_this.Display();
			});
		}
	},

	ShowInfoPopup: function(item, html, onCloseHandler)
	{
		var popup = BX.PopupWindowManager.create(this.oEC.id + "-disconnect-popup", item,
			{
				autoHide: true,
				closeByEsc: true,
				offsetTop: -1,
				offsetLeft: 1,
				lightShadow: true,
				content: BX.create('DIV', {props: {className: 'bxec-disconnect-popup-wrap'}, html:html})
			});
		popup.show(true);

		function destroyPopup()
		{
			if (onCloseHandler && typeof onCloseHandler == 'function')
				onCloseHandler();

			if(popup && popup.destroy)
			{
				BX.removeCustomEvent(popup, 'onPopupClose', destroyPopup);
				popup.destroy();
				popup = null;
			}
		}
		BX.addCustomEvent(popup, 'onPopupClose', destroyPopup);
	},

	ConnectExchange: function()
	{
		var sync = this.GetSyncItem('exchange');
		if (sync && sync.pConnectLink)
		{
			this.ShowInfoPopup(sync.pConnectLink, EC_MESS.connectExchange);
		}
	},

	DisconnectExchange: function()
	{
		var sync = this.GetSyncItem('exchange');
		if (sync && sync.pDisconnectLink)
		{
			this.ShowInfoPopup(sync.pDisconnectLink, EC_MESS.disconnectExchange);
		}
	},

	SyncExchange: function()
	{
		this.oEC.Request({
			postData: this.oEC.GetReqData('exchange_sync'),
			handler: function(oRes)
			{
				var res = oRes.result;
				setTimeout(function()
				{
					if (res === true)
						top.window.location = top.window.location;
					else if (res === false)
						alert(EC_MESS.ExchNoSync);
				}, 100);
			}
		});
	},

	BuildSyncItem: function(sync, parentCont)
	{
		if (!parentCont)
			parentCont = this.pWrap;

		if (sync.active)
		{
			sync.pOuter = parentCont.appendChild(BX.create("DIV", {props: {className: 'bxec-sect-access-el ' + (sync.className || '')}}));
			sync.pInner = sync.pOuter.appendChild(BX.create("DIV", {props: {className: 'bxec-sect-access-el-block'}}));
			if (!sync.connected && sync.connectHandler)
			{
				sync.pConnectLink = sync.pInner.appendChild(BX.create("A", {
					props: {className: 'bxec-sect-access-connect-link'}, text: EC_MESS.syncConnect
				}));
				BX.bind(sync.pConnectLink, 'click', sync.connectHandler);
			}
			sync.pIcon = sync.pInner.appendChild(BX.create("DIV", {props: {className: 'bxec-sect-access-icon'}}));
			sync.pTextWrap = sync.pInner.appendChild(BX.create("DIV", {
				props: {className: 'bxec-sect-access-text-wrap'},
				text: sync.label
			}));

			if (!this.brightMode || sync.connected)
				BX.addClass(sync.pOuter, 'bxec-sect-access-connected');

			if (sync.connected)
			{
				sync.pInner.appendChild(BX.create("DIV", {props: {className: 'bxec-sect-access-allowed-icon'}}));

				sync.pInfoCont = sync.pOuter.appendChild(BX.create("DIV", {props: {className: 'bxec-sect-access-block-info bxec-sect-access-el-block-active'}}));
				var tbl = sync.pInfoCont.appendChild(BX.create("TABLE", {props: {className: 'bxec-sect-access-el-table'}}));
				var row = tbl.insertRow(-1);
				BX.adjust(row.insertCell(-1), {props : {className: 'bxec-sect-access-status'}, html: EC_MESS.syncOk});
				var cell = BX.adjust(row.insertCell(-1), {style: {textAlign: 'right'}});
				if (sync.syncDate)
				{
					sync.pSyncDate = cell.appendChild(BX.create("DIV", {props: {className: 'bxec-sect-access-status-time'}}));
					sync.pSyncDate.innerHTML = sync.syncDate;
				}
				if (sync.disconnectHandler)
				{
					sync.pDisconnectLink = cell.appendChild(BX.create("SPAN", {props: {className: 'bxec-sect-access-disconnect-link'}, html: EC_MESS.syncDisconnect}));
					BX.bind(sync.pDisconnectLink, 'click', sync.disconnectHandler);
				}

				if (sync.id == 'exchange' && this.oEC.arConfig.bExchange)
				{
					sync.pTextWrap.style.cursor = 'pointer';
					sync.pTextWrap.title = EC_MESS.syncExchangeTitle;
					BX.bind(sync.pTextWrap, 'click', BX.proxy(this.SyncExchange, this));
				}
			}
		}
	},

	GetSyncItem: function(id)
	{
		var i;
		for (i = 0; i < this.syncList.length; i++)
		{
			if (this.syncList[i].active && this.syncList[i].id == id)
			{
				return this.syncList[i];
			}
		}
	}
};

var ECMonthSelector = function(oEC)
{
	this.oEC = oEC;
	this.Build();
	this.content = {month: '', week: '', day: ''};
};

ECMonthSelector.prototype = {
	Build : function()
	{
		var _this = this;
		this.pPrev = BX(this.oEC.id + "selector-prev");
		this.pNext = BX(this.oEC.id + "selector-next");
		this.pCont = BX(this.oEC.id + "selector-cont");
		this.pContInner = BX(this.oEC.id + "selector-cont-inner");

		this.pPrev.onclick = function(){_this.ChangeValue(false);};
		this.pNext.onclick = function(){_this.ChangeValue(true);};
	},

	ChangeMode : function(mode)
	{
		this.mode = mode || this.oEC.activeTabId;
		if (this.mode == 'month')
		{
			this.pCont.className = 'bxec-sel-but';
			this.pCont.onclick = BX.proxy(this.ShowMonthPopup, this);
		}
		else
		{
			this.pCont.className = 'bxec-sel-text';
			this.pCont.onclick = BX.False;
		}
	},

	OnChange : function(year, month, week, date)
	{
		month = parseInt(month, 10);
		year = parseInt(year);
		var res, dayOffset;

		this.pNext.style.marginLeft = (this.mode == 'month' && BX.browser.IsIE() && !BX.browser.IsIE9()) ? '10px' : ''; // Hack for IE 8

		if (this.mode == 'month')
		{
			if (month < 0 || month > 11)
				return alert('Error! Incorrect month');

			this.content.month = this.oEC.arConfig.month[month] + ',&nbsp;' + year + '<span class="bxec-sel-but-arr">';
		}
		else if (this.mode == 'week')
		{
			var startWeekDate = new Date();
			startWeekDate.setFullYear(year, month, 1);

			//if (week < 0 && this.oEC.weekStart != this.oEC.GetWeekDayByInd(startWeekDate.getDay()))
			//	week = 0;

			dayOffset = this.oEC.GetWeekDayOffset(this.oEC.GetWeekDayByInd(startWeekDate.getDay()));

			if(dayOffset > 0)
				startWeekDate.setDate(startWeekDate.getDate() - dayOffset); // Now it-s first day in of this week

			if (week != 0)
				startWeekDate.setDate(startWeekDate.getDate() + (7 * week));

			var oSunDate = new Date(startWeekDate.getTime());
			oSunDate.setDate(oSunDate.getDate() + 6);
			var
				content,
				month_r = this.oEC.arConfig.month_r,
				d_f = startWeekDate.getDate(),
				m_f = startWeekDate.getMonth(),
				y_f = startWeekDate.getFullYear(),
				d_t = oSunDate.getDate(),
				m_t = oSunDate.getMonth(),
				y_t = oSunDate.getFullYear();

			if (m_f == m_t)
				content = d_f + '&nbsp;-&nbsp;' + d_t + '&nbsp;' + month_r[m_f] + '&nbsp;' + y_f;
			else if(y_f == y_t)
				content = d_f + '&nbsp;' + month_r[m_f] + '&nbsp;-&nbsp;' + d_t + '&nbsp;' + month_r[m_t] + '&nbsp;' + y_f;
			else
				content = d_f + '&nbsp;' + month_r[m_f] + '&nbsp;' + y_f + '&nbsp;-&nbsp;' + d_t + '&nbsp;' + month_r[m_t] + '&nbsp;' + y_t;

			this.content.week = '<nobr>' + content + '</nobr>';
			res = {
				dateFrom: d_f,
				monthFrom: m_f,
				yearFrom: y_f,
				weekStartDate: startWeekDate,
				monthTo: m_t,
				yearTo: y_t,
				dateTo: d_t,
				weekEndDate: oSunDate
			};
		}
		else if (this.mode == 'day')
		{
			var oDate = new Date();
			oDate.setFullYear(year, month, date);
			day = this.oEC.ConvertDayIndex(oDate.getDay());
			date = oDate.getDate(),
			month = oDate.getMonth(),
			year = oDate.getFullYear();

			this.content.day = '<nobr>' + this.oEC.arConfig.days[day][0] + ',&nbsp;' + date + '&nbsp;' + this.oEC.arConfig.month_r[month] + '&nbsp;' + year + '</nobr>';
			res = {date: date, month: month, year: year, oDate: oDate};
		}

		this.Show(this.mode);
		return res;
	},

	Show: function(mode)
	{
		this.pContInner.innerHTML = this.content[mode];
	},

	ChangeValue: function(bNext)
	{
		var delta = bNext ? 1 : -1;
		this.oEC.bJustRedraw = false;
		if (this.mode == 'month')
		{
			//IncreaseCurMonth
			var m = bxInt(this.oEC.activeDate.month) + delta;
			var y = this.oEC.activeDate.year;
			if (m < 0)
			{
				m += 12;
				y--;
			}
			else if (m > 11)
			{
				m -= 12;
				y++;
			}
			this.oEC.SetMonth(m, y);
		}
		else if (this.mode == 'week')
		{
			this.oEC.SetWeek(this.oEC.activeDate.week + delta, this.oEC.activeDate.month, this.oEC.activeDate.year);
		}
		else if (this.mode == 'day')
		{
			this.oEC.SetDay(this.oEC.activeDate.date + delta, this.oEC.activeDate.month, this.oEC.activeDate.year);
		}
	},

	ShowMonthPopup: function()
	{
		if (!this.oMonthWin)
		{
			var _this = this;
			this.oMonthWin = new BX.PopupWindow(this.oEC.id + "bxc-month-sel", this.pCont, {
				overlay: {opacity: 1},
				autoHide : true,
				offsetTop : 1,
				offsetLeft : 0,
				lightShadow : true,
				contentColor : "white",
				contentNoPaddings : true,
				content : BX('bxec_month_win_' + this.oEC.id)
			});
			this.oMonthWin.CAL = {
				DOM : {
					Year: BX(this.oEC.id + 'md-year'),
					MonthList: BX(this.oEC.id + 'md-month-list')
				},
				curYear: parseInt(this.oEC.activeDate.year)
			};

			this.oMonthWin.CAL.DOM.Year.innerHTML = this.oMonthWin.CAL.curYear;
			BX(this.oEC.id + 'md-selector-prev').onclick = function(){_this.oMonthWin.CAL.DOM.Year.innerHTML = --_this.oMonthWin.CAL.curYear;};
			BX(this.oEC.id + 'md-selector-next').onclick = function(){_this.oMonthWin.CAL.DOM.Year.innerHTML = ++_this.oMonthWin.CAL.curYear;};

			var
				i, m, div,
				arM = [0, 4, 8, 1, 5, 9, 2, 6, 10, 3, 7, 11];

			for (i = 0; i < 12; i++)
			{
				m = arM[i];
				div = this.oMonthWin.CAL.DOM.MonthList.appendChild(BX.create("DIV", {
					props: {id: 'bxec_ms_m_' + arM[i], className: 'bxec-month-div' + (arM[i] == this.oEC.activeDate.month ? ' bxec-month-act' : '') + ' bxec-' + this.GetSeason(arM[i])},
					html: '<span>' + this.oEC.arConfig.month[arM[i]] + '</span>',
					events: {click: function()
					{
						_this.oEC.bJustRedraw = false;
						BX.removeClass(_this.oMonthWin.CAL.DOM.curMonth, 'bxec-month-act');
						BX.addClass(this, 'bxec-month-act');
						_this.oMonthWin.CAL.DOM.curMonth = this;
						_this.oEC.SetMonth(parseInt(this.id.substr('bxec_ms_m_'.length)), _this.oMonthWin.CAL.curYear);
						_this.oMonthWin.close();
					}}
				}));
				if (arM[i] == this.oEC.activeDate.month)
					this.oMonthWin.CAL.DOM.curMonth = div;
			}
		}

		this.oMonthWin.show();
	},

	GetSeason : function(m)
	{
		switch(m)
		{
			case 11: case 0: case 1:
				return 'winter';
			case 2: case 3: case 4:
				return 'spring';
			case 5: case 6: case 7:
				return 'summer';
			case 8: case 9: case 10:
				return 'autumn';
		}
	}
};

var ECCalendarAccess = function(Params)
{
	BX.Access.Init();
	if (!window.EC_MESS)
		EC_MESS = {};

	this.bind = Params.bind;
	this.GetAccessName = Params.GetAccessName;
	this.pTbl = Params.pCont.appendChild(BX.create("TABLE", {props: {className: "bxc-access-tbl"}}));
	this.pSel = BX('bxec-' + this.bind);
	var _this = this;
	this.delTitle = Params.delTitle || EC_MESS.Delete;
	this.noAccessRights = Params.noAccessRights || EC_MESS.NoAccessRights;

	this.inputName = Params.inputName || false;

	Params.pLink.onclick = function(){
		BX.Access.ShowForm({
			callback: BX.proxy(_this.InsertRights, _this),
			bind: _this.bind
		});
	};
};

ECCalendarAccess.prototype = {
	InsertRights: function(obSelected)
	{
		var provider, code;
		for(provider in obSelected)
			for(code in obSelected[provider])
				this.InsertAccessRow(BX.Access.GetProviderName(provider) + ' ' + obSelected[provider][code].name, code);
	},

	InsertAccessRow: function(title, code, value)
	{
		var _this = this, row, pLeft, pRight, pTaskSelect;
		if (this.pTbl.rows[0] && this.pTbl.rows[0].cells[0] && this.pTbl.rows[0].cells[0].className.indexOf('bxc-access-no-vals') != -1)
			this.DeleteRow(0);

		row = this.pTbl.insertRow(-1);
		pLeft = BX.adjust(row.insertCell(-1), {props : {className: 'bxc-access-c-l'}, html: title + ':'});
		pRight = BX.adjust(row.insertCell(-1), {props : {className: 'bxc-access-c-r'}});
		pTaskSelect = pRight.appendChild(this.pSel.cloneNode(true));
		//pTaskSelect.name = 'BXEC_ACCESS_' + code;
		pTaskSelect.id = 'BXEC_ACCESS_' + code;

		if (value)
			pTaskSelect.value = value;
		pDel = pRight.appendChild(BX.create('A', {props:{className: 'access-delete', href: 'javascript:void(0)', title: this.delTitle}, events: {click: function(){_this.DeleteRow(this.parentNode.parentNode.rowIndex);}}}));

		if (this.inputName)
		{
			pTaskSelect.name = this.inputName + '[' + code + ']';
			//pRight.appendChild(BX.create('INPUT', {props:{type: 'hidden', value: this.inputName + '[' + code + ']'}}));
		}
	},

	DeleteRow: function(rowIndex)
	{
		if (this.pTbl.rows[rowIndex])
			this.pTbl.deleteRow(rowIndex);
	},

	GetValues: function()
	{
		var
			id, taskId,
			res = {},
			arSelect = this.pTbl.getElementsByTagName("SELECT"),
			i, l = arSelect.length;

		for(i = 0; i < l; i++)
		{
			id = arSelect[i].id.substr('BXEC_ACCESS_'.length);
			taskId = arSelect[i].value;
			res[id] = taskId;
		}

		return res;
	},

	SetSelected: function(oAccess)
	{
		if (!oAccess)
			oAccess = {};

		while (this.pTbl.rows[0])
			this.pTbl.deleteRow(0);

		var
			code,
			oSelected = {};

		for (code in oAccess)
		{
			this.InsertAccessRow(this.GetTitleByCode(code), code, oAccess[code]);
			oSelected[code] = true;
		}

		// Insert 'no value'  if no permissions exists
		if (this.pTbl.rows.length <= 0)
			BX.adjust(this.pTbl.insertRow(-1).insertCell(-1), {props : {className: 'bxc-access-no-vals', colSpan: 2}, html: '<span>' + this.noAccessRights + '</span>'});

		BX.Access.SetSelected(oSelected, this.bind);
	},

	GetTitleByCode: function(code)
	{
		return this.GetAccessName(code);
	}
};

function ECColorPicker(Params)
{
	//this.bCreated = false;
	this.bOpened = false;
	this.zIndex = 5000;
	this.id = '';
	this.Popups = {};
	this.Conts = {};
}

ECColorPicker.prototype = {
	Create: function ()
	{
		var _this = this;
		var pColCont = document.body.appendChild(BX.create("DIV", {props: {className: "ec-colpick-cont"}, style: {zIndex: this.zIndex}}));

		var
			arColors = [
			'#3AD0FF', '#A6DC00', '#FF5C5A', '#B47153','#2FC7F7','#04B4AB','#FFA801','#5CD1DF','#6E54D1','#29AD49','#FE5957','#DAA187','#78D4F1','#43DAD2','#EECE8F','#AEE5EC',
			'#FF0000', '#FFFF00', '#00FF00', '#00FFFF', '#0000FF', '#FF00FF', '#FFFFFF', '#EBEBEB', '#E1E1E1', '#D7D7D7', '#CCCCCC', '#C2C2C2', '#B7B7B7', '#ACACAC', '#A0A0A0', '#959595',
			'#EE1D24', '#FFF100', '#00A650', '#00AEEF', '#2F3192', '#ED008C', '#898989', '#7D7D7D', '#707070', '#626262', '#555', '#464646', '#363636', '#262626', '#111', '#000000',
			'#F7977A', '#FBAD82', '#FDC68C', '#FFF799', '#C6DF9C', '#A4D49D', '#81CA9D', '#7BCDC9', '#6CCFF7', '#7CA6D8', '#8293CA', '#8881BE', '#A286BD', '#BC8CBF', '#F49BC1', '#F5999D',
			'#F16C4D', '#F68E54', '#FBAF5A', '#FFF467', '#ACD372', '#7DC473', '#39B778', '#16BCB4', '#00BFF3', '#438CCB', '#5573B7', '#5E5CA7', '#855FA8', '#A763A9', '#EF6EA8', '#F16D7E',
			'#EE1D24', '#F16522', '#F7941D', '#FFF100', '#8FC63D', '#37B44A', '#00A650', '#00A99E', '#00AEEF', '#0072BC', '#0054A5', '#2F3192', '#652C91', '#91278F', '#ED008C', '#EE105A',
			'#9D0A0F', '#A1410D', '#A36209', '#ABA000', '#588528', '#197B30', '#007236', '#00736A', '#0076A4', '#004A80', '#003370', '#1D1363', '#450E61', '#62055F', '#9E005C', '#9D0039',
			'#790000', '#7B3000', '#7C4900', '#827A00', '#3E6617', '#045F20', '#005824', '#005951', '#005B7E', '#003562', '#002056', '#0C004B', '#30004A', '#4B0048', '#7A0045', '#7A0026'],
			row, cell, colorCell,
			tbl = BX.create("TABLE", {props: {className: 'ec-colpic-tbl'}}),
			i, l = arColors.length;

		row = tbl.insertRow(-1);
		cell = row.insertCell(-1);
		cell.colSpan = 8;
		var defBut = cell.appendChild(BX.create("SPAN", {props: {className: 'ec-colpic-def-but'}, text: EC_MESS.DefaultColor}));
		defBut.onmouseover = function()
		{
			this.className = 'ec-colpic-def-but ec-colpic-def-but-over';
			colorCell.style.backgroundColor = '#FF0000';
		};
		defBut.onmouseout = function(){this.className = 'ec-colpic-def-but';};
		defBut.onmousedown = function(e){_this.Select('#FF0000');}

		colorCell = row.insertCell(-1);
		colorCell.colSpan = 8;
		colorCell.className = 'ec-color-inp-cell';
		colorCell.style.backgroundColor = arColors[38];

		for(i = 0; i < l; i++)
		{
			if (Math.round(i / 16) == i / 16) // new row
				row = tbl.insertRow(-1);

			cell = row.insertCell(-1);
			cell.innerHTML = '&nbsp;';
			cell.className = 'ec-col-cell';
			cell.style.backgroundColor = arColors[i];
			cell.id = 'lhe_color_id__' + i;

			cell.onmouseover = function (e)
			{
				this.className = 'ec-col-cell ec-col-cell-over';
				colorCell.style.backgroundColor = arColors[this.id.substring('lhe_color_id__'.length)];
			};
			cell.onmouseout = function (e){this.className = 'ec-col-cell';};
			cell.onmousedown = function (e)
			{
				var k = this.id.substring('lhe_color_id__'.length);
				_this.Select(arColors[k]);
			};
		}

		pColCont.appendChild(tbl);

		this.Conts[this.id] = pColCont;
		//this.bCreated = true;
	},

	Open: function(Params)
	{
		this.id = Params.id + Math.round(Math.random() * 1000000);
		this.key = Params.key;
		this.OnSelect = Params.onSelect;

		if (!this.Conts[this.id])
			this.Create();

		if (!this.Popups[this.id])
		{
			this.Popups[this.id] = BX.PopupWindowManager.create("bxc-color-popup" + this.id, Params.pWnd, {
				autoHide : true,
				offsetTop : 1,
				offsetLeft : 0,
				lightShadow : true,
				content : this.Conts[this.id]
			});
		}

		this.Popups[this.id].show();
	},

	Close: function ()
	{
		this.Popups[this.id].close();
		this.Popups[this.id].destroy();
	},

	OnKeyPress: function(e)
	{
		if(!e) e = window.event
		if(e.keyCode == 27)
			this.Close();
	},

	Select: function (color)
	{
		if (this.OnSelect && typeof this.OnSelect == 'function')
			this.OnSelect(color);
		this.Close();
	}
};


/* DESTINATION */
// Calbacks for destination
window.BxEditEventGridSetLinkName = function(name)
{
	var destLink = BX('event-grid-dest-add-link');
	if (destLink)
		destLink.innerHTML = BX.SocNetLogDestination.getSelectedCount(name) > 0 ? BX.message("BX_FPD_LINK_2") : BX.message("BX_FPD_LINK_1");
}

window.BxEditEventGridSelectCallback = function(item, type, search)
{
	var type1 = type;
	prefix = 'S';
	if (type == 'sonetgroups')
		prefix = 'SG';
	else if (type == 'groups')
	{
		prefix = 'UA';
		type1 = 'all-users';
	}
	else if (type == 'users')
		prefix = 'U';
	else if (type == 'department')
		prefix = 'DR';

	BX('event-grid-dest-item').appendChild(
		BX.create("span", { attrs : {'data-id' : item.id }, props : {className : "event-grid-dest event-grid-dest-"+type1 }, children: [
			BX.create("input", { attrs : {type : 'hidden', name : 'EVENT_DESTINATION['+prefix+'][]', value : item.id }}),
			BX.create("span", { props : {className : "event-grid-dest-text" }, html : item.name}),
			BX.create("span", { props : {className : "feed-event-del-but"}, attrs: {'data-item-id': item.id, 'data-item-type': type}})
		]})
	);

	BX.onCustomEvent('OnDestinationAddNewItem', [item]);
	BX('event-grid-dest-input').value = '';
	BxEditEventGridSetLinkName(editEventDestinationFormName);
}

// remove block
window.BxEditEventGridUnSelectCallback = function(item, type, search)
{
	var elements = BX.findChildren(BX('event-grid-dest-item'), {attribute: {'data-id': ''+item.id+''}}, true);
	if (elements != null)
	{
		for (var j = 0; j < elements.length; j++)
			BX.remove(elements[j]);
	}

	BX.onCustomEvent('OnDestinationUnselect');
	BX('event-grid-dest-input').value = '';
	BxEditEventGridSetLinkName(editEventDestinationFormName);
}
window.BxEditEventGridOpenDialogCallback = function()
{
	BX.style(BX('event-grid-dest-input-box'), 'display', 'inline-block');
	BX.style(BX('event-grid-dest-add-link'), 'display', 'none');
	BX.focus(BX('event-grid-dest-input'));
}

window.BxEditEventGridCloseDialogCallback = function()
{
	if (!BX.SocNetLogDestination.isOpenSearch() && BX('event-grid-dest-input').value.length <= 0)
	{
		BX.style(BX('event-grid-dest-input-box'), 'display', 'none');
		BX.style(BX('event-grid-dest-add-link'), 'display', 'inline-block');
		BxEditEventGridDisableBackspace();
	}
}

window.BxEditEventGridCloseSearchCallback = function()
{
	if (!BX.SocNetLogDestination.isOpenSearch() && BX('event-grid-dest-input').value.length > 0)
	{
		BX.style(BX('event-grid-dest-input-box'), 'display', 'none');
		BX.style(BX('event-grid-dest-add-link'), 'display', 'inline-block');
		BX('event-grid-dest-input').value = '';
		BxEditEventGridDisableBackspace();
	}

}
window.BxEditEventGridDisableBackspace = function(event)
{
	if (BX.SocNetLogDestination.backspaceDisable || BX.SocNetLogDestination.backspaceDisable != null)
		BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);

	BX.bind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable = function(event){
		if (event.keyCode == 8)
		{
			BX.PreventDefault(event);
			return false;
		}
	});
	setTimeout(function(){
		BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);
		BX.SocNetLogDestination.backspaceDisable = null;
	}, 5000);
}

window.BxEditEventGridSearchBefore = function(event)
{
	return BX.SocNetLogDestination.searchBeforeHandler(event, {
		formName: editEventDestinationFormName,
		inputId: 'event-grid-dest-input'
	});
}
window.BxEditEventGridSearch = function(event)
{
	return BX.SocNetLogDestination.searchHandler(event, {
		formName: editEventDestinationFormName,
		inputId: 'event-grid-dest-input',
		linkId: 'event-grid-dest-add-link',
		sendAjax: true
	});
}
/* END DESTINATION */

;(function(window){
	window.EditEventPopupController = function(config)
	{
		this.config = config;
		this.id = this.config.id;
		this.oEC = this.config.oEC;
		this.oEvent = this.config.oEvent;
		this.Form = this.config.form;

		this.Init();
	};

	window.EditEventPopupController.prototype = {
		Init: function()
		{
			this.InitDateTimeControls();

			var
				_this = this,
				editorId = this.oEC.id + '_event_editor',
				oEditor = window["BXHtmlEditor"].Get(editorId);

			if (oEditor && oEditor.IsShown())
			{
				this.CustomizeHtmlEditor(oEditor);
			}
			else
			{
				BX.addCustomEvent(window["BXHtmlEditor"], 'OnEditorCreated', function(editor)
				{
					if (editor.id == editorId)
					{
						_this.CustomizeHtmlEditor(editor);
					}
				});
			}

			if (this.oEC.allowMeetings)
				this.InitDestinationControls();
			this.FillFormFields();
		},

		SaveForm: function(params, bConfirmed)
		{
			var
				fromDate, toDate,
				_this = this,
				month = parseInt(this.oEC.activeDate.month, 10),
				year = this.oEC.activeDate.year,
				url = this.oEC.actionUrl,
				reqId = Math.round(Math.random() * 1000000);

			url += (url.indexOf('?') == -1) ? '?' : '&';
			url += 'action=edit_event&bx_event_calendar_request=Y&sessid=' + BX.bitrix_sessid() + '&reqId=' + reqId;
			this.Form.action = url;

			if (BX.util.trim(this.pFromTime.value) != '' || BX.util.trim(this.pFromDate.value) != '')
			{
				fromDate = this.oEC.ParseDate(BX.util.trim(this.pFromDate.value) + (this.pFullDay.checked ? '' : ' ' + BX.util.trim(this.pFromTime.value)));
			}
			if (BX.util.trim(this.pToTime.value) != '' || BX.util.trim(this.pToDate.value) != '')
			{
				toDate = this.oEC.ParseDate(BX.util.trim(this.pToDate.value) + (this.pFullDay.checked ? '' : ' ' + BX.util.trim(this.pToTime.value)));
			}

			if (params.recurentEventEditMode)
			{
				BX('event-current-date-from' + this.id).value = this.oEvent.DATE_FROM;
				BX('event-rec-edit-mode' + this.id).value = params.recurentEventEditMode;
			}
			else
			{
				BX('event-current-date-from' + this.id).value = '';
				BX('event-rec-edit-mode' + this.id).value = '';
			}

			// Location
			BX('event-location-old' + this.id).value = this.Loc.OLD || false;
			BX('event-location-new' + this.id).value = this.Loc.NEW;

			if (this.Loc.NEW.substr(0, 5) == 'ECMR_' && this.RepeatCheck.checked)
			{
				alert(EC_MESS.reservePeriodWarn);
				return false;
			}

			if (!this.CheckUserAccessibility())
			{
				alert(EC_MESS.EC_BUSY_ALERT);
				return;
			}

			// Check Meeting rooms accessibility
			if (this.Loc.NEW.substr(0, 5) == 'ECMR_' && !params.bLocationChecked)
			{
				if (toDate && this.pFullDay.checked)
				{
					toDate = new Date(toDate.getTime() + 86400000 /* one day*/);
				}

				if (fromDate && toDate)
				{
					this.oEC.CheckMeetingRoom(
						{
							id : this.oEvent.ID || 0,
							from : _this.oEC.FormatDateTime(fromDate),
							to : _this.oEC.FormatDateTime(toDate),
							location_new : this.Loc.NEW,
							location_old : this.Loc.OLD || false
						},
						function(check)
						{
							if (!check)
								return alert(EC_MESS.MRReserveErr);
							if (check == 'reserved')
								return alert(EC_MESS.MRNotReservedErr);

							params.bLocationChecked = true;
							_this.SaveForm(params);
						}
					);
					return false;
				}
			}

			if (fromDate && BX.util.trim(this.pFromTime.value) != '')
				this.pFromTime.value = this.oEC.FormatTime(fromDate, true);

			if (toDate && BX.util.trim(this.pToTime.value) != '')
				this.pToTime.value = this.oEC.FormatTime(toDate, true);

			BX('event-id' + this.id).value = this.oEvent.ID || 0;
			BX('event-month' + this.id).value = month + 1;
			BX('event-year' + this.id).value = year;

			// RRULE
			if (this.RepeatCheck.checked)
			{
				var FREQ = this.RepeatSelect.value;

				if (this.RepeatDiapTo.value == EC_MESS.NoLimits)
					this.RepeatDiapTo.value = '';

				if (FREQ == 'WEEKLY')
				{
					var ar = [], i;
					for (i = 0; i < 7; i++)
						if (this.RepeatWeekDaysCh[i].checked)
							ar.push(this.RepeatWeekDaysCh[i].value);

					if (ar.length == 0)
						this.RepeatSelect.value = 'NONE';
					else
						BX('event-rrule-byday' + this.id).value = ar.join(',');
				}
			}

			if (this.oEvent.ID && this.oEC.Event.IsRecursive(this.oEvent) && !bConfirmed)
			{
				if (this.CheckForSignificantChanges())
				{
					this.oEC.ShowConfirmEditDialog(this.oEvent, {params: params});
					return false;
				}
			}

			BX.ajax.submit(this.Form, function()
			{
				if (params.recurentEventEditMode)
				{
					_this.oEC.Event.ReloadAll(false);
				}
				else
				{
					var oRes = top.BXCRES[reqId];
					if (oRes)
					{
						if (oRes.eventIds && oRes.eventIds.length > 0)
						{
							for (var i = 0; i < oRes.eventIds.length; i++)
							{
								_this.oEC.Event.UnDisplay(oRes.eventIds[i], false);
							}
						}
						_this.oEC.HandleEvents(oRes.events, oRes.attendees);
						_this.oEC.arLoadedMonth[month + '.' + year] = true;
						_this.oEC.Event.Display();
					}
				}
			});

			// Color
			var
				sectId = this.pSectSelect.value,
				oSect = _this.oEC.oSections && _this.oEC.oSections[sectId] ? _this.oEC.oSections[sectId] : {},
				text_color = this.TextColor,
				color = this.Color;

			if (!oSect.COLOR || oSect.COLOR && oSect.COLOR.toLowerCase() != color.toLowerCase())
				BX(this.id + '_bxec_color').value = color;
			if (!oSect.TEXT_COLOR || oSect.TEXT_COLOR && oSect.TEXT_COLOR.toLowerCase() != text_color.toLowerCase())
				BX(this.id + '_bxec_text_color').value = text_color;

			if (!this.oEC.arConfig.userTimezoneName)
			{
				this.config.userTimezoneName = this.oEC.arConfig.userTimezoneName = this.pDefTimezone.value;
			}

			if (params.callback)
				params.callback();
		},

		CheckUserAccessibility: function()
		{
			var i, res = true;
			if (this.plannerData)
			{
				for (i in this.plannerData.entries)
				{
					if (this.plannerData.entries.hasOwnProperty(i) &&
						this.plannerData.entries[i].id &&
						this.plannerData.entries[i].status != 'h' &&
						this.plannerData.entries[i].strictStatus &&
						!this.plannerData.entries[i].currentStatus
					)
					{
						res = false;
						break;
					}
				}
			}
			return res;
		},

		CheckForSignificantChanges: function()
		{
			var res = false;

			// Name
			if (!res && this.oEvent.NAME !== this.Form.name.value)
				res = true;
			// Description
			if (!res && this.oEvent.DESCRIPTION !== this.Form.desc.value)
				res = true;
			// Location
			if (!res && this.oEvent.LOCATION !== this.Loc.NEW)
				res = true;

			// Color
			if (!res && this.oEvent.displayColor !== this.Color)
				res = true;
			// Text color
			if (!res && this.oEvent.displayTextColor !== this.TextColor)
				res = true;

			// Date & time
			if (!res && (this.oEvent.DT_SKIP_TIME == 'Y') != this.Form.skip_time.checked)
				res = true;

			if (!res)
			{
				var
					dateFrom = this.pFromDate.value,
					dateTo = this.pToDate.value;

				if (this.oEvent.DT_SKIP_TIME != 'Y')
				{
					// Timezones
					if (!res && (this.oEvent.TZ_FROM != this.Form.tz_from.value || this.oEvent.TZ_TO != this.Form.tz_to.value))
						res = true;

					dateFrom += ' ' + this.pFromTime.value;
					dateTo += ' ' + this.pToTime.value;

					if (!res)
					{
						var
							dFromOrig = this.oEC.ParseDate(this.oEvent['~DATE_FROM']),
							dToOrig = this.oEC.ParseDate(this.oEvent['~DATE_TO']),
							dFrom = this.oEC.ParseDate(dateFrom),
							dTo = this.oEC.ParseDate(dateTo);

							if (!dFromOrig || !dToOrig || !dFrom || !dTo)
								res = true;

						if (!res && Math.abs(dFromOrig.getTime() - dFrom.getTime()) > 1000 || Math.abs(dToOrig.getTime() - dTo.getTime()) > 1000)
							res = true;
					}
				}
				else
				{
					if (!res && (this.oEvent['~DATE_FROM'] != dateFrom || this.oEvent['~DATE_TO'] != dateTo))
						res = true;
				}
			}

			// Attendees
			if (!res && this.plannerData)
			{
				var i, attendeesInd = {}, count = 0;
				if (this.oEvent.IS_MEETING && this.oEvent['~ATTENDEES'])
				{
					for (i in this.oEvent['~ATTENDEES'])
					{
						if (this.oEvent['~ATTENDEES'].hasOwnProperty(i) && this.oEvent['~ATTENDEES'][i]['USER_ID'])
						{
							attendeesInd[this.oEvent['~ATTENDEES'][i]['USER_ID']] = true;
							count++
						}
					}
				}

				// Check if we have new attendees
				for (i in this.plannerData.entries)
				{
					if (this.plannerData.entries.hasOwnProperty(i) && this.plannerData.entries[i].type == 'user' && this.plannerData.entries[i].id)
					{
						if (attendeesInd[this.plannerData.entries[i].id])
						{
							attendeesInd[this.plannerData.entries[i].id] = '+';
						}
						else
						{
							res = true;
							break;
						}
					}
				}

				// Check if we have all old attendees
				if (!res && attendeesInd)
				{
					for (i in attendeesInd)
					{
						if (attendeesInd.hasOwnProperty(i) && attendeesInd[i] !== '+')
						{
							res = true;
							break;
						}
					}
				}
			}

			// Recurtion
			if (!res && (this.oEvent.RRULE.FREQ != this.RepeatSelect.value))
				res = true;

			if (!res && (this.oEvent.RRULE.INTERVAL != this.RepeatCount.value))
				res = true;

			if (!res && this.oEvent.RRULE.FREQ == 'WEEKLY' && this.oEvent.RRULE.BYDAY)
			{
				var BYDAY = [];
				for (i in this.oEvent.RRULE.BYDAY)
				{
					if (this.oEvent.RRULE.BYDAY.hasOwnProperty(i))
					{
						BYDAY.push(this.oEvent.RRULE.BYDAY[i]);
					}
				}
				if (BYDAY.join(',') != BX('event-rrule-byday' + this.id).value)
					res = true;
			}

			return res;
		},

		InitDestinationControls: function()
		{
			BX.addCustomEvent('OnDestinationAddNewItem', BX.proxy(this.CheckPlannerState, this));
			BX.addCustomEvent('OnDestinationUnselect', BX.proxy(this.CheckPlannerState, this));
			BX.addCustomEvent('OnSetTab', BX.proxy(this.OnPlannerTabShow, this));
			this.pMeetingParams = BX('event-grid-meeting-params' + this.id);
			this.pDestValuesCont = BX('event-grid-dest-item');

			this.pPlannerCont = BX('event-grid-planner-cont' + this.id);
			this.plannerId = this.id + '_Planner';
			BX.addCustomEvent('OnCalendarPlannerSelectorChanged', BX.proxy(this.OnCalendarPlannerSelectorChanged, this));
			BX.addCustomEvent('OnCalendarPlannerScaleChanged', BX.proxy(this.OnCalendarPlannerScaleChanged, this));

			BX.bind(this.pDestValuesCont, 'click', function(e)
			{
				var targ = e.target || e.srcElement;
				if (targ.className == 'feed-event-del-but') // Delete button
				{
					BX.SocNetLogDestination.deleteItem(targ.getAttribute('data-item-id'), targ.getAttribute('data-item-type'), editEventDestinationFormName);
					BX.PreventDefault(e);
				}
			});

			BX.bind(this.pDestValuesCont, 'mouseover', function(e)
			{
				var targ = e.target || e.srcElement;
				if (targ.className == 'feed-event-del-but') // Delete button
					BX.addClass(targ.parentNode, 'event-grid-dest-hover');
			});
			BX.bind(this.pDestValuesCont, 'mouseout', function(e)
			{
				var targ = e.target || e.srcElement;
				if (targ.className == 'feed-event-del-but') // Delete button
					BX.removeClass(targ.parentNode, 'event-grid-dest-hover');
			});

			if (this.oEvent.IS_MEETING)
			{
				this.pMeetingParams.style.display = 'block';
			}

			this.AddMeetTextLink = BX(this.id + '_add_meet_text');
			this.HideMeetTextLink = BX(this.id + '_hide_meet_text');
			this.MeetTextCont = BX(this.id + '_meet_text_cont');
			this.MeetText = BX(this.id + '_meeting_text');

			this.OpenMeeting = BX(this.id + '_ed_open_meeting');
			this.NotifyStatus = BX(this.id + '_ed_notify_status');
			this.Reinvite = BX(this.id + '_ed_reivite');
			this.ReinviteCont = BX(this.id + '_ed_reivite_cont');

			if (this.oEvent.IS_MEETING)
			{
				this.OpenMeeting.checked = !!(this.oEvent.MEETING && this.oEvent.MEETING.OPEN);
				this.NotifyStatus.checked = !!(this.oEvent.MEETING && this.oEvent.MEETING.NOTIFY);
				this.Reinvite.checked = this.oEvent.MEETING.REINVITE === true;
			}

			this.AddMeetTextLink.parentNode.style.display = 'none';
		},

		OnCalendarPlannerSelectorChanged: function(params)
		{
			this.pFromDate.value = this.oEC.FormatDate(params.dateFrom);
			this.pFromTime.value = this.oEC.FormatTime(params.dateFrom);
			this.pToDate.value = this.oEC.FormatDate(params.dateTo);
			this.pToTime.value = this.oEC.FormatTime(params.dateTo);
			this.pFullDay.checked = !!params.fullDay;
			this.FullDay(false, !params.fullDay);
		},

		OnCalendarPlannerScaleChanged: function(params)
		{
			this.UpdatePlanner({
				entrieIds: params.entrieIds,
				entries: params.entries,
				from: params.from,
				to: params.to,
				location: this.Loc && this.Loc.NEW ? this.Loc.NEW : '',
				roomEventId: this.Loc && this.Loc.OLD_mrevid ? parseInt(this.Loc.OLD_mrevid) : '',
				focusSelector: params.focusSelector === true
			});
		},

		DestroyDestinationControls: function()
		{
			BX.removeCustomEvent('OnDestinationAddNewItem', BX.proxy(this.CheckPlannerState, this));
			BX.removeCustomEvent('OnDestinationUnselect', BX.proxy(this.CheckPlannerState, this));
			BX.removeCustomEvent('OnCalendarPlannerSelectorChanged', BX.proxy(this.OnCalendarPlannerSelectorChanged, this));
			BX.removeCustomEvent('OnCalendarPlannerScaleChanged', BX.proxy(this.OnCalendarPlannerScaleChanged, this));
			BX.removeCustomEvent('OnSetTab', BX.proxy(this.OnPlannerTabShow, this));
			BX.onCustomEvent('OnCalendarPlannerDoUninstall', [{plannerId: this.plannerId}]);
		},

		OnPlannerTabShow: function(tab, show)
		{
			if (show && tab.tab == BX(this.id + 'ed-tab-2'))
			{
				var
					location = '',
					arLoc = this.oEC.ParseLocation(this.Loc.NEW, true);
				if (arLoc.mrid)
					location = this.Loc.NEW;

				if (this.plannerLoadedTimezone !== this.pFromTz.value || this.plannerLoadedlocation !== location)
				{
					this.CheckPlannerState();
				}
				else
				{
					this.RefreshPlannerState();
				}
			}
		},

		CheckPlannerState: function()
		{
			var
				i, params = {codes: []},
				fromDate = this.oEC.ParseDate(BX.util.trim(this.pFromDate.value)),
				toDate = this.oEC.ParseDate(BX.util.trim(this.pToDate.value)),
				arInputs = this.pDestValuesCont.getElementsByTagName('INPUT'),
				arLoc = this.oEC.ParseLocation(this.Loc.NEW, true),
				locMrind = arLoc.mrind == undefined ? false : arLoc.mrind;

			for (i = 0; i < arInputs.length; i++)
			{
				params.codes.push(arInputs[i].value);
			}

			if (arLoc.mrid)
				params.location = this.Loc.NEW;
			params.locationMrind = locMrind;

			if (this.Loc && this.Loc.OLD_mrevid)
				params.roomEventId = this.Loc.OLD_mrevid;

			if (
				fromDate && toDate &&
				fromDate.getTime && toDate.getTime &&
				fromDate.getTime() <= toDate.getTime() &&
				(params.location || params.codes.length > 0)
			)
			{
				params.from = BX.date.format(this.oEC.DATE_FORMAT, (fromDate.getTime() - this.oEC.dayLength * 3) / 1000);
				params.to = BX.date.format(this.oEC.DATE_FORMAT, (toDate.getTime() + this.oEC.dayLength * 10) / 1000);
				this.UpdatePlanner(params);
			}
			else if (this.pPlannerCont && BX.hasClass(this.pPlannerCont, 'event-grid-planner-cont-shown'))
			{
				this.HidePlanner();
			}
		},

		UpdatePlanner: function(params)
		{
			if (!params)
				params = {};

			this.plannerLoadedTimezone = params.tzFrom || this.pFromTz.value;
			this.plannerLoadedlocation = params.location || '';

			var
				_this = this,
				curEventId = params.eventId || this.oEvent.ID || 0,
				addCurUserToAttendees = !curEventId || !this.oEvent || !this.oEvent.IS_MEETING;

			this.oEC.Request({
				getData: this.oEC.GetReqData('update_planner', {
					codes: params.codes || [],
					cur_event_id: curEventId,
					date_from: params.dateFrom || params.from || '',
					date_to: params.dateTo || params.to || '',
					timezone: this.plannerLoadedTimezone,
					location: this.plannerLoadedlocation,
					roomEventId: params.roomEventId || '',
					entries: params.entrieIds || false,
					add_cur_user_to_list: addCurUserToAttendees ? 'Y' : 'N'
				}),
				handler: function(oRes)
				{
					var
						showPlanner = !!(params.entries || (oRes.entries && oRes.entries.length > 0)),
						plannerShown = BX.hasClass(_this.pPlannerCont, 'event-grid-planner-cont-shown');

					// Show first time or refresh it state
					if (showPlanner)
					{
						var refreshParams = {
							show: showPlanner && !plannerShown
						};

						if (params.entries)
						{
							oRes.entries = params.entries;
							refreshParams.scaleFrom = params.from;
							refreshParams.scaleTo = params.to;
						}

						refreshParams.loadedDataFrom = params.from;
						refreshParams.loadedDataTo = params.to;

						refreshParams.data = {
							entries: oRes.entries,
							accessibility: oRes.accessibility
						};

						refreshParams.focusSelector = params.focusSelector == undefined ? false : params.focusSelector;

						_this.ShowPlannerAnimation();
						_this.RefreshPlannerState(refreshParams);
					}
					else if (!showPlanner && plannerShown) // Hide
					{
						_this.HidePlanner();
					}
				}
			});
		},

		RefreshPlannerState: function(params)
		{
			if (!params || typeof params !== 'object')
				params = {};

			this.plannerData = params.data;

			var
				fromDate, toDate,
				fullDay = this.pFullDay.checked,
				config = {}, i,
				plannerWidth,
				scaleFrom, scaleTo,
				plannerShown = this.pPlannerCont && BX.hasClass(this.pPlannerCont, 'event-grid-planner-cont-shown');

			if (params.focusSelector == undefined)
				params.focusSelector = true;

			if (fullDay)
			{
				fromDate = this.oEC.ParseDate(BX.util.trim(this.pFromDate.value));
				toDate = this.oEC.ParseDate(BX.util.trim(this.pToDate.value)) || fromDate;
			}
			else
			{
				fromDate = this.oEC.ParseDate(BX.util.trim(this.pFromDate.value) + ' ' + BX.util.trim(this.pFromTime.value));
				if (this.pToDate.value == '')
					this.pToDate.value = this.pFromDate.value;
				toDate = this.oEC.ParseDate(BX.util.trim(this.pToDate.value) + ' ' + BX.util.trim(this.pToTime.value));
			}

			if (fromDate && toDate &&
				fromDate.getTime && toDate.getTime &&
				fromDate.getTime() <= toDate.getTime())
			{
				if (!plannerShown && !params.data)
				{
					this.CheckPlannerState();
				}
				else
				{
					// Show planner cont
					if (params.show)
					{
						BX.addClass(this.pPlannerCont, 'event-grid-planner-cont-shown');
						this.pMeetingParams.style.display = 'block';
						plannerWidth = this.pPlannerCont.offsetWidth - 8;
						if (!plannerShown && params.show)
						{
							params.focusSelector = true;
						}
					}

					if (fullDay)
					{
						scaleFrom = new Date(fromDate.getTime());
						scaleFrom = params.scaleFrom || new Date(scaleFrom.getTime() - this.oEC.dayLength * 3);
						scaleTo = params.scaleTo || new Date(scaleFrom.getTime() + this.oEC.dayLength * 10);
						config.scaleType = '1day';
						config.scaleDateFrom = scaleFrom;
						config.scaleDateTo = scaleTo;
						config.adjustCellWidth = false;
					}
					else
					{
						config.changeFromFullDay = {
							scaleType: '1hour',
							timelineCellWidth: 40
						};
						config.shownScaleTimeFrom = parseInt(this.config.workTimeStart);
						config.shownScaleTimeTo = parseInt(this.config.workTimeEnd);
					}
					config.entriesListWidth = 170;
					config.width = plannerWidth;

					// RRULE
					var RRULE = false;
					if (this.RepeatCheck.checked)
					{
						RRULE = {
							FREQ: this.RepeatSelect.value,
							INTERVAL: this.RepeatCount.value,
							UNTIL: this.RepeatDiapTo.value
						};

						if (RRULE.UNTIL == EC_MESS.NoLimits)
							RRULE.UNTIL = '';

						if (RRULE.FREQ == 'WEEKLY')
						{
							RRULE.WEEK_DAYS = [];
							for (i = 0; i < 7; i++)
							{
								if (this.RepeatWeekDaysCh[i].checked)
								{
									RRULE.WEEK_DAYS.push(this.RepeatWeekDaysCh[i].value);
								}
							}

							if (!RRULE.WEEK_DAYS.length)
							{
								RRULE = false;
							}
						}
					}

					BX.onCustomEvent('OnCalendarPlannerDoUpdate', [
						{
							plannerId: this.plannerId,
							config: config,
							focusSelector: params.focusSelector,
							selector: {
								from: fromDate,
								to: toDate,
								fullDay: !!this.pFullDay.checked,
								RRULE: RRULE,
								animation: true,
								updateScaleLimits: true
							},
							data: params.data || false,
							loadedDataFrom: params.loadedDataFrom,
							loadedDataTo: params.loadedDataTo,
							show: !!params.show
						}
					]);
				}
			}
			else if (plannerShown)
			{
				this.HidePlanner();
			}
		},

		HidePlanner: function()
		{
			var _this = this;

			// Opacity animation
			this.pPlannerCont.style.opacity = 1;
			this.pPlannerCont.style.display = '';
			this.pPlannerCont.style.height = this.pPlannerCont.offsetHeight + 'px';
			this.pPlannerCont.style.overflow = 'hidden';

			BX.onCustomEvent('OnCalendarPlannerDoUpdate', [
				{
					plannerId: this.plannerId,
					hide: true
				}
			]);

			this.pMeetingParams.style.display = 'none';

			new BX.easing({
				duration: 600,
				start: {opacity: 100, height: parseInt(this.pPlannerCont.offsetHeight), padding: 14},
				finish: {opacity: 0, height: 0, padding: 0},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
				step: function(state)
				{
					_this.pPlannerCont.style.opacity = state.opacity / 100;
					_this.pPlannerCont.style.height = state.height + 'px';
				},
				complete: function()
				{
					_this.pPlannerCont.removeAttribute('style');
					BX.removeClass(_this.pPlannerCont, 'event-grid-planner-cont-shown');
				}
			}).animate();
		},

		ShowPlannerAnimation: function()
		{
			var _this = this;
			// Opacity animation
			this.pPlannerCont.style.opacity = 0;
			this.pPlannerCont.style.display = '';
			new BX.easing({
				duration: 300,
				start: {opacity: 0},
				finish: {opacity: 100},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
				step: function(state){
					_this.pPlannerCont.style.opacity = state.opacity / 100;
				},
				complete: function(){
					_this.pPlannerCont.removeAttribute('style');
				}
			}).animate();
		},

		InitDateTimeControls: function()
		{
			// From-to
			this.pFromToCont = BX('feed-cal-from-to-cont' + this.id);
			this.pFromDate = BX('feed-cal-event-from' + this.id);
			this.pToDate = BX('feed-cal-event-to' + this.id);
			this.pFromTime = BX('feed_cal_event_from_time' + this.id);
			this.pToTime = BX('feed_cal_event_to_time' + this.id);
			this.pFullDay = BX('event-full-day' + this.id);
			this.pDefTimezone = BX('event-tz-def' + this.id);
			this.pDefTimezoneWrap = BX('event-tz-def-wrap' + this.id);
			this.pDefTimezone.onchange = BX.proxy(this.DefaultTimezoneOnChange, this);
			this.pFromTz = BX('event-tz-from' + this.id);
			this.pToTz = BX('event-tz-to' + this.id);
			this.pTzOuterCont = BX('event-tz-cont-outer' + this.id);
			this.pTzSwitch = BX('event-tz-switch' + this.id);
			this.pTzCont = BX('event-tz-cont' + this.id);
			this.pTzInnerCont = BX('event-tz-inner-cont' + this.id);
			this.pTzSwitch.onclick = BX.proxy(this.TimezoneSwitch, this);
			this.pFromTz.onchange = BX.proxy(this.TimezoneFromOnChange, this);
			this.pToTz.onchange = BX.proxy(this.TimezoneToOnChange, this);
			// Timezone hints for dialog
			new BX.CHint({parent: BX('event-tz-tip' + this.id), hint: EC_MESS.eventTzHint});
			new BX.CHint({parent: BX('event-tz-def-tip' + this.id), hint: EC_MESS.eventTzDefHint});

			//Reminder
			this.pReminderCont = BX('feed-cal-reminder-cont' + this.id);
			this.pReminder = BX('event-reminder' + this.id);
			this.pRemType = BX('event_remind_type' + this.id);
			this.pRemCount = BX('event_remind_count' + this.id);
			// Control events
			this.pFullDay.onclick = BX.proxy(this.FullDay, this);
			this.pReminder.onclick = BX.proxy(this.Reminder, this);

			var _this = this;
			// Date
			this.pFromDate.onclick = function(){BX.calendar({node: this.parentNode, field: this, bTime: false});};
			this.pToDate.onclick = function(){BX.calendar({node: this.parentNode, field: this, bTime: false});};

			this.pFromDate.onchange = function()
			{
				if(_this._FromDateValue)
				{
					var
						prevF = BX.parseDate(_this._FromDateValue),
						F = BX.parseDate(_this.pFromDate.value),
						T = BX.parseDate(_this.pToDate.value);

					if (F && prevF)
					{
						var duration = T.getTime() - prevF.getTime();
						if (duration < 0)
							duration = 0;
						T = new Date(F.getTime() + duration);
						if (T)
							_this.pToDate.value = bxFormatDate(T.getDate(), T.getMonth() + 1, T.getFullYear());
					}
				}
				_this._FromDateValue = _this.pFromDate.value;
			};

			// Time
			this.pFromTime.parentNode.onclick = this.pFromTime.onclick = window['bxShowClock_' + 'feed_cal_event_from_time' + _this.id];
			this.pToTime.parentNode.onclick = this.pToTime.onclick = window['bxShowClock_' + 'feed_cal_event_to_time' + _this.id];

			this.pFromTime.onchange = function()
			{
				var fromDate = _this.oEC.ParseDate(BX.util.trim(_this.pFromDate.value) + ' ' + BX.util.trim(_this.pFromTime.value));
				if (_this.pToDate.value == '')
					_this.pToDate.value = _this.pFromDate.value;

				var toDate = _this.oEC.ParseDate(BX.util.trim(_this.pToDate.value) + ' ' + BX.util.trim(_this.pToTime.value));

				if (_this._FromTimeValue)
				{
					var prefFromDate = _this.oEC.ParseDate(BX.util.trim(_this.pFromDate.value) + ' ' + _this._FromTimeValue);
					var duration = toDate.getTime() - prefFromDate.getTime();
					if (duration < 0)
						duration = 3600000; // 1 hour

					var newToDate = new Date(fromDate.getTime() + duration);
					_this.pToDate.value = _this.oEC.FormatDate(newToDate);
					_this.pToTime.value = _this.oEC.FormatTime(newToDate);
				}

				_this._FromTimeValue = _this.pFromTime.value;
			};

			var dateFrom, dateTo;
			if (this.oEvent.ID)
			{
				this.pFullDay.checked = this.bFullDay = this.oEvent.DT_SKIP_TIME == 'Y';
				this.pFullDay.onclick();

				dateFrom = (BX.parseDate(this.oEvent['~DATE_FROM'] || this.oEvent.DATE_FROM));
				dateTo = (BX.parseDate(this.oEvent['~DATE_TO'] || this.oEvent.DATE_TO));

				this.pFromDate.value = this.oEC.FormatDate(dateFrom);
				this.pToDate.value = this.oEC.FormatDate(dateTo);

				if (this.oEvent.DT_SKIP_TIME !== 'Y')
				{
					this.pFromTime.value = this.oEC.FormatTime(dateFrom);
					this.pToTime.value = this.oEC.FormatTime(dateTo);

					this.pFromTz.value = this.oEvent.TZ_FROM || '';
					this.pToTz.value = this.oEvent.TZ_TO || '';

					if (this.oEvent.TZ_TO !== this.oEvent.TZ_FROM ||
						this.oEvent.TZ_TO !== this.config.userTimezoneName ||
						parseInt(this.oEvent['~USER_OFFSET_FROM']) > 0 ||
						parseInt(this.oEvent['~USER_OFFSET_TO']) > 0)
					{
						this.pTzCont.style.height = this.pTzInnerCont.offsetHeight + 'px';
						BX.addClass(this.pTzOuterCont, 'bxec-timezone-outer-wrap-opened');
					}
				}

				// Reminder
				if(this.oEvent.REMIND && this.oEvent.REMIND.length > 0)
				{
					// Default value
					this.pReminder.checked = true;
					this.pRemType.value = this.oEvent.REMIND[0].type;
					this.pRemCount.value = this.oEvent.REMIND[0].count;
					this.Reminder(false, true);
				}
				else
				{
					// Default value
					this.pReminder.checked = false;
					this.pRemType.value = 'min';
					this.pRemCount.value = '15';
					this.Reminder(false, false);
				}

				if (this.config.userTimezoneName)
				{
					this.pDefTimezoneWrap.style.display = 'none';
				}
				else
				{
					this.pDefTimezoneWrap.style.display = '';
					this.pDefTimezone.value = this.config.userTimezoneDefault || '';
				}
			}
			else // new event
			{
				// Dafault values for from-to fields
				dateFrom = this.oEC.GetUsableDateTime(new Date().getTime(), 15);
				dateTo = this.oEC.GetUsableDateTime(dateFrom.getTime() + 3600000 /* one hour*/, 15);

				this.pFromDate.value = this.oEC.FormatDate(dateFrom);
				this.pFromTime.value = this.oEC.FormatTime(dateFrom);
				this.pToDate.value = this.oEC.FormatDate(dateTo);
				this.pToTime.value = this.oEC.FormatTime(dateTo);

				if (this.config.userTimezoneName)
				{
					this.pDefTimezoneWrap.style.display = 'none';
					this.pDefTimezone.value = this.config.userTimezoneName;
					this.pFromTz.value = this.pToTz.value = this.config.userTimezoneName;
				}
				else
				{
					this.pDefTimezoneWrap.style.display = '';
					this.pFromTz.value = this.pToTz.value = this.pDefTimezone.value = this.config.userTimezoneDefault || '';
				}

				// Default value
				this.pFullDay.checked = false;
				this.FullDay(false, true);

				// Default value
				this.pReminder.checked = true;
				this.pRemType.value = 'min';
				this.pRemCount.value = '15';
				this.Reminder(false, true);
			}
			this.linkFromToTz = this.pFromTz.value == this.pToTz.value;
			this.linkFromToDefaultTz = this.pFromTz.value == this.pToTz.value && this.pFromTz.value == this.pDefTimezone.value;

			this._FromTimeValue = this.pFromTime.value;
			this._FromDateValue = this.pFromDate.value;
		},

		FillFormFields: function()
		{
			var _this = this;
			this.pName = BX(this.id + '_edit_ed_name');
			this.pName.value = this.oEvent.NAME || '';
			this.Title = this.config.Title;

			this.pName.onkeydown = this.pName.onchange = function()
			{
				if (this._titleTimeout)
					clearTimeout(this._titleTimeout);

				this._titleTimeout = setTimeout(
					function(){
						var
							val = BX.util.htmlspecialchars(_this.pName.value);
						_this.Title.innerHTML = (_this.oEvent.ID ? EC_MESS.EditEvent : EC_MESS.NewEvent) + (val != '' ? ': ' + val : '');
					}, 20
				);
			};

			// Location
			this.Location = new BXInputPopup({
				id: this.id + 'loc_1',
				values: this.oEC.bUseMR ? this.oEC.meetingRooms : false,
				input: BX(this.id + '_planner_location1'),
				defaultValue: EC_MESS.SelectMR,
				openTitle: EC_MESS.OpenMRPage,
				className: 'calendar-inp calendar-inp-time',
				noMRclassName: 'calendar-inp calendar-inp-time'
			});
			this.Loc = {};
			BX.addCustomEvent(this.Location, 'onInputPopupChanged', BX.proxy(this.LocationOnChange, this));

			if (this.oEvent.ID)
			{
				var loc = BX.util.htmlspecialcharsback(this.oEvent.LOCATION);
				this.Loc.OLD = loc;
				this.Loc.NEW = loc;
				var arLoc = this.oEC.ParseLocation(loc, true);
				if (arLoc.mrid && arLoc.mrevid)
				{
					this.Location.Set(arLoc.mrind, '');
					this.Loc.OLD_mrid = arLoc.mrid;
					this.Loc.OLD_mrevid = arLoc.mrevid;
				}
				else
				{
					this.Location.Set(false, loc);
				}
			}
			else
			{
				this.Location.Set(false, '');
			}

			// Accessibility
			this.pAccessibility = BX(this.id + '_bxec_accessibility');
			if (this.pAccessibility)
				this.pAccessibility.value = this.oEvent.ACCESSIBILITY || 'busy';
			// Private
			this.pPrivate = BX(this.id + '_bxec_private');
			if (this.pPrivate)
				this.pPrivate.checked = this.oEvent.PRIVATE_EVENT || false;
			// Importance
			this.pImportance = BX(this.id + '_bxec_importance');
			if (this.pImportance)
				this.pImportance.value = this.oEvent.IMPORTANCE || 'normal';

			// Sections
			this.pSectSelect = BX(this.id + '_edit_ed_calend_sel');
			var sectId = this.oEvent.SECT_ID || this.oEC.GetLastSection();
			if (!this.oEC.oSections[sectId])
			{
				sectId = this.oEC.arSections[0].ID;
				this.oEC.SaveLastSection(sectId);
			}

			this.oEC.BuildSectionSelect(this.pSectSelect, sectId);
			this.pSectSelect.onchange = function()
			{
				var sectId = this.value;
				if (_this.oEC.oSections[sectId])
				{
					_this.oEC.SaveLastSection(sectId);
					//D.CAL.DOM.Warn.style.display = _this.oActiveSections[sectId] ? 'none' : 'block';
					_this.ColorControl.Set(_this.oEC.oSections[sectId].COLOR, _this.oEC.oSections[sectId].TEXT_COLOR);
				}
			};

			// Repeat
			this.RepeatCheck = BX(this.id + '_edit_ed_rep_check');
			this.RepeatSelect = BX(this.id + '_edit_ed_rep_sel');
			this.RepeatCont = BX(this.id + '_edit_ed_rep_cont');

			this.RepeatPhrase1 = BX(this.id + '_edit_ed_rep_phrase1');
			this.RepeatPhrase2 = BX(this.id + '_edit_ed_rep_phrase2');
			this.RepeatWeekDays = BX(this.id + '_edit_ed_rep_week_days');
			this.RepeatCount = BX(this.id + '_edit_ed_rep_count');

			this.RepeatEndsOnNever = BX(this.id + 'edit-ev-rep-endson-never');
			this.RepeatEndsOnCount = BX(this.id + 'edit-ev-rep-endson-count');
			this.RepeatEndsOnUntil = BX(this.id + 'edit-ev-rep-endson-until');
			this.RepeatDiapTo = BX(this.id + 'edit-ev-rep-diap-to');
			this.RepeatCountInp = BX(this.id + 'edit-ev-rep-endson-count-input');

			this.RepeatSelect.onchange = function() {_this.RepeatSelectOnChange(this.value);};
			this.RepeatCount.onmousedown = function() {_this.bEditEventDialogOver = true;};

			this.RepeatCheck.onclick = function()
			{
				if (this.checked)
					BX.addClass(_this.RepeatCont, 'bxec-popup-row-repeat-show');
				else
					BX.removeClass(_this.RepeatCont, 'bxec-popup-row-repeat-show');
			};

			BX.bind(this.RepeatEndsOnNever, 'change', BX.proxy(this.EndsOnChange, this));
			BX.bind(this.RepeatEndsOnCount, 'change', BX.proxy(this.EndsOnChange, this));
			BX.bind(this.RepeatEndsOnUntil, 'change', BX.proxy(this.EndsOnChange, this));

			BX.bind(this.RepeatDiapTo, 'click', BX.proxy(function()
			{
				this.RepeatEndsOnUntil.checked = 'checked';
				BX.calendar({node: this.RepeatDiapTo, field: this.RepeatDiapTo, bTime: false});
				BX.focus(this.RepeatDiapTo);
				this.EndsOnChange();
			}, this));

			BX.bind(this.RepeatCountInp, 'click', BX.proxy(function()
			{
				this.RepeatEndsOnCount.checked = 'checked';
				BX.focus(this.RepeatCountInp);
				this.EndsOnChange();
			}, this));

			/*
			this.RepeatDiapTo.onblur = this.RepeatDiapTo.onchange = function()
			{
				//if (this.value && this.value != EC_MESS.NoLimits)
				//{
				//	this.style.color = '#000000';
				//	return;
				//}
				//this.value = EC_MESS.NoLimits;
				//this.style.color = '#C0C0C0';
			};

			this.RepeatDiapTo.onclick = function(){BX.calendar({node: this, field: this, bTime: false});BX.focus(this);};
			this.RepeatDiapTo.onfocus = function()
			{
				if (!this.value || this.value == EC_MESS.NoLimits)
					this.value = '';
				this.style.color = '#000000';
			};
			*/

			// Set recurtion rules "RRULE"
			if (this.oEC.Event.IsRecursive(this.oEvent))
			{
				this.RepeatCheck.checked = true;
				this.RepeatSelect.value = this.oEvent.RRULE.FREQ;

			}
			else
			{
				this.RepeatCheck.checked = false;
			}
			this.RepeatCheck.onclick();
			this.RepeatSelect.onchange();

			// Color
			this.ColorControl = this.oEC.InitColorDialogControl('event', function(color, textColor)
			{
				_this.Color = color;
				_this.TextColor = textColor;
			});

			if (!this.oEvent.displayColor && this.oEC.oSections[sectId])
				this.oEvent.displayColor = this.oEC.oSections[sectId].COLOR;
			if (!this.oEvent.displayTextColor && this.oEC.oSections[sectId])
				this.oEvent.displayTextColor = this.oEC.oSections[sectId].TEXT_COLOR;
			if (this.oEvent.displayColor)
				this.ColorControl.Set(this.oEvent.displayColor, this.oEvent.displayTextColor);
			else if(this.oEC.oSections[sectId])
				this.ColorControl.Set(this.oEC.oSections[sectId].COLOR, this.oEC.oSections[sectId].TEXT_COLOR);
		},

		EndsOnChange: function()
		{
			if (this.RepeatEndsOnNever.checked)
			{
				this.RepeatCountInp.value = '';
				this.RepeatDiapTo.value = '';
			}
			else if (this.RepeatEndsOnCount.checked)
			{
				this.RepeatDiapTo.value = '';
				if (!this.RepeatCountInp.value)
					this.RepeatCountInp.value = this.RepeatCountInp.placeholder;
				BX.focus(this.RepeatCountInp);
				this.RepeatCountInp.select();
			}
			else
			{
				this.RepeatCountInp.value = '';
				BX.focus(this.RepeatDiapTo);
				this.RepeatDiapTo.select();
			}
		},

		LocationOnChange: function(oLoc, ind, value)
		{
			this.Loc.NEW = (ind === false)
				?
					(value || '')
				:
					'ECMR_' + this.oEC.meetingRooms[ind].ID;
		},

		RepeatSelectOnChange: function(val)
		{
			var i, BYDAY, date;

			val = val.toUpperCase();

			if (val == 'NONE')
			{
				//this.RepeatSect.style.display =  'none';
			}
			else
			{
				//this.RepeatSect.style.display =  'block';
				this.RepeatPhrase2.innerHTML = EC_MESS.DeDot; // Works only for de lang

				if (val == 'WEEKLY')
				{
					this.RepeatPhrase1.innerHTML = EC_MESS.EveryF;
					this.RepeatPhrase2.innerHTML += EC_MESS.WeekP;
					this.RepeatWeekDays.style.display = (val == 'WEEKLY') ? 'inline-block' : 'none';
					BYDAY = {};

					if (!this.RepeatWeekDaysCh)
					{
						this.RepeatWeekDaysCh = [];
						for (i = 0; i < 7; i++)
							this.RepeatWeekDaysCh[i] = BX(this.id + 'bxec_week_day_' + i);
					}

					if (this.oEvent && this.oEvent.ID && this.oEvent.RRULE && this.oEvent.RRULE.BYDAY)
					{
						BYDAY = this.oEvent.RRULE.BYDAY;
					}
					else
					{
						date = BX.parseDate(this.pFromDate.value);
						if (!date)
							date = bxGetDateFromTS(this.oEvent.DT_FROM_TS);

						if(date)
							BYDAY[this.oEC.GetWeekDayByInd(date.getDay())] = true;
					}

					for (i = 0; i < 7; i++)
						this.RepeatWeekDaysCh[i].checked = !!BYDAY[this.RepeatWeekDaysCh[i].value];
				}
				else
				{
					if (val == 'YEARLY')
						this.RepeatPhrase1.innerHTML = EC_MESS.EveryN;
					else
						this.RepeatPhrase1.innerHTML = EC_MESS.EveryM;

					if (val == 'DAILY')
						this.RepeatPhrase2.innerHTML += EC_MESS.DayP;
					else if (val == 'MONTHLY')
						this.RepeatPhrase2.innerHTML += EC_MESS.MonthP;
					else if (val == 'YEARLY')
						this.RepeatPhrase2.innerHTML += EC_MESS.YearP;

					this.RepeatWeekDays.style.display = 'none';
				}

				var bPer = this.oEvent && this.oEC.Event.IsRecursive(this.oEvent);
				this.RepeatCount.value = (!this.oEvent.ID || !bPer) ? 1 : this.oEvent.RRULE.INTERVAL;

				if (!this.oEvent.ID || !bPer)
				{
					this.RepeatEndsOnNever.checked = true;
				}
				else
				{
					if (this.oEvent.RRULE && this.oEvent.RRULE.COUNT > 0)
					{
						this.RepeatCountInp.value = parseInt(this.oEvent.RRULE.COUNT);
						this.RepeatEndsOnCount.checked = true;
					}
					else if (this.oEvent.RRULE && this.oEvent.RRULE['~UNTIL'])
					{
						this.RepeatDiapTo.value = this.oEvent.RRULE['~UNTIL'];
						this.RepeatEndsOnUntil.checked = true;
					}
					else
					{
						this.RepeatEndsOnNever.checked = true;
					}
					this.EndsOnChange();
				}
			}
		},

		CustomizeHtmlEditor: function(editor)
		{
			if (editor.toolbar.controls && editor.toolbar.controls.spoiler)
			{
				BX.remove(editor.toolbar.controls.spoiler.pCont);
			}
		},

		FullDay: function(bSaveOption, value)
		{
			if (value == undefined)
				value = !this.bFullDay;

			if (value &&
				this.pFromDate.value !== '' && this.pFromTime.value === '' &&
				this.pToDate.value !== '' && this.pToTime.value === '')
			{
				var
					dateFrom = BX.parseDate(this.pFromDate.value),
					dateTo = BX.parseDate(this.pToDate.value),
					oneDay = dateFrom.getTime() === dateTo.getTime();

				if (dateFrom)
				{
					dateFrom.setHours(12);
					dateFrom.setMinutes(0);
					this.pFromTime.value = this.oEC.FormatTime(dateFrom);
				}
				if (dateTo)
				{
					dateTo.setHours(oneDay ? 13 : 12);
					dateTo.setMinutes(0);
					this.pToTime.value = this.oEC.FormatTime(dateTo);
				}

				this.UpdateAccessibility();
			}

			if (value && this.config.userTimezoneName && (this.pFromTz.value == '' || this.pToTz.value == ''))
			{
				this.pFromTz.value = this.pToTz.value = this.pDefTimezone.value = this.config.userTimezoneName;
			}

			if (value)
			{
				BX.removeClass(this.pFromToCont, 'feed-cal-full-day');
			}
			else
			{
				BX.addClass(this.pFromToCont, 'feed-cal-full-day');
			}
			this.bFullDay = value;
		},

		Reminder: function(bSaveOption, value)
		{
			if (value == undefined)
				value = !this.bReminder;

			this.pReminderCont.className = value ? 'bxec-reminder' : 'bxec-reminder-collapsed';

			this.bReminder = value;
		},

		TimezoneSwitch: function()
		{
			if(this.pTzCont.offsetHeight > 0)
			{
				this.pTzCont.style.height = 0;
				BX.removeClass(this.pTzOuterCont, 'bxec-timezone-outer-wrap-opened');
			}
			else
			{
				this.pTzCont.style.height = this.pTzInnerCont.offsetHeight + 'px';
				BX.addClass(this.pTzOuterCont, 'bxec-timezone-outer-wrap-opened');
			}
		},

		DefaultTimezoneOnChange: function()
		{
			var defTimezoneName = this.pDefTimezone.value;
			BX.userOptions.save('calendar', 'timezone_name', 'timezone_name', defTimezoneName);
			if (this.linkFromToDefaultTz)
				this.pToTz.value = this.pFromTz.value = this.pDefTimezone.value;
		},

		TimezoneFromOnChange: function()
		{
			if (this.linkFromToTz)
				this.pToTz.value = this.pFromTz.value;
			this.linkFromToDefaultTz = false;

		},

		TimezoneToOnChange: function()
		{
			this.linkFromToTz = false;
			this.linkFromToDefaultTz = false;
		},

		UpdateAccessibility: function(bTimeout)
		{
			var
				_this = this;

			//if (this.attendeeIndex = {};)
			if (bTimeout !== false)
			{
				if (this.updateAccessibilityTimeout)
					this.updateAccessibilityTimeout = clearTimeout(this.updateAccessibilityTimeout);

				this.updateAccessibilityTimeout = setTimeout(function()
				{
					_this.UpdateAccessibility(false);
				}, 500);
				return;
			}
			//get_accessibility
		},

		ParseDateFromTo: function()
		{
			var
				dateFrom = this.pFromDate.value,
				dateTo = this.pToDate.value;

			if (!this.pFullDay.checked)
			{
				dateFrom += ' ' + this.pFromTime.value;
				dateTo += ' ' + this.pToTime.value;
			}
			return {
				from: this.oEC.ParseDate(dateFrom),
				to: this.oEC.ParseDate(dateTo)
			};
		}
	};

	window.ECDragDropControl = function(Params)
	{
		this.oEC = Params.calendar;
		this.enabled = true;
	};

	window.ECDragDropControl.prototype = {
		Reset: function()
		{
			jsDD.Reset();
		},

		RegisterDay: function(dayCont)
		{
			if(!this.enabled)
				return;

			var _this = this;
			jsDD.registerDest(dayCont);

			dayCont.onbxdestdragfinish = function(currentNode, x, y)
			{
				if (_this.oDiv)
				{
					var
						eventInd = parseInt(_this.oDiv.getAttribute('data-bx-event-ind')),
						dayDate = new Date(_this.oEC.activeDateDays[_this.oEC.GetDayIndexByElement(dayCont.parentNode)].getTime());

					if (!isNaN(eventInd) && _this.oEC.arEvents[eventInd])
						_this.MoveEventToNewDate(_this.oEC.arEvents[eventInd], dayDate, "day");

					BX.removeClass(dayCont, 'bxc-day-drag');
				}

				_this.OnDragFinish();

				return true;
			};
			dayCont.onbxdestdraghover = function(currentNode, x, y)
			{
				if (_this.oDiv)
					BX.addClass(dayCont, 'bxc-day-drag');
			};
			dayCont.onbxdestdraghout = function(currentNode, x, y)
			{
				if (_this.oDiv)
					BX.removeClass(dayCont, 'bxc-day-drag');
			};
		},

		RegisterTitleDay: function(dayCont1, dayCont2, tabId)
		{
			if(!this.enabled)
				return;

			var _this = this;
			jsDD.registerDest(dayCont1);
			jsDD.registerDest(dayCont2);

			dayCont1.onbxdestdragfinish = dayCont2.onbxdestdragfinish = function(currentNode, x, y)
			{
				if (_this.oDiv)
				{
					var
						eventInd = parseInt(_this.oDiv.getAttribute('data-bx-event-ind')),
						dayInd = parseInt(dayCont1.getAttribute('data-bx-day-ind')),
						day = _this.oEC.Tabs[tabId].arDays[dayInd],
						dayDate = new Date();
					dayDate.setFullYear(day.year, day.month, day.date);

					if (!isNaN(eventInd) && _this.oEC.arEvents[eventInd])
						_this.MoveEventToNewDate(_this.oEC.arEvents[eventInd], dayDate, "day");
				}

				BX.removeClass(dayCont1, 'bxc-day-drag');
				BX.removeClass(dayCont2, 'bxc-day-drag');

				_this.OnDragFinish();
				return true;
			};
			dayCont1.onbxdestdraghover = dayCont2.onbxdestdraghover = function(currentNode, x, y)
			{
				BX.addClass(dayCont1, 'bxc-day-drag');
				BX.addClass(dayCont2, 'bxc-day-drag');
			};
			dayCont1.onbxdestdraghout = dayCont2.onbxdestdraghout = function(currentNode, x, y)
			{
				BX.removeClass(dayCont1, 'bxc-day-drag');
				BX.removeClass(dayCont2, 'bxc-day-drag');
			};
		},

		RegisterTimeline: function(timelineCont, oTab)
		{
			if(!this.enabled)
				return;

			var _this = this;
			jsDD.registerDest(timelineCont);

			timelineCont.onbxdestdragfinish = function(currentNode, x, y)
			{
				if (_this.oDiv)
				{
					var eventInd = parseInt(_this.oDiv.getAttribute('data-bx-event-ind'));
					if (isNaN(eventInd) || !_this.oEC.arEvents[eventInd])
						return;
					var oEvent = _this.oEC.arEvents[eventInd];

					if (currentNode.getAttribute('data-bx-event-resizer') == 'Y')
					{
						// Delta height
						var
							originalHeight = parseInt(_this.oDiv.getAttribute('data-bx-original-height'), 10),
							deltaHeight = _this.oDiv.offsetHeight - originalHeight,
							dur = parseInt(((deltaHeight - 1) / 40) * 3600); // In seconds

						_this.ResizeEventTimeline(oEvent, dur);
					}
					else
					{
						var dayInd = _this.oDiv.getAttribute('data-bx-day-index');

						if (dayInd != undefined && oTab.arDays[dayInd])
						{
							var
								curDay = oTab.arDays[dayInd],
								eventY = parseInt(_this.oDiv.style.top, 10) - BX.pos(timelineCont).top + timelineCont.scrollTop,
								dtFrom = Math.max((eventY - 1) / 42 * 60, 0); // In seconds

							dtFrom = Math.round(dtFrom / 10) * 10; // Round to 10 minutes

							var
								hour = parseInt(dtFrom / 60, 10),
								min = Math.max(dtFrom - hour * 60, 0),
								dayDate = new Date();

							dayDate.setFullYear(curDay.year, curDay.month, curDay.date);
							dayDate.setHours(hour);
							dayDate.setMinutes(min);
							dayDate.setSeconds(0);

							if (_this.oDiv.getAttribute("data-bx-title-event"))
							{
								oEvent.DT_SKIP_TIME = 'N'; // It cames from title
								_this.MoveEventToNewDate(oEvent, dayDate, "timeline", 3600000);
							}
							else
								_this.MoveEventToNewDate(oEvent, dayDate, "timeline");
						}
					}
				}

				_this.OnDragFinish();
				return true;
			};

			timelineCont.onbxdestdraghover = function(currentNode, x, y)
			{
				_this.timeLineEventOver = true;
				_this.PrepareTimelineDaysPos(timelineCont, oTab);
				BX.addClass(timelineCont, 'bxec-timeline-div-drag');
			};

			timelineCont.onbxdestdraghout = function(currentNode, x, y)
			{
				_this.ClearTimeline(timelineCont);
			};
			timelineCont.onbxdestdragstop = function(currentNode, x, y)
			{
				_this.ClearTimeline(timelineCont);
			};
		},

		ClearTimeline: function(timelineCont)
		{
			this.timeLineEventOver = false;
			BX.removeClass(timelineCont, 'bxec-timeline-div-drag');
			jsDD.current_dest_index = false;
		},

		GetTimelinePos: function(obDest)
		{
			return obDest.__bxpos;
		},

		PrepareTimelineDaysPos: function(timelineCont, oTab)
		{
			this.timeLinePos = this.GetTimelinePos(timelineCont);

			var pTimelineRow = oTab.pTimelineTable.rows[0];

			var dayCell, i, dayPos;
			this.arDays = [];
			for (var i = 1; i < pTimelineRow.cells.length; i++)
			{
				dayCell = pTimelineRow.cells[i];
				dayPos = BX.pos(dayCell);
				dayPos._left = dayPos.left - this.timeLinePos[0];
				dayPos._right = dayPos.right - this.timeLinePos[0];
				this.arDays.push(dayPos);
			}

			if (!this.activeDayDrop)
			{
				this.activeDayDrop = BX.create("DIV", {props: {className: 'bxec-timeline-active-day-drag-selector'}});
				this.activeDayDrop.style.height = parseInt(oTab.pTimelineTable.offsetHeight, 10) + 'px';
			}
			if (this.activeDayDrop.parentNode != timelineCont)
				timelineCont.appendChild(this.activeDayDrop);

			if (!this.timelineDragOverlay)
			{
				this.timelineDragOverlay = BX.create("DIV", {props: {className: 'bxec-timeline-drag-overlay'}});
				this.timelineDragOverlay.style.height = parseInt(oTab.pTimelineTable.offsetHeight, 10) + 'px';
			}
			if (this.timelineDragOverlay.parentNode != timelineCont)
				timelineCont.appendChild(this.timelineDragOverlay);
		},

		CheckTimelineOverPos: function(x, y)
		{
			if (this.timeLineEventOver)
			{
				this.activeDayDrop.style.display = 'block';
				var i, l = this.arDays.length;

				for (i = 0; i < l; i++)
				{
					if (x >= this.arDays[i].left && x <= this.arDays[i].right)
					{
						this.activeDayDrop.style.left = (this.arDays[i]._left - 1) + 'px';
						this.activeDayDrop.style.width = (this.arDays[i].width -1) + 'px';

						this.oDiv.style.width = (this.arDays[i].width - 5) + 'px';
						this.oDiv.style.left = (this.arDays[i].left + 1) + 'px';
						this.oDiv.style.top = (y - 10) + 'px';

						this.oDiv.setAttribute('data-bx-day-index', i);
						break;
					}
				}
			}
			else
			{
				if (this.activeDayDrop)
					this.activeDayDrop.style.display = 'none';
			}
		},

		RegisterEvent: function(oDiv, event, tab)
		{
			if(!this.enabled)
				return;

			var bDeny = (event['~TYPE'] == 'tasks' || !this.oEC.Event.CanDo(event, 'edit'));

			if (this.oEC.Event.IsMeeting(event) && !this.oEC.Event.IsHost(event))
				bDeny = true;

			if (event.PRIVATE_EVENT && !this.oEC.Personal())
				bDeny = true;

			var _this = this;
			jsDD.registerObject(oDiv);

			oDiv.setAttribute("data-bx-title-event", true);

			oDiv.onbxdragstart = function()
			{
				if (bDeny)
				{
					_this.oDiv = null;
					document.body.style.cursor = 'default';
					_this.ShowDenyNotice(oDiv, event);
				}
				else
				{
					_this.oDiv = oDiv.cloneNode(true);
					_this.oDiv.className = 'bxec-event bxec-event-drag';
					document.body.appendChild(_this.oDiv);
					_this.oDiv.style.top = '-1000px';
					_this.oDiv.style.left = '-1000px';

					var moreEventsWin = _this.oEC.MoreEventsWin;
					if(moreEventsWin)
					{
						moreEventsWin.close();
						moreEventsWin.destroy();
						moreEventsWin = null;
					}
				}
			};

			oDiv.onbxdrag = function(x, y)
			{
				if (_this.oDiv)
				{
					_this.oDiv.style.left = (x - 20) + 'px';
					_this.oDiv.style.top = (y - 10) + 'px';

					if (tab == 'week_title')
					{
						// We move event from title to timeline (week, day mode)
						_this.CheckTimelineOverPos(x, y);
					}
				}
			};

			oDiv.onbxdragstop = function(x, y)
			{
				if (_this.oDiv)
				{
					setTimeout(function()
					{
						if (_this.oDiv && _this.oDiv.parentNode)
						{
							_this.oDiv.parentNode.removeChild(_this.oDiv);
							_this.oDiv = null;
						}
					}, 100);
				}
				_this.OnDragFinish();
			};

			oDiv.onbxdragfinish = function(destination, x, y)
			{
				_this.OnDragFinish();
				return true;
			};
		},

		RegisterTimelineEvent: function(oDiv, event, tab)
		{
			if(!this.enabled)
				return;

			var bDeny = (event['~TYPE'] == 'tasks' || !this.oEC.Event.CanDo(event, 'edit'));

			if (this.oEC.Event.IsMeeting(event) && !this.oEC.Event.IsHost(event))
				bDeny = true;

			if (event.PRIVATE_EVENT && !this.oEC.Personal())
				bDeny = true;

			var _this = this;
			jsDD.registerObject(oDiv);

			oDiv.onbxdragstart = function()
			{
				if (bDeny)
				{
					_this.oDiv = null;
					document.body.style.cursor = 'default';
					_this.ShowDenyNotice(oDiv, event);
				}
				else
				{
					_this.oDiv = oDiv.cloneNode(true);
					_this.oDiv.className = 'bxec-tl-event bxec-event-drag';
					document.body.appendChild(_this.oDiv);
					_this.oDiv.style.top = '-1000px';
					_this.oDiv.style.left = '-1000px';
				}
			};

			oDiv.onbxdrag = function(x, y)
			{
				if (!_this.oDiv)
					return;

				if (_this.timeLineEventOver)
				{
					var i, l = _this.arDays.length;
					for (i = 0; i < l; i++)
					{
						if (x >= _this.arDays[i].left && x <= _this.arDays[i].right)
						{
							_this.oDiv.style.width = (_this.arDays[i].width - 15) + 'px';
							_this.oDiv.style.left = (_this.arDays[i].left + 1) + 'px';
							_this.oDiv.style.top = (y - 10) + 'px';
							_this.oDiv.setAttribute('data-bx-day-index', i);
							break;
						}
					}
				}
			};

			oDiv.onbxdragstop = function(x, y)
			{
				_this.OnDragFinish();
				if (!_this.oDiv)
					return;

				setTimeout(function()
				{
					if (_this.oDiv && _this.oDiv.parentNode)
					{
						_this.oDiv.parentNode.removeChild(_this.oDiv);
						_this.oDiv = null;
					}
				}, 100);
			};

			oDiv.onbxdragfinish = function(destination, x, y)
			{
				_this.OnDragFinish();
			};
		},

		RegisterTimelineEventResizer: function(ddResizer, oDiv, event, tab)
		{
			if(!this.enabled)
				return;

			var bDeny = (event['~TYPE'] == 'tasks' || !this.oEC.Event.CanDo(event, 'edit'));

			ddResizer.setAttribute('data-bx-event-resizer', 'Y');

			BX.bind(ddResizer, "mousedown", function(e)
			{
				var wndSize = BX.GetWindowSize();
				e = e || window.event;

				_this.timelineResize = {
					oDiv : oDiv,
					startY: e.clientY + wndSize.scrollTop,
					height: parseInt(oDiv.offsetHeight)
				};
			});

			var _this = this;
			jsDD.registerObject(ddResizer);

			ddResizer.onbxdragstart = function()
			{
				if (bDeny)
				{
					_this.oDiv = null;
					document.body.style.cursor = 'default';
					_this.ShowDenyNotice(ddResizer, event);
					return;
				}

				document.body.style.cursor = 's-resize';
				_this.oDiv = oDiv;
				BX.removeClass(_this.oDiv, 'bxec-tl-ev-hlt');
			};

			ddResizer.onbxdrag = function(x, y)
			{
				if (_this.oDiv && _this.timeLineEventOver)
				{
					var height = (_this.timelineResize.height + y - _this.timelineResize.startY + 5);
					if (height <= 0)
						height = 5;

					_this.timelineResize.oDiv.style.height = height + 'px';
				}
			};

			ddResizer.onbxdragstop = function(x, y)
			{
				_this.OnDragFinish();
				if (!_this.oDiv)
					return;
			};

			ddResizer.onbxdragfinish = function(destination, x, y)
			{
				_this.OnDragFinish();
			};
		},

		ResizeEventTimeline: function(event, length)
		{
			var
				attendees = [],
				_this = this;
			event.DT_LENGTH = Math.max(parseInt(event.DT_LENGTH, 10) + length, 0);
			event.DT_LENGTH = Math.round(event.DT_LENGTH / 600) * 600; // Round to 10 min
			event.dateTo.setTime(event.dateFrom.getTime() + event.DT_LENGTH * 1000);
			event.DATE_TO = this.oEC.FormatDateTime(event.dateTo);

			if (this.oEC.Event.IsMeeting(event))
			{
				event['~ATTENDEES'].forEach(function(element){attendees.push(element['USER_ID']);});
			}

			var setTimezone = event.TZ_FROM != this.oEC.arConfig.userTimezoneName || event.TZ_TO != this.oEC.arConfig.userTimezoneName;

			this.oEC.Request({
				postData: this.oEC.GetReqData('move_event_to_date',
					{
						id: event.ID,
						current_date_from: event.DATE_FROM,
						recursive: this.oEC.Event.IsRecursive(event) ? 'Y' : 'N',
						is_meeting: this.oEC.Event.IsMeeting(event) ? 'Y' : 'N',
						attendees: attendees,
						date_from: event.DATE_FROM,
						date_to: event.DATE_TO,
						section: event.SECT_ID,
						skip_time: event.DT_SKIP_TIME,
						timezone: setTimezone ? this.oEC.arConfig.userTimezoneName : event.TZ_FROM, //timezone
						set_timezone: setTimezone ? 'Y' : 'N'
					}
				),
				errorText: EC_MESS.EventSaveError,
				handler: function(oRes)
				{
					if (oRes.reload || _this.oEC.Event.IsRecursive(event))
						_this.oEC.Event.ReloadAll(false);
					return true;
				}
			});

			this.oEC.Event.PreHandle(event);
			this.oEC.Event.Display();
		},

		MoveEventToNewDate: function(event, newDate, mode, DT_LENGTH)
		{
			var
				attendees = [],
				_this = this,
				dayLen = 86400000,
				newDateTo;

			if (mode == 'day' && event.DT_SKIP_TIME == 'N')
			{
				newDate.setHours(event.dateFrom.getHours() || 0);
				newDate.setMinutes(event.dateFrom.getMinutes() || 0);
			}

			if (DT_LENGTH)
			{
				newDateTo = new Date(newDate.getTime() + DT_LENGTH);
			}
			else if (event.DT_SKIP_TIME == 'N')
			{
				newDateTo = new Date(newDate.getTime() + event.DT_LENGTH * 1000);
			}
			else if (event.DT_SKIP_TIME == 'Y')
			{
				newDateTo = new Date(newDate.getTime() + event.DT_LENGTH * 1000 - dayLen);
			}

			var currentDateFrom = event.DATE_FROM;
			event.DATE_FROM = event.DT_SKIP_TIME == 'Y' ? this.oEC.FormatDate(newDate) : this.oEC.FormatDateTime(newDate);
			event.DATE_TO = event.DT_SKIP_TIME == 'Y' ? this.oEC.FormatDate(newDateTo) : this.oEC.FormatDateTime(newDateTo);

			if (this.oEC.Event.IsMeeting(event))
			{
				event['~ATTENDEES'].forEach(function(element){attendees.push(element['USER_ID']);});
			}

			var setTimezone = event.TZ_FROM != this.oEC.arConfig.userTimezoneName || event.TZ_TO != this.oEC.arConfig.userTimezoneName;

			this.oEC.Request({
				postData: this.oEC.GetReqData('move_event_to_date',
					{
						id: event.ID,
						current_date_from: currentDateFrom,
						recursive: this.oEC.Event.IsRecursive(event) ? 'Y' : 'N',
						is_meeting: this.oEC.Event.IsMeeting(event) ? 'Y' : 'N',
						attendees: attendees,
						date_from: event.DATE_FROM,
						date_to: event.DATE_TO,
						section: event.SECT_ID,
						skip_time: event.DT_SKIP_TIME,
						timezone: setTimezone ? this.oEC.arConfig.userTimezoneName : event.TZ_FROM, //timezone
						set_timezone: setTimezone ? 'Y' : 'N'
					}
				),
				errorText: EC_MESS.EventSaveError,
				handler: function(oRes)
				{
					if (oRes.reload || _this.oEC.Event.IsRecursive(event))
						_this.oEC.Event.ReloadAll(false);

					if (_this.oEC.Event.IsMeeting(event) && oRes.busy_warning)
					{
						alert(EC_MESS.EC_BUSY_ALERT);
					}

					return true;
				}
			});

			if (event.DT_SKIP_TIME == 'N')
			{
				event['~USER_OFFSET_FROM'] = 0;
				event['~USER_OFFSET_TO'] = 0;
				event.TZ_FROM = event.TZ_TO = this.oEC.arConfig.userTimezoneName;
			}

			if (DT_LENGTH != undefined)
				event.DT_LENGTH = parseInt(DT_LENGTH, 10) / 1000;

			this.oEC.Event.PreHandle(event);
			this.oEC.Event.Display();
		},

		ShowDenyNotice: function(oDiv, event)
		{
			if (!this.pNotice)
				this.pNotice = document.body.appendChild(BX.create("DIV", {props: {className: "bxec-event-drag-deny-notice"}}));

			if (this.bNoticeShown)
				this.HideDenyNotice();

			if (event['~TYPE'] == 'tasks')
				this.pNotice.innerHTML = EC_MESS.ddDenyTask;
			else
				this.pNotice.innerHTML = EC_MESS.ddDenyEvent;

			var pos = BX.align(oDiv, 250, 50, 'top');
			this.pNotice.style.left = pos.left + 'px';
			this.pNotice.style.top = pos.top + 'px';
			this.pNotice.style.display = "block";
			this.bNoticeShown = true;

			BX.bind(document, "mouseup", BX.proxy(this.HideDenyNotice, this));
		},

		HideDenyNotice: function()
		{
			if (this.bNoticeShown)
			{
				this.bNoticeShown = false;
				if (this.pNotice)
					this.pNotice.style.display = "none";

				BX.unbind(document, "mouseup", BX.proxy(this.HideDenyNotice, this));
			}
		},

		OnDragFinish: function()
		{
		},

		IsDragDropNow: function()
		{
			return jsDD.bStarted;
		}
	};
})(window);



