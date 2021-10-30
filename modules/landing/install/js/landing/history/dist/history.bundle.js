this.BX = this.BX || {};
(function (exports,landing_main,main_core,landing_pageobject,landing_ui_highlight) {
	'use strict';

	var UNDO = 'undo';
	var REDO = 'redo';
	var INIT = 'init';
	var RESOLVED = 'resolved';
	var PENDING = 'pending';
	var MAX_ENTRIES_COUNT = 100;

	var _BX$Landing$Utils = BX.Landing.Utils,
	    scrollTo = _BX$Landing$Utils.scrollTo,
	    highlight = _BX$Landing$Utils.highlight;
	/**
	 * @param {string} state
	 * @param {object} entry
	 * @return {Promise}
	 */

	function editText(state, entry) {
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

	    return scrollTo(node.node).then(highlight.bind(null, node.node, true)).then(function () {
	      return node.setValue(entry[state], false, true);
	    });
	  });
	}

	var _BX$Landing$Utils$1 = BX.Landing.Utils,
	    scrollTo$1 = _BX$Landing$Utils$1.scrollTo,
	    highlight$1 = _BX$Landing$Utils$1.highlight;
	/**
	 * @param {string} state
	 * @param {object} entry
	 * @return {Promise}
	 */

	function editEmbed(state, entry) {
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

	    return scrollTo$1(node.node).then(highlight$1.bind(null, node.node, true)).then(function () {
	      return node.setValue(entry[state], false, true);
	    });
	  });
	}

	var _BX$Landing$Utils$2 = BX.Landing.Utils,
	    scrollTo$2 = _BX$Landing$Utils$2.scrollTo,
	    highlight$2 = _BX$Landing$Utils$2.highlight;
	/**
	 * @param {string} state
	 * @param {object} entry
	 * @return {Promise}
	 */

	function editMap(state, entry) {
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

	    return scrollTo$2(node.node).then(highlight$2.bind(null, node.node, true)).then(function () {
	      return node.setValue(entry[state], false, true);
	    });
	  });
	}

	var _BX$Landing$Utils$3 = BX.Landing.Utils,
	    scrollTo$3 = _BX$Landing$Utils$3.scrollTo,
	    highlight$3 = _BX$Landing$Utils$3.highlight;
	/**
	 * @param {string} state
	 * @param {object} entry
	 * @return {Promise}
	 */

	function editImage(state, entry) {
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

	    return scrollTo$3(node.node).then(highlight$3.bind(null, node.node)).then(function () {
	      entry[state].id = 0;
	      return node.setValue(entry[state], false, true);
	    });
	  });
	}

	var editIcon = editImage;

	var _BX$Landing$Utils$4 = BX.Landing.Utils,
	    scrollTo$4 = _BX$Landing$Utils$4.scrollTo,
	    highlight$4 = _BX$Landing$Utils$4.highlight;
	/**
	 * @param {string} state
	 * @param {object} entry
	 * @return {Promise}
	 */

	function editLink(state, entry) {
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

	    return scrollTo$4(node.node).then(highlight$4.bind(null, node.node)).then(function () {
	      return node.setValue(entry[state], false, true);
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

	function sortBlock(state, entry) {
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.block);
	    block.forceInit();
	    return scrollTo$5(block.node).then(highlight$5.bind(null, block.node)).then(function () {
	      return block[entry[state]](true);
	    });
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

	function addBlock(state, entry) {
	  return landing_pageobject.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry[state].currentBlock);
	    return new Promise(function (resolve) {
	      if (block) {
	        block.forceInit();
	        return scrollTo$6(block.node).then(highlight$6.bind(null, block.node, false, true)).then(resolve);
	      }

	      resolve();
	    }).then(function () {
	      var landing = BX.Landing.Main.getInstance();
	      landing.currentBlock = block;
	      return landing_pageobject.PageObject.getInstance().view().then(function (iframe) {
	        landing.currentArea = iframe.contentDocument.body.querySelector("[data-landing=\"".concat(entry[state].lid, "\"]"));
	        return landing.onAddBlock(entry[state].code, entry.block, true);
	      });
	    });
	  });
	}

	var _BX$Landing$Utils$7 = BX.Landing.Utils,
	    scrollTo$7 = _BX$Landing$Utils$7.scrollTo,
	    highlight$7 = _BX$Landing$Utils$7.highlight;
	/**
	 * @param {string} state
	 * @param {object} entry
	 * @return {Promise}
	 */

	function removeBlock(state, entry) {
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.block);
	    block.forceInit();
	    return scrollTo$7(block.node).then(function () {
	      highlight$7(block.node);
	      return block.deleteBlock(true);
	    });
	  });
	}

	var _BX$Landing$Utils$8 = BX.Landing.Utils,
	    scrollTo$8 = _BX$Landing$Utils$8.scrollTo,
	    highlight$8 = _BX$Landing$Utils$8.highlight;
	/**
	 * @param {string} state
	 * @param {object} entry
	 * @return {Promise}
	 */

	function addCard(state, entry) {
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.block);

	    if (block) {
	      block.forceInit();
	    }

	    if (!block) {
	      return Promise.reject();
	    }

	    return block;
	  }).then(function (block) {
	    return BX.Landing.PageObject.getInstance().view().then(function (iframe) {
	      return [block, iframe.contentDocument.querySelector(entry[state].container)];
	    });
	  }).then(function (params) {
	    return scrollTo$8(params[1]).then(function () {
	      return params;
	    });
	  }).then(function (params) {
	    params[0].addCard({
	      index: entry[state].index,
	      container: params[1],
	      content: entry[state].html,
	      selector: entry.selector
	    });
	    var card = params[0].cards.getBySelector(entry.selector);

	    if (!card) {
	      return Promise.reject();
	    }

	    return highlight$8(card.node);
	  }).catch(function () {});
	}

	var _BX$Landing$Utils$9 = BX.Landing.Utils,
	    scrollTo$9 = _BX$Landing$Utils$9.scrollTo,
	    highlight$9 = _BX$Landing$Utils$9.highlight;
	/**
	 * @param {string} state
	 * @param {object} entry
	 * @return {Promise}
	 */

	function removeCard(state, entry) {
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.block);
	    block.forceInit();

	    if (!block) {
	      return Promise.reject();
	    }

	    var card = block.cards.getBySelector(entry.selector);

	    if (!card) {
	      return Promise.reject();
	    }

	    return scrollTo$9(card.node).then(highlight$9.bind(null, card.node)).then(function () {
	      return block.removeCard(entry.selector, true);
	    });
	  });
	}

	/**
	 * History entry action for add node.
	 * @param {string} state State code.
	 * @param {object} entry History entry.
	 * @return {Promise}
	 */
	function addNode(state, entry) {
	  var _this = this;

	  // entry.block === null >> designer mode
	  return new Promise(function (resolve, reject) {
	    var tags = (entry.redo || {}).tags || (entry.undo || {}).tags || [];
	    top.BX.onCustomEvent(_this, 'Landing:onHistoryAddNode', [tags]);
	    resolve();
	  });
	}

	/**
	 * History entry action for remove node.
	 * @param {string} state State code.
	 * @param {object} entry History entry.
	 * @return {Promise}
	 */
	function removeNode(state, entry) {
	  var _this = this;

	  // entry.block === null >> designer mode
	  return new Promise(function (resolve, reject) {
	    var tags = (entry.redo || {}).tags || (entry.undo || {}).tags || [];
	    top.BX.onCustomEvent(_this, 'Landing:onHistoryRemoveNode', [tags]);
	    resolve();
	  });
	}

	var _BX$Landing$Utils$a = BX.Landing.Utils,
	    scrollTo$a = _BX$Landing$Utils$a.scrollTo,
	    slice = _BX$Landing$Utils$a.slice;
	/**
	 * @param {string} state
	 * @param {object} entry
	 * @return {Promise}
	 */

	function editStyle(state, entry) {
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.block);

	    if (!block) {
	      return Promise.reject();
	    }

	    block.forceInit();
	    block.initStyles();
	    return block;
	  }).then(function (block) {
	    return scrollTo$a(block.node).then(function () {
	      return block;
	    });
	  }).then(function (block) {
	    var elements = slice(block.node.querySelectorAll(entry.selector));

	    if (block.selector === entry.selector) {
	      elements = [block.content];
	    }

	    elements.forEach(function (element) {
	      element.className = entry[state].className;

	      if (entry[state].style) {
	        element.style = entry[state].style;
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

	    var styleNode = block.styles.find(function (style) {
	      return style.selector === entry.selector || style.relativeSelector === entry.selector;
	    });

	    if (styleNode) {
	      block.onStyleInputWithDebounce({
	        node: styleNode.node,
	        data: styleNode.getValue()
	      });
	    }
	  });
	}

	var _BX$Landing$Utils$b = BX.Landing.Utils,
	    scrollTo$b = _BX$Landing$Utils$b.scrollTo,
	    highlight$a = _BX$Landing$Utils$b.highlight;
	/**
	 * @param {string} state
	 * @param {object} entry
	 * @return {Promise}
	 */

	function updateBlockState(state, entry) {
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.block);
	    block.forceInit();
	    return scrollTo$b(block.node).then(function () {
	      void highlight$a(block.node);
	      block.updateBlockState(BX.clone(entry[state]), true);
	    });
	  });
	}

	var _BX$Landing$Utils$c = BX.Landing.Utils,
	    scrollTo$c = _BX$Landing$Utils$c.scrollTo,
	    highlight$b = _BX$Landing$Utils$c.highlight;
	/**
	 * @param {string} state
	 * @param {object} entry
	 * @return {Promise}
	 */

	function updateContent(state, entry) {
	  return BX.Landing.PageObject.getInstance().blocks().then(function (blocks) {
	    var block = blocks.get(entry.block);
	    block.forceInit();
	    return scrollTo$c(block.node).then(function () {
	      void highlight$b(block.node);
	      return block.updateContent(entry[state]);
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
	  this.undo = main_core.Type.isFunction(options.undo) ? options.undo : function () {};
	  this.redo = main_core.Type.isFunction(options.redo) ? options.redo : function () {};
	};

	/**
	 * Registers base internal commands
	 * @param {History} history
	 * @return {Promise<History>}
	 */
	function registerBaseCommands(history) {
	  history.registerCommand(new Command({
	    id: 'editText',
	    undo: editText.bind(null, UNDO),
	    redo: editText.bind(null, REDO)
	  }));
	  history.registerCommand(new Command({
	    id: 'editEmbed',
	    undo: editEmbed.bind(null, UNDO),
	    redo: editEmbed.bind(null, REDO)
	  }));
	  history.registerCommand(new Command({
	    id: 'editMap',
	    undo: editMap.bind(null, UNDO),
	    redo: editMap.bind(null, REDO)
	  }));
	  history.registerCommand(new Command({
	    id: 'editImage',
	    undo: editImage.bind(null, UNDO),
	    redo: editImage.bind(null, REDO)
	  }));
	  history.registerCommand(new Command({
	    id: 'editIcon',
	    undo: editIcon.bind(null, UNDO),
	    redo: editIcon.bind(null, REDO)
	  }));
	  history.registerCommand(new Command({
	    id: 'editLink',
	    undo: editLink.bind(null, UNDO),
	    redo: editLink.bind(null, REDO)
	  }));
	  history.registerCommand(new Command({
	    id: 'sortBlock',
	    undo: sortBlock.bind(null, UNDO),
	    redo: sortBlock.bind(null, REDO)
	  }));
	  history.registerCommand(new Command({
	    id: 'addBlock',
	    undo: removeBlock.bind(null, UNDO),
	    redo: addBlock.bind(null, REDO)
	  }));
	  history.registerCommand(new Command({
	    id: 'removeBlock',
	    undo: addBlock.bind(null, UNDO),
	    redo: removeBlock.bind(null, REDO)
	  }));
	  history.registerCommand(new Command({
	    id: 'updateStyle',
	    undo: editStyle.bind(null, UNDO),
	    redo: editStyle.bind(null, REDO)
	  }));
	  history.registerCommand(new Command({
	    id: 'addCard',
	    undo: removeCard.bind(null, UNDO),
	    redo: addCard.bind(null, REDO)
	  }));
	  history.registerCommand(new Command({
	    id: 'removeCard',
	    undo: addCard.bind(null, UNDO),
	    redo: removeCard.bind(null, REDO)
	  }));
	  history.registerCommand(new Command({
	    id: 'addNode',
	    undo: removeNode.bind(null, UNDO),
	    redo: addNode.bind(null, REDO)
	  }));
	  history.registerCommand(new Command({
	    id: 'removeNode',
	    undo: addNode.bind(null, UNDO),
	    redo: removeNode.bind(null, REDO)
	  }));
	  history.registerCommand(new Command({
	    id: 'updateBlockState',
	    undo: updateBlockState.bind(null, UNDO),
	    redo: updateBlockState.bind(null, REDO)
	  }));
	  history.registerCommand(new Command({
	    id: 'updateContent',
	    undo: updateContent.bind(null, UNDO),
	    redo: updateContent.bind(null, REDO)
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
	  var currentPageId;

	  try {
	    currentPageId = landing_main.Main.getInstance().id;
	  } catch (err) {
	    currentPageId = -1;
	  }

	  return asyncJsonParse(window.localStorage.history).then(function (historyData) {
	    if (main_core.Type.isPlainObject(historyData) && currentPageId in historyData) {
	      return historyData[currentPageId];
	    }

	    return Promise.reject();
	  }).then(function (landingData) {
	    Object.keys(landingData.stack).forEach(function (key, index) {
	      history.stack.push(new BX.Landing.History.Entry(landingData.stack[key]));

	      if (index >= MAX_ENTRIES_COUNT) {
	        history.stack.shift();
	      }
	    });
	    history.position = Math.min(main_core.Text.toNumber(landingData.position), history.stack.length - 1);
	    history.state = landingData.state;
	    return history;
	  }).catch(function () {
	    return history;
	  });
	}

	/**
	 * Saves history to storage
	 * @param {History} history
	 * @return {Promise<History>}
	 */
	function saveStack(history) {
	  var currentPageId;

	  try {
	    currentPageId = landing_main.Main.getInstance().id;
	  } catch (err) {
	    currentPageId = -1;
	  }

	  return asyncJsonParse(window.localStorage.history).then(function (historyData) {
	    return main_core.Type.isPlainObject(historyData) ? historyData : {};
	  }).then(function (all) {
	    all[currentPageId] = {};
	    all[currentPageId].stack = history.stack;
	    all[currentPageId].position = history.position;
	    all[currentPageId].state = history.state;
	    return all;
	  }).then(asyncJsonStringify).then(function (allString) {
	    window.localStorage.history = allString;
	    return history;
	  });
	}

	/**
	 * Fetches entities from entries
	 * @param {BX.Landing.History.Entry[]} items
	 * @return {Promise<any>}
	 */
	function fetchEntities(items) {
	  var entities = {
	    blocks: [],
	    images: []
	  };
	  items.forEach(function (item) {
	    if (item.command === 'addBlock') {
	      entities.blocks.push(item.block);
	    }

	    if (item.command === 'editImage') {
	      entities.images.push({
	        block: item.block,
	        id: item.redo.id
	      });
	    }
	  });
	  return Promise.resolve(entities);
	}

	/**
	 * Makes request with removed entities
	 * @param {{
	 * 		blocks: int[],
	 * 		images: {block: int, id: int}[]
	 * 	}} entities
	 * @param {History} history
	 * @return {Promise<History>}
	 */
	function removeEntities(entities, history) {
	  // if (entities.blocks.length || entities.images.length)
	  // {
	  // 	return BX.Landing.Backend.getInstance().action("Landing::removeEntities", {data: entities})
	  // 		.then(function() {
	  // 			return onNewBranch(history);
	  // 		})
	  // 		.then(onUpdate);
	  // }
	  return Promise.resolve(history);
	}

	/**
	 * Offsets history by offset length
	 * @param {History} history
	 * @param {Integer} offsetValue
	 */
	function offset(history, offsetValue) {
	  if (history.commandState === PENDING) {
	    return Promise.resolve(history);
	  }

	  var position = history.position + offsetValue;
	  var state = history.state;

	  if (offsetValue < 0 && history.state !== UNDO) {
	    position += 1;
	    state = UNDO;
	  }

	  if (offsetValue > 0 && history.state !== REDO) {
	    position -= 1;
	    state = REDO;
	  }

	  if (position <= history.stack.length - 1 && position >= 0) {
	    history.position = position;
	    history.state = state;
	    var entry = history.stack[position];

	    if (entry) {
	      var command = history.commands[entry.command];

	      if (command) {
	        history.commandState = PENDING;
	        return command[state](entry).then(function () {
	          history.commandState = RESOLVED;
	          return history;
	        }).catch(function () {
	          history.commandState = RESOLVED;
	          return offset(history, offsetValue);
	        });
	      }
	    }
	  }

	  return Promise.resolve(history);
	}

	/**
	 * Clears history stack
	 * @param {History} history
	 * @return {Promise<History>}
	 */
	function clear(history) {
	  history.stack = [];
	  history.position = -1;
	  history.state = INIT;
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
	  this.undo = options.undo;
	  this.redo = options.redo;
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
	    this.stack = [];
	    this.commands = {};
	    this.position = -1;
	    this.state = INIT;
	    this.commandState = RESOLVED;
	    this.onStorage = this.onStorage.bind(this);
	    main_core.Event.bind(window, 'storage', this.onStorage);
	    registerBaseCommands(this).then(loadStack).then(saveStack).then(onInit);
	  }

	  babelHelpers.createClass(History, [{
	    key: "undo",

	    /**
	     * Applies preview history entry
	     * @return {Promise}
	     */
	    value: function undo() {
	      if (this.canUndo()) {
	        return offset(this, -1).then(saveStack).then(onUpdate);
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
	      if (this.canRedo()) {
	        return offset(this, 1).then(saveStack).then(onUpdate);
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
	      return this.position > 0 && this.state === REDO || this.position > 0 && this.state === UNDO || this.position === 0 && this.state !== UNDO;
	    }
	    /**
	     * Check that there are actions to redo
	     * @returns {boolean}
	     */

	  }, {
	    key: "canRedo",
	    value: function canRedo() {
	      return this.position < this.stack.length - 1 && this.state !== INIT || this.position !== -1 && this.position === this.stack.length - 1 && this.state !== REDO;
	    }
	    /**
	     * Adds entry to history stack
	     * @param {BX.Landing.History.Entry} entry
	     */

	  }, {
	    key: "push",
	    value: function push(entry) {
	      var startIndex = this.position + 1;
	      var deleteCount = this.stack.length;

	      if (this.state === UNDO) {
	        startIndex -= 1;
	      }

	      var deletedEntries = this.stack.splice(startIndex, deleteCount, entry);

	      if (this.stack.length > MAX_ENTRIES_COUNT) {
	        deletedEntries.push(this.stack.shift());
	      }

	      if (deletedEntries.length) {
	        void this.onNewBranch(deletedEntries);
	      }

	      this.position = this.stack.length - 1;
	      this.state = REDO;
	      saveStack(this).then(onUpdate);
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
	      }).then(onUpdate).catch(function () {});
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
	    /**
	     * Handles new branch events
	     * @param {BX.Landing.History.Entry[]} entries
	     * @return {Promise<History>}
	     */

	  }, {
	    key: "onNewBranch",
	    value: function onNewBranch(entries) {
	      var _this = this;

	      return fetchEntities(entries, this).then(function (entities) {
	        return removeEntities(entities, _this);
	      });
	    }
	  }], [{
	    key: "getInstance",
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
	babelHelpers.defineProperty(History, "Action", {
	  editText: editText,
	  editEmbed: editEmbed,
	  editMap: editMap,
	  editImage: editImage,
	  editIcon: editIcon,
	  editLink: editLink,
	  sortBlock: sortBlock,
	  addBlock: addBlock,
	  removeBlock: removeBlock,
	  addCard: addCard,
	  removeCard: removeCard,
	  editStyle: editStyle,
	  updateBlockState: updateBlockState,
	  addNode: addNode,
	  removeNode: removeNode,
	  updateContent: updateContent
	});

	exports.History = History;

}((this.BX.Landing = this.BX.Landing || {}),BX.Landing,BX,BX.Landing,BX.Landing.UI));
//# sourceMappingURL=history.bundle.js.map
