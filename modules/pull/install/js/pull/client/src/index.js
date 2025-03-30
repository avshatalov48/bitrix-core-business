/**
 * Bitrix Push & Pull
 * Pull client
 *
 * @package bitrix
 * @subpackage pull
 * @copyright 2001-2019 Bitrix
 */

/*              !ATTENTION!
 * Do not use Bitrix CoreJS in this package
 * The client can be instantiated on a page without Bitrix Framework
 */

import { PullClient } from './client';

export {
	PullClient,
};

if (!globalThis.BX)
{
	globalThis.BX = {};
}

if (!BX.PULL)
{
	BX.PULL = new PullClient();
}

BX.PullClient = PullClient;
