<?php

namespace h4kuna\Upload\Upload;

use Nette\Http;

/**
 * Author: Ivo Toman
 */
interface IFileName
{

	/**
	 * @param Http\FileUpload $fileUpload
	 * @return string
	 */
	function createUniqueName(Http\FileUpload $fileUpload);
}