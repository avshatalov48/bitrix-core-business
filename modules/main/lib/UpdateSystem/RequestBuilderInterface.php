<?php
namespace Bitrix\Main\UpdateSystem;

interface RequestBuilderInterface
{
	public function setHeaders(): self;
	public function setUrl(): self;
	public function setProxy(): self;
	public function setBody(): self;
	public function build(): Request;
}