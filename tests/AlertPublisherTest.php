namespace App\Test;

use PerformanceMonitor\Service\AlertPublisher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;

class AlertPublisherTest extends TestCase
{
    public function testSendAlertWithMercureEnabled()
    {
        // Mock du ParameterBag
        $parameterBag = $this->createMock(ParameterBagInterface::class);
        $parameterBag->expects($this->once())
            ->method('get')
            ->with('mercure.enabled', false)
            ->willReturn(true);
        
        // Mock du PublisherInterface
        $publisher = $this->createMock(PublisherInterface::class);
        $publisher->expects($this->once())
            ->method('publish')
            ->with($this->callback(function (Update $update) {
                $this->assertEquals('performance/alerts', $update->getTopics()[0]);
                $data = json_decode($update->getData(), true);
                $this->assertArrayHasKey('message', $data);
                $this->assertArrayHasKey('timestamp', $data);
                return true;
            }));
        
        $alertPublisher = new AlertPublisher($parameterBag, $publisher);
        $alertPublisher->sendAlert('Test alert message');
    }
    
    public function testSendAlertWithMercureDisabled()
    {
        // Mock du ParameterBag
        $parameterBag = $this->createMock(ParameterBagInterface::class);
        $parameterBag->expects($this->once())
            ->method('get')
            ->with('mercure.enabled', false)
            ->willReturn(false);
        
        // Mock du PublisherInterface qui ne devrait pas être appelé
        $publisher = $this->createMock(PublisherInterface::class);
        $publisher->expects($this->never())
            ->method('publish');
        
        $alertPublisher = new AlertPublisher($parameterBag, $publisher);
        $alertPublisher->sendAlert('Test alert message');
        
        // Pas d'assertion nécessaire ici, le test vérifie simplement que publish() n'est pas appelé
    }
}
