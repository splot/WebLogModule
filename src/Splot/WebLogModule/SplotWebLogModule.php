<?php
namespace Splot\WebLogModule;

use MD\Foundation\Debug\Debugger;
use MD\Foundation\Utils\StringUtils;

use Splot\Log\LogContainer;
use Splot\Log\ExportableLogInterface;

use Splot\Framework\Modules\AbstractModule;
use Splot\Framework\Events\WillSendResponse;

class SplotWebLogModule extends AbstractModule
{

    public function boot() {
        if (LogContainer::isEnabled()) {
            $container = $this->container;

            $container->get('event_manager')->subscribe(WillSendResponse::getName(), function(WillSendResponse $event) use ($container) {
                $benchmark = array();

                $logsContent = Debugger::consoleStringDump('######################### SPLOT LOG #########################');
                foreach(LogContainer::getLogs() as $name => $log) {
                    if (!$log instanceof ExportableLogInterface) {
                        continue;
                    }

                    $logsContent .= Debugger::consoleStringDump('####### '. $name);
                    foreach($log->getLog() as $item) {
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

}