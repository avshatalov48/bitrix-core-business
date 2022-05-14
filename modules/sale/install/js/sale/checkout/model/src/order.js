import {VuexBuilderModel} from 'ui.vue.vuex';
import {Type} from 'main.core';

export class Order extends VuexBuilderModel
{
    getName()
    {
        return 'order';
    }

    getState()
    {
        return {
            order: Order.getBaseItem(),
            errors: []
        }
    }

    static getBaseItem()
    {
        return {
            id: 0,
            payed: 'N',
            accountNumber: null,
            hash: null,
        };
    }

    validate(fields)
    {
        const result = {};

        if (Type.isObject(fields.order))
        {
            result.order = this.validateOrder(fields.order);
        }

        return result;
    }

    validateOrder(fields)
    {
        const result = {};

        if (Type.isNumber(fields.id) || Type.isString(fields.id))
        {
            result.id = parseInt(fields.id);
        }

        if (Type.isNumber(fields.accountNumber) || Type.isString(fields.accountNumber))
        {
            result.accountNumber = fields.accountNumber.toString();
        }

        if (Type.isString(fields.hash))
        {
            result.hash = fields.hash.toString()
        }
    
        if (Type.isString(fields.payed))
        {
            result.payed = fields.payed.toString() === 'Y' ? 'Y':'N'
        }

        return result;
    }

    getActions()
    {
        return {
            set: ({ commit }, payload) =>
            {
                payload = this.validate({order: payload});
                commit('set', payload);
            }
        }
    }

    getGetters()
    {
        return {
            getOrder: state =>
            {
                return state.order;
            }
        }
    }

    getMutations()
    {
        return {
            set: (state, payload) =>
            {
                let item = Order.getBaseItem();
                state.order = Object.assign(item, payload.order);
            }
        }
    }
}