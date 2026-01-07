<?php

declare(strict_types=1);

namespace App\Application\Messaging;

enum Transport: string
{
    case HTTP = 'http';
    case RABBITMQ = 'rabbitmq';
    case SQS = 'sqs';
}
