namespace PerformanceMonitor\DependencyInjection;

use PerformanceMonitor\DataCollector\SQLDataCollector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Extension\Extension;

class PerformanceMonitorExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        // Enregistrement du DataCollector
        $container->register(SQLDataCollector::class)
            ->addArgument(new Reference('doctrine.dbal.default_connection'))
            ->addTag('data_collector', ['template' => '@PerformanceMonitor/monitor/sql_data_collector.html.twig']);
    }
}
