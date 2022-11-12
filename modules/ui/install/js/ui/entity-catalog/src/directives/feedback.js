import {Event} from "main.core";
import "ui.feedback.form";

export const feedback = {
	beforeMount(element: HTMLElement, bindings): void
	{
		Event.bind(element, 'click', (event) => {
			event.preventDefault();

			BX.UI.Feedback.Form.open(bindings.value);
		});
	}
};
