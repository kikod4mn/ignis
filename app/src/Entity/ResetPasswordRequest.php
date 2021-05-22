<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Concerns\IdConcern;
use App\Entity\Concerns\TimestampsConcern;
use App\Entity\Contracts\IdContract;
use App\Entity\Contracts\TimeStampableContract;
use App\Repository\ResetPasswordRequestRepository;
use Carbon\Carbon;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResetPasswordRequestRepository::class)
 */
class ResetPasswordRequest implements IdContract, TimeStampableContract {
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
	private DateTimeInterface $requestedAt;
	
	/**
	 * @ORM\Column(type="carbon_immutable")
	 */
	private DateTimeInterface $expiresAt;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User")
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
		$this->requestedAt = Carbon::now();
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
	
	public function getRequestedAt(): DateTimeInterface {
		return $this->requestedAt;
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
