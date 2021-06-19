import {VuexBuilder} from "ui.vue.vuex";
import {NotificationsModel} from 'im.model';

let vuex = null;
let store = null;

before(async () => {
	vuex = await new VuexBuilder()
		.addModel(
			NotificationsModel.create()
				.useDatabase(false)
				.setVariables({
					host: 'http://test.com',
				})
		)
		.build();

	store = vuex.store.state.notifications;
});

afterEach(() => {
	clearStore();
});

describe('Im model: Notifications', () => {
	describe('Initialization', () => {
		it('model is loaded', () => {
			assert(typeof NotificationsModel !== 'undefined');
		});

		it('model is initialized', async () => {
			assert.equal(store.host, 'http://test.com');
			assert.equal(Array.isArray(store.collection), true);
			assert.equal(Array.isArray(store.searchCollection), true);
		});
	});
	describe('getElementState', () => {
		it('returns a object', () => {
			assert.equal(typeof NotificationsModel.prototype.getElementState(), 'object');
		});

		it('returns default element', () => {
			const newElement = NotificationsModel.prototype.getElementState();
			assert.equal(newElement.id, 0);
			assert.equal(newElement.authorId, 0);
			assert.equal(newElement.date instanceof Date, true);
			assert.equal(newElement.text, '');
			assert.equal(newElement.sectionCode, 'notification');
			assert.equal(newElement.textConverted, '');
			assert.equal(newElement.unread, false);
			assert.equal(newElement.template, 'item');
			assert.equal(newElement.templateId, 0);
			assert.equal(newElement.display, true);
			assert.equal(newElement.settingName, 'im|default');
			assert.equal(newElement.type, 0);
		});
	});

	describe('Mutations', () => {
		describe('set', () => {
			it('exists', () => {
				assert.equal(typeof NotificationsModel.prototype.getMutations().set, 'function');
			});

			it('expects array and does nothing if separate item is passed', () => {
				vuex.store.commit('notifications/set', {data: createItem()});

				assert.equal(store.collection, 0);
			});

			it('adds new item in collection for each item in payload', () => {
				const payload = {};
				payload.notification = [
					{id: 1, text: 'First item'},
					{id: 2, text: 'Second item'}
				];

				vuex.store.commit('notifications/set', payload);

				assert.equal(store.collection.length, 2);
				assert.equal(store.collection[0].text, payload.notification[0].text);
				assert.equal(store.collection[1].text, payload.notification[1].text);
			});
		});
	});
});



function createItem(data = {})
{
	return Object.assign(
		NotificationsModel.prototype.getElementState(),
		{
			id: 123,
			authorId: 22,
			date: new Date(),
			text: 'test!',
			sectionCode: 'notification',
			textConverted: 'test!',
			unread: false,
			template: 'item',
			templateId: 0,
			display: true,
			settingName: 'im|default',
			type: 2
		},
		data
	);
}

function clearStore()
{
	store.collection = [];
}