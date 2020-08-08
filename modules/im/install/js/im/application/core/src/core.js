/**
 * Bitrix Im
 * Core application
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */
import {Controller} from "im.controller";
import "im.application.launch";

class CoreApplication
{
	constructor()
	{
		this.controller = new Controller();
	}

	ready()
	{
		return this.controller.ready();
	}
}

let Core = new CoreApplication();
export {Core};