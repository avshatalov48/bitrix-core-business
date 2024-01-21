import Http from '../../../src/lib/http';

describe('core/http/data', () => {
	it('Should exported as function', () => {
		assert.ok(typeof Http.Data === 'function', 'Http.Data is not a function');
	});

	describe('#convertObjectToFormData', () => {
		it('Should convert object to form data', () => {
			const now = new Date();
			const file = new File(['foo'], 'foo.txt', {
				type: 'text/plain',
			});

			const source = {
				prop1: {
					key1: 'key1Value',
					key2: {
						subKey1: 'subKey1Value',
						subKey2: 'subKey2Value',
					},
					key3: [1, 2, 3],
					key4: now,
					key5: file,
				},
				prop2: [
					1,
					{
						key1: 'key1Value',
						key2: 'key2Value',
					},
					[
						1,
						2,
					]
				],
				prop3: now,
				prop4: file,
				prop6: [
					now,
					file,
				]
			};

			const resultEntries = [
				['prop1[key1]', 'key1Value'],
				['prop1[key2][subKey1]', 'subKey1Value'],
				['prop1[key2][subKey2]', 'subKey2Value'],
				['prop1[key3][0]', '1'],
				['prop1[key3][1]', '2'],
				['prop1[key3][2]', '3'],
				['prop1[key4]', now.toISOString()],
				['prop1[key5]', file],
				['prop2[0]', '1'],
				['prop2[1][key1]', 'key1Value'],
				['prop2[1][key2]', 'key2Value'],
				['prop2[2][0]', '1'],
				['prop2[2][1]', '2'],
				['prop3', now.toISOString()],
				['prop4', file],
				['prop6[0]', now.toISOString()],
				['prop6[1]', file],
			];

			const fd = Http.Data.convertObjectToFormData(source);
			const entries = [...fd];

			assert.deepEqual(entries, resultEntries);

		});

		it('Should works with property[value][] (BUG: 177189)', () => {
			const source = {
				'prop[value][]': [1, 2, 3],
			};

			const fd = Http.Data.convertObjectToFormData(source);
			const fdEntries = [...fd.entries()];

			const [key1, value1] = fdEntries.at(0);
			assert.ok(key1 === 'prop[value][0]');
			assert.ok(value1 === '1');

			const [key2, value2] = fdEntries.at(1);
			assert.ok(key2 === 'prop[value][1]');
			assert.ok(value2 === '2');

			const [key3, value3] = fdEntries.at(2);
			assert.ok(key3 === 'prop[value][2]');
			assert.ok(value3 === '3');
		});
	});
});
