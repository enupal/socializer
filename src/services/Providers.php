<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\services;

use Craft;
use craft\db\Query;
use enupal\socializer\elements\Provider;
use Hybridauth\Provider\Facebook;
use Hybridauth\Provider\LinkedIn;
use Hybridauth\Provider\Twitter;
use Hybridauth\Provider\Google;
use yii\base\Component;
use enupal\socializer\Socializer;
use yii\db\Exception;

class Providers extends Component
{
    /**
     * @return array
     */
    public function getAllProviderTypes()
    {
        return [
            Facebook::class,
            Twitter::class,
            Google::class,
            LinkedIn::class
        ];
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getProviderTypesAsOptions()
    {
        $providers = $this->getAllProviderTypes();
        $asOptions = [];
        foreach ($providers as $provider) {
            $option = [
                "label" => $this->getClassNameFromNamespace($provider),
                "value" => $provider
            ];
            $asOptions[] = $option;
        }

        return $asOptions;
    }

    /**
     * @param $class
     * @return string
     * @throws \ReflectionException
     */
    public function getClassNameFromNamespace($class)
    {
        return (new \ReflectionClass($class))->getShortName();
    }

    /**
     * Returns a Provider model if one is found in the database by id
     *
     * @param int $id
     * @param int $siteId
     *
     * @return null|Provider
     */
    public function getProviderById(int $id, int $siteId = null)
    {
        /** @var Provider $provider */
        $provider = Craft::$app->getElements()->getElementById($id, Provider::class, $siteId);

        return $provider;
    }

    /**
     * @param string $handle
     * @param int|null $siteId
     * @return array|Provider|null
     */
    public function getProviderByHandle(string $handle, int $siteId = null)
    {
        $query = Provider::find();
        $query->handle($handle);
        $query->siteId($siteId);

        return $query->one();
    }

    /**
     * @param string $type
     * @param int|null $siteId
     * @return array|Provider|null
     */
    public function getProviderByType(string $type, int $siteId = null)
    {
        $query = Provider::find();
        $query->type($type);
        $query->siteId($siteId);

        return $query->one();
    }

    /**
     * @param string $name
     * @param string $handle
     * @param string $type
     *
     * @return Provider
     * @throws \Exception
     * @throws \Throwable
     */
    public function createNewProvider(string $name, string $handle, string $type): Provider
    {
        $paymentForm = new Provider();

        $paymentForm->name = $name;
        $paymentForm->handle = $handle;
        $paymentForm->hasUnlimitedStock = 1;
        $paymentForm->enableBillingAddress = 0;
        $paymentForm->enableShippingAddress = 0;
        $paymentForm->customerQuantity = 0;
        $paymentForm->buttonClass = 'enupal-stripe-button';
        $paymentForm->amountType = AmountType::ONE_TIME_SET_AMOUNT;
        $paymentForm->currency = $settings->defaultCurrency ? $settings->defaultCurrency : 'USD';
        $paymentForm->enabled = 1;
        $paymentForm->language = 'en';

        // Set default variant
        $paymentForm = $this->addDefaultVariant($paymentForm);

        $this->savePaymentForm($paymentForm);

        return $paymentForm;
    }
}