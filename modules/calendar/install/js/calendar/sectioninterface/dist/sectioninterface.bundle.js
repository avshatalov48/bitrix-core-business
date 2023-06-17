this.BX = this.BX || {};
(function (exports,calendar_sync_interface,main_popup,main_core_events,ui_entitySelector,main_core,calendar_util,calendar_sectionmanager,ui_dialogs_messagebox) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4;
	class EditForm extends main_core_events.EventEmitter {
	  constructor(options = {}) {
	    super();
	    this.DOM = {};
	    this.isCreated = false;
	    this.setEventNamespace('BX.Calendar.SectionInterface.EditForm');
	    this.DOM.outerWrap = options.wrap;
	    this.sectionAccessTasks = options.sectionAccessTasks;
	    this.sectionManager = options.sectionManager;
	    this.closeCallback = options.closeCallback;
	    this.BX = calendar_util.Util.getBX();
	    this.keyHandlerBinded = this.keyHandler.bind(this);
	  }
	  show(params = {}) {
	    this.section = params.section;
	    this.create();
	    this.showAccess = params.showAccess !== false;
	    this.allowChangeName = params.allowChangeName !== false;
	    if (this.showAccess) {
	      this.DOM.accessLink.style.display = '';
	      this.DOM.accessWrap.style.display = '';
	    } else {
	      this.DOM.accessLink.style.display = 'none';
	      this.DOM.accessWrap.style.display = 'none';
	    }
	    main_core.Event.bind(document, 'keydown', this.keyHandlerBinded);
	    main_core.Dom.addClass(this.DOM.outerWrap, 'show');
	    if (params.section) {
	      if (params.section.color) {
	        this.setColor(params.section.color);
	      }
	      this.setAccess(params.section.access || params.section.data.ACCESS || {});
	      if (params.section.name) {
	        this.DOM.sectionTitleInput.value = params.section.name;
	      }
	    }
	    if (this.allowChangeName) {
	      BX.focus(this.DOM.sectionTitleInput);
	      if (this.DOM.sectionTitleInput.value !== '') {
	        this.DOM.sectionTitleInput.select();
	      }
	    } else {
	      main_core.Dom.addClass(this.DOM.sectionTitleInput, '--disabled');
	      this.DOM.sectionTitleInput.disabled = true;
	    }
	    this.isOpenedState = true;
	  }
	  close() {
	    this.isOpenedState = false;
	    main_core.Event.unbind(document, 'keydown', this.keyHandlerBinded);
	    main_core.Dom.removeClass(this.DOM.outerWrap, 'show');
	    if (main_core.Type.isFunction(this.closeCallback)) {
	      this.closeCallback();
	    }
	  }
	  isOpened() {
	    return this.isOpenedState;
	  }
	  create() {
	    this.wrap = this.DOM.outerWrap.querySelector('.calendar-form-content');
	    if (this.wrap) {
	      main_core.Dom.clean(this.wrap);
	    } else {
	      this.wrap = this.DOM.outerWrap.appendChild(main_core.Dom.create('DIV', {
	        props: {
	          className: 'calendar-form-content'
	        }
	      }));
	    }
	    this.DOM.formFieldsWrap = this.wrap.appendChild(main_core.Dom.create('DIV', {
	      props: {
	        className: 'calendar-list-slider-widget-content'
	      }
	    })).appendChild(main_core.Dom.create('DIV', {
	      props: {
	        className: 'calendar-list-slider-widget-content-block'
	      }
	    }));

	    // Title
	    this.DOM.sectionTitleInput = this.DOM.formFieldsWrap.appendChild(main_core.Dom.create('DIV', {
	      props: {
	        className: 'calendar-field-container calendar-field-container-string'
	      }
	    })).appendChild(main_core.Dom.create('DIV', {
	      props: {
	        className: 'calendar-field-block'
	      }
	    })).appendChild(main_core.Dom.create('INPUT', {
	      attrs: {
	        type: 'text',
	        placeholder: main_core.Loc.getMessage('EC_SEC_SLIDER_SECTION_TITLE')
	      },
	      props: {
	        className: 'calendar-field calendar-field-string'
	      }
	    }));
	    this.DOM.optionsWrap = this.DOM.formFieldsWrap.appendChild(main_core.Dom.create('DIV', {
	      props: {
	        className: 'calendar-list-slider-new-calendar-options-container'
	      }
	    }));
	    this.initSectionColorSelector();
	    this.initAccessController();

	    // Buttons
	    this.buttonsWrap = this.DOM.formFieldsWrap.appendChild(main_core.Dom.create('DIV', {
	      props: {
	        className: 'calendar-list-slider-btn-container'
	      }
	    }));
	    this.saveBtn = new BX.UI.Button({
	      text: main_core.Loc.getMessage('EC_SEC_SLIDER_SAVE'),
	      className: 'ui-btn ui-btn-success',
	      events: {
	        click: this.save.bind(this)
	      }
	    });
	    this.saveBtn.renderTo(this.buttonsWrap);
	    new BX.UI.Button({
	      text: main_core.Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
	      className: 'ui-btn ui-btn-link',
	      events: {
	        click: this.checkClose.bind(this)
	      }
	    }).renderTo(this.buttonsWrap);
	    this.isCreated = true;
	  }
	  keyHandler(e) {
	    if (e.keyCode === calendar_util.Util.getKeyCode('escape')) {
	      this.checkClose();
	    } else if (e.keyCode === calendar_util.Util.getKeyCode('enter')) {
	      this.save();
	    }
	  }
	  checkClose() {
	    this.close();
	  }
	  save() {
	    this.saveBtn.setWaiting(true);
	    this.sectionManager.saveSection(this.DOM.sectionTitleInput.value, this.color, this.access, {
	      section: this.section
	    }).then(() => {
	      this.saveBtn.setWaiting(false);
	      this.close();
	    });
	  }
	  initSectionColorSelector() {
	    this.DOM.colorContWrap = this.DOM.optionsWrap.appendChild(main_core.Dom.create('DIV', {
	      props: {
	        className: 'calendar-list-slider-new-calendar-option-color'
	      },
	      html: main_core.Loc.getMessage('EC_SEC_SLIDER_COLOR')
	    }));
	    this.colorIcon = this.DOM.colorContWrap.appendChild(main_core.Dom.create('SPAN', {
	      props: {
	        className: 'calendar-list-slider-new-calendar-option-color-selected'
	      }
	    }));
	    this.colorChangeLink = this.DOM.colorContWrap.appendChild(main_core.Dom.create('SPAN', {
	      props: {
	        className: 'calendar-list-slider-new-calendar-option-color-change'
	      },
	      html: main_core.Loc.getMessage('EC_SEC_SLIDER_CHANGE')
	    }));
	    main_core.Event.bind(this.colorIcon, 'click', this.showSimplePicker.bind(this));
	    main_core.Event.bind(this.colorChangeLink, 'click', this.showSimplePicker.bind(this));
	  }
	  showSimplePicker(value) {
	    const colors = main_core.Runtime.clone(calendar_util.Util.getDefaultColorList(), true);
	    const innerCont = main_core.Dom.create('DIV', {
	      props: {
	        className: 'calendar-simple-color-wrap calendar-field-container-colorpicker-square'
	      }
	    });
	    const colorWrap = innerCont.appendChild(main_core.Dom.create('DIV', {
	      events: {
	        click: BX.delegate(this.simplePickerClick, this)
	      }
	    }));
	    const moreLinkWrap = innerCont.appendChild(main_core.Dom.create('DIV', {
	      props: {
	        className: 'calendar-simple-color-more-link-wrap'
	      }
	    }));
	    const moreLink = moreLinkWrap.appendChild(main_core.Dom.create('SPAN', {
	      props: {
	        className: 'calendar-simple-color-more-link'
	      },
	      html: main_core.Loc.getMessage('EC_COLOR'),
	      events: {
	        click: BX.delegate(this.showFullPicker, this)
	      }
	    }));
	    this.simplePickerColorWrap = colorWrap;
	    this.colors = [];
	    if (!colors.includes(this.color)) {
	      colors.push(this.color);
	    }
	    for (let i = 0; i < colors.length; i++) {
	      this.colors.push({
	        color: colors[i],
	        node: colorWrap.appendChild(main_core.Dom.create('SPAN', {
	          props: {
	            className: 'calendar-field-colorpicker-color-item'
	          },
	          style: {
	            backgroundColor: colors[i]
	          },
	          attrs: {
	            'data-bx-calendar-color': colors[i]
	          },
	          html: '<span class="calendar-field-colorpicker-color"></span>'
	        }))
	      });
	    }
	    this.lastActiveNode = this.colors[BX.util.array_search(this.color, colors) || 0].node;
	    main_core.Dom.addClass(this.lastActiveNode, 'active');
	    this.simpleColorPopup = BX.PopupWindowManager.create("simple-color-popup-" + calendar_util.Util.getRandomInt(), this.colorIcon, {
	      //zIndex: this.zIndex,
	      autoHide: true,
	      closeByEsc: true,
	      offsetTop: 0,
	      offsetLeft: 9,
	      lightShadow: true,
	      content: innerCont,
	      cacheable: false
	    });
	    this.simpleColorPopup.setAngle({
	      offset: 10
	    });
	    this.simpleColorPopup.show(true);
	  }
	  simplePickerClick(e) {
	    const target = calendar_util.Util.findTargetNode(e.target || e.srcElement, this.DOM.outerWrap);
	    if (main_core.Type.isElementNode(target)) {
	      const value = target.getAttribute('data-bx-calendar-color');
	      if (value !== null) {
	        if (this.lastActiveNode) {
	          main_core.Dom.removeClass(this.lastActiveNode, 'active');
	        }
	        main_core.Dom.addClass(target, 'active');
	        this.lastActiveNode = target;
	        this.setColor(value);
	      }
	    }
	  }
	  showFullPicker() {
	    var _this$fullColorPicker;
	    if (this.simpleColorPopup) {
	      this.simpleColorPopup.close();
	    }
	    if (!this.fullColorPicker || (_this$fullColorPicker = this.fullColorPicker.getPopupWindow()) != null && _this$fullColorPicker.isDestroyed()) {
	      this.fullColorPicker = new BX.ColorPicker({
	        bindElement: this.colorIcon,
	        onColorSelected: BX.delegate(function (color) {
	          this.setColor(color);
	        }, this),
	        popupOptions: {
	          cacheable: false,
	          zIndex: this.zIndex,
	          events: {
	            onPopupClose: BX.delegate(function () {}, this)
	          }
	        }
	      });
	    }
	    this.fullColorPicker.open();
	  }
	  setColor(value) {
	    this.colorIcon.style.backgroundColor = value;
	    this.color = value;
	  }
	  setAccess(value) {
	    let rowsCount = 0;
	    for (let code in value) {
	      if (value.hasOwnProperty(code)) {
	        rowsCount++;
	      }
	    }
	    this.accessRowsCount = rowsCount;
	    this.access = value;
	    for (let code in value) {
	      if (value.hasOwnProperty(code)) {
	        this.insertAccessRow(calendar_util.Util.getAccessName(code), code, value[code]);
	      }
	    }
	    this.checkAccessTableHeight();
	  }
	  initAccessController() {
	    this.buildAccessController();
	    if (this.sectionManager && this.sectionManager.calendarType === 'group') {
	      this.initDialogGroup();
	    } else {
	      this.initDialogStandard();
	    }
	    this.initAccessSelectorPopup();
	  }
	  initAccessSelectorPopup() {
	    main_core.Event.bind(this.DOM.accessWrap, 'click', e => {
	      const target = calendar_util.Util.findTargetNode(e.target || e.srcElement, this.DOM.outerWrap);
	      if (main_core.Type.isElementNode(target)) {
	        if (target.getAttribute('data-bx-calendar-access-selector') !== null) {
	          // show selector
	          const code = target.getAttribute('data-bx-calendar-access-selector');
	          if (this.accessControls[code]) {
	            this.showAccessSelectorPopup({
	              node: this.accessControls[code].removeIcon,
	              setValueCallback: value => {
	                if (this.accessTasks[value] && this.accessControls[code]) {
	                  this.accessControls[code].valueNode.innerHTML = main_core.Text.encode(this.accessTasks[value].title);
	                  this.access[code] = value;
	                }
	              }
	            });
	          }
	        } else if (target.getAttribute('data-bx-calendar-access-remove') !== null) {
	          const code = target.getAttribute('data-bx-calendar-access-remove');
	          if (this.accessControls[code]) {
	            main_core.Dom.remove(this.accessControls[code].rowNode);
	            this.accessControls[code] = null;
	            delete this.access[code];
	          }
	        }
	      }
	    });
	  }
	  buildAccessController() {
	    this.DOM.accessLink = this.DOM.optionsWrap.appendChild(main_core.Tag.render(_t || (_t = _`<div class="calendar-list-slider-new-calendar-option-more">${0}</div>`), main_core.Loc.getMessage('EC_SEC_SLIDER_ACCESS')));
	    this.DOM.accessWrap = this.DOM.formFieldsWrap.appendChild(main_core.Tag.render(_t2 || (_t2 = _`
				<div class="calendar-list-slider-access-container">
					<div class="calendar-list-slider-access-inner-wrap">
						${0}
					</div>
					<div class="calendar-list-slider-new-calendar-options-container">
						${0}
					</div>
				</div>`), this.DOM.accessTable = main_core.Tag.render(_t3 || (_t3 = _`
							<table class="calendar-section-slider-access-table"></table>
						`)), this.DOM.accessButton = main_core.Tag.render(_t4 || (_t4 = _`
							<span class="calendar-list-slider-new-calendar-option-add">
								${0}
							</span>`), main_core.Loc.getMessage('EC_SEC_SLIDER_ACCESS_ADD'))));
	    this.accessControls = {};
	    this.accessTasks = this.sectionAccessTasks;
	    main_core.Event.bind(this.DOM.accessLink, 'click', () => {
	      if (main_core.Dom.hasClass(this.DOM.accessWrap, 'shown')) {
	        main_core.Dom.removeClass(this.DOM.accessWrap, 'shown');
	      } else {
	        main_core.Dom.addClass(this.DOM.accessWrap, 'shown');
	      }
	      this.checkAccessTableHeight();
	    });
	  }
	  initDialogStandard() {
	    main_core.Event.bind(this.DOM.accessButton, 'click', () => {
	      this.entitySelectorDialog = new ui_entitySelector.Dialog({
	        targetNode: this.DOM.accessButton,
	        context: 'CALENDAR',
	        preselectedItems: [],
	        enableSearch: true,
	        events: {
	          'Item:onSelect': this.handleEntitySelectorChanges.bind(this),
	          'Item:onDeselect': this.handleEntitySelectorChanges.bind(this)
	        },
	        popupOptions: {
	          targetContainer: document.body
	        },
	        entities: [{
	          id: 'user'
	        }, {
	          id: 'project'
	        }, {
	          id: 'department',
	          options: {
	            selectMode: 'usersAndDepartments'
	          }
	        }, {
	          id: 'meta-user',
	          options: {
	            'all-users': true
	          }
	        }]
	      });
	      this.entitySelectorDialog.show();
	    });
	  }
	  initDialogGroup() {
	    main_core.Event.bind(this.DOM.accessButton, 'click', () => {
	      this.entitySelectorDialog = new ui_entitySelector.Dialog({
	        targetNode: this.DOM.accessButton,
	        context: 'CALENDAR',
	        preselectedItems: [],
	        enableSearch: true,
	        events: {
	          'Item:onSelect': this.handleEntitySelectorChanges.bind(this),
	          'Item:onDeselect': this.handleEntitySelectorChanges.bind(this)
	        },
	        popupOptions: {
	          targetContainer: document.body
	        },
	        entities: [{
	          id: 'user'
	        }, {
	          id: 'department',
	          options: {
	            selectMode: 'usersAndDepartments'
	          }
	        }, {
	          id: 'meta-user',
	          options: {
	            'all-users': true
	          }
	        }],
	        tabs: [{
	          id: 'groupAccess',
	          title: this.sectionManager.ownerName
	        }],
	        items: [{
	          id: 'SG' + this.sectionManager.ownerId + '_' + 'A',
	          entityId: 'group',
	          tabs: 'groupAccess',
	          title: main_core.Loc.getMessage('EC_ACCESS_GROUP_ADMIN')
	        }, {
	          id: 'SG' + this.sectionManager.ownerId + '_' + 'E',
	          entityId: 'group',
	          tabs: 'groupAccess',
	          title: main_core.Loc.getMessage('EC_ACCESS_GROUP_MODERATORS')
	        }, {
	          id: 'SG' + this.sectionManager.ownerId + '_' + 'K',
	          entityId: 'group',
	          tabs: 'groupAccess',
	          title: main_core.Loc.getMessage('EC_ACCESS_GROUP_MEMBERS')
	        }]
	      });
	      this.entitySelectorDialog.show();
	    });
	  }
	  handleEntitySelectorChanges() {
	    const entityList = this.entitySelectorDialog.getSelectedItems();
	    this.entitySelectorDialog.hide();
	    if (main_core.Type.isArray(entityList)) {
	      entityList.forEach(entity => {
	        let title;
	        if (entity.entityId === 'group') {
	          title = this.sectionManager.ownerName + ': ' + entity.title.text;
	        } else {
	          title = entity.title.text;
	        }
	        const code = calendar_util.Util.convertEntityToAccessCode(entity);
	        calendar_util.Util.setAccessName(code, title);
	        this.insertAccessRow(title, code);
	      });
	    }
	    main_core.Runtime.debounce(() => {
	      this.entitySelectorDialog.destroy();
	    }, 400)();
	  }

	  // todo: refactor it
	  insertAccessRow(title, code, value) {
	    if (!this.accessControls[code]) {
	      if (value === undefined) {
	        for (let taskId in this.sectionAccessTasks) {
	          if (this.sectionAccessTasks.hasOwnProperty(taskId) && this.sectionAccessTasks[taskId].name === 'calendar_view') {
	            value = taskId;
	            break;
	          }
	        }
	      }
	      const rowNode = main_core.Dom.adjust(this.DOM.accessTable.insertRow(-1), {
	          props: {
	            className: 'calendar-section-slider-access-table-row'
	          }
	        }),
	        titleNode = main_core.Dom.adjust(rowNode.insertCell(-1), {
	          props: {
	            className: 'calendar-section-slider-access-table-cell'
	          },
	          html: '<span class="calendar-section-slider-access-title">' + main_core.Text.encode(title) + ':</span>'
	        }),
	        valueCell = main_core.Dom.adjust(rowNode.insertCell(-1), {
	          props: {
	            className: 'calendar-section-slider-access-table-cell'
	          },
	          attrs: {
	            'data-bx-calendar-access-selector': code
	          }
	        }),
	        selectNode = valueCell.appendChild(main_core.Dom.create('SPAN', {
	          props: {
	            className: 'calendar-section-slider-access-container'
	          }
	        })),
	        valueNode = selectNode.appendChild(main_core.Dom.create('SPAN', {
	          text: this.accessTasks[value] ? this.accessTasks[value].title : '',
	          props: {
	            className: 'calendar-section-slider-access-value'
	          }
	        })),
	        removeIcon = selectNode.appendChild(main_core.Dom.create('SPAN', {
	          props: {
	            className: 'calendar-section-slider-access-remove'
	          },
	          attrs: {
	            'data-bx-calendar-access-remove': code
	          }
	        }));
	      this.access[code] = value;
	      this.accessControls[code] = {
	        rowNode: rowNode,
	        titleNode: titleNode,
	        valueNode: valueNode,
	        removeIcon: removeIcon
	      };
	    }
	  }
	  checkAccessTableHeight() {
	    if (this.checkTableTimeout) {
	      this.checkTableTimeout = clearTimeout(this.checkTableTimeout);
	    }
	    this.checkTableTimeout = setTimeout(() => {
	      if (main_core.Dom.hasClass(this.DOM.accessWrap, 'shown')) {
	        if (this.DOM.accessWrap.offsetHeight - this.DOM.accessTable.offsetHeight < 36) {
	          this.DOM.accessWrap.style.maxHeight = parseInt(this.DOM.accessTable.offsetHeight) + 100 + 'px';
	        }
	      } else {
	        this.DOM.accessWrap.style.maxHeight = '';
	      }
	    }, 300);
	  }
	  showAccessSelectorPopup(params) {
	    if (this.accessPopupMenu && this.accessPopupMenu.popupWindow && this.accessPopupMenu.popupWindow.isShown()) {
	      return this.accessPopupMenu.close();
	    }
	    const _this = this;
	    const menuItems = [];
	    for (let taskId in this.accessTasks) {
	      if (this.accessTasks.hasOwnProperty(taskId)) {
	        menuItems.push({
	          text: this.accessTasks[taskId].title,
	          onclick: function (value) {
	            return function () {
	              params.setValueCallback(value);
	              _this.accessPopupMenu.close();
	            };
	          }(taskId)
	        });
	      }
	    }
	    this.accessPopupMenu = this.BX.PopupMenu.create('section-access-popup' + calendar_util.Util.randomInt(), params.node, menuItems, {
	      closeByEsc: true,
	      autoHide: true,
	      offsetTop: -5,
	      offsetLeft: 0,
	      angle: true,
	      cacheable: false
	    });
	    this.accessPopupMenu.show();
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1,
	  _t4$1,
	  _t5,
	  _t6,
	  _t7,
	  _t8,
	  _t9;
	class TrackingUsersForm {
	  constructor(options = {}) {
	    this.DOM = {};
	    this.isCreated = false;
	    this.interfaceType = 'users';
	    this.DOM.outerWrap = options.wrap;
	    this.trackingUsers = options.trackingUsers || [];
	    this.trackingUserIdList = this.trackingUsers.map(item => {
	      return parseInt(item.ID);
	    });
	    this.trackingGroupIdList = [];
	    this.CHECKED_CLASS = 'calendar-list-slider-item-checkbox-checked';
	    this.selectorId = 'add-tracking' + calendar_util.Util.getRandomInt();
	    this.closeCallback = options.closeCallback;
	    this.superposedSections = main_core.Type.isArray(options.superposedSections) ? options.superposedSections : [];
	    this.selected = {};
	    this.superposedSections.forEach(section => {
	      this.selected[section.id] = true;
	    }, this);
	    this.isCreated = false;
	    this.keyHandlerBinded = this.keyHandler.bind(this);
	  }
	  show() {
	    if (!this.isCreated) {
	      this.create();
	    }
	    main_core.Dom.addClass(this.DOM.outerWrap, 'show');
	    this.checkInnerWrapHeight();
	    main_core.Event.bind(document, 'keydown', this.keyHandlerBinded);
	    this.updateSectionList();
	    this.firstTrackingUserIdList = main_core.Runtime.clone(this.trackingUserIdList);
	    this.isOpenedState = true;
	  }
	  close() {
	    main_core.Event.unbind(document, 'keydown', this.keyHandlerBinded);
	    this.isOpenedState = false;
	    main_core.Dom.removeClass(this.DOM.outerWrap, 'show');
	    this.DOM.outerWrap.style.cssText = '';
	    if (main_core.Type.isFunction(this.closeCallback)) {
	      this.closeCallback();
	    }
	  }
	  isOpened() {
	    return this.isOpenedState;
	  }
	  create() {
	    if (!this.DOM.innerWrap) {
	      this.DOM.innerWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t$1 || (_t$1 = _$1`<div></div>`)));
	    }
	    this.selectorWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create('DIV', {
	      props: {
	        className: 'calendar-list-slider-selector-wrap'
	      }
	    }));
	    this.userTagSelector = new ui_entitySelector.TagSelector({
	      dialogOptions: {
	        width: 320,
	        context: 'CALENDAR',
	        preselectedItems: this.trackingUsers.map(item => {
	          return ['user', parseInt(item.ID)];
	        }),
	        events: {
	          'Item:onSelect': this.handleUserSelectorChanges.bind(this),
	          'Item:onDeselect': this.handleUserSelectorChanges.bind(this)
	        },
	        entities: [{
	          id: 'user'
	        }]
	      }
	    });
	    this.userTagSelector.renderTo(this.selectorWrap);

	    // List of sections
	    this.sectionsWrap = this.DOM.innerWrap.appendChild(main_core.Tag.render(_t2$1 || (_t2$1 = _$1`<div class="calendar-list-slider-sections-wrap"></div>`)));
	    this.createButtons();
	    this.isCreated = true;
	  }
	  createButtons() {
	    this.DOM.innerWrap.appendChild(main_core.Tag.render(_t3$1 || (_t3$1 = _$1`<div class="calendar-list-slider-btn-container">
				<button 
					class="ui-btn ui-btn-sm ui-btn-primary"
					onclick="${0}"
				>${0}</button>
				<button 
					class="ui-btn ui-btn-link"
					onclick="${0}"
				>${0}</button>
			</div>`), this.save.bind(this), main_core.Loc.getMessage('EC_SEC_SLIDER_SAVE'), this.close.bind(this), main_core.Loc.getMessage('EC_SEC_SLIDER_CANCEL')));
	  }
	  handleUserSelectorChanges() {
	    const selectedItems = this.userTagSelector.getDialog().getSelectedItems();
	    this.trackingUserIdList = [];
	    selectedItems.forEach(item => {
	      if (item.entityId === 'user') {
	        this.trackingUserIdList.push(item.id);
	      }
	    });
	    this.updateSectionList();
	  }
	  save() {
	    BX.ajax.runAction('calendar.api.calendarajax.setTrackingSections', {
	      data: {
	        userIdList: this.trackingUserIdList,
	        sections: this.prepareTrackingSections(),
	        type: this.interfaceType
	      }
	    }).then(response => {
	      location.reload();
	    }, response => {
	      calendar_util.Util.displayError(response.errors);
	    });
	    this.close();
	  }
	  prepareTrackingSections() {
	    let sections = this.getSelectedSections();
	    for (let id in this.sectionIndex) {
	      if (this.sectionIndex.hasOwnProperty(id) && this.sectionIndex[id].checkbox) {
	        if (main_core.Dom.hasClass(this.sectionIndex[id].checkbox, this.CHECKED_CLASS)) {
	          if (!sections.includes(parseInt(id))) {
	            sections.push(parseInt(id));
	          }
	        } else if (sections.includes(parseInt(id))) {
	          sections = sections.filter(section => {
	            return parseInt(section) !== parseInt(id);
	          });
	        }
	      }
	    }
	    return sections;
	  }
	  getSelectedSections() {
	    const sections = [];
	    this.superposedSections.forEach(section => {
	      if (this.interfaceType === 'users' && section.type === 'user' && this.trackingUserIdList && !this.trackingUserIdList.includes(section.ownerId)) {
	        return;
	      }
	      sections.push(parseInt(section.id));
	    }, this);
	    return sections;
	  }
	  updateSectionList(delayExecution) {
	    if (this.updateSectionLoader) {
	      main_core.Dom.remove(this.updateSectionLoader);
	    }
	    this.updateSectionLoader = this.sectionsWrap.appendChild(main_core.Dom.adjust(calendar_util.Util.getLoader(), {
	      style: {
	        height: '140px'
	      }
	    }));
	    if (this.updateSectionTimeout) {
	      this.updateSectionTimeout = clearTimeout(this.updateSectionTimeout);
	    }
	    if (delayExecution !== false) {
	      this.updateSectionTimeout = setTimeout(() => {
	        this.updateSectionList(false);
	      }, 300);
	      return;
	    }
	    this.checkInnerWrapHeight();
	    BX.ajax.runAction('calendar.api.calendarajax.getTrackingSections', {
	      data: {
	        userIdList: this.trackingUserIdList,
	        type: 'users'
	      }
	    }).then(
	    // Success
	    response => {
	      main_core.Dom.clean(this.sectionsWrap);
	      this.sectionIndex = {};
	      this.checkInnerWrapHeight();

	      // Users calendars
	      response.data.users.forEach(user => {
	        const sections = response.data.sections.filter(function (section) {
	          return parseInt(section.OWNER_ID) === parseInt(user.ID);
	        });
	        this.sectionsWrap.appendChild(main_core.Tag.render(_t4$1 || (_t4$1 = _$1`
							<div>
								<span class="calendar-list-slider-card-section-title-text">
									${0}
								</span>
							</div>
						`), main_core.Text.encode(user.FORMATTED_NAME)));
	        if (sections.length > 0) {
	          this.createSectionBlock({
	            sectionList: sections,
	            wrap: this.sectionsWrap
	          });
	        } else {
	          this.sectionsWrap.appendChild(main_core.Tag.render(_t5 || (_t5 = _$1`
								<div>
									<span class="calendar-list-slider-card-section-title-text">
										${0}
									</span>
								</div>
							`), main_core.Loc.getMessage('EC_SEC_SLIDER_NO_SECTIONS')));
	        }
	      });
	    }, response => {
	      calendar_util.Util.displayError(response.errors);
	    });
	  }
	  createSectionBlock(params = {}) {
	    let result = false;
	    if (main_core.Type.isArray(params.sectionList) && params.sectionList.length && main_core.Type.isElementNode(params.wrap)) {
	      let listWrap;
	      params.wrap.appendChild(main_core.Tag.render(_t6 || (_t6 = _$1`
				<div class="calendar-list-slider-widget-content">
					<div class="calendar-list-slider-widget-content-block">
						${0}
					</div>
				</div>
			`), listWrap = main_core.Tag.render(_t7 || (_t7 = _$1`<ul class="calendar-list-slider-container"></ul>`))));
	      main_core.Event.bind(listWrap, 'click', this.sectionClick.bind(this));
	      params.sectionList.forEach(section => {
	        const id = section.ID.toString();
	        let checkbox;
	        const li = listWrap.appendChild(main_core.Tag.render(_t8 || (_t8 = _$1`
					<li class="calendar-list-slider-item" data-bx-calendar-section="${0}">
						${0}
						<div class="calendar-list-slider-item-name">${0}</div>
					</li>
				`), id, checkbox = main_core.Tag.render(_t9 || (_t9 = _$1`
							<div class="calendar-list-slider-item-checkbox" style="background: ${0}"></div>
						`), section.COLOR), main_core.Text.encode(section.NAME)));
	        this.sectionIndex[id] = {
	          item: li,
	          checkbox: checkbox
	        };
	        if (this.selected[id] || !main_core.Type.isArray(this.firstTrackingUserIdList) || !this.firstTrackingUserIdList.includes(parseInt(section.OWNER_ID))) {
	          main_core.Dom.addClass(checkbox, this.CHECKED_CLASS);
	        }
	      });
	    }
	    return result;
	  }
	  sectionClick(e) {
	    const target = calendar_util.Util.findTargetNode(e.target || e.srcElement, this.DOM.outerWrap);
	    if (main_core.Type.isElementNode(target)) {
	      if (target.getAttribute('data-bx-calendar-section') !== null) {
	        const id = target.getAttribute('data-bx-calendar-section');
	        if (this.sectionIndex[id] && this.sectionIndex[id].checkbox) {
	          if (main_core.Dom.hasClass(this.sectionIndex[id].checkbox, this.CHECKED_CLASS)) {
	            main_core.Dom.removeClass(this.sectionIndex[id].checkbox, this.CHECKED_CLASS);
	          } else {
	            main_core.Dom.addClass(this.sectionIndex[id].checkbox, this.CHECKED_CLASS);
	          }
	        }
	      }
	    }
	  }
	  keyHandler(e) {
	    if (e.keyCode === calendar_util.Util.getKeyCode('escape')) {
	      this.close();
	    } else if (e.keyCode === calendar_util.Util.getKeyCode('enter')) {
	      this.save();
	    }
	  }
	  checkInnerWrapHeight() {
	    if (this.checkHeightTimeout) {
	      this.checkHeightTimeout = clearTimeout(this.checkHeightTimeout);
	    }
	    this.checkHeightTimeout = setTimeout(() => {
	      if (main_core.Dom.hasClass(this.DOM.outerWrap, 'show')) {
	        if (this.DOM.outerWrap.offsetHeight - this.DOM.innerWrap.offsetHeight < 36) {
	          this.DOM.outerWrap.style.maxHeight = parseInt(this.DOM.innerWrap.offsetHeight) + 200 + 'px';
	        }
	      } else {
	        this.DOM.outerWrap.style.maxHeight = '';
	      }
	    }, 300);
	  }
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$2;
	class TrackingGroupsForm extends TrackingUsersForm {
	  constructor(options = {}) {
	    super(options);
	    this.interfaceType = 'groups';
	    this.trackingGroupIdList = options.trackingGroups || [];
	  }
	  create() {
	    if (!this.DOM.innerWrap) {
	      this.DOM.innerWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t$2 || (_t$2 = _$2`<div></div>`)));
	    }
	    this.selectorWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create('DIV', {
	      props: {
	        className: 'calendar-list-slider-selector-wrap'
	      }
	    }));
	    this.groupTagSelector = new ui_entitySelector.TagSelector({
	      dialogOptions: {
	        width: 320,
	        context: 'CALENDAR',
	        preselectedItems: this.trackingGroupIdList.map(id => {
	          return ['project', id];
	        }),
	        events: {
	          'Item:onSelect': this.handleGroupSelectorChanges.bind(this),
	          'Item:onDeselect': this.handleGroupSelectorChanges.bind(this)
	        },
	        entities: [{
	          id: 'project'
	        }]
	      }
	    });
	    this.groupTagSelector.renderTo(this.selectorWrap);

	    // List of sections
	    this.sectionsWrap = this.DOM.innerWrap.appendChild(main_core.Tag.render(_t2$2 || (_t2$2 = _$2`<div class="calendar-list-slider-sections-wrap"></div>`)));
	    this.createButtons();
	    this.isCreated = true;
	  }
	  handleGroupSelectorChanges() {
	    const selectedItems = this.groupTagSelector.getDialog().getSelectedItems();
	    this.trackingGroupIdList = [];
	    selectedItems.forEach(item => {
	      if (item.entityId === 'project') {
	        this.trackingGroupIdList.push(item.id);
	      }
	    });
	    this.updateSectionList();
	  }
	  updateSectionList() {
	    if (this.updateSectionLoader) {
	      main_core.Dom.remove(this.updateSectionLoader);
	    }
	    this.updateSectionLoader = this.sectionsWrap.appendChild(main_core.Dom.adjust(calendar_util.Util.getLoader(), {
	      style: {
	        height: '140px'
	      }
	    }));
	    if (this.updateSectionTimeout) {
	      this.updateSectionTimeout = clearTimeout(this.updateSectionTimeout);
	    }
	    this.checkInnerWrapHeight();
	    BX.ajax.runAction('calendar.api.calendarajax.getTrackingSections', {
	      data: {
	        groupIdList: this.trackingGroupIdList,
	        type: 'groups'
	      }
	    }).then(response => {
	      main_core.Dom.clean(this.sectionsWrap);
	      this.sectionIndex = {};
	      this.checkInnerWrapHeight();

	      // Groups calendars
	      this.createSectionBlock({
	        sectionList: response.data.sections,
	        wrap: this.sectionsWrap
	      });
	    }, response => {
	      calendar_util.Util.displayError(response.errors);
	    });
	  }
	  getSelectedSections() {
	    const sections = [];
	    this.superposedSections.forEach(section => {
	      if (this.interfaceType === 'groups' && section.type === 'group' && this.trackingGroupIdList && !this.trackingGroupIdList.includes(section.ownerId)) {
	        return;
	      }
	      sections.push(parseInt(section.id));
	    }, this);
	    return sections;
	  }
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$3,
	  _t3$2;
	class TrackingTypesForm extends TrackingUsersForm {
	  constructor(options = {}) {
	    super(options);
	    this.trackingGroups = options.trackingGroups || [];
	    this.interfaceType = 'company';
	    this.selectGroups = true;
	    this.selectUsers = false;
	    this.addLinkMessage = main_core.Loc.getMessage('EC_SEC_SLIDER_SELECT_GROUPS');
	  }
	  show() {
	    if (!this.isCreated) {
	      this.create();
	    }
	    this.updateSectionList();
	    this.isOpenedState = true;
	    main_core.Dom.addClass(this.DOM.outerWrap, 'show');
	  }
	  create() {
	    if (!this.DOM.innerWrap) {
	      this.DOM.innerWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t$3 || (_t$3 = _$3`<div></div>`)));
	    }

	    // List of sections
	    this.sectionsWrap = this.DOM.innerWrap.appendChild(main_core.Tag.render(_t2$3 || (_t2$3 = _$3`<div class="calendar-list-slider-sections-wrap"></div>`)));
	    this.createButtons();
	    this.isCreated = true;
	  }
	  updateSectionList() {
	    if (this.updateSectionLoader) {
	      main_core.Dom.remove(this.updateSectionLoader);
	    }
	    this.updateSectionLoader = this.sectionsWrap.appendChild(main_core.Dom.adjust(calendar_util.Util.getLoader(), {
	      style: {
	        height: '140px'
	      }
	    }));
	    if (this.updateSectionTimeout) {
	      this.updateSectionTimeout = clearTimeout(this.updateSectionTimeout);
	    }
	    BX.ajax.runAction('calendar.api.calendarajax.getTrackingSections', {
	      data: {
	        type: 'company'
	      }
	    }).then(response => {
	      main_core.Dom.clean(this.sectionsWrap);
	      this.sectionIndex = {};
	      this.checkInnerWrapHeight();
	      if (main_core.Type.isArray(response.data.sections) && response.data.sections.length) {
	        this.createSectionBlock({
	          sectionList: response.data.sections,
	          wrap: this.sectionsWrap
	        });
	      } else {
	        this.sectionsWrap.appendChild(main_core.Tag.render(_t3$2 || (_t3$2 = _$3`
								<div>
									<span class="calendar-list-slider-card-section-title-text">
										${0}
									</span>
								</div>
							`), main_core.Loc.getMessage('EC_SEC_SLIDER_NO_SECTIONS')));
	      }
	    }, response => {
	      calendar_util.Util.displayError(response.errors);
	    });
	    this.checkInnerWrapHeight();
	  }
	  save() {
	    BX.ajax.runAction('calendar.api.calendarajax.setTrackingSections', {
	      data: {
	        sections: this.prepareTrackingSections()
	      }
	    }).then(response => {
	      location.reload();
	    }, response => {
	      calendar_util.Util.displayError(response.errors);
	    });
	    this.close();
	  }
	  getSelectedSections() {
	    const sections = [];
	    this.superposedSections.forEach(section => {
	      sections.push(parseInt(section.id));
	    }, this);
	    return sections;
	  }
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$4,
	  _t3$3,
	  _t4$2,
	  _t5$1,
	  _t6$1,
	  _t7$1,
	  _t8$1,
	  _t9$1,
	  _t10,
	  _t11,
	  _t12,
	  _t13,
	  _t14,
	  _t15,
	  _t16,
	  _t17,
	  _t18,
	  _t19,
	  _t20,
	  _t21,
	  _t22,
	  _t23,
	  _t24,
	  _t25,
	  _t26,
	  _t27;
	class SectionInterface extends main_core_events.EventEmitter {
	  constructor({
	    calendarContext,
	    readonly,
	    sectionManager
	  }) {
	    super();
	    this.name = 'sectioninterface';
	    this.uid = null;
	    this.DOM = {};
	    this.SLIDER_WIDTH = 400;
	    this.SLIDER_DURATION = 80;
	    this.sliderId = "calendar:section-slider";
	    this.denyClose = false;
	    this.deletedSectionsIds = [];
	    this.setEventNamespace('BX.Calendar.SectionInterface');
	    this.sectionManager = sectionManager;
	    this.calendarContext = calendarContext;
	    this.readonly = readonly;
	    this.BX = calendar_util.Util.getBX();
	    this.deleteSectionHandlerBinded = this.deleteSectionHandler.bind(this);
	    this.refreshSectionListBinded = this.refreshSectionList.bind(this);
	    this.keyHandlerBinded = this.keyHandler.bind(this);
	    if (this.calendarContext !== null) {
	      if (this.calendarContext.util.config.accessNames) {
	        var _this$calendarContext, _this$calendarContext2, _this$calendarContext3;
	        calendar_util.Util.setAccessNames((_this$calendarContext = this.calendarContext) == null ? void 0 : (_this$calendarContext2 = _this$calendarContext.util) == null ? void 0 : (_this$calendarContext3 = _this$calendarContext2.config) == null ? void 0 : _this$calendarContext3.accessNames);
	      }
	    }
	  }
	  show() {
	    this.BX.SidePanel.Instance.open(this.sliderId, {
	      contentCallback: this.createContent.bind(this),
	      width: this.SLIDER_WIDTH,
	      animationDuration: this.SLIDER_DURATION,
	      events: {
	        onCloseByEsc: this.escHide.bind(this),
	        onClose: this.hide.bind(this),
	        onCloseComplete: this.destroy.bind(this),
	        onLoad: this.onLoadSlider.bind(this)
	      }
	    });
	    this.addEventEmitterSubscriptions();
	    main_core.Event.bind(document, 'keydown', this.keyHandlerBinded);
	  }
	  addEventEmitterSubscriptions() {
	    this.BX.Event.EventEmitter.subscribe('BX.Calendar.Section:delete', this.deleteSectionHandlerBinded);
	    this.BX.Event.EventEmitter.subscribe('BX.Calendar.Section:pull-delete', this.deleteSectionHandlerBinded);
	    this.BX.Event.EventEmitter.subscribe('BX.Calendar.Section:edit', this.refreshSectionListBinded);
	    this.BX.Event.EventEmitter.subscribe('BX.Calendar.Section:pull-reload-data', this.refreshSectionListBinded);
	  }
	  destroyEventEmitterSubscriptions() {
	    this.BX.Event.EventEmitter.unsubscribe('BX.Calendar.Section:delete', this.deleteSectionHandlerBinded);
	    this.BX.Event.EventEmitter.unsubscribe('BX.Calendar.Section:pull-delete', this.deleteSectionHandlerBinded);
	    this.BX.Event.EventEmitter.unsubscribe('BX.Calendar.Section:edit', this.refreshSectionListBinded);
	    this.BX.Event.EventEmitter.unsubscribe('BX.Calendar.Section:pull-reload-data', this.refreshSectionListBinded);
	  }
	  escHide(event) {
	    if (event && event.getSlider && event.getSlider().getUrl() === this.sliderId && this.denyClose) {
	      event.denyAction();
	    }
	  }
	  hide(event) {
	    if (event && event.getSlider && event.getSlider().getUrl() === this.sliderId) {
	      this.closeForms();
	      this.destroyEventEmitterSubscriptions();
	      main_core.Event.unbind(document, 'keydown', this.keyHandlerBinded);
	    }
	  }
	  close() {
	    BX.SidePanel.Instance.close();
	  }
	  destroy(event) {
	    if (event && event.getSlider && event.getSlider().getUrl() === this.sliderId) {
	      this.destroyEventEmitterSubscriptions();
	      main_core.Event.unbind(document, 'keydown', this.keyHandlerBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Section:delete', this.deleteSectionHandlerBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Section:pull-delete', this.deleteSectionHandlerBinded);
	      BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", BX.proxy(this.destroy, this));
	      BX.SidePanel.Instance.destroy(this.sliderId);
	      delete this.DOM.localSectionListWrap;
	      this.deletedSectionsIds = [];
	      if (this.sectionActionMenu) {
	        this.sectionActionMenu.close();
	      }
	      if (this.trackingTypesForm) {
	        delete this.trackingTypesForm;
	      }
	      if (this.trackingUsersForm) {
	        delete this.trackingUsersForm;
	      }
	      if (this.trackingGroupsForm) {
	        delete this.trackingGroupsForm;
	      }
	    }
	  }
	  createContent() {
	    this.DOM.outerWrap = main_core.Tag.render(_t$4 || (_t$4 = _$4`
			<div class="calendar-list-slider-wrap"></div>
		`));
	    this.DOM.titleWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t2$4 || (_t2$4 = _$4`
				<div class="calendar-list-slider-title-container">
					<div class="calendar-list-slider-title"> 
						${0}
					</div>
				</div>
			`), main_core.Loc.getMessage('EC_SECTION_BUTTON')));
	    const calendarContext = this.calendarContext || calendar_util.Util.getCalendarContext();
	    if (calendarContext && !this.readonly) {
	      this.DOM.sectionFormWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t3$3 || (_t3$3 = _$4`
					<div class="calendar-list-slider-card-widget calendar-list-slider-form-wrap">
						<div class="calendar-list-slider-card-widget-title">
							<span class="calendar-list-slider-card-widget-title-text">
								${0}
							</span>
						</div>
					</div>
				`), main_core.Loc.getMessage('EC_SEC_SLIDER_NEW_SECTION')));
	    }
	    if (calendarContext && !this.readonly && (!calendarContext.util.isUserCalendar() || calendarContext.util.userIsOwner())) {
	      // #1. Controls
	      this.createAddButton();

	      // #2. Forms
	      this.DOM.trackingGroupsFormWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t4$2 || (_t4$2 = _$4`
					<div class="calendar-list-slider-card-widget calendar-list-slider-form-wrap">
						<div class="calendar-list-slider-card-widget-title">
							<span class="calendar-list-slider-card-widget-title-text">
								${0}
							</span>
						</div>
					</div>								
				`), main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_GROUP')));
	      this.DOM.trackingUsersFormWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t5$1 || (_t5$1 = _$4`
					<div class="calendar-list-slider-card-widget calendar-list-slider-form-wrap">
						<div class="calendar-list-slider-card-widget-title">
							<span class="calendar-list-slider-card-widget-title-text">
								${0}
							</span>
						</div>
					</div>
				`), main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_USER')));
	      this.DOM.trackingTypesFormWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t6$1 || (_t6$1 = _$4`
					<div class="calendar-list-slider-card-widget calendar-list-slider-form-wrap">
						<div class="calendar-list-slider-card-widget-title">
							<span class="calendar-list-slider-card-widget-title-text">
								${0}
							</span>
						</div>
					</div>								
				`), main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_COMP')));
	    }

	    // #3. List of sections
	    this.createSectionList();
	    return this.DOM.outerWrap;
	  }
	  onLoadSlider(event) {
	    this.slider = event.getSlider();
	    this.sliderId = this.slider.getUrl();
	    this.DOM.content = this.slider.layout.content;
	  }
	  createSectionList() {
	    this.sliderSections = this.sectionManager.getSections().filter(section => {
	      return !this.deletedSectionsIds.find(id => id === section.id);
	    });
	    if (main_core.Type.isElementNode(this.DOM.sectonListOuterWrap)) {
	      main_core.Dom.remove(this.DOM.sectonListOuterWrap);
	    }
	    this.DOM.sectonListOuterWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t7$1 || (_t7$1 = _$4`<div></div>`)));
	    main_core.Event.bind(this.DOM.sectonListOuterWrap, 'click', this.sectionClickHandler.bind(this));
	    this.createLocalSectionsList();
	    this.createExternalSectionsList();
	  }
	  createLocalSectionsList() {
	    this.DOM.localSectionListWrap = this.DOM.sectonListOuterWrap.appendChild(this.getSectionListWrap(this.getLocalSectionListTitle()));
	    this.createSectionsBlock({
	      wrap: this.DOM.localSectionListWrap,
	      sectionList: this.sliderSections.filter(section => {
	        return section.externalTypeIsLocal() && section.belongsToView() || section.isPseudo();
	      })
	    });
	    this.createCompanySectionList();
	    this.createUsersSectionList();
	    this.createGroupsSectionList();
	  }
	  createExternalSectionsList() {
	    const externalSections = this.sliderSections.filter(section => {
	      return !section.externalTypeIsLocal() && section.belongsToView();
	    });
	    this.DOM.extSectionListWrap = [];
	    externalSections.forEach(section => {
	      const listWrap = this.getSectionListWrapForSection(section);
	      this.createSectionUnit({
	        section: section,
	        wrap: listWrap
	      });
	    });
	  }
	  getSectionListWrapForSection(section) {
	    var _sectionExternalConne;
	    let sectionExternalType = section.getExternalType();
	    if (section.isGoogle()) {
	      sectionExternalType = 'google';
	    }
	    if (section.data['IS_EXCHANGE']) {
	      sectionExternalType = 'exchange';
	    }
	    const sectionExternalConnection = calendar_sectionmanager.SectionManager.getSectionExternalConnection(section, sectionExternalType);
	    const calendarContext = this.calendarContext || calendar_util.Util.getCalendarContext();
	    section.data.CAL_DAV_CON = (sectionExternalConnection == null ? void 0 : (_sectionExternalConne = sectionExternalConnection.addParams) == null ? void 0 : _sectionExternalConne.id) || null;
	    let key = sectionExternalType + (sectionExternalConnection ? sectionExternalConnection.getId() : '-disconnected');
	    if (!main_core.Type.isElementNode(this.DOM.extSectionListWrap[key])) {
	      const sectionListWrap = this.DOM.sectonListOuterWrap.appendChild(this.getSectionListWrap(this.getExternalConnectionBlockTitle({
	        type: sectionExternalType,
	        connection: sectionExternalConnection
	      })));
	      sectionListWrap.appendChild(main_core.Tag.render(_t8$1 || (_t8$1 = _$4`
				<div class="calendar-list-slider-widget-content">
					<div class="calendar-list-slider-widget-content-block">
						${0}
					</div>
				</div>
			`), this.DOM.extSectionListWrap[key] = main_core.Tag.render(_t9$1 || (_t9$1 = _$4`<ul class="calendar-list-slider-container"/>`))));
	      if (!sectionExternalConnection && calendarContext && calendarContext.util.userIsOwner() && !section.isArchive() && (!section.isExchange() || !calendarContext.util.config.bExchange && section.isExchange())) {
	        sectionListWrap.querySelector('.calendar-list-slider-widget-content-block').appendChild(main_core.Tag.render(_t10 || (_t10 = _$4`
							<div data-bx-calendar-open-sync="Y" class="calendar-list-slider-card-widget-bottom-button">
								<span class="calendar-list-slider-link">
									${0}
								</span>
							</div>`), main_core.Loc.getMessage('EC_SEC_SLIDER_ADJUST_SYNC')));
	        sectionListWrap.querySelector('.calendar-list-slider-card-widget-title').appendChild(main_core.Tag.render(_t11 || (_t11 = _$4`
							<span class="calendar-list-slider-card-widget-title-text calendar-list-title-disabled" >
								${0}
							</span>`), main_core.Loc.getMessage('EC_SEC_SLIDER_SYNC_DISABLED')));
	      } else if (section.isArchive()) {
	        const hintNode = sectionListWrap.querySelector('.calendar-list-slider-card-widget-title').appendChild(main_core.Tag.render(_t12 || (_t12 = _$4`
						<div class="ui-icon ui-icon-common-question calendar-list-slider-archive-hint"
						data-hint="${0}">
							<i></i>	
						</div>
				`), main_core.Loc.getMessage('EC_SEC_SLIDER_TYPE_ARCHIVE_HELPER')));
	        if (main_core.Type.isDomNode(hintNode)) {
	          calendar_util.Util.initHintNode(hintNode);
	        }
	      }
	    }
	    return this.DOM.extSectionListWrap[key];
	  }
	  getExternalConnectionBlockTitle({
	    type,
	    connection
	  }) {
	    let title = '';
	    const connectionName = connection ? connection.getConnectionAccountName() || connection.getConnectionName() : null;
	    switch (type) {
	      case 'google':
	        if (connectionName) {
	          title = main_core.Loc.getMessage('EC_SEC_SLIDER_TYPE_GOOGLE', {
	            '#CONNECTION_NAME#': connectionName
	          });
	        } else {
	          title = main_core.Loc.getMessage('EC_SEC_SLIDER_TYPE_GOOGLE_DIS');
	        }
	        break;
	      case 'office365':
	        if (connectionName) {
	          title = main_core.Loc.getMessage('EC_SEC_SLIDER_TYPE_OFFICE365', {
	            '#CONNECTION_NAME#': connectionName
	          });
	        } else {
	          title = main_core.Loc.getMessage('EC_SEC_SLIDER_TYPE_OFFICE365_DIS');
	        }
	        break;
	      case 'icloud':
	        if (connectionName) {
	          title = main_core.Loc.getMessage('EC_SEC_SLIDER_TYPE_ICLOUD', {
	            '#CONNECTION_NAME#': connectionName
	          });
	        } else {
	          title = main_core.Loc.getMessage('EC_SEC_SLIDER_TYPE_ICLOUD_DIS');
	        }
	        break;
	      case 'caldav':
	        if (connectionName) {
	          if (connection.getType() === 'yandex') {
	            title = main_core.Loc.getMessage('EC_SEC_SLIDER_TYPE_YANDEX', {
	              '#CONNECTION_NAME#': connectionName
	            });
	          } else {
	            title = main_core.Loc.getMessage('EC_SEC_SLIDER_TYPE_CALDAV', {
	              '#CONNECTION_NAME#': connectionName
	            });
	          }
	        } else {
	          title = main_core.Loc.getMessage('EC_SEC_SLIDER_TYPE_DEFAULT');
	        }
	        break;
	      case 'exchange':
	        title = main_core.Loc.getMessage('EC_CAL_SYNC_EXCHANGE');
	        break;
	      case 'archive':
	        title = main_core.Loc.getMessage('EC_SEC_SLIDER_TYPE_ARCHIVE');
	        break;
	      default:
	        title = main_core.Loc.getMessage('EC_SEC_SLIDER_TYPE_DEFAULT');
	    }
	    return title;
	  }
	  createCompanySectionList() {
	    const sections = this.sliderSections.filter(function (section) {
	      return section.isCompanyCalendar() && !section.belongsToView();
	    });
	    if (sections.length > 0) {
	      this.DOM.localSectionListWrap.appendChild(main_core.Tag.render(_t13 || (_t13 = _$4`
				<div class="calendar-list-slider-card-section-title">
					<span class="calendar-list-slider-card-section-title-text">${0}</span>
				</div>
			`), main_core.Loc.getMessage('EC_SEC_SLIDER_TITLE_COMP_CAL')));
	      this.createSectionsBlock({
	        wrap: this.DOM.localSectionListWrap,
	        sectionList: this.sliderSections.filter(section => {
	          return section.isCompanyCalendar();
	        })
	      });
	    }
	  }
	  createUsersSectionList() {
	    this.calendarContext.util.getSuperposedTrackedUsers().forEach(user => {
	      const sections = this.sliderSections.filter(section => {
	        return !section.belongsToView() && section.type === 'user' && section.data.OWNER_ID === user.ID;
	      });
	      if (sections.length > 0) {
	        this.DOM.localSectionListWrap.appendChild(main_core.Tag.render(_t14 || (_t14 = _$4`
					<div class="calendar-list-slider-card-section-title">
						<span class="calendar-list-slider-card-section-title-text">${0}</span>
					</div>
				`), main_core.Text.encode(user.FORMATTED_NAME)));
	        this.createSectionsBlock({
	          wrap: this.DOM.localSectionListWrap,
	          sectionList: sections
	        });
	      }
	    }, this);
	  }
	  createGroupsSectionList() {
	    const sections = this.sliderSections.filter(section => {
	      return !section.belongsToView() && section.type === 'group';
	    });
	    if (sections.length > 0) {
	      this.DOM.localSectionListWrap.appendChild(main_core.Tag.render(_t15 || (_t15 = _$4`
				<div class="calendar-list-slider-card-section-title">
					<span class="calendar-list-slider-card-section-title-text">${0}</span>
				</div>
			`), main_core.Loc.getMessage('EC_SEC_SLIDER_TITLE_GROUP_CAL')));
	      this.createSectionsBlock({
	        wrap: this.DOM.localSectionListWrap,
	        sectionList: sections
	      });
	    }
	  }
	  getSectionListWrap(title) {
	    return main_core.Tag.render(_t16 || (_t16 = _$4`
				<div class="calendar-list-slider-card-widget">
					<div class="calendar-list-slider-card-widget-title">
						<span class="calendar-list-slider-card-widget-title-text">
							${0}
						</span>
					</div>
				</div>
			`), title);
	  }
	  getLocalSectionListTitle() {
	    if (this.sectionManager.calendarType === 'user') {
	      return main_core.Loc.getMessage('EC_SEC_SLIDER_MY_CALENDARS_LIST');
	    } else if (this.sectionManager.calendarType === 'group') {
	      return main_core.Loc.getMessage('EC_SEC_SLIDER_GROUP_CALENDARS_LIST');
	    } else {
	      return main_core.Loc.getMessage('EC_SEC_SLIDER_TYPE_CALENDARS_LIST');
	    }
	  }
	  createAddButton() {
	    if (this.calendarContext.util.config.perm && this.calendarContext.util.config.perm.edit_section) {
	      const addButtonOuter = this.DOM.titleWrap.appendChild(main_core.Tag.render(_t17 || (_t17 = _$4`
				<span class="ui-btn-split ui-btn-light-border" style="margin-right: 0"></span>
			`)));
	      this.DOM.addButton = addButtonOuter.appendChild(main_core.Tag.render(_t18 || (_t18 = _$4`
				<span class="ui-btn-main">${0}</span>
			`), main_core.Loc.getMessage('EC_ADD')));
	      this.DOM.addButtonMore = addButtonOuter.appendChild(main_core.Tag.render(_t19 || (_t19 = _$4`
				<span class="ui-btn-extra"></span>
			`)));
	      main_core.Event.bind(this.DOM.addButtonMore, 'click', this.showAddButtonPopup.bind(this));
	      main_core.Event.bind(this.DOM.addButton, 'click', this.showEditSectionForm.bind(this));
	    }
	  }
	  showAddButtonPopup() {
	    if (this.addBtnMenu && this.addBtnMenu.popupWindow && this.addBtnMenu.popupWindow.isShown()) {
	      return this.addBtnMenu.close();
	    }
	    const menuItems = [new main_popup.MenuItem({
	      text: main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_NEW_TITLE'),
	      delimiter: true
	    }), {
	      html: main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_NEW_MENU'),
	      onclick: () => {
	        this.addBtnMenu.close();
	        this.showEditSectionForm();
	      }
	    }, new main_popup.MenuItem({
	      text: main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_EXIST_TITLE'),
	      delimiter: true
	    }), {
	      html: main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_COMP'),
	      onclick: () => {
	        this.addBtnMenu.close();
	        this.showTrackingTypesForm();
	      }
	    }, {
	      html: main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_USER'),
	      onclick: () => {
	        this.addBtnMenu.close();
	        this.showTrackingUsersForm();
	      }
	    }, {
	      html: main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_GROUP'),
	      onclick: () => {
	        this.addBtnMenu.close();
	        this.showTrackingGroupsForm();
	      }
	    }];
	    this.addBtnMenu = this.BX.PopupMenu.create('add-btn-' + calendar_util.Util.getRandomInt(), this.DOM.addButtonMore, menuItems, {
	      closeByEsc: true,
	      autoHide: true,
	      zIndex: this.zIndex,
	      offsetTop: 0,
	      offsetLeft: 15,
	      angle: true,
	      cacheable: false
	    });
	    this.addBtnMenu.show();
	  }
	  createSectionsBlock({
	    sectionList,
	    wrap
	  }) {
	    if (main_core.Type.isArray(sectionList)) {
	      const listWrap = wrap.appendChild(main_core.Tag.render(_t20 || (_t20 = _$4`<div class="calendar-list-slider-widget-content"></div>`))).appendChild(main_core.Tag.render(_t21 || (_t21 = _$4`<div class="calendar-list-slider-widget-content-block"></div>`))).appendChild(main_core.Tag.render(_t22 || (_t22 = _$4`<ul class="calendar-list-slider-container"></ul>`)));
	      sectionList.forEach(section => {
	        this.createSectionUnit({
	          section,
	          wrap: listWrap
	        });
	      });
	    }
	  }
	  createSectionUnit({
	    section,
	    wrap
	  }) {
	    if (!section.DOM) {
	      section.DOM = {};
	    }
	    const sectionId = section.id.toString();
	    const li = wrap.appendChild(main_core.Tag.render(_t23 || (_t23 = _$4`
			<li class="calendar-list-slider-item" data-bx-calendar-section="${0}"></li>
		`), sectionId));
	    const checkbox = li.appendChild(main_core.Tag.render(_t24 || (_t24 = _$4`
			<div class="calendar-list-slider-item-checkbox ${0}" style="background-color: ${0}"></div>
		`), section.isShown() ? 'calendar-list-slider-item-checkbox-checked' : '', section.color));
	    const title = li.appendChild(main_core.Tag.render(_t25 || (_t25 = _$4`
			<div class="calendar-list-slider-item-name" title="${0}">${0}</div>
		`), main_core.Text.encode(section.name), main_core.Text.encode(section.name)));
	    section.DOM.item = li;
	    section.DOM.checkbox = checkbox;
	    section.DOM.title = title;
	    section.DOM.actionCont = li.appendChild(main_core.Tag.render(_t26 || (_t26 = _$4`
			<div class="calendar-list-slider-item-actions-container" data-bx-calendar-section-menu="${0}">
				<span class="calendar-list-slider-item-context-menu"></span>
			</div>
		`), sectionId));
	  }
	  sectionClickHandler(e) {
	    const target = calendar_util.Util.findTargetNode(e.target || e.srcElement, this.DOM.outerWrap);
	    if (target && target.getAttribute) {
	      if (target.getAttribute('data-bx-calendar-section-menu') !== null) {
	        let sectionId = target.getAttribute('data-bx-calendar-section-menu');
	        sectionId = sectionId === 'tasks' ? sectionId : parseInt(sectionId);
	        this.showSectionMenu(this.sectionManager.getSection(sectionId), target);
	      } else if (target.getAttribute('data-bx-calendar-section') !== null) {
	        this.switchSection(this.sectionManager.getSection(target.getAttribute('data-bx-calendar-section')));
	      } else if (target.getAttribute('data-bx-calendar-open-sync') !== null) {
	        this.calendarContext.syncInterface.openSyncPanel();
	      }
	    }
	  }
	  findCheckBoxNodes(id) {
	    return this.DOM.sectonListOuterWrap.querySelectorAll('.calendar-list-slider-item[data-bx-calendar-section=\'' + id + '\'] .calendar-list-slider-item-checkbox');
	  }
	  switchSection(section) {
	    const checkboxNodes = this.findCheckBoxNodes(section.id);
	    for (let i = 0; i < checkboxNodes.length; i++) {
	      if (section.isShown()) {
	        main_core.Dom.removeClass(checkboxNodes[i], 'calendar-list-slider-item-checkbox-checked');
	      } else {
	        main_core.Dom.addClass(checkboxNodes[i], 'calendar-list-slider-item-checkbox-checked');
	      }
	    }
	    if (section.isShown()) {
	      section.hide();
	    } else {
	      section.show();
	    }

	    // TODO: should use eventEmtter
	    this.calendarContext.reload();
	  }
	  switchOnSection(section) {
	    const checkboxNodes = this.findCheckBoxNodes(section.id);
	    for (let i = 0; i < checkboxNodes.length; i++) {
	      if (!section.isShown()) {
	        main_core.Dom.addClass(checkboxNodes[i], 'calendar-list-slider-item-checkbox-checked');
	      }
	    }
	    if (!section.isShown()) {
	      section.show();
	    }
	  }
	  switchOffSection(section) {
	    const checkboxNodes = this.findCheckBoxNodes(section.id);
	    for (let i = 0; i < checkboxNodes.length; i++) {
	      if (section.isShown()) {
	        main_core.Dom.removeClass(checkboxNodes[i], 'calendar-list-slider-item-checkbox-checked');
	      }
	    }
	    if (section.isShown()) {
	      section.hide();
	    }
	  }
	  showSectionMenu(section, menuItemNode) {
	    const menuItems = [];
	    const itemNode = menuItemNode.closest('[data-bx-calendar-section]');
	    if (main_core.Type.isElementNode(itemNode)) {
	      main_core.Dom.addClass(itemNode, 'active');
	    }
	    if (section.canDo('view_time')) {
	      menuItems.push({
	        text: main_core.Loc.getMessage('EC_SEC_LEAVE_ONE'),
	        onclick: () => {
	          this.sectionActionMenu.close();
	          this.showOnlyOneSection(section, this.sectionManager.sections);
	        }
	      });
	    }
	    if (!section.isPseudo() && section.getLink() && !section.belongsToView()) {
	      menuItems.push({
	        text: main_core.Loc.getMessage('EC_SEC_OPEN_LINK'),
	        href: section.getLink()
	      });
	    }
	    if (!this.readonly && section.canDo('edit_section') && !section.isPseudo()) {
	      menuItems.push({
	        text: main_core.Loc.getMessage('EC_SEC_EDIT'),
	        onclick: () => {
	          this.sectionActionMenu.close();
	          this.showEditSectionForm({
	            section: section
	          });
	        }
	      });
	    }
	    if (section.isSuperposed() && !section.belongsToView()) {
	      menuItems.push({
	        text: main_core.Loc.getMessage('EC_SEC_HIDE'),
	        onclick: () => {
	          this.hideSuperposedHandler(section);
	          this.sectionActionMenu.close();
	        }
	      });
	    }
	    if (section.canBeConnectedToOutlook() && section.data['EXTERNAL_TYPE'] === 'local') {
	      menuItems.push({
	        text: main_core.Loc.getMessage('EC_SEC_CONNECT_TO_OUTLOOK'),
	        onclick: () => {
	          this.sectionActionMenu.close();
	          section.connectToOutlook();
	          this.close();
	        }
	      });
	    }
	    if (!section.isPseudo() && section.data.EXPORT && section.data.EXPORT.LINK && section.data['EXTERNAL_TYPE'] === 'local') {
	      menuItems.push({
	        text: main_core.Loc.getMessage('EC_ACTION_EXPORT'),
	        onclick: () => {
	          this.sectionActionMenu.close();
	          const options = {
	            sectionLink: section.data.EXPORT.LINK,
	            calendarPath: this.calendarContext.util.config.path
	          };
	          if (calendar_sync_interface.IcalSyncPopup.checkPathes(options)) {
	            calendar_sync_interface.IcalSyncPopup.createInstance(options).show();
	          } else {
	            calendar_sync_interface.IcalSyncPopup.showPopupWithPathesError();
	          }
	        }
	      });
	    }
	    let provider = undefined;
	    let connection = undefined;
	    if (section.data.CAL_DAV_CON && section.belongsToView() && this.calendarContext.syncInterface) {
	      [provider, connection] = this.calendarContext.syncInterface.getProviderById(section.data.CAL_DAV_CON);
	    }
	    if (section.canDo('edit_section') && section.belongsToView() && !section.isPseudo() && (!section.isGoogle() && !connection || section.data['EXTERNAL_TYPE'] === 'local' || !connection)) {
	      menuItems.push({
	        text: main_core.Loc.getMessage('EC_SEC_DELETE'),
	        onclick: () => {
	          this.sectionActionMenu.close();
	          this.showSectionConfirm('delete', section);
	        }
	      });
	    }
	    if (section.canDo('edit_section') && connection) {
	      if (section.isGoogle() || section.isIcloud() || section.isOffice365() || section.isCalDav()) {
	        menuItems.push({
	          text: main_core.Loc.getMessage('EC_ACTION_EXTERNAL_ADJUST'),
	          onclick: () => {
	            this.sectionActionMenu.close();
	            if (provider) {
	              provider.openActiveConnectionSlider(connection);
	            }
	          }
	        });
	      }
	      if (section.isGoogle() || section.isIcloud() || section.isOffice365()) {
	        menuItems.push({
	          text: main_core.Loc.getMessage('EC_ACTION_HIDE'),
	          onclick: () => {
	            this.sectionActionMenu.close();
	            this.showSectionConfirm('hideSync', section);
	          }
	        });
	      } else if (section.isCalDav()) {
	        menuItems.push({
	          text: main_core.Loc.getMessage('EC_ACTION_HIDE'),
	          onclick: () => {
	            this.sectionActionMenu.close();
	            this.showSectionConfirm('hideExternal', section);
	          }
	        });
	      }
	    }
	    if (section.isPseudo() && section.taskSectionBelongToUser()) {
	      menuItems.push({
	        text: main_core.Loc.getMessage('EC_SEC_EDIT'),
	        onclick: () => {
	          this.sectionActionMenu.close();
	          this.showEditSectionForm({
	            section: section
	          });
	        }
	      });
	      menuItems.push({
	        text: main_core.Loc.getMessage('EC_SEC_TASK_HIDE'),
	        onclick: () => {
	          this.sectionActionMenu.close();
	          BX.userOptions.save('calendar', 'user_settings', 'showTasks', 'N');
	          main_core.Dom.addClass(section.DOM.item, 'calendar-list-slider-item-disappearing');
	          setTimeout(() => {
	            main_core.Dom.clean(section.DOM.item, true);
	            BX.reload();
	          }, 300);
	        }
	      });
	    }
	    if (menuItems && menuItems.length > 0) {
	      this.sectionActionMenu = top.BX.PopupMenu.create('section-menu-' + calendar_util.Util.getRandomInt(), menuItemNode, menuItems, {
	        closeByEsc: true,
	        autoHide: true,
	        zIndex: this.zIndex,
	        offsetTop: 0,
	        offsetLeft: 9,
	        angle: true,
	        cacheable: false
	      });
	      this.sectionActionMenu.show();
	      this.sectionActionMenu.popupWindow.subscribe('onClose', () => {
	        if (main_core.Type.isElementNode(itemNode)) {
	          main_core.Dom.removeClass(itemNode, 'active');
	        }
	        this.allowSliderClose();
	      });
	      this.denySliderClose();
	    }
	  }
	  denySliderClose() {
	    this.denyClose = true;
	  }
	  allowSliderClose() {
	    this.denyClose = false;
	  }
	  closeForms() {
	    if (this.addBtnMenu) {
	      this.addBtnMenu.close();
	    }
	    if (this.editSectionForm) {
	      this.editSectionForm.close();
	    }
	    if (this.trackingUsersForm) {
	      this.trackingUsersForm.close();
	    }
	    if (this.trackingGroupsForm) {
	      this.trackingGroupsForm.close();
	    }
	    if (this.trackingTypesForm) {
	      this.trackingTypesForm.close();
	    }
	  }
	  showEditSectionForm(params = {}) {
	    this.closeForms();
	    const formTitleNode = this.DOM.sectionFormWrap.querySelector('.calendar-list-slider-card-widget-title-text');
	    this.editSectionForm = new EditForm({
	      wrap: this.DOM.sectionFormWrap,
	      sectionAccessTasks: this.sectionManager.getSectionAccessTasks(),
	      sectionManager: this.sectionManager,
	      closeCallback: () => {
	        this.allowSliderClose();
	      }
	    });
	    let showAccessControl = true;
	    if (params.section && (!params.section.belongsToView() || params.section.isPseudo())) {
	      formTitleNode.innerHTML = main_core.Loc.getMessage('EC_SEC_SLIDER_EDIT_SECTION_PERSONAL');
	      showAccessControl = false;
	    } else if (params.section && params.section.id) {
	      formTitleNode.innerHTML = main_core.Loc.getMessage('EC_SEC_SLIDER_EDIT_SECTION');
	      showAccessControl = params.section.hasPermission('access');
	    } else {
	      formTitleNode.innerHTML = main_core.Loc.getMessage('EC_SEC_SLIDER_NEW_SECTION');
	    }
	    this.editSectionForm.show({
	      showAccess: showAccessControl,
	      allowChangeName: params.section ? !params.section.isPrimaryForConnection() : true,
	      section: params.section || {
	        color: calendar_util.Util.getRandomColor(),
	        access: this.sectionManager.getDefaultSectionAccess()
	      }
	    });
	    this.denySliderClose();
	  }
	  showTrackingTypesForm() {
	    this.closeForms();
	    if (!this.trackingTypesForm) {
	      this.trackingTypesForm = new TrackingTypesForm({
	        wrap: this.DOM.trackingTypesFormWrap,
	        superposedSections: this.sectionManager.getSuperposedSectionList(),
	        closeCallback: () => {
	          this.allowSliderClose();
	        }
	      });
	    }
	    this.trackingTypesForm.show();
	    this.denySliderClose();
	  }
	  showTrackingUsersForm() {
	    this.closeForms();
	    if (!this.trackingUsersForm) {
	      this.trackingUsersForm = new TrackingUsersForm({
	        wrap: this.DOM.trackingUsersFormWrap,
	        trackingUsers: this.calendarContext.util.getSuperposedTrackedUsers(),
	        superposedSections: this.sectionManager.getSuperposedSectionList(),
	        closeCallback: () => {
	          this.allowSliderClose();
	        }
	      });
	    }
	    this.trackingUsersForm.show();
	    this.denySliderClose();
	  }
	  showTrackingGroupsForm() {
	    this.closeForms();
	    if (!this.trackingGroupsForm) {
	      const superposedSections = this.sectionManager.getSuperposedSectionList();
	      const trackingGroups = this.calendarContext.util.getSuperposedTrackedGroups();
	      superposedSections.forEach(section => {
	        if (section.getType() === 'group' && !trackingGroups.includes(section.getOwnerId())) {
	          trackingGroups.push(section.getOwnerId());
	        }
	      });
	      this.trackingGroupsForm = new TrackingGroupsForm({
	        wrap: this.DOM.trackingGroupsFormWrap,
	        trackingGroups: trackingGroups,
	        superposedSections: superposedSections,
	        closeCallback: () => {
	          this.allowSliderClose();
	        }
	      });
	    }
	    this.trackingGroupsForm.show();
	    this.denySliderClose();
	  }
	  deleteSectionHandler(event) {
	    if (event && event instanceof this.BX.Event.BaseEvent) {
	      const data = event.getData();
	      const sectionId = parseInt(data.sectionId, 10);
	      this.sliderSections.forEach((section, index) => {
	        if (parseInt(section.id) === sectionId) {
	          this.sectionManager.deleteSectionHandler(sectionId);
	          this.deletedSectionsIds.push(sectionId);
	          const deleteSectionNodes = this.DOM.sectonListOuterWrap.querySelectorAll(`.calendar-list-slider-item[data-bx-calendar-section='${sectionId}']`);
	          deleteSectionNodes.forEach(node => {
	            main_core.Dom.addClass(node, 'calendar-list-slider-item-disappearing');
	          });
	          if (!section.externalTypeIsLocal()) {
	            const listWrap = this.getSectionListWrapForSection(section);
	            this.sliderSections = BX.util.deleteFromArray(this.sliderSections, index);
	            setTimeout(() => {
	              deleteSectionNodes.forEach(node => {
	                main_core.Dom.remove(node);
	              });
	              if (!listWrap.querySelector('li.calendar-list-slider-item')) {
	                main_core.Dom.remove(listWrap.closest('.calendar-list-slider-card-widget'));
	              }
	            }, 300);
	          }
	        }
	      }, this);
	      this.closeForms();
	    }
	  }
	  hideSuperposedHandler(section) {
	    const superposedSections = this.sectionManager.getSuperposedSectionList();
	    const sections = [];
	    let i;
	    for (i = 0; i < superposedSections.length; i++) {
	      if (parseInt(section.id) !== parseInt(superposedSections[i].id)) {
	        sections.push(parseInt(superposedSections[i].id));
	      }
	    }
	    BX.ajax.runAction('calendar.api.calendarajax.setTrackingSections', {
	      data: {
	        sections: sections
	      }
	    }).then(
	    // Success
	    response => {
	      BX.reload();
	    },
	    // Failure
	    response => {
	      calendar_util.Util.displayError(response.errors);
	    });
	  }
	  refreshSectionList() {
	    this.createSectionList();
	  }
	  showOnlyOneSection(section, sections) {
	    for (let curSection of sections) {
	      if (curSection.id === section.id) {
	        this.switchOnSection(curSection);
	      } else {
	        this.switchOffSection(curSection);
	      }
	    }
	    this.calendarContext.reload();
	  }
	  keyHandler(e) {
	    if (e.keyCode === calendar_util.Util.getKeyCode('enter') && this.DOM.confirmSectionPopup && this.currentConfirmMode && this.currentSection) {
	      if (this.currentConfirmMode === 'delete') {
	        this.removeSection(this.currentSection);
	      } else if (this.currentConfirmMode === 'hideSync') {
	        this.hideSyncSection(this.currentSection);
	      } else if (this.currentConfirmMode === 'hideExternal') {
	        this.hideExternalSection(this.currentSection);
	      }
	    }
	  }
	  showSectionConfirm(mode, section) {
	    this.currentSection = section;
	    this.currentConfirmMode = mode;
	    const confirmCallback = this.getConfirmCallback();
	    const okCaption = this.getOkCaption();
	    this.DOM.confirmSectionPopup = new ui_dialogs_messagebox.MessageBox({
	      message: this.getSectionConfirmContent(),
	      minHeight: 120,
	      minWidth: 280,
	      maxWidth: 300,
	      buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
	      onOk: confirmCallback,
	      onCancel: () => {
	        this.DOM.confirmSectionPopup.close();
	      },
	      okCaption: okCaption,
	      popupOptions: {
	        events: {
	          onPopupClose: () => {
	            delete this.DOM.confirmSectionPopup;
	            delete this.currentSection;
	            delete this.currentConfirmMode;
	          }
	        },
	        closeByEsc: true,
	        padding: 0,
	        contentPadding: 0,
	        animation: 'fading-slide'
	      }
	    });
	    this.DOM.confirmSectionPopup.show();
	  }
	  getConfirmCallback() {
	    if (this.currentConfirmMode === 'delete') {
	      return () => {
	        this.removeSection(this.currentSection);
	      };
	    } else if (this.currentConfirmMode === 'hideSync') {
	      return () => {
	        this.hideSyncSection(this.currentSection);
	      };
	    } else if (this.currentConfirmMode === 'hideExternal') {
	      return () => {
	        this.hideExternalSection(this.currentSection);
	      };
	    }
	  }
	  getOkCaption() {
	    if (this.currentConfirmMode === 'delete') {
	      return main_core.Loc.getMessage('EC_SEC_DELETE');
	    } else if (this.currentConfirmMode === 'hideSync' || this.currentConfirmMode === 'hideExternal') {
	      return main_core.Loc.getMessage('EC_CAL_SYNC_DISCONNECT');
	    }
	  }
	  getSectionConfirmContent() {
	    let phrase = '';
	    if (this.currentConfirmMode === 'delete') {
	      phrase = main_core.Loc.getMessage('EC_SEC_DELETE_CONFIRM');
	    } else if (this.currentConfirmMode === 'hideSync' || this.currentConfirmMode === 'hideExternal') {
	      phrase = main_core.Loc.getMessage('EC_CAL_GOOGLE_HIDE_CONFIRM');
	    }
	    return main_core.Tag.render(_t27 || (_t27 = _$4`
			<div class="calendar-list-slider-messagebox-text">${0}</div>
		`), phrase);
	  }
	  removeSection(section) {
	    section.remove();
	    this.DOM.confirmSectionPopup.close();
	  }
	  hideSyncSection(section) {
	    section.hideSyncSection();
	    this.DOM.confirmSectionPopup.close();
	  }
	  hideExternalSection(section) {
	    section.hideExternalCalendarSection();
	    this.DOM.confirmSectionPopup.close();
	  }
	}

	exports.SectionInterface = SectionInterface;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Calendar.Sync.Interface,BX.Main,BX.Event,BX.UI.EntitySelector,BX,BX.Calendar,BX.Calendar,BX.UI.Dialogs));
//# sourceMappingURL=sectioninterface.bundle.js.map
