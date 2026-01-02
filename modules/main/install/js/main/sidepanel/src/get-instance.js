import { SliderManager } from './slider-manager';
import { PageObject } from 'main.pageobject';

let instance = null;

export function getInstance(): SliderManager
{
	const topWindow = PageObject.getRootWindow();
	if (topWindow !== window)
	{
		return topWindow.BX.SidePanel.Instance;
	}

	if (instance === null)
	{
		instance = new SliderManager();
	}

	return instance;
}
