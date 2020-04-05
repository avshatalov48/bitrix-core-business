import {Text} from '../../src/core';

describe('core/text', () => {
	describe('encode', () => {
		it('Should be exported as function', () => {
			assert(typeof Text.encode === 'function');
		});

		it('Should encode passed string with html', () => {
			let source = `Yo <div class="name">World</div>`;
			let result = 'Yo &lt;div class=&quot;name&quot;&gt;World&lt;/div&gt;';

			assert(Text.encode(source) === result);
		});

		it('Should return passed value if passed not string', () => {
			assert(Text.encode(null) === null);
			assert(Text.encode(true) === true);

			let arr = [];
			assert(Text.encode(arr) === arr);

			let obj = {};
			assert(Text.encode(obj) === obj);
		});
	});

	describe('decode', () => {
		it('Should be exported as function', () => {
			assert(typeof Text.decode === 'function');
		});

		it('Should Text.decode passed string with encoded html', () => {
			let source = 'Yo &lt;div class=&quot;name&quot;&gt;World&lt;/div&gt;';
			let result = `Yo <div class="name">World</div>`;

			assert(Text.decode(source) === result);
		});

		it('Should return passed value if passed not string', () => {
			assert(Text.decode(null) === null);
			assert(Text.decode(true) === true);

			let arr = [];
			assert(Text.decode(arr) === arr);

			let obj = {};
			assert(Text.decode(obj) === obj);
		});
	});

	// https://www.ecma-international.org/ecma-262/5.1/#sec-15.1.2.3
	describe('toNumber', () => {
		it('Should be a function', () => {
			assert.ok(typeof Text.toNumber === 'function');
		});

		it('Should return 1 for 1 (number)', () => {
			assert.ok(Text.toNumber(1) === 1);
		});

		it('Should return 0 for 0 (number)', () => {
			assert.ok(Text.toNumber(0) === 0);
		});

		it('Should return 1.1 for 1.1 (number)', () => {
			assert.ok(Text.toNumber(1.1) === 1.1);
		});

		it('Should return 1.00001 for 1.00001 (number)', () => {
			assert.ok(Text.toNumber(1.00001) === 1.00001);
		});

		it('Should return 1 for "1" (string)', () => {
			assert.ok(Text.toNumber("1") === 1);
		});

		it('Should return 0 for "0" (string)', () => {
			assert.ok(Text.toNumber("0") === 0);
		});

		it('Should return 1.1 for "1.1" (string)', () => {
			assert.ok(Text.toNumber("1.1") === 1.1);
		});

		it('Should return 1.00001 for "1.00001" (string)', () => {
			assert.ok(Text.toNumber("1.00001") === 1.00001);
		});

		it('Should return 0 for true (boolean)', () => {
			assert.ok(Text.toNumber(true) === 0);
		});

		it('Should return 0 for false (boolean)', () => {
			assert.ok(Text.toNumber(false) === 0);
		});

		it('Should return 0 for {} (object)', () => {
			assert.ok(Text.toNumber({}) === 0);
		});

		it('Should return 0 for [] (object)', () => {
			assert.ok(Text.toNumber({}) === 0);
		});

		it('Should return 10 for "10px" (string)', () => {
			assert.ok(Text.toNumber('10test') === 10);
		});

		it('Should return 0 for "px10" (string)', () => {
			assert.ok(Text.toNumber('px10') === 0);
		});

		it('Should return 0 for NaN (number)', () => {
			assert.ok(Text.toNumber(NaN) === 0);
		});
	});

	// https://www.ecma-international.org/ecma-262/5.1/#sec-15.1.2.2
	describe('toInteger', () => {
		it('Should be a function', () => {
			assert.ok(typeof Text.toInteger === 'function');
		});

		it('Should return 1 for 1 (number)', () => {
			assert.ok(Text.toInteger(1) === 1);
		});

		it('Should return 0 for 0 (number)', () => {
			assert.ok(Text.toInteger(0) === 0);
		});

		it('Should return 1 for 1.1 (number)', () => {
			assert.ok(Text.toInteger(1.1) === 1);
		});

		it('Should return 1 for 1.4 (number)', () => {
			assert.ok(Text.toInteger(1.4) === 1);
		});

		it('Should return 1 for 1.9 (number)', () => {
			assert.ok(Text.toInteger(1.9) === 1);
		});

		it('Should return 0 for 0.9 (number)', () => {
			assert.ok(Text.toInteger(0.9) === 0);
		});

		it('Should return 1 for "1" (string)', () => {
			assert.ok(Text.toInteger('1') === 1);
		});

		it('Should return 0 for "0" (string)', () => {
			assert.ok(Text.toInteger('0') === 0);
		});

		it('Should return 1.1 for "1.1" (string)', () => {
			assert.ok(Text.toInteger('1.1') === 1);
		});

		it('Should return 1 for "1.4" (string)', () => {
			assert.ok(Text.toInteger('1.4') === 1);
		});

		it('Should return 1 for "1.9" (string)', () => {
			assert.ok(Text.toInteger('1.9') === 1);
		});

		it('Should return 0 for {} (object)', () => {
			assert.ok(Text.toInteger({}) === 0);
		});

		it('Should return 0 for [] (object)', () => {
			assert.ok(Text.toInteger({}) === 0);
		});

		it('Should return 0 for "" (string)', () => {
			assert.ok(Text.toInteger('') === 0);
		});

		it('Should return 2 for "2.5%" (string)', () => {
			assert.ok(Text.toInteger('') === 0);
		});

		it('Should return 0 for NaN (number)', () => {
			assert.ok(Text.toInteger(NaN) === 0);
		});
	});

	describe('toBoolean', () => {
		it('Should be a function', () => {
			assert.ok(typeof Text.toBoolean === 'function');
		});

		it('Should return true for true (boolean)', () => {
			assert.ok(Text.toBoolean(true) === true);
		});

		it('Should return false for false (boolean)', () => {
			assert.ok(Text.toBoolean(false) === false);
		});

		it('Should return true for 1 (number)', () => {
			assert.ok(Text.toBoolean(1) === true);
		});

		it('Should return false for 0 (number)', () => {
			assert.ok(Text.toBoolean(0) === false);
		});

		it('Should return true for "Y" (string)', () => {
			assert.ok(Text.toBoolean("Y") === true);
		});

		it('Should return true for "y" (string)', () => {
			assert.ok(Text.toBoolean("y") === true);
		});

		it('Should return false for "N" (string)', () => {
			assert.ok(Text.toBoolean("N") === false);
		});

		it('Should return false for "n" (string)', () => {
			assert.ok(Text.toBoolean("n") === false);
		});

		it('Should return true for "1" (string)', () => {
			assert.ok(Text.toBoolean("1") === true);
		});

		it('Should return false for "0" (string)', () => {
			assert.ok(Text.toBoolean("0") === false);
		});

		it('Should return true for custom true-value', () => {
			assert.ok(Text.toBoolean('on', ['on']) === true);
		});

		it('Should return false for custom true-value', () => {
			assert.ok(Text.toBoolean('no', ['on']) === false);
		});
	});

	describe('toCamelCase', () => {

		it('Should be a function', () => {
			assert.ok(typeof Text.toCamelCase === 'function');
		});

		it('Should convert standard string identifiers', () => {
			assert.equal(Text.toCamelCase('one-two-three'), 'oneTwoThree');
			assert.equal(Text.toCamelCase('-one-two-three'), 'oneTwoThree');
			assert.equal(Text.toCamelCase('one--two--three'), 'oneTwoThree');
			assert.equal(Text.toCamelCase('one_two_three'), 'oneTwoThree');
			assert.equal(Text.toCamelCase('one-two'), 'oneTwo');
			assert.equal(Text.toCamelCase('one_two'), 'oneTwo');
			assert.equal(Text.toCamelCase('one'), 'one');
			assert.equal(Text.toCamelCase('___one___'), 'one');
			assert.equal(Text.toCamelCase('oneTwo'), 'oneTwo');
			assert.equal(Text.toCamelCase('ABC'), 'abc');
			assert.equal(Text.toCamelCase('Ab'), 'ab');
			assert.equal(Text.toCamelCase('aB'), 'aB');
			assert.equal(Text.toCamelCase('ab'), 'ab');
			assert.equal(Text.toCamelCase('A_B'), 'aB');
			assert.equal(Text.toCamelCase('---A_B---'), 'aB');
			assert.equal(Text.toCamelCase('A_b'), 'aB');
			assert.equal(Text.toCamelCase('A___b'), 'aB');
			assert.equal(Text.toCamelCase('____A___b___'), 'aB');
			assert.equal(Text.toCamelCase('a_B'), 'aB');
			assert.equal(Text.toCamelCase('a_b'), 'aB');
			assert.equal(Text.toCamelCase('aBc'), 'aBc');
			assert.equal(Text.toCamelCase('aBc def'), 'abcDef');
			assert.equal(Text.toCamelCase('aBc def_ghi'), 'abcDefGhi');
			assert.equal(Text.toCamelCase('aBc def_ghi 123'), 'abcDefGhi123');
		});

		it('Should return the same value for a wrong argument', () => {
			const obj = {};
			assert.equal(Text.toCamelCase(''), '');
			assert.equal(Text.toCamelCase(null), null);
			assert.equal(Text.toCamelCase(undefined), undefined);
			assert.equal(Text.toCamelCase(obj), obj);
		});

	});

	describe('toPascalCase', () => {

		it('Should be a function', () => {
			assert.ok(typeof Text.toPascalCase === 'function');
		});

		it('Should convert standard string identifiers', () => {
			assert.equal(Text.toPascalCase('one-two-three'), 'OneTwoThree');
			assert.equal(Text.toPascalCase('-one-two-three'), 'OneTwoThree');
			assert.equal(Text.toPascalCase('one--two--three'), 'OneTwoThree');
			assert.equal(Text.toPascalCase('one_two_three'), 'OneTwoThree');
			assert.equal(Text.toPascalCase('one-two'), 'OneTwo');
			assert.equal(Text.toPascalCase('one_two'), 'OneTwo');
			assert.equal(Text.toPascalCase('one'), 'One');
			assert.equal(Text.toPascalCase('___one___'), 'One');
			assert.equal(Text.toPascalCase('oneTwo'), 'OneTwo');
			assert.equal(Text.toPascalCase('ABC'), 'Abc');
			assert.equal(Text.toPascalCase('Ab'), 'Ab');
			assert.equal(Text.toPascalCase('aB'), 'AB');
			assert.equal(Text.toPascalCase('ab'), 'Ab');
			assert.equal(Text.toPascalCase('A_B'), 'AB');
			assert.equal(Text.toPascalCase('---A_B---'), 'AB');
			assert.equal(Text.toPascalCase('A_b'), 'AB');
			assert.equal(Text.toPascalCase('A___b'), 'AB');
			assert.equal(Text.toPascalCase('____A___b___'), 'AB');
			assert.equal(Text.toPascalCase('a_B'), 'AB');
			assert.equal(Text.toPascalCase('a_b'), 'AB');
			assert.equal(Text.toPascalCase('aBc'), 'ABc');
			assert.equal(Text.toPascalCase('aBc def'), 'AbcDef');
			assert.equal(Text.toPascalCase('aBc def_ghi'), 'AbcDefGhi');
			assert.equal(Text.toPascalCase('aBc def_ghi 123'), 'AbcDefGhi123');
		});

		it('Should return the same value for a wrong argument', () => {
			const obj = {};
			assert.equal(Text.toPascalCase(''), '');
			assert.equal(Text.toPascalCase(null), null);
			assert.equal(Text.toPascalCase(undefined), undefined);
			assert.equal(Text.toPascalCase(obj), obj);
		});

	});

	describe('toKebabCase', () => {

		it('Should be a function', () => {
			assert.ok(typeof Text.toKebabCase === 'function');
		});

		it('Should convert standard string identifiers', () => {
			assert.equal(Text.toKebabCase('oneTwoThree'), 'one-two-three');
			assert.equal(Text.toKebabCase('marginTop'), 'margin-top');
			assert.equal(Text.toKebabCase('margin'), 'margin');
			assert.equal(Text.toKebabCase('-margin'), 'margin');
			assert.equal(Text.toKebabCase('---margin___top'), 'margin-top');
			assert.equal(Text.toKebabCase('-webkit-margin-top'), 'webkit-margin-top');
			assert.equal(Text.toKebabCase('ABC'), 'abc');
			assert.equal(Text.toKebabCase('Top'), 'top');
			assert.equal(Text.toKebabCase('ThisIsATest'), 'this-is-a-test');
		});

		it('Should return the same value for a wrong argument', () => {
			const obj = {};
			assert.equal(Text.toKebabCase(''), '');
			assert.equal(Text.toKebabCase(null), null);
			assert.equal(Text.toKebabCase(undefined), undefined);
			assert.equal(Text.toKebabCase(obj), obj);
		});
	});
});