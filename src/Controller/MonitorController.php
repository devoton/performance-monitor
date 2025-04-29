namespace PerformanceMonitor\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MonitorController extends AbstractController
{
    #[Route('/dev/monitor/data', name: 'performance_monitor_data')]
    public function data(): JsonResponse
    {
        if ($this->getParameter('kernel.environment') !== 'dev') {
            throw $this->createAccessDeniedException();
        }
        else {
            $dataFile = $this->getParameter('kernel.project_dir') . '/var/log/performance_data.json';
            $entries = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

            return $this->json(array_reverse($entries)); // dernières entrées d'abord
        }
    }
}
