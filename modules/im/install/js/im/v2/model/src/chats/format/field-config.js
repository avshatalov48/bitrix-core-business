import { Utils } from 'im.v2.lib.utils';
import { Type } from 'main.core';
import { convertToNumber, convertToString, isNumberOrString } from '../../utils/format';
import { formatFieldsWithConfig, type FieldsConfig } from 'im.v2.model';
import {
	prepareAvatar,
	prepareChatName,
	prepareLastMessageViews,
	prepareWritingList,
	prepareManagerList,
	prepareMuteList,
} from './format-functions';

export const chatFieldsConfig: FieldsConfig = [
	{
		fieldName: 'dialogId',
		targetFieldName: 'dialogId',
		checkFunction: isNumberOrString,
		formatFunction: convertToString,
	},
	{
		fieldName: ['id', 'chatId'],
		targetFieldName: 'chatId',
		checkFunction: isNumberOrString,
		formatFunction: convertToNumber,
	},
	{
		fieldName: 'type',
		targetFieldName: 'type',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'quoteId',
		targetFieldName: 'quoteId',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'counter',
		targetFieldName: 'counter',
		checkFunction: isNumberOrString,
		formatFunction: convertToNumber,
	},
	{
		fieldName: 'userCounter',
		targetFieldName: 'userCounter',
		checkFunction: isNumberOrString,
		formatFunction: convertToNumber,
	},
	{
		fieldName: 'lastId',
		targetFieldName: 'lastReadId',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'markedId',
		targetFieldName: 'markedId',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'lastMessageId',
		targetFieldName: 'lastMessageId',
		checkFunction: isNumberOrString,
		formatFunction: convertToNumber,
	},
	{
		fieldName: 'lastMessageViews',
		targetFieldName: 'lastMessageViews',
		checkFunction: Type.isPlainObject,
		formatFunction: prepareLastMessageViews,
	},
	{
		fieldName: 'hasPrevPage',
		targetFieldName: 'hasPrevPage',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'hasNextPage',
		targetFieldName: 'hasNextPage',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'savedPositionMessageId',
		targetFieldName: 'savedPositionMessageId',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: ['title', 'name'],
		targetFieldName: 'name',
		checkFunction: isNumberOrString,
		formatFunction: prepareChatName,
	},
	{
		fieldName: ['owner', 'ownerId'],
		targetFieldName: 'ownerId',
		checkFunction: isNumberOrString,
		formatFunction: convertToNumber,
	},
	{
		fieldName: 'avatar',
		targetFieldName: 'avatar',
		checkFunction: Type.isString,
		formatFunction: prepareAvatar,
	},
	{
		fieldName: 'color',
		targetFieldName: 'color',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'extranet',
		targetFieldName: 'extranet',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'entityLink',
		targetFieldName: 'entityLink',
		checkFunction: Type.isPlainObject,
		formatFunction: (target) => {
			return formatFieldsWithConfig(target, chatEntityFieldsConfig);
		},
	},
	{
		fieldName: 'dateCreate',
		targetFieldName: 'dateCreate',
		formatFunction: Utils.date.cast,
	},
	{
		fieldName: 'public',
		targetFieldName: 'public',
		checkFunction: Type.isPlainObject,
	},
	{
		fieldName: 'writingList',
		targetFieldName: 'writingList',
		checkFunction: Type.isArray,
		formatFunction: prepareWritingList,
	},
	{
		fieldName: 'managerList',
		targetFieldName: 'managerList',
		checkFunction: Type.isArray,
		formatFunction: prepareManagerList,
	},
	{
		fieldName: 'muteList',
		targetFieldName: 'muteList',
		checkFunction: [Type.isArray, Type.isPlainObject],
		formatFunction: prepareMuteList,
	},
	{
		fieldName: 'inited',
		targetFieldName: 'inited',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'loading',
		targetFieldName: 'loading',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'description',
		targetFieldName: 'description',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'diskFolderId',
		targetFieldName: 'diskFolderId',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'role',
		targetFieldName: 'role',
		checkFunction: Type.isString,
		formatFunction: (target: string) => target.toLowerCase(),
	},
	{
		fieldName: 'permissions',
		targetFieldName: 'permissions',
		checkFunction: Type.isPlainObject,
	},
	{
		fieldName: 'tariffRestrictions',
		targetFieldName: 'tariffRestrictions',
		checkFunction: Type.isPlainObject,
	},
	{
		fieldName: 'parentChatId',
		targetFieldName: 'parentChatId',
		checkFunction: Type.isNumber,
	},
];

export const chatEntityFieldsConfig = [
	{
		fieldName: 'type',
		targetFieldName: 'type',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'url',
		targetFieldName: 'url',
		checkFunction: Type.isString,
	},
];
