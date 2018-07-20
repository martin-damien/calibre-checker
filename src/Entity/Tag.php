<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tags")
 */
final class Tag
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    protected $name;

    public function getName(): string
    {
        return $this->name;
    }
}