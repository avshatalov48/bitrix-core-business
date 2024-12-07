/* eslint-disable */
this.BX = this.BX || {};
(function (exports,landing_main,main_core,landing_backend,landing_pageobject,landing_ui_highlight) {
	'use strict';

	var RESOLVED = 'resolved';
	var PENDING = 'pending';
	var HISTORY_TYPES = {
	  landing: 'L',
	  designerBlock: 'D'
	};

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

	var Entry = function Entry(options) {
	  babelHelpers.classCallCheck(this, Entry);
	  this.block = options.block;
	  this.selector = options.selector;
	  this.command = main_core.Type.isStringFilled(options.command) ? options.command : '#invalidCommand';
	  this.params = options.params;
	};

	var _BX$Landing$Utils$9 = BX.Landing.Utils,
	  scrollTo$9 = _BX$Landing$Utils$9.scrollTo,
	  highlight$8 = _BX$Landing$Utils$9.highlight;
	var editComponent = function editComponent(entry) {
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    /**
	     * @type {BX.Landing.Block}
	     */
	    var block = blocks.get(entry.block);
	    if (!block) {
	      return Promise.reject();
	    }
	    block.forceInit();
	    if (!block.node) {
	      return Promise.reject();
	    }
	    return scrollTo$9(block.node).then(function () {
	      return block.applyAttributeChanges(babelHelpers.defineProperty({}, entry.params.selector, {
	        attrs: entry.params.value
	      }), true);
	    }).then(block.reload.bind(block)).then(highlight$8.bind(null, block.node, false, false));
	  });
	};

	var _BX$Landing$Utils$a = BX.Landing.Utils,
	  scrollTo$a = _BX$Landing$Utils$a.scrollTo,
	  highlight$9 = _BX$Landing$Utils$a.highlight;

	/**
	 * @param {object} entry
	 * @return {Promise}
	 */
	function updateContent(entry) {
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.block);
	    block.forceInit();
	    return scrollTo$a(block.node).then(function () {
	      void highlight$9(block.node);
	      return block.updateContent(entry.params.content, true);
	    });
	  });
	}

	var _BX$Landing$Utils$b = BX.Landing.Utils,
	  scrollTo$b = _BX$Landing$Utils$b.scrollTo,
	  highlight$a = _BX$Landing$Utils$b.highlight;

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
	      return scrollTo$b(block.node).then(function () {
	        void highlight$a(block.node);
	        if (Object.keys(updateBlockStateData).length > 0) {
	          block.updateBlockState(updateBlockStateData, true);
	        }
	      });
	    }
	  });
	}

	var _BX$Landing$Utils$c = BX.Landing.Utils,
	  scrollTo$c = _BX$Landing$Utils$c.scrollTo,
	  highlight$b = _BX$Landing$Utils$c.highlight;

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

	var _BX$Landing$Utils$d = BX.Landing.Utils,
	  scrollTo$d = _BX$Landing$Utils$d.scrollTo,
	  highlight$c = _BX$Landing$Utils$d.highlight;

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
	      scrollTo$d(block).then(highlight$c.bind(null, block, false, false));
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
	    id: 'editComponent',
	    command: editComponent
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
	 * Clears history stack
	 * @param {History} history
	 * @return {Promise<History>}
	 */
	function clear(history) {
	  history.stack = null;
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

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _loadFromBackend = /*#__PURE__*/new WeakSet();
	var _getLoadBackendActionName = /*#__PURE__*/new WeakSet();
	var _getLoadBackendParams = /*#__PURE__*/new WeakSet();
	var _adjustMultiPage = /*#__PURE__*/new WeakSet();
	var _isMultiPage = /*#__PURE__*/new WeakSet();
	var Stack = /*#__PURE__*/function () {
	  /**
	   * ID and type of main entity (landing or design block)
	   */

	  /**
	   * All entities in stack and them current steps
	   */

	  function Stack(_entityId) {
	    var entityType = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : HISTORY_TYPES.landing;
	    babelHelpers.classCallCheck(this, Stack);
	    _classPrivateMethodInitSpec(this, _isMultiPage);
	    _classPrivateMethodInitSpec(this, _adjustMultiPage);
	    _classPrivateMethodInitSpec(this, _getLoadBackendParams);
	    _classPrivateMethodInitSpec(this, _getLoadBackendActionName);
	    _classPrivateMethodInitSpec(this, _loadFromBackend);
	    babelHelpers.defineProperty(this, "items", []);
	    babelHelpers.defineProperty(this, "entitySteps", {});
	    this.mainEntityId = _entityId;
	    this.entityType = entityType;
	  }
	  babelHelpers.createClass(Stack, [{
	    key: "init",
	    value: function init() {
	      return _classPrivateMethodGet(this, _loadFromBackend, _loadFromBackend2).call(this).then(_classPrivateMethodGet(this, _adjustMultiPage, _adjustMultiPage2).bind(this));
	    }
	  }, {
	    key: "reload",
	    value: function reload() {
	      this.items = [];
	      this.step = 0;
	      return _classPrivateMethodGet(this, _loadFromBackend, _loadFromBackend2).call(this);
	    }
	  }, {
	    key: "setTypeDesignerBlock",
	    value: function setTypeDesignerBlock(blockId) {
	      this.mainEntityId = blockId;
	      this.entityType = HISTORY_TYPES.designerBlock;
	      return this.reload();
	    }
	  }, {
	    key: "getCommandName",
	    value: function getCommandName() {
	      var undo = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      var step = undo ? this.step : this.step + 1;
	      step--; // array index correction

	      return this.items[step] ? this.items[step].command : null;
	    }
	  }, {
	    key: "getCommandEntityId",
	    value: function getCommandEntityId() {
	      var undo = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      var step = undo ? this.step : this.step + 1;
	      step--; // array index correction

	      return this.items[step] ? this.items[step].entityId : null;
	    }
	    /**
	     * Check is stack undoable
	     * @return {boolean}
	     */
	  }, {
	    key: "canUndo",
	    value: function canUndo() {
	      return this.step > 0 && this.step <= this.items.length;
	    }
	    /**
	     * Check is stack reduable
	     * @return {boolean}
	     */
	  }, {
	    key: "canRedo",
	    value: function canRedo() {
	      return this.step >= 0 && this.step < this.items.length;
	    }
	    /**
	     * Change stack when undo or redo
	     * @param undo - if false - redo
	     * @return {Promise}
	     */
	  }, {
	    key: "offset",
	    value: function offset() {
	      var undo = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      var newStep = undo ? this.step - 1 : this.step + 1;
	      if (newStep >= 0 && newStep <= this.items.length) {
	        this.step = newStep;
	      }
	      return Promise.resolve();
	    }
	  }, {
	    key: "push",
	    value: function push() {
	      var _this = this;
	      // For some types actions history.push called before backend changes. Need add input timeout
	      return new Promise(function (resolve) {
	        setTimeout(function () {
	          // change values before load
	          if (_this.step < _this.items.length) {
	            _this.items = _this.items.slice(0, _this.step - 1);
	          }
	          _this.step++;
	          _this.items.push(_this.items[_this.step - 1]);
	          return _this.reload().then(resolve);
	        }, 500);
	      });
	    }
	  }]);
	  return Stack;
	}();
	function _loadFromBackend2() {
	  var _this2 = this;
	  return BX.Landing.Backend.getInstance().action(_classPrivateMethodGet(this, _getLoadBackendActionName, _getLoadBackendActionName2).call(this), _classPrivateMethodGet(this, _getLoadBackendParams, _getLoadBackendParams2).call(this)).then(function (data) {
	    var items = main_core.Type.isArray(data.stack) ? data.stack : [];
	    items.forEach(function (item) {
	      if (item.entityId && main_core.Type.isNumber(item.entityId) && item.command && main_core.Type.isString(item.command)) {
	        _this2.items.push({
	          entityId: item.entityId,
	          command: item.command
	        });
	        if (item.current && item.current === true) {
	          _this2.entitySteps[item.entityId] = _this2.items.length;
	        }
	      }
	    });
	    var step = main_core.Text.toNumber(data.step);
	    _this2.step = Math.min(_this2.items.length, step);
	    _this2.step = Math.max(0, _this2.step);
	  })["catch"](function (e) {
	    console.error('History load error', e);
	    return history;
	  });
	}
	function _getLoadBackendActionName2() {
	  if (this.entityType === HISTORY_TYPES.designerBlock) {
	    return "History::getForDesignerBlock";
	  }
	  return "History::getForLanding";
	}
	function _getLoadBackendParams2() {
	  if (this.entityType === HISTORY_TYPES.designerBlock) {
	    return {
	      blockId: this.mainEntityId
	    };
	  }
	  return {
	    lid: this.mainEntityId
	  };
	}
	function _adjustMultiPage2() {
	  var _this3 = this;
	  var currentItem = this.items[this.step - 1];
	  if (currentItem && this.entityType === HISTORY_TYPES.landing && _classPrivateMethodGet(this, _isMultiPage, _isMultiPage2).call(this)) {
	    var entitiesToClearFuture = [];
	    this.items.forEach(function (item, index) {
	      var step = index + 1;
	      if (step >= _this3.step) {
	        return;
	      }

	      // Clear future for all entities, except current, that have future (have steps after own current)
	      if (item.entityId !== currentItem.entityId && _this3.entitySteps[item.entityId] < step) {
	        entitiesToClearFuture.push(item.entityId);
	      }
	    });
	    if (entitiesToClearFuture.length > 0) {
	      var backend = landing_backend.Backend.getInstance();
	      var promises = [];
	      entitiesToClearFuture.forEach(function (entityId) {
	        promises.push(backend.action('History::clearFutureForLanding', {
	          landingId: entityId
	        }));
	      });
	      return Promise.all(promises).then(this.reload.bind(this));
	    }
	  }
	  return Promise.resolve();
	}
	function _isMultiPage2() {
	  return Object.keys(this.entitySteps).length > 1;
	}

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
	  /**
	   * Stack of action commands
	   */

	  /**
	   * Key - command name, value - a Command object
	   */

	  /**
	   * If command now running - set to PENDING
	   * @type {string}
	   */

	  /**
	   * Type of current entity
	   * @type {string}
	   */

	  function History() {
	    var _this = this;
	    babelHelpers.classCallCheck(this, History);
	    babelHelpers.defineProperty(this, "stack", null);
	    babelHelpers.defineProperty(this, "commands", {});
	    babelHelpers.defineProperty(this, "commandState", RESOLVED);
	    babelHelpers.defineProperty(this, "entityType", HISTORY_TYPES.landing);
	    try {
	      this.entityId = landing_main.Main.getInstance().id;
	    } catch (err) {
	      this.entityId = -1;
	    }
	    this.stack = new Stack(this.entityId);
	    this.stack.init().then(function () {
	      return registerBaseCommands(_this);
	    }).then(onInit);
	  }
	  babelHelpers.createClass(History, [{
	    key: "setTypeDesignerBlock",
	    /**
	     * Set special type for designer block history
	     * @param blockId
	     * @return {Promise<BX.Landing.History>|*}
	     */
	    value: function setTypeDesignerBlock(blockId) {
	      var _this2 = this;
	      this.entityType = HISTORY_TYPES.designerBlock;
	      this.entityId = blockId;
	      return this.stack.setTypeDesignerBlock(blockId).then(function () {
	        return _this2;
	      });
	    }
	  }, {
	    key: "getEntityId",
	    value: function getEntityId() {
	      return this.entityId;
	    }
	  }, {
	    key: "beforeUndo",
	    value: function beforeUndo() {
	      var commandName = this.stack.getCommandName();
	      if (commandName && this.commands[commandName]) {
	        var command = this.commands[commandName];
	        return command.onBeforeCommand();
	      }
	      return Promise.resolve();
	    }
	  }, {
	    key: "beforeRedo",
	    value: function beforeRedo() {
	      var commandName = this.stack.getCommandName(false);
	      if (commandName && this.commands[commandName]) {
	        var command = this.commands[commandName];
	        return command.onBeforeCommand();
	      }
	      return Promise.resolve();
	    }
	    /**
	     * Applies preview history entry
	     * @return {Promise}
	     */
	  }, {
	    key: "undo",
	    value: function undo() {
	      var _this3 = this;
	      if (this.canUndo()) {
	        this.commandState = PENDING;
	        return this.beforeUndo().then(function () {
	          return landing_backend.Backend.getInstance().action(_this3.getBackendActionName(true), _this3.getBackendActionParams(true));
	        }).then(function (command) {
	          if (command) {
	            var params = command.params;
	            var entry = new Entry({
	              block: params.block,
	              selector: params.selector,
	              command: command.command,
	              params: params
	            });
	            return _this3.runCommand(entry);
	          }
	          return Promise.reject();
	        }).then(function () {
	          return _this3.offset();
	        }).then(onUpdate);
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
	      var _this4 = this;
	      if (this.canRedo()) {
	        this.commandState = PENDING;
	        return this.beforeRedo().then(function () {
	          return landing_backend.Backend.getInstance().action(_this4.getBackendActionName(false), _this4.getBackendActionParams(false));
	        }).then(function (command) {
	          if (command) {
	            var params = command.params;
	            var entry = new Entry({
	              block: params.block,
	              selector: params.selector,
	              command: command.command,
	              params: params
	            });
	            return _this4.runCommand(entry);
	          }
	          return Promise.reject();
	        }).then(function () {
	          return _this4.offset(false);
	        }).then(onUpdate);
	      }
	      return Promise.resolve(this);
	    }
	    /**
	     * Get name for backend action
	     * @param {boolean} undo - true, if need undo, false for redo
	     * @return {string}
	     */
	  }, {
	    key: "getBackendActionName",
	    value: function getBackendActionName() {
	      var undo = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      if (this.entityType === HISTORY_TYPES.designerBlock) {
	        return undo ? 'History::undoDesignerBlock' : 'History::redoDesignerBlock';
	      }
	      return undo ? 'History::undoLanding' : 'History::redoLanding';
	    }
	    /**
	     * Get id for entity for backend action
	     * @param {boolean} undo - true, if need undo, false for redo
	     * @return {string}
	     */
	  }, {
	    key: "getBackendActionParams",
	    value: function getBackendActionParams() {
	      var undo = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      if (this.entityType === HISTORY_TYPES.designerBlock) {
	        return {
	          blockId: this.entityId
	        };
	      }
	      return {
	        lid: this.stack.getCommandEntityId(undo)
	      };
	    }
	  }, {
	    key: "runCommand",
	    value: function runCommand(entry) {
	      var _this5 = this;
	      if (entry) {
	        var command = this.commands[entry.command];
	        if (command) {
	          this.commandState = PENDING;
	          return command.command(entry).then(function () {
	            _this5.commandState = RESOLVED;
	            return _this5;
	          })["catch"](function (err) {
	            console.error("History error in command ".concat(command.id, "."), err);
	            _this5.commandState = RESOLVED;
	            return _this5;
	          });
	        }
	      }
	    }
	  }, {
	    key: "offset",
	    value: function offset() {
	      var _this6 = this;
	      var undo = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      if (this.commandState === PENDING) {
	        return Promise.resolve(this);
	      }
	      return this.stack.offset(undo).then(function () {
	        return _this6;
	      });
	    }
	    /**
	     * Check that there are actions to undo
	     * @returns {boolean}
	     */
	  }, {
	    key: "canUndo",
	    value: function canUndo() {
	      return this.commandState !== PENDING && this.stack.canUndo();
	    }
	    /**
	     * Check that there are actions to redo
	     * @returns {boolean}
	     */
	  }, {
	    key: "canRedo",
	    value: function canRedo() {
	      return this.commandState !== PENDING && this.stack.canRedo();
	    }
	    /**
	     * Adds entry to history stack
	     */
	  }, {
	    key: "push",
	    value: function push() {
	      var _this7 = this;
	      return this.stack.push().then(function () {
	        return onUpdate(_this7);
	      });
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
	babelHelpers.defineProperty(History, "Command", Command);
	babelHelpers.defineProperty(History, "Entry", Entry);
	babelHelpers.defineProperty(History, "Highlight", Highlight);

	exports.History = History;

}((this.BX.Landing = this.BX.Landing || {}),BX.Landing,BX,BX.Landing,BX.Landing,BX.Landing.UI));
//# sourceMappingURL=history.bundle.js.map
