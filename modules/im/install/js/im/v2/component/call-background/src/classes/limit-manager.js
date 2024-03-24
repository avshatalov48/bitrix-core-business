import 'ui.info-helper';

import { Utils } from 'im.v2.lib.utils';
import { DesktopApi, DesktopFeature } from 'im.v2.lib.desktop-api';

import { Action } from './items/action';

import type { LimitRestResult } from '../types/rest';

export class LimitManager
{
	static limitCode = {
		blur: 'call_blur_background',
		image: 'call_background',
	};

	limits: {[limitId: string]: LimitRestResult} = {};

	constructor(params: {limits: LimitRestResult[], infoHelperUrlTemplate: string})
	{
		const { limits, infoHelperUrlTemplate } = params;
		this.#initLimits(limits);
		this.#initInfoHelper(infoHelperUrlTemplate);
	}

	isLimitedAction(action: Action): boolean
	{
		if (action.isEmpty() || action.isUpload())
		{
			return false;
		}

		return action.isBlur() && this.#limitIsActive(LimitManager.limitCode.blur);
	}

	isLimitedBackground(): boolean
	{
		return this.#limitIsActive(LimitManager.limitCode.image);
	}

	showLimitSlider(limitCode: string)
	{
		window.BX.UI.InfoHelper.show(this.limits[limitCode].articleCode);
	}

	// region Mask feature
	static isMaskFeatureAvailable(): boolean
	{
		if (!Utils.platform.isBitrixDesktop())
		{
			return true;
		}

		return DesktopApi.isFeatureEnabled(DesktopFeature.mask.id);
	}

	static isMaskFeatureSupportedByDesktopVersion(): boolean
	{
		if (!Utils.platform.isBitrixDesktop())
		{
			return true;
		}

		return DesktopApi.isFeatureSupported(DesktopFeature.mask.id);
	}
	// endregion Mask feature

	static showHelpArticle(articleCode: string)
	{
		window.BX.Helper?.show(`redirect=detail&code=${articleCode}`);
	}

	#initLimits(limits: LimitRestResult[])
	{
		limits.forEach((limit: LimitRestResult) => {
			this.limits[limit.id] = limit;
		});
	}

	#initInfoHelper(infoHelperUrlTemplate: string)
	{
		if (window.BX.UI.InfoHelper.isInited())
		{
			return;
		}

		window.BX.UI.InfoHelper.init({
			frameUrlTemplate: infoHelperUrlTemplate
		});
	}

	#limitIsActive(limitCode: string): boolean
	{
		const limitIsActive = !!this.limits[limitCode]?.active;
		const articleIsActive = !!this.limits[limitCode]?.articleCode;

		return limitIsActive && articleIsActive;
	}
}
