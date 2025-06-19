<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: CollectionCSRF.php,v 1.2 2022/08/18 09:44:27 jparis Exp $
namespace Pmb\Common\Library\CSRF;

use Pmb\Common\Helper\Request;

class CollectionCSRF
{

	public function __construct()
	{
		if (isset($_SESSION['csrf_token']) && is_array($_SESSION['csrf_token'])) {
			$this->checkTokens();
		} else {
			$_SESSION['csrf_token'] = array();
		}
	}

	protected function checkTokens(): void
	{
		foreach ($_SESSION['csrf_token'] as $index => $csrf_token) {
			$csrf = $this->buildInstance($csrf_token['token'], $csrf_token['time']);
			if ($csrf->expireToken()) {
				static::removeIndex($index);
			}
		}
	}

	/**
	 *
	 * @param CSRF $csrf
	 */
	protected function append(CSRF $csrf): void
	{
		array_push($_SESSION['csrf_token'], [
			"token" => $csrf->getToken(),
			"time" => $csrf->getTime()
		]);
	}

	/**
	 *
	 * @return string
	 */
	public function generateToken(): string
	{
		$csrf = $this->buildInstance();
		$token = $csrf->generateToken();
		$this->append($csrf);
		return $token;
	}

	/**
	 *
	 * @param string $token
	 * @param string $redirect
	 * @param string $default_redirect
	 * @return boolean
	 */
	public function valideToken(string $token = "", string $redirect = "", string $default_redirect): bool
	{
		foreach ($_SESSION['csrf_token'] as $index => $csrf_token) {

			if ($csrf_token['token'] != $token) {
				continue;
			}

			$csrf = $this->buildInstance($csrf_token['token'], $csrf_token['time']);
			if ($csrf->valideToken($token, $redirect, $default_redirect)) {
				static::removeIndex($index);
				return true;
			}
		}

		if (empty($redirect)) {
		    $redirect = $default_redirect;
		}
		
		Request::redirect($redirect);
		return false;
	}

	/**
	 *
	 * @param string $token
	 * @param int $time
	 * @return CSRF
	 */
	protected function buildInstance(string $token = "", int $time = 0): CSRF
	{
	    return new CSRF($token, $time);
	}

	/**
	 *
	 * @param string $index
	 * @return boolean
	 */
	protected static function removeIndex(string $index): bool
	{
		array_splice($_SESSION['csrf_token'], $index, 1);
		return true;
	}
}

