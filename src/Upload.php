<?php declare(strict_types=1);

namespace h4kuna\Upload;

use h4kuna\Upload\Exceptions;
use h4kuna\Upload\Store;
use h4kuna\Upload\Upload\Options;
use Nette\Http;

class Upload
{

	/** @var IDriver */
	private $driver;

	/** @var Options[] */
	private $uploadOptions = [];


	public function __construct(IDriver $driver)
	{
		$this->driver = $driver;
	}


	/**
	 * @param Http\FileUpload $fileUpload
	 * @param Options|string|null $uploadOptions - string is path
	 * @throws Exceptions\FileUploadFailed
	 */
	public function save(Http\FileUpload $fileUpload, $uploadOptions = null): Store\File
	{
		if ($uploadOptions === null || is_scalar($uploadOptions)) {
			$uploadOptions = $this->getUploadOptions((string) $uploadOptions);
		} elseif (!$uploadOptions instanceof Options) {
			throw new Exceptions\InvalidArgument('Second parameter must be instance of UploadOptions or null or string.');
		}

		return self::saveFileUpload($fileUpload, $this->driver, $uploadOptions);
	}


	private function getUploadOptions(string $key): Options
	{
		if (!isset($this->uploadOptions[$key])) {
			$this->uploadOptions[$key] = new Options($key);
		}
		return $this->uploadOptions[$key];
	}


	/**
	 * Output object save to database.
	 * Don't forget use nette rule Form::MIME_TYPE and Form::IMAGE.
	 * $fileUpload->isOk() nette call automatically.
	 * @throws Exceptions\FileUploadFailed
	 * @throws Exceptions\UnSupportedFileType
	 */
	public static function saveFileUpload(Http\FileUpload $fileUpload, IDriver $driver, Options $uploadOptions): Store\File
	{
		if ($uploadOptions->getContentTypeFilter() !== NULL && !$uploadOptions->getContentTypeFilter()->isValid($fileUpload)) { // if forgot use Utils::setMimeTypeRule();
			throw new Exceptions\UnSupportedFileType('name: ' . $fileUpload->getName() . ', type: ' . $fileUpload->getContentType());
		}

		do {
			$relativePath = $uploadOptions->getPath() . $uploadOptions->getFilename()->createUniqueName($fileUpload);
		} while ($driver->isFileExists($relativePath));

		$storeFile = new Store\File($relativePath, $fileUpload->getName(), $fileUpload->getContentType());

		$uploadOptions->runExtendStoredFile($storeFile, $fileUpload);

		try {
			$driver->save($fileUpload, $relativePath);
		} catch (\Exception $e) {
			throw new Exceptions\FileUploadFailed('Driver "' . get_class($driver) . '" failed.', 0, $e);
		}

		return $storeFile;
	}

}
