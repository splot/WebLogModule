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

        $container->get('event_manager')->subscribe(WillSendResponse::getName(), function(WillSendResponse $event) use ($container) {
            $benchmark = array();

            $logsContent = Debugger::consoleStringDump('######################### SPLOT LOG #########################');
            $messages = $container->get('clog.writer.memory')->getMessages();
            $messages = ArrayUtils::groupBy($messages, '_name');

            foreach($messages as $name => $msgs) {
                $logsContent .= Debugger::consoleStringDump('####### '. $name);
                foreach($msgs as $item) {
                    $logsContent .= Debugger::consoleStringDump($item);
                }
            }

            // log benchmark data
            $timer = $container->get('application')->getTimer();
            $logsContent .= Debugger::consoleStringDump('####### Benchmark', array(
                'Execution Time' => round($timer->stop() * 1000) . ' ms',
                'Memory Used' => StringUtils::bytesToString($timer->getStopMemoryPeak())
            ));

            $event->getResponse()->alterPart('</body>', '<script type="text/javascript">'. $logsContent .'</script>'. NL .'</body>');
        }, -99999);
    }

}