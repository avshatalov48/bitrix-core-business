<?php

namespace Bitrix\Main\Engine;


class DefaultController extends Controller
{
	public function configureActions()
	{
		return array(
			'index' => array(
				'prefilters' => array(
					new ActionFilter\Authentication,
					new ActionFilter\HttpMethod(array('GET')),
				),
			),
		);
	}

	public function indexAction()
	{
		return "It's default behavior";
	}
}