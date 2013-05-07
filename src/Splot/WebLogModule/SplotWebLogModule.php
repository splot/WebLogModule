<?php
namespace Splot\WebLogModule;

use MD\Foundation\Debug\Debugger;

use Splot\Log\LogContainer;
use Splot\Log\ExportableLogInterface;

use Splot\Framework\Modules\AbstractModule;
use Splot\Framework\Events\WillSendResponse;

class SplotWebLogModule extends AbstractModule
{

    public function boot() {
        if (LogContainer::isEnabled()) {
            $this->container->get('event_manager')->subscribe(WillSendResponse::getName(), function(WillSendResponse $event) {
                $logsContent = \MD\console_string_dump('######################### SPLOT LOG #########################');
                foreach(LogContainer::getLogs() as $name => $log) {
                    if (!$log instanceof ExportableLogInterface) {
                        continue;
                    }

                    $logsContent .= \MD\console_string_dump('####### '. $name);
                    foreach($log->getLog() as $item) {
                        $logsContent .= \MD\console_string_dump($item);
                    }
                }

                $event->getResponse()->alterPart('</body>', '<script type="text/javascript">'. $logsContent .'</script>'. NL .'</body>');
            }, -99999);
        }
    }

}