import 'im.v2.test';
import { Loc } from 'main.core';
import { Core } from 'im.v2.application.core';
import { RecentModel } from 'im.v2.model';

import type { Store } from 'ui.vue3.vuex';

const INITIAL_COUNTERS = {
	1: 1,
	2: 2,
	3: 3,
	4: 4,
	5: 5,
	6: 6,
	7: 7,
	8: 8,
};
const INITIAL_TOTAL_COUNTER = Object.values(INITIAL_COUNTERS).reduce((prev, curr) => {
	return prev + curr;
}, 0);

describe.only('chat counters', () => {
	let store: Store = null;

	before(async () => {
		await Core.ready();
		store = Core.getStore();
	});

	beforeEach(() => {
		sinon.stub(Core, 'getUserId').returns(1);
		sinon.stub(Loc, 'getMessage').returns('');
	});

	afterEach(() => {
		Core.storeBuilder.clearModelState();
		sinon.restore();
	});

	describe('initial counters', () => {
		it('should store initial counters', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);

			const unloadedChatCounters = store.getters['counters/getUnloadedChatCounters'];
			assert.deepEqual(unloadedChatCounters, INITIAL_COUNTERS);
		});

		it('should return total counter for initial counters', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);

			const totalCounter = store.getters['counters/getTotalChatCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER);
		});
	});

	describe('updating after adding new items to recent', () => {
		it('should update state after adding new items to the recent list', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItems = [
				getRecentItem({ dialogId: 'chat5', chat_id: 5, counter: 5 }),
				getRecentItem({ dialogId: 'chat6', chat_id: 6, counter: 6 }),
				getRecentItem({ dialogId: 'chat7', chat_id: 7, counter: 7 }),
				getRecentItem({ dialogId: 'chat8', chat_id: 8, counter: 8 }),
			];
			await store.dispatch('recent/setRecent', newRecentItems);
			await store.dispatch('chats/set', newRecentItems);

			const expectedUnloadedChatCounters = {
				1: 1,
				2: 2,
				3: 3,
				4: 4,
			};

			const unloadedChatCounters = store.getters['counters/getUnloadedChatCounters'];
			assert.deepEqual(unloadedChatCounters, expectedUnloadedChatCounters);
		});

		it('should keep total counter the same after adding new items to the recent list', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItems = [
				getRecentItem({ dialogId: 'chat5', chat_id: 5, counter: 5 }),
				getRecentItem({ dialogId: 'chat6', chat_id: 6, counter: 6 }),
				getRecentItem({ dialogId: 'chat7', chat_id: 7, counter: 7 }),
				getRecentItem({ dialogId: 'chat8', chat_id: 8, counter: 8 }),
			];
			await store.dispatch('recent/setRecent', newRecentItems);
			await store.dispatch('chats/set', newRecentItems);

			const totalCounter = store.getters['counters/getTotalChatCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER);
		});

		it('should keep total counter the same after adding new single item to the recent list', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItem = getRecentItem({ dialogId: 'chat8', chat_id: 8, counter: 8 });
			await store.dispatch('recent/setRecent', newRecentItem);
			await store.dispatch('chats/set', newRecentItem);

			const totalCounter = store.getters['counters/getTotalChatCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER);
		});
	});

	describe('working with muted chats', () => {
		it('should not count muted chats for current user total counter', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItem = getRecentItem({ dialogId: 'chat8', chat_id: 8, counter: 8, muteList: [1] });
			await store.dispatch('recent/setRecent', newRecentItem);
			await store.dispatch('chats/set', newRecentItem);

			const totalCounter = store.getters['counters/getTotalChatCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER - 8);
		});

		it('should decrement total counter after muting the chat', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItem = getRecentItem({ dialogId: 'chat8', chat_id: 8, counter: 8 });
			await store.dispatch('recent/setRecent', newRecentItem);
			await store.dispatch('chats/set', newRecentItem);

			const totalCounterBeforeMute = store.getters['counters/getTotalChatCounter'];
			assert.equal(totalCounterBeforeMute, INITIAL_TOTAL_COUNTER, 'FIRST');

			await store.dispatch('chats/mute', { dialogId: 'chat8' });
			const totalCounterAfterMute = store.getters['counters/getTotalChatCounter'];
			assert.equal(totalCounterAfterMute, INITIAL_TOTAL_COUNTER - 8, 'SECOND');
		});

		it('should increment total counter after unmuting the chat', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItem = getRecentItem({ dialogId: 'chat8', chat_id: 8, counter: 8, muteList: [1] });
			await store.dispatch('recent/setRecent', newRecentItem);
			await store.dispatch('chats/set', newRecentItem);

			const totalCounterBeforeUnmute = store.getters['counters/getTotalChatCounter'];
			assert.equal(totalCounterBeforeUnmute, INITIAL_TOTAL_COUNTER - 8);

			await store.dispatch('chats/unmute', { dialogId: 'chat8' });
			const totalCounterAfterUnmute = store.getters['counters/getTotalChatCounter'];
			assert.equal(totalCounterAfterUnmute, INITIAL_TOTAL_COUNTER);
		});
	});

	describe('working with marked chats', () => {
		it('should add 1 to total counter for unmuted marked chat without counter', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItems = [
				getRecentItem({ dialogId: 'chat9', chat_id: 9, counter: 0, unread: true }),
				getRecentItem({ dialogId: 'chat10', chat_id: 10, counter: 0, unread: true }),
			];
			await store.dispatch('recent/setRecent', newRecentItems);
			await store.dispatch('chats/set', newRecentItems);

			const totalCounter = store.getters['counters/getTotalChatCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER + 2);
		});

		it('should not add 1 to total counter for unmuted marked chat with counter', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItems = [
				getRecentItem({dialogId: 'chat7', chat_id: 7, counter: 7, unread: true}),
				getRecentItem({dialogId: 'chat8', chat_id: 8, counter: 8, unread: true})
			];
			await store.dispatch('recent/setRecent', newRecentItems);
			await store.dispatch('chats/set', newRecentItems);

			const totalCounter = store.getters['counters/getTotalChatCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER);
		});

		it('should not add 1 to total counter for muted marked chat without counter', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItems = [
				getRecentItem({dialogId: 'chat9', chat_id: 9, counter: 0, unread: true, muteList: [1]}),
				getRecentItem({dialogId: 'chat10', chat_id: 10, counter: 0, unread: true, muteList: [1]})
			];
			await store.dispatch('recent/setRecent', newRecentItems);
			await store.dispatch('chats/set', newRecentItems);

			const totalCounter = store.getters['counters/getTotalChatCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER);
		});

		it('should not add 1 to total counter for muted marked chat with counter', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItems = [
				getRecentItem({dialogId: 'chat9', chat_id: 9, counter: 9, unread: true, muteList: [1]}),
				getRecentItem({dialogId: 'chat10', chat_id: 10, counter: 10, unread: true, muteList: [1]})
			];
			await store.dispatch('recent/setRecent', newRecentItems);
			await store.dispatch('chats/set', newRecentItems);

			const totalCounter = store.getters['counters/getTotalChatCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER);
		});
	});

	describe('updating single counters for existing chats', () => {
		it('should not update state after updating counter for existing chat', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItems = [
				getRecentItem({ dialogId: 'chat5', chat_id: 5, counter: 5 }),
				getRecentItem({ dialogId: 'chat6', chat_id: 6, counter: 6 }),
				getRecentItem({ dialogId: 'chat7', chat_id: 7, counter: 7 }),
				getRecentItem({ dialogId: 'chat8', chat_id: 8, counter: 8 }),
			];
			await store.dispatch('recent/setRecent', newRecentItems);
			await store.dispatch('chats/set', newRecentItems);
			await store.dispatch('chats/update', {
				dialogId: 'chat7',
				fields: { counter: 8 },
			});

			const expectedUnloadedChatCounters = {
				1: 1,
				2: 2,
				3: 3,
				4: 4,
			};

			const unloadedChatCounters = store.getters['counters/getUnloadedChatCounters'];
			assert.deepEqual(unloadedChatCounters, expectedUnloadedChatCounters);
		});

		it('should update total counter after updating counter for existing chat', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItems = [
				getRecentItem({ dialogId: 'chat5', chat_id: 5, counter: 5 }),
				getRecentItem({ dialogId: 'chat6', chat_id: 6, counter: 6 }),
				getRecentItem({ dialogId: 'chat7', chat_id: 7, counter: 7 }),
				getRecentItem({ dialogId: 'chat8', chat_id: 8, counter: 8 }),
			];
			await store.dispatch('recent/setRecent', newRecentItems);
			await store.dispatch('chats/set', newRecentItems);
			await store.dispatch('chats/update', {
				dialogId: 'chat7',
				fields: { counter: 8 },
			});

			const totalCounter = store.getters['counters/getTotalChatCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER + 1);
		});
	});

	describe('updating single counters for unloaded chats', () => {
		it('should update state after updating counter for unloaded chat', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);
			const newCounter = { 5: 6 };
			await store.dispatch('counters/setUnloadedChatCounters', newCounter);
			const expectedUnloadedChatCounters = { ...INITIAL_COUNTERS, 5: 6 };

			const unloadedChatCounters = store.getters['counters/getUnloadedChatCounters'];
			assert.deepEqual(unloadedChatCounters, expectedUnloadedChatCounters);
		});

		it('should update total counter after updating counter for unloaded chat', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);
			const newCounter = { 5: 6 };
			await store.dispatch('counters/setUnloadedChatCounters', newCounter);

			const totalCounter = store.getters['counters/getTotalChatCounter'];
			assert.equal(totalCounter, INITIAL_TOTAL_COUNTER + 1);
		});
	});

	describe('adding counters after deleting item from recent', () => {
		it('should update state after deleting item from recent', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItem = getRecentItem({ dialogId: 'chat9', chat_id: 9, counter: 9 });
			await store.dispatch('recent/setRecent', newRecentItem);
			await store.dispatch('chats/set', newRecentItem);

			await store.dispatch('recent/delete', { id: 'chat9' });

			const unloadedChatCounters = store.getters['counters/getUnloadedChatCounters'];
			assert.deepEqual(unloadedChatCounters, INITIAL_COUNTERS);
		});

		it('should reduce total counter after deleting item from recent', async () => {
			await store.dispatch('counters/setUnloadedChatCounters', INITIAL_COUNTERS);

			const newRecentItem = getRecentItem({ dialogId: 'chat9', chat_id: 9, counter: 9 });
			await store.dispatch('recent/setRecent', newRecentItem);
			await store.dispatch('chats/set', newRecentItem);

			const totalCounterBefore = store.getters['counters/getTotalChatCounter'];
			assert.equal(totalCounterBefore, INITIAL_TOTAL_COUNTER + 9);

			await store.dispatch('recent/delete', { id: 'chat9' });
			const totalCounterAfter = store.getters['counters/getTotalChatCounter'];
			assert.equal(totalCounterAfter, INITIAL_TOTAL_COUNTER);
		});
	});
});

function getRecentItem(params = {})
{
	return { ...RecentModel.prototype.getElementState(), ...params };
}
