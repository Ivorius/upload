<?php

namespace h4kuna\Upload;

use Tester\Assert;

$container = require __DIR__ . '/../bootsrap.php';

class UploadTest extends \Tester\TestCase
{

	/** @var Upload */
	private $upload;

	/** @var DocumentRoot */
	private $documentRoot;

	/** @var \Salamium\Testinium\FileUploadFactory */
	private $fileUploadFactory;

	public function __construct(Upload $upload, DocumentRoot $documentRoot, \Salamium\Testinium\FileUploadFactory $fileUploadFactory)
	{
		$this->upload = $upload;
		$this->documentRoot = $documentRoot;
		$this->fileUploadFactory = $fileUploadFactory;
	}

	public function testUpload()
	{
		$uploadFile = $this->fileUploadFactory->create('čivava.txt');
		$relativePath = $this->upload->save($uploadFile);

		Assert::notSame(NULL, $relativePath);

		$absolutePath = $this->documentRoot->createAbsolutePath($relativePath);
		Assert::true(is_file($absolutePath));

//		$this->fileRepository->insert([
//			'name' => $uploadFile->getName(),
//			'size' => $uploadFile->getSize(),
//			'filesystem' => $relativePath,
//			'type' => $uploadFile->getContentType(),
//		]);
	}

	public function testUploadSubDir()
	{
		$uploadFile = $this->fileUploadFactory->create('čivava.txt');
		$relativePath = $this->upload->save($uploadFile, 'my/path/is/here');

		Assert::notSame(NULL, $relativePath);

		$absolutePath = $this->documentRoot->createAbsolutePath($relativePath);
		Assert::true(is_file($absolutePath));
	}

	public function testUploadFail()
	{
		$uploadFile = $this->fileUploadFactory->create('čivava.txt', 1);
		$relativePath = $this->upload->save($uploadFile);
		Assert::null($relativePath);
	}

}

$upload = $container->getService('uploadExtension.upload');
$documentRoot = $container->getService('uploadExtension.documentRoot');
$fileUploadFactory = $container->getByType('Salamium\Testinium\FileUploadFactory');

(new UploadTest($upload, $documentRoot, $fileUploadFactory))->run();
