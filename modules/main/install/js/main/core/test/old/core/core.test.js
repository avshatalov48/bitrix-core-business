import BX from './internal/bootstrap';
import '../../../core_ajax';

describe('old/core.js', () => {
	describe('BX', () => {
		it('Should be a function', () => {
			assert.ok(typeof BX === 'function');
		});

		it('Should return element if passed element', () => {
			const element = document.createElement('div');
			assert.ok(BX(element) === element);
		});

		it('Should return element if passed id', () => {
			const element = document.createElement('div');
			element.id = 'testElement';
			document.body.appendChild(element);

			assert.ok(BX('testElement') === element);
		});

		it('Should return call passed callback function', () => {
			const callbackStub = sinon.spy();

			BX(callbackStub);

			assert.ok(callbackStub.calledOnce);
		});

		it('Should return null if passed empty string', () => {
			assert.ok(BX('') === null);
		});

		it('Should return null if passed null', () => {
			assert.ok(BX(null) === null);
		});

		it('Should return null if passed number', () => {
			assert.ok(BX(2) === null);
		});

		it('Should return null if passed undefined', () => {
			assert.ok(BX() === null);
		});

		it('Should return null if passed object', () => {
			assert.ok(BX({}) === null);
			assert.ok(BX([]) === null);
		});
	});

	describe('#util', () => {
		describe('#array_values', () => {
			it('Should return not nil values from passed array', () => {
				const numbers = [0, '0', 1, '1'];
				const nils = [null, undefined, ,];
				const stringifiedNils = ['null', 'undefined'];
				const functions = [function() {}, () => {}, class Test1 {}];
				const objects = [{}, [], new (class Test1 {})];

				const source = [
					...numbers,
					...nils,
					...stringifiedNils,
					...functions,
					...objects,
				];

				const actual = [
					...numbers,
					...stringifiedNils,
					...functions,
					...objects,
				];

				const result = BX.util.array_values(source);

				assert.deepEqual(actual, result);
			});

			it('Should return not nil values from passed object', () => {
				const numbers = {prop0: 0, prop0s: '0', prop1: 1, prop1s: '1'};
				const nils = {propNull: null, propUndefined: undefined};
				const stringifiedNils = {propNullString: 'null', propUndefinedString: 'undefined'};
				const functions = {propFunction: function() {}, propArrayFunction: () => {}};
				const objects = {propObject: {}, propArray: [], propTypedObject: (new class Test1 {})};

				const source = {
					...numbers,
					...nils,
					...stringifiedNils,
					...functions,
					...objects,
				};

				const actual = [
					...Object.values(numbers),
					...Object.values(stringifiedNils),
					...Object.values(functions),
					...Object.values(objects),
				];

				const result = BX.util.array_values(source);

				assert.deepEqual(actual, result);
			});

			it('Should return not nil values from passed custom object', () => {
				const numbers = {prop0: 0, prop0s: '0', prop1: 1, prop1s: '1'};
				const nils = {propNull: null, propUndefined: undefined};
				const stringifiedNils = {propNullString: 'null', propUndefinedString: 'undefined'};
				const functions = {propFunction: function() {}, propArrayFunction: () => {}};
				const objects = {propObject: {}, propArray: [], propTypedObject: (new class Test1 {})};

				class SourceClass {
					prop0 = numbers.prop0;
					prop0s = numbers.prop0s;
					prop1 = numbers.prop1;
					prop1s = numbers.prop1s;
					propNull = nils.propNull;
					propUndefined = nils.propUndefined;
					propNullString = stringifiedNils.propNullString;
					propUndefinedString = stringifiedNils.propUndefinedString;
					propFunction = functions.propFunction;
					propArrayFunction = functions.propArrayFunction;
					propObject = objects.propObject;
					propArray = objects.propArray;
					propTypedObject = objects.propTypedObject;
				}

				const actual = [
					...Object.values(numbers),
					...Object.values(stringifiedNils),
					...Object.values(functions),
					...Object.values(objects),
				];

				const result = BX.util.array_values(new SourceClass());

				assert.deepEqual(actual, result);
			});

			it('Should return new array', () => {
				const source = [];
				const result = BX.util.array_values(source);

				assert.ok(Array.isArray(result));
				assert.ok(source !== result);
			});

			it('Should does not throws if passed invalid param', () => {
				assert.doesNotThrow(() => {
					BX.util.array_values();
					BX.util.array_values('');
					BX.util.array_values(123123);
					BX.util.array_values(null);
				});
			});

			describe('Return array always', () => {
				it('Should return array if passed string', () => {
					assert.ok(Array.isArray(BX.util.array_values('')));
				});

				it('Should return array if passed null', () => {
					assert.ok(Array.isArray(BX.util.array_values(null)));
				});

				it('Should return array if passed undefined', () => {
					assert.ok(Array.isArray(BX.util.array_values()));
				});

				it('Should return array if passed number', () => {
					assert.ok(Array.isArray(BX.util.array_values(2)));
				});

				it('Should return array if passed boolean', () => {
					assert.ok(Array.isArray(BX.util.array_values(true)));
				});

				it('Should return array if passed function', () => {
					assert.ok(Array.isArray(BX.util.array_values(() => {})));
				});
			});
		});

		describe('#array_keys', () => {
			it('Should return keys for not nil array values', () => {
				const numbers = [0, '0', 1, '1'];
				const nils = [null, undefined, ,];
				const stringifiedNils = ['null', 'undefined'];
				const functions = [function() {}, () => {}, class Test1 {}];
				const objects = [{}, [], new (class Test1 {})];

				const source = [
					...numbers,
					...nils,
					...stringifiedNils,
					...functions,
					...objects,
				];

				const actual = Object.keys([
					...numbers,
					, , ,
					...stringifiedNils,
					...functions,
					...objects,
				]);

				const result = BX.util.array_keys(source);

				assert.deepEqual(actual, result);
			});

			it('Should return keys for not nil object item values', () => {
				const numbers = {prop0: 0, prop0s: '0', prop1: 1, prop1s: '1'};
				const nils = {propNull: null, propUndefined: undefined};
				const stringifiedNils = {propNullString: 'null', propUndefinedString: 'undefined'};
				const functions = {propFunction: function() {}, propArrayFunction: () => {}};
				const objects = {propObject: {}, propArray: [], propTypedObject: (new class Test1 {})};

				const source = {
					...numbers,
					...nils,
					...stringifiedNils,
					...functions,
					...objects,
				};

				const actual = [
					...Object.keys(numbers),
					...Object.keys(stringifiedNils),
					...Object.keys(functions),
					...Object.keys(objects),
				];

				const result = BX.util.array_keys(source);

				assert.deepEqual(actual, result);
			});

			it('Should return keys for not nil custom object values', () => {
				const numbers = {prop0: 0, prop0s: '0', prop1: 1, prop1s: '1'};
				const nils = {propNull: null, propUndefined: undefined};
				const stringifiedNils = {propNullString: 'null', propUndefinedString: 'undefined'};
				const functions = {propFunction: function() {}, propArrayFunction: () => {}};
				const objects = {propObject: {}, propArray: [], propTypedObject: (new class Test1 {})};

				class SourceClass {
					prop0 = numbers.prop0;
					prop0s = numbers.prop0s;
					prop1 = numbers.prop1;
					prop1s = numbers.prop1s;
					propNull = nils.propNull;
					propUndefined = nils.propUndefined;
					propNullString = stringifiedNils.propNullString;
					propUndefinedString = stringifiedNils.propUndefinedString;
					propFunction = functions.propFunction;
					propArrayFunction = functions.propArrayFunction;
					propObject = objects.propObject;
					propArray = objects.propArray;
					propTypedObject = objects.propTypedObject;
				}

				const actual = [
					...Object.keys(numbers),
					...Object.keys(stringifiedNils),
					...Object.keys(functions),
					...Object.keys(objects),
				];

				const result = BX.util.array_keys(new SourceClass());

				assert.deepEqual(actual, result);
			});

			it('Should does not throws if passed invalid param', () => {
				assert.doesNotThrow(() => {
					BX.util.array_keys();
					BX.util.array_keys('');
					BX.util.array_keys(123123);
					BX.util.array_keys(null);
					BX.util.array_keys(null, null);
				});
			});

			describe('Return array always', () => {
				it('Should return array if passed string', () => {
					assert.ok(Array.isArray(BX.util.array_keys('')));
				});

				it('Should return array if passed null', () => {
					assert.ok(Array.isArray(BX.util.array_keys(null)));
				});

				it('Should return array if passed undefined', () => {
					assert.ok(Array.isArray(BX.util.array_keys()));
				});

				it('Should return array if passed number', () => {
					assert.ok(Array.isArray(BX.util.array_keys(2)));
				});

				it('Should return array if passed boolean', () => {
					assert.ok(Array.isArray(BX.util.array_keys(true)));
				});

				it('Should return array if passed function', () => {
					assert.ok(Array.isArray(BX.util.array_keys(() => {})));
				});
			});
		});

		describe('#array_merge', () => {
			it('Should merge two arrays', () => {
				const arr1 = [1, '1', 0, '0'];
				const arr2 = [null, undefined, true, false];

				const actual = [...arr1, ...arr2];
				const result = BX.util.array_merge(arr1, arr2);

				assert.deepEqual(actual, result);
			});

			it('Should modify first array', () => {
				const arr1 = [1, 2];
				const arr2 = [3, 4];

				const result = BX.util.array_merge(arr1, arr2);

				assert.ok(arr1 === result);
			});

			it('Should not modify second array', () => {
				const arr1 = [1, 2];
				const arr2 = [3, 4];

				BX.util.array_merge(arr1, arr2);

				assert.ok(arr2.length === 2);
				assert.ok(arr2[0] === 3);
				assert.ok(arr2[1] === 4);
			});

			it('Should does not throws if passed invalid param', () => {
				assert.doesNotThrow(() => {
					BX.util.array_merge();
					BX.util.array_merge('', '');
					BX.util.array_merge(123123, 213);
					BX.util.array_merge(null);
					BX.util.array_merge(null, null);
				});
			});

			describe('Return array always', () => {
				it('Should return array if passed strings', () => {
					assert.ok(Array.isArray(BX.util.array_merge('', '')));
				});

				it('Should return array if passed null', () => {
					assert.ok(Array.isArray(BX.util.array_merge(null, null)));
				});

				it('Should return array if passed undefined', () => {
					assert.ok(Array.isArray(BX.util.array_merge()));
				});

				it('Should return array if passed number', () => {
					assert.ok(Array.isArray(BX.util.array_merge(2, 3)));
				});

				it('Should return array if passed boolean', () => {
					assert.ok(Array.isArray(BX.util.array_merge(true, false)));
				});

				it('Should return array if passed function', () => {
					assert.ok(Array.isArray(BX.util.array_merge(() => {}, () => {})));
				});
			});
		});

		describe('#array_unique', () => {
			it('Should return unique array from array with not unique items', () => {
				const source = [1, '1', 0, '0', true, 'true', false, 'false'];
				const actual = [1, 0, 'true', 'false'];
				const result = BX.util.array_unique(source);

				assert.deepEqual(actual, result);
			});

			it('Should modify passed array', () => {
				const source = [1, '1', 0, '0', true, 'true', false, 'false'];
				const result = BX.util.array_unique(source);

				assert.ok(result === source);
			});

			it('Should return object if passed object', () => {
				const source = {test1: 1};
				const actual = {test1: 1};
				const result = BX.util.array_unique(source);

				assert.deepEqual(actual, result);
				assert.ok(result === source);
			});

			it('Should return string if passed string with length 0', () => {
				assert.ok(BX.util.array_unique('') === '');
			});

			it('Should return string if passed string a length 1', () => {
				assert.ok(BX.util.array_unique('a') === 'a');
			});

			it('Should throws if passed string with length 2', () => {
				assert.throws(() => {
					BX.util.array_unique('aa');
				});
			});

			it('Should throws if passed string with length more them 2', () => {
				assert.throws(() => {
					BX.util.array_unique('aaaavvvvbbbbb');
				});
			});

			it('Should return number if passed integer', () => {
				assert.ok(BX.util.array_unique(1) === 1);
				assert.ok(BX.util.array_unique(1222) === 1222);
			});

			it('Should return number if passed float', () => {
				assert.ok(BX.util.array_unique(1.1) === 1.1);
				assert.ok(BX.util.array_unique(90.1222) === 90.1222);
			});

			it('Should works with array like objects', () => {
				const source = {'0': 1, 0: 1, 1: 2, length: 3};
				const actual = {'0': 1, 1: 2, length: 3};
				const result = BX.util.array_unique(source);

				assert.deepEqual(actual, result);
			});

			it('Should return true if passed true', () => {
				assert.ok(BX.util.array_unique(true) === true);
			});

			it('Should return false if passed false', () => {
				assert.ok(BX.util.array_unique(false) === false);
			});
		});

		describe('#in_array', () => {
			it('Should return true if array includes passed item (strict)', () => {
				assert.ok(BX.util.in_array(2, [1, 2, 3]));
			});

			it('Should return true if array includes passed item (not strict)', () => {
				assert.ok(BX.util.in_array('2', [1, 2, 3]));
				assert.ok(BX.util.in_array(2, ['1', '2', '3']));
			});

			it('Should return false if array not includes passed item', () => {
				assert.ok(BX.util.in_array(7, [1, 2, 3]) === false);
			});

			it('Should return works with own properties only', () => {
				assert.ok(BX.util.in_array('length', [1, 2, 3]) === false);
			});

			it('Should return false always if passed object', () => {
				assert.ok(BX.util.in_array(1, {test: 1, test2: 2}) === false);
				assert.ok(BX.util.in_array(2, {test: 1, test2: 2}) === false);
			});

			it('Should return true if array like object includes passed item (strict)', () => {
				assert.ok(BX.util.in_array(2, {0: 1, 1: 2, 2: 3, length: 3}));
			});

			it('Should return true if string includes passed character', () => {
				assert.ok(BX.util.in_array('t', 'test_test'));
				assert.ok(BX.util.in_array('e', 'test_test'));
			});

			it('Should return false if string not includes passed character', () => {
				assert.ok(BX.util.in_array('Y', 'test_test') === false);
				assert.ok(BX.util.in_array('o', 'test_test') === false);
			});

			it('Should return false if passed boolean', () => {
				assert.ok(BX.util.in_array(true, true) === false);
				assert.ok(BX.util.in_array(false, false) === false);
			});

			it('Should return false if passed function', () => {
				assert.ok(BX.util.in_array(true, () => {}) === false);
				assert.ok(BX.util.in_array(false, () => {}) === false);
			});

			it('Should throws if passed undefined', () => {
				assert.throws(() => {
					BX.util.in_array(true, undefined);
				});
			});

			it('Should throws if passed null', () => {
				assert.throws(() => {
					BX.util.in_array(true, null);
				});
			});

			it('Should return false if passed Set', () => {
				assert.ok(BX.util.in_array(true, new Set([true])) === false);
			});

			it('Should return false if passed Map', () => {
				const map = new Map();
				map.set(true, true);
				assert.ok(BX.util.in_array(true, map) === false);
			});
		});

		describe('#array_search', () => {
			it('Should return index if passed item includes in passed array (strict)', () => {
				assert.equal(1, BX.util.array_search(3, [2, 3, 4]));
			});

			it('Should return index if passed item includes in passed array (not strict)', () => {
				assert.equal(1, BX.util.array_search('3', [2, 3, 4]));
				assert.equal(1, BX.util.array_search(3, [2, '3', 4]));
			});

			it('Should return -1 if passed item not includes in passed array', () => {
				assert.equal(-1, BX.util.array_search(5, [2, 3, 4]));
			});

			it('Should return index if passed charset includes in passed string', () => {
				assert.equal(0, BX.util.array_search('t', 'test_test'));
				assert.equal(4, BX.util.array_search('_', 'test_test'));
			});

			it('Should return index if passed item includes in passed array-like object', () => {
				assert.equal(1, BX.util.array_search(5, {0: 1, 1: 5, length: 2}));
			});

			it('Should return -1 if passed object', () => {
				assert.equal(-1, BX.util.array_search(5, {test: 5}));
				assert.equal(-1, BX.util.array_search(5, {0: 5}));
			});

			it('Should return -1 if passed function', () => {
				assert.equal(-1, BX.util.array_search(5, () => {}));
			});

			it('Should return -1 if passed number', () => {
				assert.equal(-1, BX.util.array_search(5, 5));
			});
		});
	});

	describe('BX.userOptions', () => {
		BX.message({
			'bitrix_sessid': 'x',
			'COOKIE_PREFIX': 'x',
		});

		const queryStringToObject = function(queryString) {
			const params = new URLSearchParams(queryString);
			const result = {};

			// Step 1: Convert query string to a nested object
			params.forEach((value, key) => {
				let keys = key.match(/(\w+)/g); // Extract keys using regex
				let lastKey = keys.pop(); // The last key corresponds to the deepest nested value
				let obj = keys.reduce((res, k) => res[k] = res[k] || {}, result); // Build nested objects
				obj[lastKey] = value; // Assign the value to the deepest level
			});

			// Step 2: Transform the top level of the object into an array
			const resultArray = Object.keys(result).map(topLevelKey => {
				// 'result[topLevelKey]' is assumed to be an object itself, and we're transforming
				// it into an array of objects, each corresponding to one of its properties
				return Object.keys(result[topLevelKey]).map(index => {
					return result[topLevelKey][index];
				});
			}).flat(); // Use 'flat()' to flatten the resulting array of arrays into a single array

			return resultArray;
		};

		describe('Maintain backward compatibility', () => {
			it('Should store one value', () => {
				BX.userOptions.save('disk', 'config_status', 'fix_footer', 'off');

				const oldFashion = queryStringToObject(BX.userOptions.__get());
				const newFashion = BX.userOptions.__get_values({backwardCompatibility: true});

				assert.deepEqual(oldFashion, newFashion);
			});

			it('Should store few values', () => {
				BX.userOptions.save('disk', 'config_status', 'fix_footer', 'off');
				BX.userOptions.save('disk', 'config_status', 'fix_footer1', 'off');
				BX.userOptions.save('crm', 'status', 'fix_footer', 'off');

				const oldFashion = queryStringToObject(BX.userOptions.__get());
				const newFashion = BX.userOptions.__get_values({backwardCompatibility: true});

				assert.deepEqual(oldFashion, newFashion);
			});

			it('Should rewrite value', () => {
				BX.userOptions.save('disk', 'config_status', 'fix_footer', 'off');
				BX.userOptions.save('disk', 'config_status', 'fix_footer', 'on');
				BX.userOptions.save('disk', 'config_status', 'fix_footer', 'off');
				BX.userOptions.save('disk', 'config_status', 'fix_footer', 'on');

				const oldFashion = queryStringToObject(BX.userOptions.__get());
				const newFashion = BX.userOptions.__get_values({backwardCompatibility: true});

				assert.deepEqual(oldFashion, newFashion);
			});

			it('Should support Common', () => {
				BX.userOptions.save('disk', 'config_status', 'fix_footer', 'off', true);

				const oldFashion = queryStringToObject(BX.userOptions.__get());
				const newFashion = BX.userOptions.__get_values({backwardCompatibility: true});

				assert.deepEqual(oldFashion, newFashion);
			});

			it('Should support rewrite Common', () => {
				BX.userOptions.save('disk', 'config_status', 'fix_footer', 'off', true);
				BX.userOptions.save('disk', 'config_status', 'fix_footer', 'off', false);

				const oldFashion = queryStringToObject(BX.userOptions.__get());
				const newFashion = BX.userOptions.__get_values({backwardCompatibility: true});

				assert.deepEqual(oldFashion, newFashion);

				BX.userOptions.save('disk', 'config_status', 'fix_footer', 'off', false);
				BX.userOptions.save('disk', 'config_status', 'fix_footer', 'off', true);

				const oldFashion2 = queryStringToObject(BX.userOptions.__get());
				const newFashion2 = BX.userOptions.__get_values({backwardCompatibility: true});

				assert.deepEqual(oldFashion2, newFashion2);
			});

			it('Should support Arrays', () => {
				BX.userOptions.save('intranet', 'user_search', 'last_selected', [1, 2]);
				BX.userOptions.save('my', 'choice', 'be_a_superman', [null, 'can', 4, 2, 'solve', 'any', 'problem ?+_=']);

				const oldFashion = queryStringToObject(BX.userOptions.__get());
				const newFashion = BX.userOptions.__get_values({backwardCompatibility: true});

				assert.deepEqual(oldFashion, newFashion);
			});

			it('Should not support Objects', () => {
				BX.userOptions.save('intranet', 'user_search', 'last_selected', {"sample": [1,2]});
				BX.userOptions.save('intranet', 'user_search', 'last_selected', {"sample": {"example": [1,2]}});

				const oldFashion = queryStringToObject(BX.userOptions.__get());
				const newFashion = BX.userOptions.__get_values({backwardCompatibility: true});

				assert.notDeepEqual(oldFashion, newFashion);
			});
		});

	});
});
