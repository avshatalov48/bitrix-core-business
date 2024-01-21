import { convertObjectKeysToCamelCase } from '../src/utils/format';

describe('convertObjectKeysToCamelCase', () => {
	it('should convert snake-case keys for simple objects', () => {
		const input = { snake_case_key: 'value', another_key: 'another value' };
		const expected = { snakeCaseKey: 'value', anotherKey: 'another value' };

		assert.deepStrictEqual(convertObjectKeysToCamelCase(input), expected);
	});

	it('should correctly convert snake-case keys with numbers', () => {
		const input = { snake_case_key_1: 'value', another_key_2: 'another value' };
		const expected = { snakeCaseKey1: 'value', anotherKey2: 'another value' };

		assert.deepStrictEqual(convertObjectKeysToCamelCase(input), expected);
	});

	it('should convert snake-case keys to lower case', () => {
		const input = { snake_case_key: 'value', ANOTHER_KEY: 'another value', camelCaseKey: 'third value' };
		const expected = { snakeCaseKey: 'value', anotherKey: 'another value', camelCaseKey: 'third value' };

		assert.deepStrictEqual(convertObjectKeysToCamelCase(input), expected);
	});

	it('should not change camelCase keys', () => {
		const input = { snake_case_key: 'value', camelCaseKey: 'another value' };
		const expected = { snakeCaseKey: 'value', camelCaseKey: 'another value' };

		assert.deepStrictEqual(convertObjectKeysToCamelCase(input), expected);
	});

	it('should convert keys for nested objects', () => {
		const input = { outer_key: { inner_key: 'value' } };
		const expected = { outerKey: { innerKey: 'value' } };

		assert.deepStrictEqual(convertObjectKeysToCamelCase(input), expected);
	});

	it('should convert keys for nested arrays of objects', () => {
		const input = {
			outer_key: [
				{
					array_key_1: 'array_key_1_value',
					array_key_2: 'array_key_2_value',
				},
				{
					array_key_3: 'array_key_3_value',
					array_key_4: 'array_key_4_value',
				},
			],
		};
		const expected = {
			outerKey: [
				{
					arrayKey1: 'array_key_1_value',
					arrayKey2: 'array_key_2_value',
				},
				{
					arrayKey3: 'array_key_3_value',
					arrayKey4: 'array_key_4_value',
				},
			]
		};

		assert.deepStrictEqual(convertObjectKeysToCamelCase(input), expected);
	});

	it('should not convert keys for nested arrays of primitive values', () => {
		const input = {
			outer_key: ['foo', 123],
		};
		const expected = {
			outerKey: ['foo', 123],
		};

		assert.deepStrictEqual(convertObjectKeysToCamelCase(input), expected);
	});

	it('should handle empty objects', () => {
		const input = {};
		const expected = {};

		assert.deepStrictEqual(convertObjectKeysToCamelCase(input), expected);
	});
});
