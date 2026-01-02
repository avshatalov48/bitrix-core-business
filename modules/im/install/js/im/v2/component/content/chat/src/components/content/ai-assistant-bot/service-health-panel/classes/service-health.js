import { ajax as Ajax, Loc } from 'main.core';

import { Core } from 'im.v2.application.core';

export type ServiceHealthResponse = {
	result: 'notice' | 'warning' | 'error',
	info: string,
	statusTitle: string,
	statusInfo: string,
	id: number,
};

export class HealthCheckService
{
	getEndpoint(): string
	{
		const langId = Loc.getMessage('LANGUAGE_ID');
		const { serviceHealthUrl } = Core.getApplicationData();

		const url = new URL(serviceHealthUrl);
		url.searchParams.set('userLang', langId);

		return url.toString();
	}

	async getServiceHealthStatus(): Promise<ServiceHealthResponse | null>
	{
		const endpoint = this.getEndpoint();

		return new Promise((resolve) => {
			Ajax.get(endpoint, (rawJson: string) => {
				let response = {};

				try
				{
					response = JSON.parse(rawJson);
				}
				catch
				{
					resolve(null);

					return;
				}

				if (response.result !== 'error')
				{
					resolve(null);

					return;
				}

				resolve(response);
			});
		});
	}
}
