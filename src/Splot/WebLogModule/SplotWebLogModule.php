<?php
namespace Splot\WebLogModule;

use MD\Foundation\Debug\Debugger;
use MD\Foundation\Utils\ArrayUtils;
use MD\Foundation\Utils\StringUtils;

use Splot\Framework\Modules\AbstractModule;
use Splot\Framework\Events\WillSendResponse;

class SplotWebLogModule extends AbstractModule
{

    public function boot() {
        $container = $this->container;
        $self = $this;

        $container->get('event_manager')->subscribe(WillSendResponse::getName(), function(WillSendResponse $event) use ($container, $self) {
            $request = $event->getRequest();

            // log benchmark data
            $timer = $container->get('application')->getTimer();
            $executionTime = round($timer->stop() * 1000);
            $memoryUsed = $timer->getStopMemoryPeak();
            $container->get('logger_provider')->provide('Benchmark')->info('Execution of {method} request for {uri} took {time} ms and used {memory} of memory', array(
                'method' => $request->getMethod(),
                'uri' => $request->getRequestUri(),
                'time' => $executionTime,
                'memoryUsed' => $memoryUsed,
                'memory' => StringUtils::bytesToString($memoryUsed)
            ));

            $logsContent = Debugger::consoleStringDump('######################### SPLOT LOG #########################');
            $messages = $container->get('clog.writer.memory')->getMessages();
            $messages = $self->groupMessages($messages);

            foreach($messages as $name => $msgs) {
                $logsContent .= Debugger::consoleStringDump('####### '. $name);
                foreach($msgs as $item) {
                    $logsContent .= Debugger::consoleStringDump($item);
                }
            }

            $event->getResponse()->alterPart('</body>', '<script type="text/javascript">'. $logsContent .'</script>'. NL .'</body>');
        }, -99999);
    }

    public function groupMessages(array $messages) {
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