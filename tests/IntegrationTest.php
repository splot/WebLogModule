<?php
namespace Splot\WebLogModule\Tests;

use Splot\WebLogModule\SplotWebLogModule;

class IntegrationTest extends \Splot\Framework\Testing\ApplicationTestCase
{

    public static $_applicationClass = "Splot\Framework\Testing\Stubs\TestApplication";

    public function testServicesAreRegistered() {
        $this->_application->addTestModule(new SplotWebLogModule());

        $container = $this->_application->getContainer();

        $this->assertTrue($container->has('splot.weblog_injector'));

        $injector = $container->get('splot.weblog_injector');
        $this->assertInstanceOf('Splot\WebLogModule\LogInjector', $injector);
    }

    public function testListenerIsExecuted() {
        $this->_application->addTestModule(new SplotWebLogModule());

        $request = $this->getMock('Splot\Framework\HTTP\Request');
        $response = $this->getMock('Splot\Framework\HTTP\Response');
        $response->expects($this->atLeastOnce())
            ->method('alterPart');
            
        $this->_application->sendResponse($response, $request);
    }

}