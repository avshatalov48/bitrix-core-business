import { Type, Dom } from 'main.core';
import { Alert } from 'ui.alerts';

export class AlertManager
{
	constructor(
		params: {
			errorContainerId: string,
		}
	)
	{
		if (!Type.isStringFilled(params.errorContainerId))
		{
			return;
		}

		this.globalErrorContainer = document.getElementById(params.errorContainerId);

		this.nodeAlerts = new Map();
	}

	showAlert(text, targetNode)
	{
		if (Type.isDomNode(targetNode))
		{
			targetNode.classList.add('ui-ctl-danger');
		}
		else
		{
			targetNode = this.globalErrorContainer;
		}


		const textAlert = new Alert({
			color: Alert.Color.DANGER,
			animate: true,
		});

		this.nodeAlerts.set(targetNode, textAlert);

		setTimeout(() => {
			targetNode.parentNode.insertBefore(textAlert.getContainer(), targetNode.nextSibling);
			textAlert.setText(text);

			window.scrollTo({
				top: Dom.getPosition(targetNode).top,
				behavior: 'smooth',
			});
		}, 500);

	}

	hideAllAlerts()
	{
		this.nodeAlerts.forEach((textAlert, targetNode) => {
			textAlert.hide();

			if (Type.isDomNode(targetNode))
			{
				targetNode.classList.remove('ui-ctl-danger');
			}
		});

		this.nodeAlerts.clear();
	}
}
