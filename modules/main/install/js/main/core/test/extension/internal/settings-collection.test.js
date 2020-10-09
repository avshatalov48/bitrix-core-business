import SettingsCollection from '../../../src/lib/extension/internal/settings-collection';

describe('main.core/extension/internal/settings-collection', () => {
	it('Should created from object', () => {
		const sourceObject = {
			param1: {
				childParam1: 1,
				childParam2: [1, 2, 3],
			},
			param2: 'Param 2 value',
		};

		assert.deepEqual(
			new SettingsCollection(sourceObject),
			sourceObject,
		);
	});

	describe('#get', () => {
		it('Should return all options if method calls without params', () => {
			const sourceObject = {
				param1: {
					childParam1: 1,
					childParam2: [1, 2, 3],
				},
				param2: 'Param 2 value',
			};

			const settingsCollection = new SettingsCollection(sourceObject);

			assert.deepEqual(
				settingsCollection.get(),
				sourceObject,
			);
		});

		it('Should return option value by path', () => {
			const sourceObject = {
				param1: {
					childParam1: 1,
					childParam2: [1, 2, 3],
				},
				param2: 'Param 2 value',
			};

			const settingsCollection = new SettingsCollection(sourceObject);

			assert.deepEqual(
				settingsCollection.get('param1.childParam1'),
				1,
			);

			assert.deepEqual(
				settingsCollection.get('param1.childParam2'),
				[1, 2, 3],
			);

			assert.deepEqual(
				settingsCollection.get('param1.childParam2[0]'),
				1,
			);

			assert.deepEqual(
				settingsCollection.get('param1.childParam2[1]'),
				2,
			);

			assert.deepEqual(
				settingsCollection.get('param1.childParam2[2]'),
				3,
			);

			assert.deepEqual(
				settingsCollection.get('param2'),
				'Param 2 value',
			);
		});

		it('Should return default value if path not resolved', () => {
			const sourceObject = {
				param1: {
					childParam1: 1,
					childParam2: [1, 2, 3],
				},
				param2: 'Param 2 value',
			};

			const settingsCollection = new SettingsCollection(sourceObject);

			assert.deepEqual(
				settingsCollection.get('param1.childParam1.param2'),
				null,
			);

			assert.deepEqual(
				settingsCollection.get('param1.childParam1.param2', false),
				false,
			);

			assert.deepEqual(
				settingsCollection.get('param4'),
				null,
			);

			assert.deepEqual(
				settingsCollection.get('param4', 222),
				222,
			);
		});
	});
});