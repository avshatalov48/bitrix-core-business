;(function(window) {
	function SyncSlider(params)
	{
		this.calendar = params.calendar;
		this.id = this.calendar.id + '_sync';
		this.zIndex = params.zIndex || 1000;

		this.sliderId = "calendar:sync-slider";
		this.SLIDER_WIDTH = 500;
		this.SLIDER_DURATION = 80;

		this.DOM = {button: params.button};
		this.config = this.calendar.util.config;
		this.syncInfo = this.config.syncInfo;

		if (this.DOM.button)
		{
			BX.bind(this.DOM.button, 'click', BX.proxy(this.show, this));
		}
	}

	SyncSlider.prototype = {
		show: function ()
		{
			this.init();
			BX.SidePanel.Instance.open(this.sliderId, {
				contentCallback: BX.delegate(this.create, this),
				width: this.SLIDER_WIDTH,
				animationDuration: this.SLIDER_DURATION
			});

			BX.addCustomEvent("SidePanel.Slider:onClose", BX.proxy(this.hide, this));
			BX.addCustomEvent("SidePanel.Slider:onCloseComplete", BX.proxy(this.destroy, this));
			this.calendar.disableKeyHandler();
		},

		close: function ()
		{
			BX.SidePanel.Instance.close();
		},

		hide: function (event)
		{
			if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId)
			{
				if (this.denyClose)
				{
					event.denyAction();
				}
				else
				{
					BX.removeCustomEvent("SidePanel.Slider:onClose", BX.proxy(this.hide, this));
				}
			}
		},

		destroy: function (event)
		{
			if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId)
			{
				BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", BX.proxy(this.destroy, this));
				BX.SidePanel.Instance.destroy(this.sliderId);
				this.calendar.enableKeyHandler();
			}
		},

		init: function ()
		{
			this.syncList = [
				{
					id: 'google',
					label: BX.message('EC_CAL_SYNC_GOOGLE'),
					active: !!this.syncInfo.google.active,
					connected: !!this.syncInfo.google.connected,
					syncDate: this.syncInfo.google.syncDate,
					connectHandler: BX.delegate(function(e)
					{
						BX.util.popup(this.config.googleCalDavStatus.authLink, 500, 600);
						return BX.PreventDefault(e || window.event);
					},this),
					disconnectHandler:BX.delegate(function(e)
					{
						this.disconnectGoogle(e);
						return BX.PreventDefault(e || window.event);
					}, this)
				},
				{
					id: 'macosx',
					label: BX.message('EC_CAL_SYNC_MAC'),
					active: !!this.syncInfo.macosx.active,
					connected: !!this.syncInfo.macosx.connected,
					syncDate: this.syncInfo.macosx.syncDate,
					connectHandler: BX.delegate(function(e)
					{
						this.connectMacOSX();
						return BX.PreventDefault(e || window.event);
					}, this),
					disconnectHandler:BX.delegate(function(e)
					{
						this.disconnectMacOSX();
						return BX.PreventDefault(e || window.event);
					}, this)
				},
				{
					id: 'iphone',
					label: BX.message('EC_CAL_SYNC_IPHONE'),
					active: !!this.syncInfo.iphone.active,
					connected: !!this.syncInfo.iphone.connected,
					syncDate: this.syncInfo.iphone.syncDate,
					connectHandler: BX.delegate(function(e)
					{
						this.connectIphone();
						return BX.PreventDefault(e || window.event);
					}, this),
					disconnectHandler:BX.delegate(function(e)
					{
						this.disconnectIphone();
						return BX.PreventDefault(e || window.event);
					}, this)
				},
				{
					id: 'android',
					label: BX.message('EC_CAL_SYNC_ANDROID'),
					active: !!this.syncInfo.android.active,
					connected: !!this.syncInfo.android.connected,
					syncDate: this.syncInfo.android.syncDate,
					connectHandler: BX.delegate(function(e)
					{
						this.connectAndroid();
						return BX.PreventDefault(e || window.event);
					}, this),
					disconnectHandler:BX.delegate(function(e)
					{
						this.disconnectAndroid();
						return BX.PreventDefault(e || window.event);
					}, this)
				},
				{
					id: 'outlook',
					label: BX.message('EC_CAL_SYNC_OUTLOOK'),
					active: !!this.syncInfo.outlook.active && !BX.browser.IsMac(),
					connected: !!this.syncInfo.outlook.connected,
					syncDate: this.syncInfo.outlook.syncDate,
					connectHandler: BX.delegate(function(e)
					{
						this.connectOutlook();
						return BX.PreventDefault(e || window.event);
					}, this),
					disconnectHandler:BX.delegate(function(e)
					{
						this.disconnectOutlook();
						return BX.PreventDefault(e || window.event);
					}, this)
				},
				{
					id: 'office365',
					label: BX.message('EC_CAL_SYNC_OFFICE_365'),
					active: !!this.syncInfo.office365.active,
					connected: !!this.syncInfo.office365.connected,
					syncDate: this.syncInfo.office365.syncDate
				},
				{
					id: 'exchange',
					label: BX.message('EC_CAL_SYNC_EXCHANGE'),
					active: !!this.syncInfo.exchange.active,
					connected: !!this.syncInfo.exchange.connected,
					syncDate: this.syncInfo.exchange.syncDate,
					connectHandler:function(e)
					{
						// TODO: slider with information
						//var sync = this.getSyncItem('exchange');
						//if (sync && sync.pConnectLink)
						//{
						//	this.showInfoPopup(sync.pConnectLink, BX.message('EC_CAL_CONNECT_EXCHANGE'));
						//}
					},
					disconnectHandler:function(e)
					{
						// TODO: slider with information
						//var sync = this.getSyncItem('exchange');
						//if (sync && sync.pDisconnectLink)
						//{
						//	this.showInfoPopup(sync.pDisconnectLink, BX.message('EC_CAL_DISCONNECT_EXCHANGE'));
						//}
					},
					refreshHandler: BX.delegate(function(e)
					{
						this.calendar.request({
							type: 'post',
							data: {
								action: 'exchange_sync'
							},
							handler: BX.delegate(function(response)
							{
								if (response.result === false)
									alert(BX.message('EC_BAN_EXCH_NO_SYNC'));
								else
									BX.reload();
							}, this)
						});
						return BX.PreventDefault(e || window.event);
					}, this)
				}
			];

			this.syncList.forEach(function(sync){

				if (sync.active && sync.connected && sync.syncDate)
				{
					sync.syncDate = BX.parseDate(sync.syncDate);
				}
				sync.DOM = {};
			});

			this.syncList.sort(function(a, b)
			{
				if (a.active && a.connected
					&& b.active && b.connected
					&& a.syncDate && b.syncDate
				)
				{
					return b.syncDate.getTime() - a.syncDate.getTime();
				}
				if (a.active && a.connected)
					return -1;
				if (b.active && b.connected)
					return 1;

				return 0;
			});
		},

		create: function ()
		{
			this.DOM.wrap = BX.create('DIV', {props: {className: 'calendar-slider-calendar-wrap calendar-custom-scroll'}});
			this.DOM.header = this.DOM.wrap.appendChild(BX.create('DIV', {
				props: {className: 'calendar-slider-header'},
				html: '<div class="calendar-head-area"><div class="calendar-head-area-inner"><div class="calendar-head-area-title">' +
				'<span class="calendar-head-area-name">' + BX.message('EC_CAL_SYNC_TITLE') + '</span>' +
				'</div></div></div>'
			}));
			this.DOM.sliderWorkarea = this.DOM.wrap.appendChild(BX.create('DIV', {props: {className: 'calendar-slider-workarea'}, style: {minWidth: 'auto'}}));
			this.DOM.tableWrap = this.DOM.sliderWorkarea.appendChild(BX.create('DIV', {props: {className: 'calendar-slider-content calendar-slider-sync-content'}}));

			this.DOM.table = this.DOM.tableWrap.appendChild(BX.create("TABLE", {props: {className: 'calendar-sync'}}));

			var
				iconCell, nameNode, statusWrap, statusWrapInner,  statusInfoNode, statusDateNode,
				actionWrap,
				i, row, sync;

			for (i = 0; i < this.syncList.length; i++)
			{
				sync = this.syncList[i];
				sync.DOM = {};

				if (sync.active)
				{
					row = BX.adjust(this.DOM.table.insertRow(-1), {props: {className: 'calendar-sync-column'}});

					iconCell = BX.adjust(row.insertCell(-1), {props: {className: 'calendar-sync-cell calendar-sync-cell-icon'}}).appendChild(BX.create("DIV", {props: {className: 'calendar-sync-platform-icon calendar-sync-platform-icon-' + this.syncList[i].id}}));

					nameNode = BX.adjust(row.insertCell(-1), {props: {className: 'calendar-sync-cell'}}).appendChild(BX.create("DIV", {
						props: {className: 'calendar-sync-platform-name calendar-sync-cell'},
						text: this.syncList[i].label
					}));

					statusWrap = BX.adjust(row.insertCell(-1), {props: {className: 'calendar-sync-cell'}});

					if (sync.connected)
					{
						statusWrapInner = statusWrap.appendChild(BX.create("DIV", {props: {className: 'calendar-sync-info'}})).appendChild(BX.create("DIV", {props: {className: 'calendar-sync-info-inner'}}));

						statusInfoNode = statusWrapInner.appendChild(BX.create("DIV", {props: {className: 'calendar-sync-info-status'}, text: BX.message('EC_CAL_SYNC_OK')}));

						if (sync.syncDate)
						{
							var textDate = this.calendar.util.formatDateUsable(sync.syncDate);
							if ((new Date().getTime() - sync.syncDate.getTime()) / this.calendar.util.dayLength < 3)
							{
								textDate += ' ' + this.calendar.util.formatTime(sync.syncDate.getHours(), sync.syncDate.getMinutes());
							}
							statusDateNode = statusWrapInner.appendChild(BX.create("DIV", {props: {className: 'calendar-sync-info-date'}, html: textDate}));
						}

						if (sync.id == 'exchange' && this.calendar.util.config.bExchange)
						{
							nameNode.style.cursor = 'pointer';
							BX.bind(nameNode, 'click', BX.proxy(this.syncExchange, this));
						}
					}

					actionWrap = BX.adjust(row.insertCell(-1), {props: {className: 'calendar-sync-cell calendar-sync-cell-link'}});
					if (!sync.connected && sync.connectHandler)
					{
						sync.DOM.connectLink = actionWrap.appendChild(BX.create("SPAN", {
							props: {className: 'calendar-sync-link'},
							events: {click: sync.connectHandler},
							text: BX.message('EC_CAL_SYNC_CONNECT')
						}));
					}
					else if (sync.connected)
					{
						if (sync.disconnectHandler)
						{
							sync.DOM.disconnectLink = actionWrap.appendChild(BX.create("SPAN", {
								props: {className: 'calendar-sync-link'},
								events: {click: sync.disconnectHandler},
								text: BX.message('EC_CAL_SYNC_DISCONNECT')
							}));
						}

						if (sync.refreshHandler)
						{
							sync.DOM.refreshLink = actionWrap.appendChild(BX.create("SPAN", {
								props: {className: 'calendar-sync-link'},
								events: {click: sync.refreshHandler},
								text: BX.message('EC_CAL_SYNC_REFRESH')
							}));
						}

						if (sync.disconnectHandler && sync.refreshHandler)
						{
							BX.addClass(actionWrap, 'calendar-sync-two-links');
						}
					}

					sync.DOM.row = row;
				}
			}

			return this.DOM.wrap;
		},

		syncSectionWithOutlook: function(section)
		{
			if(section && section.data.OUTLOOK_JS)
				try{eval(section.data.OUTLOOK_JS);}catch(e){}
		},

		disconnectGoogle: function(e)
		{
			if (confirm(BX.message('EC_CAL_REMOVE_GOOGLE_SYNC_CONFIRM')))
			{
				var i, con = null;
				for (i = 0; i < this.calendar.calDavConnections.length; i++)
				{
					con = this.calendar.calDavConnections[i];
					if (con.account_type == "caldav_google_oauth")
					{
						break;
					}
				}

				if (con && con.id)
				{
					this.calendar.request({
						type: 'post',
						data: {
							action: 'disconnect_google',
							connectionId: con.id
						},
						handler: BX.delegate(function(response)
						{
							BX.reload();
						}, this)
					});
				}
			}
			return BX.PreventDefault(e || window.event);
		},

		connectOutlook: function()
		{
			var
				sectionList = this.calendar.sectionController.getSectionList(),
				_this = this,
				sections = [],
				menuItems, i, icon;

			for (i = 0; i < sectionList.length; i++)
			{
				if (sectionList[i].belongsToView() && sectionList[i].data.OUTLOOK_JS)
				{
					sections.push(sectionList[i]);
				}
			}

			// Only one section
			if (sections.length == 1)
			{
				this.syncSectionWithOutlook(sections[0]);
			}
			else
			{
				// Show popup
				var sync = this.getSyncItem('outlook');
				if(sync)
				{
					menuItems = [];
					for (i = 0; i < sections.length; i++)
					{
						menuItems.push({
							id: 'bx-calendar-outlook-' + sections[i].id,
							text: BX.util.htmlspecialchars(sections[i].name),
							color: sections[i].color,
							className: 'calendar-add-popup-section-menu-item',
							onclick: (function (value)
							{
								return function ()
								{
									_this.syncSectionWithOutlook(_this.calendar.sectionController.getSection(value));
									_this.sectionMenu.close();
								}
							})(sections[i].id)
						});
					}

					this.sectionMenu = BX.PopupMenu.create(
						"outlookSectionMenu" + this.calendar.id,
						sync.DOM.connectLink,
						menuItems,
						{
							closeByEsc : true,
							autoHide : true,
							zIndex: 3200,
							offsetTop: 0,
							offsetLeft: 0,
							angle: true
						}
					);
					this.sectionMenu.show();

					// Paint round icons for section menu
					for (i = 0; i < this.sectionMenu.menuItems.length; i++)
					{
						if (this.sectionMenu.menuItems[i].layout.item)
						{
							icon = this.sectionMenu.menuItems[i].layout.item.querySelector('.menu-popup-item-icon');
							if (icon)
							{
								icon.style.backgroundColor = this.sectionMenu.menuItems[i].color;
							}
						}
					}
				}
			}
		},

		connectIphone: function()
		{
			this.showSyncHelp('iphone');
		},

		connectMacOSX: function()
		{
			this.showSyncHelp('macosx');
		},

		connectAndroid: function()
		{
			this.showSyncHelp('android');
		},

		disconnectIphone: function()
		{
			//1. Send request to clear sync information
			this.clearSyncInformation('iphone');

			//2. Show popup with info how to disconnect it
			var sync = this.getSyncItem('iphone');
			if (sync && sync.pDisconnectLink)
			{
				var _this = this;
				this.showInfoPopup(sync.pDisconnectLink, BX.message('EC_CAL_DISCONNECT_IPHONE'), function ()
				{
					_this.syncInfo.iphone.connected = false;
					_this.syncInfo.iphone.syncDate = false;
					//_this.Display();
				});
			}
		},

		disconnectMacOSX: function()
		{
			//1. Send request to clear sync information
			this.clearSyncInformation('mac');

			//2. Show popup with info how to disconnect it
			var sync = this.getSyncItem('macosx');
			if (sync && sync.pDisconnectLink)
			{
				var _this = this;
				this.showInfoPopup(sync.pDisconnectLink, BX.message('EC_CAL_DISCONNECT_MAC'), function ()
				{
					_this.syncInfo.macosx.connected = false;
					_this.syncInfo.macosx.syncDate = false;
					//_this.Display();
				});
			}
		},

		disconnectAndroid: function()
		{
			//1. Send request to clear sync information
			this.clearSyncInformation('android');

			//2. Show popup with info how to disconnect it
			var sync = this.getSyncItem('android');
			if (sync && sync.pDisconnectLink)
			{
				var _this = this;
				this.showInfoPopup(sync.pDisconnectLink, BX.message('EC_CAL_DISCONNECT_ANDROID'), function ()
				{
					_this.syncInfo.android.connected = false;
					_this.syncInfo.android.syncDate = false;
					//_this.Display();
				});
			}
		},

		disconnectOutlook: function()
		{
			//1. Send request to clear sync information
			this.clearSyncInformation('outlook');

			//2. Show popup with info how to disconnect it
			var sync = this.getSyncItem('outlook');
			if (sync && sync.pDisconnectLink)
			{
				var _this = this;
				this.showInfoPopup(sync.pDisconnectLink, BX.message('EC_CAL_DISCONNECT_OUTLOOK'), function ()
				{
					_this.syncInfo.outlook.connected = false;
					_this.syncInfo.outlook.syncDate = false;
					//_this.Display();
				});
			}
		},

		showInfoPopup: function(item, html, onCloseHandler)
		{
			var popup = BX.PopupWindowManager.create(this.id + "-disconnect-popup", item,
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

		buildSyncItem: function(sync, parentCont)
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
						props: {className: 'bxec-sect-access-connect-link'}, text: BX.message('EC_CAL_SYNC_CONNECT')
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
					BX.adjust(row.insertCell(-1), {props : {className: 'bxec-sect-access-status'}, html: BX.message('EC_CAL_SYNC_OK')});
					var cell = BX.adjust(row.insertCell(-1), {style: {textAlign: 'right'}});
					if (sync.syncDate)
					{
						sync.pSyncDate = cell.appendChild(BX.create("DIV", {props: {className: 'bxec-sect-access-status-time'}}));
						sync.pSyncDate.innerHTML = sync.syncDate;
					}
					if (sync.disconnectHandler)
					{
						sync.pDisconnectLink = cell.appendChild(BX.create("SPAN", {props: {className: 'bxec-sect-access-disconnect-link'}, html: BX.message('EC_CAL_SYNC_DISCONNECT')}));
						BX.bind(sync.pDisconnectLink, 'click', sync.disconnectHandler);
					}

					if (sync.id == 'exchange' && this.calendar.util.config.bExchange)
					{

						sync.pTextWrap.style.cursor = 'pointer';

						BX.bind(sync.pTextWrap, 'click', BX.proxy(this.syncExchange, this));
					}
				}
			}
		},

		getSyncItem: function(id)
		{
			var i;
			for (i = 0; i < this.syncList.length; i++)
			{
				if (this.syncList[i].active && this.syncList[i].id == id)
				{
					return this.syncList[i];
				}
			}
		},

		showSyncHelp: function(sync)
		{
			var arLinks = [], i;
			var syncMeta = this.syncList.filter(function (s)
			{
				return s.id == sync;
			});
			if (!syncMeta || !syncMeta[0])
				return;
			syncMeta = syncMeta[0];

			if (!syncMeta.DOM.helpCell)
			{
				syncMeta.DOM.helpRow = BX.adjust(this.DOM.table.insertRow(syncMeta.DOM.row.rowIndex + 1), {
					props: {className: 'calendar-sync-column calendar-sync-desc'}
				});
				syncMeta.DOM.helpCell = BX.adjust(syncMeta.DOM.helpRow.insertCell(-1), {
					attrs: {colspan: '4'},
					props: {className: 'calendar-sync-cell'}
				}).appendChild(BX.create("DIV", {props: {className: 'calendar-sync-help-wrap'}}));

				if (sync == 'iphone')
				{
					syncMeta.DOM.helpCell.innerHTML = BX.message('EC_MOBILE_HELP_IPHONE');
				}
				else if (sync == 'macosx')
				{
					syncMeta.DOM.helpCell.innerHTML = BX.message('EC_MOBILE_HELP_MAC');
				}
				else if (sync == 'android')
				{
					syncMeta.DOM.helpCell.innerHTML = BX.message('EC_MOBILE_HELP_ANDROID');
				}

				if (sync == 'iphone' || sync == 'macosx')
				{
					arLinks = arLinks.concat(BX.findChildren(syncMeta.DOM.helpCell, {tagName: 'SPAN', className: 'bxec-link'}, true));
					for (i = 0; i < arLinks.length; i++)
					{
						if (arLinks[i] && arLinks[i].nodeName)
						{
							arLinks[i].innerHTML = this.calendar.util.config.caldav_link_all;
						}
					}
				}
			}

			if (BX.hasClass(syncMeta.DOM.helpCell, 'open'))
			{
				BX.removeClass(syncMeta.DOM.helpCell, 'open');
				setTimeout(function(){
					syncMeta.DOM.helpRow.style.display = 'none';
				}, 300);
			}
			else
			{
				syncMeta.DOM.helpRow.style.display = '';
				setTimeout(function(){
					BX.addClass(syncMeta.DOM.helpCell, 'open');
				}, 0);
			}
		},

		clearSyncInformation: function(sync_type)
		{
			this.calendar.request({
				type: 'post',
				data: {
					action: 'clear_sync_info',
					sync_type: sync_type
				},
				handler: BX.delegate(function(response)
				{
					BX.reload();
				}, this)
			});
		},

		showCalDavSyncDialog: function()
		{
			if (!this.calDavSyncDialog)
			{
				var id = this.calendar.id;

				this.calDavSyncDialog = {
					DOM : {
						content: BX.create('DIV', {
							props: {className: 'calendar-caldav-popup-wrap'},
							html: '<div class="bxec-dav-list" id="' + id + '_caldav_list"></div>'
						})
					},
					popup: false
				};

				var _this = this;
				this.calDavSyncDialog.popup = new BX.PopupWindow("BXCExternalDialog" + this.id, null, {
					overlay: {opacity: 10},
					autoHide: false,
					closeByEsc : true,
					zIndex: 4000,
					width: 600,
					offsetLeft: 0,
					offsetTop: 0,
					draggable: true,
					titleBar: BX.message('EC_CALDAV_TITLE'),
					closeIcon: {right : "12px", top : "10px"},
					className: "bxc-popup-window bxc-popup-window-white",
					contentColor : "white",
					contentNoPaddings : true,
					buttons: [
						new BX.PopupWindowButton({
							text: BX.message('EC_ADD_CALDAV'),
							events: {click : function()
							{
								_this.connections.push({name: BX.message('EC_NEW_CONNECTION_NAME'), link: '', user_name: ''});
								_this.displayConnection(_this.connections[_this.connections.length - 1], _this.connections.length - 1);
							}}
						}),
						new BX.PopupWindowButton({
							text: BX.message('EC_SEC_SLIDER_SAVE'),
							className: "popup-window-button-accept",
							events: {click : function(){
								if (_this.calDavSyncDialog.bLockClosing)
									return alert(BX.message('EC_CAL_DAV_CON_WAIT'));

								_this.calDavSyncDialog.bLockClosing = true;

								_this.saveCalDavConnections(
									function(res)
									{
										_this.calDavSyncDialog.bLockClosing = false;
										if (res)
										{
											_this.calDavSyncDialog.popup.close();
											BX.reload();
										}
									}
								);
							}}
						}),
						new BX.PopupWindowButtonLink({
							text: BX.message('EC_SEC_SLIDER_CLOSE'),
							className: "popup-window-button-link-cancel",
							events: {click : function(){_this.calDavSyncDialog.popup.close();}}
						})
					],
					content: this.calDavSyncDialog.DOM.content
				});

				BX.addCustomEvent(this.calDavSyncDialog.popup, 'onPopupClose', BX.proxy(this.closeCalDavSyncDialog, this));
			}

			this.calDavSyncDialog.popup.show();

			this.calDavSyncDialog.DOM.list = BX(id + '_caldav_list');

			this.calendar.disableKeyHandler();
			this.calDavSyncDialog.curEditedConInd = false;

			BX.cleanNode(this.calDavSyncDialog.DOM.list);

			this.connections = BX.clone(this.calendar.util.getCalDavConnections());
			this.connections.forEach(this.displayConnection, this);

			if (this.connections.length == 0) // No connections - open form to add new connection
			{
				this.connections.push({name: BX.message('EC_NEW_CONNECTION_NAME'), link: '', user_name: ''});
				this.displayConnection(this.connections[this.connections.length - 1], this.connections.length - 1);
			}
		},

		displayConnection: function(con, ind)
		{
			var
				_this = this,
				id = this.calendar.id, editWrap,
				conDiv = this.calDavSyncDialog.DOM.list.appendChild(BX.create("DIV", {props: {id: id + '_dav_con_' + ind, className: 'calendar-caldav-item'}})),
				title = conDiv.appendChild(BX.create("DIV", {props: {className: 'calendar-caldav-item-title'}})),
				status = title.appendChild(BX.create("IMG", {props: {src: "/bitrix/images/1.gif", className: 'bxec-dav-item-status'}})),
				text = title.appendChild(BX.create("SPAN", {text: con.name})),
				count = title.appendChild(BX.create("SPAN", {text: ''})),
				del = title.appendChild(BX.create("A", {props: {href: 'javascript: void(0);', className: 'bxec-dav-del'}, text: BX.message('EC_CALDAV_DEL')}));

			if (con.id > 0 && (con.account_type == 'google_api_oauth' || con.account_type == 'caldav_google_oauth'))
			{
				editWrap = conDiv.appendChild(BX.create("DIV", {
					props: {className: 'bxec-dav-new-form'},
					html: '<div class="calendar-caldav-field-container-wrap">' +
							'<div  class="calendar-caldav-sections-outer-wrap" id="' + id + '_dav_sections_cont_outer' + ind + '">' +
								'<div class="calendar-caldav-sections-title">' + BX.message('EC_ADD_CALDAV_SECTIONS')+ ' : </div>' +
								'<div class="calendar-caldav-sections-wrap" id="' + id + '_dav_sections_cont' + ind + '"></div>' +
							'</div>' +
					'</div>'
				}));
			}
			else
			{
				editWrap = conDiv.appendChild(BX.create("DIV", {
					props: {className: 'bxec-dav-new-form'},
					html: '<div  class="calendar-caldav-field-container-wrap">' +
						'<div class="calendar-field-container calendar-field-container-string"><div class="calendar-field-block"><input id="' + id + '_caldav_name' + ind + '" type="text" placeholder="' + BX.message('EC_ADD_CALDAV_NAME') + '" class="calendar-field calendar-field-string"></div></div>' +
						'<div class="calendar-field-container calendar-field-container-string"><div class="calendar-field-block"><input id="' + id + '_caldav_link' + ind + '" type="text" placeholder="' + BX.message('EC_ADD_CALDAV_LINK') + '" class="calendar-field calendar-field-string"></div></div>' +
						'<div class="calendar-field-container calendar-field-container-string"><div class="calendar-field-block"><input id="' + id + '_caldav_username' + ind + '" type="text" placeholder="' + BX.message('EC_ADD_CALDAV_USER_NAME') + '" class="calendar-field calendar-field-string"></div></div>' +
						'<div class="calendar-field-container calendar-field-container-string"><div class="calendar-field-block"><input id="' + id + '_caldav_password' + ind + '" type="password" placeholder="' + BX.message('EC_ADD_CALDAV_PASS') + '" class="calendar-field calendar-field-string"></div></div>' +
						'<div  class="calendar-caldav-sections-outer-wrap" id="' + id + '_dav_sections_cont_outer' + ind + '">' +
						'<div class="calendar-caldav-sections-title">' + BX.message('EC_ADD_CALDAV_SECTIONS')+ ' : </div>' +
						'<div class="calendar-caldav-sections-wrap" id="' + id + '_dav_sections_cont' + ind + '"></div>' +
						'</div>' +
					'</div>'
				}));
			}

			if (con.id > 0)
			{
				if (con.last_result && con.last_result.indexOf("[200]") >= 0)
				{
					status.className = 'bxec-dav-item-status bxec-dav-ok';
					status.title = BX.message('EC_CALDAV_SYNC_OK') + '. ' + BX.message('EC_CALDAV_SYNC_DATE') + ': ' + con.sync_date;
				}
				else
				{
					status.className += 'bxec-dav-item-status bxec-dav-error';
					status.title = BX.message('EC_CALDAV_SYNC_ERROR') + ': ' + con.last_result + '. '+ BX.message('EC_CALDAV_SYNC_DATE') + ': ' + con.sync_date;
				}

				var countNum = 0;
				con.sections = {};
				this.calendar.sectionController.sections.forEach(function(section)
				{
					if (section.belongsToView() && (section.isCalDav() || section.isGoogle()) && section.data.CAL_DAV_CON == con.id)
					{
						countNum++;
						var sectionWrap = BX(id + '_dav_sections_cont' + ind).appendChild(BX.create("DIV", {props: {className: 'bxec-dav-sect'}}));
						con.sections[section.id] = {
							section: section,
							checkbox: sectionWrap.appendChild(
								BX.create("SPAN", {props: {className: "bxec-dav-sect-check"}}))
								.appendChild(BX.create("INPUT", {
									props: {
										type: "checkbox",
										id: id + '_dav_sections_cont' + ind + section.id,
										checked: section.isActive()
									}
								}))
						};
						sectionWrap.appendChild(BX.create("SPAN", {props: {className: "bxc-spd-sect-label"}, html: '<label for="' + id + '_dav_sections_cont' + ind + section.id + '"><span>' + BX.util.htmlspecialchars(section.name) + '</span></label>'}));

					}
				}, this);
				count.innerHTML = " (" + countNum + ")";
				if (countNum > 0)
				{
					BX(id + '_dav_sections_cont_outer' + ind).style.display = '';
				}
				else
				{
					BX(id + '_dav_sections_cont_outer' + ind).style.display = 'none';
				}

				del.style.display = 'inline-block';
			}
			else
			{
				BX(id + '_dav_sections_cont_outer' + ind).style.display = 'none';
				del.style.display = 'none';
			}

			con.nameInput = BX(id + '_caldav_name' + ind) || false;
			con.linkInput = BX(id + '_caldav_link' + ind) || false;
			con.userInput = BX(id + '_caldav_username' + ind) || false;
			con.passInput = BX(id + '_caldav_password' + ind) || false;

			del.onclick = function(e)
			{
				if (con.id > 0 && (con.account_type == 'google_api_oauth' || con.account_type == 'caldav_google_oauth'))
				{
					_this.disconnectGoogle(e);
				}
				else
				{

				}
				return BX.PreventDefault(e);
			};
		},

		saveCalDavConnections: function(Calback)
		{
			var connections = [];
			this.connections.forEach(function(connection)
			{
				var sectId, sections = {};
				for (sectId in connection.sections)
				{
					if (connection.sections.hasOwnProperty(sectId))
					{
						sections[sectId] = connection.sections[sectId].checkbox.checked ? 'Y' : 'N';
					}
				}

				var item = {
					id: connection.id || 0,
					name: connection.nameInput ? connection.nameInput.value : connection.name,
					link: connection.linkInput ? connection.linkInput.value : connection.link,
					user_name: connection.userInput ? connection.userInput.value : connection.user_name,
					pass: connection.passInput && connection.passInput.value ? connection.passInput.value : 'bxec_not_modify_pass',
					del: connection.del ? 'Y' : 'N',
					del_calendars: 'Y',
					sections: sections
				};

				if (!connection.id && (!item.name || !item.user_name  || !item.pass))
					return;

				connections.push(item);
			}, this);

			this.calendar.request({
				type: 'post',
				data: {
					action: 'connections_edit',
					connections : connections
				},
				handler: BX.delegate(function(response)
				{
					setTimeout(function(){
						if (BX.type.isFunction(Calback))
							Calback(true);
					}, 100);

					if (response.result === false)
						alert(BX.message('EC_BAN_EXCH_NO_SYNC'));
					else
						BX.reload();
				}, this),
				onerror: BX.delegate(function(response)
				{
					setTimeout(function(){
						if (BX.type.isFunction(Calback))
							Calback(false);
					}, 100);
				}, this),
			});
			return true;
		},

		closeCalDavSyncDialog: function()
		{
			this.calendar.enableKeyHandler();
			this.calDavSyncDialog.popup.destroy();
			this.calDavSyncDialog = null;
		},

		showICalExportDialog: function(section)
		{
			var _this = this;

			if (!this.exportDialog)
			{
				var content = BX.create('DIV', {html: '<span>' + BX.message('EC_EXP_TEXT') + '</span>'});

				this.exportDialog = new BX.PopupWindow("export_dialog" + this.calendar.id, null, {
					autoHide: false,
					closeByEsc : true,
					zIndex: 4000,
					offsetLeft: 0,
					offsetTop: 0,
					width: 800,
					draggable: true,
					titleBar: BX.message('EC_JS_EXPORT_TILE'),
					closeIcon: {right : "12px", top : "10px"},
					className: "bxc-popup-window",
					buttons: [
						new BX.PopupWindowButtonLink({
							text: BX.message('EC_SEC_SLIDER_CLOSE'),
							className: "popup-window-button-link-cancel",
							events: {click : function(){_this.exportDialog.close();}}
						})
					],
					content: content
				});

				this.exportDialog.DOM = {};
			}
			this.exportDialog.show();

			// Create link
			var link = this.calendar.util.config.path;
			link += (link.indexOf('?') >= 0) ? '&' : '?';
			if (section && section.data.EXPORT.LINK)
			{
				link += 'action=export' + section.data.EXPORT.LINK;
			}


			this.exportDialog.DOM.link = content.appendChild(BX.create('DIV', {props: {className: ''}}))
				.appendChild(BX.create('A', {
					props: {
						href: link,
						target: "_blank"
					},
					html: link,
					events: {
						click: function(e){
							window.location.href = 'webcal' + link.substr(link.indexOf('://'));
							e.preventDefault();
							e.stopPropagation();
						}
					}
				}));


			BX.ajax.get(link + '&check=Y', "", function(result)
			{
				setTimeout(function()
				{
					if (!result || result.length <= 0 || result.toUpperCase().indexOf('BEGIN:VCALENDAR') == -1)
					{
						alert(BX.message('EC_EDEV_EXP_WARN'));
					}
				}, 300);
			});
		}
	};

	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.SyncSlider = SyncSlider;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.SyncSlider = SyncSlider;
		});
	}
})(window);