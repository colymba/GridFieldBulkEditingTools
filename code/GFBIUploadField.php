<?php

class GFBIUploadField extends UploadField
{
	public function setConfig($key, $val) {
		$this->ufConfig[$key] = $val;
		return $this;
	}

	function extractFileData($postvars)
	{
		return $this->extractUploadedFileData($postvars);
	}

	function saveTempFile($tmpFile, &$error = null)
	{
		return $this->saveTemporaryFile($tmpFile, $error);
	}

	function encodeFileAttr(File $file)
	{
		return $this->encodeFileAttributes($file);
	}
	
}