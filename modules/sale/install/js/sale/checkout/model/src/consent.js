import {VuexBuilderModel} from 'ui.vue.vuex';
import {Type} from 'main.core';
import {Consent as Const} from 'sale.checkout.const';

export class Consent extends VuexBuilderModel
{
    getName()
    {
        return 'consent';
    }

    getState()
    {
        return {
            status: Const.status.init,
            consent: Consent.getBaseItem(),
            errors: []
        }
    }

    static getBaseItem()
    {
        return {
            id: 0,
            title: '',
            isLoaded: '',
            autoSave: '',
            isChecked: '',
            submitEventName: '',
            params: []
        };
    }

    validate(fields)
    {
        const result = {};

        if (Type.isString(fields.status))
        {
            result.status = fields.status.toString()
        }

        if (Type.isObject(fields.consent))
        {
            result.consent = this.validateConsent(fields.consent);
        }

        return result;
    }

    validateConsent(fields)
    {
        const result = {};

        if (Type.isNumber(fields.id) || Type.isString(fields.id))
        {
            result.id = parseInt(fields.id);
        }

        if (Type.isString(fields.title))
        {
            result.title = fields.title.toString();
        }

        if (Type.isString(fields.isLoaded))
        {
            result.isLoaded = fields.isLoaded.toString();
        }

        if (Type.isString(fields.autoSave))
        {
            result.autoSave = fields.autoSave.toString();
        }

        if (Type.isString(fields.isChecked))
        {
            result.isChecked = fields.isChecked.toString();
        }

        if (Type.isString(fields.submitEventName))
        {
            result.submitEventName = fields.submitEventName.toString();
        }

        if (Type.isArrayFilled(fields.params))
        {
            result.params = this.validateParams(fields.params);
        }

        return result;
    }

    validateParams(fields)
    {
        const result = [];
        try
        {
            for (let key in fields)
            {
                if (!fields.hasOwnProperty(key))
                {
                    continue;
                }

                if (Type.isNumber(fields[key]) || Type.isString(fields[key]))
                {
                    result[key] = fields[key];
                }
            }
        }
        catch (e) {}

        return result;
    }

    getActions()
    {
        return {
            setStatus: ({ commit }, payload) =>
            {
                payload = this.validate({status: payload});

                const status = Object.values(Const.status);

                payload.status = status.includes(payload.status) ? payload.status : Const.status.init;

                commit('setStatus', payload);
            },

            set: ({ commit }, payload) =>
            {
                payload = this.validate({consent: payload});
                commit('set', payload);
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
            get: state =>
            {
                return state.consent;
            },
        }
    }

    getMutations()
    {
        return {
            setStatus: (state, payload) =>
            {
                state.status = payload.status;
            },

            set: (state, payload) =>
            {
                let item = Consent.getBaseItem();

                state.consent = Object.assign(item, payload.consent);
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
}