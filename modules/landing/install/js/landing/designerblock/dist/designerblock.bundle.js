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

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-designer-block-node-hover-add\">\n\t\t\t\t<span class=\"landing-designer-block-node-hover-add-title\">\n\t\t\t\t\t", "\n\t\t\t\t</span>\n\t\t\t</div>"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-designer-block-pseudo-last\"></div>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-designer-block-node-hover\"></div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var DesignerBlockUI = /*#__PURE__*/function () {
	  function DesignerBlockUI() {
	    babelHelpers.classCallCheck(this, DesignerBlockUI);
	  }

	  babelHelpers.createClass(DesignerBlockUI, null, [{
	    key: "getHoverDiv",
	    value: function getHoverDiv() {
	      return main_core.Tag.render(_templateObject());
	    }
	  }, {
	    key: "getPseudoLast",
	    value: function getPseudoLast() {
	      return main_core.Tag.render(_templateObject2());
	    }
	  }, {
	    key: "getAddNodeButton",
	    value: function getAddNodeButton() {
	      return main_core.Tag.render(_templateObject3(), landing_loc.Loc.getMessage('LANDING_DESIGN_BLOCK_REPO_BUTTON'));
	    }
	  }]);
	  return DesignerBlockUI;
	}();

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-layer-list-container\"></div>"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
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

	    _this.renderTo(document.body);

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
	      Object.keys(element.manifest.nodes).map(function (selector) {
	        var randPostfix = '-' + _this3.randomNum(1000, 9999);

	        var className = selector.substr(1);
	        element.html = element.html.replaceAll(new RegExp(className + '([\\s"]{1})', 'g'), className + randPostfix + '$1');
	        newManifest[selector + randPostfix] = element.manifest.nodes[selector];
	      });
	      element.manifest.nodes = newManifest;
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
	        return main_core.Tag.render(_templateObject$1());
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

	function _templateObject3$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["", ""]);

	  _templateObject3$1 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<style>", "{display: none !important;}</style>"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["", ""]);

	  _templateObject$2 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var DesignerBlock = /*#__PURE__*/function () {
	  function DesignerBlock(blockNode, options) {
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
	    this.preventEvents();
	    this.initHistoryEvents();
	    this.initTopPanel();
	    this.initNodes();
	    this.initGrid();
	    this.initSliders();
	    this.initHoverArea();
	  }

	  babelHelpers.createClass(DesignerBlock, [{
	    key: "clearHtml",
	    value: function clearHtml(content) {
	      return content.replace(/<div class="[^"]*landing-designer-block-pseudo-last[^"]*"[^>]*>[\s]*<\/div>/g, '').replace(/<div class="[^"]*landing-highlight-border[^"]*"[^>]*>[\s]*<\/div>/g, '').replace(/url\(&quot;(.*?)&quot;\)/g, 'url($1)').replace(/\s*data-(landingwrapper)="[^"]+"\s*/g, ' ').replace(/\s*[\w-_]+--type-wrapper\s*/g, ' ').replace(/<div[\s]*>[\s]*<\/div>/g, '').replace(/\s*style=""/g, '');
	    }
	  }, {
	    key: "preventEvents",
	    value: function preventEvents() {
	      var _this = this;

	      var preventMap = {
	        a: 'click',
	        form: 'submit',
	        input: 'keydown'
	      };
	      Object.keys(preventMap).map(function (tag) {
	        babelHelpers.toConsumableArray(_this.blockNode.querySelectorAll(tag)).map(function (node) {
	          main_core.Event.bind(node, preventMap[tag], function (e) {
	            e.preventDefault();
	          });
	        });
	      });
	    }
	  }, {
	    key: "initHistoryEvents",
	    value: function initHistoryEvents() {
	      var _this2 = this;

	      var body = this.getDocumentBody();
	      top.BX.addCustomEvent('Landing:onHistoryAddNode', function (tags) {
	        var elementAdded = false;
	        tags.map(function (tag) {
	          var insertAfterSelector = tag.insertAfterSelector || null;
	          var parentNodeSelector = tag.parentNodeSelector || null;
	          var element = main_core.Tag.render(_templateObject$2(), tag.elementHtml);

	          if (insertAfterSelector) {
	            elementAdded = true;
	            main_core.Dom.insertAfter(element, body.querySelector(insertAfterSelector));
	          } else if (parentNodeSelector) {
	            elementAdded = true;
	            main_core.Dom.prepend(element, body.querySelector(parentNodeSelector));
	          }
	        });

	        if (elementAdded) {
	          _this2.refreshManifest();

	          setTimeout(function () {
	            _this2.sendLabel('designerBlock', 'onHistoryAddNode');
	          }, 0);
	        }
	      });
	      top.BX.addCustomEvent('Landing:onHistoryRemoveNode', function (tags) {
	        tags.map(function (tag) {
	          _this2.removeNode(body.querySelector(tag.elementSelector));
	        });

	        _this2.refreshManifest();

	        setTimeout(function () {
	          _this2.sendLabel('designerBlock', 'onHistoryRemoveNode');
	        }, 0);
	      });
	    }
	  }, {
	    key: "initTopPanel",
	    value: function initTopPanel() {
	      var _this3 = this;

	      main_core.Event.bind(this.saveButton, 'click', function () {
	        _this3.highlight.hide();

	        var finishCallback = function finishCallback() {
	          if (BX.SidePanel && BX.SidePanel.Instance) {
	            BX.SidePanel.Instance.close();
	          }
	        };

	        if (!_this3.changed) {
	          finishCallback();
	          return;
	        }

	        if (!_this3.designAllowed) {
	          top.BX.UI.InfoHelper.show('limit_crm_free_superblock1');
	          return;
	        }

	        _this3.saving = true;
	        var batch = {};
	        batch['Block::updateContent'] = {
	          action: 'Block::updateContent',
	          data: {
	            lid: _this3.landingId,
	            block: _this3.blockId,
	            content: _this3.clearHtml(_this3.originalNode.innerHTML).replaceAll(' style="', ' bxstyle="'),
	            designed: 1
	          }
	        };

	        if (_this3.autoPublicationEnabled) {
	          batch['Landing::publication'] = {
	            action: 'Landing::publication',
	            data: {
	              lid: _this3.landingId
	            }
	          };
	        }

	        landing_backend.Backend.getInstance().batch('Block::updateContent', batch).then(function () {
	          _this3.saving = false;
	          finishCallback();
	        });

	        _this3.sendLabel('designerBlock', 'save' + '&designed=' + (_this3.designed ? 'Y' : 'N') + '&code=' + _this3.blockCode);
	      });
	    }
	  }, {
	    key: "initNodes",
	    value: function initNodes() {
	      var _this4 = this;

	      Object.keys(this.nodes).map(function (selector) {
	        _this4.cardSelectors.map(function (cardSelector) {
	          babelHelpers.toConsumableArray(_this4.blockNode.querySelectorAll((cardSelector ? cardSelector + ' ' : '') + selector)).map(function (element) {
	            if (_this4.nodes[selector]['useInDesigner'] === false) {
	              return;
	            }

	            _this4.addNode({
	              element: element,
	              selector: selector,
	              cardSelector: cardSelector,
	              type: _this4.nodes[selector]['type']
	            });
	          });
	        });
	      });
	    }
	  }, {
	    key: "initGrid",
	    value: function initGrid() {
	      var _this5 = this;

	      // collect node's parent and add pseudo last elements into the wrappers
	      Object.keys(this.nodes).map(function (selector) {
	        _this5.cardSelectors.map(function (cardSelector) {
	          babelHelpers.toConsumableArray(_this5.blockNode.querySelectorAll((cardSelector ? cardSelector + ' ' : '') + selector)).map(function (element) {
	            if (_this5.nodes[selector]['useInDesigner'] === false) {
	              return;
	            }

	            var wrapper = _this5.nodes[selector]['type'] === 'icon' ? element.parentNode.parentNode : element.parentNode;

	            if (main_core.Dom.attr(wrapper, 'data-landingWrapper')) {
	              return;
	            }

	            var pseudoElement = DesignerBlockUI.getPseudoLast();
	            main_core.Dom.attr(wrapper, 'data-landingWrapper', true);
	            main_core.Dom.append(pseudoElement, wrapper);

	            _this5.addNode({
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
	        document.head.appendChild(main_core.Tag.render(_templateObject2$1(), selector));
	      });
	    }
	  }, {
	    key: "initHoverArea",
	    value: function initHoverArea() {
	      var _this6 = this;

	      if (this.hoverArea) {
	        return;
	      }

	      this.hoverArea = DesignerBlockUI.getHoverDiv();
	      var addNodeElement = DesignerBlockUI.getAddNodeButton();
	      var CardAction = BX.Landing.UI.Button.CardAction;
	      var BaseButtonPanel = BX.Landing.UI.Panel.BaseButtonPanel;
	      var cardAction = new BaseButtonPanel('nodeAction', 'landing-ui-panel-block-card-action');
	      main_core.Event.bind(addNodeElement, 'click', function () {
	        _this6.repoManager.showPanel();

	        _this6.hideHoverArea();
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
	        _this6.hideHoverArea();
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
	      var _this7 = this;

	      if (this.hoverArea) {
	        setTimeout(function () {
	          main_core.Dom.hide(_this7.hoverArea);
	        }, 0);
	      }
	    }
	  }, {
	    key: "refreshManifest",
	    value: function refreshManifest(manifest) {
	      var _this8 = this;

	      if (manifest) {
	        Object.keys(manifest).map(function (selector) {
	          _this8.nodes[selector] = manifest[selector];
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
	      var _this9 = this;

	      var activeNode = this.activeNode;
	      var tags = [];
	      babelHelpers.toConsumableArray(document.body.querySelectorAll(activeNode.getSelector())).map(function (node) {
	        var elementHtml = repoElement.html;
	        var element = main_core.Tag.render(_templateObject3$1(), elementHtml);
	        var insertAfter = _this9.isInsideElement(node) ? node.parentNode : node;
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
	      BX.Landing.History.getInstance().push(new BX.Landing.History.Entry({
	        command: 'addNode',
	        block: null,
	        undo: null,
	        redo: {
	          tags: tags
	        }
	      }));
	    }
	  }, {
	    key: "removeElement",
	    value: function removeElement() {
	      var _this10 = this;

	      var tags = [];
	      this.hideHoverArea();
	      this.highlight.hide();
	      setTimeout(function () {
	        _this10.sendLabel('designerBlock', 'removeElement' + '&tagName=' + _this10.activeNode.getElement().tagName + '&code=' + _this10.blockCode);

	        babelHelpers.toConsumableArray(document.body.querySelectorAll(_this10.activeNode.getSelector())).map(function (node) {
	          tags.push({
	            elementHtml: _this10.clearHtml(node.outerHTML),
	            elementSelector: BX.Landing.Utils.getCSSSelector(node),
	            insertAfterSelector: node.previousElementSibling ? BX.Landing.Utils.getCSSSelector(node.previousElementSibling) : null,
	            parentNodeSelector: BX.Landing.Utils.getCSSSelector(node.parentNode)
	          });

	          _this10.removeNode(node);
	        });
	        _this10.changed = true;

	        _this10.refreshManifest();

	        BX.Landing.History.getInstance().push(new BX.Landing.History.Entry({
	          selector: _this10.activeNode.getOriginalSelector(),
	          command: 'removeNode',
	          block: null,
	          undo: {
	            tags: tags
	          },
	          redo: null
	        }));
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
	        } // for some type we get parent node


	        var withWrapper = this.typeWithWrapper(nodeOptions.type);
	        nodeOptions.element = withWrapper ? nodeOptions.element.parentNode : nodeOptions.element;

	        if (withWrapper) {
	          nodeOptions.selector = nodeOptions.selector + '--type-wrapper';
	          main_core.Dom.addClass(nodeOptions.element, nodeOptions.selector.substr(1));
	        } // mouse over callback


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
	        this.nodeMap.delete(node);
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
