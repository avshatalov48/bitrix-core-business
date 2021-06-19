this.BX = this.BX || {};
(function (exports,calendar_entry,calendar_controls,main_core_events,ui_entitySelector,main_core,calendar_util,calendar_sectionmanager) {
	'use strict';

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<span class=\"calendar-list-slider-new-calendar-option-add\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</span>"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<table class=\"calendar-section-slider-access-table\"></table>\n\t\t\t\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-list-slider-access-container\">\n\t\t\t\t\t<div class=\"calendar-list-slider-access-inner-wrap\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"calendar-list-slider-new-calendar-options-container\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-list-slider-new-calendar-option-more\">", "</div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
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
	      this.section = params.section;

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
	      var _this4 = this;

	      this.DOM.accessLink = this.DOM.optionsWrap.appendChild(main_core.Tag.render(_templateObject(), main_core.Loc.getMessage('EC_SEC_SLIDER_ACCESS')));
	      this.DOM.accessWrap = this.DOM.formFieldsWrap.appendChild(main_core.Tag.render(_templateObject2(), this.DOM.accessTable = main_core.Tag.render(_templateObject3()), this.DOM.accessButton = main_core.Tag.render(_templateObject4(), main_core.Loc.getMessage('EC_SEC_SLIDER_ACCESS_ADD'))));
	      this.accessControls = {};
	      this.accessTasks = this.sectionAccessTasks;
	      main_core.Event.bind(this.DOM.accessLink, 'click', function () {
	        if (main_core.Dom.hasClass(_this4.DOM.accessWrap, 'shown')) {
	          main_core.Dom.removeClass(_this4.DOM.accessWrap, 'shown');
	        } else {
	          main_core.Dom.addClass(_this4.DOM.accessWrap, 'shown');
	        }

	        _this4.checkAccessTableHeight();
	      });
	      main_core.Event.bind(this.DOM.accessButton, 'click', function () {
	        _this4.entitySelectorDialog = new ui_entitySelector.Dialog({
	          targetNode: _this4.DOM.accessButton,
	          context: 'CALENDAR',
	          preselectedItems: [],
	          enableSearch: true,
	          events: {
	            'Item:onSelect': _this4.handleEntitySelectorChanges.bind(_this4),
	            'Item:onDeselect': _this4.handleEntitySelectorChanges.bind(_this4)
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

	        _this4.entitySelectorDialog.show();
	      });
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
	    key: "handleEntitySelectorChanges",
	    value: function handleEntitySelectorChanges() {
	      var _this5 = this;

	      var entityList = this.entitySelectorDialog.getSelectedItems();
	      this.entitySelectorDialog.hide();

	      if (main_core.Type.isArray(entityList)) {
	        entityList.forEach(function (entity) {
	          var title = entity.title.text;
	          var code = calendar_util.Util.convertEntityToAccessCode(entity);
	          calendar_util.Util.setAccessName(code, title);

	          _this5.insertAccessRow(title, code);
	        });
	      }

	      main_core.Runtime.debounce(function () {
	        _this5.entitySelectorDialog.destroy();
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
	      var _this6 = this;

	      if (this.checkTableTimeout) {
	        this.checkTableTimeout = clearTimeout(this.checkTableTimeout);
	      }

	      this.checkTableTimeout = setTimeout(function () {
	        if (main_core.Dom.hasClass(_this6.DOM.accessWrap, 'shown')) {
	          if (_this6.DOM.accessWrap.offsetHeight - _this6.DOM.accessTable.offsetHeight < 36) {
	            _this6.DOM.accessWrap.style.maxHeight = parseInt(_this6.DOM.accessTable.offsetHeight) + 100 + 'px';
	          }
	        } else {
	          _this6.DOM.accessWrap.style.maxHeight = '';
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

	function _templateObject9() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<div class=\"calendar-list-slider-item-checkbox\" style=\"background: ", "\"></div>\n\t\t\t\t\t\t"]);

	  _templateObject9 = function _templateObject9() {
	    return data;
	  };

	  return data;
	}

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<li class=\"calendar-list-slider-item\" data-bx-calendar-section=\"", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t<div class=\"calendar-list-slider-item-name\">", "</div>\n\t\t\t\t\t</li>\n\t\t\t\t"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["<ul class=\"calendar-list-slider-container\"></ul>"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-list-slider-widget-content\">\n\t\t\t\t\t<div class=\"calendar-list-slider-widget-content-block\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t<div>\n\t\t\t\t\t\t\t\t\t<span class=\"calendar-list-slider-card-section-title-text\">\n\t\t\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<div>\n\t\t\t\t\t\t\t\t<span class=\"calendar-list-slider-card-section-title-text\">\n\t\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t"]);

	  _templateObject4$1 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-list-slider-btn-container\">\n\t\t\t\t<button \n\t\t\t\t\tclass=\"ui-btn ui-btn-sm ui-btn-primary\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t>", "</button>\n\t\t\t\t<button \n\t\t\t\t\tclass=\"ui-btn ui-btn-link\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t>", "</button>\n\t\t\t</div>"]);

	  _templateObject3$1 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-list-slider-sections-wrap\"></div>"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div></div>"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var TrackingUsersForm = /*#__PURE__*/function () {
	  function TrackingUsersForm() {
	    var _this = this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, TrackingUsersForm);
	    babelHelpers.defineProperty(this, "DOM", {});
	    babelHelpers.defineProperty(this, "isCreated", false);
	    this.DOM.outerWrap = options.wrap;
	    this.trackingUsers = options.trackingUsers || [];
	    this.trackingUserIdList = this.trackingUsers.map(function (item) {
	      return parseInt(item.ID);
	    });
	    this.CHECKED_CLASS = 'calendar-list-slider-item-checkbox-checked';
	    this.selectorId = 'add-tracking' + calendar_util.Util.getRandomInt();
	    this.closeCallback = options.closeCallback;
	    this.superposedSections = main_core.Type.isArray(options.superposedSections) ? options.superposedSections : [];
	    this.selected = {};
	    this.superposedSections.forEach(function (section) {
	      _this.selected[section.id] = true;
	    }, this);
	    this.isCreated = false;
	    this.keyHandlerBinded = this.keyHandler.bind(this);
	  }

	  babelHelpers.createClass(TrackingUsersForm, [{
	    key: "show",
	    value: function show() {
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
	  }, {
	    key: "close",
	    value: function close() {
	      main_core.Event.unbind(document, 'keydown', this.keyHandlerBinded);
	      this.isOpenedState = false;
	      main_core.Dom.removeClass(this.DOM.outerWrap, 'show');
	      this.DOM.outerWrap.style.cssText = '';

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
	      if (!this.DOM.innerWrap) {
	        this.DOM.innerWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_templateObject$1()));
	      }

	      this.selectorWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create('DIV', {
	        props: {
	          className: 'calendar-list-slider-selector-wrap'
	        }
	      }));
	      this.userTagSelector = new ui_entitySelector.TagSelector({
	        dialogOptions: {
	          context: 'CALENDAR',
	          preselectedItems: this.trackingUsers.map(function (item) {
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
	      this.userTagSelector.renderTo(this.selectorWrap); // List of sections

	      this.sectionsWrap = this.DOM.innerWrap.appendChild(main_core.Tag.render(_templateObject2$1()));
	      this.createButtons();
	      this.isCreated = true;
	    }
	  }, {
	    key: "createButtons",
	    value: function createButtons() {
	      this.DOM.innerWrap.appendChild(main_core.Tag.render(_templateObject3$1(), this.save.bind(this), main_core.Loc.getMessage('EC_SEC_SLIDER_SAVE'), this.close.bind(this), main_core.Loc.getMessage('EC_SEC_SLIDER_CANCEL')));
	    }
	  }, {
	    key: "handleUserSelectorChanges",
	    value: function handleUserSelectorChanges() {
	      var _this2 = this;

	      var selectedItems = this.userTagSelector.getDialog().getSelectedItems();
	      this.trackingUserIdList = [];
	      selectedItems.forEach(function (item) {
	        if (item.entityId === 'user') {
	          _this2.trackingUserIdList.push(item.id);
	        }
	      });
	      this.updateSectionList();
	    }
	  }, {
	    key: "save",
	    value: function save() {
	      BX.ajax.runAction('calendar.api.calendarajax.setTrackingSections', {
	        data: {
	          userIdList: this.trackingUserIdList,
	          sections: this.prepareTrackingSections(),
	          type: 'users'
	        }
	      }).then(function (response) {
	        location.reload();
	      }, function (response) {
	        calendar_util.Util.displayError(response.errors);
	      });
	      this.close();
	    }
	  }, {
	    key: "prepareTrackingSections",
	    value: function prepareTrackingSections() {
	      var _this3 = this;

	      var sections = [];
	      this.superposedSections.forEach(function (section) {
	        sections.push(parseInt(section.id));
	      }, this);

	      var _loop = function _loop(id) {
	        if (_this3.sectionIndex.hasOwnProperty(id) && _this3.sectionIndex[id].checkbox) {
	          if (main_core.Dom.hasClass(_this3.sectionIndex[id].checkbox, _this3.CHECKED_CLASS)) {
	            if (!sections.includes(parseInt(id))) {
	              sections.push(parseInt(id));
	            }
	          } else if (sections.includes(parseInt(id))) {
	            sections = sections.filter(function (section) {
	              return parseInt(section) !== parseInt(id);
	            });
	          }
	        }
	      };

	      for (var id in this.sectionIndex) {
	        _loop(id);
	      }

	      return sections;
	    }
	  }, {
	    key: "updateSectionList",
	    value: function updateSectionList(delayExecution) {
	      var _this4 = this;

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
	        this.updateSectionTimeout = setTimeout(function () {
	          _this4.updateSectionList(false);
	        }, 300);
	        return;
	      }

	      this.checkInnerWrapHeight();
	      BX.ajax.runAction('calendar.api.calendarajax.getTrackingSections', {
	        data: {
	          userIdList: this.trackingUserIdList,
	          type: 'users'
	        }
	      }).then( // Success
	      function (response) {
	        main_core.Dom.clean(_this4.sectionsWrap);
	        _this4.sectionIndex = {};

	        _this4.checkInnerWrapHeight(); // Users calendars


	        response.data.users.forEach(function (user) {
	          var sections = response.data.sections.filter(function (section) {
	            return parseInt(section.OWNER_ID) === parseInt(user.ID);
	          });

	          _this4.sectionsWrap.appendChild(main_core.Tag.render(_templateObject4$1(), main_core.Text.encode(user.FORMATTED_NAME)));

	          if (sections.length > 0) {
	            _this4.createSectionBlock({
	              sectionList: sections,
	              wrap: _this4.sectionsWrap
	            });
	          } else {
	            _this4.sectionsWrap.appendChild(main_core.Tag.render(_templateObject5(), main_core.Loc.getMessage('EC_SEC_SLIDER_NO_SECTIONS')));
	          }
	        });
	      }, function (response) {
	        calendar_util.Util.displayError(response.errors);
	      });
	    }
	  }, {
	    key: "createSectionBlock",
	    value: function createSectionBlock() {
	      var _this5 = this;

	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var result = false;

	      if (main_core.Type.isArray(params.sectionList) && params.sectionList.length && main_core.Type.isElementNode(params.wrap)) {
	        var listWrap;
	        params.wrap.appendChild(main_core.Tag.render(_templateObject6(), listWrap = main_core.Tag.render(_templateObject7())));
	        main_core.Event.bind(listWrap, 'click', this.sectionClick.bind(this));
	        params.sectionList.forEach(function (section) {
	          var id = section.ID.toString();
	          var checkbox;
	          var li = listWrap.appendChild(main_core.Tag.render(_templateObject8(), id, checkbox = main_core.Tag.render(_templateObject9(), section.COLOR), main_core.Text.encode(section.NAME)));
	          _this5.sectionIndex[id] = {
	            item: li,
	            checkbox: checkbox
	          };

	          if (_this5.selected[id] || !main_core.Type.isArray(_this5.firstTrackingUserIdList) || !_this5.firstTrackingUserIdList.includes(parseInt(section.OWNER_ID))) {
	            main_core.Dom.addClass(checkbox, _this5.CHECKED_CLASS);
	          }
	        });
	      }

	      return result;
	    }
	  }, {
	    key: "sectionClick",
	    value: function sectionClick(e) {
	      var target = calendar_util.Util.findTargetNode(e.target || e.srcElement, this.DOM.outerWrap);

	      if (main_core.Type.isElementNode(target)) {
	        if (target.getAttribute('data-bx-calendar-section') !== null) {
	          var id = target.getAttribute('data-bx-calendar-section');

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
	  }, {
	    key: "keyHandler",
	    value: function keyHandler(e) {
	      if (e.keyCode === calendar_util.Util.getKeyCode('escape')) {
	        this.close();
	      } else if (e.keyCode === calendar_util.Util.getKeyCode('enter')) {
	        this.save();
	      }
	    }
	  }, {
	    key: "checkInnerWrapHeight",
	    value: function checkInnerWrapHeight() {
	      var _this6 = this;

	      if (this.checkHeightTimeout) {
	        this.checkHeightTimeout = clearTimeout(this.checkHeightTimeout);
	      }

	      this.checkHeightTimeout = setTimeout(function () {
	        if (main_core.Dom.hasClass(_this6.DOM.outerWrap, 'show')) {
	          if (_this6.DOM.outerWrap.offsetHeight - _this6.DOM.innerWrap.offsetHeight < 36) {
	            _this6.DOM.outerWrap.style.maxHeight = parseInt(_this6.DOM.innerWrap.offsetHeight) + 200 + 'px';
	          }
	        } else {
	          _this6.DOM.outerWrap.style.maxHeight = '';
	        }
	      }, 300);
	    }
	  }]);
	  return TrackingUsersForm;
	}();

	function _templateObject2$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-list-slider-sections-wrap\"></div>"]);

	  _templateObject2$2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div></div>"]);

	  _templateObject$2 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var TrackingGroupsForm = /*#__PURE__*/function (_TrackingUsersForm) {
	  babelHelpers.inherits(TrackingGroupsForm, _TrackingUsersForm);

	  function TrackingGroupsForm() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, TrackingGroupsForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TrackingGroupsForm).call(this, options));
	    _this.trackingGroupIdList = options.trackingGroups || [];
	    return _this;
	  }

	  babelHelpers.createClass(TrackingGroupsForm, [{
	    key: "create",
	    value: function create() {
	      if (!this.DOM.innerWrap) {
	        this.DOM.innerWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_templateObject$2()));
	      }

	      this.selectorWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create('DIV', {
	        props: {
	          className: 'calendar-list-slider-selector-wrap'
	        }
	      }));
	      this.groupTagSelector = new ui_entitySelector.TagSelector({
	        dialogOptions: {
	          context: 'CALENDAR',
	          preselectedItems: this.trackingGroupIdList.map(function (id) {
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
	      this.groupTagSelector.renderTo(this.selectorWrap); // List of sections

	      this.sectionsWrap = this.DOM.innerWrap.appendChild(main_core.Tag.render(_templateObject2$2()));
	      this.createButtons();
	      this.isCreated = true;
	    }
	  }, {
	    key: "handleGroupSelectorChanges",
	    value: function handleGroupSelectorChanges() {
	      var _this2 = this;

	      var selectedItems = this.groupTagSelector.getDialog().getSelectedItems();
	      this.trackingGroupIdList = [];
	      selectedItems.forEach(function (item) {
	        if (item.entityId === 'project') {
	          _this2.trackingGroupIdList.push(item.id);
	        }
	      });
	      this.updateSectionList();
	    }
	  }, {
	    key: "updateSectionList",
	    value: function updateSectionList() {
	      var _this3 = this;

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
	      }).then(function (response) {
	        main_core.Dom.clean(_this3.sectionsWrap);
	        _this3.sectionIndex = {};

	        _this3.checkInnerWrapHeight(); // Groups calendars


	        _this3.createSectionBlock({
	          sectionList: response.data.sections,
	          wrap: _this3.sectionsWrap
	        });
	      }, function (response) {
	        calendar_util.Util.displayError(response.errors);
	      });
	    }
	  }]);
	  return TrackingGroupsForm;
	}(TrackingUsersForm);

	function _templateObject3$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t<div>\n\t\t\t\t\t\t\t\t\t<span class=\"calendar-list-slider-card-section-title-text\">\n\t\t\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t"]);

	  _templateObject3$2 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-list-slider-sections-wrap\"></div>"]);

	  _templateObject2$3 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div></div>"]);

	  _templateObject$3 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var TrackingTypesForm = /*#__PURE__*/function (_TrackingUsersForm) {
	  babelHelpers.inherits(TrackingTypesForm, _TrackingUsersForm);

	  function TrackingTypesForm() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, TrackingTypesForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TrackingTypesForm).call(this, options));
	    _this.trackingGroups = options.trackingGroups || [];
	    _this.selectGroups = true;
	    _this.selectUsers = false;
	    _this.addLinkMessage = main_core.Loc.getMessage('EC_SEC_SLIDER_SELECT_GROUPS');
	    return _this;
	  }

	  babelHelpers.createClass(TrackingTypesForm, [{
	    key: "show",
	    value: function show() {
	      if (!this.isCreated) {
	        this.create();
	      }

	      this.updateSectionList();
	      this.isOpenedState = true;
	      main_core.Dom.addClass(this.DOM.outerWrap, 'show');
	    }
	  }, {
	    key: "create",
	    value: function create() {
	      if (!this.DOM.innerWrap) {
	        this.DOM.innerWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_templateObject$3()));
	      } // List of sections


	      this.sectionsWrap = this.DOM.innerWrap.appendChild(main_core.Tag.render(_templateObject2$3()));
	      this.createButtons();
	      this.isCreated = true;
	    }
	  }, {
	    key: "updateSectionList",
	    value: function updateSectionList() {
	      var _this2 = this;

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
	      }).then(function (response) {
	        main_core.Dom.clean(_this2.sectionsWrap);
	        _this2.sectionIndex = {};

	        _this2.checkInnerWrapHeight();

	        if (main_core.Type.isArray(response.data.sections) && response.data.sections.length) {
	          _this2.createSectionBlock({
	            sectionList: response.data.sections,
	            wrap: _this2.sectionsWrap
	          });
	        } else {
	          _this2.sectionsWrap.appendChild(main_core.Tag.render(_templateObject3$2(), main_core.Loc.getMessage('EC_SEC_SLIDER_NO_SECTIONS')));
	        }
	      }, function (response) {
	        calendar_util.Util.displayError(response.errors);
	      });
	      this.checkInnerWrapHeight();
	    }
	  }, {
	    key: "save",
	    value: function save() {
	      BX.ajax.runAction('calendar.api.calendarajax.setTrackingSections', {
	        data: {
	          sections: this.prepareTrackingSections()
	        }
	      }).then(function (response) {
	        location.reload();
	      }, function (response) {
	        calendar_util.Util.displayError(response.errors);
	      });
	      this.close();
	    }
	  }]);
	  return TrackingTypesForm;
	}(TrackingUsersForm);

	function _templateObject$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div>\n\t\t\t\t\t\t<div class=\"calendar-list-slider-card-widget-title\">\n\t\t\t\t\t\t\t<span class=\"calendar-list-slider-card-widget-title-text\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject$4 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var SectionInterface = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(SectionInterface, _EventEmitter);

	  function SectionInterface(_ref) {
	    var _this;

	    var calendarContext = _ref.calendarContext,
	        readonly = _ref.readonly,
	        sectionManager = _ref.sectionManager;
	    babelHelpers.classCallCheck(this, SectionInterface);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SectionInterface).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "name", 'sectioninterface');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "uid", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "DOM", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "SLIDER_WIDTH", 400);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "SLIDER_DURATION", 80);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "sliderId", "calendar:section-slider");
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "denyClose", false);

	    _this.setEventNamespace('BX.Calendar.SectionInterface');

	    _this.sectionManager = sectionManager;
	    _this.calendarContext = calendarContext;
	    _this.readonly = readonly;
	    _this.BX = calendar_util.Util.getBX();
	    _this.sliderOnClose = _this.hide.bind(babelHelpers.assertThisInitialized(_this));
	    _this.deleteSectionHandlerBinded = _this.deleteSectionHandler.bind(babelHelpers.assertThisInitialized(_this));
	    _this.refreshSectionListBinded = _this.refreshSectionList.bind(babelHelpers.assertThisInitialized(_this));

	    if (_this.calendarContext.util.config.accessNames) {
	      var _this$calendarContext, _this$calendarContext2, _this$calendarContext3;

	      calendar_util.Util.setAccessNames((_this$calendarContext = _this.calendarContext) === null || _this$calendarContext === void 0 ? void 0 : (_this$calendarContext2 = _this$calendarContext.util) === null || _this$calendarContext2 === void 0 ? void 0 : (_this$calendarContext3 = _this$calendarContext2.config) === null || _this$calendarContext3 === void 0 ? void 0 : _this$calendarContext3.accessNames);
	    }

	    return _this;
	  }

	  babelHelpers.createClass(SectionInterface, [{
	    key: "show",
	    value: function show() {
	      this.BX.SidePanel.Instance.open(this.sliderId, {
	        contentCallback: this.createContent.bind(this),
	        width: this.SLIDER_WIDTH,
	        animationDuration: this.SLIDER_DURATION,
	        events: {
	          onCloseByEsc: this.escHide.bind(this),
	          onClose: this.sliderOnClose,
	          onCloseComplete: this.destroy.bind(this),
	          onLoad: this.onLoadSlider.bind(this)
	        }
	      });
	      this.addEventEmitterSubscriptions();
	    }
	  }, {
	    key: "addEventEmitterSubscriptions",
	    value: function addEventEmitterSubscriptions() {
	      calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Section:delete', this.deleteSectionHandlerBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Section:pull-delete', this.deleteSectionHandlerBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Section:edit', this.refreshSectionListBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.subscribe('BX.Calendar.Section:pull-edit', this.refreshSectionListBinded);
	    }
	  }, {
	    key: "destroyEventEmitterSubscriptions",
	    value: function destroyEventEmitterSubscriptions() {
	      calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Section:delete', this.deleteSectionHandlerBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Section:pull-delete', this.deleteSectionHandlerBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Section:edit', this.refreshSectionListBinded);
	      calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Section:pull-edit', this.refreshSectionListBinded);
	    }
	  }, {
	    key: "escHide",
	    value: function escHide(event) {
	      if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId && this.denyClose) {
	        event.denyAction();
	      }
	    }
	  }, {
	    key: "hide",
	    value: function hide(event) {
	      if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId) {
	        this.closeForms();
	        this.destroyEventEmitterSubscriptions();
	      }
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      BX.SidePanel.Instance.close();
	    }
	  }, {
	    key: "destroy",
	    value: function destroy(event) {
	      if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId) {
	        this.destroyEventEmitterSubscriptions();
	        calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Section:delete', this.deleteSectionHandlerBinded);
	        calendar_util.Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Section:pull-delete', this.deleteSectionHandlerBinded);
	        BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", BX.proxy(this.destroy, this));
	        BX.SidePanel.Instance.destroy(this.sliderId);
	        delete this.DOM.sectionListWrap; //this.calendarContext.enableKeyHandler();

	        if (this.sectionActionMenu) {
	          this.sectionActionMenu.close();
	        }
	      }
	    }
	  }, {
	    key: "createContent",
	    value: function createContent() {
	      // this.BX.onCustomEvent(top, 'onCalendarBeforeCustomSliderCreate');
	      this.DOM.outerWrap = main_core.Dom.create('DIV', {
	        props: {
	          className: 'calendar-list-slider-wrap'
	        }
	      });
	      this.DOM.titleWrap = this.DOM.outerWrap.appendChild(main_core.Dom.create('DIV', {
	        props: {
	          className: 'calendar-list-slider-title-container'
	        },
	        html: '<div class="calendar-list-slider-title">' + main_core.Loc.getMessage('EC_SECTION_BUTTON') + '</div>'
	      }));

	      if (!this.readonly) {
	        // #1. Controls
	        this.createAddButton(); // #2. Forms

	        this.DOM.sectionFormWrap = this.DOM.outerWrap.appendChild(main_core.Dom.create('DIV', {
	          props: {
	            className: 'calendar-list-slider-card-widget calendar-list-slider-form-wrap'
	          },
	          html: '<div class="calendar-list-slider-card-widget-title"><span class="calendar-list-slider-card-widget-title-text">' + main_core.Loc.getMessage('EC_SEC_SLIDER_NEW_SECTION') + '</span></div>'
	        }));
	        this.DOM.trackingCompanyFormWrap = this.DOM.outerWrap.appendChild(main_core.Dom.create('DIV', {
	          props: {
	            className: 'calendar-list-slider-card-widget calendar-list-slider-form-wrap'
	          },
	          html: '<div class="calendar-list-slider-card-widget-title"><span class="calendar-list-slider-card-widget-title-text">' + main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_COMP') + '</span></div>'
	        }));
	        this.DOM.trackingUsersFormWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_templateObject$4(), main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_GROUP')));
	        this.DOM.trackingGroupsFormWrap = this.DOM.outerWrap.appendChild(main_core.Dom.create('DIV', {
	          props: {
	            className: 'calendar-list-slider-card-widget calendar-list-slider-form-wrap'
	          },
	          html: '<div class="calendar-list-slider-card-widget-title"><span class="calendar-list-slider-card-widget-title-text">' + main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_GROUP') + '</span></div>'
	        }));
	      } // #3. List of sections


	      this.createSectionList();
	      return this.DOM.outerWrap;
	    }
	  }, {
	    key: "onLoadSlider",
	    value: function onLoadSlider(event) {
	      this.slider = event.getSlider();
	      this.sliderId = this.slider.getUrl();
	      this.DOM.content = this.slider.layout.content; // Used to execute javasctipt and attach CSS from ajax responce
	      //this.BX.html(this.slider.layout.content, this.slider.getData().get("sliderContent"));
	      // this.initControls(this.uid);
	      // this.setFormValues();
	    }
	  }, {
	    key: "createSectionList",
	    value: function createSectionList() {
	      var _this2 = this;

	      var title;
	      this.sliderSections = this.sectionManager.getSections();
	      var type = this.sectionManager.calendarType;

	      if (type === 'user') {
	        title = main_core.Loc.getMessage('EC_SEC_SLIDER_MY_CALENDARS_LIST');
	      } else if (type === 'group') {
	        title = main_core.Loc.getMessage('EC_SEC_SLIDER_GROUP_CALENDARS_LIST');
	      } else {
	        title = main_core.Loc.getMessage('EC_SEC_SLIDER_TYPE_CALENDARS_LIST');
	      }

	      if (this.DOM.sectionListWrap) {
	        main_core.Dom.clean(this.DOM.sectionListWrap);
	        main_core.Dom.adjust(this.DOM.sectionListWrap, {
	          props: {
	            className: 'calendar-list-slider-card-widget'
	          },
	          html: '<div class="calendar-list-slider-card-widget-title"><span class="calendar-list-slider-card-widget-title-text">' + title + '</span></div>'
	        });
	      } else {
	        this.DOM.sectionListWrap = this.DOM.outerWrap.appendChild(main_core.Dom.create('DIV', {
	          props: {
	            className: 'calendar-list-slider-card-widget'
	          },
	          html: '<div class="calendar-list-slider-card-widget-title"><span class="calendar-list-slider-card-widget-title-text">' + title + '</span></div>'
	        }));
	      }

	      this.createSectionBlock({
	        wrap: this.DOM.sectionListWrap,
	        sectionList: this.sliderSections.filter(function (section) {
	          return section.belongsToView() || section.isPseudo();
	        })
	      }); // Company calendar

	      var sections = this.sliderSections.filter(function (section) {
	        return section.isCompanyCalendar() && !section.belongsToView();
	      });

	      if (sections.length > 0) {
	        this.DOM.sectionListWrap.appendChild(main_core.Dom.create('DIV', {
	          props: {
	            className: 'calendar-list-slider-card-section-title'
	          },
	          html: '<span class="calendar-list-slider-card-section-title-text">' + main_core.Loc.getMessage('EC_SEC_SLIDER_TITLE_COMP_CAL') + '</span>'
	        }));
	        this.createSectionBlock({
	          wrap: this.DOM.sectionListWrap,
	          sectionList: this.sliderSections.filter(function (section) {
	            return section.isCompanyCalendar();
	          })
	        });
	      } // Users calendars


	      this.calendarContext.util.getSuperposedTrackedUsers().forEach(function (user) {
	        var sections = _this2.sliderSections.filter(function (section) {
	          return !section.belongsToView() && section.type === 'user' && section.data.OWNER_ID === user.ID;
	        });

	        if (sections.length > 0) {
	          _this2.DOM.sectionListWrap.appendChild(main_core.Dom.create('DIV', {
	            props: {
	              className: 'calendar-list-slider-card-section-title'
	            },
	            html: '<span class="calendar-list-slider-card-section-title-text">' + BX.util.htmlspecialchars(user.FORMATTED_NAME) + '</span>'
	          }));

	          _this2.createSectionBlock({
	            wrap: _this2.DOM.sectionListWrap,
	            sectionList: sections
	          });
	        }
	      }, this); // Groups calendars

	      sections = this.sliderSections.filter(function (section) {
	        return !section.belongsToView() && section.type === 'group';
	      });

	      if (sections.length > 0) {
	        this.DOM.sectionListWrap.appendChild(main_core.Dom.create('DIV', {
	          props: {
	            className: 'calendar-list-slider-card-section-title'
	          },
	          html: '<span class="calendar-list-slider-card-section-title-text">' + main_core.Loc.getMessage('EC_SEC_SLIDER_TITLE_GROUP_CAL') + '</span>'
	        }));
	        this.createSectionBlock({
	          wrap: this.DOM.sectionListWrap,
	          sectionList: sections
	        });
	      }
	    }
	  }, {
	    key: "createAddButton",
	    value: function createAddButton() {
	      if (this.calendarContext.util.config.perm && this.calendarContext.util.config.perm.edit_section) {
	        var addButtonOuter = this.DOM.titleWrap.appendChild(main_core.Dom.create('SPAN', {
	          props: {
	            className: 'ui-btn-split ui-btn-light-border'
	          },
	          style: {
	            marginRight: 0
	          }
	        }));
	        this.DOM.addButton = addButtonOuter.appendChild(main_core.Dom.create('SPAN', {
	          props: {
	            className: 'ui-btn-main'
	          },
	          text: main_core.Loc.getMessage('EC_ADD')
	        }));
	        this.DOM.addButtonMore = addButtonOuter.appendChild(main_core.Dom.create('SPAN', {
	          props: {
	            className: 'ui-btn-extra'
	          }
	        }));
	        main_core.Event.bind(this.DOM.addButtonMore, 'click', this.showAddButtonPopup.bind(this));
	        main_core.Event.bind(this.DOM.addButton, 'click', this.showEditSectionForm.bind(this));
	      }
	    }
	  }, {
	    key: "showAddButtonPopup",
	    value: function showAddButtonPopup() {
	      var _this3 = this;

	      if (this.addBtnMenu && this.addBtnMenu.popupWindow && this.addBtnMenu.popupWindow.isShown()) {
	        return this.addBtnMenu.close();
	      }

	      var submenuClass = 'main-buttons-submenu-separator main-buttons-submenu-item main-buttons-hidden-label';
	      var menuItems = [{
	        html: '<span>' + main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_NEW_TITLE') + '</span>',
	        className: submenuClass
	      }, {
	        html: main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_NEW_MENU'),
	        onclick: function onclick() {
	          _this3.addBtnMenu.close();

	          _this3.showEditSectionForm();
	        }
	      }, {
	        html: '<span>' + main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_EXIST_TITLE') + '</span>',
	        className: submenuClass
	      }, {
	        html: main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_COMP'),
	        onclick: function onclick() {
	          _this3.addBtnMenu.close();

	          _this3.showTrackingTypesForm();
	        }
	      }, {
	        html: main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_USER'),
	        onclick: function onclick() {
	          _this3.addBtnMenu.close();

	          _this3.showTrackingUsersForm();
	        }
	      }, {
	        html: main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_GROUP'),
	        onclick: function onclick() {
	          _this3.addBtnMenu.close();

	          _this3.showTrackingGroupsForm();
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
	      this.addBtnMenu.show(); //Dom.addClass(_this.sectionField.select, 'active');
	      // this.denySliderClose();
	      // top.BX.addCustomEvent(this.addBtnMenu.popupWindow, 'onPopupClose', function()
	      // {
	      // 	_this.allowSliderClose();
	      // });
	    }
	  }, {
	    key: "createSectionBlock",
	    value: function createSectionBlock(_ref2) {
	      var sectionList = _ref2.sectionList,
	          wrap = _ref2.wrap;

	      if (main_core.Type.isArray(sectionList)) {
	        var listWrap = wrap.appendChild(main_core.Dom.create('DIV', {
	          props: {
	            className: 'calendar-list-slider-widget-content'
	          }
	        })).appendChild(main_core.Dom.create('DIV', {
	          props: {
	            className: 'calendar-list-slider-widget-content-block'
	          }
	        })).appendChild(main_core.Dom.create('UL', {
	          props: {
	            className: 'calendar-list-slider-container'
	          }
	        }));
	        main_core.Event.bind(listWrap, 'click', this.sectionClickHandler.bind(this));
	        sectionList.forEach(function (section) {
	          if (!section.DOM) {
	            section.DOM = {};
	          }

	          var sectionId = section.id.toString();
	          var li = listWrap.appendChild(main_core.Dom.create('LI', {
	            props: {
	              className: 'calendar-list-slider-item'
	            },
	            attrs: {
	              'data-bx-calendar-section': sectionId
	            }
	          }));
	          var checkbox = li.appendChild(main_core.Dom.create('DIV', {
	            props: {
	              className: 'calendar-list-slider-item-checkbox' + (section.isShown() ? ' calendar-list-slider-item-checkbox-checked' : '')
	            },
	            style: {
	              backgroundColor: section.color
	            }
	          }));
	          var title = li.appendChild(main_core.Dom.create('DIV', {
	            props: {
	              className: 'calendar-list-slider-item-name',
	              title: section.name
	            },
	            text: section.name
	          }));
	          section.DOM.item = li;
	          section.DOM.checkbox = checkbox;
	          section.DOM.title = title; //if (sectionId !== 'tasks' || this.calendarContext.util.userIsOwner())

	          {
	            var actionCont = li.appendChild(main_core.Dom.create('DIV', {
	              props: {
	                className: 'calendar-list-slider-item-actions-container'
	              },
	              attrs: {
	                'data-bx-calendar-section-menu': sectionId
	              },
	              html: '<span class="calendar-list-slider-item-context-menu"></span>'
	            }));
	            section.DOM.actionCont = actionCont;
	          }
	        });
	      }
	    }
	  }, {
	    key: "sectionClickHandler",
	    value: function sectionClickHandler(e) {
	      var target = calendar_util.Util.findTargetNode(e.target || e.srcElement, this.DOM.outerWrap);

	      if (target && target.getAttribute) {
	        if (target.getAttribute('data-bx-calendar-section-menu') !== null) {
	          var sectionId = target.getAttribute('data-bx-calendar-section-menu');
	          sectionId = sectionId === 'tasks' ? sectionId : parseInt(sectionId);
	          this.showSectionMenu(this.sectionManager.getSection(sectionId), target);
	        } else if (target.getAttribute('data-bx-calendar-section') !== null) {
	          this.switchSection(this.sectionManager.getSection(target.getAttribute('data-bx-calendar-section')));
	        }
	      }
	    }
	  }, {
	    key: "switchSection",
	    value: function switchSection(section) {
	      var checkboxNodes = this.DOM.sectionListWrap.querySelectorAll('.calendar-list-slider-item[data-bx-calendar-section=\'' + section.id + '\'] .calendar-list-slider-item-checkbox');

	      for (var i = 0; i < checkboxNodes.length; i++) {
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
	      } // TODO: should use eventEmtter


	      this.calendarContext.reload();
	    }
	  }, {
	    key: "showSectionMenu",
	    value: function showSectionMenu(section, menuItemNode) {
	      var _this4 = this;

	      var menuItems = [];
	      var itemNode = menuItemNode.closest('[data-bx-calendar-section]');

	      if (main_core.Type.isElementNode(itemNode)) {
	        main_core.Dom.addClass(itemNode, 'active');
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
	          onclick: function onclick() {
	            _this4.sectionActionMenu.close();

	            _this4.showEditSectionForm({
	              section: section
	            });
	          }
	        });
	      }

	      if (section.isSuperposed() && !section.belongsToView()) {
	        menuItems.push({
	          text: main_core.Loc.getMessage('EC_SEC_HIDE'),
	          onclick: function onclick() {
	            _this4.hideSuperposedHandler(section);

	            _this4.sectionActionMenu.close();
	          }
	        });
	      }

	      if (section.canBeConnectedToOutlook()) {
	        menuItems.push({
	          text: main_core.Loc.getMessage('EC_SEC_CONNECT_TO_OUTLOOK'),
	          onclick: function onclick() {
	            _this4.sectionActionMenu.close();

	            section.connectToOutlook();

	            _this4.close();
	          }
	        });
	      }

	      if (!section.isPseudo() && section.data.EXPORT && section.data.EXPORT.LINK) {
	        menuItems.push({
	          text: main_core.Loc.getMessage('EC_ACTION_EXPORT'),
	          onclick: function onclick() {
	            _this4.sectionActionMenu.close();

	            var options = {
	              sectionLink: section.data.EXPORT.LINK,
	              calendarPath: _this4.calendarContext.util.config.path
	            };

	            if (BX.Calendar.Sync.Interface.IcalSyncPopup.checkPathes(options)) {
	              BX.Calendar.Sync.Interface.IcalSyncPopup.createInstance(options).show();
	            } else {
	              BX.Calendar.Sync.Interface.IcalSyncPopup.showPopupWithPathesError();
	            }
	          }
	        });
	      }

	      if (section.canDo('edit_section') && section.belongsToView() && !section.isPseudo()) {
	        menuItems.push({
	          text: main_core.Loc.getMessage('EC_SEC_DELETE'),
	          onclick: function onclick() {
	            _this4.sectionActionMenu.close();

	            section.remove();
	          }
	        });
	      }

	      if ((section.isGoogle() || section.isCalDav()) && section.canDo('edit_section')) {
	        menuItems.push({
	          text: main_core.Loc.getMessage('EC_ACTION_REFRESH'),
	          onclick: function onclick() {
	            _this4.sectionActionMenu.close();

	            _this4.calendarContext.reload({
	              syncGoogle: true
	            });

	            _this4.close();
	          }
	        });

	        if (this.calendarContext.syncInterface && this.calendarContext.syncInterface.syncButton) {
	          menuItems.push({
	            text: main_core.Loc.getMessage('EC_ACTION_EXTERNAL_ADJUST'),
	            onclick: function onclick() {
	              _this4.sectionActionMenu.close();

	              _this4.calendarContext.syncInterface.syncButton.handleClick();
	            }
	          });
	        }

	        menuItems.push({
	          text: main_core.Loc.getMessage('EC_ACTION_HIDE'),
	          onclick: function onclick() {
	            _this4.sectionActionMenu.close();

	            section.hideGoogle();
	          }
	        });
	      }

	      if (section.isPseudo()) {
	        menuItems.push({
	          text: main_core.Loc.getMessage('EC_SEC_EDIT'),
	          onclick: function onclick() {
	            _this4.sectionActionMenu.close();

	            _this4.showEditSectionForm({
	              section: section
	            });
	          }
	        });
	        menuItems.push({
	          text: main_core.Loc.getMessage('EC_SEC_TASK_HIDE'),
	          onclick: function onclick() {
	            _this4.sectionActionMenu.close();

	            BX.userOptions.save('calendar', 'user_settings', 'showTasks', 'N');
	            main_core.Dom.addClass(section.DOM.item, 'calendar-list-slider-item-disappearing');
	            setTimeout(function () {
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
	        this.sectionActionMenu.popupWindow.subscribe('onClose', function () {
	          if (main_core.Type.isElementNode(itemNode)) {
	            main_core.Dom.removeClass(itemNode, 'active');
	          }

	          _this4.allowSliderClose();
	        });
	        this.denySliderClose();
	      }
	    }
	  }, {
	    key: "denySliderClose",
	    value: function denySliderClose() {
	      this.denyClose = true;
	    }
	  }, {
	    key: "allowSliderClose",
	    value: function allowSliderClose() {
	      this.denyClose = false;
	    }
	  }, {
	    key: "closeForms",
	    value: function closeForms() {
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
	  }, {
	    key: "showEditSectionForm",
	    value: function showEditSectionForm() {
	      var _this5 = this;

	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.closeForms();
	      var formTitleNode = this.DOM.sectionFormWrap.querySelector('.calendar-list-slider-card-widget-title-text');
	      this.editSectionForm = new EditForm({
	        wrap: this.DOM.sectionFormWrap,
	        sectionAccessTasks: this.sectionManager.getSectionAccessTasks(),
	        sectionManager: this.sectionManager,
	        closeCallback: function closeCallback() {
	          _this5.allowSliderClose();
	        }
	      });
	      var showAccessControl = true;

	      if (params.section && (!params.section.belongsToView() || params.section.isPseudo())) {
	        formTitleNode.innerHTML = main_core.Loc.getMessage('EC_SEC_SLIDER_EDIT_SECTION_PERSONAL');
	        showAccessControl = false;
	      } else if (params.section && params.section.id) {
	        formTitleNode.innerHTML = main_core.Loc.getMessage('EC_SEC_SLIDER_EDIT_SECTION');
	        showAccessControl = params.section.canDo('access');
	      } else {
	        formTitleNode.innerHTML = main_core.Loc.getMessage('EC_SEC_SLIDER_NEW_SECTION');
	      }

	      this.editSectionForm.show({
	        showAccess: showAccessControl,
	        section: params.section || {
	          color: calendar_util.Util.getRandomColor(),
	          access: this.sectionManager.getDefaultSectionAccess()
	        }
	      });
	      this.denySliderClose();
	    }
	  }, {
	    key: "showTrackingTypesForm",
	    value: function showTrackingTypesForm() {
	      var _this6 = this;

	      this.closeForms();

	      if (!this.trackingTypesForm) {
	        this.trackingTypesForm = new TrackingTypesForm({
	          wrap: this.DOM.trackingCompanyFormWrap,
	          superposedSections: this.sectionManager.getSuperposedSectionList(),
	          closeCallback: function closeCallback() {
	            _this6.allowSliderClose();
	          }
	        });
	      }

	      this.trackingTypesForm.show();
	      this.denySliderClose();
	    }
	  }, {
	    key: "showTrackingUsersForm",
	    value: function showTrackingUsersForm() {
	      var _this7 = this;

	      this.closeForms();

	      if (!this.trackingUsersForm) {
	        this.trackingUsersForm = new TrackingUsersForm({
	          wrap: this.DOM.trackingUsersFormWrap,
	          trackingUsers: this.calendarContext.util.getSuperposedTrackedUsers(),
	          superposedSections: this.sectionManager.getSuperposedSectionList(),
	          closeCallback: function closeCallback() {
	            _this7.allowSliderClose();
	          }
	        });
	      }

	      this.trackingUsersForm.show();
	      this.denySliderClose();
	    }
	  }, {
	    key: "showTrackingGroupsForm",
	    value: function showTrackingGroupsForm() {
	      var _this8 = this;

	      this.closeForms();

	      if (!this.trackingGroupsForm) {
	        var superposedSections = this.sectionManager.getSuperposedSectionList();
	        var trackingGroups = this.calendarContext.util.getSuperposedTrackedGroups();
	        superposedSections.forEach(function (section) {
	          if (section.getType() === 'group' && !trackingGroups.includes(section.getOwnerId())) {
	            trackingGroups.push(section.getOwnerId());
	          }
	        });
	        this.trackingGroupsForm = new TrackingGroupsForm({
	          wrap: this.DOM.trackingGroupsFormWrap,
	          trackingGroups: trackingGroups,
	          superposedSections: superposedSections,
	          closeCallback: function closeCallback() {
	            _this8.allowSliderClose();
	          }
	        });
	      }

	      this.trackingGroupsForm.show();
	      this.denySliderClose();
	    }
	  }, {
	    key: "deleteSectionHandler",
	    value: function deleteSectionHandler(event) {
	      var _this9 = this;

	      if (event && event instanceof calendar_util.Util.getBX().Event.BaseEvent) {
	        var data = event.getData();
	        var sectionId = parseInt(data.sectionId, 10);
	        this.sliderSections.forEach(function (section, index) {
	          if (parseInt(section.id) === sectionId && section.DOM && section.DOM.item) {
	            main_core.Dom.addClass(section.DOM.item, 'calendar-list-slider-item-disappearing');
	            setTimeout(function () {
	              main_core.Dom.clean(section.DOM.item, true);
	              _this9.sliderSections = BX.util.deleteFromArray(_this9.sliderSections, index);
	            }, 300);
	          }
	        }, this);
	      }
	    }
	  }, {
	    key: "hideSuperposedHandler",
	    value: function hideSuperposedHandler(section) {
	      var superposedSections = this.sectionManager.getSuperposedSectionList();
	      var sections = [];
	      var i;

	      for (i = 0; i < superposedSections.length; i++) {
	        if (parseInt(section.id) !== parseInt(superposedSections[i].id)) {
	          sections.push(parseInt(superposedSections[i].id));
	        }
	      }

	      BX.ajax.runAction('calendar.api.calendarajax.setTrackingSections', {
	        data: {
	          sections: sections
	        }
	      }).then( // Success
	      function (response) {
	        BX.reload();
	      }, // Failure
	      function (response) {
	        calendar_util.Util.displayError(response.errors);
	      });
	    }
	  }, {
	    key: "refreshSectionList",
	    value: function refreshSectionList() {
	      this.createSectionList();
	    }
	  }]);
	  return SectionInterface;
	}(main_core_events.EventEmitter);

	exports.SectionInterface = SectionInterface;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Calendar,BX.Calendar.Controls,BX.Event,BX.UI.EntitySelector,BX,BX.Calendar,BX.Calendar));
//# sourceMappingURL=sectioninterface.bundle.js.map
