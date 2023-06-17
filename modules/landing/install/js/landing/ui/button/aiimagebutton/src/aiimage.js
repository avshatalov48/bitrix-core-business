import {BaseButton} from "landing.ui.button.basebutton";

import './css/ai_image.css';
import 'ui.fonts.opensans';

/**
 * @memberOf BX.Landing.UI.Button
 */
export class AiImageButton extends BaseButton
{
	constructor(id: string, options: {})
	{
		super(id, options);
		this.layout.classList.add("landing-ui-button-ai-image");
	}
}