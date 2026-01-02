import { Type } from 'main.core';
import { convertToNumber, isNumberOrString } from 'im.v2.model';
import type { FieldsConfig } from 'im.v2.model';

export const voteFieldsConfig: FieldsConfig = [
	{
		fieldName: 'voteId',
		targetFieldName: 'id',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'questions',
		targetFieldName: 'questions',
		checkFunction: Type.isObject,
		formatFunction: (target) => {
			return Object.keys(target).map((id) => convertToNumber(id));
		},
	},
	{
		fieldName: 'anonymity',
		targetFieldName: 'isAnonymous',
		checkFunction: Type.isNumber,
		formatFunction: (target) => {
			return target === 2;
		},
	},
	{
		fieldName: 'canEdit',
		targetFieldName: 'canEdit',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'isVoted',
		targetFieldName: 'isVoted',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'canVote',
		targetFieldName: 'canVote',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'canRevote',
		targetFieldName: 'canRevoke',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'isFinished',
		targetFieldName: 'isCompleted',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'resultUrl',
		targetFieldName: 'resultUrl',
		checkFunction: Type.isString,
	},
];

export const questionFieldsConfig: FieldsConfig = [
	{
		fieldName: 'id',
		targetFieldName: 'id',
		checkFunction: Type.isString,
		formatFunction: convertToNumber,
	},
	{
		fieldName: 'answers',
		targetFieldName: 'answers',
		checkFunction: Type.isObject,
		formatFunction: (target) => {
			return Object.keys(target).map((id) => convertToNumber(id));
		},
	},
	{
		fieldName: 'question',
		targetFieldName: 'text',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'fieldType',
		targetFieldName: 'isMultiple',
		checkFunction: Type.isString,
		formatFunction: (target) => target === '1',
	},
	{
		fieldName: 'counter',
		targetFieldName: 'totalCounter',
		checkFunction: isNumberOrString,
		formatFunction: convertToNumber,
	},
];

export const answerFieldsConfig: FieldsConfig = [
	{
		fieldName: 'id',
		targetFieldName: 'id',
		checkFunction: Type.isString,
		formatFunction: convertToNumber,
	},
	{
		fieldName: 'message',
		targetFieldName: 'text',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'counter',
		targetFieldName: 'counter',
		checkFunction: isNumberOrString,
		formatFunction: convertToNumber,
	},
	{
		fieldName: 'percent',
		targetFieldName: 'percent',
		checkFunction: Type.isNumber,
	},
];
