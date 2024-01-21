import { Type } from 'main.core';

import { formatFieldsWithConfig, type FieldsConfig } from '../src/utils/validate';

describe('formatFieldsWithConfig', () => {
	it('should extract provided field to target field', () => {
		const config: FieldsConfig = [
			{
				fieldName: 'initialFieldName',
				targetFieldName: 'targetFieldName',
			},
		];

		const input = {
			initialFieldName: 'initialFieldValue',
		};

		const expected = {
			targetFieldName: 'initialFieldValue',
		};

		assert.deepEqual(formatFieldsWithConfig(input, config), expected);
	});

	it('should not extract fields that are not listed in config', () => {
		const config: FieldsConfig = [
			{
				fieldName: 'initialFieldName',
				targetFieldName: 'targetFieldName',
			},
		];

		const input = {
			randomFieldName: 'initialFieldValue',
		};

		const expected = {};

		assert.deepEqual(formatFieldsWithConfig(input, config), expected);
	});

	it('should not extract fields with undefined value', () => {
		const config: FieldsConfig = [
			{
				fieldName: 'initialFieldName',
				targetFieldName: 'targetFieldName',
			},
		];

		const input = {
			initialFieldName: undefined,
		};

		const expected = {};

		assert.deepEqual(formatFieldsWithConfig(input, config), expected);
	});

	it('should accept array of strings for field name to extract', () => {
		const config: FieldsConfig = [
			{
				fieldName: ['initialFieldName', 'anotherFieldNameOption'],
				targetFieldName: 'targetFieldName',
			},
		];

		const input = {
			anotherFieldNameOption: 'initialFieldValue',
		};

		const expected = {
			targetFieldName: 'initialFieldValue',
		};

		assert.deepEqual(formatFieldsWithConfig(input, config), expected);
	});

	it('should extract field if its value passes validation function', () => {
		const config: FieldsConfig = [
			{
				fieldName: 'initialFieldName',
				targetFieldName: 'targetFieldName',
				checkFunction: Type.isBoolean,
			},
		];

		const input = {
			initialFieldName: true,
		};

		const expected = {
			targetFieldName: true,
		};

		assert.deepEqual(formatFieldsWithConfig(input, config), expected);
	});

	it('should not extract field if its value fails validation function', () => {
		const config: FieldsConfig = [
			{
				fieldName: 'initialFieldName',
				targetFieldName: 'targetFieldName',
				checkFunction: Type.isBoolean,
			},
		];

		const input = {
			initialFieldName: 'initialFieldValue',
		};

		const expected = {};

		assert.deepEqual(formatFieldsWithConfig(input, config), expected);
	});

	it('should extract field if its value passes at least one of provided validation functions', () => {
		const config: FieldsConfig = [
			{
				fieldName: 'initialFieldName',
				targetFieldName: 'targetFieldName',
				checkFunction: [Type.isString, Type.isBoolean],
			},
		];

		const input = {
			initialFieldName: true,
		};

		const expected = {
			targetFieldName: true,
		};

		assert.deepEqual(formatFieldsWithConfig(input, config), expected);
	});

	it('should format field value with provided format function', () => {
		const config: FieldsConfig = [
			{
				fieldName: 'initialFieldName',
				targetFieldName: 'targetFieldName',
				formatFunction: (target) => target.toString(),
			},
		];

		const input = {
			initialFieldName: 5,
		};

		const expected = {
			targetFieldName: '5',
		};

		assert.deepEqual(formatFieldsWithConfig(input, config), expected);
	});

	it('should provide already formatted values to format function', () => {
		const config: FieldsConfig = [
			{
				fieldName: 'initialFieldName1',
				targetFieldName: 'targetFieldName1',
			},
			{
				fieldName: 'initialFieldName2',
				targetFieldName: 'targetFieldName2',
				formatFunction(target, currentResult) {
					if (currentResult.targetFieldName1 === 5)
					{
						return 0;
					}

					return target;
				},
			},
		];

		const input = {
			initialFieldName1: 5,
			initialFieldName2: 10,
		};

		const expected = {
			targetFieldName1: 5,
			targetFieldName2: 0,
		};

		assert.deepEqual(formatFieldsWithConfig(input, config), expected);
	});

	it('should provide raw fields values to format function', () => {
		const config: FieldsConfig = [
			{
				fieldName: 'initialFieldName1',
				targetFieldName: 'targetFieldName1',
				formatFunction: (target) => target + 1,
			},
			{
				fieldName: 'initialFieldName2',
				targetFieldName: 'targetFieldName2',
				formatFunction(target, currentResult, rawFields) {
					if (rawFields.initialFieldName1 === 5)
					{
						return 0;
					}

					return target;
				},
			},
		];

		const input = {
			initialFieldName1: 5,
			initialFieldName2: 10,
		};

		const expected = {
			targetFieldName1: 6,
			targetFieldName2: 0,
		};

		assert.deepEqual(formatFieldsWithConfig(input, config), expected);
	});
});
