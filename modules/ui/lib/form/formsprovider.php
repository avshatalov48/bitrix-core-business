<?php

namespace Bitrix\UI\Form;

/* @deprecated use Bitrix\UI\Form\FormProvider instead */
class FormsProvider
{
    /* @deprecated use (new Bitrix\UI\Form\FormProvider)->getPartnerFormList() instead */
	public static function getForms(): array
    {
        return (new FormProvider())->getPartnerFormList();
    }
}
