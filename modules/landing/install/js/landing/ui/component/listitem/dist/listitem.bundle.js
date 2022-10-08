this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,ui_designTokens,main_core,main_core_events,landing_loc,landing_ui_form_baseform,landing_ui_component_iconbutton,landing_ui_component_internal) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5;

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var ListItem = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ListItem, _EventEmitter);

	  function ListItem(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, ListItem);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ListItem).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Component.ListItem');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    _this.onEditButtonClick = _this.onEditButtonClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onRemoveButtonClick = _this.onRemoveButtonClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onFormChange = _this.onFormChange.bind(babelHelpers.assertThisInitialized(_this));
	    _this.options = _objectSpread({}, options);
	    _this.cache = new main_core.Cache.MemoryCache();

	    if (main_core.Type.isDomNode(_this.options.appendTo)) {
	      _this.appendTo(_this.options.appendTo);
	    } else if (main_core.Type.isDomNode(_this.options.prependTo)) {
	      _this.prependTo(_this.options.prependTo);
	    }

	    if (main_core.Type.isArrayFilled(_this.options.actions)) {
	      _this.setActionsButtons(babelHelpers.toConsumableArray(_this.options.actions));
	    }

	    if (_this.options.error) {
	      main_core.Dom.addClass(_this.getLayout(), 'landing-ui-error');
	    }

	    return _this;
	  }

	  babelHelpers.createClass(ListItem, [{
	    key: "setActionsButtons",
	    value: function setActionsButtons(actionsButtons) {
	      this.cache.set('actionsButtons', actionsButtons);
	    }
	  }, {
	    key: "getActionsButtons",
	    value: function getActionsButtons() {
	      return this.cache.get('actionsButtons', []);
	    }
	  }, {
	    key: "appendTo",
	    value: function appendTo(target) {
	      main_core.Dom.append(this.getLayout(), target);
	    }
	  }, {
	    key: "prependTo",
	    value: function prependTo(target) {
	      main_core.Dom.prepend(this.getLayout(), target);
	    }
	  }, {
	    key: "getDragButtonLayout",
	    value: function getDragButtonLayout() {
	      return this.cache.remember('dragButtonLayout', function () {
	        var button = new landing_ui_component_iconbutton.IconButton({
	          type: landing_ui_component_iconbutton.IconButton.Types.drag,
	          title: landing_loc.Loc.getMessage('LANDING_UI_COMPONENT_LIST_ITEM_DRAG_TITLE'),
	          style: {
	            position: 'absolute',
	            left: '1px',
	            width: '8px'
	          }
	        });
	        return button.getLayout();
	      });
	    }
	  }, {
	    key: "getTitleLayout",
	    value: function getTitleLayout() {
	      var _this2 = this;

	      return this.cache.remember('titleLayout', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-component-list-item-text-title\">", "</div>\n\t\t\t"])), main_core.Text.encode(_this2.options.title));
	      });
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      this.getTitleLayout().textContent = title;
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      return this.getTitleLayout().innerText;
	    }
	  }, {
	    key: "getDescriptionLayout",
	    value: function getDescriptionLayout() {
	      var _this3 = this;

	      return this.cache.remember('descriptionLayout', function () {
	        return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-component-list-item-text-description\">", "</div>\n\t\t\t"])), main_core.Text.encode(_this3.options.description));
	      });
	    }
	  }, {
	    key: "setDescription",
	    value: function setDescription(description) {
	      this.getDescriptionLayout().textContent = description;
	    }
	  }, {
	    key: "getDescription",
	    value: function getDescription() {
	      return this.getDescriptionLayout().innerText;
	    }
	  }, {
	    key: "getEditButtonLayout",
	    value: function getEditButtonLayout() {
	      var _this4 = this;

	      return this.cache.remember('editButtonLayout', function () {
	        var button = new landing_ui_component_iconbutton.IconButton({
	          type: landing_ui_component_iconbutton.IconButton.Types.edit,
	          onClick: _this4.onEditButtonClick,
	          title: landing_loc.Loc.getMessage('LANDING_UI_COMPONENT_LIST_ITEM_EDIT_TITLE')
	        });
	        return button.getLayout();
	      });
	    }
	  }, {
	    key: "onEditButtonClick",
	    value: function onEditButtonClick(event) {
	      event.preventDefault();
	      var editEvent = new main_core_events.BaseEvent();
	      this.emit('onEdit', editEvent);

	      if (!editEvent.isDefaultPrevented()) {
	        if (!this.isOpened()) {
	          this.open();
	        } else {
	          this.close();
	        }
	      }
	    }
	  }, {
	    key: "getRemoveButtonLayout",
	    value: function getRemoveButtonLayout() {
	      var _this5 = this;

	      return this.cache.remember('removeButtonLayout', function () {
	        var button = new landing_ui_component_iconbutton.IconButton({
	          type: landing_ui_component_iconbutton.IconButton.Types.remove,
	          onClick: _this5.onRemoveButtonClick,
	          title: landing_loc.Loc.getMessage('LANDING_UI_COMPONENT_LIST_ITEM_REMOVE_TITLE')
	        });
	        return button.getLayout();
	      });
	    }
	  }, {
	    key: "onRemoveButtonClick",
	    value: function onRemoveButtonClick(event) {
	      event.preventDefault();
	      main_core.Dom.remove(this.getLayout());
	      this.emit('onRemove');
	    }
	  }, {
	    key: "getHeaderLayout",
	    value: function getHeaderLayout() {
	      var _this6 = this;

	      return this.cache.remember('headerLayout', function () {
	        return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-component-list-item-header\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"landing-ui-component-list-item-text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"landing-ui-component-list-item-actions\">\n\t\t\t\t\t\t<div class=\"landing-ui-component-list-item-actions-custom\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), _this6.options.draggable ? _this6.getDragButtonLayout() : '', _this6.getTitleLayout(), _this6.getDescriptionLayout(), _this6.getActionsButtons().map(function (button) {
	          return button.getLayout();
	        }), _this6.options.editable ? _this6.getEditButtonLayout() : '', _this6.options.removable ? _this6.getRemoveButtonLayout() : '');
	      });
	    }
	  }, {
	    key: "getBodyLayout",
	    value: function getBodyLayout() {
	      return this.cache.remember('bodyLayout', function () {
	        return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-component-list-item-body\"></div>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this7 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div \n\t\t\t\t\tclass=\"landing-ui-component-list-item\" \n\t\t\t\t\tdata-id=\"", "\"\n\t\t\t\t\tdata-type=\"", "\"\n\t\t\t\t\tdata-style=\"", "\"\n\t\t\t\t>\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this7.options.id, _this7.options.type, _this7.options.isSeparator ? 'separator' : 'item', _this7.getHeaderLayout(), _this7.getBodyLayout());
	      });
	    }
	  }, {
	    key: "open",
	    value: function open() {
	      main_core.Dom.addClass(this.getLayout(), 'landing-ui-component-list-item-opened');

	      if (!main_core.Type.isStringFilled(this.getBodyLayout().innerHTML)) {
	        if (this.options.form) {
	          main_core.Dom.append(this.options.form.getLayout(), this.getBodyLayout());
	          this.options.form.subscribe('onChange', this.onFormChange);
	        }
	      }
	    }
	  }, {
	    key: "isOpened",
	    value: function isOpened() {
	      return main_core.Dom.hasClass(this.getLayout(), 'landing-ui-component-list-item-opened');
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      main_core.Dom.removeClass(this.getLayout(), 'landing-ui-component-list-item-opened');
	    }
	  }, {
	    key: "onFormChange",
	    value: function onFormChange() {
	      this.emit('onFormChange');
	    }
	  }, {
	    key: "setId",
	    value: function setId(id) {
	      main_core.Dom.attr(this.getLayout(), 'data-id', id);
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return main_core.Dom.attr(this.getLayout(), 'data-id');
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var value = {
	        name: this.options.id
	      };

	      if (main_core.Type.isStringFilled(this.options.type)) {
	        value.type = this.options.type;
	      }

	      if (this.options.form) {
	        var formValue = this.options.form.serialize();
	        Object.assign(value, formValue);
	      }

	      if (this.options.content) {
	        value.content = this.options.content;
	      }

	      if (this.options.sourceOptions) {
	        var sourceOptions = main_core.Runtime.clone(this.options.sourceOptions);
	        Object.entries(sourceOptions).forEach(function (_ref) {
	          var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	              key = _ref2[0],
	              propValue = _ref2[1];

	          if (main_core.Type.isArray(propValue) && main_core.Type.isArray(value[key])) {
	            delete sourceOptions[key];
	          }
	        });
	        return main_core.Runtime.merge(sourceOptions, value);
	      }

	      return value;
	    }
	  }]);
	  return ListItem;
	}(main_core_events.EventEmitter);

	exports.ListItem = ListItem;

}((this.BX.Landing.UI.Component = this.BX.Landing.UI.Component || {}),BX,BX,BX.Event,BX.Landing,BX.Landing.UI.Form,BX.Landing.UI.Component,BX.Landing.UI.Component));
//# sourceMappingURL=listitem.bundle.js.map
