<?php

declare(strict_types = 1);

namespace App\Security;

use App\Entity\User;
use App\Security\Exception\AccountDisabledException;
use App\Security\Exception\AccountNotActiveException;
use App\Security\Exception\EmailNotConfirmedException;
use App\Service\Contracts\Flashes;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\UserPassportInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use function sprintf;

final class LoginFormAuthenticator implements AuthenticatorInterface {
	use TargetPathTrait;
	
	public const LOGIN_ROUTE = 'security-login';
	
	public function __construct(private FlashBagInterface $flashBag, private LoggerInterface $logger) { }
	
	public function supports(Request $request): ?bool {
		return self::LOGIN_ROUTE === $request->attributes->get('_route') && $request->isMethod(Request::METHOD_POST);
	}
	
	public function authenticate(Request $request): PassportInterface {
		$formData = $request->request->get('security_login');
		$request->getSession()->set(Security::LAST_USERNAME, $formData['_email']);
		return new Passport(
			new UserBadge($formData['_email']),
			new PasswordCredentials($formData['_password']),
			[new CsrfTokenBadge('_security_login[_csrf_token]', $formData['_token'])],
		);
	}
	
	public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response {
		$targetPath = $this->getTargetPath($request->getSession(), $firewallName);
		/** @var User $user */
		$user = $token->getUser();
		$this->flashBag->add(Flashes::SUCCESS_MESSAGE, 'Welcome back! ' . $user->getName());
		if ($targetPath) {
			return new RedirectResponse($targetPath);
		}
		return new RedirectResponse('/');
	}
	
	public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response {
		if ($exception instanceof EmailNotConfirmedException) {
			$this->setEmailNotConfirmedMessage($exception);
			return new RedirectResponse('/login');
		}
		if ($exception instanceof AccountNotActiveException) {
			$this->setAccountNotActiveMessage($exception);
			return new RedirectResponse('/login');
		}
		if ($exception instanceof BadCredentialsException) {
			$this->setBadCredentialsMessage($exception);
			return new RedirectResponse('/login');
		}
		if ($exception instanceof InvalidCsrfTokenException) {
			$this->setInvalidCsrfTokenMessage($exception);
			return new RedirectResponse('/login');
		}
		if ($exception instanceof UsernameNotFoundException) {
			$this->setUserNameNotFoundMessage($exception);
			return new RedirectResponse('/login');
		}
		if ($exception instanceof AccountDisabledException) {
			$this->setAccountDisabledMessage($exception);
			return new RedirectResponse('/login');
		}
		$this->flashBag->add(Flashes::DANGER_MESSAGE, 'Error logging in. Please try again.');
		return new RedirectResponse('/login');
	}
	
	public function createAuthenticatedToken(PassportInterface $passport, string $firewallName): TokenInterface {
		if (! $passport instanceof UserPassportInterface) {
			throw new LogicException('Passport does not contain a user object.');
		}
		return new PostAuthenticationToken($passport->getUser(), $firewallName, $passport->getUser()->getRoles());
	}
	
	private function setAccountNotActiveMessage(AccountNotActiveException $exception): void {
		/** @var User $user */
		$user = $exception->getUser();
		$this->logger->error(
			sprintf(
				'User "%s", id: "%d" failed to login because of an inactive account.',
				(string) $user->getUsername(), $user->getId()
			)
		);
		$this->flashBag->add(Flashes::DANGER_MESSAGE, $exception->getMessage());
	}
	
	private function setBadCredentialsMessage(BadCredentialsException $exception): void {
		$user = $exception->getToken()?->getUser();
		if ($user instanceof User) {
			$this->logger->error(
				sprintf(
					'User "%s", id: "%d" failed to login because of invalid credentials.',
					(string) $user->getUsername(), $user->getId()
				)
			);
		} else {
			$this->logger->error(
				sprintf(
					'User "%s" failed to login because of invalid credentials.',
					$user instanceof UserInterface ? (string) $user->getUsername() : (string) $user
				)
			);
		}
		$this->flashBag->add(Flashes::DANGER_MESSAGE, 'The entered email and password do not match. Have you forgotten Your password?');
	}
	
	private function setInvalidCsrfTokenMessage(InvalidCsrfTokenException $exception): void {
		$user = $exception->getToken()?->getUser();
		if ($user instanceof User) {
			$this->logger->error(
				sprintf(
					'User "%s", id: "%d" failed to login because of invalid csrf token received from the form.',
					(string) $user->getUsername(), $user->getId()
				)
			);
		} else {
			$this->logger->error(
				sprintf(
					'User "%s" failed to login because of invalid csrf token received from the form..',
					$user instanceof UserInterface ? (string) $user->getUsername() : (string) $user
				)
			);
		}
		$this->flashBag->add(
			Flashes::DANGER_MESSAGE,
			'The form has invalid data. Please go back to login, refresh the page manually, enter your details again and then try to login once more.'
		);
	}
	
	private function setEmailNotConfirmedMessage(EmailNotConfirmedException $exception): void {
		/** @var User $user */
		$user = $exception->getUser();
		$this->logger->error(
			sprintf(
				'User "%s", id: "%d" failed to login because of an unconfirmed email.',
				(string) $user->getUsername(), $user->getId()
			)
		);
		$this->flashBag->add(Flashes::DANGER_MESSAGE, $exception->getMessage());
	}
	
	private function setUserNameNotFoundMessage(UsernameNotFoundException $exception): void {
		$username = $exception->getUsername();
		$this->logger->error(
			sprintf(
				'User "%s" failed to login because of non-existing account.',
				$username
			)
		);
		$this->flashBag->add(Flashes::DANGER_MESSAGE, 'The email does not exist in our database. Please check your email and try again.');
	}
	
	private function setAccountDisabledMessage(AccountDisabledException $exception): void {
		$user = $exception->getUser();
		$this->logger->error(
			sprintf(
				'User "%s" failed to login because of a disabled account.',
				$user->getUsername()
			)
		);
		$this->flashBag->add(Flashes::DANGER_MESSAGE, $exception->getMessage());
	}
}