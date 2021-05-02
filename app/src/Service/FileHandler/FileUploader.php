<?php

declare(strict_types = 1);

namespace App\Service\FileHandler;

use App\Service\FileHandler\Contracts\FileUploaderInterface;
use App\Service\FileHandler\Exception\FileExtensionUnknownException;
use App\Service\FileHandler\Exception\InitFunctionNotRunException;
use App\Service\FileHandler\Exception\NoHandlerFoundForFileException;
use Symfony\Component\HttpFoundation\File\File;

final class FileUploader implements FileUploaderInterface {
	private string $dbPath = '';
	private ?File  $file   = null;
	/** @var array<int|string, mixed> */
	private array $config = [
		// default directory is always assumed as %project_dir%/public/uploads.
		// Anything user inputs is added and month and year separation is automatic if configured in options.
		'targetDirectory'     => '',
		'separateByMonth'     => true,
		'separateByYear'      => true,
		'useOriginalFilename' => false,
		// If new filename is anything but a string with alphanumeric chars, a random name will be generated.
		'newFilename'         => '',
		// Users should not set these. Will be overridden and user input is not expected here.
		'targetDbDir'         => '',
		'targetFsDir'         => '',
	];
	
	public function __construct(private string $projectDir) { }
	
	public function getDbPath(): string {
		return $this->dbPath;
	}
	
	/**
	 * @param   File                   $file
	 * @param   array<string, mixed>   $config
	 */
	public function init(File $file, array $config = []): void {
		$this->file                  = $file;
		$this->config                = [...$this->config, ...$config];
		$this->config['targetFsDir'] = $this->fsDir();
		$this->config['targetDbDir'] = $this->dbDir();
	}
	
	/**
	 * @return bool
	 * @throws FileExtensionUnknownException
	 * @throws InitFunctionNotRunException
	 * @throws NoHandlerFoundForFileException
	 */
	public function upload(): bool {
		//		$handler = match ($this->file->getMimeType()) {
		//			'image/jpg' => new JpgHandler(),
		//			'image/png' => new PngHandler(),
		//			'image/gif' => new GifHandler(),
		//			'image/webp' => new WebpHandler(),
		//		};
		if ($this->file === null) {
			throw new InitFunctionNotRunException();
		}
		if ($this->file->guessExtension() === null) {
			throw new FileExtensionUnknownException((string) $this->file->guessExtension());
		} else {
			$handlerName = ucfirst(strtolower($this->file->guessExtension())) . 'Handler';
		}
		if (! class_exists($handlerName)) {
			throw new NoHandlerFoundForFileException($this->file->guessExtension());
		} else {
			$handler = new $handlerName();
		}
		$handler->setFile($this->file)->setConfig($this->config);
		if ($handler->handle()) {
			$this->dbPath = $handler->getDbPath();
			return true;
		} else {
			return false;
		}
	}
	
	private function fsDir(): string {
		return $this->projectDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $this->config['targetFsDir'];
	}
	
	private function dbDir(): string {
		return DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $this->config['targetFsDir'];
	}
}