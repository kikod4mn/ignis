<?php

declare(strict_types = 1);

namespace App\Entity\Concerns;

use App\Entity\Contracts\AuthorableContract;
use App\Entity\User;

trait AuthorConcern {
	public function getAuthor(): ?User {
		return $this->author;
	}
	
	/**
	 * @return AuthorableContract|$this
	 */
	public function setAuthor(?User $author): AuthorableContract {
		$this->author = $author;
		return $this;
	}
}