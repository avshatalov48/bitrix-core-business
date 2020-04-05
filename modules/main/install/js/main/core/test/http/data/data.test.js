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
			const blob = new Blob(['foo'], {
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
					key6: blob,
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
				prop5: blob,
				prop6: [
					now,
					file,
					blob,
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
				['prop1[key6]', blob],
				['prop2[0]', '1'],
				['prop2[1][key1]', 'key1Value'],
				['prop2[1][key2]', 'key2Value'],
				['prop2[2][0]', '1'],
				['prop2[2][1]', '2'],
				['prop3', now.toISOString()],
				['prop4', file],
				['prop5', blob],
				['prop6[0]', now.toISOString()],
				['prop6[1]', file],
				['prop6[2]', blob],
			];

			const fd = Http.Data.convertObjectToFormData(source);
			const entries = [...fd];

			assert.deepEqual(entries, resultEntries);

		});
	});
});