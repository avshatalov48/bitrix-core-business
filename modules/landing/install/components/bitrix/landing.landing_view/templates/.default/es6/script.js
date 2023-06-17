this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core,main_popup) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _iframe = /*#__PURE__*/new WeakMap();

	var _postInternalCommand = /*#__PURE__*/new WeakSet();

	var Action = /*#__PURE__*/function () {
	  function Action(options) {
	    babelHelpers.classCallCheck(this, Action);

	    _classPrivateMethodInitSpec(this, _postInternalCommand);

	    _classPrivateFieldInitSpec(this, _iframe, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _iframe, options.iframe);

	    if (!main_core.Type.isDomNode(babelHelpers.classPrivateFieldGet(this, _iframe))) {
	      throw new Error("Missed 'frame' option as iFrame Element.");
	    }
	  }
	  /**
	   * Sends action with payload to child window.
	   *
	   * @param {string} action Command to internal iframe.
	   * @param {Object} payload Command's payload.
	   */


	  babelHelpers.createClass(Action, [{
	    key: "onDesignerBlockClick",

	    /**
	     * Handles on Designer click.
	     *
	     * @param {number} blockId Block id.
	     */
	    value: function onDesignerBlockClick(blockId) {
	      _classPrivateMethodGet(this, _postInternalCommand, _postInternalCommand2).call(this, 'onDesignerBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Style Block click.
	     *
	     * @param {number} blockId Block id.
	     */

	  }, {
	    key: "onStyleBlockClick",
	    value: function onStyleBlockClick(blockId) {
	      _classPrivateMethodGet(this, _postInternalCommand, _postInternalCommand2).call(this, 'onStyleBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Edit Block click.
	     *
	     * @param {number} blockId Block id.
	     */

	  }, {
	    key: "onEditBlockClick",
	    value: function onEditBlockClick(blockId) {
	      _classPrivateMethodGet(this, _postInternalCommand, _postInternalCommand2).call(this, 'onEditBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Down Block click.
	     *
	     * @param {number} blockId Block id.
	     */

	  }, {
	    key: "onSortDownBlockClick",
	    value: function onSortDownBlockClick(blockId) {
	      _classPrivateMethodGet(this, _postInternalCommand, _postInternalCommand2).call(this, 'onSortDownBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Up Block click.
	     *
	     * @param {number} blockId Block id.
	     */

	  }, {
	    key: "onSortUpBlockClick",
	    value: function onSortUpBlockClick(blockId) {
	      _classPrivateMethodGet(this, _postInternalCommand, _postInternalCommand2).call(this, 'onSortUpBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Remove Block click.
	     *
	     * @param {number} blockId Block id.
	     */

	  }, {
	    key: "onRemoveBlockClick",
	    value: function onRemoveBlockClick(blockId) {
	      _classPrivateMethodGet(this, _postInternalCommand, _postInternalCommand2).call(this, 'onRemoveBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Change State Block click.
	     *
	     * @param {number} blockId Block id.
	     */

	  }, {
	    key: "onChangeStateBlockClick",
	    value: function onChangeStateBlockClick(blockId) {
	      _classPrivateMethodGet(this, _postInternalCommand, _postInternalCommand2).call(this, 'onChangeStateBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Cut Block click.
	     *
	     * @param {number} blockId Block id.
	     */

	  }, {
	    key: "onCutBlockClick",
	    value: function onCutBlockClick(blockId) {
	      _classPrivateMethodGet(this, _postInternalCommand, _postInternalCommand2).call(this, 'onCutBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Copy Block click.
	     *
	     * @param {number} blockId Block id.
	     */

	  }, {
	    key: "onCopyBlockClick",
	    value: function onCopyBlockClick(blockId) {
	      _classPrivateMethodGet(this, _postInternalCommand, _postInternalCommand2).call(this, 'onCopyBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Paste Block click.
	     *
	     * @param {number} blockId Block id.
	     */

	  }, {
	    key: "onPasteBlockClick",
	    value: function onPasteBlockClick(blockId) {
	      _classPrivateMethodGet(this, _postInternalCommand, _postInternalCommand2).call(this, 'onPasteBlockClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Feedback click.
	     *
	     * @param {number} blockId Block id.
	     */

	  }, {
	    key: "onFeedbackClick",
	    value: function onFeedbackClick(blockId) {
	      _classPrivateMethodGet(this, _postInternalCommand, _postInternalCommand2).call(this, 'onFeedbackClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Handles on Save In Library click.
	     *
	     * @param {number} blockId Block id.
	     */

	  }, {
	    key: "onSaveInLibraryClick",
	    value: function onSaveInLibraryClick(blockId) {
	      _classPrivateMethodGet(this, _postInternalCommand, _postInternalCommand2).call(this, 'onSaveInLibraryClick', {
	        blockId: blockId
	      });
	    }
	    /**
	     * Hide opened editor panel.
	     */

	  }, {
	    key: "onHideEditorPanel",
	    value: function onHideEditorPanel() {
	      _classPrivateMethodGet(this, _postInternalCommand, _postInternalCommand2).call(this, 'onHideEditorPanel');
	    }
	  }]);
	  return Action;
	}();

	function _postInternalCommand2(action, payload) {
	  babelHelpers.classPrivateFieldGet(this, _iframe).contentWindow.postMessage({
	    action: action,
	    payload: payload
	  }, window.location.origin);
	}

	var Loc = /*#__PURE__*/function () {
	  function Loc() {
	    babelHelpers.classCallCheck(this, Loc);
	  }

	  babelHelpers.createClass(Loc, null, [{
	    key: "loadMessages",
	    value: function loadMessages(messages) {
	      Loc.messages = messages;
	    }
	  }, {
	    key: "getMessage",
	    value: function getMessage(code) {
	      return Loc.messages[code];
	    }
	  }]);
	  return Loc;
	}();

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9;
	var UI = /*#__PURE__*/function () {
	  function UI() {
	    babelHelpers.classCallCheck(this, UI);
	  }

	  babelHelpers.createClass(UI, null, [{
	    key: "setPendingMenuItemValue",

	    /**
	     * Till Menu for this block not show, sets predefined prop value for menu item.
	     *
	     * @param {number} blockId
	     * @param {string} itemCode
	     * @param {string} itemProp
	     * @param {mixed} value
	     */
	    value: function setPendingMenuItemValue(blockId, itemCode, itemProp, value) {
	      if (!UI.pendingMenuItems[blockId]) {
	        UI.pendingMenuItems[blockId] = {};
	      }

	      if (!UI.pendingMenuItems[blockId][itemCode]) {
	        UI.pendingMenuItems[blockId][itemCode] = {};
	      }

	      UI.pendingMenuItems[blockId][itemCode][itemProp] = value;
	    }
	    /**
	     * Returns predefined prop value for menu item (if exists).
	     *
	     * @param {number} blockId
	     * @param {string} itemCode
	     * @param {string} itemProp
	     */

	  }, {
	    key: "getPendingMenuItemValue",
	    value: function getPendingMenuItemValue(blockId, itemCode, itemProp) {
	      if (UI.pendingMenuItems[blockId] && UI.pendingMenuItems[blockId][itemCode]) {
	        return UI.pendingMenuItems[blockId][itemCode][itemProp] || null;
	      }

	      return null;
	    }
	    /**
	     * Returns Designer Button.
	     *
	     * @param {() => {}} onClick Click handler.
	     * @return {HTMLButtonElement}
	     */

	  }, {
	    key: "getDesignerBlockButton",
	    value: function getDesignerBlockButton(onClick) {
	      var title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_DESIGNER_BLOCK');
	      var button = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button class=\"landing-ui-button landing-ui-button-action --separate", "\" type=\"button\" title=\"", "\">\n\t\t\t\t<span class=\"landing-ui-button-text\">", "</span>\n\t\t\t</button>\n\t\t"])), onClick ? '' : ' landing-ui-disabled', title, title);

	      if (onClick) {
	        main_core.Event.bind(button, 'click', onClick);
	      }

	      return button;
	    }
	    /**
	     * Returns Style Block Button.
	     *
	     * @param {() => {}} onClick Click handler.
	     * @return {HTMLButtonElement}
	     */

	  }, {
	    key: "getStyleBlockButton",
	    value: function getStyleBlockButton(onClick) {
	      var label = Loc.getMessage('LANDING_TPL_EXT_BUTTON_STYLE_BLOCK');
	      var title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_STYLE_BLOCK_TITLE');
	      var button = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button class=\"landing-ui-button landing-ui-button-action --separate", "\" type=\"button\" title=\"", "\">\n\t\t\t\t<span class=\"landing-ui-button-text\">", "</span>\n\t\t\t</button>\n\t\t"])), onClick ? '' : ' landing-ui-disabled', title, label);

	      if (onClick) {
	        main_core.Event.bind(button, 'click', onClick);
	      }

	      return button;
	    }
	    /**
	     * Returns Edit Block Button.
	     *
	     * @param {() => {}} onClick Click handler.
	     * @return {HTMLButtonElement}
	     */

	  }, {
	    key: "getEditBlockButton",
	    value: function getEditBlockButton(onClick) {
	      //data-id="content"
	      var label = Loc.getMessage('LANDING_TPL_EXT_BUTTON_EDIT_BLOCK');
	      var title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_EDIT_BLOCK_TITLE');
	      var button = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button class=\"landing-ui-button landing-ui-button-action --separate", "\" type=\"button\" title=\"", "\" data-id=\"content\">\n\t\t\t\t<span class=\"landing-ui-button-text\">", "</span>\n\t\t\t</button>\n\t\t"])), onClick ? '' : ' landing-ui-disabled', title, label);

	      if (onClick) {
	        main_core.Event.bind(button, 'click', onClick);
	      }

	      return button;
	    }
	    /**
	     * Returns left container for block's actions.
	     *
	     * @param {LeftContainerOptions} options Options for left container.
	     * @return {HTMLDivElement}
	     */

	  }, {
	    key: "getLeftContainer",
	    value: function getLeftContainer(options) {
	      return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-external-left-container\">\n\t\t\t\t<div class=\"landing-ui-external-left-top-hr\"></div>\n\t\t\t\t<div class=\"landing-ui-external-body\">\n\t\t\t\t\t<div class=\"landing-ui-external-panel\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"landing-ui-external-left-bottom-hr\"></div>\n\t\t\t</div>\n\t\t"])), UI.getDesignerBlockButton(options.designerBlockClick), UI.getStyleBlockButton(options.styleBlockClick), UI.getEditBlockButton(options.editBlockClick));
	    }
	    /**
	     * Returns Sort Down Button.
	     *
	     * @param {() => {}} onClick Click handler.
	     * @return {HTMLButtonElement}
	     */

	  }, {
	    key: "getSortDownBlockButton",
	    value: function getSortDownBlockButton(onClick) {
	      var title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_DOWN_BLOCK');
	      var button = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button class=\"landing-ui-button landing-ui-button-action", "\" type=\"button\" data-id=\"down\" title=\"", "\"><span class=\"landing-ui-button-text\">&nbsp;</span></button>\n\t\t"])), onClick ? '' : ' landing-ui-disabled', title);

	      if (onClick) {
	        main_core.Event.bind(button, 'click', onClick);
	      }

	      return button;
	    }
	    /**
	     * Returns Sort Up Button.
	     *
	     * @param {() => {}} onClick Click handler.
	     * @return {HTMLButtonElement}
	     */

	  }, {
	    key: "getSortUpBlockButton",
	    value: function getSortUpBlockButton(onClick) {
	      var title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_UP_BLOCK');
	      var button = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button class=\"landing-ui-button landing-ui-button-action", "\" type=\"button\" data-id=\"up\" title=\"", "\"><span class=\"landing-ui-button-text\">&nbsp;</span></button>\n\t\t"])), onClick ? '' : ' landing-ui-disabled', title);

	      if (onClick) {
	        main_core.Event.bind(button, 'click', onClick);
	      }

	      return button;
	    }
	    /**
	     * Returns Additional Items Menu for Block.
	     *
	     * @param {number} blockId Block id.
	     * @return {Menu}
	     */

	  }, {
	    key: "getBlockAdditionalMenu",
	    value: function getBlockAdditionalMenu(blockId) {
	      return main_popup.MenuManager.getMenuById('block_actions_' + blockId);
	    }
	    /**
	     * Closes Additional Items Menu for Block.
	     *
	     * @param {number} blockId Block id.
	     */

	  }, {
	    key: "closeBlockAdditionalMenu",
	    value: function closeBlockAdditionalMenu(blockId) {
	      var menu = UI.getBlockAdditionalMenu(blockId);

	      if (menu) {
	        menu.close();
	      }
	    }
	    /**
	     * Change state for Additional Menu Item 'Activate'.
	     *
	     * @param {number} blockId Block id.
	     * @param {boolean} state State.
	     */

	  }, {
	    key: "changeStateMenuItem",
	    value: function changeStateMenuItem(blockId, state) {
	      var menu = UI.getBlockAdditionalMenu(blockId);
	      var title = Loc.getMessage(!state ? 'LANDING_TPL_EXT_BUTTON_ACTIONS_SHOW' : 'LANDING_TPL_EXT_BUTTON_ACTIONS_HIDE');

	      if (menu) {
	        BX.Landing.Utils.setTextContent(menu.getMenuItem('show_hide').getLayout()['text'], title);
	      } else {
	        UI.setPendingMenuItemValue(blockId, 'show_hide', 'state', state);
	      }
	    }
	    /**
	     * Enables/disables paste-item.
	     *
	     * @param {number} blockId Block id.
	     * @param {boolean} enablePaste Flag.
	     */

	  }, {
	    key: "changePasteMenuItem",
	    value: function changePasteMenuItem(blockId, enablePaste) {
	      var menu = UI.getBlockAdditionalMenu(blockId);

	      if (menu) {
	        var item = menu.getMenuItem('paste');

	        if (item) {
	          if (enablePaste) {
	            item.enable();
	          } else {
	            item.disable();
	          }
	        }
	      } else {
	        UI.setPendingMenuItemValue(blockId, 'paste', 'disabled', !enablePaste);
	      }
	    }
	    /**
	     * Returns List of Actions for Block.
	     *
	     * @param {number} blockId Block id.
	     * @param {AdditionalActions} actions Additional actions for Block.
	     * @return {HTMLButtonElement}
	     */

	  }, {
	    key: "getActionsList",
	    value: function getActionsList(blockId, actions) {
	      var label = Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_BLOCK');
	      var title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_BLOCK_TITLE');
	      var actionButton = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button class=\"landing-ui-button landing-ui-button-action\" type=\"button\" data-id=\"actions\" title=\"", "\">\n\t\t\t\t<span class=\"landing-ui-button-text\">", "</span>\n\t\t\t</button>\n\t\t"])), title, label); // when click is occurred open exists menu or create new one

	      main_core.Event.bind(actionButton, 'click', function (event) {
	        if (actions.onOpenAdditionalMenu) {
	          actions.onOpenAdditionalMenu(blockId);
	          event.stopPropagation();
	        }

	        var menu = UI.getBlockAdditionalMenu(blockId);

	        if (menu) {
	          menu.show();
	          return;
	        }

	        main_popup.MenuManager.create({
	          id: 'block_actions_' + blockId,
	          bindElement: actionButton,
	          className: 'landing-ui-block-actions-popup',
	          angle: {
	            position: 'top',
	            offset: 95
	          },
	          offsetTop: -6,
	          offsetLeft: -26,
	          items: [new main_popup.MenuItem({
	            id: 'show_hide',
	            disabled: !actions.changeStateClick,
	            text: Loc.getMessage(actions.state || UI.getPendingMenuItemValue(blockId, 'show_hide', 'state') ? 'LANDING_TPL_EXT_BUTTON_ACTIONS_HIDE' : 'LANDING_TPL_EXT_BUTTON_ACTIONS_SHOW'),
	            onclick: function onclick() {
	              actions.changeStateClick();
	            }
	          }), new main_popup.MenuItem({
	            id: 'cut',
	            disabled: !actions.cutClick,
	            text: Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_CUT'),
	            onclick: function onclick() {
	              actions.cutClick();
	            }
	          }), new main_popup.MenuItem({
	            id: 'copy',
	            disabled: !actions.copyClick,
	            text: Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_COPY'),
	            onclick: function onclick() {
	              actions.copyClick();
	            }
	          }), new main_popup.MenuItem({
	            id: 'paste',
	            disabled: !actions.pasteClick || UI.getPendingMenuItemValue(blockId, 'paste', 'disabled'),
	            text: Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_PASTE'),
	            onclick: function onclick() {
	              actions.pasteClick();
	            }
	          }), new main_popup.MenuItem({
	            id: 'feedback',
	            disabled: !actions.feedbackClick,
	            text: Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_FEEDBACK'),
	            onclick: function onclick() {
	              actions.feedbackClick();
	            }
	          }), actions.saveInLibrary ? new main_popup.MenuItem({
	            delimiter: true
	          }) : null, new main_popup.MenuItem({
	            id: 'save_in_library',
	            disabled: !actions.saveInLibrary,
	            text: Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_SAVE_IN_LIBRARY'),
	            onclick: function onclick() {
	              actions.saveInLibrary();
	            }
	          })]
	        }).show();
	      });
	      return actionButton;
	    }
	    /**
	     * Returns Remove Button.
	     *
	     * @param {() => {}} onClick Click handler.
	     * @return {HTMLButtonElement}
	     */

	  }, {
	    key: "getRemoveBlockButton",
	    value: function getRemoveBlockButton(onClick) {
	      var title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_REMOVE_BLOCK');
	      var button = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button class=\"landing-ui-button landing-ui-button-action", "\" type=\"button\" data-id=\"remove\" title=\"", "\"><span class=\"landing-ui-button-text\">&nbsp;</span></button>\n\t\t"])), onClick ? '' : ' landing-ui-disabled', title);

	      if (onClick) {
	        main_core.Event.bind(button, 'click', onClick);
	      }

	      return button;
	    }
	    /**
	     * Returns right container for block's actions.
	     *
	     * @param {RightContainerOptions} options Options for right container.
	     * @return {HTMLDivElement}
	     */

	  }, {
	    key: "getRightContainer",
	    value: function getRightContainer(options) {
	      return main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-external-right-container\">\n\t\t\t\t<div class=\"landing-ui-external-right-top-hr\"></div>\n\t\t\t\t<div class=\"landing-ui-external-body\">\n\t\t\t\t\t<div class=\"landing-ui-external-panel\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"landing-ui-external-right-bottom-hr\"></div>\n\t\t\t</div>\n\t\t"])), UI.getSortDownBlockButton(options.sortDownBlockClick), UI.getSortUpBlockButton(options.sortUpBlockClick), UI.getActionsList(options.blockId, options), UI.getRemoveBlockButton(options.removeBlockClick));
	    }
	  }]);
	  return UI;
	}();
	babelHelpers.defineProperty(UI, "pendingMenuItems", {});

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _action = /*#__PURE__*/new WeakMap();

	var _externalBlocks = /*#__PURE__*/new WeakMap();

	var _container = /*#__PURE__*/new WeakMap();

	var _iframeWrapper = /*#__PURE__*/new WeakMap();

	var _currentOpenBlockId = /*#__PURE__*/new WeakMap();

	var _currentOpenMenuBlock = /*#__PURE__*/new WeakMap();

	var _listenChildFrame = /*#__PURE__*/new WeakSet();

	var _onStorageChange = /*#__PURE__*/new WeakSet();

	var _registerBlocks = /*#__PURE__*/new WeakSet();

	var _getBlock = /*#__PURE__*/new WeakSet();

	var _updateBlock = /*#__PURE__*/new WeakSet();

	var _changeState = /*#__PURE__*/new WeakSet();

	var _hideAllControls = /*#__PURE__*/new WeakSet();

	var _showControls = /*#__PURE__*/new WeakSet();

	var _hideAndShowControls = /*#__PURE__*/new WeakSet();

	var _onChangeMode = /*#__PURE__*/new WeakSet();

	var ExternalControls = function ExternalControls(options) {
	  var _this = this;

	  babelHelpers.classCallCheck(this, ExternalControls);

	  _classPrivateMethodInitSpec$1(this, _onChangeMode);

	  _classPrivateMethodInitSpec$1(this, _hideAndShowControls);

	  _classPrivateMethodInitSpec$1(this, _showControls);

	  _classPrivateMethodInitSpec$1(this, _hideAllControls);

	  _classPrivateMethodInitSpec$1(this, _changeState);

	  _classPrivateMethodInitSpec$1(this, _updateBlock);

	  _classPrivateMethodInitSpec$1(this, _getBlock);

	  _classPrivateMethodInitSpec$1(this, _registerBlocks);

	  _classPrivateMethodInitSpec$1(this, _onStorageChange);

	  _classPrivateMethodInitSpec$1(this, _listenChildFrame);

	  _classPrivateFieldInitSpec$1(this, _action, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec$1(this, _externalBlocks, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec$1(this, _container, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec$1(this, _iframeWrapper, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec$1(this, _currentOpenBlockId, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec$1(this, _currentOpenMenuBlock, {
	    writable: true,
	    value: void 0
	  });

	  options = options || {};
	  babelHelpers.classPrivateFieldSet(this, _container, options.container);
	  babelHelpers.classPrivateFieldSet(this, _iframeWrapper, options.iframeWrapper);

	  if (!main_core.Type.isDomNode(babelHelpers.classPrivateFieldGet(this, _container))) {
	    throw new Error("Missed 'container' option as Dom Node.");
	  }

	  if (!main_core.Type.isDomNode(babelHelpers.classPrivateFieldGet(this, _iframeWrapper))) {
	    throw new Error("Missed 'iframe' option as Dom Node.");
	  }

	  babelHelpers.classPrivateFieldSet(this, _externalBlocks, new Map());
	  babelHelpers.classPrivateFieldSet(this, _action, new Action({
	    iframe: babelHelpers.classPrivateFieldGet(this, _iframeWrapper).querySelector('iframe')
	  }));
	  Loc.loadMessages(options.messages);
	  window.addEventListener('message', _classPrivateMethodGet$1(this, _listenChildFrame, _listenChildFrame2).bind(this));
	  babelHelpers.classPrivateFieldGet(this, _container).addEventListener('click', function (event) {
	    babelHelpers.classPrivateFieldGet(_this, _action).onHideEditorPanel();
	  });
	  window.addEventListener('storage', _classPrivateMethodGet$1(this, _onStorageChange, _onStorageChange2).bind(this));
	}
	/**
	 * Handler on listening child iframe commands.
	 *
	 * @param event
	 */
;

	function _listenChildFrame2(event) {
	  var data = event.data || {};

	  if (!data.payload) {
	    return;
	  }

	  if (data.action === 'register') {
	    _classPrivateMethodGet$1(this, _registerBlocks, _registerBlocks2).call(this, data.payload.blocks);
	  } else if (data.action === 'showcontrols') {
	    _classPrivateMethodGet$1(this, _showControls, _showControls2).call(this, data.payload.blockId, data.payload.top, data.payload.height);
	  } else if (data.action === 'changestate') {
	    _classPrivateMethodGet$1(this, _changeState, _changeState2).call(this, data.payload.blockId, data.payload.state);
	  } else if (data.action === 'mode') {
	    _classPrivateMethodGet$1(this, _onChangeMode, _onChangeMode2).call(this, data.payload);
	  } else if (data.action === 'hideall') {
	    _classPrivateMethodGet$1(this, _hideAllControls, _hideAllControls2).call(this);
	  } else if (data.action === 'showblockcontrols') {
	    _classPrivateMethodGet$1(this, _hideAllControls, _hideAllControls2).call(this);

	    _classPrivateMethodGet$1(this, _hideAndShowControls, _hideAndShowControls2).call(this, data.payload.blockId);
	  }
	}

	function _onStorageChange2() {
	  var blocks = babelHelpers.classPrivateFieldGet(this, _externalBlocks).values();
	  var allowPaste = !!window.localStorage.getItem('landingBlockId');

	  for (var i = 0, c = babelHelpers.classPrivateFieldGet(this, _externalBlocks).size; i < c; i++) {
	    var blockItem = blocks.next().value;
	    UI.changePasteMenuItem(blockItem.id, allowPaste);

	    _classPrivateMethodGet$1(this, _updateBlock, _updateBlock2).call(this, blockItem.id, _objectSpread(_objectSpread({}, blockItem), {}, {
	      permissions: _objectSpread(_objectSpread({}, blockItem.permissions), {}, {
	        allowPaste: allowPaste
	      })
	    }));
	  }
	}

	function _registerBlocks2(blocks) {
	  var _this2 = this;

	  blocks.map(function (block) {
	    var blockId = block.id; // left controls

	    block.leftContainer = UI.getLeftContainer({
	      designerBlockClick: block.permissions.allowDesignBlock ? function () {
	        babelHelpers.classPrivateFieldGet(_this2, _action).onDesignerBlockClick(blockId);
	      } : null,
	      styleBlockClick: block.permissions.allowModifyStyles ? function () {
	        babelHelpers.classPrivateFieldGet(_this2, _action).onStyleBlockClick(blockId);
	      } : null,
	      editBlockClick: block.permissions.allowEditContent ? function () {
	        babelHelpers.classPrivateFieldGet(_this2, _action).onEditBlockClick(blockId);
	      } : null
	    }); // right controls

	    block.rightContainer = UI.getRightContainer({
	      blockId: blockId,
	      state: block.state,
	      sortDownBlockClick: block.permissions.allowSorting ? function () {
	        babelHelpers.classPrivateFieldGet(_this2, _action).onSortDownBlockClick(blockId);

	        _classPrivateMethodGet$1(_this2, _hideAllControls, _hideAllControls2).call(_this2);
	      } : null,
	      sortUpBlockClick: block.permissions.allowSorting ? function () {
	        babelHelpers.classPrivateFieldGet(_this2, _action).onSortUpBlockClick(blockId);

	        _classPrivateMethodGet$1(_this2, _hideAllControls, _hideAllControls2).call(_this2);
	      } : null,
	      removeBlockClick: block.permissions.allowRemove ? function () {
	        babelHelpers.classPrivateFieldGet(_this2, _action).onRemoveBlockClick(blockId);

	        _classPrivateMethodGet$1(_this2, _hideAllControls, _hideAllControls2).call(_this2);
	      } : null,
	      onOpenAdditionalMenu: function onOpenAdditionalMenu(blockId) {
	        babelHelpers.classPrivateFieldSet(_this2, _currentOpenMenuBlock, blockId);
	        setTimeout(function () {
	          babelHelpers.classPrivateFieldGet(_this2, _action).onHideEditorPanel();
	        }, 0);
	      },
	      changeStateClick: block.permissions.allowChangeState ? function () {
	        UI.closeBlockAdditionalMenu(blockId);
	        babelHelpers.classPrivateFieldGet(_this2, _action).onChangeStateBlockClick(blockId);

	        _classPrivateMethodGet$1(_this2, _hideAndShowControls, _hideAndShowControls2).call(_this2, blockId);
	      } : null,
	      cutClick: block.permissions.allowRemove ? function () {
	        babelHelpers.classPrivateFieldGet(_this2, _action).onCutBlockClick(blockId);

	        _classPrivateMethodGet$1(_this2, _hideAllControls, _hideAllControls2).call(_this2);
	      } : null,
	      copyClick: function copyClick() {
	        UI.closeBlockAdditionalMenu(blockId);
	        babelHelpers.classPrivateFieldGet(_this2, _action).onCopyBlockClick(blockId);

	        _classPrivateMethodGet$1(_this2, _hideAndShowControls, _hideAndShowControls2).call(_this2, blockId);
	      },
	      pasteClick: block.permissions.allowPaste ? function () {
	        UI.closeBlockAdditionalMenu(blockId);
	        babelHelpers.classPrivateFieldGet(_this2, _action).onPasteBlockClick(blockId);

	        _classPrivateMethodGet$1(_this2, _hideAndShowControls, _hideAndShowControls2).call(_this2, blockId);
	      } : null,
	      feedbackClick: function feedbackClick() {
	        UI.closeBlockAdditionalMenu(blockId);
	        babelHelpers.classPrivateFieldGet(_this2, _action).onFeedbackClick(blockId);
	      },
	      saveInLibrary: block.permissions.allowSaveInLibrary ? function () {
	        UI.closeBlockAdditionalMenu(blockId);
	        babelHelpers.classPrivateFieldGet(_this2, _action).onSaveInLibraryClick(blockId);
	      } : null
	    });
	    main_core.Dom.append(block.leftContainer, babelHelpers.classPrivateFieldGet(_this2, _container));
	    main_core.Dom.append(block.rightContainer, babelHelpers.classPrivateFieldGet(_this2, _container));
	    main_core.Dom.hide(block.leftContainer);
	    main_core.Dom.hide(block.rightContainer);
	    babelHelpers.classPrivateFieldGet(_this2, _externalBlocks).set(blockId, block);
	  });
	}

	function _getBlock2(blockId) {
	  return babelHelpers.classPrivateFieldGet(this, _externalBlocks).get(parseInt(blockId));
	}

	function _updateBlock2(blockId, data) {
	  babelHelpers.classPrivateFieldGet(this, _externalBlocks).set(parseInt(blockId), data);
	}

	function _changeState2(blockId, state) {
	  var block = _classPrivateMethodGet$1(this, _getBlock, _getBlock2).call(this, blockId);

	  if (block) {
	    UI.changeStateMenuItem(blockId, state);

	    _classPrivateMethodGet$1(this, _updateBlock, _updateBlock2).call(this, blockId, _objectSpread(_objectSpread({}, block), {}, {
	      state: state
	    }));
	  }
	}

	function _hideAllControls2() {
	  if (babelHelpers.classPrivateFieldGet(this, _currentOpenBlockId)) {
	    var blockItem = babelHelpers.classPrivateFieldGet(this, _externalBlocks).get(babelHelpers.classPrivateFieldGet(this, _currentOpenBlockId));
	    main_core.Dom.hide(blockItem.leftContainer);
	    main_core.Dom.hide(blockItem.rightContainer);
	  } else {
	    var blocks = babelHelpers.classPrivateFieldGet(this, _externalBlocks).values();

	    for (var i = 0, c = babelHelpers.classPrivateFieldGet(this, _externalBlocks).size; i < c; i++) {
	      var _blockItem = blocks.next().value;
	      main_core.Dom.hide(_blockItem.leftContainer);
	      main_core.Dom.hide(_blockItem.rightContainer);
	    }
	  } // if some menu is opened, close it


	  if (babelHelpers.classPrivateFieldGet(this, _currentOpenMenuBlock)) {
	    UI.closeBlockAdditionalMenu(babelHelpers.classPrivateFieldGet(this, _currentOpenMenuBlock));
	    babelHelpers.classPrivateFieldSet(this, _currentOpenMenuBlock, null);
	  }
	}

	function _showControls2(blockId, top, height) {
	  var block = _classPrivateMethodGet$1(this, _getBlock, _getBlock2).call(this, blockId);

	  if (!block) {
	    return;
	  }

	  var iframeRect = babelHelpers.classPrivateFieldGet(this, _iframeWrapper).getBoundingClientRect();

	  _classPrivateMethodGet$1(this, _hideAllControls, _hideAllControls2).call(this);

	  babelHelpers.classPrivateFieldSet(this, _currentOpenBlockId, block.id);
	  babelHelpers.classPrivateFieldGet(this, _action).onHideEditorPanel();
	  top = parseInt(top); // adjust top and bottom borders

	  if (top < 0 && height + top > 50) {
	    height = height + top;
	    top = 0;
	    main_core.Dom.addClass(block.leftContainer, 'hide-top');
	    main_core.Dom.addClass(block.rightContainer, 'hide-top');
	  } else {
	    main_core.Dom.removeClass(block.leftContainer, 'hide-top');
	    main_core.Dom.removeClass(block.rightContainer, 'hide-top');
	  }

	  main_core.Dom.show(block.leftContainer);
	  main_core.Dom.show(block.rightContainer); // adjust top and heights

	  block.leftContainer.style.width = iframeRect.left + 'px';
	  block.leftContainer.style.top = top + 'px';
	  block.leftContainer.style.height = height + 'px';
	  block.rightContainer.style.width = iframeRect.left + 'px';
	  block.rightContainer.style.left = iframeRect.left + iframeRect.width + 'px';
	  block.rightContainer.style.top = top + 'px';
	  block.rightContainer.style.height = height + 'px';
	}

	function _hideAndShowControls2(blockId) {
	  var _this3 = this;

	  var block = _classPrivateMethodGet$1(this, _getBlock, _getBlock2).call(this, blockId);

	  if (block) {
	    babelHelpers.classPrivateFieldSet(this, _currentOpenBlockId, null);
	    main_core.Dom.hide(block.leftContainer);
	    main_core.Dom.hide(block.rightContainer);
	    setTimeout(function () {
	      babelHelpers.classPrivateFieldSet(_this3, _currentOpenBlockId, block.id);
	      main_core.Dom.show(block.leftContainer);
	      main_core.Dom.show(block.rightContainer);
	    }, 500);
	  }
	}

	function _onChangeMode2(data) {
	  if (data.type === 'internal') {
	    _classPrivateMethodGet$1(this, _hideAllControls, _hideAllControls2).call(this);
	  }
	}

	var Devices = {
	  defaultDevice: {
	    tablet: 'iphone14pro',
	    mobile: 'iphone14pro'
	  },
	  devices: {
	    delimiter1: {
	      code: 'delimiter',
	      langCode: 'LANDING_PREVIEW_DEVICE_MOBILES'
	    },
	    iphone14pro: {
	      name: 'iPhone 14 Pro',
	      code: 'iphone14pro',
	      className: '--iphone-14-pro',
	      width: 393,
	      height: 852
	    },
	    iPhoneXR: {
	      name: 'iPhone XR',
	      code: 'iPhoneXR',
	      className: '--iphone-xr',
	      width: 414,
	      height: 896
	    },
	    iPhoneSE: {
	      name: 'iPhone SE',
	      code: 'iPhoneSE',
	      className: '--iphone-se',
	      width: 375,
	      height: 667
	    },
	    SamsungGalaxyNote10: {
	      name: 'Samsung Galaxy Note10',
	      code: 'SamsungGalaxyNote10',
	      className: '--samsung-galaxy-note10',
	      width: 412,
	      height: 896
	    },
	    SamsungGalaxyS8: {
	      name: 'Samsung Galaxy S8+',
	      code: 'SamsungGalaxyS8',
	      className: '--samsung-galaxy-s8-plus',
	      width: 360,
	      height: 740
	    },
	    GooglePixel4: {
	      name: 'Google Pixel 4',
	      code: 'GooglePixel4',
	      className: '--google-pixel-4',
	      width: 353,
	      height: 745
	    },
	    delimiter2: {
	      code: 'delimiter',
	      langCode: 'LANDING_PREVIEW_DEVICE_TABLETS'
	    },
	    iPad: {
	      name: 'iPad',
	      code: 'iPad',
	      className: '--ipad',
	      width: 810,
	      height: 1080
	    },
	    iPadMini: {
	      name: 'iPad Mini',
	      code: 'iPadMini',
	      className: '--ipad-mini',
	      width: 744,
	      height: 1133
	    },
	    SamsungGalaxyTabS8: {
	      name: 'Samsung Galaxy Tab S8',
	      code: 'SamsungGalaxyTabS8',
	      className: '--samsung-galaxy-tab-s8',
	      width: 800,
	      height: 1280
	    }
	  }
	};

	var _templateObject$1, _templateObject2$1, _templateObject3$1, _templateObject4$1, _templateObject5$1;

	var DeviceUI = /*#__PURE__*/function () {
	  function DeviceUI() {
	    babelHelpers.classCallCheck(this, DeviceUI);
	  }

	  babelHelpers.createClass(DeviceUI, null, [{
	    key: "getPreview",

	    /**
	     * Returns Landing Preview Block above the screen.
	     *
	     * @param {Options} options Preview options.
	     * @return {HTMLDivElement}
	     */
	    value: function getPreview(options) {
	      if (options.messages) {
	        DeviceUI.messages = options.messages;
	      }

	      if (!localStorage.getItem('deviceOrientation')) {
	        localStorage.setItem('deviceOrientation', 'portrait');
	      }

	      var switcherClick = function switcherClick(event) {
	        localStorage.setItem('deviceHidden', !main_core.Dom.hasClass(layout.switcher, 'landing-switcher-hide'));
	        main_core.Dom.toggleClass(layout.switcher, 'landing-switcher-hide');
	        main_core.Dom.toggleClass(layout.wrapper, 'landing-device-wrapper-hidden');
	      };

	      var rotateClick = function rotateClick(event) {
	        if (localStorage.getItem('deviceOrientation') === 'portrait') {
	          localStorage.setItem('deviceOrientation', 'landscape');
	        } else {
	          localStorage.setItem('deviceOrientation', 'portrait');
	        }

	        layout.wrapper.style.setProperty("width", "".concat(layout.wrapper.offsetHeight, "px"));
	        layout.wrapper.style.setProperty("height", "".concat(layout.wrapper.offsetWidth, "px"));
	        layout.frame.style.setProperty("width", "".concat(layout.frame.offsetHeight, "px"));
	        layout.frame.style.setProperty("height", "".concat(layout.frame.offsetWidth, "px"));
	        layout.wrapper.querySelector('[data-role="device-orientation"]').innerHTML = localStorage.getItem('deviceOrientation');
	      };

	      var hidden = localStorage.getItem('deviceHidden') === 'true';
	      var layout = {
	        wrapper: main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-device-wrapper", "\">\n\t\t\t\t\t<div class=\"landing-device-name\" onclick=\"", "\">\n\t\t\t\t\t\t<span data-role=\"device-name\">Device</span>\n\t\t\t\t\t\t<span data-role=\"device-orientation\" class=\"landing-device-orientation\">Orientation</span>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), hidden ? ' landing-device-wrapper-hidden' : '', options.clickHandler),
	        switcher: main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-device-switcher", "\" onclick=\"", "\" data-role=\"landing-device-switcher\"></div>"])), hidden ? ' landing-switcher-hide' : '', switcherClick),
	        rotate: main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-device-rotate\" onclick=\"", "\" data-role=\"landing-device-rotate\"></div>"])), rotateClick),
	        frame: main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["<iframe data-role=\"landing-device-preview-iframe\" src=\"", "\"></iframe>"])), options.frameUrl),
	        frameWrapper: main_core.Tag.render(_templateObject5$1 || (_templateObject5$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-device-preview\" data-role=\"landing-device-preview\"></div>"])))
	      };
	      layout.wrapper.appendChild(layout.switcher);
	      layout.wrapper.appendChild(layout.rotate);
	      layout.wrapper.appendChild(layout.frameWrapper);
	      layout.frameWrapper.appendChild(layout.frame);
	      return layout.wrapper;
	    }
	    /**
	     * Creates and open menu with list of devices.
	     *
	     * @param {HTMLElement} bindElement HTML element to bind position of menu.
	     * @param {Array<DeviceItem>} devices List of devices.
	     * @param {(device: DeviceItem) => {}} clickHandler Invokes when user clicked on the menu item.
	     */

	  }, {
	    key: "openDeviceMenu",
	    value: function openDeviceMenu(bindElement, devices, clickHandler) {
	      var menuId = 'device_selector';
	      var menu = main_popup.MenuManager.getMenuById(menuId);

	      if (menu) {
	        menu.show();
	        return;
	      }

	      var menuItems = [];
	      devices.map(function (device) {
	        if (device.code === 'delimiter') {
	          menuItems.push(new main_popup.MenuItem({
	            delimiter: true,
	            text: device.langCode ? DeviceUI.messages[device.langCode] : ''
	          }));
	          return;
	        }

	        menuItems.push(new main_popup.MenuItem({
	          id: device.className,
	          html: "".concat(device.name),
	          onclick: function onclick() {
	            main_popup.MenuManager.getMenuById(menuId).close();
	            clickHandler(device);
	          }
	        }));
	      });

	      if (bindElement) {
	        bindElement = bindElement.parentNode;
	      }

	      menu = main_popup.MenuManager.create({
	        id: menuId,
	        bindElement: bindElement,
	        className: 'landing-ui-block-actions-popup',
	        items: menuItems,
	        angle: true,
	        offsetTop: 0,
	        offsetLeft: 40,
	        minWidth: bindElement.offsetWidth,
	        animation: 'fading-slide'
	      });
	      menu.show();
	    }
	  }]);
	  return DeviceUI;
	}();

	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$2(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _frameUrl = /*#__PURE__*/new WeakMap();

	var _editorFrameWrapper = /*#__PURE__*/new WeakMap();

	var _previewElement = /*#__PURE__*/new WeakMap();

	var _previewWindow = /*#__PURE__*/new WeakMap();

	var _currentDevice = /*#__PURE__*/new WeakMap();

	var _editorEnabled = /*#__PURE__*/new WeakMap();

	var _pendingReload = /*#__PURE__*/new WeakMap();

	var _commandsToRefresh = /*#__PURE__*/new WeakMap();

	var _registerListeners = /*#__PURE__*/new WeakSet();

	var _backendAction = /*#__PURE__*/new WeakSet();

	var _reloadPreviewWindow = /*#__PURE__*/new WeakSet();

	var _scrollDevice = /*#__PURE__*/new WeakSet();

	var _resolveDeviceByType = /*#__PURE__*/new WeakSet();

	var _setDevice = /*#__PURE__*/new WeakSet();

	var _adjustPreviewScroll = /*#__PURE__*/new WeakSet();

	var _buildPreview = /*#__PURE__*/new WeakSet();

	var _onClickDeviceSelector = /*#__PURE__*/new WeakSet();

	var _showPreview = /*#__PURE__*/new WeakSet();

	var _hidePreview = /*#__PURE__*/new WeakSet();

	var Device = // window object of iframe

	/**
	 * Device constructor.
	 *
	 * @param {Options} options Constructor options.
	 */
	function Device(_options) {
	  babelHelpers.classCallCheck(this, Device);

	  _classPrivateMethodInitSpec$2(this, _hidePreview);

	  _classPrivateMethodInitSpec$2(this, _showPreview);

	  _classPrivateMethodInitSpec$2(this, _onClickDeviceSelector);

	  _classPrivateMethodInitSpec$2(this, _buildPreview);

	  _classPrivateMethodInitSpec$2(this, _adjustPreviewScroll);

	  _classPrivateMethodInitSpec$2(this, _setDevice);

	  _classPrivateMethodInitSpec$2(this, _resolveDeviceByType);

	  _classPrivateMethodInitSpec$2(this, _scrollDevice);

	  _classPrivateMethodInitSpec$2(this, _reloadPreviewWindow);

	  _classPrivateMethodInitSpec$2(this, _backendAction);

	  _classPrivateMethodInitSpec$2(this, _registerListeners);

	  _classPrivateFieldInitSpec$2(this, _frameUrl, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec$2(this, _editorFrameWrapper, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec$2(this, _previewElement, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec$2(this, _previewWindow, {
	    writable: true,
	    value: void 0
	  });

	  _classPrivateFieldInitSpec$2(this, _currentDevice, {
	    writable: true,
	    value: null
	  });

	  _classPrivateFieldInitSpec$2(this, _editorEnabled, {
	    writable: true,
	    value: false
	  });

	  _classPrivateFieldInitSpec$2(this, _pendingReload, {
	    writable: true,
	    value: false
	  });

	  _classPrivateFieldInitSpec$2(this, _commandsToRefresh, {
	    writable: true,
	    value: ['Landing::upBlock', 'Landing::downBlock', 'Landing::showBlock', 'Landing::hideBlock', 'Landing::markDeletedBlock', 'Landing::addBlock', 'Landing::copyBlock', 'Landing::moveBlock', 'Block::changeNodeName', 'Block::updateContent', 'Block::getContent', 'Landing\\Block::addCard', 'Landing\\Block::cloneCard', 'Landing\\Block::removeCard', 'Landing\\Block::updateNodes', 'Landing\\Block::updateStyles', 'Landing\\Block::saveForm' // fake-action
	    ]
	  });

	  babelHelpers.classPrivateFieldSet(this, _frameUrl, _options.frameUrl);
	  babelHelpers.classPrivateFieldSet(this, _editorFrameWrapper, _options.editorFrameWrapper);

	  _classPrivateMethodGet$2(this, _registerListeners, _registerListeners2).call(this, _options);

	  _classPrivateMethodGet$2(this, _buildPreview, _buildPreview2).call(this, _options);

	  _classPrivateMethodGet$2(this, _showPreview, _showPreview2).call(this);

	  _classPrivateMethodGet$2(this, _setDevice, _setDevice2).call(this, _classPrivateMethodGet$2(this, _resolveDeviceByType, _resolveDeviceByType2).call(this, 'mobile'));
	}
	/**
	 * Registers Handlers you need.
	 *
	 * @param {Options} options Constructor options.
	 */
;

	function _registerListeners2(options) {
	  var _this = this;

	  // when user click different window size
	  BX.addCustomEvent('BX.Landing.Main:editorSizeChange', function (deviceType) {
	    _classPrivateMethodGet$2(_this, _setDevice, _setDevice2).call(_this, _classPrivateMethodGet$2(_this, _resolveDeviceByType, _resolveDeviceByType2).call(_this, deviceType));
	  }); // listen messages from editor frame

	  window.addEventListener('message', function (event) {
	    var data = event.data || {};

	    if (data.action === 'editorenable') {
	      if (!!data.payload.enable) {
	        babelHelpers.classPrivateFieldSet(_this, _editorEnabled, true);
	      } else {
	        if (babelHelpers.classPrivateFieldGet(_this, _pendingReload)) {
	          _classPrivateMethodGet$2(_this, _reloadPreviewWindow, _reloadPreviewWindow2).call(_this);
	        }

	        babelHelpers.classPrivateFieldSet(_this, _editorEnabled, false);
	        babelHelpers.classPrivateFieldSet(_this, _pendingReload, false);
	      }
	    } else if (data.action === 'backendaction') {
	      _classPrivateMethodGet$2(_this, _backendAction, _backendAction2).call(_this, data.payload);
	    }
	  });
	}

	function _backendAction2(payload) {
	  if (babelHelpers.classPrivateFieldGet(this, _commandsToRefresh).includes(payload.action)) {
	    if (babelHelpers.classPrivateFieldGet(this, _editorEnabled)) {
	      babelHelpers.classPrivateFieldSet(this, _pendingReload, true);
	    } else {
	      var _payload$data, _payload$data3, _payload$data3$update, _payload$data3$update2;

	      var blockId = null;

	      if ((_payload$data = payload.data) !== null && _payload$data !== void 0 && _payload$data.block) {
	        var _payload$data2;

	        blockId = (_payload$data2 = payload.data) === null || _payload$data2 === void 0 ? void 0 : _payload$data2.block;
	      }

	      if ((_payload$data3 = payload.data) !== null && _payload$data3 !== void 0 && (_payload$data3$update = _payload$data3.updateNodes) !== null && _payload$data3$update !== void 0 && (_payload$data3$update2 = _payload$data3$update.data) !== null && _payload$data3$update2 !== void 0 && _payload$data3$update2.block) {
	        var _payload$data4, _payload$data4$update, _payload$data4$update2;

	        blockId = (_payload$data4 = payload.data) === null || _payload$data4 === void 0 ? void 0 : (_payload$data4$update = _payload$data4.updateNodes) === null || _payload$data4$update === void 0 ? void 0 : (_payload$data4$update2 = _payload$data4$update.data) === null || _payload$data4$update2 === void 0 ? void 0 : _payload$data4$update2.block;
	      }

	      _classPrivateMethodGet$2(this, _reloadPreviewWindow, _reloadPreviewWindow2).call(this, blockId);
	    }
	  }
	}

	function _reloadPreviewWindow2(blockId) {
	  if (babelHelpers.classPrivateFieldGet(this, _previewWindow)) {
	    var blockIdPrefix = 'editor';
	    var timestamp = Date.now();
	    babelHelpers.classPrivateFieldGet(this, _previewWindow).location.href = babelHelpers.classPrivateFieldGet(this, _frameUrl) + '?ts=' + timestamp + '&scrollTo=' + blockIdPrefix + blockId;
	  }
	}

	function _scrollDevice2(topInPercent) {
	  if (babelHelpers.classPrivateFieldGet(this, _previewWindow)) {
	    var _document = babelHelpers.classPrivateFieldGet(this, _previewWindow).document;
	    var scrollHeight = Math.max(_document.body.scrollHeight, _document.documentElement.scrollHeight, _document.body.offsetHeight, _document.documentElement.offsetHeight, _document.body.clientHeight, _document.documentElement.clientHeight);
	    babelHelpers.classPrivateFieldGet(this, _previewWindow).scroll(0, scrollHeight * topInPercent / 100);
	  }
	}

	function _resolveDeviceByType2(deviceType) {
	  var _Devices$defaultDevic;

	  var deviceCode = localStorage.getItem('deviceCode');

	  if (deviceCode && Devices.devices[deviceCode]) {
	    return Devices.devices[deviceCode];
	  }

	  deviceCode = (_Devices$defaultDevic = Devices.defaultDevice) === null || _Devices$defaultDevic === void 0 ? void 0 : _Devices$defaultDevic[deviceType];

	  if (!deviceCode) {
	    return;
	  }

	  return Devices.devices[deviceCode];
	}

	function _setDevice2(newDevice) {
	  if (!newDevice) {
	    return;
	  }

	  localStorage.setItem('deviceCode', newDevice.code); // remove old class within preview

	  if (babelHelpers.classPrivateFieldGet(this, _currentDevice)) {
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldGet(this, _previewElement), babelHelpers.classPrivateFieldGet(this, _currentDevice).className);
	    babelHelpers.classPrivateFieldGet(this, _previewElement).style.removeProperty("top");
	  }

	  babelHelpers.classPrivateFieldSet(this, _currentDevice, newDevice);
	  babelHelpers.classPrivateFieldGet(this, _previewElement).querySelector('[data-role="device-name"]').innerHTML = newDevice.name;
	  babelHelpers.classPrivateFieldGet(this, _previewElement).querySelector('[data-role="device-orientation"]').innerHTML = localStorage.getItem('deviceOrientation');
	  var frame = babelHelpers.classPrivateFieldGet(this, _previewElement).querySelector('[data-role="landing-device-preview-iframe"]');
	  var frameWrapper = babelHelpers.classPrivateFieldGet(this, _previewElement).querySelector('[data-role="landing-device-preview"]'); // scale for device

	  if (frame && frameWrapper && babelHelpers.classPrivateFieldGet(this, _currentDevice).width && babelHelpers.classPrivateFieldGet(this, _currentDevice).height) {
	    var scale = window.innerHeight / (babelHelpers.classPrivateFieldGet(this, _currentDevice).height + 300);
	    var padding = parseInt(window.getComputedStyle(frameWrapper).padding);
	    var param1 = babelHelpers.classPrivateFieldGet(this, _currentDevice).width;
	    var param2 = babelHelpers.classPrivateFieldGet(this, _currentDevice).height;

	    if (localStorage.getItem('deviceOrientation') === 'landscape') {
	      param1 = babelHelpers.classPrivateFieldGet(this, _currentDevice).height;
	      param2 = babelHelpers.classPrivateFieldGet(this, _currentDevice).width;
	    }

	    frame.style.setProperty("width", "".concat(param1, "px"));
	    frame.style.setProperty("height", "".concat(param2, "px"));
	    frameWrapper.style.setProperty("transform", "scale(".concat(scale, ")"));
	    babelHelpers.classPrivateFieldGet(this, _previewElement).style.setProperty("width", "".concat((param1 + padding * 2) * scale, "px"));
	    babelHelpers.classPrivateFieldGet(this, _previewElement).style.setProperty("height", "".concat((param2 + padding * 2) * scale, "px"));
	  }

	  main_core.Dom.addClass(babelHelpers.classPrivateFieldGet(this, _previewElement), babelHelpers.classPrivateFieldGet(this, _currentDevice).className);
	}

	function _adjustPreviewScroll2() {
	  var documentEditorFrame = babelHelpers.classPrivateFieldGet(this, _editorFrameWrapper).querySelector('iframe').contentWindow.document;
	  var scrollHeight = Math.max(documentEditorFrame.body.scrollHeight, documentEditorFrame.documentElement.scrollHeight, documentEditorFrame.body.offsetHeight, documentEditorFrame.documentElement.offsetHeight, documentEditorFrame.body.clientHeight, documentEditorFrame.documentElement.clientHeight);
	  var scrollTop = documentEditorFrame.documentElement.scrollTop || documentEditorFrame.body.scrollTop;

	  _classPrivateMethodGet$2(this, _scrollDevice, _scrollDevice2).call(this, scrollTop / scrollHeight * 100);
	}

	function _buildPreview2(options) {
	  if (!babelHelpers.classPrivateFieldGet(this, _previewElement)) {
	    babelHelpers.classPrivateFieldSet(this, _previewElement, DeviceUI.getPreview({
	      frameUrl: options.frameUrl,
	      clickHandler: _classPrivateMethodGet$2(this, _onClickDeviceSelector, _onClickDeviceSelector2).bind(this),
	      messages: options.messages
	    }));
	    main_core.Dom.hide(babelHelpers.classPrivateFieldGet(this, _previewElement));
	    document.body.appendChild(babelHelpers.classPrivateFieldGet(this, _previewElement)); //#170065
	    //this.#previewElement.querySelector('iframe').contentWindow.addEventListener('load', () => {

	    if (!babelHelpers.classPrivateFieldGet(this, _previewWindow)) {
	      babelHelpers.classPrivateFieldSet(this, _previewWindow, babelHelpers.classPrivateFieldGet(this, _previewElement).querySelector('iframe').contentWindow);
	      var previewDocument = babelHelpers.classPrivateFieldGet(this, _previewElement).querySelector('iframe').contentWindow.document;
	      main_core.Dom.removeClass(previewDocument.querySelector('html'), 'bx-no-touch');
	      main_core.Dom.addClass(previewDocument.querySelector('html'), 'bx-touch');
	    }

	    _classPrivateMethodGet$2(this, _adjustPreviewScroll, _adjustPreviewScroll2).call(this); //});

	  }
	}

	function _onClickDeviceSelector2() {
	  DeviceUI.openDeviceMenu(babelHelpers.classPrivateFieldGet(this, _previewElement).querySelector('[data-role="device-name"]'), Object.values(Devices.devices), _classPrivateMethodGet$2(this, _setDevice, _setDevice2).bind(this));
	}

	function _showPreview2() {
	  main_core.Dom.show(babelHelpers.classPrivateFieldGet(this, _previewElement));
	}

	exports.ExternalControls = ExternalControls;
	exports.Device = Device;

}((this.BX.Landing.View = this.BX.Landing.View || {}),BX,BX.Main));
//# sourceMappingURL=script.js.map
