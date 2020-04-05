BX.namespace('BX.Report');
BX.mergeEx(BX.Report, {
	firstButtonInModalWindow: null,
	windowsWithoutManager: {},
	entityToNewShared: {},

	ajax: function (config)
	{
		config.data = config.data || {};
		config.data['SITE_ID'] = BX.message('SITE_ID');
		config.data['sessid'] = BX.bitrix_sessid();

		return BX.ajax(config);
	},
	modalWindow: function (params)
	{
		params = params || {};
		params.title = params.title || false;
		params.bindElement = params.bindElement || null;
		params.overlay = typeof params.overlay == 'undefined' ? true : params.overlay;
		params.autoHide = params.autoHide || false;
		params.closeIcon = typeof params.closeIcon == 'undefined'?
			{right: '20px', top: '10px'} : params.closeIcon;
		params.modalId = params.modalId || 'report_modal_window_' + (Math.random() * (200000 - 100) + 100);
		params.withoutContentWrap = typeof params.withoutContentWrap == 'undefined' ?
			false : params.withoutContentWrap;
		params.contentClassName = params.contentClassName || '';
		params.contentStyle = params.contentStyle || {};
		params.content = params.content || [];
		params.buttons = params.buttons || false;
		params.events = params.events || {};
		params.withoutWindowManager = !!params.withoutWindowManager || false;

		var contentDialogChildren = [];
		if (params.title) {
			contentDialogChildren.push(BX.create('div', {
				props: {
					className: 'bx-report-popup-title'
				},
				text: params.title
			}));
		}
		if (params.withoutContentWrap) {
			contentDialogChildren = contentDialogChildren.concat(params.content);
		}
		else {
			contentDialogChildren.push(BX.create('div', {
				props: {
					className: 'bx-report-popup-content ' + params.contentClassName
				},
				style: params.contentStyle,
				children: params.content
			}));
		}
		var buttons = [];
		if (params.buttons) {
			for (var i in params.buttons) {
				if (!params.buttons.hasOwnProperty(i)) {
					continue;
				}
				if (i > 0) {
					buttons.push(BX.create('SPAN', {html: '&nbsp;'}));
				}
				buttons.push(params.buttons[i]);
			}

			contentDialogChildren.push(BX.create('div', {
				props: {
					className: 'bx-report-popup-buttons'
				},
				children: buttons
			}));
		}

		var contentDialog = BX.create('div', {
			props: {
				className: 'bx-report-popup-container'
			},
			children: contentDialogChildren
		});

		params.events.onPopupShow = BX.delegate(function () {
			if (buttons.length) {
				this.firstButtonInModalWindow = buttons[0];
				BX.bind(document, 'keydown', BX.proxy(this._keyPress, this));
			}

			if(params.events.onPopupShow)
				BX.delegate(params.events.onPopupShow, BX.proxy_context);
		}, this);
		var closePopup = params.events.onPopupClose;
		params.events.onPopupClose = BX.delegate(function () {

			this.firstButtonInModalWindow = null;
			try
			{
				BX.unbind(document, 'keydown', BX.proxy(this._keypress, this));
			}
			catch (e) { }

			if(closePopup)
			{
				BX.delegate(closePopup, BX.proxy_context)();
			}

			if(params.withoutWindowManager)
			{
				delete this.windowsWithoutManager[params.modalId];
			}

			BX.proxy_context.destroy();
		}, this);

		var modalWindow;
		if(params.withoutWindowManager)
		{
			if(!!this.windowsWithoutManager[params.modalId])
			{
				return this.windowsWithoutManager[params.modalId]
			}
			modalWindow = new BX.PopupWindow(params.modalId, params.bindElement, {
				content: contentDialog,
				closeByEsc: true,
				closeIcon: params.closeIcon,
				autoHide: params.autoHide,
				overlay: params.overlay,
				events: params.events,
				buttons: [],
				zIndex : isNaN(params['zIndex']) ? 0 : params.zIndex
			});
			this.windowsWithoutManager[params.modalId] = modalWindow;
		}
		else
		{
			modalWindow = BX.PopupWindowManager.create(params.modalId, params.bindElement, {
				content: contentDialog,
				closeByEsc: true,
				closeIcon: params.closeIcon,
				autoHide: params.autoHide,
				overlay: params.overlay,
				events: params.events,
				buttons: [],
				zIndex : isNaN(params['zIndex']) ? 0 : params.zIndex
			});

		}

		modalWindow.show();

		return modalWindow;
	},
	modalWindowLoader: function (queryUrl, params, bindElement)
	{
		bindElement = bindElement || null;
		params = params || {};
		var modalId = params.id;
		var expectResponseType = params.responseType || 'html';
		var afterSuccessLoad = params.afterSuccessLoad || null;
		var onPopupClose = params.onPopupClose || null;
		var postData = params.postData || {};

		var popup = BX.PopupWindowManager.create(
			'bx-report-' + modalId,
			bindElement,
			{
				closeIcon: true,
				offsetTop: 5,
				autoHide: true,
				lightShadow: false,
				overlay: true,
				content: BX.create('div', {
					children: [
						BX.create('div', {
								style: {
									display: 'table',
									width: '30px',
									height: '30px'
								},
								children: [
									BX.create('div', {
										style: {
											display: 'table-cell',
											verticalAlign: 'middle',
											textAlign: 'center'
										},
										children: [
											BX.create('div', {
												props: {
													className: 'bx-report-wrap-loading-modal'
												}
											}),
											BX.create('span', {
												text: ''
											})
										]
									})
								]
							}
						)
					]
				}),
				closeByEsc: true,
				events: {
					onPopupClose: function ()
					{
						if (onPopupClose) {
							BX.delegate(onPopupClose, this)();
						}

						this.destroy();
					}
				}
			}
		);
		popup.show();

		postData['sessid'] = BX.bitrix_sessid();
		postData['SITE_ID'] = BX.message('SITE_ID');

		BX.ajax({
			url: queryUrl,
			method: 'POST',
			dataType: expectResponseType,
			data: postData,
			onsuccess: BX.delegate(function (data)
			{

				if (expectResponseType == 'html') {
					popup.setContent(BX.create('DIV', {html: data}));
					popup.adjustPosition();
				}
				else if(expectResponseType == 'json')
				{
					data = data || {};
				}

				afterSuccessLoad && afterSuccessLoad(data, popup);
			}, this),
			onfailure: function (data)
			{
			}
		});
	},
	getRightLabelByName: function(name){
		switch(name.toLowerCase())
		{
			case 'access_read':
				return BX.message('REPORT_JS_SHARING_RIGHT_READ');
			case 'access_edit':
				return BX.message('REPORT_JS_SHARING_RIGHT_EDIT');
			default:
				return 'error';
		}
	},
	appendNewShared: function (params) {

		var readOnly = params.readOnly;
		var destFormName = params.destFormName;

		var entityId = params.item.id;
		var entityName = params.item.name;
		var entityAvatar = params.item.avatar;
		var type = params.type;
		var right = params.right || 'access_read';

		this.entityToNewShared[entityId] = {
			item: params.item,
			type: params.type,
			right: right
		};

		BX('bx-report-popup-shared-people-list').appendChild(
			BX.create('tr', {
				attrs: {
					'data-dest-id': entityId
				},
				children: [
					BX.create('td', {
						props: {
							className: 'bx-report-popup-shared-people-list-col1'
						},
						children: [
							BX.create('a', {
								props: {
									className: 'bx-report-filepage-used-people-link'
								},
								children: [
									BX.create('span', {
										props: {
											className: 'bx-report-filepage-used-people-avatar '+
											(type != 'users'? ' group' : '')
										},
										style: {
											backgroundImage: entityAvatar?'url('+entityAvatar+')':null
										}
									}),
									entityName
								]
							})
						]
					}),
					BX.create('td', {
						props: {
							className: 'bx-report-popup-shared-people-list-col2'
						},
						children: [
							BX.create('a', {
								props: {
									className: 'bx-report-filepage-used-people-permission'
								},
								text: this.getRightLabelByName(right),
								events: {}
							})
						]
					}),
					BX.create('td', {
						props: {
							className: 'bx-report-popup-shared-people-list-col3 tar'
						},
						children: [
							(!readOnly? BX.create('span', {
								props: {
									className: 'bx-report-filepage-used-people-del'
								},
								events: {
									click: BX.delegate(function(e){
										BX.SocNetLogDestination.deleteItem(entityId, type, destFormName);
										var src = e.target || e.srcElement;
										BX.remove(src.parentNode.parentNode);
									}, this)
								}
							}) : null)
						]
					})
				]
			})
		);
	},
	removeElement: function (elem)
	{
		return elem.parentNode ? elem.parentNode.removeChild(elem) : elem;
	},
	addToLinkParam: function (link, name, value)
	{
		if (!link.length) {
			return '?' + name + '=' + value;
		}
		link = BX.util.remove_url_param(link, name);
		if (link.indexOf('?') != -1) {
			return link + '&' + name + '=' + value;
		}
		return link + '?' + name + '=' + value;
	},
	getFirstErrorFromResponse: function(reponse)
	{
		reponse = reponse || {};
		if(!reponse.errors)
			return '';

		return reponse.errors.shift().message;
	},
	showModalWithStatusAction: function (response, action)
	{
		response = response || {};
		if (!response.message) {
			if (response.status == 'success') {
				response.message = BX.message('REPORT_JS_STATUS_ACTION_SUCCESS');
			}
			else {
				response.message = BX.message('REPORT_JS_STATUS_ACTION_ERROR') + '. ' +
					this.getFirstErrorFromResponse(response);
			}
		}
		var messageBox = BX.create('div', {
			props: {
				className: 'bx-report-alert'
			},
			children: [
				BX.create('span', {
					props: {
						className: 'bx-report-aligner'
					}
				}),
				BX.create('span', {
					props: {
						className: 'bx-report-alert-text'
					},
					text: response.message
				}),
				BX.create('div', {
					props: {
						className: 'bx-report-alert-footer'
					}
				})
			]
		});

		var currentPopup = BX.PopupWindowManager.getCurrentPopup();
		if(currentPopup)
		{
			currentPopup.destroy();
		}

		var idTimeout = setTimeout(function ()
		{
			var w = BX.PopupWindowManager.getCurrentPopup();
			if (!w || w.uniquePopupId != 'bx-report-status-action') {
				return;
			}
			w.close();
			w.destroy();
		}, 2500);
		var popupConfirm = BX.PopupWindowManager.create('bx-report-status-action', null, {
			content: messageBox,
			onPopupClose: function ()
			{
				this.destroy();
				clearTimeout(idTimeout);
			},
			autoHide: true,
			zIndex: 2000,
			className: 'bx-report-alert-popup'
		});
		popupConfirm.show();

		BX('bx-report-status-action').onmouseover = function (e)
		{
			clearTimeout(idTimeout);
		};

		BX('bx-report-status-action').onmouseout = function (e)
		{
			idTimeout = setTimeout(function ()
			{
				var w = BX.PopupWindowManager.getCurrentPopup();
				if (!w || w.uniquePopupId != 'bx-report-status-action') {
					return;
				}
				w.close();
				w.destroy();
			}, 2500);
		};
	},
	show: function(element)
	{
		if (this.getRealDisplay(element) != 'none')
			return;

		var old = element.getAttribute('displayOld');
		element.style.display = old || '';

		if (this.getRealDisplay(element) === 'none' ) {
			var nodeName = element.nodeName, body = document.body, display;

			if (displayCache[nodeName]) {
				display = displayCache[nodeName];
			} else {
				var testElem = document.createElement(nodeName);
				body.appendChild(testElem);
				display = this.getRealDisplay(testElem);

				if (display === 'none' ) {
					display = 'block';
				}

				body.removeChild(testElem);
				displayCache[nodeName] = display;
			}

			element.setAttribute('displayOld', display);
			element.style.display = display;
		}
	},
	hide: function(element)
	{
		if (!element.getAttribute('displayOld'))
		{
			element.setAttribute('displayOld', element.style.display);
		}
		element.style.display = 'none';
	},
	getRealDisplay: function (element)
	{
		if (element.currentStyle) {
			return element.currentStyle.display;
		} else if (window.getComputedStyle) {
			var computedStyle = window.getComputedStyle(element, null );
			return computedStyle.getPropertyValue('display');
		}
	},
	isEmptyObject: function(object)
	{
		for(var name in object) {
			return false;
		}
		return true;
	}
});

if(typeof(BX.ReportUserSearchPopup) === 'undefined')
{
    BX.ReportUserSearchPopup = function()
    {
        this._id = '';
        this._search_input = null;
        this._data_input = null;
        this._componentName = '';
        this._componentContainer = null;
        this._componentObj = null;
        this._serviceContainer = null;
        this._zIndex = 0;
        this._dlg = null;
        this._dlgDisplayed = false;
        this._currentUser = {};

        this._searchKeyHandler = BX.delegate(this._handleSearchKey, this);
        this._searchFocusHandler = BX.delegate(this._handleSearchFocus, this);
        this._externalClickHandler = BX.delegate(this._handleExternalClick, this);
        this._userSelectorInitCounter = 0;
    };

    BX.ReportUserSearchPopup.prototype =
    {
        //initialize: function(id, search_input, data_input, componentName, user, serviceContainer, zIndex)
        initialize: function(id, settings)
        {
            this._id = BX.type.isNotEmptyString(id) ? id : ('crm_user_search_popup_' + Math.random());

            if(!settings)
            {
                settings = {};
            }

            if(!BX.type.isElementNode(settings['searchInput']))
            {
                throw  "BX.ReportUserSearchPopup: 'search_input' is not defined!";
            }
            this._search_input = settings['searchInput'];

            if(!BX.type.isElementNode(settings['dataInput']))
            {
                throw  "BX.ReportUserSearchPopup: 'data_input' is not defined!";
            }
            this._data_input = settings['dataInput'];

            if(!BX.type.isNotEmptyString(settings['componentName']))
            {
                throw  "BX.ReportUserSearchPopup: 'componentName' is not defined!";
            }

            this._currentUser = settings['user'] ? settings['user'] : {};
            this._componentName = settings['componentName'];
            this._componentContainer = BX(this._componentName + '_selector_content');

            this._initializeUserSelector();
            this._adjustUser();

            this._serviceContainer = settings['serviceContainer'] ? settings['serviceContainer'] : document.body;
            this.setZIndex(settings['zIndex']);
        },
        _initializeUserSelector: function()
        {
            var objName = 'O_' + this._componentName;
            if(!window[objName])
            {
                if(this._userSelectorInitCounter === 10)
                {
                    throw "BX.ReportUserSearchPopup: Could not find '"+ objName +"' user selector!";
                }

                this._userSelectorInitCounter++;
                window.setTimeout(BX.delegate(this._initializeUserSelector, this), 200);
                return;
            }

            this._componentObj = window[objName];
            this._componentObj.onSelect = BX.delegate(this._handleUserSelect, this);
            this._componentObj.searchInput = this._search_input;

            if(this._currentUser)
            {
                this._componentObj.setSelected([ this._currentUser ]);
            }

            BX.bind(this._search_input, 'keyup', this._searchKeyHandler);
            BX.bind(this._search_input, 'focus', this._searchFocusHandler);
            BX.bind(document, 'click', this._externalClickHandler);
        },
        open: function()
        {
            this._componentContainer.style.display = '';
            this._dlg = new BX.PopupWindow(
                this._id,
                this._search_input,
                {
                    autoHide: false,
                    draggable: false,
                    closeByEsc: true,
                    offsetLeft: 0,
                    offsetTop: 0,
                    zIndex: this._zIndex,
                    bindOptions: { forceBindPosition: true },
                    content : this._componentContainer,
                    events:
                    {
                        onPopupShow: BX.delegate(
                            function()
                            {
                                this._dlgDisplayed = true;
                            },
                            this
                        ),
                        onPopupClose: BX.delegate(
                            function()
                            {
                                this._dlgDisplayed = false;
                                this._componentContainer.parentNode.removeChild(this._componentContainer);
                                this._serviceContainer.appendChild(this._componentContainer);
                                this._componentContainer.style.display = 'none';
                                this._dlg.destroy();
                            },
                            this
                        ),
                        onPopupDestroy: BX.delegate(
                            function()
                            {
                                this._dlg = null;
                            },
                            this
                        )
                    }
                }
            );

            this._dlg.show();
        },
        _adjustUser: function()
        {
            //var container = BX.findParent(this._search_input, { className: 'webform-field-textbox' });
            if(parseInt(this._currentUser['id']) > 0)
            {
                this._data_input.value = this._currentUser['id'];
                this._search_input.value = this._currentUser['name'] ? this._currentUser.name : this._currentUser['id'];
                //BX.removeClass(container, 'webform-field-textbox-empty');
            }
            else
            {
                this._data_input.value = this._search_input.value = '';
                //BX.addClass(container, 'webform-field-textbox-empty');
            }
        },
        getZIndex: function()
        {
            return this._zIndex;
        },
        setZIndex: function(zIndex)
        {
            if(typeof(zIndex) === 'undefined' || zIndex === null)
            {
                zIndex = 0;
            }

            var i = parseInt(zIndex);
            this._zIndex = !isNaN(i) ? i : 0;
        },
        close: function()
        {
            if(this._dlg)
            {
                this._dlg.close();
            }
        },
        select: function(user)
        {
            this._currentUser = user;
            this._adjustUser();
            if(this._componentObj)
            {
                this._componentObj.setSelected([ user ]);
            }
        },
        _onBeforeDelete: function()
        {
            if(BX.type.isElementNode(this._search_input))
            {
                BX.unbind(this._search_input, 'keyup', this._searchKeyHandler);
                BX.unbind(this._search_input, 'focus', this._searchFocusHandler);
            }
            BX.unbind(document, 'click', this._externalClickHandler);
        },
        _handleExternalClick: function(e)
        {
            if(!e)
            {
                e = window.event;
            }

            if(!this._dlgDisplayed)
            {
                return;
            }

            var target = null;
            if(e)
            {
                if(e.target)
                {
                    target = e.target;
                }
                else if(e.srcElement)
                {
                    target = e.srcElement;
                }
            }

            if(target !== this._search_input &&
                !BX.findParent(target, { attribute:{ id: this._componentName + '_selector_content' } }))
            {
                this._adjustUser();
                this.close();
            }
        },
        _handleSearchKey: function(e)
        {
            if(!this._dlg || !this._dlgDisplayed)
            {
                this.open();
            }

            this._componentObj.search();
        },
        _handleSearchFocus: function(e)
        {
            if(!this._dlg || !this._dlgDisplayed)
            {
                this.open();
            }

            this._componentObj._onFocus(e);
        },
        _handleUserSelect: function(user)
        {
            this._currentUser = user;
            this._adjustUser();
            this.close();
        }
    };

    BX.ReportUserSearchPopup.items = {};

    BX.ReportUserSearchPopup.create = function(id, settings, delay)
    {
        if(isNaN(delay))
        {
            delay = 0;
        }

        if(delay > 0)
        {
            window.setTimeout(
                function(){ BX.ReportUserSearchPopup.create(id, settings, 0); },
                delay
            );
            return null;
        }

        var self = new BX.ReportUserSearchPopup();
        self.initialize(id, settings);
        this.items[id] = self;
        return self;
    };

    BX.ReportUserSearchPopup.createIfNotExists = function(id, settings)
    {
        var self = this.items[id];
        if(typeof(self) !== 'undefined')
        {
            self.initialize(id, settings);
        }
        else
        {
            self = new BX.ReportUserSearchPopup();
            self.initialize(id, settings);
            this.items[id] = self;
        }
        return self;
    };

    BX.ReportUserSearchPopup.deletePopup = function(id)
    {
        var item = this.items[id];
        if(typeof(item) === 'undefined')
        {
            return false;
        }

        item._onBeforeDelete();
        delete this.items[id];
        return true;
    }
}