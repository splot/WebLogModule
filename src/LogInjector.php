<?php
namespace Splot\WebLogModule;

use Psr\Log\LoggerInterface;

use MD\Clog\Writers\MemoryLogger;

use MD\Foundation\Debug\Debugger;
use MD\Foundation\Debug\Timer;
use MD\Foundation\Utils\ArrayUtils;
use MD\Foundation\Utils\StringUtils;

use Splot\Framework\Events\WillSendResponse;
use Splot\Framework\HTTP\Request;

class LogInjector
{

    protected $timer;

    protected $logger;

    protected $memoryLogger;

    public function __construct(Timer $timer, LoggerInterface $logger, MemoryLogger $memoryLogger) {
        $this->timer = $timer;
        $this->logger = $logger;
        $this->memoryLogger = $memoryLogger;
    }

    public function injectLog(WillSendResponse $event) {
        $request = $event->getRequest();

        $this->logBenchmarkData($request);

        $logsContent = Debugger::consoleStringDump('######################### SPLOT LOG #########################');
        $messages = $this->memoryLogger->getMessages();
        $messages = $this->groupMessages($messages);

        foreach($messages as $name => $msgs) {
            $logsContent .= Debugger::consoleStringDump('####### '. $name);
            foreach($msgs as $item) {
                $logsContent .= Debugger::consoleStringDump($item);
            }
        }

        $event->getResponse()->alterPart('</body>', '<script type="text/javascript">'. $logsContent .'</script>'. NL .'</body>');
    }

    protected function logBenchmarkData(Request $request) {
        // log benchmark data
        $executionTime = round($this->timer->stop() * 1000);
        $memoryUsed = $this->timer->getStopMemoryPeak();
        $this->logger->info('Execution of {method} request for {uri} took {time} ms and used {memory} of memory', array(
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'time' => $executionTime,
            'memoryUsed' => $memoryUsed,
            'memory' => StringUtils::bytesToString($memoryUsed)
        ));
    }

    protected function groupMessages(array $messages) {
        $grouped = array();

        foreach($messages as $message) {
            $name = isset($message['context']) && isset($message['context']['_name'])
                ? $message['context']['_name']
                : 'general';
            if (!isset($grouped[$name])) {
                $grouped[$name] = array();
            }

            $grouped[$name][] = $message;
        }

        return $grouped;
    }

}
