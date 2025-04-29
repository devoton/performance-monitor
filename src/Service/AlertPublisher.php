namespace PerformanceMonitor\Service;

use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AlertPublisher
{
    private ?PublisherInterface $publisher;
    private bool $enabled;

    public function __construct(ParameterBagInterface $params, ?PublisherInterface $publisher = null)
    {
        $this->publisher = $publisher;
        $this->enabled = $params->get('mercure.enabled', false);  // On vérifie si Mercure est activé
    }

    public function sendAlert(string $message): void
    {
        // Si Mercure est activé, envoie l'alerte via WebSocket
        if ($this->enabled && $this->publisher) {
            $update = new Update(
                'performance/alerts',
                json_encode(['message' => $message, 'timestamp' => time()]),
                ['topic' => 'performance_alert']
            );

            $this->publisher->publish($update);
        }
        // Sinon, tu peux enregistrer l'alerte ou envoyer une autre forme de notification
    }
}
