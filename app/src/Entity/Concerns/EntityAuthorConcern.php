<?php

declare(strict_types = 1);

namespace App\Entity\Concerns;

use App\Entity\Contracts\EntityAuthorableContract;
use App\Entity\User;

trait EntityAuthorConcern {
	public function getAuthor(): ?User {
		return $this->author;
	}
	
	/**
	 * @return EntityAuthorableContract|$this
	 */
	public function setAuthor(?User $author): EntityAuthorableContract {
		$this->author = $author;
		return $this;
	}
}