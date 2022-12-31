import { Vue } from 'ui.vue';
import { VuexBuilderModel } from 'ui.vue.vuex';
import { Type } from 'main.core';
import { Property as Const } from 'sale.checkout.const';

export class Property extends VuexBuilderModel
{
    getName()
    {
        return 'property';
    }

    getState()
    {
        return {
            property: [],
            errors: []
        }
    }

    static getBaseItem()
    {
        return {
            id: 0,
            name: "",
            type: Const.type.undefined,
            value: "",
            validated: Const.validate.unvalidated,
			required: 'N',
			multiple: 'N',
        };
    }

    validate(fields)
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

        if (Type.isString(fields.type))
        {
            let allowed = Object.values(Const.type);

            let type = fields.type.toString();

            result.type = allowed.includes(type) ? type : Const.type.undefined;
        }

        if (Type.isString(fields.value))
        {
            result.value = fields.value.toString();
        }

        if (Type.isString(fields.validated))
        {
            result.validated = fields.validated.toString();
        }

        if (Type.isNumber(fields.personTypeId) || Type.isString(fields.personTypeId))
        {
            result.personTypeId = parseInt(fields.personTypeId);
        }

		if (Type.isString(fields.required))
		{
			const requiredValue = fields.required.toString();
			result.required = requiredValue === 'Y' ? 'Y' : 'N';
		}

		if (Type.isString(fields.multiple))
		{
			const multipleValue = fields.multiple.toString();
			result.multiple = multipleValue === 'Y' ? 'Y' : 'N';
		}

        return result;
    }

    getActions()
    {
        return {
            addItem: ({ commit }, payload) =>
            {
                payload.fields = this.validate(payload.fields);

                commit('addItem', payload);
            },
            changeItem: ({ commit }, payload) =>
            {
                payload.fields = this.validate(payload.fields);

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
            get: state => id =>
            {
                if (!state.property[id] || state.property[id].length <= 0)
                {
                    return [];
                }

                return state.property[id];
            },
            getProperty: state =>
            {
                return state.property;
            },
            getBaseItem: state =>
            {
                return Property.getBaseItem();
            },
            getErrors: state =>
            {
                return state.errors;
            }
        }
    }

    getMutations()
    {
        return {
            addItem: (state, payload) =>
            {
                payload = this.prepareFields(payload);

                let item = Property.getBaseItem();

                item = Object.assign(item, payload);
                state.property.unshift(item);
                state.property.forEach((item, index) => {
                    item.sort = index + 1;
                });
            },
            updateItem: (state, payload) =>
            {
                if (typeof state.property[payload.index] === 'undefined')
                {
                    Vue.set(state.property, payload.index, Property.getBaseItem());
                }

                payload = this.prepareFields(payload);

                state.property[payload.index] = Object.assign(
                    state.property[payload.index],
                    payload.fields
                );
            },
            deleteItem: (state, payload) =>
            {
                state.property.splice(payload.index, 1);
            },
            clearProperty: (state) =>
            {
                state.property = [];
            },
            setErrors: (state, payload) =>
            {
                state.errors = payload;
            },
            clearErrors: (state) =>
            {
                state.errors = [];
            }
        }
    }

    prepareFields(fields)
    {
        const result = {};
        try
        {
            for (let field in fields)
            {
                if (!fields.hasOwnProperty(field))
                {
                    continue;
                }

                if (field === 'validated')
                {
                    const validate = Object.values(Const.validate);

                    fields.validated = validate.includes(fields.validated) ? fields.validated : Const.validate.unvalidated;
                    result[field] = fields.validated;

                }
                else
                {
                    result[field] = fields[field];
                }
            }
        }
        catch (e) {}

        return result;
    }
}