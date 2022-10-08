import {VuexBuilderModel} from 'ui.vue.vuex';
import {Type} from 'main.core';
import {Application as ApplicationConst, Loader as LoaderConst} from 'sale.checkout.const';

export class Application extends VuexBuilderModel
{
    getName()
    {
        return 'application';
    }

    getState()
    {
        return {
            stage: ApplicationConst.stage.undefined,
            status: LoaderConst.status.none,
            path:
                {
                    emptyCart: this.getVariable('path.emptyCart', null),
                    mainPage: this.getVariable('path.mainPage', null),
                    location: this.getVariable('path.location', null)
                },
            common:
                {
                    siteId: this.getVariable('common.siteId', null),
                    personTypeId: this.getVariable('common.personTypeId', 0),
                    tradingPlatformId: this.getVariable('common.tradingPlatformId', null),
                },
            option:
                {
                    signedParameters: this.getVariable('option.signedParameters', null),
                    showReturnButton: this.getVariable('option.showReturnButton', true),
                },
            message:
                {
                    buttonCheckoutTitle: this.getVariable('messages.buttonCheckoutTitle', null)
                },
            errors: []
        }
    }

    validate(fields)
    {
        const result = {};

        if (Type.isString(fields.stage))
        {
            result.stage = fields.stage.toString()
        }

        if (Type.isString(fields.status))
        {
            result.status = fields.status.toString()
        }

        if (Type.isObject(fields.path))
        {
            result.path = this.validatePaths(fields.path);
        }

        if (Type.isObject(fields.common))
        {
            result.common = this.validateCommon(fields.common);
        }

        if (Type.isObject(fields.options))
        {
            result.options = this.validateOptions(fields.options);
        }

        return result;
    }

    validateCommon(fields)
    {
        const result = {};

        if (Type.isString(fields.siteId))
        {
            result.siteId = fields.siteId.toString();
        }

        if (Type.isNumber(fields.tradingPlatformId) || Type.isString(fields.tradingPlatformId))
        {
            result.tradingPlatformId = parseInt(fields.tradingPlatformId)
        }

        if (Type.isNumber(fields.personTypeId) || Type.isString(fields.personTypeId))
        {
            result.personTypeId = parseInt(fields.personTypeId);
        }

        return result;
    }

    validatePaths(fields)
    {
        const result = {};

        if (Type.isString(fields.productNoImage))
        {
            result.productNoImage = fields.productNoImage.toString();
        }

        if (Type.isString(fields.emptyCart))
        {
            result.emptyCart = fields.emptyCart.toString();
        }

        if (Type.isString(fields.mainPage))
        {
            result.mainPage = fields.mainPage.toString();
        }

        if (Type.isString(fields.location))
        {
            result.location = fields.location.toString();
        }

        return result;
    }

    validateOptions(fields)
    {
        const result = {};

        if (Type.isString(fields.signedParameters))
        {
            result.signedParameters = fields.signedParameters.toString();
        }

		if (Type.isString(fields.showReturnButton))
		{
			result.showReturnButton = fields.showReturnButton.toString() === 'Y' ? 'Y' : 'N';
		}

        return result;
    }

    getActions()
    {
        return {
            setPathLocation: ({ commit }, payload) =>
            {
                payload = this.validatePaths({location: payload});
                commit('setPathLocation', payload.location);
            },
            setStatus: ({ commit }, payload) =>
            {
                payload = this.validate(payload);

                const status = [
                    LoaderConst.status.none,
                    LoaderConst.status.wait,
                ];

                payload.status = status.includes(payload.status) ? payload.status : LoaderConst.status.none;

                commit('setStatus', payload);
            },
            setStage: ({ commit }, payload) =>
            {
                payload = this.validate(payload);

                let allowed = Object.values(ApplicationConst.stage);

                payload.stage = allowed.includes(payload.stage) ? payload.stage : ApplicationConst.stage.undefined;
                commit('setStage', payload);
            }
        }
    }

    getGetters()
    {
        return {
            getErrors: state =>
            {
                return state.errors;
            },
            getPath: state =>
            {
                return state.path;
            },
            getSignedParameters: state =>
            {
                return state.option.signedParameters;
            },
			getShowReturnButton: state =>
			{
				return state.option.showReturnButton;
			},
            getPathLocation: (state, getters) =>
            {
                return getters.getPath.location;
            },
            getPathMainPage: (state, getters) =>
            {
                return getters.getPath.mainPage;
            },
            getTradingPlatformId: state =>
            {
                return state.common.tradingPlatformId;
            },
            getTitleCheckoutButton: state =>
            {
                return state.message.buttonCheckoutTitle;
            },
            getSiteId: state =>
            {
                return state.common.siteId;
            },
            getPersonTypeId: state =>
            {
                return state.common.personTypeId;
            },
            getStatus: state =>
            {
                return state.status;
            },
            getStage: state =>
            {
                return state.stage;
            },
        }
    }

    getMutations()
    {
        return {
            setPathLocation: (state, payload) =>
            {
                state.path.location = payload;
            },
            setStatus: (state, payload) =>
            {
                let item = { status: LoaderConst.status.none };

                item = Object.assign(item, payload);
                state.status = item.status;
            },
            setStage: (state, payload) =>
            {
                let item = { stage: ApplicationConst.stage.undefined };

                item = Object.assign(item, payload);
                state.stage = item.stage;
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