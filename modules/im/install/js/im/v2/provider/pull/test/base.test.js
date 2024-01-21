import 'im.test';

import 'im.v2.provider.pull';
import {EventType} from 'im.v2.const';
import {Controller} from "im.v2.controller";
import {ImBasePullHandler} from "im.v2.provider.pull";

//setting controller and pullHandler before each test
let controller = null;
let pullHandler = null;

beforeEach(async () => {
	controller = await new Controller().ready();
	pullHandler = new ImBasePullHandler({
		store: controller.store,
		controller: controller,
	});
});

describe('Base pull handler', function() {
	describe('handleMessageAdd', function() {
		const updatedChatData = getDefaultChatData({name: 'Test chat updated'});
		const testUserData1 = getDefaultUserData();
		const testUserData2 = getDefaultUserData({
			id: 13,
			name: 'Stas Fuflov',
			first_name: 'Stas',
			last_name: 'Fuflov',
		});

		let messageAddData = {
			chat: {
				[updatedChatData.id]: updatedChatData
			},
			chatId: updatedChatData.id,
			counter: 1,
			dialogId: updatedChatData.dialogId,
			files: {},
			lines: [],
			message: getDefaultMessageData({
				id: getDefaultMessageData().id + 1,
				text: 'message from messageAdd',
			}),
			notify: true,
			userBlockChat: {},
			userInChat: {
				[updatedChatData.id]: [testUserData1.id, testUserData2.id]
			},
			users: {
				[testUserData1.id]: testUserData1,
				[testUserData2.id]: testUserData2
			}
		};

		it('should exist', function() {
			assert.equal(typeof pullHandler.handleMessageAdd, 'function');
		});

		it('should update existing dialog with new values', function() {
			return setInitialData(controller.store).then(() => {
				assert.equal(controller.store.getters['chats/get'](getDefaultChatData().dialogId).name, getDefaultChatData().name);

				pullHandler.handleMessageAdd(messageAddData);

				assert.equal(controller.store.getters['chats/get'](getDefaultChatData().dialogId).name, updatedChatData.name);
			});
		});

		it('should update recent model', function() {
			return setInitialData(controller.store).then(() => {
				assert.equal(controller.store.state.recent.collection.general[0].title, getDefaultRecentData().general[0].title);
				assert.equal(controller.store.state.recent.collection.general[1].title, getDefaultRecentData().general[1].title);

				pullHandler.handleMessageAdd(messageAddData);

				assert.equal(controller.store.getters['recent/get'](messageAddData.dialogId).element.counter, messageAddData.counter);
				assert.equal(controller.store.getters['recent/get'](messageAddData.dialogId).element.message.id, messageAddData.message.id);
				assert.equal(controller.store.getters['recent/get'](messageAddData.dialogId).element.message.text, messageAddData.message.text);
			});
		});

		it('should set users if they were passed', function() {
			return setInitialData(controller.store).then(() => {
				assert.equal(Object.keys(controller.store.state.users.collection).length, 0);

				pullHandler.handleMessageAdd(messageAddData);

				assert.equal(Object.keys(controller.store.state.users.collection).length, Object.keys(messageAddData.users).length);
				assert.equal(controller.store.getters['users/get'](testUserData1.id).name, testUserData1.name);
				assert.equal(controller.store.getters['users/get'](testUserData2.id).name, testUserData2.name);
			});
		});

		describe('files processing', function() {
			it('should update template file', function() {
				return setInitialData(controller.store)
					.then(() => {
						assert.equal(Object.keys(controller.store.state.files.collection[messageAddData.chatId]).length, 1);
						//get created templateId
						messageAddData.message.templateFileId = controller.store.state.files.collection[messageAddData.chatId][0]['templateId'];
						messageAddData.files = {
							[getDefaultFileData().id]: getDefaultFileData({name: 'Test file updated.txt'})
						};

						pullHandler.handleMessageAdd(messageAddData);

						assert.equal(Object.keys(controller.store.state.files.collection[messageAddData.chatId]).length, 1);
						assert.equal(controller.store.state.files.collection[messageAddData.chatId][0].name, messageAddData.files[getDefaultFileData().id].name);
					});
			});

			it('should add new files', function() {
				return setInitialData(controller.store).then(() => {
					assert.equal(Object.keys(controller.store.state.files.collection).length, 1);
					const newFile1 = getDefaultFileData({id: 2, name: 'new file 1'});
					const newFile2 = getDefaultFileData({id: 3, name: 'new file 2'});
					messageAddData.files = {
						[newFile1.id]: newFile1,
						[newFile2.id]: newFile2
					};

					pullHandler.handleMessageAdd(messageAddData);

					assert.equal(Object.keys(controller.store.state.files.collection[messageAddData.chatId]).length, 3);
					assert.equal(controller.store.state.files.collection[messageAddData.chatId][1].name, newFile1.name);
					assert.equal(controller.store.state.files.collection[messageAddData.chatId][2].name, newFile2.name);
				});
			});
		});

		describe('message processing', function() {
			it('should update template message', function() {
				return setInitialData(controller.store).then(() => {
					const emitSpy = sinon.spy(pullHandler.controller.application, 'emit');

					assert.equal(Object.keys(controller.store.state.messages.collection[messageAddData.chatId]).length, 1);
					//get created templateId
					messageAddData.message.templateId = controller.store.state.messages.collection[messageAddData.chatId][0].templateId;

					pullHandler.handleMessageAdd(messageAddData);

					assert.equal(controller.store.state.messages.collection[messageAddData.chatId][0].text, messageAddData.message.text);
					setTimeout(() => {
						assert.equal(pullHandler.controller.application.emit.calledOnce, true);
						assert.equal(pullHandler.controller.application.emit.getCall(0).args[0], EventType.dialog.scrollToBottom);
					}, 0);
				});
			});


			//TODO: Skipped that part
			//it('should consider isUnreadMessagesLoaded', function() {
			//
			//});
		});

		it('should stop opponent writing', function() {
			return setInitialData(controller.store).then(() => {
				const stopOpponentWritingSpy = sinon.spy(pullHandler.controller.application, 'stopOpponentWriting');

				pullHandler.handleMessageAdd(messageAddData);

				assert.equal(pullHandler.controller.application.stopOpponentWriting.calledOnce, true);
				assert.equal(pullHandler.controller.application.stopOpponentWriting.getCall(0).args[0].dialogId, messageAddData.dialogId);
				assert.equal(pullHandler.controller.application.stopOpponentWriting.getCall(0).args[0].userId, messageAddData.message.senderId);
			});
		});

		//TODO: skipped 'messages/readMessages' part
		it('should read all messages and set counter to 0 if current user send new message', function() {
			return setInitialData(controller.store).then(() => {
				// set current user
				pullHandler.controller.setUserId(getDefaultUserData().id);

				pullHandler.handleMessageAdd(messageAddData);

				assert.equal(controller.store.getters['chats/get'](messageAddData.dialogId).counter, 0);
			});
		});

		it('should increase counter if message was sent by non-current user', function() {
			return setInitialData(controller.store).then(() => {
				pullHandler.handleMessageAdd(messageAddData);

				assert.equal(controller.store.getters['chats/get'](messageAddData.dialogId).counter, 1);
			});
		});
	});
});

function getDefaultChatData(additionalData = {})
{
	return Object.assign({}, {
		dialogId: 'chat99',
		avatar: "/bitrix/js/im/images/blank.gif",
		call: "0",
		call_number: "",
		color: "",
		date_create: "2020-05-21T09:44:16+02:00",
		entity_data_1: "",
		entity_data_2: "",
		entity_data_3: "",
		entity_id: "",
		entity_type: "CHAT",
		extranet: false,
		id: '99',
		manager_list: [1],
		message_type: 'C',
		mute_list: {},
		name: "Test chat",
		owner: '1',
		public: null,
		type: 'chat'
	}, additionalData);
}

function getDefaultMessageData(additionalData = {})
{
	return Object.assign({}, {
		chatId: 99,
		counter: 0,
		date: "2020-05-21T14:58:10+02:00",
		id: 7777,
		params: [],
		prevId: 7776,
		recipientId: 'chat99',
		senderId: getDefaultUserData().id,
		system: 'N',
		templateFileId: '',
		templateId: '',
		text: 'Test message',
	}, additionalData);
}

function getDefaultUserData(additionalData = {})
{
	return Object.assign({}, {
		absent: false,
		active: true,
		avatar: '',
		avatar_id: 0,
		birthday: '18-08',
		bot: false,
		color: '',
		connector: false,
		departments: [1],
		desktop_last_date: null,
		external_auth_id: 'default',
		extranet: false,
		first_name: 'Denis',
		gender: 'M',
		id: 1,
		idle: false,
		last_activity_date: "2020-05-21T14:58:08+02:00",
		last_name: "Kotlyarchuk",
		mobile_last_date: "2020-05-19T14:10:45+02:00",
		name: "Denis Kotlyarchuk",
		network: false,
		phone_device: false,
		phones: false,
		profile: "/company/personal/user/1/",
		services: null,
		status: "online",
		tz_offset: 0,
		work_position: ""
	}, additionalData);
}

function getDefaultRecentData(additionalData = {})
{
	const additionalChatData = {id: 100, dialogId: 'chat100', name: 'Test chat 2'};

	return  {
		general: [
			{
				avatar: '',
				chat: getDefaultChatData(),
				chatId: getDefaultChatData().id,
				color: '',
				counter: 0,
				date_update: "2020-05-21T15:10:23+02:00",
				id: getDefaultChatData().dialogId,
				message: {
					attach: false,
					author_id: getDefaultUserData().id,
					date: "2020-05-21T15:10:23+02:00",
					file: false,
					id: 9999,
					status: 'delivered',
					text: 'Test message'
				},
				pinned: false,
				title: getDefaultChatData().name,
				type: 'chat',
				unread: false,
				user: getDefaultUserData(),
				userId: getDefaultUserData().id
			},
			{
				avatar: '',
				chat: getDefaultChatData(additionalChatData),
				chatId: getDefaultChatData(additionalChatData).id,
				color: '',
				counter: 0,
				date_update: "2020-05-21T15:10:23+02:00",
				id: getDefaultChatData(additionalChatData).dialogId,
				message: {
					attach: false,
					author_id: getDefaultUserData().id,
					date: "2020-05-21T15:10:23+02:00",
					file: false,
					id: 9999,
					status: 'delivered',
					text: 'Test message'
				},
				pinned: false,
				title: getDefaultChatData(additionalChatData).name,
				type: 'chat',
				unread: false,
				user: getDefaultUserData(),
				userId: getDefaultUserData().id
			}
		],
		pinned: []
	};
}

function getDefaultFileData(additionalData = {})
{
	const defaultFileId = 1;

	return Object.assign({}, {
			authorId: getDefaultUserData().id,
			authorName: getDefaultUserData().name,
			chatId: getDefaultChatData().id,
			date: "2020-05-22T12:43:20+02:00",
			extension: 'txt',
			id: defaultFileId,
			image: false,
			name: 'testFile.txt',
			progress: 100,
			size: 999,
			status: 'done',
			type: 'file',
			urlDownload: '',
			urlPreview: '',
			urlShow: '',
			viewerAttrs: {}
	}, additionalData);
}

async function setInitialData(store)
{
	await store.dispatch('chats/set', getDefaultChatData());
	await store.dispatch('recent/store', getDefaultRecentData());
	await store.dispatch('files/add', getDefaultFileData());
	await store.dispatch('messages/add', getDefaultMessageData());
	return Promise.resolve();
}