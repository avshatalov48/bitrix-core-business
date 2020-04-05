import Type from '../../src/lib/type';

describe('core/type', () => {

	describe('isStringFilled', () => {
		it("rejects everything except strings", function() {
			assert(Type.isStringFilled(true) === false);
			assert(Type.isStringFilled(false) === false);
			assert(Type.isStringFilled([]) === false);
			assert(Type.isStringFilled([1,2]) === false);
			assert(Type.isStringFilled({}) === false);
			assert(Type.isStringFilled(1) === false);
		});

		it("rejects an empty string", function() {
			assert(Type.isStringFilled("") === false);
		});

		it("accepts a none-empty string", function() {
			assert(Type.isStringFilled("123") === true);
			assert(Type.isStringFilled(" ") === true);
			assert(Type.isStringFilled("0") === true);
		});
	});

	describe('isArrayFilled', () => {
		it("rejects everything except array", function() {
			assert(Type.isArrayFilled(true) === false);
			assert(Type.isArrayFilled(false) === false);
			assert(Type.isArrayFilled({}) === false);
			assert(Type.isArrayFilled(1) === false);
			assert(Type.isArrayFilled("123") === false);
		});

		it("rejects an empty array", function() {
			assert(Type.isArrayFilled([]) === false);
		});

		it("accepts a none-empty array", function() {
			assert(Type.isArrayFilled([1]) === true);
			assert(Type.isArrayFilled([1,2]) === true);
			assert(Type.isArrayFilled([""]) === true);
		});
	});

	describe('isPlainObject', () => {

		it("accepts an empty object", function() {
			assert(Type.isPlainObject({}) === true);
		});

		it("accepts an object with nullable prototype", function() {
			assert(Type.isPlainObject(Object.create(null)) === true);
		});

		it("accepts an regular non-empty object", function() {
			assert(Type.isPlainObject({a: 1, b: 2}) === true);
		});

		it("rejects a function", function() {
			assert(Type.isPlainObject(function() {}) === false);
		});

		it("rejects a boolean value", function() {
			assert(Type.isPlainObject(true) === false);
		});

		it("rejects an undefined value", function() {
			assert(Type.isPlainObject() === false);
			assert(Type.isPlainObject(undefined) === false);
		});

		it("rejects a null value", function() {
			assert(Type.isPlainObject(null) === false);
		});

		it("rejects an instance", function() {
			assert(Type.isPlainObject(new function() { this.a = 1 }) === false);
			assert(Type.isPlainObject(new Type()) === false);
		});
	})
});