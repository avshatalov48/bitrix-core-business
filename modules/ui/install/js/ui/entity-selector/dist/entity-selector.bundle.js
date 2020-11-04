this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_popup,main_core_events,main_core,main_loader) {
	'use strict';

	var _Symbol$iterator;
	_Symbol$iterator = Symbol.iterator;

	var ItemCollection = /*#__PURE__*/function () {
	  function ItemCollection() {
	    var comparator = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	    babelHelpers.classCallCheck(this, ItemCollection);
	    babelHelpers.defineProperty(this, "comparator", null);
	    babelHelpers.defineProperty(this, "items", []);
	    this.comparator = main_core.Type.isFunction(comparator) ? comparator : null;
	  }

	  babelHelpers.createClass(ItemCollection, [{
	    key: "add",
	    value: function add(item) {
	      var index = -1;

	      if (this.comparator) {
	        index = this.searchIndexToInsert(item);
	        this.items.splice(index, 0, item);
	      } else {
	        this.items.push(item);
	      }

	      return index;
	    }
	  }, {
	    key: "has",
	    value: function has(item) {
	      return this.items.includes(item);
	    }
	  }, {
	    key: "getIndex",
	    value: function getIndex(item) {
	      return this.items.indexOf(item);
	    }
	  }, {
	    key: "getByIndex",
	    value: function getByIndex(index) {
	      if (main_core.Type.isNumber(index) && index >= 0) {
	        var item = this.items[index];
	        return main_core.Type.isUndefined(item) ? null : item;
	      }

	      return null;
	    }
	  }, {
	    key: "getFirst",
	    value: function getFirst() {
	      var first = this.items[0];
	      return main_core.Type.isUndefined(first) ? null : first;
	    }
	  }, {
	    key: "getLast",
	    value: function getLast() {
	      var last = this.items[this.count() - 1];
	      return main_core.Type.isUndefined(last) ? null : last;
	    }
	  }, {
	    key: "count",
	    value: function count() {
	      return this.items.length;
	    }
	  }, {
	    key: "delete",
	    value: function _delete(item) {
	      var index = this.getIndex(item);

	      if (index !== -1) {
	        this.items.splice(index, 1);
	        return true;
	      }

	      return false;
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      this.items = [];
	    }
	  }, {
	    key: _Symbol$iterator,
	    value: function value() {
	      return this.items[Symbol.iterator]();
	    }
	  }, {
	    key: "forEach",
	    value: function forEach(callbackfn, thisArg) {
	      return this.items.forEach(callbackfn, thisArg);
	    }
	  }, {
	    key: "getItems",
	    value: function getItems() {
	      return this.items;
	    }
	  }, {
	    key: "searchIndexToInsert",
	    value: function searchIndexToInsert(value) {
	      var low = 0;
	      var high = this.items.length;

	      while (low < high) {
	        var mid = Math.floor((low + high) / 2);

	        if (this.comparator(this.items[mid], value) >= 0) {
	          low = mid + 1;
	        } else {
	          high = mid;
	        }
	      }

	      return low;
	    }
	  }]);
	  return ItemCollection;
	}();

	var ItemNodeComparator = /*#__PURE__*/function () {
	  function ItemNodeComparator() {
	    babelHelpers.classCallCheck(this, ItemNodeComparator);
	  }

	  babelHelpers.createClass(ItemNodeComparator, null, [{
	    key: "makeComparator",
	    value: function makeComparator(orderProperty, orderDirection) {
	      var sortOrder = orderDirection === 'desc' ? 1 : -1;
	      return function (nodeA, nodeB) {
	        var itemA = nodeA.getItem();
	        var itemB = nodeB.getItem();
	        var valueA = itemA[orderProperty];
	        var valueB = itemB[orderProperty];
	        var result = 0;

	        if (main_core.Type.isString(valueA)) {
	          result = valueA.localeCompare(valueB);
	        } else {
	          if (main_core.Type.isNull(valueA) || main_core.Type.isNull(valueB)) {
	            result = valueA === valueB ? 0 : main_core.Type.isNull(valueA) ? 1 : -1;
	          } else {
	            result = valueA < valueB ? -1 : valueA > valueB ? 1 : 0;
	          }
	        }

	        return result * sortOrder;
	      };
	    }
	  }, {
	    key: "makeMultipleComparator",
	    value: function makeMultipleComparator(order) {
	      var _this = this;

	      var props = Object.keys(order);
	      return function (a, b) {
	        var i = 0;
	        var result = 0;
	        var numberOfProperties = props.length;

	        while (result === 0 && i < numberOfProperties) {
	          var orderProperty = props[i];
	          var orderDirection = order[props[i]];
	          result = _this.makeComparator(orderProperty, orderDirection)(a, b);
	          i += 1;
	        }

	        return result;
	      };
	    }
	  }]);
	  return ItemNodeComparator;
	}();

	var Highlighter = /*#__PURE__*/function () {
	  function Highlighter() {
	    babelHelpers.classCallCheck(this, Highlighter);
	  }

	  babelHelpers.createClass(Highlighter, null, [{
	    key: "mark",
	    value: function mark(text, matches) {
	      if (!main_core.Type.isStringFilled(text) || !matches || matches.count() === 0) {
	        return text;
	      }

	      var result = '';
	      var offset = 0;
	      matches.forEach(function (match) {
	        if (offset > match.getStartIndex()) {
	          return;
	        } // console.log(match.getStartIndex(), match.getEndIndex(), match.getQueryWord());


	        result += main_core.Text.encode(text.substring(offset, match.getStartIndex()));
	        result += '<span class="ui-selector-highlight-mark">';
	        result += main_core.Text.encode(text.substring(match.getStartIndex(), match.getEndIndex()));
	        result += '</span>';
	        offset = match.getEndIndex();
	      });
	      result += main_core.Text.encode(text.substring(offset)); // console.log(result);

	      return result;
	    }
	  }]);
	  return Highlighter;
	}();

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-selector-item-badge\"></span>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var ItemBadge = /*#__PURE__*/function () {
	  function ItemBadge(badgeOptions) {
	    babelHelpers.classCallCheck(this, ItemBadge);
	    babelHelpers.defineProperty(this, "title", '');
	    babelHelpers.defineProperty(this, "textColor", null);
	    babelHelpers.defineProperty(this, "bgColor", null);
	    babelHelpers.defineProperty(this, "container", null);
	    var options = main_core.Type.isPlainObject(badgeOptions) ? badgeOptions : {};
	    this.setTitle(options.title);
	    this.setTextColor(options.textColor);
	    this.setBgColor(options.bgColor);
	  }

	  babelHelpers.createClass(ItemBadge, [{
	    key: "getTitle",
	    value: function getTitle() {
	      return this.title;
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      if (main_core.Type.isStringFilled(title)) {
	        this.title = title;
	      }

	      return this;
	    }
	  }, {
	    key: "getTextColor",
	    value: function getTextColor() {
	      return this.textColor;
	    }
	  }, {
	    key: "setTextColor",
	    value: function setTextColor(textColor) {
	      if (main_core.Type.isString(textColor) || textColor === null) {
	        this.textColor = textColor;
	      }

	      return this;
	    }
	  }, {
	    key: "getBgColor",
	    value: function getBgColor() {
	      return this.bgColor;
	    }
	  }, {
	    key: "setBgColor",
	    value: function setBgColor(bgColor) {
	      if (main_core.Type.isString(bgColor) || bgColor === null) {
	        this.bgColor = bgColor;
	      }

	      return this;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var container = main_core.Tag.render(_templateObject());
	      container.textContent = this.getTitle();
	      main_core.Dom.style(container, 'color', this.getTextColor());
	      main_core.Dom.style(container, 'background-color', this.getBgColor());
	      return container;
	    }
	  }]);
	  return ItemBadge;
	}();

	var comparator = function comparator(a, b) {
	  if (a.getStartIndex() === b.getStartIndex()) {
	    return a.getEndIndex() > b.getEndIndex() ? 1 : -1;
	  } else {
	    return a.getStartIndex() > b.getStartIndex() ? -1 : 1;
	  }
	};

	var MatchField = /*#__PURE__*/function () {
	  function MatchField(field) {
	    var indexes = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
	    babelHelpers.classCallCheck(this, MatchField);
	    babelHelpers.defineProperty(this, "field", null);
	    babelHelpers.defineProperty(this, "matchIndexes", new ItemCollection(comparator));
	    this.field = field;
	    this.addIndexes(indexes);
	  }

	  babelHelpers.createClass(MatchField, [{
	    key: "getField",
	    value: function getField() {
	      return this.field;
	    }
	  }, {
	    key: "getMatches",
	    value: function getMatches() {
	      return this.matchIndexes;
	    }
	  }, {
	    key: "addIndex",
	    value: function addIndex(matchIndex) {
	      this.matchIndexes.add(matchIndex);
	    }
	  }, {
	    key: "addIndexes",
	    value: function addIndexes(matchIndexes) {
	      var _this = this;

	      if (main_core.Type.isArray(matchIndexes)) {
	        matchIndexes.forEach(function (matchIndex) {
	          _this.addIndex(matchIndex);
	        });
	      }
	    }
	  }]);
	  return MatchField;
	}();

	function _templateObject12() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"ui-selector-item-link-text\">", "</span>\n\t\t\t"]);

	  _templateObject12 = function _templateObject12() {
	    return data;
	  };

	  return data;
	}

	function _templateObject11() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a \n\t\t\t\t\tclass=\"ui-selector-item-link\"\n\t\t\t\t\thref=\"", "\" \n\t\t\t\t\ttarget=\"_blank\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t>", "</a>\n\t\t\t"]);

	  _templateObject11 = function _templateObject11() {
	    return data;
	  };

	  return data;
	}

	function _templateObject10() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-item-badges\">", "</div>\n\t\t\t"]);

	  _templateObject10 = function _templateObject10() {
	    return data;
	  };

	  return data;
	}

	function _templateObject9() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-item-indicator\"></div>\n\t\t\t"]);

	  _templateObject9 = function _templateObject9() {
	    return data;
	  };

	  return data;
	}

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-item-caption\"></div>\n\t\t\t"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-item-supertitle\"></div>\n\t\t\t"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-item-subtitle\"></div>\n\t\t\t"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-item-title\"></div>\n\t\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-item-avatar\"></div>\n\t\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div \n\t\t\t\t\tclass=\"ui-selector-item\" \n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\tonmouseenter=\"", "\"\n\t\t\t\t\tonmouseleave=\"", "\"\n\t\t\t\t>\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"ui-selector-item-titles\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t<div class=\"ui-selector-item-title-box\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-item-children\" ontransitionend=\"", "\"></div>\n\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-item-box", "\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var RenderMode = function RenderMode() {
	  babelHelpers.classCallCheck(this, RenderMode);
	};
	babelHelpers.defineProperty(RenderMode, "PARTIAL", 'partial');
	babelHelpers.defineProperty(RenderMode, "OVERRIDE", 'override');

	var ItemNode = /*#__PURE__*/function () {
	  // for the fast access
	  function ItemNode(item, nodeOptions) {
	    babelHelpers.classCallCheck(this, ItemNode);
	    babelHelpers.defineProperty(this, "item", null);
	    babelHelpers.defineProperty(this, "tab", null);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    babelHelpers.defineProperty(this, "parentNode", null);
	    babelHelpers.defineProperty(this, "children", null);
	    babelHelpers.defineProperty(this, "childItems", new WeakMap());
	    babelHelpers.defineProperty(this, "loaded", false);
	    babelHelpers.defineProperty(this, "dynamic", false);
	    babelHelpers.defineProperty(this, "dynamicPromise", null);
	    babelHelpers.defineProperty(this, "loader", null);
	    babelHelpers.defineProperty(this, "open", false);
	    babelHelpers.defineProperty(this, "autoOpen", false);
	    babelHelpers.defineProperty(this, "focused", false);
	    babelHelpers.defineProperty(this, "renderMode", RenderMode.PARTIAL);
	    babelHelpers.defineProperty(this, "title", null);
	    babelHelpers.defineProperty(this, "subtitle", null);
	    babelHelpers.defineProperty(this, "caption", null);
	    babelHelpers.defineProperty(this, "supertitle", null);
	    babelHelpers.defineProperty(this, "avatar", null);
	    babelHelpers.defineProperty(this, "link", null);
	    babelHelpers.defineProperty(this, "linkTitle", null);
	    babelHelpers.defineProperty(this, "textColor", null);
	    babelHelpers.defineProperty(this, "badges", null);
	    babelHelpers.defineProperty(this, "highlights", []);
	    babelHelpers.defineProperty(this, "renderWithDebounce", main_core.Runtime.debounce(this.render, 50, this));
	    var options = main_core.Type.isPlainObject(nodeOptions) ? nodeOptions : {};

	    if (main_core.Type.isObject(item)) {
	      this.item = item;
	    }

	    var comparator = null;

	    if (main_core.Type.isFunction(options.itemOrder)) {
	      comparator = options.itemOrder;
	    } else if (main_core.Type.isPlainObject(options.itemOrder)) {
	      comparator = ItemNodeComparator.makeMultipleComparator(options.itemOrder);
	    }

	    this.children = new ItemCollection(comparator);
	    this.renderMode = options.renderMode === RenderMode.OVERRIDE ? RenderMode.OVERRIDE : RenderMode.PARTIAL;

	    if (this.renderMode === RenderMode.OVERRIDE) {
	      this.title = '';
	      this.subtitle = '';
	      this.caption = '';
	      this.supertitle = '';
	      this.avatar = '';
	      this.textColor = '';
	      this.link = '';
	      this.linkTitle = '';
	      this.badges = [];
	    }

	    this.setTitle(options.title);
	    this.setSubtitle(options.subtitle);
	    this.setSupertitle(options.supertitle);
	    this.setCaption(options.caption);
	    this.setAvatar(options.avatar);
	    this.setTextColor(options.textColor);
	    this.setLink(options.link);
	    this.setLinkTitle(options.linkTitle);
	    this.setBadges(options.badges);
	    this.setDynamic(options.dynamic);
	    this.setOpen(options.open);
	  }

	  babelHelpers.createClass(ItemNode, [{
	    key: "getItem",
	    value: function getItem() {
	      return this.item;
	    }
	  }, {
	    key: "isRoot",
	    value: function isRoot() {
	      return this.getParentNode() === null;
	    }
	  }, {
	    key: "getDialog",
	    value: function getDialog() {
	      return this.getTab().getDialog();
	    }
	  }, {
	    key: "setTab",
	    value: function setTab(tab) {
	      this.tab = tab;
	      return this;
	    }
	  }, {
	    key: "getTab",
	    value: function getTab() {
	      return this.tab;
	    }
	  }, {
	    key: "getParentNode",
	    value: function getParentNode() {
	      return this.parentNode;
	    }
	  }, {
	    key: "setParentNode",
	    value: function setParentNode(parentNode) {
	      this.parentNode = parentNode;
	      return this;
	    }
	  }, {
	    key: "getNextSibling",
	    value: function getNextSibling() {
	      if (!this.getParentNode()) {
	        return null;
	      }

	      var siblings = this.getParentNode().getChildren();
	      var index = siblings.getIndex(this);
	      return siblings.getByIndex(index + 1);
	    }
	  }, {
	    key: "getPreviousSibling",
	    value: function getPreviousSibling() {
	      if (!this.getParentNode()) {
	        return null;
	      }

	      var siblings = this.getParentNode().getChildren();
	      var index = siblings.getIndex(this);
	      return siblings.getByIndex(index - 1);
	    }
	  }, {
	    key: "addChildren",
	    value: function addChildren(children) {
	      var _this = this;

	      if (!main_core.Type.isArray(children)) {
	        return;
	      }

	      children.forEach(function (childOptions) {
	        delete childOptions.tabs;

	        var childItem = _this.getDialog().addItem(childOptions);

	        var childNode = _this.addItem(childItem, childOptions.nodeOptions);

	        childNode.addChildren(childOptions.children);
	      });
	    }
	  }, {
	    key: "addChild",
	    value: function addChild(child) {
	      if (!(child instanceof ItemNode)) {
	        throw new Error('EntitySelector.ItemNode: an item must be an instance of EntitySelector.ItemNode.');
	      }

	      if (this.isChildOf(child) || child === this) {
	        throw new Error('EntitySelector.ItemNode: a child item cannot be a parent of current item.');
	      }

	      if (this.getChildren().has(child) || this.childItems.has(child.getItem())) {
	        return null;
	      }

	      this.getChildren().add(child);
	      this.childItems.set(child.getItem(), child);
	      child.setTab(this.getTab());
	      child.setParentNode(this);

	      if (this.isRendered()) {
	        this.renderWithDebounce();
	      }

	      return child;
	    }
	  }, {
	    key: "getDepthLevel",
	    value: function getDepthLevel() {
	      return this.isRoot() ? 0 : this.getParentNode().getDepthLevel() + 1;
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(item, nodeOptions) {
	      var itemNode = this.childItems.get(item);

	      if (!itemNode) {
	        itemNode = item.createNode(nodeOptions);
	        this.addChild(itemNode);
	      }

	      return itemNode;
	    }
	  }, {
	    key: "hasItem",
	    value: function hasItem(item) {
	      return this.childItems.has(item);
	    }
	  }, {
	    key: "removeChild",
	    value: function removeChild(child) {
	      if (!this.getChildren().has(child)) {
	        return false;
	      }

	      while (child.getFirstChild()) {
	        child.removeChild(child.getFirstChild());
	      }

	      if (child.isFocused()) {
	        child.unfocus();
	      }

	      child.setParentNode(null);
	      child.getItem().removeNode(child);
	      this.getChildren().delete(child);
	      this.childItems.delete(child.getItem());

	      if (this.isRendered()) {
	        this.render();
	      }

	      return true;
	    }
	  }, {
	    key: "removeChildren",
	    value: function removeChildren() {
	      while (this.getFirstChild()) {
	        this.removeChild(this.getFirstChild());
	      }
	    }
	  }, {
	    key: "hasChild",
	    value: function hasChild(child) {
	      return this.getChildren().has(child);
	    }
	  }, {
	    key: "isChildOf",
	    value: function isChildOf(parent) {
	      var parentNode = this.getParentNode();

	      while (parentNode !== null) {
	        if (parentNode === parent) {
	          return true;
	        }

	        parentNode = parentNode.getParentNode();
	      }

	      return false;
	    }
	  }, {
	    key: "getFirstChild",
	    value: function getFirstChild() {
	      return this.children.getFirst();
	    }
	  }, {
	    key: "getLastChild",
	    value: function getLastChild() {
	      return this.children.getLast();
	    }
	  }, {
	    key: "getChildren",
	    value: function getChildren() {
	      return this.children;
	    }
	  }, {
	    key: "hasChildren",
	    value: function hasChildren() {
	      return this.children.count() > 0;
	    }
	  }, {
	    key: "loadChildren",
	    value: function loadChildren() {
	      var _this2 = this;

	      if (!this.isDynamic()) {
	        throw new Error('EntitySelector.ItemNode.loadChildren: an item node is not dynamic.');
	      }

	      if (this.dynamicPromise) {
	        return this.dynamicPromise;
	      }

	      this.dynamicPromise = main_core.ajax.runAction('ui.entityselector.getChildren', {
	        json: {
	          parentItem: this.getItem(),
	          dialog: this.getDialog()
	        },
	        getParameters: {
	          context: this.getDialog().getContext()
	        }
	      });
	      this.dynamicPromise.then(function (response) {
	        if (response && response.data && main_core.Type.isPlainObject(response.data.dialog)) {
	          _this2.addChildren(response.data.dialog.items);

	          _this2.render();
	        }

	        _this2.loaded = true;
	      });
	      this.dynamicPromise.catch(function (error) {
	        _this2.loaded = false;
	        _this2.dynamicPromise = null;
	        console.error(error);
	      });
	      return this.dynamicPromise;
	    }
	  }, {
	    key: "setOpen",
	    value: function setOpen(open) {
	      if (main_core.Type.isBoolean(open)) {
	        if (open && this.isDynamic() && !this.isLoaded()) {
	          this.setAutoOpen(true);
	        } else {
	          this.open = open;
	        }
	      }

	      return this;
	    }
	  }, {
	    key: "isOpen",
	    value: function isOpen() {
	      return this.open;
	    }
	  }, {
	    key: "isAutoOpen",
	    value: function isAutoOpen() {
	      return this.autoOpen && this.isDynamic() && !this.isLoaded();
	    }
	  }, {
	    key: "setAutoOpen",
	    value: function setAutoOpen(autoOpen) {
	      if (main_core.Type.isBoolean(autoOpen)) {
	        this.autoOpen = autoOpen;
	      }

	      return this;
	    }
	  }, {
	    key: "setDynamic",
	    value: function setDynamic(dynamic) {
	      if (main_core.Type.isBoolean(dynamic)) {
	        this.dynamic = dynamic;
	      }

	      return this;
	    }
	  }, {
	    key: "isDynamic",
	    value: function isDynamic() {
	      return this.dynamic;
	    }
	  }, {
	    key: "isLoaded",
	    value: function isLoaded() {
	      return this.loaded;
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      if (this.loader === null) {
	        this.loader = new main_loader.Loader({
	          target: this.getIndicatorContainer(),
	          size: 30
	        });
	      }

	      return this.loader;
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      this.getLoader().show();
	      main_core.Dom.addClass(this.getIndicatorContainer(), 'ui-selector-item-indicator-hidden');
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      this.getLoader().hide();
	      main_core.Dom.removeClass(this.getIndicatorContainer(), 'ui-selector-item-indicator-hidden');
	    }
	  }, {
	    key: "destroyLoader",
	    value: function destroyLoader() {
	      this.getLoader().destroy();
	      this.loader = null;
	      main_core.Dom.removeClass(this.getIndicatorContainer(), 'ui-selector-item-indicator-hidden');
	    }
	  }, {
	    key: "expand",
	    value: function expand() {
	      var _this3 = this;

	      if (this.isOpen() || !this.hasChildren() && !this.isDynamic()) {
	        return;
	      }

	      if (this.isDynamic() && !this.isLoaded()) {
	        this.loadChildren().then(function () {
	          _this3.destroyLoader();

	          _this3.expand();
	        });
	        this.showLoader();
	        return;
	      }

	      main_core.Dom.style(this.getChildrenContainer(), 'height', "".concat(this.getChildrenContainer().scrollHeight, "px"));
	      main_core.Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-open');
	      this.setOpen(true);
	    }
	  }, {
	    key: "collapse",
	    value: function collapse() {
	      var _this4 = this;

	      if (!this.isOpen()) {
	        return;
	      }

	      main_core.Dom.style(this.getChildrenContainer(), 'height', "".concat(this.getChildrenContainer().offsetHeight, "px"));
	      requestAnimationFrame(function () {
	        main_core.Dom.removeClass(_this4.getOuterContainer(), 'ui-selector-item-box-open');
	        main_core.Dom.style(_this4.getChildrenContainer(), 'height', null);

	        _this4.setOpen(false);
	      });
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      if (this.isRoot()) {
	        this.renderRoot();
	        return;
	      }

	      this.getTitleContainer().textContent = main_core.Type.isString(this.getTitle()) ? this.getTitle() : '';
	      this.getSubtitleContainer().textContent = main_core.Type.isString(this.getSubtitle()) ? this.getSubtitle() : '';
	      this.getSupertitleContainer().textContent = main_core.Type.isString(this.getSupertitle()) ? this.getSupertitle() : '';
	      this.getCaptionContainer().textContent = main_core.Type.isString(this.getCaption()) ? this.getCaption() : '';

	      if (main_core.Type.isStringFilled(this.getTextColor())) {
	        main_core.Dom.style(this.getTitleContainer(), 'color', this.getTextColor());
	      } else {
	        main_core.Dom.style(this.getTitleContainer(), 'color', null);
	      }

	      if (main_core.Type.isStringFilled(this.getAvatar())) {
	        main_core.Dom.style(this.getAvatarContainer(), 'background-image', "url('".concat(this.getAvatar(), "')"));
	      } else {
	        main_core.Dom.style(this.getAvatarContainer(), 'background-image', null);
	      }

	      if (this.hasChildren() || this.isDynamic()) {
	        main_core.Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-has-children');

	        if (this.getDepthLevel() >= this.getTab().getItemMaxDepth()) {
	          main_core.Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-max-depth');
	        }
	      } else {
	        main_core.Dom.removeClass(this.getOuterContainer(), ['ui-selector-item-box-has-children', 'ui-selector-item-box-max-depth']);
	      }

	      this.highlight();

	      if (this.isAutoOpen()) {
	        this.expand();
	        this.setAutoOpen(false);
	      }

	      this.renderChildren();
	      this.rendered = true;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "renderRoot",
	    value: function renderRoot() {
	      this.renderChildren();
	      this.rendered = true;
	      var stub = this.getTab().getStub();

	      if (stub && stub.isAutoShow() && (this.getDialog().isLoaded() || !this.getDialog().hasDynamicLoad())) {
	        if (this.hasChildren()) {
	          stub.hide();
	        } else {
	          stub.show();
	        }
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "renderChildren",
	    value: function renderChildren() {
	      var _this5 = this;

	      main_core.Dom.clean(this.getChildrenContainer());

	      if (this.hasChildren()) {
	        this.getChildren().forEach(function (child) {
	          child.render();
	          main_core.Dom.append(child.getOuterContainer(), _this5.getChildrenContainer());
	        });
	      }
	    }
	  }, {
	    key: "isRendered",
	    value: function isRendered() {
	      return this.rendered && this.getDialog() && this.getDialog().isRendered();
	    }
	  }, {
	    key: "enableRender",
	    value: function enableRender() {
	      this.rendered = true;
	    }
	  }, {
	    key: "disableRender",
	    value: function disableRender() {
	      this.rendered = false;
	    }
	  }, {
	    key: "getRenderMode",
	    value: function getRenderMode() {
	      return this.renderMode;
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      return this.title !== null ? this.title : this.getItem().getTitle();
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      if (main_core.Type.isString(title) || title === null) {
	        this.title = title;
	      }

	      return this;
	    }
	  }, {
	    key: "getSubtitle",
	    value: function getSubtitle() {
	      return this.subtitle !== null ? this.subtitle : this.getItem().getSubtitle();
	    }
	  }, {
	    key: "setSubtitle",
	    value: function setSubtitle(subtitle) {
	      if (main_core.Type.isString(subtitle) || subtitle === null) {
	        this.subtitle = subtitle;
	      }

	      return this;
	    }
	  }, {
	    key: "getSupertitle",
	    value: function getSupertitle() {
	      return this.supertitle !== null ? this.supertitle : this.getItem().getSupertitle();
	    }
	  }, {
	    key: "setSupertitle",
	    value: function setSupertitle(supertitle) {
	      if (main_core.Type.isString(supertitle) || supertitle === null) {
	        this.supertitle = supertitle;
	      }

	      return this;
	    }
	  }, {
	    key: "getAvatar",
	    value: function getAvatar() {
	      return this.avatar !== null ? this.avatar : this.getItem().getAvatar();
	    }
	  }, {
	    key: "setAvatar",
	    value: function setAvatar(avatar) {
	      if (main_core.Type.isString(avatar) || avatar === null) {
	        this.avatar = avatar;
	      }

	      return this;
	    }
	  }, {
	    key: "getTextColor",
	    value: function getTextColor() {
	      return this.textColor !== null ? this.textColor : this.getItem().getTextColor();
	    }
	  }, {
	    key: "setTextColor",
	    value: function setTextColor(textColor) {
	      if (main_core.Type.isString(textColor) || textColor === null) {
	        this.textColor = textColor;
	      }

	      return this;
	    }
	  }, {
	    key: "getCaption",
	    value: function getCaption() {
	      return this.caption !== null ? this.caption : this.getItem().getCaption();
	    }
	  }, {
	    key: "setCaption",
	    value: function setCaption(caption) {
	      if (main_core.Type.isString(caption) || caption === null) {
	        this.caption = caption;
	      }

	      return this;
	    }
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      return this.link !== null ? this.getItem().replaceMacros(this.link) : this.getItem().getLink();
	    }
	  }, {
	    key: "setLink",
	    value: function setLink(link) {
	      if (main_core.Type.isString(link) || link === null) {
	        this.link = link;
	      }

	      return this;
	    }
	  }, {
	    key: "getLinkTitle",
	    value: function getLinkTitle() {
	      return this.linkTitle !== null ? this.linkTitle : this.getItem().getLinkTitle();
	    }
	  }, {
	    key: "setLinkTitle",
	    value: function setLinkTitle(title) {
	      if (main_core.Type.isString(title) || title === null) {
	        this.linkTitle = title;
	      }

	      return this;
	    }
	  }, {
	    key: "getBadges",
	    value: function getBadges() {
	      return this.badges !== null ? this.badges : this.getItem().getBadges();
	    }
	  }, {
	    key: "setBadges",
	    value: function setBadges(badges) {
	      var _this6 = this;

	      if (main_core.Type.isArray(badges)) {
	        this.badges = [];
	        badges.forEach(function (badge) {
	          _this6.badges.push(new ItemBadge(badge));
	        });
	      } else if (badges === null) {
	        this.badges = null;
	      }

	      return this;
	    }
	  }, {
	    key: "getOuterContainer",
	    value: function getOuterContainer() {
	      var _this7 = this;

	      return this.cache.remember('outer-container', function () {
	        var className = '';

	        if (_this7.hasChildren() || _this7.isDynamic()) {
	          className += ' ui-selector-item-box-has-children';

	          if (_this7.getDepthLevel() >= _this7.getTab().getItemMaxDepth()) {
	            className += ' ui-selector-item-box-max-depth';
	          }
	        } else if (_this7.getItem().isSelected()) {
	          className += ' ui-selector-item-box-selected';
	        }

	        if (_this7.isOpen()) {
	          className += ' ui-selector-item-box-open';
	        }

	        return main_core.Tag.render(_templateObject$1(), className, _this7.getContainer(), _this7.getChildrenContainer());
	      });
	    }
	  }, {
	    key: "getChildrenContainer",
	    value: function getChildrenContainer() {
	      var _this8 = this;

	      if (this.isRoot() && this.getTab()) {
	        return this.getTab().getItemsContainer();
	      }

	      return this.cache.remember('children-container', function () {
	        return main_core.Tag.render(_templateObject2(), _this8.handleTransitionEnd.bind(_this8));
	      });
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      var _this9 = this;

	      return this.cache.remember('container', function () {
	        return main_core.Tag.render(_templateObject3(), _this9.handleClick.bind(_this9), _this9.handleMouseEnter.bind(_this9), _this9.handleMouseLeave.bind(_this9), _this9.getAvatarContainer(), _this9.getSupertitleContainer(), _this9.getTitleContainer(), main_core.Type.isArrayFilled(_this9.getBadges()) ? _this9.getBadgeContainer() : '', _this9.getCaptionContainer(), _this9.getSubtitleContainer(), main_core.Type.isStringFilled(_this9.getLink()) ? _this9.getLinkContainer() : '', _this9.getIndicatorContainer());
	      });
	    }
	  }, {
	    key: "getAvatarContainer",
	    value: function getAvatarContainer() {
	      return this.cache.remember('avatar', function () {
	        return main_core.Tag.render(_templateObject4());
	      });
	    }
	  }, {
	    key: "getTitleContainer",
	    value: function getTitleContainer() {
	      return this.cache.remember('title', function () {
	        return main_core.Tag.render(_templateObject5());
	      });
	    }
	  }, {
	    key: "getSubtitleContainer",
	    value: function getSubtitleContainer() {
	      return this.cache.remember('subtitle', function () {
	        return main_core.Tag.render(_templateObject6());
	      });
	    }
	  }, {
	    key: "getSupertitleContainer",
	    value: function getSupertitleContainer() {
	      return this.cache.remember('supertitle', function () {
	        return main_core.Tag.render(_templateObject7());
	      });
	    }
	  }, {
	    key: "getCaptionContainer",
	    value: function getCaptionContainer() {
	      return this.cache.remember('caption', function () {
	        return main_core.Tag.render(_templateObject8());
	      });
	    }
	  }, {
	    key: "getIndicatorContainer",
	    value: function getIndicatorContainer() {
	      return this.cache.remember('indicator', function () {
	        return main_core.Tag.render(_templateObject9());
	      });
	    }
	  }, {
	    key: "getBadgeContainer",
	    value: function getBadgeContainer() {
	      var _this10 = this;

	      return this.cache.remember('badge', function () {
	        var badges = [];

	        _this10.getBadges().forEach(function (badge) {
	          badge.render();
	          badges.push(badge.render());
	        });

	        return main_core.Tag.render(_templateObject10(), badges);
	      });
	    }
	  }, {
	    key: "getLinkContainer",
	    value: function getLinkContainer() {
	      var _this11 = this;

	      return this.cache.remember('link', function () {
	        return main_core.Tag.render(_templateObject11(), _this11.getLink(), _this11.handleLinkClick.bind(_this11), _this11.getLinkTextContainer());
	      });
	    }
	  }, {
	    key: "getLinkTextContainer",
	    value: function getLinkTextContainer() {
	      var _this12 = this;

	      return this.cache.remember('link-text', function () {
	        return main_core.Tag.render(_templateObject12(), _this12.getLinkTitle());
	      });
	    }
	  }, {
	    key: "showLink",
	    value: function showLink() {
	      var _this13 = this;

	      if (main_core.Type.isStringFilled(this.getLink())) {
	        main_core.Dom.addClass(this.getLinkContainer(), 'ui-selector-item-link--show');
	        requestAnimationFrame(function () {
	          requestAnimationFrame(function () {
	            main_core.Dom.addClass(_this13.getLinkContainer(), 'ui-selector-item-link--animate');
	          });
	        });
	      }
	    }
	  }, {
	    key: "hideLink",
	    value: function hideLink() {
	      if (main_core.Type.isStringFilled(this.getLink())) {
	        main_core.Dom.removeClass(this.getLinkContainer(), ['ui-selector-item-link--show', 'ui-selector-item-link--animate']);
	      }
	    }
	  }, {
	    key: "setHighlights",
	    value: function setHighlights(highlights) {
	      this.highlights = highlights;
	    }
	  }, {
	    key: "getHighlights",
	    value: function getHighlights() {
	      return this.highlights;
	    }
	  }, {
	    key: "highlight",
	    value: function highlight() {
	      var _this14 = this;

	      this.getHighlights().forEach(function (matchField) {
	        var field = matchField.getField();
	        var fieldName = field.getName();

	        if (field.isCustom()) {
	          var text = _this14.getItem().getCustomData().get(fieldName);

	          _this14.getSubtitleContainer().innerHTML = Highlighter.mark(text, matchField.getMatches());
	        } else if (field.getName() === 'title') {
	          _this14.getTitleContainer().innerHTML = Highlighter.mark(_this14.getItem().getTitle(), matchField.getMatches());
	        } else if (field.getName() === 'subtitle') {
	          _this14.getSubtitleContainer().innerHTML = Highlighter.mark(_this14.getItem().getSubtitle(), matchField.getMatches());
	        }
	      });
	    }
	  }, {
	    key: "select",
	    value: function select() {
	      if (this.hasChildren() || this.isDynamic()) {
	        return;
	      }

	      main_core.Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-selected');
	    }
	  }, {
	    key: "deselect",
	    value: function deselect() {
	      if (this.hasChildren() || this.isDynamic()) {
	        return;
	      }

	      main_core.Dom.removeClass(this.getOuterContainer(), 'ui-selector-item-box-selected');
	    }
	  }, {
	    key: "focus",
	    value: function focus() {
	      if (this.isFocused()) {
	        return;
	      }

	      this.focused = true;
	      main_core.Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-focused');
	      this.getDialog().emit('ItemNode:onFocus', {
	        node: this
	      });
	    }
	  }, {
	    key: "unfocus",
	    value: function unfocus() {
	      if (!this.isFocused()) {
	        return;
	      }

	      this.focused = false;
	      main_core.Dom.removeClass(this.getOuterContainer(), 'ui-selector-item-box-focused');
	      this.getDialog().emit('ItemNode:onUnfocus', {
	        node: this
	      });
	    }
	  }, {
	    key: "isFocused",
	    value: function isFocused() {
	      return this.focused;
	    }
	  }, {
	    key: "click",
	    value: function click() {
	      if (this.hasChildren() || this.isDynamic()) {
	        if (this.isOpen()) {
	          this.collapse();
	        } else {
	          this.expand();
	        }
	      } else {
	        if (this.getItem().isSelected()) {
	          this.getItem().deselect();

	          if (this.getDialog().shouldHideOnDeselect()) {
	            this.getDialog().hide();
	          }
	        } else {
	          this.getItem().select();

	          if (this.getDialog().shouldHideOnSelect()) {
	            this.getDialog().hide();
	          }
	        }
	      }

	      this.getDialog().focusSearch();
	    }
	  }, {
	    key: "scrollIntoView",
	    value: function scrollIntoView() {
	      var tabContainer = this.getTab().getContainer();
	      var nodeContainer = this.getContainer();
	      var tabRect = main_core.Dom.getPosition(tabContainer);
	      var nodeRect = main_core.Dom.getPosition(nodeContainer);
	      var margin = 9; // 'ui-selector-items' padding - 'ui-selector-item' margin = 10 - 1

	      if (nodeRect.top < tabRect.top) // scroll up
	        {
	          tabContainer.scrollTop -= tabRect.top - nodeRect.top + margin;
	        } else if (nodeRect.bottom > tabRect.bottom) // scroll down
	        {
	          tabContainer.scrollTop += nodeRect.bottom - tabRect.bottom + margin;
	        }
	    }
	  }, {
	    key: "handleClick",
	    value: function handleClick() {
	      this.click();
	    }
	  }, {
	    key: "handleLinkClick",
	    value: function handleLinkClick(event) {
	      this.getDialog().emit('ItemNode:onLinkClick', {
	        node: this,
	        event: event
	      });
	      event.stopPropagation();
	    }
	  }, {
	    key: "handleTransitionEnd",
	    value: function handleTransitionEnd(event) {
	      if (event.propertyName === 'height') {
	        main_core.Dom.style(this.getChildrenContainer(), 'height', null);
	      }
	    }
	  }, {
	    key: "handleMouseEnter",
	    value: function handleMouseEnter() {
	      this.focus();
	      this.showLink();
	    }
	  }, {
	    key: "handleMouseLeave",
	    value: function handleMouseLeave() {
	      this.unfocus();
	      this.hideLink();
	    }
	  }]);
	  return ItemNode;
	}();

	var SearchFieldIndex = /*#__PURE__*/function () {
	  function SearchFieldIndex(field) {
	    var indexes = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
	    babelHelpers.classCallCheck(this, SearchFieldIndex);
	    babelHelpers.defineProperty(this, "field", null);
	    babelHelpers.defineProperty(this, "indexes", []);
	    this.field = field;
	    this.addIndexes(indexes);
	  }

	  babelHelpers.createClass(SearchFieldIndex, [{
	    key: "getField",
	    value: function getField() {
	      return this.field;
	    }
	  }, {
	    key: "getIndexes",
	    value: function getIndexes() {
	      return this.indexes;
	    }
	  }, {
	    key: "addIndex",
	    value: function addIndex(index) {
	      this.getIndexes().push(index);
	    }
	  }, {
	    key: "addIndexes",
	    value: function addIndexes(indexes) {
	      var _this = this;

	      indexes.forEach(function (index) {
	        _this.addIndex(index);
	      });
	    }
	  }]);
	  return SearchFieldIndex;
	}();

	var WordIndex = /*#__PURE__*/function () {
	  function WordIndex(word, startIndex) {
	    babelHelpers.classCallCheck(this, WordIndex);
	    babelHelpers.defineProperty(this, "word", '');
	    babelHelpers.defineProperty(this, "startIndex", 0);
	    this.setWord(word);
	    this.setStartIndex(startIndex);
	  }

	  babelHelpers.createClass(WordIndex, [{
	    key: "getWord",
	    value: function getWord() {
	      return this.word;
	    }
	  }, {
	    key: "setWord",
	    value: function setWord(word) {
	      if (main_core.Type.isStringFilled(word)) {
	        this.word = word;
	      }

	      return this;
	    }
	  }, {
	    key: "getStartIndex",
	    value: function getStartIndex() {
	      return this.startIndex;
	    }
	  }, {
	    key: "setStartIndex",
	    value: function setStartIndex(index) {
	      if (main_core.Type.isNumber(index) && index >= 0) {
	        this.startIndex = index;
	      }

	      return this;
	    }
	  }]);
	  return WordIndex;
	}();

	/**
	 * @license
	 * Lodash <https://lodash.com/>
	 * Copyright OpenJS Foundation and other contributors <https://openjsf.org/>
	 * Released under MIT license <https://lodash.com/license>
	 * Based on Underscore.js 1.8.3 <http://underscorejs.org/LICENSE>
	 * Copyright Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
	 */

	/** Used to compose unicode character classes. */
	var rsAstralRange = "\\ud800-\\udfff";
	var rsComboMarksRange = "\\u0300-\\u036f";
	var reComboHalfMarksRange = "\\ufe20-\\ufe2f";
	var rsComboSymbolsRange = "\\u20d0-\\u20ff";
	var rsComboMarksExtendedRange = "\\u1ab0-\\u1aff";
	var rsComboMarksSupplementRange = "\\u1dc0-\\u1dff";
	var rsComboRange = rsComboMarksRange + reComboHalfMarksRange + rsComboSymbolsRange + rsComboMarksExtendedRange + rsComboMarksSupplementRange;
	var rsDingbatRange = "\\u2700-\\u27bf";
	var rsLowerRange = 'a-z\\xdf-\\xf6\\xf8-\\xff';
	var rsMathOpRange = '\\xac\\xb1\\xd7\\xf7';
	var rsNonCharRange = '\\x00-\\x2f\\x3a-\\x40\\x5b-\\x60\\x7b-\\xbf';
	var rsPunctuationRange = "\\u2000-\\u206f";
	var rsSpaceRange = " \\t\\x0b\\f\\xa0\\ufeff\\n\\r\\u2028\\u2029\\u1680\\u180e\\u2000\\u2001\\u2002\\u2003\\u2004\\u2005\\u2006\\u2007\\u2008\\u2009\\u200a\\u202f\\u205f\\u3000";
	var rsUpperRange = 'A-Z\\xc0-\\xd6\\xd8-\\xde';
	var rsVarRange = "\\ufe0e\\ufe0f";
	var rsBreakRange = rsMathOpRange + rsNonCharRange + rsPunctuationRange + rsSpaceRange;
	/** Used to compose unicode capture groups. */

	var rsApos = "['\u2019]";
	var rsBreak = "[".concat(rsBreakRange, "]");
	var rsCombo = "[".concat(rsComboRange, "]");
	var rsDigit = '\\d';
	var rsDingbat = "[".concat(rsDingbatRange, "]");
	var rsLower = "[".concat(rsLowerRange, "]");
	var rsMisc = "[^".concat(rsAstralRange).concat(rsBreakRange + rsDigit + rsDingbatRange + rsLowerRange + rsUpperRange, "]");
	var rsFitz = "\\ud83c[\\udffb-\\udfff]";
	var rsModifier = "(?:".concat(rsCombo, "|").concat(rsFitz, ")");
	var rsNonAstral = "[^".concat(rsAstralRange, "]");
	var rsRegional = "(?:\\ud83c[\\udde6-\\uddff]){2}";
	var rsSurrPair = "[\\ud800-\\udbff][\\udc00-\\udfff]";
	var rsUpper = "[".concat(rsUpperRange, "]");
	var rsZWJ = "\\u200d";
	/** Used to compose unicode regexes. */

	var rsMiscLower = "(?:".concat(rsLower, "|").concat(rsMisc, ")");
	var rsMiscUpper = "(?:".concat(rsUpper, "|").concat(rsMisc, ")");
	var rsOptContrLower = "(?:".concat(rsApos, "(?:d|ll|m|re|s|t|ve))?");
	var rsOptContrUpper = "(?:".concat(rsApos, "(?:D|LL|M|RE|S|T|VE))?");
	var reOptMod = "".concat(rsModifier, "?");
	var rsOptVar = "[".concat(rsVarRange, "]?");
	var rsOptJoin = "(?:".concat(rsZWJ, "(?:").concat([rsNonAstral, rsRegional, rsSurrPair].join('|'), ")").concat(rsOptVar + reOptMod, ")*");
	var rsOrdLower = '\\d*(?:1st|2nd|3rd|(?![123])\\dth)(?=\\b|[A-Z_])';
	var rsOrdUpper = '\\d*(?:1ST|2ND|3RD|(?![123])\\dTH)(?=\\b|[a-z_])';
	var rsSeq = rsOptVar + reOptMod + rsOptJoin;
	var rsEmoji = "(?:".concat([rsDingbat, rsRegional, rsSurrPair].join('|'), ")").concat(rsSeq);
	var unicodeWordsRegExp = new RegExp(["".concat(rsUpper, "?").concat(rsLower, "+").concat(rsOptContrLower, "(?=").concat([rsBreak, rsUpper, '$'].join('|'), ")"), "".concat(rsMiscUpper, "+").concat(rsOptContrUpper, "(?=").concat([rsBreak, rsUpper + rsMiscLower, '$'].join('|'), ")"), "".concat(rsUpper, "?").concat(rsMiscLower, "+").concat(rsOptContrLower), "".concat(rsUpper, "+").concat(rsOptContrUpper), rsOrdUpper, rsOrdLower, "".concat(rsDigit, "+"), rsEmoji].join('|'), 'g');

	var asciiWordRegExp = /[^\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f]+/g;
	var hasUnicodeWordRegExp = /[a-z][A-Z]|[A-Z]{2}[a-z]|[0-9][a-zA-Z]|[a-zA-Z][0-9]|[^a-zA-Z0-9 ]/;

	var SearchIndex = /*#__PURE__*/function () {
	  function SearchIndex() {
	    babelHelpers.classCallCheck(this, SearchIndex);
	    babelHelpers.defineProperty(this, "indexes", []);
	  }

	  babelHelpers.createClass(SearchIndex, [{
	    key: "addIndex",
	    value: function addIndex(fieldIndex) {
	      if (main_core.Type.isObject(fieldIndex)) {
	        this.getIndexes().push(fieldIndex);
	      }
	    }
	  }, {
	    key: "getIndexes",
	    value: function getIndexes() {
	      return this.indexes;
	    }
	  }], [{
	    key: "create",
	    value: function create(item) {
	      var _this = this;

	      var index = new SearchIndex();
	      var entity = item.getEntity();

	      if (!item.isSearchable() || !entity.isSearchable() || item.isHidden()) {
	        return index;
	      }

	      var searchFields = entity.getSearchFields();
	      searchFields.forEach(function (field) {
	        if (!field.isSeachable()) {
	          return;
	        }

	        if (field.isSystem()) {
	          if (field.getName() === 'title') {
	            index.addIndex(_this.createIndex(field, item.getTitle()));
	          } else if (field.getName() === 'subtitle') {
	            index.addIndex(_this.createIndex(field, item.getSubtitle()));
	          }
	        } else {
	          var customData = item.getCustomData().get(field.getName());

	          if (!main_core.Type.isUndefined(customData)) {
	            index.addIndex(_this.createIndex(field, customData));
	          }
	        }
	      });
	      return index;
	    }
	  }, {
	    key: "createIndex",
	    value: function createIndex(field, text) {
	      if (!main_core.Type.isStringFilled(text)) {
	        return null;
	      }

	      var index = null;

	      if (field.getType() === 'string') {
	        var wordIndexes = this.splitText(text);

	        if (main_core.Type.isArrayFilled(wordIndexes)) {
	          // "GoPro111 Leto15"
	          // [go, pro, 111, leto, 15] + [gopro111, leto15]
	          this.fillComplexWords(wordIndexes);
	          index = new SearchFieldIndex(field, wordIndexes);
	        }
	      } else if (field.getType() === 'email') {
	        var position = text.indexOf('@');

	        if (position !== -1) {
	          index = new SearchFieldIndex(field, [new WordIndex(text.toLowerCase(), 0), new WordIndex(text.substr(position + 1).toLowerCase(), position + 1)]);
	        }
	      }

	      return index;
	    }
	  }, {
	    key: "splitText",
	    value: function splitText(text) {
	      if (!main_core.Type.isStringFilled(text)) {
	        return [];
	      }

	      return this.hasUnicodeWord(text) ? this.splitUnicodeText(text) : this.splitAsciiText(text);
	    }
	  }, {
	    key: "splitUnicodeText",
	    value: function splitUnicodeText(text) {
	      return this.splitTextInternal(text, unicodeWordsRegExp);
	    }
	  }, {
	    key: "splitAsciiText",
	    value: function splitAsciiText(text) {
	      return this.splitTextInternal(text, asciiWordRegExp);
	    }
	  }, {
	    key: "hasUnicodeWord",
	    value: function hasUnicodeWord(text) {
	      return hasUnicodeWordRegExp.test(text);
	    }
	  }, {
	    key: "splitTextInternal",
	    value: function splitTextInternal(text, regExp) {
	      var match;
	      var result = [];

	      while ((match = regExp.exec(text)) !== null) {
	        if (match.index === regExp.lastIndex) {
	          regExp.lastIndex++;
	        }

	        result.push(new WordIndex(match[0].toLowerCase(), match.index));
	      }

	      return result;
	    }
	    /**
	     *  @private
	     */

	  }, {
	    key: "fillComplexWords",
	    value: function fillComplexWords(indexes) {
	      if (indexes.length < 2) {
	        return;
	      }

	      var complexWord = null;
	      var startIndex = null;
	      indexes.forEach(function (currentIndex, currentArrayIndex) {
	        var nextIndex = indexes[currentArrayIndex + 1];

	        if (nextIndex) {
	          var sameWord = currentIndex.getStartIndex() + currentIndex.getWord().length === nextIndex.getStartIndex();

	          if (sameWord) {
	            if (complexWord === null) {
	              complexWord = currentIndex.getWord();
	              startIndex = currentIndex.getStartIndex();
	            }

	            complexWord += nextIndex.getWord();
	          } else if (complexWord !== null) {
	            indexes.push(new WordIndex(complexWord, startIndex));
	            complexWord = null;
	            startIndex = null;
	          }
	        } else if (complexWord !== null) {
	          indexes.push(new WordIndex(complexWord, startIndex));
	          complexWord = null;
	          startIndex = null;
	        }
	      });
	    }
	  }]);
	  return SearchIndex;
	}();

	var SearchField = /*#__PURE__*/function () {
	  function SearchField(fieldOptions) {
	    babelHelpers.classCallCheck(this, SearchField);
	    babelHelpers.defineProperty(this, "name", null);
	    babelHelpers.defineProperty(this, "type", 'string');
	    babelHelpers.defineProperty(this, "searchable", true);
	    babelHelpers.defineProperty(this, "system", false);
	    var options = main_core.Type.isPlainObject(fieldOptions) ? fieldOptions : {};

	    if (!main_core.Type.isStringFilled(options.name)) {
	      throw new Error('EntitySelector.SearchField: "name" parameter is required.');
	    }

	    this.name = options.name;
	    this.setType(options.type);
	    this.setSystem(options.system);
	    this.setSeachable(options.searchable);
	  }

	  babelHelpers.createClass(SearchField, [{
	    key: "getName",
	    value: function getName() {
	      return this.name;
	    }
	  }, {
	    key: "getType",
	    value: function getType() {
	      return this.type;
	    }
	  }, {
	    key: "setType",
	    value: function setType(type) {
	      if (main_core.Type.isStringFilled(type)) {
	        this.type = type;
	      }
	    }
	  }, {
	    key: "setSeachable",
	    value: function setSeachable(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.searchable = flag;
	      }

	      return this;
	    }
	  }, {
	    key: "isSeachable",
	    value: function isSeachable() {
	      return this.searchable;
	    }
	  }, {
	    key: "setSystem",
	    value: function setSystem(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.system = flag;
	      }

	      return this;
	    }
	  }, {
	    key: "isCustom",
	    value: function isCustom() {
	      return !this.isSystem();
	    }
	  }, {
	    key: "isSystem",
	    value: function isSystem() {
	      return this.system;
	    }
	  }]);
	  return SearchField;
	}();

	/**
	 * @memberof BX.UI.EntitySelector
	 */
	var Entity = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Entity, _EventEmitter);

	  function Entity(entityOptions) {
	    var _this;

	    babelHelpers.classCallCheck(this, Entity);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Entity).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "id", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "options", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "searchable", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "searchFields", new ItemCollection());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "dynamicLoad", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "dynamicSearch", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "searchCacheLimits", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "itemOptions", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagOptions", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "badgeOptions", []);

	    _this.setEventNamespace('BX.UI.EntitySelector.Entity');

	    var options = main_core.Type.isPlainObject(entityOptions) ? entityOptions : {};

	    if (!main_core.Type.isStringFilled(options.id)) {
	      throw new Error('EntitySelector.Entity: "id" parameter is required.');
	    }

	    var defaultOptions = _this.constructor.getEntityOptions(options.id) || {};
	    options = main_core.Runtime.merge({}, defaultOptions, options);
	    _this.id = options.id;
	    _this.options = main_core.Type.isPlainObject(options.options) ? options.options : {};
	    _this.itemOptions = main_core.Type.isPlainObject(options.itemOptions) ? options.itemOptions : {};
	    _this.tagOptions = main_core.Type.isPlainObject(options.tagOptions) ? options.tagOptions : {};
	    _this.badgeOptions = main_core.Type.isArray(options.badgeOptions) ? options.badgeOptions : [];

	    _this.setSeachable(options.searchable);

	    _this.setDynamicLoad(options.dynamicLoad);

	    _this.setDynamicSearch(options.dynamicSearch);

	    _this.setSearchFields(options.searchFields);

	    _this.setSearchCacheLimits(options.searchCacheLimits);

	    return _this;
	  }

	  babelHelpers.createClass(Entity, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getOptions",
	    value: function getOptions() {
	      return this.options;
	    }
	  }, {
	    key: "getItemOptions",
	    value: function getItemOptions() {
	      return this.itemOptions;
	    }
	  }, {
	    key: "getItemOption",
	    value: function getItemOption(item, option) {
	      var entityType = item.getEntityType();

	      if (this.itemOptions[entityType] && !main_core.Type.isUndefined(this.itemOptions[entityType][option])) {
	        return this.itemOptions[entityType][option];
	      } else if (this.itemOptions['default'] && !main_core.Type.isUndefined(this.itemOptions['default'][option])) {
	        return this.itemOptions['default'][option];
	      }

	      return null;
	    }
	  }, {
	    key: "getTagOptions",
	    value: function getTagOptions() {
	      return this.tagOptions;
	    }
	  }, {
	    key: "getTagOption",
	    value: function getTagOption(item, option) {
	      var entityType = item.getEntityType();

	      if (this.tagOptions[entityType] && !main_core.Type.isUndefined(this.tagOptions[entityType][option])) {
	        return this.tagOptions[entityType][option];
	      } else if (this.tagOptions['default'] && !main_core.Type.isUndefined(this.tagOptions['default'][option])) {
	        return this.tagOptions['default'][option];
	      }

	      return null;
	    }
	  }, {
	    key: "getBadges",
	    value: function getBadges(item) {
	      var entityTypeBadges = this.getItemOption(item, 'badges') || [];
	      var badges = babelHelpers.toConsumableArray(entityTypeBadges);
	      this.badgeOptions.forEach(function (badge) {
	        if (main_core.Type.isPlainObject(badge.conditions)) {
	          for (var condition in badge.conditions) {
	            if (item.getCustomData().get(condition) !== badge.conditions[condition]) {
	              return;
	            }
	          }

	          badges.push(badge);
	        }
	      });
	      return badges;
	    }
	  }, {
	    key: "isSearchable",
	    value: function isSearchable() {
	      return this.searchable;
	    }
	  }, {
	    key: "setSeachable",
	    value: function setSeachable(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.searchable = flag;
	      }

	      return this;
	    }
	  }, {
	    key: "getSearchFields",
	    value: function getSearchFields() {
	      return this.searchFields;
	    }
	  }, {
	    key: "setSearchFields",
	    value: function setSearchFields(searchFields) {
	      var _this2 = this;

	      this.searchFields.clear(); // Default Search Fields

	      var titleField = new SearchField({
	        name: 'title',
	        searchable: true,
	        system: true,
	        type: 'string'
	      });
	      var subtitleField = new SearchField({
	        name: 'subtitle',
	        searchable: true,
	        system: true,
	        type: 'string'
	      });
	      this.searchFields.add(titleField);
	      this.searchFields.add(subtitleField); // Custom Search Fields

	      var customFields = main_core.Type.isArray(searchFields) ? searchFields : [];
	      customFields.forEach(function (fieldOptions) {
	        var field = new SearchField(fieldOptions);

	        if (field.isSystem()) // Entity can override default fields.
	          {
	            // delete a default title field
	            if (field.getName() === 'title') {
	              _this2.searchFields.delete(titleField);
	            } else if (field.getName() === 'subtitle') {
	              _this2.searchFields.delete(subtitleField);
	            }
	          }

	        _this2.searchFields.add(field);
	      });
	    }
	  }, {
	    key: "setSearchCacheLimits",
	    value: function setSearchCacheLimits(limits) {
	      var _this3 = this;

	      if (main_core.Type.isArrayFilled(limits)) {
	        limits.forEach(function (limit) {
	          if (main_core.Type.isStringFilled(limit)) {
	            _this3.searchCacheLimits.push(new RegExp(limit, 'i'));
	          }
	        });
	      }
	    }
	  }, {
	    key: "getSearchCacheLimits",
	    value: function getSearchCacheLimits() {
	      return this.searchCacheLimits;
	    }
	  }, {
	    key: "hasDynamicLoad",
	    value: function hasDynamicLoad() {
	      return this.dynamicLoad;
	    }
	  }, {
	    key: "setDynamicLoad",
	    value: function setDynamicLoad(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.dynamicLoad = flag;
	      }

	      return this;
	    }
	  }, {
	    key: "hasDynamicSearch",
	    value: function hasDynamicSearch() {
	      return this.dynamicSearch;
	    }
	  }, {
	    key: "setDynamicSearch",
	    value: function setDynamicSearch(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.dynamicSearch = flag;
	      }

	      return this;
	    }
	  }, {
	    key: "toJSON",
	    value: function toJSON() {
	      return {
	        id: this.getId(),
	        options: this.getOptions(),
	        searchable: this.isSearchable(),
	        dynamicLoad: this.hasDynamicLoad(),
	        dynamicSearch: this.hasDynamicSearch()
	      };
	    }
	  }], [{
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      var _this4 = this;

	      if (this.defaultOptions === null) {
	        this.defaultOptions = {};
	        this.getExtensions().forEach(function (extension) {
	          var settings = main_core.Extension.getSettings(extension);
	          var entities = settings.get('entities', []);
	          entities.forEach(function (entity) {
	            if (main_core.Type.isStringFilled(entity.id) && main_core.Type.isPlainObject(entity.options)) {
	              _this4.defaultOptions[entity.id] = JSON.parse(JSON.stringify(entity.options)); // clone
	            }
	          });
	        });
	      }

	      return this.defaultOptions;
	    }
	  }, {
	    key: "getExtensions",
	    value: function getExtensions() {
	      if (this.extensions === null) {
	        var settings = main_core.Extension.getSettings('ui.entity-selector');
	        this.extensions = settings.get('extensions', []);
	      }

	      return this.extensions;
	    }
	  }, {
	    key: "getEntityOptions",
	    value: function getEntityOptions(entityId) {
	      return this.getDefaultOptions()[entityId] || null;
	    }
	  }, {
	    key: "getItemOptions",
	    value: function getItemOptions(entityId, entityType) {
	      if (!main_core.Type.isStringFilled(entityId)) {
	        return null;
	      }

	      var options = this.getEntityOptions(entityId);
	      var itemOptions = options && options['itemOptions'] ? options['itemOptions'] : null;

	      if (main_core.Type.isUndefined(entityType)) {
	        return itemOptions;
	      } else {
	        return itemOptions && itemOptions[entityType] ? itemOptions[entityType] : null;
	      }
	    }
	  }, {
	    key: "getItemOption",
	    value: function getItemOption(entityId, entityType, option) {
	      if (!main_core.Type.isStringFilled(entityType) || !main_core.Type.isStringFilled(option)) {
	        return null;
	      }

	      var options = this.getItemOptions(entityId, entityType);
	      return options && !main_core.Type.isUndefined(options[option]) ? options[option] : null;
	    }
	  }, {
	    key: "getTagOptions",
	    value: function getTagOptions(entityId, entityType) {
	      if (!main_core.Type.isStringFilled(entityId)) {
	        return null;
	      }

	      var options = this.getEntityOptions(entityId);
	      var tagOptions = options && options['tagOptions'] ? options['tagOptions'] : null;

	      if (main_core.Type.isUndefined(entityType)) {
	        return tagOptions;
	      } else {
	        return tagOptions && !main_core.Type.isUndefined(tagOptions[entityType]) ? tagOptions[entityType] : null;
	      }
	    }
	  }, {
	    key: "getTagOption",
	    value: function getTagOption(entityId, entityType, option) {
	      if (!main_core.Type.isStringFilled(entityType) || !main_core.Type.isStringFilled(option)) {
	        return null;
	      }

	      var options = this.getTagOptions(entityId, entityType);
	      return options && options[option] ? options[option] : null;
	    }
	  }]);
	  return Entity;
	}(main_core_events.EventEmitter);

	babelHelpers.defineProperty(Entity, "extensions", null);
	babelHelpers.defineProperty(Entity, "defaultOptions", null);

	function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	/**
	 * @memberof BX.UI.EntitySelector
	 * @package ui.entity-selector
	 */
	var Item = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Item, _EventEmitter);

	  function Item(itemOptions) {
	    var _this;

	    babelHelpers.classCallCheck(this, Item);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Item).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "id", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "entityId", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "entityType", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "title", '');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "subtitle", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "caption", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "supertitle", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "avatar", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "textColor", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "link", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "linkTitle", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagOptions", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "badges", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "dialog", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "nodes", new Set());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "selected", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "searchable", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "saveable", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "deselectable", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "hidden", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "searchIndex", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "customData", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "sort", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "contextSort", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "globalSort", null);

	    _this.setEventNamespace('BX.UI.EntitySelector.Item');

	    var options = main_core.Type.isPlainObject(itemOptions) ? itemOptions : {};

	    if (!main_core.Type.isStringFilled(options.id) && !main_core.Type.isNumber(options.id)) {
	      throw new Error('EntitySelector.Item: "id" parameter is required.');
	    }

	    if (!main_core.Type.isStringFilled(options.entityId)) {
	      throw new Error('EntitySelector.Item: "entityId" parameter is required.');
	    }

	    _this.id = options.id;
	    _this.entityId = options.entityId;
	    _this.entityType = main_core.Type.isStringFilled(options.entityType) ? options.entityType : 'default';
	    _this.selected = main_core.Type.isBoolean(options.selected) ? options.selected : false;
	    _this.customData = main_core.Type.isPlainObject(options.customData) ? new Map(Object.entries(options.customData)) : new Map();
	    _this.tagOptions = main_core.Type.isPlainObject(options.tagOptions) ? new Map(Object.entries(options.tagOptions)) : new Map();

	    _this.setTitle(options.title);

	    _this.setSubtitle(options.subtitle);

	    _this.setSupertitle(options.supertitle);

	    _this.setCaption(options.caption);

	    _this.setAvatar(options.avatar);

	    _this.setTextColor(options.textColor);

	    _this.setLink(options.link);

	    _this.setLinkTitle(options.linkTitle);

	    _this.setBadges(options.badges);

	    _this.setSearchable(options.searchable);

	    _this.setSaveable(options.saveable);

	    _this.setDeselectable(options.deselectable);

	    _this.setHidden(options.hidden);

	    _this.setContextSort(options.contextSort);

	    _this.setGlobalSort(options.globalSort);

	    _this.setSort(options.sort);

	    return _this;
	  }

	  babelHelpers.createClass(Item, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getEntityId",
	    value: function getEntityId() {
	      return this.entityId;
	    }
	  }, {
	    key: "getEntity",
	    value: function getEntity() {
	      var entity = this.getDialog().getEntity(this.getEntityId());

	      if (entity === null) {
	        entity = new Entity({
	          id: this.getEntityId()
	        });
	        this.getDialog().addEntity(entity);
	      }

	      return entity;
	    }
	  }, {
	    key: "getEntityType",
	    value: function getEntityType() {
	      return this.entityType;
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      return this.title;
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      if (main_core.Type.isStringFilled(title)) {
	        this.title = title;

	        if (this.isRendered()) {
	          var _iterator = _createForOfIteratorHelper(this.getNodes()),
	              _step;

	          try {
	            for (_iterator.s(); !(_step = _iterator.n()).done;) {
	              var node = _step.value;
	              node.render();
	            }
	          } catch (err) {
	            _iterator.e(err);
	          } finally {
	            _iterator.f();
	          }
	        }
	      }
	    }
	  }, {
	    key: "getSubtitle",
	    value: function getSubtitle() {
	      return this.subtitle !== null ? this.subtitle : this.getEntity().getItemOption(this, 'subtitle');
	    }
	  }, {
	    key: "setSubtitle",
	    value: function setSubtitle(subtitle) {
	      if (main_core.Type.isString(subtitle) || subtitle === null) {
	        this.subtitle = subtitle;

	        if (this.isRendered()) {
	          var _iterator2 = _createForOfIteratorHelper(this.getNodes()),
	              _step2;

	          try {
	            for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	              var node = _step2.value;
	              node.render();
	            }
	          } catch (err) {
	            _iterator2.e(err);
	          } finally {
	            _iterator2.f();
	          }
	        }
	      }
	    }
	  }, {
	    key: "getSupertitle",
	    value: function getSupertitle() {
	      return this.supertitle !== null ? this.supertitle : this.getEntity().getItemOption(this, 'supertitle');
	    }
	  }, {
	    key: "setSupertitle",
	    value: function setSupertitle(supertitle) {
	      if (main_core.Type.isString(supertitle) || supertitle === null) {
	        this.supertitle = supertitle;

	        if (this.isRendered()) {
	          var _iterator3 = _createForOfIteratorHelper(this.getNodes()),
	              _step3;

	          try {
	            for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
	              var node = _step3.value;
	              node.render();
	            }
	          } catch (err) {
	            _iterator3.e(err);
	          } finally {
	            _iterator3.f();
	          }
	        }
	      }
	    }
	  }, {
	    key: "getAvatar",
	    value: function getAvatar() {
	      return this.avatar !== null ? this.avatar : this.getEntity().getItemOption(this, 'avatar');
	    }
	  }, {
	    key: "setAvatar",
	    value: function setAvatar(avatar) {
	      if (main_core.Type.isString(avatar) || avatar === null) {
	        this.avatar = avatar;

	        if (this.isRendered()) {
	          var _iterator4 = _createForOfIteratorHelper(this.getNodes()),
	              _step4;

	          try {
	            for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
	              var node = _step4.value;
	              node.render();
	            }
	          } catch (err) {
	            _iterator4.e(err);
	          } finally {
	            _iterator4.f();
	          }
	        }
	      }
	    }
	  }, {
	    key: "getTextColor",
	    value: function getTextColor() {
	      return this.textColor !== null ? this.textColor : this.getEntity().getItemOption(this, 'textColor');
	    }
	  }, {
	    key: "setTextColor",
	    value: function setTextColor(textColor) {
	      if (main_core.Type.isString(textColor) || textColor === null) {
	        this.textColor = textColor;

	        if (this.isRendered()) {
	          var _iterator5 = _createForOfIteratorHelper(this.getNodes()),
	              _step5;

	          try {
	            for (_iterator5.s(); !(_step5 = _iterator5.n()).done;) {
	              var node = _step5.value;
	              node.render();
	            }
	          } catch (err) {
	            _iterator5.e(err);
	          } finally {
	            _iterator5.f();
	          }
	        }
	      }
	    }
	  }, {
	    key: "getCaption",
	    value: function getCaption() {
	      return this.caption !== null ? this.caption : this.getEntity().getItemOption(this, 'caption');
	    }
	  }, {
	    key: "setCaption",
	    value: function setCaption(caption) {
	      if (main_core.Type.isString(caption) || caption === null) {
	        this.caption = caption;

	        if (this.isRendered()) {
	          var _iterator6 = _createForOfIteratorHelper(this.getNodes()),
	              _step6;

	          try {
	            for (_iterator6.s(); !(_step6 = _iterator6.n()).done;) {
	              var node = _step6.value;
	              node.render();
	            }
	          } catch (err) {
	            _iterator6.e(err);
	          } finally {
	            _iterator6.f();
	          }
	        }
	      }
	    }
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      var link = this.link !== null ? this.link : this.getEntity().getItemOption(this, 'link');
	      return this.replaceMacros(link);
	    }
	  }, {
	    key: "setLink",
	    value: function setLink(link) {
	      if (main_core.Type.isString(link) || link === null) {
	        this.link = link;
	      }
	    }
	  }, {
	    key: "getLinkTitle",
	    value: function getLinkTitle() {
	      if (this.linkTitle !== null) {
	        return this.linkTitle;
	      }

	      var linkTitle = this.getEntity().getItemOption(this, 'linkTitle');
	      return linkTitle !== null ? linkTitle : main_core.Loc.getMessage('UI_SELECTOR_ITEM_LINK_TITLE');
	    }
	  }, {
	    key: "setLinkTitle",
	    value: function setLinkTitle(linkTitle) {
	      if (main_core.Type.isString(linkTitle) || linkTitle === null) {
	        this.linkTitle = linkTitle;
	      }
	    }
	  }, {
	    key: "getBadges",
	    value: function getBadges() {
	      if (this.badges !== null) {
	        return this.badges;
	      }

	      var badges = this.getEntity().getBadges(this);

	      if (main_core.Type.isArray(badges)) {
	        this.setBadges(badges);
	      } else {
	        this.badges = [];
	      }

	      return this.badges;
	    }
	  }, {
	    key: "setBadges",
	    value: function setBadges(badges) {
	      var _this2 = this;

	      if (main_core.Type.isArray(badges)) {
	        this.badges = [];
	        badges.forEach(function (badge) {
	          _this2.badges.push(new ItemBadge(badge));
	        });
	      } else if (badges === null) {
	        this.badges = null;
	      }
	    }
	    /**
	     * @internal
	     */

	  }, {
	    key: "setDialog",
	    value: function setDialog(dialog) {
	      this.dialog = dialog;
	    }
	  }, {
	    key: "getDialog",
	    value: function getDialog() {
	      return this.dialog;
	    }
	  }, {
	    key: "createNode",
	    value: function createNode(nodeOptions) {
	      var itemNode = new ItemNode(this, nodeOptions);
	      this.nodes.add(itemNode);
	      return itemNode;
	    }
	  }, {
	    key: "removeNode",
	    value: function removeNode(node) {
	      this.nodes.delete(node);
	    }
	  }, {
	    key: "getNodes",
	    value: function getNodes() {
	      return this.nodes;
	    }
	  }, {
	    key: "select",
	    value: function select() {
	      var preselectedMode = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

	      if (this.selected) {
	        return;
	      }

	      var dialog = this.getDialog();
	      var emitEvents = dialog && !preselectedMode;

	      if (emitEvents) {
	        var event = new main_core_events.BaseEvent({
	          data: {
	            item: this
	          }
	        });
	        dialog.emit('Item:onBeforeSelect', event);

	        if (event.isDefaultPrevented()) {
	          return;
	        }
	      }

	      this.selected = true;

	      if (dialog) {
	        dialog.handleItemSelect(this, !preselectedMode);
	      }

	      if (this.isRendered()) {
	        this.getNodes().forEach(function (node) {
	          node.select();
	        });
	      }

	      if (emitEvents) {
	        dialog.emit('Item:onSelect', {
	          item: this
	        });
	        dialog.saveRecentItem(this);
	      }
	    }
	  }, {
	    key: "deselect",
	    value: function deselect() {
	      if (!this.selected || !this.isDeselectable()) {
	        return;
	      }

	      var dialog = this.getDialog();

	      if (dialog) {
	        var event = new main_core_events.BaseEvent({
	          data: {
	            item: this
	          }
	        });
	        dialog.emit('Item:onBeforeDeselect', event);

	        if (event.isDefaultPrevented()) {
	          return;
	        }
	      }

	      this.selected = false;

	      if (dialog) {
	        dialog.handleItemDeselect(this);
	      }

	      if (this.isRendered()) {
	        this.getNodes().forEach(function (node) {
	          node.deselect();
	        });
	      }

	      if (dialog) {
	        dialog.emit('Item:onDeselect', {
	          item: this
	        });

	        if (dialog.getTagSelector()) {
	          dialog.getTagSelector().removeTag({
	            id: this.getId(),
	            entityId: this.getEntityId()
	          });
	        }
	      }
	    }
	  }, {
	    key: "isSelected",
	    value: function isSelected() {
	      return this.selected;
	    }
	  }, {
	    key: "setSearchable",
	    value: function setSearchable(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.searchable = flag;
	      }
	    }
	  }, {
	    key: "isSearchable",
	    value: function isSearchable() {
	      return this.searchable;
	    }
	  }, {
	    key: "setSaveable",
	    value: function setSaveable(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.saveable = flag;
	      }
	    }
	  }, {
	    key: "isSaveable",
	    value: function isSaveable() {
	      return this.saveable;
	    }
	  }, {
	    key: "setDeselectable",
	    value: function setDeselectable(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.deselectable = flag;

	        if (this.getDialog() && this.getDialog().getTagSelector()) {
	          var tag = this.getDialog().getTagSelector().getTag({
	            id: this.getId(),
	            entityId: this.getEntityId()
	          });

	          if (tag) {
	            tag.setDeselectable(flag);
	          }
	        }
	      }
	    }
	  }, {
	    key: "isDeselectable",
	    value: function isDeselectable() {
	      return this.deselectable;
	    }
	  }, {
	    key: "setHidden",
	    value: function setHidden(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.hidden = flag;
	      }
	    }
	  }, {
	    key: "isHidden",
	    value: function isHidden() {
	      return this.hidden;
	    }
	  }, {
	    key: "setContextSort",
	    value: function setContextSort(sort) {
	      if (main_core.Type.isNumber(sort) || sort === null) {
	        this.contextSort = sort;
	      }
	    }
	  }, {
	    key: "getContextSort",
	    value: function getContextSort() {
	      return this.contextSort;
	    }
	  }, {
	    key: "setGlobalSort",
	    value: function setGlobalSort(sort) {
	      if (main_core.Type.isNumber(sort) || sort === null) {
	        this.globalSort = sort;
	      }
	    }
	  }, {
	    key: "getGlobalSort",
	    value: function getGlobalSort() {
	      return this.globalSort;
	    }
	  }, {
	    key: "setSort",
	    value: function setSort(sort) {
	      if (main_core.Type.isNumber(sort) || sort === null) {
	        this.sort = sort;
	      }
	    }
	  }, {
	    key: "getSort",
	    value: function getSort() {
	      return this.sort;
	    }
	  }, {
	    key: "getSearchIndex",
	    value: function getSearchIndex() {
	      if (this.searchIndex === null) {
	        this.searchIndex = SearchIndex.create(this);
	      }

	      return this.searchIndex;
	    }
	  }, {
	    key: "resetSearchIndex",
	    value: function resetSearchIndex() {
	      this.searchIndex = null;
	    }
	  }, {
	    key: "getCustomData",
	    value: function getCustomData() {
	      return this.customData;
	    }
	  }, {
	    key: "isRendered",
	    value: function isRendered() {
	      return this.getDialog() && this.getDialog().isRendered();
	    }
	  }, {
	    key: "getTagOptions",
	    value: function getTagOptions() {
	      return this.tagOptions;
	    }
	  }, {
	    key: "getTagOption",
	    value: function getTagOption(option) {
	      var useEntityOptions = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
	      var value = this.getTagOptions().get(option);

	      if (!main_core.Type.isUndefined(value)) {
	        return value;
	      } else if (useEntityOptions !== false) {
	        return this.getEntity().getTagOption(this, option);
	      }

	      return null;
	    }
	  }, {
	    key: "getTagGlobalOption",
	    value: function getTagGlobalOption(propName) {
	      var value = null;

	      if (this.getTagOption(propName, false) !== null) {
	        value = this.getTagOption(propName);
	      } else if (this[propName] !== null) {
	        value = this[propName];
	      } else if (this.getEntity().getTagOption(this, propName) !== null) {
	        value = this.getEntity().getTagOption(this, propName);
	      } else {
	        value = this.getEntity().getItemOption(this, propName);
	      }

	      return value;
	    }
	  }, {
	    key: "getTagAvatar",
	    value: function getTagAvatar() {
	      return this.getTagGlobalOption('avatar');
	    }
	  }, {
	    key: "getTagLink",
	    value: function getTagLink() {
	      return this.replaceMacros(this.getTagGlobalOption('link'));
	    }
	  }, {
	    key: "replaceMacros",
	    value: function replaceMacros(str) {
	      if (!main_core.Type.isStringFilled(str)) {
	        return str;
	      }

	      return str.replace(/#id#/i, this.getId()).replace(/#element_id#/i, this.getId());
	    }
	  }, {
	    key: "createTag",
	    value: function createTag() {
	      return {
	        id: this.getId(),
	        entityId: this.getEntityId(),
	        title: this.getTagOption('title', false) || this.getTitle(),
	        deselectable: this.isDeselectable(),
	        customData: this.getCustomData(),
	        avatar: this.getTagAvatar(),
	        link: this.getTagLink(),
	        maxWidth: this.getTagOption('maxWidth'),
	        textColor: this.getTagOption('textColor'),
	        bgColor: this.getTagOption('bgColor'),
	        fontWeight: this.getTagOption('fontWeight')
	      };
	    }
	  }, {
	    key: "toJSON",
	    value: function toJSON() {
	      return {
	        id: this.getId(),
	        entityId: this.getEntityId(),
	        entityType: this.getEntityType(),
	        selected: this.isSelected(),
	        deselectable: this.isDeselectable(),
	        hidden: this.isHidden(),
	        title: this.getTitle(),
	        link: this.getLink(),
	        linkTitle: this.getLinkTitle(),
	        subtitle: this.getSubtitle(),
	        supertitle: this.getSupertitle(),
	        caption: this.getCaption(),
	        avatar: this.getAvatar(),
	        customData: this.getCustomData()
	      };
	    }
	  }]);
	  return Item;
	}(main_core_events.EventEmitter);

	function _templateObject$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-tab-stub\">", "</div>\n\t\t\t"]);

	  _templateObject$2 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var BaseStub = /*#__PURE__*/function () {
	  function BaseStub(tab, options) {
	    babelHelpers.classCallCheck(this, BaseStub);
	    babelHelpers.defineProperty(this, "tab", null);
	    babelHelpers.defineProperty(this, "autoShow", true);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    babelHelpers.defineProperty(this, "content", null);
	    this.options = main_core.Type.isPlainObject(options) ? options : {};
	    this.tab = tab;
	    this.autoShow = this.getOption('autoShow', true);
	  }

	  babelHelpers.createClass(BaseStub, [{
	    key: "getTab",
	    value: function getTab() {
	      return this.tab;
	    }
	  }, {
	    key: "getOuterContainer",
	    value: function getOuterContainer() {
	      var _this = this;

	      return this.cache.remember('outer-container', function () {
	        return main_core.Tag.render(_templateObject$2(), _this.render());
	      });
	    }
	  }, {
	    key: "isAutoShow",
	    value: function isAutoShow() {
	      return this.autoShow;
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      main_core.Dom.append(this.getOuterContainer(), this.getTab().getContainer());
	      /*requestAnimationFrame(() => {
	      	Dom.addClass(this.getOuterContainer(), 'ui-selector-tab-stub--show');
	      });*/
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      // Dom.removeClass(this.getOuterContainer(), 'ui-selector-tab-stub--show');
	      main_core.Dom.remove(this.getOuterContainer());
	    }
	  }, {
	    key: "getOptions",
	    value: function getOptions() {
	      return this.options;
	    }
	  }, {
	    key: "getOption",
	    value: function getOption(option, defaultValue) {
	      if (!main_core.Type.isUndefined(this.options[option])) {
	        return this.options[option];
	      } else if (!main_core.Type.isUndefined(defaultValue)) {
	        return defaultValue;
	      }

	      return null;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      throw new Error('You must implement render() method.');
	    }
	  }]);
	  return BaseStub;
	}();

	function _templateObject3$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-selector-tab-default-stub-arrow\"></div>"]);

	  _templateObject3$1 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-selector-tab-default-stub-subtitle\">", "</div>"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-tab-default-stub\">\n\t\t\t\t\t<div class=\"ui-selector-tab-default-stub-icon\"", "></div>\n\t\t\t\t\t<div class=\"ui-selector-tab-default-stub-titles\">\n\t\t\t\t\t\t<div class=\"ui-selector-tab-default-stub-title\">", "</div>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject$3 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var DefaultStub = /*#__PURE__*/function (_BaseStub) {
	  babelHelpers.inherits(DefaultStub, _BaseStub);

	  function DefaultStub(tab, options) {
	    var _this;

	    babelHelpers.classCallCheck(this, DefaultStub);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DefaultStub).call(this, tab, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "content", null);
	    return _this;
	  }

	  babelHelpers.createClass(DefaultStub, [{
	    key: "getContainer",
	    value: function getContainer() {
	      var _this2 = this;

	      return this.cache.remember('container', function () {
	        var subtitle = _this2.getOption('subtitle');

	        var title = main_core.Type.isStringFilled(_this2.getOption('title')) ? _this2.getOption('title') : _this2.getDefaultTitle();

	        var icon = _this2.getOption('icon') || _this2.getTab().getIcon('default');

	        var iconOpacity = 35;

	        if (main_core.Type.isNumber(_this2.getOption('iconOpacity'))) {
	          iconOpacity = Math.min(100, Math.max(0, _this2.getOption('iconOpacity')));
	        }

	        var iconStyle = main_core.Type.isStringFilled(icon) ? "style=\"background-image: url('".concat(icon, "'); opacity: ").concat(iconOpacity / 100, ";\"") : '';
	        var arrow = _this2.getOption('arrow', false) && _this2.getTab().getDialog().getActiveFooter() !== null;
	        return main_core.Tag.render(_templateObject$3(), iconStyle, title, subtitle ? main_core.Tag.render(_templateObject2$1(), subtitle) : '', arrow ? main_core.Tag.render(_templateObject3$1()) : '');
	      });
	    }
	  }, {
	    key: "getDefaultTitle",
	    value: function getDefaultTitle() {
	      var tabTitle = main_core.Text.encode(this.getTab().getTitle());
	      return main_core.Loc.getMessage('UI_SELECTOR_TAB_STUB_TITLE').replace(/#TAB_TITLE#/, tabTitle);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      return this.getContainer();
	    }
	  }]);
	  return DefaultStub;
	}(BaseStub);

	function _templateObject$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-footer\">", "</div>\n\t\t\t"]);

	  _templateObject$4 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var BaseFooter = /*#__PURE__*/function () {
	  function BaseFooter(context, options) {
	    babelHelpers.classCallCheck(this, BaseFooter);
	    babelHelpers.defineProperty(this, "dialog", null);
	    babelHelpers.defineProperty(this, "tab", null);
	    babelHelpers.defineProperty(this, "container", null);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    this.options = main_core.Type.isPlainObject(options) ? options : {};

	    if (context instanceof Dialog) {
	      this.dialog = context;
	    } else {
	      this.tab = context;
	      this.dialog = this.tab.getDialog();
	    }
	  }

	  babelHelpers.createClass(BaseFooter, [{
	    key: "getDialog",
	    value: function getDialog() {
	      return this.dialog;
	    }
	  }, {
	    key: "getTab",
	    value: function getTab() {
	      return this.tab;
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      main_core.Dom.addClass(this.getContainer(), 'ui-selector-footer--show');
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      main_core.Dom.removeClass(this.getContainer(), 'ui-selector-footer--show');
	    }
	  }, {
	    key: "getOptions",
	    value: function getOptions() {
	      return this.options;
	    }
	  }, {
	    key: "getOption",
	    value: function getOption(option, defaultValue) {
	      if (!main_core.Type.isUndefined(this.options[option])) {
	        return this.options[option];
	      } else if (!main_core.Type.isUndefined(defaultValue)) {
	        return defaultValue;
	      }

	      return null;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (this.container === null) {
	        this.container = main_core.Tag.render(_templateObject$4(), this.render());
	      }

	      return this.container;
	    }
	    /**
	     * @abstract
	     */

	  }, {
	    key: "render",
	    value: function render() {
	      throw new Error('You must implement render() method.');
	    }
	  }]);
	  return BaseFooter;
	}();

	function _templateObject5$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-items\"></div>\n\t\t\t"]);

	  _templateObject5$1 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-tab-title\">", "</div>\n\t\t\t"]);

	  _templateObject4$1 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-tab-icon\"></div>\n\t\t\t"]);

	  _templateObject3$2 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div \n\t\t\t\t\tclass=\"ui-selector-tab-label", "\" \n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\tonmouseenter=\"", "\"\n\t\t\t\t\tonmouseleave=\"", "\"\n\t\t\t\t>\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject2$2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-tab-content\">", "</div>\n\t\t\t"]);

	  _templateObject$5 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	/**
	 * @memberof BX.UI.EntitySelector
	 */
	var Tab = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Tab, _EventEmitter);

	  function Tab(dialog, tabOptions) {
	    var _this;

	    babelHelpers.classCallCheck(this, Tab);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Tab).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "id", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "title", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "rootNode", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "dialog", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "stub", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "visible", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "rendered", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "locked", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "selected", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "hovered", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "icon", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "textColor", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "bgColor", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "itemMaxDepth", 3);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "footer", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "showDefaultFooter", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "cache", new main_core.Cache.MemoryCache());

	    _this.setEventNamespace('BX.UI.EntitySelector.Tab');

	    var options = main_core.Type.isPlainObject(tabOptions) ? tabOptions : {};

	    if (!main_core.Type.isStringFilled(options.id)) {
	      throw new Error('EntitySelector.Tab: "id" parameter is required.');
	    }

	    _this.setDialog(dialog);

	    _this.id = options.id;
	    _this.showDefaultFooter = options.showDefaultFooter !== false;
	    _this.rootNode = new ItemNode(null, {
	      itemOrder: options.itemOrder
	    });

	    _this.rootNode.setTab(babelHelpers.assertThisInitialized(_this));

	    _this.setVisible(options.visible);

	    _this.setTitle(options.title);

	    _this.setItemMaxDepth(options.itemMaxDepth);

	    _this.setIcon(options.icon);

	    _this.setTextColor(options.textColor);

	    _this.setBgColor(options.bgColor);

	    _this.setStub(options.stub, options.stubOptions);

	    _this.setFooter(options.footer, options.footerOptions);

	    return _this;
	  }

	  babelHelpers.createClass(Tab, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	    /**
	     * @internal
	     */

	  }, {
	    key: "setDialog",
	    value: function setDialog(dialog) {
	      this.dialog = dialog;
	    }
	  }, {
	    key: "getDialog",
	    value: function getDialog() {
	      return this.dialog;
	    }
	  }, {
	    key: "getStub",
	    value: function getStub() {
	      return this.stub;
	    }
	  }, {
	    key: "setStub",
	    value: function setStub(stub, stubOptions) {
	      var instance = null;
	      var options = main_core.Type.isPlainObject(stubOptions) ? stubOptions : {};

	      if (main_core.Type.isString(stub)) {
	        var className = main_core.Reflection.getClass(stub);

	        if (main_core.Type.isFunction(className)) {
	          instance = new className(this, options);

	          if (!(instance instanceof BaseStub)) {
	            console.error('EntitySelector: stub is not an instance of BaseStub.');
	            instance = null;
	          }
	        }
	      }

	      if (!instance && stub !== false) {
	        instance = new DefaultStub(this, options);
	      }

	      this.stub = instance;
	    }
	  }, {
	    key: "getFooter",
	    value: function getFooter() {
	      return this.footer;
	    }
	  }, {
	    key: "setFooter",
	    value: function setFooter(footerContent, footerOptions) {
	      /** @var {BaseFooter} */
	      var footer = null;

	      if (footerContent !== null) {
	        footer = Dialog.createFooter(this, footerContent, footerOptions);

	        if (footer === null) {
	          return;
	        }
	      }

	      if (this.isRendered() && this.getFooter() !== null) {
	        main_core.Dom.remove(this.getFooter().getContainer());
	        this.getDialog().adjustFooter();
	      }

	      this.footer = footer;

	      if (this.isRendered()) {
	        this.getDialog().appendFooter(footer);
	        this.getDialog().adjustFooter();
	      }
	    }
	  }, {
	    key: "canShowDefaultFooter",
	    value: function canShowDefaultFooter() {
	      return this.showDefaultFooter;
	    }
	  }, {
	    key: "enableDefaultFooter",
	    value: function enableDefaultFooter() {
	      this.showDefaultFooter = true;
	      this.getDialog().adjustFooter();
	    }
	  }, {
	    key: "disableDefaultFooter",
	    value: function disableDefaultFooter() {
	      this.showDefaultFooter = false;
	      this.getDialog().adjustFooter();
	    }
	  }, {
	    key: "getRootNode",
	    value: function getRootNode() {
	      return this.rootNode;
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      if (main_core.Type.isStringFilled(title)) {
	        this.title = title;

	        if (this.isRendered()) {
	          this.getTitleContainer().textContent = title;
	        }
	      }
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      return this.title;
	    }
	  }, {
	    key: "setIcon",
	    value: function setIcon(icon) {
	      return this.setProperty('icon', icon);
	    }
	  }, {
	    key: "getIcon",
	    value: function getIcon(state) {
	      return this.getPropertyByState('icon', state);
	    }
	  }, {
	    key: "setBgColor",
	    value: function setBgColor(bgColor) {
	      return this.setProperty('bgColor', bgColor);
	    }
	  }, {
	    key: "getBgColor",
	    value: function getBgColor(state) {
	      return this.getPropertyByState('bgColor', state);
	    }
	  }, {
	    key: "setTextColor",
	    value: function setTextColor(textColor) {
	      return this.setProperty('textColor', textColor);
	    }
	  }, {
	    key: "getTextColor",
	    value: function getTextColor(state) {
	      return this.getPropertyByState('textColor', state);
	    }
	  }, {
	    key: "setProperty",
	    value: function setProperty(name, states) {
	      var property = this[name];

	      if (!property) {
	        return;
	      }

	      if (main_core.Type.isPlainObject(states)) {
	        Object.keys(states).forEach(function (state) {
	          if (main_core.Type.isStringFilled(states[state])) {
	            property[state] = states[state];
	          }
	        });
	      } else if (main_core.Type.isStringFilled(states)) {
	        property['default'] = states;
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "getPropertyByState",
	    value: function getPropertyByState(name, state) {
	      var property = this[name];
	      var labelState = main_core.Type.isStringFilled(state) ? state : 'default';

	      if (!main_core.Type.isUndefined(property) && !main_core.Type.isUndefined(property[labelState])) {
	        return property[labelState];
	      }

	      return null;
	    }
	  }, {
	    key: "getPropertyByCurrentState",
	    value: function getPropertyByCurrentState(name) {
	      var property = this[name];

	      if (this.isSelected() && this.isHovered() && property.selectedHovered) {
	        return property.selectedHovered;
	      } else if (this.isSelected() && property.selected) {
	        return property.selected;
	      } else if (this.isHovered() && property.hovered) {
	        return property.hovered;
	      } else if (property.default) {
	        return property.default;
	      }

	      return null;
	    }
	  }, {
	    key: "setItemMaxDepth",
	    value: function setItemMaxDepth(depth) {
	      if (main_core.Type.isNumber(depth) && depth > 0) {
	        this.itemMaxDepth = depth;
	      }
	    }
	  }, {
	    key: "getItemMaxDepth",
	    value: function getItemMaxDepth() {
	      return this.itemMaxDepth;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      var _this2 = this;

	      return this.cache.remember('container', function () {
	        return main_core.Tag.render(_templateObject$5(), _this2.getItemsContainer());
	      });
	    }
	  }, {
	    key: "getLabelContainer",
	    value: function getLabelContainer() {
	      var _this3 = this;

	      return this.cache.remember('label', function () {
	        var className = _this3.isVisible() ? '' : ' ui-selector-tab-label-hidden';
	        return main_core.Tag.render(_templateObject2$2(), className, _this3.handleLabelClick.bind(_this3), _this3.handleLabelMouseEnter.bind(_this3), _this3.handleLabelMouseLeave.bind(_this3), _this3.getIconContainer(), _this3.getTitleContainer());
	      });
	    }
	  }, {
	    key: "getIconContainer",
	    value: function getIconContainer() {
	      return this.cache.remember('icon', function () {
	        return main_core.Tag.render(_templateObject3$2());
	      });
	    }
	  }, {
	    key: "getTitleContainer",
	    value: function getTitleContainer() {
	      var _this4 = this;

	      return this.cache.remember('title', function () {
	        return main_core.Tag.render(_templateObject4$1(), main_core.Text.encode(_this4.getTitle()));
	      });
	    }
	  }, {
	    key: "getItemsContainer",
	    value: function getItemsContainer() {
	      return this.cache.remember('items', function () {
	        return main_core.Tag.render(_templateObject5$1());
	      });
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      this.getRootNode().render();
	      this.rendered = true;
	    }
	  }, {
	    key: "renderLabel",
	    value: function renderLabel() {
	      main_core.Dom.style(this.getTitleContainer(), 'color', this.getPropertyByCurrentState('textColor'));
	      main_core.Dom.style(this.getLabelContainer(), 'background-color', this.getPropertyByCurrentState('bgColor'));
	      var icon = this.getPropertyByCurrentState('icon');
	      main_core.Dom.style(this.getIconContainer(), 'background-image', icon ? "url('".concat(icon, "')") : null);
	    }
	  }, {
	    key: "isVisible",
	    value: function isVisible() {
	      return this.visible;
	    }
	  }, {
	    key: "setVisible",
	    value: function setVisible(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.visible = flag;

	        if (this.isRendered()) {
	          if (this.visible) {
	            main_core.Dom.remove(this.getLabelContainer(), 'ui-selector-tab-label-hidden');
	          } else {
	            main_core.Dom.addClass(this.getLabelContainer(), 'ui-selector-tab-label-hidden');
	          }
	        }
	      }
	    }
	  }, {
	    key: "isRendered",
	    value: function isRendered() {
	      return this.rendered && this.getDialog() && this.getDialog().isRendered();
	    }
	  }, {
	    key: "select",
	    value: function select() {
	      if (this.isSelected()) {
	        return;
	      }

	      main_core.Dom.addClass(this.getContainer(), 'ui-selector-tab-content-active');

	      if (this.isVisible()) {
	        main_core.Dom.addClass(this.getLabelContainer(), 'ui-selector-tab-label-active');
	        this.renderLabel();
	      }

	      this.getDialog().emit('Tab:onSelect', {
	        tab: this
	      });
	      this.selected = true;

	      if (this.isVisible()) {
	        this.renderLabel();
	      }

	      if (this.getFooter()) {
	        this.getFooter().show();
	      }
	    }
	  }, {
	    key: "deselect",
	    value: function deselect() {
	      if (!this.isSelected()) {
	        return;
	      }

	      main_core.Dom.removeClass(this.getContainer(), 'ui-selector-tab-content-active');

	      if (this.isVisible()) {
	        main_core.Dom.removeClass(this.getLabelContainer(), 'ui-selector-tab-label-active');
	      }

	      this.getDialog().emit('Tab:onDeselect', {
	        tab: this
	      });
	      this.selected = false;

	      if (this.isVisible()) {
	        this.renderLabel();
	      }

	      if (this.getFooter()) {
	        this.getFooter().hide();
	      }
	    }
	  }, {
	    key: "hover",
	    value: function hover() {
	      if (this.isHovered()) {
	        return;
	      }

	      main_core.Dom.addClass(this.getLabelContainer(), 'ui-selector-tab-label-hover');
	      this.hovered = true;
	      this.renderLabel();
	    }
	  }, {
	    key: "unhover",
	    value: function unhover() {
	      if (!this.isHovered()) {
	        return;
	      }

	      main_core.Dom.removeClass(this.getLabelContainer(), 'ui-selector-tab-label-hover');
	      this.hovered = false;
	      this.renderLabel();
	    }
	  }, {
	    key: "isSelected",
	    value: function isSelected() {
	      return this.selected;
	    }
	  }, {
	    key: "isHovered",
	    value: function isHovered() {
	      return this.hovered;
	    }
	  }, {
	    key: "lock",
	    value: function lock() {
	      this.locked = true;
	      main_core.Dom.addClass(this.getContainer(), 'ui-selector-tab-content-locked');
	    }
	  }, {
	    key: "unlock",
	    value: function unlock() {
	      this.locked = false;
	      main_core.Dom.removeClass(this.getContainer(), 'ui-selector-tab-content-locked');
	    }
	  }, {
	    key: "isLocked",
	    value: function isLocked() {
	      return this.locked;
	    }
	  }, {
	    key: "handleLabelClick",
	    value: function handleLabelClick() {
	      this.getDialog().selectTab(this.getId());
	    }
	  }, {
	    key: "handleLabelMouseEnter",
	    value: function handleLabelMouseEnter() {
	      this.hover();
	    }
	  }, {
	    key: "handleLabelMouseLeave",
	    value: function handleLabelMouseLeave() {
	      this.unhover();
	    }
	  }]);
	  return Tab;
	}(main_core_events.EventEmitter);

	var Animation = /*#__PURE__*/function () {
	  function Animation() {
	    babelHelpers.classCallCheck(this, Animation);
	  }

	  babelHelpers.createClass(Animation, null, [{
	    key: "handleTransitionEnd",
	    value: function handleTransitionEnd(element, propertyName) {
	      var properties = main_core.Type.isArray(propertyName) ? new Set(propertyName) : new Set([propertyName]);
	      return new Promise(function (resolve) {
	        var handler = function handler(event) {
	          if (event.target !== element || !properties.has(event.propertyName)) {
	            return;
	          }

	          properties.delete(event.propertyName);

	          if (properties.size === 0) {
	            resolve(event);
	            main_core.Event.unbind(element, 'transitionend', handler);
	          }
	        };

	        main_core.Event.bind(element, 'transitionend', handler);
	      });
	    }
	  }, {
	    key: "handleAnimationEnd",
	    value: function handleAnimationEnd(element, animationName) {
	      return new Promise(function (resolve) {
	        var handler = function handler(event) {
	          if (!animationName || event.animationName === animationName) {
	            resolve(event);
	            main_core.Event.bind(element, 'animationend', handler);
	          }
	        };

	        main_core.Event.bind(element, 'animationend', handler);
	      });
	    }
	  }]);
	  return Animation;
	}();

	function _templateObject6$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-tag-selector-tag-remove\" onclick=\"", "\"></div>\n\t\t\t"]);

	  _templateObject6$1 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-tag-selector-tag-title\"></div>\n\t\t\t"]);

	  _templateObject5$2 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-tag-selector-tag-avatar\"></div>\n\t\t\t"]);

	  _templateObject4$2 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div \n\t\t\t\t\t\tclass=\"ui-tag-selector-tag-content", "\" \n\t\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t>\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t\n\t\t\t\t"]);

	  _templateObject3$3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<a\n\t\t\t\t\t\tclass=\"ui-tag-selector-tag-content\"\n\t\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t\thref=\"", "\"\n\t\t\t\t\t\ttarget=\"_blank\"\n\t\t\t\t\t>\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</a>\n\t\t\t\t"]);

	  _templateObject2$3 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-tag-selector-item ui-tag-selector-tag\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>"]);

	  _templateObject$6 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var TagItem = /*#__PURE__*/function () {
	  function TagItem(itemOptions) {
	    babelHelpers.classCallCheck(this, TagItem);
	    babelHelpers.defineProperty(this, "id", null);
	    babelHelpers.defineProperty(this, "entityId", null);
	    babelHelpers.defineProperty(this, "entityType", null);
	    babelHelpers.defineProperty(this, "title", '');
	    babelHelpers.defineProperty(this, "avatar", null);
	    babelHelpers.defineProperty(this, "maxWidth", null);
	    babelHelpers.defineProperty(this, "textColor", null);
	    babelHelpers.defineProperty(this, "bgColor", null);
	    babelHelpers.defineProperty(this, "fontWeight", null);
	    babelHelpers.defineProperty(this, "link", null);
	    babelHelpers.defineProperty(this, "onclick", null);
	    babelHelpers.defineProperty(this, "deselectable", null);
	    babelHelpers.defineProperty(this, "customData", null);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    babelHelpers.defineProperty(this, "selector", null);
	    babelHelpers.defineProperty(this, "rendered", false);
	    var options = main_core.Type.isPlainObject(itemOptions) ? itemOptions : {};

	    if (!main_core.Type.isStringFilled(options.id) && !main_core.Type.isNumber(options.id)) {
	      throw new Error('TagSelector.TagItem: "id" parameter is required.');
	    }

	    if (!main_core.Type.isStringFilled(options.entityId)) {
	      throw new Error('TagSelector.TagItem: "entityId" parameter is required.');
	    }

	    this.id = options.id;
	    this.entityId = options.entityId;
	    this.entityType = main_core.Type.isStringFilled(options.entityType) ? options.entityType : 'default';
	    this.customData = main_core.Type.isPlainObject(options.customData) ? new Map(Object.entries(options.customData)) : new Map();
	    this.onclick = main_core.Type.isFunction(options.onclick) ? options.onclick : null;
	    this.link = main_core.Type.isStringFilled(options.link) ? options.link : null;
	    this.setTitle(options.title);
	    this.setDeselectable(options.deselectable);
	    this.setAvatar(options.avatar);
	    this.setMaxWidth(options.maxWidth);
	    this.setTextColor(options.textColor);
	    this.setBgColor(options.bgColor);
	    this.setFontWeight(options.fontWeight);
	  }

	  babelHelpers.createClass(TagItem, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getEntityId",
	    value: function getEntityId() {
	      return this.entityId;
	    }
	  }, {
	    key: "getEntityType",
	    value: function getEntityType() {
	      return this.entityType;
	    }
	  }, {
	    key: "getSelector",
	    value: function getSelector() {
	      return this.selector;
	    }
	  }, {
	    key: "setSelector",
	    value: function setSelector(selector) {
	      this.selector = selector;
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      return this.title;
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      if (main_core.Type.isStringFilled(title)) {
	        this.title = title;
	      }

	      return this;
	    }
	  }, {
	    key: "getAvatar",
	    value: function getAvatar() {
	      if (this.avatar !== null) {
	        return this.avatar;
	      } else if (this.getSelector().getTagAvatar() !== null) {
	        return this.getSelector().getTagAvatar();
	      } else if (this.getEntityTagOption('avatar') !== null) {
	        return this.getEntityTagOption('avatar');
	      }

	      return this.getEntityItemOption('avatar');
	    }
	  }, {
	    key: "setAvatar",
	    value: function setAvatar(avatar) {
	      if (main_core.Type.isString(avatar) || avatar === null) {
	        this.avatar = avatar;
	      }

	      return this;
	    }
	  }, {
	    key: "getTextColor",
	    value: function getTextColor() {
	      if (this.textColor !== null) {
	        return this.textColor;
	      } else if (this.getSelector().getTagTextColor() !== null) {
	        return this.getSelector().getTagTextColor();
	      }

	      return this.getEntityTagOption('textColor');
	    }
	  }, {
	    key: "setTextColor",
	    value: function setTextColor(textColor) {
	      if (main_core.Type.isString(textColor) || textColor === null) {
	        this.textColor = textColor;
	      }

	      return this;
	    }
	  }, {
	    key: "getBgColor",
	    value: function getBgColor() {
	      if (this.bgColor !== null) {
	        return this.bgColor;
	      } else if (this.getSelector().getTagBgColor() !== null) {
	        return this.getSelector().getTagBgColor();
	      }

	      return this.getEntityTagOption('bgColor');
	    }
	  }, {
	    key: "setBgColor",
	    value: function setBgColor(bgColor) {
	      if (main_core.Type.isString(bgColor) || bgColor === null) {
	        this.bgColor = bgColor;
	      }

	      return this;
	    }
	  }, {
	    key: "getFontWeight",
	    value: function getFontWeight() {
	      if (this.fontWeight !== null) {
	        return this.fontWeight;
	      } else if (this.getSelector().getTagFontWeight() !== null) {
	        return this.getSelector().getTagFontWeight();
	      }

	      return this.getEntityTagOption('fontWeight');
	    }
	  }, {
	    key: "setFontWeight",
	    value: function setFontWeight(fontWeight) {
	      if (main_core.Type.isString(fontWeight) || fontWeight === null) {
	        this.fontWeight = fontWeight;
	      }

	      return this;
	    }
	  }, {
	    key: "getMaxWidth",
	    value: function getMaxWidth() {
	      if (this.maxWidth !== null) {
	        return this.maxWidth;
	      } else if (this.getSelector().getTagMaxWidth() !== null) {
	        return this.getSelector().getTagMaxWidth();
	      }

	      return this.getEntityTagOption('maxWidth');
	    }
	  }, {
	    key: "setMaxWidth",
	    value: function setMaxWidth(width) {
	      if (main_core.Type.isNumber(width) && width >= 0 || width === null) {
	        this.maxWidth = width;
	      }
	    }
	  }, {
	    key: "setDeselectable",
	    value: function setDeselectable(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.deselectable = flag;
	      }

	      return this;
	    }
	  }, {
	    key: "isDeselectable",
	    value: function isDeselectable() {
	      return this.deselectable !== null ? this.deselectable : this.getSelector().isDeselectable();
	    }
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      return this.link;
	    }
	  }, {
	    key: "getOnclick",
	    value: function getOnclick() {
	      return this.onclick;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      this.getTitleContainer().textContent = this.getTitle();
	      main_core.Dom.attr(this.getContentContainer(), 'title', this.getTitle());
	      var avatar = this.getAvatar();

	      if (main_core.Type.isStringFilled(avatar)) {
	        main_core.Dom.addClass(this.getContainer(), 'ui-tag-selector-tag--has-avatar');
	        main_core.Dom.style(this.getAvatarContainer(), 'background-image', "url('".concat(avatar, "')"));
	      } else {
	        main_core.Dom.removeClass(this.getContainer(), 'ui-tag-selector-tag--has-avatar');
	        main_core.Dom.style(this.getAvatarContainer(), 'background-image', null);
	      }

	      var maxWidth = this.getMaxWidth();

	      if (maxWidth > 0) {
	        main_core.Dom.style(this.getContainer(), 'max-width', "".concat(maxWidth, "px"));
	      } else {
	        main_core.Dom.style(this.getContainer(), 'max-width', null);
	      }

	      if (this.isDeselectable()) {
	        main_core.Dom.removeClass(this.getContainer(), 'ui-tag-selector-tag-readonly');
	      } else {
	        main_core.Dom.addClass(this.getContainer(), 'ui-tag-selector-tag-readonly');
	      }

	      main_core.Dom.style(this.getTitleContainer(), 'color', this.getTextColor());
	      main_core.Dom.style(this.getTitleContainer(), 'font-weight', this.getFontWeight());
	      main_core.Dom.style(this.getContainer(), 'background-color', this.getBgColor());
	      this.rendered = true;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      var _this = this;

	      return this.cache.remember('container', function () {
	        return main_core.Tag.render(_templateObject$6(), _this.getContentContainer(), _this.getRemoveIcon());
	      });
	    }
	  }, {
	    key: "getContentContainer",
	    value: function getContentContainer() {
	      var _this2 = this;

	      return this.cache.remember('content-container', function () {
	        if (main_core.Type.isStringFilled(_this2.getLink())) {
	          return main_core.Tag.render(_templateObject2$3(), _this2.handleContainerClick.bind(_this2), _this2.getLink(), _this2.getAvatarContainer(), _this2.getTitleContainer());
	        } else {
	          var className = main_core.Type.isFunction(_this2.getOnclick()) ? ' ui-tag-selector-tag-content--clickable' : '';
	          return main_core.Tag.render(_templateObject3$3(), className, _this2.handleContainerClick.bind(_this2), _this2.getAvatarContainer(), _this2.getTitleContainer());
	        }
	      });
	    }
	  }, {
	    key: "getAvatarContainer",
	    value: function getAvatarContainer() {
	      return this.cache.remember('avatar', function () {
	        return main_core.Tag.render(_templateObject4$2());
	      });
	    }
	  }, {
	    key: "getTitleContainer",
	    value: function getTitleContainer() {
	      return this.cache.remember('title', function () {
	        return main_core.Tag.render(_templateObject5$2());
	      });
	    }
	  }, {
	    key: "getRemoveIcon",
	    value: function getRemoveIcon() {
	      var _this3 = this;

	      return this.cache.remember('remove-icon', function () {
	        return main_core.Tag.render(_templateObject6$1(), _this3.handleRemoveIconClick.bind(_this3));
	      });
	    }
	  }, {
	    key: "getEntityTagOption",
	    value: function getEntityTagOption(option) {
	      return Entity.getTagOption(this.getEntityId(), this.getEntityType(), option);
	    }
	  }, {
	    key: "getEntityItemOption",
	    value: function getEntityItemOption(option) {
	      return Entity.getItemOption(this.getEntityId(), this.getEntityType(), option);
	    }
	  }, {
	    key: "isRendered",
	    value: function isRendered() {
	      return this.rendered && this.getSelector() && this.getSelector().isRendered();
	    }
	  }, {
	    key: "remove",
	    value: function remove() {
	      var _this4 = this;

	      var animate = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	      if (animate === false) {
	        main_core.Dom.remove(this.getContainer());
	        return Promise.resolve();
	      }

	      return new Promise(function (resolve) {
	        main_core.Dom.style(_this4.getContainer(), 'width', "".concat(_this4.getContainer().offsetWidth, "px"));
	        main_core.Dom.addClass(_this4.getContainer(), 'ui-tag-selector-tag--remove');
	        Animation.handleAnimationEnd(_this4.getContainer(), 'ui-tag-selector-tag-remove').then(function () {
	          main_core.Dom.remove(_this4.getContainer());
	          resolve();
	        });
	      });
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      var _this5 = this;

	      return new Promise(function (resolve) {
	        main_core.Dom.addClass(_this5.getContainer(), 'ui-tag-selector-tag--show');
	        Animation.handleAnimationEnd(_this5.getContainer(), 'ui-tag-selector-tag-show').then(function () {
	          main_core.Dom.removeClass(_this5.getContainer(), 'ui-tag-selector-tag--show');
	          resolve();
	        });
	      });
	    }
	  }, {
	    key: "handleContainerClick",
	    value: function handleContainerClick() {
	      var fn = this.getOnclick();

	      if (main_core.Type.isFunction(fn)) {
	        fn(this);
	      }
	    }
	  }, {
	    key: "handleRemoveIconClick",
	    value: function handleRemoveIconClick(event) {
	      event.stopPropagation();

	      if (this.isDeselectable()) {
	        this.getSelector().removeTag(this);
	      }
	    }
	  }]);
	  return TagItem;
	}();

	function _templateObject6$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-tag-selector-create-button", "\">\n\t\t\t\t\t<span \n\t\t\t\t\t\tclass=\"ui-tag-selector-create-button-caption\"\n\t\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t>", "</span>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject6$2 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"ui-tag-selector-item ui-tag-selector-add-button", "\">\n\t\t\t\t\t<span \n\t\t\t\t\t\tclass=\"ui-tag-selector-add-button-caption\" \n\t\t\t\t\t\tonclick=\"", "\">", "</span>\n\t\t\t\t</span>\n\t\t\t"]);

	  _templateObject5$3 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input \n\t\t\t\t\ttype=\"text\" \n\t\t\t\t\tclass=\"ui-tag-selector-item ui-tag-selector-text-box", "\" \n\t\t\t\t\tautocomplete=\"off\"\n\t\t\t\t\tplaceholder=\"", "\"\n\t\t\t\t\toninput=\"", "\"\n\t\t\t\t\tonblur=\"", "\"\n\t\t\t\t\tonkeyup=\"", "\"\n\t\t\t\t\tonkeydown=\"", "\"\n\t\t\t\t\tvalue=\"\"\n\t\t\t\t>\n\t\t\t"]);

	  _templateObject4$3 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-tag-selector-items\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject3$4 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div \n\t\t\t\t\tclass=\"ui-tag-selector-container\" \n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t", "\n\t\t\t\t>\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject2$4 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-tag-selector-outer-container", "\">", "</div>\n\t\t\t"]);

	  _templateObject$7 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	/**
	 * @memberof BX.UI.EntitySelector
	 */
	var TagSelector = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(TagSelector, _EventEmitter);

	  function TagSelector(selectorOptions) {
	    var _this;

	    babelHelpers.classCallCheck(this, TagSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TagSelector).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tags", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "cache", new main_core.Cache.MemoryCache());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "multiple", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "readonly", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "locked", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "deselectable", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "addButtonCaption", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "addButtonCaptionMore", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "createButtonCaption", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "addButtonVisible", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "createButtonVisible", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "textBoxVisible", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "textBoxWidth", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "maxHeight", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "placeholder", '');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "textBoxAutoHide", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "textBoxOldValue", '');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagAvatar", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagTextColor", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagBgColor", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagFontWeight", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagMaxWidth", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "dialog", null);

	    _this.setEventNamespace('BX.UI.EntitySelector.TagSelector');

	    var options = main_core.Type.isPlainObject(selectorOptions) ? selectorOptions : {};
	    _this.id = main_core.Type.isStringFilled(options.id) ? options.id : "ui-tag-selector-".concat(main_core.Text.getRandom().toLowerCase());
	    _this.multiple = main_core.Type.isBoolean(options.multiple) ? options.multiple : true;
	    _this.addButtonVisible = options.showAddButton !== false;
	    _this.createButtonVisible = options.showCreateButton === true;
	    _this.textBoxVisible = options.showTextBox === true;

	    _this.setReadonly(options.readonly);

	    _this.setLocked(options.locked);

	    _this.setAddButtonCaption(options.addButtonCaption);

	    _this.setAddButtonCaptionMore(options.addButtonCaptionMore);

	    _this.setCreateButtonCaption(options.createButtonCaption);

	    _this.setPlaceholder(options.placeholder);

	    _this.setTextBoxAutoHide(options.textBoxAutoHide);

	    _this.setTextBoxWidth(options.textBoxWidth);

	    _this.setDeselectable(options.deselectable);

	    _this.setMaxHeight(options.maxHeight);

	    _this.setTagAvatar(options.tagAvatar);

	    _this.setTagMaxWidth(options.tagMaxWidth);

	    _this.setTagTextColor(options.tagTextColor);

	    _this.setTagBgColor(options.tagBgColor);

	    _this.setTagFontWeight(options.tagFontWeight);

	    if (main_core.Type.isPlainObject(options.dialogOptions)) {
	      var selectedItems = main_core.Type.isArray(options.items) ? options.items : [];

	      if (main_core.Type.isArray(options.dialogOptions.selectedItems)) {
	        selectedItems = selectedItems.concat(options.dialogOptions.selectedItems);
	      }

	      var dialogOptions = Object.assign({}, options.dialogOptions, {
	        tagSelectorOptions: null,
	        selectedItems: selectedItems,
	        multiple: _this.isMultiple(),
	        tagSelector: babelHelpers.assertThisInitialized(_this)
	      });
	      new Dialog(dialogOptions);
	    } else if (main_core.Type.isArray(options.items)) {
	      options.items.forEach(function (item) {
	        _this.addTag(item);
	      });
	    }

	    _this.subscribeFromOptions(options.events);

	    return _this;
	  }

	  babelHelpers.createClass(TagSelector, [{
	    key: "getDialog",
	    value: function getDialog() {
	      return this.dialog;
	    }
	  }, {
	    key: "setDialog",
	    value: function setDialog(dialog) {
	      this.dialog = dialog;
	    }
	  }, {
	    key: "setReadonly",
	    value: function setReadonly(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.readonly = flag;

	        if (this.isRendered()) {
	          if (flag) {
	            main_core.Dom.addClass(this.getOuterContainer(), 'ui-tag-selector-container-readonly');
	          } else {
	            main_core.Dom.removeClass(this.getOuterContainer(), 'ui-tag-selector-container-readonly');
	          }
	        }
	      }
	    }
	  }, {
	    key: "isReadonly",
	    value: function isReadonly() {
	      return this.readonly;
	    }
	  }, {
	    key: "setLocked",
	    value: function setLocked(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.locked = flag;

	        if (this.isRendered()) {
	          if (flag) {
	            main_core.Dom.addClass(this.getOuterContainer(), 'ui-tag-selector-container-locked');
	            this.getTextBox().disabled = true;
	          } else {
	            main_core.Dom.removeClass(this.getOuterContainer(), 'ui-tag-selector-container-locked');
	            this.getTextBox().disabled = false;
	          }
	        }
	      }
	    }
	  }, {
	    key: "lock",
	    value: function lock() {
	      if (!this.isLocked()) {
	        this.setLocked(true);
	      }
	    }
	  }, {
	    key: "unlock",
	    value: function unlock() {
	      if (this.isLocked()) {
	        this.setLocked(false);
	      }
	    }
	  }, {
	    key: "isLocked",
	    value: function isLocked() {
	      return this.locked;
	    }
	  }, {
	    key: "isMultiple",
	    value: function isMultiple() {
	      return this.multiple;
	    }
	  }, {
	    key: "setDeselectable",
	    value: function setDeselectable(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        var changed = this.deselectable !== flag;
	        this.deselectable = flag;

	        if (changed) {
	          this.updateTags();
	        }
	      }
	    }
	  }, {
	    key: "isDeselectable",
	    value: function isDeselectable() {
	      return this.deselectable;
	    }
	  }, {
	    key: "getTag",
	    value: function getTag(tagItem) {
	      if (tagItem instanceof TagItem) {
	        return this.getTags().find(function (tag) {
	          return tag === tagItem;
	        });
	      } else if (main_core.Type.isPlainObject(tagItem)) {
	        var id = tagItem.id,
	            entityId = tagItem.entityId;
	        return this.getTags().find(function (tag) {
	          return tag.getId() === id && tag.getEntityId() === entityId;
	        });
	      }

	      return null;
	    }
	  }, {
	    key: "addTag",
	    value: function addTag(tagOptions) {
	      var _this2 = this;

	      if (!main_core.Type.isObjectLike(tagOptions)) {
	        throw new Error('TagSelector.addTag: wrong item options.');
	      }

	      if (this.getTag(tagOptions)) {
	        return null;
	      }

	      var tag = new TagItem(tagOptions);
	      tag.setSelector(this);
	      var event = new main_core_events.BaseEvent({
	        data: {
	          tag: tag
	        }
	      });
	      this.emit('onBeforeTagAdd', event);

	      if (event.isDefaultPrevented()) {
	        return null;
	      }

	      if (!this.isMultiple()) {
	        this.removeTags();
	      }

	      this.tags.push(tag);
	      this.emit('onTagAdd', {
	        tag: tag
	      });

	      if (this.isRendered()) {
	        tag.render();
	        this.getItemsContainer().insertBefore(tag.getContainer(), this.getTextBox());

	        if (tagOptions.animate !== false) {
	          tag.show().then(function () {
	            _this2.getContainer().scrollTop = _this2.getContainer().scrollHeight - _this2.getContainer().offsetHeight;

	            _this2.emit('onAfterTagAdd', {
	              tag: tag
	            });
	          });
	        } else {
	          this.emit('onAfterTagAdd', {
	            tag: tag
	          });
	        }

	        this.toggleAddButtonCaption();
	      } else {
	        this.emit('onAfterTagAdd', {
	          tag: tag
	        });
	      }

	      return tag;
	    }
	  }, {
	    key: "removeTag",
	    value: function removeTag(item) {
	      var _this3 = this;

	      var animate = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
	      var tagItem = this.getTag(item);

	      if (!tagItem) {
	        return;
	      }

	      var event = new main_core_events.BaseEvent({
	        data: {
	          tag: tagItem
	        }
	      });
	      this.emit('onBeforeTagRemove', event);

	      if (event.isDefaultPrevented()) {
	        return;
	      }

	      this.tags = this.tags.filter(function (el) {
	        return el !== tagItem;
	      });
	      this.emit('onTagRemove', {
	        tag: tagItem
	      });

	      if (this.isRendered()) {
	        tagItem.remove(animate).then(function () {
	          _this3.toggleAddButtonCaption();

	          _this3.emit('onAfterTagRemove', {
	            tag: tagItem
	          });
	        });
	      } else {
	        this.emit('onAfterTagRemove', {
	          tag: tagItem
	        });
	      }
	    }
	  }, {
	    key: "removeTags",
	    value: function removeTags() {
	      var _this4 = this;

	      this.getTags().forEach(function (tag) {
	        _this4.removeTag(tag, false);
	      });
	    }
	  }, {
	    key: "getTags",
	    value: function getTags() {
	      return this.tags;
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(node) {
	      var _this5 = this;

	      this.rendered = true;
	      this.getTags().forEach(function (tag) {
	        tag.render();

	        _this5.getItemsContainer().insertBefore(tag.getContainer(), _this5.getTextBox());
	      });

	      if (main_core.Type.isDomNode(node)) {
	        node.appendChild(this.getOuterContainer());
	      }
	    }
	  }, {
	    key: "isRendered",
	    value: function isRendered() {
	      return this.rendered;
	    }
	  }, {
	    key: "updateTags",
	    value: function updateTags() {
	      if (this.isRendered()) {
	        this.getTags().forEach(function (tag) {
	          tag.render();
	        });
	      }
	    }
	  }, {
	    key: "getOuterContainer",
	    value: function getOuterContainer() {
	      var _this6 = this;

	      return this.cache.remember('outer-container', function () {
	        var className = _this6.isReadonly() ? ' ui-tag-selector-container-readonly' : '';
	        className += _this6.isLocked() ? ' ui-tag-selector-container-locked' : '';
	        return main_core.Tag.render(_templateObject$7(), className, _this6.getContainer());
	      });
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      var _this7 = this;

	      return this.cache.remember('container', function () {
	        var style = _this7.getMaxHeight() ? " style=\"max-height: ".concat(_this7.getMaxHeight(), "px; -ms-overflow-style: -ms-autohiding-scrollbar;\"") : '';
	        return main_core.Tag.render(_templateObject2$4(), _this7.handleContainerClick.bind(_this7), style, _this7.getItemsContainer(), _this7.getCreateButton());
	      });
	    }
	  }, {
	    key: "getItemsContainer",
	    value: function getItemsContainer() {
	      var _this8 = this;

	      return this.cache.remember('items-container', function () {
	        return main_core.Tag.render(_templateObject3$4(), _this8.getTextBox(), _this8.getAddButton());
	      });
	    }
	  }, {
	    key: "getTextBox",
	    value: function getTextBox() {
	      var _this9 = this;

	      return this.cache.remember('text-box', function () {
	        var className = _this9.textBoxVisible ? '' : ' ui-tag-selector-item-hidden';
	        var input = main_core.Tag.render(_templateObject4$3(), className, main_core.Text.encode(_this9.getPlaceholder()), _this9.handleTextBoxInput.bind(_this9), _this9.handleTextBoxBlur.bind(_this9), _this9.handleTextBoxKeyUp.bind(_this9), _this9.handleTextBoxKeyDown.bind(_this9));

	        var width = _this9.getTextBoxWidth();

	        if (width !== null) {
	          main_core.Dom.style(input, 'width', main_core.Type.isStringFilled(width) ? width : "".concat(width, "px"));
	        }

	        if (_this9.isLocked()) {
	          input.disabled = true;
	        }

	        return input;
	      });
	    }
	  }, {
	    key: "getItemsHeight",
	    value: function getItemsHeight() {
	      return this.getItemsContainer().scrollHeight;
	    }
	  }, {
	    key: "calcHeight",
	    value: function calcHeight() {
	      if (this.getMaxHeight() !== null) {
	        return Math.min(this.getItemsHeight(), this.getMaxHeight());
	      }

	      return Math.max(this.getItemsHeight(), this.getMinHeight());
	    }
	  }, {
	    key: "getTextBoxValue",
	    value: function getTextBoxValue() {
	      return this.getTextBox().value;
	    }
	  }, {
	    key: "clearTextBox",
	    value: function clearTextBox() {
	      this.getTextBox().value = '';
	    }
	  }, {
	    key: "showTextBox",
	    value: function showTextBox() {
	      this.textBoxVisible = true;
	      main_core.Dom.removeClass(this.getTextBox(), 'ui-tag-selector-item-hidden');
	    }
	  }, {
	    key: "hideTextBox",
	    value: function hideTextBox() {
	      this.textBoxVisible = false;
	      main_core.Dom.addClass(this.getTextBox(), 'ui-tag-selector-item-hidden');
	    }
	  }, {
	    key: "focusTextBox",
	    value: function focusTextBox() {
	      this.getTextBox().focus();
	    }
	  }, {
	    key: "setTextBoxAutoHide",
	    value: function setTextBoxAutoHide(autoHide) {
	      if (main_core.Type.isBoolean(autoHide)) {
	        this.textBoxAutoHide = autoHide;
	      }
	    }
	  }, {
	    key: "getTextBoxWidth",
	    value: function getTextBoxWidth() {
	      return this.textBoxWidth;
	    }
	  }, {
	    key: "setTextBoxWidth",
	    value: function setTextBoxWidth(width) {
	      if (main_core.Type.isStringFilled(width) || width === null) {
	        this.textBoxWidth = width;

	        if (this.isRendered()) {
	          main_core.Dom.style(this.getTextBox(), 'width', width);
	        }
	      } else if (main_core.Type.isNumber(width) && width > 0) {
	        this.textBoxWidth = width;

	        if (this.isRendered()) {
	          main_core.Dom.style(this.getTextBox(), 'width', "".concat(width, "px"));
	        }
	      }
	    }
	  }, {
	    key: "getTagMaxWidth",
	    value: function getTagMaxWidth() {
	      return this.tagMaxWidth;
	    }
	  }, {
	    key: "setTagMaxWidth",
	    value: function setTagMaxWidth(width) {
	      if (main_core.Type.isNumber(width) && width >= 0 || width === null) {
	        this.tagMaxWidth = width;
	        this.updateTags();
	      }
	    }
	  }, {
	    key: "getTagAvatar",
	    value: function getTagAvatar() {
	      return this.tagAvatar;
	    }
	  }, {
	    key: "setTagAvatar",
	    value: function setTagAvatar(tagAvatar) {
	      if (main_core.Type.isString(tagAvatar) || tagAvatar === null) {
	        this.tagAvatar = tagAvatar;
	        this.updateTags();
	      }
	    }
	  }, {
	    key: "getTagTextColor",
	    value: function getTagTextColor() {
	      return this.tagTextColor;
	    }
	  }, {
	    key: "setTagTextColor",
	    value: function setTagTextColor(textColor) {
	      if (main_core.Type.isString(textColor) || textColor === null) {
	        this.tagTextColor = textColor;
	        this.updateTags();
	      }
	    }
	  }, {
	    key: "getTagBgColor",
	    value: function getTagBgColor() {
	      return this.tagBgColor;
	    }
	  }, {
	    key: "setTagBgColor",
	    value: function setTagBgColor(bgColor) {
	      if (main_core.Type.isString(bgColor) || bgColor === null) {
	        this.tagBgColor = bgColor;
	        this.updateTags();
	      }
	    }
	  }, {
	    key: "getTagFontWeight",
	    value: function getTagFontWeight() {
	      return this.tagFontWeight;
	    }
	  }, {
	    key: "setTagFontWeight",
	    value: function setTagFontWeight(fontWeight) {
	      if (main_core.Type.isString(fontWeight) || fontWeight === null) {
	        this.tagFontWeight = fontWeight;
	        this.updateTags();
	      }
	    }
	  }, {
	    key: "getPlaceholder",
	    value: function getPlaceholder() {
	      return this.placeholder;
	    }
	  }, {
	    key: "setPlaceholder",
	    value: function setPlaceholder(placeholder) {
	      if (main_core.Type.isStringFilled(placeholder)) {
	        this.placeholder = placeholder;

	        if (this.isRendered()) {
	          this.getTextBox().placeholder = placeholder;
	        }
	      }
	    }
	  }, {
	    key: "getMaxHeight",
	    value: function getMaxHeight() {
	      return this.maxHeight;
	    }
	  }, {
	    key: "getMinHeight",
	    value: function getMinHeight() {
	      return 33;
	    }
	  }, {
	    key: "setMaxHeight",
	    value: function setMaxHeight(height) {
	      if (main_core.Type.isNumber(height) && height > 0 || height === null) {
	        this.maxHeight = height;

	        if (this.isRendered()) {
	          main_core.Dom.style(this.getContainer(), 'max-height', height > 0 ? "".concat(height, "px") : null);
	          main_core.Dom.style(this.getContainer(), '-ms-overflow-style', height > 0 ? '-ms-autohiding-scrollbar' : null);
	        }
	      }
	    }
	  }, {
	    key: "getAddButton",
	    value: function getAddButton() {
	      var _this10 = this;

	      return this.cache.remember('add-button', function () {
	        var caption = main_core.Text.encode(_this10.getActualButtonCaption());
	        var className = _this10.addButtonVisible ? '' : ' ui-tag-selector-item-hidden';
	        return main_core.Tag.render(_templateObject5$3(), className, _this10.handleAddButtonClick.bind(_this10), caption);
	      });
	    }
	  }, {
	    key: "getAddButtonCaption",
	    value: function getAddButtonCaption() {
	      return this.addButtonCaption === null ? main_core.Loc.getMessage('UI_TAG_SELECTOR_ADD_BUTTON_CAPTION') : this.addButtonCaption;
	    }
	  }, {
	    key: "setAddButtonCaption",
	    value: function setAddButtonCaption(caption) {
	      if (main_core.Type.isStringFilled(caption)) {
	        this.addButtonCaption = caption;

	        if (this.isRendered()) {
	          this.toggleAddButtonCaption();
	        }
	      }
	    }
	  }, {
	    key: "getAddButtonCaptionMore",
	    value: function getAddButtonCaptionMore() {
	      return this.addButtonCaptionMore === null ? this.isMultiple() ? main_core.Loc.getMessage('UI_TAG_SELECTOR_ADD_BUTTON_CAPTION') : main_core.Loc.getMessage('UI_TAG_SELECTOR_ADD_BUTTON_CAPTION_SINGLE') : this.addButtonCaptionMore;
	    }
	  }, {
	    key: "setAddButtonCaptionMore",
	    value: function setAddButtonCaptionMore(caption) {
	      if (main_core.Type.isStringFilled(caption)) {
	        this.addButtonCaptionMore = caption;

	        if (this.isRendered()) {
	          this.toggleAddButtonCaption();
	        }
	      }
	    }
	  }, {
	    key: "toggleAddButtonCaption",
	    value: function toggleAddButtonCaption() {
	      if (this.getAddButtonCaptionMore() === null) {
	        return;
	      }

	      this.getAddButton().children[0].textContent = this.getActualButtonCaption();
	    }
	  }, {
	    key: "getActualButtonCaption",
	    value: function getActualButtonCaption() {
	      return this.getTags().length > 0 && this.getAddButtonCaptionMore() !== null ? this.getAddButtonCaptionMore() : this.getAddButtonCaption();
	    }
	  }, {
	    key: "showAddButton",
	    value: function showAddButton() {
	      this.addButtonVisible = true;
	      main_core.Dom.removeClass(this.getAddButton(), 'ui-tag-selector-item-hidden');
	    }
	  }, {
	    key: "hideAddButton",
	    value: function hideAddButton() {
	      this.addButtonVisible = false;
	      main_core.Dom.addClass(this.getAddButton(), 'ui-tag-selector-item-hidden');
	    }
	  }, {
	    key: "getCreateButton",
	    value: function getCreateButton() {
	      var _this11 = this;

	      return this.cache.remember('create-button', function () {
	        var className = _this11.createButtonVisible ? '' : ' ui-tag-selector-item-hidden';
	        return main_core.Tag.render(_templateObject6$2(), className, _this11.handleCreateButtonClick.bind(_this11), main_core.Text.encode(_this11.getCreateButtonCaption()));
	      });
	    }
	  }, {
	    key: "showCreateButton",
	    value: function showCreateButton() {
	      this.createButtonVisible = true;
	      main_core.Dom.removeClass(this.getCreateButton(), 'ui-tag-selector-item-hidden');
	    }
	  }, {
	    key: "hideCreateButton",
	    value: function hideCreateButton() {
	      this.createButtonVisible = false;
	      main_core.Dom.addClass(this.getCreateButton(), 'ui-tag-selector-item-hidden');
	    }
	  }, {
	    key: "getCreateButtonCaption",
	    value: function getCreateButtonCaption() {
	      return this.createButtonCaption === null ? main_core.Loc.getMessage('UI_TAG_SELECTOR_CREATE_BUTTON_CAPTION') : this.createButtonCaption;
	    }
	  }, {
	    key: "setCreateButtonCaption",
	    value: function setCreateButtonCaption(caption) {
	      if (main_core.Type.isStringFilled(caption)) {
	        this.createButtonCaption = caption;

	        if (this.isRendered()) {
	          this.getCreateButton().children[0].textContent = caption;
	        }
	      }
	    }
	  }, {
	    key: "handleContainerClick",
	    value: function handleContainerClick(event) {
	      this.emit('onContainerClick', {
	        selector: this,
	        event: event
	      });
	    }
	  }, {
	    key: "handleTextBoxInput",
	    value: function handleTextBoxInput(event) {
	      var newValue = this.getTextBoxValue();

	      if (newValue !== this.textBoxOldValue) {
	        this.textBoxOldValue = newValue;
	        this.emit('onInput', {
	          selector: this,
	          event: event
	        });
	      }
	    }
	  }, {
	    key: "handleTextBoxBlur",
	    value: function handleTextBoxBlur(event) {
	      this.emit('onBlur', {
	        selector: this,
	        event: event
	      });

	      if (this.textBoxAutoHide) {
	        this.getTextBox().value = '';
	        this.showAddButton();
	        this.hideTextBox();
	      }
	    }
	  }, {
	    key: "handleTextBoxKeyUp",
	    value: function handleTextBoxKeyUp(event) {
	      this.emit('onKeyUp', {
	        selector: this,
	        event: event
	      });

	      if (event.key === 'Enter') {
	        this.emit('onEnter', {
	          selector: this,
	          event: event
	        });

	        if (this.textBoxAutoHide) {
	          this.getTextBox().value = '';
	          this.showAddButton();
	          this.hideTextBox();
	        }
	      }
	    }
	  }, {
	    key: "handleTextBoxKeyDown",
	    value: function handleTextBoxKeyDown(event) {
	      if (event.key === 'Enter') {
	        // prevent a form submit
	        event.preventDefault();

	        if (main_core.Browser.isMac() && event.metaKey || event.ctrlKey) {
	          this.emit('onMetaEnter', {
	            selector: this,
	            event: event
	          });
	        }
	      }

	      this.emit('onKeyDown', {
	        selector: this,
	        event: event
	      });
	    }
	  }, {
	    key: "handleAddButtonClick",
	    value: function handleAddButtonClick(event) {
	      this.hideAddButton();
	      this.showTextBox();
	      this.focusTextBox();
	      this.emit('onAddButtonClick', {
	        selector: this,
	        event: event
	      });
	    }
	  }, {
	    key: "handleCreateButtonClick",
	    value: function handleCreateButtonClick(event) {
	      this.emit('onCreateButtonClick', {
	        selector: this,
	        event: event
	      });
	    }
	  }]);
	  return TagSelector;
	}(main_core_events.EventEmitter);

	var Navigation = /*#__PURE__*/function () {
	  function Navigation(dialog) {
	    babelHelpers.classCallCheck(this, Navigation);
	    babelHelpers.defineProperty(this, "dialog", null);
	    babelHelpers.defineProperty(this, "lockedTab", null);
	    babelHelpers.defineProperty(this, "enabled", false);
	    this.dialog = dialog;
	    this.dialog.subscribe('onShow', this.handleDialogShow.bind(this));
	    this.dialog.subscribe('onHide', this.handleDialogHide.bind(this));
	    this.dialog.subscribe('onDestroy', this.handleDialogDestroy.bind(this));
	    this.handleDocumentKeyDown = this.handleDocumentKeyDown.bind(this);
	    this.handleDocumentMouseMove = this.handleDocumentMouseMove.bind(this);
	  }

	  babelHelpers.createClass(Navigation, [{
	    key: "getDialog",
	    value: function getDialog() {
	      return this.dialog;
	    }
	  }, {
	    key: "enable",
	    value: function enable() {
	      if (!this.isEnabled()) {
	        this.bindEvents();
	      }

	      this.enabled = true;
	    }
	  }, {
	    key: "disable",
	    value: function disable() {
	      if (this.isEnabled()) {
	        this.unbindEvents();
	        this.unlockTab();
	      }

	      this.enabled = false;
	    }
	  }, {
	    key: "isEnabled",
	    value: function isEnabled() {
	      return this.enabled;
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      main_core.Event.bind(document, 'keydown', this.handleDocumentKeyDown);
	    }
	  }, {
	    key: "unbindEvents",
	    value: function unbindEvents() {
	      main_core.Event.unbind(document, 'keydown', this.handleDocumentKeyDown);
	    }
	  }, {
	    key: "getNextNode",
	    value: function getNextNode() {
	      if (!this.getActiveNode()) {
	        return null;
	      }

	      var nextNode = null;
	      var currentNode = this.getActiveNode();

	      if (currentNode.hasChildren() && currentNode.isOpen()) {
	        nextNode = currentNode.getFirstChild();
	      }

	      while (nextNode === null && currentNode !== null) {
	        nextNode = currentNode.getNextSibling();

	        if (nextNode) {
	          break;
	        }

	        currentNode = currentNode.getParentNode();
	      }

	      return nextNode;
	    }
	  }, {
	    key: "getPreviousNode",
	    value: function getPreviousNode() {
	      if (!this.getActiveNode()) {
	        return null;
	      }

	      var previousNode = this.getActiveNode().getPreviousSibling();

	      if (previousNode) {
	        while (previousNode.hasChildren() && previousNode.isOpen()) {
	          var lastChild = previousNode.getLastChild();

	          if (lastChild === null) {
	            break;
	          }

	          previousNode = lastChild;
	        }
	      } else {
	        if (this.getActiveNode().getParentNode() && !this.getActiveNode().getParentNode().isRoot()) {
	          previousNode = this.getActiveNode().getParentNode();
	        }
	      }

	      return previousNode;
	    }
	  }, {
	    key: "getFirstNode",
	    value: function getFirstNode() {
	      var tab = this.getDialog().getActiveTab();
	      return tab && tab.getRootNode().getFirstChild();
	    }
	  }, {
	    key: "getLastNode",
	    value: function getLastNode() {
	      var tab = this.getDialog().getActiveTab();

	      if (!tab) {
	        return null;
	      }

	      var lastNode = tab.getRootNode().getLastChild();

	      if (lastNode !== null) {
	        while (lastNode.hasChildren() && lastNode.isOpen()) {
	          var lastChild = lastNode.getLastChild();

	          if (lastChild === null) {
	            break;
	          }

	          lastNode = lastChild;
	        }
	      }

	      return lastNode;
	    }
	  }, {
	    key: "getActiveNode",
	    value: function getActiveNode() {
	      return this.getDialog().getFocusedNode();
	    }
	  }, {
	    key: "focusOnNode",
	    value: function focusOnNode(node) {
	      if (node) {
	        node.focus();
	        node.scrollIntoView();
	      }
	    }
	  }, {
	    key: "lockTab",
	    value: function lockTab() {
	      var activeTab = this.getDialog().getActiveTab();

	      if (this.lockedTab === activeTab) {
	        return;
	      } else if (this.lockedTab !== null) {
	        this.unlockTab();
	      }

	      this.lockedTab = activeTab;
	      this.lockedTab.lock();
	      main_core.Event.bind(document, 'mousemove', this.handleDocumentMouseMove);
	    }
	  }, {
	    key: "unlockTab",
	    value: function unlockTab() {
	      if (this.lockedTab === null) {
	        return;
	      }

	      this.lockedTab.unlock();
	      this.lockedTab = null;
	      main_core.Event.unbind(document, 'mousemove', this.handleDocumentMouseMove);
	    }
	  }, {
	    key: "handleDialogShow",
	    value: function handleDialogShow() {
	      this.enable();
	    }
	  }, {
	    key: "handleDialogHide",
	    value: function handleDialogHide() {
	      this.disable();
	    }
	  }, {
	    key: "handleDialogDestroy",
	    value: function handleDialogDestroy() {
	      this.disable();
	    }
	  }, {
	    key: "handleDocumentMouseMove",
	    value: function handleDocumentMouseMove() {
	      this.unlockTab();
	    }
	  }, {
	    key: "handleDocumentKeyDown",
	    value: function handleDocumentKeyDown(event) {
	      if (!this.getDialog().isOpen()) {
	        this.unbindEvents();
	        return;
	      }

	      if (event.metaKey || event.ctrlKey || event.altKey) {
	        return;
	      }

	      var activeTab = this.getDialog().getActiveTab();

	      if (!activeTab) {
	        return;
	      }

	      var keyName = this.constructor.keyMap[event.key] || event.key;
	      var query = this.getDialog().getTagSelectorQuery();

	      if (main_core.Type.isStringFilled(query) && ['ArrowLeft', 'ArrowRight'].includes(keyName)) {
	        return;
	      }

	      var handler = this["handle".concat(keyName, "Press")];

	      if (handler) {
	        handler.call(this, event);
	        this.lockTab(activeTab);
	        event.preventDefault();
	      }
	    }
	  }, {
	    key: "handleArrowDownPress",
	    value: function handleArrowDownPress() {
	      if (!this.getActiveNode()) {
	        var firstNode = this.getFirstNode();
	        this.focusOnNode(firstNode);
	      } else {
	        var nextNode = this.getNextNode();

	        if (nextNode) {
	          this.focusOnNode(nextNode);
	        } else {
	          var _firstNode = this.getFirstNode();

	          this.focusOnNode(_firstNode);
	        }
	      }
	    }
	  }, {
	    key: "handleArrowUpPress",
	    value: function handleArrowUpPress() {
	      if (!this.getActiveNode()) {
	        var lastNode = this.getLastNode();
	        this.focusOnNode(lastNode);
	      } else {
	        var previousNode = this.getPreviousNode();

	        if (previousNode) {
	          this.focusOnNode(previousNode);
	        } else {
	          var _lastNode = this.getLastNode();

	          this.focusOnNode(_lastNode);
	        }
	      }
	    }
	  }, {
	    key: "handleArrowRightPress",
	    value: function handleArrowRightPress() {
	      if (this.getActiveNode()) {
	        this.getActiveNode().expand();
	      }
	    }
	  }, {
	    key: "handleArrowLeftPress",
	    value: function handleArrowLeftPress() {
	      if (!this.getActiveNode()) {
	        return;
	      }

	      if (this.getActiveNode().isOpen()) {
	        this.getActiveNode().collapse();
	      } else {
	        var parentNode = this.getActiveNode().getParentNode();

	        if (parentNode && !parentNode.isRoot()) {
	          this.focusOnNode(parentNode);
	        }
	      }
	    }
	  }, {
	    key: "handleEnterPress",
	    value: function handleEnterPress() {
	      if (this.getActiveNode()) {
	        this.getActiveNode().click();
	      }
	    }
	    /*handleSpacePress(event: KeyboardEvent): void
	    {
	    	const searchQuery = this.getDialog().getTagSelector() && this.getDialog().getTagSelector().getTextBoxValue();
	    	if (this.getActiveNode() && !Type.isStringFilled(searchQuery))
	    	{
	    		this.getActiveNode().click();
	    		event.preventDefault();
	    	}
	    }*/

	  }, {
	    key: "handleTabPress",
	    value: function handleTabPress(event) {
	      var activeTab = this.getDialog().getActiveTab();

	      if (!activeTab) {
	        this.getDialog().selectFirstTab();
	        return;
	      }

	      if (event.shiftKey) {
	        var previousTab = this.getDialog().getPreviousTab();

	        if (previousTab) {
	          this.getDialog().selectTab(previousTab.getId());
	        } else {
	          this.getDialog().selectLastTab();
	        }
	      } else {
	        var nextTab = this.getDialog().getNextTab();

	        if (nextTab) {
	          this.getDialog().selectTab(nextTab.getId());
	        } else {
	          this.getDialog().selectFirstTab();
	        }
	      }
	    }
	  }]);
	  return Navigation;
	}();

	babelHelpers.defineProperty(Navigation, "keyMap", {
	  // IE/Edge
	  'Down': 'ArrowDown',
	  'Up': 'ArrowUp',
	  'Left': 'ArrowLeft',
	  'Right': 'ArrowRight',
	  'Spacebar': 'Space',
	  ' ': 'Space' // All

	});

	var SliderIntegration = /*#__PURE__*/function () {
	  function SliderIntegration(dialog) {
	    babelHelpers.classCallCheck(this, SliderIntegration);
	    babelHelpers.defineProperty(this, "dialog", null);
	    babelHelpers.defineProperty(this, "sliders", new Set());
	    this.dialog = dialog;
	    this.dialog.subscribe('onShow', this.handleDialogShow.bind(this));
	    this.dialog.subscribe('onHide', this.handleDialogHide.bind(this));
	    this.dialog.subscribe('onDestroy', this.handleDialogDestroy.bind(this));
	    this.handleSliderOpen = this.handleSliderOpen.bind(this);
	    this.handleSliderClose = this.handleSliderClose.bind(this);
	  }

	  babelHelpers.createClass(SliderIntegration, [{
	    key: "getDialog",
	    value: function getDialog() {
	      return this.dialog;
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      main_core_events.EventEmitter.subscribe('SidePanel.Slider:onOpen', this.handleSliderOpen);
	      main_core_events.EventEmitter.subscribe('SidePanel.Slider:onCloseComplete', this.handleSliderClose);
	      main_core_events.EventEmitter.subscribe('SidePanel.Slider:onDestroy', this.handleSliderClose);
	    }
	  }, {
	    key: "unbindEvents",
	    value: function unbindEvents() {
	      main_core_events.EventEmitter.unsubscribe('SidePanel.Slider:onOpen', this.handleSliderOpen);
	      main_core_events.EventEmitter.unsubscribe('SidePanel.Slider:onCloseComplete', this.handleSliderClose);
	      main_core_events.EventEmitter.unsubscribe('SidePanel.Slider:onDestroy', this.handleSliderClose);
	    }
	  }, {
	    key: "handleDialogShow",
	    value: function handleDialogShow() {
	      this.bindEvents();
	    }
	  }, {
	    key: "handleDialogHide",
	    value: function handleDialogHide() {
	      this.sliders.clear();
	      this.unbindEvents();
	      this.getDialog().unfreeze();
	    }
	  }, {
	    key: "handleDialogDestroy",
	    value: function handleDialogDestroy() {
	      this.sliders.clear();
	      this.unbindEvents();
	      this.getDialog().unfreeze();
	    }
	  }, {
	    key: "handleSliderOpen",
	    value: function handleSliderOpen(event) {
	      var _event$getData = event.getData(),
	          _event$getData2 = babelHelpers.slicedToArray(_event$getData, 1),
	          sliderEvent = _event$getData2[0];

	      var slider = sliderEvent.getSlider();
	      this.sliders.add(slider);
	      this.getDialog().freeze();
	    }
	  }, {
	    key: "handleSliderClose",
	    value: function handleSliderClose(event) {
	      var _event$getData3 = event.getData(),
	          _event$getData4 = babelHelpers.slicedToArray(_event$getData3, 1),
	          sliderEvent = _event$getData4[0];

	      var slider = sliderEvent.getSlider();
	      this.sliders.delete(slider);

	      if (this.sliders.size === 0) {
	        this.getDialog().unfreeze();
	      }
	    }
	  }]);
	  return SliderIntegration;
	}();

	function _templateObject$8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-selector-footer-default\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject$8 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var DefaultFooter = /*#__PURE__*/function (_BaseFooter) {
	  babelHelpers.inherits(DefaultFooter, _BaseFooter);

	  function DefaultFooter(context, options) {
	    var _this;

	    babelHelpers.classCallCheck(this, DefaultFooter);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DefaultFooter).call(this, context, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "content", null);

	    _this.setContent(_this.getOption('content'));

	    return _this;
	  }

	  babelHelpers.createClass(DefaultFooter, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$8(), this.getContent() ? this.getContent() : '');
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      return this.content;
	    }
	  }, {
	    key: "setContent",
	    value: function setContent(content) {
	      if (main_core.Type.isStringFilled(content) || main_core.Type.isDomNode(content) || main_core.Type.isArrayFilled(content)) {
	        this.content = content;
	      }
	    }
	  }]);
	  return DefaultFooter;
	}(BaseFooter);

	var RecentTab = /*#__PURE__*/function (_Tab) {
	  babelHelpers.inherits(RecentTab, _Tab);

	  function RecentTab(dialog, tabOptions) {
	    babelHelpers.classCallCheck(this, RecentTab);
	    var icon = 'data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2219%22%20height%3D%2219%22%20fill%3D' + '%22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M12.43%2011.985a.96.' + '96%200%2000-.959-.96H6.504a.96.96%200%20000%201.92h4.967c.53%200%20.96-.43.96-.96zM12.43%209.' + '009a.96.96%200%2000-.959-.96H6.504a.96.96%200%20000%201.92h4.967c.53%200%20.96-.43.96-.96zM12.' + '43%206.033a.96.96%200%2000-.959-.96H6.504a.96.96%200%20000%201.92h4.967c.53%200%20.96-.43.96-' + '.96z%22%20fill%3D%22%23ACB2B8%22/%3E%3Cpath%20fill-rule%3D%22evenodd%22%20clip-rule%3D%22' + 'evenodd%22%20d%3D%22M8.988%2017.52c1.799%200%203.468-.558%204.843-1.51l2.205%202.204a1.525%201.' + '525%200%20102.157-2.157l-2.205-2.205a8.512%208.512%200%2010-7%203.668zm0-2.403a6.108%206.108%200%2' + '0100-12.216%206.108%206.108%200%20000%2012.216z%22%20fill%3D%22%23ACB2B8%22/%3E%3C/svg%3E';
	    var defaults = {
	      title: main_core.Loc.getMessage('UI_SELECTOR_RECENT_TAB_TITLE'),
	      itemOrder: {
	        sort: 'asc'
	      },
	      icon: {
	        //default: '/bitrix/js/ui/entity-selector/src/css/images/recent-tab-icon.svg',
	        //selected: '/bitrix/js/ui/entity-selector/src/css/images/recent-tab-selected-icon.svg'
	        default: icon,
	        selected: icon.replace(/ACB2B8/g, 'fff')
	      }
	    };
	    var options = Object.assign({}, defaults, tabOptions);
	    options.id = 'recents';
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(RecentTab).call(this, dialog, options));
	  }

	  return RecentTab;
	}(Tab);

	var MatchResult = /*#__PURE__*/function () {
	  function MatchResult(item, queryWords) {
	    var matchIndexes = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : [];
	    babelHelpers.classCallCheck(this, MatchResult);
	    babelHelpers.defineProperty(this, "item", null);
	    babelHelpers.defineProperty(this, "queryWords", null);
	    babelHelpers.defineProperty(this, "matchFields", new Map());
	    this.item = item;
	    this.queryWords = queryWords;
	    this.addIndexes(matchIndexes);
	  }

	  babelHelpers.createClass(MatchResult, [{
	    key: "getItem",
	    value: function getItem() {
	      return this.item;
	    }
	  }, {
	    key: "getQueryWords",
	    value: function getQueryWords() {
	      return this.queryWords;
	    }
	  }, {
	    key: "getMatchFields",
	    value: function getMatchFields() {
	      return this.matchFields;
	    }
	  }, {
	    key: "addIndex",
	    value: function addIndex(matchIndex) {
	      var matchField = this.matchFields.get(matchIndex.getField());

	      if (!matchField) {
	        matchField = new MatchField(matchIndex.getField());
	        this.matchFields.set(matchIndex.getField(), matchField);
	      }

	      matchField.addIndex(matchIndex);
	    }
	  }, {
	    key: "addIndexes",
	    value: function addIndexes(matchIndexes) {
	      var _this = this;

	      matchIndexes.forEach(function (matchIndex) {
	        _this.addIndex(matchIndex);
	      });
	    }
	  }]);
	  return MatchResult;
	}();

	var MatchIndex = /*#__PURE__*/function () {
	  function MatchIndex(field, queryWord, startIndex) {
	    babelHelpers.classCallCheck(this, MatchIndex);
	    babelHelpers.defineProperty(this, "field", null);
	    babelHelpers.defineProperty(this, "queryWord", null);
	    babelHelpers.defineProperty(this, "startIndex", null);
	    babelHelpers.defineProperty(this, "endIndex", null);
	    this.field = field;
	    this.queryWord = queryWord;
	    this.startIndex = startIndex;
	    this.endIndex = startIndex + queryWord.length;
	  }

	  babelHelpers.createClass(MatchIndex, [{
	    key: "getField",
	    value: function getField() {
	      return this.field;
	    }
	  }, {
	    key: "getQueryWord",
	    value: function getQueryWord() {
	      return this.queryWord;
	    }
	  }, {
	    key: "getStartIndex",
	    value: function getStartIndex() {
	      return this.startIndex;
	    }
	  }, {
	    key: "getEndIndex",
	    value: function getEndIndex() {
	      return this.endIndex;
	    }
	  }]);
	  return MatchIndex;
	}();

	var collator = new Intl.Collator(undefined, {
	  sensitivity: 'base'
	});

	var SearchEngine = /*#__PURE__*/function () {
	  function SearchEngine() {
	    babelHelpers.classCallCheck(this, SearchEngine);
	  }

	  babelHelpers.createClass(SearchEngine, null, [{
	    key: "matchItems",
	    value: function matchItems(items, searchQuery) {
	      var _this = this;

	      var matchResults = [];
	      var queryWords = searchQuery.getQueryWords();
	      items.forEach(function (item) {
	        if (item.isSelected() || !item.isSearchable() || item.isHidden() || !item.getEntity().isSearchable()) {
	          return;
	        }

	        var matchResult = _this.matchItem(item, queryWords);

	        if (matchResult) {
	          matchResults.push(matchResult);
	        }
	      });
	      return matchResults;
	    }
	  }, {
	    key: "matchItem",
	    value: function matchItem(item, queryWords) {
	      var matches = [];

	      for (var i = 0; i < queryWords.length; i++) {
	        var queryWord = queryWords[i];
	        var results = this.matchWord(item, queryWord); //const match = this.matchWord(item, queryWord);
	        //if (match === null)

	        if (results.length === 0) {
	          return null;
	        } else {
	          matches = matches.concat(results); //matches.push(match);
	        }
	      }

	      if (matches.length > 0) {
	        return new MatchResult(item, queryWords, matches);
	      } else {
	        return null;
	      }
	    }
	  }, {
	    key: "matchWord",
	    value: function matchWord(item, queryWord) {
	      var searchIndexes = item.getSearchIndex().getIndexes();
	      var matches = [];

	      for (var i = 0; i < searchIndexes.length; i++) {
	        var fieldIndex = searchIndexes[i];
	        var indexes = fieldIndex.getIndexes();

	        for (var j = 0; j < indexes.length; j++) {
	          var index = indexes[j];
	          var word = index.getWord().substring(0, queryWord.length);

	          if (collator.compare(queryWord, word) === 0) {
	            matches.push(new MatchIndex(fieldIndex.getField(), queryWord, index.getStartIndex())); //return new MatchIndex(field, queryWord, index[i][1]);
	          }
	        }

	        if (matches.length > 0) {
	          break;
	        }
	      }

	      return matches; //return null;
	    }
	  }]);
	  return SearchEngine;
	}();

	var SearchQuery = /*#__PURE__*/function () {
	  function SearchQuery(query) {
	    babelHelpers.classCallCheck(this, SearchQuery);
	    babelHelpers.defineProperty(this, "queryWords", []);
	    babelHelpers.defineProperty(this, "query", '');
	    babelHelpers.defineProperty(this, "cacheable", true);
	    babelHelpers.defineProperty(this, "dynamicSearchEntities", []);
	    this.query = query.trim().replace(/\s\s+/g, ' ');
	    this.queryWords = main_core.Type.isStringFilled(this.query) ? this.query.split(' ') : [];
	  }

	  babelHelpers.createClass(SearchQuery, [{
	    key: "getQueryWords",
	    value: function getQueryWords() {
	      return this.queryWords;
	    }
	  }, {
	    key: "getQuery",
	    value: function getQuery() {
	      return this.query;
	    }
	  }, {
	    key: "isEmpty",
	    value: function isEmpty() {
	      return this.getQueryWords().length === 0;
	    }
	  }, {
	    key: "setCacheable",
	    value: function setCacheable(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.cacheable = flag;
	      }
	    }
	  }, {
	    key: "isCacheable",
	    value: function isCacheable() {
	      return this.cacheable;
	    }
	  }, {
	    key: "hasDynamicSearch",
	    value: function hasDynamicSearch() {
	      return this.getDynamicSearchEntities().length > 0;
	    }
	  }, {
	    key: "hasDynamicSearchEntity",
	    value: function hasDynamicSearchEntity(entityId) {
	      return this.getDynamicSearchEntities().includes(entityId);
	    }
	  }, {
	    key: "setDynamicSearchEntities",
	    value: function setDynamicSearchEntities(entities) {
	      var _this = this;

	      if (main_core.Type.isArrayFilled(entities)) {
	        entities.forEach(function (entityId) {
	          if (main_core.Type.isStringFilled(entityId) && !_this.hasDynamicSearchEntity(entityId)) {
	            _this.dynamicSearchEntities.push(entityId);
	          }
	        });
	      }

	      return this.dynamicSearchEntities;
	    }
	  }, {
	    key: "getDynamicSearchEntities",
	    value: function getDynamicSearchEntities() {
	      return this.dynamicSearchEntities;
	    }
	  }, {
	    key: "toJSON",
	    value: function toJSON() {
	      return {
	        queryWords: this.getQueryWords(),
	        query: this.getQuery(),
	        dynamicSearchEntities: this.getDynamicSearchEntities()
	      };
	    }
	  }]);
	  return SearchQuery;
	}();

	function _templateObject5$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-selector-search-loader-spacer\"></div>"]);

	  _templateObject5$4 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-search-loader-text\">", "</div>\n\t\t\t"]);

	  _templateObject4$4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-selector-search-loader-icon\"></div>"]);

	  _templateObject3$5 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-search-loader-box\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>"]);

	  _templateObject2$5 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$9() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-search-loader\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject$9 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var SearchLoader = /*#__PURE__*/function () {
	  function SearchLoader(tab) {
	    babelHelpers.classCallCheck(this, SearchLoader);
	    babelHelpers.defineProperty(this, "tab", null);
	    babelHelpers.defineProperty(this, "loader", null);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    this.tab = tab;
	  }

	  babelHelpers.createClass(SearchLoader, [{
	    key: "getTab",
	    value: function getTab() {
	      return this.tab;
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      if (this.loader === null) {
	        this.loader = new main_loader.Loader({
	          target: this.getIconContainer(),
	          size: 32
	        });
	      }

	      return this.loader;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      var _this = this;

	      return this.cache.remember('container', function () {
	        return main_core.Tag.render(_templateObject$9(), _this.getBoxContainer(), _this.getSpacerContainer());
	      });
	    }
	  }, {
	    key: "getBoxContainer",
	    value: function getBoxContainer() {
	      var _this2 = this;

	      return this.cache.remember('box-container', function () {
	        return main_core.Tag.render(_templateObject2$5(), _this2.getIconContainer(), _this2.getTextContainer());
	      });
	    }
	  }, {
	    key: "getIconContainer",
	    value: function getIconContainer() {
	      return this.cache.remember('icon', function () {
	        return main_core.Tag.render(_templateObject3$5());
	      });
	    }
	  }, {
	    key: "getTextContainer",
	    value: function getTextContainer() {
	      return this.cache.remember('text', function () {
	        return main_core.Tag.render(_templateObject4$4(), main_core.Loc.getMessage('UI_SELECTOR_SEARCH_LOADER_TEXT'));
	      });
	    }
	  }, {
	    key: "getSpacerContainer",
	    value: function getSpacerContainer() {
	      return this.cache.remember('spacer', function () {
	        return main_core.Tag.render(_templateObject5$4());
	      });
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      var _this3 = this;

	      if (!this.getContainer().parentNode) {
	        main_core.Dom.append(this.getContainer(), this.getTab().getContainer());
	      }

	      void this.getLoader().show();
	      main_core.Dom.addClass(this.getContainer(), 'ui-selector-search-loader--show');
	      requestAnimationFrame(function () {
	        main_core.Dom.addClass(_this3.getContainer(), 'ui-selector-search-loader--animate');
	      });
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (this.loader === null) {
	        return;
	      }

	      main_core.Dom.removeClass(this.getContainer(), ['ui-selector-search-loader--show', 'ui-selector-search-loader--animate']);
	      void this.getLoader().hide();
	    }
	  }, {
	    key: "isShown",
	    value: function isShown() {
	      return this.loader !== null && this.loader.isShown();
	    }
	  }]);
	  return SearchLoader;
	}();

	function _templateObject4$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-search-footer-loader\"></div>\n\t\t\t"]);

	  _templateObject4$5 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"ui-selector-search-footer-query\"></span>\n\t\t\t"]);

	  _templateObject3$6 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"ui-selector-search-footer-label\">", "</span>\n\t\t\t"]);

	  _templateObject2$6 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$a() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-selector-search-footer\" onclick=\"", "\">\n\t\t\t\t<div class=\"ui-selector-search-footer-box\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"ui-selector-search-footer-cmd\">", "</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject$a = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var SearchTabFooter = /*#__PURE__*/function (_BaseFooter) {
	  babelHelpers.inherits(SearchTabFooter, _BaseFooter);

	  function SearchTabFooter(tab, options) {
	    var _this;

	    babelHelpers.classCallCheck(this, SearchTabFooter);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SearchTabFooter).call(this, tab, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "loader", null);

	    _this.getDialog().subscribe('onSearch', _this.handleOnSearch.bind(babelHelpers.assertThisInitialized(_this)));

	    var tagSelector = _this.getDialog().getTagSelector();

	    if (tagSelector) {
	      tagSelector.subscribe('onMetaEnter', _this.handleMetaEnter.bind(babelHelpers.assertThisInitialized(_this)));
	    }

	    return _this;
	  }

	  babelHelpers.createClass(SearchTabFooter, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$a(), this.handleClick.bind(this), this.getLabelContainer(), this.getQueryContainer(), this.getLoaderContainer(), main_core.Browser.isMac() ? '&#8984;+Enter' : 'Ctrl+Enter');
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      if (this.loader === null) {
	        this.loader = new main_loader.Loader({
	          target: this.getLoaderContainer(),
	          size: 17,
	          color: 'rgba(82, 92, 105, 0.9)'
	        });
	      }

	      return this.loader;
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      this.getLoader().show();
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      this.getLoader().hide();
	    }
	  }, {
	    key: "setLabel",
	    value: function setLabel(label) {
	      if (main_core.Type.isString(label)) {
	        this.getLabelContainer().textContent = label;
	      }
	    }
	  }, {
	    key: "getLabelContainer",
	    value: function getLabelContainer() {
	      var _this2 = this;

	      return this.cache.remember('label', function () {
	        return main_core.Tag.render(_templateObject2$6(), _this2.getOption('label', main_core.Loc.getMessage('UI_SELECTOR_CREATE_ITEM_LABEL')));
	      });
	    }
	  }, {
	    key: "getQueryContainer",
	    value: function getQueryContainer() {
	      return this.cache.remember('name-container', function () {
	        return main_core.Tag.render(_templateObject3$6());
	      });
	    }
	  }, {
	    key: "getLoaderContainer",
	    value: function getLoaderContainer() {
	      return this.cache.remember('loader', function () {
	        return main_core.Tag.render(_templateObject4$5());
	      });
	    }
	  }, {
	    key: "createItem",
	    value: function createItem() {
	      var _this3 = this;

	      if (this.getDialog().getTagSelector().isLocked()) {
	        return;
	      }

	      var finalize = function finalize() {
	        _this3.hideLoader();

	        if (_this3.getDialog().getTagSelector()) {
	          _this3.getDialog().getTagSelector().unlock();

	          _this3.getDialog().focusSearch();
	        }
	      };

	      event.preventDefault();
	      this.showLoader();
	      this.getDialog().getTagSelector().lock();
	      this.getDialog().emitAsync('Search:onItemCreateAsync', {
	        searchQuery: this.getTab().getLastSearchQuery()
	      }).then(function () {
	        _this3.getTab().clearResults();

	        _this3.getDialog().clearSearch();

	        if (_this3.getDialog().getActiveTab() === _this3.getTab()) {
	          _this3.getDialog().selectFirstTab();
	        }

	        finalize();
	      }).catch(function () {
	        finalize();
	      });
	    }
	  }, {
	    key: "handleClick",
	    value: function handleClick() {
	      this.createItem();
	    }
	  }, {
	    key: "handleMetaEnter",
	    value: function handleMetaEnter(event) {
	      if (this.getDialog().getActiveTab() !== this.getTab()) {
	        return;
	      }

	      this.handleClick();
	    }
	  }, {
	    key: "handleOnSearch",
	    value: function handleOnSearch(event) {
	      var _event$getData = event.getData(),
	          query = _event$getData.query;

	      this.getQueryContainer().textContent = query;
	    }
	  }]);
	  return SearchTabFooter;
	}(BaseFooter);

	var SearchTab = /*#__PURE__*/function (_Tab) {
	  babelHelpers.inherits(SearchTab, _Tab);

	  function SearchTab(dialog, tabOptions, searchOptions) {
	    var _this;

	    babelHelpers.classCallCheck(this, SearchTab);
	    var defaults = {
	      title: main_core.Loc.getMessage('UI_SELECTOR_SEARCH_TAB_TITLE'),
	      visible: false,
	      stub: true,
	      stubOptions: {
	        autoShow: false,
	        title: main_core.Loc.getMessage('UI_SELECTOR_SEARCH_STUB_TITLE'),
	        subtitle: main_core.Loc.getMessage('UI_SELECTOR_SEARCH_STUB_SUBTITLE')
	      }
	    };
	    var options = Object.assign({}, defaults, tabOptions);
	    options.id = 'search';
	    options.stubOptions.autoShow = false;
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SearchTab).call(this, dialog, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "lastSearchQuery", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "loadWithDebounce", main_core.Runtime.debounce(_this.load, 500, babelHelpers.assertThisInitialized(_this)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "queryCache", new Set());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "queryXhr", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "searchLoader", new SearchLoader(babelHelpers.assertThisInitialized(_this)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "allowCreateItem", false);
	    searchOptions = main_core.Type.isPlainObject(searchOptions) ? searchOptions : {};

	    _this.setAllowCreateItem(searchOptions.allowCreateItem, searchOptions.footerOptions);

	    return _this;
	  }

	  babelHelpers.createClass(SearchTab, [{
	    key: "search",
	    value: function search(query) {
	      var searchQuery = new SearchQuery(query);
	      var dynamicEntities = this.getDynamicEntities(searchQuery);
	      searchQuery.setDynamicSearchEntities(dynamicEntities);

	      if (searchQuery.isEmpty()) {
	        this.getSearchLoader().hide();
	        return;
	      }

	      this.lastSearchQuery = searchQuery;
	      var matchResults = [];
	      this.getDialog().getItems().forEach(function (items) {
	        matchResults = matchResults.concat(SearchEngine.matchItems(items, searchQuery));
	      });
	      this.clearResults();
	      this.appendResults(matchResults);

	      if (this.getDialog().shouldFocusOnFirst()) {
	        this.getDialog().focusOnFirstNode();
	      }

	      if (this.shouldLoad(searchQuery)) {
	        this.loadWithDebounce(searchQuery);

	        if (!this.isEmptyResult()) {
	          this.getStub().hide();
	        }
	      } else {
	        if (!this.getSearchLoader().isShown()) {
	          this.toggleEmptyResult();
	        }
	      }
	    }
	  }, {
	    key: "getLastSearchQuery",
	    value: function getLastSearchQuery() {
	      return this.lastSearchQuery;
	    }
	  }, {
	    key: "setAllowCreateItem",
	    value: function setAllowCreateItem(flag, options) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.allowCreateItem = flag;

	        if (flag) {
	          this.setFooter(SearchTabFooter, options);
	        } else {
	          this.setFooter(null);
	        }
	      }
	    }
	  }, {
	    key: "canCreateItem",
	    value: function canCreateItem() {
	      return this.allowCreateItem;
	    }
	  }, {
	    key: "appendResults",
	    value: function appendResults(matchResults) {
	      var _this2 = this;

	      matchResults.sort(function (a, b) {
	        var contextSortA = a.getItem().getContextSort();
	        var contextSortB = b.getItem().getContextSort();

	        if (contextSortA !== null && contextSortB === null) {
	          return -1;
	        } else if (contextSortA === null && contextSortB !== null) {
	          return 1;
	        } else if (contextSortA !== null && contextSortB !== null) {
	          return contextSortB - contextSortA;
	        } else {
	          var globalSortA = a.getItem().getGlobalSort();
	          var globalSortB = b.getItem().getGlobalSort();

	          if (globalSortA !== null && globalSortB === null) {
	            return -1;
	          } else if (globalSortA === null && globalSortB !== null) {
	            return 1;
	          } else if (globalSortA !== null && globalSortB !== null) {
	            return globalSortB - globalSortA;
	          }

	          return 0;
	        }
	      });
	      this.getRootNode().disableRender();
	      matchResults.forEach(function (matchResult) {
	        var item = matchResult.getItem();

	        if (!_this2.getRootNode().hasItem(item)) {
	          var node = _this2.getRootNode().addItem(item);

	          node.setHighlights(matchResult.getMatchFields());
	        }
	      });
	      this.getRootNode().enableRender();
	      this.render();
	    }
	  }, {
	    key: "getDynamicEntities",
	    value: function getDynamicEntities(searchQuery) {
	      var result = [];
	      this.getDialog().getEntities().forEach(function (entity) {
	        if (entity.isSearchable()) {
	          var hasCacheLimit = entity.getSearchCacheLimits().some(function (pattern) {
	            return pattern.test(searchQuery.getQuery());
	          });

	          if (hasCacheLimit) {
	            result.push(entity.getId());
	          }
	        }
	      });
	      return result;
	    }
	  }, {
	    key: "isQueryCacheable",
	    value: function isQueryCacheable(searchQuery) {
	      return searchQuery.isCacheable() && !searchQuery.hasDynamicSearch();
	    }
	  }, {
	    key: "isQueryLoaded",
	    value: function isQueryLoaded(searchQuery) {
	      var found = false;
	      this.queryCache.forEach(function (query) {
	        if (found === false && searchQuery.getQuery().startsWith(query)) {
	          found = true;
	        }
	      });
	      return found;
	    }
	  }, {
	    key: "addCacheQuery",
	    value: function addCacheQuery(searchQuery) {
	      if (this.isQueryCacheable(searchQuery)) {
	        this.queryCache.add(searchQuery.getQuery());
	      }
	    }
	  }, {
	    key: "removeCacheQuery",
	    value: function removeCacheQuery(searchQuery) {
	      this.queryCache.delete(searchQuery.getQuery());
	    }
	  }, {
	    key: "shouldLoad",
	    value: function shouldLoad(searchQuery) {
	      if (!this.isQueryCacheable(searchQuery)) {
	        return true;
	      }

	      if (!this.getDialog().hasDynamicSearch()) {
	        return false;
	      }

	      return !this.isQueryLoaded(searchQuery);
	    }
	  }, {
	    key: "load",
	    value: function load(searchQuery) {
	      var _this3 = this;

	      if (!this.shouldLoad(searchQuery)) {
	        return;
	      }
	      /*if (this.queryXhr)
	      {
	      	this.queryXhr.abort();
	      }*/


	      this.addCacheQuery(searchQuery);
	      this.getStub().hide();
	      this.getSearchLoader().show();
	      main_core.ajax.runAction('ui.entityselector.doSearch', {
	        json: {
	          dialog: this.getDialog(),
	          searchQuery: searchQuery
	        },
	        onrequeststart: function onrequeststart(xhr) {
	          _this3.queryXhr = xhr;
	        },
	        getParameters: {
	          context: this.getDialog().getContext()
	        }
	      }).then(function (response) {
	        _this3.getSearchLoader().hide();

	        if (!response || !response.data || !response.data.dialog || !response.data.dialog.items) {
	          _this3.removeCacheQuery(searchQuery);

	          _this3.toggleEmptyResult();

	          return;
	        }

	        if (response.data.searchQuery && response.data.searchQuery.cacheable === false) {
	          _this3.removeCacheQuery(searchQuery);
	        }

	        if (main_core.Type.isArrayFilled(response.data.dialog.items)) {
	          var items = new Set();
	          response.data.dialog.items.forEach(function (itemOptions) {
	            delete itemOptions.tabs;
	            delete itemOptions.children;

	            var item = _this3.getDialog().addItem(itemOptions);

	            items.add(item);
	          });

	          var isTabEmpty = _this3.isEmptyResult();

	          var matchResults = SearchEngine.matchItems(items, _this3.getLastSearchQuery());

	          _this3.appendResults(matchResults);

	          if (isTabEmpty && _this3.getDialog().shouldFocusOnFirst()) {
	            _this3.getDialog().focusOnFirstNode();
	          }
	        }

	        _this3.toggleEmptyResult();
	      }).catch(function (error) {
	        _this3.removeCacheQuery(searchQuery);

	        _this3.getSearchLoader().hide();

	        _this3.toggleEmptyResult();

	        console.error(error);
	      });
	    }
	  }, {
	    key: "getSearchLoader",
	    value: function getSearchLoader() {
	      return this.searchLoader;
	    }
	  }, {
	    key: "clearResults",
	    value: function clearResults() {
	      this.getRootNode().removeChildren();
	    }
	  }, {
	    key: "isEmptyResult",
	    value: function isEmptyResult() {
	      return !this.getRootNode().hasChildren();
	    }
	  }, {
	    key: "toggleEmptyResult",
	    value: function toggleEmptyResult() {
	      if (this.isEmptyResult()) {
	        this.getStub().show();
	      } else {
	        this.getStub().hide();
	      }
	    }
	  }]);
	  return SearchTab;
	}(Tab);

	function _templateObject6$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-footer-container\">", "</div>\n\t\t\t"]);

	  _templateObject6$3 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div \n\t\t\t\t\tclass=\"ui-selector-tab-labels\"\n\t\t\t\t\tonmouseenter=\"", "\"\n\t\t\t\t\tonmouseleave=\"", "\"\n\t\t\t\t></div>\n\t\t\t"]);

	  _templateObject5$5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$6() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-selector-tab-contents\"></div>"]);

	  _templateObject4$6 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-tabs\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject3$7 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-selector-dialog\" style=\"width:", "px; height:", "px;\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject2$7 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$b() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-selector-search\"></div>"]);

	  _templateObject$b = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var LoadState = function LoadState() {
	  babelHelpers.classCallCheck(this, LoadState);
	};

	babelHelpers.defineProperty(LoadState, "UNSENT", 'UNSENT');
	babelHelpers.defineProperty(LoadState, "LOADING", 'LOADING');
	babelHelpers.defineProperty(LoadState, "DONE", 'DONE');

	var TagSelectorMode = function TagSelectorMode() {
	  babelHelpers.classCallCheck(this, TagSelectorMode);
	};

	babelHelpers.defineProperty(TagSelectorMode, "INSIDE", 'INSIDE');
	babelHelpers.defineProperty(TagSelectorMode, "OUTSIDE", 'OUTSIDE');
	var instances = new Map();
	/**
	 * @memberof BX.UI.EntitySelector
	 */

	var Dialog = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Dialog, _EventEmitter);
	  babelHelpers.createClass(Dialog, null, [{
	    key: "getById",
	    value: function getById(id) {
	      return instances.get(id) || null;
	    }
	  }]);

	  function Dialog(dialogOptions) {
	    var _this;

	    babelHelpers.classCallCheck(this, Dialog);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Dialog).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "id", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "items", new Map());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tabs", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "entities", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "targetNode", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "popup", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "cache", new main_core.Cache.MemoryCache());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "multiple", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "hideOnSelect", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "hideOnDeselect", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "context", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "selectedItems", new Set());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "preselectedItems", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "undeselectedItems", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "dropdownMode", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "frozen", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "frozenProps", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "hideByEsc", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "autoHide", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "offsetTop", 5);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "offsetLeft", 0);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "zIndex", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "cacheable", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "width", 565);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "height", 420);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "maxLabelWidth", 160);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "minLabelWidth", 40);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "activeTab", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "recentTab", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "searchTab", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "rendered", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "loadState", LoadState.UNSENT);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "loader", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagSelector", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagSelectorMode", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagSelectorHeight", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "saveRecentItemsWithDebounce", main_core.Runtime.debounce(_this.saveRecentItems, 2000, babelHelpers.assertThisInitialized(_this)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "recentItemsToSave", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "navigation", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "footer", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "popupOptions", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "focusOnFirst", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "focusedNode", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "clearUnavailableItems", false);

	    _this.setEventNamespace('BX.UI.EntitySelector.Dialog');

	    var options = main_core.Type.isPlainObject(dialogOptions) ? dialogOptions : {};
	    _this.id = main_core.Type.isStringFilled(options.id) ? options.id : "ui-selector-".concat(main_core.Text.getRandom().toLowerCase());
	    _this.multiple = main_core.Type.isBoolean(options.multiple) ? options.multiple : true;
	    _this.context = main_core.Type.isStringFilled(options.context) ? options.context : null;
	    _this.clearUnavailableItems = options.clearUnavailableItems === true;

	    if (main_core.Type.isArray(options.entities)) {
	      options.entities.forEach(function (entity) {
	        _this.addEntity(entity);
	      });
	    }

	    if (options.enableSearch === true) {
	      var defaultOptions = {
	        placeholder: main_core.Loc.getMessage('UI_TAG_SELECTOR_SEARCH_PLACEHOLDER'),
	        maxHeight: 99,
	        textBoxWidth: 105
	      };
	      var customOptions = main_core.Type.isPlainObject(options.tagSelectorOptions) ? options.tagSelectorOptions : {};
	      var mandatoryOptions = {
	        dialogOptions: null,
	        showTextBox: true,
	        showAddButton: false,
	        showCreateButton: false,
	        multiple: _this.isMultiple()
	      };
	      var tagSelectorOptions = Object.assign(defaultOptions, customOptions, mandatoryOptions);
	      var tagSelector = new TagSelector(tagSelectorOptions);
	      _this.tagSelectorMode = TagSelectorMode.INSIDE;

	      _this.setTagSelector(tagSelector);
	    } else if (options.tagSelector instanceof TagSelector) {
	      _this.tagSelectorMode = TagSelectorMode.OUTSIDE;

	      _this.setTagSelector(options.tagSelector);
	    }

	    _this.setTargetNode(options.targetNode);

	    _this.setHideOnSelect(options.hideOnSelect);

	    _this.setHideOnDeselect(options.hideOnDeselect);

	    _this.setWidth(options.width);

	    _this.setHeight(options.height);

	    _this.setAutoHide(options.autoHide);

	    _this.setHideByEsc(options.hideByEsc);

	    _this.setOffsetLeft(options.offsetLeft);

	    _this.setOffsetTop(options.offsetTop);

	    _this.setZindex(options.zIndex);

	    _this.setCacheable(options.cacheable);

	    _this.setFocusOnFirst(options.focusOnFirst);

	    _this.recentTab = new RecentTab(babelHelpers.assertThisInitialized(_this), options.recentTabOptions);
	    _this.searchTab = new SearchTab(babelHelpers.assertThisInitialized(_this), options.searchTabOptions, options.searchOptions);

	    _this.addTab(_this.recentTab);

	    _this.addTab(_this.searchTab);

	    _this.setDropdownMode(options.dropdownMode);

	    _this.setPreselectedItems(options.preselectedItems);

	    _this.setUndeselectedItems(options.undeselectedItems);

	    _this.setOptions(options);

	    var preload = options.preload === true || _this.getPreselectedItems().length > 0;

	    if (preload) {
	      _this.load();
	    }

	    if (main_core.Type.isPlainObject(options.popupOptions)) {
	      var allowedOptions = ['overlay', 'bindOptions'];
	      var popupOptions = {};
	      Object.keys(options.popupOptions).forEach(function (option) {
	        if (allowedOptions.includes(option)) {
	          popupOptions[option] = options.popupOptions[option];
	        }
	      });
	      _this.popupOptions = popupOptions;
	    }

	    _this.navigation = new Navigation(babelHelpers.assertThisInitialized(_this));
	    new SliderIntegration(babelHelpers.assertThisInitialized(_this));

	    _this.subscribe('ItemNode:onFocus', _this.handleItemNodeFocus.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.subscribe('ItemNode:onUnfocus', _this.handleItemNodeUnfocus.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.subscribeFromOptions(options.events);

	    instances.set(_this.id, babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(Dialog, [{
	    key: "setOptions",
	    value: function setOptions(dialogOptions) {
	      var _this2 = this;

	      var options = main_core.Type.isPlainObject(dialogOptions) ? dialogOptions : {};

	      if (main_core.Type.isArray(options.tabs)) {
	        options.tabs.forEach(function (tab) {
	          _this2.addTab(tab);
	        });
	      }

	      if (main_core.Type.isArray(options.selectedItems)) {
	        options.selectedItems.forEach(function (itemOptions) {
	          var options = Object.assign({}, main_core.Type.isPlainObject(itemOptions) ? itemOptions : {});
	          options.selected = true;

	          _this2.addItem(options);
	        });
	      }

	      if (main_core.Type.isArray(options.items)) {
	        options.items.forEach(function (itemOptions) {
	          _this2.addItem(itemOptions);
	        });
	      }

	      this.setFooter(options.footer, options.footerOptions);
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getContext",
	    value: function getContext() {
	      return this.context;
	    }
	  }, {
	    key: "getNavigation",
	    value: function getNavigation() {
	      return this.navigation;
	    }
	  }, {
	    key: "getFooter",
	    value: function getFooter() {
	      return this.footer;
	    }
	  }, {
	    key: "getActiveFooter",
	    value: function getActiveFooter() {
	      if (!this.getActiveTab()) {
	        return null;
	      }

	      if (this.getActiveTab().getFooter()) {
	        return this.getActiveTab().getFooter();
	      }

	      return this.getFooter() && this.getActiveTab().canShowDefaultFooter() ? this.getFooter() : null;
	    }
	  }, {
	    key: "adjustFooter",
	    value: function adjustFooter() {
	      if (!this.getActiveTab()) {
	        return;
	      }

	      if (this.getActiveTab().getFooter()) {
	        if (this.getFooter()) {
	          this.getFooter().hide();
	        }

	        this.getActiveTab().getFooter().show();
	      } else {
	        if (this.getFooter()) {
	          if (this.getActiveTab().canShowDefaultFooter()) {
	            this.getFooter().show();
	          } else {
	            this.getFooter().hide();
	          }
	        }
	      }
	    }
	  }, {
	    key: "setFooter",
	    value: function setFooter(footerContent, footerOptions) {
	      /** @var {BaseFooter} */
	      var footer = null;

	      if (footerContent !== null) {
	        footer = this.constructor.createFooter(this, footerContent, footerOptions);

	        if (footer === null) {
	          return;
	        }
	      }

	      if (this.isRendered() && this.getFooter() !== null) {
	        main_core.Dom.remove(this.getFooter().getContainer());
	        this.adjustFooter();
	      }

	      this.footer = footer;

	      if (this.isRendered()) {
	        this.appendFooter(footer);
	        this.adjustFooter();
	      }
	    }
	  }, {
	    key: "appendFooter",
	    value: function appendFooter(footer) {
	      if (footer instanceof BaseFooter) {
	        main_core.Dom.append(footer.getContainer(), this.getFooterContainer());
	      }
	    }
	  }, {
	    key: "addTab",
	    value: function addTab(tab) {
	      if (main_core.Type.isPlainObject(tab)) {
	        tab = new Tab(this, tab);
	      }

	      if (!(tab instanceof Tab)) {
	        throw new Error('EntitySelector: a tab must be an instance of EntitySelector.Tab.');
	      }

	      if (this.getTab(tab.getId())) {
	        console.error("EntitySelector: the \"".concat(tab.getId(), "\" tab is already existed."));
	        return tab;
	      }

	      tab.setDialog(this);
	      this.tabs.push(tab);

	      if (this.isRendered()) {
	        this.insertTab(tab);
	      }

	      return tab;
	    }
	  }, {
	    key: "getTabs",
	    value: function getTabs() {
	      return this.tabs;
	    }
	  }, {
	    key: "getTab",
	    value: function getTab(id) {
	      if (!main_core.Type.isStringFilled(id)) {
	        return null;
	      }

	      var tab = this.getTabs().find(function (tab) {
	        return tab.getId() === id;
	      });
	      return tab || null;
	    }
	  }, {
	    key: "getRecentTab",
	    value: function getRecentTab() {
	      return this.recentTab;
	    }
	  }, {
	    key: "getSearchTab",
	    value: function getSearchTab() {
	      return this.searchTab;
	    }
	  }, {
	    key: "selectTab",
	    value: function selectTab(id) {
	      var newActiveTab = this.getTab(id);

	      if (!newActiveTab || newActiveTab === this.getActiveTab()) {
	        return newActiveTab;
	      }

	      if (this.getActiveTab()) {
	        this.getActiveTab().deselect();
	      }

	      this.activeTab = newActiveTab;
	      newActiveTab.select();

	      if (!newActiveTab.isRendered()) {
	        newActiveTab.render();
	      }

	      this.focusSearch();
	      this.clearNodeFocus();

	      if (this.shouldFocusOnFirst()) {
	        this.focusOnFirstNode();
	      }

	      this.adjustFooter();
	      return newActiveTab;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "insertTab",
	    value: function insertTab(tab) {
	      tab.renderLabel();
	      main_core.Dom.append(tab.getLabelContainer(), this.getLabelsContainer());
	      main_core.Dom.append(tab.getContainer(), this.getTabContentsContainer());

	      if (tab.getFooter()) {
	        main_core.Dom.append(tab.getFooter().getContainer(), this.getFooterContainer());
	      }
	    }
	  }, {
	    key: "selectFirstTab",
	    value: function selectFirstTab() {
	      var onlyVisible = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	      for (var i = 0; i < this.getTabs().length; i++) {
	        var tab = this.getTabs()[i];

	        if (onlyVisible === false || tab.isVisible()) {
	          return this.selectTab(tab.getId());
	        }
	      }

	      if (this.isDropdownMode()) {
	        return this.selectTab(this.getRecentTab().getId());
	      }

	      return null;
	    }
	  }, {
	    key: "selectLastTab",
	    value: function selectLastTab() {
	      var onlyVisible = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	      for (var i = this.getTabs().length - 1; i >= 0; i--) {
	        var tab = this.getTabs()[i];

	        if (onlyVisible === false || tab.isVisible()) {
	          return this.selectTab(tab.getId());
	        }
	      }

	      if (this.isDropdownMode()) {
	        return this.selectTab(this.getRecentTab().getId());
	      }

	      return null;
	    }
	  }, {
	    key: "getActiveTab",
	    value: function getActiveTab() {
	      return this.activeTab;
	    }
	  }, {
	    key: "getNextTab",
	    value: function getNextTab() {
	      var onlyVisible = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      var nextTab = null;
	      var activeFound = false;

	      for (var i = 0; i < this.getTabs().length; i++) {
	        var tab = this.getTabs()[i];

	        if (onlyVisible && !tab.isVisible()) {
	          continue;
	        }

	        if (tab === this.getActiveTab()) {
	          activeFound = true;
	        } else if (activeFound) {
	          nextTab = tab;
	          break;
	        }
	      }

	      return nextTab;
	    }
	  }, {
	    key: "getPreviousTab",
	    value: function getPreviousTab() {
	      var onlyVisible = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      var previousTab = null;
	      var activeFound = false;

	      for (var i = this.getTabs().length - 1; i >= 0; i--) {
	        var tab = this.getTabs()[i];

	        if (onlyVisible && !tab.isVisible()) {
	          continue;
	        }

	        if (tab === this.getActiveTab()) {
	          activeFound = true;
	        } else if (activeFound) {
	          previousTab = tab;
	          break;
	        }
	      }

	      return previousTab;
	    }
	  }, {
	    key: "removeTab",
	    value: function removeTab(id) {
	      var tab = this.getTab(id);

	      if (!tab) {
	        return;
	      }

	      tab.removeNodes();
	      this.tabs = this.tabs.filter(function (el) {
	        return tab.getId() !== el.getId();
	      });
	      main_core.Dom.remove(tab.getLabelContainer(), this.getLabelsContainer());
	      main_core.Dom.remove(tab.getContainer(), this.getTabContentsContainer());

	      if (tab.getFooter()) {
	        main_core.Dom.remove(tab.getFooter().getContainer(), this.getFooterContainer());
	      }

	      this.selectFirstTab();
	    }
	  }, {
	    key: "getItem",
	    value: function getItem(item) {
	      var id = null;
	      var entityId = null;

	      if (main_core.Type.isArray(item) && item.length === 2) {
	        var _item = babelHelpers.slicedToArray(item, 2);

	        entityId = _item[0];
	        id = _item[1];
	      } else if (item instanceof Item) {
	        id = item.getId();
	        entityId = item.getEntityId();
	      } else if (main_core.Type.isObjectLike(item)) {
	        id = item.id;
	        entityId = item.entityId;
	      }

	      var entityItems = this.getEntityItems(entityId);

	      if (entityItems) {
	        return entityItems.get(String(id)) || null;
	      }

	      return null;
	    }
	  }, {
	    key: "getItems",
	    value: function getItems() {
	      return this.items;
	    }
	  }, {
	    key: "getSelectedItems",
	    value: function getSelectedItems() {
	      return Array.from(this.selectedItems);
	    }
	  }, {
	    key: "setPreselectedItems",
	    value: function setPreselectedItems(itemIds) {
	      this.preselectedItems = this.validateItemIds(itemIds);
	    }
	  }, {
	    key: "getPreselectedItems",
	    value: function getPreselectedItems() {
	      return this.preselectedItems;
	    }
	  }, {
	    key: "setUndeselectedItems",
	    value: function setUndeselectedItems(itemIds) {
	      this.undeselectedItems = this.validateItemIds(itemIds);
	    }
	  }, {
	    key: "getUndeselectedItems",
	    value: function getUndeselectedItems() {
	      return this.undeselectedItems;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "validateItemIds",
	    value: function validateItemIds(itemIds) {
	      if (!main_core.Type.isArrayFilled(itemIds)) {
	        return [];
	      }

	      var result = [];
	      itemIds.forEach(function (itemId) {
	        if (!main_core.Type.isArray(itemId) || itemId.length !== 2) {
	          return;
	        }

	        var _itemId = babelHelpers.slicedToArray(itemId, 2),
	            entityId = _itemId[0],
	            id = _itemId[1];

	        if (main_core.Type.isStringFilled(entityId) && (main_core.Type.isStringFilled(id) || main_core.Type.isNumber(id))) {
	          result.push(itemId);
	        }
	      });
	      return result;
	    }
	  }, {
	    key: "getEntityItems",
	    value: function getEntityItems(entityId) {
	      return this.items.get(entityId) || null;
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(options) {
	      var _this3 = this;

	      if (!main_core.Type.isPlainObject(options)) {
	        throw new Error('EntitySelector.addItem: wrong item options.');
	      }

	      var item = this.getItem(options);

	      if (!item) {
	        item = new Item(options);
	        var undeselectable = this.getUndeselectedItems().some(function (itemId) {
	          return itemId[0] === item.getEntityId() && itemId[1] === item.getId();
	        });

	        if (undeselectable) {
	          item.setDeselectable(false);
	        }

	        item.setDialog(this);
	        var entity = this.getEntity(item.getEntityId());

	        if (entity === null) {
	          this.addEntity({
	            id: item.getEntityId()
	          });
	        }

	        var entityItems = this.items.get(item.getEntityId());

	        if (!entityItems) {
	          entityItems = new Map();
	          this.items.set(item.getEntityId(), entityItems);
	        }

	        entityItems.set(String(item.getId()), item);

	        if (item.isSelected()) {
	          this.handleItemSelect(item);
	        }
	      }

	      var tabs = [];

	      if (main_core.Type.isArray(options.tabs)) {
	        tabs = options.tabs;
	      } else if (main_core.Type.isStringFilled(options.tabs)) {
	        tabs = [options.tabs];
	      }

	      var children = main_core.Type.isArray(options.children) ? options.children : [];
	      tabs.forEach(function (tabId) {
	        var tab = _this3.getTab(tabId);

	        if (tab) {
	          var itemNode = tab.getRootNode().addItem(item, options.nodeOptions);
	          itemNode.addChildren(children);
	        }
	      });
	      return item;
	    }
	  }, {
	    key: "removeItem",
	    value: function removeItem(item) {
	      item = this.getItem(item);

	      if (item) {
	        var entityItems = this.getEntityItems(item.getEntityId());

	        if (entityItems) {
	          entityItems.delete(item.getId());

	          if (entityItems.size === 0) {
	            this.items.delete(item.getEntityId());
	          }
	        }

	        item.getNodes().forEach(function (node) {
	          node.getParentNode().removeChild(node);
	        });
	      }

	      return item;
	    }
	  }, {
	    key: "deselectAll",
	    value: function deselectAll() {
	      this.getSelectedItems().forEach(function (item) {
	        item.deselect();
	      });
	    }
	  }, {
	    key: "isMultiple",
	    value: function isMultiple() {
	      return this.multiple;
	    }
	  }, {
	    key: "setHideOnSelect",
	    value: function setHideOnSelect(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.hideOnSelect = flag;
	      }
	    }
	  }, {
	    key: "shouldHideOnSelect",
	    value: function shouldHideOnSelect() {
	      if (this.hideOnSelect !== null) {
	        return this.hideOnSelect;
	      }

	      return !this.isMultiple();
	    }
	  }, {
	    key: "setHideOnDeselect",
	    value: function setHideOnDeselect(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.hideOnDeselect = flag;
	      }
	    }
	  }, {
	    key: "shouldHideOnDeselect",
	    value: function shouldHideOnDeselect() {
	      if (this.hideOnDeselect !== null) {
	        return this.hideOnDeselect;
	      }

	      return false;
	    }
	  }, {
	    key: "addEntity",
	    value: function addEntity(entity) {
	      if (main_core.Type.isPlainObject(entity)) {
	        entity = new Entity(entity);
	      }

	      if (!(entity instanceof Entity)) {
	        throw new Error('EntitySelector: an entity must be an instance of EntitySelector.Entity.');
	      }

	      if (this.hasEntity(entity.getId())) {
	        console.error("EntitySelector: the \"".concat(entity.getId(), "\" entity is already existed."));
	        return;
	      }

	      this.entities.push(entity);
	    }
	  }, {
	    key: "getEntity",
	    value: function getEntity(id) {
	      return this.getEntities().find(function (entity) {
	        return entity.getId() === id;
	      }) || null;
	    }
	  }, {
	    key: "hasEntity",
	    value: function hasEntity(id) {
	      return this.getEntities().some(function (entity) {
	        return entity.getId() === id;
	      });
	    }
	  }, {
	    key: "getEntities",
	    value: function getEntities() {
	      return this.entities;
	    }
	  }, {
	    key: "removeEntity",
	    value: function removeEntity(id) {
	      var index = this.getEntities().find(function (entity) {
	        return entity.getId() === id;
	      });

	      if (index >= 0) {
	        this.getEntities().splice(index, 1);
	      }
	    }
	  }, {
	    key: "getTagSelector",
	    value: function getTagSelector() {
	      return this.tagSelector;
	    }
	  }, {
	    key: "getTagSelectorMode",
	    value: function getTagSelectorMode() {
	      return this.tagSelectorMode;
	    }
	  }, {
	    key: "isTagSelectorInside",
	    value: function isTagSelectorInside() {
	      return this.getTagSelector() && this.getTagSelectorMode() === TagSelectorMode.INSIDE;
	    }
	  }, {
	    key: "isTagSelectorOutside",
	    value: function isTagSelectorOutside() {
	      return this.getTagSelector() && this.getTagSelectorMode() === TagSelectorMode.OUTSIDE;
	    }
	  }, {
	    key: "getTagSelectorQuery",
	    value: function getTagSelectorQuery() {
	      return this.getTagSelector() ? this.getTagSelector().getTextBoxValue() : '';
	    }
	  }, {
	    key: "setTagSelector",
	    value: function setTagSelector(tagSelector) {
	      this.tagSelector = tagSelector;
	      this.tagSelector.subscribe('onInput', main_core.Runtime.debounce(this.handleTagSelectorInput, 200, this));
	      this.tagSelector.subscribe('onAddButtonClick', this.handleTagSelectorAddButtonClick.bind(this));
	      this.tagSelector.subscribe('onTagRemove', this.handleTagSelectorTagRemove.bind(this));
	      this.tagSelector.subscribe('onAfterTagRemove', this.handleTagSelectorAfterTagRemove.bind(this));
	      this.tagSelector.subscribe('onAfterTagAdd', this.handleTagSelectorAfterTagAdd.bind(this));
	      this.tagSelector.subscribe('onContainerClick', this.handleTagSelectorClick.bind(this));
	      this.tagSelector.setDialog(this);
	    }
	  }, {
	    key: "focusSearch",
	    value: function focusSearch() {
	      if (this.getTagSelector()) {
	        if (this.getActiveTab() !== this.getSearchTab()) {
	          this.getTagSelector().clearTextBox();
	        }

	        this.getTagSelector().focusTextBox();
	      }
	    }
	  }, {
	    key: "clearSearch",
	    value: function clearSearch() {
	      if (this.getTagSelector()) {
	        this.getTagSelector().clearTextBox();
	      }
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      if (this.loader === null) {
	        this.loader = new main_loader.Loader({
	          target: this.getTabsContainer(),
	          size: 100
	        });
	      }

	      return this.loader;
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      this.getLoader().show();
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      if (this.loader !== null) {
	        this.getLoader().hide();
	      }
	    }
	  }, {
	    key: "destroyLoader",
	    value: function destroyLoader() {
	      if (this.loader !== null) {
	        this.getLoader().destroy();
	      }

	      this.loader = null;
	    }
	  }, {
	    key: "handleTagSelectorInput",
	    value: function handleTagSelectorInput() {
	      if (this.getTagSelectorMode() === TagSelectorMode.OUTSIDE && !this.isOpen()) {
	        this.show();
	      }

	      var query = this.getTagSelector().getTextBoxValue();
	      this.search(query);
	    }
	  }, {
	    key: "handleTagSelectorAddButtonClick",
	    value: function handleTagSelectorAddButtonClick() {
	      this.show();
	    }
	  }, {
	    key: "handleTagSelectorTagRemove",
	    value: function handleTagSelectorTagRemove(event) {
	      var _event$getData = event.getData(),
	          tag = _event$getData.tag;

	      var item = this.getItem({
	        id: tag.getId(),
	        entityId: tag.getEntityId()
	      });

	      if (item) {
	        item.deselect();
	      }

	      this.focusSearch();
	    }
	  }, {
	    key: "handleTagSelectorAfterTagRemove",
	    value: function handleTagSelectorAfterTagRemove() {
	      this.adjustByTagSelector();
	    }
	  }, {
	    key: "handleTagSelectorAfterTagAdd",
	    value: function handleTagSelectorAfterTagAdd() {
	      this.adjustByTagSelector();
	    }
	  }, {
	    key: "handleTagSelectorClick",
	    value: function handleTagSelectorClick() {
	      this.focusSearch();
	    }
	  }, {
	    key: "adjustByTagSelector",
	    value: function adjustByTagSelector() {
	      var _this4 = this;

	      if (this.getTagSelectorMode() === TagSelectorMode.OUTSIDE) {
	        this.adjustPosition();
	      } else if (this.getTagSelectorMode() === TagSelectorMode.INSIDE) {
	        var newTagSelectorHeight = this.getTagSelector().calcHeight();

	        if (newTagSelectorHeight > 0) {
	          var offset = newTagSelectorHeight - (this.tagSelectorHeight || this.getTagSelector().getMinHeight());
	          this.tagSelectorHeight = newTagSelectorHeight;

	          if (offset !== 0) {
	            var height = this.getHeight();
	            this.setHeight(height + offset).then(function () {
	              _this4.adjustPosition();
	            });
	          }
	        }
	      }
	    }
	  }, {
	    key: "observeTabOverlapping",
	    value: function observeTabOverlapping() {
	      var _this5 = this;

	      var observer = new MutationObserver(function () {
	        if (_this5.getTabs().some(function (tab) {
	          return tab.isVisible();
	        })) {
	          var left = parseInt(_this5.getPopup().getPopupContainer().style.left, 10);

	          if (left < _this5.getMinLabelWidth()) {
	            main_core.Dom.style(_this5.getPopup().getPopupContainer(), 'left', "".concat(_this5.getMinLabelWidth(), "px"));
	          }
	        }
	      });
	      observer.observe(this.getPopup().getPopupContainer(), {
	        attributes: true,
	        attributeFilter: ['style']
	      });
	    }
	    /**
	     * @internal
	     */

	  }, {
	    key: "handleItemSelect",
	    value: function handleItemSelect(item) {
	      var animate = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

	      if (!this.isMultiple()) {
	        this.deselectAll();
	      }

	      if (this.getTagSelector() && (this.isMultiple() || this.isTagSelectorOutside())) {
	        var tag = item.createTag();
	        tag.animate = animate;
	        this.getTagSelector().addTag(tag);
	      }

	      this.selectedItems.add(item);
	    }
	    /**
	     * @internal
	     */

	  }, {
	    key: "handleItemDeselect",
	    value: function handleItemDeselect(item) {
	      this.selectedItems.delete(item);
	    }
	  }, {
	    key: "handlePopupAfterShow",
	    value: function handlePopupAfterShow() {
	      this.focusSearch();
	      this.adjustByTagSelector();
	      this.emit('onShow');
	    }
	  }, {
	    key: "handlePopupFirstShow",
	    value: function handlePopupFirstShow() {
	      var _this6 = this;

	      this.emit('onFirstShow');
	      requestAnimationFrame(function () {
	        requestAnimationFrame(function () {
	          main_core.Dom.addClass(_this6.getPopup().getPopupContainer(), 'ui-selector-popup-container');
	        });
	      });
	      this.observeTabOverlapping();
	    }
	  }, {
	    key: "handlePopupClose",
	    value: function handlePopupClose() {
	      if (this.isTagSelectorOutside()) {
	        if (this.getActiveTab() && this.getActiveTab() === this.getSearchTab()) {
	          this.selectFirstTab();
	        }

	        this.getTagSelector().clearTextBox();
	        this.getTagSelector().showAddButton();
	        this.getTagSelector().hideTextBox();
	      }

	      this.emit('onHide');
	    }
	  }, {
	    key: "handlePopupDestroy",
	    value: function handlePopupDestroy() {
	      this.destroy();
	    }
	  }, {
	    key: "handleLabelsMouseEnter",
	    value: function handleLabelsMouseEnter() {
	      var _this7 = this;

	      var rect = main_core.Dom.getPosition(this.getLabelsContainer());
	      var freeSpace = rect.right;

	      if (freeSpace > this.getMinLabelWidth()) {
	        main_core.Dom.removeClass(this.getLabelsContainer(), 'ui-selector-tab-labels--animate-hide');
	        main_core.Dom.addClass(this.getLabelsContainer(), 'ui-selector-tab-labels--animate-show');
	        main_core.Dom.style(this.getLabelsContainer(), 'max-width', "".concat(Math.min(freeSpace, this.getMaxLabelWidth()), "px"));
	        Animation.handleTransitionEnd(this.getLabelsContainer(), 'max-width').then(function () {
	          main_core.Dom.removeClass(_this7.getLabelsContainer(), 'ui-selector-tab-labels--animate-show');
	          main_core.Dom.addClass(_this7.getLabelsContainer(), 'ui-selector-tab-labels--active');
	        });
	      } else {
	        main_core.Dom.addClass(this.getLabelsContainer(), 'ui-selector-tab-labels--active');
	      }
	    }
	  }, {
	    key: "handleLabelsMouseLeave",
	    value: function handleLabelsMouseLeave() {
	      var _this8 = this;

	      main_core.Dom.addClass(this.getLabelsContainer(), 'ui-selector-tab-labels--animate-hide');
	      main_core.Dom.removeClass(this.getLabelsContainer(), 'ui-selector-tab-labels--animate-show');
	      main_core.Dom.removeClass(this.getLabelsContainer(), 'ui-selector-tab-labels--active');
	      Animation.handleTransitionEnd(this.getLabelsContainer(), 'max-width').then(function () {
	        main_core.Dom.removeClass(_this8.getLabelsContainer(), 'ui-selector-tab-labels--animate-hide');
	      });
	      main_core.Dom.style(this.getLabelsContainer(), 'max-width', null);
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      this.load();
	      this.getPopup().show();
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      this.getPopup().close();
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.destroying) {
	        return;
	      }

	      this.destroying = true;
	      this.emit('onDestroy');
	      instances.delete(this.getId());

	      if (this.isRendered()) {
	        this.getPopup().destroy();
	      }

	      for (var property in this) {
	        if (this.hasOwnProperty(property)) {
	          delete this[property];
	        }
	      }

	      Object.setPrototypeOf(this, null);
	    }
	  }, {
	    key: "isOpen",
	    value: function isOpen() {
	      return this.popup && this.popup.isShown();
	    }
	  }, {
	    key: "setTargetNode",
	    value: function setTargetNode(node) {
	      if (!main_core.Type.isDomNode(node) && !main_core.Type.isNull(node) && !main_core.Type.isObject(node)) {
	        return;
	      }

	      this.targetNode = node;

	      if (this.isRendered()) {
	        this.getPopup().setBindElement(this.targetNode);
	        this.getPopup().adjustPosition();
	      }
	    }
	  }, {
	    key: "getTargetNode",
	    value: function getTargetNode() {
	      if (this.targetNode === null) {
	        if (this.getTagSelectorMode() === TagSelectorMode.OUTSIDE) {
	          return this.getTagSelector().getOuterContainer();
	        }
	      }

	      return this.targetNode;
	    }
	  }, {
	    key: "adjustPosition",
	    value: function adjustPosition() {
	      if (this.isRendered()) {
	        this.getPopup().adjustPosition();
	      }
	    }
	  }, {
	    key: "setAutoHide",
	    value: function setAutoHide(enable) {
	      if (main_core.Type.isBoolean(enable)) {
	        this.autoHide = enable;

	        if (this.isRendered()) {
	          this.getPopup().setAutoHide(enable);
	        }
	      }
	    }
	  }, {
	    key: "isAutoHide",
	    value: function isAutoHide() {
	      return this.autoHide;
	    }
	  }, {
	    key: "setHideByEsc",
	    value: function setHideByEsc(enable) {
	      if (main_core.Type.isBoolean(enable)) {
	        this.hideByEsc = enable;

	        if (this.isRendered()) {
	          this.getPopup().setClosingByEsc(enable);
	        }
	      }
	    }
	  }, {
	    key: "shouldHideByEsc",
	    value: function shouldHideByEsc() {
	      return this.hideByEsc;
	    }
	  }, {
	    key: "freeze",
	    value: function freeze() {
	      if (this.isFrozen()) {
	        return;
	      }

	      this.frozenProps = {
	        autoHide: this.isAutoHide(),
	        hideByEsc: this.shouldHideByEsc()
	      };
	      this.setAutoHide(false);
	      this.setHideByEsc(false);
	      this.getNavigation().disable();
	      main_core.Dom.addClass(this.getContainer(), 'ui-selector-dialog--freeze');
	      this.frozen = true;
	    }
	  }, {
	    key: "unfreeze",
	    value: function unfreeze() {
	      if (!this.isFrozen()) {
	        return;
	      }

	      this.setAutoHide(this.frozenProps.autoHide !== false);
	      this.setHideByEsc(this.frozenProps.hideByEsc !== false);
	      this.getNavigation().enable();
	      main_core.Dom.removeClass(this.getContainer(), 'ui-selector-dialog--freeze');
	      this.frozen = false;
	    }
	  }, {
	    key: "isFrozen",
	    value: function isFrozen() {
	      return this.frozen;
	    }
	  }, {
	    key: "getWidth",
	    value: function getWidth() {
	      return this.width;
	    }
	  }, {
	    key: "setWidth",
	    value: function setWidth(width) {
	      if (main_core.Type.isNumber(width) && width > 0) {
	        this.width = width;

	        if (this.isRendered()) {
	          main_core.Dom.style(this.getContainer(), 'width', "".concat(width, "px"));
	        }
	      }
	    }
	  }, {
	    key: "getHeight",
	    value: function getHeight() {
	      return this.height;
	    }
	  }, {
	    key: "setHeight",
	    value: function setHeight(height) {
	      if (main_core.Type.isNumber(height) && height > 0) {
	        this.height = height;

	        if (this.isRendered()) {
	          main_core.Dom.style(this.getContainer(), 'height', "".concat(height, "px"));
	          return Animation.handleTransitionEnd(this.getContainer(), 'height');
	        } else {
	          return Promise.resolve();
	        }
	      } else {
	        return Promise.resolve();
	      }
	    }
	  }, {
	    key: "getOffsetLeft",
	    value: function getOffsetLeft() {
	      return this.offsetLeft;
	    }
	  }, {
	    key: "setOffsetLeft",
	    value: function setOffsetLeft(offset) {
	      if (main_core.Type.isNumber(offset) && offset >= 0) {
	        this.offsetLeft = offset;

	        if (this.isRendered()) {
	          this.getPopup().setOffset({
	            offsetLeft: offset
	          });
	          this.adjustPosition();
	        }
	      }
	    }
	  }, {
	    key: "getOffsetTop",
	    value: function getOffsetTop() {
	      return this.offsetTop;
	    }
	  }, {
	    key: "setOffsetTop",
	    value: function setOffsetTop(offset) {
	      if (main_core.Type.isNumber(offset) && offset >= 0) {
	        this.offsetTop = offset;

	        if (this.isRendered()) {
	          this.getPopup().setOffset({
	            offsetTop: offset
	          });
	          this.adjustPosition();
	        }
	      }
	    }
	  }, {
	    key: "getZindex",
	    value: function getZindex() {
	      return this.getPopup().getZindex();
	    }
	  }, {
	    key: "setZindex",
	    value: function setZindex(index) {
	      if (main_core.Type.isNumber(index) && index > 0 || index === null) {
	        this.zIndex = index;

	        if (this.isRendered()) {
	          this.getPopup().params.zIndexAbsolute = index !== null ? index : 0;
	          this.adjustPosition();
	        }
	      }
	    }
	  }, {
	    key: "isCacheable",
	    value: function isCacheable() {
	      return this.cacheable;
	    }
	  }, {
	    key: "setCacheable",
	    value: function setCacheable(cacheable) {
	      if (main_core.Type.isBoolean(cacheable)) {
	        this.cacheable = cacheable;

	        if (this.isRendered()) {
	          this.getPopup().setCacheable(cacheable);
	        }
	      }
	    }
	  }, {
	    key: "shouldFocusOnFirst",
	    value: function shouldFocusOnFirst() {
	      return this.focusOnFirst;
	    }
	  }, {
	    key: "setFocusOnFirst",
	    value: function setFocusOnFirst(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.focusOnFirst = flag;
	      }
	    }
	  }, {
	    key: "isDropdownMode",
	    value: function isDropdownMode() {
	      return this.dropdownMode;
	    }
	  }, {
	    key: "setDropdownMode",
	    value: function setDropdownMode(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.dropdownMode = flag;
	        this.getRecentTab().setVisible(!flag);
	      }
	    }
	  }, {
	    key: "getMaxLabelWidth",
	    value: function getMaxLabelWidth() {
	      return this.maxLabelWidth;
	    }
	  }, {
	    key: "getMinLabelWidth",
	    value: function getMinLabelWidth() {
	      return this.minLabelWidth;
	    }
	  }, {
	    key: "getPopup",
	    value: function getPopup() {
	      var _this9 = this;

	      if (this.popup !== null) {
	        return this.popup;
	      }

	      this.getTabs().forEach(function (tab) {
	        _this9.insertTab(tab);
	      });
	      this.popup = new main_popup.Popup(Object.assign({
	        contentPadding: 0,
	        padding: 0,
	        offsetTop: this.getOffsetTop(),
	        offsetLeft: this.getOffsetLeft(),
	        zIndexAbsolute: this.zIndex,
	        animation: {
	          showClassName: 'ui-selector-popup-animation-show',
	          closeClassName: 'ui-selector-popup-animation-close',
	          closeAnimationType: 'animation'
	        },
	        bindElement: this.getTargetNode(),
	        bindOptions: {
	          forceBindPosition: true
	        },
	        autoHide: this.isAutoHide(),
	        closeByEsc: this.shouldHideByEsc(),
	        cacheable: this.isCacheable(),
	        events: {
	          onFirstShow: this.handlePopupFirstShow.bind(this),
	          onAfterShow: this.handlePopupAfterShow.bind(this),
	          onClose: this.handlePopupClose.bind(this),
	          onDestroy: this.handlePopupDestroy.bind(this)
	        },
	        content: this.getContainer()
	      }, this.popupOptions));
	      this.rendered = true;
	      this.selectFirstTab();
	      return this.popup;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      var _this10 = this;

	      return this.cache.remember('container', function () {
	        var searchContainer = '';

	        if (_this10.getTagSelectorMode() === TagSelectorMode.INSIDE) {
	          searchContainer = main_core.Tag.render(_templateObject$b());

	          _this10.getTagSelector().renderTo(searchContainer);
	        }

	        return main_core.Tag.render(_templateObject2$7(), _this10.getWidth(), _this10.getHeight(), searchContainer, _this10.getTabsContainer(), _this10.getFooterContainer());
	      });
	    }
	  }, {
	    key: "getTabsContainer",
	    value: function getTabsContainer() {
	      var _this11 = this;

	      return this.cache.remember('tabs-container', function () {
	        return main_core.Tag.render(_templateObject3$7(), _this11.getTabContentsContainer(), _this11.getLabelsContainer());
	      });
	    }
	  }, {
	    key: "getTabContentsContainer",
	    value: function getTabContentsContainer() {
	      return this.cache.remember('tab-contents', function () {
	        return main_core.Tag.render(_templateObject4$6());
	      });
	    }
	  }, {
	    key: "getLabelsContainer",
	    value: function getLabelsContainer() {
	      var _this12 = this;

	      return this.cache.remember('labels-container', function () {
	        return main_core.Tag.render(_templateObject5$5(), _this12.handleLabelsMouseEnter.bind(_this12), _this12.handleLabelsMouseLeave.bind(_this12));
	      });
	    }
	  }, {
	    key: "getFooterContainer",
	    value: function getFooterContainer() {
	      var _this13 = this;

	      return this.cache.remember('footer', function () {
	        var footer = _this13.getFooter() && _this13.getFooter().getContainer();

	        return main_core.Tag.render(_templateObject6$3(), footer ? footer : '');
	      });
	    }
	  }, {
	    key: "isRendered",
	    value: function isRendered() {
	      return this.rendered;
	    }
	  }, {
	    key: "load",
	    value: function load() {
	      var _this14 = this;

	      if (this.loadState !== LoadState.UNSENT || !this.hasDynamicLoad()) {
	        return;
	      }

	      if (this.getTagSelector()) {
	        this.getTagSelector().lock();
	      }

	      setTimeout(function () {
	        if (_this14.isLoading()) {
	          _this14.showLoader();
	        }
	      }, 400);
	      this.loadState = LoadState.LOADING;
	      main_core.ajax.runAction('ui.entityselector.load', {
	        json: {
	          dialog: this
	        },
	        getParameters: {
	          context: this.getContext()
	        }
	      }).then(function (response) {
	        if (response && response.data && main_core.Type.isPlainObject(response.data.dialog)) {
	          _this14.loadState = LoadState.DONE;
	          var entities = main_core.Type.isArrayFilled(response.data.dialog.entities) ? response.data.dialog.entities : [];
	          entities.forEach(function (entityOptions) {
	            var entity = _this14.getEntity(entityOptions.id);

	            if (entity) {
	              entity.setDynamicSearch(entityOptions.dynamicSearch);
	            }
	          });

	          _this14.setOptions(response.data.dialog);

	          _this14.getPreselectedItems().forEach(function (preselectedItem) {
	            var item = _this14.getItem(preselectedItem);

	            if (item) {
	              item.select(true);
	            }
	          });

	          var recentItems = response.data.dialog.recentItems;

	          if (main_core.Type.isArray(recentItems)) {
	            recentItems.forEach(function (recentItem) {
	              var item = _this14.getItem(recentItem);

	              if (item) {
	                _this14.getRecentTab().getRootNode().addItem(item);
	              }
	            });
	          }

	          if (!_this14.getRecentTab().getRootNode().hasChildren() && _this14.getRecentTab().getStub()) {
	            _this14.getRecentTab().getStub().show();
	          }

	          if (_this14.getTagSelector()) {
	            _this14.getTagSelector().unlock();
	          }

	          if (_this14.isRendered() && !_this14.getActiveTab()) {
	            _this14.selectFirstTab();
	          }

	          _this14.focusSearch();

	          _this14.destroyLoader();

	          if (_this14.shouldFocusOnFirst()) {
	            _this14.focusOnFirstNode();
	          }
	        }
	      }).catch(function (error) {
	        _this14.loadState = LoadState.UNSENT;

	        if (_this14.getTagSelector()) {
	          _this14.getTagSelector().unlock();
	        }

	        _this14.focusSearch();

	        _this14.destroyLoader();

	        console.error(error);
	      });
	    }
	  }, {
	    key: "hasDynamicLoad",
	    value: function hasDynamicLoad() {
	      return this.getEntities().some(function (entity) {
	        return entity.hasDynamicLoad();
	      });
	    }
	  }, {
	    key: "hasDynamicSearch",
	    value: function hasDynamicSearch() {
	      return this.getEntities().some(function (entity) {
	        return entity.isSearchable() && entity.hasDynamicSearch();
	      });
	    }
	  }, {
	    key: "isLoaded",
	    value: function isLoaded() {
	      return this.loadState === LoadState.DONE;
	    }
	  }, {
	    key: "isLoading",
	    value: function isLoading() {
	      return this.loadState === LoadState.LOADING;
	    }
	  }, {
	    key: "search",
	    value: function search(queryString) {
	      var query = main_core.Type.isStringFilled(queryString) ? queryString.trim() : '';

	      if (!main_core.Type.isStringFilled(query)) {
	        this.selectFirstTab();

	        if (this.getSearchTab()) {
	          this.getSearchTab().clearResults();
	        }
	      } else if (this.getSearchTab()) {
	        this.selectTab(this.getSearchTab().getId());
	        this.getSearchTab().search(query);
	      }

	      this.emit('onSearch', {
	        query: query
	      });
	    }
	  }, {
	    key: "saveRecentItem",
	    value: function saveRecentItem(item) {
	      if (this.getContext() === null || !item.isSaveable()) {
	        return;
	      }

	      this.recentItemsToSave.push(item);
	      this.saveRecentItemsWithDebounce();
	    }
	  }, {
	    key: "saveRecentItems",
	    value: function saveRecentItems() {
	      if (!main_core.Type.isArrayFilled(this.recentItemsToSave)) {
	        return;
	      }

	      main_core.ajax.runAction('ui.entityselector.saveRecentItems', {
	        json: {
	          dialog: this,
	          recentItems: this.recentItemsToSave
	        },
	        getParameters: {
	          context: this.getContext()
	        }
	      }).then(function (response) {}).catch(function (error) {
	        console.error(error);
	      });
	      this.recentItemsToSave = [];
	    }
	  }, {
	    key: "getFocusedNode",
	    value: function getFocusedNode() {
	      return this.focusedNode;
	    }
	  }, {
	    key: "clearNodeFocus",
	    value: function clearNodeFocus() {
	      if (this.focusedNode) {
	        this.focusedNode.unfocus();
	        this.focusedNode = null;
	      }
	    }
	  }, {
	    key: "focusOnFirstNode",
	    value: function focusOnFirstNode() {
	      if (this.getActiveTab()) {
	        var itemNode = this.getActiveTab().getRootNode().getFirstChild();

	        if (itemNode) {
	          itemNode.focus();
	          return itemNode;
	        }
	      }

	      return null;
	    }
	  }, {
	    key: "shouldClearUnavailableItems",
	    value: function shouldClearUnavailableItems() {
	      return this.clearUnavailableItems;
	    }
	  }, {
	    key: "handleItemNodeFocus",
	    value: function handleItemNodeFocus(event) {
	      var _event$getData2 = event.getData(),
	          node = _event$getData2.node;

	      if (this.focusedNode === node) {
	        return;
	      }

	      this.clearNodeFocus();
	      this.focusedNode = node;
	    }
	  }, {
	    key: "handleItemNodeUnfocus",
	    value: function handleItemNodeUnfocus() {
	      this.clearNodeFocus();
	    }
	  }, {
	    key: "toJSON",
	    value: function toJSON() {
	      return {
	        id: this.getId(),
	        context: this.getContext(),
	        entities: this.getEntities(),
	        preselectedItems: this.getPreselectedItems(),
	        clearUnavailableItems: this.shouldClearUnavailableItems()
	      };
	    }
	  }], [{
	    key: "createFooter",
	    value: function createFooter(context, footerContent, footerOptions) {
	      if (!main_core.Type.isStringFilled(footerContent) && !main_core.Type.isArrayFilled(footerContent) && !main_core.Type.isDomNode(footerContent) && !main_core.Type.isFunction(footerContent)) {
	        return null;
	      }
	      /** @var {BaseFooter} */


	      var footer = null;
	      var options = main_core.Type.isPlainObject(footerOptions) ? footerOptions : {};

	      if (main_core.Type.isFunction(footerContent) || main_core.Type.isString(footerContent)) {
	        var className = main_core.Type.isString(footerContent) ? main_core.Reflection.getClass(footerContent) : footerContent;

	        if (main_core.Type.isFunction(className)) {
	          footer = new className(context, options);

	          if (!(footer instanceof BaseFooter)) {
	            console.error('EntitySelector: footer is not an instance of BaseFooter.');
	            footer = null;
	          }
	        }
	      }

	      if (footerContent !== null && !footer) {
	        footer = new DefaultFooter(context, Object.assign({}, options, {
	          content: footerContent
	        }));
	      }

	      return footer;
	    }
	  }]);
	  return Dialog;
	}(main_core_events.EventEmitter);

	exports.Dialog = Dialog;
	exports.Item = Item;
	exports.Tab = Tab;
	exports.TagSelector = TagSelector;
	exports.BaseFooter = BaseFooter;
	exports.DefaultFooter = DefaultFooter;
	exports.BaseStub = BaseStub;
	exports.DefaultStub = DefaultStub;

}((this.BX.UI.EntitySelector = this.BX.UI.EntitySelector || {}),BX.Main,BX.Event,BX,BX));
//# sourceMappingURL=entity-selector.bundle.js.map
