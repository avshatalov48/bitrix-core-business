/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,ui_bbcode_encoder,main_core) {
	'use strict';

	function getByIndex(array, index) {
	  if (!main_core.Type.isArray(array)) {
	    throw new TypeError('array is not a array');
	  }
	  if (!main_core.Type.isInteger(index)) {
	    throw new TypeError('index is not a integer');
	  }
	  const preparedIndex = index < 0 ? array.length + index : index;
	  return array[preparedIndex];
	}

	const privateMap = new WeakMap();
	const nameSymbol = Symbol('name');
	class BBCodeNode {
	  constructor(options = {}) {
	    this[nameSymbol] = '#unknown';
	    this.children = [];
	    privateMap.set(this, {
	      delayedChildren: []
	    });
	    this.setName(options.name);
	    privateMap.get(this).scheme = options.scheme;
	    this.setParent(options.parent);
	    this.setChildren(options.children);
	  }
	  static get ELEMENT_NODE() {
	    return 1;
	  }
	  static get TEXT_NODE() {
	    return 2;
	  }
	  static get ROOT_NODE() {
	    return 3;
	  }
	  static get FRAGMENT_NODE() {
	    return 4;
	  }
	  static freezeProperty(node, property, value, enumerable = true) {
	    Object.defineProperty(node, property, {
	      value,
	      writable: false,
	      configurable: false,
	      enumerable
	    });
	  }
	  static makeNonEnumerableProperty(node, property) {
	    Object.defineProperty(node, property, {
	      writable: false,
	      enumerable: false,
	      configurable: false
	    });
	  }
	  static flattenChildren(children) {
	    if (main_core.Type.isArrayFilled(children)) {
	      return children.flatMap(node => {
	        if (node.getType() === BBCodeNode.FRAGMENT_NODE) {
	          return node.getChildren();
	        }
	        return node;
	      });
	    }
	    return [];
	  }
	  setScheme(scheme, onUnknown) {
	    privateMap.get(this).scheme = scheme;
	  }
	  getScheme() {
	    return privateMap.get(this).scheme;
	  }
	  getTagScheme() {
	    return this.getScheme().getTagScheme(this.getName());
	  }
	  getEncoder() {
	    return this.getScheme().getEncoder();
	  }
	  prepareCase(value) {
	    const scheme = this.getScheme();
	    const currentCase = scheme.getOutputTagCase();
	    if (currentCase === 'upper') {
	      return value.toUpperCase();
	    }
	    return value.toLowerCase();
	  }
	  setName(name) {
	    if (main_core.Type.isString(name)) {
	      this[nameSymbol] = name.toLowerCase();
	    }
	  }
	  getName() {
	    return this[nameSymbol];
	  }
	  getDisplayedName() {
	    return this.prepareCase(this.getName());
	  }
	  setParent(parent = null) {
	    const mounted = !this.hasParent() && parent;
	    privateMap.get(this).parent = parent;
	    if (mounted) {
	      this.onNodeDidMount();
	    }
	  }
	  getParent() {
	    return privateMap.get(this).parent;
	  }
	  getType() {
	    return privateMap.get(this).type;
	  }
	  hasParent() {
	    return Boolean(privateMap.get(this).parent);
	  }
	  remove() {
	    if (this.hasParent()) {
	      this.getParent().removeChild(this);
	    }
	  }
	  setChildren(children) {
	    if (main_core.Type.isArray(children)) {
	      this.children = [];
	      this.appendChild(...children);
	    }
	  }
	  getChildren() {
	    return [...this.children];
	  }
	  getLastChild() {
	    return getByIndex(this.getChildren(), -1);
	  }
	  getLastChildOfType(type) {
	    return this.getChildren().reverse().find(node => {
	      return node.getType() === type;
	    });
	  }
	  getLastChildOfName(name) {
	    return this.getChildren().reverse().find(node => {
	      return node.getType() === BBCodeNode.ELEMENT_NODE && node.getName() === name;
	    });
	  }
	  getFirstChild() {
	    return getByIndex(this.getChildren(), 0);
	  }
	  getFirstChildOfType(type) {
	    return this.getChildren().find(node => {
	      return node.getType() === type;
	    });
	  }
	  getFirstChildOfName(name) {
	    return this.getChildren().find(node => {
	      return node.getType() === BBCodeNode.ELEMENT_NODE && node.getName() === name;
	    });
	  }
	  getPreviewsSibling() {
	    if (this.hasParent()) {
	      const parentChildren = this.getParent().getChildren();
	      const currentIndex = parentChildren.indexOf(this);
	      if (currentIndex > 0) {
	        return getByIndex(parentChildren, currentIndex - 1);
	      }
	    }
	    return null;
	  }
	  getPreviewsSiblings() {
	    if (this.hasParent()) {
	      const parentChildren = this.getParent().getChildren();
	      const currentIndex = parentChildren.indexOf(this);
	      return parentChildren.filter((child, index) => {
	        return index < currentIndex;
	      });
	    }
	    return null;
	  }
	  getNextSibling() {
	    if (this.hasParent()) {
	      const parentChildren = this.getParent().getChildren();
	      const currentIndex = parentChildren.indexOf(this);
	      if (currentIndex !== -1 && currentIndex !== parentChildren.length) {
	        return getByIndex(parentChildren, currentIndex + 1);
	      }
	    }
	    return null;
	  }
	  getNextSiblings() {
	    if (this.hasParent()) {
	      const parentChildren = this.getParent().getChildren();
	      const currentIndex = parentChildren.indexOf(this);
	      return parentChildren.filter((child, index) => {
	        return index > currentIndex;
	      });
	    }
	    return null;
	  }
	  getChildrenCount() {
	    return this.children.length;
	  }
	  hasChildren() {
	    return this.getChildrenCount() > 0;
	  }
	  isEmpty() {
	    return this.getChildrenCount() === 0;
	  }
	  adjustChildren() {
	    this.setChildren(this.getChildren());
	  }
	  setDelayedChildren(children) {
	    if (main_core.Type.isArray(children)) {
	      privateMap.get(this).delayedChildren = children;
	    }
	  }
	  addDelayedChildren(children) {
	    if (main_core.Type.isArrayFilled(children)) {
	      this.setDelayedChildren([...this.getDelayedChildren(), ...children]);
	    }
	  }
	  hasDelayedChildren() {
	    return privateMap.get(this).delayedChildren.length > 0;
	  }
	  getDelayedChildren() {
	    return [...privateMap.get(this).delayedChildren];
	  }
	  appendChild(...children) {
	    const flattenedChildren = BBCodeNode.flattenChildren(children);
	    flattenedChildren.forEach(node => {
	      node.remove();
	      node.setParent(this);
	      this.children.push(node);
	    });
	  }
	  prependChild(...children) {
	    const flattenedChildren = BBCodeNode.flattenChildren(children);
	    flattenedChildren.forEach(node => {
	      node.remove();
	      node.setParent(this);
	      this.children.unshift(node);
	    });
	  }
	  insertBefore(...nodes) {
	    if (this.hasParent() && main_core.Type.isArrayFilled(nodes)) {
	      const parent = this.getParent();
	      const parentChildren = parent.getChildren();
	      const currentNodeIndex = parentChildren.indexOf(this);
	      const deleteCount = 0;
	      parentChildren.splice(currentNodeIndex, deleteCount, ...nodes);
	      parent.setChildren(parentChildren);
	    }
	  }
	  insertAfter(...nodes) {
	    if (this.hasParent() && main_core.Type.isArrayFilled(nodes)) {
	      const parent = this.getParent();
	      const parentChildren = parent.getChildren();
	      const currentNodeIndex = parentChildren.indexOf(this);
	      const startIndex = currentNodeIndex + 1;
	      const deleteCount = 0;
	      parentChildren.splice(startIndex, deleteCount, ...nodes);
	      parent.setChildren(parentChildren);
	    }
	  }
	  propagateChild(...children) {
	    if (this.hasParent()) {
	      this.insertBefore(...children.filter(child => {
	        return !['#linebreak', '#tab'].includes(child.getName());
	      }));
	    } else {
	      this.addDelayedChildren(children);
	    }
	  }
	  onNodeDidMount() {
	    const delayedChildren = this.getDelayedChildren();
	    if (main_core.Type.isArrayFilled(delayedChildren)) {
	      this.propagateChild(...delayedChildren);
	      this.setDelayedChildren([]);
	    }
	  }
	  removeChild(...children) {
	    const filteredChildren = [];
	    this.children.forEach(node => {
	      if (children.includes(node)) {
	        node.setParent(null);
	      } else {
	        filteredChildren.push(node);
	      }
	    });
	    this.children = filteredChildren;
	  }
	  replaceChild(targetNode, ...children) {
	    this.children = this.children.flatMap(node => {
	      if (node === targetNode) {
	        node.setParent(null);
	        const flattenedChildren = BBCodeNode.flattenChildren(children);
	        return flattenedChildren.map(child => {
	          child.remove();
	          child.setParent(this);
	          return child;
	        });
	      }
	      return node;
	    });
	  }
	  replace(...children) {
	    if (this.hasParent()) {
	      const parent = this.getParent();
	      parent.replaceChild(this, ...children);
	    }
	  }
	  clone(options = {}) {
	    const children = (() => {
	      if (options.deep) {
	        return this.getChildren().map(child => {
	          return child.clone(options);
	        });
	      }
	      return [];
	    })();
	    return this.getScheme().createNode({
	      name: this.getName(),
	      parent: this.getParent(),
	      children
	    });
	  }
	  toPlainText() {
	    return this.getChildren().map(child => {
	      return child.toPlainText();
	    }).join('');
	  }
	  getTextContent() {
	    return this.toPlainText();
	  }
	  getPlainTextLength() {
	    return this.toPlainText().length;
	  }
	  removePreviewsSiblings() {
	    const removePreviewsSiblings = node => {
	      const previewsSiblings = node.getPreviewsSiblings();
	      if (main_core.Type.isArray(previewsSiblings)) {
	        previewsSiblings.forEach(sibling => {
	          sibling.remove();
	        });
	      }
	      const parent = node.getParent();
	      if (parent) {
	        removePreviewsSiblings(parent);
	      }
	    };
	    removePreviewsSiblings(this);
	  }
	  removeNextSiblings() {
	    const removeNextSiblings = node => {
	      const nextSiblings = node.getNextSiblings();
	      if (main_core.Type.isArray(nextSiblings)) {
	        nextSiblings.forEach(sibling => {
	          sibling.remove();
	        });
	      }
	      const parent = node.getParent();
	      if (parent) {
	        removeNextSiblings(parent);
	      }
	    };
	    removeNextSiblings(this);
	  }
	  findByTextIndex(index) {
	    let currentIndex = 0;
	    let startIndex = 0;
	    let endIndex = 0;
	    const node = BBCodeNode.flattenAst(this).find(child => {
	      if (child.getName() === '#text' || child.getName() === '#linebreak' || child.getName() === '#tab') {
	        startIndex = currentIndex;
	        endIndex = startIndex + child.getLength();
	        currentIndex = endIndex;
	        return index >= startIndex && endIndex >= index;
	      }
	      return false;
	    });
	    if (node) {
	      return {
	        node,
	        startIndex,
	        endIndex
	      };
	    }
	    return null;
	  }
	  split(options) {
	    const {
	      offset,
	      byWord = false
	    } = options;
	    const plainTextLength = this.getPlainTextLength();
	    const leftTree = (() => {
	      if (plainTextLength === offset) {
	        return this.clone({
	          deep: true
	        });
	      }
	      if (offset <= 0 || offset > plainTextLength) {
	        return null;
	      }
	      const tree = this.clone({
	        deep: true
	      });
	      const {
	        node,
	        startIndex
	      } = tree.findByTextIndex(offset);
	      const [leftNode, rightNode] = node.split({
	        offset: offset - startIndex,
	        byWord
	      });
	      if (leftNode) {
	        node.replace(leftNode);
	        leftNode.removeNextSiblings();
	      } else if (rightNode) {
	        rightNode.removeNextSiblings();
	        rightNode.remove();
	      }
	      return tree;
	    })();
	    const rightTree = (() => {
	      if (plainTextLength === offset) {
	        return null;
	      }
	      if (offset === 0) {
	        return this.clone({
	          deep: true
	        });
	      }
	      const tree = this.clone({
	        deep: true
	      });
	      const {
	        node,
	        startIndex
	      } = tree.findByTextIndex(offset);
	      const [leftNode, rightNode] = node.split({
	        offset: offset - startIndex,
	        byWord
	      });
	      if (rightNode) {
	        node.replace(rightNode);
	        rightNode.removePreviewsSiblings();
	      } else if (leftNode) {
	        leftNode.removePreviewsSiblings();
	        if (leftNode.hasParent()) {
	          const parent = leftNode.getParent();
	          leftNode.remove();
	          if (parent.getChildrenCount() === 0) {
	            parent.remove();
	          }
	        }
	      }
	      return tree;
	    })();
	    return [leftTree, rightTree];
	  }
	  static flattenAst(ast) {
	    const flat = [];
	    const traverse = node => {
	      flat.push(node);
	      if (node.hasChildren()) {
	        node.getChildren().forEach(child => {
	          traverse(child);
	        });
	      }
	    };
	    if (ast.hasChildren()) {
	      ast.getChildren().forEach(child => {
	        traverse(child);
	      });
	    }
	    return flat;
	  }
	  toJSON() {
	    return {
	      name: this.getName(),
	      children: this.getChildren().map(child => {
	        return child.toJSON();
	      })
	    };
	  }
	}

	const voidSymbol = Symbol('void');
	class BBCodeElementNode extends BBCodeNode {
	  constructor(options = {}) {
	    super(options);
	    this.attributes = {};
	    this.value = '';
	    this[voidSymbol] = false;
	    privateMap.get(this).type = BBCodeNode.ELEMENT_NODE;
	    const tagScheme = this.getTagScheme();
	    this[voidSymbol] = tagScheme.isVoid();
	    this.setValue(options.value);
	    this.setAttributes(options.attributes);
	  }
	  setScheme(scheme, onUnknown) {
	    this.getChildren().forEach(node => {
	      node.setScheme(scheme, onUnknown);
	    });
	    if (scheme.isAllowedTag(this.getName())) {
	      super.setScheme(scheme);
	      const tagScheme = this.getTagScheme();
	      this[voidSymbol] = tagScheme.isVoid();
	    } else {
	      super.setScheme(scheme);
	      onUnknown(this, scheme);
	    }
	  }
	  filterChildren(children) {
	    const filteredChildren = {
	      resolved: [],
	      unresolved: []
	    };
	    const scheme = this.getScheme();
	    children.forEach(child => {
	      if (scheme.isChildAllowed(this, child)) {
	        filteredChildren.resolved.push(child);
	      } else {
	        filteredChildren.unresolved.push(child);
	      }
	    });
	    return filteredChildren;
	  }
	  convertChildren(children) {
	    const tagScheme = this.getTagScheme();
	    const childConverter = tagScheme.getChildConverter();
	    if (childConverter) {
	      const scheme = this.getScheme();
	      return children.map(child => {
	        return childConverter(child, scheme);
	      });
	    }
	    return children;
	  }
	  setValue(value) {
	    if (main_core.Type.isString(value) || main_core.Type.isNumber(value) || main_core.Type.isBoolean(value)) {
	      this.value = value;
	    }
	  }
	  getValue() {
	    return this.value;
	  }
	  isVoid() {
	    return this[voidSymbol];
	  }
	  canBeEmpty() {
	    return this.getTagScheme().canBeEmpty();
	  }
	  hasGroup(groupName) {
	    return this.getTagScheme().hasGroup(groupName);
	  }
	  setAttributes(attributes) {
	    if (main_core.Type.isPlainObject(attributes)) {
	      const entries = Object.entries(attributes).map(([key, value]) => {
	        return [key.toLowerCase(), value];
	      });
	      this.attributes = Object.fromEntries(entries);
	    }
	  }
	  setAttribute(name, value) {
	    if (main_core.Type.isStringFilled(name)) {
	      const preparedName = name.toLowerCase();
	      if (main_core.Type.isNil(value)) {
	        delete this.attributes[preparedName];
	      } else {
	        this.attributes[preparedName] = value;
	      }
	    }
	  }
	  getAttribute(name) {
	    if (main_core.Type.isString(name)) {
	      return this.attributes[name.toLowerCase()];
	    }
	    return null;
	  }
	  getAttributes() {
	    return {
	      ...this.attributes
	    };
	  }
	  appendChild(...children) {
	    const flattenedChildren = BBCodeNode.flattenChildren(children);
	    const convertedChildren = this.convertChildren(flattenedChildren);
	    const filteredChildren = this.filterChildren(convertedChildren);
	    filteredChildren.resolved.forEach(node => {
	      node.remove();
	      node.setParent(this);
	      this.children.push(node);
	    });
	    if (main_core.Type.isArrayFilled(filteredChildren.unresolved)) {
	      const tagScheme = this.getTagScheme();
	      if (tagScheme.hasNotAllowedChildrenCallback()) {
	        tagScheme.runNotAllowedChildrenCallback({
	          node: this,
	          children: filteredChildren.unresolved,
	          scheme: this.getScheme()
	        });
	      } else if (this.getScheme().isAllowedUnresolvedNodesHoisting()) {
	        this.propagateChild(...filteredChildren.unresolved);
	      } else {
	        filteredChildren.unresolved.forEach(node => {
	          node.remove();
	        });
	      }
	    }
	  }
	  prependChild(...children) {
	    const flattenedChildren = BBCodeNode.flattenChildren(children);
	    const convertedChildren = this.convertChildren(flattenedChildren);
	    const filteredChildren = this.filterChildren(convertedChildren);
	    filteredChildren.resolved.forEach(node => {
	      node.remove();
	      node.setParent(this);
	      this.children.unshift(node);
	    });
	    if (main_core.Type.isArrayFilled(filteredChildren.unresolved)) {
	      const tagScheme = this.getTagScheme();
	      if (tagScheme.hasNotAllowedChildrenCallback()) {
	        tagScheme.runNotAllowedChildrenCallback({
	          node: this,
	          children: filteredChildren.unresolved,
	          scheme: this.getScheme()
	        });
	      } else if (this.getScheme().isAllowedUnresolvedNodesHoisting()) {
	        this.propagateChild(...filteredChildren.unresolved);
	      } else {
	        filteredChildren.unresolved.forEach(node => {
	          node.remove();
	        });
	      }
	    }
	  }
	  replaceChild(targetNode, ...children) {
	    this.children = this.children.flatMap(node => {
	      if (node === targetNode) {
	        node.setParent(null);
	        const flattenedChildren = BBCodeNode.flattenChildren(children);
	        const convertedChildren = this.convertChildren(flattenedChildren);
	        const filteredChildren = this.filterChildren(convertedChildren);
	        return filteredChildren.resolved.map(child => {
	          child.remove();
	          child.setParent(this);
	          return child;
	        });
	      }
	      return node;
	    });
	  }
	  toStringValue() {
	    const value = this.getValue();
	    const encodedValue = this.getEncoder().encodeAttribute(value);
	    return value ? `=${encodedValue}` : '';
	  }
	  toStringAttributes() {
	    return Object.entries(this.getAttributes()).map(([key, attrValue]) => {
	      const preparedKey = this.prepareCase(key);
	      const encodedValue = this.getEncoder().encodeAttribute(attrValue);
	      return attrValue ? `${preparedKey}=${encodedValue}` : preparedKey;
	    }).join(' ');
	  }
	  getContent(options = {}) {
	    return this.getChildren().map(child => {
	      return child.toString(options);
	    }).join('');
	  }
	  getOpeningTag() {
	    const displayedName = this.getDisplayedName();
	    const tagValue = this.toStringValue();
	    const attributes = this.toStringAttributes();
	    const formattedAttributes = main_core.Type.isStringFilled(attributes) ? ` ${attributes}` : '';
	    return `[${displayedName}${tagValue}${formattedAttributes}]`;
	  }
	  getClosingTag() {
	    return `[/${this.getDisplayedName()}]`;
	  }
	  clone(options = {}) {
	    const children = (() => {
	      if (options.deep) {
	        return this.getChildren().map(child => {
	          return child.clone(options);
	        });
	      }
	      return [];
	    })();
	    return this.getScheme().createElement({
	      name: this.getName(),
	      void: this.isVoid(),
	      value: this.getValue(),
	      attributes: {
	        ...this.getAttributes()
	      },
	      children
	    });
	  }
	  splitByChildIndex(index) {
	    if (!main_core.Type.isNumber(index)) {
	      throw new TypeError('index is not a number');
	    }
	    const childrenCount = this.getChildrenCount();
	    if (index < 0 || index > childrenCount) {
	      throw new TypeError(`index '${index}' is out of range ${0}-${childrenCount}`);
	    }
	    const leftNode = (() => {
	      if (index === childrenCount) {
	        return this;
	      }
	      if (index === 0) {
	        return null;
	      }
	      const leftChildren = this.getChildren().filter((child, childIndex) => {
	        return childIndex < index;
	      });
	      const node = this.clone();
	      node.setChildren(leftChildren);
	      return node;
	    })();
	    const rightNode = (() => {
	      if (index === 0) {
	        return this;
	      }
	      if (index === childrenCount) {
	        return null;
	      }
	      const rightChildren = this.getChildren();
	      const node = this.clone();
	      node.setChildren(rightChildren);
	      return node;
	    })();
	    if (leftNode && rightNode) {
	      this.replace(leftNode, rightNode);
	    }
	    return [leftNode, rightNode];
	  }
	  getTagScheme() {
	    return super.getTagScheme();
	  }
	  trimStartLinebreaks() {
	    const firstChild = this.getFirstChild();
	    if (firstChild && firstChild.getName() === '#linebreak') {
	      firstChild.remove();
	      this.trimStartLinebreaks();
	    }
	  }
	  trimEndLinebreaks() {
	    const lastChild = this.getLastChild();
	    if (lastChild && lastChild.getName() === '#linebreak') {
	      lastChild.remove();
	      this.trimEndLinebreaks();
	    }
	  }
	  trimLinebreaks() {
	    this.trimStartLinebreaks();
	    this.trimEndLinebreaks();
	  }
	  toString(options = {}) {
	    const tagScheme = this.getTagScheme();
	    const stringifier = tagScheme.getStringifier();
	    if (main_core.Type.isFunction(stringifier)) {
	      const scheme = this.getScheme();
	      return stringifier(this, scheme, options);
	    }
	    const openingTag = this.getOpeningTag();
	    const content = this.getContent(options);
	    if (this.isVoid()) {
	      return `${openingTag}${content}`;
	    }
	    const closingTag = this.getClosingTag();
	    return `${openingTag}${content}${closingTag}`;
	  }
	  toJSON() {
	    return {
	      ...super.toJSON(),
	      value: this.getValue(),
	      attributes: this.getAttributes(),
	      void: this.isVoid()
	    };
	  }
	}

	class BBCodeRootNode extends BBCodeElementNode {
	  constructor(options) {
	    super({
	      ...options,
	      name: '#root'
	    });
	    privateMap.get(this).type = BBCodeNode.ROOT_NODE;
	    BBCodeRootNode.makeNonEnumerableProperty(this, 'value');
	    BBCodeRootNode.makeNonEnumerableProperty(this, 'attributes');
	    BBCodeRootNode.freezeProperty(this, nameSymbol, '#root');
	  }
	  setScheme(scheme, onUnknown) {
	    BBCodeNode.flattenAst(this).forEach(node => {
	      node.setScheme(scheme, onUnknown);
	    });
	    super.setScheme(scheme);
	    BBCodeNode.flattenAst(this).forEach(node => {
	      node.adjustChildren();
	    });
	  }
	  getParent() {
	    return null;
	  }
	  clone(options = {}) {
	    const children = (() => {
	      if (options.deep) {
	        return this.getChildren().map(child => {
	          return child.clone(options);
	        });
	      }
	      return [];
	    })();
	    return this.getScheme().createRoot({
	      children
	    });
	  }
	  toString(options = {}) {
	    return this.getChildren().map(child => {
	      return child.toString(options);
	    }).join('');
	  }
	  toJSON() {
	    return this.getChildren().map(node => {
	      return node.toJSON();
	    });
	  }
	}

	class BBCodeFragmentNode extends BBCodeElementNode {
	  constructor(options) {
	    super({
	      ...options,
	      name: '#fragment'
	    });
	    privateMap.get(this).type = BBCodeNode.FRAGMENT_NODE;
	    BBCodeFragmentNode.makeNonEnumerableProperty(this, 'value');
	    BBCodeFragmentNode.makeNonEnumerableProperty(this, 'attributes');
	    BBCodeFragmentNode.freezeProperty(this, nameSymbol, '#fragment');
	  }
	  clone(options = {}) {
	    const children = (() => {
	      if (options.deep) {
	        return this.getChildren().map(child => {
	          return child.clone(options);
	        });
	      }
	      return [];
	    })();
	    return this.getScheme().createFragment({
	      children
	    });
	  }
	}

	const contentSymbol = Symbol('content');
	class BBCodeTextNode extends BBCodeNode {
	  constructor(options = {}) {
	    const nodeOptions = main_core.Type.isString(options) ? {
	      content: options
	    } : options;
	    super(nodeOptions);
	    this[nameSymbol] = '#text';
	    this[contentSymbol] = '';
	    privateMap.get(this).type = BBCodeNode.TEXT_NODE;
	    this.setContent(nodeOptions.content);
	    BBCodeNode.makeNonEnumerableProperty(this, 'children');
	  }
	  static isTextNodeContent(value) {
	    return main_core.Type.isString(value) || main_core.Type.isNumber(value);
	  }
	  setName(name) {}
	  setContent(content) {
	    if (BBCodeTextNode.isTextNodeContent(content)) {
	      this[contentSymbol] = content;
	    }
	  }
	  getContent() {
	    return this[contentSymbol];
	  }
	  adjustChildren() {}
	  getLength() {
	    return String(this[contentSymbol]).length;
	  }
	  isEmpty() {
	    return this.getLength() === 0;
	  }
	  clone(options) {
	    return this.getScheme().createText({
	      content: this.getContent()
	    });
	  }
	  split(options) {
	    const {
	      offset: sourceOffset,
	      byWord = false
	    } = options;
	    if (!main_core.Type.isNumber(sourceOffset)) {
	      throw new TypeError('offset is not a number');
	    }
	    const contentLength = this.getLength();
	    if (sourceOffset < 0 || sourceOffset > contentLength) {
	      throw new TypeError(`offset '${sourceOffset}' is out of range ${0}-${contentLength}`);
	    }
	    const content = this.getContent();
	    const offset = (() => {
	      if (byWord && sourceOffset !== contentLength) {
	        const lastIndex = content.lastIndexOf(' ', sourceOffset);
	        if (lastIndex !== -1) {
	          if (sourceOffset > lastIndex) {
	            return lastIndex + 1;
	          }
	          return lastIndex;
	        }
	        return 0;
	      }
	      return sourceOffset;
	    })();
	    const leftNode = (() => {
	      if (offset === contentLength) {
	        return this;
	      }
	      if (offset === 0) {
	        return null;
	      }
	      const node = this.clone();
	      node.setContent(content.slice(0, offset));
	      return node;
	    })();
	    const rightNode = (() => {
	      if (offset === 0) {
	        return this;
	      }
	      if (offset === contentLength) {
	        return null;
	      }
	      const node = this.clone();
	      node.setContent(content.slice(offset, contentLength));
	      return node;
	    })();
	    return [leftNode, rightNode];
	  }
	  toString(options = {}) {
	    if (options.encode !== false) {
	      return this.getEncoder().encodeText(this.getContent());
	    }
	    return this.getContent();
	  }
	  toPlainText() {
	    return this.toString({
	      encode: false
	    });
	  }
	  toJSON() {
	    return {
	      name: this.getName(),
	      content: this.toString()
	    };
	  }
	}

	class BBCodeNewLineNode extends BBCodeTextNode {
	  constructor(options = {}) {
	    super(options);
	    this[nameSymbol] = '#linebreak';
	    this[contentSymbol] = '\n';
	  }
	  setContent(options) {}
	  clone(options) {
	    return this.getScheme().createNewLine();
	  }
	}

	class BBCodeTabNode extends BBCodeTextNode {
	  constructor(options = {}) {
	    super(options);
	    this[nameSymbol] = '#tab';
	    this[contentSymbol] = '\t';
	  }
	  setContent(options) {}
	  clone(options) {
	    return this.getScheme().createTab();
	  }
	}

	class BBCodeNodeScheme {
	  constructor(options) {
	    this.name = [];
	    this.group = [];
	    this.stringifier = null;
	    this.serializer = null;
	    this.allowedIn = [];
	    this.onChangeHandler = null;
	    if (!main_core.Type.isPlainObject(options)) {
	      throw new TypeError('options is not a object');
	    }
	    if (!main_core.Type.isArrayFilled(this.name) && !main_core.Type.isArrayFilled(options.name) && !main_core.Type.isStringFilled(options.name)) {
	      throw new TypeError('options.name is not specified');
	    }
	    this.setGroup(options.group);
	    this.setName(options.name);
	    this.setAllowedIn(options.allowedIn);
	    this.setStringifier(options.stringify);
	    this.setSerializer(options.serialize);
	    this.setOnChangeHandler(options.onChange);
	  }
	  setName(name) {
	    if (main_core.Type.isStringFilled(name)) {
	      this.name = [name];
	      this.runOnChangeHandler();
	    }
	    if (main_core.Type.isArrayFilled(name)) {
	      this.name = name;
	      this.runOnChangeHandler();
	    }
	  }
	  getName() {
	    return this.name;
	  }
	  removeName(...names) {
	    this.setName(this.getName().filter(name => {
	      return !names.includes(name);
	    }));
	    this.runOnChangeHandler();
	  }
	  setGroup(name) {
	    if (main_core.Type.isStringFilled(name)) {
	      this.group = [name];
	      this.runOnChangeHandler();
	    }
	    if (main_core.Type.isArrayFilled(name)) {
	      this.group = name;
	      this.runOnChangeHandler();
	    }
	  }
	  removeGroup(...groups) {
	    this.setGroup(this.getGroup().filter(group => {
	      return !groups.includes(group);
	    }));
	    this.runOnChangeHandler();
	  }
	  getGroup() {
	    return this.group;
	  }
	  hasGroup(groupName) {
	    return this.getGroup().includes(groupName);
	  }
	  setStringifier(stringifier) {
	    if (main_core.Type.isFunction(stringifier) || main_core.Type.isNull(stringifier)) {
	      this.stringifier = stringifier;
	    }
	  }
	  getStringifier() {
	    return this.stringifier;
	  }
	  setSerializer(serializer) {
	    if (main_core.Type.isFunction(serializer) || main_core.Type.isNull(serializer)) {
	      this.serializer = serializer;
	    }
	  }
	  getSerializer() {
	    return this.serializer;
	  }
	  setAllowedIn(allowedParents) {
	    if (main_core.Type.isArray(allowedParents)) {
	      this.allowedIn = [...allowedParents];
	      this.runOnChangeHandler();
	    }
	  }
	  getAllowedIn() {
	    return this.allowedIn;
	  }
	  isAllowedIn(tagName) {
	    const allowedIn = this.getAllowedIn();
	    return !main_core.Type.isArrayFilled(allowedIn) || main_core.Type.isArrayFilled(allowedIn) && allowedIn.includes(tagName);
	  }
	  setOnChangeHandler(handler) {
	    this.onChangeHandler = handler;
	  }
	  getOnChangeHandler() {
	    return this.onChangeHandler;
	  }
	  runOnChangeHandler() {
	    const handler = this.getOnChangeHandler();
	    if (main_core.Type.isFunction(handler)) {
	      handler();
	    }
	  }
	}

	const canBeEmptySymbol = Symbol('@canBeEmpty');
	const voidSymbol$1 = Symbol('@void');
	class BBCodeTagScheme extends BBCodeNodeScheme {
	  constructor(options) {
	    super(options);
	    this[voidSymbol$1] = false;
	    this[canBeEmptySymbol] = true;
	    this.childConverter = null;
	    this.allowedChildren = [];
	    this.notAllowedChildrenCallback = null;
	    this.setVoid(options.void);
	    this.setCanBeEmpty(options.canBeEmpty);
	    this.setChildConverter(options.convertChild);
	    this.setAllowedChildren(options.allowedChildren);
	    this.setOnChangeHandler(options.onChange);
	    this.setNotAllowedChildrenCallback(options.onNotAllowedChildren);
	  }
	  static defaultBlockStringifier(node, scheme, options = {}) {
	    const isAllowNewlineBeforeOpeningTag = (() => {
	      const previewsSibling = node.getPreviewsSibling();
	      return previewsSibling && previewsSibling.getName() !== '#linebreak';
	    })();
	    const isAllowNewlineAfterClosingTag = (() => {
	      const nextSibling = node.getNextSibling();
	      return nextSibling && nextSibling.getName() !== '#linebreak' && !(nextSibling.getType() === BBCodeNode.ELEMENT_NODE && !nextSibling.getTagScheme().getGroup().includes('#inline'));
	    })();
	    const openingTag = node.getOpeningTag();
	    const content = node.getContent(options);
	    const closingTag = node.getClosingTag();
	    const isAllowContentLinebreaks = content.length > 0;
	    return [isAllowNewlineBeforeOpeningTag ? '\n' : '', openingTag, isAllowContentLinebreaks ? '\n' : '', content, isAllowContentLinebreaks ? '\n' : '', closingTag, isAllowNewlineAfterClosingTag ? '\n' : ''].join('');
	  }
	  setVoid(value) {
	    if (main_core.Type.isBoolean(value)) {
	      this[voidSymbol$1] = value;
	      this.runOnChangeHandler();
	    }
	  }
	  isVoid() {
	    return this[voidSymbol$1];
	  }
	  setCanBeEmpty(value) {
	    if (main_core.Type.isBoolean(value)) {
	      this[canBeEmptySymbol] = value;
	      this.runOnChangeHandler();
	    }
	  }
	  canBeEmpty() {
	    return this[canBeEmptySymbol];
	  }
	  setChildConverter(converter) {
	    if (main_core.Type.isFunction(converter) || main_core.Type.isNull(converter)) {
	      this.childConverter = converter;
	    }
	  }
	  getChildConverter() {
	    return this.childConverter;
	  }
	  setAllowedChildren(allowedChildren) {
	    if (main_core.Type.isArray(allowedChildren)) {
	      this.allowedChildren = allowedChildren;
	      this.runOnChangeHandler();
	    }
	  }
	  getAllowedChildren() {
	    return this.allowedChildren;
	  }
	  isChildAllowed(tagName) {
	    const allowedChildren = this.getAllowedChildren();
	    return !main_core.Type.isArrayFilled(allowedChildren) || main_core.Type.isArrayFilled(allowedChildren) && allowedChildren.includes(tagName);
	  }
	  setNotAllowedChildrenCallback(callback) {
	    this.notAllowedChildrenCallback = callback;
	  }
	  hasNotAllowedChildrenCallback() {
	    return main_core.Type.isFunction(this.notAllowedChildrenCallback);
	  }
	  runNotAllowedChildrenCallback(options) {
	    if (main_core.Type.isFunction(this.notAllowedChildrenCallback)) {
	      this.notAllowedChildrenCallback(options);
	    }
	  }
	}

	class BBCodeScheme {
	  static isNodeScheme(value) {
	    return value instanceof BBCodeNodeScheme;
	  }
	  static getTagName(node) {
	    if (main_core.Type.isString(node)) {
	      return node;
	    }
	    if (main_core.Type.isObject(node) && node instanceof BBCodeNode) {
	      return node.getName();
	    }
	    return null;
	  }
	  constructor(options = {}) {
	    this.tagSchemes = [];
	    this.outputTagCase = BBCodeScheme.Case.LOWER;
	    this.unresolvedNodesHoisting = true;
	    this.encoder = new ui_bbcode_encoder.BBCodeEncoder();
	    this.parentChildMap = null;
	    if (!main_core.Type.isPlainObject(options)) {
	      throw new TypeError('options is not a object');
	    }
	    this.onTagSchemeChange = this.onTagSchemeChange.bind(this);
	    this.setTagSchemes(options.tagSchemes);
	    this.setOutputTagCase(options.outputTagCase);
	    this.setUnresolvedNodesHoisting(options.unresolvedNodesHoisting);
	    this.setEncoder(options.encoder);
	  }
	  onTagSchemeChange() {
	    this.parentChildMap = null;
	  }
	  setTagSchemes(tagSchemes) {
	    if (main_core.Type.isArray(tagSchemes)) {
	      const invalidSchemeIndex = tagSchemes.findIndex(scheme => {
	        return !BBCodeScheme.isNodeScheme(scheme);
	      });
	      if (invalidSchemeIndex > -1) {
	        throw new TypeError(`tagScheme #${invalidSchemeIndex} is not TagScheme instance`);
	      }
	      tagSchemes.forEach(tagScheme => {
	        tagScheme.setOnChangeHandler(this.onTagSchemeChange);
	      });
	      this.tagSchemes = [...tagSchemes];
	    }
	  }
	  setTagScheme(...tagSchemes) {
	    const invalidSchemeIndex = tagSchemes.findIndex(scheme => {
	      return !BBCodeScheme.isNodeScheme(scheme);
	    });
	    if (invalidSchemeIndex > -1) {
	      throw new TypeError(`tagScheme #${invalidSchemeIndex} is not TagScheme instance`);
	    }
	    const newTagSchemesNames = tagSchemes.flatMap(scheme => {
	      return scheme.getName();
	    });
	    const currentTagSchemes = this.getTagSchemes();
	    currentTagSchemes.forEach(scheme => {
	      scheme.removeName(...newTagSchemesNames);
	    });
	    const filteredCurrentTagSchemes = currentTagSchemes.filter(scheme => {
	      return main_core.Type.isArrayFilled(scheme.getName());
	    });
	    this.setTagSchemes([...filteredCurrentTagSchemes, ...tagSchemes]);
	  }
	  getTagSchemes() {
	    return [...this.tagSchemes];
	  }
	  getTagScheme(node) {
	    const tagName = BBCodeScheme.getTagName(node);
	    if (main_core.Type.isString(tagName)) {
	      return this.getTagSchemes().find(scheme => {
	        return scheme.getName().includes(tagName.toLowerCase());
	      });
	    }
	    return null;
	  }
	  setOutputTagCase(tagCase) {
	    if (!main_core.Type.isNil(tagCase)) {
	      const allowedCases = Object.values(BBCodeScheme.Case);
	      if (allowedCases.includes(tagCase)) {
	        this.outputTagCase = tagCase;
	      } else {
	        throw new TypeError(`'${tagCase}' is not allowed`);
	      }
	    }
	  }
	  getOutputTagCase() {
	    return this.outputTagCase;
	  }
	  setUnresolvedNodesHoisting(value) {
	    if (!main_core.Type.isNil(value)) {
	      if (main_core.Type.isBoolean(value)) {
	        this.unresolvedNodesHoisting = value;
	      } else {
	        throw new TypeError(`'${value}' is not allowed value`);
	      }
	    }
	  }
	  isAllowedUnresolvedNodesHoisting() {
	    return this.unresolvedNodesHoisting;
	  }
	  setEncoder(encoder) {
	    if (encoder instanceof ui_bbcode_encoder.BBCodeEncoder) {
	      this.encoder = encoder;
	    }
	  }
	  getEncoder() {
	    return this.encoder;
	  }
	  getAllowedTags() {
	    return this.getTagSchemes().flatMap(tagScheme => {
	      return tagScheme.getName();
	    });
	  }
	  isAllowedTag(node) {
	    const allowedTags = this.getAllowedTags();
	    const tagName = BBCodeScheme.getTagName(node);
	    return allowedTags.includes(String(tagName).toLowerCase());
	  }
	  isVoid(node) {
	    const tagScheme = this.getTagScheme(node);
	    if (tagScheme) {
	      return tagScheme.isVoid();
	    }
	    return false;
	  }
	  isElement(node) {
	    return node && node.getType() === BBCodeNode.ELEMENT_NODE;
	  }
	  isRoot(node) {
	    return node && node.getName() === '#root';
	  }
	  isFragment(node) {
	    return node && node.getName() === '#fragment';
	  }
	  isAnyText(node) {
	    return node && node.getType() === BBCodeNode.TEXT_NODE;
	  }
	  isText(node) {
	    return node && node.getName() === '#text';
	  }
	  isNewLine(node) {
	    return node && node.getName() === '#linebreak';
	  }
	  isTab(node) {
	    return node && node.getName() === '#tab';
	  }
	  getParentChildMap() {
	    if (main_core.Type.isNull(this.parentChildMap)) {
	      const tagSchemes = this.getTagSchemes();
	      const map = new Map();
	      tagSchemes.forEach(tagScheme => {
	        const groups = tagScheme.getGroup();
	        const schemeNames = [...tagScheme.getName(), ...groups, ...(tagScheme.isVoid() ? ['#void'] : [])];
	        const allowedChildren = tagScheme.getAllowedChildren();
	        const allowedIn = tagScheme.getAllowedIn();
	        schemeNames.forEach(name => {
	          if (!map.has(name)) {
	            map.set(name, {
	              allowedChildren: new Set(),
	              allowedIn: new Set(),
	              aliases: new Set()
	            });
	          }
	          const entry = map.get(name);
	          const newEntry = {
	            allowedChildren: new Set([...entry.allowedChildren, ...allowedChildren]),
	            allowedIn: new Set([...entry.allowedIn, ...allowedIn]),
	            aliases: new Set([name, ...groups, ...(tagScheme.isVoid() ? ['#void'] : [])])
	          };
	          map.set(name, newEntry);
	        });
	      });
	      this.parentChildMap = map;
	    }
	    return this.parentChildMap;
	  }
	  isChildAllowed(parent, child) {
	    const parentName = BBCodeScheme.getTagName(parent);
	    const childName = BBCodeScheme.getTagName(child);
	    if (main_core.Type.isStringFilled(parentName) && main_core.Type.isStringFilled(childName)) {
	      if (parentName === '#fragment') {
	        return true;
	      }
	      const parentChildMap = this.getParentChildMap();
	      const parentMap = parentChildMap.get(parentName);
	      const childMap = parentChildMap.get(childName);
	      if (main_core.Type.isPlainObject(parentMap) && main_core.Type.isPlainObject(childMap)) {
	        return (parentMap.allowedChildren.size === 0 || [...childMap.aliases].some(name => {
	          return parentMap.allowedChildren.has(name);
	        })) && (childMap.allowedIn.size === 0 || [...parentMap.aliases].some(name => {
	          return childMap.allowedIn.has(name);
	        }));
	      }
	    }
	    return false;
	  }
	  createRoot(options = {}) {
	    return new BBCodeRootNode({
	      ...options,
	      scheme: this
	    });
	  }
	  createNode(options) {
	    if (!main_core.Type.isPlainObject(options)) {
	      throw new TypeError('options is not a object');
	    }
	    if (!main_core.Type.isStringFilled(options.name)) {
	      throw new TypeError('options.name is required');
	    }
	    if (!this.isAllowedTag(options.name)) {
	      throw new TypeError(`Scheme for "${options.name}" tag is not specified.`);
	    }
	    return new BBCodeNode({
	      ...options,
	      scheme: this
	    });
	  }
	  createElement(options = {}) {
	    if (!main_core.Type.isPlainObject(options)) {
	      throw new TypeError('options is not a object');
	    }
	    if (!main_core.Type.isStringFilled(options.name)) {
	      throw new TypeError('options.name is required');
	    }
	    if (!this.isAllowedTag(options.name)) {
	      throw new TypeError(`Scheme for "${options.name}" tag is not specified.`);
	    }
	    return new BBCodeElementNode({
	      ...options,
	      scheme: this
	    });
	  }
	  createText(options = {}) {
	    const preparedOptions = main_core.Type.isPlainObject(options) ? options : {
	      content: options
	    };
	    return new BBCodeTextNode({
	      ...preparedOptions,
	      scheme: this
	    });
	  }
	  createNewLine(options = {}) {
	    const preparedOptions = main_core.Type.isPlainObject(options) ? options : {
	      content: options
	    };
	    return new BBCodeNewLineNode({
	      ...preparedOptions,
	      scheme: this
	    });
	  }
	  createTab(options = {}) {
	    const preparedOptions = main_core.Type.isPlainObject(options) ? options : {
	      content: options
	    };
	    return new BBCodeTabNode({
	      ...preparedOptions,
	      scheme: this
	    });
	  }
	  createFragment(options = {}) {
	    return new BBCodeFragmentNode({
	      ...options,
	      scheme: this
	    });
	  }
	}
	BBCodeScheme.Case = {
	  LOWER: 'lower',
	  UPPER: 'upper'
	};

	class BBCodeTextScheme extends BBCodeNodeScheme {
	  constructor(options) {
	    super({
	      ...options,
	      name: ['#text']
	    });
	  }
	}

	class BBCodeNewLineScheme extends BBCodeNodeScheme {
	  constructor(options = {}) {
	    super({
	      ...options,
	      name: ['#linebreak']
	    });
	  }
	}

	class BBCodeTabScheme extends BBCodeNodeScheme {
	  constructor(options) {
	    super({
	      ...options,
	      name: ['#tab']
	    });
	  }
	}

	class DefaultBBCodeScheme extends BBCodeScheme {
	  constructor(options = {}) {
	    const tagSchemes = [new BBCodeTagScheme({
	      name: ['b', 'u', 'i', 's'],
	      group: ['#inline', '#format'],
	      allowedChildren: ['#text', '#linebreak', '#inline'],
	      canBeEmpty: false
	    }), new BBCodeTagScheme({
	      name: ['img'],
	      group: ['#inlineBlock'],
	      allowedChildren: ['#text'],
	      canBeEmpty: false
	    }), new BBCodeTagScheme({
	      name: ['url'],
	      group: ['#inline'],
	      allowedChildren: ['#text', '#format', 'img'],
	      canBeEmpty: false,
	      stringify(node) {
	        const openingTag = node.getOpeningTag();
	        const closingTag = node.getClosingTag();
	        const content = node.getContent();
	        return `${openingTag}${content}${closingTag}`;
	      }
	    }), new BBCodeTagScheme({
	      name: 'p',
	      group: ['#block'],
	      allowedChildren: ['#text', '#linebreak', '#inline', '#inlineBlock'],
	      stringify: BBCodeTagScheme.defaultBlockStringifier,
	      allowedIn: ['#root', '#shadowRoot']
	    }), new BBCodeTagScheme({
	      name: 'list',
	      group: ['#block'],
	      allowedChildren: ['*'],
	      stringify: BBCodeTagScheme.defaultBlockStringifier,
	      allowedIn: ['#root', '#shadowRoot'],
	      canBeEmpty: false,
	      onNotAllowedChildren: ({
	        node,
	        children
	      }) => {
	        const notAllowedChildren = new Set(['#tab', '#linebreak']);
	        const bePropagated = [];
	        children.forEach(child => {
	          if (notAllowedChildren.has(child.getName()) || child.getName() === '#text' && /^\s+$/.test(child.getContent())) {
	            child.remove();
	          } else {
	            bePropagated.push(child);
	          }
	        });
	        node.propagateChild(...bePropagated);
	      }
	    }), new BBCodeTagScheme({
	      name: ['*'],
	      allowedChildren: ['#text', '#linebreak', '#inline', '#inlineBlock'],
	      stringify: (node, scheme, toStringOptions) => {
	        const openingTag = node.getOpeningTag();
	        const content = node.getContent(toStringOptions).trim();
	        return `${openingTag}${content}`;
	      },
	      allowedIn: ['list'],
	      onNotAllowedChildren: ({
	        node,
	        children
	      }) => {
	        const bePropagated = [];
	        children.forEach(child => {
	          if (child.getName() === '#tab') {
	            child.remove();
	          } else {
	            bePropagated.push(child);
	          }
	        });
	        node.propagateChild(...bePropagated);
	      }
	    }), new BBCodeTagScheme({
	      name: 'table',
	      group: ['#block'],
	      allowedChildren: ['tr'],
	      stringify: BBCodeTagScheme.defaultBlockStringifier,
	      allowedIn: ['#root', 'td', 'th', 'quote', 'spoiler'],
	      canBeEmpty: false
	    }), new BBCodeTagScheme({
	      name: 'tr',
	      allowedChildren: ['th', 'td'],
	      allowedIn: ['table'],
	      canBeEmpty: false
	    }), new BBCodeTagScheme({
	      name: ['th', 'td'],
	      group: ['#shadowRoot'],
	      allowedChildren: ['#text', '#linebreak', '#inline', '#inlineBlock', '#block'],
	      allowedIn: ['tr']
	    }), new BBCodeTagScheme({
	      name: 'quote',
	      group: ['#block', '#shadowRoot'],
	      allowedChildren: ['#text', '#linebreak', '#inline', '#inlineBlock', '#block'],
	      allowedIn: ['#root', '#shadowRoot']
	    }), new BBCodeTagScheme({
	      name: 'code',
	      group: ['#block'],
	      stringify: BBCodeTagScheme.defaultBlockStringifier,
	      allowedChildren: ['#text', '#linebreak', '#tab'],
	      allowedIn: ['#root', '#shadowRoot'],
	      convertChild: (child, scheme, toStringOptions) => {
	        if (['#linebreak', '#tab', '#text'].includes(child.getName())) {
	          return child;
	        }
	        return scheme.createText(child.toString(toStringOptions));
	      }
	    }), new BBCodeTagScheme({
	      name: 'video',
	      group: ['#inlineBlock'],
	      allowedChildren: ['#text'],
	      allowedIn: ['#root', '#shadowRoot', 'p'],
	      canBeEmpty: false
	    }), new BBCodeTagScheme({
	      name: 'spoiler',
	      group: ['#block', '#shadowRoot'],
	      allowedChildren: ['#text', '#linebreak', '#inline', '#inlineBlock', '#block'],
	      allowedIn: ['#root', '#shadowRoot']
	    }), new BBCodeTagScheme({
	      name: ['user', 'project', 'department'],
	      group: ['#inline', '#mention'],
	      allowedChildren: ['#text', '#format'],
	      canBeEmpty: false
	    }), new BBCodeTagScheme({
	      name: ['#root']
	    }), new BBCodeTagScheme({
	      name: ['#fragment']
	    }), new BBCodeTagScheme({
	      name: ['#text']
	    }), new BBCodeTagScheme({
	      name: ['#linebreak']
	    }), new BBCodeTagScheme({
	      name: ['#tab'],
	      stringify: () => {
	        return '';
	      }
	    })];
	    if ((options == null ? void 0 : options.fileTag) !== 'none') {
	      tagSchemes.push(new BBCodeTagScheme({
	        name: (options == null ? void 0 : options.fileTag) === 'file' ? 'file' : 'disk',
	        group: ['#inline'],
	        void: true
	      }));
	    }
	    super({
	      tagSchemes,
	      outputTagCase: BBCodeScheme.Case.LOWER,
	      unresolvedNodesHoisting: true
	    });
	    if (main_core.Type.isPlainObject(options)) {
	      this.setTagSchemes(options.tagSchemes);
	      this.setOutputTagCase(options.outputTagCase);
	      this.setUnresolvedNodesHoisting(options.unresolvedNodesHoisting);
	    }
	  }
	}

	exports.BBCodeNode = BBCodeNode;
	exports.BBCodeRootNode = BBCodeRootNode;
	exports.BBCodeElementNode = BBCodeElementNode;
	exports.BBCodeFragmentNode = BBCodeFragmentNode;
	exports.BBCodeNewLineNode = BBCodeNewLineNode;
	exports.BBCodeTabNode = BBCodeTabNode;
	exports.BBCodeTextNode = BBCodeTextNode;
	exports.BBCodeScheme = BBCodeScheme;
	exports.BBCodeTagScheme = BBCodeTagScheme;
	exports.BBCodeTextScheme = BBCodeTextScheme;
	exports.BBCodeNewLineScheme = BBCodeNewLineScheme;
	exports.BBCodeTabScheme = BBCodeTabScheme;
	exports.DefaultBBCodeScheme = DefaultBBCodeScheme;

}((this.BX.UI.BBCode = this.BX.UI.BBCode || {}),BX.UI.BBCode,BX));
//# sourceMappingURL=model.bundle.js.map
