<?php

namespace App\Entity;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookRepository")
 * @ORM\Table(name="books")
 */
final class Book
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    protected $title;

    /**
     * @ORM\Column(type="text")
     */
    protected $path;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag")
     * @ORM\JoinTable(name="books_tags_link",
     *     joinColumns={@ORM\JoinColumn(name="book", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="tag", referencedColumnName="id")}
     * )
     */
    protected $tags;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }
}