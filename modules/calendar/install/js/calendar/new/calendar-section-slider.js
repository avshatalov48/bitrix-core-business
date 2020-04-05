;(function(window) {

	function SectionSlider(params)
	{
		this.calendar = params.calendar;
		this.button = params.button;
		this.zIndex = params.zIndex || 3100;
		this.SLIDER_WIDTH = 400;
		this.SLIDER_DURATION = 80;
		this.sliderId = "calendar:section-slider";
		this.denyClose = false;
		BX.bind(this.button, 'click', BX.delegate(this.show, this));
	}

	SectionSlider.prototype = {
		show: function ()
		{
			BX.SidePanel.Instance.open(this.sliderId, {
				contentCallback: BX.delegate(this.create, this),
				width: this.SLIDER_WIDTH,
				animationDuration: this.SLIDER_DURATION
			});

			BX.addCustomEvent("SidePanel.Slider:onCloseByEsc", BX.proxy(this.escHide, this));
			BX.addCustomEvent("SidePanel.Slider:onClose", BX.proxy(this.hide, this));
			BX.addCustomEvent("SidePanel.Slider:onCloseComplete", BX.proxy(this.destroy, this));

			BX.addCustomEvent("BXCalendar:onSectionDelete", BX.proxy(this.deleteSectionHandler, this));
			BX.addCustomEvent("BXCalendar:onSectionChange", BX.proxy(this.changeSectionHandler, this));
			BX.addCustomEvent("BXCalendar:onSectionAdd", BX.proxy(this.addSectionHandler, this));
			this.calendar.disableKeyHandler();
		},

		escHide: function (event)
		{
			if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId && this.denyClose)
			{
				event.denyAction();
			}
		},

		hide: function (event)
		{
			if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId)
			{
				this.closeForms();
				BX.removeCustomEvent("SidePanel.Slider:onClose", BX.proxy(this.hide, this));
				BX.removeCustomEvent("SidePanel.Slider:onCloseByEsc", BX.proxy(this.escHide, this));
				BX.removeCustomEvent("BXCalendar:onSectionDelete", BX.proxy(this.deleteSectionHandler, this));
				BX.removeCustomEvent("BXCalendar:onSectionChange", BX.proxy(this.changeSectionHandler, this));
				BX.removeCustomEvent("BXCalendar:onSectionAdd", BX.proxy(this.addSectionHandler, this));
			}
		},

		close: function ()
		{
			BX.SidePanel.Instance.close();
		},

		destroy: function (event)
		{
			if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId)
			{
				BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", BX.proxy(this.destroy, this));
				BX.SidePanel.Instance.destroy(this.sliderId);
				delete this.sectionListWrap;

				this.calendar.enableKeyHandler();

				if (this.sectionActionMenu)
					this.sectionActionMenu.close();
			}
		},

		create: function ()
		{
			this.outerWrap = BX.create('DIV', {props: {className: 'calendar-list-slider-wrap'}});
			this.titleWrap = this.outerWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-title-container'}, html: '<div class="calendar-list-slider-title">' + BX.message('EC_SECTION_BUTTON') + '</div>'}));

			if (!this.calendar.util.readOnlyMode())
			{
				// #1. Controls
				this.createAddButton();

				// #2. Forms
				this.editSectionFormWrap = this.outerWrap.appendChild(BX.create('DIV', {
					props: {className: 'calendar-list-slider-card-widget calendar-list-slider-form-wrap'},
					html: '<div class="calendar-list-slider-card-widget-title"><span class="calendar-list-slider-card-widget-title-text">' + BX.message('EC_SEC_SLIDER_NEW_SECTION') + '</span></div>'
				}));

				this.trackingCompanyFormWrap = this.outerWrap.appendChild(BX.create('DIV', {
					props: {className: 'calendar-list-slider-card-widget calendar-list-slider-form-wrap'},
					html: '<div class="calendar-list-slider-card-widget-title"><span class="calendar-list-slider-card-widget-title-text">' + BX.message('EC_SEC_SLIDER_POPUP_MENU_ADD_COMP') + '</span></div>'
				}));

				this.trackingUsersFormWrap = this.outerWrap.appendChild(BX.create('DIV', {
					props: {className: 'calendar-list-slider-card-widget calendar-list-slider-form-wrap'},
					html: '<div class="calendar-list-slider-card-widget-title"><span class="calendar-list-slider-card-widget-title-text">' + BX.message('EC_SEC_SLIDER_POPUP_MENU_ADD_USER') + '</span></div>'
				}));

				this.trackingGroupsFormWrap = this.outerWrap.appendChild(BX.create('DIV', {
					props: {className: 'calendar-list-slider-card-widget calendar-list-slider-form-wrap'},
					html: '<div class="calendar-list-slider-card-widget-title"><span class="calendar-list-slider-card-widget-title-text">' + BX.message('EC_SEC_SLIDER_POPUP_MENU_ADD_GROUP') + '</span></div>'
				}));
			}

			// #3. List of sections
			this.createSectionList();

			return this.outerWrap;
		},

		createSectionList: function()
		{
			var sections, title;
			this.sliderSections = this.calendar.sectionController.getSectionList();

			if (this.calendar.util.type == 'user')
			{
				title = BX.message('EC_SEC_SLIDER_MY_CALENDARS_LIST');
			}
			else if (this.calendar.util.type == 'group')
			{
				title = BX.message('EC_SEC_SLIDER_GROUP_CALENDARS_LIST');
			}
			else
			{
				title = BX.message('EC_SEC_SLIDER_TYPE_CALENDARS_LIST');
			}

			if (this.sectionListWrap)
			{
				BX.cleanNode(this.sectionListWrap);
				BX.adjust(this.sectionListWrap, {
					props: {className: 'calendar-list-slider-card-widget'},
					html: '<div class="calendar-list-slider-card-widget-title"><span class="calendar-list-slider-card-widget-title-text">' + title + '</span></div>'
				});
			}
			else
			{
				this.sectionListWrap = this.outerWrap.appendChild(BX.create('DIV', {
					props: {className: 'calendar-list-slider-card-widget'},
					html: '<div class="calendar-list-slider-card-widget-title"><span class="calendar-list-slider-card-widget-title-text">' + title + '</span></div>'
				}));
			}

			this.createSectionBlock({
				wrap: this.sectionListWrap,
				sectionList: this.sliderSections.filter(function(section){
					return section.belongsToView() || section.isPseudo();
				})
			});

			// Company calendar
			sections = this.sliderSections.filter(function(section)
			{
				return section.isCompanyCalendar() && !section.belongsToView();
			});
			if (sections.length > 0)
			{
				this.sectionListWrap.appendChild(BX.create('DIV', {
					props: {className: 'calendar-list-slider-card-section-title'},
					html: '<span class="calendar-list-slider-card-section-title-text">' + BX.message('EC_SEC_SLIDER_TITLE_COMP_CAL') + '</span>'
				}));

				this.createSectionBlock({
					wrap: this.sectionListWrap, sectionList: this.sliderSections.filter(function (section)
					{
						return section.isCompanyCalendar();
					})
				});
			}

			// Users calendars
			this.calendar.util.getSuperposedTrackedUsers().forEach(function(user)
			{
				var sections = this.sliderSections.filter(function(section)
				{
					return !section.belongsToView()
						&& section.isSuperposed()
						&& section.type == 'user'
						&& section.data.OWNER_ID == user.ID;
				});

				if (sections.length > 0)
				{
					this.sectionListWrap.appendChild(BX.create('DIV', {
						props: {className: 'calendar-list-slider-card-section-title'},
						html: '<span class="calendar-list-slider-card-section-title-text">' + BX.util.htmlspecialchars(user.FORMATTED_NAME) + '</span>'
					}));
					this.createSectionBlock({
						wrap: this.sectionListWrap, sectionList: sections
					});
				}
			}, this);

			// Groups calendars
			sections = this.sliderSections.filter(function (section)
			{
				return !section.belongsToView() && section.type == 'group' && section.isSuperposed();
			});
			if (sections.length > 0)
			{
				this.sectionListWrap.appendChild(BX.create('DIV', {
					props: {className: 'calendar-list-slider-card-section-title'},
					html: '<span class="calendar-list-slider-card-section-title-text">' + BX.message('EC_SEC_SLIDER_TITLE_GROUP_CAL') + '</span>'
				}));
				this.createSectionBlock({
					wrap: this.sectionListWrap, sectionList: sections
				});
			}
		},

		createAddButton:function()
		{
			this.addButtonOuter = this.titleWrap.appendChild(BX.create('SPAN', {
				props: {className: 'webform-small-button-separate-wrap'},
				style: {marginRight: 0}
			}));

			this.addButton = this.addButtonOuter.appendChild(BX.create('SPAN', {props: {className: 'webform-small-button'}, text: BX.message('EC_ADD')}));
			this.addButtonMore = this.addButtonOuter.appendChild(BX.create('SPAN', {props: {className: 'webform-small-button-right-part'}}));

			this.addButtonMorePopupId = "add_btn_popup_" + this.calendar.id;
			BX.bind(this.addButtonMore, 'click', BX.proxy(this.showAddBtnPopup, this));
			BX.bind(this.addButton, 'click', BX.proxy(this.showEditSectionForm, this));
		},

		showAddBtnPopup: function(e)
		{
			if (this.addBtnMenu && this.addBtnMenu.popupWindow && this.addBtnMenu.popupWindow.isShown())
			{
				return this.addBtnMenu.close();
			}

			var
				_this = this,
				submenuClass = 'main-buttons-submenu-separator main-buttons-submenu-item main-buttons-hidden-label',
				menuItems = [
					{
						text: '<span>' + BX.message('EC_SEC_SLIDER_POPUP_NEW_TITLE') + '</span>',
						className: submenuClass
					},
					{
						text: BX.message('EC_SEC_SLIDER_POPUP_NEW_MENU'),
						onclick: BX.proxy(function(){
							this.addBtnMenu.close();
							this.showEditSectionForm();
						}, this)
					},
					{
						text: '<span>' + BX.message('EC_SEC_SLIDER_POPUP_EXIST_TITLE') + '</span>',
						className: submenuClass
					},
					{
						text: BX.message('EC_SEC_SLIDER_POPUP_MENU_ADD_COMP'),
						onclick: BX.proxy(function(){
							this.addBtnMenu.close();
							this.showTrackingTypesForm();
						}, this)
					},
					{
						text: BX.message('EC_SEC_SLIDER_POPUP_MENU_ADD_USER'),
						onclick: BX.proxy(function(){
							this.addBtnMenu.close();
							this.showTrackingUsersForm();
						}, this)
					},
					{
						text: BX.message('EC_SEC_SLIDER_POPUP_MENU_ADD_GROUP'),
						onclick: BX.proxy(function(){
							this.addBtnMenu.close();
							this.showTrackingGroupsForm();
						}, this)
					}
				];

			this.addBtnMenu = BX.PopupMenu.create(
				this.addButtonMorePopupId,
				this.addButtonMore,
				menuItems,
				{
					closeByEsc : true,
					autoHide : true,
					zIndex: this.zIndex,
					offsetTop: 0,
					offsetLeft: 15,
					angle: true
				}
			);

			this.addBtnMenu.show();

			//BX.addClass(_this.sectionField.select, 'active');
			this.denySliderClose();

			BX.addCustomEvent(this.addBtnMenu.popupWindow, 'onPopupClose', function()
			{
				_this.allowSliderClose();
				//BX.removeClass(_this.sectionField.select, 'active');
				BX.PopupMenu.destroy(_this.addButtonMorePopupId);
			});
		},

		createSectionBlock: function(params)
		{
			var result = false;
			if (params.sectionList && params.sectionList.length)
			{
				var listWrap = params.wrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-widget-content'}}))
					.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-widget-content-block'}}))
					.appendChild(BX.create('UL', {props: {className: 'calendar-list-slider-container'}}));

				BX.bind(listWrap, 'click', BX.proxy(this.sectionClickHandler, this));

				var i, li, checkbox, title, actionCont;
				for (i = 0; i < params.sectionList.length; i++)
				{
					li = listWrap.appendChild(BX.create('LI', {
						props: {className: 'calendar-list-slider-item'},
						attrs: {'data-bx-calendar-section': params.sectionList[i].id.toString()}
					}));

					checkbox = li.appendChild(BX.create('DIV', {
						props: {className: 'calendar-list-slider-item-checkbox' + (params.sectionList[i].isShown() ? ' calendar-list-slider-item-checkbox-checked' : '')},
						style: {backgroundColor: params.sectionList[i].color}
					}));

					title = li.appendChild(BX.create('DIV', {
						props: {className: 'calendar-list-slider-item-name'},
						text: params.sectionList[i].name
					}));

					actionCont = li.appendChild(BX.create('DIV', {
						props: {className: 'calendar-list-slider-item-actions-container'},
						attrs: {'data-bx-calendar-section-menu': params.sectionList[i].id.toString()},
						html: '<span class="calendar-list-slider-item-context-menu"></span>'
					}));

					if (!params.sectionList[i].DOM)
					{
						params.sectionList[i].DOM = {};
					}

					params.sectionList[i].DOM.item = li;
					params.sectionList[i].DOM.checkbox = checkbox;
					params.sectionList[i].DOM.title = title;
					params.sectionList[i].DOM.actionCont = actionCont;

				}
			}

			return result;
		},

		sectionClickHandler: function(e)
		{
			var target = this.calendar.util.findTargetNode(e.target || e.srcElement, this.outerWrap);

			if (target && target.getAttribute)
			{
				if (target.getAttribute('data-bx-calendar-section-menu') !== null)
				{
					this.showSectionMenu(this.calendar.sectionController.getSection(target.getAttribute('data-bx-calendar-section-menu')));
				}
				else if(target.getAttribute('data-bx-calendar-section') !== null)
				{
					this.switchSection(this.calendar.sectionController.getSection(target.getAttribute('data-bx-calendar-section')));
				}
			}
		},

		switchSection: function(section)
		{
			if (BX.hasClass(section.DOM.checkbox, 'calendar-list-slider-item-checkbox-checked'))
			{
				BX.removeClass(section.DOM.checkbox, 'calendar-list-slider-item-checkbox-checked');
				section.hide();
			}
			else
			{
				BX.addClass(section.DOM.checkbox, 'calendar-list-slider-item-checkbox-checked');
				section.show();
			}
			this.calendar.refresh();
		},

		showSectionMenu : function(section)
		{
			var
				_this = this,
				menuItems = [],
				menuId = this.calendar.id + '_section_' + section.id;

			BX.addClass(section.DOM.item, 'active');

			if (section.getLink() && !section.belongsToView())
			{
				menuItems.push({
					text: BX.message('EC_SEC_OPEN_LINK'),
					href: section.getLink()
				});
			}

			//if (el.PERM.edit_section && this.permEx.section_edit && !bSuperpose)
			if (!this.calendar.util.readOnlyMode() && section.canDo('edit_section') && !section.isPseudo())
			{
				menuItems.push({
					text : BX.message('EC_SEC_EDIT'),
					onclick: function(){
						_this.sectionActionMenu.close();
						_this.showEditSectionForm({
							section: section
						});
					}
				});
			}

			if (section.isSuperposed() && !section.belongsToView())
			{
				menuItems.push({
					text : BX.message('EC_SEC_HIDE'),
					onclick: function()
					{
						_this.hideSuperposedHandler(section);
						_this.sectionActionMenu.close();
					}
				});
			}

			if (section.canBeConnectedToOutlook())
			{
				menuItems.push({
					text : BX.message('EC_SEC_CONNECT_TO_OUTLOOK'),
					onclick: function(){
						_this.sectionActionMenu.close();
						section.connectToOutlook();
						_this.close();
					}
				});
			}

			if (!section.isPseudo() && section.data.EXPORT.LINK)
			{
				menuItems.push({
					text: BX.message('EC_ACTION_EXPORT'), onclick: BX.delegate(function ()
					{
						_this.sectionActionMenu.close();

						if (!_this.calendar.syncSlider)
						{
							_this.calendar.syncSlider = new window.BXEventCalendar.SyncSlider({
								calendar: _this.calendar
							});
						}

						_this.calendar.syncSlider.showICalExportDialog(section);
					}, this)
				});
			}

			//if (el.PERM.edit_section  && this.permEx.section_edit && !isGoogle  && !bSuperpose && !isFirstExchange)
			if (section.canDo('edit_section') && section.belongsToView() && !section.isPseudo())
			{
				menuItems.push({
					text : BX.message('EC_SEC_DELETE'),
					onclick: function(){
						_this.sectionActionMenu.close();
						section.remove();
					}
				});
			}

			if ((section.isGoogle() || section.isCalDav()) && section.canDo('edit_section'))
			{
				menuItems.push({
					text : BX.message('EC_ACTION_REFRESH'),
					onclick: BX.delegate(function ()
					{
						this.sectionActionMenu.close();
						this.calendar.reload({syncGoogle: true});
						this.close();
					}, this)
				});

				if (this.calendar.syncSlider)
				{
					menuItems.push({
						text : BX.message('EC_ACTION_EXTERNAL_ADJUST'),
						onclick: function(){
							_this.sectionActionMenu.close();
							_this.calendar.syncSlider.showCalDavSyncDialog();
						}
					});
				}

				menuItems.push({
					text: BX.message('EC_ACTION_HIDE'),
					onclick: BX.delegate(function ()
					{
						this.sectionActionMenu.close();
						section.hideGoogle();
					}, this)
				});
			}

			if (menuItems && menuItems.length > 0)
			{
				this.sectionActionMenu = BX.PopupMenu.create(
					menuId,
					section.DOM.actionCont,
					menuItems,
					{
						closeByEsc : true,
						autoHide : true,
						zIndex: this.zIndex,
						offsetTop: 0,
						offsetLeft: 9,
						angle: true
					}
				);

				this.sectionActionMenu.show();
				this.denySliderClose();

				BX.addCustomEvent(this.sectionActionMenu.popupWindow, 'onPopupClose', function()
				{
					if (section.DOM.item)
						BX.removeClass(section.DOM.item, 'active');
					_this.allowSliderClose();
					BX.PopupMenu.destroy(menuId);
				});
			}
		},

		denySliderClose: function()
		{
			this.denyClose = true;
		},

		allowSliderClose: function()
		{
			this.denyClose = false;
		},

		closeForms: function()
		{
			if (this.addBtnMenu)
				this.addBtnMenu.close();

			if (this.editSectionForm)
				this.editSectionForm.close();

			if (this.trackingUsersForm)
				this.trackingUsersForm.close();

			if (this.trackingGroupsForm)
				this.trackingGroupsForm.close();

			if (this.trackingTypesForm)
				this.trackingTypesForm.close();
		},

		showEditSectionForm: function(params)
		{
			if (!params)
				params = {};

			if (this.editSectionForm && this.editSectionForm.isOpenedState)
				return this.closeForms();

			this.closeForms();
			//setTimeout(BX.delegate(function(){
				this.editSectionFormTitle = this.editSectionFormWrap.querySelector('.calendar-list-slider-card-widget-title-text');

				this.editSectionForm = new SectionForm({
					calendar: this.calendar,
					wrap: this.editSectionFormWrap,
					zIndex: this.zIndex,
					closeCallback: BX.delegate(function()
					{
						this.allowSliderClose();
					}, this)
				});

				var showAccessControl = true;
				if (params.section && (!params.section.belongsToView() || params.section.isPseudo()))
				{
					this.editSectionFormTitle.innerHTML = BX.message('EC_SEC_SLIDER_EDIT_SECTION_PERSONAL');
					showAccessControl = false;
				}
				else if (params.section && params.section.id)
				{
					this.editSectionFormTitle.innerHTML = BX.message('EC_SEC_SLIDER_EDIT_SECTION');
				}
				else
				{
					this.editSectionFormTitle.innerHTML = BX.message('EC_SEC_SLIDER_NEW_SECTION');
				}

				this.editSectionForm.show({
					showAccess: showAccessControl,
					section: params.section || {
						color: this.calendar.sectionController.getDefaultSectionColor(),
						access: this.calendar.sectionController.getDefaultSectionAccess()
					}
				});

				this.denySliderClose();
			//}, this), 100);
		},

		showTrackingTypesForm: function()
		{
			this.closeForms();

			if (!this.trackingTypesForm)
			{
				this.trackingTypesForm = new TrackingTypesForm({
					calendar: this.calendar,
					wrap: this.trackingCompanyFormWrap,
					superposedSections: this.calendar.sectionController.getSuperposedSectionList(),
					closeCallback: BX.delegate(function()
					{
						this.allowSliderClose();
					}, this)
				});
			}

			this.trackingTypesForm.show();
			this.denySliderClose();
		},

		showTrackingUsersForm: function()
		{
			this.closeForms();

			if (!this.trackingUsersForm)
			{
				this.trackingUsersForm = new TrackingUsersForm({
					calendar: this.calendar,
					wrap: this.trackingUsersFormWrap,
					trackingUsers: this.calendar.util.getSuperposedTrackedUsers(),
					superposedSections: this.calendar.sectionController.getSuperposedSectionList(),
					closeCallback: BX.delegate(function()
					{
						this.allowSliderClose();
					}, this)
				});
			}

			this.trackingUsersForm.show();
			this.denySliderClose();
		},

		showTrackingGroupsForm: function()
		{
			this.closeForms();

			if (!this.trackingGroupsForm)
			{
				var
					superposedSections = this.calendar.sectionController.getSuperposedSectionList(),
					trackingGroups = this.calendar.util.getSuperposedTrackedGroups();

				if (!trackingGroups.length)
				{
					superposedSections.forEach(function(section)
					{
						if (section.type == 'group')
						{
							var groupId = section.data.OWNER_ID;
							if (!BX.util.in_array(groupId, trackingGroups))
							{
								trackingGroups.push(groupId);
							}
						}
					}, this);
				}

				this.trackingGroupsForm = new TrackingGroupsForm({
					calendar: this.calendar,
					wrap: this.trackingGroupsFormWrap,
					trackingGroups: trackingGroups,
					superposedSections: superposedSections
				});
			}

			this.trackingGroupsForm.show();
		},

		deleteSectionHandler: function(sectionId)
		{
			this.sliderSections.forEach(function(section, index)
			{
				if (section.id == sectionId && section.DOM && section.DOM.item)
				{
					BX.addClass(section.DOM.item, 'calendar-list-slider-item-disappearing');
					setTimeout(BX.delegate(function(){
						BX.cleanNode(section.DOM.item, true);
						this.sliderSections = BX.util.deleteFromArray(this.sliderSections, index);
					}, this), 300);
				}
			}, this);
		},

		hideSuperposedHandler: function(section)
		{
			var
				superposedSections = this.calendar.sectionController.getSuperposedSectionList(),
				sections = [], i;

			for (i = 0; i < superposedSections.length; i++)
			{
				if (section.id != parseInt(superposedSections[i].id))
					sections.push(parseInt(superposedSections[i].id));
			}

			this.calendar.request({
				data: {
					action: 'set_tracking_sections',
					sect: sections
				},
				handler: BX.delegate(function(response)
				{
					BX.reload();
				}, this)
			});
		},

		changeSectionHandler: function(sectionId, params)
		{
			this.sliderSections.forEach(function(section)
			{
				if (section.id == sectionId && section.DOM && section.DOM.item)
				{
					section.DOM.title.innerHTML = BX.util.htmlspecialchars(params.name);
					section.DOM.checkbox.style.backgroundColor = params.color;
				}
			}, this);
		},

		addSectionHandler: function()
		{
			this.createSectionList();
		}
	};

	function SectionForm(params)
	{
		this.calendar = params.calendar;
		this.outerWrap = params.wrap;
		this.zIndex = params.zIndex;
		this.closeCallback = params.closeCallback;
		this.isCreated = false;
	}

	SectionForm.prototype = {
		show: function (params)
		{
			this.create();

			this.showAccess = params.showAccess !== false;
			if (this.showAccess)
			{
				this.accessLink.style.display = '';
				this.accessWrap.style.display = '';
			}
			else
			{
				this.accessLink.style.display = 'none';
				this.accessWrap.style.display = 'none';
			}

			BX.bind(document, 'keydown', BX.proxy(this.keyHandler, this));
			BX.addClass(this.outerWrap, 'show');

			this.section = params.section;
			if (params.section)
			{
				if (params.section.color)
				{
					this.setColor(params.section.color);
				}

				this.setAccess(params.section.access || params.section.data.ACCESS || {});

				if (params.section.name)
				{
					this.sectionTitleInput.value = params.section.name;
				}
			}

			BX.focus(this.sectionTitleInput);
			if (this.sectionTitleInput.value !== '')
				this.sectionTitleInput.select();

			this.isOpenedState = true;
		},

		close: function()
		{
			this.isOpenedState = false;
			BX.unbind(document, 'keydown', BX.proxy(this.keyHandler, this));
			BX.removeClass(this.outerWrap, 'show');

			if (this.closeCallback)
				this.closeCallback();
		},

		isOpened: function()
		{
			return this.isOpenedState;
		},

		create: function()
		{
			this.wrap = this.outerWrap.querySelector('.calendar-form-content');

			if (this.wrap)
				BX.cleanNode(this.wrap);
			else
				this.wrap = this.outerWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-form-content'}}));

			this.formFieldsWrap = this.wrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-widget-content'}}))
				.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-widget-content-block'}}));

			// Title
			this.sectionTitleInput = this.formFieldsWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-field-container calendar-field-container-string'}}))
				.appendChild(BX.create('DIV', {props: {className: 'calendar-field-block'}}))
				.appendChild(BX.create('INPUT', {
					attrs: {type: 'text', placeholder: BX.message('EC_SEC_SLIDER_SECTION_TITLE')},
					props: {className: 'calendar-field calendar-field-string'}
				}));

			var optionsWrap = this.formFieldsWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-new-calendar-options-container'}}));

			// Color
			this.colorContWrap = optionsWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-new-calendar-option-color'}, html: BX.message('EC_SEC_SLIDER_COLOR')}));
			this.colorIcon = this.colorContWrap.appendChild(BX.create('SPAN', {
				props: {className: 'calendar-list-slider-new-calendar-option-color-selected'}
			}));
			this.colorChangeLink = this.colorContWrap.appendChild(BX.create('SPAN', {props: {className: 'calendar-list-slider-new-calendar-option-color-change'}, html: BX.message('EC_SEC_SLIDER_CHANGE')}));
			this.initSectionColorSelector();

			// Access
			this.accessLink = optionsWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-new-calendar-option-more'}, html: BX.message('EC_SEC_SLIDER_ACCESS')}));
			this.accessWrap = this.formFieldsWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-access-container'}}));
			this.initAccessController();

			// Buttons
			this.buttonsWrap = this.formFieldsWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-btn-container'}}));
			this.saveBtn = this.buttonsWrap.appendChild(BX.create('DIV', {
				props: {className: 'webform-small-button webform-small-button-blue'},
				text: BX.message('EC_SEC_SLIDER_SAVE'),
				events: {click: BX.proxy(this.save, this)}
			}));

			this.cancelBtn = this.buttonsWrap.appendChild(BX.create('SPAN', {
				props: {className: 'webform-button-link'},
				text: BX.message('EC_SEC_SLIDER_CANCEL'),
				events: {click: BX.proxy(this.checkClose, this)}
			}));

			this.isCreated = true;
		},

		keyHandler: function(e)
		{
			if(e.keyCode == this.calendar.util.KEY_CODES['escape'])
			{
				this.checkClose();
			}
			else if(e.keyCode == this.calendar.util.KEY_CODES['enter'])
			{
				this.save();
			}
		},

		checkClose: function()
		{
			this.close();
		},

		save: function()
		{
			this.calendar.sectionController.saveSection(
				this.sectionTitleInput.value,
				this.color,
				this.access,
				{section: this.section}
			);
			this.close();
		},

		initSectionColorSelector: function()
		{
			BX.bind(this.colorIcon, 'click', BX.delegate(this.showSimplePicker, this));
			BX.bind(this.colorChangeLink, 'click', BX.delegate(this.showSimplePicker, this));
		},

		showSimplePicker:function(value)
		{
			var
				colors = BX.clone(this.calendar.util.getDefaultColors(), true),
				innerCont = BX.create('DIV', {props: {className: 'calendar-simple-color-wrap calendar-field-container-colorpicker-square'}}),
				colorWrap = innerCont.appendChild(BX.create('DIV', {
					events: {click: BX.delegate(this.simplePickerClick, this)}
				})),
				moreLinkWrap = innerCont.appendChild(BX.create('DIV', {props: {className: 'calendar-simple-color-more-link-wrap'}})),
				moreLink = moreLinkWrap.appendChild(BX.create('SPAN', {
					props: {className: 'calendar-simple-color-more-link'},
					html: BX.message('EC_COLOR'),
					events: {click: BX.delegate(this.showFullPicker, this)}
				}));

			this.simplePickerColorWrap = colorWrap;
			this.colors = [];

			if (!BX.util.in_array(this.color, colors))
				colors.push(this.color);

			for (var i = 0; i < colors.length; i++)
			{
				this.colors.push({
					color: colors[i],
					node: colorWrap.appendChild(BX.create('SPAN', {
						props: {className: 'calendar-field-colorpicker-color-item'},
						style: {backgroundColor: colors[i]},
						attrs: {'data-bx-calendar-color': colors[i]},
						html: '<span class="calendar-field-colorpicker-color"></span>'
					}))
				});
			}

			this.lastActiveNode = this.colors[BX.util.array_search(this.color, colors) || 0].node;
			BX.addClass(this.lastActiveNode, 'active');

			this.simpleColorPopup = BX.PopupWindowManager.create(
				this.calendar.id + "-simple-color-popup",
				this.colorIcon,
				{
					zIndex: this.zIndex,
					autoHide: true,
					closeByEsc: true,
					offsetTop: 0,
					offsetLeft: 9,
					lightShadow: true,
					content: innerCont
				});

			this.simpleColorPopup.setAngle({offset: 10});
			this.simpleColorPopup.show(true);

			BX.addCustomEvent(this.simpleColorPopup, 'onPopupClose', BX.delegate(function()
			{
				this.simpleColorPopup.destroy();
			}, this));
		},

		simplePickerClick: function(e)
		{
			var target = this.calendar.util.findTargetNode(e.target || e.srcElement, this.outerWrap);
			if (target && target.getAttribute)
			{
				var value = target.getAttribute('data-bx-calendar-color');
				if(value !== null)
				{
					if (this.lastActiveNode)
					{
						BX.removeClass(this.lastActiveNode, 'active');
					}

					BX.addClass(target, 'active');
					this.lastActiveNode = target;
					this.setColor(value);
				}
			}
		},

		showFullPicker: function()
		{
			if (this.simpleColorPopup)
				this.simpleColorPopup.close();

			if (!this.fullColorPicker)
			{
				this.fullColorPicker = new BX.ColorPicker({
					bindElement: this.colorIcon,
					onColorSelected: BX.delegate(function(color){
						this.setColor(color);
					}, this),
					popupOptions: {
						zIndex: this.zIndex,
						events: {
							onPopupClose:BX.delegate(function(){
							}, this)
						}
					}
				});
			}
			this.fullColorPicker.open();
		},

		setColor: function(value)
		{
			this.colorIcon.style.backgroundColor = value;
			this.color = value;
		},

		setAccess: function(value)
		{
			var rowsCount = 0;
			for (var code in value)
			{
				if (value.hasOwnProperty(code))
				{
					rowsCount++;
				}
			}
			this.accessRowsCount = rowsCount;
			this.access = value;

			for (code in value)
			{
				if (value.hasOwnProperty(code))
				{
					this.insertAccessRow(this.calendar.util.getAccessName(code), code, value[code]);
				}
			}
			this.checkAccessTableHeight();
		},

		initAccessController: function()
		{
			this.accessControls = {};
			this.accessTasks = this.calendar.util.getSectionAccessTasks();

			BX.bind(this.accessLink, 'click', BX.delegate(function(){
				if (BX.hasClass(this.accessWrap, 'shown'))
				{
					BX.removeClass(this.accessWrap, 'shown');
				}
				else
				{
					BX.addClass(this.accessWrap, 'shown');
				}
				this.checkAccessTableHeight();
			}, this));

			BX.Access.Init();

			this.accessWrapInner = this.accessWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-access-inner-wrap'}}));
			this.accessTable = this.accessWrapInner.appendChild(BX.create("TABLE", {props: {className: "calendar-section-slider-access-table"}}));
			this.accessButtonWrap = this.accessWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-new-calendar-options-container'}}));
			this.accessButton = this.accessButtonWrap.appendChild(BX.create('SPAN', {props: {className: 'calendar-list-slider-new-calendar-option-add'}, html: BX.message('EC_SEC_SLIDER_ACCESS_ADD')}));

			BX.bind(this.accessButton, 'click', BX.proxy(function()
			{
				BX.Access.ShowForm({
					showSelected: false,
					callback: BX.proxy(function(selected)
					{
						var provider, code;
						for(provider in selected)
						{
							if (selected.hasOwnProperty(provider))
							{
								for (code in selected[provider])
								{
									if (selected[provider].hasOwnProperty(code))
									{
										this.calendar.util.setAccessName(code, BX.Access.GetProviderName(provider) + ' ' + selected[provider][code].name);
										this.insertAccessRow(this.calendar.util.getAccessName(code), code);
									}
								}
							}
						}
						this.checkAccessTableHeight();
					}, this),
					bind: this.calendar.id + '_calendar_section_' + Math.round(Math.random() * 100000)
				});

				if (BX.Access.popup && BX.Access.popup.popupContainer)
				{
					BX.Access.popup.popupContainer.style.zIndex = this.zIndex + 10;
				}
			}, this));


			BX.bind(this.accessWrapInner, 'click', BX.proxy(function(e)
			{
				var
					code,
					target = this.calendar.util.findTargetNode(e.target || e.srcElement, this.outerWrap);
				if (target && target.getAttribute)
				{
					if(target.getAttribute('data-bx-calendar-access-selector') !== null)
					{
						// show selector
						code = target.getAttribute('data-bx-calendar-access-selector');
						if (this.accessControls[code])
						{
							this.showAccessSelectorPopup({
									node: this.accessControls[code].removeIcon,
									setValueCallback: BX.delegate(function(value)
									{
										if (this.accessTasks[value] && this.accessControls[code])
										{
											this.accessControls[code].valueNode.innerHTML = BX.util.htmlspecialchars(this.accessTasks[value].title);
											this.access[code] = value;
										}
									}, this)
								}
							);
						}
					}
					else if(target.getAttribute('data-bx-calendar-access-remove') !== null)
					{
						code = target.getAttribute('data-bx-calendar-access-remove');
						if (this.accessControls[code])
						{
							BX.remove(this.accessControls[code].rowNode);
							this.accessControls[code] = null;
							delete this.access[code];
						}
					}
				}

			}, this));
		},

		insertAccessRow: function(title, code, value)
		{
			if (!this.accessControls[code])
			{
				if (value === undefined)
				{
					value = this.calendar.util.getDefaultSectionAccessTask();
				}

				var
					rowNode = BX.adjust(this.accessTable.insertRow(-1), {props : {className: 'calendar-section-slider-access-table-row'}}),
					titleNode = BX.adjust(rowNode.insertCell(-1), {
						props : {className: 'calendar-section-slider-access-table-cell'},
						html: '<span class="calendar-section-slider-access-title">' + BX.util.htmlspecialchars(title) + ':</span>'}),
					valueCell = BX.adjust(rowNode.insertCell(-1), {
						props : {className: 'calendar-section-slider-access-table-cell'},
						attrs: {'data-bx-calendar-access-selector': code}
					}),
					selectNode = valueCell.appendChild(BX.create('SPAN', {
						props: {className: 'calendar-section-slider-access-value'}
					})),
					valueNode = selectNode.appendChild(BX.create('SPAN', {
						text: this.accessTasks[value] ? this.accessTasks[value].title : ''
					})),
					removeIcon = selectNode.appendChild(BX.create('SPAN', {
						props: {className: 'calendar-section-slider-access-remove'},
						attrs: {'data-bx-calendar-access-remove': code}
					}));

				this.access[code] = value;

				this.accessControls[code] = {
					rowNode: rowNode,
					titleNode: titleNode,
					valueNode: valueNode,
					removeIcon: removeIcon
				};
			}
		},

		checkAccessTableHeight: function()
		{
			if (this.checkTableTimeout)
			{
				this.checkTableTimeout = clearTimeout(this.checkTableTimeout);
			}

			this.checkTableTimeout = setTimeout(BX.delegate(function(){
				if (BX.hasClass(this.accessWrap, 'shown'))
				{
					if (this.accessWrap.offsetHeight - this.accessTable.offsetHeight < 36)
					{
						this.accessWrap.style.maxHeight = parseInt(this.accessTable.offsetHeight) + 100 + 'px';
					}
				}
				else
				{
					this.accessWrap.style.maxHeight = '';
				}
			}, this), 300);
		},

		showAccessSelectorPopup: function(params)
		{
			if (this.accessPopupMenu && this.accessPopupMenu.popupWindow && this.accessPopupMenu.popupWindow.isShown())
			{
				return this.accessPopupMenu.close();
			}

			var
				menuId = this.calendar.id + '_section_access_popup',
				taskId,
				_this = this,
				menuItems = [];

			for(taskId in this.accessTasks)
			{
				if (this.accessTasks.hasOwnProperty(taskId))
				{
					menuItems.push(
						{
							text: this.accessTasks[taskId].title,
							onclick: (function (value)
							{
								return function ()
								{
									params.setValueCallback(value);
									_this.accessPopupMenu.close();
								}
							})(taskId)
						}
					);
				}
			}

			this.accessPopupMenu = BX.PopupMenu.create(
				menuId,
				params.node,
				menuItems,
				{
					closeByEsc : true,
					autoHide : true,
					zIndex: this.zIndex,
					offsetTop: -5,
					offsetLeft: 0,
					angle: true
				}
			);

			this.accessPopupMenu.show();

			BX.addCustomEvent(this.accessPopupMenu.popupWindow, 'onPopupClose', function()
			{
				BX.PopupMenu.destroy(menuId);
			});
		}
	};

	function TrackingUsersForm(params)
	{
		this.calendar = params.calendar;
		this.outerWrap = params.wrap;
		this.trackingUsers = params.trackingUsers || [];
		this.selectedCodes = {};
		this.CHECKED_CLASS = 'calendar-list-slider-item-checkbox-checked';
		this.selectorId = this.calendar.id + '_tracking_users';
		this.selectGroups = false;
		this.selectUsers = true;
		this.addLinkMessage = BX.message('EC_SEC_SLIDER_SELECT_USERS');
		this.closeCallback = params.closeCallback;

		this.selected = {};
		params.superposedSections.forEach(function(section)
		{
			this.selected[section.id] = true;
		}, this);

		this.isCreated = false;
	}

	TrackingUsersForm.prototype = {
		show: function ()
		{
			if (!this.innerWrap)
			{
				this.innerWrap = this.outerWrap.appendChild(BX.create('DIV'));
			}
			this.trackingUsers.forEach(function(user)
			{
				this.selectedCodes['U' + user.ID] = 'users';
			}, this);

			if (!this.isCreated)
			{
				this.create();
			}

			BX.addClass(this.outerWrap, 'show');
			this.checkInnerWrapHeight();

			BX.bind(document, 'keydown', BX.proxy(this.keyHandler, this));

			this.updateSectionList();
			this.isOpenedState = true;
		},

		close: function()
		{
			BX.bind(document, 'keydown', BX.proxy(this.keyHandler, this));

			this.isOpenedState = false;
			BX.removeClass(this.outerWrap, 'show');
			this.outerWrap.style.cssText = '';

			if (this.closeCallback)
				this.closeCallback();
		},

		isOpened: function()
		{
			return this.isOpenedState;
		},

		create: function()
		{
			this.selectorWrap = this.innerWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-selector-wrap'}}));

			// Attendees selector
			this.destinationSelector = new window.BXEventCalendar.DestinationSelector(this.selectorId,
			{
				calendar: this.calendar,
				wrapNode: this.selectorWrap,
				itemsSelected : this.selectedCodes,
				addLinkMessage: this.addLinkMessage,
				selectGroups: this.selectGroups,
				selectUsers: this.selectUsers
			});
			BX.addCustomEvent('OnDestinationAddNewItem', BX.proxy(this.updateSectionList, this));
			BX.addCustomEvent('OnDestinationUnselect', BX.proxy(this.updateSectionList, this));

			// List of sections
			this.sectionsWrap = this.innerWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-sections-wrap'}}));

			// Buttons
			this.buttonsWrap = this.innerWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-btn-container'}}));
			this.saveBtn = this.buttonsWrap.appendChild(BX.create('DIV', {
				props: {className: 'webform-small-button webform-small-button-blue'},
				text: BX.message('EC_SEC_SLIDER_SAVE'),
				events: {click: BX.proxy(this.save, this)}
			}));

			this.cancelBtn = this.buttonsWrap.appendChild(BX.create('SPAN', {
				props: {className: 'webform-button-link'},
				text: BX.message('EC_SEC_SLIDER_CANCEL'),
				events: {click: BX.proxy(this.close, this)}
			}));

			this.isCreated = true;
		},

		save: function()
		{
			var
				superposedSections = this.calendar.sectionController.getSuperposedSectionList(),
				sections = [], users = [], id, i;

			for (i = 0; i < superposedSections.length; i++)
			{
				if (superposedSections[i].type != 'user')
				{
					sections.push(parseInt(superposedSections[i].id));
				}
			}

			for (id in this.sectionIndex)
			{
				if (this.sectionIndex.hasOwnProperty(id))
				{
					if (BX.hasClass(this.sectionIndex[id].checkbox, this.CHECKED_CLASS))
					{
						if (!BX.util.in_array(id, sections))
						{
							sections.push(parseInt(id));
						}
					}
					else if(BX.util.in_array(id, sections))
					{
						sections = BX.util.deleteFromArray(sections, BX.util.array_search(id, sections));
					}
				}
			}

			// save tracking users
			this.calendar.request({
				data: {
					action: 'set_tracking_sections',
					codes: this.destinationSelector.getCodes(),
					sect: sections,
					type: 'users'
				},
				handler: BX.delegate(function(response)
				{
					BX.reload();
				}, this)
			});

			this.close();
		},

		updateSectionList: function()
		{
			var codes = this.destinationSelector.getCodes();

			this.sectionsWrap.appendChild(BX.adjust(this.calendar.util.getLoader(), {style: {height: '140px'}}));

			this.checkInnerWrapHeight();
			this.calendar.request({
				data: {
					action: 'get_tracking_sections',
					codes: codes || [],
					type: 'users'
				},
				handler: BX.delegate(function(response)
				{
					BX.cleanNode(this.sectionsWrap);
					this.sectionIndex = {};
					this.checkInnerWrapHeight();

					// Users calendars
					response.users.forEach(function(user)
					{
						var sections = response.sections.filter(function(section)
						{
							return section.OWNER_ID == user.ID;
						});

						this.sectionsWrap.appendChild(BX.create('DIV', {
							props: {className: 'calendar-list-slider-card-section-title'},
							html: '<span class="calendar-list-slider-card-section-title-text">' + BX.util.htmlspecialchars(user.FORMATTED_NAME) + '</span>'}));

						if (sections.length > 0)
						{
							this.createSectionBlock({
								sectionList: sections,
								wrap: this.sectionsWrap
							});
						}
						else
						{
							this.sectionsWrap.appendChild(BX.create('DIV', {
								props: {className: ''},
								html: '<span class="">' + BX.message('EC_SEC_SLIDER_NO_SECTIONS') + '</span>'}));
						}
					}, this);

				}, this)
			});
		},

		createSectionBlock: function(params)
		{
			var result = false;
			if (params.sectionList && params.sectionList.length)
			{
				var listWrap = params.wrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-widget-content'}}))
					.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-widget-content-block'}}))
					.appendChild(BX.create('UL', {props: {className: 'calendar-list-slider-container'}}));

				BX.bind(listWrap, 'click', BX.proxy(this.sectionClick, this));

				var i, li, checkbox, title, id;
				for (i = 0; i < params.sectionList.length; i++)
				{
					id = params.sectionList[i].ID.toString();
					li = listWrap.appendChild(BX.create('LI', {
						props: {className: 'calendar-list-slider-item'},
						attrs: {'data-bx-calendar-section': id}
					}));

					checkbox = li.appendChild(BX.create('DIV', {
						props: {className: 'calendar-list-slider-item-checkbox'},
						style: {backgroundColor: params.sectionList[i].COLOR}
					}));

					title = li.appendChild(BX.create('DIV', {
						props: {className: 'calendar-list-slider-item-name'},
						text: params.sectionList[i].NAME
					}));

					this.sectionIndex[id] = {
						item: li,
						checkbox: checkbox
					};

					if (this.selected[id])
					{
						BX.addClass(checkbox, this.CHECKED_CLASS);
					}
				}
			}

			return result;
		},

		sectionClick: function(e)
		{
			var target = this.calendar.util.findTargetNode(e.target || e.srcElement, this.outerWrap);
			if (target && target.getAttribute)
			{
				if(target.getAttribute('data-bx-calendar-section') !== null)
				{
					var id = target.getAttribute('data-bx-calendar-section');
					if (this.sectionIndex[id] && this.sectionIndex[id].checkbox)
					{
						if (BX.hasClass(this.sectionIndex[id].checkbox, this.CHECKED_CLASS))
						{
							BX.removeClass(this.sectionIndex[id].checkbox, this.CHECKED_CLASS);
						}
						else
						{
							BX.addClass(this.sectionIndex[id].checkbox, this.CHECKED_CLASS);
						}
					}
				}
			}
		},

		keyHandler: function(e)
		{
			if(e.keyCode == this.calendar.util.KEY_CODES['escape'])
			{
				this.close();
			}
			else if(e.keyCode == this.calendar.util.KEY_CODES['enter'])
			{
				this.save();
			}
		},

		checkInnerWrapHeight: function()
		{
			if (this.checkHeightTimeout)
			{
				this.checkHeightTimeout = clearTimeout(this.checkHeightTimeout);
			}

			this.checkHeightTimeout = setTimeout(BX.delegate(function(){
				if (BX.hasClass(this.outerWrap, 'show'))
				{
					if (this.outerWrap.offsetHeight - this.innerWrap.offsetHeight < 36)
					{
						this.outerWrap.style.maxHeight = parseInt(this.innerWrap.offsetHeight) + 200 + 'px';
					}
				}
				else
				{
					this.outerWrap.style.maxHeight = '';
				}
			}, this), 300);
		}
	};

	function TrackingTypesForm(params)
	{
		TrackingUsersForm.apply(this, arguments);
		this.trackingGroups = params.trackingGroups || [];
		this.selectGroups = true;
		this.selectUsers = false;
		this.addLinkMessage = BX.message('EC_SEC_SLIDER_SELECT_GROUPS');

	}
	TrackingTypesForm.prototype = Object.create(TrackingUsersForm.prototype);
	TrackingTypesForm.prototype.constructor = TrackingTypesForm;

	TrackingTypesForm.prototype.create = function()
	{
		if (!this.innerWrap)
		{
			this.innerWrap = this.outerWrap.appendChild(BX.create('DIV'));
		}


		// List of sections
		this.sectionsWrap = this.innerWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-sections-wrap'}}));

		// Buttons
		this.buttonsWrap = this.innerWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-list-slider-btn-container'}}));
		this.saveBtn = this.buttonsWrap.appendChild(BX.create('DIV', {
			props: {className: 'webform-small-button webform-small-button-blue'},
			text: BX.message('EC_SEC_SLIDER_SAVE'),
			events: {click: BX.proxy(this.save, this)}
		}));

		this.cancelBtn = this.buttonsWrap.appendChild(BX.create('SPAN', {
			props: {className: 'webform-button-link'},
			text: BX.message('EC_SEC_SLIDER_CANCEL'),
			events: {click: BX.proxy(this.close, this)}
		}));

		this.isCreated = true;
	};

	TrackingTypesForm.prototype.show = function()
	{
		if (!this.isCreated)
		{
			this.create();
		}

		this.updateSectionList();
		this.isOpenedState = true;
		BX.addClass(this.outerWrap, 'show');
	};

	TrackingTypesForm.prototype.updateSectionList = function()
	{
		this.sectionsWrap.appendChild(BX.adjust(this.calendar.util.getLoader(), {style: {height: '140px'}}));
		this.calendar.request({
			data: {
				action: 'get_tracking_sections',
				type: 'company'
			},
			handler: BX.delegate(function(response)
			{
				BX.cleanNode(this.sectionsWrap);
				this.sectionIndex = {};
				this.createSectionBlock({
					sectionList: response.sections,
					wrap: this.sectionsWrap
				});
				this.checkInnerWrapHeight();
			}, this)
		});
		this.checkInnerWrapHeight();
	};

	TrackingTypesForm.prototype.save = function()
	{
		var
			superposedSections = this.calendar.sectionController.getSuperposedSectionList(),
			sections = [], id, i;

		for (i = 0; i < superposedSections.length; i++)
		{
			sections.push(parseInt(superposedSections[i].id));
		}

		for (id in this.sectionIndex)
		{
			if (this.sectionIndex.hasOwnProperty(id))
			{
				if (BX.hasClass(this.sectionIndex[id].checkbox, this.CHECKED_CLASS))
				{
					if (!BX.util.in_array(id, sections))
					{
						sections.push(parseInt(id));
					}
				}
				else if(BX.util.in_array(id, sections))
				{
					sections = BX.util.deleteFromArray(sections, BX.util.array_search(id, sections));
				}
			}
		}

		// save tracking users
		this.calendar.request({
			data: {
				action: 'set_tracking_sections',
				sect: sections
			},
			handler: BX.delegate(function(response)
			{
				BX.reload();
			}, this)
		});

		this.close();
	};


	function TrackingGroupsForm(params)
	{
		TrackingUsersForm.apply(this, arguments);
		this.trackingGroups = params.trackingGroups || [];
		this.selectorId = this.calendar.id + '_tracking_groups';
		this.selectGroups = true;
		this.selectUsers = false;
		this.addLinkMessage = BX.message('EC_SEC_SLIDER_SELECT_GROUPS');
	}
	TrackingGroupsForm.prototype = Object.create(TrackingUsersForm.prototype);
	TrackingGroupsForm.prototype.constructor = TrackingGroupsForm;

	TrackingGroupsForm.prototype.show = function()
	{
		this.trackingGroups.forEach(function(groupId)
		{
			this.selectedCodes['SG' + groupId] = "sonetgroups";
		}, this);
		TrackingUsersForm.prototype.show.apply(this, arguments);
	};

	TrackingGroupsForm.prototype.save = function()
	{
		var
			superposedSections = this.calendar.sectionController.getSuperposedSectionList(),
			sections = [], id, i;

		for (i = 0; i < superposedSections.length; i++)
		{
			sections.push(parseInt(superposedSections[i].id));
		}

		for (id in this.sectionIndex)
		{
			if (this.sectionIndex.hasOwnProperty(id))
			{
				if (BX.hasClass(this.sectionIndex[id].checkbox, this.CHECKED_CLASS))
				{
					if (!BX.util.in_array(id, sections))
					{
						sections.push(parseInt(id));
					}
				}
				else if(BX.util.in_array(id, sections))
				{
					sections = BX.util.deleteFromArray(sections, BX.util.array_search(id, sections));
				}
			}
		}

		// save tracking users
		this.calendar.request({
			data: {
				action: 'set_tracking_sections',
				codes: this.destinationSelector.getCodes(),
				sect: sections,
				type: 'groups'
			},
			handler: BX.delegate(function(response)
			{
				BX.reload();
			}, this)
		});

		this.close();
	};

	TrackingGroupsForm.prototype.updateSectionList = function()
	{
		var codes = this.destinationSelector.getCodes();
		this.sectionsWrap.appendChild(BX.adjust(this.calendar.util.getLoader(), {style: {height: '140px'}}));
		this.calendar.request({
			data: {
				action: 'get_tracking_sections',
				codes: codes || [],
				type: 'groups'
			},
			handler: BX.delegate(function(response)
			{
				BX.cleanNode(this.sectionsWrap);
				this.sectionIndex = {};
				this.createSectionBlock({
					sectionList: response.sections,
					wrap: this.sectionsWrap
				});
				this.checkInnerWrapHeight();
			}, this)
		});
		this.checkInnerWrapHeight();
	};

	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.SectionSlider = SectionSlider;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.SectionSlider = SectionSlider;
		});
	}
})(window);