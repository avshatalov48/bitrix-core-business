import {VuexBuilder} from "ui.vue.vuex";
import {RecentModel} from '../src/recent';

let vuex = null;
let store = null;

before(async () => {

	vuex = await new VuexBuilder()
		.addModel(
			RecentModel.create()
				.useDatabase(false)
				.setVariables({
					host: 'http://test.com',
				})
		)
		.build();

	store = vuex.store.state.recent;
});

afterEach(() => {
	clearStore();
});

describe('Im model: Recent', () => {
	describe('Initialization', () => {
		it('model is loaded', () => {
			assert(typeof RecentModel !== 'undefined');
		});

		it('model is initialized', async () => {
			assert.equal(store.host, 'http://test.com');
			assert.equal(Array.isArray(store.collection.general), true);
			assert.equal(Array.isArray(store.collection.pinned), true);
		});
	});

	describe('getElementState', () => {
		it('returns a object', () => {
			assert.equal(typeof RecentModel.prototype.getElementState(), 'object');
		});

		it('returns default item', () => {
			let newItem = RecentModel.prototype.getElementState();
			assert.equal(newItem.id, 0);
			assert.equal(newItem.templateId, '');
			assert.equal(newItem.template, 'item');
			assert.equal(newItem.chatType, 'chat');
			assert.equal(newItem.sectionCode, 'general');
			assert.equal(newItem.avatar, '');
			assert.equal(newItem.color, '#048bd0');
			assert.equal(newItem.title, '');
			assert.equal(typeof newItem.message, 'object');
			assert.equal(newItem.message.id, 0);
			assert.equal(newItem.message.text, '');
			assert.equal(newItem.message.date instanceof Date, true);
			assert.equal(newItem.counter, 0);
			assert.equal(newItem.pinned, false);
			assert.equal(newItem.chatId, 0);
			assert.equal(newItem.userId, 0);
		});
	});

	describe('Mutations', () => {
		describe('set', () => {
			it('exists', () => {
				assert.equal(typeof RecentModel.prototype.getMutations().set, 'function');
			});

			it('expects array and does nothing if separate item is passed', () => {
				vuex.store.commit('recent/set', createItem());

				assert.equal(store.collection.general.length, 0);
				assert.equal(store.collection.pinned.length, 0);
			});

			it('adds new item in general collection for each item in payload.general', () => {
				let payload = {};
				payload.general = [
					{title: 'First item'},
					{title: 'Second item'}
				];

				vuex.store.commit('recent/set', payload);

				assert.equal(store.collection.general.length, 2);
				assert.equal(store.collection.pinned.length, 0);
				assert.equal(store.collection.general[0].title, payload.general[0].title);
				assert.equal(store.collection.general[1].title, payload.general[1].title);
			});

			it('adds new item in pinned collection for each item in payload.pinned', () => {
				let payload = {};
				payload.pinned = [
					{title: 'First item'},
					{title: 'Second item'}
				];

				vuex.store.commit('recent/set', payload);

				assert.equal(store.collection.general.length, 0);
				assert.equal(store.collection.pinned.length, 2);
				assert.equal(store.collection.pinned[0].title, payload.pinned[0].title);
				assert.equal(store.collection.pinned[1].title, payload.pinned[1].title);
			});

			it('adds items in both collections if there are items in both payload.general and payload.pinned', () => {
				let payload = {};
				payload.general = [
					{title: 'First general item'},
					{title: 'Second general item'},
					{title: 'Third general item'}
				];
				payload.pinned = [
					{title: 'First pinned item'},
					{title: 'Second pinned item'}
				];

				vuex.store.commit('recent/set', payload);

				assert.equal(store.collection.general.length, 3);
				assert.equal(store.collection.general[0].title, payload.general[0].title);
				assert.equal(store.collection.general[1].title, payload.general[1].title);
				assert.equal(store.collection.general[2].title, payload.general[2].title);
				assert.equal(store.collection.pinned.length, 2);
				assert.equal(store.collection.pinned[0].title, payload.pinned[0].title);
				assert.equal(store.collection.pinned[1].title, payload.pinned[1].title);
			});
		});

		describe('update', () => {
			it('exists', () => {
				assert.equal(typeof RecentModel.prototype.getActions().update, 'function');
			});

			it('expects single object (fields, section, index) and does nothing if something else is passed', () => {
				let newItem = createItem({
					id: 'chat1',
					title: 'Original title'
				});
				store.collection.general.push(newItem);

				let payload = [
					{id: 'chat1', fields: {title: 'Changed title'}},
					{id: 'chat2', fields: {}}
				];
				vuex.store.commit('recent/update', payload);
				let existingItem = RecentModel.prototype.findItem(store.collection, newItem.id);

				assert.equal(existingItem.element.title, newItem.title);
			});

			it('updates existing item with passed data', () => {
				let newItem = createItem({
					id: 'chat1',
					title: 'Original title'
				});
				store.collection.general.push(newItem);

				let existingItem = RecentModel.prototype.findItem(store.collection, newItem.id);
				let payload = {
					index: existingItem.index,
					fields: {title: 'Changed title'},
					section: existingItem.element.sectionCode
				};
				vuex.store.commit('recent/update', payload);

				existingItem = RecentModel.prototype.findItem(store.collection, newItem.id);
				assert.equal(existingItem.element.title, payload.fields.title);
			});
		});

		describe('delete', () => {
			it('exists', () => {
				assert.equal(typeof RecentModel.prototype.getActions().delete, 'function');
			});

			it('expects single object (section, index) and does nothing if something else is passed', () => {
				let newItem = createItem({
					id: 'chat1',
					title: 'Original title'
				});
				store.collection.general.push(newItem);

				let payload = {
					index: 0
				};
				vuex.store.commit('recent/delete', payload);
				let existingItem = RecentModel.prototype.findItem(store.collection, newItem.id);

				assert.equal(store.collection.general.length, 1);
				assert.equal(existingItem.element.title, newItem.title);
			});

			it('deletes item with passed index in passed section of collection', () => {
				let newItem = createItem({
					id: 'chat1',
					title: 'Original title'
				});
				store.collection.general.push(newItem);
				let existingItem = RecentModel.prototype.findItem(store.collection, newItem.id);

				let payload = {
					index: existingItem.index,
					section: existingItem.element.sectionCode
				};
				vuex.store.commit('recent/delete', payload);

				assert.equal(store.collection.general.length, 0);
			});
		});
	});

	describe('Actions', () => {
		describe('set', () => {
			it('exists', () => {
				assert.equal(typeof RecentModel.prototype.getActions().set, 'function');
			});

			it('expects object with pinned and general properties and does nothing if something else was provided', () => {
				let payload = {
					id: 'chat13',
					title: 'My new title',
					color: 'red'
				};

				return vuex.store.dispatch('recent/set', payload).then(() => {
					assert.equal(store.collection.general.length, 0);
				});
			});

			it('can set single item', () => {
				let payload = {};
				payload.general = {
					id: 'chat13',
					title: 'My new title',
					color: 'red'
				};

				return vuex.store.dispatch('recent/set', payload).then(() => {
					assert.equal(store.collection.general.length, 1);
					assert.equal(store.collection.general[0].id, payload.general.id);
					assert.equal(store.collection.general[0].title, payload.general.title);
					assert.equal(store.collection.general[0].color, payload.general.color);
				});
			});

			it('can set array of items', () => {
				let payload = {};
				payload.general = [
					{
						id: 'chat13',
						title: 'My new title',
						color: 'red'
					},
					{
						id: 'chat14',
						title: 'My second title',
						color: 'blue'
					},
				];
				payload.pinned = [
					{
						id: 'chat15',
						title: 'My pinned title',
						color: 'green'
					}
				];

				return vuex.store.dispatch('recent/set', payload).then(() => {
					assert.equal(store.collection.general.length, 2);
					assert.equal(store.collection.general[0].id, payload.general[0].id);
					assert.equal(store.collection.general[0].title, payload.general[0].title);
					assert.equal(store.collection.general[0].color, payload.general[0].color);
					assert.equal(store.collection.general[1].id, payload.general[1].id);
					assert.equal(store.collection.general[1].title, payload.general[1].title);
					assert.equal(store.collection.general[1].color, payload.general[1].color);
					assert.equal(store.collection.pinned.length, 1);
					assert.equal(store.collection.pinned[0].id, payload.pinned[0].id);
					assert.equal(store.collection.pinned[0].title, payload.pinned[0].title);
					assert.equal(store.collection.pinned[0].color, payload.pinned[0].color);
				});
			});
		});

		describe('update', () => {
			it('exists', () => {
				assert.equal(typeof RecentModel.prototype.getActions().update, 'function');
			});

			it('expects object with id and fields properties and does nothing if something else was passed', () => {
				let newItem = createItem({
					title: 'Original title'
				});
				store.collection.general.push(newItem);

				let payload = {
					id: newItem.id,
					item: {title: 'Changed title'}
				};

				return vuex.store.dispatch('recent/set', payload).then(() => {
					assert.equal(store.collection.general[0].title, newItem.title);
				});
			});

			it('updates chat elements', () => {
				let newItem = createItem({
					id: 'chat15',
					title: 'Original title'
				});
				store.collection.general.push(newItem);

				let payload = {
					id: newItem.id,
					fields: {
						title: 'Changed title'
					}
				};

				return vuex.store.dispatch('recent/update', payload)
					.then(() => {
						assert.equal(store.collection.general[0].title, payload.fields.title);
					});
			});

			it('updates user elements', () => {
				let newItem = createItem({
					id: 18,
					title: 'Original user title'
				});
				store.collection.general.push(newItem);

				let payload = {
					id: '18',
					fields: {
						title: 'Changed user title'
					}
				};

				return vuex.store.dispatch('recent/update', payload)
					.then(() => {
						assert.equal(store.collection.general[0].title, payload.fields.title);
					});
			});
		});

		describe('delete', () => {
			it('exists', () => {
				assert.equal(typeof RecentModel.prototype.getActions().delete, 'function');
			});

			it('deletes an element', () => {
				let newItem = createItem({
					id: 'chat13'
				});
				store.collection.general.push(newItem);

				let payload = {
					id: newItem.id
				};

				return vuex.store.dispatch('recent/delete', payload)
					.then(() => {
						assert.equal(store.collection.general.length, 0);
					});
			});
		});

		describe('pin', () => {
			it('exists', () => {
				assert.equal(typeof RecentModel.prototype.getActions().pin, 'function');
			});

			it('can pin item - adds item to pinned section and removes it from general section', () => {
				let newItem = createItem({
					id: 'chat99',
					title: 'Pin me!'
				});
				store.collection.general.push(newItem);

				let payload = {
					id: newItem.id,
					action: true
				};

				return vuex.store.dispatch('recent/pin', payload)
					.then(() => {
						assert.equal(store.collection.general.length, 0);
						assert.equal(store.collection.pinned.length, 1);
						assert.equal(store.collection.pinned[0].title, newItem.title);
						assert.equal(store.collection.pinned[0].sectionCode, 'pinned');
						assert.equal(store.collection.pinned[0].pinned, true);
					});
			});

			it('can unpin item - adds item to general section and removes it from pinned section', () => {
				let newItem = createItem({
					id: 'chat99',
					title: 'Unpin me!'
				});
				store.collection.pinned.push(newItem);

				let payload = {
					id: newItem.id,
					action: false
				};

				return vuex.store.dispatch('recent/pin', payload)
					.then(() => {
						assert.equal(store.collection.pinned.length, 0);
						assert.equal(store.collection.general.length, 1);
						assert.equal(store.collection.general[0].title, newItem.title);
						assert.equal(store.collection.general[0].sectionCode, 'general');
						assert.equal(store.collection.general[0].pinned, false);
					});
			});
		});

		describe('clearPlaceholders', () => {
			it('exists', () => {
				assert.equal(typeof RecentModel.prototype.getActions().clearPlaceholders, 'function');
			});

			it('removes all items from "general" collection with id starting with "placeholder"', () => {
				let newItem = createItem({
					id: 'placeholder1'
				});

				let newItem2 = createItem({
					id: 'placeholder2'
				});

				let newItem3 = createItem({
					id: 'chat3'
				});

				store.collection.general.push(newItem, newItem2, newItem3);

				return vuex.store.dispatch('recent/clearPlaceholders')
					.then(() => {
						assert.equal(store.collection.general.length, 1);
					});
			});
		});

		describe('updatePlaceholders', () => {
			it('exists', () => {
				assert.equal(typeof RecentModel.prototype.getActions().updatePlaceholders, 'function');
			});

			it('updates item and deletes appropriate placeholder if there is item with passed id', () => {
				let newItem = createItem({
					id: 'chat3',
					title: 'I was here already!'
				});
				let newItem2 = createItem({
					id: 'chat4',
					title: 'I was here already too!'
				});
				store.collection.general.push(newItem, newItem2);

				let placeholders = [];
				placeholders.push(
					{
						id: 'placeholder0',
						templateId: 'placeholder0',
						template: 'placeholder',
						sectionCode: 'general'
					},
					{
						id: 'placeholder1',
						templateId: 'placeholder1',
						template: 'placeholder',
						sectionCode: 'general'
					}
				);
				store.collection.general.push(...placeholders);

				let payload = {};
				payload.items = [
					{
						id: newItem.id,
						title: 'I am new data!',
					},
					{
						id: newItem2.id,
						title: 'I am new data too!',
					}
				];
				payload.firstMessage = 0;

				return vuex.store.dispatch('recent/updatePlaceholders', payload)
					.then(() => {
						assert.equal(store.collection.general.length, 2);
						assert.equal(store.collection.general[0].title, payload.items[0].title);
						assert.equal(store.collection.general[1].title, payload.items[1].title);
					});
			});

			it('updates appropriate placeholder with passed data if there is no item with passed id', () => {
				let placeholders = [];
				placeholders.push(
					{
						id: 'placeholder0',
						templateId: 'placeholder0',
						template: 'placeholder',
						sectionCode: 'general'
					},
					{
						id: 'placeholder1',
						templateId: 'placeholder1',
						template: 'placeholder',
						sectionCode: 'general'
					}
				);
				store.collection.general.push(...placeholders);

				let payload = {};
				payload.items = [
					{
						id: 'chat11',
						title: 'I am totally new data!'
					},
					{
						id: 'chat12',
						title: 'I am totally new data too!'
					}
				];
				payload.firstMessage = 0;

				return vuex.store.dispatch('recent/updatePlaceholders', payload)
					.then(() => {
						assert.equal(store.collection.general.length, 2);
						assert.equal(store.collection.general[0].title, payload.items[0].title);
						assert.equal(store.collection.general[1].title, payload.items[1].title);
					});
			});
		});
	});

	describe('Helpers', () => {
		describe('findItem()', () => {
			it('exists', () => {
				assert.equal(typeof RecentModel.prototype.findItem, 'function');
			});

			it('returns empty object if there is no such item', () => {
				let existingItem = RecentModel.prototype.findItem(store.collection, 'id', 999);
				assert.equal(typeof existingItem, 'object');
				assert.equal(typeof existingItem.element, 'undefined');
				assert.equal(typeof existingItem.index, 'undefined');
			});

			describe('successful search', () => {
				it('finds items based on ID by default', () => {
					let newItem = createItem();
					store.collection.general.push(newItem);

					let existingItem = RecentModel.prototype.findItem(store.collection, newItem.id);
					assert.notEqual(typeof existingItem.index, 'undefined');
					assert.notEqual(typeof existingItem.element, 'undefined');
				});

				it('can find item based on any key', () => {
					let newItem = createItem();
					store.collection.general.push(newItem);

					let existingItem = RecentModel.prototype.findItem(store.collection, newItem.title, 'title');
					assert.notEqual(typeof existingItem.index, 'undefined');
					assert.notEqual(typeof existingItem.element, 'undefined');
				});

				it('returns index of found item', () => {
					let newItem = createItem();
					store.collection.general.push(newItem);

					let existingItem = RecentModel.prototype.findItem(store.collection, newItem.id);
					assert.equal(existingItem.index, 0);
				});

				it('returns found item itself', () => {
					let newItem = createItem();
					store.collection.general.push(newItem);

					let existingItem = RecentModel.prototype.findItem(store.collection, newItem.id);
					assert.equal(existingItem.element, newItem);
					assert.equal(existingItem.element.id, newItem.id);
					assert.equal(existingItem.element.title, newItem.title);
					assert.equal(existingItem.element.message.text, newItem.message.text);
				});

				it('can find items both in general and pinned sections', () => {
					let newItem = createItem();
					let newPinnedItem = Object.assign({}, RecentModel.prototype.getElementState(), {
						id: 'chat6',
						title: 'Second user',
						message: {
							id: 6,
							text: 'Message of second user'
						}
					});
					store.collection.general.push(newItem);
					store.collection.pinned.push(newPinnedItem);

					let existingItem = RecentModel.prototype.findItem(store.collection, newPinnedItem.id, undefined, 'pinned');
					assert.equal(existingItem.element, newPinnedItem);
					assert.equal(existingItem.element.id, newPinnedItem.id);
					assert.equal(existingItem.element.title, newPinnedItem.title);
					assert.equal(existingItem.element.message.text, newPinnedItem.message.text);
				});
			});
		});

		describe('initCollection()', () => {
			it('exists', () => {
				assert.equal(typeof RecentModel.prototype.initCollection, 'function');
			});

			it('returns index and flag if element is already exists', () => {
				let newItem = createItem();
				store.collection.general.push(newItem);

				let {index, alreadyExists} = RecentModel.prototype.initCollection(store, newItem, 'general');

				assert.equal(alreadyExists, true);
				assert.equal(typeof index, 'number');
				assert.equal(index, 0);
			});

			it('if not found - creates new element with given data and returns its index and flag', () => {
				store.collection.general.push(createItem());
				let payload = {id: 999};
				let {index, alreadyExists} = RecentModel.prototype.initCollection(store, payload, 'general');
				let existingItem = RecentModel.prototype.findItem(store.collection, payload.id);

				assert.equal(alreadyExists, false);
				assert.equal(store.collection.general.length, 2);
				assert.equal(index, 1);
				assert.equal(existingItem.element.id, payload.id);
			});
		});

		describe('prepareItem()', () => {
			it('exists', () => {
				assert.equal(typeof RecentModel.prototype.prepareItem, 'function');
			});

			it('creates new object from getElementState and validated payload', () => {
				let payload = {
					id: 13,
					title: 'My super title'
				};

				let result = RecentModel.prototype.prepareItem(payload);
				let defaultState = RecentModel.prototype.getElementState();

				assert.equal(result.id, payload.id);
				assert.equal(result.title, payload.title);
				assert.equal(result.color, defaultState.color);
				assert.equal(result.message.text, defaultState.message.text);
			});

			it('can add provided options to item', () => {
				let payload = {
					id: 13,
					title: 'My super title'
				};

				let options = {host: 'www.bitri24.ru'};
				let result = RecentModel.prototype.prepareItem(payload, options);
				assert.equal(result.host, options.host);
			});
		});

		describe('validate()', () => {
			it('exists', () => {
				assert.equal(typeof RecentModel.prototype.validate, 'function');
			});

			it('checks if id is number or string', () => {
				let payload = {
					id: true
				};

				let result = RecentModel.prototype.validate(payload);
				assert.equal(result.id, undefined);

				payload = {
					id: {}
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.id, undefined);

				payload = {
					id: 'chat1'
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.id, payload.id);

				payload = {
					id: 18
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.id, payload.id);
			});

			it('checks if templateId is string', () => {
				let payload = {
					templateId: true
				};

				let result = RecentModel.prototype.validate(payload);
				assert.equal(result.templateId, undefined);

				payload = {
					templateId: {}
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.templateId, undefined);

				payload = {
					templateId: 18
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.templateId, undefined);

				payload = {
					templateId: 'chat1'
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.templateId, payload.templateId);
			});

			it('checks if template is string', () => {
				let payload = {
					template: true
				};

				let result = RecentModel.prototype.validate(payload);
				assert.equal(result.template, undefined);

				payload = {
					template: {}
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.template, undefined);

				payload = {
					template: 18
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.template, undefined);

				payload = {
					template: 'chat1'
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.template, payload.template);
			});

			it('checks if type is string', () => {
				let payload = {
					type: true
				};

				let result = RecentModel.prototype.validate(payload);
				assert.equal(result.type, undefined);

				payload = {
					type: {}
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.type, undefined);

				payload = {
					type: 18
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.type, undefined);
			});

			it('sets chatType for chats, users and notifications', () => {
				let payload = {
					type: 'chat',
					chat: {
						type: 'open'
					}
				};

				let result = RecentModel.prototype.validate(payload);
				assert.equal(result.chatType, payload.chat.type);

				payload = {
					type: 'chat',
					chat: {
						type: 'chat'
					}
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.chatType, payload.chat.type);

				payload = {
					type: 'user',
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.chatType, payload.type);

				payload = {
					type: 'notification',
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.chatType, payload.type);
				assert.equal(payload.title, 'Notifications');
			});

			it('checks if avatar is string', () => {
				let payload = {
					avatar: true
				};

				let result = RecentModel.prototype.validate(payload);
				assert.equal(result.avatar, undefined);

				payload = {
					avatar: {}
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.avatar, undefined);

				payload = {
					avatar: 18
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.avatar, undefined);
			});

			it('formats avatar string', () => {
				let payload = {
					avatar: ''
				};

				let result = RecentModel.prototype.validate(payload);
				assert.equal(result.avatar, undefined);

				payload = {
					avatar: 'http://www.google.com'
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.avatar, encodeURI(payload.avatar));

				payload = {
					avatar: '/images/my-photo.png'
				};

				let host = 'www.bitrix24.com';
				result = RecentModel.prototype.validate(payload, {host: host});
				assert.equal(result.avatar, encodeURI(host + payload.avatar));
			});

			it('checks if color is string', () => {
				let payload = {
					color: true
				};

				let result = RecentModel.prototype.validate(payload);
				assert.equal(result.color, undefined);

				payload = {
					color: {}
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.color, undefined);

				payload = {
					color: 18
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.color, undefined);

				payload = {
					color: '#ccc'
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.color, payload.color);
			});

			it('checks if title is string', () => {
				let payload = {
					title: true
				};

				let result = RecentModel.prototype.validate(payload);
				assert.equal(result.title, undefined);

				payload = {
					title: {}
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.title, undefined);

				payload = {
					title: 18
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.title, undefined);

				payload = {
					title: 'My title'
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.color, payload.color);
			});

			it('checks if message is object', () => {
				let payload = {
					message: []
				};

				let result = RecentModel.prototype.validate(payload);
				assert.equal(result.message, undefined);

				payload = {
					message: null
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.message, undefined);

				payload = {
					message: 'My message'
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.message, undefined);

				payload = {
					message: 18
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.message, undefined);

				payload = {
					message: {}
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(typeof result.message, 'object');
			});

			it('checks if counter is number', () => {
				let payload = {
					counter: true
				};

				let result = RecentModel.prototype.validate(payload);
				assert.equal(result.counter, undefined);

				payload = {
					counter: {}
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.counter, undefined);

				payload = {
					counter: 'My title'
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.counter, undefined);

				payload = {
					counter: 18
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.counter, payload.counter);
			});

			it('checks if pinned is boolean', () => {
				let payload = {
					pinned: {}
				};

				let result = RecentModel.prototype.validate(payload);
				assert.equal(result.pinned, undefined);

				payload = {
					pinned: 'My title'
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.pinned, undefined);

				payload = {
					pinned: 18
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.pinned, undefined);

				payload = {
					pinned: true
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.pinned, payload.pinned);
			});

			it('checks if chatId is number', () => {
				let payload = {
					chatId: true
				};

				let result = RecentModel.prototype.validate(payload);
				assert.equal(result.chatId, undefined);

				payload = {
					chatId: {}
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.chatId, undefined);

				payload = {
					chatId: 'My title'
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.chatId, undefined);

				payload = {
					chatId: 18
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.chatId, payload.chatId);
			});

			it('checks if userId is number', () => {
				let payload = {
					userId: true
				};

				let result = RecentModel.prototype.validate(payload);
				assert.equal(result.userId, undefined);

				payload = {
					userId: {}
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.userId, undefined);

				payload = {
					userId: 'My title'
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.userId, undefined);

				payload = {
					userId: 18
				};

				result = RecentModel.prototype.validate(payload);
				assert.equal(result.userId, payload.userId);
			});
		});
	});
});

function createItem(data = {})
{
	return Object.assign(
		RecentModel.prototype.getElementState(),
		{
			id: 'chatTest',
			title: 'Test user',
			message: {
				id: 5,
				text: 'Message of test user'
			}
		},
		data
	);
}

function clearStore()
{
	store.collection.general = [];
	store.collection.pinned = [];
}