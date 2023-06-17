import 'im.v2.test';
import {Core} from 'im.v2.application.core';
import {RecentModel} from 'im.v2.model';

describe('chat counters', () => {
	before(() => {
		return Core.ready();
	});

	let recentModel;
	let INITIAL_COUNTERS;
	let INITIAL_TOTAL_COUNTER;

	beforeEach(() => {
		INITIAL_COUNTERS = {
			1: 1,
			2: 2,
			3: 3,
			4: 4,
			5: 5,
			6: 6,
			7: 7,
			8: 8
		};
		INITIAL_TOTAL_COUNTER = Object.values(INITIAL_COUNTERS).reduce((prev, curr) => {
			return prev + curr;
		}, 0);
		recentModel = Core.getStore().state.recent;

		sinon.stub(Core, 'getUserId').returns(1);
	});

	afterEach(() => {
		recentModel.collection = {};
		recentModel.recentCollection = new Set();
		recentModel.unloadedChatCounters = {};

		Core.getStore().state.dialogues.collection = {};
		Core.getStore().state.dialogues.mutedCollection = new Set();

		sinon.restore();
	});

	describe('initial counters', () => {
		it('should store initial counters', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);

			const {unloadedChatCounters} = recentModel;
			assert.deepEqual(unloadedChatCounters, INITIAL_COUNTERS);
		});

		it('should return total counter for initial counters', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);

			const totalCounter = Core.getStore().getters['recent/getTotalCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER);
		});
	});

	describe('updating after adding new items to recent', () => {
		it('should update state after adding new items to the recent list', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItems = [
				getRecentItem({dialogId: 'chat5', chat_id: 5, counter: 5}),
				getRecentItem({dialogId: 'chat6', chat_id: 6, counter: 6}),
				getRecentItem({dialogId: 'chat7', chat_id: 7, counter: 7}),
				getRecentItem({dialogId: 'chat8', chat_id: 8, counter: 8}),
			];
			await Core.getStore().dispatch('recent/setRecent', newRecentItems);
			await Core.getStore().dispatch('dialogues/set', newRecentItems);

			const expectedUnloadedChatCounters = {
				1: 1,
				2: 2,
				3: 3,
				4: 4
			};

			const {unloadedChatCounters} = recentModel;
			assert.deepEqual(unloadedChatCounters, expectedUnloadedChatCounters);
		});

		it('should keep total counter the same after adding new items to the recent list', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItems = [
				getRecentItem({dialogId: 'chat5', chat_id: 5, counter: 5}),
				getRecentItem({dialogId: 'chat6', chat_id: 6, counter: 6}),
				getRecentItem({dialogId: 'chat7', chat_id: 7, counter: 7}),
				getRecentItem({dialogId: 'chat8', chat_id: 8, counter: 8}),
			];
			await Core.getStore().dispatch('recent/setRecent', newRecentItems);
			await Core.getStore().dispatch('dialogues/set', newRecentItems);

			const totalCounter = Core.getStore().getters['recent/getTotalCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER);
		});

		it('should keep total counter the same after adding new single item to the recent list', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItem = getRecentItem({dialogId: 'chat8', chat_id: 8, counter: 8});
			await Core.getStore().dispatch('recent/setRecent', newRecentItem);
			await Core.getStore().dispatch('dialogues/set', newRecentItem);

			const totalCounter = Core.getStore().getters['recent/getTotalCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER);
		});
	});

	describe('working with muted chats', () => {
		it('should not count muted chats for current user total counter', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItem = getRecentItem({dialogId: 'chat8', chat_id: 8, counter: 8, muteList: [1]});
			await Core.getStore().dispatch('recent/setRecent', newRecentItem);
			await Core.getStore().dispatch('dialogues/set', newRecentItem);

			const totalCounter = Core.getStore().getters['recent/getTotalCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER - 8);
		});

		it('should decrement total counter after muting the chat', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItem = getRecentItem({dialogId: 'chat8', chat_id: 8, counter: 8});
			await Core.getStore().dispatch('recent/setRecent', newRecentItem);
			await Core.getStore().dispatch('dialogues/set', newRecentItem);

			const totalCounterBeforeMute = Core.getStore().getters['recent/getTotalCounter'];
			assert.equal(totalCounterBeforeMute, INITIAL_TOTAL_COUNTER, 'FIRST');

			await Core.getStore().dispatch('dialogues/mute', {dialogId: 'chat8'});
			const totalCounterAfterMute = Core.getStore().getters['recent/getTotalCounter'];
			assert.equal(totalCounterAfterMute, INITIAL_TOTAL_COUNTER - 8, 'SECOND');
		});

		it('should increment total counter after unmuting the chat', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItem = getRecentItem({dialogId: 'chat8', chat_id: 8, counter: 8, muteList: [1]});
			await Core.getStore().dispatch('recent/setRecent', newRecentItem);
			await Core.getStore().dispatch('dialogues/set', newRecentItem);

			const totalCounterBeforeUnmute = Core.getStore().getters['recent/getTotalCounter'];
			assert.equal(totalCounterBeforeUnmute, INITIAL_TOTAL_COUNTER - 8);

			await Core.getStore().dispatch('dialogues/unmute', {dialogId: 'chat8'});
			const totalCounterAfterUnmute = Core.getStore().getters['recent/getTotalCounter'];
			assert.equal(totalCounterAfterUnmute, INITIAL_TOTAL_COUNTER);
		});
	});

	describe('working with marked chats', () => {
		it('should add 1 to total counter for unmuted marked chat without counter', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItems = [
				getRecentItem({dialogId: 'chat9', chat_id: 9, counter: 0, unread: true}),
				getRecentItem({dialogId: 'chat10', chat_id: 10, counter: 0, unread: true})
			];
			await Core.getStore().dispatch('recent/setRecent', newRecentItems);
			await Core.getStore().dispatch('dialogues/set', newRecentItems);

			const totalCounter = Core.getStore().getters['recent/getTotalCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER + 2);
		});

		it('should not add 1 to total counter for unmuted marked chat with counter', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItems = [
				getRecentItem({dialogId: 'chat7', chat_id: 7, counter: 7, unread: true}),
				getRecentItem({dialogId: 'chat8', chat_id: 8, counter: 8, unread: true})
			];
			await Core.getStore().dispatch('recent/setRecent', newRecentItems);
			await Core.getStore().dispatch('dialogues/set', newRecentItems);

			const totalCounter = Core.getStore().getters['recent/getTotalCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER);
		});

		it('should not add 1 to total counter for muted marked chat without counter', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItems = [
				getRecentItem({dialogId: 'chat9', chat_id: 9, counter: 0, unread: true, muteList: [1]}),
				getRecentItem({dialogId: 'chat10', chat_id: 10, counter: 0, unread: true, muteList: [1]})
			];
			await Core.getStore().dispatch('recent/setRecent', newRecentItems);
			await Core.getStore().dispatch('dialogues/set', newRecentItems);

			const totalCounter = Core.getStore().getters['recent/getTotalCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER);
		});

		it('should not add 1 to total counter for muted marked chat with counter', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItems = [
				getRecentItem({dialogId: 'chat9', chat_id: 9, counter: 9, unread: true, muteList: [1]}),
				getRecentItem({dialogId: 'chat10', chat_id: 10, counter: 10, unread: true, muteList: [1]})
			];
			await Core.getStore().dispatch('recent/setRecent', newRecentItems);
			await Core.getStore().dispatch('dialogues/set', newRecentItems);

			const totalCounter = Core.getStore().getters['recent/getTotalCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER);
		});
	});

	describe('updating single counters for existing chats', () => {
		it('should not update state after updating counter for existing chat', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItems = [
				getRecentItem({dialogId: 'chat5', chat_id: 5, counter: 5}),
				getRecentItem({dialogId: 'chat6', chat_id: 6, counter: 6}),
				getRecentItem({dialogId: 'chat7', chat_id: 7, counter: 7}),
				getRecentItem({dialogId: 'chat8', chat_id: 8, counter: 8}),
			];
			await Core.getStore().dispatch('recent/setRecent', newRecentItems);
			await Core.getStore().dispatch('dialogues/set', newRecentItems);
			await Core.getStore().dispatch('dialogues/update', {
				dialogId: 'chat7',
				fields: {
					counter: 8
				}
			});

			const expectedUnloadedChatCounters = {
				1: 1,
				2: 2,
				3: 3,
				4: 4
			};

			const {unloadedChatCounters} = recentModel;
			assert.deepEqual(unloadedChatCounters, expectedUnloadedChatCounters);
		});

		it('should update total counter after updating counter for existing chat', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItems = [
				getRecentItem({dialogId: 'chat5', chat_id: 5, counter: 5}),
				getRecentItem({dialogId: 'chat6', chat_id: 6, counter: 6}),
				getRecentItem({dialogId: 'chat7', chat_id: 7, counter: 7}),
				getRecentItem({dialogId: 'chat8', chat_id: 8, counter: 8}),
			];
			await Core.getStore().dispatch('recent/setRecent', newRecentItems);
			await Core.getStore().dispatch('dialogues/set', newRecentItems);
			await Core.getStore().dispatch('dialogues/update', {
				dialogId: 'chat7',
				fields: {
					counter: 8
				}
			});

			const totalCounter = Core.getStore().getters['recent/getTotalCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER + 1);
		});
	});

	describe('updating single counters for unloaded chats', () => {
		it('should update state after updating counter for unloaded chat', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);
			const newCounter = {5: 6};
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', newCounter);
			const expectedUnloadedChatCounters = {...INITIAL_COUNTERS, 5: 6};

			const {unloadedChatCounters} = recentModel;
			assert.deepEqual(unloadedChatCounters, expectedUnloadedChatCounters);
		});

		it('should update total counter after updating counter for unloaded chat', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);
			const newCounter = {5: 6};
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', newCounter);

			const totalCounter = Core.getStore().getters['recent/getTotalCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER + 1);
		});
	});

	describe.skip('adding counters after deleting item from recent', () => {
		it('should update state after deleting item from recent', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItem = getRecentItem({dialogId: 'chat8', chat_id: 8, counter: 8});
			await Core.getStore().dispatch('recent/setRecent', newRecentItem);
			await Core.getStore().dispatch('dialogues/set', newRecentItem);

			await Core.getStore().dispatch('recent/delete', {id: 'chat8'});

			const {unloadedChatCounters} = recentModel;
			assert.deepEqual(unloadedChatCounters, INITIAL_COUNTERS);
		});

		it('should keep total counter the same after deleting item from recent', async () => {
			await Core.getStore().dispatch('recent/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItem = getRecentItem({dialogId: 'chat8', chat_id: 8, counter: 8});
			await Core.getStore().dispatch('recent/setRecent', newRecentItem);
			await Core.getStore().dispatch('dialogues/set', newRecentItem);

			await Core.getStore().dispatch('recent/delete', {id: 'chat8'});
			const totalCounter = Core.getStore().getters['recent/getTotalCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER);
		});
	});
});

function getRecentItem(params = {})
{
	return {...RecentModel.prototype.getElementState(), ...params};
}