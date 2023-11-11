this.BX = this.BX || {};
(function (exports,landing_main,main_core,landing_pageobject,landing_ui_highlight) {
	'use strict';

	var RESOLVED = 'resolved';
	var PENDING = 'pending';

	var _BX$Landing$Utils = BX.Landing.Utils,
	  scrollTo = _BX$Landing$Utils.scrollTo,
	  highlight = _BX$Landing$Utils.highlight;

	/**
	 * @param {object} entry
	 * @return {Promise}
	 */
	var editNode = function editNode(entry) {
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.block);
	    if (!block) {
	      return Promise.reject();
	    }
	    block.forceInit();
	    var node = block.nodes.getBySelector(entry.selector);
	    if (!node) {
	      return Promise.reject();
	    }
	    return scrollTo(node.node).then(highlight.bind(null, node.node, editNode.useRangeRect)).then(function () {
	      return node.setValue(entry.params.value, false, true);
	    });
	  });
	};
	editNode.useRangeRect = true;

	var editText = editNode;

	var editEmbed = editNode;

	var editMap = editNode;

	var editImage = editNode;
	editImage.useRangeRect = false;

	var editIcon = editImage;

	var editLink = editNode;
	editLink.useRangeRect = false;

	var _BX$Landing$Utils$1 = BX.Landing.Utils,
	  scrollTo$1 = _BX$Landing$Utils$1.scrollTo,
	  highlight$1 = _BX$Landing$Utils$1.highlight;

	/**
	 * @param {object} entry
	 * @return {Promise}
	 */
	function changeNodeName(entry) {
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.block);
	    if (!block) {
	      return Promise.reject();
	    }
	    block.forceInit();
	    var node = block.nodes.getBySelector(entry.selector);
	    if (!node) {
	      return Promise.reject();
	    }
	    return scrollTo$1(node.node).then(function () {
	      return highlight$1(node.node);
	    }).then(function () {
	      if (node.onChangeTag) {
	        node.onChangeTag(entry.params.value, true);
	      }
	      return true;
	    });
	  });
	}

	var _BX$Landing$Utils$2 = BX.Landing.Utils,
	  scrollTo$2 = _BX$Landing$Utils$2.scrollTo,
	  highlight$2 = _BX$Landing$Utils$2.highlight;

	/**
	 * @param {object} entry
	 * @return {Promise}
	 */
	function sortBlock(entry) {
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.block);
	    block.forceInit();
	    return scrollTo$2(block.node).then(highlight$2.bind(null, block.node)).then(function () {
	      return block[entry.params.direction](true);
	    });
	  });
	}

	var _BX$Landing$Utils$3 = BX.Landing.Utils,
	  scrollTo$3 = _BX$Landing$Utils$3.scrollTo,
	  highlight$3 = _BX$Landing$Utils$3.highlight;

	/**
	 * @param {object} entry
	 * @return {Promise}
	 */
	function addBlock(entry) {
	  return landing_pageobject.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.params.currentBlock);
	    return new Promise(function (resolve) {
	      if (block) {
	        block.forceInit();
	      }
	      resolve();
	    }).then(function () {
	      var landing = BX.Landing.Main.getInstance();
	      landing.currentBlock = block;
	      return landing_pageobject.PageObject.getInstance().view().then(function (iframe) {
	        landing.currentArea = iframe.contentDocument.body.querySelector("[data-landing=\"".concat(entry.params.lid, "\"]"));
	        landing.insertBefore = entry.params.insertBefore;
	        return landing.onAddBlock(entry.params.code, entry.block, true).then(function (newBlock) {
	          return scrollTo$3(newBlock).then(highlight$3.bind(null, newBlock, false, false));
	        });
	      });
	    });
	  });
	}

	var _BX$Landing$Utils$4 = BX.Landing.Utils,
	  scrollTo$4 = _BX$Landing$Utils$4.scrollTo,
	  highlight$4 = _BX$Landing$Utils$4.highlight;

	/**
	 * @param {object} entry
	 * @return {Promise}
	 */
	function removeBlock(entry) {
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.block);
	    block.forceInit();
	    return scrollTo$4(block.node).then(function () {
	      highlight$4(block.node);
	      return block.deleteBlock(true);
	    });
	  });
	}

	var _BX$Landing$Utils$5 = BX.Landing.Utils,
	  scrollTo$5 = _BX$Landing$Utils$5.scrollTo,
	  highlight$5 = _BX$Landing$Utils$5.highlight;

	/**
	 * @param {string} state
	 * @param {object} entry
	 * @return {Promise}
	 */
	function addCard(entry) {
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.block);
	    if (block) {
	      block.forceInit();
	    }
	    if (!block) {
	      return Promise.reject();
	    }
	    var parentNode = block.node.querySelector(entry.params.selector).parentNode;
	    return scrollTo$5(parentNode).then(function () {
	      return block.addCard({
	        index: entry.params.position,
	        container: parentNode,
	        content: entry.params.content,
	        selector: entry.params.selector
	      }, true).then(function () {
	        var cardSelector = entry.params.selector + '@' + entry.params.position;
	        var card = block.cards.getBySelector(cardSelector);
	        if (!card) {
	          return Promise.reject();
	        }
	        return highlight$5(card.node);
	      });
	    });
	  })["catch"](function (err) {
	    console.log("Error in history action addCard", err);
	  });
	}

	var _BX$Landing$Utils$6 = BX.Landing.Utils,
	  scrollTo$6 = _BX$Landing$Utils$6.scrollTo,
	  highlight$6 = _BX$Landing$Utils$6.highlight;

	/**
	 * @param {string} state
	 * @param {object} entry
	 * @return {Promise}
	 */
	function removeCard(entry) {
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.block);
	    block.forceInit();
	    if (!block) {
	      return Promise.reject();
	    }
	    var relativeSelector = entry.params.selector + '@' + (entry.params.position + 1);
	    var card = block.cards.getBySelector(relativeSelector);
	    if (!card) {
	      return Promise.reject();
	    }
	    return scrollTo$6(card.node).then(highlight$6.bind(null, card.node)).then(function () {
	      return block.removeCard(relativeSelector, true);
	    });
	  });
	}

	/**
	 * History entry action for add node.
	 * @param {object} entry History entry.
	 * @return {Promise}
	 */
	function addNode(entry) {
	  var _this = this;
	  // entry.block === null >> designer mode

	  return new Promise(function (resolve, reject) {
	    var tags = entry.params.tags || {};
	    top.BX.onCustomEvent(_this, 'Landing:onHistoryAddNode', [tags]);
	    resolve();
	  });
	}

	/**
	 * History entry action for remove node.
	 * @param {object} entry History entry.
	 * @return {Promise}
	 */
	function removeNode(entry) {
	  var _this = this;
	  // entry.block === null >> designer mode

	  return new Promise(function (resolve, reject) {
	    var tags = entry.params.tags || {};
	    top.BX.onCustomEvent(_this, 'Landing:onHistoryRemoveNode', [tags]);
	    resolve();
	  });
	}

	var _BX$Landing$Utils$7 = BX.Landing.Utils,
	  scrollTo$7 = _BX$Landing$Utils$7.scrollTo,
	  slice = _BX$Landing$Utils$7.slice;

	/**
	 * @param {object} entry
	 * @return {Promise}
	 */
	function editStyle(entry) {
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.block);
	    if (!block) {
	      return Promise.reject();
	    }
	    block.forceInit();
	    block.initStyles();
	    return block;
	  }).then(function (block) {
	    return scrollTo$7(block.node).then(function () {
	      return block;
	    });
	  }).then(function (block) {
	    var elements = slice(block.node.querySelectorAll(entry.selector));
	    if (entry.params.isWrapper) {
	      elements = [block.content];
	      entry.selector += ' > :first-child';
	    }
	    elements.forEach(function (element, pos) {
	      if (entry.params.position >= 0 && entry.params.position !== pos) {
	        return;
	      }
	      element.className = entry.params.value.className;
	      if (entry.params.value.style && entry.params.value.style !== '') {
	        element.style = entry.params.value.style;
	      } else {
	        element.removeAttribute('style');
	      }
	    });
	    return block;
	  }).then(function (block) {
	    var form = block.forms.find(function (currentForm) {
	      return currentForm.selector === entry.selector || currentForm.relativeSelector === entry.selector;
	    });
	    if (form) {
	      form.fields.forEach(function (field) {
	        field.reset();
	        field.onFrameLoad();
	      });
	    }

	    // todo: relative selector? position?
	    var styleNode = block.styles.find(function (style) {
	      return style.selector === entry.selector || style.relativeSelector === entry.selector;
	    });
	    if (styleNode) {
	      if (entry.params.affect && entry.params.affect.length > 0) {
	        styleNode.setAffects(entry.params.affect);
	      }
	      block.onStyleInputWithDebounce({
	        node: styleNode.node,
	        data: styleNode.getValue()
	      }, true);
	    }
	  });
	}

	var _BX$Landing$Utils$8 = BX.Landing.Utils,
	  scrollTo$8 = _BX$Landing$Utils$8.scrollTo,
	  highlight$7 = _BX$Landing$Utils$8.highlight;

	/**
	 * @param {object} entry
	 * @return {Promise}
	 */
	function editAttributes(entry) {
	  return landing_pageobject.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.block);
	    return new Promise(function (resolve, reject) {
	      if (block) {
	        block.forceInit();
	        resolve(block);
	      } else {
	        reject();
	      }
	    }).then(function (block) {
	      return scrollTo$8(block.node).then(function () {
	        return block.applyAttributeChanges(babelHelpers.defineProperty({}, entry.params.selector, {
	          attrs: babelHelpers.defineProperty({}, entry.params.attribute, entry.params.value)
	        }));
	      }).then(highlight$7.bind(null, block.node, false, false));
	    });
	  });
	}

	var _BX$Landing$Utils$9 = BX.Landing.Utils,
	  scrollTo$9 = _BX$Landing$Utils$9.scrollTo,
	  highlight$8 = _BX$Landing$Utils$9.highlight;

	/**
	 * @param {object} entry
	 * @return {Promise}
	 */
	function updateContent(entry) {
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.block);
	    block.forceInit();
	    return scrollTo$9(block.node).then(function () {
	      void highlight$8(block.node);
	      return block.updateContent(entry.params.content, true);
	    });
	  });
	}

	var _BX$Landing$Utils$a = BX.Landing.Utils,
	  scrollTo$a = _BX$Landing$Utils$a.scrollTo,
	  highlight$9 = _BX$Landing$Utils$a.highlight;

	/**
	 * @param {object} entry
	 * @return {Promise}
	 */
	function multiply(entry) {
	  var blockId = null;
	  var updateBlockStateData = {};
	  entry.params.forEach(function (singleAction) {
	    if (!blockId && singleAction.params.block) {
	      blockId = singleAction.params.block;
	    }
	    if (singleAction.command === 'editText' || singleAction.command === 'editImage' || singleAction.command === 'editEmbed' || singleAction.command === 'editMap' || singleAction.command === 'editIcon' || singleAction.command === 'editLink') {
	      updateBlockStateData[singleAction.params.selector] = singleAction.params.value;
	    }
	    if (singleAction.command === 'updateDynamic') {
	      updateBlockStateData.dynamicParams = singleAction.params.dynamicParams;
	      updateBlockStateData.dynamicState = singleAction.params.dynamicState;
	    }
	    if (singleAction.command === 'changeAnchor') {
	      updateBlockStateData.settings = {
	        id: singleAction.params.value
	      };
	    }
	  });
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(blockId);
	    if (block) {
	      block.forceInit();
	      return scrollTo$a(block.node).then(function () {
	        void highlight$9(block.node);
	        if (Object.keys(updateBlockStateData).length > 0) {
	          block.updateBlockState(updateBlockStateData, true);
	        }
	      });
	    }
	  });
	}

	var _BX$Landing$Utils$b = BX.Landing.Utils,
	  scrollTo$b = _BX$Landing$Utils$b.scrollTo,
	  highlight$a = _BX$Landing$Utils$b.highlight;

	/**
	 * @param {object} entry
	 * @return {Promise}
	 */
	function replaceLanding(entry) {
	  return new Promise(function (resolve, reject) {
	    top.window.location.reload();
	    resolve();
	  });
	}

	var _BX$Landing$Utils$c = BX.Landing.Utils,
	  scrollTo$c = _BX$Landing$Utils$c.scrollTo,
	  highlight$b = _BX$Landing$Utils$c.highlight;

	/**
	 * @param {object} entry
	 * @return {Promise}
	 */
	function changeAnchor(entry) {
	  return landing_pageobject.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.params.currentBlock);
	    return new Promise(function (resolve, reject) {
	      if (block) {
	        block.forceInit();
	        resolve(block);
	      } else {
	        reject();
	      }
	    }).then(function (block) {
	      scrollTo$c(block).then(highlight$b.bind(null, block, false, false));
	    });
	  });
	}

	/**
	 * Implements interface for works with command of history
	 * @param {{id: string, undo: function, redo: function}} options
	 */
	var Command = function Command(options) {
	  babelHelpers.classCallCheck(this, Command);
	  this.id = main_core.Type.isStringFilled(options.id) ? options.id : '#invalidCommand';
	  this.command = main_core.Type.isFunction(options.command) ? options.command : function () {};
	  this.onBeforeCommand = main_core.Type.isFunction(options.onBeforeCommand) ? options.onBeforeCommand : function () {
	    return Promise.resolve();
	  };
	};

	var _templateObject;
	/**
	 * Registers base internal commands
	 * @param {History} history
	 * @return {Promise<History>}
	 */
	function registerBaseCommands(history) {
	  history.registerCommand(new Command({
	    id: 'editText',
	    command: editText
	  }));
	  history.registerCommand(new Command({
	    id: 'editImage',
	    command: editImage
	  }));
	  history.registerCommand(new Command({
	    id: 'editEmbed',
	    command: editEmbed
	  }));
	  history.registerCommand(new Command({
	    id: 'editMap',
	    command: editMap
	  }));
	  history.registerCommand(new Command({
	    id: 'editIcon',
	    command: editIcon
	  }));
	  history.registerCommand(new Command({
	    id: 'editLink',
	    command: editLink
	  }));
	  history.registerCommand(new Command({
	    id: 'cnangeNodeName',
	    command: changeNodeName
	  }));
	  history.registerCommand(new Command({
	    id: 'sortBlock',
	    command: sortBlock
	  }));
	  history.registerCommand(new Command({
	    id: 'addBlock',
	    command: addBlock
	  }));
	  history.registerCommand(new Command({
	    id: 'removeBlock',
	    command: removeBlock
	  }));
	  history.registerCommand(new Command({
	    id: 'updateStyle',
	    command: editStyle
	  }));
	  history.registerCommand(new Command({
	    id: 'addCard',
	    command: addCard
	  }));
	  history.registerCommand(new Command({
	    id: 'removeCard',
	    command: removeCard
	  }));
	  history.registerCommand(new Command({
	    id: 'addNode',
	    command: addNode
	  }));
	  history.registerCommand(new Command({
	    id: 'removeNode',
	    command: removeNode
	  }));
	  history.registerCommand(new Command({
	    id: 'updateContent',
	    command: updateContent
	  }));
	  history.registerCommand(new Command({
	    id: 'replaceLanding',
	    command: replaceLanding,
	    onBeforeCommand: function onBeforeCommand() {
	      return main_core.Runtime.loadExtension('main.loader').then(function () {
	        var editor = BX.Landing.PageObject.getEditorWindow();
	        if (editor) {
	          var container = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-modal\"></div>"])));
	          main_core.Dom.append(container, editor.document.body);
	          var loader = new BX.Loader({
	            target: container
	          });
	          loader.show();
	        }
	        return Promise.resolve();
	      });
	    }
	  }));
	  history.registerCommand(new Command({
	    id: 'changeAnchor',
	    command: changeAnchor
	  }));
	  history.registerCommand(new Command({
	    id: 'editAttributes',
	    command: editAttributes
	  }));
	  history.registerCommand(new Command({
	    id: 'multiply',
	    command: multiply
	  }));
	  return Promise.resolve(history);
	}

	var worker = new Worker('/bitrix/js/landing/history/src/worker/json-parse-worker.js');

	/**
	 * Parses json string
	 * @param {string} str
	 * @return {Promise<?Object|array>}
	 */
	function asyncJsonParse(str) {
	  return new Promise(function (resolve) {
	    worker.postMessage(str);
	    worker.addEventListener('message', function (event) {
	      resolve(event.data);
	    });
	  });
	}

	var worker$1 = new Worker('/bitrix/js/landing/history/src/worker/json-stringify-worker.js');

	/**
	 * Serializes object
	 * @param {Object|array} obj
	 * @return {Promise<?String>}
	 */
	function asyncJsonStringify(obj) {
	  return new Promise(function (resolve) {
	    worker$1.postMessage(obj);
	    worker$1.addEventListener('message', function (event) {
	      resolve(event.data);
	    });
	  });
	}

	/**
	 * Removes page history from storage
	 * @param {int} pageId
	 * @param {History} history
	 * @return {Promise<History>}
	 */
	function removePageHistory(pageId, history) {
	  return asyncJsonParse(window.localStorage.history).then(function (historyData) {
	    return main_core.Type.isPlainObject(historyData) ? historyData : {};
	  }).then(function (all) {
	    if (pageId in all) {
	      delete all[pageId];
	    }
	    return all;
	  }).then(asyncJsonStringify).then(function (allString) {
	    window.localStorage.history = allString;
	    return history;
	  });
	}

	/**
	 * Loads history from storage
	 * @param {History} history
	 * @return {Promise<History>}
	 */
	function loadStack(history) {
	  return BX.Landing.Backend.getInstance().action(history.getLoadBackendActionName(), history.getLoadBackendParams()).then(function (data) {
	    history.stack = main_core.Type.isObject(data.stack) ? data.stack : {};
	    history.stackCount = main_core.Text.toNumber(data.stackCount);
	    history.step = Math.min(main_core.Text.toNumber(data.step), history.stackCount);
	    return history;
	  })["catch"](function (e) {
	    return history;
	  });
	}

	/**
	 * Clears history stack
	 * @param {History} history
	 * @return {Promise<History>}
	 */
	function clear(history) {
	  history.stack = {};
	  history.stackCount = 0;
	  history.step = 0;
	  history.commandState = RESOLVED;
	  return Promise.resolve(history);
	}

	/**
	 * Calls on update history stack
	 * @param {History} history
	 * @return {Promise<History>}
	 */
	function onUpdate(history) {
	  var rootWindow = BX.Landing.PageObject.getRootWindow();
	  BX.onCustomEvent(rootWindow.window, 'BX.Landing.History:update', [history]);
	  return Promise.resolve(history);
	}

	/**
	 * Calls on init history object
	 * @param history
	 * @return {Promise<History>}
	 */
	function onInit(history) {
	  var rootWindow = BX.Landing.PageObject.getRootWindow();
	  BX.onCustomEvent(rootWindow.window, 'BX.Landing.History:init', [history]);
	  return Promise.resolve(history);
	}

	var Entry = function Entry(options) {
	  babelHelpers.classCallCheck(this, Entry);
	  this.block = options.block;
	  this.selector = options.selector;
	  this.command = main_core.Type.isStringFilled(options.command) ? options.command : '#invalidCommand';
	  this.params = options.params;
	};

	var Highlight = /*#__PURE__*/function (_HighlightNode) {
	  babelHelpers.inherits(Highlight, _HighlightNode);
	  function Highlight() {
	    var _this;
	    babelHelpers.classCallCheck(this, Highlight);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Highlight).call(this));
	    _this.layout.classList.add('landing-ui-highlight-animation');
	    _this.animationDuration = 300;
	    return _this;
	  }
	  babelHelpers.createClass(Highlight, [{
	    key: "show",
	    value: function show(element, rect) {
	      var _this2 = this;
	      BX.Landing.UI.Highlight.prototype.show.call(this, element, rect);
	      return new Promise(function (resolve) {
	        setTimeout(resolve, _this2.animationDuration);
	        _this2.hide();
	      });
	    }
	  }], [{
	    key: "getInstance",
	    value: function getInstance() {
	      var rootWindow = landing_pageobject.PageObject.getRootWindow();
	      if (!rootWindow.BX.Landing.History.Highlight.instance) {
	        rootWindow.BX.Landing.History.Highlight.instance = new Highlight();
	      }
	      return rootWindow.BX.Landing.History.Highlight.instance;
	    }
	  }]);
	  return Highlight;
	}(landing_ui_highlight.Highlight);

	/**
	 * Implements interface for works with landing history
	 * Implements singleton pattern use as BX.Landing.History.getInstance()
	 * @memberOf BX.Landing
	 */
	var History = /*#__PURE__*/function () {
	  function History() {
	    babelHelpers.classCallCheck(this, History);
	    babelHelpers.defineProperty(this, "designerBlockId", null);
	    this.type = History.TYPE_LANDING;
	    this.stack = {};
	    this.stackCount = 0;
	    this.step = 0;
	    this.commands = {};
	    this.commandState = RESOLVED;
	    this.onStorage = this.onStorage.bind(this);
	    try {
	      this.landingId = landing_main.Main.getInstance().id;
	    } catch (err) {
	      this.landingId = -1;
	    }
	    main_core.Event.bind(window, 'storage', this.onStorage);
	    registerBaseCommands(this).then(loadStack).then(onInit);
	  }
	  babelHelpers.createClass(History, [{
	    key: "setTypeDesignerBlock",
	    /**
	     * Set special type for designer block
	     * @param blockId
	     * @return {Promise<BX.Landing.History>|*}
	     */
	    value: function setTypeDesignerBlock(blockId) {
	      this.type = History.TYPE_DESIGNER_BLOCK;
	      this.designerBlockId = blockId;
	      return loadStack(this);
	    }
	  }, {
	    key: "getLoadBackendActionName",
	    value: function getLoadBackendActionName() {
	      if (this.type === History.TYPE_DESIGNER_BLOCK) {
	        return "History::getForDesignerBlock";
	      }
	      return "History::getForLanding";
	    }
	  }, {
	    key: "getLoadBackendParams",
	    value: function getLoadBackendParams() {
	      if (this.type === History.TYPE_DESIGNER_BLOCK) {
	        return {
	          blockId: this.designerBlockId
	        };
	      }
	      return {
	        lid: this.landingId
	      };
	    }
	  }, {
	    key: "getUndoBackendActionName",
	    value: function getUndoBackendActionName() {
	      if (this.type === History.TYPE_DESIGNER_BLOCK) {
	        return "History::undoDesignerBlock";
	      }
	      return "History::undoLanding";
	    }
	  }, {
	    key: "beforeUndo",
	    value: function beforeUndo() {
	      var step = this.step;
	      if (this.stack[step] && this.commands[this.stack[step]]) {
	        var command = this.commands[this.stack[step]];
	        return command.onBeforeCommand();
	      }
	      return Promise.resolve();
	    }
	  }, {
	    key: "getRedoBackendActionName",
	    value: function getRedoBackendActionName() {
	      if (this.type === History.TYPE_DESIGNER_BLOCK) {
	        return "History::redoDesignerBlock";
	      }
	      return "History::redoLanding";
	    }
	  }, {
	    key: "beforeRedo",
	    value: function beforeRedo() {
	      var step = this.step + 1;
	      if (this.stack[step] && this.commands[this.stack[step]]) {
	        var command = this.commands[this.stack[step]];
	        return command.onBeforeCommand();
	      }
	      return Promise.resolve();
	    }
	  }, {
	    key: "getBackendActionParams",
	    value: function getBackendActionParams() {
	      if (this.type === History.TYPE_DESIGNER_BLOCK && this.designerBlockId) {
	        return {
	          blockId: this.designerBlockId
	        };
	      }
	      return {
	        lid: this.landingId
	      };
	    }
	    /**
	     * Applies preview history entry
	     * @return {Promise}
	     */
	  }, {
	    key: "undo",
	    value: function undo() {
	      var _this = this;
	      if (this.canUndo()) {
	        this.commandState = PENDING;
	        return this.beforeUndo().then(function () {
	          return BX.Landing.Backend.getInstance().action(_this.getUndoBackendActionName(), _this.getBackendActionParams());
	        }).then(function (command) {
	          if (command) {
	            var params = command.params;
	            var entry = new Entry({
	              block: params.block,
	              selector: params.selector,
	              command: command.command,
	              params: params
	            });
	            return _this.runCommand(entry, -1);
	          }
	          return Promise.reject();
	        }).then(function (res) {
	          return _this.offset(-1).then(onUpdate);
	        });
	      }
	      return Promise.resolve(this);
	    }
	    /**
	     * Applies preview next history entry
	     * @return {Promise}
	     */
	  }, {
	    key: "redo",
	    value: function redo() {
	      var _this2 = this;
	      if (this.canRedo()) {
	        this.commandState = PENDING;
	        return this.beforeRedo().then(function () {
	          return BX.Landing.Backend.getInstance().action(_this2.getRedoBackendActionName(), _this2.getBackendActionParams());
	        }).then(function (command) {
	          if (command) {
	            var params = command.params;
	            var entry = new Entry({
	              block: params.block,
	              selector: params.selector,
	              command: command.command,
	              params: params
	            });
	            return _this2.runCommand(entry, 1);
	          }
	          return Promise.reject();
	        }).then(function (res) {
	          return _this2.offset(1).then(onUpdate);
	        });
	      }
	      return Promise.resolve(this);
	    }
	  }, {
	    key: "runCommand",
	    value: function runCommand(entry, offsetValue) {
	      var _this3 = this;
	      if (entry) {
	        var command = this.commands[entry.command];
	        if (command) {
	          this.commandState = PENDING;
	          return command.command(entry).then(function () {
	            _this3.commandState = RESOLVED;
	            return _this3;
	          })["catch"](function () {
	            _this3.commandState = RESOLVED;
	            return _this3;
	          });
	        }
	      }
	    }
	  }, {
	    key: "offset",
	    value: function offset(offsetValue) {
	      if (this.commandState === PENDING) {
	        return Promise.resolve(this);
	      }
	      var step = this.step + offsetValue;
	      if (step >= 0 && step <= this.stackCount) {
	        this.step = step;
	      }
	      return Promise.resolve(this);
	    }
	    /**
	     * Check that there are actions to undo
	     * @returns {boolean}
	     */
	  }, {
	    key: "canUndo",
	    value: function canUndo() {
	      return this.commandState !== PENDING && this.step > 0 && this.stackCount > 0 && this.step <= this.stackCount;
	    }
	    /**
	     * Check that there are actions to redo
	     * @returns {boolean}
	     */
	  }, {
	    key: "canRedo",
	    value: function canRedo() {
	      return this.commandState !== PENDING && this.step < this.stackCount && this.step >= 0;
	    }
	    /**
	     * Adds entry to history stack
	     * @param {BX.Landing.History.Entry} entry
	     */
	  }, {
	    key: "push",
	    value: function push() {
	      var _this4 = this;
	      if (this.step < this.stackCount) {
	        this.stackCount = this.step;
	      }
	      this.step++;
	      this.stackCount++;
	      return new Promise(function (resolve) {
	        setTimeout(resolve, 400);
	      }).then(function () {
	        return loadStack(_this4);
	      }).then(onUpdate);
	    }
	    /**
	     * Registers unique history command
	     * @param {Command} command
	     */
	  }, {
	    key: "registerCommand",
	    value: function registerCommand(command) {
	      if (command instanceof Command) {
	        this.commands[command.id] = command;
	      }
	    }
	    /**
	     * Removes page history from storage
	     * @param {int} pageId
	     * @return {Promise<BX.Landing.History>}
	     */
	  }, {
	    key: "removePageHistory",
	    value: function removePageHistory$$1(pageId) {
	      // todo: publication clear method
	      return removePageHistory(pageId, this).then(function (history) {
	        var currentPageId;
	        try {
	          currentPageId = BX.Landing.Main.getInstance().id;
	        } catch (err) {
	          currentPageId = -1;
	        }
	        if (currentPageId === pageId) {
	          return clear(history);
	        }
	        return Promise.reject();
	      }).then(onUpdate)["catch"](function () {});
	    }
	    /**
	     * Handles storage event
	     * @param {StorageEvent} event
	     */
	  }, {
	    key: "onStorage",
	    value: function onStorage(event) {
	      if (event.key === null) {
	        if (!window.localStorage.history) {
	          clear(this).then(onUpdate);
	        }
	      }
	    }
	  }], [{
	    key: "getInstance",
	    // not delete - just for export
	    value: function getInstance() {
	      var rootWindow = landing_pageobject.PageObject.getRootWindow();
	      if (!rootWindow.BX.Landing.History.instance) {
	        rootWindow.BX.Landing.History.instance = new BX.Landing.History();
	      }
	      return rootWindow.BX.Landing.History.instance;
	    }
	  }]);
	  return History;
	}();
	babelHelpers.defineProperty(History, "TYPE_LANDING", 'L');
	babelHelpers.defineProperty(History, "TYPE_DESIGNER_BLOCK", 'D');
	babelHelpers.defineProperty(History, "Command", Command);
	babelHelpers.defineProperty(History, "Entry", Entry);
	babelHelpers.defineProperty(History, "Highlight", Highlight);

	exports.History = History;

}((this.BX.Landing = this.BX.Landing || {}),BX.Landing,BX,BX.Landing,BX.Landing.UI));
//# sourceMappingURL=history.bundle.js.map
