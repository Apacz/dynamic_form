<?php

namespace App\Entity;

use App\Repository\FormFieldRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormFieldRepository::class)]
class FormField
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
    private bool $required = true;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dateFormat = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $OptionList = null;

    #[ORM\ManyToOne(inversedBy: 'formFields')]
    private ?FormSchema $formSchema = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'field', targetEntity: PostFormValue::class, orphanRemoval: true)]
    private Collection $postFormValues;

    public function __construct()
    {
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

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): static
    {
        $this->required = $required;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDateFormat(): ?string
    {
        return $this->dateFormat;
    }

    public function setDateFormat(?string $dateFormat): static
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    public function getOptionList(): ?string
    {
        return $this->OptionList;
    }

    public function setOptionList(?string $OptionList): static
    {
        $this->OptionList = $OptionList;

        return $this;
    }

    public function getFormSchema(): ?FormSchema
    {
        return $this->formSchema;
    }

    public function setFormSchema(?FormSchema $formSchema): static
    {
        $this->formSchema = $formSchema;

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
            $postFormValue->setField($this);
        }

        return $this;
    }

    public function removePostFormValue(PostFormValue $postFormValue): static
    {
        if ($this->postFormValues->removeElement($postFormValue)) {
            // set the owning side to null (unless already changed)
            if ($postFormValue->getField() === $this) {
                $postFormValue->setField(null);
            }
        }

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
