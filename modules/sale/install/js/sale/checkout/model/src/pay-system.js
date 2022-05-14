import {Vue} from 'ui.vue';
import {VuexBuilderModel} from 'ui.vue.vuex';
import {Type} from 'main.core';
import {Loader as LoaderConst, PaySystem as Const} from 'sale.checkout.const';

export class PaySystem extends VuexBuilderModel
{
	getName()
	{
		return 'pay-system';
	}
	
	getState()
	{
		return {
			paySystem: [],
			status: LoaderConst.status.none,
		}
	}
	
	static getBaseItem()
	{
		return {
			id: 0,
			name: null,
			type: Const.type.undefined,
			picture: null
		};
	}
	
	validate(fields)
	{
		const result = {};
		
		if (Type.isObject(fields.paySystem))
		{
			result.paySystem = this.validatePaySystem(fields.paySystem);
		}
		
		if (Type.isString(fields.status))
		{
			result.status = fields.status.toString()
		}
		
		return result;
	}
	
	validatePaySystem(fields)
	{
		const result = {};
		
		if (Type.isNumber(fields.id) || Type.isString(fields.id))
		{
			result.id = parseInt(fields.id);
		}
		
		if (Type.isString(fields.name))
		{
			result.name = fields.name.toString();
		}

		if (Type.isString(fields.logotypeSrc) && fields.logotypeSrc.length > 0)
		{
			result.picture = fields.logotypeSrc.toString();
		}
		
		if (Type.isString(fields.type))
		{
			let allowed = Object.values(Const.type);
			
			let type = fields.type.toString();
			
			result.type = allowed.includes(type) ? type : Const.type.undefined;
		}
		
		return result;
	}
	
	getActions()
	{
		return {
			setStatus: ({ commit }, payload) =>
			{
				payload = this.validate(payload);
				
				const status = Object.values(LoaderConst.status);
				
				payload.status = status.includes(payload.status) ? payload.status : LoaderConst.status.none;
				
				commit('setStatus', payload);
			},
			addItem: ({ commit }, payload) =>
			{
				payload.fields = this.validatePaySystem(payload.fields);
				commit('addItem', payload);
			},
			changeItem: ({ commit }, payload) =>
			{
				payload.fields = this.validatePaySystem(payload.fields);
				commit('updateItem', payload);
			},
			removeItem({ commit }, payload)
			{
				commit('deleteItem', payload);
			}
		}
	}
	
	getGetters()
	{
		return {
			getStatus: state =>
			{
				return state.status;
			},
			getPaySystem: state =>
			{
				return state.paySystem;
			},
		}
	}
	
	getMutations()
	{
		return {
			setStatus: (state, payload) =>
			{
				let item = { status: LoaderConst.status.none };
				
				item = Object.assign(item, payload);
				state.status = item.status;
			},
			addItem: (state, payload) =>
			{
				let item = PaySystem.getBaseItem();
				
				item = Object.assign(item, payload.fields);
				
				state.paySystem.push(item);
			},
			updateItem: (state, payload) =>
			{
				if (typeof state.paySystem[payload.index] === 'undefined')
				{
					Vue.set(state.paySystem, payload.index, PaySystem.getBaseItem());
				}
				
				state.paySystem[payload.index] = Object.assign(
					state.paySystem[payload.index],
					payload.fields
				);
			},
			deleteItem: (state, payload) =>
			{
				state.paySystem.splice(payload.index, 1);
			},
			clearPaySystem: (state) =>
			{
				state.paySystem = [];
			},
		}
	}
}