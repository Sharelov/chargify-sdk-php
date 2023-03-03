<?php

namespace Crucial\Tests\Lib\Crucial\Service;

use Crucial\Tests\Helpers\ClientV2Helper;
use PHPUnit\Framework\TestCase;

class ChargifyV2Test extends TestCase
{
    public function testHelperInstances()
    {
        $chargify = ClientV2Helper::getInstance();

        $direct = $chargify->direct();
        $this->assertInstanceOf('Crucial\Service\ChargifyV2\Direct', $direct);

        $call = $chargify->call();
        $this->assertInstanceOf('Crucial\Service\ChargifyV2\Call', $call);
    }
}