this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,calendar_controls,calendar_sectioninterface,main_core,main_core_events,ui_entitySelector,calendar_util) {
	'use strict';

	var ReserveButton = /*#__PURE__*/function (_AddButton) {
	  babelHelpers.inherits(ReserveButton, _AddButton);

	  function ReserveButton() {
	    var _this;

	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ReserveButton);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ReserveButton).call(this, params));

	    _this.setEventNamespace('BX.Calendar.Rooms.ReserveButton');

	    _this.zIndex = params.zIndex || 3200;
	    _this.popupId = params.id || 'add-button-' + Math.round(Math.random() * 10000);
	    _this.showTasks = params.showTasks;
	    _this.addEntryHandler = main_core.Type.isFunction(params.addEntry) ? params.addEntry : null;
	    _this.addTaskHandler = main_core.Type.isFunction(params.addTask) ? params.addTask : null;

	    _this.create();

	    return _this;
	  }

	  babelHelpers.createClass(ReserveButton, [{
	    key: "create",
	    value: function create() {
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
	  }]);
	  return ReserveButton;
	}(calendar_controls.AddButton);

	var _templateObject, _templateObject2, _templateObject3, _templateObject4;
	var EditForm = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(EditForm, _EventEmitter);

	  function EditForm() {
	    var _this2;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, EditForm);
	    _this2 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(EditForm).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this2), "DOM", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this2), "isCreated", false);

	    _this2.setEventNamespace('BX.Calendar.SectionInterface.EditForm');

	    _this2.DOM.outerWrap = options.wrap;
	    _this2.sectionAccessTasks = options.sectionAccessTasks;
	    _this2.sectionManager = options.sectionManager;
	    _this2.closeCallback = options.closeCallback;
	    _this2.BX = calendar_util.Util.getBX();
	    _this2.keyHandlerBinded = _this2.keyHandler.bind(babelHelpers.assertThisInitialized(_this2));
	    return _this2;
	  }

	  babelHelpers.createClass(EditForm, [{
	    key: "show",
	    value: function show() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.section = params.section;
	      this.create();
	      this.showAccess = params.showAccess !== false;

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

	      BX.focus(this.DOM.sectionTitleInput);

	      if (this.DOM.sectionTitleInput.value !== '') {
	        this.DOM.sectionTitleInput.select();
	      }

	      this.isOpenedState = true;
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      this.isOpenedState = false;
	      main_core.Event.unbind(document, 'keydown', this.keyHandlerBinded);
	      main_core.Dom.removeClass(this.DOM.outerWrap, 'show');

	      if (main_core.Type.isFunction(this.closeCallback)) {
	        this.closeCallback();
	      }
	    }
	  }, {
	    key: "isOpened",
	    value: function isOpened() {
	      return this.isOpenedState;
	    }
	  }, {
	    key: "create",
	    value: function create() {
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
	      })); // Title

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
	      this.initAccessController(); // Buttons

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
	  }, {
	    key: "keyHandler",
	    value: function keyHandler(e) {
	      if (e.keyCode === calendar_util.Util.getKeyCode('escape')) {
	        this.checkClose();
	      } else if (e.keyCode === calendar_util.Util.getKeyCode('enter')) {
	        this.save();
	      }
	    }
	  }, {
	    key: "checkClose",
	    value: function checkClose() {
	      this.close();
	    }
	  }, {
	    key: "save",
	    value: function save() {
	      var _this3 = this;

	      this.saveBtn.setWaiting(true);
	      this.sectionManager.saveSection(this.DOM.sectionTitleInput.value, this.color, this.access, {
	        section: this.section
	      }).then(function () {
	        _this3.saveBtn.setWaiting(false);

	        _this3.close();
	      });
	    }
	  }, {
	    key: "initSectionColorSelector",
	    value: function initSectionColorSelector() {
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
	  }, {
	    key: "showSimplePicker",
	    value: function showSimplePicker(value) {
	      var colors = main_core.Runtime.clone(calendar_util.Util.getDefaultColorList(), true);
	      var innerCont = main_core.Dom.create('DIV', {
	        props: {
	          className: 'calendar-simple-color-wrap calendar-field-container-colorpicker-square'
	        }
	      });
	      var colorWrap = innerCont.appendChild(main_core.Dom.create('DIV', {
	        events: {
	          click: BX.delegate(this.simplePickerClick, this)
	        }
	      }));
	      var moreLinkWrap = innerCont.appendChild(main_core.Dom.create('DIV', {
	        props: {
	          className: 'calendar-simple-color-more-link-wrap'
	        }
	      }));
	      var moreLink = moreLinkWrap.appendChild(main_core.Dom.create('SPAN', {
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

	      for (var i = 0; i < colors.length; i++) {
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
	  }, {
	    key: "simplePickerClick",
	    value: function simplePickerClick(e) {
	      var target = calendar_util.Util.findTargetNode(e.target || e.srcElement, this.DOM.outerWrap);

	      if (main_core.Type.isElementNode(target)) {
	        var value = target.getAttribute('data-bx-calendar-color');

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
	  }, {
	    key: "showFullPicker",
	    value: function showFullPicker() {
	      if (this.simpleColorPopup) {
	        this.simpleColorPopup.close();
	      }

	      if (!this.fullColorPicker) {
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
	  }, {
	    key: "setColor",
	    value: function setColor(value) {
	      this.colorIcon.style.backgroundColor = value;
	      this.color = value;
	    }
	  }, {
	    key: "setAccess",
	    value: function setAccess(value) {
	      var rowsCount = 0;

	      for (var code in value) {
	        if (value.hasOwnProperty(code)) {
	          rowsCount++;
	        }
	      }

	      this.accessRowsCount = rowsCount;
	      this.access = value;

	      for (var _code in value) {
	        if (value.hasOwnProperty(_code)) {
	          this.insertAccessRow(calendar_util.Util.getAccessName(_code), _code, value[_code]);
	        }
	      }

	      this.checkAccessTableHeight();
	    }
	  }, {
	    key: "initAccessController",
	    value: function initAccessController() {
	      this.buildAccessController();

	      if (this.sectionManager && this.sectionManager.calendarType === 'group') {
	        this.initDialogGroup();
	      } else {
	        this.initDialogStandard();
	      }

	      this.initAccessSelectorPopup();
	    }
	  }, {
	    key: "initAccessSelectorPopup",
	    value: function initAccessSelectorPopup() {
	      var _this4 = this;

	      main_core.Event.bind(this.DOM.accessWrap, 'click', function (e) {
	        var target = calendar_util.Util.findTargetNode(e.target || e.srcElement, _this4.DOM.outerWrap);

	        if (main_core.Type.isElementNode(target)) {
	          if (target.getAttribute('data-bx-calendar-access-selector') !== null) {
	            // show selector
	            var code = target.getAttribute('data-bx-calendar-access-selector');

	            if (_this4.accessControls[code]) {
	              _this4.showAccessSelectorPopup({
	                node: _this4.accessControls[code].removeIcon,
	                setValueCallback: function setValueCallback(value) {
	                  if (_this4.accessTasks[value] && _this4.accessControls[code]) {
	                    _this4.accessControls[code].valueNode.innerHTML = main_core.Text.encode(_this4.accessTasks[value].title);
	                    _this4.access[code] = value;
	                  }
	                }
	              });
	            }
	          } else if (target.getAttribute('data-bx-calendar-access-remove') !== null) {
	            var _code2 = target.getAttribute('data-bx-calendar-access-remove');

	            if (_this4.accessControls[_code2]) {
	              main_core.Dom.remove(_this4.accessControls[_code2].rowNode);
	              _this4.accessControls[_code2] = null;
	              delete _this4.access[_code2];
	            }
	          }
	        }
	      });
	    }
	  }, {
	    key: "buildAccessController",
	    value: function buildAccessController() {
	      var _this5 = this;

	      this.DOM.accessLink = this.DOM.optionsWrap.appendChild(main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-list-slider-new-calendar-option-more\">", "</div>"])), main_core.Loc.getMessage('EC_SEC_SLIDER_ACCESS')));
	      this.DOM.accessWrap = this.DOM.formFieldsWrap.appendChild(main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-list-slider-access-container\">\n\t\t\t\t\t<div class=\"calendar-list-slider-access-inner-wrap\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"calendar-list-slider-new-calendar-options-container\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>"])), this.DOM.accessTable = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<table class=\"calendar-section-slider-access-table\"></table>\n\t\t\t\t\t\t"]))), this.DOM.accessButton = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<span class=\"calendar-list-slider-new-calendar-option-add\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</span>"])), main_core.Loc.getMessage('EC_SEC_SLIDER_ACCESS_ADD'))));
	      this.accessControls = {};
	      this.accessTasks = this.sectionAccessTasks;
	      main_core.Event.bind(this.DOM.accessLink, 'click', function () {
	        if (main_core.Dom.hasClass(_this5.DOM.accessWrap, 'shown')) {
	          main_core.Dom.removeClass(_this5.DOM.accessWrap, 'shown');
	        } else {
	          main_core.Dom.addClass(_this5.DOM.accessWrap, 'shown');
	        }

	        _this5.checkAccessTableHeight();
	      });
	    }
	  }, {
	    key: "initDialogStandard",
	    value: function initDialogStandard() {
	      var _this6 = this;

	      main_core.Event.bind(this.DOM.accessButton, 'click', function () {
	        _this6.entitySelectorDialog = new ui_entitySelector.Dialog({
	          targetNode: _this6.DOM.accessButton,
	          context: 'CALENDAR',
	          preselectedItems: [],
	          enableSearch: true,
	          events: {
	            'Item:onSelect': _this6.handleEntitySelectorChanges.bind(_this6),
	            'Item:onDeselect': _this6.handleEntitySelectorChanges.bind(_this6)
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

	        _this6.entitySelectorDialog.show();
	      });
	    }
	  }, {
	    key: "initDialogGroup",
	    value: function initDialogGroup() {
	      var _this7 = this;

	      main_core.Event.bind(this.DOM.accessButton, 'click', function () {
	        _this7.entitySelectorDialog = new ui_entitySelector.Dialog({
	          targetNode: _this7.DOM.accessButton,
	          context: 'CALENDAR',
	          preselectedItems: [],
	          enableSearch: true,
	          events: {
	            'Item:onSelect': _this7.handleEntitySelectorChanges.bind(_this7),
	            'Item:onDeselect': _this7.handleEntitySelectorChanges.bind(_this7)
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
	            title: _this7.sectionManager.ownerName
	          }],
	          items: [{
	            id: 'SG' + _this7.sectionManager.ownerId + '_' + 'A',
	            entityId: 'group',
	            tabs: 'groupAccess',
	            title: main_core.Loc.getMessage('EC_ACCESS_GROUP_ADMIN')
	          }, {
	            id: 'SG' + _this7.sectionManager.ownerId + '_' + 'E',
	            entityId: 'group',
	            tabs: 'groupAccess',
	            title: main_core.Loc.getMessage('EC_ACCESS_GROUP_MODERATORS')
	          }, {
	            id: 'SG' + _this7.sectionManager.ownerId + '_' + 'K',
	            entityId: 'group',
	            tabs: 'groupAccess',
	            title: main_core.Loc.getMessage('EC_ACCESS_GROUP_MEMBERS')
	          }]
	        });

	        _this7.entitySelectorDialog.show();
	      });
	    }
	  }, {
	    key: "handleEntitySelectorChanges",
	    value: function handleEntitySelectorChanges() {
	      var _this8 = this;

	      var entityList = this.entitySelectorDialog.getSelectedItems();
	      this.entitySelectorDialog.hide();

	      if (main_core.Type.isArray(entityList)) {
	        entityList.forEach(function (entity) {
	          var title;

	          if (entity.entityId === 'group') {
	            title = _this8.sectionManager.ownerName + ': ' + entity.title.text;
	          } else {
	            title = entity.title.text;
	          }

	          var code = calendar_util.Util.convertEntityToAccessCode(entity);
	          calendar_util.Util.setAccessName(code, title);

	          _this8.insertAccessRow(title, code);
	        });
	      }

	      main_core.Runtime.debounce(function () {
	        _this8.entitySelectorDialog.destroy();
	      }, 400)();
	    } // todo: refactor it

	  }, {
	    key: "insertAccessRow",
	    value: function insertAccessRow(title, code, value) {
	      if (!this.accessControls[code]) {
	        if (value === undefined) {
	          for (var taskId in this.sectionAccessTasks) {
	            if (this.sectionAccessTasks.hasOwnProperty(taskId) && this.sectionAccessTasks[taskId].name === 'calendar_view') {
	              value = taskId;
	              break;
	            }
	          }
	        }

	        var rowNode = main_core.Dom.adjust(this.DOM.accessTable.insertRow(-1), {
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
	  }, {
	    key: "checkAccessTableHeight",
	    value: function checkAccessTableHeight() {
	      var _this9 = this;

	      if (this.checkTableTimeout) {
	        this.checkTableTimeout = clearTimeout(this.checkTableTimeout);
	      }

	      this.checkTableTimeout = setTimeout(function () {
	        if (main_core.Dom.hasClass(_this9.DOM.accessWrap, 'shown')) {
	          if (_this9.DOM.accessWrap.offsetHeight - _this9.DOM.accessTable.offsetHeight < 36) {
	            _this9.DOM.accessWrap.style.maxHeight = parseInt(_this9.DOM.accessTable.offsetHeight) + 100 + 'px';
	          }
	        } else {
	          _this9.DOM.accessWrap.style.maxHeight = '';
	        }
	      }, 300);
	    }
	  }, {
	    key: "showAccessSelectorPopup",
	    value: function showAccessSelectorPopup(params) {
	      if (this.accessPopupMenu && this.accessPopupMenu.popupWindow && this.accessPopupMenu.popupWindow.isShown()) {
	        return this.accessPopupMenu.close();
	      }

	      var _this = this;

	      var menuItems = [];

	      for (var taskId in this.accessTasks) {
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
	  }]);
	  return EditForm;
	}(main_core_events.EventEmitter);

	var _templateObject$1, _templateObject2$1, _templateObject3$1, _templateObject4$1, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10, _templateObject11, _templateObject12;
	var EditFormRoom = /*#__PURE__*/function (_EditForm) {
	  babelHelpers.inherits(EditFormRoom, _EditForm);

	  function EditFormRoom() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, EditFormRoom);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(EditFormRoom).call(this, options));

	    _this.setEventNamespace('BX.Calendar.Rooms.EditFormRoom');

	    _this.DOM.outerWrap = options.wrap;
	    _this.roomsManager = options.roomsManager;
	    _this.capacityNumbers = [3, 5, 7, 10, 25];
	    _this.zIndex = options.zIndex || 3100;
	    _this.closeCallback = options.closeCallback;
	    _this.BX = calendar_util.Util.getBX();
	    _this.keyHandlerBinded = _this.keyHandler.bind(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(EditFormRoom, [{
	    key: "show",
	    value: function show() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.actionType = params.actionType;
	      this.room = params.room;
	      this.create();
	      this.showAccess = params.showAccess !== false;

	      if (this.showAccess) {
	        this.DOM.accessLink.style.display = '';
	        this.DOM.accessWrap.style.display = '';
	      } else {
	        this.DOM.accessLink.style.display = 'none';
	        this.DOM.accessWrap.style.display = 'none';
	      }

	      main_core.Event.bind(document, 'keydown', this.keyHandlerBinded);
	      main_core.Dom.addClass(this.DOM.outerWrap, 'show');

	      if (params.room) {
	        if (params.room.color) {
	          this.setColor(params.room.color);
	        }

	        this.setAccess(params.room.access || params.room.data.ACCESS || {});

	        if (params.room.name) {
	          this.DOM.roomsTitleInput.value = params.room.name;
	        }

	        if (params.room.capacity) {
	          this.DOM.roomsCapacityInput.value = params.room.capacity;
	        }
	      }

	      BX.focus(this.DOM.roomsTitleInput);

	      if (this.DOM.roomsTitleInput.value !== '') {
	        this.DOM.roomsTitleInput.select();
	      }

	      this.isOpenedState = true;
	    }
	  }, {
	    key: "create",
	    value: function create() {
	      this.wrap = this.DOM.outerWrap.querySelector('.calendar-form-content');

	      if (this.wrap) {
	        main_core.Dom.clean(this.wrap);
	      } else {
	        this.wrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"calendar-form-content\"></div>\n\t\t\t\t"]))));
	      }

	      this.DOM.formFieldsWrap = this.wrap.appendChild(main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-list-slider-widget-content\"></div>\n\t\t\t"])))).appendChild(main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-list-slider-widget-content-block\"></div>"])))); // Title

	      this.DOM.roomsTitleInput = this.DOM.formFieldsWrap.appendChild(main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-field-container calendar-field-container-string\"></div>"])))).appendChild(main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-field-block\"></div>"])))).appendChild(main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input type=\"text\" placeholder=\"", "\" \n\t\t\tclass=\"calendar-field calendar-field-string\"/>"])), main_core.Loc.getMessage('EC_SEC_SLIDER_SECTION_TITLE'))); //Capacity

	      this.DOM.roomsCapacityInput = this.DOM.formFieldsWrap.appendChild(main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-field-container calendar-field-container-string\"></div>"])))).appendChild(main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-field-block\"></div>"])))).appendChild(main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class =\"calendar-list-slider-card-widget-title\">\n\t\t\t\t\t\t<span class=\"calendar-list-slider-card-widget-title-text\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\t\n\t\t\t\t\t</div>\t\t\t\t\t\t\n\t\t\t\t\t"])), main_core.Loc.getMessage('EC_SEC_SLIDER_SECTION_CAPACITY'))).appendChild(main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input type=\"number\" class=\"calendar-field calendar-field-number\" placeholder=\"0\"/>"]))));
	      this.DOM.optionsWrap = this.DOM.formFieldsWrap.appendChild(main_core.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-list-slider-new-calendar-options-container\"></div>"]))));
	      this.initSectionColorSelector();
	      this.initAccessController(); // Buttons

	      this.buttonsWrap = this.DOM.formFieldsWrap.appendChild(main_core.Tag.render(_templateObject12 || (_templateObject12 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-list-slider-btn-container\"></div>"]))));

	      if (this.actionType === 'createRoom') {
	        this.saveBtn = new BX.UI.Button({
	          text: main_core.Loc.getMessage('EC_SEC_SLIDER_SAVE'),
	          className: 'ui-btn ui-btn-success',
	          events: {
	            click: this.createRoom.bind(this)
	          }
	        });
	        this.saveBtn.renderTo(this.buttonsWrap);
	      } else if (this.actionType === 'updateRoom') {
	        this.saveBtn = new BX.UI.Button({
	          text: main_core.Loc.getMessage('EC_SEC_SLIDER_SAVE'),
	          className: 'ui-btn ui-btn-success',
	          events: {
	            click: this.updateRoom.bind(this)
	          }
	        });
	        this.saveBtn.renderTo(this.buttonsWrap);
	      }

	      new BX.UI.Button({
	        text: main_core.Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
	        className: 'ui-btn ui-btn-link',
	        events: {
	          click: this.checkClose.bind(this)
	        }
	      }).renderTo(this.buttonsWrap);
	      this.isCreated = true;
	    }
	  }, {
	    key: "createRoom",
	    value: function createRoom() {
	      var _this2 = this;

	      this.saveBtn.setWaiting(true);
	      this.roomsManager.createRoom({
	        name: this.DOM.roomsTitleInput.value,
	        capacity: this.DOM.roomsCapacityInput.value,
	        color: this.color,
	        access: this.access
	      }).then(function () {
	        _this2.saveBtn.setWaiting(false);

	        _this2.close();
	      });
	    }
	  }, {
	    key: "initAccessController",
	    value: function initAccessController() {
	      this.buildAccessController();
	      this.initDialogStandard();
	      this.initAccessSelectorPopup();
	    }
	  }, {
	    key: "updateRoom",
	    value: function updateRoom() {
	      var _this3 = this;

	      this.saveBtn.setWaiting(true);
	      this.roomsManager.updateRoom({
	        id: this.room.id,
	        location_id: this.room.location_id,
	        name: this.DOM.roomsTitleInput.value,
	        capacity: this.DOM.roomsCapacityInput.value,
	        color: this.color,
	        access: this.access
	      }).then(function () {
	        _this3.saveBtn.setWaiting(false);

	        _this3.close();
	      });
	    }
	  }, {
	    key: "keyHandler",
	    value: function keyHandler(e) {
	      if (e.keyCode === calendar_util.Util.getKeyCode('escape')) {
	        this.checkClose();
	      } else if (e.keyCode === calendar_util.Util.getKeyCode('enter') && this.actionType === 'createRoom') {
	        this.createRoom();
	      } else if (e.keyCode === calendar_util.Util.getKeyCode('enter') && this.actionType === 'updateRoom') {
	        this.updateRoom();
	      }
	    }
	  }]);
	  return EditFormRoom;
	}(EditForm);

	var _templateObject$2, _templateObject2$2, _templateObject3$2, _templateObject4$2, _templateObject5$1, _templateObject6$1, _templateObject7$1, _templateObject8$1, _templateObject9$1, _templateObject10$1, _templateObject11$1, _templateObject12$1, _templateObject13;
	var RoomsInterface = /*#__PURE__*/function (_SectionInterface) {
	  babelHelpers.inherits(RoomsInterface, _SectionInterface);

	  function RoomsInterface(_ref) {
	    var _this;

	    var calendarContext = _ref.calendarContext,
	        readonly = _ref.readonly,
	        roomsManager = _ref.roomsManager,
	        _ref$isConfigureList = _ref.isConfigureList,
	        isConfigureList = _ref$isConfigureList === void 0 ? false : _ref$isConfigureList;
	    babelHelpers.classCallCheck(this, RoomsInterface);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(RoomsInterface).call(this, {
	      calendarContext: calendarContext,
	      readonly: readonly,
	      roomsManager: roomsManager
	    }));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "SLIDER_WIDTH", 400);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "SLIDER_DURATION", 80);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "sliderId", "calendar:rooms-slider");

	    _this.setEventNamespace('BX.Calendar.RoomsInterface');

	    _this.roomsManager = roomsManager;
	    _this.isConfigureList = isConfigureList;
	    _this.calendarContext = calendarContext;
	    _this.readonly = readonly;
	    _this.BX = calendar_util.Util.getBX();
	    _this.sliderOnClose = _this.hide.bind(babelHelpers.assertThisInitialized(_this));
	    _this.deleteRoomHandlerBinded = _this.deleteRoomHandler.bind(babelHelpers.assertThisInitialized(_this));
	    _this.refreshRoomListBinded = _this.refreshRoomList.bind(babelHelpers.assertThisInitialized(_this));

	    if (_this.calendarContext !== null) {
	      if (_this.calendarContext.util.config.accessNames) {
	        var _this$calendarContext, _this$calendarContext2, _this$calendarContext3;

	        calendar_util.Util.setAccessNames((_this$calendarContext = _this.calendarContext) === null || _this$calendarContext === void 0 ? void 0 : (_this$calendarContext2 = _this$calendarContext.util) === null || _this$calendarContext2 === void 0 ? void 0 : (_this$calendarContext3 = _this$calendarContext2.config) === null || _this$calendarContext3 === void 0 ? void 0 : _this$calendarContext3.accessNames);
	      }
	    }

	    return _this;
	  }

	  babelHelpers.createClass(RoomsInterface, [{
	    key: "addEventEmitterSubscriptions",
	    value: function addEventEmitterSubscriptions() {
	      calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms:create', this.refreshRoomListBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms:update', this.refreshRoomListBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms:delete', this.deleteRoomHandlerBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms:pull-create', this.refreshRoomListBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms:pull-update', this.refreshRoomListBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Rooms:pull-delete', this.deleteRoomHandlerBinded);
	    }
	  }, {
	    key: "destroyEventEmitterSubscriptions",
	    value: function destroyEventEmitterSubscriptions() {
	      calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms:create', this.refreshRoomListBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms:update', this.refreshRoomListBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms:delete', this.deleteRoomHandlerBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms:pull-create', this.refreshRoomListBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms:pull-update', this.refreshRoomListBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Rooms:pull-delete', this.deleteRoomHandlerBinded);
	    }
	  }, {
	    key: "createContent",
	    value: function createContent() {
	      this.DOM.outerWrap = main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-list-slider-wrap\"></div>\n\t\t"])));
	      this.DOM.titleWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-list-slider-title-container\">\n\t\t\t\t\t<div class=\"calendar-list-slider-title\">", "</div>\n\t\t\t\t</div>\n\t\t\t"])), main_core.Loc.getMessage('EC_SECTION_ROOMS')));

	      if (!this.readonly) {
	        // #1. Controls
	        this.createAddButton(); // #2. Forms

	        this.DOM.roomFormWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_templateObject3$2 || (_templateObject3$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"calendar-list-slider-card-widget calendar-list-slider-form-wrap\">\n\t\t\t\t\t\t<div class=\"calendar-list-slider-card-widget-title\">\n\t\t\t\t\t\t\t<span class=\"calendar-list-slider-card-widget-title-text\">", "</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t"])), main_core.Loc.getMessage('EC_SEC_SLIDER_NEW_ROOM')));
	      }

	      this.createRoomList();
	      return this.DOM.outerWrap;
	    }
	  }, {
	    key: "createAddButton",
	    value: function createAddButton() {
	      //add button in slider list of meeting rooms
	      this.actionType = 'createRoom';
	      var addButtonOuter = this.DOM.titleWrap.appendChild(main_core.Tag.render(_templateObject4$2 || (_templateObject4$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"ui-btn-light-border\" style=\"margin-right: 0\"></span>\n\t\t\t"]))));
	      this.DOM.addButton = addButtonOuter.appendChild(main_core.Tag.render(_templateObject5$1 || (_templateObject5$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"ui-btn\" onclick=\"", "\">", "</span>\n\t\t\t"])), this.showEditRoomForm.bind(this), main_core.Loc.getMessage('EC_ADD')));
	    }
	  }, {
	    key: "createRoomList",
	    value: function createRoomList() {
	      this.sliderRoom = this.roomsManager.getRooms(); // title = Loc.getMessage('EC_SEC_SLIDER_TYPE_ROOM_LIST');

	      if (this.DOM.roomListWrap) {
	        main_core.Dom.clean(this.DOM.roomListWrap);
	        main_core.Dom.adjust(this.DOM.roomListWrap, {
	          props: {
	            className: 'calendar-list-slider-card-widget'
	          }
	        });
	      } else {
	        this.DOM.roomListWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_templateObject6$1 || (_templateObject6$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"calendar-list-slider-card-widget\">\n\t\t\t\t\t</div>\n\t\t\t\t"]))));
	      }

	      this.createRoomBlock({
	        wrap: this.DOM.roomListWrap,
	        roomList: this.sliderRoom.filter(function (room) {
	          return room.belongsToView() || room.isPseudo();
	        })
	      });
	    }
	  }, {
	    key: "showEditRoomForm",
	    value: function showEditRoomForm() {
	      var _this2 = this;

	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      if (typeof params.actionType === 'undefined') {
	        params.actionType = 'createRoom';
	      }

	      this.closeForms();
	      var formTitleNode = this.DOM.roomFormWrap.querySelector('.calendar-list-slider-card-widget-title-text');
	      this.editSectionForm = new EditFormRoom({
	        wrap: this.DOM.roomFormWrap,
	        sectionAccessTasks: this.roomsManager.getSectionAccessTasks(),
	        roomsManager: this.roomsManager,
	        closeCallback: function closeCallback() {
	          _this2.allowSliderClose();
	        }
	      });
	      var showAccessControl = true;

	      if (params.room && params.room.id) {
	        formTitleNode.innerHTML = main_core.Loc.getMessage('EC_SEC_SLIDER_EDIT_SECTION_ROOM');
	        showAccessControl = params.room.canDo('access');
	      } else {
	        formTitleNode.innerHTML = main_core.Loc.getMessage('EC_SEC_SLIDER_NEW_ROOM');
	      }

	      this.editSectionForm.show({
	        showAccess: showAccessControl,
	        room: params.room || {
	          color: calendar_util.Util.getRandomColor(),
	          access: this.roomsManager.getDefaultSectionAccess()
	        },
	        actionType: params.actionType
	      });
	      this.denySliderClose();
	    }
	  }, {
	    key: "showRoomMenu",
	    value: function showRoomMenu(room, menuItemNode) {
	      var _this3 = this;

	      var menuItems = [];
	      var itemNode = menuItemNode.closest('[data-bx-calendar-section]') || menuItemNode.closest('[ data-bx-calendar-section-without-action]');

	      if (main_core.Type.isElementNode(itemNode)) {
	        main_core.Dom.addClass(itemNode, 'active');
	      }

	      if (room.canDo('view_time') && !this.isConfigureList) {
	        menuItems.push({
	          text: main_core.Loc.getMessage('EC_SEC_LEAVE_ONE_ROOM'),
	          onclick: function onclick() {
	            _this3.roomActionMenu.close();

	            _this3.showOnlyOneSection(room, _this3.roomsManager.rooms);
	          }
	        });
	      }

	      if (!this.readonly && room.canDo('edit_section')) {
	        menuItems.push({
	          text: main_core.Loc.getMessage('EC_SEC_EDIT'),
	          onclick: function onclick() {
	            _this3.roomActionMenu.close();

	            _this3.showEditRoomForm({
	              room: room,
	              actionType: 'updateRoom'
	            });
	          }
	        });
	      }

	      if (room.canDo('edit_section') && room.belongsToView()) {
	        menuItems.push({
	          text: main_core.Loc.getMessage('EC_SEC_DELETE'),
	          onclick: function onclick() {
	            _this3.roomActionMenu.close();

	            _this3.deleteRoom(room);
	          }
	        });
	      }

	      if (menuItems && menuItems.length > 0) {
	        this.roomActionMenu = top.BX.PopupMenu.create('section-menu-' + calendar_util.Util.getRandomInt(), menuItemNode, menuItems, {
	          closeByEsc: true,
	          autoHide: true,
	          zIndex: this.zIndex,
	          offsetTop: 0,
	          offsetLeft: 9,
	          angle: true,
	          cacheable: false
	        });
	        this.roomActionMenu.show();
	        this.roomActionMenu.popupWindow.subscribe('onClose', function () {
	          if (main_core.Type.isElementNode(itemNode)) {
	            main_core.Dom.removeClass(itemNode, 'active');
	          }

	          _this3.allowSliderClose();
	        });
	        this.denySliderClose();
	      } else {
	        main_core.Dom.removeClass(itemNode, 'active');
	      }
	    }
	  }, {
	    key: "refreshRoomList",
	    value: function refreshRoomList() {
	      this.createRoomList();
	    }
	  }, {
	    key: "createRoomBlock",
	    value: function createRoomBlock(_ref2) {
	      var _this4 = this;

	      var wrap = _ref2.wrap,
	          roomList = _ref2.roomList;

	      if (main_core.Type.isArray(roomList)) {
	        var listWrap = wrap.appendChild(main_core.Tag.render(_templateObject7$1 || (_templateObject7$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"calendar-list-slider-widget-content\"></div>\n\t\t\t\t"])))).appendChild(main_core.Tag.render(_templateObject8$1 || (_templateObject8$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"calendar-list-slider-widget-content-block\"></div>\n\t\t\t\t\t"])))).appendChild(main_core.Tag.render(_templateObject9$1 || (_templateObject9$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<ul class=\"calendar-list-slider-container\"></ul>\n\t\t\t\t\t"]))));
	        main_core.Event.bind(listWrap, 'click', this.roomClickHandler.bind(this));
	        roomList.forEach(function (room) {
	          if (!room.DOM) {
	            room.DOM = {};
	          }

	          var roomId = room.id;
	          var li;
	          var checkbox;

	          if (_this4.isConfigureList) {
	            li = listWrap.appendChild(main_core.Tag.render(_templateObject10$1 || (_templateObject10$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t<li class=\"calendar-list-slider-item\"  data-bx-calendar-section-without-action=\"", "\"></li>\n\t\t\t\t\t"])), roomId));
	            checkbox = li.appendChild(main_core.Dom.create('DIV', {
	              props: {
	                className: 'calendar-field-select-icon'
	              },
	              style: {
	                backgroundColor: room.color
	              }
	            }));
	          } else {
	            li = listWrap.appendChild(main_core.Tag.render(_templateObject11$1 || (_templateObject11$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t<li class=\"calendar-list-slider-item\" data-bx-calendar-section=\"", "\"></li>\n\t\t\t\t\t"])), roomId));
	            checkbox = li.appendChild(main_core.Dom.create('DIV', {
	              props: {
	                className: 'calendar-list-slider-item-checkbox' + (room.isShown() ? ' calendar-list-slider-item-checkbox-checked' : '')
	              },
	              style: {
	                backgroundColor: room.color
	              }
	            }));
	          }

	          var title = li.appendChild(main_core.Tag.render(_templateObject12$1 || (_templateObject12$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"calendar-list-slider-item-name\" title=\"", "\">", "</div>\n\t\t\t\t\t"])), BX.util.htmlspecialchars(room.name), BX.util.htmlspecialchars(room.name)));
	          room.DOM.item = li;
	          room.DOM.checkbox = checkbox;
	          room.DOM.title = title;
	          room.DOM.actionCont = li.appendChild(main_core.Tag.render(_templateObject13 || (_templateObject13 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"calendar-list-slider-item-actions-container\" data-bx-calendar-section-menu=\"", "\">\n\t\t\t\t\t\t<span class=\"calendar-list-slider-item-context-menu\"></span>\n\t\t\t\t\t</div>\n\t\t\t\t"])), roomId));
	        });
	      }
	    }
	  }, {
	    key: "roomClickHandler",
	    value: function roomClickHandler(e) {
	      var target = calendar_util.Util.findTargetNode(e.target || e.srcElement, this.DOM.outerWrap);

	      if (target && target.getAttribute) {
	        if (target.getAttribute('data-bx-calendar-section-menu') !== null) {
	          var roomId = target.getAttribute('data-bx-calendar-section-menu');
	          this.showRoomMenu(this.roomsManager.getRoom(roomId), target);
	        } else if (target.getAttribute('data-bx-calendar-section') !== null) {
	          var _roomId = target.getAttribute('data-bx-calendar-section');

	          this.switchSection(this.roomsManager.getRoom(_roomId));
	        }
	      }
	    }
	  }, {
	    key: "findCheckBoxNodes",
	    value: function findCheckBoxNodes(id) {
	      return this.DOM.roomListWrap.querySelectorAll('.calendar-list-slider-item[data-bx-calendar-section=\'' + id + '\'] .calendar-list-slider-item-checkbox');
	    }
	  }, {
	    key: "destroy",
	    value: function destroy(event) {
	      if (event && event.getSlider && event.getSlider().getUrl() === this.sliderId) {
	        this.destroyEventEmitterSubscriptions();
	        BX.removeCustomEvent('SidePanel.Slider:onCloseComplete', BX.proxy(this.destroy, this));
	        BX.SidePanel.Instance.destroy(this.sliderId);
	        delete this.DOM.roomListWrap;

	        if (this.roomActionMenu) {
	          this.roomActionMenu.close();
	        }
	      }
	    }
	  }, {
	    key: "deleteRoomHandler",
	    value: function deleteRoomHandler(event) {
	      var _this5 = this;

	      if (event && event instanceof calendar_util.Util.getBX().Event.BaseEvent) {
	        var data = event.getData();
	        var deleteID = parseInt(data.id);
	        this.sliderRoom.forEach(function (room, index) {
	          if (parseInt(room.id) === deleteID && room.DOM && room.DOM.item) {
	            main_core.Dom.addClass(room.DOM.item, 'calendar-list-slider-item-disappearing');
	            setTimeout(function () {
	              main_core.Dom.clean(room.DOM.item, true);

	              _this5.sliderRoom.splice(index, 1);
	            }, 300);
	          }
	        }, this);
	        this.closeForms();
	      }
	    }
	  }, {
	    key: "deleteRoom",
	    value: function deleteRoom(room) {
	      this.roomsManager.deleteRoom(room.id, room.location_id);
	    }
	  }]);
	  return RoomsInterface;
	}(calendar_sectioninterface.SectionInterface);

	exports.ReserveButton = ReserveButton;
	exports.RoomsInterface = RoomsInterface;
	exports.EditFormRoom = EditFormRoom;

}((this.BX.Calendar.Rooms = this.BX.Calendar.Rooms || {}),BX.Calendar.Controls,BX.Calendar,BX,BX.Event,BX.UI.EntitySelector,BX.Calendar));
//# sourceMappingURL=rooms.bundle.js.map
