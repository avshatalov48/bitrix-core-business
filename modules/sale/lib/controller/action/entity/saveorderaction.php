<?php
namespace Bitrix\Sale\Controller\Action\Entity;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Crm;
use Bitrix\Crm\Order\Manager;

/**
 * Class SaveOrderAction
 * @package Bitrix\Sale\Controller\Action\Entity
 * @example BX.ajax.runAction("sale.entity.saveOrder", { data: { fields: { siteId:'s1', [userId:1, personTypeId:1] properties: {...}}}});
 * @internal
 */
final class SaveOrderAction extends Sale\Controller\Action\BaseAction
{
	private ?int $compilationDealId;

	/**
	 * @param array $fields
	 * @return array|null
	 */
	public function run(array $fields)
	{
		$checkFieldsResult = $this->checkFields($fields);
		if (!$checkFieldsResult->isSuccess())
		{
			$this->addErrors($checkFieldsResult->getErrors());
			return null;
		}

		$saveOrderResult = $this->saveOrder($fields);
		if ($saveOrderResult->isSuccess())
		{
			$saveOrderData = $saveOrderResult->getData();

			/** @var Sale\Order $order */
			$order = $saveOrderData['ORDER'];

			/** @var array $user */
			$user = $saveOrderData['USER'];

			return array_merge(
				Sale\Helpers\Controller\Action\Entity\Order::getAggregateOrder($order),
				[
					'USER' => $user,
					'HASH' => $order->getHash(),
				]
			);
		}

		$this->addErrors($saveOrderResult->getErrors());
		return null;
	}

	private function saveOrder(array $fields)
	{
		$result = new Sale\Result();

		$resultData = [];

		$prepareOrderResult = $this->prepareOrder($fields);
		if ($prepareOrderResult->isSuccess())
		{
			$getOrderData = $prepareOrderResult->getData();

			/** @var Sale\Order $order */
			$order = $getOrderData['order'];

			$setUserResult = $this->setUser($order, $fields);
			if ($setUserResult->isSuccess())
			{
				$resultData['USER'] = $setUserResult->getData();
			}
			else
			{
				$this->fillErrorCollection(
					$result,
					$setUserResult->getErrors(),
					Sale\Controller\ErrorEnumeration::SAVE_ORDER_ACTION_SET_USER
				);
				return $result;
			}

			$userProfileData = $fields['USER_PROFILE'] ?? null;
			$setProfileResult = $this->setProfile($order, $userProfileData);
			if (!$setProfileResult->isSuccess())
			{
				$this->fillErrorCollection(
					$result,
					$setProfileResult->getErrors(),
					Sale\Controller\ErrorEnumeration::SAVE_ORDER_ACTION_SET_PROFILE
				);
				return $result;
			}

			$doFinalActionsResult = $this->doFinalActions($order);
			if (!$doFinalActionsResult->isSuccess())
			{
				$this->fillErrorCollection(
					$result,
					$doFinalActionsResult->getErrors(),
					Sale\Controller\ErrorEnumeration::SAVE_ORDER_ACTION_FINAL_ACTIONS
				);
				return $result;
			}

			$saveResult = $order->save();
			if (!$saveResult->isSuccess())
			{
				$this->fillErrorCollection(
					$result,
					$saveResult->getErrors(),
					Sale\Controller\ErrorEnumeration::SAVE_ORDER_ACTION_SAVE
				);
				return $result;
			}

			if (
				Main\Loader::includeModule('crm')
				&& class_exists(Crm\Integration\CompilationManager::class)
			)
			{
				if (isset($this->compilationDealId))
				{
					Manager::copyOrderProductsToDeal($order, $this->compilationDealId);
				}

				Crm\Integration\CompilationManager::sendOrderBoundEvent($order);
				Crm\Integration\CompilationManager::sendToCompilationDealTimeline($order);
			}

			$resultData['ORDER'] = $order;
			$result->setData($resultData);
		}
		else
		{
			$result->addErrors($prepareOrderResult->getErrors());
		}

		return $result;
	}

	private function checkFields(array $fields): Sale\Result
	{
		$result = new Sale\Result();

		if (empty($fields['SITE_ID']))
		{
			$result->addError(
				new Main\Error(
					'siteId not found',
					Sale\Controller\ErrorEnumeration::SAVE_ORDER_ACTION_SITE_ID_NOT_FOUND
				)
			);
		}

		if (empty($fields['FUSER_ID']) || (int)$fields['FUSER_ID'] <= 0)
		{
			$result->addError(
				new Main\Error(
					'fuserId not found',
					Sale\Controller\ErrorEnumeration::SAVE_ORDER_ACTION_FUSER_ID_NOT_FOUND
				)
			);
		}

		if (empty($fields['PERSON_TYPE_ID']))
		{
			$result->addError(
				new Main\Error(
					'personTypeId not found',
					Sale\Controller\ErrorEnumeration::SAVE_ORDER_ACTION_PERSON_TYPE_ID_NOT_FOUND
				)
			);
		}

		return $result;
	}

	private function prepareOrder(array $fields): Sale\Result
	{
		$result = new Sale\Result();

		$order = $this->createOrder($fields['SITE_ID']);

		$personTypeId = (int)$fields['PERSON_TYPE_ID'];
		$setPersonTypeIdResult = $this->setPersonTypeId($order, $personTypeId);
		if (!$setPersonTypeIdResult->isSuccess())
		{
			$this->fillErrorCollection(
				$result,
				$setPersonTypeIdResult->getErrors(),
				Sale\Controller\ErrorEnumeration::SAVE_ORDER_ACTION_SET_PERSON_TYPE_ID
			);
			return $result;
		}

		if (!empty($fields['TRADING_PLATFORM_ID']))
		{
			$tradingPlatformId = $fields['TRADING_PLATFORM_ID'];
			$setTradeBindingResult = $this->setTradeBinding($order, $tradingPlatformId);
			if (!$setTradeBindingResult->isSuccess())
			{
				$this->fillErrorCollection(
					$result,
					$setTradeBindingResult->getErrors(),
					Sale\Controller\ErrorEnumeration::SAVE_ORDER_ACTION_SET_TRADE_BINDINGS
				);
				return $result;
			}
		}

		$properties = $fields['PROPERTIES'] ?? null;
		if ($properties)
		{
			$setPropertiesResult = $this->setProperties($order, $properties);
			if (!$setPropertiesResult->isSuccess())
			{
				$this->fillErrorCollection(
					$result,
					$setPropertiesResult->getErrors(),
					Sale\Controller\ErrorEnumeration::SAVE_ORDER_ACTION_SET_PROPERTIES
				);
				return $result;
			}
		}

		$setBasketResult = $this->setBasket($order, $fields['FUSER_ID']);
		if (!$setBasketResult->isSuccess())
		{
			$this->fillErrorCollection(
				$result,
				$setBasketResult->getErrors(),
				Sale\Controller\ErrorEnumeration::SAVE_ORDER_ACTION_SET_BASKET
			);
			return $result;
		}

		if (
			Main\Loader::includeModule('crm')
			&& class_exists(Crm\Integration\CompilationManager::class)
		)
		{
			$this->compilationDealId = Crm\Integration\CompilationManager::processOrderForCompilation($order);
		}

		$result->setData(['order' => $order]);
		return $result;
	}

	private function createOrder(string $siteId): Sale\Order
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();

		return $orderClassName::create($siteId);
	}

	private function setPersonTypeId(Sale\Order $order, int $personTypeId): Sale\Result
	{
		$result = new Sale\Result();

		$setPersonTypeIdResult = $order->setPersonTypeId($personTypeId);
		if (!$setPersonTypeIdResult->isSuccess())
		{
			$result->addErrors($setPersonTypeIdResult->getErrors());
		}

		return $result;
	}

	private function setProperties(Sale\Order $order, array $properties): Sale\Result
	{
		$result = new Sale\Result();

		$propertyCollection = $order->getPropertyCollection();
		$setValuesResult = $propertyCollection->setValuesFromPost(['PROPERTIES' => $properties], []);
		if (!$setValuesResult->isSuccess())
		{
			foreach ($setValuesResult->getErrors() as $error)
			{
				$result->addError(new Main\Error($error->getMessage()));
			}
		}

		/** @var Sale\PropertyValue $propValue */
		foreach ($propertyCollection as $propValue)
		{
			if ($propValue->isUtil())
			{
				continue;
			}

			$verifyResult = $propValue->verify();
			if (!$verifyResult->isSuccess())
			{
				foreach ($verifyResult->getErrors() as $error)
				{
					$result->addError(
						new Main\Error($error->getMessage(), 0, ['id' => $propValue->getPropertyId()])
					);
				}
			}

			$checkRequiredValueResult = $propValue->checkRequiredValue($propValue->getPropertyId(), $propValue->getValue());
			if (!$checkRequiredValueResult->isSuccess())
			{
				foreach ($checkRequiredValueResult->getErrors() as $error)
				{
					$result->addError(
						new Main\Error($error->getMessage(), 0, ['id' => $propValue->getPropertyId()])
					);
				}
			}
		}

		return $result;
	}

	private function setBasket(Sale\Order $order, int $fuserId): Sale\Result
	{
		$result = new Sale\Result();

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\Basket $basketClassName */
		$basketClassName = $registry->getBasketClassName();
		$basket = $basketClassName::loadItemsForFUser($fuserId, $order->getSiteId());

		$refreshResult = $basket->refresh();
		if ($refreshResult->isSuccess())
		{
			$saveBasketResult = $basket->save();
			if (!$saveBasketResult->isSuccess())
			{
				$result->addErrors($refreshResult->getErrors());
			}
		}
		else
		{
			$result->addErrors($refreshResult->getErrors());
		}

		if (!$result->isSuccess(true))
		{
			return $result;
		}

		$availableBasket = $basket->getOrderableItems();
		if ($availableBasket && !$availableBasket->isEmpty())
		{
			$setBasketResult = $order->setBasket($availableBasket);
			if (!$setBasketResult->isSuccess())
			{
				$result->addErrors($setBasketResult->getErrors());
			}
		}
		elseif ($availableBasket)
		{
			$result->addError(new Main\Error('basket is empty'));
		}
		else
		{
			$result->addError(new Main\Error('basket is null'));
		}

		return $result;
	}

	private function setUser(Sale\Order $order, array $fields): Sale\Result
	{
		$result = new Sale\Result();

		$isNewUser = false;

		if ((int)($fields['USER_ID'] ?? 0) > 0)
		{
			$userId = (int)$fields['USER_ID'];

			if ($this->isLandingShop($order) && Main\Loader::includeModule('crm'))
			{
				Crm\Service\Sale\Order\BuyerService::getInstance()->attachUserToBuyers($userId);
			}
		}
		else
		{
			$properties = [];

			/** @var Sale\PropertyValue $property */
			foreach ($order->getPropertyCollection() as $property)
			{
				$properties[$property->getPropertyId()] = $property->getValue();
			}

			$userProps = Sale\Property::getMeaningfulValues($order->getPersonTypeId(), $properties);

			$email = $userProps['EMAIL'] ?? '';
			$phone = $userProps['PHONE'] ?? '';

			$userId = $this->searchExistingUser($email, $phone);
			if (!$userId)
			{
				$registerNewUserResult = $this->registerNewUser($order, $userProps);
				if ($registerNewUserResult->isSuccess())
				{
					$registerNewUserData = $registerNewUserResult->getData();
					$userId = $registerNewUserData['userId'];

					$isNewUser = true;
				}
				else
				{
					$result->addErrors($registerNewUserResult->getErrors());
					return $result;
				}
			}
		}

		if (!$userId)
		{
			$result->addError(new Main\Error('User not found'));
			return $result;
		}

		$order->setFieldNoDemand('USER_ID', $userId);

		$result->setData([
			'ID' => $userId,
			'IS_NEW' => $isNewUser,
		]);

		return $result;
	}

	private function searchExistingUser(string $email, string $phone): ?int
	{
		$existingUserId = null;

		if (!empty($email))
		{
			$res = Main\UserTable::getRow([
				'filter' => [
					'=ACTIVE' => 'Y',
					'=EMAIL' => $email,
				],
				'select' => ['ID'],
			]);
			if (isset($res['ID']))
			{
				$existingUserId = (int)$res['ID'];
			}
		}

		if (!$existingUserId && !empty($phone))
		{
			$normalizedPhone = NormalizePhone($phone);
			$normalizedPhoneForRegistration = Main\UserPhoneAuthTable::normalizePhoneNumber($phone);

			if (!empty($normalizedPhone))
			{
				$res = Main\UserTable::getRow([
					'filter' => [
						'ACTIVE' => 'Y',
						[
							'LOGIC' => 'OR',
							'=PHONE_AUTH.PHONE_NUMBER' => $normalizedPhoneForRegistration,
							'=PERSONAL_PHONE' => $normalizedPhone,
							'=PERSONAL_MOBILE' => $normalizedPhone,
						],
					],
					'select' => ['ID'],
				]);
				if (isset($res['ID']))
				{
					$existingUserId = (int)$res['ID'];
				}
			}
		}

		return $existingUserId;
	}

	private function registerNewUser(Sale\Order $order, array $userProps): Sale\Result
	{
		$result = new Sale\Result();

		$siteId = $order->getSiteId();

		$userData = $this->generateUserData($userProps);
		$fields = [
			'LOGIN' => $userData['NEW_LOGIN'],
			'NAME' => $userData['NEW_NAME'],
			'LAST_NAME' => $userData['NEW_LAST_NAME'],
			'PASSWORD' => $userData['NEW_PASSWORD'],
			'CONFIRM_PASSWORD' => $userData['NEW_PASSWORD_CONFIRM'],
			'EMAIL' => $userData['NEW_EMAIL'],
			'GROUP_ID' => $userData['GROUP_ID'],
			'ACTIVE' => 'Y',
			'LID' => $siteId,
			'PERSONAL_PHONE' => isset($userProps['PHONE']) ? NormalizePhone($userProps['PHONE']) : '',
			'PERSONAL_ZIP' => $userProps['ZIP'] ?? '',
			'PERSONAL_STREET' => $userProps['ADDRESS'] ?? '',
		];

		$userPhoneAuth = Main\Config\Option::get('main', 'new_user_phone_auth', 'N', $siteId) === 'Y';
		if ($userPhoneAuth)
		{
			$normalizedPhoneForRegistration = '';
			if (!empty($userProps['PHONE']))
			{
				$normalizedPhoneForRegistration = Main\UserPhoneAuthTable::normalizePhoneNumber($userProps['PHONE']);
			}

			$fields['PHONE_NUMBER'] = $normalizedPhoneForRegistration;
		}

		if ($this->isLandingShop($order) && Main\Loader::includeModule('crm'))
		{
			$fields['GROUP_ID'] = Crm\Order\BuyerGroup::getDefaultGroups();
			$fields['EXTERNAL_AUTH_ID'] = 'shop';
			$fields['UF_DEPARTMENT'] = [];
			if (!empty($userData['NEW_EMAIL']))
			{
				$fields['LOGIN'] = $userData['NEW_EMAIL'];
			}
		}

		$user = new \CUser();
		$addResult = $user->Add($fields);
		if ((int)$addResult <= 0)
		{
			$errors = explode('<br>', $user->LAST_ERROR);
			TrimArr($errors, true);
			foreach ($errors as $error)
			{
				$result->addError(new Main\Error($error));
			}
		}
		else
		{
			$result->setData(['userId' => $addResult]);
		}

		return $result;
	}

	private function generateUserData(array $userProps = []): array
	{
		$userEmail = isset($userProps['EMAIL']) ? trim((string)$userProps['EMAIL']) : '';
		$newLogin = $userEmail;

		if (empty($userEmail))
		{
			$newEmail = false;

			$normalizedPhone = NormalizePhone($userProps['PHONE']);
			if (!empty($normalizedPhone))
			{
				$newLogin = $normalizedPhone;
			}
		}
		else
		{
			$newEmail = $userEmail;
		}

		if (empty($newLogin))
		{
			$newLogin = Main\Security\Random::getString(5).random_int(0, 99999);
		}

		$pos = mb_strpos($newLogin, '@');
		if ($pos !== false)
		{
			$newLogin = mb_substr($newLogin, 0, $pos);
		}

		if (mb_strlen($newLogin) > 47)
		{
			$newLogin = mb_substr($newLogin, 0, 47);
		}

		$newLogin = str_pad($newLogin, 3, '_');

		$dbUserLogin = \CUser::GetByLogin($newLogin);
		if ($userLoginResult = $dbUserLogin->Fetch())
		{
			do
			{
				$newLoginTmp = $newLogin.random_int(0, 99999);
				$dbUserLogin = \CUser::GetByLogin($newLoginTmp);
			}
			while ($userLoginResult = $dbUserLogin->Fetch());

			$newLogin = $newLoginTmp;
		}

		$newName = '';
		$newLastName = '';

		$payerName = isset($userProps['PAYER']) ? trim((string)$userProps['PAYER']) : '';
		if (!empty($payerName))
		{
			$payerName = preg_replace('/\s{2,}/', ' ', $payerName);
			$nameParts = explode(' ', $payerName);
			if (isset($nameParts[1]))
			{
				$newName = $nameParts[1];
				$newLastName = $nameParts[0];
			}
			else
			{
				$newName = $nameParts[0];
			}
		}

		$groupIds = [];

		$defaultGroups = Main\Config\Option::get('main', 'new_user_registration_def_group', '');
		if (!empty($defaultGroups))
		{
			$groupIds = explode(',', $defaultGroups);
		}

		$newPassword = \CUser::GeneratePasswordByPolicy($groupIds);

		return [
			'NEW_EMAIL' => $newEmail,
			'NEW_LOGIN' => $newLogin,
			'NEW_NAME' => $newName,
			'NEW_LAST_NAME' => $newLastName,
			'NEW_PASSWORD' => $newPassword,
			'NEW_PASSWORD_CONFIRM' => $newPassword,
			'GROUP_ID' => $groupIds,
		];
	}

	private function doFinalActions(Sale\Order $order): Sale\Result
	{
		$result = new Sale\Result();

		$hasMeaningfulFields = $order->hasMeaningfulField();
		$doFinalActionResult = $order->doFinalAction($hasMeaningfulFields);

		if (!$doFinalActionResult->isSuccess())
		{
			$result->addErrors($doFinalActionResult->getErrors());
		}

		return $result;
	}

	private function setTradeBinding(Sale\Order $order, $tradingPlatformId): Sale\Result
	{
		$result = new Sale\Result();

		$platform = Sale\TradingPlatform\Manager::getList([
			'select' => ['ID'],
			'filter' => [
				'=ID' => $tradingPlatformId,
			],
		])->fetch();
		if ($platform)
		{
			$collection = $order->getTradeBindingCollection();

			/** @var Sale\TradeBindingEntity $binding */
			$binding = $collection->createItem();
			$setFieldResult = $binding->setFields([
				'TRADING_PLATFORM_ID' => $tradingPlatformId,
			]);

			if (!$setFieldResult->isSuccess())
			{
				$result->addErrors($setFieldResult->getErrors());
			}
		}
		else
		{
			$result->addError(
				new Main\Error('Trading platform with id:"'.$tradingPlatformId.' not found"')
			);
		}

		return $result;
	}

	private function setProfile(Sale\Order $order, array $profileFields = null): Sale\Result
	{
		$result = new Sale\Result();

		$profileId = $profileFields['ID'] ?? 0;
		$profileName = $profileFields['NAME'] ?? '';

		$properties = [];
		/** @var Sale\PropertyValue $property */
		foreach ($order->getPropertyCollection() as $property)
		{
			$properties[$property->getPropertyId()] = $property->getValue();
		}

		$errors = [];
		\CSaleOrderUserProps::DoSaveUserProfile(
			$order->getUserId(),
			$profileId,
			$profileName,
			$order->getPersonTypeId(),
			$properties,
			$errors
		);

		foreach ($errors as $error)
		{
			$result->addError(new Main\Error($error['TEXT']));
		}

		return $result;
	}

	private function isLandingShop(Sale\Order $order): bool
	{
		/** @var Sale\TradeBindingEntity $tradingItem */
		foreach ($order->getTradeBindingCollection() as $tradingItem)
		{
			$platformId = $tradingItem->getField('TRADING_PLATFORM_ID');
			$platform = Sale\TradingPlatform\Manager::getObjectById($platformId);
			if ($platform instanceof Sale\TradingPlatform\Landing\Landing)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param Sale\Result $result
	 * @param Main\Error[] $errors
	 * @param $code
	 */
	private function fillErrorCollection(Sale\Result $result, array $errors, $code): void
	{
		foreach ($errors as $error)
		{
			$result->addError(new Main\Error($error->getMessage(), $code, $error->getCustomData()));
		}
	}
}
