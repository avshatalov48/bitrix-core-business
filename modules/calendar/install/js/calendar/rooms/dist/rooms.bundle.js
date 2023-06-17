this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,calendar_controls,calendar_sectioninterface,main_core_events,main_core,calendar_util,ui_entitySelector,ui_dialogs_messagebox) {
	'use strict';

	class ReserveButton extends calendar_controls.AddButton {
	  constructor(params = {}) {
	    super(params);
	    this.setEventNamespace('BX.Calendar.Rooms.ReserveButton');
	    this.zIndex = params.zIndex || 3200;
	    this.popupId = params.id || 'add-button-' + Math.round(Math.random() * 10000);
	    this.showTasks = params.showTasks;
	    this.addEntryHandler = main_core.Type.isFunction(params.addEntry) ? params.addEntry : null;
	    this.addTaskHandler = main_core.Type.isFunction(params.addTask) ? params.addTask : null;
	    this.create();
	  }
	  create() {
	    this.DOM.wrap = main_core.Dom.create('button', {
	      props: {
	        className: 'ui-btn ui-btn-success',
	        type: 'button'
	      },
	      html: main_core.Loc.getMessage('EC_RESERVE'),
	      events: {
	        click: this.addEntry.bind(this)
	      }
	    });
	  }
	}

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
	  _t8;
	class EditFormRoom extends EditForm {
	  constructor(options = {}) {
	    super(options);
	    this.setEventNamespace('BX.Calendar.Rooms.EditFormRoom');
	    this.DOM.outerWrap = options.wrap;
	    this.roomsManager = options.roomsManager;
	    this.categoryManager = options.categoryManager;
	    this.capacityNumbers = [3, 5, 7, 10, 25];
	    this.zIndex = options.zIndex || 3100;
	    this.closeCallback = options.closeCallback;
	    this.BX = calendar_util.Util.getBX();
	    this.keyHandlerBinded = this.keyHandler.bind(this);
	    this.freezeButtonsCallback = options.freezeButtonsCallback;
	  }
	  show(params = {}) {
	    this.setParams(params);
	    this.create();
	    if (this.showAccess) {
	      main_core.Dom.style(this.DOM.accessLink, 'display', null);
	      main_core.Dom.style(this.DOM.accessWrap, 'display', null);
	    } else {
	      main_core.Dom.style(this.DOM.accessLink, 'display', 'none');
	      main_core.Dom.style(this.DOM.accessWrap, 'display', 'none');
	    }
	    main_core.Event.bind(document, 'keydown', this.keyHandlerBinded);
	    main_core.Dom.addClass(this.DOM.outerWrap, 'show');
	    if (this.room) {
	      this.setInputValues(this.room);
	    }
	    this.setFocusOnInput();
	    this.isOpenedState = true;
	  }
	  setParams(params) {
	    this.actionType = params.actionType;
	    this.room = params.room;
	    this.showAccess = params.showAccess !== false;
	  }
	  setInputValues(room) {
	    if (room.color) {
	      this.setColor(room.color);
	    }
	    this.setAccess(room.access || room.data.ACCESS || {});
	    if (room.name) {
	      this.DOM.roomsTitleInput.value = room.name;
	    }
	    if (this.room.capacity) {
	      this.DOM.roomsCapacityInput.value = room.capacity;
	    }
	  }
	  setFocusOnInput() {
	    BX.focus(this.DOM.roomsTitleInput);
	    if (this.DOM.roomsTitleInput.value !== '') {
	      this.DOM.roomsTitleInput.select();
	    }
	  }
	  create() {
	    this.wrap = this.getSliderContentWrap();
	    this.DOM.formFieldsWrap = this.getFormFieldsWrap(this.wrap);
	    this.DOM.roomsTitleInput = this.createTitleInput(this.DOM.formFieldsWrap);
	    this.DOM.roomsCapacityInput = this.createCapacityInput(this.DOM.formFieldsWrap);
	    this.DOM.categorySelect = this.DOM.formFieldsWrap.appendChild(this.renderCategorySelector());
	    this.createBottomOptions(this.DOM.formFieldsWrap);
	    this.createButtons(this.DOM.formFieldsWrap);
	    this.isCreated = true;
	  }
	  getSliderContentWrap() {
	    let sliderContentWrap = this.DOM.outerWrap.querySelector('.calendar-form-content');
	    if (sliderContentWrap) {
	      main_core.Dom.clean(sliderContentWrap);
	    } else {
	      sliderContentWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t$1 || (_t$1 = _$1`
					<div class="calendar-form-content"></div>
				`)));
	    }
	    return sliderContentWrap;
	  }
	  getFormFieldsWrap(wrap) {
	    return wrap.appendChild(main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
				<div class="calendar-list-slider-widget-content"></div>
			`))).appendChild(main_core.Tag.render(_t3$1 || (_t3$1 = _$1`
				<div class="calendar-list-slider-widget-content-block"></div>
			`)));
	  }
	  createTitleInput(wrap) {
	    return wrap.appendChild(main_core.Tag.render(_t4$1 || (_t4$1 = _$1`
				<div class="calendar-field-container calendar-field-container-string">
					<div class="calendar-field-block">
						<input type="text" placeholder="${0}" 
							class="calendar-field calendar-field-string"
						/>
					</div>
				</div>
		`), main_core.Loc.getMessage('EC_SEC_SLIDER_SECTION_TITLE'))).querySelector('.calendar-field');
	  }
	  createCapacityInput(wrap) {
	    return wrap.appendChild(main_core.Tag.render(_t5 || (_t5 = _$1`
				<div class="calendar-field-container calendar-field-container-string">
					<div class="calendar-field-block">
						<div class ="calendar-list-slider-card-widget-title" style="margin-bottom: 0">
							<span class="calendar-list-slider-card-widget-title-text">
								${0}
							</span>
							<input type="number" class="calendar-field calendar-field-number" placeholder="0"/>
						</div>
					</div>
				</div>
		`), main_core.Loc.getMessage('EC_SEC_SLIDER_SECTION_CAPACITY'))).querySelector('.calendar-field');
	  }
	  renderCategorySelector() {
	    const categorySelector = this.renderCategorySelectorWrap();
	    this.categoryTagSelector = this.createCategoryTagSelector();
	    this.categoryTagSelector.renderTo(categorySelector.querySelector('.calendar-list-slider-card-widget-title'));
	    if (this.categoryTagSelector.isRendered()) {
	      this.onAfterCategorySelectorRender();
	    }
	    return categorySelector;
	  }
	  renderCategorySelectorWrap() {
	    return main_core.Tag.render(_t6 || (_t6 = _$1`
			<div class="calendar-field-container calendar-field-container-string calendar-field-container-rooms">
				<div class="calendar-field-block">
					<div class ="calendar-list-slider-card-widget-title">
						<span class="calendar-list-slider-card-widget-title-text">
							${0}
						</span>
					</div>
				</div>
			</div>
		`), main_core.Loc.getMessage('EC_SEC_SLIDER_ROOM_CATEGORY'));
	  }
	  createCategoryTagSelector() {
	    let preparedCategories = [];
	    preparedCategories = this.prepareCategoriesForDialog(this.categoryManager.getCategories());
	    this.selectedCategory = null;
	    if (this.room && this.room.categoryId) {
	      this.selectedCategory = this.prepareCategoriesForDialog([this.categoryManager.getCategory(this.room.categoryId)]);
	    }
	    return new ui_entitySelector.TagSelector({
	      placeholder: main_core.Loc.getMessage('EC_SEC_SLIDER_CATEGORY_SELECTOR_PLACEHOLDER'),
	      textBoxWidth: 320,
	      multiple: false,
	      events: {
	        onTagAdd: () => {
	          const itemsContainer = this.categoryTagSelector.getItemsContainer();
	          main_core.Dom.addClass(itemsContainer, 'calendar-room-form-category-selector-container-with-change-button');
	        },
	        onTagRemove: () => {
	          const itemsContainer = this.categoryTagSelector.getItemsContainer();
	          main_core.Dom.removeClass(itemsContainer, 'calendar-room-form-category-selector-container-with-change-button');
	        }
	      },
	      dialogOptions: {
	        context: 'CALENDAR_CONTEXT',
	        width: 315,
	        height: 280,
	        compactView: true,
	        showAvatars: false,
	        dropdownMode: true,
	        tabs: [{
	          id: 'category',
	          title: 'categories',
	          itemOrder: {
	            title: 'asc'
	          },
	          icon: 'none',
	          stubOptions: {
	            title: main_core.Loc.getMessage('EC_SEC_SLIDER_CATEGORY_SELECTOR_STUB')
	          }
	        }],
	        items: preparedCategories,
	        selectedItems: this.selectedCategory
	      }
	    });
	  }
	  onAfterCategorySelectorRender() {
	    //make avatar containers in input smaller and hide tab icon
	    main_core.Dom.addClass(this.categoryTagSelector.getDialog().getContainer(), 'calendar-room-form-category-selector-dialog');

	    //make entity selector input style similar to other inputs in room slider
	    main_core.Dom.addClass(this.categoryTagSelector.getOuterContainer(), 'calendar-field-tag-selector-outer-container');
	    main_core.Dom.addClass(this.categoryTagSelector.getTextBox(), 'calendar-field-tag-selector-text-box');
	    if (this.selectedCategory !== null) {
	      const itemsContainer = this.categoryTagSelector.getItemsContainer();
	      main_core.Dom.addClass(itemsContainer, 'calendar-room-form-category-selector-container-with-change-button');
	    }
	  }
	  createBottomOptions(wrap) {
	    this.DOM.optionsWrap = wrap.appendChild(main_core.Tag.render(_t7 || (_t7 = _$1`
			<div class="calendar-list-slider-new-calendar-options-container"></div>`)));
	    this.initSectionColorSelector();
	    this.initAccessController();
	  }
	  createButtons(wrap) {
	    this.buttonsWrap = wrap.appendChild(main_core.Tag.render(_t8 || (_t8 = _$1`
				<div class="calendar-list-slider-btn-container"></div>
			`)));
	    if (this.actionType === 'createRoom') {
	      this.renderCreateButton(this.buttonsWrap);
	    } else if (this.actionType === 'updateRoom') {
	      this.renderUpdateButton(this.buttonsWrap);
	    }
	    this.renderCancelButton(this.buttonsWrap);
	  }
	  renderCreateButton(wrap) {
	    this.saveBtn = new BX.UI.Button({
	      text: main_core.Loc.getMessage('EC_SEC_SLIDER_SAVE'),
	      className: 'ui-btn ui-btn-success',
	      events: {
	        click: this.createRoom.bind(this)
	      }
	    });
	    this.saveBtn.renderTo(wrap);
	  }
	  renderUpdateButton(wrap) {
	    this.saveBtn = new BX.UI.Button({
	      text: main_core.Loc.getMessage('EC_SEC_SLIDER_SAVE'),
	      className: 'ui-btn ui-btn-success',
	      events: {
	        click: this.updateRoom.bind(this)
	      }
	    });
	    this.saveBtn.renderTo(wrap);
	  }
	  renderCancelButton(wrap) {
	    new BX.UI.Button({
	      text: main_core.Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
	      className: 'ui-btn ui-btn-link',
	      events: {
	        click: this.checkClose.bind(this)
	      }
	    }).renderTo(wrap);
	  }
	  createRoom() {
	    if (this.freezeButtonsCallback) {
	      this.freezeButtonsCallback();
	    }
	    this.saveBtn.setWaiting(true);
	    this.roomsManager.createRoom({
	      name: this.DOM.roomsTitleInput.value,
	      capacity: this.DOM.roomsCapacityInput.value,
	      color: this.color,
	      access: this.access,
	      categoryId: this.getSelectedCategory()
	    }).then(() => {
	      this.saveBtn.setWaiting(false);
	      this.close();
	    });
	  }
	  initAccessController() {
	    this.buildAccessController();
	    this.initDialogStandard();
	    this.initAccessSelectorPopup();
	  }
	  updateRoom() {
	    if (this.freezeButtonsCallback) {
	      this.freezeButtonsCallback();
	    }
	    this.saveBtn.setWaiting(true);
	    this.roomsManager.updateRoom({
	      id: this.room.id,
	      location_id: this.room.location_id,
	      name: this.DOM.roomsTitleInput.value,
	      capacity: this.DOM.roomsCapacityInput.value,
	      color: this.color,
	      access: this.access,
	      categoryId: this.getSelectedCategory()
	    }).then(() => {
	      this.saveBtn.setWaiting(false);
	      this.close();
	    });
	  }
	  keyHandler(e) {
	    if (this.categoryTagSelector.getDialog().isOpen()) {
	      return;
	    }
	    if (e.keyCode === calendar_util.Util.getKeyCode('escape')) {
	      this.checkClose();
	    } else if (e.keyCode === calendar_util.Util.getKeyCode('enter') && this.actionType === 'createRoom') {
	      this.createRoom();
	    } else if (e.keyCode === calendar_util.Util.getKeyCode('enter') && this.actionType === 'updateRoom') {
	      this.updateRoom();
	    }
	  }
	  prepareCategoriesForDialog(categories) {
	    return categories.map(category => {
	      return {
	        id: category.id,
	        entityId: 'category',
	        title: category.name,
	        tabs: 'category'
	      };
	    });
	  }
	  getSelectedCategory() {
	    const item = this.categoryTagSelector.getDialog().getSelectedItems()[0];
	    return item ? item.id : null;
	  }
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$2,
	  _t3$2,
	  _t4$2,
	  _t5$1,
	  _t6$1;
	class EditFormCategory extends EditForm {
	  constructor(options = {}) {
	    super(options);
	    this.setEventNamespace('BX.Calendar.Rooms.EditFormCategory');
	    this.DOM.outerWrap = options.wrap;
	    this.categoryManager = options.categoryManager;
	    this.zIndex = options.zIndex || 3100;
	    this.closeCallback = options.closeCallback;
	    this.BX = calendar_util.Util.getBX();
	    this.keyHandlerBinded = this.keyHandler.bind(this);
	    this.preparedSelectedRooms = [];
	    this.freezeButtonsCallback = options.freezeButtonsCallback;
	  }
	  show(params = {}) {
	    this.setParams(params);
	    if (this.category && this.category.rooms) {
	      this.preparedSelectedRooms = this.prepareRoomsForDialog(this.category.rooms);
	    }
	    this.create();
	    main_core.Event.bind(document, 'keydown', this.keyHandlerBinded);
	    main_core.Dom.addClass(this.DOM.outerWrap, 'show');
	    if (this.category) {
	      this.setInputValues(this.category);
	    }
	    this.setFocusOnInput();
	    this.isOpenedState = true;
	  }
	  setParams(params) {
	    this.actionType = params.actionType;
	    this.category = params.category;
	  }
	  setInputValues() {
	    if (this.category.name) {
	      this.DOM.categoryTitleInput.value = this.category.name;
	    }
	  }
	  setFocusOnInput() {
	    BX.focus(this.DOM.categoryTitleInput);
	    if (this.DOM.categoryTitleInput.value !== '') {
	      this.DOM.categoryTitleInput.select();
	    }
	  }
	  create(params) {
	    this.wrap = this.getSliderContentWrap();
	    this.DOM.formFieldsWrap = this.getFormFieldsWrap(this.wrap);
	    this.DOM.categoryTitleInput = this.createTitleInput(this.DOM.formFieldsWrap);
	    this.DOM.locationSelector = this.DOM.formFieldsWrap.appendChild(this.renderRoomSelector());
	    this.createButtons(this.DOM.formFieldsWrap);
	    this.isCreated = true;
	  }
	  getSliderContentWrap() {
	    let sliderContentWrap = this.DOM.outerWrap.querySelector('.calendar-form-content');
	    if (sliderContentWrap) {
	      main_core.Dom.clean(sliderContentWrap);
	    } else {
	      sliderContentWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t$2 || (_t$2 = _$2`
					<div class="calendar-form-content"></div>
				`)));
	    }
	    return sliderContentWrap;
	  }
	  getFormFieldsWrap(wrap) {
	    return wrap.appendChild(main_core.Tag.render(_t2$2 || (_t2$2 = _$2`
				<div class="calendar-list-slider-widget-content"></div>
			`))).appendChild(main_core.Tag.render(_t3$2 || (_t3$2 = _$2`
				<div class="calendar-list-slider-widget-content-block"></div>
			`)));
	  }
	  createTitleInput(wrap) {
	    return wrap.appendChild(main_core.Tag.render(_t4$2 || (_t4$2 = _$2`
				<div class="calendar-field-container calendar-field-container-string">
					<div class="calendar-field-block">
						<input type="text" placeholder="${0}" 
							class="calendar-field calendar-field-string"
						/>
					</div>
				</div>
		`), main_core.Loc.getMessage('EC_SEC_SLIDER_SECTION_TITLE'))).querySelector('.calendar-field');
	  }
	  renderRoomSelector() {
	    const roomSelector = this.renderRoomSelectorWrap();
	    this.roomTagSelector = this.createRoomTagSelector();
	    this.roomTagSelector.renderTo(roomSelector.querySelector('.calendar-list-slider-card-widget-title'));
	    if (this.roomTagSelector.isRendered()) {
	      this.onAfterRoomSelectorRender();
	    }
	    return roomSelector;
	  }
	  renderRoomSelectorWrap() {
	    return main_core.Tag.render(_t5$1 || (_t5$1 = _$2`
				<div class="calendar-field-container calendar-field-container-string">
					<div class="calendar-field-block" >
						<div class ="calendar-list-slider-card-widget-title" style="border: none">
							<span class="calendar-list-slider-card-widget-title-text">
								${0}
							</span>
						</div>
					</div>
				</div>
		`), main_core.Loc.getMessage('EC_SEC_SLIDER_ROOM_SELECTOR'));
	  }
	  createRoomTagSelector() {
	    return new ui_entitySelector.TagSelector({
	      placeholder: main_core.Loc.getMessage('EC_SEC_SLIDER_ROOM_SELECTOR_PLACEHOLDER'),
	      textBoxWidth: 320,
	      dialogOptions: {
	        context: 'CALENDAR_CONTEXT',
	        width: 315,
	        height: 280,
	        compactView: true,
	        showAvatars: true,
	        dropdownMode: true,
	        preload: true,
	        entities: [{
	          id: 'room',
	          dynamicLoad: true,
	          filters: [{
	            id: 'calendar.roomFilter'
	          }]
	        }],
	        selectedItems: this.preparedSelectedRooms,
	        tabs: [{
	          id: 'room',
	          title: 'rooms',
	          itemOrder: {
	            title: 'asc'
	          },
	          icon: 'none',
	          stubOptions: {
	            title: main_core.Loc.getMessage('EC_SEC_SLIDER_ROOM_SELECTOR_STUB')
	          }
	        }]
	      }
	    });
	  }
	  onAfterRoomSelectorRender() {
	    //make avatar containers in input smaller and hide tab icon
	    main_core.Dom.addClass(this.roomTagSelector.getDialog().getContainer(), 'calendar-category-form-room-selector-dialog');
	    main_core.Dom.addClass(this.roomTagSelector.getContainer(), 'calendar-category-form-room-tag-selector');

	    //make entity selector input style similar to other inputs in room slider
	    main_core.Dom.addClass(this.roomTagSelector.getOuterContainer(), 'calendar-field-tag-selector-outer-container');
	    main_core.Dom.addClass(this.roomTagSelector.getTextBox(), 'calendar-field-tag-selector-text-box');
	  }
	  createButtons(wrap) {
	    this.buttonsWrap = wrap.appendChild(main_core.Tag.render(_t6$1 || (_t6$1 = _$2`
				<div class="calendar-list-slider-btn-container"></div>
			`)));
	    if (this.actionType === 'createCategory') {
	      this.renderCreateButton(this.buttonsWrap);
	    } else if (this.actionType === 'updateCategory') {
	      this.renderUpdateButton(this.buttonsWrap);
	    }
	    this.renderCancelButton(this.buttonsWrap);
	  }
	  renderCreateButton(wrap) {
	    this.saveBtn = new BX.UI.Button({
	      text: main_core.Loc.getMessage('EC_SEC_SLIDER_SAVE'),
	      className: 'ui-btn ui-btn-success',
	      events: {
	        click: this.createCategory.bind(this)
	      }
	    });
	    this.saveBtn.renderTo(wrap);
	  }
	  renderUpdateButton(wrap) {
	    this.saveBtn = new BX.UI.Button({
	      text: main_core.Loc.getMessage('EC_SEC_SLIDER_SAVE'),
	      className: 'ui-btn ui-btn-success',
	      events: {
	        click: this.updateCategory.bind(this)
	      }
	    });
	    this.saveBtn.renderTo(wrap);
	  }
	  renderCancelButton(wrap) {
	    new BX.UI.Button({
	      text: main_core.Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
	      className: 'ui-btn ui-btn-link',
	      events: {
	        click: this.checkClose.bind(this)
	      }
	    }).renderTo(wrap);
	  }
	  createCategory() {
	    if (this.freezeButtonsCallback) {
	      this.freezeButtonsCallback();
	    }
	    this.saveBtn.setWaiting(true);
	    const selectedRooms = this.getSelectedRooms();
	    this.categoryManager.createCategory({
	      name: this.DOM.categoryTitleInput.value,
	      rooms: selectedRooms
	    }).then(() => {
	      this.saveBtn.setWaiting(false);
	      this.close();
	    });
	  }
	  updateCategory() {
	    if (this.freezeButtonsCallback) {
	      this.freezeButtonsCallback();
	    }
	    const newSelectedRooms = this.prepareRoomsBeforeUpdate(this.getSelectedRooms());
	    const oldSelectedRooms = this.prepareRoomsBeforeUpdate(this.preparedSelectedRooms);
	    const toAddCategory = newSelectedRooms.filter(x => !oldSelectedRooms.includes(x));
	    const toRemoveCategory = oldSelectedRooms.filter(x => !newSelectedRooms.includes(x));
	    this.saveBtn.setWaiting(true);
	    this.categoryManager.updateCategory({
	      toAddCategory,
	      toRemoveCategory,
	      id: this.category.id,
	      name: this.DOM.categoryTitleInput.value
	    }).then(() => {
	      this.saveBtn.setWaiting(false);
	      this.close();
	    });
	  }
	  getSelectedRooms() {
	    const items = this.roomTagSelector.getDialog().getSelectedItems();
	    const rooms = [];
	    items.map(item => rooms.push(item.id));
	    return rooms;
	  }
	  keyHandler(e) {
	    if (this.roomTagSelector.getDialog().isOpen()) {
	      return;
	    }
	    if (e.keyCode === calendar_util.Util.getKeyCode('escape')) {
	      this.checkClose();
	    } else if (e.keyCode === calendar_util.Util.getKeyCode('enter') && this.actionType === 'createCategory') {
	      this.createCategory();
	    } else if (e.keyCode === calendar_util.Util.getKeyCode('enter') && this.actionType === 'updateCategory') {
	      this.updateCategory();
	    }
	  }
	  prepareRoomsForDialog(rooms) {
	    return rooms.map(room => {
	      return {
	        id: room.id,
	        entityId: 'room',
	        title: room.name,
	        avatarOptions: {
	          'bgColor': room.color,
	          'bgSize': '22px',
	          'bgImage': 'none'
	        },
	        tabs: 'room'
	      };
	    });
	  }
	  prepareRoomsBeforeUpdate(rooms) {
	    if (!rooms) {
	      return [];
	    }
	    return rooms.map(room => {
	      if (room.id) {
	        return parseInt(room.id, 10);
	      }
	      return parseInt(room, 10);
	    });
	  }
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$3,
	  _t3$3,
	  _t4$3,
	  _t5$2,
	  _t6$2,
	  _t7$1,
	  _t8$1,
	  _t9,
	  _t10,
	  _t11,
	  _t12,
	  _t13,
	  _t14,
	  _t15,
	  _t16,
	  _t17,
	  _t18,
	  _t19;
	class RoomsInterface extends calendar_sectioninterface.SectionInterface {
	  constructor({
	    calendarContext,
	    readonly,
	    roomsManager,
	    categoryManager,
	    isConfigureList = false
	  }) {
	    super({
	      calendarContext,
	      readonly,
	      roomsManager
	    });
	    this.SLIDER_WIDTH = 400;
	    this.SLIDER_DURATION = 80;
	    this.sliderId = "calendar:rooms-slider";
	    this.CATEGORY_ROOMS_SHOWN_ALL = 0;
	    this.CATEGORY_ROOMS_SHOWN_SOME = 1;
	    this.CATEGORY_ROOMS_SHOWN_NONE = 2;
	    this.setEventNamespace('BX.Calendar.RoomsInterface');
	    this.roomsManager = roomsManager;
	    this.categoryManager = categoryManager;
	    this.isConfigureList = isConfigureList;
	    this.calendarContext = calendarContext;
	    this.readonly = readonly;
	    this.BX = calendar_util.Util.getBX();
	    this.sliderOnClose = this.hide.bind(this);
	    this.deleteRoomHandlerBinded = this.deleteRoomHandler.bind(this);
	    this.refreshRoomsBinded = this.refreshRooms.bind(this);
	    this.refreshCategoriesBinded = this.refreshCategories.bind(this);
	    if (this.calendarContext !== null) {
	      if (this.calendarContext.util.config.accessNames) {
	        var _this$calendarContext, _this$calendarContext2, _this$calendarContext3;
	        calendar_util.Util.setAccessNames((_this$calendarContext = this.calendarContext) == null ? void 0 : (_this$calendarContext2 = _this$calendarContext.util) == null ? void 0 : (_this$calendarContext3 = _this$calendarContext2.config) == null ? void 0 : _this$calendarContext3.accessNames);
	      }
	    }
	    this.setRoomsFromManager();
	    this.setCategoriesFromManager();
	  }
	  addEventEmitterSubscriptions() {
	    calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms:create', this.refreshRoomsBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms:update', this.refreshRoomsBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms:delete', this.deleteRoomHandlerBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms:pull-create', this.refreshRoomsBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms:pull-update', this.refreshRoomsBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms:pull-delete', this.deleteRoomHandlerBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms.Categories:create', this.refreshCategoriesBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms.Categories:update', this.refreshCategoriesBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms.Categories:delete', this.refreshCategoriesBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms.Categories:pull-create', this.refreshCategoriesBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms.Categories:pull-update', this.refreshCategoriesBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms.Categories:pull-delete', this.refreshCategoriesBinded);
	  }
	  destroyEventEmitterSubscriptions() {
	    calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms:create', this.refreshRoomsBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms:update', this.refreshRoomsBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms:delete', this.deleteRoomHandlerBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms:pull-create', this.refreshRoomsBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms:pull-update', this.refreshRoomsBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms:pull-delete', this.deleteRoomHandlerBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms.Categories:create', this.refreshCategoriesBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms.Categories:update', this.refreshCategoriesBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms.Categories:delete', this.refreshCategoriesBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms.Categories:pull-create', this.refreshCategoriesBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms.Categories:pull-update', this.refreshCategoriesBinded);
	    calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms.Categories:pull-delete', this.refreshCategoriesBinded);
	  }
	  createContent() {
	    this.DOM.outerWrap = this.renderOuterWrap();
	    this.DOM.titleWrap = this.DOM.outerWrap.appendChild(this.renderTitleWrap());
	    if (!this.readonly) {
	      // #1. Controls
	      this.DOM.addButton = this.DOM.titleWrap.appendChild(this.renderAddButton());

	      // #2. Forms
	      this.DOM.roomFormWrap = this.DOM.outerWrap.appendChild(this.renderRoomFormWrap());
	    }
	    this.createRoomBlocks();
	    return this.DOM.outerWrap;
	  }
	  renderOuterWrap() {
	    return main_core.Tag.render(_t$3 || (_t$3 = _$3`
				<div class="calendar-list-slider-wrap"></div>
			`));
	  }
	  renderTitleWrap() {
	    return main_core.Tag.render(_t2$3 || (_t2$3 = _$3`
				<div class="calendar-list-slider-title-container">
					<div class="calendar-list-slider-title">${0}</div>
				</div>
			`), main_core.Loc.getMessage('EC_SECTION_ROOMS'));
	  }
	  renderAddButton() {
	    return main_core.Tag.render(_t3$3 || (_t3$3 = _$3`
				<span class="ui-btn-split ui-btn-light-border" style="margin-right: 0">
					<span class="ui-btn-main" onclick="${0}">
						${0}
					</span>
					<span id = "add-menu-button" class="ui-btn-menu" onclick="${0}"></span>
				</span>
		`), this.showEditRoomForm.bind(this), main_core.Loc.getMessage('EC_ADD'), this.showAddMenu.bind(this));
	  }
	  renderRoomFormWrap() {
	    return main_core.Tag.render(_t4$3 || (_t4$3 = _$3`
				<div class="calendar-list-slider-card-widget calendar-list-slider-form-wrap">
					<div class="calendar-list-slider-card-widget-title">
						<span class="calendar-list-slider-card-widget-title-text">${0}</span>
					</div>
				</div>
		`), main_core.Loc.getMessage('EC_SEC_SLIDER_NEW_ROOM'));
	  }
	  showAddMenu() {
	    const menuButtons = this.createAddMenuButtons();
	    if (menuButtons && menuButtons.length > 0) {
	      this.addRoomMenu = this.createAddMenu(menuButtons);
	      this.addRoomMenu.popupWindow.show();
	      this.addRoomMenu.popupWindow.subscribe('onClose', () => {
	        this.allowSliderClose();
	      });
	      this.denySliderClose();
	    }
	  }
	  createAddMenuButtons() {
	    const menuButtons = [];
	    menuButtons.push({
	      text: main_core.Loc.getMessage('EC_ADD_LOCATION'),
	      onclick: () => {
	        this.addRoomMenu.close();
	        this.showEditRoomForm();
	      }
	    });
	    menuButtons.push({
	      text: main_core.Loc.getMessage('EC_ADD_CATEGORY'),
	      onclick: () => {
	        this.addRoomMenu.close();
	        this.showEditCategoryForm();
	      }
	    });
	    return menuButtons;
	  }
	  createAddMenu(menuButtons) {
	    const params = {
	      offsetLeft: 20,
	      closeByEsc: true,
	      angle: {
	        position: 'top'
	      },
	      autoHide: true,
	      offsetTop: 0,
	      cacheable: false
	    };
	    return new BX.PopupMenuWindow('add-menu-form-' + calendar_util.Util.getRandomInt(), BX("add-menu-button"), menuButtons, params);
	  }
	  createRoomBlocks() {
	    this.setBlocksWrap();
	    if (main_core.Type.isArray(this.rooms) || main_core.Type.isObject(this.categories)) {
	      this.categories['categories'].forEach(category => {
	        if (category.rooms.length !== 0) {
	          this.createCategoryBlock(category, this.createBlockWrap(this.DOM.blocksWrap));
	        }
	      });
	      if (this.categories['default'].length > 0) {
	        let defaultBlockWrap = this.createBlockWrap(this.DOM.blocksWrap);
	        this.categories['default'].forEach(room => this.createRoomBlock(room, defaultBlockWrap));
	      }
	      this.categories['categories'].forEach(category => {
	        if (category.rooms.length === 0 && this.categoryManager.canDo('edit')) {
	          this.createCategoryBlock(category, this.createBlockWrap(this.DOM.blocksWrap));
	        }
	      });
	    }
	    if (this.isFrozen()) {
	      this.unfreezeButtons();
	    }
	  }
	  setRoomsFromManager() {
	    this.rooms = this.roomsManager.getRooms().filter(function (room) {
	      return room.belongsToView() || room.isPseudo();
	    });
	  }
	  setCategoriesFromManager() {
	    this.categories = this.categoryManager.getCategoriesWithRooms(this.rooms);
	  }
	  setBlocksWrap() {
	    if (this.DOM.blocksWrap) {
	      main_core.Dom.clean(this.DOM.blocksWrap);
	      main_core.Dom.adjust(this.DOM.blocksWrap, {
	        props: {
	          className: ''
	        }
	      });
	    } else {
	      this.DOM.blocksWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t5$2 || (_t5$2 = _$3`
					<div></div>
				`)));
	    }
	  }
	  showEditRoomForm(params = {}) {
	    if (typeof params.actionType === 'undefined') {
	      params.actionType = 'createRoom';
	    }
	    this.closeForms();
	    const formTitleNode = this.DOM.roomFormWrap.querySelector('.calendar-list-slider-card-widget-title-text');
	    this.editSectionForm = new EditFormRoom({
	      wrap: this.DOM.roomFormWrap,
	      sectionAccessTasks: this.roomsManager.getSectionAccessTasks(),
	      roomsManager: this.roomsManager,
	      categoryManager: this.categoryManager,
	      freezeButtonsCallback: this.freezeButtons.bind(this),
	      closeCallback: () => {
	        this.allowSliderClose();
	      }
	    });
	    let showAccess = true;
	    if (params.room && params.room.id) {
	      formTitleNode.innerHTML = main_core.Loc.getMessage('EC_SEC_SLIDER_EDIT_SECTION_ROOM');
	      showAccess = params.room.canDo('access');
	    } else {
	      formTitleNode.innerHTML = main_core.Loc.getMessage('EC_SEC_SLIDER_NEW_ROOM');
	    }
	    this.editSectionForm.show({
	      showAccess,
	      room: params.room || {
	        color: calendar_util.Util.getRandomColor(),
	        access: this.roomsManager.getDefaultSectionAccess()
	      },
	      actionType: params.actionType
	    });
	    this.denySliderClose();
	  }
	  showEditCategoryForm(params = {}) {
	    if (typeof params.actionType === 'undefined') {
	      params.actionType = 'createCategory';
	    }
	    this.closeForms();
	    const formTitleNode = this.DOM.roomFormWrap.querySelector('.calendar-list-slider-card-widget-title-text');
	    this.editSectionForm = new EditFormCategory({
	      wrap: this.DOM.roomFormWrap,
	      sectionAccessTasks: this.roomsManager.getSectionAccessTasks(),
	      categoryManager: this.categoryManager,
	      freezeButtonsCallback: this.freezeButtons.bind(this),
	      closeCallback: () => {
	        this.allowSliderClose();
	      }
	    });
	    if (params.category && params.category.id) {
	      formTitleNode.innerHTML = main_core.Loc.getMessage('EC_SEC_SLIDER_EDIT_ROOM_CATEGORY');
	    } else {
	      formTitleNode.innerHTML = main_core.Loc.getMessage('EC_SEC_SLIDER_NEW_CATEGORY');
	    }
	    this.editSectionForm.show({
	      category: params.category || {},
	      actionType: params.actionType
	    });
	    this.denySliderClose();
	  }
	  showRoomMenu(room, menuItemNode) {
	    const itemNode = menuItemNode.closest('[data-bx-calendar-section]') || menuItemNode.closest('[ data-bx-calendar-section-without-action]');
	    if (main_core.Type.isElementNode(itemNode)) {
	      main_core.Dom.addClass(itemNode, 'active');
	    }
	    const menuItems = this.createRoomMenuButtons(room);
	    if (menuItems && menuItems.length > 0) {
	      this.roomActionMenu = this.createRoomMenu(menuItems, menuItemNode);
	      this.roomActionMenu.show();
	      this.roomActionMenu.popupWindow.subscribe('onClose', () => {
	        if (main_core.Type.isElementNode(itemNode)) {
	          main_core.Dom.removeClass(itemNode, 'active');
	        }
	        this.allowSliderClose();
	      });
	      this.denySliderClose();
	    } else {
	      main_core.Dom.removeClass(itemNode, 'active');
	    }
	  }
	  createRoomMenuButtons(room) {
	    const menuItems = [];
	    if (room.canDo('view_time') && !this.isConfigureList) {
	      menuItems.push({
	        text: main_core.Loc.getMessage('EC_SEC_LEAVE_ONE_ROOM'),
	        onclick: () => {
	          this.roomActionMenu.close();
	          this.showOnlyOneSection(room, this.roomsManager.rooms);
	          this.updateAllCategoriesCheckboxState();
	        }
	      });
	    }
	    if (!this.readonly && room.canDo('edit_section')) {
	      menuItems.push({
	        text: main_core.Loc.getMessage('EC_SEC_EDIT'),
	        onclick: () => {
	          this.roomActionMenu.close();
	          this.showEditRoomForm({
	            room: room,
	            actionType: 'updateRoom'
	          });
	        }
	      });
	    }
	    if (room.canDo('edit_section') && room.belongsToView()) {
	      menuItems.push({
	        text: main_core.Loc.getMessage('EC_SEC_DELETE'),
	        onclick: () => {
	          this.roomActionMenu.close();
	          this.showRoomDeleteConfirm(room);
	          // this.deleteRoom(room);
	        }
	      });
	    }

	    return menuItems;
	  }
	  createRoomMenu(menuItems, menuItemNode) {
	    const params = {
	      closeByEsc: true,
	      autoHide: true,
	      zIndex: this.zIndex,
	      offsetTop: 0,
	      offsetLeft: 9,
	      angle: true,
	      cacheable: false
	    };
	    return top.BX.PopupMenu.create('section-menu-' + calendar_util.Util.getRandomInt(), menuItemNode, menuItems, params);
	  }
	  refreshRooms() {
	    this.setRoomsFromManager();
	    this.setCategoriesFromManager();
	    this.createRoomBlocks();
	  }
	  refreshCategories() {
	    this.roomsManager.reloadRoomsFromDatabase().then(this.refreshRoomsBinded);
	  }
	  createBlockWrap(wrap) {
	    const listWrap = wrap.appendChild(main_core.Tag.render(_t6$2 || (_t6$2 = _$3`
					<div class="calendar-list-slider-card-widget calendar-list-slider-category-widget">
						<div class="calendar-list-slider-widget-content">
							<div class="calendar-list-slider-widget-content-block">
								<ul class="calendar-list-slider-container"></ul>
							</div>
						</div>
					</div>
				`))).querySelector('.calendar-list-slider-container');
	    main_core.Event.bind(listWrap, 'click', this.roomClickHandler.bind(this));
	    return listWrap;
	  }
	  createCategoryBlock(category, listWrap) {
	    if (!category.DOM) {
	      category.DOM = {};
	    }
	    category.DOM.item = listWrap.appendChild(this.renderCategoryBlockWrap(category));
	    const categoryRooms = this.categoryManager.getCategoryRooms(category, this.rooms);
	    if (!this.isConfigureList && categoryRooms.length) {
	      category.setCheckboxStatus(this.determineCategoryCheckboxStatus(category, categoryRooms));
	      category.DOM.checkbox = category.DOM.item.appendChild(this.renderCategoryBlockCheckbox(category, categoryRooms));
	    }
	    category.DOM.title = category.DOM.item.appendChild(this.renderCategoryBlockTitle(category));
	    if (this.categoryManager.canDo('edit') || category.rooms.length > 0) {
	      category.DOM.actionCont = category.DOM.item.appendChild(this.renderCategoryBlockActionsContainer(category));
	    }
	    this.createCategoryBlockContent(category, listWrap);
	    return category;
	  }
	  renderCategoryBlockWrap(category) {
	    if (this.isConfigureList) {
	      return main_core.Tag.render(_t7$1 || (_t7$1 = _$3`
					<li class="calendar-list-slider-item-category"
						data-bx-calendar-category-without-action="${0}"
					>
					</li>
				`), category.id);
	    }
	    return main_core.Tag.render(_t8$1 || (_t8$1 = _$3`
					<li class="calendar-list-slider-item-category" data-bx-calendar-category="${0}"></li>
		`), category.id);
	  }
	  renderCategoryBlockCheckbox(category) {
	    let checkboxStyle = '';
	    if (category.checkboxStatus === this.CATEGORY_ROOMS_SHOWN_ALL) {
	      checkboxStyle = 'calendar-list-slider-item-checkbox-checked';
	    } else if (category.checkboxStatus === this.CATEGORY_ROOMS_SHOWN_SOME) {
	      checkboxStyle = 'calendar-list-slider-item-checkbox-indeterminate';
	    }
	    return main_core.Tag.render(_t9 || (_t9 = _$3`
					<div class="calendar-title-checkbox calendar-list-slider-item-checkbox
						${0}" style="background-color: #a5abb2"
					>
					</div>
		`), checkboxStyle);
	  }
	  renderCategoryBlockActionsContainer(category) {
	    return main_core.Tag.render(_t10 || (_t10 = _$3`
					<div class="calendar-list-slider-item-actions-container
					calendar-list-slider-item-context-menu-category-wrap" 
						data-bx-calendar-category-menu="${0}"
					>
						<span class="calendar-list-slider-item-context-menu
							calendar-list-slider-item-context-menu-category"
						>
						</span>
					</div>
		`), category.id);
	  }
	  renderCategoryBlockTitle(category) {
	    return main_core.Tag.render(_t11 || (_t11 = _$3`
				<div class="calendar-list-slider-card-widget-title-text calendar-list-slider-item-category-text" 
					title="${0}"
				>
					${0}
				</div>
		`), main_core.Text.encode(category.name), main_core.Text.encode(category.name));
	  }
	  createCategoryBlockContent(category, wrap) {
	    if (category.rooms.length) {
	      category.rooms.forEach(room => this.createRoomBlock(room, wrap));
	    } else {
	      wrap.appendChild(main_core.Tag.render(_t12 || (_t12 = _$3`
					<li class="calendar-list-slider-card-widget-title-text">${0}</li>
				`), main_core.Loc.getMessage('EC_CATEGORY_EMPTY')));
	    }
	  }
	  createRoomBlock(room, listWrap) {
	    if (!room.DOM) {
	      room.DOM = {};
	    }
	    room.DOM.item = listWrap.appendChild(this.renderRoomBlockWrap(room));
	    room.DOM.checkbox = room.DOM.item.appendChild(this.renderRoomBlockCheckbox(room));
	    room.DOM.title = room.DOM.item.appendChild(this.renderRoomBlockTitle(room));
	    room.DOM.actionCont = room.DOM.item.appendChild(this.renderRoomBlockActionsContainer(room));
	    return room;
	  }
	  renderRoomBlockWrap(room) {
	    if (this.isConfigureList) {
	      return main_core.Tag.render(_t13 || (_t13 = _$3`
					<li class="calendar-list-slider-item"  data-bx-calendar-section-without-action="${0}"></li>
			`), room.id);
	    }
	    return main_core.Tag.render(_t14 || (_t14 = _$3`
					<li class="calendar-list-slider-item" data-bx-calendar-section="${0}"></li>
		`), room.id);
	  }
	  renderRoomBlockCheckbox(room) {
	    if (this.isConfigureList) {
	      return main_core.Tag.render(_t15 || (_t15 = _$3`
					<div class="calendar-field-select-icon" style="background-color: ${0}"></div>
			`), room.color);
	    }
	    return main_core.Tag.render(_t16 || (_t16 = _$3`
				<div class="calendar-list-slider-item-checkbox 
					${0}" 
					style="background-color: ${0}"
				>
				</div>
		`), room.isShown() ? 'calendar-list-slider-item-checkbox-checked' : '', room.color);
	  }
	  renderRoomBlockTitle(room) {
	    return main_core.Tag.render(_t17 || (_t17 = _$3`
				<div class="calendar-list-slider-item-name" title="${0}">
					${0}
				</div>
		`), main_core.Text.encode(room.name), main_core.Text.encode(room.name));
	  }
	  renderRoomBlockActionsContainer(room) {
	    return main_core.Tag.render(_t18 || (_t18 = _$3`
				<div class="calendar-list-slider-item-actions-container" data-bx-calendar-section-menu="${0}">
					<span class="calendar-list-slider-item-context-menu"></span>
				</div>
		`), room.id);
	  }
	  roomClickHandler(e) {
	    const target = calendar_util.Util.findTargetNode(e.target || e.srcElement, this.DOM.outerWrap);
	    if (target && target.getAttribute) {
	      if (target.getAttribute('data-bx-calendar-category') !== null) {
	        const category = this.categoryManager.getCategory(parseInt(target.getAttribute('data-bx-calendar-category'), 10));
	        if (category && category.rooms.length > 0) {
	          this.switchCategory(category, this.rooms);
	        }
	      } else if (target.getAttribute('data-bx-calendar-category-menu') !== null) {
	        let categoryId = target.getAttribute('data-bx-calendar-category-menu');
	        this.showCategoryMenu(this.categoryManager.getCategory(categoryId), target);
	      } else if (target.getAttribute('data-bx-calendar-section-menu') !== null) {
	        let roomId = target.getAttribute('data-bx-calendar-section-menu');
	        this.showRoomMenu(this.roomsManager.getRoom(roomId), target);
	      } else if (target.getAttribute('data-bx-calendar-section') !== null) {
	        let roomId = target.getAttribute('data-bx-calendar-section');
	        const room = this.roomsManager.getRoom(roomId);
	        this.switchSection(room);
	        this.updateCategoryCheckboxState(this.categoryManager.getCategory(room.categoryId));
	      }
	    }
	  }
	  setRoomsForCategory(categoryId) {
	    this.categoryManager.unsetCategoryRooms(categoryId);
	    const rooms = this.roomsManager.getRooms();
	    const categoryManager = this.categoryManager;
	    rooms.forEach(function (room) {
	      if (room.categoryId === categoryId) {
	        categoryManager.getCategory(categoryId).addRoom(room);
	      }
	    }, this);
	  }
	  showOnlyOneCategory(category, sections) {
	    for (let curSection of sections) {
	      if (curSection.categoryId === category.id) {
	        this.switchOnSection(curSection);
	      } else {
	        this.switchOffSection(curSection);
	      }
	    }
	    this.updateAllCategoriesCheckboxState();
	    this.calendarContext.reload();
	  }
	  showCategoryMenu(category, menuItemNode) {
	    this.setRoomsForCategory(category.id);
	    const menuItems = this.createCategoryMenuButtons(category);
	    if (menuItems && menuItems.length > 0) {
	      this.categoryActionMenu = this.createCategoryMenu(menuItems, menuItemNode);
	      this.categoryActionMenu.show();
	      this.categoryActionMenu.popupWindow.subscribe('onClose', () => {
	        this.allowSliderClose();
	      });
	      this.denySliderClose();
	    }
	  }
	  createCategoryMenuButtons(category) {
	    const menuItems = [];
	    if (this.categoryManager.canDo('view') && !this.isConfigureList && category.rooms.length > 0) {
	      menuItems.push({
	        text: main_core.Loc.getMessage('EC_SEC_LEAVE_ONE_ROOM'),
	        onclick: () => {
	          this.categoryActionMenu.close();
	          this.showOnlyOneCategory(category, this.roomsManager.rooms);
	        }
	      });
	    }
	    if (!this.readonly && this.categoryManager.canDo('edit')) {
	      menuItems.push({
	        text: main_core.Loc.getMessage('EC_SEC_EDIT'),
	        onclick: () => {
	          this.categoryActionMenu.close();
	          this.showEditCategoryForm({
	            category: category,
	            actionType: 'updateCategory'
	          });
	        }
	      });
	    }
	    if (this.categoryManager.canDo('edit')) {
	      menuItems.push({
	        text: main_core.Loc.getMessage('EC_SEC_DELETE'),
	        onclick: () => {
	          this.categoryActionMenu.close();
	          this.freezeButtons();
	          this.showCategoryDeleteConfirm(category);
	        }
	      });
	    }
	    return menuItems;
	  }
	  createCategoryMenu(menuItems, menuItemNode) {
	    const params = {
	      closeByEsc: true,
	      autoHide: true,
	      zIndex: this.zIndex,
	      offsetTop: 0,
	      offsetLeft: 9,
	      angle: true,
	      cacheable: false
	    };
	    return top.BX.PopupMenu.create('category-menu-' + calendar_util.Util.getRandomInt(), menuItemNode, menuItems, params);
	  }
	  findCheckBoxNodes(id) {
	    return this.DOM.blocksWrap.querySelectorAll('.calendar-list-slider-item[data-bx-calendar-section=\'' + id + '\'] .calendar-list-slider-item-checkbox');
	  }
	  destroy(event) {
	    if (event && event.getSlider && event.getSlider().getUrl() === this.sliderId) {
	      this.destroyEventEmitterSubscriptions();
	      BX.removeCustomEvent('SidePanel.Slider:onCloseComplete', BX.proxy(this.destroy, this));
	      BX.SidePanel.Instance.destroy(this.sliderId);
	      delete this.DOM.blocksWrap;
	      if (this.roomActionMenu) {
	        this.roomActionMenu.close();
	      }
	    }
	  }
	  deleteRoomHandler(event) {
	    if (event && event instanceof calendar_util.Util.getBX().Event.BaseEvent) {
	      const data = event.getData();
	      const deleteID = parseInt(data.id);
	      this.rooms.forEach((room, index) => {
	        if (parseInt(room.id) === deleteID && room.DOM && room.DOM.item) {
	          main_core.Dom.addClass(room.DOM.item, 'calendar-list-slider-item-disappearing');
	          setTimeout(() => {
	            main_core.Dom.clean(room.DOM.item, true);
	            this.rooms.splice(index, 1);
	          }, 300);
	        }
	      }, this);
	      this.closeForms();
	    }
	    this.refreshRooms();
	  }
	  deleteRoom(room) {
	    this.roomsManager.deleteRoom(room.id, room.location_id);
	    if (this.DOM.confirmRoomPopup) {
	      this.DOM.confirmRoomPopup.close();
	      delete this.DOM.confirmRoomPopup;
	    }
	    if (this.currentRoom) {
	      delete this.currentRoom;
	    }
	  }
	  deleteCategory(category) {
	    this.categoryManager.deleteCategory(category.id);
	    if (this.DOM.confirmCategoryPopup) {
	      this.DOM.confirmCategoryPopup.close();
	      delete this.DOM.confirmCategoryPopup;
	    }
	    if (this.currentCategory) {
	      delete this.currentCategory;
	    }
	  }
	  freezeButtons() {
	    main_core.Dom.addClass(this.DOM.outerWrap, 'calendar-content-locked');
	  }
	  unfreezeButtons() {
	    main_core.Dom.removeClass(this.DOM.outerWrap, 'calendar-content-locked');
	  }
	  isFrozen() {
	    return main_core.Dom.hasClass(this.DOM.outerWrap, 'calendar-content-locked');
	  }
	  updateCategoryCheckboxState(category) {
	    if (!category) {
	      return;
	    }
	    const updatedCategoryCheckboxStatus = this.determineCategoryCheckboxStatus(category, this.roomsManager.rooms);
	    if (category.checkboxStatus !== updatedCategoryCheckboxStatus) {
	      category.setCheckboxStatus(updatedCategoryCheckboxStatus);
	      this.setCategoryCheckboxState(this.findCategoryCheckBoxNode(category.id), updatedCategoryCheckboxStatus);
	    }
	  }
	  determineCategoryCheckboxStatus(category, rooms) {
	    let hasEnabled = false;
	    let hasDisabled = false;
	    rooms.forEach(room => {
	      if (room.categoryId === category.id) {
	        if (room.isShown() && !hasEnabled) {
	          hasEnabled = true;
	        }
	        if (!room.isShown() && !hasDisabled) {
	          hasDisabled = true;
	        }
	      }
	    });
	    if (hasEnabled && hasDisabled) {
	      return this.CATEGORY_ROOMS_SHOWN_SOME;
	    }
	    if (hasEnabled) {
	      return this.CATEGORY_ROOMS_SHOWN_ALL;
	    }
	    return this.CATEGORY_ROOMS_SHOWN_NONE;
	  }
	  switchCategory(category, rooms) {
	    const checkboxNode = this.findCategoryCheckBoxNode(category.id);
	    switch (category.checkboxStatus) {
	      case this.CATEGORY_ROOMS_SHOWN_SOME:
	      case this.CATEGORY_ROOMS_SHOWN_NONE:
	        this.switchOnCategoryRooms(category.id, rooms);
	        this.setCategoryCheckboxState(checkboxNode, this.CATEGORY_ROOMS_SHOWN_ALL);
	        category.setCheckboxStatus(this.CATEGORY_ROOMS_SHOWN_ALL);
	        break;
	      case this.CATEGORY_ROOMS_SHOWN_ALL:
	        this.switchOffCategoryRooms(category.id, rooms);
	        this.setCategoryCheckboxState(checkboxNode, this.CATEGORY_ROOMS_SHOWN_NONE);
	        category.setCheckboxStatus(this.CATEGORY_ROOMS_SHOWN_NONE);
	        break;
	      default:
	        break;
	    }
	    this.calendarContext.reload();
	  }
	  setCategoryCheckboxState(checkboxNode, checkboxStatus) {
	    main_core.Dom.removeClass(checkboxNode, 'calendar-list-slider-item-checkbox-checked');
	    main_core.Dom.removeClass(checkboxNode, 'calendar-list-slider-item-checkbox-indeterminate');
	    switch (checkboxStatus) {
	      case this.CATEGORY_ROOMS_SHOWN_SOME:
	        main_core.Dom.addClass(checkboxNode, 'calendar-list-slider-item-checkbox-indeterminate');
	        break;
	      case this.CATEGORY_ROOMS_SHOWN_ALL:
	        main_core.Dom.addClass(checkboxNode, 'calendar-list-slider-item-checkbox-checked');
	        break;
	      default:
	        break;
	    }
	  }
	  findCategoryCheckBoxNode(id) {
	    return this.DOM.outerWrap.querySelector('.calendar-list-slider-item-category[data-bx-calendar-category=\'' + id + '\'] .calendar-list-slider-item-checkbox');
	  }
	  switchOnCategoryRooms(categoryId, rooms) {
	    rooms.forEach(room => {
	      if (room.categoryId === categoryId && !room.isShown()) {
	        this.switchOnSection(room);
	      }
	    });
	  }
	  switchOffCategoryRooms(categoryId, rooms) {
	    rooms.forEach(room => {
	      if (room.categoryId === categoryId && room.isShown()) {
	        this.switchOffSection(room);
	      }
	    });
	  }
	  updateAllCategoriesCheckboxState() {
	    this.categoryManager.getCategories().forEach(category => this.updateCategoryCheckboxState(category));
	  }
	  showRoomDeleteConfirm(room) {
	    this.currentRoom = room;
	    this.DOM.confirmRoomPopup = new ui_dialogs_messagebox.MessageBox({
	      message: this.getConfirmRoomInterfaceContent(main_core.Loc.getMessage('EC_ROOM_DELETE_CONFIRM')),
	      minHeight: 120,
	      minWidth: 280,
	      maxWidth: 300,
	      buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
	      onOk: () => {
	        this.deleteRoom(room);
	      },
	      onCancel: () => {
	        this.DOM.confirmRoomPopup.close();
	      },
	      okCaption: main_core.Loc.getMessage('EC_SEC_DELETE'),
	      popupOptions: {
	        events: {
	          onPopupClose: () => {
	            delete this.DOM.confirmRoomPopup;
	            delete this.currentRoom;
	          }
	        },
	        closeByEsc: true,
	        padding: 0,
	        contentPadding: 0,
	        animation: 'fading-slide'
	      }
	    });
	    this.DOM.confirmRoomPopup.show();
	  }
	  showCategoryDeleteConfirm(category) {
	    this.currentCategory = category;
	    this.DOM.confirmCategoryPopup = new ui_dialogs_messagebox.MessageBox({
	      message: this.getConfirmRoomInterfaceContent(main_core.Loc.getMessage('EC_CATEGORY_DELETE_CONFIRM')),
	      minHeight: 120,
	      minWidth: 280,
	      maxWidth: 300,
	      buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
	      onOk: () => {
	        this.deleteCategory(category);
	      },
	      onCancel: () => {
	        this.DOM.confirmCategoryPopup.close();
	      },
	      okCaption: main_core.Loc.getMessage('EC_SEC_DELETE'),
	      popupOptions: {
	        events: {
	          onPopupClose: () => {
	            this.unfreezeButtons();
	            delete this.DOM.confirmCategoryPopup;
	            delete this.currentCategory;
	          }
	        },
	        closeByEsc: true,
	        padding: 0,
	        contentPadding: 0,
	        animation: 'fading-slide'
	      }
	    });
	    this.DOM.confirmCategoryPopup.show();
	  }
	  getConfirmRoomInterfaceContent(text) {
	    return main_core.Tag.render(_t19 || (_t19 = _$3`<div class="calendar-list-slider-messagebox-text">${0}</div>`), text);
	  }
	  keyHandler(e) {
	    if (e.keyCode === calendar_util.Util.getKeyCode('enter')) {
	      if (this.DOM.confirmRoomPopup && this.currentRoom) {
	        this.deleteRoom(this.currentRoom);
	      }
	      if (this.DOM.confirmCategoryPopup && this.currentCategory) {
	        this.deleteCategory(this.currentCategory);
	      }
	    }
	  }
	}

	exports.ReserveButton = ReserveButton;
	exports.RoomsInterface = RoomsInterface;
	exports.EditFormRoom = EditFormRoom;

}((this.BX.Calendar.Rooms = this.BX.Calendar.Rooms || {}),BX.Calendar.Controls,BX.Calendar,BX.Event,BX,BX.Calendar,BX.UI.EntitySelector,BX.UI.Dialogs));
//# sourceMappingURL=rooms.bundle.js.map
