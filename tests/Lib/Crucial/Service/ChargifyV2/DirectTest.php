<?php

namespace Crucial\Tests\Lib\Crucial\Service\ChargifyV2;

use Crucial\Service\ChargifyV2\Exception\BadMethodCallException;
use Crucial\Tests\Helpers\ClientV2Helper;
use PHPUnit\Framework\TestCase;

class DirectTest extends TestCase
{
    public function testAuthSuccess()
    {
        $chargify = ClientV2Helper::getInstance('v2.authTest.success');
        $direct   = $chargify->direct();
        $success  = $direct->checkAuth();

        $this->assertTrue($success);
    }

    public function testAuthFailure()
    {
        $chargify = ClientV2Helper::getInstance('v2.authTest.error');
        $direct   = $chargify->direct();
        $success  = $direct->checkAuth();

        $this->assertFalse($success);
    }

    public function testSetData()
    {
        $chargify = ClientV2Helper::getInstance();
        $direct   = $chargify->direct();

        // set redirect
        $redirect = 'http://example.local';
        $direct->setRedirect($redirect);

        // set data
        $data = [
            'signup' => [
                'product'  => [
                    'id' => 1234
                ],
                'customer' => [
                    'first_name' => 'Dan',
                    'last_name'  => 'Bowen',
                    'email'      => 'foo@mailinator.com'
                ]
            ]
        ];
        $direct->setData($data);

        // redirect_uri gets merged into data
        $expected = array_merge_recursive($data, ['redirect_uri' => $redirect]);

        $this->assertEquals($expected, $direct->getData());
        $this->assertEquals($redirect, $direct->getRedirect());
    }

    public function testGetRequestSignature()
    {
        $chargify = ClientV2Helper::getInstance();
        $direct   = $chargify->direct();

        $string = $direct->getApiId()
            . $direct->getTimeStamp()
            . $direct->getNonce()
            . $direct->getDataString();

        $signature = hash_hmac('sha1', $string, $chargify->getApiSecret());

        $this->assertEquals($signature, $direct->getRequestSignature());
    }

    public function testGetResponseSignature()
    {
        $chargify   = ClientV2Helper::getInstance();
        $direct     = $chargify->direct();
        $apiId      = $chargify->getApiId();
        $timeStamp  = $direct->getTimeStamp();
        $nonce      = $direct->getNonce();
        $statusCode = '200';
        $resultCode = '2000';
        $callId     = '1234';

        $signatureString = $apiId
            . $timeStamp
            . $nonce
            . $statusCode
            . $resultCode
            . $callId;

        $signature = hash_hmac('sha1', $signatureString, $chargify->getApiSecret());

        $this->assertEquals($signature, $direct->getResponseSignature($apiId, $timeStamp, $nonce, $statusCode, $resultCode, $callId));
    }

    public function testIsValidResponseSignature()
    {
        $chargify   = ClientV2Helper::getInstance();
        $direct     = $chargify->direct();
        $apiId      = $chargify->getApiId();
        $timeStamp  = $direct->getTimeStamp();
        $nonce      = $direct->getNonce();
        $statusCode = '200';
        $resultCode = '2000';
        $callId     = '1234';

        $validSignatureString = $apiId
            . $timeStamp
            . $nonce
            . $statusCode
            . $resultCode
            . $callId;

        $validSignature   = hash_hmac('sha1', $validSignatureString, $chargify->getApiSecret());
        $inValidSignature = str_repeat('x', 40);

        $this->assertTrue($direct->isValidResponseSignature($validSignature, $apiId, $timeStamp, $nonce, $statusCode, $resultCode, $callId));
        $this->assertFalse($direct->isValidResponseSignature($inValidSignature, $apiId, $timeStamp, $nonce, $statusCode, $resultCode, $callId));
    }

    public function testFormCreation()
    {
        $chargify = ClientV2Helper::getInstance();
        $direct   = $chargify->direct();

        // set redirect
        $direct->setRedirect('http://example.local');

        // set data
        $data = [
            'signup' => [
                'product'  => [
                    'id' => 1234
                ],
                'customer' => [
                    'first_name' => 'Dan',
                    'last_name'  => 'Bowen',
                    'email'      => 'foo@mailinator.com'
                ]
            ]
        ];
        $direct->setData($data);

        $signupAction = 'https://api.chargify.com/api/v2/signups';
        $this->assertEquals($signupAction, $direct->getSignupAction());

        // get hidden fields
        $hiddenFields   = $direct->getHiddenFields();
        $apiIdField     = '<input type="hidden" name="secure[api_id]"    value="' . $chargify->getApiId() . '" />';
        $timestampField = '<input type="hidden" name="secure[timestamp]" value="' . $direct->getTimeStamp() . '" />';
        $nonceField     = '<input type="hidden" name="secure[nonce]"     value="' . $direct->getNonce() . '" />';
        $dataField      = '<input type="hidden" name="secure[data]"      value="' . $direct->getDataStringEncoded() . '" />';
        $signatureField = '<input type="hidden" name="secure[signature]" value="' . $direct->getRequestSignature() . '" />';

        $this->assertStringContainsString($apiIdField, $hiddenFields);
        $this->assertStringContainsString($timestampField, $hiddenFields);
        $this->assertStringContainsString($nonceField, $hiddenFields);
        $this->assertStringContainsString($dataField, $hiddenFields);
        $this->assertStringContainsString($signatureField, $hiddenFields);
    }

    public function testSetRedirectAfterRequestSignatureThrowsException()
    {
        $this->expectException(BadMethodCallException::class);

        $chargify = ClientV2Helper::getInstance();
        $direct   = $chargify->direct();

        $direct->getRequestSignature();
        $direct->setRedirect('http://example.local');
    }

    public function testSetDataAfterRequestSignatureThrowsException()
    {
        $this->expectException(BadMethodCallException::class);

        $chargify = ClientV2Helper::getInstance();
        $direct   = $chargify->direct();

        $direct->getRequestSignature();
        $direct->setData([]);
    }

    public function testGetCardUpdateAction()
    {
        $chargify = ClientV2Helper::getInstance();
        $direct   = $chargify->direct();

        $this->assertEquals('https://api.chargify.com/api/v2/subscriptions/1234/card_update', $direct->getCardUpdateAction('1234'));
    }
}