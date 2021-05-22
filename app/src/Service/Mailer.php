<?php

declare(strict_types = 1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class Mailer {
	public function __construct(
		private MailerInterface $mailer, private LoggerInterface $logger, private string $adminTo, private string $adminFrom
	) {
	}
	
	/**
	 * @param   string    $to
	 * @param   string    $subject
	 * @param   string    $template
	 * @param   mixed[]   $args
	 */
	public function htmlMessage(string $to, string $subject, string $template, array $args): void {
		try {
			$this->mailer->send($this->twigTemplate($to, $subject, $template, $args));
		} catch (TransportExceptionInterface $e) {
			$this->logger->critical($e->getMessage());
		}
	}
	
	public function message(string $to, string $subject, string $text): void {
		try {
			$this->mailer->send($this->regMessage($to, $subject, $text));
		} catch (TransportExceptionInterface $e) {
			$this->logger->critical($e->getMessage());
		}
	}
	
	/**
	 * @param   string    $to
	 * @param   string    $subject
	 * @param   string    $template
	 * @param   mixed[]   $args
	 * @param   mixed[]   $attachments
	 * @return TemplatedEmail
	 */
	private function twigTemplate(string $to, string $subject, string $template, array $args, array $attachments = []): TemplatedEmail {
		$mail = (new TemplatedEmail())
			->from($this->adminFrom)
			->to($to)
			->subject($subject)
			->htmlTemplate($template)
			->context($args)
		;
		if (count($attachments) > 0) {
			foreach ($attachments as $attachment) {
				$mail->attach($attachment);
			}
		}
		return $mail;
	}
	
	/**
	 * @param   string    $to
	 * @param   string    $subject
	 * @param   string    $text
	 * @param   mixed[]   $attachments
	 * @return Email
	 */
	private function regMessage(string $to, string $subject, string $text, array $attachments = []): Email {
		$mail = (new Email())
			->from($this->adminFrom)
			->to($to)
			->subject($subject)
			->text($text)
		;
		if (count($attachments) > 0) {
			foreach ($attachments as $attachment) {
				$mail->attach($attachment);
			}
		}
		return $mail;
	}
}