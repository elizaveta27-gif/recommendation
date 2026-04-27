<?php

namespace App\Entity;

use App\Enum\AttributePriority;
use App\Repository\AttributeSchemaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttributeSchemaRepository::class)]
class AttributeSchema
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $code;

    #[ORM\Column(type: 'string', enumType: AttributePriority::class)]
    private AttributePriority $priority;

    public function getCode(): string
    {
        return $this->code;
    }

    public function getPriority(): AttributePriority
    {
        return $this->priority;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

}