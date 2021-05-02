<?php

declare(strict_types = 1);

namespace App\Tests\acceptance\Concerns;

use App\Entity\User;
use App\Repository\UserRepository;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function get_class;
use function sprintf;

trait FindsRandomUser {
	protected function getRandomUser(ContainerInterface $container): User {
		$repository = $container->get(UserRepository::class);
		if ($repository === null) {
			throw new LogicException('Var $repository is null');
		}
		if (! $repository instanceof UserRepository) {
			throw new LogicException(
				sprintf(
					'Var $repository must be an instance of "%s" but is actually "%s"',
					UserRepository::class, get_class($repository)
				)
			);
		}
		$user = $repository->findOneBy(['active' => true]);
		if ($user === null) {
			throw new LogicException('Var $user is null');
		}
		return $user;
	}
}