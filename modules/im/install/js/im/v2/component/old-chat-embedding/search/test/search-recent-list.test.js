import {SearchRecentList} from '../src/search-recent-list';
import {Type} from 'main.core';

describe('SearchRecentList', () => {
	const store = null;
	const controller = null;
	let restClient = null;
	let $Bitrix = null;
	let search = null;

	before(async () => {
		restClient = {
			callMethod: () => {}
		};
		$Bitrix = {
			Data: {
				get()
				{
					return {store, controller};
				}
			},
			RestClient: {
				get()
				{
					return restClient;
				}
			}
		};
	});

	it('should be a function type', () => {
		assert.equal(Type.isFunction(SearchRecentList), true);
	});

	describe('doesItemMatchQuery', () => {
		before(() => {
			search = new SearchRecentList($Bitrix);
		});
		it('should return true for complete match', () => {
			const fieldsForSearch = ['ivan'];
			const queryWords = ['ivan'];
			const matchResult = search.doesItemMatchQuery(fieldsForSearch, queryWords);
			assert.equal(matchResult, true);
		});

		it('should return false if query word doesn\'t match', () => {
			const fieldsForSearch = ['ivan'];
			const queryWords = ['petr'];
			const matchResult = search.doesItemMatchQuery(fieldsForSearch, queryWords);
			assert.equal(matchResult, false);
		});

		it('should return true for one match', () => {
			const fieldsForSearch = ['ivan', 'smirnov', 'developer'];
			const queryWords = ['smi'];
			const matchResult = search.doesItemMatchQuery(fieldsForSearch, queryWords);
			assert.equal(matchResult, true);
		});

		it('should return true for two matches', () => {
			const fieldsForSearch = ['ivan', 'smirnov', 'developer'];
			const queryWords = ['iva', 'smi'];
			const matchResult = search.doesItemMatchQuery(fieldsForSearch, queryWords);
			assert.equal(matchResult, true);
		});

		it('should return false if any query word doesn\'t match', () => {
			const fieldsForSearch = ['Ivan', 'Ivanov'];
			const queryWords = ['Ivan', 'Smirnov'];
			const matchResult = search.doesItemMatchQuery(fieldsForSearch, queryWords);
			assert.equal(matchResult, false);
		});

		it('should return true if all query words match with one field', () => {
			// trying to repeat the backend behavior
			const fieldsForSearch = ['Ivan', 'Smirnov'];
			const queryWords = ['Ivan', 'Ivan'];
			const matchResult = search.doesItemMatchQuery(fieldsForSearch, queryWords);
			assert.equal(matchResult, true);
		});
	});
});