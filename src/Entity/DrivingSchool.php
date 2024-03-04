<?php

namespace App\Entity;

use App\Repository\DrivingSchoolRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\PseudoTypes\Numeric_;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DrivingSchoolRepository::class)]
class DrivingSchool
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez renseigner le nom de votre auto-école')]
    #[Assert\Length(
        min: 7,
        max: 255,
        minMessage: 'Le nom de votre auto-école doit contenir au moins {{ limit }} caractères, le votre en contient {{ value_length }}.',
        maxMessage: 'Le nom de votre auto-école ne peut pas dépasser {{ limit }} caractères, le votre en contient {{ value_length }}.'
    )]
    private ?string $name = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez renseigner l\'adresse de votre auto-école')]
    #[Assert\Length(
        min: 7,
        max: 255,
        minMessage: 'L\'adresse de votre auto-école doit contenir au moins {{ limit }} caractères, le votre en contient {{ value_length }}.',
        maxMessage: 'L\'adresse  de votre auto-école ne peut pas dépasser {{ limit }} caractères, le votre en contient {{ value_length }}.'
    )]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    #[Assert\Regex(
        pattern: '/^\d+$/',
        message: 'Le numéro de siret doit contenir que des chiffres',
        match: true,
    )]
    #[Assert\NotBlank(message: 'Veuillez renseigner votre numéro de siret')]
    #[Assert\Length(
        min: 14,
        minMessage: 'Le numéro de siret doit contenir {{ limit }} chiffre, le votre en contient {{ value_length }}.',
    )]
    private ?string $siret = null;

    #[ORM\OneToMany(mappedBy: 'drivingSchool', targetEntity: Client::class)]
    private Collection $clients;

    #[ORM\OneToMany(mappedBy: 'drivingSchool', targetEntity: Contract::class)]
    private Collection $contracts;

    #[ORM\OneToMany(mappedBy: 'drivingSchool', targetEntity: Invoice::class)]
    private Collection $invoices;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'drivingSchools')]
    private Collection $users;

    #[ORM\Column]
    #[Assert\Positive]
    #[Assert\NotBlank(message: 'Veuillez renseigner un numéro de voie')]
    #[Assert\Length(
        min: 1,
        max: 3,
        minMessage: 'Le numéro de voie de votre auto-école doit contenir au moins {{ limit }} caractères, le votre en contient {{ value_length }}.',
        maxMessage: 'Le numéro de voie de votre auto-école doit contenir maximum {{ limit }} caractères, le votre en contient {{ value_length }}.',
    )]
    private ?int $number = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez renseigner la ville de votre auto-école')]
    #[Assert\Length(
        min: 4,
        max: 50,
        minMessage: 'La ville de votre auto-école doit contenir au moins {{ limit }} caractères, le votre en contient {{ value_length }}.',
        maxMessage: 'La ville de votre auto-école ne peut pas dépasser {{ limit }} caractères, le votre en contient {{ value_length }}.'
    )]
    private ?string $city = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Veuillez renseigner un code postal')]
    #[Assert\Length(
        min: 5,
        minMessage: 'Le code postal de votre auto-école doit contenir au moins {{ limit }} caractères, le votre en contient {{ value_length }}.',
    )]
    #[Assert\Positive]
    private ?int $zipCode = null;

    #[ORM\OneToMany(mappedBy: 'drivingSchool', targetEntity: Product::class)]
    private Collection $products;

    public function __construct()
    {
        $this->clients = new ArrayCollection();
        $this->contracts = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): static
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
            $client->setDrivingSchool($this);
        }

        return $this;
    }

    public function removeClient(Client $client): static
    {
        if ($this->clients->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getDrivingSchool() === $this) {
                $client->setDrivingSchool(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Contract>
     */
    public function getContracts(): Collection
    {
        return $this->contracts;
    }

    public function addContract(Contract $contract): static
    {
        if (!$this->contracts->contains($contract)) {
            $this->contracts->add($contract);
            $contract->setDrivingSchool($this);
        }

        return $this;
    }

    public function removeContract(Contract $contract): static
    {
        if ($this->contracts->removeElement($contract)) {
            // set the owning side to null (unless already changed)
            if ($contract->getDrivingSchool() === $this) {
                $contract->setDrivingSchool(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): static
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
            $invoice->setDrivingSchool($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): static
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getDrivingSchool() === $this) {
                $invoice->setDrivingSchool(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addDrivingSchool($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeDrivingSchool($this);
        }

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getZipCode(): ?int
    {
        return $this->zipCode;
    }

    public function setZipCode(int $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setDrivingSchool($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getDrivingSchool() === $this) {
                $product->setDrivingSchool(null);
            }
        }

        return $this;
    }
}
