import {Loc, Tag} from 'main.core';
import {Popup} from 'main.popup';

class WorkgroupCardUtil
{
	static processAJAXError(errorCode)
	{
		if (errorCode.indexOf('SESSION_ERROR', 0) === 0)
		{
			this.showError(Loc.getMessage('SGMErrorSessionWrong'));
		}
		else if (errorCode.indexOf('CURRENT_USER_NOT_AUTH', 0) === 0)
		{
			this.showError(Loc.getMessage('SGMErrorCurrentUserNotAuthorized'));
		}
		else if (errorCode.indexOf('SONET_MODULE_NOT_INSTALLED', 0) === 0)
		{
			this.showError(Loc.getMessage('SGMErrorModuleNotInstalled'));
		}
		else
		{
			this.showError(errorCode);
		}

		return false;
	}

	static showError(errorText)
	{
		(new Popup(`sgm-error${Math.random()}`, null, {
			autoHide: true,
			lightShadow: false,
			zIndex: 2,
			content: Tag.render`<div class="sonet-sgm-error-text-block">${errorText}</div>`,
			closeByEsc: true,
			closeIcon: true,
		})).show();
	}
}

export {
	WorkgroupCardUtil,
}