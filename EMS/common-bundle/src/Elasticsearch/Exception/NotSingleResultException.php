<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Exception;

class NotSingleResultException extends \Exception
{
    public function __construct(private readonly int $total)
    {
        parent::__construct(\sprintf('Not single result exception: 1 result was expected, got %d', $total));
    }

    public function getTotal(): int
    {
        return $this->total;
    }
}
