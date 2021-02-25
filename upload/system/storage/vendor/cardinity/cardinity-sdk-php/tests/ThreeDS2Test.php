<?php

namespace Cardinity\Tests;

use Cardinity\Exception;
use Cardinity\Method\Payment;
use Cardinity\Method\ResultObject;


class ThreeDS2Test extends ClientTestCase
{
    /**
     * @dataProvider optionalBrowserInfoProvider
     */
    // public function testBrowserInfoOptionalParams($args)
    // {
    //     $browserInfo = $this->getBrowserInfo($args);
    //     $testBrowserInfo = $this->browserInfoProvider($args);
    //     foreach ($browserInfo as $k => $v) $this->assertEquals($v, $testBrowserInfo[$k]);
    // }

    public function optionalBrowserInfoProvider()
    {
        return [
            [['ip_address' => '0.0.0.0']],
            [['javascript_enabled' => false]],
            [['javascript_enabled' => true]],
            [['java_enabled' => false]],
            [['java_enabled' => true]],
            [['ip_address' => '0.0.0.0', 'javascript_enabled' => true]],
            [['ip_address' => '0.0.0.0', 'java_enabled' => true]],
            [['ip_address' => '0.0.0.0', 'java_enabled' => false, 'javascript_enabled' => true]],
            [['java_enabled' => false, 'javascript_enabled' => true]],
        ];
    }


    public function optionalAddressProvider()
    {
        return [
            [['address_line2' => 'address line 2']],
            [['address_line3' => 'address line 3']],
            [['state' => 'vilnius']],
            [['address_line2' => 'address line 2', 'state' => 'vilnius']],
        ];
    }

    // public function testThreeDS2DataParams()
    // {
    //     $treeDS2Data = $this->getThreeDS2DataMandatoryData();
    //     $test3DS2Data = $this->ThreeDS2DataProvider();

    //     foreach ($treeDS2Data as $key => $val) {
    //         if (!is_array($val)) $this->assertEquals($val, $test3DS2Data[$key]);
    //         elseif ($val) {
    //             foreach ($val as $k => $v)
    //                 if ($v) $this->assertEquals($v, $test3DS2Data[$k]);
    //         }
    //     }
    // }

    public function testClientCallSuccess()
    {
        // $payment = $this->getPayment();
        // $card = $this->getCard();
        // $payment->setPaymentInstrument($card);

        // $info = new Payment\AuthorizationInformation();
        // $info->setUrl('http://...');
        // $info->setData('some_data');
        // $payment->setAuthorizationInformation($info);
        
        $threeDS2Data = $this->getThreeDS2DataMandatoryData();
        unset($threeDS2Data['notification_url']);
        // $billingAddress = $this->getAddress();
        // $threeDS2Data['billing_address'] = $billingAddress;

        // $payment->setThreeDS2Data($threeDS2Data);
        // $testPayment = $this->ThreeDS2PaymentProvider();
        $method = new Payment\Create([
            'amount' => 59.01,
            'currency' => 'EUR',
            'settle' => true,
            'description' => '3ds2-Testing-for-pass',
            'order_id' => 'orderid123',
            'country' => 'LT',
            'payment_method' => Payment\Create::CARD,
            'payment_instrument' => [
                'pan' => '5454545454545454',
                'exp_year' => date('Y') + 4,
                'exp_month' => 12,
                'cvc' => '456',
                'holder' => 'Shb Mike Dough'
            ],
            'threeds2_data' => $threeDS2Data
        ]);

        try {
            $payment = $this->client->call($method);
            $this->assertEquals('pending', $payment->getStatus());
        } catch (Exception\Declined $exception) {
            /** @type Cardinity\Method\Payment\Payment */
            $payment = $exception->getResult();
            $status = $payment->getStatus(); // value will be 'declined'
            $errors = $exception->getErrors(); // list of errors occurred
        } catch (Exception\ValidationFailed $exception) {
            /** @type Cardinity\Method\Payment\Payment */
            $payment = $exception->getResult();
            $status = $payment->getStatus(); // value will be 'declined'
            $errors = $exception->getErrors(); // list of errors occurred
        } catch (Cardinity\Exception\InvalidAttributeValue $exception) {
            /** @type Cardinity\Method\Payment\Payment */
            // $payment = $exception->getResult();
            // $status = $payment->getStatus(); // value will be 'declined'
            $errors = $exception->getErrors(); // list of errors occurred
        }
        // finally { echo'this is finally'; }
        if (isset($errors)) {
            $this->assertContains('[threeds2_data][notification_url]',$errors);
        }
    }

    public function ThreeDS2PaymentProvider()
    {
        $paymentArr = $this->getPaymentParams();
        $paymentArr['threeds2_data'] = $this->ThreeDS2DataProvider();
        return $paymentArr;
    }
    
    public function ThreeDS2DataProvider(array $args = [])
    {
        $data = [
            "notification_url" => "https://notification.url/",
            "browser_info"=> $this->browserInfoProvider()
        ];
        if ($args) array_push($data, $args);

        return $data;
    }

    public function addressProvider(array $args = [])
    {
        $address = [
            "address_line1"=>"adress 1",
            "city"=>"city",
            "country"=>"LT",
            "postal_code"=>"02245"
        ];
        if ($args) foreach ($args as $k => $v) $address[$k] = $v;

        return $address;
    }

    public function browserInfoProvider(array $args = [])
    {
        $info = [
            "accept_header"=>"HTTP accept header.",
            "browser_language"=>"cardholder language IETF BCP 47.",
            "screen_width"=>600,
            "screen_height"=>400,
            "challenge_window_size"=>"600x400",
            "user_agent"=>"agent James Bond",
            "color_depth"=>24,
            "time_zone"=>-60
        ];
        if ($args) foreach ($args as $k => $v) $info[$k] = $v;
        return $info;
    }
}