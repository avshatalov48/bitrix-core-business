import BX from '../../src/internal/bx';
import * as lib from '../../src/core';

global.BX = BX;
window.BX = BX;
Object.keys(lib).forEach(item => global.BX[item] = lib[item]);

const types = {
	'null': null,
	'undefined': undefined,
	'notEmptyString': 'not empty string',
	'emptyString': '',
	'integer': 99,
	'float': 0.99,
	'true': true,
	'false': false,
	'emptyObject': {},
	'notEmptyObject': {1: 1, '2': 2, bool: true, 'null': null, 'undefined': undefined, 'object': {}, 'array': []},
	'notPlainObject': new (function TestF() {}),
	'emptyArray': [],
	'notEmptyArray': [1, '2', true, null, undefined, {test: 'test'}, ['test']],
	'function': function() {},
	'date': new Date(),
	'htmlelement': document.createElement('hr'),
	'textNode': document.createTextNode('test'),
	'set': new Set(),
	'map': new Map(),
	'weakMap': new WeakMap(),
	'weakSet': new WeakSet(),
	'prototype': Map.prototype,
	'regexp': new RegExp('(.*)'),
	'regexpShortSyntax': /(.*)/,
	'cssClassName': 'test-class-name',
	'cssClassNames': ['class1', 'class2', 'class3', 'class4', 'class5']
};

describe('Core js', () => {
	describe('BX', () => {
		it('Should be function', () => {
			assert(typeof BX === 'function');
		});

		it('Should be return element by id', () => {
			let element = document.createElement('div');
			element.id = 'testElement';
			document.body.appendChild(element);

			assert(BX('testElement') === element);
			assert(BX('testElement').id === element.id);
		});

		it('Should be return null if element not exists', () => {
			assert(BX('undefinedElement') === null);
		});

		it('Should be execute callback on ready event', () => {
			BX(() => assert(true));
		});
	});

	describe('BX.type', () => {
		it('.isString()', () => {
			assert(BX.type.isString(types.notEmptyString) === true);
			assert(BX.type.isString(types.emptyString) === true);

			assert(BX.type.isString() === false);
			assert(BX.type.isString(types.null) === false);
			assert(BX.type.isString(types.undefined) === false);
			assert(BX.type.isString(types.integer) === false);
			assert(BX.type.isString(types.float) === false);
			assert(BX.type.isString(types.true) === false);
			assert(BX.type.isString(types.false) === false);
			assert(BX.type.isString(types.emptyObject) === false);
			assert(BX.type.isString(types.notEmptyObject) === false);
			assert(BX.type.isString(types.notPlainObject) === false);
			assert(BX.type.isString(types.emptyArray) === false);
			assert(BX.type.isString(types.notEmptyArray) === false);
			assert(BX.type.isString(types.function) === false);
		});

		it('.isFunction()', () => {
			assert(BX.type.isFunction(types.function) === true);

			assert(BX.type.isFunction(types.notEmptyString) === false);
			assert(BX.type.isFunction(types.emptyString) === false);
			assert(BX.type.isFunction() === false);
			assert(BX.type.isFunction(types.null) === false);
			assert(BX.type.isFunction(types.undefined) === false);
			assert(BX.type.isFunction(types.integer) === false);
			assert(BX.type.isFunction(types.float) === false);
			assert(BX.type.isFunction(types.true) === false);
			assert(BX.type.isFunction(types.false) === false);
			assert(BX.type.isFunction(types.emptyObject) === false);
			assert(BX.type.isFunction(types.notEmptyObject) === false);
			assert(BX.type.isFunction(types.emptyArray) === false);
			assert(BX.type.isFunction(types.notEmptyArray) === false);
			assert(BX.type.isFunction(types.notPlainObject) === false);
		});

		it('.isObject()', () => {
			assert(BX.type.isObject(types.emptyObject) === true);
			assert(BX.type.isObject(types.notEmptyObject) === true);
			assert(BX.type.isObject(types.emptyArray) === true);
			assert(BX.type.isObject(types.notEmptyArray) === true);
			assert(BX.type.isObject(types.function) === true);
			assert(BX.type.isObject(types.notPlainObject) === true);

			assert(BX.type.isObject(types.notEmptyString) === false);
			assert(BX.type.isObject(types.emptyString) === false);
			assert(BX.type.isObject() === false);
			assert(BX.type.isObject(types.null) === false);
			assert(BX.type.isObject(types.undefined) === false);
			assert(BX.type.isObject(types.integer) === false);
			assert(BX.type.isObject(types.float) === false);
			assert(BX.type.isObject(types.true) === false);
			assert(BX.type.isObject(types.false) === false);
		});

		it('.isObjectLike()', () => {
			assert(BX.type.isObjectLike(types.emptyObject) === true);
			assert(BX.type.isObjectLike(types.notEmptyObject) === true);
			assert(BX.type.isObjectLike(types.emptyArray) === true);
			assert(BX.type.isObjectLike(types.notEmptyArray) === true);
			assert(BX.type.isObjectLike(types.notPlainObject) === true);

			assert(BX.type.isObjectLike(types.notEmptyString) === false);
			assert(BX.type.isObjectLike(types.emptyString) === false);
			assert(BX.type.isObjectLike() === false);
			assert(BX.type.isObjectLike(types.null) === false);
			assert(BX.type.isObjectLike(types.undefined) === false);
			assert(BX.type.isObjectLike(types.integer) === false);
			assert(BX.type.isObjectLike(types.float) === false);
			assert(BX.type.isObjectLike(types.true) === false);
			assert(BX.type.isObjectLike(types.false) === false);
			assert(BX.type.isObjectLike(types.function) === false);
		});

		it('.isPlainObject()', () => {
			assert(BX.type.isPlainObject(types.emptyObject) === true);
			assert(BX.type.isPlainObject(types.notEmptyObject) === true);

			assert(BX.type.isPlainObject(types.notPlainObject) === false);
			assert(BX.type.isPlainObject(types.emptyArray) === false);
			assert(BX.type.isPlainObject(types.notEmptyArray) === false);
			assert(BX.type.isPlainObject(types.notEmptyString) === false);
			assert(BX.type.isPlainObject(types.emptyString) === false);
			assert(BX.type.isPlainObject() === false);
			assert(BX.type.isPlainObject(types.null) === false);
			assert(BX.type.isPlainObject(types.undefined) === false);
			assert(BX.type.isPlainObject(types.integer) === false);
			assert(BX.type.isPlainObject(types.float) === false);
			assert(BX.type.isPlainObject(types.true) === false);
			assert(BX.type.isPlainObject(types.false) === false);
			assert(BX.type.isPlainObject(types.function) === false);
		});

		it('.isBoolean()', () => {
			assert(BX.type.isBoolean(types.true) === true);
			assert(BX.type.isBoolean(types.false) === true);

			assert(BX.type.isBoolean(types.emptyObject) === false);
			assert(BX.type.isBoolean(types.notEmptyObject) === false);
			assert(BX.type.isBoolean(types.notPlainObject) === false);
			assert(BX.type.isBoolean(types.emptyArray) === false);
			assert(BX.type.isBoolean(types.notEmptyArray) === false);
			assert(BX.type.isBoolean(types.notEmptyString) === false);
			assert(BX.type.isBoolean(types.emptyString) === false);
			assert(BX.type.isBoolean() === false);
			assert(BX.type.isBoolean(types.null) === false);
			assert(BX.type.isBoolean(types.undefined) === false);
			assert(BX.type.isBoolean(types.integer) === false);
			assert(BX.type.isBoolean(types.float) === false);
			assert(BX.type.isBoolean(types.function) === false);
		});

		it('.isNumber()', () => {
			assert(BX.type.isNumber(types.integer) === true);
			assert(BX.type.isNumber(types.float) === true);

			assert(BX.type.isNumber(types.true) === false);
			assert(BX.type.isNumber(types.false) === false);
			assert(BX.type.isNumber(types.emptyObject) === false);
			assert(BX.type.isNumber(types.notEmptyObject) === false);
			assert(BX.type.isNumber(types.notPlainObject) === false);
			assert(BX.type.isNumber(types.emptyArray) === false);
			assert(BX.type.isNumber(types.notEmptyArray) === false);
			assert(BX.type.isNumber(types.notEmptyString) === false);
			assert(BX.type.isNumber(types.emptyString) === false);
			assert(BX.type.isNumber() === false);
			assert(BX.type.isNumber(types.null) === false);
			assert(BX.type.isNumber(types.undefined) === false);
			assert(BX.type.isNumber(types.function) === false);
		});

		it('.isInteger()', () => {
			assert(BX.type.isInteger(types.integer) === true);

			assert(BX.type.isInteger(types.float) === false);
			assert(BX.type.isInteger(types.true) === false);
			assert(BX.type.isInteger(types.false) === false);
			assert(BX.type.isInteger(types.emptyObject) === false);
			assert(BX.type.isInteger(types.notEmptyObject) === false);
			assert(BX.type.isInteger(types.notPlainObject) === false);
			assert(BX.type.isInteger(types.emptyArray) === false);
			assert(BX.type.isInteger(types.notEmptyArray) === false);
			assert(BX.type.isInteger(types.notEmptyString) === false);
			assert(BX.type.isInteger(types.emptyString) === false);
			assert(BX.type.isInteger() === false);
			assert(BX.type.isInteger(types.null) === false);
			assert(BX.type.isInteger(types.undefined) === false);
			assert(BX.type.isInteger(types.function) === false);
		});

		it('.isFloat()', () => {
			assert(BX.type.isFloat(types.float) === true);

			assert(BX.type.isFloat(types.integer) === false);
			assert(BX.type.isFloat(types.true) === false);
			assert(BX.type.isFloat(types.false) === false);
			assert(BX.type.isFloat(types.emptyObject) === false);
			assert(BX.type.isFloat(types.notEmptyObject) === false);
			assert(BX.type.isFloat(types.notPlainObject) === false);
			assert(BX.type.isFloat(types.emptyArray) === false);
			assert(BX.type.isFloat(types.notEmptyArray) === false);
			assert(BX.type.isFloat(types.notEmptyString) === false);
			assert(BX.type.isFloat(types.emptyString) === false);
			assert(BX.type.isFloat() === false);
			assert(BX.type.isFloat(types.null) === false);
			assert(BX.type.isFloat(types.undefined) === false);
			assert(BX.type.isFloat(types.function) === false);
		});

		it('.isArray()', () => {
			assert(BX.type.isArray(types.emptyArray) === true);
			assert(BX.type.isArray(types.notEmptyArray) === true);

			assert(BX.type.isArray(types.float) === false);
			assert(BX.type.isArray(types.integer) === false);
			assert(BX.type.isArray(types.true) === false);
			assert(BX.type.isArray(types.false) === false);
			assert(BX.type.isArray(types.emptyObject) === false);
			assert(BX.type.isArray(types.notEmptyObject) === false);
			assert(BX.type.isArray(types.notPlainObject) === false);
			assert(BX.type.isArray(types.notEmptyString) === false);
			assert(BX.type.isArray(types.emptyString) === false);
			assert(BX.type.isArray() === false);
			assert(BX.type.isArray(types.null) === false);
			assert(BX.type.isArray(types.undefined) === false);
			assert(BX.type.isArray(types.function) === false);
		});

		it('.isArrayLike()', () => {
			assert(BX.type.isArrayLike(types.emptyArray) === true);
			assert(BX.type.isArrayLike(types.notEmptyArray) === true);
			assert(BX.type.isArrayLike(types.notEmptyString) === true);
			assert(BX.type.isArrayLike(types.emptyString) === true);

			assert(BX.type.isArrayLike(types.float) === false);
			assert(BX.type.isArrayLike(types.integer) === false);
			assert(BX.type.isArrayLike(types.true) === false);
			assert(BX.type.isArrayLike(types.false) === false);
			assert(BX.type.isArrayLike(types.emptyObject) === false);
			assert(BX.type.isArrayLike(types.notEmptyObject) === false);
			assert(BX.type.isArrayLike(types.notPlainObject) === false);
			assert(BX.type.isArrayLike() === false);
			assert(BX.type.isArrayLike(types.null) === false);
			assert(BX.type.isArrayLike(types.undefined) === false);
			assert(BX.type.isArrayLike(types.function) === false);
		});

		it('.isDate()', () => {
			assert(BX.type.isDate(types.date) === true);

			assert(BX.type.isDate(types.emptyArray) === false);
			assert(BX.type.isDate(types.notEmptyArray) === false);
			assert(BX.type.isDate(types.notEmptyString) === false);
			assert(BX.type.isDate(types.emptyString) === false);
			assert(BX.type.isDate(types.float) === false);
			assert(BX.type.isDate(types.integer) === false);
			assert(BX.type.isDate(types.true) === false);
			assert(BX.type.isDate(types.false) === false);
			assert(BX.type.isDate(types.emptyObject) === false);
			assert(BX.type.isDate(types.notEmptyObject) === false);
			assert(BX.type.isDate(types.notPlainObject) === false);
			assert(BX.type.isDate() === false);
			assert(BX.type.isDate(types.null) === false);
			assert(BX.type.isDate(types.undefined) === false);
			assert(BX.type.isDate(types.function) === false);
		});

		it('.isDomNode()', () => {
			assert(BX.type.isDomNode(types.htmlelement) === true);

			assert(BX.type.isDomNode(types.date) === false);
			assert(BX.type.isDomNode(types.emptyArray) === false);
			assert(BX.type.isDomNode(types.notEmptyArray) === false);
			assert(BX.type.isDomNode(types.notEmptyString) === false);
			assert(BX.type.isDomNode(types.emptyString) === false);
			assert(BX.type.isDomNode(types.float) === false);
			assert(BX.type.isDomNode(types.integer) === false);
			assert(BX.type.isDomNode(types.true) === false);
			assert(BX.type.isDomNode(types.false) === false);
			assert(BX.type.isDomNode(types.emptyObject) === false);
			assert(BX.type.isDomNode(types.notEmptyObject) === false);
			assert(BX.type.isDomNode(types.notPlainObject) === false);
			assert(BX.type.isDomNode() === false);
			assert(BX.type.isDomNode(types.null) === false);
			assert(BX.type.isDomNode(types.undefined) === false);
			assert(BX.type.isDomNode(types.function) === false);
		});

		it('.isElementNode()', () => {
			assert(BX.type.isElementNode(types.htmlelement) === true);

			assert(BX.type.isElementNode(types.date) === false);
			assert(BX.type.isElementNode(types.emptyArray) === false);
			assert(BX.type.isElementNode(types.notEmptyArray) === false);
			assert(BX.type.isElementNode(types.notEmptyString) === false);
			assert(BX.type.isElementNode(types.emptyString) === false);
			assert(BX.type.isElementNode(types.float) === false);
			assert(BX.type.isElementNode(types.integer) === false);
			assert(BX.type.isElementNode(types.true) === false);
			assert(BX.type.isElementNode(types.false) === false);
			assert(BX.type.isElementNode(types.emptyObject) === false);
			assert(BX.type.isElementNode(types.notEmptyObject) === false);
			assert(BX.type.isElementNode(types.notPlainObject) === false);
			assert(BX.type.isElementNode() === false);
			assert(BX.type.isElementNode(types.null) === false);
			assert(BX.type.isElementNode(types.undefined) === false);
			assert(BX.type.isElementNode(types.function) === false);
		});

		it('.isTextNode()', () => {
			assert(BX.type.isTextNode(types.textNode) === true);

			assert(BX.type.isTextNode(types.htmlelement) === false);
			assert(BX.type.isTextNode(types.date) === false);
			assert(BX.type.isTextNode(types.emptyArray) === false);
			assert(BX.type.isTextNode(types.notEmptyArray) === false);
			assert(BX.type.isTextNode(types.notEmptyString) === false);
			assert(BX.type.isTextNode(types.emptyString) === false);
			assert(BX.type.isTextNode(types.float) === false);
			assert(BX.type.isTextNode(types.integer) === false);
			assert(BX.type.isTextNode(types.true) === false);
			assert(BX.type.isTextNode(types.false) === false);
			assert(BX.type.isTextNode(types.emptyObject) === false);
			assert(BX.type.isTextNode(types.notEmptyObject) === false);
			assert(BX.type.isTextNode(types.notPlainObject) === false);
			assert(BX.type.isTextNode() === false);
			assert(BX.type.isTextNode(types.null) === false);
			assert(BX.type.isTextNode(types.undefined) === false);
			assert(BX.type.isTextNode(types.function) === false);
		});

		it('.isMap()', () => {
			assert(BX.type.isMap(types.map) === true);

			assert(BX.type.isMap(types.set) === false);
			assert(BX.type.isMap(types.weakMap) === false);
			assert(BX.type.isMap(types.weakSet) === false);
			assert(BX.type.isMap(types.textNode) === false);
			assert(BX.type.isMap(types.htmlelement) === false);
			assert(BX.type.isMap(types.date) === false);
			assert(BX.type.isMap(types.emptyArray) === false);
			assert(BX.type.isMap(types.notEmptyArray) === false);
			assert(BX.type.isMap(types.notEmptyString) === false);
			assert(BX.type.isMap(types.emptyString) === false);
			assert(BX.type.isMap(types.float) === false);
			assert(BX.type.isMap(types.integer) === false);
			assert(BX.type.isMap(types.true) === false);
			assert(BX.type.isMap(types.false) === false);
			assert(BX.type.isMap(types.emptyObject) === false);
			assert(BX.type.isMap(types.notEmptyObject) === false);
			assert(BX.type.isMap(types.notPlainObject) === false);
			assert(BX.type.isMap() === false);
			assert(BX.type.isMap(types.null) === false);
			assert(BX.type.isMap(types.undefined) === false);
			assert(BX.type.isMap(types.function) === false);
		});

		it('.isSet()', () => {
			assert(BX.type.isSet(types.set) === true);

			assert(BX.type.isSet(types.map) === false);
			assert(BX.type.isSet(types.weakMap) === false);
			assert(BX.type.isSet(types.weakSet) === false);
			assert(BX.type.isSet(types.textNode) === false);
			assert(BX.type.isSet(types.htmlelement) === false);
			assert(BX.type.isSet(types.date) === false);
			assert(BX.type.isSet(types.emptyArray) === false);
			assert(BX.type.isSet(types.notEmptyArray) === false);
			assert(BX.type.isSet(types.notEmptyString) === false);
			assert(BX.type.isSet(types.emptyString) === false);
			assert(BX.type.isSet(types.float) === false);
			assert(BX.type.isSet(types.integer) === false);
			assert(BX.type.isSet(types.true) === false);
			assert(BX.type.isSet(types.false) === false);
			assert(BX.type.isSet(types.emptyObject) === false);
			assert(BX.type.isSet(types.notEmptyObject) === false);
			assert(BX.type.isSet(types.notPlainObject) === false);
			assert(BX.type.isSet() === false);
			assert(BX.type.isSet(types.null) === false);
			assert(BX.type.isSet(types.undefined) === false);
			assert(BX.type.isSet(types.function) === false);
		});

		it('.isWeakMap()', () => {
			assert(BX.type.isWeakMap(types.weakMap) === true);

			assert(BX.type.isWeakMap(types.set) === false);
			assert(BX.type.isWeakMap(types.map) === false);
			assert(BX.type.isWeakMap(types.weakSet) === false);
			assert(BX.type.isWeakMap(types.textNode) === false);
			assert(BX.type.isWeakMap(types.htmlelement) === false);
			assert(BX.type.isWeakMap(types.date) === false);
			assert(BX.type.isWeakMap(types.emptyArray) === false);
			assert(BX.type.isWeakMap(types.notEmptyArray) === false);
			assert(BX.type.isWeakMap(types.notEmptyString) === false);
			assert(BX.type.isWeakMap(types.emptyString) === false);
			assert(BX.type.isWeakMap(types.float) === false);
			assert(BX.type.isWeakMap(types.integer) === false);
			assert(BX.type.isWeakMap(types.true) === false);
			assert(BX.type.isWeakMap(types.false) === false);
			assert(BX.type.isWeakMap(types.emptyObject) === false);
			assert(BX.type.isWeakMap(types.notEmptyObject) === false);
			assert(BX.type.isWeakMap(types.notPlainObject) === false);
			assert(BX.type.isWeakMap() === false);
			assert(BX.type.isWeakMap(types.null) === false);
			assert(BX.type.isWeakMap(types.undefined) === false);
			assert(BX.type.isWeakMap(types.function) === false);
		});

		it('.isWeakSet()', () => {
			assert(BX.type.isWeakSet(types.weakSet) === true);

			assert(BX.type.isWeakSet(types.weakMap) === false);
			assert(BX.type.isWeakSet(types.set) === false);
			assert(BX.type.isWeakSet(types.map) === false);
			assert(BX.type.isWeakSet(types.textNode) === false);
			assert(BX.type.isWeakSet(types.htmlelement) === false);
			assert(BX.type.isWeakSet(types.date) === false);
			assert(BX.type.isWeakSet(types.emptyArray) === false);
			assert(BX.type.isWeakSet(types.notEmptyArray) === false);
			assert(BX.type.isWeakSet(types.notEmptyString) === false);
			assert(BX.type.isWeakSet(types.emptyString) === false);
			assert(BX.type.isWeakSet(types.float) === false);
			assert(BX.type.isWeakSet(types.integer) === false);
			assert(BX.type.isWeakSet(types.true) === false);
			assert(BX.type.isWeakSet(types.false) === false);
			assert(BX.type.isWeakSet(types.emptyObject) === false);
			assert(BX.type.isWeakSet(types.notEmptyObject) === false);
			assert(BX.type.isWeakSet(types.notPlainObject) === false);
			assert(BX.type.isWeakSet() === false);
			assert(BX.type.isWeakSet(types.null) === false);
			assert(BX.type.isWeakSet(types.undefined) === false);
			assert(BX.type.isWeakSet(types.function) === false);
		});

		it('.isPrototype()', () => {
			assert(BX.type.isPrototype(types.prototype) === true);

			assert(BX.type.isPrototype(types.weakSet) === false);
			assert(BX.type.isPrototype(types.weakMap) === false);
			assert(BX.type.isPrototype(types.set) === false);
			assert(BX.type.isPrototype(types.map) === false);
			assert(BX.type.isPrototype(types.textNode) === false);
			assert(BX.type.isPrototype(types.htmlelement) === false);
			assert(BX.type.isPrototype(types.date) === false);
			assert(BX.type.isPrototype(types.emptyArray) === false);
			assert(BX.type.isPrototype(types.notEmptyArray) === false);
			assert(BX.type.isPrototype(types.notEmptyString) === false);
			assert(BX.type.isPrototype(types.emptyString) === false);
			assert(BX.type.isPrototype(types.float) === false);
			assert(BX.type.isPrototype(types.integer) === false);
			assert(BX.type.isPrototype(types.true) === false);
			assert(BX.type.isPrototype(types.false) === false);
			assert(BX.type.isPrototype(types.emptyObject) === false);
			assert(BX.type.isPrototype(types.notEmptyObject) === false);
			assert(BX.type.isPrototype(types.notPlainObject) === false);
			assert(BX.type.isPrototype() === false);
			assert(BX.type.isPrototype(types.null) === false);
			assert(BX.type.isPrototype(types.undefined) === false);
			assert(BX.type.isPrototype(types.function) === false);
		});

		it('.isNil()', () => {
			assert(BX.type.isNil(types.null) === true);
			assert(BX.type.isNil(types.undefined) === true);
			assert(BX.type.isNil() === true);

			assert(BX.type.isNil(types.prototype) === false);
			assert(BX.type.isNil(types.weakSet) === false);
			assert(BX.type.isNil(types.weakMap) === false);
			assert(BX.type.isNil(types.set) === false);
			assert(BX.type.isNil(types.map) === false);
			assert(BX.type.isNil(types.textNode) === false);
			assert(BX.type.isNil(types.htmlelement) === false);
			assert(BX.type.isNil(types.date) === false);
			assert(BX.type.isNil(types.emptyArray) === false);
			assert(BX.type.isNil(types.notEmptyArray) === false);
			assert(BX.type.isNil(types.notEmptyString) === false);
			assert(BX.type.isNil(types.emptyString) === false);
			assert(BX.type.isNil(types.float) === false);
			assert(BX.type.isNil(types.integer) === false);
			assert(BX.type.isNil(types.true) === false);
			assert(BX.type.isNil(types.false) === false);
			assert(BX.type.isNil(types.emptyObject) === false);
			assert(BX.type.isNil(types.notEmptyObject) === false);
			assert(BX.type.isNil(types.notPlainObject) === false);
			assert(BX.type.isNil(types.function) === false);
		});

		it('.isRegExp()', () => {
			assert(BX.type.isRegExp(types.regexp) === true);
			assert(BX.type.isRegExp(types.regexpShortSyntax) === true);

			assert(BX.type.isRegExp(types.null) === false);
			assert(BX.type.isRegExp(types.undefined) === false);
			assert(BX.type.isRegExp() === false);
			assert(BX.type.isRegExp(types.prototype) === false);
			assert(BX.type.isRegExp(types.weakSet) === false);
			assert(BX.type.isRegExp(types.weakMap) === false);
			assert(BX.type.isRegExp(types.set) === false);
			assert(BX.type.isRegExp(types.map) === false);
			assert(BX.type.isRegExp(types.textNode) === false);
			assert(BX.type.isRegExp(types.htmlelement) === false);
			assert(BX.type.isRegExp(types.date) === false);
			assert(BX.type.isRegExp(types.emptyArray) === false);
			assert(BX.type.isRegExp(types.notEmptyArray) === false);
			assert(BX.type.isRegExp(types.notEmptyString) === false);
			assert(BX.type.isRegExp(types.emptyString) === false);
			assert(BX.type.isRegExp(types.float) === false);
			assert(BX.type.isRegExp(types.integer) === false);
			assert(BX.type.isRegExp(types.true) === false);
			assert(BX.type.isRegExp(types.false) === false);
			assert(BX.type.isRegExp(types.emptyObject) === false);
			assert(BX.type.isRegExp(types.notEmptyObject) === false);
			assert(BX.type.isRegExp(types.notPlainObject) === false);
			assert(BX.type.isRegExp(types.function) === false);
		});

		it('.isNull()', () => {
			assert(BX.type.isNull(types.null) === true);

			assert(BX.type.isNull(types.regexp) === false);
			assert(BX.type.isNull(types.regexpShortSyntax) === false);
			assert(BX.type.isNull(types.undefined) === false);
			assert(BX.type.isNull() === false);
			assert(BX.type.isNull(types.prototype) === false);
			assert(BX.type.isNull(types.weakSet) === false);
			assert(BX.type.isNull(types.weakMap) === false);
			assert(BX.type.isNull(types.set) === false);
			assert(BX.type.isNull(types.map) === false);
			assert(BX.type.isNull(types.textNode) === false);
			assert(BX.type.isNull(types.htmlelement) === false);
			assert(BX.type.isNull(types.date) === false);
			assert(BX.type.isNull(types.emptyArray) === false);
			assert(BX.type.isNull(types.notEmptyArray) === false);
			assert(BX.type.isNull(types.notEmptyString) === false);
			assert(BX.type.isNull(types.emptyString) === false);
			assert(BX.type.isNull(types.float) === false);
			assert(BX.type.isNull(types.integer) === false);
			assert(BX.type.isNull(types.true) === false);
			assert(BX.type.isNull(types.false) === false);
			assert(BX.type.isNull(types.emptyObject) === false);
			assert(BX.type.isNull(types.notEmptyObject) === false);
			assert(BX.type.isNull(types.notPlainObject) === false);
			assert(BX.type.isNull(types.function) === false);
		});

		it('.isUndefined()', () => {
			assert(BX.type.isUndefined(types.undefined) === true);
			assert(BX.type.isUndefined() === true);

			assert(BX.type.isUndefined(types.null) === false);
			assert(BX.type.isUndefined(types.regexp) === false);
			assert(BX.type.isUndefined(types.regexpShortSyntax) === false);
			assert(BX.type.isUndefined(types.prototype) === false);
			assert(BX.type.isUndefined(types.weakSet) === false);
			assert(BX.type.isUndefined(types.weakMap) === false);
			assert(BX.type.isUndefined(types.set) === false);
			assert(BX.type.isUndefined(types.map) === false);
			assert(BX.type.isUndefined(types.textNode) === false);
			assert(BX.type.isUndefined(types.htmlelement) === false);
			assert(BX.type.isUndefined(types.date) === false);
			assert(BX.type.isUndefined(types.emptyArray) === false);
			assert(BX.type.isUndefined(types.notEmptyArray) === false);
			assert(BX.type.isUndefined(types.notEmptyString) === false);
			assert(BX.type.isUndefined(types.emptyString) === false);
			assert(BX.type.isUndefined(types.float) === false);
			assert(BX.type.isUndefined(types.integer) === false);
			assert(BX.type.isUndefined(types.true) === false);
			assert(BX.type.isUndefined(types.false) === false);
			assert(BX.type.isUndefined(types.emptyObject) === false);
			assert(BX.type.isUndefined(types.notEmptyObject) === false);
			assert(BX.type.isUndefined(types.notPlainObject) === false);
			assert(BX.type.isUndefined(types.function) === false);
		});
	});

	describe('BX.ready', () => {
		it('Should execute callback if readyState is interactive or complete', () => {
			Object.defineProperty(document, "readyState", {
				get: () => 'interactive',
				configurable: true
			});
			assert(document.readyState === 'interactive');
			BX.ready(() => assert(true));

			Object.defineProperty(document, "readyState", {
				get: () => 'complete',
				configurable: true
			});
			assert(document.readyState === 'complete');
			BX.ready(() => assert(true));
		});

		it('Should not execute callback if readyState is loading', () => {
			Object.defineProperty(document, "readyState", {
				get: () => 'loading',
				configurable: true
			});
			assert(document.readyState === 'loading');
			BX.ready(() => assert(false));
		});
	});

	describe('BX.namespace', () => {
		it('Should be return Object', () => {
			let res = BX.namespace('BX.TestTest');
			assert(BX.type.isPlainObject(res));
		});

		it('Should be return Function', () => {
			BX.TestTest = types.function;
			let res = BX.namespace('BX.TestTest');
			assert(BX.type.isFunction(res));
			assert(res === types.function);
		});

		it('Should be return BX', () => {
			assert(BX.namespace('BX') === BX);
		});
	});

	describe('BX.message', () => {
		it('Should return message', () => {
			let message = {key: 'TEST_MESSAGE', value: 'Test message'};
			BX.message[message.key] = message.value;
			assert(BX.message(message.key) === message.value);
		});

		it('Should set messages from object', () => {
			let messages = {
				'TEST_MESSAGE_1': 'Test message 1',
				'TEST_MESSAGE_2': 'Test message 2',
				'TEST_MESSAGE_3': 'Test message 3'
			};

			BX.message(messages);

			Object.keys(messages)
				.forEach(key => assert(BX.message(key) === messages[key]));
		});

		it('Should return a message that has been set as static property', () => {
			let message = {key: 'TEST_MESSAGE_', value: 'Test message _'};
			BX.message[message.key] = message.value;
			assert(BX.message(message.key) === message.value);
		});
	});

	describe('BX.getClass', () => {
		it('Should return a function if exists', () => {
			BX.namespace('BX.Module.Test');

			let component = function TestTestComponent() {};
			BX.Module.Test.Component = component;

			assert(BX.getClass('BX.Module.Test.Component') === component);
		});

		it('Should return null if class not exists', () => {
			assert(BX.type.isNull(BX.getClass('BX.Testtttt')));
		});
	});

	describe('BX.hasClass', () => {
		it('Should return true if element contains class', () => {
			let element = document.createElement('div');
			element.classList.add(types.cssClassName);
			assert(BX.hasClass(element, types.cssClassName) === true);
		});

		it('Should return false if element not contains class', () => {
			let element = document.createElement('div');
			assert(BX.hasClass(element, types.cssClassName) === false);
		});

		it('Should return true if element contains all classes as array', () => {
			let element = document.createElement('div');
			types.cssClassNames.forEach(className => element.classList.add(className));
			assert(BX.hasClass(element, types.cssClassNames) === true);
		});

		it('Should return false if element contains not all classes from array', () => {
			let element = document.createElement('div');
			types.cssClassNames.forEach((className, index) => {
				if (index) {
					element.classList.add(className);
				}
			});
			assert(BX.hasClass(element, types.cssClassNames) === false);
		});

		it('Should return true if element contains all classes from string', () => {
			let element = document.createElement('div');
			types.cssClassNames.forEach(className => element.classList.add(className));
			assert(BX.hasClass(element, types.cssClassNames.join(' ')) === true);
		});

		it('Should return false if element contains not all classes from string', () => {
			let element = document.createElement('div');
			types.cssClassNames.forEach((className, index) => {
				if (index) {
					element.classList.add(className);
				}
			});
			assert(BX.hasClass(element, types.cssClassNames.join(' ')) === false);
		});

		it('Should return false if element not element', () => {
			assert(BX.hasClass(types.emptyString, 'test') === false);
			assert(BX.hasClass(types.notEmptyString, 'test') === false);
			assert(BX.hasClass(types.emptyObject, 'test') === false);
			assert(BX.hasClass(types.notEmptyObject, 'test') === false);
			assert(BX.hasClass(types.notPlainObject, 'test') === false);
			assert(BX.hasClass(types.emptyArray, 'test') === false);
			assert(BX.hasClass(types.notEmptyArray, 'test') === false);
			assert(BX.hasClass(types.integer, 'test') === false);
			assert(BX.hasClass(types.float, 'test') === false);
			assert(BX.hasClass(types.regexp, 'test') === false);
			assert(BX.hasClass(types.regexpShortSyntax, 'test') === false);
		});

		it('Should return false if class not class', () => {
			let element = document.createElement('div');
			assert(BX.hasClass(element, types.emptyString) === false);
			assert(BX.hasClass(element, types.notEmptyString) === false);
			assert(BX.hasClass(element, types.emptyObject) === false);
			assert(BX.hasClass(element, types.notEmptyObject) === false);
			assert(BX.hasClass(element, types.notPlainObject) === false);
			assert(BX.hasClass(element, types.emptyArray) === false);
			assert(BX.hasClass(element, types.notEmptyArray) === false);
			assert(BX.hasClass(element, types.integer) === false);
			assert(BX.hasClass(element, types.float) === false);
			assert(BX.hasClass(element, types.regexp) === false);
			assert(BX.hasClass(element, types.regexpShortSyntax) === false);
			assert(BX.hasClass(element, types.function) === false);
		});
	});

	describe('BX.addClass', () => {
		it('Should set single class', () => {
			let element = document.createElement('div');
			BX.addClass(element, types.cssClassName);
			assert(element.classList.contains(types.cssClassName));
		});

		it('Should set classes from array', () => {
			let element = document.createElement('div');
			BX.addClass(element, types.cssClassNames);
			types.cssClassNames.forEach(name => {
				assert(element.classList.contains(name))
			});
		});

		it('Should set all classes from string', () => {
			let element = document.createElement('div');
			BX.addClass(element, types.cssClassNames.join(' '));
			types.cssClassNames.forEach(name => {
				assert(element.classList.contains(name))
			});
		});
	});

	describe('BX.removeClass', () => {
		it('Should remove single class', () => {
			let element = document.createElement('div');
			element.classList.add(types.cssClassName);
			BX.removeClass(element, types.cssClassName);
			assert(element.classList.contains(types.cssClassName) === false);
		});

		it('Should remove all classes from array', () => {
			let element = document.createElement('div');
			types.cssClassNames.forEach(className => {
				element.classList.add(className);
			});
			BX.removeClass(element, types.cssClassNames);
			let res = types.cssClassNames.every(name => !element.classList.contains(name));
			assert(res === true);
		});

		it('Should remove all classes from string', () => {
			let element = document.createElement('div');
			types.cssClassNames.forEach(className => {
				element.classList.add(className);
			});
			BX.removeClass(element, types.cssClassNames.join(' '));
			let res = types.cssClassNames.every(name => !element.classList.contains(name));
			assert(res === true);
		});
	});

	describe('BX.toggleClass', () => {
		it('Should toggle single class', () => {
			let element = document.createElement('div');
			BX.toggleClass(element, types.cssClassName);
			assert(element.classList.contains(types.cssClassName) === true);

			BX.toggleClass(element, types.cssClassName);
			assert(element.classList.contains(types.cssClassName) === false);

			BX.toggleClass(element, types.cssClassName);
			assert(element.classList.contains(types.cssClassName) === true);
		});

		it('Should toggle all classes from array', () => {
			let element = document.createElement('div');

			BX.toggleClass(element, types.cssClassNames);
			assert(types.cssClassNames.every(className => {
				return element.classList.contains(className);
			}) === true);

			BX.toggleClass(element, types.cssClassNames);
			assert(types.cssClassNames.every(className => {
				return !element.classList.contains(className);
			}) === true);

			BX.toggleClass(element, types.cssClassNames);
			assert(types.cssClassNames.every(className => {
				return element.classList.contains(className);
			}) === true);
		});

		it('Should toggle all classes from string', () => {
			let element = document.createElement('div');

			BX.toggleClass(element, types.cssClassNames.join(' '));
			assert(types.cssClassNames.every(className => {
				return element.classList.contains(className);
			}) === true);

			BX.toggleClass(element, types.cssClassNames.join(' '));
			assert(types.cssClassNames.every(className => {
				return !element.classList.contains(className);
			}) === true);

			BX.toggleClass(element, types.cssClassNames.join(' '));
			assert(types.cssClassNames.every(className => {
				return element.classList.contains(className);
			}) === true);
		});
	});

	describe('BX.show', () => {
		it('Should remove display: none', () => {
			let element = document.createElement('div');
			element.style.setProperty('display', 'none');
			BX.show(element);

			assert(element.style.getPropertyValue('display') !== 'none');
		});

		it('Should set hidden = false', () => {
			let element = document.createElement('div');
			element.hidden = true;
			BX.show(element);

			assert(element.hidden === false);
		});
	});

	describe('BX.hide', () => {
		it('Should add display: none; style ', () => {
			let element = document.createElement('div');
			BX.hide(element);

			assert(element.style.getPropertyValue('display') === 'none');
		});

		it('Should set hidden = true', () => {
			let element = document.createElement('div');
			BX.hide(element);

			assert(element.hidden === true);
		});
	});

	describe('BX.isShown', () => {
		it('Should detect element visibility', () => {
			let element = document.createElement('div');

			element.hidden = false;
			assert(BX.isShown(element) === true);

			element.hidden = true;
			assert(BX.isShown(element) === false);

			element.style.setProperty('display', 'none');
			element.hidden = false;
			assert(BX.isShown(element) === false);

			element.style.removeProperty('display');
			element.hidden = true;
			assert(BX.isShown(element) === false);
		});
	});

	describe('BX.toggle', () => {
		it('Should toggle element visibility', () => {
			let element = document.createElement('div');

			BX.toggle(element);
			assert(element.style.getPropertyValue('display') === 'none');
			assert(element.hidden === true);

			BX.toggle(element);
			assert(element.style.getPropertyValue('display') !== 'none');
			assert(element.hidden === false);

			BX.toggle(element);
			assert(element.style.getPropertyValue('display') === 'none');
			assert(element.hidden === true);
		});
	});

	describe('BX.clone', () => {
		it('Should clone object', () => {
			let test1 = {0: 'value1', 1: 'value2'};
			let test2 = BX.clone(test1);

			assert(BX.type.isPlainObject(test2) === true);
			assert(test1 !== test2);
			assert(test1[0] === test2[0] && test1[1] === test2[1]);
		});

		it('Should deep clone object', () => {
			let test1 = {0: {test: 1}, 1: {test: 2}};
			let test2 = BX.clone(test1);

			assert(BX.type.isPlainObject(test2) === true);
			assert(test1 !== test2);

			assert(BX.type.isPlainObject(test2[0]));
			assert(BX.type.isPlainObject(test2[1]));

			assert(test1[0] !== test2[0]);
			assert(test1[1] !== test2[1]);
		});

		it('Should return not cloneable value without changes', () => {
			assert(BX.clone(types.cssClassName) === types.cssClassName);
			assert(BX.clone(types.float) === types.float);
			assert(BX.clone(types.integer) === types.integer);
			assert(BX.clone(types.undefined) === types.undefined);
			assert(BX.clone(types.null) === types.null);
			assert(BX.clone(types.false) === types.false);
			assert(BX.clone(types.true) === types.true);
			assert(BX.clone(types.weakMap) === types.weakMap);
			assert(BX.clone(types.weakSet) === types.weakSet);
		});

		it('Should be clone an Object with circular references', () => {
			let data = {
				a: {},
				b: {c: '', d: ''}
			};

			data.b.c = data.a;
			data.b.d = data;

			let clone = BX.clone(data);

			assert(clone !== data);
			assert(clone.b.c === clone.a);
			assert(clone.b.d === clone);
		});

		it('Should be clone an Array with circular reference', () => {
			let data = [];
			data[0] = data;

			let clone = BX.clone(data);

			assert(clone !== data);
			assert(clone[0] === clone);
		});

		it('Should be clone an Map with circular reference', () => {
			let data = new Map();
			data.set(data, data);

			let clone = BX.clone(data);

			assert(clone !== data);
			assert(clone.get(clone) === clone);
		});

		it('Should be clone an Set with circular reference', () => {
			let data = new Set();
			data.add(data);

			let clone = BX.clone(data);

			assert(clone !== data);
			assert(clone.has(clone) === true);
		});

		it('Should be clone Element node', () => {
			let element = document.createElement('div');
			let clone = BX.clone(element);

			assert(BX.type.isDomNode(clone));
			assert(clone !== element);
		});

		it('Should be clone Text node', () => {
			let element = document.createTextNode('test');
			let clone = BX.clone(element);

			assert(BX.type.isDomNode(clone));
			assert(element !== clone);
		});
	});

	describe('BX.cleanNode', () => {
		it('Should remove all child', () => {
			let element = document.createElement('div');
			let childElement = document.createElement('div');

			element.appendChild(childElement);

			assert(element.innerHTML !== '');

			BX.cleanNode(element);

			assert(element.innerHTML === '');
		});

		it('Should return element', () => {
			let element = document.createElement('div');

			assert(BX.cleanNode(element) === element);
		});
	});
});
