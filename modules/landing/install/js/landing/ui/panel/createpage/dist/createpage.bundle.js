this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,main_loader,landing_ui_panel_content,landing_loc,landing_backend,landing_env,landing_sliderhacks,landing_ui_field_textfield) {
	'use strict';

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-panel-create-page-fail\">\n\t\t\t\t\t<div class=\"landing-ui-panel-create-page-fail-header\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-panel-create-page-success\">\n\t\t\t\t<div class=\"landing-ui-panel-create-page-success-header\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"landing-ui-panel-create-page-actions\">\n\t\t\t\t\t<a href=\"", "\" target=\"_blank\">", "</a> &nbsp;\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	/**
	 * @memberOf BX.Landing.UI.Panel
	 */

	var CreatePage = /*#__PURE__*/function (_Content) {
	  babelHelpers.inherits(CreatePage, _Content);
	  babelHelpers.createClass(CreatePage, null, [{
	    key: "getInstance",
	    value: function getInstance() {
	      if (!CreatePage.instance) {
	        CreatePage.instance = new CreatePage('landing_create_page_panel', {
	          title: landing_loc.Loc.getMessage('LANDING_CREATE_PAGE_PANEL_TITLE')
	        });
	      }

	      return CreatePage.instance;
	    }
	  }]);

	  function CreatePage(id, data) {
	    var _this;

	    babelHelpers.classCallCheck(this, CreatePage);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CreatePage).call(this, id, data));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "cache", new main_core.Cache.MemoryCache());
	    main_core.Dom.addClass(_this.layout, 'landing-ui-panel-create-page');
	    main_core.Dom.addClass(_this.overlay, 'landing-ui-panel-create-page');

	    _this.appendFooterButton(new BX.Landing.UI.Button.BaseButton('save_block_content', {
	      text: BX.Landing.Loc.getMessage('BLOCK_SAVE'),
	      onClick: _this.onSave.bind(babelHelpers.assertThisInitialized(_this)),
	      className: 'landing-ui-button-content-save'
	    }));

	    _this.appendFooterButton(new BX.Landing.UI.Button.BaseButton('cancel_block_content', {
	      text: BX.Landing.Loc.getMessage('BLOCK_CANCEL'),
	      onClick: _this.hide.bind(babelHelpers.assertThisInitialized(_this)),
	      className: 'landing-ui-button-content-cancel'
	    }));

	    _this.renderTo(document.body);

	    return _this;
	  }

	  babelHelpers.createClass(CreatePage, [{
	    key: "getTitleField",
	    value: function getTitleField() {
	      return this.cache.remember('titleField', function () {
	        return new landing_ui_field_textfield.TextField({
	          title: landing_loc.Loc.getMessage('LANDING_CREATE_PAGE_PANEL_FIELD_PAGE_TITLE'),
	          textOnly: true
	        });
	      });
	    }
	  }, {
	    key: "getCodeField",
	    value: function getCodeField() {
	      return this.cache.remember('codeField', function () {
	        return new landing_ui_field_textfield.TextField({
	          title: landing_loc.Loc.getMessage('LANDING_CREATE_PAGE_PANEL_FIELD_PAGE_CODE'),
	          textOnly: true
	        });
	      });
	    }
	  }, {
	    key: "getForm",
	    value: function getForm() {
	      var _this2 = this;

	      return this.cache.remember('form', function () {
	        return new BX.Landing.UI.Form.BaseForm({
	          fields: [_this2.getTitleField(), _this2.getCodeField()]
	        });
	      });
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
	          _ref$title = _ref.title,
	          title = _ref$title === void 0 ? '' : _ref$title;

	      main_core.Dom.style(this.footer, 'display', null);
	      this.range = document.getSelection().getRangeAt(0);

	      this.node = function () {
	        if (BX.Landing.Block.Node.Text.currentNode.isEditable()) {
	          return BX.Landing.Block.Node.Text.currentNode;
	        }

	        return landing_ui_field_textfield.TextField.currentField;
	      }();

	      var capitalizedTitle = title.replace(/^\w/, function (c) {
	        return c.toUpperCase();
	      });
	      this.getTitleField().setValue(capitalizedTitle);
	      var translitedTitle = BX.translit(title, {
	        change_case: 'L',
	        replace_space: '-',
	        replace_other: ''
	      });
	      this.getCodeField().setValue(translitedTitle);
	      this.clear();
	      this.appendForm(this.getForm());
	      return babelHelpers.get(babelHelpers.getPrototypeOf(CreatePage.prototype), "show", this).call(this);
	    }
	  }, {
	    key: "getSuccessMessage",
	    value: function getSuccessMessage(id) {
	      var envOptions = landing_env.Env.getInstance().getOptions();
	      var urlMask = envOptions.params.sef_url.landing_view;
	      var siteId = envOptions.site_id;
	      var editLink = urlMask.replace('#site_show#', siteId).replace('#landing_edit#', id);
	      return main_core.Tag.render(_templateObject(), landing_loc.Loc.getMessage('LANDING_CREATE_PAGE_PANEL_SUCCESS_MESSAGE_TITLE'), editLink, landing_loc.Loc.getMessage('LANDING_CONTENT_PANEL_TITLE'));
	    }
	  }, {
	    key: "getFailMessage",
	    value: function getFailMessage() {
	      return this.cache.remember('failMessage', function () {
	        return main_core.Tag.render(_templateObject2(), landing_loc.Loc.getMessage('LANDING_CREATE_PAGE_PANEL_FAIL_MESSAGE_TITLE'));
	      });
	    }
	  }, {
	    key: "onSave",
	    value: function onSave() {
	      var _this3 = this;

	      var backend = landing_backend.Backend.getInstance();
	      var title = this.getTitleField().getValue();
	      var code = BX.translit(this.getCodeField().getValue(), {
	        change_case: 'L',
	        replace_space: '-',
	        replace_other: ''
	      });

	      var _Env$getInstance$getO = landing_env.Env.getInstance().getOptions(),
	          folderId = _Env$getInstance$getO.folder_id;

	      var loader = new main_loader.Loader();
	      this.clear();
	      loader.show(this.body);
	      void backend.createPage({
	        title: title,
	        code: code,
	        folderId: folderId
	      }).then(function (result) {
	        return new Promise(function (resolve) {
	          setTimeout(function () {
	            return resolve(result);
	          }, 500);
	        });
	      }).then(function (result) {
	        loader.hide();

	        if (main_core.Type.isNumber(result)) {
	          var successMessage = _this3.getSuccessMessage(result);

	          if (landing_env.Env.getInstance().getType() === 'KNOWLEDGE' || landing_env.Env.getInstance().getType() === 'GROUP') {
	            var _link = successMessage.querySelector('a');

	            if (_link) {
	              main_core.Event.bind(_link, 'click', function (event) {
	                event.preventDefault();
	                void landing_sliderhacks.SliderHacks.reloadSlider(_link.href, window.parent);
	              });
	            }
	          }

	          main_core.Dom.append(successMessage, _this3.content);
	          var value = {
	            href: "#landing".concat(result)
	          };
	          document.getSelection().removeAllRanges();
	          document.getSelection().addRange(_this3.range);

	          _this3.node.enableEdit();

	          var tmpHref = main_core.Text.encode("".concat(value.href).concat(main_core.Text.getRandom()));
	          var selection = document.getSelection();
	          document.execCommand('createLink', false, tmpHref);
	          var link = selection.anchorNode.parentElement.parentElement.parentElement.querySelector("[href=\"".concat(tmpHref, "\"]"));

	          if (link) {
	            main_core.Dom.attr(link, 'href', value.href);
	            main_core.Dom.attr(link, 'target', value.target);

	            if (main_core.Type.isString(value.text)) {
	              link.innerText = value.text;
	            }

	            if (main_core.Type.isPlainObject(value.attrs)) {
	              main_core.Dom.attr(link, value.attrs);
	            }
	          }

	          main_core.Dom.style(_this3.footer, 'display', 'none');
	        } else {
	          main_core.Dom.append(_this3.getFailMessage(), _this3.content);
	        }
	      });
	    }
	  }]);
	  return CreatePage;
	}(landing_ui_panel_content.Content);

	exports.CreatePage = CreatePage;

}((this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {}),BX,BX,BX.Landing.UI.Panel,BX.Landing,BX.Landing,BX.Landing,BX.Landing,BX.Landing.UI.Field));
//# sourceMappingURL=createpage.bundle.js.map
