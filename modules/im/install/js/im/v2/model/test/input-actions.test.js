import 'im.v2.test';
import { Core } from 'im.v2.application.core';
import { InputAction } from 'im.v2.lib.input-action';

import type { Store } from 'ui.vue3.vuex';
import type { InputActionState } from 'im.v2.model';

describe('input actions model', () => {
	let store: Store = null;
	let state: InputActionState = null;

	before(async () => {
		await Core.ready();
		store = Core.getStore();
	});

	beforeEach(() => {
		state = store.state.chats.inputActions;
	});

	afterEach(async () => {
		state.collection = {};
	});

	it('should have initial empty state', () => {
		assert.deepEqual(state, { collection: {} });
	});

	describe('actions', () => {
		describe('start', () => {
			it('should create structure for non-existing dialogId', () => {
				const payload = {
					type: InputAction.writing,
					dialogId: 'chat1',
					userId: 1,
					userName: 'User1',
				};

				store.dispatch('chats/inputActions/start', payload);

				assert.equal(Boolean(state.collection[payload.dialogId]), true);
			});

			it('should save new user record', () => {
				const payload = {
					type: InputAction.writing,
					dialogId: 'chat1',
					userId: 1,
					userName: 'User1',
				};

				store.dispatch('chats/inputActions/start', payload);

				const userRecord = state.collection[payload.dialogId][0];
				assert.deepEqual(userRecord, { type: InputAction.writing, userId: 1, userName: 'User1' });
			});

			it('should correctly save user records for different types', () => {
				const payload1 = {
					type: InputAction.writing,
					dialogId: 'chat1',
					userId: 1,
					userName: 'User1',
				};
				const payload2 = {
					type: InputAction.sendingFile,
					dialogId: 'chat1',
					userId: 2,
					userName: 'User2',
				};

				store.dispatch('chats/inputActions/start', payload1);
				store.dispatch('chats/inputActions/start', payload2);

				const userRecords = state.collection[payload1.dialogId];
				assert.deepEqual(userRecords[0], { type: payload1.type, userId: payload1.userId, userName: payload1.userName });
				assert.deepEqual(userRecords[1], { type: payload2.type, userId: payload2.userId, userName: payload2.userName });
			});

			it('should correctly save user records for different chats, types and users', () => {
				const payload1 = {
					type: InputAction.writing,
					dialogId: 'chat1',
					userId: 1,
					userName: 'User1',
				};
				const payload2 = {
					type: InputAction.sendingFile,
					dialogId: 'chat1',
					userId: 2,
					userName: 'User2',
				};
				const payload3 = {
					type: InputAction.writing,
					dialogId: 'chat2',
					userId: 1,
					userName: 'User1',
				};

				store.dispatch('chats/inputActions/start', payload1);
				store.dispatch('chats/inputActions/start', payload2);
				store.dispatch('chats/inputActions/start', payload3);

				const chatList1 = state.collection[payload1.dialogId];
				const chatList2 = state.collection[payload3.dialogId];
				assert.deepEqual(chatList1[0], { type: payload1.type, userId: payload1.userId, userName: payload1.userName });
				assert.deepEqual(chatList1[1], { type: payload2.type, userId: payload2.userId, userName: payload2.userName });
				assert.deepEqual(chatList2[0], { type: payload3.type, userId: payload3.userId, userName: payload3.userName });
			});

			it('should not save user record if it exists already', () => {
				const payload = {
					type: InputAction.writing,
					dialogId: 'chat1',
					userId: 1,
					userName: 'User1',
				};

				store.dispatch('chats/inputActions/start', payload);
				store.dispatch('chats/inputActions/start', payload);

				const chatList = state.collection[payload.dialogId];
				assert.equal(chatList.length, 1);
			});
		});

		describe('stop', () => {
			it('should remove user record', () => {
				const payload1 = {
					type: InputAction.writing,
					dialogId: 'chat1',
					userId: 1,
					userName: 'User1',
				};
				const payload2 = {
					type: InputAction.writing,
					dialogId: 'chat1',
					userId: 2,
					userName: 'User2',
				};

				store.dispatch('chats/inputActions/start', payload1);
				store.dispatch('chats/inputActions/start', payload2);
				store.dispatch('chats/inputActions/stop', payload1);

				const chatList = state.collection[payload1.dialogId];
				assert.equal(chatList.length, 1);
			});
		});
	});

	describe('getters', () => {
		describe('isChatActive', () => {
			it('should return false if there is no collection for provided chat', () => {
				const result = store.getters['chats/inputActions/isChatActive']({
					dialogId: 'chat1',
				});
				assert.equal(result, false);
			});

			it('should return true if there is any action for provided chat', () => {
				const payload = {
					type: InputAction.writing,
					dialogId: 'chat1',
					userId: 1,
					userName: 'User1',
				};
				store.dispatch('chats/inputActions/start', payload);

				const result = store.getters['chats/inputActions/isChatActive'](payload.dialogId);
				assert.equal(result, true);
			});
		});
		describe('isActionActive', () => {
			it('should return false if there is no collection for provided chat', () => {
				const result = store.getters['chats/inputActions/isActionActive']({
					dialogId: 'chat1',
					type: InputAction.writing,
					userId: 1,
				});
				assert.equal(result, false);
			});

			it('should return true if there is user record for provided payload', () => {
				const payload = {
					type: InputAction.writing,
					dialogId: 'chat1',
					userId: 1,
					userName: 'User1',
				};
				store.dispatch('chats/inputActions/start', payload);

				const result = store.getters['chats/inputActions/isActionActive']({
					dialogId: 'chat1',
					type: InputAction.writing,
					userId: 1,
				});
				assert.equal(result, true);
			});
		});
	});
});
