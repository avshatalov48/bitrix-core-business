import { ServiceConstructor } from './services/base-service';
import { type BaseService } from './services/base-service';

import { Youtube } from './services/youtube';
import { Facebook } from './services/facebook';
import { Vimeo } from './services/vimeo';
import { Instagram } from './services/instagram';
import { VK } from './services/vk';
import { Rutube } from './services/rutube';

export class VideoService
{
	static #services: ServiceConstructor[] = [
		Youtube,
		Facebook,
		Vimeo,
		Instagram,
		VK,
		Rutube,
	];

	static createByUrl(url: string): BaseService
	{
		for (const ServiceClass of this.#services)
		{
			if (ServiceClass.matchByUrl(url))
			{
				return new ServiceClass(url);
			}
		}

		return null;
	}

	static createByHost(host: string): BaseService
	{
		for (const ServiceClass of this.#services)
		{
			if (ServiceClass.getDomains().includes(host))
			{
				return new ServiceClass(host);
			}
		}

		return null;
	}

	static getEmbeddedUrl(url: string): string | null
	{
		const videoService = this.createByUrl(url);
		if (videoService)
		{
			return videoService.getEmbeddedUrl();
		}

		return null;
	}
}
