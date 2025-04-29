namespace PerformanceMonitor\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use PerformanceMonitor\DataCollector\SQLDataCollector;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestPerformanceListener implements EventSubscriberInterface
{
    private ParameterBagInterface $params;
    private SQLDataCollector $sqlCollector;
    private float $startTime;
    private int $startMemory;

    public function __construct(SQLDataCollector $sqlCollector, ParameterBagInterface $params)
    {
        $this->sqlCollector = $sqlCollector;
        $this->params = $params;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 10],
            KernelEvents::TERMINATE => ['onTerminate', 0],
        ];
    }

    public function onRequest(TerminateEvent $event): void
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();
    }

    public function onTerminate(TerminateEvent $event): void
    {
        $alerts = $this->params->get('performance_monitor.alerts');

        // Extractions des données de performance
        $duration = microtime(true) - $this->startTime;
        $memory = memory_get_usage() - $this->startMemory;
        $sqlQueries = $this->sqlCollector->getQueries();
        $sqlTime = $this->sqlCollector->getQueriesTime();

        // Alerte sur temps de réponse
        if ($duration * 1000 > $alerts['time']) {
            $this->triggerAlert('Long response time', $duration * 1000);
        }

        // Alerte sur l'utilisation de la mémoire
        if ($memory / 1024 > $alerts['memory']) {
            $this->triggerAlert('High memory usage', $memory / 1024);
        }

        // Alerte sur le nombre de requêtes SQL
        if ($sqlQueries > $alerts['sql_queries']) {
            $this->triggerAlert('Too many SQL queries', $sqlQueries);
        }

        // Alerte sur le temps des requêtes SQL
        if ($sqlTime > $alerts['sql_time']) {
            $this->triggerAlert('SQL queries taking too long', $sqlTime);
        }

        // Log normal
        $this->logPerformance($event, $duration, $memory, $sqlQueries, $sqlTime);
    }

    private function triggerAlert(string $message, $value): void
    {
        // Log ou envoi d'alerte
        $projectDir = $this->params->get('kernel.project_dir');
        file_put_contents(
            $projectDir . '/var/log/performance_alerts.log',
            sprintf('[ALERT] %s: %s\n', $message, $value),
            FILE_APPEND
        );
    }

    private function logPerformance(TerminateEvent $event, $duration, $memory, $sqlQueries, $sqlTime)
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        $log = sprintf(
            '[Performance] Route: %s | Time: %.2f ms | Memory: %.2f KB | SQL Queries: %d | SQL Time: %.2f ms',
            $route,
            $duration * 1000,
            $memory / 1024,
            $sqlQueries,
            $sqlTime
        );

        file_put_contents(
            $event->getKernel()->getProjectDir() . '/var/log/performance.log',
            $log . PHP_EOL,
            FILE_APPEND
        );
    }
}
