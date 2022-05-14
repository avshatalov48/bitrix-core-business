this.BX = this.BX || {};
(function (exports,main_core_events,landing_env,landing_loc,landing_ui_panel_content,landing_ui_panel_saveblock,landing_sliderhacks,landing_pageobject,main_core,landing_backend) {
	'use strict';

	/**
	 * Checks that element contains block
	 * @param {HTMLElement} element
	 * @return {boolean}
	 */
	function hasBlock(element) {
	  return !!element && !!element.querySelector('.block-wrapper');
	}

	/**
	 * Checks that element contains "Add new Block" button
	 * @param {HTMLElement} element
	 * @return {boolean}
	 */
	function hasCreateButton(element) {
	  return !!element && !!element.querySelector('button[data-id="insert_first_block"]');
	}

	function onAnimationEnd(element, animationName) {
	  return new Promise(function (resolve) {
	    var onAnimationEndListener = function onAnimationEndListener(event) {
	      if (!animationName || event.animationName === animationName) {
	        resolve(event);
	        main_core.Event.bind(element, 'animationend', onAnimationEndListener);
	      }
	    };

	    main_core.Event.bind(element, 'animationend', onAnimationEndListener);
	  });
	}

	function isEmpty(value) {
	  if (main_core.Type.isNil(value)) {
	    return true;
	  }

	  if (main_core.Type.isArrayLike(value)) {
	    return !value.length;
	  }

	  if (main_core.Type.isObject(value)) {
	    return Object.keys(value).length <= 0;
	  }

	  return true;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["", ""]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	BX.Landing.getMode = function () {
	  return 'edit';
	};
	/**
	 * @memberOf BX.Landing
	 */


	var Main = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Main, _EventEmitter);
	  babelHelpers.createClass(Main, null, [{
	    key: "getMode",
	    value: function getMode() {
	      return 'edit';
	    }
	  }, {
	    key: "createInstance",
	    value: function createInstance(id) {
	      var rootWindow = BX.Landing.PageObject.getRootWindow();
	      rootWindow.BX.Landing.Main.instance = new BX.Landing.Main(id);
	    }
	  }, {
	    key: "getInstance",
	    value: function getInstance() {
	      var rootWindow = BX.Landing.PageObject.getRootWindow();
	      rootWindow.BX.Reflection.namespace('BX.Landing.Main');

	      if (rootWindow.BX.Landing.Main.instance) {
	        return rootWindow.BX.Landing.Main.instance;
	      }

	      rootWindow.BX.Landing.Main.instance = new Main(-1);
	      return rootWindow.BX.Landing.Main.instance;
	    }
	  }]);

	  function Main(id) {
	    var _this;

	    babelHelpers.classCallCheck(this, Main);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Main).call(this));

	    _this.setEventNamespace('BX.Landing.Main');

	    var options = landing_env.Env.getInstance().getOptions();
	    _this.id = id;
	    _this.options = Object.freeze(options);
	    _this.blocks = _this.options.blocks;
	    _this.currentBlock = null;
	    _this.isDesignBlockModeFlag = _this.options["design_block"] === true;
	    _this.loadedDeps = {};
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.onSliderFormLoaded = _this.onSliderFormLoaded.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onBlockDelete = _this.onBlockDelete.bind(babelHelpers.assertThisInitialized(_this));
	    BX.addCustomEvent('Landing.Block:onAfterDelete', _this.onBlockDelete);

	    _this.adjustEmptyAreas();

	    BX.Landing.UI.Panel.StatusPanel.setLastModified(options.lastModified);

	    if (!_this.isDesignBlockModeFlag) {
	      BX.Landing.UI.Panel.StatusPanel.getInstance().show();
	    }

	    var pageType = landing_env.Env.getInstance().getType();

	    if (pageType === Main.TYPE_KNOWLEDGE || pageType === Main.TYPE_GROUP) {
	      var mainArea = document.querySelector('.landing-main');

	      if (main_core.Type.isDomNode(mainArea)) {
	        main_core.Dom.addClass(mainArea, 'landing-ui-collapse');
	      }
	    }

	    return _this;
	  }

	  babelHelpers.createClass(Main, [{
	    key: "isCrmFormPage",
	    value: function isCrmFormPage() {
	      return landing_env.Env.getInstance().getOptions().specialType === 'crm_forms';
	    }
	  }, {
	    key: "isDesignBlockMode",
	    value: function isDesignBlockMode() {
	      return this.isDesignBlockModeFlag;
	    }
	  }, {
	    key: "getSaveBlockPanel",
	    value: function getSaveBlockPanel() {
	      var panel = new landing_ui_panel_saveblock.SaveBlock('save_block_panel', {
	        block: this.currentBlock
	      });
	      panel.layout.hidden = true;
	      panel.content.hidden = false;
	      main_core.Dom.append(panel.layout, document.body);
	      return panel;
	    }
	  }, {
	    key: "getBlocksPanel",
	    value: function getBlocksPanel() {
	      var _this2 = this;

	      return this.cache.remember('blockPanel', function () {
	        var blocksPanel = _this2.createBlocksPanel();

	        setTimeout(function () {
	          if (blocksPanel.sidebarButtons.get(_this2.options.default_section)) {
	            blocksPanel.sidebarButtons.get(_this2.options.default_section).layout.click();
	          } else {
	            babelHelpers.toConsumableArray(blocksPanel.sidebarButtons)[0].layout.click();
	          }
	        });
	        blocksPanel.layout.hidden = true;
	        blocksPanel.content.hidden = false;
	        main_core.Dom.append(blocksPanel.layout, document.body);
	        return blocksPanel;
	      });
	    }
	  }, {
	    key: "hideBlocksPanel",
	    value: function hideBlocksPanel() {
	      if (this.getBlocksPanel()) {
	        return this.getBlocksPanel().hide();
	      }

	      return Promise.resolve();
	    }
	  }, {
	    key: "getLayoutAreas",
	    value: function getLayoutAreas() {
	      return this.cache.remember('layoutAreas', function () {
	        return [].concat(babelHelpers.toConsumableArray(document.body.querySelectorAll('.landing-header')), babelHelpers.toConsumableArray(document.body.querySelectorAll('.landing-sidebar')), babelHelpers.toConsumableArray(document.body.querySelectorAll('.landing-main')), babelHelpers.toConsumableArray(document.body.querySelectorAll('.landing-footer')));
	      });
	    }
	    /**
	     * Creates insert block button
	     * @param {HTMLElement} area
	     * @return {BX.Landing.UI.Button.Plus}
	     */

	  }, {
	    key: "createInsertBlockButton",
	    value: function createInsertBlockButton(area) {
	      var button = new BX.Landing.UI.Button.Plus('insert_first_block', {
	        text: landing_loc.Loc.getMessage('ACTION_BUTTON_CREATE')
	      });
	      button.on('click', this.showBlocksPanel.bind(this, null, area, button));
	      button.on('mouseover', this.onCreateButtonMouseover.bind(this, area, button));
	      button.on('mouseout', this.onCreateButtonMouseout.bind(this, area, button));
	      return button;
	    }
	  }, {
	    key: "onCreateButtonMouseover",
	    value: function onCreateButtonMouseover(area, button) {
	      if (main_core.Dom.hasClass(area, 'landing-header') || main_core.Dom.hasClass(area, 'landing-footer')) {
	        var areas = this.getLayoutAreas();

	        if (areas.length > 1) {
	          var createText = landing_loc.Loc.getMessage('ACTION_BUTTON_CREATE');

	          if (main_core.Dom.hasClass(area, 'landing-main')) {
	            button.setText("".concat(createText, " ").concat(landing_loc.Loc.getMessage('LANDING_ADD_BLOCK_TO_MAIN')));
	          }

	          if (main_core.Dom.hasClass(area, 'landing-header')) {
	            button.setText("".concat(createText, " ").concat(landing_loc.Loc.getMessage('LANDING_ADD_BLOCK_TO_HEADER')));
	          }

	          if (main_core.Dom.hasClass(area, 'landing-sidebar')) {
	            button.setText("".concat(createText, " ").concat(landing_loc.Loc.getMessage('LANDING_ADD_BLOCK_TO_SIDEBAR')));
	          }

	          if (main_core.Dom.hasClass(area, 'landing-footer')) {
	            button.setText("".concat(createText, " ").concat(landing_loc.Loc.getMessage('LANDING_ADD_BLOCK_TO_FOOTER')));
	          }

	          clearTimeout(this.fadeTimeout);
	          this.fadeTimeout = setTimeout(function () {
	            main_core.Dom.addClass(area, 'landing-area-highlight');
	            areas.filter(function (currentArea) {
	              return currentArea !== area;
	            }).forEach(function (currentArea) {
	              main_core.Dom.addClass(currentArea, 'landing-area-fade');
	            });
	          }, 400);
	        }
	      }
	    }
	  }, {
	    key: "onCreateButtonMouseout",
	    value: function onCreateButtonMouseout(area, button) {
	      clearTimeout(this.fadeTimeout);

	      if (main_core.Dom.hasClass(area, 'landing-header') || main_core.Dom.hasClass(area, 'landing-footer')) {
	        var areas = this.getLayoutAreas();

	        if (areas.length > 1) {
	          button.setText(landing_loc.Loc.getMessage('ACTION_BUTTON_CREATE'));
	          areas.forEach(function (currentArea) {
	            main_core.Dom.removeClass(currentArea, 'landing-area-highlight');
	            main_core.Dom.removeClass(currentArea, 'landing-area-fade');
	          });
	        }
	      }
	    }
	  }, {
	    key: "initEmptyArea",
	    value: function initEmptyArea(area) {
	      if (area) {
	        area.innerHTML = '';
	        main_core.Dom.append(this.createInsertBlockButton(area).layout, area);
	        main_core.Dom.addClass(area, 'landing-empty');
	      }
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "destroyEmptyArea",
	    value: function destroyEmptyArea(area) {
	      if (area) {
	        var button = area.querySelector('button[data-id="insert_first_block"]');

	        if (button) {
	          main_core.Dom.remove(button);
	        }

	        main_core.Dom.removeClass(area, 'landing-empty');
	      }
	    }
	    /**
	     * Adjusts areas
	     */

	  }, {
	    key: "adjustEmptyAreas",
	    value: function adjustEmptyAreas() {
	      this.getLayoutAreas().filter(function (area) {
	        return hasBlock(area) && hasCreateButton(area);
	      }).forEach(this.destroyEmptyArea, this);
	      this.getLayoutAreas().filter(function (area) {
	        return !hasBlock(area) && !hasCreateButton(area);
	      }).forEach(this.initEmptyArea, this);
	      var main = document.body.querySelector('main.landing-edit-mode');
	      var isAllEmpty = !this.getLayoutAreas().some(hasBlock);

	      if (main) {
	        if (isAllEmpty) {
	          main_core.Dom.addClass(main, 'landing-empty');
	          return;
	        }

	        main_core.Dom.removeClass(main, 'landing-empty');
	      }
	    }
	    /**
	     * Enables landing controls
	     */
	    // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "enableControls",
	    value: function enableControls() {
	      main_core.Dom.removeClass(document.body, 'landing-ui-hide-controls');
	    }
	    /**
	     * Disables landing controls
	     */
	    // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "disableControls",
	    value: function disableControls() {
	      main_core.Dom.addClass(document.body, 'landing-ui-hide-controls');
	    }
	    /**
	     * Checks that landing controls is enabled
	     * @return {boolean}
	     */
	    // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "isControlsEnabled",
	    value: function isControlsEnabled() {
	      return !main_core.Dom.hasClass(document.body, 'landing-ui-hide-controls');
	    }
	    /**
	     * Set BX classes to mark this landing frame as mobile (touch) device
	     */

	  }, {
	    key: "setTouchDevice",
	    value: function setTouchDevice() {
	      main_core.Dom.removeClass(document.documentElement, 'bx-no-touch');
	      main_core.Dom.addClass(document.documentElement, 'bx-touch');
	    }
	    /**
	     * Set BX classes to mark this landing frame as desktop (no touch) device
	     */

	  }, {
	    key: "setNoTouchDevice",
	    value: function setNoTouchDevice() {
	      main_core.Dom.removeClass(document.documentElement, 'bx-touch');
	      main_core.Dom.addClass(document.documentElement, 'bx-no-touch');
	    }
	    /**
	     * Appends block
	     * @param {addBlockResponse} data
	     * @param {boolean} [withoutAnimation]
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "appendBlock",
	    value: function appendBlock(data, withoutAnimation) {
	      var block = main_core.Tag.render(_templateObject(), data.content);
	      block.id = "block".concat(data.id);

	      if (!withoutAnimation) {
	        main_core.Dom.addClass(block, 'landing-ui-show');
	        onAnimationEnd(block, 'showBlock').then(function () {
	          main_core.Dom.removeClass(block, 'landing-ui-show');
	        });
	      }

	      this.insertToBlocksFlow(block);
	      return block;
	    }
	    /**
	     * Shows blocks list panel
	     * @param {BX.Landing.Block} block
	     * @param {HTMLElement} [area]
	     * @param [button]
	     * @param [insertBefore]
	     */

	  }, {
	    key: "showBlocksPanel",
	    value: function showBlocksPanel(block, area, button, insertBefore) {
	      this.currentBlock = block;
	      this.currentArea = area;
	      this.insertBefore = insertBefore;
	      BX.Landing.UI.Panel.EditorPanel.getInstance().hide();

	      if (this.isCrmFormPage()) {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        main_core.Dom.append(this.getBlocksPanel().layout, rootWindow.document.body);
	        main_core.Dom.append(this.getBlocksPanel().overlay, rootWindow.document.body);
	      }

	      this.getBlocksPanel().show();
	      this.disableAddBlockButtons();

	      if (!!area && !!button) {
	        this.onCreateButtonMouseout(area, button);
	      }
	    }
	  }, {
	    key: "showSaveBlock",
	    value: function showSaveBlock(block) {
	      this.currentBlock = block;
	      this.getSaveBlockPanel().show();
	    }
	  }, {
	    key: "disableAddBlockButtons",
	    value: function disableAddBlockButtons() {
	      landing_pageobject.PageObject.getBlocks().forEach(function (block) {
	        var panel = block.panels.get('create_action');

	        if (panel) {
	          var button = panel.buttons.get('insert_after');

	          if (button) {
	            button.disable();
	          }
	        }
	      });
	    }
	  }, {
	    key: "enableAddBlockButtons",
	    value: function enableAddBlockButtons() {
	      landing_pageobject.PageObject.getBlocks().forEach(function (block) {
	        var panel = block.panels.get('create_action');

	        if (panel) {
	          var button = panel.buttons.get('insert_after');

	          if (button) {
	            button.enable();
	          }
	        }
	      });
	    }
	    /**
	     * Creates blocks list panel
	     * @returns {BX.Landing.UI.Panel.Content}
	     */

	  }, {
	    key: "createBlocksPanel",
	    value: function createBlocksPanel() {
	      var _this3 = this;

	      var blocks = this.options.blocks;
	      var categories = Object.keys(blocks);
	      var panel = new landing_ui_panel_content.Content('blocks_panel', {
	        title: landing_loc.Loc.getMessage('LANDING_CONTENT_BLOCKS_TITLE'),
	        className: 'landing-ui-panel-block-list',
	        scrollAnimation: true
	      });
	      panel.subscribe('onCancel', function () {
	        _this3.enableAddBlockButtons();
	      });
	      categories.forEach(function (categoryId) {
	        var hasItems = !isEmpty(blocks[categoryId].items);
	        var isPopular = categoryId === 'popular';
	        var isSeparator = blocks[categoryId].separator;

	        if (hasItems && !isPopular || isSeparator) {
	          panel.appendSidebarButton(_this3.createBlockPanelSidebarButton(categoryId, blocks[categoryId]));
	        }
	      });
	      panel.appendSidebarButton(new BX.Landing.UI.Button.SidebarButton('feedback_button', {
	        className: 'landing-ui-button-sidebar-feedback',
	        text: landing_loc.Loc.getMessage('LANDING_BLOCKS_LIST_FEEDBACK_BUTTON'),
	        onClick: this.showFeedbackForm.bind(this)
	      }));
	      return panel;
	    }
	    /**
	     * Shows feedback form
	     * @param data
	     */

	  }, {
	    key: "showSliderFeedbackForm",
	    value: function showSliderFeedbackForm() {
	      var _this4 = this;
	      main_core.Runtime.loadExtension('ui.feedback.form').then(function () {
	        var data = {};
	        data.bitrix24 = _this4.options.server_name;
	        data.siteId = _this4.options.site_id;
	        data.siteUrl = _this4.options.url;
	        data.siteTemplate = _this4.options.xml_id;
	        data.productType = _this4.options.productType || 'Undefined';

	        data.typeproduct = function () {
	          if (_this4.options.params.type === Main.TYPE_GROUP) {
	            return 'KNOWLEDGE_GROUP';
	          }

	          return _this4.options.params.type;
	        }();

	        BX.UI.Feedback.Form.open({
	          id: Math.random() + '',
	          forms: _this4.getFeedbackFormOptions(),
	          presets: data
	        });
	      });
	    }
	    /**
	     * Gets feedback form options
	     * @return {{id: string, sec: string, lang: string}}
	     */
	    // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "getFeedbackFormOptions",
	    value: function getFeedbackFormOptions() {
	      return [{
	        zones: ['en', 'eu', 'in', 'uk'],
	        id: 16,
	        lang: 'en',
	        sec: '3h483y'
	      }, {
	        zones: ['ru', 'by', 'kz'],
	        id: 8,
	        lang: 'ru',
	        sec: 'x80yjw'
	      }, {
	        zones: ['ua'],
	        id: 18,
	        lang: 'ua',
	        sec: 'd9e09o'
	      }, {
	        zones: ['la', 'co', 'mx'],
	        id: 14,
	        lang: 'la',
	        sec: 'wu561i'
	      }, {
	        zones: ['de'],
	        id: 10,
	        lang: 'de',
	        sec: 'eraz2q'
	      }, {
	        zones: ['com.br', 'br'],
	        id: 12,
	        lang: 'br',
	        sec: 'r6wvge'
	      }];
	    }
	    /**
	     * Handles feedback loaded event
	     */

	  }, {
	    key: "onSliderFormLoaded",
	    value: function onSliderFormLoaded() {
	      this.sliderFormLoader.hide();
	    }
	    /**
	     * Shows feedback form for blocks list panel
	     */

	  }, {
	    key: "showFeedbackForm",
	    value: function showFeedbackForm() {
	      this.showSliderFeedbackForm({
	        target: 'blocksList'
	      });
	    }
	    /**
	     * Initialises feedback form
	     */
	    // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "initFeedbackForm",
	    value: function initFeedbackForm() {
	      var rootWindow = landing_pageobject.PageObject.getRootWindow();

	      (function (w, d, u, b) {
	        w.Bitrix24FormObject = b;

	        w[b] = w[b] || function () {
	          // eslint-disable-next-line prefer-rest-params
	          arguments[0].ref = u; // eslint-disable-next-line prefer-rest-params

	          (w[b].forms = w[b].forms || []).push(arguments[0]);
	        };

	        if (w[b].forms) return;
	        var s = d.createElement('script');
	        var r = 1 * new Date();
	        s.async = 1;
	        s.src = "".concat(u, "?").concat(r);
	        var h = d.getElementsByTagName('script')[0];
	        h.parentNode.insertBefore(s, h);
	      })(rootWindow, rootWindow.document, 'https://landing.bitrix24.ru/bitrix/js/crm/form_loader.js', 'b24formFeedBack');
	    }
	    /**
	     * Creates blocks list panel sidebar button
	     * @param {string} category
	     * @param {object} options
	     * @returns {BX.Landing.UI.Button.SidebarButton}
	     */

	  }, {
	    key: "createBlockPanelSidebarButton",
	    value: function createBlockPanelSidebarButton(category, options) {
	      return new BX.Landing.UI.Button.SidebarButton(category, {
	        text: options.name,
	        child: !options.separator,
	        className: options.new ? 'landing-ui-new-section' : '',
	        onClick: this.onBlocksListCategoryChange.bind(this, category)
	      });
	    }
	    /**
	     * Adds dynamically new block to the category.
	     * @param {string} category Category code.
	     * @param {{code: string, name: string, preview: string, section: Array<string>}} block Block data.
	     */

	  }, {
	    key: "addNewBlockToCategory",
	    value: function addNewBlockToCategory(category, block) {
	      if (this.blocks[category]) {
	        var blockCode = block['codeOriginal'] || block['code'];

	        if (category === 'last') {
	          if (!this.lastBlocks) {
	            this.lastBlocks = Object.keys(this.blocks.last.items);
	          }

	          this.lastBlocks.unshift(blockCode);
	        } else {
	          this.blocks[category].items[blockCode] = block;
	        }

	        this.onBlocksListCategoryChange(category);
	      }
	    }
	    /**
	     * Returns page's template code if exists.
	     * @return {string|null}
	     */

	  }, {
	    key: "getTemplateCode",
	    value: function getTemplateCode() {
	      var _Env$getInstance$getO = landing_env.Env.getInstance().getOptions(),
	          tplCode = _Env$getInstance$getO.tplCode;

	      if (tplCode.indexOf('@') > 0) {
	        tplCode = tplCode.split('@')[1];
	      }

	      if (!tplCode || tplCode.length <= 0) {
	        tplCode = null;
	      }

	      return tplCode;
	    }
	    /**
	     * Handles event on blocks list category change
	     * @param {string} category - Category id
	     */

	  }, {
	    key: "onBlocksListCategoryChange",
	    value: function onBlocksListCategoryChange(category) {
	      var _this5 = this;

	      var templateCode = this.getTemplateCode();
	      this.getBlocksPanel().content.hidden = false;
	      this.getBlocksPanel().sidebarButtons.forEach(function (button) {
	        var action = button.id === category ? 'add' : 'remove';
	        button.layout.classList[action]('landing-ui-active');
	      });
	      this.getBlocksPanel().content.innerHTML = '';

	      if (category === 'last') {
	        if (!this.lastBlocks) {
	          this.lastBlocks = Object.keys(this.blocks.last.items);
	        }

	        this.lastBlocks = babelHelpers.toConsumableArray(new Set(this.lastBlocks));
	        this.lastBlocks.forEach(function (blockKey) {
	          var block = _this5.getBlockFromRepository(blockKey);

	          _this5.getBlocksPanel().appendCard(_this5.createBlockCard(blockKey, block));
	        });
	        return;
	      }

	      Object.keys(this.blocks[category].items).forEach(function (blockKey) {
	        var block = _this5.blocks[category].items[blockKey];
	        var blockTplCode = block['tpl_code'] && block['tpl_code'].length > 0 ? block['tpl_code'] : null;

	        if (!templateCode || !blockTplCode || blockTplCode && blockTplCode === templateCode) {
	          _this5.getBlocksPanel().appendCard(_this5.createBlockCard(blockKey, block));
	        }
	      });

	      if (this.getBlocksPanel().content.scrollTop) {
	        requestAnimationFrame(function () {
	          _this5.getBlocksPanel().content.scrollTop = 0;
	        });
	      }
	    } // eslint-disable-next-line consistent-return

	  }, {
	    key: "getBlockFromRepository",
	    value: function getBlockFromRepository(code) {
	      var blocks = this.options.blocks;
	      var categories = Object.keys(blocks);
	      var category = categories.find(function (categoryId) {
	        return code in blocks[categoryId].items;
	      });

	      if (category) {
	        return blocks[category].items[code];
	      }
	    }
	    /**
	     * Handles copy block event
	     * @param {BX.Landing.Block} block
	     */
	    // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "onCopyBlock",
	    value: function onCopyBlock(block) {
	      window.localStorage.landingBlockId = block.id;
	      window.localStorage.landingBlockName = block.manifest.block.name;
	      window.localStorage.landingBlockAction = 'copy';

	      try {
	        window.localStorage.requiredUserAction = JSON.stringify(block.requiredUserActionOptions);
	      } catch (err) {
	        window.localStorage.requiredUserAction = '';
	      }
	    }
	    /**
	     * Handles cut block event
	     * @param {BX.Landing.Block} block
	     */
	    // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "onCutBlock",
	    value: function onCutBlock(block) {
	      window.localStorage.landingBlockId = block.id;
	      window.localStorage.landingBlockName = block.manifest.block.name;
	      window.localStorage.landingBlockAction = 'cut';

	      try {
	        window.localStorage.requiredUserAction = JSON.stringify(block.requiredUserActionOptions);
	      } catch (err) {
	        window.localStorage.requiredUserAction = '';
	      }

	      BX.Landing.PageObject.getBlocks().remove(block);
	      main_core.Dom.remove(block.node);
	      BX.onCustomEvent('Landing.Block:onAfterDelete', [block]);
	    }
	    /**
	     * Handles paste block event
	     * @param {BX.Landing.Block} block
	     */

	  }, {
	    key: "onPasteBlock",
	    value: function onPasteBlock(block) {
	      var _this6 = this;

	      if (window.localStorage.landingBlockId) {
	        var action = 'Landing::copyBlock';

	        if (window.localStorage.landingBlockAction === 'cut') {
	          action = 'Landing::moveBlock';
	        }

	        var requestBody = {};
	        requestBody[action] = {
	          action: action,
	          data: {
	            lid: block.lid || BX.Landing.Main.getInstance().id,
	            block: window.localStorage.landingBlockId,
	            params: {
	              AFTER_ID: block.id,
	              RETURN_CONTENT: 'Y'
	            }
	          }
	        };
	        BX.Landing.Backend.getInstance().batch(action, requestBody, {
	          action: action
	        }).then(function (res) {
	          _this6.currentBlock = block;
	          return _this6.addBlock(res[action].result.content);
	        });
	      }
	    }
	    /**
	     * Adds block from server response
	     * @param {addBlockResponse} res
	     * @param {boolean} [preventHistory = false]
	     * @param {boolean} [withoutAnimation = false]
	     * @return {Promise<T>}
	     */

	  }, {
	    key: "addBlock",
	    value: function addBlock(res, preventHistory, withoutAnimation) {
	      if (this.lastBlocks) {
	        this.lastBlocks.unshift(res.manifest.codeOriginal || res.manifest.code);
	      }

	      var self = this;
	      var block = this.appendBlock(res, withoutAnimation);
	      return this.loadBlockDeps(res).then(function (blockRes) {
	        if (!main_core.Type.isBoolean(preventHistory) || preventHistory === false) {
	          var lid = null;
	          var id = null;

	          if (self.currentBlock) {
	            lid = self.currentBlock.lid;
	            id = self.currentBlock.id;
	          }

	          if (self.currentArea) {
	            lid = main_core.Dom.attr(self.currentArea, 'data-landing');
	            id = main_core.Dom.attr(self.currentArea, 'data-site');
	          } // Add history entry


	          BX.Landing.History.getInstance().push(new BX.Landing.History.Entry({
	            block: blockRes.id,
	            selector: "#block".concat(blockRes.id),
	            command: 'addBlock',
	            undo: '',
	            redo: {
	              currentBlock: id,
	              lid: lid,
	              code: blockRes.manifest.code
	            }
	          }));
	        }

	        self.currentBlock = null;
	        self.currentArea = null;
	        var blockId = parseInt(res.id);
	        var oldBlock = BX.Landing.PageObject.getBlocks().get(blockId);

	        if (oldBlock) {
	          main_core.Dom.remove(oldBlock.node);
	          BX.Landing.PageObject.getBlocks().remove(oldBlock);
	        } // Init block entity


	        void new BX.Landing.Block(block, {
	          id: blockId,
	          requiredUserAction: res.requiredUserAction,
	          manifest: res.manifest,
	          access: res.access,
	          active: main_core.Text.toBoolean(res.active),
	          php: res.php,
	          designed: res.designed,
	          anchor: res.anchor,
	          dynamicParams: res.dynamicParams,
	          repoId: res.repoId
	        });
	        return self.runBlockScripts(res).then(function () {
	          return block;
	        });
	      }).catch(function (err) {
	        console.warn(err);
	      });
	    }
	    /**
	     * Handles edd block event
	     * @param {string} blockCode
	     * @param {*} [restoreId]
	     * @param {?boolean} [preventHistory = false]
	     * @return {Promise<BX.Landing.Block>}
	     */

	  }, {
	    key: "onAddBlock",
	    value: function onAddBlock(blockCode, restoreId, preventHistory) {
	      var _this7 = this;

	      var id = main_core.Text.toNumber(restoreId);
	      this.hideBlocksPanel();
	      return this.showBlockLoader().then(this.loadBlock(blockCode, id)).then(function (res) {
	        return new Promise(function (resolve) {
	          setTimeout(function () {
	            resolve(res);
	          }, 500);
	        });
	      }).then(function (res) {
	        res.manifest.codeOriginal = blockCode;

	        var p = _this7.addBlock(res, preventHistory, false);

	        _this7.insertBefore = false;

	        _this7.adjustEmptyAreas();

	        void _this7.hideBlockLoader();

	        _this7.enableAddBlockButtons();

	        return p;
	      });
	    }
	    /**
	     * Inserts element to blocks flow.
	     * Element can be inserted after current block or after last block
	     * @param {HTMLElement} element
	     */

	  }, {
	    key: "insertToBlocksFlow",
	    value: function insertToBlocksFlow(element) {
	      var isCurrentBlockAvailable = this.currentBlock && this.currentBlock.node && this.currentBlock.node.parentNode;

	      if (isCurrentBlockAvailable && !this.insertBefore) {
	        main_core.Dom.insertAfter(element, this.currentBlock.node);
	        return;
	      }

	      if (isCurrentBlockAvailable && this.insertBefore) {
	        main_core.Dom.insertBefore(element, this.currentBlock.node);
	      }

	      main_core.Dom.prepend(element, this.currentArea);
	    }
	    /**
	     * Gets block loader
	     * @return {HTMLElement}
	     */

	  }, {
	    key: "getBlockLoader",
	    value: function getBlockLoader() {
	      if (!this.blockLoader) {
	        this.blockLoader = new BX.Loader({
	          size: 60
	        });
	        this.blockLoaderContainer = main_core.Dom.create('div', {
	          props: {
	            className: 'landing-block-loader-container'
	          },
	          children: [this.blockLoader.layout]
	        });
	      }

	      return this.blockLoaderContainer;
	    }
	    /**
	     * Shows block loader
	     * @return {Function}
	     */

	  }, {
	    key: "showBlockLoader",
	    value: function showBlockLoader() {
	      this.insertToBlocksFlow(this.getBlockLoader());
	      this.blockLoader.show();
	      return Promise.resolve();
	    }
	    /**
	     * Hides block loader
	     * @return {Function}
	     */

	  }, {
	    key: "hideBlockLoader",
	    value: function hideBlockLoader() {
	      main_core.Dom.remove(this.getBlockLoader());
	      this.blockLoader = null;
	      return Promise.resolve();
	    }
	    /**
	     * Loads block dependencies
	     * @param {addBlockResponse} data
	     * @returns {Promise<addBlockResponse>}
	     */

	  }, {
	    key: "loadBlockDeps",
	    value: function loadBlockDeps(data) {
	      var _this8 = this;

	      var ext = BX.processHTML(data.content_ext);

	      if (BX.type.isArray(ext.SCRIPT)) {
	        ext.SCRIPT = ext.SCRIPT.filter(function (item) {
	          return !item.isInternal;
	        });
	      }

	      var loadedScripts = 0;
	      var scriptsCount = data.js.length + ext.SCRIPT.length + ext.STYLE.length + data.css.length;
	      var resPromise = null;

	      if (!this.loadedDeps[data.manifest.code] && scriptsCount > 0) {
	        resPromise = new Promise(function (resolve) {
	          function onLoad() {
	            loadedScripts += 1;

	            if (loadedScripts === scriptsCount) {
	              resolve(data);
	            }
	          }

	          if (scriptsCount > loadedScripts) {
	            // Load extensions files
	            ext.SCRIPT.forEach(function (item) {
	              if (!item.isInternal) {
	                BX.loadScript(item.JS, onLoad);
	              }
	            });
	            ext.STYLE.forEach(function (item) {
	              BX.loadScript(item, onLoad);
	            }); // Load block files

	            data.css.forEach(function (item) {
	              BX.loadScript(item, onLoad);
	            });
	            data.js.forEach(function (item) {
	              BX.loadScript(item, onLoad);
	            });
	          } else {
	            onLoad();
	          }

	          _this8.loadedDeps[data.manifest.code] = true;
	        });
	      } else {
	        resPromise = Promise.resolve(data);
	      }

	      return resPromise;
	    }
	    /**
	     * Executes block scripts
	     * @param data
	     * @return {Promise}
	     */
	    // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "runBlockScripts",
	    value: function runBlockScripts(data) {
	      return new Promise(function (resolve) {
	        var scripts = BX.processHTML(data.content).SCRIPT;

	        if (scripts.length) {
	          BX.ajax.processScripts(scripts, undefined, function () {
	            resolve(data);
	          });
	        } else {
	          resolve(data);
	        }
	      });
	    }
	    /**
	     * Load new block from server
	     * @param {string} blockCode
	     * @param {int} [restoreId]
	     * @returns {Function}
	     */

	  }, {
	    key: "loadBlock",
	    value: function loadBlock(blockCode, restoreId) {
	      var _this9 = this;

	      return function () {
	        var lid = _this9.id;
	        var siteId = _this9.options.site_id;

	        if (_this9.currentBlock) {
	          lid = _this9.currentBlock.lid;
	          siteId = _this9.currentBlock.siteId;
	        }

	        if (_this9.currentArea) {
	          lid = main_core.Dom.attr(_this9.currentArea, 'data-landing');
	          siteId = main_core.Dom.attr(_this9.currentArea, 'data-site');
	        }

	        var requestBody = {
	          lid: lid,
	          siteId: siteId
	        };
	        var fields = {
	          ACTIVE: 'Y',
	          CODE: blockCode,
	          AFTER_ID: _this9.currentBlock ? _this9.currentBlock.id : 0,
	          RETURN_CONTENT: 'Y'
	        };

	        if (!restoreId) {
	          requestBody.fields = fields;
	          return landing_backend.Backend.getInstance().action('Landing::addBlock', requestBody, {
	            code: blockCode
	          }).then(function (result) {
	            if (_this9.insertBefore) {
	              return landing_backend.Backend.getInstance().action('Landing::upBlock', {
	                lid: lid,
	                siteId: siteId,
	                block: result.id
	              }).then(function () {
	                return result;
	              });
	            }

	            return result;
	          });
	        }

	        requestBody = {
	          undeleete: {
	            action: 'Landing::markUndeletedBlock',
	            data: {
	              lid: lid,
	              block: restoreId
	            }
	          },
	          getContent: {
	            action: 'Block::getContent',
	            data: {
	              block: restoreId,
	              lid: lid,
	              fields: fields,
	              editMode: 1
	            }
	          }
	        };
	        return BX.Landing.Backend.getInstance().batch('Landing::addBlock', requestBody, {
	          code: blockCode
	        }).then(function (res) {
	          res.getContent.result.id = restoreId;
	          return res.getContent.result;
	        });
	      };
	    }
	    /**
	     * Creates block preview card
	     * @param {string} blockKey - Block key (folder name)
	     * @param {{name: string, [preview]: ?string, [new]: ?boolean}} block - Object with block data
	     * @param {string} [mode]
	     * @returns {BX.Landing.UI.Card.BlockPreviewCard}
	     */

	  }, {
	    key: "createBlockCard",
	    value: function createBlockCard(blockKey, block, mode) {
	      return new BX.Landing.UI.Card.BlockPreviewCard({
	        title: block.name,
	        image: block.preview,
	        code: blockKey,
	        app_expired: block.app_expired,
	        favorite: !!block.favorite,
	        favoriteMy: !!block.favoriteMy,
	        repo_id: block.repo_id,
	        mode: mode,
	        isNew: block.new === true,
	        onClick: this.onAddBlock.bind(this, blockKey)
	      });
	    }
	    /**
	     * Handles block delete event
	     */

	  }, {
	    key: "onBlockDelete",
	    value: function onBlockDelete(block) {
	      if (!block.parent.querySelector('.block-wrapper')) {
	        this.adjustEmptyAreas();
	      }
	    }
	    /**
	     * Shows page overlay
	     */
	    // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "showOverlay",
	    value: function showOverlay() {
	      var main = document.querySelector('main.landing-edit-mode');

	      if (main) {
	        main_core.Dom.addClass(main, 'landing-ui-overlay');
	      }
	    }
	    /**
	     * Hides page overlay
	     */
	    // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "hideOverlay",
	    value: function hideOverlay() {
	      var main = document.querySelector('main.landing-edit-mode');

	      if (main) {
	        main_core.Dom.removeClass(main, 'landing-ui-overlay');
	      }
	    }
	  }, {
	    key: "reloadSlider",
	    value: function reloadSlider(url) {
	      return landing_sliderhacks.SliderHacks.reloadSlider(url, window.parent);
	    }
	  }]);
	  return Main;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(Main, "TYPE_PAGE", 'PAGE');
	babelHelpers.defineProperty(Main, "TYPE_STORE", 'STORE');
	babelHelpers.defineProperty(Main, "TYPE_KNOWLEDGE", 'KNOWLEDGE');
	babelHelpers.defineProperty(Main, "TYPE_GROUP", 'GROUP');

	exports.Main = Main;

}((this.BX.Landing = this.BX.Landing || {}),BX.Event,BX.Landing,BX.Landing,BX.Landing.UI.Panel,BX.Landing.UI.Panel,BX.Landing,BX.Landing,BX,BX.Landing));
//# sourceMappingURL=main.bundle.js.map
