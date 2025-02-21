<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Repository\PostFormValueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostFormValueRepository::class)]
class PostFormValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'postFormValues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post = null;

    #[ORM\ManyToOne(inversedBy: 'postFormValues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FormSchema $schema = null;

    #[ORM\ManyToOne(inversedBy: 'postFormValues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FormField $field = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

        return $this;
    }

    public function getSchema(): ?FormSchema
    {
        return $this->schema;
    }

    public function setSchema(?FormSchema $schema): static
    {
        $this->schema = $schema;

        return $this;
    }

    public function getField(): ?FormField
    {
        return $this->field;
    }

    public function setField(?FormField $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
