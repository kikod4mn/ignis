<?php

declare(strict_types = 1);

namespace App\Entity\Contracts;

use App\Entity\User;

interface AuthorableContract {
	public function getAuthor(): ?User;
	
	public function setAuthor(User $author): AuthorableContract;
}