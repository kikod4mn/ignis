<?php

declare(strict_types = 1);

namespace App\Tests\functional\Concerns;

use LogicException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use function sprintf;

trait TokenManagerConcern {
	private CsrfTokenManagerInterface $tokenManager;
	
	public function getTokenManager(): CsrfTokenManagerInterface {
		if (! isset($this->tokenManager)) {
			$tokenMgr = static::$container->get('security.csrf.token_manager');
			if (! $tokenMgr instanceof CsrfTokenManagerInterface) {
				throw new LogicException(sprintf('Var $tokenMgr is an invalid type of class "%s"', $tokenMgr::class));
			}
			$this->tokenManager = $tokenMgr;
		}
		return $this->tokenManager;
	}
}