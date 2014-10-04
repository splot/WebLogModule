<?php
namespace Splot\WebLogModule\Tests;

use Splot\WebLogModule\LogInjector;

/**
 * @coversDefaultClass \Splot\WebLogModule\LogInjector
 */
class LogInjectorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers ::logBenchmarkData
     */
    public function testLoggingBenchmarkData() {
        $mocks = $this->provideMocks();
        $mocks['logger']->expects($this->once())
            ->method('info');
        $mocks['memory_logger']->expects($this->once())
            ->method('getMessages')
            ->will($this->returnValue(array()));
        $logInjector = $this->provideLogInjector($mocks);

        $request = $this->getMock('Splot\Framework\HTTP\Request');
        $request->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('GET'));
        $request->expects($this->any())
            ->method('getRequestUri')
            ->will($this->returnValue('/'));
        $response = $this->getMock('Splot\Framework\HTTP\Response');

        $event = $this->getMockBuilder('Splot\Framework\Events\WillSendResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $event->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));

        $logInjector->injectLog($event);

    }

    /**
     * @covers ::injectLog
     * @covers ::groupMessages
     */
    public function testInjectingLog() {
        $mocks = $this->provideMocks();
        $mocks['memory_logger']->expects($this->once())
            ->method('getMessages')
            ->will($this->returnValue(array('Message 1')));
        $logInjector = $this->provideLogInjector($mocks);

        $request = $this->getMock('Splot\Framework\HTTP\Request');
        $response = $this->getMock('Splot\Framework\HTTP\Response');
        $response->expects($this->once())
            ->method('alterPart');

        $event = $this->getMockBuilder('Splot\Framework\Events\WillSendResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $event->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));

        $logInjector->injectLog($event);
    }

    protected function provideMocks() {
        $mocks = array();
        $mocks['timer'] = $this->getMock('MD\Foundation\Debug\Timer');
        $mocks['logger'] = $this->getMock('Psr\Log\LoggerInterface');
        $mocks['memory_logger'] = $this->getMock('MD\Clog\Writers\MemoryLogger');
        return $mocks;
    }

    protected function provideLogInjector(array $mocks = array()) {
        $mocks = empty($mocks) ? $this->provideMocks() : $mocks;
        return new LogInjector($mocks['timer'], $mocks['logger'], $mocks['memory_logger']);
    }

}
