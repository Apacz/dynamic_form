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

use App\Repository\FormSchemaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormSchemaRepository::class)]
class FormSchema
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $displayName = null;

    #[ORM\Column]
    private ?bool $visibility = null;

    #[ORM\OneToMany(mappedBy: 'formSchema', targetEntity: FormField::class, cascade: ['persist'])]
    private Collection $formFields;

    public function __construct()
    {
        $this->formFields = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): static
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function isVisibility(): ?bool
    {
        return $this->visibility;
    }

    public function setVisibility(bool $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @return Collection<int, FormField>
     */
    public function getFormFields(): Collection
    {
        return $this->formFields;
    }

    public function addFormField(FormField $formField): static
    {
        if (!$this->formFields->contains($formField)) {
            $this->formFields->add($formField);
            $formField->setFormSchema($this);
        }

        return $this;
    }

    public function removeFormField(FormField $formField): static
    {
        if ($this->formFields->removeElement($formField)) {
            // set the owning side to null (unless already changed)
            if ($formField->getFormSchema() === $this) {
                $formField->setFormSchema(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->displayName;
    }
}
