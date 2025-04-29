namespace PerformanceMonitor\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SQLDataCollector extends DataCollector
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
        $queries = $this->connection->getQueryCount();
        $this->data['queries'] = $queries;
        $this->data['queries_time'] = $this->connection->getQueryTime();
    }

    public function getQueries(): int
    {
        return $this->data['queries'];
    }

    public function getQueriesTime(): float
    {
        return $this->data['queries_time'];
    }

    public function reset(): void
    {
        $this->data = [];
    }

    public function getName(): string
    {
        return 'performance_monitor.sql';
    }
}
