this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_backend,landing_env,landing_imagecompressor,landing_loc,landing_main,landing_screenshoter,landing_ui_card_messagecard,landing_ui_field_textfield,landing_ui_panel_content,main_core) {
	'use strict';

	var _templateObject;
	/**
	 * @memberOf BX.Landing.UI.Panel
	 */

	var SaveBlock = /*#__PURE__*/function (_Content) {
	  babelHelpers.inherits(SaveBlock, _Content);
	  babelHelpers.createClass(SaveBlock, null, [{
	    key: "getInstance",
	    value: function getInstance() {
	      if (!SaveBlock.instance) {
	        SaveBlock.instance = new SaveBlock('landing_save_block_panel');
	      }

	      return SaveBlock.instance;
	    }
	  }]);

	  function SaveBlock(id, data) {
	    var _this;

	    babelHelpers.classCallCheck(this, SaveBlock);
	    data = data || {};
	    data.title = landing_loc.Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_TITLE_MSGVER_1');
	    data.showFromRight = true;

	    if (!data.block) {
	      return babelHelpers.possibleConstructorReturn(_this);
	    }

	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SaveBlock).call(this, id, data));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "cache", new main_core.Cache.MemoryCache());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "bock", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "previewFileIds", []);
	    _this.block = data.block;
	    _this.mainInstance = landing_main.Main.getInstance();
	    main_core.Dom.addClass(_this.layout, 'landing-ui-panel-save-block');
	    main_core.Dom.addClass(_this.overlay, 'landing-ui-panel-save-block');

	    _this.setButtons();

	    _this.renderTo(window.parent.document.body);

	    return _this;
	  }

	  babelHelpers.createClass(SaveBlock, [{
	    key: "setButtons",
	    value: function setButtons() {
	      this.appendFooterButton(new BX.Landing.UI.Button.BaseButton('save_block_content', {
	        text: landing_loc.Loc.getMessage('BLOCK_SAVE'),
	        onClick: this.onSave.bind(this),
	        className: 'landing-ui-button-content-save'
	      }));
	      this.appendFooterButton(new BX.Landing.UI.Button.BaseButton('cancel_block_content', {
	        text: landing_loc.Loc.getMessage('BLOCK_CANCEL'),
	        onClick: this.hide.bind(this),
	        className: 'landing-ui-button-content-cancel'
	      }));
	    }
	  }, {
	    key: "getTitleField",
	    value: function getTitleField() {
	      return this.cache.remember('titleField', function () {
	        return new landing_ui_field_textfield.TextField({
	          title: landing_loc.Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_FIELD_TITLE'),
	          textOnly: true
	        });
	      });
	    }
	  }, {
	    key: "getSectionsField",
	    value: function getSectionsField() {
	      return this.cache.remember('sectionsField', function () {
	        var items = [];

	        var _Env$getInstance$getO = landing_env.Env.getInstance().getOptions(),
	            blocks = _Env$getInstance$getO.blocks;

	        Object.keys(blocks).map(function (key) {
	          if (key !== 'last' && key !== 'separator_apps' && key.indexOf('.') === -1) {
	            items.push({
	              value: key,
	              name: blocks[key].name
	            });
	          }
	        });
	        return new BX.Landing.UI.Field.MultiSelect({
	          title: landing_loc.Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_FIELD_SECTIONS'),
	          items: items
	        });
	      });
	    }
	  }, {
	    key: "getTemplateRefField",
	    value: function getTemplateRefField() {
	      return this.cache.remember('templateRefField', function () {
	        return new BX.Landing.UI.Field.Checkbox({
	          items: [{
	            value: 'N',
	            name: landing_loc.Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_FIELD_TEMPLATE_REF')
	          }]
	        });
	      });
	    }
	  }, {
	    key: "getPreviewField",
	    value: function getPreviewField() {
	      var _this2 = this;

	      return this.cache.remember('preview', function () {
	        return new BX.Landing.UI.Field.Image({
	          title: landing_loc.Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_FIELD_PREVIEW'),
	          disableLink: true,
	          disableAltField: true,
	          uploadParams: {
	            action: 'Block::uploadFile',
	            block: _this2.block.id
	          },
	          content: {
	            src: '/bitrix/images/1.gif',
	            id: -1,
	            alt: ''
	          },
	          dimensions: {
	            width: 1200,
	            height: 600
	          }
	        });
	      });
	    }
	  }, {
	    key: "getMessage",
	    value: function getMessage() {
	      return this.cache.remember('message', function () {
	        return new landing_ui_card_messagecard.MessageCard({
	          id: 'fieldsMessage',
	          header: landing_loc.Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_MESSAGE_TITLE_MSGVER_1'),
	          description: landing_loc.Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_MESSAGE_TEXT_MSGVER_1'),
	          //icon: messageIcon,
	          restoreState: true
	        });
	      });
	    }
	  }, {
	    key: "getForm",
	    value: function getForm() {
	      var _this3 = this;

	      return this.cache.remember('form', function () {
	        return new BX.Landing.UI.Form.BaseForm({
	          fields: [_this3.getTitleField(), _this3.getSectionsField(), _this3.mainInstance.getTemplateCode() ? _this3.getTemplateRefField() : null, _this3.getPreviewField()]
	        });
	      });
	    }
	  }, {
	    key: "makeScreenshot",
	    value: function makeScreenshot() {
	      var _this4 = this;

	      this.getPreviewField().showLoader();
	      void landing_screenshoter.Screenshoter.makeBlockScreenshot(this.block.id).then(function (sourceFile) {
	        return landing_imagecompressor.ImageCompressor.compress(sourceFile, {
	          maxWidth: 830,
	          maxHeight: 300
	        });
	      }).then(function (compressedFile) {
	        return landing_backend.Backend.getInstance().upload(compressedFile, {
	          block: _this4.block.id,
	          temp: true
	        });
	      }).then(function (response) {
	        _this4.getPreviewField().setValue(response);

	        _this4.getPreviewField().hideLoader();
	      });
	    }
	  }, {
	    key: "show",
	    value: function show(options) {
	      var _this$block, _this$block$manifest, _this$block$manifest$, _this$block2, _this$block2$manifest, _this$block2$manifest2, _this$block3, _this$block3$manifest, _this$block4, _this$block4$manifest, _this$block4$manifest2;

	      main_core.Dom.style(this.footer, 'display', null);
	      this.getTitleField().setValue((_this$block = this.block) === null || _this$block === void 0 ? void 0 : (_this$block$manifest = _this$block.manifest) === null || _this$block$manifest === void 0 ? void 0 : (_this$block$manifest$ = _this$block$manifest.block) === null || _this$block$manifest$ === void 0 ? void 0 : _this$block$manifest$.name);
	      this.getSectionsField().setValue(((_this$block2 = this.block) === null || _this$block2 === void 0 ? void 0 : (_this$block2$manifest = _this$block2.manifest) === null || _this$block2$manifest === void 0 ? void 0 : (_this$block2$manifest2 = _this$block2$manifest.block) === null || _this$block2$manifest2 === void 0 ? void 0 : _this$block2$manifest2.section) || []);
	      this.getTemplateRefField().setValue(['Y']);
	      this.getPreviewField().setValue({
	        src: ((_this$block3 = this.block) === null || _this$block3 === void 0 ? void 0 : (_this$block3$manifest = _this$block3.manifest) === null || _this$block3$manifest === void 0 ? void 0 : _this$block3$manifest.preview) || ((_this$block4 = this.block) === null || _this$block4 === void 0 ? void 0 : (_this$block4$manifest = _this$block4.manifest) === null || _this$block4$manifest === void 0 ? void 0 : (_this$block4$manifest2 = _this$block4$manifest.block) === null || _this$block4$manifest2 === void 0 ? void 0 : _this$block4$manifest2.preview) || ''
	      });
	      this.makeScreenshot();
	      this.clear();
	      main_core.Dom.prepend(this.getMessage().getLayout(), this.content);
	      this.appendForm(this.getForm());
	      return babelHelpers.get(babelHelpers.getPrototypeOf(SaveBlock.prototype), "show", this).call(this);
	    }
	  }, {
	    key: "getFailMessage",
	    value: function getFailMessage() {
	      return this.cache.remember('failMessage', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-panel-save-block-fail\">\n\t\t\t\t\t<div class=\"landing-ui-panel-save-block-fail-header\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), landing_loc.Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_FAIL_MESSAGE_TITLE'));
	      });
	    }
	  }, {
	    key: "onSave",
	    value: function onSave() {
	      var _this$block5,
	          _this$block5$manifest,
	          _this5 = this;

	      var backend = landing_backend.Backend.getInstance();
	      var title = this.getTitleField().getValue();
	      var templateRef = this.getTemplateRefField().getValue().length > 0;
	      var preview = this.getPreviewField().getValue();
	      var blockCode = (_this$block5 = this.block) === null || _this$block5 === void 0 ? void 0 : (_this$block5$manifest = _this$block5.manifest) === null || _this$block5$manifest === void 0 ? void 0 : _this$block5$manifest.code;
	      var sections = this.getSectionsField().getValue();
	      this.clear();
	      this.hide();

	      if (!blockCode) {
	        return;
	      }

	      backend.action('Landing::favoriteBlock', {
	        lid: this.block.lid,
	        block: this.block.id,
	        meta: {
	          name: title,
	          section: sections,
	          preview: Math.max(preview.id, 0),
	          tpl_code: templateRef ? this.mainInstance.getTemplateCode() : null
	        }
	      }, {
	        code: blockCode
	      }).then(function (newBlockId) {
	        if (newBlockId) {
	          top.BX.UI.Notification.Center.notify({
	            content: landing_loc.Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_SUCCESS')
	          });
	          sections.push('last');
	          sections.map(function (section) {
	            _this5.mainInstance.addNewBlockToCategory(section, {
	              code: blockCode,
	              codeOriginal: blockCode + '@' + newBlockId,
	              name: title,
	              preview: preview.src,
	              section: sections,
	              favorite: true,
	              favoriteMy: true,
	              repo_id: _this5.block.repoId
	            });
	          });
	        } else {
	          main_core.Dom.append(_this5.getFailMessage(), _this5.content);
	        }
	      });
	    }
	  }]);
	  return SaveBlock;
	}(landing_ui_panel_content.Content);

	exports.SaveBlock = SaveBlock;

}((this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {}),BX.Landing,BX.Landing,BX.Landing,BX.Landing,BX.Landing,BX.Landing,BX.Landing.UI.Card,BX.Landing.UI.Field,BX.Landing.UI.Panel,BX));
//# sourceMappingURL=saveblock.bundle.js.map
