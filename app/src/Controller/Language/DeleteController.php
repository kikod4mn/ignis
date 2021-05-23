<?php

declare(strict_types = 1);

namespace App\Controller\Language;

use App\Entity\Language;

use App\Entity\User;
use App\Event\Updators\DeleteEvent;
use App\Service\Contracts\Flashes;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DeleteController extends AbstractController {
	public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger, private EventDispatcherInterface $ed) { }
	
	/**
	 * @Route("languages/{language_uuid}/delete", name="language-delete", methods={"GET"})
	 * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"language_uuid": "uuid"}})
	 */
	public function __invoke(Language $language): Response {
		if ($this->isGranted(User::ROLE_TEST_USER)) {
			return $this->showcaseDelete($language);
		}
		if ($this->isGranted(User::ROLE_DELETE_LANGUAGE, $language) && $this->isGranted(User::ROLE_PROJECT_LEAD)) {
			return $this->delete($language);
		}
		if (! $this->isGranted(User::ROLE_USER)) {
			throw $this->createAccessDeniedException();
		}
		throw $this->createNotFoundException();
	}
	
	private function delete(Language $language): Response {
		$this->ed->dispatch(new DeleteEvent($language));
		$this->em->flush();
		/** @var User $user */
		$user = $this->getUser();
		$this->logger->info(
			sprintf(
				'User "%s" with id "%d", deleted language: "%s", with id: "%d".',
				$user->getName(), $user->getId(), $language->getName(), $language->getId()
			)
		);
		if ($language->getHardDeleted()) {
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Deleted the language! It is now gone and forgotten!');
		} else {
			$this->addFlash(Flashes::SUCCESS_MESSAGE, 'The language is now soft deleted to trash! Only admins and feature author can see it.');
		}
		return $this->redirectToRoute('languages-list');
	}
	
	private function showcaseDelete(): Response {
		$this->addFlash(Flashes::SUCCESS_MESSAGE, 'Deleted the language! It is now gone and forgotten!');
		$this->addFlash(Flashes::INFO_MESSAGE, 'Actually nothing changed. Just a test user doing test user things!');
		return $this->redirectToRoute('languages-list');
	}
}