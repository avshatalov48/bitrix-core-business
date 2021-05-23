import SearchIndex from '../src/search/search-index';
import SearchField from '../src/search/search-field';

describe('Search Index', () => {

	it('Common phrases', () => {
		const searchField = new SearchField({
			name: "title",
			type: "string",
			searchable: true,
			system: true
		});

		const tests = [
			[
				'Ivan Petrov',
				[
					{ word: 'ivan', startIndex: 0 },
					{ word: 'petrov', startIndex: 5 },
				],
			],
			[
				'Bitrix Site Manager',
				[
					{ word: 'bitrix', startIndex: 0 },
					{ word: 'site', startIndex: 7 },
					{ word: 'manager', startIndex: 12 },
				],
			],
			[
				'Bitrix: Site Manager',
				[
					{ word: 'bitrix', startIndex: 0 },
					{ word: 'site', startIndex: 8 },
					{ word: 'manager', startIndex: 13 },
					{ word: 'bitrix:', startIndex: 0 },
				],
			],
			[
				'Alexey Ivanov-Vodkin',
				[
					{ word: 'alexey', startIndex: 0 },
					{ word: 'ivanov', startIndex: 7 },
					{ word: 'vodkin', startIndex: 14 },
					{ word: 'ivanov-vodkin', startIndex: 7 },
				],
			],
		];

		tests.forEach(test => {
			const [ phrase, indexes ] = test;
			const index = SearchIndex.createIndex(searchField, phrase);
			assert.deepEqual(index.getIndexes(), indexes, phrase);
		});
	});

	it('Complex Words', () => {

		const searchField = new SearchField({
			name: "title",
			type: "string",
			searchable: true,
			system: true
		});

		const tests = [
			[
				'GoPro105 walk500Miles word',
				[
					{ word: 'go', startIndex: 0 },
					{ word: 'pro', startIndex: 2 },
					{ word: '105', startIndex: 5 },
					{ word: 'walk', startIndex: 9 },
					{ word: '500', startIndex: 13 },
					{ word: 'miles', startIndex: 16 },
					{ word: 'word', startIndex: 22 },
					{ word: 'gopro105', startIndex: 0 },
					{ word: 'walk500miles', startIndex: 9 }
				],
			],
			[
				'GoPro105 walk500Miles word GoPro105',
				[
					{ word: 'go', startIndex: 0 },
					{ word: 'pro', startIndex: 2 },
					{ word: '105', startIndex: 5 },
					{ word: 'walk', startIndex: 9 },
					{ word: '500', startIndex: 13 },
					{ word: 'miles', startIndex: 16 },
					{ word: 'word', startIndex: 22 },
					{ word: 'go', startIndex: 27 },
					{ word: 'pro', startIndex: 29 },
					{ word: '105', startIndex: 32 },
					{ word: 'gopro105', startIndex: 0 },
					{ word: 'walk500miles', startIndex: 9 },
					{ word: 'gopro105', startIndex: 27 }
				]
			],
			[
				'GoPro105',
				[
					{ word: 'go', startIndex: 0 },
					{ word: 'pro', startIndex: 2 },
					{ word: '105', startIndex: 5 },
					{ word: 'gopro105', startIndex: 0 }
				]
			],
			[
				'GoPro',
				[
					{ word: 'go', startIndex: 0 },
					{ word: 'pro', startIndex: 2 },
					{ word: 'gopro', startIndex: 0 }
				]
			],
			[
				'(GoPro105)',
				[
					{ word: 'go', startIndex: 1 },
					{ word: 'pro', startIndex: 3 },
					{ word: '105', startIndex: 6 },
					{ word: 'gopro105', startIndex: 1 },
					{ word: '(gopro105)', startIndex: 0 },
					{ word: 'gopro105)', startIndex: 1 }
				]
			],
			[
				'walk500Miles',
				[
					{ word: 'walk', startIndex: 0 },
					{ word: '500', startIndex: 4 },
					{ word: 'miles', startIndex: 7 },
					{ word: 'walk500miles', startIndex: 0 }
				]
			],
			[
				'isISO8601',
				[
					{ word: 'is', startIndex: 0 },
					{ word: 'iso', startIndex: 2 },
					{ word: '8601', startIndex: 5 },
					{ word: 'isiso8601', startIndex: 0 }
				]
			],
			[
				'GoPro10GoPro10',
				[
					{ word: 'go', startIndex: 0 },
					{ word: 'pro', startIndex: 2 },
					{ word: '10', startIndex: 5 },
					{ word: 'go', startIndex: 7 },
					{ word: 'pro', startIndex: 9 },
					{ word: '10', startIndex: 12 },
					{ word: 'gopro10gopro10', startIndex: 0 }
				]
			],
			[
				'gopro10gopro10',
				[
					{ word: 'gopro', startIndex: 0 },
					{ word: '10', startIndex: 5 },
					{ word: 'gopro', startIndex: 7 },
					{ word: '10', startIndex: 12 },
					{ word: 'gopro10gopro10', startIndex: 0 }
				]
			],
		];

		tests.forEach(test => {
			const [ phrase, indexes ] = test;
			const index = SearchIndex.createIndex(searchField, phrase);
			assert.deepEqual(index.getIndexes(), indexes, phrase);
		});

	});

	it('Special Chars', () => {
		const searchField = new SearchField({
			name: "title",
			type: "string",
			searchable: true,
			system: true
		});

		const tests = [
			[
				'100+',
				[
					{ word: '100', startIndex: 0 },
					{ word: '100+', startIndex: 0 },
				]
			],
			[
				'[100+]',
				[
					{ word: '100', startIndex: 1 },
					{ word: '[100+]', startIndex: 0 },
					{ word: '100+]', startIndex: 1 },
				]
			],
			[
				'[#100+]',
				[
					{ word: '100', startIndex: 2 },
					{ word: '[#100+]', startIndex: 0 },
					{ word: '#100+]', startIndex: 1 },
					{ word: '100+]', startIndex: 2 },
				]
			],
			[
				'{#100+}',
				[
					{ word: '100', startIndex: 2 },
					{ word: '{#100+}', startIndex: 0 },
					{ word: '#100+}', startIndex: 1 },
					{ word: '100+}', startIndex: 2 },
				]
			],
			[
				'"+100"',
				[
					{ word: '100', startIndex: 2 },
					{ word: '"+100"', startIndex: 0 },
					{ word: '+100"', startIndex: 1 },
					{ word: '100"', startIndex: 2 },
				]
			],
			[
				`'#mytag'`,
				[
					{ word: 'mytag', startIndex: 2 },
					{ word: `'#mytag'`, startIndex: 0 },
					{ word: `#mytag'`, startIndex: 1 },
					{ word: `mytag'`, startIndex: 2 },
				]
			],
			[
				'Red & White',
				[
					{ word: 'red', startIndex: 0 },
					{ word: 'white', startIndex: 6 },
					{ word: '&', startIndex: 4 },
				]
			],
			[
				'Sprint 6 (13.04.2020 - 27.04.2020)',
				[
					{ word: "sprint", startIndex: 0 },
					{ word: "6", startIndex: 7 },
					{ word: "13", startIndex: 10 },
					{ word: "04", startIndex: 13 },
					{ word: "2020", startIndex: 16 },
					{ word: "27", startIndex: 23 },
					{ word: "04", startIndex: 26 },
					{ word: "2020", startIndex: 29 },
					{ word: "(13.04.2020", startIndex: 9 },
					{ word: "13.04.2020", startIndex: 10 },
					{ word: "-", startIndex: 21 },
					{ word: "27.04.2020)", startIndex: 23 }
				]
			],
		];

		tests.forEach(test => {
			const [ phrase, indexes ] = test;
			const index = SearchIndex.createIndex(searchField, phrase);
			assert.deepEqual(index.getIndexes(), indexes, phrase);
		});

	});
});