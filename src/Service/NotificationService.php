<?php

namespace App\Service;

use App\Enum\NotificationStatus;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class NotificationService
{

    private Session $session;

    public function __construct(RequestStack $session)
    {
        $this->session = $session->getSession();
    }

    public function addMessage(NotificationStatus $type, $message): void
    {
        $this->session->getFlashBag()->add($type->getStatus(), $message);
    }

    public function addSuccess($message): void
    {
        $this->addMessage(NotificationStatus::SUCCESS, $message);
    }

    public function addDanger($message): void
    {
        $this->addMessage(NotificationStatus::DANGER, $message);
    }

}