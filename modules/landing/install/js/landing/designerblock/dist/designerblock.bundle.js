/* eslint-disable */
this.BX = this.BX || {};
(function (exports,landing_backend,landing_env,landing_metrika,landing_ui_highlight,landing_loc,landing_ui_panel_content,main_core) {
	'use strict';

	var Node = /*#__PURE__*/function () {
	  function Node(options) {
	    babelHelpers.classCallCheck(this, Node);
	    this.element = options.element;
	    this.selector = options.selector;
	    this.cardSelector = options.cardSelector;
	    this.onHover = options.onHover;
	    this.pseudoElement = main_core.Dom.hasClass(this.element, 'landing-designer-block-pseudo-last');
	    main_core.Event.bind(this.element, 'mouseover', this.onMouseOver.bind(this));
	    if (options.className) {
	      main_core.Dom.addClass(this.element, options.className);
	    }
	  }
	  babelHelpers.createClass(Node, [{
	    key: "isPseudoElement",
	    value: function isPseudoElement() {
	      return this.pseudoElement;
	    }
	  }, {
	    key: "getSelector",
	    value: function getSelector() {
	      return (this.cardSelector ? this.cardSelector + ' ' : '') + this.selector;
	    }
	  }, {
	    key: "getCardSelector",
	    value: function getCardSelector() {
	      return this.cardSelector;
	    }
	  }, {
	    key: "getOriginalSelector",
	    value: function getOriginalSelector() {
	      return this.selector;
	    }
	  }, {
	    key: "getElement",
	    value: function getElement() {
	      return this.element;
	    }
	  }, {
	    key: "onMouseOver",
	    value: function onMouseOver(event) {
	      event.stopPropagation();
	      this.onHover(this);
	    }
	  }]);
	  return Node;
	}();

	var _templateObject, _templateObject2, _templateObject3;
	var DesignerBlockUI = /*#__PURE__*/function () {
	  function DesignerBlockUI() {
	    babelHelpers.classCallCheck(this, DesignerBlockUI);
	  }
	  babelHelpers.createClass(DesignerBlockUI, null, [{
	    key: "getHoverDiv",
	    value: function getHoverDiv() {
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-designer-block-node-hover\"></div>"])));
	    }
	  }, {
	    key: "getPseudoLast",
	    value: function getPseudoLast() {
	      return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-designer-block-pseudo-last\"></div>"])));
	    }
	  }, {
	    key: "getAddNodeButton",
	    value: function getAddNodeButton() {
	      return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-designer-block-node-hover-add\">\n\t\t\t\t<span class=\"landing-designer-block-node-hover-add-title\">\n\t\t\t\t\t", "\n\t\t\t\t</span>\n\t\t\t</div>"])), landing_loc.Loc.getMessage('LANDING_DESIGN_BLOCK_REPO_BUTTON'));
	    }
	  }]);
	  return DesignerBlockUI;
	}();

	var _templateObject$1;
	var RepoPanel = /*#__PURE__*/function (_Content) {
	  babelHelpers.inherits(RepoPanel, _Content);
	  function RepoPanel(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, RepoPanel);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(RepoPanel).call(this, 'design_repo', {
	      title: landing_loc.Loc.getMessage('LANDING_DESIGN_BLOCK_REPO_TITLE'),
	      scrollAnimation: true
	    }));
	    _this.currentCategory = null;
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.onElementSelect = options.onElementSelect;
	    _this.renderTo(parent.document.body ? parent.document.body : document.body);
	    main_core.Dom.addClass(_this.layout, 'landing-ui-panel-repo');
	    return _this;
	  }
	  babelHelpers.createClass(RepoPanel, [{
	    key: "addRepository",
	    value: function addRepository(repository) {
	      var _this2 = this;
	      repository.map(function (item) {
	        _this2.addElement(item);
	      });
	    }
	  }, {
	    key: "makeElementUnique",
	    value: function makeElementUnique(element) {
	      var _this3 = this;
	      var newManifest = {};
	      var newStyleManifest = {};
	      var origNodes = element.manifest.nodes;
	      Object.keys(element.manifest.nodes).map(function (selector) {
	        var randPostfix = '-' + _this3.randomNum(1000, 9999);
	        var className = selector.substring(1);
	        element.html = element.html.replaceAll(new RegExp(className + '([\\s"]{1})', 'g'), className + randPostfix + '$1');
	        newManifest[selector + randPostfix] = element.manifest.nodes[selector];
	        if (element.manifest.style && selector in element.manifest.style) {
	          newStyleManifest[selector + randPostfix] = element.manifest.style[selector];
	        }
	      });
	      element.manifest.nodes = newManifest;
	      if (element.manifest.style) {
	        Object.keys(element.manifest.style).map(function (selector) {
	          if (selector in origNodes) {
	            return;
	          }
	          var randPostfix = '-' + _this3.randomNum(1000, 9999);
	          var className = selector.substring(1);
	          element.html = element.html.replaceAll(new RegExp(className + '([\\s"]{1})', 'g'), className + randPostfix + '$1');
	          newStyleManifest[selector + randPostfix] = element.manifest.style[selector];
	        });
	        element.manifest.style = newStyleManifest;
	      }
	      return element;
	    }
	  }, {
	    key: "addElement",
	    value: function addElement(element) {
	      var _this4 = this;
	      var nodeCard = new BX.Landing.UI.Card.BlockPreviewCard({
	        title: element.name,
	        image: '/bitrix/images/landing/designerblock/presets/' + element.code + '.jpg',
	        onClick: function onClick() {
	          _this4.onElementSelect(_this4.makeElementUnique(element));
	          void _this4.hide();
	        }
	      });
	      this.appendCard(nodeCard);
	    }
	  }, {
	    key: "randomNum",
	    value: function randomNum(min, max) {
	      return parseInt(Math.random() * (max - min) + min);
	    }
	  }, {
	    key: "getListContainer",
	    value: function getListContainer() {
	      return this.cache.remember('listContainer', function () {
	        return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-layer-list-container\"></div>"])));
	      });
	    }
	  }]);
	  return RepoPanel;
	}(landing_ui_panel_content.Content);

	var RepoManager = /*#__PURE__*/function () {
	  function RepoManager(options) {
	    babelHelpers.classCallCheck(this, RepoManager);
	    this.panel = new RepoPanel({
	      onElementSelect: options.onElementSelect
	    });
	    this.panel.addRepository(options.repository);
	  }
	  babelHelpers.createClass(RepoManager, [{
	    key: "showPanel",
	    value: function showPanel() {
	      this.panel.show().then();
	    }
	  }]);
	  return RepoManager;
	}();

	var _templateObject$2, _templateObject2$1, _templateObject3$1;
	var DesignerBlock = /*#__PURE__*/function () {
	  function DesignerBlock(blockNode, options) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, DesignerBlock);
	    babelHelpers.defineProperty(this, "hoverArea", null);
	    babelHelpers.defineProperty(this, "activeNode", null);
	    babelHelpers.defineProperty(this, "changed", false);
	    babelHelpers.defineProperty(this, "saving", false);
	    if (!blockNode) {
	      return;
	    }
	    this.originalNode = blockNode;
	    this.blockNode = blockNode.children[0];
	    this.blockCode = options.code;
	    this.blockId = options.id;
	    this.designed = options.designed;
	    this.autoPublicationEnabled = options.autoPublicationEnabled;
	    this.landingId = options.lid;
	    this.nodes = options.manifest.nodes;
	    this.highlight = new landing_ui_highlight.Highlight();
	    this.cardSelectors = options.manifest.cards ? Object.keys(options.manifest.cards) : [];
	    this.designAllowed = !!landing_env.Env.getInstance().getOptions().design_block_allowed;
	    this.cardSelectors.push(''); // for without cards elements
	    this.nodeMap = new WeakMap();
	    this.metrika = new landing_metrika.Metrika(true);
	    this.repoManager = new RepoManager({
	      repository: options.repository,
	      onElementSelect: this.addElement.bind(this)
	    });
	    this.saveButton = parent.document.getElementById('landing-design-block-save') || top.document.getElementById('landing-design-block-save') || document.getElementById('landing-design-block-save');
	    BX.addCustomEvent('Landing.Editor:load', function () {
	      _this.preventEvents();
	      _this.initHistoryEvents();
	      _this.initTopPanel();
	      _this.initNodes();
	      _this.initGrid();
	      _this.initSliders();
	      _this.initHoverArea();
	    });
	  }
	  babelHelpers.createClass(DesignerBlock, [{
	    key: "clearHtml",
	    value: function clearHtml(content) {
	      return content.replace(/<div class="[^"]*landing-designer-block-pseudo-last[^"]*"[^>]*>[\s]*<\/div>/g, '').replace(/<div class="[^"]*landing-highlight-border[^"]*"[^>]*>[\s]*<\/div>/g, '').replace(/url\(&quot;(.*?)&quot;\)/g, 'url($1)').replace(/\s*data-(landingwrapper)="[^"]+"\s*/g, ' ').replace(/\s*[\w-_]+--type-wrapper\s*/g, ' ').replace(/<div[\s]*>[\s]*<\/div>/g, '').replace(/\s*style=""/g, '').replace(/cursor: pointer;/g, '').replace(/user-select: none;/g, '');
	    }
	  }, {
	    key: "preventEvents",
	    value: function preventEvents() {
	      var _this2 = this;
	      var preventMap = {
	        a: 'click',
	        form: 'submit',
	        input: 'keydown'
	      };
	      Object.keys(preventMap).map(function (tag) {
	        babelHelpers.toConsumableArray(_this2.blockNode.querySelectorAll(tag)).map(function (node) {
	          main_core.Event.bind(node, preventMap[tag], function (e) {
	            e.preventDefault();
	          });
	        });
	      });
	    }
	  }, {
	    key: "initHistoryEvents",
	    value: function initHistoryEvents() {
	      var _this3 = this;
	      BX.Landing.History.getInstance().setTypeDesignerBlock(this.blockId).then(function () {
	        return landing_backend.Backend.getInstance().action("History::clearDesignerBlock", {
	          blockId: _this3.blockId
	        });
	      });
	      var body = this.getDocumentBody();
	      top.BX.addCustomEvent('Landing:onHistoryAddNode', function (tags) {
	        var elementAdded = false;
	        tags.map(function (tag) {
	          var insertAfterSelector = tag.insertAfterSelector || null;
	          var parentNodeSelector = tag.parentNodeSelector || null;
	          var element = main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["", ""])), tag.elementHtml);
	          if (insertAfterSelector) {
	            elementAdded = true;
	            main_core.Dom.insertAfter(element, body.querySelector(insertAfterSelector));
	          } else if (parentNodeSelector) {
	            elementAdded = true;
	            main_core.Dom.prepend(element, body.querySelector(parentNodeSelector));
	          }
	        });
	        if (elementAdded) {
	          _this3.refreshManifest();
	          setTimeout(function () {
	            _this3.sendLabel('designerBlock', 'onHistoryAddNode');
	          }, 0);
	        }
	      });
	      top.BX.addCustomEvent('Landing:onHistoryRemoveNode', function (tags) {
	        tags.map(function (tag) {
	          _this3.removeNode(body.querySelector(tag.elementSelector));
	        });
	        _this3.refreshManifest();
	        setTimeout(function () {
	          _this3.sendLabel('designerBlock', 'onHistoryRemoveNode');
	        }, 0);
	      });
	    }
	  }, {
	    key: "initTopPanel",
	    value: function initTopPanel() {
	      var _this4 = this;
	      main_core.Event.bind(this.saveButton, 'click', function () {
	        _this4.highlight.hide(true);
	        var finishCallback = function finishCallback() {
	          if (BX.SidePanel && BX.SidePanel.Instance) {
	            BX.SidePanel.Instance.close();
	          }
	        };
	        if (!_this4.changed) {
	          finishCallback();
	          return;
	        }
	        if (!_this4.designAllowed) {
	          top.BX.UI.InfoHelper.show('limit_crm_superblock');
	          return;
	        }
	        _this4.saving = true;
	        var batch = {};
	        batch['Block::updateContent'] = {
	          action: 'Block::updateContent',
	          data: {
	            lid: _this4.landingId,
	            block: _this4.blockId,
	            content: _this4.clearHtml(_this4.originalNode.innerHTML).replaceAll(' style="', ' bxstyle="'),
	            designed: 1
	          }
	        };
	        if (_this4.autoPublicationEnabled) {
	          batch['Landing::publication'] = {
	            action: 'Landing::publication',
	            data: {
	              lid: _this4.landingId
	            }
	          };
	        }
	        batch['History::clearDesignerBlock'] = {
	          action: 'History::clearDesignerBlock',
	          data: {
	            blockId: _this4.blockId
	          }
	        };
	        landing_backend.Backend.getInstance().batch('Block::updateContent', batch).then(function () {
	          _this4.saving = false;
	          finishCallback();
	        });
	        _this4.sendLabel('designerBlock', 'save' + '&designed=' + (_this4.designed ? 'Y' : 'N') + '&code=' + _this4.blockCode);
	      });
	    }
	  }, {
	    key: "initNodes",
	    value: function initNodes() {
	      var _this5 = this;
	      Object.keys(this.nodes).map(function (selector) {
	        _this5.cardSelectors.map(function (cardSelector) {
	          babelHelpers.toConsumableArray(_this5.blockNode.querySelectorAll((cardSelector ? cardSelector + ' ' : '') + selector)).map(function (element) {
	            if (_this5.nodes[selector]['useInDesigner'] === false) {
	              return;
	            }
	            _this5.addNode({
	              element: element,
	              selector: selector,
	              cardSelector: cardSelector,
	              type: _this5.nodes[selector]['type']
	            });
	          });
	        });
	      });
	    }
	  }, {
	    key: "initGrid",
	    value: function initGrid() {
	      var _this6 = this;
	      // collect node's parent and add pseudo last elements into the wrappers
	      Object.keys(this.nodes).map(function (selector) {
	        _this6.cardSelectors.map(function (cardSelector) {
	          babelHelpers.toConsumableArray(_this6.blockNode.querySelectorAll((cardSelector ? cardSelector + ' ' : '') + selector)).map(function (element) {
	            if (_this6.nodes[selector]['useInDesigner'] === false) {
	              return;
	            }
	            var wrapper = _this6.nodes[selector]['type'] === 'icon' ? element.parentNode.parentNode : element.parentNode;
	            if (main_core.Dom.attr(wrapper, 'data-landingWrapper')) {
	              return;
	            }
	            var pseudoElement = DesignerBlockUI.getPseudoLast();
	            main_core.Dom.attr(wrapper, 'data-landingWrapper', true);
	            main_core.Dom.append(pseudoElement, wrapper);
	            _this6.addNode({
	              cardSelector: cardSelector,
	              element: pseudoElement,
	              className: selector.substr(1) + '-last',
	              selector: selector + '-last'
	            });
	          });
	        });
	      });
	    }
	  }, {
	    key: "initSliders",
	    value: function initSliders() {
	      var sliderSelector = '.js-carousel';
	      babelHelpers.toConsumableArray(this.blockNode.querySelectorAll(sliderSelector)).map(function (slider) {
	        var count = (main_core.Text.toNumber(slider.dataset.slidesShow) || 1) * (main_core.Text.toNumber(slider.dataset.rows) || 1);
	        var selector = ".".concat(babelHelpers.toConsumableArray(slider.classList).join('.'), " .js-slide:not(:nth-child(-n+").concat(count, "))");
	        document.head.appendChild(main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["<style>", "{display: none !important;}</style>"])), selector));
	      });
	    }
	  }, {
	    key: "initHoverArea",
	    value: function initHoverArea() {
	      var _this7 = this;
	      if (this.hoverArea) {
	        return;
	      }
	      this.hoverArea = DesignerBlockUI.getHoverDiv();
	      var addNodeElement = DesignerBlockUI.getAddNodeButton();
	      var CardAction = BX.Landing.UI.Button.CardAction;
	      var BaseButtonPanel = BX.Landing.UI.Panel.BaseButtonPanel;
	      var cardAction = new BaseButtonPanel('nodeAction', 'landing-ui-panel-block-card-action');
	      main_core.Event.bind(addNodeElement, 'click', function () {
	        _this7.repoManager.showPanel();
	        _this7.hideHoverArea();
	      });
	      cardAction.addButton(new CardAction('remove', {
	        html: '&nbsp;',
	        onClick: this.removeElement.bind(this)
	      }));
	      void cardAction.show();
	      main_core.Dom.append(addNodeElement, this.hoverArea);
	      main_core.Dom.append(cardAction.layout, this.hoverArea);
	      main_core.Dom.append(this.hoverArea, this.getDocumentBody());
	      main_core.Event.bind(this.blockNode, 'mouseover', function () {
	        _this7.hideHoverArea();
	      });
	    }
	  }, {
	    key: "adjustHoverArea",
	    value: function adjustHoverArea() {
	      if (!this.hoverArea) {
	        return;
	      }
	      this.showHoverArea();
	      var clientRect = this.activeNode.getElement().getBoundingClientRect();
	      var hoverElementAdd = this.hoverArea.querySelector('.landing-designer-block-node-hover-add');
	      var hoverElementActions = this.hoverArea.querySelector('div[data-id="nodeAction"]');
	      var editorWindow = BX.Landing.PageObject.getEditorWindow();
	      if (hoverElementActions) {
	        if (this.activeNode.isPseudoElement()) {
	          main_core.Dom.hide(hoverElementActions);
	        } else {
	          main_core.Dom.show(hoverElementActions);
	        }
	      }
	      if (hoverElementAdd) {
	        main_core.Dom.style(hoverElementAdd, {
	          top: clientRect.height - 5 + 'px'
	        });
	      }
	      main_core.Dom.style(this.hoverArea, {
	        top: clientRect.top + editorWindow.scrollY + 'px',
	        left: clientRect.left + (clientRect.width < 30 ? 30 : 0) + 'px',
	        width: clientRect.width + 'px',
	        height: '35px'
	      });
	    }
	  }, {
	    key: "showHoverArea",
	    value: function showHoverArea() {
	      if (this.hoverArea) {
	        main_core.Dom.show(this.hoverArea);
	      }
	    }
	  }, {
	    key: "hideHoverArea",
	    value: function hideHoverArea() {
	      var _this8 = this;
	      if (this.hoverArea) {
	        setTimeout(function () {
	          main_core.Dom.hide(_this8.hoverArea);
	        }, 0);
	      }
	    }
	  }, {
	    key: "refreshManifest",
	    value: function refreshManifest(manifest) {
	      var _this9 = this;
	      if (manifest) {
	        Object.keys(manifest).map(function (selector) {
	          _this9.nodes[selector] = manifest[selector];
	        });
	      }
	      this.initNodes();
	      this.initGrid();
	    }
	  }, {
	    key: "getDocumentBody",
	    value: function getDocumentBody() {
	      return document.body;
	    }
	  }, {
	    key: "isInsideElement",
	    value: function isInsideElement(element) {
	      return element.parentElement.tagName === 'A';
	    }
	  }, {
	    key: "sendLabel",
	    value: function sendLabel(key, value) {
	      this.metrika.clearSendedLabel();
	      this.metrika.sendLabel(null, key, value);
	    }
	  }, {
	    key: "addElement",
	    value: function addElement(repoElement) {
	      var _this10 = this;
	      var activeNode = this.activeNode;
	      var tags = [];
	      babelHelpers.toConsumableArray(document.body.querySelectorAll(activeNode.getSelector())).map(function (node) {
	        var elementHtml = repoElement.html;
	        var element = main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["", ""])), elementHtml);
	        var insertAfter = _this10.isInsideElement(node) ? node.parentNode : node;
	        main_core.Dom.insertAfter(element, insertAfter);
	        tags.push({
	          elementHtml: elementHtml,
	          elementSelector: BX.Landing.Utils.getCSSSelector(element),
	          insertAfterSelector: BX.Landing.Utils.getCSSSelector(insertAfter)
	        });
	      });
	      this.sendLabel('designerBlock', 'addElement' + '&code=' + this.blockCode + '&name=' + repoElement.code + '&preset=' + (Object.keys(repoElement.manifest.nodes).length === 1 ? 'N' : 'Y'));
	      this.changed = true;
	      this.refreshManifest(repoElement.manifest.nodes);
	      this.highlight.show(null);
	      landing_backend.Backend.getInstance().action("History::pushDesignerBlock", {
	        blockId: this.blockId,
	        action: 'ADD_NODE',
	        data: {
	          tags: tags
	        }
	      }).then(function (result) {
	        BX.Landing.History.getInstance().push();
	      });
	    }
	  }, {
	    key: "removeElement",
	    value: function removeElement() {
	      var _this11 = this;
	      var tags = [];
	      this.hideHoverArea();
	      this.highlight.hide();
	      setTimeout(function () {
	        _this11.sendLabel('designerBlock', 'removeElement' + '&tagName=' + _this11.activeNode.getElement().tagName + '&code=' + _this11.blockCode);
	        babelHelpers.toConsumableArray(document.body.querySelectorAll(_this11.activeNode.getSelector())).map(function (node) {
	          tags.push({
	            elementHtml: _this11.clearHtml(node.outerHTML),
	            elementSelector: BX.Landing.Utils.getCSSSelector(node),
	            insertAfterSelector: node.previousElementSibling ? BX.Landing.Utils.getCSSSelector(node.previousElementSibling) : null,
	            parentNodeSelector: BX.Landing.Utils.getCSSSelector(node.parentNode)
	          });
	          _this11.removeNode(node);
	        });
	        _this11.changed = true;
	        _this11.refreshManifest();
	        landing_backend.Backend.getInstance().action("History::pushDesignerBlock", {
	          blockId: _this11.blockId,
	          action: 'REMOVE_NODE',
	          data: {
	            selector: _this11.activeNode.getOriginalSelector(),
	            tags: tags
	          }
	        }).then(function (result) {
	          BX.Landing.History.getInstance().push();
	        });
	      }, 0);
	    }
	  }, {
	    key: "typeWithWrapper",
	    value: function typeWithWrapper(type) {
	      return type === 'icon' || type === 'embed';
	    }
	  }, {
	    key: "addNode",
	    value: function addNode(nodeOptions) {
	      if (!this.nodeMap.get(nodeOptions.element)) {
	        if (nodeOptions.selector.match(/^\.[\w-_]+$/i) === null) {
	          return false;
	        }

	        // for some type we get parent node
	        var withWrapper = this.typeWithWrapper(nodeOptions.type);
	        nodeOptions.element = withWrapper ? nodeOptions.element.parentNode : nodeOptions.element;
	        if (withWrapper) {
	          nodeOptions.selector = nodeOptions.selector + '--type-wrapper';
	          main_core.Dom.addClass(nodeOptions.element, nodeOptions.selector.substr(1));
	        }
	        // mouse over callback
	        nodeOptions.onHover = this.onMouseOver.bind(this);
	        this.nodeMap.set(nodeOptions.element, new Node(nodeOptions));
	        return true;
	      }
	      return false;
	    }
	  }, {
	    key: "removeNode",
	    value: function removeNode(node) {
	      if (node) {
	        main_core.Dom.remove(node);
	        this.nodeMap["delete"](node);
	      }
	    }
	  }, {
	    key: "onMouseOver",
	    value: function onMouseOver(node) {
	      if (this.saving) {
	        return;
	      }
	      this.activeNode = node;
	      this.adjustHoverArea();
	      if (!node.isPseudoElement()) {
	        this.highlight.show(node.getElement());
	      }
	    }
	  }]);
	  return DesignerBlock;
	}();

	exports.DesignerBlock = DesignerBlock;

}((this.BX.Landing = this.BX.Landing || {}),BX.Landing,BX.Landing,BX.Landing,BX.Landing.UI,BX.Landing,BX.Landing.UI.Panel,BX));
//# sourceMappingURL=designerblock.bundle.js.map
