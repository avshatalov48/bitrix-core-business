/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,ui_bbcode_parser,main_core,ui_bbcode_model) {
	'use strict';

	const nameSymbol = Symbol('name');
	const groupSymbol = Symbol('group');
	const validateSymbol = Symbol('validate');
	const beforeSymbol = Symbol('before');
	const convertSymbol = Symbol('convert');
	const forChildSymbol = Symbol('forChild');
	const afterSymbol = Symbol('after');
	const formatterSymbol = Symbol('formatter');
	const defaultValidator = () => true;
	const defaultNodeConverter = ({
	  node
	}) => node;
	const defaultElementConverter = ({
	  element
	}) => element;
	class NodeFormatter {
	  constructor(options = {}) {
	    this[nameSymbol] = 'unknown';
	    this[groupSymbol] = null;
	    this[beforeSymbol] = null;
	    this[convertSymbol] = null;
	    this[forChildSymbol] = null;
	    this[afterSymbol] = null;
	    if (main_core.Type.isArray(options.name)) {
	      this[groupSymbol] = [...options.name];
	    } else {
	      this.setName(options.name);
	    }
	    if (!main_core.Type.isNil(options.formatter)) {
	      this.setFormatter(options.formatter);
	    }
	    this.setValidate(options.validate);
	    this.setBefore(options.before);
	    this.setConvert(options.convert);
	    this.setForChild(options.forChild);
	    this.setAfter(options.after);
	  }
	  setName(name) {
	    if (!main_core.Type.isStringFilled(name)) {
	      throw new TypeError('Name is not a string');
	    }
	    this[nameSymbol] = name;
	  }
	  getName() {
	    return this[nameSymbol];
	  }
	  setValidate(callback) {
	    if (main_core.Type.isFunction(callback)) {
	      this[validateSymbol] = callback;
	    } else {
	      this[validateSymbol] = defaultValidator;
	    }
	  }
	  validate(options) {
	    const result = this[validateSymbol](options);
	    if (main_core.Type.isBoolean(result)) {
	      return result;
	    }
	    throw new TypeError(`Validate callback for "${this.getName()}" returned not boolean`);
	  }
	  setBefore(callback) {
	    if (main_core.Type.isFunction(callback)) {
	      this[beforeSymbol] = callback;
	    } else {
	      this[beforeSymbol] = defaultNodeConverter;
	    }
	  }
	  runBefore(options) {
	    return this[beforeSymbol](options);
	  }
	  setConvert(callback) {
	    if (!main_core.Type.isFunction(callback)) {
	      throw new TypeError('Convert is not a function');
	    }
	    this[convertSymbol] = callback;
	  }
	  runConvert(options) {
	    return this[convertSymbol](options);
	  }
	  setForChild(callback) {
	    if (main_core.Type.isFunction(callback)) {
	      this[forChildSymbol] = callback;
	    } else {
	      this[forChildSymbol] = defaultElementConverter;
	    }
	  }
	  runForChild(options) {
	    return this[forChildSymbol](options);
	  }
	  setAfter(callback) {
	    if (main_core.Type.isFunction(callback)) {
	      this[afterSymbol] = callback;
	    } else {
	      this[afterSymbol] = defaultElementConverter;
	    }
	  }
	  runAfter(options) {
	    return this[afterSymbol](options);
	  }
	  setFormatter(formatter) {
	    this[formatterSymbol] = formatter;
	  }
	  getFormatter() {
	    return this[formatterSymbol];
	  }
	}

	const formattersSymbol = Symbol('formatters');
	const onUnknownSymbol = Symbol('onUnknown');
	const dataSymbol = Symbol('data');

	/**
	 * @memberOf BX.UI.BBCode
	 */
	class Formatter {
	  constructor(options = {}) {
	    this[formattersSymbol] = new Map();
	    this[onUnknownSymbol] = null;
	    this[dataSymbol] = null;
	    this.setNodeFormatters(options.formatters);
	    if (main_core.Type.isNil(options.onUnknown)) {
	      this.setOnUnknown(this.getDefaultUnknownNodeCallback());
	    } else {
	      this.setOnUnknown(options.onUnknown);
	    }
	  }
	  isElement(source) {
	    return main_core.Type.isObject(source) && main_core.Type.isFunction(source.appendChild);
	  }
	  static prepareSourceNode(source) {
	    if (source instanceof ui_bbcode_model.BBCodeNode) {
	      return source;
	    }
	    if (main_core.Type.isString(source)) {
	      return new ui_bbcode_parser.BBCodeParser().parse(source);
	    }
	    return null;
	  }
	  setData(data) {
	    this[dataSymbol] = data;
	  }
	  getData() {
	    return this[dataSymbol];
	  }
	  setNodeFormatters(formatters) {
	    if (main_core.Type.isArrayFilled(formatters)) {
	      formatters.forEach(formatter => {
	        this.setNodeFormatter(formatter);
	      });
	    }
	  }
	  setNodeFormatter(formatter) {
	    if (formatter instanceof NodeFormatter) {
	      this[formattersSymbol].set(formatter.getName(), formatter);
	    } else {
	      throw new TypeError('formatter is not a NodeFormatter instance.');
	    }
	  }
	  getDefaultUnknownNodeCallback() {
	    throw new TypeError('Must be implemented in subclass');
	  }
	  setOnUnknown(callback) {
	    if (main_core.Type.isFunction(callback)) {
	      this[onUnknownSymbol] = callback;
	    } else {
	      throw new TypeError('OnUnknown callback is not a function.');
	    }
	  }
	  runOnUnknown(options) {
	    const result = this[onUnknownSymbol](options);
	    if (result instanceof NodeFormatter || main_core.Type.isNull(result)) {
	      return result;
	    }
	    throw new TypeError('OnUnknown callback returned not NodeFormatter instance or null.');
	  }
	  getNodeFormatter(node) {
	    const formatter = this[formattersSymbol].get(node.getName());
	    if (formatter instanceof NodeFormatter) {
	      return formatter;
	    }
	    return this.runOnUnknown({
	      node,
	      formatter: this
	    });
	  }
	  getNodeFormatters() {
	    return this[formattersSymbol];
	  }
	  format(options) {
	    if (!main_core.Type.isPlainObject(options)) {
	      throw new TypeError('options is not a object');
	    }
	    const {
	      source,
	      data = {}
	    } = options;
	    if (!main_core.Type.isUndefined(data) && !main_core.Type.isPlainObject(data)) {
	      throw new TypeError('options.data is not a object');
	    }
	    this.setData(data);
	    const sourceNode = Formatter.prepareSourceNode(source);
	    if (main_core.Type.isNull(sourceNode)) {
	      throw new TypeError('options.source is not a BBCodeNode or string');
	    }
	    const nodeFormatter = this.getNodeFormatter(sourceNode);
	    const isValidNode = nodeFormatter.validate({
	      node: sourceNode,
	      formatter: this,
	      data
	    });
	    if (!isValidNode) {
	      return null;
	    }
	    const preparedNode = nodeFormatter.runBefore({
	      node: sourceNode,
	      formatter: this,
	      data
	    });
	    if (main_core.Type.isNull(preparedNode)) {
	      return null;
	    }
	    const convertedElement = nodeFormatter.runConvert({
	      node: preparedNode,
	      formatter: this,
	      data
	    });
	    if (main_core.Type.isNull(convertedElement)) {
	      return null;
	    }
	    preparedNode.getChildren().forEach(childNode => {
	      const childElement = this.format({
	        source: childNode,
	        data
	      });
	      if (childElement !== null) {
	        const convertedChildElement = nodeFormatter.runForChild({
	          node: childNode,
	          element: childElement,
	          formatter: this,
	          data
	        });
	        if (convertedChildElement !== null && this.isElement(convertedElement)) {
	          convertedElement.appendChild(convertedChildElement);
	        }
	      }
	    });
	    return nodeFormatter.runAfter({
	      node: preparedNode,
	      element: convertedElement,
	      formatter: this,
	      data
	    });
	  }
	}

	exports.Formatter = Formatter;
	exports.NodeFormatter = NodeFormatter;

}((this.BX.UI.BBCode = this.BX.UI.BBCode || {}),BX.UI.BBCode,BX,BX.UI.BBCode));
//# sourceMappingURL=formatter.bundle.js.map
