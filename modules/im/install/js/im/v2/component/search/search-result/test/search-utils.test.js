import {SearchUtils} from '../src/classes/search-utils';
import {Type} from 'main.core';

describe('SearchUtils', () => {
	it('should be an Object', () => {
		assert.equal(Type.isPlainObject(SearchUtils), true);
	});

	describe('convertKeysToLowerCase', () => {
		it('should convert flat object keys', () => {
			const object = {
				teSt: 'TEST',
				foO: 'Foo',
				BaR: 'bar',
			};
			assert.equal(SearchUtils.convertKeysToLowerCase(object).test, 'TEST');
			assert.equal(SearchUtils.convertKeysToLowerCase(object).foo, 'Foo');
			assert.equal(SearchUtils.convertKeysToLowerCase(object).bar, 'bar');
		});

		it('should convert object keys deeply', () => {
			const object = {
				teSt: 'TEST',
				fOO: {
					teSt: 'TEST',
					fOObAR: {
						qWe: 'bar'
					}
				}
			};

			assert.equal(SearchUtils.convertKeysToLowerCase(object).foo.test, 'TEST');
			assert.equal(SearchUtils.convertKeysToLowerCase(object).foo.foobar.qwe, 'bar');
		});

		it('should not convert arrays inside objects', () => {
			const object = {
				tesT: ['aRrAy'],
				BaR: {
					teSt: 'some value',
				}
			};
			const convertedObject = SearchUtils.convertKeysToLowerCase(object);

			assert.equal(Type.isArray(convertedObject.test), true);
			assert.equal(convertedObject.test[0], 'aRrAy');
			assert.equal(convertedObject.bar.test, 'some value');
		});
	});
});