import { BaseError, Type, Loc } from 'main.core';

export default class UploaderError extends BaseError
{
	description: string = '';
	origin: string = 'client';

	constructor(code: string, ...args)
	{
		let message = Type.isString(args[0]) ? args[0] : null;
		let description = Type.isString(args[1]) ? args[1] : null;
		const customData = Type.isPlainObject(args[args.length - 1]) ? args[args.length - 1] : {};

		const replacements = {};
		Object.keys(customData).forEach((key: string) => {
			replacements[`#${key}#`] = customData[key];
		});

		if (!Type.isString(message) && Loc.hasMessage(`UPLOADER_${code}`))
		{
			message = Loc.getMessage(`UPLOADER_${code}`, replacements);
		}

		if (Type.isStringFilled(message) && !Type.isString(description) && Loc.hasMessage(`UPLOADER_${code}_DESC`))
		{
			description = Loc.getMessage(`UPLOADER_${code}_DESC`, replacements);
		}

		super(message, code, customData);
		this.setDescription(description);
	}

	static createFromAjaxErrors(errors: Array): UploaderError
	{
		if (!Type.isArrayFilled(errors) || !Type.isPlainObject(errors[0]))
		{
			return new this('SERVER_ERROR');
		}

		const uploaderError = errors.find(error => {
			return error.type === 'file-uploader';
		});

		if (uploaderError && !uploaderError.system)
		{
			// Take the First Uploader User Error
			const { code, message, description, customData } = uploaderError;
			const error = new this(code, message, description, customData);
			error.setOrigin('server');

			return error;
		}
		else
		{
			let { code, message, description, customData } = errors[0];
			if (code === 'NETWORK_ERROR')
			{
				message = Loc.getMessage('UPLOADER_NETWORK_ERROR');
			}
			else
			{
				code = Type.isStringFilled(code) ? code : 'SERVER_ERROR';
				if (!Type.isStringFilled(description))
				{
					description = message;
					message = Loc.getMessage('UPLOADER_SERVER_ERROR');
				}
			}

			console.error('Uploader', errors);

			const error = new this(code, message, description, customData);
			error.setOrigin('server');

			return error;
		}
	}

	getDescription(): string
	{
		return this.description;
	}

	setDescription(text: string): this
	{
		if (Type.isString(text))
		{
			this.description = text;
		}

		return this;
	}

	getOrigin(): string
	{
		return this.origin;
	}

	setOrigin(origin: string): this
	{
		if (Type.isStringFilled(origin))
		{
			this.origin = origin;
		}

		return this;
	}

	clone(): UploaderError
	{
		const options = JSON.parse(JSON.stringify(this));
		const error = new UploaderError(
			options.code,
			options.message,
			options.description,
			options.customData
		);

		error.setOrigin(options.origin);

		return error;
	}

	toJSON(): { [key: string]: any }
	{
		return {
			code: this.getCode(),
			message: this.getMessage(),
			description: this.getDescription(),
			origin: this.getOrigin(),
			customData: this.getCustomData(),
		};
	}
}
