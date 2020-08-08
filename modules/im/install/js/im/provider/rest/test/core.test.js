import 'im.test';

import {CoreRestHandler} from "im.provider.rest";
import {Controller} from "im.controller";

//setting controller and restHandler before each test
let controller = null;
let restHandler = null;

beforeEach(async () => {
	controller = await new Controller().ready();
	restHandler = new CoreRestHandler({
		store: controller.store,
		controller: controller,
	});
});

describe('Core rest handler', function() {
	describe('handleImUserListGetSuccess', function() {
		it('should add users to model', function() {
			const testUser1 = getDefaultUserData();
			const testUser2 = getDefaultUserData({
				id: 13,
				first_name: "Stanislav",
				last_name: "Fuflov",
				name: "Stanislav Fuflov"
			});
			restHandler.execute('im.user.list.get', getDefaultRestAnswer({
				[testUser1.id]: testUser1,
				[testUser2.id]: testUser2
			}));

			assert.equal(controller.store.state.users.collection[testUser1.id].name, testUser1.name);
			assert.equal(controller.store.state.users.collection[testUser2.id].name, testUser2.name);
		});
	});
});

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

function getDefaultRestAnswer(data = {})
{
	return {
		answer: {
			result: data
		},
		error()
		{
			return false;
		},
		data()
		{
			return this.answer.result;
		}
	}
}