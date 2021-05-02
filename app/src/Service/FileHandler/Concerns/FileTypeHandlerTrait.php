<?php

declare(strict_types = 1);

namespace App\Service\FileHandler\Concerns;

use App\Service\FileHandler\Contracts\FileHandlerInterface;
use App\Service\FileHandler\Exception\FileNameNotAllowedException;
use App\Service\FileHandler\Exception\FileNameTooLongException;
use App\Service\FileHandler\Exception\InvalidMethodExecutionOrderException;
use App\Service\FileHandler\Exception\InvalidUploadDirectoryException;
use App\Service\FileHandler\Exception\TargetDirectoryCannotBeAccessedException;
use Kikopolis\Str;
use Kikopolis\StringWrapper;
use Symfony\Component\HttpFoundation\File\File;

trait FileTypeHandlerTrait {
	protected string $filename = '';
	protected string $dbPath   = '';
	protected string $fsPath   = '';
	protected File   $file;
	protected array  $config;
	protected array  $errors   = [];
	
	public function getDbPath(): string {
		return $this->dbPath;
	}
	
	/**
	 * @param   File   $file
	 * @return FileHandlerInterface|$this
	 */
	public function setFile(File $file): FileHandlerInterface {
		$this->file = $file;
		return $this;
	}
	
	/**
	 * @param   array   $config
	 * @return FileHandlerInterface|$this
	 */
	public function setConfig(array $config): FileHandlerInterface {
		$this->config = $config;
		return $this;
	}
	
	public function handle(): bool {
		$this->directory()->filename()->paths()->move();
		return true;
	}
	
	protected function directory(): self {
		if (! array_key_exists('targetFsDir', $this->config) && Str::empty($this->config['targetFsDir'])) {
			throw new InvalidUploadDirectoryException($this->config['targetFsDir']);
		}
		if (! file_exists($this->config['targetFsDir']) && ! is_dir($this->config['targetFsDir'])) {
			throw new TargetDirectoryCannotBeAccessedException($this->config['targetFsDir']);
		}
		return $this;
	}
	
	protected function filename(): self {
		if ($this->config['useOldFileName']) {
			$this->filename = $this->file->getBasename($this->file->getExtension());
		} else {
			if (Str::empty($this->config['newFilename'])) {
				$this->filename = $this->config['newFilename'];
			} else {
				$this->filename = StringWrapper::random(32) . $this->file;
			}
		}
		if (mb_strlen($this->filename) > 64) {
			throw new FileNameTooLongException($this->filename, mb_strlen($this->filename));
		}
		if (! preg_match('/^[a-zA-Z0-9_]+$/', $this->filename)) {
			throw new FileNameNotAllowedException($this->filename);
		}
		return $this;
	}
	
	protected function paths(): self {
		if (Str::empty($this->filename)) {
			throw new InvalidMethodExecutionOrderException('Method "paths()" requires "directory()" and "filename()" before it can run.');
		}
		$this->dbPath = $this->config['targetDbDir'] . DIRECTORY_SEPARATOR . $this->filename;
		$this->fsPath = $this->config['targetFsDir'];
		return $this;
	}
	
	protected function move(): self {
		if (Str::empty($this->filename) || Str::empty($this->fsPath)) {
			throw new InvalidMethodExecutionOrderException('Method "move()" requires "directory()", "filename()" and "paths()" before it can run.');
		}
		$this->file->move($this->fsPath, $this->filename);
		return $this;
	}
	
	protected function addYear(): void {
		$this->dbPath = $this->addToDir($this->dbPath, date('Y'));
		$this->fsPath = $this->addToDir($this->fsPath, date('Y'));
	}
	
	protected function addMonth(): void {
		$this->dbPath = $this->addToDir($this->dbPath, strtolower(date('F')));
		$this->fsPath = $this->addToDir($this->fsPath, strtolower(date('F')));
	}
	
	protected function addToDir(string $dir, string $add): string {
		if ($add === '') {
			return rtrim($dir, '/\\') . '/';
		}
		return rtrim($dir, '/\\') . '/' . rtrim(ltrim($add, '/\\'), '/\\') . '/';
	}
}