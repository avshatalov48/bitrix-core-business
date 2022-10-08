<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp\Old;

class EmojiConverter extends \Bitrix\MessageService\Providers\Edna\WhatsApp\EmojiConverter
{
	protected function convertKeyboardSection(?array $keyboardSection, string $type): array
	{
		if (
			isset($keyboardSection['row']['buttons'])
			&& is_array($keyboardSection['row']['buttons'])
		)
		{
			foreach ($keyboardSection['row']['buttons'] as $index => $button)
			{
				if (isset($button['text']))
				{
					$keyboardSection['row']['buttons'][$index]['text'] = $this->convertEmoji($button['text'], $type);
				}
			}
		}

		return $keyboardSection;
	}

}