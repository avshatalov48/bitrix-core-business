import LocalStorageCache from '../../../src/lib/cache/local-storage-cache';

describe('core/cache/local-storage-cache', () => {
	beforeEach(() => {
		localStorage.removeItem('BX.Cache.Storage.LsStorage.stack');
	});

	it('Should be exported as function', () => {
		assert.ok(typeof LocalStorageCache === 'function', 'LocalStorageCache is not a function');
	});

	describe('#get', () => {
		it('Should return undefined if cache storage not contains entry with specified key', () => {
			const cache = new LocalStorageCache();

			assert.ok(cache.get('test') === undefined);
		});

		it('Should return default value if cache storage not contains cache entry with specified key', () => {
			const cache = new LocalStorageCache();

			assert.ok(cache.get('test', 10) === 10);
			assert.ok(cache.get('test', 99) === 99);
			assert.ok(cache.get('test', 'value') === 'value');
			assert.ok(cache.get('test', false) === false);
			assert.ok(cache.get('test', true) === true);

			const testObject = {};
			assert.ok(cache.get('test', testObject) === testObject);

			const testArray = [];
			assert.ok(cache.get('test', testArray) === testArray);
		});

		it('Should return value from cache storage if entry with specified exists', () => {
			const cache = new LocalStorageCache();
			cache.set('test', 'testValue');

			assert.ok(cache.get('test') === 'testValue');
		});

		it('Should not return default value if storage contains entry with specified key', () => {
			const cache = new LocalStorageCache();
			cache.set('test', 'testValue');

			assert.ok(cache.get('test', 'default') === 'testValue');
		});

		it('Should return default value from specified callback', () => {
			const cache = new LocalStorageCache();

			assert.ok(cache.get('test', () => 'testValue') === 'testValue');
		});

		it('Should not save default value to storage', () => {
			const cache = new LocalStorageCache();

			void cache.get('test', 'testValue');
			assert.ok(cache.get('test') === undefined);
		});
	});

	describe('#set', () => {
		it('Should set entry with specified key and value', () => {
			const cache = new LocalStorageCache();

			cache.set('test', 'testValue');
			assert.ok(cache.get('test') === 'testValue');
		});

		it('Should set primitive value', () => {
			const cache = new LocalStorageCache();

			cache.set('test1', 'test1');
			assert.ok(cache.get('test1') === 'test1');

			cache.set('test2', 2);
			assert.ok(cache.get('test2') === 2);

			cache.set('test3', 3.2);
			assert.ok(cache.get('test3') === 3.2);

			cache.set('test4', null);
			assert.ok(cache.get('test4') === null);

			cache.set('test5', true);
			assert.ok(cache.get('test5') === true);

			cache.set('test6', false);
			assert.ok(cache.get('test6') === false);

			cache.set('test7', undefined);
			assert.ok(cache.get('test7') === undefined);
		});

		it('Should set object value', () => {
			const cache = new LocalStorageCache();

			const testObject = {};
			cache.set('test1', testObject);
			assert.ok(cache.get('test1') === testObject);

			const testArray = [];
			cache.set('test2', testArray);
			assert.ok(cache.get('test2') === testArray);

			const testFunction = () => {};
			cache.set('test3', testFunction);
			assert.ok(cache.get('test3') === testFunction);

			const testMap = new Map();
			cache.set('test4', testMap);
			assert.ok(cache.get('test4') === testMap);

			const testElement = document.createElement('div');
			cache.set('test4', testElement);
			assert.ok(cache.get('test4') === testElement);
		});
	});

	describe('#remember', () => {
		it('Should return and set default value if storage not contain entry with specified key', () => {
			const cache = new LocalStorageCache();

			assert.ok(cache.remember('test', 'testValue') === 'testValue');
			assert.ok(cache.get('test') === 'testValue');

			assert.ok(cache.remember('test2', () => 'test2Value') === 'test2Value');
			assert.ok(cache.get('test2') === 'test2Value');

			const testObject = {};
			assert.ok(cache.remember('test3', () => testObject) === testObject);
			assert.ok(cache.get('test3') === testObject);
		});
	});

	describe('#has', () => {
		it('Should return true if storage contain entry with specified key', () => {
			const cache = new LocalStorageCache();
			cache.set('test', 'testValue');
			assert.ok(cache.has('test') === true);
		});

		it('Should return false if storage not contain entry with specified key', () => {
			const cache = new LocalStorageCache();
			assert.ok(cache.has('test') === false);
		});
	});

	describe('#delete', () => {
		it('Should delete entry with specified key', () => {
			const cache = new LocalStorageCache();

			cache.set('test1', 'test1value');
			cache.set('test2', 'test2value');

			cache.delete('test1');
			assert.ok(cache.get('test1') === undefined);
			assert.ok(cache.get('test2') === 'test2value');

			cache.delete('test2');
			assert.ok(cache.get('test2') === undefined);
		});
	});

	describe('#size', () => {
		it('Should return correct size of cache storage', () => {
			const cache = new LocalStorageCache();

			cache.set('test1', 'test1value');
			cache.set('test2', 'test2value');
			cache.set('test3', 'test3value');
			assert.ok(cache.size() === 3);

			cache.delete('test1');
			assert.ok(cache.size() === 2);

			cache.delete('test2');
			cache.delete('test3');
			assert.ok(cache.size() === 0);
		});
	});

	describe('#keys', () => {
		it('Should return correct storage keys list', () => {
			const cache = new LocalStorageCache();

			cache.set('test1', 'test1value');
			cache.set('test2', 'test2value');
			cache.set('test3', 'test3value');
			assert.deepEqual(cache.keys(), ['test1', 'test2', 'test3']);

			cache.delete('test1');
			assert.deepEqual(cache.keys(), ['test2', 'test3']);
		});
	});

	describe('#values', () => {
		it('Should return correct storage values list', () => {
			const cache = new LocalStorageCache();

			cache.set('test1', 'test1value');
			cache.set('test2', 'test2value');
			cache.set('test3', 'test3value');
			assert.deepEqual(cache.values(), ['test1value', 'test2value', 'test3value']);

			cache.delete('test1');
			assert.deepEqual(cache.values(), ['test2value', 'test3value']);
		});
	});
});