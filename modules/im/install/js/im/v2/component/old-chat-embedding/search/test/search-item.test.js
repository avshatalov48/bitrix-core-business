import {SearchItem} from '../src/search-item';
import {Type} from 'main.core';
import {DumbData} from './dumb-data';

let sandbox = null;
beforeEach(() => {
	sandbox = sinon.createSandbox();
});

afterEach(() => {
	sandbox.restore();
});

describe('SearchItem', () => {
	it('should be a function', () => {
		assert.equal(Type.isFunction(SearchItem), true);
	});

	describe('Item from provider response', () => {
		it('should be created', () => {
			const item = new SearchItem(DumbData.providerData[0]);
			assert.equal(Type.isObjectLike(item), true);
		});
		it('should have correct id', () => {
			const item = new SearchItem(DumbData.providerData[0]);
			assert.equal(item.getId(), 136);
		});
		it('should have correct dialogId', () => {
			const item = new SearchItem(DumbData.providerData[0]);
			assert.equal(item.getDialogId(), 'chat136');
		});
		it('should have correct entityFullId', () => {
			const item = new SearchItem(DumbData.providerData[0]);
			assert.equal(item.getEntityFullId(), 'chat|136');
		});
		it('should be a chat type', () => {
			const item = new SearchItem(DumbData.providerData[0]);
			assert.equal(item.isUser(), false);
			assert.equal(item.isChat(), true);
		});
	});

	describe('Item from model', () => {
		it('should be created', () => {
			const item = new SearchItem(DumbData.chatModelsData[0]);
			assert.equal(Type.isObjectLike(item), true);
		});
		it('should have correct id', () => {
			const item = new SearchItem(DumbData.chatModelsData[0]);
			assert.equal(item.getId(), 25);
		});
		it('should have correct dialogId', () => {
			const item = new SearchItem(DumbData.chatModelsData[0]);
			assert.equal(item.getDialogId(), '25');
		});
		it('should have correct entityFullId', () => {
			const item = new SearchItem(DumbData.chatModelsData[0]);
			assert.equal(item.getEntityFullId(), 'user|25');
		});
		it('should be a user type', () => {
			const item = new SearchItem(DumbData.chatModelsData[0]);
			assert.equal(item.isChat(), false);
			assert.equal(item.isUser(), true);
		});
	});

	describe('isExtranet', () => {
		it('should return false for chat', () => {
			const chatFromProvider = new SearchItem(DumbData.providerData[0]);
			const chatFromModel = new SearchItem(DumbData.chatModelsData[0]);
			assert.equal(chatFromProvider.isExtranet(), false);
			assert.equal(chatFromModel.isExtranet(), false);
		});
		it('should return true for extranet user', () => {
			const item = new SearchItem(DumbData.providerData[6]);
			assert.equal(item.isExtranet(), true);
		});
	});

	describe('isOpeLinesType', () => {
		it('should return false for chat', () => {
			const chatFromProvider = new SearchItem(DumbData.providerData[0]);
			const chatFromModel = new SearchItem(DumbData.chatModelsData[0]);
			assert.equal(chatFromProvider.isOpeLinesType(), false);
			assert.equal(chatFromModel.isOpeLinesType(), false);
		});
		it('should return false for user', () => {
			const chatFromProvider = new SearchItem(DumbData.providerData[0]);
			const chatFromModel = new SearchItem(DumbData.providerData[6]);
			assert.equal(chatFromProvider.isOpeLinesType(), false);
			assert.equal(chatFromModel.isOpeLinesType(), false);
		});
		it('should return true for openlines chat', () => {
			const item = new SearchItem(DumbData.providerData[2]);
			assert.equal(item.isOpeLinesType(), true);
		});
	});

	describe('getOpenlineEntityId', () => {
		it('should return correct id for openlines item', () => {
			const openlineItem = new SearchItem(DumbData.providerData[2]);
			assert.equal(openlineItem.getOpenlineEntityId(), 'livechat');
		});
		it('should return empty string for not openlines item', () => {
			const openlineItem = new SearchItem(DumbData.providerData[0]);
			assert.equal(openlineItem.getOpenlineEntityId(), '');
		});
	});

	describe('getAvatarColor', () => {
		it('should return correct color for item from provider response', () => {
			const firstItem = new SearchItem(DumbData.providerData[0]);
			const secondItem = new SearchItem(DumbData.providerData[1]);
			const thirdItem = new SearchItem(DumbData.providerData[2]);
			assert.equal(firstItem.getAvatarColor(), '#1eb4aa');
			assert.equal(secondItem.getAvatarColor(), '#ab7761');
			assert.equal(thirdItem.getAvatarColor(), '#ba9c7b');
		});
		it('should return correct color for item from model', () => {
			const firstItem = new SearchItem(DumbData.chatModelsData[0]);
			const secondItem = new SearchItem(DumbData.chatModelsData[1]);
			assert.equal(firstItem.getAvatarColor(), '#3e99ce');
			assert.equal(secondItem.getAvatarColor(), '#58cc47');
		});
	});

	describe('isCrmSession', () => {
		it('should return false for not openline chat', () => {
			const firstItem = new SearchItem(DumbData.providerData[0]);
			assert.equal(firstItem.isCrmSession(), false);
		});
		it('should return true for openline chat with CRM binding', () => {
			const firstItem = new SearchItem(DumbData.providerData[2]);
			assert.equal(firstItem.isCrmSession(), true);
		});
		it('should return false for openline chat without CRM binding', () => {
			DumbData.providerData[2].customData.imChat.ENTITY_DATA_1 = 'N|NONE|0|N|N|0|1646297379|0|0|0';
			const firstItem = new SearchItem(DumbData.providerData[2]);
			assert.equal(firstItem.isCrmSession(), false);
		});
		it('should return false for items from model', () => {
			// we don't have OL chats in models for now
			const itemsFromModel = DumbData.chatModelsData.map(chat => {
				return new SearchItem(chat);
			});
			itemsFromModel.forEach(item => {
				assert.equal(item.isCrmSession(), false);
			});
		});
	});
});