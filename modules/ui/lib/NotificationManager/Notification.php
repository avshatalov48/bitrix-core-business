<?php

namespace Bitrix\UI\NotificationManager;

use Bitrix\Main\ArgumentException;
use Bitrix\UI\NotificationManager\Helpers\Uuid;

final class Notification implements \JsonSerializable
{
	private const SEPARATOR = 'u1F9D1';

	private $uid;
	private $category;
	private $title;
	private $text;
	private $icon;
	private $inputPlaceholderText;
	private $button1Text;
	private $button2Text;

	/**
	 * Value object of notification to send via NotificationManager
	 * @see \Bitrix\UI\NotificationManager\NotificationManager
	 *
	 * @param array $options = [
	 *     'id' => '', //required filled string
	 *     'category' => '', //optional
	 *     'title' => '', //optional
	 *     'text' => '', //optional
	 *     'icon' => '', //optional
	 *     'inputPlaceholderText' => '', //optional
	 *     'button1Text' => '', //optional
	 *     'button2Text' => '', //optional
	 * ];
	 *
	 * @throws ArgumentException
	 */
	public function __construct(array $options)
	{
		$this->setUid($options['id']);
		$this->setCategory($options['category']);
		$this->setTitle($options['title']);
		$this->setText($options['text']);
		$this->setIcon($options['icon']);
		$this->setInputPlaceholderText($options['inputPlaceholderText']);
		$this->setButton1Text($options['button1Text']);
		$this->setButton2Text($options['button2Text']);
	}

	private function setUid($id): void
	{
		$id = (string)$id;
		if ($id === '')
		{
			throw new ArgumentException('NotificationManager: Cannot create a notification without an ID');
		}

		$this->uid = $id . self::SEPARATOR . Uuid::getV4();
	}

	private function setCategory($category): void
	{
		$this->category = (string)$category;
	}

	private function setTitle($title): void
	{
		$this->title = (string)$title;
	}

	private function setText($text): void
	{
		$this->text = (string)$text;
	}

	private function setIcon($icon): void
	{
		$this->icon = (string)$icon;
	}

	private function setInputPlaceholderText($inputPlaceholderText): void
	{
		$this->inputPlaceholderText = (string)$inputPlaceholderText;
	}

	private function setButton1Text($button1Text): void
	{
		$this->button1Text = (string)$button1Text;
	}

	private function setButton2Text($button2Text): void
	{
		$this->button2Text = (string)$button2Text;
	}

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->uid,
			'category' => $this->category,
			'title' => $this->title,
			'text' => $this->text,
			'icon' => $this->icon,
			'inputPlaceholderText' => $this->inputPlaceholderText,
			'button1Text' => $this->button1Text,
			'button2Text' => $this->button2Text,
		];
	}
}