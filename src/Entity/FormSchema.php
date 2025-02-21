<?php

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
    private bool $visibility = true;

    #[ORM\OneToMany(mappedBy: 'formSchema', targetEntity: FormField::class, cascade: ['persist'])]
    private Collection $formFields;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'schema', targetEntity: PostFormValue::class, orphanRemoval: true)]
    private Collection $postFormValues;

    public function __construct()
    {
        $this->formFields = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable(); // Set timestamp on creation
        $this->postFormValues = new ArrayCollection();
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

    public function isVisibility(): bool
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

    /**
     * @return Collection<int, FormField>
     */
    public function getFormFieldsOrderedByCreatedAt(): Collection
    {
        $iterator = $this->formFields->getIterator();

        $iterator->uasort(fn(FormField $a, FormField $b) =>
            $a->getCreatedAt() <=> $b->getCreatedAt()
        );

        return new ArrayCollection(iterator_to_array($iterator));
    }


    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
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

    /**
     * @return Collection<int, PostFormValue>
     */
    public function getPostFormValues(): Collection
    {
        return $this->postFormValues;
    }

    public function addPostFormValue(PostFormValue $postFormValue): static
    {
        if (!$this->postFormValues->contains($postFormValue)) {
            $this->postFormValues->add($postFormValue);
            $postFormValue->setSchema($this);
        }

        return $this;
    }

    public function removePostFormValue(PostFormValue $postFormValue): static
    {
        if ($this->postFormValues->removeElement($postFormValue)) {
            // set the owning side to null (unless already changed)
            if ($postFormValue->getSchema() === $this) {
                $postFormValue->setSchema(null);
            }
        }

        return $this;
    }
}
