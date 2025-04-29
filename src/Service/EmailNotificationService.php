namespace PerformanceMonitor\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailNotificationService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendAlertEmail(string $subject, string $message): void
    {
        $email = (new Email())
            ->from('no-reply@example.com')
            ->to('developer@example.com')
            ->subject($subject)
            ->text($message);

        $this->mailer->send($email);
    }
}
