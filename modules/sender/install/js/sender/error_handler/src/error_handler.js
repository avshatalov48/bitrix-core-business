import {Loc, Tag, Type, Runtime} from "main.core";
import {EventEmitter} from "main.core.events";
import "./error_handler.css";

export class ErrorHandler
{
	constructor()
	{
	}

	onError(errorCode, data, callbackSuccess, callbackFailure)
	{
		let handlers = this.getHandlers(callbackSuccess, callbackFailure);
		if (handlers.hasOwnProperty(errorCode) && Type.isFunction(handlers[errorCode]))
		{
			handlers[errorCode].apply(this, [data]);
		}
	}

	getHandlers(callbackSuccess, callbackFailure, extraData)
	{
		return {
			'WRONG_EMAIL_FROM': this.getWrongEmailFromHandler.bind(this, callbackSuccess, callbackFailure, extraData),
			'FEATURE_NOT_AVAILABLE': this.getFeatureUnavailableHandler.bind(this, callbackSuccess, callbackFailure, extraData),
			'NEED_ACCEPT_AGREEMENT': this.needAcceptAgreementHandler.bind(this, callbackSuccess, callbackFailure, extraData),
		};
	}

	getWrongEmailFromHandler(callbackSuccess, callbackFailure, extraData, data) {
		if (extraData) {
			Object.assign(data, extraData);
		}
		this.oncloseCalbackActive = true;
		if (!this.wrongEmailFromPopup)
		{
			this.wrongEmailFromPopup = BX.PopupWindowManager.create(
				{
					id: 'sender_user_error_wrongEmailFrom',
					autoHide: true,
					lightShadow: true,
					closeByEsc: true,
					overlay: {backgroundColor: 'black', opacity: 500},
					events: {
						onPopupClose: () => {
							if (this.oncloseCalbackActive && Type.isFunction(callbackFailure)) {
								callbackFailure(data);
							}
						}
					}
				}
			);
		}
		this.wrongEmailFromPopup.setContent(Tag.render`<div class="sender-user-error-handler-text">
			${Loc.getMessage('SENDER_ERROR_HANDLER_WRONG_FROM_EMAIL_TITLE')}
			<br>
			${Loc.getMessage('SENDER_ERROR_HANDLER_WRONG_FROM_EMAIL_MESSAGE')}
			</div>`);
		this.wrongEmailFromPopup.setButtons([
			new BX.UI.Button({
				text : Loc.getMessage('SENDER_ERROR_HANDLER_WRONG_FROM_EMAIL_EDIT_EMAIL'),
				color: BX.UI.Button.Color.SUCCESS,
				onclick: () => {
					location.href = data.editUrl;
					this.oncloseCalbackActive = false;
					this.wrongEmailFromPopup.close();
				}
			}),
			new BX.UI.Button({
				text : Loc.getMessage('SENDER_ERROR_HANDLER_WRONG_FROM_EMAIL_CANCEL'),
				color: BX.UI.Button.Color.LINK,
				onclick: () => {
					this.wrongEmailFromPopup.close();
				}
			}),
		]);
		this.wrongEmailFromPopup.show();
	}

	getFeatureUnavailableHandler(callbackSuccess, callbackFailure, extraData, data) {
		if (extraData) {
			Object.assign(data, extraData);
		}
		if (BX.Sender.B24License)
		{
			BX.Sender.B24License.showPopup('Ad');
		}
		callbackFailure(data);
	}

	needAcceptAgreementHandler(callbackSuccess, callbackFailure, extraData, data) {
		if (extraData) {
			Object.assign(data, extraData);
		}
		Runtime.loadExtension('sender_agreement').then(() => {
			EventEmitter.subscribe('BX.Sender.Agreement:onAccept', () => {
				callbackSuccess();
			});
			BX.Sender.Agreement.isAccepted = false;
			BX.Sender.Agreement.showPopup();
		});
	}
}