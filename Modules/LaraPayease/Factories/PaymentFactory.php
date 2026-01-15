<?php

namespace Modules\LaraPayease\Factories;
use Modules\LaraPayease\Drivers\Stripe;
use Modules\LaraPayease\Drivers\Paystack;
use Modules\LaraPayease\Utils\CurrencyUtil;

class PaymentFactory {

   /**
    * @return \Modules\LaraPayease\Drivers\Stripe
    */

    public function stripe(): Stripe{
        return new Stripe();
     }

    /**
     * @return \Modules\LaraPayease\Drivers\Paystack
     */
    public function paystack(): Paystack{
        return new Paystack();
     }

     /**
      * @return \Modules\LaraPayease\Utils\CurrencyUtil\supportedCurrencies
      */

     public function supportedCurrencies(): array{
        return CurrencyUtil::$supportedCurrencies;
     }

      /**
       * @return array $supportedGateways
       */
     public function supportedGateways() : array{
        return [
           'stripe' => [
              'keys' => [
                 'stripe_key' => '',
                 'stripe_secret' => '',
              ],
              'status' => 'off',
              'currency' => 'USD',
              'exchange_rate' => '',
              'ipn_url_type' => 'get.success'
          ],
          'paystack' => [
              'keys' => [
                 'paystack_public_key' => '',
                 'paystack_secret_key' => '',
              ],
              'status' => 'off',
              'currency' => 'NGN',
              'exchange_rate' => '',
              'ipn_url_type' => 'get.success'
          ],
        ];
     }

      /**
       * @return string $getIpnUrl
       */
     public function getIpnUrl($method): string {
          $gateWays = $this->supportedGateways();
          if(!empty($gateWays[$method]['ipn_url_type'])) {
              return $gateWays[$method]['ipn_url_type'];
          }
          return '';
     }
}
