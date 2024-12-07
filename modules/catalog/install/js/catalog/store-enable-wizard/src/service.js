import { ajax } from 'main.core';

class Service
{
	static enable(config: Object): Promise
	{
		return new Promise((resolve, reject) => {
			ajax.runAction('catalog.config.inventoryManagementEnable', config)
				.then((response) => resolve(response))
				.catch((response) => reject(response.errors[0]));
		});
	}

	static disable(): Promise
	{
		return new Promise((resolve, reject) => {
			ajax.runAction('catalog.config.inventoryManagementDisable')
				.then((response) => resolve())
				.catch((response) => reject(response.errors[0]));
		});
	}

	static isOnecAppInstalled(): Promise
	{
		return new Promise((resolve, reject) => {
			ajax.runComponentAction('bitrix:catalog.store.enablewizard', 'getOnecApp', {
				mode: 'class',
			})
				.then((response) => resolve(Boolean(response?.data?.isInstalled)))
				.catch((e) => reject(e))
			;
		});
	}
}

export {
	Service,
};
