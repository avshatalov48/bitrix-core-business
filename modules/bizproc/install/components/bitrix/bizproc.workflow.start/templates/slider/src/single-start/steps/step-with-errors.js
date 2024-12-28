import { Type } from 'main.core';

import { ErrorNotifier } from '../../components/error-notifier';

import { Step } from './step';

export class StepWithErrors extends Step
{
	errorNotifier: ErrorNotifier;

	constructor(config)
	{
		super(config);

		this.errorNotifier = new ErrorNotifier({});
	}

	renderErrors(): HTMLElement
	{
		return this.errorNotifier.render();
	}

	showErrors(errors)
	{
		if (Type.isArrayFilled(errors))
		{
			this.errorNotifier.errors = errors;
			this.errorNotifier.show();
		}
	}

	cleanErrors()
	{
		this.errorNotifier.errors = [];
		this.errorNotifier.clean();
	}
}
