<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Product;
use App\Entity\Logs;
use App\Repository\LogsRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

final class ProductListener implements EventSubscriberInterface
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['sendMail', EventPriorities::POST_WRITE],
        ];
    }



    public function sendMail(ViewEvent $event): void
    {
        $Product = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$Product instanceof Product || Request::METHOD_POST !== $method) {
            return;
        }

        $message = (new Email())
            ->from('system@example.com')
            ->to('admin@example.com')
            ->subject('A new Product has been added')
            ->text(sprintf('The Product #%d has been added.', $Product->getId()));

        $this->mailer->send($message);
    }
}