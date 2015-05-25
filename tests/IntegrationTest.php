<?php
namespace Splot\WebLogModule\Tests;

use Splot\Framework\Testing\ApplicationTestCase;

use Splot\WebLogModule\SplotWebLogModule;

class IntegrationTest extends ApplicationTestCase
{

    public static $applicationClass = "Splot\Framework\Testing\Stubs\TestApplication";

    public function testServicesAreRegistered() {
        $this->application->addTestModule(new SplotWebLogModule());

        $container = $this->application->getContainer();

        $this->assertTrue($container->has('splot.weblog_injector'));

        $injector = $container->get('splot.weblog_injector');
        $this->assertInstanceOf('Splot\WebLogModule\LogInjector', $injector);
    }

    public function testListenerIsExecuted() {
        $this->application->addTestModule(new SplotWebLogModule());

        $request = $this->getMock('Splot\Framework\HTTP\Request');
        $response = $this->getMock('Splot\Framework\HTTP\Response');
        $response->expects($this->atLeastOnce())
            ->method('alterPart');
            
        $this->application->sendResponse($response, $request);
    }

}
