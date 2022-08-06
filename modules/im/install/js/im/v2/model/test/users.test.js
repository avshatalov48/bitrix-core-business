import {VuexBuilder} from "ui.vue.vuex";
import {UsersModel} from '../src/users';

describe('Im model: Users', () => {

	let vuex = null;

	before(async () => {

		vuex = await new VuexBuilder()
			.addModel(
				UsersModel.create()
					.useDatabase(false)
					.setVariables({
						host: 'http://bitrix24.com',
						default: {name: 'Anonymous'}
					})
			)
			.build()
		;
	});

	it('Model is loaded', () => {
		assert(typeof UsersModel !== 'undefined');
	});

	it('Model is initialize', async () => {
		assert.equal(vuex.store.state.users.host, 'http://bitrix24.com');
	});

	/*
	*	= test case for update only name without lastActivityDate =
	*
	*	vuex.store.dispatch('users/update', {
	*		id: 1,
	*		fields: { name: 'Ivan Ivanov' }
	*	});
	*
	* 	= test case for update digits in name =
	*
	* 	vuex.store.dispatch('users/update', {
	*		id: 1,
	*		fields: { firstName: 123, lastName: 456 }
	*	});
	*
	* 	= test case for empty name, should by default name like Guest and compile full name field =
	*
	* 	vuex.store.dispatch('users/update', {
	*		id: 1,
	*		fields: { firstName: '', lastName: '' }
	*	});
	*
	* 	= test case for empty name, should by default name like Guest and compile first and full field =
	*
	* 	vuex.store.dispatch('users/update', {
	*		id: 1,
	*		fields: { name: '' }
	*	});
	*
	* */

});