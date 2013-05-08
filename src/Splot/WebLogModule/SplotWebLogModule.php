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
            $this->container->get('event_manager')->subscribe(WillSendResponse::getName(), function(WillSendResponse $event) {
                $benchmark = array();

                $logsContent = Debugger::consoleStringDump('######################### SPLOT LOG #########################');
                foreach(LogContainer::getLogs() as $name => $log) {
                    if (!$log instanceof ExportableLogInterface) {
                        continue;
                    }

                    $logsContent .= Debugger::consoleStringDump('####### '. $name);
                    foreach($log->getLog() as $item) {
                        $logsContent .= Debugger::consoleStringDump($item);

                        // try to get benchmark data as well
                        if ($name === 'Application' && in_array('profiling', $item['_tags'])) {
                            $benchmark = array(
                                'Execution Time' => $item['context']['time'],
                                'Memory Used' => StringUtils::bytesToString($item['context']['memory'])
                            );
                        }
                    }
                }

                // log benchmark data
                if (!empty($benchmark)) {
                    $logsContent .= Debugger::consoleStringDump('####### Benchmark', $benchmark);
                }

                $event->getResponse()->alterPart('</body>', '<script type="text/javascript">'. $logsContent .'</script>'. NL .'</body>');
            }, -99999);
        }
    }

}