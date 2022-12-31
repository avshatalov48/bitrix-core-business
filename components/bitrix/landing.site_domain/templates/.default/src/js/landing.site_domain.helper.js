import {Dom, Loc} from 'main.core';

export class Helper
{
	static DEFAULT_LENGTH_LIMIT = 63;

	/**
	 * Constructor.
	 */
	constructor(params)
	{
		this.idDomainName = params.idDomainName;
		this.idDomainMessage = params.idDomainMessage;
		this.idDomainLoader = params.idDomainLoader;
		this.idDomainLength = params.idDomainLength || null;
		this.idDomainErrorAlert = params.idDomainErrorAlert;

		this.classes = {
			dangerBorder: 'ui-ctl-danger',
			successBorder: 'ui-ctl-success',
			dangerAlert: 'landing-domain-alert-danger',
			successAlert: 'landing-domain-alert-success'
		};

		if (this.idDomainName)
		{
			this.idDomainNameParent = this.idDomainName.parentNode;
		}
	}
	/**
	 * Shows loader div near input.
	 */
	showLoader()
	{
		this.clearMessage();
		this.hideLength();
		Dom.show(this.idDomainLoader);
	}

	/**
	 * Hides loader div near input.
	 */
	hideLoader()
	{
		this.showLength();
		Dom.hide(this.idDomainLoader);
	}

	/**
	 * Returns true if loader showed.
	 * @return {boolean}
	 */
	isLoaderShowed()
	{
		return this.idDomainLoader && this.idDomainLoader.style.display !== 'none';
	}

	setLength(length: number, limit: number = Helper.DEFAULT_LENGTH_LIMIT)
	{
		if (this.idDomainLength)
		{
			this.idDomainLength.innerHTML = Loc.getMessage('LANDING_TPL_DOMAIN_LENGTH_LIMIT', {
				'#LENGTH#': length,
				'#LIMIT#': limit,
			});
		}
		Dom.show(this.idDomainLength);
	}

	hideLength()
	{
		if (this.idDomainLength)
		{
			Dom.hide(this.idDomainLength);
		}
	}

	showLength()
	{
		if (this.idDomainLength)
		{
			Dom.show(this.idDomainLength);
		}
	}

	/**
	 * Marks input with success class.
	 * @param {string} successMessage Success message.
	 */
	setSuccess(successMessage)
	{
		if (this.idDomainErrorAlert)
		{
			Dom.hide(this.idDomainErrorAlert);
		}
		this.setMessage(successMessage);
	}

	/**
	 * Sets error message on error occurred or hide message if errorMessage is empty.
	 * @param {string} errorMessage Error message.
	 */
	setError(errorMessage)
	{
		this.setMessage(errorMessage, true);
	}

	/**
	 * Returns true if error message showed.
	 * @return {boolean}
	 */
	isErrorShowed()
	{
		return this.idDomainMessage &&
			Dom.hasClass(this.idDomainMessage, this.classes.dangerAlert) &&
			this.idDomainMessage.style.display !== 'none';
	}

	/**
	 * Sets success or fail message.
	 * @param {string} message Error message.
	 * @param {boolean} error Error message (false by default).
	 */
	setMessage(message: string, error: boolean)
	{
		if (!this.idDomainMessage)
		{
			return;
		}
		error = !!error;
		this.clearMessage();
		if (message)
		{
			if (this.idDomainNameParent)
			{
				Dom.addClass(
					this.idDomainNameParent,
					error
						? this.classes.dangerBorder
						: this.classes.successBorder
				);
			}
			Dom.addClass(
				this.idDomainMessage,
				error
					? this.classes.dangerAlert
					: this.classes.successAlert
			);
			Dom.show(this.idDomainMessage);
			this.idDomainMessage.innerHTML = message;
		}
	}

	/**
	 * Clears message alert.
	 */
	clearMessage()
	{
		if (!this.idDomainMessage)
		{
			return;
		}

		if (this.idDomainNameParent)
		{
			Dom.removeClass(this.idDomainNameParent, this.classes.dangerBorder);
			Dom.removeClass(this.idDomainNameParent, this.classes.successBorder);
		}
		Dom.removeClass(this.idDomainMessage, this.classes.dangerAlert);
		Dom.removeClass(this.idDomainMessage, this.classes.successAlert);

		this.idDomainMessage.innerHTML = '';
	}
}