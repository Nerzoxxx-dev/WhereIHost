<?php

namespace App\Entity;

use App\Repository\HostsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HostsRepository::class)
 * @ORM\Table(name="hosts", indexes={@ORM\Index(columns={"name", "description", "website", "legal_number"}, flags={"fulltext"})})
 */

class Hosts
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=18)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=400)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $legal_number;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_verified;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $verified_at;

    /**
     * @ORM\Column(type="integer")
     */
    private $likes;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $logo_filename;

    /**
     * @ORM\Column(type="integer")
     */
    private $author_id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $verification_proofs;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_suspend;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $suspend_at;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $suspend_by;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLegalNumber(): ?string
    {
        return $this->legal_number;
    }

    public function setLegalNumber(string $legal_number): self
    {
        $this->legal_number = $legal_number;

        return $this;
    }

    public function getIsVerified(): ?bool
    {
        return $this->is_verified;
    }

    public function setIsVerified(bool $is_verified): self
    {
        $this->is_verified = $is_verified;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->verified_at;
    }

    public function setVerifiedAt(?\DateTimeImmutable $verified_at): self
    {
        $this->verified_at = $verified_at;

        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): self
    {
        $this->likes = $likes;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getLogoFilename(): ?string
    {
        return $this->logo_filename;
    }

    public function setLogoFilename(string $logo_filename): self
    {
        $this->logo_filename = $logo_filename;

        return $this;
    }

    public function getAuthorId(): ?int
    {
        return $this->author_id;
    }

    public function setAuthorId(int $author_id): self
    {
        $this->author_id = $author_id;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getVerificationProofs()
    {
        $verification_proofs = $this->verification_proofs;

        if(empty($verification_proofs)) 
        { 
            $verification_proofs = [];
        }elseif(!strpos(',', $verification_proofs)) {
            $verification_proofs = [$verification_proofs];
        }else {
            $verification_proofs = implode(',', $verification_proofs);
        }

        return $verification_proofs;
    }

    public function setVerificationProofs(string $verification_proofs): self
    {

        $this->verification_proofs = $verification_proofs;

        return $this;
    }

    public function getIsSuspend(): ?bool
    {
        return $this->is_suspend;
    }

    public function setIsSuspend(bool $is_suspend): self
    {
        $this->is_suspend = $is_suspend;

        return $this;
    }

    public function getSuspendAt(): ?string
    {
        return $this->suspend_at->format('r');
    }

    public function setSuspendAt(?\DateTimeImmutable $suspend_at): self
    {
        $this->suspend_at = $suspend_at;

        return $this;
    }

    public function getSuspendBy(): ?int
    {
        return $this->suspend_by;
    }

    public function setSuspendBy(?int $suspend_by): self
    {
        $this->suspend_by = $suspend_by;

        return $this;
    }
}
