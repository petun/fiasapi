<?php

class FiasParser
{

	private $_token;

	private $_key;

	private $_apiUrl = 'http://kladr-api.ru/api.php?token=%s&key=%s&';


	public function __construct($token, $key) {
		$this->_token = $token;
		$this->_key = $key;

		$this->_apiUrl = sprintf($this->_apiUrl, $this->_token, $this->_key);
	}

	public function cityList($regionId, $limit = 10) {
		$params['contentType'] = 'city';
		$params['withParent'] = '1';
		$params['limit'] = $limit;
		$params['regionId'] = $regionId;

		return $this->_apiCall($params);
	}

	public function streetList($cityId, $limit = 10) {
		$params['contentType'] = 'street';
		$params['withParent'] = '0';
		$params['limit'] = $limit;
		$params['cityId'] = $cityId;

		return $this->_apiCall($params);
	}

	private function _apiCall($params) {
		$url = $this->_apiUrl;
		foreach ($params as $key => $value) {
			$urlPaths[] = $key . '=' . $value;
		}
		$url .= implode('&', $urlPaths);
		return json_decode($this->_parseUrl($url));
	}

	public function toArray($o) {
		// сам объект
		$r[$o->id] = $this->_sArray($o);

		if ($o->parents) {
			$lastParent = $o->parents[ count($o->parents) - 1];

			$r[$o->id]['parentId'] = $lastParent->id;


			// родительские объекты - по иерархии...
			for ($i=count($o->parents) - 1; $i>0; $i--) {
				$p = $o->parents[$i];
				$prevP = $o->parents[$i-1];

				$r[$p->id] = $this->_sArray($p);
				$r[$p->id]['parentId'] = $prevP->id;
			}
			$firstParent = $o->parents[0];
			$r[$firstParent->id] = $this->_sArray($firstParent);
			$r[$firstParent->id]['parentId'] = 0;

		}
		return $r;
	}

	private function _sArray($o) {
		return  array(
			'id' => $o->id,
			'name' => $o->name,
			'zip' => $o->zip,
			'type' => $o->type,
			'typeShort' => $o->typeShort,
			'okato' => $o->okato,
			'contentType' => $o->contentType

		);
	}



	private function _parseUrl($url) {
		return file_get_contents($url);
	}


}