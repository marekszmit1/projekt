<?php

namespace App\Events;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Logs;
use App\Entity\Product;
use App\Repository\LogsRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpClient\HttpClientTrait;

final class ProductListener implements EventSubscriberInterface
{
    private $mailer;
    private $logRep;

    public function __construct(LogsRepository $logRep)
    {
        $this->logRep = $logRep;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [
                'saveLog', EventPriorities::POST_WRITE/*, 
                'sendMail', EventPriorities::POST_WRITE*/
            ]
        ];
    }

    public function saveLog(ViewEvent $event): void
    {
        $Product = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        if (!$Product instanceof Product) {
            return;
        }

        switch ($method) {
            case Request::METHOD_POST:
                $operation = 'added';
                break;
            case Request::METHOD_PATCH:
                $operation = 'updated';
                break;
            case Request::METHOD_DELETE:
                $operation = 'removed';
                break;
            case Request::METHOD_PUT:
                $operation = 'replaced';
                break;
            case Request::METHOD_GET:
                $operation = 'retrieved';
                break;
        }



        $log = new Logs();
        $log->setText('Product "' . $Product->getName() . '" (id: ' . $Product->getId() . ') was ' . $operation . ' at ' . $Product->getCreatedAt()->format('c'));

        $this->logRep->add($log, true);
    }

    // public function sendMail(ViewEvent $event): void
    // {
    //     $Product = $event->getControllerResult();
    //     $method = $event->getRequest()->getMethod();
    //     if (!$Product instanceof Product || Request::METHOD_POST !== $method) {
    //         return;
    //     }
    //     $message = (new Email())
    //         ->from('system@example.com')
    //         ->to('admin@example.com')
    //         ->subject('A new Product has been added')
    //         ->text(sprintf('The Product #%d has been added.', $Product->getId()));

    //     $this->mailer->send($message);
    // }
}
