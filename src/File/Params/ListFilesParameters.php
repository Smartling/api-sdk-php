<?php

namespace Smartling\File\Params;

class ListFilesParameters extends BaseParameters {

	public function setUriMask ( $uri_mask ) {
		$this->params['uriMask'] = $uri_mask;

		return $this;
	}

	public function setFileTypes ( $file_types ) {
		$this->params['fileTypes[]'] = $file_types;

		return $this;
	}

	public function setLastUploadedAfter ( $last_uploaded_after ) {
		$this->params['lastUploadedAfter'] = $last_uploaded_after;

		return $this;
	}

	public function setLastUploadedBefore ( $last_uploaded_before ) {
		$this->params['lastUploadedBefore'] = $last_uploaded_before;

		return $this;
	}

	public function setOffset ( $offset ) {
		$this->params['offset'] = $offset;

		return $this;
	}

	public function setLimit ( $limit ) {
		$this->params['limit'] = $limit;

		return $this;
	}
}
