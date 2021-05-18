<?php

declare(strict_types = 1);

namespace App\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Entity;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class IH {
	/**
	 * @param   ContainerInterface   $container
	 * @param   string               $repoFQN
	 * @return EntityRepository<Entity>
	 */
	public static function getRepository(ContainerInterface $container, string $repoFQN): EntityRepository {
		$svc = $container->get($repoFQN);
		if (! $svc instanceof EntityRepository) {
			throw new RuntimeException(sprintf('Cannot find service "%s"', $repoFQN));
		}
		return $svc;
	}
	
	public static function getCsrf(ContainerInterface $container): CsrfTokenManagerInterface {
		$tokenMgr = $container->get('security.csrf.token_manager');
		if (! $tokenMgr instanceof CsrfTokenManagerInterface) {
			if (is_null($tokenMgr)) {
				throw new RuntimeException('Var $tokenMgr is null.');
			}
			throw new RuntimeException(sprintf('Var $tokenMgr is an invalid type of class "%s"', $tokenMgr::class));
		}
		return $tokenMgr;
	}
	
	public static function disableSoftDeleteFilter(ContainerInterface $container): void {
		/** @var EntityManagerInterface $em */
		$em = $container->get('doctrine.orm.default_entity_manager');
		if ($em->getFilters()->isEnabled('soft_deleted_filter')) {
			$em->getFilters()->disable('soft_deleted_filter');
		}
	}
}