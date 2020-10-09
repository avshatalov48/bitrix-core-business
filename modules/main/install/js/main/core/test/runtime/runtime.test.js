import Runtime from '../../src/lib/runtime';
import {internalClone} from '../../src/lib/runtime/clone';

function shuffle(a) {
	for (let i = a.length - 1; i > 0; i--)
	{
		const j = Math.floor(Math.random() * (i + 1));
		[a[i], a[j]] = [a[j], a[i]];
	}
	return a;
}

describe('Runtime', () => {
	it('Should be exported as function', () => {
		assert(typeof Runtime === 'function');
	});

	describe('#merge', () => {
		it('Should be a function', () => {
			assert(typeof Runtime.merge === 'function');
		});

		it('Should merge 2 simple objects', () => {
			const source1 = {prop1: 1, prop2: 2};
			const source2 = {prop1: 99, prop3: 3};
			const result = Runtime.merge(source1, source2);

			assert.equal(Object.keys(result).length, 3);
			assert.equal(result.prop1, 99);
			assert.equal(result.prop2, 2);
			assert.equal(result.prop3, 3);
		});

		it('Should merge more than 2 objects', () => {
			const source1 = {prop1: 1, prop2: 2};
			const source2 = {prop1: 99, prop3: 3};
			const source3 = {prop1: 100, prop4: 4};
			const source4 = {prop2: 222, prop5: 5};

			const result = Runtime.merge(source1, source2, source3, source4);

			assert.equal(Object.keys(result).length, 5);
			assert.equal(result.prop1, 100);
			assert.equal(result.prop2, 222);
			assert.equal(result.prop3, 3);
			assert.equal(result.prop4, 4);
			assert.equal(result.prop5, 5);
		});

		it('Should not modify merging objects', () => {
			const source1 = {prop1: 1, prop2: 2};
			const source1Clone = {...source1};
			const source2 = {prop1: 99, prop3: 3};
			const source2Clone = {...source2};
			const source3 = {prop1: 100, prop4: 4};
			const source3Clone = {...source3};
			const source4 = {prop2: 222, prop5: 5};
			const source4Clone = {...source4};

			void Runtime.merge(source1, source2, source3, source4);

			assert.deepEqual(source1, source1Clone);
			assert.deepEqual(source2, source2Clone);
			assert.deepEqual(source3, source3Clone);
			assert.deepEqual(source4, source4Clone);
		});

		it('Should merge child objects', () => {
			const source1 = {
				prop1: 1,
				prop2: 2,
				propA: [1, 2, 3],
				child: {
					prop11: 11,
					prop22: 22,
					prop99: {
						prop111: 111,
					}
				},
			};

			const source2 = {
				prop1: 1,
				prop2: 2,
				propA: [1, 2, 4, 5],
				child: {
					prop11: 11,
					prop22: 2222,
					prop33: 33,
					prop99: {
						prop222: 222,
					}
				},
			};

			const result = {
				prop1: 1,
				prop2: 2,
				propA: [1, 2, 4, 5],
				child: {
					prop11: 11,
					prop22: 2222,
					prop33: 33,
					prop99: {
						prop111: 111,
						prop222: 222,
					}
				},
			};

			const merged = Runtime.merge(source1, source2);

			assert.deepEqual(merged, result);
		});

		it('Should merge arrays', () => {
			const source1 = [1, 2, 3, 4];
			const source2 = [9,,,7];

			const result = [9, 2, 3, 7];
			const merged = Runtime.merge(source1, source2);

			assert.deepEqual(merged, result);
		});

		it('Should deep merge array of objects', () => {
			const source1 = [{test1: 1}, {test2: 2}];
			const source2 = [{test1: 99}, {test3: 3}];

			const result = [{test1: 99}, {test2: 2, test3: 3}];
			const merged = Runtime.merge(source1, source2);

			assert.deepEqual(merged, result);
		});

		it('Should merge arrays with html elements', () => {
			const element = document.createElement('div');
			const source1 = ['', 'test'];
			const source2 = [element];

			const result = Runtime.merge(source1, source2);

			assert.ok(result[0] === element);
			assert.ok(result[1] === 'test');
		});

		describe('Memory leak detection', () => {
			it('Should not retain passed params', () => {
				let item1 = [1, 2, {test: 1}];
				let item2 = [3, 4, {test: 2}];
				let result = Runtime.merge(item1, item2);

				let isItem1Collected = false;
				global.weak(item1, () => {
					isItem1Collected = true;
				});

				let isItem2Collected = false;
				global.weak(item2, () => {
					isItem2Collected = true;
				});

				let isResultCollected = false;
				global.weak(result, () => {
					isResultCollected = true;
				});

				item1 = null;
				item2 = null;
				result = null;

				global.gc();

				assert.ok(isItem1Collected, 'Memory leak detected! "item1" is not collected');
				assert.ok(isItem2Collected, 'Memory leak detected! "item2" is not collected');
				assert.ok(isResultCollected, 'Memory leak detected! "result" is not collected');
			});
		});
	});

	describe('#clone', () => {
		it('Should be a function', () => {
			assert(typeof Runtime.clone === 'function');
		});

		it('Should clone plain object', () => {
			const source = {
				prop1: 'value1',
				prop2: 'value2',
				prop3: ['1', '2', '3']
			};
			const clone = Runtime.clone(source);

			assert.deepEqual(source, clone);
			assert(clone !== source);
		});

		it('Should clone plain object with circular reference', () => {
			const source = {
				prop1: 'value1',
				prop2: 'value2',
				prop3: ['1', '2', '3'],
				prop4: source,
			};
			const clone = Runtime.clone(source);

			assert.deepEqual(source, clone);
			assert(clone !== source);
		});

		it('Should clone array', () => {
			const source = [1, 2, 3, 4];
			const clone = Runtime.clone(source);

			assert.deepEqual(source, clone);
			assert(clone !== source);
		});

		it('Should clone array with circular reference', () => {
			const source = [1, 2, 3, 4, source];
			const clone = Runtime.clone(source);

			assert.deepEqual(source, clone);
			assert(clone !== source);
		});

		it('Should clone typed object', () => {
			class MyClass2 {}
			class MyClass extends MyClass2 {
				constructor() {
					super();
					this.prop1 = 1;
					this.prop2 = 2;
				}
			}

			const source = new MyClass();
			const clone = Runtime.clone(source);

			assert.deepEqual(source, clone);
			assert(clone !== source);
			assert(clone instanceof MyClass);
			assert(clone instanceof MyClass2);
		});

		it('Should clone typed object', () => {
			function MyClass() {
				this.prop1 = 1;
			}

			MyClass.prototype.test = function() {};

			const source = new MyClass();
			const clone = Runtime.clone(source);

			assert.deepEqual(source, clone);
			assert(clone !== source);
		});

		it('Should clone named object without constructor property in prototype', () => {
			function MyClass() {}
			MyClass.prototype = {
				getName() {
					return 'MyClass';
				}
			};

			const source = new MyClass();
			const clone = Runtime.clone(source);

			assert.deepEqual(source, clone);
			assert(source !== clone);
			assert(source instanceof MyClass);
			assert(clone instanceof MyClass);
		});

		describe('Memory leak detection', () => {
			it('Should not leak if clone object', () => {
				let source = {
					string: 'test',
					number: 11,
					object: {test: 1},
					array: [1, 2, 3],
					element: document.createElement('div'),
				};
				let cloned = Runtime.clone(source);

				let isSourceCollected = false;
				global.weak(source, () => {
					isSourceCollected = true;
				});

				let isClonedCollected = false;
				global.weak(cloned, () => {
					isClonedCollected = true;
				});

				source = null;
				cloned = null;

				global.gc();

				assert.ok(isSourceCollected, 'Memory leak detected! "Source" not collected');
				assert.ok(isClonedCollected, 'Memory leak detected! "Cloned" not collected');
			});

			it('Should not leak if clone array', () => {
				let source = ['test', 1, {test: 1}, document.createElement('div'), source];
				let cloned = Runtime.clone(source);

				let isSourceCollected = false;
				global.weak(source, () => {
					isSourceCollected = true;
				});

				let isClonedCollected = false;
				global.weak(cloned, () => {
					isClonedCollected = true;
				});

				source = null;
				cloned = null;

				global.gc();

				assert.ok(isSourceCollected, 'Memory leak detected! "Source" not collected');
				assert.ok(isClonedCollected, 'Memory leak detected! "Cloned" not collected');
			});

			it('Should not leak if clone element without child', () => {
				let source = document.createElement('div');
				let cloned = Runtime.clone(source);

				let isSourceCollected = false;
				global.weak(source, () => {
					isSourceCollected = true;
				});

				let isClonedCollected = false;
				global.weak(cloned, () => {
					isClonedCollected = true;
				});

				source = null;
				cloned = null;

				global.gc();

				assert.ok(isSourceCollected, 'Memory leak detected! "Source" not collected');
				assert.ok(isClonedCollected, 'Memory leak detected! "Cloned" not collected');
			});

			it('Should not leak if clone element with child', () => {
				let source = document.createElement('div');
				let childElement = document.createElement('span');
				let childTextNode = document.createTextNode('Hello!');
				source.appendChild(childElement);
				source.appendChild(childTextNode);
				let cloned = Runtime.clone(source);

				let isSourceCollected = false;
				global.weak(source, () => {
					isSourceCollected = true;
				});

				let isClonedCollected = false;
				global.weak(cloned, () => {
					isClonedCollected = true;
				});

				childElement = null;
				childTextNode = null;
				source = null;
				cloned = null;

				global.gc();

				assert.ok(isSourceCollected, 'Memory leak detected! "Source" not collected');
				assert.ok(isClonedCollected, 'Memory leak detected! "Cloned" not collected');
			});

			it('Should not leak if clone Date', () => {
				let source = new Date();
				let cloned = Runtime.clone(source);

				let isSourceCollected = false;
				global.weak(source, () => {
					isSourceCollected = true;
				});

				let isClonedCollected = false;
				global.weak(cloned, () => {
					isClonedCollected = true;
				});

				source = null;
				cloned = null;

				global.gc();

				assert.ok(isSourceCollected, 'Memory leak detected! "Source" not collected');
				assert.ok(isClonedCollected, 'Memory leak detected! "Cloned" not collected');
			});

			it('Should not leak if clone Map', () => {
				let source = new Map();
				let cloned = Runtime.clone(source);

				let isSourceCollected = false;
				global.weak(source, () => {
					isSourceCollected = true;
				});

				let isClonedCollected = false;
				global.weak(cloned, () => {
					isClonedCollected = true;
				});

				source = null;
				cloned = null;

				global.gc();

				assert.ok(isSourceCollected, 'Memory leak detected! "Source" not collected');
				assert.ok(isClonedCollected, 'Memory leak detected! "Cloned" not collected');
			});

			it('Should not leak if clone Set', () => {
				let source = new Set();
				let cloned = Runtime.clone(source);

				let isSourceCollected = false;
				global.weak(source, () => {
					isSourceCollected = true;
				});

				let isClonedCollected = false;
				global.weak(cloned, () => {
					isClonedCollected = true;
				});

				source = null;
				cloned = null;

				global.gc();

				assert.ok(isSourceCollected, 'Memory leak detected! "Source" not collected');
				assert.ok(isClonedCollected, 'Memory leak detected! "Cloned" not collected');
			});

			it('Should not leak if clone RegExp', () => {
				let source = /\w+/;
				let cloned = Runtime.clone(source);

				let isSourceCollected = false;
				global.weak(source, () => {
					isSourceCollected = true;
				});

				let isClonedCollected = false;
				global.weak(cloned, () => {
					isClonedCollected = true;
				});

				source = null;
				cloned = null;

				global.gc();

				assert.ok(isSourceCollected, 'Memory leak detected! "Source" not collected');
				assert.ok(isClonedCollected, 'Memory leak detected! "Cloned" not collected');
			});

			it('Should not leak in internalClone', () => {
				let map = new WeakMap();
				let source = {test: 1};
				let cloned = internalClone(source, map);

				let isMapCollected = false;
				global.weak(source, () => {
					isMapCollected = true;
				});

				let isSourceCollected = false;
				global.weak(source, () => {
					isSourceCollected = true;
				});

				let isClonedCollected = false;
				global.weak(cloned, () => {
					isClonedCollected = true;
				});

				map = null;
				source = null;
				cloned = null;

				global.gc();

				assert.ok(isMapCollected, 'Memory leak detected! "map" not collected');
				assert.ok(isSourceCollected, 'Memory leak detected! "Source" not collected');
				assert.ok(isClonedCollected, 'Memory leak detected! "Cloned" not collected');
			});
		});
	});

	describe('#orderBy', () => {
		it('Should be a function', () => {
			assert.ok(typeof Runtime.orderBy === 'function');
		});

		it('Should sort array of objects by field with string value (default asc)', () => {
			const arr = [
				{name: 'b'},
				{name: 'a'},
				{name: 'c'},
				{name: 'z'},
				{name: 'd'},
			];
			const ascResult = [
				{name: 'a'},
				{name: 'b'},
				{name: 'c'},
				{name: 'd'},
				{name: 'z'},
			];

			assert.deepEqual(Runtime.orderBy(arr, ['name']), ascResult);
		});

		it('Should sort array of objects by field with string value (asc)', () => {
			const arr = [
				{name: 'b'},
				{name: 'a'},
				{name: 'c'},
				{name: 'z'},
				{name: 'd'},
			];
			const ascResult = [
				{name: 'a'},
				{name: 'b'},
				{name: 'c'},
				{name: 'd'},
				{name: 'z'},
			];

			assert.deepEqual(Runtime.orderBy(arr, ['name'], ['asc']), ascResult);
		});

		it('Should sort array of objects by field with string value (desc)', () => {
			const arr = [
				{name: 'b'},
				{name: 'a'},
				{name: 'c'},
				{name: 'z'},
				{name: 'd'},
			];
			const descResult = [
				{name: 'z'},
				{name: 'd'},
				{name: 'c'},
				{name: 'b'},
				{name: 'a'},
			];

			assert.deepEqual(Runtime.orderBy(arr, ['name'], ['desc']), descResult);
		});

		it('Should sort array of object by field with date value (asc)', () => {
			const time = +new Date();

			const arr = [
				{value: new Date(time - 4), sort: 3},
				{value: new Date(time - 2), sort: 5},
				{value: new Date(time - 1), sort: 6},
				{value: new Date(time - 3), sort: 4},
				{value: new Date(time - 6), sort: 1},
				{value: new Date(time - 5), sort: 2},
			];

			const ascResult = [
				{value: new Date(time - 6), sort: 1},
				{value: new Date(time - 5), sort: 2},
				{value: new Date(time - 4), sort: 3},
				{value: new Date(time - 3), sort: 4},
				{value: new Date(time - 2), sort: 5},
				{value: new Date(time - 1), sort: 6},
			];

			assert.deepEqual(Runtime.orderBy(arr, ['value'], ['asc']), ascResult);
		});

		it('Should sort array of object by field with date value (desc)', () => {
			const time = +new Date();

			const arr = [
				{value: new Date(time - 4), sort: 3},
				{value: new Date(time - 2), sort: 5},
				{value: new Date(time - 1), sort: 6},
				{value: new Date(time - 3), sort: 4},
				{value: new Date(time - 6), sort: 1},
				{value: new Date(time - 5), sort: 2},
			];

			const descResult = [
				{value: new Date(time - 1), sort: 6},
				{value: new Date(time - 2), sort: 5},
				{value: new Date(time - 3), sort: 4},
				{value: new Date(time - 4), sort: 3},
				{value: new Date(time - 5), sort: 2},
				{value: new Date(time - 6), sort: 1},
			];

			assert.deepEqual(Runtime.orderBy(arr, ['value'], ['desc']), descResult);
		});

		it('Should sort array of objects by multiple fields (asc, asc)', () => {
			const arr = [
				{id: 1, range: 1},
				{id: 5, range: 5},
				{id: 2, range: 2},
				{id: 3, range: 3},
				{id: 4, range: 4},
				{id: 3, range: 2},
			];
			const ascAscResult = [
				{id: 1, range: 1},
				{id: 2, range: 2},
				{id: 3, range: 2},
				{id: 3, range: 3},
				{id: 4, range: 4},
				{id: 5, range: 5},
			];

			assert.deepEqual(Runtime.orderBy(arr, ['id', 'range']), ascAscResult);
		});

		it('Should sort array of objects by multiple fields (asc, desc)', () => {
			const arr = [
				{id: 1, range: 1},
				{id: 5, range: 5},
				{id: 2, range: 2},
				{id: 3, range: 3},
				{id: 4, range: 4},
				{id: 3, range: 2},
			];
			const ascDescResult = [
				{id: 1, range: 1},
				{id: 2, range: 2},
				{id: 3, range: 3},
				{id: 3, range: 2},
				{id: 4, range: 4},
				{id: 5, range: 5},
			];

			assert.deepEqual(Runtime.orderBy(arr, ['id', 'range'], ['asc', 'desc']), ascDescResult);
		});

		it('Should sort array of objects by multiple fields (desc, desc)', () => {
			const arr = [
				{id: 1, range: 1},
				{id: 5, range: 5},
				{id: 2, range: 2},
				{id: 3, range: 3},
				{id: 4, range: 4},
				{id: 3, range: 2},
			];
			const descDescResult = [
				{id: 5, range: 5},
				{id: 4, range: 4},
				{id: 3, range: 3},
				{id: 3, range: 2},
				{id: 2, range: 2},
				{id: 1, range: 1},
			];

			assert.deepEqual(Runtime.orderBy(arr, ['id', 'range'], ['desc', 'desc']), descDescResult);
		});

		it('Should sort array of objects by multiple fields (desc, asc)', () => {
			const arr = [
				{id: 1, range: 1},
				{id: 5, range: 5},
				{id: 2, range: 2},
				{id: 3, range: 3},
				{id: 4, range: 4},
				{id: 3, range: 2},
			];
			const descAscResult = [
				{id: 5, range: 5},
				{id: 4, range: 4},
				{id: 3, range: 2},
				{id: 3, range: 3},
				{id: 2, range: 2},
				{id: 1, range: 1},
			];

			assert.deepEqual(Runtime.orderBy(arr, ['id', 'range'], ['desc', 'asc']), descAscResult);
		});

		it('Should sort array of objects by field with string case-insensitive (asc)', () => {
			const arr = [
				{name: 'a', sort: 1},
				{name: 'b', sort: 3},
				{name: 'A', sort: 2},
				{name: 'B', sort: 4},
			];
			const caseInsensitiveResult = [
				{name: 'a', sort: 1},
				{name: 'A', sort: 2},
				{name: 'b', sort: 3},
				{name: 'B', sort: 4},
			];

			assert.deepEqual(Runtime.orderBy(arr, ['name']), caseInsensitiveResult);
		});

		it('Should sort array of objects by field with string case-insensitive (desc)', () => {
			const arr = [
				{name: 'a', sort: 1},
				{name: 'b', sort: 3},
				{name: 'A', sort: 2},
				{name: 'B', sort: 4},
			];
			const caseInsensitiveResult = [
				{name: 'b', sort: 3},
				{name: 'B', sort: 4},
				{name: 'a', sort: 1},
				{name: 'A', sort: 2},
			];

			assert.deepEqual(Runtime.orderBy(arr, ['name'], ['desc']), caseInsensitiveResult);
		});

		it('Should implements stable asc sort algorithm', () => {
			const ascResult = [
				{name: 'a', sort: 1},
				{name: 'a', sort: 2},
				{name: 'b', sort: 3},
				{name: 'c', sort: 4},
				{name: 'd', sort: 5},
				{name: 'z', sort: 6},
			];

			Array.from({length: 1000}).forEach(() => {
				const arr = [
					...shuffle([
						{name: 'b', sort: 3},
						{name: 'c', sort: 4},
						{name: 'z', sort: 6},
						{name: 'd', sort: 5},
					]),
					...[
						{name: 'a', sort: 1},
						{name: 'a', sort: 2},
					]
				];
				assert.deepEqual(Runtime.orderBy(arr, ['name'], ['asc']), ascResult);
			});
		});

		it('Should implements stable desc sort algorithm', () => {
			const descResult = [
				{name: 'z', sort: 6},
				{name: 'd', sort: 5},
				{name: 'c', sort: 4},
				{name: 'b', sort: 3},
				{name: 'a', sort: 1},
				{name: 'a', sort: 2},
			];

			Array.from({length: 1000}).forEach(() => {
				const arr = [
					...shuffle([
						{name: 'b', sort: 3},
						{name: 'c', sort: 4},
						{name: 'z', sort: 6},
						{name: 'd', sort: 5},
					]),
					...[
						{name: 'a', sort: 1},
						{name: 'a', sort: 2},
					]
				];
				assert.deepEqual(Runtime.orderBy(arr, ['name'], ['desc']), descResult);
			});
		});
	});

	describe('#destroy', () => {
		it('Should destroy plain object', () => {
			const object = {
				prop1: 1,
				prop2: 2,
				getAny() {
					return 'any';
				},
			};

			Runtime.destroy(object);

			assert.throws(
				() => {
					void object.prop1;
				},
				'Uncaught Error: Object is destroyed',
			);

			assert.throws(
				() => {
					void object.prop2;
				},
				'Uncaught Error: Object is destroyed',
			);

			assert.throws(
				() => {
					object.getAny();
				},
				'Uncaught Error: Object is destroyed',
			);

			assert.throws(
				() => {
					object.toString();
				},
				'Uncaught Error: Object is destroyed',
			);

			assert.throws(
				() => {
					object.propertyIsEnumerable('prop1');
				},
				'Uncaught Error: Object is destroyed',
			);
		});

		it('Should destroy ES6 class instance', () => {
			class MyClass
			{
				constructor()
				{
					this.prop1 = 1;
					this.prop2 = 2;
				}

				getAny()
				{
					return 'any';
				}
			}

			const object = new MyClass();

			Runtime.destroy(object);

			assert.throws(
				() => {
					void object.prop1;
				},
				'Uncaught Error: Object is destroyed',
			);

			assert.throws(
				() => {
					void object.prop2;
				},
				'Uncaught Error: Object is destroyed',
			);

			assert.throws(
				() => {
					object.getAny();
				},
				'Uncaught Error: Object is destroyed',
			);
		});

		it('Should does not throws if passed destroyed object', () => {
			const object = {
				prop1: 1,
				prop2: 2,
				getAny() {
					return 'any';
				},
			};

			Runtime.destroy(object);

			assert.doesNotThrow(() => {
				Runtime.destroy(object);
			});
		});

		it('Should does not throws if passed primitive', () => {
			assert.doesNotThrow(() => {
				Runtime.destroy(1);
			});

			assert.doesNotThrow(() => {
				Runtime.destroy(true);
			});

			assert.doesNotThrow(() => {
				Runtime.destroy('string');
			});

			assert.doesNotThrow(() => {
				Runtime.destroy(Symbol('111'));
			});
		});
	});
});