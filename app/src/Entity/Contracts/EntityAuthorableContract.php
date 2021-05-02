<?php

declare(strict_types = 1);

namespace App\Entity\Contracts;

use App\Entity\User;

interface EntityAuthorableContract {
	public function getAuthor(): ?User;
	
	public function setAuthor(User $author): EntityAuthorableContract;
}