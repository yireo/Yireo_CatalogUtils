<?php

declare(strict_types=1);

namespace Yireo\CatalogUtils\Test\Integration;

use PHPUnit\Framework\TestCase;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsEnabled;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsRegistered;

class ModuleTest extends TestCase
{
    use AssertModuleIsEnabled;
    use AssertModuleIsRegistered;

    public function testIfModuleWorks(): void
    {
        $this->assertModuleIsEnabled('Yireo_CatalogUtils');
        $this->assertModuleIsRegistered('Yireo_CatalogUtils');
    }
}
