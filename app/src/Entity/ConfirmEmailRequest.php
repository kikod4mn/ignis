<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Concerns\IdConcern;
use App\Entity\Concerns\TimestampsConcern;
use App\Entity\Contracts\IdContract;
use App\Entity\Contracts\TimeStampableContract;
use App\Repository\ConfirmEmailRequestRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConfirmEmailRequestRepository::class)
 */
class ConfirmEmailRequest implements IdContract, TimeStampableContract {
	use IdConcern;
	use TimestampsConcern;
	
	/**
	 * @ORM\Column(type="string", length=20)
	 */
	private string $selector;
	
	/**
	 * @ORM\Column(type="string", length=100)
	 */
	private string $hashedToken;
	
	/**
	 * @ORM\Column(type="carbon_immutable")
	 */
	private ?DateTimeInterface $confirmedAt = null;
	
	/**
	 * @ORM\Column(type="carbon_immutable")
	 */
	private DateTimeInterface $expiresAt;
	
	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\User")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private User $user;
	
	/**
	 * @ORM\Column(type="string", length=64)
	 */
	private string $fromIp;
	
	/**
	 * @ORM\Column(type="string", length=300)
	 */
	private string $fromBrowser;
	
	public function __construct(User $user, DateTimeInterface $expiresAt, string $selector, string $hashedToken, string $fromIp, string $fromBrowser) {
		$this->user        = $user;
		$this->expiresAt   = $expiresAt;
		$this->selector    = $selector;
		$this->hashedToken = $hashedToken;
		$this->fromIp      = $fromIp;
		$this->fromBrowser = $fromBrowser;
	}
	
	public function getSelector(): string {
		return $this->selector;
	}
	
	public function getHashedToken(): string {
		return $this->hashedToken;
	}
	
	public function getConfirmedAt(): ?DateTimeInterface {
		return $this->confirmedAt;
	}
	
	public function setConfirmedAt(?DateTimeInterface $confirmedAt): void {
		$this->confirmedAt = $confirmedAt;
	}
	
	public function isExpired(): bool {
		return $this->expiresAt->getTimestamp() <= time();
	}
	
	public function getExpiresAt(): DateTimeInterface {
		return $this->expiresAt;
	}
	
	public function getUser(): User {
		return $this->user;
	}
	
	public function getFromIp(): string {
		return $this->fromIp;
	}
	
	public function getFromBrowser(): string {
		return $this->fromBrowser;
	}
}
